<?php

/**
 * WebsiteController.
 */
namespace Website\CMSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Website\CMSBundle\Util\FgPageElement;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Common\UtilityBundle\Util\FgUtility;
use Website\CMSBundle\Util\FgWebsite;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Intl\Intl;
use Common\FilemanagerBundle\Util\FgFileManager;

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
class WebsiteController extends Controller
{

    /**
     * Landing page of all public website pages
     *
     * @param string $menu Menu url identifier
     *
     * @throws Error
     * 
     * @return response Redirection based on page type
     */
    public function navigationLandingAction(Request $request, $menu = null)
    {
        $club = $this->get('club');
        $clubId = $club->get('id');
        $contactId = $this->get('contact')->get('id');
        $requestUri = $request->getRequestUri();
        $session = $this->container->get('session');
        $websiteNavigationDetails = $club->get('navigationHeirarchy');
        $pageDetails = array();
        $additionalMenus = array();
        $websiteNavDetails = array();
        if ($contactId) {
            $websiteNavDetails = $websiteNavigationDetails;
            $homePage = $websiteNavDetails['homePageUrl'];
            unset($websiteNavDetails['publicPages'], $websiteNavDetails['publicPageUrl']);
        } else {
            $websiteNavDetails = $websiteNavigationDetails['publicPages'];
            $homePage = $websiteNavigationDetails['publicPageUrl'];
        }
        if ($menu == null) {
            if ($homePage != '') {
                if ($session->get('themePreviewFlag') && (strpos($requestUri, '/themepreview') > 0)) {
                    return $this->redirect($this->generateUrl('website_theme_preview_page_menu', array('menu' => $homePage)));
                    
                } else {
                    
                    return $this->forward('WebsiteCMSBundle:Website:navigationLanding', array('menu' => $homePage));                    
                }
            }
        } else {
            $navUrls = array_column($websiteNavDetails, 'navigation_url');
            if (!in_array($menu, $navUrls)) {
                //Additional Navigation Menu Checking 
                $additionalMenus = $this->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgCmsNavigation')
                    ->getAddtionalNavigationDetails($club->get('id'), $contactId, $club->get('default_lang'), $this->container, $menu);

                if ($additionalMenus) {
                    $navigationId = $additionalMenus[0]['id'];
                    $pageDetails[] = array('id' => $additionalMenus[0]['pageId'], 'type' => $additionalMenus[0]['pageType']);
                } else {

                    return $this->render('WebsiteCMSBundle:Website:errorPagePreview.html.twig');
                }
            } else {
                $currentNavIndex = array_search($menu, $navUrls);
                $navigationId = $websiteNavDetails[$currentNavIndex]['id'];
            }
        }

        // fetch the current pageDetails data from the exiting navigation heirarchy
        $currentPageDetailsArr = $websiteNavDetails[$currentNavIndex];
        if ($currentPageDetailsArr['page_id'] && !$pageDetails) {

            $pageDetails[] = array('id' => $websiteNavDetails[$currentNavIndex]['page_id'], 'type' => $websiteNavDetails[$currentNavIndex]['type']);
        }
        if (!$pageDetails) {
            //redirection to error page- styling to be done in future sprint
            return $this->render('WebsiteCMSBundle:Website:errorPagePreview.html.twig');
        }

        $returnArray = $this->baseReturnArrWebsiteFrontend($clubId, $menu);
        if (strpos($requestUri, '/themepreview') > 0) {
            $previewFlag = 'preview';
        } else {
            $previewFlag = 'website';
        }
        $returnArray['previewFlag'] = $previewFlag;

        $returnArray['currentNavigationId'] = $navigationId;

        return $this->redirectionBasedOnPageType($request, $pageDetails, $returnArray);
    }

