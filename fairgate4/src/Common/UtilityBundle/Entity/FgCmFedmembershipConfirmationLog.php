<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgCmFedmembershipConfirmationLog
 */
class FgCmFedmembershipConfirmationLog
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $existingClubIds;

    /**
     * @var \DateTime
     */
    private $modifiedDate;

    /**
     * @var \DateTime
     */
    private $decidedDate;

    /**
     * @var string
     */
    private $status;

    /**
     * @var integer
     */
    private $isMerging;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $federationClub;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $contact;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $modifiedBy;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $decidedBy;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmMembership
     */
    private $fedmembershipValueBefore;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmMembership
     */
    private $fedmembershipValueAfter;


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
     * Set existingClubIds
     *
     * @param string $existingClubIds
     *
     * @return FgCmFedmembershipConfirmationLog
     */
    public function setExistingClubIds($existingClubIds)
    {
        $this->existingClubIds = $existingClubIds;

        return $this;
    }

    /**
     * Get existingClubIds
     *
     * @return string
     */
    public function getExistingClubIds()
    {
        return $this->existingClubIds;
    }

    /**
     * Set modifiedDate
     *
     * @param \DateTime $modifiedDate
     *
     * @return FgCmFedmembershipConfirmationLog
     */
    public function setModifiedDate($modifiedDate)
    {
        $this->modifiedDate = $modifiedDate;

        return $this;
    }

    /**
     * Get modifiedDate
     *
     * @return \DateTime
     */
    public function getModifiedDate()
    {
        return $this->modifiedDate;
    }

    /**
     * Set decidedDate
     *
     * @param \DateTime $decidedDate
     *
     * @return FgCmFedmembershipConfirmationLog
     */
    public function setDecidedDate($decidedDate)
    {
        $this->decidedDate = $decidedDate;

        return $this;
    }

    /**
     * Get decidedDate
     *
     * @return \DateTime
     */
    public function getDecidedDate()
    {
        return $this->decidedDate;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return FgCmFedmembershipConfirmationLog
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
     * Set isMerging
     *
     * @param integer $isMerging
     *
     * @return FgCmFedmembershipConfirmationLog
     */
    public function setIsMerging($isMerging)
    {
        $this->isMerging = $isMerging;

        return $this;
    }

    /**
     * Get isMerging
     *
     * @return integer
     */
    public function getIsMerging()
    {
        return $this->isMerging;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     *
     * @return FgCmFedmembershipConfirmationLog
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
     * Set federationClub
     *
     * @param \Common\UtilityBundle\Entity\FgClub $federationClub
     *
     * @return FgCmFedmembershipConfirmationLog
     */
    public function setFederationClub(\Common\UtilityBundle\Entity\FgClub $federationClub = null)
    {
        $this->federationClub = $federationClub;

        return $this;
    }

    /**
     * Get federationClub
     *
     * @return \Common\UtilityBundle\Entity\FgClub
     */
    public function getFederationClub()
    {
        return $this->federationClub;
    }

    /**
     * Set contact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $contact
     *
     * @return FgCmFedmembershipConfirmationLog
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
     * Set modifiedBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $modifiedBy
     *
     * @return FgCmFedmembershipConfirmationLog
     */
    public function setModifiedBy(\Common\UtilityBundle\Entity\FgCmContact $modifiedBy = null)
    {
        $this->modifiedBy = $modifiedBy;

        return $this;
    }

    /**
     * Get modifiedBy
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact
     */
    public function getModifiedBy()
    {
        return $this->modifiedBy;
    }

    /**
     * Set decidedBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $decidedBy
     *
     * @return FgCmFedmembershipConfirmationLog
     */
    public function setDecidedBy(\Common\UtilityBundle\Entity\FgCmContact $decidedBy = null)
    {
        $this->decidedBy = $decidedBy;

        return $this;
    }

    /**
     * Get decidedBy
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact
     */
    public function getDecidedBy()
    {
        return $this->decidedBy;
    }

    /**
     * Set fedmembershipValueBefore
     *
     * @param \Common\UtilityBundle\Entity\FgCmMembership $fedmembershipValueBefore
     *
     * @return FgCmFedmembershipConfirmationLog
     */
    public function setFedmembershipValueBefore(\Common\UtilityBundle\Entity\FgCmMembership $fedmembershipValueBefore = null)
    {
        $this->fedmembershipValueBefore = $fedmembershipValueBefore;

        return $this;
    }

    /**
     * Get fedmembershipValueBefore
     *
     * @return \Common\UtilityBundle\Entity\FgCmMembership
     */
    public function getFedmembershipValueBefore()
    {
        return $this->fedmembershipValueBefore;
    }

    /**
     * Set fedmembershipValueAfter
     *
     * @param \Common\UtilityBundle\Entity\FgCmMembership $fedmembershipValueAfter
     *
     * @return FgCmFedmembershipConfirmationLog
     */
    public function setFedmembershipValueAfter(\Common\UtilityBundle\Entity\FgCmMembership $fedmembershipValueAfter = null)
    {
        $this->fedmembershipValueAfter = $fedmembershipValueAfter;

        return $this;
    }

    /**
     * Get fedmembershipValueAfter
     *
     * @return \Common\UtilityBundle\Entity\FgCmMembership
     */
    public function getFedmembershipValueAfter()
    {
        return $this->fedmembershipValueAfter;
    }
}

