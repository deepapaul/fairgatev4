<?php

/**
 * LogController
 *
 * This controller was created for listing logentries
 *
 * @package    ClubadminContactBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
namespace Clubadmin\ContactBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Intl\Intl;
use Clubadmin\ContactBundle\Util\ContactDetailsAccess;
use Clubadmin\ContactBundle\Util\FgMembershipValidator;
use Clubadmin\ContactBundle\Util\NextpreviousContact;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Util\FgSettings;
use Common\UtilityBundle\Repository\Pdo\membershipPdo;
use Common\UtilityBundle\Repository\Pdo\ContactPdo;

/**
 * Manage contact listing related functionality
 */
class LogController extends FgController
{

    /**
     * Function for listing log entries
     * 
     * @param int $offset  Offset
     * @param int $contact ContactId
     *
     * @return object View Template Render Object
     */
    public function indexAction($offset, $contact)
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();

        $accessObj = $this->checkAccess($contact);
        $contactType = $accessObj->contactviewType;
        //get contact details 
        $dataSet1 = $this->getContactData($contact, $contactType);
        $contactData = $dataSet1['contactData'];
        //get membership detials
        $dataSet2 = $this->getMembershipDetails(); 
        //get tab and count details
        $activeTabName = $request->get('activeTab', 'data');
        $dataSet3 = $this->getTabDetails($accessObj, $activeTabName, $contact, $contactType, $offset, $contactData['is_company']);
        //data sets to be passed to twig
        $dataSet = array('contactId' => $contact, 'offset' => $offset, 'clubType' => $this->clubType);
        $dataSet['fedmembershipEdit'] = ($this->clubType == 'federation') ? 1 : 0;
        //translate functions
        $dataSet['transKindFields'] = $this->transKindFields();
        $dataSet['transArr'] = $this->transArr();
        $dataSet['countryList'] = Intl::getRegionBundle()->getCountryNames();
        $dataSet['languageList'] = Intl::getLanguageBundle()->getLanguageNames();
        $dataSet['languageAttrIds'] = array($this->container->getParameter('system_field_corress_lang'));
        $dataSet['countryAttrIds'] = array($this->container->getParameter('system_field_corres_land'), $this->container->getParameter('system_field_invoice_land'), $this->container->getParameter('system_field_nationality1'), $this->container->getParameter('system_field_nationality2'));
        $dataSet['sysAttrTransIds'] = array($this->container->getParameter('system_field_gender'), $this->container->getParameter('system_field_salutaion'));
        // Generating next and previous data for the next-previous functionality in the overview page
        $nextprevious = new NextpreviousContact($this->container);
        $dataSet['$nextPreviousResultset'] = $nextprevious->nextPreviousContactData($this->contactId, $contact, $offset, 'log_listing', 'offset', 'contact', $flag = 0);
        $dataSet['missingReqAssgment'] = $this->em->getRepository('CommonUtilityBundle:FgRmCategory')->missingReqFedAssign($contact, $this->clubId, $this->federationId, $this->subFederationId, $this->clubType, $this->clubDefaultLang, $this->conn);
        $dataSet['federationId'] = ($this->clubType == 'federation') ? $this->clubId : $this->federationId;
        $dataSet['isMembershipEditable'] = ($this->container->get('club')->get('module') == 'contact' && !in_array('contact', $this->loggedUserRoles)) ? 0 : ($contactData['club_id'] == $this->clubId) ? 1 : 0;
        $groupUserDetails = $this->em->getRepository('CommonUtilityBundle:SfGuardGroup')->getGroupDetails($this->conn, $this->clubId, $contact);
        $dataSet['hasUserRights'] = (count($groupUserDetails) > 0) ? 1 : 0;
        $dataSet['isReadOnlyContact'] = $this->isReadOnlyContact();
        $dataSet['clubType'] = $this->clubType;
        $dataSet['clubLogo'] = '<img src="' . FgUtility::getClubLogo($this->federationId, $this->em) . '">';
        $returnArray = array_merge($dataSet, $dataSet1, $dataSet2, $dataSet3);

