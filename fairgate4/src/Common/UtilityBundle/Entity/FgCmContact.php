<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCmContact
 */
class FgCmContact
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $memberId;

    /**
     * @var boolean
     */
    private $isCompany;

    /**
     * @var boolean
     */
    private $isSponsor;

    /**
     * @var boolean
     */
    private $isStealthMode;

    /**
     * @var boolean
     */
    private $intranetAccess;

    /**
     * @var boolean
     */
    private $isSubscriber;

    /**
     * @var boolean
     */
    private $isDeleted;

    /**
     * @var string
     */
    private $systemLanguage;

    /**
     * @var boolean
     */
    private $isPermanentDelete;

    /**
     * @var boolean
     */
    private $isDraft;

    /**
     * @var boolean
     */
    private $isPostalAddress;

    /**
     * @var boolean
     */
    private $isNew;

    /**
     * @var boolean
     */
    private $isFairgate;

    /**
     * @var boolean
     */
    private $isFormerFedMember;

    /**
     * @var string
     */
    private $isFedMembershipConfirmed;

    /**
     * @var boolean
     */
    private $isClubAssignmentConfirmed;

    /**
     * @var integer
     */
    private $compDefContact;

    /**
     * @var string
     */
    private $compDefContactFun;

    /**
     * @var \DateTime
     */
    private $lastUpdated;

    /**
     * @var string
     */
    private $dispatchTypeInvoice;

    /**
     * @var string
     */
    private $dispatchTypeDun;

    /**
     * @var boolean
     */
    private $hasMainContact;

    /**
     * @var boolean
     */
    private $hasMainContactAddress;

    /**
     * @var boolean
     */
    private $isHouseholdHead;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $joiningDate;

    /**
     * @var \DateTime
     */
    private $leavingDate;

    /**
     * @var \DateTime
     */
    private $firstJoiningDate;

    /**
     * @var \DateTime
     */
    private $archivedOn;

    /**
     * @var boolean
     */
    private $isSeperateInvoice;

    /**
     * @var boolean
     */
    private $sameInvoiceAddress;

    /**
     * @var integer
     */
    private $loginCount;

    /**
     * @var \DateTime
     */
    private $lastLogin;

    /**
     * @var string
     */
    private $importTable;

    /**
     * @var integer
     */
    private $importId;

    /**
     * @var boolean
     */
    private $allowMerging;

    /**
     * @var \DateTime
     */
    private $resignedOn;

    /**
     * @var boolean
     */
    private $quickwindowVisibilty;

    /**
     * @var boolean
     */
    private $isFedAdmin;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $mainClub;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $mergeToContact;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmMembership
     */
    private $clubMembershipCat;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmMembership
     */
    private $fedMembershipCat;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmMembership
     */
    private $oldFedMembership;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $fedMembershipAssignedClub;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $createdClub;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $fedContact;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $subfedContact;


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
     * Set memberId
     *
     * @param integer $memberId
     * @return FgCmContact
     */
    public function setMemberId($memberId)
    {
        $this->memberId = $memberId;

        return $this;
    }

    /**
     * Get memberId
     *
     * @return integer
     */
    public function getMemberId()
    {
        return $this->memberId;
    }

    /**
     * Set isCompany
     *
     * @param boolean $isCompany
     * @return FgCmContact
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
     * Set isSponsor
     *
     * @param boolean $isSponsor
     * @return FgCmContact
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
     * Set isStealthMode
     *
     * @param boolean $isStealthMode
     * @return FgCmContact
     */
    public function setIsStealthMode($isStealthMode)
    {
        $this->isStealthMode = $isStealthMode;

        return $this;
    }

    /**
     * Get isStealthMode
     *
     * @return boolean
     */
    public function getIsStealthMode()
    {
        return $this->isStealthMode;
    }

    /**
     * Set intranetAccess
     *
     * @param boolean $intranetAccess
     * @return FgCmContact
     */
    public function setIntranetAccess($intranetAccess)
    {
        $this->intranetAccess = $intranetAccess;

        return $this;
    }

    /**
     * Get intranetAccess
     *
     * @return boolean
     */
    public function getIntranetAccess()
    {
        return $this->intranetAccess;
    }

    /**
     * Set isSubscriber
     *
     * @param boolean $isSubscriber
     * @return FgCmContact
     */
    public function setIsSubscriber($isSubscriber)
    {
        $this->isSubscriber = $isSubscriber;

        return $this;
    }

    /**
     * Get isSubscriber
     *
     * @return boolean
     */
    public function getIsSubscriber()
    {
        return $this->isSubscriber;
    }

    /**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     * @return FgCmContact
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
     * Set systemLanguage
     *
     * @param string $systemLanguage
     * @return FgCmContact
     */
    public function setSystemLanguage($systemLanguage)
    {
        $this->systemLanguage = $systemLanguage;

        return $this;
    }

    /**
     * Get systemLanguage
     *
     * @return string
     */
    public function getSystemLanguage()
    {
        return $this->systemLanguage;
    }

    /**
     * Set isPermanentDelete
     *
     * @param boolean $isPermanentDelete
     * @return FgCmContact
     */
    public function setIsPermanentDelete($isPermanentDelete)
    {
        $this->isPermanentDelete = $isPermanentDelete;

        return $this;
    }

    /**
     * Get isPermanentDelete
     *
     * @return boolean
     */
    public function getIsPermanentDelete()
    {
        return $this->isPermanentDelete;
    }

    /**
     * Set isDraft
     *
     * @param boolean $isDraft
     * @return FgCmContact
     */
    public function setIsDraft($isDraft)
    {
        $this->isDraft = $isDraft;

        return $this;
    }

    /**
     * Get isDraft
     *
     * @return boolean
     */
    public function getIsDraft()
    {
        return $this->isDraft;
    }

    /**
     * Set isPostalAddress
     *
     * @param boolean $isPostalAddress
     * @return FgCmContact
     */
    public function setIsPostalAddress($isPostalAddress)
    {
        $this->isPostalAddress = $isPostalAddress;

        return $this;
    }

    /**
     * Get isPostalAddress
     *
     * @return boolean
     */
    public function getIsPostalAddress()
    {
        return $this->isPostalAddress;
    }

    /**
     * Set isNew
     *
     * @param boolean $isNew
     * @return FgCmContact
     */
    public function setIsNew($isNew)
    {
        $this->isNew = $isNew;

        return $this;
    }

    /**
     * Get isNew
     *
     * @return boolean
     */
    public function getIsNew()
    {
        return $this->isNew;
    }

    /**
     * Set isFairgate
     *
     * @param boolean $isFairgate
     * @return FgCmContact
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
     * Set isFormerFedMember
     *
     * @param boolean $isFormerFedMember
     * @return FgCmContact
     */
    public function setIsFormerFedMember($isFormerFedMember)
    {
        $this->isFormerFedMember = $isFormerFedMember;

        return $this;
    }

    /**
     * Get isFormerFedMember
     *
     * @return boolean
     */
    public function getIsFormerFedMember()
    {
        return $this->isFormerFedMember;
    }

    /**
     * Set isFedMembershipConfirmed
     *
     * @param string $isFedMembershipConfirmed
     * @return FgCmContact
     */
    public function setIsFedMembershipConfirmed($isFedMembershipConfirmed)
    {
        $this->isFedMembershipConfirmed = $isFedMembershipConfirmed;

        return $this;
    }

    /**
     * Get isFedMembershipConfirmed
     *
     * @return string
     */
    public function getIsFedMembershipConfirmed()
    {
        return $this->isFedMembershipConfirmed;
    }

    /**
     * Set isClubAssignmentConfirmed
     *
     * @param boolean $isClubAssignmentConfirmed
     * @return FgCmContact
     */
    public function setIsClubAssignmentConfirmed($isClubAssignmentConfirmed)
    {
        $this->isClubAssignmentConfirmed = $isClubAssignmentConfirmed;

        return $this;
    }

    /**
     * Get isClubAssignmentConfirmed
     *
     * @return boolean
     */
    public function getIsClubAssignmentConfirmed()
    {
        return $this->isClubAssignmentConfirmed;
    }

    /**
     * Set compDefContact
     *
     * @param integer $compDefContact
     * @return FgCmContact
     */
    public function setCompDefContact($compDefContact)
    {
        $this->compDefContact = $compDefContact;

        return $this;
    }

    /**
     * Get compDefContact
     *
     * @return integer
     */
    public function getCompDefContact()
    {
        return $this->compDefContact;
    }

    /**
     * Set compDefContactFun
     *
     * @param string $compDefContactFun
     * @return FgCmContact
     */
    public function setCompDefContactFun($compDefContactFun)
    {
        $this->compDefContactFun = $compDefContactFun;

        return $this;
    }

    /**
     * Get compDefContactFun
     *
     * @return string
     */
    public function getCompDefContactFun()
    {
        return $this->compDefContactFun;
    }

    /**
     * Set lastUpdated
     *
     * @param \DateTime $lastUpdated
     * @return FgCmContact
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
     * Set dispatchTypeInvoice
     *
     * @param string $dispatchTypeInvoice
     * @return FgCmContact
     */
    public function setDispatchTypeInvoice($dispatchTypeInvoice)
    {
        $this->dispatchTypeInvoice = $dispatchTypeInvoice;

        return $this;
    }

    /**
     * Get dispatchTypeInvoice
     *
     * @return string
     */
    public function getDispatchTypeInvoice()
    {
        return $this->dispatchTypeInvoice;
    }

    /**
     * Set dispatchTypeDun
     *
     * @param string $dispatchTypeDun
     * @return FgCmContact
     */
    public function setDispatchTypeDun($dispatchTypeDun)
    {
        $this->dispatchTypeDun = $dispatchTypeDun;

        return $this;
    }

    /**
     * Get dispatchTypeDun
     *
     * @return string
     */
    public function getDispatchTypeDun()
    {
        return $this->dispatchTypeDun;
    }

    /**
     * Set hasMainContact
     *
     * @param boolean $hasMainContact
     * @return FgCmContact
     */
    public function setHasMainContact($hasMainContact)
    {
        $this->hasMainContact = $hasMainContact;

        return $this;
    }

    /**
     * Get hasMainContact
     *
     * @return boolean
     */
    public function getHasMainContact()
    {
        return $this->hasMainContact;
    }

    /**
     * Set hasMainContactAddress
     *
     * @param boolean $hasMainContactAddress
     * @return FgCmContact
     */
    public function setHasMainContactAddress($hasMainContactAddress)
    {
        $this->hasMainContactAddress = $hasMainContactAddress;

        return $this;
    }

    /**
     * Get hasMainContactAddress
     *
     * @return boolean
     */
    public function getHasMainContactAddress()
    {
        return $this->hasMainContactAddress;
    }

    /**
     * Set isHouseholdHead
     *
     * @param boolean $isHouseholdHead
     * @return FgCmContact
     */
    public function setIsHouseholdHead($isHouseholdHead)
    {
        $this->isHouseholdHead = $isHouseholdHead;

        return $this;
    }

    /**
     * Get isHouseholdHead
     *
     * @return boolean
     */
    public function getIsHouseholdHead()
    {
        return $this->isHouseholdHead;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return FgCmContact
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
     * Set joiningDate
     *
     * @param \DateTime $joiningDate
     * @return FgCmContact
     */
    public function setJoiningDate($joiningDate)
    {
        $this->joiningDate = $joiningDate;

        return $this;
    }

    /**
     * Get joiningDate
     *
     * @return \DateTime
     */
    public function getJoiningDate()
    {
        return $this->joiningDate;
    }

    /**
     * Set leavingDate
     *
     * @param \DateTime $leavingDate
     * @return FgCmContact
     */
    public function setLeavingDate($leavingDate)
    {
        $this->leavingDate = $leavingDate;

        return $this;
    }

    /**
     * Get leavingDate
     *
     * @return \DateTime
     */
    public function getLeavingDate()
    {
        return $this->leavingDate;
    }

    /**
     * Set firstJoiningDate
     *
     * @param \DateTime $firstJoiningDate
     * @return FgCmContact
     */
    public function setFirstJoiningDate($firstJoiningDate)
    {
        $this->firstJoiningDate = $firstJoiningDate;

        return $this;
    }

    /**
     * Get firstJoiningDate
     *
     * @return \DateTime
     */
    public function getFirstJoiningDate()
    {
        return $this->firstJoiningDate;
    }

    /**
     * Set archivedOn
     *
     * @param \DateTime $archivedOn
     * @return FgCmContact
     */
    public function setArchivedOn($archivedOn)
    {
        $this->archivedOn = $archivedOn;

        return $this;
    }

    /**
     * Get archivedOn
     *
     * @return \DateTime
     */
    public function getArchivedOn()
    {
        return $this->archivedOn;
    }

    /**
     * Set isSeperateInvoice
     *
     * @param boolean $isSeperateInvoice
     * @return FgCmContact
     */
    public function setIsSeperateInvoice($isSeperateInvoice)
    {
        $this->isSeperateInvoice = $isSeperateInvoice;

        return $this;
    }

    /**
     * Get isSeperateInvoice
     *
     * @return boolean
     */
    public function getIsSeperateInvoice()
    {
        return $this->isSeperateInvoice;
    }

    /**
     * Set sameInvoiceAddress
     *
     * @param boolean $sameInvoiceAddress
     * @return FgCmContact
     */
    public function setSameInvoiceAddress($sameInvoiceAddress)
    {
        $this->sameInvoiceAddress = $sameInvoiceAddress;

        return $this;
    }

    /**
     * Get sameInvoiceAddress
     *
     * @return boolean
     */
    public function getSameInvoiceAddress()
    {
        return $this->sameInvoiceAddress;
    }

    /**
     * Set loginCount
     *
     * @param integer $loginCount
     * @return FgCmContact
     */
    public function setLoginCount($loginCount)
    {
        $this->loginCount = $loginCount;

        return $this;
    }

    /**
     * Get loginCount
     *
     * @return integer
     */
    public function getLoginCount()
    {
        return $this->loginCount;
    }

    /**
     * Set lastLogin
     *
     * @param \DateTime $lastLogin
     * @return FgCmContact
     */
    public function setLastLogin($lastLogin)
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    /**
     * Get lastLogin
     *
     * @return \DateTime
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    /**
     * Set importTable
     *
     * @param string $importTable
     * @return FgCmContact
     */
    public function setImportTable($importTable)
    {
        $this->importTable = $importTable;

        return $this;
    }

    /**
     * Get importTable
     *
     * @return string
     */
    public function getImportTable()
    {
        return $this->importTable;
    }

    /**
     * Set importId
     *
     * @param integer $importId
     * @return FgCmContact
     */
    public function setImportId($importId)
    {
        $this->importId = $importId;

        return $this;
    }

    /**
     * Get importId
     *
     * @return integer
     */
    public function getImportId()
    {
        return $this->importId;
    }

    /**
     * Set allowMerging
     *
     * @param boolean $allowMerging
     * @return FgCmContact
     */
    public function setAllowMerging($allowMerging)
    {
        $this->allowMerging = $allowMerging;

        return $this;
    }

    /**
     * Get allowMerging
     *
     * @return boolean
     */
    public function getAllowMerging()
    {
        return $this->allowMerging;
    }

    /**
     * Set resignedOn
     *
     * @param \DateTime $resignedOn
     * @return FgCmContact
     */
    public function setResignedOn($resignedOn)
    {
        $this->resignedOn = $resignedOn;

        return $this;
    }

    /**
     * Get resignedOn
     *
     * @return \DateTime
     */
    public function getResignedOn()
    {
        return $this->resignedOn;
    }

    /**
     * Set quickwindowVisibilty
     *
     * @param boolean $quickwindowVisibilty
     * @return FgCmContact
     */
    public function setQuickwindowVisibilty($quickwindowVisibilty)
    {
        $this->quickwindowVisibilty = $quickwindowVisibilty;

        return $this;
    }

    /**
     * Get quickwindowVisibilty
     *
     * @return boolean
     */
    public function getQuickwindowVisibilty()
    {
        return $this->quickwindowVisibilty;
    }

    /**
     * Set isFedAdmin
     *
     * @param boolean $isFedAdmin
     * @return FgCmContact
     */
    public function setIsFedAdmin($isFedAdmin)
    {
        $this->isFedAdmin = $isFedAdmin;
    
        return $this;
    }

    /**
     * Get isFedAdmin
     *
     * @return boolean 
     */
    public function getIsFedAdmin()
    {
        return $this->isFedAdmin;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     * @return FgCmContact
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
     * Set mainClub
     *
     * @param \Common\UtilityBundle\Entity\FgClub $mainClub
     * @return FgCmContact
     */
    public function setMainClub(\Common\UtilityBundle\Entity\FgClub $mainClub = null)
    {
        $this->mainClub = $mainClub;

        return $this;
    }

    /**
     * Get mainClub
     *
     * @return \Common\UtilityBundle\Entity\FgClub
     */
    public function getMainClub()
    {
        return $this->mainClub;
    }

    /**
     * Set mergeToContact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $mergeToContact
     * @return FgCmContact
     */
    public function setMergeToContact(\Common\UtilityBundle\Entity\FgCmContact $mergeToContact = null)
    {
        $this->mergeToContact = $mergeToContact;

        return $this;
    }

    /**
     * Get mergeToContact
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact
     */
    public function getMergeToContact()
    {
        return $this->mergeToContact;
    }

    /**
     * Set clubMembershipCat
     *
     * @param \Common\UtilityBundle\Entity\FgCmMembership $clubMembershipCat
     * @return FgCmContact
     */
    public function setClubMembershipCat(\Common\UtilityBundle\Entity\FgCmMembership $clubMembershipCat = null)
    {
        $this->clubMembershipCat = $clubMembershipCat;

        return $this;
    }

    /**
     * Get clubMembershipCat
     *
     * @return \Common\UtilityBundle\Entity\FgCmMembership
     */
    public function getClubMembershipCat()
    {
        return $this->clubMembershipCat;
    }

    /**
     * Set fedMembershipCat
     *
     * @param \Common\UtilityBundle\Entity\FgCmMembership $fedMembershipCat
     * @return FgCmContact
     */
    public function setFedMembershipCat(\Common\UtilityBundle\Entity\FgCmMembership $fedMembershipCat = null)
    {
        $this->fedMembershipCat = $fedMembershipCat;

        return $this;
    }

    /**
     * Get fedMembershipCat
     *
     * @return \Common\UtilityBundle\Entity\FgCmMembership
     */
    public function getFedMembershipCat()
    {
        return $this->fedMembershipCat;
    }

    /**
     * Set oldFedMembership
     *
     * @param \Common\UtilityBundle\Entity\FgCmMembership $oldFedMembership
     * @return FgCmContact
     */
    public function setOldFedMembership(\Common\UtilityBundle\Entity\FgCmMembership $oldFedMembership = null)
    {
        $this->oldFedMembership = $oldFedMembership;

        return $this;
    }

    /**
     * Get oldFedMembership
     *
     * @return \Common\UtilityBundle\Entity\FgCmMembership
     */
    public function getOldFedMembership()
    {
        return $this->oldFedMembership;
    }

    /**
     * Set fedMembershipAssignedClub
     *
     * @param \Common\UtilityBundle\Entity\FgClub $fedMembershipAssignedClub
     * @return FgCmContact
     */
    public function setFedMembershipAssignedClub(\Common\UtilityBundle\Entity\FgClub $fedMembershipAssignedClub = null)
    {
        $this->fedMembershipAssignedClub = $fedMembershipAssignedClub;

        return $this;
    }

    /**
     * Get fedMembershipAssignedClub
     *
     * @return \Common\UtilityBundle\Entity\FgClub
     */
    public function getFedMembershipAssignedClub()
    {
        return $this->fedMembershipAssignedClub;
    }

    /**
     * Set createdClub
     *
     * @param \Common\UtilityBundle\Entity\FgClub $createdClub
     * @return FgCmContact
     */
    public function setCreatedClub(\Common\UtilityBundle\Entity\FgClub $createdClub = null)
    {
        $this->createdClub = $createdClub;

        return $this;
    }

    /**
     * Get createdClub
     *
     * @return \Common\UtilityBundle\Entity\FgClub
     */
    public function getCreatedClub()
    {
        return $this->createdClub;
    }

    /**
     * Set fedContact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $fedContact
     * @return FgCmContact
     */
    public function setFedContact(\Common\UtilityBundle\Entity\FgCmContact $fedContact = null)
    {
        $this->fedContact = $fedContact;

        return $this;
    }

    /**
     * Get fedContact
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact
     */
    public function getFedContact()
    {
        return $this->fedContact;
    }

    /**
     * Set subfedContact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $subfedContact
     * @return FgCmContact
     */
    public function setSubfedContact(\Common\UtilityBundle\Entity\FgCmContact $subfedContact = null)
    {
        $this->subfedContact = $subfedContact;

        return $this;
    }

    /**
     * Get subfedContact
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact
     */
    public function getSubfedContact()
    {
        return $this->subfedContact;
    }
}
