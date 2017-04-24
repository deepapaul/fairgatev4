<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCnNewsletterTemplate
 */
class FgCnNewsletterTemplate
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
    private $themeName;

    /**
     * @var string
     */
    private $headerImage;

    /**
     * @var boolean
     */
    private $isArticleDisplay;

    /**
     * @var boolean
     */
    private $showArticleImage;

    /**
     * @var string
     */
    private $colorBg;

    /**
     * @var string
     */
    private $colorTocBg;

    /**
     * @var string
     */
    private $colorStdText;

    /**
     * @var string
     */
    private $colorTocText;

    /**
     * @var string
     */
    private $colorTitleText;

    /**
     * @var \DateTime
     */
    private $lastUpdated;

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
     * @return FgCnNewsletterTemplate
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
     * Set themeName
     *
     * @param string $themeName
     * @return FgCnNewsletterTemplate
     */
    public function setThemeName($themeName)
    {
        $this->themeName = $themeName;

        return $this;
    }

    /**
     * Get themeName
     *
     * @return string
     */
    public function getThemeName()
    {
        return $this->themeName;
    }

    /**
     * Set headerImage
     *
     * @param string $headerImage
     * @return FgCnNewsletterTemplate
     */
    public function setHeaderImage($headerImage)
    {
        $this->headerImage = $headerImage;

        return $this;
    }

    /**
     * Get headerImage
     *
     * @return string
     */
    public function getHeaderImage()
    {
        return $this->headerImage;
    }

    /**
     * Set isArticleDisplay
     *
     * @param boolean $isArticleDisplay
     * @return FgCnNewsletterTemplate
     */
    public function setIsArticleDisplay($isArticleDisplay)
    {
        $this->isArticleDisplay = $isArticleDisplay;

        return $this;
    }

    /**
     * Get isArticleDisplay
     *
     * @return boolean
     */
    public function getIsArticleDisplay()
    {
        return $this->isArticleDisplay;
    }

    /**
     * Set showArticleImage
     *
     * @param boolean $showArticleImage
     * @return FgCnNewsletterTemplate
     */
    public function setShowArticleImage($showArticleImage)
    {
        $this->showArticleImage = $showArticleImage;

        return $this;
    }

    /**
     * Get showArticleImage
     *
     * @return boolean
     */
    public function getShowArticleImage()
    {
        return $this->showArticleImage;
    }

    /**
     * Set colorBg
     *
     * @param string $colorBg
     * @return FgCnNewsletterTemplate
     */
    public function setColorBg($colorBg)
    {
        $this->colorBg = $colorBg;

        return $this;
    }

    /**
     * Get colorBg
     *
     * @return string
     */
    public function getColorBg()
    {
        return $this->colorBg;
    }

    /**
     * Set colorTocBg
     *
     * @param string $colorTocBg
     * @return FgCnNewsletterTemplate
     */
    public function setColorTocBg($colorTocBg)
    {
        $this->colorTocBg = $colorTocBg;

        return $this;
    }

    /**
     * Get colorTocBg
     *
     * @return string
     */
    public function getColorTocBg()
    {
        return $this->colorTocBg;
    }

    /**
     * Set colorStdText
     *
     * @param string $colorStdText
     * @return FgCnNewsletterTemplate
     */
    public function setColorStdText($colorStdText)
    {
        $this->colorStdText = $colorStdText;

        return $this;
    }

    /**
     * Get colorStdText
     *
     * @return string
     */
    public function getColorStdText()
    {
        return $this->colorStdText;
    }

    /**
     * Set colorTocText
     *
     * @param string $colorTocText
     * @return FgCnNewsletterTemplate
     */
    public function setColorTocText($colorTocText)
    {
        $this->colorTocText = $colorTocText;

        return $this;
    }

    /**
     * Get colorTocText
     *
     * @return string
     */
    public function getColorTocText()
    {
        return $this->colorTocText;
    }

    /**
     * Set colorTitleText
     *
     * @param string $colorTitleText
     * @return FgCnNewsletterTemplate
     */
    public function setColorTitleText($colorTitleText)
    {
        $this->colorTitleText = $colorTitleText;

        return $this;
    }

    /**
     * Get colorTitleText
     *
     * @return string
     */
    public function getColorTitleText()
    {
        return $this->colorTitleText;
    }

    /**
     * Set lastUpdated
     *
     * @param \DateTime $lastUpdated
     * @return FgCnNewsletterTemplate
     */
    public function setLastUpdated($lastUpdated)
    {
        $this->lastUpdated = $lastUpdated;

        return $this;
    }

    /**
     * Get lastUpdated
     *
     * @return \DateTime
     */
    public function getLastUpdated()
    {
        return $this->lastUpdated;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     * @return FgCnNewsletterTemplate
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
     * @var string
     */
    private $articleDisplay;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $editedBy;


    /**
     * Set articleDisplay
     *
     * @param string $articleDisplay
     * @return FgCnNewsletterTemplate
     */
    public function setArticleDisplay($articleDisplay)
    {
        $this->articleDisplay = $articleDisplay;
    
        return $this;
    }

    /**
     * Get articleDisplay
     *
     * @return string 
     */
    public function getArticleDisplay()
    {
        return $this->articleDisplay;
    }

    /**
     * Set editedBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $editedBy
     * @return FgCnNewsletterTemplate
     */
    public function setEditedBy(\Common\UtilityBundle\Entity\FgCmContact $editedBy = null)
    {
        $this->editedBy = $editedBy;
    
        return $this;
    }

    /**
     * Get editedBy
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact 
     */
    public function getEditedBy()
    {
        return $this->editedBy;
    }
    /**
     * @var \DateTime
     */
    private $createdOn;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $createdBy;


    /**
     * Set createdOn
     *
     * @param \DateTime $createdOn
     * @return FgCnNewsletterTemplate
     */
    public function setCreatedOn($createdOn)
    {
        $this->createdOn = $createdOn;
    
        return $this;
    }

    /**
     * Get createdOn
     *
     * @return \DateTime 
     */
    public function getCreatedOn()
    {
        return $this->createdOn;
    }

    /**
     * Set createdBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $createdBy
     * @return FgCnNewsletterTemplate
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
     * @var integer
     */
    private $clubId;

    /**
     * @var string
     */
    private $senderName;

    /**
     * @var string
     */
    private $senderEmail;

    /**
     * @var string
     */
    private $salutationType;

    /**
     * @var string
     */
    private $salutation;

    /**
     * @var string
     */
    private $languageSelection;


    /**
     * Set clubId
     *
     * @param integer $clubId
     * @return FgCnNewsletterTemplate
     */
    public function setClubId($clubId)
    {
        $this->clubId = $clubId;
    
        return $this;
    }

    /**
     * Get clubId
     *
     * @return integer 
     */
    public function getClubId()
    {
        return $this->clubId;
    }

    /**
     * Set senderName
     *
     * @param string $senderName
     * @return FgCnNewsletterTemplate
     */
    public function setSenderName($senderName)
    {
        $this->senderName = $senderName;
    
        return $this;
    }

    /**
     * Get senderName
     *
     * @return string 
     */
    public function getSenderName()
    {
        return $this->senderName;
    }

    /**
     * Set senderEmail
     *
     * @param string $senderEmail
     * @return FgCnNewsletterTemplate
     */
    public function setSenderEmail($senderEmail)
    {
        $this->senderEmail = $senderEmail;
    
        return $this;
    }

    /**
     * Get senderEmail
     *
     * @return string 
     */
    public function getSenderEmail()
    {
        return $this->senderEmail;
    }

    /**
     * Set salutationType
     *
     * @param string $salutationType
     * @return FgCnNewsletterTemplate
     */
    public function setSalutationType($salutationType)
    {
        $this->salutationType = $salutationType;
    
        return $this;
    }

    /**
     * Get salutationType
     *
     * @return string 
     */
    public function getSalutationType()
    {
        return $this->salutationType;
    }

    /**
     * Set salutation
     *
     * @param string $salutation
     * @return FgCnNewsletterTemplate
     */
    public function setSalutation($salutation)
    {
        $this->salutation = $salutation;
    
        return $this;
    }

    /**
     * Get salutation
     *
     * @return string 
     */
    public function getSalutation()
    {
        return $this->salutation;
    }

    /**
     * Set languageSelection
     *
     * @param string $languageSelection
     * @return FgCnNewsletterTemplate
     */
    public function setLanguageSelection($languageSelection)
    {
        $this->languageSelection = $languageSelection;
    
        return $this;
    }

    /**
     * Get languageSelection
     *
     * @return string 
     */
    public function getLanguageSelection()
    {
        return $this->languageSelection;
    }
}