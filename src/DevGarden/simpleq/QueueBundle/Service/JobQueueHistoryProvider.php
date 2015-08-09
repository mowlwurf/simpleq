<?php

namespace DevGarden\simpleq\QueueBundle\Service;

use Doctrine\Common\Persistence\ManagerRegistry;

class JobQueueHistoryProvider
{
    const QUEUE_REPOSITORY = 'QueueBundle';

    /**
     * @var ManagerRegistry
     */
    protected $doctrine;

    /**
     * @var QueueProvider
     */
    private $queueProvider;

    /**
     * @param ManagerRegistry $doctrine
     * @param QueueProvider $queueProvider
     */
    public function __construct(ManagerRegistry $doctrine, QueueProvider $queueProvider)
    {
        $this->doctrine = $doctrine;
        $this->queueProvider = $queueProvider;
    }

    /**
     * @param string $queue
     * @param int $id
     */
    public function archiveQueueEntry($queue, $id)
    {
        $class  = 'DevGarden\simpleq\QueueBundle\Entity\\'.ucfirst($queue).'History';
        $entity = $this->queueProvider->getQueueEntryById(ucfirst($queue), $id);
        $logEntity = new $class();
        $logEntity->setStatus($entity->getStatus());
        $logEntity->setTask($entity->getTask());
        $logEntity->setData($entity->getData());
        $logEntity->setCreated($entity->getCreated());
        $logEntity->setUpdated($entity->getUpdated());
        $logEntity->setArchived(new \DateTime());
        $this->doctrine->getManager()->persist($logEntity);
        $this->doctrine->getManager()->flush();
    }

    /**
     * @param string $queue
     * @return array
     */
    public function getQueueHistory($queue)
    {
        $entity = $queue.'History';
        $repository = $this->doctrine->getRepository(sprintf('%s:%s', self::QUEUE_REPOSITORY, ucfirst($entity)));

        return $repository->findAll();
    }

    /**
     * @param string $queue
     */
    public function clearQueueHistory($queue)
    {
        $this->doctrine->getConnection()->exec('TRUNCATE %s', $queue.'_history_');
    }
}