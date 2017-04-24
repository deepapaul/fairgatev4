<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgCmsPageAreas
 */
class FgCmsPageAreas
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmsPage
     */
    private $page;

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
     * Set page
     *
     * @param \Common\UtilityBundle\Entity\FgCmsPage $page
     *
     * @return FgCmsPageAreas
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
     * Set role
     *
     * @param \Common\UtilityBundle\Entity\FgRmRole $role
     *
     * @return FgCmsPageAreas
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

