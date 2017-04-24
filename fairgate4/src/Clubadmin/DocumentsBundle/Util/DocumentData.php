<?php

namespace Clubadmin\DocumentsBundle\Util;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Common\UtilityBundle\Util\FgUtility;
use Clubadmin\DocumentsBundle\Util\Documentlist;
use Clubadmin\DocumentsBundle\Util\Documentdatatable;
use Clubadmin\DocumentsBundle\Util\Documentfilter;
use Clubadmin\DocumentsBundle\Util\Documenttablesetting;

/**
 * For create document query
 */
class DocumentData {

    protected $em;
    private $container;
    private $club;
    private $clubId;
    private $clubtype;
    private $conn;
    private $docType;
    private $request;
    private $session;
    private $contact;
    private $contactId;

    public function __construct(ContainerInterface $container, $docType) {
        $this->container = $container;
        $this->club = $this->container->get('club');
        $this->clubId = $this->club->get("id");
        $this->clubtype = $this->club->get("type");
        $this->conn = $this->container->get('database_connection');
        $this->em = $this->container->get('doctrine')->getManager();
        $this->docType = $docType;
        $this->session = $this->container->get('session');
        $this->contact = $this->container->get('contact');
        $this->contactId = $this->contact->get('id');
    }
    /**
     * Function to get document array for document listing
     * 
     * @return array
     */
    public function getDocumentList($request){
        $this->request = $request;
        $dataTableColumnData = $this->request->get('columns', '');
        $tablePostValue = $this->request->get('tableField', '');
        $filterPostValue = $this->request->get('filterdata', '');
        if ($filterPostValue != '') {
            if ($filterPostValue == '0') {
                $output = array( "iTotalRecords" => 0, "iTotalDisplayRecords" => 0,"aaData" => array() );
        
                return $output;
            }
        }
        //call a service for collect all relevant data related to the club
        $this->session->set('documentType', $this->docType);
        $filterValue = $sSearch = $nonQuotedColumns = $columnsArray = $tableField = '';
        $tabledatas = $aColumns = array();
        if ($tablePostValue != "") {
            $tableField = $tablePostValue;
            $jsonArray = $tablePostValue;
            $tabledatas = json_decode($jsonArray, true);
            $nonQuotedColumns = $tabledatas;
        }
        //For collect the table columns
        $aColumns = $this->getTableColumns($tabledatas);
        array_push($aColumns, 'docname', 'fdd.id as documentId', 'fdd.club_id as club_id', 'fg_dm_version.file as fileName', 'fg_dm_version.id as versionId', 'fdd.subcategory_id as subcatId');
        $documentlistClass = new Documentlist($this->container, $this->docType);
        $documentlistClass->setCount();
        $documentlistClass->setFrom();
        $documentlistClass->setCondition();
        $fieldArray = $this->getSearchableFields($this->docType);
        $searchPostValue = $this->request->get('search', '');
        if (is_array($searchPostValue) && $searchPostValue['value'] != "") {
            $sSearch = $searchPostValue['value'];
            $sWhere = $this->getAddCondition($searchPostValue, $fieldArray);
            $documentlistClass->addCondition($sWhere);
        }
        //For set the filter condition
        $this->setFilterConditions($documentlistClass, $filterPostValue, $searchPostValue);
        //call query for collect the data
        $totallistquery = $documentlistClass->getResult();
        $totalcontactlistDatas = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($totallistquery);
        //pagination handling area
        $paginationArgs = $this->setOrderAndLimit($documentlistClass, $dataTableColumnData);
        $documentlistClass->setColumns($aColumns);
        //call query for collect the data
        $listquery = $documentlistClass->getResult();
        $documentlistdata = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($listquery);
        
        foreach ($documentlistdata as $key => $value) {
            $documentlistdata[$key]['fedicon'] = FgUtility::getClubLogo($value['club_id'], $this->em);
        }
        //collect total number of records
        $totalrecords = (is_array($totalcontactlistDatas) && count($totalcontactlistDatas) > 0) ? $totalcontactlistDatas[0]['count']:0;
        $output = array( "iTotalRecords" => $totalrecords, "iTotalDisplayRecords" => $totalrecords, "aaData" => array());
        $this->session->set($this->contactId . $this->clubId, $totalrecords);
        // Section for next and previous functionality
        $flag = $this->session->get('documentFlag');
        if (isset($flag)) {
            $sessionContactlistDatas = $this->session->get('documentslistDatas');
            if ($documentlistdata === $sessionContactlistDatas) {
                $this->setSessionForNextPre($aColumns, $sSearch, $filterValue, $paginationArgs, $tableField, $dataTableColumnData);
                $this->session->set('documentFlag', 0);
            } else {
                $this->session->set('documentFlag', 1);
                $this->removeNextPrevSession();
                $this->session->set('documentslistDatas', $documentlistdata);
                $this->setSessionForNextPre($aColumns, $sSearch, $filterValue, $paginationArgs, $tableField, $dataTableColumnData);
            }
        } else {
            $this->session->set('documentFlag', 1);
            $this->setSessionForNextPre($aColumns, $sSearch, $filterValue, $paginationArgs, $tableField, $dataTableColumnData);
            $this->session->set('documentslistDatas', $documentlistdata);
        }
        //iterate the result
        $documentDatatabledata = new Documentdatatable($this->container);
        $output['aaData'] = $documentDatatabledata->iterateDataTableData($documentlistdata, $this->docType);
        $output['aaDataType'] = $this->getContactFieldDetails($tabledatas);
        $output['start'] = $this->request->get('start', '0');
        
        return $output;
    }
    /**
     * For set the limit and order in a query
     * 
     * @param object $documentlistClass   Document list class object
     * @param array  $dataTableColumnData Table column arrays
     * 
     * @return array
     */
    private function setOrderAndLimit($documentlistClass, $dataTableColumnData)
    {
        $displayStartPostValue = $this->request->get('start', '');
        if ($displayStartPostValue != '') {
            $iDisplayLength = $this->request->get('length');
            $paginationArg['iDisplayLength'] = $iDisplayLength;
            $paginationArg['limit'] = $displayStartPostValue;
            $documentlistClass->setLimit($displayStartPostValue);
            $documentlistClass->setOffset($iDisplayLength);
        }
        $sortColumnPostValue = $this->request->get('order', '');

        if ($sortColumnPostValue != "" && $dataTableColumnData[$sortColumnPostValue[0]['column']]['data'] != 'edit') {
            $paginationArg['iSortColVal'] = $sortColumnPostValue[0]['column'];
            $paginationArg['mDataProp'] = $dataTableColumnData[$sortColumnPostValue[0]['column']]['data'];
            $paginationArg['sSortDirVal'] = $sortColumnPostValue[0]['dir'];
            $sortColumnValue = $this->getSortColumnValue($sortColumnPostValue, $dataTableColumnData);
            $documentlistClass->addOrderBy($sortColumnValue);
            $this->session->set("sort-order" . $this->contactId . $this->clubId, $sortColumnValue);
        }

        return $paginationArg;
    }
    
