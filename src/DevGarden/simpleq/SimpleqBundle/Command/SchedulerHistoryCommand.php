<?php

namespace DevGarden\simpleq\SimpleqBundle\Command;


use DevGarden\simpleq\SchedulerBundle\Service\WorkingQueueHistoryProvider;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SchedulerHistoryCommand extends ContainerAwareCommand
{
    public function configure(){
        $this->setName('simpleq:scheduler:history');
        $this->addArgument('name');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output){
        $name = ($input->getArgument('name')) ? $input->getArgument('name') : null;
        $provider = $this->getSchedulerHistoryProvider();
        $activeWorkers = $provider->getWorkerHistory($name);
        $output->writeln(sprintf('Scheduler working queue history contains %s executed workers', count($activeWorkers)));
        $table = $this->getHelper('table');
        $output->writeln('Setting headers ...');
        $table->setHeaders(array('ID', 'Status', 'PID', 'Worker', 'Created', 'Updated','Archived'));
        $output->writeln('Setting rows ...');
        $table->setRows($this->mapWorkerQueueObjectsToArray($activeWorkers));
        $output->writeln('Print output ...');
        $table->render($output);
    }

    /**
     * @return WorkingQueueHistoryProvider
     */
    protected function getSchedulerHistoryProvider(){
        return $this->getContainer()->get('simpleq.scheduler.history.provider');
    }

    /**
     * @param array $activeWorkers
     * @return array
     */
    protected function mapWorkerQueueObjectsToArray($activeWorkers){
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