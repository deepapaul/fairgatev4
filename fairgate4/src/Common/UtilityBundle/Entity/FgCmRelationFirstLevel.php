<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgCmRelationFirstLevel
 */
class FgCmRelationFirstLevel
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
     *
     * @return FgCmRelationFirstLevel
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
     *
     * @return FgCmRelationFirstLevel
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
     *
     * @return FgCmRelationFirstLevel
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
}

