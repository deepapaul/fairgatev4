<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgRmCategory
 */
class FgRmCategory
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
     * @var string
     */
    private $contactAssign;

    /**
     * @var string
     */
    private $roleAssign;

    /**
     * @var string
     */
    private $functionAssign;

    /**
     * @var boolean
     */
    private $isActive;

    /**
     * @var boolean
     */
    private $isTeam;

    /**
     * @var boolean
     */
    private $isWorkgroup;

    /**
     * @var integer
     */
    private $sortOrder;

    /**
     * @var boolean
     */
    private $isAllowedFedmemberSubfed;

    /**
     * @var boolean
     */
    private $isAllowedFedmemberClub;

    /**
     * @var boolean
     */
    private $isRequiredFedmemberSubfed;

    /**
     * @var boolean
     */
    private $isRequiredFedmemberClub;

    /**
     * @var boolean
     */
    private $isFedCategory;

    /**
     * @var string
     */
    private $calendarColorCode;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;


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
     * @return FgRmCategory
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
     * Set contactAssign
     *
     * @param string $contactAssign
     *
     * @return FgRmCategory
     */
    public function setContactAssign($contactAssign)
    {
        $this->contactAssign = $contactAssign;

        return $this;
    }

    /**
     * Get contactAssign
     *
     * @return string
     */
    public function getContactAssign()
    {
        return $this->contactAssign;
    }

    /**
     * Set roleAssign
     *
     * @param string $roleAssign
     *
     * @return FgRmCategory
     */
    public function setRoleAssign($roleAssign)
    {
        $this->roleAssign = $roleAssign;

        return $this;
    }

    /**
     * Get roleAssign
     *
     * @return string
     */
    public function getRoleAssign()
    {
        return $this->roleAssign;
    }

    /**
     * Set functionAssign
     *
     * @param string $functionAssign
     *
     * @return FgRmCategory
     */
    public function setFunctionAssign($functionAssign)
    {
        $this->functionAssign = $functionAssign;

        return $this;
    }

    /**
     * Get functionAssign
     *
     * @return string
     */
    public function getFunctionAssign()
    {
        return $this->functionAssign;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     *
     * @return FgRmCategory
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
     * Set isTeam
     *
     * @param boolean $isTeam
     *
     * @return FgRmCategory
     */
    public function setIsTeam($isTeam)
    {
        $this->isTeam = $isTeam;

        return $this;
    }

    /**
     * Get isTeam
     *
     * @return boolean
     */
    public function getIsTeam()
    {
        return $this->isTeam;
    }

    /**
     * Set isWorkgroup
     *
     * @param boolean $isWorkgroup
     *
     * @return FgRmCategory
     */
    public function setIsWorkgroup($isWorkgroup)
    {
        $this->isWorkgroup = $isWorkgroup;

        return $this;
    }

    /**
     * Get isWorkgroup
     *
     * @return boolean
     */
    public function getIsWorkgroup()
    {
        return $this->isWorkgroup;
    }

    /**
     * Set sortOrder
     *
     * @param integer $sortOrder
     *
     * @return FgRmCategory
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
     * Set isAllowedFedmemberSubfed
     *
     * @param boolean $isAllowedFedmemberSubfed
     *
     * @return FgRmCategory
     */
    public function setIsAllowedFedmemberSubfed($isAllowedFedmemberSubfed)
    {
        $this->isAllowedFedmemberSubfed = $isAllowedFedmemberSubfed;

        return $this;
    }

    /**
     * Get isAllowedFedmemberSubfed
     *
     * @return boolean
     */
    public function getIsAllowedFedmemberSubfed()
    {
        return $this->isAllowedFedmemberSubfed;
    }

    /**
     * Set isAllowedFedmemberClub
     *
     * @param boolean $isAllowedFedmemberClub
     *
     * @return FgRmCategory
     */
    public function setIsAllowedFedmemberClub($isAllowedFedmemberClub)
    {
        $this->isAllowedFedmemberClub = $isAllowedFedmemberClub;

        return $this;
    }

    /**
     * Get isAllowedFedmemberClub
     *
     * @return boolean
     */
    public function getIsAllowedFedmemberClub()
    {
        return $this->isAllowedFedmemberClub;
    }

    /**
     * Set isRequiredFedmemberSubfed
     *
     * @param boolean $isRequiredFedmemberSubfed
     *
     * @return FgRmCategory
     */
    public function setIsRequiredFedmemberSubfed($isRequiredFedmemberSubfed)
    {
        $this->isRequiredFedmemberSubfed = $isRequiredFedmemberSubfed;

        return $this;
    }

    /**
     * Get isRequiredFedmemberSubfed
     *
     * @return boolean
     */
    public function getIsRequiredFedmemberSubfed()
    {
        return $this->isRequiredFedmemberSubfed;
    }

    /**
     * Set isRequiredFedmemberClub
     *
     * @param boolean $isRequiredFedmemberClub
     *
     * @return FgRmCategory
     */
    public function setIsRequiredFedmemberClub($isRequiredFedmemberClub)
    {
        $this->isRequiredFedmemberClub = $isRequiredFedmemberClub;

        return $this;
    }

    /**
     * Get isRequiredFedmemberClub
     *
     * @return boolean
     */
    public function getIsRequiredFedmemberClub()
    {
        return $this->isRequiredFedmemberClub;
    }

    /**
     * Set isFedCategory
     *
     * @param boolean $isFedCategory
     *
     * @return FgRmCategory
     */
    public function setIsFedCategory($isFedCategory)
    {
        $this->isFedCategory = $isFedCategory;

        return $this;
    }

    /**
     * Get isFedCategory
     *
     * @return boolean
     */
    public function getIsFedCategory()
    {
        return $this->isFedCategory;
    }

    /**
     * Set calendarColorCode
     *
     * @param string $calendarColorCode
     *
     * @return FgRmCategory
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
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     *
     * @return FgRmCategory
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
}

