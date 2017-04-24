<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgEmCalendarDetailsAttachments
 */
class FgEmCalendarDetailsAttachments
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
     * @var integer
     */
    private $size;

    /**
     * @var \Common\UtilityBundle\Entity\FgEmCalendarDetails
     */
    private $calendarDetail;


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
     * @return FgEmCalendarDetailsAttachments
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
     * Set size
     *
     * @param integer $size
     * @return FgEmCalendarDetailsAttachments
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
     * Set calendarDetail
     *
     * @param \Common\UtilityBundle\Entity\FgEmCalendarDetails $calendarDetail
     * @return FgEmCalendarDetailsAttachments
     */
    public function setCalendarDetail(\Common\UtilityBundle\Entity\FgEmCalendarDetails $calendarDetail = null)
    {
        $this->calendarDetail = $calendarDetail;
    
        return $this;
    }

    /**
     * Get calendarDetail
     *
     * @return \Common\UtilityBundle\Entity\FgEmCalendarDetails 
     */
    public function getCalendarDetail()
    {
        return $this->calendarDetail;
    }
    /**
     * @var \Common\UtilityBundle\Entity\FgFileManager
     */
    private $fileManager;


    /**
     * Set fileManager
     *
     * @param \Common\UtilityBundle\Entity\FgFileManager $fileManager
     * @return FgEmCalendarDetailsAttachments
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
}