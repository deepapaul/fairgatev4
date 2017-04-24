<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

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
    private $isFairgate;

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
     * @var boolean
     */
    private $isRegistered;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $urlIdentifier;

    /**
     * @var integer
     */
    private $year;

    /**
     * @var boolean
     */
    private $isActive;

    /**
     * @var boolean
     */
    private $isVisible;

    /**
     * @var boolean
     */
    private $isDeleted;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $website;

    /**
     * @var \DateTime
     */
    private $nextContactDate;

    /**
     * @var integer
     */
    private $responsibleContactId;

    /**
     * @var integer
     */
    private $firstContactTypeId;

    /**
     * @var \DateTime
     */
    private $lastUpdated;

    /**
     * @var \DateTime
     */
    private $invoiceDate;

    /**
     * @var \DateTime
     */
    private $nextRenewalDate;

    /**
     * @var boolean
     */
    private $isBackendAccess;

    /**
     * @var \DateTime
     */
    private $accessUpdatedOn;

    /**
     * @var string
     */
    private $hearAboutFairgate;

    /**
     * @var string
     */
    private $backendLang;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var boolean
     */
    private $isSbo;

    /**
     * @var string
     */
    private $newsletterPowered;

    /**
     * @var string
     */
    private $okk;

    /**
     * @var string
     */
    private $state;

    /**
     * @var string
     */
    private $invoiceDispatchType;

    /**
     * @var string
     */
    private $dunDispatchType;

    /**
     * @var integer
     */
    private $clientstateAmount;

    /**
     * @var integer
     */
    private $diskSpace;

    /**
     * @var float
     */
    private $diskspaceAmount;

    /**
     * @var boolean
     */
    private $isFgNewsletterSubscriber;

    /**
     * @var string
     */
    private $clientstatecheck;

    /**
     * @var boolean
     */
    private $defaultContactSubscription;

    /**
     * @var integer
     */
    private $clubNumber;

    /**
     * @var \Common\UtilityBundle\Entity\FgClubAddress
     */
    private $correspondence;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $fairgateSolutionContact;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $fairgateSponsorContact;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $fairgateInvoiceContact;

    /**
     * @var \Common\UtilityBundle\Entity\FgClubAddress
     */
    private $billing;

    /**
     * @var \Common\UtilityBundle\Entity\FgSubcategorisation
     */
    private $assignmentCountry;

    /**
     * @var \Common\UtilityBundle\Entity\FgSubsubcategorisation
     */
    private $assignmentState;

    /**
     * @var \Common\UtilityBundle\Entity\FgSubcategorisation
     */
    private $assignmentActivity;

    /**
     * @var \Common\UtilityBundle\Entity\FgSubsubcategorisation
     */
    private $assignmentSubactivity;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $accessUpdatedBy;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $createdBy;
    
    /**
     * @var boolean
     */
    private $hasSubfederation;

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
     * Set subfederationId
     *
     * @param integer $subFederationId
     * @return FgClub
     */
    public function setSubFederationId($subFederationId)
    {
        $this->subFederationId = $subFederationId;

        return $this;
    }

    /**
     * Get subfederationId
     *
     * @return integer
     */
    public function getSubFederationId()
    {
        return $this->subFederationId;
    }
    
    /**
     * Set isFairgate
     *
     * @param boolean $isFairgate
     * @return FgClub
     */
    public function setIsFairgate($isFairgate)
    {
        $this->isFairgate = $isFairgate;

        return $this;
    }

    /**
     * Get isFairgate
     *
     * @return boolean
     */
    public function getIsFairgate()
    {
        return $this->isFairgate;
    }

    /**
     * Set isFederation
     *
     * @param boolean $isFederation
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
     * Set isRegistered
     *
     * @param boolean $isRegistered
     * @return FgClub
     */
    public function setIsRegistered($isRegistered)
    {
        $this->isRegistered = $isRegistered;

        return $this;
    }

    /**
     * Get isRegistered
     *
     * @return boolean
     */
    public function getIsRegistered()
    {
        return $this->isRegistered;
    }

    /**
     * Set title
     *
     * @param string $title
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
     * Set year
     *
     * @param integer $year
     * @return FgClub
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get year
     *
     * @return integer
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return FgClub
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
     * Set isVisible
     *
     * @param boolean $isVisible
     * @return FgClub
     */
    public function setIsVisible($isVisible)
    {
        $this->isVisible = $isVisible;

        return $this;
    }

    /**
     * Get isVisible
     *
     * @return boolean
     */
    public function getIsVisible()
    {
        return $this->isVisible;
    }

    /**
     * Set isDeleted
     *
     * @param boolean $isDeleted
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
     * Set email
     *
     * @param string $email
     * @return FgClub
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set website
     *
     * @param string $website
     * @return FgClub
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Get website
     *
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set nextContactDate
     *
     * @param \DateTime $nextContactDate
     * @return FgClub
     */
    public function setNextContactDate($nextContactDate)
    {
        $this->nextContactDate = $nextContactDate;

        return $this;
    }

    /**
     * Get nextContactDate
     *
     * @return \DateTime
     */
    public function getNextContactDate()
    {
        return $this->nextContactDate;
    }

    /**
     * Set responsibleContactId
     *
     * @param integer $responsibleContactId
     * @return FgClub
     */
    public function setResponsibleContactId($responsibleContactId)
    {
        $this->responsibleContactId = $responsibleContactId;

        return $this;
    }

    /**
     * Get responsibleContactId
     *
     * @return integer
     */
    public function getResponsibleContactId()
    {
        return $this->responsibleContactId;
    }

    /**
     * Set firstContactTypeId
     *
     * @param integer $firstContactTypeId
     * @return FgClub
     */
    public function setFirstContactTypeId($firstContactTypeId)
    {
        $this->firstContactTypeId = $firstContactTypeId;

        return $this;
    }

    /**
     * Get firstContactTypeId
     *
     * @return integer
     */
    public function getFirstContactTypeId()
    {
        return $this->firstContactTypeId;
    }

    /**
     * Set lastUpdated
     *
     * @param \DateTime $lastUpdated
     * @return FgClub
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
     * Set invoiceDate
     *
     * @param \DateTime $invoiceDate
     * @return FgClub
     */
    public function setInvoiceDate($invoiceDate)
    {
        $this->invoiceDate = $invoiceDate;

        return $this;
    }

    /**
     * Get invoiceDate
     *
     * @return \DateTime
     */
    public function getInvoiceDate()
    {
        return $this->invoiceDate;
    }

    /**
     * Set nextRenewalDate
     *
     * @param \DateTime $nextRenewalDate
     * @return FgClub
     */
    public function setNextRenewalDate($nextRenewalDate)
    {
        $this->nextRenewalDate = $nextRenewalDate;

        return $this;
    }

    /**
     * Get nextRenewalDate
     *
     * @return \DateTime
     */
    public function getNextRenewalDate()
    {
        return $this->nextRenewalDate;
    }

    /**
     * Set isBackendAccess
     *
     * @param boolean $isBackendAccess
     * @return FgClub
     */
    public function setIsBackendAccess($isBackendAccess)
    {
        $this->isBackendAccess = $isBackendAccess;

        return $this;
    }

    /**
     * Get isBackendAccess
     *
     * @return boolean
     */
    public function getIsBackendAccess()
    {
        return $this->isBackendAccess;
    }

    /**
     * Set accessUpdatedOn
     *
     * @param \DateTime $accessUpdatedOn
     * @return FgClub
     */
    public function setAccessUpdatedOn($accessUpdatedOn)
    {
        $this->accessUpdatedOn = $accessUpdatedOn;

        return $this;
    }

    /**
     * Get accessUpdatedOn
     *
     * @return \DateTime
     */
    public function getAccessUpdatedOn()
    {
        return $this->accessUpdatedOn;
    }

    /**
     * Set hearAboutFairgate
     *
     * @param string $hearAboutFairgate
     * @return FgClub
     */
    public function setHearAboutFairgate($hearAboutFairgate)
    {
        $this->hearAboutFairgate = $hearAboutFairgate;

        return $this;
    }

    /**
     * Get hearAboutFairgate
     *
     * @return string
     */
    public function getHearAboutFairgate()
    {
        return $this->hearAboutFairgate;
    }

    /**
     * Set backendLang
     *
     * @param string $backendLang
     * @return FgClub
     */
    public function setBackendLang($backendLang)
    {
        $this->backendLang = $backendLang;

        return $this;
    }

    /**
     * Get backendLang
     *
     * @return string
     */
    public function getBackendLang()
    {
        return $this->backendLang;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return FgClub
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
     * Set isSbo
     *
     * @param boolean $isSbo
     * @return FgClub
     */
    public function setIsSbo($isSbo)
    {
        $this->isSbo = $isSbo;

        return $this;
    }

    /**
     * Get isSbo
     *
     * @return boolean
     */
    public function getIsSbo()
    {
        return $this->isSbo;
    }

    /**
     * Set newsletterPowered
     *
     * @param string $newsletterPowered
     * @return FgClub
     */
    public function setNewsletterPowered($newsletterPowered)
    {
        $this->newsletterPowered = $newsletterPowered;

        return $this;
    }

    /**
     * Get newsletterPowered
     *
     * @return string
     */
    public function getNewsletterPowered()
    {
        return $this->newsletterPowered;
    }

    /**
     * Set okk
     *
     * @param string $okk
     * @return FgClub
     */
    public function setOkk($okk)
    {
        $this->okk = $okk;

        return $this;
    }

    /**
     * Get okk
     *
     * @return string
     */
    public function getOkk()
    {
        return $this->okk;
    }

    /**
     * Set state
     *
     * @param string $state
     * @return FgClub
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
     * Set invoiceDispatchType
     *
     * @param string $invoiceDispatchType
     * @return FgClub
     */
    public function setInvoiceDispatchType($invoiceDispatchType)
    {
        $this->invoiceDispatchType = $invoiceDispatchType;

        return $this;
    }

    /**
     * Get invoiceDispatchType
     *
     * @return string
     */
    public function getInvoiceDispatchType()
    {
        return $this->invoiceDispatchType;
    }

    /**
     * Set dunDispatchType
     *
     * @param string $dunDispatchType
     * @return FgClub
     */
    public function setDunDispatchType($dunDispatchType)
    {
        $this->dunDispatchType = $dunDispatchType;

        return $this;
    }

    /**
     * Get dunDispatchType
     *
     * @return string
     */
    public function getDunDispatchType()
    {
        return $this->dunDispatchType;
    }

    /**
     * Set clientstateAmount
     *
     * @param integer $clientstateAmount
     * @return FgClub
     */
    public function setClientstateAmount($clientstateAmount)
    {
        $this->clientstateAmount = $clientstateAmount;

        return $this;
    }

    /**
     * Get clientstateAmount
     *
     * @return integer
     */
    public function getClientstateAmount()
    {
        return $this->clientstateAmount;
    }

    /**
     * Set diskSpace
     *
     * @param integer $diskSpace
     * @return FgClub
     */
    public function setDiskSpace($diskSpace)
    {
        $this->diskSpace = $diskSpace;

        return $this;
    }

    /**
     * Get diskSpace
     *
     * @return integer
     */
    public function getDiskSpace()
    {
        return $this->diskSpace;
    }

    /**
     * Set diskspaceAmount
     *
     * @param float $diskspaceAmount
     * @return FgClub
     */
    public function setDiskspaceAmount($diskspaceAmount)
    {
        $this->diskspaceAmount = $diskspaceAmount;

        return $this;
    }

    /**
     * Get diskspaceAmount
     *
     * @return float
     */
    public function getDiskspaceAmount()
    {
        return $this->diskspaceAmount;
    }

    /**
     * Set isFgNewsletterSubscriber
     *
     * @param boolean $isFgNewsletterSubscriber
     * @return FgClub
     */
    public function setIsFgNewsletterSubscriber($isFgNewsletterSubscriber)
    {
        $this->isFgNewsletterSubscriber = $isFgNewsletterSubscriber;

        return $this;
    }

    /**
     * Get isFgNewsletterSubscriber
     *
     * @return boolean
     */
    public function getIsFgNewsletterSubscriber()
    {
        return $this->isFgNewsletterSubscriber;
    }

    /**
     * Set clientstatecheck
     *
     * @param string $clientstatecheck
     * @return FgClub
     */
    public function setClientstatecheck($clientstatecheck)
    {
        $this->clientstatecheck = $clientstatecheck;

        return $this;
    }

    /**
     * Get clientstatecheck
     *
     * @return string
     */
    public function getClientstatecheck()
    {
        return $this->clientstatecheck;
    }

    /**
     * Set defaultContactSubscription
     *
     * @param boolean $defaultContactSubscription
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
     * Set clubNumber
     *
     * @param integer $clubNumber
     * @return FgClub
     */
    public function setClubNumber($clubNumber)
    {
        $this->clubNumber = $clubNumber;

        return $this;
    }

    /**
     * Get clubNumber
     *
     * @return integer
     */
    public function getClubNumber()
    {
        return $this->clubNumber;
    }

    /**
     * Set correspondence
     *
     * @param \Common\UtilityBundle\Entity\FgClubAddress $correspondence
     * @return FgClub
     */
    public function setCorrespondence(\Common\UtilityBundle\Entity\FgClubAddress $correspondence = null)
    {
        $this->correspondence = $correspondence;

        return $this;
    }

    /**
     * Get correspondence
     *
     * @return \Common\UtilityBundle\Entity\FgClubAddress
     */
    public function getCorrespondence()
    {
        return $this->correspondence;
    }

    /**
     * Set fairgateSolutionContact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $fairgateSolutionContact
     * @return FgClub
     */
    public function setFairgateSolutionContact(\Common\UtilityBundle\Entity\FgCmContact $fairgateSolutionContact = null)
    {
        $this->fairgateSolutionContact = $fairgateSolutionContact;

        return $this;
    }

    /**
     * Get fairgateSolutionContact
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact
     */
    public function getFairgateSolutionContact()
    {
        return $this->fairgateSolutionContact;
    }

    /**
     * Set fairgateSponsorContact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $fairgateSponsorContact
     * @return FgClub
     */
    public function setFairgateSponsorContact(\Common\UtilityBundle\Entity\FgCmContact $fairgateSponsorContact = null)
    {
        $this->fairgateSponsorContact = $fairgateSponsorContact;

        return $this;
    }

    /**
     * Get fairgateSponsorContact
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact
     */
    public function getFairgateSponsorContact()
    {
        return $this->fairgateSponsorContact;
    }

    /**
     * Set fairgateInvoiceContact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $fairgateInvoiceContact
     * @return FgClub
     */
    public function setFairgateInvoiceContact(\Common\UtilityBundle\Entity\FgCmContact $fairgateInvoiceContact = null)
    {
        $this->fairgateInvoiceContact = $fairgateInvoiceContact;

        return $this;
    }

    /**
     * Get fairgateInvoiceContact
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact
     */
    public function getFairgateInvoiceContact()
    {
        return $this->fairgateInvoiceContact;
    }

    /**
     * Set billing
     *
     * @param \Common\UtilityBundle\Entity\FgClubAddress $billing
     * @return FgClub
     */
    public function setBilling(\Common\UtilityBundle\Entity\FgClubAddress $billing = null)
    {
        $this->billing = $billing;

        return $this;
    }

    /**
     * Get billing
     *
     * @return \Common\UtilityBundle\Entity\FgClubAddress
     */
    public function getBilling()
    {
        return $this->billing;
    }

    /**
     * Set assignmentCountry
     *
     * @param \Common\UtilityBundle\Entity\FgSubcategorisation $assignmentCountry
     * @return FgClub
     */
    public function setAssignmentCountry(\Common\UtilityBundle\Entity\FgSubcategorisation $assignmentCountry = null)
    {
        $this->assignmentCountry = $assignmentCountry;

        return $this;
    }

    /**
     * Get assignmentCountry
     *
     * @return \Common\UtilityBundle\Entity\FgSubcategorisation
     */
    public function getAssignmentCountry()
    {
        return $this->assignmentCountry;
    }

    /**
     * Set assignmentState
     *
     * @param \Common\UtilityBundle\Entity\FgSubsubcategorisation $assignmentState
     * @return FgClub
     */
    public function setAssignmentState(\Common\UtilityBundle\Entity\FgSubsubcategorisation $assignmentState = null)
    {
        $this->assignmentState = $assignmentState;

        return $this;
    }

    /**
     * Get assignmentState
     *
     * @return \Common\UtilityBundle\Entity\FgSubsubcategorisation
     */
    public function getAssignmentState()
    {
        return $this->assignmentState;
    }

    /**
     * Set assignmentActivity
     *
     * @param \Common\UtilityBundle\Entity\FgSubcategorisation $assignmentActivity
     * @return FgClub
     */
    public function setAssignmentActivity(\Common\UtilityBundle\Entity\FgSubcategorisation $assignmentActivity = null)
    {
        $this->assignmentActivity = $assignmentActivity;

        return $this;
    }

    /**
     * Get assignmentActivity
     *
     * @return \Common\UtilityBundle\Entity\FgSubcategorisation
     */
    public function getAssignmentActivity()
    {
        return $this->assignmentActivity;
    }

    /**
     * Set assignmentSubactivity
     *
     * @param \Common\UtilityBundle\Entity\FgSubsubcategorisation $assignmentSubactivity
     * @return FgClub
     */
    public function setAssignmentSubactivity(\Common\UtilityBundle\Entity\FgSubsubcategorisation $assignmentSubactivity = null)
    {
        $this->assignmentSubactivity = $assignmentSubactivity;

        return $this;
    }

    /**
     * Get assignmentSubactivity
     *
     * @return \Common\UtilityBundle\Entity\FgSubsubcategorisation
     */
    public function getAssignmentSubactivity()
    {
        return $this->assignmentSubactivity;
    }

    /**
     * Set accessUpdatedBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $accessUpdatedBy
     * @return FgClub
     */
    public function setAccessUpdatedBy(\Common\UtilityBundle\Entity\FgCmContact $accessUpdatedBy = null)
    {
        $this->accessUpdatedBy = $accessUpdatedBy;

        return $this;
    }

    /**
     * Get accessUpdatedBy
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact
     */
    public function getAccessUpdatedBy()
    {
        return $this->accessUpdatedBy;
    }

    /**
     * Set createdBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $createdBy
     * @return FgClub
     */
    public function setCreatedBy(\Common\UtilityBundle\Entity\FgCmContact $createdBy = null)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set hasSubfederation
     *
     * @param boolean $hasSubfederation
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
     * @var \DateTime
     */
    private $settingsUpdated;


    /**
     * @var string
     */
    private $calendarColorCode;
    
    /**
     * Set calendarColorCode
     *
     * @param string $calendarColorCode
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
     * @var boolean
     */
    private $clubMembershipAvailable;

    /**
     * @var boolean
     */
    private $fedMembershipMandatory;

    /**
     * @var boolean
     */
    private $assignFedmembershipWithApplication;

    /**
     * @var boolean
     */
    private $addExistingFedMemberClub;

    /**
     * @var boolean
     */
    private $fedAdminAccess;

    /**
     * @var boolean
     */
    private $hasPromobox;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $fedContact;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $contact;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->fedContact = new \Doctrine\Common\Collections\ArrayCollection();
        $this->contact = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Set clubMembershipAvailable
     *
     * @param boolean $clubMembershipAvailable
     * @return FgClub
     */
    public function setClubMembershipAvailable($clubMembershipAvailable)
    {
        $this->clubMembershipAvailable = $clubMembershipAvailable;
    
        return $this;
    }

    /**
     * Get clubMembershipAvailable
     *
     * @return boolean 
     */
    public function getClubMembershipAvailable()
    {
        return $this->clubMembershipAvailable;
    }

    /**
     * Set fedMembershipMandatory
     *
     * @param boolean $fedMembershipMandatory
     * @return FgClub
     */
    public function setFedMembershipMandatory($fedMembershipMandatory)
    {
        $this->fedMembershipMandatory = $fedMembershipMandatory;
    
        return $this;
    }

    /**
     * Get fedMembershipMandatory
     *
     * @return boolean 
     */
    public function getFedMembershipMandatory()
    {
        return $this->fedMembershipMandatory;
    }

    /**
     * Set assignFedmembershipWithApplication
     *
     * @param boolean $assignFedmembershipWithApplication
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
     * Set addExistingFedMemberClub
     *
     * @param boolean $addExistingFedMemberClub
     * @return FgClub
     */
    public function setAddExistingFedMemberClub($addExistingFedMemberClub)
    {
        $this->addExistingFedMemberClub = $addExistingFedMemberClub;
    
        return $this;
    }

    /**
     * Get addExistingFedMemberClub
     *
     * @return boolean 
     */
    public function getAddExistingFedMemberClub()
    {
        return $this->addExistingFedMemberClub;
    }

    /**
     * Set fedAdminAccess
     *
     * @param boolean $fedAdminAccess
     * @return FgClub
     */
    public function setFedAdminAccess($fedAdminAccess)
    {
        $this->fedAdminAccess = $fedAdminAccess;
    
        return $this;
    }

    /**
     * Get fedAdminAccess
     *
     * @return boolean 
     */
    public function getFedAdminAccess()
    {
        return $this->fedAdminAccess;
    }
    
    /**
     * Set hasPromobox
     *
     * @param boolean $hasPromobox
     * @return FgClub
     */
    public function setHasPromobox($hasPromobox)
    {
        $this->hasPromobox = $hasPromobox;
    
        return $this;
}

    /**
     * Get hasPromobox
     *
     * @return boolean 
     */
    public function getHasPromobox()
    {
        return $this->hasPromobox;
    }
}