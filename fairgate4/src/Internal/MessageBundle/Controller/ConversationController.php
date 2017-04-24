<?php

/**
 * Conversation Controller
 *
 * This controller is used for handling conversation in message section.
 *
 * @package    InternalMessageBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 *
 */
namespace Internal\MessageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Common\UtilityBundle\Repository\Pdo\MessagePdo;
use Common\UtilityBundle\Repository\Pdo\ContactPdo;
use Symfony\Component\HttpFoundation\JsonResponse;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Util\FgSettings;
use Symfony\Component\HttpFoundation\Response;
use Clubadmin\Util\Contactlist;
use Common\UtilityBundle\Util\FgPermissions;
use Symfony\Component\HttpFoundation\Request;

/**
 * Inbox Controller
 *
 * This controller is used for handling inbox in message section.
 */
class ConversationController extends Controller
{

    /**
     * Converastion page
     *
     * @param int $messageId Message Id
     *
     * @return Template
     */
    public function conversationAction($messageId)
    {
        $em = $this->getDoctrine()->getManager();
        $clubId = $this->container->get('club')->get('id');
        $contactId = $this->container->get('contact')->get('id');
        $objMessage = new MessagePdo($this->container);
        $groupTitle = $this->get('translator')->trans('MESSAGE_GROUP');
        $personalTitle = $this->get('translator')->trans('MESSAGE_PERSONAL');
        $terminologyService = $this->container->get('fairgate_terminology_service');
        $executiveBoardTitle = $terminologyService->getTerminology('Executive Board', $this->container->getParameter('singular'));
        /* get message headers */
        $messageHeaders = $objMessage->geConversationHeaders($messageId, $clubId, $contactId, $groupTitle, $personalTitle, $executiveBoardTitle);
        $permissionObj = new FgPermissions($this->container);
        $accessCheckArray = array('from' => 'message', 'is_existing' => $messageHeaders['id'], 'has_access' => $messageHeaders['isDeleted'], 'type' => 'message');
        $permissionObj->checkAreaAccess($accessCheckArray);
        $messageHeaders['replyLink'] = $this->getReplyLinkofConversation($messageHeaders, $contactId);
        $contactDetails = $em->getRepository('CommonUtilityBundle:FgCmContact')->getNameOfAContact($contactId);
        $messageHeaders['currentContact'] = $contactDetails[0]['name'];
        $messageHeaders['currentDate'] = date(FgSettings::$PHP_DATE_FORMAT);
        $messageHeaders['currentDateTime'] = date('Y-m-d H:i:s');
        $messageHeaders['contactImageClass'] = "fg-round-img";
        $pathService = $this->container->get('fg.avatar');
        $messageHeaders['contactImage'] = $pathService->getAvatar($contactId, 65);
        $return = array("messageId" => $messageId, "contactId" => $contactId, "messageHeaders" => $messageHeaders);

        return $this->render('InternalMessageBundle:Conversation:conversation.html.twig', $return);
    }

    /**
     * Method to get replky link of convarsation
     *
     * @param array $messageHeaders Messaghe details
     * @param int   $contactId      Contact Id
     *
     * @return String
     */
    private function getReplyLinkofConversation($messageHeaders, $contactId)
    {
        $replyAll = $this->get('translator')->trans('MESSAGE_REPLY_ALL');
        $replyTo = $this->get('translator')->trans('MESSAGE_REPLY_TO');

        if (($messageHeaders['message_type'] === "PERSONAL") || ($messageHeaders['message_type'] === "GROUP" && $messageHeaders['group_type'] === "CONTACT" && $messageHeaders['receiversCount'] == "1")) {
            if ($messageHeaders['message_type'] === "PERSONAL") {
                $replyLink = ($messageHeaders['createdBy'] == $contactId && $messageHeaders['receiversCount'] != "1") ? "" : $replyTo . ' ' . $messageHeaders['messageToContact'];
            } else {
                $replyLink = $replyTo . ' ' . $messageHeaders['receiverNames'];
            }
        } else {
            $replyLink = $replyAll;
        }

        return $replyLink;
    }

