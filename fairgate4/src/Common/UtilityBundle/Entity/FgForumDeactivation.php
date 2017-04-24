<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgForumDeactivation
 */
class FgForumDeactivation
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $isDeactivatedForum;

    /**
     * @var \Common\UtilityBundle\Entity\FgRmRole
     */
    private $group;

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
     * Set isDeactivatedForum
     *
     * @param boolean $isDeactivatedForum
     * @return FgForumDeactivation
     */
    public function setIsDeactivatedForum($isDeactivatedForum)
    {
        $this->isDeactivatedForum = $isDeactivatedForum;
    
        return $this;
    }

    /**
     * Get isDeactivatedForum
     *
     * @return boolean 
     */
    public function getIsDeactivatedForum()
    {
        return $this->isDeactivatedForum;
    }

    /**
     * Set group
     *
     * @param \Common\UtilityBundle\Entity\FgRmRole $group
     * @return FgForumDeactivation
     */
    public function setGroup(\Common\UtilityBundle\Entity\FgRmRole $group = null)
    {
        $this->group = $group;
    
        return $this;
    }

    /**
     * Get group
     *
     * @return \Common\UtilityBundle\Entity\FgRmRole 
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     * @return FgForumDeactivation
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
