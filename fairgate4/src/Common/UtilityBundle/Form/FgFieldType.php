<?php

namespace Common\UtilityBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Constraints\Length;
use Clubadmin\ContactBundle\Validator\Constraints\File;
use Clubadmin\ContactBundle\Validator\Constraints\FgEmailExists;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Util\FgSettings;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * FgFieldType
 *
 * This FgFieldType was created for handling form
 *
 * @package    CommonUtilityBundle
 * @subpackage Form
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
class FgFieldType extends AbstractType
{

    public $fieldId;
    public $fieldType;
    public $fieldValue;
    public $containerParameters;
    public $isIntranet;
    public $contactChanges;
    public $currContactId;

    /**
     * @param Array $fieldsArray         Fields
     * @param Array $submittedData       Data
     * @param Array $editData            Data
     * @param Array $containerParameters Params
     * @param bool  $isIntranet          Whether form is used in intranet or not
     * @param array $contactChanges      Changes to be confirmed
     * @param int   $currContactId       Current logged-in contact
     */
    public function __construct($fieldsArray, $submittedData, $editData, $containerParameters, $isIntranet, $contactChanges, $currContactId)
    {
        $this->fieldsArray = $fieldsArray;
        $this->submittedData = $submittedData;
        $this->editData = $editData;
        $this->containerParameters = $containerParameters;
        $this->isIntranet = $isIntranet;
        $this->contactChanges = $contactChanges;
        $this->currContactId = $currContactId;
    }
    
    /**
     * Method to set custom values as globals
     * 
     * @param array $customValues array of sutom values to set
     */
    private function setCustomValues($customValues) {
        $this->fieldsArray = $customValues['fieldsArray'];
        $this->submittedData = $customValues['submittedData'];
        $this->editData = $customValues['editData'];
        $this->containerParameters = $customValues['containerParameters'];
        $this->isIntranet = (isset($customValues['isIntranet'])) ? $customValues['isIntranet'] : false;
        $this->contactChanges = (isset($customValues['contactChanges'])) ? $customValues['contactChanges'] : array(); 
        $this->currContactId = $customValues['currContactId'];
    }

