<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgDmContactSighted
 */
class FgDmContactSighted
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Common\UtilityBundle\Entity\FgDmDocuments
     */
    private $document;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $contact;


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
     * Set document
     *
     * @param \Common\UtilityBundle\Entity\FgDmDocuments $document
     *
     * @return FgDmContactSighted
     */
    public function setDocument(\Common\UtilityBundle\Entity\FgDmDocuments $document = null)
    {
        $this->document = $document;

        return $this;
    }

    /**
     * Get document
     *
     * @return \Common\UtilityBundle\Entity\FgDmDocuments
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Set contact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $contact
     *
     * @return FgDmContactSighted
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
}

