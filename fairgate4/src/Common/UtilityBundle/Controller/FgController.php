<?php
/**
 * FgController
 *
 * This controller is used to handle common variables and functions
 *
 * @package    CommonUtilityBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
namespace Common\UtilityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Clubadmin\Classes\Contactfilter;
use Clubadmin\Util\Contactlist;
use Common\UtilityBundle\Util\FgGenericQueryhandler;
use Clubadmin\DocumentsBundle\Util\Documentlist;
use Clubadmin\ContactBundle\Util\ContactlistData;
use Common\UtilityBundle\Util\FgPermissions;
use Common\UtilityBundle\Util\FgUtility;

/**
 * This controller is used to handle common variables and functions
 *
 * @author PIT Solutions <pit@solutions.com>
 */
class FgController extends Controller
{

    /**
     * doctrine entity manager
     * @var Obeject
     */
    public $em;

    /**
     * Request
     * @var Obeject
     */
    public $request;

    /**
     * Session
     * @var Obeject
     */
    public $session;
    /*
     * Connection
     * @var Obeject
     */
    public $conn;

    /**
     * Club Id
     * @var int
     */
    public $clubId;

    /**
     * Club Url Identifier
     * @var string
     */
    public $clubUrlIdentifier;

    /**
     * Federation Id
     * @var int
     */
    public $federationId;

    /**
     * Sub Federation Id
     * @var int
     */
    public $subFederationId;

    /**
     * Club Type (standard_club/federation_club/sub_federation_club/federation/sub_federation)
     * @var string {standard_club/federation_club/sub_federation_club/federation/sub_federation}
     */
    public $clubType;

    /**
     * Club Default Language
     * @var string
     */
    public $clubDefaultLang;

    /**
     * Club Default System Language
     * @var string
     */
    public $clubDefaultSystemLang;

    /**
     * Club Title
     * @var string
     */
    public $clubTitle;

    /**
     * Club Languages
     * @var array
     */
    public $clubLanguages;

    /**
     * Logged in contact
     * @var int
     */
    public $contactId;

    /**
     * Logged user roles
     * @var array
     */
    public $loggedUserRoles;
    public $fgpermission;

    /**
     * Club admin manage entity manager
     * @var type 
     */
    public $adminEntityManager;

    /**
     * This function is used to preexecute commonly used services, to reduce the number of requests
     *
     */
    public function preExecute()
    {
        $club = $this->get('club');
        $clubId = $club->get('id');
        if ($clubId == 0) {
            throw $this->createNotFoundException('Not a club!');
        } else {
            $this->clubId = $clubId;
            $this->clubUrlIdentifier = $club->get('url_identifier');
            $this->federationId = $club->get('federation_id');
            $this->subFederationId = $club->get('sub_federation_id');
            $this->clubType = $club->get('type');
            $this->clubDefaultLang = $club->get('default_lang');
            $this->clubDefaultSystemLang = $club->get('default_system_lang');
            $this->clubTitle = $club->get('title');
            $this->clubLanguages = $club->get('club_languages');
            $this->federationName = $club->get('federation_name');
            $this->subFederationName = $club->get('sub_federation_name');
            $this->clubExecutiveBoardId = $club->get('club_executiveboard_id');
            $this->clubWorkgroupId = $club->get('club_workgroup_id');
            $this->clubTeamId = $club->get('club_team_id');
            $this->bookedModulesDet = $club->get('bookedModulesDet');
        }
        $this->request = $this->container->get('request_stack')->getCurrentRequest();
        $this->session = $this->request->getSession();
        $this->em = $this->container->get('doctrine')->getManager();
        $this->conn = $this->container->get('database_connection');
        $this->fgpermission = new FgPermissions($this->container);


        // Handled for login related functionalities
        if ($this->container->get('security.token_storage')->getToken()->getUser()) {
            $user = $this->container->get('security.token_storage')->getToken()->getUser();
            $contact = $this->get('contact');
            $this->session->set('loggedClubUserId', $contact->get("id"));
            $this->session->set('loggedContactName', $contact->get("name"));
            $newUserArray = $contact->get("allowedModules");
            $this->session->set('allowedUserRights', $newUserArray);
            $this->loggedUserRoles = $newUserArray;
            $club->setAllowedRights($newUserArray);
            $club->setContactId($contact->get("id"));
            $this->contactId = $contact->get("id");
            $this->contactName = $contact->get("name");
            $this->contactNameNoSort = $contact->get("nameNoSort");
            if ($this->container->get('security.authorization_checker')->isGranted('ROLE_USER')) { // in public pages user is 'ROLE_USER' is not granted. There custom logout trigget is not required
                $this->em->getRepository('CommonUtilityBundle:SfGuardUser')->customLogoutTrigger($this->container, $this->session, $this->generateUrl('fairgate_user_security_logout'), $this->request, $user);
            }
        }
        //Club admin entity manger
        $this->adminEntityManager = $this->container->get("fg.admin.connection")->getAdminEntityManager();
    }

