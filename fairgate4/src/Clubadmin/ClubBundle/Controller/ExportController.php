<?php

/**
 * ExportController
 *
 * To handle the export functionality of the club data
 *
 * @package    ClubadminClubBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
namespace Clubadmin\ClubBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Symfony\Component\HttpFoundation\Response;
use Clubadmin\Classes\Clublist;
use Symfony\Component\Intl\Intl;
use Clubadmin\ClubBundle\Util\ClublistData;
use Symfony\Component\HttpFoundation\Request;

/**
 * ExportController
 */
class ExportController extends FgController
{

    /**
     * Pre exicute function to allow access to federation club only
     *
     */
    public function preExecute()
    {
        parent::preExecute();
        if ($this->clubType != 'federation' && $this->clubType != 'sub_federation') {
            throw $this->createNotFoundException($this->clubTitle . ' have no access to this page');
        }
    }

    /**
     * Function that shows the export index page
     *
     * @return Template
     */
    public function indexAction(Request $request)
    {
        $countSession = $this->session->get($this->clubType . $this->clubId);
        $club = $this->get('club');
        $terminologyService = $this->get('fairgate_terminology_service');
        $clubterminologyTerm = $terminologyService->getTerminology('Club', $this->container->getParameter('plural'));
        $totalCount = 0;
        if ($request->getMethod() == 'POST') {
            $selectedIds = $request->request->get('selcontacthidden');
            $searchval = $request->request->get('searchhidden');
            $contactCount = $request->request->get('counthidden');
        }
        /* setting the total count */
        if (!(empty($contactCount))) {
            $totalCount = $contactCount;
        } elseif (!(empty($selectedIds))) {
            $selectIdCountarray = explode(',', $selectedIds);
            $totalCount = count($selectIdCountarray);
        } elseif (isset($countSession)) {
            $totalCount = $countSession;
        } else {
            $clublistClass = new Clublist($this->container, $club);
            $clublistClass->setCount();
            $clublistClass->setFrom();
            $clublistClass->setCondition();
            $countQuery = $clublistClass->getResult();
            $totalcontactlist = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($countQuery);
            $totalCount = $totalcontactlist[0]['count'];
        }
        /* ends here */
        $breadCrumb = array('breadcrumb_data' => array());
        $allTableSettings = $this->em->getRepository('CommonUtilityBundle:FgClubTableSettings')->getAllClubTableSettings($this->clubId, $this->contactId, 'DATA');
        $defaultSettings = $this->container->getParameter('default_club_table_settings');

        return $this->render('ClubadminClubBundle:Export:index.html.twig', array('breadCrumb' => $breadCrumb, 'clubId' => $this->clubId, 'totalCount' => $totalCount, 'contactId' => $this->contactId, 'selectedIds' => $selectedIds, 'searchval' => $searchval, 'allColumnSettings' => $allTableSettings, 'defaultSettings' => $defaultSettings, 'clubterminologyTerm' => $clubterminologyTerm));
    }

    /**
     * Function that is used to do export functionality
     *
     * @return Response
     */
    public function exportAction(Request $request)
    {
        ini_set('max_execution_time', 0);
        ini_set("memory_limit", "2000M");
        $sortSession = $this->session->get("sort-order" . $this->clubType . $this->clubId);
        $delimiter = '';
        $clublistData = new ClublistData($this->contactId, $this->container);
        if ($request->getMethod() == 'POST') {
            /* Form values */
            $csvType = $request->request->get('CSVtype');
            $checked = $request->request->get('check');
            /* setting the CSV type delimiter */
            $delimiter = ($csvType == "colonSep") ? ";" : ",";
            /* Ends here */
            $datarray = json_decode($request->get('formhidden'), true);
            $columnNames = $datarray['columnNames'];
            $this->setClublistVariables($clublistData, $datarray);
        }
        //set the selected clubid for export
        if (!(empty($datarray['selectIds']))) {
            $clublistData->selectedIds = $datarray['selectIds'];
        }
        $clublistDatas = $clublistData->getClubData();
        //$finalResultArray = $this->optimizeClubData($clublistDatas['data']);
        $response = $this->createCsvfile($clublistDatas['data'], $columnNames, $delimiter, $checked);

        return $response;
    }

