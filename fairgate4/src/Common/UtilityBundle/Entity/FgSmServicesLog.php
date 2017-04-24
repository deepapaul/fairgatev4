<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgSmServicesLog
 */
class FgSmServicesLog
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
     * @var string
     */
    private $actionType;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $sponsor;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $changedBy;

    /**
     * @var \Common\UtilityBundle\Entity\FgSmServices
     */
    private $service;


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
     * @return FgSmServicesLog
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
     * @return FgSmServicesLog
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
     * @return FgSmServicesLog
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
     * @return FgSmServicesLog
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
     * @return FgSmServicesLog
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
     * Set actionType
     *
     * @param string $actionType
     *
     * @return FgSmServicesLog
     */
    public function setActionType($actionType)
    {
        $this->actionType = $actionType;

        return $this;
    }

    /**
     * Get actionType
     *
     * @return string
     */
    public function getActionType()
    {
        return $this->actionType;
    }

    /**
     * Set sponsor
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $sponsor
     *
     * @return FgSmServicesLog
     */
    public function setSponsor(\Common\UtilityBundle\Entity\FgCmContact $sponsor = null)
    {
        $this->sponsor = $sponsor;

        return $this;
    }

    /**
     * Get sponsor
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact
     */
    public function getSponsor()
    {
        return $this->sponsor;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     *
     * @return FgSmServicesLog
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
     * @return FgSmServicesLog
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
     * Set service
     *
     * @param \Common\UtilityBundle\Entity\FgSmServices $service
     *
     * @return FgSmServicesLog
     */
    public function setService(\Common\UtilityBundle\Entity\FgSmServices $service = null)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * Get service
     *
     * @return \Common\UtilityBundle\Entity\FgSmServices
     */
    public function getService()
    {
        return $this->service;
    }
}

