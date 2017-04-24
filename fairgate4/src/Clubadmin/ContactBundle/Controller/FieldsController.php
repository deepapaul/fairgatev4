<?php

namespace Clubadmin\ContactBundle\Controller;

use Common\UtilityBundle\Controller\FgController as ParentController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Common\UtilityBundle\Repository\Pdo\ContactPdo;
use Common\UtilityBundle\Util\FgPermissions;
use Symfony\Component\HttpFoundation\Request;
use Clubadmin\ContactBundle\Util\ContactFieldsIterator;

/**
 * Contact Field Administration
 *
 * @return template
 *
 */
class FieldsController extends ParentController
{

    /**
     * Contact Field Administration main page
     *
     * @return template
     */
    public function indexAction()
    {       
        $conn = $this->container->get('database_connection');
        $membersipFields = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getMembershipsOfClub($this->container);
        //get field ids for tooltips 
        $systemFields = $this->getSystemFieldsForTooltip();
        $clubIdArray = $this->getClubDetailsArray();
        $corrLangId = $this->container->getParameter('system_field_corress_lang');
        $catCommunication = $this->container->getParameter('system_category_communication');
        $clubmembershipAval = $this->get('club')->get('clubMembershipAvailable');
        $fieldList = $this->getAllContactFieldList();
        $fieldDetails = $this->em->getRepository('CommonUtilityBundle:FgCmAttributeset')->getAllClubContactFields($clubIdArray, $conn);
        $fieldDetails = $this->em->getRepository('CommonUtilityBundle:FgCmAttributeset')->fieldItrator($fieldDetails);
        if (count($this->clubLanguages) == 1) {
            unset($fieldDetails['fieldsArray'][$catCommunication]['values'][$corrLangId]);
        }
        //get terminology of Profilbild starts
        $terminologyService = $this->get('fairgate_terminology_service');
        $termi21 = $terminologyService->getTerminology('Club', $this->container->getParameter('singular'));
        $termi5 = $terminologyService->getTerminology('Team', $this->container->getParameter('singular'));
        $profilePictureTeam = $this->container->getParameter('system_field_team_picture');
        $profilePictureClub = $this->container->getParameter('system_field_communitypicture');
        if (isset($fieldDetails['attrTitles'][$profilePictureTeam][$this->clubDefaultSystemLang])) {
            $fieldDetails['attrTitles'][$profilePictureTeam][$this->clubDefaultSystemLang] = str_replace('%Team%', ucfirst($termi5), $fieldDetails['attrTitles'][$profilePictureTeam][$this->clubDefaultSystemLang]);
        }
        if (isset($fieldDetails['attrTitles'][$profilePictureClub][$this->clubDefaultSystemLang])) {
            $fieldDetails['attrTitles'][$profilePictureClub][$this->clubDefaultSystemLang] = str_replace('%Club%', ucfirst($termi21), $fieldDetails['attrTitles'][$profilePictureClub][$this->clubDefaultSystemLang]);
        }

        //get terminology of Profilbild ends
        return $this->render('ClubadminContactBundle:Fields:index.html.twig', array('fieldDetails' => $fieldDetails['fieldsArray'], 'bookedModules' => $this->get('club')->get('bookedModulesDet'), 'clubId' => $this->clubId, 'clubIdArray' => $clubIdArray, 'clubUrlIdentifier' => $this->clubUrlIdentifier, 'attrTitles' => $fieldDetails['attrTitles'], 'systemFields' => $systemFields, 'memberships' => $membersipFields, 'fieldList' => $fieldList, 'system_category_address' => $this->container->getParameter('system_category_address'), 'settings' => true, 'clubmembershipAval' => $clubmembershipAval));
    }

