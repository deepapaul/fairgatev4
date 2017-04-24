<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCmsForms
 */
class FgCmsForms
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
    private $formType;

    /**
     * @var string
     */
    private $formStage;

    /**
     * @var string
     */
    private $contactFormType;

    /**
     * @var string
     */
    private $confirmationEmailSender;

    /**
     * @var string
     */
    private $confirmationEmailSubject;

    /**
     * @var string
     */
    private $confirmationEmailContent;

    /**
     * @var string
     */
    private $notificationEmailRecipients;

    /**
     * @var string
     */
    private $acceptanceEmailSender;

    /**
     * @var string
     */
    private $acceptanceEmailSubject;

    /**
     * @var string
     */
    private $acceptanceEmailContent;

    /**
     * @var boolean
     */
    private $isAcceptanceEmailActive;

    /**
     * @var string
     */
    private $dismissalEmailSender;

    /**
     * @var string
     */
    private $dismissalEmailSubject;

    /**
     * @var string
     */
    private $dismissalEmailContent;

    /**
     * @var boolean
     */
    private $isDismissalEmailActive;

    /**
     * @var string
     */
    private $completionPromptSuccessMessage;

    /**
     * @var boolean
     */
    private $isActive;

    /**
     * @var boolean
     */
    private $isDeleted;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var integer
     */
    private $createdBy;

    /**
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @var integer
     */
    private $updatedBy;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;


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
     * @return FgCmsForms
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
     * Set formType
     *
     * @param string $formType
     * @return FgCmsForms
     */
    public function setFormType($formType)
    {
        $this->formType = $formType;
    
        return $this;
    }

    /**
     * Get formType
     *
     * @return string 
     */
    public function getFormType()
    {
        return $this->formType;
    }

    /**
     * Set formStage
     *
     * @param string $formStage
     * @return FgCmsForms
     */
    public function setFormStage($formStage)
    {
        $this->formStage = $formStage;
    
        return $this;
    }

    /**
     * Get formStage
     *
     * @return string 
     */
    public function getFormStage()
    {
        return $this->formStage;
    }

    /**
     * Set contactFormType
     *
     * @param string $contactFormType
     * @return FgCmsForms
     */
    public function setContactFormType($contactFormType)
    {
        $this->contactFormType = $contactFormType;
    
        return $this;
    }

    /**
     * Get contactFormType
     *
     * @return string 
     */
    public function getContactFormType()
    {
        return $this->contactFormType;
    }

    /**
     * Set confirmationEmailSender
     *
     * @param string $confirmationEmailSender
     * @return FgCmsForms
     */
    public function setConfirmationEmailSender($confirmationEmailSender)
    {
        $this->confirmationEmailSender = $confirmationEmailSender;
    
        return $this;
    }

    /**
     * Get confirmationEmailSender
     *
     * @return string 
     */
    public function getConfirmationEmailSender()
    {
        return $this->confirmationEmailSender;
    }

    /**
     * Set confirmationEmailSubject
     *
     * @param string $confirmationEmailSubject
     * @return FgCmsForms
     */
    public function setConfirmationEmailSubject($confirmationEmailSubject)
    {
        $this->confirmationEmailSubject = $confirmationEmailSubject;
    
        return $this;
    }

    /**
     * Get confirmationEmailSubject
     *
     * @return string 
     */
    public function getConfirmationEmailSubject()
    {
        return $this->confirmationEmailSubject;
    }

    /**
     * Set confirmationEmailContent
     *
     * @param string $confirmationEmailContent
     * @return FgCmsForms
     */
    public function setConfirmationEmailContent($confirmationEmailContent)
    {
        $this->confirmationEmailContent = $confirmationEmailContent;
    
        return $this;
    }

    /**
     * Get confirmationEmailContent
     *
     * @return string 
     */
    public function getConfirmationEmailContent()
    {
        return $this->confirmationEmailContent;
    }

    /**
     * Set notificationEmailRecipients
     *
     * @param string $notificationEmailRecipients
     * @return FgCmsForms
     */
    public function setNotificationEmailRecipients($notificationEmailRecipients)
    {
        $this->notificationEmailRecipients = $notificationEmailRecipients;
    
        return $this;
    }

    /**
     * Get notificationEmailRecipients
     *
     * @return string 
     */
    public function getNotificationEmailRecipients()
    {
        return $this->notificationEmailRecipients;
    }

    /**
     * Set acceptanceEmailSender
     *
     * @param string $acceptanceEmailSender
     * @return FgCmsForms
     */
    public function setAcceptanceEmailSender($acceptanceEmailSender)
    {
        $this->acceptanceEmailSender = $acceptanceEmailSender;
    
        return $this;
    }

    /**
     * Get acceptanceEmailSender
     *
     * @return string 
     */
    public function getAcceptanceEmailSender()
    {
        return $this->acceptanceEmailSender;
    }

    /**
     * Set acceptanceEmailSubject
     *
     * @param string $acceptanceEmailSubject
     * @return FgCmsForms
     */
    public function setAcceptanceEmailSubject($acceptanceEmailSubject)
    {
        $this->acceptanceEmailSubject = $acceptanceEmailSubject;
    
        return $this;
    }

    /**
     * Get acceptanceEmailSubject
     *
     * @return string 
     */
    public function getAcceptanceEmailSubject()
    {
        return $this->acceptanceEmailSubject;
    }

    /**
     * Set acceptanceEmailContent
     *
     * @param string $acceptanceEmailContent
     * @return FgCmsForms
     */
    public function setAcceptanceEmailContent($acceptanceEmailContent)
    {
        $this->acceptanceEmailContent = $acceptanceEmailContent;
    
        return $this;
    }

    /**
     * Get acceptanceEmailContent
     *
     * @return string 
     */
    public function getAcceptanceEmailContent()
    {
        return $this->acceptanceEmailContent;
    }

    /**
     * Set isAcceptanceEmailActive
     *
     * @param boolean $isAcceptanceEmailActive
     * @return FgCmsForms
     */
    public function setIsAcceptanceEmailActive($isAcceptanceEmailActive)
    {
        $this->isAcceptanceEmailActive = $isAcceptanceEmailActive;
    
        return $this;
    }

    /**
     * Get isAcceptanceEmailActive
     *
     * @return boolean 
     */
    public function getIsAcceptanceEmailActive()
    {
        return $this->isAcceptanceEmailActive;
    }

    /**
     * Set dismissalEmailSender
     *
     * @param string $dismissalEmailSender
     * @return FgCmsForms
     */
    public function setDismissalEmailSender($dismissalEmailSender)
    {
        $this->dismissalEmailSender = $dismissalEmailSender;
    
        return $this;
    }

    /**
     * Get dismissalEmailSender
     *
     * @return string 
     */
    public function getDismissalEmailSender()
    {
        return $this->dismissalEmailSender;
    }

    /**
     * Set dismissalEmailSubject
     *
     * @param string $dismissalEmailSubject
     * @return FgCmsForms
     */
    public function setDismissalEmailSubject($dismissalEmailSubject)
    {
        $this->dismissalEmailSubject = $dismissalEmailSubject;
    
        return $this;
    }

    /**
     * Get dismissalEmailSubject
     *
     * @return string 
     */
    public function getDismissalEmailSubject()
    {
        return $this->dismissalEmailSubject;
    }

    /**
     * Set dismissalEmailContent
     *
     * @param string $dismissalEmailContent
     * @return FgCmsForms
     */
    public function setDismissalEmailContent($dismissalEmailContent)
    {
        $this->dismissalEmailContent = $dismissalEmailContent;
    
        return $this;
    }

    /**
     * Get dismissalEmailContent
     *
     * @return string 
     */
    public function getDismissalEmailContent()
    {
        return $this->dismissalEmailContent;
    }

    /**
     * Set isDismissalEmailActive
     *
     * @param boolean $isDismissalEmailActive
     * @return FgCmsForms
     */
    public function setIsDismissalEmailActive($isDismissalEmailActive)
    {
        $this->isDismissalEmailActive = $isDismissalEmailActive;
    
        return $this;
    }

    /**
     * Get isDismissalMailActive
     *
     * @return boolean 
     */
    public function getIsDismissalEmailActive()
    {
        return $this->isDismissalEmailActive;
    }

    /**
     * Set completionPromptSuccessMessage
     *
     * @param string $completionPromptSuccessMessage
     * @return FgCmsForms
     */
    public function setCompletionPromptSuccessMessage($completionPromptSuccessMessage)
    {
        $this->completionPromptSuccessMessage = $completionPromptSuccessMessage;
    
        return $this;
    }

    /**
     * Get completionPromptSuccessMessage
     *
     * @return string 
     */
    public function getCompletionPromptSuccessMessage()
    {
        return $this->completionPromptSuccessMessage;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return FgCmsForms
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    
        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean 
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     * @return FgCmsForms
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;
    
        return $this;
    }

    /**
     * Get isDeleted
     *
     * @return boolean 
     */
    public function getIsDeleted()
    {
        return $this->isDeleted;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return FgCmsForms
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
     * Set createdBy
     *
     * @param integer $createdBy
     * @return FgCmsForms
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;
    
        return $this;
    }

    /**
     * Get createdBy
     *
     * @return integer 
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return FgCmsForms
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    
        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime 
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set updatedBy
     *
     * @param integer $updatedBy
     * @return FgCmsForms
     */
    public function setUpdatedBy($updatedBy)
    {
        $this->updatedBy = $updatedBy;
    
        return $this;
    }

    /**
     * Get updatedBy
     *
     * @return integer 
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     * @return FgCmsForms
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
}
