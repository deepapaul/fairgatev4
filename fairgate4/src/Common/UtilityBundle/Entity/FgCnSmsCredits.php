<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCnSmsCredits
 */
class FgCnSmsCredits
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var integer
     */
    private $credits;

    /**
     * @var string
     */
    private $status;

    /**
     * @var boolean
     */
    private $isTest;

    /**
     * @var integer
     */
    private $recredits;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;

    /**
     * @var \Common\UtilityBundle\Entity\FgCnSmsClubPackages
     */
    private $clubPackages;

    /**
     * @var \Common\UtilityBundle\Entity\FgCnSms
     */
    private $sms;


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
     * Set date
     *
     * @param \DateTime $date
     * @return FgCnSmsCredits
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
     * Set credits
     *
     * @param integer $credits
     * @return FgCnSmsCredits
     */
    public function setCredits($credits)
    {
        $this->credits = $credits;

        return $this;
    }

    /**
     * Get credits
     *
     * @return integer
     */
    public function getCredits()
    {
        return $this->credits;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return FgCnSmsCredits
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
     * Set isTest
     *
     * @param boolean $isTest
     * @return FgCnSmsCredits
     */
    public function setIsTest($isTest)
    {
        $this->isTest = $isTest;

        return $this;
    }

    /**
     * Get isTest
     *
     * @return boolean
     */
    public function getIsTest()
    {
        return $this->isTest;
    }

    /**
     * Set recredits
     *
     * @param integer $recredits
     * @return FgCnSmsCredits
     */
    public function setRecredits($recredits)
    {
        $this->recredits = $recredits;

        return $this;
    }

    /**
     * Get recredits
     *
     * @return integer
     */
    public function getRecredits()
    {
        return $this->recredits;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     * @return FgCnSmsCredits
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
     * Set clubPackages
     *
     * @param \Common\UtilityBundle\Entity\FgCnSmsClubPackages $clubPackages
     * @return FgCnSmsCredits
     */
    public function setClubPackages(\Common\UtilityBundle\Entity\FgCnSmsClubPackages $clubPackages = null)
    {
        $this->clubPackages = $clubPackages;

        return $this;
    }

    /**
     * Get clubPackages
     *
     * @return \Common\UtilityBundle\Entity\FgCnSmsClubPackages
     */
    public function getClubPackages()
    {
        return $this->clubPackages;
    }

    /**
     * Set sms
     *
     * @param \Common\UtilityBundle\Entity\FgCnSms $sms
     * @return FgCnSmsCredits
     */
    public function setSms(\Common\UtilityBundle\Entity\FgCnSms $sms = null)
    {
        $this->sms = $sms;

        return $this;
    }

    /**
     * Get sms
     *
     * @return \Common\UtilityBundle\Entity\FgCnSms
     */
    public function getSms()
    {
        return $this->sms;
    }
}