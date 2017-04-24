<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgCmsPageContentTextElementHistory
 */
class FgCmsPageContentTextElementHistory
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $text;

    /**
     * @var \DateTime
     */
    private $lastEditedDate;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmsPageContentTextElement
     */
    private $textElement;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $lastEditedBy;


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
     * Set text
     *
     * @param string $text
     *
     * @return FgCmsPageContentTextElementHistory
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
     * Set lastEditedDate
     *
     * @param \DateTime $lastEditedDate
     *
     * @return FgCmsPageContentTextElementHistory
     */
    public function setLastEditedDate($lastEditedDate)
    {
        $this->lastEditedDate = $lastEditedDate;

        return $this;
    }

    /**
     * Get lastEditedDate
     *
     * @return \DateTime
     */
    public function getLastEditedDate()
    {
        return $this->lastEditedDate;
    }

    /**
     * Set textElement
     *
     * @param \Common\UtilityBundle\Entity\FgCmsPageContentTextElement $textElement
     *
     * @return FgCmsPageContentTextElementHistory
     */
    public function setTextElement(\Common\UtilityBundle\Entity\FgCmsPageContentTextElement $textElement = null)
    {
        $this->textElement = $textElement;

        return $this;
    }

    /**
     * Get textElement
     *
     * @return \Common\UtilityBundle\Entity\FgCmsPageContentTextElement
     */
    public function getTextElement()
    {
        return $this->textElement;
    }

    /**
     * Set lastEditedBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $lastEditedBy
     *
     * @return FgCmsPageContentTextElementHistory
     */
    public function setLastEditedBy(\Common\UtilityBundle\Entity\FgCmContact $lastEditedBy = null)
    {
        $this->lastEditedBy = $lastEditedBy;

        return $this;
    }

    /**
     * Get lastEditedBy
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact
     */
    public function getLastEditedBy()
    {
        return $this->lastEditedBy;
    }
}

