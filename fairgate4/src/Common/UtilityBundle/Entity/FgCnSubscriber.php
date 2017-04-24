<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCnSubscriber
 */
class FgCnSubscriber
{
    /**
     * @var integer
     */
    private $clubId;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $lastName;

    /**
     * @var string
     */
    private $comany;

    /**
     * @var string
     */
    private $gender;

    /**
     * @var string
     */
    private $salutation;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $editedAt;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $id;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $editedBy;


    /**
     * Set clubId
     *
     * @param integer $clubId
     * @return FgCnSubscriber
     */
    public function setClubId($clubId)
    {
        $this->clubId = $clubId;

        return $this;
    }

    /**
     * Get clubId
     *
     * @return integer
     */
    public function getClubId()
    {
        return $this->clubId;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return FgCnSubscriber
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
     * Set firstName
     *
     * @param string $firstName
     * @return FgCnSubscriber
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     * @return FgCnSubscriber
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set comany
     *
     * @param string $comany
     * @return FgCnSubscriber
     */
    public function setComany($comany)
    {
        $this->comany = $comany;

        return $this;
    }

    /**
     * Get comany
     *
     * @return string
     */
    public function getComany()
    {
        return $this->comany;
    }

    /**
     * Set gender
     *
     * @param string $gender
     * @return FgCnSubscriber
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get gender
     *
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set salutation
     *
     * @param string $salutation
     * @return FgCnSubscriber
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return FgCnSubscriber
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
     * Set editedAt
     *
     * @param \DateTime $editedAt
     * @return FgCnSubscriber
     */
    public function setEditedAt($editedAt)
    {
        $this->editedAt = $editedAt;

        return $this;
    }

    /**
     * Get editedAt
     *
     * @return \DateTime
     */
    public function getEditedAt()
    {
        return $this->editedAt;
    }

    /**
     * Set id
     *
     * @param \Common\UtilityBundle\Entity\FgClub $id
     * @return FgCnSubscriber
     */
    public function setId(\Common\UtilityBundle\Entity\FgClub $id = null)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return \Common\UtilityBundle\Entity\FgClub
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set editedBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $editedBy
     * @return FgCnSubscriber
     */
    public function setEditedBy(\Common\UtilityBundle\Entity\FgCmContact $editedBy = null)
    {
        $this->editedBy = $editedBy;

        return $this;
    }

    /**
     * Get editedBy
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact
     */
    public function getEditedBy()
    {
        return $this->editedBy;
    }
    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;


    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     * @return FgCnSubscriber
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
     * @var string
     */
    private $company;

    /**
     * @var string
     */
    private $importTable;

    /**
     * @var integer
     */
    private $importId;


    /**
     * Set company
     *
     * @param string $company
     * @return FgCnSubscriber
     */
    public function setCompany($company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set importTable
     *
     * @param string $importTable
     * @return FgCnSubscriber
     */
    public function setImportTable($importTable)
    {
        $this->importTable = $importTable;

        return $this;
    }

    /**
     * Get importTable
     *
     * @return string
     */
    public function getImportTable()
    {
        return $this->importTable;
    }

    /**
     * Set importId
     *
     * @param integer $importId
     * @return FgCnSubscriber
     */
    public function setImportId($importId)
    {
        $this->importId = $importId;

        return $this;
    }

    /**
     * Get importId
     *
     * @return integer
     */
    public function getImportId()
    {
        return $this->importId;
    }
    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $createdBy;


    /**
     * Set createdBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $createdBy
     * @return FgCnSubscriber
     */
    public function setCreatedBy(\Common\UtilityBundle\Entity\FgCmContact $createdBy = null)
    {
        $this->createdBy = $createdBy;
    
        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact 
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }
    /**
     * @var string
     */
    private $correspondanceLang;


    /**
     * Set correspondanceLang
     *
     * @param string $correspondanceLang
     * @return FgCnSubscriber
     */
    public function setCorrespondanceLang($correspondanceLang)
    {
        $this->correspondanceLang = $correspondanceLang;
    
        return $this;
    }

    /**
     * Get correspondanceLang
     *
     * @return string 
     */
    public function getCorrespondanceLang()
    {
        return $this->correspondanceLang;
    }
}