<?php

namespace simpleq\SimpleqBundle\Command;

use simpleq\QueueBundle\Service\JobQueueHistoryProvider;
use simpleq\SimpleqBundle\Extension\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class QueueHistoryCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this->setName(Command::QUEUE_HISTORY);
        $this->addArgument('name', InputArgument::REQUIRED);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $name       = $input->getArgument('name');
        $provider   = $this->getQueueHistoryProvider();
        $activeJobs = $provider->getQueueHistory($name);
        $output->writeln(
            sprintf(
                'Job queue history contains %s executed jobs',
                count($activeJobs)
            )
        );
        $table = $this->getHelper('table');
        $output->writeln('Setting headers ...');
        $table->setHeaders(array('ID', 'Status', 'PID', 'Worker', 'Created', 'Updated', 'Archived'));
        $output->writeln('Setting rows ...');
        $table->setRows($activeJobs);
        $output->writeln('Print output ...');
        $table->render($output);
    }

    /**
     * @return JobQueueHistoryProvider
     */
    protected function getQueueHistoryProvider()
    {
        return $this->getContainer()->get('simpleq.queue.history.provider');
    }
}