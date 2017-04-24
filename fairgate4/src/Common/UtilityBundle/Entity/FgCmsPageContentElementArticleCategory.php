<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgCmsPageContentElementArticleCategory
 */
class FgCmsPageContentElementArticleCategory
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
     * Set element
     *
     * @param \Common\UtilityBundle\Entity\FgCmsPageContentElement $element
     *
     * @return FgCmsPageContentElementArticleCategory
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
     * @param \Common\UtilityBundle\Entity\FgCmsArticleCategory $category
     *
     * @return FgCmsPageContentElementArticleCategory
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

