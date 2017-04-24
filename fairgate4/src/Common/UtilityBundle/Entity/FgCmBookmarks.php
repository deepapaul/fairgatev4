<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgCmBookmarks
 */
class FgCmBookmarks
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
     * @var \Common\UtilityBundle\Entity\FgCmMembership
     */
    private $membership;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $contact;

    /**
     * @var \Common\UtilityBundle\Entity\FgRmRole
     */
    private $role;

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
     * @return FgCmBookmarks
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
     * @return FgCmBookmarks
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
     * Set membership
     *
     * @param \Common\UtilityBundle\Entity\FgCmMembership $membership
     *
     * @return FgCmBookmarks
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
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     *
     * @return FgCmBookmarks
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
     * @return FgCmBookmarks
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
     * Set role
     *
     * @param \Common\UtilityBundle\Entity\FgRmRole $role
     *
     * @return FgCmBookmarks
     */
    public function setRole(\Common\UtilityBundle\Entity\FgRmRole $role = null)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return \Common\UtilityBundle\Entity\FgRmRole
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set filter
     *
     * @param \Common\UtilityBundle\Entity\FgFilter $filter
     *
     * @return FgCmBookmarks
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

