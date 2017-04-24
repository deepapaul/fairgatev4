<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgTmThemeBgImages
 */
class FgTmThemeBgImages
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $bgType;

    /**
     * @var integer
     */
    private $sortOrder;

    /**
     * @var string
     */
    private $positionHorizontal;

    /**
     * @var string
     */
    private $positionVertical;

    /**
     * @var string
     */
    private $bgRepeat;

    /**
     * @var boolean
     */
    private $isScrollable;

    /**
     * @var \Common\UtilityBundle\Entity\FgGmItems
     */
    private $galleryItem;

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
     * Set bgType
     *
     * @param string $bgType
     *
     * @return FgTmThemeBgImages
     */
    public function setBgType($bgType)
    {
        $this->bgType = $bgType;

        return $this;
    }

    /**
     * Get bgType
     *
     * @return string
     */
    public function getBgType()
    {
        return $this->bgType;
    }

    /**
     * Set sortOrder
     *
     * @param integer $sortOrder
     *
     * @return FgTmThemeBgImages
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
     * Set positionHorizontal
     *
     * @param string $positionHorizontal
     *
     * @return FgTmThemeBgImages
     */
    public function setPositionHorizontal($positionHorizontal)
    {
        $this->positionHorizontal = $positionHorizontal;

        return $this;
    }

    /**
     * Get positionHorizontal
     *
     * @return string
     */
    public function getPositionHorizontal()
    {
        return $this->positionHorizontal;
    }

    /**
     * Set positionVertical
     *
     * @param string $positionVertical
     *
     * @return FgTmThemeBgImages
     */
    public function setPositionVertical($positionVertical)
    {
        $this->positionVertical = $positionVertical;

        return $this;
    }

    /**
     * Get positionVertical
     *
     * @return string
     */
    public function getPositionVertical()
    {
        return $this->positionVertical;
    }

    /**
     * Set bgRepeat
     *
     * @param string $bgRepeat
     *
     * @return FgTmThemeBgImages
     */
    public function setBgRepeat($bgRepeat)
    {
        $this->bgRepeat = $bgRepeat;

        return $this;
    }

    /**
     * Get bgRepeat
     *
     * @return string
     */
    public function getBgRepeat()
    {
        return $this->bgRepeat;
    }

    /**
     * Set isScrollable
     *
     * @param boolean $isScrollable
     *
     * @return FgTmThemeBgImages
     */
    public function setIsScrollable($isScrollable)
    {
        $this->isScrollable = $isScrollable;

        return $this;
    }

    /**
     * Get isScrollable
     *
     * @return boolean
     */
    public function getIsScrollable()
    {
        return $this->isScrollable;
    }

    /**
     * Set galleryItem
     *
     * @param \Common\UtilityBundle\Entity\FgGmItems $galleryItem
     *
     * @return FgTmThemeBgImages
     */
    public function setGalleryItem(\Common\UtilityBundle\Entity\FgGmItems $galleryItem = null)
    {
        $this->galleryItem = $galleryItem;

        return $this;
    }

    /**
     * Get galleryItem
     *
     * @return \Common\UtilityBundle\Entity\FgGmItems
     */
    public function getGalleryItem()
    {
        return $this->galleryItem;
    }

    /**
     * Set configuration
     *
     * @param \Common\UtilityBundle\Entity\FgTmThemeConfiguration $configuration
     *
     * @return FgTmThemeBgImages
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