    /**
     * Executes generatequery action
     *
     * Function used to generate native query from json data
     * and execute the query
     * @param String $tablename Table name
     * @param Array  $catArr    category array
     * @param Int    $clubId    Club id
     */
    public function generatequeryAction($tablename, $catArr, $clubId = '', $contactType = 'contact')
    {
        $genericQueryhandler = new FgGenericQueryhandler($this->container);
        $genericQueryhandler->generatequeryAction($tablename, $catArr, $contactType);

        return array('deleledIds' => $genericQueryhandler->deletedIdArray);
    }

    /**
     * Function for executing query string through transaction
     *
     * Function used to execute native query using transaction
     * @param String $updateFullQry query for updation
     * @param String $delQryStr     query for deleting data
     * @param String $unsetFedCat   delete bookmark
     * @param String $tablename     Table name
     * @param Array  $bookmarkArr   bookmark
     * @param int    $clubId        ClubId
     * @param int    $updateLog     updateLog
     */
    public function addtotransaction($updateFullQry, $delQryStr, $unsetFedCat, $tablename, $bookmarkArr, $clubId, $updateLog = '')
    {
        /*         * ******** BEGIN TRANSACTION ******** */
        if ($updateFullQry !== '' || $delQryStr != '' || count($unsetFedCat) > 0) {
            $conn = $this->container->get("database_connection");
            try {
                $conn->beginTransaction();
                if ($updateLog != '') {
                    $conn->executeQuery($updateLog);
                }
                if ($updateFullQry !== '') {
                    $conn->executeUpdate($updateFullQry);
                }
                if ($delQryStr != '') {
                    $clubIdField = ($tablename == 'fg_club_classification') ? 'federation_id' : 'club_id';
                    $delArr = explode(',', rtrim($delQryStr, ','));
                    $stmt = $conn->executeQuery("DELETE FROM $tablename WHERE id IN (?) AND $clubIdField = $clubId", array($delArr), array(\Doctrine\DBAL\Connection::PARAM_INT_ARRAY));
                }
                if (count($unsetFedCat) > 0) {
                    $stmt = $conn->executeQuery("DELETE FROM fg_cm_bookmarks WHERE membership_id IN (?) AND type = 'membership' AND club_id = $clubId", array($unsetFedCat), array(\Doctrine\DBAL\Connection::PARAM_INT_ARRAY));
                }
                $conn->commit();
            } catch (Exception $ex) {
                $conn->rollback();
                throw $ex;
            }
            $conn->close();
        }
        if (count($bookmarkArr) > 0) {
            $this->em->getRepository('CommonUtilityBundle:FgCmBookmarks')->createDeletebookmark('membership', $bookmarkArr, $this->clubId, $this->contactId);
        }
        /*         * ******** END TRANSACTION ******** */
    }

