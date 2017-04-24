<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgTmThemeFonts
 */
class FgTmThemeFonts
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $fontLabel;

    /**
     * @var string
     */
    private $fontName;

    /**
     * @var string
     */
    private $fontStrength;

    /**
     * @var boolean
     */
    private $isItalic;

    /**
     * @var boolean
     */
    private $isUppercase;

    /**
     * @var \Common\UtilityBundle\Entity\FgTmThemeConfiguration
     */
    private $configuration;


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
     * Set fontLabel
     *
     * @param string $fontLabel
     * @return FgTmThemeFonts
     */
    public function setFontLabel($fontLabel)
    {
        $this->fontLabel = $fontLabel;
    
        return $this;
    }

    /**
     * Get fontLabel
     *
     * @return string 
     */
    public function getFontLabel()
    {
        return $this->fontLabel;
    }

    /**
     * Set fontName
     *
     * @param string $fontName
     * @return FgTmThemeFonts
     */
    public function setFontName($fontName)
    {
        $this->fontName = $fontName;
    
        return $this;
    }

    /**
     * Get fontName
     *
     * @return string 
     */
    public function getFontName()
    {
        return $this->fontName;
    }

    /**
     * Set fontStrength
     *
     * @param string $fontStrength
     * @return FgTmThemeFonts
     */
    public function setFontStrength($fontStrength)
    {
        $this->fontStrength = $fontStrength;
    
        return $this;
    }

    /**
     * Get fontStrength
     *
     * @return string 
     */
    public function getFontStrength()
    {
        return $this->fontStrength;
    }

    /**
     * Set isItalic
     *
     * @param boolean $isItalic
     * @return FgTmThemeFonts
     */
    public function setIsItalic($isItalic)
    {
        $this->isItalic = $isItalic;
    
        return $this;
    }

    /**
     * Get isItalic
     *
     * @return boolean 
     */
    public function getIsItalic()
    {
        return $this->isItalic;
    }

    /**
     * Set isUppercase
     *
     * @param boolean $isUppercase
     * @return FgTmThemeFonts
     */
    public function setIsUppercase($isUppercase)
    {
        $this->isUppercase = $isUppercase;
    
        return $this;
    }

    /**
     * Get isUppercase
     *
     * @return boolean 
     */
    public function getIsUppercase()
    {
        return $this->isUppercase;
    }

    /**
     * Set configuration
     *
     * @param \Common\UtilityBundle\Entity\FgTmThemeConfiguration $configuration
     * @return FgTmThemeFonts
     */
    public function setConfiguration(\Common\UtilityBundle\Entity\FgTmThemeConfiguration $configuration = null)
    {
        $this->configuration = $configuration;
    
        return $this;
    }

    /**
     * Get configuration
     *
     * @return \Common\UtilityBundle\Entity\FgTmThemeConfiguration 
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }
}
