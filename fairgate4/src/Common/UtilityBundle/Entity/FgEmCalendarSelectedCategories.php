<?php

namespace Common\UtilityBundle\Entity;

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
     * @var \Common\UtilityBundle\Entity\FgEmCalendarDetails
     */
    private $calendarDetails;

    /**
     * @var \Common\UtilityBundle\Entity\FgEmCalendarCategory
     */
    private $category;


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
     * Set calendarDetails
     *
     * @param \Common\UtilityBundle\Entity\FgEmCalendarDetails $calendarDetails
     *
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

    /**
     * Set category
     *
     * @param \Common\UtilityBundle\Entity\FgEmCalendarCategory $category
     *
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
}

