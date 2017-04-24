<?php

namespace Internal\ProfileBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Clubadmin\ContactBundle\Util\ContactOverviewConfig;
use Symfony\Component\Intl\Intl;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Repository\Pdo\ContactPdo;
use Common\UtilityBundle\Util\FgPermissions;

class PrivacyController extends Controller
{

    /**
     * Function is used to list all the fields with there categories to set the privacy settings
     *
     * @return HTML
     */
    public function privacySettingsAction()
    {
        $em = $this->getDoctrine()->getManager();
        $conn = $this->container->get('database_connection');
        $club = $this->get('club');
        $clubDefaultLang = $club->get('default_lang');
        $clubDefaultSystemLang = $club->get('default_system_lang');
        $contact = $this->get('contact');
        $loggedContactId = $contact->get('id');
        $mainAdminRightsForFrontend = $this->container->get('contact')->get('mainAdminRightsForFrontend');
        if (in_array('ROLE_SUPER', $mainAdminRightsForFrontend) || (($club->get('federation_id') != $club->get('id') && in_array('ROLE_FED_ADMIN', $mainAdminRightsForFrontend) ))) {
            $permissionObj = new FgPermissions($this->container);
            $permissionObj->checkUserAccess(0, 'no_access');
        }
        //get club details as array
        $clubIdArray = $this->getClubArray();
        $fieldType = ($contact->get('isCompany') == 1) ? 'Company' : 'Single person';
        // For getting all personal contact fields
        $fieldDetails = $em->getRepository('CommonUtilityBundle:FgCmAttributeset')->getAllPersonalContactFields($clubIdArray, $conn, 0, $fieldType, $clubDefaultLang, $clubDefaultSystemLang, $loggedContactId);
        $isStealthModeFlag = $fieldDetails[0]['is_stealth_mode']; // Getting the stealth mode flag from the result set
        $contactDetails = $em->getRepository('CommonUtilityBundle:FgCmContact')->getNameOfAContact($loggedContactId);
        $resultArray = array('fieldDetails' => json_encode($fieldDetails), 'isStealthModeFlag' => $isStealthModeFlag, 'contactName' => $contactDetails[0]['name']);
        $resultArray['tabs'] = array(0 => array("text" => $this->get('translator')->trans('INTERNAL_OVERVIEW_TAB_TITLE'), "url" => $this->generateUrl('internal_dashboard')), 1 => array("text" => $this->get('translator')->trans('INTERNAL_DATA_TAB_TITLE'), "url" => $this->generateUrl('internal_mydata')), 2 => array("text" => $this->get('translator')->trans('INTERNAL_SETTINGS_TAB_TITLE'), "url" => $this->generateUrl('internal_privacy_settings')));
        $resultArray['privacytabs'] = array(0 => array("text" => $this->get('translator')->trans('INTERNAL_TAB_PRIVACY'), "url" => "#fg_category_privacy", 'dataType' => "privacy", "activeClass" => "active"), 1 => array("text" => $this->get('translator')->trans('INTERNAL_TAB_SYSTEM_LANGUAGE'), "url" => "#fg_category_system_language", 'dataType' => "system_language", "activeClass" => ""), 2 => array("text" => $this->get('translator')->trans('INTERNAL_TAB_NEWSLETTER'), "url" => "#fg_category_newsletter", 'dataType' => "newsletter", "activeClass" => ""));
        //get contact system language, languages to show in select box and newsletter_subscription to show in language and newsletter tabs
        $contactPdo = new ContactPdo($this->container);
        //get contact system_language, correspondence language, is_subscriber
        $contactSettings = $contactPdo->getContactSettingsDetails($loggedContactId, $club->get('clubTable'));
        $contactCorrespondenceLang = (in_array($contactSettings['correspondence_lang'], $club->get('club_languages'))) ? $contactSettings['correspondence_lang'] : $clubDefaultLang;
        $contactCorrLangArray = FgUtility::getClubLanguageNames(array($contactCorrespondenceLang));
        $contactCorrLangName = $this->get('translator')->trans('INTERNAL_PRIVACY_LANGUAGE_TEXT', array('%language%' => $contactCorrLangArray[$contactCorrespondenceLang]));
        $resultArray['defaultLanguages'] = json_encode(array_merge(array('default' => $contactCorrLangName), FgUtility::getDefaultLanguages($this->container)), true);
        $resultArray['contactSystemLang'] = ($contactSettings['system_language'] == '') ? 'default' : $contactSettings['system_language'];
        $resultArray['is_subscriber'] = $contactSettings['is_subscriber'];

        return $this->render('InternalProfileBundle:Privacy:privacySettingsTabs.html.twig', $resultArray);
    }

