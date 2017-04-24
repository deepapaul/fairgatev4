<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCmsPageContainerColumn
 */
class FgCmsPageContainerColumn
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $widthValue;

    /**
     * @var integer
     */
    private $sortOrder;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmsPageContainer
     */
    private $container;


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
     * Set widthValue
     *
     * @param integer $widthValue
     * @return FgCmsPageContainerColumn
     */
    public function setWidthValue($widthValue)
    {
        $this->widthValue = $widthValue;
    
        return $this;
    }

    /**
     * Get widthValue
     *
     * @return integer 
     */
    public function getWidthValue()
    {
        return $this->widthValue;
    }

    /**
     * Set sortOrder
     *
     * @param integer $sortOrder
     * @return FgCmsPageContainerColumn
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
     * Set container
     *
     * @param \Common\UtilityBundle\Entity\FgCmsPageContainer $container
     * @return FgCmsPageContainerColumn
     */
    public function setContainer(\Common\UtilityBundle\Entity\FgCmsPageContainer $container = null)
    {
        $this->container = $container;
    
        return $this;
    }

    /**
     * Get container
     *
     * @return \Common\UtilityBundle\Entity\FgCmsPageContainer 
     */
    public function getContainer()
    {
        return $this->container;
    }
}
