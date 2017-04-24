<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCmsPageCategories
 */
class FgCmsPageCalendarCategories
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Common\UtilityBundle\Entity\FgEmCalendarCategory
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
     * @var \Common\UtilityBundle\Entity\FgEmCalendarCategory
     * @return FgCmsPageCategories
     */
    public function setCategory(\Common\UtilityBundle\Entity\FgEmCalendarCategory $category = null)
    {
        $this->category = $category;
    
        return $this;
    }

    /**
     * Get category
     *
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