    /**
     * base Return Array for Website Frontend
     * @param int   $clubId clubId
     * @param string $menu  menu
     *
     * @return array
     */
    private function baseReturnArrWebsiteFrontend($clubId, $menu)
    {
        $backgroundPath = FgUtility::getUploadFilePath($clubId, 'cms_background_image', false, false);
        $cssPath = FgUtility::getUploadFilePath($clubId, 'cms_themecss', false, false);
        $colorSchemeClubId = $this->get('club')->get('publicConfig')['colorSchemeClubId'];
        $colorCssPath = FgUtility::getUploadFilePath($colorSchemeClubId, 'cms_themecss', false, false);

        return array('menu' => $menu, 'cssPath' => $cssPath, 'colorCssPath' => $colorCssPath, 'backgroundPath' => $backgroundPath,
            'isHeader' => true);
    }

    /**
     * Function to redirect Based On Page Type
     *
     * @param object $request       request
     * @param array $pageDetails    pageDetails
     * @param array $returnArray    returnArray
     *
     * @return template
     */
    private function redirectionBasedOnPageType($request, $pageDetails, $returnArray)
    {
        $websiteObj = new FgWebsite($this->container);
        $returnArray['pageTitle'] = $websiteObj->getPageTitle($pageDetails[0]['id']);
        $returnArray['metaDetails'] = $websiteObj->getMetaDetails($returnArray['pageTitle']);
        $returnArray['pageDetails'] = $pageDetails;
        $returnArray['mainPageId'] = $pageDetails[0]['id'];
        $returnArray['clubDefaultLang'] = $this->container->get('club')->get('club_default_lang');
        $returnArray['contactLang'] = $this->container->get('club')->get('default_lang');
        
        $returnArray['uploadPath']['fileuploadPath'] = FgUtility::getUploadFilePath('**clubId**', 'contactfield_file');
        $returnArray['uploadPath']['imageuploadPath'] = FgUtility::getUploadFilePath('**clubId**', 'contactfield_image');
        $returnArray['uploadPath']['profilePic'] = FgUtility::getUploadFilePath('**clubId**', 'profilepic');
        $returnArray['uploadPath']['companyLogo'] = FgUtility::getUploadFilePath('**clubId**', 'companylogo');
        $returnArray['uploadPath']['placeholderImage'] = FgUtility::getUploadFilePath('**clubId**', 'cms_portrait_placeholder');
        $returnArray['submitButtonTemplate'] = $this->container->get('cms.themes')->getViewPage('formSubmitButtonTemplate');
        $returnArray['formCaptchaTemplate'] = $this->container->get('cms.themes')->getViewPage('formCaptchaTemplate');

        switch ($pageDetails[0]['type']) {
            case 'article':
                $queryParam = $request->query->all();
                $returnArray['pagecontentData'] = $websiteObj->getPageDetails($pageDetails[0]['id'], true, false);
                $returnArray['pageData'] = $websiteObj->getOnPageLoadElementData($pageDetails[0]['id'],$returnArray['currentNavigationId'],$returnArray['pagecontentData']['pageElementsArray']);

                return $this->forward("WebsiteCMSBundle:Article:articlelist", $queryParam, array('returnArray' => $returnArray));
            case 'gallery':
                $queryParam = $request->query->all();
                $returnArray['pagecontentData'] = $websiteObj->getPageDetails($pageDetails[0]['id'], true, false);
                $returnArray['pageData'] = $websiteObj->getOnPageLoadElementData($pageDetails[0]['id'],$returnArray['currentNavigationId'],$returnArray['pagecontentData']['pageElementsArray']);

                return $this->forward('WebsiteCMSBundle:Gallery:gallerySpecialPage', $queryParam, array('returnArray' => $returnArray));
            case 'calendar':
                $queryParam = $request->query->all();
                $returnArray['pagecontentData'] = $websiteObj->getPageDetails($pageDetails[0]['id'], true, false);
                $returnArray['pageData'] = $websiteObj->getOnPageLoadElementData($pageDetails[0]['id'],$returnArray['currentNavigationId'],$returnArray['pagecontentData']['pageElementsArray']);

                return $this->forward('WebsiteCMSBundle:Calendar:calendarSpecialPage', $queryParam, array('returnArray' => $returnArray));
            default:
                $returnArray['pagecontentData'] = $websiteObj->getPageDetails($pageDetails[0]['id']);
                $returnArray['pageData'] = $websiteObj->getOnPageLoadElementData($pageDetails[0]['id'],$returnArray['currentNavigationId'],$returnArray['pagecontentData']['pageElementsArray']);

                return $this->render('WebsiteCMSBundle:Website:displayWebsitePage.html.twig', $returnArray);
        }
    }

