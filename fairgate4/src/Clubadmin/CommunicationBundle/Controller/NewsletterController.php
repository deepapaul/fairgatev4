<?php

namespace Clubadmin\CommunicationBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Common\UtilityBundle\Entity\FgCnNewsletter;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\HttpFoundation\Request;

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
class NewsletterController extends FgController
{

    /**
     * This action is used for create newsletter.
     *
     * @Template("ClubadminCommunicationBundle:Newsletterwizard:step1_general.html.twig")
     *
     * @return array Data array.
     */
    public function indexAction(Request $request)
    {
        $backLink = $this->generateUrl('newsletter_mailings');
        $breadCrumb = array(
            'breadcrumb_data' => array(),
            'back' => $backLink
        );
        $contactId = $this->contactId;
        $contactPrimaryEmail = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getContactPrimaryEmail($contactId);
        $contactName = $this->contactNameNoSort;
        $club = $this->get('club');
        $bookedModuleDetails = $club->get('bookedModulesDet');
        $pagetype = $request->get('level1');
        $newsletterId = $request->get('id', '0');
        $accessCheckArray = array('from' => 'newsletter', 'type' => 'newsletter', 'newsletterId' => $newsletterId);

        $templateList = array();
        $listTemplateDetails = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletterTemplate')->getTemplateList($this->clubId, 0, 50, 'title', 'asc', '', true);
        foreach ($listTemplateDetails as $key => $listTemplate) {
            $templateList[$key] = array('title' => $listTemplate[1], 'id' => $listTemplate[0]);
        }
        $step = 1;
        $clubId = $this->clubId;
        if ($newsletterId > 0) {
            $editdetails = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletter')->editnewsletterdetails($newsletterId, $clubId);
            $return['contactname'] = $editdetails['senderName'];
            $return['Email'] = $editdetails['senderEmail'];
            $wizardStep = $editdetails['step'];

            $isAccess = count($editdetails) > 0 ? 1 : 0;
            $accessCheckArray = array('from' => 'newsletter', 'isAccess' => $isAccess);
            $this->fgpermission->checkAreaAccess($accessCheckArray);
        } else {
            $editdetails = array();
            $wizardStep = 0;
            $return['contactname'] = $contactName;
            $return['Email'] = $contactPrimaryEmail;
        }
        $fieldLanguages = array();
        $languages = Intl::getLanguageBundle()->getLanguageNames();
        foreach ($this->clubLanguages as $shortName) {
            $fieldLanguages[$shortName] = $languages[$shortName];
        }

        $return['languages'] = $fieldLanguages;
        if (count($this->clubLanguages) == 1) {
            $lang = $this->clubLanguages;
            $singleLang = $lang[0];
            $return['singleLang'] = $singleLang;
        } else {
            $return['singleLang'] = '';
        }
        $return['langData'] = $this->clubLanguages;
        $return['totallanguageCount'] = count($this->clubLanguages);
        $return['type'] = "GENERAL";
        $return['templateList'] = $templateList;

        return array('breadCrumb' => $breadCrumb, 'dataArray' => $return, 'editData' => $editdetails, 'newsletterId' => $newsletterId, 'step' => $step, 'pageType' => $pagetype, 'bookedModule' => $bookedModuleDetails, 'wizardStep' => $wizardStep);
    }

