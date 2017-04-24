<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgDnClubDomains
 */
class FgDnClubDomains
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $domain;

    /**
     * @var boolean
     */
    private $isDefault;

    /**
     * @var string
     */
    private $domainType;

    /**
     * @var string
     */
    private $mailRequestStatus;

    /**
     * @var boolean
     */
    private $isSecure;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $mailRequester;


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
     * Set domain
     *
     * @param string $domain
     *
     * @return FgDnClubDomains
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * Get domain
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Set isDefault
     *
     * @param boolean $isDefault
     *
     * @return FgDnClubDomains
     */
    public function setIsDefault($isDefault)
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    /**
     * Get isDefault
     *
     * @return boolean
     */
    public function getIsDefault()
    {
        return $this->isDefault;
    }

    /**
     * Set domainType
     *
     * @param string $domainType
     *
     * @return FgDnClubDomains
     */
    public function setDomainType($domainType)
    {
        $this->domainType = $domainType;

        return $this;
    }

    /**
     * Get domainType
     *
     * @return string
     */
    public function getDomainType()
    {
        return $this->domainType;
    }

    /**
     * Set mailRequestStatus
     *
     * @param string $mailRequestStatus
     *
     * @return FgDnClubDomains
     */
    public function setMailRequestStatus($mailRequestStatus)
    {
        $this->mailRequestStatus = $mailRequestStatus;

        return $this;
    }

    /**
     * Get mailRequestStatus
     *
     * @return string
     */
    public function getMailRequestStatus()
    {
        return $this->mailRequestStatus;
    }

    /**
     * Set isSecure
     *
     * @param boolean $isSecure
     *
     * @return FgDnClubDomains
     */
    public function setIsSecure($isSecure)
    {
        $this->isSecure = $isSecure;

        return $this;
    }

    /**
     * Get isSecure
     *
     * @return boolean
     */
    public function getIsSecure()
    {
        return $this->isSecure;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     *
     * @return FgDnClubDomains
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
     * Set mailRequester
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $mailRequester
     *
     * @return FgDnClubDomains
     */
    public function setMailRequester(\Common\UtilityBundle\Entity\FgCmContact $mailRequester = null)
    {
        $this->mailRequester = $mailRequester;

        return $this;
    }

    /**
     * Get mailRequester
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact
     */
    public function getMailRequester()
    {
        return $this->mailRequester;
    }
}

