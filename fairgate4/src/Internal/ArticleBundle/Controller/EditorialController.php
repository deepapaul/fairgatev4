<?php

/**
 * Article Editorial Controller
 */
namespace Internal\ArticleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Internal\ArticleBundle\Util\EditorialSidebar;
use Common\UtilityBundle\Util\FgPermissions;
use Internal\ArticleBundle\Util\ArticlesList;
use Common\UtilityBundle\Util\FgSettings;
use Common\FilemanagerBundle\Util\FgFileManager;
use Symfony\Component\HttpFoundation\Request;

/**
 * Article Editorial Controller
 *
 * This controller is used for handling the editorial section
 *
 * @package    InternalArticleBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 *
 */
class EditorialController extends Controller
{

    /**
     * Method to create/edit article.
     *
     * @param object     $request   \Symfony\Component\HttpFoundation\Request
     * @param int|string $articleId articleId
     *
     * @return object View Template Render Object
     */
    public function indexAction(Request $request, $articleId = '')
    {
        $club = $this->container->get('club');
        $returnArray['editorialMode'] = $request->get('editorialMode', '');
        $returnArray['clubLanguageArr'] = $club->get('club_languages');
        $returnArray['defaultClubLang'] = $club->get('club_default_lang');
        $returnArray['breadCrumb'] = array('back' => $this->generateUrl('internal_article_editorial_list'));
        $returnArray['clubLanguages'] = json_encode($club->get('club_languages'));
        $returnArray['mode'] = ($articleId == '') ? 'create' : 'edit';
        $returnArray['articleId'] = ($articleId == '') ? '' : $articleId;
        $returnArray['clubId'] = $club->get('id');
        $returnArray['authorName'] = $this->container->get('contact')->get('nameNoSort');
        $contact = $this->container->get('contact');
        $returnArray['isCluborSuperAdmin'] = (count($contact->get('mainAdminRightsForFrontend')) > 0) ? 1 : 0;
        $returnArray['isClubArticleAdmin'] = in_array('ROLE_ARTICLE', $contact->get('allRights')) ? 1 : 0;
        $availableUserRights = $this->container->get('contact')->get('availableUserRights');
        //team/workgroup admin | team/workgroup article admin
        $returnArray['isGroupAdmin'] = (count(array_intersect(array('ROLE_GROUP_ADMIN', 'ROLE_ARTICLE_ADMIN'), $availableUserRights)) > 0) ? 1 : 0;
        $permissionObj = new FgPermissions($this->container);
        //permission check
        $hasRight = $this->container->get('article.create')->checkRightsForEdit($articleId, $returnArray['isCluborSuperAdmin'], $returnArray['isClubArticleAdmin'], $returnArray['isGroupAdmin']);
        $permissionObj->checkUserAccess('articleEditorialUserrights', $hasRight);

        return $this->render('InternalArticleBundle:Editorial:index.html.twig', $returnArray);
    }

    /**
     * Method to create/edit article data.
     *
     * @param int|string $articleId articleId
     * @param string     $fromPage  create/edit/detail from page
     *
     * @return object JSON Response Object
     */
    public function saveAction($articleId = '', $fromPage = 'create')
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $dataArray = json_decode($request->get('saveData'), true);
        $saveArticleObj = $this->container->get('article.create');
        $saveArticleObj->setDataArray($dataArray);
        $validationErrors = $saveArticleObj->validateArticleData();
        if (count($validationErrors) == 0) {
            $saveArticleObj->beginLog($articleId);
            $responseArray['articleId'] = $saveArticleObj->saveArticle($articleId);
            $saveArticleObj->saveLog($responseArray['articleId']);
            $responseArray['status'] = true;
            if ($fromPage != 'detail') {
                $responseArray['redirect'] = $this->generateUrl('internal_article_editorial_list');
            } else {
                $responseArray['noparentload'] = 1;
            }
            $responseArray['flash'] = $this->get('translator')->trans('ARTICLE_SAVED');
            $responseArray['sync'] = 1;
        } else {
            $responseArray['status'] = false;
            $responseArray['message'] = $validationErrors;
        }

