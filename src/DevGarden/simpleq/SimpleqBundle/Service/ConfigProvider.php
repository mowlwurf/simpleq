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
            foreach ($queue['worker'] as $workerKey => $worker) {
                $worker['queue'] = $key;
                $worker['name']  = $workerKey;
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
        return isset($this->queues[$name]) && is_array($this->queues[$name]) ? $this->queues[$name] : false;
    }

    /**
     * @return array
     */
    public function getQueueList(){
        return $this->queues;
    }

    /**
     * @param $queue
     * @param $name
     * @return bool
     */
    public function getWorker($queue, $name){
        foreach ($this->workers as $worker) {
            if ($worker['queue'] == $queue && $worker['name'] == $name) {
                return $worker;
            }
        }
        return false;
    }

    /**
     * @return array
     */
    public function getWorkerList(){
        return $this->workers;
    }

    /**
     * @param $id
     * @return bool
     */
    public function getQueueByWorkerService($id){
        foreach($this->workers as $worker){
            if ($worker['class'] === $id) {
                return $worker['queue'];
            }
        }
        return false;
    }
}