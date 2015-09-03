<?php

namespace DevGarden\simpleq\SimpleqBundle\Command;

class QueueCheckCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName(\CommandPatterns::QUEUE_CHECK);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Checking queues ...');
        $queueProvider = $this->getQueueProvider();
        $queues = $queueProvider->getQueues();
        try {
            foreach ($queues as $key => $queue) {
                $entries = $queueProvider->getQueueEntries($key);
                if (!empty($entries)) {
                    $table = $this->getHelper('table');
                    $output->writeln('Setting headers ...');
                    $table->setHeaders(array('ID', 'Status', 'Task', 'Data', 'Created', 'Updated'));
                    $output->writeln('Setting rows ...');
                    $table->setRows($entries);
                    $output->writeln('Print output ...');
                    $table->render($output);
                }
                $output->writeln(sprintf('Queue %s contains %s tasks', $key, count($entries)));
            }
        } catch (\Exception $e) {
            $output->writeln('Could not read database: ' . $e->getMessage());
        }
    }

    /**
     * @return QueueProvider
     */
    protected function getQueueProvider()
    {
        return $this->getContainer()->get('simpleq.queue.provider');
    }

    /**
     *
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getDoctrineManager()
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }
}