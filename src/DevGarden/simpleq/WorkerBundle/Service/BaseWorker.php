<?php

namespace DevGarden\simpleq\WorkerBundle\Service;

use DevGarden\simpleq\SchedulerBundle\Service\WorkingQueueHistoryProvider;
use DevGarden\simpleq\WorkerBundle\Extension\WorkerStatus;

class BaseWorker extends WorkerInterface
{
    /**
     * @var WorkerProvider
     */
    protected $provider;

    /**
     * @var WorkingQueueHistoryProvider
     */
    private $historyProvider;

    /**
     * @param WorkerProvider $workerProvider
     * @param WorkingQueueHistoryProvider $historyProvider
     */
    public function __construct(WorkerProvider $workerProvider, WorkingQueueHistoryProvider $historyProvider){
        $this->provider = $workerProvider;
        $this->historyProvider = $historyProvider;
    }

    /**
     * @param int $statusCode WorkerStatus Code [open,running,failed,success]
     * @param string $statusMessage WorkerStatus Message
     */
    public function setWorkerStatus($statusCode, $statusMessage){
        $this->status = ['code' => $statusCode, 'message' => $statusMessage];
    }

    /**
     * @return array
     */
    public function getWorkerStatus(){
        return $this->status;
    }

    /**
     * @param int $processId
     */
    public function setProcessId($processId){
        $this->processId = $processId;
    }

    /**
     * @return int
     */
    public function getProcessId(){
        return $this->processId;
    }

    /**
     * @param mixed|string|int $taskId
     */
    public function setTaskId($taskId){
        $this->taskId = $taskId;
    }

    /**
     * @return int|mixed|string
     */
    public function getTaskId(){
        return $this->taskId;
    }

    /**
     * @param int $pid
     * @param string $worker
     * @return array
     */
    public function run($pid, $worker){
        $this->provider->pushWorkerToWorkingQueue($pid, $worker);
        $this->setProcessId($pid);
        try {
            $this->pushWorkerStatus(WorkerStatus::WORKER_STATUS_PENDING_CODE,WorkerStatus::WORKER_STATUS_PENDING_MESSAGE);
            $this->prepare();
            $this->pushWorkerStatus(WorkerStatus::WORKER_STATUS_RUNNING_CODE,WorkerStatus::WORKER_STATUS_RUNNING_MESSAGE);
            $this->execute();
            $this->endJob();
        } catch (\Exception $e) {
            $this->pushWorkerStatus(WorkerStatus::WORKER_STATUS_FAILED_CODE,WorkerStatus::WORKER_STATUS_FAILED_MESSAGE);
        }
        $this->pushWorkerStatus(WorkerStatus::WORKER_STATUS_SUCCESS_CODE,WorkerStatus::WORKER_STATUS_SUCCESS_MESSAGE);
        $this->historyProvider->archiveWorkingQueueEntry($pid);
        return $this->getWorkerStatus();
    }

    /**
     * @param $code
     * @param $message
     */
    protected function pushWorkerStatus($code, $message){
        $this->setWorkerStatus($code, $message);
        $this->provider->pushWorkerStatus($this->getProcessId(), $code);
    }

    /**
     * prepare to execute stuff must be done before the worker execute its job
     * OVERWRITE THIS FUNCTION WITH YOUR CHILD WORKER CLASS TO EXECUTE YOUR CUSTOM CODE
     */
    public function prepare(){}

    /**
     * execute the job, should set WorkerStatus to FAILED on exception
     * OVERWRITE THIS FUNCTION WITH YOUR CHILD WORKER CLASS TO EXECUTE YOUR CUSTOM CODE
     */
    public function execute(){}

    /**
     * do stuff like clean up jobs
     * OVERWRITE THIS FUNCTION WITH YOUR CHILD WORKER CLASS TO EXECUTE YOUR CUSTOM CODE
     */
    public function endJob(){}
}