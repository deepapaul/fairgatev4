<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgCnSmsNumberVerification
 */
class FgCnSmsNumberVerification
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $phoneNumber;

    /**
     * @var string
     */
    private $verficationCode;

    /**
     * @var integer
     */
    private $contactId;

    /**
     * @var boolean
     */
    private $status;


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
     * Set phoneNumber
     *
     * @param string $phoneNumber
     *
     * @return FgCnSmsNumberVerification
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * Get phoneNumber
     *
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Set verficationCode
     *
     * @param string $verficationCode
     *
     * @return FgCnSmsNumberVerification
     */
    public function setVerficationCode($verficationCode)
    {
        $this->verficationCode = $verficationCode;

        return $this;
    }

    /**
     * Get verficationCode
     *
     * @return string
     */
    public function getVerficationCode()
    {
        return $this->verficationCode;
    }

    /**
     * Set contactId
     *
     * @param integer $contactId
     *
     * @return FgCnSmsNumberVerification
     */
    public function setContactId($contactId)
    {
        $this->contactId = $contactId;

        return $this;
    }

    /**
     * Get contactId
     *
     * @return integer
     */
    public function getContactId()
    {
        return $this->contactId;
    }

    /**
     * Set status
     *
     * @param boolean $status
     *
     * @return FgCnSmsNumberVerification
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return boolean
     */
    public function getStatus()
    {
        return $this->status;
    }
}

