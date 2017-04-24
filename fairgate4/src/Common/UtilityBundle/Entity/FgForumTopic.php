<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgForumTopic
 */
class FgForumTopic
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
    private $replies;

    /**
     * @var boolean
     */
    private $followTopic;

    /**
     * @var boolean
     */
    private $isImportant;

    /**
     * @var boolean
     */
    private $isSticky;

    /**
     * @var boolean
     */
    private $isClosed;

    /**
     * @var integer
     */
    private $views;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;

    /**
     * @var \Common\UtilityBundle\Entity\FgRmRole
     */
    private $group;


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
     * @return FgForumTopic
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
     * Set replies
     *
     * @param string $replies
     * @return FgForumTopic
     */
    public function setReplies($replies)
    {
        $this->replies = $replies;
    
        return $this;
    }

    /**
     * Get replies
     *
     * @return string 
     */
    public function getReplies()
    {
        return $this->replies;
    }

    /**
     * Set followTopic
     *
     * @param boolean $followTopic
     * @return FgForumTopic
     */
    public function setFollowTopic($followTopic)
    {
        $this->followTopic = $followTopic;
    
        return $this;
    }

    /**
     * Get followTopic
     *
     * @return boolean 
     */
    public function getFollowTopic()
    {
        return $this->followTopic;
    }

    /**
     * Set isImportant
     *
     * @param boolean $isImportant
     * @return FgForumTopic
     */
    public function setIsImportant($isImportant)
    {
        $this->isImportant = $isImportant;
    
        return $this;
    }

    /**
     * Get isImportant
     *
     * @return boolean 
     */
    public function getIsImportant()
    {
        return $this->isImportant;
    }

    /**
     * Set isSticky
     *
     * @param boolean $isSticky
     * @return FgForumTopic
     */
    public function setIsSticky($isSticky)
    {
        $this->isSticky = $isSticky;
    
        return $this;
    }

    /**
     * Get isSticky
     *
     * @return boolean 
     */
    public function getIsSticky()
    {
        return $this->isSticky;
    }

    /**
     * Set isClosed
     *
     * @param boolean $isClosed
     * @return FgForumTopic
     */
    public function setIsClosed($isClosed)
    {
        $this->isClosed = $isClosed;
    
        return $this;
    }

    /**
     * Get isClosed
     *
     * @return boolean 
     */
    public function getIsClosed()
    {
        return $this->isClosed;
    }

    /**
     * Set views
     *
     * @param integer $views
     * @return FgForumTopic
     */
    public function setViews($views)
    {
        $this->views = $views;
    
        return $this;
    }

    /**
     * Get views
     *
     * @return integer 
     */
    public function getViews()
    {
        return $this->views;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     * @return FgForumTopic
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
     * Set group
     *
     * @param \Common\UtilityBundle\Entity\FgRmRole $group
     * @return FgForumTopic
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
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var integer
     */
    private $createdBy;

    /**
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @var integer
     */
    private $updatedBy;


    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return FgForumTopic
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set createdBy
     *
     * @param integer $createdBy
     * @return FgForumTopic
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;
    
        return $this;
    }

    /**
     * Get createdBy
     *
     * @return integer 
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return FgForumTopic
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    
        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime 
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set updatedBy
     *
     * @param integer $updatedBy
     * @return FgForumTopic
     */
    public function setUpdatedBy($updatedBy)
    {
        $this->updatedBy = $updatedBy;
    
        return $this;
    }

    /**
     * Get updatedBy
     *
     * @return integer 
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }
}