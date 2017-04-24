<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCmsPageCategories
 */
class FgCmsPageArticleCategories
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @param \Common\UtilityBundle\Entity\FgCmsArticleCategory $category
     */
    private $category;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmsPage
     */
    private $page;


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
     * Set category
     *
     * @param \Common\UtilityBundle\Entity\FgCmsArticleCategory $category
     * @return FgCmsPageCategories
     */
    public function setCategory(\Common\UtilityBundle\Entity\FgCmsArticleCategory $category = null)
    {
        $this->category = $category;
    
        return $this;
    }

    /**
     * Get category
     * @return integer 
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set page
     *
     * @param \Common\UtilityBundle\Entity\FgCmsPage $page
     * @return FgCmsPageCategories
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
}
