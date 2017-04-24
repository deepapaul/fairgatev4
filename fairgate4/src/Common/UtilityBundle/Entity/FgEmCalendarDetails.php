<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgEmCalendarDetails
 */
class FgEmCalendarDetails
{
    
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var \DateTime
     */
    private $startDate;

    /**
     * @var \DateTime
     */
    private $endDate;

    /**
     * @var string
     */
    private $location;

    /**
     * @var float
     */
    private $locationLatitude;

    /**
     * @var float
     */
    private $locationLongitude;

    /**
     * @var boolean
     */
    private $isShowInGooglemap;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $description;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @var boolean
     */
    private $status;

    /**
     * @var boolean
     */
    private $isMaster;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $createdBy;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $updatedBy;

    /**
     * @var \Common\UtilityBundle\Entity\FgEmCalendar
     */
    private $calendar;


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
     * Set title
     *
     * @param string $title
     * @return FgEmCalendarDetails
     */
    public function setTitle($title)
    {
        $this->title = $title;
    
        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set startDate
     *
     * @param \DateTime $startDate
     * @return FgEmCalendarDetails
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    
        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime 
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     * @return FgEmCalendarDetails
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    
        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime 
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set location
     *
     * @param string $location
     * @return FgEmCalendarDetails
     */
    public function setLocation($location)
    {
        $this->location = $location;
    
        return $this;
    }

    /**
     * Get location
     *
     * @return string 
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set locationLatitude
     *
     * @param float $locationLatitude
     * @return FgEmCalendarDetails
     */
    public function setLocationLatitude($locationLatitude)
    {
        $this->locationLatitude = $locationLatitude;
    
        return $this;
    }

    /**
     * Get locationLatitude
     *
     * @return float 
     */
    public function getLocationLatitude()
    {
        return $this->locationLatitude;
    }

    /**
     * Set locationLongitude
     *
     * @param float $locationLongitude
     * @return FgEmCalendarDetails
     */
    public function setLocationLongitude($locationLongitude)
    {
        $this->locationLongitude = $locationLongitude;
    
        return $this;
    }

    /**
     * Get locationLongitude
     *
     * @return float 
     */
    public function getLocationLongitude()
    {
        return $this->locationLongitude;
    }

    /**
     * Set isShowInGooglemap
     *
     * @param boolean $isShowInGooglemap
     * @return FgEmCalendarDetails
     */
    public function setIsShowInGooglemap($isShowInGooglemap)
    {
        $this->isShowInGooglemap = $isShowInGooglemap;
    
        return $this;
    }

    /**
     * Get isShowInGooglemap
     *
     * @return boolean 
     */
    public function getIsShowInGooglemap()
    {
        return $this->isShowInGooglemap;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return FgEmCalendarDetails
     */
    public function setUrl($url)
    {
        $this->url = $url;
    
        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return FgEmCalendarDetails
     */
    public function setDescription($description)
    {
        $this->description = $description;
    
        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return FgEmCalendarDetails
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return FgEmCalendarDetails
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    
        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime 
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set status
     *
     * @param boolean $status
     * @return FgEmCalendarDetails
     */
    public function setStatus($status)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return boolean 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set isMaster
     *
     * @param boolean $isMaster
     * @return FgEmCalendarDetails
     */
    public function setIsMaster($isMaster)
    {
        $this->isMaster = $isMaster;
    
        return $this;
    }

    /**
     * Get isMaster
     *
     * @return boolean 
     */
    public function getIsMaster()
    {
        return $this->isMaster;
    }

    /**
     * Set createdBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $createdBy
     * @return FgEmCalendarDetails
     */
    public function setCreatedBy(\Common\UtilityBundle\Entity\FgCmContact $createdBy = null)
    {
        $this->createdBy = $createdBy;
    
        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact 
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set updatedBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $updatedBy
     * @return FgEmCalendarDetails
     */
    public function setUpdatedBy(\Common\UtilityBundle\Entity\FgCmContact $updatedBy = null)
    {
        $this->updatedBy = $updatedBy;
    
        return $this;
    }

    /**
     * Get updatedBy
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact 
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * Set calendar
     *
     * @param \Common\UtilityBundle\Entity\FgEmCalendar $calendar
     * @return FgEmCalendarDetails
     */
    public function setCalendar(\Common\UtilityBundle\Entity\FgEmCalendar $calendar = null)
    {
        $this->calendar = $calendar;
    
        return $this;
    }

    /**
     * Get calendar
     *
     * @return \Common\UtilityBundle\Entity\FgEmCalendar 
     */
    public function getCalendar()
    {
        return $this->calendar;
    }
    /**
     * @var \DateTime
     */
    private $untill;


    /**
     * Set untill
     *
     * @param \DateTime $untill
     * @return FgEmCalendarDetails
     */
    public function setUntill($untill)
    {
        $this->untill = $untill;
    
        return $this;
    }

    /**
     * Get untill
     *
     * @return \DateTime 
     */
    public function getUntill()
    {
        return $this->untill;
    }
}