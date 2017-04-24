<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgCnNewsletterArticleDocuments
 */
class FgCnNewsletterArticleDocuments
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
     * @var string
     */
    private $docType;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var string
     */
    private $title;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \Common\UtilityBundle\Entity\FgFileManager
     */
    private $fileManager;

    /**
     * @var \Common\UtilityBundle\Entity\FgDmDocuments
     */
    private $documents;

    /**
     * @var \Common\UtilityBundle\Entity\FgCnNewsletter
     */
    private $newsletter;

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
     * Set sortOrder
     *
     * @param integer $sortOrder
     *
     * @return FgCnNewsletterArticleDocuments
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
     * Set docType
     *
     * @param string $docType
     *
     * @return FgCnNewsletterArticleDocuments
     */
    public function setDocType($docType)
    {
        $this->docType = $docType;

        return $this;
    }

    /**
     * Get docType
     *
     * @return string
     */
    public function getDocType()
    {
        return $this->docType;
    }

    /**
     * Set filename
     *
     * @param string $filename
     *
     * @return FgCnNewsletterArticleDocuments
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return FgCnNewsletterArticleDocuments
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return FgCnNewsletterArticleDocuments
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set fileManager
     *
     * @param \Common\UtilityBundle\Entity\FgFileManager $fileManager
     *
     * @return FgCnNewsletterArticleDocuments
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

    /**
     * Set documents
     *
     * @param \Common\UtilityBundle\Entity\FgDmDocuments $documents
     *
     * @return FgCnNewsletterArticleDocuments
     */
    public function setDocuments(\Common\UtilityBundle\Entity\FgDmDocuments $documents = null)
    {
        $this->documents = $documents;

        return $this;
    }

    /**
     * Get documents
     *
     * @return \Common\UtilityBundle\Entity\FgDmDocuments
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * Set newsletter
     *
     * @param \Common\UtilityBundle\Entity\FgCnNewsletter $newsletter
     *
     * @return FgCnNewsletterArticleDocuments
     */
    public function setNewsletter(\Common\UtilityBundle\Entity\FgCnNewsletter $newsletter = null)
    {
        $this->newsletter = $newsletter;

        return $this;
    }

    /**
     * Get newsletter
     *
     * @return \Common\UtilityBundle\Entity\FgCnNewsletter
     */
    public function getNewsletter()
    {
        return $this->newsletter;
    }

    /**
     * Set article
     *
     * @param \Common\UtilityBundle\Entity\FgCnNewsletterArticle $article
     *
     * @return FgCnNewsletterArticleDocuments
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
}

