<?php

namespace DevGarden\simpleq\QueueBundle\Process;


use DevGarden\simpleq\SimpleqBundle\Process\BaseProcess;

class CreateDoctrineEntityProcess extends BaseProcess
{
    CONST CMD_PATTERN = 'app/console doctrine:generate:entities %s';

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
}