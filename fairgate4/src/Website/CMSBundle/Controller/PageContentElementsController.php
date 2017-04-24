<?php

/**
 * PageContentElementsController
 *
 */
namespace Website\CMSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Internal\ArticleBundle\Util\ArticlesList;
use Internal\GalleryBundle\Util\GalleryList;
use Internal\CalendarBundle\Util\CalenderEvents;
use Common\UtilityBundle\Repository\Pdo\CalendarPdo;
use Internal\CalendarBundle\Util\CalendarRecurrence;
use Internal\CalendarBundle\Util\Calenderfilter;
use Common\UtilityBundle\Util\FgSettings;
use Website\CMSBundle\Util\FgPageContent;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Repository\Pdo\CmsPdo;
use Common\UtilityBundle\Repository\Pdo\SponsorPdo;

/**
 * PageContentElementsController
 *
 * Description of PageContentElementsController
 *
 * @package 	Website
 * @subpackage 	CMSBundle
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 */
class PageContentElementsController extends Controller
{

    /**
     * This function render the element creation page.
     *
     * @param Request $request Request Object of this function
     *
     * @return object View Template Render Object
     */
    public function addNewElementAction(Request $request)
    {
        $returnArray = array();
        $elementType = $request->get('elementType');
        $pageId = $request->get('pageId');
        $boxId = $request->get('boxId');
        $elementId = $request->get('elementId');
        $sortOrder = $request->get('sortOrder');


        switch ($elementType) {
            case 'text':
                $renderObject = $this->textElement($pageId, $boxId, $elementId, $sortOrder);
                break;
            case 'image':
                $renderObject = $this->imageVideoElement($pageId, $boxId, $sortOrder, $elementId);
                break;
            case 'header':
                $renderObject = $this->headerElement($pageId, $boxId, $sortOrder, $elementId);
                break;
            case 'calendar':
                $renderObject = $this->calendarElement($pageId, $boxId, $sortOrder, $elementId);
                break;
            case 'articles':
                $renderObject = $this->articleElement($pageId, $boxId, $sortOrder, $elementId);
                break;
            case 'map':
                $renderObject = $this->mapElement($pageId, $boxId, $sortOrder, $elementId);
                break;
            case 'iframe':
                $renderObject = $this->iframeElement($pageId, $boxId, $sortOrder, $elementId);
                break;
            case 'login':
                $renderObject = $this->loginElement($pageId, $boxId, $sortOrder, $elementId);
                break;
            case 'form':
                if ($elementId > 0) {
                    return $this->redirectToRoute('website_cms_form_element_edit_frompage', array('formId' => $elementId));
                } else {
                    $renderObject = $this->getFormElementTemplatePopup($pageId, $boxId, $sortOrder, $elementId);
                }
                break;
            case 'supplementary-menu':
                $renderObject = $this->getSupplementaryMenuElementPopup($pageId, $boxId, $sortOrder, $elementId);
                break;
            case 'sponsor-ads':
                $renderObject = $this->getSponsorAdsElement($pageId, $boxId, $sortOrder, $elementId);
                break;
            case 'contact-application-form':
                if ($elementId > 0) {
                    return $this->redirectToRoute('contact_application_form_create', array('formId' => $elementId));
                } else {
                    $formId = $request->get('formId');
                    $renderObject = $this->getContactElementTemplatePopup($pageId, $boxId, $sortOrder, $elementId, $formId);
                }
                break;
            case 'newsletter-subscription':
                $renderObject = $this->newsletterSubscriptionElement($pageId, $boxId, $sortOrder, $elementId);
                break;
            case 'newsletter-archive':
                $renderObject = $this->newsletterArchiveElement($pageId, $boxId, $sortOrder, $elementId);
                break;
            case 'twitter':
                $renderObject = $this->twitterElement($pageId, $boxId, $sortOrder, $elementId);
                break;
            //contacts-table element handled directly in Fg_cms_page.js
            default:
                $renderObject = $this->render('WebsiteCMSBundle:PageContentElements:addNewElement.html.twig', $returnArray);
                break;
        }
        return $renderObject;
    }

    /**
     * This function returns the form element array
     *
     * @param Int $pageId      page id
     * @param Int $boxId       box id in page
     * @param Int $sortOrder   sort order of elements in box
     * @param Int $elementId   element id in box
     *
     * @return Object View Template Render Object
     */
    private function getContactElementTemplatePopup($pageId, $boxId, $sortOrder, $elementId, $formId)
    {
        $club = $this->container->get('club');
        $clubId = $club->get('id');
        $formList = $this->getDoctrine()->getRepository('CommonUtilityBundle:FgCmsForms')->activeContactFormAppList($clubId, false);
        $countForm = count($formList);
        if ($countForm > 1) {
            $return = array('formCount' => $countForm, 'appFormArray' => $formList, 'pageId' => $pageId, 'boxId' => $boxId, 'sortOrder' => $sortOrder, 'elementId' => $elementId);

            return $this->render('WebsiteCMSBundle:PageContentElements:contactApplicationFormPopup.html.twig', $return);
        } else {

            return $this->insertContactFormAction($pageId, $boxId, $sortOrder, $formId, $countForm);
        }
    }

    /**
     * insert selected contact form application
     *
     * @param    $pageId     int     page id
     * @param    $boxId      int     box id
     * @param    $sortOrder  int     sort order
     * @param    $formId     int     form id
     * @param    $countForm  int     count
     *
     * @return Object View Template Render Object
     */
    public function insertContactFormAction($pageId, $boxId, $sortOrder, $formId, $countForm)
    {
        $em = $this->getDoctrine()->getManager();
        $club = $this->container->get('club');
        $clubId = $club->get('id');
        $clubDefaultLang = $club->get('club_default_lang');
        $contactId = $this->container->get('contact')->get('id');
        $formObj = $em->getRepository('CommonUtilityBundle:FgCmsForms')->find($formId);
        if (!empty($formObj)) {
            $em->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->reOrderSortPosition($boxId, $sortOrder);
            $elementId = $em->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->insertNewElement($clubId, $boxId, $sortOrder, 'contact-application-form', $formId);

            /* add log */
            $defaultClubLang = $this->container->get('club')->get('club_default_lang');
            $pageTitles = $em->getRepository('CommonUtilityBundle:FgCmsPage')->getPageTite($pageId);
            $pageTitle = isset($pageTitles[$defaultClubLang]) ? $pageTitles[$defaultClubLang] : ($pageTitles['default'] == 'footer' ? $this->get('translator')->trans('TOP_NAV_CMS_FOOTER') : $this->get('translator')->trans('CMS_SIDEBAR'));
            $logArray[] = "('$elementId', '$pageId', 'element', 'added', '', '', now(), $contactId)";
            $logArray[] = "('$elementId', '$pageId', 'page', 'added', '', '$pageTitle', now(), $contactId)";

            $cmsPdo = new CmsPdo($this->container);
            $cmsPdo->saveLog($logArray);

            //Save content update time
            $session = $this->container->get('session');
            $pageObject = $em->getRepository('CommonUtilityBundle:FgCmsPage')->find($pageId);
            $session->set("lastCmsPageEditTime_" + $pageId, $pageObject->getContentUpdateTime()->format('Y-m-d H:i:s'));

            $pageContentObj = new FgPageContent($this->container);
            $pageContentDetails = $pageContentObj->getContentElementData($pageId);
            $clipboardContentDetails = $em->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->getClipboardDetails($clubId, $clubDefaultLang);

            return new JsonResponse(array('formCount' => $countForm, 'status' => 'SUCCESS', 'type' => 'loginTypeSave', 'noparentload' => true, 'clipboardData' => $clipboardContentDetails, 'data' => $pageContentDetails, 'flash' => $this->container->get('translator')->trans('CONTACT_APPFORM_SAVE_SUCCESS')));
        } else {
            $pageContentObj = new FgPageContent($this->container);
            $pageContentDetails = $pageContentObj->getContentElementData($pageId);
            $clipboardContentDetails = $em->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->getClipboardDetails($clubId, $clubDefaultLang);

            return new JsonResponse(array('formCount' => $countForm, 'status' => 'ERROR', 'noparentload' => true, 'clipboardData' => $clipboardContentDetails, 'data' => $pageContentDetails, 'flash' => $this->get('translator')->trans('CONTACT_APPFORM_SAVE_FAILED')));
        }
    }

    /**
     * This function is used to show supplementary menu pop up
     *
     * @param Int $pageId      page id
     * @param Int $boxId       box id in page
     * @param Int $sortOrder   sort order of elements in box
     * @param Int $elementId   element id in box
     *
     * @return Object View Template Render Object
     */
    private function getSupplementaryMenuElementPopup($pageId, $boxId, $sortOrder, $elementId = '')
    {
        $popupTitle = $this->get('translator')->trans('ADD_SUPPLEMENTARY_MENU_POPUP_TITLE');
        $popupText = $this->get('translator')->trans('SUPPLEMENTARY_MENU_POPUP_TEXT');
        $return = array('title' => $popupTitle, 'text' => $popupText, 'pageId' => $pageId, 'boxId' => $boxId, 'sortOrder' => $sortOrder, 'elementId' => $elementId);

        return $this->render('WebsiteCMSBundle:PageContentElements:supplementaryMenuPopup.html.twig', $return);
    }

    /**
     * Function to save Supplementary Menu Element
     *
     * @param Object $request request object
     *
     * @return Object View Template Render Object
     */
    public function saveSupplementaryMenuElementAction(Request $request)
    {
        $club = $this->container->get('club');
        $clubId = $club->get('id');
        $clubDefaultLang = $club->get('club_default_lang');
        $pageId = $request->get('pageId');
        $boxId = $request->get('boxId');
        $sortOrder = $request->get('sortOrder');
        $postElementId = $request->get('elementId');
        $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->reOrderSortPosition($boxId, $sortOrder);
        $session = $this->container->get('session');
        $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->saveSupplementaryMenuElement($clubId, $pageId, $boxId, $sortOrder, $postElementId, $session);
        $pageContentObj = new FgPageContent($this->container);
        $pageContentDetails = $pageContentObj->getContentElementData($pageId);
        $clipboardContentDetails = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->getClipboardDetails($clubId, $clubDefaultLang);
        //Save Json
        $pageContentObj->saveJsonContent($pageId);

        return new JsonResponse(array('status' => 'SUCCESS', 'type' => 'SupplementarySave', 'noparentload' => true, 'clipboardData' => $clipboardContentDetails, 'data' => $pageContentDetails, 'flash' => $this->container->get('translator')->trans('SUPPLEMENTARY_ELEMENT_SAVE_SUCCESS')));
    }

