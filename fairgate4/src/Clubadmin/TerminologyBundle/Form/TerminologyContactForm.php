<?php

namespace Clubadmin\TerminologyBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Common\UtilityBundle\Entity\FgClubTerminology;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * TerminologyContactForm
 *
 * This TerminologyContactForm was created for handling form
 *
 * @package    ClubadminTerminologyBundle
 * @subpackage Form
 * @author     pitsolutions.ch
 * @version    Release:1
 */
class TerminologyContactForm extends AbstractType {

    /**
     * Constructor
     *
     * @param array $individualclub Individualclub
     * @param array $defaultclub    Defaultclub
     * @param array $clubLanguages  ClubLanguages
     *
     */
    public function __construct($individualclub, $defaultclub, $defaultClubForLanguage, $defaultFedForLanguage, $clubLanguages, $clubLanguagesDet, $translator, $transarray, $entityManager, $clubId) {

        $this->individualclub = $individualclub;
        $this->defaultclub = $defaultclub;
        $this->defaultClubForLanguage = $defaultClubForLanguage;
        $this->defaultFedForLanguage = $defaultFedForLanguage;
        $this->clubLanguages = $clubLanguages;
        $this->clubLanguagesDet = $clubLanguagesDet;
        $this->translator = $translator;
        $this->transarray = $transarray;
        $this->entityManager = $entityManager;
        $this->clubId = $clubId;
    }
    
    /**
     * Method to set custom values as globals
     * 
     * @param array $customValues array of sutom values to set
     */
    private function setCustomValues($customValues) {
        $this->individualclub = $customValues['individualclub'];        
        $this->defaultclub = $customValues['defaultclub'];
        $this->defaultClubForLanguage = $customValues['defaultClubForLanguage'];
        $this->defaultFedForLanguage = $customValues['defaultFedForLanguage'];
        $this->clubLanguages = $customValues['clubLanguages'];
        $this->clubLanguagesDet = $customValues['clubLanguagesDet'];
        $this->translator = $customValues['translator'];
        $this->transarray = $customValues['transarray'];
        $this->entityManager = $customValues['entityManager'];        
        $this->clubId = $customValues['clubId']; 
    }

    /**
     * Area to build form
     *
     * @param FormBuilderInterface $builder Builder interface
     * @param array                $options Options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $this->setCustomValues($options['custom_value']);                
        $individualclub = $this->individualclub;
        $defaultclub = $this->defaultclub;
        $lang = $this->clubLanguages;
        $translator = $this->translator;
        $translatorarray = $this->transarray;
        $placeholderAndLangCodes = $this->getPlaceholderAndLanguageCodes();
        foreach ($lang as $language) {
            foreach ($defaultclub as $value) {
                $clubsingular = "";
                $clubplural = "";
                $defaultSingleTerm = $value['defaultSingularTerm'];
                $defSingularTerm = str_replace(' ', '_', $value['defaultSingularTerm']);
                $defPluralTerm = str_replace(' ', '_', $value['defaultPluralTerm']);
                foreach ($individualclub as $indvalue) {

                    if (($value['defaultSingularTerm'] == $indvalue['defaultSingularTerm']) && ($language == $indvalue['lang'])) {
                        $clubsingular = $indvalue['singular'];
                        $clubplural = $indvalue['plural'];
                    }
                }
                if (in_array($value['defaultSingularTerm'], $translatorarray)) {
                    $transtext = array_search(trim($value['defaultSingularTerm']), $translatorarray);
                    $singularlabel = $translator->trans($transtext);
                } else {
                    $singularlabel = $value['defaultSingularTerm'];
                }
                if (in_array($value['defaultPluralTerm'], $translatorarray)) {
                    $transtext = array_search($value['defaultPluralTerm'], $translatorarray);
                    $plurallabel = $translator->trans($transtext);
                } else {
                    $plurallabel = $value['defaultPluralTerm'];
                }
                $placeHolders = $this->getPlaceHolderValues($defaultSingleTerm, $language, $value, $placeholderAndLangCodes['singularTermsArray'], $placeholderAndLangCodes['pluralTermsArray'], $placeholderAndLangCodes['langCodes']);

                $builder->add($defSingularTerm . '_' . $language, TextType::class, array('constraints' => array(new NotBlank(array('message' => 'Required'))), 'label' => $singularlabel, 'data' => $clubsingular, 'required' => false, 'attr' => array('class' => 'txtfield', 'placeholder' => $placeHolders['single'], 'data-key' => $value['id'] . '.' . $value['defaultSingularTerm'] . '.' . $language)))
                        ->add($defPluralTerm . '_' . $language, TextType::class, array('constraints' => array(new NotBlank(array('message' => 'Required'))), 'label' => $plurallabel, 'data' => $clubplural, 'required' => false, 'attr' => array('class' => 'txtfield', 'placeholder' => $placeHolders['plural'], 'data-key' => $value['id'] . '.' . $value['defaultPluralTerm'] . '.' . $language)));
            }
        }
    }

    /**
     * Function to get placeholder values of terminology terms and system language codes of club languages.
     *
     * @return array $resultArray Result array of placeholders and language codes
     */
    private function getPlaceholderAndLanguageCodes() {
        $singularTermsArray = array();
        $pluralTermsArray = array();
        foreach($this->defaultClubForLanguage as $defaultRow){
            $singularTermsArray[$defaultRow['lang']][$defaultRow['defaultSingularTerm']] = $defaultRow['singular'];
            $pluralTermsArray[$defaultRow['lang']][$defaultRow['defaultSingularTerm']] = $defaultRow['plural'];
        }
        foreach($this->defaultFedForLanguage as $defaultRow){
            $singularTermsArray[$defaultRow['lang']][$defaultRow['defaultSingularTerm']] = $defaultRow['singular'];
            $pluralTermsArray[$defaultRow['lang']][$defaultRow['defaultSingularTerm']] = $defaultRow['plural'];
        }
        $resultArray = array('singularTermsArray' => $singularTermsArray, 'pluralTermsArray' => $pluralTermsArray, 'langCodes' => $this->clubLanguagesDet);

        return $resultArray;
    }

    /**
     * Function to get placeholder values(single, multiple) of a terminology term.
     *
     * @param string $defaultSingleTerm  Default singular term
     * @param string $language           Language
     * @param array  $value              Values array
     * @param array  $singularTermsArray Default singular terms array
     * @param array  $pluralTermsArray   Default plural terms array
     * @param array  $langCodes          Language codes array
     *
     * @return array $placeHolders Array of singular and plural placeholder
     */
    private function getPlaceHolderValues($defaultSingleTerm, $language, $value, $singularTermsArray, $pluralTermsArray, $langCodes) {
        $langSystemCode = $langCodes[$language];
        $singularTerm = isset($singularTermsArray[$langSystemCode][$defaultSingleTerm]) ? $singularTermsArray[$langSystemCode][$defaultSingleTerm] : $value['defaultSingularTerm'];
        $pluralTerm = isset($pluralTermsArray[$langSystemCode][$defaultSingleTerm]) ? $pluralTermsArray[$langSystemCode][$defaultSingleTerm] : $value['defaultPluralTerm'];
        $placeHolders = array('single' => $singularTerm, 'plural' => $pluralTerm);
        return $placeHolders;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'custom_value' => null,
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix() {
        return 'contactadmin_terminologybundle_contactform';
    }

}
