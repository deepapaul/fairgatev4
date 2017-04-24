<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCnNewsletterIntroClosingWords
 */
class FgCnNewsletterIntroClosingWords
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
    private $introText;

    /**
     * @var string
     */
    private $wordType;

    /**
     * @var integer
     */
    private $sortOrder;

    /**
     * @var \DateTime
     */
    private $updatedDate;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $updatedBy;


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
     * @return FgCnNewsletterIntroClosingWords
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
     * Set introText
     *
     * @param string $introText
     * @return FgCnNewsletterIntroClosingWords
     */
    public function setIntroText($introText)
    {
        $this->introText = $introText;

        return $this;
    }

    /**
     * Get introText
     *
     * @return string
     */
    public function getIntroText()
    {
        return $this->introText;
    }

    /**
     * Set wordType
     *
     * @param string $wordType
     * @return FgCnNewsletterIntroClosingWords
     */
    public function setWordType($wordType)
    {
        $this->wordType = $wordType;

        return $this;
    }

    /**
     * Get wordType
     *
     * @return string
     */
    public function getWordType()
    {
        return $this->wordType;
    }

    /**
     * Set sortOrder
     *
     * @param integer $sortOrder
     * @return FgCnNewsletterIntroClosingWords
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
     * Set updatedDate
     *
     * @param \DateTime $updatedDate
     * @return FgCnNewsletterIntroClosingWords
     */
    public function setUpdatedDate($updatedDate)
    {
        $this->updatedDate = $updatedDate;

        return $this;
    }

    /**
     * Get updatedDate
     *
     * @return \DateTime
     */
    public function getUpdatedDate()
    {
        return $this->updatedDate;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     * @return FgCnNewsletterIntroClosingWords
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
     * Set updatedBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $updatedBy
     * @return FgCnNewsletterIntroClosingWords
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
}