    /**
     * Function to get image elements data for provided element ids and page/sidebar/footer
     *
     * $request Object request object
     * 
     * @return array
     */
    public function getImageElementAction(Request $request)
    {
        $em = $this->container->get('doctrine')->getManager();
        $elementIds = $request->get('elementIds');
        $pageId = $request->get('pageId');
        $elementData = $em->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->getImageElementsDataAjax($this->container,$pageId,$elementIds);
        $returnArray['club_id'] = $this->container->get('club')->get('id');
        $returnArray['clubDefLang'] = $this->container->get('club')->get('club_default_lang');
        $returnArray['defLang'] = $this->container->get('contact')->get('default_lang');
        foreach($elementIds as $key => $element){
            $returnArray['imageData'] = array_values(array_filter($elementData,function($v,$k) use($element){ if($v['id'] == $element){return $v;}},ARRAY_FILTER_USE_BOTH));
            $returnArray['elementId'] = $element;
            $returnArray['pageId'] = $pageId;
            $returnArray['columnWidth'] = $em->getRepository('CommonUtilityBundle:FgCmsPage')->getPageContainerColumnWidth($pageId, $element);
            $returnArray['imageCount'] = count($returnArray['imageData']);
            $finalArray = $this->getDisplayDetails($returnArray);
            $retun[$element]['finalArray'] = $finalArray;
            $retun[$element]['ogtag'] = $em->getRepository('CommonUtilityBundle:FgCmsPageContentMedia')->getImageOGDetails($element);
        }
        
        return new JsonResponse($retun);
    }
    
     

    /**
     * Function to get image display details  for showing it in website
     *
     * @param array $returnArray image element array
     *
     * @return array full image details array
     */
    private function getDisplayDetails($returnArray)
    {
        if ($returnArray['imageCount'] > 0) {
            if ($returnArray['imageData'][0]['image_element_display_type'] == 'slider') {
                $returnArray['imageWidth'] = ($returnArray['columnWidth'] >= 4) ? 'width_1140' : (($returnArray['columnWidth'] == 3 || $returnArray['columnWidth'] == 2) ? 'width_580' : 'width_300');
            } elseif ($returnArray['imageData'][0]['image_element_display_type'] == 'row') {
                if ($returnArray['imageCount'] == 3) {
                    $returnArray['imageWidth'] = ($returnArray['columnWidth'] >= 5) ? 'width_580' : 'width_300';
                } elseif ($returnArray['imageCount'] == 2) {
                    $returnArray['imageWidth'] = ($returnArray['columnWidth'] >= 5) ? 'width_1140' : (($returnArray['columnWidth'] == 3 || $returnArray['columnWidth'] == 4) ? 'width_580' : 'width_300');
                } elseif ($returnArray['imageCount'] == 1) {
                    $returnArray['imageWidth'] = ($returnArray['columnWidth'] >= 4) ? 'width_1140' : ((($returnArray['columnWidth'] == 2) || ($returnArray['columnWidth'] == 3)) ? 'width_580' : 'width_300');
                } else {
                    $returnArray['imageWidth'] = 'width_300';
                }
            } else {
                $returnArray['imageWidth'] = 'width_300';
            }
        }
        return $returnArray;
    }

