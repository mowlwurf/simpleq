<?php

namespace simpleq\SimpleqBundle\Command;

class SchedulerStatusCommand extends BaseCommand
{
    public function configure()
    {
        $this->setName(CommandPatterns::SCHEDULER_STATUS);
        $this->addArgument('name');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $name = ($input->getArgument('name')) ? $input->getArgument('name') : null;
        $provider = $this->getWorkerProvider();
        $activeWorkers = $provider->getActiveWorkers($name);
        $output->writeln(sprintf('Scheduler working queue contains %s active Workers', count($activeWorkers)));
        $table = $this->getHelper('table');
        $output->writeln('Setting headers ...');
        $table->setHeaders(array('ID', 'Status', 'PID', 'Worker', 'Created', 'Updated'));
        $output->writeln('Setting rows ...');
        $table->setRows($activeWorkers);
        $output->writeln('Print output ...');
        $table->render($output);
    }

    /**
     * @return WorkerProvider
     */
    protected function getWorkerProvider()
    {
        return $this->getContainer()->get('simpleq.worker.provider');
    }
}