    /**
     * Contact Field Administration option page
     *
     * @return template
     */
    public function optionAction()
    {
        $conn = $this->container->get('database_connection');
        if (!in_array($this->clubType, array('federation', 'sub_federation'))) {
            $permissionObj = new FgPermissions($this->container);
            $permissionObj->checkClubAccess('', '', '');
        }  
        $clubIdArray = $this->getClubDetailsArray();
        $fieldDetails = $this->em->getRepository('CommonUtilityBundle:FgCmAttributeset')->getAllClubContactFields($clubIdArray, $conn);
        $visibleFields = $this->setVisibileFields($fieldDetails);
        //get terminology of Profilbild starts
        $terminologyService = $this->get('fairgate_terminology_service');
        $terminologyArray['club'] = $terminologyService->getTerminology('Club', $this->container->getParameter('singular'));
        $terminologyArray['subfederation'] = $terminologyService->getTerminology('Sub-federation', $this->container->getParameter('singular'));
        //get terminology of Profilbild ends
        return $this->render('ClubadminContactBundle:Fields:option.html.twig', array('fieldDetails' => $visibleFields, 'clubIdArray' => $clubIdArray, 'terminologyArray' => $terminologyArray));
    }
    /**
     * Function to get field ids for tooltips 
     * 
     * @return array $systemFields field ids for tooltips
     */
    private function getSystemFieldsForTooltip()
    {
        $systemFields['address'] = $this->getSpecialFields();
        $systemFields['mobile'] = array($this->container->getParameter('system_field_mobile1'), $this->container->getParameter('system_field_mobile2'));
        $systemFields['parentEmail'] = array($this->container->getParameter('system_field_parentemail1'), $this->container->getParameter('system_field_parentemail2'));
        $systemFields['primaryEmail'] = array($this->container->getParameter('system_field_primaryemail'));
        $systemFields['disabledFields'] = $this->getDisabledFields();

        return $systemFields;
    }

    /**
     * Function to get club details 
     * 
     * @return array $clubIdArray club details 
     */
    private function getClubDetailsArray()
    {
        $correspondanceCategory = $this->container->getParameter('system_category_address');
        $invoiceCategory = $this->container->getParameter('system_category_invoice');
        $clubIdArray = array('clubId' => $this->clubId, 'federationId' => $this->federationId,
            'subFederationId' => $this->subFederationId, 'clubType' => $this->clubType, 'correspondanceCategory' => $correspondanceCategory, 'invoiceCategory' => $invoiceCategory,
            'sysLang' => $this->get('club')->get('club_default_lang'),'hasSubfederation' => $this->get('club')->get('hasSubfederation'), 'defSysLang' => $this->clubDefaultSystemLang, 'clubLanguages' => $this->clubLanguages, 'address' => $this->get('translator')->trans('CONTACT_FIELD_ADDRESS'));

        return $clubIdArray;
    }

    /**
     * Function to set visibile fields
     * @param  array $fieldDetails  contact field details
     * 
     * @return array $visibleFields visibile fields array
     */
    private function setVisibileFields($fieldDetails)
    {
        $visibleFields = array();
        foreach ($fieldDetails as $field) {
            if ((($field['isSystemField'] == 1 && $this->clubType == 'federation') || ($this->clubId == $field['createdBy'])) ||
                ( ($this->clubType == 'sub_federation' && $this->clubId == $field['createdBy']))
            ) {
                $visibleFields[$field['catId']]['catId'] = $field['catId'];
                $visibleFields[$field['catId']]['title'] = $field['title'];
                $visibleFields[$field['catId']]['isSystem'] = $field['isSystem'];
                $visibleFields[$field['catId']]['isFairgate'] = $field['isFairgate'];
                $visibleFields[$field['catId']]['lang'][$field['catLang']] = $field['catTitleLang'];

                $visibleFields[$field['catId']]['fields'][$field['attrId']]['attrId'] = $field['attrId'];
                $visibleFields[$field['catId']]['fields'][$field['attrId']]['title'] = $field['fieldname'];
                $visibleFields[$field['catId']] ['fields'] [$field['attrId']] ['lang'] [$field['lang']] = $field['fieldnameLang'];

                $visibleFields[$field['catId']]['fields'][$field['attrId']]['isSystemField'] = $field['isSystemField'];
                $visibleFields[$field['catId']]['fields'][$field['attrId']]['isFairgateField'] = $field['isFairgate'];
                $visibleFields[$field['catId']]['fields'][$field['attrId']]['isCrucialSystemField'] = $field['isCrucialSystemField'];

                $visibleFields[$field['catId']]['fields'][$field['attrId']]['availabilitySubFed'] = $field['availabilitySubFed'];
                $visibleFields[$field['catId']]['fields'][$field['attrId']]['availabilityClub'] = $field['availabilityClub'];
                $visibleFields[$field['catId']]['fields'][$field['attrId']]['isRequiredFedmemberSubfed'] = $field['isRequiredFedmemberSubfed'];
                $visibleFields[$field['catId']]['fields'][$field['attrId']]['isRequiredFedmemberClub'] = $field['isRequiredFedmemberClub'];
            }
        }

        return $visibleFields;
    }

