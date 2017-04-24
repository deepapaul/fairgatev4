<?php

namespace Admin\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCmContact
 */
class FgCmContact
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var boolean
     */
    private $isActive;

    /**
     * @var string
     */
    private $firstname;

    /**
     * @var string
     */
    private $lastname;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $mobileNumber;

    /**
     * @var \Admin\UtilityBundle\Entity\FgClub
     */
    private $club;

    /**
     * @var \Admin\UtilityBundle\Entity\FgClub
     */
    private $mainClub;


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
     * Set name
     *
     * @param string $name
     * @return FgCmContact
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return FgCmContact
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    
        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean 
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     * @return FgCmContact
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    
        return $this;
    }

    /**
     * Get firstname
     *
     * @return string 
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     * @return FgCmContact
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    
        return $this;
    }

    /**
     * Get lastname
     *
     * @return string 
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return FgCmContact
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
     * Set mobileNumber
     *
     * @param string $mobileNumber
     * @return FgCmContact
     */
    public function setMobileNumber($mobileNumber)
    {
        $this->mobileNumber = $mobileNumber;
    
        return $this;
    }

    /**
     * Get mobileNumber
     *
     * @return string 
     */
    public function getMobileNumber()
    {
        return $this->mobileNumber;
    }

    /**
     * Set club
     *
     * @param \Admin\UtilityBundle\Entity\FgClub $club
     * @return FgCmContact
     */
    public function setClub(\Admin\UtilityBundle\Entity\FgClub $club = null)
    {
        $this->club = $club;
    
        return $this;
    }

    /**
     * Get club
     *
     * @return \Admin\UtilityBundle\Entity\FgClub 
     */
    public function getClub()
    {
        return $this->club;
    }

    /**
     * Set mainClub
     *
     * @param \Admin\UtilityBundle\Entity\FgClub $mainClub
     * @return FgCmContact
     */
    public function setMainClub(\Admin\UtilityBundle\Entity\FgClub $mainClub = null)
    {
        $this->mainClub = $mainClub;
    
        return $this;
    }

    /**
     * Get mainClub
     *
     * @return \Admin\UtilityBundle\Entity\FgClub 
     */
    public function getMainClub()
    {
        return $this->mainClub;
    }
}
