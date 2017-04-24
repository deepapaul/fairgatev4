<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCmsContactTable
 */
class FgCmsContactTable
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $includeContacts;

    /**
     * @var string
     */
    private $excludeContacts;

    /**
     * @var string
     */
    private $columnData;

    /**
     * @var integer
     */
    private $rowPerpage;

    /**
     * @var string
     */
    private $overflowBehavior;

    /**
     * @var boolean
     */
    private $rowHighlighting;

    /**
     * @var boolean
     */
    private $tableSearch;

    /**
     * @var string
     */
    private $tableExport;

    /**
     * @var string
     */
    private $stage;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @var boolean
     */
    private $isDeleted;

    /**
     * @var string
     */
    private $displayType;

    /**
     * @var boolean
     */
    private $portraitPerRow;

    /**
     * @var string
     */
    private $initialSortingDetails;

    /**
     * @var string
     */
    private $initialSortOrder;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;

    /**
     * @var \Common\UtilityBundle\Entity\FgFilter
     */
    private $filter;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $createdBy;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $updatedBy;


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
     * Set includeContacts
     *
     * @param string $includeContacts
     * @return FgCmsContactTable
     */
    public function setIncludeContacts($includeContacts)
    {
        $this->includeContacts = $includeContacts;
    
        return $this;
    }

    /**
     * Get includeContacts
     *
     * @return string 
     */
    public function getIncludeContacts()
    {
        return $this->includeContacts;
    }

    /**
     * Set excludeContacts
     *
     * @param string $excludeContacts
     * @return FgCmsContactTable
     */
    public function setExcludeContacts($excludeContacts)
    {
        $this->excludeContacts = $excludeContacts;
    
        return $this;
    }

    /**
     * Get excludeContacts
     *
     * @return string 
     */
    public function getExcludeContacts()
    {
        return $this->excludeContacts;
    }

    /**
     * Set columnData
     *
     * @param string $columnData
     * @return FgCmsContactTable
     */
    public function setColumnData($columnData)
    {
        $this->columnData = $columnData;
    
        return $this;
    }

    /**
     * Get columnData
     *
     * @return string 
     */
    public function getColumnData()
    {
        return $this->columnData;
    }

    /**
     * Set rowPerpage
     *
     * @param integer $rowPerpage
     * @return FgCmsContactTable
     */
    public function setRowPerpage($rowPerpage)
    {
        $this->rowPerpage = $rowPerpage;
    
        return $this;
    }

    /**
     * Get rowPerpage
     *
     * @return integer 
     */
    public function getRowPerpage()
    {
        return $this->rowPerpage;
    }

    /**
     * Set overflowBehavior
     *
     * @param string $overflowBehavior
     * @return FgCmsContactTable
     */
    public function setOverflowBehavior($overflowBehavior)
    {
        $this->overflowBehavior = $overflowBehavior;
    
        return $this;
    }

    /**
     * Get overflowBehavior
     *
     * @return string 
     */
    public function getOverflowBehavior()
    {
        return $this->overflowBehavior;
    }

    /**
     * Set rowHighlighting
     *
     * @param boolean $rowHighlighting
     * @return FgCmsContactTable
     */
    public function setRowHighlighting($rowHighlighting)
    {
        $this->rowHighlighting = $rowHighlighting;
    
        return $this;
    }

    /**
     * Get rowHighlighting
     *
     * @return boolean 
     */
    public function getRowHighlighting()
    {
        return $this->rowHighlighting;
    }

    /**
     * Set tableExport
     *
     * @param string $tableExport
     * @return FgCmsContactTable
     */
    public function setTableExport($tableExport)
    {
        $this->tableExport = $tableExport;
    
        return $this;
    }

    /**
     * Get tableExport
     *
     * @return string 
     */
    public function getTableExport()
    {
        return $this->tableExport;
    }

    /**
     * Set tableSearch
     *
     * @param boolean $tableSearch
     * @return FgCmsContactTable
     */
    public function setTableSearch($tableSearch)
    {
        $this->tableSearch = $tableSearch;
    
        return $this;
    }

    /**
     * Get tableSearch
     *
     * @return boolean 
     */
    public function getTableSearch()
    {
        return $this->tableSearch;
    }

    /**
     * Set stage
     *
     * @param string $stage
     * @return FgCmsContactTable
     */
    public function setStage($stage)
    {
        $this->stage = $stage;
    
        return $this;
    }

    /**
     * Get stage
     *
     * @return string 
     */
    public function getStage()
    {
        return $this->stage;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return FgCmsContactTable
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
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return FgCmsContactTable
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
     * Set isDeleted
     *
     * @param boolean $isDeleted
     * @return FgCmsContactTable
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
     * Set displayType
     *
     * @param string $displayType
     * @return FgCmsContactTable
     */
    public function setDisplayType($displayType)
    {
        $this->displayType = $displayType;
    
        return $this;
    }

    /**
     * Get displayType
     *
     * @return string 
     */
    public function getDisplayType()
    {
        return $this->displayType;
    }

    /**
     * Set portraitPerRow
     *
     * @param boolean $portraitPerRow
     * @return FgCmsContactTable
     */
    public function setPortraitPerRow($portraitPerRow)
    {
        $this->portraitPerRow = $portraitPerRow;
    
        return $this;
    }

    /**
     * Get portraitPerRow
     *
     * @return boolean 
     */
    public function getPortraitPerRow()
    {
        return $this->portraitPerRow;
    }

    /**
     * Set initialSortingDetails
     *
     * @param string $initialSortingDetails
     * @return FgCmsContactTable
     */
    public function setInitialSortingDetails($initialSortingDetails)
    {
        $this->initialSortingDetails = $initialSortingDetails;
    
        return $this;
    }

    /**
     * Get initialSortingDetails
     *
     * @return string 
     */
    public function getInitialSortingDetails()
    {
        return $this->initialSortingDetails;
    }

    /**
     * Set initialSortOrder
     *
     * @param string $initialSortOrder
     * @return FgCmsContactTable
     */
    public function setInitialSortOrder($initialSortOrder)
    {
        $this->initialSortOrder = $initialSortOrder;
    
        return $this;
    }

    /**
     * Get initialSortOrder
     *
     * @return string 
     */
    public function getInitialSortOrder()
    {
        return $this->initialSortOrder;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     * @return FgCmsContactTable
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
     * Set filter
     *
     * @param \Common\UtilityBundle\Entity\FgFilter $filter
     * @return FgCmsContactTable
     */
    public function setFilter(\Common\UtilityBundle\Entity\FgFilter $filter = null)
    {
        $this->filter = $filter;
    
        return $this;
    }

    /**
     * Get filter
     *
     * @return \Common\UtilityBundle\Entity\FgFilter 
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Set createdBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $createdBy
     * @return FgCmsContactTable
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
     * Set updatedBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $updatedBy
     * @return FgCmsContactTable
     */
    public function setUpdatedBy(\Common\UtilityBundle\Entity\FgCmContact $updatedBy = null)
    {
        $this->updatedBy = $updatedBy;
    
        return $this;
    }

    /**
     * Get updatedBy
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact 
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }
}
