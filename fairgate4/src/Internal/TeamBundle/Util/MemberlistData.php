<?php

namespace Internal\TeamBundle\Util;

use Common\UtilityBundle\Util\FgUtility;

/**
 * Used to handling different  contact list functions.
 *
 * @author  pitsolutions.ch <pit@solutions.com>
 *
 * @version Release: <v4>
 */
class MemberlistData
{

    /**
     * $em.
     *
     * @var object entitymanager object
     */
    private $em;
    private $contactId;
    private $clubId;

    /**
     * $club.
     *
     * @var Service {clubservice}
     */
    private $club;
    private $container;
    private $session;
    public $defaultSystemLang;

    /**
     * json decoded table column array.
     *
     * @var array
     */
    public $tabledata = array();
    public $searchval = array();
    public $startValue = '';
    public $groupByColumn = '';

    /**
     * direct setting of sorting details as a string.
     *
     * @var String
     */
    public $sortColumnDetails = '';

    /**
     * column field array.
     *
     * @var array
     */
    public $aoColumns = array();

    /**
     * datatabel column details.
     *
     * @var type
     */
    public $dataTableColumnData;
    public $filterValue = '';

    /**
     * Contains the detail to create the sorting.
     *
     * @var array
     */
    public $sortColumnValue;

    /**
     * json array values.
     *
     * @var string
     */
    public $tableFieldValues = '';
    public $displayLength = '';
    public $sSortDir = '';
    public $memberId, $memberCategoryId;
    public $memberlistType = 'team';
    public $adminFlag;
    public $extraCond = '';
    public $editConf = 0;
    /**
     *
     * @var int
     */
    public $conn;

    /**
     * Constructor for initial setting.
     *
     * @param type $contactId   contact   id
     * @param type $container   container
     * @param type $contactType (active/archive/formerfederationmember)
     */
    public function __construct($container, $contactId)
    {
        $this->contactId = $contactId;
        $this->container = $container;
        $this->club = $this->container->get('club');
        $this->em = $this->container->get('doctrine')->getManager();
        $this->session = $this->container->get('session');
        $this->clubId = $this->club->get('id');
        $this->defaultSystemLang = $this->club->get('default_system_lang');
        $this->conn = $this->container->get('database_connection');
    }

