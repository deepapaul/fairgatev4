<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgApiGotcourts
 */
class FgApiGotcourts
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $apitoken;

    /**
     * @var string
     */
    private $status;

    /**
     * @var integer
     */
    private $isActive;

    /**
     * @var \DateTime
     */
    private $bookedOn;

    /**
     * @var \DateTime
     */
    private $generatedOn;

    /**
     * @var \DateTime
     */
    private $registeredOn;

    /**
     * @var \DateTime
     */
    private $regeneratedOn;

    /**
     * @var \DateTime
     */
    private $cancelledOn;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $bookedBy;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $generatedBy;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $regeneratedBy;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $cancelledBy;


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
     * Set apitoken
     *
     * @param string $apitoken
     * @return FgApiGotcourts
     */
    public function setApitoken($apitoken)
    {
        $this->apitoken = $apitoken;
    
        return $this;
    }

    /**
     * Get apitoken
     *
     * @return string 
     */
    public function getApitoken()
    {
        return $this->apitoken;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return FgApiGotcourts
     */
    public function setStatus($status)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set isActive
     *
     * @param integer $isActive
     * @return FgApiGotcourts
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    
        return $this;
    }

    /**
     * Get isActive
     *
     * @return integer 
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set bookedOn
     *
     * @param \DateTime $bookedOn
     * @return FgApiGotcourts
     */
    public function setBookedOn($bookedOn)
    {
        $this->bookedOn = $bookedOn;
    
        return $this;
    }

    /**
     * Get bookedOn
     *
     * @return \DateTime 
     */
    public function getBookedOn()
    {
        return $this->bookedOn;
    }

    /**
     * Set generatedOn
     *
     * @param \DateTime $generatedOn
     * @return FgApiGotcourts
     */
    public function setGeneratedOn($generatedOn)
    {
        $this->generatedOn = $generatedOn;
    
        return $this;
    }

    /**
     * Get generatedOn
     *
     * @return \DateTime 
     */
    public function getGeneratedOn()
    {
        return $this->generatedOn;
    }

    /**
     * Set registeredOn
     *
     * @param \DateTime $registeredOn
     * @return FgApiGotcourts
     */
    public function setRegisteredOn($registeredOn)
    {
        $this->registeredOn = $registeredOn;
    
        return $this;
    }

    /**
     * Get registeredOn
     *
     * @return \DateTime 
     */
    public function getRegisteredOn()
    {
        return $this->registeredOn;
    }

    /**
     * Set regeneratedOn
     *
     * @param \DateTime $regeneratedOn
     * @return FgApiGotcourts
     */
    public function setRegeneratedOn($regeneratedOn)
    {
        $this->regeneratedOn = $regeneratedOn;
    
        return $this;
    }

    /**
     * Get regeneratedOn
     *
     * @return \DateTime 
     */
    public function getRegeneratedOn()
    {
        return $this->regeneratedOn;
    }

    /**
     * Set cancelledOn
     *
     * @param \DateTime $cancelledOn
     * @return FgApiGotcourts
     */
    public function setCancelledOn($cancelledOn)
    {
        $this->cancelledOn = $cancelledOn;
    
        return $this;
    }

    /**
     * Get cancelledOn
     *
     * @return \DateTime 
     */
    public function getCancelledOn()
    {
        return $this->cancelledOn;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     * @return FgApiGotcourts
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
     * Set bookedBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $bookedBy
     * @return FgApiGotcourts
     */
    public function setBookedBy(\Common\UtilityBundle\Entity\FgCmContact $bookedBy = null)
    {
        $this->bookedBy = $bookedBy;
    
        return $this;
    }

    /**
     * Get bookedBy
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact 
     */
    public function getBookedBy()
    {
        return $this->bookedBy;
    }

    /**
     * Set generatedBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $generatedBy
     * @return FgApiGotcourts
     */
    public function setGeneratedBy(\Common\UtilityBundle\Entity\FgCmContact $generatedBy = null)
    {
        $this->generatedBy = $generatedBy;
    
        return $this;
    }

    /**
     * Get generatedBy
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact 
     */
    public function getGeneratedBy()
    {
        return $this->generatedBy;
    }

    /**
     * Set regeneratedBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $regeneratedBy
     * @return FgApiGotcourts
     */
    public function setRegeneratedBy(\Common\UtilityBundle\Entity\FgCmContact $regeneratedBy = null)
    {
        $this->regeneratedBy = $regeneratedBy;
    
        return $this;
    }

    /**
     * Get regeneratedBy
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact 
     */
    public function getRegeneratedBy()
    {
        return $this->regeneratedBy;
    }

    /**
     * Set cancelledBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $cancelledBy
     * @return FgApiGotcourts
     */
    public function setCancelledBy(\Common\UtilityBundle\Entity\FgCmContact $cancelledBy = null)
    {
        $this->cancelledBy = $cancelledBy;
    
        return $this;
    }

    /**
     * Get cancelledBy
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact 
     */
    public function getCancelledBy()
    {
        return $this->cancelledBy;
    }
}
