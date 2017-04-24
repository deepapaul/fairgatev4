<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgMbModule
 */
class FgMbModule
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
     * @var integer
     */
    private $sortOrder;

    /**
     * @var boolean
     */
    private $isVisible;


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
     * @return FgMbModule
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
     * Set sortOrder
     *
     * @param integer $sortOrder
     * @return FgMbModule
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
     * Set isVisible
     *
     * @param boolean $isVisible
     * @return FgMbModule
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
     * @var string
     */
    private $moduleKey;


    /**
     * Set moduleKey
     *
     * @param string $moduleKey
     * @return FgMbModule
     */
    public function setModuleKey($moduleKey)
    {
        $this->moduleKey = $moduleKey;
    
        return $this;
    }

    /**
     * Get moduleKey
     *
     * @return string 
     */
    public function getModuleKey()
    {
        return $this->moduleKey;
    }
}