    /**
     * This action is used for create simple mail.
     *
     * @Template("ClubadminCommunicationBundle:Newsletterwizard:step1_general.html.twig")
     *
     * @return array Data array.
     */
    public function mailIndexAction(Request $request)
    {
        $backLink = $this->generateUrl('newsletter_simplemailings');
        $breadCrumb = array(
            'breadcrumb_data' => array(),
            'back' => $backLink
        );
        $newsletterId = $request->get('id', '0');
        $club = $this->get('club');
        $bookedModuleDetails = $club->get('bookedModulesDet');
        $contactName = $this->contactName;
        $contactId = $this->contactId;
        $contactPrimaryEmail = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getContactPrimaryEmail($contactId);
        $pagetype = $request->get('level1');
        $step = 1;
        $clubId = $this->clubId;
        $fieldLanguages = array();
        if ($newsletterId > 0) {
            $editdetails = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletter')->editnewsletterdetails($newsletterId, $clubId);
            $wizardStep = $editdetails['step'];
            $isAccess = count($editdetails) > 0 ? 1 : 0;
            $accessCheckArray = array('from' => 'newsletter', 'isAccess' => $isAccess);
            $allowedTabs = $this->fgpermission->checkAreaAccess($accessCheckArray);
        } else {
            $editdetails = array();
            $wizardStep = 0;
        }
        $languages = Intl::getLanguageBundle()->getLanguageNames();
        foreach ($this->clubLanguages as $shortName) {
            $fieldLanguages[$shortName] = $languages[$shortName];
        }
        $return['Email'] = $contactPrimaryEmail;
        $return['languages'] = $fieldLanguages;
        if (count($this->clubLanguages) == 1) {
            $lang = $this->clubLanguages;
            $singleLang = $lang[0];
            $return['singleLang'] = $singleLang;
        } else {
            $return['singleLang'] = '';
        }
        $return['totallanguageCount'] = count($this->clubLanguages);
        $return['contactname'] = $contactName;
        $return['type'] = "EMAIL";


        return array('breadCrumb' => $breadCrumb, 'dataArray' => $return, 'editData' => $editdetails, 'newsletterId' => $newsletterId, 'step' => $step, 'pageType' => $pagetype, 'bookedModule' => $bookedModuleDetails, 'wizardStep' => $wizardStep);
    }

    /**
     * This action is used for Mailings
     *
     * @Template("ClubadminCommunicationBundle:Newsletter:createNewsletter.html.twig")
     *
     * @return array Data array.
     */
    public function mailingAction()
    {

        $breadCrumb = array(
            'breadcrumb_data' => array()
        );
        $delete = $this->get('translator')->trans('NL_DELETE');
        $move = $this->get('translator')->trans('NL_MOVE');
        $duplicate = $this->get('translator')->trans('NL_DUPLICATE');

        return $this->render('ClubadminCommunicationBundle:Newsletter:mailingsList.html.twig', array('breadCrumb' => $breadCrumb, 'totalTemplates' => 0, 'delete' => $delete, 'duplicate' => $duplicate, 'move' => $move, 'contact' => $this->contactId, 'clubId' => $this->clubId));
    }

    /**
     * Function is used for setting listing templates parameters in datatable
     *
     * @param String $mail Email
     * @param String $type Type
     *
     * @return Response
     */
    public function getNewsletterTypesAction(Request $request, $mail, $type)
    {
        $start = $request->get('start', 0);
        $length = $request->get('length', 50);
        $result = array();

        $deletePath = $this->generateUrl('delete_draft_newsletter', array('type' => 'type', 'newsletterId' => '_ID_'));
        $moveDraftPath = $this->generateUrl('move_draft_newsletter', array('type' => 'type', 'newsletterId' => '_ID_'));
        $duplicatePath = $this->generateUrl('duplicate_newsletter', array('type' => 'type', 'newsletterId' => '_ID_'));

        if ($mail == 'GENERAL') {
            $mailingsPreview = $this->generateUrl('mailings_newsletter_preview', array('status' => 'status', 'id' => '_ID_'));
            $mailingsRecipients = $this->generateUrl('mailings_newsletter_recipients', array('status' => 'status', 'id' => '_ID_'));
            $mailingsEdit = $this->generateUrl('edit_newsletter', array('id' => '_ID_'));
        } else {
            $mailingsPreview = $this->generateUrl('mailings_simplemail_preview', array('status' => 'status', 'id' => '_ID_'));
            $mailingsRecipients = $this->generateUrl('mailings_simplemail_recipients', array('status' => 'status', 'id' => '_ID_'));
            $mailingsEdit = $this->generateUrl('edit_simplemail', array('id' => '_ID_'));
        }

        switch ($type) {
            case 'draft':
                $result = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletter')->getNewslettersList($this->container, $this->clubId, $type, $start, 50, $mail, $deletePath, $moveDraftPath, $duplicatePath, $mailingsPreview, $mailingsRecipients, $mailingsEdit);
                break;
            case 'scheduled':
                $result = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletter')->getNewslettersList($this->container, $this->clubId, $type, $start, 50, $mail, $deletePath, $moveDraftPath, $duplicatePath, $mailingsPreview, $mailingsRecipients, $mailingsEdit);
                break;
            case 'sending':
                $result = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletter')->getNewslettersList($this->container, $this->clubId, $type, $start, 50, $mail, $deletePath, $moveDraftPath, $duplicatePath, $mailingsPreview, $mailingsRecipients, $mailingsEdit);
                break;
            case 'sent':
                $result = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletter')->getNewslettersList($this->container, $this->clubId, $type, $start, $length, $mail, $deletePath, $moveDraftPath, $duplicatePath, $mailingsPreview, $mailingsRecipients, $mailingsEdit);
                break;
            default:
                break;
        }
        $count = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletter')->getMailingsNewsletterCount($this->clubId, $type, $mail);

        $return['aaData'] = $result ? $result : array();
        $return["iTotalRecords"] = $count ? $count : 0;
        $return["iTotalDisplayRecords"] = $count ? $count : 0;

        return new JsonResponse($return);
    }

