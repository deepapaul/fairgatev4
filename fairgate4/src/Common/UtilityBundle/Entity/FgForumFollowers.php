<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgForumFollowers
 */
class FgForumFollowers
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $isFollowForum;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $contact;

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
     * Set isFollowForum
     *
     * @param boolean $isFollowForum
     * @return FgForumFollowers
     */
    public function setIsFollowForum($isFollowForum)
    {
        $this->isFollowForum = $isFollowForum;
    
        return $this;
    }

    /**
     * Get isFollowForum
     *
     * @return boolean 
     */
    public function getIsFollowForum()
    {
        return $this->isFollowForum;
    }

    /**
     * Set contact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $contact
     * @return FgForumFollowers
     */
    public function setContact(\Common\UtilityBundle\Entity\FgCmContact $contact = null)
    {
        $this->contact = $contact;
    
        return $this;
    }

    /**
     * Get contact
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact 
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Set group
     *
     * @param \Common\UtilityBundle\Entity\FgRmRole $group
     * @return FgForumFollowers
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
     * @return FgForumFollowers
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
