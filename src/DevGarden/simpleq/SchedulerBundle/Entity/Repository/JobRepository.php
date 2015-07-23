<?php

namespace DevGarden\simpleq\SchedulerBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class JobRepository extends EntityRepository
{
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
                    'SELECT p FROM QueueBundle:%s WHERE %s ORDER BY created DESC LIMIT 1',
                    ucfirst($queue),
                    $taskPattern
                )
            )
            ->getResult();
    }
}