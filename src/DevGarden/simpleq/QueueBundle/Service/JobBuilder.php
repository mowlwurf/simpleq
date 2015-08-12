<?php

namespace DevGarden\simpleq\QueueBundle\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDOConnection;

class JobBuilder
{
    /**
     * @var array
     */
    protected $job;

    /**
     * @var string
     */
    protected $queue;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection){

        $this->connection = $connection;
    }

    /**
     * create new job item
     *
     * @param $queue
     */
    public function create($queue){
        $time        = new \DateTime();
        $this->job   = [
            'created' => $time,
            'updated' => $time,
            'status'  => 'open'
        ];
        $this->queue = $queue;
    }

    /**
     * set task for job (optional)
     *
     * @param string $task
     */
    public function setTask($task){
        $this->job['task'] = $task;
    }

    /**
     * set data for job (optional)
     *
     * @param string $data
     */
    public function setData($data){
        $this->job['data'] = $data;
    }

    /**
     * persists job to queue
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function persist(){
        $task = isset($this->job['task']) ? $this->job['task'] : '';
        $data = isset($this->job['data']) ? $this->job['data'] : '';
        $statement = <<<'SQL'
INSERT INTO %s (`status`,`task`,`data`,`created`,`updated`)
VALUES (:status,:task,:jdata,:created,:updated)
SQL;

        $preparedStatement = $this->connection->prepare(sprintf($statement, $this->queue));
        $preparedStatement->bindValue('status', $this->job['status'], PDOConnection::PARAM_STR);
        $preparedStatement->bindValue('task', $task, PDOConnection::PARAM_STR);
        $preparedStatement->bindValue('jdata', $data, PDOConnection::PARAM_STR);
        $preparedStatement->bindValue('created', $this->job['created']->format('Y-m-d h:i:s'), PDOConnection::PARAM_STR);
        $preparedStatement->bindValue('updated', $this->job['updated']->format('Y-m-d h:i:s'), PDOConnection::PARAM_STR);
        $preparedStatement->execute();
    }
}