    /**
     * Executes getMaxSortOrderTable
     *
     * Function used to get the next sort order value of the table
     *
     * @param String $tablename       name of the table of which the sort order is needed
     * @param String $type            Type
     * @param Int    $clubId          ClubId
     * @param String $whereExtraParam Where condition
     * @param Object $conn            The connection to be used
     *
     * @return int
     */
    public function getMaxSortOrderTable($tablename, $type = '', $clubId = '', $whereExtraParam = '', $conn = null)
    {
        if ($clubId) {
            if (null === $conn) {
                $conn = $this->container->get("database_connection");
            }

            $where = $whereExtraParam;
            if (($type == 'executiveFunction' || $tablename == 'fg_rm_function')) {
                $where = ' category_id=' . $clubId;
            } else if ($type != '') {
                $where = " club_id=$clubId $whereExtraParam";
            }
            //echo "SELECT MAX(sort_order) FROM $tablename $where";
            $sortOrder = $conn->fetchColumn("SELECT MAX(sort_order) FROM $tablename WHERE 1 AND $where");
            $conn->close();

            return $sortOrder + 1;
        }
    }

    /**
     * Function to insert tables from sidebar
     * 
     * @param Array $tableValues Table values
     * @param type $connection   database connection
     */
    public function insertIntoTableSidebar($tableValues, $connection = null)
    {

        if (null === $connection) {
            $connection = $this->container->get("database_connection");
        }

        //$connection = ($connectionType=='clubadmin') ? $this->container->get("fg.admin.connection")->getAdminConnection() :  $this->conn;

        $query = array();
        $clubDefaultLang = $this->get('club')->get('club_default_lang');
        foreach ($tableValues as $table => $values) {
            if (count($values) > 0) {
                if (!empty($values['languages'])) {
                    array_unshift($values['values'], "'$clubDefaultLang'");
                    $query[] = "INSERT INTO $table (" . implode(',', $values['fields']) . ") VALUES (" . implode(',', $values['values']) . ")";
                    array_shift($values['values']);
                } else {
                    $query[] = "INSERT INTO $table (" . implode(',', $values['fields']) . ") VALUES (" . implode(',', $values['values']) . ")";
                }
            }
        }
        if (count($query) > 0) {
            $connection->executeQuery(implode(';', $query));
        }
    }

    /**
     * Executes getLastInsertedId
     *
     * Function used to get the last inserted id
     *
     * @param String $tablename name of the table of which the sort order is needed
     * @param Int    $type      Type
     * @param String $where     Where condition
     * @param Object $conn      The connection to be used
     * 
     * @return int
     */
    public function getLastInsertedId($tablename, $type = '', $where = '', $conn = null)
    {
        if (null === $conn) {
            $conn = $this->container->get("database_connection");
        }
        $whereCond = "WHERE $where";
        if (($type == 'membership' || $type == 'rolecategory' || $type == 'teamcategory') && $arg != '') {
            $where = "WHERE $where";
        } else if ($type == 'role' && $arg != '') {
            $where = "WHERE $where";
        } else if ($type == 'executiveFunction' && $arg != '') {
            $where = 'WHERE category_id=' . $arg;
        }
        $lastInsertedId = $conn->fetchColumn("SELECT MAX(id) FROM $tablename $whereCond");
        $conn->close();

        return $lastInsertedId;
    }

    /**
     * function to check whether a club has booked frontend 1 module
     * @param int $clubId the club id
     *
     * @return int
     */
    public function isFrontend1Booked($clubId = "")
    {
        return 1;
    }

