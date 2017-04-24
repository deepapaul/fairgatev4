<?php namespace Clubadmin\ContactBundle\Util;

use Clubadmin\Util\Contactlist;
use Clubadmin\Util\Contactfilter;
use Clubadmin\Util\Tablesettings;
use Website\CMSBundle\Util\FgTablesettings;
use Clubadmin\SponsorBundle\Util\Sponsorfilter;
use Common\UtilityBundle\Util\FgUtility;
use Website\CMSBundle\Classes\FgContactFrontTableFilters;

/**
 * Used to handling different  contact list functions.
 *
 * @author  pitsolutions.ch <pit@solutions.com>
 *
 * @version Release: <v4>
 */
class ContactlistData
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
    public $contactType = 'contact';
    public $searchval = array();
    public $roleFilter = '';
    public $functionTypeValue = 'none';
    public $startValue = '';
    public $selectedIds = '';
    public $excludedIds = '';
    public $includedIds = '';
    public $groupByColumn = '';

    /**
     * The columns in which text is to be searched
     * 
     * @var array 
     */
    public $searchableColumns = array();

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

    /**
     * Default system language.
     *
     * @var String
     */
    public $isExtraColumn = true;

    /**
     * Default parameters for export.
     *
     * @var type
     */
    public $exportFlag = false;

    /**
     * Special parameter for handle frondend contact table filter(contact table elemnt filter)
     * @var  
     */
    public $specialFilter = '';
    public $exportSearchColumns = array();
    public $searchTableData = array();
    public $nextPreFlag = '';
    public $currentOffset = '';
    public $conn;

    /**
     * For shandle separate listing in portrait elemnt
     * @var boolean 
     */
    public $separateList = false;

    /**
     * For handle separate listing in portrait elemnt
     * @var Array 
     */
    public $separateListingDetails = '';

    /**
     * For handle separate list depend columns
     * @var type 
     */
    public $dependColumns = array();

    /**
     * 
     */
    public $tableSetting;

    /**
     * Constructor for initial setting.
     *
     * @param type $contactId   contact   id
     * @param type $container   container
     * @param type $contactType (active/archive/formerfederationmember)
     */
    public function __construct($contactId, $container, $contactType, $tableSetting = 'Clubadmin')
    {
        $this->contactId = $contactId;
        $this->container = $container;
        $this->club = $this->container->get('club');
        $this->em = $this->container->get('doctrine')->getManager();
        $this->session = $this->container->get('session');
        $this->contactType = $contactType;
        $this->clubId = $this->club->get('id');
        $this->defaultSystemLang = $this->club->get('default_system_lang');
        $this->conn = $this->container->get('database_connection');
        $this->tableSetting = $tableSetting;
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
    public function getContactData($isQuery = false, $nextPreFlag = '', $currentOffset = '')
    {
        if ($this->tableFieldValues != '') {
            $this->tabledata = json_decode($this->tableFieldValues, true);
        }
        // if aocolumn value  is empty, set its value
        if (empty($this->aoColumns)) {
            $this->aoColumns = $this->getTableColumns();
        }
        //to initialize contact list class
        $contactlistClass = $this->initializeContactList();

        //export selected contacts from contact list

        if ($this->selectedIds != '') {
            $sWhere = "fg_cm_contact.id  IN( $this->selectedIds )";
            $contactlistClass->addCondition($sWhere);
        }

        //excluded contact ids 
        if ($this->excludedIds != '') {
            $sWhere = "fg_cm_contact.id  NOT IN( $this->excludedIds )";
            $contactlistClass->addCondition($sWhere);
        }

        //set filterconditions
        $sWhere = $this->setFilterConditions($contactlistClass);
        $contactlistClass->addCondition($sWhere);
        if ($this->includedIds != '') {
            $sWhere = "fg_cm_contact.id  IN( $this->includedIds )";
            $contactlistClass->orCondition($sWhere);
        }
        //set search fields
        $sWhere = (count($this->searchableColumns) > 0) ? $this->setSearchFieldsForContactPortrait() : $this->setSearchFields();
        $contactlistClass->addCondition($sWhere);
        //set special filter condition(contact table/portrait)

        if ($this->specialFilter != '') {
            $filterValue = $this->specialFilter;
            $contactTableFilterObj = new FgContactFrontTableFilters($this->container, $filterValue); 
            $contactFilterString = $contactTableFilterObj->generateFilter();
            if ($contactFilterString != '') {
                $sWhere[] = ' (' . $contactFilterString . ')';
            }
            $whereCondition = (count($sWhere) > 0) ? implode(' AND ', $sWhere) : '';
            $contactlistClass->addCondition($whereCondition);
        }

        //pagination handling area
        // Checking whether it is called for next and previous functionality
        if ($nextPreFlag != 1) {
            //call query for collect the data
            //$totallistquery = $contactlistClass->getResult();
            // $totalcontactlistDatas = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($totallistquery);

            if ($this->startValue != '') {
                $contactlistClass->setLimit($this->startValue);
                $contactlistClass->setOffset($this->displayLength);
            }
        } elseif ($nextPreFlag == 1) {
            if (isset($currentOffset)) {
                $limitStart = ($currentOffset - 5 > 0) ? ($currentOffset - 5) : '0';
                $contactlistClass->setLimit($limitStart);
                $contactlistClass->setOffset($this->displayLength);
            }
        }
        //SORTING CONDITION HANDLE AREA
        if ($this->sortColumnValue != '' && $this->dataTableColumnData[$this->sortColumnValue[0]['column']]['data'] != 'edit') {
            $mDataProp = $this->dataTableColumnData[$this->sortColumnValue[0]['column']]['data'];
            $sSortDirVal = ($nextPreFlag == 1) ? $this->sSortDir : $this->sortColumnValue[0]['dir'];
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
            $contactlistClass->addOrderBy($sortColumnValue);
            $this->session->set('sort-order' . $this->contactId . $this->clubId, $sortColumnValue);
        }
        //SETTING  ORDER BY CONDITION AREA
        //
        if ($this->separateList && $contactlistClass->separateListGroupBy != '') {
            $this->groupByColumn = $contactlistClass->separateListGroupBy;
        }
        if ($this->separateList && $contactlistClass->separateListWhere != '') {
            $where = $contactlistClass->getCondition();
            $contactlistClass->where = $contactlistClass->separateListWhere . " AND " . $where;
        }


        if ($this->groupByColumn != '') {
            $contactlistClass->setGroupBy($this->groupByColumn);
        }

        if ($this->sortColumnDetails != '') {
            $contactlistClass->addOrderBy($this->sortColumnDetails);
        }
        //COLUMN SETTING AREA
        $contactlistClass->setColumns($this->aoColumns);
        if ($contactlistClass->selectionFields != '*') {
            $countColumn = 'SQL_CALC_FOUND_ROWS fg_cm_contact.id,';
            $contactlistClass->selectionFields = $countColumn . $contactlistClass->selectionFields;
        }
        //call query for collect the data
        $listquery = $contactlistClass->getResult();

        file_put_contents('query.txt', $listquery);
        // Checking whether return query
        if ($isQuery) {
            return $listquery;
        }
        $results = array('totalcount' => 0, 'data' => '');
        $results['data'] = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($listquery);
        $totalcontactlistDatas = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList('SELECT FOUND_ROWS() as count');
        if (is_array($totalcontactlistDatas) && $totalcontactlistDatas[0]['count'] >= 0) {
            $results['totalcount'] = $totalcontactlistDatas[0]['count'];
        }
        return $results;
    }

    /**
     * To create where condition according to the filter settings.
     *
     * @param object $contactlistClass contactlist class object
     *
     * @return string
     */
    public function setFilterConditions($contactlistClass)
    {
        $sWhere = array();
        $whereCondition = '';
        if ($this->filterValue != 'contact' && $this->filterValue != 'sponsor' && $this->filterValue != '') {
            $filterValue = $this->filterValue;
            $filterData = array_shift($filterValue);
            //for create filter condition from filter array
            if ($this->contactType == 'sponsor' || $this->contactType == 'archivedsponsor') {
                $filterObj = new Sponsorfilter($this->container, $filterData);
            } else {
                $filterObj = new Contactfilter($this->container, $contactlistClass, $filterData);
            }
            $filterString = $filterObj->generateFilter();
            if ($filterString != '') {
                $sWhere[] = ' (' . $filterString . ')';
            }
        }

        $whereCondition = (count($sWhere) > 0) ? implode(' AND ', $sWhere) : '';

        return $whereCondition;
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
        if ($this->functionTypeValue != 'none') {
            list($roleId, $categoryId, $fnType) = explode('#', $this->functionTypeValue);
            array_push($this->tabledata, array('id' => $categoryId, 'type' => 'Fn', 'sub_ids' => $roleId, 'club_id' => 1, 'name' => 'Function', 'fnType' => $fnType));
        } elseif ($this->functionTypeValue == 'none' && $this->contactType != 'servicelist') {
            //DUMMY FUNCTION DATA SETTING
            array_push($this->tabledata, array('id' => 1, 'type' => 'Fn', 'sub_ids' => 1, 'club_id' => 1, 'name' => 'Function', 'fnType' => 'TEAM'));
        }
        if ($this->tableSetting == 'Clubadmin') {
            //HANDLE EXPORT AREA TABLE SETTING
            if ($this->exportFlag) {
                $table = new Tablesettings($this->container, $this->searchTableData, $this->club);
                $aColumns = $table->getColumns();
            } elseif (is_array($this->tabledata) && count($this->tabledata) > 0) {
                $table = new Tablesettings($this->container, $this->tabledata, $this->club);
                $aColumns = $table->getColumns();
            }
        } else {
            //webisite table setting - contact table setting element
            //HANDLE EXPORT AREA TABLE SETTING
            if ($this->exportFlag) {
                $table = new FgTablesettings($this->container, $this->searchTableData, $this->club);
                $table->dependColumns = $this->dependColumns;
                $table->separateListing = $this->separateList;
                $table->separateListColumn = (count($this->separateListingDetails) > 0) ? $this->separateListingDetails['separateListingColumn'] : '';
                $table->separateListFun = (count($this->separateListingDetails) > 0) ? $this->separateListingDetails['separateListingFunc'] : '';
                $aColumns = $table->getColumns();
            } elseif (is_array($this->tabledata) && count($this->tabledata) > 0) {
                $table = new FgTablesettings($this->container, $this->tabledata, $this->club);
                $table->dependColumns = $this->dependColumns;
                $table->separateListing = $this->separateList;
                $table->separateListColumn = (count($this->separateListingDetails) > 0) ? $this->separateListingDetails['separateListingColumn'] : '';
                $table->separateListFun = (count($this->separateListingDetails) > 0) ? $this->separateListingDetails['separateListingFunc'] : '';
                $aColumns = $table->getColumns();
                $this->searchableColumns = $table->getSearchableColumns();
            }
        }
        if ($this->contactType == 'contact' && $this->isExtraColumn === true) {
            array_push($aColumns, 'contactid', 'contactnamewithcomma', 'isMember', 'gender', 'isCompany', 'contactclubid', 'fedmembershipType', 'createdclubid', 'clubmembershipType', 'compDefContact', 'hasMainContact', 'sameInvoiceAddress', 'fedMembershipId', 'clubMembershipId', 'fedmembershipApprove', 'fedicon', 'isSponsor');
        } elseif ($this->contactType == 'servicelist') {
            array_push($aColumns, 'contactid', 'contactname', 'isMember', 'gender', 'isCompany', 'fedmembershipType', 'clubmembershipType', 'isSponsor');
        } elseif ($this->contactType == 'formerfederationmember' && $this->isExtraColumn === true) {
            array_push($aColumns, 'contactid', 'resigned_on', 'contactnamewithcomma', 'isMember', 'gender', 'isCompany', 'contactclubid', 'createdclubid', 'fedmembershipType', 'clubmembershipType', 'isSponsor');
        } elseif ($this->isExtraColumn == false) {
            array_push($aColumns, 'contactid', 'contactnamewithcomma', 'createdclubid');
        } else {
            array_push($aColumns, 'contactid', 'archived_on', 'contactnamewithcomma', 'isMember', 'gender', 'isCompany', 'contactclubid', 'createdclubid', 'fedmembershipType', 'clubmembershipType', 'compDefContact', 'hasMainContact', 'sameInvoiceAddress', 'fedMembershipId', 'clubMembershipId', 'fedmembershipApprove', 'fedicon', 'isSponsor');
        }


        return $aColumns;
    }

    /**
     * To initialize the contact class.
     *
     * @return \Clubadmin\Classes\contactlist
     */
    public function initializeContactList()
    {
        $contactlistClass = new Contactlist($this->container, $this->contactId, $this->club, $this->contactType);
        $contactlistClass->separateListing = ($this->separateList) ? true : false;
        $contactlistClass->separateListingDetails = $this->separateListingDetails;
        $contactlistClass->setCount();
        $contactlistClass->setFrom();
        $contactlistClass->setCondition($this->tableSetting);

        return $contactlistClass;
    }

    /**
     * To creating the serach field condition from the search value setting.
     *
     * @return string
     */
    public function setSearchFields()
    {
        $sWhere = '';
        if (is_array($this->searchval) && $this->searchval['value'] != '') {
            $searchcolumns = (count($this->exportSearchColumns) > 0) ? $this->exportSearchColumns : $this->aoColumns;
            foreach ($searchcolumns as $searchcolumn) {
                //for remove the alias name from the fields
                if (strrpos($searchcolumn, 'AS ') !== false) {
                    $columns[] = substr($searchcolumn, 0, strrpos($searchcolumn, 'AS '));
                }
            }
            //to set the fields for contact name
            $columns[] = '`' . $this->container->getParameter('system_field_firstname') . '`';
            $columns[] = '`' . $this->container->getParameter('system_field_lastname') . '`';
            $columns[] = '`' . $this->container->getParameter('system_field_companyname') . '`';

            $sWhere .= $this->getSearchCriteria($columns, $this->searchval['value']);
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
     * To setting the session values.
     *
     * @param type $contactlistDatas contact list data
     */
    public function setSessionValues($contactlistDatas)
    {
        $flag = $this->session->get('flag');
        if (isset($flag)) {
            $sessionContactlistDatas = $this->session->get('contactlistDatas');
            //For check  seesion array and current getting array is same
            if ($contactlistDatas === $sessionContactlistDatas) {
                $this->session->set('flag', 0);
            } else {
                $this->session->set('flag', 1);
                $this->removeNextPrevSession('columnsArray', 'filteredContactDetailsiSortCol_0', 'filteredContactDetailsmDataProp', 'filteredContactDetailsSortDir_0', 'filteredContactDetailsSearch', 'filteredContactDetailsFilterdata', 'nextPreviousContactListData', 'tableField');
                $this->session->set('contactlistDatas', $contactlistDatas);
            }
        } else {
            $this->session->set('flag', 1);
            $this->session->set('contactlistDatas', $contactlistDatas);
        }
        //For setting the all session related to the datatable value
        $this->setSessionForNextPre('columnsArray', 'filteredContactDetailsiSortCol_0', 'filteredContactDetailsmDataProp', 'filteredContactDetailsSortDir_0', 'filteredContactDetailsSearch', 'filteredContactDetailsFilterdata', 'tableField', 'filteredContactDetailsDisplayLength');
    }

    /**
     * Function to set session related to Next and previous links.
     *
     * @param type $columnsArray                 Displayed columns in the listing
     * @param type $filteredDetailsiSortCol      Sort columns
     * @param type $filteredDetailsmDataProp     Sorting data
     * @param type $filteredDetailsSortDir       Sorting order
     * @param type $filteredDetailsSearch        Search value
     * @param type $filteredDetailsFilterdata    Filter value
     * @param type $tableField                   Selected table fields
     * @param type $filteredDetailsDisplayLength List length
     */
    public function setSessionForNextPre($columnsArray, $filteredDetailsiSortCol, $filteredDetailsmDataProp, $filteredDetailsSortDir, $filteredDetailsSearch, $filteredDetailsFilterdata, $tableField, $filteredDetailsDisplayLength)
    {
        if (isset($this->tableFieldValues) && $this->tableFieldValues != '') {
            $this->session->set($tableField, $this->tableFieldValues);
        }
        $this->session->set($columnsArray, $this->aoColumns);
        if (isset($this->searchval) && $this->searchval['value'] != '') {
            $this->session->set($filteredDetailsSearch, $this->searchval);
        }
        if (isset($this->filterValue) && $this->filterValue != 'contact' && $this->filterValue != '') {
            $this->session->set($filteredDetailsFilterdata, $this->filterValue);
        }
        if (isset($this->displayLength) && $this->displayLength != '') {
            $this->session->set($filteredDetailsDisplayLength, $this->displayLength);
        }
        if (isset($this->sortColumnValue[0]['column']) && $this->sortColumnValue[0]['column'] != '' && $this->dataTableColumnData[$this->sortColumnValue[0]['column']]['data'] != 'edit') {
            $this->session->set($filteredDetailsiSortCol, $this->sortColumnValue);
            $this->session->set($filteredDetailsmDataProp, $this->dataTableColumnData[$this->sortColumnValue[0]['column']]['data']);
            $this->session->set($filteredDetailsSortDir, $this->sortColumnValue[0]['dir']);
        }
    }

    /**
     * Function to remove session related to Next and previous links.
     *
     * @param type $columnsArray              Displayed columns in the listing
     * @param type $filteredDetailsiSortCol   Sort columns
     * @param type $filteredDetailsmDataProp  Sorting data
     * @param type $filteredDetailsSortDir    Sorting order
     * @param type $filteredDetailsSearch     Search value
     * @param type $filteredDetailsFilterdata Filter value
     * @param type $nextPreviousListData      Listing data
     * @param type $tableField                Selected table fields
     */
    private function removeNextPrevSession($columnsArray, $filteredDetailsiSortCol, $filteredDetailsmDataProp, $filteredDetailsSortDir, $filteredDetailsSearch, $filteredDetailsFilterdata, $nextPreviousListData, $tableField)
    {
        $this->session->remove($columnsArray);
        $this->session->remove($filteredDetailsiSortCol);
        $this->session->remove($filteredDetailsmDataProp);
        $this->session->remove($filteredDetailsSortDir);
        $this->session->remove($filteredDetailsSearch);
        $this->session->remove($filteredDetailsFilterdata);
        $this->session->remove($nextPreviousListData);
        $this->session->remove($tableField);
    }

    /**
     * To setting the session values.
     *
     * @param type $sponsorlistDatas contact list data
     */
    public function setSponsorSessionValues($sponsorlistDatas)
    {
        $flag = $this->session->get('sponsorflag');
        if (isset($flag)) {
            $sessionSponsorlistDatas = $this->session->get('contactlistDatas');
            //For check  seesion array and current getting array is same
            if ($sponsorlistDatas === $sessionContactlistDatas) {
                $this->session->set('sponsorflag', 0);
            } else {
                $this->session->set('sponsorflag', 1);
                $this->removeNextPrevSession('sponsorcolumnsArray', 'filteredSponsorDetailsiSortCol_0', 'filteredSponsorDetailsmDataProp', 'filteredSponsorDetailsSortDir_0', 'filteredSponsorDetailsSearch', 'filteredSponsorDetailsFilterdata', 'nextPreviousSponsorListData', 'sponsortableField');
                $this->session->set('sponsorlistDatas', $sponsorlistDatas);
            }
        } else {
            $this->session->set('sponsorflag', 1);
            $this->session->set('sponsorlistDatas', $sponsorlistDatas);
        }
        //For setting the all session related to the datatable value
        $this->setSessionForNextPre('sponsorcolumnsArray', 'filteredSponsorDetailsiSortCol_0', 'filteredSponsorDetailsmDataProp', 'filteredSponsorDetailsSortDir_0', 'filteredSponsorDetailsSearch', 'filteredSponsorDetailsFilterdata', 'sponsortableField', 'filteredSponsorDetailsDisplayLength');
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

        if ($this->contactType == 'archivedsponsor' || $this->contactType == 'archive') {
            array_push($tabledata, array('id' => 'archived_on'));
        } elseif ($this->contactType == 'formerfederationmember') {
            array_push($tabledata, array('id' => 'resigned_on'));
        }

        foreach ($tabledata as $datafields) {
            if (in_array($sortColumn, $datafields)) {
                $flag = 1;
            } elseif (($sortColumn == 'joining_date' || $sortColumn == 'leaving_date') && in_array('join_leave_dates', $datafields)) {
                $flag = 1;
            }
        }

        return ($flag == 1) ? $sortColumn : 'contactname';
    }

    /**
     * This function is used to get the criteria for searching contacts
     * 
     * @param array  $columns     The columns to be searched
     * @param string $searchValue The search string
     * 
     * @return string $sWhere The search criteria
     */
    private function getSearchCriteria($columns, $searchValue)
    {
        $sWhere = '(';
        $country_fields = $this->container->getParameter('country_fields');
        $corress_lang = $this->container->getParameter('system_field_corress_lang');
        $gender = $this->container->getParameter('system_field_gender');
        $salutaion = $this->container->getParameter('system_field_salutaion');
        $searchVal = FgUtility::getSecuredDataString($searchValue, $this->conn);
        foreach ($columns as $column) {
            if (in_array(preg_replace('/[^0-9]/', '', $column), $country_fields)) { // Search from fg_search_countries table
                $sWhere .= "(" . $column . " IN (SELECT short_name FROM fg_search_countries WHERE title LIKE '%" . $searchVal . "%' AND fg_search_countries.lang='$this->defaultSystemLang' )) OR ";
            } elseif (preg_replace('/[^0-9]/', '', $column) == $corress_lang) {    // Search from fg_search_lang table
                $sWhere .= "(" . $column . " IN (SELECT short_name FROM fg_search_lang WHERE title LIKE '%" . $searchVal . "%' AND fg_search_lang.lang='$this->defaultSystemLang' )) OR ";
            } elseif (preg_replace('/[^0-9]/', '', $column) == $gender) { // search from fg_search_misc for type genger
                $sWhere .= "(" . $column . " IN (SELECT title FROM fg_search_misc WHERE translation LIKE '%" . $searchVal . "%' and fg_search_misc.type='gender' AND fg_search_misc.lang='$this->defaultSystemLang' )) OR ";
            } elseif (preg_replace('/[^0-9]/', '', $column) == $salutaion) { // search from fg_search_misc for type satutation
                $sWhere .= "(" . $column . " IN (SELECT title FROM fg_search_misc WHERE translation LIKE '%" . $searchVal . "%' and fg_search_misc.type='salutation' AND fg_search_misc.lang='$this->defaultSystemLang' )) OR ";
            } else {
                $sWhere .= "(" . $column . " LIKE '%" . $searchVal . "%') OR ";
            }
        }
        $sWhere = substr_replace($sWhere, '', -3);
        $sWhere .= ')';

        return $sWhere;
    }

    /**
     * This function is used to build the search criteria for portrait element
     * 
     * @return string $sWhere The search string
     */
    private function setSearchFieldsForContactPortrait()
    {
        $sWhere = '';
        if (is_array($this->searchPortraitValue) && $this->searchPortraitValue['value'] !== '') {
            if (count($this->searchableColumns) > 0) {
                $sWhere .= $this->getSearchCriteria($this->searchableColumns, $this->searchPortraitValue['value']);
            }
        }

        return $sWhere;
    }
}