    /**
     * Json data for building intranet header.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return json header navigation
     */
    public function websiteHeaderNavAction(Request $request)
    {
        $menu = $request->get('level1');
        $contactId = $this->get('contact')->get('id');
        $clubService = $this->container->get('club');
        $session = $this->container->get('session');
        $requestUri = $request->getRequestUri();

        $clubId = $clubService->get('id');
        //fair2384
        $superadmin = $this->get('contact')->get('isSuperAdmin');
        if (!$superadmin && $contactId) {
            // logged in contact and is not a superadmin
            $contactLang = $this->get('contact')->get('corrLang');
        } else {
            //is superadmin or is not logged in
            $contactLang = $clubService->get('club_default_lang');
        }
        $websiteNavigationDetails = $clubService->get('navigationHeirarchy');
        if ($contactId) {
            $websiteNavDetails = $websiteNavigationDetails;
            unset($websiteNavDetails['homePageUrl']);
            unset($websiteNavDetails['publicPages']);
            unset($websiteNavDetails['publicPageUrl']); //echo '<pre>';print_r($websiteNavDetails);exit;
        } else {
            $websiteNavDetails = $websiteNavigationDetails['publicPages'];
        }
        $menuIds = array_column($websiteNavDetails, 'id');
        $menuDetails = array_combine($menuIds, $websiteNavDetails);
        $flatNavArr = array();
        $treeNavArr = array();
        foreach ($menuDetails as $childId => $childDetails) {
            $parentId = $childDetails['parent_id'];
            if (!isset($flatNavArr[$childId])) {
                $flatNavArr[$childId] = $childDetails;
            } else {
                $flatNavArr[$childId] = array_merge($childDetails, $flatNavArr[$childId]);
            }
            if ($parentId != 1) {
                $flatNavArr[$parentId]['subMenus'][] = & $flatNavArr[$childId];
            } else {
                $treeNavArr[] = & $flatNavArr[$childId];
            }
        }
        if ($session->get('themePreviewFlag') && (strpos($requestUri, '/themepreview') > 0)) {
            $navPath = $this->generateUrl('website_theme_preview_page_menu', array('menu' => '**dummy**'));
        } else {
            $navPath = $this->generateUrl('website_public_page_menus', array('menu' => '**dummy**'));
        }
        $configuration = $clubService->get('publicConfig');
        $headerPath = array('original' => FgUtility::getUploadFilePath($clubId, 'cms_header', false, false), 
            'resize1920' => '/uploads'.FgUtility::getUploadFilePath($clubId, 'cms_header1920', false, false), 
            'resize1170' => '/uploads'.FgUtility::getUploadFilePath($clubId, 'cms_header1170', false, false) );

        return new JsonResponse(array('clubLang' => $clubService->get('club_default_lang'), 'lang' => $contactLang, 'menu' => $menu, 'data' => $treeNavArr, 'contactId' => $contactId, 'navPath' => $navPath, 'config' => $configuration, 'headerPath' => $headerPath, 'logoPath' => $this->generateUrl('website_public_home_page')));
    }

    /**
     * Function to get article data for an article element for showing it in preview area
     *
     * @param int $elementId element id
     * @param int $pageId    page id
     *
     * @return object View Template Render Object
     *
     */
    public function getArticleElementDetailsAction(Request $request, $elementId, $pageId)
    {
        $contactId = $this->container->get('contact')->get('id');
        $isPublic = ($contactId > 0) ? false : true;
        $pageElementObj = new FgPageElement($this->container);
        $getArticleDisplayData = $this->container->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->getArticleEditData($elementId);
        $articleLimit = ($getArticleDisplayData['articleDisplayType'] == 'slider') ? $getArticleDisplayData['articleCount'] : $getArticleDisplayData['articlePerRow'] * $getArticleDisplayData['articleRowsCount'];
        $returnArray['articleData'] = $pageElementObj->getCmsPageArticleElementData($elementId, $isPublic, array(), 0, '', $articleLimit,'element',$getArticleDisplayData);
        $navObj = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsNavigation')->findOneBy(array(page => $pageId));
        if (!is_null($navObj)) {
            $menuName = $navObj->getNavigationUrl();
        } else {
            if ($request->get('menu') != '') {
                $menuName = $request->get('menu');
            } else {
                $menuName = 'unassignPagePreview';
            }
        }
        foreach ($returnArray['articleData'] as $id => $data) {
            $encodedString = base64_encode(json_encode(array('e' => $elementId, 'i' => $i++)));
            $returnArray['articleData'][$id]['detailsUrl'] = $this->generateUrl('website_article_details_page', array('menu' => $menuName, 'type' => 'article', 'id' => $data['articleId'], 'encodedString' => $encodedString));
        }
        $returnArray['elementId'] = $elementId;
        $returnArray['pageId'] = $pageId;
        $returnArray['columnWidth'] = $this->container->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->getPageContainerColumnWidth($pageId, $elementId);
        $returnArray['displayWidth'] = ($returnArray['columnWidth'] >= 5) ? 'width_580' : 'width_300';
        $filterData = $this->container->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->getArticleElementDetails($elementId, 'article');
        $returnArray['filterData'] = $filterData;
        $getArticleDisplayData['quotient']= floor($returnArray['columnWidth']/$getArticleDisplayData['articlePerRow']);
        $getArticleDisplayData['remainder'] = $returnArray['columnWidth'] % $getArticleDisplayData['articlePerRow'];
        $returnArray['structureData'] = $getArticleDisplayData;
        $returnAry = $this->articleElementFrondendModification($filterData, $returnArray);    
        $viewPage = $this->container->get('cms.themes')->getViewPage('articleElementTemplate');
        $retun['htmlContent'] = $this->renderView($viewPage, $returnAry);
        $retun['elementType'] = 'article';
        $retun['elementId'] = $elementId;

        return new JsonResponse($retun);
    }

