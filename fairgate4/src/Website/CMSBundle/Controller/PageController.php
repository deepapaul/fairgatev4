<?php

/**
 * PageController.
 *
 * @package 	Website
 * @subpackage 	CMSBundle
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 *
 */
namespace Website\CMSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Website\CMSBundle\Util\FgPageContainerDetails;
use Website\CMSBundle\Util\FgPageContent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Website\CMSBundle\Util\FgPageElement;
use Website\CMSBundle\Util\FgCmsTheme;
use Website\CMSBundle\Util\FgFormElement;
use Common\UtilityBundle\Repository\Pdo\CmsPdo;
use Common\UtilityBundle\Util\FgUtility;
use Symfony\Component\Intl\Intl;
use Common\UtilityBundle\Repository\Pdo\SponsorPdo;
use Website\CMSBundle\Util\FgCmsPortraitFrontend;
use Common\UtilityBundle\Repository\Pdo\membershipPdo;

use Common\UtilityBundle\Routing\FgRoutingListener;

/**
 * PageController
 *
 * This controller is used for list/manage CMS pages
 */
class PageController extends Controller
{

    /**
     * This function is used to display index.
     *
     * @return object View Template Render Object
     */
    public function indexAction()
    {
        return $this->render('WebsiteCMSBundle:Dashboard:index.html.twig');
    }

    /**
     * This function is used to list pages.
     *
     * @return object View Template Render Object
     */
    public function listAllPagesAction()
    {
        $club = $this->container->get('club');
        $contact = $this->container->get('contact');
        $clubLang = $club->get('club_languages');
        $defLang = $club->get('club_default_lang');
        $conn = $this->container->get('database_connection');
        //Action menu settings
        $availableUserRights = $contact->get('availableUserRights');
        $isAdmin = (count(array_intersect(array('ROLE_CMS_ADMIN'), $availableUserRights)) > 0) ? 1 : 0;
        $pageAdmins = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:SfGuardUserPage')->getPageAdminNames($conn, $club->get('id'));
        $pageAdmins = json_encode(array_column($pageAdmins, 'contactname', 'contact'));
        $isPageAdmin = (count(array_intersect(array('ROLE_PAGE_ADMIN'), $availableUserRights)) > 0) ? 1 : 0;
        $returnArray['actionMenu'] = $this->actionMenuSettings($isAdmin);
        $returnArray['breadCrumb'] = array();
        $returnArray['clubLanguages'] = $clubLang;
        $returnArray['clubDefaultLang'] = $defLang;
        $returnArray['pageAdmins'] = $pageAdmins;
        $sidebarData = $this->getSidebarDetails();
        $returnArray['sidebarData'] = $sidebarData;
        $returnArray['clubId'] = $club->get('id');
        $returnArray['contactId'] = $contact->get('id');
        $returnArray['hasSidebar'] = ($isPageAdmin && !$isAdmin) ? 0 : 1;
        $tabs['cmsTabPreview'] = array('text' => $this->get('translator')->trans('CMS_PREVIEW'), 'activeClass' => 'active');
        $tabs['cmsTabContent'] = array('text' => $this->get('translator')->trans('CMS_CONTENT'));
        $returnArray['tabs'] = $tabs;
        $pageElementObj = new FgPageElement($this->container);
        $returnArray['allGalleries'] = $pageElementObj->getGalleryList();
        $returnArray['areas'] = $pageElementObj->getAllAreasForArticleAndCalendar();
        $clubDefaultLanguage = $this->container->get('club')->get('club_default_lang');
        $returnArray['fedId'] = $this->container->get('club')->get('federation_id');
        $returnArray['subFedId'] = $this->container->get('club')->get('sub_federation_id');
        $returnArray['clubType'] = $this->container->get('club')->get('type');
        $returnArray['articleCategories'] = $this->container->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgCmsArticleCategory')->getArticleCategories($returnArray['clubId'], $clubDefaultLanguage);
        $returnArray['calendarCategories'] = $this->container->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgEmCalendarCategory')->getCalendarCategories($returnArray['clubId'], $clubDefaultLanguage);
        //calendar count for deavtivating create special page if it is zero
        $returnArray['calendarCount'] = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgEmCalendar')->checkCalendarVisibility($this->container);
        //article count for deavtivating create special page if it is zero
        $returnArray['articleCount'] = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsArticle')->getArticleCountOfClubForCms($this->container);
        //collect header details
        $returnArray['headerDetails'] = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentType')->getHeaderDetails($club->get('type'));
       
        return $this->render('WebsiteCMSBundle:Page:listAllPages.html.twig', $returnArray);
    }

    /**
     * This function is used to save page details.
     *
     * @return object JSON Response Object
     */
    public function savePageDetailsAction()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $data['title'] = $request->get('cmsPageTitle');
        $data['type'] = 'page';
        $data['sidebarType'] = $request->get('cmsSidebarType');
        $data['sidebarArea'] = $request->get('cmsSidebarArea');
        $data['hideTitle'] = ($request->get('hideTitle') != '' ? $request->get('hideTitle') : 0);
        $navId = $request->get('cmsNavigation');

        $pageId = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->createPage($this->container, $data);
        $pageContainerObj = new FgPageContainerDetails($this->container);
        $pageContainerObj->setDefaultContainerSettings($pageId, 1, 1);
        if ($navId) {
            $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsNavigation')->savePageToNavigation($this->container, $navId, $pageId, 'existing');
        }

        //Adding global sidebar and footer
        $sidebarObj = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->findBy(array('type' => 'sidebar', 'club' => $this->container->get('club')->get('id')));
        if (!$sidebarObj) {
            $pageContainerObj->setNewPage('sidebar', 'sidebar', array('sidebarType' => 'small'));
            $pageContainerObj->setNewPage('sidebar', 'sidebar', array('sidebarType' => 'wide'));
        }
        //Adding global sidebar and footer
        $footerObj = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->findBy(array('type' => 'footer', 'club' => $this->container->get('club')->get('id')));
        if (!$footerObj) {
            $pageContainerObj->setNewPage('footer', 'footer');
        }
        //To create default theme and color scheme css
        $defaultConfig = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->findOneBy(array('isDefault' => '1', 'club' => $this->container->get('club')->get('id')));
        $configId = $defaultConfig->getId();
        $colorSchemes = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgTmThemeColorScheme')->findBy(array('isDefault' => '1'));
        $cmsTheme = new FgCmsTheme($this->container);
        if ($defaultConfig->getCssFilename() == '') {
            $cmsTheme->createCss($configId, 'theme');
        }
        foreach ($colorSchemes as $colorScheme) {
            if ($colorScheme->getCssFilename() == '') {
                $cmsTheme->createCss($configId, 'color', $colorScheme->getId());
            }
        }

