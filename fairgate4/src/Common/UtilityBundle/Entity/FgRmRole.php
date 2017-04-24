<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgRmRole
 */
class FgRmRole
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var boolean
     */
    private $isActive;

    /**
     * @var boolean
     */
    private $isExecutiveBoard;

    /**
     * @var integer
     */
    private $sortOrder;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $image;

    /**
     * @var string
     */
    private $type;

    /**
     * @var \DateTime
     */
    private $filterUpdated;

    /**
     * @var boolean
     */
    private $isDeactivatedForum;

    /**
     * @var string
     */
    private $calendarColorCode;

    /**
     * @var boolean
     */
    private $visibleForAll;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;

    /**
     * @var \Common\UtilityBundle\Entity\FgTeamCategory
     */
    private $teamCategory;

    /**
     * @var \Common\UtilityBundle\Entity\FgFilter
     */
    private $filter;

    /**
     * @var \Common\UtilityBundle\Entity\FgRmCategory
     */
    private $category;


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
     * Set title
     *
     * @param string $title
     *
     * @return FgRmRole
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     *
     * @return FgRmRole
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set isExecutiveBoard
     *
     * @param boolean $isExecutiveBoard
     *
     * @return FgRmRole
     */
    public function setIsExecutiveBoard($isExecutiveBoard)
    {
        $this->isExecutiveBoard = $isExecutiveBoard;

        return $this;
    }

    /**
     * Get isExecutiveBoard
     *
     * @return boolean
     */
    public function getIsExecutiveBoard()
    {
        return $this->isExecutiveBoard;
    }

    /**
     * Set sortOrder
     *
     * @param integer $sortOrder
     *
     * @return FgRmRole
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
     * Set description
     *
     * @param string $description
     *
     * @return FgRmRole
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set image
     *
     * @param string $image
     *
     * @return FgRmRole
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return FgRmRole
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
     * Set filterUpdated
     *
     * @param \DateTime $filterUpdated
     *
     * @return FgRmRole
     */
    public function setFilterUpdated($filterUpdated)
    {
        $this->filterUpdated = $filterUpdated;

        return $this;
    }

    /**
     * Get filterUpdated
     *
     * @return \DateTime
     */
    public function getFilterUpdated()
    {
        return $this->filterUpdated;
    }

    /**
     * Set isDeactivatedForum
     *
     * @param boolean $isDeactivatedForum
     *
     * @return FgRmRole
     */
    public function setIsDeactivatedForum($isDeactivatedForum)
    {
        $this->isDeactivatedForum = $isDeactivatedForum;

        return $this;
    }

    /**
     * Get isDeactivatedForum
     *
     * @return boolean
     */
    public function getIsDeactivatedForum()
    {
        return $this->isDeactivatedForum;
    }

    /**
     * Set calendarColorCode
     *
     * @param string $calendarColorCode
     *
     * @return FgRmRole
     */
    public function setCalendarColorCode($calendarColorCode)
    {
        $this->calendarColorCode = $calendarColorCode;

        return $this;
    }

    /**
     * Get calendarColorCode
     *
     * @return string
     */
    public function getCalendarColorCode()
    {
        return $this->calendarColorCode;
    }

    /**
     * Set visibleForAll
     *
     * @param boolean $visibleForAll
     *
     * @return FgRmRole
     */
    public function setVisibleForAll($visibleForAll)
    {
        $this->visibleForAll = $visibleForAll;

        return $this;
    }

    /**
     * Get visibleForAll
     *
     * @return boolean
     */
    public function getVisibleForAll()
    {
        return $this->visibleForAll;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     *
     * @return FgRmRole
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
     * Set teamCategory
     *
     * @param \Common\UtilityBundle\Entity\FgTeamCategory $teamCategory
     *
     * @return FgRmRole
     */
    public function setTeamCategory(\Common\UtilityBundle\Entity\FgTeamCategory $teamCategory = null)
    {
        $this->teamCategory = $teamCategory;

        return $this;
    }

    /**
     * Get teamCategory
     *
     * @return \Common\UtilityBundle\Entity\FgTeamCategory
     */
    public function getTeamCategory()
    {
        return $this->teamCategory;
    }

    /**
     * Set filter
     *
     * @param \Common\UtilityBundle\Entity\FgFilter $filter
     *
     * @return FgRmRole
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

    /**
     * Set category
     *
     * @param \Common\UtilityBundle\Entity\FgRmCategory $category
     *
     * @return FgRmRole
     */
    public function setCategory(\Common\UtilityBundle\Entity\FgRmCategory $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \Common\UtilityBundle\Entity\FgRmCategory
     */
    public function getCategory()
    {
        return $this->category;
    }
}

