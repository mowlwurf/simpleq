<?php

namespace simpleq\SchedulerBundle\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDOConnection;
use PDO;

/**
 * @codeCoverageIgnore
 */
class WorkingQueueHistoryProvider
{
    const SCHEDULER_WORKING_QUEUE_HISTORY_TABLE = 'working_queue_history';

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
     */
    public function archiveWorkingQueueEntry(array $entity)
    {
        $created           = new \DateTime($entity['created']);
        $updated           = new \DateTime($entity['updated']);
        $archived          = new \DateTime();
        $statement         = <<<'SQL'
INSERT INTO %s (`pid`,`status`,`worker`,`created`,`updated`,`archived`)
VALUES (:pid,:status,:worker,:created,:updated,:archived)
SQL;
        $preparedStatement = $this->connection->prepare(sprintf(
            $statement,
            self::SCHEDULER_WORKING_QUEUE_HISTORY_TABLE
        ));
        $preparedStatement->bindValue('pid', $entity['pid'], PDOConnection::PARAM_STR);
        $preparedStatement->bindValue('status', $entity['status'], PDOConnection::PARAM_INT);
        $preparedStatement->bindValue('worker', $entity['worker'], PDOConnection::PARAM_STR);
        $preparedStatement->bindValue('created', $created->format('Y-m-d h:i:s'), PDOConnection::PARAM_STR);
        $preparedStatement->bindValue('updated', $updated->format('Y-m-d h:i:s'), PDOConnection::PARAM_STR);
        $preparedStatement->bindValue('archived', $archived->format('Y-m-d h:i:s'), PDOConnection::PARAM_STR);
        $preparedStatement->execute();
    }

    /**
     * @param string $name
     * @return array
     */
    public function getWorkerHistory($name = null)
    {
        $statement         = <<<'SQL'
SELECT * FROM %s %s
SQL;
        $preparedStatement = $this->connection->prepare(sprintf(
            $statement,
            self::SCHEDULER_WORKING_QUEUE_HISTORY_TABLE,
            is_null($name) ? '' : 'WHERE worker = :worker'
        ));
        if (is_null($name)) {
            $preparedStatement->bindValue('worker', $name, PDOConnection::PARAM_STR);
        }
        $preparedStatement->execute();

        return $preparedStatement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * param name could be set to clear only one defined worker type from history
     *
     * @param string $name
     * @return bool|int
     */
    public function clearWorkerHistory($name = null)
    {
        if (is_null($name)) {
            return $this->connection->exec(sprintf('TRUNCATE %s', self::SCHEDULER_WORKING_QUEUE_HISTORY_TABLE));
        }
        $preparedStatement = $this->connection->prepare(sprintf(
            'DELETE FROM %s WHERE worker = :worker',
            self::SCHEDULER_WORKING_QUEUE_HISTORY_TABLE
        ));
        $preparedStatement->bindValue('worker', $name, PDOConnection::PARAM_STR);

        return $preparedStatement->execute();
    }
}