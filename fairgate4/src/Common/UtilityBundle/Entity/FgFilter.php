<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgFilter
 */
class FgFilter
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
     * @var string
     */
    private $type;

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
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $contact;

    /**
     * @var \Common\UtilityBundle\Entity\FgTableSettings
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
     *
     * @return FgFilter
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
     * Set type
     *
     * @param string $type
     *
     * @return FgFilter
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set tableRows
     *
     * @param integer $tableRows
     *
     * @return FgFilter
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
     *
     * @return FgFilter
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
     *
     * @return FgFilter
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
     *
     * @return FgFilter
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
     *
     * @return FgFilter
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
     * @param \Common\UtilityBundle\Entity\FgClub $club
     *
     * @return FgFilter
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
     * Set contact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $contact
     *
     * @return FgFilter
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
     * Set tableAttributes
     *
     * @param \Common\UtilityBundle\Entity\FgTableSettings $tableAttributes
     *
     * @return FgFilter
     */
    public function setTableAttributes(\Common\UtilityBundle\Entity\FgTableSettings $tableAttributes = null)
    {
        $this->tableAttributes = $tableAttributes;

        return $this;
    }

    /**
     * Get tableAttributes
     *
     * @return \Common\UtilityBundle\Entity\FgTableSettings
     */
    public function getTableAttributes()
    {
        return $this->tableAttributes;
    }
}

