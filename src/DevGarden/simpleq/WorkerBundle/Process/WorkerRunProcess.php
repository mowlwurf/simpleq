<?php

namespace DevGarden\simpleq\WorkerBundle\Process;

use DevGarden\simpleq\SimpleqBundle\Process\BaseProcess;

class WorkerRunProcess extends BaseProcess
{
    CONST CMD_PATTERN = 'app/console simpleq:worker:run %s';

    public function __construct(){
        parent::__construct(self::CMD_PATTERN);
    }

    /**
     * @param string $id
     * @param bool $verbose
     * @return bool
     */
    public function execute($id, $verbose = false){
        $this->setCommandLine(
            sprintf(self::CMD_PATTERN, $id)
        );
        return $this->executeProcess($verbose);
    }

    /**
     * @param string $id
     * @return int|null
     */
    public function executeAsync($id){
        $this->setCommandLine(
            sprintf(self::CMD_PATTERN, $id)
        );
        return $this->executeAsyncProcess();
    }
}