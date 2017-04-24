<?php

namespace Clubadmin\ContactBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Clubadmin\Util\Contactlist;
use Clubadmin\Classes\Contactfilter;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Util\FgSettings;
use Common\UtilityBundle\Repository\Pdo\ContactPdo;
use Common\UtilityBundle\Util\FgFedMemberships;
use Symfony\Component\HttpFoundation\Request;
use Common\UtilityBundle\Repository\Pdo\membershipPdo;

/**
 * ArchiveController.
 *
 * This controller was created for handling Archive functionalities
 *
 * @version    Release:1
 */
class ArchiveController extends FgController
{

    /**
     * Template for archive contacts.
     *
     * @return template
     */
    public function movetoarchiveAction(Request $request)
    {
        $actionType = $request->get('actionType') ? $request->get('actionType') : 'assign';
        $selActionType = $request->get('selActionType') ? $request->get('selActionType') : '';
        $selContactIds = $request->get('selContacts') ? $request->get('selContacts') : '';
        $joiningDateDetails = $this->em->getRepository('CommonUtilityBundle:FgCmMembershipHistory')->getMaxofJoiningDate($selContactIds, $this->clubId, $this->clubType);
        $joiningDate = $this->get('club')->formatDate($joiningDateDetails['maxDate'], 'date');
        $dateToday = $this->get('club')->formatDate(date('Y-m-d H:i:s'), 'date');
        $terminologyService = $this->get('fairgate_terminology_service');
        $clubObj = $this->container->get('club');
        $communicationModule = (in_array('communication', $clubObj->get('bookedModulesDet'))) ? 1 : 0;
        if ($dragCatType == 'TEAM') {
            $dragCat = $this->clubTeamId;
        }
        $serviceAssignArray = array();
        $servicesCount = $this->em->getRepository('CommonUtilityBundle:FgSmBookings')->getCountOfAllServiceAssignments($this->clubId, $selContactIds);
        foreach ($servicesCount as $val) {
            if ($val['serviceCount'] > 0) {
                $serviceAssignArray[] = $val['contact_id'];
            }
        }

        $return = array('actionType' => $actionType, 'clubId' => $this->clubId, 'clubType' => $this->clubType, 'selActionType' => $selActionType, 'loggedContactId' => $this->contactId, 'joiningDate' => $joiningDate, 'memberCount' => $joiningDateDetails['memberId'], 'dateToday' => $dateToday, 'serviceAssignArray' => json_encode($serviceAssignArray),'communicationModule'=>$communicationModule);

        return $this->render('ClubadminContactBundle:Archive:archivecontacts.html.twig', $return);
    }
    
    
     /**
     * Function to get Subscriber
     *
     * @return json data
     */
    public function checkSubscriberAction(Request $request)
    {
        
        $selContactIds =  $request->get('selContacts') ? $request->get('selContacts') : '';
        $selContactIds = json_decode($selContactIds);
        $selectedIds = implode(',', $selContactIds);
        $email = $this->container->getParameter('system_field_primaryemail');
        $columns = array('contactName', 'contactid', 'clubId', "`$email` as email");
        $getSubscriber = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getContactsHavingPrimaryEmail($this->container, $this->contactId, $this->container->get('club'), $columns, 'contact', 1, false, $selectedIds);
                                                                                                                    
        $getSubscriberArray = array();
        $getSubscriberEmailArray = array();
                
        foreach ($getSubscriber as $key => $val) {
            $getSubscriberArray[] = $val['id'];
            $getSubscriberEmailArray[] = $val ;
        }
        
        return new JsonResponse(array('getSubscriber' => $getSubscriberArray ,'getSubscriberEmail' => $getSubscriberEmailArray ));
    }


