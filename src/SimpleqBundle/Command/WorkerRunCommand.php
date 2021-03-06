<?php

namespace simpleq\SimpleqBundle\Command;

use simpleq\QueueBundle\Service\JobProvider;
use simpleq\SchedulerBundle\Service\WorkerProvider;
use simpleq\SchedulerBundle\Service\WorkingQueueHistoryProvider;
use simpleq\SimpleqBundle\Extension\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WorkerRunCommand extends ContainerAwareCommand
{

    public function configure()
    {
        $this->setName(Command::WORKER_RUN);
        $this->addArgument('service', InputArgument::REQUIRED, 'service id of the worker you want to run');
        $this->addArgument('jobId', InputArgument::REQUIRED);
        $this->addArgument('data', InputArgument::REQUIRED);
        $this->addArgument('pid', InputArgument::OPTIONAL);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $workerProvider = $this->getWorkerProvider();
        $jobProvider    = $this->getJobProvider();
        $retry          = $workerProvider->getWorkerRetry($input->getArgument('service'));
        $retry          = $retry == 0 ? 1 : $retry;
        $ownPid         = getmypid();
        $pid            = ($input->getArgument('pid')) ? $input->getArgument('pid') : $ownPid;
        $workerProvider->updateWorkerPid($pid, $ownPid);
        $workerClass = $this->getContainer()->get($input->getArgument('service'));

        do {
            try {
                $workerClass->setWorkerProvider($workerProvider);
                $workerClass->setJobProvider($jobProvider);
                $workerClass->run(
                    $input->getArgument('jobId'),
                    $ownPid,
                    $input->getArgument('service'),
                    $input->getArgument('data')
                );
                $retry = 0;
            } catch (\Exception $e) {
                $retry--;
                $output->writeln($e->getMessage());
            }
        } while ($retry > 0);

        $workingQueueEntry = $workerProvider->getWorkingQueueEntryByPid($ownPid);
        if (is_array($workingQueueEntry)) {
            $this->getHistoryProvider()->archiveWorkingQueueEntry($workingQueueEntry);
            $workerProvider->removeWorkingQueueEntry($ownPid);
        }
    }

    /**
     * @return WorkerProvider
     */
    protected function getWorkerProvider()
    {
        return $this->getContainer()->get('simpleq.worker.provider');
    }

    /**
     * @return JobProvider
     */
    protected function getJobProvider()
    {
        return $this->getContainer()->get('simpleq.job.provider');
    }

    /**
     * @return WorkingQueueHistoryProvider
     */
    protected function getHistoryProvider()
    {
        return $this->getContainer()->get('simpleq.scheduler.history.provider');
    }
}