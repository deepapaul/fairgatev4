<?php

/**
 * Article Controller
 */
namespace Internal\ArticleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Common\UtilityBundle\Util\FgSettings;
use Internal\ArticleBundle\Util\ArticleSidebar;
use Internal\ArticleBundle\Util\ArticlesList;
use Common\UtilityBundle\Util\FgPermissions;
use Common\UtilityBundle\Util\FgUtility;

/**
 * This controller is used for handling the article section
 * 
 * @package    InternalArticleBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
class ArticleController extends Controller
{

    /**
     * Method to view article details.
     *
     * @param int|string $articleId articleId
     *
     * @return object View Template Render Object
     */
    public function indexAction($articleId = '')
    {
        // article Access check
        $articleListObj = new ArticlesList($this->container, 'article', false);
        $accessibleArticles = $articleListObj->getMyVisibleArticleIds();
        $accessibleArticle = explode(',', $accessibleArticles);
        if (!in_array($articleId, $accessibleArticle)) {
            $permissionObj = new FgPermissions($this->container);
            $permissionObj->checkClubAccess(0, 'articleAccess');
        }

        $club = $this->container->get('club');
        $defaultLang = $club->get('default_lang');
        $returnArray['clubLanguageArr'] = $club->get('club_languages');
        $returnArray['defaultClubLang'] = $defaultLang;
        $returnArray['clubId'] = $this->container->get('club')->get('id');
        $returnArray['contactId'] = $this->container->get('contact')->get('id');
        $returnArray['articleId'] = $articleId;
        $returnArray['breadCrumb'] = array('back' => $this->generateUrl('internal_article_list'));
        $returnArray['commentCount'] = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsArticleComments')->getCommentsTotal($articleId);

        $articleDataObj = $this->container->get('article.data');
        $articleDataObj->clubDefaultLanguage = $club->get('default_lang');
        $articleDetails = $articleDataObj->getArticleDatas($articleId);
        $returnArray['articleTitle'] = ($articleDetails['article']['text'][$defaultLang]['title']) ? $articleDetails['article']['text'][$defaultLang]['title'] : $articleDetails['article']['text']['default']['title'];

        return $this->render('InternalArticleBundle:Article:index.html.twig', $returnArray);
    }

    /**
     * Function list article list
     * 
     * @return object View Template Render Object
     */
    public function articleListAction()
    {
        $club = $this->get('club');
        $clubDefaultLang = $club->get('default_lang');
        $defaultSysLang = $club->get('default_system_lang');
        //array of possible user rights
        $userRights = array('ROLE_GROUP', 'ROLE_GROUP_ADMIN', 'ROLE_USERS');
        //get all user rights of current user
        $allRights = $this->container->get('contact')->get('availableUserRights');
        $roleAdminFlag = 0;

        //check if the user has team admin rights privilage
        if (in_array('ROLE_GROUP_ADMIN', $allRights)) {
            $roleAdminFlag = 1;
        }

        $userRightsIntersect = array_intersect($userRights, $allRights);
        $adminFlag = 0;
        //check the user has superadmin privilage
        $isAdmin = ($this->container->get('contact')->get('isSuperAdmin') || (($this->container->get('contact')->get('isFedAdmin')) && ($this->container->get('club')->get('type') != 'federation'))) ? 1 : 0;
        if (count($userRightsIntersect) > 0 || $isAdmin == 1) {
            $adminFlag = 1;
        }
        $sidebarActionRights = 0;
        //check the user can show the filter action menus
        if (in_array('ROLE_USERS', $allRights) || in_array('ROLE_ARTICLE', $allRights) || $isAdmin == 1) {
            $sidebarActionRights = 1;
        }
        $breadCrumb = array('breadcrumb_data' => array());

        $clubHeirarchy = $this->container->get('club')->get('clubHeirarchyDet');
        $clubTitles = array();
        //set the club heirarchy
        foreach ($clubHeirarchy as $clubId => $clubArr) {
            $clubTitles[$clubId]['title'] = ucfirst($clubArr['title']);
            $clubTitles[$clubId]['clubType'] = $clubArr['club_type'];
        }
        $clubTitles[$this->container->get('club')->get('id')]['title'] = ucfirst($club->get('title'));
        $clubTitles[$this->container->get('club')->get('id')]['clubType'] = 'club';

        $sidebarOptions = $this->getSidebarOptions();
        $currentClubType = $this->container->get('club')->get('type');
        $areaExist = (count($sidebarOptions['general']) > 0 || ($sidebarOptions['eventsWithoutArea'] > 0)) ? 1 : 0;
        $categoryExist = (count($sidebarOptions['category']) > 0) ? 1 : 0;
        //lazyLoadingPerRequest
        $lazyLoadingPerRequest = $this->container->getParameter('lazyLoadingPerRequest');
        $returnArray = array('breadCrumb' => $breadCrumb, 'adminFlag' => $adminFlag, 'sidebaractionMenuFlag' => $sidebarActionRights, 'roleadminFlag' => $roleAdminFlag, 'clubDefaultLang' => $clubDefaultLang, 'clubTitles' => $clubTitles, 'defaultSysLang' => $defaultSysLang, 'sidebarOptions' => json_encode($sidebarOptions), 'clubType' => $currentClubType, 'contactId' => $this->container->get('contact')->get('id'), 'clubId' => $this->container->get('club')->get('id'), 'areaExist' => $areaExist, 'categoryExist' => $categoryExist, 'lazyLoadingPerRequest' => $lazyLoadingPerRequest);

        return $this->render('InternalArticleBundle:Article:ArticleList.html.twig', $returnArray);
    }

    /**
     * Function to get article list data
     * 
     * @return object JsonResponse object
     */
    public function getListDataAction()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $pageId = $request->get('pageId', 1);
        $count = $request->get('countIs', '');
        $tableColumns = $this->getTableColumns();
        $clubId = $this->container->get('club')->get('id');

        $filterArray = $this->getFilterDataFromRequest($request);
        $articleListObj = new ArticlesList($this->container, 'article');
        $articleListObj->columnData = $tableColumns;
        $articleListObj->filterData = $filterArray;
        $articleListObj->setColumnData();
        $articleListObj->setColumnDataFrom();
        $articleListObj->setGroupBy();
        $articleListObj->addOrderBy();
        $articleListObj->addHaving(array("STATUS = 'published'"));
        $articleListObj->setLimit(($pageId - 1), $count);
        $articles = $articleListObj->getArticleData();
        //FAIR-2489
        foreach ($articles as $articlesKey => $articlesDet) {
            $articles[$articlesKey]['text'] = FgUtility::correctCkEditorUrl($articlesDet['text'], $this->container, $clubId);
        }
        $output['aaData'] = $articles;

        $this->saveArticleForNavigation($articles, $clubId . '_ARTICLE_OVERVIEW');

        return new JsonResponse($output);
    }

    /**
     * Function to get table columns
     * 
     * @return array article listing fields columns
     */
    private function getTableColumns()
    {
        $colArr = array();
        $columns = array('PUBLICATION_DATE', 'ARCHIVING_DATE', 'AUTHOR', 'AREAS', 'CATEGORIES');
        $em = $this->getDoctrine()->getManager();
        //global club comment settings 
        $club = $this->get('club');
        $clubId = $club->get('id');
        $getGlobalClubSettings = $em->getRepository('CommonUtilityBundle:FgCmsArticleClubsetting')->getClubSettings($clubId);
        $isCommentActive = $getGlobalClubSettings['commentActive'];
        if ($isCommentActive) {
            $columns[] = 'COMMENTS';
        }
        foreach ($columns as $colValue) {
            $colArr[]['id'] = $colValue;
        }

        return $colArr;
    }

    /**
     * Method get the areas/category of current club for the sidebar.
     *
     * @return array Sidebar data array
     */
    private function getSidebarOptions()
    {
        $articleList = new ArticlesList($this->container, 'article');
        $myArticles = $articleList->getMyVisibleArticleIds();
        $articleSidebarObj = new ArticleSidebar($this->container, $myArticles);

        return $articleSidebarObj->getDataForSidebar();
    }

    /**
     * Format filter array for article listing class
     * 
     * @param object $request Request object
     * 
     * @return array article filter Value
     */
    private function getFilterDataFromRequest($request)
    {
        $searchData = $request->get('searchDetails', '');
        $filterDate = json_decode($request->get('filterDetails', '[{}]'), true);
        $category = $areas = $areaHier = $dateFilter = $catHier = $filterValue = array();
        foreach ($filterDate as $value) {
            switch ($value['type']) {
                case 'workgroup':
                case 'team':
                    if (!empty($value['id'])) {
                        $areas[] = $value['id'];
                    }
                    break;
                case 'CLUB':
                    $filterValue['IS_CLUB'] = 1;
                    if (!isset($filterValue['AREAS'])) {
                        $filterValue['AREAS'] = 'NULL';
                    }
                    break;
                case 'AREA_WITHOUT':
                    $filterValue[$value['type']] = 1;
                    if (!isset($filterValue['AREAS'])) {
                        $filterValue['AREAS'] = 'NULL';
                    }
                    break;
                case 'CA_WITHOUT':
                    $filterValue[$value['type']] = 1;
                    if (!isset($filterValue['CATEGORIES'])) {
                        $filterValue['CATEGORIES'] = 'NULL';
                    }
                    break;
                case 'CA';
                    if (!empty($value['id'])) {
                        $category[] = $value['id'];
                    }
                    break;
                case 'year';
                    if (!empty($value['value'])) {
                        $dateValue = explode('#', $value['value']);
                        if (!empty($dateValue[0])) {
                            $filterValue['START_DATE'] = $dateValue[0];
                        }
                        if (!empty($dateValue[1])) {
                            $filterValue['END_DATE'] = $dateValue[1];
                        }
                    }
                    break;
                case 'CA_LEVELS';
                    $catHier[] = $value['value'];
                    break;
                case 'FED';
                    $areaHier[] = $value['id'];
                    break;
                case 'START_DATE':
                case 'END_DATE':
                    if (!empty($value['value'])) {
                        $date = new \DateTime();
                        $valueDate = $date->createFromFormat(FgSettings::getPhpDateFormat(), $value['value'])->format('Y-m-d');
                        $dateFilter[$value['type']] = $valueDate;
                    }
                    break;
            }
        }
        $filterValue = array_merge($filterValue, $dateFilter);
        if (count($areas) > 0) {
            if (count($areas) > 0) {
                $filterValue['AREAS'] = implode(',', $areas);
            }
        }
        if (count($category) > 0) {
            $filValues = implode(',', $category);
            if ($filValues !== 'ALL') {
                $filterValue['CATEGORIES'] = $filValues;
            }
        }
        if (count($catHier) > 0) {
            $filterValue['CAT_CLUB'] = implode(',', $catHier);
            if (!isset($filterValue['CATEGORIES'])) {
                $filterValue['CATEGORIES'] = 'NULL';
            }
        }
        if (count($areaHier) > 0) {
            $filterValue['AREA_CLUB'] = implode(',', $areaHier);
            if (!isset($filterValue['AREAS'])) {
                $filterValue['AREAS'] = 'NULL';
            }
        }
        if ($searchData != '') {
            $searchData = addslashes($searchData);
            $filterValue['SEARCH'] = $searchData;
        }

        return $filterValue;
    }

    /**
     * The function to set the next/previous data to session
     * 
     * @param Array  $articleList List of articles
     * @param string $key         Key value
     * 
     * @return void
     */
    private function saveArticleForNavigation($articleList, $key)
    {
        $articleString = '';
        $session = $this->get("session");
        foreach ($articleList as $article) {
            $articleString.= $article['articleId'] . ',';
        }
        $session->set($key, $articleString);
        
        return;
    }
}
