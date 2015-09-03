<?php

namespace DevGarden\simpleq\SimpleqBundle\Command;

class SchedulerInitCommand extends BaseCommand
{
    public function configure()
    {
        $this->setName(\CommandPatterns::SCHEDULER_INIT);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Init scheduler ...');
        $process = $this->getCreateEntityProcess();
        $process->execute('DevGarden/simpleq/SchedulerBundle/Entity');
        $command = $this->getApplication()->find('doctrine:schema:update');

        $arguments = array(
            'command' => 'doctrine:schema:update',
            '--force' => true,
        );

        $input = new ArrayInput($arguments);
        $returnCode = $command->run($input, $output);
        $output->writeln('Scheduler initiated. You can spawn workers now!');
    }

    /**
     * @return CreateDoctrineEntityProcess
     */
    protected function getCreateEntityProcess()
    {
        return $this->getContainer()->get('simpleq.queue.create.process');
    }
}