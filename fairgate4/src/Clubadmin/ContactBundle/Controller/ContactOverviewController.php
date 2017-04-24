<?php

/**
 * ContactOverviewController.
 *
 * This controller was created for handling category role functionalities
 *
 * @author     pitsolutions.ch
 *
 * @version    Fairgate V4
 */
namespace Clubadmin\ContactBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Common\UtilityBundle\Entity\FgCmOverviewSettings;
use Symfony\Component\Intl\Intl;
use Clubadmin\Util\Contactlist;
use Common\UtilityBundle\Util\FgUtility;
use Clubadmin\ContactBundle\Util\ContactDetailsAccess;
use Clubadmin\ContactBundle\Util\ContactOverviewConfig;
use Clubadmin\ContactBundle\Util\NextpreviousContact;
use Clubadmin\SponsorBundle\Util\NextpreviousSponsor;
use Common\UtilityBundle\Repository\Pdo\ContactPdo;
use Common\FilemanagerBundle\Util\FileChecking;
use Clubadmin\ContactBundle\Util\FgClubAssignmentValidator;
use Clubadmin\ContactBundle\Util\ContactDetailsSave;
use Symfony\Component\HttpFoundation\Request;
use Common\UtilityBundle\Repository\Pdo\ClubPdo;
use Common\UtilityBundle\Repository\Pdo\membershipPdo;
/**
 * ContactOverview controller for handlying overview and settings.
 *
 * @author PIT Solutions <pit@solutions.com
 */
class ContactOverviewController extends FgController
{

    private $overviewSave = 0;
    /**
     * Function is used to display contact overview settings.
     *
     * @return template
     */
    public function indexAction()
    {
        return $this->render('ClubadminContactBundle:ContactOverview:index.html.twig', array('clubId' => $this->clubId));
    }

    /**
     * Function is used to get all the array values to display in the contact overview settings page.
     *
     * @return template
     */
    public function renderOverviewContentAction()
    {
        $clubService = $this->container->get('club');
        $clubIdArray = array('clubId' => $this->clubId, 'federationId' => $this->federationId, 'subFederationId' => $this->subFederationId, 'clubType' => $this->clubType, 'clubMembershipAvailable' => $clubService->get('clubMembershipAvailable'));
        $terminologyService = $this->get('fairgate_terminology_service');

        // Class for getting all default settings for overview
        $contactOverviewConfig = new ContactOverviewConfig($this->container, $terminologyService);

        // Function to get basic combined array structure
        $finalArray = $this->getCombinedOverviewStructure($clubIdArray);

        // Getting already saved overview settings data in the json format
        $overviewSettings = $this->em->getRepository('CommonUtilityBundle:FgCmOverviewSettings')->getOverviewSettings($this->clubId, 'contact');
        $overviewSettings = json_decode($overviewSettings['settings'], true);

        $displayedArray = array();

        // Used to get federation and subfed categories to display the fed icons in the overview settings
        $getAllCat = $this->em->getRepository('CommonUtilityBundle:FgRmCategory')->getAllRoleCategories($clubIdArray, $this->conn, $this->clubDefaultLang);

        /*
         * Checking if the saved overview setting is empty. If then we just need to use the base array structure for the settings to display.
         * Othervise need to compare the saved data with the base structure.
         */
        if (empty($overviewSettings)) {
            $return['getAllCatDetails'] = $getAllCat;
            $return['displayedArray'] = $finalArray;

            return new JsonResponse($return);
        } else {
            $displayedArray = $contactOverviewConfig->overviewSettingsArrayLoop($overviewSettings, $finalArray);
        }

        $return['getAllCatDetails'] = $getAllCat;
        $return['displayedArray'] = $displayedArray;
        foreach ($return['getAllCatDetails'] as $key => $value) {
            $return['getAllCatDetails'][$key]['fedicon'] = FgUtility::getClubLogo($value['clubId'], $this->em);
        }

        return new JsonResponse($return);
    }

    /**
     * Function is used to get all default enabled categories and fields.
     *
     * @return Array
     */
    private function defaultEnabledCategoriesAndFields()
    {
        $resultArray['defaultEnabledCat'] = array($this->container->getParameter('system_category_address'), $this->container->getParameter('system_category_invoice'));
        $resultArray['defaultEnabledFields'] = array($this->container->getParameter('system_field_firstname'), $this->container->getParameter('system_field_lastname'), $this->container->getParameter('system_field_team_picture'), $this->container->getParameter('system_field_communitypicture'), $this->container->getParameter('system_field_companyname'), $this->container->getParameter('system_field_companylogo'));

        return $resultArray;
    }

    /**
     * Function is used to save overview settings values.
     *
     * @return template
     */
    public function saveOverviewSettingsAction()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $settings = $request->get('postArr');
        $fgCmOverviewSettingsArray = $this->em->getRepository('CommonUtilityBundle:FgCmOverviewSettings')->getOverviewSettings($this->clubId, 'contact');

        // Checking whether there is already saved overview settings in the database.
        // If then, only need to edit it.
        if (!empty($fgCmOverviewSettingsArray)) {
            $fgCmOverviewSettings = $this->em->getRepository('CommonUtilityBundle:FgCmOverviewSettings')->find($fgCmOverviewSettingsArray['settingsId']);
            $fgCmOverviewSettings->setSettings($settings);
        } else {
            $club = $this->em->getRepository('CommonUtilityBundle:FgClub')->find($this->clubId);
            $fgCmOverviewSettings = new FgCmOverviewSettings();
            $fgCmOverviewSettings->setClub($club);
            $fgCmOverviewSettings->setSettings($settings);
            $fgCmOverviewSettings->setType('contact');
        }

        $this->em->persist($fgCmOverviewSettings);
        $this->em->flush();

