<?php

namespace Clubadmin\ClubBundle\Util;

use Clubadmin\Classes\Clublist;
use Clubadmin\Classes\Clubfilter;
use Clubadmin\Classes\Clubtablesetting;
use Common\UtilityBundle\Util\FgUtility;

/**
 * Used to handling different  club list functions
 *
 * @author  pitsolutions.ch <pit@solutions.com>
 * @version Release: <v4>
 */
class ClublistData
{

    /**
     * $em
     * @var object entitymanager object
     */
    private $em;
    private $contactId;
    private $clubId;
    private $clubType;

    /**
     * $club
     * @var Service {clubservice}
     */
    private $club;
    private $container;
    private $session;

    /**
     * json decoded table column array
     * @var array
     */
    public $tabledata = array();
    public $searchval = array();
    public $roleFilter = '';
    public $functionTypeValue = '';
    public $startValue = '';
    public $selectedIds = '';

    /**
     * direct setting of sorting details as a string
     * @var String
     */
    public $sortColumnDetails = '';

    /**
     * column field array
     * @var array
     */
    public $aoColumns = array();

    /**
     * datatabel column details
     * @var type
     */
    public $dataTableColumnData;
    public $filterValue = '';

    /**
     * Contains the detail to create the sorting
     * @var array
     */
    public $sortColumnValue;

    /**
     * json array values
     * @var string
     */
    public $tableFieldValues = '';
    public $displayLength = '';
    public $sSortDir = '';
    public $conn;

    /**
     * Constructor for initial setting
     *
     * @param type $contactId   contact   id
     * @param type $container   container
     */
    public function __construct($contactId, $container)
    {

        $this->contactId = $contactId;
        $this->container = $container;
        $this->club = $this->container->get('club');
        $this->em = $this->container->get('doctrine')->getManager();
        $this->session = $this->container->get('session');
        $this->clubId = $this->club->get('id');
        $this->clubType = $this->club->get('type');
        $this->defaultSystemLang = $this->club->get('default_system_lang');
        $this->conn = $this->container->get('database_connection');
    }

    /**
     * To create list query according to the settings and give result
     *
     * @param type $isQuery       Return query flag
     * @param type $nextPreFlag   Next previous flag
     * @param type $currentOffset Current offset value of contact
     *
     * @return array
     */
    public function getClubData($isQuery = false, $nextPreFlag = '', $currentOffset = '')
    {
        if ($this->tableFieldValues != "") {
            $this->tabledata = json_decode($this->tableFieldValues, true);
        }
        // if aocolumn value  is empty, set its value
        if (empty($this->aoColumns)) {
            $this->aoColumns = $this->getTableColumns();
        }
        //to initialize contact list class
        $clublistClass = $this->initializeClubList();
        //set search fields
        $sWhere = $this->setSearchFields();

        $clublistClass->addCondition($sWhere);
        //set filterconditions
        $sWhere = $this->setFilterConditions($clublistClass);
        $clublistClass->addCondition($sWhere);

        if ($this->selectedIds != '') {
            $sWhere = "fc.id  IN( $this->selectedIds )";
            $clublistClass->addCondition($sWhere);
        }

        //pagination handling area
        // Checking whether it is called for next and previous functionality
        if ($nextPreFlag != 1) {
            //call query for collect the data
            $totallistquery = $clublistClass->getResult();
            $totalcontactlistDatas = $this->container->get('fg.admin.connection')->executeQuery($totallistquery);
            
            if ($this->startValue != '') {
                $clublistClass->setLimit($this->startValue);
                $clublistClass->setOffset($this->displayLength);
            }
        } elseif ($nextPreFlag == 1) {
            if (isset($currentOffset)) {
                if ($currentOffset - 5 > 0) {
                    $limitStart = $currentOffset - 5;
                } else {
                    $limitStart = '0';
                }
                $clublistClass->setLimit($limitStart);
                $clublistClass->setOffset($this->displayLength);
            }
        }

        if ($this->sortColumnValue != "" && $this->dataTableColumnData[$this->sortColumnValue[0]['column']]['data'] != 'edit') {
            $mDataProp = $this->dataTableColumnData[$this->sortColumnValue[0]['column']]['data'];
            $sSortDirVal = ($nextPreFlag == 1) ? $this->sSortDir : $this->sortColumnValue[0]['dir'];
            $sortColumn = $mDataProp;
            $sortColumn = $this->findSortColumn($sortColumn);
            if ($sortColumn == "CF_created_at") {
                $sortColumn = "fc.created_at";
            }
            $sortColumnValue = " (CASE WHEN " . $sortColumn . " = 0 then 4 WHEN " . $sortColumn . " IS NULL then 3 WHEN (" . $sortColumn . "='' and " . $sortColumn . "!='0.00') then 2  WHEN (" . $sortColumn . "='0000-00-00 00:00:00' and " . $sortColumn . "!='0.00' )then 1 ELSE 0 END)," . $sortColumn . " " . $sSortDirVal;
            $clublistClass->addOrderBy($sortColumnValue);
            $this->session->set("sort-order" . $this->clubType . $this->clubId, $sortColumnValue);
        }
        //direct value setting to order by condition
        if ($this->sortColumnDetails != '') {
            $clublistClass->addOrderBy($this->sortColumnDetails);
        }
        $clublistClass->setColumns($this->aoColumns);
        //call query for collect the data
        $listquery = $clublistClass->getResult();
        file_put_contents("query.txt", $listquery . "\n");
        // Checking whether return query
        if ($isQuery) {
            return $listquery;
        }
        $results = array("totalcount" => 0, 'data' => '');
        $results["data"] = $this->container->get('fg.admin.connection')->executeQuery($listquery);
        if (is_array($totalcontactlistDatas) && count($totalcontactlistDatas) > 0) {
            $results["totalcount"] = $totalcontactlistDatas[0]['count'];
        }

        return $results;
    }

