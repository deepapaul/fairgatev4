<?php

/**
 * Message Wizard Controller
 *
 * This controller was created for handling create/edit message wizard.
 *
 * @package    InternalMessageBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 *
 */
namespace Internal\MessageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Common\UtilityBundle\Repository\Pdo\MessagePdo;
use Common\UtilityBundle\Repository\Pdo\ContactPdo;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Util\FgPermissions;
use Symfony\Component\HttpFoundation\Request;

class MessageWizardController extends Controller
{

    /**
     * Function to create/edit messgae step 1 UI
     *
     * @param Request $request   Request object
     * @param type    $messageId Id of the message
     *
     * @return type
     */
    public function wizardGeneralAction(Request $request, $messageId = false)
    {
        $em = $this->getDoctrine()->getManager();
        $clubId = $this->container->get('club')->get('id');
        $recipients = $selectedRecipients = $parameters = array();
        $parameters['source'] = $request->get('source', '');        

        //edit draftn message
        if ($messageId) {
            //get the messagedetails and pass it to the view
            $messageDetailArray = $em->getRepository('CommonUtilityBundle:FgMessage')->getMessageById($messageId, $clubId);

            //Check if the logged in contact is the creator
            $this->checkAccess($messageDetailArray);
            $parameters['messageDetailArray'] = $messageDetailArray;
            $parameters['edit'] = true;
            $recipienttype = $messageDetailArray['groupType'];

            //Get the teams/workgroups/contacts for which the message has been sent
            if ($messageDetailArray['groupType'] == 'TEAM' || $messageDetailArray['groupType'] == 'WORKGROUP') {
                $selectedRecipients = $em->getRepository('CommonUtilityBundle:FgMessageGroup')->getMessageGroupDetails($messageId);
            } else if ($messageDetailArray['groupType'] == 'CONTACT') {
                $selectedRecipients = $em->getRepository('CommonUtilityBundle:FgMessageReceivers')->getMessageReceivers($messageId);
            }
        } else { //create message
            $parameters['edit'] = false;
            $recipienttype = $request->get('recipienttype');

            //The list of recipients that have been selected
            $selectedFromLastPageRecipients = $request->get('mr');
        }

        //Get the email of the logged in club
        $parameters['emailList'] = $this->getEmailsOfLoggedUser();

        /*         * ************* Get the list of team/workgroup recipients ******************* */
        if ($recipienttype == 'TEAM' || $recipienttype == 'WORKGROUP') {
            if ($recipienttype == 'TEAM') {
                $recipientList = $this->get('contact')->get('teams');
                $type = 'team';
            } else if ($recipienttype == 'WORKGROUP') {
                $recipientList = $this->get('contact')->get('workgroups');
                $type = 'workgroup';
            }
            if (!is_array($selectedFromLastPageRecipients))
                $selectedFromLastPageRecipients = array($selectedFromLastPageRecipients);

            foreach ($recipientList as $reciepientId => $reciepientName) {
                $recipients[] = array("title" => $reciepientName, "id" => $reciepientId);

                //If the team/contact been selected from the page set it as reciepient
                if (count($selectedFromLastPageRecipients) > 0 && in_array($reciepientId, $selectedFromLastPageRecipients)) {
                    $selectedRecipients[] = array("title" => $reciepientName, "id" => $reciepientId);
                }
            }
        } else if ($recipienttype == 'CONTACT') {
            //If the contact is been selected from the page set it as recipient
            if (count($selectedFromLastPageRecipients) > 0) {
                $selectedRecipients = $em->getRepository('CommonUtilityBundle:FgCmContact')->getContactTitle($selectedFromLastPageRecipients);
            }
        }
        /*         * ************************************************************************ */

        $parameters['step'] = 1;
        $parameters['recipienttype'] = $recipienttype;
        $parameters['recipients'] = $recipients;
        $parameters['selectedRecipients'] = $selectedRecipients;

        $parameters['pageTitle'] = $this->get('translator')->trans('MESSAGE_CREATION_HEADER');

        return $this->render('InternalMessageBundle:MessageWizard:wizardGeneral.html.twig', $parameters);
    }

