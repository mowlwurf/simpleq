<?php

namespace simpleq\SimpleqBundle\Command;

use simpleq\SchedulerBundle\Service\WorkingQueueHistoryProvider;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WorkingQueueHistoryCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this->setName(\Command::SCHEDULER_HISTORY);
        $this->addArgument('name', InputArgument::OPTIONAL);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $name          = ($input->getArgument('name')) ? $input->getArgument('name') : null;
        $provider      = $this->getSchedulerHistoryProvider();
        $activeWorkers = $provider->getWorkerHistory($name);
        $output->writeln(sprintf('Scheduler working queue history contains %s executed workers',
            count($activeWorkers)));
        $table = $this->getHelper('table');
        $output->writeln('Setting headers ...');
        $table->setHeaders(array('ID', 'Status', 'PID', 'Worker', 'Created', 'Updated', 'Archived'));
        $output->writeln('Setting rows ...');
        $table->setRows($activeWorkers);
        $output->writeln('Print output ...');
        $table->render($output);
    }

    /**
     * @return WorkingQueueHistoryProvider
     */
    protected function getSchedulerHistoryProvider()
    {
        return $this->getContainer()->get('simpleq.scheduler.history.provider');
    }
}