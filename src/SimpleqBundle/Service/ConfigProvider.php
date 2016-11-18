<?php

namespace simpleq\SimpleqBundle\Service;

class ConfigProvider
{
    const DEFAULT_CONFIG_PATH_PATTERN = '/../../simpleq/';

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
    public function __construct(array $queues)
    {
        foreach ($queues as $key => $queue) {
            $isChain        = $queue['type'] == 'chain' ? true : false;
            $queueTaskChain = [];
            foreach ($queue['worker'] as $workerKey => $worker) {
                $worker['queue'] = $key;
                $worker['name']  = $workerKey;
                array_push($this->workers, $worker);
                if ($isChain) {
                    array_push($queueTaskChain, $worker['task']);
                }
            }
            if ($isChain) {
                $queues[$key]['task_chain'] = $queueTaskChain;
            }
        }
        $this->queues = $queues;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function getQueue($name)
    {
        return isset($this->queues[$name]) && is_array($this->queues[$name]) ? $this->queues[$name] : false;
    }

    /**
     * @return array
     */
    public function getQueueList()
    {
        return $this->queues;
    }

    /**
     * @param string $queue
     * @param string $name
     * @return bool
     */
    public function getWorker($queue, $name)
    {
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
    public function getWorkerList()
    {
        return $this->workers;
    }

    /**
     * @param string $attr
     * @param string $id
     * @return bool|mixed|string
     */
    public function getQueueAttributeByQueueId($attr, $id)
    {
        foreach ($this->queues as $key => $queue) {
            if ($key == $id) {
                $return = $attr == 'delete_on_failure' ? true : 0;

                return isset($queue[$attr]) ? $queue[$attr] : $return;
            }
        }

        return false;
    }

    /**
     * @param string $attr
     * @param string $id
     * @return bool|mixed|string
     */
    public function getWorkerAttributeByServiceId($attr, $id)
    {
        foreach ($this->workers as $worker) {
            if ($worker['class'] === $id) {
                return isset($worker[$attr]) ? $worker[$attr] : 0;
            }
        }

        return false;
    }
}