    /**
     * Save selected contacts as archive contacts.
     *
     * @return template
     */
    public function saveArchiveContactsAction(Request $request)
    {
        $archiveContacts = json_decode($request->get('archiveData', '0'));
        $fromPage = $request->get('fromPage', '');
        $actionType = $request->get('actionType', '');
        $totalCount = $request->get('totalCount', '');
        $leavingDate = $request->get('leavingDate', '');
        $subscriberData = $request->get('subscriberData', '');
        $subscriberArray = json_decode($subscriberData);
        
       
        $phpDateFormat = FgSettings::getPhpDateFormat();
        $dateObj = new \DateTime();
        $leavingDate = $dateObj->createFromFormat($phpDateFormat, $leavingDate)->format('Y-m-d H:i:s');
        $totalOwnMembers = $request->get('totalOwnMembers', '');
        $terminologyService = $this->get('fairgate_terminology_service');
       
        $nowdate = strtotime(date('Y-m-d H:i:s'));
        $dateToday = date('Y-m-d H:i:s', $nowdate);
        if ($request->getMethod() == 'POST') {
           
            foreach ($archiveContacts as $cont) {
                $contactIds[] = $cont;
                if ($actionType == 'archive') {
                    $is_subscriber = 0;
                    
                    if(in_array($cont, $subscriberArray)){
                        $is_subscriber = 1;
                    }
                         
                    $insert .= "( '" . $cont . "','" . $this->clubId . "','" . $this->contactId . "','" . $dateToday . "','" . $is_subscriber . "'),";
                }
            }
            if (count($contactIds) > 0) {
                $teamTerminology = ucfirst($terminologyService->getTerminology('Team', $this->container->getParameter('singular')));
                $club = $this->get('club');
                $clubHeirarchy = $club->get('clubHeirarchy');
                $shareFlag = 0;
                $contactDetailsResult = array();
                $toBeArchivedContacts = array();
                // Checking whether the contact is a shared contact or bot
                foreach ($contactIds as $contactId) {
                    $result = false;
                    $contactDetailsResult = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->checkFedMemberArchive($contactId);
                    if (count($contactDetailsResult) > 0) {
                        //only if contact is an active fed member proceed to fed membership removal class.
                        if (($contactDetailsResult['isFedCategory'] != '' && $contactDetailsResult['isFedMembershipConfirmed'] == '0') || ($contactDetailsResult['isFedMembershipConfirmed'] == '1' && $contactDetailsResult['oldFedMembershipId'] != '')) {
                            $fgFedMembership = new FgFedMemberships($this->container);
                            if ($this->clubType == 'federation' || $this->clubType == 'sub_federation') {
                                $result = $fgFedMembership->removeFedMembershipBeforeArchiving($contactId, $leavingDate);
                            } else {
                                $result = $fgFedMembership->removeFedMembershipBeforeArchiving($contactId);
                            }
                        } else {
                            $contactPdo = new ContactPdo($this->container);
                            $contactPdo->sharedMainContactUpdation($contactDetailsResult['fedContactId'], $contactDetailsResult['fedContactId'], $this->clubId, 'archive');

                            $result = true;
                        }
                    }

                    if ($result) {
                        $toBeArchivedContacts[] = $contactId;
                    }
                };
               
                $this->em->getRepository('CommonUtilityBundle:FgCmContact')->archiveContact($toBeArchivedContacts, $this->contactId, $insert, $dateToday, $this->clubId, $this->container->getParameter('system_field_firstname'), $this->container->getParameter('system_field_lastname'), $this->container->getParameter('system_field_companyname'), $this->container->getParameter('system_field_dob'), $teamTerminology, $clubHeirarchy, $this->clubType, $this->container->getParameter('system_field_primaryemail'), $this->container->getParameter('system_field_salutaion'), $this->container->getParameter('system_field_gender'), $this->container->getParameter('system_field_corress_lang'), $leavingDate ,1 );
                if ($fromPage == 'contactlist') {
                    $flashMsg = '';
                    if ($actionType == 'archive') {
                        $flashMsg = 'CONTACTS_ARCHIVED_SUCCESSFULLY';
                    }
                    $redirect = $this->generateUrl('contact_index');

                    return new JsonResponse(array('status' => 'SUCCESS', 'sync' => 1, 'redirect' => $redirect, 'flash' => $this->get('translator')->trans($flashMsg, array('%selcount%' => count($toBeArchivedContacts), '%totalcount%' => $totalCount))));
                }
            }
        }
    }

    /**
     * Check create/edit contact is mergeable
     * @param type $formValues
     * @param type $editData
     * @return boolean
     */
    private function isMergeableContact($formValues, $editData)
    {
        $mergeable = false;
        if (($formValues['fed_membership_cat_id'] == '' || $formValues['fed_membership_cat_id'] == null) && ($editData != '' || $editData != null)) {
            $mergeable = true;
        }
        return $mergeable;
    }

