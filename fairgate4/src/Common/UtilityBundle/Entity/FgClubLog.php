<?php

namespace Common\UtilityBundle\Entity;

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
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $changedBy;

    /**
     * @var \Common\UtilityBundle\Entity\FgRmRoleLog
     */
    private $rolelog;


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
     * @param \Common\UtilityBundle\Entity\FgClub $club
     * @return FgClubLog
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
     * @return FgClubLog
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
     * Set rolelog
     *
     * @param \Common\UtilityBundle\Entity\FgRmRoleLog $rolelog
     * @return FgClubLog
     */
    public function setRolelog(\Common\UtilityBundle\Entity\FgRmRoleLog $rolelog = null)
    {
        $this->rolelog = $rolelog;

        return $this;
    }

    /**
     * Get rolelog
     *
     * @return \Common\UtilityBundle\Entity\FgRmRoleLog
     */
    public function getRolelog()
    {
        return $this->rolelog;
    }
    /**
     * @var \Common\UtilityBundle\Entity\FgClubClassLog
     */
    private $classLog;


    /**
     * Set classLog
     *
     * @param \Common\UtilityBundle\Entity\FgClubClassLog $classLog
     * @return FgClubLog
     */
    public function setClassLog(\Common\UtilityBundle\Entity\FgClubClassLog $classLog = null)
    {
        $this->classLog = $classLog;

        return $this;
    }

    /**
     * Get classLog
     *
     * @return \Common\UtilityBundle\Entity\FgClubClassLog
     */
    public function getClassLog()
    {
        return $this->classLog;
    }
}