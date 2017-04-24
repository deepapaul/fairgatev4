<?php

/**
 * Forum Controller.
 *
 * This controller is used for forum section.
 *
 * @author     pitsolutions.ch
 *
 * @version    Fairgate V4
 */
namespace Internal\TeamBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Common\UtilityBundle\Repository\Pdo\ContactPdo;
use Common\UtilityBundle\Repository\Pdo\InternalTeamPdo;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Util\FgPermissions;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Internal\TeamBundle\Util\ForumlistData;
use Common\UtilityBundle\Util\FgSettings;

class ForumController extends Controller
{

    /**
     * Create a forum topic.
     *
     * @param Request $request Request object
     * @param int     $id      roleid
     *
     * @return template
     */
    public function createTopicAction(Request $request, $id)
    {
        $grpType = $request->get('module');
        $permissionObj = new FgPermissions($this->container);
        $permissionObj->checkClubAccess($id, $grpType . 's');

        $locale = $this->container->get('club')->get('default_system_lang');

        $backLink = ($grpType == 'team') ? $this->generateUrl('team_forum_views') : $this->generateUrl('workgroup_forum_views');
        $breadCrumb = array(
            'breadcrumb_data' => array(),
            'back' => $backLink,
        );

        return $this->render('InternalTeamBundle:Forum:createTopic.html.twig', array('locale' => $locale, 'backLink' => $backLink, 'breadCrumb' => $breadCrumb,
                'save_button_val' => $this->get('translator')->trans('CREATE'), 'discard_button_val' => $this->get('translator')->trans('CANCEL'),
                'second_btn_val' => $this->get('translator')->trans('PREVIEW'), 'second_btn_valedit' => $this->get('translator')->trans('EDIT'), 'grp_id' => $id, 'grpType' => $grpType,));
    }

    /**
     * save the forum topic - first topic.
     *
     * @param Request $request Request object
     *
     * @return JsonResponse
     */
    public function saveTopicAction(Request $request)
    {
        $role = $request->get('role');
        $roleType = $request->get('grpType'); //team/workgroup
        $clubId = $this->container->get('club')->get('id');
        $contactId = $this->container->get('contact')->get('id');
        $conn = $this->container->get('database_connection');
        $forumArray = json_decode($request->get('postArr'), true); // Getting save values from setting page in JSON format
        $forumArray['topic-title'] = FgUtility::getSecuredData($forumArray['topic-title'], $conn);
        $forumArray['forum-post-text'] = FgUtility::getSecuredData($forumArray['forum-post-text'], $conn);
        $em = $this->getDoctrine()->getManager();
        $newTopicId = $em->getRepository('CommonUtilityBundle:FgForumTopic')->saveNewTopic($forumArray, $clubId, $contactId, $role);
        if ($newTopicId) {
            $isDeactivated = $em->getRepository('CommonUtilityBundle:FgForumTopic')->isDeactivated($newTopicId);
            if (!$isDeactivated) {
                $this->addToNotificationSpool($request, $newTopicId, $clubId, $contactId, $role, 'newTopic', $roleType);
            }
            $redirect = ($roleType == 'team') ? $this->generateUrl('team_forum_views') : $this->generateUrl('workgroup_forum_views');

            return new JsonResponse(array('status' => 'SUCCESS', 'sync' => 1, 'redirect' => $redirect, 'flash' => $this->get('translator')->trans('FORUM_NEW_TOPIC_SUCCESS')));
        } else {
            return new JsonResponse(array('status' => 'SUCCESS', 'sync' => 1, 'redirect' => $redirect));
        }
    }

    /**
     * Forum list page.
     *
     * @param Request $request Request object
     * @param int     $roleId  roleid
     *
     * @return template
     */
    public function forumListdetailsAction(Request $request, $roleId = null)
    {
        $mod = $request->get('module');
        $adminFlag = 0;
        $userRights = array('ROLE_GROUP_ADMIN', 'ROLE_FORUM_ADMIN');
        $contactId = $this->container->get('contact')->get('id');
        $Grouprights = $this->container->get('contact')->get('clubRoleRightsRoleWise');
        $allGrouprights = array();
        foreach ($Grouprights as $key => $grts) {
            $allGrouprights[$key] = $grts['rights'][0];
        }
        $userrightsIntersect = array_intersect($userRights, $allGrouprights);
        $isAdmin = (count($this->container->get('contact')->get('mainAdminRightsForFrontend')) > 0) ? 1 : 0;
        if (count($userrightsIntersect) > 0 || $isAdmin == 1) {
            $adminFlag = 1;
        }
        $permissionObj = new FgPermissions($this->container);
        $accessCheckArray = ($mod == 'team') ? array('from' => 'forum', 'type' => 'teams', 'adminflag' => $adminFlag, 'groupid' => $roleId) : array('from' => 'forum', 'type' => 'workgroups', 'adminflag' => $adminFlag, 'groupid' => $roleId);
        $newTabs = $permissionObj->checkAreaAccess($accessCheckArray);
        //if unfollow link is set from notification mail, session is set as 'UNFOLLOWFORUM_SESSION'.
        //When session is set, set $unfollow, othrwise empty it. Also destroy session after setting unfollow
        $session = $request->getSession();
        $unfollowSession = $session->get('unfollowForum');
        if ($unfollowSession == 'UNFOLLOWFORUM_SESSION') {
            $unfollow = 'UNFOLLOWFORUM_SESSION';
            $session->set('unfollowForum', '');
        } else {
            $unfollow = '';
        }

        return $this->render('InternalTeamBundle:Forum:forumlist.html.twig', array('clubType' => $this->container->get('club')->get('type'), 'contactId' => $contactId, 'tabs' => $newTabs, 'teamCount' => count($newTabs), 'type' => $mod, 'clubId' => $this->container->get('club')->get('id'), 'url' => $this->generateUrl('forum_topic_list', array('groupId' => 'dummyId', 'groupCategory' => $mod)), 'isAdmin' => $adminFlag, 'perpageCount' => $this->container->getParameter('forumPostsPerPage'), 'id' => $roleId, 'unfollow' => $unfollow));
    }

