<?php

namespace DevGarden\simpleq\SimpleqBundle\Command;


use DevGarden\simpleq\WorkerBundle\Service\WorkerProvider;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SchedulerStatusCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this->setName('simpleq:scheduler:status');
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
        $table->setRows($this->mapWorkerQueueObjectsToArray($activeWorkers));
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
            $worker['created'] = $createDate->format('Y-m-d H:i:s');
            $worker['updated'] = $updateDate->format('Y-m-d H:i:s');
            $workers[] = $worker;
        }

        return $workers;
    }
}