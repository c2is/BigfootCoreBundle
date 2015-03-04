<?php

namespace Bigfoot\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Process
 * @package Bigfoot\Bundle\CoreBundle\Entity
 *
 * @ORM\Table(name="bigfoot_process", indexes={@ORM\Index(name="bigfoot_process_status_index", columns={"status"})})
 * @ORM\Entity(repositoryClass="Bigfoot\Bundle\CoreBundle\Entity\ProcessRepository")
 * @Serializer\AccessType("public_method")
 */
class Process
{
    const STATUS_ONGOING = 1;
    const STATUS_SUCCESS = 2;
    const STATUS_ERROR = 3;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=255, nullable=true)
     */
    private $token;

    /**
     * @var string
     *
     * @ORM\Column(name="current_task", type="string", length=255, nullable=true)
     * @Serializer\SerializedName("currentTask")
     */
    private $currentTask;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="started_at", type="datetime", nullable=true)
     * @Serializer\SerializedName("startedAt")
     */
    private $startedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="ended_at", type="datetime", nullable=true)
     * @Serializer\SerializedName("endedAt")
     */
    private $endedAt;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer", nullable=true)
     */
    private $status = self::STATUS_ONGOING;

    /**
     * @var integer
     *
     * @ORM\Column(name="progress", type="integer", nullable=true)
     */
    private $progress = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="step", type="integer", nullable=true)
     */
    private $step = 1;

    /**
     * @var integer
     *
     * @ORM\Column(name="goal", type="integer", nullable=true)
     */
    private $goal;

    /**
     * @var string
     *
     * @ORM\Column(name="logs", type="text", nullable=true)
     */
    private $logs;

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $token
     * @return $this
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $currentTask
     * @return $this
     */
    public function setCurrentTask($currentTask)
    {
        $this->currentTask = $currentTask;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrentTask()
    {
        return $this->currentTask;
    }

    /**
     * @param \DateTime $startedAt
     * @return $this
     */
    public function setStartedAt($startedAt)
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getStartedAt()
    {
        return $this->startedAt;
    }

    /**
     * @param \DateTime $endedAt
     * @return $this
     */
    public function setEndedAt($endedAt)
    {
        $this->endedAt = $endedAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEndedAt()
    {
        return $this->endedAt;
    }

    /**
     * @param int $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $progress
     * @return $this
     */
    public function setProgress($progress)
    {
        $this->progress = $progress;

        return $this;
    }

    /**
     * @return int
     */
    public function getProgress()
    {
        return $this->progress;
    }

    /**
     * @param int $step
     * @return $this
     */
    public function setStep($step)
    {
        $this->step = $step;

        return $this;
    }

    /**
     * @return int
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * @param int $goal
     * @return $this
     */
    public function setGoal($goal)
    {
        $this->goal = $goal;

        return $this;
    }

    /**
     * @return int
     */
    public function getGoal()
    {
        return $this->goal;
    }

    /**
     * @param string $logs
     * @return $this
     */
    public function setLogs($logs)
    {
        $this->logs = $logs;

        return $this;
    }

    /**
     * @return string
     */
    public function getLogs()
    {
        return $this->logs;
    }

    /**
     * @return float
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("completionPercentage")
     */
    public function getCompletionPercentage()
    {
        return round($this->progress * 100 / $this->goal);
    }

    /**
     * @return $this
     */
    public function incrementProgress()
    {
        $this->progress += $this->step;

        return $this;
    }

    /**
     * @param $string
     * @return $this
     */
    public function addLog($string)
    {
        $this->logs .= rtrim($string) . "\n";

        return $this;
    }

    /**
     * @param bool $success
     * @return $this
     */
    public function terminate($success = true)
    {
        $this->progress = $this->goal;
        $this->status = $success ? self::STATUS_SUCCESS : self::STATUS_ERROR;
        $this->endedAt = new \DateTime();

        return $this;
    }
}
