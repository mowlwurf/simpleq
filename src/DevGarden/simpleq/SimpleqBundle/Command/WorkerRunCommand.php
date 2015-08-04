<?php

namespace DevGarden\simpleq\SimpleqBundle\Command;


use DevGarden\simpleq\WorkerBundle\Service\WorkerProvider;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WorkerRunCommand extends ContainerAwareCommand
{

    public function configure()
    {
        $this->setName('simpleq:worker:run');
        $this->addArgument('service', InputArgument::REQUIRED, 'service id of the worker you want to run');
        $this->addArgument('jobId', InputArgument::REQUIRED);
        $this->addArgument('data', InputArgument::REQUIRED);
        $this->addArgument('pid', InputArgument::OPTIONAL);
        $this->addArgument('task', InputArgument::OPTIONAL);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $ownPid = getmypid();
            $pid = ($input->getArgument('pid')) ? $input->getArgument('pid') : $ownPid;
            $this->getWorkerProvider()->updateWorkerPid($pid, $ownPid);
            $task = ($input->getArgument('task')) ? $input->getArgument('task') : null;
            $workerClass = $this->getContainer()->get($input->getArgument('service'));
            $workerClass->run($input->getArgument('jobId'), $ownPid, $input->getArgument('service'),
                $input->getArgument('data'));
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
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