    /**
     * get club details array
     *
     * @return type array
     */
    private function getClubArray()
    {
        $club = $this->get('club');
        $container = $this->container->getParameterBag();
        $clubIdArray = array('clubId' => $club->get('id'),
            'federationId' => $club->get('federation_id'),
            'subFederationId' => $club->get('sub_federation_id'),
            'clubType' => $club->get('type'),
            'correspondanceCategory' => $container->get('system_category_address'),
            'invoiceCategory' => $container->get('system_category_invoice'));
        $clubIdArray['address'] = $this->get('translator')->trans('CONTACT_FIELD_ADDRESS');
        $clubIdArray['sysLang'] = $club->get('default_lang');
        $clubIdArray['defSysLang'] = $club->get('default_system_lang');
        $clubIdArray['defaultClubLang'] = $club->get('default_lang');
        $clubIdArray['clubLanguages'] = $club->get('club_languages');

        return $clubIdArray;
    }

    /**
     * Function used to save the changed privacy settings including the stealth mode
     *
     * @return JSON
     */
    public function savePrivacySettingsAction()
    {        
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $settings = json_decode($request->get('postArr'), true);
        $contact = $this->get('contact');
        $loggedContactId = $contact->get('id');

        // Calling the pdo repository function for saving
        $contactPdo = new ContactPdo($this->container);
        $contactPdo->savePrivacySettings($settings, $loggedContactId, $this->get('club'));

        return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('INTERNAL_PRIVACY_SETTINGS_SAVED')));
    }

    /**
     * Function is used to list all the fields with there categories to set the privacy settings
     *
     * @param string $contactId ContactId
     * @return HTML
     */
    public function communityProfileAction($contactId)
    {
        $em = $this->getDoctrine()->getManager();
        $conn = $this->container->get('database_connection');

        $club = $this->get('club');
        $clubDefaultLang = $club->get('default_lang');
        $clubDefaultSystemLang = $club->get('default_system_lang');
        $applicationArea = $club->get('applicationArea');
        $terminologyService = $this->get('fairgate_terminology_service');

        $contactOverviewConfig = new ContactOverviewConfig($this->container, $terminologyService);
        $ContactPdo = new ContactPdo($this->container);
        $stealthModeAccess = $ContactPdo->getStealthmodeOfContact($contactId, $club->get('clubTable'), $club->get('type'));

        if ($stealthModeAccess) {
            $permissionObj = new FgPermissions($this->container);
            $permissionObj->checkUserAccess(0, 'no_access');
        }
        $contactObj = $em->getRepository('CommonUtilityBundle:FgCmContact')->find($contactId);
        $fieldType = ($contactObj->getIsCompany() == 1) ? (($contactObj->getHasMainContact() == 1) ? 'Company' : 'companyNoMain') : 'Single person';

        $moduleRights = $this->get('contact')->get('allowedModules');

        //get club details as array
        $clubIdArray = $this->getClubArray();
        $clubIdArray['contactid'] = $this->get('contact')->get('id');
        $clubIdArray['isSuperAdmin'] = ($this->get('contact')->get('isSuperAdmin') || (($this->get('contact')->get('isFedAdmin')) && ($this->get('club')->get('type') != 'federation'))) ? 1 : 0;
        $clubIdArray['isClubAdmin'] = (in_array('clubAdmin', $moduleRights)) ? "1" : "0";

        $contactRoleDetails = $this->get('contact')->get('clubRoleRightsGroupWise');
        $myadminGroups = $myGroups = array();
        if (isset($contactRoleDetails['ROLE_GROUP_ADMIN'])) {
            $myadminGroups = array_merge($myadminGroups, $contactRoleDetails['ROLE_GROUP_ADMIN']['teams'], $contactRoleDetails['ROLE_GROUP_ADMIN']['workgroups']);
        }
        if (isset($contactRoleDetails['ROLE_CONTACT_ADMIN'])) {
            $myadminGroups = array_merge($myadminGroups, $contactRoleDetails['ROLE_CONTACT_ADMIN']['teams'], $contactRoleDetails['ROLE_CONTACT_ADMIN']['workgroups']);
        }

        if (isset($contactRoleDetails['MEMBER'])) {
            $myGroups = array_merge($contactRoleDetails['MEMBER']['teams'], $contactRoleDetails['MEMBER']['workgroups']);
        }

        // For getting all personal contact fields
        $fieldDetails = $em->getRepository('CommonUtilityBundle:FgCmAttributeset')->getAllPersonalContactFields($clubIdArray, $conn, 0, $fieldType, $clubDefaultLang, $clubDefaultSystemLang, $contactId, 1, false, $myGroups, $myadminGroups, $applicationArea);
        
        // Function to get Teams and workgroups of the contact
        $assignedTeams = $em->getRepository('CommonUtilityBundle:FgRmCategory')->getAllAssignedCategories($clubIdArray, $conn, $contactId, 0, false, 1);

        $queryArray = $this->queryCreation($fieldDetails, $contactId);
        $resultArray = $contactOverviewConfig->getInternalContactFieldValuesForOverview($queryArray, $contactId, $contactId);
        
        $em->getRepository('CommonUtilityBundle:FgCmContact')->setContainer($this->container);
        $membershipDetails = $em->getRepository('CommonUtilityBundle:FgCmContact')->getMembershipDetails($contactId);

        ##checking C1 customization
        if ($club->get('type') == 'sub_federation' || $club->get('type') == 'federation' || $club->get('clubMembershipAvailable') == 0) {
            $membershipDetails['clubmembershipTitle'] = '';
        }

        $contactName = $em->getRepository('CommonUtilityBundle:FgCmContact')->getContactName($contactId, $conn, $club, $this->container, 'all');
        
        $returnArray = $this->getReturnArray($contactId) + array('fieldDetails' => json_encode($fieldDetails), 'resultArray' => json_encode($resultArray[0]), 'isFedMemberConfirmed' => $contactObj->getIsFedMembershipConfirmed(),'assignedTeams' => json_encode($assignedTeams), 'userEmail' => $resultArray['0']['categoryset_CF_' . $this->container->getParameter('system_field_primaryemail')], 'contactName' => $contactName[$contactId], 'contactId' => $contactId, 'isCompany' => $contactObj->getIsCompany());

        return $this->render('InternalProfileBundle:Privacy:communityProfile.html.twig', $returnArray);
    }
    
    /**
     * Function to create the parameters that are need to the view
     * 
     * @param int $contactId contactId
     *
     * @return Array
     */
    private function getReturnArray($contactId)
    {
        $returnArray = array();
        $club = $this->get('club');
        
       // Built-in function to get country and language list
        $countryList = Intl::getRegionBundle()->getCountryNames();
        $languages = Intl::getLanguageBundle()->getLanguageNames();
        $returnArray['languages'] = json_encode($languages);
        $returnArray['countryList'] = json_encode($countryList);
        
        $entityManager = $this->getDoctrine()->getManager();
        $fedlogoPath = FgUtility::getClubLogo($club->get('federation_id'), $entityManager);
        $returnArray['fedlogoPath'] = $fedlogoPath;
        
        $membershipDetails = $entityManager->getRepository('CommonUtilityBundle:FgCmContact')->getMembershipDetails($contactId);        
        $returnArray['fedmembershipCatId'] = $membershipDetails['fedMembershipId'];    
        $returnArray['oldFedMembershipId'] = $membershipDetails['oldFedmembershipId'];    
        $returnArray['clubmembershipTitle'] = $membershipDetails['clubmembershipTitle'];    
        $returnArray['fedmembershipTitle'] = $membershipDetails['fedmembershipTitle'];   
        
        $pathService = $this->container->get('fg.avatar');
        $returnArray['imagePath'] = $pathService->getAvatar($contactId, 150);
        
        $getParameterBag = $this->container->getParameterBag();
        $returnArray['correspondenceAddressCategory'] = $getParameterBag->get('system_category_address');
        $returnArray['addressTranslatorString'] = $this->get('translator')->trans('CONTACT_FIELD_ADDRESS');
        
        return $returnArray;
    }
    
    /**
     * Function to create the parameters that are need to the view
     *
     * @return Array
     */
    private function queryCreation($fieldDetails, $contactId)
    {
        $container = $this->container->getParameterBag();
        $invoiceAddressCategory = $container->get('system_category_invoice');
        $contactObj = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmContact')->find($contactId);
        $contactClubId = $contactObj->getClub()->getId();
        
        $queryArray = array();
        $i = 0;
        // Looping the contact fields to generate an array for result of each contact fields
        foreach ($fieldDetails as $key => $fieldDetail) {
            //No need to show the invoice address FAIR 1390
            if ($fieldDetail['catId'] == $invoiceAddressCategory) {
                unset($fieldDetails[$key]);
                continue;
            }
            if ($fieldDetail['inputType'] == "imageupload" || $fieldDetail['inputType'] = "fileupload") {
                $pathService = $this->container->get('fg.avatar');
                $imageContactfield = $pathService->getContactfieldPath($fieldDetail['attrId']);
                $fieldDetails[$key]['path'] = $imageContactfield;
            }
            $queryArray = $this->buildQueryArray($queryArray, $i, $fieldDetail['attrId'], $fieldDetail['club_id']);
            $i++;
        }

        // Setting the primary email id in the query array
        $queryArray = $this->buildQueryArray($queryArray, $i, $this->container->getParameter('system_field_primaryemail'), $contactClubId);
        $i++;
        
        // Setting the profile picture(if company then company logo else community picture) in the query array
        $picAttrId = ($contactObj->getIsCompany() == 1) ? $this->container->getParameter('system_field_companylogo') : $this->container->getParameter('system_field_communitypicture');
        $queryArray = $this->buildQueryArray($queryArray, $i, $picAttrId, $contactClubId);
        
        return $queryArray;
    }
    
    
    /**
     * Function is used to generate query array for contact fields values
     * @param array  $queryArray            Query array
     * @param string $i                    Counter variable
     * @param string $attrId             Field value array
     * @param string $clubId               ClubId
     *
     * @return Array
     */
    private function buildQueryArray($queryArray, $i, $attrId, $clubId)
    {
        $queryArray[$i]['type'] = 'CF';
        $queryArray[$i]['id'] = $attrId;
        $queryArray[$i]['club_id'] = $clubId;
        $queryArray[$i]['name'] = 'categoryset_CF_' . $attrId;

        return $queryArray;
    }
}
