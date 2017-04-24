<?php

namespace Common\UtilityBundle\Entity;

/**
 * MasterSystem
 */
class MasterSystem
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $salutation;

    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $primaryEmail;

    /**
     * @var \DateTime
     */
    private $dateOfBirth;

    /**
     * @var string
     */
    private $teamProfilePicture;

    /**
     * @var string
     */
    private $companyName;

    /**
     * @var string
     */
    private $clubProfilePicture;

    /**
     * @var string
     */
    private $lastName;

    /**
     * @var string
     */
    private $correspondanceStrasse;

    /**
     * @var string
     */
    private $companyLogo;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $gender;

    /**
     * @var string
     */
    private $nationality1;

    /**
     * @var string
     */
    private $correspondanceOrt;

    /**
     * @var string
     */
    private $correspondanceKanton;

    /**
     * @var string
     */
    private $correspondancePlz;

    /**
     * @var string
     */
    private $mobile1;

    /**
     * @var string
     */
    private $mobile2;

    /**
     * @var string
     */
    private $parentEmail1;

    /**
     * @var string
     */
    private $correspondanceLand;

    /**
     * @var string
     */
    private $nationality2;

    /**
     * @var string
     */
    private $parentEmail2;

    /**
     * @var string
     */
    private $correspondanceLang;

    /**
     * @var string
     */
    private $website;

    /**
     * @var string
     */
    private $correspondancePostfach;

    /**
     * @var string
     */
    private $invoiceStrasse;

    /**
     * @var string
     */
    private $invoicePlz;

    /**
     * @var string
     */
    private $invoiceOrt;

    /**
     * @var string
     */
    private $invoiceKanton;

    /**
     * @var string
     */
    private $invoiceLand;

    /**
     * @var string
     */
    private $invoicePostfach;

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
     * Set salutation
     *
     * @param string $salutation
     *
     * @return MasterSystem
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
     * Set firstName
     *
     * @param string $firstName
     *
     * @return MasterSystem
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
     * Set primaryEmail
     *
     * @param string $primaryEmail
     *
     * @return MasterSystem
     */
    public function setPrimaryEmail($primaryEmail)
    {
        $this->primaryEmail = $primaryEmail;

        return $this;
    }

    /**
     * Get primaryEmail
     *
     * @return string
     */
    public function getPrimaryEmail()
    {
        return $this->primaryEmail;
    }

    /**
     * Set dateOfBirth
     *
     * @param \DateTime $dateOfBirth
     *
     * @return MasterSystem
     */
    public function setDateOfBirth($dateOfBirth)
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    /**
     * Get dateOfBirth
     *
     * @return \DateTime
     */
    public function getDateOfBirth()
    {
        return $this->dateOfBirth;
    }

    /**
     * Set teamProfilePicture
     *
     * @param string $teamProfilePicture
     *
     * @return MasterSystem
     */
    public function setTeamProfilePicture($teamProfilePicture)
    {
        $this->teamProfilePicture = $teamProfilePicture;

        return $this;
    }

    /**
     * Get teamProfilePicture
     *
     * @return string
     */
    public function getTeamProfilePicture()
    {
        return $this->teamProfilePicture;
    }

    /**
     * Set companyName
     *
     * @param string $companyName
     *
     * @return MasterSystem
     */
    public function setCompanyName($companyName)
    {
        $this->companyName = $companyName;

        return $this;
    }

    /**
     * Get companyName
     *
     * @return string
     */
    public function getCompanyName()
    {
        return $this->companyName;
    }

    /**
     * Set clubProfilePicture
     *
     * @param string $clubProfilePicture
     *
     * @return MasterSystem
     */
    public function setClubProfilePicture($clubProfilePicture)
    {
        $this->clubProfilePicture = $clubProfilePicture;

        return $this;
    }

    /**
     * Get clubProfilePicture
     *
     * @return string
     */
    public function getClubProfilePicture()
    {
        return $this->clubProfilePicture;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     *
     * @return MasterSystem
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
     * Set correspondanceStrasse
     *
     * @param string $correspondanceStrasse
     *
     * @return MasterSystem
     */
    public function setCorrespondanceStrasse($correspondanceStrasse)
    {
        $this->correspondanceStrasse = $correspondanceStrasse;

        return $this;
    }

    /**
     * Get correspondanceStrasse
     *
     * @return string
     */
    public function getCorrespondanceStrasse()
    {
        return $this->correspondanceStrasse;
    }

    /**
     * Set companyLogo
     *
     * @param string $companyLogo
     *
     * @return MasterSystem
     */
    public function setCompanyLogo($companyLogo)
    {
        $this->companyLogo = $companyLogo;

        return $this;
    }

    /**
     * Get companyLogo
     *
     * @return string
     */
    public function getCompanyLogo()
    {
        return $this->companyLogo;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return MasterSystem
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set gender
     *
     * @param string $gender
     *
     * @return MasterSystem
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
     * Set nationality1
     *
     * @param string $nationality1
     *
     * @return MasterSystem
     */
    public function setNationality1($nationality1)
    {
        $this->nationality1 = $nationality1;

        return $this;
    }

    /**
     * Get nationality1
     *
     * @return string
     */
    public function getNationality1()
    {
        return $this->nationality1;
    }

    /**
     * Set correspondanceOrt
     *
     * @param string $correspondanceOrt
     *
     * @return MasterSystem
     */
    public function setCorrespondanceOrt($correspondanceOrt)
    {
        $this->correspondanceOrt = $correspondanceOrt;

        return $this;
    }

    /**
     * Get correspondanceOrt
     *
     * @return string
     */
    public function getCorrespondanceOrt()
    {
        return $this->correspondanceOrt;
    }

    /**
     * Set correspondanceKanton
     *
     * @param string $correspondanceKanton
     *
     * @return MasterSystem
     */
    public function setCorrespondanceKanton($correspondanceKanton)
    {
        $this->correspondanceKanton = $correspondanceKanton;

        return $this;
    }

    /**
     * Get correspondanceKanton
     *
     * @return string
     */
    public function getCorrespondanceKanton()
    {
        return $this->correspondanceKanton;
    }

    /**
     * Set correspondancePlz
     *
     * @param string $correspondancePlz
     *
     * @return MasterSystem
     */
    public function setCorrespondancePlz($correspondancePlz)
    {
        $this->correspondancePlz = $correspondancePlz;

        return $this;
    }

    /**
     * Get correspondancePlz
     *
     * @return string
     */
    public function getCorrespondancePlz()
    {
        return $this->correspondancePlz;
    }

    /**
     * Set mobile1
     *
     * @param string $mobile1
     *
     * @return MasterSystem
     */
    public function setMobile1($mobile1)
    {
        $this->mobile1 = $mobile1;

        return $this;
    }

    /**
     * Get mobile1
     *
     * @return string
     */
    public function getMobile1()
    {
        return $this->mobile1;
    }

    /**
     * Set mobile2
     *
     * @param string $mobile2
     *
     * @return MasterSystem
     */
    public function setMobile2($mobile2)
    {
        $this->mobile2 = $mobile2;

        return $this;
    }

    /**
     * Get mobile2
     *
     * @return string
     */
    public function getMobile2()
    {
        return $this->mobile2;
    }

    /**
     * Set parentEmail1
     *
     * @param string $parentEmail1
     *
     * @return MasterSystem
     */
    public function setParentEmail1($parentEmail1)
    {
        $this->parentEmail1 = $parentEmail1;

        return $this;
    }

    /**
     * Get parentEmail1
     *
     * @return string
     */
    public function getParentEmail1()
    {
        return $this->parentEmail1;
    }

    /**
     * Set correspondanceLand
     *
     * @param string $correspondanceLand
     *
     * @return MasterSystem
     */
    public function setCorrespondanceLand($correspondanceLand)
    {
        $this->correspondanceLand = $correspondanceLand;

        return $this;
    }

    /**
     * Get correspondanceLand
     *
     * @return string
     */
    public function getCorrespondanceLand()
    {
        return $this->correspondanceLand;
    }

    /**
     * Set nationality2
     *
     * @param string $nationality2
     *
     * @return MasterSystem
     */
    public function setNationality2($nationality2)
    {
        $this->nationality2 = $nationality2;

        return $this;
    }

    /**
     * Get nationality2
     *
     * @return string
     */
    public function getNationality2()
    {
        return $this->nationality2;
    }

    /**
     * Set parentEmail2
     *
     * @param string $parentEmail2
     *
     * @return MasterSystem
     */
    public function setParentEmail2($parentEmail2)
    {
        $this->parentEmail2 = $parentEmail2;

        return $this;
    }

    /**
     * Get parentEmail2
     *
     * @return string
     */
    public function getParentEmail2()
    {
        return $this->parentEmail2;
    }

    /**
     * Set correspondanceLang
     *
     * @param string $correspondanceLang
     *
     * @return MasterSystem
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

    /**
     * Set website
     *
     * @param string $website
     *
     * @return MasterSystem
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Get website
     *
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set correspondancePostfach
     *
     * @param string $correspondancePostfach
     *
     * @return MasterSystem
     */
    public function setCorrespondancePostfach($correspondancePostfach)
    {
        $this->correspondancePostfach = $correspondancePostfach;

        return $this;
    }

    /**
     * Get correspondancePostfach
     *
     * @return string
     */
    public function getCorrespondancePostfach()
    {
        return $this->correspondancePostfach;
    }

    /**
     * Set invoiceStrasse
     *
     * @param string $invoiceStrasse
     *
     * @return MasterSystem
     */
    public function setInvoiceStrasse($invoiceStrasse)
    {
        $this->invoiceStrasse = $invoiceStrasse;

        return $this;
    }

    /**
     * Get invoiceStrasse
     *
     * @return string
     */
    public function getInvoiceStrasse()
    {
        return $this->invoiceStrasse;
    }

    /**
     * Set invoicePlz
     *
     * @param string $invoicePlz
     *
     * @return MasterSystem
     */
    public function setInvoicePlz($invoicePlz)
    {
        $this->invoicePlz = $invoicePlz;

        return $this;
    }

    /**
     * Get invoicePlz
     *
     * @return string
     */
    public function getInvoicePlz()
    {
        return $this->invoicePlz;
    }

    /**
     * Set invoiceOrt
     *
     * @param string $invoiceOrt
     *
     * @return MasterSystem
     */
    public function setInvoiceOrt($invoiceOrt)
    {
        $this->invoiceOrt = $invoiceOrt;

        return $this;
    }

    /**
     * Get invoiceOrt
     *
     * @return string
     */
    public function getInvoiceOrt()
    {
        return $this->invoiceOrt;
    }

    /**
     * Set invoiceKanton
     *
     * @param string $invoiceKanton
     *
     * @return MasterSystem
     */
    public function setInvoiceKanton($invoiceKanton)
    {
        $this->invoiceKanton = $invoiceKanton;

        return $this;
    }

    /**
     * Get invoiceKanton
     *
     * @return string
     */
    public function getInvoiceKanton()
    {
        return $this->invoiceKanton;
    }

    /**
     * Set invoiceLand
     *
     * @param string $invoiceLand
     *
     * @return MasterSystem
     */
    public function setInvoiceLand($invoiceLand)
    {
        $this->invoiceLand = $invoiceLand;

        return $this;
    }

    /**
     * Get invoiceLand
     *
     * @return string
     */
    public function getInvoiceLand()
    {
        return $this->invoiceLand;
    }

    /**
     * Set invoicePostfach
     *
     * @param string $invoicePostfach
     *
     * @return MasterSystem
     */
    public function setInvoicePostfach($invoicePostfach)
    {
        $this->invoicePostfach = $invoicePostfach;

        return $this;
    }

    /**
     * Get invoicePostfach
     *
     * @return string
     */
    public function getInvoicePostfach()
    {
        return $this->invoicePostfach;
    }

    /**
     * Set fedContact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $fedContact
     *
     * @return MasterSystem
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
}

