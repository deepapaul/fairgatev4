<?php

namespace Common\UtilityBundle\Util;


/**
 * FgLogHandler act as a layer which handle all the log entries.
 * Entire solution log entries will be passing through this layer.
 * Used for handling insert to different table as a generic solution.
 *
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
class FgLogHandler {

    /**
     * Club Id
     *
     * @var int
     */
    public $clubId;

    /**
     * Club Contact Id
     *
     * @var int
     */
    private $contactId;

    /**
     * Federation Contact Id
     *
     * @var int
     */
    private $fedContactId;

    /**
     * Log Filed details array
     *
     * @var array
     */
    private $logFieldArr = array();

    /**
     * Log Value details array
     *
     * @var array
     */
    private $logValueArr = array();

    /**
     * Log Parameter field details array
     *
     * @var array
     */
    private $logParamFieldArr = array();

    /**
     * Constructor for initial setting
     *
     * @param type $container
     */
    public function __construct($container) {
        $this->container = $container;
        $this->club = $this->container->get('club');
        $this->clubId = $this->club->get('id');        
        $this->contactId = $this->club->get('contactId');
        $this->fedContactId = $this->club->get('fedContactId');
        $this->conn = $this->container->get('database_connection');
        $this->adminConn = $this->container->get("fg.admin.connection")->getAdminConnection();
        $this->adminEntityManager = $this->container->get("fg.admin.connection")->getAdminEntityManager();
        $this->em = $this->container->get('doctrine')->getManager();  
    }

    /**
     * For generate a query and execute according to the settings and give result
     *
     * @param String $logType      Type of the log entry
     * @param String $logTablename Name of the table to be updated
     * @param Array  $logValues    Data for inserting log entry
     *
     */
    public function processLogEntryAction($logType, $logTablename, $logValues) {
        //Prepare log entry fields based on the logtype
        $this->prepareLogFieldArray($logType);
        $this->logParamFieldArr = $this->logFieldArr;
        //Prepare log entry value array based on the fields
        foreach ($logValues as $key => $valueArr) {
            $this->logValueArr = array();
            $this->prepareLogValueArray($logType, $valueArr);
            //prepare log entry for inserting            
            $this->executeLogEntryAction($logTablename);            
        }
    }

    /**
     * Log field array preparation function
     *
     * @param String $logType Type of the log array
     *
     */
    private function prepareLogFieldArray($logType) {
        $this->logFieldArr = array(0 => 'date', 1 => 'kind', 2 => 'field', 3 => 'value_before', 4 => 'value_after', 5 => 'changed_by', 6 => 'club_id');

        switch ($logType) {
            case 'membership':
                    $this->logFieldArr[7] = 'membership_id';
                break;
            case 'fedMembershipAssignment':
            case 'club_membership_assignment':
                    $this->logFieldArr[7] = 'membership_id';
                    $this->logFieldArr[8] = 'contact_id';
                break;
            case 'contactlogin':
                    $this->logFieldArr[7] = 'contact_id';
                break;
            case 'membership_history':
                    $this->logFieldArr = array(0 => 'contact_id', 1 => 'membership_club_id', 2 => 'membership_id', 3 => 'membership_type', 4 => 'joining_date', 5 => 'leaving_date', 6 => 'changed_by');
                break;
            case 'fed_membership_confirmation':
                    $this->logFieldArr = array(0 => 'club_id', 1 => 'contact_id', 2 => 'federation_club_id', 3 => 'modified_date', 4 => 'modified_by', 5 => 'decided_date', 6 => 'decided_by', 7 => 'fed_membership_value_before', 8 => 'fed_membership_value_after', 9 => 'status');
                break;
            case 'contactOverview_document':
                    $contactOverviewDocumentArr = array(7 => 'document_type', 8 => 'documents_id', 9 => 'value_before_id', 10 => 'value_after_id');
                    $this->logFieldArr = array_merge($this->logFieldArr, $contactOverviewDocumentArr);
                break;
            case 'contactOverview_notes':
                    $this->logFieldArr = array(0 => 'note_club_id', 1 => 'note_contact_id', 2 => 'assigned_club_id', 3 => 'date', 4 => 'type', 5 => 'value_before', 6 => 'value_after', 7 => 'changed_by');
                break;
            case 'contact_field':
                $this->logFieldArr[7] = 'contact_id';
                $this->logFieldArr[8] = 'attribute_id';
                break;
            case 'contact_field_confirm':
                $this->logFieldArr[7] = 'contact_id';
                $this->logFieldArr[8] = 'attribute_id';
                $this->logFieldArr[9] = 'is_confirmed';
                $this->logFieldArr[10] = 'confirmed_by';
                $this->logFieldArr[11] = 'confirmed_date';
                break;
            case 'fedRoleAssignment':case 'subFedRoleAssignment':case 'roleAssignment':
                $this->logFieldArr = array(0 => 'contact_id', 1 => 'category_club_id', 2 => 'role_type', 3 => 'category_id', 4 => 'role_id', 5 => 'function_id', 6 => 'date', 7 => 'category_title', 8 => 'value_before', 9 => 'value_after', 10 => 'changed_by');
                break;
            case 'assignment_club':
                //$this->logFieldArr[7] = 'rolelog_id';
                break;
            case 'assignment_role':
                $this->logFieldArr[7] = 'role_id';
                $this->logFieldArr[8] = 'contact_id';
                break;
            case 'assignment_function':
                $this->logFieldArr[7] = 'role_id';
                $this->logFieldArr[8] = 'function_id';
                $this->logFieldArr[9] = 'contact_id';
                break;
            case 'userRights':
                $this->logFieldArr[7] = 'contact_id';
                break;
            case 'sponsor':
                $this->logFieldArr[7] = 'contact_id';
                break;
            case 'contactSystemLogs':
                $this->logFieldArr[7] = 'contact_id';
                break;
            default :
                break;
        }
    }

    /**
     * Log value array preparation function
     *
     * @param String $logType Type of the log array
     *
     * @return array logvalue array
     */
    private function prepareLogValueArray($logType, $logValues) {
        $nowdate = strtotime(date('Y-m-d H:i:s'));
        $dateToday = date('Y-m-d H:i:s', $nowdate);
        foreach ($this->logFieldArr as $key => $value) {
            if (($value == 'date') && (isset($logValues['date'])) && ($logType == 'club_membership_assignment' || $logType == 'fedMembershipAssignment')) {
                $this->logValueArr[":$value"] = $logValues['date'];
            } else if ($value == 'date') {
                $this->logValueArr[":$value"] = $dateToday;
            } else if ($value == 'kind') {
                $this->logValueArr[":$value"] = isset($logValues['kind']) ? $logValues['kind'] : 'data';
            } else if ($value == 'value_before') {
                if ($logType == 'membership' && (isset($logValues['value_before']) && $logValues['value_before'] !== '')) {
                    $this->logParamFieldArr[$key] = (isset($logValues['value_before']) && $logValues['value_before'] !== '') ? $logValues['value_before'] : '';
                } else {
                    if ($logType == 'membership'&&$logValues['value_before'] =='')
                        $this->logParamFieldArr[$key] ='value_before';
                    $this->logValueArr[":$value"] = isset($logValues['value_before']) ? $logValues['value_before'] : '';
                }
            } else if (($logType == 'contact_field' || $logType == 'contact_field_confirm') && $value == 'contact_id') {
                $this->logParamFieldArr[$key] = (isset($logValues['contact_id']) && $logValues['contact_id'] !== '') ? $logValues['contact_id'] : '@contactId';
            } else if (($value == 'changed_by' || $value == 'contact_id') && (($logType != 'contact_field' && $logType != 'contact_field_confirm'))) {
                if (($logType == 'fedRoleAssignment' || $logType == 'subFedRoleAssignment' || $logType == 'fedMembershipAssignment') && isset($logValues[$value])) {
                    $diffLevelContactId = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getFederationContactId($logValues[$value]);
                    $fedContactId = $diffLevelContactId['fedContactId'];
                    $subFedContactId = $diffLevelContactId['subFedContactId'];
                    $this->logValueArr[":$value"] = (($logType == 'fedRoleAssignment') || ($logType == 'fedMembershipAssignment')) ? $fedContactId : $subFedContactId;
                } else {
                    $this->logValueArr[":$value"] = ($logType == 'fedmembership') ? $this->fedContactId: (isset($logValues[$value]) ? $logValues[$value] : $this->contactId);
                }
            } else if ($value == 'club_id' && ($logType != 'contact_field' && $logType != 'contact_field_confirm' && $logType != 'assignment_role' && $logType != 'contactSystemLogs')) {
                $federationClubId = $this->getFederationClubId(); 
                $this->logValueArr[":$value"] = ($logType == 'fedmembership' || $logType == 'fedMembershipAssignment') ? $federationClubId : $this->clubId;
            } else {
                $this->logValueArr[":$value"] = $logValues[$value];
            }
        }
    }
    
    /**
     * Method to get federation clubId of current club
     * 
     * @return int federation clubId
     */
    private function getFederationClubId() {
        $clubObj = $this->adminEntityManager->getRepository('AdminUtilityBundle:FgClub')->find($this->clubId);
        $clubType = $clubObj->getClubType();
        if($clubType == 'federation' || $clubType == 'standard_club') {
            $federationId = $this->clubId;
        } else {
            $federationId = $clubObj->getFederationId();
        }
        
        return $federationId;
    }

    /**
     * For prepare and execute query according to the settings and give result
     *
     * @param String $tablename Name of the table to be updated
     *
     */
    private function executeLogEntryAction($logTablename) {
        $logQuery = "INSERT INTO `$logTablename` (`" . implode('`,`', $this->logFieldArr) . "`) VALUES (:" . implode(',:', $this->logParamFieldArr) . ");";
        $logEntryQuery1 = str_replace(',:(SELECT', ',(SELECT', $logQuery);
        $logEntryQuery2 = str_replace(',:@contactId', ',@contactId', $logEntryQuery1);
        $logEntryQuery3 = str_replace(',:@fedcontactId', ',@fedcontactId', $logEntryQuery2);
        $logEntryQuery4 = str_replace(',:@subfedcontactId', ',@subfedcontactId', $logEntryQuery3);
        
        if(in_array($logTablename, array('fg_club_log'))){
            $stmt = $this->adminConn->prepare($logEntryQuery4);
        } else {
            $stmt = $this->conn->prepare($logEntryQuery4);
        }
        
        $stmt->execute($this->logValueArr);
    }
}