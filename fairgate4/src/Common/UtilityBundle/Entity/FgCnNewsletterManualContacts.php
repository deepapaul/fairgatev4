<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgCnNewsletterManualContacts
 */
class FgCnNewsletterManualContacts
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $contact;

    /**
     * @var \Common\UtilityBundle\Entity\FgCnNewsletter
     */
    private $newsletter;


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
     * Set contact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $contact
     *
     * @return FgCnNewsletterManualContacts
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
     * Set newsletter
     *
     * @param \Common\UtilityBundle\Entity\FgCnNewsletter $newsletter
     *
     * @return FgCnNewsletterManualContacts
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
}

