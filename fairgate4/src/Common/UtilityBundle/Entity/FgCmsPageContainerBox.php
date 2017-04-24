<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCmsPageContainerBox
 */
class FgCmsPageContainerBox
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
     * @var \Common\UtilityBundle\Entity\FgCmsPageContainerColumn
     */
    private $column;


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
     * @return FgCmsPageContainerBox
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
     * Set column
     *
     * @param \Common\UtilityBundle\Entity\FgCmsPageContainerColumn $column
     * @return FgCmsPageContainerBox
     */
    public function setColumn(\Common\UtilityBundle\Entity\FgCmsPageContainerColumn $column = null)
    {
        $this->column = $column;
    
        return $this;
    }

    /**
     * Get column
     *
     * @return \Common\UtilityBundle\Entity\FgCmsPageContainerColumn 
     */
    public function getColumn()
    {
        return $this->column;
    }
}
