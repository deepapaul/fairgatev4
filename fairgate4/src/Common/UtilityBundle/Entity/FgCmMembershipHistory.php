<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCmMembershipHistory
 */
class FgCmMembershipHistory
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $membershipType;

    /**
     * @var \DateTime
     */
    private $joiningDate;

    /**
     * @var \DateTime
     */
    private $leavingDate;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $contact;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmMembership
     */
    private $membership;


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
     * Set membershipType
     *
     * @param string $membershipType
     * @return FgCmMembershipHistory
     */
    public function setMembershipType($membershipType)
    {
        $this->membershipType = $membershipType;
    
        return $this;
    }

    /**
     * Get membershipType
     *
     * @return string 
     */
    public function getMembershipType()
    {
        return $this->membershipType;
    }

    /**
     * Set joiningDate
     *
     * @param \DateTime $joiningDate
     * @return FgCmMembershipHistory
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
     * @return FgCmMembershipHistory
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
     * Set contact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $contact
     * @return FgCmMembershipHistory
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
     * Set membership
     *
     * @param \Common\UtilityBundle\Entity\FgCmMembership $membership
     * @return FgCmMembershipHistory
     */
    public function setMembership(\Common\UtilityBundle\Entity\FgCmMembership $membership = null)
    {
        $this->membership = $membership;
    
        return $this;
    }

    /**
     * Get membership
     *
     * @return \Common\UtilityBundle\Entity\FgCmMembership 
     */
    public function getMembership()
    {
        return $this->membership;
    }
    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $changedBy;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $membershipClub;


    /**
     * Set changedBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $changedBy
     * @return FgCmMembershipHistory
     */
    public function setChangedBy(\Common\UtilityBundle\Entity\FgCmContact $changedBy = null)
    {
        $this->changedBy = $changedBy;
    
        return $this;
    }

    /**
     * Get changedBy
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact 
     */
    public function getChangedBy()
    {
        return $this->changedBy;
    }

    /**
     * Set membershipClub
     *
     * @param \Common\UtilityBundle\Entity\FgClub $membershipClub
     * @return FgCmMembershipHistory
     */
    public function setMembershipClub(\Common\UtilityBundle\Entity\FgClub $membershipClub = null)
    {
        $this->membershipClub = $membershipClub;
    
        return $this;
    }

    /**
     * Get membershipClub
     *
     * @return \Common\UtilityBundle\Entity\FgClub 
     */
    public function getMembershipClub()
    {
        return $this->membershipClub;
    }
}