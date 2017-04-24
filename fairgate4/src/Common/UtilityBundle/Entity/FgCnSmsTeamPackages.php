<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCnSmsTeamPackages
 */
class FgCnSmsTeamPackages
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $clubPackagesId;

    /**
     * @var integer
     */
    private $teamId;


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
     * Set clubPackagesId
     *
     * @param integer $clubPackagesId
     * @return FgCnSmsTeamPackages
     */
    public function setClubPackagesId($clubPackagesId)
    {
        $this->clubPackagesId = $clubPackagesId;

        return $this;
    }

    /**
     * Get clubPackagesId
     *
     * @return integer
     */
    public function getClubPackagesId()
    {
        return $this->clubPackagesId;
    }

    /**
     * Set teamId
     *
     * @param integer $teamId
     * @return FgCnSmsTeamPackages
     */
    public function setTeamId($teamId)
    {
        $this->teamId = $teamId;

        return $this;
    }

    /**
     * Get teamId
     *
     * @return integer
     */
    public function getTeamId()
    {
        return $this->teamId;
    }
}