    /**
     * Function is used to build form
     *
     * @param Int   $builder Form builder
     * @param Array $options Options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->setCustomValues($options['custom_value']);
        $singleLineTextMaxLength = $this->containerParameters->get('singleline_max_length');
        $multiLineTextMaxLength = $this->containerParameters->get('multiline_max_length');
        $maxFileSize = $this->containerParameters->get('max_file_size');
        $imageMimeTypes = $this->containerParameters->get('image_mime_types');
        $unlimitedMimeTypes = $this->containerParameters->get('unlimited_mime_types');

        $categoryPersonal = $this->containerParameters->get('system_category_personal');
        $categoryCorrespondance = $this->containerParameters->get('system_category_address');
        $categoryInvoice = $this->containerParameters->get('system_category_invoice');
        $categoryCompany = $this->containerParameters->get('system_category_company');

        $nationality1 = $this->containerParameters->get('system_field_nationality1');
        $nationality2 = $this->containerParameters->get('system_field_nationality2');

        $land1 = $this->containerParameters->get('system_field_corres_land');
        $land2 = $this->containerParameters->get('system_field_invoice_land');

        $correspondanceLang = $this->containerParameters->get('system_field_corress_lang');
        $teamPicture = $this->containerParameters->get('system_field_team_picture');
        $communityPicture = $this->containerParameters->get('system_field_communitypicture');
        $companyLogo = $this->containerParameters->get('system_field_companylogo');
        $gender = $this->containerParameters->get('system_field_gender');
        $salutation = $this->containerParameters->get('system_field_salutaion');

        $maxSizeMessage = 'SIZE_EXCEED';
        $mimeTypesMessage = 'NOT_VALID_TYPE';
        $requiredMessage = 'REQUIRED';
        $invalidEmailMessage = 'INVALID_EMAIL';
        $virusDetectmessage  =  'VIRUS_FILE_CONTACT';
        $invalidUrl = 'INVALID_URL';
        $emailExistsMessage = 'EMAIL_EXIST';
        $maxMultilineLengthMessage = 'LENGHT_EXCEED';
        $invalidNumberMessage = 'INVALID_NUMBER';
        $selectDefault = 'SELECT_DEFAULT';
        $fieldDetail = $this->fieldsArray;
        $attrTitle = $fieldDetail['attrTitles'];
        $sysLang = $fieldDetail['clubIdArray']['sysLang'];
        $defSysLang = $fieldDetail['clubIdArray']['defSysLang'];
        $contactId = $fieldDetail['contactId'];
        $count = 0;
        foreach ($fieldDetail['values'] as $value2) {
            if ($this->isIntranet) {
                if (($value2['isChangableTeamAdmin'] == '0') || ($value2['isChangableTeamAdmin'] == 0)) {
                    continue;
                }
            }
            $reqType = $dataValue = $dataAttrVal = '';
            $required = false;
            $dataOriginalVal = '';
            if ($this->editData) {
                $attr = $value2['attrId'];
                $dataValue = $dataAttrVal = $this->editData[0][$attr];
                $dataOriginalVal = $dataValue;
            }
            if ($this->submittedData) {
                $dataValue = $this->submittedData[$value2['catId']][$value2['attrId']];
            }
            if ($value2['attrId'] == $teamPicture || $value2['attrId'] == $communityPicture || $value2['attrId'] == $companyLogo) {
                continue;
            }
            $count++;
            if ($value2['isSystemField'] || $value2['isFairgateField']) {
                $fieldTitle = (isset($attrTitle[$value2['attrId']][$defSysLang])) ? $attrTitle[$value2['attrId']][$defSysLang] : $value2['fieldname'];
            } else {
                $fieldTitle = (isset($attrTitle[$value2['attrId']][$sysLang])) ? $attrTitle[$value2['attrId']][$sysLang] : $value2['fieldname'];
            }

            if ($value2['catId'] == $categoryPersonal && $count == 1 && $fieldDetail['fieldType'] == 'Company') {
                //main contact autocomplete and function
                $mcName = array('label' => 'MAIN_CONTACT', 'attr' => array('class' => "form-control hide", 'data-fieldType' => 'company', 'data-attrId' => 'mainContactName'));
                $mcFunction = array('label' => 'FUNCTION', 'attr' => array('class' => "form-control", 'data-fieldType' => 'company', 'data-attrId' => 'mainContactFunction'));
                if ($this->editData) {
                    $mcName['data'] = $this->editData[0]['mainContactName'];
                    $mcFunction['data'] = $this->editData[0]['mainContactFunction'];
                }
                if ($this->getMainContactType() == 'existing') {
                    $mcFunction['constraints'][] = $mcName['constraints'][] = new NotBlank(array('message' => $requiredMessage));
                }
                $builder->add('mainContactName', TextType::class, $mcName);
                $builder->add('mainContactFunction', TextType::class, $mcFunction);
            }
            if ($value2['isCompany'] && $value2['isPersonal']) {
                $fieldType = 'both';
            } else {
                $fieldType = ($value2['isCompany']) ? 'company' : 'personal';
            }
            $isrequiredforfedMember = 0;
            $isRequiredType = (isset($value2['isRequiredType'])) ? $value2['isRequiredType'] : 'not_required';
            $value2['memberships'] = (is_array($value2['memberships']))? $value2['memberships']:array_unique(explode(',', $value2['memberships']));
            $reqMembers = ($isRequiredType == 'selected_members') ? implode(':', $value2['memberships']) : '';
            if (($value2['isRequiredFedmemberClub'] == 1 && (($fieldDetail['clubIdArray']['clubType'] != 'sub_federation') && ($fieldDetail['clubIdArray']['clubType'] != 'federation'))) || ($value2['isRequiredFedmemberSubfed'] == 1 && $fieldDetail['clubIdArray']['clubType'] == 'sub_federation')) {
                if ($isRequiredType == 'not_required' && ($fieldDetail['clubIdArray']['clubType'] != 'federation')) {
                    $reqType = 'FRD';
                    $isRequiredType = ($isRequiredType == 'not_required') ? 'selected_members' : $isRequiredType;
                    $isrequiredforfedMember = 1;
                }
            }
            $required = $this->isRequired($value2['isRequiredType'], $value2['memberships'], $fieldDetail['fedMemberships'], $isrequiredforfedMember, $fieldDetail['selectedMembership'], $fieldDetail['clubIdArray']['clubType']);
            $fieldProp = array('label' => $fieldTitle, 'required' => $required);
            if ($this->submittedData[$categoryCorrespondance]['same_invoice_address'] && $value2['catId'] == $categoryInvoice && !empty($value2['addressId'])) {
                $required = false;
            }
            if ($dataValue != '') {
                $fieldProp['data'] = $dataValue;
            }
            if (isset($this->contactChanges[$value2['attrId']])) {
                $fieldProp['data'] = $dataAttrVal = $this->contactChanges[$value2['attrId']]['value'];
            }
 
            if ($value2['inputType'] == 'date' && isset($this->contactChanges[$value2['attrId']])) {
                if ($dataValue == "0000-00-00 00:00:00" || $dataValue == "0000-00-00" || $dataValue == "" ) {
                    $dataOriginalVal = '';
                } else if(date_create_from_format('Y-m-d', $dataValue)){
                    $date = new \DateTime();
                    $dataOriginalVal = $date->createFromFormat('Y-m-d', $dataValue)->format(FgSettings::getPhpDateFormat());
                } else if(date_create_from_format('d.m.Y', $dataValue)){
                    $date = new \DateTime();
                    $dataOriginalVal = $date->createFromFormat('d.m.Y', $dataValue)->format(FgSettings::getPhpDateFormat());
                } else {
                    $date = new \DateTime();
                    $dataOriginalVal = $date->createFromFormat(FgSettings::getPhpDateFormat(), $dataValue)->format(FgSettings::getPhpDateFormat());
                }

                if ($dataAttrVal == "0000-00-00 00:00:00" || $dataAttrVal == "0000-00-00" || $dataAttrVal == ""  ) {
                    $dataAttrVal = '';
                } else if(date_create_from_format('Y-m-d', $dataAttrVal)){
                    $date = new \DateTime();
                    $dataAttrVal = $date->createFromFormat('Y-m-d', $dataAttrVal)->format(FgSettings::getPhpDateFormat());
                } else if(date_create_from_format('d.m.Y', $dataAttrVal)){
                    $date = new \DateTime();
                    $dataAttrVal = $date->createFromFormat('d.m.Y', $dataAttrVal)->format(FgSettings::getPhpDateFormat());
                } else {
                    $date = new \DateTime();
                    $dataAttrVal = $date->createFromFormat(FgSettings::getPhpDateFormat(), $dataAttrVal)->format(FgSettings::getPhpDateFormat());
                }
                
            }
            if (($value2['attrId'] == $gender) || ($value2['attrId'] == $salutation)) {
                $attrTranslateArray = ($value2['attrId'] == $gender) ? array('Male'=>'CM_MALE','Female'=>'CM_FEMALE') : array('Formal'=>'CM_FORMAL','Informal'=>'CM_INFORMAL');
                $dataOriginalVal = $attrTranslateArray[$dataOriginalVal];
                $dataAttrVal = $attrTranslateArray[$dataAttrVal];
            }
            if ($value2['attrId'] == 'teamfunctions') {
                $dataOriginalVal = $this->editData[0]['savedFunctions'];
                $assignmentDiff1 = array_diff($dataOriginalVal, $dataAttrVal);
                $assignmentDiff2 = array_diff($dataAttrVal, $dataOriginalVal);
                if ((count($assignmentDiff1) > 0) || (count($assignmentDiff2) > 0)) {
                    $functionNames = array();
                    foreach ($dataOriginalVal as $functionId) {
                        $functionNames[] = $value2['teamFunctions'][$functionId];
                    }
                    $dataOriginalVal = implode(',', $functionNames);
                }
                $fieldProp['multiple'] = true;
            }
            $fieldProp['attr'] = array('class' => 'form-control', 'data-fieldType' => $fieldType, 'data-required' => $isRequiredType, 'data-attrId' => $value2['attrId'], 'data-members' => $reqMembers, 'data-reqType' => $reqType, 'data-originalVal' => $dataOriginalVal, 'data-attrVal' => $dataAttrVal);
            if ($this->isIntranet) {
                if (($value2['isConfirmTeamadmin'] == '1') || ($value2['isConfirmTeamadmin'] == 1)) {
                    $fieldProp['attr']['data-key'] = $value2['catId'] . '.' . $value2['attrId'];
                    if ($isRequiredType == 'not_required') {
                        $fieldProp['attr']['data-notrequired'] = 'true';
                    }
                    if ($this->submittedData) {
                        $dataCheckVal = $this->submittedData[$value2['catId']][$value2['attrId']];
                        if (($value2['attrId'] == $gender) || ($value2['attrId'] == $salutation)) {
                            $attrTranslateArray = ($value2['attrId'] == $gender) ? array('Male'=>'CM_MALE','Female'=>'CM_FEMALE') : array('Formal'=>'CM_FORMAL','Informal'=>'CM_INFORMAL');
                            $dataCheckVal = $attrTranslateArray[$dataCheckVal];
                        }
                        if ($dataAttrVal != $dataCheckVal) {
                            $fieldProp['attr']['class'] .= " fairgatedirty";
                        }
                    }
                }
                if (isset($this->contactChanges[$value2['attrId']])) {
                    if ($this->contactChanges[$value2['attrId']]['changedBy'] != $this->currContactId) {
                        $fieldProp['attr']['readonly'] = 'true';
                        $required = false;
                    }
                }
            }
            if ($value2['catId'] == $categoryInvoice || $value2['catId'] == $categoryCorrespondance) {
                $fieldProp['attr']['data-addressType'] = $value2['addressType'];
                $fieldProp['attr']['data-addressId'] = $value2['addressId'];
            }
            if (($fieldDetail['fieldType'] == 'Company') && $value2['catId'] == $categoryPersonal) {
                if ($this->getMainContactType() == 'withMain' && $required) {
                    $fieldProp['constraints'][] = new NotBlank(array('message' => $requiredMessage));
                }
            } elseif ($required) {
                //to handle the image required error
                if ($value2['inputType'] == 'imageupload' || $value2['inputType'] == 'fileupload') {
                    if ($dataValue == "") {
                        if ($this->editData[0][$attr] != "") {
                            $deleted = explode(',', $fieldDetail['deletedFiles']);
                            if (in_array($value2['attrId'], $deleted)) {
                                $fieldProp['constraints'][] = new NotBlank(array('message' => $requiredMessage));
                            }
                        } else {
                            $fieldProp['constraints'][] = new NotBlank(array('message' => $requiredMessage));
                        }
                    }
                    //ends
                } else {
                    $fieldProp['constraints'][] = new NotBlank(array('message' => $requiredMessage));
                }
            }
            switch ($value2['inputType']) {
                case 'multiline':
                    $builder->add($value2['attrId'], TextareaType::class, $fieldProp);
                    $fieldProp['constraints'][] = new Length(array('max' => $multiLineTextMaxLength, 'maxMessage' => $maxMultilineLengthMessage));

                    break;
                case 'date':
                    $fieldProp['widget'] = "single_text";
                    $fieldProp['input'] = 'datetime';
                    if ($fieldProp['data']) {
                        if ($fieldProp['data'] == "0000-00-00 00:00:00" || $fieldProp['data'] == "0000-00-00" ) {
                            unset($fieldProp['data']);
                        } else if(date_create_from_format('Y-m-d', $fieldProp['data'])){
                            $date = new \DateTime();
                            $fieldProp['data'] = $date->createFromFormat('Y-m-d', $fieldProp['data']);
                        } else if(date_create_from_format('d.m.Y', $fieldProp['data'])){
                            $date = new \DateTime();
                            $fieldProp['data'] = $date->createFromFormat('d.m.Y', $fieldProp['data']);
                        } else {
                            $date = new \DateTime();
                            $fieldProp['data'] = $date->createFromFormat(FgSettings::getPhpDateFormat(), $fieldProp['data']);
                        }
                    } else {
                        unset($fieldProp['data']);
                    }
                    $fieldProp['format'] = FgSettings::getSymfonyDateFormat();
                    $fieldProp['attr']['isDate'] = "1";
                    $fieldProp['attr']['class'].=" datemask";
                    $builder->add($value2['attrId'], DateType::class, $fieldProp);
                    break;
                case 'login email':
                    $required = $this->isRequired($value2['isRequiredType'], $value2['memberships'], $fieldDetail['fedMemberships'], $isrequiredforfedMember, $fieldDetail['selectedMembership'], $fieldDetail['clubIdArray']['clubType']);
                    $hasFedMembership = (!empty($fieldDetail['selectedMembership']['fed']) && $fieldDetail['selectedMembership']['fed']!=='default') ? true :false;
                    $fieldProp['constraints'][] = new Email(array('message' => $invalidEmailMessage));
                    $fieldProp['constraints'][] = new FgEmailExists(array('contactId' => $contactId, 'typeOfContact' => $this->contactChanges['typeOfContact'], 'hasFedMembership' => $hasFedMembership, 'emailExistsMessage' => $emailExistsMessage, 'excludeMergableContacts' => true));
                    $builder->add($value2['attrId'], EmailType::class, $fieldProp);
                    break;
                case 'email':
                    $fieldProp['constraints'][] = new Email(array('message' => $invalidEmailMessage));
                    $builder->add($value2['attrId'], EmailType::class, $fieldProp);
                    break;
                case 'url':
                    $fieldProp['attr']['class'].= " fg-urlmask";
                    $fieldProp['constraints'][] = new Url(array('message' => $invalidUrl));
                    $builder->add($value2['attrId'], TextType::class, $fieldProp);
                    break;
                case 'radio':
                    if (is_array($value2['predefinedValue'])) {
                        $values = $value2['predefinedValue'];
                    } else {
                        $value = explode(';', $value2['predefinedValue']);
                        $values = array_combine($value, $value);
                    }
                    $fieldProp['choices'] = array_flip($values);                    
                    $fieldProp['expanded'] = true;
                    if($required==false) {
                        $fieldProp['placeholder'] = 'NONE';
                    }
                    if ($this->editData[1] && $value2['attrId'] == 'contactType' && $this->editData[0]['switchable']==false) {
                       $fieldProp['disabled'] = true;
                    }
                    $fieldProp['attr']['class'] = "radio-list";
                    $builder->add($value2['attrId'], ChoiceType::class, $fieldProp);
                    break;
                case 'checkbox':
                    if (is_array($value2['predefinedValue'])) {
                        $values = $value2['predefinedValue'];
                    } else {
                        $value = explode(';', $value2['predefinedValue']);
                        $values = array_combine($value, $value);
                    }
                    if (empty($fieldProp['data'])) {
                        unset($fieldProp['data']);
                    }
                    elseif (!is_array($fieldProp['data']))
                        $fieldProp['data'] = explode(';', $fieldProp['data']);
                    $fieldProp['attr']['class'] = "radio-list";
                    $fieldProp['choices'] = array_flip($values);    
                    $fieldProp['expanded'] = true;
                    $fieldProp['multiple'] = true;
                    $builder->add($value2['attrId'], ChoiceType::class, $fieldProp);
                    break;
                case 'select':
                    if ($value2['attrId'] == $nationality1 || $value2['attrId'] == $nationality2 || $value2['attrId'] == $land1 || $value2['attrId'] == $land2) {
                        $fieldProp['attr']['data-live-search'] = "true";
                        if ($this->isIntranet) {
                            $fieldProp['attr']['class'] .= " select2 select-with-search";
                        } else {
                            $fieldProp['attr']['class'] .= " bs-select select-with-search";
                        }
                        $fieldProp['placeholder'] = $selectDefault;
                        $fieldProp['choices'] = FgUtility::getCountryListchanged();
                        if ($fieldProp['attr']['data-originalVal'] != '') {
                            $fieldProp['attr']['data-originalValue'] = $fieldProp['choices'][$fieldProp['attr']['data-originalVal']];
                        }
                        $fieldProp['choices'] = array_flip($fieldProp['choices']);
                        $builder->add($value2['attrId'], ChoiceType::class, $fieldProp);
                    } else {
                        $fieldClass= " select2";
                        if (!isset($value2['teamFunctions'])) {
                            $fieldProp['placeholder'] = $selectDefault;
                        }
                        if (isset($value2['membershipCats'])) {
                            if($value2['attrId'] =='fedMembership'){
                                if($value2['isRequiredType']!='all_contacts'){
                                     $fieldProp['placeholder'] = 'NO_FED_MEMBERSHIP';
                                } 
                            } else {
                                $fieldProp['placeholder'] = 'NO_MEMBERSHIP';
                            }
                            $values = $value2['membershipCats'];
                            $fieldClass= " bs-select fg-option-left";
                            if($dataValue==''&& $this->editData[1]){
                                $fieldProp['data'] = 'default';
                            }
                        } elseif (isset($value2['teamFunctions'])) {
                            $values = $value2['teamFunctions'];
                            $values = array_flip($values);
                            $fieldClass = " selectpicker ";
                            $fieldProp['attr']['multiple'] = 'true';
                        } elseif ($value2['attrId'] == $correspondanceLang) {//handle Korrespondenzsprache
                            $values = $fieldDetail['fullLanguageName'];
                            $values = array_flip($values);
                            if(count($values)==1){
                                continue;
                            }
                        } elseif ($value2['attrId'] == $gender || $salutation==$value2['attrId']) {
                            $values= ($value2['attrId'] == $gender) ? array('Male'=>'CM_MALE','Female'=>'CM_FEMALE'):array('Formal'=>'CM_FORMAL','Informal'=>'CM_INFORMAL');
                            $values = array_flip($values);
                            if ($this->isIntranet) {
                                $fieldClass = " bs-select";
                            }
                        } else {
                            $value = explode(';', $value2['predefinedValue']);
                            $values = array_combine($value, $value);
                            $values = array_flip($values);
                        }
                        $fieldProp['attr']['class'] .= $fieldClass;
                        $fieldProp['choices'] = $values;
                        if ($this->editData[1] && $value2['attrId'] == 'fedMembership' && $this->editData[0]['fedMembership'] !='' &&$this->editData[0]['is_fed_membership_confirmed']==true) {
                            $fieldProp['disabled'] = true;
                            $fieldProp['attr']['data-confirm']='pending';
                        }
                        if($this->editData[0]['notOwnContact']==true && $this->editData[1] && $value2['attrId'] == 'fedMembership' ){
                            $fieldProp['disabled'] = true;
                        }
                        $builder->add($value2['attrId'], ChoiceType::class, $fieldProp);
                    }
                    break;
                case 'fileupload':
                    $fieldProp['attr']['class'] = "";
                    $fieldProp['attr']['data-value'] = $this->editData[0][$attr];
                    $fieldProp['attr']['data-changedVal'] = $fieldProp['data'];
                    unset($fieldProp['data']);
                    $fieldProp['constraints'][] = new File(array('maxSize' => $maxFileSize, 'mimeTypes' => $unlimitedMimeTypes, 'maxSizeMessage' => $maxSizeMessage, 'mimeTypesMessage' => $mimeTypesMessage,'virusDetectmessage' =>$virusDetectmessage));
                    $builder->add($value2['attrId'], FileType::class, $fieldProp);
                    break;
                case 'imageupload':
                    $fieldProp['attr']['class'] = "";
                    $fieldProp['attr']['data-value'] = $this->editData[0][$attr];
                    $fieldProp['attr']['data-changedVal'] = $fieldProp['data'];
                    unset($fieldProp['data']);
                    $fieldProp['constraints'][] = new File(array('maxSize' => $maxFileSize, 'mimeTypes' => $imageMimeTypes, 'maxSizeMessage' => $maxSizeMessage, 'mimeTypesMessage' => $mimeTypesMessage,'virusDetectmessage' =>$virusDetectmessage));
                    $builder->add($value2['attrId'], FileType::class, $fieldProp);
                    break;
                case 'number':
                    $fieldProp['attr']['class'].= " numbermask";
                    // $fieldProp['precision'] = 2;
                    //$fieldProp['constraints'][] = new Range(array('min' => 0, 'invalidMessage' => $invalidNumberMessage));
                    $builder->add($value2['attrId'], TextType::class, $fieldProp);
                    break;
                case 'hidden':
                    $fieldProp['constraints'][] = new Length(array('max' => $singleLineTextMaxLength, 'maxMessage' => $singleLineTextMaxLength));
                    $builder->add($value2['attrId'], HiddenType::class, $fieldProp);
                    break;
                default:
                    $fieldProp['constraints'][] = new Length(array('max' => $singleLineTextMaxLength, 'maxMessage' => $singleLineTextMaxLength));
                    $builder->add($value2['attrId'], TextType::class, $fieldProp);
                    break;
            }
            //since profile picture,community picture and company logo got removed order changed
            if ($value2['catId'] == $categoryCompany && $count == 1) {
                //main contact option selection for company
                $choice = ($this->editData[0]['is_deleted'] == '1') ? array('noMain' => 'NO_MAIN_CONTACT', 'withMain' => 'WITH_MAIN_CONTACT'):array('noMain' => 'NO_MAIN_CONTACT', 'withMain' => 'WITH_MAIN_CONTACT', 'existing' => 'EXISTING_MAIN_CONTACT');
                $value = $this->getMainContactType();
                $builder->add('mainContact', ChoiceType::class, array('label' => ' ', 'choices' => array_flip($choice), 'expanded' => true, 'data' => $value, 'attr' => array('class' => 'radio-list', 'data-fieldType' => 'text', 'data-attrId' => 'mainContact', 'data-required' => 'exclude')));
            }
        }
    }

    /**
     * Check a field is required or not
     *
     * @param string  $requiredType           Type
     * @param array   $requiredMemberships    Membership
     * @param array   $fedMemberships         Feds
     * @param boolean $isrequiredforfedMember Fed member
     * @param array   $selectedMembership     Selected membership
     * @param array   $clubType               ClubType
     *
     * @return boolean
     */
    public function isRequired($requiredType, $requiredMemberships, $fedMemberships, $isrequiredforfedMember, $selectedMembership = '', $clubType)
    {
        $required = false;
        switch ($requiredType) {
            case 'not_required':
                break;
            case 'all_contacts':
                $required = true;
                break;
            case 'all_fed_members':
                if ($selectedMembership['fed']!='' && $selectedMembership['fed']!='default') {
                    $required = true;
                }
                break;
            case 'all_club_members':
                if ($selectedMembership['club']!='' && $selectedMembership['club']!='default') {
                    $required = true;
                }
                break;
            case 'selected_members':
                if (in_array($selectedMembership['fed'], $requiredMemberships)||in_array($selectedMembership['club'], $requiredMemberships)) {
                    $required = true;
                }
                break;
        }
        /* if federaion membership is selected and the particular field is mandatory for federation member */
        if ($isrequiredforfedMember == 1) {
            if ($selectedMembership['fed']!='' && $selectedMembership['fed']!='default') {
                $required = true;
            }
        }
        if (($requiredType == 'not_required') && ($clubType == 'federation')) {
            $required = false;
        }

        return $required;
    }

    private function getMainContactType()
    {
        $categoryCompany = $this->containerParameters->get('system_category_company');
        $value = 'withMain';
        if ($this->editData) {
            if ($this->editData[0]['has_main_contact'] == 0) {
                $value = 'noMain';
            } elseif (empty($this->editData[0]['comp_def_contact'])) {
                $value = 'withMain';
            } else {
                $value = 'existing';
            }
        }
        if ($this->submittedData) {
            $value = ($this->submittedData[$categoryCompany]['mainContact']) ? $this->submittedData[$categoryCompany]['mainContact'] : $value;
        }

        return $value;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {

        return 'fg_field';
    }
    
    /**
     * @param OptionsResolver $resolver resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'custom_value' => null,
        ));
    }
}
