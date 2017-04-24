<?php

/**
 * MailingsController
 *
 * This controller used for handling the mailings
 *
 * @package    ClubadminCommunicationBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
namespace Clubadmin\CommunicationBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Common\UtilityBundle\Util\FgUtility;
use Clubadmin\ContactBundle\Util\FgRecepientEmailValidator;
use Clubadmin\ContactBundle\Util\ContactDetailsSave;
use Clubadmin\CommunicationBundle\Util\FgSentNewsletterRecipients;
use Clubadmin\Util\Contactlist;
use Admin\UtilityBundle\Repository\Pdo\ClubPdo;

/**
 * Manage mailings of a newsletter
 */
class MailingsController extends FgController
{

    /**
     * Function to list the mailings
     *
     * @param string $type tab type
     * @param int    $id   newsletter id
     *
     * @return template
     */
    public function indexAction($type, $id)
    {
        return $this->render('ClubadminCommunicationBundle:Mailings:index.html.twig', array('tab' => $type, 'newsletterId' => $id));
    }

    /**
     * This action is used for showing the preview of simple mail and newsletter
     *
     * @param string $status       the status(drafts,planned,sending,sent)
     * @param int    $newsletterId the newsletter id
     *
     * @Template("ClubadminCommunicationBundle:Mailings:preview.html.twig")
     *
     * @return array
     */
    public function previewAction(Request $request, $status, $id)
    {
        $type = $request->get('level1');
        $valid = 1;
        $attachments = array();
        $newsletterDetails = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletter')->getPreviewDetails($type, $id, $this->clubId);
        $recType = ($newsletterDetails['publishType'] == 'SUBSCRIPTION') ? 'nonmandatory' : 'mandatory';
        if ($status == 'sent') {
            $recepientCount = $newsletterDetails['recepientsCount'];
        } else {
            $recepientCountArr = $this->em->getRepository('CommonUtilityBundle:FgCnRecepients')->getNewsletterRecipients($recType, $status, $id, $this->clubId, $this->container, $this->contactId, false, false, array('getCount' => true), false, $this->clubDefaultSystemLang, false, true);
            $recepientCount = $recepientCountArr[0]['recipientsCount'];
        }
        if ($type == "simplemail") {
            if ($newsletterDetails['newsletterType'] != 'EMAIL') {
                $valid = 0;
            }
        } else {
            if ($newsletterDetails['newsletterType'] != 'GENERAL') {
                $valid = 0;
            }
        }
        if (!$valid) {
            throw $this->createNotFoundException($this->clubTitle . ' have no access to this page');
        }
        if ($newsletterDetails['attachments']) {
            $attachments = explode(',', $newsletterDetails['attachments']);
        }
        $isAttached = count($attachments);
        if ($type == "simplemail") {
            $backLink = $this->generateUrl('newsletter_simplemailings');
        } else {
            $backLink = $this->generateUrl('newsletter_mailings');
        }
        $breadCrumb = array(
            'breadcrumb_data' => array(),
            'back' => $backLink
        );
        $pageArr = array();
        $nextPreviousPath = $nextPreviousType = '';
        if ($type == "simplemail") {
            $nextPreviousPath = 'mailings_simplemail_preview';
            $nextPreviousType = $this->clubId . '_SIMPLEMAIL_' . strtoupper($status);
        } else {
            $nextPreviousPath = 'mailings_newsletter_preview';
            $nextPreviousType = $this->clubId . '_NEWSLETTER_' . strtoupper($status);
        }
        $pageArr = $this->get('club')->getNextPrevious($id, $nextPreviousType, $nextPreviousPath, 'id', array('status' => $status));
        if (($pageArr['prev'] != '') || ($pageArr['next'] != '')) {
            $breadCrumb['prev'] = $pageArr['prev'];
            $breadCrumb['next'] = $pageArr['next'];
        }
        $tabs = array(
            0 => 'newsletter_preview',
            1 => 'recipients'
        );
        $tabsData = FgUtility::getTabsArrayDetails($this->container, $tabs, $status, $id, $recepientCount, 'newsletter_preview', $type);
        $results = array('type' => $type,
            'newsletterId' => $id,
            'newsletterDetails' => $newsletterDetails,
            'attachments' => $attachments,
            'clubId' => $this->clubId,
            'breadCrumb' => $breadCrumb,
            'status' => $status,
            'isAttached' => $isAttached,
            'bookedModules' => $this->bookedModulesDet,
            'recepientsCount' => $recepientCount,
            'tabs' => $tabsData
        );

        return $results;
    }

