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
     * @param $job
     * @param bool $verbose
     * @return bool
     */
    public function execute($id, $job, $verbose = false){
        $this->setCommandLine(
            sprintf(self::CMD_PATTERN, $id, $job->getId())
        );
        return $this->executeProcess($verbose);
    }

    /**
     * @param string $id
     * @param object $job
     * @return int|null
     */
    public function executeAsync($id, $job){;
        $this->setCommandLine(
            sprintf(self::CMD_PATTERN, $id, $job->getId())
        );
        return $this->executeAsyncProcess();
    }
}