    /**
     * Function to save the message on create and update
     *
     * @param Request $request   Request object
     * @param type    $messageId The id of the message
     *
     * @return JsonResponse
     */
    public function wizardGeneralSaveAction(Request $request, $messageId = false)
    {
        $em = $this->getDoctrine()->getManager();
        $edit = ($messageId) ? true : false;

        //insert message
        $clubDetails = $this->get('club');
        $contactId = $this->get('contact')->get('id');

        $messageDetailArray['senderemail'] = $request->get('senderemail');
        $messageDetailArray['messagetype'] = $request->get('conversationtype');
        $messageDetailArray['grouptype'] = $request->get('grouptype');
        $messageDetailArray['createdby'] = $em->getRepository('CommonUtilityBundle:FgCmContact')->find($contactId);

        if (!$edit) {
            //insert
            $messageDetailArray['step'] = 1;
            $messageDetailArray['clubobj'] = $em->getRepository('CommonUtilityBundle:FgClub')->find($clubDetails->get('id'));
            $messageDetailArray['createdby'] = $em->getRepository('CommonUtilityBundle:FgCmContact')->find($contactId);
            $messageId = $em->getRepository('CommonUtilityBundle:FgMessage')->insertMessageStep1($messageDetailArray);
        } else {
            $messageDetailArray['messageid'] = $messageId;
            $em->getRepository('CommonUtilityBundle:FgMessage')->updateMessageStep1($messageDetailArray);
        }
        $messageDetailObj = $em->getRepository('CommonUtilityBundle:FgMessage')->find($messageId);

        //insert to message group
        if ($messageDetailArray['grouptype'] == 'TEAM' || $messageDetailArray['grouptype'] == 'WORKGROUP') {
            if ($edit) { //Delete already addedgroups
                $em->getRepository('CommonUtilityBundle:FgMessageGroup')->deleteGroupsInMessage($messageId);
            }
            $em->getRepository('CommonUtilityBundle:FgMessageGroup')->insertMessageGroup($messageDetailObj, $request->get('message_recipients'));
        }

        //insert to recipients
        $recipientArray = array();
        if ($messageDetailArray['grouptype'] == 'CONTACT') {
            if ($edit) { //Delete already recipients
                $em->getRepository('CommonUtilityBundle:FgMessageReceivers')->deleteReceiversInMessage($messageId);
            }
            $recipientArray = $request->get('message_recipients');
        }

        //Add myself as recipient
        $recipientArray[] = $this->container->get('contact')->get('id');
        $em->getRepository('CommonUtilityBundle:FgMessageReceivers')->insertMessageReceivers($messageDetailObj, $recipientArray, $this->container->get('contact')->get('id'));

        $responseArray['redirect'] = $this->generateUrl('internal_create_message_step2', array('messageId' => $messageId), true);
        $responseArray['sync'] = 1;
        return new JsonResponse($responseArray);
    }

    /**
     * Function to load message stage 2 UI for add/edit
     *
     * @param type $messageId The id of the message
     *
     * @return template
     */
    public function wizardMessageAction($messageId)
    {
        $parameters = array();
        $em = $this->getDoctrine()->getManager();
        $clubId = $this->container->get('club')->get('id');
        $messageDetailArray = $em->getRepository('CommonUtilityBundle:FgMessage')->getMessageById($messageId, $clubId);

        //Check if the logged in contact is the creator
        $this->checkAccess($messageDetailArray);

        $messageContentDetailArray = $em->getRepository('CommonUtilityBundle:FgMessageData')->getFirstMessage($messageId);
        $messageAttachmentDetailArray = $em->getRepository('CommonUtilityBundle:FgMessageAttachments')->getMessageAttachments($messageContentDetailArray['id']);
        //FAIR-2489
        $messageContentDetailArray['message'] = FgUtility::correctCkEditorUrl($messageContentDetailArray['message'], $this->container, $clubId);
        $edit = ($messageDetailArray['step'] == 1) ? false : true;
        $messageDetailArray['content'] = $messageContentDetailArray['message'];
        $parameters['messageDetailArray'] = $messageDetailArray;
        $parameters['messageAttachmentDetailArray'] = $messageAttachmentDetailArray;
        $parameters['step'] = 2;
        $parameters['edit'] = $edit;
        $parameters['pageTitle'] = $this->get('translator')->trans('MESSAGE_CREATION_HEADER');

        return $this->render('InternalMessageBundle:MessageWizard:wizardMessage.html.twig', $parameters);
    }

