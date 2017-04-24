<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgSmServices
 */
class FgSmServices
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $price;

    /**
     * @var string
     */
    private $serviceType;

    /**
     * @var string
     */
    private $paymentPlan;

    /**
     * @var integer
     */
    private $repetitionMonths;

    /**
     * @var integer
     */
    private $sortOrder;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;

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
     * Set title
     *
     * @param string $title
     *
     * @return FgSmServices
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return FgSmServices
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
     * Set price
     *
     * @param string $price
     *
     * @return FgSmServices
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set serviceType
     *
     * @param string $serviceType
     *
     * @return FgSmServices
     */
    public function setServiceType($serviceType)
    {
        $this->serviceType = $serviceType;

        return $this;
    }

    /**
     * Get serviceType
     *
     * @return string
     */
    public function getServiceType()
    {
        return $this->serviceType;
    }

    /**
     * Set paymentPlan
     *
     * @param string $paymentPlan
     *
     * @return FgSmServices
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
     * Set repetitionMonths
     *
     * @param integer $repetitionMonths
     *
     * @return FgSmServices
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
     * Set sortOrder
     *
     * @param integer $sortOrder
     *
     * @return FgSmServices
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
     * @return FgSmServices
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
     * Set category
     *
     * @param \Common\UtilityBundle\Entity\FgSmCategory $category
     *
     * @return FgSmServices
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

