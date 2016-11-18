<?php

namespace simpleq\SimpleqBundle\Command;

use simpleq\WorkerBundle\Extension\WorkerStatus\WorkerProvider;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SchedulerClearCommand extends BaseCommand
{

    public function configure()
    {
        $this->setName(\Command::SCHEDULER_CLEAR);
        $this->addArgument('name', InputArgument::OPTIONAL);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name') != '' ? $input->getArgument('name') : null;
        $output->write(sprintf('Clearing queue %s .', is_null($name) ? 'all' : $name));
        $output->write('.');
        $provider = $this->getWorkerProvider();
        $output->write('.');
        $provider->clearQueue($name);
        $output->writeln('Finished!');
    }

    /**
     * @return WorkerProvider
     */
    protected function getWorkerProvider()
    {
        return $this->getContainer()->get('simpleq.worker.provider');
    }

}