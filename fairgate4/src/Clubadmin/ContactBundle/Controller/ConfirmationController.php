<?php

/**
 * Confirmation Controller
 *
 * This controller was created for handling the changes to be confirmed in Contact management.
 *
 * @package    ClubadminContactBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 *
 */

namespace Clubadmin\ContactBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Common\UtilityBundle\Controller\FgController as ParentController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Common\UtilityBundle\Util\FgUtility;
use Symfony\Component\Intl\Intl;
use Clubadmin\Util\Contactlist;
use Clubadmin\ContactBundle\Util\FedMemApplication;
use Common\UtilityBundle\Util\FgFedMemberships;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Common\UtilityBundle\Repository\Pdo\ContactPdo;
use Common\UtilityBundle\Repository\Pdo\membershipPdo;

/**
 * Confirmation Controller
 *
 * This controller is used for handling changes to be confirmed.
 */
class ConfirmationController extends ParentController {

    /**
     * This action is used for listing changes to be confirmed.
     *
     * @param string $type Whether changes to confirm listing or log listing
     *
     * @Template("ClubadminContactBundle:Confirmation:confirmationchanges.html.twig")
     *
     * @return array Data array.
     */
    public function confirmationChangesAction($type) {
        $changesCount = $this->em->getRepository('CommonUtilityBundle:FgCmChangeToconfirm')->getChangesToConfirmCount($this->clubId);
        $countryFields = $this->container->getParameter('country_fields');
        $countryList = FgUtility::getCountryList();
        $languageAttrIds = array($this->container->getParameter('system_field_corress_lang'));
        $languageList = Intl::getLanguageBundle()->getLanguageNames();
        $tabs = array(0 => 'change_tab',
            1 => 'change_log'
        );
        $activetab = ($type == 'log') ? 'change_log' : 'change_tab';
        $tabsData = FgUtility::getTabsArrayDetails($this->container, $tabs, '', '', $changesCount, $activetab);
        $return = array('actionType' => $type, 'changesCount' => $changesCount, 'countryFields' => $countryFields, 'countryList' => $countryList, 'languageAttrIds' => $languageAttrIds, 'languageList' => $languageList, 'tabs' => $tabsData, 'page' => 'changes');

        return $return;
    }

    /**
     * For listing the changes to be confirmed.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse Changes to be confirmed
     */
    public function listChangesToConfirmAction(Request $request) {
        $totalChanges = $request->get('changesCount');
        $changesToConfirm = $this->em->getRepository('CommonUtilityBundle:FgCmChangeToconfirm')->getChangesToConfirm($this->clubId, $this->container, $this->get('club'), $this->contactId);
        $return['aaData'] = $changesToConfirm;
        $return["iTotalRecords"] = $totalChanges ? $totalChanges : 0;
        $return["iTotalDisplayRecords"] = $totalChanges ? $totalChanges : 0;

        return new JsonResponse($return);
    }

    /**
     * For listing the log of confirmed changes.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse Confirmation log
     */
    public function listConfirmationLogAction(Request $request) {
        $order = $request->get('order');
        $columns = $request->get('columns');
        $orderAs = $order[0]['dir'];
        $this->session->set('confirmationlog_orderAs', $orderAs);
        $orderBy = $columns[$order[0]['column']]['name'];
        $this->session->set('confirmationlog_orderBy', $orderBy);
        $changesLog = $this->em->getRepository('CommonUtilityBundle:FgCmChangeLog')->getConfirmationLog($this->clubId, $this->clubDefaultLang);
        $totalChanges = count($changesLog);
        $return['aaData'] = $changesLog;
        $return["iTotalRecords"] = $totalChanges ? $totalChanges : 0;
        $return["iTotalDisplayRecords"] = $totalChanges ? $totalChanges : 0;

        return new JsonResponse($return);
    }

    /**
     * This action is used to confirm or discard changes.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $action Whether to confirm or discard
     *
     * @Template("ClubadminContactBundle:Confirmation:confirmordiscardchange.html.twig")
     *
     * @return array Data array.
     */
    public function confirmOrDiscardChangesAction(Request $request, $action) {
        $actionType = $request->get('actionType');
        $selActionType = $request->get('selActionType') ? $request->get('selActionType') : '';
        $return = array('actionType' => $actionType, 'selActionType' => $selActionType, 'action' => $action);

        return $return;
    }