    /**
     * Function to get article category and area to display
     *
     * @param array $filterData saved filter data
     * @param array $returnArray    article element details
     *
     * @return array article frontend data to display
     *
     */
    private function articleElementFrondendModification($filterData, $returnArray)
    {
        $clubId = $this->container->get('club')->get('id');
        $clubTitles = array();
        $clubHeirarchy = $this->container->get('club')->get('clubHeirarchyDet');
        //set the club heirarchy
        foreach ($clubHeirarchy as $club => $clubArr) {
            $clubTitles[$club]['title'] = ucfirst($clubArr['title']);
            $clubTitles[$club]['clubType'] = $clubArr['club_type'];
        }
        $clubTitles[$clubId]['title'] = ucfirst($this->container->get('club')->get('title'));
        $clubTitles[$clubId]['clubType'] = 'club';


        $isAllCategory = $filterData['isAllCategory'];
        $isAllArea = $filterData['isAllArea'];
        $categoryCount = ($filterData['categoryIds'] != null) ? count(explode(',', $filterData['categoryIds'])) : 0;
        $areaCount = ($filterData['areas'] != null) ? count(explode(',', $filterData['areas'])) : 0;
        $sharedClub = array_key_exists('sharedClub', $filterData);
        $areaClub = $filterData['areaClub'];
        $areaCount = ($areaClub) ? $areaCount + 1 : $areaCount;
        foreach ($returnArray['articleData'] as $index => $article) {
            $returnArray['articleData'][$index]['isAllCategory'] = $isAllCategory;
            $returnArray['articleData'][$index]['isAllArea'] = $isAllArea;
            $returnArray['articleData'][$index]['categoryCount'] = $categoryCount;
            $returnArray['articleData'][$index]['areaCount'] = $areaCount;
            $returnArray['articleData'][$index]['areaClub'] = $areaClub;
            $returnArray['articleData'][$index]['sharedClub'] = $sharedClub;
            if ($returnArray['articleData'][$index]['text']) {
                $returnArray['articleData'][$index]['text'] = $this->truncateTextString($returnArray['articleData'][$index]['text'], 160);
            }
            $returnArray['articleData'][$index]['areaTooltip'] = '';
            $returnArray['articleData'][$index]['catTooltip'] = '';
            $returnArray['articleData'][$index]['isCurrentClub'] = 1;
            if ($article['club_id'] != $clubId) {
                if ($clubTitles[$article['club_id']]['clubType'] == 'federation' || $clubTitles[$article['club_id']]['clubType'] == 'sub_federation') {
                    $returnArray['articleData'][$index]['areaTooltip'] = $clubTitles[$article['club_id']]['title'];
                    $returnArray['articleData'][$index]['catTooltip'] = $clubTitles[$article['club_id']]['title'];
                }
                $returnArray['articleData'][$index]['AREAS'] = '';
                $returnArray['articleData'][$index]['CATEGORIES'] = '';
                $returnArray['articleData'][$index]['isCurrentClub'] = 0;
            }
        }

        return $returnArray;
    }

