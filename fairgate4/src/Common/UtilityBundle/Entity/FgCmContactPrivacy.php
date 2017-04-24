<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCmContactPrivacy
 */
class FgCmContactPrivacy
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $privacy;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $contact;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmAttribute
     */
    private $attribute;


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
     * Set privacy
     *
     * @param string $privacy
     * @return FgCmContactPrivacy
     */
    public function setPrivacy($privacy)
    {
        $this->privacy = $privacy;

        return $this;
    }

    /**
     * Get privacy
     *
     * @return string
     */
    public function getPrivacy()
    {
        return $this->privacy;
    }

    /**
     * Set contact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $contact
     * @return FgCmContactPrivacy
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
     * Set attribute
     *
     * @param \Common\UtilityBundle\Entity\FgCmAttribute $attribute
     * @return FgCmContactPrivacy
     */
    public function setAttribute(\Common\UtilityBundle\Entity\FgCmAttribute $attribute = null)
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * Get attribute
     *
     * @return \Common\UtilityBundle\Entity\FgCmAttribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }
}