<?php

namespace Admin\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgClubClassLog
 */
class FgClubClassLog
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $clubId;

    /**
     * @var integer
     */
    private $classId;

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
     * @var integer
     */
    private $changedByContact;

    /**
     * @var integer
     */
    private $changedByClub;

    /**
     * @var boolean
     */
    private $isHistorical;


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
     * Set clubId
     *
     * @param integer $clubId
     * @return FgClubClassLog
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
     * Set classId
     *
     * @param integer $classId
     * @return FgClubClassLog
     */
    public function setClassId($classId)
    {
        $this->classId = $classId;

        return $this;
    }

    /**
     * Get classId
     *
     * @return integer
     */
    public function getClassId()
    {
        return $this->classId;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return FgClubClassLog
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
     * @return FgClubClassLog
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
     * @return FgClubClassLog
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
     * @return FgClubClassLog
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
     * @return FgClubClassLog
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
     * Set changedByContact
     *
     * @param integer $changedByContact
     * @return FgClubClassLog
     */
    public function setChangedByContact($changedByContact)
    {
        $this->changedByContact = $changedByContact;

        return $this;
    }

    /**
     * Get changedByContact
     *
     * @return integer
     */
    public function getChangedByContact()
    {
        return $this->changedByContact;
    }

    /**
     * Set changedByClub
     *
     * @param integer $changedByClub
     * @return FgClubClassLog
     */
    public function setChangedByClub($changedByClub)
    {
        $this->changedByClub = $changedByClub;

        return $this;
    }

    /**
     * Get changedByClub
     *
     * @return integer
     */
    public function getChangedByClub()
    {
        return $this->changedByClub;
    }

    /**
     * Set isHistorical
     *
     * @param boolean $isHistorical
     * @return FgClubClassLog
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
     * @var string
     */
    private $changedByContactName;


    /**
     * Set changedByContactName
     *
     * @param string $changedByContactName
     *
     * @return FgClubClassLog
     */
    public function setChangedByContactName($changedByContactName)
    {
        $this->changedByContactName = $changedByContactName;

        return $this;
    }

    /**
     * Get changedByContactName
     *
     * @return string
     */
    public function getChangedByContactName()
    {
        return $this->changedByContactName;
    }
}