    /**
     * Function to collect the topic details.
     *
     * @param Request $request       Request object
     * @param type    $groupId       id of team/workgroup
     * @param type    $groupCategory category
     *
     * @return JsonResponse
     */
    public function topiclistAction(Request $request, $groupId, $groupCategory)
    {
        $output = array('iTotalRecords' => 0, 'iTotalDisplayRecords' => 0, 'aaData' => array());
        $contactId = $this->container->get('contact')->get('id');
        $columns = array('1' => array('id' => 'title', 'name' => 'title'),
            '2' => array('id' => 'author', 'name' => 'author'),
            '3' => array('id' => 'replies', 'name' => 'replies'),
            '4' => array('id' => 'views', 'name' => 'views'),
            '5' => array('id' => 'last_reply', 'name' => 'lastReply'),
            '6' => array('id' => 'created_date', 'name' => 'createdDate'),
        );
        $forumListData = new ForumlistData($this->container, $contactId);
        $forumListData->aoColumns = $columns;
        $forumListData->sortColumnValue = $request->get('order', '');
        $forumListData->startValue = $request->get('start', '');
        $forumListData->displayLength = $request->get('length', '');
        $forumListData->defaultSystemLang = $this->get('club')->get('default_system_lang');
        $forumListData->groupId = $request->get('groupId', $groupId);
        $forumListData->memberlistType = $groupCategory;
        $forumListData->sortname = $request->get('sortName', '');

        //$forumListData->grouplistType = 'team';
        //check the admin flag
        $userRights = array('ROLE_GROUP_ADMIN', 'ROLE_CONTACT_ADMIN', 'ROLE_FORUM_ADMIN', 'ROLE_DOCUMENT_ADMIN');
        $allGrouprights = $this->container->get('contact')->get('clubRoleRightsRoleWise');
        $grouprights = array();
        if (in_array($groupId, array_keys($allGrouprights))) {
            $grouprights = $allGrouprights[$groupId]['rights'];
        }
        $userrightsIntersect = array_intersect($userRights, $grouprights);
        $adminFlag = false;
        $isAdmin = (count($this->container->get('contact')->get('mainAdminRightsForFrontend')) > 0) ? 1 : 0;
        if (count($userrightsIntersect) > 0 || $isAdmin == 1) {
            $adminFlag = true;
        }
        $forumListData->adminFlag = $adminFlag;
        $topiclistData = $forumListData->getForumlistData();
        $totalrecords = $topiclistData['totalcount'];
        //For set the datatable json array
        $output['iTotalRecords'] = $totalrecords;
        $output['iTotalDisplayRecords'] = $totalrecords;
        $output['aaData'] = $this->iterateDataTableData($topiclistData['data'], $groupCategory, $groupId);
        $output['isActivatedForum'] = $this->isActivatedForum($groupId);
        $output['isAdmin'] = $adminFlag;
        $output['isFollowTopic'] = $this->isFollowTopic($groupId, $this->container->get('club')->get('id'), $contactId);
        $output['createtopicUrl'] = ($groupCategory == 'team') ? $this->container->get('router')->generate('create_team_forum_topic', array('id' => $groupId)) : $this->container->get('router')->generate('create_wg_forum_topic', array('id' => $groupId));
        $output['searchUrl'] = ($groupCategory == 'team') ? $this->generateUrl('team_forum_search_list', array('groupId' => $groupId)) : $this->generateUrl('workgroup_forum_search_list', array('groupId' => $groupId));

        return new JsonResponse($output);
    }

    /**
     * Method to add entries to notification spool when a new topic is created or when new reply for a forum topic.
     *
     * @param Request $request   Request object
     * @param int     $topicId   Topic Id
     * @param int     $clubId    Current club-id
     * @param int     $contactId Current contact-id
     * @param int     $roleId    Current role-id
     * @param string  $forumType newTopic/newReply (when the notification mail is sent)
     * @param string  $roleType  team/workgroup
     * @param int     $uniqueId  Forum topic data-uniqueId
     */
    private function addToNotificationSpool($request, $topicId, $clubId, $contactId, $roleId, $forumType, $roleType, $uniqueId = 0)
    {
        $contactPdo = new ContactPdo($this->container);
        if ($forumType == 'newTopic') { //When adding a new topic
            $receiverEmails = $contactPdo->getForumFollowerEmails($clubId, $contactId, $roleId);
        } else { //When reply to a topic
            $receiverEmails = $contactPdo->getTopicFollowerEmails($topicId, $contactId);
        }
        if (count($receiverEmails) > 0) {
            $em = $this->getDoctrine()->getManager();
            $topicName = $em->getRepository('CommonUtilityBundle:FgForumTopic')->find($topicId)->getTitle();
            /* get parameters for template creation of notification mail */
            $emailTemplateParameters = $this->getEmailTemplateParameters($roleId, $topicName, $forumType, $roleType, $topicId, $uniqueId);            
            $noreplyEmail = $this->container->getParameter('noreplyEmail');
            $currentLocateSettings = array(0 => array('id' => $contactId, 'default_lang' => $this->container->get('club')->get('default_lang'), 'default_system_lang' => $this->container->get('club')->get('default_system_lang')));
            $baseurlArr = FgUtility::getMainDomainUrl($this->container, $this->container->get('club')->get('id')); //FAIR-2489
            $baseurl = $baseurlArr['baseUrl']; //Fair-2484   
            foreach ($receiverEmails as $contact => $contactEmail) {
                //set locale with respect to particular contact                
                $rowContactLocale = $contactPdo->getContactLanguageDetails($contact, $clubId, $this->container->get('club')->get('clubTable'), $this->container->get('club')->get('type'));
                $this->container->get('contact')->setContactLocale($this->container, $request, $rowContactLocale);
                //To set the club TITLE, SIGNATURE based on default language
                $this->container->get('contact')->setClubParamsOnLangauge($this->container->get('club'));
                
                //get salutation of contact
                $emailTemplateParameters['salutation'] = $em->getRepository('CommonUtilityBundle:FgCnNewsletter')->getSalutationText($contact, $clubId, $this->container->get('club')->get('default_system_lang'));                
                $emailTemplateParameters['clubTitle'] = $this->container->get('club')->get('title');
                $emailTemplateParameters['signature'] = $this->container->get('club')->get('signature');    
                $clubLogoPath = $this->container->get('club')->getClubLogoPath(false);
                $emailTemplateParameters['logoURL'] = ($clubLogoPath == '') ? '' : $baseurl . '/' . $clubLogoPath;
                //Build email template in the corresponding language
                $emailTemplateForNotification = $this->renderView('InternalGeneralBundle:MailTemplate:notificationMail.html.twig', $emailTemplateParameters);
                $em->getRepository('CommonUtilityBundle:FgNotificationSpool')->addNotificationEntries($contactEmail[0]['email'], $emailTemplateForNotification, $topicName, $noreplyEmail, 'FORUM');
                if ($forumType == 'newReply') { //When adding a new reply
                    $em->getRepository('CommonUtilityBundle:FgForumTopic')->updateNotificationSendDate($topicId, $contact);
                }
            }
            //reset contact locale with respect to logged in contact
            $this->container->get('contact')->setContactLocale($this->container, $request, $currentLocateSettings);
            //To set the club TITLE, SIGNATURE based on default language
            $this->container->get('contact')->setClubParamsOnLangauge($this->container->get('club'));
        }
    }

