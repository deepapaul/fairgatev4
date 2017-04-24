<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgDmAssigment
 */
class FgDmAssigment
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $documentType;

    /**
     * @var string
     */
    private $contactAssignType;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $contact;

    /**
     * @var \Common\UtilityBundle\Entity\FgDmDocuments
     */
    private $document;

    /**
     * @var \Common\UtilityBundle\Entity\FgRmRole
     */
    private $role;


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
     * Set documentType
     *
     * @param string $documentType
     *
     * @return FgDmAssigment
     */
    public function setDocumentType($documentType)
    {
        $this->documentType = $documentType;

        return $this;
    }

    /**
     * Get documentType
     *
     * @return string
     */
    public function getDocumentType()
    {
        return $this->documentType;
    }

    /**
     * Set contactAssignType
     *
     * @param string $contactAssignType
     *
     * @return FgDmAssigment
     */
    public function setContactAssignType($contactAssignType)
    {
        $this->contactAssignType = $contactAssignType;

        return $this;
    }

    /**
     * Get contactAssignType
     *
     * @return string
     */
    public function getContactAssignType()
    {
        return $this->contactAssignType;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     *
     * @return FgDmAssigment
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
     * Set contact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $contact
     *
     * @return FgDmAssigment
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
     * Set document
     *
     * @param \Common\UtilityBundle\Entity\FgDmDocuments $document
     *
     * @return FgDmAssigment
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
     * Set role
     *
     * @param \Common\UtilityBundle\Entity\FgRmRole $role
     *
     * @return FgDmAssigment
     */
    public function setRole(\Common\UtilityBundle\Entity\FgRmRole $role = null)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return \Common\UtilityBundle\Entity\FgRmRole
     */
    public function getRole()
    {
        return $this->role;
    }
}

