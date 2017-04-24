<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgCmsArticleClubsetting
 */
class FgCmsArticleClubsetting
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $commentActive;

    /**
     * @var integer
     */
    private $showMultilanguageVersion;

    /**
     * @var integer
     */
    private $timeperiodStartDay;

    /**
     * @var integer
     */
    private $timeperiodStartMonth;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;


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
     * Set commentActive
     *
     * @param integer $commentActive
     *
     * @return FgCmsArticleClubsetting
     */
    public function setCommentActive($commentActive)
    {
        $this->commentActive = $commentActive;

        return $this;
    }

    /**
     * Get commentActive
     *
     * @return integer
     */
    public function getCommentActive()
    {
        return $this->commentActive;
    }

    /**
     * Set showMultilanguageVersion
     *
     * @param integer $showMultilanguageVersion
     *
     * @return FgCmsArticleClubsetting
     */
    public function setShowMultilanguageVersion($showMultilanguageVersion)
    {
        $this->showMultilanguageVersion = $showMultilanguageVersion;

        return $this;
    }

    /**
     * Get showMultilanguageVersion
     *
     * @return integer
     */
    public function getShowMultilanguageVersion()
    {
        return $this->showMultilanguageVersion;
    }

    /**
     * Set timeperiodStartDay
     *
     * @param integer $timeperiodStartDay
     *
     * @return FgCmsArticleClubsetting
     */
    public function setTimeperiodStartDay($timeperiodStartDay)
    {
        $this->timeperiodStartDay = $timeperiodStartDay;

        return $this;
    }

    /**
     * Get timeperiodStartDay
     *
     * @return integer
     */
    public function getTimeperiodStartDay()
    {
        return $this->timeperiodStartDay;
    }

    /**
     * Set timeperiodStartMonth
     *
     * @param integer $timeperiodStartMonth
     *
     * @return FgCmsArticleClubsetting
     */
    public function setTimeperiodStartMonth($timeperiodStartMonth)
    {
        $this->timeperiodStartMonth = $timeperiodStartMonth;

        return $this;
    }

    /**
     * Get timeperiodStartMonth
     *
     * @return integer
     */
    public function getTimeperiodStartMonth()
    {
        return $this->timeperiodStartMonth;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     *
     * @return FgCmsArticleClubsetting
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
}

