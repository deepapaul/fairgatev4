<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgClubClassAssignment
 */
class FgClubClassAssignment
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Common\UtilityBundle\Entity\FgClubClass
     */
    private $class;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $assinedFederation;

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
     * Set class
     *
     * @param \Common\UtilityBundle\Entity\FgClubClass $class
     * @return FgClubClassAssignment
     */
    public function setClass(\Common\UtilityBundle\Entity\FgClubClass $class = null)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Get class
     *
     * @return \Common\UtilityBundle\Entity\FgClubClass
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set assinedFederation
     *
     * @param \Common\UtilityBundle\Entity\FgClub $assinedFederation
     * @return FgClubClassAssignment
     */
    public function setAssinedFederation(\Common\UtilityBundle\Entity\FgClub $assinedFederation = null)
    {
        $this->assinedFederation = $assinedFederation;

        return $this;
    }

    /**
     * Get assinedFederation
     *
     * @return \Common\UtilityBundle\Entity\FgClub
     */
    public function getAssinedFederation()
    {
        return $this->assinedFederation;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     * @return FgClubClassAssignment
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
