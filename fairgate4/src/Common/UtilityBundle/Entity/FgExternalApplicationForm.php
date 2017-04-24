<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgExternalApplicationForm
 */
class FgExternalApplicationForm
{
    /**
     * @var integer
     */
    private $id;

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
    private $gender;

    /**
     * @var \DateTime
     */
    private $dob;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $street;

    /**
     * @var string
     */
    private $zipcode;

    /**
     * @var string
     */
    private $location;

    /**
     * @var string
     */
    private $telM;

    /**
     * @var string
     */
    private $telG;

    /**
     * @var string
     */
    private $relatives;

    /**
     * @var string
     */
    private $employer;
    
    /**
     * @var integer
     */
    private $personalNumber;

    /**
     * @var string
     */
    private $comment;

    /**
     * @var string
     */
    private $clubSelected;

    /**
     * @var \DateTime
     */
    private $createdDate;

    /**
     * @var \DateTime
     */
    private $decisionDate;

    /**
     * @var string
     */
    private $status;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmMembership
     */
    private $fedMembership;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $decidedBy;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $fedContact;


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
     * Set firstName
     *
     * @param string $firstName
     * @return FgExternalApplicationForm
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
     * @return FgExternalApplicationForm
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
     * Set gender
     *
     * @param string $gender
     * @return FgExternalApplicationForm
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
     * Set dob
     *
     * @param \DateTime $dob
     * @return FgExternalApplicationForm
     */
    public function setDob($dob)
    {
        $this->dob = $dob;
    
        return $this;
    }

    /**
     * Get dob
     *
     * @return \DateTime 
     */
    public function getDob()
    {
        return $this->dob;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return FgExternalApplicationForm
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
     * Set street
     *
     * @param string $street
     * @return FgExternalApplicationForm
     */
    public function setStreet($street)
    {
        $this->street = $street;
    
        return $this;
    }

    /**
     * Get street
     *
     * @return string 
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Set zipcode
     *
     * @param string $zipcode
     * @return FgExternalApplicationForm
     */
    public function setZipcode($zipcode)
    {
        $this->zipcode = $zipcode;
    
        return $this;
    }

    /**
     * Get zipcode
     *
     * @return string 
     */
    public function getZipcode()
    {
        return $this->zipcode;
    }

    /**
     * Set location
     *
     * @param string $location
     * @return FgExternalApplicationForm
     */
    public function setLocation($location)
    {
        $this->location = $location;
    
        return $this;
    }

    /**
     * Get location
     *
     * @return string 
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set telM
     *
     * @param string $telM
     * @return FgExternalApplicationForm
     */
    public function setTelM($telM)
    {
        $this->telM = $telM;
    
        return $this;
    }

    /**
     * Get telM
     *
     * @return string 
     */
    public function getTelM()
    {
        return $this->telM;
    }

    /**
     * Set telG
     *
     * @param string $telG
     * @return FgExternalApplicationForm
     */
    public function setTelG($telG)
    {
        $this->telG = $telG;
    
        return $this;
    }

    /**
     * Get telG
     *
     * @return string 
     */
    public function getTelG()
    {
        return $this->telG;
    }

    /**
     * Set relatives
     *
     * @param string $relatives
     * @return FgExternalApplicationForm
     */
    public function setRelatives($relatives)
    {
        $this->relatives = $relatives;
    
        return $this;
    }

    /**
     * Get relatives
     *
     * @return string 
     */
    public function getRelatives()
    {
        return $this->relatives;
    }

    /**
     * Set employer
     *
     * @param string $employer
     * @return FgExternalApplicationForm
     */
    public function setEmployer($employer)
    {
        $this->employer = $employer;
    
        return $this;
    }
    
    /**
     * Get employer
     *
     * @return string 
     */
    public function getEmployer()
    {
        return $this->employer;
    }
    
    /**
     * Set personalNumber
     *
     * @param int $personalNumber
     * 
     * @return FgExternalApplicationForm
     */
    public function setPersonalNumber($personalNumber)
    {
        $this->personalNumber = $personalNumber;
    
        return $this;
    }
    
    /**
     * Get $personalNumber
     *
     * @return int 
     */
    public function getPersonalNumber()
    {
        return $this->personalNumber;
    }

    /**
     * Set comment
     *
     * @param string $comment
     * @return FgExternalApplicationForm
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    
        return $this;
    }

    /**
     * Get comment
     *
     * @return string 
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set clubSelected
     *
     * @param string $clubSelected
     * @return FgExternalApplicationForm
     */
    public function setClubSelected($clubSelected)
    {
        $this->clubSelected = $clubSelected;
    
        return $this;
    }

    /**
     * Get clubSelected
     *
     * @return string 
     */
    public function getClubSelected()
    {
        return $this->clubSelected;
    }

    /**
     * Set createdDate
     *
     * @param \DateTime $createdDate
     * @return FgExternalApplicationForm
     */
    public function setCreatedDate($createdDate)
    {
        $this->createdDate = $createdDate;
    
        return $this;
    }

    /**
     * Get createdDate
     *
     * @return \DateTime 
     */
    public function getCreatedDate()
    {
        return $this->createdDate;
    }

    /**
     * Set decisionDate
     *
     * @param \DateTime $decisionDate
     * @return FgExternalApplicationForm
     */
    public function setDecisionDate($decisionDate)
    {
        $this->decisionDate = $decisionDate;
    
        return $this;
    }

    /**
     * Get decisionDate
     *
     * @return \DateTime 
     */
    public function getDecisionDate()
    {
        return $this->decisionDate;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return FgExternalApplicationForm
     */
    public function setStatus($status)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set fedMembership
     *
     * @param \Common\UtilityBundle\Entity\FgCmMembership $fedMembership
     * @return FgExternalApplicationForm
     */
    public function setFedMembership(\Common\UtilityBundle\Entity\FgCmMembership $fedMembership = null)
    {
        $this->fedMembership = $fedMembership;
    
        return $this;
    }

    /**
     * Get fedMembership
     *
     * @return \Common\UtilityBundle\Entity\FgCmMembership 
     */
    public function getFedMembership()
    {
        return $this->fedMembership;
    }

    /**
     * Set decidedBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $decidedBy
     * @return FgExternalApplicationForm
     */
    public function setDecidedBy(\Common\UtilityBundle\Entity\FgCmContact $decidedBy = null)
    {
        $this->decidedBy = $decidedBy;
    
        return $this;
    }

    /**
     * Get decidedBy
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact 
     */
    public function getDecidedBy()
    {
        return $this->decidedBy;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     * @return FgExternalApplicationForm
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
     * Set fedContact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $fedContact
     * @return FgExternalApplicationForm
     */
    public function setFedContact(\Common\UtilityBundle\Entity\FgCmContact $fedContact = null)
    {
        $this->fedContact = $fedContact;
    
        return $this;
    }

    /**
     * Get fedContact
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact 
     */
    public function getFedContact()
    {
        return $this->fedContact;
    }
    
    /**
     * Set employernumber
     *
     * @param int employernumber
     * 
     * @return FgExternalApplicationForm
     */
    public function setEmployernumber($employernumber)
    {
        $this->employernumber = $employernumber;
    
        return $this;
    }
    
    /**
     * Get $personalNumber
     *
     * @return int 
     */
    public function getEmployernumber()
    {
        return $this->employernumber;
    }
}