    /**
     * This action is used for showing the recepients of simple mail and newsletter
     *
     * @param string $status       the status(drafts,planned,sending,sent)
     * @param int    $newsletterId the newsletter id
     *
     * @Template("ClubadminCommunicationBundle:Mailings:recipients.html.twig")
     *
     * @return array
     */
    public function recipientsAction(Request $request, $status, $id)
    {
        $type = $request->get('level1');
        $newsletterDetails = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletter')->getRecepientDetails($type, $id, $this->clubId);
        $emailFields = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletter')->getEmailFieldsofRecepients($id, $this->clubId, $this->container);
        $recType = ($newsletterDetails['publishType'] == 'SUBSCRIPTION') ? 'nonmandatory' : 'mandatory';
        $langExists = 0;
        if (count($this->clubLanguages) > 1) {
            $langExists = 1;
        }
        $correLang = array();
        if ($langExists) {
            $clubLangs = explode(',', $newsletterDetails['language']);
            $correLang = FgUtility::getClubLanguageNames($clubLangs);
        }
        if (isset($correLang)) {
            $correLang = implode(', ', $correLang);
        }
        if ($type == "simplemail") {
            $backLink = $this->generateUrl('newsletter_simplemailings');
        } else {
            $backLink = $this->generateUrl('newsletter_mailings');
        }
        $breadCrumb = array(
            'breadcrumb_data' => array(),
            'back' => $backLink
        );
        $pageArr = array();
        $nextPreviousPath = $nextPreviousType = '';
        if ($type == "simplemail") {
            $nextPreviousPath = 'mailings_simplemail_recipients';
            $nextPreviousType = $this->clubId . '_SIMPLEMAIL_' . strtoupper($status);
        } else {
            $nextPreviousPath = 'mailings_newsletter_recipients';
            $nextPreviousType = $this->clubId . '_NEWSLETTER_' . strtoupper($status);
        }
        $pageArr = $this->get('club')->getNextPrevious($id, $nextPreviousType, $nextPreviousPath, 'id', array('status' => $status));
        if (($pageArr['prev'] != '') || ($pageArr['next'] != '')) {
            $breadCrumb['prev'] = $pageArr['prev'];
            $breadCrumb['next'] = $pageArr['next'];
        }
        $data[] = array('id' => 'email', 'data-edit-type' => 'text');
        $tabs = array(
            0 => 'newsletter_preview',
            1 => 'recipients'
        );
        if ($status == 'sent') {
            $recepientCount = $newsletterDetails['recepientsCount'];
        } else {
            $recepientCountArr = $this->em->getRepository('CommonUtilityBundle:FgCnRecepients')->getNewsletterRecipients($recType, $status, $id, $this->clubId, $this->container, $this->contactId, false, false, array('getCount' => true), false, $this->clubDefaultSystemLang, false, true);
            $recepientCount = $recepientCountArr[0]['recipientsCount'];
        }
        $tabsData = FgUtility::getTabsArrayDetails($this->container, $tabs, $status, $id, $recepientCount, 'recipients', $type);
        //get club titles for displaying in listing
        $clubObj = new ClubPdo($this->container);
        $clubData = $clubObj->getAllSubLevelData($this->federationId);
        $clubs = array_column($clubData, 'title', 'id');  
        $clubDatas = str_replace(array("'",'"'),array("&apos;","&quot;"),$clubs);  
        
        $results = array('type' => $type,
            'newsletterId' => $id,
            'newsletterDetails' => $newsletterDetails,
            'status' => $status,
            'langExists' => $langExists,
            'corresLang' => $correLang,
            'breadCrumb' => $breadCrumb,
            'inlineeditData' => $data,
            'recepientsCount' => $recepientCount,
            'emailFields' => $emailFields,
            'tabs' => $tabsData,
            'clubData' => $clubDatas); 

        return $results;
    }

