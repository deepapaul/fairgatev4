<?php

/**
 * FgCmsPageContentElementFormFieldsRepository.
 *
 * @package 	WebsiteCMSBundle
 * @subpackage 	Repository
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 *
 */
namespace Common\UtilityBundle\Repository\Cms;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Util\FgSettings;

/**
 * FgCmsPageContentElementFormFieldsRepository
 *
 * This class is used for handling CMS form fields.
 */
class FgCmsPageContentElementFormFieldsRepository extends EntityRepository
{

    /**
     * Function to save the form field details
     * 
     * @param array  $dataArray       The array of form field that needed to be saved
     * @param int    $formId          The id of the form that the data is to be tetirvied
     * @param string $clubDefaultLang The club default language
     * @param array  $clubLanguages   The club service object
     * 
     * @return int $formFieldId Newly inserted form field id
     */
    public function saveFormField($dataArray, $formId, $clubDefaultLang, $clubLanguages, $formFieldType = 'form')
    {
        //save form field
        $formFieldId = $this->saveFormFieldObject($dataArray, $formId, $formFieldType);

        //insert/update the I18n
        $this->_em->getRepository('CommonUtilityBundle:FgCmsPageContentElementFormFieldsI18n')
            ->saveElementI18n($formFieldId, $dataArray, $clubLanguages, $this->_em->getConnection());

        if (in_array($dataArray['fieldType'], array('select', 'radio', 'checkbox'))) {
            //insert options and options i18
            $this->_em->getRepository('CommonUtilityBundle:FgCmsPageContentElementFormFieldOptions')
                ->saveFieldOptions($formFieldId, $dataArray['options'], $clubDefaultLang, $clubLanguages);
        }

        return $formFieldId;
    }

    /**
     * Function to save form field captcha
     * 
     * @param int  $formId         Id of form element
     * @param bool $captchaEnabled Captcha chechbox value
     */
    public function saveFormCaptcha($formId, $captchaEnabled, $formFieldType = 'form')
    {
        $formObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsForms')->find($formId);
        if ($formObj != '') {
            $elementObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsPageContentElementFormFields')->findOneBy(array('form' => $formId, 'fieldType' => 'captcha'));
            if ($captchaEnabled == 1 && !is_object($elementObj)) {
                // New element create contactId
                $elementObj = new \Common\UtilityBundle\Entity\FgCmsPageContentElementFormFields();
                $elementObj->setForm($formObj);
                $elementObj->setFieldType('captcha');
                $elementObj->setIsActive(1);
                $elementObj->setIsDeleted(0);
                $elementObj->setFormFieldType($formFieldType);
                $this->_em->persist($elementObj);
                $this->_em->flush();
            } elseif (is_object($elementObj) && $elementObj->getId() != '' && $captchaEnabled != 1 ) {
                $this->_em->remove($this->find($elementObj->getId()));
                $this->_em->flush();
            }
        }
    }

