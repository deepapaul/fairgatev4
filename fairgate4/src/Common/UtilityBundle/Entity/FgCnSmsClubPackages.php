<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgCnSmsClubPackages
 */
class FgCnSmsClubPackages
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
     * @var integer
     */
    private $sortOrder;

    /**
     * @var string
     */
    private $price;

    /**
     * @var integer
     */
    private $totalCredits;

    /**
     * @var integer
     */
    private $balanceCredits;

    /**
     * @var string
     */
    private $sponsorType;

    /**
     * @var string
     */
    private $sponsorText;

    /**
     * @var integer
     */
    private $clubId;

    /**
     * @var \DateTime
     */
    private $bookingDate;

    /**
     * @var boolean
     */
    private $isClub;

    /**
     * @var integer
     */
    private $isAll;


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
     * @return FgCnSmsClubPackages
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
     * Set sortOrder
     *
     * @param integer $sortOrder
     *
     * @return FgCnSmsClubPackages
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
     * Set price
     *
     * @param string $price
     *
     * @return FgCnSmsClubPackages
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
     * Set totalCredits
     *
     * @param integer $totalCredits
     *
     * @return FgCnSmsClubPackages
     */
    public function setTotalCredits($totalCredits)
    {
        $this->totalCredits = $totalCredits;

        return $this;
    }

    /**
     * Get totalCredits
     *
     * @return integer
     */
    public function getTotalCredits()
    {
        return $this->totalCredits;
    }

    /**
     * Set balanceCredits
     *
     * @param integer $balanceCredits
     *
     * @return FgCnSmsClubPackages
     */
    public function setBalanceCredits($balanceCredits)
    {
        $this->balanceCredits = $balanceCredits;

        return $this;
    }

    /**
     * Get balanceCredits
     *
     * @return integer
     */
    public function getBalanceCredits()
    {
        return $this->balanceCredits;
    }

    /**
     * Set sponsorType
     *
     * @param string $sponsorType
     *
     * @return FgCnSmsClubPackages
     */
    public function setSponsorType($sponsorType)
    {
        $this->sponsorType = $sponsorType;

        return $this;
    }

    /**
     * Get sponsorType
     *
     * @return string
     */
    public function getSponsorType()
    {
        return $this->sponsorType;
    }

    /**
     * Set sponsorText
     *
     * @param string $sponsorText
     *
     * @return FgCnSmsClubPackages
     */
    public function setSponsorText($sponsorText)
    {
        $this->sponsorText = $sponsorText;

        return $this;
    }

    /**
     * Get sponsorText
     *
     * @return string
     */
    public function getSponsorText()
    {
        return $this->sponsorText;
    }

    /**
     * Set clubId
     *
     * @param integer $clubId
     *
     * @return FgCnSmsClubPackages
     */
    public function setClubId($clubId)
    {
        $this->clubId = $clubId;

        return $this;
    }

    /**
     * Get clubId
     *
     * @return integer
     */
    public function getClubId()
    {
        return $this->clubId;
    }

    /**
     * Set bookingDate
     *
     * @param \DateTime $bookingDate
     *
     * @return FgCnSmsClubPackages
     */
    public function setBookingDate($bookingDate)
    {
        $this->bookingDate = $bookingDate;

        return $this;
    }

    /**
     * Get bookingDate
     *
     * @return \DateTime
     */
    public function getBookingDate()
    {
        return $this->bookingDate;
    }

    /**
     * Set isClub
     *
     * @param boolean $isClub
     *
     * @return FgCnSmsClubPackages
     */
    public function setIsClub($isClub)
    {
        $this->isClub = $isClub;

        return $this;
    }

    /**
     * Get isClub
     *
     * @return boolean
     */
    public function getIsClub()
    {
        return $this->isClub;
    }

    /**
     * Set isAll
     *
     * @param integer $isAll
     *
     * @return FgCnSmsClubPackages
     */
    public function setIsAll($isAll)
    {
        $this->isAll = $isAll;

        return $this;
    }

    /**
     * Get isAll
     *
     * @return integer
     */
    public function getIsAll()
    {
        return $this->isAll;
    }
}