    /**
     * Function to save the message stage 2 details
     *
     * @param Request $request   Request object
     * @param type    $messageId The id of the message
     *
     * @return jsonResponse
     */
    public function wizardMessageSaveAction(Request $request, $messageId)
    {
        $parameters = array();
        $em = $this->getDoctrine()->getManager();
        $clubId = $this->container->get('club')->get('id');
        $messageDetailArray = $em->getRepository('CommonUtilityBundle:FgMessage')->getMessageById($messageId, $clubId);

        //Check if the logged in contact is the creator
        $this->checkAccess($messageDetailArray);

        $messageContentDetailArray = $em->getRepository('CommonUtilityBundle:FgMessageData')->getFirstMessage($messageId);


        $edit = ($messageDetailArray['step'] == 1) ? false : true;
        $messageContentArray['messagecontent'] = $request->get('message');
        if (!$edit) {
            $messageDetailArray['step'] = 2;

            //insert the message content
            $messageContentArray['messageid'] = $em->getRepository('CommonUtilityBundle:FgMessage')->find($messageId);
            $messageContentArray['senderid'] = $em->getRepository('CommonUtilityBundle:FgCmContact')->find($this->get('contact')->get('id'));
            $messageContentArray['id'] = $em->getRepository('CommonUtilityBundle:FgMessageData')->insertMessageContent($messageContentArray);
        } else {
            //update the message content
            $messageContentArray['id'] = $messageContentDetailArray['id'];
            $em->getRepository('CommonUtilityBundle:FgMessageData')->updateMessageContent($messageContentArray);

            //Delete the added attachements
            $em->getRepository('CommonUtilityBundle:FgMessageAttachments')->deleteMessageAttachment($messageContentArray['id']);
        }

        $messageDetailArray['subject'] = $request->get('subject');
        $messageDetailArray['updatedby'] = $em->getRepository('CommonUtilityBundle:FgCmContact')->find($this->get('contact')->get('id'));
        $em->getRepository('CommonUtilityBundle:FgMessage')->updateMessageStep2($messageDetailArray);

        //insert the attachements of the
        //temporary name as save in temp folder
        $uploadedAttachments = $request->get('uploaded_attachments');
        //original filename as we uploaded
        $uploadedAttachmentNames = $request->get('uploaded_attachment_names');

        $uploadedAttachmentsSize = $request->get('uploaded_attachments_size');
        if (count($uploadedAttachments) > 0) {
            //filenames after replace single quotes and appending 1,2,3 ..)
            $uploadedFileNames = FgUtility::moveMessageAttachments($uploadedAttachmentNames, $uploadedAttachments, $this->container);
            $messageObj = $em->getRepository('CommonUtilityBundle:FgMessageData')->find($messageContentArray['id']);
            $em->getRepository('CommonUtilityBundle:FgMessageAttachments')->insertMessageAttachment($messageObj, $uploadedFileNames, $uploadedAttachmentsSize);
        }

        $responseArray['redirect'] = $this->generateUrl('internal_create_message_step3', array('messageId' => $messageId), true);
        $responseArray['sync'] = 1;
        return new JsonResponse($responseArray);
    }

    /**
     * Function to create/edit message step 3 page
     *
     * @param int $messageId
     */
    public function wizardSendingAction($messageId)
    {

        $return['emailAttrIds'] = $this->get('club')->get('emailFields');
        $return['emailAttrIds'][] = 'parent';
        $return['contactFields'] = $this->get('club')->get('allContactFields');
        $return['messageId'] = $messageId;
        $clubId = $this->container->get('club')->get('id');
        $em = $this->getDoctrine()->getManager();
        $messageDetails = $em->getRepository('CommonUtilityBundle:FgMessage')->getMessageById($messageId, $clubId);

        //Check if the logged in contact is the creator
        $this->checkAccess($messageDetails);

        //get team and workgroup ids of which current contact is the admin.
        $adminRoles = $this->get('contact')->get('clubRoleRightsGroupWise');
        $adminRoles = array_merge($adminRoles['ROLE_GROUP_ADMIN']['teams'], $adminRoles['ROLE_GROUP_ADMIN']['workgroups']);
        $isSuperAdmin = ($this->get('contact')->get('isSuperAdmin') || (($this->get('contact')->get('isFedAdmin')) && ($this->get('club')->get('type') != 'federation'))) ? 1 : 0;
        $userAdminModules = $this->get('contact')->get('allowedModules');
        //contact is superadmin or club admin or module admin (isAdmin=true => is andmin have full previlege,2=>admin of some roles or workgroup previlege on some contacts )
        if ($isSuperAdmin || in_array('communication', $userAdminModules) || in_array('contact', $userAdminModules) || in_array('clubAdmin', $userAdminModules)) {
            $return['isAdmin'] = true;
        } else {
            $return['isAdmin'] = false;
        }
        if ($messageDetails['groupType'] != 'CONTACT') {
            $selectedRoles = $em->getRepository('CommonUtilityBundle:FgMessageGroup')->getMessageGroups($messageId);
            $selectedRoles = explode(',', $selectedRoles['groups']);
            $adminTeams = array_intersect($selectedRoles, $adminRoles);
            //contact is an admin of some teams or workgroups
            if (count($adminTeams) > 0 && $return['isAdmin'] == false) {
                $return['isAdmin'] = 2;
            }
        }
        $return['step'] = 3;
        $return['messageDetailArray'] = $messageDetails;
        $return['pageTitle'] = $this->get('translator')->trans('MESSAGE_CREATION_HEADER');

        return $this->render('InternalMessageBundle:MessageWizard:wizardSending.html.twig', $return);
    }

