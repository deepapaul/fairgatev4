<?php

/**
 * Contact Confirmation Controller
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
use Common\UtilityBundle\Repository\Pdo\ContactPdo;
use Common\UtilityBundle\Util\FgSettings;
use Clubadmin\ContactBundle\Util\FgContactForm;
use Common\UtilityBundle\Repository\Pdo\membershipPdo;

/**
 * Contact Confirmation Controller
 *
 * This controller is used for handling changes to be confirmed.
 */
class ContactConfirmationController extends ParentController
{

    /**
     * This action is used for listing creations to be confirmed.
     *
     * @Template("ClubadminContactBundle:ContactConfirmation:confirmations.html.twig")
     *
     * @return array Data array.
     */
    public function creationsListAction()
    {
        $club = $this->container->get('club');
        $clubType = ['standard_club', 'federation_club', 'sub_federation_club'];
        if (!in_array($club->get('type'), $clubType)) {
            throw $this->createNotFoundException($club->get('title') . ' have no access to this page');
        }
        $confirmationsCount = $this->em->getRepository('CommonUtilityBundle:FgCmsContactFormApplications')->getApplicationsToConfirm($this->clubId, true, array('PENDING'));

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
     * This action is used for fetching creations list and log data
     *
     * @param string $type List or log
     *
     * @return JsonResponse
     */
    public function getCreationsAction($type)
    {
        if ($type == 'list') {
            $creationsToConfirm = $this->em->getRepository('CommonUtilityBundle:FgCmsContactFormApplications')->getApplicationsToConfirm($this->clubId, false, array('PENDING'));
            $return['aaData'] = $creationsToConfirm;
        } else {
            $creationsLog = $this->em->getRepository('CommonUtilityBundle:FgCmsContactFormApplications')->getApplicationsToConfirm($this->clubId, false, array('CONFIRMED', 'DISMISSED'));
            $return['aaData'] = $creationsLog;
        }

        return new JsonResponse($return);
    }

    /**
     * This action is used to confirm or discard confirmations creations
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $action Whether to confirm or discard
     *
     * @Template("ClubadminContactBundle:ContactConfirmation:confirmOrDiscardConfirmations.html.twig")
     *
     * $page = creations
     * $action = {confirm/discard}
     * $selActionType = {all/selected}
     *
     * @return array Data array.
     */
    public function confirmOrDiscardConfirmationPopupAction(Request $request, $page, $action)
    {
        $selActionType = $request->get('selActionType') ? $request->get('selActionType') : 'all';
        $return['page'] = $page;
        $return['selActionType'] = $selActionType;
        $return['action'] = $action;
        $return['clubType'] = $this->clubType;

        if ($action == 'confirm') {
            $return['fedMembershipMandatory'] = $this->get('club')->get('fedMembershipMandatory');
            $memberships = array();
            if ($return['fedMembershipMandatory']) {
                $return['header'] = ($selActionType == 'all') ? 'CONFIRMATION_CONFIRM_CREATION_HEADER_ALL' : 'CONFIRMATION_CONFIRM_CREATION_HEADER_SELECTED';
                $return['label'] = ($selActionType == 'all') ? 'CONFIRMATION_CONFIRM_CREATION_LABEL_ALL' : 'CONFIRMATION_CONFIRM_CREATION_LABEL_SELECTED';

                $memberships['fed'][0] = $this->get('translator')->trans('SELECT_DROPDOWN');
                $objMembershipPdo = new membershipPdo($this->container);
                $membersipFields = $objMembershipPdo->getMemberships($this->clubType, $this->clubId, $this->subFederationId, $this->federationId);
                $clubDefaultLang = $this->get('club')->get('default_lang');
                foreach ($membersipFields as $key => $memberCat) {
                    $title = $memberCat['allLanguages'][$clubDefaultLang]['titleLang'] != '' ? $memberCat['allLanguages'][$clubDefaultLang]['titleLang'] : $memberCat['membershipName'];
                    if (($memberCat['clubId'] == $this->federationId)) {
                        $memberships['fed'][$key] = $title;
                    }
                }
            } else {
                $return['header'] = ($selActionType == 'all') ? 'CONFIRM_CREATION_HEADER_ALL' : 'CONFIRM_CREATION_HEADER_SELECTED';
                $return['label'] = ($selActionType == 'all') ? 'CONFIRM_CREATION_LABEL_ALL' : 'CONFIRM_CREATION_LABEL_SELECTED';
            }

            $return['memberships'] = $memberships;
        } else {
            $return['header'] = ($selActionType == 'all') ? 'DISCARD_ALL_CREATIONS' : 'DISCARD_SELECTED_CREATIONS';
            $return['label'] = ($selActionType == 'all') ? 'DISCARD_ALL_CREATIONS_TEXT' : 'DISCARD_SELECTED_CREATIONS_TEXT';
        }

        return $return;
    }

    /**
     * This action is used to confirm or discard confirmations creations
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * $page = {creations}
     * $action = {confirm/discard}
     * $selActionType = {all/selected}
     *
     * @return Json Response.
     */
    public function updateConfirmationsAction(Request $request)
    {
        $action = $request->get('action');
        $page = $request->get('page');
        $selectedIds = json_decode($request->get('selectedId', '0'));
        $totCount = count($selectedIds);
        $successCount = count($selectedIds);
        $selectedMembership['fed'] = $request->get('fedMembership', 0);
        $selectedMembership['club'] = 0;
        $hasFedmembership = ($selectedMembership['fed'] != 0) ? true : false;
        $pdo = new ContactPdo($this->container);
        $clubHeirarchy = $this->get('club')->get('clubHeirarchy');
        $primaryEmail = $this->container->getParameter('system_field_primaryemail');
        if ($action == 'discard') {
            $this->sendSwiftMail('dismissal', $selectedIds);
            $this->em->getRepository('CommonUtilityBundle:FgCmsContactFormApplications')->discardSelectedConfirmations($this->contactId, $selectedIds);
            $successCount = count($selectedIds);
        } else {
            //get contact details
            $contactDetails = $this->em->getRepository('CommonUtilityBundle:FgCmsContactFormApplications')->getContactInConfirmation($selectedIds);
            //get memberships
            $memberships = array();
            $objMembershipPdo = new membershipPdo($this->container);
            $membershipFields = $objMembershipPdo->getMemberships($this->clubType, $this->clubId, $this->subFederationId, $this->federationId);
            foreach ($membershipFields as $key => $memberCat) {
                if (($memberCat['clubId'] == $this->federationId)) {
                    $memberships['fed'][] = $key;
                }
            }
            $contactIdArr = array_keys($contactDetails);
            $emails = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getEmailField(implode(',', $contactIdArr), $primaryEmail);
            $contactDetailsCpy = $contactDetails;
            // Checking the count of reactivate contact and the contact has the federation membership
            
            foreach ($contactDetails as $contactId => $details) {
                $contactObj = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->find($contactId);
                $contactData = $pdo->getContactDetailsForMembershipDetails('draft', $contactId);
                $contactData = $contactData[0];
                $fieldType = ($contactData['Iscompany'] == 1) ? 'Company' : 'Single person';
                // Email validations for the contact
                if ($emails[$contactObj->getFedContact()->getId()] != '') {
                    $result = $this->em->getRepository('CommonUtilityBundle:FgCmAttribute')->searchEmailExistAndIsMergable($this->container, $contactId, $emails[$contactObj->getFedContact()->getId()], $hasFedmembership, 0, 'contact', true, $fieldType);
                } else {
                    $result = array();
                }
                if ($hasFedmembership) {
                    $contactData['fedMembershipId'] = $selectedMembership['fed'];
                    //if email validation fails
                    if (count($result) > 0) {
                        if (count($selectedIds) == 1) {
                            $failedEmail = array('status' => 'EMAILFAILED', 'noparentload' => true, 'page' => $page);

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
                } else {
                    //if email validation fails
                    if (count($result)>0) {
                        unset($contactDetails[$contactId]);
                        if (count($selectedIds) == 1) {
                            $failedEmail = array('status' => 'EMAILFAILED', 'noparentload' => true, 'page' => $page);

                            return new JsonResponse($failedEmail);
                        }
                        //unset values from selected contact to confirm
                        if (($key = array_search($contactDetails[$contactId][2], $selectedIds)) !== false) {
                            unset($selectedIds[$key]);
                        }
                        unset($contactDetails[$contactId]);
                        $successCount--;
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
            if (count($selectedIds)) {
                $this->em->getRepository('CommonUtilityBundle:FgCmsContactFormApplications')->confirmSelectedConfirmations($this->contactId, $selectedIds);
                $mailAction = ($action == 'confirm') ? 'acceptance' : 'dismissal';
                $this->sendSwiftMail($mailAction, $selectedIds);
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
    private function convertDataToMergableFormat($contactData)
    {
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
    public function saveConfirmationContactAction(Request $request)
    {
        $contactData = $request->get('contactData', '');
        $mergeTo = $request->get('mergeTo', '');
        $typeMer = $request->get('typeMer', '');
        $merging = $request->get('merging', '');
        $creationArray = $request->get('creationArray', '');
        $mergeType = $request->get('mergeType', '');
        $clubHeirarchy = $this->get('club')->get('clubHeirarchy');
        //get memberships
        $memberships = array();
        $objMembershipPdo = new \Common\UtilityBundle\Repository\Pdo\membershipPdo($this->container);
        $membershipFields = $objMembershipPdo->getMemberships($this->clubType, $this->clubId, $this->subFederationId, $this->federationId);
        foreach ($membershipFields as $key => $memberCat) {
            if (($memberCat['clubId'] == $this->federationId)) {
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
                $this->em->getRepository('CommonUtilityBundle:FgCmsContactFormApplications')->confirmSelectedConfirmations($this->contactId, $creationArray['selectedIds']);

                $flashMsg = '%selcount%_OUT_OF_%totalcount%_CREATIONS_CONFIRMED_SUCCESSFULLY';
                $status = array('status' => 'SUCCESS', 'totalCount' => count($contactData['id']), 'noparentload' => false, 'flash' => $this->get('translator')->trans($flashMsg, array('%totalcount%' => count($contactData['id']), '%selcount%' => count($contactData['id']))), 'page' => 'creationsappform');
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
                    $this->em->getRepository('CommonUtilityBundle:FgCmsContactFormApplications')->confirmSelectedConfirmations($this->contactId, array($creationArray['contactDetails'][$merFrm]['2']));
                    $selcount++;
                    $flag = true;
                }
                // add membership and confirm selected ids for non-mergable contacts
                if (count($creationArray['nonMergeableContacts']) > 0) {
                    foreach ($creationArray['nonMergeableContacts'] as $contKey => $nonMerContacts) {
                        $this->em->getRepository('CommonUtilityBundle:FgCmContact')->addMemberships($this->container, array($contKey => $nonMerContacts), $creationArray['selectedMembership'], $memberships, $this->clubId, $this->clubType, $clubHeirarchy, $this->federationId);
                        $this->em->getRepository('CommonUtilityBundle:FgCmsContactFormApplications')->confirmSelectedConfirmations($this->contactId, array($creationArray['nonMergeableContacts'][$contKey]['2']));
                        $selcount++;
                    }
                    $flag = true;
                }
            }
            if (!$flag) {
                $flashMsg = 'CREATIONS_NOT_SUCCESS';
                $status = array('status' => 'FAILURE', 'totalCount' => $selcount, 'noparentload' => false, 'flash' => $this->get('translator')->trans($flashMsg), 'page' => 'creationsappform');
            } else {
                $this->sendSwiftMail('acceptance', array($creationArray['selectedIds']));
                $flashMsg = '%selcount%_OUT_OF_%totalcount%_CREATIONS_CONFIRMED_SUCCESSFULLY';
                $status = array('status' => 'SUCCESS', 'totalCount' => $selcount, 'noparentload' => false, 'flash' => $this->get('translator')->trans($flashMsg, array('%totalcount%' => $creationArray['totCount'], '%selcount%' => $selcount)), 'page' => 'creationsappform');
            }
        }
        return new JsonResponse($status);
    }

    /**
     * Contact detail template
     *
     * @param int $contact contact id
     * 
     * @return HTML
     */
    public function contactDetailAction($contact)
    {
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
     * Function to show pop up in confirmation listing
     *
     * @param \Clubadmin\ContactBundle\Controller\Request $request
     * @return Template
     */
    public function contactProfilePreviewExistingFedAction(Request $request)
    {
        $contactId = $request->get('contact');
        $contacts = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->addExistingFedMember($this->container, '', $this->federationId, $this->clubId, $contactId);

        return $this->render('ClubadminContactBundle:Confirmation:profileAddExistingPopUp.html.twig', array('contactDetails' => $contacts[0])
        );
    }

    /**
     * Function to show pop up in confirmation listing
     *
     * @param \Clubadmin\ContactBundle\Controller\Request $request
     * 
     * @return Template
     */
    public function contactAppFormPreviewAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $id = $request->get('id');
        $appObj = $em->getRepository('CommonUtilityBundle:FgCmsContactFormApplications')->find($id);
        $jsonData = json_decode($appObj->getFormData(), true);
        
        $createdAt = date_format($appObj->getCreatedAt(), 'Y-m-d H:i:s');
        $clubService = $this->container->get('club');
        $clubId = $clubService->get('id');
        $contactLang = $this->getContactLang($clubService);

        $fields = $em->getRepository('CommonUtilityBundle:FgCmsPageContentElementFormFields')->getAllFormFields($clubId, $contactLang, $appObj->getForm()->getId());        
        $objMembershipPdo = new membershipPdo($this->container);
        $membershipFields = $objMembershipPdo->getMemberships($this->clubType, $this->clubId, $this->subFederationId, $this->federationId);
        foreach ($membershipFields as $key => $memberCat) {
            if (empty($memberCat['allLanguages'][$contactLang])) {
                $memberships[$key] = $memberCat['membershipName'];
            } else {
                $memberships[$key] = $memberCat['allLanguages'][$contactLang]['titleLang'];
            }
        }

        unset($jsonData['form']['files']);
        $formFields = $this->formatArray($fields, $jsonData['form'], false, $memberships);       
        $contactFields = $this->formatArray($fields, $jsonData['contact'], false, $memberships);        
        $membership = $this->formatArray($fields, $jsonData['club-membership'], false, $memberships);    
        //instead of merging arrays, union those arrays, so that we can preserve the keys. keys are sortOrder. So we can diplay the fields in sort order
        // consider the case the sortorder of field will nor repeat
        $data = ($contactFields + $membership + $formFields);
        ksort($data);
        $status = $appObj->getStatus();

        return $this->render('ClubadminContactBundle:ContactConfirmation:contactFormApplicationPopUp.html.twig', array('data' => $data, 'createdAt' => $createdAt, 'status' =>$status)
        );
    }

    /**
     * Method to format form content
     *
     * @param array   $fields           form-Fields
     * @param array   $jsonDatas        json data
     * @param boolean $showEmptyValues  if true, empty values also listed, else avoid that
     *
     * @return array formatted array
     */
    private function formatArray($fields, $jsonDatas, $showEmptyValues, $memberships)
    {
        $formattedData = array();
        $dateObj = new \DateTime();
        $phpDateFormat = FgSettings::getPhpDateFormat();
        $phpTimeFormat = FgSettings::getPhpTimeFormat();
        foreach ($jsonDatas as $key => $jsonData) {
            $key = 'a' . $key;
            $fieldValue = '';
            switch ($fields[$key]['fieldType']) {
                case 'select':
                case 'radio':
                case 'checkbox':
                    //traslation for salutation and gender
                    if ($key == 'a' . $this->container->getParameter('system_category_personal')) {
                        $jsonData = ($jsonData == 'Informal') ? $this->container->get('translator')->trans('CN_INFORMAL') : (($jsonData == 'Formal') ? $this->container->get('translator')->trans('CN_FORMAL') : $jsonData);
                    }
                    if ($key == 'a' . $this->container->getParameter('system_field_gender')) {
                        $jsonData = ($jsonData == 'male' || $jsonData == 'Male') ? $this->container->get('translator')->trans('CN_MALE') : (($jsonData == 'female' || $jsonData == 'Female') ? $this->container->get('translator')->trans('CN_FEMALE') : $jsonData);
                    }
                    if ($key == 'a' . $this->container->getParameter('system_field_corress_lang')) {
                        $jsonData = Intl::getLanguageBundle()->getLanguageName($jsonData);
                    }
                    if ($key == 'a' . $this->container->getParameter('system_field_nationality1') || $key == 'a' . $this->container->getParameter('system_field_nationality2')) {
                        $jsonData = Intl::getRegionBundle()->getCountryName($jsonData);
                    }
                    $fieldValues = array();
                    if ($fields[$key]['attribute'] == '') {
                        $fieldValue = '';
                        if (is_array($fields[$key]['fieldoptions'])) {
                            if (is_array($jsonData)) {
                                foreach ($jsonData as $option) {
                                    $fieldValue .= $fields[$key]['fieldoptions'][$option]['title'] . ', ';
                                }
                            } else {
                                $fieldValue .= $fields[$key]['fieldoptions'][$jsonData]['title'] . ', ';
                            }
                            $fieldValue = trim($fieldValue, ", ");
                        } else {
                            $fieldValue = $jsonData;
                        }
                    } else {
                        if (is_array($jsonData)) {
                            $fieldValue = implode(', ', $jsonData);
                        } else {
                            $fieldValue = $jsonData;
                        }
                    }
                    break;
                case 'date':
                    $fieldValue = ($jsonData) ? $dateObj->createFromFormat('Y-m-d', $jsonData)->format($phpDateFormat) : '';
                    break;
                case 'time':
                    $fieldValue = ($jsonData) ? $dateObj->createFromFormat('H:i', $jsonData)->format($phpTimeFormat) : '';
                    break;
                default:
                    if ($fields[$key]['formFieldType'] == 'club-membership') {
                        $clubMem = $this->container->get('translator')->trans('CLUB_MEMBERSHIP');
                        $fields[$key]['fieldname'] = $clubMem;

                        $jsonData = $memberships[$jsonData];
                    }
                    $fieldValue = $jsonData;                    
                    break;
            }

            if (($showEmptyValues) || ($fieldValue && $fields[$key]['fieldname'] != '' )) {
                $sortOrder = $fields[$key]['sortOrder'];
                $formattedData[$sortOrder] = array(
                    'fieldName' => $fields[$key]['fieldname'],
                    'fieldValue' => str_replace('<script', '<scri&nbsp;pt', (strip_tags($fieldValue))),
                    'fieldValueForPopup' => str_replace('<script', '<scri&nbsp;pt', nl2br(strip_tags($fieldValue))),
                    'fieldType' => $fields[$key]['fieldType'],
                    'sortOrder' => $sortOrder
                        );                
                if ($fields[$key]['fieldType'] == 'fileupload' || $fields[$key]['fieldType'] == 'imageupload') {
                    $createdClubId = 0;
                    if ($fields[$key]['formFieldType'] == 'contact') {
                        $attrObj = $this->em->getRepository('CommonUtilityBundle:FgCmAttribute')->find($fields[$key]['attribute']);
                        $createdClubId = $attrObj->getClub()->getId();
                    }
                    $params = array('folder' => ($fields[$key]['formFieldType'] == 'contact' ? ($fields[$key]['fieldType'] == 'fileupload' ? 'contactfield_file' : 'contactfield_image') : 'contact_application_file'), 'file' => str_replace('<script', '<scri&nbsp;pt', (strip_tags($fieldValue['fileNameNew']))));
                    $formattedData[$sortOrder]['fileUrl'] = FgUtility::generateUrlForSharedClub($this->container, 'filemanager_download_contact_application', $createdClubId, $params);
                    $formattedData[$sortOrder]['fieldValue'] = str_replace('<script', '<scri&nbsp;pt', (strip_tags($fieldValue['fileNameOriginal'])));
                }
            }
        }
        ksort($formattedData);
        return $formattedData;
    }

    /**
     * Send Swift Mail
     *
     * @param int   $formId     formId
     * @parma array $contactIds contactIds
     *
     * @return email
     */
    public function sendSwiftMail($action, $selectedIds)
    {
        $fgContactForm = new FgContactForm($this->container);
        foreach ($selectedIds as $appForm) {
            $appObj = $this->em->getRepository('CommonUtilityBundle:FgCmsContactFormApplications')->find($appForm);
            $toEmails = $this->toEmails($appObj);
            $rowContactLocale = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getContactData($appObj->getClubContact()->getId(), true);
            $contactLang = $fgContactForm->getContactLocale($rowContactLocale);
            $mailContent = $fgContactForm->getNotificationMailParams($appObj->getForm()->getId(), $action, $contactLang['default_system_lang']);
            if (!empty($toEmails) && $mailContent['sendMail']) {
                try {
                    $mailContent['body'] = $this->renderView('ClubadminContactBundle:ContactConfirmation:contactFormMailTemplate.html.twig', $mailContent);
                    $fgContactForm->sendSwiftMesage($mailContent['body'], $toEmails, $mailContent['senderEmail'], $mailContent['subject']);
                }
                /* If the emaild id is not RFC compliant */ catch (\Swift_RfcComplianceException $e) {
                    $error = 'Swift_RfcComplianceException. Recepients : ' . implode(',', $toEmails);
                }
                /* Other exceptions */ catch (Exception $e) {
                    $error = $e;
                }
                /* If any exception is caught */
                if ($error != '') {
//                    $this->writeLog($e . "\n");
//                    $this->writeLog('\nform : ' . $appObj->getForm()->getId() . ',  Recepients : ' . implode(',', $toEmails) . '\n');
                }
            }
        }
    }

    /**
     * get the recipients email address from all the contact and form fields with use mail for notification = 1
     *
     * @param object $appObj application objects
     *
     * @return array email array
     */
    private function toEmails($appObj)
    {
        $jsonData = json_decode($appObj->getFormData(), true);
        $emailFields = $this->em->getRepository('CommonUtilityBundle:FgCmsPageContentElementFormFields')->getAllEmailFieldsForContact($appObj->getForm()->getId(), $this->clubId);
        $emails = array();
        foreach ($emailFields as $emailField) {
            if ($emailField['attribute'] > 0) {
                $flag = array_key_exists($emailField['attribute'], $jsonData['contact']);
                if ($flag > 0) {
                    $emails[] = $jsonData['contact'][$emailField['attribute']];
                }
            } else {
                $flag = array_key_exists($emailField['formfield'], $jsonData['form']);
                if ($flag > 0) {
                    $emails[] = $jsonData['form'][$emailField['formfield']];
                }
            }
        }
        return $emails;
    }

    /**
     * get contact language
     *
     * @param object $clubService   clubService
     *
     * @return string
     */
    private function getContactLang($clubService)
    {
        $superadmin = $this->get('contact')->get('isSuperAdmin');
        if (!$superadmin && $this->contactId) {
            // logged in contact and is not a superadmin
            $contactLang = $this->get('contact')->get('corrLang');
        } else {
            //is superadmin or is not logged in
            $contactLang = $clubService->get('club_default_lang');
        }
        return $contactLang;
    }
}
