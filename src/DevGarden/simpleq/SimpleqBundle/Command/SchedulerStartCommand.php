<?php

namespace DevGarden\simpleq\SimpleqBundle\Command;

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
        $provider = $this->getWorkerProvider();
        $workers  = $provider->getRegisteredWorkers();
        do{
            foreach ($workers as $key => $worker) {
                $activeWorkers = $provider->getActiveWorkers($worker['class']);
                //$jobs          = $this->getJobProvider()->provideJob();
                $countActiveWorkers = count($activeWorkers);
                if ($countActiveWorkers >= $worker['limit']) {
                    $output->writeln(sprintf('Limit reached for service %s', $worker['class']));
                    continue;
                }
                try{
                    $process = $this->getWorkerRunProcess();
                    $process->executeAsync($worker['class']);
                    $output->writeln('Spawned worker on pid '. $process->getPid());
                } catch(\Exception $e){
                    $output->writeln($e->getMessage());
                }
            }
            //TODO bad hack to avoid spawning new workers before, recently spawned workers have been started
            sleep(1);
        } while (true);
    }

    /**
     * @return WorkerProvider
     */
    protected function getWorkerProvider(){
        return $this->getContainer()->get('simpleq.worker.provider');
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