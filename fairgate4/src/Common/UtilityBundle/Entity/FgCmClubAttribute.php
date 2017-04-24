<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCmClubAttribute
 */
class FgCmClubAttribute
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $isSetPrivacyItself;

    /**
     * @var string
     */
    private $privacyContact;

    /**
     * @var boolean
     */
    private $isConfirmContact;

    /**
     * @var boolean
     */
    private $isMandatory;

    /**
     * @var boolean
     */
    private $isEdited;

    /**
     * @var integer
     */
    private $sortOrder;

    /**
     * @var string
     */
    private $isRequiredType;

    /**
     * @var boolean
     */
    private $profileStatus;

    /**
     * @var boolean
     */
    private $isConfirmTeamadmin;

    /**
     * @var boolean
     */
    private $isRequiredFedmemberSubfed;

    /**
     * @var boolean
     */
    private $isRequiredFedmemberClub;

    /**
     * @var string
     */
    private $availabilityContact;

    /**
     * @var string
     */
    private $availabilityGroupadmin;

    /**
     * @var boolean
     */
    private $isActive;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmAttribute
     */
    private $attribute;

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
     * Set isSetPrivacyItself
     *
     * @param boolean $isSetPrivacyItself
     * @return FgCmClubAttribute
     */
    public function setIsSetPrivacyItself($isSetPrivacyItself)
    {
        $this->isSetPrivacyItself = $isSetPrivacyItself;
    
        return $this;
    }

    /**
     * Get isSetPrivacyItself
     *
     * @return boolean 
     */
    public function getIsSetPrivacyItself()
    {
        return $this->isSetPrivacyItself;
    }

    /**
     * Set privacyContact
     *
     * @param string $privacyContact
     * @return FgCmClubAttribute
     */
    public function setPrivacyContact($privacyContact)
    {
        $this->privacyContact = $privacyContact;
    
        return $this;
    }

    /**
     * Get privacyContact
     *
     * @return string 
     */
    public function getPrivacyContact()
    {
        return $this->privacyContact;
    }

    /**
     * Set isConfirmContact
     *
     * @param boolean $isConfirmContact
     * @return FgCmClubAttribute
     */
    public function setIsConfirmContact($isConfirmContact)
    {
        $this->isConfirmContact = $isConfirmContact;
    
        return $this;
    }

    /**
     * Get isConfirmContact
     *
     * @return boolean 
     */
    public function getIsConfirmContact()
    {
        return $this->isConfirmContact;
    }

    /**
     * Set isMandatory
     *
     * @param boolean $isMandatory
     * @return FgCmClubAttribute
     */
    public function setIsMandatory($isMandatory)
    {
        $this->isMandatory = $isMandatory;
    
        return $this;
    }

    /**
     * Get isMandatory
     *
     * @return boolean 
     */
    public function getIsMandatory()
    {
        return $this->isMandatory;
    }

    /**
     * Set isEdited
     *
     * @param boolean $isEdited
     * @return FgCmClubAttribute
     */
    public function setIsEdited($isEdited)
    {
        $this->isEdited = $isEdited;
    
        return $this;
    }

    /**
     * Get isEdited
     *
     * @return boolean 
     */
    public function getIsEdited()
    {
        return $this->isEdited;
    }

    /**
     * Set sortOrder
     *
     * @param integer $sortOrder
     * @return FgCmClubAttribute
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;
    
        return $this;
    }

    /**
     * Get sortOrder
     *
     * @return integer 
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * Set isRequiredType
     *
     * @param string $isRequiredType
     * @return FgCmClubAttribute
     */
    public function setIsRequiredType($isRequiredType)
    {
        $this->isRequiredType = $isRequiredType;
    
        return $this;
    }

    /**
     * Get isRequiredType
     *
     * @return string 
     */
    public function getIsRequiredType()
    {
        return $this->isRequiredType;
    }

    /**
     * Set profileStatus
     *
     * @param boolean $profileStatus
     * @return FgCmClubAttribute
     */
    public function setProfileStatus($profileStatus)
    {
        $this->profileStatus = $profileStatus;
    
        return $this;
    }

    /**
     * Get profileStatus
     *
     * @return boolean 
     */
    public function getProfileStatus()
    {
        return $this->profileStatus;
    }

    /**
     * Set isConfirmTeamadmin
     *
     * @param boolean $isConfirmTeamadmin
     * @return FgCmClubAttribute
     */
    public function setIsConfirmTeamadmin($isConfirmTeamadmin)
    {
        $this->isConfirmTeamadmin = $isConfirmTeamadmin;
    
        return $this;
    }

    /**
     * Get isConfirmTeamadmin
     *
     * @return boolean 
     */
    public function getIsConfirmTeamadmin()
    {
        return $this->isConfirmTeamadmin;
    }

    /**
     * Set isRequiredFedmemberSubfed
     *
     * @param boolean $isRequiredFedmemberSubfed
     * @return FgCmClubAttribute
     */
    public function setIsRequiredFedmemberSubfed($isRequiredFedmemberSubfed)
    {
        $this->isRequiredFedmemberSubfed = $isRequiredFedmemberSubfed;
    
        return $this;
    }

    /**
     * Get isRequiredFedmemberSubfed
     *
     * @return boolean 
     */
    public function getIsRequiredFedmemberSubfed()
    {
        return $this->isRequiredFedmemberSubfed;
    }

    /**
     * Set isRequiredFedmemberClub
     *
     * @param boolean $isRequiredFedmemberClub
     * @return FgCmClubAttribute
     */
    public function setIsRequiredFedmemberClub($isRequiredFedmemberClub)
    {
        $this->isRequiredFedmemberClub = $isRequiredFedmemberClub;
    
        return $this;
    }

    /**
     * Get isRequiredFedmemberClub
     *
     * @return boolean 
     */
    public function getIsRequiredFedmemberClub()
    {
        return $this->isRequiredFedmemberClub;
    }

    /**
     * Set availabilityContact
     *
     * @param string $availabilityContact
     * @return FgCmClubAttribute
     */
    public function setAvailabilityContact($availabilityContact)
    {
        $this->availabilityContact = $availabilityContact;
    
        return $this;
    }

    /**
     * Get availabilityContact
     *
     * @return string 
     */
    public function getAvailabilityContact()
    {
        return $this->availabilityContact;
    }

    /**
     * Set availabilityGroupadmin
     *
     * @param string $availabilityGroupadmin
     * @return FgCmClubAttribute
     */
    public function setAvailabilityGroupadmin($availabilityGroupadmin)
    {
        $this->availabilityGroupadmin = $availabilityGroupadmin;
    
        return $this;
    }

    /**
     * Get availabilityGroupadmin
     *
     * @return string 
     */
    public function getAvailabilityGroupadmin()
    {
        return $this->availabilityGroupadmin;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return FgCmClubAttribute
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    
        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean 
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set attribute
     *
     * @param \Common\UtilityBundle\Entity\FgCmAttribute $attribute
     * @return FgCmClubAttribute
     */
    public function setAttribute(\Common\UtilityBundle\Entity\FgCmAttribute $attribute = null)
    {
        $this->attribute = $attribute;
    
        return $this;
    }

    /**
     * Get attribute
     *
     * @return \Common\UtilityBundle\Entity\FgCmAttribute 
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     * @return FgCmClubAttribute
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
