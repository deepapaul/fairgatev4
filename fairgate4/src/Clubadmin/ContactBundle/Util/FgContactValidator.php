<?php

namespace Clubadmin\ContactBundle\Util;

use Common\UtilityBundle\Util\FgUtility;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContext;
use Clubadmin\ContactBundle\Validator\Constraints\FgEmailExists;
use Common\UtilityBundle\Util\FgSettings;

/**
 * For handling the validation of contact data edit via inline edit.
 */
class FgContactValidator
{
    /**
     * $em.
     *
     * @var object {entitymanager object}
     */
    private $em;

    /**
     * $contactId.
     *
     * @var int ContactId
     */
    private $contactId;

    /**
     * $club.
     *
     * @var Service {clubservice}
     */
    private $club;

    /**
     * $container.
     *
     * @var object {container object}
     */
    private $container;

    /**
     * $attributeId.
     *
     * @var int AttributeId
     */
    private $attributeId;

    /**
     * $value.
     *
     * @var string New value
     */
    private $value;

    /**
     * $contactData.
     *
     * @var array Contact details Array
     */
    private $contactData = array();

    /**
     * $clubData.
     *
     * @var array Club details Array
     */
    private $clubData = array();
    /**
     * $valid.
     *
     * @var int Valid flag
     */
    private $valid = 0;

    /**
     * $isRequired.
     *
     * @var int Required flag
     */
    private $isRequired = 0;

    /**
     * $attributeDetails.
     *
     * @var array AttributeDetailsArray
     */
    private $attributeDetails = array();
    /* date format */
    private $phpDateFormat;
    /**
     * Constructor for initial setting.
     *
     * @param object $container
     * @param int    $contactId
     * @param int    $attributeId
     * @param string $value
     * @param array  $contactData
     */
    public function __construct($container, $contactId, $attributeId, $value, $contactData, $clubData)
    {
        $this->contactId = $contactId;
        $this->container = $container;
        $this->club = $this->container->get('club');
        $this->em = $this->container->get('doctrine')->getManager();
        $this->attributeId = $attributeId;
        $this->contactData = $contactData;
        $this->value = $value;
        $this->clubData = $clubData;
        $this->setAttributeDetails();
        $this->phpDateFormat = FgSettings::getPhpDateFormat();
    }

    /**
     * Function to initialize data.
     */
    private function setAttributeDetails()
    {
        switch ($this->attributeId) {
            case 'CMfirst_joining_date':
            case 'CMjoining_date':
            case 'CMleaving_date':
            case 'FMfirst_joining_date':
            case 'FMjoining_date':
            case 'FMleaving_date':
            case 'Function':
                break;
            default:
                $conn = $this->em->getConnection();
                $clubId = $this->clubData['clubId'];
                $federationId = $this->clubData['federationId'];
                $subFederationId = $this->clubData['subFederationId'];
                $clubType = $this->club->get('type');
                $clubHeirarchy = $this->club->get('clubHeirarchy');
                $clubDefaultLang = $this->club->get('clubDefaultLang');
                $clubDefaultSystemLang = $this->club->get('clubDefaultSystemLang');
                $clubDetails = array('clubId' => $clubId, 'federationId' => $federationId, 'subFederationId' => $subFederationId, 'clubType' => $clubType, 'clubHeirarchy' => $clubHeirarchy, 'clubDefaultLang' => $clubDefaultLang, 'clubDefaultSystemLang' => $clubDefaultSystemLang);
                $attributeDetailsArray = $this->em->getRepository('CommonUtilityBundle:FgCmAttributeset')->getAttributeDetails($this->attributeId, $clubDetails, $conn);
                $this->attributeDetails = $attributeDetailsArray[0];
                break;
        }
    }

    /**
     * Function to do required validation.
     */
    private function doRequiredFieldValidation()
    {
        $this->systemFieldsReqdValidation();
        if ($this->valid) {
            $this->fieldSettingsReqdValidation();
        }
    }

