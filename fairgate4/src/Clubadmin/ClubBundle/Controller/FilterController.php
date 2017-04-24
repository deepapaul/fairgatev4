<?php

/**
 * FilterController
 *
 * This controller was created for handling contact listing functionalities
 *
 * @package    ClubadminClubBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
namespace Clubadmin\ClubBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Common\UtilityBundle\Util\FgUtility;
use Symfony\Component\HttpFoundation\Response;
use Clubadmin\Classes\Clublist;
use Clubadmin\Classes\Clubfilter;
use Admin\UtilityBundle\Repository\Pdo\ClubPdo;
use Common\UtilityBundle\Util\FgAdminQueryhandler;

/**
 * FilterController used for managing club assignment functionalities
 */
class FilterController extends FgController
{

    /**
     * for return the club data
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getClubDataAction()
    {
        $countryList = FgUtility::getCountryList();
        $countryCodes = implode(',', array_keys($countryList));
        $terminologyService = $this->get('fairgate_terminology_service');
        $cluHeirarchyDet = $this->get('club')->get('clubHeirarchyDet');
        $fieldLanguages = FgUtility::getClubLanguageNames($this->clubLanguages);
        $countryList = FgUtility::getCountryList();
        $club = $this->get('club');

        foreach ($fieldLanguages as $lanKey => $lanValue) {
            $clubLanguages[] = array('id' => $lanKey, 'title' => $lanValue);
        }
        foreach ($countryList as $cnKey => $cnValue) {
            $country[] = array('id' => $cnKey, 'title' => $cnValue);
        }

        $filterData = array();
        $federationTerTitle = ucfirst($terminologyService->getTerminology('Federation member', $this->container->getParameter('plural')));
        $clubTerTitle = ucfirst($terminologyService->getTerminology('Club', $this->container->getParameter('singular')));
        $subfedTerTitle = ucfirst($terminologyService->getTerminology('Sub-federation', $this->container->getParameter('singular')));
        //System infos
        $filterData['SI']['id'] = 'SI';
        $filterData['SI']['title'] = $this->get('translator')->trans('CM_SYSTEM_INFOS');
        $filterData['SI']['fixed_options'][0][] = array('id' => '', 'title' => $this->get('translator')->trans('CM_SELECT_INFORMTAION_FIELD'));
        $filterData['SI']['entry'][] = array('id' => 'CLUB_ID', 'title' => $clubTerTitle . $this->get('translator')->trans('CL_ID'), 'type' => 'number');
        $filterData['SI']['entry'][] = array('id' => 'FED_MEMBERS', 'title' => $federationTerTitle, 'type' => 'number');
        $filterData['SI']['entry'][] = array('id' => 'LAST_CONTACT_EDIT', 'title' => $this->get('translator')->trans('LAST_CONTACT_EDITING'), 'type' => 'date');
        $filterData['SI']['entry'][] = array('id' => 'LAST_ADMIN_LOGIN', 'title' => $this->get('translator')->trans('LAST_ADMIN_LOGIN'), 'type' => 'date');
        //club data field
        $filterData['CD']['id'] = 'CD';
        $filterData['CD']['title'] = $this->get('translator')->trans('CLUB_DATA_FIELDS', array('%club%' => $clubTerTitle));
        $filterData['CD']['fixed_options'][0][] = array('id' => '', 'title' => "- " . $this->get('translator')->trans('CL_SELECT_CLUB_FIELD', array('%club%' => $clubTerTitle)) . " -");
        $filterData['CD']['fixed_options'][1][] = array('id' => '', 'title' => $this->get('translator')->trans('CM_SELECT_VALUE'));

        $filterData['CD']['entry'][] = array('id' => 'website', 'title' => $this->get('translator')->trans('CL_URL'), 'type' => 'text', 'selectgroup' => $clubTerTitle . " " . $this->get('translator')->trans('CL_INFO'));
        $filterData['CD']['entry'][] = array('id' => 'url_identifier', 'title' => $this->get('translator')->trans('CL_IDENTIFIER'), 'type' => 'text', 'selectgroup' => $clubTerTitle . " " . $this->get('translator')->trans('CL_INFO'));
        $filterData['CD']['entry'][] = array('id' => 'email', 'title' => $this->get('translator')->trans('CL_EMAIL'), 'type' => 'text', 'selectgroup' => $clubTerTitle . " " . $this->get('translator')->trans('CL_INFO'));
        $filterData['CD']['entry'][] = array('id' => 'created_at', 'title' => $this->get('translator')->trans('CL_ESTABLISH'), 'type' => 'date', 'selectgroup' => $clubTerTitle . " " . $this->get('translator')->trans('CL_INFO'));
        $filterData['CD']['entry'][] = array('id' => 'number', 'title' => $this->get('translator')->trans('CL_NUMBER', array('%club%' => $clubTerTitle)), 'type' => 'number', 'selectgroup' => $clubTerTitle . " " . $this->get('translator')->trans('CL_INFO'));
        $filterData['CD']['entry'][] = array('id' => 'language', 'title' => $this->get('translator')->trans('CL_CORRESPOND_LANG'), 'type' => 'select', 'selectgroup' => $clubTerTitle . " " . $this->get('translator')->trans('CL_INFO'), 'input' => $clubLanguages);

        $filterData['CD']['entry'][] = array('id' => 'C_co', 'title' => $this->get('translator')->trans('CL_CO'), 'type' => 'text', 'selectgroup' => $this->get('translator')->trans('CL_CR_ADDR'), 'groupshortname' => $this->get('translator')->trans('CL_CORRESPONDENCE'), 'grp' => 'correspondence');
        $filterData['CD']['entry'][] = array('id' => 'C_street', 'title' => $this->get('translator')->trans('CL_STREET'), 'type' => 'text', 'selectgroup' => $this->get('translator')->trans('CL_CR_ADDR'), 'groupshortname' => $this->get('translator')->trans('CL_CORRESPONDENCE'), 'grp' => 'correspondence');
        $filterData['CD']['entry'][] = array('id' => 'C_pobox', 'title' => $this->get('translator')->trans('CL_POST_BOX'), 'type' => 'text', 'selectgroup' => $this->get('translator')->trans('CL_CR_ADDR'), 'groupshortname' => $this->get('translator')->trans('CL_CORRESPONDENCE'), 'grp' => 'correspondence');
        $filterData['CD']['entry'][] = array('id' => 'C_city', 'title' => $this->get('translator')->trans('CL_CITY'), 'type' => 'text', 'selectgroup' => $this->get('translator')->trans('CL_CR_ADDR'), 'groupshortname' => $this->get('translator')->trans('CL_CORRESPONDENCE'), 'grp' => 'correspondence');
        $filterData['CD']['entry'][] = array('id' => 'C_zipcode', 'title' => $this->get('translator')->trans('CL_ZIPCODE'), 'type' => 'number', 'selectgroup' => $this->get('translator')->trans('CL_CR_ADDR'), 'groupshortname' => $this->get('translator')->trans('CL_CORRESPONDENCE'), 'grp' => 'correspondence');
        $filterData['CD']['entry'][] = array('id' => 'C_state', 'title' => $this->get('translator')->trans('CL_STATE'), 'type' => 'text', 'selectgroup' => $this->get('translator')->trans('CL_CR_ADDR'), 'groupshortname' => $this->get('translator')->trans('CL_CORRESPONDENCE'), 'grp' => 'correspondence');
        $filterData['CD']['entry'][] = array('id' => 'C_country', 'title' => $this->get('translator')->trans('CL_COUNTRY'), 'type' => 'select', 'input' => $country, 'selectgroup' => $this->get('translator')->trans('CL_CR_ADDR'), 'groupshortname' => $this->get('translator')->trans('CL_CORRESPONDENCE'), 'grp' => 'correspondence');
        //invoice address

        $filterData['CD']['entry'][] = array('id' => 'I_co', 'title' => $this->get('translator')->trans('CL_CO'), 'type' => 'text', 'selectgroup' => $this->get('translator')->trans('CL_IV_ADDR'), 'groupshortname' => $this->get('translator')->trans('CL_INVOICE'), 'grp' => 'invoice');
        $filterData['CD']['entry'][] = array('id' => 'I_street', 'title' => $this->get('translator')->trans('CL_STREET'), 'type' => 'text', 'selectgroup' => $this->get('translator')->trans('CL_IV_ADDR'), 'groupshortname' => $this->get('translator')->trans('CL_INVOICE'), 'grp' => 'invoice');
        $filterData['CD']['entry'][] = array('id' => 'I_pobox', 'title' => $this->get('translator')->trans('CL_POST_BOX'), 'type' => 'text', 'selectgroup' => $this->get('translator')->trans('CL_IV_ADDR'), 'groupshortname' => $this->get('translator')->trans('CL_INVOICE'), 'grp' => 'invoice');
        $filterData['CD']['entry'][] = array('id' => 'I_city', 'title' => $this->get('translator')->trans('CL_CITY'), 'type' => 'text', 'selectgroup' => $this->get('translator')->trans('CL_IV_ADDR'), 'groupshortname' => $this->get('translator')->trans('CL_INVOICE'), 'grp' => 'invoice');
        $filterData['CD']['entry'][] = array('id' => 'I_zipcode', 'title' => $this->get('translator')->trans('CL_ZIPCODE'), 'type' => 'number', 'selectgroup' => $this->get('translator')->trans('CL_IV_ADDR'), 'groupshortname' => $this->get('translator')->trans('CL_INVOICE'), 'grp' => 'invoice');
        $filterData['CD']['entry'][] = array('id' => 'I_state', 'title' => $this->get('translator')->trans('CL_STATE'), 'type' => 'text', 'selectgroup' => $this->get('translator')->trans('CL_IV_ADDR'), 'groupshortname' => $this->get('translator')->trans('CL_INVOICE'), 'grp' => 'invoice');
        $filterData['CD']['entry'][] = array('id' => 'I_country', 'title' => $this->get('translator')->trans('CL_COUNTRY'), 'type' => 'select', 'input' => $country, 'selectgroup' => $this->get('translator')->trans('CL_IV_ADDR'), 'groupshortname' => $this->get('translator')->trans('CL_INVOICE'), 'grp' => 'invoice');
        // Addition Fields
        $filterData['AF']['id'] = 'AF';
        $filterData['AF']['show_filter'] = 1;
        $filterData['AF']['fixed_options'] = array();
        $filterData['AF']['title'] = $this->get('translator')->trans('CL_AF');
        $filterData['AF']['entry'][] = array('id' => 'Notes', 'title' => $this->get('translator')->trans('CL_NOTES'), 'type' => 'number', 'selectgroup' => $this->get('translator')->trans('CL_AF'), 'show_filter' => 1);
        $filterData['AF']['entry'][] = array('id' => 'Documents', 'title' => $this->get('translator')->trans('CL_DOCUMENTS'), 'type' => 'number', 'selectgroup' => $this->get('translator')->trans('CL_AF'), 'show_filter' => 0);
        //collect the all subfederations under the federation
        $clubPdo = new ClubPdo($this->container);
        $rowClubsArray = $clubPdo->getAllSubLevelClubs($this->clubId, $this->contactId, 'c.is_sub_federation=1', 0, $club->get('default_lang')); 
        if (count($rowClubsArray) > 0) {
            //club options
            $filterData['CO']['id'] = 'CO';
            $filterData['CO']['title'] = $this->get('translator')->trans('CL_OPTIONS', array('%club%' => $clubTerTitle));

            foreach ($rowClubsArray as $clKey => $clValue) {
                $subFederation[] = array('id' => $clValue['id'], 'title' => $clValue['title'], 'bookMarkId' => $clValue['bookMarkId'], 'itemType' => 'subfed', 'count' => $clValue['count']);
            }
            $filterData['CO']['fixed_options'][][] = array('id' => '', 'title' => "- " . $this->get('translator')->trans('CL_SELECT_SUBFEDERATION', array('%subfederation%' => $clubTerTitle)) . " -");
            $filterData['CO']['fixed_options'][][] = array('id' => 'any', 'title' => $this->get('translator')->trans('CL_ANY_SUBFEDERATION', array('%subfederation%' => $subfedTerTitle)));

            $filterData['CO']['entry'][] = array('id' => 'subfed', 'title' => $subfedTerTitle, 'type' => 'select', 'input' => $subFederation, 'selectgroup' => $clubTerTitle . " options");
        }
        //ASSIGNMENTS
        $clubId = '';
        if ($this->clubType == 'federation') {
            $clubId = $this->clubId;
        } else {
            $clubId = $this->federationId;
        }
        //collect the classification under the federation
        $clubObj = new ClubPdo($this->container);
        $allClassificationsArray = $clubObj->getClubClassificationsTitle($clubId, $this->clubId, $this->clubDefaultLang, $this->clubType);
        $filterData['class']['id'] = 'class';
        $filterData['class']['title'] = $this->get('translator')->trans('CLASSES');
        $filterData['class']['fixed_options'][0][] = array('id' => '', 'title' => "- " . $this->get('translator')->trans('CL_SELECT_CLASSIFICATION') . " -");

        $filterData['class']['fixed_options'][1][] = array('id' => 'any', 'title' => $this->get('translator')->trans('CL_ANY_CLASS'));
        $filterData['class']['fixed_options'][1][] = array('id' => '', 'title' => $this->get('translator')->trans('CL_SELECT_CLASS'));
        $classCount = 0;
        if (count($allClassificationsArray) > 0) {
            $iCount = 0;
            foreach ($allClassificationsArray as $classification) {
                //collect the classs detials of the particular classification
                $clubObj = new ClubPdo($this->container);
                $allClasses = $clubObj->getClassificationClassesTitle($clubId, $this->clubId, $this->contactId, $classification['id'], $this->clubDefaultLang);
                $filterData['class']['entry'][$iCount]['id'] = $classification['id'];

                $filterData['class']['entry'][$iCount]['title'] = $classification['title'];
                $filterData['class']['entry'][$iCount]['clubcount'] = $classification['clubCount'];
                $filterData['class']['entry'][$iCount]['type'] = "select";
                $filterData['class']['entry'][$iCount]['input'] = array();
                if (!empty($allClasses) && count($allClasses) > 0) {
                    $filterData['class']['entry'][$iCount]['show_filter'] = 1;
                    $classCount = 0;
                    foreach ($allClasses as $classes) {
                        $filterData['class']['entry'][$iCount]['input'][$classCount]['id'] = $classes['id'];
                        $filterData['class']['entry'][$iCount]['input'][$classCount]['title'] = $classes['title'];
                        $filterData['class']['entry'][$iCount]['input'][$classCount]['itemType'] = 'class';
                        $filterData['class']['entry'][$iCount]['input'][$classCount]['count'] = $classes['count'];
                        $filterData['class']['entry'][$iCount]['input'][$classCount]['categoryId'] = $classes['categoryId'];
                        $filterData['class']['entry'][$iCount]['input'][$classCount]['show_filter'] = 1;
                        $filterData['class']['entry'][$iCount]['input'][$classCount]['draggable'] = $classification['draggable'];
                        $filterData['class']['entry'][$iCount]['input'][$classCount]['bookMarkId'] = $classes['bookMarkId'];

                        $classCount++;
                    }
                } else {
                    $filterData['class']['entry'][$iCount]['show_filter'] = 0;
                }
                $iCount++;
            }
        } else {
            $filterData['class']['show_filter'] = 0;
            $filterData['class']['entry'] = array();
        }
        if (isset($classCount) && $classCount == 0) {
            $filterData['class']['show_filter'] = 0;
        } else {
            $filterData['class']['show_filter'] = 1;
        }
        $allSavedFilter = $this->adminEntityManager->getRepository('AdminUtilityBundle:FgClubFilter')->getSideBarSavedFilter($this->contactId, $this->clubId);
        $filterData['filter']['show_filter'] = 0;
        $filterData['filter']['id'] = 'filter';
        $filterData['filter']['entry'] = $allSavedFilter;
        $clubObj = new ClubPdo($this->container);
        $bookmarkDetails = $clubObj->getClubBookmarks($this->contactId, $this->clubId, $this->clubType);
        $filterData['bookmark']['show_filter'] = 0;
        $filterData['bookmark']['id'] = 'bookmark';
        $filterData['bookmark']['entry'] = $bookmarkDetails;
        return new JsonResponse($filterData);
    }

    /**
     * get save filter data for club
     *
     * @param type $id filterId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getFilterDataAction($id = 0)
    {
        $rowFilter = $this->adminEntityManager->getRepository('AdminUtilityBundle:FgClubFilter')->find($id);
        
        if ($rowFilter) {
            $rowClubId = $rowFilter->getClub()->getId();
            if ($rowClubId == 1 || $rowClubId == $this->clubId) {
                $content = $rowFilter->getFilterData();
                if ($content != '') {
                    $result = $content;
                }
            }
        }

        return new JsonResponse(array('content' => $result));
    }

    /**
     * get save filter count for club
     *
     * @param type $id clubfilterId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getFilterCountAction($id = 0)
    {
        try {
            //call a service for collect all relevant data related to the club
            $singleSavedFilter = $this->adminEntityManager->getRepository('AdminUtilityBundle:FgClubFilter')->getSingleSavedClubSidebarFilter($id, $this->contactId, $this->clubId);
            $filterdata = $singleSavedFilter[0]['filterData'];
            $club = $this->get('club');

            $clublistClass = new Clublist($this->container, $club);
            $clublistClass->setCount();
            $clublistClass->setFrom();
            $clublistClass->setCondition();

            $filterarr = json_decode($filterdata, true);
            $filter = array_shift($filterarr);
            $filterObj = new Clubfilter($this->container, $clublistClass, $filter, $club);
            $sWhere .= " " . $filterObj->generateClubFilter();
            $clublistClass->addCondition($sWhere);
            //call query for collect the data
            $countQuery = $clublistClass->getResult();
            $totalcontactlist = $this->adminEntityManager->getConnection()->executeQuery($countQuery)->fetchAll();
            return new Response($totalcontactlist[0]['count']);
        } catch (\Doctrine\DBAL\DBALException $e) {
            $error = '-1';
            return new Response($error);
        }
    }

    /**
     * Executes savedFilter Action
     *
     * Action to get all saved filters in club overview
     *
     * @return Json array
     */
    public function sidebarSavedFilterAction()
    {
        $allSavedFilter = $this->adminEntityManager->getRepository('AdminUtilityBundle:FgClubFilter')->getSideBarSavedFilter($this->contactId, $this->clubId);

        return new JsonResponse($allSavedFilter);
    }

