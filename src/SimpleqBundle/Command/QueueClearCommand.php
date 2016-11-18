<?php

namespace simpleq\SimpleqBundle\Command;

use simpleq\QueueBundle\Service\QueueProvider;
use simpleq\SimpleqBundle\Extension\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class QueueClearCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this->setName(Command::QUEUE_CLEAR);
        $this->addArgument('name', InputArgument::REQUIRED);
    }

    /**
     * @param InputInterface  $input
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