    /**
     * Method to get forum link and link to unfollow in forum notification mails (handled cases when new topic is created/ new reply is added).
     *
     * @param string $forumType newTopic/newReply
     * @param string $roleType  team/workgroup
     * @param int    $roleId    workgroupId/teamId
     * @param int    $topicId   Forum topic id
     * @param int    $uniqueId  Forum topic data-uniqueId
     *
     * @return array
     */
    private function getLinksinNotification($forumType, $roleType, $roleId, $topicId = '', $uniqueId = '')
    {
        $em = $this->getDoctrine()->getManager();
        $checkClubHasDomain = $em->getRepository('CommonUtilityBundle:FgDnClubDomains')->checkClubHasDomain($this->container->get('club')->get('id'));
        if ($forumType == 'newTopic') {
            if ($roleType == 'team') {
                $forumLink = FgUtility::generateUrlForHost($this->container, $this->container->get('club')->get('url_identifier'), 'team_forum_topic_view', $checkClubHasDomain, array('roleId' => $roleId, 'topicId' => $topicId, 'page' => 1));
                $unfollowLink = FgUtility::generateUrlForHost($this->container, $this->container->get('club')->get('url_identifier'), 'forum_unfollow_from_mail', $checkClubHasDomain, array('roleType' => 'team', 'roleId' => $roleId));
            } else {
                $forumLink = FgUtility::generateUrlForHost($this->container, $this->container->get('club')->get('url_identifier'), 'workgroup_forum_topic_view', $checkClubHasDomain, array('roleId' => $roleId, 'topicId' => $topicId, 'page' => 1));
                $unfollowLink = FgUtility::generateUrlForHost($this->container, $this->container->get('club')->get('url_identifier'), 'forum_unfollow_from_mail', $checkClubHasDomain, array('roleType' => 'workgroup', 'roleId' => $roleId));
            }
        } else {
            if ($roleType == 'team') {
                $unfollowLink = FgUtility::generateUrlForHost($this->container, $this->container->get('club')->get('url_identifier'), 'topic_update_follower_from_mail', $checkClubHasDomain, array('roleType' => 'team', 'roleId' => $roleId, 'topicId' => $topicId, 'followVal' => 0, 'unfollow' => 'unfollow'));
            } else {
                $unfollowLink = FgUtility::generateUrlForHost($this->container, $this->container->get('club')->get('url_identifier'), 'topic_update_follower_from_mail', $checkClubHasDomain, array('roleType' => 'workgroup', 'roleId' => $roleId, 'topicId' => $topicId, 'followVal' => 0, 'unfollow' => 'unfollow'));
            }
            $forumLink = FgUtility::generateUrlForHost($this->container, $this->container->get('club')->get('url_identifier'), 'forum_topic_pos_calc', $checkClubHasDomain, array('grp' => $roleType, 'grpId' => $roleId, 'topicId' => $topicId, 'id' => $uniqueId));
        }

        return array('link' => $forumLink, 'unfollowLink' => $unfollowLink);
    }

    /**
     * Method to get parameters for email template for notification sending on adding new forum topic/add reply to a topic.
     *
     * @param int    $roleId    TeamId/WorkgroupId
     * @param string $topicName Topic title
     * @param string $forumType newTopic/newReply  case where notification mail is sending
     * @param string $roleType  team/workgroup
     * @param int    $topicId   Current topic Id (used in case newReply)
     * @param int    $uniqueId  unique id of topic data (used in case newReply)
     *
     * @return array $emailTemplateParameters
     */
    private function getEmailTemplateParameters($roleId, $topicName, $forumType, $roleType, $topicId = 0, $uniqueId = 0)
    {        
        $roleName = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgRmRole')->find($roleId)->getTitle();
        if ($forumType == 'newTopic') {
            $links = $this->getLinksinNotification($forumType, $roleType, $roleId, $topicId);
        } else {  // case newReply
            $links = $this->getLinksinNotification($forumType, $roleType, $roleId, $topicId, $uniqueId);
        }
        $emailTemplateParameters = array(            
            'notifType' => 'forum',
            'createdBy' => $this->container->get('contact')->get('nameNoSort'),
            'forumType' => $forumType,
            'topicName' => $topicName,
            'teamName' => $roleName,
            'forumLink' => $links['link'],
            'unfollowLink' => $links['unfollowLink'],            
        );

        return $emailTemplateParameters;
    }