    /**
     * Function to do systems field reqd validation.
     */
    private function systemFieldsReqdValidation()
    {
        $firstName = $this->container->getParameter('system_field_firstname');
        $lastName = $this->container->getParameter('system_field_lastname');
        $companyName = $this->container->getParameter('system_field_companyname');
        $gender = $this->container->getParameter('system_field_gender');
        $salutation = $this->container->getParameter('system_field_salutaion');
        $correspondanceLang = $this->container->getParameter('system_field_corress_lang');
        if (($this->contactData['is_company'] == 0) || (($this->contactData['is_company'] == 1) && ($this->contactData['has_main_contact'] == 1))) {
            $defReqdSystemFields = array($firstName, $lastName, $gender, $salutation, $correspondanceLang);
            if ($this->contactData['is_company'] == 1) {
                $defReqdSystemFields[] = $companyName;
            }
        } elseif ($this->contactData['is_company'] == 1) {
            $defReqdSystemFields = array($companyName, $correspondanceLang);
        }
        if (in_array($this->attributeId, $defReqdSystemFields)) {
            $this->valid = ($this->value == '') ? 0 : 1;
            $this->isRequired = 1;
        } else {
            $this->valid = 1;
            $this->isRequired = 0;
        }
    }

    /**
     * Function to do federation reqd field validation.
     */
    private function isFederationRequiredField()
    {
        $clubType = $this->club->get('type');
        switch ($clubType) {
            case 'sub_federation':
                $isFedReqd = $this->attributeDetails['isRequiredFedmemberSubfed'];
                break;
            case 'federation_club':
            case 'sub_federation_club':
                $isFedReqd = $this->attributeDetails['isRequiredFedmemberClub'];
                break;
            default:
                $isFedReqd = 0;
                break;
        }

        return $isFedReqd;
    }

    /**
     * Function to do club reqd field validation.
     */
    private function isClubRequiredField()
    {
        $isClubReqd = 0;
        $isRequiredType = $this->attributeDetails['isRequiredType'];
        switch ($isRequiredType) {
            case 'not_required':
                $isClubReqd = 0;
                break;
            case 'all_contacts':
                $isClubReqd = 1;
                break;
            case 'all_members':
                $isClubReqd = (isset($this->contactData['membership_cat_id']) && ($this->contactData['membership_cat_id'] != null)) ? 1 : 0;
                break;
            case 'selected_members':
                $isClubReqd = 0;
                $requiredMemberships = explode(',', $this->attributeDetails['reqdMemberships']);
                if ((count($requiredMemberships) > 0) && (isset($this->contactData['membership_cat_id']) && ($this->contactData['membership_cat_id'] != null))) {
                    if (in_array($this->contactData['membership_cat_id'], $requiredMemberships)) {
                        $isClubReqd = 1;
                    }
                }
                break;
            default:
                $isClubReqd = 0;
                break;
        }

        return $isClubReqd;
    }

    /**
     * Function to do field settings reqd validation.
     */
    private function fieldSettingsReqdValidation()
    {
        $IsFedReqdField = 0;
        if (isset($this->contactData['is_fed_member']) && ($this->contactData['is_fed_member'] == 1)) {
            $IsFedReqdField = $this->isFederationRequiredField();
        }
        if ($IsFedReqdField) {
            $this->valid = ($this->value == '') ? 0 : 1;
        } else {
            $isClubReqdField = $this->isClubRequiredField();
            if ($isClubReqdField) {
                $this->valid = ($this->value == '') ? 0 : 1;
            } else {
                $this->valid = 1;
            }
        }
        $this->isRequired = ($IsFedReqdField || $isClubReqdField) ? 1 : 0;
    }

