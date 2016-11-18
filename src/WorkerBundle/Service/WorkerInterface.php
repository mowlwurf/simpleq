<?php

namespace simpleq\WorkerBundle\Service;

use simpleq\QueueBundle\Service\JobProvider;
use simpleq\WorkerBundle\Extension\WorkerStatus\WorkerProvider;

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
     * @var string
     */
    protected $data;

    /**
     * @var WorkerProvider
     */
    protected $workerProvider;

    /**
     * @var JobProvider
     */
    protected $jobProvider;


    /**
     * @return int processId
     */
    public abstract function execute();

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