    /**
     * Function to display a topic's details.
     *
     * @param object $request Request object
     * @param int    $roleId  RoleId
     * @param int    $topicId Topic Id
     * @param int    $page    Page
     *
     * @return Template
     */
    public function topicViewAction(Request $request, $roleId, $topicId, $page = 1)
    {
        $em = $this->getDoctrine()->getManager();
        $club = $this->get('club');
        $contact = $this->get('contact');
        $clubId = $club->get('id');
        $contactId = $contact->get('id');
//      check whether forum is deactivated or not
        $roleObj = $em->getRepository('CommonUtilityBundle:FgRmRole')->find($roleId);
        if ($roleObj) {
            $isForumDeactive = ($roleObj->getIsDeactivatedForum() == 1) ? 1 : 0;
        }
        $isAdmin = $this->checkForumAdminRights($roleId);
        $isMember = $this->checkForumMembership($roleId);
        $permissionObj = new FgPermissions($this->container);
//      handle no access exception for unauthorized topic
        if (($isForumDeactive && !$isAdmin) || (!($isAdmin || $isMember))) {
            $permissionObj->checkUserAccess('', 'no_access', array('message' => 'have no access to this topic'));
        }
        $objInternalTeamPdo = new InternalTeamPdo($this->container);
        $topicDetails = $objInternalTeamPdo->getTopicDetails($clubId, $topicId, $page, $this->container->getParameter('forumPostsPerPage'));
        if (count($topicDetails) > 0) {
            $topicData = $topicDetails[0];
            $isOwner = ($topicData['createdById'] == $contactId) ? 1 : 0;
            if ($topicData['isClosed'] == 1 && !($isAdmin || $isOwner)) {
                $permissionObj = new FgPermissions($this->container);
                $permissionObj->checkUserAccess('', 'no_access', array('message' => 'have no access to this topic'));
            }
            //      insert or update fg_forum_contact_details
            $this->updateForumContactDetails($em, $topicId, $contactId);
            $totalPosts = $topicData['postCount'];
            $roleType = $topicData['roleType'];
            $topicSettingsRights = ($isAdmin || $isOwner);
            $pathService = $this->container->get('fg.avatar');
            $topicDatas = $this->getProfileImgPath($topicDetails);
            $pageTitleSettings = array('topicId' => $topicId, 'title' => $topicData['title'], 'isClosed' => $topicData['isClosed'], 'isImportant' => $topicData['isImportant'], 'isRepliesAllowed' => $topicData['isRepliesAllowed'], 'isFollower' => $topicData['isFollower'], 'topicSettingsMenu' => $topicSettingsRights, 'rightBlock' => true);
            $dataSet = array('data' => $topicDatas, 'roleId' => $roleId, 'topicId' => $topicId, 'page' => $page, 'totalCnt' => $totalPosts, 'settings' => $pageTitleSettings, 'isAdmin' => $isAdmin, 'contactId' => $contactId, 'roleType' => $roleType);
            $dataSet['dpp'] = $this->container->getParameter('forumPostsPerPage');
            $dataSet['isClosed'] = ($topicData['isClosed'] == 1) ? 1 : 0;
            $dataSet['isImportant'] = ($topicData['isImportant'] == 1) ? 1 : 0;
            $dataSet['isFollower'] = ($topicData['isFollower'] == 1) ? 1 : 0;
            $dataSet['isRepliesAllowed'] = ($topicData['isRepliesAllowed'] == 1) ? 1 : 0;
            $dataSet['isDeactivated'] = ($topicData['isDeactivated'] == 1) ? 1 : 0;
            $redirect = ($roleType == 'T') ? 'team_forum_views' : 'workgroup_forum_views';
            $dataSet['breadCrumb'] = array('back' => $this->generateUrl($redirect));
            $dataSet['backLink'] = $this->generateUrl($redirect);
            $dataSet['currentContact'] = $contact->get('nameNoSort');
            $dataSet['contactImage'] = $pathService->getAvatar($contactId, 150);
            $dataSet['contactIsCompany'] = $this->container->get('contact')->get('isCompany');
            $dataSet['locale'] = $this->container->get('club')->get('default_system_lang');
            $dataSet['save_button_val'] = $this->get('translator')->trans('FORUM_REPLY_BTN');
            $dataSet['preview_button_val'] = $this->get('translator')->trans('PREVIEW');
            $dataSet['module'] = $request->get('module');
            $dataSet['clubType'] = $this->container->get('club')->get('type');
            //if unfollow link is set from notification mail, settion is set as 'UNFOLLOWTOPIC_SESSION'.
            //When session is set, set unfollow, othrwise empty it. Also destroy session after setting unfollow
            $session = $request->getSession();
            $unfollowSession = $session->get('unfollowTopic');
            if ($unfollowSession == 'UNFOLLOWTOPIC_SESSION') {
                $dataSet['unfollow'] = 'UNFOLLOWTOPIC_SESSION';
                $session->set('unfollowTopic', '');
            } else {
                $dataSet['unfollow'] = '';
            }

            return $this->render('InternalTeamBundle:Forum:topicView.html.twig', $dataSet);
        } else {
            $permissionObj->checkClubAccess(null, '', $this->get('translator')->trans('FORUM_TOPIC_DOES_NOT_EXIST'));
        }
    }

    /**
     * Method to add profile image path( based on the contact, if he is company contact, take from companylogo folder or profilepic folder) to the input array
     * 
     * @param array $topicDetails input array containing topic details
     * 
     * @return array included imagePath with topic details
     */
    private function getProfileImgPath($topicDetails)
    {
        $federationId = ($this->container->get('club')->get('type') != 'standard_club') ? $this->container->get('club')->get('federation_id') : $this->container->get('club')->get('id');
        $rootPath = FgUtility::getRootPath($this->container);
        foreach ($topicDetails as $key => $value) {
            $subFolder = ($value['isCompany'] == 1) ? 'companylogo' : 'profilepic';
            $imageLocation = FgUtility::getUploadFilePath($federationId, $subFolder);
            $topicDetails[$key]['imagePath'] = FgUtility::getContactImage($rootPath, $federationId, $value['profileImg'], 'width_150', '', $imageLocation);
        }

        return $topicDetails;
    }