    /**
     * Function to update the newsletter to publish on archive page
     *
     * @param \Symfony\Component\HttpFoundation\Request $request request parameter
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function updateNlPublishAction(Request $request)
    {
        $newsletterId = $request->get('id');
        $value = $request->get('is_publish');
        if ($newsletterId) {
            $fgCnNewsletter = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletter')->find($newsletterId);
            $fgCnNewsletter->setIsDisplayInArchive($value);
            $this->em->persist($fgCnNewsletter);
            $this->em->flush();

            $output['status'] = 'SUCCESS';
            $output['value'] = $value;
            $output['success_msg'] = $this->get('translator')->trans('MAILINGS_PUBLISH_SUCCESS');

            return new JsonResponse($output);
        }
    }

    /**
     * function to edit the bounced recepients
     *
     * @param int $newsletterId newsletter id
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function editBouncedRecepientsAction(Request $request, $newsletterId)
    {
        $rowId = $request->get('rowId');
        $emailNewVal = $request->get('value');
        $preEmailVal = $request->get('prevVal');
        if ($emailNewVal != $preEmailVal) {
            $this->em->getRepository('CommonUtilityBundle:FgCnNewsletterReceiverLog')->updateBouncedEmail($emailNewVal, $rowId);
        }

        return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->container->get('translator')->trans('RECEPIENTS_EDIT_SUCCESS')));
    }

    /**
     * function to do the resend in recepients tab of newsletter
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function recepientsResendAction(Request $request)
    {
        $newsletterId = $request->get('newsletterId');
        $type = $request->get('type');
        $status = $request->get('status');
        if ($newsletterId) {
            $this->em->getRepository('CommonUtilityBundle:FgCnNewsletter')->updateResendStatus($newsletterId);
            if ($type == "simplemail") {
                $redirect = $this->generateUrl('mailings_simplemail_recipients', array('status' => $status, 'id' => $newsletterId));
            } else {
                $redirect = $this->generateUrl('mailings_newsletter_recipients', array('status' => $status, 'id' => $newsletterId));
            }

            return new JsonResponse(array('status' => 'SUCCESS', 'sync' => 1, 'redirect' => $redirect, 'flash' => $this->get('translator')->trans('RECEPIENTS_RESEND_SUCCESS')));
        }
    }

    /**
     * function to get whether the newsletter has any bounced mails and total count
     *
     * @param string $type the newsletter type(simple mail or newsletter)
     * @param string $status the newsletter status(drafts,scheduled,sending and sent)
     * @param int $id the newsletter id
     * @param string $recType Newsletter publish type
     *
     * @return array
     */
    public function getRecepientsValues($type, $status, $id, $recType)
    {
        $recipients = $this->em->getRepository('CommonUtilityBundle:FgCnRecepients')->getNewsletterRecipients($recType, $status, $id, $this->clubId, $this->container, $this->contactId, false, false, array(), false, $this->clubDefaultSystemLang, true);
        $resultArr = array();
        $isBounced = false;
        $count = 0;
        foreach ($recipients as $key => $val) {
            if ($val['isBounce']) {
                $isBounced = true;
                $count++;
            }
        }
        $resultArr['isBounced'] = $isBounced;
        $resultArr['recepientCount'] = count($recipients);
        $resultArr['totalBounced'] = $count;

        return $resultArr;
    }

