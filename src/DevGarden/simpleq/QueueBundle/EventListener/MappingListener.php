<?php

namespace DevGarden\simpleq\QueueBundle\EventListener;


use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;

class MappingListener
{
    /**
     * @var LoadClassMetadataEventArgs
     */
    protected $eventArgs;

    /**
     * @var string
     */
    protected $name;

    /**
     * @param $name
     */
    public function __construct($name = null){
        $this->name = $name;
    }

    /**
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $this->eventArgs = $eventArgs;
        $classMetaData   = $this->getClassMetaData();
        $classMetaData->setPrimaryTable(['name' => sprintf('%s_%s', $classMetaData->getTableName(), $this->name)]);
    }

    /**
     * @return ClassMetadata
     */
    protected function getClassMetaData(){
        return $this->eventArgs->getClassMetadata();
    }
}