    /**
     * To create list query according to the settings and give result.
     *
     * @param type $isQuery       Return query flag
     * @param type $nextPreFlag   Next previous flag
     * @param type $currentOffset Current offset value of contact
     *
     * @return array
     */
    public function getMemberlistData()
    {
        if ($this->tableFieldValues != '') {
            $this->tabledata = json_decode($this->tableFieldValues, true);
        }
        // if aocolumn value  is empty, set its value
        if (empty($this->aoColumns)) {
            $this->aoColumns = $this->getTableColumns();
        }
        //to initialize contact list class
        $memberlistClass = $this->initializeMemberList();
        //set search fields
        $sWhere = $this->setSearchFields();

        $memberlistClass->addCondition($sWhere);
        $totallistquery = $memberlistClass->getResult();
        $totalmemberlistDatas = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($totallistquery);
        //pagination handling area

        if ($this->sortColumnValue != '' && $this->dataTableColumnData[$this->sortColumnValue[0]['column']]['data'] != 'edit') {
            $mDataProp = $this->dataTableColumnData[$this->sortColumnValue[0]['column']]['data'];
            $sSortDirVal = $this->sortColumnValue[0]['dir'];
            $sortColumn = $mDataProp;
            $sortColumn = $this->findSortColumn($sortColumn);
            $splitColumn = explode('_', $mDataProp);
            foreach ($this->club->get('allContactFields') as $sortFields) {
                if (in_array('CF', $splitColumn) && $splitColumn[1] == $sortFields['id']) {
                    switch ($sortFields['type']) {
                        case 'number':
                            $sortColumn = 'CAST(`' . $sortColumn . '` as DECIMAL(10,5))';
                            break;
                    }
                }
            }
            $sortColumnValue = ' (CASE WHEN ' . $sortColumn . ' IS NULL then 3 WHEN ' . $sortColumn . "='' then 2 WHEN " . $sortColumn . "='0000-00-00 00:00:00' then 1 ELSE 0 END)," . $sortColumn . ' ' . $sSortDirVal;
            $memberlistClass->addOrderBy($sortColumnValue);
            $this->session->set('sort-order' . $this->contactId . $this->clubId, $sortColumnValue);
        }
        //Extra condition
        if ($this->extraCond != '') {

            $memberlistClass->addCondition($this->extraCond);
        }

        if ($this->startValue != '') {
            $memberlistClass->setLimit($this->startValue);
            $memberlistClass->setOffset($this->displayLength);
        }
        //direct value setting to order by condition

        if ($this->groupByColumn != '') {
            $memberlistClass->setGroupBy($this->groupByColumn);
        }

        if ($this->sortColumnDetails != '') {
            $memberlistClass->addOrderBy($this->sortColumnDetails);
        }
        if($this->editConf==1){
            $memberlistClass->editContact =1;
        }
        $memberlistClass->setColumns($this->aoColumns);
        //call query for collect the data
        $listquery = $memberlistClass->getResult();
       // echo $listquery;
        //die;
        file_put_contents('query.txt', $listquery . "\n");

        $results = array('totalcount' => 0, 'data' => '');

        $results['data'] = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($listquery);
        if (is_array($totalmemberlistDatas) && count($totalmemberlistDatas) > 0) {
            $results['totalcount'] = $totalmemberlistDatas[0]['count'];
        }

        return $results;
    }

    /**
     * To setting the column values array.
     *
     * @return array
     */
    public function getTableColumns()
    {
        $aColumns = array();
        //push function type column field to tabledata array
        array_push($this->tabledata, array('id' => '', 'type' => 'Fn', 'sub_ids' => $this->memberId, 'club_id' => 1, 'name' => 'Function', 'fnType' => $this->memberlistType));
        if (is_array($this->tabledata) && count($this->tabledata) > 0) {
            $table = new Tablesettings($this->container, $this->tabledata, $this->adminFlag, $this->memberId, $this->memberlistType);
            $aColumns = $table->getColumns();
        }
        array_push($aColumns, 'contactid', 'contactname', 'isMember', 'gender', 'isCompany', 'contactclubid');

        return $aColumns;
    }

    /**
     * To initialize the contact class.
     *
     * @return \Clubadmin\Classes\contactlist
     */
    public function initializeMemberList()
    {
        $memberlistClass = new Memberlist($this->container, $this->contactId, $this->memberlistType, $this->memberId, $this->memberCategoryId);
        $memberlistClass->isAdmin = $this->adminFlag;
        $memberlistClass->setCount();
        $memberlistClass->setFrom();
        $memberlistClass->setCondition();

        return $memberlistClass;
    }

