<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgSmSponsorAds
 */
class FgSmSponsorAds
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $image;

    /**
     * @var string
     */
    private $url;

    /**
     * @var integer
     */
    private $imageSize;

    /**
     * @var boolean
     */
    private $isDefault;

    /**
     * @var \Common\UtilityBundle\Entity\FgSmAdArea
     */
    private $adArea;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $contact;


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
     * Set description
     *
     * @param string $description
     * @return FgSmSponsorAds
     */
    public function setDescription($description)
    {
        $this->description = $description;
    
        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set image
     *
     * @param string $image
     * @return FgSmSponsorAds
     */
    public function setImage($image)
    {
        $this->image = $image;
    
        return $this;
    }

    /**
     * Get image
     *
     * @return string 
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return FgSmSponsorAds
     */
    public function setUrl($url)
    {
        $this->url = $url;
    
        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set imageSize
     *
     * @param integer $imageSize
     * @return FgSmSponsorAds
     */
    public function setImageSize($imageSize)
    {
        $this->imageSize = $imageSize;
    
        return $this;
    }

    /**
     * Get imageSize
     *
     * @return integer 
     */
    public function getImageSize()
    {
        return $this->imageSize;
    }

    /**
     * Set isDefault
     *
     * @param boolean $isDefault
     * @return FgSmSponsorAds
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
     * Set adArea
     *
     * @param \Common\UtilityBundle\Entity\FgSmAdArea $adArea
     * @return FgSmSponsorAds
     */
    public function setAdArea(\Common\UtilityBundle\Entity\FgSmAdArea $adArea = null)
    {
        $this->adArea = $adArea;
    
        return $this;
    }

    /**
     * Get adArea
     *
     * @return \Common\UtilityBundle\Entity\FgSmAdArea 
     */
    public function getAdArea()
    {
        return $this->adArea;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     * @return FgSmSponsorAds
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
     * Set contact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $contact
     * @return FgSmSponsorAds
     */
    public function setContact(\Common\UtilityBundle\Entity\FgCmContact $contact = null)
    {
        $this->contact = $contact;
    
        return $this;
    }

    /**
     * Get contact
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact 
     */
    public function getContact()
    {
        return $this->contact;
    }
    /**
     * @var integer
     */
    private $sortOrder;


    /**
     * Set sortOrder
     *
     * @param integer $sortOrder
     * @return FgSmSponsorAds
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
}