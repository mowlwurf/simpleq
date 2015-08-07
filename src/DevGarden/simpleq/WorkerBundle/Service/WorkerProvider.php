<?php

namespace DevGarden\simpleq\WorkerBundle\Service;

use DevGarden\simpleq\SchedulerBundle\Entity\WorkingQueue;
use DevGarden\simpleq\SimpleqBundle\Service\ConfigProvider;
use DevGarden\simpleq\WorkerBundle\Extension\WorkerStatus;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\DBAL\Driver\PDOConnection;

class WorkerProvider
{
    const SCHEDULER_REPOSITORY = 'SchedulerBundle';
    const SCHEDULER_WORKING_QUEUE = 'WorkingQueue';
    const SCHEDULER_WORKING_QUEUE_TABLE = 'working_queue';

    /**
     * @var ConfigProvider
     */
    protected $config;

    /**
     * @var ManagerRegistry
     */
    protected $doctrine;

    /**
     * @var ObjectRepository
     */
    protected $repository;

    /**
     * @var PDOConnection
     */
    protected $connection;

    /**
     * @param ConfigProvider $config
     * @param ManagerRegistry $doctrine
     */
    public function __construct(ConfigProvider $config, ManagerRegistry $doctrine)
    {
        $this->config = $config;
        $this->doctrine = $doctrine;
        $this->repository = $this->doctrine->getRepository(sprintf(
            '%s:%s',
            self::SCHEDULER_REPOSITORY,
            self::SCHEDULER_WORKING_QUEUE
        ));
        $this->connection = $this->doctrine->getConnection();
        $this->connection->getConfiguration()->setSQLLogger(null);
    }

    /**
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
    public function getActiveWorkers($name = null)
    {
        if (is_null($name)) {
            $preparedStatement = $this->connection->exec(sprintf('SELECT count(id) FROM %s_', self::SCHEDULER_WORKING_QUEUE_TABLE));
        } else {
            $statement = <<<'SQL'
SELECT count(id) FROM %s_ WHERE worker = :worker
SQL;
            $preparedStatement = $this->connection->prepare(sprintf($statement, self::SCHEDULER_WORKING_QUEUE_TABLE));
            $preparedStatement->bindValue('worker', $name, PDOConnection::PARAM_STR);
            $preparedStatement->execute();
        }
        return $preparedStatement->fetchColumn();
    }

    /**
     * @param int $pid
     * @return WorkingQueue
     */
    public function getWorkingQueueEntryByPid($pid)
    {
        return $this->repository->findOneBy(['pid' => $pid]);
    }

    /**
     * @param string $name
     */
    public function clearQueue($name = null)
    {
        if (is_null($name)) {
            $this->connection->exec(sprintf('TRUNCATE %s_', self::SCHEDULER_WORKING_QUEUE_TABLE));
        } else {
            $statement = <<<'SQL'
DELETE FROM %s_ WHERE worker = :worker
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
        $tempPid = md5(microtime() . $workerService);
        $worker = new WorkingQueue();
        $worker->setPid($tempPid);
        $worker->setStatus(WorkerStatus::WORKER_STATUS_OPEN_CODE);
        $worker->setWorker($workerService);
        $worker->setCreated(new \DateTime());
        $worker->setUpdated(new \DateTime());
        $this->doctrine->getManager()->persist($worker);
        $this->doctrine->getManager()->flush();

        return $tempPid;
    }

    /**
     * @param string $tempPid
     * @param int $pid
     */
    public function updateWorkerPid($tempPid, $pid)
    {
        $statement = <<<'SQL'
UPDATE %s_ SET pid = :npid WHERE pid = :pid
SQL;
        $preparedStatement = $this->connection->prepare(sprintf($statement, self::SCHEDULER_WORKING_QUEUE_TABLE));
        $preparedStatement->bindValue('npid', $pid, PDOConnection::PARAM_INT);
        $preparedStatement->bindValue('pid', $tempPid, PDOConnection::PARAM_STR);
        $preparedStatement->execute();
    }

    /***
     * @param int $pid
     */
    public function removeWorkingQueueEntry($pid)
    {
        $statement = <<<'SQL'
DELETE FROM %s_ WHERE pid = :pid
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
        $statement = <<<'SQL'
UPDATE %s_ SET status = :status WHERE pid = :pid
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
        return $this->config->getQueueByWorkerService($id);
    }
}