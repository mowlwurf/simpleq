<?php

namespace DevGarden\simpleq\SimpleqBundle\Command;

use DevGarden\simpleq\QueueBundle\Service\QueueProvider;
use DevGarden\simpleq\SchedulerBundle\Service\JobProvider;
use DevGarden\simpleq\WorkerBundle\Process\WorkerRunProcess;
use DevGarden\simpleq\WorkerBundle\Service\WorkerProvider;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SchedulerStartCommand extends BaseDaemonCommand
{
    public function configure(){
        $this->setName('simpleq:scheduler:start');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output){
        $this->assertSingleInstance();
        $provider    = $this->getWorkerProvider();
        $queues      = $this->getQueueProvider()->getQueues();
        do{
            foreach ($queues as $qKey => $queue) {
                $workers = $queue['worker'];
                if(!is_array($workers) || empty($workers)){
                    unset($queues[$qKey]);
                    $output->writeln('No workers registered for queue '. $qKey);
                    continue;
                }
                foreach ($workers as $key => $worker) {
                    $task  = null;
                    $limit = $worker['limit'];
                    // get already started worker for this type
                    $activeWorkers = $provider->getActiveWorkers($worker['class']);
                    $countActiveWorkers = count($activeWorkers);
                    if ($countActiveWorkers >= $limit) {
                        $output->writeln(sprintf('Limit reached for service %s', $worker['class']));
                        continue;
                    }
                    for($i=0;$i<$limit;$i++){
                        $job = $this->provideJob($qKey, $task);
                        $job = $job[0];
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
                            $process = $this->getWorkerRunProcess();
                            $process->executeAsync($worker['class'],$job);
                            $output->writeln('Spawned worker on pid '. $process->getPid());
                        } catch(\Exception $e){
                            $output->writeln($e->getMessage());
                        }
                    }
                }
            }
            //TODO bad hack to avoid spawning new workers before, recently spawned workers have been started
            sleep(5);
        } while (true);
    }

    /**
     * @param $queue
     * @param null $task
     * @return array
     */
    protected function provideJob($queue, $task = null){
        return $this->getJobProvider()->provideJob($queue, $task);
    }

    /**
     * @return WorkerProvider
     */
    protected function getWorkerProvider(){
        return $this->getContainer()->get('simpleq.worker.provider');
    }

    /**
     * @return QueueProvider
     */
    protected function getQueueProvider(){
        return $this->getContainer()->get('simpleq.queue.provider');
    }

    /**
     * @return JobProvider
     */
    protected function getJobProvider(){
        return $this->getContainer()->get('simpleq.scheduler.job.provider');
    }

    /**
     * @return WorkerRunProcess
     */
    protected function getWorkerRunProcess(){
        return $this->getContainer()->get('simpleq.worker.run.process');
    }
}