<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgEmCalendarSelectedCategories
 */
class FgEmCalendarSelectedCategories
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Common\UtilityBundle\Entity\FgEmCalendarCategory
     */
    private $category;

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
     * Set category
     *
     * @param \Common\UtilityBundle\Entity\FgEmCalendarCategory $category
     * @return FgEmCalendarSelectedCategories
     */
    public function setCategory(\Common\UtilityBundle\Entity\FgEmCalendarCategory $category = null)
    {
        $this->category = $category;
    
        return $this;
    }

    /**
     * Get category
     *
     * @return \Common\UtilityBundle\Entity\FgEmCalendarCategory 
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set calendar
     *
     * @param \Common\UtilityBundle\Entity\FgEmCalendar $calendar
     * @return FgEmCalendarSelectedCategories
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
     * @var \Common\UtilityBundle\Entity\FgEmCalendarDetails
     */
    private $calendarDetails;


    /**
     * Set calendarDetails
     *
     * @param \Common\UtilityBundle\Entity\FgEmCalendarDetails $calendarDetails
     * @return FgEmCalendarSelectedCategories
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