    /**
     * For reactivate the archived contact.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getSelectedArchiveContactAction(Request $request)
    {
        $primaryEmail = $this->container->getParameter('system_field_primaryemail');
        $pdo = new ContactPdo($this->container);
        //Get the POST data
        $archiveDatas = $request->get('archivedData', '');
        if ($archiveDatas != '') {
            $contactType = $archiveDatas['contactType'];
            $this->removeNextPrevSession();
            $selectedIds = explode(',', $archiveDatas['selcontactIds']);
            $fedMembershipVal = $archiveDatas['fedMembershipVal'];
            $hasFedmembership = ($fedMembershipVal != 0) ? true : false;
            $emails = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getEmailField($archiveDatas['selcontactIds'], $primaryEmail);
            $status = '';
            $updateIds = '';
            $duplicateEmailId = '';
            $updateIdsCount = 0;
            $subscriberIds = '';
            $contactErrorArray = array();

            // Checking the count of reactivate contact and the contact has the federation membership
            if ($hasFedmembership && count($selectedIds) > 1) {

                $return = $this->multipleReactivateWithFedMem($selectedIds, $emails, $hasFedmembership, $fedMembershipVal);
                return new JsonResponse($return);
            } elseif ($hasFedmembership && count($selectedIds) == 1) {
                $contactObj = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->find($selectedIds[0]);
                $contactData = $pdo->getContactDetailsForMembershipDetails('editable', $selectedIds[0]);
                $contactData = $contactData[0];
                $federaionObj = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->find($fedMembershipVal);
                $contactData['fedMembershipId'] = $fedMembershipVal;
                $contactData['fedMembershipTitle'] = $federaionObj->getTitle();
                $fieldType = ($contactData['Iscompany'] == 1) ? 'Company' : 'Single person';
                $result = array();
                if ($emails[$contactObj->getFedContact()->getId()]) {
                    $result = $this->em->getRepository('CommonUtilityBundle:FgCmAttribute')->searchEmailExistAndIsMergable($this->container, $selectedIds[0], $emails[$contactObj->getFedContact()->getId()], $hasFedmembership, $subscriberId = 0, $from = 'contact', $excluMerge = true, $fieldType);
                }

                if (count($result) > 0) {
                    $contactErrorArray[$selectedIds[0]] = $selectedIds[0];
                } else {
                    $pdo = new ContactPdo($this->container);
                    $contactDataInMergableFormat = $this->convertDataToMergableFormat($contactData);
                    $mergeableReturn = $pdo->getMergeableContacts($contactDataInMergableFormat, $fieldType, $selectedIds[0]);

                    // If the contact is mergable, then merging popup will displayed
                    if (count($mergeableReturn['duplicates']) > 0 || count($mergeableReturn['mergeEmail']) > 0) {
                        $mergeableReturn['status'] = 'MERGE';
                        $mergeableReturn['noparentload'] = true;
                        $mergeableReturn['mergeable'] = true;
                        $mergeableReturn['currentContactData'] = $contactData;

                        return new JsonResponse($mergeableReturn);
                    } else {
                        //Assign/Change/Remove Fed memebership handling
                        $fgFedMembershipObj = new FgFedMemberships($this->container);
                        $fgFedMembershipObj->processFedMembership($contactData['id'], $contactData['fedMembershipId']);

                        $updateIds = $contactData['id'];
                        $updateIdsCount++;
                        $emailCount = $this->checkSubscriberEmailExists($contactData['id'], $emails[$contactObj->getFedContact()->getId()], $this->clubId);
                        if ($emailCount > 0) {
                            $subscriberIds = $selectedIds[0];
                        }
                    }
                }
            } else {
                foreach ($selectedIds as $id) {
                    $contactObj = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->find($id);
                    if ($this->checkEmailExist($id, $emails[$contactObj->getFedContact()->getId()])) {
                        $duplicateEmailId .= ',' . $id;
                    } else {
                        $updateIds .= ',' . $id;
                        $updateIdsCount++;
                    }
                    $emailCount = $this->checkSubscriberEmailExists($id, $emails[$contactObj->getFedContact()->getId()], $this->clubId);
                    if ($emailCount > 0) {
                        $subscriberIds .= ',' . $id;
                    }
                }
            }

            if ($updateIds != '') {
                $ids = ltrim($updateIds, ',');
                $subscriberIds = ltrim($subscriberIds, ',');
                $this->em->getRepository('CommonUtilityBundle:FgCmContact')->activateContact($ids, $this->contactId, $subscriberIds, $this->clubId, $hasFedmembership);
                $flashMsg = 'REACTIVATE_SUCCESS_MESSAGE';
                $status = array('status' => 'SUCCESS', 'pageTpe' => $contactType, 'totalCount' => count($selectedIds), 'flash' => $this->get('translator')->trans($flashMsg, array('%totalcount%' => count($selectedIds), '%updatecount%' => $updateIdsCount)));

                return new JsonResponse($status);
            } else {
                $flashMsg = 'REACTIVATE_NOT_SUCCESS_MESSAGE';
                $status = array('status' => 'FAILURE', 'pageTpe' => $contactType, 'flash' => $this->get('translator')->trans($flashMsg, array('%totalcount%' => count($selectedIds), '%updatecount%' => 0)));

                return new JsonResponse($status);
            }
        } else {
            $status = array('Count' => '', 'status' => 'FAILURE');

            return new JsonResponse($status);
        }
    }

    /**
     * multiple Reactivate With FedMem
     * @param array $selectedIds selectedIds
     * @param array $emails      emails
     * @param int $hasFedmembership hasFedmembership
     * @param int $fedMembershipVal fedMembershipVal
     * @return array
     */
    private function multipleReactivateWithFedMem($selectedIds, $emails, $hasFedmembership, $fedMembershipVal)
    {
        $meargable = array();
        $count = 0;
        $pdo = new ContactPdo($this->container);
        foreach ($selectedIds as $id) {
            $contactObj = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->find($id);
            $contactData = $pdo->getContactDetailsForMembershipDetails('editable', $id);
            $contactData = $contactData[0];
            $contactData['fedMembershipId'] = $fedMembershipVal;
            $fieldType = ($contactData['Iscompany'] == 1) ? 'Company' : 'Single person';

            // Email validations for the contact
            $result = $this->em->getRepository('CommonUtilityBundle:FgCmAttribute')->searchEmailExistAndIsMergable($this->container, $id, $emails[$contactObj->getFedContact()->getId()], $hasFedmembership, $subscriberId = 0, $from = 'contact', $excluMerge = true, $fieldType);

            if (count($result) > 0) {
                $contactErrorArray[$id] = $id;
                continue;
            } else {
                $isMergeable = $this->isMergeableContact($contactData, $fedMembershipVal);
                if ($isMergeable) {
                    $contactDataInMergableFormat = $this->convertDataToMergableFormat($contactData);
                    $mergeableReturn = $pdo->getMergeableContacts($contactDataInMergableFormat, $fieldType, $id);

                    // Checking the contact is mergable
                    if (count($mergeableReturn['duplicates']) > 0 || count($mergeableReturn['mergeEmail']) > 0) {
                        $contactErrorArray[$id] = $id;
                        $meargable[$id]['currentContactData'] = $contactData;
                        $meargable[$id]['meargable'] = $mergeableReturn;
                        continue;
                    } else {
                        $count++;
                        $fgFedMembershipObj = new FgFedMemberships($this->container);
                        $fgFedMembershipObj->processFedMembership($contactData['id'], $contactData['fedMembershipId']);

                        $updateIdss .= ',' . $id;

                        $emailCount = $this->checkSubscriberEmailExists($id, $emails[$contactObj->getFedContact()->getId()], $this->clubId);
                        if ($emailCount > 0) {
                            $subscriberIds .= ',' . $id;
                        }
                    }
                } else {
                    $count++;
                    $fgFedMembershipObj = new FgFedMemberships($this->container);
                    $fgFedMembershipObj->processFedMembership($contactData['id'], $contactData['fedMembershipId']);

                    $updateIdss .= ',' . $id;

                    $emailCount = $this->checkSubscriberEmailExists($id, $emails[$contactObj->getFedContact()->getId()], $this->clubId);
                    if ($emailCount > 0) {
                        $subscriberIds .= ',' . $id;
                    }
                }
            }
        }

        $return['noparentload'] = true;
        $return['mergeable'] = false;
        $return['status'] = 'NORMAL';
        $return['totalCount'] = count($selectedIds);
        if (count($meargable) > 0) {
            $return['mergeable'] = true;
            $return['mergableContacts'] = $meargable;
            $return['status'] = 'MERGE';
        }
        $return['alreadyActivatedCnt'] = $count;
        $return['contacts'] = array_values($selectedIds);
        //$return['totalCnt'] =  count($selectedIds);

        if ($updateIdss != '') {
            $ids = ltrim($updateIdss, ',');
            $subscriberIds = ltrim($subscriberIds, ',');
            $this->em->getRepository('CommonUtilityBundle:FgCmContact')->activateContact($ids, $this->contactId, $subscriberIds, $this->clubId, $hasFedmembership);
            $flashMsg = 'REACTIVATE_SUCCESS_MESSAGE';
            if ($return['mergeable'] == false) {
                $return['flash'] = $this->get('translator')->trans($flashMsg, array('%totalcount%' => count($selectedIds), '%updatecount%' => $count));
            }
        } else {
            $flashMsg = 'REACTIVATE_NOT_SUCCESS_MESSAGE';
            if ($return['mergeable'] == false && $count == 0) {
                $return['flash'] = $this->get('translator')->trans($flashMsg, array('%totalcount%' => count($selectedIds), '%updatecount%' => $count));
            }
        }

        return $return;
    }

