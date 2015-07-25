<?php

namespace DevGarden\simpleq\SimpleqBundle\Command;

use DevGarden\simpleq\QueueBundle\Service\QueueProvider;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class QueueCheckCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('simpleq:queue:check');
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
                    $table->setRows($this->mapQueueObjectsToArray($entries));
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
     * @param $entities
     * @return array
     */
    public function MapQueueObjectsToArray($entities)
    {
        $arr = [];
        foreach ($entities as $entity) {
            $created = $entity->getCreated();
            $updated = $entity->getUpdated();
            $arr[] = [
                'id' => $entity->getId(),
                'status' => $entity->getStatus(),
                'task' => $entity->getTask(),
                'data' => $entity->getData(),
                'created' => $created->format('Y-m-d h:i:s'),
                'updated' => $updated->format('Y-m-d h:i:s')
            ];
        }

        return $arr;
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