    /**
     * Function to validate contact data.
     */
    public function validateContactData()
    {
        $requiredMessage = 'REQUIRED';
        $invalidEmailMessage = 'INVALID_EMAIL';
        $invalidUrl = 'INVALID_URL';
        $emailExistsMessage = 'EMAIL_EXIST';
        $maxMultilineLengthMessage = 'LENGHT_EXCEED';
        $invalidNumberMessage = 'INVALID_NUMBER';
        $updateContactMessage = 'CONTACT_UPDATE_SUCCESS';
        $singleLineTextMaxLength = $this->container->getParameter('singleline_max_length');
        $multiLineTextMaxLength = $this->container->getParameter('multiline_max_length');
        $countryFields = $this->container->getParameter('country_fields');
        $correspondanceLang = $this->container->getParameter('system_field_corress_lang');
        $dataType = $this->attributeDetails['inputType'];
        $valueConstraints = array();
        if ($this->isRequired) {
            $valueConstraints[] = new NotBlank(array('message' => $requiredMessage));
        }
        switch ($dataType) {
            case 'multiline':
                $valueConstraints[] = new Length(array('max' => $multiLineTextMaxLength, 'maxMessage' => $maxMultilineLengthMessage));
                break;
            case 'date':                
                $valueConstraints[] = new Callback(array('callback' => array($this, 'isValidDate')));
                break;
            case 'email':
                $valueConstraints[] = new Email(array('message' => $invalidEmailMessage));
                break;
            case 'login email':
                $valueConstraints[] = new Email(array('message' => $invalidEmailMessage));
                $valueConstraints[] = new FgEmailExists(array('contactId' => $this->contactData['fed_contact_id'], 'hasFedMembership' => $this->contactData['is_fed_member'], 'emailExistsMessage' => $emailExistsMessage));
                break;
            case 'url':
                $valueConstraints[] = new Url(array('message' => $invalidUrl));
                break;
            case 'number':
                $this->value = str_replace(FgSettings::getDecimalMarker(), '.', $this->value);
                if (($this->isRequired == 1) || ($this->value != '')) {
                    $valueConstraints[] = new Range(array('min' => 0, 'invalidMessage' => $invalidNumberMessage));
                }
                break;
            case 'select':
                if (($this->isRequired == 1) || ($this->value != '')) {
                    $choicesArr = ($this->attributeId == $correspondanceLang) ? FgUtility::getAllLanguageNames() : FgUtility::getCountryListchanged();
                    $choices = ($this->attributeId == $correspondanceLang) ? (array_keys($choicesArr)) : ((in_array($this->attributeId, $countryFields)) ? (array_keys($choicesArr)) : (explode(';', $this->attributeDetails['predefinedValue'])));
                    $valueConstraints[] = new Choice(array('choices' => $choices, 'message' => $requiredMessage));
                }
                break;
            case 'checkbox':
                if (($this->isRequired == 1) || ($this->value != '')) {
                    $this->value = ($this->value == '') ? array() : $this->value;
                    $choices = explode(';', $this->attributeDetails['predefinedValue']);
                    $valueConstraints[] = new Choice(array('choices' => $choices, 'multiple' => true, 'min' => 1, 'minMessage' => $requiredMessage, 'message' => $requiredMessage));
                }
                break;
            case 'radio':
                if (($this->isRequired == 1) || ($this->value != '')) {
                    $choices = explode(';', $this->attributeDetails['predefinedValue']);
                    $valueConstraints[] = new Choice(array('min' => 1, 'choices' => $choices, 'minMessage' => $requiredMessage, 'message' => $requiredMessage));
                }
                break;
            default:
                $valueConstraints[] = new Length(array('max' => $singleLineTextMaxLength, 'maxMessage' => $maxMultilineLengthMessage));
                break;
        }
        $fgContactData = array('contactId' => $this->contactId, 'attributeId' => $this->attributeId, 'value' => $this->value);
        $collectionConstraint = new Collection(array(
            'contactId' => array(
                new NotBlank(),
            ),
            'value' => $valueConstraints,
            'attributeId' => array(
                new NotBlank(),
            ),
        ));
        $errors = $this->container->get('validator')->validate($fgContactData, $collectionConstraint);
        if (count($errors) !== 0) {
            $output = array('valid' => 'false', 'msg' => $this->container->get('translator')->trans($errors[0]->getMessage()));
        } else {
            $output = array('valid' => 'true', 'msg' => $this->container->get('translator')->trans($updateContactMessage));
        }

        return $output;
    }

