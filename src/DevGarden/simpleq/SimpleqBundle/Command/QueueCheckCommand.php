<?php

namespace DevGarden\simpleq\SimpleqBundle\Command;

use DevGarden\simpleq\QueueBundle\Entity\Demoqueue;
use DevGarden\simpleq\QueueBundle\Entity\Queue;
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
            foreach ($queues as $queue) {
                $entries = $queueProvider->getQueueEntries($queue);
                $output->writeln(sprintf('Queue %s contains %s tasks', $queue, count($entries)));
            }
        } catch (\Exception $e) {
            $output->writeln('Could not read database: '.$e->getMessage());
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
     *  @return \Doctrine\ORM\EntityManager
     */
    protected function getDoctrineManager(){
        return  $this->getContainer()->get('doctrine')->getManager();
    }
}