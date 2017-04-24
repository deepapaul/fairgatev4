<?php

namespace Internal\GeneralBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Clubadmin\Util\Contactlist;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Repository\Pdo\DocumentPdo;
use Common\UtilityBundle\Repository\Pdo\ContactPdo;
use Clubadmin\DocumentsBundle\Util\Documentlist;
use Common\UtilityBundle\Util\FgPermissions;

/**
 * This controller is used for Navigation management.
 * 
 * @package    NavigationController
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
class NavigationController extends Controller
{

    /**
     * Javascript variables used in app.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function internalVariablesAction()
    {
        $forbiddenFiletypes = $this->container->getParameter('forbiddenFiletypes');
        $rendered = $this->renderView('InternalGeneralBundle:Navigation:internalVariables.html.twig', array('forbiddenFiletypes' => implode(',', $forbiddenFiletypes)));
        $response = new Response($rendered);
        $response->headers->set('Content-Type', 'application/x-javascript');

        return $response;
    }

    /**
     * Json data for building intranet header.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return json
     */
    public function internalHeaderAction(Request $request)
    {
        $module = $request->get('module');
        $level1 = $request->get('level1');
        $level2 = $request->get('level2');
        $contactId = $this->container->get('contact')->get('id');
        $megaMenuSub = array();
        $adminFlag = 0;
        $adminRights = $this->container->get('contact')->get('mainAdminRightsForFrontend');
        $availableUserRights = $this->container->get('contact')->get('availableUserRights');
        $isClubAdmin = (in_array('ROLE_USERS', $adminRights)) ? 1 : 0;
        $isSuperAdmin = ($this->container->get('contact')->get('isSuperAdmin') || (($this->container->get('contact')->get('isFedAdmin')) && ($this->container->get('club')->get('type') != 'federation'))) ? 1 : 0;
        $bookedModule = $this->container->get('club')->get('bookedModulesDet');
        $isArticleAdmin = (in_array('ROLE_ARTICLE', $availableUserRights)) ? 1 : 0;
        $userRights = array('ROLE_GROUP', 'ROLE_CALENDAR');
        $userrightsIntersect = array_intersect($userRights, $availableUserRights);
        $isAdmin = (count($adminRights) > 0) ? 1 : 0;
        if (count($userrightsIntersect) > 0 || $isAdmin == 1 || $isClubAdmin == 1) {
            $adminFlag = 1;
        }

        //Set topnavigation for switch
        $topNavigationArr = $this->setTopNavigationSwitch($module);
        // CMS Menu display
        if (in_array('frontend2', $bookedModule)) {
            $topNavigationArr['leftmenu'][] = $this->setTopNavigationCmsBlock($availableUserRights, $this->container->get('club')->get('id'), $isClubAdmin, $isSuperAdmin, $module, $level1);
        }

        // Team/Workgroup/Article menu block
        $topNavigationArr['leftmenu'][] = $this->setTopNavigationRoleArticleBlock($availableUserRights, $isArticleAdmin, $isClubAdmin, $isSuperAdmin, $module, $level1, $level2);

        // Left Navigation internal - Calendar/gallery/search block
        $topNavigationArr = $this->setTopNavigationCalendarBlock($topNavigationArr, $adminFlag, $module, $level1);

        // Left Navigation internal - team/Workgroup block        
        $teamWorkgroup = $this->setTopNavigationTeamWorkgroupMenu($module, $level1);
        //echo '<pre>';print_r($teamWorkgroup);exit;
        if (count($teamWorkgroup)) {
            foreach ($teamWorkgroup[0] as $key => $value) {
                $topNavigationArr['rightmenu'][] = $value;
            }
            foreach ($teamWorkgroup[1] as $key1 => $value1) {
                $megaMenuSub[] = $value1;
            }
        }

        //Profile Navigation
        $subMenu = $this->getProfileHeaderMenus($module, $level1);
        $topNavContactName = ($isSuperAdmin == 1) ? $this->container->get('contact')->get('nameNoSort') : $subMenu['contactName'];
        unset($subMenu['contactName']);
        $topNavigationArr['rightmenu'][] = array('title' => $topNavContactName, 'url' => '#', 'submenu' => $subMenu, 'class' => (($module == 'profile') ? 'fg-dev-header-nav-active active' : ''), 'breadcrumbTitle' => $this->get('translator')->trans('BREADCRUMB_PERSONAL_SECTION'), 'arrowClass' => (($module == 'profile') ? 'open' : ''));
        $megaMenuSub[] = array('title' => $this->get('translator')->trans('TOP_NAV_PERSONAL'), 'url' => '#', 'submenu' => $subMenu, 'class' => (($module == 'profile') ? 'fg-dev-header-nav-active active' : ''), 'breadcrumbTitle' => $this->get('translator')->trans('BREADCRUMB_PERSONAL_SECTION'));
        $jsonData['topNavArr'] = $topNavigationArr;
        //To handle megamenu for medium resolution screens
        $jsonData['rightMegaMenu'] = array('title' => $topNavContactName, 'url' => '#', 'submenu' => $megaMenuSub, 'class' => (($module == 'profile') ? 'fg-dev-header-nav-active active' : ''), 'breadcrumbTitle' => $this->get('translator')->trans('BREADCRUMB_PERSONAL_SECTION'));
        if ($isSuperAdmin) {
            $jsonData['intranetAccess'] = true;
        } else {
            $jsonData['intranetAccess'] = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmContact')->checkIntranetAccess($contactId);
        }

        return new JsonResponse($jsonData);
    }

    /**
     * Function to get all contacts to be listed in intranet top navigation autocomplete search.
     *
     * @param Request $request Request object
     *
     * @return Json response
     */
    public function searchContactsAction(Request $request)
    {
        $conn = $this->container->get('database_connection');
        $em = $this->getDoctrine()->getManager();
        $searchTerm = FgUtility::getSecuredData($request->get('term'), $conn);
        $club = $this->get('club');
        $dobAttrId = $this->container->getParameter('system_field_dob');
        $contactlistClass = new Contactlist($this->container, '', $club, 'contact');
        $contacts = $em->getRepository('CommonUtilityBundle:FgCmContact')->getContactsForSearch($contactlistClass, $dobAttrId, $searchTerm, '', true, 0, 1);
        for ($i = 0; $i < count($contacts); $i++) {
            $contactId = $contacts[$i]['id'];
            $contacts[$i]['path'] = $this->generateUrl('internal_community_profile', array('contactId' => $contactId));
        }

        return new JsonResponse($contacts);
    }

    /**
     * Function for building team header menus.
     *
     * @param string $module        ModuleName
     * @param string $level1        PageLevel1
     * @param int    $teamDocsCount team document count
     *
     * @return array Menu details
     */
    public function getTeamHeaderMenus($module, $level1, $teamDocsCount)
    {
        $menuPermissions = $this->getMenuDisplayStatus('teams');
        $docCatCount = $this->getDocumentCategoryExistCount('TEAM');
        $subMenu = array();
        if ($menuPermissions['overview'] == 1) {
            $subMenu[] = array('title' => $this->get('translator')->trans('TOP_NAV_ROLE_OVERVIEW'), 'url' => $this->generateUrl('team_overview'), 'class' => (($module == 'team' && $level1 == 'teamOverview') ? 'fg-dev-header-nav-active active' : ''), 'breadcrumbTitle' => $this->get('translator')->trans('TOP_NAV_ROLE_OVERVIEW'));
        }
        if ($menuPermissions['teammember'] == 1) {
            $subMenu[] = array('title' => ucfirst($this->get('fairgate_terminology_service')->getTerminology('Team member', $this->container->getParameter('plural'))), 'url' => $this->generateUrl('team_detail_overview'), 'class' => (($module == 'team' && in_array($level1, array('teamMemberlist', 'teammember'))) ? 'fg-dev-header-nav-active active' : ''), 'breadcrumbTitle' => ucfirst($this->get('fairgate_terminology_service')->getTerminology('Team member', $this->container->getParameter('plural'))));
        }

        if ($docCatCount > 0 && $menuPermissions['document'] == 1) {
            $subMenu[] = array('title' => $this->get('translator')->trans('TOP_NAV_DOCUMENTS'), 'url' => $this->generateUrl('internal_team_document_list'), 'showNewBadge' => true, 'newBadgeId' => 'fg-dev-unread-team-documents', 'class' => (($module == 'team' && $level1 == 'document') ? 'fg-dev-header-nav-active active' : ''), 'newCount' => $teamDocsCount, 'breadcrumbTitle' => $this->get('translator')->trans('TOP_NAV_DOCUMENTS'));
        }

        //**To check forum user rights*//
        $adminFlag = 0;
        $userRights = array('ROLE_GROUP_ADMIN', 'ROLE_FORUM_ADMIN', 'MEMBER');
        $Grouprights = $this->container->get('contact')->get('clubRoleRightsRoleWise');
        $allGrouprights = array_map(function ($a) {
            if ($a['type'] == 'W')
                return $a['rights'][0];
        }, $Grouprights);
        $userrightsIntersect = array_intersect($userRights, $allGrouprights);
        $isAdmin = (count($this->container->get('contact')->get('mainAdminRightsForFrontend')) > 0) ? 1 : 0;
        if (count($userrightsIntersect) > 0 || $isAdmin == 1) {
            $adminFlag = 1;
        }
        $accessCheckArray = array('from' => 'forum', 'type' => 'teams', 'adminflag' => $adminFlag);
        $permissionObj = new FgPermissions($this->container);
        $newTabs = $permissionObj->checkForumAccess($accessCheckArray);
        if ($menuPermissions['forum'] == 1) {
            if ($adminFlag == 1 || count($newTabs) > 0) {
                $subMenu[] = array('title' => $this->get('translator')->trans('TOP_NAV_FORUM'), 'url' => $this->generateUrl('team_forum_views'), 'class' => (($module == 'team' && $level1 == 'forum') ? 'fg-dev-header-nav-active active' : ''), 'breadcrumbTitle' => $this->get('translator')->trans('TOP_NAV_FORUM'));
            }
        }

        return $subMenu;
    }

    /**
     * Function for building workgroup header menus.
     *
     * @param string $module      ModuleName
     * @param string $level1      PageLevel1
     * @param int    $wgDocsCount workgroup document count
     *
     * @return array Menu details
     */
    public function getWorkgroupHeaderMenus($module, $level1, $wgDocsCount)
    {
        $docCatCount = $this->getDocumentCategoryExistCount('WORKGROUP');
        $menuPermissions = $this->getMenuDisplayStatus('workgroups');
        $subMenu = array();
        if ($menuPermissions['overview'] == 1) {
            $subMenu[] = array('title' => $this->get('translator')->trans('TOP_NAV_ROLE_OVERVIEW'), 'url' => $this->generateUrl('workgroup_overview'), 'class' => (($module == 'workgroup' && $level1 == 'workgroupOverview') ? 'fg-dev-header-nav-active active' : ''), 'breadcrumbTitle' => $this->get('translator')->trans('TOP_NAV_ROLE_OVERVIEW'));
        }
        if ($menuPermissions['teammember'] == 1) {
            $subMenu[] = array('title' => $this->get('translator')->trans('TOP_NAV_WORKGROUP_MEMBER'), 'url' => $this->generateUrl('workgroup_detail_overview'), 'class' => (($module == 'workgroup' && in_array($level1, array('workgroupMemberlist', 'workgroupmember'))) ? 'fg-dev-header-nav-active active' : ''), 'breadcrumbTitle' => $this->get('translator')->trans('TOP_NAV_WORKGROUP_MEMBER'));
        }

        if ($docCatCount > 0 && $menuPermissions['document'] == 1) {
            $subMenu[] = array('title' => $this->get('translator')->trans('TOP_NAV_DOCUMENTS'), 'url' => $this->generateUrl('internal_workgroup_document_list'), 'showNewBadge' => true, 'newBadgeId' => 'fg-dev-unread-workgroup-documents', 'class' => (($module == 'workgroup' && $level1 == 'document') ? 'fg-dev-header-nav-active active' : ''), 'newCount' => $wgDocsCount, 'breadcrumbTitle' => $this->get('translator')->trans('TOP_NAV_DOCUMENTS'));
        }
        //**To check forum user rights*//
        $adminFlag = 0;
        $userRights = array('ROLE_GROUP_ADMIN', 'ROLE_FORUM_ADMIN', 'MEMBER');
        //$teamRight = $this->container->get('contact')->checkClubRoleRights($this->contactId, false);
        $Grouprights = $this->container->get('contact')->get('clubRoleRightsRoleWise');
        $allGrouprights = array_map(function ($a) {
            if ($a['type'] == 'W')
                return $a['rights'][0];
        }, $Grouprights);
        $userrightsIntersect = array_intersect($userRights, $allGrouprights);
        $isAdmin = (count($this->container->get('contact')->get('mainAdminRightsForFrontend')) > 0) ? 1 : 0;
        if (count($userrightsIntersect) > 0 || $isAdmin == 1) {
            $adminFlag = 1;
        }
        $accessCheckArray = array('from' => 'forum', 'type' => 'workgroups', 'adminflag' => $adminFlag);
        $permissionObj = new FgPermissions($this->container);
        $newTabs = $permissionObj->checkForumAccess($accessCheckArray);
        ////////
        if ($menuPermissions['forum'] == 1) {
            if ($adminFlag == 1 || count($newTabs) > 0) {
                $subMenu[] = array('title' => $this->get('translator')->trans('TOP_NAV_FORUM'), 'url' => $this->generateUrl('workgroup_forum_views'), 'class' => (($module == 'workgroup' && $level1 == 'forum') ? 'fg-dev-header-nav-active active' : ''), 'breadcrumbTitle' => $this->get('translator')->trans('TOP_NAV_FORUM'));
            }
        }

        return $subMenu;
    }

    /**
     * Function to build profile header navigation menus.
     *
     * @param string $module ModuleName
     * @param string $level1 PageLevel1
     *
     * @return array Menu details
     */
    private function getProfileHeaderMenus($module, $level1)
    {
        $em = $this->getDoctrine()->getManager();
        $club = $this->container->get('club');

        $clubId = $club->get('id');
        $federationId = $club->get('federation_id');

        $contactId = $this->container->get('contact')->get('id');
        $isSuperAdmin = $this->container->get('contact')->get('isSuperAdmin');

        $unreadCountFlag = 1;
        $unreadMessages = $em->getRepository('CommonUtilityBundle:FgMessage')->getContactMessagesCount($contactId, $clubId, $unreadCountFlag);

        $adminRights = $this->container->get('contact')->get('mainAdminRightsForFrontend');
        $isFedAdmin = (in_array('ROLE_FED_ADMIN', $adminRights)) ? 1 : 0;
        $subMenu = array();

        //exclude superadmin if clubid=federation
        //exclude fedadmin and superamdin and
        if ($clubId == $federationId) {
            $entry = $isSuperAdmin ? 0 : 1;
        } else {
            $entry = ($isSuperAdmin || $isFedAdmin) ? 0 : 1;
        }
        if ($entry) {
            $subMenu[] = array('title' => $this->get('translator')->trans('TOP_NAV_MY_PROFILE'), 'url' => $this->generateUrl('internal_dashboard'), 'breadcrumbTitle' => $this->get('translator')->trans('TOP_NAV_MY_PROFILE'), 'class' => (($module == 'profile' && ($level1 == 'overview')) ? 'fg-dev-header-nav-active active' : ''));
            $subMenu[] = array('title' => $this->get('translator')->trans('TOP_NAV_MESSAGES'), 'url' => $this->generateUrl('internal_message_inbox'), 'class' => (($module == 'profile' && ($level1 == 'messages')) ? 'fg-dev-header-nav-active active' : ''), 'breadcrumbTitle' => $this->get('translator')->trans('TOP_NAV_MESSAGES'), 'showNewBadge' => true, 'newBadgeId' => 'fg-dev-unread-messages', 'newCount' => $unreadMessages);
            $showDocumentLink = false;
            $clubType = $this->container->get('club')->get('type');
            $fedMember = $this->container->get('contact')->get('isFedMember');
            if (($clubType != 'federation' || $clubType != 'standard_club') && ($fedMember == 1)) {
                $showDocumentLink = true;
            } else {
                $docCatCount = $this->getDocumentCategoryExistCount(array('TEAM', 'WORKGROUP', 'CONTACT', 'CLUB'));
                $showDocumentLink = ($docCatCount > 0) ? true : false;
            }
            if ($showDocumentLink) {
                $docCount = $this->getPersonalDocumentsCountInternal();
                $subMenu[] = array('title' => $this->get('translator')->trans('TOP_NAV_DOCUMENTS'), 'url' => $this->generateUrl('documents_personal_list'), 'class' => (($module == 'profile' && ($level1 == 'documents')) ? 'fg-dev-header-nav-active active' : ''), 'breadcrumbTitle' => $this->get('translator')->trans('TOP_NAV_DOCUMENTS'), 'showNewBadge' => true, 'newBadgeId' => 'fg-dev-unread-personal-documents', 'newCount' => $docCount);
            }
            $contactPdo = new ContactPdo($this->container);
            $stealthMode = $contactPdo->getStealthmodeOfContact($contactId, $club->get('clubTable'), $clubType);

            //Need to check
            // 1. If he is a member of any team ans invisible
            // 2. If he has any available fields in that page
            $myTeams = $this->container->get('contact')->get('teams');
            $myContactFields = $this->getPrivacyFields();
            if (count($myContactFields) > 0) {
                //If stealth mode then display link when he has only teams, If not stealth mode the display link always
                if (($stealthMode && count($myTeams) > 0)) {
                    //$subMenu[] = array('title' => $this->get('translator')->trans('TOP_NAV_PRIVACY_SETTINGS'), 'url' => $this->generateUrl('internal_privacy_settings'), 'class' => (($module == 'profile' && ($level1 == 'privacysettings')) ? 'fg-dev-header-nav-active active' : ''), 'breadcrumbTitle' => $this->get('translator')->trans('TOP_NAV_PRIVACY_SETTINGS'));
                } elseif (!$stealthMode) {
                    //$subMenu[] = array('title' => $this->get('translator')->trans('TOP_NAV_PRIVACY_SETTINGS'), 'url' => $this->generateUrl('internal_privacy_settings'), 'class' => (($module == 'profile' && ($level1 == 'privacysettings')) ? 'fg-dev-header-nav-active active' : ''), 'breadcrumbTitle' => $this->get('translator')->trans('TOP_NAV_PRIVACY_SETTINGS'));
                }
            }
            $subSubMenu = $this->getChangeUserMenus($em);
            $subMenu['contactName'] = $subSubMenu['contactName'];
            unset($subSubMenu['contactName']);
            if (count($subSubMenu) > 0) {
                $subMenu[] = array('title' => $this->get('translator')->trans('TOP_NAV_CHANGE_USER'), 'url' => '#', 'class' => 'dropdown-submenu', 'subMenu' => $subSubMenu, 'breadcrumbTitle' => $this->get('translator')->trans('TOP_NAV_CHANGE_USER'));
            }
        }

        return $subMenu;
    }

    /**
     * Function to build switch user menus.
     *
     * @param object $em EntityManagerObject
     *
     * @return array Menu details
     */
    private function getChangeUserMenus($em)
    {
        $subSubMenu = array();
        $contactId = $this->container->get('contact')->get('id');
        $isCompany = $this->container->get('contact')->get('isCompany');

        //change user possibility only for single person or if parent is logged in
        $session = $this->container->get('session');
        if (($isCompany == 0) || ($session->has('parentId'))) {
            $parentContactId = ($session->has('parentId')) ? $session->get('parentId') : $contactId;
            $childRelations = $em->getRepository('CommonUtilityBundle:FgCmLinkedcontact')->getChildrensHavingProfileAccessForParents($parentContactId, $contactId, $this->container);
            foreach ($childRelations as $childRelation) {
                $subSubMenu['contactName'] = $childRelation['contactName'];
                unset($childRelation['contactName']);
                if (count($childRelation) > 0) {
                    if ($childRelation['id'] != $contactId) {
                        $subSubMenu[$childRelation['id']] = array('title' => $childRelation['name'], 'url' => $this->generateUrl('switch_user', array('contactId' => $childRelation['id'])), 'class' => '');
                    }
                }
            }
            //companies for which this contact is the maincontact
            $myCompanies = $em->getRepository('CommonUtilityBundle:FgCmContact')->getCompaniesOfAContact($parentContactId, $this->container);
            foreach ($myCompanies as $myCompany) {
                if ($myCompany['id'] != $contactId && $myCompany['id'] != '') {
                    $subSubMenu[$myCompany['id']] = array('title' => $myCompany['name'], 'url' => $this->generateUrl('switch_user', array('contactId' => $myCompany['id'])), 'class' => '');
                }
                $topNavParentName = $myCompany['parentName'];
            }
            if (($session->get('parentId')) && ($session->get('parentId') != $contactId)) {
                $subSubMenu[$session->get('parentId')] = array('title' => $topNavParentName, 'url' => $this->generateUrl('switch_user', array('contactId' => $session->get('parentId'))), 'class' => '');
            }
        } else {
            $subSubMenu['contactName'] = $this->container->get('contact')->get('nameNoSort');
        }

        return $subSubMenu;
    }

    /**
     * Function to get documents count for personal overview in internal area.
     *
     * @return $count Unseen documents are there or not
     */
    private function getPersonalDocumentsCountInternal()
    {
        $documentlistClass = new Documentlist($this->container, 'ALL');
        $documentlistClass->setCountForInternal();
        $documentlistClass->setConditionForInternal('personal');
        $documentlistClass->setFromForInternal();
        $documentlistClass->addCondition('fdcs.contact_id IS NULL');
        $qry = $documentlistClass->getResult();
        $documentPdo = new DocumentPdo($this->container);
        $result = $documentPdo->executeDocumentsQuery($qry);
        $count = $result[0]['count'];

        return $count;
    }

    /**
     * Function to get documents count for team and workgroup overview in internal area.
     *
     * @param string $roleType TEAM or WORKGROUP
     *
     * @return int $count Unseen documents are there or not
     */
    private function getRoleDocumentsCountInternal($roleType = 'TEAM')
    {
        $documentlistClass = new Documentlist($this->container, $roleType);
        $documentlistClass->setCountForInternal();
        $type = ($roleType == 'WORKGROUP') ? 'workgroupTopNavCount' : 'teamTopNavCount';
        $documentlistClass->setConditionForInternal($type);
        $documentlistClass->setFromForInternal();
        $documentlistClass->addCondition('fdcs.contact_id IS NULL');
        $qry = $documentlistClass->getResult();
        $documentPdo = new DocumentPdo($this->container);
        $result = $documentPdo->executeDocumentsQuery($qry);
        $count = $result[0]['count'];

        return $count;
    }

    /**
     * Function to get documents category count for personal overview in internal area.
     *
     * @param string or array $type document type
     *
     * @return int count
     */
    private function getDocumentCategoryExistCount($type)
    {
        $catCount = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgDmDocumentCategory')->getCategoryCountDetails($this->get('club')->get('id'), $type);

        return $catCount;
    }

    /**
     * Function to get documents category count for personal overview in internal area.
     *
     * @param string or array $type document type
     *
     * @return int count
     */
    private function getPrivacyFields()
    {
        $em = $this->getDoctrine()->getManager();
        $conn = $this->container->get('database_connection');
        $club = $this->get('club');
        $contact = $this->get('contact');

        $clubDefaultLang = $club->get('default_lang');
        $clubDefaultSystemLang = $club->get('default_system_lang');
        $loggedContactId = $contact->get('id');

        $container = $this->container->getParameterBag();
        $clubIdArray = array('clubId' => $club->get('id'),
            'federationId' => $club->get('federation_id'),
            'subFederationId' => $club->get('sub_federation_id'),
            'clubType' => $club->get('type'),
            'correspondanceCategory' => $container->get('system_category_address'),
            'invoiceCategory' => $container->get('system_category_invoice'),);
        $clubIdArray['address'] = $this->get('translator')->trans('CONTACT_FIELD_ADDRESS');
        $clubIdArray['sysLang'] = $club->get('default_lang');
        $clubIdArray['defSysLang'] = $club->get('default_system_lang');
        $clubIdArray['defaultClubLang'] = $club->get('default_lang');
        $clubIdArray['clubLanguages'] = $club->get('club_languages');

        $fieldType = ($contact->get('isCompany') == 1) ? 'Company' : 'Single person';
        // For getting all personal contact fields
        $fieldDetails = $em->getRepository('CommonUtilityBundle:FgCmAttributeset')
            ->getAllPersonalContactFields($clubIdArray, $conn, 0, $fieldType, $clubDefaultLang, $clubDefaultSystemLang, $loggedContactId);

        return $fieldDetails;
    }

    /**
     * To create display flag of all menu.
     *
     * @param string $type group type(team/workgroup)
     *
     * @return array
     */
    private function getMenuDisplayStatus($type)
    {
        $permissions = $this->container->getParameter('groupMenuPermissions');
        $permittedMenu = array();
        $menupermissions = $this->getAllPermission($type);
        $mainRights = $this->container->get('contact')->get('mainAdminRightsForFrontend');
        foreach ($permissions as $key => $permission) {
            if (count(array_intersect($permission, $menupermissions)) > 0 || (!empty($mainRights))) {
                $permittedMenu[$key] = 1;
            } else {
                $permittedMenu[$key] = 0;
            }
        }

        return $permittedMenu;
    }

    /**
     * To get all permission of a user.
     *
     * @param String $groupType group type(team/workgroup)
     *
     * @return array
     */
    private function getAllPermission($groupType)
    {
        $menuPermissionArray = array();
        $groupRights = $this->container->get('contact')->get('clubRoleRightsGroupWise');
        foreach ($groupRights as $key => $groupRight) {
            if (count($groupRight[$groupType]) > 0) {
                array_push($menuPermissionArray, $key);
            }
        }

        return $menuPermissionArray;
    }

    /**
     * This function is used to identify the apps for which the logged in user has access.
     *
     * @param String $module current module name
     * 
     * @return array $topNavigationArr Top navigation switch
     */
    private function setTopNavigationSwitch($module)
    {
        $topNavigationArr = array();
        $hasBackendAccess = $this->container->get('contact')->get('hasBackendAccess');
        $activeClass = '';
        $bookedModule = $this->container->get('club')->get('bookedModulesDet');
        //If contact having only internal access, then application selection switch is not visible
        if (in_array('frontend2', $bookedModule)) {
            $activeClass = (( $module == 'cms' || $module == 'website')) ? 'active' : '';
            $topNavigationArr['switch'][] = array('title' => $this->get('translator')->trans('TOP_NAV_WEBSITE'), 'url' => $this->generateUrl('website_public_home_page'), 'class' => $activeClass);
        }
        $topNavigationArr['switch'][] = array('title' => $this->get('translator')->trans('TOP_NAV_INTERNAL'), 'url' => $this->generateUrl('internal_dashboard'), 'class' => ($activeClass != '') ? '' : 'active');
        if ($hasBackendAccess) {
            $topNavigationArr['switch'][] = array('title' => $this->get('translator')->trans('TOP_NAV_BACKEND'), 'url' => $this->generateUrl('show_dashboard'));
        }
        if (!count($topNavigationArr['switch'])) {
            $topNavigationArr['logo'][] = array('url' => $this->generateUrl('internal_dashboard'), 'class' => 'navbar-brand fg-top-logo-internal');
        }

        return $topNavigationArr;
    }

    /**
     * This function is used to set the top navigation CMS manu.
     *
     * @param array  $availableUserRights Available user rights array
     * @param int    $clubId              Current Club Id
     * @param int    $isClubAdmin         Is the logged in contact is club admin or not
     * @param int    $isSuperAdmin        Is the logged in contact is super admin or not
     * @param array  $module              Is the logged in contact is super admin or not
     * @param string $level1              Active level1 menu of the topnavigation
     * 
     * @return array $topNavigationCmsArr Top navigation CMS array block
     */
    private function setTopNavigationCmsBlock($availableUserRights, $clubId, $isClubAdmin, $isSuperAdmin, $module, $level1)
    {
        $em = $this->getDoctrine()->getManager();
        $isCmsAdmin = (in_array('ROLE_CMS_ADMIN', $availableUserRights)) ? 1 : 0;
        $isPageAdmin = (in_array('ROLE_PAGE_ADMIN', $availableUserRights)) ? 1 : 0;
        //checking whether form inquiries exist in that club
        $isFormInquiriesExist = $em->getRepository('CommonUtilityBundle:FgCmsPageContentElementFormInquiries')->hasFormInquiries($clubId);

        $pageMainNavUrl = '';
        $topNavigationCmsArr = array();

        if (($isCmsAdmin) || ($isClubAdmin) || ($isSuperAdmin)) {
            //give class fg-dev-header-menu-with-sidebar and sidebarType = localstorage key for managing loading sidebar on top nav menu click
            $subMenuCms[] = array('title' => $this->get('translator')->trans('TOP_NAV_CMS_NAVIGATIONPAGES'), 'class' => (($module == 'cms' && $level1 == 'navigation') ? 'fg-dev-header-nav-active active' : ''), 'url' => $this->generateUrl('website_cms_listpages'), 'breadcrumbTitle' => $this->get('translator')->trans('TOP_NAV_CMS_NAVIGATIONPAGES'), 'sidebarType' => 'CMS', 'sidebarClass' => 'fg-dev-header-menu-with-sidebar');
            $subMenuCms[] = array('title' => $this->get('translator')->trans('TOP_NAV_CMS_FOOTER'), 'class' => (($module == 'cms' && $level1 == 'footer') ? 'fg-dev-header-nav-active active' : ''), 'url' => $this->generateUrl('website_cms_edit_global_footer'), 'breadcrumbTitle' => $this->get('translator')->trans('TOP_NAV_CMS_FOOTER'));
            if ($isFormInquiriesExist) {
                $subMenuCms[] = array('title' => $this->get('translator')->trans('TOP_NAV_CMS_FORMINQUIRY'), 'class' => (($module == 'cms' && $level1 == 'forminquiry') ? 'fg-dev-header-nav-active active' : ''), 'url' => $this->generateUrl('website_cms_form_inquiry'), 'breadcrumbTitle' => $this->get('translator')->trans('TOP_NAV_CMS_FORMINQUIRY'), 'sidebarType' => 'FORMINQUIRY', 'sidebarClass' => 'fg-dev-header-menu-with-sidebar');
            }
            $subMenuCms[] = array('title' => $this->get('translator')->trans('TOP_NAV_ARTICLE_USERRIGHTS'), 'class' => (($module == 'cms' && $level1 == 'userrights') ? 'fg-dev-header-nav-active active' : ''), 'url' => $this->generateUrl('website_cms_userrights'), 'breadcrumbTitle' => $this->get('translator')->trans('TOP_NAV_ARTICLE_USERRIGHTS'));
            $subMenuCms[] = array('title' => $this->get('translator')->trans('TOP_NAV_CMS_THEME'), 'class' => (($module == 'cms' && $level1 == 'design') ? 'fg-dev-header-nav-active active' : ''), 'url' => $this->generateUrl('website_theme_configuration_list'), 'breadcrumbTitle' => $this->get('translator')->trans('TOP_NAV_CMS_THEME'));
            $subMenuCms[] = array('title' => $this->get('translator')->trans('TOP_NAV_CMS_SETTINGS'), 'class' => (($module == 'cms' && $level1 == 'settings') ? 'fg-dev-header-nav-active active' : ''), 'url' => $this->generateUrl('website_cms_settings'), 'breadcrumbTitle' => $this->get('translator')->trans('TOP_NAV_CMS_SETTINGS'));
            $pageMainNavUrl = '#';
        } else if ($isPageAdmin) {
            $pageMainNavUrl = $this->generateUrl("website_cms_listpages");
        }
        if ($pageMainNavUrl != '') {
            $cmsNavigation = array('title' => $this->get('translator')->trans('TOP_NAV_CMS'), 'class' => (($module == 'cms') ? 'fg-dev-header-nav-active active' : ''), 'breadcrumbTitle' => $this->get('translator')->trans('TOP_NAV_CMS'), 'url' => $pageMainNavUrl);
            if (count($subMenuCms) > 0) {
                $cmsNavigation['submenu'] = $subMenuCms;
            }
            $topNavigationCmsArr = $cmsNavigation;
        }

        return $topNavigationCmsArr;
    }

    /**
     * This function is used to set the top navigation of Team, Workgroup & Article manu.
     *
     * @param array  $availableUserRights Available user rights array
     * @param int    $isArticleAdmin      Is the logged in contact is article admin or not
     * @param int    $isClubAdmin         Is the logged in contact is club admin or not
     * @param int    $isSuperAdmin        Is the logged in contact is super admin or not  
     * @param array  $module              Is the logged in contact is super admin or not   
     * @param string $level1              Active level1 menu of the topnavigation
     * @param string $level2              Active level2 menu of the topnavigation
     * 
     * @return array $topNavigationRoleArr Top navigation Role block
     */
    private function setTopNavigationRoleArticleBlock($availableUserRights, $isArticleAdmin, $isClubAdmin, $isSuperAdmin, $module, $level1, $level2)
    {
        //team/workgroup admin | team/workgroup article admin    
        $isGroupAdmin = (count(array_intersect(array('ROLE_GROUP_ADMIN', 'ROLE_ARTICLE_ADMIN'), $availableUserRights)) > 0) ? 1 : 0;
        if (($isArticleAdmin) || ($isClubAdmin) || ($isSuperAdmin) || ($isGroupAdmin)) {
            $subMenuArticle[] = array('title' => $this->get('translator')->trans('TOP_NAV_ARTICLE'), 'class' => (($level1 == 'article') ? 'fg-dev-header-nav-active active' : ''), 'url' => $this->generateUrl('internal_article_list'), 'breadcrumbTitle' => $this->get('translator')->trans('TOP_NAV_ARTICLE'));
            $subMenuArticle[] = array('title' => $this->get('translator')->trans('TOP_NAV_ARTICLE_EDITORIAL'), 'class' => (($level1 == 'editorial') ? 'fg-dev-header-nav-active active' : ''), 'url' => $this->generateUrl('internal_article_editorial_list'), 'breadcrumbTitle' => $this->get('translator')->trans('TOP_NAV_ARTICLE_EDITORIAL'), 'sidebarType' => 'ARTICLE', 'sidebarClass' => 'fg-dev-header-menu-with-sidebar');
            $articleMainNavUrl = '#';
        } else {
            $articleMainNavUrl = $this->generateUrl('internal_article_list');
        }

        $subSubMenuArticle[] = array('title' => $this->get('translator')->trans('TOP_NAV_ARTICLE_COMMENTS'), 'url' => $this->generateUrl('internal_article_settings_comments_page'), 'class' => (($level2 == 'articlecomments') ? 'fg-dev-header-nav-active active' : ''));
        $subSubMenuArticle[] = array('title' => $this->get('translator')->trans('TOP_NAV_ARTICLE_USERRIGHTS'), 'url' => $this->generateUrl('internal_article_settings_userrights'), 'class' => (($level2 == 'articleuserrights') ? 'fg-dev-header-nav-active active' : ''));
        $subSubMenuArticle[] = array('title' => $this->get('translator')->trans('TOP_NAV_ARTICLE_MULTILANG'), 'url' => $this->generateUrl('internal_article_settings_multilanguage_page'), 'class' => (($level2 == 'articlemultilang') ? 'fg-dev-header-nav-active active' : ''));

        if (($isArticleAdmin) || ($isClubAdmin) || ($isSuperAdmin)) {
            $articleBreadcrumbTitle = ($level2 == 'articlecomments') ? $this->get('translator')->trans('TOP_NAV_ARTICLE_COMMENTS') : (($level2 == 'articleuserrights') ? $this->get('translator')->trans('TOP_NAV_ARTICLE_USERRIGHTS') : (($level2 == 'articlemultilang') ? $this->get('translator')->trans('TOP_NAV_ARTICLE_MULTILANG') : '') );
            $subMenuArticle[] = array('title' => $this->get('translator')->trans('TOP_NAV_ARTICLE_SETTINGS'), 'class' => (($level1 == 'articlesettings') ? 'dropdown-submenu fg-dev-header-nav-active active' : 'dropdown-submenu'), 'url' => '#', 'subMenu' => $subSubMenuArticle, 'breadcrumbTitle' => ($level1 == 'articlesettings') ? $this->get('translator')->trans('TOP_NAV_ARTICLE_SETTINGS') : '', 'breadcrumbTitle' => $articleBreadcrumbTitle);
        }
        $roleArticleNavigation = array('title' => $this->get('translator')->trans('TOP_NAV_ARTICLE'), 'class' => (($module == 'article') ? 'fg-dev-header-nav-active active' : ''), 'breadcrumbTitle' => $this->get('translator')->trans('TOP_NAV_ARTICLE'), 'url' => $articleMainNavUrl);
        if (count($subMenuArticle) > 0) {
            $roleArticleNavigation['submenu'] = $subMenuArticle;
        }

        return $roleArticleNavigation;
    }

    /**
     * This function is used to set the top navigation of calendar/gallery & search manu.
     *
     * @param array  $topNavigationLeftArr Existing topnav array
     * @param int    $adminFlag            Is the logged in contact is an admin
     * @param array  $module               Is the logged in contact is super admin or not   
     * @param string $level1               Active level1 menu of the topnavigation
     * 
     * @return array $topNavigationRoleArr Top navigation Role block
     */
    private function setTopNavigationCalendarBlock($topNavigationLeftArr, $adminFlag, $module, $level1)
    {
        $subMenu = array();
        if ($adminFlag == 1) {
            $subMenu[] = array('title' => $this->get('translator')->trans('TOP_NAV_CALENDAR'), 'class' => (($level1 == 'calendar') ? ' fg-dev-header-nav-active active' : ''), 'url' => $this->generateUrl('internal_calendar_view'), 'breadcrumbTitle' => $this->get('translator')->trans('TOP_NAV_CALENDAR'));
            $subMenu[] = array('title' => $this->get('translator')->trans('TOP_NAV_CALENDAR_USER_RIGHTS'), 'class' => (($module == 'calendar' && $level1 == 'userrights') ? ' fg-dev-header-nav-active active' : ''), 'url' => $this->generateUrl('internal_calendar_userrights'), 'breadcrumbTitle' => ($module == 'calendar' && $level1 == 'userrights') ? $this->get('translator')->trans('TOP_NAV_CALENDAR_USER_RIGHTS') : '');
            $calendarMainNavUrl = '#';
        } else {
            $calendarMainNavUrl = $this->generateUrl('internal_calendar_view');
        }

        $calendarNavigation = array('title' => $this->get('translator')->trans('TOP_NAV_CALENDAR'), 'class' => (($module == 'calendar') ? 'fg-dev-header-nav-active active' : ''), 'breadcrumbTitle' => $this->get('translator')->trans('TOP_NAV_CALENDAR'), 'url' => $calendarMainNavUrl);
        if (count($subMenu) > 0) {
            $calendarNavigation['submenu'] = $subMenu;
        }
        $topNavigationLeftArr['leftmenu'][] = $calendarNavigation;
        $topNavigationLeftArr['leftmenu'][] = array('title' => $this->get('translator')->trans('TOP_NAV_GALLERY'), 'class' => (($module == 'gallery') ? 'fg-dev-header-nav-active active' : ''), 'url' => $this->generateUrl('internal_gallery_view'), 'breadcrumbTitle' => $this->get('translator')->trans('TOP_NAV_GALLERY'));
        //Autocomplete search
        $topNavigationLeftArr['rightmenu'][] = array('title' => $this->get('translator')->trans('TOP_NAV_SEARCH') . '...', 'url' => 'search');

        return $topNavigationLeftArr;
    }

    /**
     * This function is used to set the top navigation of team/workgroup & mega manu.
     *
     * @param array  $module Is the logged in contact is super admin or not   
     * @param string $level1 Active level1 menu of the topnavigation
     * 
     * @return array $rightMenu,$megaMenuArray  Top navigation megamenu array and right menu
     */
    private function setTopNavigationTeamWorkgroupMenu($module, $level1)
    {
        $assignedTeams = $this->container->get('contact')->get('teams');
        $assignedWorkgroups = $this->container->get('contact')->get('workgroups');
        $rightMenu = $megaMenuArray = array();
        //Team Navigation
        if (count($assignedTeams) > 0) {
            $keys = array_keys($assignedTeams);
            $teamDocsCount = $this->getRoleDocumentsCountInternal('TEAM');
            $title = (count($assignedTeams) > 1) ? $this->get('translator')->trans('TOP_NAV_MY_TEAMS', array('%teams%' => $this->get('fairgate_terminology_service')->getTerminology('Team', $this->container->getParameter('plural')))) : ucfirst($assignedTeams[$keys[0]]);
            $subMenu = $this->getTeamHeaderMenus($module, $level1, $teamDocsCount);
            if (count($subMenu) > 0) {
                $rightMenu[] = array('title' => $title, 'url' => '#', 'submenu' => $subMenu, 'class' => (($module == 'team') ? 'fg-dev-header-nav-active active' : ''), 'breadcrumbTitle' => $title);
                $megaMenuArray[] = array('title' => $title, 'url' => '#', 'submenu' => $subMenu, 'class' => (($module == 'team') ? 'fg-dev-header-nav-active active' : ''), 'breadcrumbTitle' => $title);
            }
        }
        //Workgroup Navigation
        if (count($assignedWorkgroups) > 0) {
            $keys = array_keys($assignedWorkgroups);
            $wgDocsCount = $this->getRoleDocumentsCountInternal('WORKGROUP');
            $title = (count($assignedWorkgroups) > 1) ? $this->get('translator')->trans('TOP_NAV_MY_WORKGROUPS') : ucfirst($assignedWorkgroups[$keys[0]]);
            $subMenu = $this->getWorkgroupHeaderMenus($module, $level1, $wgDocsCount);
            if (count($subMenu) > 0) {
                $rightMenu[] = array('title' => $title, 'url' => '#', 'submenu' => $subMenu, 'class' => (($module == 'workgroup') ? 'fg-dev-header-nav-active active' : ''), 'breadcrumbTitle' => $title);
                $megaMenuArray[] = array('title' => $title, 'url' => '#', 'submenu' => $subMenu, 'class' => (($module == 'workgroup') ? 'fg-dev-header-nav-active active' : ''), 'breadcrumbTitle' => $title);
            }
        }

        if (!(count($rightMenu) && count($megaMenuArray))) {
            return false;
        } else {
            return array($rightMenu, $megaMenuArray);
        }
    }

    /**
     * This function is used to save the next previous data to the server
     *
     * @return Json response
     */
    public function saveNextPreviousDataAction()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $session = $this->get("session");
        $key = $request->get('key');
        $id = $request->get('id');
        $session->set($key, $id);

        return new JsonResponse(array());
    }
}
