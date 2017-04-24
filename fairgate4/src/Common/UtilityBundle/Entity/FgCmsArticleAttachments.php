<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCmsArticleAttachments
 */
class FgCmsArticleAttachments
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
     * @var \Common\UtilityBundle\Entity\FgCmsArticle
     */
    private $article;

    /**
     * @var \Common\UtilityBundle\Entity\FgFileManager
     */
    private $filemanager;


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
     * @return FgCmsArticleAttachments
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
     * @param \Common\UtilityBundle\Entity\FgCmsArticle $article
     * @return FgCmsArticleAttachments
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
     * Set filemanager
     *
     * @param \Common\UtilityBundle\Entity\FgFileManager $filemanager
     * @return FgCmsArticleAttachments
     */
    public function setFilemanager(\Common\UtilityBundle\Entity\FgFileManager $filemanager = null)
    {
        $this->filemanager = $filemanager;
    
        return $this;
    }

    /**
     * Get filemanager
     *
     * @return \Common\UtilityBundle\Entity\FgFileManager 
     */
    public function getFilemanager()
    {
        return $this->filemanager;
    }
}