    /**
     * This action is used for Mailings
     *
     * @return array Data array.
     */
    public function simpleMailingAction()
    {
        $breadCrumb = array(
            'breadcrumb_data' => array()
        );
        $delete = $this->get('translator')->trans('SM_DELETE');
        $move = $this->get('translator')->trans('SM_MOVE');
        $duplicate = $this->get('translator')->trans('SM_DUPLICATE');

        return $this->render('ClubadminCommunicationBundle:Newsletter:simpleMailingsList.html.twig', array('breadCrumb' => $breadCrumb, 'delete' => $delete, 'duplicate' => $duplicate, 'move' => $move, 'contact' => $this->contactId, 'clubId' => $this->clubId));
    }

    /**
     * Template to delete draft newsletter
     *
     * @param String $type         type
     * @param String $newsletterId newsletter id
     *
     * @return template
     */
    public function deleteNewsletterDraftAction(Request $request, $type, $newsletterId)
    {
        //$actionType = $request->get('actionType');
        if ($type == 'newsletter') {
            $newsletterDesc = 'NL_CONFIRM_DELETE_DESC';
            $newsletterTitle = 'NL_CONFIRM_DELETE_TITLE';
        }
        if ($type == 'simplemail') {
            $newsletterDesc = 'DRAFT_MAIL_CONFIRM_DELETE_DESC';
            $newsletterTitle = 'DRAFT_MAIL_CONFIRM_DELETE_TITLE';
        }
        $selActionType = $request->get('selActionType') ? $request->get('selActionType') : '';
        $return = array('actionType' => $type, 'newsletterId' => $newsletterId, 'clubId' => $this->clubId, 'selActionType' => $selActionType, 'deleteDesc' => $this->get('translator')->trans($newsletterDesc), 'deleteTitle' => $this->get('translator')->trans($newsletterTitle));

        return $this->render('ClubadminCommunicationBundle:Newsletter:confirmDelete.html.twig', $return);
    }

