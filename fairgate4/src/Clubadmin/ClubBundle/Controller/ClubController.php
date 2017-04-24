<?php

/**
 * This controller was created for handling contact listing functionalities
 */
namespace Clubadmin\ClubBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Clubadmin\Classes\Clublist;
use Clubadmin\Classes\Clubfilter;
use Clubadmin\Classes\Clubdatatable;
use Symfony\Component\HttpFoundation\Response;
use Clubadmin\ClubBundle\Util\ClublistData;
use Symfony\Component\HttpFoundation\Request;
use Common\UtilityBundle\Util\FgUtility;

/**
 * ClubController used for managing club listing, searching & filtering  functionalities
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 */
class ClubController extends FgController
{

    /**
     * Pre execute function to allow access to federation club only
     *
     */
    public function preExecute()
    {
        parent::preExecute();

        if ($this->clubType != 'federation' && $this->clubType != 'sub_federation') {
            $permissionObj = $this->fgpermission;
            $permissionObj->checkClubAccess(0, "backend_club");
        }
    }

    /**
     * Execute all the contact related to the particular club/federation action
     *
     * Function to list all the  club under the federation or sub-federation
     *
     * @return Response
     */
    public function listclubAction(Request $request)
    {
        $clublistData = new ClublistData($this->contactId, $this->container);
        $clublistData->filterValue = $request->get('filterdata', '');
        if ($clublistData->filterValue != '' && $clublistData->filterValue == '0') {
            $output = array("iTotalRecords" => 0, "iTotalDisplayRecords" => 0, "aaData" => array());
            return new JsonResponse($output);
        }
        try {
            $clublistData->dataTableColumnData = $request->get('columns', '');
            $clublistData->sortColumnValue = $request->get('order', '');
            $clublistData->searchval = $request->get('search', '');
            $clublistData->tableFieldValues = $request->get('tableField', '');
            $clublistData->startValue = $request->get('start', '');
            $clublistData->displayLength = $request->get('length', '');
            //For get the contact list array
            $clubData = $clublistData->getClubData();
            //For set the datatable json array
            $output = array("iTotalRecords" => $clubData['totalcount'], "iTotalDisplayRecords" => $clubData['totalcount'], "aaData" => array());
            $this->session->set($this->clubType . $this->clubId, $clubData['totalcount']);
            // Section for next and previous functionality
            $clublistData->setSessionValues($clubData['data']);
            // Section for next and previous functionality ends
            $clubListDatatabledata = new Clubdatatable($this->container);
            $output['aaData'] = $clubListDatatabledata->iterateDataTableData($clubData['data']);
            $output['aaDataType'] = $this->getClubFieldDetails();
            return new JsonResponse($output);
        } catch (\Doctrine\DBAL\DBALException $e) {
            $output = array("iTotalRecords" => 0, "iTotalDisplayRecords" => 0, "aaData" => array());
            return new JsonResponse($output);
        }
    }

    /**
     * Function to view the contact of a club or federation.
     *
     * @return Template
     */
    public function viewclubAction()
    {
        $breadCrumb = array(
            'breadcrumb_data' => array('Active Clubs' => '#')
        );
        $settingsType = 'DATA';
        $allTableSettings = $this->em->getRepository('CommonUtilityBundle:FgClubTableSettings')->getAllClubTableSettings($this->clubId, $this->contactId, $settingsType);
        $defaultSettings = $this->container->getParameter('default_club_table_settings');

        return $this->render('ClubadminClubBundle:ClubList:clublist.html.twig', array('breadCrumb' => $breadCrumb, 'clubId' => $this->clubId, 'contactId' => $this->contactId, 'allTableSettings' => $allTableSettings, 'defaultSettings' => $defaultSettings));
    }

    /**
     * Execute Savesd filter listings
     *
     * Function to list the Savesd filters
     *
     * @return Template
     */
    public function savedClubfilterAction()
    {

        $breadCrumb = array(
            'breadcrumb_data' => array(
                'Active Contacts' => '#',
                'Saved Filter' => '#'
            ),
            'back' => $this->generateUrl('club_homepage')
        );
        $allSavedFilter = $this->em->getRepository('CommonUtilityBundle:FgClubFilter')->getSavedClubSidebarFilter($this->contactId, $this->clubId);

        return $this->render('ClubadminClubBundle:ClubList:savedClubfilter.html.twig', array('breadCrumb' => $breadCrumb, 'allSavedFilter' => $allSavedFilter, 'clubId' => $this->clubId, 'contactId' => $this->contactId));
    }

    /**
     * Executes sidebarFilterCount Action
     *
     * Function to get the count of each filter
     *
     * @return Response
     */
    public function sidebarFilterCountAction(Request $request)
    {
        $id = $request->get('filter_id');
        try {
            //call a service for collect all relevant data related to the club
            $club = $this->get('club');
            $singleSavedFilter = $this->em->getRepository('CommonUtilityBundle:FgClubFilter')->getSingleSavedClubSidebarFilter($id, $this->contactId, $this->clubId);
            $filterdata = $singleSavedFilter[0]['filterData'];
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
            $totalcontactlist = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($countQuery);
            return new Response($totalcontactlist[0]['count']);
        } catch (\Doctrine\DBAL\DBALException $e) {
            $singleSavedFilter = $this->em->getRepository('CommonUtilityBundle:FgClubFilter')->updateClubBorkenFilter($id, '1');

            return new Response('-1');
        }
    }

