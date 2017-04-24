<?php

namespace Admin\UtilityBundle\Entity;

/**
 * FgVoucherLot
 */
class FgVoucherLot
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $clubId;

    /**
     * @var \DateTime
     */
    private $validFrom;

    /**
     * @var \DateTime
     */
    private $validTo;

    /**
     * @var \DateTime
     */
    private $createdDate;

    /**
     * @var \DateTime
     */
    private $updatedDate;

    /**
     * @var \Admin\UtilityBundle\Entity\FgCmContact
     */
    private $updatedBy;

    /**
     * @var \Admin\UtilityBundle\Entity\FgCmContact
     */
    private $createdBy;


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
     * Set clubId
     *
     * @param integer $clubId
     *
     * @return FgVoucherLot
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
     * Set validFrom
     *
     * @param \DateTime $validFrom
     *
     * @return FgVoucherLot
     */
    public function setValidFrom($validFrom)
    {
        $this->validFrom = $validFrom;

        return $this;
    }

    /**
     * Get validFrom
     *
     * @return \DateTime
     */
    public function getValidFrom()
    {
        return $this->validFrom;
    }

    /**
     * Set validTo
     *
     * @param \DateTime $validTo
     *
     * @return FgVoucherLot
     */
    public function setValidTo($validTo)
    {
        $this->validTo = $validTo;

        return $this;
    }

    /**
     * Get validTo
     *
     * @return \DateTime
     */
    public function getValidTo()
    {
        return $this->validTo;
    }

    /**
     * Set createdDate
     *
     * @param \DateTime $createdDate
     *
     * @return FgVoucherLot
     */
    public function setCreatedDate($createdDate)
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    /**
     * Get createdDate
     *
     * @return \DateTime
     */
    public function getCreatedDate()
    {
        return $this->createdDate;
    }

    /**
     * Set updatedDate
     *
     * @param \DateTime $updatedDate
     *
     * @return FgVoucherLot
     */
    public function setUpdatedDate($updatedDate)
    {
        $this->updatedDate = $updatedDate;

        return $this;
    }

    /**
     * Get updatedDate
     *
     * @return \DateTime
     */
    public function getUpdatedDate()
    {
        return $this->updatedDate;
    }

    /**
     * Set updatedBy
     *
     * @param \Admin\UtilityBundle\Entity\FgCmContact $updatedBy
     *
     * @return FgVoucherLot
     */
    public function setUpdatedBy(\Admin\UtilityBundle\Entity\FgCmContact $updatedBy = null)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Get updatedBy
     *
     * @return \Admin\UtilityBundle\Entity\FgCmContact
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * Set createdBy
     *
     * @param \Admin\UtilityBundle\Entity\FgCmContact $createdBy
     *
     * @return FgVoucherLot
     */
    public function setCreatedBy(\Admin\UtilityBundle\Entity\FgCmContact $createdBy = null)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \Admin\UtilityBundle\Entity\FgCmContact
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }
    /**
     * @var \Admin\UtilityBundle\Entity\FgClub
     */
    private $club;


    /**
     * Set club
     *
     * @param \Admin\UtilityBundle\Entity\FgClub $club
     *
     * @return FgVoucherLot
     */
    public function setClub(\Admin\UtilityBundle\Entity\FgClub $club = null)
    {
        $this->club = $club;

        return $this;
    }

    /**
     * Get club
     *
     * @return \Admin\UtilityBundle\Entity\FgClub
     */
    public function getClub()
    {
        return $this->club;
    }
}
