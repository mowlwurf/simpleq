<?php

namespace simpleq\SchedulerBundle\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDOConnection;
use PDO;
use simpleq\SimpleqBundle\Service\ConfigProvider;

class WorkerProvider
{
    const SCHEDULER_REPOSITORY          = 'SchedulerBundle';
    const SCHEDULER_WORKING_QUEUE       = 'WorkingQueue';
    const SCHEDULER_WORKING_QUEUE_TABLE = 'working_queue';

    /**
     * @var ConfigProvider
     */
    protected $config;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @param ConfigProvider $config
     * @param Connection     $connection
     */
    public function __construct(ConfigProvider $config, Connection $connection)
    {
        $this->config     = $config;
        $this->connection = $connection;
        $this->connection->getConfiguration()->setSQLLogger(null);
    }

    /**
     * @codeCoverageIgnore
     * @return array
     */
    public function getRegisteredWorkers()
    {
        return $this->config->getWorkerList();
    }

    /**
     * @param string $name
     * @return array
     */
    public function getActiveWorkerCount($name = null)
    {
        if (!is_null($name)) {
            $statement         = <<<'SQL'
SELECT count(id) FROM %s WHERE worker = :worker
SQL;
            $preparedStatement = $this->connection->prepare(sprintf($statement, self::SCHEDULER_WORKING_QUEUE_TABLE));
            $preparedStatement->bindValue('worker', $name, PDOConnection::PARAM_STR);
            $preparedStatement->execute();
        } else {
            $preparedStatement = $this->connection->prepare(
                sprintf(
                    'SELECT count(id) FROM %s',
                    self::SCHEDULER_WORKING_QUEUE_TABLE
                )
            );
            $preparedStatement->execute();
        }

        return $preparedStatement->fetchColumn();
    }

    /**
     * @param string $name
     * @return array
     */
    public function getActiveWorkers($name = null)
    {
        if (is_null($name)) {
            $preparedStatement = $this->connection->prepare(
                sprintf(
                    'SELECT * FROM %s',
                    self::SCHEDULER_WORKING_QUEUE_TABLE
                )
            );
            $preparedStatement->execute();
        } else {
            $statement         = <<<'SQL'
SELECT * FROM %s WHERE worker = :worker
SQL;
            $preparedStatement = $this->connection->prepare(sprintf($statement, self::SCHEDULER_WORKING_QUEUE_TABLE));
            $preparedStatement->bindValue('worker', $name, PDOConnection::PARAM_STR);
            $preparedStatement->execute();
        }

        return $preparedStatement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param int $pid
     * @return bool|array
     */
    public function getWorkingQueueEntryByPid($pid)
    {
        $preparedStatement = $this->connection->prepare(
            sprintf(
                'SELECT * FROM %s WHERE pid = :pid',
                self::SCHEDULER_WORKING_QUEUE_TABLE
            )
        );
        $preparedStatement->bindValue('pid', $pid, PDOConnection::PARAM_STR);
        $preparedStatement->execute();

        return $preparedStatement->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @param string $name
     */
    public function clearQueue($name = null)
    {
        if (is_null($name)) {
            $params = $this->connection->getParams();
            if (isset($params['driver']) && $params['driver'] == 'pdo_sqlite') {
                $this->connection->exec(sprintf('DELETE FROM %s', self::SCHEDULER_WORKING_QUEUE_TABLE));
            } else {
                $this->connection->exec(sprintf('TRUNCATE %s',
                    self::SCHEDULER_WORKING_QUEUE_TABLE)); // @codeCoverageIgnore
            }
        } else {
            $statement         = <<<'SQL'
DELETE FROM %s WHERE worker = :worker
SQL;
            $preparedStatement = $this->connection->prepare(sprintf($statement, self::SCHEDULER_WORKING_QUEUE_TABLE));
            $preparedStatement->bindValue('worker', $name, PDOConnection::PARAM_STR);
            $preparedStatement->execute();
        }
    }

    /**
     * @param string $workerService
     * @return string $tempPid
     */
    public function pushWorkerToWorkingQueue($workerService)
    {
        $tempPid   = md5(microtime() . $workerService);
        $time      = new \DateTime();
        $statement = <<<'SQL'
INSERT INTO %s (`pid`,`status`,`worker`,`created`,`updated`)
VALUES (:pid,:status,:worker,:created,:updated)
SQL;

        $preparedStatement = $this->connection->prepare(sprintf($statement, self::SCHEDULER_WORKING_QUEUE_TABLE));
        $preparedStatement->bindValue('pid', $tempPid, PDOConnection::PARAM_STR);
        $preparedStatement->bindValue('status', WorkerStatus::WORKER_STATUS_OPEN_CODE, PDOConnection::PARAM_INT);
        $preparedStatement->bindValue('worker', $workerService, PDOConnection::PARAM_STR);
        $preparedStatement->bindValue('created', $time->format('Y-m-d h:i:s'), PDOConnection::PARAM_STR);
        $preparedStatement->bindValue('updated', $time->format('Y-m-d h:i:s'), PDOConnection::PARAM_STR);
        $preparedStatement->execute();

        return $tempPid;
    }

    /**
     * @param string $tempPid
     * @param int    $pid
     */
    public function updateWorkerPid($tempPid, $pid)
    {
        $statement         = <<<'SQL'
UPDATE %s SET pid = :npid WHERE pid = :pid
SQL;
        $preparedStatement = $this->connection->prepare(sprintf($statement, self::SCHEDULER_WORKING_QUEUE_TABLE));
        $preparedStatement->bindValue('npid', $pid, PDOConnection::PARAM_STR);
        $preparedStatement->bindValue('pid', $tempPid, PDOConnection::PARAM_STR);
        $preparedStatement->execute();
    }

    /***
     * @param int $pid
     */
    public function removeWorkingQueueEntry($pid)
    {
        $statement         = <<<'SQL'
DELETE FROM %s WHERE pid = :pid
SQL;
        $preparedStatement = $this->connection->prepare(sprintf($statement, self::SCHEDULER_WORKING_QUEUE_TABLE));
        $preparedStatement->bindValue('pid', $pid, PDOConnection::PARAM_INT);
        $preparedStatement->execute();
    }

    /**
     * @param int $pid
     * @param int $status
     */
    public function pushWorkerStatus($pid, $status)
    {
        $statement         = <<<'SQL'
UPDATE %s SET status = :status WHERE pid = :pid
SQL;
        $preparedStatement = $this->connection->prepare(sprintf($statement, self::SCHEDULER_WORKING_QUEUE_TABLE));
        $preparedStatement->bindValue('status', $status, PDOConnection::PARAM_INT);
        $preparedStatement->bindValue('pid', $pid, PDOConnection::PARAM_STR);
        $preparedStatement->execute();
    }

    /**
     * @param string $id
     * @return bool|string
     */
    public function getWorkerQueue($id)
    {
        return $this->config->getWorkerAttributeByServiceId('queue', $id);
    }

    /**
     * @param string $id
     * @return bool|int
     */
    public function getWorkerRetry($id)
    {
        return $this->config->getWorkerAttributeByServiceId('retry', $id);
    }

    /**
     * @param string $id
     * @return bool|string
     */
    public function getWorkerTask($id)
    {
        return $this->config->getWorkerAttributeByServiceId('task', $id);
    }

    /**
     * @param string $id
     * @return bool|int
     */
    public function getWorkerMaxLoad($id)
    {
        return $this->config->getWorkerAttributeByServiceId('max_load', $id);
    }
}
