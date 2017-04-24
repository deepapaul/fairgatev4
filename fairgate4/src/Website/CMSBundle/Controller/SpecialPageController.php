<?php

/**
 * SpecialPageController.
 *
 * @package 	Website
 * @subpackage 	CMSBundle
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 *
 */
namespace Website\CMSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Website\CMSBundle\Util\FgPageElement;
use Website\CMSBundle\Util\FgPageContainerDetails;

class SpecialPageController extends Controller
{

    /**
     * This function is used to show gallery create popup page
     */
    public function createGalleriesPageAction()
    {
        return new JsonResponse(0);
    }

    /**
     * This function is used to save special page details
     *
     * @param Request $request Request Object
     *
     * @return object JSON Response Object
     */
    public function createGalleriesPageSaveAction(Request $request)
    {
        $galleryRoleArray = $request->get('galleryRoleArray');
        $title = $request->get('title');
        $navId = $request->get('navId');
        $isAllGalleries = ($galleryRoleArray[0] == 'ALL_GALLERIES') ? 1 : 0;
        $data = array('hideTitle' => 0, 'title' => $title, 'type' => 'gallery', 'galleryRoleArray' => $galleryRoleArray, 'isAllGalleries' => $isAllGalleries,);
        $pageId = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->createPage($this->container, $data);
        $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageGallery')->saveData($pageId, $data);
        if ($navId) {
            $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsNavigation')->savePageToNavigation($this->container, $navId, $pageId, 'gallery');
        }
        $this->saveCommonFooter();
        $return = array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('CMS_CREATE_GALLERY_SPECIALPAGE_SUCCESS'), 'noparentload' => true, 'pageId' => $pageId);