    /**
     * To create where condition according to the filter settings
     * @param object $clublistClass contactlist class object
     *
     * @return string
     */
    public function setFilterConditions($clublistClass)
    {
        $sWhere = '';
        if ($this->filterValue != "all") {
            $filterValue = $this->filterValue;
            $filterData = array_shift($filterValue);
            if ($filterData != '') {
                //for create filter condition from filter array
                $filterObj = new Clubfilter($this->container, $clublistClass, $filterData, $this->club);
                $filterString = $filterObj->generateClubFilter();
                $sWhere .= " (" . $filterString . ")";
            }
        }

        return $sWhere;
    }

    /**
     * To setting the column values array
     *
     * @return array
     */
    public function getTableColumns()
    {
        $aColumns = array();
        //push function type column field to tabledata array
        if (is_array($this->tabledata) && count($this->tabledata) > 0) {
            $table = new Clubtablesetting($this->container, $this->tabledata, $this->club);
            $aColumns = $table->getClubColumns();
        }
        array_unshift($aColumns, 'fc.id', 'clubname');

        return $aColumns;
    }

    /**
     * To initialize the contact class
     * @return \Clubadmin\Classes\contactlist
     */
    public function initializeClubList()
    {
        $clublistClass = new Clublist($this->container, $this->club);
        $clublistClass->setCount();
        $clublistClass->setFrom();
        $clublistClass->setCondition();

        return $clublistClass;
    }

    /**
     * To creating the serach field condition from the search value setting
     * @return string
     */
    public function setSearchFields()
    {
        $sWhere = '';
        if (is_array($this->searchval) && $this->searchval['value'] != '') {
            $searchcolumns = $this->aoColumns;
            foreach ($searchcolumns as $searchcolumn) {
                //for remove the alias name from the fields
                if (strrpos($searchcolumn, "AS ") !== false) {
                    $columns[] = substr($searchcolumn, 0, strrpos($searchcolumn, "AS "));
                    $colNames[] = substr($searchcolumn, (strrpos($searchcolumn, "AS ") + 2));
                }
            }

            //for collecting all Contact fields in one array
            $sWhere = '(';
            //set search conditions

            $country_fields = $this->container->getParameter('country_fields');
            $corress_lang = $this->container->getParameter('system_field_corress_lang');
            $gender = $this->container->getParameter('system_field_gender');
            $salutaion = $this->container->getParameter('system_field_salutaion');

            foreach ($columns as $key => $column) {
                if (preg_replace("/\s+/", "", $colNames[$key]) == 'CF_C_country' || preg_replace("/\s+/", "", $colNames[$key]) == 'CF_I_country') { // Search from fg_search_countries table
                    $searchVal = FgUtility::getSecuredDataString($this->searchval['value'], $this->conn);
                    $sWhere .= $column . " IN (SELECT short_name FROM fg_search_countries WHERE title LIKE '%" . $searchVal . "%' AND fg_search_countries.lang='$this->defaultSystemLang' ) OR "; //AND lang='$this->defaultSystemLang'
                } elseif (preg_replace("/\s+/", "", $colNames[$key]) == 'CF_language') {    // Search from fg_search_lang table
                    $searchVal = FgUtility::getSecuredDataString($this->searchval['value'], $this->conn);
                    $sWhere .= $column . " IN (SELECT short_name FROM fg_search_lang WHERE title LIKE '%" . $searchVal . "%' AND fg_search_lang.lang='$this->defaultSystemLang' ) OR ";
                } else {
                    $searchVal = FgUtility::getSecuredDataString($this->searchval['value'], $this->conn);
                    $sWhere .= $column . " LIKE '%" . $searchVal . "%' OR ";
                }
            }

            //static colums
            $scolumns = $this->getSearchFields();
            foreach ($scolumns as $column) {
                $searchVal = FgUtility::getSecuredDataString($this->searchval['value'], $this->conn);
                $sWhere.= $column . " LIKE '%" . $searchVal . "%' OR ";
            }

            $sWhere = substr_replace($sWhere, '', -3);
            $sWhere .= ')';
        }

        return $sWhere;
    }

