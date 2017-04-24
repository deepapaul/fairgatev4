<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgWebSettings
 */
class FgWebSettings
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $defaultLogo;

    /**
     * @var string
     */
    private $favicon;

    /**
     * @var string
     */
    private $fallbackImage;

    /**
     * @var string
     */
    private $siteDescription;

    /**
     * @var string
     */
    private $domainVerificationFilename;

    /**
     * @var string
     */
    private $googleAnalyticsTrackid;

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set defaultLogo
     *
     * @param string $defaultLogo
     *
     * @return FgWebSettings
     */
    public function setDefaultLogo($defaultLogo)
    {
        $this->defaultLogo = $defaultLogo;

        return $this;
    }

    /**
     * Get defaultLogo
     *
     * @return string
     */
    public function getDefaultLogo()
    {
        return $this->defaultLogo;
    }

    /**
     * Set favicon
     *
     * @param string $favicon
     *
     * @return FgWebSettings
     */
    public function setFavicon($favicon)
    {
        $this->favicon = $favicon;

        return $this;
    }

    /**
     * Get favicon
     *
     * @return string
     */
    public function getFavicon()
    {
        return $this->favicon;
    }

    /**
     * Set fallbackImage
     *
     * @param string $fallbackImage
     *
     * @return FgWebSettings
     */
    public function setFallbackImage($fallbackImage)
    {
        $this->fallbackImage = $fallbackImage;

        return $this;
    }

    /**
     * Get fallbackImage
     *
     * @return string
     */
    public function getFallbackImage()
    {
        return $this->fallbackImage;
    }

    /**
     * Set siteDescription
     *
     * @param string $siteDescription
     *
     * @return FgWebSettings
     */
    public function setSiteDescription($siteDescription)
    {
        $this->siteDescription = $siteDescription;

        return $this;
    }

    /**
     * Get siteDescription
     *
     * @return string
     */
    public function getSiteDescription()
    {
        return $this->siteDescription;
    }

    /**
     * Set domainVerificationFilename
     *
     * @param string $domainVerificationFilename
     *
     * @return FgWebSettings
     */
    public function setDomainVerificationFilename($domainVerificationFilename)
    {
        $this->domainVerificationFilename = $domainVerificationFilename;

        return $this;
    }

    /**
     * Get domainVerificationFilename
     *
     * @return string
     */
    public function getDomainVerificationFilename()
    {
        return $this->domainVerificationFilename;
    }

    /**
     * Set googleAnalyticsTrackid
     *
     * @param string $googleAnalyticsTrackid
     *
     * @return FgWebSettings
     */
    public function setGoogleAnalyticsTrackid($googleAnalyticsTrackid)
    {
        $this->googleAnalyticsTrackid = $googleAnalyticsTrackid;

        return $this;
    }

    /**
     * Get googleAnalyticsTrackid
     *
     * @return string
     */
    public function getGoogleAnalyticsTrackid()
    {
        return $this->googleAnalyticsTrackid;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return FgWebSettings
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
     * @return FgWebSettings
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
     * @return FgWebSettings
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

