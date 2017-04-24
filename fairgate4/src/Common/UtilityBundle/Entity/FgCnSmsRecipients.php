<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCnSmsRecipients
 */
class FgCnSmsRecipients
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $contactId;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var integer
     */
    private $smsId;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $statusMessage;

    /**
     * @var string
     */
    private $actualSmsContent;

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
    private $fieldName;


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
     * Set contactId
     *
     * @param integer $contactId
     * @return FgCnSmsRecipients
     */
    public function setContactId($contactId)
    {
        $this->contactId = $contactId;

        return $this;
    }

    /**
     * Get contactId
     *
     * @return integer
     */
    public function getContactId()
    {
        return $this->contactId;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return FgCnSmsRecipients
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
     * Set smsId
     *
     * @param integer $smsId
     * @return FgCnSmsRecipients
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
     * Set status
     *
     * @param string $status
     * @return FgCnSmsRecipients
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set statusMessage
     *
     * @param string $statusMessage
     * @return FgCnSmsRecipients
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
     * Set actualSmsContent
     *
     * @param string $actualSmsContent
     * @return FgCnSmsRecipients
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
     * Set jobId
     *
     * @param string $jobId
     * @return FgCnSmsRecipients
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
     * @return FgCnSmsRecipients
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
     * Set fieldName
     *
     * @param string $fieldName
     * @return FgCnSmsRecipients
     */
    public function setFieldName($fieldName)
    {
        $this->fieldName = $fieldName;

        return $this;
    }

    /**
     * Get fieldName
     *
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }
}