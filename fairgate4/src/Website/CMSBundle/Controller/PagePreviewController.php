<?php

/**
 * PagePreviewController.
 *
 * @package 	Website
 * @subpackage 	CMSBundle
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 *
 */
namespace Website\CMSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Common\UtilityBundle\Util\FgUtility;
use Website\CMSBundle\Util\FgWebsite;
use Symfony\Component\HttpFoundation\Request;

class PagePreviewController extends Controller
{

    /**
     * This function is used to show page preview
     *
     * @param type $pageId Page id
     *
     * @return object View Template Render Object
     */
    public function displayPagePreviewAction(Request $request, $pageId)
    {
        $em = $this->getDoctrine()->getManager();
        $clubId = $this->get('club')->get('id');
        $contactId = $this->get('contact')->get('id');
        $pageObject = $em->getRepository('CommonUtilityBundle:FgCmsPage')->find($pageId);
        if (!$pageObject) {
            return $this->render('WebsiteCMSBundle:Website:errorPagePreview.html.twig');
        }

        $pageDetails = $em->getRepository('CommonUtilityBundle:FgCmsPage')->getPageData($pageId);
        $backgroundPath = FgUtility::getUploadFilePath($clubId, 'cms_background_image', false, false);
        $cssPath = FgUtility::getUploadFilePath($clubId, 'cms_themecss', false, false);
        $colorSchemeClubId = $this->get('club')->get('publicConfig')['colorSchemeClubId'];
        $colorCssPath = FgUtility::getUploadFilePath($colorSchemeClubId, 'cms_themecss', false, false);

        $returnArray = array('menu' => null, 'cssPath' => $cssPath, 'colorCssPath' => $colorCssPath, 'isHeader' => false,
            'isNavigation' => false, 'backendPreview' => 1, 'clubId' => $clubId);
        $returnArray['pageDetails'][0] = $pageDetails;
        $returnArray['backgroundPath'] = $backgroundPath;
        $returnArray['mainPageId'] = $pageDetails['id'];
        $returnArray['pagePreview'] = ($pageDetails['type'] == 'footer') ? 'Footer' : 'Page';
        $returnArray['clubDefaultLang'] = $this->container->get('club')->get('club_default_lang');
        $returnArray['contactLang'] = $this->container->get('club')->get('default_lang');
        $returnArray['submitButtonTemplate'] = $this->container->get('cms.themes')->getViewPage('formSubmitButtonTemplate');
        $returnArray['uploadPath'] = $this->getPortraitUploadPath();
        $returnArray['formCaptchaTemplate'] = $this->container->get('cms.themes')->getViewPage('formCaptchaTemplate');
        $queryParam = $request->query->all();
        $websiteObj = new FgWebsite($this->container);
        $navObj = $em->getRepository('CommonUtilityBundle:FgCmsNavigation')->findOneBy(array('page' => $pageDetails['id']));
        $returnArray['currentNavigationId'] = $navObj->getId();
        switch ($pageDetails['type']) {
            case 'article':
                $returnArray['pagecontentData'] = $websiteObj->getPageDetails($pageDetails['id'], false, false);
                $returnArray['pageData'] = $websiteObj->getOnPageLoadElementData($pageDetails['id'],$returnArray['currentNavigationId'],$returnArray['pagecontentData']['pageElementsArray']);
                
                return $this->forward("WebsiteCMSBundle:Article:articlelist", $queryParam, array('returnArray' => $returnArray));
            case 'gallery':
                $returnArray['pagecontentData'] = $websiteObj->getPageDetails($pageDetails['id'], false, false);
                $returnArray['pageData'] = $websiteObj->getOnPageLoadElementData($pageDetails['id'],$returnArray['currentNavigationId'],$returnArray['pagecontentData']['pageElementsArray']);

                return $this->forward('WebsiteCMSBundle:Gallery:gallerySpecialPage', $queryParam, array('returnArray' => $returnArray));
            case 'calendar':
                $queryParam = $request->query->all();
                $returnArray['pagecontentData'] = $websiteObj->getPageDetails($pageDetails['id'], false, false);
                $returnArray['pageData'] = $websiteObj->getOnPageLoadElementData($pageDetails['id'],$returnArray['currentNavigationId'],$returnArray['pagecontentData']['pageElementsArray']);

                return $this->forward('WebsiteCMSBundle:Calendar:calendarSpecialPage', $queryParam, array('returnArray' => $returnArray));
            default:
                $websiteObj = new FgWebsite($this->container);
                $getPageContentDetails = ($pageDetails['type'] == 'footer') ? $websiteObj->getPageDetails($pageDetails['id'], true, false) : $websiteObj->getPageDetails($pageDetails['id'], false);
                $returnArray['pagecontentData'] = $getPageContentDetails;
                $returnArray['pageData'] = $websiteObj->getOnPageLoadElementData($pageDetails['id'],$returnArray['currentNavigationId'],$returnArray['pagecontentData']['pageElementsArray']);

                return $this->render('WebsiteCMSBundle:PagePreview:displayPageContentPreview.html.twig', $returnArray);
        }
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
}