    /**
     * Function to reactivate mergable contacts.
     *
     * @return JsonResponse
     */
    public function saveReactivateContactAction(Request $request)
    {
        $contactData = $request->get('contactData', '');
        $mergeTo = $request->get('mergeTo', '');
        $typeMer = $request->get('typeMer', '');
        $merging = $request->get('merging', '');
        $mergeType = $request->get('mergeType', 'single');
        $totalCnt = $request->get('totalCnt', 0);
        $alreadyActivated = $request->get('alreadyActivated', 0);

        if ($mergeType == 'single') {
            if ($merging == 'save') {
                if ($merging == 'save' && $mergeTo != 'fed_mem') {
                    $contactUpdateStr = 'UPDATE fg_cm_contact SET merge_to_contact_id=' . $mergeTo . ",allow_merging=1, is_deleted=0 WHERE id ='" . $contactData['id'] . "'";
                    $this->conn->executeQuery($contactUpdateStr);
                    //updating last_updated date for all shared levels
                    $contactObj = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->find($contactData['id']);
                    $this->em->getRepository('CommonUtilityBundle:FgCmContact')->updateLastUpdated($contactObj->getFedContact()->getId(), 'fedContact');
                }
                //Assign/Change/Remove Fed memebership handling
                $fgFedMembershipObj = new FgFedMemberships($this->container);
                $fgFedMembershipObj->processFedMembership($contactData['id'], $contactData['fedMembershipId']);

                $primaryEmail = $this->container->getParameter('system_field_primaryemail');
                $emailCount = $this->checkSubscriberEmailExists($contactData['id'], $contactData[$primaryEmail], $this->clubId);
                if ($emailCount > 0) {
                    $subscriberId = $contactData['id'];
                }

                $this->em->getRepository('CommonUtilityBundle:FgCmContact')->activateContact($contactData['id'], $this->contactId, $subscriberId, $this->clubId, 1);
                $flashMsg = 'REACTIVATE_SUCCESS_MESSAGE';
                $status = array('status' => 'SUCCESS', 'pageTpe' => $contactType, 'totalCount' => count($contactData['id']), 'flash' => $this->get('translator')->trans($flashMsg, array('%totalcount%' => count($contactData['id']), '%updatecount%' => count($contactData['id']))));

                return new JsonResponse($status);
            } else {
                $flashMsg = 'REACTIVATE_NOT_SUCCESS_MESSAGE';
                $status = array('status' => 'FAILURE', 'pageTpe' => $contactType, 'totalCount' => count($contactData['id']), 'flash' => $this->get('translator')->trans($flashMsg, array('%totalcount%' => count($contactData['id']), '%updatecount%' => count($contactData['id']))));

                return new JsonResponse($status);
            }
        } else {
            foreach ($mergeTo as $contact => $value) {
                if ($merging == 'save') {
                    if ($merging == 'save' && $value['applymer'] != 'fed_mem') {
                        $contactUpdateStr = 'UPDATE fg_cm_contact SET merge_to_contact_id=' . $value['applymer'] . ",allow_merging=1, is_deleted=0 WHERE id ='" . $contact . "'";
                        $this->conn->executeQuery($contactUpdateStr);
//                        //updating last_updated date for all shared levels
                        $contactObj = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->find($contact);
                        $this->em->getRepository('CommonUtilityBundle:FgCmContact')->updateLastUpdated($contactObj->getFedContact()->getId(), 'fedContact');
                    }
                    //Assign/Change/Remove Fed memebership handling
                    $fgFedMembershipObj = new FgFedMemberships($this->container);
                    $fgFedMembershipObj->processFedMembership($contact, $contactData[$contact]['currentContactData']['fedMembershipId']);

                    $primaryEmail = $this->container->getParameter('system_field_primaryemail');
                    $emailCount = $this->checkSubscriberEmailExists($contact, $contactData[$contact]['currentContactData'][$primaryEmail], $this->clubId);
                    if ($emailCount > 0) {
                        $subscriberId = $contact;
                    }

                    $this->em->getRepository('CommonUtilityBundle:FgCmContact')->activateContact($contact, $this->contactId, $subscriberId, $this->clubId, 1);
                } else {
                    unset($contactData[$contact]);
                }
            }

            $flashMsg = (count($contactData) + $alreadyActivated > 0) ? 'REACTIVATE_SUCCESS_MESSAGE' : 'REACTIVATE_NOT_SUCCESS_MESSAGE';
            $status = array('status' => 'SUCCESS', 'noparentload' => true, 'pageTpe' => $contactType, 'totalCount' => count($contactData) + $alreadyActivated, 'flash' => $this->get('translator')->trans($flashMsg, array('%totalcount%' => $totalCnt, '%updatecount%' => count($contactData) + $alreadyActivated)));

            return new JsonResponse($status);
        }
    }

