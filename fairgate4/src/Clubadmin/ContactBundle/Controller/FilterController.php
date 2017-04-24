<?php

/**
 * RoleController
 *
 * This controller was created for handling contact listing functionalities
 *
 * @package    ClubadminContactBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */

namespace Clubadmin\ContactBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Clubadmin\ContactBundle\Util\FgFilterData;
use Symfony\Component\HttpFoundation\Request;

/**
 * For handle the Contact filter conditions
 */
class FilterController extends FgController {

   
    /**
     * Get all filter data
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getDataAction() {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $filterClass = new FgFilterData($this->container,$request);
        $filterData = $filterClass->buildFilterData();

        return new JsonResponse($filterData);
    }

     /**
     * getsave filter data from active contact page
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getSavedDataAction(Request $request) {
        $filterContent = false;
        $filterId = $request->request->get('filterId', '0');
        $type = $request->request->get('type', 'filter');
        if ($type == 'filterrole') {
            $rowFilter = $this->em->getRepository('CommonUtilityBundle:FgFilter')->getFilterRoledata($filterId);
            $rowClubId = $rowFilter['club'];
            if ($rowClubId == $this->clubId) {
                $content = $rowFilter['filterData'];
                $filterContent = ($content != '' ? $content : false);
            }
        } else {
            $rowFilter = $this->em->getRepository('CommonUtilityBundle:FgFilter')->find($filterId);
            if ($rowFilter) {
                $rowClubId = $rowFilter->getClub()->getId();
                if ($rowClubId == 1 || $rowClubId == $this->clubId) {
                    $content = $rowFilter->getFilterData();
                    $filterContent = ($content != '' ? $content : false);
                }
            }
        }

        return new JsonResponse(array('content' => $filterContent));
    }

    /**
     * save filter data from active contact page
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function saveFilterAction() {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $contactType = $request->get('contactType', 'contact');
        $filterdata['jString'] = $request->get('jString');
        $filterdata['name'] = $request->get('value');
        $filterdata['clubId'] = $this->clubId;
        $filterdata['contactId'] = $this->contactId;
        $filterdata['type'] = ($contactType == 'sponsor') ? 'sponsor' : 'general';
        $filter = $this->em->getRepository('CommonUtilityBundle:FgFilter')->saveFilter($filterdata);
        $input[] = array('id' => $filter['last_id']['lastid'], 'title' => $filterdata['name'], 'itemType' => 'filter', 'count' => 0, 'bookMarkId' => '');
        return new JsonResponse(array("input" => $input, 'operation' => $filter['operation']));
    }

    /**
     * Executes sidebarFilter newly added Action
     *
     * Function specifies the newly added Filter for the club
     *
     * @return Json array
     */
    public function sidebarSingleFilterAction() {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $id = $request->get('id');
        $type = $request->get('type', 'filter');
        $singleSavedFilter = $this->em->getRepository('CommonUtilityBundle:FgFilter')->getSingleSavedSidebarFilter($id, $this->contactId, $this->clubId, $type);

        return new JsonResponse(array('singleSavedFilter' => $singleSavedFilter));
    }

    /**
     * Executes upadteSavedFilter newly added Action
     *
     * Function update Filter for the club
     *
     * @return Json array
     */
    public function upadteSavedFilterAction() {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $contactType = $request->get('contactType', 'contact');
        if ($request->getMethod() == 'POST') {
            $flitArr = json_decode($request->request->get('filterArr'), true);
            if (count($flitArr) > 0) {
                $this->generatequeryAction('fg_filter', $flitArr, $this->clubId, $contactType);

                if ($contactType == 'sponsor') {
                    return new JsonResponse(array('status' => 'SUCCESS', 'noparentload' => true, 'flash' => $this->get('translator')->trans('CM_CONTACT_FILTER_SAVE_SUCCESS')));
                } else {
                    $redirect = $this->generateUrl('saved_filter_settings');
                    return new JsonResponse(array('status' => 'SUCCESS', 'sync' => 1, 'noparentload' => true, 'flash' => $this->get('translator')->trans('CM_CONTACT_FILTER_SAVE_SUCCESS')));
                }
            }

            return new JsonResponse(array('status' => 'SUCCESS', 'sync' => 1, 'redirect' => $redirect, 'flash' => $this->get('translator')->trans('CM_CONTACT_FILTER_SAVE_FAILED')));
        }
    }

    /**
     * Executes updateBrokenFilter Action
     *
     * Function update Broken fileld of Filter Action
     *
     * @return integer
     */
    public function updateBrokenFilterAction() {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        if ($request->getMethod() == 'POST') {
            $id = $request->get('id');
            $broken = $request->get('broken');
            $this->em->getRepository('CommonUtilityBundle:FgFilter')->updateBorkenFilter($id, $broken);

            return new Response(0);
        }
    }
    
    
}
