<?php

namespace DevGarden\simpleq\SimpleqBundle\Service;

class ConfigProvider
{
    /**
     * @var array
     */
    protected $queues;

    /**
     * @var array
     */
    protected $workers;

    /**
     * @param array $queues
     * @param array $workers
     */
    public function __construct(array $queues, array $workers){
        $this->queues = $queues;
        $this->workers = $workers;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function getQueue($name){
        $key = array_search($name, $this->queues);
        return $key !== false ? $this->queues[$key] : false;
    }

    /**
     * @return array
     */
    public function getQueueList(){
        return $this->queues;
    }

    /**
     * @param $name
     * @return bool
     */
    public function getWorker($name){
        $key = array_search($name, $this->workers);
        return $key !== false ? $this->workers[$key] : false;
    }

    /**
     * @return array
     */
    public function getWorkerList(){
        return $this->workers;
    }
}