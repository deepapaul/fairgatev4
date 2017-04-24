<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgEmCalendarRules
 */
class FgEmCalendarRules
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $freq;

    /**
     * @var string
     */
    private $byday;

    /**
     * @var string
     */
    private $interval;

    /**
     * @var string
     */
    private $bymonthday;

    /**
     * @var string
     */
    private $bymonth;


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
     * Set freq
     *
     * @param string $freq
     *
     * @return FgEmCalendarRules
     */
    public function setFreq($freq)
    {
        $this->freq = $freq;

        return $this;
    }

    /**
     * Get freq
     *
     * @return string
     */
    public function getFreq()
    {
        return $this->freq;
    }

    /**
     * Set byday
     *
     * @param string $byday
     *
     * @return FgEmCalendarRules
     */
    public function setByday($byday)
    {
        $this->byday = $byday;

        return $this;
    }

    /**
     * Get byday
     *
     * @return string
     */
    public function getByday()
    {
        return $this->byday;
    }

    /**
     * Set interval
     *
     * @param string $interval
     *
     * @return FgEmCalendarRules
     */
    public function setInterval($interval)
    {
        $this->interval = $interval;

        return $this;
    }

    /**
     * Get interval
     *
     * @return string
     */
    public function getInterval()
    {
        return $this->interval;
    }

    /**
     * Set bymonthday
     *
     * @param string $bymonthday
     *
     * @return FgEmCalendarRules
     */
    public function setBymonthday($bymonthday)
    {
        $this->bymonthday = $bymonthday;

        return $this;
    }

    /**
     * Get bymonthday
     *
     * @return string
     */
    public function getBymonthday()
    {
        return $this->bymonthday;
    }

    /**
     * Set bymonth
     *
     * @param string $bymonth
     *
     * @return FgEmCalendarRules
     */
    public function setBymonth($bymonth)
    {
        $this->bymonth = $bymonth;

        return $this;
    }

    /**
     * Get bymonth
     *
     * @return string
     */
    public function getBymonth()
    {
        return $this->bymonth;
    }
}

