<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgDmVersion
 */
class FgDmVersion
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $file;

    /**
     * @var \DateTime
     */
    private $createdAt;
    

    /**
     * @var integer
     */
    private $sortOrder;

    /**
     * @var string
     */
    private $size;

    /**
     * @var \Common\UtilityBundle\Entity\FgDmDocuments
     */
    private $document;


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
     * Set file
     *
     * @param string $file
     * @return FgDmVersion
     */
    public function setFile($file)
    {
        $this->file = $file;
    
        return $this;
    }

    /**
     * Get file
     *
     * @return string 
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return FgDmVersion
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
     * Set sortOrder
     *
     * @param integer $sortOrder
     * @return FgDmVersion
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
     * Set size
     *
     * @param string $size
     * @return FgDmVersion
     */
    public function setSize($size)
    {
        $this->size = $size;
    
        return $this;
    }

    /**
     * Get size
     *
     * @return string 
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set document
     *
     * @param \Common\UtilityBundle\Entity\FgDmDocuments $document
     * @return FgDmVersion
     */
    public function setDocument(\Common\UtilityBundle\Entity\FgDmDocuments $document = null)
    {
        $this->document = $document;
    
        return $this;
    }

    /**
     * Get document
     *
     * @return \Common\UtilityBundle\Entity\FgDmDocuments 
     */
    public function getDocument()
    {
        return $this->document;
    }    
    /**
     * @var integer
     */
    private $createdBy;

    /**
     * @var \DateTime
     */
    private $updatedAt;


    /**
     * Set createdBy
     *
     * @param integer $createdBy
     * @return FgDmVersion
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;
    
        return $this;
    }

    /**
     * Get createdBy
     *
     * @return integer 
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return FgDmVersion
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
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $updatedBy;


    /**
     * Set updatedBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $updatedBy
     * @return FgDmVersion
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