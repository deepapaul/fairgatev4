<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgCmsArticle
 */
class FgCmsArticle
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $publicationDate;

    /**
     * @var \DateTime
     */
    private $expiryDate;

    /**
     * @var string
     */
    private $author;

    /**
     * @var string
     */
    private $scope;

    /**
     * @var string
     */
    private $position;

    /**
     * @var integer
     */
    private $isDraft;

    /**
     * @var integer
     */
    private $commentAllow;

    /**
     * @var \DateTime
     */
    private $createdOn;

    /**
     * @var \DateTime
     */
    private $updatedOn;

    /**
     * @var integer
     */
    private $isDeleted;

    /**
     * @var boolean
     */
    private $shareWithLower;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmsArticleText
     */
    private $textversion;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $createdBy;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $updatedBy;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $archivedBy;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;


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
     * Set publicationDate
     *
     * @param \DateTime $publicationDate
     *
     * @return FgCmsArticle
     */
    public function setPublicationDate($publicationDate)
    {
        $this->publicationDate = $publicationDate;

        return $this;
    }

    /**
     * Get publicationDate
     *
     * @return \DateTime
     */
    public function getPublicationDate()
    {
        return $this->publicationDate;
    }

    /**
     * Set expiryDate
     *
     * @param \DateTime $expiryDate
     *
     * @return FgCmsArticle
     */
    public function setExpiryDate($expiryDate)
    {
        $this->expiryDate = $expiryDate;

        return $this;
    }

    /**
     * Get expiryDate
     *
     * @return \DateTime
     */
    public function getExpiryDate()
    {
        return $this->expiryDate;
    }

    /**
     * Set author
     *
     * @param string $author
     *
     * @return FgCmsArticle
     */
    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set scope
     *
     * @param string $scope
     *
     * @return FgCmsArticle
     */
    public function setScope($scope)
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * Get scope
     *
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Set position
     *
     * @param string $position
     *
     * @return FgCmsArticle
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set isDraft
     *
     * @param integer $isDraft
     *
     * @return FgCmsArticle
     */
    public function setIsDraft($isDraft)
    {
        $this->isDraft = $isDraft;

        return $this;
    }

    /**
     * Get isDraft
     *
     * @return integer
     */
    public function getIsDraft()
    {
        return $this->isDraft;
    }

    /**
     * Set commentAllow
     *
     * @param integer $commentAllow
     *
     * @return FgCmsArticle
     */
    public function setCommentAllow($commentAllow)
    {
        $this->commentAllow = $commentAllow;

        return $this;
    }

    /**
     * Get commentAllow
     *
     * @return integer
     */
    public function getCommentAllow()
    {
        return $this->commentAllow;
    }

    /**
     * Set createdOn
     *
     * @param \DateTime $createdOn
     *
     * @return FgCmsArticle
     */
    public function setCreatedOn($createdOn)
    {
        $this->createdOn = $createdOn;

        return $this;
    }

    /**
     * Get createdOn
     *
     * @return \DateTime
     */
    public function getCreatedOn()
    {
        return $this->createdOn;
    }

    /**
     * Set updatedOn
     *
     * @param \DateTime $updatedOn
     *
     * @return FgCmsArticle
     */
    public function setUpdatedOn($updatedOn)
    {
        $this->updatedOn = $updatedOn;

        return $this;
    }

    /**
     * Get updatedOn
     *
     * @return \DateTime
     */
    public function getUpdatedOn()
    {
        return $this->updatedOn;
    }

    /**
     * Set isDeleted
     *
     * @param integer $isDeleted
     *
     * @return FgCmsArticle
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /**
     * Get isDeleted
     *
     * @return integer
     */
    public function getIsDeleted()
    {
        return $this->isDeleted;
    }

    /**
     * Set shareWithLower
     *
     * @param boolean $shareWithLower
     *
     * @return FgCmsArticle
     */
    public function setShareWithLower($shareWithLower)
    {
        $this->shareWithLower = $shareWithLower;

        return $this;
    }

    /**
     * Get shareWithLower
     *
     * @return boolean
     */
    public function getShareWithLower()
    {
        return $this->shareWithLower;
    }

    /**
     * Set textversion
     *
     * @param \Common\UtilityBundle\Entity\FgCmsArticleText $textversion
     *
     * @return FgCmsArticle
     */
    public function setTextversion(\Common\UtilityBundle\Entity\FgCmsArticleText $textversion = null)
    {
        $this->textversion = $textversion;

        return $this;
    }

    /**
     * Get textversion
     *
     * @return \Common\UtilityBundle\Entity\FgCmsArticleText
     */
    public function getTextversion()
    {
        return $this->textversion;
    }

    /**
     * Set createdBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $createdBy
     *
     * @return FgCmsArticle
     */
    public function setCreatedBy(\Common\UtilityBundle\Entity\FgCmContact $createdBy = null)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set updatedBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $updatedBy
     *
     * @return FgCmsArticle
     */
    public function setUpdatedBy(\Common\UtilityBundle\Entity\FgCmContact $updatedBy = null)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Get updatedBy
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * Set archivedBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $archivedBy
     *
     * @return FgCmsArticle
     */
    public function setArchivedBy(\Common\UtilityBundle\Entity\FgCmContact $archivedBy = null)
    {
        $this->archivedBy = $archivedBy;

        return $this;
    }

    /**
     * Get archivedBy
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact
     */
    public function getArchivedBy()
    {
        return $this->archivedBy;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     *
     * @return FgCmsArticle
     */
    public function setClub(\Common\UtilityBundle\Entity\FgClub $club = null)
    {
        $this->club = $club;

        return $this;
    }

    /**
     * Get club
     *
     * @return \Common\UtilityBundle\Entity\FgClub
     */
    public function getClub()
    {
        return $this->club;
    }
}

