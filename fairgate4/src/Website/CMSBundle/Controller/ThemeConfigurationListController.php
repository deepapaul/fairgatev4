<?php

/**
 * ThemeConfigurationListController.
 */
namespace Website\CMSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Website\CMSBundle\Util\FgCmsTheme;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * ThemeConfigurationListController
 * 
 * This controller is used for displaying theme configuration list
 * 
 * @package 	Website
 * @subpackage 	CMSBundle
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 * 
 */
class ThemeConfigurationListController extends Controller
{

    /**
     * Function to list configurations
     * 
     * @return object View Template Render Object
     */
    public function configurationListingsAction()
    {
        $em = $this->getDoctrine()->getManager();
        $clubId = $this->container->get('club')->get('id');
        $themeConfigs = $em->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->getThemeConfigurations($clubId);

        return $this->render('WebsiteCMSBundle:ListConfiguration:configurationsListing.html.twig', array('breadCrumb' => array(), 'themeConfigs' => $themeConfigs));
    }

    /**
     * Function to duplicate configuration
     * 
     * @param Request $request Request object
     * 
     * @return object JSON Response Object
     */
    public function duplicateConfigurationAction(Request $request)
    {
        $configId = $request->get('configId');
        $em = $this->getDoctrine()->getManager();
        $clubId = $this->container->get('club')->get('id');
        $newConfigId = $em->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->duplicateThemeConfig($configId, $this->container);
        $configDetails = $em->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->getThemeConfigurations($clubId, $newConfigId);
        //To create dynamic theme css
        $cmsTheme = new FgCmsTheme($this->container);
        $cmsTheme->createCss($newConfigId, 'theme');
        $return = array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('CMS_DUPLICATE_CONFIG_SUCCESS'), 'noparentload' => true, 'details' => $configDetails[0]);

        return new JsonResponse($return);
    }

    /**
     * Function to delete theme configuration
     * 
     * @param Request $request Request object
     * 
     * @return object JSON Response Object
     */
    public function deleteConfigurationAction(Request $request)
    {
        $configId = $request->get('configId');
        $em = $this->getDoctrine()->getManager();
        $flag = $em->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->deleteThemeConfig($configId);
        if ($flag) {
            $request->getSession()->remove('themePreviewConfigId');
            $return = array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('CMS_DELETE_CONFIG_SUCCESS'), 'noparentload' => true, 'configId' => $configId);
        } else {
            $return = array('status' => 'ERROR', 'flash' => $this->get('translator')->trans('CMS_DELETE_CONFIG_FAILED'), 'noparentload' => true,);
        }

        //To delete dynamic theme css
        $cmsTheme = new FgCmsTheme($this->container);
        $cmsTheme->removeCssFile($configId, 'theme');

        return new JsonResponse($return);
    }

    /**
     * This function is used to activate a theme configuration.
     * 
     * @param Request $request Request object
     * 
     * @return object JSON Response Object
     */
    public function activateConfigurationAction(Request $request)
    {
        $configId = $request->get('configId');
        $em = $this->getDoctrine()->getManager();
        $clubId = $this->container->get('club')->get('id');
        $flag = $em->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->activateThemeConfiguration($configId, $clubId, $this->container->get('club'));
        if($flag)
            $return = array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('CMS_ACTIVATE_CONFIG_SUCCESS'), 'noparentload' => true, 'configId' => $configId);
        else
           $return = array('status' => 'ERROR', 'flash' => $this->get('translator')->trans('CMS_ACTIVATE_CONFIG_FAILED'),'noparentload' => true,);
         
        return new JsonResponse($return);
    }

    /**
     * Function to show delete theme configuration popup
     *  
     * @param Request $request request object
     * 
     * @return object View Template Render Object
     */
    public function deleteConfigurationPopupAction(Request $request)
    {
        $popupTitle = $this->get('translator')->trans('CMS_DELETE_THEME_CONFIG_POPUP_TITLE');
        $popupText = $this->get('translator')->trans('CMS_DELETE_THEME_CONFIG_CONFIRM_MESSAGE');
        $pageCount = '';
        $pageArray = array();
        $data['configId'] = $request->get('configId');
        $return = array("title" => $popupTitle, 'text' => $popupText, 'pageArray' => $pageArray, 'data' => $data, 'pageCount' => $pageCount, 'buttonText' => $this->get('translator')->trans('DELETE_BUTTON_TEXT'), 'type' => 'delete');

        return $this->render('WebsiteCMSBundle:ListConfiguration:ConfirmationPopup.html.twig', $return);
    }
}
