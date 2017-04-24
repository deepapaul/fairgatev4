<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgClubBookmarks
 */
class FgClubBookmarks
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
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $contact;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $subfed;

    /**
     * @var \Common\UtilityBundle\Entity\FgClubFilter
     */
    private $filter;

    /**
     * @var \Common\UtilityBundle\Entity\FgClubClass
     */
    private $class;


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
     * @return FgClubBookmarks
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
     * @return FgClubBookmarks
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
     * @return FgClubBookmarks
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
     * @return FgClubBookmarks
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
     * Set subfed
     *
     * @param \Common\UtilityBundle\Entity\FgClub $subfed
     * @return FgClubBookmarks
     */
    public function setSubfed(\Common\UtilityBundle\Entity\FgClub $subfed = null)
    {
        $this->subfed = $subfed;

        return $this;
    }

    /**
     * Get subfed
     *
     * @return \Common\UtilityBundle\Entity\FgClub
     */
    public function getSubfed()
    {
        return $this->subfed;
    }

    /**
     * Set filter
     *
     * @param \Common\UtilityBundle\Entity\FgClubFilter $filter
     * @return FgClubBookmarks
     */
    public function setFilter(\Common\UtilityBundle\Entity\FgClubFilter $filter = null)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * Get filter
     *
     * @return \Common\UtilityBundle\Entity\FgClubFilter
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Set class
     *
     * @param \Common\UtilityBundle\Entity\FgClubClass $class
     * @return FgClubBookmarks
     */
    public function setClass(\Common\UtilityBundle\Entity\FgClubClass $class = null)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Get class
     *
     * @return \Common\UtilityBundle\Entity\FgClubClass
     */
    public function getClass()
    {
        return $this->class;
    }
}
