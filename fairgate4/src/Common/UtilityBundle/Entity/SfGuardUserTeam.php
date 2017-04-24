<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SfGuardUserTeam
 */
class SfGuardUserTeam
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $contactId;

    /**
     * @var integer
     */
    private $groupId;

    /**
     * @var integer
     */
    private $roleId;


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
     * Set contactId
     *
     * @param integer $contactId
     * @return SfGuardUserTeam
     */
    public function setContactId($contactId)
    {
        $this->contactId = $contactId;
    
        return $this;
    }

    /**
     * Get contactId
     *
     * @return integer 
     */
    public function getContactId()
    {
        return $this->contactId;
    }

    /**
     * Set groupId
     *
     * @param integer $groupId
     * @return SfGuardUserTeam
     */
    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;
    
        return $this;
    }

    /**
     * Get groupId
     *
     * @return integer 
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * Set roleId
     *
     * @param integer $roleId
     * @return SfGuardUserTeam
     */
    public function setRoleId($roleId)
    {
        $this->roleId = $roleId;
    
        return $this;
    }

    /**
     * Get roleId
     *
     * @return integer 
     */
    public function getRoleId()
    {
        return $this->roleId;
    }
    /**
     * @var \Common\UtilityBundle\Entity\FgRmRole
     */
    private $role;

    /**
     * @var \Common\UtilityBundle\Entity\SfGuardUser
     */
    private $user;

    /**
     * @var \Common\UtilityBundle\Entity\SfGuardGroup
     */
    private $group;


    /**
     * Set role
     *
     * @param \Common\UtilityBundle\Entity\FgRmRole $role
     * @return SfGuardUserTeam
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
     * Set user
     *
     * @param \Common\UtilityBundle\Entity\SfGuardUser $user
     * @return SfGuardUserTeam
     */
    public function setUser(\Common\UtilityBundle\Entity\SfGuardUser $user = null)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return \Common\UtilityBundle\Entity\SfGuardUser 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set group
     *
     * @param \Common\UtilityBundle\Entity\SfGuardGroup $group
     * @return SfGuardUserTeam
     */
    public function setGroup(\Common\UtilityBundle\Entity\SfGuardGroup $group = null)
    {
        $this->group = $group;
    
        return $this;
    }

    /**
     * Get group
     *
     * @return \Common\UtilityBundle\Entity\SfGuardGroup 
     */
    public function getGroup()
    {
        return $this->group;
    }
}