    /**
     * To setting the session values
     * @param type $contactlistDatas contact list data
     */
    public function setSessionValues($clublistDatas)
    {
        $flag = $this->session->get('clubflag');
        if (isset($flag)) {
            $sessionClublistDatas = $this->session->get('clublistDatas');
            //For check  seesion array and current getting array is same
            if ($clublistDatas === $sessionClublistDatas) {
                $this->session->set('clubflag', 0);
            } else {
                $this->session->set('clubflag', 1);
                $this->removeNextPrevSession();
                $this->session->set('clublistDatas', $clublistDatas);
            }
        } else {
            $this->session->set('clubflag', 1);
            $this->session->set('clublistDatas', $clublistDatas);
        }
        //For setting the all session related to the datatable value
        $this->setSessionForNextPre();
    }

    /**
     * Function to set session related to Next and previous links
     */
    public function setSessionForNextPre()
    {
        if (isset($this->tableFieldValues) && $this->tableFieldValues != '') {
            $this->session->set('clubtableField', $this->tableFieldValues);
        }
        $this->session->set('clubcolumnsArray', $this->aoColumns);
        if (isset($this->searchval) && $this->searchval['value'] != "") {
            $this->session->set('filteredClubDetailsSearch', $this->searchval);
        }
        if (isset($this->filterValue) && $this->filterValue != "contact" && $this->filterValue != '') {
            $this->session->set('filteredClubDetailsFilterdata', $this->filterValue);
        }
        if (isset($this->displayLength) && $this->displayLength != '') {
            $this->session->set('filteredClubDetailsDisplayLength', $this->displayLength);
        }
        if (isset($this->sortColumnValue[0]['column']) && $this->sortColumnValue[0]['column'] != "" && $this->dataTableColumnData[$this->sortColumnValue[0]['column']]['data'] != 'edit') {
            $this->session->set('filteredClubDetailsiSortCol_0', $this->sortColumnValue);
            $this->session->set('filteredClubDetailsmDataProp', $this->dataTableColumnData[$this->sortColumnValue[0]['column']]['data']);
            $this->session->set('filteredClubDetailsSortDir_0', $this->sortColumnValue[0]['dir']);
        }
    }

    /**
     * Function to remove session related to Next and previous links
     */
    private function removeNextPrevSession()
    {
        $this->session->remove('clubcolumnsArray');
        $this->session->remove('filteredClubDetailsiSortCol_0');
        $this->session->remove('filteredClubDetailsmDataProp');
        $this->session->remove('filteredClubDetailsSortDir_0');
        $this->session->remove('filteredClubDetailsSearch');
        $this->session->remove('filteredClubDetailsFilterdata');
        $this->session->remove('nextPreviousClubListData');
        $this->session->remove('clubtableField');
    }

    /**
     * For set the search column fields
     * @return array column fields array
     */
    private function getSearchFields()
    {
        $searchColumns[] = 'fc.title';
        $searchColumns[] = "fc.email";
        $searchColumns[] = "fc.url_identifier";
        $searchColumns[] = "fc.website";

        return $searchColumns;
    }

    /**
     * To find the sorted column
     * @param type $sortColumn
     * @return type
     */
    private function findSortColumn($sortColumn)
    {
        $flag = 0;
        foreach ($this->tabledata as $datafields) {
            if (in_array($sortColumn, $datafields)) {
                $flag = 1;
            }
        }
        return ($flag == 1) ? $sortColumn : 'clubname';
    }
    private function checkfun($query) {
        
        $connection = $this->adminEm->getConnection();
        $statement = $connection->prepare($query);
        
        $statement->execute();
        $results = $statement->fetchAll();
        return $results;
    }
}
