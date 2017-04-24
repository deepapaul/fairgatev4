<?php

namespace Clubadmin\CommunicationBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Clubadmin\Util\Contactlist;
use Clubadmin\Classes\Contactdatatable;
use Admin\UtilityBundle\Repository\Pdo\ClubPdo;

/**
 * RecipientsController
 *
 * This controller was created for handling Recipients List in Communication.
 *
 * @package    ClubadminCommunicationBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
class NewsletterwizardrecipientsController extends FgController
{

    /**
     * For show the step2
     *
     * @return type
     */
    public function indexAction(Request $request, $newsletterId)
    {
        $recipientList = $this->em->getRepository('CommonUtilityBundle:FgCnRecepients')->getRecipientsList($this->clubId);
        $emailFieldsArray = $this->iterateEmailFields($recipientList);
        $club = $this->get('club');
        $bookedModuleDetails = $club->get('bookedModulesDet');
        $clubType = $club->get("type");
        $additionalsubscriberflag = 0;
        $federationdisplayflag = 0;
        $recipientId = 0;
        $manualSelectedIds = '';
        $checkType = "";
        $salutationArray = '';
        $selectedFlags = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletter')->getNewsletterSettingFlags($newsletterId, $this->clubId);
        if (is_array($selectedFlags) && count($selectedFlags[0]) > 1) {
            $additionalsubscriberflag = $selectedFlags[0]['isSubscriberSelection'];
            $federationdisplayflag = $selectedFlags[0]['includeFormerMembers'];
            $recipientId = $selectedFlags[0]['id'];
            $newsletterType = $selectedFlags[0]['publishType'];
            $checkType = $newsletterType;
            $savedstep = $selectedFlags[0]['step'];
            $savedMailtype = $selectedFlags[0]['newsletterType'];
            $salutationDetails['type'] = $selectedFlags[0]['salutationType'];
            $salutationDetails['salutaion'] = $selectedFlags[0]['salutation'];
            $salutationArray = json_encode($salutationDetails);
        } else {
            throw $this->createNotFoundException($this->clubTitle . ' have no access to this page');
        }
        $backUrl = '';
        $pageType = 'simplemail';
        $backLink = '';
        if ($savedMailtype == 'GENERAL') {
            $pageType = 'newsletter';
            $backUrl = $this->generateUrl('edit_newsletter', array("id" => $newsletterId));
            $backLink = $this->generateUrl('newsletter_mailings');
        } else {
            $backUrl = $this->generateUrl('edit_simplemail', array("id" => $newsletterId));
            $backLink = $this->generateUrl('newsletter_simplemailings');
        }
        $breadCrumb = array(
            'breadcrumb_data' => array(),
            'back' => $backLink
        );
        $mailType = ($checkType == '') ? "MANDATORY" : $checkType;
        $emailFields = '';
        $mandatoryDatas = '';
        $selectedEmailFields = array('main' => '', 'substitute' => '');
        if ($checkType == 'MANDATORY') {
            $emailFields = $this->mandatoryRecipientSetting($club);
            $selectedEmailFields = $this->selectedEmailFields($newsletterId);
        }
        $manualySelectedContacts = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletterManualContacts')->getManualySelectedContact($newsletterId);
        if (is_array($manualySelectedContacts) && count($manualySelectedContacts) > 0) {
            $manualSelectedIds = json_encode($manualySelectedContacts);
        }

        if ($checkType == 'MANDATORY') {
            return $this->render('ClubadminCommunicationBundle:Newsletterwizard:step2_mandatory_recipient.html.twig', array('breadCrumb' => $breadCrumb, 'recipientLists' => $recipientList, 'emailFields' => $emailFieldsArray, 'newsletterId' => $newsletterId, 'clubType' => $clubType, 'step' => 2, 'newsletterType' => $mailType, 'checkType' => $mailType, 'additionalSubFlag' => $additionalsubscriberflag, "feddisplayFlag" => $federationdisplayflag, 'recipientid' => $recipientId, 'allemailFields' => $emailFields, "mandatoryDatas" => $mandatoryDatas, 'manualselectedIds' => $manualSelectedIds, 'selectedEmailfields' => $selectedEmailFields, 'bookedModule' => $bookedModuleDetails, 'wizardStep' => $savedstep, 'savedMailtype' => $savedMailtype, 'backUrl' => $backUrl, 'pageType' => $pageType));
        } else {
            return $this->render('ClubadminCommunicationBundle:Newsletterwizard:step2_nonmandatory_recipient.html.twig', array('breadCrumb' => $breadCrumb, 'recipientLists' => $recipientList, 'emailFields' => $emailFieldsArray, 'newsletterId' => $newsletterId, 'clubType' => $clubType, 'step' => 2, 'newsletterType' => $mailType, 'checkType' => $mailType, 'additionalSubFlag' => $additionalsubscriberflag, "feddisplayFlag" => $federationdisplayflag, 'recipientid' => $recipientId, 'allemailFields' => $emailFields, "mandatoryDatas" => $mandatoryDatas, 'manualselectedIds' => $manualSelectedIds, 'selectedEmailfields' => $selectedEmailFields, 'bookedModule' => $bookedModuleDetails, 'wizardStep' => $savedstep, 'savedMailtype' => $savedMailtype, 'backUrl' => $backUrl, 'pageType' => $pageType, 'salutationarray' => $salutationArray));
        }
    }

    /**
     * For iterate the email fields
     *
     * @param type $emailFields
     *
     * @return array
     */
    private function iterateEmailFields($emailFields)
    {
        if (is_array($emailFields) && count($emailFields) > 0) {
            foreach ($emailFields as $emailField) {
                if (isset($emailField['mainEmailIds']) && $emailField['mainEmailIds'] != '') {
                    $mainMailIds = explode(',', $emailField['mainEmailIds']);
                    $mainfieldArray[$emailField['id']] = $this->getFieldsName($mainMailIds);
                }
                if (isset($emailField['substituteEmailId']) && $emailField['substituteEmailId'] != '') {
                    $subMailIds = explode(',', $emailField['substituteEmailId']);
                    $subfieldArray[$emailField['id']] = $this->getFieldsName($subMailIds);
                }
            }
        }
        $fieldnameArrays = array();
        $fieldnameArrays['mainEmails'] = $mainfieldArray;
        $fieldnameArrays['substituteEmails'] = $subfieldArray;

        return $fieldnameArrays;
    }

    /**
     * For collect the field name
     *
     * @param type $fieldids
     *
     * @return string
     */
    private function getFieldsName($fieldids)
    {
        $club = $this->get('club');
        $allfieldArrays = $club->get('allContactFields');
        foreach ($fieldids as $ids) {
            if ($ids == 'parent_email') {
                $fildnames.= $this->get('translator')->trans('CONNECTED_PARENT_EMAIL') . ', ';
            } else {
                if (array_key_exists($ids, $allfieldArrays)) {
                    $fildnames.= $allfieldArrays[$ids]['title'] . ", ";
                }
            }
        }
        $fildnames = rtrim(trim($fildnames), ",");

        return $fildnames;
    }

    /**
     * For show the non-mandatory tabs
     * @return type
     */
    public function getNonmandatoryTabshowAction(Request $request)
    {
        $newsletterId = $request->get('newsletterId', '');
        $recipientId = $request->get('recipient', '');
        $club = $this->get('club');
        $clubType = $club->get("type");
        $manualselectedContact = $request->get('send_message', '');
        $manualDeletedContact = $request->get('removedRecipient', '');
        $federationmemberFlag = $request->get('formermember_flag', '0');
        $additionalsubscriberFlag = $request->get('additional_subscriber_flag', '0');
        $manualSelectedId = ($manualselectedContact != '') ? implode(",", $manualselectedContact['user']['id']) : '';
        /* FAIR-715 */
        $fedMemberSubscriptionCount = $federationmemberFlag ? $this->getFedMemberSubscriptionCount() : 0;
        if ($additionalsubscriberFlag) {
            $subscribers = $this->em->getRepository('CommonUtilityBundle:FgCnSubscriber')->getAdditionalSubscribersList($newsletterId, $this->clubId, $this->clubDefaultSystemLang, $this->clubDefaultLang);
            $additionalSubscriberCount = count($subscribers);
        } else {
            $additionalSubscriberCount = 0;
        }

        return $this->render('ClubadminCommunicationBundle:Newsletterwizard:step2.tab.html.twig', array('newsletterId' => $newsletterId, 'recipientId' => $recipientId, 'clubType' => $clubType, 'manualSelectedContact' => $manualSelectedId, 'manualDeletedContact' => $manualDeletedContact, 'federationmemberFlag' => $federationmemberFlag, 'additionalsubscriberFlag' => $additionalsubscriberFlag, 'fedCount' => $fedMemberSubscriptionCount, 'additionalSubscriberCount' => $additionalSubscriberCount));
    }

    /**
     * function find the count of fed member with subscription
     */
    private function getFedMemberSubscriptionCount()
    {
        $club = $this->get('club');
        $contactType = 'formerfederationmember';
        $contactlistClass = new Contactlist($this->container, $this->contactId, $club, $contactType);
        $contactlistClass->setCount();
        $contactlistClass->setFrom();
        $contactlistClass->setCondition();
        $contactlistClass->addCondition("(`" . $this->container->getParameter('system_field_primaryemail') . "` IS NOT NULL AND `" . $this->container->getParameter('system_field_primaryemail') . "` !='' AND fg_cm_contact.is_subscriber=1)");
        //call query for collect the data
        $totallistquery = $contactlistClass->getResult();
        $totalcontactlistDatas = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($totallistquery);
        //collect total number of records
        if (is_array($totalcontactlistDatas) && count($totalcontactlistDatas) > 0) {
            $totalrecords = $totalcontactlistDatas[0]['count'];
        } else {
            $totalrecords = 0;
        }

        return $totalrecords;
    }

    /**
     * For get the active subscriber list
     * 
     * @param Request $request The request object
     * 
     * @return view The subscriber list preview
     */
    public function getSubscriberPreviewListAction(Request $request)
    {
        $newsletterId = $request->get('newsletterId', '');

        return $this->render('ClubadminCommunicationBundle:Newsletterwizard:activesubscriberpreview_step2.html.twig', array('newsletterId' => $newsletterId));
    }

    /**
     * For get the non-mandatory preview list
     *
     * @return type
     */
    public function getNonmandatoryPreviewListAction(Request $request)
    {
        $recipientId = $request->get('recipient', '');
        $manualselectedContact = $request->get('manualSelectedIds', '');
        $newsletterId = $request->get('newsletterId', '');
        $passedSettings = array('recipientId' => $recipientId, 'manual_contacts' => $manualselectedContact, 'clubAndSubFedIds' => true);
        $recipientsData = $this->em->getRepository('CommonUtilityBundle:FgCnRecepients')->getNewsletterRecipients('nonmandatory', 'draft', $newsletterId, $this->clubId, $this->container, $this->contactId, false, true, $passedSettings, false, $this->clubDefaultSystemLang);
        $recipients = json_encode($recipientsData);
        $exceptionsData = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletterExcludeContacts')->getExceptionsOfNewsletter($newsletterId);
        $exceptions = json_encode($exceptionsData);
        //get club titles for displaying in listing
        $clubObj = new ClubPdo($this->container);
        $clubData = $clubObj->getAllSubLevelData($this->federationId);
        $clubs = json_encode(array_column($clubData, 'title', 'id'));

        return $this->render('ClubadminCommunicationBundle:Newsletterwizard:activerecipientpreview_step2.html.twig', array('newsletterId' => $newsletterId, 'recipientId' => $recipientId, 'manualSelectedContact' => $manualselectedContact, 'recipientData' => $recipients, 'exceptiondata' => $exceptions, 'clubData' => $clubs));
    }

    /**
     * For get the fedmemberreciepienttab
     *
     * @return type
     */
    public function getFedmembersPreviewTabAction()
    {
        return $this->render('ClubadminCommunicationBundle:Newsletterwizard:formermemberpreview_step2.html.twig');
    }

    /**
     * For get the fedmember data
     * 
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getFedmembersPreviewListAction()
    {
        //call a service for collect all relevant data related to the club
        $club = $this->get('club');
        $clubType = $club->get('type');
        $aColumns = array();
        array_push($aColumns, 'contactname', "`" . $this->container->getParameter('system_field_corress_lang') . "` AS CL_lang", "salutationText(fg_cm_contact.id, {$this->clubId}, '{$club->get('default_system_lang')}', NULL) AS salutation", "`" . $this->container->getParameter('system_field_primaryemail') . "` AS Email");
        if ($clubType == "federation") {
            array_push($aColumns, 'clubTitle', 'subFedTitle');
        } elseif ($clubType == "sub_federation") {
            array_push($aColumns, 'clubTitle');
        }
        $tablecolumns = $aColumns;
        if (in_array('contactname', $aColumns)) {
            $key = array_search('contactname', $aColumns); //
            unset($tablecolumns[$key]);
        }
        $contactType = 'formerfederationmember';
        $contactlistClass = new Contactlist($this->container, $this->contactId, $club, $contactType);
        $contactlistClass->setCount();
        $contactlistClass->setFrom();
        $contactlistClass->setCondition();
        $contactlistClass->addCondition("(`" . $this->container->getParameter('system_field_primaryemail') . "` IS NOT NULL AND `" . $this->container->getParameter('system_field_primaryemail') . "` !='' AND fg_cm_contact.is_subscriber=1)");

        //call query for collect the data
        $totallistquery = $contactlistClass->getResult();
        $totalcontactlistDatas = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($totallistquery);
        $contactlistClass->setColumns($aColumns);
        //call query for collect the data
        $listquery = $contactlistClass->getResult();
        //file_put_contents("query.txt", $listquery . "\n");
        $contactlistDatas = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($listquery);
        //collect total number of records
        if (is_array($totalcontactlistDatas) && count($totalcontactlistDatas) > 0) {
            $totalrecords = $totalcontactlistDatas[0]['count'];
        } else {
            $totalrecords = 0;
        }
        $output = array(
            "iTotalRecords" => $totalrecords,
            "iTotalDisplayRecords" => $totalrecords,
            "aaData" => $contactlistDatas
        );

        return new JsonResponse($output);
    }

    /**
     * Contact autocomplete action
     *
     * @param string $term          search parameter
     * @param string $passedColumns Extra columns to be taken
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getcontactNamesAction(Request $request)
    {
        $exclude = $request->get('exclude');
        $term = $request->get('term');
        $firstname = "`" . $this->container->getParameter('system_field_firstname') . "`";
        $lastname = "`" . $this->container->getParameter('system_field_lastname') . "`";
        $isComany = $request->get('isCompany');
        $step = $request->get('step') ? $request->get('step') : '';
        $joins = '';
        if ($request->get('type') == 'company') {
            $sWhere = "C.is_permanent_delete=0 and C.club_id = '{$this->clubId}'";
        } else {
            if ($this->clubType == 'federation') {
                $sWhere = "C.is_permanent_delete=0 and C.club_id = '{$this->clubId}' AND (C.main_club_id = '{$this->clubId}' OR (C.fed_membership_cat_id != '' AND C.fed_membership_cat_id IS NOT NULL AND C.is_fed_membership_confirmed='0'))";
                $joins = "LEFT JOIN master_federation_{$this->clubId} AS mc ON mc.fed_contact_id = C.id";
            } elseif ($this->clubType == 'sub_federation') {
                $sWhere = "C.is_permanent_delete=0 and C.club_id = '{$this->clubId}' AND (C.main_club_id = '{$this->clubId}' OR (C.fed_membership_cat_id != '' AND C.fed_membership_cat_id IS NOT NULL AND C.is_fed_membership_confirmed='0'))";
                $joins = "LEFT JOIN master_federation_{$this->clubId} AS mc ON mc.contact_id = C.id";
            } else {
                $sWhere = "C.is_permanent_delete=0 and C.club_id = '{$this->clubId}' AND (C.main_club_id = '{$this->clubId}' OR (C.fed_membership_cat_id != '' AND C.fed_membership_cat_id IS NOT NULL AND C.is_fed_membership_confirmed='0'))";
            }
        }
        $sWhere .= ($exclude) ? " AND id NOT IN($exclude)" : '';
        if ($isComany != 2) {
            $sWhere .= ($isComany == 1) ? " AND is_company=1" : " AND is_company=0";
        }

        $is_draft = 0;
        if (isset($is_draft)) {
            $sWhere .= ' AND is_draft = ' . $is_draft;
        }

        $newfield = "IF (C.is_company=0 ,CONCAT(contactName(C.id),IF(DATE_FORMAT(`4`,'%Y') = '0000' OR `4` is NULL OR `4` ='','',CONCAT(' (',DATE_FORMAT(`4`,'%Y'),')'))),contactName(C.id)) as title";
        if ($passedColumns != "") {
            $newfield .= ", $passedColumns";
        }
        if ($step == 5) {
            $quer = " AND (S.3 IS NOT NULL AND S.3!='') ";
        } else {
            $quer = " ";
        }
        if ($term == '') {
            $listquery = "SELECT C.id, $newfield FROM fg_cm_contact C left join master_system S on C.fed_contact_id=S.fed_contact_id $joins where  $sWhere AND C.is_deleted=0 ORDER BY title LIMIT 10";
        } else {
            $search = explode(" ", trim($term), 2);
            if (sizeof($search) > 1) {
                $listquery = "SELECT C.id, $newfield FROM fg_cm_contact C left join master_system S on C.fed_contact_id=S.fed_contact_id $joins where  $sWhere AND C.is_deleted=0 AND (S.$firstname LIKE '$search[0]%' OR S.$lastname LIKE '$search[0]%' OR S.`9` LIKE '$search[0]%') AND (S.$firstname LIKE '$search[1]%' OR S.$lastname LIKE '$search[1]%' OR S.`9` LIKE '$search[1]%')" . $quer . " ORDER BY title LIMIT 10";
            } else {
                $listquery = "SELECT C.id, $newfield FROM fg_cm_contact C left join master_system S on C.fed_contact_id=S.fed_contact_id $joins where  $sWhere AND C.is_deleted=0 AND (S.$firstname LIKE :search OR S.$lastname LIKE :search OR S.`9` LIKE :search)" . $quer . " ORDER BY title LIMIT 10";
            }
        }
        $fieldsArray = $this->conn->fetchAll($listquery, array(':search' => $term . '%'));

        return new JsonResponse($fieldsArray);
    }

    /**
     * Delete contact list action
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getDeletedContactListAction(Request $request)
    {
        $specialFieldsArray = $this->container->getParameter('country_fields');
        //$manualDeletedContact = $request->get('manualDeletedIds', '');
        $manualDeletedContact = '';
        if ($manualDeletedContact == '') {
            $output = array(
                "iTotalRecords" => 0,
                "iTotalDisplayRecords" => 0,
                "aaData" => array()
            );

            return new JsonResponse($output);
        }
        //call a service for collect all relevant data related to the club
        $club = $this->get('club');
        $aColumns = array();
        array_push($aColumns, 'contactname', "`" . $this->container->getParameter('system_field_corress_lang') . "` AS CL_lang", "salutationText(mc.contact_id, {$this->clubId}, '{$club->get('default_system_lang')}', NULL) AS salutation", "`" . $this->container->getParameter('system_field_primaryemail') . "` AS Email");
        $columnsArray = array();
        array_push($columnsArray, 'count(fg_cm_contact.id) as count');
        array_push($columnsArray, '`515`');
        $contactlistClass = new Contactlist($this->container, $this->contactId, $club, $contactType);
        $contactlistClass->setColumns($columnsArray);
        $contactlistClass->setFrom();
        $contactlistClass->setCondition();
        $contactlistClass->addCondition("(`" . $this->container->getParameter('system_field_primaryemail') . "` IS NOT NULL AND `" . $this->container->getParameter('system_field_primaryemail') . "` !='')");
        if ($manualDeletedContact != '') {
            $sWhere = "(mc.contact_id  IN($manualDeletedContact))";
            $contactlistClass->addCondition($sWhere);
        }
        $languagearrayQuery = $contactlistClass->getResult();
        $languagearrayQuery = $languagearrayQuery . " group by `515`";
        // $languageArrayResult = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($languagearrayQuery);
        $totalCount = 0;
        $contactlistClass->setColumns($aColumns);
        //call query for collect the data
        $listquery = $contactlistClass->getResult();
        file_put_contents("query.txt", $listquery . "\n");
        $contactlistDatas = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($listquery);
        //collect total number of records
        $output = array(
            "iTotalRecords" => $totalCount,
            "iTotalDisplayRecords" => $totalCount,
            "aaData" => array()
        );
        //iterate the result
        $contactDatatabledata = new Contactdatatable($this->container, $club);
        $output['aaData'] = $contactDatatabledata->iterateDataTableData($contactlistDatas, $specialFieldsArray, '');

        return new JsonResponse($output);
    }

    /**
     * Action to save Newletter wizard step 2.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse Array of saved status.
     */
    public function saveNewsletterStep2Action(Request $request)
    {
        $newsletterId = $request->get('newsletterId', '');
        if ($newsletterId != '') {
            $manualContacts = $request->get('send_message', array());
            $includedContacts = isset($manualContacts['user']['id']) ? $manualContacts['user']['id'] : array();
            $newsletterType = $request->get('newsletterType', 'MANDATORY');
            $dataArray = array(
                'recipientListId' => $request->get('recipient', ''),
                'includedContacts' => $includedContacts,
                'excludedData' => $request->get('removedRecipient', ''),
                'include_subscribers' => $request->get('additional_subscriber_flag', '0'),
                'include_formermembers' => $request->get('formermember_flag', '0'),
                'recieversCount' => $request->get('activerecipientCount', '0')
            );
            if ($newsletterType == 'MANDATORY') {
                $dataArray['mainEmails'] = $request->get('selected-email-fields', array());
                $dataArray['substituteEmail'] = $request->get('selected-subemail-fields', '');
            }
            $this->em->getRepository('CommonUtilityBundle:FgCnNewsletter')->saveNewsletterWizardStep2($newsletterId, $dataArray, $this->container, $this->contactId, $this->clubId, $this->clubDefaultSystemLang);
        }
        $showNext = $request->get('showNext', false);
        $mailType = $request->get('mailType', 'GENERAL');
        if ($showNext == 'true') {
            $redirectPath = ($mailType == 'GENERAL') ? $this->generateUrl('newsletter_step_content', array('newsletterId' => $newsletterId)) : $this->generateUrl('simplemail_step_content', array('newsletterId' => $newsletterId));
            $jsonResponse = array('status' => 'SUCCESS', 'redirect' => $redirectPath, 'sync' => 1, 'flash' => $this->get('translator')->trans('NEWSLETTER_WIZARD_STEP2_SAVED'));
        } else {
            $redirectPath = ($mailType == "GENERAL") ? $this->generateUrl('nl_newsletter_recepients', array('newsletterId' => $newsletterId)) : $this->generateUrl('nl_simplemail_recepients', array('newsletterId' => $newsletterId));
            $jsonResponse = array('status' => 'SUCCESS', 'redirect' => $redirectPath, 'sync' => 1, 'flash' => $this->get('translator')->trans('NEWSLETTER_WIZARD_STEP2_SAVED'));
        }

        return new JsonResponse($jsonResponse);
    }

    /**
     * For get the mandatory tab
     *
     * @return type
     */
    public function getMandatoryTabshowAction(Request $request)
    {
        $newsletterId = $request->get('newsletterId', '');
        $recipientId = $request->get('recipient', '');
        $mandatoryEmails = $request->get('selected-email-fields', array());
        $substituteEmail = $request->get('selected-subemail-fields', '');
        $manualselectedContact = $request->get('send_message', '');
        $manualSelectedId = '';
        if ($manualselectedContact != '') {
            $manualSelectedId = implode(",", $manualselectedContact['user']['id']);
        }
        if ($recipientId != '') {
            $this->em->getRepository('CommonUtilityBundle:FgCnRecepients')->updateRecipientContacts($this->container, $this->contactId, $recipientId, $this->clubDefaultSystemLang);
            $this->em->getRepository('CommonUtilityBundle:FgCnNewsletter')->updateNewsletterRecipientsCount($newsletterId, $this->clubId, $this->container, $this->contactId, null, $this->clubDefaultSystemLang);
        }
        $passedSettings = array('recipientId' => $recipientId, 'manual_contacts' => $manualSelectedId, 'mainEmails' => $mandatoryEmails, 'substituteEmail' => $substituteEmail, 'clubAndSubFedIds' => true);
        $recipientsData = $this->em->getRepository('CommonUtilityBundle:FgCnRecepients')->getNewsletterRecipients('mandatory', 'draft', $newsletterId, $this->clubId, $this->container, $this->contactId, false, true, $passedSettings, false, $this->clubDefaultSystemLang);
        $recipients = json_encode($recipientsData);

        $exceptionsData = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletterExcludeContacts')->getExceptionsOfNewsletter($newsletterId);
        $exceptions = json_encode($exceptionsData);

        //get club titles for displaying in listing
        $clubObj = new ClubPdo($this->container);
        $clubData = $clubObj->getAllSubLevelData($this->federationId);
        $clubs = json_encode(array_column($clubData, 'title', 'id'));

        return $this->render('ClubadminCommunicationBundle:Newsletterwizard:step2.mandatorytab.html.twig', array('newsletterId' => $newsletterId, 'recipientId' => $recipientId, 'manualSelectedContact' => $manualSelectedId, 'recipientData' => $recipients, 'exceptiondata' => $exceptions, 'clubData' => $clubs));
    }

    /**
     * Function for mandatory recipient list
     *
     * @param object $club
     *
     * @return array
     */
    private function mandatoryRecipientSetting($club)
    {
        $corrLangAttrId = $this->container->getParameter('system_field_corress_lang');
        $clubHeirarchy = $club->get('clubHeirarchy');
        $clubDetails = array('clubType' => $this->clubType, 'clubId' => $this->clubId, 'clubHeirarchy' => $clubHeirarchy, 'defaultLang' => $this->clubDefaultLang, 'defaultSystemLang' => $this->clubDefaultSystemLang, 'corrLangAttrId' => $corrLangAttrId, 'clubLanguages' => $this->clubLanguages);
        $emailFields = $this->em->getRepository('CommonUtilityBundle:FgCmAttribute')->getAllContactFields($clubDetails, false, array('email', 'login email'));

        return $emailFields;
    }

    /**
     * For selected email fields of newsletter
     *
     * @param int $newsletterId
     *
     * @return array
     */
    private function selectedEmailFields($newsletterId)
    {
        $selectedArrays = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletterManualContactsEmail')->getEmailSettingsofNewsletter($newsletterId);

        return $selectedArrays;
    }

    /**
     * For collect the additional newsletter details
     * 
     * @param Request $request The request object
     * 
     * @return JsonResponse
     */
    public function getAdditionalSubscriberAction(Request $request)
    {
        $newsletterId = $request->get('newsletterId', '');
        $subscribers = $this->em->getRepository('CommonUtilityBundle:FgCnSubscriber')->getAdditionalSubscribersList($newsletterId, $this->clubId, $this->clubDefaultSystemLang, $this->clubDefaultLang);
        $return['aaData'] = $subscribers;
        $return["iTotalRecords"] = (count($subscribers) > 0) ? count($subscribers) : 0;
        $return["iTotalDisplayRecords"] = (count($subscribers)) ? count($subscribers) : 0;

        return new JsonResponse($return);
    }
}
