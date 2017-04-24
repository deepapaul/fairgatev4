<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCmsPageContentElementFormFieldOptions
 */
class FgCmsPageContentElementFormFieldOptions
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $isActive;

    /**
     * @var string
     */
    private $selectionValueName;

    /**
     * @var integer
     */
    private $sortOrder;

    /**
     * @var boolean
     */
    private $isDeleted;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmsPageContentElementFormFields
     */
    private $field;


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
     * Set isActive
     *
     * @param boolean $isActive
     * @return FgCmsPageContentElementFormFieldOptions
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
     * Set selectionValueName
     *
     * @param string $selectionValueName
     * @return FgCmsPageContentElementFormFieldOptions
     */
    public function setSelectionValueName($selectionValueName)
    {
        $this->selectionValueName = $selectionValueName;
    
        return $this;
    }

    /**
     * Get selectionValueName
     *
     * @return string 
     */
    public function getSelectionValueName()
    {
        return $this->selectionValueName;
    }

    /**
     * Set sortOrder
     *
     * @param integer $sortOrder
     * @return FgCmsPageContentElementFormFieldOptions
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
     * @return FgCmsPageContentElementFormFieldOptions
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
     * Set field
     *
     * @param \Common\UtilityBundle\Entity\FgCmsPageContentElementFormFields $field
     * @return FgCmsPageContentElementFormFieldOptions
     */
    public function setField(\Common\UtilityBundle\Entity\FgCmsPageContentElementFormFields $field = null)
    {
        $this->field = $field;
    
        return $this;
    }

    /**
     * Get field
     *
     * @return \Common\UtilityBundle\Entity\FgCmsPageContentElementFormFields 
     */
    public function getField()
    {
        return $this->field;
    }
}