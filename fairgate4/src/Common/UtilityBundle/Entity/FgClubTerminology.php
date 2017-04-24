<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgClubTerminology
 */
class FgClubTerminology
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $defaultTerm;

    /**
     * @var string
     */
    private $singular;

    /**
     * @var string
     */
    private $plural;

    /**
     * @var integer
     */
    private $sortOrder;

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
     * Set defaultTerm
     *
     * @param string $defaultTerm
     * @return FgClubTerminology
     */
    public function setDefaultTerm($defaultTerm)
    {
        $this->defaultTerm = $defaultTerm;

        return $this;
    }

    /**
     * Get defaultTerm
     *
     * @return string
     */
    public function getDefaultTerm()
    {
        return $this->defaultTerm;
    }

    /**
     * Set singular
     *
     * @param string $singular
     * @return FgClubTerminology
     */
    public function setSingular($singular)
    {
        $this->singular = $singular;

        return $this;
    }

    /**
     * Get singular
     *
     * @return string
     */
    public function getSingular()
    {
        return $this->singular;
    }

    /**
     * Set plural
     *
     * @param string $plural
     * @return FgClubTerminology
     */
    public function setPlural($plural)
    {
        $this->plural = $plural;

        return $this;
    }

    /**
     * Get plural
     *
     * @return string
     */
    public function getPlural()
    {
        return $this->plural;
    }

    /**
     * Set sortOrder
     *
     * @param integer $sortOrder
     * @return FgClubTerminology
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    /**
     * Get sortOrder
     *
     * @return integer
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     * @return FgClubTerminology
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
     * @var string
     */
    private $defaultSingularTerm;

    /**
     * @var string
     */
    private $defaultPluralTerm;


    /**
     * Set defaultSingularTerm
     *
     * @param string $defaultSingularTerm
     * @return FgClubTerminology
     */
    public function setDefaultSingularTerm($defaultSingularTerm)
    {
        $this->defaultSingularTerm = $defaultSingularTerm;

        return $this;
    }

    /**
     * Get defaultSingularTerm
     *
     * @return string
     */
    public function getDefaultSingularTerm()
    {
        return $this->defaultSingularTerm;
    }

    /**
     * Set defaultPluralTerm
     *
     * @param string $defaultPluralTerm
     * @return FgClubTerminology
     */
    public function setDefaultPluralTerm($defaultPluralTerm)
    {
        $this->defaultPluralTerm = $defaultPluralTerm;

        return $this;
    }

    /**
     * Get defaultPluralTerm
     *
     * @return string
     */
    public function getDefaultPluralTerm()
    {
        return $this->defaultPluralTerm;
    }
    /**
     * @var boolean
     */
    private $isFederation;


    /**
     * Set isFederation
     *
     * @param boolean $isFederation
     * @return FgClubTerminology
     */
    public function setIsFederation($isFederation)
    {
        $this->isFederation = $isFederation;

        return $this;
    }

    /**
     * Get isFederation
     *
     * @return boolean
     */
    public function getIsFederation()
    {
        return $this->isFederation;
    }
}