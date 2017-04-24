<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCmClubAssignmentConfirmationLog
 */
class FgCmClubAssignmentConfirmationLog
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
    private $fedContact;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $modifiedBy;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $decidedBy;


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
     * @return FgCmClubAssignmentConfirmationLog
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
     * @return FgCmClubAssignmentConfirmationLog
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
     * @return FgCmClubAssignmentConfirmationLog
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
     * @return FgCmClubAssignmentConfirmationLog
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
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     * @return FgCmClubAssignmentConfirmationLog
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
     * @return FgCmClubAssignmentConfirmationLog
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
     * Set fedContact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $fedContact
     * @return FgCmClubAssignmentConfirmationLog
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
     * Set modifiedBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $modifiedBy
     * @return FgCmClubAssignmentConfirmationLog
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
     * @return FgCmClubAssignmentConfirmationLog
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
}