    /**
     * This function returns the login element array
     *
     * @param Int $pageId      page id
     * @param Int $boxId       box id in page
     * @param Int $sortOrder   sort order of elements in box
     * @param Int $elementId   element id in box
     *
     * @return Object View Template Render Object
     */
    private function loginElement($pageId, $boxId, $sortOrder, $elementId)
    {
        $club = $this->container->get('club');
        $clubId = $club->get('id');
        $clubDefaultLang = $club->get('club_default_lang');
        //save element
        $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->reOrderSortPosition($boxId, $sortOrder);
        $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->saveLoginElement($clubId, $pageId, $boxId, $sortOrder, $elementId);

        //Save content update time
        $session = $this->container->get('session');
        $pageObject = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->find($pageId);
        $session->set("lastCmsPageEditTime_" + $pageId, $pageObject->getContentUpdateTime()->format('Y-m-d H:i:s'));

        $pageContentObj = new FgPageContent($this->container);
        $pageContentDetails = $pageContentObj->getContentElementData($pageId);
        $clipboardContentDetails = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->getClipboardDetails($clubId, $clubDefaultLang);

        return new JsonResponse(array('status' => 'SUCCESS', 'type' => 'loginTypeSave', 'noparentload' => true, 'clipboardData' => $clipboardContentDetails, 'data' => $pageContentDetails, 'flash' => $this->container->get('translator')->trans('LOGIN_ELEMENT_SAVE_SUCCESS')));
    }

    /**
     * This function returns the newsletter Subscription element array
     *
     * @param Int $pageId      page id
     * @param Int $boxId       box id in page
     * @param Int $sortOrder   sort order of elements in box
     * @param Int $elementId   element id in box
     *
     * @return Object View Template Render Object
     */
    private function newsletterSubscriptionElement($pageId, $boxId, $sortOrder, $elementId)
    {
        $club = $this->container->get('club');
        $clubId = $club->get('id');
        $clubDefaultLang = $club->get('club_default_lang');
        //save element
        $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->reOrderSortPosition($boxId, $sortOrder);
        $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->saveNewsletterSubscriptionElement($clubId, $pageId, $boxId, $sortOrder, $elementId, $this->container->get('session'));
        $pageContentObj = new FgPageContent($this->container);
        $pageContentDetails = $pageContentObj->getContentElementData($pageId);
        $clipboardContentDetails = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->getClipboardDetails($clubId, $clubDefaultLang);

        return new JsonResponse(array('status' => 'SUCCESS', 'type' => 'newsletterSubscriptionTypeSave', 'noparentload' => true, 'clipboardData' => $clipboardContentDetails, 'data' => $pageContentDetails, 'flash' => $this->container->get('translator')->trans('NEWSLETTER_SUBSCRIPTION_ELEMENT_SAVE_SUCCESS')));
    }

    /**
     * This function returns the newsletter Archive element array
     *
     * @param Int $pageId      page id
     * @param Int $boxId       box id in page
     * @param Int $sortOrder   sort order of elements in box
     * @param Int $elementId   element id in box
     *
     * @return Object View Template Render Object
     */
    private function newsletterArchiveElement($pageId, $boxId, $sortOrder, $elementId)
    {
        $club = $this->container->get('club');
        $clubId = $club->get('id');
        $clubDefaultLang = $club->get('club_default_lang');
        //save element
        $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->reOrderSortPosition($boxId, $sortOrder);
        $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->saveNewsletterArchiveElementElement($clubId, $pageId, $boxId, $sortOrder, $elementId, $this->container->get('session'));

        $pageContentObj = new FgPageContent($this->container);
        $pageContentDetails = $pageContentObj->getContentElementData($pageId);
        $clipboardContentDetails = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->getClipboardDetails($clubId, $clubDefaultLang);

        return new JsonResponse(array('status' => 'SUCCESS', 'type' => 'newsletterArchiveTypeSave', 'noparentload' => true, 'clipboardData' => $clipboardContentDetails, 'data' => $pageContentDetails, 'flash' => $this->container->get('translator')->trans('NEWSLETTER_ARCHIVE_ELEMENT_SAVE_SUCCESS')));
    }

    /**
     * This function is used to show twitter-element edit page
     *
     * @param Int $pageId      page id
     * @param Int $boxId       box id in page
     * @param Int $sortOrder   sort order of elements in box
     * @param Int $elementId   element id in box
     *
     * @return Object View Template Render Object
     */
    private function twitterElement($pageId, $boxId, $sortOrder, $elementId = '')
    {

        $club = $this->container->get('club');
        $clubId = $club->get('id');
        $contactId = $this->container->get('contact')->get('id');
        $em = $this->getDoctrine()->getManager();
        $return['defLang'] = $club->get('club_default_lang');
        $return['clubLanguageArr'] = $club->get('club_languages');
        $return['clubId'] = $clubId;
        $return['clubTitle'] = $club->get('urlIdentifier');
        $return['contactId'] = $contactId;
        $tabs['cmsTwitterElementContent'] = array('text' => $this->get('translator')->trans('CMS_TAB_CONTENT'), 'activeClass' => 'active',);
        $tabs['cmsTwitterElementLog'] = array('text' => $this->get('translator')->trans('CMS_TAB_LOG'));
        $return['tabs'] = $tabs;
        $return['pageId'] = $pageId;
        $return['boxId'] = $boxId;
        $return['elementId'] = ($elementId) ? $elementId : 'new';
        $return['sortOrder'] = $sortOrder;
        $backLink = $this->generateUrl('website_cms_page_edit', array('pageId' => $pageId));
        $return['backLink'] = $backLink;
        $return['breadCrumb'] = array('back' => $backLink);
        $accountDetails = array();
        if ($return['elementId'] != 'new') {
            $accountDetails = $em->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->getTwitterAccountDetails($return['elementId']);
        }
        $return['accountDetails'] = $accountDetails;
//echo "<pre>";print_r($accountDetails);exit;
        return $this->render('WebsiteCMSBundle:PageContentElements:twitterElement.html.twig', $return);
    }

    /**
     * This function returns the image video element array
     *
     * @param Int $pageId      page id
     * @param Int $boxId       box id in page
     * @param Int $sortOrder   sort order of elements in box
     * @param Int $elementId   element id in box
     *
     * @return Object View Template Render Object
     */
    private function imageVideoElement($pageId, $boxId, $sortOrder, $elementId = '')
    {
        $club = $this->container->get('club');
        $backLink = $this->generateUrl('website_cms_page_edit', array('pageId' => $pageId));
        $returnArray['breadCrumb'] = array('back' => $backLink);
        $tabs['cmsImageVideoElementContent'] = array('text' => $this->get('translator')->trans('CMS_TAB_CONTENT'), 'activeClass' => 'active',);
        $tabs['cmsImageVideoElementLog'] = array('text' => $this->get('translator')->trans('CMS_TAB_LOG'));
        $returnArray['tabs'] = $tabs;
        $returnArray['contactId'] = $this->container->get('contact')->get('id');
        $returnArray['clubLanguageArr'] = $club->get('club_languages');
        $returnArray['defaultClubLang'] = $club->get('club_default_lang');
        $contactLang = $club->get('default_lang');
        $returnArray['clubId'] = $club->get('id');
        $returnArray['pageId'] = $pageId;
        $returnArray['boxId'] = $boxId;
        $returnArray['sortOrder'] = $sortOrder;
        $returnArray['elementId'] = $elementId;
        $returnArray['status'] = ($elementId > 1) ? 'old' : 'new';
        if ($returnArray['status'] == 'old') {
            $returnArray['editData'] = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->getImageElementData($elementId);
            foreach ($returnArray['editData'] as $key => $val) {
                $returnArray['editData'][$key]['descLang'] = stripslashes($val['descLang']);
            }
        }
        $returnArray['internalLinkArr'] = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsNavigation')->getNavigationDetails($this->container, $club, $returnArray['clubId'], $contactLang, true);

        $returnArray = array_merge($returnArray, $this->checkUserRightsForPages($returnArray['clubId'], $returnArray['contactId']));
        return $this->render('WebsiteCMSBundle:PageContentElements:imageElement.html.twig', $returnArray);
    }

    /**
     * Function to save check whether link can be given to page
     *
     * @param Int $clubId    Club id
     * @param Int $contactId Contact id
     *
     * @return Array page ids with user access
     */
    private function checkUserRightsForPages($clubId, $contactId)
    {
        $returnArray = array();
        $availableUserRights = $this->container->get('contact')->get('availableUserRights');
        $adminRights = $this->container->get('contact')->get('mainAdminRightsForFrontend');
        $isClubAdmin = (in_array('ROLE_USERS', $adminRights)) ? 1 : 0;
        $isCMSAdmin = (in_array('ROLE_CMS_ADMIN', $availableUserRights)) ? 1 : 0;
        $isSuperAdmin = ($this->container->get('contact')->get('isSuperAdmin') || (($this->container->get('contact')->get('isFedAdmin')) && ($this->container->get('club')->get('type') != 'federation'))) ? 1 : 0;
        if (($isCMSAdmin) || ($isClubAdmin) || ($isSuperAdmin)) {
            $returnArray['isSuper'] = 1;
            $returnArray['myPages'] = array();
        } else {
            $returnArray['isSuper'] = 0;

            $tempNavigationArray = array();
            $myPageAndNavigation = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:SfGuardUserPage')
                ->getMyPageAndNavigation($clubId, $contactId);
            foreach ($myPageAndNavigation as $page) {
                if ($page['nav_id'] != '') {
                    $tempNavigationArray[$page['nav_id']] = $page['nav_id'];
                }
            }
            $returnArray['myPages'] = $tempNavigationArray;
        }

        return $returnArray;
    }

