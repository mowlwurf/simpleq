<?php

namespace simpleq\SimpleqBundle\Command;

class SchedulerClearCommand extends BaseCommand
{

    public function configure()
    {
        $this->setName(\CommandPatterns::SCHEDULER_CLEAR);
        $this->addArgument('name', InputArgument::OPTIONAL);
    }

    /**
     * @param InputInterface $input
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