<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCmRelationSecondLevel
 */
class FgCmRelationSecondLevel
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $isSystem;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmRelation
     */
    private $relation;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmRelation
     */
    private $firstLevelRelation;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmRelation
     */
    private $secondLevelRelation;


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
     * Set isSystem
     *
     * @param boolean $isSystem
     * @return FgCmRelationSecondLevel
     */
    public function setIsSystem($isSystem)
    {
        $this->isSystem = $isSystem;

        return $this;
    }

    /**
     * Get isSystem
     *
     * @return boolean
     */
    public function getIsSystem()
    {
        return $this->isSystem;
    }

    /**
     * Set relation
     *
     * @param \Common\UtilityBundle\Entity\FgCmRelation $relation
     * @return FgCmRelationSecondLevel
     */
    public function setRelation(\Common\UtilityBundle\Entity\FgCmRelation $relation = null)
    {
        $this->relation = $relation;

        return $this;
    }

    /**
     * Get relation
     *
     * @return \Common\UtilityBundle\Entity\FgCmRelation
     */
    public function getRelation()
    {
        return $this->relation;
    }

    /**
     * Set firstLevelRelation
     *
     * @param \Common\UtilityBundle\Entity\FgCmRelation $firstLevelRelation
     * @return FgCmRelationSecondLevel
     */
    public function setFirstLevelRelation(\Common\UtilityBundle\Entity\FgCmRelation $firstLevelRelation = null)
    {
        $this->firstLevelRelation = $firstLevelRelation;

        return $this;
    }

    /**
     * Get firstLevelRelation
     *
     * @return \Common\UtilityBundle\Entity\FgCmRelation
     */
    public function getFirstLevelRelation()
    {
        return $this->firstLevelRelation;
    }

    /**
     * Set secondLevelRelation
     *
     * @param \Common\UtilityBundle\Entity\FgCmRelation $secondLevelRelation
     * @return FgCmRelationSecondLevel
     */
    public function setSecondLevelRelation(\Common\UtilityBundle\Entity\FgCmRelation $secondLevelRelation = null)
    {
        $this->secondLevelRelation = $secondLevelRelation;

        return $this;
    }

    /**
     * Get secondLevelRelation
     *
     * @return \Common\UtilityBundle\Entity\FgCmRelation
     */
    public function getSecondLevelRelation()
    {
        return $this->secondLevelRelation;
    }
}