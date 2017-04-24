<?php
/**
 * PortraitElementController.
 *
 * @package 	Website
 * @subpackage 	CMSBundle
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 *
 */
namespace Website\CMSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Website\CMSBundle\Util\FgCmsPortrait;
use Website\CMSBundle\Util\FgCmsPortraitDisplay;
use Common\UtilityBundle\Repository\Pdo\CmsPdo;
use Clubadmin\ContactBundle\Util\ContactlistData;
use Clubadmin\Classes\Contactdatatable;
use Website\CMSBundle\Util\FgCmsPortraitFrontend;

/**
 * PortraitElementController
 *
 * This controller is used for handling various functionalities of portrait contacts  element 
 */
class PortraitElementController extends Controller
{

    /**
     * To collect the satage3 action
     * @param Request $request
     * @return JsonResponse
     */
    public function portraitGetStage3Action(Request $request)
    {
        $portraitId = $request->get('portraitId');
        $em = $this->getDoctrine()->getManager();
        $data = $em->getRepository('CommonUtilityBundle:FgCmsContactTable')->getPortraitColumnData($portraitId);
        $portraitContainerData = new FgCmsPortrait($this->container);
        $reorderData = $portraitContainerData->formatContainerData($portraitId, $data);
        $stage = 3;
        $return = array('data' => $reorderData, 'stage' => $stage, 'error' => null);

        return new JsonResponse($return);
    }

    /**
     * Method to get data of element display (stage 2) of portrait element
     * 
     * @param Request $request request object
     * 
     * @return JsonResponse data of element display
     */
    public function portraitsElementDisplayAction(Request $request)
    {
        $tableId = $request->get('tableId');
        $em = $this->getDoctrine()->getManager();
        $details = $em->getRepository('CommonUtilityBundle:FgCmsContactTable')->getPortraitElementDisplay($tableId);
        $stage = $em->getRepository('CommonUtilityBundle:FgCmsContactTable')->find($tableId)->getStage();

        return new JsonResponse(array('data' => $details, 'stage' => $stage));
    }

