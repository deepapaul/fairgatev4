<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgRmRoleLog
 */
class FgRmRoleLog
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
    private $historicalId;

    /**
     * @var boolean
     */
    private $isHistorical;

    /**
     * @var integer
     */
    private $bookingId;

    /**
     * @var \Common\UtilityBundle\Entity\FgRmRole
     */
    private $role;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $changedBy;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmChangeLog
     */
    private $contactlog;


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
     * @return FgRmRoleLog
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
     * Set date
     *
     * @param \DateTime $date
     * @return FgRmRoleLog
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
     * @return FgRmRoleLog
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
     * @return FgRmRoleLog
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
     * @return FgRmRoleLog
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
     * @return FgRmRoleLog
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
     * Set historicalId
     *
     * @param integer $historicalId
     * @return FgRmRoleLog
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
     * @return FgRmRoleLog
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
     * Set bookingId
     *
     * @param integer $bookingId
     * @return FgRmRoleLog
     */
    public function setBookingId($bookingId)
    {
        $this->bookingId = $bookingId;
    
        return $this;
    }

    /**
     * Get bookingId
     *
     * @return integer 
     */
    public function getBookingId()
    {
        return $this->bookingId;
    }

    /**
     * Set role
     *
     * @param \Common\UtilityBundle\Entity\FgRmRole $role
     * @return FgRmRoleLog
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
     * Set changedBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $changedBy
     * @return FgRmRoleLog
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
     * Set contactlog
     *
     * @param \Common\UtilityBundle\Entity\FgCmChangeLog $contactlog
     * @return FgRmRoleLog
     */
    public function setContactlog(\Common\UtilityBundle\Entity\FgCmChangeLog $contactlog = null)
    {
        $this->contactlog = $contactlog;
    
        return $this;
    }

    /**
     * Get contactlog
     *
     * @return \Common\UtilityBundle\Entity\FgCmChangeLog 
     */
    public function getContactlog()
    {
        return $this->contactlog;
    }
    /**
     * @var string
     */
    private $importTable;

    /**
     * @var integer
     */
    private $importContact;


    /**
     * Set importTable
     *
     * @param string $importTable
     * @return FgRmRoleLog
     */
    public function setImportTable($importTable)
    {
        $this->importTable = $importTable;
    
        return $this;
    }

    /**
     * Get importTable
     *
     * @return string 
     */
    public function getImportTable()
    {
        return $this->importTable;
    }

    /**
     * Set importContact
     *
     * @param integer $importContact
     * @return FgRmRoleLog
     */
    public function setImportContact($importContact)
    {
        $this->importContact = $importContact;
    
        return $this;
    }

    /**
     * Get importContact
     *
     * @return integer 
     */
    public function getImportContact()
    {
        return $this->importContact;
    }
}