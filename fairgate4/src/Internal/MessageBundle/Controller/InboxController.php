<?php

/**
 * Inbox Controller
 *
 * This controller is used for handling inbox in message section.
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
use Symfony\Component\HttpFoundation\JsonResponse;
use Common\UtilityBundle\Util\FgUtility;
use Symfony\Component\HttpFoundation\Request;

/**
 * Inbox Controller
 *
 * This controller is used for handling inbox in message section.
 */
class InboxController extends Controller
{

    /**
     * data limit of inbox page
     *
     * @var int
     */
    private $limit = 50;

    /**
     * Method for inbox page
     *
     * @return Template
     */
    public function inboxAction()
    {
        $contactId = $this->container->get('contact')->get('id');
        $em = $this->getDoctrine()->getManager();
        $countInbox = $em->getRepository('CommonUtilityBundle:FgMessage')->getContactMessagesCount($contactId, $this->get('club')->get('id'));
        $countDrafts = $em->getRepository('CommonUtilityBundle:FgMessage')->getContactDraftsCount($contactId, $this->get('club')->get('id'));
        $totalCount = $countInbox + $countDrafts;
        $myTeams = $this->get('contact')->get('teams');
        $myWorkgroups = $this->get('contact')->get('workgroups');

        $return = array("tabs" => array("inbox" => array('text' => $this->get('translator')->trans('MESSAGE_CONVERSATION'), 'count' => $countInbox), "drafts" => array('text' => $this->get('translator')->trans('MESSAGE_DRAFTS'), 'count' => $countDrafts)),
            "countInbox" => $countInbox, 'totalCount' => $totalCount, "countDrafts" => $countDrafts, 'limit' => $this->limit, 'myTeams' => $myTeams, 'myWorkgroups' => $myWorkgroups, 'currentContact' => $contactId);

        return $this->render('InternalMessageBundle:Inbox:inbox.html.twig', $return);
    }

    /**
     * Method to get json array for meassage inbox
     *
     * @param Request $request Request object
     *
     * @return JsonResponse
     */
    public function inboxMessagesAction(Request $request)
    {
        $contactId = $this->container->get('contact')->get('id');
        $clubId = $this->container->get('club')->get('id');
        $masterTable = $this->container->get('club')->get('clubTable');
        $clubType = $this->container->get('club')->get('type');
        $profileImgField = $this->container->getParameter('system_field_communitypicture');
        $companyLogoField = $this->container->getParameter('system_field_companylogo');
        $objMessage = new MessagePdo($this->container);
        $federationId = ($this->container->get('club')->get('type') != 'standard_club') ? $this->container->get('club')->get('federation_id') : $clubId;
        $rootPath = FgUtility::getRootPath($this->container);
        $offset = $request->get('start');
        /* get message details of contact */
        $messages = $objMessage->geMessagesOfContact($contactId, $clubId, $profileImgField, $companyLogoField, $masterTable, $offset, $this->limit, $clubType);
        /* insert profile image path of sender and last replier to the array */
        for ($i = 0; $i < count($messages); $i++) {
            $subFolder = ($messages[$i]['isCompanySender'] == 1) ? 'companylogo' : 'profilepic';
            $imageLocation = FgUtility::getUploadFilePath($federationId, $subFolder);
            $messages[$i]['senderImage'] = FgUtility::getContactImage($rootPath, $federationId, $messages[$i]['senderProfileImg'], 'width_65', '', $imageLocation);
            $subFolder2 = ($messages[$i]['isCompanyUpdated'] == 1) ? 'companylogo' : 'profilepic';
            $imageLocation2 = FgUtility::getUploadFilePath($federationId, $subFolder2);
            $messages[$i]['updatedImage'] = FgUtility::getContactImage($rootPath, $federationId, $messages[$i]['updatedProfileImg'], 'width_65', '', $imageLocation2);
        }
        $em = $this->getDoctrine()->getManager();
        $totalMessagesCount = $em->getRepository('CommonUtilityBundle:FgMessage')->getContactMessagesCount($contactId, $clubId);
        $return = array("aaData" => $messages, "iTotalRecords" => $totalMessagesCount, "iTotalDisplayRecords" => $totalMessagesCount);

        return new JsonResponse($return);
    }

