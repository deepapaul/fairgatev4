<?php

namespace Admin\UtilityBundle\Entity;

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
     * @var \Admin\UtilityBundle\Entity\FgClub
     */
    private $club;

    /**
     * @var \Admin\UtilityBundle\Entity\FgCmContact
     */
    private $contact;

    /**
     * @var \Admin\UtilityBundle\Entity\FgClub
     */
    private $subfed;

    /**
     * @var \Admin\UtilityBundle\Entity\FgClubFilter
     */
    private $filter;

    /**
     * @var \Admin\UtilityBundle\Entity\FgClubClass
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
     * @param \Admin\UtilityBundle\Entity\FgClub $club
     * @return FgClubBookmarks
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
     * @return FgClubBookmarks
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
     * Set subfed
     *
     * @param \Admin\UtilityBundle\Entity\FgClub $subfed
     * @return FgClubBookmarks
     */
    public function setSubfed(\Admin\UtilityBundle\Entity\FgClub $subfed = null)
    {
        $this->subfed = $subfed;

        return $this;
    }

    /**
     * Get subfed
     *
     * @return \Admin\UtilityBundle\Entity\FgClub
     */
    public function getSubfed()
    {
        return $this->subfed;
    }

    /**
     * Set filter
     *
     * @param \Admin\UtilityBundle\Entity\FgClubFilter $filter
     * @return FgClubBookmarks
     */
    public function setFilter(\Admin\UtilityBundle\Entity\FgClubFilter $filter = null)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * Get filter
     *
     * @return \Admin\UtilityBundle\Entity\FgClubFilter
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Set class
     *
     * @param \Admin\UtilityBundle\Entity\FgClubClass $class
     * @return FgClubBookmarks
     */
    public function setClass(\Admin\UtilityBundle\Entity\FgClubClass $class = null)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Get class
     *
     * @return \Admin\UtilityBundle\Entity\FgClubClass
     */
    public function getClass()
    {
        return $this->class;
    }
    /**
     * @var integer
     */
    private $clubId;

    /**
     * @var integer
     */
    private $subfedId;


    /**
     * Set clubId
     *
     * @param integer $clubId
     *
     * @return FgClubBookmarks
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

    /**
     * Set subfedId
     *
     * @param integer $subfedId
     *
     * @return FgClubBookmarks
     */
    public function setSubfedId($subfedId)
    {
        $this->subfedId = $subfedId;

        return $this;
    }

    /**
     * Get subfedId
     *
     * @return integer
     */
    public function getSubfedId()
    {
        return $this->subfedId;
    }
}
