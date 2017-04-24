<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCnSmsExcludedNumbers
 */
class FgCnSmsExcludedNumbers
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $mobileNumber;

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
     * Set mobileNumber
     *
     * @param string $mobileNumber
     * @return FgCnSmsExcludedNumbers
     */
    public function setMobileNumber($mobileNumber)
    {
        $this->mobileNumber = $mobileNumber;

        return $this;
    }

    /**
     * Get mobileNumber
     *
     * @return string
     */
    public function getMobileNumber()
    {
        return $this->mobileNumber;
    }

    /**
     * Set sms
     *
     * @param \Common\UtilityBundle\Entity\FgCnSms $sms
     * @return FgCnSmsExcludedNumbers
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