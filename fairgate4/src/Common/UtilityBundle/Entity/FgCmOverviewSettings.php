<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgCmOverviewSettings
 */
class FgCmOverviewSettings
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $settings;

    /**
     * @var string
     */
    private $type;

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
     * Set settings
     *
     * @param string $settings
     *
     * @return FgCmOverviewSettings
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;

        return $this;
    }

    /**
     * Get settings
     *
     * @return string
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return FgCmOverviewSettings
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     *
     * @return FgCmOverviewSettings
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

