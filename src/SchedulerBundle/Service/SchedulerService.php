<?php

namespace simpleq\SchedulerBundle\Service;

use simpleq\QueueBundle\Service\JobProvider;
use simpleq\QueueBundle\Service\QueueProvider;
use simpleq\SchedulerBundle\Extension\JobStatus;
use simpleq\WorkerBundle\Process\WorkerRunProcess;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @codeCoverageIgnore
 */
class SchedulerService
{
    /**
     * @var WorkerProvider
     */
    protected $workers;

    /**
     * @var JobProvider
     */
    protected $jobs;

    /**
     * @var array
     */
    protected $queues;

    /**
     * @var WorkerRunProcess
     */
    protected $process;

    /**
     * @var WorkerSpawnValidator
     */
    protected $validator;

    /**
     * @param QueueProvider        $queues
     * @param WorkerProvider       $workers
     * @param JobProvider          $jobs
     * @param WorkerRunProcess     $process
     * @param WorkerSpawnValidator $spawnValidator
     */
    public function __construct(
        QueueProvider $queues,
        WorkerProvider $workers,
        JobProvider $jobs,
        WorkerRunProcess $process,
        WorkerSpawnValidator $spawnValidator
    ) {
        $this->workers   = $workers;
        $this->jobs      = $jobs;
        $this->queues    = $queues->getQueues();
        $this->process   = $process;
        $this->validator = $spawnValidator;
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
                $output->writeln('No workers registered for queue '.$qKey);
                continue;
            }
            $this->spawnWorkers($workers, $qKey, $output);
        }
    }

    /**
     * @param array           $workers
     * @param string          $queue
     * @param OutputInterface $output
     * @throws \Exception
     */
    protected function spawnWorkers(array $workers, $queue, OutputInterface $output)
    {
        foreach ($workers as $key => $worker) {
            $task    = isset($worker['task']) ? $worker['task'] : null;
            $isValidSpawnProcess = $this->validator->validate($worker);
            if ($isValidSpawnProcess !== TRUE) {
                $output->writeln($isValidSpawnProcess);
                continue;
            }

            $job = $this->getJob($queue, $task, $worker['class']);
            if (!$job) {
                $output->writeln(
                    sprintf(
                        'No jobs available for queue: %s and task: %s',
                        $queue,
                        empty($task) ? 'none' : $task
                    )
                );
                continue;
            }
            $tempPid = $this->registerWorker($worker['class']);
            try {
                $this->process->executeAsync($worker['class'], $job, $tempPid);
                $output->writeln(
                    sprintf('Spawned worker for job %s from queue %s', $job['id'], $queue)
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
            throw new \Exception (
                sprintf(
                    'Worker %s cannot be pushed to WorkingQueue: %s',
                    $service,
                    $e->getMessage()
                )
            );
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
            $this->jobs->updateJobAttribute(
                $this->workers->getWorkerQueue($service),
                $job['id'],
                'status',
                JobStatus::JOB_STATUS_RUNNING
            );

            return $job;
        } catch (\Exception $e) {
            throw new \Exception ('Could not provide job for worker '.$service);
        }
    }

    /**
     * @param string $queue
     * @param string $task
     * @return object
     */
    protected function provideJob($queue, $task = null)
    {
        return $this->jobs->provideJob($queue, $task);
    }
}