<?php

/**
 * OverviewController
 *
 * This controller used for managing the personal dashboard for internal calendar area
 *
 * @package    InternalProfileBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
namespace Internal\ProfileBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Common\UtilityBundle\Repository\Pdo\ContactPdo;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Repository\Pdo\MessagePdo;
use Common\UtilityBundle\Repository\Pdo\DocumentPdo;
use Clubadmin\DocumentsBundle\Util\Documentlist;
use Common\UtilityBundle\Repository\Pdo\InternalTeamPdo;
use Internal\CalendarBundle\Util\CalenderEvents;
use Common\UtilityBundle\Repository\Pdo\CalendarPdo;
use Internal\CalendarBundle\Util\CalendarRecurrence;
use Internal\ArticleBundle\Util\ArticlesList;
use Clubadmin\Util\Contactlist;
use Clubadmin\DocumentsBundle\Util\DocumentDetails;
use DateTime;

class OverviewController extends Controller
{

    /**
     * Function to display the personal overview page in internal area
     *
     * @return template
     */
    public function indexAction()
    {
        $contact = $this->get('contact');
        $loggedContactId = $contact->get('id');
        $clubId = $this->container->get('club')->get('id');
        $em = $this->getDoctrine()->getManager();
        $contactDetails = $em->getRepository('CommonUtilityBundle:FgCmContact')->getNameOfAContact($loggedContactId);
        $loggedContactName = $contactDetails[0]['name'];
        $isSuperAdmin = ($contact->get('isSuperAdmin') || (($contact->get('isFedAdmin')) && ($this->container->get('club')->get('type') != 'federation'))) ? 1 : 0;
        $contacttype = ($contact->get('isCompany') == 1) ? 'Company' : 'Single person';
        $conn = $this->container->get('database_connection');
        $systemFieldsArray = $this->get('club')->get('systemFields');
        $fedContactid = $contact->get('fedContactId');
        $contactDetails = $this->getFieldDetailsofLoggedcontact($systemFieldsArray, $contacttype, $loggedContactId, $fedContactid);
        $clubIdArray = $this->getClubArray();
        // Function to get Teams and workgroups of the contact
        $assignedTeamsandWorkgroups = $em->getRepository('CommonUtilityBundle:FgRmCategory')->getAllAssignedCategories($clubIdArray, $conn, $loggedContactId, 0, false, 1);
        foreach ($assignedTeamsandWorkgroups as $key => $value) {
            if ($assignedTeamsandWorkgroups[$key]['is_team'] == 1) {
                $assignedTeamsandWorkgroups[$key]['Url'] = $this->generateUrl('team_overview');
            } elseif ($assignedTeamsandWorkgroups[$key]['is_workgroup'] == 1) {
                $assignedTeamsandWorkgroups[$key]['Url'] = $this->generateUrl('workgroup_overview');
            }
            $assignedTeamsandWorkgroups[$key]['contactId'] = $loggedContactId;
        }
        $accessibleClubs = $this->getClubsAccessibleToAContact($em, $contact, $this->container);
        $resize = ($contact->get('isCompany') == 1) ? 150 : 150;
        // $contactDetails['imagePath'] = FgUtility::getContactImage($rootPath, $contactDetails['createdclub'], $contactDetails['profilepicture'], '', 'communityProfile',$imageLocation);
        $pathService = $this->container->get('fg.avatar');
        $contactDetails['imagePath'] = $pathService->getAvatar($loggedContactId, $resize);

        $contactDetails['isSuperAdmin'] = $isSuperAdmin;
        $contactDetails['mydataPath'] = $this->generateUrl('internal_mydata');
        $contactDetails['isCompanyContact'] = $contact->get('isCompany');
        $contactDetails['tabs'] = ($isSuperAdmin) ? array() : array(0 => array("text" => $this->get('translator')->trans('INTERNAL_OVERVIEW_TAB_TITLE'), "url" => $this->generateUrl('internal_dashboard')), 1 => array("text" => $this->get('translator')->trans('INTERNAL_DATA_TAB_TITLE'), "url" => $this->generateUrl('internal_mydata')), 2 => array("text" => $this->get('translator')->trans('INTERNAL_SETTINGS_TAB_TITLE'), "url" => $this->generateUrl('internal_privacy_settings')));
        return $this->render('InternalProfileBundle:Overview:overview.html.twig', array('contactId' => $loggedContactId, 'contactDetails' => $contactDetails, 'assignedTeamsandWorkgroups' => $assignedTeamsandWorkgroups, 'accessibleClubs' => $accessibleClubs, 'contactName' => $loggedContactName, 'currentClubId' => $clubId));
    }

    /**
     * Function to get the profile details of logged in contact
     *
     * @param array  $systemFieldsArray array that contains active system fields
     * @param string $contacttype       logged in contact type whether company or single person
     * @param int    $loggedContactId   logged contact id
     *
     * @return array $contactProfileDetails
     */
    private function getFieldDetailsofLoggedcontact($systemFieldsArray, $contacttype, $loggedContactId, $fedContactid)
    {

        $fields = array('street' => $this->container->getParameter('system_field_corres_strasse'),
            'zipcode' => $this->container->getParameter('system_field_corres_plz'),
            'location' => $this->container->getParameter('system_field_corres_ort'),
            'primaryEmail' => $this->container->getParameter('system_field_primaryemail'),
            'mobile' => $this->container->getParameter('system_field_mobile1')
        );

        $resultFieldIds = array();
        foreach ($fields as $key => $value) {
            if ($systemFieldsArray[$value]['is_visible_contact'] == 1) {
                $resultFieldIds[$key] = $systemFieldsArray[$value]['id'];
            }
        }

        $pdo = new ContactPdo($this->container);
        $contactProfileDetails = $pdo->getLoggedProfileDetails($resultFieldIds, $fedContactid, $loggedContactId, $contacttype);
        return $contactProfileDetails;
    }

    /**
     * Function to get the  club details array
     *
     * @return type array
     */
    private function getClubArray()
    {
        $club = $this->get('club');
        $container = $this->container->getParameterBag();
        $clubIdArray = array('clubId' => $club->get('id'),
            'federationId' => $club->get('federation_id'),
            'subFederationId' => $club->get('sub_federation_id'),
            'clubType' => $club->get('type'),
            'correspondanceCategory' => $container->get('system_category_address'),
            'invoiceCategory' => $container->get('system_category_invoice'));
        $clubIdArray['address'] = $this->get('translator')->trans('CONTACT_FIELD_ADDRESS');
        $clubIdArray['sysLang'] = $club->get('default_lang');
        $clubIdArray['defSysLang'] = $club->get('default_system_lang');
        $clubIdArray['defaultClubLang'] = $club->get('default_lang');
        $clubIdArray['clubLanguages'] = $club->get('club_languages');

        return $clubIdArray;
    }

    /**
     * Function to get all connections of the logged in contact
     *
     * @return JsonResponse Connections details
     */
    public  function getConnectionsAction()
    {
        $em = $this->getDoctrine()->getManager();
        $contact = $this->get('contact');
        $contactId = $contact->get('id');
        $contactFedid = $contact->get('fedContactId');
        $isCompany = $contact->get('isCompany');
        $contactPdo = new ContactPdo($this->container);
        $connections = $contactPdo->getMyConnections($contactId);
        $mainContact = array();
        $myCompanies = array();
        if ($isCompany) {
            $mainContact = $contactPdo->getMainContact($contactId);
        } else {
            $myCompanies = $contactPdo->getCompaniesOfAContact($contactFedid);
        }
        $connections = array_merge($connections, $myCompanies, $mainContact);
        $myConnections = array();
        $overviewPath = $this->generateUrl('internal_community_profile', array('contactId' => 'dummy'));
        $rootPath = FgUtility::getRootPath($this->container);
        $federationId = ($this->container->get('club')->get('type') != 'standard_club') ? $this->container->get('club')->get('federation_id') : $this->container->get('club')->get('id');
        foreach ($connections as $connection) {
            $connection['overviewUrl'] = ($connection['is_stealth_mode'] == 0) ? (str_replace('dummy', $connection['contactId'], $overviewPath)) : '#';
            $subFolder = ($connection['isCompany'] == 1) ? 'companylogo' : 'profilepic';
            $imageLocation = FgUtility::getUploadFilePath($federationId, $subFolder);
            $connection['imgPath'] = FgUtility::getContactImage($rootPath, $federationId, $connection['profilePic'], 'width_65', '', $imageLocation);
            $myConnections[] = $connection;
        }

        return new JsonResponse($myConnections);
    }

    /**
     * Function for getting the next 7 bithday details in personal/team/workgroup overview
     *
     * @param object $request request object
     *
     * @return JsonResponse
     */
    public function getNextBirthdayOverviewAction(Request $request)
    {
        $roleType = $request->get('roleType');
        $roleId = $request->get('roleId');
        $club = $this->get('club');
        $clubId = $club->get('id');
        $categoryId = ($roleType == "team") ? $club->get('club_team_id') : (($roleType == "workgroup") ? $club->get('club_workgroup_id') : "sss");
        $container = $this->container;
        $contactlistClass = new Contactlist($container, '', $club);
        $userRights = $club->get('allowedRights');
        $contactRights = ($userRights['contact']) ? 1 : 0;
        $contactPdo = new ContactPdo($container);
        $nextBirthDays = $contactPdo->getNextBirthDaysFromContactList($contactlistClass, $container, $roleType, $roleId, $categoryId, $clubId);
        for ($i = 0; $i < count($nextBirthDays); $i++) {
            if ($nextBirthDays[$i]['nextBirthDay'] === date('d.m.Y')) {
                $nextBirthDays[$i]['nextBirthDay'] = $this->get('translator')->trans('DASHBOARD_TODAY');
            } else {
                $nextBirthDays[$i]['nextBirthDay'] = $container->get('club')->formatDate($nextBirthDays[$i]['nextBirthDay'], 'date', 'd.m.Y');
            }
            $nextBirthDays[$i]['contactRights'] = $contactRights;
            $contactsIdsArray = explode(",", $nextBirthDays[$i]['contacts']);
            $contactsArray = $this->getArrayOfContactDetails($contactsIdsArray);
            $nextBirthDays[$i]['contacts'] = $contactsArray;
            $nextBirthDays[$i]['contactsNumber'] = count($contactsArray);
        }
        $textShowAll = $this->get('translator')->trans('DASHBOARD_SHOW_ALL');
        $textShowLess = $this->get('translator')->trans('DASHBOARD_SHOW_LESS');
        $return = array("birthdayDetails" => $nextBirthDays, "textShowAll" => $textShowAll, "textShowLess" => $textShowLess, "roleType" => $roleType, "roleId" => $roleId);

        return new JsonResponse($return);
    }

    /**
     * Function to return array of name, contact-overview url, age of each contacts
     *
     * @param array $contactsIdsArray array of string for each contact
     *
     * @return array
     */
    private function getArrayOfContactDetails($contactsIdsArray)
    {
        $contactsArray = array();
        $c = 0;
        foreach ($contactsIdsArray as $contact) {
            $c++;
            $classname = ($c <= 5) ? "" : "fg-bithday-contact hide";
            $contactDetails = explode("~", $contact);
            $path = $this->generateUrl('internal_community_profile', array('contactId' => $contactDetails[2]));
            array_push($contactsArray, array("name" => $contactDetails[0], "path" => $path, "age" => $contactDetails[1], 'classname' => $classname, 'isStealthMode' => $contactDetails[3]));
        }

        return $contactsArray;
    }

    /**
     * Function to display the messages section in personal overview
     *
     * @return JsonResponse
     */
    public function getMessagesOverviewAction()
    {
        $contactId = $this->container->get('contact')->get('id');
        $clubId = $this->container->get('club')->get('id');
        $clubTable = $this->container->get('club')->get('clubTable');
        $clubType = $this->container->get('club')->get('type');
        $profileImgField = $this->container->getParameter('system_field_communitypicture');
        $companyLogoField = $this->container->getParameter('system_field_companylogo');
        $rootPath = FgUtility::getRootPath($this->container);
        $messagePdo = new MessagePdo($this->container);
        $unreadMessages = $messagePdo->geUnreadMessagesOfContact($contactId, $clubId, $profileImgField, $companyLogoField, $clubTable, $clubType);
        $federationId = ($clubType != 'standard_club') ? $this->container->get('club')->get('federation_id') : $clubId;

        foreach ($unreadMessages as $key => $value) {
            $unreadMessages[$key]["profileLink"] = $this->generateUrl('internal_community_profile', array('contactId' => $value['contactId']));
            //FAIR-2489
            $unreadMessages[$key]['message'] = FgUtility::correctCkEditorUrl($unreadMessages[$key]['message'], $this->container, $clubId);
            $unreadMessages[$key]["conversationLink"] = $this->generateUrl('internal_message_conversation', array('messageId' => $value['id']));
            $subFolder = ($value['isCompanySender'] == 1) ? 'companylogo' : 'profilepic';
            $imageLocation = FgUtility::getUploadFilePath($federationId, $subFolder);
            $unreadMessages[$key]["imageLink"] = FgUtility::getContactImage($rootPath, $federationId, $value['senderProfileImg'], 'width_65', '', $imageLocation);
        }
        return new JsonResponse($unreadMessages);
    }

    /**
     * Function to return the links of clubs which are accessible to logged contact
     *
     * @param object $em        entity manager
     * @param object $contact   object
     * @param object $container object
     *
     * @return array
     */
    private function getClubsAccessibleToAContact($em, $contact, $container)
    {
        $club = $container->get('club');
        $contactId = $contact->get('id');
        $contactFedId = $contact->get('fedContactId');
        $clubId = $club->get('id');
        $accessibleClubs = array();

        $clubLevels = $em->getRepository('CommonUtilityBundle:FgCmContact')->getFedContactAssignedClubs($contactFedId, $club->get('default_lang'));
        if (count($clubLevels) > 0) {

            foreach ($clubLevels as $key => $clubLevel) {
                if ($clubLevel['Club_id'] == $clubId) {
                    unset($clubLevels[$key]);
                }
            }

            $contactPdo = new ContactPdo($container);
            $clubLevels = array_values($clubLevels);
            if (count($clubLevels) > 0) {
                foreach ($clubLevels as $clubLevel) {
                    $clubTable = ($clubLevel['club_type'] == 'federation' || $clubLevel['club_type'] == 'sub_federation') ? 'master_federation_' : 'master_club_';
                    $clubTable .= $clubLevel['Club_id'];
                    $hasAccessToClub = $contactPdo->checkWhetherContactHasAccessToClubFrontend($contactId, $clubLevel['Club_id'], $clubLevel['fedContactId'], $clubLevel['subFedContactId'], $clubTable, $clubLevel['club_type']);
                    if ($hasAccessToClub) {
                        //FAIR-2489
                        $checkClubHasDomain = $em->getRepository('CommonUtilityBundle:FgDnClubDomains')->checkClubHasDomain($clubLevel['Club_id']);
                        $routePath = str_replace('/' . $club->get('url_identifier') . '/', '/', $container->get('router')->generate('internal_dashboard'));
                        if ($checkClubHasDomain) {
                            $clubUrl = $checkClubHasDomain['domain'] . $routePath;
                            ;
                        } else {
                            $baseUrl = $container->getParameter('base_url');
                            $clubUrl = $baseUrl . '/' . $clubLevel['url_identifier'] . $routePath;
                        }
                        $accessibleClubs[] = array('title' => $clubLevel['title'], 'url' => $clubUrl);
                    }
                }
            }
        }
        return $accessibleClubs;
    }

    /**
     * Function to display the documents section in personal overview
     *
     * @return JsonResponse
     */
    public function documentslistingOverviewAction()
    {
        $terminologyService = $this->get('fairgate_terminology_service');
        $clubBadge = $terminologyService->getTerminology('Club', $this->container->getParameter('singular'));
        $contactBadge = $this->get('translator')->trans('DASHBOARD_DOCUMENT_CONTACT');

        //my teams and workgroups
        $docDetails = new DocumentDetails($this->container);
        $myTeams = $docDetails->getMyDocumentRoles('team', true);
        $myWorkgroups = $docDetails->getMyDocumentRoles('workgroup', true);
        $myRoles = array_replace($myTeams, $myWorkgroups);
        $myRoleIds = array_keys($myRoles);

        $mysqldate = $this->get('club')->get('mysqldate');
        $mysqltime = $this->get('club')->get('mysqltime');

        //data columns
        $aColumns = array('DISTINCT(fdd.id)', 'fdd.document_type', 'fdv.id AS versionId', 'DATE_FORMAT(fdv.updated_at,"' . $mysqldate . '" ) AS updatedDate', 'fdv.updated_at', 'DATE_FORMAT( fdv.updated_at,"' . $mysqltime . '" ) AS updatedTime', 'fdv.size', 'fdv.file', 'fdd.deposited_with AS depositedWith', '(IF(fdcs.contact_id IS NULL, 1, 0)) AS isUnread', 'fda.role_id AS roleId');
        $aColumns[] = '(CASE WHEN (fdi18.name_lang IS NULL OR fdi18.name_lang = "") THEN fdd.name ELSE fdi18.name_lang END) AS docName';
        if (count($myRoleIds) > 0) {
            $aColumns[] = "(CASE WHEN fdd.document_type = 'CLUB' THEN '$clubBadge' WHEN fdd.document_type = 'CONTACT' THEN '$contactBadge' WHEN ((fdd.document_type = 'TEAM' OR fdd.document_type = 'WORKGROUP') AND fdd.deposited_with = 'SELECTED') THEN "
                . "(SELECT GROUP_CONCAT(DISTINCT dma.role_id SEPARATOR '~#~') FROM fg_dm_assigment dma WHERE dma.document_id=fdd.id AND dma.role_id IN (" . implode(',', $myRoleIds) . ")) WHEN (fdd.document_type = 'TEAM' AND fdd.deposited_with = 'ALL') THEN '" . implode('~#~', array_keys($myTeams)) . "' WHEN (fdd.document_type = 'WORKGROUP' AND fdd.deposited_with = 'ALL') THEN '" . implode('~#~', array_keys($myWorkgroups)) . "' ELSE '' END) AS badge";
        } else {
            $aColumns[] = "(CASE WHEN fdd.document_type = 'CLUB' THEN '$clubBadge' WHEN fdd.document_type = 'CONTACT' THEN '$contactBadge' WHEN fdd.document_type = 'TEAM' THEN 'TEAM' WHEN fdd.document_type = 'WORKGROUP' THEN 'WORKGROUP' ELSE '' END) AS badge";
        }
        //get document criteria query
        $documentlistClass = new Documentlist($this->container, 'ALL');
        $documentlistClass->setColumnsForInternal($aColumns);
        $documentlistClass->setConditionForInternal('personal');
        $documentlistClass->setFromForInternal();

        $documentlistClass->setGroupBy('fdd.id');
        $documentlistClass->addOrderBy('fdv.updated_at DESC');
        $qry = $documentlistClass->getResult();

        //execute document query
        $documentPdo = new DocumentPdo($this->container);

        $documents = $documentPdo->executeDocumentsQuery($qry);
        $documentsData = $this->getoptimizedDocuments($documents, $myRoles);

        return new JsonResponse($documentsData);
    }

    /**
     * Function to convert the bytes to mb in dovu=cument section
     *
     * @param int $bytes size of document in bytes
     *
     * @return string
     */
    public function convertByteToMb($bytes)
    {
        $filesize = number_format($bytes / 1048576, 2);
        if ($filesize < 0.1) {
            $filesize = '< ' . $this->get('club')->formatNumber(0.1) . ' MB';
        } else {
            $filesize = $this->get('club')->formatNumber($filesize) . " " . ' MB';
        }

        return $filesize;
    }

    /**
     * Function for getting documents team and workgroup overview
     *
     * @param object $request request object
     *
     * @return JsonResponse
     */
    public function roleOverviewDocumentsAction(Request $request)
    {
        $roleType = $request->get('roleType');
        $roleId = $request->get('roleId');

        $mysqldate = $this->get('club')->get('mysqldate');
        $mysqltime = $this->get('club')->get('mysqltime');

        //data columns
        $aColumns = array('DISTINCT(fdd.id)', 'fdd.document_type', 'fdv.id AS versionId', 'DATE_FORMAT(fdv.updated_at,"' . $mysqldate . '" ) AS updatedDate', 'fdv.updated_at', 'DATE_FORMAT( fdv.updated_at,"' . $mysqltime . '" ) AS updatedTime', 'fdv.size', 'fdv.file', 'fda.role_id AS roleId', 'fdd.deposited_with AS depositedWith', '(IF(fdcs.contact_id IS NULL, 1, 0)) AS isUnread');
        $aColumns[] = '(CASE WHEN (fdi18.name_lang IS NULL OR fdi18.name_lang = "") THEN fdd.name ELSE fdi18.name_lang END) AS docName';

        //get document criteria query
        $documentlistClass = new Documentlist($this->container, strtoupper($roleType), $roleId);
        $documentlistClass->setColumnsForInternal($aColumns);
        $documentlistClass->setFromForInternal();
        $documentlistClass->setConditionForInternal($roleType, $roleId);
        $documentlistClass->setGroupBy('fdd.id');
        $documentlistClass->addOrderBy('fdv.updated_at DESC');
        $qry = $documentlistClass->getResult();

        //execute document query
        $documentPdo = new DocumentPdo($this->container);
        $documents = $documentPdo->executeDocumentsQuery($qry);
        $documentsData = $this->getoptimizedDocuments($documents);

        return new JsonResponse($documentsData);
    }

    /**
     * Function for getting optimzed documents in team, personal and workgroup overview
     *
     * @param array $documents documents array
     * @param array $myRoles   My teams and workgroups array(id-title combination)
     *
     * @return array $data Document list
     */
    public function getoptimizedDocuments($documents, $myRoles = array())
    {
        foreach ($documents as $key => $value) {
            $documents[$key]["downloadLink"] = $this->generateUrl('document_download', array('docId' => $value['id'], 'versionId' => $value['versionId']));
            $documents[$key]["imageIcon"] = FgUtility::getDocumentIcon($value['file'], true);
            $documents[$key]["fileSize"] = $this->convertByteToMb($value['size']);
            if ($documents[$key]['updatedDate'] === date('d.m.Y')) {
                $documents[$key]['updatedDate'] = $this->get('translator')->trans('DASHBOARD_TODAY');
            } else if ($documents[$key]['updatedDate'] === date('d.m.Y', time() - 86400)) {
                $documents[$key]['updatedDate'] = $this->get('translator')->trans('DASHBOARD_DOCUMENT_YESTERDAY');
            }
        }
        $data = array('myDocuments' => $documents, 'myRoles' => $myRoles);

        return $data;
    }

    /**
     * Method to get forum list in the personal overview
     *
     * @param string $role role name (null/team/workgroup)
     *
     * @return JsonResponse
     */
    public function forumlistingOverviewAction(Request $request, $role = null)
    {

        $objInternalTeamPdo = new InternalTeamPdo($this->container);
        $roleId = $request->get('roleId', 0); //team/workgroup -id
        $terminologyService = $this->container->get('fairgate_terminology_service');
        $executiveBoardTitle = $terminologyService->getTerminology('Executive Board', $this->container->getParameter('singular'));
        $contactRoles = $this->getRightwiseWiseRoles($role);
        $administrativeRoles = array_merge($contactRoles['adminstrativeRoles']['Team'], $contactRoles['adminstrativeRoles']['Workgroup']);
        /* administrative roles excluded from member roles */
        $memberRoles = array_diff(array_merge($contactRoles['memberRoles']['Team'], $contactRoles['memberRoles']['Workgroup']), $administrativeRoles);

        if ($roleId) { /* In team/workgroup overview only that specific roles are needed */
            $administrativeRoles = array_intersect(array($roleId), $administrativeRoles);
            $memberRoles = array_intersect(array($roleId), $memberRoles);
        }
        $administrativeRoles = count($administrativeRoles) > 0 ? $administrativeRoles : array(0);
        $memberRoles = count($memberRoles) > 0 ? $memberRoles : array(0);

        $forumList = $objInternalTeamPdo->getPersonalForumList($executiveBoardTitle, $administrativeRoles, $memberRoles, $roleId);
        foreach ($forumList as $key => $personalForumDetail) {
            $forumList[$key]['updatedAt'] = $this->getFormattedDate($personalForumDetail['updatedAt']);

            if ($personalForumDetail['forumType'] == 'team') {
                $forumList[$key]['forumLink'] = $this->generateUrl('forum_view_team', array('roleId' => $personalForumDetail['roleId']));
                $forumList[$key]['topicLink'] = $this->generateUrl('team_forum_topic_view', array('roleId' => $personalForumDetail['roleId'], 'topicId' => $personalForumDetail['id'], 'page' => 1));
            } else {
                $forumList[$key]['forumLink'] = $this->generateUrl('forum_view_workgroup', array('roleId' => $personalForumDetail['roleId']));
                $forumList[$key]['topicLink'] = $this->generateUrl('workgroup_forum_topic_view', array('roleId' => $personalForumDetail['roleId'], 'topicId' => $personalForumDetail['id'], 'page' => 1));
            }

            $forumList[$key]['topicPostLink'] = $this->generateUrl('forum_topic_redirect_lastpost', array('grp' => $personalForumDetail['forumType'], 'grpId' => $personalForumDetail['roleId'], 'topicId' => $personalForumDetail['id']));
            $forumList[$key]['contactLink'] = ($personalForumDetail['hideProfile'] == '1') ? '' : $this->generateUrl('internal_community_profile', array('contactId' => $personalForumDetail['forumCreatedById']));
        }
        $resultArray = array("forums" => $forumList);
        if ($roleId) {
            $resultArray['role'] = $roleId;
        }

        return new JsonResponse($resultArray);
    }

    /**
     * Method to get formatted date (Today H:s/ Yesterday H:s/ d.m.Y H:s)
     *
     * @param string $dateString
     *
     * @return string
     */
    private function getFormattedDate($dateString)
    {
        if (date('Y-m-d', strtotime($dateString)) == date('Y-m-d')) {
            $resultDate = $this->get('translator')->trans('DASHBOARD_TODAY') . ' ' . $this->container->get('club')->formatDate($dateString, 'time');
        } else if (date('Y-m-d', strtotime($dateString)) == date('Y-m-d', strtotime("-1 day"))) {
            $resultDate = $this->get('translator')->trans('DASHBOARD_YESTERDAY') . ' ' . $this->container->get('club')->formatDate($dateString, 'time');
        } else {
            $resultDate = $this->container->get('club')->formatDate($dateString, 'datetime');
        }

        return $resultDate;
    }

    /**
     * Method to get administrative (teams/workgroups) and member (teams/workgroups) of the logged-in contact
     *
     * @param string $role roleName (null/team/workgroup)
     *
     * @return array
     */
    private function getRightwiseWiseRoles($role)
    {
        $clubRoleRights = $this->container->get('contact')->get('clubRoleRightsGroupWise');
        $teamAdminstrativeRoles = $workgroupAdminstrativeRoles = $teamMemberRoles = $workgroupMemberRoles = array();
        $adminRights = $this->container->get('contact')->get('mainAdminRightsForFrontend'); //clubadmin or superadmin
        if ($role != 'workgroup') {  //get teams
            if (count($adminRights) > 0) {
                $teamAdminstrativeRoles = array_keys($this->container->get('contact')->get('teams'));
            } else {
                /* Teams which the contact have adminstrative role */
                $teamAdminstrativeRoles = (count($clubRoleRights['ROLE_GROUP_ADMIN']['teams']) > 0) ? $clubRoleRights['ROLE_GROUP_ADMIN']['teams'] : array();
                if ($clubRoleRights['ROLE_FORUM_ADMIN']['teams']) {
                    $teamAdminstrativeRoles = array_merge($teamAdminstrativeRoles, $clubRoleRights['ROLE_FORUM_ADMIN']['teams']);
                }
            }
            /* Teams which the contact is a member of */
            $teamMemberRoles = (count($clubRoleRights['MEMBER']['teams']) > 0) ? $clubRoleRights['MEMBER']['teams'] : array();
        }
        if ($role != 'team') {  //get workgroup
            if (count($adminRights) > 0) {
                $workgroupAdminstrativeRoles = array_keys($this->container->get('contact')->get('workgroups'));
            } else {
                /* Workgroups which the contact have adminstrative role */
                $workgroupAdminstrativeRoles = (count($clubRoleRights['ROLE_GROUP_ADMIN']['workgroups']) > 0) ? $clubRoleRights['ROLE_GROUP_ADMIN']['workgroups'] : array();
                if ($clubRoleRights['ROLE_FORUM_ADMIN']['workgroups']) {
                    $workgroupAdminstrativeRoles = array_merge($workgroupAdminstrativeRoles, $clubRoleRights['ROLE_FORUM_ADMIN']['workgroups']);
                }
            }
            /* Workgroups which the contact is a member of */
            $workgroupMemberRoles = (count($clubRoleRights['MEMBER']['workgroups']) > 0) ? $clubRoleRights['MEMBER']['workgroups'] : array();
        }

        return array("adminstrativeRoles" => array("Team" => $teamAdminstrativeRoles, "Workgroup" => $workgroupAdminstrativeRoles), "memberRoles" => array("Team" => $teamMemberRoles, "Workgroup" => $workgroupMemberRoles));
    }

    /**
     * Function for getting calendar team and workgroup overview
     *
     * @param object $request request object
     *
     * @return JsonResponse
     */
    public function roleOverviewCalendarAction(Request $request)
    {

        $roleType = $request->get('roleType');
        $roleId = $request->get('roleId');
        $startDate = date('Y-m-d H:i:s');

        //initialize the calendar events class and get the qry.
        $calenderEventsObj = new CalenderEvents($this->container, '', '', $startDate);
        $calenderEventsObj->setColumns();
        $calenderEventsObj->setFrom();
        $calenderEventsObj->setCondition();
        if ($roleType != '') {
            $calenderEventsObj->addCondition('CSA.role_id=' . $roleId);
        }
        $calenderEventsObj->setGroupBy('CD.id');
        $calenderEventsObj->addOrderBy('eventSortField ASC');
        $qry = $calenderEventsObj->getResult();

        //execute the qry and get the results
        $calendarPdo = new CalendarPdo($this->container);
        $eventDetails = $calendarPdo->executeQuery($qry);

        //loop the results through recurr class to get all dates of repeating + non repeating events
        $eventDetailsWithRecurrence = $this->getRecurrenceDetails($eventDetails);
        //echo "<pre>";print_r($eventDetailsWithRecurrence);exit;
        $clubDetails = $this->getclubDetailsData();
        $finalData = $this->getOptimizedCalendarData($eventDetailsWithRecurrence, $clubDetails);

        return new JsonResponse($finalData);
    }

    /**
     * Method to add recurrence periods of repeating events.
     * Here loop each event detail and if it is repeating, add the recurrence between the interval to the array
     *
     * @param array $eventDetails event details
     *
     * @return array
     */
    private function getRecurrenceDetails($eventDetails)
    {
        $result = array();
        $calendarObj = new CalendarRecurrence();
        foreach ($eventDetails as $eventDetail) {
            /* Deleted items ($eventDetail['eventDetailType'] == '2' ) are excluded from list */
            if ($eventDetail['eventDetailType'] == "0") { //repeating events
                $calendarObj->recurrenceRule = $eventDetail['eventRules'];
                $calendarObj->setStartDate($eventDetail['startDate']);
                if ($eventDetail['endDate']) {
                    $calendarObj->setEndDate($eventDetail['endDate']);
                }
                if ($eventDetail['eventDetailUntillDate']) {
                    $calendarObj->setUntilDate($eventDetail['eventDetailUntillDate']);
                }
                $recurrences = $calendarObj->getRecurrenceAfter($eventDetail['intervalStartDate'], $eventDetail['eventRepeatUntillDate']);
                foreach ($recurrences as $recurrence) {
                    $eventDetail['startDate'] = $recurrence['recurrenceStartDate'];
                    $eventDetail['endDate'] = $recurrence['recurrenceEndDate'];
                    $result[] = $eventDetail;
                }
            } else if ($eventDetail['eventDetailType'] == "1") { //non-repeating events
                $result[] = $eventDetail;
            }
        }

        return $result;
    }

    /**
     * Method to get the optimized result data for showing calendar widget in overview
     *
     * @param array $eventDetails event details
     * @param array $clubDetails  club details array
     *
     * @return array
     */
    public function getOptimizedCalendarData($eventDetails, $clubDetails)
    {
        foreach ($eventDetails as $key => $details) {
            $eventDetails[$key]['dateDetails'] = $this->getDateDataForDetailsPage($details['startDate'], $details['endDate'], $details['isAllday']);
            $eventDetails[$key]['catDetails'] = ($details['eventCategories']) ? $this->getseperatedCategoryData($details['eventCategories']) : array();
            $eventDetails[$key]['roleDetails'] = ($details['eventRoleAreas']) ? $this->getseperatedRoleData($details['eventRoleAreas']) : array();
            $eventDetails[$key]['detailLink'] = $this->generateUrl('calendar_appointment_details', array('eventId' => $details['eventDetailId'], 'startTimeStamp' => 'startDate', 'endTimeStamp' => 'endDate'));
            if ($eventDetails[$key]['isClubAreaSelected'] == 1) {
                $eventDetails[$key]['clubDetails']['title'] = $clubDetails[$eventDetails[$key]['clubId']]['title'];
                $eventDetails[$key]['clubDetails']['clubColorCode'] = $eventDetails[$key]['clubColorCode'];
                $eventDetails[$key]['clubDetails']['clubType'] = $clubDetails[$eventDetails[$key]['clubId']]['clubType'];
                $eventDetails[$key]['clubDetails']['clubLogoPath'] = $clubDetails[$eventDetails[$key]['clubId']]['clubLogoPath'];
            }
        }
        return $eventDetails;
    }

    /**
     * Function to get date data details in details page
     *
     * @param int $startDateVal  start date
     * @param int $endDateVal    end date
     * @param int $isAllday      is event a all day event or not
     *
     * @return array
     */
    public function getDateDataForDetailsPage($startDateVal, $endDateVal, $isAllday)
    {
        $dateArray = array();
        $startDate = date('Y-m-d', strtotime($startDateVal));
        $endDate = date('Y-m-d', strtotime($endDateVal));
        $startTime = date('H:i:s', strtotime($startDateVal));
        $endTime = date('H:i:s', strtotime($endDateVal));
        if ($isAllday == 1) {
            if ($startDate == $endDate) {
                $dateArray['startDate'] = $this->get('club')->formatDate($startDate, 'date', 'Y-m-d');
            } else {
                $dateArray['startDate'] = $this->get('club')->formatDate($startDate, 'date', 'Y-m-d');
                $dateArray['endDate'] = $this->get('club')->formatDate($endDate, 'date', 'Y-m-d');
            }
        } else {
            if ($startDate == $endDate) {
                $dateArray['startDate'] = $this->get('club')->formatDate($startDate, 'date', 'Y-m-d') . ', ' . $this->get('club')->formatDate($startTime, 'time', 'H:i:s') . ' - ' . $this->get('club')->formatDate($endTime, 'time', 'H:i:s');
            } else {
                $dateArray['startDate'] = $this->get('club')->formatDate($startDateVal, 'datetime');
                $dateArray['endDate'] = $this->get('club')->formatDate($endDateVal, 'datetime');
            }
        }

        return $dateArray;
    }

    /**
     * Function to get seperate category names for listing in calendar box
     *
     * @param string $eventDetails  event details category string
     *
     * @return array
     */
    public function getseperatedCategoryData($eventDetails)
    {

        $details = explode('|&&&|', $eventDetails);
        $splitDetails = array();
        foreach ($details as $key => $data) {
            $splitDetailsFinal = explode('|@@@|', $data);
            $splitDetails[$key] = $splitDetailsFinal[1];
        }

        return $splitDetails;
    }

    /**
     * Function to get seperate role names for listing in calendar box
     *
     * @param string $eventDetails event details role string
     *
     * @return array
     */
    public function getseperatedRoleData($eventDetails)
    {
        $details = explode('|&&&|', $eventDetails);
        $splitDetails = array();
        foreach ($details as $key => $data) {
            $splitDetailsFinal = explode('|@@@|', $data);
            $splitDetails[$splitDetailsFinal[0]]['role'] = $splitDetailsFinal[1];
            $splitDetails[$splitDetailsFinal[0]]['color'] = $splitDetailsFinal[2];
        }

        return $splitDetails;
    }

    /**
     * This function is used to club details data for showing club block in personal overview
     *
     * @return array
     */
    public function getclubDetailsData()
    {
        $terminologyService = $this->container->get('fairgate_terminology_service');
        $clubHeirarchy = $this->container->get('club')->get('clubHeirarchyDet');
        $clubTitles = array();
        $entityManager = $this->getDoctrine()->getManager();
        foreach ($clubHeirarchy as $clubId => $clubArr) {
            $term = ($clubArr['club_type'] == 'federation') ? 'Federation' : (($clubArr['club_type'] == 'sub_federation') ? 'Sub-federation' : 'Club');
            $clubTitles[$clubId]['title'] = ucfirst($terminologyService->getTerminology($term, $this->container->getParameter('singular')));
            $clubTitles[$clubId]['clubType'] = $clubArr['club_type'];
            $clubTitles[$clubId]['clubLogoPath'] = FgUtility::getClubLogo($clubId, $entityManager);
        }
        $clubTitles[$this->container->get('club')->get('id')]['title'] = ucfirst($terminologyService->getTerminology('Club', $this->container->getParameter('singular')));
        $clubTitles[$this->container->get('club')->get('id')]['clubType'] = 'club';
        $clubTitles[$this->container->get('club')->get('id')]['clubLogoPath'] = FgUtility::getClubLogo($this->container->get('club')->get('id'), $entityManager);

        return $clubTitles;
    }

    /**
     * Method to get article list in the personal overview
     *
     * @param string $role role name (null/team/workgroup)
     *
     * @return JsonResponse
     */
    public function articleListingOverviewAction(Request $request, $role = null)
    {
        $roleId = $request->get('roleId', 0);
        $terminologyService = $this->get('fairgate_terminology_service');
        $clubBadge = $terminologyService->getTerminology('Club', $this->container->getParameter('singular'));
        $articleListObj = new ArticlesList($this->container, 'article');
        $articleListObj->columnData = array('0' => array('id' => 'PUBLICATION_DATE'), '1' => array('id' => 'AREAS'));
        if ($roleId) {
            $filterArray = array('AREAS' => $roleId);
            $articleListObj->filterData = $filterArray;
        }
        $articleListObj->setColumnData();
        $articleListObj->setColumnDataFrom();
        $articleListObj->setGroupBy();
        $articleListObj->addOrderBy();
        $articleListObj->setLimit(0, 7);
        $articleListObj->addHaving(array("STATUS = 'published'"));
        $articles = $articleListObj->getArticleData();
        foreach ($articles as $key => $value) {
            $articles[$key]['articleLink'] = $this->generateUrl('internal_article_details_view', array('articleId' => $value['articleId']));
            $teamArray = array();
            if ($value['isClub']) {
                $teamArray[0] = $clubBadge;
            }
            $value['PUBLICATION_DATE'] = $this->getDateDetails($value['PUBLICATION_DATE']);
            $articles[$key]['roles'] = ($value['AREAS']) ? array_merge($teamArray, explode("*##*", $value['AREAS'])) : $teamArray;
            $articles[$key]['PUBLICATION_DATE'] = $this->get('club')->formatDate($value['PUBLICATION_DATE'], 'date');
        }
        $resultArray = array("article" => $articles);
        if ($roleId) {
            $resultArray['role'] = $roleId;
        }

        return new JsonResponse($resultArray);
    }

    /**
     * Method to get today/yesterday values
     *
     * @param string $publishDate article published date
     *
     * @return string
     */
    private function getDateDetails($publishDate)
    {
        $today = new DateTime();
        $today->setTime(0, 0, 0); // reset time part, to prevent partial comparison

        $match_date = DateTime::createFromFormat("Y-m-d H:i:s", $publishDate);
        $match_date->setTime(0, 0, 0); // reset time part, to prevent partial comparison

        $diff = $today->diff($match_date);
        $diffDays = (integer) $diff->format("%R%a"); // Extract days count in interval

        switch ($diffDays) {
            case 0:
                $publishDate = $this->get('translator')->trans('DASHBOARD_TODAY');
                break;
            case -1:
                $publishDate = $this->get('translator')->trans('DASHBOARD_YESTERDAY');
                break;
            default:
                break;
        }

        return $publishDate;
    }
}
