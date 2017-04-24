<?php

namespace Admin\UtilityBundle\Entity;

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
     * @var \Admin\UtilityBundle\Entity\FgClubClass
     */
    private $class;

    /**
     * @var \Admin\UtilityBundle\Entity\FgClub
     */
    private $assinedFederation;

    /**
     * @var \Admin\UtilityBundle\Entity\FgClub
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
     * @param \Admin\UtilityBundle\Entity\FgClubClass $class
     * @return FgClubClassAssignment
     */
    public function setClass(\Admin\UtilityBundle\Entity\FgClubClass $class = null)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Get class
     *
     * @return \Admin\UtilityBundle\Entity\FgClubClass
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set assinedFederation
     *
     * @param \Admin\UtilityBundle\Entity\FgClub $assinedFederation
     * @return FgClubClassAssignment
     */
    public function setAssinedFederation(\Admin\UtilityBundle\Entity\FgClub $assinedFederation = null)
    {
        $this->assinedFederation = $assinedFederation;

        return $this;
    }

    /**
     * Get assinedFederation
     *
     * @return \Admin\UtilityBundle\Entity\FgClub
     */
    public function getAssinedFederation()
    {
        return $this->assinedFederation;
    }

    /**
     * Set club
     *
     * @param \Admin\UtilityBundle\Entity\FgClub $club
     * @return FgClubClassAssignment
     */
    public function setClub(\Admin\UtilityBundle\Entity\FgClub $club = null)
    {
        $this->club = $club;

        return $this;
    }

    /**
     * Get club
     *
     * @return \Admin\UtilityBundle\Entity\FgClub
     */
    public function getClub()
    {
        return $this->club;
    }
    /**
     * @var integer
     */
    private $assinedFederationId;

    /**
     * @var integer
     */
    private $clubId;


    /**
     * Set assinedFederationId
     *
     * @param integer $assinedFederationId
     *
     * @return FgClubClassAssignment
     */
    public function setAssinedFederationId($assinedFederationId)
    {
        $this->assinedFederationId = $assinedFederationId;

        return $this;
    }

    /**
     * Get assinedFederationId
     *
     * @return integer
     */
    public function getAssinedFederationId()
    {
        return $this->assinedFederationId;
    }

    /**
     * Set clubId
     *
     * @param integer $clubId
     *
     * @return FgClubClassAssignment
     */
    public function setClubId($clubId)
    {
        $this->clubId = $clubId;

        return $this;
    }

    /**
     * Get clubId
     *
     * @return integer
     */
    public function getClubId()
    {
        return $this->clubId;
    }
}