    /**
     * This action is to get reci[ient bounce message
     *
     * @param int $logId log id
     * @Template("ClubadminCommunicationBundle:Mailings:bouncemessage.html.twig")
     *
     * @return array
     */
    public function getRecepientsBounceMessageAction($logId)
    {
        $recepientsLogDetails = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletterReceiverLog')->getBounceMessages($logId);
        $bounceMessage = '';
        if ($recepientsLogDetails[0]['bounceMessage'] != "") {
            $bounceMessage = $recepientsLogDetails[0]['bounceMessage'];
        }
        $results = array(
            'messages' => nl2br($bounceMessage),
        );

        return $results;
    }

    /**
     * function to handle the switching for preview and receptions when it comes from mailings page
     *
     * @param int $newsletterId the newsletter id
     * @param string $status the status(draft,sending,scheduled and sent)
     * @param string $type the type (newsletter or simplemail)
     * @param string $flag the flag for preview or recepients
     *
     * @return array
     */
    public function handlePrevandNext($newsletterId, $status, $type, $flag)
    {
        $plannedArr = '';
        $sendingArr = '';
        $sentArr = '';
        $orderBy = 'sendDate';
        $orderAs = 'desc';
        $search = '';
        $prevLink = '';
        $nextLink = '';
        $newsletterIds = array();
        $prevNextArr = array();
        if ($status == 'scheduled') {
            $plannedCookieName = "communication_" . $this->clubId . "_" . $this->contactId . "_plannedsortorder";
            $plannedArr = explode(',', $_COOKIE[$plannedCookieName]);
            $headArray = array(0 => 'sendDate', 1 => 'updatedBy', 2 => 'subject', 3 => 'publishType', 4 => 'senderEmail', 5 => 'recepientCount');
            if ($plannedArr[0]) {
                $orderBy = $headArray[$plannedArr[0]];
            }
            if ($plannedArr[1]) {
                $orderAs = $plannedArr[1];
            }
        } else if ($status == 'sending') {
            $sendingCookieName = 'communication_' . $this->clubId . '_' . $this->contactId . '_sendingsortorder';
            $sendingArr = explode(',', $_COOKIE[$sendingCookieName]);
            $headArray = array(0 => 'sendDate', 1 => 'updatedBy', 2 => 'subject', 3 => 'publishType', 4 => 'senderEmail', 5 => 'recepientCount');
            if ($sendingArr[0]) {
                $orderBy = $headArray[$sendingArr[0]];
            }
            if ($sendingArr[1]) {
                $orderAs = $sendingArr[1];
            }
        } else if ($status == 'sent') {
            $sentCookieName = 'communication_' . $this->clubId . '_' . $this->contactId . '_sentsortorder';
            $sentArr = explode(',', $_COOKIE[$sentCookieName]);
            $headArray = array(0 => 'sendDate', 1 => 'updatedBy', 2 => 'subject', 3 => 'publishType', 4 => 'senderEmail', 5 => 'recepientCount', 6 => 'openedAt');
            if ($sentArr[0]) {
                $orderBy = $headArray[$sentArr[0]];
            }
            if ($sentArr[1]) {
                $orderAs = $sentArr[1];
            }
            if ($sentArr[2]) {
                $search = $sentArr[2];
            }
        }
        //to get newsletter ids based on sorting
        $newsletterIds = $this->getNlDetailsForPrevandNext($this->clubId, $status, $orderBy, $orderAs, $type, $search);
        //to get previous and next links based on current id and all ids
        $prevNextArr = $this->getPreviousAndNextLinks($newsletterId, $newsletterIds, $status);
        if ($type == "simplemail") {
            if ($flag == 'preview') {
                $prevLink = $prevNextArr['prevNlId'] ? $this->generateUrl('mailings_simplemail_preview', array('status' => $status, 'id' => $prevNextArr['prevNlId'])) : '#';
                $nextLink = $prevNextArr['nextNlId'] ? $this->generateUrl('mailings_simplemail_preview', array('status' => $status, 'id' => $prevNextArr['nextNlId'])) : '#';
            } else {
                $prevLink = $prevNextArr['prevNlId'] ? $this->generateUrl('mailings_simplemail_recipients', array('status' => $status, 'id' => $prevNextArr['prevNlId'])) : '#';
                $nextLink = $prevNextArr['nextNlId'] ? $this->generateUrl('mailings_simplemail_recipients', array('status' => $status, 'id' => $prevNextArr['nextNlId'])) : '#';
            }
        } else {
            if ($flag == 'preview') {
                $prevLink = $prevNextArr['prevNlId'] ? $this->generateUrl('mailings_newsletter_preview', array('status' => $status, 'id' => $prevNextArr['prevNlId'])) : '#';
                $nextLink = $prevNextArr['nextNlId'] ? $this->generateUrl('mailings_newsletter_preview', array('status' => $status, 'id' => $prevNextArr['nextNlId'])) : '#';
            } else {
                $prevLink = $prevNextArr['prevNlId'] ? $this->generateUrl('mailings_newsletter_recipients', array('status' => $status, 'id' => $prevNextArr['prevNlId'])) : '#';
                $nextLink = $prevNextArr['nextNlId'] ? $this->generateUrl('mailings_newsletter_recipients', array('status' => $status, 'id' => $prevNextArr['nextNlId'])) : '#';
            }
        }
        $returnArr['prev'] = $prevLink;
        $returnArr['next'] = $nextLink;

        return $returnArr;
    }

