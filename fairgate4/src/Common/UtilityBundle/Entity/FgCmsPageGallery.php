<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCmsPageGallery
 */
class FgCmsPageGallery
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $galleryType;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmsPage
     */
    private $page;

    /**
     * @var \Common\UtilityBundle\Entity\FgRmRole
     */
    private $galleryRole;


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
     * Set galleryType
     *
     * @param string $galleryType
     * @return FgCmsPageGallery
     */
    public function setGalleryType($galleryType)
    {
        $this->galleryType = $galleryType;
    
        return $this;
    }

    /**
     * Get galleryType
     *
     * @return string 
     */
    public function getGalleryType()
    {
        return $this->galleryType;
    }

    /**
     * Set page
     *
     * @param \Common\UtilityBundle\Entity\FgCmsPage $page
     * @return FgCmsPageGallery
     */
    public function setPage(\Common\UtilityBundle\Entity\FgCmsPage $page = null)
    {
        $this->page = $page;
    
        return $this;
    }

    /**
     * Get page
     *
     * @return \Common\UtilityBundle\Entity\FgCmsPage 
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Set galleryRole
     *
     * @param \Common\UtilityBundle\Entity\FgRmRole $galleryRole
     * @return FgCmsPageGallery
     */
    public function setGalleryRole(\Common\UtilityBundle\Entity\FgRmRole $galleryRole = null)
    {
        $this->galleryRole = $galleryRole;
    
        return $this;
    }

    /**
     * Get galleryRole
     *
     * @return \Common\UtilityBundle\Entity\FgRmRole 
     */
    public function getGalleryRole()
    {
        return $this->galleryRole;
    }
}
