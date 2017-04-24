<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgTmThemeColorScheme
 */
class FgTmThemeColorScheme
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $colorSchemes;

    /**
     * @var string
     */
    private $cssFilename;

    /**
     * @var boolean
     */
    private $isDefault;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;

    /**
     * @var \Common\UtilityBundle\Entity\FgTmTheme
     */
    private $theme;


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
     * Set colorSchemes
     *
     * @param string $colorSchemes
     *
     * @return FgTmThemeColorScheme
     */
    public function setColorSchemes($colorSchemes)
    {
        $this->colorSchemes = $colorSchemes;

        return $this;
    }

    /**
     * Get colorSchemes
     *
     * @return string
     */
    public function getColorSchemes()
    {
        return $this->colorSchemes;
    }

    /**
     * Set cssFilename
     *
     * @param string $cssFilename
     *
     * @return FgTmThemeColorScheme
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
     * Set isDefault
     *
     * @param boolean $isDefault
     *
     * @return FgTmThemeColorScheme
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
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     *
     * @return FgTmThemeColorScheme
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
     * Set theme
     *
     * @param \Common\UtilityBundle\Entity\FgTmTheme $theme
     *
     * @return FgTmThemeColorScheme
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
}

