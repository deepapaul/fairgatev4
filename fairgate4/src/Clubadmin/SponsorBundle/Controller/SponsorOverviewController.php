<?php

/**
 * SponsorOverviewController.
 *
 * This controller was created for handlying oevrview data
 *
 * @author     pitsolutions.ch
 *
 * @version    Fairgate V4
 */
namespace Clubadmin\SponsorBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Common\UtilityBundle\Entity\FgCmOverviewSettings;
use Clubadmin\Util\Contactlist;
use Clubadmin\ContactBundle\Util\ContactOverviewConfig;
use Symfony\Component\Intl\Intl;
use Clubadmin\ContactBundle\Util\ContactDetailsAccess;
use Clubadmin\SponsorBundle\Util\NextpreviousSponsor;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Util\FgPermissions;
use Symfony\Component\HttpFoundation\Request;

/**
 * SponsorOverview controller for handlying overview and settings.
 *
 * @author PITSolutions <pit@pitsolutions.com>
 */
class SponsorOverviewController extends FgController
{
    /**
     * Function is used to display Sponsor overview settings.
     *
     * @return template
     */
    public function indexAction()
    {   
        return $this->render('ClubadminSponsorBundle:SponsorOverview:index.html.twig', array('clubId' => $this->clubId));
    }

    /**
     * Function is used to get all the array values to display in the Sponsor overview settings page.
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
        $overviewSettings = $this->em->getRepository('CommonUtilityBundle:FgCmOverviewSettings')->getOverviewSettings($this->clubId, 'sponsor');
        $overviewSettings = json_decode($overviewSettings['settings'], true);

        $displayedArray = array();
        /*
         * Checking if the saved overview setting is empty. If then we just need to use the base array structure for the settings to display.
         * Othervise need to compare the saved data with the base structure.
         */
        if (empty($overviewSettings)) {
            $return['displayedArray'] = $finalArray;

            return new JsonResponse($return);
        } else {
            $displayedArray = $contactOverviewConfig->overviewSettingsArrayLoop($overviewSettings, $finalArray);
        }
        $return['displayedArray'] = $displayedArray;

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

