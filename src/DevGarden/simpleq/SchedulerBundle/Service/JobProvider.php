<?php

namespace DevGarden\simpleq\SchedulerBundle\Service;

use DevGarden\simpleq\QueueBundle\Service\JobQueueHistoryProvider;
use DevGarden\simpleq\QueueBundle\Service\QueueProvider;

class JobProvider
{
    /**
     * @var QueueProvider
     */
    private $provider;

    /**
     * @var JobQueueHistoryProvider
     */
    private $history;

    /**
     * @param QueueProvider $provider
     * @param JobQueueHistoryProvider $history
     */
    public function __construct(QueueProvider $provider, JobQueueHistoryProvider $history)
    {
        $this->provider = $provider;
        $this->history = $history;
    }

    /**
     * @param string $queue
     * @param mixed|string|array $task
     * @return array
     */
    public function provideJob($queue, $task = null)
    {
        return $this->provider->getNextOpenQueueEntry($queue, $task);
    }

    /***
     * @param string $queue
     * @param int $jobId
     */
    public function removeJob($queue, $jobId)
    {
        $this->provider->removeQueueEntry($queue, $jobId);
    }

    /**
     * @param string $queue
     * @param int $jobId
     */
    public function archiveJob($queue, $jobId)
    {
        $this->history->archiveQueueEntry($queue, $jobId);
    }

    /**
     * @param $queue
     * @return bool
     */
    public function hasToDeleteFailedJob($queue)
    {
        return $this->provider->deleteOnFailure($queue);
    }

    /**
     * @param string $queue
     * @return bool
     */
    public function hasToArchiveJob($queue)
    {
        return $this->provider->hasQueueHistory($queue);
    }

    /**
     * @param string $queue
     * @param int $jobId
     * @param string $status
     */
    public function updateJobStatus($queue, $jobId, $status)
    {
        $args = ['status' => $status];
        $this->provider->updateQueueEntry($queue, $jobId, $args);
    }

    /**
     * @return string
     */
    public function getQueueRepository()
    {
        return $this->provider->getQueueRepository();
    }
}