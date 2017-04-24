<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgSmBookmarks
 */
class FgSmBookmarks
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $type;

    /**
     * @var integer
     */
    private $sortOrder;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;

    /**
     * @var \Common\UtilityBundle\Entity\FgSmServices
     */
    private $services;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $contact;

    /**
     * @var \Common\UtilityBundle\Entity\FgFilter
     */
    private $filter;


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
     * Set type
     *
     * @param string $type
     *
     * @return FgSmBookmarks
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
     * Set sortOrder
     *
     * @param integer $sortOrder
     *
     * @return FgSmBookmarks
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
     * @return FgSmBookmarks
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
     * Set services
     *
     * @param \Common\UtilityBundle\Entity\FgSmServices $services
     *
     * @return FgSmBookmarks
     */
    public function setServices(\Common\UtilityBundle\Entity\FgSmServices $services = null)
    {
        $this->services = $services;

        return $this;
    }

    /**
     * Get services
     *
     * @return \Common\UtilityBundle\Entity\FgSmServices
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * Set contact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $contact
     *
     * @return FgSmBookmarks
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
     * Set filter
     *
     * @param \Common\UtilityBundle\Entity\FgFilter $filter
     *
     * @return FgSmBookmarks
     */
    public function setFilter(\Common\UtilityBundle\Entity\FgFilter $filter = null)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * Get filter
     *
     * @return \Common\UtilityBundle\Entity\FgFilter
     */
    public function getFilter()
    {
        return $this->filter;
    }
}

