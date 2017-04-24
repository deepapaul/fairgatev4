<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgEmCalendar
 */
class FgEmCalendar
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $scope;

    /**
     * @var boolean
     */
    private $shareWithLower;

    /**
     * @var boolean
     */
    private $isAllday;

    /**
     * @var boolean
     */
    private $isRepeat;

    /**
     * @var \DateTime
     */
    private $repeatUntillDate;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $createdBy;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $updatedBy;

    /**
     * @var \Common\UtilityBundle\Entity\FgEmCalendarRules
     */
    private $calendarRules;


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
     * Set scope
     *
     * @param string $scope
     *
     * @return FgEmCalendar
     */
    public function setScope($scope)
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * Get scope
     *
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Set shareWithLower
     *
     * @param boolean $shareWithLower
     *
     * @return FgEmCalendar
     */
    public function setShareWithLower($shareWithLower)
    {
        $this->shareWithLower = $shareWithLower;

        return $this;
    }

    /**
     * Get shareWithLower
     *
     * @return boolean
     */
    public function getShareWithLower()
    {
        return $this->shareWithLower;
    }

    /**
     * Set isAllday
     *
     * @param boolean $isAllday
     *
     * @return FgEmCalendar
     */
    public function setIsAllday($isAllday)
    {
        $this->isAllday = $isAllday;

        return $this;
    }

    /**
     * Get isAllday
     *
     * @return boolean
     */
    public function getIsAllday()
    {
        return $this->isAllday;
    }

    /**
     * Set isRepeat
     *
     * @param boolean $isRepeat
     *
     * @return FgEmCalendar
     */
    public function setIsRepeat($isRepeat)
    {
        $this->isRepeat = $isRepeat;

        return $this;
    }

    /**
     * Get isRepeat
     *
     * @return boolean
     */
    public function getIsRepeat()
    {
        return $this->isRepeat;
    }

    /**
     * Set repeatUntillDate
     *
     * @param \DateTime $repeatUntillDate
     *
     * @return FgEmCalendar
     */
    public function setRepeatUntillDate($repeatUntillDate)
    {
        $this->repeatUntillDate = $repeatUntillDate;

        return $this;
    }

    /**
     * Get repeatUntillDate
     *
     * @return \DateTime
     */
    public function getRepeatUntillDate()
    {
        return $this->repeatUntillDate;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return FgEmCalendar
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
     *
     * @return FgEmCalendar
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
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     *
     * @return FgEmCalendar
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
     * Set createdBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $createdBy
     *
     * @return FgEmCalendar
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
     *
     * @return FgEmCalendar
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
     * Set calendarRules
     *
     * @param \Common\UtilityBundle\Entity\FgEmCalendarRules $calendarRules
     *
     * @return FgEmCalendar
     */
    public function setCalendarRules(\Common\UtilityBundle\Entity\FgEmCalendarRules $calendarRules = null)
    {
        $this->calendarRules = $calendarRules;

        return $this;
    }

    /**
     * Get calendarRules
     *
     * @return \Common\UtilityBundle\Entity\FgEmCalendarRules
     */
    public function getCalendarRules()
    {
        return $this->calendarRules;
    }
}

