<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgClubLanguageSettings
 */
class FgClubLanguageSettings
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $sortOrder;

    /**
     * @var boolean
     */
    private $isActive;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;

    /**
     * @var \Common\UtilityBundle\Entity\FgClubLanguage
     */
    private $clubLanguage;


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
     * Set sortOrder
     *
     * @param integer $sortOrder
     * @return FgClubLanguageSettings
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
     * Set isActive
     *
     * @param boolean $isActive
     * @return FgClubLanguageSettings
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
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     * @return FgClubLanguageSettings
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
     * Set clubLanguage
     *
     * @param \Common\UtilityBundle\Entity\FgClubLanguage $clubLanguage
     * @return FgClubLanguageSettings
     */
    public function setClubLanguage(\Common\UtilityBundle\Entity\FgClubLanguage $clubLanguage = null)
    {
        $this->clubLanguage = $clubLanguage;
    
        return $this;
    }

    /**
     * Get clubLanguage
     *
     * @return \Common\UtilityBundle\Entity\FgClubLanguage 
     */
    public function getClubLanguage()
    {
        return $this->clubLanguage;
    }
}