    /**
     * Function to convert array format.
     *
     * @param array $contactData Current contact data
     *
     * @return Array
     */
    private function convertDataToMergableFormat($contactData)
    {
        $primaryEmail = $this->container->getParameter('system_field_primaryemail');
        $catCommun = $this->container->getParameter('system_category_communication');
        $catPerson = $this->container->getParameter('system_category_personal');
        $firstname = $this->container->getParameter('system_field_firstname');
        $lastname = $this->container->getParameter('system_field_lastname');
        $dob = $this->container->getParameter('system_field_dob');
        $land = $this->container->getParameter('system_field_corres_ort');
        $corrCat = $this->container->getParameter('system_category_personal');

        $newContactData = $contactData;
        $newContactData[$catCommun][$primaryEmail] = $contactData[$primaryEmail];
        $newContactData[$catPerson][$firstname] = $contactData[$firstname];
        $newContactData[$catPerson][$lastname] = $contactData[$lastname];
        $newContactData[$catPerson][$dob] = $contactData[$dob];
        $newContactData[$corrCat][$land] = $contactData[$land];

        return $newContactData;
    }

    /**
     * Function to remove session related to Next and previous links.
     */
    private function removeNextPrevSession()
    {
        $this->session->remove('flag');
        $this->session->remove('filteredContactDetailsDisplayLength');
        $this->session->remove('nextPreviousContactListData');
    }

