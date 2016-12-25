<?php

namespace simpleq\QueueBundle\Process;

use simpleq\SimpleqBundle\Process\BaseProcess;

class CreateDoctrineEntityProcess extends BaseProcess
{
    CONST CMD_PATTERN = 'bin/console doctrine:generate:entities %s';

    public function __construct()
    {
        parent::__construct(self::CMD_PATTERN);
    }

    /**
     * @param string $id
     * @param bool   $verbose
     * @return bool
     */
    public function execute($id, $verbose = false)
    {
        $this->setCommandLine(
            sprintf(self::CMD_PATTERN, $id)
        );

        return $this->executeProcess($verbose);
    }
}