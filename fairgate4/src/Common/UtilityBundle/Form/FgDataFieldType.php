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
use Symfony\Component\Intl\Intl;
use Common\UtilityBundle\Util\FgSettings;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

/**
 * FgDataFieldType
 *
 * This FgDataFieldType was created for handling form
 *
 * @package    CommonUtilityBundle
 * @subpackage Form
 */
class FgDataFieldType extends AbstractType
{
    public $globalParameters;
    public $isIntranet;
    public $contactChanges;
    private $container;

    /**
     * Constructor
     *
     * @param array $formArray        Form array
     * @param array $submittedvalue   Submitted value
     * @param array $existingData     Existing values
     * @param array $globalParameters Global values
     * @param array $catgoryId        Cat id
     * @param bool  $isIntranet       Whether form is used in intranet or not
     * @param array $contactChanges   Changes to be confirmed
     */
    public function __construct($formArray, $submittedvalue, $existingData, $globalParameters, $catgoryId, $container, $isIntranet, $contactChanges)
    {
        $this->formArray = $formArray;
        $this->submittedvalue = $submittedvalue;
        $this->existingData = $existingData;
        $this->globalParameters = $globalParameters;
        $this->catgoryId = $catgoryId;
        $this->isIntranet = $isIntranet;
        $this->contactChanges = $contactChanges;
        $this->container = $container;
    }

    /**
     * Method to set custom values as globals
     *
     * @param array $customValues array of sutom values to set
     */
    private function setCustomValues($customValues) {
        $this->formArray = $customValues['formArray'];
        $this->submittedvalue = $customValues['submittedvalue'];
        $this->existingData = $customValues['existingData'];
        $this->globalParameters = $customValues['globalParameters'];
        $this->catgoryId = $customValues['catgoryId'];
        $this->isIntranet = $customValues['isIntranet'];
        $this->contactChanges = $customValues['contactChanges'];
        $this->container = $customValues['container'];
    }