    /**
     * To creating the serach field condition from the search value setting.
     *
     * @return string
     */
    public function setSearchFields()
    {
        if (is_array($this->searchval) && $this->searchval['value'] != '') {

            foreach ($this->aoColumns as $searchcolumn) {

                $strPosition = strrpos($searchcolumn, "AS ");
                $removedString = ($strPosition !== false) ? substr($searchcolumn, $strPosition, strlen($searchcolumn)) : '';
                $isExistflag = false;
                if ($removedString != '') {
                    foreach ($this->tabledata as $columnData) {
                        if (strstr($removedString, $columnData['name'] . "_visibility") != false) {
                            $isExistflag = true;
                        } else if (strstr($removedString, $columnData['name'] . "_Flag") != false) {
                            $isExistflag = true;
                        }
                    }
                }
                //for remove the alias name from the fields
                if ($strPosition !== false && $isExistflag == false) {
                    $columns[] = substr($searchcolumn, 0, strrpos($searchcolumn, "AS "));
                }
            }
            //to set the fields for contact name
            $columns[] = "`" . $this->container->getParameter('system_field_firstname') . "`";
            $columns[] = "`" . $this->container->getParameter('system_field_lastname') . "`";
            $columns[] = "`" . $this->container->getParameter('system_field_companyname') . "`";
            $sWhere = '(';

            $country_fields = $this->container->getParameter('country_fields');
            $corress_lang = $this->container->getParameter('system_field_corress_lang');
            $gender = $this->container->getParameter('system_field_gender');
            $salutaion = $this->container->getParameter('system_field_salutaion');
            //set search conditions

            foreach ($columns as $column) {
                if (in_array(preg_replace("/[^0-9]/", "", $column), $country_fields)) {
                    $searchVal = FgUtility::getSecuredDataString($this->searchval['value'], $this->conn);
                    $sWhere .= $column . " IN (SELECT short_name FROM fg_search_countries WHERE title LIKE '%" . $searchVal . "%' AND fg_search_countries.lang='$this->defaultSystemLang' ) OR "; //and lang='$this->defaultSystemLang'
                } elseif (preg_replace("/[^0-9]/", "", $column) == $corress_lang) {
                    $searchVal = FgUtility::getSecuredDataString($this->searchval['value'], $this->conn);
                    $sWhere .= $column . " IN (SELECT short_name FROM fg_search_lang WHERE title LIKE '%" . $searchVal . "%' AND fg_search_lang.lang='$this->defaultSystemLang' ) OR ";
                } elseif (preg_replace("/[^0-9]/", "", $column) == $gender) {
                    $searchVal = FgUtility::getSecuredDataString($this->searchval['value'], $this->conn);
                    $sWhere .= $column . " IN (SELECT title FROM fg_search_misc WHERE translation LIKE '%" . $searchVal . "%' and fg_search_misc.type='gender' AND fg_search_misc.lang='$this->defaultSystemLang' ) OR ";
                } elseif (preg_replace("/[^0-9]/", "", $column) == $salutaion) {
                    $searchVal = FgUtility::getSecuredDataString($this->searchval['value'], $this->conn);
                    $sWhere .= $column . " IN (SELECT title FROM fg_search_misc WHERE translation LIKE '%" . $searchVal . "%' and fg_search_misc.type='salutation' AND fg_search_misc.lang='$this->defaultSystemLang' ) OR ";
                } else {
                    $searchVal = FgUtility::getSecuredDataString($this->searchval['value'], $this->conn);
                    $sWhere .= $column . " LIKE '%" . $searchVal . "%' OR ";
                }
            }
            $sWhere = substr_replace($sWhere, '', -3);
            $sWhere .= ')';
        }

        return $sWhere;
    }

    /**
     * To remove the unwanted column from the array.
     *
     * @return array
     */
    public function removeColumns()
    {
        $tablecolumns = $this->aoColumns;
        //contactname column check in array
        if (in_array('contactname', $this->aoColumns)) {
            $key = array_search('contactname', $this->aoColumns); //
            unset($tablecolumns[$key]);
        }
        //contactid column check in array
        if (in_array('contactid', $this->aoColumns)) {
            $key = array_search('contactid', $this->aoColumns); //
            unset($tablecolumns[$key]);
        }

        return $tablecolumns;
    }

    /**
     * To find the sorted column.
     *
     * @param type $sortColumn
     *
     * @return type
     */
    private function findSortColumn($sortColumn)
    {
        $flag = 0;
        $tabledata = $this->tabledata;

        foreach ($tabledata as $datafields) {
            if (in_array($sortColumn, $datafields)) {
                $flag = 1;
            } elseif (($sortColumn == 'joining_date' || $sortColumn == 'leaving_date') && in_array('join_leave_dates', $datafields)) {
                $flag = 1;
            }
        }

        return ($flag == 1) ? $sortColumn : 'contactname';
    }
}