    /**
     * For get the additional where condition for document query
     * 
     * @param array $searchPostValue Array contains search field values detail
     * @param array $fieldArray      Table column fields
     *
     * @return string
     */
    private function getAddCondition($searchPostValue, $fieldArray)
    {

        foreach ($fieldArray as $field) {
            $columns[] = $field;
        }
        $columns[] = 'docname';
        if (in_array('docname', $columns)) {
            $key = array_search('docname', $columns);
            unset($columns[$key]);
            $columns[] = "IF(fdi18.name_lang ='', fdd.name, fdi18.name_lang)";
        }

        $sWhere = "  (";
        foreach ($columns as $column) {
            $column = is_numeric($column) ? "`" . $column . "`" : $column;
            $searchVal = FgUtility::getSecuredDataString($searchPostValue['value'], $this->conn);
            $sWhere .= $column . " LIKE '%" . $searchVal . "%' OR ";
        }
        $sWhere = substr_replace($sWhere, "", -3);
        $sWhere .= ')';

        return $sWhere;
    }
    /**
     * For create the sort column of document listing query
     * 
     * @param array $sortColumnPostValue Sort column details
     * @param array $dataTableColumnData Table column details
     *
     * @return string
     */
    private function getSortColumnValue($sortColumnPostValue, $dataTableColumnData)
    {
        $mDataProp = $dataTableColumnData[$sortColumnPostValue[0]['column']]['data'];
        $sSortDirVal = $sortColumnPostValue[0]['dir'];
        $sortColumn = $mDataProp;
        $sortColumnValue = '';
        if ($sortColumn == 'CO_FO_VISIBLE_TO' || $sortColumn == 'WG_FO_VISIBLE_TO' || $sortColumn == 'CL_FO_VISIBLE_TO') {
            $sortColumnValue = $sortColumn . " " . $sSortDirVal;
        }else if ($sortColumn == 'CO_FO_ISPUBLIC' || $sortColumn == 'WG_FO_ISPUBLIC' || $sortColumn == 'CL_FO_ISPUBLIC') {
            $sortColumnValue = $sortColumn . " " . $sSortDirVal;
        }
        elseif ((strpos($sortColumn, 'DO_UPLOADED') !== false ) || (strpos($sortColumn, 'DO_LAST_UPDATED') !== false )) {
            $modifiiedsortColumn = (strpos($sortColumn, 'DO_UPLOADED') !== false) ? 'fg_dm_version.created_at' : 'fg_dm_version.updated_at';
            $sortColumnValue = " (CASE WHEN " . $modifiiedsortColumn . " IS NULL then 4 WHEN " . $modifiiedsortColumn . "='' then 3 WHEN " . $modifiiedsortColumn . "='0000-00-00 00:00:00' then 2 WHEN " . $modifiiedsortColumn . "='-' then 1 ELSE 0 END)," . $modifiiedsortColumn . " " . $sSortDirVal;
        } else {
            $sortColumnValue = " (CASE WHEN " . $sortColumn . " IS NULL then 4 WHEN " . $sortColumn . "='' then 3 WHEN " . $sortColumn . "='0000-00-00 00:00:00' then 2 WHEN " . $sortColumn . "='-' then 1 ELSE 0 END)," . $sortColumn . " " . $sSortDirVal;
        }

        return $sortColumnValue;
    }
    
