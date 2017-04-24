<?php

/**
 * PageContentController.
 *
 */
namespace Website\CMSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Website\CMSBundle\Util\FgPageContainerDetails;
use Symfony\Component\HttpFoundation\JsonResponse;
use Website\CMSBundle\Util\FgPageContent;
use Website\CMSBundle\Util\FgCmsPortraitContainer;

/**
 * PageContentController
 *
 * This controller is used for displaying the website CMS content area
 *
 * @package 	Website
 * @subpackage 	CMSBundle
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 */
class PageContentController extends Controller
{

    /**
     * This function is used to display index.
     *
     * @return Object View Template Render Object
     */
    public function savePageContainerAction()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $postArray = json_decode($request->get('postArr'), true);
        $pageDetails = json_decode($request->get('pageDetails'), true);
        $pageContainerObj = new FgPageContainerDetails($this->container);
        $pageContentArray = $postArray;

        $pageId = array_keys($pageContentArray['page'])[0];
        $session = $this->container->get('session');
        $pageObject = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->find($pageId);

        if ($pageObject->getContentUpdateTime()->format('Y-m-d H:i:s') != $session->get("lastCmsPageEditTime_" + $pageId)) {
            $returnDetails['redirect'] = $this->generateUrl('website_cms_page_edit', array('pageId' => $pageId));
            $returnDetails['status'] = 'ERROR';
            $returnDetails['flash'] = $this->container->get('translator')->trans('CMS_PAGE_NOT_SAVED');
        } else {
            $returnDetails = $pageContainerObj->commonSavePageContentDetails($pageContentArray, $pageDetails);
        }

        return new JsonResponse($returnDetails);
    }
    
    /**
     * This function is move elements of a box to clipboard and delete that box
     *
     * @return JsonResponse
     */
    public function saveBoxDeleteAction()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        //array contains the elements of box to move to clipboard        
        $postArray = json_decode($request->get('postArr'), true);
        $pageDetails = array();
        //array contains details of box to delete
        $boxDetails = json_decode($request->get('boxDetails'), true);
        $pageContainerObj = new FgPageContainerDetails($this->container);
        $pageContentArray = $postArray;

        $pageId = array_keys($pageContentArray['page'])[0];
        $session = $this->container->get('session');
        $pageObject = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->find($pageId);

        if ($pageObject->getContentUpdateTime()->format('Y-m-d H:i:s') != $session->get("lastCmsPageEditTime_" + $pageId)) {
            $returnDetails['redirect'] = $this->generateUrl('website_cms_page_edit', array('pageId' => $pageId));
            $returnDetails['status'] = 'ERROR';
            $returnDetails['flash'] = $this->container->get('translator')->trans('CMS_PAGE_NOT_SAVED');
        } else {
            //move elements to clipboard
            $returnMoveElements = $pageContainerObj->commonSavePageContentDetails($pageContentArray, $pageDetails);
            //delete box
            $returnDetails = $pageContainerObj->commonSavePageContentDetails($boxDetails, $pageDetails);
            $returnDetails['type'] = $returnMoveElements['type'];
            $returnDetails['clipboardData'] = $returnMoveElements['clipboardData'];
        }

        return new JsonResponse($returnDetails);
    }

    /**
     * To exclude/include side columns  inside a page
     *
     * @return JSON Json response object
     */
    public function excludeSidecolumnAction()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $pageId = (int) $request->get('cmsPageId');
        $type = $request->get('cmsSidebarType');
        $area = $request->get('cmsSidebarArea');
        $action = $request->get('cmsPageSidebarAction');
        $pageContainerObj = new FgPageContainerDetails($this->container);
        $pageContentObj = new FgPageContent($this->container);
        $pageContentDetails = $pageContentObj->getContentElementData($pageId);
        if ($action == 'exclude') {

            $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->excludePageSidebar($pageId, null, null);
            $returnDetails['flash'] = $this->get('translator')->trans('CMS_EXCLUDE_SIDEBAR_SUCCESS');
        } else {

            $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->excludePageSidebar($pageId, $type, $area);
            $columnValue = ($type == 'small') ? '5' : '4';

            
            foreach ($pageContentDetails['page']['container'] as $containerId => $value) {
                $pageContainerObj->decreaseColumnCountSidebar($containerId, $columnValue);
            }
            $returnDetails['flash'] = $this->get('translator')->trans('CMS_INCLUDE_SIDEBAR_SUCCESS');
        }
        
        //To handle portrait element column width adjustment on sidebar include and exclude
         $fgcmsPortraitObj =  new FgCmsPortraitContainer($this->container);
         $fgcmsPortraitObj->adjustPortraitElementOnSidebarIncludeExclude(array_keys($pageContentDetails['page']['container']));
        
        //retrive content details of a page
        $pageContentObj->saveJsonContent($pageId);

        $returnDetails['redirect'] = $this->generateUrl('website_cms_page_edit', array('pageId' => $pageId));
        $returnDetails['sync'] = true;
        $returnDetails['status'] = $this->get('translator')->trans('CMS_INCLUDE_SIDEBAR_SUCCESS');

        return new JsonResponse($returnDetails);
    }
}