    /**
     * Area to build form
     *
     * @param FormBuilderInterface $builder Builder interface
     * @param array                $options Options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->setCustomValues($options['custom_value']);
        $maxLengthSingleLineText = $this->globalParameters->get('singleline_max_length');
        $maxLengthMultiLineText = $this->globalParameters->get('multiline_max_length');
        $maxFlSz = $this->globalParameters->get('max_file_size');
        $mimeTypeImages = $this->globalParameters->get('image_mime_types');
        $mimeTypeUnlimited = $this->globalParameters->get('unlimited_mime_types');

        $personalId = $this->globalParameters->get('system_category_personal');
        $correspondanceId = $this->globalParameters->get('system_category_address');
        $invoiceId = $this->globalParameters->get('system_category_invoice');
        $companyId = $this->globalParameters->get('system_category_company');

        $nationality1 = $this->globalParameters->get('system_field_nationality1');
        $nationality2 = $this->globalParameters->get('system_field_nationality2');
        $corresLang = $this->globalParameters->get('system_field_corress_lang');

        $corresLand = $this->globalParameters->get('system_field_corres_land');
        $invoiceLand = $this->globalParameters->get('system_field_invoice_land');

        $teamPicture = $this->globalParameters->get('system_field_team_picture');
        $communityPicture = $this->globalParameters->get('system_field_communitypicture');
        $companyLogo = $this->globalParameters->get('system_field_companylogo');
        $gender = $this->globalParameters->get('system_field_gender');
        $salutation = $this->globalParameters->get('system_field_salutaion');

        $msgMaxSize = 'SIZE_EXCEED';
        $msgMimeType = 'NOT_VALID_TYPE';
        $reqMessage = 'REQUIRED';
        $invalidemailMsg = 'INVALID_EMAIL';
        $invalidUrl = 'INVALID_URL';
        $emailExMsg = 'EMAIL_EXIST';
        $maxmultilineLnMsg = 'LENGHT_EXCEED';
        $invalidnummsg = 'INVALID_NUMBER';
        $selectDefault = 'SELECT_DEFAULT';
        $formArray = $this->formArray;
        $atrTle = $formArray['attrTitles'];
        $sysLang = $formArray['clubIdArray']['sysLang'];
        $defSysLang = $formArray['clubIdArray']['defSysLang'];
        $contactId = $formArray['contactId'];
        $catgoryId = $this->catgoryId;
        $count = 0;
        $session = $this->container->get('session');
        $parentContactId = $session->get('parentId', 0);

        foreach ($formArray['values'] as $atrId => $val) {
            if ($atrId == $teamPicture) {
                continue;
            }
            $reqType = $fieldValue = $dataAttrVal = '';
            $required = false;
            if ($this->existingData) {
                $attr = $val['attrId'];
                $fieldValue = $dataAttrVal = $dataOriginalVal = $this->existingData[0][$attr];
            }
            if ($this->submittedvalue) {
                $fieldValue = $this->submittedvalue[$val['catId']][$val['attrId']];
            }

            $count++;
            if ($val['isSystemField'] || $val['isFairgateField'] || in_array($val['attrId'], array($communityPicture,$companyLogo)) ) {
                $fldTitle = (isset($atrTle[$val['attrId']][$defSysLang])) ? $atrTle[$val['attrId']][$defSysLang] : $val['fieldname'];
            } else {
                $fldTitle = (isset($atrTle[$val['attrId']][$sysLang])) ? $atrTle[$val['attrId']][$sysLang] : $val['fieldname'];
            }

            if ($val['catId'] == $personalId && $count == 1 && $formArray['fieldType'] == 'Company') {
                //main contact autocomplete and function
                $mcName = array('label' => 'MAIN_CONTACT', 'attr' => array('class' => "form-control hide", 'data-fieldType' => 'company', 'data-attrId' => 'mainContactName'));
                $mcFunction = array('label' => 'FUNCTION', 'attr' => array('class' => "form-control", 'data-fieldType' => 'company', 'data-attrId' => 'mainContactFunction'));
                if ($this->existingData) {
                    $mcName['data'] = $this->existingData[0]['mainContactName'];
                    $mcFunction['data'] = $this->existingData[0]['mainContactFunction'];
                }
                if ($this->getMainContactType() == 'existing') {
                    $mcFunction['constraints'][] = $mcName['constraints'][] = new NotBlank(array('message' => $reqMessage));
                }
                $builder->add('mainContactName', TextType::class, $mcName);
                $builder->add('mainContactFunction', TextType::class, $mcFunction);
            }
            if ($val['isCompany'] && $val['isPersonal']) {
                $fieldType = 'both';
            } else {
                $fieldType = ($val['isCompany']) ? 'company' : 'personal';
            }
            $isrequiredforfedMember = 0;
            $isReqType = (isset($val['isRequiredType'])) ? $val['isRequiredType'] : 'not_required';
            $val['memberships'] = (is_array($val['memberships']))? $val['memberships']:array_unique(explode(',', $val['memberships']));
            $reqMembers = ($isReqType == 'selected_members') ? implode(':', $val['memberships']) : '';
            if (($val['isRequiredFedmemberClub'] == 1 && (($formArray['clubIdArray']['clubType'] != 'sub_federation') && ($formArray['clubIdArray']['clubType'] != 'federation'))) || ($val['isRequiredFedmemberSubfed'] == 1 && $formArray['clubIdArray']['clubType'] == 'sub_federation')) {
                if ($isReqType == 'not_required' && ($formArray['clubIdArray']['clubType'] != 'federation' )) {
                    $reqType = 'FRD';
                    $isReqType = ($isReqType == 'not_required') ? 'selected_members' : $isReqType;
                    $isrequiredforfedMember = 1;
                }
            }
            $required = $this->isRequired($val['isRequiredType'], $val['memberships'], $formArray['fedMemberships'], $isrequiredforfedMember, $formArray['selectedMembership'], $formArray['clubIdArray']['clubType']);
            /** FAIR-2008 - Primary e-mail as mandatory field for own contact in internal area **/