    /**
     * Function to update a contact's post read details.
     *
     * @param object $em        Entity manager object
     * @param int    $topicId   Topic Id
     * @param int    $contactId Contact Id
     */
    private function updateForumContactDetails($em, $topicId, $contactId)
    {
        //update views count if unread posts are there.
        $objInternalTeamPdo = new InternalTeamPdo($this->container);
        $unreadPostsCount = $objInternalTeamPdo->checkIfUnreadPostsAreThere($topicId, $contactId);
        if ($unreadPostsCount > 0) {
            $topicObj = $em->getRepository('CommonUtilityBundle:FgForumTopic')->find($topicId);
            $viewsCount = $topicObj->getViews();
            $topicObj->setViews($viewsCount + 1);
            $em->persist($topicObj);
        }
        $em->flush();

//      update last read time of contact for this topic
        $em->getRepository('CommonUtilityBundle:FgForumContactDetails')->updateLastReadTimeOfTopic($topicId, $contactId);
    }

    /**
     * Function to check whether topic admin privilage is there or not.
     *
     * @param int $roleId RoleId
     *
     * @return bool $hasRights
     */
    public function checkForumAdminRights($roleId = '')
    {
        $contact = $this->get('contact');
        //has topic settings menu if superadmin or clubadmin or group admin or forum admin
        $hasRights = (count($contact->get('mainAdminRightsForFrontend')) > 0) ? 1 : 0;
        if (!$hasRights) {
            $groupRights = $contact->get('clubRoleRightsRoleWise');
            $roleRights = (isset($groupRights[$roleId]['rights'])) ? $groupRights[$roleId]['rights'] : array();
            $hasRights = (in_array('ROLE_GROUP_ADMIN', $roleRights) || in_array('ROLE_FORUM_ADMIN', $roleRights)) ? 1 : 0;
        }

        return $hasRights;
    }

    /**
     * Function to check whether logged in contact has member rights in the current forum.
     *
     * @param int $roleId RoleId
     *
     * @return int $isMember Member Flag
     */
    public function checkForumMembership($roleId = '')
    {
        $isMember = 0;
        $contact = $this->get('contact');
        $groupRights = $contact->get('clubRoleRightsGroupWise');
        $memberArray = array_merge($groupRights['MEMBER']['teams'], $groupRights['MEMBER']['workgroups']);
        if (count($memberArray) > 0 && in_array($roleId, $memberArray)) {
            $isMember = 1;
        }

        return $isMember;
    }

    /**
     * Function to get post details on pagination.
     *
     * @param int $roleId  RoleId
     * @param int $topicId TopicId
     * @param int $page    Page
     *
     * @return JsonResponse
     */
    public function getTopicPostsAction($roleId, $topicId, $page)
    {
        $club = $this->get('club');
        $clubId = $club->get('id');
        $contactId = $this->get('contact')->get('id');
        $objInternalTeamPdo = new InternalTeamPdo($this->container);
        $topicDetails = $objInternalTeamPdo->getTopicDetails($clubId, $topicId, $page, $this->container->getParameter('forumPostsPerPage'));
        $topicData = $topicDetails[0];
        $totalPosts = $topicData['postCount'];
        $isAdmin = $this->checkForumAdminRights($roleId);
        $dataSet = array();
        $dataSet['topicDetails'] = $this->getProfileImgPath($topicDetails);
        $dataSet['page'] = $page;
        $dataSet['totalCnt'] = $totalPosts;
        $dataSet['dpp'] = $this->container->getParameter('forumPostsPerPage');
        $dataSet['isAdmin'] = $isAdmin;
        $dataSet['contactId'] = $contactId;

        return new JsonResponse($dataSet);
    }

    /**
     * find on which page the post is listed.
     *
     * @param Request $request Request object
     * @param string  $grp     team/wg
     * @param int     $grpId   role id
     * @param int     $topicId topic id
     *
     * @return redirectresponse url
     */
    public function getForumPostRedirectionAction(Request $request, $grp, $grpId, $topicId, $id = '')
    {
        $param = $request->get('param', 'magicid');
        $clubId = $this->container->get('club')->get('id');
        $em = $this->getDoctrine()->getManager();
        $contactId = $this->get('contact')->get('id');
        $flag = 0;
        $datetimeFormat = FgSettings::getPhpDateTimeFormat();
        if ($param == 'lastread' || $param == 'lastpost' || $param == 'magicid') {
            //case when unique id is provided-> redirect to oldest unread post
            $list = $em->getRepository('CommonUtilityBundle:FgForumTopic')->getListForumPost($grpId, $topicId, $clubId, $contactId);
            $listArray = array();
            if ($param == 'lastread') {
                foreach ($list as $key => $value) {
                    $createdAt = date_format($value['createdAt'], $datetimeFormat);
                    $readAt = date_format($value['readAt'], $datetimeFormat);
                    if ($createdAt > $readAt && !$flag) {
                        $flag = 1;
                        $uniqueId = $value['uniquePostId'];
                    }
                    if ($flag == 0) {
                        $uniqueId = $value['uniquePostId'];
                    }
                    $listArray[$key] = $value['uniquePostId'];
                }
            } elseif ($param == 'magicid') {
                foreach ($list as $key => $value) {
                    if ($value['id'] == $id) {
                        $uniqueId = $value['uniquePostId'];
                    }
                    $listArray[$key] = $value['uniquePostId'];
                }
            } else {
                foreach ($list as $key => $value) {
                    $uniqueId = $value['uniquePostId'];
                    $listArray[$key] = $value['uniquePostId'];
                }
            }
            $location = array_search($uniqueId, $listArray) + 1;
            $perPage = $this->container->getParameter('forumPostsPerPage');

            $inPage = ceil($location / $perPage);

            $urlArray = array('roleId' => $grpId, 'topicId' => $topicId, 'page' => $inPage);
            $url = ($grp == 'team') ? $this->generateUrl('team_forum_topic_view', $urlArray) : $this->generateUrl('workgroup_forum_topic_view', $urlArray);
            $url = $url . '#' . $uniqueId;
        } else {
            $urlArray = array('roleId' => $grpId, 'topicId' => $topicId, 'page' => $id);
            $url = ($grp == 'team') ? $this->generateUrl('team_forum_topic_view', $urlArray) : $this->generateUrl('workgroup_forum_topic_view', $urlArray);
        }

        return new RedirectResponse($url);
    }

