<?php

namespace simpleq\WorkerBundle\Process;

class WorkerRunProcess
{
    CONST CMD_PATTERN = 'bin/console simpleq:worker:run %s %s \'%s\' %s &> /dev/null &';

    /**
     * @param string $id
     * @param object $job
     * @param string $pid
     * @return int|null
     */
    public function executeAsync($id, $job, $pid)
    {
        $cmd = sprintf(self::CMD_PATTERN, $id, $job['id'], $job['data'], $pid);
        shell_exec($cmd);
    }
}