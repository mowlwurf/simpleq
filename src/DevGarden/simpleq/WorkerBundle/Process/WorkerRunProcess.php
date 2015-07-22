<?php

namespace DevGarden\simpleq\WorkerBundle\Process;

use DevGarden\simpleq\SimpleqBundle\Process\BaseProcess;

class WorkerRunProcess extends BaseProcess
{
    CONST CMD_PATTERN = 'app/console simpleq:worker:run %s %s';

    public function __construct(){
        parent::__construct(self::CMD_PATTERN);
    }

    /**
     * @param string $id
     * @param string $task
     * @param bool $verbose
     * @return bool
     */
    public function execute($id, $task, $verbose = false){
        $this->setCommandLine(
            sprintf(self::CMD_PATTERN, $id, $task)
        );
        return $this->executeProcess($verbose);
    }

    /**
     * @param string $id
     * @param string $task
     * @return int|null
     */
    public function executeAsync($id, $task){
        $this->setCommandLine(
            sprintf(self::CMD_PATTERN, $id, $task)
        );
        return $this->executeAsyncProcess();
    }
}