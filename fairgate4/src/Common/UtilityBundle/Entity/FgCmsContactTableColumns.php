<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgCmsContactTableColumns
 */
class FgCmsContactTableColumns
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $columnType;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $columnSubtype;

    /**
     * @var string
     */
    private $functionIds;

    /**
     * @var boolean
     */
    private $showProfilePicture;

    /**
     * @var integer
     */
    private $sortOrder;

    /**
     * @var boolean
     */
    private $isDeleted;

    /**
     * @var string
     */
    private $fieldDisplayType;

    /**
     * @var integer
     */
    private $lineBreakBefore;

    /**
     * @var string
     */
    private $emptyValueDisplay;

    /**
     * @var string
     */
    private $separateListing;

    /**
     * @var string
     */
    private $profileImage;

    /**
     * @var \Common\UtilityBundle\Entity\FgRmRole
     */
    private $role;

    /**
     * @var \Common\UtilityBundle\Entity\FgRmCategory
     */
    private $roleCategory;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmsContactTable
     */
    private $table;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmAttribute
     */
    private $attribute;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmsPortraitContainerColumn
     */
    private $column;


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
     * Set columnType
     *
     * @param string $columnType
     *
     * @return FgCmsContactTableColumns
     */
    public function setColumnType($columnType)
    {
        $this->columnType = $columnType;

        return $this;
    }

    /**
     * Get columnType
     *
     * @return string
     */
    public function getColumnType()
    {
        return $this->columnType;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return FgCmsContactTableColumns
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
     * Set columnSubtype
     *
     * @param string $columnSubtype
     *
     * @return FgCmsContactTableColumns
     */
    public function setColumnSubtype($columnSubtype)
    {
        $this->columnSubtype = $columnSubtype;

        return $this;
    }

    /**
     * Get columnSubtype
     *
     * @return string
     */
    public function getColumnSubtype()
    {
        return $this->columnSubtype;
    }

    /**
     * Set functionIds
     *
     * @param string $functionIds
     *
     * @return FgCmsContactTableColumns
     */
    public function setFunctionIds($functionIds)
    {
        $this->functionIds = $functionIds;

        return $this;
    }

    /**
     * Get functionIds
     *
     * @return string
     */
    public function getFunctionIds()
    {
        return $this->functionIds;
    }

    /**
     * Set showProfilePicture
     *
     * @param boolean $showProfilePicture
     *
     * @return FgCmsContactTableColumns
     */
    public function setShowProfilePicture($showProfilePicture)
    {
        $this->showProfilePicture = $showProfilePicture;

        return $this;
    }

    /**
     * Get showProfilePicture
     *
     * @return boolean
     */
    public function getShowProfilePicture()
    {
        return $this->showProfilePicture;
    }

    /**
     * Set sortOrder
     *
     * @param integer $sortOrder
     *
     * @return FgCmsContactTableColumns
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
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return FgCmsContactTableColumns
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /**
     * Get isDeleted
     *
     * @return boolean
     */
    public function getIsDeleted()
    {
        return $this->isDeleted;
    }

    /**
     * Set fieldDisplayType
     *
     * @param string $fieldDisplayType
     *
     * @return FgCmsContactTableColumns
     */
    public function setFieldDisplayType($fieldDisplayType)
    {
        $this->fieldDisplayType = $fieldDisplayType;

        return $this;
    }

    /**
     * Get fieldDisplayType
     *
     * @return string
     */
    public function getFieldDisplayType()
    {
        return $this->fieldDisplayType;
    }

    /**
     * Set lineBreakBefore
     *
     * @param integer $lineBreakBefore
     *
     * @return FgCmsContactTableColumns
     */
    public function setLineBreakBefore($lineBreakBefore)
    {
        $this->lineBreakBefore = $lineBreakBefore;

        return $this;
    }

    /**
     * Get lineBreakBefore
     *
     * @return integer
     */
    public function getLineBreakBefore()
    {
        return $this->lineBreakBefore;
    }

    /**
     * Set emptyValueDisplay
     *
     * @param string $emptyValueDisplay
     *
     * @return FgCmsContactTableColumns
     */
    public function setEmptyValueDisplay($emptyValueDisplay)
    {
        $this->emptyValueDisplay = $emptyValueDisplay;

        return $this;
    }

    /**
     * Get emptyValueDisplay
     *
     * @return string
     */
    public function getEmptyValueDisplay()
    {
        return $this->emptyValueDisplay;
    }

    /**
     * Set separateListing
     *
     * @param string $separateListing
     *
     * @return FgCmsContactTableColumns
     */
    public function setSeparateListing($separateListing)
    {
        $this->separateListing = $separateListing;

        return $this;
    }

    /**
     * Get separateListing
     *
     * @return string
     */
    public function getSeparateListing()
    {
        return $this->separateListing;
    }

    /**
     * Set profileImage
     *
     * @param string $profileImage
     *
     * @return FgCmsContactTableColumns
     */
    public function setProfileImage($profileImage)
    {
        $this->profileImage = $profileImage;

        return $this;
    }

    /**
     * Get profileImage
     *
     * @return string
     */
    public function getProfileImage()
    {
        return $this->profileImage;
    }

    /**
     * Set role
     *
     * @param \Common\UtilityBundle\Entity\FgRmRole $role
     *
     * @return FgCmsContactTableColumns
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
     * Set roleCategory
     *
     * @param \Common\UtilityBundle\Entity\FgRmCategory $roleCategory
     *
     * @return FgCmsContactTableColumns
     */
    public function setRoleCategory(\Common\UtilityBundle\Entity\FgRmCategory $roleCategory = null)
    {
        $this->roleCategory = $roleCategory;

        return $this;
    }

    /**
     * Get roleCategory
     *
     * @return \Common\UtilityBundle\Entity\FgRmCategory
     */
    public function getRoleCategory()
    {
        return $this->roleCategory;
    }

    /**
     * Set table
     *
     * @param \Common\UtilityBundle\Entity\FgCmsContactTable $table
     *
     * @return FgCmsContactTableColumns
     */
    public function setTable(\Common\UtilityBundle\Entity\FgCmsContactTable $table = null)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Get table
     *
     * @return \Common\UtilityBundle\Entity\FgCmsContactTable
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Set attribute
     *
     * @param \Common\UtilityBundle\Entity\FgCmAttribute $attribute
     *
     * @return FgCmsContactTableColumns
     */
    public function setAttribute(\Common\UtilityBundle\Entity\FgCmAttribute $attribute = null)
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * Get attribute
     *
     * @return \Common\UtilityBundle\Entity\FgCmAttribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Set column
     *
     * @param \Common\UtilityBundle\Entity\FgCmsPortraitContainerColumn $column
     *
     * @return FgCmsContactTableColumns
     */
    public function setColumn(\Common\UtilityBundle\Entity\FgCmsPortraitContainerColumn $column = null)
    {
        $this->column = $column;

        return $this;
    }

    /**
     * Get column
     *
     * @return \Common\UtilityBundle\Entity\FgCmsPortraitContainerColumn
     */
    public function getColumn()
    {
        return $this->column;
    }
}

