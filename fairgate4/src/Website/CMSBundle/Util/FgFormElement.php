<?php

/**
 * FgPageElement
 */
namespace Website\CMSBundle\Util;

use Common\UtilityBundle\Util\FgSettings;
use Common\UtilityBundle\Repository\Pdo\CmsPdo;

/**
 * FgPageElement - The wrapper class to handle all functionalities on form elements
 *
 * @package         Website
 * @subpackage      CMS
 * @author          pitsolutions.ch
 * @version         Fairgate V4
 */
class FgFormElement
{

    /**
     * The constructor function
     *
     * @param object $container container:\Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->club = $this->container->get('club');
        $this->clubId = $this->club->get('id');

        $this->contact = $this->container->get('contact');
        $this->contactId = $this->contact->get('id');

        $this->clubDefaultLang = $this->club->get('club_default_lang');
        $this->clubLanguages = $this->club->get('club_languages');

        $this->em = $this->container->get('doctrine')->getManager();
        $this->translator = $this->container->get('translator');
    }

    /**
     * Function to get all elements of a form element
     *
     * @param int    $formId Form Element Id
     * @param string $event create/edit
     *
     * @return array $formElementArray The formatted array of data
     */
    public function getFormElementData($formId, $event)
    {
        $result = $this->getFormElementDataFromDB($formId);
        $decimalSeperator = FgSettings::getDecimalMarker();

        $formId = ($event == 'create') ? 'new' . rand() : $formId;
        $dateFormat = FgSettings::getPhpDateFormat();

        foreach ($result as $element) {
            $formFieldId = ($event == 'create') ? 'new'.md5($element['formFieldId']) : $element['formFieldId'];
            $formFieldIdIndex = 'I_' . $formFieldId;

            $minValue = ($element['numberMinValue'] != '') ? $element['numberMinValue'] + 0 : '';
            $maxValue = ($element['numberMaxValue'] != '') ? $element['numberMaxValue'] + 0 : '';
            $stepValue = ($element['numberStepValue'] != '') ? $element['numberStepValue'] + 0 : '';

            $formElementArray[$formFieldIdIndex]['formFieldId'] = $formFieldId;
            $formElementArray[$formFieldIdIndex]['formId'] = $formId;
            $formElementArray[$formFieldIdIndex]['formName'] = $element['formName'];
            $formElementArray[$formFieldIdIndex]['fieldType'] = $element['fieldType'];
            $formElementArray[$formFieldIdIndex]['isRequired'] = ($element['isRequired']) ? 1 : 0;
            $formElementArray[$formFieldIdIndex]['sortOrder'] = $element['formElementSortOrder'];
            $formElementArray[$formFieldIdIndex]['formElementSortOrder'] = $element['formElementSortOrder'];
            $formElementArray[$formFieldIdIndex]['formElementIsActive'] = $element['formElementIsActive'];
            $formElementArray[$formFieldIdIndex]['formElementIsDeleted'] = $element['formElementIsDeleted'];
            $formElementArray[$formFieldIdIndex]['minValue'] = str_replace('.', $decimalSeperator, $minValue);
            $formElementArray[$formFieldIdIndex]['maxValue'] = str_replace('.', $decimalSeperator, $maxValue);
            $formElementArray[$formFieldIdIndex]['stepValue'] = str_replace('.', $decimalSeperator, $stepValue);
            $formElementArray[$formFieldIdIndex]['showInline'] = $element['showSelectionValuesInline'];
            $formElementArray[$formFieldIdIndex]['isMultiSelectable'] = $element['isMultiSelectable'];
            $formElementArray[$formFieldIdIndex]['useMailForNotification'] = $element['useMailForNotification'];

            $formElementArray[$formFieldIdIndex]['default_label'] = $element['fieldName'];

            if ($element['dateMin'] != '') {
                $formElementArray[$formFieldIdIndex]['dateMin'] = $element['dateMin']->format($dateFormat);
            }
            if ($element['dateMax'] != '') {
                $formElementArray[$formFieldIdIndex]['dateMax'] = $element['dateMax']->format($dateFormat);
            }

            (!is_array($formElementArray[$formFieldIdIndex]['label'])) ? $formElementArray[$formFieldIdIndex]['label'] = array() : '';
            (!is_array($formElementArray[$formFieldIdIndex]['predefined'])) ? $formElementArray[$formFieldIdIndex]['predefined'] = array() : '';
            (!is_array($formElementArray[$formFieldIdIndex]['placeholder'])) ? $formElementArray[$formFieldIdIndex]['placeholder'] = array() : '';
            (!is_array($formElementArray[$formFieldIdIndex]['tooltip'])) ? $formElementArray[$formFieldIdIndex]['tooltip'] = array() : '';

            if ($element['fieldNameI18n'] != '') {
                $formElementArray[$formFieldIdIndex]['label'][$element['fieldLang']] = $element['fieldNameI18n'];
            }

            if ($element['predefinedValueI18n'] != '') {
                $formElementArray[$formFieldIdIndex]['predefined'][$element['fieldLang']] = $element['predefinedValueI18n'];
            }

            if ($element['placeholderValueI18n'] != '') {
                $formElementArray[$formFieldIdIndex]['placeholder'][$element['fieldLang']] = $element['placeholderValueI18n'];
            }

            if ($element['tooltipValueI18n'] != '') {
                $formElementArray[$formFieldIdIndex]['tooltip'][$element['fieldLang']] = $element['tooltipValueI18n'];
            }

            if ($element['optionId'] != '') {
                $formFieldOptionId = ($event == 'create') ?'new'.md5($element['optionId']) : $element['optionId'];
                $formElementArray[$formFieldIdIndex]['options'][$formFieldOptionId]['id'] = $element['optionId'];
                $formElementArray[$formFieldIdIndex]['options'][$formFieldOptionId]['isActive'] = $element['formElementOptionIsActive'];
                $formElementArray[$formFieldIdIndex]['options'][$formFieldOptionId]['isDeleted'] = $element['formElementOptionIsDeleted'];
                $formElementArray[$formFieldIdIndex]['options'][$formFieldOptionId]['sortOrder'] = $element['formElementOptionSortOrder'];

                $formElementArray[$formFieldIdIndex]['options'][$formFieldOptionId]['default_value'] = $element['formElementSelectionValueName'];
                if ($element['formElementSelectionValueNameI18n'] != '') {
                    $formElementArray[$formFieldIdIndex]['options'][$formFieldOptionId]['value'][$element['optionLang']] = $element['formElementSelectionValueNameI18n'];
                }
            }
        }
        return $formElementArray;
    }

