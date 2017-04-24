<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgSmBookingDeposited
 */
class FgSmBookingDeposited
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $contact;

    /**
     * @var \Common\UtilityBundle\Entity\FgRmRole
     */
    private $role;

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
     * Set contact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $contact
     *
     * @return FgSmBookingDeposited
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
     * Set role
     *
     * @param \Common\UtilityBundle\Entity\FgRmRole $role
     *
     * @return FgSmBookingDeposited
     */
    public function setRole(\Common\UtilityBundle\Entity\FgRmRole $role = null)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return \Common\UtilityBundle\Entity\FgRmRole
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set booking
     *
     * @param \Common\UtilityBundle\Entity\FgSmBookings $booking
     *
     * @return FgSmBookingDeposited
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

