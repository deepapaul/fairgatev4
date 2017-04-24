<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCnNewsletterContent
 */
class FgCnNewsletterContent
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $contentType;

    /**
     * @var string
     */
    private $imagePath;

    /**
     * @var integer
     */
    private $sortOrder;

    /**
     * @var boolean
     */
    private $isActive;

    /**
     * @var string
     */
    private $introClosingWords;

    /**
     * @var string
     */
    private $picturePosition;

    /**
     * @var string
     */
    private $imageLink;

    /**
     * @var string
     */
    private $sponsorAdWidth;

    /**
     * @var string
     */
    private $contentTitle;
    
    /**
     * @var string
     */
    private $articleLang; 
    
    /**
     * @var boolean
     */
    private $includeAttachments;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmsArticle
     */
    private $article;

    /**
     * @var \Common\UtilityBundle\Entity\FgCnNewsletter
     */
    private $newsletter;

    /**
     * @var \Common\UtilityBundle\Entity\FgGmItems
     */
    private $items;

    /**
     * @var \Common\UtilityBundle\Entity\FgSmAdArea
     */
    private $sponsorAdArea;


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
     * Set contentType
     *
     * @param string $contentType
     * @return FgCnNewsletterContent
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    
        return $this;
    }

    /**
     * Get contentType
     *
     * @return string 
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Set imagePath
     *
     * @param string $imagePath
     * @return FgCnNewsletterContent
     */
    public function setImagePath($imagePath)
    {
        $this->imagePath = $imagePath;
    
        return $this;
    }

    /**
     * Get imagePath
     *
     * @return string 
     */
    public function getImagePath()
    {
        return $this->imagePath;
    }

    /**
     * Set sortOrder
     *
     * @param integer $sortOrder
     * @return FgCnNewsletterContent
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
     * Set isActive
     *
     * @param boolean $isActive
     * @return FgCnNewsletterContent
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

    /**
     * Set introClosingWords
     *
     * @param string $introClosingWords
     * @return FgCnNewsletterContent
     */
    public function setIntroClosingWords($introClosingWords)
    {
        $this->introClosingWords = $introClosingWords;
    
        return $this;
    }

    /**
     * Get introClosingWords
     *
     * @return string 
     */
    public function getIntroClosingWords()
    {
        return $this->introClosingWords;
    }

    /**
     * Set picturePosition
     *
     * @param string $picturePosition
     * @return FgCnNewsletterContent
     */
    public function setPicturePosition($picturePosition)
    {
        $this->picturePosition = $picturePosition;
    
        return $this;
    }

    /**
     * Get picturePosition
     *
     * @return string 
     */
    public function getPicturePosition()
    {
        return $this->picturePosition;
    }

    /**
     * Set imageLink
     *
     * @param string $imageLink
     * @return FgCnNewsletterContent
     */
    public function setImageLink($imageLink)
    {
        $this->imageLink = $imageLink;
    
        return $this;
    }

    /**
     * Get imageLink
     *
     * @return string 
     */
    public function getImageLink()
    {
        return $this->imageLink;
    }

    /**
     * Set sponsorAdWidth
     *
     * @param string $sponsorAdWidth
     * @return FgCnNewsletterContent
     */
    public function setSponsorAdWidth($sponsorAdWidth)
    {
        $this->sponsorAdWidth = $sponsorAdWidth;
    
        return $this;
    }

    /**
     * Get sponsorAdWidth
     *
     * @return string 
     */
    public function getSponsorAdWidth()
    {
        return $this->sponsorAdWidth;
    }

    /**
     * Set contentTitle
     *
     * @param string $contentTitle
     * @return FgCnNewsletterContent
     */
    public function setContentTitle($contentTitle)
    {
        $this->contentTitle = $contentTitle;
    
        return $this;
    }

    /**
     * Get contentTitle
     *
     * @return string 
     */
    public function getContentTitle()
    {
        return $this->contentTitle;
    }

    /**
     * Set includeAttachments
     *
     * @param boolean $includeAttachments
     * @return FgCnNewsletterContent
     */
    public function setIncludeAttachments($includeAttachments)
    {
        $this->includeAttachments = $includeAttachments;
    
        return $this;
    }

    /**
     * Get includeAttachments
     *
     * @return boolean 
     */
    public function getIncludeAttachments()
    {
        return $this->includeAttachments;
    }

    /**
     * Set article
     *
     * @param \Common\UtilityBundle\Entity\FgCmsArticle $article
     * @return FgCnNewsletterContent
     */
    public function setArticle(\Common\UtilityBundle\Entity\FgCmsArticle $article = null)
    {
        $this->article = $article;
    
        return $this;
    }

    /**
     * Get article
     *
     * @return \Common\UtilityBundle\Entity\FgCmsArticle 
     */
    public function getArticle()
    {
        return $this->article;
    }

    /**
     * Set newsletter
     *
     * @param \Common\UtilityBundle\Entity\FgCnNewsletter $newsletter
     * @return FgCnNewsletterContent
     */
    public function setNewsletter(\Common\UtilityBundle\Entity\FgCnNewsletter $newsletter = null)
    {
        $this->newsletter = $newsletter;
    
        return $this;
    }

    /**
     * Get newsletter
     *
     * @return \Common\UtilityBundle\Entity\FgCnNewsletter 
     */
    public function getNewsletter()
    {
        return $this->newsletter;
    }

    /**
     * Set items
     *
     * @param \Common\UtilityBundle\Entity\FgGmItems $items
     * @return FgCnNewsletterContent
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
     * Set sponsorAdArea
     *
     * @param \Common\UtilityBundle\Entity\FgSmAdArea $sponsorAdArea
     * @return FgCnNewsletterContent
     */
    public function setSponsorAdArea(\Common\UtilityBundle\Entity\FgSmAdArea $sponsorAdArea = null)
    {
        $this->sponsorAdArea = $sponsorAdArea;
    
        return $this;
    }

    /**
     * Get sponsorAdArea
     *
     * @return \Common\UtilityBundle\Entity\FgSmAdArea 
     */
    public function getSponsorAdArea()
    {
        return $this->sponsorAdArea;
    }
    
    /**
     * Set articleLang
     *
     * @param string $articleLang
     * @return FgCnNewsletterContent
     */
    public function setArticleLang($articleLang)
    {
        $this->articleLang = $articleLang;
    
        return $this;
    }

    /**
     * Get articleLang
     *
     * @return string 
     */
    public function getArticleLang()
    {
        return $this->articleLang;
    }
}