    /**
     * Function to save the form elements data to the DB
     *
     * @param array $dataArray The form data + form element data to be inserted
     *
     * @return int $formId Form Element Id
     */
    public function saveForm($dataArray)
    {
        $formData = array();
        $formData['title'] = $dataArray['formName'];
        $formData['boxId'] = $dataArray['boxId'];
        $formData['pageId'] = $pageId = $dataArray['pageId'];
        $formData['sortOrder'] = $dataArray['sortOrder'];
        $defaultClubLang = $this->clubDefaultLang;

        if ($dataArray['event'] == 'create') {
             $formData['elementId'] = $dataArray['formId'];
            $formId = $this->em->getRepository('CommonUtilityBundle:FgCmsForms')->saveFormStage1($formData['title'], $this->clubId, $this->contactId);
            $formData['formId'] = $formId;
            $this->em->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->reOrderSortPosition($dataArray['boxId'], $dataArray['sortOrder']);
            $elementId = $this->em->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->saveFormElement($formData, $this->clubId);

            /* Log Entry */
            $pageTitles = $this->em->getRepository('CommonUtilityBundle:FgCmsPage')->getPageTite($pageId);
            $pageTitle = isset($pageTitles[$defaultClubLang]) ? $pageTitles[$defaultClubLang] : ($pageTitles['default'] == 'footer' ? $this->translator->trans('TOP_NAV_CMS_FOOTER') : $this->translator->trans('CMS_SIDEBAR'));
            $logArray[] = "('$elementId', '$pageId', 'page', 'added', '', '$pageTitle', now(), $this->contactId)";
        } else {
            $elementObj = $this->em->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->findOneBy(array('form' => $dataArray['formId']));
            $elementId = $elementObj->getId();
            $formId = $dataArray['formId'];
            $this->em->getRepository('CommonUtilityBundle:FgCmsForms')->saveFormStage1($formData['title'], $this->clubId, $this->contactId, $formId);
        }

        $logArray[] = "('$elementId', '$pageId', 'element', 'changed', '', '', now(), $this->contactId)";

        $cmsPdo = new CmsPdo($this->container);
        $cmsPdo->saveLog($logArray);

        $this->saveFormElementData($dataArray, $formId);
        return $formId;
    }

