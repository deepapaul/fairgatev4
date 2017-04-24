<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgCnRecepientsMandatory
 */
class FgCnRecepientsMandatory
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $emailType;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $salutation;

    /**
     * @var string
     */
    private $corresLang;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmAttribute
     */
    private $emailField;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $linkedContact;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $contact;

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
     * Set emailType
     *
     * @param string $emailType
     *
     * @return FgCnRecepientsMandatory
     */
    public function setEmailType($emailType)
    {
        $this->emailType = $emailType;

        return $this;
    }

    /**
     * Get emailType
     *
     * @return string
     */
    public function getEmailType()
    {
        return $this->emailType;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return FgCnRecepientsMandatory
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
     * @return FgCnRecepientsMandatory
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
     * Set corresLang
     *
     * @param string $corresLang
     *
     * @return FgCnRecepientsMandatory
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
     * Set emailField
     *
     * @param \Common\UtilityBundle\Entity\FgCmAttribute $emailField
     *
     * @return FgCnRecepientsMandatory
     */
    public function setEmailField(\Common\UtilityBundle\Entity\FgCmAttribute $emailField = null)
    {
        $this->emailField = $emailField;

        return $this;
    }

    /**
     * Get emailField
     *
     * @return \Common\UtilityBundle\Entity\FgCmAttribute
     */
    public function getEmailField()
    {
        return $this->emailField;
    }

    /**
     * Set linkedContact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $linkedContact
     *
     * @return FgCnRecepientsMandatory
     */
    public function setLinkedContact(\Common\UtilityBundle\Entity\FgCmContact $linkedContact = null)
    {
        $this->linkedContact = $linkedContact;

        return $this;
    }

    /**
     * Get linkedContact
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact
     */
    public function getLinkedContact()
    {
        return $this->linkedContact;
    }

    /**
     * Set contact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $contact
     *
     * @return FgCnRecepientsMandatory
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
     * Set recepientList
     *
     * @param \Common\UtilityBundle\Entity\FgCnRecepients $recepientList
     *
     * @return FgCnRecepientsMandatory
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
}