        return $this->render('ClubadminContactBundle:Log:index.html.twig', $returnArray);
    }
    
    /**
     * Function to get contact -all logs
     *
     * @param int $contact contactId
     * 
     * @return JsonResponse
     */
    public function logDatasAction($contact)
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $fedContactId = $request->get('fed_contact_id');
        $subfedContactId = $request->get('subfed_contact_id');

        $club = $this->container->get('club');
        $clubDetails = array('clubId' => $this->clubId, 'clubType' => $this->clubType, 'clubHeirarchy' => $club->get('clubHeirarchy'), 'clubDefaultLang' => $this->clubDefaultLang);
        $federationId = $this->clubType == 'federation' ? $this->clubId : $this->federationId;
        $logEntriesDataTab = $this->getFieldLogEntries($clubDetails, $contact, $fedContactId, $subfedContactId);
        $contactPdo = new ContactPdo($this->container);
        $logEntriesAssignmentTab = $contactPdo->getAssignmentLogEntries($clubDetails, $contact, $fedContactId, $subfedContactId);
        $logEntriesConnectionTab = $contactPdo->getConnectionLogEntries($clubDetails, $contact);

        $memberShipTabDetails = $this->getMembershipTabDetails($clubDetails, $contact, $federationId, $fedContactId);

        $logEntriesSystemTab = $contactPdo->getSystemLogEntries($clubDetails, $contact, $fedContactId, $club);

        $logEntriesNotesTabDetails = $this->getNotesLogEntries($clubDetails, $contact);
        $logEntriesCommunicationTab = $contactPdo->getCommunicationLogEntries($contact, $clubDetails);

        $output['aaData'] = array('data' => $logEntriesDataTab, 'assignments' => $logEntriesAssignmentTab,
            'connections' => $logEntriesConnectionTab, 'membership' => $memberShipTabDetails['membership'],
            'communication' => $logEntriesCommunicationTab, 'notes' => $logEntriesNotesTabDetails,
            'system' => $logEntriesSystemTab, 'contact' => $contact, 'fed_membership' => $memberShipTabDetails['fed_membership']);

        return new JsonResponse($output);
    }
    
    /**
     * function to do the membership delete
     *
     * @return JsonResponse
     */
    public function membershipLogDeleteAction()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $fromDate = $request->get('from');
        $toDate = $request->get('to');
        $membershipId = $request->get('membershipid');
        $contactId = $request->get('contactId');
        $currentMembershipLogId = $request->get('currentmembershipLogId');
        $todelLogId = $request->get('todelLogId');
        $contactPdo = new ContactPdo($this->container);

        if ($membershipId && ($currentMembershipLogId != $todelLogId)) {
            $contactPdo->deleteMemebershipLog($contactId, $this->clubId, $membershipId, $fromDate, $toDate);

            $this->em->getRepository('CommonUtilityBundle:FgCmContact')->updateCurrentJoiningDate($contactId);
            $this->em->getRepository('CommonUtilityBundle:FgCmContact')->updateFirstJoiningDate($contactId);
            $this->em->getRepository('CommonUtilityBundle:FgCmContact')->updateCurrentLeavingDate($contactId);

            $output = array('status' => 'SUCCESS', 'msg' => $this->get('translator')->trans('MEMBERSHIP_LOG_DELETE_SUCCESS'));

            return new JsonResponse($output);
        }
    }
    
    /**
     * function to add the membership log
     * 
     * @return JsonResponse
     */
    public function membershipLogAddAction()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $joingDate = $request->get('from');
        $leavingDate = $request->get('to');
        $membershipId = $request->get('membershipid');
        $contactId = $request->get('contactId');
        $contactname = $request->get('contactname');
        $type = $request->get('type');
        if ($joingDate != "" && $leavingDate != "" && $membershipId != "") {
            $checkIsValid = $this->em->getRepository('CommonUtilityBundle:FgCmMembershipHistory')->checkValidMembershipPeriod($joingDate, $leavingDate, $contactId, $this->clubId);
            $isValid = ($checkIsValid == 1) ? 0 : 1;
        } else {
            $isValid = 0;
        }

        if ($isValid == 1) {
            $lastId = $this->em->getRepository('CommonUtilityBundle:FgCmMembershipHistory')->addMembershipLog($joingDate, $leavingDate, $membershipId, $contactId, $this->clubId, $this->contactId, $contactname, $type);
            $membershipArr = $this->em->getRepository('CommonUtilityBundle:FgCmMembershipHistory')->getLatestMembershipLogDetails($lastId, $this->contactId);
            $membershipTitle = ($membershipArr['Membership']) ? $membershipArr['Membership'] : '';
            $editedBy = ($membershipArr['editedBy']) ? $membershipArr['editedBy'] : '';

            $this->em->getRepository('CommonUtilityBundle:FgCmContact')->updateCurrentJoiningDate($contactId);
            $this->em->getRepository('CommonUtilityBundle:FgCmContact')->updateFirstJoiningDate($contactId);
            $this->em->getRepository('CommonUtilityBundle:FgCmContact')->updateCurrentLeavingDate($contactId);

            $output = array('status' => 'SUCCESS', 'membership' => $membershipTitle, 'editedBy' => $editedBy, 'membershipHistoryId' => $lastId, 'membershipId' => $membershipId, 'dateFromOriginal' => $membershipArr['dateFromOriginal'], 'dateToOriginal' => $membershipArr['dateToOriginal']);
            $output['success_msg'] = $this->get('translator')->trans('MEMBERSHIP_LOG_ADD_SUCCESS');
        } else {
            $output = array('status' => 'ERROR');
        }

        return new JsonResponse($output);
    }
    
    /**
     * function to validate and edit membership history entry
     * 
     * @param int $contact ContactId
     *
     * @return JsonResponse
     */
    public function editMembershipLogAction($contact)
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $membershipHistoryId = $request->get('rowId');
        $field = $request->get('colId');
        $changedValue = trim($request->get('value'));

        if ($changedValue != '') {
            $phpDateFormat = FgSettings::getPhpDateFormat();
            $clubArray = array('clubType' => $this->clubType, 'clubId' => $this->clubId, 'subFederationId' => $this->subFederationId, 'federationId' => $this->federationId);
            $membershipLogValidatorObj = new FgMembershipValidator($this->container, $contact, $membershipHistoryId, $field, $changedValue, $clubArray);
            $output = $membershipLogValidatorObj->validateMembershipLogData();
            if ($output['valid'] == 'true') {
                switch ($field) {
                    case 'joining_date':
                    case 'leaving_date':
                        $output = $this->editDates($phpDateFormat, $changedValue, $membershipHistoryId, $field, $contact, $output);
                        break;
                    case 'membership':
                        $this->editMemberShip($contact, $membershipHistoryId, $changedValue);
                        break;
                    default:
                        break;
                }
            }
        } else {
            $output['valid'] = 'false';
            $output['msg'] = $this->container->get('translator')->trans('REQUIRED');
        }
        return new JsonResponse($output);
    }
    
    /**
     * Method to get tab and count details
     * 
     * @param object  $accessObj        object of ContactDetailsAccess
     * @param string  $activeTabName    current active tab name
     * @param int     $contact          contactId
     * @param string  $contactType      contact/sponsor/formerfederation
     * @param int     $offset           offest parameter in url
     * @param boolean $isCompanyContact 0/1-isCompanyContact
     * 
     * @return array of tab and count details
     */
    private function getTabDetails($accessObj, $activeTabName, $contact, $contactType, $offset, $isCompanyContact)
    {
        $logTabs = $this->logTabs();
        $activeTab = array_search($activeTabName, $logTabs) ? array_search($activeTabName, $logTabs) : 1;
        $activeTab1 = array_search($activeTabName, $logTabs) ? $activeTabName : 'data';
        $getAsgmntCount = ($contactType == 'archive' or $contactType == 'archivedsponsor') ? false : true;
        $contCountDetails = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getContactCountDetails($this->clubId, $contact, $isCompanyContact, $this->clubType, true, $getAsgmntCount, true, false, false, false, false, $this->federationId, $this->subFederationId);
        $contCountDetails['documentsCount'] = $this->em->getRepository('CommonUtilityBundle:FgDmAssigment')->getCountOfAssignedDocuments('CONTACT', $this->clubId, $contact);
        $tabs = FgUtility::getTabsArrayDetails($this->container, $accessObj->tabArray, $offset, $contact, $contCountDetails, "log", "contact");

        return array('logTabs' => $logTabs, 'activeTab' => $activeTab, 'activeTab1' => $activeTab1, 'logTabsCount' => count($logTabs),
            'tabs' => $tabs);
    }

    /**
     * Method to get membership log details 
     * 
     * @return array of fed membership, club memebership and inline edit details
     */
    private function getMembershipDetails()
    {
        $membershipFields = $this->membershipFields();
        //for inline edit
        $inlineEdit = $this->inlineEditArray($membershipFields['membershipsArr'], $membershipFields['fedmembershipsArr']);

        return array('fedmembershipsArray' => $membershipFields['fedmemberships'], 'membershipsArray' => $membershipFields['memberships'],
            'membershipEditArr' => $inlineEdit['membershipInline'], 'fedmembershipEditArr' => $inlineEdit['fedmembershipInline']);
    }
    
    /**
     * Method to get contact details
     * 
     * @param int    $contact     contactId
     * @param string $contactType contact/sponsor/formerfederation/...
     * 
     * @return array of contact details
     */
    private function getContactData($contact, $contactType)
    {
        $contactPdo = new ContactPdo($this->container);
        $contactData = $contactPdo->contactDetails($contact);
        if ($contactData['clubMembershipId']) {
            $notDeletableMembershipLogId = $this->em->getRepository('CommonUtilityBundle:FgCmMembershipHistory')->getCurrentMembershipLogId($contactData['clubMembershipId'], $contact);
        }
        if ($contactData['fedMembershipId']) {
            $fedMem = ($contactData['fedmembershipApprove'] == 1) ? $contactData['old_fed_membership_id'] : $contactData['fedMembershipId'];
            $notDeletableFedMembershipLogId = $this->em->getRepository('CommonUtilityBundle:FgCmMembershipHistory')->getCurrentMembershipLogId($fedMem, $contact);
        }
        $typeContact = ($contactData['is_permanent_delete'] == 1) ? 'formerfederationmember' : $contactType;

        return array('contactData' => $contactData, 'membershipNotDelId' => $notDeletableMembershipLogId,
            'notDeletableFedMembershipLogId' => $notDeletableFedMembershipLogId, 'type' => $typeContact);
    }

    /**
     * Method to check access of contact in that page
     * 
     * @param int $contact contactId
     * 
     * @return object ContactDetailsAccess
     */
    private function checkAccess($contact)
    {
        //security
        $accessObj = new ContactDetailsAccess($contact, $this->container);
        if ($accessObj->accessType == 'NO_ACCESS' || !in_array('log', $accessObj->tabArray)) {
            $this->fgpermission->checkClubAccess('', 'backend_contact_log');
        }
        $contactType = $accessObj->contactviewType;
        $contactMenuModule = $accessObj->menuType;
        //set menu module
        if ($contactMenuModule == 'archive') {
            $this->get('club')->set('moduleMenu', 'contactarchive');
        } elseif ($contactMenuModule == 'formerfederationmember') {
            $this->get('club')->set('moduleMenu', 'formerfederationmember');
        }
        $this->session->set('contactType', $contactType);

        return $accessObj;
    }

    /**
     * Method to get membership fields of club/fed membershipsa
     * 
     * @return array of membership fields
     */
    private function membershipFields()
    {
        $club = $this->get('club');
        $clubDefaultLang = $club->get('default_lang');
        $membershipsArr = $fedmembershipsArr = array();

        $objMembershipPdo = new membershipPdo($this->container);
        $membersipFields = $objMembershipPdo->getMemberships($this->clubType, $this->clubId, $this->subFederationId, $this->federationId);
        if ($this->clubType == 'federation' || $this->clubType == 'sub_federation') {
            foreach ($membersipFields as $key => $memberCat) {
                $title = $memberCat['allLanguages'][$clubDefaultLang]['titleLang'] != '' ? $memberCat['allLanguages'][$clubDefaultLang]['titleLang'] : $memberCat['membershipName'];
                if (($memberCat['clubId'] == ($this->clubType == 'federation') ? $this->clubId : $this->federationId)) {
                    $fedmemberships[$key] = str_replace('"', '\"', $title);
                    $fedmembershipsArr[] = array('id' => $key, 'title' => str_replace('"', '\"', $title));
                }
            }
        } else {
            foreach ($membersipFields as $key => $memberCat) {
                $title = $memberCat['allLanguages'][$clubDefaultLang]['titleLang'] != '' ? $memberCat['allLanguages'][$clubDefaultLang]['titleLang'] : $memberCat['membershipName'];
                if (($memberCat['clubId'] != $this->federationId)) {
                    $memberships[$key] = str_replace('"', '\"', $title);
                    $membershipsArr[] = array('id' => $key, 'title' => str_replace('"', '\"', $title));
                }
            }
        }

        return array('fedmemberships' => $fedmemberships, 'fedmembershipsArr' => $fedmembershipsArr, 'memberships' => $memberships, 'membershipsArr' => $membershipsArr);
    }

    /**
     * Method to get details array for inline edit
     *
     * @param array $membershipsArr
     * @param array $fedmembershipsArr
     * 
     * @return array for inline edit
     */
    private function inlineEditArray($membershipsArr, $fedmembershipsArr)
    {
        $data = $dataF = array();
        $data[] = array('id' => 'joining_date', 'data-edit-type' => 'date');
        $data[] = array('id' => 'leaving_date', 'data-edit-type' => 'date');
        $data[] = array('id' => 'membership', 'data-edit-type' => 'select2', 'input' => $membershipsArr);

        $dataF[] = array('id' => 'joining_date', 'data-edit-type' => 'date');
        $dataF[] = array('id' => 'leaving_date', 'data-edit-type' => 'date');
        $dataF[] = array('id' => 'membership', 'data-edit-type' => 'select2', 'input' => $fedmembershipsArr);

        return array('membershipInline' => $data, 'fedmembershipInline' => $dataF);
    }

    /**
     * Method to return contact log Tabs
     * 
     * @return array $logTabs
     */
    private function logTabs()
    {

        $club = $this->container->get('club');
        switch ($this->clubType) {
            case 'standard_club':
                if (in_array('communication', $club->get('allowedRights')) && in_array('communication', $club->get('bookedModulesDet'))) {
                    if ($club->get('clubMembershipAvailable')) {
                        $logTabs = array(1 => 'data', 2 => 'assignments', 3 => 'connections', 4 => 'membership', 5 => 'communication', 6 => 'notes', 7 => 'system');
                    } else {
                        $logTabs = array(1 => 'data', 2 => 'assignments', 3 => 'connections', 4 => 'communication', 5 => 'notes', 6 => 'system');
                    }
                } else {
                    $logTabs = array(1 => 'data', 2 => 'assignments', 3 => 'connections', 4 => 'membership', 5 => 'notes', 6 => 'system');
                }
                break;
            case 'federation':
            case 'sub_federation':
                if (in_array('communication', $club->get('allowedRights')) && in_array('communication', $club->get('bookedModulesDet'))) {
                    $logTabs = array(1 => 'data', 2 => 'assignments', 3 => 'connections', 4 => 'fed_membership', 5 => 'communication', 6 => 'notes', 7 => 'system');
                } else {
                    $logTabs = array(1 => 'data', 2 => 'assignments', 3 => 'connections', 4 => 'fed_membership', 5 => 'notes', 6 => 'system');
                }
                break;
            default:
                if (in_array('communication', $club->get('allowedRights')) && in_array('communication', $club->get('bookedModulesDet'))) {
                    if ($club->get('clubMembershipAvailable')) {
                        $logTabs = array(1 => 'data', 2 => 'assignments', 3 => 'connections', 4 => 'membership', 5 => 'fed_membership', 6 => 'communication', 7 => 'notes', 8 => 'system');
                    } else {
                        $logTabs = array(1 => 'data', 2 => 'assignments', 3 => 'connections', 4 => 'fed_membership', 5 => 'communication', 6 => 'notes', 7 => 'system');
                    }
                } else
                if ($club->get('clubMembershipAvailable')) {
                    $logTabs = array(1 => 'data', 2 => 'assignments', 3 => 'connections', 4 => 'membership', 5 => 'fed_membership', 6 => 'notes', 7 => 'system');
                } else {
                    $logTabs = array(1 => 'data', 2 => 'assignments', 3 => 'connections', 4 => 'fed_membership', 5 => 'notes', 6 => 'system');
                }
                break;
        }

        return $logTabs;
    }

    /**
     * Method to get readonly status of current contact
     *
     * @return boolean $isReadOnlyContact
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
     * Function for log translate transKindFields
     */
    private function transKindFields()
    {
        $terminologyService = $this->get('fairgate_terminology_service');
        $transKindFields = array(
            'intranet access' => $this->get('translator')->trans('LOG_INTRANET_ACCESS'), 'stealth mode' => $this->get('translator')->trans('LOG_STEALTH_MODE'),
            'data' => $this->get('translator')->trans('GN_DATA'), 'communication' => $this->get('translator')->trans('LOG_COMMUNICATION'), 'assignment' => $this->get('translator')->trans('GN_ASSIGNMENTS'),
            'linked contacts' => $this->get('translator')->trans('GN_CONNECTIONS'), 'user rights' => $this->get('translator')->trans('GN_USERRIGHTS'), 'notes' => $this->get('translator')->trans('GN_NOTES'),
            'login' => $this->get('translator')->trans('GN_LOGIN'), 'emails' => $this->get('translator')->trans('GN_EMAILS'),
            'contact type' => $this->get('translator')->trans('CM_CONTACT_TYPE'), 'contact status' => $this->get('translator')->trans('GN_CONTACT_STATUS'),
            'assignments' => $this->get('translator')->trans('GN_ASSIGNMENTS'), 'connections' => $this->get('translator')->trans('GN_CONNECTIONS'),
            'fed_membership' => $terminologyService->getTerminology('Fed membership', $this->container->getParameter('singular')), 'membership' => $this->get('translator')->trans('CM_MEMBERSHIP'), 'system' => $this->get('translator')->trans('SYSTEM'),
            'Formal' => $this->get('translator')->trans('CM_FORMAL'), 'Informal' => $this->get('translator')->trans('CM_INFORMAL'),
            'Male' => $this->get('translator')->trans('CM_MALE'), 'Female' => $this->get('translator')->trans('CM_FEMALE'), 'added' => $this->get('translator')->trans('LOG_FLAG_ADDED'),
            'removed' => $this->get('translator')->trans('LOG_FLAG_REMOVED'), 'changed' => $this->get('translator')->trans('LOG_FLAG_CHANGED'),
            'male' => $this->get('translator')->trans('CM_MALE'), 'female' => $this->get('translator')->trans('CM_FEMALE'), 'GN_LOGIN' => $this->get('translator')->trans('GN_LOGIN'),
            'GN_PASSWORD' => $this->get('translator')->trans('GN_PASSWORD'), 'GN_CONTACT STATUS' => $this->get('translator')->trans('GN_CONTACT STATUS'),
            'contact_type' => $this->get('translator')->trans('GN_SPONSORSHIP'),
            'newsletter' => $this->get('translator')->trans('LOG_NEWSLETTER')
        );

        return $transKindFields;
    }

    /**
     * Function to translate $transArr
     */
    private function transArr()
    {
        $transArr = array(
            'YES' => $this->get('translator')->trans('YES'), 'NO' => $this->get('translator')->trans('NO'),
            'LOG_NEWSLETTER' => $this->get('translator')->trans('LOG_NEWSLETTER'), 'LOG_SIMPLEMAIL' => $this->get('translator')->trans('LOG_SIMPLEMAIL'),
            'LOG_ACTIVE' => $this->get('translator')->trans('LOG_ACTIVE'), 'LOG_ARCHIVED' => $this->get('translator')->trans('LOG_ARCHIVED'),
            'LOG_REACTIVATED' => $this->get('translator')->trans('LOG_REACTIVATED'), 'LOG_REQUESTED' => $this->get('translator')->trans('LOG_REQUESTED'),
            'Household contact' => $this->get('translator')->trans('LOG_HOUSEHOLD_CONTACT'),
            'LOG_CLUB ADMINISTRATOR' => $this->get('translator')->trans('LOG_CLUB ADMINISTRATOR'), 'LOG_CONTACTS' => $this->get('translator')->trans('LOG_CONTACTS'),
            'LOG_DOCUMENTS' => $this->get('translator')->trans('LOG_DOCUMENTS'), 'LOG_COMMUNICATION' => $this->get('translator')->trans('LOG_COMMUNICATION'),
            'Main contact' => $this->get('translator')->trans('LOG_MAIN_CONTACT'), 'Main contact of company' => $this->get('translator')->trans('LOG_MAIN_CONTACT_OF_COMPANY'),
            'Single person' => $this->get('translator')->trans('LOG_SINGLE_PERSON'), 'Company' => $this->get('translator')->trans('LOG_COMPANY'),
            'LOG_SINGLE PERSON' => $this->get('translator')->trans('LOG_SINGLE_PERSON'), 'LOG_COMPANY' => $this->get('translator')->trans('LOG_COMPANY'), 'LOG_GALLERYADMIN' => $this->get('translator')->trans('LOG_GALLERYADMIN'), 'LOG_GALLERY' => $this->get('translator')->trans('LOG_GALLERY'),
            'LOG_CHANGED' => $this->get('translator')->trans('LOG_CHANGED'), 'LOG_GROUPADMIN' => $this->get('translator')->trans('LOG_GROUPADMIN'), 'LOG_SPONSOR' => $this->get('translator')->trans('LOG_SPONSOR'),
            'LOG_CONTACTADMIN' => $this->get('translator')->trans('LOG_CONTACTADMIN'), 'LOG_DOCUMENTADMIN' => $this->get('translator')->trans('LOG_DOCUMENTADMIN'), 'LOG_FORUMADMIN' => $this->get('translator')->trans('LOG_FORUMADMIN'), 'LOG_P' => $this->get('translator')->trans('LOG_P'),
            'LOG_T' => $this->get('translator')->trans('LOG_T'), 'LOG_W' => $this->get('translator')->trans('LOG_W'), 'LOG_READONLY SPONSOR' => $this->get('translator')->trans('LOG_READONLY SPONSOR'), 'LOG_CALENDARADMIN' => $this->get('translator')->trans('LOG_CALENDARADMIN'),
            'LOG_READONLY CONTACT' => $this->get('translator')->trans('LOG_READONLY CONTACT'),
            'LOG_CMSADMIN' => $this->get('translator')->trans('LOG_CMS ADMINISTRATOR'),
            'LOG_ARTICLE' => $this->get('translator')->trans('LOG_ARTICLE'),
            'LOG_ARTICLEADMIN' => $this->get('translator')->trans('LOG_ARTICLEADMIN'),
            'SUBSCRIBED' => $this->get('translator')->trans('LOG_SUBSCRIBE'), 'LOG_CALENDAR' => $this->get('translator')->trans('LOG_CALENDAR'),
            'UNSUBSCRIBED' => $this->get('translator')->trans('LOG_UNSUBSCRIBE'), 'LOG_FEDADMIN' => $this->get('translator')->trans('USER_RIGHTS_FED_ADMINISTRATOR'));

        return $transArr;
    }

    /**
     * Method to get array of contact field log entries
     * 
     * @param array $clubDetails     clubDetails
     * @param int   $contact         contactId
     * @param int   $fedContactId    fedContactId
     * @param int   $subfedContactId subfedContactId 
     * 
     * @return array of contact field log entries
     */
    private function getFieldLogEntries($clubDetails, $contact, $fedContactId, $subfedContactId)
    {
        $contactPdo = new ContactPdo($this->container);
        $logEntriesDataTab = $contactPdo->getContactFieldLogEntries($clubDetails, $contact, $this->container, $fedContactId, $subfedContactId);
        foreach ($logEntriesDataTab as $key => $dataFields) {
            $logEntriesDataTab[$key]['value_before'] = htmlentities($dataFields['value_before'], ENT_COMPAT, "UTF-8");
            $logEntriesDataTab[$key]['value_after'] = htmlentities($dataFields['value_after'], ENT_COMPAT, "UTF-8");
        }

        return $logEntriesDataTab;
    }

    /**
     * Method to get notes log entries
     * 
     * @param array $clubDetails club Details 
     * @param int   $contact     contactId
     * 
     * @return array of notes log entries
     */
    private function getNotesLogEntries($clubDetails, $contact)
    {
        $contactPdo = new ContactPdo($this->container);
        $logEntriesNotesTab = $contactPdo->getNotesLogEntries($clubDetails, $contact);
        foreach ($logEntriesNotesTab as $key => $noteFields) {
            $noteFields['value_after'] = str_replace("\"", "#~#", $noteFields['value_after']);
            $noteFields['value_before'] = str_replace("\"", "#~#", $noteFields['value_before']);
            $noteFields['value_after'] = str_replace("<", "#~~#", $noteFields['value_after']);
            $noteFields['value_before'] = str_replace("<", "#~~#", $noteFields['value_before']);
            $logEntriesNotesTab[$key]['value_before'] = htmlentities($noteFields['value_before'], ENT_COMPAT, "UTF-8");
            $logEntriesNotesTab[$key]['value_after'] = htmlentities($noteFields['value_after'], ENT_COMPAT, "UTF-8");
        }

        return $logEntriesNotesTab;
    }

    /**
     * Method to get membership tab details
     * 
     * @param array $clubDetails  clubDetails
     * @param int   $contact      contactId
     * @param int   $federationId federationId
     * @param int   $fedContactId fedContactId 
     * 
     * @return array of membership tab details
     */
    private function getMembershipTabDetails($clubDetails, $contact, $federationId, $fedContactId)
    {
        $contactPdo = new ContactPdo($this->container);
        $logEntriesMembershipTab = $logEntriesFedMembershipTab = array();
        if ($this->clubType == 'standard_club') {
            $logEntriesMembershipTab = $contactPdo->getMembershipLogEntries($clubDetails, $contact, $federationId);
        } else {
            if ($this->clubType == 'federation') {
                $logEntriesFedMembershipTab = $contactPdo->getFedMembershipLogEntries($contact, $this->get('contact')->get('corrLang'), $federationId, $this->clubId);
            } elseif ($this->clubType == 'sub_federation') {
                $logEntriesFedMembershipTab = $contactPdo->getFedMembershipLogEntries($fedContactId, $this->get('contact')->get('corrLang'), $federationId, $this->clubId);
            } else {
                $logEntriesFedMembershipTab = $contactPdo->getFedMembershipLogEntries($fedContactId, $this->get('contact')->get('corrLang'), $federationId, $this->clubId);
                $logEntriesMembershipTab = $contactPdo->getMembershipLogEntries($clubDetails, $contact, $federationId);
            }
        }

        return array('fed_membership' => $logEntriesFedMembershipTab, 'membership' => $logEntriesMembershipTab);
    }

    /**
     * Method to edit membership
     * 
     * @param int    $contact             contactId
     * @param int    $membershipHistoryId membershipHistoryId 
     * @param string $changedValue        changedValue
     */
    private function editMemberShip($contact, $membershipHistoryId, $changedValue)
    {
        $this->em->getRepository('CommonUtilityBundle:FgCmMembershipLog')->updateMembershipLogEntryOfContact($contact, $membershipHistoryId, 'membership', $changedValue);
        $this->em->getRepository('CommonUtilityBundle:FgCmMembershipHistory')->updateMembershipHistoryEntryOfContact($contact, $membershipHistoryId, 'membership_id', $changedValue);
    }

    /**
     * Method to edit joining/leaving dates
     * 
     * @param string $phpDateFormat       club date format
     * @param string $changedValue        changedValue
     * @param int    $membershipHistoryId membershipHistoryId
     * @param string $field               joining_date/leaving_date
     * @param int    $contact             contactId
     * @param array  $output              array of returnvalues
     * 
     * @return array
     */
    private function editDates($phpDateFormat, $changedValue, $membershipHistoryId, $field, $contact, $output)
    {
        $dateObj = new \DateTime();
        $joiningDateObj = $dateObj->createFromFormat($phpDateFormat, $changedValue);
        if ($joiningDateObj !== false) {
            $joiningDate = $joiningDateObj->format('Y-m-d') . ' 00:00:00';
            $errorexists = $this->em->getRepository('CommonUtilityBundle:FgCmMembershipHistory')->validateInlineMembershipPeriod($field, $membershipHistoryId, $contact, $changedValue, $this->clubId);
        }
        if ($joiningDateObj !== false && (!$errorexists)) {
            $this->em->getRepository('CommonUtilityBundle:FgCmMembershipLog')->updateMembershipLogEntryOfContact($contact, $membershipHistoryId, $field, $joiningDate);
            $this->em->getRepository('CommonUtilityBundle:FgCmContact')->updateContactJoiningLeavingDate($contact, $membershipHistoryId, $field, $joiningDate);
            $this->em->getRepository('CommonUtilityBundle:FgCmMembershipHistory')->updateMembershipHistoryEntryOfContact($contact, $membershipHistoryId, $field, $joiningDate);

            switch ($field) {
                case 'joining_date':
                    $this->em->getRepository('CommonUtilityBundle:FgCmContact')->updateFirstJoiningDate($contact);
                    $this->em->getRepository('CommonUtilityBundle:FgCmContact')->updateCurrentJoiningDate($contact);
                    break;
                case 'leaving_date':
                    $this->em->getRepository('CommonUtilityBundle:FgCmContact')->updateCurrentLeavingDate($contact);
                    break;
                default:
                    break;
            }
        } else {
            $output['valid'] = 'false';
            $output['msg'] = $this->container->get('translator')->trans('MEMBERSHIP_LOG_UPDATE_FAILED');
        }

        return $output;
    }
}