            if($val['inputType'] == 'login email' && $this->isIntranet && ($parentContactId==0 || ($parentContactId > 0 && $parentContactId == $contactId) )){
                $required = true;
            }
            $attrFldProp['required'] = $required;
            /** FAIR-2008 - Primary e-mail as mandatory field for own contact in internal area **/
            $attrFldProp = array('label' => $fldTitle, 'required' => $required);
            if ($this->submittedvalue[$correspondanceId]['same_invoice_address'] && $val['catId'] == $invoiceId && !empty($val['addressId'])) {
                $required = false;
            }
            if ($fieldValue != '') {
                $attrFldProp['data'] = $fieldValue;
            }
            if (isset($this->contactChanges[$val['attrId']])) {
                $attrFldProp['data'] = $dataAttrVal = $this->contactChanges[$val['attrId']]['value'];
            }
            if ($val['inputType'] == 'date') {
                if ($fieldValue == "0000-00-00 00:00:00" || $fieldValue == "0000-00-00" || $fieldValue == "" ) {
                    $dataOriginalVal = '';
                } else if(date_create_from_format('Y-m-d', $fieldValue)){
                    $date = new \DateTime();
                    $dataOriginalVal = $date->createFromFormat('Y-m-d', $fieldValue)->format(FgSettings::getPhpDateFormat());
                } else if(date_create_from_format('d.m.Y', $fieldValue)){
                    $date = new \DateTime();
                    $dataOriginalVal = $date->createFromFormat('d.m.Y', $fieldValue)->format(FgSettings::getPhpDateFormat());
                } else {
                    $date = new \DateTime();
                    $dataOriginalVal = $date->createFromFormat(FgSettings::getPhpDateFormat(), $fieldValue)->format(FgSettings::getPhpDateFormat());
                }

                if ($dataAttrVal == "0000-00-00 00:00:00" || $dataAttrVal == "0000-00-00" || $dataAttrVal == ""  ) {
                    $dataAttrVal = '';
                } else if(date_create_from_format('Y-m-d', $dataAttrVal)) {
                    $date = new \DateTime();
                    $dataAttrVal = $date->createFromFormat('Y-m-d', $dataAttrVal)->format(FgSettings::getPhpDateFormat());
                } else if(date_create_from_format('d.m.Y', $dataAttrVal)) {
                    $date = new \DateTime();
                    $dataAttrVal = $date->createFromFormat('d.m.Y', $dataAttrVal)->format(FgSettings::getPhpDateFormat());
                } else {
                    $date = new \DateTime();
                    $dataAttrVal = $date->createFromFormat(FgSettings::getPhpDateFormat(), $dataAttrVal)->format(FgSettings::getPhpDateFormat());
                }
            }
            if (($val['attrId'] == $gender) || ($val['attrId'] == $salutation)) {
                $attrTranslateArray = ($val['attrId'] == $gender) ? array('Male'=>'CM_MALE','Female'=>'CM_FEMALE') : array('Formal'=>'CM_FORMAL','Informal'=>'CM_INFORMAL');
                $dataOriginalVal = $attrTranslateArray[$dataOriginalVal];
                $dataAttrVal = $attrTranslateArray[$dataAttrVal];
            }
            $attrFldProp['attr'] = array('class' => 'form-control', 'data-fieldType' => $fieldType, 'data-required' => $isReqType, 'data-attrId' => $val['attrId'], 'data-members' => $reqMembers, 'data-reqType' => $reqType, 'data-originalVal' => (is_array($dataOriginalVal) ? implode(';',$dataOriginalVal) : $dataOriginalVal), 'data-attrVal' => $dataAttrVal);
            if ($this->isIntranet) {
                if (($val['isChangableUser'] == '0') || ($val['isChangableUser'] == 0)) {
                    $attrFldProp['attr']['readonly'] = 'true';
                    $required = false;
                }
                if (isset($this->contactChanges[$val['attrId']])) {
                    if ($this->contactChanges[$val['attrId']]['changedBy'] != $contactId) {
                        $attrFldProp['attr']['readonly'] = 'true';
                        $required = false;
                    }
                }
                if (($val['isConfirmContact'] == '1') || ($val['isConfirmContact'] == 1)) {
                    $attrFldProp['attr']['data-key'] = $val['catId'] . '.' . $val['attrId'];
                    if ($isReqType == 'not_required') {
                        $attrFldProp['attr']['data-notrequired'] = 'true';
                    }
                    if ($this->submittedvalue) {
                        if ($dataAttrVal != $this->submittedvalue[$val['catId']][$val['attrId']]) {
                            $attrFldProp['attr']['class'] .= " fairgatedirty";
                        }
                    }
                }
            }
            if ($val['catId'] == $invoiceId || $val['catId'] == $correspondanceId) {
                $attrFldProp['attr']['data-addressType'] = $val['addressType'];
                $attrFldProp['attr']['data-addressId'] = $val['addressId'];
            }
            if (($formArray['fieldType'] == 'Company') && $val['catId'] == $personalId) {
                if ($this->getMainContactType() == 'withMain' && $required) {
                    $attrFldProp['constraints'][] = new NotBlank(array('message' => $reqMessage));
                }
            } elseif ($required) {
                //to handle the image required error
                if ($val['inputType'] == 'imageupload' || $val['inputType'] == 'fileupload') {
                    if ($fieldValue == "") {
                        if ($this->existingData[0][$attr] != "") {
                            if ($val['attrId'] == $communityPicture || $val['attrId'] == $companyLogo) {
                                $deleteddragFiles = explode(',', $formArray['deleteddragFiles']);
                                if (in_array($val['attrId'], $deleteddragFiles)) {
                                    $attrFldProp['constraints'][] = new NotBlank(array('message' => $reqMessage));
                                }
                            } else {
                                $deleted = explode(',', $formArray['deletedFiles']);
                                if (in_array($val['attrId'], $deleted)) {
                                    $attrFldProp['constraints'][] = new NotBlank(array('message' => $reqMessage));
                                }
                            }
                        } else {
                            if ($val['attrId'] == $communityPicture || $val['attrId'] == $companyLogo) {
                                if (count($formArray['dragFiles']) == 0) {
                                    $attrFldProp['constraints'][] = new NotBlank(array('message' => $reqMessage));
                                }
                            } else {
                                $attrFldProp['constraints'][] = new NotBlank(array('message' => $reqMessage));
                            }
                        }
                    }
                    //ends
                } else {
                    $attrFldProp['constraints'][] = new NotBlank(array('message' => $reqMessage));
                }
            }
            switch ($val['inputType']) {
                case 'multiline':
                    $builder->add($val['attrId'], TextareaType::class, $attrFldProp);
                    $attrFldProp['constraints'][] = new Length(array('max' => $maxLengthMultiLineText, 'maxMessage' => $maxmultilineLnMsg));
                    if ($this->isIntranet) {
                        $attrFldProp['attr']['maxlength'] = $maxLengthMultiLineText;
                    }

                    break;
                case 'date':
                    $attrFldProp['widget'] = "single_text";
                    $attrFldProp['input'] = 'datetime';
                    if ($attrFldProp['data']) {
                        if ($attrFldProp['data'] == "0000-00-00 00:00:00" || $attrFldProp['data'] == "0000-00-00" ) {
                            unset($attrFldProp['data']);
                        } else if(date_create_from_format('Y-m-d', $attrFldProp['data'])){
                            $date = new \DateTime();
                            $attrFldProp['data'] = $date->createFromFormat('Y-m-d', $attrFldProp['data']);
                        } else if(date_create_from_format('d.m.Y', $attrFldProp['data'])){
                            $date = new \DateTime();
                            $attrFldProp['data'] = $date->createFromFormat('d.m.Y', $attrFldProp['data']);
                        } else {
                            $date = new \DateTime();
                            $attrFldProp['data'] = $date->createFromFormat(FgSettings::getPhpDateFormat(), $attrFldProp['data']);
                        }
                    } else {
                        unset($attrFldProp['data']);
                    }
                    $attrFldProp['format'] = FgSettings::getSymfonyDateFormat();;
                    $attrFldProp['attr']['isDate'] = "1";
                    $attrFldProp['attr']['class'].=" datemask";
                    $builder->add($val['attrId'], DateType::class, $attrFldProp);
                    break;
                case 'login email':
                    $required = $this->isRequired($val['isRequiredType'], $val['memberships'], $formArray['fedMemberships'], $isrequiredforfedMember, $formArray['selectedMembership'], $formArray['clubIdArray']['clubType']);
                    $hasFedMembership = empty($formArray['selectedMembership']['fed']) ? false :true;
                    $attrFldProp['constraints'][] = new Email(array('message' => $invalidemailMsg));
                    $attrFldProp['constraints'][] = new FgEmailExists(array('contactId' => $contactId,'typeOfContact'=>$this->contactChanges['typeOfContact'], 'hasFedMembership' => $hasFedMembership, 'emailExistsMessage' => $emailExMsg));
                    $builder->add($val['attrId'], EmailType::class, $attrFldProp);
                    break;
                case 'email':
                    $attrFldProp['constraints'][] = new Email(array('message' => $invalidemailMsg));
                    $builder->add($val['attrId'], EmailType::class, $attrFldProp);
                    break;
                case 'url':
                    $attrFldProp['attr']['class'].= " fg-urlmask";
                    $attrFldProp['constraints'][] = new Url(array('message' => $invalidUrl));
                    $builder->add($val['attrId'], TextType::class, $attrFldProp);
                    break;
                case 'radio':
                    if (is_array($val['predefinedValue'])) {
                        $values = $val['predefinedValue'];
                    } else {
                        $value = explode(';', $val['predefinedValue']);
                        $values = array_combine($value, $value);
                    }
                    if($required==false) {
                        $attrFldProp['placeholder'] = 'NONE';
                    }
                    $attrFldProp['choices'] = $values;
                    $attrFldProp['expanded'] = true;
                    if ($this->existingData[1] && $val['attrId'] == 'contactType') {
                        $attrFldProp['disabled'] = true;
                    }
                    $attrFldProp['attr']['class'] = "radio-list";

                    $builder->add($val['attrId'], ChoiceType::class, $attrFldProp);
                    break;
                case 'checkbox':
                    if (is_array($val['predefinedValue'])) {
                        $values = $val['predefinedValue'];
                    } else {
                        $value = explode(';', $val['predefinedValue']);
                        $values = array_combine($value, $value);
                    }
                    if (empty($attrFldProp['data'])) {
                        unset($attrFldProp['data']);
                    } else if (!is_array($attrFldProp['data'])) {
                        $attrFldProp['data'] = explode(';', $attrFldProp['data']);
                    }
                    $attrFldProp['attr']['class'] = "radio-list";
                    $attrFldProp['choices'] = $values;
                    $attrFldProp['expanded'] = true;
                    $attrFldProp['multiple'] = true;

                    $builder->add($val['attrId'], ChoiceType::class, $attrFldProp);
                    break;
                case 'select':
                    if ($val['attrId'] == $nationality1 || $val['attrId'] == $nationality2 || $val['attrId'] == $corresLand || $val['attrId'] == $invoiceLand) {
                        $attrFldProp['attr']['data-live-search'] = "true";
                        $attrFldProp['attr']['data-local-storage'] = "countylist";
                        if ($this->isIntranet) {
                            $attrFldProp['attr']['class'] .= " select2 fg-select-with-search";
                        } else {
                            $attrFldProp['attr']['class'] .= " select2 fg-select-with-search";
                        }
                        if($attrFldProp['data']) {
                            $countryList = Intl::getRegionBundle()->getCountryNames();
                            $attrFldProp['choices'] = array($attrFldProp['data'] => $countryList[$attrFldProp['data']]);
                        }
                        $attrFldProp['placeholder'] = $selectDefault;
                        if ($attrFldProp['attr']['data-originalVal'] != '') {
                            $attrFldProp['attr']['data-originalValue'] = is_array($attrFldProp['choices'][$attrFldProp['attr']['data-originalVal']]) ? implode(';', $attrFldProp['choices'][$attrFldProp['attr']['data-originalVal']]) : $attrFldProp['choices'][$attrFldProp['attr']['data-originalVal']];
                        }
                        $attrFldProp['choices'] = array_flip($attrFldProp['choices']);

                        $builder->add($val['attrId'], ChoiceType::class, $attrFldProp);

                    } else {
                        $selectClass = " bs-select";
                        if (isset($val['membershipCats'])) {
                            $values = $val['membershipCats'];
                        } elseif ($val['attrId'] == $corresLang) { //handle Korrespondenzsprache
                            $values = $formArray['fullLanguageName'];
                            if(count($values)==1){
                                continue;
                            }
                            if ($this->isIntranet) {
                                $selectClass = " select2";
                            }
                        } elseif ($val['attrId'] == $gender || $salutation==$val['attrId']) {
                            $values= ($val['attrId'] == $gender) ? array('Male'=>'CM_MALE','Female'=>'CM_FEMALE'):array('Formal'=>'CM_FORMAL','Informal'=>'CM_INFORMAL');
                        } else {
                            $value = explode(';', $val['predefinedValue']);
                            $values = array_combine($value, $value);
                            if ($this->isIntranet) {
                                $selectClass = " select2";
                            }
                        }
                        $attrFldProp['attr']['class'] .= $selectClass;
                        $attrFldProp['choices'] = array_flip($values);
                        $attrFldProp['placeholder'] = $selectDefault;

                        $builder->add($val['attrId'], ChoiceType::class, $attrFldProp);

                    }
                    break;
                case 'fileupload':
                    $attrFldProp['attr']['class'] = "";
                    $attrFldProp['attr']['data-value'] = $this->existingData[0][$attr];
                    $attrFldProp['attr']['data-changedVal'] = $attrFldProp['data'];
                    unset($attrFldProp['data']);
                    $attrFldProp['constraints'][] = new File(array('maxSize' => $maxFlSz, 'mimeTypes' => $mimeTypeUnlimited, 'maxSizeMessage' => $msgMaxSize, 'mimeTypesMessage' => $msgMimeType));

                    $builder->add($val['attrId'], FileType::class, $attrFldProp);
                    break;
                case 'imageupload':
                    $attrFldProp['attr']['class'] = "";
                    $attrFldProp['attr']['data-value'] = $this->existingData[0][$attr];
                    $attrFldProp['attr']['data-changedVal'] = $attrFldProp['data'];
                    unset($attrFldProp['data']);
                    $attrFldProp['constraints'][] = new File(array('maxSize' => $maxFlSz, 'mimeTypes' => $mimeTypeImages, 'maxSizeMessage' => $msgMaxSize, 'mimeTypesMessage' => $msgMimeType));
                    $builder->add($val['attrId'], FileType::class, $attrFldProp);
                    break;
                case 'number':
                    $attrFldProp['attr']['class'].= " numbermask";
                   // $attrFldProp['precision'] = 2;
                    //$attrFldProp['constraints'][] = new Range(array('min' => 0, 'invalidMessage' => $invalidnummsg));
                    $builder->add($val['attrId'], TextType::class, $attrFldProp);
                    break;
                default:
                    $attrFldProp['constraints'][] = new Length(array('max' => $maxLengthSingleLineText, 'maxMessage' => $maxLengthSingleLineText));
                    if ($this->isIntranet) {
                        $attrFldProp['attr']['maxlength'] = $maxLengthSingleLineText;
                    }
                    $builder->add($val['attrId'], TextType::class, $attrFldProp);
                    break;
            }
            if ($val['catId'] == $companyId && $count == 1) {
                //main contact option selection for company
                $choice = ($this->existingData[0]['is_deleted'] == '1') ? array('noMain' => 'NO_MAIN_CONTACT', 'withMain' => 'WITH_MAIN_CONTACT'):array('noMain' => 'NO_MAIN_CONTACT', 'withMain' => 'WITH_MAIN_CONTACT', 'existing' => 'EXISTING_SINGLE_MAIN_CONTACT');
                $value = $this->getMainContactType();
                $builder->add('mainContact', ChoiceType::class, array('label' => ' ', 'choices' => array_flip($choice), 'expanded' => true, 'data' => $value, 'attr' => array('class' => 'radio-list', 'data-fieldType' => 'text', 'data-attrId' => 'mainContact', 'data-required' => 'exclude')));
            }
        }
    }

    /**
     * Check a field is required or not
     *
     * @param string  $reqType                Type
     * @param array   $requiredMemberships    Requires membership array
     * @param array   $fedMemberships         Fed memberships
     * @param boolean $isrequiredforfedMember Fed member
     * @param array   $selectedMembership     Selected membership
     * @param String  $clubType               Club type
     *
     * @return boolean
     */

    public function isRequired($reqType, $requiredMemberships, $fedMemberships, $isrequiredforfedMember, $selectedMembership = '', $clubType = '')
    {
        $isReq = false;
        switch ($reqType) {
            case 'not_required':
                break;
            case 'all_contacts':
                $isReq = true;
                break;
            case 'all_fed_members':
                if ($selectedMembership['fed']!='' && $selectedMembership['fed']!='default') {
                    $isReq = true;
                }
                break;
            case 'all_club_members':
                if ($selectedMembership['club']!='' && $selectedMembership['club']!='default') {
                    $isReq = true;
                }
                break;
            case 'selected_members':
                $requiredMemberships = is_array($requiredMemberships) ? $requiredMemberships : explode(',', $requiredMemberships);
                if (in_array($selectedMembership['fed'], $requiredMemberships)||in_array($selectedMembership['club'], $requiredMemberships)) {
                    $isReq = true;
                }
                break;
        }
        /* if federaion membership is selected and the particular field is mandatory for federation member */
        if ($isrequiredforfedMember == 1) {
            if ($selectedMembership['fed']!='') {
                $isReq = true;
            }
        }
        if (($reqType == 'not_required') && ($clubType == 'federation')) {
            $isReq = false;
        }

        return $isReq;
    }

    /**
     * function to get the type of main contact
     * @return string
     */
    private function getMainContactType()
    {
        $companyId = $this->globalParameters->get('system_category_company');
        $mainContactval = 'withMain';
        if ($this->existingData) {
            if ($this->existingData[0]['has_main_contact'] == 0) {
                $mainContactval = 'noMain';
            } elseif (empty($this->existingData[0]['comp_def_contact'])) {
                $mainContactval = 'withMain';
            } else {
                $mainContactval = 'existing';
            }
        }
        if ($this->submittedvalue) {
            $mainContactval = ($this->submittedvalue[$companyId]['mainContact']) ? $this->submittedvalue[$companyId]['mainContact'] : $mainContactval;
        }

        return $mainContactval;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'fg_field';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'custom_value' => null,
            'allow_extra_fields' =>true,
        ));
    }

}