    /**
     * Function to save image/video element
     *
     * @param Object $request request object
     *
     * @return object JSON Response Object
     */
    public function saveImageElementAction(Request $request)
    {
        $data = $request->request->all();
        $imageVideoData = json_decode($data['saveData'], true);
        $elmId = ($data['elementId'] > 0) ? $data['elementId'] : 'new';

        $club = $this->container->get('club');
        $clubId = $club->get('id');
        //new image data
        $newImgData = count($imageVideoData['element']['images']['new']) ? $imageVideoData['element']['images']['new'] : array();
        //existing gallery item
        $itemImgData = count($imageVideoData['element']['images']['item']) ? $imageVideoData['element']['images']['item'] : array();
        //new video data
        $videoData = count($imageVideoData['element']['video']['new']) ? $imageVideoData['element']['video']['new'] : array();
        //old image data
        $oldImgData = count($imageVideoData['element']['images']['old']) ? $imageVideoData['element']['images']['old'] : array();
        //old video data
        $oldVideoData = count($imageVideoData['element']['video']['old']) ? $imageVideoData['element']['video']['old'] : array();
        $imageElementData = $itemImgData + $newImgData + $videoData + $oldImgData + $oldVideoData;
        $newElementData = $this->getImageVideoInSaveFormat($newImgData, $videoData);
        //update sortorder
        if ($elmId === 'new') {
            $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->reOrderSortPosition($data['boxId'], $data['sortOrder']);
        }
        //save image and video in items table
        $galleryImageItemId = count($newElementData['image']) ? $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgGmItems')->saveGalleryImage($newElementData['image'], $this->container) : array();
        $galleryVideoItemId = count($newElementData['video']) ? $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgGmItems')->saveGalleryImage($newElementData['video'], $this->container) : array();
        //save element
        $elementId = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->saveImageElement($this->container,$data, $clubId, $elmId);
        //link media and element
        $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentMedia')->linkElementItem($imageElementData, array_merge($galleryImageItemId, $galleryVideoItemId), $elementId);
        //edit gallery description
        $this->editItemDesc($imageVideoData, $data['elementId']);
        $this->saveLogData($data['elementId'], $elementId, $data['pageId']);
        //Save Json
        $pageContentObj = new FgPageContent($this->container);
        $pageContentObj->saveJsonContent($data['pageId']);
        $saveReturnArray = ($data['saveType'] == 'save') ? array('noparentload' => true) : array('sync' => 1, 'redirect' => $this->generateUrl('website_cms_page_edit', array('pageId' => $data['pageId'])));
        $returnArray = array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('IMAGE_ELEMENT_SUCCESS_SAVE'), 'noparentload' => true, 'elementId' => $elementId);
        $return = array_merge($saveReturnArray, $returnArray);
        return new JsonResponse($return);
    }

    /**
     * This function is used to edit item desc
     *
     * @param Array $imageVideoData image video data
     * @param Int   $elementId      element id
     */
    private function editItemDesc($imageVideoData, $elementId)
    {
        $newItemArr = $itemImgData = $isDeletedArray = array();
        $imgOldData = count($imageVideoData['element']['images']['old']) ? $imageVideoData['element']['images']['old'] : array();
        $imgItemData = count($imageVideoData['element']['images']['item']) ? $imageVideoData['element']['images']['item'] : array();
        $vdoOldData = count($imageVideoData['element']['video']['old']) ? $imageVideoData['element']['video']['old'] : array();
        $itemImgData = $imgOldData + $imgItemData + $vdoOldData;
        $club = $this->container->get('club');
        $clubLanguageArr = $club->get('club_languages');
        $defaultClubLang = $club->get('club_default_lang');
        if (count($itemImgData) > 0) {
            foreach ($itemImgData as $key => $val) {
                if (isset($val['description'])) {
                    $newItemArr[$key] = $val;
                }
                if ($val['is_deleted'] == 1) {
                    $isDeletedArray[$key] = $val;
                }
            }
            if (count($newItemArr) > 0) {
                $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentMedia')->editItemDesc($newItemArr, $defaultClubLang, $clubLanguageArr, $this->container);
            }
            if (count($isDeletedArray) > 0) {
                $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentMedia')->unLinkElementItem($isDeletedArray, $elementId);
            }
        }
    }

    /**
     * This function is used to save image element log
     *
     * @param Int $elmId     to get status (new or edit)
     * @param Int $elementId element id
     * @param Int $pageId    page id
     *
     */
    private function saveLogData($elmId, $elementId, $pageId)
    {
        $club = $this->container->get('club');
        $contact = $this->container->get('contact');
        $contactId = $contact->get('id');
        $status = ($elmId > 0) ? 'changed' : 'added';
        $defaultClubLang = $club->get('club_default_lang');
        $pageTitles = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->getPageTite($pageId);
        $pageTitle = isset($pageTitles[$defaultClubLang]) ? $pageTitles[$defaultClubLang] : ($pageTitles['default'] == 'footer' ? $this->get('translator')->trans('TOP_NAV_CMS_FOOTER') : $this->get('translator')->trans('CMS_SIDEBAR'));
        $logArray[] = "('$elementId', '$pageId', 'element', '$status', '', '', now(), $contactId)";
        if ($status == 'added') {
            $logArray[] = "('$elementId', '$pageId', 'page', '$status', '', '$pageTitle', now(), $contactId)";
        }
        $cmsPdo = new CmsPdo($this->container);
        $cmsPdo->saveLog($logArray);
    }

    /**
     * This function is used to get data in save format and move to club galery directory
     *
     * @param Array $newImgData image data
     * @param Array $videoData  video data
     *
     * @return Array new image array
     */
    private function getImageVideoInSaveFormat($newImgData, $videoData)
    {
        $galleryListObj = new GalleryList($this->container);
        //if uploaded media is new image
        $newImageArr = $newVideoArr = array();
        if (count($newImgData) > 0) {
            $newImageArr['imgCount'] = count($newImgData);
            $newImageArr['type'] = 'IMAGE';
            $newImageArr['clubId'] = $this->container->get('club')->get('id');
            $newImageArr['contactId'] = $this->container->get('contact')->get('id');
            $newImageArr['defLang'] = $this->container->get('club')->get('club_default_lang');
            $newImageArr['clubLang'] = $this->container->get('club')->get('club_languages');
            $newImageArr['source'] = 'cmsimageelement';
            foreach ($newImgData as $key => $value) {
                foreach ($value['description'] as $descKey => $descVal) {
                    $newImageArr['imgDesc'][$descKey][] = $value['description'][$descKey];
                }
                $newImageArr['imgScope'][$key] = 'PUBLIC';
                $newImageArr['uploadedImages'][] = $value['value'];
                $newImageArr['uploadedImageId'][] = $key;
                $newImageArr['fileSize'][] = $value['size'];
                $newImageArr['fileName'][] = $value['name'];
            }
            $newImageArr = $galleryListObj->movetoclubgallery($newImageArr['uploadedImages'], $newImageArr['fileName'], $this->container->get('club')->get('id'), $newImageArr);
        }
        $newVideoArr = $this->getVideoInSaveFormat($videoData, $galleryListObj);

        return array('image' => $newImageArr, 'video' => $newVideoArr);
    }

    /**
     * This function is used to get video data in save format and move to club galery directory
     *
     * @param Array  $videoData      video data
     * @param Object $galleryListObj gallery list object
     *
     * @return Array new video array
     */
    private function getVideoInSaveFormat($videoData, $galleryListObj)
    {
        if (count($videoData) > 0) {
            $newVideoArr['imgCount'] = count($videoData);
            $newVideoArr['type'] = 'VIDEO';
            $newVideoArr['gm_video_scope'] = 'PUBLIC';
            $newVideoArr['clubId'] = $this->container->get('club')->get('id');
            $newVideoArr['contactId'] = $this->container->get('contact')->get('id');
            $newVideoArr['defLang'] = $this->container->get('club')->get('club_default_lang');
            $newVideoArr['clubLang'] = $this->container->get('club')->get('club_languages');
            $newVideoArr['source'] = 'cmsimageelement';
            foreach ($videoData as $videoKey => $videoValue) {
                foreach ($videoValue['description'] as $videoDescKey => $videoDescVal) {
                    $newVideoArr['imgDesc'][$videoDescKey][] = $videoValue['description'][$videoDescKey];
                }
                $newVideoArr['uploadedImages'][] = $videoValue['videoThumb'];
                $newVideoArr['uploadedImageId'][] = $videoKey;
                $imageExtension = end(explode('.', $videoValue['videoThumbImg']));
                $content = file_get_contents($videoValue['videoThumbImg']);
                $fileName = md5(rand()) . '.' . $imageExtension;

                $fp = fopen('uploads/temp/' . $fileName, 'w');
                fwrite($fp, $content);
                fclose($fp);
                $imageDetails = $galleryListObj->movetoclubgallery(array($fileName), array($fileName), $this->container->get('club')->get('id'), $imageDetails);
                $newVideoArr['videoThumb'][] = $fileName;
            }
        }

        return $newVideoArr;
    }

    /**
     * This function is used to show calendar-element edit page
     *
     * @return Object View Template Render Object
     */
    private function calendarElement($pageId, $boxId, $sortOrder, $elementId = '')
    {
        $club = $this->container->get('club');
        $clubId = $club->get('id');
        $contactId = $this->container->get('contact')->get('id');
        $defLang = $club->get('club_default_lang');
        $em = $this->getDoctrine()->getManager();
        $calendarCategories = $em->getRepository('CommonUtilityBundle:FgEmCalendarCategory')->getCalendarCategories($clubId, $defLang);
        $return['defLang'] = $defLang;
        $return['category'] = $calendarCategories;
        $return['areas'] = $this->getAllAreasForArticleAndCalendar();
        $return['clubId'] = $clubId;
        $return['clubTitle'] = $club->get('title');
        $return['contactId'] = $contactId;
        $tabs['cmsCalendarElementContent'] = array('text' => $this->get('translator')->trans('CMS_TAB_CONTENT'), 'activeClass' => 'active',);
        $tabs['cmsCalendarElementLog'] = array('text' => $this->get('translator')->trans('CMS_TAB_LOG'));
        $return['tabs'] = $tabs;
        $return['pageId'] = $pageId;
        $return['boxId'] = $boxId;
        $return['elementId'] = ($elementId) ? $elementId : 'new';
        $return['sortOrder'] = $sortOrder;
        $backLink = $this->generateUrl('website_cms_page_edit', array('pageId' => $pageId));
        $return['backLink'] = $backLink;
        $return['breadCrumb'] = array('back' => $backLink);
        $return['clubType'] = $club->get('type');
        $return['fedId'] = $club->get('federation_id');
        $return['subFedId'] = $club->get('sub_federation_id');
        $selectedAreasandCategories = array();
        if ($return['elementId'] != 'new') {
            $selectedAreasandCategories = $em->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->getArticleElementDetails($return['elementId'], 'calendar');
        }
        $return['selectedAreasandCategories'] = $selectedAreasandCategories;
        $return['isFedSharedEventsAvailable'] = (($return['clubType'] != 'federation') && ($return['clubType'] != 'standard_club')) ? $em->getRepository('CommonUtilityBundle:FgEmCalendar')->checkForSharedEvents($return['fedId']) : 0;
        $return['isSubFedSharedEventsAvailable'] = ($return['clubType'] == 'sub_federation_club') ? $em->getRepository('CommonUtilityBundle:FgEmCalendar')->checkForSharedEvents($return['subFedId']) : 0;

        return $this->render('WebsiteCMSBundle:PageContentElements:calendarElement.html.twig', $return);
    }

    /**
     * This function is used to show heading-element edit page
     *
     * @param Int $pageId      page id
     * @param Int $boxId       box id in page
     * @param Int $sortOrder   sort order of elements in box
     * @param Int $elementId   element id in box
     *
     * @return Object View Template Render Object
     */
    private function headerElement($pageId, $boxId, $sortOrder, $elementId = '')
    {
        $club = $this->container->get('club');
        $contact = $this->container->get('contact');
        $returnArray = array();
        $returnArray['breadCrumb'] = array();
        $tabs['elementContent'] = array('text' => $this->get('translator')->trans('CMS_TAB_CONTENT'), 'activeClass' => 'active',);
        $tabs['elementLog'] = array('text' => $this->get('translator')->trans('CMS_TAB_LOG'));
        $returnArray['tabs'] = $tabs;
        $returnArray['clubId'] = $club->get('id');
        $returnArray['contactId'] = $contact->get('id');
        $returnArray['defaultlang'] = $club->get('club_default_lang');
        $returnArray['pageId'] = $pageId;
        $returnArray['boxId'] = $boxId;
        $returnArray['elementId'] = $elementId;
        $returnArray['sortOrder'] = $sortOrder;
        if ($returnArray['elementId']) {
            $returnArray['elementDetails'] = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->getElementDetails($returnArray['elementId']);
        }
        $returnArray['clubLanguageArr'] = $club->get('club_languages');
        $returnArray['defaultClubLang'] = $club->get('club_default_lang');
        $returnArray['sizeArray'] = array(
            'large' => $this->get('translator')->trans('CMS_SIZE_LARGE'),
            'medium' => $this->get('translator')->trans('CMS_SIZE_MEDIUM'),
            'small' => $this->get('translator')->trans('CMS_SIZE_SMALL'),
            'mini' => $this->get('translator')->trans('CMS_SIZE_MINI'),
            'nano' => $this->get('translator')->trans('CMS_SIZE_NANO'),
        );
        $returnArray['breadCrumb'] = array('back' => $this->generateUrl('website_cms_page_edit', array('pageId' => $pageId)));

        return $this->render('WebsiteCMSBundle:PageContentElements:headerElement.html.twig', $returnArray);
    }

    /**
     * Function to save heading element
     *
     * @param Object $request request object
     *
     * @return Object JSON Response Object
     */
    public function saveHeaderElementAction(Request $request)
    {
        $club = $this->container->get('club');
        $contact = $this->container->get('contact');
        $clubId = $club->get('id');
        $contactId = $contact->get('id');
        $saveType = $request->get('saveType');
        $headingTitleArray = $request->get('titleArray');
        $headingSize = $request->get('titleSize');
        $pageId = $request->get('pageId');
        $boxId = $request->get('boxId');
        $sortOrder = $request->get('sortOrder');
        $logEntryFlag = $request->get('logEntry');
        $elId = $request->get('elementId') ? $request->get('elementId') : 'new';
        $status = ($elId == 'new') ? 'added' : 'changed';
        $defaultClubLang = $club->get('club_default_lang');
        $data = array('title' => $headingTitleArray, 'titleSize' => $headingSize, 'sortOrder' => $sortOrder, 'pageId' => $pageId);
        $pageTitles = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->getPageTite($pageId);
        $pageTitle = isset($pageTitles[$defaultClubLang]) ? $pageTitles[$defaultClubLang] : ($pageTitles['default'] == 'footer' ? $this->get('translator')->trans('TOP_NAV_CMS_FOOTER') : $this->get('translator')->trans('CMS_SIDEBAR'));
        //update sortorder
        if ($elId === 'new') {
            $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->reOrderSortPosition($boxId, $sortOrder);
        }
        $elementId = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->saveHeaderElement($data, $clubId, $boxId, $elId, $defaultClubLang);
        if ($logEntryFlag === '1') {
            $logArray[] = "('$elementId', '$pageId', 'element', '$status', '', '', now(), $contactId)";
            if ($status == 'added') {
                $logArray[] = "('$elementId', '$pageId', 'page', '$status', '', '$pageTitle', now(), $contactId)";
            }
            $cmsPdo = new CmsPdo($this->container);
            $cmsPdo->saveLog($logArray);
        }
        //Save Json
        $pageContentObj = new FgPageContent($this->container);
        $pageContentObj->saveJsonContent($pageId);
        $elementDetails = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->getElementDetails($elementId);
        $return = array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('CMS_CREATE_HEADING_ELEMENT_SUCCESS'), 'noparentload' => true, 'saveType' => $saveType, 'elementId' => $elementId, 'elementDetails' => $elementDetails);

        return new JsonResponse($return);
    }

    /**
     * Function to show article element page
     *
     * @param Int $pageId      page id
     * @param Int $boxId       box id in page
     * @param Int $sortOrder   sort order of elements in box
     * @param Int $elementId   element id in box
     *
     * @return Object View Template Render Object
     */
    public function articleElement($pageId, $boxId, $sortOrder, $elementId = '')
    {
        $clubId = $this->container->get('club')->get('id');
        $clubDefaultLanguage = $this->container->get('club')->get('club_default_lang');
        $returnArray['categories'] = $this->container->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgCmsArticleCategory')->getArticleCategories($clubId, $clubDefaultLanguage);
        $returnArray['areas'] = $this->getAllAreasForArticleAndCalendar();
        $tabs['cmsArticleElementContent'] = array('text' => $this->get('translator')->trans('CMS_TAB_CONTENT'), 'activeClass' => 'active');
        $tabs['cmsArticleElementLog'] = array('text' => $this->get('translator')->trans('CMS_TAB_LOG'));
        $returnArray['tabs'] = $tabs;
        $returnArray['contactId'] = $this->get('contact')->get('id');
        $returnArray['boxId'] = $boxId;
        $returnArray['elementId'] = ($elementId) ? $elementId : 'new';
        $returnArray['sortOrder'] = $sortOrder;
        $returnArray['pageId'] = $pageId;
        $returnArray['clubType'] = $this->container->get('club')->get('type');
        $returnArray['fedId'] = $this->container->get('club')->get('federation_id');
        $returnArray['subFedId'] = $this->container->get('club')->get('sub_federation_id');
        $returnArray['fedLowerLevelArticleCount'] = ($returnArray['clubType'] != 'federation' && $returnArray['clubType'] != 'standard_club') ? $this->container->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgCmsArticle')->getLowerLevelSharedArticleCount($returnArray['fedId']) : 0;
        $returnArray['subFedLowerLevelArticleCount'] = ($returnArray['clubType'] == 'sub_federation_club') ? $this->container->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgCmsArticle')->getLowerLevelSharedArticleCount($returnArray['subFedId']) : 0;
        $selectedAreasandCategories = array();
        if ($returnArray['elementId'] != 'new') {
            $selectedAreasandCategories = $this->container->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->getArticleElementDetails($returnArray['elementId'], 'article');
        }
        $returnArray['selectedAreasandCategories'] = $selectedAreasandCategories;
        $returnArray['breadCrumb'] = array('back' => $this->generateUrl('website_cms_page_edit', array('pageId' => $pageId)));

        return $this->render('WebsiteCMSBundle:PageContentElements:articleElement.html.twig', $returnArray);
    }

    /**
     * Function to save calendar element
     *
     * @param Object $request Request object
     *
     * @return Object View Template Render Object
     */
    public function saveCalendarElementAction(Request $request)
    {
        $clubId = $this->container->get('club')->get('id');
        $contactId = $this->container->get('contact')->get('id');
        $defaultClubLang = $this->container->get('club')->get('club_default_lang');
        $elementArr = $request->get('param');
        $pageId = $elementArr['pageId'];
        $postElementId = $elementArr['elementId'];
        $status = ($postElementId == 'new') ? 'added' : 'changed';
        $pageTitles = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->getPageTite($pageId);
        $pageTitle = isset($pageTitles[$defaultClubLang]) ? $pageTitles[$defaultClubLang] : ($pageTitles['default'] == 'footer' ? $this->get('translator')->trans('TOP_NAV_CMS_FOOTER') : $this->get('translator')->trans('CMS_SIDEBAR'));
        if ($postElementId === 'new') {
            $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->reOrderSortPosition($elementArr['boxId'], $elementArr['sortOrder']);
        }
        $elementId = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->saveCalendarElement($elementArr, $clubId);
        $logArray[] = "('$elementId', '$pageId', 'element','$status', '', '', now(), $contactId)";
        if ($status == 'added') {
            $logArray[] = "('$elementId', '$pageId', 'page', '$status', '', '$pageTitle', now(), $contactId)";
        }
        $cmsPdo = new CmsPdo($this->container);
        $cmsPdo->saveLog($logArray);
        //Save Json
        $pageContentObj = new FgPageContent($this->container);
        $pageContentObj->saveJsonContent($pageId);
        $saveType = $elementArr['saveType'];
        $saveReturnArray = ($saveType == 'save') ? array('noparentload' => true) : array('sync' => 1, 'redirect' => $this->generateUrl('website_cms_page_edit', array('pageId' => $pageId)));
        $returnArray = array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('CMS_CREATE_CALENDAR_ELEMENT_SUCCESS'), 'noparentload' => true, 'elementId' => $elementId);
        $return = array_merge($saveReturnArray, $returnArray);

        return new JsonResponse($return);
    }

    /**
     * Function to get club level areas for article and calendar elements
     *
     * @return Array  club level areas
     */
    private function getClubLevelAreas()
    {
        $clubObj = $this->container->get('club');
        $clubHeirarchy = $clubObj->get('clubHeirarchyDet');
        $cmsClubLevelRoleIds = $this->container->getParameter('cms_club_level_roleids');
        foreach ($clubHeirarchy as $clubArr) {
            if ($clubArr['club_type'] == 'federation') {
                $level = $cmsClubLevelRoleIds['federation'];
            } else if ($clubArr['club_type'] == 'sub_federation') {
                $level = $cmsClubLevelRoleIds['sub-federation'];
            } else {
                $level = $cmsClubLevelRoleIds['club'];
            }
            
            $levelArray[$level] = ucfirst($clubArr['title']);
        }
         $levelArray[$cmsClubLevelRoleIds['club']] = ucfirst($clubObj->get('title'));
         
        return $levelArray;
    }

    /**
     * Function to get JSON data for element log listing
     *
     * @param Object $request Request object
     *
     * @return Object JSON Response Object
     */
    public function getLogDetailsAction(Request $request)
    {
        $clubId = $this->container->get('club')->get('id');
        $elementId = $request->get('elementId');
        $returnArray['aaData'] = $this->container->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElementLog')->getLogData($elementId, $clubId);

        return new JsonResponse($returnArray);
    }

    /**
     * add new element - text element
     *
     * @param Int $pageId      page id
     * @param Int $boxId       box id in page
     * @param Int $sortOrder   sort order of elements in box
     * @param Int $elementId   element id in box
     *
     * @return Object View Template Render Object
     */
    private function textElement($pageId, $boxId, $elementId, $sortOrder)
    {
        $returnArray = array();
        $club = $this->container->get('club');
        $returnArray['sortOrder'] = $sortOrder;
        $returnArray['pageId'] = $pageId;
        $returnArray['boxId'] = $boxId != '' ? $boxId : 'new';
        $returnArray['elementId'] = $elementId != 0 ? $elementId : 'new';
        $returnArray['tabs'] = array('cmsTextElementContent' => array('text' => $this->get('translator')->trans('CMS_TAB_CONTENT'), 'activeClass' => 'active'), 'cmsTextElementLog' => array('text' => $this->get('translator')->trans('CMS_TAB_LOG')));
        $returnArray['editorialMode'] = '';
        $returnArray['clubLanguageArr'] = $club->get('club_languages');
        $returnArray['defaultClubLang'] = $club->get('club_default_lang');
        $returnArray['breadCrumb'] = array('back' => $this->generateUrl('website_cms_page_edit', array('pageId' => $pageId)));
        $returnArray['backLink'] = $this->generateUrl('website_cms_page_edit', array('pageId' => $pageId));
        $returnArray['clubLanguages'] = json_encode($club->get('club_languages'));
        $returnArray['mode'] = ($elementId == 0) ? 'create' : 'edit';
        $returnArray['contactId'] = $this->container->get('contact')->get('id');
        $returnArray['clubId'] = $club->get('id');
        $returnArray['authorName'] = $this->container->get('contact')->get('nameNoSort');
        $contact = $this->container->get('contact');
        $returnArray['isCluborSuperAdmin'] = (count($contact->get('mainAdminRightsForFrontend')) > 0) ? 1 : 0;
        $returnArray['isClubArticleAdmin'] = in_array('ROLE_ARTICLE', $contact->get('allRights')) ? 1 : 0;
        $availableUserRights = $this->container->get('contact')->get('availableUserRights');
        //team/workgroup admin | team/workgroup article admin
        $returnArray['isGroupAdmin'] = (count(array_intersect(array('ROLE_GROUP_ADMIN', 'ROLE_CMS_ADMIN'), $availableUserRights)) > 0) ? 1 : 0;
        $colorSchemeClubId = $this->get('club')->get('publicConfig')['colorSchemeClubId'];
        $returnArray['cssPath'] = FgUtility::getUploadFilePath($club->get('id'), 'cms_themecss', false, false);
        $returnArray['colorCssPath'] = FgUtility::getUploadFilePath($colorSchemeClubId, 'cms_themecss', false, false);

        return $this->render('WebsiteCMSBundle:PageContentElements:textElement.html.twig', $returnArray);
    }

    /**
     * save text element - create and edit - add log
     *
     * @param Request $request request
     *
     * @return JSON JSON Response Object
     */
    public function saveTextElementAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $contactId = $this->container->get('contact')->get('id');
            $pageId = $request->get('pageId');
            $boxId = $request->get('boxId');
            $sortOrder = $request->get('sortOrder');
            $elementId = $request->get('elementId');
            $saveData = json_decode($request->get('saveData'), true);
            if ( (!isset($saveData['textelement']['slider_time'])) || (!is_numeric($saveData['textelement']['slider_time']) )) {
                $saveData['textelement']['slider_time'] = 4;
            }
            $defaultClubLang = $this->container->get('club')->get('club_default_lang');
           
            $elementId1 = $this->container->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentTextElement')->saveTextElement($this->container, $saveData['textelement'], $pageId, $boxId, $sortOrder, $elementId,$defaultClubLang);
            //update sortorder
            if ($elementId != $elementId1) {
                $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->reOrderSortPosition($boxId, $sortOrder);
            }

            /* add log */
            
            $pageTitles = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->getPageTite($pageId);
            $pageTitle = isset($pageTitles[$defaultClubLang]) ? $pageTitles[$defaultClubLang] : ($pageTitles['default'] == 'footer' ? $this->get('translator')->trans('TOP_NAV_CMS_FOOTER') : $this->get('translator')->trans('CMS_SIDEBAR'));
            $status = ($elementId != $elementId1) ? 'added' : 'changed';
            $logArray[] = "('$elementId1', '$pageId', 'element', '$status', '', '', now(), $contactId)";
            if ($elementId != $elementId1) {
                $logArray[] = "('$elementId1', '$pageId', 'page', '$status', '', '$pageTitle', now(), $contactId)";
            }
            $cmsPdo = new CmsPdo($this->container);
            $cmsPdo->saveLog($logArray);
            //Save Json
            $pageContentObj = new FgPageContent($this->container);
            $pageContentObj->saveJsonContent($pageId);


            $flashMsg = ($elementId == $elementId1) ? $this->get('translator')->trans('TEXT_ELEMENT_EDITED') : $this->get('translator')->trans('TEXT_ELEMENT_CREATED');
            if ($elementId == $elementId1) {
                return new JsonResponse(array('status' => 'SUCCESS', 'noparentload' => true, 'flash' => $this->get('translator')->trans($flashMsg)));
            } else {
                $redirect = $this->generateUrl('website_cms_page_edit', array('pageId' => $pageId));
                return new JsonResponse(array('status' => 'SUCCESS', 'sync' => 1, 'redirect' => $redirect, 'flash' => $this->get('translator')->trans($flashMsg)));
            }
        }
    }

    /**
     * Function is to save twitter element
     *
     * @param Object $request Request object
     *
     * @return Object View Template Render Object
     */
    public function saveTwitterElementAction(Request $request)
    {

        $clubId = $this->container->get('club')->get('id');
        $contactId = $this->container->get('contact')->get('id');
        $defaultClubLang = $this->container->get('club')->get('club_default_lang');
        $elementArr = $request->get('param');
        $elementArr['acccountName'] = $this->stripSpecialChars($elementArr['acccountName']);
        $pageId = $elementArr['pageId'];
        $postElementId = $elementArr['elementId'];
        $status = ($postElementId == 'new') ? 'added' : 'changed';
        $pageTitles = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->getPageTite($pageId);
        $pageTitle = isset($pageTitles[$defaultClubLang]) ? $pageTitles[$defaultClubLang] : ($pageTitles['default'] == 'footer' ? $this->get('translator')->trans('TOP_NAV_CMS_FOOTER') : $this->get('translator')->trans('CMS_SIDEBAR'));
        if ($postElementId === 'new') {
            $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->reOrderSortPosition($elementArr['boxId'], $elementArr['sortOrder']);
        }
        $elementId = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->saveTwitterElement($elementArr, $defaultClubLang, $clubId);
        $logArray[] = "('$elementId', '$pageId', 'element','$status', '', '', now(), $contactId)";
        if ($status == 'added') {
            $logArray[] = "('$elementId', '$pageId', 'page', '$status', '', '$pageTitle', now(), $contactId)";
        }
        $cmsPdo = new CmsPdo($this->container);
        $cmsPdo->saveLog($logArray);
        //Save Json
        $pageContentObj = new FgPageContent($this->container);
        $pageContentObj->saveJsonContent($pageId);
        $saveType = $elementArr['saveType'];
        $elementDetails = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->getTwitterAccountDetails($elementId);
        $saveReturnArray = ($saveType == 'save') ? array('noparentload' => true,'accountTitle' => $elementDetails) : array('sync' => 1, 'redirect' => $this->generateUrl('website_cms_page_edit', array('pageId' => $pageId)));
        $returnArray = array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('CMS_CREATE_TWITTER_ELEMENT_SUCCESS'), 'noparentload' => true, 'elementId' => $elementId);
        $return = array_merge($saveReturnArray, $returnArray);

        return new JsonResponse($return);
    }
    /**
     * Function is to remove @ from twitter account
     * 
     * @param   array $accountArray account name array
     * 
     * @return  array $accountArray modified account name array
     */
    private function stripSpecialChars($accountArray)
    {
        foreach ($accountArray as $key => $value) {
            $accountArray[$key] = str_replace("@", "", $value);
        }

        return $accountArray;
    }

    /**
     * Function to save article element data
     *
     * @param Object $request Request object
     *
     * @return JSON JSON Response Object
     */
    public function saveArticleElementAction(Request $request)
    {
        $data = $request->request->all();
        $clubId = $this->container->get('club')->get('id');
        $contactId = $this->container->get('contact')->get('id');
        $defaultClubLang = $this->container->get('club')->get('club_default_lang');
        $pageId = $data['pageId'];
        $postElementId = $data['elementId'];
        $pageTitles = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->getPageTite($pageId);
        $pageTitle = isset($pageTitles[$defaultClubLang]) ? $pageTitles[$defaultClubLang] : ($pageTitles['default'] == 'footer' ? $this->get('translator')->trans('TOP_NAV_CMS_FOOTER') : $this->get('translator')->trans('CMS_SIDEBAR'));
        if ($postElementId === 'new') {
            $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->reOrderSortPosition($data['boxId'], $data['sortOrder']);
        }
        $status = ($postElementId == 'new') ? 'added' : 'changed';
        $elementId = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->saveArticleElement($data, $clubId);
        if ($status == 'added') {
            $logArray[] = "('$elementId', '$pageId', 'page', '$status', '', '$pageTitle', now(), $contactId)";
        }
        $logArray[] = "('$elementId', '$pageId', 'element', '$status', '', '', now(), $contactId)";
        $cmsPdo = new CmsPdo($this->container);
        $cmsPdo->saveLog($logArray);
        //Save Json
        $pageContentObj = new FgPageContent($this->container);
        $pageContentObj->saveJsonContent($pageId);
        $saveType = $data['saveType'];
        $saveReturnArray = ($saveType == 'save') ? array('noparentload' => true) : array('sync' => 1, 'redirect' => $this->generateUrl('website_cms_page_edit', array('pageId' => $pageId)));
        $returnArray = array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('ARTICLE_ELEMENT_SUCCESS_SAVE'), 'noparentload' => true, 'elementId' => $elementId);
        $return = array_merge($saveReturnArray, $returnArray);

        return new JsonResponse($return);
    }

    /**
     * get data for text element
     *
     * @param Request $request Request object
     *
     * @return JSON json response result
     */
    public function getDataAction(Request $request)
    {
        $elementId = $request->get('elementId');

        $result = array();
        if ($elementId != 'new') {
            $result = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentTextElement')->getTextElement($elementId, $this->container);
            $result['textelement']['media'] = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentTextElement')->getTextElementMedia($this->container, $elementId);
        }
        $club = $this->container->get('club');
        $result['elementId'] = $elementId;
        if($elementId != 'new'){
           $textElementObj = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->find($elementId);
           $result['textslider'] = $textElementObj->getImageElementSliderTime(); 
        }
        $result['clubLanguages'] = json_encode($club->get('club_languages'));
        $result['defaultClubLang'] = $club->get('club_default_lang');
        $result['clubType'] = $club->get('type');
      
        return new JsonResponse($result);
    }

    /**
     * Function to get all areas for article and calendar element for listing in dropdown
     *
     * @return Array areas array
     */
    private function getAllAreasForArticleAndCalendar()
    {
        $areas = array();
        $workgroupCatId = $this->container->get('club')->get('club_workgroup_id');
        $teamCatId = $this->container->get('club')->get('club_team_id');
        $areas['clubLevels'] = $this->getClubLevelAreas();
        $roles = $this->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgRmRole')->getAllActiveRolesOfAClub($this->container, array($teamCatId, $workgroupCatId));
        $areas['teams'] = $roles['teams'];
        $areas['workgroups'] = $roles['workgroups'];

        return $areas;
    }

    /**
     * Function to get article data of an article element for showing it in content area
     *
     * @param Int $elementId element id
     * @param Int $pageId    page id
     *
     * @return Object View Template Render Object
     */
    public function getArticleElementDetailsAction($elementId, $pageId)
    {
        $selectedAreasandCategories = $this->container->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->getArticleElementDetails($elementId, 'article');
        $tableColumns = $this->getTableColumns();
        $filterArray = $this->getFilterArrayForArticleElement($selectedAreasandCategories);
        $articleListObj = new ArticlesList($this->container, 'article');
        $articleListObj->columnData = $tableColumns;
        $articleListObj->filterData = $filterArray;
        $articleListObj->setColumnData();
        $articleListObj->setColumnDataFrom();
        $articleListObj->setGroupBy();
        $articleListObj->addOrderBy();
        $articleListObj->setLimit(0, 5);
        $articleListObj->addHaving(array("STATUS = 'published'"));
        $articles = $articleListObj->getArticleData();
        $returnArray['articleData'] = $articles;
        $returnArray['elementId'] = $elementId;
        $returnArray['pageId'] = $pageId;

        $returnArray['columnWidth'] = $this->container->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->getPageContainerColumnWidth($pageId, $elementId);
        $returnArray['displayWidth'] = ($returnArray['columnWidth'] >= 5) ? 'width_580' : 'width_300';
        $filterData = $this->container->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->getArticleElementDetails($elementId, 'article');
        $returnAry = $this->articleElementFrondendModification($filterData, $returnArray);

        return $this->render('WebsiteCMSBundle:PageContentElements:articleElementTemplate.html.twig', $returnAry);
    }

    /**
     * Function to get article category and area to display in tags
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
//echo "<pre>";print_r($returnArray);exit;
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
     * Function to get image data of an image element for showing it in content area
     *
     * @param Int $elementId element id
     * @param Int $pageId    page id
     *
     * @return Object View Template Render Object
     */
    public function getImageElementDetailsAction($elementId, $pageId)
    {
        $elementData = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->getImageElementData($elementId);
        $returnArray['imageData'] = $elementData;
        $returnArray['elementId'] = $elementId;
        $returnArray['pageId'] = $pageId;
        $returnArray['club_id'] = $this->container->get('club')->get('id');
        $returnArray['columnWidth'] = $this->container->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->getPageContainerColumnWidth($pageId, $elementId);
        $returnArray['imageCount'] = count($returnArray['imageData']);
        $finalArray = $this->getDisplayDetails($returnArray);

        return $this->render('WebsiteCMSBundle:PageContentElements:imageElementTemplate.html.twig', $finalArray);
    }

    /**
     *  Function to get image display details  for showing it in  content area
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
     * Function to get table columns for getting article data
     *
     * @return Array article listing fields columns
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
     * Function to get filter array for getting the articles of an article element
     *
     * @param Array $selectedAreasandCategories selected categories and areas of ana article element
     *
     * @return Array filter array for article element
     */
    private function getFilterArrayForArticleElement($selectedAreasandCategories)
    {
        $clubId = $this->container->get('club')->get('id');
        $filterArray = array();

        //Generates filter array for selected areas
        if ($selectedAreasandCategories['areas']) {
            $workgroupCatId = $this->container->get('club')->get('club_workgroup_id');
            $teamCatId = $this->container->get('club')->get('club_team_id');
            $roles = $this->container->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgRmRole')->getAllActiveRolesIdsOfAClub($this->container, array($teamCatId, $workgroupCatId));
            $activeAreas = array_intersect(explode(',', $selectedAreasandCategories['areas']), explode(',', $roles));
            $areas = implode(',', $activeAreas);
            if ($areas) {
                $filterArray['AREAS'] = $areas;
            }
        }

        //Generates filter array for selected categories
        if ($selectedAreasandCategories['categoryIds']) {
            $categories = implode(',', $selectedAreasandCategories['catIds']);
            $filterArray['CATEGORIES'] = $categories;
        }

        //Generates filter array for all areas
        if ($selectedAreasandCategories['isAllArea'] == 1) {
            $filterArray['AREAS'] = $this->getAllAreasForArticleFilter();
            $filterArray['IS_CLUB'] = 1;
        }

        //Generates filter array for all categories
        if ($selectedAreasandCategories['isAllCategory'] == 1) {
            $filterArray['CATEGORIES'] = $this->getAllCategoriesForArticleFilter($clubId);
        }

        //Generates filter array when shared article check box is checked
        if ($selectedAreasandCategories['clubShared']) {
            $filterArray['AREA_CLUB'] = $selectedAreasandCategories['clubShared'];
            $filterArray['CAT_CLUB'] = $selectedAreasandCategories['clubShared'];
            if (!array_key_exists('AREAS', $filterArray)) {
                $filterArray['AREAS'] = 'NULL';
            }
            if (!array_key_exists('CATEGORIES', $filterArray)) {
                $filterArray['CATEGORIES'] = 'NULL';
            }
        }

        //Generates filter array when club is selected in area dropdown
        if ($selectedAreasandCategories['areaClub']) {
            $filterArray['IS_CLUB'] = 1;
            if (!$selectedAreasandCategories['areas']) {
                $filterArray['AREAS'] = 'NULL';
            }
        }
        // FAIR-2544 Change of area and category selection on calendar and article pages and elements
        if (!isset($filterArray['AREA_CLUB'])) {
            if (!isset($filterArray['CATEGORIES'])) {
                $filterArray = array('AREAS' => -1);
            }
            if (!isset($filterArray['AREAS']) && !isset($filterArray['IS_CLUB'])) {
                $filterArray = array('CATEGORIES' => -1);
            }
        }

        return $filterArray;
    }

    /**
     * Function to get data in filter for handling all categories
     *
     * @param Array $clubId current club id
     *
     * @return String all categories for article filter
     */
    public function getAllCategoriesForArticleFilter($clubId)
    {
        $clubDefaultLanguage = $this->container->get('club')->get('club_default_lang');
        $allCategories = $this->container->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgCmsArticleCategory')->getArticleCategories($clubId, $clubDefaultLanguage);
        $catArray = array();
        foreach ($allCategories as $key => $value) {
            $catArray[$key] = $value['id'];
        }

        return implode(',', $catArray);
    }

    /**
     * Function to show map element page
     *
     * @param Int $pageId      page id
     * @param Int $boxId       box id in page
     * @param Int $sortOrder   sort order of elements in box
     * @param Int $elementId   element id in box
     *
     * @return Object View Template Render Object
     */
    public function mapElement($pageId, $boxId, $sortOrder, $elementId = '')
    {
        $tabs['cmsMapElementContent'] = array('text' => $this->get('translator')->trans('CMS_TAB_CONTENT'), 'activeClass' => 'active');
        $tabs['cmsMapElementLog'] = array('text' => $this->get('translator')->trans('CMS_TAB_LOG'));
        $returnArray['tabs'] = $tabs;
        $returnArray['contactId'] = $this->get('contact')->get('id');
        $returnArray['boxId'] = $boxId;
        $returnArray['elementId'] = ($elementId) ? $elementId : 'new';
        $returnArray['sortOrder'] = $sortOrder;
        $returnArray['pageId'] = $pageId;
        $existingMapElementData = array();
        if ($returnArray['elementId'] != 'new') {
            $existingMapElementData = $this->container->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->getMapElementData($returnArray['elementId']);
        }
        $returnArray['existingMapElementData'] = $existingMapElementData;
        $returnArray['breadCrumb'] = array('back' => $this->generateUrl('website_cms_page_edit', array('pageId' => $pageId)));

        return $this->render('WebsiteCMSBundle:PageContentElements:mapElement.html.twig', $returnArray);
    }

    /**
     * Function to save map element data
     *
     * @param Object $request Request object
     *
     * @return Object JSON Response Object
     */
    public function saveMapElementAction(Request $request)
    {
        $data = $request->request->all();
        $clubId = $this->container->get('club')->get('id');
        $contactId = $this->container->get('contact')->get('id');
        $defaultClubLang = $this->container->get('club')->get('club_default_lang');
        $pageId = $data['pageId'];
        $postElementId = $data['elementId'];
        $pageTitles = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->getPageTite($pageId);
        $pageTitle = isset($pageTitles[$defaultClubLang]) ? $pageTitles[$defaultClubLang] : ($pageTitles['default'] == 'footer' ? $this->get('translator')->trans('TOP_NAV_CMS_FOOTER') : $this->get('translator')->trans('CMS_SIDEBAR'));
        if ($postElementId === 'new') {
            $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->reOrderSortPosition($data['boxId'], $data['sortOrder']);
        }
        $status = ($postElementId == 'new') ? 'added' : 'changed';
        $elementId = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->saveMapElement($data, $clubId);
        $logArray[] = "('$elementId', '$pageId', 'element', '$status', '', '', now(), $contactId)";
        if ($status == 'added') {
            $logArray[] = "('$elementId', '$pageId', 'page', '$status', '', '$pageTitle', now(), $contactId)";
        }
        $cmsPdo = new CmsPdo($this->container);
        $cmsPdo->saveLog($logArray);
        $saveType = $data['saveType'];

        //Save Json
        $pageContentObj = new FgPageContent($this->container);
        $pageContentObj->saveJsonContent($pageId);
        $saveReturnArray = ($saveType == 'save') ? array('noparentload' => true) : array('sync' => 1, 'redirect' => $this->generateUrl('website_cms_page_edit', array('pageId' => $pageId)));
        $returnArray = array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('CMS_MAP_ELEMENT_SUCCESS_SAVE'), 'noparentload' => true, 'elementId' => $elementId);
        $return = array_merge($saveReturnArray, $returnArray);

        return new JsonResponse($return);
    }

    /**
     * text Element History
     *
     * @param Request $request element Id
     *
     * @return JSON text element history
     */
    public function textElementHistoryAction(Request $request)
    {

        $clubId = $this->container->get('club')->get('id');
        $elementId = $request->get('elementId');
        $returnArray['textElementHistory'] = $this->container->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentTextElement')->getHistory($elementId, $clubId);

        return new JsonResponse($returnArray);
    }

    /**
     * revision Update Text Element
     *
     * @param Request $request textelement/version
     *
     * @return JSON text element data
     */
    public function revisionUpdateTextElementAction(Request $request)
    {

        $textElementId = $request->get('textelement');
        $textVersionId = $request->get('version');
        $this->container->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentTextElement')->updateVersion($textVersionId, $textElementId);
        $flash = $this->get('translator')->trans('TEXT_VERSION_UPDATED');

        return new JsonResponse(array('status' => 'SUCCESS', 'noparentload' => true, 'flash' => $this->get('translator')->trans($flash)));
    }

    /**
     * Function to get all calendar events .
     *
     * @return JSON Event details array
     */
    public function getCalendarElementDetailsAction($elementId, $pageId)
    {
        $startDate = date('Y-m-d H:i:s');
        $selectedAreasandCategories = $this->container->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->getCalendarElementDetails($elementId);
        $areasAndcategories = $this->getAllcategoriesAndAreas($selectedAreasandCategories);
        $filterClass = new Calenderfilter($this->container);
        $filterClass->filterArray = $areasAndcategories;
        $filterCondition = $filterClass->generateFilter();
        $eventDetails = $eventDetailsWithRecurrence = array();
        //initialize the calendar events class and get the qry.
        $calenderEventsObj = new CalenderEvents($this->container, '', '', $startDate);
        $calenderEventsObj->setColumns();
        $calenderEventsObj->setFrom();
        $calenderEventsObj->setCondition();
        $where = '1';
        if (count($filterCondition) > 0) {
            $where = implode(' AND ', $filterCondition);
        }
        $calenderEventsObj->addCondition($where);
        $calenderEventsObj->setGroupBy('CD.id');
        $calenderEventsObj->addOrderBy('eventSortField ASC');
        $qry = $calenderEventsObj->getResult();
        file_put_contents('query.txt', $qry);
        //execute the qry and get the results
        $calendarPdo = new CalendarPdo($this->container);
        $eventDetails = $calendarPdo->executeQuery($qry);
        //loop the results through recurr class to get all dates of repeating + non repeating events
        $eventDetailsWithRecurrence = $this->getRecurrenceDetails($eventDetails);
        $finalData = $this->getOptimizedCalendarData($eventDetailsWithRecurrence);
        $returnArray['calendarData'] = $finalData;
        $returnArray['elementId'] = $elementId;
        $returnArray['pageId'] = $pageId;
        $columnWidth = $this->container->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->getPageContainerColumnWidth($pageId, $elementId);
        $returnArray['clubDateFormat'] = ($columnWidth > 3) ? 'EEEE' : 'EE';
        $localeArr = FgSettings::getLocaleDetails();
        $locale = $localeArr[$this->get('contact')->get('default_lang')][0];
        $returnArray['clubLocale'] = $locale;

        return $this->render('WebsiteCMSBundle:PageContentElements:templateCalendarElement.html.twig', $returnArray);
    }

    /**
     * Method to add recurrence periods of repeating events.
     * Here loop each event detail and if it is repeating, add the recurrence between the interval to the array.
     * Also add 'hasEditRights' to the array & Also check whether assigned role is active, if it is not remove it.
     *
     * @param Array $eventDetails event details
     *
     * @return Array recurrence Event details
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
                } else { //case until date is null
                }
                $recurrences = $calendarObj->getRecurrenceAfter($eventDetail['intervalStartDate'], $eventDetail['eventRepeatUntillDate']);
                foreach ($recurrences as $recurrence) {
                    $eventDetail['startDate'] = $recurrence['recurrenceStartDate'];
                    $eventDetail['endDate'] = $recurrence['recurrenceEndDate'];
                    $result[] = $eventDetail;
                }
            } elseif ($eventDetail['eventDetailType'] == "1") { //non-repeating events
                $result[] = $eventDetail;
            } else {

            }
        }

        return $result;
    }

    /**
     * Method to get the optimized result data for showing calendar element preview
     *
     * @param Array $eventDetails event details
     *
     * @return Array optimized calendar details
     */
    public function getOptimizedCalendarData($eventDetails)
    {
        foreach ($eventDetails as $key => $details) {
            $eventDetails[$key]['dateDetails'] = $this->getDateDataForDetailsPage($details['startDate'], $details['endDate'], $details['isAllday']);
            $eventDetails[$key]['startDateTimestamp'] = strtotime($details['startDate']);
            $eventDetails[$key]['endDateTimestamp'] = strtotime($details['endDate']);
        }

        //order it by startDateTimestamp
        // reference http://stackoverflow.com/questions/2699086/sort-multi-dimensional-array-by-value
        usort($eventDetails, function ($a, $b) {
            return $a['startDateTimestamp'] - $b['startDateTimestamp'];
        });

        return array_slice($eventDetails, 0, 5);
    }

    /**
     * Function to get date data details in details page
     *
     * @param Int $startDateVal  start date
     * @param Int $endDateVal    end date
     * @param Int $isAllday      is event a all day event or not
     *
     * @return Array Date of detail page
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
     * Function to get all selected categories and areas
     *
     * @param String $selectedAreasandCategories selected areas and categories
     *
     * @return Array areas and categories array
     */
    private function getAllcategoriesAndAreas($selectedAreasandCategories)
    {
        $club = $this->container->get('club');
        $clubId = $club->get('id');
        $defLang = $club->get('club_default_lang');
        $categoryArray = array();
        $clubArray = array();
        $sharedClubArray = array();
        $areaArray = array();
        $workgroupCatId = $this->container->get('club')->get('club_workgroup_id');
        $teamCatId = $this->container->get('club')->get('club_team_id');
        $allAreaIds = $this->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgRmRole')->getAllActiveRolesIdsOfAClub($this->container, array($teamCatId, $workgroupCatId));
        if ($selectedAreasandCategories['areaIds']) {
            $activeArr = array_intersect(explode(",", $allAreaIds), explode(",", $selectedAreasandCategories['areaIds']));
            if ($activeArr) {
                $areaArray = $this->container->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgRmRole')->getRoleType(implode(",", $activeArr));
            }
        } elseif ($selectedAreasandCategories['isAllArea'] == 1) {
            $allAreaArray = $this->container->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgRmRole')->getRoleType($allAreaIds);
            $clubAreaArray = $this->getClubAreaArray();
            $areaArray = array_merge($allAreaArray, $clubAreaArray);
        } else {

        }
        if ($selectedAreasandCategories['areaClub']) {
            $clubArray = $this->getClubAreaArray();
        }
        if ($selectedAreasandCategories['sharedClub']) {
            $sharedClubArray = $this->getSharedAreaArray($selectedAreasandCategories['sharedClub']);
        }
        if ($selectedAreasandCategories['categoryIds']) {
            $categoryArray = $this->getCategoryArray($selectedAreasandCategories['categoryIds']);
        } elseif ($selectedAreasandCategories['isAllCategory'] == 1) {
            $cat = $this->container->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgEmCalendarCategory')->getCalendarCategories($clubId, $defLang);
            $catArr = array();
            foreach ($cat as $value) {
                $catArr[] = $value['id'];
            }
            if (count($catArr) > 0) {
                $catString = implode(",", $catArr);
                $categoryArray = $this->getCategoryArray($catString);
            }
        } else {

        }
        $areasAndcategories = array_merge($areaArray, $categoryArray, $clubArray, $sharedClubArray);
        // FAIR-2544 Change of area and category selection on calendar and article pages and elements
        if (count($categoryArray) == 0 && count($sharedClubArray) == 0) {
            $areasAndcategories = array(array('id' => 0, 'type' => 'team'), array('id' => 0, 'type' => 'workgroup'), array('id' => 0, 'type' => 'CLUB'), array('id' => 0, 'type' => 'CA'));
        }
        if (count($areaArray) == 0 && count($clubArray) == 0 && count($sharedClubArray) == 0) {
            $areasAndcategories = array(array('id' => 0, 'type' => 'team'), array('id' => 0, 'type' => 'workgroup'), array('id' => 0, 'type' => 'CLUB'), array('id' => 0, 'type' => 'CA'));
        }

        return $areasAndcategories;
    }

    /**
     * Function to get all categories array
     *
     * @param String $category category id comma seprated string
     *
     * @return Array category array
     */
    private function getCategoryArray($category)
    {
        $categoryArray = explode(',', $category);
        $catTypeArray = array();
        foreach ($categoryArray as $keys => $val) {
            $catTypeArray[$keys]['id'] = $val;
            $catTypeArray[$keys]['type'] = 'CA';
        }

        return $catTypeArray;
    }

    /**
     * This function is used to show iframe element add/edit page
     *
     * @param Int $pageId      page id
     * @param Int $boxId       box id in page
     * @param Int $sortOrder   sort order of elements in box
     * @param Int $elementId   element id in box
     *
     * @return Object View Template Render Object
     */
    private function iframeElement($pageId, $boxId, $sortOrder, $elementId = '')
    {
        $club = $this->container->get('club');
        $contact = $this->container->get('contact');
        $returnArray = array();
        $tabs['cmsIframeElementContent'] = array('text' => $this->get('translator')->trans('CMS_TAB_CONTENT'), 'activeClass' => 'active',);
        $tabs['cmsIframeElementLog'] = array('text' => $this->get('translator')->trans('CMS_TAB_LOG'));
        $returnArray['tabs'] = $tabs;
        $returnArray['clubId'] = $club->get('id');
        $returnArray['contactId'] = $contact->get('id');
        $returnArray['defaultlang'] = $club->get('club_default_lang');
        $returnArray['pageId'] = $pageId;
        $returnArray['boxId'] = $boxId;
        $returnArray['elementId'] = $elementId;
        $returnArray['sortOrder'] = $sortOrder;
        if ($returnArray['elementId']) {
            $returnArray['elementDetails'] = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->getElementDetails($returnArray['elementId']);
        }
        $returnArray['clubLanguageArr'] = $club->get('club_languages');
        $returnArray['defaultClubLang'] = $club->get('club_default_lang');

        $returnArray['breadCrumb'] = array('back' => $this->generateUrl('website_cms_page_edit', array('pageId' => $pageId)));
        return $this->render('WebsiteCMSBundle:PageContentElements:iframeElement.html.twig', $returnArray);
    }

    /**
     * Function to save iframe element
     *
     * @param Object $request the request object of this function
     *
     * @return Object JSON Response Object
     */
    public function saveIframeElementAction(Request $request)
    {
        $club = $this->container->get('club');
        $contact = $this->container->get('contact');
        $clubId = $club->get('id');
        $defaultClubLang = $club->get('club_default_lang');
        $contactId = $contact->get('id');
        $saveType = $request->get('saveType');
        $pageId = $request->get('pageId');
        $boxId = $request->get('boxId');
        $sortOrder = $request->get('sortOrder');
        $elId = $request->get('elementId') ? $request->get('elementId') : 'new';
        $status = ($elId == 'new') ? 'added' : 'changed';
        $iframeCode = $request->get('iframeCode');
        $iframeUrl = $request->get('iframeUrl');
        $iframeHeight = $request->get('iframeHeight');

        $data = array('iframeCode' => $iframeCode, 'iframeUrl' => $iframeUrl, 'iframeHeight' => $iframeHeight, 'sortOrder' => $sortOrder, 'pageId' => $pageId);
        $pageTitles = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->getPageTite($pageId);
        $pageTitle = isset($pageTitles[$defaultClubLang]) ? $pageTitles[$defaultClubLang] : ($pageTitles['default'] == 'footer' ? $this->get('translator')->trans('TOP_NAV_CMS_FOOTER') : $this->get('translator')->trans('CMS_SIDEBAR'));
        //update sortorder
        if ($elId === 'new') {
            $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->reOrderSortPosition($boxId, $sortOrder);
        }
        $elementId = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->saveIframeElement($data, $clubId, $boxId, $elId);
        $logArray[] = "('$elementId', '$pageId', 'element', '$status', '', '', now(), $contactId)";
        if ($status == 'added') {
            $logArray[] = "('$elementId', '$pageId', 'page', '$status', '', '$pageTitle', now(), $contactId)";
        }
        //Save Json
        $pageContentObj = new FgPageContent($this->container);
        $pageContentObj->saveJsonContent($pageId);

        $cmsPdo = new CmsPdo($this->container);
        $cmsPdo->saveLog($logArray);
        $return = array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('CMS_CREATE_IFRAME_ELEMENT_SUCCESS'), 'noparentload' => true, 'saveType' => $saveType, 'elementId' => $elementId);

        return new JsonResponse($return);
    }

    /**
     * Function to get text element data for showing it in content area
     *
     * @param int $elementId element id
     * @param int $pageId    page id
     *
     * @return Object View Template Render Object
     */
    public function previewTextElementAction($elementId, $pageId)
    {
        $returnArray = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentTextElement')->getTextElement($elementId, $this->container);
        $returnArray['columnWidth'] = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->getPageContainerColumnWidth($pageId, $elementId);
        $returnArray['textelement']['media'] = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentTextElement')->getTextElementMedia($this->container, $elementId, '', $returnArray['columnWidth']);

        $returnArray['elementId'] = $elementId;
        $returnArray['pageId'] = $pageId;
        $returnArray['club_id'] = $this->container->get('club')->get('id');

        return $this->render('WebsiteCMSBundle:PageContentElements:templateTextElement.html.twig', $returnArray);
    }

    /**
     * Function to get club details
     *
     * @return Array club array with id and type
     */
    private function getClubAreaArray()
    {
        $clubArray = array(0 => array(
                'id' => $this->container->get('club')->get('id'),
                'type' => 'CLUB'
            )
        );

        return $clubArray;
    }

    /**
     * Function to get shared club ids and categories
     *
     * @param String $sharedAreas fed/subFed ids
     *
     * @return Array shared club areas and category array
     */
    private function getSharedAreaArray($sharedAreas)
    {
        $sharedAreaArray = explode(',', $sharedAreas);
        $club = $this->container->get('club');
        $fedId = $club->get('federation_id');
        $fedCat = $this->container->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgEmCalendarCategory')->getCalendarCategoryIds($fedId);
        if (count($sharedAreaArray) == 2) {
            $subFedId = $club->get('sub_federation_id');
            $subFedCat = $this->container->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgEmCalendarCategory')->getCalendarCategoryIds($subFedId);
            $sharedClubArray = array(
                0 => array('id' => $fedId, 'type' => 'FED'),
                1 => array('id' => 'CA_federation', 'type' => 'CA_LEVELS', 'value' => $fedCat),
                2 => array('id' => $subFedId, 'type' => 'SUBFED'),
                3 => array('id' => 'CA_sub_federation', 'type' => 'CA_LEVELS', 'value' => $subFedCat),
            );
        } else {
            if (in_array($fedId, $sharedAreaArray)) {
                $sharedClubArray = array(
                    0 => array('id' => $fedId, 'type' => 'FED'),
                    1 => array('id' => 'CA_federation', 'type' => 'CA_LEVELS', 'value' => $fedCat)
                );
            } else {
                $subFedId = $club->get('sub_federation_id');
                $subFedCat = $this->container->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgEmCalendarCategory')->getCalendarCategoryIds($subFedId);
                $sharedClubArray = array(
                    0 => array('id' => $subFedId, 'type' => 'SUBFED'),
                    1 => array('id' => 'CA_sub_federation', 'type' => 'CA_LEVELS', 'value' => $subFedCat)
                );
            }
        }

        return $sharedClubArray;
    }

    /**
     * Function to get data in filter for handling all areas
     *
     * @return String all roles
     */
    public function getAllAreasForArticleFilter()
    {
        $workgroupCatId = $this->container->get('club')->get('club_workgroup_id');
        $teamCatId = $this->container->get('club')->get('club_team_id');
        $roles = $this->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgRmRole')->getAllActiveRolesOfAClub($this->container, array($teamCatId, $workgroupCatId));
        $allRoles = array_merge(array_keys($roles['teams']), array_keys($roles['workgroups']));

        return implode(',', $allRoles);
    }

    /**
     * function to get supplementary menu element content data
     *
     * @param  int   pageId
     *
     * @return array $navDetails navigation menu details
     */
    public function getSupplementaryElementDataAction($pageId)
    {
        $contactId = $this->container->get('contact')->get('id');
        $club = $this->container->get('club');
        $navId = $this->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgCmsNavigation')->checkPageAssignedToNavigation($pageId);
        $navigationId = (!empty($navId)) ? $navId[0]['id'] : 0;
        $pageType = $this->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->getPageTite($pageId);
        $websiteNavigationDetails = $this->container->get('club')->get('navigationHeirarchy');
        $websiteNavDetails = array();
        if ($contactId) {
            $websiteNavDetails = $websiteNavigationDetails;
            unset($websiteNavDetails['publicPages']);
            unset($websiteNavDetails['publicPageUrl']);
        } else {
            $websiteNavDetails = $websiteNavigationDetails['publicPages'];
        }
        //fair2384
        $superadmin = $this->container->get('contact')->get('isSuperAdmin');
        if (!$superadmin && $contactId) {
            // logged in contact and is not a superadmin
            $contactLang = $this->container->get('contact')->get('corrLang');
        } else {
            //is superadmin or is not logged in
            $contactLang = $club->get('default_lang');
        }
        $navDetails = $this->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgCmsNavigation')->getSupplementaryElementDetails($navigationId, $websiteNavDetails, $contactLang, $this->container);
        $navDetails['pageType'] = $pageType;

        return $this->render('WebsiteCMSBundle:PageContentElements:templateSupplementaryElement.html.twig', $navDetails);
    }

    /**
     * This function is used to show sponsor ad edit page
     *
     * @param Int $pageId      page id
     * @param Int $boxId       box id in page
     * @param Int $sortOrder   sort order of elements in box
     * @param Int $elementId   element id in box
     *
     * @return Object View Template Render Object
     */
    private function getSponsorAdsElement($pageId, $boxId, $sortOrder, $elementId = '')
    {
        $club = $this->container->get('club');
        $clubId = $club->get('id');
        $contactId = $this->container->get('contact')->get('id');
        $defLang = $club->get('club_default_lang');
        $em = $this->getDoctrine()->getManager();
        $return['defLang'] = $defLang;
        $return['clubId'] = $clubId;
        $return['contactId'] = $contactId;
        $tabs['cmsSponsorElementContent'] = array('text' => $this->get('translator')->trans('CMS_TAB_CONTENT'), 'activeClass' => 'active',);
        $tabs['cmsSponsorElementLog'] = array('text' => $this->get('translator')->trans('CMS_TAB_LOG'));
        $return['tabs'] = $tabs;
        $return['pageId'] = $pageId;
        $return['boxId'] = $boxId;
        $return['elementId'] = ($elementId) ? $elementId : 'new';
        $return['sortOrder'] = $sortOrder;
        $objSponsorPdo = new SponsorPdo($this->container);
        $return['sponsorServices'] = $objSponsorPdo->getSponsorsServices($clubId, $defLang);
        $return['sponsorAdAreas'] = $this->getAdAreasDetails($clubId);
        $backLink = $this->generateUrl('website_cms_page_edit', array('pageId' => $pageId));
        $return['backLink'] = $backLink;
        $return['breadCrumb'] = array('back' => $backLink);
        $sponsorSavedData = array();
        if ($return['elementId'] != 'new') {
            $sponsorSavedData = $em->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->getSponsorAdElementData($return['elementId']);
        }
        $return['savedData'] = $sponsorSavedData;

        return $this->render('WebsiteCMSBundle:PageContentElements:sponsorAdsElement.html.twig', $return);
    }

    /**
     * Method to get Ad areas details
     *
     * @param int    $clubId current clubId
     *
     * @return array $adAreas
     */
    private function getAdAreasDetails($clubId)
    {
        $em = $this->getDoctrine()->getManager();
        $adAreas = $em->getRepository('CommonUtilityBundle:FgSmAdArea')->getAdAreas($clubId);
        if (count($adAreas) > 0) {
            for ($i = 0; $i < count($adAreas); $i++) {
                $adAreas[$i]['adTitle'] = $adAreas[$i]['isSystem'] == 1 ? $this->get('translator')->trans('SM_AD_AREA_GENERAL') : $adAreas[$i]['adTitle'];
            }
        }

        return $adAreas;
    }

    /**
     * This function returns the form element array
     *
     * @param Int $pageId      page id
     * @param Int $boxId       box id in page
     * @param Int $sortOrder   sort order of elements in box
     * @param Int $elementId   element id in box
     *
     * @return Object View Template Render Object
     */
    private function getFormElementTemplatePopup($pageId, $boxId, $sortOrder, $elementId)
    {
        $club = $this->container->get('club');
        $clubId = $club->get('id');

        $returnArray['pageId'] = $pageId;
        $returnArray['boxId'] = $boxId;
        $returnArray['sortOrder'] = $sortOrder;
        $returnArray['elementId'] = $elementId;
        $returnArray['formElements'] = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsForms')->getExistingForms('form_field', $clubId);

        return new JsonResponse($returnArray);
    }

    /**
     * Function to save sponsor ad element
     *
     * @param Object $request Request object
     *
     * @return Object View Template Render Object
     */
    public function saveSponsorAdElementAction(Request $request)
    {

        $clubId = $this->container->get('club')->get('id');
        $contactId = $this->container->get('contact')->get('id');
        $defaultClubLang = $this->container->get('club')->get('club_default_lang');
        $elementArr = $request->get('param');
        $pageId = $elementArr['pageId'];
        $postElementId = $elementArr['elementId'];
        $status = ($postElementId == 'new') ? 'added' : 'changed';
        $pageTitles = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->getPageTite($pageId);
        $pageTitle = isset($pageTitles[$defaultClubLang]) ? $pageTitles[$defaultClubLang] : ($pageTitles['default'] == 'footer' ? $this->get('translator')->trans('TOP_NAV_CMS_FOOTER') : $this->get('translator')->trans('CMS_SIDEBAR'));
        if ($postElementId === 'new') {
            $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->reOrderSortPosition($elementArr['boxId'], $elementArr['sortOrder']);
        }

        $elementId = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->saveSponsorAdElement($elementArr, $clubId);
        $logArray[] = "('$elementId', '$pageId', 'element','$status', '', '', now(), $contactId)";
        if ($status == 'added') {
            $logArray[] = "('$elementId', '$pageId', 'page', '$status', '', '$pageTitle', now(), $contactId)";
        }
        $cmsPdo = new CmsPdo($this->container);
        $cmsPdo->saveLog($logArray);
        //Save Json
        $pageContentObj = new FgPageContent($this->container);
        $pageContentObj->saveJsonContent($pageId);
        $saveType = $elementArr['saveType'];
        $saveReturnArray = ($saveType == 'save') ? array('noparentload' => true) : array('sync' => 1, 'redirect' => $this->generateUrl('website_cms_page_edit', array('pageId' => $pageId)));
        $returnArray = array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('CMS_CREATE_SPONSOR_AD_ELEMENT_SUCCESS'), 'noparentload' => true, 'elementId' => $elementId);
        $return = array_merge($saveReturnArray, $returnArray);

        return new JsonResponse($return);
    }

    /**
     * Function to get sponsor ad element details .

     * @param int $elementId element id
     * @param int $pageId    page id
     *
     * @return Object View Template Render Object
     */
    public function getSponsorAdElementDetailsAction($elementId, $pageId)
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
        $sponsorAds['view'] = 'page';

        return $this->render('WebsiteCMSBundle:PageContentElements:templateSponsorElement.html.twig', array('sponsorAds' => $sponsorAds));
    }

    /**
     * This function is used to preview the contact table element
     *
     * @return Object View Template Render Object
     */
    public function getContactTableElementAction()
    {
        return $this->render('WebsiteCMSBundle:PageContentElements:contactTableElementPreview.html.twig');
    }
}
