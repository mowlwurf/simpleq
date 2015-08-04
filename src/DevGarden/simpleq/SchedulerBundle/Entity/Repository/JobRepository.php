<?php

namespace DevGarden\simpleq\SchedulerBundle\Entity\Repository;

use DevGarden\simpleq\QueueBundle\Service\QueueProvider;
use Doctrine\ORM\EntityRepository;

class JobRepository extends EntityRepository
{
    /**
     * @param string $queue
     * @param string $tasks
     * @return array
     */
    public function findAllJobsByQueueForTasks($queue, $tasks)
    {
        $taskPattern = false;
        if (!is_array($tasks)) {
            $taskPattern = 'task = \'' . $tasks . '\'';
        } else {
            foreach ($tasks as $task) {
                $taskPattern .= 'task = \'' . $task . '\' OR';
            }
            $taskPattern = substr($taskPattern, 0, -3);
        }

        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT p FROM %s:%s WHERE %s ORDER BY created DESC LIMIT 1',
                    QueueProvider::QUEUE_REPOSITORY,
                    ucfirst($queue),
                    $taskPattern
                )
            )
            ->getResult();
    }
}