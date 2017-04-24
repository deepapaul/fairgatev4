<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgSmPaymentplans
 */
class FgSmPaymentplans
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $date;

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
     * @var \Common\UtilityBundle\Entity\FgSmBookings
     */
    private $booking;


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
     * Set date
     *
     * @param \DateTime $date
     * @return FgSmPaymentplans
     */
    public function setDate($date)
    {
        $this->date = $date;
    
        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set amount
     *
     * @param string $amount
     * @return FgSmPaymentplans
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
     * @return FgSmPaymentplans
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
     * @return FgSmPaymentplans
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
     * Set booking
     *
     * @param \Common\UtilityBundle\Entity\FgSmBookings $booking
     * @return FgSmPaymentplans
     */
    public function setBooking(\Common\UtilityBundle\Entity\FgSmBookings $booking = null)
    {
        $this->booking = $booking;
    
        return $this;
    }

    /**
     * Get booking
     *
     * @return \Common\UtilityBundle\Entity\FgSmBookings 
     */
    public function getBooking()
    {
        return $this->booking;
    }
}
