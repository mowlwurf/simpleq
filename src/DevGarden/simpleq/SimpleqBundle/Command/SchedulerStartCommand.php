<?php

namespace DevGarden\simpleq\SimpleqBundle\Command;

use DevGarden\simpleq\SchedulerBundle\Service\SchedulerService;
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
        do{
            $this->getSchedulerService()->processScheduler($output);
            //TODO bad hack to avoid spawning new workers before, recently spawned workers have been registered to working queue
            sleep(5);
        } while (true);
    }

    /**
     * @return SchedulerService
     */
    protected function getSchedulerService(){
        return $this->getContainer()->get('simpleq.scheduler.service');
    }
}