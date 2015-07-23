<?php

namespace DevGarden\simpleq\QueueBundle\Service;

use DevGarden\simpleq\QueueBundle\Process\CreateDoctrineEntityProcess;
use DevGarden\simpleq\SimpleqBundle\Service\ConfigProvider;
use Doctrine\Common\Persistence\ManagerRegistry;

class QueueProvider
{
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
     * @ORM\Column(type="string", length=32)
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
     * @param $name
     * @param mixed|string|array $task
     * @return array
     */
    public function getQueueEntries($name, $task = null)
    {
        $repository = $this->doctrine->getRepository(sprintf('QueueBundle:%s', ucfirst($name)));
        if (is_null($task)) {
            return $repository->findAll();
        }
        if (is_array($task)) {
            return $this->getQueueEntriesXOr($name, $task);
        }

        return $repository->findBy(['task' => $task]);
    }

    /**
     * @param $name
     * @param null $task
     * @return array|object
     */
    public function getNextOpenQueueEntry($name, $task = null)
    {
        $repository = $this->doctrine->getRepository(sprintf('QueueBundle:%s', ucfirst($name)));
        if (is_null($task)) {
            return $repository->findBy(['status' => 'open'], ['created' => 'ASC'], 1);
        }
        if (is_array($task)) {
            return $this->getQueueEntriesXOr($name, $task);
        }

        return $repository->findOneBy(['task' => $task]);
    }

    /**
     * @param $name
     * @param $tasks
     * @return array
     */
    public function getQueueEntriesXOr($name, $tasks)
    {
        $repository = $this->doctrine->getRepository(sprintf('QueueBundle:%s', ucfirst($name)));

        return $repository->findAllJobsByQueueForTasks($name, $tasks);

    }

    /**
     * @return array
     */
    public function getQueues()
    {
        return $this->configProvider->getQueueList();
    }

    /**
     * @param $name
     */
    public function clearQueue($name)
    {
        $repository = $this->doctrine->getRepository(sprintf('QueueBundle:%s', ucfirst($name)));
        $em = $this->doctrine->getManager();
        $entriesToDelete = $repository->findAll();
        foreach ($entriesToDelete as $entryToDelete) {
            $em->remove($entryToDelete);
        }
        $em->flush();
    }

    /**
     * @param $queue
     * @param $id
     */
    public function removeQueueEntry($queue, $id)
    {
        $repository = $this->doctrine->getRepository(sprintf('QueueBundle:%s', ucfirst($queue)));
        $em = $this->doctrine->getManager();
        $em->remove($repository->findOneBy(['id' => $id]));
        $em->flush();
    }

    /**
     * @param string $queue
     * @param int $id
     * @param array $args
     */
    public function updateQueueEntry($queue, $id, array $args)
    {
        $repository = $this->doctrine->getRepository(sprintf('QueueBundle:%s', ucfirst($queue)));
        $entry = $repository->findOneBy(['id' => $id]);
        foreach ($args as $arg => $val) {
            $fnc = sprintf('set%s', ucfirst($arg));
            $entry->$fnc($val);
        }
        $this->doctrine->getManager()->persist($entry);
        $this->doctrine->getManager()->flush();
    }
}