    /**
     * For check the email is exist or not.
     *
     * @param type $contactId
     * @param type $email
     * @param type $clubId
     *
     * @return Int count of email
     */
    private function checkSubscriberEmailExists($contactId, $email, $clubId)
    {
        $conn = $this->container->get('database_connection');
        $primaryEmail = $this->container->getParameter('system_field_primaryemail');
        if ($email == '') {
            return false;
        }
        $results = $this->em->getRepository('CommonUtilityBundle:FgCnSubscriber')->searchEmailExists($conn, $clubId, $email);
        $emailCount = '';
        foreach ($results as $result) {
            $emailCount = $result['emailCount'];
            $deleteId[] = $result['id'];
        }
        $deleteId = implode(',', $deleteId);
        $results = $this->em->getRepository('CommonUtilityBundle:FgCnSubscriber')->deleteReactivateSubscriber($conn, $deleteId);

        return $emailCount;
    }

    /**
     * For check the email is exist or not.
     *
     * @param Int    $contactId Contact id
     * @param String $email     Email
     *
     * @return bool
     */
    private function checkEmailExist($contactId, $email)
    {
        $club = $this->container->get('club');
        $conn = $this->container->get('database_connection');
        $primaryEmail = $this->container->getParameter('system_field_primaryemail');
        if ($email == '') {
            return false;
        }
        $result = $this->em->getRepository('CommonUtilityBundle:FgCmAttribute')->searchEmailExists($conn, $club, $primaryEmail, $email, $contactId, false);

        return count($result) > 0 ? true : false;
    }

    /**
     * Template for delete archive contacts.
     *
     * @return template
     */
    public function permanentdeletearchiveAction(Request $request)
    {
        $actionType = $request->get('actionType') ? $request->get('actionType') : 'assign';
        $selActionType = $request->get('selActionType') ? $request->get('selActionType') : '';
        $terminologyService = $this->get('fairgate_terminology_service');
        $return = array('actionType' => $actionType, 'clubId' => $this->clubId, 'clubType' => $this->clubType, 'selActionType' => $selActionType);

        return $this->render('ClubadminContactBundle:Archive:permanentdeletecontacts.html.twig', $return);
    }

