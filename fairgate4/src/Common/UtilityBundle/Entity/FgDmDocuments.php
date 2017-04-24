<?php

namespace Common\UtilityBundle\Entity;

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
    private $filter;

    /**
     * @var string
     */
    private $documentType;

    /**
     * @var string
     */
    private $depositedWith;

    /**
     * @var string
     */
    private $visibleFor;

    /**
     * @var integer
     */
    private $isVisibleToContact;

    /**
     * @var string
     */
    private $functionSel;

    /**
     * @var integer
     */
    private $isPublishLink;

    /**
     * @var \Common\UtilityBundle\Entity\FgDmDocumentSubcategory
     */
    private $subcategory;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;

    /**
     * @var \Common\UtilityBundle\Entity\FgDmVersion
     */
    private $currentRevision;

    /**
     * @var \Common\UtilityBundle\Entity\FgDmDocumentCategory
     */
    private $category;


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
     *
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
     *
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
     *
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
     * Set filter
     *
     * @param string $filter
     *
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
     * Set documentType
     *
     * @param string $documentType
     *
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
     * Set depositedWith
     *
     * @param string $depositedWith
     *
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
     * Set visibleFor
     *
     * @param string $visibleFor
     *
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
     *
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
     *
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
     * Set isPublishLink
     *
     * @param integer $isPublishLink
     *
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

    /**
     * Set subcategory
     *
     * @param \Common\UtilityBundle\Entity\FgDmDocumentSubcategory $subcategory
     *
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
     *
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
     * Set currentRevision
     *
     * @param \Common\UtilityBundle\Entity\FgDmVersion $currentRevision
     *
     * @return FgDmDocuments
     */
    public function setCurrentRevision(\Common\UtilityBundle\Entity\FgDmVersion $currentRevision = null)
    {
        $this->currentRevision = $currentRevision;

        return $this;
    }

    /**
     * Get currentRevision
     *
     * @return \Common\UtilityBundle\Entity\FgDmVersion
     */
    public function getCurrentRevision()
    {
        return $this->currentRevision;
    }

    /**
     * Set category
     *
     * @param \Common\UtilityBundle\Entity\FgDmDocumentCategory $category
     *
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
}