    /**
     * Function of next and previous buttons in the header
     *
     * @param int $documentId Document Id
     * @param int $offset     Offset value
     * @param int $url        Current url
     * @param int $param1     Url param
     * @param int $param2     Url param
     * @param int $flag       Flag
     *
     * @return array
     */
    public function nextPreviousBtnDocumentAction($documentId, $offset, $url, $param1, $param2, $flag = 0)
    {
        // Session values in the contact listing page
        $displayLength = $this->session->get('documentsFilteredContactDetailsDisplayLength');
        $displayLimit = $this->session->get('documentsLimitVal');
        $iSortCol = $this->session->get('documentsFilteredContactDetailsiSortCol_0');
        $mDataProp = $this->session->get('documentsFilteredContactDetailsmDataProp');
        $sSortDir = $this->session->get('documentsFilteredContactDetailsSortDir_0');
        $filteredContactDetailsSearch = $this->session->get('documentsFilteredContactDetailsSearch');
        $filteredContactDetailsFilterdata = $this->session->get('documentsFilteredContactDetailsFilterdata');
        $aColumns = $this->session->get('documentsColumnsArray');
        $tableField = $this->session->get('documentsTableField');
        $contactType = $this->session->get('contactType');
        //Session to check for new query call
        $sessionFlag = $this->session->get('documentFlag');
        $nextPreviousDocumentListData = $this->session->get('nextPreviousDocumentListData');
        $documentsDataTableColumnData = $this->session->get('documentsDataTableColumnData');

        $currentIndexValue = $offset;
        $club = $this->get('club');
        $doctype = $this->session->get('documentType');




        if ($doctype == 'CLUB') {
            $fieldArray = array("author" => "author", "description" => "description", "docuploaded" => "fg_dm_version.created_at", "last_updated" => "fg_dm_version.updated_at", "docsize" => "fg_dm_version.size");
        } else if ($doctype == 'WORKGROUP') {
            $fieldArray = array("author" => "author", "docuploaded" => "fg_dm_version.created_at", "docsize" => "fg_dm_version.size", "depositedwith" => "fdd.deposited_with");
        } else if ($doctype == 'CONTACT') {
            $fieldArray = array("author" => "author", "description" => "description", "docuploaded" => "fg_dm_version.created_at", "last_updated" => "fg_dm_version.updated_at", "docsize" => "fg_dm_version.size");
        } else if ($doctype == 'TEAM') {
            $fieldArray = array("author" => "author", "docuploaded" => "fg_dm_version.created_at", "docsize" => "fg_dm_version.size", "depositedwith" => "fdd.deposited_with");
        }

        //echo "<pre>";print_r($aColumns);exit;
        $tablecolumns = $aColumns;
        $columnsArray = $aColumns;
        $documentlistClass = new Documentlist($this->container, $doctype);
        $documentlistClass->setColumns($aColumns);
        $documentlistClass->setFrom();
        $documentlistClass->setCondition();

        if ($filteredContactDetailsSearch != "") {
            $sWhere = $this->getAddCondition($filteredContactDetailsSearch, $fieldArray);
            $documentlistClass->addCondition($sWhere);
        }

        //pagination handling area
        $this->setDocOrderLimit($displayLimit, $displayLength, $documentlistClass, $documentsDataTableColumnData, $fieldArray, $iSortCol, $mDataProp, $sSortDir);

        //call query for collect the data
        $listquery = '';
        $listquery = $documentlistClass->getResult();
        file_put_contents("queryFg.txt", $listquery . "\n");

        $callQueryFlag = 1;
        if ($flag == 0) {
            if (isset($nextPreviousDocumentListData) && !empty($nextPreviousDocumentListData)) {
                foreach ($nextPreviousDocumentListData as $val) {
                    if ($val['documentId'] == $documentId) {
                        $callQueryFlag = 0;
                        $documentlistDatas = $nextPreviousDocumentListData;
                    }
                }
            }
        }
        if (isset($sessionFlag)) {
            if ($callQueryFlag == 1 || $sessionFlag == 1) {
                $documentlistDatas = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($listquery);
                $this->session->set('nextPreviousDocumentListData', $documentlistDatas);
            }
        }

        // Section for calculating next and previous five results and changing the links according to that
        $existFlag = 0;
        $pre = '';
        $next = '';
        $arrayCount = 0;
        foreach ($documentlistDatas as $key => $value) {
            $arrayCount++;
            if ($value['documentId'] == $documentId) {
                $existFlag = 1;
                if (array_key_exists($key - 1, $documentlistDatas)) {
                    if ($offset != 0) {
                        $pre = $documentlistDatas[$key - 1]['documentId'];
                    }
                } else if ($currentIndexValue == 0) {
                    $pre = '';
                } else {
                    $result = $this->nextPreviousBtnDocumentAction($documentId, $offset, $url, $param1, $param2, $newIterationFlag = 1);

                    return $result;
                }
                if (array_key_exists($key + 1, $documentlistDatas)) {
                    $next = $documentlistDatas[$key + 1]['documentId'];
                } else if (count($documentlistDatas) < 10) {
                    $next = '';
                } else if (count($documentlistDatas) == $arrayCount) {
                    $next = '';
                } else {
                    $result = $this->nextPreviousBtnDocumentAction($documentId, $offset, $url, $param1, $param2, $newIterationFlag = 1);

                    return $result;
                }
            }
        }
        $paginationValue['previous'] = $pre;
        $paginationValue['next'] = $next;
        $paginationValue['url'] = $url;
        $paginationValue['param1'] = $param1;
        $paginationValue['param2'] = $param2;

        return $paginationValue;
    }

