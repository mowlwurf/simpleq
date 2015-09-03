<?php

namespace DevGarden\simpleq\SimpleqBundle\Command;

class SchedulerHistoryClearCommand extends BaseCommand
{
    public function configure()
    {
        $this->setName(\CommandPatterns::SCHEDULER_HISTORY_CLEAR);
        $this->addArgument('name', InputArgument::OPTIONAL);
        $this->setDescription('seriously?');
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
        $output->writeln('Starting to clear scheduler history ...');
        $provider->clearWorkerHistory($name);
        $output->writeln('Finished clearing up scheduler history');
    }

    /**
     * @return WorkingQueueHistoryProvider
     */
    protected function getSchedulerHistoryProvider()
    {
        return $this->getContainer()->get('simpleq.scheduler.history.provider');
    }
}