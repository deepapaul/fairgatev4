<?php

namespace Admin\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgClubFilter
 */
class FgClubFilter
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var integer
     */
    private $tableRows;

    /**
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @var string
     */
    private $filterData;

    /**
     * @var boolean
     */
    private $isBroken;

    /**
     * @var integer
     */
    private $sortOrder;

    /**
     * @var \Admin\UtilityBundle\Entity\FgClub
     */
    private $club;

    /**
     * @var \Admin\UtilityBundle\Entity\FgCmContact
     */
    private $contact;

    /**
     * @var \Admin\UtilityBundle\Entity\FgClubTableSettings
     */
    private $tableAttributes;


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
     * Set name
     *
     * @param string $name
     * @return FgClubFilter
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set tableRows
     *
     * @param integer $tableRows
     * @return FgClubFilter
     */
    public function setTableRows($tableRows)
    {
        $this->tableRows = $tableRows;

        return $this;
    }

    /**
     * Get tableRows
     *
     * @return integer
     */
    public function getTableRows()
    {
        return $this->tableRows;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return FgClubFilter
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set filterData
     *
     * @param string $filterData
     * @return FgClubFilter
     */
    public function setFilterData($filterData)
    {
        $this->filterData = $filterData;

        return $this;
    }

    /**
     * Get filterData
     *
     * @return string
     */
    public function getFilterData()
    {
        return $this->filterData;
    }

    /**
     * Set isBroken
     *
     * @param boolean $isBroken
     * @return FgClubFilter
     */
    public function setIsBroken($isBroken)
    {
        $this->isBroken = $isBroken;

        return $this;
    }

    /**
     * Get isBroken
     *
     * @return boolean
     */
    public function getIsBroken()
    {
        return $this->isBroken;
    }

    /**
     * Set sortOrder
     *
     * @param integer $sortOrder
     * @return FgClubFilter
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
     * Set club
     *
     * @param \Admin\UtilityBundle\Entity\FgClub $club
     * @return FgClubFilter
     */
    public function setClub(\Admin\UtilityBundle\Entity\FgClub $club = null)
    {
        $this->club = $club;

        return $this;
    }

    /**
     * Get club
     *
     * @return \Admin\UtilityBundle\Entity\FgClub
     */
    public function getClub()
    {
        return $this->club;
    }

    /**
     * Set contact
     *
     * @param \Admin\UtilityBundle\Entity\FgCmContact $contact
     * @return FgClubFilter
     */
    public function setContact(\Admin\UtilityBundle\Entity\FgCmContact $contact = null)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Get contact
     *
     * @return \Admin\UtilityBundle\Entity\FgCmContact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Set tableAttributes
     *
     * @param \Admin\UtilityBundle\Entity\FgClubTableSettings $tableAttributes
     * @return FgClubFilter
     */
    public function setTableAttributes(\Admin\UtilityBundle\Entity\FgClubTableSettings $tableAttributes = null)
    {
        $this->tableAttributes = $tableAttributes;

        return $this;
    }

    /**
     * Get tableAttributes
     *
     * @return \Admin\UtilityBundle\Entity\FgClubTableSettings
     */
    public function getTableAttributes()
    {
        return $this->tableAttributes;
    }
    /**
     * @var integer
     */
    private $clubId;


    /**
     * Set clubId
     *
     * @param integer $clubId
     *
     * @return FgClubFilter
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
}
