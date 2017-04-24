<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

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
     * @var boolean
     */
    private $isRead;

    /**
     * @var \DateTime
     */
    private $readAt;

    /**
     * @var boolean
     */
    private $isDeleted;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $contact;

    /**
     * @var \Common\UtilityBundle\Entity\FgMessage
     */
    private $message;

    /**
     * @var \Common\UtilityBundle\Entity\FgRmRole
     */
    private $team;

    /**
     * @var \Common\UtilityBundle\Entity\FgRmFunction
     */
    private $function;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;


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
     * Set isRead
     *
     * @param boolean $isRead
     * @return FgMessageReceivers
     */
    public function setIsRead($isRead)
    {
        $this->isRead = $isRead;

        return $this;
    }

    /**
     * Get isRead
     *
     * @return boolean
     */
    public function getIsRead()
    {
        return $this->isRead;
    }

    /**
     * Set readAt
     *
     * @param \DateTime $readAt
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
     * Set contact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $contact
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

    /**
     * Set team
     *
     * @param \Common\UtilityBundle\Entity\FgRmRole $team
     * @return FgMessageReceivers
     */
    public function setTeam(\Common\UtilityBundle\Entity\FgRmRole $team = null)
    {
        $this->team = $team;

        return $this;
    }

    /**
     * Get team
     *
     * @return \Common\UtilityBundle\Entity\FgRmRole
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * Set function
     *
     * @param \Common\UtilityBundle\Entity\FgRmFunction $function
     * @return FgMessageReceivers
     */
    public function setFunction(\Common\UtilityBundle\Entity\FgRmFunction $function = null)
    {
        $this->function = $function;

        return $this;
    }

    /**
     * Get function
     *
     * @return \Common\UtilityBundle\Entity\FgRmFunction
     */
    public function getFunction()
    {
        return $this->function;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     * @return FgMessageReceivers
     */
    public function setClub(\Common\UtilityBundle\Entity\FgClub $club = null)
    {
        $this->club = $club;

        return $this;
    }

    /**
     * Get club
     *
     * @return \Common\UtilityBundle\Entity\FgClub
     */
    public function getClub()
    {
        return $this->club;
    }
    /**
     * @var integer
     */
    private $unreadCount;

    /**
     * @var boolean
     */
    private $isNotificationEnabled;


    /**
     * Set unreadCount
     *
     * @param integer $unreadCount
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
     * Set isNotificationEnabled
     *
     * @param boolean $isNotificationEnabled
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
}