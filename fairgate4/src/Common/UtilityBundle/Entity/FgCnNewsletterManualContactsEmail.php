<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCnNewsletterManualContactsEmail
 */
class FgCnNewsletterManualContactsEmail
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
     * @var \Common\UtilityBundle\Entity\FgCnNewsletter
     */
    private $newsletter;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmAttribute
     */
    private $emailField;


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
     * @return FgCnNewsletterManualContactsEmail
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
     * @return FgCnNewsletterManualContactsEmail
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
     * Set newsletter
     *
     * @param \Common\UtilityBundle\Entity\FgCnNewsletter $newsletter
     * @return FgCnNewsletterManualContactsEmail
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
     * Set emailField
     *
     * @param \Common\UtilityBundle\Entity\FgCmAttribute $emailField
     * @return FgCnNewsletterManualContactsEmail
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
}