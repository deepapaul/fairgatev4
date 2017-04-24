<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgCmsNavigation
 */
class FgCmsNavigation
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
     * @var boolean
     */
    private $isActive;

    /**
     * @var integer
     */
    private $sortOrder;

    /**
     * @var boolean
     */
    private $isPublic;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $editedAt;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $externalLink;

    /**
     * @var string
     */
    private $navigationUrl;

    /**
     * @var boolean
     */
    private $isAdditional;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmsNavigation
     */
    private $parent;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmsPage
     */
    private $page;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $createdBy;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $editedBy;


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
     *
     * @return FgCmsNavigation
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
     * Set isActive
     *
     * @param boolean $isActive
     *
     * @return FgCmsNavigation
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set sortOrder
     *
     * @param integer $sortOrder
     *
     * @return FgCmsNavigation
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    /**
     * Get sortOrder
     *
     * @return integer
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * Set isPublic
     *
     * @param boolean $isPublic
     *
     * @return FgCmsNavigation
     */
    public function setIsPublic($isPublic)
    {
        $this->isPublic = $isPublic;

        return $this;
    }

    /**
     * Get isPublic
     *
     * @return boolean
     */
    public function getIsPublic()
    {
        return $this->isPublic;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return FgCmsNavigation
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
     * Set editedAt
     *
     * @param \DateTime $editedAt
     *
     * @return FgCmsNavigation
     */
    public function setEditedAt($editedAt)
    {
        $this->editedAt = $editedAt;

        return $this;
    }

    /**
     * Get editedAt
     *
     * @return \DateTime
     */
    public function getEditedAt()
    {
        return $this->editedAt;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return FgCmsNavigation
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set externalLink
     *
     * @param string $externalLink
     *
     * @return FgCmsNavigation
     */
    public function setExternalLink($externalLink)
    {
        $this->externalLink = $externalLink;

        return $this;
    }

    /**
     * Get externalLink
     *
     * @return string
     */
    public function getExternalLink()
    {
        return $this->externalLink;
    }

    /**
     * Set navigationUrl
     *
     * @param string $navigationUrl
     *
     * @return FgCmsNavigation
     */
    public function setNavigationUrl($navigationUrl)
    {
        $this->navigationUrl = $navigationUrl;

        return $this;
    }

    /**
     * Get navigationUrl
     *
     * @return string
     */
    public function getNavigationUrl()
    {
        return $this->navigationUrl;
    }

    /**
     * Set isAdditional
     *
     * @param boolean $isAdditional
     *
     * @return FgCmsNavigation
     */
    public function setIsAdditional($isAdditional)
    {
        $this->isAdditional = $isAdditional;

        return $this;
    }

    /**
     * Get isAdditional
     *
     * @return boolean
     */
    public function getIsAdditional()
    {
        return $this->isAdditional;
    }

    /**
     * Set parent
     *
     * @param \Common\UtilityBundle\Entity\FgCmsNavigation $parent
     *
     * @return FgCmsNavigation
     */
    public function setParent(\Common\UtilityBundle\Entity\FgCmsNavigation $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \Common\UtilityBundle\Entity\FgCmsNavigation
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set page
     *
     * @param \Common\UtilityBundle\Entity\FgCmsPage $page
     *
     * @return FgCmsNavigation
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

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     *
     * @return FgCmsNavigation
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
     * Set createdBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $createdBy
     *
     * @return FgCmsNavigation
     */
    public function setCreatedBy(\Common\UtilityBundle\Entity\FgCmContact $createdBy = null)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set editedBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $editedBy
     *
     * @return FgCmsNavigation
     */
    public function setEditedBy(\Common\UtilityBundle\Entity\FgCmContact $editedBy = null)
    {
        $this->editedBy = $editedBy;

        return $this;
    }

    /**
     * Get editedBy
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact
     */
    public function getEditedBy()
    {
        return $this->editedBy;
    }
}

