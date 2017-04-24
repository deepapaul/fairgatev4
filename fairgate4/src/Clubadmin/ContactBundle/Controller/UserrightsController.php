<?php

namespace Clubadmin\ContactBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Common\UtilityBundle\Util\FgUtility;
use Clubadmin\Util\Contactlist;
use Clubadmin\ContactBundle\Util\ContactDetailsAccess;
use Clubadmin\ContactBundle\Util\NextpreviousContact;
use Symfony\Component\HttpFoundation\Request;
use Common\UtilityBundle\Util\FgContactSyncDataToAdmin;

/**
 * UserrightsController
 *
 * This controller was created for handling User rights
 *
 * @package    ClubadminContactBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
class UserrightsController extends FgController
{

    /**
     * Function is used to display user rights from user rights setting page where we can add more rights
     *
     * @return template
     */
    public function indexAction()
    {

        // Function to get all assigned user rights with user details
        $groupContacts = $this->em->getRepository('CommonUtilityBundle:SfGuardGroup')->getAllGroupsContacts($this->conn, $this->clubId);

        // Function to get all listing blocks like Club admins, readonly admins etc
        $getAllListingBlocks = $this->getAllListingBlocks($groupContacts);

        // Function to get all user rights groups
        $groupDetails = $this->em->getRepository('CommonUtilityBundle:SfGuardGroup')->getAllGroups($this->conn);

        // Get all assigned rigts contacts
        $groupUserDetails = $this->em->getRepository('CommonUtilityBundle:SfGuardGroup')->getGroupDetailsAllUSer($this->conn, $this->clubId);
        $groupUserDetails = json_encode($groupUserDetails);

        $club = $this->get('club'); // Club object
        $contactService = $this->container->get('contact');


        // Translation for default texts displayed in the settings page.
        // When a new block needed in the settings page, need to add the translation in this array for the block
        $transAdministration = $this->transArray();

        $bookedModuleDetails = $club->get('bookedModulesDet');
        $tabs = array('backend');
        if (in_array('frontend1', $bookedModuleDetails)) {
            $tabs = array('backend', 'internal', 'groupuserrights');
            if (in_array('frontend2', $bookedModuleDetails)) {
                $tabs[] = 'website';
            }
        }
        $tabsData = FgUtility::getTabsArrayDetails($this->container, $tabs, '', '', '', 'backend');
        // to get booked modules array
        $bookedModuleDetails = array_flip($bookedModuleDetails);
        $bookedModuleDetails = json_encode($bookedModuleDetails);
        $allGroups = json_encode($groupDetails);
        $groupContactsJson = json_encode($getAllListingBlocks['finalUserGroupArray']);
        $existingUserDetails = json_encode($getAllListingBlocks['existingUserDetails']);
        $existingOtherAdmins = json_encode($getAllListingBlocks['existingOtherAdmins']);
        $existingReadonlyAdmins = json_encode($getAllListingBlocks['existingReadonlyAdmins']);
        $existingFedAdminDetails = json_encode($getAllListingBlocks['existingFedAdminDetails']);
        $internalUserrightsPath = $this->generateUrl('group_userrights_team');
        $c5 = $club->get('fedAdminAccess');
        $contactNameUrl = str_replace('25','',$this->generateUrl('userrightscontact_name_search',array('term' => '%QUERY')));
        $contactFedNameUrl =  str_replace('25','',$this->generateUrl('contact_fedname_search',array('term' => '%QUERY')));
        
        return $this->render('ClubadminContactBundle:Userrights:index.html.twig', array('contactFedNameUrl'=>$contactFedNameUrl,'contactNameUrl'=>$contactNameUrl,'c5' => $c5, 'clubType' => $this->clubType, 'internalPath' => $internalUserrightsPath, 'groupContacts' => $groupContactsJson, 'tabs' => $tabsData, 'existingUserDetails' => $existingUserDetails, 'loggedContactId' => $this->contactId, 'allGroups' => $allGroups, 'bookedModuleDetails' => $bookedModuleDetails, 'groupUserDetails' => $groupUserDetails, 'transAdministration' => $transAdministration, 'existingFedAdminDetails' => $existingFedAdminDetails, 'existingOtherAdmins' => $existingOtherAdmins, 'existingReadonlyAdmins' => $existingReadonlyAdmins, 'settings' => true));
    }

    /**
     * translation for admins
     *
     * @return json encoded array
     */
    private function transArray()
    {

        $transAdministration = array('communication' => $this->get('translator')->trans('USER_RIGHTS_COMMUNICATION'),
            'document' => $this->get('translator')->trans('USER_RIGHTS_DOCUMENT'),
            'contact' => $this->get('translator')->trans('USER_RIGHTS_CONTACT'),
            'sponsor' => $this->get('translator')->trans('USER_RIGHTS_SPONSOR'));
        $transAdministration = json_encode($transAdministration);

        return $transAdministration;
    }

    /**
     * Function is used to get all the user rights blocks as seperate array
     *
     * @param Array $groupContacts Group-contact details
     *
     * @return Array
     */
    private function getAllListingBlocks($groupContacts)
    {
        $finalUserGroupArray = array();
        $existingUserDetails = array();
        $existingOtherAdmins = array();
        $existingReadonlyAdmins = array();
        $existingFedAdminDetails = array();
        $i = 0;
        $j = 0;
        $n = 0;
        $k = 0;
        $m = 0;

        // Looping array containing all user rights details
        foreach ($groupContacts as $groupContact) {
            $finalUserGroupArray[$k] = $groupContact;
            $finalUserGroupArray[$k]['contactName'] = $groupContact['contactNameYOB'];

            // Seperating club admin rights
            if ($groupContact['module_type'] == 'all') {
                if ($groupContact['type'] == 'club') {
                    $existingUserDetails = $this->assignValuesToGroup($existingUserDetails, $i, $groupContact);
                    $i++;
                } else {
                    $existingFedAdminDetails = $this->assignValuesToGroup($existingFedAdminDetails, $m, $groupContact);
                    $m++;
                }
            }

            // Seperating Administraion section like sponsor admin, contact admin etc
            if ($groupContact['is_security_admin'] == 1 && $groupContact['module_type'] != 'all') {
                $existingOtherAdmins = $this->assignValuesToGroup($existingOtherAdmins, $j, $groupContact);
                $j++;
            }

            // Seperating readonly admins
            if ($groupContact['is_readonly_admin'] == 1 && $groupContact['module_type'] != 'all') {
                $existingReadonlyAdmins = $this->assignValuesToGroup($existingReadonlyAdmins, $n, $groupContact);
                $n++;
            }
            $k++;
        }

        return array('existingFedAdminDetails' => $existingFedAdminDetails, 'finalUserGroupArray' => $finalUserGroupArray, 'existingUserDetails' => $existingUserDetails, 'existingOtherAdmins' => $existingOtherAdmins, 'existingReadonlyAdmins' => $existingReadonlyAdmins);
    }

    /**
     * Assign vales to common array
     *
     * @param Array $assignGroupArray Array to be generated for listing
     * @param Int   $indexVal         Index value
     * @param Array $groupContact     Query resuly value
     *
     * @return Array
     */
    private function assignValuesToGroup($assignGroupArray, $indexVal, $groupContact)
    {
        $assignGroupArray[$indexVal][0]['id'] = $groupContact['contact_id'];
        $assignGroupArray[$indexVal][0]['value'] = $groupContact['contactname'];
        $assignGroupArray[$indexVal][0]['label'] = $groupContact['contactname'];

        return $assignGroupArray;
    }

    /**
     * Function is used to get the contact name using contact id
     *
     * @param int $contactId ContactId
     *
     * @return Array
     */
    private function contactDetails($contactId)
    {
        $club = $this->get('club');
        $contactlistClass = new Contactlist($this->container, '', $club); // Calling contact list object
        $contactlistClass->setColumns(array('contactNameYOB', 'contactname', 'ms.`2` as firstName', 'ms.`23` as lastName', 'contactid', 'ms.`3` as email', 'clubId', 'is_company', 'has_main_contact', 'fedMembershipId'));
        $contactlistClass->setFrom('*');
        $contactlistClass->setCondition();
        $sWhere = " fg_cm_contact.id=$contactId";
        $contactlistClass->addCondition($sWhere);
        $listquery = $contactlistClass->getResult();
        $fieldsArray = $this->conn->fetchAll($listquery);

        return $fieldsArray[0];
    }

    /**
     * Function is used to format array for common save function
     *
     * @param Array $userRightsArray userRightsArray
     *
     * @return template
     */
    private function formatUserRightsArray($userRightsArray)
    {

        $finalArray = array();
        $clubAdmin = $this->container->getParameter('club_admin');
        $fedAdmin = $this->container->getParameter('fed_admin');
        $cmsAdmin = $this->container->getParameter('cms_admin');
        $pgAdmin = $this->container->getParameter('page_admin');

        // Checking the delete_all index is preset in the userrights array.
        // The delete_all array represents the deletion of all user assignments of a contact using the close icon in the listing
        if (!empty($userRightsArray['delete_all'])) {

            // Getting and looping all groups available
            $groupDetails = $this->em->getRepository('CommonUtilityBundle:SfGuardGroup')->getAllGroups($this->conn);
            foreach ($groupDetails as $groups) {
                if ($groups['module_type'] != 'all') {

                    // Generating the formated array for deletion in the case of administration section in the user rights
                    foreach ($userRightsArray['delete_all']['administrator']['admin']['contact'] as $cKey => $cVal) {
                        $finalArray['is_security_admin']['contact'][$cKey] = $cKey;
                        if (isset($userRightsArray['new']['admin']['contact'][$cKey])) {
                            unset($userRightsArray['new']['admin']['contact'][$cKey]);
                        }
                        foreach ($cVal['user'] as $uKey => $uVal) {
                            if ($groups['is_readonly'] == 0) {
                                $finalArray['delete']['group'][$groups['group_id']]['user'][$uKey] = $uVal;
                            }
                        }
                    }

                    // Generating the formated array for deletion in the case of readonly admins in the user rights
                    foreach ($userRightsArray['delete_all']['readonly']['admin']['contact'] as $cKey => $cVal) {
                        $finalArray['readonly']['contact'][$cKey] = $cKey;
                        if (isset($userRightsArray['new']['admin']['contact'][$cKey])) {
                            unset($userRightsArray['new']['admin']['contact'][$cKey]);
                        }
                        foreach ($cVal['user'] as $uKey => $uVal) {
                            if ($groups['is_readonly'] == 1) {
                                $finalArray['delete']['group'][$groups['group_id']]['user'][$uKey] = $uVal;
                            }
                        }
                    }
                }
            }
        }

        // Checking and generating formated array incase of single delete of assigned group of a contact
        if (!empty($userRightsArray['delete']['admin'])) {
            foreach ($userRightsArray['delete']['admin']['group'] as $gpKey => $gpVal) {
                foreach ($gpVal['user'] as $usKey => $usVal) {
                    $finalArray['delete']['group'][$gpKey]['user'][$usKey] = 1;
                }
            }
        }

        // Generating array to delete club admin group of a contact
        if (!empty($userRightsArray['delete']['clubAdmin'])) {
            $finalArray['delete']['group'][$clubAdmin]['user'] = $userRightsArray['delete']['clubAdmin']['user'];
        }

        // Generating array when a new club admin is added
        if (!empty($userRightsArray['new']['clubAdmin'])) {
            foreach ($userRightsArray['new']['clubAdmin']['contact'] as $cKey => $cVal) {
                foreach ($cVal['group'] as $gpKey => $gpVal) {
                    $finalArray['new']['group'][$gpKey]['contact'][$cKey] = $gpVal;
                    $finalArray['new_users']['clubAdmin'][$cKey]['id'] = $cKey;
                    $finalArray['new_users']['clubAdmin'][$cKey]['type'] = 'clubAdmin';
                }
            }
        }

        /*         * ******** CMS ADMIN ********** */
        // Generating array to delete cms admin group of a contact
        if (!empty($userRightsArray['delete']['cmsAdmin'])) {
            $finalArray['delete']['group'][$cmsAdmin]['user'] = $userRightsArray['delete']['cmsAdmin']['user'];
        }

        // Generating array when a new cms admin is added
        if (!empty($userRightsArray['new']['cmsAdmin'])) {
            foreach ($userRightsArray['new']['cmsAdmin']['contact'] as $cKey => $cVal) {
                if ($cKey == "undefined") {
                    continue;
                }
                foreach ($cVal['group'] as $gpKey => $gpVal) {
                    $finalArray['new']['group'][$gpKey]['contact'][$cKey] = $gpVal;
                    $finalArray['new_users']['cmsAdmin'][$cKey]['id'] = $cKey;
                    $finalArray['new_users']['cmsAdmin'][$cKey]['type'] = 'cmsAdmin';
                }
            }
        }
        /*         * ******** END CMS ADMIN ********** */
        /*         * ******** BEGIN Page ADMIN ********** */
        if (isset($userRightsArray['cms']['pgAdmin']['delete'])) {
            foreach ($userRightsArray['cms']['pgAdmin']['delete'] as $contactId => $true) {
                $finalArray['new']['pgAdmin'][$pgAdmin]['contact'][$contactId]['team'] = 'deleted';
            }
        }
        if (isset($userRightsArray['cms']['pgAdmin']['existing'])) {
            foreach ($userRightsArray['cms']['pgAdmin']['existing'] as $contact => $teams) {
                if (is_array($teams['pages'])) {
                    $finalArray['new']['pgAdmin'][$pgAdmin]['contact'][$contact]['team'] = $teams['pages'];
                }
                if ($teams['pages'] == '') {
                    $finalArray['new']['pgAdmin'][$pgAdmin]['contact'][$contact]['team'] = 'deleted';
                }
            }
        }

        foreach ($userRightsArray['cms']['pgAdmin'] as $val) {

            if (isset($val['contact'])) {
                foreach ($val['contact'] as $value) {
                    $contactId = $value;
                }
                if ($contactId == "") {
                    continue;
                }
                if ($val['pages'] == '') {
                    $finalArray['new']['pgAdmin'][$pgAdmin]['contact'][$contactId]['team'] = 'deleted';
                } else {
                    $finalArray['new']['pgAdmin'][$pgAdmin]['contact'][$contactId]['team'] = $val['pages'];
                }
            }
        }
        /*         * ******** END PAGE ADMIN ********** */
        // Generating array to delete club admin group of a contact
        if (!empty($userRightsArray['delete']['fedAdmin'])) {
            $finalArray['delete']['group'][$fedAdmin]['user'] = $userRightsArray['delete']['fedAdmin']['user'];
        }

        // Generating array when a new fedAdmin is added
        if (!empty($userRightsArray['new']['fedAdmin'])) {
            foreach ($userRightsArray['new']['fedAdmin']['contact'] as $cKey => $cVal) {
                foreach ($cVal['group'] as $gpKey => $gpVal) {
                    $finalArray['new']['group'][$gpKey]['contact'][$cKey] = $gpVal;
                    $finalArray['new_users']['fedAdmin'][$cKey]['id'] = $cKey;
                    $finalArray['new_users']['fedAdmin'][$cKey]['type'] = 'fedAdmin';
                }
            }
        }

        // Generating array when a new group is added to an already existing contact in listing
        if (!empty($userRightsArray['new']['admin'])) {
            foreach ($userRightsArray['new']['admin']['contact'] as $cKey => $cVal) {
                foreach ($cVal['group'] as $gpKey => $gpVal) {
                    $finalArray['new']['group'][$gpKey]['contact'][$cKey] = $cKey;
                }
            }
        }


        // Generating array when a new contact is added to the administration section
        if (!empty($userRightsArray['new_all']['administrator']['admin']['contact']['id'])) {
            foreach ($userRightsArray['new_all']['administrator']['admin']['contact']['id'] as $idKey => $idVal) {
                $finalArray['new_users']['administrator'][$idVal]['id'] = $idVal;
                $finalArray['new_users']['administrator'][$idVal]['type'] = 'administrator';
            }
        }

        // Generating array when a new contact is added to the readonly admin section
        if (!empty($userRightsArray['new_all']['readonly']['admin']['contact']['id'])) {
            foreach ($userRightsArray['new_all']['readonly']['admin']['contact']['id'] as $idKey => $idVal) {
                $finalArray['new_users']['readonly'][$idVal]['id'] = $idVal;
                $finalArray['new_users']['readonly'][$idVal]['type'] = 'readonly';
            }
        }

        // Generating array when a new contact is added to the administration section
        if (!empty($userRightsArray['new_all']['administrator']['admin']['contact'])) {
            foreach ($userRightsArray['new_all']['administrator']['admin']['contact'] as $newKey => $newVal) {
                if ($newKey != 'id') {
                    foreach ($newVal['group'] as $gKey => $gVal) {
                        if ($gVal == 1) {
                            $finalArray['new']['group'][$gKey]['contact'][$newKey] = $newKey;
                        }
                    }
                }
            }
        }

        // Generating array when a new contact is added to the readonly admin section
        if (!empty($userRightsArray['new_all']['readonly']['admin']['contact'])) {
            foreach ($userRightsArray['new_all']['readonly']['admin']['contact'] as $newKey => $newVal) {
                if ($newKey != 'id') {
                    foreach ($newVal['group'] as $gKey => $gVal) {
                        if ($gVal == 1) {
                            $finalArray['new']['group'][$gKey]['contact'][$newKey] = $newKey;
                        }
                    }
                }
            }
        }

        return $finalArray;
    }

    /**
     * Function is used to save user rights from user rights setting page
     *
     * @return JSON
     */
    public function saveUserRightsAction()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $userRightsArray = json_decode($request->get('postArr'), true); // Getting save values from setting page in JSON format
        $userRightsArray = $this->formatUserRightsArray($userRightsArray); // Calling function to format the result array
        // Calling common save function to save the user rights
        $resultSuccess = $this->em->getRepository('CommonUtilityBundle:SfGuardGroup')->saveUserRights($this->conn, $userRightsArray, $this->clubId, $this->contactId, $this->container);
        
        /** Sync the contact name data to the Admin DB **/
        $userRightContactDetails = ((is_array($userRightsArray['new_users']['clubAdmin']))?$userRightsArray['new_users']['clubAdmin']:array()) +
                            ((is_array($userRightsArray['new_users']['fedAdmin']))?$userRightsArray['new_users']['fedAdmin']:array());
        $userRightContacts = array_column($userRightContactDetails, 'id');
        $contactSyncObject = new FgContactSyncDataToAdmin($this->container);
        $contactSyncObject->updateUserRights($userRightContacts)->updateLastUpdated($this->clubId)->executeQuery();
        /***********************************************/
        
        if ($resultSuccess) {
            return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('USER_RIGHTS_SAVED_SUCCESS')));
        } else {
            return new JsonResponse(array('status' => 'SUCCESS'));
        }
    }

    /**
     * Function is used to display all user rights of a user from overview
     *
     * @param int $offset  Offset value
     * @param int $contact ContactId
     *
     * @return template
     */
    public function displayRightsAction($offset, $contact)
    {

        // Checking the access type of the page
        $contactType = 'contact';
        $accessObj = new ContactDetailsAccess($contact, $this->container);

        if ($accessObj->accessType == 'NO_ACCESS' || !in_array('userright', $accessObj->tabArray)) {
            // If no access to the page, triggering exception
            $this->fgpermission->checkClubAccess('', 'backend_contact_userrights');
        }
        $contactType = $accessObj->contactviewType;
        $contactMenuModule = $accessObj->menuType;
        if ($contactMenuModule == 'archive') {
            $this->get('club')->set('moduleMenu', 'contactarchive');
        } elseif ($contactMenuModule == 'formerfederationmember') {
            $this->get('club')->set('moduleMenu', 'formerfederationmember');
        }
        $this->session->set('contactType', $contactType);
        $club = $this->get('club');
        $currentContactName = $this->contactDetails($contact); // Getting the current contact name
        if ($this->clubType == 'federation') {
            $records = $this->em->getRepository('CommonUtilityBundle:SfGuardGroup')->fedAdminEligibility($this->container, $this->conn, $this->clubId);
            $fedAdminEligibility = array_column($records, 'id');
            $c5 = $club->get('fedAdminAccess');
            $grpType = $c5 ? "'club','federation'" : "'club'";
            $hasFedAdminEligibility = in_array($contact, $fedAdminEligibility) ? 1 : 0;
        } else {
            $grpType = "'club'";
            $hasFedAdminEligibility = 0;
            $c5 = 0;
        }

        $groupDetails = $this->em->getRepository('CommonUtilityBundle:SfGuardGroup')->getAllGroups($this->conn, $grpType); // Getting all grous details
        $groupUserDetails = $this->em->getRepository('CommonUtilityBundle:SfGuardGroup')->getGroupDetails($this->conn, $this->clubId, $contact); // Getting groups details related to contact

        $hasUserRights = (count($groupUserDetails) > 0) ? 1 : 0;
        $groupUserDetails = json_encode($groupUserDetails);
        $groupDetails = json_encode($groupDetails);
        // Generating next and previous data for the next-previous functionality in the overview page
        $nextprevious = new NextpreviousContact($this->container);
        $nextPreviousResultset = $nextprevious->nextPreviousContactData($this->contactId, $contact, $offset, 'contact_user_rights', 'offset', 'contact', $flag = 0);
        $lang = $this->container->get('contact')->get('corrLang');
        $pages = $this->em->getRepository('CommonUtilityBundle:FgCmsPage')->getPagesList($this->clubId, $lang);
        $bookedModuleDetails = $club->get('bookedModulesDet'); // Getting booked modules details
        $bookedModuleDetails = array_flip($bookedModuleDetails);
        $bookedModuleDetails = json_encode($bookedModuleDetails);

        $contactService = $this->container->get('contact');
        $isSuperadmin = $contactService->get('isSuperAdmin');

        // Basic translation details array for userrights
        $transAdministration = $this->transArray();

        $clubTeams = $this->em->getRepository('CommonUtilityBundle:FgRmRole')->getTeamsOrWorkgroupsForDocumentsDepositedOption('team', $this->clubTeamId, $this->clubDefaultLang, false, false, $this->container);
        $allTeamGroups = $this->em->getRepository('CommonUtilityBundle:SfGuardGroup')->getAllTeamGroups();

        $masterTable = $club->get('clubTable');
        //get contact id from corresponding table(if club type is federation the table field name is fed_contact_id else contact_id)
        $contactFrom = ($club->get('type') === 'federation') ? "fed_contact_id" : "contact_id";

        $teamGroupsDetails = $this->em->getRepository('CommonUtilityBundle:SfGuardGroup')->getAllGroups($this->conn, "'role'"); // Getting all team grous details
        $teamGroupUserDetails = $this->em->getRepository('CommonUtilityBundle:SfGuardGroup')->getTeamGroupDetails($this->conn, $this->clubId, $contact); // Getting groups details related to contact
        $dropdownList = $this->em->getRepository('CommonUtilityBundle:FgRmRole')->getRoleTeamDetails($this->clubId, $this->container);
        $allTeamGroups = $this->formatRights($allTeamGroups);
        // page admin details
        $pageGroupDetails = $this->em->getRepository('CommonUtilityBundle:SfGuardGroup')->getAllGroups($this->conn, "'page'"); // Getting all team grous details
        $pageGroupUserDetails = $this->em->getRepository('CommonUtilityBundle:SfGuardUserPage')->getPageAdmins($this->clubId, $contact); // Getting groups details related to contact
        $return = array('c5' => $c5, 'hasFedAdminEligibility' => $hasFedAdminEligibility, 'contactType' => $currentContactName['is_company'], 'contactName' => $currentContactName['contactName'], 'displayedUserName' => $currentContactName['contactname'], 'contactId' => $contact, 'nextPreviousResultset' => $nextPreviousResultset, 'offset' => $offset, 'groupUserDetails' => $groupUserDetails, 'groupDetails' => $groupDetails, 'loggedContactId' => $this->contactId, 'transAdministration' => $transAdministration, 'bookedModuleDetails' => $bookedModuleDetails, 'primaryEmail' => $currentContactName['email'], 'hasUserRights' => $hasUserRights, 'teamsArray' => json_encode($clubTeams['roles']), 'allTeamGroups' => json_encode($allTeamGroups));
        $return['tabs'] = $accessObj->tabArray;
        $return['teamGroupsDetails'] = json_encode($teamGroupsDetails);
        $return['teamGroupUserDetails'] = json_encode($teamGroupUserDetails);
        $return['pageGroupDetails'] = json_encode($pageGroupDetails);
        $return['pageGroupUserDetails'] = json_encode($pageGroupUserDetails);
        $return['dropdownList'] = json_encode($dropdownList, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
        $return['internalAdminList'] = json_encode($this->em->getRepository('CommonUtilityBundle:SfGuardGroup')->internalAdminList($this->conn));
        $internalAdmin = $this->em->getRepository('CommonUtilityBundle:SfGuardGroup')->getInternalAdmin($this->conn, $this->clubId, $masterTable, $contactFrom);
        $return['internalAdmin'] = json_encode($internalAdmin, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
        $return['pageJson'] = json_encode($pages);

        // Get Connection, Assignments, Notes count of a Contact.
        $isCompany = $currentContactName['is_company'];
        $contCountDetails = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getContactCountDetails($this->clubId, $contact, $isCompany, $this->clubType, true, true, true, false, false, false, false, $this->federationId, $this->subFederationId);
        $return = array_merge($return, $contCountDetails);
        $return['documentsCount'] = $this->em->getRepository('CommonUtilityBundle:FgDmAssigment')->getCountOfAssignedDocuments('CONTACT', $this->clubId, $contact);
        $contCountDetails['documentsCount'] = $return['documentsCount'];
        $return['tabs'] = FgUtility::getTabsArrayDetails($this->container, $accessObj->tabArray, $offset, $contact, $contCountDetails, "userright", "contact");
        $missingReqAssgn = $this->em->getRepository('CommonUtilityBundle:FgRmCategory')->missingReqFedAssign($contact, $this->clubId, $this->federationId, $this->subFederationId, $this->clubType, $this->clubDefaultLang, $this->conn);
        $return['missingReqAssgment'] = $missingReqAssgn;
        $return['isReadOnlyContact'] = $this->isReadOnlyContact();
        $contactNameUrl = str_replace('25','',$this->generateUrl('userrightscontact_name_search',array('term' => '%QUERY')));
        $return['contactNameUrl'] =$contactNameUrl;

        return $this->render('ClubadminContactBundle:Userrights:displayUserRights.html.twig', $return);
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
     * Function is used to format user rights save details to array from overview
     *
     * @param Array $userRightsArray userRightsArray
     *
     * @return template
     */
    private function formatOverviewUserRights($userRightsArray)
    {
        $finalArray = array();
        $fedAdmin = $this->container->getParameter('fed_admin');

        // Checking new rights from administration section
        if (isset($userRightsArray['new']['administrator']['admin'])) {
            foreach ($userRightsArray['new']['administrator']['admin']['group'] as $gKey => $gVal) {
                foreach ($gVal['contact'] as $cKey => $cVal) {
                    $finalArray['new']['group'][$gKey]['contact'][$cKey] = $gpVal;
                    $finalArray['new_users']['administrator'][$cKey]['id'] = $cKey;
                    $finalArray['new_users']['administrator'][$cKey]['type'] = 'administrator';
                }
            }
        }

        // Checking new rights from readonly admin section
        if (isset($userRightsArray['new']['readonly']['admin'])) {
            foreach ($userRightsArray['new']['readonly']['admin']['group'] as $gKey => $gVal) {
                foreach ($gVal['contact'] as $cKey => $cVal) {
                    $finalArray['new']['group'][$gKey]['contact'][$cKey] = $gpVal;
                    $finalArray['new_users']['readonly'][$cKey]['id'] = $cKey;
                    $finalArray['new_users']['readonly'][$cKey]['type'] = 'readonly';
                }
            }
        }

        // Checking new rights from club admin section
        if (isset($userRightsArray['new']['group'])) {
            foreach ($userRightsArray['new']['group'] as $gkey => $gval) {
                if ($gkey == $fedAdmin) {
                    foreach ($gval['contact'] as $contact => $key3) {
                        $records = $this->em->getRepository('CommonUtilityBundle:SfGuardGroup')->fedAdminEligibility($this->container, $this->conn, $this->clubId);
                        $fedAdminEligibility = array_column($records, 'id');
                        $hasFedAdminEligibility = in_array($contact, $fedAdminEligibility) ? 1 : 0;
                        if ($hasFedAdminEligibility)
                            $finalArray['new']['group'][$gkey] = $gval;
                    }
                } else
                    $finalArray['new']['group'][$gkey] = $gval;
            }
        }

        // Checking deleted rights
        if (isset($userRightsArray['delete'])) {
            $finalArray['delete'] = $userRightsArray['delete'];
        }
        //team section
        if (isset($userRightsArray['teams']['teamSection'])) {
            $finalArray['new']['teamSection'] = $finalArray['new']['teamSection']['contact'] = array();
            $finalArray = $this->formatRoleSection($userRightsArray, $finalArray, 'teamSection');
        }
        //workgrp section
        if (isset($userRightsArray['teams']['wgSection'])) {
            $finalArray['new']['wgSection'] = $finalArray['new']['wgSection']['contact'] = array();
            $finalArray = $this->formatRoleSection($userRightsArray, $finalArray, 'wgSection');
        }

        // Checking new team admin rights from club admin section
        if (isset($userRightsArray['teams']['teamAdmin'])) {
            foreach ($userRightsArray['teams']['teamAdmin'] as $tKey => $tVal) {
                foreach ($tVal['module'] as $mKey => $mVal) {
                    if ($mVal == '') {
                        $finalArray['new']['teamAdmin'][$tKey]['contact'][$mKey]['team'] = 'deleted';
                    } else {
                        $finalArray['new']['teamAdmin'][$tKey]['contact'][$mKey]['team'] = $mVal;
                    }
                }
            }
        }
        // Checking new team admin rights from club admin section
        if (isset($userRightsArray['teams']['wgAdmin'])) {
            foreach ($userRightsArray['teams']['wgAdmin'] as $tKey => $tVal) {
                foreach ($tVal['module'] as $mKey => $mVal) {
                    if ($mVal == '') {
                        $finalArray['new']['wgAdmin'][$tKey]['contact'][$mKey]['team'] = 'deleted';
                    } else {
                        $finalArray['new']['wgAdmin'][$tKey]['contact'][$mKey]['team'] = $mVal;
                    }
                }
            }
        }
        // Checking new page admin rights from club admin section
        if (isset($userRightsArray['cms']['pgAdmin'])) {
            foreach ($userRightsArray['cms']['pgAdmin'] as $tKey => $tVal) {
                foreach ($tVal['module'] as $mKey => $mVal) {
                    if ($mVal == '') {
                        $finalArray['new']['pgAdmin'][$tKey]['contact'][$mKey]['team'] = 'deleted';
                    } else {
                        $finalArray['new']['pgAdmin'][$tKey]['contact'][$mKey]['team'] = $mVal;
                    }
                }
            }
        }
        if (isset($userRightsArray['delete']['group'])) {
            if (!isset($finalArray['delete']['group'])) {
                $finalArray['delete']['group'] = array();
            }
            foreach ($userRightsArray['delete']['group'] as $gpKey => $gpVal) {
                foreach ($gpVal['user'] as $usKey => $usVal) {
                    $finalArray['delete']['group'][$gpKey]['user'][$usKey] = 1;
                }
            }
        }
        if (isset($userRightsArray['admin'])) {
            foreach ($userRightsArray['admin'] as $admin => $rand) {
                foreach ($rand['contact'] as $contat => $contct) {
                    $contact = $contat;
                }
                foreach ($rand['group'] as $group => $value) {
                    if ($value) {
                        $finalArray['new']['group'][$group]['contact'] [$contact] = $contact;
                    }
                }
            }
        }

        return $finalArray; // Returning generated final Array
    }

    /**
     * Function is used to save user rights of a user from overview
     *
     * @return template
     */
    public function saveDisplayedUserRightsAction()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $userRightsArray = json_decode($request->get('postArr'), true); // Getting JSON array of user details from overview
        $userRightsArray = $this->formatOverviewUserRights($userRightsArray); // Calling format function to format the array to use the common save
        // Calling common save function to save the user rights
        $resultSuccess = $this->em->getRepository('CommonUtilityBundle:SfGuardGroup')->saveUserRights($this->conn, $userRightsArray, $this->clubId, $this->contactId, $this->container);
        
        /** Sync the contact name data to the Admin DB **/
        $userRightContacts = ((is_array(array_keys($userRightsArray['new']['group'][2]['contact'])))?array_keys($userRightsArray['new']['group'][2]['contact']):array()) +
                            ((is_array(array_keys($userRightsArray['new']['group'][17]['contact'])))?array_keys($userRightsArray['new']['group'][17]['contact']):array());
        $contactSyncObject = new FgContactSyncDataToAdmin($this->container);
        $contactSyncObject->updateUserRights($userRightContacts)->updateLastUpdated($this->clubId)->executeQuery();
        /***********************************************/

        if ($resultSuccess) {
            return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('USER_RIGHTS_SAVED_SUCCESS')));
        } else {
            return new JsonResponse(array('status' => 'SUCCESS'));
        }
    }

    /**
     * function to get userrights - groups tab
     * @return template
     */
    public function groupsUserrightsAction()
    {
        $club = $this->get('club');
        $bookedModuleDetails = $club->get('bookedModulesDet');
        $masterTable = $club->get('clubTable');
        if (!in_array('frontend1', $bookedModuleDetails)) {
            $this->fgpermission->checkClubAccess('', 'backend_contact_userrights');
        }
        $tabs = array('backend');
        if (in_array('frontend1', $bookedModuleDetails)) {
            $tabs = array('backend', 'internal', 'groupuserrights');
            if (in_array('frontend2', $bookedModuleDetails)) {
                $tabs[] = 'website';
            }
        }
        $tabsData = FgUtility::getTabsArrayDetails($this->container, $tabs, '', '', '', 'groupuserrights');
        $grpSection = $this->em->getRepository('CommonUtilityBundle:SfGuardGroup')->teamAdminList($this->conn, $this->clubId, 0, 0, $masterTable, $club);
        $autocompleteExistingContact = json_encode($this->getAllListingBlock($grpSection), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_HEX_AMP);
        $dropdownList = $this->em->getRepository('CommonUtilityBundle:FgRmRole')->getRoleTeamDetails($this->clubId, $this->container);
        $grpSection = json_encode($grpSection, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_HEX_AMP);
        $dropdownList = json_encode($dropdownList, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_HEX_AMP);
        $adminSection = $this->em->getRepository('CommonUtilityBundle:SfGuardGroup')->getAllTeamGroups();
        $adminSection = json_encode($this->formatRights($adminSection));
        $contactNameUrl = str_replace('25','',$this->generateUrl('userrightscontact_name_search',array('term' => '%QUERY')));
        
        return $this->render('ClubadminContactBundle:Userrights:groupUserrights.html.twig', array('contactNameUrl'=>$contactNameUrl,'groupAdmin' => $grpSection, 'loggedContactId' => $this->contactId, 'dropdown' => $dropdownList, 'tabs' => $tabsData, 'exclude' => $autocompleteExistingContact, 'admins' => $adminSection));
    }

    /**
     * function to format userrights dropdown
     * @param array $adminSection drop down list of section rights
     * @return array
     */
    private function formatRights($adminSection)
    {
        foreach ($adminSection as $key => $value) {
            switch ($value['name']) {
                case 'ContactAdmin': $adminSection[$key]['transName'] = $this->get('translator')->trans('UR_CONTACT_ADMIN');
                    break;
                case 'ForumAdmin': $adminSection[$key]['transName'] = $this->get('translator')->trans('UR_FORUM_ADMIN');
                    break;
                case 'DocumentAdmin': $adminSection[$key]['transName'] = $this->get('translator')->trans('UR_DOCUMENT_ADMIN');
                    break;
                case 'CalendarAdmin': $adminSection[$key]['transName'] = $this->get('translator')->trans('UR_CALENDAR_ADMIN');
                    break;
                case 'GalleryAdmin': $adminSection[$key]['transName'] = $this->get('translator')->trans('UR_GALLERY_ADMIN');
                    break;
                case 'ArticleAdmin': $adminSection[$key]['transName'] = $this->get('translator')->trans('UR_ARTICLE_ADMIN');
                    break;
            }
        }
        return $adminSection;
    }

    /**
     * Function is used to get all the user rights blocks as seperate array
     *
     * @param Array $groupContacts Group-contact details
     *
     * @return Array
     */
    private function getAllListingBlock($groupContacts)
    {

        $existingUserDetails = array();
        $existingSectionUser = array();
        $existingWGUserDetails = array();
        $existingWGSectionUser = array();
        $i = 0;
        $j = 0;
        $k = 0;
        $l = 0;
        $prevContactId = '';

        // Looping array containing all user rights details
        foreach ($groupContacts as $groupContact) {
            if ($groupContact['module_type'] == 'all' && $groupContact['type'] == 'role' && $groupContact['roleType'] == 'T') {
                $existingUserDetails = $this->assignValuesToGroup($existingUserDetails, $i, $groupContact);
                $i++;
            }
            if ($groupContact['module_type'] == 'all' && $groupContact['type'] == 'role' && $groupContact['roleType'] == 'W') {
                $existingWGUserDetails = $this->assignValuesToGroup($existingWGUserDetails, $k, $groupContact);
                $k++;
            }

            if ($groupContact['module_type'] != 'all' && $groupContact['type'] == 'role' && $groupContact['roleType'] == 'T') {
                $existingSectionUser = $this->assignValuesToGroup($existingSectionUser, $j, $groupContact);
                $j++;
            }
            if ($groupContact['module_type'] != 'all' && $groupContact['type'] == 'role' && $groupContact['roleType'] == 'W') {
                $existingWGSectionUser = $this->assignValuesToGroup($existingWGSectionUser, $l, $groupContact);
                $l++;
            }
        }

        return array('existingUserDetails' => $existingUserDetails, 'existingSectionUser' => $existingSectionUser,
            'existingWGUserDetails' => $existingWGUserDetails, 'existingWGSectionUser' => $existingWGSectionUser);
    }

    /**
     * Function is used to save user rights from user rights setting page
     *
     * @return JSON
     */
    public function saveGroupUserRightsAction()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $groupId = $this->container->getParameter('group_admin');
        $userRightsArray = json_decode($request->get('postArr'), true); // Getting save values from setting page in JSON format
        $formatArray['new'] = array();
        if (isset($userRightsArray['teams']['teamAdmin'])) {
            $formatArray['new']['teamAdmin'] = $formatArray['new']['teamAdmin'][$groupId] = $formatArray['new']['teamAdmin'][$groupId]['contact'] = array();
            foreach ($userRightsArray['teams']['teamAdmin'] as $val) {
                $formatArray = $this->formatTeamAdmin($userRightsArray, $formatArray, $val, 'T');
            }
        }

        if (isset($userRightsArray['teams']['wgAdmin'])) {
            $formatArray['new']['wgAdmin'] = $formatArray['new']['wgAdmin'][$groupId] = $formatArray['new']['wgAdmin'][$groupId]['contact'] = array();
            foreach ($userRightsArray['teams']['wgAdmin'] as $val) {
                $formatArray = $this->formatTeamAdmin($userRightsArray, $formatArray, $val, 'W');
            }
        }
        if (isset($userRightsArray['teams']['teamSection'])) {
            $formatArray['new']['teamSection'] = $formatArray['new']['teamSection']['contact'] = array();
            $formatArray = $this->formatRoleSection($userRightsArray, $formatArray, 'teamSection');
        }
        if (isset($userRightsArray['teams']['wgSection'])) {
            $formatArray['new']['wgSection'] = $formatArray['new']['wgSection']['contact'] = array();
            $formatArray = $this->formatRoleSection($userRightsArray, $formatArray, 'wgSection');
        }

        // Calling the function to save user rights
        $resultSuccess = $this->em->getRepository('CommonUtilityBundle:SfGuardGroup')->saveUserRights($this->conn, $formatArray, $this->clubId, $this->contactId, $this->container);

        if ($resultSuccess) {
            return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('USER_RIGHTS_SAVED_SUCCESS')));
        } else {
            return new JsonResponse(array('status' => 'SUCCESS'));
        }
    }

    /**
     * format role section area for save
     *
     * @param   array   $userRightsArray    user rights array
     * @param   array   $formatArray        format array
     * @param   string  $type               roletype
     * @return  array
     */
    private function formatRoleSection($userRightsArray, $formatArray, $type)
    {
        if (isset($userRightsArray['teams'][$type]['new'])) {

            foreach ($userRightsArray['teams'][$type]['new'] as $userRightsSubArray) {
                if (isset($userRightsSubArray['contact'])) {
                    if (is_array($userRightsSubArray['contact'])) {
                        foreach ($userRightsSubArray['contact'] as $contact => $value) {
                            $contactId = $contact;
                        }
                    } else {
                        $contactId = $userRightsSubArray['contact'];
                    }
                    if (isset($userRightsSubArray['module'])) {
                        foreach ($userRightsSubArray['module'] as $key => $val) {
                            $formatArray['new'][$type][$contactId]['role'][$val['teams']]['groups'] = $val['modules'];
                        }
                    }
                }
            }
        }
        //existing team admin modules
        if (isset($userRightsArray['teams'][$type]['existing'])) {
            foreach ($userRightsArray['teams'][$type]['existing'] as $contactId => $teams) {
                if ($teams['deleted'] == 1) {
                    $formatArray['new'][$type][$contactId]['deleted'] = 1;
                    continue;
                }
                foreach ($teams as $teamId => $modules) {
                    if (is_array($modules)) {
                        if ($modules['modules'] == '') {
                            $formatArray['new'][$type][$contactId]['role'][$teamId]['groups'] = 'deleted';
                        } else {
                            $formatArray['new'][$type][$contactId]['role'][$teamId]['groups'] = $modules['modules'];
                        }
                    } else if ($modules == 1) {
                        $formatArray['new'][$type][$contactId]['role'][$teamId]['groups'] = 'deleted';
                    }
                }
            }
        }

        return $formatArray;
    }

    /**
     * function to form array  structure for userrights save
     * @param array $userRightsArray    user rights array
     * @param array $formatArray        format array
     * @param array $val                contact array
     *
     * @return array
     */
    private function formatTeamAdmin($userRightsArray, $formatArray, $val, $roleType)
    {
        $groupId = $this->container->getParameter('group_admin');
        $role = 'teams';
        if ($roleType == 'T') {
            $grp = 'teamAdmin';
        } else {
            $grp = 'wgAdmin';
        }
        //existing team admin modules
        if (isset($userRightsArray['teams'][$grp]['existing'])) {
            foreach ($userRightsArray['teams'][$grp]['existing'] as $contact => $teams) {
                if (is_array($teams[$role])) {
                    $formatArray['new'][$grp][$groupId]['contact'][$contact]['team'] = $teams[$role];
                }
                if ($teams[$role] == '') {
                    $formatArray['new'][$grp][$groupId]['contact'][$contact]['team'] = 'deleted';
                }
            }
        }
        if (isset($userRightsArray['teams'][$grp]['delete'])) {
            foreach ($userRightsArray['teams'][$grp]['delete'] as $contactId => $true) {
                $formatArray['new'][$grp][$groupId]['contact'][$contactId]['team'] = 'deleted';
            }
        }
        if (isset($val['contact'])) {
            foreach ($val['contact'] as $value) {
                $contactId = $value;
            }
            if ($val[$role] == '') {
                $formatArray['new'][$grp][$groupId]['contact'][$contactId]['team'] = 'deleted';
            } else {
                $formatArray['new'][$grp][$groupId]['contact'][$contactId]['team'] = $val[$role];
            }
        }
        return $formatArray;
    }

    /**
     * backend setting Internal area userrights
     */
    public function internalAreaUserrightsAction()
    {

        $contact = $this->get('contact');
        $em = $this->getDoctrine()->getManager();

        $club = $this->get('club');
        $bookedModuleDetails = $club->get('bookedModulesDet');
        $tabs = array('backend');
        if (in_array('frontend1', $bookedModuleDetails)) {
            $tabs = array('backend', 'internal', 'groupuserrights');
            if (in_array('frontend2', $bookedModuleDetails)) {
                $tabs[] = 'website';
            }
        } else {
            $this->fgpermission->checkClubAccess('', 'backend_contact_userrights');
        }
        $tabsData = FgUtility::getTabsArrayDetails($this->container, $tabs, '', '', '', 'internal');

        $breadCrumb = array(
            'breadcrumb_data' => array(),
        );

        //get contact id from corresponding table(if club type is federation the table field name is fed_contact_id else contact_id)
        $contactFrom = ($club->get('type') === 'federation') ? "fed_contact_id" : "contact_id";
        $masterTable = $club->get('clubTable');
        $internalAdminList = json_encode($em->getRepository('CommonUtilityBundle:SfGuardGroup')->internalAdminList($this->conn));
        $internalAdmin = $em->getRepository('CommonUtilityBundle:SfGuardGroup')->getInternalAdmin($this->conn, $this->clubId, $masterTable, $contactFrom);
        $exclude = $this->excludeAdmins($internalAdmin);
        $exclude = json_encode($exclude['existingUserDetails'], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
        $internalAdmin = json_encode($internalAdmin, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
        $contactNameUrl = str_replace('25','',$this->generateUrl('userrightscontact_name_search',array('term' => '%QUERY')));

        return $this->render('ClubadminContactBundle:Userrights:internalAreaIndex.html.twig', array('clubId' => $this->clubId,'contactNameUrl'=>$contactNameUrl,
                'contactId' => $this->contactId, 'internalAdmin' => $internalAdmin, 'exclude' => $exclude,
                'loggedContactId' => $this->contactId, 'internalAdminList' => $internalAdminList,
                'breadCrumb' => $breadCrumb, 'tabs' => $tabsData));
    }

    /**
     * Function is used to get all the user rights blocks as seperate array.
     *
     * @param Array $groupContacts Group-contact details
     *
     * @return Array
     */
    private function excludeAdmins($groupContacts)
    {
        $existingUserDetails = array();
        $i = 0;
        // Looping array containing all user rights details
        foreach ($groupContacts as $groupContact) {
            $existingUserDetails = $this->assignValuesToGroup($existingUserDetails, $i, $groupContact);
            $i++;
        }

        return array('existingUserDetails' => $existingUserDetails);
    }

    /**
     * Function is used to save user rights from user rights setting page -Internal area tab
     *
     * @return JSON
     */
    public function saveInternalUserRightsAction()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $clubCalendarAdmin = $this->container->getParameter('club_calendar_admin');
        $clubGalleryAdmin = $this->container->getParameter('club_gallery_admin');
        $clubArticleAdmin = $this->container->getParameter('club_article_admin');
        $userRightsArray = json_decode($request->get('postArr'), true); // Getting save values from setting page in JSON format
        $finalArray['new'] = array();
        if (isset($userRightsArray['delete_all'])) {
            $finalArray['delete']['group'] = array();
            foreach ($userRightsArray['delete_all'] as $uKey => $uVal) {
                $finalArray['delete']['group'][$clubCalendarAdmin]['user'] = $uVal;
                $finalArray['delete']['group'][$clubGalleryAdmin]['user'] = $uVal;
                $finalArray['delete']['group'][$clubArticleAdmin]['user'] = $uVal;
            }
        }
        if (isset($userRightsArray['delete']['group'])) {
            if (!isset($finalArray['delete']['group'])) {
                $finalArray['delete']['group'] = array();
            }
            foreach ($userRightsArray['delete']['group'] as $gpKey => $gpVal) {
                foreach ($gpVal['user'] as $usKey => $usVal) {
                    $finalArray['delete']['group'][$gpKey]['user'][$usKey] = 1;
                }
            }
        }

        if (isset($userRightsArray['admin'])) {

            foreach ($userRightsArray['admin'] as $admin => $rand) {
                foreach ($rand['contact'] as $contat => $contct) {
                    $contact = $contat;
                }
                foreach ($rand['group'] as $group => $value) {
                    if ($value) {
                        $finalArray['new']['group'][$group]['contact'] [$contact] = $contact;
                    }
                }
            }
        }
        if (isset($userRightsArray['new']['group'])) {
            if (!isset($finalArray['new']['group'])) {
                $finalArray['new']['group'] = array();
            }
            foreach ($userRightsArray['new']['group'] as $gpKey => $gpVal) {
                foreach ($gpVal['contact'] as $cKey => $cVal) {
                    $finalArray['new']['group'][$gpKey]['contact'][$cKey] = $cKey;
                }
            }
        }
        // Calling the function to save user rights
        $resultSuccess = $this->em->getRepository('CommonUtilityBundle:SfGuardGroup')->saveUserRights($this->conn, $finalArray, $this->clubId, $this->contactId, $this->container);

        if ($resultSuccess) {
            return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('USER_RIGHTS_SAVED_SUCCESS')));
        } else {
            return new JsonResponse(array('status' => 'SUCCESS'));
        }
    }

    /**
     * Contact autocomplete action for fed admin
     *
     * @param string $term          search parameter
     * @param string $passedColumns Extra columns to be taken
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function contactNamesForFedadminAction(Request $request, $term, $passedColumns = "")
    {
        $exclude = $request->get('exclude');
        $isComany = $request->get('isCompany');
        $contType = $request->get('type');
        $contactsArray = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getAutocompleteFedadminContacts($exclude, $isComany, $contType, $this->container, $this->clubId, $this->clubType, $passedColumns, $term);

        return new JsonResponse($contactsArray);
    }

    //to display website section backend
    public function displayCmsUserrightAction()
    {        
        $club = $this->get('club'); // Club object
        $bookedModuleDetails = $club->get('bookedModulesDet');
        if (!in_array('frontend2', $bookedModuleDetails)) {
            $this->fgpermission->checkClubAccess('', 'backend_contact_userrights');
        }
        $cmsAdmin = $this->container->getParameter('cms_admin');

        // Function to get all assigned user rights with user details
        $existingUserCms = $this->em->getRepository('CommonUtilityBundle:SfGuardGroup')->getGroupDetailsAllUSerByGroup($this->clubId, $cmsAdmin);
        $existingUserPage = $this->em->getRepository('CommonUtilityBundle:SfGuardUserPage')->getPageAdmins($this->clubId);
        // Translation for default texts displayed in the settings page.
        // When a new block needed in the settings page, need to add the translation in this array for the block'
        $existingUserArray = $this->excludeAdmins($existingUserCms);
        $existingUserDetails = json_encode($existingUserArray['existingUserDetails'], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
        $existingPageAdminArray = $this->excludeAdmins($existingUserPage);
        $existingPageAdminDet = json_encode($existingPageAdminArray['existingUserDetails'], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
        $lang = $this->container->get('contact')->get('corrLang');
        $dropdown = $this->em->getRepository('CommonUtilityBundle:FgCmsPage')->getPagesList($this->clubId, $lang);
        $dropdownJson = json_encode($dropdown);

        // Function to get all user rights groups
        $groupDetails = $this->em->getRepository('CommonUtilityBundle:SfGuardGroup')->getAllGroups($this->conn);
        $allGroups = json_encode($groupDetails);
        $transAdministration = $this->transArray();
        $groupUserDetails = json_encode($existingUserCms);
        $groupPageUserDetails = json_encode($existingUserPage);
                
        $tabs = array('website');
        if (in_array('frontend1', $bookedModuleDetails)) {
            $tabs = array('backend', 'internal', 'groupuserrights');
            if (in_array('frontend2', $bookedModuleDetails)) {
                $tabs[] = 'website';
            }
        }
        $tabsData = FgUtility::getTabsArrayDetails($this->container, $tabs, '', '', '', 'website');
        $contactNameUrl = str_replace('25','',$this->generateUrl('userrightscontact_name_search',array('term' => '%QUERY')));

        return $this->render('ClubadminContactBundle:Userrights:displayCmsUserrights.html.twig', array('groupPageUserDetails' => $groupPageUserDetails,'contactNameUrl'=>$contactNameUrl,
                'dropdownJson' => $dropdownJson, 'pageList' => $dropdownJson, 'clubType' => $this->clubType, 'tabs' => $tabsData, 'existingUserDetails' => $existingUserDetails, 'loggedContactId' => $this->contactId, 'allGroups' => $allGroups, 'bookedModuleDetails' => $bookedModuleDetails, 'groupUserDetails' => $groupUserDetails, 'transAdministration' => $transAdministration, 'existingPageAdminDet' => $existingPageAdminDet, 'settings' => true));
    }
}
