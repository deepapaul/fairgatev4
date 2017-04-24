<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgCnRecepientsEmail
 */
class FgCnRecepientsEmail
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $selectionType;

    /**
     * @var string
     */
    private $emailType;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmAttribute
     */
    private $emailField;

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
     * Set selectionType
     *
     * @param string $selectionType
     *
     * @return FgCnRecepientsEmail
     */
    public function setSelectionType($selectionType)
    {
        $this->selectionType = $selectionType;

        return $this;
    }

    /**
     * Get selectionType
     *
     * @return string
     */
    public function getSelectionType()
    {
        return $this->selectionType;
    }

    /**
     * Set emailType
     *
     * @param string $emailType
     *
     * @return FgCnRecepientsEmail
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
     * Set emailField
     *
     * @param \Common\UtilityBundle\Entity\FgCmAttribute $emailField
     *
     * @return FgCnRecepientsEmail
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
     * Set recepientList
     *
     * @param \Common\UtilityBundle\Entity\FgCnRecepients $recepientList
     *
     * @return FgCnRecepientsEmail
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

