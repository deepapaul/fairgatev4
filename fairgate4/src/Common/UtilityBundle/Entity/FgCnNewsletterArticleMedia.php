<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCnNewsletterArticleMedia
 */
class FgCnNewsletterArticleMedia
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $galleryItem;

    /**
     * @var string
     */
    private $mediaText;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $mediaType;

    /**
     * @var integer
     */
    private $sortOrder;

    /**
     * @var \Common\UtilityBundle\Entity\FgCnNewsletterArticle
     */
    private $article;


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
     * Set galleryItem
     *
     * @param \Common\UtilityBundle\Entity\FgGmItems $galleryItem
     * @return FgCnNewsletterArticleMedia
     */
    public function setGalleryItem($galleryItem)
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
     * Set mediaText
     *
     * @param string $mediaText
     * @return FgCnNewsletterArticleMedia
     */
    public function setMediaText($mediaText)
    {
        $this->mediaText = $mediaText;
    
        return $this;
    }

    /**
     * Get mediaText
     *
     * @return string 
     */
    public function getMediaText()
    {
        return $this->mediaText;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return FgCnNewsletterArticleMedia
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
     * Set mediaType
     *
     * @param string $mediaType
     * @return FgCnNewsletterArticleMedia
     */
    public function setMediaType($mediaType)
    {
        $this->mediaType = $mediaType;
    
        return $this;
    }

    /**
     * Get mediaType
     *
     * @return string 
     */
    public function getMediaType()
    {
        return $this->mediaType;
    }

    /**
     * Set sortOrder
     *
     * @param integer $sortOrder
     * @return FgCnNewsletterArticleMedia
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
     * Set article
     *
     * @param \Common\UtilityBundle\Entity\FgCnNewsletterArticle $article
     * @return FgCnNewsletterArticleMedia
     */
    public function setArticle(\Common\UtilityBundle\Entity\FgCnNewsletterArticle $article = null)
    {
        $this->article = $article;
    
        return $this;
    }

    /**
     * Get article
     *
     * @return \Common\UtilityBundle\Entity\FgCnNewsletterArticle 
     */
    public function getArticle()
    {
        return $this->article;
    }
    /**
     * @var \Common\UtilityBundle\Entity\FgFileManager
     */
    private $fileManager;


    /**
     * Set fileManager
     *
     * @param \Common\UtilityBundle\Entity\FgFileManager $fileManager
     * @return FgCnNewsletterArticleMedia
     */
    public function setFileManager(\Common\UtilityBundle\Entity\FgFileManager $fileManager = null)
    {
        $this->fileManager = $fileManager;
    
        return $this;
    }

    /**
     * Get fileManager
     *
     * @return \Common\UtilityBundle\Entity\FgFileManager 
     */
    public function getFileManager()
    {
        return $this->fileManager;
    }
}
