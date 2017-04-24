<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgGmAlbumItems
 */
class FgGmAlbumItems
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
    private $isCoverImage;

    /**
     * @var \Common\UtilityBundle\Entity\FgGmItems
     */
    private $items;

    /**
     * @var \Common\UtilityBundle\Entity\FgGmAlbum
     */
    private $album;


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
     *
     * @return FgGmAlbumItems
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
     * Set isCoverImage
     *
     * @param boolean $isCoverImage
     *
     * @return FgGmAlbumItems
     */
    public function setIsCoverImage($isCoverImage)
    {
        $this->isCoverImage = $isCoverImage;

        return $this;
    }

    /**
     * Get isCoverImage
     *
     * @return boolean
     */
    public function getIsCoverImage()
    {
        return $this->isCoverImage;
    }

    /**
     * Set items
     *
     * @param \Common\UtilityBundle\Entity\FgGmItems $items
     *
     * @return FgGmAlbumItems
     */
    public function setItems(\Common\UtilityBundle\Entity\FgGmItems $items = null)
    {
        $this->items = $items;

        return $this;
    }

    /**
     * Get items
     *
     * @return \Common\UtilityBundle\Entity\FgGmItems
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Set album
     *
     * @param \Common\UtilityBundle\Entity\FgGmAlbum $album
     *
     * @return FgGmAlbumItems
     */
    public function setAlbum(\Common\UtilityBundle\Entity\FgGmAlbum $album = null)
    {
        $this->album = $album;

        return $this;
    }

    /**
     * Get album
     *
     * @return \Common\UtilityBundle\Entity\FgGmAlbum
     */
    public function getAlbum()
    {
        return $this->album;
    }
}