        //redirect to content edit page
        $return = array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('CMS_CREATE_PAGE_SUCCESS'), 'noparentload' => true, 'pageId' => $pageId);

        return new JsonResponse($return);
    }

    /**
     * This function is used to show popup of existing/external assign functionality
     *
     * @return object View Template Render Object
     */
    public function assignPageAction()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $return['pageFlag'] = $request->get('pageFlag');
        $redirect = 'WebsiteCMSBundle:Page:existingExternalPagePopup.html.twig';
        //assign section
        if ($return['pageFlag'] === 'assign') {
            $return['module'] = $request->get('module');
            $return['navId'] = $request->get('navId');
            //existing popup
            if ($return['module'] == 'existing') {
                $club = $this->container->get('club');
                $clubId = $club->get('id');
                $defLang = $club->get('default_lang');
                $return['pagesList'] = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->getPagesList($clubId, $defLang);
            } elseif ($return['module'] == 'editExternal') {
                //edit external popup
                $navObj = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsNavigation')->find($return['navId']);
                $return['externalLink'] = $navObj->getExternalLink();
                $redirect = 'WebsiteCMSBundle:Page:editExternalPagePopup.html.twig';
            }
        } else {
            //un assign popup
            $unAssignPopup = $this->unAssignPagePopup($request);
            $redirect = $unAssignPopup['redirect'];
            $return = $unAssignPopup['popup'];
        }

        return $this->render($redirect, $return);
    }

    /**
     * This function is used to save existing/external assign functionality
     *
     * @return object JSON Response Object
     */
    public function assignPopupSaveAction()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $pageFlag = $request->get('pageFlag');
        if ($pageFlag === 'assign') {
            $module = $request->get('module');
            $navId = $request->get('navId');
            $em = $this->getDoctrine()->getManager();
            $return = array();

            $transModule = strtoupper($module);
            $pageId = $request->get('pageId');
            if ($module != 'external' && $module != 'editExternal' && $pageId != '') { // Page id should not be external link
                $pageType = $em->getRepository('CommonUtilityBundle:FgCmsPage')->find($pageId)->getType();
                $module = ($pageType == 'gallery' || $pageType == 'calendar' || $pageType == 'article') ? $pageType : $module;
            }
            $assignPageToNavigation = $em->getRepository('CommonUtilityBundle:FgCmsNavigation')->savePageToNavigation($this->container, $navId, $pageId, $module);
            if ($assignPageToNavigation) {
                $return = array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('CMS_' . $transModule . '_ASSIGN_PAGE_SUCCESS'), 'noparentload' => true, 'module' => $module, 'navId' => $navId);
            } else {
                $return = array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('CMS_' . $transModule . '_CREATE_PAGE_FAILED'), 'noparentload' => true, 'module' => $module, 'navId' => $navId);
            }
        } else {
            $return = $this->unAssignPageSave($request);
        }

        return new JsonResponse($return);
    }

    /**
     * This function is used to show popup for unassign page from listing
     *
     * @param Request $request Request object
     *
     * @return array redirect template and popup content
     */
    private function unAssignPagePopup(Request $request)
    {
        $checkedIds = $request->get('checkedIds');
        $checkedIdArr = explode(',', $checkedIds);
        $popup = $checkPageAssigned = array();
        $popup['pageArray'] = $request->get('pageDetails');
        //footer bar OK button or DELETE button
        $popup['totalCount'] = count($checkedIdArr);
        $pageAssignment = 0;
        foreach ($checkedIdArr as $pageId) {
            $checkPageAssignedRes = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsNavigation')->checkPageAssignedToNavigation($pageId);
            //if page is assigned to navigation point increment variable
            if (count($checkPageAssignedRes) > 0) {
                $pageAssignment++;
                foreach ($checkPageAssignedRes as $navIds) {
                    $checkPageAssigned[$pageId][] = $navIds['id'];
                }
            } else {
                unset($checkPageAssigned[$pageId]);
            }
        }
        $popup['pageAssigned'] = json_encode($checkPageAssigned);
        //retrive content details of a page
        $pageContentObj = new FgPageContent($this->container);
        $popup = $pageContentObj->getPopupContentData($checkedIdArr, $pageAssignment, $popup, $this->get('translator'));

        return array('redirect' => 'WebsiteCMSBundle:Page:unAssignPagePopup.html.twig', 'popup' => $popup);
    }

    /**
     * This function is used to save unassign page
     *
     * @param Request $request Request object
     *
     * @return array query success return
     */
    private function unAssignPageSave(Request $request)
    {
        $navIdArr = $request->get('navIds');
        $totCount = $request->get('totCount');
        $successCount = 0;
        foreach ($navIdArr as $navId) {
            $em = $this->getDoctrine()->getManager();
            $em->getRepository('CommonUtilityBundle:FgCmsNavigation')->unAssignPageFromNav($navId, $this->container);
            $successCount++;
        }
        $transText = '';
        if ($totCount > 1) {
            $transText = ($totCount == $successCount) ? $this->get('translator')->trans('CMS_UNASSIGN_PAGE_SUCCESS_ALL', array('%count%' => $totCount)) : $this->get('translator')->trans('CMS_UNASSIGN_PAGE_SUCCESS_MIXED', array('%sucCount%' => $successCount, '%count%' => $totCount));
        } else {
            $transText = $this->get('translator')->trans('CMS_UNASSIGN_PAGE_SUCCESS');
        }

        return array('status' => 'SUCCESS', 'flash' => $transText, 'noparentload' => true);
    }

    /**
     * This function is used to save page details.
     *
     * @param boolean $adminflag is admin or not.
     *
     * @return Array
     */
    private function actionMenuSettings($adminflag = 0)
    {
        if ($adminflag) {
            //none selection begins
            $noneSelectedText['createPage'] = array('isVisibleAlways' => 'true', 'title' => $this->get('translator')->trans('CL_CREATE'), 'dataUrl' => '', 'isActive' => 'true');
            $noneSelectedText['editPage'] = array('title' => $this->get('translator')->trans('CL_EDIT'), 'dataUrl' => '', 'isActive' => 'false');
            $noneSelectedText['previewPage'] = array('title' => $this->get('translator')->trans('CMS_CONTENT_PREVIEW'), 'dataUrl' => '', 'isActive' => 'false');
            $noneSelectedText['unAssignPage'] = array('title' => $this->get('translator')->trans('UN_ASSIGN'), 'dataUrl' => '', 'isActive' => 'false');
            $noneSelectedText['deletePage'] = array('title' => $this->get('translator')->trans('DELETE_BUTTON_TEXT'), 'dataUrl' => '', 'isActive' => 'false');
            //single selection begins
            $singleSelectedText['createPage'] = array('title' => $this->get('translator')->trans('CL_CREATE'), 'dataUrl' => '', 'isActive' => 'false');
            $singleSelectedText['editPage'] = array('title' => $this->get('translator')->trans('CL_EDIT'), 'dataUrl' => '', 'isActive' => 'true');
            $singleSelectedText['previewPage'] = array('title' => $this->get('translator')->trans('CMS_CONTENT_PREVIEW'), 'dataUrl' => '', 'isActive' => 'true');
            $singleSelectedText['unAssignPage'] = array('title' => $this->get('translator')->trans('UN_ASSIGN'), 'dataUrl' => '', 'isActive' => 'true');
            $singleSelectedText['deletePage'] = array('title' => $this->get('translator')->trans('DELETE_BUTTON_TEXT'), 'dataUrl' => '', 'isActive' => 'true');
            //multiple selection begins
            $multipleSelectedText['createPage'] = array('title' => $this->get('translator')->trans('CL_CREATE'), 'dataUrl' => '', 'isActive' => 'false');
            $multipleSelectedText['editPage'] = array('title' => $this->get('translator')->trans('CL_EDIT'), 'dataUrl' => '', 'isActive' => 'false');
            $multipleSelectedText['previewPage'] = array('title' => $this->get('translator')->trans('CMS_CONTENT_PREVIEW'), 'dataUrl' => '', 'isActive' => 'false');
            $multipleSelectedText['unAssignPage'] = array('title' => $this->get('translator')->trans('UN_ASSIGN'), 'dataUrl' => '', 'isActive' => 'true');
            $multipleSelectedText['deletePage'] = array('title' => $this->get('translator')->trans('DELETE_BUTTON_TEXT'), 'dataUrl' => '', 'isActive' => 'true');
        } else {
            $noneSelectedText = array();
            $singleSelectedText = array();
            $multipleSelectedText = array();
        }

        return array('none' => $noneSelectedText, 'single' => $singleSelectedText, 'multiple' => $multipleSelectedText, 'adminFlag' => $adminflag);
    }

    /**
     * This function is used to get JSON data for page-list datatable.
     *
     * @return object JSON Response Object
     */
    public function getPageListAction()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $club = $this->container->get('club');
        $contact = $this->container->get('contact');
        $menutype = $request->get('menuType');
        $contactId = $contact->get('id');
        $defLang = $club->get('default_lang');
        $availableUserRights = $contact->get('availableUserRights');
        $isPageAdmin = (count(array_intersect(array('ROLE_PAGE_ADMIN'), $availableUserRights)) > 0) ? 1 : 0;
        $isCmsAdmin = (count(array_intersect(array('ROLE_CMS_ADMIN'), $availableUserRights)) > 0) ? 1 : 0;
        //IF user has page admin privilage only
        $userObj = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:SfGuardUser')->findOneBy(array('contact' => $contactId, 'club' => $club->get('id')));
        $pageAdminUserId = ($isPageAdmin && !$isCmsAdmin) ? $userObj->getId() : '';
        $dataList = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->listPageDetails($club->get('id'), $defLang, $menutype, $pageAdminUserId);
        $navDet = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsNavigation')->getSidebarNavigationMainMenus($this->container, false);
        $dataSet['aaData'] = $dataList;
        $dataSet['iTotalDisplayRecords'] = count($dataList);
        $dataSet['iTotalRecords'] = count($dataList);
        $dataSet['navDetails'] = $navDet;
        $dataSet['isPageAdmin'] = $isPageAdmin;
        $dataSet['isCmsAdmin'] = $isCmsAdmin;

        return new JsonResponse($dataSet);
    }

    /**
     * This function is used to show confirmation popup to delete page
     *
     * @param Request $request Request object
     *
     * @return object View Template Render Object
     */
    public function deletePagePopupAction(Request $request)
    {
        $pageArray = $request->get('pageArray');
        $pageCount = count($pageArray);
        $hasElements = 0;

        $type = $pageArray[0]['type'];
        foreach ($pageArray as $val) {
            if ($val['type'] != $type) {
                $type = 'default';
            }
            if ($val['elementCount'] > 0) {
                $type = 'default';
                $hasElements = 1;
                continue;
            }
        }
        if ($type == 'gallery') {
            $popupTitle = ($pageCount > 1) ? $this->get('translator')->trans('DELETE_CMS_SPLPAGE_POPUP_GALLERY_TITlE_PLURAL') : $this->get('translator')->trans('DELETE_CMS_SPLPAGE_POPUP_GALLERY_TITlE_SINGLE');
            $popupText = ($pageCount > 1) ? str_replace('%count%', $pageCount, $this->get('translator')->trans('DELETE_CMS_SPLPAGE_POPUP_GALLERY_DESCRIPTION_PLURAL')) : $this->get('translator')->trans('DELETE_CMS_SPLPAGE_POPUP_GALLERY_DESCRIPTION_SINGLE');
        } else if ($type == 'calendar') {
            $popupTitle = ($pageCount > 1) ? $this->get('translator')->trans('DELETE_CMS_SPLPAGE_POPUP_CALENDAR_TITlE_PLURAL') : $this->get('translator')->trans('DELETE_CMS_SPLPAGE_POPUP_CALENDAR_TITlE_SINGLE');
            $popupText = ($pageCount > 1) ? str_replace('%count%', $pageCount, $this->get('translator')->trans('DELETE_CMS_SPLPAGE_POPUP_CALENDAR_DESCRIPTION_PLURAL')) : $this->get('translator')->trans('DELETE_CMS_SPLPAGE_POPUP_CALENDAR_DESCRIPTION_SINGLE');
        } elseif ($type == 'article') {
            $popupTitle = ($pageCount > 1) ? $this->get('translator')->trans('DELETE_CMS_SPLPAGE_POPUP_ARTICLE_TITlE_PLURAL') : $this->get('translator')->trans('DELETE_CMS_SPLPAGE_POPUP_ARTICLE_TITlE_SINGLE');
            $popupText = ($pageCount > 1) ? str_replace('%count%', $pageCount, $this->get('translator')->trans('DELETE_CMS_SPLPAGE_POPUP_ARTICLE_DESCRIPTION_PLURAL')) : $this->get('translator')->trans('DELETE_CMS_SPLPAGE_POPUP_ARTICLE_DESCRIPTION_SINGLE');
        } else {
            $popupTitle = ($pageCount > 1) ? $this->get('translator')->trans('DELETE_CMS_PAGE_POPUP_TITlE_PLURAL') : $this->get('translator')->trans('DELETE_CMS_PAGE_POPUP_TITlE_SINGLE');
            $popupText = ($pageCount > 1) ? str_replace('%count%', $pageCount, $this->get('translator')->trans('DELETE_CMS_PAGE_POPUP_DESCRIPTION_PLURAL')) : $this->get('translator')->trans('DELETE_CMS_PAGE_POPUP_DESCRIPTION_SINGLE');
        }
        if ($hasElements) {
            $popupText = ($pageCount > 1) ? $this->get('translator')->trans('DELETE_CMS_PAGE_AND_ELEMENTS_POPUP_DESCRIPTION_MULTIPLE') : $this->get('translator')->trans('DELETE_CMS_PAGE_AND_ELEMENTS_POPUP_DESCRIPTION');
        }
        $return = array("title" => $popupTitle, 'text' => $popupText, 'pageArray' => $pageArray, 'pageCount' => $pageCount, 'buttonText' => $this->get('translator')->trans('DELETE_BUTTON_TEXT'), 'type' => 'delete');

        return $this->render('WebsiteCMSBundle:Page:ConfirmationPopup.html.twig', $return);
    }

    /**
     * Function is used to delete pages
     *
     * @param Request $request Request object
     *
     * @return object JSON Response Object
     */
    public function deleteAction(Request $request)
    {
        $deleteData = $request->get('pageDetails');
        $type = $deleteData[0]['type'];

        foreach ($deleteData as $value) {
            if ($value['type'] != $type) {
                $type = 'default';
            }
            $deletePages[] = $value['id'];
        }
        $pageCount = count($deleteData);
        if ($type == 'gallery') {
            $flash = ($pageCount > 1) ? $this->get('translator')->trans('CMS_GALLERY_SPLPAGE_DELETED_SUCCESS_PLURAL', array('%count%' => $pageCount)) : $this->get('translator')->trans('CMS_GALLERY_SPLPAGE_DELETED_SUCCESS_SINGLE');
        } else if ($type == 'calendar') {
            $flash = ($pageCount > 1) ? $this->get('translator')->trans('CMS_CALENDAR_SPLPAGE_DELETED_SUCCESS_PLURAL', array('%count%' => $pageCount)) : $this->get('translator')->trans('CMS_CALENDAR_SPLPAGE_DELETED_SUCCESS_SINGLE');
        } elseif ($type == 'article') {
            $flash = ($pageCount > 1) ? $this->get('translator')->trans('CMS_ARTICLE_SPLPAGE_DELETED_SUCCESS_PLURAL', array('%count%' => $pageCount)) : $this->get('translator')->trans('CMS_ARTICLE_SPLPAGE_DELETED_SUCCESS_SINGLE');
        } else {
            $flash = ($pageCount > 1) ? $this->get('translator')->trans('CMS_PAGE_DELETED_SUCCESS_PLURAL', array('%count%' => $pageCount)) : $this->get('translator')->trans('CMS_PAGE_DELETED_SUCCESS_SINGLE');
        }
        $cmsPdo = new CmsPdo($this->container);
        $pageAdmins = $cmsPdo->getPageAdminIds($deletePages);
        $conn = $this->getDoctrine()->getManager()->getConnection();
        $clubId = $this->container->get('club')->get('id');
        $contactId = $this->container->get('contact')->get('id');
        $userRightsArray = array();
        $pgAdmin = $this->container->getParameter('page_admin');
        if (!empty($pageAdmins)) {
            foreach ($pageAdmins as $pageAdmin) {
                $userRightsArray['new']['pgAdmin'][$pgAdmin]['contact'][$pageAdmin['contact_id']]['team'] = 'deleted';
            }
        }
        // Calling common save function to save the user rights
        $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:SfGuardGroup')->saveUserRights($conn, $userRightsArray, $clubId, $contactId, $this->container);
        $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->deletePages($deletePages, $this->container);
        $cmsPdo->deleteUserRightsGroup();
        $return = array('status' => 'SUCCESS', 'flash' => $flash, 'noparentload' => true);

        return new JsonResponse($return);
    }

    /**
     * This function is used to get the data for building the sidebar template
     *
     * @return array $sidebarData Sidebar data array
     */
    private function getSidebarDetails()
    {
        $sidebarData = array();
        $sidebarData['PAGES'] = $this->getNavigationPageMenus();
        $sidebarData['MM'] = $this->getNavigationMainMenus();
        $sidebarData['ADDITIONAL'] = $this->getNavigationAdditionalMenus();

        return $sidebarData;
    }

    /**
     * This function is used to get the page menu details for building sidebar
     *
     * @return array $pageMenus Page menu detalis array
     */
    private function getNavigationPageMenus()
    {
        $clubId = $this->container->get('club')->get('id');
        $pagesCount = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->getCountOfPagesForSidebar($clubId);

        $pageMenus = array();
        $pageMenus['id'] = 'PAGES';
        $pageMenus['title'] = $this->get('translator')->trans('CMS_NAVIGATION_SIDEBAR_PAGES');
        $pageMenus['entry'][] = array('id' => 'all_pages', 'title' => $this->get('translator')->trans('CMS_NAVIGATION_SIDEBAR_ALL_PAGES'), 'showCount' => true, 'showIcon' => false, 'count' => $pagesCount['allPages'], 'draggable' => 0, 'itemType' => 'PAGES', 'menuType' => 'PAGES');
        $pageMenus['entry'][] = array('id' => 'pages_without_navigation', 'title' => $this->get('translator')->trans('CMS_NAVIGATION_SIDEBAR_PAGES_WITHOUT_NAVIGATION'), 'showCount' => true, 'showIcon' => false, 'count' => $pagesCount['unassignedPages'], 'draggable' => 0, 'itemType' => 'PAGES', 'menuType' => 'PAGES');

        return $pageMenus;
    }

    /**
     * To retrieve the page content details using page id
     *
     * @param integer $pageId page id
     *
     * @return object View Template Render Object
     */
    public function editPageContentAction($pageId)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $this->container->get('session');
        $pageObject = $em->getRepository('CommonUtilityBundle:FgCmsPage')->find($pageId);
        $session->set("lastCmsPageEditTime_" + $pageId, $pageObject->getContentUpdateTime()->format('Y-m-d H:i:s'));
        $session->remove('cmsPageContentIdArray');
        //club service
        $clubService = $this->container->get('club');
        //retrieve default language
        $clubDefaultLang = $clubService->get('club_default_lang');
        $contactLang = $clubService->get('default_lang');
        //retrive content details of a page
        $pageContentObj = new FgPageContent($this->container);
        $pageContentDetails = $pageContentObj->getContentElementData($pageId);
        //to save page content
        $pageContentObj->saveJsonContent($pageId);

        if (count($pageContentDetails) == 0) {
            throw $this->createNotFoundException($this->clubTitle . ' have no access to this page');
        }
        if ($pageContentDetails['page']['pageType'] == 'sidebar') {
            $url = $this->generateUrl('website_cms_content_sidebar_edit', array('pageId' => $pageId));
            return new RedirectResponse($url);
        }
        if ($pageContentDetails['page']['pageType'] == 'footer') {
            $url = $this->generateUrl('website_cms_edit_global_footer');
            return new RedirectResponse($url);
        }
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $session->set('lastVisitedCmsPage', $request->getUri());
        $breadCrumb = array('back' => $this->generateUrl('website_cms_listpages'));
        $hasSidebar = $this->sidebarAccess();
        $tabUrl = $this->getPreviewUrl($pageId);
        $listAllpages = $this->generateUrl('website_cms_listpages');
        $navUrl = ($tabUrl == $listAllpages) ? 1 : 0;
        switch ($pageContentDetails['page']['pageType']) {
            case 'page':
                $tabs['preview'] = array('text' => $this->get('translator')->trans('CMS_PREVIEW'), 'url' => $tabUrl, 'name' => '', 'type' => 'preview', 'activeClass' => '', 'hrefLink' => true);
                $tabs['content'] = array('text' => $this->get('translator')->trans('CMS_CONTENT'), 'url' => $this->generateUrl('website_cms_page_edit', array('pageId' => $pageId)), 'name' => '', 'type' => 'content', 'activeClass' => 'active', 'hrefLink' => true);
                break;
        }
        //collect header details
        $headerDetails = $em->getRepository('CommonUtilityBundle:FgCmsPageContentType')->getHeaderDetails($clubService->get('type'));
        //calendar event count for deavtivating the drag if it is zero
        $calendarCount = $em->getRepository('CommonUtilityBundle:FgEmCalendar')->checkCalendarVisibility($this->container);
        //article count for deavtivating the drag if it is zero
        $articleCount = $em->getRepository('CommonUtilityBundle:FgCmsArticle')->getArticleCountOfClubForCms($this->container);
        //contact application count for deavtivating the drag if it is zero
        $contactAppCount = $this->getDoctrine()->getRepository('CommonUtilityBundle:FgCmsForms')->activeContactFormAppList($clubService->get('id'), true);
        if (in_array('sponsor', $clubService->get('bookedModulesDet'))) {
            $objSponsorPdo = new SponsorPdo($this->container);
            $services = $objSponsorPdo->getSponsorsServices($clubService->get('id'), $clubService->get('club_default_lang'));
            $sponsorActive = count($services) > 0 ? 1 : 0;
            if ($sponsorActive < 1) {
                $toolTipMessage = $this->get('translator')->trans('CREATE_SERVICE_MESSAGE', array(), 'tooltip');
            }
        } else {
            $sponsorActive = 0;
            $toolTipMessage = $this->get('translator')->trans('BOOK_SPONSOR_MESSAGE', array(), 'tooltip');
        }
        $commModuleAvailable = (in_array('communication', $clubService->get('bookedModulesDet'))) ? 1 : 0;
        //global sidebar settings
        $globalSidebar = $em->getRepository('CommonUtilityBundle:FgCmsPage')->getGlobalSidebarSetting($clubService->get('id'));
        //collect club languages
        $clubLanguages = $clubService->get('club_languages');
        //retrive clip board elements of a club
        $clipboardContentDetails = $em->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->getClipboardDetails($clubService->get('id'), $clubDefaultLang);

        //To get the map element count in a page
        $mapElementCount = $em->getRepository('CommonUtilityBundle:FgCmsPage')->getMapElementCountInPage($pageId);
        $contactTableElementCount = $em->getRepository('CommonUtilityBundle:FgCmsPage')->getContactTableElementCountInPage($pageId);
        $newsletterArchiveElementCount = $em->getRepository('CommonUtilityBundle:FgCmsPage')->getNewsletterArchiveElementCountInPage($pageId);
        $pageTitleStatus = ($pageObject->getHideTitle() ? 1 : 0);
        $pageContentDetails['googleCaptchaSitekey'] = $this->container->getParameter('googleCaptchaSitekey');
        $twitterElementCount = $em->getRepository('CommonUtilityBundle:FgCmsPage')->getTwitterElementCountInPage($pageId);
        $contactService = $this->container->get('contact');
        $hasContactRights = ((in_array('ROLE_CONTACT', $contactService->get('availableUserRights'))) && in_array('contact', $clubService->get('bookedModulesDet'))) ? 1 : 0;
        $hasAdminRights = $this->hasAdminRights();
        $pageElementArray = json_decode($pageObject->getPageElement(),true);
        $portraitElementSettings = $this->getPortraitElementSettings($pageElementArray);

        return $this->render('WebsiteCMSBundle:Page:editPageContent.html.twig', array('hasContactRights' => $hasContactRights, 'hasAdminRights' => $hasAdminRights, 'pageTitleStatus' => $pageTitleStatus, 'pagecontentData' => $pageContentDetails, 'tabs' => $tabs, 'cmsClubLanguages' => $clubLanguages, 'clubDefaultLang' => $clubDefaultLang, 'contactLang' => $contactLang, 'clipboardDetails' => $clipboardContentDetails, 'headerDetails' => $headerDetails, 'breadCrumb' => $breadCrumb, 'globalSidebar' => $globalSidebar, 'isActveTab' => 'content', 'mapElementCount' => $mapElementCount, 'twitterElementCount' => $twitterElementCount, 'contactTableElementCount' => $contactTableElementCount, 'hasSidebar' => $hasSidebar, 'articleCount' => $articleCount, 'calendarCount' => $calendarCount, 'sponsorActive' => $sponsorActive, 'toolTipMessage' => $toolTipMessage, 'mainPageId' => $pageId, 'navUrl' => $navUrl, 'commModuleAvailable' => $commModuleAvailable, 'contactAppCount' => $contactAppCount['count'], 'initialForm' => $contactAppCount['id'], 'newsletterArchiveElementCount' => $newsletterArchiveElementCount,'portraitElementSettings' =>$portraitElementSettings,'portUploadPath' => $this->getPortraitUploadPath()));
    }

    /**
     * Method to find whether the logged in contact has admin rights(superadmin or clubadmin or cmsadmin)
     * 
     * @return boolean true if has admin rights
     */
    private function hasAdminRights()
    {
        $adminRights = $this->container->get('contact')->get('mainAdminRightsForFrontend');
        $isCmsAdmin = (in_array('ROLE_CMS_ADMIN', $this->container->get('contact')->get('availableUserRights'))) ? 1 : 0;
        $isClubAdmin = (in_array('ROLE_USERS', $adminRights)) ? 1 : 0;
        $isSuperAdmin = ($this->container->get('contact')->get('isSuperAdmin') || (($this->container->get('contact')->get('isFedAdmin')) && ($this->container->get('club')->get('type') != 'federation'))) ? 1 : 0;

        return (($isCmsAdmin) || ($isClubAdmin) || ($isSuperAdmin)) ? 1 : 0;
    }

    /**
     * This function is used to get the main menu details for building sidebar
     *
     * @return array $mainMenus Main menu details array
     */
    private function getNavigationMainMenus()
    {
        $mainMenus = array();
        $mainMenus['id'] = 'MM';
        $mainMenus['title'] = $this->get('translator')->trans('CMS_NAVIGATION_SIDEBAR_MAIN_MENU');
        $mainMenus['entry'] = $this->getMainMenus();

        return $mainMenus;
    }

    /**
     * This function is used to get the details of all menus under MAIN MENU
     *
     * @return array $mainMenus Menu details array
     */
    private function getMainMenus($isAdditional = 0)
    {
        return $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsNavigation')->
                getSidebarNavigationMainMenus($this->container, true, $isAdditional);
    }

    /**
     * This function is used to get the data to update the sidebar count and
     * icons after different actions
     *
     * @return object JSON Response Object
     */
    public function getDataToUpdateSidebarAction()
    {
        $clubId = $this->container->get('club')->get('id');
        $pagesCount = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->getCountOfPagesForSidebar($clubId);
        $menuDetails = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsNavigation')->getMenuAssignmentDetails($clubId, $this->container->get('club')->get('default_lang'));
        $countData = array('all_pages' => $pagesCount['allPages'], 'pages_without_navigation' => $pagesCount['unassignedPages']);
        $result = array_merge($countData, $menuDetails);

        return new JsonResponse($result);
    }

    /**
     * Function to show edit pagetitle popup
     *
     * @param Request $request Request object
     *
     * @return object View Template Render Object
     */
    public function editPageTitlePopupAction(Request $request)
    {
        $pageId = $request->get('pageId');
        $club = $this->container->get('club');
        $clubLang = $club->get('club_languages');
        $clubDefaultLang = $club->get('club_default_lang');
        $pageTitlesArray = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->getPageTite($pageId);
        $return = array("clubLanguages" => $clubLang, 'clubDefaultLang' => $clubDefaultLang, 'pageTitlesArray' => $pageTitlesArray, 'pageId' => $pageId);

        return $this->render('WebsiteCMSBundle:Page:editPageTitlePopup.html.twig', $return);
    }

    /**
     * Function to save page title
     *
     * @param Request $request Request object
     *
     * @return object JSON Response Object
     */
    public function savePageTitleAction(Request $request)
    {
        $pageId = $request->get('pageId');
        $data['title'] = $request->get('titleArray');
        $club = $this->container->get('club');
        $defaultLang = $club->get('club_default_lang');

        $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->savePageDetails($pageId, $this->container, $data);
        //Save content json
        $pageContentObj = new FgPageContent($this->container);
        $pageContentObj->saveJsonContent($pageId);
        //Save content update time
        $session = $this->container->get('session');
        $pageObject = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->find($pageId);
        $session->set("lastCmsPageEditTime_" + $pageId, $pageObject->getContentUpdateTime()->format('Y-m-d H:i:s'));
        $flash = $this->get('translator')->trans('CMS_PAGE_TITLE_SAVE_SUCCESS');
        $newTitle = $data['title'][$defaultLang];
        $return = array('status' => 'SUCCESS', 'flash' => $flash, 'noparentload' => true, 'pageTitle' => $newTitle);

        return new JsonResponse($return);
    }

    /**
     * To retrieve the sidebar content details using page id
     *
     * @param int $pageId page id
     * @param boolean $fromList check refere from list
     *
     * @return object View Template Render Object
     */
    public function editSidecolumnAction($pageId, $fromList)
    {
        $em = $this->getDoctrine()->getManager();
        //club service
        $clubService = $this->container->get('club');
        //retrieve default language
        $clubDefaultLang = $clubService->get('club_default_lang');
        $contactLang = $clubService->get('default_lang');
        //global sidebar settings
        $globalSidebar = $em->getRepository('CommonUtilityBundle:FgCmsPage')->getGlobalSidebarSetting($clubService->get('id'));
        //retrive content details of a page
        $pageContentObj = new FgPageContent($this->container);
        $pageContentDetails = $pageContentObj->getContentElementData($pageId);
        $pageContentDetails['sidebar']['container'] = $pageContentDetails['page']['container'];
        $pageContentDetails['sidebar']['id'] = $pageContentDetails['page']['id'];
        $pageContentDetails['sidebar']['title'] = $pageContentDetails['page']['title'];

        $session = $this->container->get('session');
        $pageObject = $em->getRepository('CommonUtilityBundle:FgCmsPage')->find($pageId);
        $session->set("lastCmsPageEditTime_" + $pageId, $pageObject->getContentUpdateTime()->format('Y-m-d H:i:s'));

        if ($pageId == $globalSidebar[1]['id']) {
            $pageContentDetails['sidebar']['sidebar'] = array('side' => 'left', 'type' => 'normal', 'size' => 'small');
            $pageContentDetails['sidebar'] = array('side' => 'left', 'type' => 'normal', 'size' => 'small');
            $isActveTab = 1;
        } else {
            $pageContentDetails['sidebar']['sidebar'] = array('side' => 'left', 'type' => 'wide', 'size' => 'wide');
            $pageContentDetails['sidebar'] = array('side' => 'left', 'type' => 'wide', 'size' => 'wide');
            $isActveTab = 0;
        }
        if (count($pageContentDetails) == 0 || ($pageContentDetails['page']['pageType'] != 'sidebar')) {
            throw $this->createNotFoundException($this->clubTitle . ' have no access to this page');
        }
        $breadCrumb = array('back' => $this->generateUrl('website_cms_listpages'));
        $tabs['preview'] = array('text' => $this->get('translator')->trans('CMS_SIDEBAR_WIDE'), 'url' => $this->generateUrl('website_cms_content_sidebar_edit', array('pageId' => $globalSidebar[0]['id'])), 'name' => '', 'type' => 'wide', 'activeClass' => 'active', 'hrefLink' => true);
        $tabs['content'] = array('text' => $this->get('translator')->trans('CMS_SIDEBAR_SMALL'), 'url' => $this->generateUrl('website_cms_content_sidebar_edit', array('pageId' => $globalSidebar[1]['id'])), 'name' => '', 'type' => 'small', 'activeClass' => '', 'hrefLink' => true);

        //collect header details
        $headerDetails = $em->getRepository('CommonUtilityBundle:FgCmsPageContentType')->getHeaderDetails($clubService->get('type'));
        //collect club languages
        $clubLanguages = $clubService->get('club_languages');
        //retrive clip board elements of a club
        $clipboardContentDetails = $em->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->getClipboardDetails($clubService->get('id'), $clubDefaultLang);
        //calendar event count for deavtivating the drag if it is zero
        $calendarCount = $em->getRepository('CommonUtilityBundle:FgEmCalendar')->checkCalendarVisibility($this->container);
        //article count for deavtivating the drag if it is zero
        $articleCount = $em->getRepository('CommonUtilityBundle:FgCmsArticle')->getArticleCountOfClubForCms($this->container);
        //contact application count for deavtivating the drag if it is zero
        $contactAppCount = $this->getDoctrine()->getRepository('CommonUtilityBundle:FgCmsForms')->activeContactFormAppList($clubService->get('id'), true);
        if (in_array('sponsor', $clubService->get('bookedModulesDet'))) {
            $objSponsorPdo = new SponsorPdo($this->container);
            $services = $objSponsorPdo->getSponsorsServices($clubService->get('id'), $clubService->get('club_default_lang'));
            $sponsorActive = count($services) > 0 ? 1 : 0;
            if ($sponsorActive < 1) {
                $toolTipMessage = $this->get('translator')->trans('CREATE_SERVICE_MESSAGE', array(), 'tooltip');
            }
        } else {
            $sponsorActive = 0;
            $toolTipMessage = $this->get('translator')->trans('BOOK_SPONSOR_MESSAGE', array(), 'tooltip');
        }
        $commModuleAvailable = (in_array('communication', $clubService->get('bookedModulesDet'))) ? 1 : 0;
        $hasSidebar = $this->sidebarAccess();
        //To get the map element count in a page
        $mapElementCount = $em->getRepository('CommonUtilityBundle:FgCmsPage')->getMapElementCountInPage($pageId);
        $contactTableElementCount = $em->getRepository('CommonUtilityBundle:FgCmsPage')->getContactTableElementCountInPage($pageId);
        $twitterElementCount = $em->getRepository('CommonUtilityBundle:FgCmsPage')->getTwitterElementCountInPage($pageId);
        $referer = $session->get('lastVisitedCmsPage');
        $pageContentDetails['googleCaptchaSitekey'] = $this->container->getParameter('googleCaptchaSitekey');
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $referer = ($fromList) ? $request->headers->get('referer') : $session->get('lastVisitedCmsPage');
        $contactService = $this->container->get('contact');
        $hasContactRights = ((in_array('ROLE_CONTACT', $contactService->get('availableUserRights'))) && in_array('contact', $clubService->get('bookedModulesDet'))) ? 1 : 0;
        $hasAdminRights = $this->hasAdminRights();
        $pageElementArray = json_decode($pageObject->getPageElement(),true);
        $portraitElementSettings = $this->getPortraitElementSettings($pageElementArray);

        return $this->render('WebsiteCMSBundle:Page:editPageContent.html.twig', array('hasContactRights' => $hasContactRights, 'hasAdminRights' => $hasAdminRights, 'contactAppCount' => $contactAppCount['count'], 'initialForm' => $contactAppCount['id'], 'pageTitleStatus' => -1, 'pagecontentData' => $pageContentDetails, 'tabs' => $tabs, 'cmsClubLanguages' => $clubLanguages, 'clubDefaultLang' => $clubDefaultLang, 'contactLang' => $contactLang, 'clipboardDetails' => $clipboardContentDetails, 'headerDetails' => $headerDetails, 'breadCrumb' => $breadCrumb, 'isActveTab' => $isActveTab, 'mapElementCount' => $mapElementCount, 'twitterElementCount' => $twitterElementCount, 'contactTableElementCount' => $contactTableElementCount, 'hasSidebar' => $hasSidebar, 'referer' => $referer, 'calendarCount' => $calendarCount, 'toolTipMessage' => $toolTipMessage, 'commModuleAvailable' => $commModuleAvailable, 'sponsorActive' => $sponsorActive, 'articleCount' => $articleCount, 'mainPageId' => $pageId,'portraitElementSettings' => $portraitElementSettings,'portUploadPath' => $this->getPortraitUploadPath()));
    }

    /**
     * To retrieve the footer content details from pageID
     *
     * @param integer $pageId page id
     *
     * @return object View Template Render Object
     */
    public function editFooterContentAction()
    {
        $em = $this->getDoctrine()->getManager();
        //club service
        $clubService = $this->container->get('club');
        //retrieve default language
        $clubDefaultLang = $clubService->get('club_default_lang');
        $contactLang = $clubService->get('default_lang');
        $footerObj = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->findBy(array('type' => 'footer', 'club' => $clubService->get('id')));
        if (!$footerObj) {
            $pageContainerObj = new FgPageContainerDetails($this->container);
            $pageContainerObj->setNewPage('footer', 'footer');
        }
        $footerId = $em->getRepository('CommonUtilityBundle:FgCmsPage')->getGlobalFooterId($clubService->get('id'));
        //retrive content details of a page
        $pageContentObj = new FgPageContent($this->container);
        $pageContentDetails = $pageContentObj->getContentElementData($footerId);
        if (count($pageContentDetails) == 0) {
            throw $this->createNotFoundException($this->clubTitle . ' have no access to this page');
        }
        if ($pageContentDetails['page']['pageType'] == 'sidebar') {
            $url = $this->generateUrl('website_cms_content_sidebar_edit', array('pageId' => $footerId));
            return new RedirectResponse($url);
        }
        $breadCrumb = array();
        switch ($pageContentDetails['page']['pageType']) {
            case 'footer':
                $tabs['preview'] = array('text' => $this->get('translator')->trans('CMS_PREVIEW'), 'url' => $this->getPreviewUrl($footerId, 'footer'), 'name' => '', 'type' => 'preview', 'activeClass' => '', 'hrefLink' => true);
                $tabs['content'] = array('text' => $this->get('translator')->trans('CMS_CONTENT'), 'url' => $this->generateUrl('website_cms_page_edit', array('pageId' => $footerId)), 'name' => '', 'type' => 'content', 'activeClass' => 'active', 'hrefLink' => true);
                break;
            default:
                break;
        }

        $session = $this->container->get('session');
        $pageObject = $em->getRepository('CommonUtilityBundle:FgCmsPage')->find($footerId);
        $session->set("lastCmsPageEditTime_" + $footerId, $pageObject->getContentUpdateTime()->format('Y-m-d H:i:s'));

        $hasSidebar = $this->sidebarAccess();
        //collect header details
        $headerDetails = $em->getRepository('CommonUtilityBundle:FgCmsPageContentType')->getHeaderDetails($clubService->get('type'));
        //collect club languages
        $clubLanguages = $clubService->get('club_languages');
        //calendar event count for deavtivating the drag if it is zero
        $calendarCount = $em->getRepository('CommonUtilityBundle:FgEmCalendar')->checkCalendarVisibility($this->container);
        //article count for deavtivating the drag if it is zero
        $articleCount = $em->getRepository('CommonUtilityBundle:FgCmsArticle')->getArticleCountOfClubForCms($this->container);
        //contact application count for deavtivating the drag if it is zero
        $contactAppCount = $this->getDoctrine()->getRepository('CommonUtilityBundle:FgCmsForms')->activeContactFormAppList($clubService->get('id'), true);
        if (in_array('sponsor', $clubService->get('bookedModulesDet'))) {
            $objSponsorPdo = new SponsorPdo($this->container);
            $services = $objSponsorPdo->getSponsorsServices($clubService->get('id'), $clubService->get('club_default_lang'));
            $sponsorActive = count($services) > 0 ? 1 : 0;
            if ($sponsorActive < 1) {
                $toolTipMessage = $this->get('translator')->trans('CREATE_SERVICE_MESSAGE', array(), 'tooltip');
            }
        } else {
            $sponsorActive = 0;
            $toolTipMessage = $this->get('translator')->trans('BOOK_SPONSOR_MESSAGE', array(), 'tooltip');
        }
        //retrive clip board elements of a club
        $clipboardContentDetails = $em->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->getClipboardDetails($clubService->get('id'), $clubDefaultLang);
        //To get the map element count in a page
        $mapElementCount = $em->getRepository('CommonUtilityBundle:FgCmsPage')->getMapElementCountInPage($footerId);
        $contactTableElementCount = $em->getRepository('CommonUtilityBundle:FgCmsPage')->getContactTableElementCountInPage($footerId);
        $twitterElementCount = $em->getRepository('CommonUtilityBundle:FgCmsPage')->getTwitterElementCountInPage($footerId);
        $pageContentDetails['googleCaptchaSitekey'] = $this->container->getParameter('googleCaptchaSitekey');
        $commModuleAvailable = (in_array('communication', $clubService->get('bookedModulesDet'))) ? 1 : 0;
        $contactService = $this->container->get('contact');
        $hasContactRights = ((in_array('ROLE_CONTACT', $contactService->get('availableUserRights'))) && in_array('contact', $clubService->get('bookedModulesDet'))) ? 1 : 0;
        $hasAdminRights = $this->hasAdminRights();
        $pageElementArray = json_decode($pageObject->getPageElement(),true);
        $portraitElementSettings = $this->getPortraitElementSettings($pageElementArray);

        return $this->render('WebsiteCMSBundle:Page:editPageContent.html.twig', array('hasContactRights' => $hasContactRights, 'hasAdminRights' => $hasAdminRights, 'contactAppCount' => $contactAppCount['count'], 'initialForm' => $contactAppCount['id'], 'pageTitleStatus' => -1, 'pagecontentData' => $pageContentDetails, 'tabs' => $tabs, 'cmsClubLanguages' => $clubLanguages, 'clubDefaultLang' => $clubDefaultLang, 'contactLang' => $contactLang, 'clipboardDetails' => $clipboardContentDetails, 'headerDetails' => $headerDetails, 'breadCrumb' => $breadCrumb, 'globalSidebar' => array(), 'isActveTab' => 'content', 'isFooter' => '1', 'mapElementCount' => $mapElementCount, 'twitterElementCount' => $twitterElementCount, 'contactTableElementCount' => $contactTableElementCount, 'hasSidebar' => $hasSidebar, 'calendarCount' => $calendarCount, 'sponsorActive' => $sponsorActive, 'toolTipMessage' => $toolTipMessage, 'commModuleAvailable' => $commModuleAvailable, 'articleCount' => $articleCount, 'mainPageId' => $footerId,'portraitElementSettings' =>$portraitElementSettings,'portUploadPath' => $this->getPortraitUploadPath()));
    }

    /**
     * To check the current user has sidebar access
     *
     * @return int Retrun 0 or 1
     */
    private function sidebarAccess()
    {
        //Action menu settings
        $contact = $this->container->get('contact');
        $club = $this->container->get('club');
        $availableUserRights = $contact->get('availableUserRights');
        $conn = $this->container->get('database_connection');
        $isAdmin = (count(array_intersect(array('ROLE_CMS_ADMIN'), $availableUserRights)) > 0) ? 1 : 0;
        $pageAdmins = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:SfGuardUserPage')->getPageAdminNames($conn, $club->get('id'));
        $pageAdmins = json_encode(array_column($pageAdmins, 'contactname', 'contact'));
        $isPageAdmin = (count(array_intersect(array('ROLE_PAGE_ADMIN'), $availableUserRights)) > 0) ? 1 : 0;

        return ($isPageAdmin && !$isAdmin) ? 0 : 1;
    }

    /**
     * page preview content
     *
     * @return object View Template Render Object
     */
    public function getPagePreviewContentAction($pageId)
    {
        $clubService = $this->container->get('club');
        $breadCrumb = array('back' => $this->generateUrl('website_cms_listpages'));
        $pageContentObj = new FgPageContent($this->container);
        $pageContentDetails = $pageContentObj->getContentElementData($pageId);
        $url = $this->generateUrl('website_cms_listpages');
        $tabs['preview'] = array('text' => $this->get('translator')->trans('CMS_PREVIEW'), 'url' => $this->getPreviewUrl($pageId), 'name' => '', 'type' => 'preview', 'activeClass' => 'active', 'hrefLink' => true);
        $tabs['content'] = array('text' => $this->get('translator')->trans('CMS_CONTENT'), 'url' => $this->generateUrl('website_cms_page_edit', array('pageId' => $pageId)), 'name' => '', 'type' => 'content', 'activeClass' => '', 'hrefLink' => true);
        $checkPageAssignedRes = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsNavigation')->checkPageAssignedToNavigation($pageId);
        $navUrl = (count($checkPageAssignedRes) > 0) ? 1 : 0;

        return $this->render('WebsiteCMSBundle:Page:preview.html.twig', array('pagecontentData' => $pageContentDetails, 'tabs' => $tabs, 'cmsClubLanguages' => $clubService->get('club_languages'), 'clubDefaultLang' => $clubService->get('club_default_lang'), 'pageId' => $pageId, 'breadCrumb' => $breadCrumb, 'referer' => $url, 'navUrl' => $navUrl));
    }

    /**
     * To save json Content of a Page
     *
     * @return object JSON Response Object
     */
    public function saveJsonContentAction()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $pageId = (int) $request->get('pageId');
        //retrive content details of a page
        $pageContentObj = new FgPageContent($this->container);
        $pageContentObj->saveJsonContent($pageId);
        $returnDetails['status'] = 'success';
        $returnDetails['noparentload'] = 'true';
        return new JsonResponse($returnDetails);
    }

    /**
     * To get the url for preview tab
     * 
     * @param int    $pageId   Page id
     * @param string $pageType Page type 'page' or 'footer'
     * 
     * @return string $url Preview url
     */
    private function getPreviewUrl($pageId, $pageType = 'page')
    {
        $contact = $this->container->get('contact');
        $availableUserRights = $contact->get('availableUserRights');
        $checkPageAssignedRes = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsNavigation')->checkPageAssignedToNavigation($pageId);
        $url = ($pageType == 'footer') ? $this->generateUrl('website_cms_footer_preview', array('pageId' => $pageId)) : $this->generateUrl('website_cms_editpage_preview', array('pageId' => $pageId));
        $isPageAdmin = (count(array_intersect(array('ROLE_PAGE_ADMIN'), $availableUserRights)) > 0) ? 1 : 0;
        $isAdmin = (count(array_intersect(array('ROLE_CMS_ADMIN'), $availableUserRights)) > 0) ? 1 : 0;
        $hasSidebar = ($isPageAdmin && !$isAdmin) ? 0 : 1;
        if ((count($checkPageAssignedRes)) && $hasSidebar == 1) {
            $url = $this->generateUrl('website_cms_listpages');
        }
        return $url;
    }

    /**
     * update page title display flag
     *
     * @param Request $request
     *
     * @return json array
     */
    public function updateDisplayPageTitleAction(Request $request)
    {
        $pageId = $request->get('pageId');
        $status = $request->get('status');
        $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->hideShowPageTitle($pageId, $status);

        return new JsonResponse(array('status' => 'success', 'noparentload' => true));
    }

    /**
     * get form element 
     *
     * @param Request $request
     *
     * @return json array
     */
    public function getFormElementAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $elementId = $request->get('elementId');
        $fromedit = $request->get('fromedit', 2);
        $elementObj = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->find($elementId);
        $club = $this->container->get('club');

        if ($elementObj != '') {
            $formObj = $elementObj->getForm();
            if ($formObj->getIsActive() != '1' || $formObj->getIsDeleted() == '1') {
                return new JsonResponse(0);
            }
            $formId = $formObj->getId();
            $formElementObj = new FgFormElement($this->container);
            $result['formData'] = $formElementObj->getFormElementDataForView($formId, $fromedit);
            $result['formOption'] = $em->getRepository('CommonUtilityBundle:FgCmsForms')->getFormOptions($formId, $this->container, $club);
            $result['defLang'] = (!empty($this->container->get('contact')->get('corrLang'))) ? $this->container->get('contact')->get('corrLang') : $this->container->get('club')->get('club_default_lang');
            $result['elementType'] = $elementObj->getPageContentType()->getType();
            $result['elementId'] = $elementId;
            $elementObj = $em->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->find($elementId);
            $result['formStage'] = $formObj->getFormStage();
            
            $objMembershipPdo = new membershipPdo($this->container);
            $clubMemberships = $objMembershipPdo->getMemberships($club->get('type'), $club->get('id'));
            $result['contactFormOptions'] = array(
                'allClubMemberships' => $this->formatMembership($clubMemberships, $result['defLang']),
                'countryFields' => $this->container->getParameter('country_fields'),
                'systemFieldGender' => $this->container->getParameter('system_field_gender'),
                'systemFieldCorrLang' => $this->container->getParameter('system_field_corress_lang'),
                'systemFieldSalutation' => $this->container->getParameter('system_field_salutaion'),
                'countryList' => FgUtility::getCountryListchanged(),
                'clubLanguages' => $this->getLanguageArray(),
                'formId' => $formId
            );
        }

        return new JsonResponse($result);
    }

    /**
     * This function is used to get the additional menu details for building sidebar
     *
     * @return array $mainMenus additional menu details array
     */
    private function getNavigationAdditionalMenus()
    {
        $mainMenus = array();
        $mainMenus['id'] = 'ADDITIONAL';
        $mainMenus['title'] = $this->get('translator')->trans('CMS_NAVIGATION_SIDEBAR_ADDITIONAL_MENU');
        $mainMenus['entry'] = $this->getMainMenus(1);

        return $mainMenus;
    }

    /**
     * This function is used to format $memberships array
     * 
     * @param array $memberships club memebership array
     * @param string $lang language
     * 
     * @return array $result formatted array output
     */
    private function formatMembership($memberships, $lang)
    {
        $result = array();
        foreach ($memberships as $key => $value) {
            $result[] = array(
                'id' => $key,
                'titleLang' => ($value['allLanguages'][$lang] != '') ? $value['allLanguages'][$lang]['titleLang'] : $value['titleLang']
            );
        }
        return $result;
    }

    /**
     * This function is used to get club language array.
     *
     * @return array $fieldLanguages
     */
    private function getLanguageArray()
    {
        $languages = Intl::getLanguageBundle()->getLanguageNames();
        $fieldLanguages = array();
        $clubLanguages = $this->container->get('club')->get('club_languages');
        foreach ($clubLanguages as $shortName) {
            $fieldLanguages[$shortName] = $languages[$shortName];
        }

        return $fieldLanguages;
    }
    /**
     * This function is used to get portarit element container settings data and template array.
     * @param array $pageElementArray page element list
     * 
     * @return array portrait element template
     */
    private function getPortraitElementSettings($pageElementArray)
    {
        $portraitElemTemplate = '';

        foreach ($pageElementArray as $elementId => $value) {
            if ($value == 'portrait-element') {
                $portraitDetailsObj = new FgCmsPortraitFrontend($this->container);
                $portraitElemDetails = $portraitDetailsObj->getPortraitElementDetails($elementId);
                if ($portraitElemDetails['stage'] == 'stage4') {
                    $portraitElemTemplate[$elementId]['template'] = $this->renderView('WebsiteCMSBundle:ContactPortraitsElement:templatePortraitElement.html.twig', $portraitElemDetails);
                    $portraitElemTemplate[$elementId]['data'] = $portraitElemDetails;
                }
            }
        }

        return $portraitElemTemplate;
    }
    /**
     * This function is used to get portarit element file upload path.
     * 
     * @return array file path array
     */
    private function getPortraitUploadPath()
    {
        $uploadPath['fileuploadPath'] = FgUtility::getUploadFilePath('**clubId**', 'contactfield_file');
        $uploadPath['imageuploadPath'] = FgUtility::getUploadFilePath('**clubId**', 'contactfield_image');
        $uploadPath['profilePic'] = FgUtility::getUploadFilePath('**clubId**', 'profilepic');
        $uploadPath['companyLogo'] = FgUtility::getUploadFilePath('**clubId**', 'companylogo');
        $uploadPath['placeholderImage'] = FgUtility::getUploadFilePath('**clubId**', 'cms_portrait_placeholder');

        return $uploadPath;
    }
    
    /**
     * To save json Content of all Pages in the system
     *
     * @return object JSON Response Object
     */
    public function saveJsonContentTempAction()
    {
        set_time_limit(0);
        $conn = $this->container->get('database_connection');
        $container = $this->container;
        $pagesArray = $conn->fetchAll("SELECT id,club_id FROM `fg_cms_page` ORDER BY id DESC");
        //$pagesArray = $conn->fetchAll("SELECT id,club_id FROM `fg_cms_page`  WHERE id < 1000 AND id > 0 ORDER BY id DESC");
        //$pagesArray = $conn->fetchAll("SELECT id,club_id FROM `fg_cms_page`  WHERE id=3487 ORDER BY id DESC");
        echo time().PHP_EOL;
        foreach($pagesArray as $page){
            $club = new FgRoutingListener($this->container, null, $page['club_id'], true);
            $container->set('club', $club);
                
            try{
                $pageContentObj = new FgPageContent($this->container);
                $pageContentObj->saveJsonContent($page['id']);
                file_put_contents('successmigration.txt', "Page migration success for page ".$page['id'].PHP_EOL, FILE_APPEND);
            } catch (Exception $e){
                file_put_contents('failedmigration.txt', "Page migration failed for page ".$page['id'].PHP_EOL, FILE_APPEND);
            }
            usleep(10);
        }
        echo time();
        return $this->render('WebsiteCMSBundle:Dashboard:index.html.twig');
    }
}
