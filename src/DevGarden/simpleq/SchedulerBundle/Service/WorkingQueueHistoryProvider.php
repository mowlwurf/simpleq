<?php

namespace DevGarden\simpleq\SchedulerBundle\Service;

use DevGarden\simpleq\SchedulerBundle\Entity\WorkingQueueHistory;
use DevGarden\simpleq\WorkerBundle\Service\WorkerProvider;
use Doctrine\Common\Persistence\ManagerRegistry;

class WorkingQueueHistoryProvider
{
    /**
     * @var ManagerRegistry
     */
    protected $doctrine;

    /**
     * @var WorkerProvider
     */
    private $workerProvider;

    /**
     * @param ManagerRegistry $doctrine
     * @param WorkerProvider $workerProvider
     */
    public function __construct(ManagerRegistry $doctrine, WorkerProvider $workerProvider)
    {
        $this->doctrine = $doctrine;
        $this->workerProvider = $workerProvider;
    }

    /**
     * @param int $pid
     */
    public function archiveWorkingQueueEntry($pid)
    {
        $entity = $this->workerProvider->getWorkingQueueEntryByPid($pid);
        $logEntity = new WorkingQueueHistory();
        $logEntity->setPid($entity->getPid());
        $logEntity->setStatus($entity->getStatus());
        $logEntity->setWorker($entity->getWorker());
        $logEntity->setCreated($entity->getCreated());
        $logEntity->setUpdated($entity->getUpdated());
        $logEntity->setArchived(new \DateTime());
        $this->doctrine->getManager()->persist($logEntity);
        $this->doctrine->getManager()->flush();
    }

    /**
     * @param string $name
     * @return array
     */
    public function getWorkerHistory($name = null)
    {
        $repository = $this->doctrine->getRepository('SchedulerBundle:WorkingQueueHistory');

        return is_null($name) ? $repository->findAll() : $repository->findBy(['worker' => $name]);
    }

    /**
     * param name could be set to clear only one defined worker type from history
     *
     * @param string $name
     */
    public function clearWorkerHistory($name = null)
    {
        $repository = $this->doctrine->getRepository('SchedulerBundle:WorkingQueueHistory');
        $entries = !is_null($name) ? $repository->findBy(['worker' => $name]) : $repository->findAll();
        foreach ($entries as $entry) {
            $this->doctrine->getManager()->remove($entry);
        }
    }
}