    /**
     * Method to get form field names to display in inquiry list , in the particular language, by sorting in the order not-deleted
     *  comes first and deleted comes last in the order deteted_date desc
     * 
     * @param int      $clubId       clubId
     * @param string   $contactLang  contact-correspondence-language
     * @param int|null $elementId    form-Id
     * @param boolean  $specificType whether any particular type is specified (default false)
     * @param string   $fieldType    if $specificType, then which type
     * 
     * @return array of fieldnames
     */
    public function getFormFields($clubId, $contactLang, $elementId, $specificType = false, $fieldType = '')
    {
        $query = $this->createQueryBuilder('FF')
            ->select("FF.id as fieldId, CASE WHEN (L.fieldnameLang IS NULL OR L.fieldnameLang ='') THEN FF.fieldname ELSE L.fieldnameLang END as fieldname, FF.fieldType, "
                . "CASE WHEN FF.isDeleted = 0 THEN CURRENT_TIMESTAMP() ELSE FF.deletedAt END as sortDate, FF.isActive, FF.sortOrder, "
                . "OPT.id as optId, OPT.sortOrder as optSort, CASE WHEN (OPTLANG.selectionValueNameLang IS NULL OR OPTLANG.selectionValueNameLang = '') THEN OPT.selectionValueName ELSE OPTLANG.selectionValueNameLang END as optLang ")
            ->innerJoin('CommonUtilityBundle:FgCmsForms', 'F', 'WITH', 'F.id = FF.form ')
            ->innerJoin('CommonUtilityBundle:FgCmsPageContentElement', 'E', 'WITH', 'E.form = F.id ')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentElementFormFieldsI18n', 'L', 'WITH', 'L.id = FF.id AND L.lang = :contactLang ')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentElementFormFieldOptions', 'OPT', 'WITH', 'OPT.field = FF.id  ')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentElementFormFieldOptionsI18n', 'OPTLANG', 'WITH', 'OPT.id = OPTLANG.id AND OPTLANG.lang = :contactLang ')
            ->where('E.club = :clubId')
            ->andWhere("E.id = :elementId AND FF.fieldType != 'button' AND FF.fieldType != 'captcha' AND FF.fieldType != 'heading' ")
            ->orderBy('FF.isDeleted', 'ASC')
            ->addOrderBy('sortDate', 'DESC')
            ->addOrderBy('FF.sortOrder', 'ASC')
            ->addOrderBy('OPT.sortOrder', 'ASC');
        $query->setParameters(array('clubId' => $clubId, 'contactLang' => $contactLang, 'elementId' => $elementId));
        if ($specificType) {
            switch ($fieldType) {
                case 'fileupload':
                    $query->andWhere("FF.fieldType = 'fileupload' ");
                    break;
                case 'confirmationemail':
                    $query->andWhere("FF.fieldType = 'email' AND FF.useMailForNotification = 1 ");
                    break;
                default:
                    $query->andWhere("FF.fieldType IS NOT NULL");
                    break;
            }
        }
        $resultArray = $query->getQuery()->getArrayResult();

