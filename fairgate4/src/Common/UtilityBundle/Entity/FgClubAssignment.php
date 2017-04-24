<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgClubAssignment
 */
class FgClubAssignment
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $fromDate;

    /**
     * @var \DateTime
     */
    private $toDate;

    /**
     * @var boolean
     */
    private $isApproved;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $fedContact;


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
     * Set fromDate
     *
     * @param \DateTime $fromDate
     *
     * @return FgClubAssignment
     */
    public function setFromDate($fromDate)
    {
        $this->fromDate = $fromDate;

        return $this;
    }

    /**
     * Get fromDate
     *
     * @return \DateTime
     */
    public function getFromDate()
    {
        return $this->fromDate;
    }

    /**
     * Set toDate
     *
     * @param \DateTime $toDate
     *
     * @return FgClubAssignment
     */
    public function setToDate($toDate)
    {
        $this->toDate = $toDate;

        return $this;
    }

    /**
     * Get toDate
     *
     * @return \DateTime
     */
    public function getToDate()
    {
        return $this->toDate;
    }

    /**
     * Set isApproved
     *
     * @param boolean $isApproved
     *
     * @return FgClubAssignment
     */
    public function setIsApproved($isApproved)
    {
        $this->isApproved = $isApproved;

        return $this;
    }

    /**
     * Get isApproved
     *
     * @return boolean
     */
    public function getIsApproved()
    {
        return $this->isApproved;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     *
     * @return FgClubAssignment
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
     * Set fedContact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $fedContact
     *
     * @return FgClubAssignment
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
}

