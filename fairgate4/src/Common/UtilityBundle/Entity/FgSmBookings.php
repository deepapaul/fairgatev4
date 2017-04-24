<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgSmBookings
 */
class FgSmBookings
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $beginDate;

    /**
     * @var \DateTime
     */
    private $endDate;

    /**
     * @var string
     */
    private $paymentPlan;

    /**
     * @var \DateTime
     */
    private $firstPaymentDate;

    /**
     * @var \DateTime
     */
    private $lastPaymentDate;

    /**
     * @var integer
     */
    private $repetitionMonths;

    /**
     * @var string
     */
    private $amount;

    /**
     * @var string
     */
    private $discountType;

    /**
     * @var string
     */
    private $discount;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @var string
     */
    private $timestamp;

    /**
     * @var boolean
     */
    private $isDeleted;

    /**
     * @var integer
     */
    private $isSkipped;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;

    /**
     * @var \Common\UtilityBundle\Entity\FgSmServices
     */
    private $service;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $contact;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $createdBy;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $updatedBy;

    /**
     * @var \Common\UtilityBundle\Entity\FgSmCategory
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
     * Set beginDate
     *
     * @param \DateTime $beginDate
     *
     * @return FgSmBookings
     */
    public function setBeginDate($beginDate)
    {
        $this->beginDate = $beginDate;

        return $this;
    }

    /**
     * Get beginDate
     *
     * @return \DateTime
     */
    public function getBeginDate()
    {
        return $this->beginDate;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     *
     * @return FgSmBookings
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set paymentPlan
     *
     * @param string $paymentPlan
     *
     * @return FgSmBookings
     */
    public function setPaymentPlan($paymentPlan)
    {
        $this->paymentPlan = $paymentPlan;

        return $this;
    }

    /**
     * Get paymentPlan
     *
     * @return string
     */
    public function getPaymentPlan()
    {
        return $this->paymentPlan;
    }

    /**
     * Set firstPaymentDate
     *
     * @param \DateTime $firstPaymentDate
     *
     * @return FgSmBookings
     */
    public function setFirstPaymentDate($firstPaymentDate)
    {
        $this->firstPaymentDate = $firstPaymentDate;

        return $this;
    }

    /**
     * Get firstPaymentDate
     *
     * @return \DateTime
     */
    public function getFirstPaymentDate()
    {
        return $this->firstPaymentDate;
    }

    /**
     * Set lastPaymentDate
     *
     * @param \DateTime $lastPaymentDate
     *
     * @return FgSmBookings
     */
    public function setLastPaymentDate($lastPaymentDate)
    {
        $this->lastPaymentDate = $lastPaymentDate;

        return $this;
    }

    /**
     * Get lastPaymentDate
     *
     * @return \DateTime
     */
    public function getLastPaymentDate()
    {
        return $this->lastPaymentDate;
    }

    /**
     * Set repetitionMonths
     *
     * @param integer $repetitionMonths
     *
     * @return FgSmBookings
     */
    public function setRepetitionMonths($repetitionMonths)
    {
        $this->repetitionMonths = $repetitionMonths;

        return $this;
    }

    /**
     * Get repetitionMonths
     *
     * @return integer
     */
    public function getRepetitionMonths()
    {
        return $this->repetitionMonths;
    }

    /**
     * Set amount
     *
     * @param string $amount
     *
     * @return FgSmBookings
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set discountType
     *
     * @param string $discountType
     *
     * @return FgSmBookings
     */
    public function setDiscountType($discountType)
    {
        $this->discountType = $discountType;

        return $this;
    }

    /**
     * Get discountType
     *
     * @return string
     */
    public function getDiscountType()
    {
        return $this->discountType;
    }

    /**
     * Set discount
     *
     * @param string $discount
     *
     * @return FgSmBookings
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;

        return $this;
    }

    /**
     * Get discount
     *
     * @return string
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return FgSmBookings
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
     *
     * @return FgSmBookings
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
     * Set timestamp
     *
     * @param string $timestamp
     *
     * @return FgSmBookings
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    /**
     * Get timestamp
     *
     * @return string
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return FgSmBookings
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
     * Set isSkipped
     *
     * @param integer $isSkipped
     *
     * @return FgSmBookings
     */
    public function setIsSkipped($isSkipped)
    {
        $this->isSkipped = $isSkipped;

        return $this;
    }

    /**
     * Get isSkipped
     *
     * @return integer
     */
    public function getIsSkipped()
    {
        return $this->isSkipped;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     *
     * @return FgSmBookings
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
     * Set service
     *
     * @param \Common\UtilityBundle\Entity\FgSmServices $service
     *
     * @return FgSmBookings
     */
    public function setService(\Common\UtilityBundle\Entity\FgSmServices $service = null)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * Get service
     *
     * @return \Common\UtilityBundle\Entity\FgSmServices
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Set contact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $contact
     *
     * @return FgSmBookings
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
     * Set createdBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $createdBy
     *
     * @return FgSmBookings
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
     *
     * @return FgSmBookings
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
     * Set category
     *
     * @param \Common\UtilityBundle\Entity\FgSmCategory $category
     *
     * @return FgSmBookings
     */
    public function setCategory(\Common\UtilityBundle\Entity\FgSmCategory $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \Common\UtilityBundle\Entity\FgSmCategory
     */
    public function getCategory()
    {
        return $this->category;
    }
}