    /**
     * Function to validate date.
     */
    public function isValidDate($date, ExecutionContext $context)
    {
        $mimeTypesMessage = $this->container->get('translator')->trans('NOT_VALID_TYPE');
        $format = FgSettings::getPhpDateFormat();
        if ($date != '') {
            $newDate = \DateTime::createFromFormat($format, $date);
            if (!$newDate) {
                $context->addViolation($mimeTypesMessage);
            }
        }
    }

    /**
     * Function to check is valid.
     */
    public function checkIsValid()
    {
        if (in_array($this->attributeId, array('CMfirst_joining_date', 'CMjoining_date', 'CMleaving_date', 'FMfirst_joining_date', 'FMjoining_date', 'FMleaving_date'))) {
            $output = $this->validateJoiningLeavingDates();
        } elseif ($this->attributeId == 'Function') {
            return array('valid' => 'true', 'msg' => $this->container->get('translator')->trans('CONTACT_UPDATE_SUCCESS'));
        } else {
            $this->doRequiredFieldValidation();
            $output = $this->validateContactData();
        }

        return $output;
    }

    /**
     * Function to validate first joining date.
     */
    public function isValidFirstJoiningDate($joiningDate, ExecutionContext $context)
    {
        if ($joiningDate != '') {
            $isFederation = ($this->clubData['clubType'] == 'federation' ) ? 1 : 0;
            $clubId = ($isFederation) ? $this->clubData['federationId'] : $this->clubData['clubId'];
            $contactId = ($isFederation) ? $this->contactData['fed_contact_id'] : $this->contactId;
            $firstLeavingDate = $this->em->getRepository('CommonUtilityBundle:FgCmMembershipHistory')->getFirstJoiningOrLeavingDate($contactId, $clubId);

            $dateObj = new \DateTime();
            $currentDateTimestamp = strtotime('+1 day');//Actually its yesterdays
            $currentDate = date($this->phpDateFormat);

            $joiningDateTimestamp = $dateObj->createFromFormat($this->phpDateFormat, $joiningDate)->format('U');
            if (count($firstLeavingDate) > 0) {
                $firstLeavingDateTimestamp = $dateObj->createFromFormat($this->phpDateFormat, $firstLeavingDate[0])->format('U');
                if ((($joiningDateTimestamp >= $firstLeavingDateTimestamp) || ($joiningDateTimestamp >= $currentDateTimestamp))) {
                    $joiningDateMessage = $this->container->get('translator')->trans('JOINING_DATE_VALIDATION_MSG', array('%a%' => $firstLeavingDate[0]));
                    $context->addViolation($joiningDateMessage);
                }
            } else {
                if ($joiningDateTimestamp >= $currentDateTimestamp) {
                    $joiningDateMessage = $this->container->get('translator')->trans('JOINING_DATE_VALIDATION_MSG', array('%a%' => $currentDate));
                    $context->addViolation($joiningDateMessage);
                }
            }
        }
    }

