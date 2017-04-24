<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCmMutationLog
 */
class FgCmMutationLog
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $confirmedDate;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $contact;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $confirmedBy;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmChangeToconfirm
     */
    private $toconfirm;


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
     * Set confirmedDate
     *
     * @param \DateTime $confirmedDate
     * @return FgCmMutationLog
     */
    public function setConfirmedDate($confirmedDate)
    {
        $this->confirmedDate = $confirmedDate;
    
        return $this;
    }

    /**
     * Get confirmedDate
     *
     * @return \DateTime 
     */
    public function getConfirmedDate()
    {
        return $this->confirmedDate;
    }

    /**
     * Set contact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $contact
     * @return FgCmMutationLog
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
     * Set confirmedBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $confirmedBy
     * @return FgCmMutationLog
     */
    public function setConfirmedBy(\Common\UtilityBundle\Entity\FgCmContact $confirmedBy = null)
    {
        $this->confirmedBy = $confirmedBy;
    
        return $this;
    }

    /**
     * Get confirmedBy
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact 
     */
    public function getConfirmedBy()
    {
        return $this->confirmedBy;
    }

    /**
     * Set toconfirm
     *
     * @param \Common\UtilityBundle\Entity\FgCmChangeToconfirm $toconfirm
     * @return FgCmMutationLog
     */
    public function setToconfirm(\Common\UtilityBundle\Entity\FgCmChangeToconfirm $toconfirm = null)
    {
        $this->toconfirm = $toconfirm;
    
        return $this;
    }

    /**
     * Get toconfirm
     *
     * @return \Common\UtilityBundle\Entity\FgCmChangeToconfirm 
     */
    public function getToconfirm()
    {
        return $this->toconfirm;
    }
}