    /**
     * Delete confirmation
     *
     * @return template
     */
    public function confirmDeleteDraftNewsletterAction(Request $request)
    {
        $selectedId = json_decode($request->get('selectedId', '0'));
        $actionType = $request->get('actionType', '');
        if ($request->getMethod() == 'POST') {
            $flashMsg = '';
            if (count($selectedId) > 0) {
                if ($actionType == 'newsletter') {
                    $type = "GENERAL";
                    $flashMsg = 'NEWSLETTER_DRAFT_DELETED_SUCCESSFULLY';
                } else {
                    $type = "EMAIL";
                    $flashMsg = 'SIMPLEMAIL_DRAFT_DELETED_SUCCESSFULLY';
                }
                $this->em->getRepository('CommonUtilityBundle:FgCnNewsletter')->deleteNewsletterSimpleMailDraft($selectedId, $this->clubId, $type, $this->container);
                return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans($flashMsg), 'noparentload' => 1));
            }
        }
    }

    /**
     * function to move simle mail and newslwtter to draft based on type passed
     *
     * @param type $type         type
     * @param type $newsletterId newsletter id
     *
     * @return type template
     */
    public function moveScheduledNewsletterToDraftAction($type, $newsletterId)
    {
        if ($type == 'newsletter') {
            $newsletterDesc = 'NL_CONFIRM_MOVE_DESC';
            $newsletterTitle = 'NL_CONFIRM_MOVE_TITLE';
        }
        if ($type == 'simplemail') {
            $newsletterDesc = 'SM_CONFIRM_MOVE_DESC';
            $newsletterTitle = 'SM_CONFIRM_MOVE_TITLE';
        }
        $return = array('actionType' => $type, 'newsletterId' => $newsletterId, 'clubId' => $this->clubId, 'moveDesc' => $this->get('translator')->trans($newsletterDesc), 'moveTitle' => $this->get('translator')->trans($newsletterTitle));

        return $this->render('ClubadminCommunicationBundle:Newsletter:confirmMove.html.twig', $return);
    }

    /**
     * confirm move to draft
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function confirmMoveToDraftAction(Request $request)
    {
        $selectedId = json_decode($request->get('selectedId', '0'));
        $actionType = $request->get('actionType', '');
        if ($request->getMethod() == 'POST') {
            $flashMsg = '';
            if (count($selectedId) > 0) {
                if ($actionType == 'newsletter') {
                    $type = "GENERAL";
                    $flashMsg = 'NEWSLETTER_DRAFT_MOVED_SUCCESSFULLY';
                } else {
                    $type = "EMAIL";
                    $flashMsg = 'SIMPLE_MAIL_DRAFT_MOVED_SUCCESSFULLY';
                }
                $this->em->getRepository('CommonUtilityBundle:FgCnNewsletter')->moveScheduledToDraft($selectedId, $this->clubId, $type, $this->contactId);
                return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans($flashMsg), 'noparentload' => 1));
            }
        }
    }

    /**
     * function to move simle mail and newslwtter to draft based on type passed
     *
     * @param string $type         type
     * @param int    $newsletterId newsletter id
     *
     * @return type template
     */
    public function duplicateNewsletterAction($type, $newsletterId)
    {

        if ($type == 'newsletter') {
            $newsletterDesc = 'NL_DUPLICATE_DUPLICATE_DESC';
            $newsletterTitle = 'NL_CONFIRM_DUPLICATE_TITLE';
        }
        if ($type == 'simplemail') {
            $newsletterDesc = 'SM_CONFIRM_DUPLICATE_DESC';
            $newsletterTitle = 'SM_CONFIRM_DUPLICATE_TITLE';
        }

        $return = array('actionType' => $type, 'newsletterId' => $newsletterId, 'clubId' => $this->clubId, 'moveDesc' => $this->get('translator')->trans($newsletterDesc), 'moveTitle' => $this->get('translator')->trans($newsletterTitle));

        return $this->render('ClubadminCommunicationBundle:Newsletter:confirmDuplicate.html.twig', $return);
    }

    /**
     * function to confirm duplicate newsletter
     *
     * @return Jsonresponse
     */
    public function confirmDuplicateNewsletterAction(Request $request)
    {
        $newsletterId = json_decode($request->get('selectedId', '0'));
        $actionType = $request->get('actionType');
        $type = ($actionType == 'newsletter') ? "GENERAL" : "EMAIL";
        $flashMsg = ($actionType == 'newsletter') ? "NL_DUPLICATION_SUCCESSFUL" : "SM_DUPLICATION_SUCCESSFUL";
        $this->em->getRepository('CommonUtilityBundle:FgCnNewsletter')->duplicate($newsletterId, $type, $this->contactId, $this->clubId, $this->container);


        return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans($flashMsg), 'noparentload' => 1));
    }

    /**
     * Function to save step 1 of newsletter and simple mail
     *
     * @return Json Response
     */
    public function generalSaveAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $newsletterId = $request->request->get('newsletterId');
            $langCount = $request->request->get('langCount');
            if ($langCount > 1) {
                $languages = $request->request->get('language');
                $languageSelection = in_array('selectall', $languages) ? 'ALL' : 'SELECTED';

                if (in_array('selectall', $languages)) {
                    unset($languages[0]);
                }
            } else {
                $languages = array($request->request->get('singleLang'));
                $languageSelection = 'ALL';
            }
            $showNext = $request->request->get('showNext');
            $subject = $request->request->get('subject');
            $senderName = $request->request->get('username');
            $senderEmail = $request->request->get('email');
            $salutationType = $request->request->get('salutationType');
            $newsletterType = $request->request->get('type');
            $templateId = ($newsletterType == "GENERAL") ? $request->request->get('templateId') : 0;
            $salutation = ($salutationType == "SAME") ? $request->request->get('salutation') : '';
            $publishType = ($newsletterType == "GENERAL") ? $request->request->get('publishType') : "MANDATORY";
            if ($newsletterId != 0) {
                $newsletter = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletter')->find($newsletterId);
            } else {
                $newsletter = new FgCnNewsletter();
            }
            $formData = array('subject' => $subject, 'senderName' => $senderName, 'Email' => $senderEmail, 'salutationType' => $salutationType, 'salutation' => $salutation, 'newsletterType' => $newsletterType, 'publishType' => $publishType, 'languageSelected' => $languageSelection, 'templateId' => $templateId);
            $newsletterId = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletter')->generalStepSave($newsletter, $newsletterId, $formData, $this->clubId, $this->contactId, $languages);
            $redirectUrl = ($newsletterType == "GENERAL") ? $this->generateUrl('nl_newsletter_recepients', array('newsletterId' => $newsletterId)) : $this->generateUrl('nl_simplemail_recepients', array('newsletterId' => $newsletterId));

            if ($showNext == "true") {
                return new JsonResponse(array('status' => 'SUCCESS', 'sync' => '1', 'redirect' => $redirectUrl, 'newsletterId' => $newsletterId));
            }
        }
    }

    /**
     * This action is used for create newsletter ste5.
     *
     * @param int $newsletterId newsletter id
     *
     * @Template("ClubadminCommunicationBundle:Newsletterwizard:newsletterDesign.html.twig")
     *
     * @return array Data array.
     */
    public function templateNlDesignAction(Request $request, $newsletterId)
    {
        $newsletterId = $request->get('newsletterId');
        $type = $request->get('level1');
        $objNewsletter = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletter')->find($newsletterId);
        $ntype = $objNewsletter->getNewsletterType();
        $clubId = $objNewsletter->getClub()->getId();
        $templateId = $objNewsletter->getTemplate()->getId();
        $breadcrumb = array('back' => $this->generateUrl('newsletter_mailings'));
        if (($clubId != $this->clubId) || ($type != "newsletter") || ($ntype != "GENERAL")) {
            throw $this->createNotFoundException($this->clubTitle . ' has no access to this page');
        }
        $wizardStep = $objNewsletter->getStep();
        $club = $this->get('club');
        $bookedModuleDetails = $club->get('bookedModulesDet');
        if (in_array('sponsor', $bookedModuleDetails)) {
            //$backPath = $this->generateUrl('nl_step_sidebar', array('newsletterId' => $newsletterId));
            //temporary avoiding step 4
            $backPath = $this->generateUrl('newsletter_step_content', array('newsletterId' => $newsletterId));
        } else {
            $backPath = $this->generateUrl('newsletter_step_content', array('newsletterId' => $newsletterId));
        }
        //temporary avoiding step 4
        return array('step' => 4, 'newsletterId' => $newsletterId, 'pageType' => $type, 'backUrl' => $backPath,
            'bookedModule' => $bookedModuleDetails, 'breadCrumb' => $breadcrumb, 'wizardStep' => $wizardStep, 'templateId' => $templateId);
    }

    /**
     * This action is used for create simplemail step5.
     *
     * @param int $newsletterId newsletter id
     *
     * @Template("ClubadminCommunicationBundle:Newsletterwizard:newsletterDesign.html.twig")
     *
     * @return array Data array.
     */
    public function templateSmDesignAction(Request $request, $newsletterId)
    {
        $newsletterId = $request->get('newsletterId');
        $type = $request->get('level1');
        $objNewsletter = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletter')->find($newsletterId);
        $clubId = $objNewsletter->getClub()->getId();
        $ntype = $objNewsletter->getNewsletterType();
        $breadcrumb = array('back' => $this->generateUrl('newsletter_simplemailings'));
        if (($clubId != $this->clubId) || ($type != "simplemail") || ($ntype != "EMAIL")) {
            throw $this->createNotFoundException($this->clubTitle . ' has no access to this page');
        }
        $wizardStep = $objNewsletter->getStep();
        $club = $this->get('club');
        $backPath = $this->generateUrl('simplemail_step_content', array('newsletterId' => $newsletterId));
        $bookedModuleDetails = $club->get('bookedModulesDet');
        return array('step' => 5, 'newsletterId' => $newsletterId, 'pageType' => $type,
            'bookedModule' => $bookedModuleDetails, 'breadCrumb' => $breadcrumb, 'backUrl' => $backPath, 'wizardStep' => $wizardStep);
    }

    /**
     * To bulid underscore template selection
     *
     * @param int    $newsletterId newsletter id
     * @param string $pageType     type
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function templateSelectionAction($newsletterId, $pageType)
    {
        if ($pageType == "newsletter") {
            $objNewsletter = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletter')->find($newsletterId);
            $selectedTemplate = "";
            if ($objNewsletter != "") {
                $selectedTemplate = ($objNewsletter->getTemplate()) ? $objNewsletter->getTemplate()->getId() : null;
            }
        }
        $defaultEmail = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getEmailField($this->contactId, 3);
        $return = array('pageType' => $pageType, 'defaultEmail' => $defaultEmail[$this->contactId], 'newsletterId' => $newsletterId, 'defaultContact' => $this->contactName, 'defaultContactId' => $this->contactId, 'selectedTemplate' => $selectedTemplate);

        return new JsonResponse($return);
    }

    /**
     * Test mail sending simplemail and newsletter
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function testMailAction(Request $request)
    {
        $passed = $request->get('passed');
        $type = $request->get('type');
        $emails = array();
        $result['attachments'] = array();
        if ($passed == 'ids') {
            $ids = $request->get('ids');
            $emailresult = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getPrimaryEmail($ids);
            $emails = $emailresult;
        } else {
            $passsedEmails = $request->get('emails');
            $emailArray = explode(',', $passsedEmails);
            foreach ($emailArray as $key => $value) {
                if ($value != "") {
                    $output = $this->validateEmail($value);
                    if ($output['valid'] == 'true') {
                        $value = $value;
                        array_push($emails, $value);
                    } else {
                        $error = "INVALID_EMAIL";
                        return new JsonResponse(array('status' => 'ERROR', 'errorMsg' => $this->get('translator')->trans($error), 'noparentload' => 1));
                    }
                }
            }
        }
        $templateId = $request->get('template');
        $newsletterId = $request->get('newsletter');
        if ($type == "newsletter") {
            $result = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletterContent')->getNewsletterContentDetails($this->container, $this->clubId, $newsletterId, $templateId, 'testmail');
            $result['testmail'] = 'testmail';
            $body = $this->container->get('templating')->render("ClubadminCommunicationBundle:Preview:newsletter-preview.html.twig", $result);
        } else {
            $result = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletterContent')->getSimplemailContentDetails($this->container, $this->clubId, $newsletterId, $this->contactId, $this->clubTitle, 'testmail');
            $body = $this->container->get('templating')->render("ClubadminCommunicationBundle:Preview:simpleMail-preview.html.twig", $result);
        }
        $nl = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletter')->find($newsletterId);
        $subject = $nl->getSubject();
        $email = $nl->getSenderEmail();
        $senderName = $nl->getSenderName() ? $nl->getSenderName() : $nl->getSenderEmail();
        if ($passed == 'ids') {
            $salutationType = $nl->getSalutationType();
            foreach ($emails as $key => $value) {
                if ($salutationType == "SAME") {
                    $salutation = $nl->getSalutation();
                } elseif ($salutationType == "INDIVIDUAL") {
                    $salutation = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletter')->getSalutationText($value['contactId'], $this->clubId, $this->clubDefaultSystemLang);
                } else {
                    $salutation = " ";
                }
                $trans = array("@@#salutation#@@" => $salutation);
                $bodyNew = strtr($body, $trans);
                $mailer = $this->get('mailer');
                $this->sendSwiftMesage($bodyNew, $value['email'], $email, $subject, $senderName);
            }
        } else {
            $salutationType = $nl->getSalutationType();
            if ($salutationType == "SAME") {
                $salutation = $nl->getSalutation();
            } elseif ($salutationType == "NONE") {
                $salutation = " ";
            } else {
                /* Here for the query statement,first parameter is passed as 0 inorder to get general salutation value */
                /* Also since no subscriber id is passed no need to pass club default lang to salutation function */
                $salutation = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletter')->getSalutationText(0, $this->clubId, $this->clubDefaultSystemLang);
            }
            $trans = array("@@#salutation#@@" => $salutation);
            $bodyNew = strtr($body, $trans);
            $this->sendSwiftMesage($bodyNew, $emails, $email, $subject, $senderName);
        }
        if ($type == "newsletter") {
            $flashMsg = "TEST_NL_MAIL_SENT";
        } else {
            $flashMsg = "TEST_SM_MAIL_SENT";
        }
        return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans($flashMsg), 'noparentload' => 1));
    }

    /**
     * Send Swift Mail Nl/SM
     *
     * @param type $bodyNew
     * @param type $emails
     * @param type $email
     * @param type $subject
     * @param type $attachments
     *
     * @return email
     */
    public function sendSwiftMesage($bodyNew, $emails, $email, $subject, $senderName, $attachments = array())
    {
        $mailer = $this->get('mailer');
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom(array($email => $senderName))
            ->setTo($emails)
            ->setBody(stripslashes($bodyNew), 'text/html');
        foreach ($attachments as $key => $value) {
            $message->attach(\Swift_Attachment::fromPath($value['filePath'])->setFilename($value['fileTitle']));
        }
        $message->setCharset('utf-8');
        $mailer->send($message);
    }

    /**
     * This action is used for create newsletter step6.
     *
     * @param int $newsletterId newsletter id
     *
     * @Template("ClubadminCommunicationBundle:Newsletterwizard:newsletterSending.html.twig")
     *
     * @return array Data array.
     */
    public function sendingNewsletterAction(Request $request, $newsletterId)
    {
        $type = $request->get('level1');
        $objNewsletter = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletter')->find($newsletterId);
        $ntype = $objNewsletter->getNewsletterType();
        $clubId = $objNewsletter->getClub()->getId();
        $club = $this->get('club');
        $bookedModuleDetails = $club->get('bookedModulesDet');
        $flag = (in_array("frontend1", $bookedModuleDetails) && in_array("frontend2", $bookedModuleDetails)) ? 1 : 0;
        if (($ntype != "GENERAL") || ($type != "newsletter") || ($clubId != $this->clubId) || ($objNewsletter->getStatus() == 'sending')) {
            throw $this->createNotFoundException($this->clubTitle . ' has no access to this page');
        }
        $breadcrumb = array('back' => $this->generateUrl('newsletter_mailings'));
        if ($objNewsletter != '') {
            $wizardStep = $objNewsletter->getStep();
            $recipientCount = $objNewsletter->getRecepientCount();
            $sendDate = $objNewsletter->getsendDate();
            $status = $objNewsletter->getStatus();
            if (is_object($sendDate)) {
                $sendDate = $sendDate->format('Y-m-d H:i');
            } else {
                $sendDate = '';
            }

            $recipientList = $objNewsletter->getRecepientList();
            $recipientList = $recipientList ? $recipientList->getId() : null;
            $objNlManualContact = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletterManualContacts')->find($newsletterId);
            if ($objNlManualContact != "") {
                $manualContactIsSet = 1;
            } else {
                $manualContactIsSet = 0;
            }
        }
        $backPath = $this->generateUrl('nl_design', array('newsletterId' => $newsletterId));
        $recipientPath = $this->generateUrl('nl_newsletter_recepients', array('newsletterId' => $newsletterId));
        //$step = 6;
        //temporary avoiding step 4
        $step = 5;

        return array('status' => $status, 'sendDate' => $sendDate, 'manualContactSet' => $manualContactIsSet, 'recipientList' => $recipientList,
            'pageType' => $type, 'step' => $step, 'newsletterId' => $newsletterId,
            'recipientPath' => $recipientPath, 'recipientCount' => $recipientCount, 'backUrl' => $backPath,
            'defaultContact' => $this->contactName, 'defaultContactId' => $this->contactId,
            'bookedModule' => $bookedModuleDetails, 'flag' => $flag, 'breadCrumb' => $breadcrumb, 'wizardStep' => $wizardStep);
    }

    /**
     * This action is used for create simplemail step6.
     *
     * @param int $newsletterId newsletter id
     *
     * @Template("ClubadminCommunicationBundle:Newsletterwizard:newsletterSending.html.twig")
     *
     * @return array Data array.
     */
    public function sendingSimpleMailAction(Request $request, $newsletterId)
    {
        $type = $request->get('level1');
        $objNewsletter = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletter')->find($newsletterId);

        $ntype = $objNewsletter->getNewsletterType();
        $clubId = $objNewsletter->getClub()->getId();
        $breadcrumb = array('back' => $this->generateUrl('newsletter_simplemailings'));

        if (($ntype != "EMAIL") || ($type != "simplemail") || ($clubId != $this->clubId) || ($objNewsletter->getStatus() == 'sending')) {
            throw $this->createNotFoundException($this->clubTitle . ' has no access to this page');
        }
        if ($objNewsletter != '') {
            $wizardStep = $objNewsletter->getStep();
            $recipientCount = $objNewsletter->getRecepientCount();
            $sendDate = $objNewsletter->getsendDate();
            $status = $objNewsletter->getStatus();
            $sendDate = $sendDate->format('Y-m-d-H-i-s');
            $recipientList = $objNewsletter->getRecepientList();
            $recipientList = $recipientList ? $recipientList->getId() : null;
            $objNlManualContact = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletterManualContacts')->find($newsletterId);
            if ($objNlManualContact != "") {
                $manualContactIsSet = 1;
            } else {
                $manualContactIsSet = 0;
            }
        }
        $backPath = $this->generateUrl('sm_design', array('newsletterId' => $newsletterId));
        $recipientPath = $this->generateUrl('nl_simplemail_recepients', array('newsletterId' => $newsletterId));
        $step = 6;

        return array('status' => $status, 'pageType' => $type, 'step' => $step, 'newsletterId' => $newsletterId,
            'manualContactSet' => $manualContactIsSet, 'recipientList' => $recipientList, 'backUrl' => $backPath,
            'recipientCount' => $recipientCount, 'defaultContact' => $this->contactName, 'recipientPath' => $recipientPath,
            'defaultContactId' => $this->contactId, 'flag' => 0, 'sendDate' => $sendDate, 'breadCrumb' => $breadcrumb, 'wizardStep' => $wizardStep);
    }

    /**
     * update Sending newsletter/ simple mail
     *
     * @return type jsonresponse
     */
    public function updateSendingNlSmAction(Request $request)
    {
        ini_set('max_execution_time', 1000);
        ini_set("memory_limit", "2000M");
        $id = $request->get('id');
        $type = $request->get('type');
        $display = $request->get('display');
        $sendingTime = $request->get('sendingTime');
        $sendingType = $request->get('sendingType');
        $rowNewsletter = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletter')->findOneBy(array('id' => $id, 'club' => $this->clubId));
        if ($rowNewsletter && $rowNewsletter->getStatus() != 'sending') {
            $this->em->getRepository('CommonUtilityBundle:FgCnNewsletter')->updateNlSmSending($rowNewsletter, $sendingType, $sendingTime, $display, $this->contactId);
            $redirect = ($type == 'simplemail') ? ($this->generateUrl('newsletter_simplemailings')) : ($this->generateUrl('newsletter_mailings'));
            $flash = ($type == 'simplemail') ? "SM_SENDING_SCHEDULED" : "NL_SENDING_SCHEDULED";
            return new JsonResponse(array('status' => 'SUCCESS', 'sync' => 1, 'redirect' => $redirect, 'flash' => $this->get('translator')->trans($flash)));
        } else {
            $error = "ERROR_SCHEDULING";
            return new JsonResponse(array('status' => 'ERROR', 'errorMsg' => $this->get('translator')->trans($error), 'noparentload' => 1));
        }
    }

    public function newsletterFirststepAjaxAction(Request $request)
    {
        $templateid = $request->get('templateId');
        $editdetails = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletterTemplate')->edittemplatedetails($templateid, $this->clubId);
        return new JsonResponse($editdetails);
    }

    /**
     * This function is used to validate email before newsletter test mail sending
     *
     * @param string $value Email value
     *
     * @return array $output Valid flag true/false
     */
    private function validateEmail($value)
    {
        $valueConstraints = array();
        $valueConstraints[] = new Email(array('message' => 'INVALID_EMAIL'));
        $valueConstraints[] = new NotBlank(array('message' => 'REQUIRED'));
        $data = array('value' => $value);
        $collectionConstraint = new Collection(array(
            'value' => $valueConstraints
        ));
        $errors = $this->container->get('validator')->validate($data, $collectionConstraint);
        $output = (count($errors) !== 0) ? array('valid' => 'false') : array('valid' => 'true');

        return $output;
    }
}
