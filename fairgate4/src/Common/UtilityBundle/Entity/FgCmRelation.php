<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCmRelation
 */
class FgCmRelation
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var boolean
     */
    private $isHousehold;

    /**
     * @var boolean
     */
    private $isOtherPersonal;

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
     * Set name
     *
     * @param string $name
     * @return FgCmRelation
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set isHousehold
     *
     * @param boolean $isHousehold
     * @return FgCmRelation
     */
    public function setIsHousehold($isHousehold)
    {
        $this->isHousehold = $isHousehold;

        return $this;
    }

    /**
     * Get isHousehold
     *
     * @return boolean
     */
    public function getIsHousehold()
    {
        return $this->isHousehold;
    }

    /**
     * Set isOtherPersonal
     *
     * @param boolean $isOtherPersonal
     * @return FgCmRelation
     */
    public function setIsOtherPersonal($isOtherPersonal)
    {
        $this->isOtherPersonal = $isOtherPersonal;

        return $this;
    }

    /**
     * Get isOtherPersonal
     *
     * @return boolean
     */
    public function getIsOtherPersonal()
    {
        return $this->isOtherPersonal;
    }

    /**
     * Set sortOrder
     *
     * @param integer $sortOrder
     * @return FgCmRelation
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
     * @return FgCmRelation
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