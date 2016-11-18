<?php

namespace simpleq\QueueBundle\Service;

namespace simpleq\QueueBundle\Process\CreateDoctrineEntityProcess;
namespace simpleq\SimpleqBundle\Service\ConfigProvider;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDOConnection;
use PDO;

class QueueProvider
{
    const QUEUE_REPOSITORY = 'QueueBundle';

    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    /**
     * @var CreateDoctrineEntityProcess
     */
    protected $entityProcess;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @param ConfigProvider $config
     * @param CreateDoctrineEntityProcess $entityProcess
     * @param Connection $connection
     */
    public function __construct(
        ConfigProvider $config,
        CreateDoctrineEntityProcess $entityProcess,
        Connection $connection
    ) {
        $this->configProvider = $config;
        $this->entityProcess = $entityProcess;
        $this->connection = $connection;
        $this->connection->getConfiguration()->setSQLLogger(null);
    }

    /**
     * @param string $name
     * @throws \Exception
     */
    public function generateQueue($name)
    {
        $queue = $this->configProvider->getQueue($name);
        if (!$queue) {
            throw new \Exception(
                sprintf(
                    'Queue %s is undefined, defined queues are [\'%s\']',
                    $name,
                    implode("','", $this->configProvider->getQueueList())
                )
            );
        }
        $txt = <<<'txt'
<?php
namespace simpleq\QueueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="%s"%s)
 */
class %s
{
   /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    protected $task;

    /**
     * @ORM\Column(type="string", length=16)
     */
    protected $status;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $data;

