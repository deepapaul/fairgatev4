<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCmChangeToconfirm
 */
class FgCmChangeToconfirm
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
    private $value;

    /**
     * @var boolean
     */
    private $logOnce;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmAttribute
     */
    private $attribute;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $contact;

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
     * @return FgCmChangeToconfirm
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
     * Set value
     *
     * @param string $value
     * @return FgCmChangeToconfirm
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set logOnce
     *
     * @param boolean $logOnce
     * @return FgCmChangeToconfirm
     */
    public function setLogOnce($logOnce)
    {
        $this->logOnce = $logOnce;

        return $this;
    }

    /**
     * Get logOnce
     *
     * @return boolean
     */
    public function getLogOnce()
    {
        return $this->logOnce;
    }

    /**
     * Set attribute
     *
     * @param \Common\UtilityBundle\Entity\FgCmAttribute $attribute
     * @return FgCmChangeToconfirm
     */
    public function setAttribute(\Common\UtilityBundle\Entity\FgCmAttribute $attribute = null)
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * Get attribute
     *
     * @return \Common\UtilityBundle\Entity\FgCmAttribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Set contact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $contact
     * @return FgCmChangeToconfirm
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
     * @return FgCmChangeToconfirm
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
     * @var string
     */
    private $type;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;


    /**
     * Set type
     *
     * @param string $type
     * @return FgCmChangeToconfirm
     */
    public function setType($type)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     * @return FgCmChangeToconfirm
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
     * @var integer
     */
    private $roleId;

    /**
     * @var string
     */
    private $functionId;


    /**
     * Set roleId
     *
     * @param integer $roleId
     * @return FgCmChangeToconfirm
     */
    public function setRoleId($roleId)
    {
        $this->roleId = $roleId;
    
        return $this;
    }

    /**
     * Get roleId
     *
     * @return integer 
     */
    public function getRoleId()
    {
        return $this->roleId;
    }

    /**
     * Set functionId
     *
     * @param string $functionId
     * @return FgCmChangeToconfirm
     */
    public function setFunctionId($functionId)
    {
        $this->functionId = $functionId;
    
        return $this;
    }

    /**
     * Get functionId
     *
     * @return string 
     */
    public function getFunctionId()
    {
        return $this->functionId;
    }
    /**
     * @var string
     */
    private $confirmStatus;


    /**
     * Set confirmStatus
     *
     * @param string $confirmStatus
     * @return FgCmChangeToconfirm
     */
    public function setConfirmStatus($confirmStatus)
    {
        $this->confirmStatus = $confirmStatus;
    
        return $this;
    }

    /**
     * Get confirmStatus
     *
     * @return string 
     */
    public function getConfirmStatus()
    {
        return $this->confirmStatus;
    }
}