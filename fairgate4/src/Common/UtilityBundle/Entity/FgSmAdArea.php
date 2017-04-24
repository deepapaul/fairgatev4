<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgSmAdArea
 */
class FgSmAdArea
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
     * @return FgSmAdArea
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
     * @return FgSmAdArea
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
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     * @return FgSmAdArea
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
