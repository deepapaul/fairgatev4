<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgMessageReceivers
 */
class FgMessageReceivers
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $unreadCount;

    /**
     * @var \DateTime
     */
    private $readAt;

    /**
     * @var boolean
     */
    private $isDeleted;

    /**
     * @var boolean
     */
    private $isNotificationEnabled;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $contact;

    /**
     * @var \Common\UtilityBundle\Entity\FgMessage
     */
    private $message;


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
     * Set unreadCount
     *
     * @param integer $unreadCount
     *
     * @return FgMessageReceivers
     */
    public function setUnreadCount($unreadCount)
    {
        $this->unreadCount = $unreadCount;

        return $this;
    }

    /**
     * Get unreadCount
     *
     * @return integer
     */
    public function getUnreadCount()
    {
        return $this->unreadCount;
    }

    /**
     * Set readAt
     *
     * @param \DateTime $readAt
     *
     * @return FgMessageReceivers
     */
    public function setReadAt($readAt)
    {
        $this->readAt = $readAt;

        return $this;
    }

    /**
     * Get readAt
     *
     * @return \DateTime
     */
    public function getReadAt()
    {
        return $this->readAt;
    }

    /**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return FgMessageReceivers
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /**
     * Get isDeleted
     *
     * @return boolean
     */
    public function getIsDeleted()
    {
        return $this->isDeleted;
    }

    /**
     * Set isNotificationEnabled
     *
     * @param boolean $isNotificationEnabled
     *
     * @return FgMessageReceivers
     */
    public function setIsNotificationEnabled($isNotificationEnabled)
    {
        $this->isNotificationEnabled = $isNotificationEnabled;

        return $this;
    }

    /**
     * Get isNotificationEnabled
     *
     * @return boolean
     */
    public function getIsNotificationEnabled()
    {
        return $this->isNotificationEnabled;
    }

    /**
     * Set contact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $contact
     *
     * @return FgMessageReceivers
     */
    public function setContact(\Common\UtilityBundle\Entity\FgCmContact $contact = null)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Get contact
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Set message
     *
     * @param \Common\UtilityBundle\Entity\FgMessage $message
     *
     * @return FgMessageReceivers
     */
    public function setMessage(\Common\UtilityBundle\Entity\FgMessage $message = null)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return \Common\UtilityBundle\Entity\FgMessage
     */
    public function getMessage()
    {
        return $this->message;
    }
}

