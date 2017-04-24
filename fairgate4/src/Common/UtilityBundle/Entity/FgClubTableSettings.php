<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgClubTableSettings
 */
class FgClubTableSettings
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $attributes;

    /**
     * @var integer
     */
    private $rows;

    /**
     * @var boolean
     */
    private $isTemp;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $contact;


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
     * Set title
     *
     * @param string $title
     * @return FgClubTableSettings
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set attributes
     *
     * @param string $attributes
     * @return FgClubTableSettings
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Get attributes
     *
     * @return string
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Set rows
     *
     * @param integer $rows
     * @return FgClubTableSettings
     */
    public function setRows($rows)
    {
        $this->rows = $rows;

        return $this;
    }

    /**
     * Get rows
     *
     * @return integer
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * Set isTemp
     *
     * @param boolean $isTemp
     * @return FgClubTableSettings
     */
    public function setIsTemp($isTemp)
    {
        $this->isTemp = $isTemp;

        return $this;
    }

    /**
     * Get isTemp
     *
     * @return boolean
     */
    public function getIsTemp()
    {
        return $this->isTemp;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     * @return FgClubTableSettings
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
     * Set contact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $contact
     * @return FgClubTableSettings
     */
    public function setContact(\Common\UtilityBundle\Entity\FgCmContact $contact = null)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Get contact
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact
     */
    public function getContact()
    {
        return $this->contact;
    }
}