    /**
     * Function that is used to generate csv data
     *
     * @param Array   $dataArray   Data array
     * @param Strings $columnNames Column names
     * @param Int     $delimiter   Delimiter
     * @param Int     $check       Check flag
     *
     * @return String
     */
    private function generateCsv($dataArray, $columnNames, $delimiter, $check)
    {
        $delimiter = '"' . $delimiter . '"';
        //To remove the first index from the array and also remove the $nbsp character from the title section
        if (!(empty($columnNames))) {
            //to collect the sTitle value from the column name array using removeFirstColumn function
            array_walk($columnNames, array($this, 'removeFirstColumn'));
            unset($columnNames[0]);
        }
        $clubTerTitle = ucfirst($this->get('fairgate_terminology_service')->getTerminology('Club', $this->container->getParameter('singular')));
        $exportColumnArray = ($check != "") ? array_merge(array(0 => $this->get('translator')->trans('%club% Id', array('%club%' => $clubTerTitle))), $columnNames) : $columnNames;
        $content = '"' . implode($delimiter, str_replace('"', '', $exportColumnArray)) . '"';
        $content .= "\n";
        foreach ($dataArray as $value) {
            //to optimise the result array using optimizeClubData function
            array_walk($value, array($this, 'optimizeClubData'));
            if ($check == "" && array_key_exists('id', $value)) {
                unset($value['id']);
            }
            $content .= '"' . implode($delimiter, str_replace('"', '', $value)) . '"' . "\n";
        }

        return $content;
    }

    /**
     *
     * @param type $clublistDatas selected club data
     */
    private function optimizeClubData(&$clublistDatas, $key)
    {
        $countryList = Intl::getRegionBundle()->getCountryNames();
        $languages = Intl::getLanguageBundle()->getLanguageNames();
        switch ($key) {
            case "SILAST_CONTACT_EDIT":
                $clublistDatas = $this->get('club')->formatDate($clublistDatas, 'date');
                break;
            case "CF_language":
                $shortkey = $clublistDatas;
                $clublistDatas = $languages[$shortkey];
                break;
            case "CF_C_country":case "CF_I_country":
                $shortCode = $clublistDatas;
                $clublistDatas = $countryList[$shortCode];
                break;
        }
    }

    /**
     * To set the clublist class variables
     * @param object $clublistData clublist class object
     * @param array  $datarray     data     from  the    post
     */
    private function setClublistVariables($clublistData, $datarray)
    {
        $clublistData->filterValue = json_decode($datarray['filterdata'], true);
        $clublistData->dataTableColumnData = '';
        $clublistData->sortColumnValue = '';
        $searchValue['value'] = $datarray['searchvalue'];
        $clublistData->searchval = $searchValue;
        $clublistData->tabledata = $datarray['columnType'];
        $clublistData->sortColumnValue = $this->session->get('filteredClubDetailsiSortCol_0');
        $clublistData->dataTableColumnData[$clublistData->sortColumnValue[0]['column']]['data'] = $this->session->get('filteredClubDetailsmDataProp');
        $clublistData->sortColumnValue[0]['dir'] = $this->session->get('filteredClubDetailsSortDir_0');
    }

    /**
     * To create csv file
     * @param array  $finalResultArray selected  club      data
     * @param array  $columnNames      selected  columns
     * @param String $delimiter        delimiter
     * @param Int    $checked          id's      visibility
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function createCsvfile($finalResultArray, $columnNames, $delimiter, $checked)
    {
        /* Creating the file name */
        $terminologyService = $this->get('fairgate_terminology_service');
        $clubname = ucfirst($terminologyService->getTerminology('Club', $this->container->getParameter('singular')));
        $filename = $clubname . '_' . date("Y-m-d") . '_' . date("H-i-s") . '.csv';
        $response = new Response();
        // prints the HTTP headers followed by the content
        $response->setContent(utf8_decode($this->generateCsv($finalResultArray, $columnNames, $delimiter, $checked)));
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->headers->set('Content-Transfer-Encoding', 'binary');

        return $response;
    }

    /**
     * For array_walk callback function
     * @param type $classnamearray  array values
     * @param type $key             array index
     */
    private function removeFirstColumn(&$classnamearray, $key)
    {
        if ($key != 0 && array_key_exists('sTitle', $classnamearray)) {
            $classnamearray = str_replace('&nbsp;', ' ', $classnamearray['sTitle']);
        }
    }
}