    /**
     * function to get the newsletter ids baesd on sorting
     *
     * @param int $clubId teh club id
     * @param string $status the status
     * @param string $orderBy the order by
     * @param string $orderAs the order as
     * @param string $type the newsletter type
     * @param string $search the search string
     *
     * @return array of newsletter ids
     */
    private function getNlDetailsForPrevandNext($clubId, $status, $orderBy, $orderAs, $type, $search)
    {
        $newsletterIds = array();
        $results = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletter')->getNewslettersNextPrev($this->clubId, $status, $orderBy, $orderAs, $type, $search);
        foreach ($results as $key => $val) {
            $newsletterIds[] = $val['id'];
        }

        return $newsletterIds;
    }

    /**
     * function to get previous and next newsletter ids
     *
     * @param int $newsletterId the newsletter id
     * @param array $newsletterIds the array of newsletter ids
     * @param string $status the sttaus
     *
     * @return array of previous next ids
     */
    private function getPreviousAndNextLinks($newsletterId, $newsletterIds, $status)
    {
        $returnArr = array();
        if (in_array($newsletterId, $newsletterIds)) {
            $currentNlId = current($newsletterIds);
            if ($currentNlId == $newsletterId) {
                $prevNlId = '';
                $nextNlId = next($newsletterIds);
            } else {
                while (($nextNlId = next($newsletterIds)) !== null) {
                    if ($nextNlId == $newsletterId) {
                        break;
                    }
                }
                $prevNlId = prev($newsletterIds);
                $currentNlId = next($newsletterIds);
                $nextNlId = next($newsletterIds);
            }
        }
        $returnArr['prevNlId'] = $prevNlId;
        $returnArr['nextNlId'] = $nextNlId;

        return $returnArr;
    }

