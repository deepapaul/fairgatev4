<?php

namespace Clubadmin\ContactBundle\Util;

use Common\UtilityBundle\Util\FgSettings;

/**
 * This class is used to handle import data validation
 *
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
class ImportValidation
{
    
    /**
     * $clubId
     * @var Integer Club id
     */
    private $clubId;
    
    /**
     * $federationId
     * @var Integer Federation id
     */
    private $federationId;
    
    /**
     * $allFixedFields
     * @var Array Array of system fields which are required
     */
    public $allFixedFields;
    
    /**
     * $updateColumns
     * @var Array Array of system fields which are required
     */
    private $updateColumns = array();
    
    /**
     * $contactFields
     * @var Array Array of all club fields
     */
    private $contactFields;
    
    /**
     * $systemFields
     * @var Array Array of all system fields
     */
    private $systemFields;
    
    /**
     * $countryFields
     * @var Array Array of all system country fields
     */
    private $countryFields;
    
    /**
     * $fixMandatoryFields
     * @var Array Array of all fixed madatory fields
     */
    public $fixMandatoryFields;
    
    /**
     * $mappingTableInsertRow
     * @var Array Array of rows to insert to import mapping table
     */
    private $mappingTableInsertRow = array();
    
    /**
     * $subscriberColumn
     * @var String To check subscriber column is there to import
     */
    private $subscriberColumn = '';
    
    /**
     * $corresFieldsToImport
     * @var Array correspondance fields to import
     */
    private $corresFieldsToImport = array();
    
    /**
     * $clubTable
     * @var String Master club table name
     */
    private $clubTable = '';
    
    /**
     * $invoiceCatId
     * @var Integer Invoice category id
     */
    private $invoiceCatId;
    
    /**
     * $corresCatId
     * @var Integer Correspondance category id
     */
    private $corresCatId;
    
    /**
     * $isSameAsInvoice
     * @var Boolean Same as invoice flog for contact
     */
    private $isSameAsInvoice = true;
    
    /**
     * $joinDateValidateQuery
     * @var String joindate validation query
     */
    private $joinDateValidateQuery = '';
    
    /**
     * member validation query for joining and leaving date
     *
     * @var type
     */
    private $memberValidateQuery = '';
    
    /**
     * member validation query for joining and leaving date
     *
     * @var type
     */
    private $fedmemberValidateQuery = '';
    
    /**
     * contactid validation query for joining and leaving date
     *
     * @var type
     */
    private $contactIdValidateQuery = '';
    
    /**
     * leaving date invalid checking query
     * @var type
     */
    private $leavingDateInvalidQuery = '';
    
    /**
     * joining date invalid checking query
     * @var type
     */
    private $joinDateInvalidQuery = '';
    
    /**
     * $headerRow
     * @var Array Column title in CSV
     */
    private $headerRow = array();
    
    /**
     * $emailDupQuery
     * @var String $emailDupQuery for primary email only to whether the email already exists in the club
     */
    private $emailDupQuery = '';
    
    /**
     * $primaryEmailTitle
     * @var String $primaryEmailTitle for primary email only to check duplicate primay emails are there to import
     */
    private $primaryEmailTitle = '';
    
    /**
     * $inCorrectSinglelineRows
     * @var Array Array of queries to check validity of single line columns
     */
    private $inCorrectSinglelineRows = array();
    
    /**
     * $singleLineTextMaxLength
     * @var Integer $primaryEmailTitle Length of single line text field
     */
    private $singleLineTextMaxLength = 0;
    
    /**
     * $multiLineTextMaxLength
     * @var Integer $primaryEmailTitle Length of multi line text field
     */
    private $multiLineTextMaxLength = 0;
    
    /**
     * $validationQueries
     * @var Array Array of queries to check validity columns
     */
    private $validationQueries = array();
    
    /**
     * $errorRows
     * @var Array Array of error records
     */
    private $errorRows = array();
    
    /**
     * $validationQueries
     * @var String Country codes, coma separated string
     */
    public $countryCodes;
    
    /**
     * $validationData
     * @var array Array of information for further processing
     */
    public $validationData = array();
    /*
     * Connection
     * @var Obeject
     */
    private $conn;
    
    /**
     * Joining date maping column
     *
     * @var String
     */
    public $joinCol = '';
    
    /**
     * Leaving date maping column
     *
     * @var String
     */
    public $leavingCol = '';
    
    /**
     * Constructor for initial setting
     * @param type $mappedFields Array of fields which are mapped to import
     * @param type $container
     */
    public function __construct($importData, $container)
    {
        $this->importData              = $importData;
        $this->container               = $container;
        $club                          = $this->container->get('club');
        $this->clubId                  = $club->get('id');
        $this->clubType                = $club->get('type');
        $this->federationId            = $club->get('federation_id');
        $this->fedMembershipMandatory  = $club->get('fedMembershipMandatory');
        $this->invoiceCatId            = $this->container->getParameter('system_category_invoice');
        $this->corresCatId             = $this->container->getParameter('system_category_address');
        $this->systemFields            = $this->container->getParameter('system_fields');
        $this->singleLineTextMaxLength = $this->container->getParameter('singleline_max_length');
        $this->multiLineTextMaxLength  = $this->container->getParameter('multiline_max_length');
        $this->countryFields           = $this->container->getParameter('country_fields');
        $this->clubTable               = $club->get('clubTable');
        $this->contactFields           = $club->get('allContactFields');
        $this->conn                    = $container->get('database_connection');
    }
    
    /**
     * Function to validate imported data
     * @return array
     */
    public function validateData()
    {
        $this->setHeaderRows();
        $this->importData['tableName'];
        $this->allFixedFields = $this->getStaticFields();
        $staticFields         = array_keys($this->allFixedFields);
        $salutation           = $this->container->getParameter('system_field_salutaion');
        $gender               = $this->container->getParameter('system_field_gender');
        $inTraslation         = $this->container->get('translator')->trans('IMPORT_IN');
        foreach ($this->importData['mapingFields'] as $key => $field) {
            
            //Skip validation if the column skipped in import step 2 or field id is empty or not aclub field
            if ($field == '' || in_array($key, $this->importData['fieldSkipped'])) {
                if ($field == 'fed_membership' || $field == 'member_category') {
                    $updatefield = ($field == 'fed_membership') ? 'fed_membership_id' : 'membership_id';
                    $this->conn->executeQuery("UPDATE `{$this->importData['tableName']}` SET $updatefield=NULL  ");
                }
                continue;
            }
            $col = 'column' . $key;
            if (in_array($field, $staticFields)) {
                $inputType  = $this->allFixedFields[$field]['type'];
                $fieldTitle = $this->allFixedFields[$field]['title'];
                if ($col != 'leaving_date') {
                    $this->updateStaticFieldDetails($field, $col, $inTraslation);
                }
            } elseif (!array_key_exists($field, $this->contactFields)) {
                continue;
            } else {
                $inputType  = $this->contactFields[$field]['type'];
                $fieldTitle = $this->updateInvoiceAndCorrespondanceFields($field, $col, $fieldTitle);
            }
            if (in_array($field, $this->fixMandatoryFields)) {
                $this->validationQueries['mandatory'][] = "SELECT '$fieldTitle' AS column_name,'{$this->headerRow[$col]}' AS header_name, GROUP_CONCAT(CONCAT(' ',row_id) SEPARATOR  ', ') AS err_rows FROM `{$this->importData['tableName']}` WHERE is_removed=0 AND  (LENGTH(`$col`) = 0 OR `$col` IS NULL)";
            }
            if ($field == $salutation || $field == $gender) {
                $this->updateColumns[$col]              = ($field == $salutation) ? 'salutation' : 'gender';
                /* Only 1,2 values are supported for this column */
                $this->validationQueries['incorrect'][] = "SELECT '$fieldTitle' AS column_name,'{$this->headerRow[$col]}' AS header_name, GROUP_CONCAT(CONCAT(' ',row_id) SEPARATOR  ', ') AS err_rows FROM `{$this->importData['tableName']}` WHERE is_removed=0 AND  (REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' ) !='1' AND REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' ) !='2'  AND LENGTH(REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' )) > 0 AND REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' ) IS  NOT NULL AND REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' ) != '')";
            }
            $this->setValidationQuery($inputType, $fieldTitle, $col, $inTraslation);
            if (in_array($field, $this->countryFields)) {
                $this->updateColumns[$col]            = 'country';
                $this->validationQueries['country'][] = "SELECT '$fieldTitle' AS column_name,'{$this->headerRow[$col]}' AS header_name, GROUP_CONCAT(CONCAT(' ',row_id) SEPARATOR  ', ') AS err_rows FROM `{$this->importData['tableName']}` WHERE is_removed=0 AND  (`$col` IS NOT NULL AND `$col`!='' AND UPPER(`$col`) NOT IN($this->countryCodes))";
            }
        }
        $this->setErrorRows();
        $this->setJoindateAndEmalDuplicate();
        $this->validationData = array(
            'mappingTableInsertQuery' => $this->mappingTableInsertRow,
            'isSameAsInvoice' => $this->isSameAsInvoice,
            'errorRows' => $this->errorRows,
            'updateColumns' => $this->updateColumns,
            'subscriberColumn' => $this->subscriberColumn,
            'corresFieldsToImport' => $this->corresFieldsToImport
        );
    }
    
    /**
     * 
     /**
     * Update membership details in the temp table
     *
     * @param type $column Import table column
     */
    private function updateMembershipDet($column, $federationMembership = 0)
    {
        #modified for fedv2
        $clubId = $this->clubId;
        if ($federationMembership == 1) {
            $clubId = $this->federationId;
            //echo "UPDATE `{$this->importData['tableName']}` AS tmp LEFT JOIN fg_cm_membership " . "AS cm  on cm.club_id='{$clubId}' JOIN fg_cm_membership_i18n mi ON cm.id=mi.id   and  mi.title_lang=tmp.$column   SET tmp.member_clubid = cm.club_id,tmp.fed_membership_id = cm.id";
            $this->conn->executeQuery("UPDATE `{$this->importData['tableName']}` AS tmp LEFT JOIN fg_cm_membership " . "AS cm  on cm.club_id='{$clubId}' JOIN fg_cm_membership_i18n mi ON cm.id=mi.id   and  mi.title_lang=tmp.$column AND tmp.$column != '' SET tmp.member_clubid = cm.club_id,tmp.fed_membership_id = cm.id");
        } else {
            $this->conn->executeQuery("UPDATE `{$this->importData['tableName']}` AS tmp LEFT JOIN fg_cm_membership " . "AS cm  on cm.club_id='{$clubId}' JOIN fg_cm_membership_i18n mi ON cm.id=mi.id   and  mi.title_lang=tmp.$column AND tmp.$column != '' SET tmp.member_clubid = cm.club_id, tmp.membership_id = cm.id");
        }
    }
    /**
     * Update main club id and shre club id for import with share
     * @param int $column index of club id column
     */
    private function updateClubIdsToShare($column){
        $this->conn->executeQuery("UPDATE `{$this->importData['tableName']}` T SET $column=REPLACE($column,':',',')");
        $this->conn->executeQuery("UPDATE `{$this->importData['tableName']}` AS tmp SET tmp.main_club_id = IF(locate(',',{$column})=0,{$column},LEFT({$column},locate(',',{$column})-1)), tmp.share_club_ids = IF(locate(',',{$column})=0,'',SUBSTRING({$column},locate(',',{$column})+1))");
        $this->conn->executeQuery("UPDATE `{$this->importData['tableName']}` T INNER JOIN fg_club C ON T.main_club_id=C.id SET T.main_club_type=C.club_type");
    }

        /**
     * Validate membership query
     */
    private function getMembershipValidateQuery($col, $federationQuery = 0)
    {
        $memField   = 'member_category';
        $memFieldId = 'membership_id';
        if ($federationQuery == 1) {
            $memField   = 'fed_membership';
            $memFieldId = 'fed_membership_id';
        }
        $mysqlDateFormat = FgSettings::getMysqlDateFormat();
        if ((($leavingKey = array_search('leaving_date', $this->importData['mapingFields'])) != null) && (($joinKey = array_search('joining_date', $this->importData['mapingFields'])) != null)) {
            $leavingKey = 'column' . $leavingKey;
            $joinKey    = 'column' . $joinKey;
            if ($federationQuery == 1) {
                $this->fedmemberValidateQuery = "SELECT 'member_category' AS column_name,'{$this->headerRow[$col]}' AS header_name, GROUP_CONCAT(CONCAT('',row_id) SEPARATOR  ', ') AS err_rows FROM `{$this->importData['tableName']}` WHERE is_removed=0 AND  (LENGTH($leavingKey) > 0 AND $leavingKey IS NOT NULL AND $leavingKey != '' AND STR_TO_DATE($leavingKey, '$mysqlDateFormat')!='0000-00-00' AND ($memFieldId IS NULL OR $memFieldId ='' ) AND (CURDATE()>STR_TO_DATE($leavingKey, '$mysqlDateFormat') AND STR_TO_DATE($leavingKey, '$mysqlDateFormat') > STR_TO_DATE($joinKey, '$mysqlDateFormat')) )";
            } else {
                $this->memberValidateQuery = "SELECT 'member_category' AS column_name,'{$this->headerRow[$col]}' AS header_name, GROUP_CONCAT(CONCAT('',row_id) SEPARATOR  ', ') AS err_rows FROM `{$this->importData['tableName']}` WHERE is_removed=0 AND  (LENGTH($leavingKey) > 0 AND $leavingKey IS NOT NULL AND $leavingKey != '' AND STR_TO_DATE($leavingKey, '$mysqlDateFormat')!='0000-00-00' AND ($memFieldId IS NULL OR $memFieldId ='' ) AND (CURDATE()>STR_TO_DATE($leavingKey, '$mysqlDateFormat') AND STR_TO_DATE($leavingKey, '$mysqlDateFormat') > STR_TO_DATE($joinKey, '$mysqlDateFormat')) )";
            }
            $this->joinDateInvalidQuery    = "SELECT 'joining_date' AS column_name,'{$this->headerRow[$joinKey]}' AS header_name, GROUP_CONCAT(CONCAT('',row_id) SEPARATOR  ', ') AS err_rows FROM `{$this->importData['tableName']}` WHERE is_removed=0 AND  (LENGTH($joinKey) > 0 AND $joinKey IS NOT NULL AND $joinKey != '' AND STR_TO_DATE($joinKey, '$mysqlDateFormat')!='0000-00-00' AND $memFieldId IS NOT NULL AND $memFieldId !='' ) AND (day(CONCAT('',STR_TO_DATE($joinKey, '$mysqlDateFormat'),'')) IS NULL ) ";
            $this->leavingDateInvalidQuery = "SELECT 'leaving_date' AS column_name,'{$this->headerRow[$leavingKey]}' AS header_name, GROUP_CONCAT(CONCAT('',row_id) SEPARATOR  ', ') AS err_rows FROM `{$this->importData['tableName']}` WHERE is_removed=0 AND  (LENGTH($leavingKey) > 0 AND $leavingKey IS NOT NULL AND $leavingKey != '' AND STR_TO_DATE($leavingKey, '$mysqlDateFormat')!='0000-00-00' AND $memFieldId IS NOT NULL AND $memFieldId !='' ) AND (day(CONCAT('',STR_TO_DATE($leavingKey, '$mysqlDateFormat'),'')) IS NULL )  ";
        }
    }
    
    private function getvalidateContactid($col)
    {
        $commonCondition = " ( cc.club_id = '{$this->clubId}') AND ((cc.main_club_id=cc.club_id) OR (cc.fed_membership_cat_id IS NOT NULL AND (cc.old_fed_membership_id IS NOT NULL OR cc.is_fed_membership_confirmed='0')) )";
        $this->conn->executeQuery("UPDATE `{$this->importData['tableName']}` AS tmp inner join fg_cm_contact cc on cc.id=tmp.$col    SET tmp.is_fed_admin=cc.is_fed_admin, tmp.contact_id = cc.id,  tmp.subfed_contact_id = cc.subfed_contact_id,tmp.fed_contact_id = cc.fed_contact_id where $commonCondition");
        $contactTypeCondition = " cc.is_company=1 ";
        if($this->importData['contactType']=='companyNoMain')
            $contactTypeCondition ="(cc.is_company=0)";
        else if($this->importData['contactType']=='companyWithMain')
            $contactTypeCondition =" (cc.is_company=0 or  (cc.is_company=1 AND cc.comp_def_contact is   null))";
        $this->contactIdValidateQuery = "SELECT T.`contact_id` AS column_name,'{$this->headerRow[$col]}' AS header_name, GROUP_CONCAT(CONCAT('',row_id) SEPARATOR  ', ') AS err_rows FROM `{$this->importData['tableName']}` T left join fg_cm_contact cc  on T.$col=cc.id   WHERE T.is_removed=0  AND  IF(T.fed_contact_id IS NOT NULL,$contactTypeCondition,T.fed_contact_id is null ) ";
    
   }
    
    /**
     * Update static fields
     *
     * @param Integer $field        Contact field id
     * @param String  $col          Column in import table
     * @param String  $inTraslation Trenslation for string 'in'
     *
     */
    private function updateStaticFieldDetails($field, $col, $inTraslation)
    {
        $this->updateColumns[$col] = $field;
        $mysqlDateFormat           = FgSettings::getMysqlDateFormat();
        $clubId                    = $this->clubId;
        switch ($field) {
            case 'member_category':
                $this->updateMembershipDet($col);
                $this->getMembershipValidateQuery($col);
                
                break;
            case 'fed_membership':
                $this->updateMembershipDet($col, 1);
                $this->fedmemberValidateQuery = "SELECT 'member_category' AS column_name,'{$this->headerRow[$col]}' AS header_name, GROUP_CONCAT(CONCAT('',row_id) SEPARATOR  ', ') AS err_rows FROM `{$this->importData['tableName']}` WHERE is_removed=0 AND  (LENGTH($col) > 0 AND $col IS NOT NULL AND (fed_membership_id IS NULL OR fed_membership_id ='' )  )";
                #modified for fedv2 for required 
                if ($this->fedMembershipMandatory == 1) {
                    $this->fedmemberValidateQuery = "SELECT 'fed_membership' AS column_name,'{$this->headerRow[$col]}' AS header_name, GROUP_CONCAT(CONCAT('',row_id) SEPARATOR  ', ') AS err_rows FROM `{$this->importData['tableName']}` WHERE is_removed=0 AND (fed_membership_id IS  NULL OR fed_membership_id ='' )";
                }
                if ($this->clubType == 'federation') {
                    $this->getMembershipValidateQuery($col, 1);
                }
                break;
            case 'is_newsletter_subscriber':
                $this->subscriberColumn        = $col;
                $this->mappingTableInsertRow[] = "('$col', 'is_subscriber', '" . $this->allFixedFields[$field]['title'] . "', 'fg_cm_contact', '{$this->importData['tableName']}','$this->clubId')";
                break;
            case 'joining_date':
                $this->importData['mapingFields'];
                $import_Data = array_diff_key($this->importData['mapingFields'], $this->importData['fieldSkipped']);
                //if leaving date is maped join date mandatory checking and leaving date validation.
                if (($joinKey = array_search('leaving_date', $import_Data)) != null) {
                    if (($this->clubType == 'federation' && array_search('fed_membership', $this->importData['mapingFields'])) || (($this->clubType == 'sub_federation_club' || $this->clubType == 'federation_club' || $this->clubType == 'standard_club') && array_search('member_category', $this->importData['mapingFields']))) {
                        $this->leavingCol               = 'column' . $joinKey;
                        $this->joinDateMandatoryQuery   = "SELECT 'joining_date' AS column_name,'{$this->headerRow[$col]}' AS header_name, GROUP_CONCAT(CONCAT( '',row_id) SEPARATOR  ', ') AS err_rows FROM `{$this->importData['tableName']}` WHERE is_removed=0 AND ((LENGTH($this->leavingCol) > 0 AND $this->leavingCol IS  NOT NULL AND $this->leavingCol != '') AND (LENGTH($col) = 0 OR $col IS  NULL OR $col = ''))";
                        $this->leavingDateValidateQuery = "SELECT 'leaving_date' AS column_name,'{$this->headerRow[$this->leavingCol]}' AS header_name, GROUP_CONCAT(CONCAT( '',row_id) SEPARATOR  ', ') AS err_rows FROM `{$this->importData['tableName']}` WHERE is_removed=0 AND  (LENGTH($this->leavingCol) > 0 AND $this->leavingCol IS NOT NULL AND $this->leavingCol != '' AND STR_TO_DATE(`$this->leavingCol`, '$mysqlDateFormat') !='0000-00-00' AND ( day(CONCAT('',STR_TO_DATE(`$this->leavingCol`, '$mysqlDateFormat'),'')) IS NULL OR STR_TO_DATE($this->leavingCol, '$mysqlDateFormat')>CURDATE() OR STR_TO_DATE($col, '$mysqlDateFormat')>STR_TO_DATE($this->leavingCol, '$mysqlDateFormat')) )";
                    }
                }
                $this->mappingTableInsertRow[] = "('$col', '$field', '" . $this->allFixedFields[$field]['title'] . "', 'fg_cm_contact', '{$this->importData['tableName']}','$clubId')";
                $this->joinDateValidateQuery   = "SELECT 'joining_date' AS column_name,'{$this->headerRow[$col]}' AS header_name, GROUP_CONCAT(CONCAT( '',row_id) SEPARATOR  ', ') AS err_rows FROM `{$this->importData['tableName']}` WHERE is_removed=0 AND (LENGTH($col) > 0 AND $col IS  NOT NULL AND $col != ''  AND day(CONCAT('',STR_TO_DATE($col, '$mysqlDateFormat'),'')) IS NOT NULL AND  STR_TO_DATE($col, '$mysqlDateFormat')>CURDATE())";
                $this->joinCol                 = $col;
                break;
            case 'first_joining_date':
                $memType   = 'club';
                $contactId = "T.contact_id";
                if ($this->clubType == 'federation') {
                    $contactId = "T.fed_contact_id";
                    $memType   = 'federation';
                }
                $this->firstJoinDateValidateQuery = "SELECT 'first_joining_date' AS column_name,'{$this->headerRow[$col]}' AS header_name, GROUP_CONCAT(CONCAT( '',T.row_id) SEPARATOR  ', ') AS err_rows FROM `{$this->importData['tableName']}` T WHERE T.is_removed=0 AND (LENGTH(T.$col) > 0 AND T.$col IS  NOT NULL AND T.$col != ''  AND day(CONCAT('',STR_TO_DATE(T.$col, '$mysqlDateFormat'),'')) IS NOT NULL AND  (STR_TO_DATE(T.$col, '$mysqlDateFormat')>CURDATE() OR STR_TO_DATE(T.$col, '$mysqlDateFormat')>(SELECT IF(leaving_date IS NULL,CURDATE(),leaving_date) FROM fg_cm_membership_history WHERE contact_id=$contactId AND membership_type='$memType' ORDER BY joining_date ASC limit 1)))";
                $this->mappingTableInsertRow[]    = "('$col', '$field', '" . $this->allFixedFields[$field]['title'] . "', 'fg_cm_contact', '{$this->importData['tableName']}','$clubId')";
                
                break;
            case 'contact_id':
                $this->getvalidateContactid($col);
                $this->mappingTableInsertRow[] = "('$col', '$field', 'contact_id', 'fg_cm_contact', '{$this->importData['tableName']}','$clubId')";
                break;
            case 'club_ids':
                $this->updateClubIdsToShare($col);
                $clubQuery = "SELECT GROUP_CONCAT(id) ids FROM fg_club WHERE federation_id=$clubId AND club_type IN ('federation_club','sub_federation_club')";
                $clubIds = $this->conn->fetchAll($clubQuery);
                $this->clubIdValidateQuery = "SELECT 'club_id' AS column_name,'{$this->headerRow[$col]}' AS header_name, GROUP_CONCAT(CONCAT( '',T.row_id) SEPARATOR  ', ') AS err_rows FROM `{$this->importData['tableName']}` T WHERE T.is_removed=0 AND (LENGTH(T.$col) > 0 AND T.$col IS  NOT NULL AND T.$col != '') AND FIND_SET_IN_SET(T.$col,'".$clubIds[0]['ids']."')=0";
                $this->mappingTableInsertRow[] = "('$col', '$field', 'club_id', 'fg_cm_contact', '{$this->importData['tableName']}','$clubId')";
                break;
            case 'intranet_access':
                $this->mappingTableInsertRow[] = "('$col', '$field', '" . $this->allFixedFields[$field]['title'] . "', 'fg_cm_contact', '{$this->importData['tableName']}','$clubId')";
                break;
            default:
                $this->mappingTableInsertRow[] = "('$col', '$field', '" . $this->allFixedFields[$field]['title'] . "', 'fg_cm_contact', '{$this->importData['tableName']}','$clubId')";
                break;
        }
    }
    
    /**
     * Update invoice & correspondance fields
     *
     * @param Integer $field      Contact field id
     * @param String  $col        Column in import table
     * @param String  $fieldTitle Import table column
     *
     * @return String
     */
    private function updateInvoiceAndCorrespondanceFields($field, $col, $fieldTitle)
    {
        $fgTableName = (in_array($field, $this->systemFields) ? 'master_system' : (($this->contactFields[$field]['club_id'] == $this->clubId) ? $this->clubTable : 'master_federation_' . $this->contactFields[$field]['club_id']));
        switch ($this->contactFields[$field]['category_id']) {
            case $this->invoiceCatId:
                $this->isSameAsInvoice = false;
                $fieldTitle            = $this->contactFields[$field]['title'] . '(Rg.)';
                break;
            case $this->corresCatId:
                $this->corresFieldsToImport[$field] = array(
                    'column' => $col,
                    'title' => $fieldTitle . ' (Rg.)',
                    'table' => $fgTableName
                );
                $fieldTitle                         = $this->contactFields[$field]['title'] . ' (Korr.)';
                break;
            default:
                $fieldTitle = $this->contactFields[$field]['title'];
                break;
        }
        $clubMapId = $this->contactFields[$field]['club_id'];
        if ($this->contactFields[$field]['club_id'] == 1 || ($fgTableName == 'master_system' && $this->contactFields[$field]['club_id'] == 0)) {
            $clubMapId = $this->federationId;
        }
        if ($this->clubType == 'standard_club') {
            $clubMapId = $this->clubId;
        }
        
        $this->mappingTableInsertRow[] = "('$col', '$field', '" . $fieldTitle . "', '$fgTableName', '{$this->importData['tableName']}','{$clubMapId}')";
        
        return $fieldTitle;
    }
    
    /**
     * Set header rows to get title of each column
     *
     */
    private function setHeaderRows()
    {
        $headerRowResult = $this->conn->fetchAll("SELECT * FROM {$this->importData['tableName']} WHERE row_id=1");
        $this->headerRow = $headerRowResult[0];
    }
    
    /**
     * Set validation for diffrent types of fields
     *
     * @param String $inputType  Type of input column
     * @param String $fieldTitle Contact field title
     * @param String $col Import table column
     * @param String $inTraslation Translation for term 'in'
     *
     */
    private function setValidationQuery($inputType, $fieldTitle, $col, $inTraslation)
    {
        switch ($inputType) {
            case 'number':
                $this->updateColumns[$col]           = 'number';
                $this->validationQueries['number'][] = "SELECT '$fieldTitle' AS column_name,'{$this->headerRow[$col]}' AS header_name, GROUP_CONCAT(CONCAT(' ',row_id) SEPARATOR  ', ') AS err_rows FROM `{$this->importData['tableName']}` WHERE is_removed=0 AND  (( `$col` NOT REGEXP '^[0-9]+(\\" . FgSettings::getDecimalMarker() . "[0-9]{2})+' AND `$col` NOT REGEXP '^[0-9]+$'  ) AND LENGTH(REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' )) > 0 AND REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' ) IS  NOT NULL AND REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' ) != '')";
                break;
            case 'date':
                $this->updateColumns[$col]         = 'date';
                $dateFormat                        = FgSettings::getMysqlDateFormat();
                $this->validationQueries['date'][] = "SELECT '$fieldTitle' AS column_name,'{$this->headerRow[$col]}' AS header_name, GROUP_CONCAT(CONCAT( ' ',row_id) SEPARATOR  ', ') AS err_rows FROM `{$this->importData['tableName']}` WHERE is_removed=0 AND  (REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' )  AND day(CONCAT('',STR_TO_DATE(`$col`, '$dateFormat'),'')) IS NULL AND LENGTH(REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' )) > 0 AND REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' ) IS  NOT NULL AND REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' ) != '')";
                break;
            case 'email':
                $this->validationQueries['email'][] = "SELECT '$fieldTitle' AS column_name,'{$this->headerRow[$col]}' AS header_name, GROUP_CONCAT(CONCAT(' ',row_id) SEPARATOR  ', ') AS err_rows FROM `{$this->importData['tableName']}` WHERE is_removed=0 AND  (REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' )  NOT REGEXP \"^[A-Z0-9!#$%&*+-/=?^_`{|}~']+@([a-zA-Z0-9._-]+)+[.]([a-zA-Z]{2,5}){1,25}$\" AND LENGTH(REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' )) > 0 AND REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' ) IS  NOT NULL AND REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' ) != '')";
                break;
            case 'login email':
                $this->validationQueries['email'][] = "SELECT '$fieldTitle' AS column_name,'{$this->headerRow[$col]}' AS header_name, GROUP_CONCAT(CONCAT(' ',row_id) SEPARATOR  ', ') AS err_rows FROM `{$this->importData['tableName']}` WHERE is_removed=0 AND  (REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' )  NOT REGEXP \"^[A-Z0-9!#$%&*+-/=?^_`{|}~']+@([a-zA-Z0-9._-]+)+[.]([a-zA-Z]{2,5}){1,25}$\" AND LENGTH(REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' )) > 0 AND REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' ) IS  NOT NULL AND REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' ) != '')";
                $this->emailDupQuery                = "SELECT GROUP_CONCAT(CONCAT(' ',row_id) SEPARATOR  ', ') AS err_rows, TRIM({$this->importData['tableName']}.`$col`) AS dup_value FROM `{$this->importData['tableName']}` INNER JOIN (SELECT TRIM(`$col`) AS dupvalue FROM {$this->importData['tableName']} WHERE is_removed=0 AND  LENGTH(REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' )) > 0 AND REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' ) IS  NOT NULL AND REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' ) != '' GROUP BY dupvalue HAVING count( dupvalue ) >1 )dup ON {$this->importData['tableName']}.`$col` = dup.dupvalue";
                $this->primaryEmailTitle            = $fieldTitle;
                break;
            case 'singleline':
                $this->validationQueries['singleline'][] = "SELECT '$fieldTitle' AS column_name,'{$this->headerRow[$col]}' AS header_name, GROUP_CONCAT(CONCAT(' ',row_id) SEPARATOR  ', ') AS err_rows FROM `{$this->importData['tableName']}` WHERE is_removed=0 AND  (LENGTH(`$col`) >" . $this->singleLineTextMaxLength . " AND LENGTH(REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' )) > 0 AND REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' ) IS  NOT NULL AND REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' ) != '')";
                break;
            case 'multiline':
                $this->validationQueries['multiline'][] = "SELECT '$fieldTitle' AS column_name,'{$this->headerRow[$col]}' AS header_name, GROUP_CONCAT(CONCAT(' ',row_id) SEPARATOR  ', ') AS err_rows FROM `{$this->importData['tableName']}` WHERE is_removed=0 AND  (LENGTH(`$col`) >" . $this->multiLineTextMaxLength . " AND LENGTH(REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' )) > 0 AND REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' ) IS  NOT NULL AND REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' ) != '')";
                break;
            case 'url':
                $this->updateColumns[$col]        = 'url';
                $this->validationQueries['url'][] = "SELECT '$fieldTitle' AS column_name,'{$this->headerRow[$col]}' AS header_name, GROUP_CONCAT(CONCAT(' ',row_id) SEPARATOR  ', ') AS err_rows FROM `{$this->importData['tableName']}` WHERE is_removed=0 AND  (`$col`  NOT
                REGEXP \"^([a-zA-Z]{2,5}://|(www\\.)?)[\.A-Za-z0-9äöü \-]+\\.[a-zA-Z]{2,4}\" AND LENGTH(REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' )) > 0 AND REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' ) IS  NOT NULL AND REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' ) != '')";
                break;
            case 'checkbox':
                if ($this->importData['csvType'] == 'semicolon') {
                    $this->updateColumns[$col] = 'checkbox';
                }
                break;
        }
    }
    
    /**
     * Executes validation query and error records
     *
     */
    private function setErrorRows()
    {
        $mysqlDateFormat = FgSettings::getPhpDateFormat();
        foreach ($this->validationQueries as $key => $rows) {
            $result = $this->conn->fetchAll(implode(' UNION ', $rows));
            switch ($key) {
                case 'mandatory':
                    $errorMessage = $this->container->get('translator')->trans('IMPORT_NO_MANDATORY_VALUE_IN_ROWS');
                    break;
                case 'incorrect':
                    
                    $errorMessage = $this->container->get('translator')->trans('IMPORT_INCORRECT_VALUE_IN_ROWS');
                    break;
                case 'number':
                    
                    $errorMessage = $this->container->get('translator')->trans('IMPORT_INCORRECT_NUMBER_IN_ROWS');
                    break;
                case 'date':
                    
                    $errorMessage = $this->container->get('translator')->trans('IMPORT_INCORRECT_DATE_IN_ROWS', array(
                        '%dateValue%' => date($mysqlDateFormat)
                    ));
                    break;
                case 'email':
                    
                    $errorMessage = $this->container->get('translator')->trans('IMPORT_INCORRECT_EMAIL_IN_ROWS');
                    break;
                case 'singleline':
                    $errorMessage = $this->container->get('translator')->trans('IMPORT_SINGLELINE_LENGTH_EXCEEDS_IN_ROWS');
                    break;
                case 'multiline':
                    $errorMessage = $this->container->get('translator')->trans('IMPORT_MULTILINE_LENGTH_EXCEEDS_IN_ROWS');
                    break;
                case 'url':
                    $errorMessage = $this->container->get('translator')->trans('IMPORT_INCORRECT_URL_IN_ROWS');
                    break;
                case 'country':
                    $errorMessage = $this->container->get('translator')->trans('IMPORT_INCORRECT_COUNTRY_IN_ROWS');
                    break;
            }
            foreach ($result as $errResult) {
                if (!is_null($errResult['column_name']) && !is_null($errResult['err_rows'])) {
                    $this->errorRows[] = array(
                        'errorMessage' => $errorMessage,
                        'headerColumn' => $errResult['header_name'],
                        'fieldname' => $errResult['column_name'],
                        'rows' => $errResult['err_rows']
                    );
                }
            }
        }
    }
    
    /**
     * Executes validation query and error records for joindate and email duplication
     *
     */
    private function setJoindateAndEmalDuplicate()
    {
        $phpDateFormat = FgSettings::getPhpDateFormat();
        if ($this->emailDupQuery != '') {
            $result = $this->conn->fetchAll($this->emailDupQuery);
            foreach ($result as $errResult) {
                if (!is_null($errResult['dup_value']) && !is_null($errResult['err_rows'])) {
                    $this->errorRows[] = array(
                        'errorMessage' => $this->container->get('translator')->trans('IMPORT_DUPLICATE_EMAIL_IN_ROWS'),
                        'headerColumn' => $errResult['header_name'],
                        'fieldname' => $this->primaryEmailTitle,
                        'rows' => $errResult['err_rows']
                    );
                }
            }
        }
        if ($this->joinDateValidateQuery != '') {
            $result = $this->conn->fetchAll($this->joinDateValidateQuery);
            foreach ($result as $errResult) {
                if (!is_null($errResult['err_rows'])) {
                    $this->errorRows[] = array(
                        'errorMessage' => $this->container->get('translator')->trans('IMPORT_JOINING_DATE_ERROR', array(
                            '%date%' => date($phpDateFormat)
                        )),
                        'fieldname' => 'Joining date',
                        'headerColumn' => $errResult['header_name'],
                        'rows' => $errResult['err_rows']
                    );
                }
            }
        }
        if ($this->firstJoinDateValidateQuery != '') {
            $result = $this->conn->fetchAll($this->firstJoinDateValidateQuery);
            foreach ($result as $errResult) {
                if (!is_null($errResult['err_rows'])) {
                    $this->errorRows[] = array(
                        'errorMessage' => $this->container->get('translator')->trans('IMPORT_FIRST_JOINING_DATE_INVALID', array(
                            '%date%' => date($phpDateFormat)
                        )),
                        'fieldname' => 'First joining date',
                        'headerColumn' => $errResult['header_name'],
                        'rows' => $errResult['err_rows']
                    );
                }
            }
        }
        if ($this->joinDateMandatoryQuery != '') {
            $result = $this->conn->fetchAll($this->joinDateMandatoryQuery);
            foreach ($result as $errResult) {
                if (!is_null($errResult['err_rows'])) {
                    $this->errorRows[] = array(
                        'errorMessage' => $this->container->get('translator')->trans('IMPORT_JOINING_DATE_MANDATORY', array(
                            '%date%' => date($phpDateFormat)
                        )),
                        'fieldname' => $this->container->get('translator')->trans('CM_JOINING_DATE'),
                        'headerColumn' => $errResult['header_name'],
                        'rows' => $errResult['err_rows']
                    );
                }
            }
        }
        if ($this->leavingDateValidateQuery != '') {
            $result = $this->conn->fetchAll($this->leavingDateValidateQuery);
            foreach ($result as $errResult) {
                if (!is_null($errResult['err_rows'])) {
                    $this->errorRows[] = array(
                        'errorMessage' => $this->container->get('translator')->trans('IMPORT_LEAVING_DATE_ERROR', array(
                            '%date%' => date($phpDateFormat)
                        )),
                        'fieldname' => $this->container->get('translator')->trans('CM_LEAVING_DATE'),
                        'headerColumn' => $errResult['header_name'],
                        'rows' => $errResult['err_rows']
                    );
                }
            }
        }
        if ($this->memberValidateQuery != '') {
            $result = $this->conn->fetchAll($this->memberValidateQuery);
            foreach ($result as $errResult) {
                if (!is_null($errResult['err_rows'])) {
                    $this->errorRows[] = array(
                        'errorMessage' => $this->container->get('translator')->trans('IMPORT_INVALID_MEMBERSHIP', array(
                            '%date%' => date($phpDateFormat)
                        )),
                        'fieldname' => $this->container->get('translator')->trans('MEMBER_CATEGORY'),
                        'headerColumn' => $errResult['header_name'],
                        'rows' => $errResult['err_rows']
                    );
                }
            }
        }
        if ($this->fedmemberValidateQuery != '') {
            $result = $this->conn->fetchAll($this->fedmemberValidateQuery);
            //print_r($result);
            foreach ($result as $errResult) {
                if (!is_null($errResult['err_rows'])) {
                    $this->errorRows[] = array(
                        'errorMessage' => $this->container->get('translator')->trans('IMPORT_INVALID_FEDMEMBERSHIP', array(
                            '%date%' => date($phpDateFormat)
                        )),
                        'fieldname' => $this->container->get('translator')->trans('FEDMEMBER_CATEGORY'),
                        'headerColumn' => $errResult['header_name'],
                        'rows' => $errResult['err_rows']
                    );
                }
            }
        }
        if ($this->contactIdValidateQuery != '') {
            // echo $this->contactIdValidateQuery;
            $result = $this->conn->fetchAll($this->contactIdValidateQuery);
            foreach ($result as $errResult) {
                if (!is_null($errResult['err_rows'])) {
                    $this->errorRows[] = array(
                        'errorMessage' => $this->container->get('translator')->trans('IMPORT_INVALID_CONTACTID', array(
                            '%date%' => date($phpDateFormat)
                        )),
                        'fieldname' => $this->container->get('translator')->trans('CONTACTID'),
                        'headerColumn' => $errResult['header_name'],
                        'rows' => $errResult['err_rows']
                    );
                }
            }
        }
        if ($this->joinDateInvalidQuery != '') {
            $result = $this->conn->fetchAll($this->joinDateInvalidQuery);
            foreach ($result as $errResult) {
                if (!is_null($errResult['err_rows'])) {
                    $this->errorRows[] = array(
                        'errorMessage' => $this->container->get('translator')->trans('IMPORT_JOINING_DATE_MANDATORY', array(
                            '%date%' => date($phpDateFormat)
                        )),
                        'fieldname' => $this->container->get('translator')->trans('CM_JOINING_DATE'),
                        'headerColumn' => $errResult['header_name'],
                        'rows' => $errResult['err_rows']
                    );
                }
            }
        }
        if ($this->leavingDateInvalidQuery != '') {
            $result = $this->conn->fetchAll($this->leavingDateInvalidQuery);
            foreach ($result as $errResult) {
                if (!is_null($errResult['err_rows'])) {
                    $this->errorRows[] = array(
                        'errorMessage' => $this->container->get('translator')->trans('IMPORT_LEAVING_DATE_ERROR', array(
                            '%date%' => date($phpDateFormat)
                        )),
                        'fieldname' => $this->container->get('translator')->trans('CM_LEAVING_DATE'),
                        'headerColumn' => $errResult['header_name'],
                        'rows' => $errResult['err_rows']
                    );
                }
            }
        }
        if ($this->clubIdValidateQuery != '') {
            $result = $this->conn->fetchAll($this->clubIdValidateQuery);
            foreach ($result as $errResult) {
                if (!is_null($errResult['err_rows'])) {
                    $this->errorRows[] = array(
                        'errorMessage' => $this->container->get('translator')->trans('IMPORT_CLUB_ID_INVALID', array(
                        )),
                        'fieldname' => 'Club Id',
                        'headerColumn' => $errResult['header_name'],
                        'rows' => $errResult['err_rows']
                    );
                }
            }
        }
    }
    
    /**
     * Function to get static fields
     *
     * @return type
     */
    private function getStaticFields()
    {
        $terminologyService                       = $this->container->get('fairgate_terminology_service');
        $staticFields['contact_id']               = array(
            'title' => $this->container->get('translator')->trans('CONTACT_ID'),
            'type' => 'integer'
        );
        $staticFields['member_category']          = array(
            'title' => $this->container->get('translator')->trans('MEMBER_CATEGORY'),
            'type' => 'integer'
        );
        $staticFields['fed_membership']           = array(
            'title' => $this->container->get('translator')->trans('Fed membership'),
            'type' => 'integer'
        );
        $staticFields['intranet_access']          = array(
            'title' => ucfirst($this->container->get('translator')->trans('INTRANET_ACCESS')),
            'type' => 'integer'
        );
        $staticFields['is_newsletter_subscriber'] = array(
            'title' => $this->container->get('translator')->trans('NEWSLETTER_SUBSCRIPTION'),
            'type' => 'integer'
        );
        $staticFields['dispatch_type_invoice']    = array(
            'title' => $this->container->get('translator')->trans('INVOICE_DISPATCH_TYPE'),
            'type' => 'number'
        );
        $staticFields['dispatch_type_dun']        = array(
            'title' => $this->container->get('translator')->trans('DUNS_DISPATCH_TYPE'),
            'type' => 'number'
        );
        $staticFields['joining_date']             = array(
            'title' => $this->container->get('translator')->trans('CM_JOINING_DATE'),
            'type' => 'date'
        );
        $staticFields['first_joining_date']       = array(
            'title' => $this->container->get('translator')->trans('CM_FIRST_JOINING_DATE'),
            'type' => 'date'
        );
        $staticFields['leaving_date']             = array(
            'title' => $this->container->get('translator')->trans('CM_LEAVING_DATE'),
            'type' => 'date'
        );
        $staticFields['club_ids']             = array(
            'title' => $this->container->get('translator')->trans('CM_CLUB_IDS_TO_SHARE'),
            'type' => 'string'
        );
        return $staticFields;
    }
}