    /**
     * Method to send reply of conversation
     *
     * @param Request $request Request object
     *
     * @return JsonResponse
     */
    public function addReplyAction(Request $request)
    {
        $message = $request->request->get('message');
        $messageId = $request->request->get('messageId');
        $contactId = $request->request->get('contactId');
        $receiversCount = $request->request->get('receiversCount');
        //temporary name as save in temp folder
        $attachments = $request->request->get('uploaded_attachments');
        //original filename as we uploaded
        $attachmentNames = $request->request->get('uploaded_attachment_names');

        $attachmentsSizes = $request->request->get('uploaded_attachments_size');
        $em = $this->getDoctrine()->getManager();

        if (count($attachments) > 0) {
            //filenames after replace single quotes and appending 1,2,3 ..)
            $uploadedFileNames = FgUtility::moveMessageAttachments($attachmentNames, $attachments, $this->container);
        }
        $newMessage = $em->getRepository('CommonUtilityBundle:FgMessage')->addReply($message, $messageId, $contactId, $receiversCount, $uploadedFileNames, $attachmentsSizes, $this->container);

        $emailTemplate = $this->getEmailTemplate($uploadedFileNames, $newMessage['messageId']);
        //add to notification spool
        $this->addToNotificationSpool($request, $contactId, $newMessage, $emailTemplate['templateParameters'], $emailTemplate['notificationMessage']);

        $return = array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('MESSAGE_REPLY_SENT'));
        if ($newMessage['messageId'] == $messageId) {
            $return['noreload'] = true;
            $return['dataId'] = $newMessage['dataId'];
            $return['msgTime'] = $newMessage['time'];
            $return['message'] = $message;
            $return['attachments'] = implode(",", $uploadedFileNames);
        } else {
            $return['redirect'] = $this->generateUrl('internal_message_conversation', array('messageId' => $newMessage['messageId']));
            $return['sync'] = true;
        }

