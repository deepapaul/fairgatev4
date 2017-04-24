<?php

namespace Common\UtilityBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * FgFieldCategoryType
 *
 * This FgFieldCategoryType was created for handling form
 *
 * @package    CommonUtilityBundle
 * @subpackage Form
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
class FgFieldCategoryType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public $submittedData;
    public $editData;
    public $containerParameters;
    /*
     * @param string $module             contact/sponsor
     */
    public $module;
    public $isIntranet;
    public $contactChanges;
    public $container;

    /**
     * Constructor
     *
     * @param array  $submittedData       Submitted value
     * @param array  $editData            Existing values
     * @param array  $containerParameters Container params
     * @param array  $bookedModuleDetails //array('sponsor','invoice','communication','frontend1','frontend2')
     * @param int    $federationId        Federation Id
     * @param int    $subfederationId     Sub-Federation Id
     * @param string $module              contact/sponsor
     * @param bool   $isIntranet          Whether form is used in intranet or not
     * @param array  $contactChanges      Changes to be confirmed
     */
    public function __construct($submittedData, $editData, $container, $bookedModuleDetails, $federationId, $subfederationId, $module = 'contact', $isIntranet = false, $contactChanges = array())
    {        
        $this->submittedData = $submittedData;
        $this->editData = $editData;
        $this->container = $container;
        $this->bookedModuleDetails = $bookedModuleDetails;
        $this->federationId = $federationId;
        $this->subfederationId = $subfederationId;
        $this->module = $module;
        $this->isIntranet = $isIntranet;
        $this->contactChanges = $contactChanges;
    }
    
    /**
     * Method to set custom values as globals
     * 
     * @param array $customValues array of sutom values to set
     */
    private function setCustomValues($customValues) {
        $this->submittedData = $customValues['submittedData'];        
        $this->editData = $customValues['editData'];
        $this->container = $customValues['container'];
        $this->containerParameters = $this->container->getParameterBag();
        $this->bookedModuleDetails = $customValues['bookedModuleDetails'];   
        $this->federationId = $customValues['federationId'];   
        $this->subfederationId = $customValues['subfederationId'];   
        $this->module = isset($customValues['module']) ? $customValues['module'] : 'contact';   
        $this->isIntranet = ($customValues['isIntranet']) ? $customValues['isIntranet'] : false;   
        $this->contactChanges = isset($customValues['contactChanges']) ? $customValues['contactChanges'] : array(); 
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
        $currContactId = $options['data']['currContactId'];
        $sysLang = $options['data']['clubIdArray']['sysLang'];
        $defSysLang = $options['data']['clubIdArray']['defSysLang'];
        $details = $this->getSystemFields($options['data']);
        $systemData[0] = $this->setSystemData($this->editData);
        $systemData[1] = ($this->editData) ? true : false;
        if ($this->submittedData) {
            $this->contactChanges['typeOfContact'] = $this->submittedData['system']['contactType'];
        } elseif ($this->editData){
            $this->contactChanges['typeOfContact'] = $this->editData[0]['is_company']=='1'? 'Company':'Single person';
        }                                                                                                                                                               
        $builder->add('system', \Common\UtilityBundle\Form\FgFieldType::class, array('label' => 'SYSTEM', 'attr' => array('data-catId' => '0'), 'custom_value' => array('fieldsArray' => $details, 'submittedData' => $this->submittedData, 'editData' => $systemData, 'containerParameters' => $this->containerParameters, 'isIntranet' => $this->isIntranet, 'contactChanges' => $this->contactChanges, 'currContactId' => $currContactId) ));
        foreach ($options['data']['fieldsArray'] as $value) {
            if (count($value['values']) > 0) {
                if ($value['isSystem'] == 1 || $value['isFairgate'] == 1) {
                    $catLabel = (isset($value['titles'][$defSysLang])) ? $value['titles'][$defSysLang] : $value['title'];
                } else {
                    $catLabel = (isset($value['titles'][$sysLang])) ? $value['titles'][$sysLang] : $value['title'];
                }
                $personalCategory = $this->containerParameters->get('system_category_personal');
                if ($options['data']['fieldType'] == 'Company' && $value['catId'] == $personalCategory) {
                    $catLabel = 'CM_MAIN_CONTACT';
                }
                $details['values'] = $value['values'];
                $details['deletedFiles'] = $options['data']['deletedFiles'];
                $attr['data-catId'] = $value['catId'];
                $correspondanceCategory = $this->containerParameters->get('system_category_address');
                if ($value['catId'] == $correspondanceCategory) {
                    //set same as value
                    if ($this->submittedData) {
                        $attr['data-same'] = $this->submittedData[$correspondanceCategory]['same_invoice_address'] ? 1:0;
                    } elseif ($this->isIntranet){
                        $attr['data-same'] = '0';
                    }
                    elseif ($this->editData){
                        $attr['data-same'] = $this->editData[0]['same_invoice_address'];
                    }
                }
                if ($value['catId'] == $personalCategory) {
                    //set Put main contact's name in the address block
                    if ($this->submittedData) {
                        $attr['data-hasMC'] = $this->submittedData[$personalCategory]['has_main_contact_address'] ? 1:0;
                    } else if ($this->editData) {
                        $attr['data-hasMC'] = $this->editData[0]['has_main_contact_address'];
                    }
                }

                if (($value['catClubId'] ==  $this->federationId) || ($value['catClubId'] == $this->subfederationId)) {

                     $attr['data-hasFedImage'] = $value['catClubId'];

                } else {
                     $attr['data-hasFedImage'] = '';
                }                                                                                                                                                        
                $builder->add($value['catId'], \Common\UtilityBundle\Form\FgFieldType::class, array('label' => $catLabel, 'attr' => $attr, 'custom_value' => array('fieldsArray' => $details, 'submittedData' => $this->submittedData, 'editData' => $this->editData, 'containerParameters' => $this->containerParameters, 'isIntranet' => $this->isIntranet, 'contactChanges' => $this->contactChanges, 'currContactId' => $currContactId)));
            }

        }

    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'fg_field_category';
    }

    /**
     * To set system data
     *
     * @param Array $editData Edit value
     *
     * @return boolean
     */
    private function setSystemData($editData)
    {
        if ($editData) {
            //set contact type
            $editValue['contactType'] = ($editData[0]['is_company'] == 1) ? 'Company' : 'Single person';
            $editValue['switchable']=$editData['switchable'];
            if($this->module != 'sponsor') {
                //set attribute
                $attribute = array();
                if ($editData[0]['intranet_access'] != '0') {
                    array_push($attribute, 'Intranet access');
                }
                if ($editData[0]['is_stealth_mode'] != '0') {
                    array_push($attribute, 'Stealth mode');
                }                
                $editValue['attribute'] = implode(';', $attribute);                
            }
            $editValue['membership'] = $editData[0]['club_membership_cat_id'];
            $editValue['fedMembership'] = $editData[0]['fed_membership_cat_id'];
            $editValue['teamfunctions'] = $editData[0]['teamfunctions'];
            $editValue['savedFunctions'] = $editData[0]['savedFunctions'];
            $editValue['is_fed_membership_confirmed'] = ($editData[0]['is_fed_membership_confirmed'] == true) ? true:false ;
            $editValue['notOwnContact'] = (($this->container->get('club')->get('type')=='sub_federation' || $this->container->get('club')->get('type')=='federation') && $editData[0]['main_club_id'] != $this->container->get('club')->get('id')) ? true:false;
        } else {
            $editValue['contactType'] = 'Single person';
            if($this->module != 'sponsor') {
                $editValue['attribute'] = 'Intranet access';
            }
        }

        return $editValue;
    }
    /**
     * function to get the system fields
     * @param array $options the options
     *
     * @return array
     */
    private function getSystemFields($options)
    {
        $details['attrTitles'] = $options['attrTitles'];
        $details['fedMemberships'] = $options['fedMemberships'];
        $federation= $options['clubIdArray']['clubType'] == "federation" ? $options['clubIdArray']['clubId'] : $options['clubIdArray']['federationId'];
        $details['selectedMembership'] = $options['selectedMembership'];
        $field[0] = array('inputType' => 'radio', 'attrId' => 'contactType', 'catId' => 'system', 'predefinedValue' => array('Single person' => 'SINGLE_PERSON_FIELD', 'Company' => 'COMPANY'), 'fieldname' => 'TYPE', 'data' => $options['fieldType'], 'isRequiredType' => 'all_contacts', 'isChangableTeamAdmin' => '1');
        if ($this->editData[0]['is_deleted'] != '1') {
            if($this->container->get('club')->get('clubMembershipAvailable') && $this->container->get('club')->get('type')!='sub_federation' && $this->container->get('club')->get('type')!='federation' ) {
                $memberships['club']=array_flip($options['memberships']['club']);
                $field[1] = array('inputType' => 'select', 'attrId' => 'membership', 'catId' => 'system', 'fieldname' => 'MEMBER_CATEGORY', 'membershipCats' => $memberships, 'isChangableTeamAdmin' => '0');
            }
            if($this->container->get('club')->get('fedMembershipMandatory')) {
                $isRequiredType = 'all_contacts';
                $fedMemberships=array_flip($options['memberships']['fed']);
            } else {
                $isRequiredType = 'not_required';
                $fedMemberships['fed']=array_flip($options['memberships']['fed']);
            }
            if($options['clubIdArray']['clubType'] != "standard_club"){
                $terminologyService = $this->container->get('fairgate_terminology_service');
                $fedMemLabel = $terminologyService->getTerminology('Fed membership', $this->container->getParameter('singular'));
                $field[2] = array('inputType' => 'select', 'attrId' => 'fedMembership', 'catId' => 'system', 'fieldname' => $fedMemLabel, 'membershipCats' => $fedMemberships, 'isChangableTeamAdmin' => '0','isRequiredType' => $isRequiredType);
            }
            
        }
        if($this->module != 'sponsor') {
            if (in_array('frontend1', $this->bookedModuleDetails)) {
                $field[3] = array('inputType' => 'checkbox', 'attrId' => 'attribute', 'catId' => 'system', 'predefinedValue' => array('Intranet access' => 'INTRANET_ACCESS', 'Stealth mode' => 'STEALTH_MODE'), 'fieldname' => 'ATTRIBUTE', 'isChangableTeamAdmin' => '0');
            }
        }
        if ($this->isIntranet) {
            $field[4] = array('inputType' => 'select', 'attrId' => 'teamfunctions', 'catId' => 'system', 'fieldname' => 'UPDATE_MEMBER_FUNCTION', 'teamFunctions' => $options['teamFunctions'], 'isChangableTeamAdmin' => '1', 'isRequiredType' => 'all_contacts');
        }

        $details['values'] = $field;
        $details['fieldType'] = $options['fieldType'];
        $details['clubIdArray'] = $options['clubIdArray'];
        $details['fullLanguageName'] = $options['fullLanguageName'];
        $details['contactId'] = $options['contactId'];

        return $details;
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