    /**
     * Function to check whether primary email exists for other contacts.
     *
     * @return JsonResponse Status array
     */
    public function checkPrimaryEmailExistsAction(Request $request)
    {
        $rowId = $request->get('rowId');
        $emailNewVal = $request->get('value');
        $preEmailVal = $request->get('prevVal');
        $contactId = $request->get('contactId');
        $emailFieldIds = $request->get('emailIds');
        $emailIdsArray = explode(',', $emailFieldIds);
        $output = array();
        $confirmType = '';
        if ($emailNewVal != $preEmailVal) {
            $validatorObj = new FgRecepientEmailValidator($this->container, $emailNewVal);
            $output = $validatorObj->isValidEmail();
            if ($output['valid'] == 'true') {
                //Check email field is editable in current club level
                $arrayValCounts = array_count_values($emailIdsArray);
                $contactFields = $this->container->get('club')->get('allContactFields');
                foreach ($emailIdsArray as $emailId) {
                    if ($contactFields[$emailId]['is_editable'] == 0) {
                        $confirmType = 'EmailNotEditable';
                        break;
                    }
                }
                //Check parent email is there
                if ($confirmType == '') {
                    $logResult = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletterReceiverLog')->getBounceMessages($rowId);
                    $linkedContactId = $logResult[0]['linkedContactIds'];
                    if ($linkedContactId != '') {
                        $confirmType = 'EmailExists';
                    }
                }
                // Check whether primary email exists.
                if ($confirmType == '' && in_array('3', $emailIdsArray) && ($arrayValCounts['3'] == 1) && $linkedContactId == '') {
                    $primaryEmail = $this->container->getParameter('system_field_primaryemail');
                    $contactObj = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->find($contactId);
                    $hasFedMembership = 0;
                    if ($contactObj) {
                        $hasFedMembership = ($contactObj->getFedMembershipCat()) ? 1 : 0;
                    }
                    $result = $this->em->getRepository('CommonUtilityBundle:FgCmAttribute')->searchEmailExistAndIsMergable($this->container, $contactId, $emailNewVal, $hasFedMembership, 0, 'contact');
                    $confirmType = (count($result) > 0 ? 'EmailExists' : '');
                }
                $return = array('confirmType' => $confirmType, 'rowId' => $rowId, 'value' => $emailNewVal, 'prevVal' => $preEmailVal, 'contactId' => $contactId, 'emailIds' => $emailFieldIds);
                $output = array_merge($output, $return);
            }
        }

        return new JsonResponse($output);
    }

