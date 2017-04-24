<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCmsPageContentMedia
 */
class FgCmsPageContentMedia
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
     * @var string
     */
    private $imageElementLinkType;

    /**
     * @var string
     */
    private $imageElementExternalLink;

    /**
     * @var \Common\UtilityBundle\Entity\FgGmItems
     */
    private $item;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmsPageContentTextElement
     */
    private $textElement;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmsPageContentElement
     */
    private $element;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmsNavigation
     */
    private $navigation;

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
     * @return FgCmsPageContentMedia
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
     * Set imageElementLinkType
     *
     * @param string $imageElementLinkType
     * @return FgCmsPageContentMedia
     */
    public function setImageElementLinkType($imageElementLinkType)
    {
        $this->imageElementLinkType = $imageElementLinkType;
    
        return $this;
    }

    /**
     * Get imageElementLinkType
     *
     * @return string 
     */
    public function getImageElementLinkType()
    {
        return $this->imageElementLinkType;
    }

    /**
     * Set imageElementExternalLink
     *
     * @param string $imageElementExternalLink
     * @return FgCmsPageContentMedia
     */
    public function setImageElementExternalLink($imageElementExternalLink)
    {
        $this->imageElementExternalLink = $imageElementExternalLink;
    
        return $this;
    }

    /**
     * Get imageElementExternalLink
     *
     * @return string 
     */
    public function getImageElementExternalLink()
    {
        return $this->imageElementExternalLink;
    }

    /**
     * Set item
     *
     * @param \Common\UtilityBundle\Entity\FgGmItems $item
     * @return FgCmsPageContentMedia
     */
    public function setItem(\Common\UtilityBundle\Entity\FgGmItems $item = null)
    {
        $this->item = $item;
    
        return $this;
    }

    /**
     * Get item
     *
     * @return \Common\UtilityBundle\Entity\FgGmItems 
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Set textElement
     *
     * @param \Common\UtilityBundle\Entity\FgCmsPageContentTextElement $textElement
     * @return FgCmsPageContentMedia
     */
    public function setTextElement(\Common\UtilityBundle\Entity\FgCmsPageContentTextElement $textElement = null)
    {
        $this->textElement = $textElement;
    
        return $this;
    }

    /**
     * Get textElement
     *
     * @return \Common\UtilityBundle\Entity\FgCmsPageContentTextElement 
     */
    public function getTextElement()
    {
        return $this->textElement;
    }

    /**
     * Set element
     *
     * @param \Common\UtilityBundle\Entity\FgCmsPageContentElement $element
     * @return FgCmsPageContentMedia
     */
    public function setElement(\Common\UtilityBundle\Entity\FgCmsPageContentElement $element = null)
    {
        $this->element = $element;
    
        return $this;
    }

    /**
     * Get element
     *
     * @return \Common\UtilityBundle\Entity\FgCmsPageContentElement 
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * Set navigation
     *
     * @param \Common\UtilityBundle\Entity\FgCmsNavigation $navigation
     * @return FgCmsPageContentMedia
     */
    public function setNavigation(\Common\UtilityBundle\Entity\FgCmsNavigation $navigation = null)
    {
        $this->navigation = $navigation;
    
        return $this;
}

    /**
     * Get navigation
     *
     * @return \Common\UtilityBundle\Entity\FgCmsNavigation 
     */
    public function getNavigation()
    {
        return $this->navigation;
    }
    /**
     * @var string
     */
    private $linkOpenType;
    

    /**
     * Set linkOpenType
     *
     * @param string $linkOpenType
     * @return FgCmsPageContentMedia
     */
    public function setLinkOpenType($linkOpenType)
    {
        $this->linkOpenType = $linkOpenType;
    
        return $this;
    }

    /**
     * Get linkOpenType
     *
     * @return string 
     */
    public function getLinkOpenType()
    {
        return $this->linkOpenType;
    }
}