        $fgCmOverviewSettingsArray = $this->em->getRepository('CommonUtilityBundle:FgCmOverviewSettings')->getOverviewSettings($this->clubId, 'sponsor');

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
            $fgCmOverviewSettings->setType('sponsor');
        }

        $this->em->persist($fgCmOverviewSettings);
        $this->em->flush();

        return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('SPONSOR_OVERVIEW_SETTINGS_SAVED')));
    }

    /**
     * function to get all the array structure.
     *
     * @param array  $contactOverviewConfig    Sponsor overview congif class object
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
        //The bookedModulesDet variable contains all the modules booked by the club
        $bookedModules = $this->bookedModulesDet;

        $fieldResultArrayWithSort = $contactOverviewConfig->contactFieldArray($fieldDetails, $commonSortOrder, $defaultEnabledCategories, $personalCategoryFieldIds, $this->clubLanguages);
        $commonSortOrder = $fieldResultArrayWithSort['commonSortOrder'];

        //Service assignments are getting from database in a formated way
        $serviceAssignments = $contactOverviewConfig->getServiceAssignmentsArray($this->clubId, $commonSortOrder);

        //To get correspondence and invoice address in the desired format for address block to display in the overview
        $addressBlockArray = $contactOverviewConfig->getFormatedAddressBlock($commonSortOrder);

        //Function is used for getting formated base structure of notes details
        $notesArray = $contactOverviewConfig->getFormatedNotesDetails($commonSortOrder);
        $allArray = array('resultArray' => $fieldResultArrayWithSort['resultArray'], 'serviceAssignments' => $serviceAssignments, 'addressBlockArray' => $addressBlockArray, 'notesArray' => $notesArray);

        return $allArray;
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
        $finalArray = $federationInfosArray + $allArrayStructure['resultArray'] + $allArrayStructure['serviceAssignments'] + $allArrayStructure['addressBlockArray'] + $allArrayStructure['notesArray'];

        return $finalArray;
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
        $contactlistClass->setColumns(array('contactName', 'ms.`2` as firstName', 'ms.`23` as lastName', 'contactname', 'contactid', 'clubId', 'is_company', 'has_main_contact', 'fed_membership_cat_id', 'club_membership_cat_id', 'has_main_contact_address', 'ms.`72` as gender', 'ms.`9` as companyName', 'ms.`1` as salutation', 'ms.`70` as title', 'ms.`47` as corresStrasse', 'ms.`71785` as corresPostfach', 'ms.`79` as corresPlz', 'ms.`77` as corresOrt', 'ms.`106` as corresLand', 'fg_cm_contact.main_club_id', 'fg_cm_contact.created_club_id','fg_cm_contact.is_fed_membership_confirmed'));
        $contactlistClass->setFrom('*');
        $contactlistClass->setCondition();
        $sWhere = " fg_cm_contact.id=$contactId";
        $contactlistClass->addCondition($sWhere);
        $listquery = $contactlistClass->getResult();
        $fieldsArray = $this->conn->fetchAll($listquery);

        return $fieldsArray[0];
    }

    /**
     * function to display the contact overview according to each contact.
     *
     * @param int $offset  the offset
     * @param int $sponsor the contact id
     *
     * @return json
     */
    public function displaySponsorOverviewAction($offset, $sponsor)
    {
        $accessObj = new ContactDetailsAccess($sponsor, $this->container, 'sponsor');
          // Checking whether the user have the access to this page
        if ($accessObj->accessType == 'NO_ACCESS' || !in_array('overview', $accessObj->tabArray)) {
            $permissionObj = new FgPermissions($this->container); 
            $permissionObj->checkClubAccess('','no_access');
        }
        $clubService = $this->container->get('club');
        $terminologyService = $this->get('fairgate_terminology_service');
        $contactOverviewConfig = new ContactOverviewConfig($this->container, $terminologyService);
        $clubIdArray = array('clubId' => $this->clubId, 'federationId' => $this->federationId, 'subFederationId' => $this->subFederationId, 'clubType' => $this->clubType);

        // Function to get basic combined array structure
        $finalArray = $this->getCombinedOverviewStructure($clubIdArray);

        // Getting saved overview settings from database
        $overviewSettings = $this->em->getRepository('CommonUtilityBundle:FgCmOverviewSettings')->getOverviewSettings($this->clubId, 'sponsor');
        $overviewSettings = json_decode($overviewSettings['settings'], true);

        $currentContactName = $this->contactDetails($sponsor);

        /*
         *  Checking whether the saved overview settings is empty. If yes, we will generate overview from the base overview array.
         *  Otherwise we will loop the base structure and the saved settings and get the desired values.
         */
        if (empty($overviewSettings)) {
            $generateOverview = $contactOverviewConfig->generateOverviewFromBase($currentContactName, $finalArray, $isFedMembership = null, $this->clubId);
        } else {
            $generateOverview = $contactOverviewConfig->generateOverviewFromSettings($currentContactName, $overviewSettings, $finalArray, $isFedMembership, $this->clubId);
        }

        // Calling contact list class to get the value of each field inside each category to display in the overview.
        $contactlistDatas = $contactOverviewConfig->getContactFieldValuesForOverview($generateOverview['contentArrayForQuery'], $sponsor, $this->contactId);

        // Built-in function to get country and language list
        $countryList = Intl::getRegionBundle()->getCountryNames();
        $languages = Intl::getLanguageBundle()->getLanguageNames();

        $addressBlockCorrespondence = $contactOverviewConfig->getAddressBlockArray($currentContactName, $countryList);
        $this->em->getRepository('CommonUtilityBundle:FgCmContact')->setContainer($this->container);
        $getMemberShip = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getMembershipDetails($sponsor);
        $getMembershipDetails = json_encode($getMemberShip);
        $getClubAssignments = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getClubAssignments($sponsor);
        //collect the count of active assignment
        $getActiveAssignmentCount = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getActiveClubAssignments($sponsor);        
        
        $getAllSubFederations = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getAllSubFederations($sponsor);
        
        $serviceAssignmentBlock = $contactOverviewConfig->getSponsorOverviewServiceAssignments($this->conn, $this->clubId, $sponsor);
        
        // Getting all notes of the user. Also the count is generated to display in the overview tab
        $notesArray = $contactOverviewConfig->getAllNotes($this->clubId, $sponsor);
        $getAllNotes = json_encode($notesArray['allNotes']);

        $contactOverview = json_encode($generateOverview['contactOverview']);
        $contactlistDatas = json_encode($contactlistDatas[0]);

        // Generating next and previous data for the next-previous functionality in the overview page
        $nextprevious = new NextpreviousSponsor($this->container);
        $nextPreviousResultset = $nextprevious->nextPreviousSponsorData($this->contactId, $sponsor, $offset, 'render_sponsor_overview', 'offset', 'sponsor', $flag = 0);

        $returnArray = array('contactType' => $currentContactName['is_company'], 'languages' => json_encode($languages), 
                            'nextPreviousResultset' => $nextPreviousResultset, 'countryList' => json_encode($countryList), 
                            'mainClubId' => $this->clubId, 'clubType' => $this->clubType, 'breadCrumb' => '', 
                            'contactId' => $sponsor, 'contactClubId' => $currentContactName['clubId'], 
                            'contactName' => $currentContactName['contactName'], 
                            'displayedUserName' => $currentContactName['contactname'], 'contactOverview' => $contactOverview, 
                            'getAllNotes' => $getAllNotes, 'offset' => $offset, 'fieldResultArray' => $contactlistDatas,
                            'addressBlockCorrespondence' => $addressBlockCorrespondence, 
                            'getMembershipDetails' => $getMembershipDetails, 'serviceAssignmentBlock' => $serviceAssignmentBlock,
                            'isFedMemberConfirmed' => $currentContactName['is_fed_membership_confirmed'],
                            'getClubAssignments'=>json_encode($getClubAssignments), 'getSubfederations' => json_encode($getAllSubFederations),
                            'fedMembershipMandatory' => $clubService->get('fedMembershipMandatory'),
                            'clubMembershipAvailable' => $clubService->get('clubMembershipAvailable'),
                            'fedMembershipId' => $getMemberShip['fedMembershipId'], 'clubMembershipId' => $getMemberShip['clubMembershipId'],
                            'createdClubId' => $currentContactName['created_club_id'],'contactClubId' => $currentContactName['clubId'],
                            'clubAssignmentCount'=> $getActiveAssignmentCount
                            );
        $contCountDetails = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getContactCountDetails($this->clubId, $sponsor, $currentContactName['is_company'], $this->clubType, true, true, true, false, false, false, false, $this->federationId, $this->subFederationId);
        $return = array_merge($returnArray, $contCountDetails);
        $return['servicesCount'] = $this->em->getRepository('CommonUtilityBundle:FgSmBookings')->getCountOfSponsorServices($this->clubId, $sponsor);
        $return['adsCount'] = $this->em->getRepository('CommonUtilityBundle:FgSmSponsorAds')->getCountOfSponsorAds($this->clubId, $sponsor);
        $return['documentsCount'] = $this->em->getRepository('CommonUtilityBundle:FgDmAssigment')->getCountOfAssignedDocuments('CONTACT', $this->clubId, $sponsor);
        
        $contCountDetails['servicesCount'] =   $return['servicesCount'];
        $contCountDetails['adsCount'] =  $return['adsCount'];
        $contCountDetails['documentsCount'] =  $return['documentsCount'];
       
        $return['tabs'] = FgUtility::getTabsArrayDetails($this->container, $accessObj->tabArray, $offset, $sponsor, $contCountDetails, "overview", "sponsor");
 
        return $this->render('ClubadminSponsorBundle:SponsorOverview:displaySponsorOverview.html.twig', $return);
    }
}
