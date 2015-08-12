<?php

namespace DevGarden\simpleq\QueueBundle\Service;

/**
 * @codeCoverageIgnore
 */
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
        $job = $this->provider->getQueueEntryByProperty($queue, 'id', $jobId);
        $this->history->archiveQueueEntry($job[0], $queue);
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
     * @return bool
     */
    public function hasTaskChain($queue)
    {
        return $this->provider->hasTaskChain($queue);
    }

    /**
     * @param string $queue
     * @return array
     */
    public function getTaskChain($queue)
    {
        return $this->provider->getTaskChain($queue);
    }

    /**
     * @param string $queue
     * @param int $jobId
     * @param string $key
     * @param string $value
     */
    public function updateJobAttribute($queue, $jobId, $key, $value)
    {
        $args = [$key => $value];
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