        return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('FIELD_SETTINGS_SAVED')));
    }

    /**
     * function to get all the array structure.
     *
     * @param array  $contactOverviewConfig    Contact overview congif class object
     * @param array  $fieldDetails             the array of field details
     * @param string $commonSortOrder          the sort order
     * @param array  $defaultEnabledCategories the array of default enabled categories
     * @param array  $personalCategoryFieldIds the fields under personal categories
     * @param array  $clubIdArray              the array of club ids
     * @param object $conn                     the object of connections
     *
     * @return array
     */
    public function getAllArrayStructure($contactOverviewConfig, $fieldDetails, $commonSortOrder, $defaultEnabledCategories, $personalCategoryFieldIds, $clubIdArray, $conn)
    {
        $club = $this->container->get('club');

        //The bookedModulesDet variable contains all the modules booked by the club
        $bookedModules = $club->get('bookedModulesDet');

        $fieldResultArrayWithSort = $contactOverviewConfig->contactFieldArray($fieldDetails, $commonSortOrder, $defaultEnabledCategories, $personalCategoryFieldIds, $this->clubLanguages);
        $commonSortOrder = $fieldResultArrayWithSort['commonSortOrder'];

        //To display membership block in overview
        $federationMembershipArray = ($clubIdArray['clubType'] == 'standard_club') ? array() : $contactOverviewConfig->getMembershipArray($commonSortOrder, $fedFlag = true);
        $clubMembershipArray = ($clubIdArray['clubType'] == 'sub_federation_club' || $clubIdArray['clubType'] == 'federation_club' || $clubIdArray['clubType'] == 'standard_club') ? ($clubIdArray['clubMembershipAvailable']) ? $contactOverviewConfig->getMembershipArray($commonSortOrder, $fedFlag = false) : array() : array();

        $newRoleCategoryArray = $contactOverviewConfig->getRoleCategoryOverviewArray($commonSortOrder, $clubIdArray, $conn, $this->clubDefaultLang);

        //This is used to get the formated array of system info details in the predefined sort order
        $systemInfosArray = $contactOverviewConfig->getSystemInfoArray($commonSortOrder, $club, $bookedModules);

        //Connection details are formated in the desired format using the following function(main contact,household and other connections)
        $connectionsArray = $contactOverviewConfig->getConnectionArray($commonSortOrder);

        //To get correspondence and invoice address in the desired format for address block to display in the overview
        $addressBlockArray = $contactOverviewConfig->getFormatedAddressBlock($commonSortOrder);

        /*
         * This function is used to get sponsor related details in a formated way.
         * Function is called only if the sponsor module is purchased by the corresponding club.
         */
        $sponsoredArray = array();
        if (in_array('sponsor', $bookedModules)) {
            $sponsoredArray = $contactOverviewConfig->getSponsorDetailsArray($commonSortOrder);
        }

        //Function is used for getting formated base structure of notes details
        $notesArray = $contactOverviewConfig->getFormatedNotesDetails($commonSortOrder);
        $allArray = array('resultArray' => $fieldResultArrayWithSort['resultArray'], 'fedmembershipArray' => $federationMembershipArray, 'clubmembershipArray' => $clubMembershipArray, 'newRoleCategoryArray' => $newRoleCategoryArray, 'systemInfosArray' => $systemInfosArray, 'connectionsArray' => $connectionsArray, 'addressBlockArray' => $addressBlockArray, 'sponsoredArray' => $sponsoredArray, 'notesArray' => $notesArray);

        return $allArray;
    }

    /**
     * function to display the contact overview according to each contact.
     *
     * @param int $offset  the offset
     * @param int $contact the contact id
     *
     * @return json
     */
    public function displayContactOverviewAction($offset, $contact)
    {
        $accessObj = new ContactDetailsAccess($contact, $this->container);
        $terminologyService = $this->get('fairgate_terminology_service');
        $contactOverviewConfig = new ContactOverviewConfig($this->container, $terminologyService);

        // Checking whether the user have the access to this page
        if ($accessObj->accessType == 'NO_ACCESS' || !in_array('overview', $accessObj->tabArray)) {
            if (in_array('data', $accessObj->tabArray)) {
                return $this->redirect($this->generateUrl('contact_data', array('offset' => 0, 'contact' => $contact)));
            }
            $this->fgpermission->checkClubAccess('', 'contactoverview');
        }
        $contactType = $accessObj->contactviewType;

        // Setting the module menu type
        $this->setModuleMenu($accessObj);

        // Query for getting the missing assignment count to display a notification if any
        $missingReqAssgn = $this->em->getRepository('CommonUtilityBundle:FgRmCategory')->missingReqFedAssign($contact, $this->clubId, $this->federationId, $this->subFederationId, $this->clubType, $this->clubDefaultLang, $this->conn);
        $clubService = $this->container->get('club');
        $this->session->set('contactType', $contactType);
        $clubIdArray = array('clubId' => $this->clubId, 'federationId' => $this->federationId, 'subFederationId' => $this->subFederationId, 'clubType' => $this->clubType, 'clubMembershipAvailable' => $clubService->get('clubMembershipAvailable'));

        // Function to get basic combined array structure
        $finalArray = $this->getCombinedOverviewStructure($clubIdArray);

        // Getting saved overview settings from database
        $overviewSettings = $this->em->getRepository('CommonUtilityBundle:FgCmOverviewSettings')->getOverviewSettings($this->clubId, 'contact');
        $overviewSettings = json_decode($overviewSettings['settings'], true);

        $connectionBoxDisplayFlag = $overviewSettings['connections']['displayFlag'];
        $houseHoldBoxFlag = $overviewSettings['connections']['fields']['household_contact_withoutlink']['displayFlag'];
        $mainContactFlag = $overviewSettings['connections']['fields']['mainContact']['displayFlag'];
        $otherConnectionsFlag = $overviewSettings['connections']['fields']['otherConnections']['displayFlag'];

        $i = 0;
        $currentContactName = $this->contactDetails($contact);
        if (isset($currentContactName['fed_membership_cat_id'])) {
            $isFedMembership = 1;
        }

        /*
         *  Checking whether the saved overview settings is empty. If yes, we will generate overview from the base overview array.
         *  Otherwise we will loop the base structure and the saved settings and get the desired values.
         */
        if (empty($overviewSettings)) {
            $generateOverview = $contactOverviewConfig->generateOverviewFromBase($currentContactName, $finalArray, $isFedMembership, $this->clubId);
            $connectionBoxDisplayFlag = 1;
            $houseHoldBoxFlag = 1;
        } else {
            $generateOverview = $contactOverviewConfig->generateOverviewFromSettings($currentContactName, $overviewSettings, $finalArray, $isFedMembership, $this->clubId);
        }

        // Calling contact list class to get the value of each field inside each category to display in the overview.
        $contactlistDatas = $contactOverviewConfig->getContactFieldValuesForOverview($generateOverview['contentArrayForQuery'], $contact, $this->contactId);
        $householdConnections = ($contactlistDatas[0]['connections_CN_household_contact_withoutlink'] != '') ? explode('|', $contactlistDatas[0]['connections_CN_household_contact_withoutlink']) : array();

        // Built-in function to get country and language list
        $countryList = Intl::getRegionBundle()->getCountryNames();
        $languages = Intl::getLanguageBundle()->getLanguageNames();

        $addressBlockCorrespondence = array();
        $addressBlockCorrespondence = $contactOverviewConfig->getAddressBlockArray($currentContactName, $countryList);
        $this->em->getRepository('CommonUtilityBundle:FgCmContact')->setContainer($this->container);
        $getMemberShip = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getMembershipDetails($contact);
        $getMembershipDetails = json_encode($getMemberShip);
        $getClubAssignments = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getClubAssignments($contact);
        $getAllSubFederations = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getAllSubFederations($contact);
         //collect the count of active assignment
        $getActiveAssignmentCount = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getActiveClubAssignments($contact);

        // Section to get Other connections to display in the overview connection block
        $allOtherConnections = $contactOverviewConfig->getAllOtherConnections($contact, $this->clubId, $this->clubDefaultSystemLang);
        $otherConnCnt = $allOtherConnections['otherConnCnt'];
        $otherConnections = json_encode($allOtherConnections['allOtherConnections']);

        // Getting main contact connections
        $mainContactsConnections = $contactOverviewConfig->getAllMainContactConnections($contact, $currentContactName, $this->clubId);
        $maincontCnt = $mainContactsConnections['maincontCnt'];
        $allMainContacts = json_encode($mainContactsConnections['allMainContacts']);

        // Getting all notes of the user. Also the count is generated to display in the overview tab
        $notesArray = $contactOverviewConfig->getAllNotes($this->clubId, $contact);
        $getAllNotes = json_encode($notesArray['allNotes']);
        $notesCnt = $notesArray['notesCnt'];

        // Getting all membership log history details. Need toi display icon beside the federation membership data in the overview
        $allMembershipLogs = $contactOverviewConfig->getAllMembershipLogs($this->clubId, $contact);

        //Getting details of sponsors of this contact
        $sponsoredByDetails = $contactOverviewConfig->getSponsorDetails($this->clubId, $contact);

        $contactOverview = json_encode($generateOverview['contactOverview']);
        $contactlistDatas = json_encode($contactlistDatas[0]);

        // Generating next and previous data for the next-previous functionality in the overview page
        $nextprevious = new NextpreviousContact($this->container);
        $nextPreviousResultset = $nextprevious->nextPreviousContactData($this->contactId, $contact, $offset, 'render_contact_overview', 'offset', 'contact', $flag = 0);

        // Function for getting all the role categories.
        $getAllCat = $this->em->getRepository('CommonUtilityBundle:FgRmCategory')->getAllRoleCategories($clubIdArray, $this->conn, $this->clubDefaultLang);
        $householdConnCnt = count($householdConnections) ? (count($householdConnections) - 1) : 0;

        $groupUserDetails = $this->em->getRepository('CommonUtilityBundle:SfGuardGroup')->getGroupDetails($this->conn, $this->clubId, $contact);
        $hasUserRights = (count($groupUserDetails) > 0) ? 1 : 0;

        $isCompany = $currentContactName['is_company'];
        $contCountDetails = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getContactCountDetails($this->clubId, $contact, $isCompany, $this->clubType, true, true, true, false, false, false, false, $this->federationId, $this->subFederationId);
        $connectionVisibility = $this->getconnectionBoxVisilbility($connectionBoxDisplayFlag, $houseHoldBoxFlag, $mainContactFlag, $otherConnectionsFlag, $householdConnCnt, $otherConnCnt, $maincontCnt);
        $docCount = $this->em->getRepository('CommonUtilityBundle:FgDmAssigment')->getCountOfAssignedDocuments('CONTACT', $this->clubId, $contact);
        $contCountDetails['documentsCount'] = $docCount;
        $finalTabsArray = FgUtility::getTabsArrayDetails($this->container, $accessObj->tabArray, $offset, $contact, $contCountDetails, 'overview', 'contact');
        $return = array('tabs' => $finalTabsArray, 'hasUserRights' => $hasUserRights,
            'notesCount' => $notesCnt, 'connectionCount' => $contCountDetails['connectionCount'],
            'contactType' => $currentContactName['is_company'], 'languages' => json_encode($languages),
            'countryList' => json_encode($countryList), 'mainClubId' => $currentContactName['main_club_id'],
            'clubType' => $this->clubType, 'breadCrumb' => $breadCrumb, 'contactId' => $contact,
            'contactClubId' => $currentContactName['clubId'], 'contactName' => $currentContactName['contactName'],
            'displayedUserName' => $currentContactName['contactname'], 'contactOverview' => $contactOverview,
            'getAllNotes' => $getAllNotes, 'allOtherConnections' => $otherConnections, 'getAllMainContact' => $allMainContacts,
            'nextPreviousResultset' => $nextPreviousResultset, 'offset' => $offset, 'fieldResultArray' => $contactlistDatas,
            'addressBlockCorrespondence' => $addressBlockCorrespondence, 'getMembershipDetails' => $getMembershipDetails,
            'allMembershipLogs' => $allMembershipLogs, 'getAllCatDetails' => json_encode($getAllCat),
            'missingAssgnment' => $missingReqAssgn, 'sponsoredByDetails' => $sponsoredByDetails,
            'fedMembershipId' => $getMemberShip['fedMembershipId'], 'clubMembershipId' => $getMemberShip['clubMembershipId'],
            'connectionVisibility' => $connectionVisibility, 'getClubAssignments' => json_encode($getClubAssignments),
            'getSubfederations' => json_encode($getAllSubFederations), 'createdClubId' => $currentContactName['created_club_id'], 'createdClubType' => $currentContactName['createdClubType'],
            'isFedMemberConfirmed' => $currentContactName['is_fed_membership_confirmed'],
            'fedMembershipMandatory' => $clubService->get('fedMembershipMandatory'),
            'clubMembershipAvailable' => $clubService->get('clubMembershipAvailable'),
            'clubTitle' => $clubService->get('title'),'clubAssignmentCount'=> $getActiveAssignmentCount);
        $return = array_merge($return, $contCountDetails);
        $return['documentsCount'] = $docCount;
        $return['isReadOnlyContact'] = $this->isReadOnlyContact();

        return $this->render('ClubadminContactBundle:ContactOverview:displayContactOverview.html.twig', $return);
    }

    /**
     * Function is used to combine all overview settings array.
     *
     * @param string $clubIdArray ClubId Array
     *
     * @return template
     */
    private function getCombinedOverviewStructure($clubIdArray)
    {
        $terminologyService = $this->get('fairgate_terminology_service');

        // Function to get default enabled categorie's ids from settings to display in the overview and settings
        $defaultEnabledCategoriesAndFields = $this->defaultEnabledCategoriesAndFields();

        //For getting contact field categories and fields from database
        $fieldDetails = $this->em->getRepository('CommonUtilityBundle:FgCmAttributeset')->getAllClubContactFieldsForOverview($clubIdArray, $this->conn, $this->clubDefaultLang, '1', $this->clubDefaultSystemLang);
        $commonSortOrder = 1;

        // Class for getting all default settings for overview
        $contactOverviewConfig = new ContactOverviewConfig($this->container, $terminologyService);

        //Call the function to get federation details for the base array structure
        $federationInfosArray = array();
        $federationInfosArray = $contactOverviewConfig->federationInfoArray($commonSortOrder, $this->clubType);

        /* Thid function is used to get all the element blocks in the overview
         * including the dynamic blocks like contact fields, assignmnts, connection, notes etc to build a basic structure of overview
         * if there is no overview settings are saved before.
         */
        $allArrayStructure = $this->getAllArrayStructure($contactOverviewConfig, $fieldDetails, $commonSortOrder, $defaultEnabledCategoriesAndFields['defaultEnabledCat'], $defaultEnabledCategoriesAndFields['defaultEnabledFields'], $clubIdArray, $this->conn);

        //Combining all the different arrays for the base structure to display in the overview
        $finalArray = array();
        $finalArray = $federationInfosArray + $allArrayStructure['resultArray'] + $allArrayStructure['newRoleCategoryArray'] + $allArrayStructure['systemInfosArray'] + $allArrayStructure['connectionsArray'] + $allArrayStructure['addressBlockArray'] + $allArrayStructure['sponsoredArray'] + $allArrayStructure['notesArray'] + $allArrayStructure['fedmembershipArray'] + $allArrayStructure['clubmembershipArray'];

        return $finalArray;
    }

    /**
     * Function is used to set module wise menu.
     *
     * @param string $accessObj Access object
     *
     * @return template
     */
    private function setModuleMenu($accessObj)
    {
        $contactMenuModule = $accessObj->menuType;
        // Checking the menu type of contact
        if ($contactMenuModule == 'archive') {
            $this->get('club')->set('moduleMenu', 'contactarchive');
        } elseif ($contactMenuModule == 'formerfederationmember') {
            $this->get('club')->set('moduleMenu', 'formerfederationmember');
        }
    }

    /**
     * Method to add mainContactName and mainContactFunction to contact's existing data
     *
     * @param object $request
     * @param array  $contactDetails contact details array
     *
     * @return array contact's existing data
     */
    private function getContactExistingData($request, $contactDetails) {
        $existingData = $contactDetails;
        if ($request->getMethod() != 'POST') {
            //handle edit
            $fieldType = ($existingData[0]['Iscompany'] == 1) ? 'Company' : 'Single person';
            if ($fieldType == 'Company' && !is_null($existingData[0]['comp_def_contact'])) {

                $existingData[0]['mainContactName'] = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getNameOfContact($existingData[0]['comp_def_contact'], $this->conn, true);
                $existingData[0]['mainContactFunction'] = $existingData[0]['comp_def_contact_fun'];
              }
        }

        return $existingData;
    }

    /**
     * Method for generating next and previous data for the next-previous functionality in the overview page
     *
     * @param string $pagetype contact/sponsor
     * @param string $contact  contact id or null in case of create
     * @param int    $offset   offest
     *
     * @return array of next and previous data
     */
    private function getNextPrevResults($pagetype, $contact, $offset) {
        // Generating next and previous data for the next-previous functionality in the overview page
        if ($pagetype == 'contact') {
            $nextprevious = new NextpreviousContact($this->container);
            $nextPreResSet = $nextprevious->nextPreviousContactData($this->contactId, $contact, $offset, 'contact_data', 'offset', 'contact', $flag = 0);
        } elseif ($pagetype == 'sponsor') {
            $nextprevious = new NextpreviousSponsor($this->container);
            $nextPreResSet = $nextprevious->nextPreviousSponsorData($this->contactId, $contact, $offset, 'sponsor_contact_data', 'offset', 'contact', $flag = 0);
        }

        return $nextPreResSet;
    }

    /**
     * Method to get count details to populate in tabs
     *
     * @param string  $contactType      sponsor/contact
     * @param string  $contact          contact id
     * @param array   $existingData     array of contact dxetails
     * @param string  $pagetype         sponsor/contact
     * @param boolean $isArchiveSponsor true/false
     *
     * @return array of count details
     */
    private function getCountDetails($contactType, $contact, $existingData, $pagetype, $isArchiveSponsor) {
        // Get Connection, Assignments, Notes count of a Contact.
        $getAsgmntCount = ($contactType == 'archive' or $contactType == 'archivedsponsor') ? false : true;
        $contCountDetails = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getContactCountDetails($this->clubId, $contact, $existingData[0]['is_company'], $this->clubType, true, $getAsgmntCount, true, false, false, false, false, $this->federationId, $this->subFederationId);
        $contCountDetails['documentsCount'] = $this->em->getRepository('CommonUtilityBundle:FgDmAssigment')->getCountOfAssignedDocuments('CONTACT', $this->clubId, $contact);

        if ($pagetype == 'sponsor') {
            $dataResult['servicesCount'] = $this->em->getRepository('CommonUtilityBundle:FgSmBookings')->getCountOfSponsorServices($this->clubId, $contact, $isArchiveSponsor);
            $dataResult['adsCount'] = $this->em->getRepository('CommonUtilityBundle:FgSmSponsorAds')->getCountOfSponsorAds($this->clubId, $contact);
            $contCountDetails['servicesCount'] = $dataResult['servicesCount'];
            $contCountDetails['adsCount'] = $dataResult['adsCount'];
        }

        return $contCountDetails;
    }

    /**
     * Method to save contact details, if the form is valid
     *
     * @param object $request        request object
     * @param object $form1          created form object
     * @param int    $contactClubId  contact's club-id
     * @param array  $fieldLanguages club languages
     * @param array  $attrfldDetails attribute field deatils
     * @param array  $dragFiles      uploaded files
     * @param array  $existingData   contact details
     * @param string $contact        contact id
     * @param string $pagetype       sponsor/contact
     * @param int    $offset         offset
     *
     * @return array
     */
    private function saveContactDetails($request, $form1, $contactClubId, $fieldLanguages, $attrfldDetails, $dragFiles, $existingData, $contact, $pagetype, $offset) {
        $submittedValues1 = $request->get($form1->getName());
        $files = $request->files->get($form1->getName());
        //to handle normal file/image uploads
        $deletedFiles = $request->request->get('deletedFiles');
        $deletedFilesArray = explode(',', $deletedFiles);
        $submittedValues = $this->uploadFiles($request, $files, $deletedFilesArray, $contactClubId, $submittedValues1, $fieldLanguages, $attrfldDetails);
        //to handle the dropzone images since the image is converted into byte array
        $deleteddragFiles = $request->request->get('deleteddragFiles');
        $otherformFields['dragFiles'] = $deleteddragFilesArray = array();
        $deleteddragFilesArray = ($deleteddragFiles != '') ? explode(',', $deleteddragFiles) : array();
        if (count($dragFiles) > 0) {
            $otherformFields['dragFiles'] = $this->uploadDragFiles($request, $dragFiles, $deleteddragFilesArray, $contactClubId, $existingData);
        }
        //to handle the form save
        $contactObj = new ContactDetailsSave($this->container, $attrfldDetails, $existingData, $contact);
        $contactObj->saveFedMemId = false;
        if($this->overviewSave==1)
            $contactObj->overviewSave = 1;
        $contactObj->saveContact($submittedValues, $deletedFilesArray, $otherformFields, $deleteddragFilesArray);
        if ($pagetype == 'sponsor') {
            $redirect = $this->generateUrl('sponsor_contact_data', array('offset' => $offset, 'contact' => $contact));
        } else {
            $redirect = $this->generateUrl('contact_data', array('offset' => $offset, 'contact' => $contact));
        }

        return array('status' => 'SUCCESS', 'sync' => 1, 'redirect' => $redirect, 'flash' => $this->get('translator')->trans('CONTACT_DATA_SAVED_SUCCESS'));
    }

    /**
     * Function to list the contact data based on contact id.
     *
     * @param \Clubadmin\ContactBundle\Controller\Request $request
     * @param int                                         $offset  the offset
     * @param int                                         $contact the contact id
     *
     * @return JsonResponse| object View Template Render Object (when form saving is success, return JsonResponse)
     */
    public function contactDataAction(Request $request, $offset, $contact = false)
    {
        $pagetype = $request->get('level1') == 'sponsor' ? 'sponsor' : 'contact';
        $dataResult['backLink'] = ($pagetype == 'sponsor') ? $this->generateUrl('clubadmin_sponsor_homepage') : $this->generateUrl('contact_index');
        $dataResult['pageType'] = $pagetype;

        $moduleType = $this->get('club')->get('moduleMenu');
        $dataResult['breadCrumb'] = array('breadcrumb_data' => array(), 'back' => ($moduleType === 'sponsor') ? $this->generateUrl('clubadmin_sponsor_homepage') : (($moduleType == 'archivedsponsor') ? $this->generateUrl('view_archived_sponsors') : (($moduleType === 'contactarchive') ? $this->generateUrl('archive_index') : $this->generateUrl('contact_index'))));

        $accessObj = $this->setContactModuleMenu($contact, $pagetype);
        if ($accessObj->module == 'sponsor' && $accessObj->contactviewType == 'archive') {
            $contactType = 'archivedsponsor';
            $this->session->set('contactType', $contactType);
            $isArchiveSponsor = true;
        } else {
           $contactType = $accessObj->contactviewType;
           $isArchiveSponsor = false;
        }
        $contactMenuModule = $accessObj->menuType;
        $contactNames = $this->contactName($contact, $contactType);
        $contactActualType = ($contactNames['is_permanent_delete'] == 1) ? 'noCondition' : $contactMenuModule;
        $contactType = ($contactNames['is_permanent_delete'] == 1) ? 'formerfederationmember' : $contactType;
        $contactpdo = new ContactPdo($this->container);
        $dataResult['contactDetails'] = $contactpdo->tempContact($contact, $contactActualType);
        $existingData = ($contact) ? $this->getContactExistingData($request, $dataResult['contactDetails'] ) : false;
        $mainContactClubDet = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getClubContactId($existingData[0]['comp_def_contact'],$this->clubId);
        $dataResult['mainContactClubId'] = $mainContactClubDet['id'];

         // To keep membership id if membership category drop down is disabled
        $selectedMembership['club'] = $existingData[0]['club_membership_cat_id'];
        $selectedMembership['fed'] = $existingData[0]['fed_membership_cat_id'];
        $dataResult['selectedMembership'] = $selectedMembership;

        $fieldType = ($existingData[0]['Iscompany'] == 1) ? 'Company' : 'Single person';
        $fieldLanguages = $this->getLanguageArray();
        $attrfldDetails = $this->getAttributeFieldDetails($request, $fieldType, $contact, $selectedMembership, $fieldLanguages);

        //get field category title for tab display
        $catTitlesarray = $this->getCatTitles($attrfldDetails['fieldsArray'], $fieldType);
        $dataformValues = $this->getDataFormValues($request, $fieldType, $contact, $existingData);
        $bookedModulesDet = $this->container->get('club')->get('bookedModulesDet');
        $form1 = $this->createForm(\Common\UtilityBundle\Form\FgDataFieldCategoryType::class, $attrfldDetails, array('custom_value' => array('dataformValues' => $dataformValues, 'existingData' => $existingData, 'containerParameters' => $this->container->getParameterBag(), 'bookedModulesDet' => $bookedModulesDet, 'container' => $this->container) ));
        $nextPreResSet = $this->getNextPrevResults($pagetype, $contact, $offset);
        $contCountDetails = $this->getCountDetails($contactType, $contact, $existingData, $pagetype, $isArchiveSponsor);
        $finalTabsArray = FgUtility::getTabsArrayDetails($this->container, $accessObj->tabArray, $offset, $contact, $contCountDetails, 'data', $contactType);

        $resultArray = array('contactType' => $fieldType, 'catTitlesarray' => $catTitlesarray, 'contact' => $contact, 'mainContactId' => $this->mainContactId, 'displayContactName' => $contactNames['contactName'], 'offset' => $offset, 'nextPreviousResultset' => $nextPreResSet, 'activeTab' => $request->get('active_tab'), 'currentcontactType' => $contactType, 'tabs' => $finalTabsArray);

        $dataResult = array_merge($this->setDataSetArray($dataResult, $existingData, $contact, $pagetype), $resultArray);

        //get contact document count
        $dataResult['documentsCount'] = $contCountDetails['documentsCount'];
        $dataResult['archiveType'] = $contactType;
        $dataResult['isReadOnlyContact'] = $this->isReadOnlyContact();

        if($request->isXmlHttpRequest()) {
            $dataResult['isAjax'] = true;
        } else {
            $dataResult['isAjax'] = false;
        }
         $dataResult['showContent'] = 1;
        //save contact details
        $typeSwitch = ($request->get('fieldType') == 1) ? true : false;
        $contactClubId = ($contact) ? $existingData[0]['created_club_id'] : $this->clubId;
        //validate form and handle save
        if ($request->getMethod() == 'POST' && $typeSwitch == false) {
            $form1->handleRequest($request);
            if ($form1->isSubmitted()) {
                $dataResult['showContent'] = 0;
                if ($form1->isValid()) {
                      if (!$request->get('preValid')) {
                          $this->overviewSave = 1;
                        $saveResult = $this->saveContactDetails($request, $form1, $contactClubId, $fieldLanguages, $attrfldDetails, $attrfldDetails['dragFiles'], $existingData, $contact, $pagetype, $offset);

                        return new JsonResponse($saveResult);
                    }
                } else {
                    $dataResult['isError'] = true;
                }
            }
        }
        //create form
        $dataResult['form'] = $form1->createView();

        return $this->render('ClubadminContactBundle:ContactOverview:contactData.html.twig', $dataResult);
    }

    /**
     * Method to get readonly status of current contact.
     *
     * @return bool $isReadOnlyContact
     */
    private function isReadOnlyContact()
    {
        $allowedModules = $this->container->get('contact')->get('allowedModules');
        if (in_array('readonly_contact', $allowedModules) && !in_array('contact', $allowedModules)) {
            $isReadOnlyContact = 1;
        } else {
            $isReadOnlyContact = 0;
        }

        return $isReadOnlyContact;
    }

    /**
     * Get languages array.
     *
     * @return type
     */
    public function getLanguageArray()
    {
        $languages = Intl::getLanguageBundle()->getLanguageNames();
        $fieldLanguages = array();
        foreach ($this->clubLanguages as $shortName) {
            $fieldLanguages[$shortName] = $languages[$shortName];
        }

        return $fieldLanguages;
    }

    /**
     * Get Country List array.
     *
     * @return type
     */
    public function getCountryListArrayAction()
    {
        $countryList = Intl::getRegionBundle()->getCountryNames();
        $country = array('CH' => $countryList['CH'], 'DE' => $countryList['DE'], 'AT' => $countryList['AT'], 'LI' => $countryList['LI']);
        unset($countryList['CH']);
        unset($countryList['DE']);
        unset($countryList['AT']);
        unset($countryList['LI']);

        return new JsonResponse($country + $countryList);
    }

    /**
     * set resultset array.
     *
     * @param type   $dataResult
     * @param type   $existingData
     * @param int    $contact      contact id
     * @param string $pagetype     sponsor or contact
     *
     * @return type array
     */
    private function setDataSetArray($dataResult, $existingData, $contact, $pagetype)
    {
        $dataResult['ownClub'] = ($existingData[0]['created_club_id'] == $this->clubId) ? true : false;
        $dataResult['mainContactVisible'] = false;
        if ($existingData[0]['is_company'] == 1 && $existingData[0]['has_main_contact'] == 1 && !empty($existingData[0]['comp_def_contact'])) {
            $contactpdo = new ContactPdo($this->container);
            $mainContactVisible = $contactpdo->getClubContactId($existingData[0]['comp_def_contact'], $this->clubId);
            if ($mainContactVisible) {
                $dataResult['mainContactVisible'] = $mainContactVisible['id'];
            }
        } elseif (empty($existingData[0]['comp_def_contact'])) {
            $dataResult['mainContactVisible'] = true;
        }
        $dataResult['clubId'] = $this->clubId;
        $dataResult['isFrontend1Booked'] = $this->isFrontend1Booked($this->clubId);
        $dataResult['fedMembers'] = $this->fedMembers . ':';
        $dataResult['fedmembership'] = $this->fedMemberships;
        $dataResult['federationId'] = $this->clubType == 'federation' ? $this->clubId : $this->federationId;
        $dataResult['fedlogoPath'] = ($dataResult['federationId']) ? FgUtility::getClubLogo($dataResult['federationId'], $this->em) : '';
        $dataResult['subfederationId'] = $this->clubType == 'sub_federation' ? $this->clubId : $this->subFederationId;
        $dataResult['subfedlogoPath'] = ($dataResult['subfederationId']) ? FgUtility::getClubLogo($dataResult['subfederationId'], $this->em) : '';
        $dataResult['contactClubId'] = $existingData[0]['contactClubId'];
        $dataResult['isArchive'] = $existingData[0]['is_deleted'];
        $dataResult['is_sponsor'] = $existingData[0]['is_sponsor'];
        $dataResult['is_stealth_mode'] = $existingData[0]['is_stealth_mode'];
        $dataResult['intranet_access'] = $existingData[0]['intranet_access'];
        if ($pagetype == 'contact') {
            $missingReqAssgn = $this->em->getRepository('CommonUtilityBundle:FgRmCategory')->missingReqFedAssign($contact, $this->clubId, $this->federationId, $this->subFederationId, $this->clubType, $this->clubDefaultLang, $this->conn);
            $dataResult['missingReqAssgment'] = $missingReqAssgn;
            $groupUserDetails = $this->em->getRepository('CommonUtilityBundle:SfGuardGroup')->getGroupDetails($this->conn, $this->clubId, $contact);
            $dataResult['hasUserRights'] = (count($groupUserDetails) > 0) ? 1 : 0;
        }

        return $dataResult;
    }

    /**
     * set ContactModuleMenu and prevent access.
     *
     * @param type $contact
     *
     * @return \Clubadmin\ContactBundle\Util\ContactDetailsAccess
     *
     * @throws type
     */
    private function setContactModuleMenu($contact, $type)
    {
        $accessObj = new ContactDetailsAccess($contact, $this->container, $type);
        if ($accessObj->accessType == 'NO_ACCESS' || !in_array('data', $accessObj->tabArray)) {
            $this->fgpermission->checkClubAccess('', 'contactdata');
        }
        $contactType = ($accessObj->module == 'sponsor') ? 'sponsor' : $accessObj->contactviewType;
        $contactMenuModule = $accessObj->menuType;

        if ($contactMenuModule == 'archive' && $accessObj->module == 'contact') {
            $this->get('club')->set('moduleMenu', 'contactarchive');
        } elseif ($contactMenuModule == 'archive' && $accessObj->module == 'sponsor') {
            $this->get('club')->set('moduleMenu', 'archivedsponsor');
        } elseif ($contactMenuModule == 'formerfederationmember') {
            $this->get('club')->set('moduleMenu', 'formerfederationmember');
        }
        $this->session->set('contactType', $contactType);

        return $accessObj;
    }

    /**
     * Upload Files.
     *
     * @param \Clubadmin\ContactBundle\Controller\Request $request
     * @param array                                       $files
     * @param array                                       $deletedFilesArray
     * @param int                                         $contactClubId
     * @param array                                       $dataformValues
     * @param array                                       $fieldLanguages
     * @param array                                       $fieldDetails
     *
     * @return array
     */
    private function uploadFiles($request, $files, $deletedFilesArray, $contactClubId, $dataformValues, $fieldLanguages, $fieldDetails = false)
    {
        $containerParameters = $this->container->getParameterBag();
        $systemAddress = $containerParameters->get('system_category_address');
        $systemPersonal = $containerParameters->get('system_category_personal');
        $systemCategoryCommunication = $containerParameters->get('system_category_communication');
        $systemCompany = $containerParameters->get('system_category_company');
        $systemCompanyLogo = $containerParameters->get('system_field_companylogo');

        if (count($files) > 0) {
            $this->createContactFolder($contactClubId);
            foreach ($files as $category => $fields) {
                foreach ($fields as $fieldId => $fieldFile) {
                    $dataformValues[$category][$fieldId] = $this->get('fg.avatar')->uploadContactField($fieldFile, $fieldId);
                }
            }
        }
        $dataformValues[$systemPersonal]['mainContactName'] = $request->request->get('mainContactId');
        $dataformValues[$systemAddress]['same_invoice_address'] = $request->request->get('same_invoice_address');
        $dataformValues[$systemCompany]['has_main_contact_address'] = $request->request->get('has_main_contact_address');
        if (count($fieldLanguages) == 1) {
            $dataformValues[$systemCategoryCommunication][$containerParameters->get('system_field_corress_lang')] = $this->clubDefaultLang;
        }
        unset($dataformValues[21]);

        return $dataformValues;
    }

    /**
     * Upload drag files.
     *
     * @param type $request
     * @param type $dragFiles
     * @param type $deleteddragFilesArray
     * @param type $contactClubId
     * @param type $existingData
     *
     * @return array drag file details
     */
    private function uploadDragFiles($request, $dragFiles, $deleteddragFilesArray, $contactClubId, $existingData)
    {
        $otherformFields['dragFiles'] = array();
        $systemCompanyLogo = $this->container->getParameter('system_field_companylogo');
        $this->createContactFolder($contactClubId);
        foreach ($dragFiles as $fileattr => $dragFilename) {
            if (!in_array($fileattr, $deleteddragFilesArray)) {
                if ($dragFilename != $existingData[0][$fileattr]) {
                    $fileType = ($fileattr == $systemCompanyLogo) ? 'companylogo' : 'profilepic';
                    $upfileName = $request->get("picture_$fileattr", '');
                    $fileName = $request->get("dropzone_file", '');
                    $FileChecking = new FileChecking($this->container);
                    $dragFilename = $FileChecking->replaceSingleQuotes($fileName);
                    $newFilename = $this->get('fg.avatar')->saveUserAndCompanyLogo($dragFilename, $fileType,$upfileName);
                    if ( $newFilename !='') {
                     $otherformFields['dragFiles'][$fileattr] = $newFilename;
                    }

                }
            }
        }

        return $otherformFields['dragFiles'];
    }

    /**
     * set terminology terms to contact detail array.
     *
     * @param type $fieldDetails
     *
     * @return array terminology details
     */
    private function setTerminologyTerms($fieldDetails)
    {
        $containerParameters = $this->container->getParameterBag();
        $profilePictureTeam = $containerParameters->get('system_field_team_picture');
        $profilePictureClub = $containerParameters->get('system_field_communitypicture');
        $terminologyService = $this->get('fairgate_terminology_service');
        $termi21 = $terminologyService->getTerminology('Club', $containerParameters->get('singular'));
        $termi5 = $terminologyService->getTerminology('Team', $containerParameters->get('singular'));
        if (isset($fieldDetails['attrTitles'][$profilePictureTeam][$this->clubDefaultSystemLang])) {
            $fieldDetails['attrTitles'][$profilePictureTeam][$this->clubDefaultSystemLang] = str_replace('%Team%', ucfirst($termi5), $fieldDetails['attrTitles'][$profilePictureTeam][$this->clubDefaultSystemLang]);
        }
        if (isset($fieldDetails['attrTitles'][$profilePictureClub][$this->clubDefaultSystemLang])) {
            $fieldDetails['attrTitles'][$profilePictureClub][$this->clubDefaultSystemLang] = str_replace('%Club%', ucfirst($termi21), $fieldDetails['attrTitles'][$profilePictureClub][$this->clubDefaultSystemLang]);
        }

        return $fieldDetails;
    }

    /**
     * Method to get attribute field details of contact
     *
     * @param object $request            request object
     * @param string $fieldType          Company'/'Single person
     * @param string $contact            contact-id
     * @param array  $selectedMembership array of selectd fed and club memberships
     * @param array  $fieldLanguages     club languages
     *
     * @return array of attribute field details
     */
    private function getAttributeFieldDetails($request, $fieldType, $contact, $selectedMembership, $fieldLanguages) {
        //get clud details array
        $clubIdArray = $this->getClubArray();
        $fieldDetails1 = $this->em->getRepository('CommonUtilityBundle:FgCmAttributeset')->getAllClubContactFields($clubIdArray, $this->conn, 0, $fieldType, true);
        //to get all the attributes based on club
        $attrfldDetails1 = $this->em->getRepository('CommonUtilityBundle:FgCmAttributeset')->fieldItrator($fieldDetails1);

        $dragFiles = $deleteddragFiles = $deletedFiles = array();
        if ($request->getMethod() == 'POST' && ($contact)) {
            $dragFiles = $this->handleDropzoneImages($request, $fieldType);
            //to handle the dropzone images since the image is converted into byte array
            $deleteddragFiles = $request->get('deleteddragFiles');
            $deletedFiles = $request->get('deletedFiles');
        }

        $attrArray = array('dragFiles' => $dragFiles, 'deleteddragFiles' => $deleteddragFiles, 'deletedFiles' => $deletedFiles, 'contactId' => $request->get('contact', false), 'selectedMembership' => $selectedMembership, 'fullLanguageName' => $fieldLanguages,  'clubIdArray' => $clubIdArray, 'fieldType' => $fieldType);
        $attrfldDetails = array_merge($this->setTerminologyTerms($attrfldDetails1), $attrArray);

        return $attrfldDetails;
    }

    /**
     * Method to get form datas after submitting the form
     *
     * @param object $request      request object
     * @param string $fieldType    company/single person
     * @param string $contact      contact-id
     * @param array  $existingData contact-details
     *
     * @return array of form datas
     */
    private function getDataFormValues($request, $fieldType, $contact, $existingData) {
        $systemPersonal = $this->container->getParameterBag()->get('system_category_personal');
        $systemAddress = $this->container->getParameterBag()->get('system_category_address');
        $dataformValues = $this->mainContactId = false;
        if ($request->getMethod() == 'POST' ) {
            $this->mainContactId = $request->request->get('mainContactId', '');
            $dataformValues = $request->get('fg_field_category');
            if ($contact) {
                if ($fieldType == 'Company' && empty($this->mainContactId)) {
                    $dataformValues[$systemPersonal]['mainContactName'] = '';
                } elseif ($fieldType == 'Company') {
                    $mainContactNameTitle = $request->get('mainContactNameTitle', '');
                    $dataformValues[$systemPersonal]['mainContactName'] = $mainContactNameTitle['title'][0];
                }
                $dataformValues[$systemPersonal]['has_main_contact_address'] = $request->request->get('has_main_contact_address', 0);
                $dataformValues[$systemAddress]['same_invoice_address'] = $request->request->get('same_invoice_address', 0);
                $request->request->set('fg_field_category', $dataformValues);
            }
        } else if ($contact) {
            $this->mainContactId = $existingData[0]['comp_def_contact'];
        }

        return $dataformValues;
    }

    /**
     * Function get category title for tab.
     *
     * @param type $fieldsArray table fields
     * @param type $fieldType
     *
     * @return int
     */
    private function getCatTitles($fieldsArray, $fieldType)
    {
        $fedId = $this->clubType == 'federation' ? $this->clubId : $this->federationId;
        $subFedId = $this->clubType == 'sub_federation' ? $this->clubId : $this->subFederationId;
        $systemPersonal = $this->container->getParameter('system_category_personal');
        $systemCompany = $this->container->getParameter('system_category_company');
        $systemAddress = $this->container->getParameter('system_category_address');
        $systemCompanyLogo = $this->container->getParameter('system_field_companylogo');
        $systemCommunityPicture = $this->container->getParameter('system_field_communitypicture');
        //to handle the tab titles
        foreach ($fieldsArray as $value) {
            if ((count($value['values']) > 0) && !($fieldType == 'Company' && $value['catId'] == $systemPersonal)) {
                if ($value['isSystem'] == 1 || $value['isFairgate'] == 1) {
                    $catTitlesarray[$value['catId']]['title'] = (isset($value['titles'][$this->clubDefaultSystemLang])) ? $value['titles'][$this->clubDefaultSystemLang] : $value['title'];
                    $catTitlesarray[$value['catId']]['fedFlag'] = $fedId == $value['catClubId'] ? 1 : 0;
                    $catTitlesarray[$value['catId']]['subfedFlag'] = $subFedId == $value['catClubId'] ? 1 : 0;
                } else {
                    $catTitlesarray[$value['catId']]['title'] = (isset($value['titles'][$this->clubDefaultLang])) ? $value['titles'][$this->clubDefaultLang] : $value['title'];
                    $catTitlesarray[$value['catId']]['fedFlag'] = $fedId == $value['catClubId'] ? 1 : 0;
                    $catTitlesarray[$value['catId']]['subfedFlag'] = $subFedId == $value['catClubId'] ? 1 : 0;
                }
                if ($value['catId'] == $systemCompany) {
                    $catTitlesarray[$value['catId']]['title'] = $this->get('translator')->trans('COMPANY');
                    $catTitlesarray[$value['catId']]['fedFlag'] = 0;
                    $catTitlesarray[$value['catId']]['subfedFlag'] = 0;
                }
                if ($value['catId'] == $systemAddress) {
                    $catTitlesarray[$value['catId']]['title'] = $this->get('translator')->trans('CONTACT_FIELD_ADDRESS');
                    $catTitlesarray[$value['catId']]['fedFlag'] = 0;
                    $catTitlesarray[$value['catId']]['subfedFlag'] = 0;
                }
            }
        }
        if ($fieldType == 'Company') {
            $catTitlesarray[$systemCompanyLogo]['title'] = $this->get('translator')->trans('DATA_COMPANY_LOGO');
            $catTitlesarray[$systemCompanyLogo]['fedFlag'] = 0;
            $catTitlesarray[$systemCompanyLogo]['subfedFlag'] = 0;
        }
        if ($fieldType == 'Single person') {
            $catTitlesarray[$systemCommunityPicture]['title'] = $this->get('translator')->trans('DATA_PROFILE_PICTURES');
            $catTitlesarray[$systemCommunityPicture]['fedFlag'] = 0;
            $catTitlesarray[$systemCommunityPicture]['subfedFlag'] = 0;
        }

        return $catTitlesarray;
    }

    /**
     * get club details array.
     *
     * @return type array
     */
    private function getClubArray()
    {
        $container = $this->container->getParameterBag();
        $clubIdArray = array('clubId' => $this->clubId,
            'federationId' => $this->federationId,
            'subFederationId' => $this->subFederationId,
            'clubType' => $this->clubType,
            'correspondanceCategory' => $container->get('system_category_address'),
            'invoiceCategory' => $container->get('system_category_invoice'),);
        $clubIdArray['address'] = $this->get('translator')->trans('CONTACT_FIELD_ADDRESS');
        $clubIdArray['sysLang'] = $this->clubDefaultLang;
        $clubIdArray['defSysLang'] = $this->clubDefaultSystemLang;
        $clubIdArray['clubLanguages'] = $this->clubLanguages;

        return $clubIdArray;
    }

    /**
     * Function to handle dropzone image array.
     *
     * @param \Clubadmin\ContactBundle\Controller\Request $request
     * @param string                                      $fieldType
     *
     * @return array
     */
    private function handleDropzoneImages($request, $fieldType)
    {
        $container = $this->container->getParameterBag();
        $systemCompanyLogo = $container->get('system_field_companylogo');
        $profilePictureTeam = $container->get('system_field_team_picture');
        $profilePictureClub = $container->get('system_field_communitypicture');
        //to handle the dropzone images
        if ($fieldType == 'Company') {
            if ($request->get("picture_$systemCompanyLogo")) {
                $dragFiles[$systemCompanyLogo] = $request->get("picture_$systemCompanyLogo", '');
            }
        } else {
            if ($request->get("picture_$profilePictureTeam")) {
                $dragFiles[$profilePictureTeam] = $request->get("picture_$profilePictureTeam", '');
            }
            if ($request->get("picture_$profilePictureClub")) {
                $dragFiles[$profilePictureClub] = $request->get("picture_$profilePictureClub", '');
            }
        }

        return $dragFiles;
    }

    /**
     * Function to get membership array.
     *
     * @return type
     */
    private function getMembershipArray($contactId)
    {
        //to manage the membership category dropdown based on federation/club/sub-fed
        $objMembershipPdo = new membershipPdo($this->container);
        $membersipFields = $objMembershipPdo->getMemberships($this->clubType, $this->clubId, $this->subFederationId, $this->federationId);
        $this->fedMemberships = array();
        $this->fedMembers = '';
        $club = $this->get('club');
        $clubDefaultLang = $club->get('default_lang');
        foreach ($membersipFields as $key => $memberCat) {
            $title = $memberCat['allLanguages'][$clubDefaultLang]['titleLang'] != '' ? $memberCat['allLanguages'][$clubDefaultLang]['titleLang'] : $memberCat['membershipName'];
            if ($this->federationId == $memberCat['clubId']) {
                $this->fedMemberships[] = $key;
                $this->fedMembers .= ':' . $key;
                $memberships['fed'][$key] = $title;
            } else {
                $memberships['club'][$key] = $title;
            }
        }

        return $memberships;
    }

    /**
     * function to get the contact name from its id.
     *
     * @param int $contactId the contact id
     * @param int $type      Type of contact
     *
     * @return array
     */
    private function contactDetails($contactId, $type = 'contact')
    {
        $club = $this->get('club');
        $contactlistClass = new Contactlist($this->container, '', $club, $type);
        $firstName = $this->container->getParameter('system_field_firstname');
        $contactlistClass->setColumns(array('contactName', 'ms.`2` as firstName', 'ms.`23` as lastName', 'contactname', 'contactid', 'clubId', 'is_company', 'has_main_contact', 'fed_membership_cat_id', 'club_membership_cat_id', 'has_main_contact_address', 'ms.`72` as gender', 'ms.`9` as companyName', 'ms.`1` as salutation', 'ms.`70` as title', 'ms.`47` as corresStrasse', 'ms.`71785` as corresPostfach', 'ms.`79` as corresPlz', 'ms.`77` as corresOrt', 'ms.`106` as corresLand', 'fg_cm_contact.main_club_id', 'fg_cm_contact.created_club_id', 'fg_cm_contact.is_fed_membership_confirmed', 'c.club_type as createdClubType'));
        $contactlistClass->setFrom('*');
        $contactlistClass->setCondition('Clubadmin');
        $sWhere = " fg_cm_contact.id=$contactId";
        $contactlistClass->addCondition($sWhere);
        $contactlistClass->addJoin(' LEFT JOIN fg_club c on fg_cm_contact.created_club_id= c.id');
        $listquery = $contactlistClass->getResult();
        $fieldsArray = $this->conn->fetchAll($listquery);

        return $fieldsArray[0];
    }

    /**
     * function to create the contact upload folders.
     *
     * @param int $clubId the club id
     */
    public function createContactFolder($clubId)
    {

        $uploadPathProfile = 'uploads/' . $clubId . '/contact/profilepic';
        $uploadPathCompany = 'uploads/' . $clubId . '/contact/companylogo';
        $this->get('fg.avatar')->createUploadDirectories($uploadPathProfile);
        $this->get('fg.avatar')->createUploadDirectories($uploadPathCompany);

        $folders = array('original', 'width_150', 'width_65');
        foreach ($folders as $folder) {
            $this->get('fg.avatar')->createUploadDirectories('uploads/' . $clubId . '/contact/profilepic/' . $folder,false);
            $this->get('fg.avatar')->createUploadDirectories('uploads/' . $clubId . '/contact/companylogo/' . $folder,false);
        }
        $this->get('fg.avatar')->createUploadDirectories('uploads/' . $clubId . '/contact/contactfield_file',false);
        $this->get('fg.avatar')->createUploadDirectories('uploads/' . $clubId . '/contact/contactfield_image',false);
        $this->get('fg.avatar')->createUploadDirectories('uploads/temp',false);

    }

    /**
     * Function to show pop up in contact overview page.
     *
     * @param \Clubadmin\ContactBundle\Controller\Request $request
     *
     * @return Template
     */
    public function editSettingsPopupAction(Request $request)
    {
        $type = $request->get('type');
        $activeFlag = $request->get('active');
        $contactTitle = $request->get('contactTitle');
        $contactId = $request->get('contactId');
        $existingFedMembershipId = $request->get('fedMembershipId');
        $existingClubMembershipId = $request->get('clubMembershipId');
        $popupDetails = $this->getPopupDetails($type, $contactTitle, $activeFlag);
        $fedId = $this->clubType == 'federation' ? $this->clubId : $this->federationId;
        $memberships = $this->getMembershipArray($contactId);
        $this->em->getRepository('CommonUtilityBundle:FgCmContact')->setContainer($this->container);
        if ($type == 'fedmembership') {
            $return = array('titleText' => $popupDetails['titleText'], 'descText' => $popupDetails['descText'], 'buttonText' => $popupDetails['buttonText'],
                'type' => $type, 'fedmembership' => array_keys($memberships['fed']), 'fed_memberships_array' => $memberships['fed'],
                'existingFedMembership' => $existingFedMembershipId, 'existingClubMembership' => $existingClubMembershipId, // PASS FROM OVERVIEW PAGE AS MembershipDetails[memberId]
                'fedlogoPath' => FgUtility::getClubLogo($fedId, $this->em),
                'contactId' => $contactId, 'active' => $activeFlag,
            );
        } elseif ($type == 'clubmembership') {
            $return = array('titleText' => $popupDetails['titleText'], 'descText' => $popupDetails['descText'], 'buttonText' => $popupDetails['buttonText'],
                'type' => $type, 'clubmembership' => array_keys($memberships['club']), 'club_memberships_array' => $memberships['club'],
                'existingFedMembership' => $existingFedMembershipId, 'existingClubMembership' => $existingClubMembershipId, // PASS FROM OVERVIEW PAGE AS MembershipDetails[memberId]
                'fedlogoPath' => FgUtility::getClubLogo($fedId, $this->em),
                'contactId' => $contactId, 'active' => $activeFlag,
            );
        } elseif ($type == 'clubAssignments') {
            $data = array();
            $data[] = array('id' => 'fromDate', 'data-edit-type' => 'date');
            $data[] = array('id' => 'toDate', 'data-edit-type' => 'date');
            $getClubAssignments = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getClubAssignments($contactId);
            $return = array('titleText' => $popupDetails['titleText'], 'descText' => $popupDetails['descText'], 'buttonText' => $popupDetails['buttonText'],
                'type' => $type, 'contactId' => $contactId, 'existingFedMembership' => $existingFedMembershipId, 'existingClubMembership' => $existingClubMembershipId, 'clubAssignments' => $getClubAssignments, 'active' => $activeFlag, 'inlineEditData' => $data,
            );
        } elseif ($type == 'subscription') {
            $return = array('titleText' => $popupDetails['titleText'], 'descText' => $popupDetails['descText'], 'buttonText' => $popupDetails['buttonText'],
                'type' => $type, 'contactId' => $contactId, 'existingFedMembership' => $existingFedMembershipId, 'existingClubMembership' => $existingClubMembershipId, 'active' => $activeFlag,
            );
        } else {
            $return = array('titleText' => $popupDetails['titleText'], 'descText' => $popupDetails['descText'], 'buttonText' => $popupDetails['buttonText'],
                'type' => $type, 'contactId' => $contactId, 'existingFedMembership' => $existingFedMembershipId, 'existingClubMembership' => $existingClubMembershipId, 'active' => $activeFlag,
            );
        }
        $clubService = $this->container->get('club');
        $return['clubMembershipAvailable'] = $clubService->get('clubMembershipAvailable');
        $return['fedMembershipMandatory'] = $clubService->get('fedMembershipMandatory');

        return $this->render('ClubadminContactBundle:ContactOverview:contactDetailsSettingsPopup.html.twig', $return);
    }

    /**
     * Function to get pop up details in contact overview page.
     *
     * @param string $type         pop up type(membership, internal_visible, internal_access)
     * @param string $contactTitle contact title
     * @param int    $activeFlag   active flag for selected pop up
     *
     * @return array
     */
    public function getPopupDetails($type, $contactTitle, $activeFlag)
    {
        switch ($type) {
            case 'fedmembership':
                $titleText = str_replace('%contact%', $contactTitle, $this->get('translator')->trans('OVERVIEW_FED_MEMBERSHIP_POPUP_TITLE'));
                $buttonText = $this->get('translator')->trans('SAVE');
                $descText = '';
                break;
            case 'clubmembership':
                $titleText = str_replace('%contact%', $contactTitle, $this->get('translator')->trans('OVERVIEW_MEMBERSHIP_POPUP_TITLE'));
                $buttonText = $this->get('translator')->trans('SAVE');
                $descText = '';
                break;
            case 'clubAssignments':
                $titleText = $this->get('translator')->trans('OVERVIEW_CLUB_ASSIGNMENTS');
                $buttonText = $this->get('translator')->trans('SAVE');
                $descText = '';
                break;
            case 'internal_visible':
                $titleText = $this->get('translator')->trans('OVERVIEW_INTERNAL_VISIBILITY_POPUP_TITLE');
                $descText = ($activeFlag == 1) ? str_replace('%contact%', $contactTitle, $this->get('translator')->trans('OVERVIEW_INTERNAL_VISIBILITY_POPUP_VISIBLE_TEXT')) : str_replace('%contact%', $contactTitle, $this->get('translator')->trans('OVERVIEW_INTERNAL_VISIBILITY_POPUP_INVISIBLE_TEXT'));
                $buttonText = ($activeFlag == 1) ? $this->get('translator')->trans('OVERVIEW_INTERNAL_VISIBILITY_POPUP_ACTIVATE_BUTTON_TEXT') : $this->get('translator')->trans('OVERVIEW_INTERNAL_VISIBILITY_POPUP_DEACTIVATE_BUTTON_TEXT');
                break;
            case 'subscription':
                $titleText = $this->get('translator')->trans('OVERVIEW_NL_SUBSCRIPTION_POPUP_TITLE');
                $descText = ($activeFlag == 1) ? str_replace('%contact%', $contactTitle, $this->get('translator')->trans('OVERVIEW_NL_SUBSCRIPTION_POPUP_UNSUBSCRIBE_TEXT')) : str_replace('%contact%', $contactTitle, $this->get('translator')->trans('OVERVIEW_NL_SUBSCRIPTION_POPUP_SUBSCRIBE_TEXT'));
                $buttonText = ($activeFlag == 1) ? $this->get('translator')->trans('OVERVIEW_NL_SUBSCRIPTION_POPUP_UNSUBSCRIBE_BUTTON_TEXT') : $this->get('translator')->trans('OVERVIEW_NL_SUBSCRIPTION_POPUP_SUBSCRIBE_BUTTON_TEXT');
                break;
            default:
                $titleText = $this->get('translator')->trans('OVERVIEW_INTERNAL_ACCESS_POPUP_TITLE');
                $descText = ($activeFlag == 1) ? str_replace('%contact%', $contactTitle, $this->get('translator')->trans('OVERVIEW_INTERNAL_ACCESS_POPUP_BLOCK_TEXT')) : str_replace('%contact%', $contactTitle, $this->get('translator')->trans('OVERVIEW_INTERNAL_ACCESS_POPUP_ALLOW_TEXT'));
                $buttonText = ($activeFlag == 1) ? $this->get('translator')->trans('OVERVIEW_INTERNAL_ACCESS_POPUP_BLOCK_BUTTON_TEXT') : $this->get('translator')->trans('OVERVIEW_INTERNAL_ACCESS_POPUP_ALLOW_BUTTON_TEXT');
        }
        $detailsArray = array('titleText' => $titleText, 'descText' => $descText, 'buttonText' => $buttonText);

        return $detailsArray;
    }

    /**
     * Function to save contact profile data in overview page.
     *
     * @param \Clubadmin\ContactBundle\Controller\Request $request
     *
     * @return JsonResponse
     */
    public function saveContactProfileDataAction(Request $request)
    {
        $systemPersonal = $this->container->getParameter('system_category_personal');
        $systemCompany = $this->container->getParameter('system_category_company');
        $systemFirstName = $this->container->getParameter('system_field_firstname');
        $systemLastName = $this->container->getParameter('system_field_lastname');
        $systemCompanyName = $this->container->getParameter('system_field_companyname');
        $systemPrimaryEmail = $this->container->getParameter('system_field_primaryemail');
        $systemCommunication = $this->container->getParameter('system_category_communication');

        $type = $request->get('type');
        $contactId = $request->get('contactId');
        $isActive = $request->get('isActive');
        $memberships = $this->getMembershipArray($contactId);
        $federationMemberships = array_keys($memberships['fed']);
        $clubMemberships = array_keys($memberships['club']);
        if ($type == 'fedmembership') {
            $fedMembershipId = $request->get('fedmembership');
        } elseif ($type == 'clubmembership') {
            $clubMembershipId = $request->get('clubmembership');
        } elseif ($type == 'clubAssignments') {
            $mainclubVal = $request->get('mainclubVal');
        } else {
            $fedMembershipId = $request->get('existingFedMembership');
            $clubMembershipId = $request->get('existingClubMembership');
        }

        $editData = $this->getContactData($contactId, 'contact');
        /* Array for saving the stealth mode and Intranet access is generated */
        if ($type != 'fedmembership' && $type != 'clubmembership') {
            $attributeArray = array();
            $flagIndex = ($type == 'internal_access') ? 'stealthFlag' : 'intranet_access';
            $pushValue1 = ($type == 'internal_access') ? 'Stealth mode' : 'Intranet access';
            $pushValue2 = ($type == 'internal_access') ? 'Intranet access' : 'Stealth mode';

            if ($editData[0][$flagIndex] == 1) {
                array_push($attributeArray, $pushValue1);
            }
            if ($isActive != 1) {
                array_push($attributeArray, $pushValue2);
            }
            if ($editData[0]['is_sponsor'] != '0') {
                array_push($attributeArray, 'Sponsor');
            }
        }
        /* Ends here */
        $fieldType = ($editData[0]['is_company'] == 1) ? 'Company' : 'Single person';
        $clubIdArray = $this->getClubArray();
        $fieldDetails1 = $this->em->getRepository('CommonUtilityBundle:FgCmAttributeset')->getAllClubContactFields($clubIdArray, $this->conn, 0, $fieldType);
        $fieldDetails = $this->em->getRepository('CommonUtilityBundle:FgCmAttributeset')->fieldItrator($fieldDetails1);
        $fieldDetailArray = array('fieldType' => $fieldType, 'memberships' => $memberships, 'clubIdArray' => $clubIdArray, 'fedMemberships' => $federationMemberships, 'clubMemberships' => $clubMemberships, 'selectedFedMembership' => $fedMembershipId, 'selectedClubMembership' => $clubMembershipId, 'contactId' => $contactId);
        $fieldDetailsFinal = array_merge($fieldDetails, $fieldDetailArray);
        $formArray = array();
        if ($type == 'fedmembership') {
            $formArray = array('system' => array('contactType' => $fieldType, 'fedMembership' => $fedMembershipId));
        } elseif ($type == 'clubmembership') {
            $formArray = array('system' => array('contactType' => $fieldType, 'membership' => $clubMembershipId));
        }
        if ($type != 'fedmembership' && $type != 'clubmembership') {
            if (!empty($attributeArray)) {
                $formArray['system']['attribute'] = $attributeArray;
            }
            if (!array_key_exists('attribute', $formArray['system'])) {
                $formArray['system']['attribute'][0] = '';
            }
        }
        //for log contact name
        if ($fieldType == 'Company') {
            $formArray[$systemCompany][$systemCompanyName] = $editData[0][$systemCompanyName];
        } else {
            $formArray[$systemPersonal][$systemLastName] = $editData[0][$systemLastName];
            $formArray[$systemPersonal][$systemFirstName] = $editData[0][$systemFirstName];
        }
        $formArray[$systemCommunication][$systemPrimaryEmail] = $editData[0][$systemPrimaryEmail];
        $flashMessage = ($type == 'fedmembership' || $type == 'clubmembership') ? $this->get('translator')->trans('OVERVIEW_MEMBERSHIP_POPUP_SAVE_SUCCESS_MESSAGE') : (($type == 'internal_visible') ? $this->get('translator')->trans('OVERVIEW_POPUP_INTERNAL_VISIBILITY_SAVE_SUCCESS_MESSAGE') : $this->get('translator')->trans('OVERVIEW_POPUP_INTERNAL_AREA_ACCESS_SAVE_SUCCESS_MESSAGE'));
        if ($type == 'clubAssignments') {
            $this->em->getRepository('CommonUtilityBundle:FgCmContact')->saveClubAssignments($this->clubId, $mainclubVal, $contactId);
        } elseif ($type == 'subscription') {
            $fgContact = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->find($contactId);
            $logData = array('club_id' => $this->clubId, 'contactId' => $contactId, 'kind' => 'system', 'field' => 'newsletter', 'value_before' => '', 'value_after' => ($isActive == 1) ? $this->get('translator')->trans('LOG_UNSUBSCRIBE') : $this->get('translator')->trans('LOG_SUBSCRIBE'));
            if ($isActive == 1) {
                $fgContact->setIsSubscriber(0);
                $flashMessage = $this->get('translator')->trans('OVERVIEW_NL_SUBSCRIPTION_UNSUBSCRIBE_SUCCESS_MESSAGE');
            } else {
                $fgContact->setIsSubscriber(1);
                $flashMessage = $this->get('translator')->trans('OVERVIEW_NL_SUBSCRIPTION_SUBSCRIBE_SUCCESS_MESSAGE');
            }
            $this->em->flush();
            $this->em->getRepository('CommonUtilityBundle:FgCmContact')->updateLastUpdated($contactId, 'id');
            $this->em->getRepository('CommonUtilityBundle:FgCmChangeLog')->insertLogEntry($logData, $this->contactId);
        } else {
            $contact = new ContactDetailsSave($this->container, $fieldDetailsFinal, $editData, $contactId); //$container,$fieldDetails,$editData,$fedMemberships
            $contactIdNew = $contact->saveContact($formArray, array());
        }

        return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $flashMessage));
    }

    /**
     * Function to get contact data.
     *
     * @param int $contactId contact id
     * @param string $module    contact module

     * @return type array
     */
    private function getContactData($contactId, $module)
    {
        $pdo = new ContactPdo($this->container);
        $editData = $pdo->getContactDetailsForMembershipDetails($module, $contactId);
        if ($editData['0']['is_deleted']) {
            $this->get('club')->set('moduleMenu', 'contactarchive');
        }
        $this->setContactModuleMenu($contactId, $module);
        $editData[0]['is_company'] = $editData[0]['Iscompany'];
        $editData[0]['contactClubId'] = $editData[0]['contactclubid'];
        $editData[0]['is_stealth_mode'] = $editData[0]['stealthFlag'];
        $editData[0]['club_membership_cat_id'] = $editData[0]['clubMembershipId'];

        return $editData;
    }

    /**
     * Function to get contact data.
     *
     * @param object $conn connection obj
     * @param string $type contact module
     * @param int    $contactId contact id

     * @return type array
     */
    public function getContactDetails($conn, $type, $contactId)
    {
        if (!is_numeric($contactId)) {
            return false;
        }
        $club = $this->get('club');
        $contactlistClass = new Contactlist($this->container, $contactId, $club, $type);
        $contactlistClass->setColumns(array('contactName', 'isMember', 'gender', 'isCompany', 'fed_membership_cat_id', 'club_membership_cat_id', 'mc.is_sponsor', 'mc.is_subscriber', 'mc.intranet_access', 'is_company', '`68`', '`5`', '`21`', 'stealthMode', 'contactclubid'));
        $contactlistClass->setFrom('*');
        $contactlistClass->setCondition();
        $sWhere = " fg_cm_contact.id=$contactId";
        $contactlistClass->addCondition($sWhere);
        $listquery = $contactlistClass->getResult();
        $fieldsArray = $conn->fetchAll($listquery);

        return $fieldsArray;
    }

    /**
     * Function to get details for profile block.
     *
     * @param int    $contact contact id
     * @param string $module  contact module
     *
     * @return JsonResponse
     */
    public function getDetailsForProfileBlockAction($contact, $module)
    {
        $contactDetails = $this->getContactData($contact, $module);
        $contactData = $contactDetails[0];
        $fedId = $this->clubType == 'federation' ? $this->clubId : $this->federationId;
        $profileImgField = $this->container->getParameter('system_field_communitypicture');
        $companyLogoField = $this->container->getParameter('system_field_companylogo');
        $isCompany = $contactData['Iscompany'];
        $contactImageName = ($isCompany == 1) ? $contactData[$companyLogoField] : $contactData[$profileImgField];
        $contactImage = '';
        if ($contactImageName != '') {
            $pathService = $this->container->get('fg.avatar');
            $contactImage = $pathService->getAvatar($contactData['id'], 150);
        }
        $contactData['hasImage'] = ($contactImage == '') ? 0 : 1;
        $contactData['contactImage'] = $contactImage;
        $contactData['fedLogoPath'] = FgUtility::getClubLogo($fedId, $this->em);
        $contactData['currentClub'] = $this->clubId;
        $contactData['readOnlyUser'] = (in_array('ROLE_READONLY_CONTACT', $this->get('contact')->get('allRights'))) ? 1 : 0;
        $contactData['newsletterText'] = ($contactData['is_subscriber'] == 1) ? $this->get('translator')->trans('OVERVIEW_NEWSLETTER_SUBSCRIPTION_TEXT') : $this->get('translator')->trans('OVERVIEW_NEWSLETTER_NO_SUBSCRIPTION_TEXT');
        if ($module == 'contact') {
            $contactData['bookedModules'] = $this->get('club')->get('bookedModulesDet');
            $contactData['sponsorText'] = ($contactData['is_sponsor'] == 1) ? $this->get('translator')->trans('OVERVIEW_SPONSOR_TEXT') : $this->get('translator')->trans('OVERVIEW_NO_SPONSOR_TEXT');
            $contactData['intranetAccessText'] = ($contactData['intranet_access'] == 1) ? $this->get('translator')->trans('OVERVIEW_INTERNAL_AREA_ACCESS_TEXT') : $this->get('translator')->trans('OVERVIEW_INTERNAL_AREA_NO_ACCESS_TEXT');
            $contactData['stealthModeText'] = ($contactData['stealthFlag'] == 1) ? $this->get('translator')->trans('OVERVIEW_INTERNAL_AREA_INVISIBLE_TEXT') : $this->get('translator')->trans('OVERVIEW_INTERNAL_AREA_VISIBILE_TEXT');
        }

        return new JsonResponse($contactData);
    }

    /**
     * Function to set connection box visibility in contact overview page.
     *
     * @param int $connectionBoxDisplayFlag visibility of connection box in overview settings
     * @param int $houseHoldBoxFlag         visibility of household connections
     * @param int $mainContactFlag          visibility of main contact connections
     * @param int $otherConnectionsFlag     visibility of other connections
     * @param int $householdConnCnt         household connections count
     * @param int $otherConnCnt             other connections count
     * @param int $maincontCnt              main contact connections count
     *
     * @return int
     */
    private function getconnectionBoxVisilbility($connectionBoxDisplayFlag, $houseHoldBoxFlag, $mainContactFlag, $otherConnectionsFlag, $householdConnCnt, $otherConnCnt, $maincontCnt)
    {
        if ($connectionBoxDisplayFlag == 1) {
            if (($householdConnCnt > 0 && $houseHoldBoxFlag == 1) || ($mainContactFlag == 1 && $maincontCnt > 0) || ($otherConnectionsFlag == 1 && $otherConnCnt > 0)) {
                $connectionBoxVisibility = 1;
            } else {
                $connectionBoxVisibility = 0;
            }
        } else {
            $connectionBoxVisibility = 0;
        }

        return $connectionBoxVisibility;
    }

    /**
     * Function to show pop up in contact overview page for editing club assignments.
     *
     * @param \Clubadmin\ContactBundle\Controller\Request $request
     *
     * @return type
     */
    public function editClubAssignmentsPopupAction(Request $request)
    {
        $type = $request->get('type');
        $activeFlag = $request->get('active');
        $contactTitle = $request->get('contactTitle');
        $contactId = $request->get('contactId');
        $existingFedMembershipId = $request->get('fedMembershipId');
        $existingClubMembershipId = $request->get('clubMembershipId');
        $popupDetails = $this->getPopupDetails($type, $contactTitle, $activeFlag);
        $fedId = $this->clubType == 'federation' ? $this->clubId : $this->federationId;
        $memberships = $this->getMembershipArray($contactId);
        if ($type == 'fedmembership') {
            $return = array('titleText' => $popupDetails['titleText'], 'descText' => $popupDetails['descText'], 'buttonText' => $popupDetails['buttonText'],
                'type' => $type, 'fedmembership' => array_keys($memberships['fed']), 'fed_memberships_array' => $memberships['fed'],
                'existingFedMembership' => $existingFedMembershipId, // PASS FROM OVERVIEW PAGE AS MembershipDetails[memberId]
                'fedlogoPath' => FgUtility::getClubLogo($fedId, $this->em),
                'contactId' => $contactId, 'active' => $activeFlag,
            );
        } elseif ($type == 'clubmembership') {
            $return = array('titleText' => $popupDetails['titleText'], 'descText' => $popupDetails['descText'], 'buttonText' => $popupDetails['buttonText'],
                'type' => $type, 'clubmembership' => array_keys($memberships['club']), 'club_memberships_array' => $memberships['club'],
                'existingClubMembership' => $existingClubMembershipId, // PASS FROM OVERVIEW PAGE AS MembershipDetails[memberId]
                'fedlogoPath' => FgUtility::getClubLogo($fedId, $this->em),
                'contactId' => $contactId, 'active' => $activeFlag,
            );
        } else {
            $return = array('titleText' => $popupDetails['titleText'], 'descText' => $popupDetails['descText'], 'buttonText' => $popupDetails['buttonText'],
                'type' => $type, 'contactId' => $contactId, 'existingFedMembership' => $existingFedMembershipId, 'existingClubMembership' => $existingClubMembershipId, 'active' => $activeFlag,
            );
        }

        return $this->render('ClubadminContactBundle:ContactOverview:contactDetailsSettingsPopup.html.twig', $return);
    }

    /**
     * function to validate and edit club assignment entry.
     *
     * @param type $contact ContactId
     *
     * @return JsonResponse
     */
    public function editClubAssignmentDateAction($contact)
    {
        $clubAssignmentId = $_POST['rowId'];
        $field = $_POST['colId'];
        $value = $_POST['value'];
        $value = trim($value);

        $clubArray = array('clubType' => $this->clubType, 'clubId' => $this->clubId, 'subFederationId' => $this->subFederationId, 'federationId' => $this->federationId);
        $clubAssignmentValidatorObj = new FgClubAssignmentValidator($this->container, $contact, $clubAssignmentId, $field, $value, $clubArray);
        $output = $clubAssignmentValidatorObj->validateClubAssignmentData();
        if ($output['valid'] == 'true') {
            switch ($field) {
                case 'fromDate':
                    if ($value != '') {
                        $date = strtotime($value);
                        if ($date !== false) {
                            $fromDate = date('Y-m-d H:i:s', $date);
                            $clubObj = new ClubPdo($this->container);
                            $clubObj->updateClubAssignmentDate($clubAssignmentId, 'from_date', $fromDate);
                        }
                    }
                    break;

                case 'toDate':
                    if ($value != '') {
                        $date = strtotime($value);
                        if ($date !== false) {
                            $toDate = date('Y-m-d H:i:s', $date);
                            $clubObj = new ClubPdo($this->container);
                            $clubObj->updateClubAssignmentDate($clubAssignmentId, 'to_date', $toDate);
                        }
                    }
                    break;
                default:
                    break;
            }
        }

        return new JsonResponse($output);
    }
}
