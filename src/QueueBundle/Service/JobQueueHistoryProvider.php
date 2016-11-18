<?php

namespace simpleq\QueueBundle\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDOConnection;
use PDO;

/**
 * @codeCoverageIgnore
 */
class JobQueueHistoryProvider
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->connection->getConfiguration()->setSQLLogger(null);
    }

    /**
     * @param array $entity
     * @param int   $queue
     */
    public function archiveQueueEntry(array $entity, $queue)
    {
        $created   = new \DateTime($entity['created']);
        $updated   = new \DateTime($entity['updated']);
        $time      = new \DateTime();
        $statement = <<<'SQL'
INSERT INTO %s_history (`status`,`task`,`data`,`created`,`updated`,`archived`)
VALUES (:status,:task,:jdata,:created,:updated,:archived)
SQL;

        $preparedStatement = $this->connection->prepare(sprintf($statement, $queue));
        $preparedStatement->bindValue('status', $entity['status'], PDOConnection::PARAM_STR);
        $preparedStatement->bindValue('task', $entity['task'], PDOConnection::PARAM_STR);
        $preparedStatement->bindValue('jdata', $entity['data'], PDOConnection::PARAM_STR);
        $preparedStatement->bindValue('created', $created->format('Y-m-d h:i:s'), PDOConnection::PARAM_STR);
        $preparedStatement->bindValue('updated', $updated->format('Y-m-d h:i:s'), PDOConnection::PARAM_STR);
        $preparedStatement->bindValue('archived', $time->format('Y-m-d h:i:s'), PDOConnection::PARAM_STR);
        $preparedStatement->execute();
    }

    /**
     * @param string $queue
     * @return array
     */
    public function getQueueHistory($queue)
    {
        $statement         = <<<'SQL'
SELECT * FROM %s_history
SQL;
        $preparedStatement = $this->connection->prepare(sprintf($statement, $queue));
        $preparedStatement->execute();

        return $preparedStatement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param string $queue
     */
    public function clearQueueHistory($queue)
    {
        $this->connection->exec(sprintf('TRUNCATE %s', $queue.'_history'));
    }
}