    /**
     * Function to get table settings for document listing
     * 
     * @param array  $tabledatas Data table details
     * @param string $doctype    Document type
     *
     * @return array
     */
    private function getTableColumns($tabledatas)
    {
        if (is_array($tabledatas) && count($tabledatas) > 0) {
            $table = new Documenttablesetting($this->container, $tabledatas, $this->club, $this->docType);
            $aColumns = $table->getDocColumns();
        } else {
            $aColumns = array();
        }

        return $aColumns;
    }
    /**
     * Function to get the searchable field of documents list
     * 
     * @param string $docType Document type CLUB/CONTACT/TEAM/WORKGROUP
     *
     * @return array
     */
    private function getSearchableFields($docType)
    {
        $fieldArray = array();
        if(in_array($docType, array("CLUB","CONTACT","TEAM","WORKGROUP"))){
           $fieldArray = array('description' => "fdd.description", 'author' => "fdd.author", "docname" => "IF(fdi18.name_lang IS NULL, fdd.name, fdi18.name_lang)");
        }

        return $fieldArray;
    }
    
    /**
     * For set the filter condition of the document
     * 
     * @param object $documentlistClass Documentlist class object
     * @param array  $filterPostValue   Filter data
     * @param array  $searchPostValue   POST value array with search options
     *
     * @return string $filterString
     */
    private function setFilterConditions($documentlistClass, $filterPostValue, $searchPostValue)
    {
        if ($filterPostValue != "all" && $filterPostValue != '') {
            $filterData = array_shift($filterPostValue);
            $filterObj = new Documentfilter($this->container, $filterData, $this->docType);
            $filterString = $filterObj->generateDocumentFilter();
            if (isset($searchPostValue['value']) && $searchPostValue['value'] != "") {
                $sWhere .= ($filterString != '') ? " (" . $filterString . ")" : '';
            } else {
                $sWhere .= " (" . $filterString . ")";
            }
            $documentlistClass->addCondition($sWhere);

            return $filterString;
        }
    }
    /**
     * Function to remove session related to Next and previous links
     */
    private function removeNextPrevSession()
    {
        $this->session->remove('documentsColumnsArray');
        $this->session->remove('documentsFilteredContactDetailsiSortCol_0');
        $this->session->remove('documentsFilteredContactDetailsmDataProp');
        $this->session->remove('documentsFilteredContactDetailsSortDir_0');
        $this->session->remove('documentsFilteredContactDetailsSearch');
        $this->session->remove('documentsFilteredContactDetailsFilterdata');
        $this->session->remove('nextPreviousDocumentListData');
        $this->session->remove('documentsTableField');
        $this->session->remove('documentsFilteredContactDetailsDisplayLength');
        $this->session->remove('documentsDataTableColumnData');
        $this->session->remove('documentsLimitVal');
        $this->session->remove('documentsColumnsArray');
    }
    
