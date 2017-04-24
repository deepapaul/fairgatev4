<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCmsArticleSelectedcategories
 */
class FgCmsArticleSelectedcategories
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmsArticle
     */
    private $article;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmsArticleCategory
     */
    private $category;


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
     * Set article
     *
     * @param \Common\UtilityBundle\Entity\FgCmsArticle $article
     * @return FgCmsArticleSelectedcategories
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
     * Set category
     *
     * @param \Common\UtilityBundle\Entity\FgCmsArticleCategory $category
     * @return FgCmsArticleSelectedcategories
     */
    public function setCategory(\Common\UtilityBundle\Entity\FgCmsArticleCategory $category = null)
    {
        $this->category = $category;
    
        return $this;
    }

    /**
     * Get category
     *
     * @return \Common\UtilityBundle\Entity\FgCmsArticleCategory 
     */
    public function getCategory()
    {
        return $this->category;
    }
}
