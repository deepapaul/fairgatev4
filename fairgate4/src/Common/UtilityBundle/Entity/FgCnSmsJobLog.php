<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCnSmsJobLog
 */
class FgCnSmsJobLog
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $jobId;

    /**
     * @var integer
     */
    private $smsId;

    /**
     * @var string
     */
    private $statusMessage;

    /**
     * @var \DateTime
     */
    private $date;


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
     * Set jobId
     *
     * @param string $jobId
     * @return FgCnSmsJobLog
     */
    public function setJobId($jobId)
    {
        $this->jobId = $jobId;

        return $this;
    }

    /**
     * Get jobId
     *
     * @return string
     */
    public function getJobId()
    {
        return $this->jobId;
    }

    /**
     * Set smsId
     *
     * @param integer $smsId
     * @return FgCnSmsJobLog
     */
    public function setSmsId($smsId)
    {
        $this->smsId = $smsId;

        return $this;
    }

    /**
     * Get smsId
     *
     * @return integer
     */
    public function getSmsId()
    {
        return $this->smsId;
    }

    /**
     * Set statusMessage
     *
     * @param string $statusMessage
     * @return FgCnSmsJobLog
     */
    public function setStatusMessage($statusMessage)
    {
        $this->statusMessage = $statusMessage;

        return $this;
    }

    /**
     * Get statusMessage
     *
     * @return string
     */
    public function getStatusMessage()
    {
        return $this->statusMessage;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return FgCnSmsJobLog
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }
}