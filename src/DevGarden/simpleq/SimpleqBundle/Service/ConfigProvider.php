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
    protected $workers = [];

    /**
     * @param array $queues
     */
    public function __construct(array $queues){
        foreach ($queues as $key=> $queue) {
            foreach ($queue['worker'] as $worker) {
                array_push($this->workers, $worker);
            }
        }
        $this->queues = $queues;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function getQueue($name){
        return is_array($this->queues[$name]) ? $this->queues[$name] : false;
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