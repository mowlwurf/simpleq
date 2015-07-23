<?php

namespace DevGarden\simpleq\WorkerBundle\Service;


abstract class WorkerInterface
{
    /**
     * @var int
     */
    protected $processId;

    /**
     * @var array
     */
    protected $status;

    /**
     * @var mixed|string|int
     */
    protected $taskId;

    /**
     * @param string $data
     * @return int processId
     */
    public abstract function execute($data);
}