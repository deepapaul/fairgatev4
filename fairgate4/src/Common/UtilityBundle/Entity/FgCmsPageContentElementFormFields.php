<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCmsPageContentElementFormFields
 */
class FgCmsPageContentElementFormFields
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
    private $fieldType;

    /**
     * @var string
     */
    private $predefinedValue;

    /**
     * @var string
     */
    private $placeholderValue;

    /**
     * @var string
     */
    private $tooltipValue;

    /**
     * @var boolean
     */
    private $isRequired;

    /**
     * @var integer
     */
    private $sortOrder;

    /**
     * @var boolean
     */
    private $isActive;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @var string
     */
    private $numberMinValue;

    /**
     * @var string
     */
    private $numberMaxValue;

    /**
     * @var string
     */
    private $numberStepValue;

    /**
     * @var \DateTime
     */
    private $dateMin;

    /**
     * @var \DateTime
     */
    private $dateMax;

    /**
     * @var boolean
     */
    private $showSelectionValuesInline;

    /**
     * @var boolean
     */
    private $isMultiSelectable;

    /**
     * @var boolean
     */
    private $useMailForNotification;

    /**
     * @var boolean
     */
    private $isDeleted;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $createdBy;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $updatedBy;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmsPageContentElement
     */
    private $element;


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
     * @return FgCmsPageContentElementFormFields
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
     * Set fieldType
     *
     * @param string $fieldType
     * @return FgCmsPageContentElementFormFields
     */
    public function setFieldType($fieldType)
    {
        $this->fieldType = $fieldType;
    
        return $this;
    }

    /**
     * Get fieldType
     *
     * @return string 
     */
    public function getFieldType()
    {
        return $this->fieldType;
    }

    /**
     * Set predefinedValue
     *
     * @param string $predefinedValue
     * @return FgCmsPageContentElementFormFields
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
     * Set placeholderValue
     *
     * @param string $placeholderValue
     * @return FgCmsPageContentElementFormFields
     */
    public function setPlaceholderValue($placeholderValue)
    {
        $this->placeholderValue = $placeholderValue;
    
        return $this;
    }

    /**
     * Get placeholderValue
     *
     * @return string 
     */
    public function getPlaceholderValue()
    {
        return $this->placeholderValue;
    }

    /**
     * Set tooltipValue
     *
     * @param string $tooltipValue
     * @return FgCmsPageContentElementFormFields
     */
    public function setTooltipValue($tooltipValue)
    {
        $this->tooltipValue = $tooltipValue;
    
        return $this;
    }

    /**
     * Get tooltipValue
     *
     * @return string 
     */
    public function getTooltipValue()
    {
        return $this->tooltipValue;
    }

    /**
     * Set isRequired
     *
     * @param boolean $isRequired
     * @return FgCmsPageContentElementFormFields
     */
    public function setIsRequired($isRequired)
    {
        $this->isRequired = $isRequired;
    
        return $this;
    }

    /**
     * Get isRequired
     *
     * @return boolean 
     */
    public function getIsRequired()
    {
        return $this->isRequired;
    }

    /**
     * Set sortOrder
     *
     * @param integer $sortOrder
     * @return FgCmsPageContentElementFormFields
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;
    
        return $this;
    }

    /**
     * Get sortOrder
     *
     * @return integer 
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return FgCmsPageContentElementFormFields
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return FgCmsPageContentElementFormFields
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
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return FgCmsPageContentElementFormFields
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    
        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime 
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set numberMinValue
     *
     * @param string $numberMinValue
     * @return FgCmsPageContentElementFormFields
     */
    public function setNumberMinValue($numberMinValue)
    {
        $this->numberMinValue = $numberMinValue;
    
        return $this;
    }

    /**
     * Get numberMinValue
     *
     * @return string 
     */
    public function getNumberMinValue()
    {
        return $this->numberMinValue;
    }

    /**
     * Set numberMaxValue
     *
     * @param string $numberMaxValue
     * @return FgCmsPageContentElementFormFields
     */
    public function setNumberMaxValue($numberMaxValue)
    {
        $this->numberMaxValue = $numberMaxValue;
    
        return $this;
    }

    /**
     * Get numberMaxValue
     *
     * @return string 
     */
    public function getNumberMaxValue()
    {
        return $this->numberMaxValue;
    }

    /**
     * Set numberStepValue
     *
     * @param string $numberStepValue
     * @return FgCmsPageContentElementFormFields
     */
    public function setNumberStepValue($numberStepValue)
    {
        $this->numberStepValue = $numberStepValue;
    
        return $this;
    }

    /**
     * Get numberStepValue
     *
     * @return string 
     */
    public function getNumberStepValue()
    {
        return $this->numberStepValue;
    }

    /**
     * Set dateMin
     *
     * @param \DateTime $dateMin
     * @return FgCmsPageContentElementFormFields
     */
    public function setDateMin($dateMin)
    {
        $this->dateMin = $dateMin;
    
        return $this;
    }

    /**
     * Get dateMin
     *
     * @return \DateTime 
     */
    public function getDateMin()
    {
        return $this->dateMin;
    }

    /**
     * Set dateMax
     *
     * @param \DateTime $dateMax
     * @return FgCmsPageContentElementFormFields
     */
    public function setDateMax($dateMax)
    {
        $this->dateMax = $dateMax;
    
        return $this;
    }

    /**
     * Get dateMax
     *
     * @return \DateTime 
     */
    public function getDateMax()
    {
        return $this->dateMax;
    }

    /**
     * Set showSelectionValuesInline
     *
     * @param boolean $showSelectionValuesInline
     * @return FgCmsPageContentElementFormFields
     */
    public function setShowSelectionValuesInline($showSelectionValuesInline)
    {
        $this->showSelectionValuesInline = $showSelectionValuesInline;
    
        return $this;
    }

    /**
     * Get showSelectionValuesInline
     *
     * @return boolean 
     */
    public function getShowSelectionValuesInline()
    {
        return $this->showSelectionValuesInline;
    }

    /**
     * Set isMultiSelectable
     *
     * @param boolean $isMultiSelectable
     * @return FgCmsPageContentElementFormFields
     */
    public function setIsMultiSelectable($isMultiSelectable)
    {
        $this->isMultiSelectable = $isMultiSelectable;
    
        return $this;
    }

    /**
     * Get isMultiSelectable
     *
     * @return boolean 
     */
    public function getIsMultiSelectable()
    {
        return $this->isMultiSelectable;
    }

    /**
     * Set useMailForNotification
     *
     * @param boolean $useMailForNotification
     * @return FgCmsPageContentElementFormFields
     */
    public function setUseMailForNotification($useMailForNotification)
    {
        $this->useMailForNotification = $useMailForNotification;
    
        return $this;
    }

    /**
     * Get useMailForNotification
     *
     * @return boolean 
     */
    public function getUseMailForNotification()
    {
        return $this->useMailForNotification;
    }

    /**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     * @return FgCmsPageContentElementFormFields
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;
    
        return $this;
    }

    /**
     * Get isDeleted
     *
     * @return boolean 
     */
    public function getIsDeleted()
    {
        return $this->isDeleted;
    }

    /**
     * Set createdBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $createdBy
     * @return FgCmsPageContentElementFormFields
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
     * Set updatedBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $updatedBy
     * @return FgCmsPageContentElementFormFields
     */
    public function setUpdatedBy(\Common\UtilityBundle\Entity\FgCmContact $updatedBy = null)
    {
        $this->updatedBy = $updatedBy;
    
        return $this;
    }

    /**
     * Get updatedBy
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact 
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * Set element
     *
     * @param \Common\UtilityBundle\Entity\FgCmsPageContentElement $element
     * @return FgCmsPageContentElementFormFields
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
     * @var string
     */
    private $formFieldType;

    /**
     * @var boolean
     */
    private $isFieldHiddenWithDefaultValue;

    /**
     * @var \DateTime
     */
    private $deletedAt;

    /**
     * @var string
     */
    private $clubMembershipSelection;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmAttribute
     */
    private $attribute;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmsForms
     */
    private $form;


    /**
     * Set formFieldType
     *
     * @param string $formFieldType
     * @return FgCmsPageContentElementFormFields
     */
    public function setFormFieldType($formFieldType)
    {
        $this->formFieldType = $formFieldType;
    
        return $this;
    }

    /**
     * Get formFieldType
     *
     * @return string 
     */
    public function getFormFieldType()
    {
        return $this->formFieldType;
    }

    /**
     * Set isFieldHiddenWithDefaultValue
     *
     * @param boolean $isFieldHiddenWithDefaultValue
     * @return FgCmsPageContentElementFormFields
     */
    public function setIsFieldHiddenWithDefaultValue($isFieldHiddenWithDefaultValue)
    {
        $this->isFieldHiddenWithDefaultValue = $isFieldHiddenWithDefaultValue;
    
        return $this;
    }

    /**
     * Get isFieldHiddenWithDefaultValue
     *
     * @return boolean 
     */
    public function getIsFieldHiddenWithDefaultValue()
    {
        return $this->isFieldHiddenWithDefaultValue;
    }

    /**
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     * @return FgCmsPageContentElementFormFields
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;
    
        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return \DateTime 
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Set clubMembershipSelection
     *
     * @param string $clubMembershipSelection
     * @return FgCmsPageContentElementFormFields
     */
    public function setClubMembershipSelection($clubMembershipSelection)
    {
        $this->clubMembershipSelection = $clubMembershipSelection;
    
        return $this;
    }

    /**
     * Get clubMembershipSelection
     *
     * @return string 
     */
    public function getClubMembershipSelection()
    {
        return $this->clubMembershipSelection;
    }

    /**
     * Set attribute
     *
     * @param \Common\UtilityBundle\Entity\FgCmAttribute $attribute
     * @return FgCmsPageContentElementFormFields
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

    /**
     * Set form
     *
     * @param \Common\UtilityBundle\Entity\FgCmsForms $form
     * @return FgCmsPageContentElementFormFields
     */
    public function setForm(\Common\UtilityBundle\Entity\FgCmsForms $form = null)
    {
        $this->form = $form;
    
        return $this;
    }

    /**
     * Get form
     *
     * @return \Common\UtilityBundle\Entity\FgCmsForms 
     */
    public function getForm()
    {
        return $this->form;
    }
    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $defaultClubMembership;


    /**
     * Set defaultClubMembership
     *
     * @param \Common\UtilityBundle\Entity\FgCmMembership $defaultClubMembership
     * @return FgCmsPageContentElementFormFields
     */
    public function setDefaultClubMembership(\Common\UtilityBundle\Entity\FgCmMembership $defaultClubMembership = null)
    {
        $this->defaultClubMembership = $defaultClubMembership;
    
        return $this;
    }

    /**
     * Get defaultClubMembership
     *
     * @return \Common\UtilityBundle\Entity\FgCmMembership 
     */
    public function getDefaultClubMembership()
    {
        return $this->defaultClubMembership;
    }
}