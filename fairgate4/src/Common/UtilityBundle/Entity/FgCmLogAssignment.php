<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgCmLogAssignment
 */
class FgCmLogAssignment
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $roleType;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var string
     */
    private $categoryTitle;

    /**
     * @var string
     */
    private $valueBefore;

    /**
     * @var string
     */
    private $valueAfter;

    /**
     * @var integer
     */
    private $historicalId;

    /**
     * @var boolean
     */
    private $isHistorical;

    /**
     * @var \Common\UtilityBundle\Entity\FgRmFunction
     */
    private $function;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $contact;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $categoryClub;

    /**
     * @var \Common\UtilityBundle\Entity\FgRmCategory
     */
    private $category;

    /**
     * @var \Common\UtilityBundle\Entity\FgRmRole
     */
    private $role;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $changedBy;


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
     * Set roleType
     *
     * @param string $roleType
     *
     * @return FgCmLogAssignment
     */
    public function setRoleType($roleType)
    {
        $this->roleType = $roleType;

        return $this;
    }

    /**
     * Get roleType
     *
     * @return string
     */
    public function getRoleType()
    {
        return $this->roleType;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return FgCmLogAssignment
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set categoryTitle
     *
     * @param string $categoryTitle
     *
     * @return FgCmLogAssignment
     */
    public function setCategoryTitle($categoryTitle)
    {
        $this->categoryTitle = $categoryTitle;

        return $this;
    }

    /**
     * Get categoryTitle
     *
     * @return string
     */
    public function getCategoryTitle()
    {
        return $this->categoryTitle;
    }

    /**
     * Set valueBefore
     *
     * @param string $valueBefore
     *
     * @return FgCmLogAssignment
     */
    public function setValueBefore($valueBefore)
    {
        $this->valueBefore = $valueBefore;

        return $this;
    }

    /**
     * Get valueBefore
     *
     * @return string
     */
    public function getValueBefore()
    {
        return $this->valueBefore;
    }

    /**
     * Set valueAfter
     *
     * @param string $valueAfter
     *
     * @return FgCmLogAssignment
     */
    public function setValueAfter($valueAfter)
    {
        $this->valueAfter = $valueAfter;

        return $this;
    }

    /**
     * Get valueAfter
     *
     * @return string
     */
    public function getValueAfter()
    {
        return $this->valueAfter;
    }

    /**
     * Set historicalId
     *
     * @param integer $historicalId
     *
     * @return FgCmLogAssignment
     */
    public function setHistoricalId($historicalId)
    {
        $this->historicalId = $historicalId;

        return $this;
    }

    /**
     * Get historicalId
     *
     * @return integer
     */
    public function getHistoricalId()
    {
        return $this->historicalId;
    }

    /**
     * Set isHistorical
     *
     * @param boolean $isHistorical
     *
     * @return FgCmLogAssignment
     */
    public function setIsHistorical($isHistorical)
    {
        $this->isHistorical = $isHistorical;

        return $this;
    }

    /**
     * Get isHistorical
     *
     * @return boolean
     */
    public function getIsHistorical()
    {
        return $this->isHistorical;
    }

    /**
     * Set function
     *
     * @param \Common\UtilityBundle\Entity\FgRmFunction $function
     *
     * @return FgCmLogAssignment
     */
    public function setFunction(\Common\UtilityBundle\Entity\FgRmFunction $function = null)
    {
        $this->function = $function;

        return $this;
    }

    /**
     * Get function
     *
     * @return \Common\UtilityBundle\Entity\FgRmFunction
     */
    public function getFunction()
    {
        return $this->function;
    }

    /**
     * Set contact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $contact
     *
     * @return FgCmLogAssignment
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
     * Set categoryClub
     *
     * @param \Common\UtilityBundle\Entity\FgClub $categoryClub
     *
     * @return FgCmLogAssignment
     */
    public function setCategoryClub(\Common\UtilityBundle\Entity\FgClub $categoryClub = null)
    {
        $this->categoryClub = $categoryClub;

        return $this;
    }

    /**
     * Get categoryClub
     *
     * @return \Common\UtilityBundle\Entity\FgClub
     */
    public function getCategoryClub()
    {
        return $this->categoryClub;
    }

    /**
     * Set category
     *
     * @param \Common\UtilityBundle\Entity\FgRmCategory $category
     *
     * @return FgCmLogAssignment
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

    /**
     * Set role
     *
     * @param \Common\UtilityBundle\Entity\FgRmRole $role
     *
     * @return FgCmLogAssignment
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
     * Set changedBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $changedBy
     *
     * @return FgCmLogAssignment
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
}