    /**
     * For set the limit and order in a query
     * @param object $displayLimit        Limit of elements
     * @param object $displayLength       Length
     * @param array  $documentlistClass   Object
     * @param array  $dataTableColumnData Table column data
     * @param array  $fieldArray          Field array
     * @param array  $iSortCol            Sort value
     * @param array  $mDataProp           Data session value
     * @param array  $sSortDir            Sort column
     */
    private function setDocOrderLimit($displayLimit, $displayLength, $documentlistClass, $dataTableColumnData, $fieldArray, $iSortCol, $mDataProp, $sSortDir)
    {
        //echo $displayLimit."----".$displayLength;exit;
        if ($displayLimit != '') {
            //echo $displayLimit."----".$displayLength;
            $documentlistClass->setLimit($displayLimit);
            $documentlistClass->setOffset($displayLength);
        }

        if ($iSortCol != "" && $sSortDir != "" && $mDataProp != 'edit') {
            $sortColumnValue = $this->getSortValue($mDataProp, $sSortDir, $fieldArray);
            $documentlistClass->addOrderBy($sortColumnValue);
            //$this->session->set("sort-order" . $this->contactId . $this->clubId, $sortColumnValue);
        }
    }

    /**
     * For create the sort column
     * @param array $mDataProp   Data from session
     * @param array $sSortDirVal Sort field
     * @param array $fieldArray  Field array
     *
     * @return string
     */
    private function getSortValue($mDataProp, $sSortDirVal, $fieldArray)
    {
        $club = $this->get('club');
        $sortColumn = $mDataProp;
        $splitColumn = explode("_", $sortColumn);
        $sortColumnValue = '';
        if (array_key_exists($mDataProp, $fieldArray)) {
            $sortColumn = $fieldArray[$mDataProp];
        }
        $sortColumnValue = " (CASE WHEN " . $sortColumn . " IS NULL then 3 WHEN " . $sortColumn . "='' then 2 WHEN " . $sortColumn . "='0000-00-00 00:00:00' then 1 ELSE 0 END)," . $sortColumn . " " . $sSortDirVal;


        return $sortColumnValue;
    }

