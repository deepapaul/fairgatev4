<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgRmFunctionLog
 */
class FgRmFunctionLog
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
     * @var \Common\UtilityBundle\Entity\FgRmRole
     */
    private $role;

    /**
     * @var \Common\UtilityBundle\Entity\FgRmFunction
     */
    private $function;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
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
     *
     * @return FgRmFunctionLog
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
     * @return FgRmFunctionLog
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
     * @return FgRmFunctionLog
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
     * @return FgRmFunctionLog
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
     * @return FgRmFunctionLog
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
     * Set role
     *
     * @param \Common\UtilityBundle\Entity\FgRmRole $role
     *
     * @return FgRmFunctionLog
     */
    public function setRole(\Common\UtilityBundle\Entity\FgRmRole $role = null)
    {
        $this->role = $role;

        return $this;

    }


    /**
     * Get role
     *
     * @return \Common\UtilityBundle\Entity\FgRmRole
     */
    public function getRole()
    {
        return $this->role;

    }


    /**
     * Set function
     *
     * @param \Common\UtilityBundle\Entity\FgRmFunction $function
     *
     * @return FgRmFunctionLog
     */
    public function setFunction(\Common\UtilityBundle\Entity\FgRmFunction $function = null)
    {
        $this->function = $function;

        return $this;

    }


    /**
     * Get function
     *
     * @return \Common\UtilityBundle\Entity\FgRmFunction
     */
    public function getFunction()
    {
        return $this->function;

    }


    /**
     * Set changedBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $changedBy
     *
     * @return FgRmFunctionLog
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
     *
     * @var integer
     */
    private $clubId;


    /**
     * Set clubId
     *
     * @param integer $clubId
     *
     * @return FgRmFunctionLog
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


}