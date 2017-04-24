<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgRmRoleFunction
 */
class FgRmRoleFunction
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Common\UtilityBundle\Entity\FgRmFunction
     */
    private $function;

    /**
     * @var \Common\UtilityBundle\Entity\FgRmRole
     */
    private $role;


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
     * Set function
     *
     * @param \Common\UtilityBundle\Entity\FgRmFunction $function
     *
     * @return FgRmRoleFunction
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
     * Set role
     *
     * @param \Common\UtilityBundle\Entity\FgRmRole $role
     *
     * @return FgRmRoleFunction
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


}