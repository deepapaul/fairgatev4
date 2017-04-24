<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgClubClass
 */
class FgClubClass
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
     * @var boolean
     */
    private $isActive;

    /**
     * @var integer
     */
    private $sortOrder;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $federation;

    /**
     * @var \Common\UtilityBundle\Entity\FgClubClassification
     */
    private $classification;


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
     * @return FgClubClass
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
     * Set isActive
     *
     * @param boolean $isActive
     * @return FgClubClass
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set sortOrder
     *
     * @param integer $sortOrder
     * @return FgClubClass
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
     * Set federation
     *
     * @param \Common\UtilityBundle\Entity\FgClub $federation
     * @return FgClubClass
     */
    public function setFederation(\Common\UtilityBundle\Entity\FgClub $federation = null)
    {
        $this->federation = $federation;

        return $this;
    }

    /**
     * Get federation
     *
     * @return \Common\UtilityBundle\Entity\FgClub
     */
    public function getFederation()
    {
        return $this->federation;
    }

    /**
     * Set classification
     *
     * @param \Common\UtilityBundle\Entity\FgClubClassification $classification
     * @return FgClubClass
     */
    public function setClassification(\Common\UtilityBundle\Entity\FgClubClassification $classification = null)
    {
        $this->classification = $classification;

        return $this;
    }

    /**
     * Get classification
     *
     * @return \Common\UtilityBundle\Entity\FgClubClassification
     */
    public function getClassification()
    {
        return $this->classification;
    }
}
