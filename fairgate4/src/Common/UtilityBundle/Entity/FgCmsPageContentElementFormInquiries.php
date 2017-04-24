<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgCmsPageContentElementFormInquiries
 */
class FgCmsPageContentElementFormInquiries
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var string
     */
    private $formData;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmsPageContentElement
     */
    private $element;

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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return FgCmsPageContentElementFormInquiries
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
     * Set formData
     *
     * @param string $formData
     *
     * @return FgCmsPageContentElementFormInquiries
     */
    public function setFormData($formData)
    {
        $this->formData = $formData;

        return $this;
    }

    /**
     * Get formData
     *
     * @return string
     */
    public function getFormData()
    {
        return $this->formData;
    }

    /**
     * Set element
     *
     * @param \Common\UtilityBundle\Entity\FgCmsPageContentElement $element
     *
     * @return FgCmsPageContentElementFormInquiries
     */
    public function setElement(\Common\UtilityBundle\Entity\FgCmsPageContentElement $element = null)
    {
        $this->element = $element;

        return $this;
    }

    /**
     * Get element
     *
     * @return \Common\UtilityBundle\Entity\FgCmsPageContentElement
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * Set contact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $contact
     *
     * @return FgCmsPageContentElementFormInquiries
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

