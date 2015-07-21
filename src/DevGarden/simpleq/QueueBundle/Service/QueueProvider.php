<?php

namespace DevGarden\simpleq\QueueBundle\Service;

use DevGarden\simpleq\QueueBundle\Entity\Demoqueue;
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
    ){
        $this->configProvider = $config;
        $this->entityProcess  = $entityProcess;
        $this->doctrine       = $doctrine;
    }

    /**
     * @param string $name
     * @throws \Exception
     */
    public function generateQueue($name){
        $queue = $this->configProvider->getQueue($name);
        if (!$queue) {
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
        file_put_contents(__DIR__ . '/../Entity/'.ucfirst($name).'.php',sprintf($txt, $name, ucfirst($name)));
        $this->entityProcess->execute('DevGarden/simpleq/QueueBundle/Entity');
    }

    /**
     * @return array
     */
    public function getQueueEntries($name){
        $repository = $this->doctrine->getRepository(sprintf('QueueBundle:%s', ucfirst($name)));
        return $repository->findAll();
    }

    /**
     * @return array
     */
    public function getQueues(){
        return $this->configProvider->getQueueList();
    }

    /**
     * @param $name
     */
    public function clearQueue($name){
        $repository = $this->doctrine->getRepository(sprintf('QueueBundle:%s', ucfirst($name)));
        $em = $this->doctrine->getManager();
        $entriesToDelete = $repository->findAll();
        foreach ($entriesToDelete as $entryToDelete) {
            $em->remove($entryToDelete);
        }
        $em->flush();
    }

    public function demoPersist(){
        $product = new DemoQueue();
        $product->setTask('test');
        $product->setStatus('open');
        $product->setCreated(new \DateTime());
        $product->setUpdated(new \DateTime());
        $this->doctrine->persist($product);
        $this->doctrine->flush();
    }
}