    /**
     * Function to get contact list and emails for notification listing
     *
     * @param type $messageId
     * @return JsonResponse
     */
    public function getContactAndEmailsForNotificAction($messageId)
    {
        $clubId = $this->container->get('club')->get('id');
        $em = $this->getDoctrine()->getManager();
        $messageGroups = $em->getRepository('CommonUtilityBundle:FgMessageGroup')->getMessageGroups($messageId);
        $messageDetails = $em->getRepository('CommonUtilityBundle:FgMessage')->getMessageById($messageId, $clubId);

        //Check if the logged in contact is the creator
        $this->checkAccess($messageDetails);

        if ($messageDetails['groupType'] != 'CONTACT') {
            $messageContacts = $em->getRepository('CommonUtilityBundle:FgRmRoleContact')->getContactsOfRoles($messageGroups['groups'], $this->get('club')->get('id'));
            //get team and workgroup ids of which current contact is the admin.
            $adminRoles = $this->get('contact')->get('clubRoleRightsGroupWise');
            $adminRoles = array_merge($adminRoles['ROLE_GROUP_ADMIN']['teams'], $adminRoles['ROLE_GROUP_ADMIN']['workgroups']);
            $selectedRoles = explode(',', $messageGroups['groups']);
            $roles = array_intersect($selectedRoles, $adminRoles);
        } else {
            $messageContacts = $em->getRepository('CommonUtilityBundle:FgMessageReceivers')->getMessageReceiverIds($messageId);
        }
        $clubDetails = $this->get('club');

        $emailAttributeIds = $clubDetails->get('emailFields');
        if (count($emailAttributeIds) > 0) {
            $emailListOfContact = $em->getRepository('CommonUtilityBundle:FgCmContact')->getAttributeValuesOfContacts($this->container, $messageContacts['contacts'], $clubDetails, $emailAttributeIds, $roles);
        }

        return new JsonResponse($emailListOfContact);
    }

    /**
     * Function to active and visible contacts of a logged in user
     *
     * @param Request $request Request object
     *
     * @return JsonResponse
     */
    public function getRecipientsAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $clubId = $this->get('club')->get('id');
        $clubType = $this->get('club')->get('type');
        $contactId = $this->get('contact')->get('id');
        $passedColumns = ' contactnameyob(C.id) AS title';
        $recipientList = $em->getRepository('CommonUtilityBundle:FgCmContact')
            ->getAutocompleteContacts($contactId, 2, '', $this->container, $clubId, $clubType, $passedColumns, $request->get('term'), true, 0, 0);

