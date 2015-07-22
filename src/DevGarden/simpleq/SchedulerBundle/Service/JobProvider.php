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
     * @param null $task
     */
    public function provideJob($queue, $task = null){
        $entries = $this->provider->getQueueEntries($queue, $task);
        var_dump($entries);
    }
}