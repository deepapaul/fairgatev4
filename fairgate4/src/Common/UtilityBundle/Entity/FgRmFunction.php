<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgRmFunction
 */
class FgRmFunction
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
    private $isVisible;

    /**
     * @var integer
     */
    private $sortOrder;

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
     * @return FgRmFunction
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
     * @return FgRmFunction
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
     * Set isVisible
     *
     * @param boolean $isVisible
     *
     * @return FgRmFunction
     */
    public function setIsVisible($isVisible)
    {
        $this->isVisible = $isVisible;

        return $this;

    }


    /**
     * Get isVisible
     *
     * @return boolean
     */
    public function getIsVisible()
    {
        return $this->isVisible;

    }


    /**
     * Set sortOrder
     *
     * @param integer $sortOrder
     *
     * @return FgRmFunction
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
     * Set category
     *
     * @param \Common\UtilityBundle\Entity\FgRmCategory $category
     *
     * @return FgRmFunction
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
     * @var boolean
     */
    private $isFederation;

    /**
     * @var boolean
     */
    private $isRequiredAssignment;


    /**
     * Set isFederation
     *
     * @param boolean $isFederation
     *
     * @return FgRmFunction
     */
    public function setIsFederation($isFederation)
    {
        $this->isFederation = $isFederation;

        return $this;

    }


    /**
     * Get isFederation
     *
     * @return boolean
     */
    public function getIsFederation()
    {
        return $this->isFederation;

    }


    /**
     * Set isRequiredAssignment
     *
     * @param boolean $isRequiredAssignment
     *
     * @return FgRmFunction
     */
    public function setIsRequiredAssignment($isRequiredAssignment)
    {
        $this->isRequiredAssignment = $isRequiredAssignment;

        return $this;

    }


    /**
     * Get isRequiredAssignment
     *
     * @return boolean
     */
    public function getIsRequiredAssignment()
    {
        return $this->isRequiredAssignment;

    }


}