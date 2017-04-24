<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCnSms
 */
class FgCnSms
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $content;

    /**
     * @var string
     */
    private $sendingArea;

    /**
     * @var string
     */
    private $sender;

    /**
     * @var string
     */
    private $state;

    /**
     * @var string
     */
    private $sendMode;

    /**
     * @var \DateTime
     */
    private $sendDate;

    /**
     * @var string
     */
    private $contactSelection;

    /**
     * @var boolean
     */
    private $isMember;

    /**
     * @var boolean
     */
    private $isSponsor;

    /**
     * @var boolean
     */
    private $isCompany;

    /**
     * @var string
     */
    private $sponsorType;

    /**
     * @var integer
     */
    private $formerSponsorMonth;

    /**
     * @var string
     */
    private $phoneSelection;

    /**
     * @var \DateTime
     */
    private $lastUpdated;

    /**
     * @var integer
     */
    private $updatedBy;

    /**
     * @var boolean
     */
    private $step;

    /**
     * @var string
     */
    private $languageSelection;

    /**
     * @var integer
     */
    private $lastSpoolContactId;

    /**
     * @var integer
     */
    private $recepientCount;

    /**
     * @var integer
     */
    private $contactId;

    /**
     * @var integer
     */
    private $clubId;

    /**
     * @var integer
     */
    private $teamId;

    /**
     * @var boolean
     */
    private $isDeleted;

    /**
     * @var boolean
     */
    private $isVerification;

    /**
     * @var boolean
     */
    private $oppositeSelection;


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
     * Set content
     *
     * @param string $content
     * @return FgCnSms
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set sendingArea
     *
     * @param string $sendingArea
     * @return FgCnSms
     */
    public function setSendingArea($sendingArea)
    {
        $this->sendingArea = $sendingArea;

        return $this;
    }

    /**
     * Get sendingArea
     *
     * @return string
     */
    public function getSendingArea()
    {
        return $this->sendingArea;
    }

    /**
     * Set sender
     *
     * @param string $sender
     * @return FgCnSms
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

    /**
     * Set state
     *
     * @param string $state
     * @return FgCnSms
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set sendMode
     *
     * @param string $sendMode
     * @return FgCnSms
     */
    public function setSendMode($sendMode)
    {
        $this->sendMode = $sendMode;

        return $this;
    }

    /**
     * Get sendMode
     *
     * @return string
     */
    public function getSendMode()
    {
        return $this->sendMode;
    }

    /**
     * Set sendDate
     *
     * @param \DateTime $sendDate
     * @return FgCnSms
     */
    public function setSendDate($sendDate)
    {
        $this->sendDate = $sendDate;

        return $this;
    }

    /**
     * Get sendDate
     *
     * @return \DateTime
     */
    public function getSendDate()
    {
        return $this->sendDate;
    }

    /**
     * Set contactSelection
     *
     * @param string $contactSelection
     * @return FgCnSms
     */
    public function setContactSelection($contactSelection)
    {
        $this->contactSelection = $contactSelection;

        return $this;
    }

    /**
     * Get contactSelection
     *
     * @return string
     */
    public function getContactSelection()
    {
        return $this->contactSelection;
    }

    /**
     * Set isMember
     *
     * @param boolean $isMember
     * @return FgCnSms
     */
    public function setIsMember($isMember)
    {
        $this->isMember = $isMember;

        return $this;
    }

    /**
     * Get isMember
     *
     * @return boolean
     */
    public function getIsMember()
    {
        return $this->isMember;
    }

    /**
     * Set isSponsor
     *
     * @param boolean $isSponsor
     * @return FgCnSms
     */
    public function setIsSponsor($isSponsor)
    {
        $this->isSponsor = $isSponsor;

        return $this;
    }

    /**
     * Get isSponsor
     *
     * @return boolean
     */
    public function getIsSponsor()
    {
        return $this->isSponsor;
    }

    /**
     * Set isCompany
     *
     * @param boolean $isCompany
     * @return FgCnSms
     */
    public function setIsCompany($isCompany)
    {
        $this->isCompany = $isCompany;

        return $this;
    }

    /**
     * Get isCompany
     *
     * @return boolean
     */
    public function getIsCompany()
    {
        return $this->isCompany;
    }

    /**
     * Set sponsorType
     *
     * @param string $sponsorType
     * @return FgCnSms
     */
    public function setSponsorType($sponsorType)
    {
        $this->sponsorType = $sponsorType;

        return $this;
    }

    /**
     * Get sponsorType
     *
     * @return string
     */
    public function getSponsorType()
    {
        return $this->sponsorType;
    }

    /**
     * Set formerSponsorMonth
     *
     * @param integer $formerSponsorMonth
     * @return FgCnSms
     */
    public function setFormerSponsorMonth($formerSponsorMonth)
    {
        $this->formerSponsorMonth = $formerSponsorMonth;

        return $this;
    }

    /**
     * Get formerSponsorMonth
     *
     * @return integer
     */
    public function getFormerSponsorMonth()
    {
        return $this->formerSponsorMonth;
    }

    /**
     * Set phoneSelection
     *
     * @param string $phoneSelection
     * @return FgCnSms
     */
    public function setPhoneSelection($phoneSelection)
    {
        $this->phoneSelection = $phoneSelection;

        return $this;
    }

    /**
     * Get phoneSelection
     *
     * @return string
     */
    public function getPhoneSelection()
    {
        return $this->phoneSelection;
    }

    /**
     * Set lastUpdated
     *
     * @param \DateTime $lastUpdated
     * @return FgCnSms
     */
    public function setLastUpdated($lastUpdated)
    {
        $this->lastUpdated = $lastUpdated;

        return $this;
    }

    /**
     * Get lastUpdated
     *
     * @return \DateTime
     */
    public function getLastUpdated()
    {
        return $this->lastUpdated;
    }

    /**
     * Set updatedBy
     *
     * @param integer $updatedBy
     * @return FgCnSms
     */
    public function setUpdatedBy($updatedBy)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Get updatedBy
     *
     * @return integer
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * Set step
     *
     * @param boolean $step
     * @return FgCnSms
     */
    public function setStep($step)
    {
        $this->step = $step;

        return $this;
    }

    /**
     * Get step
     *
     * @return boolean
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * Set languageSelection
     *
     * @param string $languageSelection
     * @return FgCnSms
     */
    public function setLanguageSelection($languageSelection)
    {
        $this->languageSelection = $languageSelection;

        return $this;
    }

    /**
     * Get languageSelection
     *
     * @return string
     */
    public function getLanguageSelection()
    {
        return $this->languageSelection;
    }

    /**
     * Set lastSpoolContactId
     *
     * @param integer $lastSpoolContactId
     * @return FgCnSms
     */
    public function setLastSpoolContactId($lastSpoolContactId)
    {
        $this->lastSpoolContactId = $lastSpoolContactId;

        return $this;
    }

    /**
     * Get lastSpoolContactId
     *
     * @return integer
     */
    public function getLastSpoolContactId()
    {
        return $this->lastSpoolContactId;
    }

    /**
     * Set recepientCount
     *
     * @param integer $recepientCount
     * @return FgCnSms
     */
    public function setRecepientCount($recepientCount)
    {
        $this->recepientCount = $recepientCount;

        return $this;
    }

    /**
     * Get recepientCount
     *
     * @return integer
     */
    public function getRecepientCount()
    {
        return $this->recepientCount;
    }

    /**
     * Set contactId
     *
     * @param integer $contactId
     * @return FgCnSms
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
     * Set clubId
     *
     * @param integer $clubId
     * @return FgCnSms
     */
    public function setClubId($clubId)
    {
        $this->clubId = $clubId;

        return $this;
    }

    /**
     * Get clubId
     *
     * @return integer
     */
    public function getClubId()
    {
        return $this->clubId;
    }

    /**
     * Set teamId
     *
     * @param integer $teamId
     * @return FgCnSms
     */
    public function setTeamId($teamId)
    {
        $this->teamId = $teamId;

        return $this;
    }

    /**
     * Get teamId
     *
     * @return integer
     */
    public function getTeamId()
    {
        return $this->teamId;
    }

    /**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     * @return FgCnSms
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
     * Set isVerification
     *
     * @param boolean $isVerification
     * @return FgCnSms
     */
    public function setIsVerification($isVerification)
    {
        $this->isVerification = $isVerification;

        return $this;
    }

    /**
     * Get isVerification
     *
     * @return boolean
     */
    public function getIsVerification()
    {
        return $this->isVerification;
    }

    /**
     * Set oppositeSelection
     *
     * @param boolean $oppositeSelection
     * @return FgCnSms
     */
    public function setOppositeSelection($oppositeSelection)
    {
        $this->oppositeSelection = $oppositeSelection;

        return $this;
    }

    /**
     * Get oppositeSelection
     *
     * @return boolean
     */
    public function getOppositeSelection()
    {
        return $this->oppositeSelection;
    }
}