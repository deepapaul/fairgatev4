<?php
/**
 * FgGenericQueryhandler is used as a generic save functionality
 * It can be used for generating custom PDO queries and executing it.
 */
namespace Common\UtilityBundle\Util;

use Common\UtilityBundle\Util\FgUtility;

/**
 * Used for handling insert to different table as a generic solution
 *
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
class FgGenericQueryhandler {

    private $contactId;
    private $clubId;
    private $delQryStr = '';
    private $updateQry = '';
    private $updateQryStr = 'SET '; //Setting the query string
    private $updateQryStri18n = '';
    private $bookmarkArr = array();
    private $insertQryStri18n = '';
    private $unsetFedCat = array();
    private $delCatIds = array();
    private $log = array();
    private $logCount = 0;
    private $bookmarkFlag = false;
    private $clubIdField = '';
    private $clubLanguages = array();
    private $clubDefaultLang = '';

    /**
     * The array with the details of array
     * @var array
     */
    public $deletedIdArray;

    /**
     * Constructor for initial setting
     *
     * @param type $contactId
     * @param type $container
     * @param type $contactType(active/archive/formerfederationmember)
     */
    public function __construct($container) {
        $this->container = $container;
        $this->club = $this->container->get('club');
        $this->conn = $this->container->get('database_connection');
        $this->clubId = $this->club->get('id');
        $this->clubLanguages = $this->club->get('club_languages');
        $this->contactId = $this->club->get('contactId');
        $this->em = $this->container->get('doctrine')->getManager();
        $this->clubDefaultLang = $this->club->get('club_default_lang');
    }

    /**
     * For generate a query and execute according to the settings and give result
     *
     * @param String $tablename Name of the table to be updated
     * @param Array  $catArr    Data for updating/deleting functionality
     */
    public function generatequeryAction($tablename, $catArr, $contactType = 'contact') {
        $this->clubIdField = ($tablename == 'fg_club_classification') ? 'federation_id' : 'club_id';
        if (count($catArr) > 0) {            
            foreach ($catArr as $categoryId => $catData) {
                if ($categoryId != 'new') {                    
                    $this->processUpdateArrayAction($catData, $categoryId, $tablename);
                    $this->updateQueryString($tablename, $categoryId);
                } else {
                    foreach ($catData as $timestamp => $newArr) {                       
                        $this->processInsertArrayAction($newArr, $tablename);
                    }
                }
            }
            //Restrict delete functionality of membership category for keeping atleast 1 default entry
            $this->restrictMembershipDelete($tablename);
            //Generate log entry query
            if ($tablename == 'fg_cm_membership') {
                if (!empty($this->log)) {
                    $insertCatId = $this->log[$this->logCount - 1]['membership_id'];
                    #added for multilanguage membership log
                    foreach($this->log as $key=>$logArray){
                        if(in_array('##dummyId##',$logArray)){
                            $this->log[$key]['membership_id']=$insertCatId;
                        }
                    }
                    $genericLoghandler = new FgLogHandler($this->container);
                    $genericLoghandler->processLogEntryAction('membership', 'fg_cm_membership_log', $this->log);
                }
            }
            //Process query using transaction
            $this->addtotransaction($tablename);

            //INSERT/UPDATE BOOKMARK ENTRIES
            if ($this->bookmarkFlag) {
                $this->addtobookmark($tablename, $contactType);
            }
        }
    }

    /**
     * For Iterating through json array and update query string
     *
     * @param Array   $catData    Data for preparing query
     * @param Integer $categoryId Data for preparing query
     * @param String  $tablename  Data for preparing query
     */
    private function processUpdateArrayAction($catData, $categoryId, $tablename) {         
        foreach ($catData as $field => $value) {
            if ($field == 'is_deleted') {
                $deletableFlag = 1;
                if( $tablename == 'fg_filter'){
                    $deletableFlag = $this->em->getRepository('CommonUtilityBundle:FgCmsContactTable')->findOneBy(array('filter' => $categoryId));
                    if(empty($deletableFlag)){
                        $deletableFlag = 1;
                    }else{
                        $deletableFlag = 0;
                    }                    
                }
                if($deletableFlag){
                    $this->delQryStr .= "$categoryId,";
                    $this->delCatIds[] = $categoryId;
                }
            } else if ($field == 'book_marked') {
                $this->bookmarkArr[] = $categoryId;
                $this->bookmarkFlag = true;
            } else if ($field == 'title') {
                $mainTitle = $dataArr;
            } else if (is_array($value)) {
                $this->iterateLanguageArray($value, $tablename, $categoryId, 'update');
            } else {
                $this->updateQryStr .= "$field = '$value',";
            }
        }
    }

    /**
     * For process the array and update the insert query string
     *
     * @param Array  $newArr    Data for preparing query
     * @param String $tablename Table to be updated
     */
    private function processInsertArrayAction($newArr, $tablename) {
        $isFedCategory = 0;
        $mainTitle = '';
        $isActive = 1;
        $sortOrder = '';
        $contactAssign = '';
        $categoryId = '##dummyId##';
        $insertArr[] = "$this->clubIdField = '$this->clubId'";
        //Iterating through json array for saving purpose
        foreach ($newArr as $field => $dataArr) {
            if ($field == 'book_marked') {
                if ($dataArr == '1') {
                    $this->bookmarkFlag = true;
                    $this->bookmarkArr[] = $categoryId;
                }
            } else if ($field == 'contact_assign') {
                $contactAssign = FgUtility::getSecuredData($dataArr, $this->conn, false, false);
                $insertArr[] = "$field = '$contactAssign'";
            } else if (is_array($dataArr)) {
                    $mainTitle = $this->iterateLanguageArray($dataArr, $tablename, $categoryId, 'insert');
                    $insertArr[] = "title = '$mainTitle'";
            } else if ($field != 'is_deleted') {
                $insertArr[] = "$field = '$dataArr'";
            }
        }
        $this->insertDataAndGetid($insertArr, $tablename, $categoryId);
    }

    /**
     * Function used to iterate through language array
     *
     * @param array   $langArr    Language array for iterating
     * @param string  $tablename  Name of the table to be updated
     * @param Integer $categoryId Category id
     * @param return  $action  	  Whether it is an insert/update
     *
     * return string  $mainTitle  Title of the default language
     */
    private function iterateLanguageArray($langArr, $tablename, $categoryId, $action = 'insert') {
        foreach ($langArr as $langkey => $title) {
            $langTitle = FgUtility::getSecuredData($title['titleLang'], $this->conn, false, false);
            $newData = false;
            if ($langTitle == '' && $langkey == $this->clubDefaultLang ) {
                continue;
            }
            if ($action == 'insert') {
                $newData = true;
                $isActive = 1;
                $this->insertQryStri18n .= "INSERT INTO $tablename" . "_i18n" . " (`id`, `lang`, `title_lang`, `is_active`) VALUES ($categoryId, '$langkey', '$langTitle', $isActive);";
                if ($this->clubDefaultLang == $langkey) {
                    $mainTitle = $langTitle;
                }
            } else {
                $this->updateQryStri18n .= "INSERT INTO $tablename" . "_i18n" . " (`id`, `lang`, `title_lang`, `is_active`) VALUES ($categoryId, '$langkey', '$langTitle', 1) ON DUPLICATE KEY UPDATE `title_lang`=VALUES(`title_lang`) ;";
                if ($this->clubDefaultLang == $langkey) {
                    $this->updateQryStr .= "title = '$langTitle',";
                    $mainTitle = $langTitle;
                }
            }
            if ($tablename == 'fg_cm_membership') {
                //create LOG ENTRY array when membership updated
                $this->insertLogentry($tablename, $categoryId, $langkey, $langTitle, $newData);
            }
        }

        return $mainTitle;
    }

    /**
     * Function used to iterate through language array
     *
     * @param array   $mainTitle     Language array for iterating
     * @param string  $tablename     Name of the table tobe updated
     * @param Integer $categoryId    Category id
     * @param string  $sortOrder     Is this club field or federation field
     * @param Integer $isFedCategory Whether it is a federation category or not id
     * @param Integer $isActive      Is the item active or not
     * @param Integer $contactAssign Contact assignment poosible or not
     */
    private function insertDataAndGetid($insertArr, $tablename, $categoryId) {
        if (count($insertArr)) {
              $insertQryStr = "INSERT INTO $tablename SET " . implode(',', $insertArr) . ";";
//            if ($tablename == 'fg_team_category') {
//                $insertQryStr = "INSERT INTO $tablename (club_id, title, sort_order) VALUES ($this->clubId, '$mainTitle', $sortOrder);";
//            } else if ($tablename == 'fg_cm_membership') {
//                $insertQryStr = "INSERT INTO $tablename (club_id, title, sort_order, is_fed_category) VALUES ($this->clubId, '$mainTitle', $sortOrder, $isFedCategory);";
//            } else if ($tablename == 'fg_club_classification') {
//                $insertQryStr = "INSERT INTO $tablename (federation_id, title, sort_order, is_active) VALUES ($this->clubId, '$mainTitle', $sortOrder, $isActive);";
//            } else {
//                $insertQryStr = "INSERT INTO $tablename (club_id, title, sort_order, is_fed_category, is_active, contact_assign) VALUES ($this->clubId, '$mainTitle', $sortOrder, $isFedCategory, $isActive, '$contactAssign');";
//            }
            $this->conn->executeQuery($insertQryStr);
            $insertedId = $this->conn->lastInsertId();
            if ($this->insertQryStri18n != '') {
                $insertQryWithId = str_replace($categoryId, $insertedId, $this->insertQryStri18n);
                $this->conn->executeQuery($insertQryWithId);
                $this->insertQryStri18n = '';
            }
            $this->log[$this->logCount - 1] = str_replace($categoryId, $insertedId, $this->log[$this->logCount - 1]);
            $this->bookmarkArr = str_replace($categoryId, $insertedId, $this->bookmarkArr);
            $this->conn->close();
        }
    }

    /**
     * Function used to update log entry query
     *
     * @param string  $tablename  name of the table to be updated
     * @param Integer $categoryId category id
     * @param integer $langkey    language name
     * @param string  $langTitle  title value in each language
     * @param boolean $newData    Whether it is an insert/update
     */
    private function insertLogentry($tablename, $categoryId, $langkey, $langTitle, $newData) {
        //create LOG ENTRY array when new membership updated
        $valueBefore = $newData ? '' : "(SELECT title FROM $tablename WHERE id=$categoryId)";
        $this->log[$this->logCount] = array('field' => "Name($langkey)", 'value_before' => $valueBefore, 'value_after' => $langTitle, 'membership_id' => $categoryId);
        $this->logCount = $this->logCount + 1;
    }

    /**
     * Function used to generate update query using the update query string
     *
     * @param string  $tablename  name of the table to be updated
     * @param Integer $categoryId category id
     */
    private function updateQueryString($tablename, $categoryId) {
        if ($this->updateQryStr !== 'SET ') {
            $updateStr = rtrim($this->updateQryStr, ',');
            $this->updateQry .= "UPDATE $tablename $updateStr WHERE id = $categoryId AND " . $this->clubIdField . " = $this->clubId;";
            $this->updateQryStr = 'SET '; // Resetting the query string
        }
    }

    /**
     * Server side validation for restricting delete functionality
     * For keeping at least 1 default entry of membership
     * Restrict the delete assignment category if there are any assignment
     *
     * @param String $tablename Table name
     */
    private function restrictMembershipDelete($tablename) {
        if ($this->delQryStr != '' && $tablename == 'fg_cm_membership') {
            $exsql = "SELECT count(id) as cnt FROM $tablename WHERE club_id = $this->clubId;";
            $membershipCount = $this->conn->executeQuery($exsql)->fetch();
            $this->conn->close();
            $delArr = explode(',', rtrim($this->delQryStr, ','));
            if ($membershipCount['cnt'] <= count($delArr)) {
                $this->delQryStr = '';
            }
        } else if ($this->delQryStr != '' && $tablename == 'fg_rm_category') {
            $delArr = explode(',', rtrim($this->delQryStr, ','));
            /* Get existing assignments of selected categories - starts */
            $assignedCatIds = array();
            if (count($this->delCatIds) > 0) {
                $this->delCatIds = FgUtility::getSecuredData(implode(',', $this->delCatIds), $this->conn);
                $catAssignmentsQry = "SELECT GROUP_CONCAT(DISTINCT b.category_id) AS catIds FROM `fg_rm_role_contact` a "
                        . "LEFT JOIN `fg_rm_category_role_function` b ON (a.fg_rm_crf_id = b.id) "
                        . "WHERE b.category_id IN ($this->delCatIds)";
                $catAssignments = $this->conn->fetchAll($catAssignmentsQry);
                if ($catAssignments[0]['catIds'] != '') {
                    $assignedCatIds = explode(',', $catAssignments[0]['catIds']);
                }
            }
            /* Get existing assignments of selected categories - ends */
            $catIdsToRemove = array_diff($delArr, $assignedCatIds);
            $this->delQryStr = (count($catIdsToRemove) > 0) ? implode(',', $catIdsToRemove) : "";
        }
    }

    /**
     * Function for execute native update/delete query through transaction
     *
     * @param String $tablename     Table name
     * @param int    $updateLog     updateLog
     */
    private function addtotransaction($tablename) {
        $updateFullQry = $this->updateQry . $this->updateQryStri18n;
        if ($updateFullQry !== '' || $this->delQryStr != '' || count($this->unsetFedCat) > 0 || $updateLog !== '') {
            try {
                $this->conn->beginTransaction();
                if ($updateFullQry !== '') {
                    $this->conn->executeUpdate($updateFullQry);
                }
                if ($this->delQryStr != '') {
                    $delArr = explode(',', rtrim($this->delQryStr, ','));
                    $stmt = $this->conn->executeQuery("DELETE FROM $tablename WHERE id IN (?) AND $this->clubIdField = $this->clubId", array($delArr), array(\Doctrine\DBAL\Connection::PARAM_INT_ARRAY));
                    $this->deletedIdArray['category'] = $delArr;
                }
                if (count($this->unsetFedCat) > 0) {
                    $stmt = $this->conn->executeQuery("DELETE FROM fg_cm_bookmarks WHERE membership_id IN (?) AND type = 'membership' AND club_id = $this->clubId", array($this->unsetFedCat), array(\Doctrine\DBAL\Connection::PARAM_INT_ARRAY));
                }
                $excludeI8n = array('fg_filter','fg_club_filter');
                if(!in_array($tablename,$excludeI8n)){
                   
                     $this->updateDefaultTable($tablename);
                }
                $this->conn->commit();
            } catch (Exception $ex) {
                $this->conn->rollback();
                throw $ex;
            }
            $this->conn->close();
        }
    }

    /**
     * Function for adding bookmarks
     *
     * @param String $tablename     Table name
     */
    private function addtobookmark($tablename, $contactType = 'contact') {
        if ($tablename == 'fg_club_filter') {
            $this->em->getRepository('CommonUtilityBundle:FgClubBookmarks')->handleBookmark('filter', $this->bookmarkArr, $this->clubId, $this->contactId);
        } else {
            $bookmarkType = $tablename == 'fg_filter' ? 'filter' : 'membership';
            if ($contactType == 'sponsor') {
                $this->em->getRepository('CommonUtilityBundle:FgSmBookmarks')->createDeletebookmark($bookmarkType, $this->bookmarkArr, $this->clubId, $this->contactId);
            } else {
                $this->em->getRepository('CommonUtilityBundle:FgCmBookmarks')->createDeletebookmark($bookmarkType, $this->bookmarkArr, $this->clubId, $this->contactId);
            }
        }
    }

    /**
     * Function to update main table title
     * @param string $tablename
     */
    private function updateDefaultTable($tablename){
        $where = ($tablename == 'fg_club_classification') ? 'A.federation_id= '.$this->clubId :'A.club_id= '.$this->clubId;
        $clubDefaultLang = $this->club->get('club_default_lang');
        $fieldArray = array('mainTable'=>$tablename,'i18nTable'=>$tablename.'_i18n','mainField'=>array('title'),'i18nFields'=>array('title_lang'));
        $query = FgUtility::updateDefaultTable($clubDefaultLang, $fieldArray, $where );

        $this->conn->executeQuery($query);
    }

}
