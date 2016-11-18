<?php

namespace simpleq\WorkerBundle\Service;

namespace simpleq\QueueBundle\Service\JobProvider;
namespace simpleq\SchedulerBundle\Service\WorkerProvider;

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