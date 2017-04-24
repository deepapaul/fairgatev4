<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgForumContactDetails
 */
class FgForumContactDetails
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $readAt;

    /**
     * @var boolean
     */
    private $isNotificationEnabled;

    /**
     * @var \DateTime
     */
    private $lastNotificationSend;

    /**
     * @var \Common\UtilityBundle\Entity\FgForumTopic
     */
    private $forumTopic;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $contact;


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
     * Set readAt
     *
     * @param \DateTime $readAt
     *
     * @return FgForumContactDetails
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
     * Set isNotificationEnabled
     *
     * @param boolean $isNotificationEnabled
     *
     * @return FgForumContactDetails
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
     * Set lastNotificationSend
     *
     * @param \DateTime $lastNotificationSend
     *
     * @return FgForumContactDetails
     */
    public function setLastNotificationSend($lastNotificationSend)
    {
        $this->lastNotificationSend = $lastNotificationSend;

        return $this;
    }

    /**
     * Get lastNotificationSend
     *
     * @return \DateTime
     */
    public function getLastNotificationSend()
    {
        return $this->lastNotificationSend;
    }

    /**
     * Set forumTopic
     *
     * @param \Common\UtilityBundle\Entity\FgForumTopic $forumTopic
     *
     * @return FgForumContactDetails
     */
    public function setForumTopic(\Common\UtilityBundle\Entity\FgForumTopic $forumTopic = null)
    {
        $this->forumTopic = $forumTopic;

        return $this;
    }

    /**
     * Get forumTopic
     *
     * @return \Common\UtilityBundle\Entity\FgForumTopic
     */
    public function getForumTopic()
    {
        return $this->forumTopic;
    }

    /**
     * Set contact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $contact
     *
     * @return FgForumContactDetails
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
}