        return new JsonResponse($return);
    }

    /**
     * Method to add notification contents to spool
     *
     * @param Request  $request                       Request object
     * @param int      $contactId                     contactId
     * @param array    $newMessage                    message details
     * @param template $templateParameters            emailTemplate parameters
     * @param array    $notificationMessageParameters array of notification message translation text and its parameters
     */
    private function addToNotificationSpool($request, $contactId, $newMessage, $templateParameters, $notificationMessageParameters)
    {
        $objMessage = new MessagePdo($this->container);
        $contactPdo = new ContactPdo($this->container);
        $clubId = $this->container->get('club')->get('id');
        $emailFields = $this->container->get('club')->get('emailFields');
        $contactlistClass = new Contactlist($this->container, $contactId, $this->container->get('club'), 'editable');
        $emailsForNotification = $objMessage->getEmailsForNotification($newMessage['messageId'], $contactId, $clubId, $emailFields, $contactlistClass);
        $em = $this->getDoctrine()->getManager();
        $currentLocateSettings = array(0 => array('id' => $contactId, 'default_lang' => $this->container->get('club')->get('default_lang'), 'default_system_lang' => $this->container->get('club')->get('default_system_lang')));
        $baseurlArr = FgUtility::getMainDomainUrl($this->container, $this->container->get('club')->get('id')); //FAIR-2489
        $baseurl = $baseurlArr['baseUrl']; //Fair-2484   
        foreach ($emailsForNotification as $contact => $contactEmail) {
            //set locale with respect to particular contact
            $rowContactLocale = $contactPdo->getContactLanguageDetails($contact, $clubId, $this->container->get('club')->get('clubTable'), $this->container->get('club')->get('type'));
            $this->container->get('contact')->setContactLocale($this->container, $request, $rowContactLocale);
            //To set the club TITLE, SIGNATURE based on default language
            $this->container->get('contact')->setClubParamsOnLangauge($this->container->get('club'));

            //date settings according to contact
            $templateParameters['sentOn'] = $this->container->get('club')->formatDate(date('Y-m-d H:i:s'), 'datetime');
            $templateParameters['clubTitle'] = $this->container->get('club')->get('title');
            $clubLogoPath = $this->container->get('club')->getClubLogoPath(false);
            $templateParameters['logoURL'] = ($clubLogoPath == '') ? '' : $baseurl . '/' . $clubLogoPath;
            //Build email template in the corresponding language
            $emailTemplate = $this->renderView('InternalGeneralBundle:MailTemplate:notificationMail.html.twig', $templateParameters);

            //Replace notification message place holder in the template
            $notificationMessage = $this->get('translator')->trans($notificationMessageParameters['text'], $notificationMessageParameters['parameters']);
            $emailTemplateForNotification = str_replace('%{NOTIFICATIONMESSAGE}%', $notificationMessage, $emailTemplate);

            //add to notification spool
            $em->getRepository('CommonUtilityBundle:FgNotificationSpool')->addNotificationEntries($contactEmail, $emailTemplateForNotification, $newMessage['subject'], $newMessage['senderEmail']);
        }

        //reset contact locale with respect to logged in contact
        $this->container->get('contact')->setContactLocale($this->container, $request, $currentLocateSettings);
        //To set the club TITLE, SIGNATURE based on default language
        $this->container->get('contact')->setClubParamsOnLangauge($this->container->get('club'));
    }

    /**
     * Method to get parameters to build email template for sending notifications on reply to a message
     *
     * @param array $attachments attachment names
     * @param int   $messageId   Message Id to generate url
     *
     * @return array of templateParameters and notificationMessage-parameters
     */
    private function getEmailTemplate($attachments, $messageId)
    {
        $uploadedAttachments = array();
        $clubId = $this->container->get('club')->get('id');
        $em = $this->getDoctrine()->getManager();
        $checkClubHasDomain = $em->getRepository('CommonUtilityBundle:FgDnClubDomains')->checkClubHasDomain($clubId);
        if ($checkClubHasDomain) {
            $baseurl = $checkClubHasDomain['domain'];
        } else {
            $baseurl = FgUtility::getBaseUrl($this->container);
        }
        if (count($attachments) > 0) {
            foreach ($attachments as $attachment) {
                $attachmentFilename = $attachment;
                $uploadedAttachments[] = array("file" => $attachmentFilename, "url" => $baseurl . '/uploads/' . $clubId . '/users/messages/' . $attachment);
            }
        }

        $messageDetails = $em->getRepository('CommonUtilityBundle:FgMessage')->getMessageById($messageId);
        $objMessage = new MessagePdo($this->container);
        $terminologyService = $this->container->get('fairgate_terminology_service');
        $executiveBoardTitle = $terminologyService->getTerminology('Executive Board', $this->container->getParameter('singular'));
        /* get message headers */
        $messageHeaders = $objMessage->geConversationHeaders($messageId, $clubId, $this->container->get('contact')->get('id'), '', '', $executiveBoardTitle);
        $contactDetails = $em->getRepository('CommonUtilityBundle:FgCmContact')->getNameOfAContact($this->container->get('contact')->get('id'));
        $messageData = $em->getRepository('CommonUtilityBundle:FgMessageData')->getLastMessage($messageId);
        
        $hostName = FgUtility::generateUrlForHost($this->container, $this->container->get('club')->get('url_identifier'), 'internal_dashboard', $checkClubHasDomain);
        //$notificationMessageParameters used for building notification message in the language, specific to a user
        if ($messageHeaders['group_type'] === "CONTACT") {
            $notificationMessageParameters = array('text' => "NOTIFICATION_FOLLOWUP_CONTACT", 'parameters' => array("%receivers%" => $messageHeaders['receiverNames'], "%admin_name%" => $contactDetails[0]['name'], "%hostName%" => $hostName));
        } else {
            $notificationMessageParameters = array('text' => "NOTIFICATION_FOLLOWUP_GROUP", 'parameters' => array("%receivers%" => $messageHeaders['receiverNames'], "%hostName%" => $hostName));
        }
        $templateParameters = array(            
            'notifType' => 'message',
            'replayLink' => FgUtility::generateUrlForHost($this->container, $this->container->get('club')->get('url_identifier'), 'internal_message_conversation', $checkClubHasDomain, array('messageId' => $messageId)),
            'messageTitle' => $messageDetails['subject'],
            'messageContent' => $messageData['message'],
            'adminName' => $contactDetails[0]['name'],
            'attachments' => $uploadedAttachments,
            'conversationUrl' => FgUtility::generateUrlForHost($this->container, $this->container->get('club')->get('url_identifier'), 'internal_message_conversation', $checkClubHasDomain, array('messageId' => $messageId)),
            'inboxUrl' => FgUtility::generateUrlForHost($this->container, $this->container->get('club')->get('url_identifier'), 'internal_message_inbox', $checkClubHasDomain),
            'notificationMessage' => '%{NOTIFICATIONMESSAGE}%',
        );

        return array('templateParameters' => $templateParameters, "notificationMessage" => $notificationMessageParameters);
    }

    /**
     * Method to get json array of contents of a convarsation
     *
     * @param Request $request   Request object
     * @param int     $messageId Message Id
     * @param int     $page      For pagination implemented by lazy loading
     * @param int     $limit     Limit
     *
     * @return JsonResponse
     */
    public function getConversationAction(Request $request, $messageId, $page = 1, $limit = 5)
    {
        $clubId = $this->container->get('club')->get('id');
        $contactId = $this->container->get('contact')->get('id');
        $profileImgField = $this->container->getParameter('system_field_communitypicture');
        $companyLogoField = $this->container->getParameter('system_field_companylogo');
        $currentDateTime = $request->request->get('currentDateTime');
        $federationId = ($this->container->get('club')->get('type') != 'standard_club') ? $this->container->get('club')->get('federation_id') : $clubId;
        $rootPath = FgUtility::getRootPath($this->container);
        $objMessage = new MessagePdo($this->container);
        $offset = ((($page - 1) * $limit) < 0) ? 0 : (($page - 1) * $limit);
        /* get message contents */
        $messageDatas = $objMessage->geConversation($messageId, $clubId, $contactId, $profileImgField, $companyLogoField, $offset, $limit, $currentDateTime);
        /* insert profile image path of sender to the array */
        for ($i = 0; $i < count($messageDatas); $i++) {
            $subFolder = ($messageDatas[$i]['isCompanySender'] == 1) ? 'companylogo' : 'profilepic';
            $imageLocation = FgUtility::getUploadFilePath($federationId, $subFolder);
            //FAIR-2489
            $messageDatas[$i]['message'] = FgUtility::correctCkEditorUrl($messageDatas[$i]['message'], $this->container, $clubId);
            $messageDatas[$i]['senderImage'] = FgUtility::getContactImage($rootPath, $federationId, $messageDatas[$i]['senderProfileImg'], 'width_65', '', $imageLocation);
        }

        return new JsonResponse($messageDatas);
    }

    /**
     * Method to set read status of message
     *
     * @param Request $request Request object
     *
     * @return JsonResponse
     */
    public function setReadAction(Request $request)
    {
        $messageId = $request->request->get('messageId');
        $contactId = $request->request->get('contactId');
        $em = $this->getDoctrine()->getManager();
        $return['readCount'] = $em->getRepository('CommonUtilityBundle:FgMessageReceivers')->setMessageRead($messageId, $contactId);

        return new JsonResponse($return);
    }

    /**
     * function to download attachments (Attachment name is actually $identifier."~~__~~".$attachmentName)
     *
     * @param int    $identifier     Identifier
     * @param string $attachmentName attachment Name
     *
     * @return response
     *
     * @throws createNotFoundException
     */
    public function downloadAttachmentsAction($attachmentName)
    {
        ini_set('max_execution_time', 0);
        ini_set("memory_limit", "2000M");
        $uploadFolderPath = FgUtility::getUploadDir();
        //$attachment = filter_var($attachmentName, FILTER_SANITIZE_URL); // Remove (more) invalid characters
        $downloadPath = $uploadFolderPath . '/' . $this->get('club')->get('id') . '/users/messages/' . $attachmentName;
        $fileInfo = new \SplFileInfo($downloadPath);
        /* get file extension */
        $fileExtension = pathinfo($fileInfo->getFilename(), PATHINFO_EXTENSION);
        if ($file = fopen($downloadPath, "r")) {
            $fsize = filesize($downloadPath);
            $response = new Response();
            $response->setStatusCode(200);
            $response->headers->set('Content-Type', 'application/' . mime_content_type($downloadPath) . '; charset=utf-8');
            $response->headers->set('Content-Disposition', 'attachment; filename= "' . $attachmentName . "\"");
            $response->headers->set('Content-Transfer-Encoding', 'utf-8');
            $response->headers->set("Content-length: $fsize");
            $response->headers->set("Cache-control: private"); //use this to open files directly
            $response->sendHeaders();
            /* In case of docx files, if we use readfile it will add some extra numerals, so using fread */
            if ($fileExtension == 'docx') {
                $response->setContent(fread($file, filesize($downloadPath)));
            } else {
                $response->setContent(readfile($downloadPath));
            }
            fclose($file);

            return $response;
        } else {
            $permissionObj = new FgPermissions($this->container);
            $accessCheckArray = array('from' => 'default', 'type' => 'file', 'message' => $this->get('translator')->trans('FILE_NOT_EXIST'));
            $permissionObj->checkAreaAccess($accessCheckArray);
        }
    }
}
