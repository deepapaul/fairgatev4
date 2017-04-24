<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgMessageGroup
 */
class FgMessageGroup
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Common\UtilityBundle\Entity\FgRmRole
     */
    private $role;

    /**
     * @var \Common\UtilityBundle\Entity\FgMessage
     */
    private $message;


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
     * Set role
     *
     * @param \Common\UtilityBundle\Entity\FgRmRole $role
     *
     * @return FgMessageGroup
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
     * Set message
     *
     * @param \Common\UtilityBundle\Entity\FgMessage $message
     *
     * @return FgMessageGroup
     */
    public function setMessage(\Common\UtilityBundle\Entity\FgMessage $message = null)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return \Common\UtilityBundle\Entity\FgMessage
     */
    public function getMessage()
    {
        return $this->message;
    }
}

