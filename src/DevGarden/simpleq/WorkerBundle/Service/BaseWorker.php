<?php

namespace DevGarden\simpleq\WorkerBundle\Service;

use DevGarden\simpleq\SchedulerBundle\Extension\JobStatus;
use DevGarden\simpleq\WorkerBundle\Extension\WorkerStatus;

class BaseWorker extends WorkerInterface
{
    /**
     * @param int $statusCode WorkerStatus Code [open,running,failed,success]
     * @param string $statusMessage WorkerStatus Message
     */
    final public function setWorkerStatus($statusCode, $statusMessage)
    {
        $this->status = ['code' => $statusCode, 'message' => $statusMessage];
    }

    /**
     * @return array
     */
    final public function getWorkerStatus()
    {
        return $this->status;
    }

    /**
     * @param int $processId
     */
    final public function setProcessId($processId)
    {
        $this->processId = $processId;
    }

    /**
     * @return int
     */
    final public function getProcessId()
    {
        return $this->processId;
    }

    /**
     * @param int $jobId
     * @param int $pid
     * @param string $worker
     * @param string $data
     * @return array
     * @throws \Exception
     */
    final public function run($jobId, $pid, $worker, $data = null)
    {
        $queue = $this->workerProvider->getWorkerQueue($worker);
        $this->setProcessId($pid);
        try {
            $this->pushWorkerStatus(
                WorkerStatus::WORKER_STATUS_PENDING_CODE,
                WorkerStatus::WORKER_STATUS_PENDING_MESSAGE
            );
            $data = $this->prepare($data);
            $this->pushWorkerStatus(
                WorkerStatus::WORKER_STATUS_RUNNING_CODE,
                WorkerStatus::WORKER_STATUS_RUNNING_MESSAGE
            );
            $data = $this->execute($data);
            $data = $this->endJob($data);
            $this->pushWorkerStatus(
                WorkerStatus::WORKER_STATUS_SUCCESS_CODE,
                WorkerStatus::WORKER_STATUS_SUCCESS_MESSAGE
            );
        } catch (\Exception $e) {
            $this->pushWorkerStatus(
                WorkerStatus::WORKER_STATUS_FAILED_CODE,
                WorkerStatus::WORKER_STATUS_FAILED_MESSAGE
            );
            try {
                $this->jobProvider->updateJobAttribute(
                    $queue,
                    $jobId,
                    'status',
                    JobStatus::JOB_STATUS_FAILED
                );
                if ($this->jobProvider->hasToDeleteFailedJob($queue)) {
                    if ($this->jobProvider->hasToArchiveJob($queue)) {
                        $this->jobProvider->archiveJob($queue, $jobId);
                    }
                    $this->jobProvider->removeJob($queue, $jobId);
                }
            } catch (\Exception $e) {
                // maybe do sth. here
            }
            throw new \Exception('Worker failed ' . $e->getMessage());
        }
        if ($this->jobProvider->hasTaskChain($queue)) {
            $taskChain = $this->jobProvider->getTaskChain($queue);
            $member = array_search($this->workerProvider->getWorkerTask($worker), $taskChain);
        }
        if ($this->jobProvider->hasTaskChain($queue) && isset($taskChain[$member+1])) {
            $this->jobProvider->updateJobAttribute($queue, $jobId, 'status', JobStatus::JOB_STATUS_OPEN);
            $this->jobProvider->updateJobAttribute($queue, $jobId, 'task', $taskChain[$member+1]);
            $this->jobProvider->updateJobAttribute($queue, $jobId, 'data', $data);
        } else {
            try {
                $this->jobProvider->updateJobAttribute(
                    $queue,
                    $jobId,
                    'status',
                    JobStatus::JOB_STATUS_FINISHED
                );
                if ($this->jobProvider->hasToArchiveJob($queue)) {
                    $this->jobProvider->archiveJob($queue, $jobId);
                }
                $this->jobProvider->removeJob($queue, $jobId);
            } catch (\Exception $e) {
                // maybe do sth. here
            }
        }
        return $this->getWorkerStatus();
    }

    /**
     * @param int $code
     * @param string $message
     */
    final protected function pushWorkerStatus($code, $message)
    {
        $this->setWorkerStatus($code, $message);
        $this->workerProvider->pushWorkerStatus($this->getProcessId(), $code);
    }

    /**
     * @return string
     */
    final public function getQueueRepository()
    {
        return $this->jobProvider->getQueueRepository();
    }

    /**
     * prepare to execute stuff must be done before the worker execute its job
     * OVERWRITE THIS FUNCTION WITH YOUR CHILD WORKER CLASS TO EXECUTE YOUR CUSTOM CODE
     * @param $data
     * @return mixed|void
     */
    public function prepare($data)
    {
        return $data;
    }

    /**
     * execute the job, should set WorkerStatus to FAILED on exception
     * OVERWRITE THIS FUNCTION WITH YOUR CHILD WORKER CLASS TO EXECUTE YOUR CUSTOM CODE
     * @param $data
     * @return mixed|void
     */
    public function execute($data)
    {
        return $data;
    }

    /**
     * do stuff like clean up jobs
     * OVERWRITE THIS FUNCTION WITH YOUR CHILD WORKER CLASS TO EXECUTE YOUR CUSTOM CODE
     * @param $data
     * @return mixed|void
     */
    public function endJob($data)
    {
        return $data;
    }
}