    /**
     * Method to save stage 2 of portrait element
     * 
     * @param Request $request request object
     * 
     * @return JsonResponse output
     */
    public function saveElementDisplayAction(Request $request)
    {
        $tableId = $request->get('table');
        $jsonData = $request->get('jsonData');
        $colSize = $request->get('colSize');
        //save data
        $em = $this->getDoctrine()->getManager();
        $em->getRepository('CommonUtilityBundle:FgCmsContactTable')->savePortraitElementDisplay($tableId, $jsonData, $this->container->get('contact')->get('id'), $colSize, $this->container);

        return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('PORTRAIT_SAVED_SUCCESSFULLY')));
    }

    /**
     * This function is used to save the portrait display step of contact portrait element
     * 
     * @param Request $request The request object
     * 
     * @return JsonResponse Wizard stage 3 save status
     */
    public function savePortraitDisplayAction(Request $request)
    {
        $tableId = $request->get('table');
        $jsonArray = $request->get('jsonData');

        $portraitObj = new FgCmsPortraitDisplay($this->container);
        $status = $portraitObj->savePortraitElementDisplay($tableId, $jsonArray);

        $elementObj = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->findOneBy(array('table' => $tableId));
        $elementId = $elementObj->getId();
        $pageId = $elementObj->getBox()->getColumn()->getContainer()->getPage()->getId();
        $contactId = $this->container->get('contact')->get('id');
        $logArray[] = "('$elementId', '$pageId', 'element', 'changed', '', '', now(), $contactId)";

        $cmsPdo = new CmsPdo($this->container);
        $cmsPdo->saveLog($logArray);

        if ($status) {
            return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('CMS_PORTRAIT_ELEMENT_DISPLAY_SAVED_SUCCESSFULLY')));
        } else {
            return new JsonResponse(array('status' => 'ERROR', 'flash' => $this->get('translator')->trans('CMS_PORTRAIT_ELEMENT_DISPLAY_SAVE_FAILED')));
        }
    }

    /**
     * To collect  portrait contact details 
     * 
     * @return JsonResponse
     */
    public function getPortraitContactDetailsAction(Request $request)
    {
        $club = $this->container->get('club');
        //Set all request value to its corresponding variables       
        $elementId = $request->get('elementId', 8116);
        $portraitElementColumnObj = new FgCmsPortraitFrontend($this->container);
        $initialDatas = $portraitElementColumnObj->getPortraitElementInitailData($elementId);
        $pagenumber = $request->get('pagenumber', '1');
        if ($initialDatas['stage'] == 'stage4') {
            $contactlistData = new ContactlistData($this->contactId, $this->container, $initialDatas['filterType'], 'website');
            $contactlistData->filterValue = json_decode($initialDatas['filterCriteria'], true);
            $contactlistData->tableFieldValues = $initialDatas['column'];
            $contactlistData->searchPortraitValue = $request->get('search', '');
            //handle startvalue from the page number
            $contactlistData->startValue = ($pagenumber > 1 ) ? ($pagenumber - 1) * $initialDatas['displayLength'] : '0';
            $contactlistData->displayLength = $initialDatas['displayLength'];
            $contactlistData->specialFilter = $request->get('filterCriteria', '');
            $contactlistData->includedIds = $initialDatas['includeIds'];
            $contactlistData->excludedIds = $initialDatas['excludedIds'];
            //check separate listing is enable or not
            $contactlistData->separateList = ($initialDatas['separateListing'] == 1) ? true : false;
            //set dependcy column details
            $contactlistData->dependColumns = $portraitElementColumnObj->getDependColumnsDetails($initialDatas['separateListingColumn']);
            //set contact sorting details 
            $contactlistData->sortColumnDetails = $initialDatas['sort'];
            //set separate listing  column details
            $contactlistData->separateListingDetails = array('separateListingColumn' => $initialDatas['separateListingColumn'], 'separateListingFunc' => $initialDatas['separateListingFunc']);
            //For get the contact list array
            $contactData = $contactlistData->getContactData();
            //collect total number of records
            $totalrecords = $contactData['totalcount'];
            //For set the datatable json array
            $output = array('totalRecords' => $totalrecords, 'portraitData' => array(), 'stage' => $initialDatas['stage']);

            //iterate the result
            $contactDatatabledata = new Contactdatatable($this->container);

            $output['portraitData'] = $contactDatatabledata->iterateDataTableData($contactData['data'], $this->container->getParameter('country_fields'), $contactlistData->tabledata, 'website');
            $output['clubDetails'] = array('clubId' => $club->get('id'), 'federationId' => $club->get('federation_id'), 'subFederationId' => $club->get('sub_federation_id'));
            $output['dataUrl'] = $this->generateUrl('portrait_element_contact_details');
            $output['elementType'] = 'portrait-element';
            $output['elementId'] = $elementId;
        } else {
            $output = array('totalRecords' => 0, 'stage' => $initialDatas['stage']);
        }



        return new JsonResponse($output);
    }

    /**
     * This function is used to show preview of portrait element in create/edit portrait element stage 3
     *
     * @return Object View Template Render Object
     */
    public function getPortraitElementPreviewAction(Request $request)
    {
        $elementId = $request->get('elementId'); //8273;
        $columnSize = $request->get('columnSize'); //5
        $portraitDetailsObj = new FgCmsPortraitFrontend($this->container);
        $portraitElemDetails = $portraitDetailsObj->getPortraitElementDetails($elementId);
        $portraitElemDetails['type'] = 'stage3-preview';
        $portraitElemSettings = array();
        $returnArray = array();
        if (($portraitElemDetails['stage'] == 'stage3') || ($portraitElemDetails['stage'] == 'stage4')) {
            $portraitElemSettings[$elementId]['template'] = $this->container->get('templating')->render('WebsiteCMSBundle:ContactPortraitsElement:templatePortraitElement.html.twig', $portraitElemDetails);
            $portraitElemSettings[$elementId]['data'] = $portraitElemDetails;
            $returnArray['portraitTemplate'] = $this->container->get('templating')->render('WebsiteCMSBundle:ContactPortraitsElement:templatePortraitPreview.html.twig', array('elementId' => $elementId, 'columnSize' => $columnSize, 'portraitElemSettings' => $portraitElemSettings));
            $returnArray['portraitData'] = $portraitElemDetails;
            $returnArray['contactsData'] = $this->getSingleContactDetailForPortraitPreview($elementId);
        }

        return new JsonResponse($returnArray);
    }

    /**
     * This function is used to get the details of first contact in the portrait
     * 
     * @param int $elementId The element id
     * 
     * @return array The array of contact details
     */
    private function getSingleContactDetailForPortraitPreview($elementId)
    {
        $portraitFrontendObj = new FgCmsPortraitFrontend($this->container);
        $initialDatas = $portraitFrontendObj->getPortraitElementInitailData($elementId);
        $contactlistData = new ContactlistData($this->contactId, $this->container, $initialDatas['filterType'], 'website');
        $contactlistData->filterValue = json_decode($initialDatas['filterCriteria'], true);
        $contactlistData->tableFieldValues = $initialDatas['column'];
        //handle startvalue from the page number
        $contactlistData->startValue = '0';
        $contactlistData->displayLength = 1;
        $contactlistData->includedIds = $initialDatas['includeIds'];
        $contactlistData->excludedIds = $initialDatas['excludedIds'];
        //set contact sorting details 
        $contactlistData->sortColumnDetails = $initialDatas['sort'];
        //check separate listing is enable or not
        $contactlistData->separateList = ($initialDatas['separateListing'] == 1) ? true : false;
        //set dependcy column details
        $contactlistData->dependColumns = $portraitFrontendObj->getDependColumnsDetails($initialDatas['separateListingColumn']);
        //set contact sorting details 
        //set contact sorting details 
        $contactlistData->sortColumnDetails = $initialDatas['sort'];
        //set separate listing  column details
        $contactlistData->separateListingDetails = array('separateListingColumn' => $initialDatas['separateListingColumn'], 'separateListingFunc' => $initialDatas['separateListingFunc']);
        //For get the contact list array
        $contactData = $contactlistData->getContactData();
        //iterate the result
        $contactDatatabledata = new Contactdatatable($this->container);

        return $contactDatatabledata->iterateDataTableData($contactData['data'], $this->container->getParameter('country_fields'), $contactlistData->tabledata, 'website');
    }

    
}
