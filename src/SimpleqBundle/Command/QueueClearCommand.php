<?php

namespace simpleq\SimpleqBundle\Command;

class QueueClearCommand extends BaseCommand
{
    public function configure()
    {
        $this->setName(\CommandPatterns::QUEUE_CLEAR);
        $this->addArgument('name', InputArgument::REQUIRED);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write(sprintf('Clearing queue %s ..', $input->getArgument('name')));
        $provider = $this->getQueueProvider();
        $output->write('.');
        $provider->clearQueue($input->getArgument('name'));
        $output->writeln('Finished!');
    }

    /**
     * @return QueueProvider
     */
    protected function getQueueProvider()
    {
        return $this->getContainer()->get('simpleq.queue.provider');
    }
}