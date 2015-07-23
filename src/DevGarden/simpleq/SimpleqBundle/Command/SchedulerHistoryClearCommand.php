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

    /**
     * @param array $activeWorkers
     * @return array
     */
    protected function mapWorkerQueueObjectsToArray($activeWorkers)
    {
        $workers = array();
        foreach ($activeWorkers as $activeWorker) {
            $worker['id'] = $activeWorker->getId();
            $worker['status'] = $activeWorker->getStatus();
            $worker['pid'] = $activeWorker->getPid();
            $worker['worker'] = $activeWorker->getWorker();
            $createDate = $activeWorker->getCreated();
            $updateDate = $activeWorker->getUpdated();
            $archivedDate = $activeWorker->getArchived();
            $worker['created'] = $createDate->format('Y-m-d H:i:s');
            $worker['updated'] = $updateDate->format('Y-m-d H:i:s');
            $worker['archived'] = $archivedDate->format('Y-m-d H:i:s');
            $workers[] = $worker;
        }

        return $workers;
    }
}