    /**
     * Delete archived contacts permanently.
     *
     * @return template
     */
    public function savepermanentdeletedarchiveAction(Request $request)
    {
        $deleteDataContacts = json_decode($request->get('deleteData', '0'));
        $fromPage = $request->get('fromPage', '');
        $actionType = $request->get('actionType', '');
        $totalCount = $request->get('totalCount', '');
        $nowdate = strtotime(date('Y-m-d H:i:s'));
        if ($request->getMethod() == 'POST') {
            //collect all subfedcontactid and fedcontactid of the selected contact
            $getAllContactIds = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getAllContactIds($deleteDataContacts);
            //iterate result for create id array
            foreach ($getAllContactIds as $contidArray) {
                foreach ($contidArray as $contid) {
                    $contactIds[] = $contid;
                }
            }

            //Remove the null entries and duplicate entries
            // null entry will happen when we delete from federation
            $contactIds = array_unique(array_filter($contactIds));

            if (count($contactIds) > 0) {
                $totalcontactlist = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->deleteContactPermanently($contactIds, $this->clubId, $this->container, $this->contactId);

                if ($fromPage == 'archivelist') {
                    $flashMsg = ($actionType == 'delete') ? 'CONTACTS_PERMANENTLY_DELETED_SUCCESSFULLY' : 'ARCHIVED_SPONSORS_PERMANENTLY_DELETED_SUCCESSFULLY';
                    $contactType = ($actionType == 'delete') ? 'archive' : 'archivedsponsor';
                    $club = $this->get('club');
                    $contactlistClass = new Contactlist($this->container, $this->contactId, $club, $contactType);
                    $contactlistClass->setCount();
                    $contactlistClass->setFrom();
                    $contactlistClass->setCondition();
                    $countQuery = $contactlistClass->getResult();
                    $totalcontactlist = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($countQuery);
                    //total count of contact list related to the particular club or federation or subfederation
                    $totalArchiveContactCount = $totalcontactlist[0]['count'];

                    return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans($flashMsg, array('%totalcount%' => $totalCount)), 'totalDeletedContactCount' => $totalArchiveContactCount, 'contactType' => $contactType, 'totalCount' => $totalCount, 'noparentload' => 1));
                }
            }
        }
    }

    /**
     * For collect the data for pop up window.
     *
     * @return type
     */
    public function getreactivatepopupAction(Request $request)
    {
        $clubService = $this->container->get('club');
        $totalCount = '';
        $primaryEmail = $this->container->getParameter('system_field_primaryemail');
        //Get the POST data
        $archiveDatas = $request->get('selcontactIds', '');
        $clubService = $this->container->get('club');
        $clubDefaultLang = $clubService->get('default_lang');
        $fedMembershipMandatory = $clubService->get('fedMembershipMandatory');
        $fedId = $this->clubType == 'federation' ? $this->clubId : $this->federationId;
        if ($archiveDatas != '') {
            $selectedIds = explode(',', $archiveDatas['selcontactIds']);
            $contactNameArray = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getContactName($archiveDatas, '', $clubService, $this->container, 'archive');
            $return = array('contactnames' => $contactNameArray, 'contactIds' => $selectedIds);

            if ($fedMembershipMandatory) {
                $objMembershipPdo = new membershipPdo($this->container);
                $membersipFields = $objMembershipPdo->getMemberships($this->clubType, $this->clubId, $this->subFederationId, $this->federationId);
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
            }

            return $this->render('ClubadminContactBundle:Archive:reactivatecontact.html.twig', $return);
        }
    }

    /**
     * Function to remove the assignment of selected or all contact of the sidebar::active category.
     *
     * @return Json
     */
    public function getAllContactArchiveHandlerAction(Request $request)
    {
        //Get the POST data
        $selectedIds = $request->get('selcontactIds', 'all');
        $searchval = $request->get('searchVal', '');
        $formdataValues = $request->get('filterData', '');
        $contactIds = $this->getContactIdsArchiveAction($selectedIds, $searchval, $formdataValues);

        return new JsonResponse(array('contactIds' => $contactIds));
    }

    /**
     * Get the contact Id's either selected or filtered list.
     *
     * @param array  $selectedIds    Selected ids
     * @param String $searchval      Search value
     * @param array  $formdataValues Form values
     *
     * @return array contactIds
     */
    public function getContactIdsArchiveAction($selectedIds, $searchval, $formdataValues)
    {
        if ($selectedIds == 'all') {
            //PREPARE CONTACT_IDS FROM FILTER
            $filter = json_decode(($formdataValues), true);
            $club = $this->get('club');
            $aColumns = array();
            array_push($aColumns, 'contactid', 'contactname', 'membershipType', 'clubId');
            $contactlistClass = new Contactlist($this->container, $this->contactId, $club, 'archive');
            $contactlistClass->setFrom();
            $contactlistClass->setCondition();
            if (!(empty($searchval))) {
                $sSearch = $searchval;
                $columns[] = $this->container->getParameter('system_field_firstname');
                $columns[] = $this->container->getParameter('system_field_lastname');
                foreach ($nonQuotedColumns as $key => $nonQuotedColumn) {
                    if ($nonQuotedColumn['type'] == 'CF') {
                        $columns[] = $nonQuotedColumn['id'];
                    }
                }
                if (in_array('contactid', $aColumns)) {
                    $key = array_search('contactid', $aColumns); //
                    unset($tablecolumns[$key]);
                }
                $sWhere = '(';
                foreach ($columns as $column) {
                    $sSearch = FgUtility::getSecuredDataString($sSearch, $this->conn);
                    $sWhere .= '`' . $column . '`' . " LIKE '%" . $sSearch . "%' OR ";
                }
                $sWhere = substr_replace($sWhere, '', -3);
                $sWhere .= ')';
                $contactlistClass->addCondition($sWhere);
            }
            if (!(empty($filter))) {
                $jsonArray = $filter;
                $filterData = array_shift($filter);
                $filterObj = new Contactfilter($this->container, $contactlistClass, $filterData, $club);
                if (!(empty($searchval))) {
                    $sWhere .= ' AND (' . $filterObj->generateFilter() . ')';
                } else {
                    $sWhere .= ' (' . $filterObj->generateFilter() . ')';
                }
                $contactlistClass->addCondition($sWhere);
            }
            $contactlistClass->setColumns($aColumns);
            //call query for collect the data
            $listquery = $contactlistClass->getResult();
            file_put_contents('query.txt', $listquery . "\n");
            $contactlistDatas = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($listquery);
            $contactIds = $contactlistDatas;
        } else {
            $contactIds = explode(',', $selectedIds);
        }

        return $contactIds;
    }

    /**
     * For collect the former federation memeber data for pop up window.
     *
     * @return type
     */
    public function getformerfederationMemberpopupAction(Request $request)
    {
        $clubService = $this->container->get('club');
        $totalCount = '';
        $primaryEmail = $this->container->getParameter('system_field_primaryemail');
        //Get the POST data
        $selectedDatas = $request->get('selcontactIds', '');
        $dataType = $request->get('dataType', '');
        if ($dataType == 'all') {
            $formermemberDatas = $this->getDatatableListdata();
            $formerFedmemberCount = count($formermemberDatas);
            $return = array('contactnames' => '', 'contactIds' => '', 'count' => $formerFedmemberCount);
        } else {
            $selectedIds = explode(',', $selectedDatas['selcontactIds']);
            $contactNameArray = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getContactName($selectedDatas, '', $clubService, $this->container, 'formerfederationmember');
            $return = array('contactnames' => $contactNameArray, 'contactIds' => $selectedIds, 'count' => 0);
        }

        return $this->render('ClubadminContactBundle:Archive:formerfederationcontact.html.twig', $return);
    }

    /**
     * For delete the former federation member contact.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteformerFederationMemberAction(Request $request)
    {
        $clubService = $this->container->get('club');
        //Get the POST data
        $archiveDatas = $request->get('archivedData', '');
        if ($archiveDatas != '') {
            $selectedIds = ($archiveDatas['selcontactIds'] != '') ? explode(',', $archiveDatas['selcontactIds']) : '';
            $datatype = $archiveDatas['dataType'];
            $status = '';
            $updateIds = '';
            $getIds = array();
            $duplicateEmailId = '';
            $updateIdsCount = 0;
            if ($datatype == 'selected') {
                $updateIds = '';
                foreach ($selectedIds as $id) {
                    // $updateIds.= "," . $id;
                    $updateIds[] = $id;
                }
                $this->em->getRepository('CommonUtilityBundle:FgCmContact')->deleteFormerFedMember($updateIds, $this->contactId, $this->container);
                foreach ($updateIds as $key => $value) {
                    $getIds[$value] = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getSubFedContactId($value);
                }
                $getIdArray = array_map('current', $getIds);
                $this->setSubscriberFromFormerFederation($getIdArray);
                $formerfedMemberClass = new Contactlist($this->container, $this->contactId, $clubService, 'formerfederationmember');
                $formerfedMemberClass->setCount();
                $formerfedMemberClass->setFrom();
                $formerfedMemberClass->setCondition();
                $countQuery = $formerfedMemberClass->getResult();
                $totalfedmemberlist = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($countQuery);
                $flashMsg = 'FORMERFED_MEM_DELETE_SUCCESS_MESSAGE';
                $status = array('status' => 'SUCCESS', 'activeFedmemberCount' => $totalfedmemberlist[0]['count'], 'flash' => $this->get('translator')->trans($flashMsg, array('%totalcount%' => count($selectedIds), '%updatecount%' => count($selectedIds))));
            } elseif ($datatype == 'all') {
                $contactIds = '';
                $formermemberDatas = $this->getDatatableListdata();
                if (count($formermemberDatas) > 0) {
                    foreach ($formermemberDatas as $formermemberData) {
                        $contactIds[] = $formermemberData['id'];
                    }
                }
                //$contactIds = ltrim($contactIds, ',');
                $this->em->getRepository('CommonUtilityBundle:FgCmContact')->deleteFormerFedMember($contactIds, $this->contactId, $this->container);
                foreach ($contactIds as $key => $value) {
                    $getIds[$value] = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getSubFedContactId($value);
                }
                $getIdArray = array_map('current', $getIds);
                $this->setSubscriberFromFormerFederation($getIdArray);
                $formerfedMemberClass = new Contactlist($this->container, $this->contactId, $clubService, 'formerfederationmember');
                $formerfedMemberClass->setCount();
                $formerfedMemberClass->setFrom();
                $formerfedMemberClass->setCondition();
                $countQuery = $formerfedMemberClass->getResult();
                $totalfedmemberlist = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($countQuery);
                $flashMsg = 'FORMERFED_MEM_DELETE_SUCCESS_MESSAGE';
                $status = array('status' => 'SUCCESS', 'activeFedmemberCount' => $totalfedmemberlist[0]['count'], 'flash' => $this->get('translator')->trans($flashMsg, array('%totalcount%' => count($contactIds), '%updatecount%' => count($contactIds))));
            } else {
                $flashMsg = 'FORMERFED_MEM_DELETE_SUCCESS_MESSAGE';
                $status = array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans($flashMsg, array('%totalcount%' => count($selectedIds), '%updatecount%' => count($selectedIds))));
            }
        } else {
            $status = array('Count' => '', 'status' => 'FAILURE');
        }

        return new JsonResponse($status);
    }

    /**
     * For adding a subscriber from former federation member.
     *
     * @param type $contactIds sign in user id
     *
     * @return bool
     */
    public function setSubscriberFromFormerFederation($contactIds)
    {
        $clubId = $this->clubId;
        $fgContact = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->find($this->contactId);
        foreach ($contactIds as $key => $ids) {
            foreach ($ids as $Valkey => $id) {
                if ($id != '') {
                    $contactClubId = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getContactClubId($id);
                    $clubobj = $this->em->getRepository('CommonUtilityBundle:FgClub')->find($contactClubId);
                    $clubType = $clubobj->getClubType();
                    $masterTable = 'master_federation_' . $contactClubId;

                    $contactDetails = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getContactDetails($id, $masterTable, $clubType);
                    if ($contactClubId != $clubId) {
                        if ($contactDetails['Email'] != '' && $contactDetails['Subscriber'] == 1) {
                            $this->em->getRepository('CommonUtilityBundle:FgCnSubscriber')->newSubscriber($contactDetails, $clubobj, '', '', $fgContact);
                        }
                    } elseif ($contactClubId == $clubId) {
                        if ($contactDetails['Email'] != '' && $contactDetails['Subscriber'] == 1) {
                            $this->em->getRepository('CommonUtilityBundle:FgCnSubscriber')->newSubscriber($contactDetails, $clubobj, '', '', $fgContact);
                        }
                    }
                }
            }
        }

        return true;
    }
}
