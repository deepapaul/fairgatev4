<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgCmChangeLog
 */
class FgCmChangeLog
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
    private $kind;

    /**
     * @var string
     */
    private $field;

    /**
     * @var string
     */
    private $valueBefore;

    /**
     * @var string
     */
    private $valueAfter;

    /**
     * @var boolean
     */
    private $isConfirmed;

    /**
     * @var integer
     */
    private $historicalId;

    /**
     * @var boolean
     */
    private $isHistorical;

    /**
     * @var integer
     */
    private $attributeId;

    /**
     * @var \DateTime
     */
    private $confirmedDate;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $contact;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $changedBy;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $confirmedBy;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;

    /**
     * @var \Common\UtilityBundle\Entity\FgCnNewsletter
     */
    private $newsletter;


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
     * @return FgCmChangeLog
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
     * Set kind
     *
     * @param string $kind
     *
     * @return FgCmChangeLog
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
     * Set field
     *
     * @param string $field
     *
     * @return FgCmChangeLog
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
     * Set valueBefore
     *
     * @param string $valueBefore
     *
     * @return FgCmChangeLog
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
     * Set valueAfter
     *
     * @param string $valueAfter
     *
     * @return FgCmChangeLog
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
     * Set isConfirmed
     *
     * @param boolean $isConfirmed
     *
     * @return FgCmChangeLog
     */
    public function setIsConfirmed($isConfirmed)
    {
        $this->isConfirmed = $isConfirmed;

        return $this;
    }

    /**
     * Get isConfirmed
     *
     * @return boolean
     */
    public function getIsConfirmed()
    {
        return $this->isConfirmed;
    }

    /**
     * Set historicalId
     *
     * @param integer $historicalId
     *
     * @return FgCmChangeLog
     */
    public function setHistoricalId($historicalId)
    {
        $this->historicalId = $historicalId;

        return $this;
    }

    /**
     * Get historicalId
     *
     * @return integer
     */
    public function getHistoricalId()
    {
        return $this->historicalId;
    }

    /**
     * Set isHistorical
     *
     * @param boolean $isHistorical
     *
     * @return FgCmChangeLog
     */
    public function setIsHistorical($isHistorical)
    {
        $this->isHistorical = $isHistorical;

        return $this;
    }

    /**
     * Get isHistorical
     *
     * @return boolean
     */
    public function getIsHistorical()
    {
        return $this->isHistorical;
    }

    /**
     * Set attributeId
     *
     * @param integer $attributeId
     *
     * @return FgCmChangeLog
     */
    public function setAttributeId($attributeId)
    {
        $this->attributeId = $attributeId;

        return $this;
    }

    /**
     * Get attributeId
     *
     * @return integer
     */
    public function getAttributeId()
    {
        return $this->attributeId;
    }

    /**
     * Set confirmedDate
     *
     * @param \DateTime $confirmedDate
     *
     * @return FgCmChangeLog
     */
    public function setConfirmedDate($confirmedDate)
    {
        $this->confirmedDate = $confirmedDate;

        return $this;
    }

    /**
     * Get confirmedDate
     *
     * @return \DateTime
     */
    public function getConfirmedDate()
    {
        return $this->confirmedDate;
    }

    /**
     * Set contact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $contact
     *
     * @return FgCmChangeLog
     */
    public function setContact(\Common\UtilityBundle\Entity\FgCmContact $contact = null)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Get contact
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Set changedBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $changedBy
     *
     * @return FgCmChangeLog
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
     * Set confirmedBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $confirmedBy
     *
     * @return FgCmChangeLog
     */
    public function setConfirmedBy(\Common\UtilityBundle\Entity\FgCmContact $confirmedBy = null)
    {
        $this->confirmedBy = $confirmedBy;

        return $this;
    }

    /**
     * Get confirmedBy
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact
     */
    public function getConfirmedBy()
    {
        return $this->confirmedBy;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     *
     * @return FgCmChangeLog
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
     * Set newsletter
     *
     * @param \Common\UtilityBundle\Entity\FgCnNewsletter $newsletter
     *
     * @return FgCmChangeLog
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
}

