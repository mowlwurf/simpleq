<?php

namespace DevGarden\simpleq\SchedulerBundle\Service;


use DevGarden\simpleq\QueueBundle\Service\QueueProvider;
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
    ){
        $this->workers = $workers;
        $this->jobs = $jobs;
        $this->queues = $queues->getQueues();
    }

    /**
     * @param OutputInterface $output
     */
    public function processScheduler(OutputInterface $output){
        foreach ($this->queues as $qKey => $queue) {
            $workers = $queue['worker'];
            if(!is_array($workers) || empty($workers)){
                unset($this->queues[$qKey]);
                $output->writeln('No workers registered for queue '. $qKey);
                continue;
            }
            foreach ($workers as $key => $worker) {
                $task  = null;
                $limit = $worker['limit'];
                // get already started worker for this type
                $activeWorkers = $this->workers->getActiveWorkers($worker['class']);
                $countActiveWorkers = count($activeWorkers);
                if ($countActiveWorkers >= $limit) {
                    $output->writeln(sprintf('Limit reached for service %s', $worker['class']));
                    continue;
                }
                for($i=0;$i<$limit;$i++){
                    $job = $this->provideJob($qKey, $task);
                    if (empty($job)) {
                        $output->writeln(
                            sprintf(
                                'No jobs available for queue %s and task %s',
                                $qKey,
                                $task
                            )
                        );
                        continue;
                    }
                    try{
                        $process = $this->getNewWorkerProcess();
                        $process->executeAsync($worker['class'],$job);
                        $output->writeln('Spawned worker on pid '. $process->getPid());
                    } catch(\Exception $e){
                        $output->writeln($e->getMessage());
                    }
                }
            }
        }
    }

    /**
     * @param string $queue
     * @param string $task
     * @return object
     */
    protected function provideJob($queue, $task = null){
        $jobs = $this->jobs->provideJob($queue, $task);
        return $jobs[0];
    }

    /**
     * @return WorkerRunProcess
     */
    protected function getNewWorkerProcess(){
        return new WorkerRunProcess();
    }
}