<?php

/**
 * NavigationPointsController.
 * 
 */
namespace Website\CMSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * NavigationPointsController
 *
 * This controller is used for handling navigation point list,edit,delete functionalities.
 * 
 * @package 	Website
 * @subpackage 	CMSBundle
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 */
class NavigationPointsController extends Controller
{

    /**
     * This function is used to list all navigation points.
     * 
     * @return Object View Template Render Object
     */
    public function listNavigationPointsAction()
    {
        $clubObj = $this->get('club');
        $clubDefaultLang = $clubObj->get('club_default_lang');
        $clubLanguages = $clubObj->get('club_languages');
        $cmsNavPageTitle = 'CMS_MANAGE_MENU_BROWSER_PAGE_TITLE';
        $pageTitle = 'CMS_MANAGE_MENU_TITLE';
        $breadCrumbData = array('back' => $this->generateUrl('website_cms_listpages'));

        $dataSet = array('clubLanguages' => $clubLanguages, 'clubDefaultLang' => $clubDefaultLang, 'cmsNavPageTitle' => $cmsNavPageTitle, 'title' => $pageTitle, 'breadCrumb' => $breadCrumbData);

        return $this->render('WebsiteCMSBundle:NavigationPoints:listNavigations.html.twig', $dataSet);
    }

    /**
     * This function is used to get details of navigation points
     * 
     * @return Object JSON Response Object
     */
    public function getNavigationPointDetailsAction(Request $request)
    {
        $clubId = $this->get('club')->get('id');
        $contactLang = $this->get('club')->get('default_lang');
        $isaddtional = $request->get('isAdditional');
        $result = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsNavigation')->getNavigationDetails($this->container, $this->get('club'), $clubId, $contactLang, false, false, 1, $isaddtional);

        return new JsonResponse($result);
    }

    /**
     * This function is used to update the navigation point details
     * 
     * @param Object $request The request object
     * 
     * @return Object JSON Response Object
     */
    public function saveNavigationPointDetailsAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $isAdditional = $request->get('isAdditional');
            $dataArr = json_decode($request->get('saveData'), true);
            if (count($dataArr) > 0) {
                $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsNavigation')->saveNavigationDetails($this->container, $dataArr, $isAdditional);
            }
            $return = array('status' => 'SUCCESS', 'noparentload' => true, 'flash' => $this->get('translator')->trans('NAVIGATION_POINTS_UPDATED'));
        } else {
            $return = array('status' => 'FAILURE', 'noparentload' => true, 'flash' => $this->get('translator')->trans('NAVIGATION_POINTS_UPDATE_FAILED'), 'errorArray' => array('error' => true));
        }

        return new JsonResponse($return);
    }

    /**
     * This function is used to list additional navigation points.
     * 
     * @return Object View Template Render Object
     */
    public function listAdditionalNavigationAction()
    {
        $clubObj = $this->get('club');
        $clubDefaultLang = $clubObj->get('club_default_lang');
        $clubLanguages = $clubObj->get('club_languages');
        $cmsNavPageTitle = 'CMS_MANAGE_MENU_BROWSER_PAGE_TITLE';
        $pageTitle = 'CMS_NAVIGATION_SIDEBAR_ADDITIONAL_MENU';
        $breadCrumbData = array('back' => $this->generateUrl('website_cms_listpages'));

        $dataSet = array('clubLanguages' => $clubLanguages, 'clubDefaultLang' => $clubDefaultLang, 'cmsNavPageTitle' => $cmsNavPageTitle, 'title' => $pageTitle, 'breadCrumb' => $breadCrumbData, 'isAdditional' => 1);

        return $this->render('WebsiteCMSBundle:NavigationPoints:listNavigations.html.twig', $dataSet);
    }
}
