<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgCmsArticleMedia
 */
class FgCmsArticleMedia
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
     * @var \Common\UtilityBundle\Entity\FgGmItems
     */
    private $items;


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
     *
     * @return FgCmsArticleMedia
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
     *
     * @return FgCmsArticleMedia
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
     * Set items
     *
     * @param \Common\UtilityBundle\Entity\FgGmItems $items
     *
     * @return FgCmsArticleMedia
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
}

