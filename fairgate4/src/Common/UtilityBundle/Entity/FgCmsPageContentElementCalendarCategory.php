<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCmsPageContentElementCalendarCategory
 */
class FgCmsPageContentElementCalendarCategory
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmsPageContentElement
     */
    private $element;

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
     * Set element
     *
     * @param \Common\UtilityBundle\Entity\FgCmsPageContentElement $element
     * @return FgCmsPageContentElementCalendarCategory
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
     * Set category
     *
     * @param \Common\UtilityBundle\Entity\FgEmCalendarCategory $category
     * @return FgCmsPageContentElementCalendarCategory
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