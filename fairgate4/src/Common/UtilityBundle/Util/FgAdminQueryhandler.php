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
class FgAdminQueryhandler
{

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
    public function __construct($container)
    {
        $this->container = $container;
        $this->club = $this->container->get('club');
        $this->conn = $this->container->get('fg.admin.connection')->getAdminConnection();
        $this->clubId = $this->club->get('id');
        $this->clubLanguages = $this->club->get('club_languages');
        $this->contactId = $this->club->get('contactId');
        $this->em = $this->container->get('fg.admin.connection')->getAdminManager();
        $this->clubDefaultLang = $this->club->get('club_default_lang');
    }

    /**
     * For generate a query and execute according to the settings and give result
     *
     * @param String $tablename Name of the table to be updated
     * @param Array  $catArr    Data for updating/deleting functionality
     */
    public function generatequeryAction($tablename, $catArr)
    {
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
            //Process query using transaction
            $this->addtotransaction($tablename);

            //INSERT/UPDATE BOOKMARK ENTRIES
            if ($this->bookmarkFlag) {
                $this->em->getRepository('AdminUtilityBundle:FgClubBookmarks')->handleBookmark('filter', $this->bookmarkArr, $this->clubId, $this->contactId);
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
    private function processUpdateArrayAction($catData, $categoryId, $tablename)
    {
        foreach ($catData as $field => $value) {
            if ($field == 'is_deleted') {
                $deletableFlag = 1;
                if ($deletableFlag) {
                    $this->delQryStr .= "$categoryId,";
                    $this->delCatIds[] = $categoryId;
                }
            } else if ($field == 'book_marked') {
                $this->bookmarkArr[] = $categoryId;
                $this->bookmarkFlag = true;
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
    private function processInsertArrayAction($newArr, $tablename)
    {
        $mainTitle = '';
        $categoryId = '##dummyId##';
        $insertArr[] = "$this->clubIdField = '$this->clubId'";
        //Iterating through json array for saving purpose
        foreach ($newArr as $field => $dataArr) {
            if ($field == 'book_marked') {
                if ($dataArr == '1') {
                    $this->bookmarkFlag = true;
                    $this->bookmarkArr[] = $categoryId;
                }
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
    private function iterateLanguageArray($langArr, $tablename, $categoryId, $action = 'insert')
    {
        foreach ($langArr as $langkey => $title) {
            $langTitle = FgUtility::getSecuredData($title['titleLang'], $this->conn, false, false);
            $newData = false;
            if ($langTitle == '' && $langkey == $this->clubDefaultLang) {
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
        }

        return $mainTitle;
    }

    /**
     * Function used to iterate through language array
     *
     * @param array   $insertArr     The array for iterating
     * @param string  $tablename     Name of the table tobe updated
     * @param Integer $categoryId    Category id
     */
    private function insertDataAndGetid($insertArr, $tablename, $categoryId)
    {
        if (count($insertArr)) {
            $insertQryStr = "INSERT INTO $tablename SET " . implode(',', $insertArr) . ";";
            $this->conn->executeQuery($insertQryStr);
            $insertedId = $this->conn->lastInsertId();
            if ($this->insertQryStri18n != '') {
                $insertQryWithId = str_replace($categoryId, $insertedId, $this->insertQryStri18n);
                $this->conn->executeQuery($insertQryWithId);
                $this->insertQryStri18n = '';
            }
            $this->bookmarkArr = str_replace($categoryId, $insertedId, $this->bookmarkArr);
            $this->conn->close();
        }
    }

    /**
     * Function used to generate update query using the update query string
     *
     * @param string  $tablename  name of the table to be updated
     * @param Integer $categoryId category id
     */
    private function updateQueryString($tablename, $categoryId)
    {
        if ($this->updateQryStr !== 'SET ') {
            $updateStr = rtrim($this->updateQryStr, ',');
            $this->updateQry .= "UPDATE $tablename $updateStr WHERE id = $categoryId AND " . $this->clubIdField . " = $this->clubId;";
            $this->updateQryStr = 'SET '; // Resetting the query string
        }
    }

    /**
     * Function for execute native update/delete query through transaction
     *
     * @param String $tablename     Table name
     * @param int    $updateLog     updateLog
     */
    private function addtotransaction($tablename)
    {
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
                $excludeI8n = array('fg_filter', 'fg_club_filter');
                if (!in_array($tablename, $excludeI8n)) {
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
     * Function to update main table title
     * @param string $tablename
     */
    private function updateDefaultTable($tablename)
    {
        $where = ($tablename == 'fg_club_classification') ? 'A.federation_id= ' . $this->clubId : 'A.club_id= ' . $this->clubId;
        $clubDefaultLang = $this->club->get('club_default_lang');
        $fieldArray = array('mainTable' => $tablename, 'i18nTable' => $tablename . '_i18n', 'mainField' => array('title'), 'i18nFields' => array('title_lang'));
        $query = FgUtility::updateDefaultTable($clubDefaultLang, $fieldArray, $where);

        $this->conn->executeQuery($query);
    }
}
