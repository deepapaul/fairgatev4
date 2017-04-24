<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgCmsArticleLog
 */
class FgCmsArticleLog
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var string
     */
    private $field;

    /**
     * @var string
     */
    private $kind;

    /**
     * @var string
     */
    private $valueAfter;

    /**
     * @var string
     */
    private $valueBefore;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $changedBy;

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
     * Set date
     *
     * @param \DateTime $date
     *
     * @return FgCmsArticleLog
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set field
     *
     * @param string $field
     *
     * @return FgCmsArticleLog
     */
    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * Get field
     *
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Set kind
     *
     * @param string $kind
     *
     * @return FgCmsArticleLog
     */
    public function setKind($kind)
    {
        $this->kind = $kind;

        return $this;
    }

    /**
     * Get kind
     *
     * @return string
     */
    public function getKind()
    {
        return $this->kind;
    }

    /**
     * Set valueAfter
     *
     * @param string $valueAfter
     *
     * @return FgCmsArticleLog
     */
    public function setValueAfter($valueAfter)
    {
        $this->valueAfter = $valueAfter;

        return $this;
    }

    /**
     * Get valueAfter
     *
     * @return string
     */
    public function getValueAfter()
    {
        return $this->valueAfter;
    }

    /**
     * Set valueBefore
     *
     * @param string $valueBefore
     *
     * @return FgCmsArticleLog
     */
    public function setValueBefore($valueBefore)
    {
        $this->valueBefore = $valueBefore;

        return $this;
    }

    /**
     * Get valueBefore
     *
     * @return string
     */
    public function getValueBefore()
    {
        return $this->valueBefore;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     *
     * @return FgCmsArticleLog
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

    /**
     * Set changedBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $changedBy
     *
     * @return FgCmsArticleLog
     */
    public function setChangedBy(\Common\UtilityBundle\Entity\FgCmContact $changedBy = null)
    {
        $this->changedBy = $changedBy;

        return $this;
    }

    /**
     * Get changedBy
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact
     */
    public function getChangedBy()
    {
        return $this->changedBy;
    }

    /**
     * Set article
     *
     * @param \Common\UtilityBundle\Entity\FgCmsArticle $article
     *
     * @return FgCmsArticleLog
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

