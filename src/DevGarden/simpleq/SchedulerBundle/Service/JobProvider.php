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
    public function __construct(QueueProvider $provider){

        $this->provider = $provider;
    }

    /**
     * @param string $queue
     * @param mixed|string|array $task
     * @return array
     */
    public function provideJob($queue, $task = null){
        return $this->provider->getNextQueueEntry($queue, $task);
    }
}