    /**
     * Function to trim article test
     *
     * @param string $unTruncatedText article text
     * @param int    $maxLength       article text lenth to show
     *
     * @return string $trimmedString  trimmed text content
     *
     */
    private function truncateTextString($unTruncatedText, $maxLength)
    {

        $unTruncatedText = strip_tags($unTruncatedText);
        $strLength = strlen($unTruncatedText);
        if ($strLength > $maxLength) {
            $unTruncatedText = $unTruncatedText . ' ';
            $trimmedString = substr($unTruncatedText, 0, strpos($unTruncatedText, ' ', $maxLength)) . "...";
        } else {
            $trimmedString = $unTruncatedText;
        }

        return $trimmedString;
    }

    /**
     * Function to get text element data for showing it in previeww area
     *
     * @param int $elementId element id
     * @param int $pageId    page id
     *
     * @return object View Template Render Object
     */
    public function getTextElementDetailsAction(Request $request)
    {
        $em = $this->container->get('doctrine')->getManager();
        $elementIds = $request->get('elementIds');
        $pageId = $request->get('pageId');
        
        $returnArray = $em->getRepository('CommonUtilityBundle:FgCmsPageContentTextElement')->getTextElementAjax($this->container,$pageId, $elementIds);
        
        return new JsonResponse($returnArray);
    }

    /**
     * page preview - no error
     *
     * @return template
     */
    public function getNoPagePreviewAction()
    {

        return $this->render('WebsiteCMSBundle:Website:errorPagePreview.html.twig');
    }

    public function getPromoBoxContentAction(Request $request)
    {
        $responseData = array();
        $contactId = $this->container->get('contact')->get('id');
        $hasPromobox = $this->container->get('club')->get('hasPromobox');
        $isPublic = ($contactId == '' || $contactId == 'NULL') ? 1 : 0;
        if ($isPublic == 1) {
            $lang = $this->container->get('club')->get('club_default_lang');
        } else {
            $lang = $this->container->get('club')->get('default_system_lang');
        }
        $responseData['displayHtml'] = file_get_contents(dirname(__FILE__) . "/../Resources/views/Website/promobox/promobox-" . $lang . ".html.twig");
        if (strlen($responseData['displayHtml']) > 0 && $hasPromobox) {
            $cookies = $request->cookies->all();
            if (array_key_exists('displayPromo', $cookies)) {
                $responseData['displayPromo'] = 1;
                $responseData['displayPromoFull'] = 0;
            } else {
                $responseData['displayPromo'] = 1;
                $responseData['displayPromoFull'] = 1;
            }
        } else {
            $responseData['displayPromo'] = 0;
            $responseData['displayPromoFull'] = 0;
        }

        return new JsonResponse($responseData);
    }

    public function setPromoCookieAction()
    {
        $response = new Response('success');
        $response->headers->setCookie(new Cookie('displayPromo', '1', time() + (60 * 60 * 24 * 30)));

        return $response;
    }

