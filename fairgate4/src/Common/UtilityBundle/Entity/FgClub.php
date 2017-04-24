<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgClub
 */
class FgClub
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $parentClubId;

    /**
     * @var integer
     */
    private $federationId;

    /**
     * @var integer
     */
    private $subFederationId;

    /**
     * @var boolean
     */
    private $isFederation;

    /**
     * @var boolean
     */
    private $isSubFederation;

    /**
     * @var string
     */
    private $clubType;

    /**
     * @var boolean
     */
    private $subfedLevel;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $urlIdentifier;

    /**
     * @var boolean
     */
    private $isDeleted;

    /**
     * @var boolean
     */
    private $defaultContactSubscription;

    /**
     * @var boolean
     */
    private $hasSubfederation;

    /**
     * @var \DateTime
     */
    private $settingsUpdated;

    /**
     * @var string
     */
    private $calendarColorCode;

    /**
     * @var boolean
     */
    private $assignFedmembershipWithApplication;

    /**
     * @var boolean
     */
    private $hasNlFairgatelogo;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $contact;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $fedContact;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->contact = new \Doctrine\Common\Collections\ArrayCollection();
        $this->fedContact = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * Set parentClubId
     *
     * @param integer $parentClubId
     *
     * @return FgClub
     */
    public function setParentClubId($parentClubId)
    {
        $this->parentClubId = $parentClubId;

        return $this;
    }

    /**
     * Get parentClubId
     *
     * @return integer
     */
    public function getParentClubId()
    {
        return $this->parentClubId;
    }

    /**
     * Set federationId
     *
     * @param integer $federationId
     *
     * @return FgClub
     */
    public function setFederationId($federationId)
    {
        $this->federationId = $federationId;

        return $this;
    }

    /**
     * Get federationId
     *
     * @return integer
     */
    public function getFederationId()
    {
        return $this->federationId;
    }

    /**
     * Set subFederationId
     *
     * @param integer $subFederationId
     *
     * @return FgClub
     */
    public function setSubFederationId($subFederationId)
    {
        $this->subFederationId = $subFederationId;

        return $this;
    }

    /**
     * Get subFederationId
     *
     * @return integer
     */
    public function getSubFederationId()
    {
        return $this->subFederationId;
    }

    /**
     * Set isFederation
     *
     * @param boolean $isFederation
     *
     * @return FgClub
     */
    public function setIsFederation($isFederation)
    {
        $this->isFederation = $isFederation;

        return $this;
    }

    /**
     * Get isFederation
     *
     * @return boolean
     */
    public function getIsFederation()
    {
        return $this->isFederation;
    }

    /**
     * Set isSubFederation
     *
     * @param boolean $isSubFederation
     *
     * @return FgClub
     */
    public function setIsSubFederation($isSubFederation)
    {
        $this->isSubFederation = $isSubFederation;

        return $this;
    }

    /**
     * Get isSubFederation
     *
     * @return boolean
     */
    public function getIsSubFederation()
    {
        return $this->isSubFederation;
    }

    /**
     * Set clubType
     *
     * @param string $clubType
     *
     * @return FgClub
     */
    public function setClubType($clubType)
    {
        $this->clubType = $clubType;

        return $this;
    }

    /**
     * Get clubType
     *
     * @return string
     */
    public function getClubType()
    {
        return $this->clubType;
    }

    /**
     * Set subfedLevel
     *
     * @param boolean $subfedLevel
     *
     * @return FgClub
     */
    public function setSubfedLevel($subfedLevel)
    {
        $this->subfedLevel = $subfedLevel;

        return $this;
    }

    /**
     * Get subfedLevel
     *
     * @return boolean
     */
    public function getSubfedLevel()
    {
        return $this->subfedLevel;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return FgClub
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set urlIdentifier
     *
     * @param string $urlIdentifier
     *
     * @return FgClub
     */
    public function setUrlIdentifier($urlIdentifier)
    {
        $this->urlIdentifier = $urlIdentifier;

        return $this;
    }

    /**
     * Get urlIdentifier
     *
     * @return string
     */
    public function getUrlIdentifier()
    {
        return $this->urlIdentifier;
    }

    /**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return FgClub
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
     * Set defaultContactSubscription
     *
     * @param boolean $defaultContactSubscription
     *
     * @return FgClub
     */
    public function setDefaultContactSubscription($defaultContactSubscription)
    {
        $this->defaultContactSubscription = $defaultContactSubscription;

        return $this;
    }

    /**
     * Get defaultContactSubscription
     *
     * @return boolean
     */
    public function getDefaultContactSubscription()
    {
        return $this->defaultContactSubscription;
    }

    /**
     * Set hasSubfederation
     *
     * @param boolean $hasSubfederation
     *
     * @return FgClub
     */
    public function setHasSubfederation($hasSubfederation)
    {
        $this->hasSubfederation = $hasSubfederation;

        return $this;
    }

    /**
     * Get hasSubfederation
     *
     * @return boolean
     */
    public function getHasSubfederation()
    {
        return $this->hasSubfederation;
    }

    /**
     * Set settingsUpdated
     *
     * @param \DateTime $settingsUpdated
     *
     * @return FgClub
     */
    public function setSettingsUpdated($settingsUpdated)
    {
        $this->settingsUpdated = $settingsUpdated;

        return $this;
    }

    /**
     * Get settingsUpdated
     *
     * @return \DateTime
     */
    public function getSettingsUpdated()
    {
        return $this->settingsUpdated;
    }

    /**
     * Set calendarColorCode
     *
     * @param string $calendarColorCode
     *
     * @return FgClub
     */
    public function setCalendarColorCode($calendarColorCode)
    {
        $this->calendarColorCode = $calendarColorCode;

        return $this;
    }

    /**
     * Get calendarColorCode
     *
     * @return string
     */
    public function getCalendarColorCode()
    {
        return $this->calendarColorCode;
    }

    /**
     * Set assignFedmembershipWithApplication
     *
     * @param boolean $assignFedmembershipWithApplication
     *
     * @return FgClub
     */
    public function setAssignFedmembershipWithApplication($assignFedmembershipWithApplication)
    {
        $this->assignFedmembershipWithApplication = $assignFedmembershipWithApplication;

        return $this;
    }

    /**
     * Get assignFedmembershipWithApplication
     *
     * @return boolean
     */
    public function getAssignFedmembershipWithApplication()
    {
        return $this->assignFedmembershipWithApplication;
    }

    /**
     * Set hasNlFairgatelogo
     *
     * @param boolean $hasNlFairgatelogo
     *
     * @return FgClub
     */
    public function setHasNlFairgatelogo($hasNlFairgatelogo)
    {
        $this->hasNlFairgatelogo = $hasNlFairgatelogo;

        return $this;
    }

    /**
     * Get hasNlFairgatelogo
     *
     * @return boolean
     */
    public function getHasNlFairgatelogo()
    {
        return $this->hasNlFairgatelogo;
    }

    /**
     * Add contact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $contact
     *
     * @return FgClub
     */
    public function addContact(\Common\UtilityBundle\Entity\FgCmContact $contact)
    {
        $this->contact[] = $contact;

        return $this;
    }

    /**
     * Remove contact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $contact
     */
    public function removeContact(\Common\UtilityBundle\Entity\FgCmContact $contact)
    {
        $this->contact->removeElement($contact);
    }

    /**
     * Get contact
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Add fedContact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $fedContact
     *
     * @return FgClub
     */
    public function addFedContact(\Common\UtilityBundle\Entity\FgCmContact $fedContact)
    {
        $this->fedContact[] = $fedContact;

        return $this;
    }

    /**
     * Remove fedContact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $fedContact
     */
    public function removeFedContact(\Common\UtilityBundle\Entity\FgCmContact $fedContact)
    {
        $this->fedContact->removeElement($fedContact);
    }

    /**
     * Get fedContact
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFedContact()
    {
        return $this->fedContact;
    }
}

