<?php

namespace DevGarden\simpleq\QueueBundle\Service;

use DevGarden\simpleq\QueueBundle\Process\CreateDoctrineEntityProcess;
use DevGarden\simpleq\SimpleqBundle\Service\ConfigProvider;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectRepository;

class QueueProvider
{
    const QUEUE_REPOSITORY = 'QueueBundle';

    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    /**
     * @var CreateDoctrineEntityProcess
     */
    protected $entityProcess;

    /**
     * @var ManagerRegistry
     */
    protected $doctrine;

    /**
     * @var array
     */
    protected $repositoryCache;

    /**
     * @param ConfigProvider $config
     * @param CreateDoctrineEntityProcess $entityProcess
     * @param ManagerRegistry $doctrine
     */
    public function __construct(
        ConfigProvider $config,
        CreateDoctrineEntityProcess $entityProcess,
        ManagerRegistry $doctrine
    ) {
        $this->configProvider = $config;
        $this->entityProcess = $entityProcess;
        $this->doctrine = $doctrine;
        try {
            $queues = $this->configProvider->getQueueList();
            foreach ($queues as $queue) {
                $this->loadRepository($queue);
            }
        } catch (\Exception $e) {
            // repository may not be generated here
        }
        $this->doctrine->getConnection()->getConfiguration()->setSQLLogger(null);
    }

    /**
     * @param string $name
     * @throws \Exception
     */
    public function generateQueue($name)
    {
        if (!$this->configProvider->getQueue($name)) {
            throw new \Exception(
                sprintf(
                    'Queue %s is undefined, defined queues are [\'%s\']',
                    $name,
                    implode("','", $this->configProvider->getQueueList())
                )
            );
        }
        $txt = <<<'txt'
<?php
namespace DevGarden\simpleq\QueueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="%s")
 */
class %s
{
   /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    protected $task;

    /**
     * @ORM\Column(type="string", length=16)
     */
    protected $status;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $data;

    /**
     * @var \DateTime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @var \DateTime $updated
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    protected $updated;
}

txt;
        file_put_contents(__DIR__ . '/../Entity/' . ucfirst($name) . '.php', sprintf($txt, $name, ucfirst($name)));
        $this->entityProcess->execute('DevGarden/simpleq/QueueBundle/Entity');
    }

    /**
     * @param $queue
     * @param mixed|string|array $task
     * @return array
     */
    public function getQueueEntries($queue, $task = null)
    {
        $repository = $this->loadRepository($queue);
        if (is_null($task)) {
            return $repository->findAll();
        }
        if (is_array($task)) {
            return $this->getQueueEntriesXOr($queue, $task);
        }

        return $repository->findBy(['task' => $task]);
    }

    /**
     * @param string $queue
     * @param mixed|string|array $task
     * @return array|object
     */
    public function getNextOpenQueueEntry($queue, $task = null)
    {
        $repository = $this->loadRepository($queue);
        if (is_null($task)) {
            return $repository->findBy(['status' => 'open'], ['created' => 'ASC'], 1);
        }
        if (is_array($task)) {
            return $this->getQueueEntriesXOr($queue, $task);
        }

        return $repository->findOneBy(['task' => $task]);
    }

    /**
     * @param string $queue
     * @param string $tasks
     * @return array
     */
    public function getQueueEntriesXOr($queue, $tasks)
    {
        $repository = $this->loadRepository($queue);

        return $repository->findAllJobsByQueueForTasks($queue, $tasks);

    }

    /**
     * @return array
     */
    public function getQueues()
    {
        return $this->configProvider->getQueueList();
    }

    /**
     * @param string $queue
     */
    public function clearQueue($queue)
    {
        $repository = $this->loadRepository($queue);
        $em = $this->doctrine->getManager();
        $entriesToDelete = $repository->findAll();
        foreach ($entriesToDelete as $entryToDelete) {
            $entryToDelete = $em->merge($entryToDelete);
            $em->remove($entryToDelete);
        }
        $em->flush();
    }

    /**
     * @param string $queue
     * @param int $id
     */
    public function removeQueueEntry($queue, $id)
    {
        $repository = $this->loadRepository($queue);
        $em = $this->doctrine->getManager();
        $entity = $repository->findOneBy(['id' => $id]);
        $entity = $em->merge($entity);
        $em->remove($entity);
        $em->flush();
    }

    /**
     * @param string $queue
     * @param int $id
     * @param array $args
     */
    public function updateQueueEntry($queue, $id, array $args)
    {
        $repository = $this->loadRepository($queue);
        $entry = $repository->findOneBy(['id' => $id]);
        foreach ($args as $arg => $val) {
            $fnc = sprintf('set%s', ucfirst($arg));
            $entry->$fnc($val);
        }
        $this->doctrine->getManager()->merge($entry);
        $this->doctrine->getManager()->flush();
    }

    /**
     * @param string $queue
     * @return ObjectRepository
     */
    protected function loadRepository($queue)
    {
        if (!isset($this->repositoryCache[$queue])) {
            $this->repositoryCache[$queue] = $this->doctrine->getRepository(sprintf(
                '%s:%s',
                self::QUEUE_REPOSITORY,
                ucfirst($queue)
            ));
        }

        return $this->repositoryCache[$queue];
    }

    /**
     * @return string
     */
    public function getQueueRepository()
    {
        return self::QUEUE_REPOSITORY;
    }
}