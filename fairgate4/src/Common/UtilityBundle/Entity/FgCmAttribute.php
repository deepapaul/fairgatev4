<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgCmAttribute
 */
class FgCmAttribute
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $fieldname;

    /**
     * @var string
     */
    private $fieldnameShort;

    /**
     * @var string
     */
    private $inputType;

    /**
     * @var string
     */
    private $fieldtype;

    /**
     * @var boolean
     */
    private $isSystemField;

    /**
     * @var boolean
     */
    private $isCrucialSystemField;

    /**
     * @var boolean
     */
    private $isFairgateField;

    /**
     * @var boolean
     */
    private $isCompany;

    /**
     * @var boolean
     */
    private $isPersonal;

    /**
     * @var string
     */
    private $predefinedValue;

    /**
     * @var boolean
     */
    private $isSingleEdit;

    /**
     * @var string
     */
    private $addresType;

    /**
     * @var boolean
     */
    private $fedProfileStatus;

    /**
     * @var string
     */
    private $availabilitySubFed;

    /**
     * @var string
     */
    private $availabilityClub;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmAttributeset
     */
    private $attributeset;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmAttribute
     */
    private $address;


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
     * Set fieldname
     *
     * @param string $fieldname
     *
     * @return FgCmAttribute
     */
    public function setFieldname($fieldname)
    {
        $this->fieldname = $fieldname;

        return $this;
    }

    /**
     * Get fieldname
     *
     * @return string
     */
    public function getFieldname()
    {
        return $this->fieldname;
    }

    /**
     * Set fieldnameShort
     *
     * @param string $fieldnameShort
     *
     * @return FgCmAttribute
     */
    public function setFieldnameShort($fieldnameShort)
    {
        $this->fieldnameShort = $fieldnameShort;

        return $this;
    }

    /**
     * Get fieldnameShort
     *
     * @return string
     */
    public function getFieldnameShort()
    {
        return $this->fieldnameShort;
    }

    /**
     * Set inputType
     *
     * @param string $inputType
     *
     * @return FgCmAttribute
     */
    public function setInputType($inputType)
    {
        $this->inputType = $inputType;

        return $this;
    }

    /**
     * Get inputType
     *
     * @return string
     */
    public function getInputType()
    {
        return $this->inputType;
    }

    /**
     * Set fieldtype
     *
     * @param string $fieldtype
     *
     * @return FgCmAttribute
     */
    public function setFieldtype($fieldtype)
    {
        $this->fieldtype = $fieldtype;

        return $this;
    }

    /**
     * Get fieldtype
     *
     * @return string
     */
    public function getFieldtype()
    {
        return $this->fieldtype;
    }

    /**
     * Set isSystemField
     *
     * @param boolean $isSystemField
     *
     * @return FgCmAttribute
     */
    public function setIsSystemField($isSystemField)
    {
        $this->isSystemField = $isSystemField;

        return $this;
    }

    /**
     * Get isSystemField
     *
     * @return boolean
     */
    public function getIsSystemField()
    {
        return $this->isSystemField;
    }

    /**
     * Set isCrucialSystemField
     *
     * @param boolean $isCrucialSystemField
     *
     * @return FgCmAttribute
     */
    public function setIsCrucialSystemField($isCrucialSystemField)
    {
        $this->isCrucialSystemField = $isCrucialSystemField;

        return $this;
    }

    /**
     * Get isCrucialSystemField
     *
     * @return boolean
     */
    public function getIsCrucialSystemField()
    {
        return $this->isCrucialSystemField;
    }

    /**
     * Set isFairgateField
     *
     * @param boolean $isFairgateField
     *
     * @return FgCmAttribute
     */
    public function setIsFairgateField($isFairgateField)
    {
        $this->isFairgateField = $isFairgateField;

        return $this;
    }

    /**
     * Get isFairgateField
     *
     * @return boolean
     */
    public function getIsFairgateField()
    {
        return $this->isFairgateField;
    }

    /**
     * Set isCompany
     *
     * @param boolean $isCompany
     *
     * @return FgCmAttribute
     */
    public function setIsCompany($isCompany)
    {
        $this->isCompany = $isCompany;

        return $this;
    }

    /**
     * Get isCompany
     *
     * @return boolean
     */
    public function getIsCompany()
    {
        return $this->isCompany;
    }

    /**
     * Set isPersonal
     *
     * @param boolean $isPersonal
     *
     * @return FgCmAttribute
     */
    public function setIsPersonal($isPersonal)
    {
        $this->isPersonal = $isPersonal;

        return $this;
    }

    /**
     * Get isPersonal
     *
     * @return boolean
     */
    public function getIsPersonal()
    {
        return $this->isPersonal;
    }

    /**
     * Set predefinedValue
     *
     * @param string $predefinedValue
     *
     * @return FgCmAttribute
     */
    public function setPredefinedValue($predefinedValue)
    {
        $this->predefinedValue = $predefinedValue;

        return $this;
    }

    /**
     * Get predefinedValue
     *
     * @return string
     */
    public function getPredefinedValue()
    {
        return $this->predefinedValue;
    }

    /**
     * Set isSingleEdit
     *
     * @param boolean $isSingleEdit
     *
     * @return FgCmAttribute
     */
    public function setIsSingleEdit($isSingleEdit)
    {
        $this->isSingleEdit = $isSingleEdit;

        return $this;
    }

    /**
     * Get isSingleEdit
     *
     * @return boolean
     */
    public function getIsSingleEdit()
    {
        return $this->isSingleEdit;
    }

    /**
     * Set addresType
     *
     * @param string $addresType
     *
     * @return FgCmAttribute
     */
    public function setAddresType($addresType)
    {
        $this->addresType = $addresType;

        return $this;
    }

    /**
     * Get addresType
     *
     * @return string
     */
    public function getAddresType()
    {
        return $this->addresType;
    }

    /**
     * Set fedProfileStatus
     *
     * @param boolean $fedProfileStatus
     *
     * @return FgCmAttribute
     */
    public function setFedProfileStatus($fedProfileStatus)
    {
        $this->fedProfileStatus = $fedProfileStatus;

        return $this;
    }

    /**
     * Get fedProfileStatus
     *
     * @return boolean
     */
    public function getFedProfileStatus()
    {
        return $this->fedProfileStatus;
    }

    /**
     * Set availabilitySubFed
     *
     * @param string $availabilitySubFed
     *
     * @return FgCmAttribute
     */
    public function setAvailabilitySubFed($availabilitySubFed)
    {
        $this->availabilitySubFed = $availabilitySubFed;

        return $this;
    }

    /**
     * Get availabilitySubFed
     *
     * @return string
     */
    public function getAvailabilitySubFed()
    {
        return $this->availabilitySubFed;
    }

    /**
     * Set availabilityClub
     *
     * @param string $availabilityClub
     *
     * @return FgCmAttribute
     */
    public function setAvailabilityClub($availabilityClub)
    {
        $this->availabilityClub = $availabilityClub;

        return $this;
    }

    /**
     * Get availabilityClub
     *
     * @return string
     */
    public function getAvailabilityClub()
    {
        return $this->availabilityClub;
    }

    /**
     * Set attributeset
     *
     * @param \Common\UtilityBundle\Entity\FgCmAttributeset $attributeset
     *
     * @return FgCmAttribute
     */
    public function setAttributeset(\Common\UtilityBundle\Entity\FgCmAttributeset $attributeset = null)
    {
        $this->attributeset = $attributeset;

        return $this;
    }

    /**
     * Get attributeset
     *
     * @return \Common\UtilityBundle\Entity\FgCmAttributeset
     */
    public function getAttributeset()
    {
        return $this->attributeset;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     *
     * @return FgCmAttribute
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
     * Set address
     *
     * @param \Common\UtilityBundle\Entity\FgCmAttribute $address
     *
     * @return FgCmAttribute
     */
    public function setAddress(\Common\UtilityBundle\Entity\FgCmAttribute $address = null)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return \Common\UtilityBundle\Entity\FgCmAttribute
     */
    public function getAddress()
    {
        return $this->address;
    }
}

