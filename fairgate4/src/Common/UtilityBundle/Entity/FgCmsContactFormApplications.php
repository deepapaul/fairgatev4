<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCmsContactFormApplications
 */
class FgCmsContactFormApplications
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $contactName;

    /**
     * @var string
     */
    private $formData;

    /**
     * @var string
     */
    private $status;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $decisionDate;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmsForms
     */
    private $form;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $decidedBy;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $clubContact;


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
     * Set contactName
     *
     * @param string $contactName
     * @return FgCmsContactFormApplications
     */
    public function setContactName($contactName)
    {
        $this->contactName = $contactName;
    
        return $this;
    }

    /**
     * Get contactName
     *
     * @return string 
     */
    public function getContactName()
    {
        return $this->contactName;
    }

    /**
     * Set formData
     *
     * @param string $formData
     * @return FgCmsContactFormApplications
     */
    public function setFormData($formData)
    {
        $this->formData = $formData;
    
        return $this;
    }

    /**
     * Get formData
     *
     * @return string 
     */
    public function getFormData()
    {
        return $this->formData;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return FgCmsContactFormApplications
     */
    public function setStatus($status)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return FgCmsContactFormApplications
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
     * Set decisionDate
     *
     * @param \DateTime $decisionDate
     * @return FgCmsContactFormApplications
     */
    public function setDecisionDate($decisionDate)
    {
        $this->decisionDate = $decisionDate;
    
        return $this;
    }

    /**
     * Get decisionDate
     *
     * @return \DateTime 
     */
    public function getDecisionDate()
    {
        return $this->decisionDate;
    }

    /**
     * Set form
     *
     * @param \Common\UtilityBundle\Entity\FgCmsForms $form
     * @return FgCmsContactFormApplications
     */
    public function setForm(\Common\UtilityBundle\Entity\FgCmsForms $form = null)
    {
        $this->form = $form;
    
        return $this;
    }

    /**
     * Get form
     *
     * @return \Common\UtilityBundle\Entity\FgCmsForms 
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Set decidedBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $decidedBy
     * @return FgCmsContactFormApplications
     */
    public function setDecidedBy(\Common\UtilityBundle\Entity\FgCmContact $decidedBy = null)
    {
        $this->decidedBy = $decidedBy;
    
        return $this;
    }

    /**
     * Get decidedBy
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact 
     */
    public function getDecidedBy()
    {
        return $this->decidedBy;
    }

    /**
     * Set clubContact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $clubContact
     * @return FgCmsContactFormApplications
     */
    public function setClubContact(\Common\UtilityBundle\Entity\FgCmContact $clubContact = null)
    {
        $this->clubContact = $clubContact;
    
        return $this;
    }

    /**
     * Get clubContact
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact 
     */
    public function getClubContact()
    {
        return $this->clubContact;
    }
}