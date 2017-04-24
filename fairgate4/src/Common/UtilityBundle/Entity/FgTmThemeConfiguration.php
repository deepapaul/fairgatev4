<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgTmThemeConfiguration
 */
class FgTmThemeConfiguration
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
    private $headerScrolling;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @var boolean
     */
    private $isActive;

    /**
     * @var boolean
     */
    private $isDefault;

    /**
     * @var string
     */
    private $customCss;

    /**
     * @var string
     */
    private $bgImageSelection;

    /**
     * @var integer
     */
    private $bgSliderTime;

    /**
     * @var boolean
     */
    private $isDeleted;

    /**
     * @var string
     */
    private $cssFilename;

    /**
     * @var \Common\UtilityBundle\Entity\FgTmThemeColorScheme
     */
    private $colorScheme;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $createdBy;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $updatedBy;

    /**
     * @var \Common\UtilityBundle\Entity\FgTmTheme
     */
    private $theme;
    
    /**
     * @var string
     */
    private $headerPosition;
    
    /**
     * @var string
     */
    private $headerLogoPosition;


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
     * @return FgTmThemeConfiguration
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
     * Set headerScrolling
     *
     * @param boolean $headerScrolling
     * @return FgTmThemeConfiguration
     */
    public function setHeaderScrolling($headerScrolling)
    {
        $this->headerScrolling = $headerScrolling;
    
        return $this;
    }

    /**
     * Get headerScrolling
     *
     * @return boolean 
     */
    public function getHeaderScrolling()
    {
        return $this->headerScrolling;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return FgTmThemeConfiguration
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return FgTmThemeConfiguration
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    
        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime 
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return FgTmThemeConfiguration
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
     * Set isDefault
     *
     * @param boolean $isDefault
     * @return FgTmThemeConfiguration
     */
    public function setIsDefault($isDefault)
    {
        $this->isDefault = $isDefault;
    
        return $this;
    }

    /**
     * Get isDefault
     *
     * @return boolean 
     */
    public function getIsDefault()
    {
        return $this->isDefault;
    }

    /**
     * Set customCss
     *
     * @param string $customCss
     * @return FgTmThemeConfiguration
     */
    public function setCustomCss($customCss)
    {
        $this->customCss = $customCss;
    
        return $this;
    }

    /**
     * Get customCss
     *
     * @return string 
     */
    public function getCustomCss()
    {
        return $this->customCss;
    }

    /**
     * Set bgImageSelection
     *
     * @param string $bgImageSelection
     * @return FgTmThemeConfiguration
     */
    public function setBgImageSelection($bgImageSelection)
    {
        $this->bgImageSelection = $bgImageSelection;
    
        return $this;
    }

    /**
     * Get bgImageSelection
     *
     * @return string 
     */
    public function getBgImageSelection()
    {
        return $this->bgImageSelection;
    }

    /**
     * Set bgSliderTime
     *
     * @param integer $bgSliderTime
     * @return FgTmThemeConfiguration
     */
    public function setBgSliderTime($bgSliderTime)
    {
        $this->bgSliderTime = $bgSliderTime;
    
        return $this;
    }

    /**
     * Get bgSliderTime
     *
     * @return integer 
     */
    public function getBgSliderTime()
    {
        return $this->bgSliderTime;
    }

    /**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     * @return FgTmThemeConfiguration
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
     * Set cssFilename
     *
     * @param string $cssFilename
     * @return FgTmThemeConfiguration
     */
    public function setCssFilename($cssFilename)
    {
        $this->cssFilename = $cssFilename;
    
        return $this;
    }

    /**
     * Get cssFilename
     *
     * @return string 
     */
    public function getCssFilename()
    {
        return $this->cssFilename;
    }

    /**
     * Set colorScheme
     *
     * @param \Common\UtilityBundle\Entity\FgTmThemeColorScheme $colorScheme
     * @return FgTmThemeConfiguration
     */
    public function setColorScheme(\Common\UtilityBundle\Entity\FgTmThemeColorScheme $colorScheme = null)
    {
        $this->colorScheme = $colorScheme;
    
        return $this;
    }

    /**
     * Get colorScheme
     *
     * @return \Common\UtilityBundle\Entity\FgTmThemeColorScheme 
     */
    public function getColorScheme()
    {
        return $this->colorScheme;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     * @return FgTmThemeConfiguration
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
     * Set createdBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $createdBy
     * @return FgTmThemeConfiguration
     */
    public function setCreatedBy(\Common\UtilityBundle\Entity\FgCmContact $createdBy = null)
    {
        $this->createdBy = $createdBy;
    
        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact 
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set updatedBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $updatedBy
     * @return FgTmThemeConfiguration
     */
    public function setUpdatedBy(\Common\UtilityBundle\Entity\FgCmContact $updatedBy = null)
    {
        $this->updatedBy = $updatedBy;
    
        return $this;
    }

    /**
     * Get updatedBy
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact 
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * Set theme
     *
     * @param \Common\UtilityBundle\Entity\FgTmTheme $theme
     * @return FgTmThemeConfiguration
     */
    public function setTheme(\Common\UtilityBundle\Entity\FgTmTheme $theme = null)
    {
        $this->theme = $theme;
    
        return $this;
    }

    /**
     * Get theme
     *
     * @return \Common\UtilityBundle\Entity\FgTmTheme 
     */
    public function getTheme()
    {
        return $this->theme;
    }
    
   /**
     * Get headerPosition
     *
     * @return string 
     */
    public function getHeaderPosition()
    {
        return $this->headerPosition;
    }

    /**
     * Set headerPosition
     *
     * @param string $headerPosition
     * @return FgTmThemeConfiguration
     */
    public function setHeaderPosition($headerPosition)
    {
        $this->headerPosition = $headerPosition;
    
        return $this;
    }
    
    /**
     * Get headerLogoPosition
     *
     * @return string 
     */
    public function getHeaderLogoPosition()
    {
        return $this->headerLogoPosition;
    }

    /**
     * Set headerLogoPosition
     *
     * @param string $headerLogoPosition
     * @return FgTmThemeConfiguration
     */
    public function setHeaderLogoPosition($headerLogoPosition)
    {
        $this->headerLogoPosition = $headerLogoPosition;
    
        return $this;
    }
}
