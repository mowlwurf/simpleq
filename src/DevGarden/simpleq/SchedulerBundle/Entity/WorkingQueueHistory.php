<?php

namespace DevGarden\simpleq\SchedulerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="working_queue_history")
 */
class WorkingQueueHistory
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $status;

    /**
     * @ORM\Column(type="integer")
     */
    protected $pid;

    /**
     * @ORM\Column(type="string", length=128)
     */
    protected $worker;

    /**
     * @var \DateTime $created
     *
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @var \DateTime $updated
     *
     * @ORM\Column(type="datetime")
     */
    protected $updated;


    /**
     * @var \DateTime $archived
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $archived;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return WorkingQueueHistory
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get pid
     *
     * @return integer
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * Set pid
     *
     * @param integer $pid
     * @return WorkingQueueHistory
     */
    public function setPid($pid)
    {
        $this->pid = $pid;

        return $this;
    }

    /**
     * Get worker
     *
     * @return string
     */
    public function getWorker()
    {
        return $this->worker;
    }

    /**
     * Set worker
     *
     * @param string $worker
     * @return WorkingQueueHistory
     */
    public function setWorker($worker)
    {
        $this->worker = $worker;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return WorkingQueueHistory
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return WorkingQueueHistory
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get archived
     *
     * @return \DateTime
     */
    public function getArchived()
    {
        return $this->archived;
    }

    /**
     * Set archived
     *
     * @param \DateTime $archived
     * @return WorkingQueueHistory
     */
    public function setArchived($archived)
    {
        $this->archived = $archived;

        return $this;
    }
}
