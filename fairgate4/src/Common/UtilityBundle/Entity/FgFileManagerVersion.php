<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgFileManagerVersion
 */
class FgFileManagerVersion
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var integer
     */
    private $size;

    /**
     * @var \DateTime
     */
    private $uploadedAt;

    /**
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $updatedBy;

    /**
     * @var \Common\UtilityBundle\Entity\FgFileManager
     */
    private $fileManager;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $uploadedBy;


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
     * Set filename
     *
     * @param string $filename
     * @return FgFileManagerVersion
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    
        return $this;
    }

    /**
     * Get filename
     *
     * @return string 
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set size
     *
     * @param integer $size
     * @return FgFileManagerVersion
     */
    public function setSize($size)
    {
        $this->size = $size;
    
        return $this;
    }

    /**
     * Get size
     *
     * @return integer 
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set uploadedAt
     *
     * @param \DateTime $uploadedAt
     * @return FgFileManagerVersion
     */
    public function setUploadedAt($uploadedAt)
    {
        $this->uploadedAt = $uploadedAt;
    
        return $this;
    }

    /**
     * Get uploadedAt
     *
     * @return \DateTime 
     */
    public function getUploadedAt()
    {
        return $this->uploadedAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return FgFileManagerVersion
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
     * Set updatedBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $updatedBy
     * @return FgFileManagerVersion
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

    /**
     * Set fileManager
     *
     * @param \Common\UtilityBundle\Entity\FgFileManager $fileManager
     * @return FgFileManagerVersion
     */
    public function setFileManager(\Common\UtilityBundle\Entity\FgFileManager $fileManager = null)
    {
        $this->fileManager = $fileManager;
    
        return $this;
    }

    /**
     * Get fileManager
     *
     * @return \Common\UtilityBundle\Entity\FgFileManager 
     */
    public function getFileManager()
    {
        return $this->fileManager;
    }

    /**
     * Set uploadedBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $uploadedBy
     * @return FgFileManagerVersion
     */
    public function setUploadedBy(\Common\UtilityBundle\Entity\FgCmContact $uploadedBy = null)
    {
        $this->uploadedBy = $uploadedBy;
    
        return $this;
    }

    /**
     * Get uploadedBy
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact 
     */
    public function getUploadedBy()
    {
        return $this->uploadedBy;
    }
    /**
     * @var string
     */
    private $mimeType;


    /**
     * Set mimeType
     *
     * @param string $mimeType
     * @return FgFileManagerVersion
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;
    
        return $this;
    }

    /**
     * Get mimeType
     *
     * @return string 
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }
}