    /**
     * Function to save the form elements data to the DB
     *
     * @param array $data The form element data to be inserted
     * @param int   $formId The id of the form
     *
     * @return void
     */
    private function saveFormElementData($data, $formId)
    {
        $captchaEnabled = $data['captchaEnabled'];
        $clubDefaultLang = $this->clubDefaultLang;
        $dataArray = $data['formFieldData'];
        $create = ($data['event'] == 'create')? 1:0;
        $elementDataArray = $dataArray[key($dataArray)];    //to unwrap the array one level

        foreach ($elementDataArray as $formFieldId => $elementData) {
            $fieldArray = $elementData;
            $fieldArray['formId'] = $formId;
            $fieldArray['formFieldId'] = $formFieldId;
            if($create==1){
              $fieldArray['formFieldId'] = 'new'.$formFieldId;  
            }
            $fieldArray['default_label'] = $elementData['label'][$clubDefaultLang];
            $fieldArray['default_placeholder'] = $elementData['placeholder'][$clubDefaultLang];
            $fieldArray['default_tooltip'] = $elementData['tooltip'][$clubDefaultLang];
            $fieldArray['default_predefined'] = $elementData['predefined'][$clubDefaultLang];
            $fieldArray['contactId'] = $this->contactId;
            $this->em->getRepository('CommonUtilityBundle:FgCmsPageContentElementFormFields')
                ->saveFormField($fieldArray, $formId, $this->clubDefaultLang, $this->clubLanguages);
        }

        $this->em->getRepository('CommonUtilityBundle:FgCmsPageContentElementFormFields')->saveFormCaptcha($formId, $captchaEnabled);

        return;
    }

    /**
     * Function to get all elements of a form element form DB
     *
     * @param int    $formId       The id of the form
     * @param string $forFrontView For admin area or front end view
     *
     * @return array
     */
    private function getFormElementDataFromDB($formId, $forFrontView = 0)
    {
        return $this->em->getRepository('CommonUtilityBundle:FgCmsForms')
                ->getFormDetails($formId, $forFrontView, $this->clubDefaultLang);
    }