        return $this->formatFormFields($resultArray);
    }

    /**
     * Method to format form fields
     * 
     * @param array $resultArray array result from query
     * 
     * @return array formatted array
     */
    private function formatFormFields($resultArray)
    {
        $returnArray = array();
        foreach ($resultArray as $result) {
            $returnArray[$result['fieldId']]['fieldname'] = $result['fieldname'];
            $returnArray[$result['fieldId']]['fieldType'] = $result['fieldType'];
            $returnArray[$result['fieldId']]['formFieldType'] = $result['formField'];
            $returnArray[$result['fieldId']]['fieldId'] = $result['fieldId'];
            $returnArray[$result['fieldId']]['isActive'] = $result['isActive'];
            $returnArray[$result['fieldId']]['sortOrder'] = $result['sortOrder'];
            if (in_array($result['fieldType'], array('select', 'radio', 'checkbox'))) {
                $returnArray[$result['fieldId']]['fieldoptions'][$result['optId']] = array('title' => $result['optLang'], 'sort' => $result['optSort']);
            }
        }

        return $returnArray;
    }

    /**
     * Method to get form field names in all languages, by sorting in the order not-deleted
     *  comes first and deleted comes last in the order deteted_date desc
     * 
     * @param int      $clubId       clubId
     * @param string   $contactLang  contact-correspondence-language
     * @param int|null $elementId    form-Id
     * 
     * @return array of fieldnames in all languages and default
     */
    public function getFormFieldNames($clubId, $elementId)
    {
        $query = $this->createQueryBuilder('FF')
            ->select("FF.id as fieldId, L.fieldnameLang as fieldname, FF.fieldname as defaultFieldName, L.lang as lang, FF.fieldType, "
                . "OPT.id as optId, OPT.selectionValueName, OPTLANG.selectionValueNameLang, OPTLANG.lang as optLang ")
            ->innerJoin('CommonUtilityBundle:FgCmsForms', 'F', 'WITH', 'F.id = FF.form')
            ->innerJoin('CommonUtilityBundle:FgCmsPageContentElement', 'E', 'WITH', 'E.form = F.id ')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentElementFormFieldsI18n', 'L', 'WITH', 'L.id = FF.id  ')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentElementFormFieldOptions', 'OPT', 'WITH', 'OPT.field = FF.id  ')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentElementFormFieldOptionsI18n', 'OPTLANG', 'WITH', 'OPT.id = OPTLANG.id  ')
            ->where('E.club = :clubId')
            ->andWhere("E.id = :elementId AND FF.fieldType != 'button' AND FF.fieldType != 'captcha' AND FF.fieldType != 'heading' ")
            ->andWhere('FF.isDeleted = 0 AND FF.isActive = 1 ')
            ->orderBy('FF.sortOrder', 'ASC')
            ->addOrderBy('OPT.sortOrder', 'ASC');
        $query->setParameters(array('clubId' => $clubId, 'elementId' => $elementId));
        $resultArray = $query->getQuery()->getArrayResult();

        return $this->formatFormFieldNames($resultArray);
    }

    /**
     * Method to format form field names
     * 
     * @param array $resultArray array result from query
     * 
     * @return array formatted array
     */
    private function formatFormFieldNames($resultArray)
    {
        $returnArray = array();
        foreach ($resultArray as $result) {
            $returnArray[$result['fieldId']]['fieldname'][$result['lang']] = $result['fieldname'];
            $returnArray[$result['fieldId']]['fieldname']['default'] = $result['defaultFieldName'];
            $returnArray[$result['fieldId']]['fieldType'] = $result['fieldType'];
            $returnArray[$result['fieldId']]['fieldId'] = $result['fieldId'];
            if (in_array($result['fieldType'], array('select', 'radio', 'checkbox'))) {
                $returnArray[$result['fieldId']]['fieldoptions'][$result['optId']][$result['optLang']] = $result['selectionValueNameLang'];
                $returnArray[$result['fieldId']]['fieldoptions'][$result['optId']]['default'] = $result['selectionValueName'];
            }
        }

        return $returnArray;
    }

    /**
     * This function is used to insert/update form field entries related data.
     * 
     * @param array  $dataArray     Array of data to be inserted/updated in db
     * @param int    $formId        Form id in which this fields belongs
     * @param string $formFieldType Enum values 'form' or 'contact' or 'club-membership'
     * 
     * @return int $formFieldId The inserted/updated form field id.
     */
    public function saveFormFieldObject($dataArray, $formId, $formFieldType = 'form')
    {
        $formObj = $this->_em->getReference('CommonUtilityBundle:FgCmsForms', $formId);
        $contactObj = $this->_em->getReference('CommonUtilityBundle:FgCmContact', $dataArray['contactId']);
        $formFieldObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsPageContentElementFormFields')->find($dataArray['formFieldId']);

        if ($formFieldObj == '') {// New form field created
            $formFieldObj = new \Common\UtilityBundle\Entity\FgCmsPageContentElementFormFields();
            $formFieldObj->setCreatedBy($contactObj);
            $formFieldObj->setCreatedAt(new \DateTime("now"));
            $formFieldObj->setForm($formObj);
            $formFieldObj->setFormFieldType($formFieldType);
        } else {// Form field updated
            $formFieldObj->setUpdatedBy($contactObj);
            $formFieldObj->setUpdatedAt(new \DateTime("now"));
        }

        if ($formFieldType == 'contact') {//save attribute id only for contact fields
            $attributeObj = $this->_em->getReference('CommonUtilityBundle:FgCmAttribute', $dataArray['attributeId']);
            $formFieldObj->setAttribute($attributeObj);
        } elseif ($formFieldType == 'club-membership') {//save default club membership only for club-membership field
            if (isset($dataArray['isFieldHiddenWithDefaultValue']) && $dataArray['isFieldHiddenWithDefaultValue']) {
                $membershipObj = $this->_em->getReference('CommonUtilityBundle:FgCmMembership', $dataArray['defaultClubMembership']);
                $formFieldObj->setDefaultClubMembership($membershipObj);
            }
            if (isset($dataArray['clubMembershipSelection'])) {
                $clubMembershipSelection = (in_array('all', $dataArray['clubMembershipSelection'])) ? 'ALL' : 'SELECTED';
            }
        }

        $minValue = ($dataArray['minValue'] == '') ? null : str_replace(',', '.', $dataArray['minValue']);
        $maxValue = ($dataArray['maxValue'] == '') ? null : str_replace(',', '.', $dataArray['maxValue']);
        $stepValue = ($dataArray['stepValue'] == '') ? null : str_replace(',', '.', $dataArray['stepValue']);

        $formFieldObj->setFieldType((isset($dataArray['fieldType'])) ? $dataArray['fieldType'] : null);
        $formFieldObj->setFieldname((isset($dataArray['default_label'])) ? $dataArray['default_label'] : null);
        $formFieldObj->setPredefinedValue((isset($dataArray['default_predefined'])) ? $dataArray['default_predefined'] : null);
        $formFieldObj->setPlaceholderValue((isset($dataArray['default_placeholder'])) ? $dataArray['default_placeholder'] : null);
        $formFieldObj->setTooltipValue($dataArray['default_tooltip']);
        $formFieldObj->setIsRequired($dataArray['isRequired']);
        $formFieldObj->setSortOrder($dataArray['formElementSortOrder']);
        $formFieldObj->setIsActive((isset($dataArray['isActive'])) ? $dataArray['isActive'] : 1);
        $formFieldObj->setNumberMinValue($minValue);
        $formFieldObj->setNumberMaxValue($maxValue);
        $formFieldObj->setNumberStepValue($stepValue);
        $formFieldObj->setShowSelectionValuesInline((isset($dataArray['showInline'])) ? $dataArray['showInline'] : 1);
        $formFieldObj->setIsMultiSelectable((isset($dataArray['isMultiSelectable'])) ? $dataArray['isMultiSelectable'] : 0);
        $formFieldObj->setUseMailForNotification(isset($dataArray['useMailForNotification']) ? $dataArray['useMailForNotification'] : 0);
        $formFieldObj->setIsDeleted(isset($dataArray['isDeleted']) ? $dataArray['isDeleted'] : 0);
        $formFieldObj->setIsFieldHiddenWithDefaultValue(isset($dataArray['isFieldHiddenWithDefaultValue']) ? $dataArray['isFieldHiddenWithDefaultValue'] : 0);
        $formFieldObj->setClubMembershipSelection(isset($clubMembershipSelection) ? $clubMembershipSelection : null);
        if ($dataArray['isDeleted'] == 1) {
            $formFieldObj->setDeletedAt(new \DateTime("now"));
        }
        $dateFormat = FgSettings::getPhpDateFormat();
        if ($dataArray['dateMin'] != '') {
            $dateMinObj = (\DateTime::createFromFormat($dateFormat, $dataArray['dateMin'])) ? (\DateTime::createFromFormat($dateFormat, $dataArray['dateMin'])) : null;
            $formFieldObj->setDateMin($dateMinObj);
        } else {
            $formFieldObj->setDateMin(null);
        }
        if ($dataArray['dateMax'] != '') {
            $dateMaxObj = (\DateTime::createFromFormat($dateFormat, $dataArray['dateMax'])) ? (\DateTime::createFromFormat($dateFormat, $dataArray['dateMax'])) : null;
            $formFieldObj->setDateMax($dateMaxObj);
        } else {
            $formFieldObj->setDateMax(null);
        }
        $this->_em->persist($formFieldObj);
        $this->_em->flush();

        return $formFieldObj->getId();
    }

    /**
     * Method to get form field names to display in application confirm , in the particular language,
     *  
     * 
     * @param int      $clubId       clubId
     * @param string   $contactLang  contact-correspondence-language
     * @param int      $form         formId
     * 
     * @return array of fieldnames
     */
    public function getAllFormFields($clubId, $contactLang, $form)
    {

        $query = $this->createQueryBuilder('FF')
            ->select("FF.id as fieldId, "
                . "CASE WHEN (FF.attribute IS NOT NULL) THEN "
                . "CASE WHEN (Ai18.fieldnameLang IS NULL OR Ai18.fieldnameLang ='') THEN A.fieldname ELSE Ai18.fieldnameLang END "
                . "ELSE "
                . "CASE WHEN (L.fieldnameLang IS NULL OR L.fieldnameLang ='') THEN FF.fieldname ELSE L.fieldnameLang  END  "
                . "END as fieldname,OPT.id as optId,  "
                . "CASE WHEN (FF.attribute IS NOT NULL) THEN A.inputType ELSE FF.fieldType  END as fieldType, "
                . "CASE WHEN (OPTLANG.selectionValueNameLang IS NULL OR OPTLANG.selectionValueNameLang = '') THEN OPT.selectionValueName ELSE OPTLANG.selectionValueNameLang END as optLang, "
                . "CASE WHEN FF.isDeleted = 0 THEN CURRENT_TIMESTAMP() ELSE FF.deletedAt END as sortDate,FF.sortOrder as sortOrder, FF.formFieldType as formField, IDENTITY(FF.attribute) as attribute"
            )
            ->innerJoin('CommonUtilityBundle:FgCmsForms', 'F', 'WITH', 'F.id = FF.form ')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentElementFormFieldsI18n', 'L', 'WITH', 'L.id = FF.id AND L.lang = :contactLang ')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentElementFormFieldOptions', 'OPT', 'WITH', 'OPT.field = FF.id  ')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentElementFormFieldOptionsI18n', 'OPTLANG', 'WITH', 'OPT.id = OPTLANG.id AND OPTLANG.lang = :contactLang ')
            ->leftJoin('CommonUtilityBundle:FgCmAttribute', 'A', 'WITH', 'A.id = FF.attribute')
            ->leftJoin('CommonUtilityBundle:FgCmAttributeI18n', 'Ai18', 'WITH', 'A.id = Ai18.id AND Ai18.lang = :contactLang')
            ->where('F.club = :clubId')
            ->andWhere('F.id = :form')
            ->andWhere('FF.isDeleted != 1 ')
            ->andWhere('FF.isActive = 1')
            ->addOrderBy('FF.sortOrder', 'ASC');
        $query->setParameters(array('clubId' => $clubId, 'contactLang' => $contactLang, 'form' => $form));

        $resultArray = $query->getQuery()->getArrayResult();

        return $this->formatAllFormFields($resultArray);
    }

    /**
     * Method to format form fields
     * 
     * @param array $resultArray array result from query
     * 
     * @return array formatted array
     */
    private function formatAllFormFields($resultArray)
    {

        $returnArray = array();
        foreach ($resultArray as $result) {
            $key = !empty($result['attribute']) ? 'a' . $result['attribute'] : 'a' . $result['fieldId'];
            $returnArray[$key]['fieldname'] = $result['fieldname'];
            $returnArray[$key]['fieldType'] = $result['fieldType'];
            $returnArray[$key]['formFieldType'] = $result['formField'];
            $returnArray[$key]['fieldId'] = $result['fieldId'];
            $returnArray[$key]['sortOrder'] = $result['sortOrder'];
            $returnArray[$key]['attribute'] = $result['attribute'];
            if (in_array($result['fieldType'], array('select', 'radio', 'checkbox'))) {
                $returnArray[$key]['fieldoptions'][$result['optId']] = array('title' => $result['optLang']);
            }
        }
        return $returnArray;
    }

    /**
     * get All Email Fields For a paticular form of a club
     * 
     * @param int $form     form
     * @param int $clubId   club Id
     * 
     * @return array
     */
    public function getAllEmailFieldsForContact($form, $clubId)
    {

        $query = $this->createQueryBuilder('FF')
            ->select("A.id as attribute ,FF.id as formfield")
            ->innerJoin('CommonUtilityBundle:FgCmsForms', 'F', 'WITH', 'F.id = FF.form ')
            ->leftJoin('CommonUtilityBundle:FgCmAttribute', 'A', 'WITH', 'A.id = FF.attribute')
            ->where('F.club = :clubId')
            ->andWhere('F.id = :form')
            ->andWhere("A.inputType = 'email' OR A.inputType= 'login email' OR FF.fieldType = 'email'")
            ->andWhere('FF.useMailForNotification = 1')
            ->andWhere('FF.isDeleted != 1 ')
            ->andWhere('FF.isActive = 1')
            ->setParameters(array('clubId' => $clubId, 'form' => $form));

        return $query->getQuery()->getArrayResult();
    }

    public function getContactFormFieldsForMailContent($clubId, $formId)
    {
        $params = array('clubId' => $clubId, 'formId' => $formId);
        $query = $this->createQueryBuilder('FF')
            ->select("FF.id as fieldId, A.fieldname as attributeName, Ai18.fieldnameLang as attributeNameLang , Ai18.lang as attributeLang, OPT.id as fieldOptionId, OPTLANG.selectionValueNameLang, OPTLANG.lang as selectionLang, OPT.selectionValueName, L.fieldnameLang, FF.fieldname, L.lang as fieldLang, "
                . "CASE WHEN (FF.attribute IS NOT NULL) THEN A.inputType ELSE FF.fieldType  END as fieldType, "
                . "CASE WHEN FF.isDeleted = 0 THEN CURRENT_TIMESTAMP() ELSE FF.deletedAt END as sortDate,FF.sortOrder as sortOrder, FF.formFieldType as formField, IDENTITY(FF.attribute) as attribute"
            )
            ->innerJoin('CommonUtilityBundle:FgCmsForms', 'F', 'WITH', 'F.id = FF.form ')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentElementFormFieldsI18n', 'L', 'WITH', 'L.id = FF.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentElementFormFieldOptions', 'OPT', 'WITH', 'OPT.field = FF.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentElementFormFieldOptionsI18n', 'OPTLANG', 'WITH', 'OPT.id = OPTLANG.id')
            ->leftJoin('CommonUtilityBundle:FgCmAttribute', 'A', 'WITH', 'A.id = FF.attribute')
            ->leftJoin('CommonUtilityBundle:FgCmAttributeI18n', 'Ai18', 'WITH', 'A.id = Ai18.id');

        $query = $query->where('F.club = :clubId')
            ->andWhere('F.id = :formId')
            ->andWhere('FF.isDeleted != 1 ')
            ->andWhere('FF.isActive = 1')
            ->addOrderBy('FF.sortOrder', 'ASC')
            ->addOrderBy('OPT.sortOrder', 'ASC');
        $query->setParameters($params);

        $resultArray = $query->getQuery()->getArrayResult();

        return $this->formatContactFormFields($resultArray);
    }

    private function formatContactFormFields($resultArray)
    {
        $returnArray = array();
        foreach ($resultArray as $result) {
            $key = !empty($result['attribute']) ? 'a' . $result['attribute'] : 'a' . $result['fieldId'];
            if (!empty($result['attribute'])) {
                $returnArray[$key]['fieldname'] = $result['attributeName'];
            } else {
                $returnArray[$key]['fieldname'] = $result['fieldname'];
            }
            if (!empty($result['attributeLang'])) {
                $returnArray[$key]['fieldnameLang'][$result['attributeLang']] = ($result['attributeNameLang'] != '') ? $result['attributeNameLang'] : $result['attributeName'];
            }
            if (!empty($result['fieldnameLang'])) {
                $returnArray[$key]['fieldnameLang'][$result['fieldLang']] = ($result['fieldnameLang'] != '') ? $result['fieldnameLang'] : $result['fieldname'];
            }
            $returnArray[$key]['fieldType'] = $result['fieldType'];
            $returnArray[$key]['formFieldType'] = $result['formField'];
            $returnArray[$key]['fieldId'] = $result['fieldId'];
            $returnArray[$key]['sortOrder'] = $result['sortOrder'];
            $returnArray[$key]['attribute'] = $result['attribute'];
            if (!empty($result['selectionValueName'])) {
                $returnArray[$key]['fieldOptions'][$result['fieldOptionId']]['default'] = $result['selectionValueName'];
            }
            if (!empty($result['selectionValueNameLang'])) {
                $returnArray[$key]['fieldOptions'][$result['fieldOptionId']][$result['selectionLang']] = $result['selectionValueNameLang'];
            }
        }

        return $returnArray;
    }

    /**
     * To remove the type changed contact fields
     * @param String $checkType form type check
     * @param Int $fieldId id of the contact field
     */
    public function removeContactFields($checkType, $fieldId)
    {
        //set contact form field type
        if ($checkType == 'company') {
            $type = 'company_with_main_contact';
        } elseif ($checkType == 'singleperson') {
            $type = 'single_person';
        }
        //select query for find the object of changed attribute
        $query = $this->createQueryBuilder('FF')
            ->select('FF')
            ->innerJoin('CommonUtilityBundle:FgCmsForms', 'F', 'WITH', 'F.id = FF.form')
            ->where('F.contactFormType=:type');
        if ($checkType == 'company') {
            $query->orWhere("F.contactFormType='company_without_main_contact'");
        }

        $results = $query->andWhere('FF.attribute=:fieldId')
                ->setParameters(array('type' => $type, 'fieldId' => $fieldId))
                ->getQuery()->execute();

        //delete changed attribute from the table 
        foreach ($results as $result) {
            $this->_em->remove($result);
        }
        $this->_em->flush();


        return;
    }

    /**
     * To deactivate contact fields
     * @param Int $fieldId id of the contact field
     */
    public function deactivateContactFields($fieldId)
    {
        $qb = $this->createQueryBuilder();
        $que = $qb->update('CommonUtilityBundle:FgCmsPageContentElementFormFields', 'FF')
            ->set('FF.isActive', '0')
            ->where('FF.attribute=:fieldId')
            ->setParameter('fieldId', $fieldId)
            ->getQuery();
        $que->execute();

        return;
    }
}
