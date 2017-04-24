<?php

/**
 * EditorialDetails Controller
 */
namespace Internal\ArticleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Common\UtilityBundle\Util\FgUtility;

/**
 * EditorialDetails Controller
 *
 * This controller was created for handling the editorial details page
 *
 * @package    InternalArticleBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 *
 * @version  Fairgate V4
 *
 */
class EditorialDetailsController extends Controller
{

    /**
     * Function to get all the comments of an article
     *
     * @param int $articleId article id
     *
     * @return object \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getCommentsDataAction($articleId)
    {
        $data = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsArticleComments')->getCommentsOfArticle($articleId);
        $clubId = $this->container->get('club')->get('id');
        $pathService = $this->container->get('fg.avatar');
        $federationId = ($this->container->get('club')->get('type') != 'standard_club') ? $this->container->get('club')->get('federation_id') : $clubId;
        $rootPath = FgUtility::getRootPath($this->container);
        foreach ($data as $key => $commentData) {
            $subFolder = ($commentData['creatorIsCompany'] == 1) ? 'companylogo' : 'profilepic';
            $imageLocation = FgUtility::getUploadFilePath($federationId, $subFolder);
            $data[$key]['contactImage'] = FgUtility::getContactImage($rootPath, $federationId, $commentData['creatorProfileImg'], 'width_65', '', $imageLocation);
        }
        $articleDetails = $this->container->get('article.data')->getArticleDatas($articleId);
        $commentAllow = $articleDetails['article']['settings']['allowcomment'];
        $contactId = $this->get('contact')->get('id');
        $contactImage = $pathService->getAvatar($contactId, 65);
        $contactName = $this->get('contact')->get('nameNoSort');

        $clubSettings = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsArticleClubsetting')->findOneBy(array('club' => $clubId));
        $globalCommentAccess = empty($clubSettings) ? 0 : $clubSettings->getCommentActive();
        $isAdmin = 0;
        $contact = $this->container->get('contact');
        $isClubOrSuperAdmin = (count($contact->get('mainAdminRightsForFrontend')) > 0) ? 1 : 0;
        $isClubArticleAdmin = in_array('ROLE_ARTICLE', $contact->get('allRights')) ? 1 : 0;
        $availableUserRights = $this->container->get('contact')->get('availableUserRights');
        $isGroupAdmin = (count(array_intersect(array('ROLE_GROUP_ADMIN', 'ROLE_ARTICLE_ADMIN'), $availableUserRights)) > 0) ? 1 : 0;
        if ($isClubOrSuperAdmin == 1 || $isClubArticleAdmin == 1 || $isGroupAdmin == 1) {
            $isAdmin = 1;
        }

        return new JsonResponse(array('data' => $data, 'contactId' => $contactId, 'articleId' => $articleId, 'isCommentAllow' => $commentAllow, 'contactImage' => $contactImage, 'contactName' => $contactName, 'isAdmin' => $isAdmin, 'globalCommentAccess' => $globalCommentAccess));
    }

    /**
     * Function to save a particular comment
     *
     * @param object $request \Symfony\Component\HttpFoundation\Request
     *
     * @return object JSON Response Object
     */
    public function saveCommentsAction(Request $request)
    {
        $articleId = $request->get('articleId');
        $commentId = $request->get('commentId');
        $comment = $request->get('comment');
        $contactId = $this->get('contact')->get('id');
        $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsArticleComments')->saveComments($articleId, $commentId, $comment, $contactId);

        return new JsonResponse(array('status' => 'SUCCESS', 'commentId' => $commentId, 'flash' => $this->container->get('translator')->trans('EDITORIAL_COMMENT_SAVE_SUCCESS_MESSAGE'), 'noparentload' => 1));
    }

    /**
     * Function to show comments delete pop up
     *
     * @param object $request \Symfony\Component\HttpFoundation\Request
     *
     * @return object View Template Render Object
     */
    public function commentsDeleteConfirmationPopupAction(Request $request)
    {
        $commentId = $request->get('commentId');
        $return = array('commentId' => $commentId);

        return $this->render('InternalArticleBundle:EditorialDetails:editorialCommentDeleteConfirmationPopup.html.twig', $return);
    }