    /**
     * For iterate the member list data.
     *
     * @param array  $topiclistDatas result data from the base query
     * @param string $groupCategory  selected type (team/workgroup)
     * @param int    $groupId        team/workgroup id
     *
     * @return type
     */
    private function iterateDataTableData($topiclistDatas, $groupCategory, $groupId)
    {
        $output['aaData'] = array();
        foreach ($topiclistDatas as $topicKey => $topiclistData) {
            // find the actual country from the country code
            $clickUrl = $this->container->get('router')->generate('forum_topic_redirect', array('grp' => $groupCategory, 'grpId' => $groupId, 'topicId' => $topiclistData['forumId'], 'id' => 'pageId'));
            $topiclistData['page_url'] = $clickUrl;
            $topiclistData['contact_overview_url'] = $this->container->get('router')->generate('internal_community_profile', array('contactId' => 'dummyId'));
            $topiclistData['topic_url'] = $this->container->get('router')->generate($groupCategory . '_forum_topic_view', array('roleId' => $groupId, 'topicId' => 'dummyTopic', 'page' => 1));
            $topiclistData['last_reply_url'] = $this->container->get('router')->generate('forum_topic_redirect_lastpost', array('grp' => $groupCategory, 'grpId' => $groupId, 'topicId' => $topiclistData['forumId']));
            $topiclistData['contactAdmin'] = ($this->container->get('contact')->get('isSuperAdmin') || (($this->container->get('contact')->get('isFedAdmin')) && ($this->container->get('club')->get('type') != 'federation'))) ? 1 : 0;
            $output['aaData'][] = $topiclistData;
        }

        return $output['aaData'];
    }

    /**
     * To activate/deactivate forum.
     *
     * @param Request $request Request object
     */
    public function activateForumAction(Request $request)
    {
        $roleId = $request->get('role');
        $status = $this->isActivatedForum($roleId);
        $newStatus = ($status == 1) ? 0 : 1;
        $em = $this->getDoctrine()->getManager();
        $em->getRepository('CommonUtilityBundle:FgForumTopic')->setActivateForum($roleId, $newStatus);
        exit();
    }

    /**
     * To follow/unfollow forum topic. (if from notification mail link, parameters will be set. Otherwise set as null).
     *
     * @param Request $request  Request object
     * @param string  $roleType team/workgroup
     * @param int     $roleId   team/workgroup id
     *
     * @return RedirectResponse or
     * @return JsonResponse
     */
    public function followForumAction(Request $request, $roleType = null, $roleId = null)
    {
        $groupId = ($roleId == null) ? $request->get('role') : $roleId;
        $contactId = $this->container->get('contact')->get('id');
        $isFollow = $this->isFollowTopic($groupId, $this->container->get('club')->get('id'), $contactId);
        $em = $this->getDoctrine()->getManager();
        $flashMsg = $this->get('translator')->trans('FORUM_UNFOLLOW_TOASTER');
        if ($isFollow > 0) { // If follower count is 1.
            $em->getRepository('CommonUtilityBundle:FgForumFollowers')->removeForumFollower($groupId, $this->container->get('club')->get('id'), $contactId);
        } elseif ($roleId == null) { //if request comes from notification mail link, follow forum is not required. So checking condition $roleId = null
            $em->getRepository('CommonUtilityBundle:FgForumFollowers')->addForumFollower($groupId, $this->container->get('club')->get('id'), $contactId);
            $flashMsg = $this->get('translator')->trans('FORUM_FOLLOW_MSG');
        }
        if (($roleType) && ($roleId)) {
            //if unfollow link is set from notification mail, session is set as 'UNFOLLOWFORUM_SESSION'. and redirect to forum list page
            $session = $request->getSession();
            $session->set('unfollowForum', 'UNFOLLOWFORUM_SESSION');
            if ($roleType == 'team') {
                $redirectUrl = $this->generateUrl('forum_view_team', array('roleId' => $roleId), true);
            } else {
                $redirectUrl = $this->generateUrl('forum_view_workgroup', array('roleId' => $roleId), true);
            }

            return new RedirectResponse($redirectUrl);
        } else {
            //work in case when clicking follow/unfollow from forum listing page

            return new JsonResponse(array('status' => 'SUCCESS', 'noparentload' => 1, 'flash' => $flashMsg));
        }
    }

    /**
     * To check whether the forum is activated.
     *
     * @return bool
     */
    private function isActivatedForum($roleId)
    {
        $em = $this->getDoctrine()->getManager();

        return $em->getRepository('CommonUtilityBundle:FgForumTopic')->isActivatedForum($roleId);
    }

    /**
     * To find topic followers.
     *
     * @return int
     */
    private function isFollowTopic($groupId, $clubId, $contactId)
    {
        $em = $this->getDoctrine()->getManager();
        $res = $em->getRepository('CommonUtilityBundle:FgForumTopic')->isFollowTopic($groupId, $clubId, $contactId);

        return $res[0][1];
    }

