<?php

namespace Admin\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgClubLog
 */
class FgClubLog
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
     * @var \Admin\UtilityBundle\Entity\FgClub
     */
    private $club;

    /**
     * @var \Admin\UtilityBundle\Entity\FgCmContact
     */
    private $changedBy;


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
     * @return FgClubLog
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
     * @return FgClubLog
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
     * @return FgClubLog
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
     * @return FgClubLog
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
     * @return FgClubLog
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
     * Set club
     *
     * @param \Admin\UtilityBundle\Entity\FgClub $club
     * @return FgClubLog
     */
    public function setClub(\Admin\UtilityBundle\Entity\FgClub $club = null)
    {
        $this->club = $club;

        return $this;
    }

    /**
     * Get club
     *
     * @return \Admin\UtilityBundle\Entity\FgClub
     */
    public function getClub()
    {
        return $this->club;
    }

    /**
     * Set changedBy
     *
     * @param \Admin\UtilityBundle\Entity\FgCmContact $changedBy
     * @return FgClubLog
     */
    public function setChangedBy(\Admin\UtilityBundle\Entity\FgCmContact $changedBy = null)
    {
        $this->changedBy = $changedBy;

        return $this;
    }

    /**
     * Get changedBy
     *
     * @return \Admin\UtilityBundle\Entity\FgCmContact
     */
    public function getChangedBy()
    {
        return $this->changedBy;
    }

    /**
     * @var \Admin\UtilityBundle\Entity\FgClubClassLog
     */
    private $classLog;


    /**
     * Set classLog
     *
     * @param \Admin\UtilityBundle\Entity\FgClubClassLog $classLog
     * @return FgClubLog
     */
    public function setClassLog(\Admin\UtilityBundle\Entity\FgClubClassLog $classLog = null)
    {
        $this->classLog = $classLog;

        return $this;
    }

    /**
     * Get classLog
     *
     * @return \Admin\UtilityBundle\Entity\FgClubClassLog
     */
    public function getClassLog()
    {
        return $this->classLog;
    }
    /**
     * @var integer
     */
    private $clubId;


    /**
     * Set clubId
     *
     * @param integer $clubId
     *
     * @return FgClubLog
     */
    public function setClubId($clubId)
    {
        $this->clubId = $clubId;

        return $this;
    }

    /**
     * Get clubId
     *
     * @return integer
     */
    public function getClubId()
    {
        return $this->clubId;
    }
    /**
     * @var string
     */
    private $changedByName;


    /**
     * Set changedByName
     *
     * @param string $changedByName
     *
     * @return FgClubLog
     */
    public function setChangedByName($changedByName)
    {
        $this->changedByName = $changedByName;

        return $this;
    }

    /**
     * Get changedByName
     *
     * @return string
     */
    public function getChangedByName()
    {
        return $this->changedByName;
    }
}
