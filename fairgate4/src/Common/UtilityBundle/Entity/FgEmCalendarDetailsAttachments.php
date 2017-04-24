<?php

namespace Common\UtilityBundle\Entity;

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
     * @var \Common\UtilityBundle\Entity\FgFileManager
     */
    private $fileManager;

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
     * Set fileManager
     *
     * @param \Common\UtilityBundle\Entity\FgFileManager $fileManager
     *
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

    /**
     * Set calendarDetail
     *
     * @param \Common\UtilityBundle\Entity\FgEmCalendarDetails $calendarDetail
     *
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
}

