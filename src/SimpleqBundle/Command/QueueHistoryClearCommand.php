<?php

namespace simpleq\SimpleqBundle\Command;

class QueueHistoryClearCommand extends BaseCommand
{
    public function configure()
    {
        $this->setName(\CommandPatterns::QUEUE_HISTORY_CLEAR);
        $this->addArgument('name', InputArgument::REQUIRED);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $provider = $this->getQueueHistoryProvider();
        $output->writeln('Starting to clear job queue history ...');
        $provider->clearQueueHistory($name);
        $output->writeln('Finished clearing up job queue history');
    }

    /**
     * @return JobQueueHistoryProvider
     */
    protected function getQueueHistoryProvider()
    {
        return $this->getContainer()->get('simpleq.queue.history.provider');
    }
}