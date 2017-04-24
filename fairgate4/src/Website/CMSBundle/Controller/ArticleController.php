<?php

/**
 * ArticleController.
 */
namespace Website\CMSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Website\CMSBundle\Util\FgPageElement;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Website\CMSBundle\Util\FgWebsite;
use Common\FilemanagerBundle\Util\FgFileManager;
use Internal\ArticleBundle\Util\ArticlesList;
use Common\UtilityBundle\Util\FgUtility;

/**
 * WebsiteController.
 *
 * This controller is used for website public page
 *
 * @package 	Website
 * @subpackage 	CMSBundle
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 *
 */
class ArticleController extends Controller
{

    /**
     * Function to get the articles for the special page
     *
     * @param Object $request The request object
     *
     * @return object/json
     */
    public function articlelistAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $requestArray = $request->query->all();
        $contactId = $this->container->get('contact')->get('id');
        $isPublic = ($contactId > 0) ? false : true;

        $returnArray = $requestArray['returnArray'];

        //club titles array for showing terminology terms in calendar view
        $clubHeirarchy = $this->container->get('club')->get('clubHeirarchyDet');
        $clubId = $this->container->get('club')->get('id');

        $clubTitles = array();
        //set the club heirarchy
        foreach ($clubHeirarchy as $club => $clubArr) {
            $clubTitles[$club]['title'] = ucfirst($clubArr['title']);
            $clubTitles[$club]['clubType'] = $clubArr['club_type'];
        }
        $clubTitles[$clubId]['title'] = ucfirst($this->container->get('club')->get('title'));
        $clubTitles[$clubId]['clubType'] = 'club';

        $returnArray['clubTitles'] = $clubTitles;
        $returnArray['clubId'] = $clubId;

