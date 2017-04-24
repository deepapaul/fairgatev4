<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgClubSettings
 */
class FgClubSettings
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $fiscalYear;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var string
     */
    private $currencyPosition;

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
     * Set fiscalYear
     *
     * @param \DateTime $fiscalYear
     * @return FgClubSettings
     */
    public function setFiscalYear($fiscalYear)
    {
        $this->fiscalYear = $fiscalYear;
    
        return $this;
    }

    /**
     * Get fiscalYear
     *
     * @return \DateTime 
     */
    public function getFiscalYear()
    {
        return $this->fiscalYear;
    }

    /**
     * Set currency
     *
     * @param string $currency
     * @return FgClubSettings
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    
        return $this;
    }

    /**
     * Get currency
     *
     * @return string 
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Set currencyPosition
     *
     * @param string $currencyPosition
     * @return FgClubSettings
     */
    public function setCurrencyPosition($currencyPosition)
    {
        $this->currencyPosition = $currencyPosition;
    
        return $this;
    }

    /**
     * Get currencyPosition
     *
     * @return string 
     */
    public function getCurrencyPosition()
    {
        return $this->currencyPosition;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     * @return FgClubSettings
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
     * @var integer
     */
    private $majorityAge;

    /**
     * @var integer
     */
    private $profileAccessAge;


    /**
     * Set majorityAge
     *
     * @param integer $majorityAge
     * @return FgClubSettings
     */
    public function setMajorityAge($majorityAge)
    {
        $this->majorityAge = $majorityAge;
    
        return $this;
    }

    /**
     * Get majorityAge
     *
     * @return integer 
     */
    public function getMajorityAge()
    {
        return $this->majorityAge;
    }

    /**
     * Set profileAccessAge
     *
     * @param integer $profileAccessAge
     * @return FgClubSettings
     */
    public function setProfileAccessAge($profileAccessAge)
    {
        $this->profileAccessAge = $profileAccessAge;
    
        return $this;
    }

    /**
     * Get profileAccessAge
     *
     * @return integer 
     */
    public function getProfileAccessAge()
    {
        return $this->profileAccessAge;
    }
    /**
     * @var string
     */
    private $signature;

    /**
     * @var string
     */
    private $logo;

    /**
     * @var string
     */
    private $federationIcon;
    
    /**
     * Set signature
     *
     * @param string $signature
     * @return FgClubSettings
     */
    public function setSignature($signature)
    {
        $this->signature = $signature;
    
        return $this;
    }

    /**
     * Get signature
     *
     * @return string 
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * Set logo
     *
     * @param string $logo
     * @return FgClubSettings
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;
    
        return $this;
    }

    /**
     * Get logo
     *
     * @return string 
     */
    public function getLogo()
    {
        return $this->logo;
    }
    
    /**
     * Set federationIcon
     *
     * @param string $federationIcon
     * @return FgClubSettings
     */
    public function setFederationIcon($federationIcon)
    {
        $this->federationIcon = $federationIcon;
    
        return $this;
    }

    /**
     * Get federationIcon
     *
     * @return string 
     */
    public function getFederationIcon()
    {
        return $this->federationIcon;
    }
}