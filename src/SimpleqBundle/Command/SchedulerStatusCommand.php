<?php

namespace simpleq\SimpleqBundle\Command;

use simpleq\SchedulerBundle\Service\WorkerProvider;
use simpleq\SimpleqBundle\Extension\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SchedulerStatusCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this->setName(Command::SCHEDULER_STATUS);
        $this->addArgument('name');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $name          = ($input->getArgument('name')) ? $input->getArgument('name') : null;
        $provider      = $this->getWorkerProvider();
        $activeWorkers = $provider->getActiveWorkers($name);
        $output->writeln(sprintf('Scheduler working queue contains %s active Workers', count($activeWorkers)));
        $table = new Table($output);
        $output->writeln('Setting headers ...');
        $table->setHeaders(array('ID', 'Status', 'PID', 'Worker','Error', 'Created', 'Updated'));
        $output->writeln('Setting rows ...');
        $table->setRows($activeWorkers);
        $output->writeln('Print output ...');
        $table->render();
    }

    /**
     * @return WorkerProvider
     */
    protected function getWorkerProvider()
    {
        return $this->getContainer()->get('simpleq.worker.provider');
    }
}