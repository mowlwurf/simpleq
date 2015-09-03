<?php

namespace DevGarden\simpleq\SimpleqBundle\Command;

use DevGarden\simpleq\SchedulerBundle\Service\SchedulerService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SchedulerStartCommand extends BaseDaemonCommand
{
    public function configure()
    {
        $this->setName(\CommandPatterns::SCHEDULER_START);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->assertSingleInstance();
        $scheduler = $this->getSchedulerService();
        do {
            $output->setVerbosity($input->getOption('verbose'));
            $scheduler->processScheduler($output);
        } while (true);
    }

    /**
     * @return SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->getContainer()->get('simpleq.scheduler.service');
    }
}