    /**
     * For get the additional where condition
     * @param array $searchPostValue Search value
     * @param array $fieldArray      All field values for search
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
            $key = array_search('docname', $columns); //
            unset($columns[$key]);
            $columns[] = "IF(fdi18.name_lang ='', fdd.name, fdi18.name_lang)";
        }

        $sWhere = "  (";
        foreach ($columns as $column) {
            $column = is_numeric($column) ? "`" . $column . "`" : $column;
            $searchPostValue = FgUtility::getSecuredDataString($searchPostValue, $this->conn);
            $sWhere .= $column . " LIKE '%" . $searchPostValue . "%' OR ";
        }
        $sWhere = substr_replace($sWhere, "", -3);
        $sWhere .= ')';

        return $sWhere;
    }

    /**
     * Function of next and previous buttons in the header
     * @param int $contact Contact Id
     *
     * @return HTML
     */
    public function getTabIconInfoAction(Request $request, $contact)
    {
        $club = $this->container->get('club');
        $bookedModulesDet = array();
        $bookedModulesDet = $club->get('bookedModulesDet');
        $clubId = $club->get('id');
        $clubType = $club->get('type');
        $federationId = ($clubType == 'federation') ? $clubId : $club->get('federation_id');
        ;
        $fedIconSrc = FgUtility::getClubLogo($federationId);
        $contactType = $request->getSession()->get('contactType');
        $contactType = ($contactType == '') ? 'contact' : $contactType;
        $tabiconDetails = $this->contactName($contact, $contactType);
        $em = $this->getDoctrine()->getManager();
        $systemFieldTeamPicture = $this->container->getParameter('system_field_team_picture');
        $systemFieldCommunitypicture = $this->container->getParameter('system_field_communitypicture');
        $systemFieldCompanylogo = $this->container->getParameter('system_field_companylogo');
        $companyProfileTeamImages = $em->getRepository('CommonUtilityBundle:FgCmContact')->getcompanyTeamProfileImages($contact, $systemFieldTeamPicture, $systemFieldCommunitypicture, $systemFieldCompanylogo);

        return $this->render('CommonUtilityBundle:partials:getTabIconInfo.html.twig', array('tabiconDetails' => $tabiconDetails, 'companyProfileTeamImages' => $companyProfileTeamImages, 'bookedModulesDet' => $bookedModulesDet, 'fedIconSrc' => $fedIconSrc));
    }

    /**
     * Function to get contact name
     * @param int $contactId   Contact Id
     * @param int $contactType Contact type
     *
     * @return array
     */
    protected function contactName($contactId, $contactType = 'contact')
    {
        $club = $this->get('club');
        $contactlistClass = new Contactlist($this->container, '', $club, 'noCondition');
        $contactlistClass->setColumns(array('contactName', 'isMember', 'gender', 'isCompany', 'fed_membership_cat_id', 'club_membership_cat_id', 'is_sponsor', 'is_subscriber', 'intranet_access', 'is_company', '`72`', 'fg_cm_contact.is_permanent_delete'));
        $contactlistClass->setFrom('*');
        $contactlistClass->setCondition();
        $sWhere = " fg_cm_contact.id = $contactId";
        $contactlistClass->addCondition($sWhere);
        $listquery = $contactlistClass->getResult();
        $fieldsArray = $this->container->get('database_connection')->fetchAll($listquery);

        return $fieldsArray[0];
    }

