<?php

namespace simpleq\SimpleqBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SchedulerStopCommand extends BaseDaemonCommand
{
    public function configure()
    {
        $this->setName(\CommandPatterns::SCHEDULER_STOP);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Stopping scheduler daemon ...');
        $this->stopDaemon();
        $output->writeln('Stopped scheduler daemon');
    }
}