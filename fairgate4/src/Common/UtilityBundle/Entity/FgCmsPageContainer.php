<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCmsPageContainer
 */
class FgCmsPageContainer
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $sortOrder;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmsPage
     */
    private $page;


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
     * Set sortOrder
     *
     * @param integer $sortOrder
     * @return FgCmsPageContainer
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
     * Set page
     *
     * @param \Common\UtilityBundle\Entity\FgCmsPage $page
     * @return FgCmsPageContainer
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
