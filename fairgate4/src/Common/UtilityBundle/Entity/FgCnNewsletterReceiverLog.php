<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgCnNewsletterReceiverLog
 */
class FgCnNewsletterReceiverLog
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $corresLang;

    /**
     * @var \DateTime
     */
    private $sendDate;

    /**
     * @var string
     */
    private $contactId;

    /**
     * @var string
     */
    private $email;

    /**
     * @var boolean
     */
    private $isSent;

    /**
     * @var \DateTime
     */
    private $openedAt;

    /**
     * @var boolean
     */
    private $isBounced;

    /**
     * @var string
     */
    private $bounceMessage;

    /**
     * @var string
     */
    private $resentEmail;

    /**
     * @var string
     */
    private $salutation;

    /**
     * @var integer
     */
    private $bounceCount;

    /**
     * @var string
     */
    private $emailFieldIds;

    /**
     * @var string
     */
    private $linkedContactIds;

    /**
     * @var boolean
     */
    private $isEmailChanged;

    /**
     * @var string
     */
    private $systemLanguage;

    /**
     * @var \Common\UtilityBundle\Entity\FgCnNewsletter
     */
    private $newsletter;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;

    /**
     * @var \Common\UtilityBundle\Entity\FgCnSubscriber
     */
    private $subscriber;


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
     * Set corresLang
     *
     * @param string $corresLang
     *
     * @return FgCnNewsletterReceiverLog
     */
    public function setCorresLang($corresLang)
    {
        $this->corresLang = $corresLang;

        return $this;
    }

    /**
     * Get corresLang
     *
     * @return string
     */
    public function getCorresLang()
    {
        return $this->corresLang;
    }

    /**
     * Set sendDate
     *
     * @param \DateTime $sendDate
     *
     * @return FgCnNewsletterReceiverLog
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
     * Set contactId
     *
     * @param string $contactId
     *
     * @return FgCnNewsletterReceiverLog
     */
    public function setContactId($contactId)
    {
        $this->contactId = $contactId;

        return $this;
    }

    /**
     * Get contactId
     *
     * @return string
     */
    public function getContactId()
    {
        return $this->contactId;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return FgCnNewsletterReceiverLog
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set isSent
     *
     * @param boolean $isSent
     *
     * @return FgCnNewsletterReceiverLog
     */
    public function setIsSent($isSent)
    {
        $this->isSent = $isSent;

        return $this;
    }

    /**
     * Get isSent
     *
     * @return boolean
     */
    public function getIsSent()
    {
        return $this->isSent;
    }

    /**
     * Set openedAt
     *
     * @param \DateTime $openedAt
     *
     * @return FgCnNewsletterReceiverLog
     */
    public function setOpenedAt($openedAt)
    {
        $this->openedAt = $openedAt;

        return $this;
    }

    /**
     * Get openedAt
     *
     * @return \DateTime
     */
    public function getOpenedAt()
    {
        return $this->openedAt;
    }

    /**
     * Set isBounced
     *
     * @param boolean $isBounced
     *
     * @return FgCnNewsletterReceiverLog
     */
    public function setIsBounced($isBounced)
    {
        $this->isBounced = $isBounced;

        return $this;
    }

    /**
     * Get isBounced
     *
     * @return boolean
     */
    public function getIsBounced()
    {
        return $this->isBounced;
    }

    /**
     * Set bounceMessage
     *
     * @param string $bounceMessage
     *
     * @return FgCnNewsletterReceiverLog
     */
    public function setBounceMessage($bounceMessage)
    {
        $this->bounceMessage = $bounceMessage;

        return $this;
    }

    /**
     * Get bounceMessage
     *
     * @return string
     */
    public function getBounceMessage()
    {
        return $this->bounceMessage;
    }

    /**
     * Set resentEmail
     *
     * @param string $resentEmail
     *
     * @return FgCnNewsletterReceiverLog
     */
    public function setResentEmail($resentEmail)
    {
        $this->resentEmail = $resentEmail;

        return $this;
    }

    /**
     * Get resentEmail
     *
     * @return string
     */
    public function getResentEmail()
    {
        return $this->resentEmail;
    }

    /**
     * Set salutation
     *
     * @param string $salutation
     *
     * @return FgCnNewsletterReceiverLog
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
     * Set bounceCount
     *
     * @param integer $bounceCount
     *
     * @return FgCnNewsletterReceiverLog
     */
    public function setBounceCount($bounceCount)
    {
        $this->bounceCount = $bounceCount;

        return $this;
    }

    /**
     * Get bounceCount
     *
     * @return integer
     */
    public function getBounceCount()
    {
        return $this->bounceCount;
    }

    /**
     * Set emailFieldIds
     *
     * @param string $emailFieldIds
     *
     * @return FgCnNewsletterReceiverLog
     */
    public function setEmailFieldIds($emailFieldIds)
    {
        $this->emailFieldIds = $emailFieldIds;

        return $this;
    }

    /**
     * Get emailFieldIds
     *
     * @return string
     */
    public function getEmailFieldIds()
    {
        return $this->emailFieldIds;
    }

    /**
     * Set linkedContactIds
     *
     * @param string $linkedContactIds
     *
     * @return FgCnNewsletterReceiverLog
     */
    public function setLinkedContactIds($linkedContactIds)
    {
        $this->linkedContactIds = $linkedContactIds;

        return $this;
    }

    /**
     * Get linkedContactIds
     *
     * @return string
     */
    public function getLinkedContactIds()
    {
        return $this->linkedContactIds;
    }

    /**
     * Set isEmailChanged
     *
     * @param boolean $isEmailChanged
     *
     * @return FgCnNewsletterReceiverLog
     */
    public function setIsEmailChanged($isEmailChanged)
    {
        $this->isEmailChanged = $isEmailChanged;

        return $this;
    }

    /**
     * Get isEmailChanged
     *
     * @return boolean
     */
    public function getIsEmailChanged()
    {
        return $this->isEmailChanged;
    }

    /**
     * Set systemLanguage
     *
     * @param string $systemLanguage
     *
     * @return FgCnNewsletterReceiverLog
     */
    public function setSystemLanguage($systemLanguage)
    {
        $this->systemLanguage = $systemLanguage;

        return $this;
    }

    /**
     * Get systemLanguage
     *
     * @return string
     */
    public function getSystemLanguage()
    {
        return $this->systemLanguage;
    }

    /**
     * Set newsletter
     *
     * @param \Common\UtilityBundle\Entity\FgCnNewsletter $newsletter
     *
     * @return FgCnNewsletterReceiverLog
     */
    public function setNewsletter(\Common\UtilityBundle\Entity\FgCnNewsletter $newsletter = null)
    {
        $this->newsletter = $newsletter;

        return $this;
    }

    /**
     * Get newsletter
     *
     * @return \Common\UtilityBundle\Entity\FgCnNewsletter
     */
    public function getNewsletter()
    {
        return $this->newsletter;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     *
     * @return FgCnNewsletterReceiverLog
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
     * Set subscriber
     *
     * @param \Common\UtilityBundle\Entity\FgCnSubscriber $subscriber
     *
     * @return FgCnNewsletterReceiverLog
     */
    public function setSubscriber(\Common\UtilityBundle\Entity\FgCnSubscriber $subscriber = null)
    {
        $this->subscriber = $subscriber;

        return $this;
    }

    /**
     * Get subscriber
     *
     * @return \Common\UtilityBundle\Entity\FgCnSubscriber
     */
    public function getSubscriber()
    {
        return $this->subscriber;
    }
}