    /**
     * This is used to create new contact element from slider bar
     *
     * @return Template
     */
    public function newElementFromSidebarAction(Request $request)
    {
        $elementType = $request->get('elementType');
        $title1 = $request->get('value');
        $tableArray = array();
        $title = FgUtility::getSecuredDataString(trim($title1), $this->conn);
        $title = '"' . $title . '"';
        if ($elementType == 'classification') {
            $tableName = 'fg_club_classification';
            $whereCond = "  federation_id = '{$this->clubId}'";
            $sortValue = $this->getMaxSortOrderTable($tableName, '', $this->clubId, $whereCond);
            $tableArray[$tableName]['fields'] = array('federation_id', 'title', 'sort_order');
            $tableArray[$tableName]['values'] = array($this->clubId, $title, $sortValue);
            $this->insertIntoTableSidebar($tableArray);
            $where = "federation_id = $this->clubId";
            $lastInsertedId = $this->getLastInsertedId($tableName, '', $where);
            $i18TableName = 'fg_club_classification_i18n';
            $i18tableArray = array();
            $i18tableArray[$i18TableName]['fields'] = array('lang', 'title_lang', 'is_active', 'id');
            $i18tableArray[$i18TableName]['values'] = array($title, 1, $lastInsertedId);
            $i18tableArray[$i18TableName]['languages'] = $this->clubLanguages;
            $this->insertIntoTableSidebar($i18tableArray);
            $input[] = array('id' => "$lastInsertedId", 'title' => $title1, 'type' => 'select');
            return new JsonResponse(array('items' => $input));
        } elseif ($elementType == 'class') {
            $tableName = 'fg_club_class';
            $categoryId = $request->get('category_id');
            $whereCond = " classification_id = $categoryId";
            $tableArray[$tableName]['fields'] = array('	classification_id', 'federation_id', 'title', 'sort_order');
            $sortValue = $this->getMaxSortOrderTable($tableName, '', $this->clubId, $whereCond);
            $tableArray[$tableName]['values'] = array($categoryId, $this->clubId, $title, $sortValue);
            $where = "federation_id = $this->clubId AND classification_id = $categoryId";
            $this->insertIntoTableSidebar($tableArray);
            $lastInsertedId = $this->getLastInsertedId($tableName, '', $where);
            $i18TableName = 'fg_club_class_i18n';
            $i18tableArray[$i18TableName]['fields'] = array('lang', 'title_lang', 'is_active', 'id');
            $i18tableArray[$i18TableName]['values'] = array($title, 1, $lastInsertedId);
            $i18tableArray[$i18TableName]['languages'] = $this->clubLanguages;
            $this->insertIntoTableSidebar($i18tableArray);
            //INSERT LOG ENTRY IF IT IS A ROLE CREATION
            $valueLogArr['title'] = FgUtility::getSecuredDataString(trim($request->get('value')), $this->conn);
            $this->em->getRepository('CommonUtilityBundle:FgClubClassification')->insertLogEntries($tableName, $lastInsertedId, $valueLogArr, true, $this->clubDefaultLang, $this->clubId, true, $this->contactId,'',$this->container);
            $filterData = array();
            $filterData['id'] = 'class';
            $filterData['title'] = $this->get('translator')->trans('CLASSES');
            $filterData['fixed_options'][0][] = array('id' => '', 'title' => "- " . $this->get('translator')->trans('CL_SELECT_CLASSIFICATION') . " -");
            $filterData['fixed_options'][1][] = array('id' => 'any', 'title' => $this->get('translator')->trans('CL_ANY_CLASS'));
            $filterData['fixed_options'][1][] = array('id' => '', 'title' => $this->get('translator')->trans('CL_SELECT_CLASS'));
            $input = array('0' => array('id' => "$lastInsertedId", 'title' => $title1, 'categoryId' => "$categoryId", 'itemType' => 'class', 'count' => '0', 'bookMarkId' => '', 'type' => 'select', 'filterData' => $filterData, 'draggable' => '1'));

            return new JsonResponse(array('input' => $input));
        }
    }

    /**
     * For get the type of selected club fields data
     *
     * @return array
     */
    private function getClubFieldDetails()
    {
        $output['aaDataType'] = array();
        $output['aaDataType'][] = array("title" => "CF_website", "type" => "CF_website");
        $output['aaDataType'][] = array("title" => "CF_email", "type" => "CF_email");
        $output['aaDataType'][] = array("title" => 'AFNotes', "type" => "AFNotes");
        $output['aaDataType'][] = array("title" => 'clubname', "type" => "clubname");
        $output['aaDataType'][] = array("title" => 'edit', "type" => "edit");
        $output['aaDataType'][] = array("title" => 'AFDocuments', "type" => "AFDocuments");

        return $output['aaDataType'];
    }
}
