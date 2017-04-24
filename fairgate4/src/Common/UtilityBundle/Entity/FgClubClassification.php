<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgClubClassification
 */
class FgClubClassification
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
    private $sublevelAssign;

    /**
     * @var string
     */
    private $classAssign;

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
     * @return FgClubClassification
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
     * Set sublevelAssign
     *
     * @param string $sublevelAssign
     * @return FgClubClassification
     */
    public function setSublevelAssign($sublevelAssign)
    {
        $this->sublevelAssign = $sublevelAssign;

        return $this;
    }

    /**
     * Get sublevelAssign
     *
     * @return string
     */
    public function getSublevelAssign()
    {
        return $this->sublevelAssign;
    }

    /**
     * Set classAssign
     *
     * @param string $classAssign
     * @return FgClubClassification
     */
    public function setClassAssign($classAssign)
    {
        $this->classAssign = $classAssign;

        return $this;
    }

    /**
     * Get classAssign
     *
     * @return string
     */
    public function getClassAssign()
    {
        return $this->classAssign;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return FgClubClassification
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
     * @return FgClubClassification
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
     * @return FgClubClassification
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
}
