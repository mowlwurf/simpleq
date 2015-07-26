<?php

namespace DevGarden\simpleq\WorkerBundle\Service;


use DevGarden\simpleq\SchedulerBundle\Service\JobProvider;
use DevGarden\simpleq\SchedulerBundle\Service\WorkingQueueHistoryProvider;

abstract class WorkerInterface
{
    /**
     * @var int
     */
    protected $processId;

    /**
     * @var array
     */
    protected $status;

    /**
     * @var mixed|string|int
     */
    protected $taskId;

    /**
     * @var WorkerProvider
     */
    protected $workerProvider;

    /**
     * @var JobProvider
     */
    protected $jobProvider;

    /**
     * @var WorkingQueueHistoryProvider
     */
    protected $historyProvider;

    /**
     * @param string $data
     * @return int processId
     */
    public abstract function execute($data);

    /**
     * @param WorkerProvider $workerProvider
     */
    public function setWorkerProvider($workerProvider){
        $this->workerProvider = $workerProvider;
    }

    /**
     * @param JobProvider $jobProvider
     */
    public function setJobProvider($jobProvider){
        $this->jobProvider = $jobProvider;
    }

    /**
     * @param WorkingQueueHistoryProvider $historyProvider
     */
    public function setHistoryProvider($historyProvider){
        $this->historyProvider = $historyProvider;
    }
}