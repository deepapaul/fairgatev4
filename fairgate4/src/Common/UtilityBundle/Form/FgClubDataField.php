<?php

/**
 * FgClubDataFields
 */
namespace Common\UtilityBundle\Form;

use Common\UtilityBundle\Util\FgUtility;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/**
 * FgClubFieldType
 *
 * This FgFieldType was created for handling form
 *
 * @package    CommonUtilityBundle
 * @subpackage Form
 */
class FgClubDataField extends AbstractType
{

    public $fieldId;
    public $fieldType;
    public $fieldValue;
    public $containerParameters;
    public $pageType;

    /**
     * @param Array $submittedData       Data
     * @param Array $editData            Data
     * @param Array $containerParameters Params
     */
    public function __construct($submittedData, $editData, $containerParameters, $pageType = "")
    {
        $this->submittedData = $submittedData;
        $this->editData = $editData;
        $this->containerParameters = $containerParameters;
        $this->pageType = $pageType;
    }

    /**
     * Method to set custom values as globals
     * 
     * @param array $customValues array of sutom values to set
     */
    private function setCustomValues($customValues)
    {
        $this->submittedData = $customValues['submittedData'];
        $this->editData = $customValues['editData'];
        $this->containerParameters = $customValues['containerParameters'];
        $this->pageType = $customValues['pageType'];
        $this->clubObj = $customValues['club'];
    }

