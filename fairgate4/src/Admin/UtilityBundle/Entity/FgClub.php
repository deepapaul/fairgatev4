<?php

namespace Admin\UtilityBundle\Entity;

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
     * @var integer
     */
    private $responsibleContactId;

    /**
     * @var \DateTime
     */
    private $lastUpdated;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var string
     */
    private $newsletterPowered;

    /**
     * @var string
     */
    private $state;

    /**
     * @var boolean
     */
    private $isFgNewsletterSubscriber;

    /**
     * @var boolean
     */
    private $defaultContactSubscription;

    /**
     * @var integer
     */
    private $clubNumber;

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
     * @var boolean
     */
    private $hasNlFairgatelogo;

    /**
     * @var \DateTime
     */
    private $lastContactUpdated;

    /**
     * @var integer
     */
    private $documentCount;

    /**
     * @var integer
     */
    private $fedmemberCount;

    /**
     * @var \DateTime
     */
    private $lastAdminLogin;

    /**
     * @var integer
     */
    private $ownFedmemberCount;

    /**
     * @var integer
     */
    private $fedNoteCount;

    /**
     * @var integer
     */
    private $activeContactCount;

    /**
     * @var integer
     */
    private $adminCount;

    /**
     * @var integer
     */
    private $mainContactLoginCount;

    /**
     * @var integer
     */
    private $contactLoginCount;

    /**
     * @var integer
     */
    private $newsletterSubscriberCount;

    /**
     * @var integer
     */
    private $subfedNoteCount;

    /**
     * @var string
     */
    private $status;

    /**
     * @var \DateTime
     */
    private $contractStartDate;

    /**
     * @var string
     */
    private $clubCreationProcess;

    /**
     * @var \DateTime
     */
    private $registrationDate;

    /**
     * @var string
     */
    private $registrationToken;

    /**
     * @var \DateTime
     */
    private $contractRenewalDate;

    /**
     * @var string
     */
    private $hearAboutFairgate;

    /**
     * @var integer
     */
    private $numberOfContacts;

    /**
     * @var \Admin\UtilityBundle\Entity\FgSubcategorisation
     */
    private $assignmentCountry;

    /**
     * @var \Admin\UtilityBundle\Entity\FgClubAddress
     */
    private $billing;

    /**
     * @var \Admin\UtilityBundle\Entity\FgClubAddress
     */
    private $correspondence;

    /**
     * @var \Admin\UtilityBundle\Entity\FgSubsubcategorisation
     */
    private $assignmentState;

    /**
     * @var \Admin\UtilityBundle\Entity\FgSubcategorisation
     */
    private $assignmentActivity;

    /**
     * @var \Admin\UtilityBundle\Entity\FgCmContact
     */
    private $lastLoginAdminContact;

    /**
     * @var \Admin\UtilityBundle\Entity\FgSubsubcategorisation
     */
    private $assignmentSubactivity;

    /**
     * @var \Admin\UtilityBundle\Entity\FgCmContact
     */
    private $fairgateSolutionContact;

    /**
     * @var \Admin\UtilityBundle\Entity\FgCmContact
     */
    private $createdBy;


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
     * Set isFairgate
     *
     * @param boolean $isFairgate
     *
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
     * Set isRegistered
     *
     * @param boolean $isRegistered
     *
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
     * Set year
     *
     * @param integer $year
     *
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
     *
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
     * Set email
     *
     * @param string $email
     *
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
     *
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
     * Set responsibleContactId
     *
     * @param integer $responsibleContactId
     *
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
     * Set lastUpdated
     *
     * @param \DateTime $lastUpdated
     *
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
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
     * Set newsletterPowered
     *
     * @param string $newsletterPowered
     *
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
     * Set state
     *
     * @param string $state
     *
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
     * Set isFgNewsletterSubscriber
     *
     * @param boolean $isFgNewsletterSubscriber
     *
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
     * Set clubNumber
     *
     * @param integer $clubNumber
     *
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
     * Set clubMembershipAvailable
     *
     * @param boolean $clubMembershipAvailable
     *
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
     *
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
     * Set addExistingFedMemberClub
     *
     * @param boolean $addExistingFedMemberClub
     *
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
     *
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
     *
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
     * Set lastContactUpdated
     *
     * @param \DateTime $lastContactUpdated
     *
     * @return FgClub
     */
    public function setLastContactUpdated($lastContactUpdated)
    {
        $this->lastContactUpdated = $lastContactUpdated;

        return $this;
    }

    /**
     * Get lastContactUpdated
     *
     * @return \DateTime
     */
    public function getLastContactUpdated()
    {
        return $this->lastContactUpdated;
    }

    /**
     * Set documentCount
     *
     * @param integer $documentCount
     *
     * @return FgClub
     */
    public function setDocumentCount($documentCount)
    {
        $this->documentCount = $documentCount;

        return $this;
    }

    /**
     * Get documentCount
     *
     * @return integer
     */
    public function getDocumentCount()
    {
        return $this->documentCount;
    }

    /**
     * Set fedmemberCount
     *
     * @param integer $fedmemberCount
     *
     * @return FgClub
     */
    public function setFedmemberCount($fedmemberCount)
    {
        $this->fedmemberCount = $fedmemberCount;

        return $this;
    }

    /**
     * Get fedmemberCount
     *
     * @return integer
     */
    public function getFedmemberCount()
    {
        return $this->fedmemberCount;
    }

    /**
     * Set lastAdminLogin
     *
     * @param \DateTime $lastAdminLogin
     *
     * @return FgClub
     */
    public function setLastAdminLogin($lastAdminLogin)
    {
        $this->lastAdminLogin = $lastAdminLogin;

        return $this;
    }

    /**
     * Get lastAdminLogin
     *
     * @return \DateTime
     */
    public function getLastAdminLogin()
    {
        return $this->lastAdminLogin;
    }

    /**
     * Set ownFedmemberCount
     *
     * @param integer $ownFedmemberCount
     *
     * @return FgClub
     */
    public function setOwnFedmemberCount($ownFedmemberCount)
    {
        $this->ownFedmemberCount = $ownFedmemberCount;

        return $this;
    }

    /**
     * Get ownFedmemberCount
     *
     * @return integer
     */
    public function getOwnFedmemberCount()
    {
        return $this->ownFedmemberCount;
    }

    /**
     * Set fedNoteCount
     *
     * @param integer $fedNoteCount
     *
     * @return FgClub
     */
    public function setFedNoteCount($fedNoteCount)
    {
        $this->fedNoteCount = $fedNoteCount;

        return $this;
    }

    /**
     * Get fedNoteCount
     *
     * @return integer
     */
    public function getFedNoteCount()
    {
        return $this->fedNoteCount;
    }

    /**
     * Set activeContactCount
     *
     * @param integer $activeContactCount
     *
     * @return FgClub
     */
    public function setActiveContactCount($activeContactCount)
    {
        $this->activeContactCount = $activeContactCount;

        return $this;
    }

    /**
     * Get activeContactCount
     *
     * @return integer
     */
    public function getActiveContactCount()
    {
        return $this->activeContactCount;
    }

    /**
     * Set adminCount
     *
     * @param integer $adminCount
     *
     * @return FgClub
     */
    public function setAdminCount($adminCount)
    {
        $this->adminCount = $adminCount;

        return $this;
    }

    /**
     * Get adminCount
     *
     * @return integer
     */
    public function getAdminCount()
    {
        return $this->adminCount;
    }

    /**
     * Set mainContactLoginCount
     *
     * @param integer $mainContactLoginCount
     *
     * @return FgClub
     */
    public function setMainContactLoginCount($mainContactLoginCount)
    {
        $this->mainContactLoginCount = $mainContactLoginCount;

        return $this;
    }

    /**
     * Get mainContactLoginCount
     *
     * @return integer
     */
    public function getMainContactLoginCount()
    {
        return $this->mainContactLoginCount;
    }

    /**
     * Set contactLoginCount
     *
     * @param integer $contactLoginCount
     *
     * @return FgClub
     */
    public function setContactLoginCount($contactLoginCount)
    {
        $this->contactLoginCount = $contactLoginCount;

        return $this;
    }

    /**
     * Get contactLoginCount
     *
     * @return integer
     */
    public function getContactLoginCount()
    {
        return $this->contactLoginCount;
    }

    /**
     * Set newsletterSubscriberCount
     *
     * @param integer $newsletterSubscriberCount
     *
     * @return FgClub
     */
    public function setNewsletterSubscriberCount($newsletterSubscriberCount)
    {
        $this->newsletterSubscriberCount = $newsletterSubscriberCount;

        return $this;
    }

    /**
     * Get newsletterSubscriberCount
     *
     * @return integer
     */
    public function getNewsletterSubscriberCount()
    {
        return $this->newsletterSubscriberCount;
    }

    /**
     * Set subfedNoteCount
     *
     * @param integer $subfedNoteCount
     *
     * @return FgClub
     */
    public function setSubfedNoteCount($subfedNoteCount)
    {
        $this->subfedNoteCount = $subfedNoteCount;

        return $this;
    }

    /**
     * Get subfedNoteCount
     *
     * @return integer
     */
    public function getSubfedNoteCount()
    {
        return $this->subfedNoteCount;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return FgClub
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
     * Set contractStartDate
     *
     * @param \DateTime $contractStartDate
     *
     * @return FgClub
     */
    public function setContractStartDate($contractStartDate)
    {
        $this->contractStartDate = $contractStartDate;

        return $this;
    }

    /**
     * Get contractStartDate
     *
     * @return \DateTime
     */
    public function getContractStartDate()
    {
        return $this->contractStartDate;
    }

    /**
     * Set clubCreationProcess
     *
     * @param string $clubCreationProcess
     *
     * @return FgClub
     */
    public function setClubCreationProcess($clubCreationProcess)
    {
        $this->clubCreationProcess = $clubCreationProcess;

        return $this;
    }

    /**
     * Get clubCreationProcess
     *
     * @return string
     */
    public function getClubCreationProcess()
    {
        return $this->clubCreationProcess;
    }

    /**
     * Set registrationDate
     *
     * @param \DateTime $registrationDate
     *
     * @return FgClub
     */
    public function setRegistrationDate($registrationDate)
    {
        $this->registrationDate = $registrationDate;

        return $this;
    }

    /**
     * Get registrationDate
     *
     * @return \DateTime
     */
    public function getRegistrationDate()
    {
        return $this->registrationDate;
    }

    /**
     * Set registrationToken
     *
     * @param string $registrationToken
     *
     * @return FgClub
     */
    public function setRegistrationToken($registrationToken)
    {
        $this->registrationToken = $registrationToken;

        return $this;
    }

    /**
     * Get registrationToken
     *
     * @return string
     */
    public function getRegistrationToken()
    {
        return $this->registrationToken;
    }

    /**
     * Set contractRenewalDate
     *
     * @param \DateTime $contractRenewalDate
     *
     * @return FgClub
     */
    public function setContractRenewalDate($contractRenewalDate)
    {
        $this->contractRenewalDate = $contractRenewalDate;

        return $this;
    }

    /**
     * Get contractRenewalDate
     *
     * @return \DateTime
     */
    public function getContractRenewalDate()
    {
        return $this->contractRenewalDate;
    }

    /**
     * Set hearAboutFairgate
     *
     * @param string $hearAboutFairgate
     *
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
     * Set numberOfContacts
     *
     * @param integer $numberOfContacts
     *
     * @return FgClub
     */
    public function setNumberOfContacts($numberOfContacts)
    {
        $this->numberOfContacts = $numberOfContacts;

        return $this;
    }

    /**
     * Get numberOfContacts
     *
     * @return integer
     */
    public function getNumberOfContacts()
    {
        return $this->numberOfContacts;
    }

    /**
     * Set assignmentCountry
     *
     * @param \Admin\UtilityBundle\Entity\FgSubcategorisation $assignmentCountry
     *
     * @return FgClub
     */
    public function setAssignmentCountry(\Admin\UtilityBundle\Entity\FgSubcategorisation $assignmentCountry = null)
    {
        $this->assignmentCountry = $assignmentCountry;

        return $this;
    }

    /**
     * Get assignmentCountry
     *
     * @return \Admin\UtilityBundle\Entity\FgSubcategorisation
     */
    public function getAssignmentCountry()
    {
        return $this->assignmentCountry;
    }

    /**
     * Set billing
     *
     * @param \Admin\UtilityBundle\Entity\FgClubAddress $billing
     *
     * @return FgClub
     */
    public function setBilling(\Admin\UtilityBundle\Entity\FgClubAddress $billing = null)
    {
        $this->billing = $billing;

        return $this;
    }

    /**
     * Get billing
     *
     * @return \Admin\UtilityBundle\Entity\FgClubAddress
     */
    public function getBilling()
    {
        return $this->billing;
    }

    /**
     * Set correspondence
     *
     * @param \Admin\UtilityBundle\Entity\FgClubAddress $correspondence
     *
     * @return FgClub
     */
    public function setCorrespondence(\Admin\UtilityBundle\Entity\FgClubAddress $correspondence = null)
    {
        $this->correspondence = $correspondence;

        return $this;
    }

    /**
     * Get correspondence
     *
     * @return \Admin\UtilityBundle\Entity\FgClubAddress
     */
    public function getCorrespondence()
    {
        return $this->correspondence;
    }

    /**
     * Set assignmentState
     *
     * @param \Admin\UtilityBundle\Entity\FgSubsubcategorisation $assignmentState
     *
     * @return FgClub
     */
    public function setAssignmentState(\Admin\UtilityBundle\Entity\FgSubsubcategorisation $assignmentState = null)
    {
        $this->assignmentState = $assignmentState;

        return $this;
    }

    /**
     * Get assignmentState
     *
     * @return \Admin\UtilityBundle\Entity\FgSubsubcategorisation
     */
    public function getAssignmentState()
    {
        return $this->assignmentState;
    }

    /**
     * Set assignmentActivity
     *
     * @param \Admin\UtilityBundle\Entity\FgSubcategorisation $assignmentActivity
     *
     * @return FgClub
     */
    public function setAssignmentActivity(\Admin\UtilityBundle\Entity\FgSubcategorisation $assignmentActivity = null)
    {
        $this->assignmentActivity = $assignmentActivity;

        return $this;
    }

    /**
     * Get assignmentActivity
     *
     * @return \Admin\UtilityBundle\Entity\FgSubcategorisation
     */
    public function getAssignmentActivity()
    {
        return $this->assignmentActivity;
    }

    /**
     * Set lastLoginAdminContact
     *
     * @param \Admin\UtilityBundle\Entity\FgCmContact $lastLoginAdminContact
     *
     * @return FgClub
     */
    public function setLastLoginAdminContact(\Admin\UtilityBundle\Entity\FgCmContact $lastLoginAdminContact = null)
    {
        $this->lastLoginAdminContact = $lastLoginAdminContact;

        return $this;
    }

    /**
     * Get lastLoginAdminContact
     *
     * @return \Admin\UtilityBundle\Entity\FgCmContact
     */
    public function getLastLoginAdminContact()
    {
        return $this->lastLoginAdminContact;
    }

    /**
     * Set assignmentSubactivity
     *
     * @param \Admin\UtilityBundle\Entity\FgSubsubcategorisation $assignmentSubactivity
     *
     * @return FgClub
     */
    public function setAssignmentSubactivity(\Admin\UtilityBundle\Entity\FgSubsubcategorisation $assignmentSubactivity = null)
    {
        $this->assignmentSubactivity = $assignmentSubactivity;

        return $this;
    }

    /**
     * Get assignmentSubactivity
     *
     * @return \Admin\UtilityBundle\Entity\FgSubsubcategorisation
     */
    public function getAssignmentSubactivity()
    {
        return $this->assignmentSubactivity;
    }

    /**
     * Set fairgateSolutionContact
     *
     * @param \Admin\UtilityBundle\Entity\FgCmContact $fairgateSolutionContact
     *
     * @return FgClub
     */
    public function setFairgateSolutionContact(\Admin\UtilityBundle\Entity\FgCmContact $fairgateSolutionContact = null)
    {
        $this->fairgateSolutionContact = $fairgateSolutionContact;

        return $this;
    }

    /**
     * Get fairgateSolutionContact
     *
     * @return \Admin\UtilityBundle\Entity\FgCmContact
     */
    public function getFairgateSolutionContact()
    {
        return $this->fairgateSolutionContact;
    }

    /**
     * Set createdBy
     *
     * @param \Admin\UtilityBundle\Entity\FgCmContact $createdBy
     *
     * @return FgClub
     */
    public function setCreatedBy(\Admin\UtilityBundle\Entity\FgCmContact $createdBy = null)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \Admin\UtilityBundle\Entity\FgCmContact
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }
    /**
     * @var \DateTime
     */
    private $registrationExpiryDate;


    /**
     * Set registrationExpiryDate
     *
     * @param \DateTime $registrationExpiryDate
     *
     * @return FgClub
     */
    public function setRegistrationExpiryDate($registrationExpiryDate)
    {
        $this->registrationExpiryDate = $registrationExpiryDate;

        return $this;
    }

    /**
     * Get registrationExpiryDate
     *
     * @return \DateTime
     */
    public function getRegistrationExpiryDate()
    {
        return $this->registrationExpiryDate;
    }
}
