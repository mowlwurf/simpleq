<?php

namespace DevGarden\simpleq\QueueBundle\Service;

use DevGarden\simpleq\QueueBundle\Process\CreateDoctrineEntityProcess;
use DevGarden\simpleq\SimpleqBundle\Service\ConfigProvider;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectRepository;
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
     * @var ManagerRegistry
     */
    protected $doctrine;

    /**
     * @var PDOConnection
     */
    protected $connection;

    /**
     * @var array
     */
    protected $repositoryCache;

    /**
     * @param ConfigProvider $config
     * @param CreateDoctrineEntityProcess $entityProcess
     * @param ManagerRegistry $doctrine
     */
    public function __construct(
        ConfigProvider $config,
        CreateDoctrineEntityProcess $entityProcess,
        ManagerRegistry $doctrine
    ) {
        $this->configProvider = $config;
        $this->entityProcess = $entityProcess;
        $this->doctrine = $doctrine;
        $this->connection = $this->doctrine->getConnection();
        $this->connection->getConfiguration()->setSQLLogger(null);
    }

    /**
     * @param string $name
     * @throws \Exception
     */
    public function generateQueue($name)
    {
        if (!$this->configProvider->getQueue($name)) {
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
namespace DevGarden\simpleq\QueueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="%s", indexes={@ORM\Index(name="newEntryRequest", columns={"status"}), @ORM\Index(name="getEntryByTask", columns={"task"})} )
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
}

txt;
        file_put_contents(__DIR__ . '/../Entity/' . ucfirst($name) . '.php', sprintf($txt, $name, ucfirst($name)));
        $this->entityProcess->execute('DevGarden/simpleq/QueueBundle/Entity');
    }

    /**
     * @param $queue
     * @param mixed|string|array $task
     * @return array
     */
    public function getQueueEntries($queue, $task = null)
    {
        $repository = $this->loadRepository($queue);
        if (is_null($task)) {
            return $repository->findAll();
        }
        if (is_array($task)) {
            return $this->getQueueEntriesXOr($queue, $task);
        }

        return $repository->findBy(['task' => $task]);
    }

    /**
     * @param string $queue
     * @param mixed|string|array $task
     * @return array|object
     */
    public function getNextOpenQueueEntry($queue, $task = null)
    {
        if (is_null($task)) {
            $statement = <<<'SQL'
SELECT id, task, data FROM %s_ WHERE status = 'open' LIMIT 1
SQL;
            $preparedStatement = $this->connection->prepare(sprintf($statement,$queue));
            $preparedStatement->execute();
            return $preparedStatement->fetch(PDO::FETCH_ASSOC);
        }
        if (is_array($task)) {
            return $this->getQueueEntriesXOr($queue, $task);
        }
        $statement = <<<'SQL'
SELECT id, task, data FROM %s_ WHERE status = 'open' AND task = :task LIMIT 1
SQL;
        $preparedStatement = $this->connection->prepare(sprintf($statement,$queue));
        $preparedStatement->bindValue('task', $task, PDOConnection::PARAM_STR);
        $preparedStatement->execute();
        return $preparedStatement->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @param string $queue
     * @param string $tasks
     * @return array
     */
    public function getQueueEntriesXOr($queue, $tasks)
    {
        $repository = $this->loadRepository($queue);

        return $repository->findAllJobsByQueueForTasks($queue, $tasks);

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
        $this->connection->exec(sprintf('TRUNCATE %s_', $queue));
    }

    /**
     * @param string $queue
     * @param int $id
     */
    public function removeQueueEntry($queue, $id)
    {
        $statement = <<<'SQL'
DELETE FROM %s_ WHERE id = :id
SQL;
        $preparedStatement = $this->connection->prepare(sprintf($statement,$queue));
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
UPDATE %s_ SET %s WHERE id = :id
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
     * @param string $queue
     * @return ObjectRepository
     */
    protected function loadRepository($queue)
    {
        if (!isset($this->repositoryCache[$queue])) {
            $this->repositoryCache[$queue] = $this->doctrine->getRepository(sprintf(
                '%s:%s',
                self::QUEUE_REPOSITORY,
                ucfirst($queue)
            ));
        }

        return $this->repositoryCache[$queue];
    }

    /**
     * @return string
     */
    public function getQueueRepository()
    {
        return self::QUEUE_REPOSITORY;
    }
}