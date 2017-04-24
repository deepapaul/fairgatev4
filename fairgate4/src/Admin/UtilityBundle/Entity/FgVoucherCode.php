<?php

namespace Admin\UtilityBundle\Entity;

/**
 * FgVoucherCode
 */
class FgVoucherCode
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $isConsumed;

    /**
     * @var string
     */
    private $voucherCode;

    /**
     * @var \Admin\UtilityBundle\Entity\FgClub
     */
    private $consumedBy;

    /**
     * @var \Admin\UtilityBundle\Entity\FgVoucherLot
     */
    private $vLot;


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
     * Set isConsumed
     *
     * @param boolean $isConsumed
     *
     * @return FgVoucherCode
     */
    public function setIsConsumed($isConsumed)
    {
        $this->isConsumed = $isConsumed;

        return $this;
    }

    /**
     * Get isConsumed
     *
     * @return boolean
     */
    public function getIsConsumed()
    {
        return $this->isConsumed;
    }

    /**
     * Set voucherCode
     *
     * @param string $voucherCode
     *
     * @return FgVoucherCode
     */
    public function setVoucherCode($voucherCode)
    {
        $this->voucherCode = $voucherCode;

        return $this;
    }

    /**
     * Get voucherCode
     *
     * @return string
     */
    public function getVoucherCode()
    {
        return $this->voucherCode;
    }

    /**
     * Set consumedBy
     *
     * @param \Admin\UtilityBundle\Entity\FgClub $consumedBy
     *
     * @return FgVoucherCode
     */
    public function setConsumedBy(\Admin\UtilityBundle\Entity\FgClub $consumedBy = null)
    {
        $this->consumedBy = $consumedBy;

        return $this;
    }

    /**
     * Get consumedBy
     *
     * @return \Admin\UtilityBundle\Entity\FgClub
     */
    public function getConsumedBy()
    {
        return $this->consumedBy;
    }

    /**
     * Set vLot
     *
     * @param \Admin\UtilityBundle\Entity\FgVoucherLot $vLot
     *
     * @return FgVoucherCode
     */
    public function setVLot(\Admin\UtilityBundle\Entity\FgVoucherLot $vLot = null)
    {
        $this->vLot = $vLot;

        return $this;
    }

    /**
     * Get vLot
     *
     * @return \Admin\UtilityBundle\Entity\FgVoucherLot
     */
    public function getVLot()
    {
        return $this->vLot;
    }
}
