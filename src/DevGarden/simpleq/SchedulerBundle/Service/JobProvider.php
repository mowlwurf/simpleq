<?php

namespace DevGarden\simpleq\SchedulerBundle\Service;

use DevGarden\simpleq\QueueBundle\Service\QueueProvider;

class JobProvider
{
    /**
     * @var QueueProvider
     */
    private $provider;

    /**
     * @param QueueProvider $provider
     */
    public function __construct(QueueProvider $provider)
    {
        $this->provider = $provider;
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
     * @param $queue
     * @param $jobId
     */
    public function removeJob($queue, $jobId)
    {
        $this->provider->removeQueueEntry($queue, $jobId);
    }

    public function updateJobStatus($queue, $jobId, $status)
    {
        $args = ['status' => $status];
        $this->provider->updateQueueEntry($queue, $jobId, $args);
    }
}