    /**
     * Function to get all elements of a form element
     *
     * @param int    $formId Form Element Id
     *
     * @return array $formElementArray The formatted array of data
     */
    public function getFormElementDataForView($formId, $fromedit)
    {
        $formFieldId = array();
        $result = $this->getFormElementDataFromDB($formId, $fromedit);
        $dateFormat = FgSettings::getPhpDateFormat();

        foreach ($result as $key => $element) {
            $formFieldId = $element['formFieldId'];
            $key = empty($element['formElementSortOrder']) ? '99' . $formFieldId : $element['formElementSortOrder'];
            $formElementArray[$key]['formFieldId'] = $formFieldId;
            $formElementArray[$key]['formId'] = $formId;
            $formElementArray[$key]['formElementId'] = $element['formElementId'];
            $formElementArray[$key]['formId'] = $formId;
            $formElementArray[$key]['fieldName'] = $element['fieldName'];
            $formElementArray[$key]['fieldCategory'] = $element['fieldCategory'];
            $formElementArray[$key]['isFieldHiddenWithDefaultValue'] = $element['isFieldHiddenWithDefaultValue'];
            $formElementArray[$key]['predefinedValue'] = $element['predefinedValue'];
            $formElementArray[$key]['placeholderValue'] = $element['placeholder'];
            $formElementArray[$key]['tooltipValue'] = $element['tooltipValue'];
            $formElementArray[$key]['fieldType'] = $element['fieldType'];
            $formElementArray[$key]['isRequired'] = ($element['isRequired']) ? 1 : 0;
            $formElementArray[$key]['sortOrder'] = $element['formElementSortOrder'];
            $formElementArray[$key]['formElementSortOrder'] = $element['formElementSortOrder'];
            $formElementArray[$key]['formElementIsActive'] = $element['formElementIsActive'];
            $formElementArray[$key]['formElementIsDeleted'] = $element['formElementIsDeleted'];
            $formElementArray[$key]['minValue'] = $element['numberMinValue'];
            $formElementArray[$key]['maxValue'] = $element['numberMaxValue'];
            $formElementArray[$key]['stepValue'] = $element['numberStepValue'];
            $formElementArray[$key]['showInline'] = $element['showSelectionValuesInline'];
            $formElementArray[$key]['isMultiSelectable'] = $element['isMultiSelectable'];
            $formElementArray[$key]['clubMembershipSelection'] = $element['clubMembershipSelection'];
            $formElementArray[$key]['attributeId'] = $element['attributeId'];
            $formElementArray[$key]['attributeSetId'] = $element['attributeSetId'];
            $formElementArray[$key]['defaultClubMembership'] = $element['defaultClubMembership'];

            //$formElementArray[$key]['useMailForNotification'] = $element['useMailForNotification'];

            if ($element['dateMin'] != '') {
                $formElementArray[$key]['dateMin'] = $element['dateMin']->format($dateFormat);
            }
            if ($element['dateMax'] != '') {
                $formElementArray[$key]['dateMax'] = $element['dateMax']->format($dateFormat);
            }

            (!is_array($formElementArray[$key]['label'])) ? $formElementArray[$key]['label'] = array() : '';
            (!is_array($formElementArray[$key]['predefined'])) ? $formElementArray[$key]['predefined'] = array() : '';
            (!is_array($formElementArray[$key]['placeholder'])) ? $formElementArray[$key]['placeholder'] = array() : '';
            (!is_array($formElementArray[$key]['tooltip'])) ? $formElementArray[$key]['tooltip'] = array() : '';

            if ($element['fieldNameI18n'] != '') {
                $formElementArray[$key]['label'][$element['fieldLang']] = $element['fieldNameI18n'];
            }

            if ($element['predefinedValueI18n'] != '') {
                $formElementArray[$key]['predefined'][$element['fieldLang']] = $element['predefinedValueI18n'];
            }

            if ($element['placeholderValueI18n'] != '') {
                $formElementArray[$key]['placeholder'][$element['fieldLang']] = $element['placeholderValueI18n'];
            }

            if ($element['tooltipValueI18n'] != '') {
                $formElementArray[$key]['tooltip'][$element['fieldLang']] = $element['tooltipValueI18n'];
            }

            if ($element['optionId'] != '') {
                $optionKey = empty($element['formElementOptionSortOrder']) ? '99' . $element['optionId'] : $element['formElementOptionSortOrder'];
                $formElementArray[$key]['options'][$optionKey]['id'] = $element['optionId'];
                $formElementArray[$key]['options'][$optionKey]['isActive'] = $element['formElementOptionIsActive'];
                $formElementArray[$key]['options'][$optionKey]['isDeleted'] = $element['formElementOptionIsDeleted'];
                $formElementArray[$key]['options'][$optionKey]['sortOrder'] = $element['formElementOptionSortOrder'];
                $formElementArray[$key]['options'][$optionKey]['selectionValueName'] = $element['formElementSelectionValueName'];

                if ($element['formElementSelectionValueNameI18n'] != '') {
                    $formElementArray[$key]['options'][$optionKey]['value'][$element['optionLang']] = $element['formElementSelectionValueNameI18n'];
                }
            } else if ($element['fieldCategory'] == 'contact') {
                $predefinedValues = explode(';', $element['mandatoryPredefinedValue']);
                foreach ($predefinedValues as $pkey => $pval) {
                    $formElementArray[$key]['options'][$pkey]['id'] = $pval;
                    $formElementArray[$key]['options'][$pkey]['sortOrder'] = $pkey;
                    $formElementArray[$key]['options'][$pkey]['selectionValueName'] = $pval;
                    $formElementArray[$key]['options'][$pkey]['value'] = $pval;
                    $formElementArray[$key]['options'][$pkey]['value'] = $pval;
                }
            }

            if ($element['clubMembershipId'] != '') {
                $formElementArray[$key]['clubMembership'][$element['clubMembershipSortOrder']]['id'] = $element['clubMembershipId'];
                $formElementArray[$key]['clubMembership'][$element['clubMembershipSortOrder']]['titleLang'] = $element['clubMembershipTitle'];
            }
        }

        return $formElementArray;
    }