    /**
     * To get disabled fields array
     *
     * @return array
     */
    public function getDisabledFields()
    {
        $disabledFieldsArray = array();
        $disabledFieldsArray[] = $this->container->getParameter('system_field_salutaion');
        $disabledFieldsArray[] = $this->container->getParameter('system_field_firstname');
        $disabledFieldsArray[] = $this->container->getParameter('system_field_companyname');
        $disabledFieldsArray[] = $this->container->getParameter('system_field_lastname');
        $disabledFieldsArray[] = $this->container->getParameter('system_field_corress_lang');
        $disabledFieldsArray[] = $this->container->getParameter('system_field_gender');

        return $disabledFieldsArray;
    }

    /**
     * To get disabled fields array
     *
     * @return array $specialFields special fields array
     */
    public function getSpecialFields()
    {
        $specialFields = array();
        $specialFields[] = $this->container->getParameter('system_field_salutaion');
        $specialFields[] = $this->container->getParameter('system_field_firstname');
        $specialFields[] = $this->container->getParameter('system_field_lastname');
        $specialFields[] = $this->container->getParameter('system_field_companyname');
        $specialFields[] = $this->container->getParameter('system_field_title');
        $specialFields[] = $this->container->getParameter('system_field_gender');
        $specialFields[] = $this->container->getParameter('system_field_corres_strasse');
        $specialFields[] = $this->container->getParameter('system_field_corres_ort');
        $specialFields[] = $this->container->getParameter('system_field_corres_plz');
        $specialFields[] = $this->container->getParameter('system_field_corres_land');
        $specialFields[] = $this->container->getParameter('system_field_corres_postfach');

        return $specialFields;
    }

