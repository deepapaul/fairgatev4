<?php

/**
 * ArticleController.
 */
namespace Website\CMSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Common\UtilityBundle\Util\FgUtility;
use Website\CMSBundle\Util\FgWebsite;
use Common\UtilityBundle\Repository\Pdo\CmsPdo;

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
class GalleryController extends Controller
{

    /**
     * Function to show gallery special page
     * 
     * @param object $request Request Object
     * 
     * @return Object View Template Render Object
     */
    public function gallerySpecialPageAction(Request $request)
    {

        $requestArray = $request->query->all();
        $returnArray = $requestArray['returnArray'];
        $pageId = $returnArray['mainPageId'];
        $websiteObj = new FgWebsite($this->container);
        $returnArray['currTitle'] = ($returnArray['pageTitle'] != '') ? $returnArray['pageTitle'] : $websiteObj->getPageTitle($pageId);
        $returnArray['pageId'] = $pageId;

        $returnArray['galleryUploadPath'] = FgUtility::getUploadFilePath($this->container->get('club')->get('id'), 'gallery');

        $viewPage = $this->container->get('cms.themes')->getViewPage('gallerySpecialPage');
            
        return $this->render($viewPage, $returnArray);
    }

    /**
     * Function to get gallery details
     */
    public function gallerySpecialPageAjaxAction()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $club = $this->container->get('club'); // Club object
        $clubId = $club->get('id');
        $pageId = $request->get('pageId');
        $galleryRoles = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageGallery')->getRolesForPreview($pageId);
        if (count($galleryRoles) == 0) {
            $galleryRoles = 'ALL';
        }
        $cmsPdo = new CmsPdo($this->container);
        $contactId = $this->container->get('contact')->get('id');
        $superadmin = $this->container->get('contact')->get('isSuperAdmin');
        $scope = ($contactId > 0) ? false : true;
        if (!$superadmin && $contactId) {
            // logged in contact and is not a superadmin
            $contactLang = $this->container->get('contact')->get('corrLang');
        } else {
            //is superadmin or is not logged in
            $contactLang = $this->container->get('club')->get('default_lang');
        }
        $getGallery = $cmsPdo->getGalleryDetails($clubId, $galleryRoles, $scope, $contactLang);

        return new JsonResponse($getGallery);
    }
}
