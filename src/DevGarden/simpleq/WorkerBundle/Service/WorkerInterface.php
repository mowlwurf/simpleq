<?php

namespace DevGarden\simpleq\WorkerBundle\Service;

use DevGarden\simpleq\QueueBundle\Service\JobProvider;
use DevGarden\simpleq\SchedulerBundle\Service\WorkerProvider;

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
     * @param string $data
     * @return int processId
     */
    public abstract function execute($data);

    /**
     * @param WorkerProvider $workerProvider
     */
    final public function setWorkerProvider($workerProvider)
    {
        $this->workerProvider = $workerProvider;
    }

    /**
     * @param JobProvider $jobProvider
     */
    final public function setJobProvider($jobProvider)
    {
        $this->jobProvider = $jobProvider;
    }
}