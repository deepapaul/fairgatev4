<?php

/**
 * FgSubscriberForm.
 */
namespace Common\UtilityBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Clubadmin\ContactBundle\Validator\Constraints\FgEmailExists;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/**
 * FgClubFieldType.
 *
 * This FgFieldType was created for handling form
 */
class FgSubscriberForm extends AbstractType
{

    public $fieldId;
    public $fieldType;
    public $fieldValue;
    public $containerParameters;

    /**
     * @param array $editData   Data
     * @param array $fieldTitle Titles
     */
    public function __construct($fieldTitle, $formData)
    {
        $this->formData = $formData;
        $this->fieldTitle = $fieldTitle;
    }

    /**
     * Method to set custom values as globals
     *
     * @param array $customValues array of sutom values to set
     */
    private function setCustomValues($customValues)
    {
        $this->formData = $customValues['formData'];
        $this->fieldTitle = $customValues['fieldTitle'];
    }

    /**
     * Function is used to build form.
     *
     * @param int   $builder Form builder
     * @param array $options Options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->setCustomValues($options['custom_value']);
        $fieldArray = $this->getFields($this->fieldTitle['subscriberId']);

//        echo "<pre>";
//        print_r($fieldArray);
//        exit;
        foreach ($fieldArray as $field => $fieldValue) {
            if ($field == 'subscriberId') {
                break;
            }
            $fieldAttr = array('attr' => array('class' => 'form-control', 'data-attrId' => 'title'));
            $fieldAttr['label'] = $fieldValue['label'];

            $fieldAttr['attr']['class'] = 'form-control';
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
                    case 'email':
                        $fieldType = EmailType::class;
                        break;
                    case 'textarea':                        
                        $fieldType = TextareaType::class;
                        break;
                    default:
                        $fieldType = TextType::class;
                        break;
                }
            }
            if (isset($fieldValue['choices'])) {
                $fieldAttr['attr']['class'] .= ' bs-select';
                $fieldAttr['choices'] = $fieldValue['choices'];
                $fieldAttr['placeholder'] = 'SELECT_DEFAULT';
            }
            if ($this->formData) {
                $fieldAttr['data'] = $this->formData[$field];
            }
            $builder->add($field, $fieldType, $fieldAttr);
            $fieldAttr = array();
        }
    }

    /**
     * Function to get field of a category.
     *
     * @param int    $catId  category
     * @param string $sameAs Current value of same as invoice field
     *
     * @return array
     */
    private function getFields($subscriberId)
    {
        $requiredMessage = 'REQUIRED';
        $invalidEmailMessage = 'INVALID_EMAIL';
        $emailExistsMessage = 'EMAIL_EXIST';
        $fields = array(
            'FirstName' => array('label' => $this->fieldTitle['FirstName']),
            'LastName' => array('label' => $this->fieldTitle['LastName']),
            'Company' => array('label' => $this->fieldTitle['Company']),
            'Email' => array('label' => $this->fieldTitle['Email'], 'type' => 'email',
                'constraints' => array(
                    new NotBlank(array('message' => $requiredMessage)),
                    new Email(array('message' => $invalidEmailMessage)),
                    new FgEmailExists(array('contactId' => 0, 'hasFedMembership' => false, 'from' => 'subscriber', 'subscriberId' => $subscriberId, 'emailExistsMessage' => $emailExistsMessage)),
                ),),
            'Salutation' => array('label' => $this->fieldTitle['Salutation'], 'type' => 'choice', 'choices' => $this->fieldTitle['salutationchoice']),
            'Gender' => array('label' => $this->fieldTitle['Gender'], 'type' => 'choice', 'choices' => $this->fieldTitle['genderchoice']),
            'CorresLang' => array('label' => $this->fieldTitle['CorresLang'], 'type' => 'choice', 'choices' => array_flip($this->fieldTitle['clOptions']),
                'constraints' => array(new NotBlank(array('message' => $requiredMessage))))
        );

        if (count($this->fieldTitle['clOptions']) == 1) {
            unset($fields['CorresLang']);
        }
        return $fields;
    }

    /**
     * Function get form.
     *
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'fg_subscriber';
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
