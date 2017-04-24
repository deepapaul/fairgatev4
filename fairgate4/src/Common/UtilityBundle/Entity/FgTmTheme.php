<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgTmTheme
 */
class FgTmTheme
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
     * @var string
     */
    private $themeOptions;

    /**
     * @var boolean
     */
    private $isActive;


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
     * @return FgTmTheme
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
     *
     * @return FgTmTheme
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
     * Set themeOptions
     *
     * @param string $themeOptions
     *
     * @return FgTmTheme
     */
    public function setThemeOptions($themeOptions)
    {
        $this->themeOptions = $themeOptions;

        return $this;
    }

    /**
     * Get themeOptions
     *
     * @return string
     */
    public function getThemeOptions()
    {
        return $this->themeOptions;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     *
     * @return FgTmTheme
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
}