    /**
     * Templete for confirmation pupup
     *
     * @param Request $request Request object
     *
     * @return Template
     */
    public function confirmationPopupAction(Request $request)
    {
        $messageIds = $request->get('messageIds');
        $selected = $request->get('selected');
        $isDraft = $request->get('isDraft');
        if ($messageIds) {
            $messageIdsArray = explode(",", $messageIds);
            if ($isDraft == "1") {
                if ($selected === "all") {
                    $popupTitle = $this->get('translator')->trans('MESSAGE_DELETE_DRAFT_TITLE_ALL');
                    $popupText = $this->get('translator')->trans('MESSAGE_DELETE_DRAFT_TEXT_ALL');
                } else if (count($messageIdsArray) > 1) {
                    $popupTitle = $this->get('translator')->trans('MESSAGE_DELETE_DRAFT_TITLE_MULTIPLE');
                    $popupText = $this->get('translator')->trans('MESSAGE_DELETE_DRAFT_TEXT_MULTIPLE');
                } else {
                    $popupTitle = $this->get('translator')->trans('MESSAGE_DELETE_DRAFT_TITLE_SINGLE');
                    $popupText = $this->get('translator')->trans('MESSAGE_DELETE_DRAFT_TEXT_SINGLE');
                }
            } else {
                if ($selected === "all") {
                    $popupTitle = $this->get('translator')->trans('MESSAGE_DELETE_TITLE_ALL');
                    $popupText = $this->get('translator')->trans('MESSAGE_DELETE_TEXT_ALL');
                } else if (count($messageIdsArray) > 1) {
                    $popupTitle = $this->get('translator')->trans('MESSAGE_DELETE_TITLE_MULTIPLE');
                    $popupText = $this->get('translator')->trans('MESSAGE_DELETE_TEXT_MULTIPLE');
                } else {
                    $popupTitle = $this->get('translator')->trans('MESSAGE_DELETE_TITLE_SINGLE');
                    $popupText = $this->get('translator')->trans('MESSAGE_DELETE_TEXT_SINGLE');
                }
            }
        }
        $return = array("title" => $popupTitle, 'text' => $popupText, 'messageIds' => $messageIds, 'isDraft' => $isDraft);

        return $this->render('InternalMessageBundle:Inbox:confirmationPopup.html.twig', $return);
    }

    /**
     * Function to delete a message
     *
     * @param Request $request Request object
     *
     * @return JsonResponse
     */
    public function deleteMessageAction(Request $request)
    {
        $messageIds = $request->get('messageIds');
        $isDraft = $request->get('isDraft');
        $contactId = $this->container->get('contact')->get('id');
        $em = $this->getDoctrine()->getManager();

        if ($isDraft === "1") {
            //DELETE draft message
            $messageIdArray = explode(',', $messageIds);
            $downloadPath = FgUtility::getUploadDir() . '/' . $this->get('club')->get('id') . '/users/messages/';
            if (count($messageIdArray) > 0) {
                //get the attachments of the message
                foreach ($messageIdArray as $message) {
                    //unlink the files
                    $messageAttachmentDetailArray = $em->getRepository('CommonUtilityBundle:FgMessageAttachments')->getMessageAttachmentsForMessage($message);
                    foreach ($messageAttachmentDetailArray as $attachment) {
                        unlink($downloadPath . $attachment['file']);
                    }
                    $em->getRepository('CommonUtilityBundle:FgMessage')->deleteMessage($message);
                }
            }
            $result = true;
        } else {
            $result = $em->getRepository('CommonUtilityBundle:FgMessageReceivers')->setMessageDeleted($messageIds, $contactId);
        }
        $return = array('status' => $result, 'flash' => $this->get('translator')->trans('MESSAGE_DELETED_SUCCESS'), 'noparentload' => true, 'isDraft' => $isDraft, 'deletedCount' => count(explode(",", $messageIds)));

        return new JsonResponse($return);
    }