    /**
     * To display properties of the contact field
     *
     * @return object JSON Response Object
     */
    public function contactpropertiesAction()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $attributeId = $request->get('attributeId');
        $categoryId = $request->get('categoryId');
        $fedCategory = false;
        if (($clubId != $this->clubId) && !in_array($categoryId, $this->getAllSyatemCategories())) {
            $fedCategory = true;
        }
        $grayedoutStatus = $grayedOutAvailable = $hideCategoryStatus = $inputTypeStatus = 0;    
        $getAllProperties = array();
        $categoryPersonal = $this->container->getParameter('system_category_personal');
        $categoryCorresAddress = $this->container->getParameter('system_category_address');
        $categoryCompany = $this->container->getParameter('system_category_company');
        $categoryInvoiceAddress = $this->container->getParameter('system_category_invoice');
        $excludeCategory = array($categoryInvoiceAddress);
        $getAllCategories = $this->em->getRepository('CommonUtilityBundle:FgCmAttributeset')->getAllCategories($this->clubId, $fedCategory, $categoryId, $excludeCategory);
        if (is_numeric($attributeId)) {
            $getAllProperties = $this->getAllPropertiesOfExistingField($attributeId, $getAllProperties);
        } else {
            list($getAllProperties, $grayedOutAvailable) = $this->getAllPropertiesOfNewField($categoryId, $categoryPersonal, $categoryCompany, $grayedOutAvailable, $getAllProperties);
        }
        if ($getAllProperties['inputType'] == 'checkbox' || $getAllProperties['inputType'] == 'select' || $getAllProperties['inputType'] == 'radio') {
            $inputTypeStatus = 1;
        }
        $isSystemField = $getAllProperties['isSystemField'];
        if (isset($getAllProperties['clubId'])) {
            $clubId = $getAllProperties['clubId'];
        }
        if (is_numeric($attributeId)) {
            if ($isSystemField == 1 || ($clubId != $this->clubId)) {
                $grayedoutStatus = 1;
            }
        }
        $availableFor = $this->getFieldAvailabilityCheck($getAllProperties);
        if ($getAllProperties['categoryId'] == $categoryPersonal || $getAllProperties['categoryId'] == $categoryCompany) {
            $grayedOutAvailable = 1;
        }
        if (!is_numeric($categoryId) || !is_numeric($attributeId)) {
            $hideCategoryStatus = 1;
        }
        $fieldListArray = $this->getAllContactFieldList();
        $systemFields['primaryEmail'] = array($this->container->getParameter('system_field_primaryemail'));
        $jsonArray['systemFields'] = $systemFields;
        $jsonArray['langArray'] = $this->clubLanguages;
        $jsonArray['field'] = $getAllProperties;
        $jsonArray['category'] = $getAllCategories;
        $jsonArray['input_type'] = $fieldListArray;
        $jsonArray['clubDefaultLang'] = $this->get('club')->get('club_default_lang');
        $jsonArray['grayedoutStatus'] = $grayedoutStatus;
        $jsonArray['categoryId'] = $categoryId;
        $jsonArray['attributeId'] = $attributeId;
        $jsonArray['displayUsedForStatus'] =  ($categoryId == $categoryCorresAddress) ? 1 : 0;
        $jsonArray['availableFor'] = $availableFor;
        $jsonArray['grayedOutAvailable'] = $grayedOutAvailable;
        $jsonArray['hideCategoryStatus'] = $hideCategoryStatus;
        $jsonArray['inputTypeStatus'] = $inputTypeStatus;

