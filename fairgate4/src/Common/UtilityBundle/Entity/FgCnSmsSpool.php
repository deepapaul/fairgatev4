<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCnSmsSpool
 */
class FgCnSmsSpool
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var string
     */
    private $actualSmsContent;

    /**
     * @var integer
     */
    private $smsId;

    /**
     * @var string
     */
    private $jobId;

    /**
     * @var string
     */
    private $phoneNumber;

    /**
     * @var string
     */
    private $sender;


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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return FgCnSmsSpool
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set actualSmsContent
     *
     * @param string $actualSmsContent
     * @return FgCnSmsSpool
     */
    public function setActualSmsContent($actualSmsContent)
    {
        $this->actualSmsContent = $actualSmsContent;

        return $this;
    }

    /**
     * Get actualSmsContent
     *
     * @return string
     */
    public function getActualSmsContent()
    {
        return $this->actualSmsContent;
    }

    /**
     * Set smsId
     *
     * @param integer $smsId
     * @return FgCnSmsSpool
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
     * Set jobId
     *
     * @param string $jobId
     * @return FgCnSmsSpool
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
     * Set phoneNumber
     *
     * @param string $phoneNumber
     * @return FgCnSmsSpool
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * Get phoneNumber
     *
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Set sender
     *
     * @param string $sender
     * @return FgCnSmsSpool
     */
    public function setSender($sender)
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * Get sender
     *
     * @return string
     */
    public function getSender()
    {
        return $this->sender;
    }
}