    /**
     * @var \DateTime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @var \DateTime $updated
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    %s
}

txt;
        $indexes = ', indexes={@ORM\Index(name="newEntryRequest", columns={"status"}), @ORM\Index(name="getEntryByTask", columns={"task"})}';
        file_put_contents(
            __DIR__ . '/../Entity/' . ucfirst($name) . '.php',
            sprintf($txt, $name, $indexes, ucfirst($name), '')
        );
        if ($this->hasQueueHistory($name)) {
            $archivedExt = <<<'txt'
/**
 * @var \DateTime $archived
 *
 * @Gedmo\Timestampable(on="create")
 * @ORM\Column(type="datetime")
 */
protected $archived;
txt;
            file_put_contents(
                __DIR__ . '/../Entity/' . ucfirst($name) . 'History.php',
                sprintf($txt, $name . '_history', '', ucfirst($name) . 'History', $archivedExt)
            );
        }
        $this->entityProcess->execute('DevGarden/simpleq/QueueBundle/Entity');
    }

    /**
     * @codeCoverageIgnore
     * @param string $queue
     * @return bool
     */
    public function hasQueueHistory($queue)
    {
        return $this->configProvider->getQueueAttributeByQueueId('history', $queue);
    }

    /**
     * @codeCoverageIgnore
     * @param string $queue
     * @return bool
     */
    public function hasTaskChain($queue)
    {
        return $this->configProvider->getQueueAttributeByQueueId('type', $queue) == 'chain';
    }

    /**
     * @codeCoverageIgnore
     * @param string $queue
     * @return array
     */
    public function getTaskChain($queue)
    {
        return $this->configProvider->getQueueAttributeByQueueId('task_chain', $queue);
    }

    /**
     * @codeCoverageIgnore
     * @param string $queue
     * @return bool
     */
    public function deleteOnFailure($queue)
    {
        return $this->configProvider->getQueueAttributeByQueueId('delete_on_failure', $queue);
    }

    /**
     * @param string $queue
     * @param string $property
     * @param mixed $val
     * @return bool|object
     */
    public function getQueueEntryByProperty($queue, $property, $val)
    {
        $preparedStatement = $this->connection->prepare(
            sprintf(
                'SELECT * FROM %s WHERE %s = :%s',
                $queue,
                $property,
                $property
            )
        );
        $preparedStatement->bindValue($property, $val, PDOConnection::PARAM_STR);
        $preparedStatement->execute();

        return $preparedStatement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param $queue
     * @param mixed|string|array $task
     * @return array
     */
    public function getQueueEntries($queue, $task = null)
    {
        if (is_null($task)) {
            $preparedStatement = $this->connection->prepare(
                sprintf(
                    'SELECT * FROM %s',
                    $queue
                )
            );
            $preparedStatement->execute();

            return $preparedStatement->fetchAll(PDO::FETCH_ASSOC);
        }
        if (is_array($task)) {
            return $this->getQueueEntriesXOr($queue, $task);
        }

        return $this->getQueueEntryByProperty($queue, 'task', $task);
    }

    /**
     * @param string $queue
     * @param mixed|string|array $task
     * @return array|object|bool
     */
    public function getNextOpenQueueEntry($queue, $task = null)
    {
        if (is_null($task)) {
            $statement = <<<'SQL'
SELECT id, task, data FROM %s WHERE status = 'open' LIMIT 1
SQL;
            $preparedStatement = $this->connection->prepare(sprintf($statement, $queue));
            $preparedStatement->execute();

            return $preparedStatement->fetch(PDO::FETCH_ASSOC);
        }
        if (is_array($task)) {
            return $this->getQueueEntriesXOr($queue, $task, 1);
        }
        $statement = <<<'SQL'
SELECT id, task, data FROM %s WHERE status = 'open' AND task = :task LIMIT 1
SQL;
        $preparedStatement = $this->connection->prepare(sprintf($statement, $queue));
        $preparedStatement->bindValue('task', $task, PDOConnection::PARAM_STR);
        $preparedStatement->execute();

        return $preparedStatement->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @param string $queue
     * @param array $tasks
     * @param int $limit
     * @return array
     */
    public function getQueueEntriesXOr($queue, $tasks, $limit = 0)
    {
        $taskPattern = false;
        foreach ($tasks as $task) {
            $taskPattern .= 'task = \'' . $task . '\' OR ';
        }
        $taskPattern = substr($taskPattern, 0, -3);
        $preparedStatement = $this->connection->prepare(
            sprintf(
                'SELECT * FROM %s WHERE %s %s',
                $queue,
                $taskPattern,
                $limit != 0 ? 'LIMIT ' . $limit : ''
            )
        );
        $preparedStatement->execute();

        return $limit == 1 ? $preparedStatement->fetch(PDO::FETCH_ASSOC) : $preparedStatement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return array
     */
    public function getQueues()
    {
        return $this->configProvider->getQueueList();
    }

    /**
     * @param string $queue
     */
    public function clearQueue($queue)
    {
        $params = $this->connection->getParams();
        if (isset($params['driver']) && $params['driver'] == 'pdo_sqlite') {
            $this->connection->exec(sprintf('DELETE FROM %s', $queue));
        } else {
            $this->connection->exec(sprintf('TRUNCATE %s', $queue)); // @codeCoverageIgnore
        }
    }

    /**
     * @param string $queue
     * @param int $id
     */
    public function removeQueueEntry($queue, $id)
    {
        $statement = <<<'SQL'
DELETE FROM %s WHERE id = :id
SQL;
        $preparedStatement = $this->connection->prepare(sprintf($statement, $queue));
        $preparedStatement->bindValue('id', $id, PDOConnection::PARAM_INT);
        $preparedStatement->execute();
    }

    /**
     * @param string $queue
     * @param int $id
     * @param array $args
     */
    public function updateQueueEntry($queue, $id, array $args)
    {
        $updates = null;
        $statement = <<<'SQL'
UPDATE %s SET %s WHERE id = :id
SQL;

        foreach ($args as $arg => $val) {
            $updates[] = sprintf("%s = '%s'", $arg, $val);
        }
        $preparedStatement = $this->connection->prepare(sprintf(
            $statement,
            $queue,
            implode(',', $updates)
        ));
        $preparedStatement->bindValue('id', $id, PDOConnection::PARAM_INT);
        $preparedStatement->execute();
    }

    /**
     * @codeCoverageIgnore
     * @return string
     */
    public function getQueueRepository()
    {
        return self::QUEUE_REPOSITORY;
    }
}