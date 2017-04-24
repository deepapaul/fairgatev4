<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgCnRecepients
 */
class FgCnRecepients
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
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @var string
     */
    private $filterData;

    /**
     * @var boolean
     */
    private $isAllActive;

    /**
     * @var integer
     */
    private $contactCount;

    /**
     * @var integer
     */
    private $mandatoryCount;

    /**
     * @var integer
     */
    private $subscriberCount;

    /**
     * @var string
     */
    private $tempId;

    /**
     * @var integer
     */
    private $sortOrder;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;


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
     * @return FgCnRecepients
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
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return FgCnRecepients
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
     * Set filterData
     *
     * @param string $filterData
     *
     * @return FgCnRecepients
     */
    public function setFilterData($filterData)
    {
        $this->filterData = $filterData;

        return $this;
    }

    /**
     * Get filterData
     *
     * @return string
     */
    public function getFilterData()
    {
        return $this->filterData;
    }

    /**
     * Set isAllActive
     *
     * @param boolean $isAllActive
     *
     * @return FgCnRecepients
     */
    public function setIsAllActive($isAllActive)
    {
        $this->isAllActive = $isAllActive;

        return $this;
    }

    /**
     * Get isAllActive
     *
     * @return boolean
     */
    public function getIsAllActive()
    {
        return $this->isAllActive;
    }

    /**
     * Set contactCount
     *
     * @param integer $contactCount
     *
     * @return FgCnRecepients
     */
    public function setContactCount($contactCount)
    {
        $this->contactCount = $contactCount;

        return $this;
    }

    /**
     * Get contactCount
     *
     * @return integer
     */
    public function getContactCount()
    {
        return $this->contactCount;
    }

    /**
     * Set mandatoryCount
     *
     * @param integer $mandatoryCount
     *
     * @return FgCnRecepients
     */
    public function setMandatoryCount($mandatoryCount)
    {
        $this->mandatoryCount = $mandatoryCount;

        return $this;
    }

    /**
     * Get mandatoryCount
     *
     * @return integer
     */
    public function getMandatoryCount()
    {
        return $this->mandatoryCount;
    }

    /**
     * Set subscriberCount
     *
     * @param integer $subscriberCount
     *
     * @return FgCnRecepients
     */
    public function setSubscriberCount($subscriberCount)
    {
        $this->subscriberCount = $subscriberCount;

        return $this;
    }

    /**
     * Get subscriberCount
     *
     * @return integer
     */
    public function getSubscriberCount()
    {
        return $this->subscriberCount;
    }

    /**
     * Set tempId
     *
     * @param string $tempId
     *
     * @return FgCnRecepients
     */
    public function setTempId($tempId)
    {
        $this->tempId = $tempId;

        return $this;
    }

    /**
     * Get tempId
     *
     * @return string
     */
    public function getTempId()
    {
        return $this->tempId;
    }

    /**
     * Set sortOrder
     *
     * @param integer $sortOrder
     *
     * @return FgCnRecepients
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
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     *
     * @return FgCnRecepients
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
}

