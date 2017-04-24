<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgClubLanguage
 */
class FgClubLanguage
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $correspondanceLang;

    /**
     * @var string
     */
    private $systemLang;

    /**
     * @var boolean
     */
    private $visibleForClub;

    /**
     * @var string
     */
    private $dateFormat;

    /**
     * @var string
     */
    private $timeFormat;

    /**
     * @var string
     */
    private $thousandSeparator;

    /**
     * @var string
     */
    private $decimalMarker;

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
     * Set correspondanceLang
     *
     * @param string $correspondanceLang
     *
     * @return FgClubLanguage
     */
    public function setCorrespondanceLang($correspondanceLang)
    {
        $this->correspondanceLang = $correspondanceLang;

        return $this;
    }

    /**
     * Get correspondanceLang
     *
     * @return string
     */
    public function getCorrespondanceLang()
    {
        return $this->correspondanceLang;
    }

    /**
     * Set systemLang
     *
     * @param string $systemLang
     *
     * @return FgClubLanguage
     */
    public function setSystemLang($systemLang)
    {
        $this->systemLang = $systemLang;

        return $this;
    }

    /**
     * Get systemLang
     *
     * @return string
     */
    public function getSystemLang()
    {
        return $this->systemLang;
    }

    /**
     * Set visibleForClub
     *
     * @param boolean $visibleForClub
     *
     * @return FgClubLanguage
     */
    public function setVisibleForClub($visibleForClub)
    {
        $this->visibleForClub = $visibleForClub;

        return $this;
    }

    /**
     * Get visibleForClub
     *
     * @return boolean
     */
    public function getVisibleForClub()
    {
        return $this->visibleForClub;
    }

    /**
     * Set dateFormat
     *
     * @param string $dateFormat
     *
     * @return FgClubLanguage
     */
    public function setDateFormat($dateFormat)
    {
        $this->dateFormat = $dateFormat;

        return $this;
    }

    /**
     * Get dateFormat
     *
     * @return string
     */
    public function getDateFormat()
    {
        return $this->dateFormat;
    }

    /**
     * Set timeFormat
     *
     * @param string $timeFormat
     *
     * @return FgClubLanguage
     */
    public function setTimeFormat($timeFormat)
    {
        $this->timeFormat = $timeFormat;

        return $this;
    }

    /**
     * Get timeFormat
     *
     * @return string
     */
    public function getTimeFormat()
    {
        return $this->timeFormat;
    }

    /**
     * Set thousandSeparator
     *
     * @param string $thousandSeparator
     *
     * @return FgClubLanguage
     */
    public function setThousandSeparator($thousandSeparator)
    {
        $this->thousandSeparator = $thousandSeparator;

        return $this;
    }

    /**
     * Get thousandSeparator
     *
     * @return string
     */
    public function getThousandSeparator()
    {
        return $this->thousandSeparator;
    }

    /**
     * Set decimalMarker
     *
     * @param string $decimalMarker
     *
     * @return FgClubLanguage
     */
    public function setDecimalMarker($decimalMarker)
    {
        $this->decimalMarker = $decimalMarker;

        return $this;
    }

    /**
     * Get decimalMarker
     *
     * @return string
     */
    public function getDecimalMarker()
    {
        return $this->decimalMarker;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     *
     * @return FgClubLanguage
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

