<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCmsContactTableFilter
 */
class FgCmsContactTableFilter
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $filterType;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $filterSubtypeIds;

    /**
     * @var integer
     */
    private $sortOrder;

    /**
     * @var boolean
     */
    private $isDeleted;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmsContactTable
     */
    private $table;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmAttribute
     */
    private $attribute;


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
     * Set filterType
     *
     * @param string $filterType
     * @return FgCmsContactTableFilter
     */
    public function setFilterType($filterType)
    {
        $this->filterType = $filterType;
    
        return $this;
    }

    /**
     * Get filterType
     *
     * @return string 
     */
    public function getFilterType()
    {
        return $this->filterType;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return FgCmsContactTableFilter
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
     * Set filterSubtypeIds
     *
     * @param string $filterSubtypeIds
     * @return FgCmsContactTableFilter
     */
    public function setFilterSubtypeIds($filterSubtypeIds)
    {
        $this->filterSubtypeIds = $filterSubtypeIds;
    
        return $this;
    }

    /**
     * Get filterSubtypeIds
     *
     * @return string 
     */
    public function getFilterSubtypeIds()
    {
        return $this->filterSubtypeIds;
    }

    /**
     * Set sortOrder
     *
     * @param integer $sortOrder
     * @return FgCmsContactTableFilter
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
     * Set isDeleted
     *
     * @param boolean $isDeleted
     * @return FgCmsContactTableFilter
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;
    
        return $this;
    }

    /**
     * Get isDeleted
     *
     * @return boolean 
     */
    public function getIsDeleted()
    {
        return $this->isDeleted;
    }

    /**
     * Set table
     *
     * @param \Common\UtilityBundle\Entity\FgCmsContactTable $table
     * @return FgCmsContactTableFilter
     */
    public function setTable(\Common\UtilityBundle\Entity\FgCmsContactTable $table = null)
    {
        $this->table = $table;
    
        return $this;
    }

    /**
     * Get table
     *
     * @return \Common\UtilityBundle\Entity\FgCmsContactTable 
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Set attribute
     *
     * @param \Common\UtilityBundle\Entity\FgCmAttribute $attribute
     * @return FgCmsContactTableFilter
     */
    public function setAttribute(\Common\UtilityBundle\Entity\FgCmAttribute $attribute = null)
    {
        $this->attribute = $attribute;
    
        return $this;
    }

    /**
     * Get attribute
     *
     * @return \Common\UtilityBundle\Entity\FgCmAttribute 
     */
    public function getAttribute()
    {
        return $this->attribute;
    }
}
