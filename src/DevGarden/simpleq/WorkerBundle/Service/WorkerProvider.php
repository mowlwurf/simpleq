<?php

namespace DevGarden\simpleq\WorkerBundle\Service;

use DevGarden\simpleq\SchedulerBundle\Entity\WorkingQueue;
use DevGarden\simpleq\SimpleqBundle\Service\ConfigProvider;
use DevGarden\simpleq\WorkerBundle\Extension\WorkerStatus;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectRepository;

class WorkerProvider
{
    const SCHEDULER_REPOSITORY = 'SchedulerBundle';
    const SCHEDULER_WORKING_QUEUE = 'WorkingQueue';

    /**
     * @var ConfigProvider
     */
    protected $config;

    /**
     * @var ManagerRegistry
     */
    protected $doctrine;

    /**
     * @var ObjectRepository
     */
    protected $repository;

    /**
     * @param ConfigProvider $config
     * @param ManagerRegistry $doctrine
     */
    public function __construct(ConfigProvider $config, ManagerRegistry $doctrine)
    {
        $this->config = $config;
        $this->doctrine = $doctrine;
        $this->repository = $this->doctrine->getRepository(sprintf(
            '%s:%s',
            self::SCHEDULER_REPOSITORY,
            self::SCHEDULER_WORKING_QUEUE
        ));
        $this->doctrine->getConnection()->getConfiguration()->setSQLLogger(null);
    }

    /**
     * @return array
     */
    public function getRegisteredWorkers()
    {
        return $this->config->getWorkerList();
    }

    /**
     * @param string $name
     * @return array
     */
    public function getActiveWorkers($name = null)
    {
        return is_null($name) ? $this->repository->findAll() : $this->repository->findBy(['worker' => $name]);
    }

    /**
     * @param int $pid
     * @return WorkingQueue
     */
    public function getWorkingQueueEntryByPid($pid)
    {
        return $this->repository->findOneBy(['pid' => $pid]);
    }

    /**
     * @param string $name
     */
    public function clearQueue($name = null)
    {
        $em = $this->doctrine->getManager();
        $entriesToDelete = is_null($name) ? $this->repository->findAll() : $this->repository->findBy(['worker' => $name]);
        foreach ($entriesToDelete as $entryToDelete) {
            $em->remove($entryToDelete);
        }
        $em->flush();
    }

    /**
     * @param string $workerService
     * @return string $tempPid
     */
    public function pushWorkerToWorkingQueue($workerService)
    {
        $tempPid = md5(rand(10000, 999999) . microtime() . $workerService);
        $worker = new WorkingQueue();
        $worker->setPid($tempPid);
        $worker->setStatus(WorkerStatus::WORKER_STATUS_OPEN_CODE);
        $worker->setWorker($workerService);
        $worker->setCreated(new \DateTime());
        $worker->setUpdated(new \DateTime());
        $this->doctrine->getManager()->persist($worker);
        $this->doctrine->getManager()->flush();

        return $tempPid;
    }

    /**
     * @param string $tempPid
     * @param int $pid
     */
    public function updateWorkerPid($tempPid, $pid)
    {
        $entry = $this->repository->findOneBy(['pid' => $tempPid]);
        $entry->setPid($pid);
        $this->doctrine->getManager()->merge($entry);
        $this->doctrine->getManager()->flush();
    }

    /***
     * @param int $pid
     */
    public function removeWorkingQueueEntry($pid)
    {
        $em = $this->doctrine->getManager();
        $entity = $this->repository->findOneBy(['pid' => $pid]);
        $entity = $em->merge($entity);
        $em->remove($entity);
        $em->flush();
    }

    /**
     * @param int $pid
     * @param int $status
     */
    public function pushWorkerStatus($pid, $status)
    {
        $worker = $this->getWorkingQueueEntryByPid($pid);
        $worker->setStatus($status);
        $this->doctrine->getManager()->merge($worker);
        $this->doctrine->getManager()->flush();
    }

    /**
     * @param string $id
     * @return bool|string
     */
    public function getWorkerQueue($id)
    {
        return $this->config->getQueueByWorkerService($id);
    }
}