    /**
     * function to show additional navigations and language
     *
     * @return JsonResponse Event details array
     */
    public function showAdditionalMenuAction(Request $request)
    {

        $contactId = $this->container->get('contact')->get('id');
        $club = $this->container->get('club');
        $return['currentmenu'] = $request->get('level1');
        //Checking Lang
        $superadmin = $this->container->get('contact')->get('isSuperAdmin');

        $contactLang = $club->get('default_lang');
        if (!$superadmin && $contactId) {
            // logged in contact and is not a superadmin
            $contactLang = $this->container->get('contact')->get('corrLang');
        } else {
            //is superadmin or is not logged in
            $contactLang = $club->get('default_lang');
        }
        //To get all language
        $clubLanguagesDet = $club->get('club_languages_det');
        $allLang = array();
        foreach ($clubLanguagesDet as $item => $clublang) {
            $allLang[$item] = Intl::getLanguageBundle()->getLanguageName($clublang['correspondanceLang'], '', $clublang['correspondanceLang']);
        }
        $langCookie = 'fg_website_lang_' . $club->get('id');
        $_COOKIE[$langCookie] = in_array($_COOKIE[$langCookie], $club->get('club_languages')) ? $_COOKIE[$langCookie] : $contactLang;
        if (count($clubLanguagesDet) > 1) {

            $return['defLang'] = (!$_COOKIE[$langCookie]) ? $contactLang : $_COOKIE[$langCookie];
            $return['langNav'] = $allLang;
        }
        $return['additionalNav'] = $this->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgCmsNavigation')
            ->getAddtionalNavigationDetails($club->get('id'), $contactId, $contactLang, $this->container);


        return new JsonResponse($return);
    }

    /**
     * Function is used to update all the json data of pages for domain
     *
     * @return response 
     */
    public function changeUrlJsonDataAction()
    {
        $websiteObj = new FgWebsite($this->container);
        $websiteObj->updateJsonPath();
        echo "Updated Successfully";

        return new Response();
    }

    /**
     * Function to get sponsor ad element details .
     * @param int $elementId element id
     * 
     * @return Object View Template Render Object
     */
    public function getSponsorDataAction($elementId)
    {
        $em = $this->getDoctrine()->getManager();
        $sponsorSavedData = $em->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->getSponsorAdElementData($elementId);
        $club = $this->container->get('club');
        $clubId = $this->container->get('club')->get('id');
        $sponsorAds['adDetails'] = $em->getRepository('CommonUtilityBundle:FgSmAdArea')->getAdPreviewDetailsOfSponsor($sponsorSavedData['sponsorServices'], $sponsorSavedData['adAreaIds'], $clubId, $this->container, $club);
        $sponsorAds['displayType'] = $sponsorSavedData['sponsorAdDisplayType'];
        $sponsorAds['faderInterval'] = $sponsorSavedData['sponsorAdDisplayTime'];
        $sponsorAds['horizontalWidth'] = $sponsorSavedData['sponsorAdMaxWidth'];
        $sponsorAds['elementId'] = $elementId;
        $retun['htmlContent'] = $this->renderView('WebsiteCMSBundle:PageContentElements:templateSponsorElement.html.twig', array('sponsorAds' => $sponsorAds));
        $retun['elementType'] = 'sponsor-ads';

        return new JsonResponse($retun);
    }

    /**
     * Method to download files of modules contact/users/admin
     *
     * @param string $module   contact/users/admin
     * @param string $source   source folder name
     * @param string $name     filename or folder name(original)
     * @param string $filename filename (it can be null)
     *
     * @return download dialog box
     */
    public function downloadFilesAction($module, $source, $name, $clubId, $filename = '')
    {
        $fileObj = new FgFileManager($this->container);
        //for handling 2 types structures.  eg1: contact/companylogo/original/filename, eg2: users/messages/filename
        //in 2nd case filename is null
        if ($filename == '') {
            $fileLocation = rtrim($clubId . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . $source . DIRECTORY_SEPARATOR);
            $downloadFileName = $originalFileName = $name;
            //for handling message attachments
            if ($source == 'messages') {
                $filenameArray = explode('~~__~~', $name);
                $originalFileName = $filenameArray[1];
            }
        } else {
            $fileLocation = rtrim($clubId . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . $source . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR);
            $downloadFileName = $originalFileName = $filename;
        }

        return $fileObj->downloadFile($downloadFileName, $originalFileName, $fileLocation);
    }

    /**
     * Function to save the url favisons from the faveicon generator
     * 
     * @return JsonResponse
     */
    public function updateOgTagAction(Request $request)
    {
        $ogTagDetail = $request->get('ogTags');
        $pageId = $request->get('pageId');
        if ($ogTagDetail != '') {
            $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->saveOpenGraphDetailsForPage($pageId, $ogTagDetail);
        }

        return new JsonResponse(array());
    }
}
