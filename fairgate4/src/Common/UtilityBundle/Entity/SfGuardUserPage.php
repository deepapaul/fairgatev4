<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SfGuardUserPage
 */
class SfGuardUserPage
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $pageId;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \Common\UtilityBundle\Entity\SfGuardUser
     */
    private $user;

    /**
     * @var \Common\UtilityBundle\Entity\SfGuardGroup
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
     * Set pageId
     *
     * @param integer $pageId
     * @return SfGuardUserPage
     */
    public function setPageId($pageId)
    {
        $this->pageId = $pageId;
    
        return $this;
    }

    /**
     * Get pageId
     *
     * @return integer 
     */
    public function getPageId()
    {
        return $this->pageId;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return SfGuardUserPage
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
     * Set user
     *
     * @param \Common\UtilityBundle\Entity\SfGuardUser $user
     * @return SfGuardUserPage
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
     * @return SfGuardUserPage
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
    /**
     * @var \Common\UtilityBundle\Entity\FgCmsPage
     */
    private $page;


    /**
     * Set page
     *
     * @param \Common\UtilityBundle\Entity\FgCmsPage $page
     * @return SfGuardUserPage
     */
    public function setPage(\Common\UtilityBundle\Entity\FgCmsPage $page = null)
    {
        $this->page = $page;
    
        return $this;
    }

    /**
     * Get page
     *
     * @return \Common\UtilityBundle\Entity\FgCmsPage 
     */
    public function getPage()
    {
        return $this->page;
    }
}