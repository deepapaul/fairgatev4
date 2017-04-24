<?php

/**
 * ExternalApplicationConfirmation Controller
 *
 * This controller was created for handling the external form application
 * to be confirmed and discared in Contact management.
 *
 * @package    ClubadminContactBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 *
 */
namespace Clubadmin\ContactBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Controller\FgController as ParentController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Intl\Intl;
use Symfony\Component\HttpFoundation\Request;
use Common\UtilityBundle\Util\FgFedMemberships;
use Clubadmin\ContactBundle\Util\ContactDetailsSave;
use Common\UtilityBundle\Repository\Pdo\ContactPdo;
use Common\UtilityBundle\Repository\Pdo\membershipPdo;

/**
 * ExternalApplicationConfirmationController
 *
 * Description of ExternalApplicationConfirmationController
 */
class ExternalApplicationConfirmationController extends ParentController
{

    /**
     * Function for listing the external form data for confirm and discard
     *
     * @return object View Template Render Object
     */
    public function externalApplicationConfirmationAction()
    {
        $clubId = $this->container->get('club')->get('id');
        $extApplAvailableClubs = $this->container->getParameter('external_application_clubids');
        if (!in_array($clubId, $extApplAvailableClubs)) {
            $logoutUrl = $this->generateUrl('fairgate_user_security_logout');
            header('Location:' . $logoutUrl);
            exit;
        }
        $applicationCount = $this->em->getRepository('CommonUtilityBundle:FgExternalApplicationForm')->getExternalApplicationConfirmationCount($clubId);

        $applTabs = array(1 => 'list', 2 => 'log');
        $tabs = array(0 => 'applicationqueue',
            1 => 'applicationlog'
        );
        $defalutActiveTab = 'applicationqueue';
        $tabsData = FgUtility::getTabsArrayDetails($this->container, $tabs, '', '', $applicationCount, $defalutActiveTab, "fedapplication");

        $return = array('title' => $this->get('translator')->trans('APPLICATION_CONFIRMATION_TITLE'), 'pageTitle' => $this->get('translator')->trans('APPLICATION_CONFIRMATION_TITLE'), 'applTabs' => $applTabs, 'applicationCount' => $applicationCount, 'tabs' => $tabsData, 'page' => 'fedapplication', 'activeTab' => 1, 'clubType' => $this->clubType);

        return $this->render('ClubadminContactBundle:ExternalApplicationConfirmation:externalApplicationConfirmation.html.twig', $return);
    }

    /**
     * Function for getting the external form data for application listing
     *
     * @param string $type  application tab type
     *
     * @return object JSON Response Object
     */
    public function getExternalApplicationsDataAction($type)
    {
        $clubId = $this->container->get('club')->get('id');
        $applicationData = $this->em->getRepository('CommonUtilityBundle:FgExternalApplicationForm')
            ->getApplicationConfirmationListData($clubId, $type);

        array_walk_recursive($applicationData, function(&$data) {
            if (!is_string($data)) {
                return false;
            }
            $data = htmlspecialchars($data);
        });
        $return['aaData'] = $applicationData;
        return new JsonResponse($return);
    }

    /**
     * Function for showing the contact detail popup in external application confirmationlisting
     *
     * @return object View Template Render Object
     */
    public function getExternalFormDataForPopupAction($extId)
    {
        $externalApplData = $this->em->getRepository('CommonUtilityBundle:FgExternalApplicationForm')
            ->getExternalApplicationDataforPopup($extId);
        $contactName = $externalApplData['contactName'];
        $terminologyService = $this->container->get('fairgate_terminology_service');

        //Creates the contact system fields array needed for external application
        $contactFields = $this->container->get('club')->get('contactFields');
        $contactFieldsExternal = $this->container->getParameter('external_application_system_fields');
        foreach ($contactFieldsExternal as $key => $fieldId) {
            foreach ($contactFields as $fieldData) {
                if ($fieldData['id'] == $fieldId) {
                    $contactFieldsExternal[$key] = $fieldData['title'];
                }
            }
        }

        //Completing the contact fields array creation using other fields available for external application
        $contactFieldsExtra = array_slice($contactFieldsExternal, 0, 10, true) +
            array("relatives" => $this->container->get('translator')->trans('EXTERNAL_APPLICATION_RELATIVES')) +
            array_slice($contactFieldsExternal, 10, count($contactFieldsExternal) - 1, true);

        $otherFields = array('membershipTitle' => $terminologyService->getTerminology('Fed membership', $this->container->getParameter('singular')),
            'selectedClubs' => $this->container->get('translator')->trans('EXTERNAL_APPLICATION_FORM_CLUB_CHOICE'),
            'comment' => $this->container->get('translator')->trans('EXTERNAL_APPLICATION_COMMENT'));

        $contactFieldsFinal = array_merge($contactFieldsExtra, $otherFields);

        return $this->container->get('templating')
                ->renderResponse('ClubadminContactBundle:ExternalApplicationConfirmation:externalFormPopup.html.twig', array('contactName' => $contactName, 'externalApplData' => $externalApplData, 'contactFields' => $contactFieldsFinal));
    }

