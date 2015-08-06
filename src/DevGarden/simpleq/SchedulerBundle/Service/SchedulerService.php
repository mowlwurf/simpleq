<?php

namespace DevGarden\simpleq\SchedulerBundle\Service;


use DevGarden\simpleq\QueueBundle\Service\QueueProvider;
use DevGarden\simpleq\SchedulerBundle\Extension\JobStatus;
use DevGarden\simpleq\WorkerBundle\Process\WorkerRunProcess;
use DevGarden\simpleq\WorkerBundle\Service\WorkerProvider;
use Symfony\Component\Console\Output\OutputInterface;

class SchedulerService
{
    /**
     * @var WorkerProvider
     */
    private $workers;

    /**
     * @var JobProvider
     */
    private $jobs;

    /**
     * @var array
     */
    private $queues;

    /**
     * @param QueueProvider $queues
     * @param WorkerProvider $workers
     * @param JobProvider $jobs
     */
    public function __construct(
        QueueProvider $queues,
        WorkerProvider $workers,
        JobProvider $jobs
    ) {
        $this->workers = $workers;
        $this->jobs = $jobs;
        $this->queues = $queues->getQueues();
    }

    /**
     * @param OutputInterface $output
     * @throws \Exception
     */
    public function processScheduler(OutputInterface $output)
    {
        foreach ($this->queues as $qKey => $queue) {
            $workers = $queue['worker'];
            if (!is_array($workers) || empty($workers)) {
                unset($this->queues[$qKey]);
                $output->writeln('No workers registered for queue ' . $qKey);
                continue;
            }
            $this->spawnWorkers($workers, $qKey, $output);
        }
    }

    /**
     * @param array $workers
     * @param string $queue
     * @param OutputInterface $output
     * @throws \Exception
     */
    protected function spawnWorkers(array $workers, $queue, OutputInterface $output)
    {
        foreach ($workers as $key => $worker) {
            $task = null;
            if ($this->isWorkerLimitReached($worker)) {
                $output->writeln(sprintf('Limit reached for service %s', $worker['class']));
                continue;
            }
            $job = $this->getJob($queue, $task, $worker['class']);
            if (!$job) {
                $output->writeln(
                    sprintf(
                        'No jobs available for queue %s and task %s',
                        $queue,
                        $task
                    )
                );
                continue;
            }
            $tempPid = $this->registerWorker($worker['class']);
            try {
                $this->getNewWorkerProcess()->executeAsync($worker['class'], $job, $tempPid);
                $output->writeln(
                    sprintf('Spawned worker for job %s from queue %s', $job->getId(), $queue)
                );
            } catch (\Exception $e) {
                $output->writeln($e->getMessage());
            }
            $job = $tempPid = null;
        }
    }

    /**
     * @param string $service
     * @return string
     * @throws \Exception
     */
    protected function registerWorker($service)
    {
        try {
            return $this->workers->pushWorkerToWorkingQueue($service);
        } catch (\Exception $e) {
            throw new \Exception (sprintf(
                'Worker %s cannot be pushed to WorkingQueue: %s',
                $service,
                $e->getMessage()
            ));
        }
    }

    /**
     * @param string $queue
     * @param string $task
     * @param string $service
     * @return bool|object
     * @throws \Exception
     */
    protected function getJob($queue, $task, $service)
    {
        try {
            $job = $this->provideJob($queue, $task);
            if (!$job) {
                return false;
            }
            $this->jobs->updateJobStatus(
                $this->workers->getWorkerQueue($service),
                $job->getId(),
                JobStatus::JOB_STATUS_RUNNING
            );

            return $job;
        } catch (\Exception $e) {
            throw new \Exception ('Could not provide job for worker ' . $service);
        }
    }

    /**
     * @param array $worker
     * @return bool
     */
    protected function isWorkerLimitReached(array $worker)
    {
        $activeWorkers = $this->workers->getActiveWorkers($worker['class']);

        return count($activeWorkers) >= $worker['limit'];
    }

    /**
     * @param string $queue
     * @param string $task
     * @return object
     */
    protected function provideJob($queue, $task = null)
    {
        $jobs = $this->jobs->provideJob($queue, $task);

        return !empty($jobs) ? $jobs[0] : false;
    }

    /**
     * @return WorkerRunProcess
     */
    protected function getNewWorkerProcess()
    {
        return new WorkerRunProcess();
    }
}