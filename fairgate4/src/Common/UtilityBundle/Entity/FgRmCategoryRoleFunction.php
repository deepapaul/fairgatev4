<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgRmCategoryRoleFunction
 */
class FgRmCategoryRoleFunction
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $fnCount;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;

    /**
     * @var \Common\UtilityBundle\Entity\FgRmRole
     */
    private $role;

    /**
     * @var \Common\UtilityBundle\Entity\FgRmFunction
     */
    private $function;

    /**
     * @var \Common\UtilityBundle\Entity\FgRmCategory
     */
    private $category;


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
     * Set fnCount
     *
     * @param integer $fnCount
     *
     * @return FgRmCategoryRoleFunction
     */
    public function setFnCount($fnCount)
    {
        $this->fnCount = $fnCount;

        return $this;
    }

    /**
     * Get fnCount
     *
     * @return integer
     */
    public function getFnCount()
    {
        return $this->fnCount;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     *
     * @return FgRmCategoryRoleFunction
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
     * Set role
     *
     * @param \Common\UtilityBundle\Entity\FgRmRole $role
     *
     * @return FgRmCategoryRoleFunction
     */
    public function setRole(\Common\UtilityBundle\Entity\FgRmRole $role = null)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return \Common\UtilityBundle\Entity\FgRmRole
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set function
     *
     * @param \Common\UtilityBundle\Entity\FgRmFunction $function
     *
     * @return FgRmCategoryRoleFunction
     */
    public function setFunction(\Common\UtilityBundle\Entity\FgRmFunction $function = null)
    {
        $this->function = $function;

        return $this;
    }

    /**
     * Get function
     *
     * @return \Common\UtilityBundle\Entity\FgRmFunction
     */
    public function getFunction()
    {
        return $this->function;
    }

    /**
     * Set category
     *
     * @param \Common\UtilityBundle\Entity\FgRmCategory $category
     *
     * @return FgRmCategoryRoleFunction
     */
    public function setCategory(\Common\UtilityBundle\Entity\FgRmCategory $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \Common\UtilityBundle\Entity\FgRmCategory
     */
    public function getCategory()
    {
        return $this->category;
    }
}