    /**
     * Function to deleta a particular comment
     *
     * @param object $request \Symfony\Component\HttpFoundation\Request
     *
     * @return object JSON Response Object
     */
    public function commentsDeleteAction(Request $request)
    {
        $commentId = $request->get('commentId');
        $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsArticleComments')->deleteComments($commentId);

        return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->container->get('translator')->trans('EDITORIAL_DELETE_COMMENT_SUCCESS_MESSAGE'), 'noparentload' => 1));
    }

    /**
     * Method to get text details of article
     *
     * @param int $articleId articleId
     *
     * @return object JSON Response Object
     */
    public function getTextAction($articleId)
    {
        $articleDataObj = $this->container->get('article.data');
        if ($articleId) {
            $result = $articleDataObj->getArticleText($articleId);
        }
        $club = $this->container->get('club');
        $result['clubLanguages'] = json_encode($club->get('club_languages'));
        $defLang = $club->get('club_default_lang');
        $result['defaultClubLang'] = $defLang;

        return new JsonResponse($result);
    }

    /**
     * Method to get media details of article
     *
     * @param int $articleId articleId
     *
     * @return object JSON Response Object
     */
    public function getMediaAction($articleId)
    {
        $articleDataObj = $this->container->get('article.data');
        if ($articleId) {
            $result = $articleDataObj->getArticleMedia($articleId);
        }
        $club = $this->container->get('club');
        $result['clubLanguages'] = json_encode($club->get('club_languages'));
        $defLang = $club->get('club_default_lang');
        $result['defaultClubLang'] = $defLang;

        return new JsonResponse($result);
    }

    /**
     * Method to get attachment details of article
     *
     * @param int $articleId articleId
     *
     * @return object JSON Response Object
     */
    public function getAttachmentsAction($articleId)
    {
        $articleDataObj = $this->container->get('article.data');
        if ($articleId) {
            $result = $articleDataObj->getArticleAttachments($articleId);
        }

        return new JsonResponse($result);
    }

    /**
     * Method to get settings of article
     *
     * @param int $articleId articleId
     *
     * @return object JSON Response Object
     */
    public function getSettingsAction($articleId)
    {
        $articleDataObj = $this->container->get('article.data');
        $articleObj = $this->container->get('article.create');
        if ($articleId) {
            $result = $articleDataObj->getArticleSettings($articleId);
        }
        $articleClubSettings = $articleObj->getArticleClubSettings();
        //handling case when no entry in artcle club settings
        $result['commentActive'] = (isset($articleClubSettings['commentActive'])) ? $articleClubSettings['commentActive'] : 1;
        $club = $this->container->get('club');
        $areas = $articleObj->getMyClubAndTeamsAndWorkgroups();
        $result['assignedTeams'] = json_encode($areas['teams']);
        $result['assignedWorkgroups'] = json_encode($areas['workgroups']);
        $result['clubTerminology'] = $areas['club'];
        $result['clubType'] = $club->get('type');
        $articleCategories = $articleObj->getAllArticleCategories();
        $result['category'] = json_encode($articleCategories);
        $result['isFrontend2Booked'] = (in_array('frontend2', $club->get('bookedModulesDet'))) ? 1 : 0;

        return new JsonResponse($result);
    }

    /**
     * Function to get all the log entries of an article
     *
     * @param int $articleId  article id
     *
     * @return object JSON Response Object
     */
    public function getEditorialLogEntriesAction($articleId)
    {
        $clubId = $this->get('club')->get('id');
        $data = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsArticleLog')->getLogDetailsofArticle($articleId, $clubId);
        $totalRecords = count($data);

        return new JsonResponse(array('aaData' => $data, 'iTotalRecords' => $totalRecords, 'iTotalDisplayRecords' => $totalRecords));
    }

    /**
     * Function to get all the history entries of an article
     *
     * @param int $articleId  article id
     *
     * @return object JSON Response Object
     */
    public function getRevisionAction($articleId)
    {
        $clubDefaultLanguage = $this->get('club')->get('club_default_lang');

        $articleTextObj = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsArticleText');
        if ($articleId) {
            $result = $articleTextObj->getArticleTextHistory($articleId, $clubDefaultLanguage);
        }

        return new JsonResponse(array('articleHistory' => $result));
    }

    /**
     * Function to update revision entry of an article
     *
     * @param int $articleId  article id
     * @param int $historyId  version history id
     *
     * @return object JSON Response Object
     */
    public function setCurrentRevisionAction($articleId, $historyId)
    {
        $articleDataObj = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsArticle');
        if ($historyId) {

            $articleDataObj->saveArticleTextVersion($historyId, $articleId);
        }

        return new JsonResponse(array('status' => 'SUCCESS', 'noparentload' => 1, 'flash' => $this->container->get('translator')->trans('ARTICLE_HISTORY_UPDATE')));
    }
}
