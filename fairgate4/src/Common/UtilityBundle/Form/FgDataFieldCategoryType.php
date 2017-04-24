<?php

namespace Common\UtilityBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * FgDataFieldCategoryType
 *
 * This FgDataFieldCategoryType was created for handling form
 *
 * @package    CommonUtilityBundle
 * @subpackage Form
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
class FgDataFieldCategoryType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public $submittedValue;
    public $existingVal;
    public $globalParameters;
    public $bookedModulesDet;
    public $isIntranet;
    public $contactChanges;
    private $container;

    /**
     * Constructor
     *
     * @param array $submittedValue   Submitted value
     * @param array $existingVal      Existing values
     * @param array $globalParameters Global params
     * @param array $bookedModulesDet // array('sponsor','invoice','communication','frontend1','frontend2')
     * @param bool  $isIntranet       Whether form is used in intranet or not
     * @param array $contactChanges   Changes to be confirmed
     */
    public function __construct($submittedValue, $existingVal, $globalParameters, $bookedModulesDet, $container, $isIntranet = false, $contactChanges = array() )
    {
        $this->submittedValue = $submittedValue;
        $this->existingVal = $existingVal;
        $this->globalParameters = $globalParameters;
        $this->bookedModulesDet = $bookedModulesDet;
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
        $this->submittedValue = $customValues['dataformValues'];
        $this->existingVal = $customValues['existingData'];
        $this->globalParameters = $customValues['containerParameters'];
        $this->bookedModulesDet = $customValues['bookedModulesDet'];
        $this->isIntranet = (isset($customValues['isIntranet'])) ? $customValues['isIntranet'] : false;
        $this->contactChanges = (isset($customValues['contactChanges'])) ? $customValues['contactChanges'] : array();
        $this->container = $customValues['container'];;
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
        $sysLang = $options['data']['clubIdArray']['sysLang'];
        $defSysLang = $options['data']['clubIdArray']['defSysLang'];
        $datas = $this->getDefaultFields($options['data']);
        $companyCategory = $this->globalParameters->get('system_category_company');
        $personalCategory = $this->globalParameters->get('system_category_personal');
        $companyLogo = $this->globalParameters->get('system_field_companylogo');
        $options['data']= $this->addPictureCategory($options['data']);
        if ($this->submittedData) {
            $this->contactChanges['typeOfContact'] = $this->submittedData['system']['contactType'];
        } elseif ($this->editData){
            $this->contactChanges['typeOfContact'] = $this->editData[0]['is_company']=='1'? 'Company':'Single person';
        }
        foreach ($options['data']['fieldsArray'] as $value) {
            if (count($value['values']) > 0) {
                if ($value['isSystem'] == 1 || $value['isFairgate'] == 1) {
                    $catLabel = (isset($value['titles'][$defSysLang])) ? $value['titles'][$defSysLang] : $value['title'];
                } else {
                    $catLabel = (isset($value['titles'][$sysLang])) ? $value['titles'][$sysLang] : $value['title'];
                }
                if ($options['data']['fieldType'] == 'Company' && $value['catId'] == $personalCategory) {
                    $catLabel = 'CM_MAIN_CONTACT';
                }
                if ($value['catId'] == $companyCategory) {
                    $catLabel = 'Personal';
                }
                $datas['values'] = $value['values'];
                $datas['deletedFiles'] = $options['data']['deletedFiles'];
                $datas['deleteddragFiles'] = $options['data']['deleteddragFiles'];
                $datas['dragFiles'] = $options['data']['dragFiles'];
                $attr['data-catId'] = $value['catId'];
                $correspondanceCategory = $this->globalParameters->get('system_category_address');
                //set same as value
                if ($value['catId'] == $correspondanceCategory) {
                    if ($this->submittedValue) {
                        $attr['data-same'] = $this->submittedValue[$correspondanceCategory]['same_invoice_address'] ? 1 : 0;
                    } elseif ($this->existingVal) {
                        $attr['data-same'] = $this->existingVal[0]['same_invoice_address'];
                    }
                }
                //set Put main contact's name in the address block
                if ($value['catId'] == $personalCategory) {
                    if ($this->submittedValue) {
                        $attr['data-hasMC'] = $this->submittedValue[$personalCategory]['has_main_contact_address'] ? 1 : 0;
                    } elseif ($this->existingVal) {
                        $attr['data-hasMC'] = $this->existingVal[0]['has_main_contact_address'];
                    }
                }
                //to build the attributes based on category id
                $builder->add($value['catId'],\Common\UtilityBundle\Form\FgDataFieldType::class, array('label' => $catLabel, 'attr' => $attr,
                    'custom_value' => array('formArray' => $datas, 'submittedvalue' => $this->submittedValue, 'existingData' => $this->existingVal, 'globalParameters' => $this->globalParameters, 'catgoryId' => $value['catId'], 'container' => $this->container, 'isIntranet' => $this->isIntranet, 'contactChanges' => $this->contactChanges)  ));

                //ends
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
     * function to set the input values of contact type,language etc
     * @param array $options the array of values of contact
     *
     * @return array
     */
    private function getDefaultFields($options)
    {
        $datas['attrTitles'] = $options['attrTitles'];
        $datas['fedMemberships'] = $options['fedMemberships'];
        $datas['selectedMembership'] = $options['selectedMembership'];
        $datas['fieldType'] = $options['fieldType'];
        $datas['clubIdArray'] = $options['clubIdArray'];
        $datas['fullLanguageName'] = $options['fullLanguageName'];
        $datas['contactId'] = $options['contactId'];

        return $datas;
    }
    /**
     * Function to set image category and fields
     *
     * @param array $inputFieldsArray
     * @return type
     */
    private function addPictureCategory($inputFieldsArray)
    {
        $fieldsArray=$inputFieldsArray['fieldsArray'];
        $companyCategory = $this->globalParameters->get('system_category_company');
        $personalCategory = $this->globalParameters->get('system_category_personal');
        $companyLogo = $this->globalParameters->get('system_field_companylogo');
        $communityPicture = $this->globalParameters->get('system_field_communitypicture');
        $fieldsArray[21]=array('title' => 'picture','catId' => 21,'catClubId' => 1,'isSystem' => 1,'isFairgate' => 0);
        if ($inputFieldsArray['fieldType'] == 'Company') {
            $fieldsArray[21]['values'][$companyLogo]=$fieldsArray[$companyCategory]['values'][$companyLogo];
            $fieldsArray[21]['values'][$companyLogo]['catId']=21;
            unset($fieldsArray[$companyCategory]['values'][$companyLogo]);
        } else {
            $fieldsArray[21]['values'][$communityPicture]=$fieldsArray[$personalCategory]['values'][$communityPicture];
            unset($fieldsArray[$personalCategory]['values'][$communityPicture]);
        }
        $inputFieldsArray['fieldsArray']=$fieldsArray;

        return $inputFieldsArray;
    }

    /**
     * @param OptionsResolver $resolver resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'custom_value' => null,
            'allow_extra_fields' =>true,
        ));
    }
}
