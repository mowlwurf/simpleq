<?php

namespace simpleq\SimpleqBundle\Process;

use Symfony\Component\Process\Process;

class BaseProcess extends Process
{
    /**
     * @param string $cmd
     * @param string $dir
     */
    public function __construct($cmd, $dir = null)
    {
        parent::__construct($cmd, $dir);
    }

    /**
     * @param bool $verbose
     * @return bool
     */
    protected function executeProcess($verbose = false)
    {
        try {
            if ($verbose) {
                return $this->run();
            } else {
                return $this->run(function ($type, $buffer) {
                    $noError = true;
                    if (Process::ERR === $type) {
                        print $buffer;
                        $noError = false;
                    } else {
                        print $buffer;
                    }

                    return $noError;
                });
            }
        } catch (\Exception $e) {
            print $e->getMessage();

            return false;
        }
    }

}