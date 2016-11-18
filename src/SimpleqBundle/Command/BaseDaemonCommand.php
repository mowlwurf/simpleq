<?php

namespace simpleq\SimpleqBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Filesystem\Filesystem;


/**
 * Class BaseDaemonCommand
 *
 * @package DevGarden\simpleq\SimpleqBundle\Command
 */
abstract class BaseDaemonCommand extends ContainerAwareCommand
{
    CONST PID_FILE_NAME = 'simpleq_scheduler_start';

    /**
     * Asserts that the current command is running as single instance. Throw exception if instance is running
     * in another runtime scope.
     *
     * @param int $mode
     * @param int $umask
     * @throws \RuntimeException
     */
    protected function assertSingleInstance($mode = 0666, $umask = 0000)
    {
        // Check if command is already running
        $pid = posix_getpid();
        $pidFilePath = sprintf('%s/%s.pid', $this->getPidFileDirectoryPath(), self::PID_FILE_NAME);
        $filesystem = new Filesystem();
        if ($filesystem->exists($pidFilePath)) {
            $pidFileHandle = new \SplFileObject($pidFilePath, 'r');
            $storedPID = trim($pidFileHandle->fgets());
            $pidProcPath = sprintf('/proc/%s', $storedPID);
            if ($filesystem->exists($pidProcPath)) {
                throw new \RuntimeException(
                    sprintf('Command is already running as PID #%s (%s)', $storedPID, $pidFilePath)
                );
            } else {
                // Remove PID file
                $filesystem->remove($pidFilePath);
            }
        }
        // Store pid file
        $pidFileHandle = new \SplFileObject($pidFilePath, 'w');
        if (!$pidFileHandle->fwrite($pid)) {
            throw new \RuntimeException(sprintf('Could not store pid file: %s', $pidFilePath));
        }
        if (true !== @chmod($pidFilePath, $mode & ~$umask)) {
            throw new \RuntimeException(sprintf('Failed to chmod file %s', $pidFilePath));
        }
    }

    /**
     * Returns a valid file name description string for the commands name
     *
     * @return string
     */
    protected function formatPidFilePath()
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '_', $this->getName());
    }

    /**
     * Get path to pid file directory
     *
     * @return string
     */
    protected function getPidFileDirectoryPath()
    {
        return '/var/lock';
    }

    public function stopDaemon()
    {
        $pidFilePath = sprintf('%s/%s.pid', $this->getPidFileDirectoryPath(), self::PID_FILE_NAME);
        $pidFileHandle = new \SplFileObject($pidFilePath, 'r');
        $storedPID = trim($pidFileHandle->fgets());
        $filesystem = new Filesystem();
        $filesystem->remove($pidFilePath);
        posix_kill($storedPID, SIGKILL);
    }
}