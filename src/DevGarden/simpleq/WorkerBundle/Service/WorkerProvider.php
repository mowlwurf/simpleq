<?php

namespace DevGarden\simpleq\WorkerBundle\Service;

use DevGarden\simpleq\SchedulerBundle\Entity\WorkingQueue;
use DevGarden\simpleq\SimpleqBundle\Service\ConfigProvider;
use DevGarden\simpleq\WorkerBundle\Extension\WorkerStatus;
use Doctrine\Common\Persistence\ManagerRegistry;

class WorkerProvider
{
    /**
     * @var ConfigProvider
     */
    protected $config;

    /**
     * @var ManagerRegistry
     */
    protected $doctrine;

    /**
     * @param ConfigProvider $config
     * @param ManagerRegistry $doctrine
     */
    public function __construct(ConfigProvider $config, ManagerRegistry $doctrine){
        $this->config = $config;
        $this->doctrine = $doctrine;
    }

    /**
     * @return array
     */
    public function getRegisteredWorkers(){
        return $this->config->getWorkerList();
    }

    /**
     * @param string $name
     * @return array
     */
    public function getActiveWorkers($name = null){
        $repository = $this->doctrine->getRepository('SchedulerBundle:WorkingQueue');
        return is_null($name) ? $repository->findAll() : $repository->findBy(['worker' => $name]);
    }

    /**
     * @param int $pid
     * @return WorkingQueue
     */
    public function getWorkingQueueEntryByPid($pid){
        $repository = $this->doctrine->getRepository('SchedulerBundle:WorkingQueue');
        return $repository->findOneBy(['pid' => $pid]);
    }

    /**
     * @param string $name
     */
    public function clearQueue($name = null){
        $repository = $this->doctrine->getRepository('SchedulerBundle:WorkingQueue');
        $em = $this->doctrine->getManager();
        $entriesToDelete = is_null($name) ? $repository->findAll() : $repository->findBy(['worker' => $name]);
        foreach ($entriesToDelete as $entryToDelete) {
            $em->remove($entryToDelete);
        }
        $em->flush();
    }

    /**
     * @param int $pid
     * @param string $workerService
     */
    public function pushWorkerToWorkingQueue($pid, $workerService){
        $worker = new WorkingQueue();
        $worker->setPid($pid);
        $worker->setStatus(WorkerStatus::WORKER_STATUS_OPEN_CODE);
        $worker->setWorker($workerService);
        $worker->setCreated(new \DateTime());
        $worker->setUpdated(new \DateTime());
        $this->doctrine->getManager()->persist($worker);
        $this->doctrine->getManager()->flush();
    }

    /***
     * @param int $pid
     */
    public function removeWorkingQueueEntry($pid){
        $repository = $this->doctrine->getRepository('SchedulerBundle:WorkingQueue');
        $em = $this->doctrine->getManager();
        $em->remove($repository->findOneBy(['pid' => $pid]));
        $em->flush();
    }

    /**
     * @param $pid
     * @param $status
     */
    public function pushWorkerStatus($pid, $status){
        $worker = $this->getWorkingQueueEntryByPid($pid);
        $worker->setStatus($status);
        $this->doctrine->getManager()->persist($worker);
        $this->doctrine->getManager()->flush();
    }

    /**
     * @param $id
     * @return bool
     */
    public function getWorkerQueue($id){
        return $this->config->getQueueByWorkerService($id);
    }
}