    /**
     * save filter data from active contact page
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function savedClubFilterAction()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $filterdata['jString'] = $request->get('jString');
        $filterdata['name'] = $request->get('value');
        $filterdata['clubId'] = $this->clubId;
        $filterdata['contactId'] = $this->contactId;
        $filter = $this->adminEntityManager->getRepository('AdminUtilityBundle:FgClubFilter')->saveClubFilter($filterdata,$this->container);
        $input[] = array('id' => $filter['last_id'], 'title' => $filterdata['name'], 'itemType' => 'filter', 'count' => 0, 'bookMarkId' => '');

        return new JsonResponse(array("input" => $input, 'operation' => $filter['operation']));
    }

    /**
     * Executes sidebarFilter newly added Action
     *
     * Function specifies the newly added Filter for the club
     *
     * @return Json array
     */
    public function clubsidebarSingleFilterAction()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $id = $request->get('id');
        $singleSavedFilter = $this->adminEntityManager->getRepository('AdminUtilityBundle:FgClubFilter')->getSingleSavedClubSidebarFilter($id, $this->contactId, $this->clubId);

        return new JsonResponse(array('singleSavedFilter' => $singleSavedFilter));
    }

    /**
     * Executes updateBrokenFilter Action
     *
     * Function update Broken fileld of Filter Action
     *
     * @return NILL
     */
    public function updateClubBrokenFilterAction()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        if ($request->getMethod() == 'POST') {
            $id = $request->get('id');
            $broken = $request->get('broken');
            $this->adminEntityManager->getRepository('AdminUtilityBundle:FgClubFilter')->updateClubBorkenFilter($id, $broken);
            exit;
        }
    }

    /**
     * Executes upadteSavedFilter newly added Action
     *
     * Function update Filter for the club
     *
     * @return Json array
     */
    public function updateSavedClubfilterAction()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $redirect = $this->generateUrl('saved_club_filter_settings');
        if ($request->getMethod() == 'POST') {
            $flitArr = json_decode($request->request->get('filterArr'), true);
            if (count($flitArr) > 0) {
                $genericQueryhandler = new FgAdminQueryhandler($this->container);
                $genericQueryhandler->generatequeryAction('fg_club_filter', $flitArr);
                
                return new JsonResponse(array('status' => 'SUCCESS', 'sync' => 1, 'redirect' => $redirect, 'flash' => $this->get('translator')->trans('CM_CONTACT_FILTER_SAVE_SUCCESS')));
            }

            return new JsonResponse(array('status' => 'SUCCESS', 'sync' => 1, 'redirect' => $redirect, 'flash' => $this->get('translator')->trans('CM_CONTACT_FILTER_SAVE_FAILED')));
        }
    }
}
