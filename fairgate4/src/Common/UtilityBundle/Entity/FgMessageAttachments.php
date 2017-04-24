<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgMessageAttachments
 */
class FgMessageAttachments
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
     * @var \Common\UtilityBundle\Entity\FgMessageData
     */
    private $messageData;


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
     * @return FgMessageAttachments
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
     * Set messageData
     *
     * @param \Common\UtilityBundle\Entity\FgMessageData $messageData
     * @return FgMessageAttachments
     */
    public function setMessageData(\Common\UtilityBundle\Entity\FgMessageData $messageData = null)
    {
        $this->messageData = $messageData;

        return $this;
    }

    /**
     * Get messageData
     *
     * @return \Common\UtilityBundle\Entity\FgMessageData
     */
    public function getMessageData()
    {
        return $this->messageData;
    }
    /**
     * @var integer
     */
    private $size;


    /**
     * Set size
     *
     * @param integer $size
     * @return FgMessageAttachments
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
}