    /**
     * Function of next and previous buttons in the header
     *
     * @return array
     */
    public function getDatatableListdata()
    {
        // Session values in the contact listing page
        $displayLength = $this->session->get('filteredContactDetailsDisplayLength');
        $iSortCol = $this->session->get('filteredContactDetailsiSortCol_0');
        $mDataProp = $this->session->get('filteredContactDetailsmDataProp');
        $sSortDir = $this->session->get('filteredContactDetailsSortDir_0');
        $filteredContactDetailsSearch = $this->session->get('filteredContactDetailsSearch');
        $filteredContactDetailsFilterdata = $this->session->get('filteredContactDetailsFilterdata');
        $aColumns = $this->session->get('columnsArray');
        $tableField = $this->session->get('tableField');
        $contactType = $this->session->get('contactType');
        //Session to check for new query call
        $currentIndexValue = $offset;
        $club = $this->get('club');
        if (isset($tableField) && $tableField != "") {
            $tabledatas = json_decode($tableField, true);
        } else {
            $tabledatas = array();
        }
        $nonQuotedColumns = $tabledatas;
        // Setting query conditions
        $contactlistClass = new Contactlist($this->container, $this->contactId, $club, $contactType);
        $contactlistClass->setColumns($aColumns);
        $contactlistClass->setFrom();
        $contactlistClass->setCondition();
        // Condition to check whether any search is initiated and change the query according to that
        if (isset($filteredContactDetailsSearch) && $filteredContactDetailsSearch != "") {
            $columns[] = $this->container->getParameter('system_field_firstname');
            $columns[] = $this->container->getParameter('system_field_lastname');
            foreach ($nonQuotedColumns as $key => $nonQuotedColumn) {
                if ($nonQuotedColumn['type'] == 'CF') {
                    $columns[] = $nonQuotedColumn['id'];
                }
            }
            if (in_array('contactname', $columns)) {
                $key = array_search('contactname', $columns); //
                unset($columns[$key]);
            }
            if (in_array('contactid', $columns)) {
                $key = array_search('contactid', $columns); //
                unset($columns[$key]);
            }
            $sWhere = "(";
            foreach ($columns as $column) {
                $filteredContactDetailsVal = FgUtility::getSecuredDataString($filteredContactDetailsSearch, $this->conn);
                $sWhere .= "`" . $column . "`" . " LIKE '%" . $filteredContactDetailsVal . "%' OR ";
            }
            $sWhere = substr_replace($sWhere, "", -3);
            $sWhere .= ')';
            $contactlistClass->addCondition($sWhere);
        }
        // Condition to check whether any filter is initiated and change the query according to that
        if (isset($filteredContactDetailsFilterdata) && $filteredContactDetailsFilterdata != "contact") {
            $filterData = array_shift($filteredContactDetailsFilterdata);
            $filterObj = new Contactfilter($this->container, $contactlistClass, $filterData, $club);
            if (isset($filteredContactDetailsSearch) && $filteredContactDetailsSearch != "") {
                $sWhere .= " AND (" . $filterObj->generateFilter() . ")";
            } else {
                $sWhere .= " " . $filterObj->generateFilter();
            }
            $contactlistClass->addCondition($sWhere);
        }
        $sWhere = '';
        // This section is to set the starting index and length of the
        // result array corresponding to pagination values
        if (isset($currentIndexValue)) {
            $limitStart = '0';
            $contactlistClass->setLimit($limitStart);
            $contactlistClass->setOffset($displayLength);
        }
        if (isset($iSortCol) && $iSortCol != "" && $mDataProp != 'edit') {
            $sortColumn = $mDataProp;
            $sortColumnValue = "`" . $sortColumn . "` " . $sSortDir;
            $contactlistClass->addOrderBy($sortColumnValue);
        }
        // Setting up the final query
        $listquery = '';
        $listquery = $contactlistClass->getResult();
        $contactlistDatas = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($listquery);

        return $contactlistDatas;
    }

    /**
     * Function to check authenticated contact
     * @param int $contactId Contact Id
     *
     * @return array
     */
    private function CheckContactAuthentication($contactId)
    {
        $club = $this->get('club');
        $allowedContact = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getAllAuthenticatedContact($club, $contactId);

        return $allowedContact;
    }

    /**
     * Function to set user roles according to booked modules
     * @param int $userRoleArr Logged user role array
     *
     * @return array
     */
    private function setUserRoles($userRoleArr)
    {
        foreach ($userRoleArr as $key => $role) {
            if ($role == 'ROLE_COMMUNICATION') {
                $newUserArray['communication'] = 'communication';
            }
            if ($role == 'ROLE_DOCUMENT') {
                $newUserArray['document'] = 'document';
            }
            if ($role == 'ROLE_CONTACT') {
                $newUserArray['contact'] = 'contact';
            }
            if ($role == 'ROLE_READONLY_CONTACT') {
                $newUserArray['readonly_contact'] = 'readonly_contact';
            }
            if ($role == 'ROLE_SPONSOR') {
                $newUserArray['sponsor'] = 'sponsor';
            }
            if ($role == 'ROLE_READONLY_SPONSOR') {
                $newUserArray['readonly_sponsor'] = 'readonly_sponsor';
            }
            if ($role == 'ROLE_USERS') {
                $newUserArray['clubAdmin'] = 'clubAdmin';
                $newUserArray['communication'] = 'communication';
                $newUserArray['document'] = 'document';
                $newUserArray['contact'] = 'contact';
                $newUserArray['sponsor'] = 'sponsor';
            }
            if ($role == 'ROLE_SUPER') {
                $newUserArray['superAdmin'] = 'clubAdmin';
                $newUserArray['communication'] = 'communication';
                $newUserArray['document'] = 'document';
                $newUserArray['contact'] = 'contact';
                $newUserArray['sponsor'] = 'sponsor';
            }
        }

        return $newUserArray;
    }
}
