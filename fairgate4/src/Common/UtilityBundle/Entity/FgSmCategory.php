<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgSmCategory
 */
class FgSmCategory
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $isSystem;

    /**
     * @var string
     */
    private $title;

    /**
     * @var integer
     */
    private $sortOrder;

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
     * Set isSystem
     *
     * @param boolean $isSystem
     * @return FgSmCategory
     */
    public function setIsSystem($isSystem)
    {
        $this->isSystem = $isSystem;
    
        return $this;
    }

    /**
     * Get isSystem
     *
     * @return boolean 
     */
    public function getIsSystem()
    {
        return $this->isSystem;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return FgSmCategory
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
     * @return FgSmCategory
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
     * @return FgSmCategory
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
