<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgCmsArticleText
 */
class FgCmsArticleText
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $teaser;

    /**
     * @var string
     */
    private $text;

    /**
     * @var \DateTime
     */
    private $lastEditedon;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $lastEditedby;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmsArticle
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
     * Set title
     *
     * @param string $title
     *
     * @return FgCmsArticleText
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set teaser
     *
     * @param string $teaser
     *
     * @return FgCmsArticleText
     */
    public function setTeaser($teaser)
    {
        $this->teaser = $teaser;

        return $this;
    }

    /**
     * Get teaser
     *
     * @return string
     */
    public function getTeaser()
    {
        return $this->teaser;
    }

    /**
     * Set text
     *
     * @param string $text
     *
     * @return FgCmsArticleText
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set lastEditedon
     *
     * @param \DateTime $lastEditedon
     *
     * @return FgCmsArticleText
     */
    public function setLastEditedon($lastEditedon)
    {
        $this->lastEditedon = $lastEditedon;

        return $this;
    }

    /**
     * Get lastEditedon
     *
     * @return \DateTime
     */
    public function getLastEditedon()
    {
        return $this->lastEditedon;
    }

    /**
     * Set lastEditedby
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $lastEditedby
     *
     * @return FgCmsArticleText
     */
    public function setLastEditedby(\Common\UtilityBundle\Entity\FgCmContact $lastEditedby = null)
    {
        $this->lastEditedby = $lastEditedby;

        return $this;
    }

    /**
     * Get lastEditedby
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact
     */
    public function getLastEditedby()
    {
        return $this->lastEditedby;
    }

    /**
     * Set article
     *
     * @param \Common\UtilityBundle\Entity\FgCmsArticle $article
     *
     * @return FgCmsArticleText
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
}

