<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCmsPage
 */
class FgCmsPage
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
    private $type;

    /**
     * @var string
     */
    private $sidebarType;

    /**
     * @var string
     */
    private $sidebarArea;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $editedAt;

    /**
     * @var \DateTime
     */
    private $contentUpdateTime;

    /**
     * @var boolean
     */
    private $isAllArea;

    /**
     * @var boolean
     */
    private $isAllCategory;

    /**
     * @var boolean
     */
    private $isAllGalleries;

    /**
     * @var string
     */
    private $sharedClub;

    /**
     * @var boolean
     */
    private $areaClub;

    /**
     * @var string
     */
    private $pageContentJson;

    /**
     * @var boolean
     */
    private $hideTitle;

    /**
     * @var string
     */
    private $pageElement;

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
    private $editedBy;


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
     * @return FgCmsPage
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
     * Set type
     *
     * @param string $type
     * @return FgCmsPage
     */
    public function setType($type)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set sidebarType
     *
     * @param string $sidebarType
     * @return FgCmsPage
     */
    public function setSidebarType($sidebarType)
    {
        $this->sidebarType = $sidebarType;
    
        return $this;
    }

    /**
     * Get sidebarType
     *
     * @return string 
     */
    public function getSidebarType()
    {
        return $this->sidebarType;
    }

    /**
     * Set sidebarArea
     *
     * @param string $sidebarArea
     * @return FgCmsPage
     */
    public function setSidebarArea($sidebarArea)
    {
        $this->sidebarArea = $sidebarArea;
    
        return $this;
    }

    /**
     * Get sidebarArea
     *
     * @return string 
     */
    public function getSidebarArea()
    {
        return $this->sidebarArea;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return FgCmsPage
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
     * Set editedAt
     *
     * @param \DateTime $editedAt
     * @return FgCmsPage
     */
    public function setEditedAt($editedAt)
    {
        $this->editedAt = $editedAt;
    
        return $this;
    }

    /**
     * Get editedAt
     *
     * @return \DateTime 
     */
    public function getEditedAt()
    {
        return $this->editedAt;
    }

    /**
     * Set contentUpdateTime
     *
     * @param \DateTime $contentUpdateTime
     * @return FgCmsPage
     */
    public function setContentUpdateTime($contentUpdateTime)
    {
        $this->contentUpdateTime = $contentUpdateTime;
    
        return $this;
    }

    /**
     * Get contentUpdateTime
     *
     * @return \DateTime 
     */
    public function getContentUpdateTime()
    {
        return $this->contentUpdateTime;
    }

    /**
     * Set isAllArea
     *
     * @param boolean $isAllArea
     * @return FgCmsPage
     */
    public function setIsAllArea($isAllArea)
    {
        $this->isAllArea = $isAllArea;
    
        return $this;
    }

    /**
     * Get isAllArea
     *
     * @return boolean 
     */
    public function getIsAllArea()
    {
        return $this->isAllArea;
    }

    /**
     * Set isAllCategory
     *
     * @param boolean $isAllCategory
     * @return FgCmsPage
     */
    public function setIsAllCategory($isAllCategory)
    {
        $this->isAllCategory = $isAllCategory;
    
        return $this;
    }

    /**
     * Get isAllCategory
     *
     * @return boolean 
     */
    public function getIsAllCategory()
    {
        return $this->isAllCategory;
    }

    /**
     * Set isAllGalleries
     *
     * @param boolean $isAllGalleries
     * @return FgCmsPage
     */
    public function setIsAllGalleries($isAllGalleries)
    {
        $this->isAllGalleries = $isAllGalleries;
    
        return $this;
    }

    /**
     * Get isAllGalleries
     *
     * @return boolean 
     */
    public function getIsAllGalleries()
    {
        return $this->isAllGalleries;
    }

    /**
     * Set sharedClub
     *
     * @param string $sharedClub
     * @return FgCmsPage
     */
    public function setSharedClub($sharedClub)
    {
        $this->sharedClub = $sharedClub;
    
        return $this;
    }

    /**
     * Get sharedClub
     *
     * @return string 
     */
    public function getSharedClub()
    {
        return $this->sharedClub;
    }

    /**
     * Set areaClub
     *
     * @param boolean $areaClub
     * @return FgCmsPage
     */
    public function setAreaClub($areaClub)
    {
        $this->areaClub = $areaClub;
    
        return $this;
    }

    /**
     * Get areaClub
     *
     * @return boolean 
     */
    public function getAreaClub()
    {
        return $this->areaClub;
    }

    /**
     * Set pageContentJson
     *
     * @param string $pageContentJson
     * @return FgCmsPage
     */
    public function setPageContentJson($pageContentJson)
    {
        $this->pageContentJson = $pageContentJson;
    
        return $this;
    }

    /**
     * Get pageContentJson
     *
     * @return string 
     */
    public function getPageContentJson()
    {
        return $this->pageContentJson;
    }

    /**
     * Set hideTitle
     *
     * @param boolean $hideTitle
     * @return FgCmsPage
     */
    public function setHideTitle($hideTitle)
    {
        $this->hideTitle = $hideTitle;
    
        return $this;
    }

    /**
     * Get hideTitle
     *
     * @return boolean 
     */
    public function getHideTitle()
    {
        return $this->hideTitle;
    }

    /**
     * Set pageElement
     *
     * @param string $pageElement
     * @return FgCmsPage
     */
    public function setPageElement($pageElement)
    {
        $this->pageElement = $pageElement;
    
        return $this;
    }

    /**
     * Get pageElement
     *
     * @return string 
     */
    public function getPageElement()
    {
        return $this->pageElement;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     * @return FgCmsPage
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
     * @return FgCmsPage
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
     * Set editedBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $editedBy
     * @return FgCmsPage
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
     * @var string
     */
    private $opengraphDetails;


    /**
     * Set opengraphDetails
     *
     * @param string $opengraphDetails
     * @return FgCmsPage
     */
    public function setOpengraphDetails($opengraphDetails)
    {
        $this->opengraphDetails = $opengraphDetails;
    
        return $this;
}

    /**
     * Get opengraphDetails
     *
     * @return string 
     */
    public function getOpengraphDetails()
    {
        return $this->opengraphDetails;
    }
}