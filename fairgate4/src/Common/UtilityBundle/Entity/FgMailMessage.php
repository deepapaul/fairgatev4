<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgMailMessage
 */
class FgMailMessage
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $salutation;

    /**
     * @var boolean
     */
    private $cronInstance;

    /**
     * @var integer
     */
    private $priority;

    /**
     * @var \Common\UtilityBundle\Entity\FgCnNewsletter
     */
    private $newsletter;

    /**
     * @var \Common\UtilityBundle\Entity\FgCnNewsletterReceiverLog
     */
    private $receiverLog;


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
     * Set email
     *
     * @param string $email
     *
     * @return FgMailMessage
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
     * Set salutation
     *
     * @param string $salutation
     *
     * @return FgMailMessage
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
     * Set cronInstance
     *
     * @param boolean $cronInstance
     *
     * @return FgMailMessage
     */
    public function setCronInstance($cronInstance)
    {
        $this->cronInstance = $cronInstance;

        return $this;
    }

    /**
     * Get cronInstance
     *
     * @return boolean
     */
    public function getCronInstance()
    {
        return $this->cronInstance;
    }

    /**
     * Set priority
     *
     * @param integer $priority
     *
     * @return FgMailMessage
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Get priority
     *
     * @return integer
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set newsletter
     *
     * @param \Common\UtilityBundle\Entity\FgCnNewsletter $newsletter
     *
     * @return FgMailMessage
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
     * Set receiverLog
     *
     * @param \Common\UtilityBundle\Entity\FgCnNewsletterReceiverLog $receiverLog
     *
     * @return FgMailMessage
     */
    public function setReceiverLog(\Common\UtilityBundle\Entity\FgCnNewsletterReceiverLog $receiverLog = null)
    {
        $this->receiverLog = $receiverLog;

        return $this;
    }

    /**
     * Get receiverLog
     *
     * @return \Common\UtilityBundle\Entity\FgCnNewsletterReceiverLog
     */
    public function getReceiverLog()
    {
        return $this->receiverLog;
    }
}