    /**
     * Function to validate joining date.
     */
    public function isValidJoiningDate($joiningDate, ExecutionContext $context)
    {
        if ($joiningDate != '') {
            $isFederation = ($this->clubData['clubType'] == 'federation') ? 1 : 0;
            $clubId = ($isFederation) ? $this->clubData['federationId'] : $this->clubData['clubId'];
            $contactId = ($isFederation) ? $this->contactData['fed_contact_id'] : $this->contactId;
            if ($isFederation) {
                $isMember = (isset($this->contactData['fed_membership_cat_id']) && ($this->contactData['fed_membership_cat_id'] != null) && (($this->contactData['is_fed_membership_confirmed'] == '0') || ($this->contactData['is_fed_membership_confirmed'] == '1' && $this->contactData['old_fed_membership_id'] != null))) ? 1 : 0;
            } else {
                $isMember = (isset($this->contactData['club_membership_cat_id']) && ($this->contactData['club_membership_cat_id'] != null)) ? 1 : 0;
            }
            $lastLeavingDates = $this->em->getRepository('CommonUtilityBundle:FgCmMembershipHistory')->getLastLeavingDates($contactId, $clubId);

            
            $dateObj = new \DateTime();
            $currentDateTimestamp = strtotime('+1 day');//Actually its yesterdays
            $currentDate = date($this->phpDateFormat);

            if ($isMember) {
                $joiningDateTimestamp = $dateObj->createFromFormat($this->phpDateFormat, $joiningDate)->format('U');
                if (count($lastLeavingDates) > 0) {
                    $lastLeavingDateTimestamp = $dateObj->createFromFormat($this->phpDateFormat, $lastLeavingDates[0])->format('U');
                    if ((($joiningDateTimestamp <= $lastLeavingDateTimestamp) || ($joiningDateTimestamp >= $currentDateTimestamp))) {
                        $joiningDateMessage = $this->container->get('translator')->trans('JOINING_LEAVING_DATE_VALIDATION_MSG', array('%a%' => $lastLeavingDates[0], '%b%' => $currentDate));
                        $context->addViolation($joiningDateMessage);
                    }
                } else {
                    if ($joiningDateTimestamp >= $currentDateTimestamp) {
                        $joiningDateMessage = $this->container->get('translator')->trans('JOINING_DATE_VALIDATION_MSG', array('%a%' => $currentDate));
                        $context->addViolation($joiningDateMessage);
                    }
                }
            } else {
                $joiningDateTimestamp = $dateObj->createFromFormat($this->phpDateFormat, $joiningDate)->format('U');
                if (count($lastLeavingDates) == 2) {
                    $lastLeavingDateTimestamp = $dateObj->createFromFormat($this->phpDateFormat, $lastLeavingDates[0])->format('U');
                    $lastLeaving1DateTimestamp = $dateObj->createFromFormat($this->phpDateFormat, $lastLeavingDates[1])->format('U');
                    if ((($joiningDateTimestamp >= $lastLeavingDateTimestamp) || ($joiningDateTimestamp <= $lastLeaving1DateTimestamp))) {
                        $joiningDateMessage = $this->container->get('translator')->trans('JOINING_LEAVING_DATE_VALIDATION_MSG', array('%a%' => $lastLeavingDates[1], '%b%' => $lastLeavingDates[0]));
                        $context->addViolation($joiningDateMessage);
                    }
                } elseif (count($lastLeavingDates) == 1) {
                    $lastLeavingDateTimestamp = $dateObj->createFromFormat($this->phpDateFormat, $lastLeavingDates[0])->format('U');
                    if ($joiningDateTimestamp >= $lastLeavingDateTimestamp) {
                        $joiningDateMessage = $this->container->get('translator')->trans('JOINING_DATE_VALIDATION_MSG', array('%a%' => $lastLeavingDates[0]));
                        $context->addViolation($joiningDateMessage);
                    }
                    if ($joiningDateTimestamp >= $currentDateTimestamp) {
                        $joiningDateMessage = $this->container->get('translator')->trans('JOINING_DATE_VALIDATION_MSG', array('%a%' => $currentDate));
                        $context->addViolation($joiningDateMessage);
                    }
                } else {
                    if ($joiningDateTimestamp >= $currentDateTimestamp) {
                        $joiningDateMessage = $this->container->get('translator')->trans('JOINING_DATE_VALIDATION_MSG', array('%a%' => $currentDate));
                        $context->addViolation($joiningDateMessage);
                    }
                }
            }
        }
    }