    /**
     * Function to set session related to Next and previous links
     * 
     * @param array  $columnsArray        Tablecolumns
     * @param string $sSearch             SearchValue 
     * @param array  $filterdata          filterdata
     * @param array  $paginationArgs      Pagination argument values
     * @param array  $tableField          tablefield
     * @param array  $dataTableColumnData Column data of the datatable
     */
    private function setSessionForNextPre($columnsArray, $sSearch, $filterdata, $paginationArgs, $tableField, $dataTableColumnData)
    {
        $limitVal = $paginationArgs['limit'];
        $iDisplayLength = $paginationArgs['iDisplayLength'];
        $iSortColVal = $paginationArgs['iSortColVal'];
        $mDataProp = $paginationArgs['mDataProp'];
        $sSortDirVal = $paginationArgs['sSortDirVal'];
        if (isset($tableField) && $tableField != '') {
            $this->session->set('documentsTableField', $tableField);
        }
        if (isset($dataTableColumnData) && $dataTableColumnData != '') {
            $this->session->set('documentsDataTableColumnData', $dataTableColumnData);
        }
        if (isset($limitVal) && $limitVal != '') {
            $this->session->set('documentsLimitVal', $limitVal);
        }
        if (isset($columnsArray) && $columnsArray != '') {
            $this->session->set('documentsColumnsArray', $columnsArray);
        }
        if (isset($sSearch) && $sSearch != "") {
            $this->session->set('documentsFilteredContactDetailsSearch', $sSearch);
        }
        if (isset($filterdata) && $filterdata != "contact" && $filterdata != '') {
            $this->session->set('documentsFilteredContactDetailsFilterdata', $filterdata);
        }
        if (isset($iDisplayLength) && $iDisplayLength != '') {
            $this->session->set('documentsFilteredContactDetailsDisplayLength', $iDisplayLength);
        }
        if (isset($iSortColVal) && $iSortColVal != "" && $mDataProp != 'edit') {
            $this->session->set('documentsFilteredContactDetailsiSortCol_0', $iSortColVal);
            $this->session->set('documentsFilteredContactDetailsmDataProp', $mDataProp);
            $this->session->set('documentsFilteredContactDetailsSortDir_0', $sSortDirVal);
        }
    }
    
    /**
     * Function to get the type of selected document fields
     *
     * @return array
     */
    private function getContactFieldDetails()
    {
        $output = array();
        $output[] = array("title" => 'docname', "type" => "docname");
        $output[] = array("title" => 'edit', "type" => "edit");
        //club
        $output[] = array("title" => 'CL_FO_SIZE', "type" => "CL_FO_SIZE");
        $output[] = array("title" => 'CL_FO_VISIBLE_TO', "type" => "CL_FO_VISIBLE_TO");
        $output[] = array("title" => 'CL_FO_DESCRIPTION', "type" => "CL_FO_DESCRIPTION");
        $output[] = array("title" => 'CL_FO_DEPOSITED_WITH', "type" => "CL_FO_DEPOSITED_WITH");
        $output[] = array("title" => 'CL_FO_ISPUBLIC', "type" => "CL_FO_ISPUBLIC"); 

        //contact
        $output[] = array("title" => 'CO_FO_SIZE', "type" => "CO_FO_SIZE");
        $output[] = array("title" => 'CO_FO_DESCRIPTION', "type" => "CO_FO_DESCRIPTION");
        $output[] = array("title" => 'CO_FO_VISIBLE_TO', "type" => "CO_FO_VISIBLE_TO");
        $output[] = array("title" => 'CO_FO_DEPOSITED_WITH', "type" => "CO_FO_DEPOSITED_WITH");
        $output[] = array("title" => 'CO_FO_ISPUBLIC', "type" => "CO_FO_ISPUBLIC"); 
        //workgroup
        $output[] = array("title" => 'WG_FO_SIZE', "type" => "WG_FO_SIZE");
        $output[] = array("title" => 'WG_FO_DEPOSITED_WITH', "type" => "WG_FO_DEPOSITED_WITH");
        $output[] = array("title" => 'WG_FO_VISIBLE_TO', "type" => "WG_FO_VISIBLE_TO");
        $output[] = array("title" => 'WG_FO_DESCRIPTION', "type" => "WG_FO_DESCRIPTION");
        $output[] = array("title" => 'WG_FO_ISPUBLIC', "type" => "WG_FO_ISPUBLIC"); 
        //team
        $output[] = array("title" => 'T_FO_VISIBLE_TO', "type" => "T_FO_VISIBLE_TO");
        $output[] = array("title" => 'T_FO_SIZE', "type" => "T_FO_SIZE");
        $output[] = array("title" => 'T_FO_DEPOSITED_WITH', "type" => "T_FO_DEPOSITED_WITH");
        $output[] = array("title" => 'T_FO_DESCRIPTION', "type" => "T_FO_DESCRIPTION");
        $output[] = array("title" => 'visibleFor', "type" => "visibleFor");
        $output[] = array("title" => 'T_FO_ISPUBLIC', "type" => "T_FO_ISPUBLIC");
        $output[] = array("title" => 'IsPublic', "type" => "IsPublic");

        return $output;
    }
}