    /**
     * Method to save stpic reply.
     *
     * @param Request $request Request object
     *
     * @return JsonResponse
     */
    public function saveTopicReplyAction(Request $request)
    {
        $role = $request->get('role');
        $roleType = $request->get('grpType'); //team/workgroup
        $topicId = $request->get('topicId');
        $clubId = $this->container->get('club')->get('id');
        $contactId = $this->container->get('contact')->get('id');
        $forumArray = json_decode($request->get('postArr'), true); // Getting save values from setting page in JSON format
        $em = $this->getDoctrine()->getManager();
        $uniqueId = $em->getRepository('CommonUtilityBundle:FgForumTopic')->saveTopicReply($forumArray['forum-post-text'], $topicId, $contactId);
        if ($uniqueId) {
            $isDeactivated = $em->getRepository('CommonUtilityBundle:FgForumTopic')->isDeactivated($topicId);
            if (!$isDeactivated) {
                $this->addToNotificationSpool($request, $topicId, $clubId, $contactId, $role, 'newReply', $roleType, $uniqueId);
            }

            return new JsonResponse(array('status' => 'SUCCESS', 'sync' => 1, 'noparentload' => 1, 'flash' => $this->get('translator')->trans('FORUM_NEW_REPLY_SUCCESS')));
        } else {
            return new JsonResponse(array('status' => 'SUCCESS', 'sync' => 1, 'noparentload' => 1, 'flash' => $this->get('translator')->trans('FORUM_REPLY_NOT_ALLOWED')));
        }
    }

    /**
     * Search functionality Listing.
     *
     * @param Request $request Request object
     * @param int     $groupId group id
     *
     * @return template
     */
    public function searchListingAction(Request $request, $groupId)
    {
        $search = str_replace('\\', '\\\\\\\\', $request->get('term'));
        $grp = $request->get('module');
        $dataPath = $this->generateUrl('forum_search_result', array('groupId' => $groupId, 'grp' => $grp, 'search' => '##dummy##'));
        $perPage = $this->container->getParameter('forumPostsPerPage');
        $noResult = $this->get('translator')->trans('FORUM_NO_RESULT');
        $createdBy = $this->get('translator')->trans('FORUM_CREATED_BY');
        $on = $this->get('translator')->trans('FORUM_ON');
        $breadCrumb = ($grp == 'team') ? array('back' => $this->generateUrl('team_forum_views')) : array('back' => $this->generateUrl('workgroup_forum_views'));

        return $this->render('InternalTeamBundle:Forum:searchResult.html.twig', array('on' => $on, 'noResult' => $noResult, 'perPage' => $perPage,
                'groupId' => $groupId, 'grp' => $grp, 'search' => $search, 'dataPath' => $dataPath, 'createdBy' => $createdBy, 'breadCrumb' => $breadCrumb,));
    }

    /**
     * get result for search.
     *
     * @param Request $request Request object
     *
     * @return JsonResponse
     */
    public function searchResultAction(Request $request)
    {
        $output = array('iTotalRecords' => 0, 'iTotalDisplayRecords' => 0, 'aaData' => array());
        $search = $request->get('search');
        $groupId = $request->get('groupId');
        $groupCategory = $request->get('grp');
        $columns = array(
            '1' => array('id' => 'title', 'name' => 'title'),
            '2' => array('id' => 'author', 'name' => 'author'),
            '3' => array('id' => 'created_date', 'name' => 'createdDate'),
            '4' => array('id' => 'last_reply', 'name' => 'last_reply'),
            '5' => array('id' => 'post_content', 'name' => 'post_content'),
            '6' => array('id' => 'first_post_content', 'name' => 'first_post_content'),
        );

        //check the admin flag
        $userRights = array('ROLE_GROUP_ADMIN', 'ROLE_CONTACT_ADMIN', 'ROLE_FORUM_ADMIN', 'ROLE_DOCUMENT_ADMIN');
        $allGrouprights = $this->container->get('contact')->get('clubRoleRightsRoleWise');
        $grouprights = array();
        if (in_array($groupId, array_keys($allGrouprights))) {
            $grouprights = $allGrouprights[$groupId]['rights'];
        }
        //$teamRight = $this->container->get('contact')->checkClubRoleRights($contactId, false);
        $userrightsIntersect = array_intersect($userRights, $grouprights);
        $adminFlag = false;
        $isAdmin = (count($this->container->get('contact')->get('mainAdminRightsForFrontend')) > 0) ? 1 : 0;

        if (count($userrightsIntersect) > 0 || $isAdmin == 1) {
            $adminFlag = true;
        }
        $contactId = $this->container->get('contact')->get('id');
        $forumListData = new ForumlistData($this->container, $contactId);
        $forumListData->adminFlag = $adminFlag;
        $forumListData->aoColumns = $columns;
        $forumListData->defaultSystemLang = $this->get('club')->get('default_system_lang');
        $forumListData->groupId = $request->get('groupId', $groupId);
        $forumListData->memberlistType = $groupCategory;
        $forumListData->searchVal = $search;
        $forumListData->groupByColumn = 'forumId';
        $forumListData->searchFlag = true;
        //check the admin flag
        $topiclistData = $forumListData->getForumlistData();
        $totalrecords = $topiclistData['totalcount'];

        //For set the datatable json array
        $output['iTotalRecords'] = $totalrecords;
        $output['iTotalDisplayRecords'] = $totalrecords;
        $output['aaData'] = $this->iterateDataTableData($topiclistData['data'], $groupCategory, $groupId);

        return new JsonResponse($output);
    }

