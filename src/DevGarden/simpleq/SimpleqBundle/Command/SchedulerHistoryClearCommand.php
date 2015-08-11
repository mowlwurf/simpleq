<?php

namespace DevGarden\simpleq\SimpleqBundle\Command;


use DevGarden\simpleq\SchedulerBundle\Service\WorkingQueueHistoryProvider;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SchedulerHistoryClearCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this->setName('simpleq:scheduler:clear:history');
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