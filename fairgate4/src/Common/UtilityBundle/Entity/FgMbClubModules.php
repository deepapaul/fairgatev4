<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgMbClubModules
 */
class FgMbClubModules
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $isCostOnetime;

    /**
     * @var float
     */
    private $costOnetime;

    /**
     * @var boolean
     */
    private $isCostYearly;

    /**
     * @var float
     */
    private $costYearly;

    /**
     * @var float
     */
    private $invoiceAmount;

    /**
     * @var \DateTime
     */
    private $signedOn;

    /**
     * @var boolean
     */
    private $isModuleActive;

    /**
     * @var string
     */
    private $backendTerms;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $signedBy;

    /**
     * @var \Common\UtilityBundle\Entity\FgMbModule
     */
    private $module;


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
     * Set isCostOnetime
     *
     * @param boolean $isCostOnetime
     *
     * @return FgMbClubModules
     */
    public function setIsCostOnetime($isCostOnetime)
    {
        $this->isCostOnetime = $isCostOnetime;

        return $this;
    }

    /**
     * Get isCostOnetime
     *
     * @return boolean
     */
    public function getIsCostOnetime()
    {
        return $this->isCostOnetime;
    }

    /**
     * Set costOnetime
     *
     * @param float $costOnetime
     *
     * @return FgMbClubModules
     */
    public function setCostOnetime($costOnetime)
    {
        $this->costOnetime = $costOnetime;

        return $this;
    }

    /**
     * Get costOnetime
     *
     * @return float
     */
    public function getCostOnetime()
    {
        return $this->costOnetime;
    }

    /**
     * Set isCostYearly
     *
     * @param boolean $isCostYearly
     *
     * @return FgMbClubModules
     */
    public function setIsCostYearly($isCostYearly)
    {
        $this->isCostYearly = $isCostYearly;

        return $this;
    }

    /**
     * Get isCostYearly
     *
     * @return boolean
     */
    public function getIsCostYearly()
    {
        return $this->isCostYearly;
    }

    /**
     * Set costYearly
     *
     * @param float $costYearly
     *
     * @return FgMbClubModules
     */
    public function setCostYearly($costYearly)
    {
        $this->costYearly = $costYearly;

        return $this;
    }

    /**
     * Get costYearly
     *
     * @return float
     */
    public function getCostYearly()
    {
        return $this->costYearly;
    }

    /**
     * Set invoiceAmount
     *
     * @param float $invoiceAmount
     *
     * @return FgMbClubModules
     */
    public function setInvoiceAmount($invoiceAmount)
    {
        $this->invoiceAmount = $invoiceAmount;

        return $this;
    }

    /**
     * Get invoiceAmount
     *
     * @return float
     */
    public function getInvoiceAmount()
    {
        return $this->invoiceAmount;
    }

    /**
     * Set signedOn
     *
     * @param \DateTime $signedOn
     *
     * @return FgMbClubModules
     */
    public function setSignedOn($signedOn)
    {
        $this->signedOn = $signedOn;

        return $this;
    }

    /**
     * Get signedOn
     *
     * @return \DateTime
     */
    public function getSignedOn()
    {
        return $this->signedOn;
    }

    /**
     * Set isModuleActive
     *
     * @param boolean $isModuleActive
     *
     * @return FgMbClubModules
     */
    public function setIsModuleActive($isModuleActive)
    {
        $this->isModuleActive = $isModuleActive;

        return $this;
    }

    /**
     * Get isModuleActive
     *
     * @return boolean
     */
    public function getIsModuleActive()
    {
        return $this->isModuleActive;
    }

    /**
     * Set backendTerms
     *
     * @param string $backendTerms
     *
     * @return FgMbClubModules
     */
    public function setBackendTerms($backendTerms)
    {
        $this->backendTerms = $backendTerms;

        return $this;
    }

    /**
     * Get backendTerms
     *
     * @return string
     */
    public function getBackendTerms()
    {
        return $this->backendTerms;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     *
     * @return FgMbClubModules
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
     * Set signedBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $signedBy
     *
     * @return FgMbClubModules
     */
    public function setSignedBy(\Common\UtilityBundle\Entity\FgCmContact $signedBy = null)
    {
        $this->signedBy = $signedBy;

        return $this;
    }

    /**
     * Get signedBy
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact
     */
    public function getSignedBy()
    {
        return $this->signedBy;
    }

    /**
     * Set module
     *
     * @param \Common\UtilityBundle\Entity\FgMbModule $module
     *
     * @return FgMbClubModules
     */
    public function setModule(\Common\UtilityBundle\Entity\FgMbModule $module = null)
    {
        $this->module = $module;

        return $this;
    }

    /**
     * Get module
     *
     * @return \Common\UtilityBundle\Entity\FgMbModule
     */
    public function getModule()
    {
        return $this->module;
    }
}