    /**
     * Templete for delete confirmation popup.
     *
     * @param Request $request        Request object
     * @param int     $topicContentId Forum topic content Id
     *
     * @return Template
     */
    public function topicContentDeleteConfirmationAction(Request $request, $topicContentId, $type)
    {
        if ($type == 'forum') {
            $popupTitle = $this->get('translator')->trans('FORUM_TOPIC_DELETE_TITLE');
            $popupText = $this->get('translator')->trans('FORUM_TOPIC_DELETE_TEXT');
        } elseif ($type == 'content') {
            $uniqueId = $request->get('uniqueid');
            $popupTitle = str_replace('%postid%', $uniqueId, $this->get('translator')->trans('FORUM_TOPIC_CONTENT_DELETE_TITLE'));
            $popupText = $this->get('translator')->trans('FORUM_TOPIC_CONTENT_DELETE_TEXT');
        }
        $return = array('type' => $type, 'title' => $popupTitle, 'text' => $popupText, 'topicContentId' => $topicContentId);

        return $this->render('InternalTeamBundle:Forum:removeconfirmationPopup.html.twig', $return);
    }

    /**
     * Method to delete Topic content.
     *
     * @param int $topicContentId Forum topic content Id
     *
     * @return JsonResponse
     */
    public function topicContentDeleteAction($topicContentId, $type)
    {
        $em = $this->getDoctrine()->getManager();
        if ($type == 'forum') {
            $em->getRepository('CommonUtilityBundle:FgForumTopic')->removeForum($topicContentId);
            $message = $this->get('translator')->trans('FORUM_DELETE_SUCCESS');
        } elseif ($type == 'content') {
            $contactId = $this->container->get('contact')->get('id');
            $em->getRepository('CommonUtilityBundle:FgForumTopicData')->removeTopicContent($topicContentId, $contactId);
            $message = $this->get('translator')->trans('FORUM_TOPIC_CONTENT_DELETE_SUCCESS');
        }

        return new JsonResponse(array('message' => $message, 'type' => $type));
    }

    /**
     * Method to edit topic reply.
     *
     * @param Request $request Request object
     *
     * @return JsonResponse
     */
    public function editTopicReplyAction(Request $request)
    {
        $dataId = $request->get('dataId');
        $uniqueId = $request->get('uniqueId');
        $contactId = $this->container->get('contact')->get('id');
        $content = $request->get('content');
        $em = $this->getDoctrine()->getManager();
        $return['updatedDate'] = $em->getRepository('CommonUtilityBundle:FgForumTopic')->editTopicReply($content, $dataId, $contactId);
        $return['updatedBy'] = $this->container->get('contact')->get('nameNoSort');
        $return['dataId'] = $dataId;
        $flashMsg = ($uniqueId == '1') ? $this->get('translator')->trans('FORUM_TOPIC_EDIT_SUCCESS') : $this->get('translator')->trans('FORUM_REPLY_EDIT_SUCCESS');

        return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $flashMsg, 'noparentload' => 1, 'returnArray' => $return));
    }

    /**
     * Topic settings menu.
     *
     * @param int    $topicId    Forum topic Id
     * @param int    $checkedVal Forum topic Checked Value
     * @param string $chkType    Forum topic Checked Type
     *
     * @return JsonResponse
     */
    public function forumSettingAction($topicId, $checkedVal, $chkType)
    {
        $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgForumTopic')->settingEditForum($topicId, $checkedVal, $chkType);
        if (($chkType == 'isImportant') && ($checkedVal == 1)) {
            $message = $this->get('translator')->trans('TOPIC_IMPORTANT_SET_SUCCESS');
        } elseif (($chkType == 'isImportant') && ($checkedVal == 0)) {
            $message = $this->get('translator')->trans('TOPIC_IMPORTANT_UNSET_SUCCESS');
        } elseif (($chkType == 'isClosed') && ($checkedVal == 1)) {
            $message = $this->get('translator')->trans('TOPIC_CLOSE_SUCCESS');
        } elseif (($chkType == 'isClosed') && ($checkedVal == 0)) {
            $message = $this->get('translator')->trans('TOPIC_UNCLOSE_SUCCESS');
        }

        return new JsonResponse($message);
    }

    /**
     * Topic Follow/Unfollow edit.
     *
     * @param Request $request   Request object
     * @param string  $roleType  team/workgroup
     * @param int     $roleId    team/workgroup id
     * @param int     $topicId   Forum topic Id
     * @param int     $followVal Forum topic follow/unfollow Value
     * @param string  $unfollow  string 'unfollow' in case: unfollow link from notification mail. Otherwise null
     *
     * @return JsonResponse
     * @return RedirectResponse
     */
    public function forumUpdateFollowerAction(Request $request, $roleType, $roleId, $topicId, $followVal, $unfollow = null)
    {
        $contactId = $this->container->get('contact')->get('id');
        $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgForumTopic')->followerEditForum($topicId, $contactId, $followVal);

        if ($unfollow == null) {
            $message = ($followVal == 1) ? $this->get('translator')->trans('FORUM_FOLLOW_EDIT_SUCCESS') : $this->get('translator')->trans('FORUM_UNFOLLOW_EDIT_SUCCESS');

            return new JsonResponse($message);
        } else {
            //if unfollow link is set from notification mail, session is set as 'UNFOLLOWTOPIC_SESSION'.
            $session = $request->getSession();
            $session->set('unfollowTopic', 'UNFOLLOWTOPIC_SESSION');
            if ($roleType == 'team') {
                $redirectUrl = $this->generateUrl('team_forum_topic_view', array('roleId' => $roleId, 'topicId' => $topicId, 'page' => 1), true);
            } else {
                $redirectUrl = $this->generateUrl('workgroup_forum_topic_view', array('roleId' => $roleId, 'topicId' => $topicId, 'page' => 1), true);
            }

            return new RedirectResponse($redirectUrl);
        }
    }

    /**
     * Topic Replies Allowed/Not Allowed.
     *
     * @param int    $topicId     Forum topic Id
     * @param string $repliesData Forum topic replies Allowed/Not Allowed
     *
     * @return JsonResponse
     */
    public function forumRepliesEditAction($topicId, $repliesData)
    {
        $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgForumTopic')->forumRepliesEdit($topicId, $repliesData);
        $message = $this->get('translator')->trans('FORUM_REPLIES_CHANGE_SUCCESS');

        return new JsonResponse($message);
    }
}