        return new JsonResponse($jsonArray);
    }
    /**
     * To get contact field availability property
     * 
     * @param $getAllProperties contact field properties
     * 
     * @return array $systemCategotyArray system fields category
     */
    public function getFieldAvailabilityCheck($getAllProperties)
    {
        $availableFor = '';
        if ($getAllProperties['isPersonal'] == 1 && $getAllProperties['isCompany'] == 1) {
            $availableFor = 'both';
        } elseif ($getAllProperties['isPersonal'] == 1) {
            $availableFor = 'personal';
        } elseif ($getAllProperties['isCompany'] == 1) {
            $availableFor = 'company';
        }

        return $availableFor;
    }
    /**
     * Function to set properties of existing contact field
     * 
     * @param int       $attributeId        attribute field id
     * @param array     $getAllProperties   field property array
     * 
     * @return array    $getAllProperties   field property array
     */
    private function getAllPropertiesOfExistingField($attributeId, $getAllProperties)
    {
        $getAllCurrentProperties = $this->em->getRepository('CommonUtilityBundle:FgCmAttribute')->getProperties($attributeId);
        foreach ($getAllCurrentProperties as $properties => $val) {
            $getAllProperties['categoryName'] = $val['categoryName'];
            $getAllProperties['categoryId'] = $val['categoryId'];
            $getAllProperties['fieldname'] = $val['fieldname'];
            $getAllProperties['fieldnameShort'] = $val['fieldnameShort'];
            $getAllProperties[$val['lang']]['fieldnameLang'] = $val['fieldnameLang'];
            $getAllProperties[$val['lang']]['fieldnameShortLang'] = $val['fieldnameShortLang'];
            $getAllProperties['lang'] = $val['lang'];
            $getAllProperties['inputType'] = $val['inputType'];
            $getAllProperties['fieldtype'] = $val['fieldtype'];
            $getAllProperties['isSystemField'] = $val['isSystemField'];
            $getAllProperties['isFairgateField'] = $val['isFairgateField'];
            $getAllProperties['predefinedValue'] = str_replace(";", ",", $val['predefinedValue']);
            $getAllProperties['isPersonal'] = $val['isPersonal'];
            $getAllProperties['isCompany'] = $val['isCompany'];
            $getAllProperties['isSingleEdit'] = $val['isSingleEdit'];
            $getAllProperties['addresType'] = $val['addresType'];
            $getAllProperties['clubId'] = $val['clubId'];
        }

        return $getAllProperties;
    }

    /**
     * Function to set properties for newly created contact field
     * 
     * @param   int     $categoryId         category field id
     * @param   int     $categoryPersonal   personal category id
     * @param   int     $categoryCompany    company filed category id
     * @param   int     $grayedOutAvailable check grayed out available
     * @param   array   $getAllProperties   contact field properties
     * 
     * @return  array   properties and grayedout value
     */
    private function getAllPropertiesOfNewField($categoryId, $categoryPersonal, $categoryCompany, $grayedOutAvailable, $getAllProperties)
    {
        foreach ($this->clubLanguages as $lang) {
            $getAllProperties['categoryId'] = $categoryId;
            $getAllProperties['fieldname'] = '';
            $getAllProperties['fieldnameShort'] = '';
            $getAllProperties[$lang]['fieldnameLang'] = '';
            $getAllProperties[$lang]['fieldnameShortLang'] = '';
            $getAllProperties['addresType'] = 'both';
            $getAllProperties['inputType'] = 'singleline';
            if ($categoryId == $categoryPersonal) {
                $getAllProperties['isPersonal'] = 1;
                $grayedOutAvailable = 1;
            } elseif ($categoryId == $categoryCompany) {
                $getAllProperties['isCompany'] = 1;
                $grayedOutAvailable = 1;
            } else {
                $getAllProperties['isPersonal'] = 1;
                $getAllProperties['isCompany'] = 1;
            }
            $getAllProperties['isSingleEdit'] = '';
        }
        return array($getAllProperties, $grayedOutAvailable);
    }

    /**
     * To display federation details of the contact field
     * 
     * @param Object $request request object
     * 
     * @return object JSON Response Object
     */
    public function fedLevelPermissionAction(Request $request)
    {
        $attributeId = $request->get('attributeId');
        $categoryId = $request->get('categoryId');
        $club = $this->container->get('club');
        $hasSubfederation = $club->get('hasSubfederation');
        if (is_numeric($attributeId)) {
            $getFedClubPermissionDetails = $this->em->getRepository('CommonUtilityBundle:FgCmAttribute')->getFedClubPermissionDetails($attributeId, $this->clubId);
        } else {
            $getFedClubPermissionDetails = array('isVisibleSubfed' => '0',
                'isEditableSubfed' => '0',
                'isRequiredFedmemberSubfed' => '0',
                'isVisibleClub' => '0',
                'isEditableClub' => '0',
                'isRequiredFedmemberClub' => '0',
                'disableStatus' => '1',
                'isSystemField' => '0');
        }
        $details = array('clubtype' => $this->clubType, 'attributeId' => $attributeId, 'categoryId' => $categoryId, 'lang' => $lang, 'hasSubfederation' => $hasSubfederation);
        $mergedFedClubPermissionDetails = array_merge($getFedClubPermissionDetails, $details);

        return new JsonResponse($mergedFedClubPermissionDetails);
    }

    /**
     * To get all contact field list
     *
     * @return array fieldlist array
     */
    public function getAllContactFieldList()
    {
        $fieldListArray = array(
            'singleline' => $this->get('translator')->trans('CONTACT_PROPERTIES_FIELDTYPE_SINGLE'),
            'multiline' => $this->get('translator')->trans('CONTACT_PROPERTIES_FIELDTYPE_MULTI'),
            'checkbox' => $this->get('translator')->trans('CONTACT_PROPERTIES_FIELDTYPE_CHECK'),
            'select' => $this->get('translator')->trans('CONTACT_PROPERTIES_FIELDTYPE_DROP'),
            'radio' => $this->get('translator')->trans('CONTACT_PROPERTIES_FIELDTYPE_RADIO'),
            'number' => $this->get('translator')->trans('CONTACT_PROPERTIES_FIELDTYPE_NUMBER'),
            'date' => $this->get('translator')->trans('CONTACT_PROPERTIES_FIELDTYPE_DATE'),
            'email' => $this->get('translator')->trans('CONTACT_PROPERTIES_FIELDTYPE_EMAIL'),
            'url' => $this->get('translator')->trans('CONTACT_PROPERTIES_FIELDTYPE_URL'),
            'fileupload' => $this->get('translator')->trans('CONTACT_PROPERTIES_FIELDTYPE_FILE'),
            'imageupload' => $this->get('translator')->trans('CONTACT_PROPERTIES_FIELDTYPE_IMAGE')
        );

        return $fieldListArray;
    }

    /**
     * To get all system categories
     *
     * @return array $systemCategotyArray system fields category
     */
    public function getAllSyatemCategories()
    {
        $systemCategotyArray = array($this->container->getParameter('system_category_personal') => 'Personal',
            $this->container->getParameter('system_category_address') => 'Address',
            $this->container->getParameter('system_category_company') => 'Company',
            $this->container->getParameter('system_category_communication') => 'Communication');

        return $systemCategotyArray;
    }

    /**
     * Function to update contact field and contact field category values
     *
     * @return object JSON Response Object
     */
    public function updateAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $clubDefaultLang = $this->get('club')->get('club_default_lang');
            $attributes = json_decode($request->request->get('attributes'), true);
            if (count($attributes) > 0) {
                $random = time() . rand(1, 1000);
                $contactIterator = new ContactFieldsIterator($this->container, $attributes, $random);
                $tableValues = $contactIterator->getTableValues();
                $correspondanceCategory = $this->container->getParameter('system_category_address');
                $invoiceCategory = $this->container->getParameter('system_category_invoice');
                $this->em->getRepository('CommonUtilityBundle:FgCmAttribute')->insertIntoTempTable($tableValues, $random, $this->clubId, $this->clubType, $correspondanceCategory, $invoiceCategory, $clubDefaultLang);
                $pdo = new ContactPdo($this->container);
                $pdo->updateContactAttributeDefault($this->get('club')->get('club_default_lang'), $this->clubId);
                //Remove apc cache entries while updating the data
                $club = $this->container->get('club');
                $domainCacheKey = $club->get('clubCacheKey');
                $cacheKey = str_replace('{{cache_area}}', 'contactfield', $domainCacheKey);
                $cacheDriver = $this->em->getConfiguration()->getResultCacheImpl();
                $cacheDriver->deleteByPrefix($cacheKey);
            }
        }
        $responseArray = array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('FIELD_SETTINGS_SAVED'));
        if ($request->request->get('source') == 'contactprofile') {
            $responseArray['redirect'] = $this->generateUrl('contact_field_profile');
            $responseArray['sync'] = 1;
        }
        if ($request->request->get('source') == 'contactfieldoption') {
            $responseArray['redirect'] = $this->generateUrl('contact_fields_administration_option');
            $responseArray['sync'] = 1;
        }

        return new JsonResponse($responseArray);
    }

    /**
     * Contact Field Administration main page
     *
     * @return template
     */
    public function profileAction()
    {
        $container = $this->container;
        $conn = $container->get('database_connection');

        $clubIdArray = array('clubId' => $this->clubId,
            'federationId' => $this->federationId,
            'subFederationId' => $this->subFederationId,
            'clubType' => $this->clubType,
            'correspondanceCategory' => $container->getParameter('system_category_address'),
            'invoiceCategory' => $container->getParameter('system_category_invoice'),
            'sysLang' => $this->get('club')->get('club_default_lang'),
            'defSysLang' => $this->clubDefaultSystemLang,
            'clubLanguages' => $this->clubLanguages,
            'address' => $this->get('translator')->trans('CONTACT_FIELD_ADDRESS'),
            'catCommunication' => $this->container->getParameter('system_category_communication'),
            'corrLangId' => $this->container->getParameter('system_field_corress_lang'),
        );

        $responseArray['fieldDetails'] = $this->em->getRepository('CommonUtilityBundle:FgCmAttributeset')
            ->getContactProfileOptions($clubIdArray, $conn);

        return $this->render('ClubadminContactBundle:Fields:profile.html.twig', $responseArray);
    }
}