    /**
     * Function to get all elements of a form element
     *
     * @param int    $formId Form Element Id
     * @param string $event create/edit
     *
     * @return array $formElementArray The formatted array of data
     */
    public function getContactFormElementData($formId, $event)
    {
        $result = $this->em->getRepository('CommonUtilityBundle:FgCmsForms')->getContactFormFullData($formId, 0, $this->clubDefaultLang);
        $decimalSeperator = FgSettings::getDecimalMarker();

        $formId = ($event == 'create') ? 'new' . rand() : $formId;
        $dateFormat = FgSettings::getPhpDateFormat();

        foreach ($result as $element) {
            $formFieldId = ($event == 'create') ? 'new'.md5($element['formFieldId']) : $element['formFieldId'];
            $formFieldIdIndex = 'I_' . $formFieldId;

            $minValue = ($element['numberMinValue'] != '') ? $element['numberMinValue'] + 0 : '';
            $maxValue = ($element['numberMaxValue'] != '') ? $element['numberMaxValue'] + 0 : '';
            $stepValue = ($element['numberStepValue'] != '') ? $element['numberStepValue'] + 0 : '';

            $formElementArray[$formFieldIdIndex]['formFieldId'] = $formFieldId;
            $formElementArray[$formFieldIdIndex]['formId'] = $formId;
            $formElementArray[$formFieldIdIndex]['formName'] = $element['formName'];
            $formElementArray[$formFieldIdIndex]['fieldType'] = $element['fieldType'];
            $formElementArray[$formFieldIdIndex]['isRequired'] = ($element['isRequired']) ? 1 : 0;
            $formElementArray[$formFieldIdIndex]['sortOrder'] = $element['formElementSortOrder'];
            $formElementArray[$formFieldIdIndex]['formElementSortOrder'] = $element['formElementSortOrder'];
            $formElementArray[$formFieldIdIndex]['formElementIsActive'] = $element['formElementIsActive'];
            $formElementArray[$formFieldIdIndex]['formElementIsDeleted'] = $element['formElementIsDeleted'];
            $formElementArray[$formFieldIdIndex]['minValue'] = str_replace('.', $decimalSeperator, $minValue);
            $formElementArray[$formFieldIdIndex]['maxValue'] = str_replace('.', $decimalSeperator, $maxValue);
            $formElementArray[$formFieldIdIndex]['stepValue'] = str_replace('.', $decimalSeperator, $stepValue);
            $formElementArray[$formFieldIdIndex]['showInline'] = $element['showSelectionValuesInline'];
            $formElementArray[$formFieldIdIndex]['isMultiSelectable'] = $element['isMultiSelectable'];
            $formElementArray[$formFieldIdIndex]['useMailForNotification'] = $element['useMailForNotification'];
            $formElementArray[$formFieldIdIndex]['attributeId'] = $element['attribute'];
            $formElementArray[$formFieldIdIndex]['fieldnameLang'] = $element['fieldnameLang'];
            $formElementArray[$formFieldIdIndex]['inputType'] = $element['inputType'];
            $formElementArray[$formFieldIdIndex]['predefinedValue'] = $element['predefinedValue'];
            $formElementArray[$formFieldIdIndex]['default_label'] = $element['fieldName'];
            $formElementArray[$formFieldIdIndex]['contactFormType'] = $element['contactFormType'];
            $formElementArray[$formFieldIdIndex]['mandatoryInputType'] = $element['mandatoryInputType'];
            $formElementArray[$formFieldIdIndex]['mandatoryPredefinedValue'] = $element['mandatoryPredefinedValue'];
            $formElementArray[$formFieldIdIndex]['isFieldHiddenWithDefaultValue'] = $element['isFieldHiddenWithDefaultValue'];
            $formElementArray[$formFieldIdIndex]['formFieldType'] = $element['formFieldType'];
            $formElementArray[$formFieldIdIndex]['clubMembershipSelection'] = $element['clubMembershipSelection'];
            $formElementArray[$formFieldIdIndex]['defaultClubMembership'] = $element['defaultClubMembership'];
            $formElementArray[$formFieldIdIndex]['clubMembershipId'] = explode(",", $element['clubMembershipId']);
            $formElementArray[$formFieldIdIndex]['clubMembershipTitle'] = $element['clubMembershipTitle'];
            $formElementArray[$formFieldIdIndex]['clubMembershipSortOrder'] = $element['clubMembershipSortOrder'];
            $formElementArray[$formFieldIdIndex]['fieldAttributeSetId'] = $element['fieldAttributeSetId'];

            if ($element['dateMin'] != '') {
                $formElementArray[$formFieldIdIndex]['dateMin'] = $element['dateMin']->format($dateFormat);
            }
            if ($element['dateMax'] != '') {
                $formElementArray[$formFieldIdIndex]['dateMax'] = $element['dateMax']->format($dateFormat);
            }

            (!is_array($formElementArray[$formFieldIdIndex]['label'])) ? $formElementArray[$formFieldIdIndex]['label'] = array() : '';
            (!is_array($formElementArray[$formFieldIdIndex]['predefined'])) ? $formElementArray[$formFieldIdIndex]['predefined'] = array() : '';
            (!is_array($formElementArray[$formFieldIdIndex]['placeholder'])) ? $formElementArray[$formFieldIdIndex]['placeholder'] = array() : '';
            (!is_array($formElementArray[$formFieldIdIndex]['tooltip'])) ? $formElementArray[$formFieldIdIndex]['tooltip'] = array() : '';

            if ($element['fieldNameI18n'] != '') {
                $formElementArray[$formFieldIdIndex]['label'][$element['fieldLang']] = $element['fieldNameI18n'];
            }

            if ($element['predefinedValueI18n'] != '') {
                $formElementArray[$formFieldIdIndex]['predefined'][$element['fieldLang']] = $element['predefinedValueI18n'];
            }

            if ($element['placeholderValueI18n'] != '') {
                $formElementArray[$formFieldIdIndex]['placeholder'][$element['fieldLang']] = $element['placeholderValueI18n'];
            }

            if ($element['tooltipValueI18n'] != '') {
                $formElementArray[$formFieldIdIndex]['tooltip'][$element['fieldLang']] = $element['tooltipValueI18n'];
            }

            if ($element['optionId'] != '') {
                $formFieldOptionId = ($event == 'create') ? 'new'.md5($element['optionId']) : $element['optionId'];
                $formElementArray[$formFieldIdIndex]['options'][$formFieldOptionId]['id'] = $element['optionId'];
                $formElementArray[$formFieldIdIndex]['options'][$formFieldOptionId]['isActive'] = $element['formElementOptionIsActive'];
                $formElementArray[$formFieldIdIndex]['options'][$formFieldOptionId]['isDeleted'] = $element['formElementOptionIsDeleted'];
                $formElementArray[$formFieldIdIndex]['options'][$formFieldOptionId]['sortOrder'] = $element['formElementOptionSortOrder'];

                $formElementArray[$formFieldIdIndex]['options'][$formFieldOptionId]['default_value'] = $element['formElementSelectionValueName'];
                if ($element['formElementSelectionValueNameI18n'] != '') {
                    $formElementArray[$formFieldIdIndex]['options'][$formFieldOptionId]['value'][$element['optionLang']] = $element['formElementSelectionValueNameI18n'];
                }
            }
        }
        return $formElementArray;
    }
}
