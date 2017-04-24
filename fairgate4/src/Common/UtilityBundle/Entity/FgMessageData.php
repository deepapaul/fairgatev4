<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgMessageData
 */
class FgMessageData
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $message;

    /**
     * @var boolean
     */
    private $isRead;

    /**
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @var boolean
     */
    private $isDeleted;

    /**
     * @var \Common\UtilityBundle\Entity\FgMessage
     */
    private $message2;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
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
     * Set subject
     *
     * @param string $subject
     * @return FgMessageData
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set message
     *
     * @param string $message
     * @return FgMessageData
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set isRead
     *
     * @param boolean $isRead
     * @return FgMessageData
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
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return FgMessageData
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     * @return FgMessageData
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
     * Set message2
     *
     * @param \Common\UtilityBundle\Entity\FgMessage $message2
     * @return FgMessageData
     */
    public function setMessage2(\Common\UtilityBundle\Entity\FgMessage $message2 = null)
    {
        $this->message2 = $message2;

        return $this;
    }

    /**
     * Get message2
     *
     * @return \Common\UtilityBundle\Entity\FgMessage
     */
    public function getMessage2()
    {
        return $this->message2;
    }

    /**
     * Set sender
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $sender
     * @return FgMessageData
     */
    public function setSender(\Common\UtilityBundle\Entity\FgCmContact $sender = null)
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * Get sender
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact
     */
    public function getSender()
    {
        return $this->sender;
    }
}