        if ($request->isXmlHttpRequest()) {
            $pageId = $request->get('pageId');
            $timeperiodArray = explode('__', $request->get('time'));
            $text = $request->get('text');
            $toBeEncodedArray['e'] = $pageId;
            $toBeEncodedArray['type'] = 'special';

            if (count($timeperiodArray) > 0) {
                $filterArray['START_DATE'] = $timeperiodArray[0];
                $filterArray['END_DATE'] = $timeperiodArray[1];
                $toBeEncodedArray['time'] = implode('__', $timeperiodArray);
            }
            if ($text != '') {
                $filterArray['SEARCH'] = $toBeEncodedArray['q'] = $text;
            }

            $pageElementObj = new FgPageElement($this->container);
            $articleData = $pageElementObj->getCmsPageArticleElementData($pageId, $isPublic, $filterArray, $request->get('page', 0), '', 10, 'page');
            //FAIR-2489
            foreach ($articleData as $articlesKey => $articlesDet) {
                $articleData[$articlesKey]['text'] = FgUtility::correctCkEditorUrl($articlesDet['text'], $this->container, $clubId);
            }
            $returnArray['articleData'] =  $articleData;
            $filterData = $em->getRepository('CommonUtilityBundle:FgCmsPage')->getArticlePageDetails($pageId, 'article');
            $returnArray['filterData'] = $filterData;
            $returnArray['toBeEncodedArray'] = $toBeEncodedArray;
            return new JsonResponse($returnArray);
        } else {
            $returnArray['pageId'] = $returnArray['pageDetails'][0]['id'];
            $websiteObj = new FgWebsite($this->container); //To show title for unassigned articles in backend
            $returnArray['articlePageTitle'] = ($returnArray['pageTitle'] != '') ? $returnArray['pageTitle'] : $websiteObj->getPageTitle($returnArray['mainPageId']);
            if ($returnArray['backendPreview'] == 1) {
                $returnArray['articleDetailLink'] = '';
            } else {
                $returnArray['articleDetailLink'] = $this->generateUrl('website_article_details_page', array('menu' => $returnArray['menu'], 'type' => 'article', 'id' => '__ARTICLEID__', 'encodedString' => '__ENCODEDSTRING__'));
                $returnArray['pagecontentData']['page']['page']['title'] = $returnArray['pageTitle'];
            }

            $returnArray['timeperiod'] = $this->getTimePeriodData($returnArray['pageId']);
            $returnArray['listTemplate'] = $this->container->get('cms.themes')->getViewPage('articleSpecialPageTemplate');
            
            return $this->render('WebsiteCMSBundle:Article:articlelist.html.twig', $returnArray);
        }
    }

    /**
     * Method to show the article details page in website 
     * 
     * @param object $request Request Object
     *
     * @return Object View Template Render Object
     */
    public function articleDetailsPageForWebsiteAction(Request $request)
    {
        $contactObj = $this->container->get('contact');
        $contactId = $contactObj->get('id');
        $articleId = $request->get('id');
        $menu = $request->get('menu');
        $isPublic = ($contactId == '' || $contactId == 'NULL') ? true : false;
        $articleListObj = new ArticlesList($this->container, 'article', $isPublic);
        $articleIds = $articleListObj->getMyVisibleArticleIds();
        $aticleIdArray = explode(',', $articleIds);
        if ($articleId == '' || !(in_array($articleId, $aticleIdArray))) {
            return $this->render('WebsiteCMSBundle:Website:errorPagePreview.html.twig');
        }
        $clubObj = $this->container->get('club');
        $pathService = $this->container->get('fg.avatar');
        $defaultLanguage = $clubObj->get('default_lang');
        $websiteObj = new FgWebsite($this->container);
        $articleObj = $this->container->get('article.create');
        $articleDataObj = $this->container->get('article.data');
        $returnArray = $websiteObj->getParametesForWebsiteLayout(array('menu' => $menu));
        $returnArray['defaultClubLang'] = $defaultLanguage;
        $returnArray['contactName'] = $contactObj->get('nameNoSort');
        $returnArray['contactImage'] = $pathService->getAvatar($contactId);
        $returnArray['isGuestContact'] = ($contactId > 0) ? false : true;
        $returnArray['articleId'] = $articleId;
        $returnArray['encodedString'] = $request->get('encodedString');
        $returnArray['pagecontentData']['googleCaptchaSitekey'] = $this->container->getParameter('googleCaptchaSitekey');
        $returnArray['commentCount'] = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsArticleComments')->getCommentsTotal($articleId);
        $returnArray['navigation'] = $this->getNextPreviousData($request);

        $articleDataObj->clubDefaultLanguage = $defaultLanguage;
        $articleData = $articleDataObj->getArticleDatas($articleId);
        
        $clubHeirarchy = $clubObj->get('clubHeirarchyDet');
        $articleData['article']['clubTitle'] = ucfirst($clubHeirarchy[$articleData['article']['club']]['title']);
        $articleClubSettings = $articleObj->getArticleClubSettings();
        $articleData['commentActive'] = (isset($articleClubSettings['commentActive'])) ? $articleClubSettings['commentActive'] : 1;
        $articleData['defaultClubLang'] = $defaultLanguage;
        $returnArray['articleData'] = $articleData;
        $returnArray = $this->setArticleDetailMeta($articleData, $returnArray, $clubObj);
        
        $returnArray['contactLang'] = $this->container->get('club')->get('default_lang');
        $returnArray['submitButtonTemplate'] = $this->container->get('cms.themes')->getViewPage('formSubmitButtonTemplate');
        $returnArray['formCaptchaTemplate'] = $this->container->get('cms.themes')->getViewPage('formCaptchaTemplate');
        $returnArray['pageData'] = $websiteObj->getOnPageLoadElementData($returnArray['footerId'],$returnArray['currentNavigationId'],$returnArray['pagecontentData']['pageElementsArray']);
            
        $viewPage = $this->container->get('cms.themes')->getViewPage('articleDetailPage');
        return $this->render($viewPage, $returnArray);
    }

    /**
     * Method to get data of an article
     *
     * @param object $request Request Object
     *
     * @return object JSON Response Object
     */
    public function getArticleDataAction(Request $request)
    {
        $result = array();
        $club = $this->container->get('club');
        $articleId = $request->get('articleId');
        $articleObj = $this->container->get('article.create');
        $articleDataObj = $this->container->get('article.data');
        $articleDataObj->clubDefaultLanguage = $club->get('default_lang');
        if ($articleId) {
            $result = $articleDataObj->getArticleDatas($articleId);
            $clubHeirarchy = $club->get('clubHeirarchyDet');
            $result['article']['clubTitle'] = ucfirst($clubHeirarchy[$result['article']['club']]['title']);
        }
        $articleClubSettings = $articleObj->getArticleClubSettings();
        $result['commentActive'] = (isset($articleClubSettings['commentActive'])) ? $articleClubSettings['commentActive'] : 1;
        $result['clubLanguages'] = json_encode($club->get('club_languages'));
        $result['defaultClubLang'] = $club->get('club_default_lang');

        return new JsonResponse($result);
    }

    /**
     * Function to get all the comments of an article
     *
     * @param int $articleId article id
     *
     * @return object JSON Response Object
     */
    public function getArticleCommentsAction($articleId)
    {
        $data = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsArticleComments')->getCommentsOfArticle($articleId);
        $pathService = $this->container->get('fg.avatar');
        $clubId = $this->container->get('club')->get('id');
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
        
        return new JsonResponse(array('data' => $data, 'contactId' => $contactId, 'articleId' => $articleId, 'isCommentAllow' => $commentAllow, 'contactImage' => $contactImage, 'contactName' => $contactName, 'globalCommentAccess' => $globalCommentAccess));
    }

    /**
     * Function to save a particular comment
     *
     * @param object $request Request Object
     *
     * @return object JSON Response Object
     */
    public function saveArticleCommentsAction(Request $request)
    {
        $articleId = $request->get('articleId');
        $commentId = $request->get('commentId');
        $comment = $request->get('comment');
        $contactId = $this->get('contact')->get('id');
        $guestContactName = $request->get('guestContactName', '');
        $return = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsArticleComments')->saveComments($articleId, $commentId, $comment, $contactId, $guestContactName);

        return new JsonResponse(array('status' => 'SUCCESS', 'date' => $return['date']));
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
     * Function to get the next/previous/back data from the url
     * 
     * @param object $request Request object
     * 
     * @return Object
     */
    private function getNextPreviousData($request)
    {
        $contactId = $this->container->get('contact')->get('id');
        $isPublic = ($contactId > 0) ? false : true;
        $encodedString = $request->get('encodedString');
        $dataArrayJson = base64_decode($encodedString);

        if ($dataArrayJson != '') {
            $dataArrayJson = json_decode($dataArrayJson, true);
            if ($dataArrayJson !== null) {
                // json array
                if ($dataArrayJson['i'] >= 0) {

                    //special page
                    if ($dataArrayJson['time'] != '' && (count(explode('__', $dataArrayJson['time'])) > 0)) {
                        $timeperiodArray = explode('__', $dataArrayJson['time']);
                        $filterArray['START_DATE'] = $timeperiodArray[0];
                        $filterArray['END_DATE'] = $timeperiodArray[1];
                    }

                    if ($dataArrayJson['q'] != '') {
                        $filterArray['SEARCH'] = $dataArrayJson['q'];
                    }

                    $elementId = $dataArrayJson['e'];

                    if ($dataArrayJson['type'] == 'special') {
                        $pageType = 'page';
                    } else {
                        $pageType = 'element';
                    }

                    if ($dataArrayJson['i'] <= 0) {
                        $offset = 0;
                        $count = 2;
                    } else {
                        $offset = ($dataArrayJson['i'] - 1);
                        $count = 3;
                    }

                    $pageElementObj = new FgPageElement($this->container);
                    $articleNavData = $pageElementObj->getCmsPageArticleElementData($elementId, $isPublic, $filterArray, 0, $offset, $count, $pageType);
                    return $this->createNavigationLinks($articleNavData, $dataArrayJson, $request);
                }
            }
        }

        return $this->createNavigationLinks(array(), array(), $request);
    }

    /**
     * Function to get the next/previous/back data from the url
     * 
     * @param array  $articleNavData Article navigation data
     * @param array  $dataArrayJson  Data array
     * @param object $request        The request object
     * 
     * @return array $returnArray The return array
     */
    private function createNavigationLinks($articleNavData, $dataArrayJson, $request)
    {
        $currentIndex = $dataArrayJson['i'];
        $menu = $request->get('menu');
        $clubObj = $this->container->get('club');
        
        $returnArray['articleDetailBackLink'] = $this->generateUrl('website_public_page_menus', array('menu' => $menu));
        $returnArray['articleDetailBackLabel'] = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsNavigation')->getPageTitleForNavigation($menu, $clubObj->get('id'), $clubObj->get('default_lang'));
        $returnArray['articleDetailBackSubLabel'] = $this->get('translator')->trans('WEBSITE_NEXTPREV_BACK');

        if ($currentIndex == 0) {
            //no previous
            if (is_array($articleNavData[1])) {
                $dataArrayJson['i'] = $currentIndex + 1;
                $returnArray['articleDetailNextLink'] = $this->generateUrl('website_article_details_page', array('menu' => $menu, 'type' => 'article', 'id' => $articleNavData[1]['articleId'], 'encodedString' => base64_encode(json_encode($dataArrayJson))));
                $returnArray['articleDetailNextLabel'] = $articleNavData[1]['title'];
                $returnArray['articleDetailNextSubLabel'] = $this->get('translator')->trans('WEBSITE_ARTICLE_NEXT');
            }
        } else {
            //previous && next
            if (is_array($articleNavData[0])) {
                $dataArrayJson['i'] = $currentIndex - 1;
                $returnArray['articleDetailPrevLink'] = $this->generateUrl('website_article_details_page', array('menu' => $menu, 'type' => 'article', 'id' => $articleNavData[0]['articleId'], 'encodedString' => base64_encode(json_encode($dataArrayJson))));
                $returnArray['articleDetailPrevLabel'] = $articleNavData[0]['title'];
                $returnArray['articleDetailPrevSubLabel'] = $this->get('translator')->trans('WEBSITE_ARTICLE_PREV');
            }

            if (is_array($articleNavData[2])) {
                $dataArrayJson['i'] = $currentIndex + 1;
                $returnArray['articleDetailNextLink'] = $this->generateUrl('website_article_details_page', array('menu' => $menu, 'type' => 'article', 'id' => $articleNavData[2]['articleId'], 'encodedString' => base64_encode(json_encode($dataArrayJson))));
                $returnArray['articleDetailNextLabel'] = $articleNavData[2]['title'];
                $returnArray['articleDetailNextSubLabel'] = $this->get('translator')->trans('WEBSITE_ARTICLE_NEXT');
            }
        }
        return $returnArray;
    }

    /**
     * Function to get the timeperiods that needed to be shown in the frontend
     * 
     * @param Int $id The id of the special page
     * 
     * @return Array
     */
    private function getTimePeriodData($id)
    {
        $pageElementObj = new FgPageElement($this->container);
        $contactId = $this->container->get('contact')->get('id');
        $isPublic = ($contactId > 0) ? false : true;
        return $pageElementObj->getCmsPageArticleTimeperiodData($id, $isPublic);
    }
        
    /**
     * Function to get the timeperiods that needed to be shown in the frontend
     * 
     * @param Array  $articleData   The array with detail of the specified article
     * @param Array  $returnArray   The array to be returned to the template
     * @param Object $clubObj       The club container
     * 
     * @return Array
     */
    private function setArticleDetailMeta($articleData, $returnArray, $clubObj)
    {
        $defaultLanguage = $clubObj->get('default_lang');
        
        $returnArray['pagecontentData']['og']['ogEnabled'] = true;
        $returnArray['pagecontentData']['og']['opengraph'] = array_column($articleData['article']['media'], 'imageName');
        $returnArray['pagecontentData']['og']['imagePath'] = FgUtility::getBaseUrl($this->container) . '/' .FgUtility::getUploadFilePath($articleData['article']['club'], 'gallery') .'/width_580/';
        
        if (($articleData['article']['text'][$defaultLanguage]) && ($articleData['article']['text'][$defaultLanguage]['title'] != '')) {
            $returnArray['pageTitle'] = $articleData['article']['text'][$defaultLanguage]['title'];
            $returnArray['metaDetails']['metaTitle'] = $articleData['article']['text'][$defaultLanguage]['title'];
        } else {
            $returnArray['pageTitle'] = $articleData['article']['text']['default']['title'];
            $returnArray['metaDetails']['metaTitle'] = $articleData['article']['text']['default']['title'];
        }

        if (($articleData['article']['text'][$defaultLanguage]['teaser'] != '')) {
            $returnArray['metaDetails']['metaDescription'] = $articleData['article']['text'][$defaultLanguage]['teaser'];
        } else if ( $articleData['article']['text']['default']['teaser']) {
            $returnArray['metaDetails']['metaDescription'] = $articleData['article']['text']['default']['teaser'];
        } else if (($articleData['article']['text'][$defaultLanguage]['text'] != '')) {
            $pureText = strip_tags($articleData['article']['text'][$defaultLanguage]['text']);
            $returnArray['metaDetails']['metaDescription'] = (strlen($pureText) <= 250)?$pureText:substr($pureText, 0, strpos($pureText, ' ', 250));
        } else if (($articleData['article']['text']['default']['text'] != '')) {
            $pureText = strip_tags($articleData['article']['text']['default']['text']);
            $returnArray['metaDetails']['metaDescription'] = (strlen($pureText) <= 250)?$pureText:substr($pureText, 0, strpos($pureText, ' ', 250));
        } else {
            $returnArray['metaDetails']['metaDescription'] = '';
        }
        
        return $returnArray;
    }
}