    /**
     * Function is used to build form
     * @param Int   $builder Form builder
     * @param Array $options Options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->setCustomValues($options['custom_value']);
        $fieldAttr = array('attr' => array('class' => "form-control", 'data-fieldType' => 'companyf', 'data-attrId' => 'title'));
        $fieldArray = $this->getFields($options['attr']['data-catId'], $options['attr']['data-same']);
        foreach ($fieldArray as $field => $fieldValue) {
            $fieldAttr = array();
            $fieldAttr['label'] = $fieldValue['label'];
            $fieldAttr['attr']['class'] = "form-control";
            $fieldAttr['attr']['data-attrId'] = $field;
            $fieldAttr['required'] = isset($fieldValue['required']) ? $fieldValue['required'] : false;
            $fieldType = TextType::class;
            if ($fieldValue['constraints'] !== false) {
                $fieldAttr['constraints'] = $fieldValue['constraints'];
            }
            if (isset($fieldValue['type'])) {
                switch ($fieldValue['type']) {
                    case 'choice':
                        $fieldType = ChoiceType::class;
                        break;
                    case 'file':
                        $fieldType = FileType::class;
                        break;
                    case 'textarea':
                        $fieldType = TextareaType::class;
                        break;
                    default:
                        $fieldType = TextType::class;
                        break;
                }
            }
            if ($field == 'year') {
                $fieldAttr['attr']['class'] .= ' numbermask';
            }
            if ($field == 'website') {
                $fieldAttr['attr']['class'] .= ' fg-urlmask';
            }
            if (isset($fieldValue['choices'])) {
                $fieldAttr['attr']['data-live-search'] = "true";
                $fieldAttr['attr']['class'] .= " bs-select";
                $fieldAttr['placeholder'] = 'SELECT_DEFAULT';
                $fieldAttr['choices'] = array_flip($fieldValue['choices']);
            }
             $fieldAttr['data'] = $this->editData[$field];

             
            if($field == 'title' || $field == 'signature' || $field == 'logo'){
                foreach($this->clubObj->get('club_languages') as $language){
                    if($this->clubObj->get('club_default_lang') != $language){
                        unset($fieldAttr['constraints'][0]);
                        $fieldAttr['required'] = false;
                    }
                    $fieldAttr['attr']['data-lang'] = $language;
                    $fieldAttr['attr']['data-def-lang'] = $this->clubObj->get('club_default_lang');
                    
                    if ($field == 'logo') {
                        $fieldAttr['data'] = false;
                    } else if ($this->editData['i18n'][$field][$language] != '') {
                        $fieldAttr['data'] = $this->editData['i18n'][$field][$language];
                    } else if ($this->clubObj->get('club_default_lang') == $language) {
                        $fieldAttr['data'] = '';
                        $fieldAttr['attr']['placeholder'] = $this->editData[$field];
                    } else {
                        $fieldAttr['data'] = '';
                        $fieldAttr['attr']['placeholder'] = $this->editData[$field];
                    }

                    $builder->add($field. '_' . $language, $fieldType, $fieldAttr);
                }
            } else {
                $builder->add($field, $fieldType, $fieldAttr);
            }
            
        }
    }

    /**
     * Function to get field of a category
     *
     * @param int    $catId  category
     * @param string $sameAs Current value of same as invoice field
     *
     * @return array
     */
    private function getFields($catId, $sameAs)
    {
        $requiredMessage = 'REQUIRED';
        $invalidEmailMessage = 'INVALID_EMAIL';
        $invalidUrl = 'INVALID_URL';
        $invalidNumberMessage = 'INVALID_NUMBER';
        $maxNumberLimitMessage = 'INVALID_MAX_NUMBER';
        $minNumberLimitMessage = 'INVALID_MIN_NUMBER';
        $country = FgUtility::getCountryListchanged();
        $languages = FgUtility::getAllLanguageNames();

        switch ($catId) {
            case '0':
                $fields = array('title' => array('label' => $this->containerParameters['title'], 'required' => true, 'constraints' => array(new NotBlank(array('message' => $requiredMessage)))),
                    'year' => array('label' => 'CL_YEAR', 'constraints' => array(new Range(array('min' => 0, 'invalidMessage' => $invalidNumberMessage)))),
                    'email' => array('label' => 'CL_EMAIL', 'constraints' => new Email(array('message' => $invalidEmailMessage))),
                    'website' => array('label' => 'CL_WEBSITE', 'constraints' => array(new Url(array('message' => $invalidUrl)))),
                    'sp_lang' => array('label' => 'CL_CORRESPONDENCE_LANG', 'type' => 'choice', 'choices' => $languages, 'required' => true, 'constraints' => array(new NotBlank(array('message' => $requiredMessage)))),
                );
                if ($this->pageType == 'ClubDataEdit') {
                    $fields['club_number'] = array('label' => 'CL_NUMBER', 'type' => 'text', 'constraints' => array(new Range(array('min' => 0, 'max' => 9999999999, 'maxMessage' => $maxNumberLimitMessage, 'minMessage' => $minNumberLimitMessage, 'invalidMessage' => $invalidNumberMessage))));
                }
                break;
            case '1':
                $fields = array('sp_co' => array('label' => 'CL_CO'),
                    'sp_street' => array('label' => 'CL_STREET'),
                    'sp_pobox' => array('label' => 'CL_POST_BOX'),
                    'sp_zipcode' => array('label' => 'CL_ZIPCODE', 'required' => true, 'constraints' => array(new NotBlank(array('message' => $requiredMessage)), new Range(array('min' => 0, 'invalidMessage' => $invalidNumberMessage)))),
                    'sp_city' => array('label' => 'CL_LOCATION', 'required' => true, 'constraints' => array(new NotBlank(array('message' => $requiredMessage)))),
                    'sp_state' => array('label' => 'CL_STATE'),
                    'sp_country' => array('label' => 'CL_COUNTRY', 'type' => 'choice', 'choices' => $country, 'required' => true, 'constraints' => array(new NotBlank(array('message' => $requiredMessage)))));
                break;
            case '2':
                $required = true;
                $constraints = $sameAs ? '' : array(new NotBlank(array('message' => $requiredMessage)));
                $constraintZip = $sameAs ? '' : array(new NotBlank(array('message' => $requiredMessage)), new Range(array('min' => 0, 'invalidMessage' => $invalidNumberMessage)));
                $fields = array('in_co' => array('label' => 'CL_CO'),
                    'in_street' => array('label' => 'CL_STREET'),
                    'in_pobox' => array('label' => 'CL_POST_BOX'),
                    'in_zipcode' => array('label' => 'CL_ZIPCODE', 'required' => $required, 'constraints' => $constraintZip),
                    'in_city' => array('label' => 'CL_LOCATION', 'required' => $required, 'constraints' => $constraints),
                    'in_state' => array('label' => 'CL_STATE'),
                    'in_country' => array('label' => 'CL_COUNTRY', 'type' => 'choice', 'choices' => $country, 'required' => $required, 'constraints' => $constraints));
                break;
            case '3':
                $fields = array('logo' => array('label' => $this->containerParameters['logo'] . " " . $this->containerParameters['logo_trans'], 'type' => 'file'),
                    'signature' => array('label' => 'CL_SIG', 'type' => 'textarea', 'required' => true, 'constraints' => array(new NotBlank(array('message' => $requiredMessage)))));
                break;
            case '4':
                $fields = array('fed_logo' => array('label' => $this->containerParameters['logo'] . " " . $this->containerParameters['icon_trans'], 'type' => 'file'));
                break;
        }

        return $fields;
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

    /**
     * Function get form
     *
     * @return string
     */
    public function getBlockPrefix()
    {

        return 'fg_club_field';
    }
}
