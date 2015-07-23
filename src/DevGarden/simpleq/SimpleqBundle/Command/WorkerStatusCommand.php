<?php

namespace DevGarden\simpleq\SimpleqBundle\Command;

use DevGarden\simpleq\WorkerBundle\Service\WorkerProvider;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WorkerStatusCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this->setName('simpleq:worker:status');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $provider = $this->getWorkerProvider();
        $workers = $provider->getRegisteredWorkers();
        if (count($workers) > 0) {
            foreach ($workers as $id => $worker) {
                $output->writeln('Registered worker ' . $id);
                $output->writeln('Service: ' . $worker['class']);
                $output->writeln('Limit: ' . $worker['limit']);
                $output->writeln('Running: ' . count($provider->getActiveWorkers($worker['class'])));
            }
        } else {
            $output->writeln('No workers registered.');
        }
    }

    /**
     * @return WorkerProvider
     */
    protected function getWorkerProvider()
    {
        return $this->getContainer()->get('simpleq.worker.provider');
    }
}