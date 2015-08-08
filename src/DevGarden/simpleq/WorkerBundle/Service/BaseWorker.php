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
    public function setWorkerStatus($statusCode, $statusMessage)
    {
        $this->status = ['code' => $statusCode, 'message' => $statusMessage];
    }

    /**
     * @return array
     */
    public function getWorkerStatus()
    {
        return $this->status;
    }

    /**
     * @param int $processId
     */
    public function setProcessId($processId)
    {
        $this->processId = $processId;
    }

    /**
     * @return int
     */
    public function getProcessId()
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
    public function run($jobId, $pid, $worker, $data = null)
    {
        $this->setProcessId($pid);
        try {
            $this->pushWorkerStatus(
                WorkerStatus::WORKER_STATUS_PENDING_CODE,
                WorkerStatus::WORKER_STATUS_PENDING_MESSAGE
            );
            $this->prepare($data);
            $this->pushWorkerStatus(
                WorkerStatus::WORKER_STATUS_RUNNING_CODE,
                WorkerStatus::WORKER_STATUS_RUNNING_MESSAGE
            );
            $this->execute($data);
            $this->endJob($data);
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
                $this->jobProvider->updateJobStatus(
                    $this->workerProvider->getWorkerQueue($worker),
                    $jobId,
                    JobStatus::JOB_STATUS_FAILED
                );
            } catch (\Exception $e) {
                // maybe do sth. here
            }
            throw new \Exception('Worker failed');
        }
        try {
            $this->jobProvider->updateJobStatus(
                $this->workerProvider->getWorkerQueue($worker),
                $jobId,
                JobStatus::JOB_STATUS_FINISHED
            );
            $this->jobProvider->removeJob($this->workerProvider->getWorkerQueue($worker), $jobId);
        } catch (\Exception $e) {
            // maybe do sth. here
        }

        return $this->getWorkerStatus();
    }

    /**
     * @param int $code
     * @param string $message
     */
    protected function pushWorkerStatus($code, $message)
    {
        $this->setWorkerStatus($code, $message);
        $this->workerProvider->pushWorkerStatus($this->getProcessId(), $code);
    }

    /**
     * @return string
     */
    public function getQueueRepository()
    {
        return $this->jobProvider->getQueueRepository();
    }

    /**
     * prepare to execute stuff must be done before the worker execute its job
     * OVERWRITE THIS FUNCTION WITH YOUR CHILD WORKER CLASS TO EXECUTE YOUR CUSTOM CODE
     * @param $data
     */
    public function prepare($data)
    {
    }

    /**
     * execute the job, should set WorkerStatus to FAILED on exception
     * OVERWRITE THIS FUNCTION WITH YOUR CHILD WORKER CLASS TO EXECUTE YOUR CUSTOM CODE
     * @param $data
     * @return int|void
     */
    public function execute($data)
    {
    }

    /**
     * do stuff like clean up jobs
     * OVERWRITE THIS FUNCTION WITH YOUR CHILD WORKER CLASS TO EXECUTE YOUR CUSTOM CODE
     * @param $data
     */
    public function endJob($data)
    {
    }
}