    /**
     * Function to update primary email of contact.
     *
     * @param int $newsletterId Newsletter id
     *
     * @return JsonResponse Save status
     */
    public function updatePrimaryEmailAction(Request $request, $newsletterId)
    {
        $rowId = $request->get('rowId');
        $emailNewVal = $request->get('value');
        $preEmailVal = $request->get('prevVal');
        $contactId = $request->get('contactId');
        $emailFieldIds = $request->get('emailIds');
        $contactIdsArray = explode(',', $contactId);
        $emailIdsArray = explode(',', $emailFieldIds);
        if ($emailNewVal != $preEmailVal) {
            // Get club contact fields.
            $clubIdArray = $this->getClubArray();
            $fieldDetails1 = $this->em->getRepository('CommonUtilityBundle:FgCmAttributeset')->getAllClubContactFields($clubIdArray, $this->conn, 0, 0);
            $fieldDetails = $this->em->getRepository('CommonUtilityBundle:FgCmAttributeset')->fieldItrator($fieldDetails1);
            $clubContFields = $this->get('club')->get('allContactFields');
            // Save email fields of contact.
            foreach ($contactIdsArray as $key => $contactId) {
                $attributeId = $emailIdsArray[$key];
                if (isset($clubContFields[$attributeId])) {
                    $attrCatId = $fieldDetails['attrCatIds'][$attributeId];
                    $formValues = array($attrCatId => array($attributeId => $emailNewVal));
                    $contactData = $this->tempContact($contactId, $this->conn);
                    $contactSave = new ContactDetailsSave($this->container, $fieldDetails, $contactData, $contactId); //$container,$fieldDetails,$editData,$fedMemberships
                    $contactSave->saveContact($formValues, array(), array(), array(), 1);
                    // $this->em->getRepository('CommonUtilityBundle:FgCmAttribute')->saveContact($this->container, $this->clubDefaultSystemLang, $contactId, $fieldDetails, $this->get('club'), $formValues, array(), array(), $contactData, $this->contactId, array(), array(), 1);
                }
            }
            $this->em->getRepository('CommonUtilityBundle:FgCnNewsletterReceiverLog')->updateBouncedEmail($emailNewVal, $rowId);
        }

        return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->container->get('translator')->trans('CONTACT_EMAIL_EDIT_SUCCESS')));
    }

    /**
     * get club details array
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
            'invoiceCategory' => $container->get('system_category_invoice'));
        $clubIdArray['address'] = $this->get('translator')->trans('CONTACT_FIELD_ADDRESS');
        $clubIdArray['sysLang'] = $this->clubDefaultLang;
        $clubIdArray['defSysLang'] = $this->clubDefaultSystemLang;
        $clubIdArray['clubLanguages'] = $this->clubLanguages;

        return $clubIdArray;
    }

    /**
     * Function used to get contact data.
     *
     * @param int    $contactId Connection
     * @param object $conn      Connection
     *
     * @return array $fieldsArray Result array
     */
    private function tempContact($contactId, $conn)
    {
        if (!is_numeric($contactId)) {
            return false;
        }
        $club = $this->get('club');
        $contactlistClass = new Contactlist($this->container, $contactId, $club, 'editable');
        $contactlistClass->setColumns('*');
        $contactlistClass->setFrom('*');
        $contactlistClass->setCondition();
        $sWhere = " fg_cm_contact.id=$contactId";
        $contactlistClass->addCondition($sWhere);
        $listquery = $contactlistClass->getResult();
        $fieldsArray = $conn->fetchAll($listquery);

        return $fieldsArray;
    }

    /**
     * This function is used to get the receivers of newsletter
     *
     * @param object $request Request object
     *
     * @return object JSON Response Object
     */
    public function getNewsletterReceiversAction(Request $request)
    {
        $newsletterId = $request->get('newsletterId');
        $status = $request->get('status');
        $publishType = ($request->get('publishType') == 'SUBSCRIPTION') ? 'nonmandatory' : 'mandatory';
        $start = ($request->get('start')) ? $request->get('start') : 0;
        $length = 20;
        $limit = $start . ',' . ($start + $length);
        $orderByDir = 'DESC';
        if ($request->get('order')) {
            $sortDetails = $request->get('order');
            $orderByDir = ($sortDetails[0]['dir'] == 'asc') ? 'ASC' : 'DESC';
        }
        if ($status == 'sent') {
            $columns = array('is_bounced', 'email', 'email_field_ids', 'contact_id', 'salutation', 'opened_at', 'is_bounced', 'is_bounced');
            $orderByColumn = ($request->get('order')) ? $columns[$sortDetails[0]['column']] : 'is_bounced';
            $orderBy = $orderByColumn . " " . $orderByDir;

            $recipientsObj = new FgSentNewsletterRecipients($this->container);
            $data = $recipientsObj->getRecipientsList($newsletterId, $orderBy, $limit);
            $totalrecords = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletterReceiverLog')->getNewsletterReceiversCount($newsletterId);
        } else {
            $columns = array('isBounce', 'email', 'emailField', 'contactNames', 'salutation', 'contactClub', 'contactSubFederation');
            $orderByColumn = ($request->get('order')) ? $columns[$sortDetails[0]['column']] : 'isBounce';
            $orderBy = " ORDER BY " . $orderByColumn . " " . $orderByDir . " LIMIT " . $limit;
            $data = $this->em->getRepository('CommonUtilityBundle:FgCnRecepients')->getNewsletterRecipients($publishType, $status, $newsletterId, $this->clubId, $this->container, $this->contactId, false, false, array('orderByLimit' => $orderBy, 'clubAndSubFedIds' => true), false, $this->clubDefaultSystemLang, false, true);
            $totalrecordsArr = $this->em->getRepository('CommonUtilityBundle:FgCnRecepients')->getNewsletterRecipients($publishType, $status, $newsletterId, $this->clubId, $this->container, $this->contactId, false, false, array('orderByLimit' => $orderBy, 'getCount' => true), false, $this->clubDefaultSystemLang, false, true);
            $totalrecords = $totalrecordsArr[0]['recipientsCount'];
        }

        //Set the datatable json array
        $output = array('iTotalRecords' => $totalrecords, 'iTotalDisplayRecords' => $totalrecords, 'aaData' => array());
        $output['aaData'] = $data;

        return new JsonResponse($output);
    }
}