    /**
     * This action is used to confirm or discard Application.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $action Whether to confirm or discard
     *
     * @Template("ClubadminContactBundle:ExternalApplicationConfirmation:confirmOrDiscardApplications.html.twig")
     *
     * $page = {creations/mutations}
     * $action = {confirm/discard}
     * $selActionType = {all/selected}
     *
     * @return array Data array.
     */
    public function getConfirmPopupAction(Request $request, $action)
    {
        $selActionType = $request->get('selActionType') ? $request->get('selActionType') : 'all';
        $return['selActionType'] = $selActionType;
        $return['action'] = $action;

        return $return;
    }

    /**
     * Function to confirm or discard the changes.
     *
     * @param Request $request Request object
     *
     * @return JsonResponse Confirmed/Discarded status
     */
    public function doConfirmExternalApplicationAction(Request $request)
    {
        $action = $request->get('action');
        $selectedIds = json_decode($request->get('selectedId', '0'));
        $clubIdArray = $this->getClubArray();
        $confirmSuccess = $this->em->getRepository('CommonUtilityBundle:FgCmChangeToconfirm')->confirmOrDiscardChanges($action, $this->clubId, $selectedIds, $this->container, $this->clubDefaultSystemLang, $this->get('club'), $this->contactId, $clubIdArray, $this->get('fairgate_terminology_service'));
        $flashMsg = ($action == 'confirm') ? '%selcount%_OUT_OF_%totalcount%_CHANGES_CONFIRMED_SUCCESSFULLY' : '%selcount%_OUT_OF_%totalcount%_CHANGES_DISCARDED_SUCCESSFULLY';

        return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->container->get('translator')->trans($flashMsg, array('%selcount%' => $confirmSuccess['successCount'], '%totalcount%' => $confirmSuccess['totalContacts']))));
    }

    /**
     * This action is used to confirm or discard application.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * $page = {creations/mutations}
     * $action = {confirm/discard}
     * $selActionType = {all/selected}
     *
     * @return Json Response.
     */
    public function updateApplicationAction(Request $request)
    {
        $action = $request->get('action');
        $selectedIds = json_decode($request->get('selectedId', '0'));
        $objExternalForm = $this->em->getRepository('CommonUtilityBundle:FgExternalApplicationForm');
        $clubId = $this->container->get('club')->get('id');
        $successCount = 0;
        if ($action == 'discard') {
            foreach ($selectedIds as $confirmId) {

                $result = $objExternalForm->updateApplicationStatus($confirmId, 'discarded', $this->container->get('contact')->get('id'));
                $successCount++;
            }
        }
        if ($action == 'confirm') {
            $flashMsg = (count($selectedIds) == $result) ? 'APPLICATION_CONFIRMED_SUCCESSFULLY' : 'APPLICATION_CONFIRMED_SUCCESSFULLY';
            $flash = $this->container->get('translator')->trans($flashMsg, array('%totalcount%' => count($selectedIds), '%selcount%' => $successCount));
        } else {
            $flashMsg = 'APPLICATION_DISCARDED_SUCCESSFULLY';
            $flash = $this->container->get('translator')->trans($flashMsg);
        }
        $countNav = $objExternalForm->getExternalApplicationConfirmationCount($clubId);
        return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $flash, 'noparentload' => false, 'topcount' => $countNav, 'count' => count($selectedIds)));
    }

    /**
     * This action is used to confirm or discard Application.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $action Whether to confirm or discard
     *
     * @Template("ClubadminContactBundle:ExternalApplicationConfirmation:confirmOrDiscardClubAssignmentApplications.html.twig")
     *
     * $page = {creations/mutations}
     * $action = {confirm/discard}
     * $selActionType = {all/selected}
     *
     * @return array Data array.
     */
    public function confirmOrDiscardClubAssignmentApplicationPopupAction(Request $request, $action)
    {
        $selActionType = $request->get('selActionType') ? $request->get('selActionType') : 'all';
        $return['selActionType'] = $selActionType;
        $return['action'] = $action;
        if (selActionType == 'selected') {
            $return['title'] = ($action == 'confirm') ? $this->container->get('translator')->trans('CONFIRM_SELECTED_APPLICATION_TITLE') : $this->container->get('translator')->trans('DISCARD_SELECTED_APPLICATION_TITLE');
            $return['content'] = ($action == 'confirm') ? $this->container->get('translator')->trans('CONFIRM_SELECTED_APPLICATION') : $this->container->get('translator')->trans('DISCARD_SELECTED_APPLICATION');
        } else {
            $return['title'] = ($action == 'confirm') ? $this->container->get('translator')->trans('CONFIRM_ALL_APPLICATION_TITLE') : $this->container->get('translator')->trans('DISCARD_ALL_APPLICATION_TITLE');
            $return['content'] = ($action == 'confirm') ? $this->container->get('translator')->trans('CONFIRM_ALL_APPLICATION') : $this->container->get('translator')->trans('DISCARD_ALL_APPLICATION');
        }

        return $return;
    }

    /**
     * Function to confirm mergable contacts
     *
     * @return JsonResponse
     */
    public function saveexternalcontactAction(Request $request)
    {
        $contactData = $request->get('contactData', '');
        $mergeTo = $request->get('mergeTo', '');
        $merging = $request->get('merging', '');
        $mergeType = $request->get('mergeType', '');
        $selectedMembership = $request->get('selectedMembership', '');
        $alreadyCreated = $request->get('alreadyActivated', '');
        $totalCnt = $request->get('totalCnt', '');
        $objExternal = $this->em->getRepository('CommonUtilityBundle:FgExternalApplicationForm');
        $club = $this->get('club');

        if ($mergeType != 'multiple') {

            if ($merging == 'save') {
                $user_id = $contactData['id'];
                $user_array = array('0' => $user_id);
                $userArr = $objExternal->getExternalUsersDetails($club->get('id'), $user_array);
                if ($mergeTo == 'fed_mem') {
                    $this->createExternalApplication($userArr[0], $userArr[0]['fed_membership']);
                    $alreadyCreated++;
                    $flag = true;
                } else {
                    $fedContactId = $mergeTo;
                    $clubIds = explode(',', $userArr[0]['club_selected']);
                    $getFedClubIdsData = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getFedContactAssignedClubs($fedContactId, $club->get('default_lang'));
                    $fedContactClub = array();
                    $k = 0;
                    foreach ($getFedClubIdsData as $fedClubdata) {
                        $fedContactClub[$k] = $fedClubdata['Club_id'];
                        $k++;
                    }
                    $club_not_exist = array_diff($clubIds, $fedContactClub);
                    if (count($club_not_exist) > 0) {
                        $clubDetails = $this->em->getRepository('CommonUtilityBundle:FgClub')->getClubsValues($club_not_exist, $club->get('default_lang'));
                        foreach ($clubDetails as $clubshared) {
                            $this->shareMembership($clubshared, $fedContactId, $userArr[0]['id']);
                            $alreadyCreated++;
                        }
                    } else {
                        $statusUpdated = $this->em->getRepository('CommonUtilityBundle:FgExternalApplicationForm')->updateApplicationStatus($userArr[0]['id'], 'confirmed', $this->container->get('contact')->get('id'), $fedContactId);
                    }
                    $flag = true;
                    $this->confirmNotificationMail($userArr[0]);
                }

                $flashMsg = '%selcount%_OUT_OF_%totalcount%_APPLICATIONS_CONFIRMED_SUCCESSFULLY';
                $status = array('status' => 'SUCCESS', 'totalCount' => count($contactData['id']), 'noparentload' => false, 'flash' => $this->get('translator')->trans($flashMsg, array('%totalcount%' => count($contactData['id']), '%selcount%' => count($contactData['id']))));
            } else {
                //Cancel
                if (!$flag) {
                    $flashMsg = 'APPLICATION_NOT_SUCCESS';
                    $status = array('status' => 'FAILURE', 'totalCount' => $totalCnt, 'noparentload' => false, 'flash' => $this->get('translator')->trans($flashMsg));
                }
            }
        } else {

            if ($merging == 'save') {
                // add membership and confirm selected ids for mergable contacts

                foreach ($mergeTo as $merFrm => $merTo) {
                    $user_id = $merFrm;
                    $user_array = array('0' => $user_id);
                    $userArr = $objExternal->getExternalUsersDetails($club->get('id'), $user_array);
                    if ($merTo['applymer'] == 'fed_mem') {
                        $this->createExternalApplication($userArr[0], $userArr[0]['fed_membership']);
                        $alreadyCreated++;
                        $flag = true;
                    } else {

                        $fedContactId = $merTo['applymer'];
                        $contactClubId = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getContactClubId($fedContactId);
                        $clubIds = explode(',', $userArr[0]['club_selected']);
                        $getFedClubIdsData = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getFedContactAssignedClubs($fedContactId, $club->get('default_lang'));
                        $fedContactClub = array();
                        $k = 0;

                        foreach ($getFedClubIdsData as $fedClubdata) {
                            $fedContactClub[$k] = $fedClubdata['Club_id'];
                            $k++;
                        }
                        $club_not_exist = array_diff($clubIds, $fedContactClub);
                        $clubDetails = $this->em->getRepository('CommonUtilityBundle:FgClub')->getClubsValues($club_not_exist, $club->get('default_lang'));
                        if (count($club_not_exist) > 0) {
                            foreach ($clubDetails as $clubshared) {
                                $this->shareMembership($clubshared, $fedContactId, $userArr[0]['id']);
                            }
                        } else {
                            $statusUpdated = $this->em->getRepository('CommonUtilityBundle:FgExternalApplicationForm')->updateApplicationStatus($userArr[0]['id'], 'confirmed', $this->container->get('contact')->get('id'), $fedContactId);
                        }
                        $alreadyCreated++;
                        $flag = true;
                        $this->confirmNotificationMail($userArr[0]);
                    }
                }
                if (!$flag) {
                    $flashMsg = 'APPLICATION_NOT_SUCCESS';
                    $status = array('status' => 'FAILURE', 'totalCount' => $totalCnt, 'noparentload' => false, 'flash' => $this->get('translator')->trans($flashMsg));
                } else {
                    $flashMsg = '%selcount%_OUT_OF_%totalcount%_APPLICATIONS_CONFIRMED_SUCCESSFULLY';
                    $status = array('status' => 'SUCCESS', 'totalCount' => $totalCnt, 'noparentload' => false, 'flash' => $this->get('translator')->trans($flashMsg, array('%totalcount%' => $totalCnt, '%selcount%' => $alreadyCreated)));
                }
            } else {
                //Cancel
                if (!$flag && $alreadyCreated == 0) {

                    $flashMsg = 'APPLICATION_NOT_SUCCESS';
                    $status = array('status' => 'FAILURE', 'totalCount' => $totalCnt, 'noparentload' => false, 'flash' => $this->get('translator')->trans($flashMsg));
                } else {//Cancel & Create
                    $flashMsg = '%selcount%_OUT_OF_%totalcount%_APPLICATIONS_CONFIRMED_SUCCESSFULLY';
                    $status = array('status' => 'SUCCESS', 'totalCount' => $totalCnt, 'noparentload' => false, 'flash' => $this->get('translator')->trans($flashMsg, array('%totalcount%' => $totalCnt, '%selcount%' => $alreadyCreated)));
                }
            }
        }


        return new JsonResponse($status);
    }

    /**
     * Function to Show Membership Popup
     * @return html
     */
    public function showFedmembershipPopupAction(Request $request)
    {
        //Get the POST data
        $archiveDatas = json_decode($request->get('selectedId', ''));
        $clubService = $this->container->get('club');
        $clubDefaultLang = $clubService->get('default_lang');
        $fedMembershipMandatory = 1;
        $totCount = count($archiveDatas);
        $userMembership = 0;
        if ($totCount == 1) {
            $objExternalForm = $this->em->getRepository('CommonUtilityBundle:FgExternalApplicationForm');
            $returnUser = $objExternalForm->getExternalUsersDetails($this->container->get('club')->get('id'), $archiveDatas);
            $userMembership = $returnUser[0]['fed_membership'];
        }
        $fedId = $clubService->get('type') == 'federation' ? $clubService->get('id') : $clubService->get('federation_id');
        if ($archiveDatas != '') {
            $contactNameArray = $this->em->getRepository('CommonUtilityBundle:FgExternalApplicationForm')->getContactNameExternalUsers($clubService->get('id'), $archiveDatas);
            $return = array('contactnames' => $contactNameArray, 'contactIds' => $archiveDatas);
            if ($fedMembershipMandatory) {
                $objMembershipPdo = new \Common\UtilityBundle\Repository\Pdo\membershipPdo($this->container);
                $membersipFields = $objMembershipPdo->getMemberships($clubService->get('type'), $clubService->get('id'), '', $clubService->get('federation_id'));
                foreach ($membersipFields as $key => $memberCat) {
                    $title = $memberCat['allLanguages'][$clubDefaultLang]['titleLang'] != '' ? $memberCat['allLanguages'][$clubDefaultLang]['titleLang'] : $memberCat['membershipName'];
                    if ($this->federationId == $memberCat['clubId']) {
                        $memberships['fed'][$key] = $title;
                    }
                }
                $return['fedTitle'] = $this->get('translator')->trans('REACTIVATE_FED_MEMBERSHIP_POPUP_TITLE');
                $return['fedlogoPath'] = FgUtility::getClubLogo($fedId, $this->em);
                $return['fedMembershipMandatory'] = $fedMembershipMandatory;
                $return['fedmembership'] = array_keys($memberships['fed']);
                $return['fed_memberships_array'] = $memberships['fed'];
                $return['userMembership'] = $userMembership;
                $return['selectedId'] = $request->get('selectedId', '');
                $return['selActionType'] = $request->get('selActionType', '');
            }
        }

        return $this->render('ClubadminContactBundle:ExternalApplicationConfirmation:showfedmembership.html.twig', $return);
    }

    /**
     * Function To Create or Show Merge Popup
     * @return JsonResponse
     */
    public function getSelectedExternalApplicationAction(Request $request)
    {
        $primaryEmail = $this->container->getParameter('system_field_primaryemail');
        $clubId = $this->container->get('club')->get('id');
        $action = $request->get('action');

        //Get the POST data
        $archiveDatas = $request->get('archivedData', '');

        if ($archiveDatas != '') {
            $contactType = $archiveDatas['contactType'];
            $selectedIds = json_decode($archiveDatas['selcontactIds']);
            $totCount = count($selectedIds);
            
            $objExternalForm = $this->em->getRepository('CommonUtilityBundle:FgExternalApplicationForm');
            $returnUser = $objExternalForm->getExternalUsersDetails($clubId, $selectedIds);
            $container = $this->container->getParameterBag();
            $meargable = array();
            $count = 0;
            
            foreach ($returnUser as $userArr) {
                $mergeableReturn = $objExternalForm->checkUserForMerge($clubId, $userArr, $container);
                $userMergeArrOption = $this->checkMergeable($mergeableReturn, $userArr);

                $selectedMembership = $userArr['fed_membership'];
                
                // Checking the contact is mergable if single contact selected for confirmation
                if (count($selectedIds) == 1) {
                    //Check for fedown Contact
                    if ($userMergeArrOption['skipped'] != 1) {
                        if ($userMergeArrOption['fed'] == 1 && $userMergeArrOption['create'] == 1) {
                            $this->createExternalApplication($userArr, $selectedMembership);
                            $count++;
                        } else if (count($mergeableReturn['duplicates']) > 0 || count($mergeableReturn['mergeEmail']) > 0) {
                            $contactId = $userArr['id'];
                            $currentUserData = array('2' => $userArr['firstname'], 'id' => $userArr['id'], '3' => $userArr['email'], '4' => $userArr['dob'], 'Gender' => $userArr['gender'], '23' => $userArr['lastname'], '47' => $userArr['street'], '79' => $userArr['zipcode'], '77' => $userArr['location'], 'club_id' => $userArr['club_id']);
                            $mergeableReturn['currentContactData'] = $currentUserData;
                            $meargable[$contactId]['meargable'] = $mergeableReturn;
                            $mergeCount = 'SINGLE';
                            $meargable = true;
                        } else {
                            $this->createExternalApplication($userArr, $selectedMembership);
                            $count++;
                        }
                    } else if ($userMergeArrOption['skipped'] == 1) {
                        continue;
                    }
                } else {
                    if ($userMergeArrOption['skipped'] != 1) {
                        // Checking the contact is mergable if multiple contact selected for confirmation
                        if (count($mergeableReturn['duplicates']) > 0 || count($mergeableReturn['mergeEmail']) > 0) {
                            $contactId = $userArr['id'];
                            $currentUserData = array('2' => $userArr['firstname'], '3' => $userArr['email'], '4' => $userArr['dob'], 'Gender' => $userArr['gender'], '23' => $userArr['lastname'], '47' => $userArr['street'], '79' => $userArr['zipcode'], '77' => $userArr['location'], 'club_id' => $userArr['club_id']);
                            $meargable[$contactId]['currentContactData'] = $currentUserData;
                            $meargable[$contactId]['meargable'] = $mergeableReturn;
                            $mergeCount = 'MULTIPLE';
                        } else {
                            $this->createExternalApplication($userArr, $selectedMembership);
                            $count++;
                        }
                    }
                }
            }

            if ((count($meargable) > 0) || ($meargable)) {
                $mergeableReturn['status'] = 'MERGE';
                $mergeableReturn['noparentload'] = true;
                $mergeableReturn['mergeable'] = true;
                $mergeableReturn['mergableContacts'] = $meargable;
                $mergeableReturn['page'] = $page;
                $mergeableReturn['selectedMembership'] = $selectedMembership;
                $mergeableReturn['selectedIds'] = $selectedIds;
                $mergeableReturn['alreadyActivatedCnt'] = $count;
                $mergeableReturn['contactDetails'] = $contactDetailsCpy;
                $mergeableReturn['mergeCount'] = $mergeCount;
                $mergeableReturn['totalCnt'] = $totCount;

                return new JsonResponse($mergeableReturn);
            }
        }
        $page = "confirmed";
        $action = ($count > 0) ? 'confirm' : '';
        $flashMsg = ($action == 'confirm') ? '%selcount%_OUT_OF_%totalcount%_APPLICATIONS_CONFIRMED_SUCCESSFULLY' : '%selcount%_OUT_OF_%totalcount%_APPLICATIONS_DISCARDED_SUCCESSFULLY';

        return new JsonResponse(array('status' => 'SUCCESS', 'totalCnt' => $totCount, 'totCount' => $totCount, 'flash' => $this->container->get('translator')->trans($flashMsg, array('%selcount%' => $count, '%totalcount%' => $totCount)), 'noparentload' => false, 'count' => $count));
    }

    /**
     * Function to Create User Profiles
     * @param array $userForm UserData
     * @param int $fedMembership Selected Membership
     * @return array
     */
    private function createExternalApplication($userForm, $fedMembership)
    {
        $clubIdArray = $this->getClubArray();
        $fieldLanguages = $this->getLanguageArray();
        $club = $this->get('club');
        $fieldType = 'Single person';
        //build contact field array
        $fieldDetails1 = $this->em->getRepository('CommonUtilityBundle:FgCmAttributeset')->getAllClubContactFields($clubIdArray, $this->conn, 0, $fieldType);
        $fieldDetails = $this->em->getRepository('CommonUtilityBundle:FgCmAttributeset')->fieldItrator($fieldDetails1);
        $fieldDetailArray = array('fieldType' => $fieldType, 'memberships' => $this->getMembershipArray(), 'clubIdArray' => $clubIdArray, 'fedMemberships' => $this->fedMemberships, 'fullLanguageName' => $fieldLanguages, 'selectedMembership' => '', 'contactId' => false, 'deletedFiles' => array());
        $fieldDetails = array_merge($this->setTerminologyTerms($fieldDetails), $fieldDetailArray);
        //Create Contact
        $fedFields = $club->get('fedFields');
        $subFedFields = $clubFields = array();
        $formValues = $this->generateExternalApplicationForm($userForm);
        $clubIds = explode(',', $userForm['club_selected']);
        $clubDetails = $this->em->getRepository('CommonUtilityBundle:FgClub')->getClubsValues($clubIds, $club->get('default_lang'));
        $clubDetails[0]['defaultSystemLang'] = $club->get('default_system_lang');
        $createClub = $clubDetails[0];
        //Create Contact
        $contact = new ContactDetailsSave($this->container, $fieldDetails, array(), false, 1);
        $contact->setClubVariables($createClub, $fedFields, $subFedFields, $clubFields);
        $contactIdNew = $contact->saveContact($formValues, array());

        //Save Membership
        $fgFedMembershipObj = new FgFedMemberships($this->container);
        $fgFedMembershipObj->setClubDetailsForExternalApplication($createClub);
        $fgFedMembershipObj->processFedMembership($contactIdNew, $fedMembership);
        $contactDetails = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getContactAndFedMembershipDetails($contactIdNew);
        $fedContactId = $contactDetails['fedContactId'];
        $pdo = new ContactPdo($this->container);
        $pdo->insertIntoSfguardUser($fedContactId);

        //Sharing
        foreach ($clubDetails as $clubshared) {
            if ($clubDetails[0]['id'] != $clubshared['id']) {
                $this->shareMembership($clubshared, $fedContactId, $userForm['id']);
            }
        }

        $statusUpdated = $this->em->getRepository('CommonUtilityBundle:FgExternalApplicationForm')->updateApplicationStatus($userForm['id'], 'confirmed', $this->container->get('contact')->get('id'), $fedContactId);
        $this->confirmNotificationMail($userForm);

        return;
    }
    
    /**
	 * Function to send email -- confirmation of external application form
	 *
     * @param array $userForm external application details
	 *
	 * @return object View Template Render Object
	 */
    private function confirmNotificationMail($userForm)
    {
        $body = $this->getNotificationMailTemplate($userForm['id']);
        $subject = $this->container->get('translator')->trans('EXTERNAL_APPLICATION_CONFIRMATION_FORM_SUBJECT');
        $this->sendSwiftMesage($body, $userForm['email'], 'noreply@fairgate.ch', $subject, 'Fairgate AG');
    }
    
    /**
	 * Function to get the body content for notification mail in external application form
	 *
	 * @param int $extId   external form id
	 *
	 * @return object View Template Render Object
	 */
	private function getNotificationMailTemplate($extId)
	{

		$externalApplData = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgExternalApplicationForm')->getExternalApplicationDataforPopup($extId);
		
		$contactFieldsFinal = $this->getExternalApplicationFieldsForMail();
		$clubObj = $this->container->get('club');
		$clubTitle = $clubObj->get('title');
		$clubLogoUrl = $this->getClubLogoForExternalApplication($clubObj);
		$salutation = $this->getSalutationofContactForMail($externalApplData['gender'], $clubObj->get('id'));
		$rendered = $this->renderView('ClubadminContactBundle:ExternalApplicationConfirmation:notificationMailApplicationTemplate.html.twig', array(
			'clubTitle' => $clubTitle,
			'salutation' => $salutation,
			'logoURL' => $clubLogoUrl,
			'externalApplData' => $externalApplData,
			'contactFields' => $contactFieldsFinal,
            'signature' =>$clubObj->get('signature'),
            'internalUrl' => FgUtility::getBaseUrl($this->container)."/".$clubObj->get('clubUrlIdentifier')."/internal/signin"
		));

		return $rendered;
	}
    
    /**
	 * Function to get the contatc fields details for  notification mail in external application form
	 *
	 * @return array $contactFieldsFinal contact fields array
	 */
	private function getExternalApplicationFieldsForMail()
	{

		$terminologyService = $this->container->get('fairgate_terminology_service');
		//Creates the contact system fields array needed for external application mail
		$contactFields = $this->container->get('club')->get('contactFields');

		$contactFieldsExternal = $this->container->getParameter('external_application_system_fields');
		foreach ($contactFieldsExternal as $key => $fieldId) {
			foreach ($contactFields as $fieldData) {
				if ($fieldData['id'] == $fieldId) {
					$contactFieldsExternal[$key] = $fieldData['title'];
				}
			}
		}

		//Completing the contact fields array creation using other fields available for external application mail
		$contactFieldsExtra = array_slice($contactFieldsExternal, 0, 10, true) +
			array("relatives" => $this->container->get('translator')->trans('EXTERNAL_APPLICATION_RELATIVES')) +
			array_slice($contactFieldsExternal, 10, count($contactFieldsExternal) - 1, true);

		$otherFields = array('membershipTitle' => $terminologyService->getTerminology('Fed membership', $this->container->getParameter('singular')),
			'selectedClubs' => $this->container->get('translator')->trans('EXTERNAL_APPLICATION_FORM_CLUB_CHOICE'),
			'comment' => $this->container->get('translator')->trans('EXTERNAL_APPLICATION_COMMENT'));

		$contactFieldsFinal = array_merge($contactFieldsExtra, $otherFields);

		return $contactFieldsFinal;
	}
    
    /**
	 * Function to get the salutation for notification mail in external application form
	 *
	 * @param object $clubObj  club listener object
	 *
	 * @return string|null  $clubLogoUrl club logo url
	 */
	public function getClubLogoForExternalApplication($clubObj)
	{
		$clubLogo = $clubObj->get('logo');
		$rootPath = FgUtility::getRootPath($this->container);
		$baseurl = FgUtility::getBaseUrl($this->container);
		if ($clubLogo == '' || !file_exists($rootPath . '/' . FgUtility::getUploadFilePath($clubObj->get('id'), 'clublogo', false, $clubLogo))) {
			$clubLogoUrl = '';
		} else {
			$clubLogoUrl = $baseurl . '/' . FgUtility::getUploadFilePath($clubObj->get('id'), 'clublogo', false, $clubLogo);
		}
		return $clubLogoUrl;
	}
    
    /**
	 * Function to get the salutation for notification mail in external application form
	 *
	 * @param string $gender  gender of contact
	 * @param int    $clubId  current club id
	 *
	 * @return string $salutation salutation value
	 */
	public function getSalutationofContactForMail($gender, $clubId)
	{
        $salutation = '';
        $defSysLang = $this->container->get('club')->get('default_system_lang');
		$salutationObj = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgClubSalutationSettings')
			->findOneBy(array('club' => $clubId));
        if ($salutationObj) {
            $salutationLangObj = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgClubSalutationSettingsI18n')
                ->findOneBy(array('id' => $salutationObj->getId(), 'lang' => $defSysLang));
            if($salutationLangObj){
                $salutation = ($gender == 'male') ? $salutationLangObj->getMaleFormalLang() : $salutationLangObj->getFemaleFormalLang();
            }else{
                $salutation = ($gender == 'male') ? $salutationObj->getMaleFormal() : $salutationObj->getFemaleFormal();
            }
        }
        if ($salutation == '') {
			$salutationObj = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgClubSalutationSettings')
				->findOneBy(array('club' => 1));
            $salutationLangObj = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgClubSalutationSettingsI18n')
                ->findOneBy(array('id' => $salutationObj->getId(), 'lang' => $defSysLang));
            $salutation = ($gender == 'male') ? $salutationLangObj->getMaleFormalLang() : $salutationLangObj->getFemaleFormalLang();
		}
        
		return $salutation;
	}
    
    /**
	 * Function to send mail after saving the external application form
	 *
	 * @param string $bodyNew     body content
	 * @param string $email       Email addresss to send
	 * @param string $senderEmail Sender email
	 * @param string $subject     Email subject
	 * @param string $senderName  Sender name
	 *
	 * @return null
	 */
	public function sendSwiftMesage($bodyNew, $email, $senderEmail, $subject, $senderName)
	{
		$mailer = $this->get('mailer');
		$message = \Swift_Message::newInstance()
			->setSubject($subject)
			->setFrom(array($senderEmail => $senderName))
			->setTo($email)
			->setBody(stripslashes($bodyNew), 'text/html');

		$message->setCharset('utf-8');
		$mailer->send($message);
	}

    /**
     * Function to Share Membership
     * @param array $clubshared
     * @param int $fedContactId
     * @param int $userId
     */
    private function shareMembership($clubshared, $fedContactId, $userId)
    {
        $fgFedMembershipObj = new FgFedMemberships($this->container);
        $fgFedMembershipObj->setClubDetailsForExternalApplication($clubshared);
        $fgFedMembershipObj->addExistingFedMember($fedContactId);
        $statusUpdated = $this->em->getRepository('CommonUtilityBundle:FgExternalApplicationForm')->updateApplicationStatus($userId, 'confirmed', $this->container->get('contact')->get('id'), $fedContactId);
    }

    /**
     * Function to Create User Profiles
     * @param array $array_values
     * @return array
     */
    private function generateExternalApplicationForm($array_values)
    {

        $container = $this->container->getParameterBag();
        $personalCat = $container->get('system_category_personal');
        $communicationCat = $container->get('system_category_communication');
        $categoryCorrespondance = $container->get('system_category_address');
        $corress_lang = $container->get('system_field_corress_lang');
        $fedFieldCat = 6;
        $telgField = 72585;
        $employerCat= $container->get('external_application_fedfield_category');
        $employerField = $container->get('external_application_system_fields')['employer']; 
        $personalNumberField = $container->get('external_application_system_fields')['personalNumber']; ;
        $salutation = $container->get('system_field_salutaion');
        $array_fields["$personalCat"] = array('firstname' => $container->get('system_field_firstname'), 'lastname' => $container->get('system_field_lastname'), 'gender' => $container->get('system_field_gender'), 'dob' => $container->get('system_field_dob'));
        $array_fields["$communicationCat"] = array('email' => $container->get('system_field_primaryemail'), 'tel_m' => $container->get('system_field_mobile1'));
        $array_fields["$categoryCorrespondance"] = array('street' => $container->get('system_field_corres_strasse'), 'zipcode' => $container->get('system_field_corres_plz'), 'location' => $container->get('system_field_corres_ort'));
        $array_fields["$fedFieldCat"]['telg'] = $telgField;
        $array_fields["$employerCat"]['employer'] = $employerField;
        $array_fields["$employerCat"]['personal_number'] = $personalNumberField;
        
        $form_array = array();
        $form_array['system']['contactType'] = 'Single person';
        $form_array['system']['attribute'] = array('0' => 'Intranet access');
        $form_array["$personalCat"]["$salutation"] = 'Formal';
        $form_array["$communicationCat"]["$corress_lang"] = $this->get('club')->get('default_system_lang');
        foreach ($array_values as $key => $values) {

            //personnal fields
            if (array_key_exists($key, $array_fields["$personalCat"])) {
                $form_array["$personalCat"][$array_fields["$personalCat"][$key]] = $values;
            } else if (array_key_exists($key, $array_fields["$communicationCat"])) {
                $form_array["$communicationCat"][$array_fields["$communicationCat"][$key]] = $values;
            } else if (array_key_exists($key, $array_fields["$categoryCorrespondance"])) {
                $form_array["$categoryCorrespondance"][$array_fields["$categoryCorrespondance"][$key]] = $values;
            } else if (array_key_exists($key, $array_fields["$fedFieldCat"])) {
                $form_array["$fedFieldCat"][$array_fields["$fedFieldCat"][$key]] = $values;
            } else if (array_key_exists($key, $array_fields["$employerCat"])) {
                $form_array["$employerCat"][$array_fields["$employerCat"][$key]] = $values;
            }
        }



        return $form_array;
    }

    /**
     * Function to Check Merging
     * @param array $checkMerging
     * @param array $userArr
     * @return array
     */
    private function checkMergeable($checkMerging, $userArr)
    {
        $mergeableArray = array();
        $mergeableArray['user_application_id'] = $userArr['id'];
        $mergeableArray['skipped'] = 0;
        $mergeableArray['create'] = 1;
        $mergeableArray['merge'] = 0;
        $mergeableArray['fed'] = 0;
        foreach ($checkMerging as $merging => $value) {
            foreach ($value as $userValue) {
                if ($userValue['fed_contact'] == 1) {
                    $mergeableArray['skipped'] = ($userValue['emailMatch'] == 1) ? 1 : 0;
                    $mergeableArray['merge'] = 0;
                    $mergeableArray['create'] = ($userValue['emailMatch'] == 1) ? 0 : 1;
                    $mergeableArray['fed'] = 1;
                } else {
                    $mergeableArray['skipped'] = 0;
                    $mergeableArray['merge'] = 1;
                    $mergeableArray['create'] = 0;
                }
            }
        }

        return $mergeableArray;
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
     * function to get language array.
     *
     * @return type
     */
    private function getLanguageArray()
    {
        $languages = Intl::getLanguageBundle()->getLanguageNames();
        $fieldLanguages = array();
        foreach ($this->clubLanguages as $shortName) {
            $fieldLanguages[$shortName] = $languages[$shortName];
        }
    }

    /**
     * Function to get membership array.
     *
     * @return array
     */
    private function getMembershipArray()
    {
        $objMembershipPdo = new membershipPdo($this->container);
        $membersipFields = $objMembershipPdo->getMemberships($this->clubType, $this->clubId, $this->subFederationId, $this->federationId);
        $this->fedMemberships = array();
        $this->fedMembers = '';
        $clubId = ($this->clubType == 'federation') ? $this->clubId : $this->federationId;
        $clubDefaultLang = $this->get('club')->get('default_lang');
        foreach ($membersipFields as $key => $memberCat) {
            $title = $memberCat['allLanguages'][$clubDefaultLang]['titleLang'] != '' ? $memberCat['allLanguages'][$clubDefaultLang]['titleLang'] : $memberCat['membershipName'];
            if (($memberCat['clubId'] == $clubId)) {
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
     * set terminology terms to contact detail array.
     *
     * @param array $fieldDetails
     *
     * @return array
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
}
