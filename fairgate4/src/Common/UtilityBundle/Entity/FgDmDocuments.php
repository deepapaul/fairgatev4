<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgDmDocuments
 */
class FgDmDocuments
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $author;

    /**
     * @var string
     */
    private $documentType;

    /**
     * @var string
     */
    private $visibleFor;

    /**
     * @var boolean
     */
    private $isVisibleToContact;

    /**
     * @var string
     */
    private $functionSel;

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
     * Set name
     *
     * @param string $name
     * @return FgDmDocuments
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return FgDmDocuments
     */
    public function setDescription($description)
    {
        $this->description = $description;
    
        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set author
     *
     * @param string $author
     * @return FgDmDocuments
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    
        return $this;
    }

    /**
     * Get author
     *
     * @return string 
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set documentType
     *
     * @param string $documentType
     * @return FgDmDocuments
     */
    public function setDocumentType($documentType)
    {
        $this->documentType = $documentType;
    
        return $this;
    }

    /**
     * Get documentType
     *
     * @return string 
     */
    public function getDocumentType()
    {
        return $this->documentType;
    }

    /**
     * Set visibleFor
     *
     * @param string $visibleFor
     * @return FgDmDocuments
     */
    public function setVisibleFor($visibleFor)
    {
        $this->visibleFor = $visibleFor;
    
        return $this;
    }

    /**
     * Get visibleFor
     *
     * @return string 
     */
    public function getVisibleFor()
    {
        return $this->visibleFor;
    }

    /**
     * Set isVisibleToContact
     *
     * @param integer $isVisibleToContact
     * @return FgDmDocuments
     */
    public function setIsVisibleToContact($isVisibleToContact)
    {
        $this->isVisibleToContact = $isVisibleToContact;
    
        return $this;
    }

    /**
     * Get isVisibleToContact
     *
     * @return integer 
     */
    public function getIsVisibleToContact()
    {
        return $this->isVisibleToContact;
    }

    /**
     * Set functionSel
     *
     * @param string $functionSel
     * @return FgDmDocuments
     */
    public function setFunctionSel($functionSel)
    {
        $this->functionSel = $functionSel;
    
        return $this;
    }

    /**
     * Get functionSel
     *
     * @return string 
     */
    public function getFunctionSel()
    {
        return $this->functionSel;
    }

    /**
     * @var integer
     */
    private $currentRevision;

    /**
     * @var string
     */
    private $filter;

    /**
     * @var string
     */
    private $depositedWith;

    /**
     * @var \Common\UtilityBundle\Entity\FgDmDocumentSubcategory
     */
    private $subcategory;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;


    /**
     * Set currentRevision
     *
     * @param integer $currentRevision
     * @return FgDmDocuments
     */
    public function setCurrentRevision($currentRevision)
    {
        $this->currentRevision = $currentRevision;
    
        return $this;
    }

    /**
     * Get currentRevision
     *
     * @return integer 
     */
    public function getCurrentRevision()
    {
        return $this->currentRevision;
    }

    /**
     * Set filter
     *
     * @param string $filter
     * @return FgDmDocuments
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;
    
        return $this;
    }

    /**
     * Get filter
     *
     * @return string 
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Set depositedWith
     *
     * @param string $depositedWith
     * @return FgDmDocuments
     */
    public function setDepositedWith($depositedWith)
    {
        $this->depositedWith = $depositedWith;
    
        return $this;
    }

    /**
     * Get depositedWith
     *
     * @return string 
     */
    public function getDepositedWith()
    {
        return $this->depositedWith;
    }

    /**
     * Set subcategory
     *
     * @param \Common\UtilityBundle\Entity\FgDmDocumentSubcategory $subcategory
     * @return FgDmDocuments
     */
    public function setSubcategory(\Common\UtilityBundle\Entity\FgDmDocumentSubcategory $subcategory = null)
    {
        $this->subcategory = $subcategory;
    
        return $this;
    }

    /**
     * Get subcategory
     *
     * @return \Common\UtilityBundle\Entity\FgDmDocumentSubcategory 
     */
    public function getSubcategory()
    {
        return $this->subcategory;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     * @return FgDmDocuments
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
     * @var \Common\UtilityBundle\Entity\FgDmDocumentCategory
     */
    private $category;


    /**
     * Set category
     *
     * @param \Common\UtilityBundle\Entity\FgDmDocumentCategory $category
     * @return FgDmDocuments
     */
    public function setCategory(\Common\UtilityBundle\Entity\FgDmDocumentCategory $category = null)
    {
        $this->category = $category;
    
        return $this;
    }

    /**
     * Get category
     *
     * @return \Common\UtilityBundle\Entity\FgDmDocumentCategory 
     */
    public function getCategory()
    {
        return $this->category;
    }
    /**
     * @var integer
     */
    private $isPublishLink;


    /**
     * Set isPublishLink
     *
     * @param integer $isPublishLink
     * @return FgDmDocuments
     */
    public function setIsPublishLink($isPublishLink)
    {
        $this->isPublishLink = $isPublishLink;
    
        return $this;
}

    /**
     * Get isPublishLink
     *
     * @return integer 
     */
    public function getIsPublishLink()
    {
        return $this->isPublishLink;
    }
}