    /**
     * Function to make a message unread
     *
     * @param Request $request Request object
     *
     * @return JsonResponse
     */
    public function unreadMessageAction(Request $request)
    {
        $messageIds = $request->get('messageIds');
        $contactId = $this->container->get('contact')->get('id');
        $em = $this->getDoctrine()->getManager();
        $resultArray = $em->getRepository('CommonUtilityBundle:FgMessageReceivers')->setMessageUnread($messageIds, $contactId);
        if (count(explode(",", $messageIds)) > 1) {
            $flasgMsg = $this->get('translator')->trans('MESSAGE_SET_UNREAD_PLURAL');
        } else {
            $flasgMsg = $this->get('translator')->trans('MESSAGE_SET_UNREAD_SINGULAR');
        }
        $return = array('status' => true, 'flash' => $flasgMsg, 'noparentload' => true, "resultArray" => $resultArray);

        return new JsonResponse($return);
    }

    /**
     * Function to make a message read
     *
     * @param Request $request Request object
     *
     * @return JsonResponse
     */
    public function readMessageAction(Request $request)
    {
        $messageIds = $request->get('messageIds');
        $contactId = $this->container->get('contact')->get('id');
        $em = $this->getDoctrine()->getManager();
        $resultArray = $em->getRepository('CommonUtilityBundle:FgMessageReceivers')->setMessagesRead($messageIds, $contactId);
        if (count(explode(",", $messageIds)) > 1) {
            $flasgMsg = $this->get('translator')->trans('MESSAGE_SET_READ_PLURAL');
        } else {
            $flasgMsg = $this->get('translator')->trans('MESSAGE_SET_READ_SINGULAR');
        }
        $return = array('status' => true, 'flash' => $flasgMsg, 'noparentload' => true, "resultArray" => $resultArray);

        return new JsonResponse($return);
    }

    /**
     * Method to get json array for draft meassage
     *
     * @param Request $request Request object
     *
     * @return JsonResponse
     */
    public function draftMessagesAction(Request $request)
    {
        $contactId = $this->container->get('contact')->get('id');
        $clubId = $this->container->get('club')->get('id');
        $objMessage = new MessagePdo($this->container);
        $offset = $request->get('start');
        $terminologyService = $this->container->get('fairgate_terminology_service');
        $executiveBoardTitle = $terminologyService->getTerminology('Executive Board', $this->container->getParameter('singular'));
        /* get draft details of contact */
        $messages = $objMessage->getDraftsOfContact($contactId, $clubId, $executiveBoardTitle, $offset, $this->limit);
        $em = $this->getDoctrine()->getManager();
        $countDrafts = $em->getRepository('CommonUtilityBundle:FgMessage')->getContactDraftsCount($contactId, $clubId);
        /* change format as '%count% more' */
        if (count($messages) > 0) {
            for ($i = 0; $i < count($messages); $i++) {
                $receivers = explode(",", $messages[$i]['receiverNames']);
                if (count($receivers) > 1) {
                    $messages[$i]['receiverNames'] = $receivers[0];
                    array_shift($receivers);
                    $messages[$i]['otherReceiverNames'] = implode(",", $receivers);
                    $messages[$i]['otherReceiversCount'] = count($receivers);
                }
            }
        }

        $return = array("aaData" => $messages, "iTotalRecords" => $countDrafts, "iTotalDisplayRecords" => $countDrafts, "start" => 0);

        return new JsonResponse($return);
    }

    /**
     * Function to set notification of a message
     *
     * @param Request $request Request object
     *
     * @return JsonResponse
     */
    public function setNotificationAction(Request $request)
    {
        $messageIds = $request->get('messageIds');
        $notificationStatus = $request->get('status');
        $contactId = $this->container->get('contact')->get('id');
        $em = $this->getDoctrine()->getManager();
        $resultArray = $em->getRepository('CommonUtilityBundle:FgMessageReceivers')->setNotification($messageIds, $contactId, $notificationStatus);
        if ($notificationStatus == 1) {
            $flasgMsg = $this->get('translator')->trans('MESSAGE_SET_NOTIFICATION');
        } else {
            $flasgMsg = $this->get('translator')->trans('MESSAGE_UNSET_NOTIFICATION');
        }
        $return = array('status' => true, 'flash' => $flasgMsg, 'noparentload' => true, "resultArray" => $resultArray, 'notificationStatus' => $notificationStatus);

        return new JsonResponse($return);
    }
}