        return new JsonResponse($recipientList);
    }

    /**
     * Function to send message
     *
     * @param Request $request   Request object
     * @param type    $messageId
     *
     * @return JsonResponse
     */
    public function wizardSendingSubmitAction(Request $request, $messageId)
    {
        $clubId = $this->container->get('club')->get('id');
        $contactData = json_decode($request->get('contactsData'), true);
        $clubDetails = $this->get('club');
        $em = $this->getDoctrine()->getManager();
        $messageDetails = $em->getRepository('CommonUtilityBundle:FgMessage')->getMessageById($messageId, $clubId);

        //Check if the logged in contact is the creator
        $this->checkAccess($messageDetails);

        $em->getRepository('CommonUtilityBundle:FgMessage')->updateMessageSending($messageId);
        //render notification template
        $messageAttachments = $em->getRepository('CommonUtilityBundle:FgMessageAttachments')->getMessageAttachmentsForMessage($messageId);
        $attachmentForTemplate = array();
        $checkClubHasDomain = $em->getRepository('CommonUtilityBundle:FgDnClubDomains')->checkClubHasDomain($clubId);
        if ($checkClubHasDomain) {
            $baseurl = $checkClubHasDomain['domain'];
        } else {
            $baseurl = FgUtility::getBaseUrl($this->container);
        }
        foreach ($messageAttachments as $key => $attachment) {
            $attachmentForTemplate[$key]['url'] = $baseurl . '/' . FgUtility::getUploadFilePath($clubDetails->get('id'), 'messages', false, $attachment['file']);
            $attachmentFilename = $attachment['file'];
            $attachmentForTemplate[$key]['file'] = $attachmentFilename;
        }

        $objMessage = new MessagePdo($this->container);
        $clubLogo = $clubDetails->get('logo');
        $rootPath = FgUtility::getRootPath($this->container);
        if ($clubLogo == "" || !file_exists($rootPath . '/' . FgUtility::getUploadFilePath($clubDetails->get('id'), 'clublogo', false, $clubLogo))) {
            $clubLogoUrl = '';
        } else {
            $clubLogoUrl = $baseurl . '/' . FgUtility::getUploadFilePath($clubDetails->get('id'), 'clublogo', false, $clubLogo);
        }
        $messageData = $em->getRepository('CommonUtilityBundle:FgMessageData')->getFirstMessage($messageId);
        $objContact = new ContactPdo($this->container);
        $adminName = $objContact->getContactName($this->container->get('contact')->get('id'), false, true);
        $hostName = FgUtility::generateUrlForHost($this->container, $this->container->get('club')->get('url_identifier'), 'internal_dashboard', $checkClubHasDomain);
        $terminologyService = $this->container->get('fairgate_terminology_service');
        $executiveBoardTitle = $terminologyService->getTerminology('Executive Board', $this->container->getParameter('singular'));
        /* get message headers */
        $messageHeaders = $objMessage->geConversationHeaders($messageId, $clubDetails->get("id"), $this->container->get('contact')->get('id'), '', '', $executiveBoardTitle);

        $templateParameters = array(           
            'notifType' => 'message',
            'replayLink' => FgUtility::generateUrlForHost($this->container, $this->container->get('club')->get('url_identifier'), 'internal_message_conversation', $checkClubHasDomain, array('messageId' => $messageId)),
            'messageTitle' => $messageDetails['subject'],
            'messageContent' => $messageData['message'],
            'adminName' => $adminName,            
            'attachments' => $attachmentForTemplate,
            'conversationUrl' => FgUtility::generateUrlForHost($this->container, $this->container->get('club')->get('url_identifier'), 'internal_message_conversation', $checkClubHasDomain, array('messageId' => $messageId)),
            'inboxUrl' => FgUtility::generateUrlForHost($this->container, $this->container->get('club')->get('url_identifier'), 'internal_message_inbox', $checkClubHasDomain)
        );

        /* add a template to array $contactData */
        $contactDatas = $this->addTemplateToContactDetail($request, $contactData, $clubId, $messageHeaders, $adminName, $hostName, $templateParameters);

        /* get message details of contact */
        $messages = $objMessage->saveSendingMessage($contactDatas, $clubDetails->get('emailFields'), $messageId, $messageDetails, $this->get('club'), $this->get('contact')->get('id'));

        $responseArray['redirect'] = $this->generateUrl('internal_message_inbox');
        $responseArray['sync'] = 1;
        $responseArray['status'] = true;

        return new JsonResponse($responseArray);
    }

    /**
     * Method to add a key 'template' to array $contactData. It contains template for sending notification mails, in the language specific to that contact
     *
     * @param Request $request        Request object
     * @param array   $contactData    Contact details array to which template is to be added
     * @param int     $clubId         Current clubId
     * @param array   $messageHeaders Message details array
     * @param string  $hostName       Host name
     *
     * @return array $contactData including key template
     */
    private function addTemplateToContactDetail($request, $contactData, $clubId, $messageHeaders, $adminName, $hostName, $templateParameters)
    {
        $contactPdo = new ContactPdo($this->container);
        if (count($contactData) > 0) {
            $currentLocateSettings = array(0 => array('id' => $this->container->get('contact')->get('id'), 'default_lang' => $this->container->get('club')->get('default_lang'), 'default_system_lang' => $this->container->get('club')->get('default_system_lang')));
            $baseurlArr = FgUtility::getMainDomainUrl($this->container, $this->container->get('club')->get('id')); //FAIR-2489
            $baseurl = $baseurlArr['baseUrl']; //Fair-2484   
            foreach ($contactData as $key => $contactDetail) {
                //set locale with respect to particular contact
                $clubType = $this->container->get('club')->get('type');
                $rowContactLocale = $contactPdo->getContactLanguageDetails($contactDetail['id'], $clubId, $this->container->get('club')->get('clubTable'), $clubType);
                $this->container->get('contact')->setContactLocale($this->container, $request, $rowContactLocale);
                //To set the club TITLE, SIGNATURE based on default language
                $this->container->get('contact')->setClubParamsOnLangauge($this->container->get('club'));

                //$notificationMessageParameters used for building notification message in the language, specific to a user
                if ($messageHeaders['group_type'] === "CONTACT") {
                    $templateParameters['notificationMessage'] = $this->get('translator')->trans('NOTIFICATION_MESSAGE_CONTACT', array("%receivers%" => $messageHeaders['receiverNames'], "%admin_name%" => $adminName, "%hostName%" => $hostName));
                } else {
                    $templateParameters['notificationMessage'] = $this->get('translator')->trans('NOTIFICATION_MESSAGE_GROUP', array("%receivers%" => $messageHeaders['receiverNames'], "%hostName%" => $hostName));
                }

                //date settings according to contact
                $templateParameters['sentOn'] = $this->container->get('club')->formatDate(date('Y-m-d H:i:s'), 'datetime');                
                $templateParameters['clubTitle'] = $this->container->get('club')->get('title');
                $clubLogoPath = $this->container->get('club')->getClubLogoPath(false);
                $templateParameters['logoURL'] = ($clubLogoPath == '') ? '' : $baseurl . '/' . $clubLogoPath;
                $this->container->get('club')->get('default_lang');
                //Build email template in the corresponding language
                 $contactData[$key]['template'] = $this->renderView('InternalGeneralBundle:MailTemplate:notificationMail.html.twig', $templateParameters);
            }
            //reset contact locale with respect to logged in contact
            $this->container->get('contact')->setContactLocale($this->container, $request, $currentLocateSettings);
            //To set the club TITLE, SIGNATURE based on default language
            $this->container->get('contact')->setClubParamsOnLangauge($this->container->get('club'));
        }

        return $contactData;
    }

    /**
     * Get the emails of the current logged in user
     *
     * @return Array
     */
    private function getEmailsOfLoggedUser()
    {

        $emailList[] = 'noreply@fairgate.ch';
        $contactDetails = $this->get('contact');
        $clubDetails = $this->get('club');
        $contactId = $this->get('contact')->get('id');

        $em = $this->getDoctrine()->getManager();
        $isSuperAdmin = ($this->get('contact')->get('isSuperAdmin') || (($this->get('contact')->get('isFedAdmin')) && ($this->get('club')->get('type') != 'federation'))) ? 1 : 0;
        if (!$isSuperAdmin) {
            $emailAttributeIds = $this->get('club')->get('emailFields');
            if (count($emailAttributeIds) > 0) {
                $emailListOfContact = $em->getRepository('CommonUtilityBundle:FgCmContact')->getEmailsOfAContact($this->container, $contactId, $clubDetails, $emailAttributeIds);
                unset($emailListOfContact[0]['id']);
                unset($emailListOfContact[0]['fed_contact_id']);
                unset($emailListOfContact[0]['subfed_contact_id']);
                $emailList = array_merge($emailList, $emailListOfContact[0]);
            }
        } else {
            $emailList[] = $contactDetails->get('email');
        }
        $emailList = array_filter($emailList);

        return $emailList;
    }
    
    /**
     * Method check access for the message wizard
     *     
     * @param array $messageDetailArray Message detail Array
     *
     */
    private function checkAccess($messageDetailArray)
    {
        $permissionObj = new FgPermissions($this->container);
        $messageDetailArray['from'] = $messageDetailArray['type'] = 'messagewizard';
        $permissionObj->checkAreaAccess($messageDetailArray);
    }
}
