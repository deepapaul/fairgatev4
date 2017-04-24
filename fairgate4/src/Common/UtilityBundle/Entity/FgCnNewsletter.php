<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCnNewsletter
 */
class FgCnNewsletter
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $senderName;

    /**
     * @var string
     */
    private $senderEmail;

    /**
     * @var string
     */
    private $salutationType;

    /**
     * @var string
     */
    private $salutation;

    /**
     * @var boolean
     */
    private $isHideTableContents;

    /**
     * @var string
     */
    private $newsletterType;

    /**
     * @var string
     */
    private $emailContent;

    /**
     * @var string
     */
    private $sendMode;

    /**
     * @var \DateTime
     */
    private $sendDate;

    /**
     * @var boolean
     */
    private $isDisplayInArchive;

    /**
     * @var string
     */
    private $publishType;

    /**
     * @var \DateTime
     */
    private $lastUpdated;

    /**
     * @var boolean
     */
    private $step;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $languageSelection;

    /**
     * @var integer
     */
    private $lastSpoolContactId;

    /**
     * @var integer
     */
    private $lastContactId;

    /**
     * @var integer
     */
    private $lastSpoolAdminReceiverId;

    /**
     * @var integer
     */
    private $recepientCount;

    /**
     * @var boolean
     */
    private $isCron;

    /**
     * @var string
     */
    private $isSubscriberSelection;

    /**
     * @var string
     */
    private $receiverType;

    /**
     * @var string
     */
    private $newsletterContent;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var boolean
     */
    private $includeFormerMembers;

    /**
     * @var boolean
     */
    private $isRecepientUpdated;

    /**
     * @var boolean
     */
    private $resentStatus;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $updatedBy;

    /**
     * @var \Common\UtilityBundle\Entity\FgCnNewsletterTemplate
     */
    private $template;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $createdBy;

    /**
     * @var \Common\UtilityBundle\Entity\FgCnRecepients
     */
    private $recepientList;


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
     * Set subject
     *
     * @param string $subject
     * @return FgCnNewsletter
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    
        return $this;
    }

    /**
     * Get subject
     *
     * @return string 
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set senderName
     *
     * @param string $senderName
     * @return FgCnNewsletter
     */
    public function setSenderName($senderName)
    {
        $this->senderName = $senderName;
    
        return $this;
    }

    /**
     * Get senderName
     *
     * @return string 
     */
    public function getSenderName()
    {
        return $this->senderName;
    }

    /**
     * Set senderEmail
     *
     * @param string $senderEmail
     * @return FgCnNewsletter
     */
    public function setSenderEmail($senderEmail)
    {
        $this->senderEmail = $senderEmail;
    
        return $this;
    }

    /**
     * Get senderEmail
     *
     * @return string 
     */
    public function getSenderEmail()
    {
        return $this->senderEmail;
    }

    /**
     * Set salutationType
     *
     * @param string $salutationType
     * @return FgCnNewsletter
     */
    public function setSalutationType($salutationType)
    {
        $this->salutationType = $salutationType;
    
        return $this;
    }

    /**
     * Get salutationType
     *
     * @return string 
     */
    public function getSalutationType()
    {
        return $this->salutationType;
    }

    /**
     * Set salutation
     *
     * @param string $salutation
     * @return FgCnNewsletter
     */
    public function setSalutation($salutation)
    {
        $this->salutation = $salutation;
    
        return $this;
    }

    /**
     * Get salutation
     *
     * @return string 
     */
    public function getSalutation()
    {
        return $this->salutation;
    }

    /**
     * Set isHideTableContents
     *
     * @param boolean $isHideTableContents
     * @return FgCnNewsletter
     */
    public function setIsHideTableContents($isHideTableContents)
    {
        $this->isHideTableContents = $isHideTableContents;
    
        return $this;
    }

    /**
     * Get isHideTableContents
     *
     * @return boolean 
     */
    public function getIsHideTableContents()
    {
        return $this->isHideTableContents;
    }

    /**
     * Set newsletterType
     *
     * @param string $newsletterType
     * @return FgCnNewsletter
     */
    public function setNewsletterType($newsletterType)
    {
        $this->newsletterType = $newsletterType;
    
        return $this;
    }

    /**
     * Get newsletterType
     *
     * @return string 
     */
    public function getNewsletterType()
    {
        return $this->newsletterType;
    }

    /**
     * Set emailContent
     *
     * @param string $emailContent
     * @return FgCnNewsletter
     */
    public function setEmailContent($emailContent)
    {
        $this->emailContent = $emailContent;
    
        return $this;
    }

    /**
     * Get emailContent
     *
     * @return string 
     */
    public function getEmailContent()
    {
        return $this->emailContent;
    }

    /**
     * Set sendMode
     *
     * @param string $sendMode
     * @return FgCnNewsletter
     */
    public function setSendMode($sendMode)
    {
        $this->sendMode = $sendMode;
    
        return $this;
    }

    /**
     * Get sendMode
     *
     * @return string 
     */
    public function getSendMode()
    {
        return $this->sendMode;
    }

    /**
     * Set sendDate
     *
     * @param \DateTime $sendDate
     * @return FgCnNewsletter
     */
    public function setSendDate($sendDate)
    {
        $this->sendDate = $sendDate;
    
        return $this;
    }

    /**
     * Get sendDate
     *
     * @return \DateTime 
     */
    public function getSendDate()
    {
        return $this->sendDate;
    }

    /**
     * Set isDisplayInArchive
     *
     * @param boolean $isDisplayInArchive
     * @return FgCnNewsletter
     */
    public function setIsDisplayInArchive($isDisplayInArchive)
    {
        $this->isDisplayInArchive = $isDisplayInArchive;
    
        return $this;
    }

    /**
     * Get isDisplayInArchive
     *
     * @return boolean 
     */
    public function getIsDisplayInArchive()
    {
        return $this->isDisplayInArchive;
    }

    /**
     * Set publishType
     *
     * @param string $publishType
     * @return FgCnNewsletter
     */
    public function setPublishType($publishType)
    {
        $this->publishType = $publishType;
    
        return $this;
    }

    /**
     * Get publishType
     *
     * @return string 
     */
    public function getPublishType()
    {
        return $this->publishType;
    }

    /**
     * Set lastUpdated
     *
     * @param \DateTime $lastUpdated
     * @return FgCnNewsletter
     */
    public function setLastUpdated($lastUpdated)
    {
        $this->lastUpdated = $lastUpdated;
    
        return $this;
    }

    /**
     * Get lastUpdated
     *
     * @return \DateTime 
     */
    public function getLastUpdated()
    {
        return $this->lastUpdated;
    }

    /**
     * Set step
     *
     * @param boolean $step
     * @return FgCnNewsletter
     */
    public function setStep($step)
    {
        $this->step = $step;
    
        return $this;
    }

    /**
     * Get step
     *
     * @return boolean 
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return FgCnNewsletter
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
     * Set languageSelection
     *
     * @param string $languageSelection
     * @return FgCnNewsletter
     */
    public function setLanguageSelection($languageSelection)
    {
        $this->languageSelection = $languageSelection;
    
        return $this;
    }

    /**
     * Get languageSelection
     *
     * @return string 
     */
    public function getLanguageSelection()
    {
        return $this->languageSelection;
    }

    /**
     * Set lastSpoolContactId
     *
     * @param integer $lastSpoolContactId
     * @return FgCnNewsletter
     */
    public function setLastSpoolContactId($lastSpoolContactId)
    {
        $this->lastSpoolContactId = $lastSpoolContactId;
    
        return $this;
    }

    /**
     * Get lastSpoolContactId
     *
     * @return integer 
     */
    public function getLastSpoolContactId()
    {
        return $this->lastSpoolContactId;
    }

    /**
     * Set lastContactId
     *
     * @param integer $lastContactId
     * @return FgCnNewsletter
     */
    public function setLastContactId($lastContactId)
    {
        $this->lastContactId = $lastContactId;
    
        return $this;
    }

    /**
     * Get lastContactId
     *
     * @return integer 
     */
    public function getLastContactId()
    {
        return $this->lastContactId;
    }

    /**
     * Set lastSpoolAdminReceiverId
     *
     * @param integer $lastSpoolAdminReceiverId
     * @return FgCnNewsletter
     */
    public function setLastSpoolAdminReceiverId($lastSpoolAdminReceiverId)
    {
        $this->lastSpoolAdminReceiverId = $lastSpoolAdminReceiverId;
    
        return $this;
    }

    /**
     * Get lastSpoolAdminReceiverId
     *
     * @return integer 
     */
    public function getLastSpoolAdminReceiverId()
    {
        return $this->lastSpoolAdminReceiverId;
    }

    /**
     * Set recepientCount
     *
     * @param integer $recepientCount
     * @return FgCnNewsletter
     */
    public function setRecepientCount($recepientCount)
    {
        $this->recepientCount = $recepientCount;
    
        return $this;
    }

    /**
     * Get recepientCount
     *
     * @return integer 
     */
    public function getRecepientCount()
    {
        return $this->recepientCount;
    }

    /**
     * Set isCron
     *
     * @param boolean $isCron
     * @return FgCnNewsletter
     */
    public function setIsCron($isCron)
    {
        $this->isCron = $isCron;
    
        return $this;
    }

    /**
     * Get isCron
     *
     * @return boolean 
     */
    public function getIsCron()
    {
        return $this->isCron;
    }

    /**
     * Set isSubscriberSelection
     *
     * @param string $isSubscriberSelection
     * @return FgCnNewsletter
     */
    public function setIsSubscriberSelection($isSubscriberSelection)
    {
        $this->isSubscriberSelection = $isSubscriberSelection;
    
        return $this;
    }

    /**
     * Get isSubscriberSelection
     *
     * @return string 
     */
    public function getIsSubscriberSelection()
    {
        return $this->isSubscriberSelection;
    }

    /**
     * Set receiverType
     *
     * @param string $receiverType
     * @return FgCnNewsletter
     */
    public function setReceiverType($receiverType)
    {
        $this->receiverType = $receiverType;
    
        return $this;
    }

    /**
     * Get receiverType
     *
     * @return string 
     */
    public function getReceiverType()
    {
        return $this->receiverType;
    }

    /**
     * Set newsletterContent
     *
     * @param string $newsletterContent
     * @return FgCnNewsletter
     */
    public function setNewsletterContent($newsletterContent)
    {
        $this->newsletterContent = $newsletterContent;
    
        return $this;
    }

    /**
     * Get newsletterContent
     *
     * @return string 
     */
    public function getNewsletterContent()
    {
        return $this->newsletterContent;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return FgCnNewsletter
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
     * Set includeFormerMembers
     *
     * @param boolean $includeFormerMembers
     * @return FgCnNewsletter
     */
    public function setIncludeFormerMembers($includeFormerMembers)
    {
        $this->includeFormerMembers = $includeFormerMembers;
    
        return $this;
    }

    /**
     * Get includeFormerMembers
     *
     * @return boolean 
     */
    public function getIncludeFormerMembers()
    {
        return $this->includeFormerMembers;
    }

    /**
     * Set isRecepientUpdated
     *
     * @param boolean $isRecepientUpdated
     * @return FgCnNewsletter
     */
    public function setIsRecepientUpdated($isRecepientUpdated)
    {
        $this->isRecepientUpdated = $isRecepientUpdated;
    
        return $this;
    }

    /**
     * Get isRecepientUpdated
     *
     * @return boolean 
     */
    public function getIsRecepientUpdated()
    {
        return $this->isRecepientUpdated;
    }

    /**
     * Set resentStatus
     *
     * @param boolean $resentStatus
     * @return FgCnNewsletter
     */
    public function setResentStatus($resentStatus)
    {
        $this->resentStatus = $resentStatus;
    
        return $this;
    }

    /**
     * Get resentStatus
     *
     * @return boolean 
     */
    public function getResentStatus()
    {
        return $this->resentStatus;
    }

    /**
     * Set updatedBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $updatedBy
     * @return FgCnNewsletter
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
     * Set template
     *
     * @param \Common\UtilityBundle\Entity\FgCnNewsletterTemplate $template
     * @return FgCnNewsletter
     */
    public function setTemplate(\Common\UtilityBundle\Entity\FgCnNewsletterTemplate $template = null)
    {
        $this->template = $template;
    
        return $this;
    }

    /**
     * Get template
     *
     * @return \Common\UtilityBundle\Entity\FgCnNewsletterTemplate 
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     * @return FgCnNewsletter
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
     * Set createdBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $createdBy
     * @return FgCnNewsletter
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
     * Set recepientList
     *
     * @param \Common\UtilityBundle\Entity\FgCnRecepients $recepientList
     * @return FgCnNewsletter
     */
    public function setRecepientList(\Common\UtilityBundle\Entity\FgCnRecepients $recepientList = null)
    {
        $this->recepientList = $recepientList;
    
        return $this;
    }

    /**
     * Get recepientList
     *
     * @return \Common\UtilityBundle\Entity\FgCnRecepients 
     */
    public function getRecepientList()
    {
        return $this->recepientList;
    }
    /**
     * @var \DateTime
     */
    private $templateUpdated;


    /**
     * Set templateUpdated
     *
     * @param \DateTime $templateUpdated
     * @return FgCnNewsletter
     */
    public function setTemplateUpdated($templateUpdated)
    {
        $this->templateUpdated = $templateUpdated;
    
        return $this;
    }

    /**
     * Get templateUpdated
     *
     * @return \DateTime 
     */
    public function getTemplateUpdated()
    {
        return $this->templateUpdated;
    }
}