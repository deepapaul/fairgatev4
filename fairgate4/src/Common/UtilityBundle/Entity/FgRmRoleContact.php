<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgRmRoleContact
 */
class FgRmRoleContact
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Common\UtilityBundle\Entity\FgRmCategoryRoleFunction
     */
    private $fgRmCrf;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $contact;


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
     * Set fgRmCrf
     *
     * @param \Common\UtilityBundle\Entity\FgRmCategoryRoleFunction $fgRmCrf
     *
     * @return FgRmRoleContact
     */
    public function setFgRmCrf(\Common\UtilityBundle\Entity\FgRmCategoryRoleFunction $fgRmCrf = null)
    {
        $this->fgRmCrf = $fgRmCrf;

        return $this;

    }


    /**
     * Get fgRmCrf
     *
     * @return \Common\UtilityBundle\Entity\FgRmCategoryRoleFunction
     */
    public function getFgRmCrf()
    {
        return $this->fgRmCrf;

    }


    /**
     * Set contact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $contact
     *
     * @return FgRmRoleContact
     */
    public function setContact(\Common\UtilityBundle\Entity\FgCmContact $contact = null)
    {
        $this->contact = $contact;

        return $this;

    }


    /**
     * Get contact
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact
     */
    public function getContact()
    {
        return $this->contact;

    }
    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $contactClub;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $assinedClub;


    /**
     * Set contactClub
     *
     * @param \Common\UtilityBundle\Entity\FgClub $contactClub
     *
     * @return FgRmRoleContact
     */
    public function setContactClub(\Common\UtilityBundle\Entity\FgClub $contactClub = null)
    {
        $this->contactClub = $contactClub;

        return $this;

    }


    /**
     * Get contactClub
     *
     * @return \Common\UtilityBundle\Entity\FgClub
     */
    public function getContactClub()
    {
        return $this->contactClub;

    }


    /**
     * Set assinedClub
     *
     * @param \Common\UtilityBundle\Entity\FgClub $assinedClub
     *
     * @return FgRmRoleContact
     */
    public function setAssinedClub(\Common\UtilityBundle\Entity\FgClub $assinedClub = null)
    {
        $this->assinedClub = $assinedClub;

        return $this;

    }


    /**
     * Get assinedClub
     *
     * @return \Common\UtilityBundle\Entity\FgClub
     */
    public function getAssinedClub()
    {
        return $this->assinedClub;

    }
    /**
     * @var string
     */
    private $updateType;

    /**
     * @var integer
     */
    private $updateCount;


    /**
     * Set updateType
     *
     * @param string $updateType
     *
     * @return FgRmRoleContact
     */
    public function setUpdateType($updateType)
    {
        $this->updateType = $updateType;

        return $this;

    }


    /**
     * Get updateType
     *
     * @return string
     */
    public function getUpdateType()
    {
        return $this->updateType;

    }


    /**
     * Set updateCount
     *
     * @param integer $updateCount
     *
     * @return FgRmRoleContact
     */
    public function setUpdateCount($updateCount)
    {
        $this->updateCount = $updateCount;

        return $this;

    }


    /**
     * Get updateCount
     *
     * @return integer
     */
    public function getUpdateCount()
    {
        return $this->updateCount;

    }


    /**
     *
     * @var \DateTime
     */
    private $updateTime;

    /**
     * Set updateTime
     *
     * @param \DateTime $updateTime
     *
     * @return FgRmRoleContact
     */
    public function setUpdateTime($updateTime)
    {
        $this->updateTime = $updateTime;

        return $this;

    }


    /**
     * Get updateTime
     *
     * @return \DateTime
     */
    public function getUpdateTime()
    {
        return $this->updateTime;

    }


    /**
     * @var boolean
     */
    private $isRemoved;


    /**
     * Set isRemoved
     *
     * @param boolean $isRemoved
     * @return FgRmRoleContact
     */
    public function setIsRemoved($isRemoved)
    {
        $this->isRemoved = $isRemoved;
    
        return $this;
    }

    /**
     * Get isRemoved
     *
     * @return boolean 
     */
    public function getIsRemoved()
    {
        return $this->isRemoved;
    }
}