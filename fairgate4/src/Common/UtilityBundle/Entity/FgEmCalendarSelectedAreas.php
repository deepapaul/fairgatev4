<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgEmCalendarSelectedAreas
 */
class FgEmCalendarSelectedAreas
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $isClub;

    /**
     * @var \Common\UtilityBundle\Entity\FgEmCalendar
     */
    private $calendar;

    /**
     * @var \Common\UtilityBundle\Entity\FgRmRole
     */
    private $role;


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
     * Set isClub
     *
     * @param boolean $isClub
     * @return FgEmCalendarSelectedAreas
     */
    public function setIsClub($isClub)
    {
        $this->isClub = $isClub;
    
        return $this;
    }

    /**
     * Get isClub
     *
     * @return boolean 
     */
    public function getIsClub()
    {
        return $this->isClub;
    }

    /**
     * Set calendar
     *
     * @param \Common\UtilityBundle\Entity\FgEmCalendar $calendar
     * @return FgEmCalendarSelectedAreas
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
     * Set role
     *
     * @param \Common\UtilityBundle\Entity\FgRmRole $role
     * @return FgEmCalendarSelectedAreas
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
     * @var \Common\UtilityBundle\Entity\FgEmCalendarDetails
     */
    private $calendarDetails;


    /**
     * Set calendarDetails
     *
     * @param \Common\UtilityBundle\Entity\FgEmCalendarDetails $calendarDetails
     * @return FgEmCalendarSelectedAreas
     */
    public function setCalendarDetails(\Common\UtilityBundle\Entity\FgEmCalendarDetails $calendarDetails = null)
    {
        $this->calendarDetails = $calendarDetails;
    
        return $this;
    }

    /**
     * Get calendarDetails
     *
     * @return \Common\UtilityBundle\Entity\FgEmCalendarDetails 
     */
    public function getCalendarDetails()
    {
        return $this->calendarDetails;
    }
}