        return new JsonResponse($responseArray);
    }

    /**
     * Function to show article template.
     *
     * @return object View Template Render Object
     */
    public function articleListAction()
    {
        //user rights checking
        $availableUserRights = $this->container->get('contact')->get('availableUserRights');
        $adminRights = $this->container->get('contact')->get('mainAdminRightsForFrontend');
        $isClubAdmin = (in_array('ROLE_USERS', $adminRights)) ? 1 : 0;
        $isArticleAdmin = (in_array('ROLE_ARTICLE', $availableUserRights)) ? 1 : 0;
        $isSuperAdmin = ($this->container->get('contact')->get('isSuperAdmin') || (($this->container->get('contact')->get('isFedAdmin')) && ($this->container->get('club')->get('type') != 'federation'))) ? 1 : 0;
        $isGroupAdmin = (count(array_intersect(array('ROLE_GROUP_ADMIN', 'ROLE_ARTICLE_ADMIN'), $availableUserRights)) > 0) ? 1 : 0;
        if (($isArticleAdmin) || ($isClubAdmin) || ($isSuperAdmin) || ($isGroupAdmin)) {
            $breadCrumb = array('breadcrumb_data' => array());
            $articleSidebarObj = new EditorialSidebar($this->container);
            $sidebarData = $articleSidebarObj->getSidebarData();
            $sidebarDataJson = $sidebarData;
            $contactId = $this->get('contact')->get('id');
            $contactName = $this->get('contact')->get('nameNoSort');
            $club = $this->get('club');
            $clubId = $club->get('id');
            $clubDefaultLang = $club->get('default_lang');
            $defaultSettings = $this->container->getParameter('default_internal_article_table_settings');
            $em = $this->getDoctrine()->getManager();
            //global club comment settings
            $getGlobalClubSettings = $em->getRepository('CommonUtilityBundle:FgCmsArticleClubsetting')->getClubSettings($clubId);
            $isCommentActive = ($getGlobalClubSettings['commentActive'] == 1) ? 1 : 0;

            $contact = $this->container->get('contact');
            $isCluborSuperAdmin = (count($contact->get('mainAdminRightsForFrontend')) > 0) ? 1 : 0;
            $isClubArticleAdmin = in_array('ROLE_ARTICLE', $contact->get('allRights')) ? 1 : 0;
            $availableUserRights = $contact->get('availableUserRights');
            $hasRights = ($isCluborSuperAdmin == 1 || $isClubArticleAdmin == 1) ? 1 : 0;

            return $this->render('InternalArticleBundle:Editorial:articleList.html.twig', array('breadCrumb' => $breadCrumb, 'clubId' => $clubId, 'contactId' => $contactId, 'clubDefaultLang' => $clubDefaultLang, 'sidebarData' => $sidebarDataJson, 'defaultColumnSetting' => $defaultSettings, 'contactName' => $contactName, 'commentSettings' => $isCommentActive, 'hasRights' => $hasRights));
        } else {
            $permissionObj = new FgPermissions($this->container);
            return $permissionObj->checkUserAccess('', 'no_access');
        }
    }

    /**
     * Function to show article listing.
     *
     * @return object JSON Response Object
     */
    public function getArticleListAction()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $listingType = $request->get('listingType', '');
        $tableSettingFields = $request->get('columns', '');
        if ($tableSettingFields != '') {
            $tableColumns = json_decode($tableSettingFields, true);
        } else {
            $tableColumns = $this->container->getParameter('default_internal_article_table_settings');
        }
        $filterArray = $this->getFilterDateFromRequest($request);
        $articleListObj = new ArticlesList($this->container, 'editorial');
        $articleListObj->columnData = $tableColumns;
        $articleListObj->filterData = $filterArray;
        $articleListObj->setColumnData();
        $articleListObj->setColumnDataFrom();
        $articleListObj->setGroupBy();
        $articleListObj->addOrderBy();
        $articleListObj->addHaving(array('isEditable = 1'));
        ($listingType == 'ARCHIVE') ? $articleListObj->addHaving(array("STATUS = 'archived'")) : $articleListObj->addHaving(array("STATUS != 'archived'"));

        $articles = $articleListObj->getArticleData();
        $output['aaData'] = $articles;
        $output['aaDataType'] = $this->getColumnDataTypes();
        $output['actionMenu'] = $this->getActionMenu(1, $listingType);

        return new JsonResponse($output);
    }

    /**
     * Function to get column data types to be shown in datatable
     *
     * @return array $aaDataType Column data types array
     */
    private function getColumnDataTypes()
    {
        $aaDataType = array();
        $aaDataType[] = array('title' => 'edit', 'type' => 'edit');
        $aaDataType[] = array('title' => 'title', 'type' => 'title');
        $aaDataType[] = array('title' => 'F_ARCHIVING_DATE', 'type' => 'F_ARCHIVING_DATE');
        $aaDataType[] = array('title' => 'ARCHIVED_BY', 'type' => 'ARCHIVED_BY');
        $aaDataType[] = array('title' => 'PUBLICATION_DATE', 'type' => 'PUBLICATION_DATE');
        $aaDataType[] = array('title' => 'ARCHIVING_DATE', 'type' => 'ARCHIVING_DATE');
        $aaDataType[] = array('title' => 'AREAS', 'type' => 'AREAS');
        $aaDataType[] = array('title' => 'CATEGORIES', 'type' => 'CATEGORIES');
        $aaDataType[] = array('title' => 'CREATED_AT', 'type' => 'CREATED_AT');
        $aaDataType[] = array('title' => 'EDITED_AT', 'type' => 'EDITED_AT');
        $aaDataType[] = array('title' => 'CREATED_BY', 'type' => 'CREATED_BY');
        $aaDataType[] = array('title' => 'EDITED_BY', 'type' => 'EDITED_BY');
        $aaDataType[] = array('title' => 'STATUS', 'type' => 'STATUS');
        $aaDataType[] = array('title' => 'SCOPE', 'type' => 'SCOPE');

        return $aaDataType;
    }

    /**
     * Method to get datas of particular article.
     *
     * @param string $pagetype pagetype either article or editorial
     *
     * @return object JSON Response Object
     */
    public function getDataAction($pagetype)
    {
        $result = array();
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $articleId = $request->get('articleId');
        $articleObj = $this->container->get('article.create');
        $articleDataObj = $this->container->get('article.data');
        if ($pagetype == 'article') {
            $articleDataObj->clubDefaultLanguage = $this->container->get('club')->get('default_lang');
        }
        if ($articleId) {
            $result = $articleDataObj->getArticleDatas($articleId);
            $result['article']['clubTitle'] = ucfirst($this->container->get('club')->get('title'));
        }
        $articleClubSettings = $articleObj->getArticleClubSettings();
        //handling case when no entry in artcle club settings
        $result['commentActive'] = (isset($articleClubSettings['commentActive'])) ? $articleClubSettings['commentActive'] : 1;
        $club = $this->container->get('club');
        $result['clubLanguages'] = json_encode($club->get('club_languages'));
        $defLang = $club->get('club_default_lang');
        $result['defaultClubLang'] = $defLang;
        $areas = $articleObj->getMyClubAndTeamsAndWorkgroups();
        $result['assignedTeams'] = json_encode($areas['teams']);
        $result['assignedWorkgroups'] = json_encode($areas['workgroups']);
        $result['clubTerminology'] = $areas['club'];
        $result['clubType'] = $club->get('type');
        $articleCategories = $articleObj->getAllArticleCategories();
        $result['category'] = json_encode($articleCategories);
        $result['isFrontend2Booked'] = (in_array('frontend2', $club->get('bookedModulesDet'))) ? 1 : 0;
        $result['commentCount'] = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsArticleComments')->getCommentsTotal($articleId);

        return new JsonResponse($result);
    }

    /**
     * common function to get  categories.
     *
     * @return object JSON Response Object
     */
    public function getArticleCategoriesAction()
    {
        $articleCategories = $this->container->get('article.create')->getAllArticleCategories();

        return new JsonResponse($articleCategories);
    }

    /**
     * Method to show popup for create new category.
     *
     * @param Request $request Request object
     *
     * @return object View Template Render Object
     */
    public function categoryCreatePopupAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $catId = $request->get('catId');
        $noParentLoad = $request->get('noParentLoad', false);
        $defaultLang = $request->get('defaultLang');
        $clubId = $this->container->get('club')->get('id');
        $clubLang = $this->container->get('club')->get('club_languages');
        $sortOrder = $em->getRepository('CommonUtilityBundle:FgCmsArticleCategory')->getCategoryCount($clubId);

        $popoverTitle = $this->get('translator')->trans('CREATE_ARTICLE_CATEGORY_POPOVER_TITLE');
        $popoverText = $this->get('translator')->trans('CREATE_CALENDAR_CATEGORY_POPOVER_TEXT');
        $return = array('title' => $popoverTitle, 'text' => $popoverText, 'catId' => $catId, 'defaultLang' => $defaultLang, 'sortOrder' => $sortOrder, 'clubLanguages' => $clubLang, 'noParentLoad' => $noParentLoad);

        return $this->render('InternalArticleBundle:Editorial:ArticleCategorySavePopup.html.twig', $return);
    }

    /**
     * To view article details page
     *
     * @param int|String $articleId article Id
     *
     * @return object View Template Render Object
     */
    public function detailsAction($articleId = '')
    {
        $club = $this->container->get('club');
        $defaultLang = $club->get('club_default_lang');
        $returnArray['clubLanguageArr'] = $club->get('club_languages');
        $returnArray['clubLanguagesJson'] = json_encode($club->get('club_languages'));
        $returnArray['defaultClubLang'] = $defaultLang;
        $clubId = $club->get('id');
        $clubSettings = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsArticleClubsetting')->findOneBy(array('club' => $clubId));
        $globalCommentAccess = empty($clubSettings) ? 0 : $clubSettings->getCommentActive();
        $allowedTabs = $this->getAllowedTabs($globalCommentAccess);
        $returnArray['clubId'] = $this->container->get('club')->get('id');
        $returnArray['contactId'] = $this->container->get('contact')->get('id');
        $returnArray['articleId'] = $articleId;
        $returnArray['roleCount'] = count($allowedTabs);
        $returnArray['breadCrumb'] = array('back' => $this->generateUrl('internal_article_editorial_list'));
        $returnArray['authorName'] = $this->container->get('contact')->get('nameNoSort');
        $contact = $this->container->get('contact');
        $returnArray['isCluborSuperAdmin'] = (count($contact->get('mainAdminRightsForFrontend')) > 0) ? 1 : 0;
        $returnArray['isClubArticleAdmin'] = in_array('ROLE_ARTICLE', $contact->get('allRights')) ? 1 : 0;
        $availableUserRights = $this->container->get('contact')->get('availableUserRights');
        //team/workgroup admin | team/workgroup article admin
        $returnArray['isGroupAdmin'] = (count(array_intersect(array('ROLE_GROUP_ADMIN', 'ROLE_ARTICLE_ADMIN'), $availableUserRights)) > 0) ? 1 : 0;
        //permission check
        $hasRight = $this->container->get('article.create')->checkRightsForEdit($articleId, $returnArray['isCluborSuperAdmin'], $returnArray['isClubArticleAdmin'], $returnArray['isGroupAdmin']);
        $permissionObj = new FgPermissions($this->container);
        $permissionObj->checkUserAccess('articleEditorialUserrights', $hasRight);

        $articleDetails = $this->container->get('article.data')->getArticleDatas($articleId);
        $returnArray['articleStatus'] = $articleDetails['article']['isDraft'];
        $returnArray['articleLevel'] = $articleDetails['article']['level'];
        $returnArray['articleTitle'] = $articleDetails['article']['text'][$defaultLang]['title'];
        $allowedTabs['articleAttachments']['count'] = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsArticleAttachments')->getCountOfAttachments($articleId);
        $allowedTabs['articleMedia']['count'] = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsArticleMedia')->getCountOfMedia($articleId);

        if (!empty($allowedTabs['comments'])) {
            $allowedTabs['comments']['count'] = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsArticleComments')->getCommentsTotal($articleId);
        }
        $returnArray['tabs'] = $allowedTabs;

        return $this->render('InternalArticleBundle:Editorial:details.html.twig', $returnArray);
    }

    /**
     * To view article details page
     *
     * @param int $globalCommentAccess global comment settings
     *
     * @return array|Array
     */
    private function getAllowedTabs($globalCommentAccess)
    {
        $tabs = array();
        $tabs['preview'] = array('text' => $this->get('translator')->trans('ARTICLE_TAB_PREVIEW'), 'url' => '#', 'name' => '', 'type' => 'preview', 'activeClass' => '');
        $tabs['articleText'] = array('text' => $this->get('translator')->trans('ARTICLE_TAB_TEXT'), 'url' => '#', 'name' => '', 'tabtype' => 'text', 'activeClass' => '');
        $tabs['articleMedia'] = array('text' => $this->get('translator')->trans('ARTICLE_SECTION_IMAGES'), 'url' => '#', 'name' => '', 'image_videos' => 'preview', 'count' => 100, 'activeClass' => '');
        $tabs['articleAttachments'] = array('text' => $this->get('translator')->trans('ARTICLE_SECTION_ATTACHMENTS'), 'url' => '#', 'name' => '', 'tabtype' => 'settings', 'activeClass' => '');
        $tabs['articleSettings'] = array('text' => $this->get('translator')->trans('ARTICLE_SECTION_SETTINGS'), 'url' => '#', 'name' => '', 'tabtype' => 'settings', 'activeClass' => '');
        if ($globalCommentAccess) {
            $tabs['comments'] = array('text' => $this->get('translator')->trans('ARTICLE_TAB_COMMENTS'), 'url' => '#', 'name' => '', 'tabtype' => 'comments', 'activeClass' => '');
        }
        $tabs['log'] = array('text' => $this->get('translator')->trans('ARTICLE_TAB_LOG'), 'url' => '#', 'name' => '', 'tabtype' => 'log', 'activeClass' => '');

        return $tabs;
    }

    /**
     * Function for create the action menu.
     *
     * @param int    $adminflag   Admin flag
     * @param string $listingType Type of listing ARCHIVE/ACTIVE
     *
     * @return array action menu array
     */
    private function getActionMenu($adminflag = 0, $listingType = '')
    {
        if ($adminflag == 1) {
            //none selection begins
            if ($listingType == 'ARCHIVE') {
                $noneSelectedText['reactivateArticle'] = array('title' => $this->get('translator')->trans('REACTIVATE_BUTTON_TEXT'), 'hrefLink' => '', 'isActive' => 'false');
            } else {
                $noneSelectedText['create'] = array('isVisibleAlways' => 'true', 'title' => $this->get('translator')->trans('CL_CREATE'), 'hrefLink' => $this->container->get('router')->generate('internal_article_editorial_create'), 'isActive' => 'true');
            }
            $noneSelectedText['editArticle'] = array('title' => $this->get('translator')->trans('CL_EDIT'), 'hrefLink' => '', 'isActive' => 'false');
            $noneSelectedText['duplicateArticle'] = array('title' => $this->get('translator')->trans('CL_DUPLICATE'), 'hrefLink' => '', 'isActive' => 'false');
            $noneSelectedText['assignArticle'] = array('title' => $this->get('translator')->trans('AL_ASSIGN'), 'hrefLink' => $this->generateUrl('calendar_import_events'), 'isActive' => 'false');
            if ($listingType == 'ARCHIVE') {
                $noneSelectedText['deleteArticle'] = array('title' => $this->get('translator')->trans('DELETE_BUTTON_TEXT'), 'hrefLink' => '', 'isActive' => 'false');
            } else {
                $noneSelectedText['archiveArticle'] = array('title' => $this->get('translator')->trans('ARCHIVE_BUTTON'), 'hrefLink' => '', 'isActive' => 'false');
            }
            //none selection ends
            //single selection begins
            if ($listingType == 'ARCHIVE') {
                $singleSelectedText['reactivateArticle'] = array('title' => $this->get('translator')->trans('REACTIVATE_BUTTON_TEXT'), 'dataUrl' => '', 'isActive' => 'true');
            } else {
                $singleSelectedText['create'] = array('title' => $this->get('translator')->trans('CL_CREATE'), 'hrefLink' => $this->container->get('router')->generate('calendar_appointment_create'), 'isActive' => 'false');
            }
            $singleSelectedText['editArticle'] = array('title' => $this->get('translator')->trans('CL_EDIT'), 'dataUrl' => $this->generateUrl('internal_article_editorial_edit', array('articleId' => 'ARTICLEIDREPLACE')), 'isActive' => 'true');
            $singleSelectedText['duplicateArticle'] = array('title' => $this->get('translator')->trans('CL_DUPLICATE'), 'dataUrl' => '', 'isActive' => 'true');
            $singleSelectedText['assignArticle'] = array('title' => $this->get('translator')->trans('AL_ASSIGN'), 'dataUrl' => '', 'isActive' => 'true');
            if ($listingType == 'ARCHIVE') {
                $singleSelectedText['deleteArticle'] = array('title' => $this->get('translator')->trans('DELETE_BUTTON_TEXT'), 'dataUrl' => '', 'isActive' => 'true');
            } else {
                $singleSelectedText['archiveArticle'] = array('title' => $this->get('translator')->trans('ARCHIVE_BUTTON'), 'dataUrl' => '', 'isActive' => 'true');
            }
            //single selection ends
            //multiple selection begins
            if ($listingType == 'ARCHIVE') {
                $multipleSelectedText['reactivateArticle'] = array('title' => $this->get('translator')->trans('REACTIVATE_BUTTON_TEXT'), 'dataUrl' => '', 'isActive' => 'true');
            } else {
                $multipleSelectedText['create'] = array('title' => $this->get('translator')->trans('CL_CREATE'), 'hrefLink' => $this->container->get('router')->generate('calendar_appointment_create'), 'isActive' => 'false');
            }
            $multipleSelectedText['editArticle'] = array('title' => $this->get('translator')->trans('CL_EDIT'), 'dataUrl' => '', 'isActive' => 'false');
            $multipleSelectedText['duplicateArticle'] = array('title' => $this->get('translator')->trans('CL_DUPLICATE'), 'hrefLink' => '', 'isActive' => 'false');
            $multipleSelectedText['assignArticle'] = array('title' => $this->get('translator')->trans('ASSIGN'), 'dataUrl' => '', 'isActive' => 'true');
            if ($listingType == 'ARCHIVE') {
                $multipleSelectedText['deleteArticle'] = array('title' => $this->get('translator')->trans('DELETE_BUTTON_TEXT'), 'dataUrl' => '', 'isActive' => 'true');
            } else {
                $multipleSelectedText['archiveArticle'] = array('title' => $this->get('translator')->trans('ARCHIVE_BUTTON'), 'dataUrl' => '', 'isActive' => 'true');
            }
            //multiple selection ends
        } else {
            $noneSelectedText = array();
            $singleSelectedText = array();
            $multipleSelectedText = array();
        }

        return array('none' => $noneSelectedText, 'single' => $singleSelectedText, 'multiple' => $multipleSelectedText, 'adminFlag' => $adminflag);
    }

    /**
     * Method to show popup for archive article.
     *
     * @param Request $request Request object
     *
     * @return object View Template Render Object
     */
    public function archiveArticlePopupAction(Request $request)
    {
        $articleArray = $request->get('articleArray');
        $articleCount = count($articleArray);
        $popupTitle = ($articleCount > 1) ? str_replace('%count%', $articleCount, $this->get('translator')->trans('ARCHIVE_ARTICLE_POPUP_TITlE_PLURAL')) : $this->get('translator')->trans('ARCHIVE_ARTICLE_POPUP_TITlE_SINGLE');
        $popupText = ($articleCount > 1) ? $this->get('translator')->trans('ARCHIVE_ARTICLE_POPUP_DESCRIPTION_PLURAL') : $this->get('translator')->trans('ARCHIVE_ARTICLE_POPUP_DESCRIPTION_SINGLE');
        $return = array("title" => $popupTitle, 'text' => $popupText, 'articleArray' => $articleArray, 'buttonText' => $this->get('translator')->trans('ARCHIVE_BUTTON_TEXT'), 'type' => 'archive', 'articleCount' => $articleCount);

        return $this->render('InternalArticleBundle:Editorial:ConfirmationPopup.html.twig', $return);
    }

    /**
     * To archive selected article
     *
     * @param Request $request Request object
     *
     * @return object JSON Response Object
     */
    public function archiveAction(Request $request)
    {
        $archiveData = $request->get('articleDetails');
        foreach ($archiveData as $value) {
            $archiveArticles[] = $value['id'];
        }
        $editableArticles = $this->getEditableArticles($archiveArticles, false);
        $articleCount = count($archiveData);
        $archiveCount = count($editableArticles);
        $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsArticle')->archiveArticles($editableArticles, $this->container);
        $timeperiodReturn = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsArticle')->getTimeperiodArticle($this->container->get('club')->get('id'), $this->container->get('club')->get('clubHeirarchy'));
        $return = array('result' => $timeperiodReturn, 'status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('ARTICLE_ARCHIVED_SUCCESS', array('%selcount%' => $archiveCount, '%totalcount%' => $articleCount)), 'noparentload' => true);

        return new JsonResponse($return);
    }

    /**
     * Format filter array for article listing class
     *
     * @param object $request Request object
     *
     * @return array Array for article listing class
     */
    private function getFilterDateFromRequest($request)
    {
        $filterDate = $request->get('filter', array());
        $category = $request->get('menuType', false);
        $selection = $request->get('subCategoryId', false);
        $filterValue = array();
        foreach ($filterDate as $key => $value) {

            if ($key == 'START_DATE' || $key == 'END_DATE') {
                $date = new \DateTime();
                $value = $date->createFromFormat(FgSettings::getPhpDateFormat(), $value)->format('Y-m-d');
                $filterValue[$key] = $value;
            } elseif ($key == 'AREAS') {
                if (in_array('CLUB', $value)) {
                    $filterValue['IS_CLUB'] = 1;
                    unset($value[array_search('CLUB', $value)]);
                }
                if (count($value) > 0) {
                    $filterValue[$key] = implode(',', $value);
                }
            } elseif (is_array($value)) {
                $filValues = implode('","', $value);
                if ($filValues !== 'ALL') {
                    $filterValue[$key] = '"' . $filValues . '"';
                }
            } elseif ($key == 'publishedBy') {
                $filterValue['CREATED_BY'] = $value;
            } else {
                
            }
        }
        if ($selection == 'WA' && ($category == 'CAT' || $category == 'AREAS')) {
            $category = ($category == 'CAT') ? 'CATEGORIES' : 'AREAS';
            $filterValue[$category] = 'NONE';
        }

        return $filterValue;
    }

    /**
     * Function to download article attachments.
     * 
     * @param Request $request Request object
     * 
     * @return Object
     */
    public function downloadArticleAttachmentAction(Request $request)
    {
        $fileManagerId = $request->get('filemanagerId');
        $clubId = $request->get('clubId');
        $fileObj = new FgFileManager($this->container);

        return $fileObj->downloadFileById($fileManagerId, $clubId . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR);
    }

    /**
     * Template for Article action-menu modal popups.
     *
     * @param Request $request Request object
     *
     * @return object View Template Render Object
     */
    public function assignArticlePopupAction(Request $request)
    {
        $checkedIds = $request->get('checkedIds'); //selected ids(comma separeted)
        $selected = $request->get('selected');
        $params = $request->get('params');  //json data of current status like currentScope
        $checkedIdsArray = explode(',', $checkedIds);
        $articleArray = $request->get('articleArray');

        $buttonSave = $this->get('translator')->trans('ASSIGN');
        if (count($checkedIdsArray) > 1) {
            $popupTitle = $this->get('translator')->trans('ARTICLE_ASSIGN_TITLE_MULTIPLE', array('%count%' => count($checkedIdsArray)));
        } else {
            $popupTitle = $this->get('translator')->trans('ARTICLE_ASSIGN_TITLE_SINGLE');
        }

        $areaAndCategory = array();
        $articleObj = $this->container->get('article.create');
        $areas = $articleObj->getMyClubAndTeamsAndWorkgroups();
        $areaAndCategory['assignedTeams'] = $areas['teams'];
        $areaAndCategory['assignedWorkgroups'] = $areas['workgroups'];
        $articleCategories = $articleObj->getAllArticleCategories();
        $areaAndCategory['category'] = $articleCategories;
        $areaAndCategory['clubTerminology'] = ucfirst($this->container->get('club')->get('title'));

        $contact = $this->container->get('contact');
        $isCluborSuperAdmin = (count($contact->get('mainAdminRightsForFrontend')) > 0) ? 1 : 0;
        $isArticleAdmin = in_array('ROLE_ARTICLE', $contact->get('allRights')) ? 1 : 0;
        $isAdmin = ($isCluborSuperAdmin || $isArticleAdmin) ? 1 : 0;

        $return = array('title' => $popupTitle, 'text' => '', 'areaCat' => $areaAndCategory, 'checkedIds' => $checkedIds, 'params' => $params, 'button_val' => $buttonSave, 'selected' => $selected, 'isAdmin' => $isAdmin, 'articleArray' => $articleArray, 'articleCount' => count($articleArray));

        return $this->render('InternalArticleBundle:Editorial:ArticleAssignPopup.html.twig', $return);
    }

    /**
     * Assign Areas and categories
     *
     * @param Request $request Request object
     *
     * @return object JSON Response Object
     */
    public function assignAreaAndCategoryAction(Request $request)
    {
        $checkedIds = $request->get('checkedIds');
        $articleIds = explode(",", $checkedIds);
        $areas = $request->get('areas');
        $categories = $request->get('categories');
        $club = $this->container->get('club');
        $contact = $this->container->get('contact');
        $clubTitle = ucfirst($club->get('title'));
        $allTeams = $contact->get('teams');
        $allWorkgroups = $contact->get('workgroups');
        $roleArray = array('Club' => $clubTitle) + $allTeams + $allWorkgroups;
        // To get selected area and category names for log.
        $selectedRoles = array();
        foreach ($areas as $id) {
            $selectedRoles[] = $roleArray[$id];
        }
        $selectedRoleNames = implode(', ', $selectedRoles);

        $articleObj = $this->container->get('article.create');
        $articleCategories = $articleObj->getAllArticleCategories();
        $selectedCategories = array();
        foreach ($articleCategories as $val) {
            if (in_array($val['id'], $categories)) {
                $selectedCategories[] = $val['title'];
            }
        }
        $selectedCategoryNames = implode(', ', $selectedCategories);
        $successArray = array();
        foreach ($articleIds as $articleId) {

            $existingAreas = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsArticleSelectedareas')->getRolesIdsFromArticle($articleId);
            $diffAreas = array_diff($areas, $existingAreas);

            $existingCategories = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsArticleSelectedcategories')->getCategoryIdsFromArticle($articleId);
            $diffCategories = array_diff($categories, $existingCategories);


            if (count($diffAreas) > 0) {
                $successArray[$articleId] = true;
                $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsArticleSelectedareas')->saveArticleAreas($diffAreas, $articleId, true);
                $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsArticleSelectedareas')->saveArticleAreaLog($articleId, $selectedRoleNames, $this->container);
            }

            if (count($diffCategories) > 0) {
                $successArray[$articleId] = true;
                $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsArticleSelectedcategories')->saveArticleCategories($diffCategories, $articleId, true);
                $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsArticleSelectedcategories')->saveArticleCategoryLog($articleId, $selectedCategoryNames, $this->container);
            }
        }
        $flash = $this->get('translator')->trans('ARTICLE_ASSIGNED_SUCCESS', array('%selcount%' => count($successArray), '%totalcount%' => count($articleIds)));
        $return = array('status' => 'SUCCESS', 'flash' => $flash, 'noparentload' => true);

        return new JsonResponse($return);
    }

    /**
     * Method to show popup for delete article.
     *
     * @param Request $request Request object
     *
     * @return object View Template Render Object
     */
    public function deleteArticlePopupAction(Request $request)
    {
        $articleArray = $request->get('articleArray');
        $articleCount = count($articleArray);
        $popupTitle = ($articleCount > 1) ? str_replace('%count%', $articleCount, $this->get('translator')->trans('DELETE_ARTICLE_POPUP_TITlE_PLURAL')) : $this->get('translator')->trans('DELETE_ARTICLE_POPUP_TITlE_SINGLE');
        $popupText = ($articleCount > 1) ? $this->get('translator')->trans('DELETE_ARTICLE_POPUP_DESCRIPTION_PLURAL') : $this->get('translator')->trans('DELETE_ARTICLE_POPUP_DESCRIPTION_SINGLE');
        $return = array("title" => $popupTitle, 'text' => $popupText, 'articleArray' => $articleArray, 'articleCount' => $articleCount, 'buttonText' => $this->get('translator')->trans('DELETE_BUTTON_TEXT'), 'type' => 'delete');

        return $this->render('InternalArticleBundle:Editorial:ConfirmationPopup.html.twig', $return);
    }

    /**
     * Method to  delete articles.
     *
     * @param Request $request Request object
     *
     * @return object JSON Response Object
     */
    public function deleteAction(Request $request)
    {
        $deleteData = $request->get('articleDetails');
        foreach ($deleteData as $value) {
            $deleteArticles[] = $value['id'];
        }
        $editableArticles = $this->getEditableArticles($deleteArticles, true);
        $articleCount = count($deleteData);
        $deleteCount = count($editableArticles);
        $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsArticle')->deleteArticles($editableArticles, $this->container);
        $return = array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('ARTICLE_DELETED_SUCCESS', array('%selcount%' => $deleteCount, '%totalcount%' => $articleCount)), 'noparentload' => true);

        return new JsonResponse($return);
    }

    /**
     * Method to show popup for reactivate article.
     *
     * @param Request $request Request object
     *
     * @return object View Template Render Object
     */
    public function reactivateArticlePopupAction(Request $request)
    {
        $articleArray = $request->get('articleArray');
        $articleCount = count($articleArray);
        $popupTitle = ($articleCount > 1) ? str_replace('%count%', $articleCount, $this->get('translator')->trans('REACTIVATE_ARTICLE_POPUP_TITlE_PLURAL')) : $this->get('translator')->trans('REACTIVATE_ARTICLE_POPUP_TITlE_SINGLE');
        $popupText = ($articleCount > 1) ? $this->get('translator')->trans('REACTIVATE_ARTICLE_POPUP_DESCRIPTION_PLURAL') : $this->get('translator')->trans('REACTIVATE_ARTICLE_POPUP_DESCRIPTION_SINGLE');
        $return = array("title" => $popupTitle, 'text' => $popupText, 'articleArray' => $articleArray, 'articleCount' => $articleCount, 'buttonText' => $this->get('translator')->trans('REACTIVATE_BUTTON_TEXT'), 'type' => 'reactivate');

        return $this->render('InternalArticleBundle:Editorial:ConfirmationPopup.html.twig', $return);
    }

    /**
     * Method to  delete articles.
     *
     * @param Request $request Request object
     *
     * @return object JSON Response Object
     */
    public function reactivateAction(Request $request)
    {
        $reactivateData = $request->get('articleDetails');
        foreach ($reactivateData as $value) {
            $reactivateArticles[] = $value['id'];
        }
        $editableArticles = $this->getEditableArticles($reactivateArticles, true);
        $articleCount = count($reactivateData);
        $reactivateCount = count($editableArticles);
        $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsArticle')->reactivateArticles($editableArticles, $this->container);
        $timeperiodReturn = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsArticle')->getTimeperiodArticle($this->container->get('club')->get('id'), $this->container->get('club')->get('clubHeirarchy'));
        $return = array('result' => $timeperiodReturn, 'status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('ARTICLE_REACTIVATED_SUCCESS', array('%selcount%' => $reactivateCount, '%totalcount%' => $articleCount)), 'noparentload' => true);

        return new JsonResponse($return);
    }

    /**
     * Function to get article created by contacts for filter
     *
     * @param Request $request Request object
     *
     * @return object JSON Response Object
     */
    public function getCreatedByContactsAction(Request $request)
    {
        $term = $request->get('term');
        $return = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsArticle')->getCreatedByContacts($this->get('club')->get('id'), $term);

        return new JsonResponse($return);
    }

    /**
     * Function to get the sidebar count of editorial article
     *
     * @return object JSON Response Object $sidebarCount Count of sidebar Area, Category, Club etc.
     */
    public function getSidebarCountAction()
    {
        //Get all the sidebar block count and get it as a one array
        $sidebarCount = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsArticle')->getArticleSidebarCount($this->container);

        return new JsonResponse($sidebarCount);
    }

    /**
     * Function to get editable articles
     * 
     * @param array   $articleIds    selected articles
     * @param boolean $allowArchived allow archived articles
     * 
     * @return array $editableArticles
     */
    private function getEditableArticles($articleIds, $allowArchived)
    {
        if (!is_array($articleIds)) {
            $articleIds = explode(",", $articleIds);
        }
        $clubId = $this->get('club')->get('id');
        $articleListObj = new ArticlesList($this->container, 'editorial');
        $editableArray = $articleListObj->getEditableArticleIds($clubId, $allowArchived);

        return array_intersect($articleIds, $editableArray);
    }

    /**
     * Function to update the status of the article
     *
     * @return object JSON Response Object
     */
    public function updateStatusAction()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $articleId = $request->get('articleId');
        $articleStatus = $request->get('status', 0);
        $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsArticle')
            ->updateArticleStatus($articleId, $articleStatus);

        //add log
        $flashMessage = ($articleStatus == 0) ? $this->get('translator')->trans('ARTICLE_ACTIVATED_SUCCESS') : $this->get('translator')->trans('ARTICLE_DEACTIVATED_SUCCESS');
        $articleDetails = $this->container->get('article.data')->getArticleSettings($articleId);
        $articleLevel = $articleDetails['article']['level'];

        return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $flashMessage, 'noparentload' => true, 'articleLevel' => $articleLevel, 'articleStatus' => $articleStatus));
    }

    /**
     * Function to update the sidebar count
     *
     * @return object JSON Response Object
     */
    public function updateSidebarAction()
    {
        $articleSidebarObj = new EditorialSidebar($this->container);
        $sidebarDataJson = $articleSidebarObj->getSidebarData();

        return new JsonResponse($sidebarDataJson);
    }
}
