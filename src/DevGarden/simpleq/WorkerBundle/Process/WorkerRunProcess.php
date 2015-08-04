<?php

namespace DevGarden\simpleq\WorkerBundle\Process;

class WorkerRunProcess
{
    CONST CMD_PATTERN = 'app/console simpleq:worker:run %s %s \'%s\' %s &> /dev/null &';

    /**
     * @param string $id
     * @param object $job
     * @return int|null
     */
    public function executeAsync($id, $job, $pid)
    {
        $cmd = sprintf(self::CMD_PATTERN, $id, $job->getId(), $job->getData(), $pid);
        shell_exec($cmd);
    }
}