    /**
     * Function to validate leaving date.
     */
    public function isValidLeavingDate($leavingDate, ExecutionContext $context)
    {
        if ($leavingDate != '') {
            $isFederation = ($this->clubData['clubType'] == 'federation' || $clubType == 'sub_federation') ? 1 : 0;
            $clubId = ($isFederation) ? $this->clubData['federationId'] : $this->clubData['clubId'];
            $contactId = ($isFederation) ? $this->contactData['fed_contact_id'] : $this->contactId;
            $joiningDate = $this->em->getRepository('CommonUtilityBundle:FgCmMembershipHistory')->getLastLeavingDates($contactId, $clubId, 'joiningDate');

            $dateObj = new \DateTime();
            $currentDateTimestamp = strtotime('+1 day');//Actually its yesterdays
            $currentDate = date($this->phpDateFormat);
            if (count($joiningDate) > 0) {
                $leavingDateTimestamp = $dateObj->createFromFormat($this->phpDateFormat, $leavingDate)->format('U');
                $joiningDateTimestamp = $dateObj->createFromFormat($this->phpDateFormat, $joiningDate[0])->format('U');
                $leavingDateMessage = $this->container->get('translator')->trans('JOINING_LEAVING_DATE_VALIDATION_MSG', array('%a%' => $joiningDate[0], '%b%' => $currentDate));
                if (($leavingDateTimestamp >= $currentDateTimestamp) || ($leavingDateTimestamp <= $joiningDateTimestamp)) {
                    $context->addViolation($leavingDateMessage);
                }
            }
        }
    }

    /**
     * Function to validate joining and leaving date.
     */
    private function validateJoiningLeavingDates()
    {
        $requiredMessage = $this->container->get('translator')->trans('REQUIRED');
        $updateContactMessage = 'CONTACT_UPDATE_SUCCESS';
        $valueConstraints = array();
        switch ($this->attributeId) {
            case 'CMfirst_joining_date':
            case 'FMfirst_joining_date':
                $valueConstraints[] = new NotBlank(array('message' => $requiredMessage));
                $valueConstraints[] = new Callback(array('callback' => array($this, 'isValidDate')));
                $valueConstraints[] = new Callback(array('callback' => array($this, 'isValidFirstJoiningDate')));                
                break;
            case 'CMjoining_date':
            case 'FMjoining_date':
                $valueConstraints[] = new NotBlank(array('message' => $requiredMessage));
                $valueConstraints[] = new Callback(array('callback' => array($this, 'isValidDate')));
                $valueConstraints[] = new Callback(array('callback' => array($this, 'isValidJoiningDate')));                        
                break;
            case 'CMleaving_date':
            case 'FMleaving_date':
                $valueConstraints[] = new NotBlank(array('message' => $requiredMessage));
                $valueConstraints[] = new Callback(array('callback' => array($this, 'isValidDate')));                     
                $valueConstraints[] = new Callback(array('callback' => array($this, 'isValidLeavingDate'))); 
                break;
            default:
                break;
        }
        $collectionConstraint = new Collection(array(
            'contactId' => array(
                new NotBlank(array('message' => $requiredMessage)),
            ),
            'value' => $valueConstraints,
            'attributeId' => array(
                new NotBlank(array('message' => $requiredMessage)),
            ),
        ));
        $fgContactData = array('contactId' => $this->contactId, 'attributeId' => $this->attributeId, 'value' => $this->value);
        $errors = $this->container->get('validator')->validate($fgContactData, $collectionConstraint);
        if (count($errors) !== 0) {
            $output = array('valid' => 'false', 'msg' => $errors[0]->getMessage());
        } else {
            $output = array('valid' => 'true', 'msg' => $this->container->get('translator')->trans($updateContactMessage));
        }

        return $output;
    }
}
