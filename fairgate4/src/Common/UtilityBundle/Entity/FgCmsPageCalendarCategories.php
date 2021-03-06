<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgCmsPageCalendarCategories
 */
class FgCmsPageCalendarCategories
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmsPage
     */
    private $page;

    /**
     * @var \Common\UtilityBundle\Entity\FgEmCalendarCategory
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
     * Set page
     *
     * @param \Common\UtilityBundle\Entity\FgCmsPage $page
     *
     * @return FgCmsPageCalendarCategories
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
     * Set category
     *
     * @param \Common\UtilityBundle\Entity\FgEmCalendarCategory $category
     *
     * @return FgCmsPageCalendarCategories
     */
    public function setCategory(\Common\UtilityBundle\Entity\FgEmCalendarCategory $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \Common\UtilityBundle\Entity\FgEmCalendarCategory
     */
    public function getCategory()
    {
        return $this->category;
    }
}