    /**
     * Function to confirm or discard the changes.
     *
     * @param Request $request Request object
     *
     * @return JsonResponse Confirmed/Discarded status
     */
    public function doConfirmOrDiscardAction(Request $request) {
        $action = $request->get('action');
        $selectedIds = json_decode($request->get('selectedId', '0'));
        $clubIdArray = $this->getClubArray();
        $confirmSuccess = $this->em->getRepository('CommonUtilityBundle:FgCmChangeToconfirm')->confirmOrDiscardChanges($action, $this->clubId, $selectedIds, $this->container, $this->clubDefaultSystemLang, $this->get('club'), $this->contactId, $clubIdArray, $this->get('fairgate_terminology_service'));
        $flashMsg = ($action == 'confirm') ? '%selcount%_OUT_OF_%totalcount%_CHANGES_CONFIRMED_SUCCESSFULLY' : '%selcount%_OUT_OF_%totalcount%_CHANGES_DISCARDED_SUCCESSFULLY';

        return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->container->get('translator')->trans($flashMsg, array('%selcount%' => $confirmSuccess['successCount'], '%totalcount%' => $confirmSuccess['totalContacts']))));
    }

    /**
     * get club details array
     *
     * @return type array
     */
    private function getClubArray() {
        $container = $this->container->getParameterBag();
        $clubIdArray = array('clubId' => $this->clubId,
            'federationId' => $this->federationId,
            'subFederationId' => $this->subFederationId,
            'clubType' => $this->clubType,
            'correspondanceCategory' => $container->get('system_category_address'),
            'invoiceCategory' => $container->get('system_category_invoice'));
        $clubIdArray['address'] = $this->get('translator')->trans('CONTACT_FIELD_ADDRESS');
        $clubIdArray['sysLang'] = $this->clubDefaultLang;
        $clubIdArray['defSysLang'] = $this->clubDefaultSystemLang;
        $clubIdArray['clubLanguages'] = $this->clubLanguages;

        return $clubIdArray;
    }

    /**
     * This action is used for listing mutations to be confirmed.
     *
     * @Template("ClubadminContactBundle:Confirmation:confirmations.html.twig")
     *
     * @return array Data array.
     */
    public function mutationsListAction() {
        $confirmationsCount = $this->em->getRepository('CommonUtilityBundle:FgCmChangeToconfirm')->getConfirmationsCount($this->clubId, 'mutation');
        $logTabs = array(1 => 'list', 2 => 'log');
        $tabs = array(0 => 'mutations_tab',
            1 => 'mutations_log'
        );
        $activetab = 'mutations_tab';
        $tabsData = FgUtility::getTabsArrayDetails($this->container, $tabs, '', '', $confirmationsCount, $activetab, "confirmations");
        $return = array('clubType' => $this->clubType, 'title' => $this->get('translator')->trans('CONFIRM_MUTATIONS_TITLE'), 'pageTitle' => $this->get('translator')->trans('CONFIRMATION_MUTATIONS_PAGE_TITLE'), 'page' => 'mutations', 'logTabs' => $logTabs, 'activeTab' => 1, 'confirmationsCount' => $confirmationsCount, 'page' => 'mutations', 'tabs' => $tabsData);

        return $return;
    }

    /**
     * This action is used for listing creations to be confirmed.
     *
     * @Template("ClubadminContactBundle:Confirmation:confirmations.html.twig")
     *
     * @return array Data array.
     */
    public function creationsListAction() {
        $confirmationsCount = $this->em->getRepository('CommonUtilityBundle:FgCmChangeToconfirm')->getConfirmationsCount($this->clubId, 'creation');
        $logTabs = array(1 => 'list', 2 => 'log');
        $tabs = array(0 => 'creations_tab',
            1 => 'creations_log'
        );
        $activetab = 'creations_tab';
        $tabsData = FgUtility::getTabsArrayDetails($this->container, $tabs, '', '', $confirmationsCount, $activetab, "confirmations");

        $return = array('clubType' => $this->clubType, 'title' => $this->get('translator')->trans('CONFIRM_CREATIONS_TITLE'), 'pageTitle' => $this->get('translator')->trans('CONFIRMATION_CREATIONS_PAGE_TITLE'), 'page' => 'creations', 'logTabs' => $logTabs, 'activeTab' => 1, 'confirmationsCount' => $confirmationsCount, 'tabs' => $tabsData, 'page' => 'creations');
        return $return;
    }

    /**
     * This action is used for fetching mutations list and log data
     *
     * @param string $type List or log
     *
     * @return JsonResponse
     */
    public function getMutationsAction($type) {
        if ($type == 'list') {
            $mutationsToConfirm = $this->em->getRepository('CommonUtilityBundle:FgCmChangeToconfirm')->getAssignmentsToConfirm($this->clubId, $this->container, 'mutation');
            $return['aaData'] = $mutationsToConfirm;
        } else {
            $mutationsLog = $this->em->getRepository('CommonUtilityBundle:FgCmChangeToconfirm')->getConfirmationsLog($this->clubId, $this->container, 'mutation');
            $return['aaData'] = $mutationsLog;
        }

        return new JsonResponse($return);
    }

    /**
     * This action is used for fetching creations list and log data
     *
     * @param string $type List or log
     *
     * @return JsonResponse
     */
    public function getCreationsAction($type) {
        if ($type == 'list') {
            $creationsToConfirm = $this->em->getRepository('CommonUtilityBundle:FgCmChangeToconfirm')->getAssignmentsToConfirm($this->clubId, $this->container, 'creation');
            $return['aaData'] = $creationsToConfirm;
        } else {
            $creationsLog = $this->em->getRepository('CommonUtilityBundle:FgCmChangeToconfirm')->getConfirmationsLog($this->clubId, $this->container, 'creation');
            $return['aaData'] = $creationsLog;
        }

        return new JsonResponse($return);
    }

    /**
     * This action is used to confirm or discard confirmations creations and mutations.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $action Whether to confirm or discard
     *
     * @Template("ClubadminContactBundle:Confirmation:confirmOrDiscardConfirmations.html.twig")
     *
     * $page = {creations/mutations}
     * $action = {confirm/discard}
     * $selActionType = {all/selected}
     *
     * @return array Data array.
     */
    public function confirmOrDiscardConfirmationPopupAction(Request $request, $page, $action) {
        $selActionType = $request->get('selActionType') ? $request->get('selActionType') : 'all';
        $clubId = ($this->clubType == 'federation') ? $this->clubId : $this->federationId;
        $return['page'] = $page;
        $return['selActionType'] = $selActionType;
        $return['action'] = $action;
        $return['clubType'] = $this->clubType;

        if ($page == 'creations') {
            if ($action == 'confirm') {
//                $membersipFields = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')
//                        ->getMemberships($this->clubType, $this->clubId, $this->subFederationId, $this->federationId);
                $clubId = ($this->clubType == 'federation') ? $this->clubId : $this->federationId;
                $transSub = ($this->clubType == 'federation') ? '_FED' : '';
                $return['header'] = ($selActionType == 'all') ? 'CONFIRMATION_CONFIRM_CREATION_HEADER_ALL' . $transSub : 'CONFIRMATION_CONFIRM_CREATION_HEADER_SELECTED' . $transSub;
                $return['label'] = ($selActionType == 'all') ? 'CONFIRMATION_CONFIRM_CREATION_LABEL_ALL' . $transSub : 'CONFIRMATION_CONFIRM_CREATION_LABEL_SELECTED' . $transSub;

                $return['clubMembershipAvailable'] = '';
                if ($this->clubType == 'federation' || $this->clubType == 'sub_federation') {
                    $return['clubMembershipAvailable'] = '';
                } elseif ($this->clubType == 'standard_club') {
                    $return['clubMembershipAvailable'] = 1;
                } else {
                    $return['clubMembershipAvailable'] = $this->get('club')->get('clubMembershipAvailable');
                }
                $return['fedMembershipMandatory'] = $this->get('club')->get('fedMembershipMandatory');
                if ($return['fedMembershipMandatory']) {
                    $memberships['fed'][0] = $this->get('translator')->trans('SELECT_DROPDOWN');
                } else {
                    $memberships['fed'][0] = $this->get('translator')->trans('NO_FED_MEMBERSHIP');
                }
                $memberships['club'][0] = $this->get('translator')->trans('NO_MEMBERSHIP');
                $objMembershipPdo = new membershipPdo($this->container);
                $membersipFields = $objMembershipPdo->getMemberships($this->clubType, $this->clubId, $this->subFederationId, $this->federationId);
                $clubDefaultLang = $this->get('club')->get('default_lang');
                foreach ($membersipFields as $key => $memberCat) {
                    $title = $memberCat['allLanguages'][$clubDefaultLang]['titleLang'] != '' ? $memberCat['allLanguages'][$clubDefaultLang]['titleLang'] : $memberCat['membershipName'];
                    if (($memberCat['clubId'] == $clubId)) {
                        $memberships['fed'][$key] = $title;
                    } else {
                        $memberships['club'][$key] = $title;
                    }
                }

                $return['memberships'] = $memberships;
            } else {
                $return['header'] = ($selActionType == 'all') ? 'DISCARD_ALL_CREATIONS' : 'DISCARD_SELECTED_CREATIONS';
                $return['label'] = ($selActionType == 'all') ? 'DISCARD_ALL_CREATIONS_TEXT' : 'DISCARD_SELECTED_CREATIONS_TEXT';
            }
        } else if ($page == 'mutations') {
            if ($action == 'confirm') {
                $return['header'] = ($selActionType == 'all') ? 'CONFIRM_ALL_MUTATIONS' : 'CONFIRM_SELECTED_MUTATIONS';
                $return['label'] = ($selActionType == 'all') ? 'CONFIRM_ALL_MUTATIONS_TEXT' : 'CONFIRM_SELECTED_MUTATIONS_TEXT';
            } else {
                $return['header'] = ($selActionType == 'all') ? 'DISCARD_ALL_MUTATIONS' : 'DISCARD_SELECTED_MUTATIONS';
                $return['label'] = ($selActionType == 'all') ? 'DISCARD_ALL_MUTATIONS_TEXT' : 'DISCARD_SELECTED_MUTATIONS_TEXT';
            }
        }

        return $return;
    }

    /**
     * This action is used to confirm or discard confirmations creations and mutations.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * $page = {creations/mutations}
     * $action = {confirm/discard}
     * $selActionType = {all/selected}
     *
     * @return Json Response.
     */
    public function updateConfirmationsAction(Request $request) {
        $action = $request->get('action');
        $page = $request->get('page');
        $selectedIds = json_decode($request->get('selectedId', '0'));
        $totCount = count($selectedIds);
        $successCount = count($selectedIds);
        $selectedMembership['fed'] = $request->get('fedMembership', '');
        $selectedMembership['club'] = $request->get('clubMembership', '');
        $hasFedmembership = ($selectedMembership['fed'] != 0) ? true : false;
        $clubIdArray = $this->getClubArray();
        $pdo = new ContactPdo($this->container);
        $clubHeirarchy = $this->get('club')->get('clubHeirarchy');
        $primaryEmail = $this->container->getParameter('system_field_primaryemail');
        if ($action == 'discard') {
            $this->em->getRepository('CommonUtilityBundle:FgCmChangeToconfirm')->discardSelectedConfirmations($this->container, $this->clubId, $this->contactId, $selectedIds, $page);
            $successCount = count($selectedIds);
        } else {
            if ($page == 'creations') {
                //get contact details
                $contactDetails = $this->em->getRepository('CommonUtilityBundle:FgCmChangeToconfirm')->getContactInConfirmation($selectedIds);
                //get memberships
                $memberships = array();
                $clubId = ($this->clubType == 'federation') ? $this->clubId : $this->federationId;
                $objMembershipPdo = new membershipPdo($this->container);
                $membershipFields = $objMembershipPdo->getMemberships($this->clubType, $this->clubId, $this->subFederationId, $this->federationId);
                foreach ($membershipFields as $key => $memberCat) {
                    if (($memberCat['clubId'] == $clubId)) {
                        $memberships['fed'][] = $key;
                    } else {
                        $memberships['club'][] = $key;
                    }
                }
                $contactIdArr = array_keys($contactDetails);
                $emails = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getEmailField(implode(',', $contactIdArr), $primaryEmail);
                $contactDetailsCpy = $contactDetails;
                // Checking the count of reactivate contact and the contact has the federation membership
                if ($hasFedmembership) {
                    foreach ($contactDetails as $contactId => $details) {
                        $contactObj = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->find($contactId);
                        $contactData = $pdo->getContactDetailsForMembershipDetails('draft', $contactId);
                        $contactData = $contactData[0];
                        $contactData['fedMembershipId'] = $selectedMembership['fed'];
                        $fieldType = ($contactData['Iscompany'] == 1) ? 'Company' : 'Single person';

                        // Email validations for the contact
                        if ($emails[$contactObj->getFedContact()->getId()] != '') {
                            $result = $this->em->getRepository('CommonUtilityBundle:FgCmAttribute')->searchEmailExistAndIsMergable($this->container, $contactId, $emails[$contactObj->getFedContact()->getId()], $hasFedmembership, 0, 'contact', true, $fieldType);
                        } else {
                            $result = array();
                        }
                        //if email validation fails
                        if (count($result) > 0) {
                            if (count($selectedIds) == 1) {
                                $failedEmail['status'] = 'EMAILFAILED';
                                $failedEmail['noparentload'] = true;
                                $failedEmail['page'] = $page;

                                return new JsonResponse($failedEmail);
                            }
                            //unset values from selected contact to confirm
                            if (($key = array_search($contactDetails[$contactId][2], $selectedIds)) !== false) {
                                unset($selectedIds[$key]);
                            }
                            unset($contactDetails[$contactId]);
                            $successCount--;
                        } else {
                            $contactDataInMergableFormat = $this->convertDataToMergableFormat($contactData);
                            $mergeableReturn = $pdo->getMergeableContacts($contactDataInMergableFormat, $fieldType, $contactId);
                            // Checking the contact is mergable if single contact selected for confirmation
                            if (count($selectedIds) == 1) {
                                if (count($mergeableReturn['duplicates']) > 0 || count($mergeableReturn['mergeEmail']) > 0) {
                                    $mergeableReturn['currentContactData'] = $contactData;
                                    $mergeCount = 'SINGLE';
                                    $meargable = true;
                                } else {
                                    $contactDetails[$contactId] = $details;
                                }
                            } else {
                                // Checking the contact is mergable if multiple contact selected for confirmation
                                if (count($mergeableReturn['duplicates']) > 0 || count($mergeableReturn['mergeEmail']) > 0) {
                                    $meargable[$contactId]['currentContactData'] = $contactData;
                                    $meargable[$contactId]['meargable'] = $mergeableReturn;
                                    $mergeCount = 'MULTIPLE';
                                    unset($contactDetails[$contactId]);
                                } else {
                                    $contactDetails[$contactId] = $details;
                                }
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
                    $mergeableReturn['nonMergeableContacts'] = $contactDetails;
                    $mergeableReturn['contactDetails'] = $contactDetailsCpy;
                    $mergeableReturn['mergeCount'] = $mergeCount;
                    $mergeableReturn['totCount'] = $totCount;

                    return new JsonResponse($mergeableReturn);
                }
                if (count($contactDetails)) {
                    $this->em->getRepository('CommonUtilityBundle:FgCmContact')
                            ->addMemberships($this->container, $contactDetails, $selectedMembership, $memberships, $this->clubId, $this->clubType, $clubHeirarchy, $this->federationId, $noFedMembership);
                }
            }
            if (count($selectedIds)) {
                $this->em->getRepository('CommonUtilityBundle:FgCmChangeToconfirm')->confirmSelectedConfirmations($this->container, $this->get('club'), $this->clubId, $this->contactId, $selectedIds, $page);
            }
        }

        $flashMsg = ($action == 'confirm') ? '%selcount%_OUT_OF_%totalcount%_' . strtoupper($page) . '_CONFIRMED_SUCCESSFULLY' : '%selcount%_OUT_OF_%totalcount%_' . strtoupper($page) . '_DISCARDED_SUCCESSFULLY';

        return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->container->get('translator')->trans($flashMsg, array('%selcount%' => $successCount, '%totalcount%' => $totCount)), 'noparentload' => false, 'count' => $successCount));
    }

    /**
     * Function to convert array format
     *
     * @param array $contactData Current contact data
     *
     * @return Array
     */
    private function convertDataToMergableFormat($contactData) {
        $primaryEmail = $this->container->getParameter('system_field_primaryemail');
        $catCommun = $this->container->getParameter('system_category_communication');
        $catPerson = $this->container->getParameter('system_category_personal');
        $firstname = $this->container->getParameter('system_field_firstname');
        $lastname = $this->container->getParameter('system_field_lastname');
        $dob = $this->container->getParameter('system_field_dob');
        $land = $this->container->getParameter('system_field_corres_ort');
        $corrCat = $this->container->getParameter('system_category_address');

        $newContactData = array();
        $newContactData[$catCommun][$primaryEmail] = $contactData[$primaryEmail];
        $newContactData[$catPerson][$firstname] = $contactData[$firstname];
        $newContactData[$catPerson][$lastname] = $contactData[$lastname];
        $newContactData[$catPerson][$dob] = $contactData[$dob];
        $newContactData[$corrCat][$land] = $contactData[$land];

        return $newContactData;
    }

    /**
     * Function to confirm mergable contacts
     *
     * @return JsonResponse
     */
    public function saveConfirmationContactAction(Request $request) {
        $contactData = $request->get('contactData', '');
        $mergeTo = $request->get('mergeTo', '');
        $typeMer = $request->get('typeMer', '');
        $merging = $request->get('merging', '');
        $creationArray = $request->get('creationArray', '');
        $mergeType = $request->get('mergeType', '');
        $clubHeirarchy = $this->get('club')->get('clubHeirarchy');
        //get memberships
        $memberships = array();
        $clubId = ($this->clubType == 'federation') ? $this->clubId : $this->federationId;
        $objMembershipPdo = new membershipPdo($this->container);
        $membershipFields = $objMembershipPdo->getMemberships($this->clubType, $this->clubId, $this->subFederationId, $this->federationId);
        foreach ($membershipFields as $key => $memberCat) {
            if (($memberCat['clubId'] == $clubId)) {
                $memberships['fed'][] = $key;
            } else {
                $memberships['club'][] = $key;
            }
        }

        if ($mergeType != 'multiple') {
            if ($merging == 'save') {
                if ($mergeTo != 'fed_mem') {
                    $contactUpdateStr = "UPDATE fg_cm_contact SET merge_to_contact_id=" . $mergeTo . ",allow_merging=1, is_deleted=0 WHERE id ='" . $contactData['id'] . "'";
                    $this->conn->executeQuery($contactUpdateStr);
                }

                $this->em->getRepository('CommonUtilityBundle:FgCmContact')->addMemberships($this->container, $creationArray['contactDetails'], $creationArray['selectedMembership'], $memberships, $this->clubId, $this->clubType, $clubHeirarchy, $this->federationId);
                $this->em->getRepository('CommonUtilityBundle:FgCmChangeToconfirm')->confirmSelectedConfirmations($this->container, $this->get('club'), $this->clubId, $this->contactId, $creationArray['selectedIds'], 'creations');

                $flashMsg = '%selcount%_OUT_OF_%totalcount%_CREATIONS_CONFIRMED_SUCCESSFULLY';
                $status = array('status' => 'SUCCESS', 'totalCount' => count($contactData['id']), 'noparentload' => false, 'flash' => $this->get('translator')->trans($flashMsg, array('%totalcount%' => count($contactData['id']), '%selcount%' => count($contactData['id']))));
            }
        } else {
            $flag = false;
            $selcount = 0;
            // set merge flag in fg_cm_contact
            if ($merging == 'save') {
                // add membership and confirm selected ids for mergable contacts
                foreach ($mergeTo as $merFrm => $merTo) {
                    if ($mergeTo['applymer'] != 'fed_mem') {
                        $contactUpdateStr = "UPDATE fg_cm_contact SET merge_to_contact_id=" . $merTo['applymer'] . ",allow_merging=1, is_deleted=0 WHERE id ='" . $merFrm . "'";
                        $this->conn->executeQuery($contactUpdateStr);
                    }
                    $this->em->getRepository('CommonUtilityBundle:FgCmContact')->addMemberships($this->container, array($merFrm => $creationArray['contactDetails'][$merFrm]), $creationArray['selectedMembership'], $memberships, $this->clubId, $this->clubType, $clubHeirarchy, $this->federationId);
                    $this->em->getRepository('CommonUtilityBundle:FgCmChangeToconfirm')->confirmSelectedConfirmations($this->container, $this->get('club'), $this->clubId, $this->contactId, array($creationArray['contactDetails'][$merFrm]['2']), 'creations');
                    $selcount++;
                    $flag = true;
                }
                // add membership and confirm selected ids for non-mergable contacts
                if (count($creationArray['nonMergeableContacts']) > 0) {
                    foreach ($creationArray['nonMergeableContacts'] as $contKey => $nonMerContacts) {
                        $this->em->getRepository('CommonUtilityBundle:FgCmContact')->addMemberships($this->container, array($contKey => $nonMerContacts), $creationArray['selectedMembership'], $memberships, $this->clubId, $this->clubType, $clubHeirarchy, $this->federationId);
                        $this->em->getRepository('CommonUtilityBundle:FgCmChangeToconfirm')->confirmSelectedConfirmations($this->container, $this->get('club'), $this->clubId, $this->contactId, array($creationArray['nonMergeableContacts'][$contKey]['2']), 'creations');
                        $selcount++;
                    }
                    $flag = true;
                }
            }
            if (!$flag) {
                $flashMsg = 'CREATIONS_NOT_SUCCESS';
                $status = array('status' => 'FAILURE', 'totalCount' => $selcount, 'noparentload' => false, 'flash' => $this->get('translator')->trans($flashMsg));
            } else {
                $flashMsg = '%selcount%_OUT_OF_%totalcount%_CREATIONS_CONFIRMED_SUCCESSFULLY';
                $status = array('status' => 'SUCCESS', 'totalCount' => $selcount, 'noparentload' => false, 'flash' => $this->get('translator')->trans($flashMsg, array('%totalcount%' => $creationArray['totCount'], '%selcount%' => $selcount)));
            }
        }
        return new JsonResponse($status);
    }

    /**
     * Contact detail template
     *
     * @return HTML
     */
    public function contactDetailAction($contact) {
        $countryFields = $this->container->getParameter('country_fields');
        $countryList = FgUtility::getCountryList();
        $languageAttrIds = array($this->container->getParameter('system_field_corress_lang'));
        $languageList = Intl::getLanguageBundle()->getLanguageNames();
        $invoiceFieldIds = array(
            $this->container->getParameter('system_field_invoice_strasse'),
            $this->container->getParameter('system_field_invoice_ort'),
            $this->container->getParameter('system_field_invoice_kanton'),
            $this->container->getParameter('system_field_invoice_plz'),
            $this->container->getParameter('system_field_invoice_land'),
            $this->container->getParameter('system_field_invoice_postfach')
        );

        $correspondenceFieldIds = array(
            $this->container->getParameter('system_field_corres_strasse'),
            $this->container->getParameter('system_field_corres_ort'),
            $this->container->getParameter('system_field_corres_kanton'),
            $this->container->getParameter('system_field_corres_plz'),
            $this->container->getParameter('system_field_corres_land'),
            $this->container->getParameter('system_field_corres_postfach')
        );

        $club = $this->get('club');
        $contactlistClass = new Contactlist($this->container, $contact, $club, 'contactForConfirmation');
        $contactlistClass->setColumns();
        $contactlistClass->setFrom();
        $contactlistClass->setCondition();
        $contactlistClass->addCondition('fg_cm_contact.id = ' . $contact);
        $query = $contactlistClass->getResult();
        $contactData = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($query);
        $contactData = $contactData[0];
        $allContactFields = $club->get('allContactFields');
        $detailsArr = array();
        $salutationField = $this->container->getParameter('system_category_personal');
        $genderField = $this->container->getParameter('system_field_gender');

        foreach ($contactData as $key => $val) {
            if ($val != '' && isset($allContactFields[$key]) && $val != '0000-00-00' && !(in_array($key, $invoiceFieldIds))) {
                if ($allContactFields[$key]['type'] == 'date') {
                    $val = $this->get('club')->formatDate($val, 'date', 'Y-m-d');
                }

                $title = $allContactFields[$key]['title'];
                //traslation for salutation and gender
                if ($key == $salutationField) {
                    $val = ($val == 'Informal') ? $this->container->get('translator')->trans('CN_INFORMAL') : (($val == 'Formal') ? $this->container->get('translator')->trans('CN_FORMAL') : $val);
                }
                if ($key == $genderField) {
                    $val = ($val == 'Male') ? $this->container->get('translator')->trans('CN_MALE') : (($val == 'Female') ? $this->container->get('translator')->trans('CN_FEMALE') : $val);
                }
                $detailsArr[] = array('id' => $key, 'title' => $title, 'value' => $val);
            }
        }
        $detailsArrOrdered = array();
        $fieldsOrder = array_keys($allContactFields);
        foreach ($fieldsOrder as $orderkey => $fieldKeyVal) {
            foreach ($detailsArr as $key => $value) {
                if ($fieldKeyVal == $value['id']) {
                    $detailsArrOrdered[] = $value;
                }
            }
        }
        $firstName = $this->container->getParameter('system_field_firstname');
        $lastName = $this->container->getParameter('system_field_lastname');
        $firma = $this->container->getParameter('system_field_companyname');
        $contactName = trim(($contactData['is_company'] == 1) ? $contactData[$firma] : ($contactData[$lastName] . ' ' . $contactData[$firstName]));

        return $this->container->get('templating')->renderResponse('ClubadminContactBundle:Confirmation:contactDetail.html.twig', array('contactName' => $contactName, 'contactData' => $detailsArrOrdered, 'countryFields' => $countryFields, 'countryList' => $countryList, 'languageAttrIds' => $languageAttrIds, 'languageList' => $languageList));
    }

    /**
     * For list federation membership applications
     *
     * @Template("ClubadminContactBundle:Confirmation:application.html.twig")
     *
     * @return array Application details to list
     */
    public function applicationConfirmationAction(Request $request) {
       
        $type = ($request->get('type')) ? $request->get('type') : 'fedMembershipList';
        $withOutApplication = $this->get('club')->get('assignFedmembershipWithApplication') ? false : true;
        if ($this->clubType == 'sub_federation' || $this->clubType == 'standard_club' || $withOutApplication) {
            throw new NotFoundHttpException();
        }
        $return = ($type == 'mergeList') ? $this->getMergeApplicationListDetails() : $this->getFedMembershipApplicationListDetails();
        $return['clubTitles'] = $this->em->getRepository('CommonUtilityBundle:FgClub')->getClubsTitlesWithinAFederation($this->federationId, $this->get('club')->get('default_lang'));

        return $return;
    }

    /**
     * This function is used to list the fed membership application details
     *
     * @return array Fed membership application details
     */
    private function getFedMembershipApplicationListDetails() {
        $defaultLang = $this->get('club')->get('default_lang');
        $applicationCount = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getConfirmApplicationCount($this->federationId, $this->clubType, $this->clubId, $defaultLang, true);
        $logTabs = array(1 => 'list', 2 => 'log');
        $tabs = array(0 => 'applicationqueue',
            1 => 'applicationlog'
        );
        $activetab = 'applicationqueue';
        $tabsData = FgUtility::getTabsArrayDetails($this->container, $tabs, '', '', $applicationCount, $activetab, "fedapplication");
        $return = array('title' => $this->get('translator')->trans('APPLICATION_CONFIRMATION_TITLE'), 'pageTitle' => $this->get('translator')->trans('APPLICATION_CONFIRMATION_TITLE'), 'logTabs' => $logTabs, 'applicationCount' => $applicationCount, 'tabs' => $tabsData, 'page' => 'fedapplication', 'activeTab' => 1, 'clubType' => $this->clubType);

        return $return;
    }

    /**
     * This function is used to list the merging application details
     *
     * @return array Merging application details
     */
    private function getMergeApplicationListDetails() {
        $applicationCount = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getMergeApplicationsCount($this->federationId, $this->clubType, $this->clubId);
        $logTabs = array(1 => 'list', 2 => 'log');
        $tabs = array(0 => 'mergeapplicationqueue',
            1 => 'mergeapplicationlog'
        );
        $activetab = 'mergeapplicationqueue';
        $tabsData = FgUtility::getTabsArrayDetails($this->container, $tabs, '', '', $applicationCount, $activetab, "fedapplication");
        $return = array('title' => $this->get('translator')->trans('MERGE_APPLICATION_CONFIRMATION_TITLE'), 'pageTitle' => $this->get('translator')->trans('MERGE_APPLICATION_CONFIRMATION_TITLE'), 'logTabs' => $logTabs, 'applicationCount' => $applicationCount, 'tabs' => $tabsData, 'page' => 'mergeapplication', 'activeTab' => 1, 'clubType' => $this->clubType);

        return $return;
    }

    /**
     * This action is used for fetching creations list and log data
     *
     * @param string $type List or log
     *
     * @return JsonResponse
     */
    public function getApplicationsAction($type, $page = 'fedapplication') {
        $applicationDetails = array();
        $defaultLang = $this->get('club')->get('default_lang');
        if ($type == 'list') {
            if ($page == 'mergeapplication') {

                $applicationDetails = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getMergeApplications($this->federationId, $this->clubType, $this->clubId, $defaultLang);
            } else {
                $defaultLang = $this->get('club')->get('default_lang');
                $applicationDetails = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getConfirmApplicationCount($this->federationId, $this->clubType, $this->clubId, $defaultLang);
            }
        } else {
            $isMerging = ($page == 'mergeapplication') ? 1 : 0;

            $applicationDetails = $this->em->getRepository('CommonUtilityBundle:FgCmFedmembershipConfirmationLog')->getApplicationConfirmationLog($this->federationId, $this->clubType, $this->clubId, $isMerging, $defaultLang);
        }
        $return['aaData'] = $applicationDetails;

        return new JsonResponse($return);
    }

    /**
     * This action is used to confirm or discard Application.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $action Whether to confirm or discard
     *
     * @Template("ClubadminContactBundle:Confirmation:confirmOrDiscardApplications.html.twig")
     *
     * $page = {creations/mutations}
     * $action = {confirm/discard}
     * $selActionType = {all/selected}
     *
     * @return array Data array.
     */
    public function confirmOrDiscardApplicationPopupAction(Request $request, $action) {
        $selActionType = $request->get('selActionType') ? $request->get('selActionType') : 'all';
        $clubId = ($this->clubType == 'federation') ? $this->clubId : $this->federationId;
        $return['page'] = $request->get('page') ? $request->get('page') : 'fedapplication';
        $return['selActionType'] = $selActionType;
        $return['action'] = $action;

        return $return;
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
    public function updateApplicationAction(Request $request) {
        $page = $request->get('page');
        $action = $request->get('action');
        $selectedIds = json_decode($request->get('selectedId', '0'));
        $fedApplication = new FedMemApplication($this->container);
        $successCount = 0;
        if ($action == 'discard') {
            foreach ($selectedIds as $confirmId) {
                $result = $fedApplication->discardFedMembership($confirmId);
                $successCount++;
            }
        } else {
            foreach ($selectedIds as $confirmId) {
                $result = $fedApplication->confirmFedMembership($confirmId);
                if ($result) {
                    $successCount++;
                }
            }
        }
        if ($action == 'confirm') {
            $flashMsg = (count($selectedIds) == $result) ? 'APPLICATION_CONFIRMED_SUCCESSFULLY' : 'APPLICATION_CONFIRMED_SUCCESSFULLY';
            $flash = $this->container->get('translator')->trans($flashMsg, array('%totalcount%' => count($selectedIds), '%selcount%' => $successCount));
        } else {
            $flashMsg = 'APPLICATION_DISCARDED_SUCCESSFULLY';
            $flash = $this->container->get('translator')->trans($flashMsg);
        }

        return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $flash, 'noparentload' => false, 'count' => count($selectedIds), 'page' => $page));
    }

    /**
     * function to get the contact name from its id
     *
     * @param int $contactId the contact id
     * @param int $type      Type of contact
     *
     * @return array
     */
    private function contactDetails($contactId, $type = 'contact') {
        $club = $this->get('club');
        $contactlistClass = new Contactlist($this->container, '', $club, $type);
        $firstName = $this->container->getParameter('system_field_firstname');
        $dob = $this->container->getParameter('system_field_dob');
        $contactlistClass->setColumns(array($dob, 'contactName', 'ms.`2` as firstName', 'ms.`3` as primaryEmail', 'ms.`23` as lastName', 'ms.`72` as gender', 'ms.`9` as companyName', 'ms.`1` as salutation', 'ms.`70` as title', 'ms.`47` as corresStrasse', 'ms.`71785` as corresPostfach', 'ms.`79` as corresPlz', 'ms.`77` as corresOrt', 'ms.`106` as corresLand'));
        $contactlistClass->setFrom('*');
        $contactlistClass->confirmedFlag = false;
        $contactlistClass->setCondition();
        $sWhere = " fg_cm_contact.id=$contactId";
        $contactlistClass->addCondition($sWhere);
        $listquery = $contactlistClass->getResult();
        $fieldsArray = $this->conn->fetchAll($listquery);

        return $fieldsArray[0];
    }

    /**
     * add Existing Federation Member Pop up
     * @return type
     */
    public function addExistingFedMemberAction() {
        $club = $this->get('club');
        $contactlistClass = new Contactlist($this->container, '', $club, 'contact');
        $contactlistClass->setColumns(array('ms.`3` as primaryEmail', 'fg_cm_contact.fed_contact_id as fedContact'));
        $contactlistClass->setFrom('*');
        //$contactlistClass->confirmedFlag=true;
        $contactlistClass->setCondition();
        $listquery = $contactlistClass->getResult();
        $fieldsArray = $this->conn->fetchAll($listquery);

        $existingEmails = array_column($fieldsArray, 'primaryEmail');
        $fedContacts = array_column($fieldsArray, 'fedContact');
        $applicationPending = $this->em->getRepository('CommonUtilityBundle:FgCmClubAssignmentConfirmationLog')->getPendingConfirmLog($this->clubId);
        $applicationPending1 = array();
        foreach ($applicationPending as $key => $value) {
            $applicationPending1[] = $value['id'];
        }
        $c4checknoapp = (($this->clubType == 'federation_club' || $this->clubType == 'sub_federation_club') && ($club->get('addExistingFedMemberClub') == 1)) ? 1 : 0;

        return $this->render('ClubadminContactBundle:Confirmation:addExistingFedMemberPopUp.html.twig', array('c4checknoapp' => $c4checknoapp, 'existingEmails' => $existingEmails, 'applicationPending' => $applicationPending1, 'fedContacts' => $fedContacts));
    }

    /**
     * add Existing Federation Member Pop up Autocomplete
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function addExistingFedMemberAutocompleteAction(Request $request) {
        $searchTerm = FgUtility::getSecuredData($request->get('term'), $this->conn);
        $contacts = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->addExistingFedMember($this->container, $searchTerm, $this->federationId, $this->clubId);

        return new JsonResponse($contacts);
    }

    /**
     * save Existing Federation Member
     */
    public function saveAddExistingFedMemberAction(Request $request) {
        $fedContactId = FgUtility::getSecuredData($request->get('contactId'), $this->conn);
        $fgFedMembershipObj = new FgFedMemberships($this->container);
        $result = $fgFedMembershipObj->addExistingFedMember($fedContactId);
        $membership = $request->get('membership');
        if ($result)
            $flashMsg = 'ADD_EXISTING_FED_MEMBER_SUCCESS';
        else
            $flashMsg = 'ADD_EXISTING_FED_MEMBER_FAILED';

        return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->container->get('translator')->trans($flashMsg), 'noparentload' => false, 'membership' => $membership));
    }

    /**
     * To list federation membership
     *
     * @Template("ClubadminContactBundle:Confirmation:confirmApplicationClubAssignments.html.twig")
     *
     * @return type
     */
    public function confirmApplicationClubAssignmentsAction() {
       
        $applicationCount = $this->em->getRepository('CommonUtilityBundle:FgCmClubAssignmentConfirmationLog')->getClubAssignmentConfirmationLog($this->clubType, $this->clubId, 'list', true, $this->clubDefaultLang);
        $logTabs = array(1 => 'list', 2 => 'log');
        $tabs = array(0 => 'clubassignmentapplicationqueue',
            1 => 'clubassignmentapplicationlog'
        );
        $activetab = 'clubassignmentapplicationqueue';
        $tabsData = FgUtility::getTabsArrayDetails($this->container, $tabs, '', '', $applicationCount, $activetab, "fedapplication");
        if ($this->clubType == 'federation')
            $title = $this->get('translator')->trans('CLUB_ASSIGNMENT_APPLICATIONS');
        else
            $title = $this->get('translator')->trans('CLUB_ASSIGNMENT_APPLICATIONS');
        $clubTitles = $this->em->getRepository('CommonUtilityBundle:FgClub')->getClubsTitlesWithinAFederation($this->federationId, $this->get('club')->get('default_lang'));
        $return = array('title' => $title, 'pageTitle' => $this->get('translator')->trans('APPLICATION_CONFIRMATION_TITLE'), 'logTabs' => $logTabs, 'applicationCount' => $applicationCount, 'tabs' => $tabsData, 'page' => 'fedapplication', 'activeTab' => 1, 'clubType' => $this->clubType, 'clubTitles' => $clubTitles);

        return $return;
    }

    /**
     * This action is used for fetching creations list and log data
     *
     * @param string $type List or log
     *
     * @return JsonResponse
     */
    public function getClubAssignmentConfirmationDataAction($type) {
        if ($type == 'list') {
            $applicationToConfirm = $this->em->getRepository('CommonUtilityBundle:FgCmClubAssignmentConfirmationLog')->getClubAssignmentConfirmationLog($this->clubType, $this->clubId, 'list', false, $this->clubDefaultLang);
            $return['aaData'] = $applicationToConfirm;
        } else {
            $applicationLog = $this->em->getRepository('CommonUtilityBundle:FgCmClubAssignmentConfirmationLog')->getClubAssignmentConfirmationLog($this->clubType, $this->clubId, 'log', false, $this->clubDefaultLang);
            $return['aaData'] = $applicationLog;
        }

        return new JsonResponse($return);
    }

    /**
     * This action is used to confirm or discard Application.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $action Whether to confirm or discard
     *
     * @Template("ClubadminContactBundle:Confirmation:confirmOrDiscardClubAssignmentApplications.html.twig")
     *
     * $page = {creations/mutations}
     * $action = {confirm/discard}
     * $selActionType = {all/selected}
     *
     * @return array Data array.
     */
    public function confirmOrDiscardClubAssignmentApplicationPopupAction(Request $request, $action) {
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
     * This action is used to confirm or discard ClubAssignment application.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * $action = {confirm/discard}
     *
     * @return Json Response.
     */
    public function updateConfirmClubAssignmentApplicationAction(Request $request) {
        $action = $request->get('action');
        $selectedIds = json_decode($request->get('selectedId', '0'));
        $fgFedMembershipObj = new FgFedMemberships($this->container);
        if ($action == 'discard') {
            $result = $fgFedMembershipObj->confirmOrDiscardClubAssignment($selectedIds, 0);

            $flashMsg = ($result) ? 'APPLICATIONS_DISCARDED_SUCCESSFULLY' : 'APPLICATIONS_DISCARDED_FAILED';
        } else {
            $result = $fgFedMembershipObj->confirmOrDiscardClubAssignment($selectedIds, 1);
            $flashMsg = ($result) ? 'APPLICATIONS_CONFIRMED_SUCCESSFULLY' : 'APPLICATIONS_CONFIRMED_FAILED';
        }
        $updatedCount = ($result) ? count($selectedIds) : 0;

        return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->container->get('translator')->trans($flashMsg), 'noparentload' => false, 'count' => $updatedCount));
    }

    /**
     * Function to show pop up in confirmation listing
     *
     * @param \Clubadmin\ContactBundle\Controller\Request $request
     * @return Template
     */
    public function contactProfilePreviewExistingFedAction(Request $request) {
        $contactId = $request->get('contactId');
        $contacts = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->addExistingFedMember($this->container, '', $this->federationId, $this->clubId, $contactId);

        return $this->render('ClubadminContactBundle:Confirmation:profileAddExistingPopUp.html.twig', array('contactDetails' => $contacts[0])
        );
    }

}
