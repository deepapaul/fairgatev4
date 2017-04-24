<?php

namespace Common\UtilityBundle\Entity;

/**
 * SfGuardUserClub
 */
class SfGuardUserClub
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;

    /**
     * @var \Common\UtilityBundle\Entity\SfGuardUser
     */
    private $user;


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
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     *
     * @return SfGuardUserClub
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
     * Set user
     *
     * @param \Common\UtilityBundle\Entity\SfGuardUser $user
     *
     * @return SfGuardUserClub
     */
    public function setUser(\Common\UtilityBundle\Entity\SfGuardUser $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Common\UtilityBundle\Entity\SfGuardUser
     */
    public function getUser()
    {
        return $this->user;
    }
}

