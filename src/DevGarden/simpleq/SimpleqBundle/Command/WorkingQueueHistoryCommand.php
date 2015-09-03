<?php

namespace DevGarden\simpleq\SimpleqBundle\Command;

class WorkingQueueHistoryCommand extends BaseCommand
{
    public function configure()
    {
        $this->setName(\CommandPatterns::SCHEDULER_HISTORY);
        $this->addArgument('name', InputArgument::OPTIONAL);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $name = ($input->getArgument('name')) ? $input->getArgument('name') : null;
        $provider = $this->getSchedulerHistoryProvider();
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