        return new JsonResponse($return);
    }

    /**
     * Function to save footer if no footer is saved in club
     */
    private function saveCommonFooter()
    {
        $footerObj = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->findBy(array('type' => 'footer', 'club' => $this->container->get('club')->get('id')));
        if (!$footerObj) {
            $pageContainerObj = new FgPageContainerDetails($this->container);
            $pageContainerObj->setNewPage('footer', 'footer');
        }
    }

    /**
     * This function is used to get gallery page details for edit
     *
     * @param Request $request Request object
     *
     * @return object JSON Response Object
     */
    public function galleriesPageEditAction(Request $request)
    {
        $pageId = $request->get('pageId');
        $pageTitles = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->getPageTite($pageId);
        $roles = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageGallery')->getGalleryRoles($pageId);
        $pageData = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->getPageData($pageId);

        return new JsonResponse(array('pageTitles' => $pageTitles, 'roles' => $roles, 'page' => $pageData));
    }

    /**
     * This function is used to update gallery special page details
     *
     * @param Request $request Request object
     *
     * @return object JSON Response Object
     */
    public function galleriesPageEditSaveAction(Request $request)
    {
        $pageId = $request->get('pageId');
        $titleArray = $request->get('titleArray');
        $galleryRoleArray = $request->get('galleryRoleArray');
        $isAllGalleries = ($galleryRoleArray[0] == 'ALL_GALLERIES') ? 1 : 0;
        $data = array('title' => $titleArray, 'isAllGalleries' => $isAllGalleries, 'galleryRoleArray' => $galleryRoleArray);
        $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->savePageDetails($pageId, $this->container, $data);
        $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageGallery')->saveData($pageId, $data);
        $pageData = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->getPageData($pageId);
        $return = array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('CMS_UPDATE_GALLERY_SPECIALPAGE_SUCCESS'), 'noparentload' => true, 'page' => $pageData);

        return new JsonResponse($return);
    }

    /**
     * This function is used to render special page create popup
     *
     * @param Request $request Request object
     *
     * @return object View Template Render Object
     */
    public function specialPageCreatePopupAction(Request $request)
    {
        $pageType = $request->get('pageType');
        $clubId = $this->container->get('club')->get('id');
        $data['clubType'] = $this->container->get('club')->get('type');
        $data['pageType'] = $pageType;
        if ($pageType == 'gallery') {
            $data['title'] = $this->get('translator')->trans('CMS_SHOW_GALLERIES');
            $pageElementObj = new FgPageElement($this->container);
            $data['allGallery'] = $pageElementObj->getGalleryList();
        } else {
            $clubDefaultLanguage = $this->container->get('club')->get('club_default_lang');
            $data['fedId'] = $this->container->get('club')->get('federation_id');
            $data['subFedId'] = $this->container->get('club')->get('sub_federation_id');
            $data['categories'] = ($pageType == 'article') ? $this->container->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgCmsArticleCategory')->getArticleCategories($clubId, $clubDefaultLanguage) : $this->container->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgEmCalendarCategory')->getCalendarCategories($clubId, $clubDefaultLanguage);
            $pageElement = new FgPageElement($this->container);
            $data['areas'] = $pageElement->getAllAreasForArticleAndCalendar();
            $data['title'] = ($pageType == 'article') ? $this->get('translator')->trans('CMS_SHOW_ARTICLES') : $this->get('translator')->trans('CMS_SHOW_CALENDARS');
            $data['fedLowerLevelCount'] = ($data['clubType'] != 'federation' && $data['clubType'] != 'standard_club') ? (($data['pageType'] == 'article') ? $this->container->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgCmsArticle')->getLowerLevelSharedArticleCount($data['fedId']) : $this->container->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgEmCalendar')->checkForSharedEvents($data['fedId'])) : 0;
            $data['subFedLowerLevelCount'] = ($data['clubType'] == 'sub_federation_club') ? (($data['pageType'] == 'article') ? $this->container->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgCmsArticle')->getLowerLevelSharedArticleCount($data['subFedId']) : $this->container->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgEmCalendar')->checkForSharedEvents($data['subFedId'])) : 0;
        }

        return $this->render('WebsiteCMSBundle:SpecialPages:cmsSpecialPageCreatePopup.html.twig', $data);
    }

    /**
     * This function is used to save special page details
     *
     * @param object $request \Symfony\Component\HttpFoundation\Request
     *
     * @return object JSON Response Object
     */
    public function articleAndCalendarPageSaveAction(Request $request)
    {
        $data = $request->request->all();
        $navId = $data['navId'];
        $data['hideTitle'] = 0;
        $pageId = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->createPage($this->container, $data);
        if ($navId) {
            $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsNavigation')->savePageToNavigation($this->container, $navId, $pageId, $data['type']);
        }
        $flashMessage = ($data['type'] == 'article') ? $this->get('translator')->trans('CMS_CREATE_ARTICLE_SPECIALPAGE_SUCCESS') : $this->get('translator')->trans('CMS_CREATE_CALENDAR_SPECIALPAGE_SUCCESS');
        $pageData = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->getPageData($pageId);
        $this->saveCommonFooter();
        $return = array('status' => 'SUCCESS', 'flash' => $flashMessage, 'noparentload' => true, 'pageId' => $pageId, 'pageType' => $data['type'], 'page' => $pageData);

        return new JsonResponse($return);
    }

    /**
     * This function is used to edit article and calendar special page
     *
     * @param object $request \Symfony\Component\HttpFoundation\Request
     *
     * @return object JSON Response Object
     */
    public function articleAndCalendarPageEditAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $data['pageId'] = $request->get('pageId');
        $data['pageTitles'] = $em->getRepository('CommonUtilityBundle:FgCmsPage')->getPageTite($data['pageId']);
        $data['clubId'] = $this->container->get('club')->get('id');
        $data['pageType'] = $request->get('pageType');
        $clubDefaultLanguage = $this->container->get('club')->get('club_default_lang');
        $data['fedId'] = $this->container->get('club')->get('federation_id');
        $data['subFedId'] = $this->container->get('club')->get('sub_federation_id');
        $data['categories'] = ($data['pageType'] == 'article') ? $em->getRepository('CommonUtilityBundle:FgCmsArticleCategory')->getArticleCategories($data['clubId'], $clubDefaultLanguage) : $em->getRepository('CommonUtilityBundle:FgEmCalendarCategory')->getCalendarCategories($data['clubId'], $clubDefaultLanguage);
        $pageElement = new FgPageElement($this->container);
        $data['areas'] = $pageElement->getAllAreasForArticleAndCalendar();
        $data['existingDatas'] = $em->getRepository('CommonUtilityBundle:FgCmsPage')->getCalendarAndArticlePageData($data['pageId'], $data['pageType']);
        $data['clubType'] = $this->container->get('club')->get('type');
        $data['fedLowerLevelCount'] = ($data['clubType'] != 'federation' && $data['clubType'] != 'standard_club') ? (($data['pageType'] == 'article') ? $em->getRepository('CommonUtilityBundle:FgCmsArticle')->getLowerLevelSharedArticleCount($data['fedId']) : $em->getRepository('CommonUtilityBundle:FgEmCalendar')->checkForSharedEvents($data['fedId'])) : 0;
        $data['subFedLowerLevelCount'] = ($data['clubType'] == 'sub_federation_club') ? (($data['pageType'] == 'article') ? $em->getRepository('CommonUtilityBundle:FgCmsArticle')->getLowerLevelSharedArticleCount($data['subFedId']) : $em->getRepository('CommonUtilityBundle:FgEmCalendar')->checkForSharedEvents($data['subFedId'])) : 0;

        return new JsonResponse($data);
    }

    /**
     * This function is used to save article and calendar special page details after edit
     *
     * @param object $request \Symfony\Component\HttpFoundation\Request
     *
     * @return object JSON Response Object
     */
    public function articleAndCalendarPageEditSaveAction(Request $request)
    {
        $data = $request->request->all();
        $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->savePageDetails($data['pageId'], $this->container, $data);
        $flashMessage = ($data['type'] == 'article') ? $this->get('translator')->trans('CMS_UPDATE_ARTICLE_SPECIALPAGE_SUCCESS') : $this->get('translator')->trans('CMS_UPDATE_CALENDAR_SPECIALPAGE_SUCCESS');
        $pageData = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->getPageData($data['pageId']);
        $return = array('status' => 'SUCCESS', 'flash' => $flashMessage, 'noparentload' => true, 'page' => $pageData);

        return new JsonResponse($return);
    }
}
