<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgCmChangeEmail
 */
class FgCmChangeEmail
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
     * @var integer
     */
    private $changeInterval;

    /**
     * @var \DateTime
     */
    private $lastSend;

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
     * Set contactId
     *
     * @param integer $contactId
     *
     * @return FgCmChangeEmail
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
     * Set changeInterval
     *
     * @param integer $changeInterval
     *
     * @return FgCmChangeEmail
     */
    public function setChangeInterval($changeInterval)
    {
        $this->changeInterval = $changeInterval;

        return $this;
    }

    /**
     * Get changeInterval
     *
     * @return integer
     */
    public function getChangeInterval()
    {
        return $this->changeInterval;
    }

    /**
     * Set lastSend
     *
     * @param \DateTime $lastSend
     *
     * @return FgCmChangeEmail
     */
    public function setLastSend($lastSend)
    {
        $this->lastSend = $lastSend;

        return $this;
    }

    /**
     * Get lastSend
     *
     * @return \DateTime
     */
    public function getLastSend()
    {
        return $this->lastSend;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     *
     * @return FgCmChangeEmail
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
}

