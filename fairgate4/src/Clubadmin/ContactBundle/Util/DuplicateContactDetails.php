<?php

namespace Clubadmin\ContactBundle\Util;

/**
 * For handle the contact details view authntication.
 */
class DuplicateContactDetails {

    /**
     * Container
     *
     * @var Object
     */
    private $container;

    /**
     * Connection
     *
     * @var Object
     */
    private $conn;

    /**
     * Current contact id
     *
     * @var int
     */
    private $contactId;

    /**
     * Old fed contact id
     *
     * @var int
     */
    private $oldFedContactId;

    /**
     * Old sub fed contact id
     *
     * @var int
     */
    private $oldSubfedContactId;

    /**
     * New fed contact id
     *
     * @var int
     */
    private $newFedContactId;

    /**
     * New sub fed contact id
     *
     * @var int
     */
    private $newSubfedContactId;

    /**
     * Club id
     *
     * @var int
     */
    private $clubId;

    /**
     * Entity manager
     *
     * @var Object
     */
    private $em;

    /**
     * New sf_guard_user id
     *
     * @var int
     */
    private $newSfGuardUserId;

    /**
     * Old sf_guard_user id
     *
     * @var int
     */
    private $oldSfGuardUserId;

    /**
     * Constructor for initial setting.
     *
     * @param int $contactId
     * @param object $container
     * @param string $module
     */
    public function __construct($container, $conn, $contactId, $oldFedContactId, $oldSubfedContactId, $newFedContactId, $newSubfedContactId, $clubId)
    {
        $this->contactId = $contactId;
        $this->conn = $conn;
        $this->container = $container;
        $this->oldFedContactId = $oldFedContactId;
        $this->oldSubfedContactId = $oldSubfedContactId;
        $this->newFedContactId = $newFedContactId;
        $this->newSubfedContactId = $newSubfedContactId;
        $this->clubId = $clubId;
        $this->em = $this->container->get('doctrine')->getManager();
    }

    /**
     * Function to duplicate fderation role assignments with new fed contact id
     *
     * @param String $type To decide whether fed roles or subfed roles
     */
    public function generateDuplicateAssignments($type)
    {
        $queryContactNew = ($type == 'fed') ? $this->newFedContactId : $this->newSubfedContactId;
        $queryContactOld = ($type == 'fed') ? $this->oldFedContactId : $this->oldSubfedContactId;

        $fgRmRoleContactDuplicateQuery = "INSERT INTO fg_rm_role_contact (`contact_id`,`fg_rm_crf_id`,`assined_club_id`,`contact_club_id`,`update_type`,`update_count`,`update_time`,`is_removed`) "
                . "SELECT " . $queryContactNew . ",frrc.fg_rm_crf_id," . $this->clubId . ",frrc.contact_club_id,frrc.update_type,frrc.update_count,frrc.update_time,frrc.is_removed "
                . "FROM fg_rm_role_contact frrc "
                . "WHERE frrc.contact_id =" . $queryContactOld;

        $this->conn->executeQuery($fgRmRoleContactDuplicateQuery);
    }

    /**
     * Function to duplicate fderation role assignments log entries with new fed contact id
     *
     * @param String $type To decide whether fed roles or subfed roles
     */
    public function generateDuplicateAssignmentLogs($type)
    {
        $queryContactNew = ($type == 'fed') ? $this->newFedContactId : $this->newSubfedContactId;
        $queryContactOld = ($type == 'fed') ? $this->oldFedContactId : $this->oldSubfedContactId;
        $roleAssignmentDuplicateQuery = "INSERT INTO fg_cm_log_assignment (`contact_id`,`category_club_id`,`role_type`,`category_id`,`role_id`,`function_id`,`date`,`category_title`,`value_before`,`value_after`,`changed_by`,`historical_id`,`is_historical`) "
                . "SELECT " . $queryContactNew . ",fcla.category_club_id,fcla.role_type,fcla.category_id,fcla.role_id,fcla.function_id,fcla.date,fcla.category_title,fcla.value_before,fcla.value_after,fcla.changed_by,fcla.historical_id,fcla.is_historical "
                . "FROM fg_cm_log_assignment fcla "
                . "WHERE fcla.contact_id =" . $queryContactOld;

        $this->conn->executeQuery($roleAssignmentDuplicateQuery);

//        $roleLogDuplicateQuery = "INSERT INTO fg_rm_role_log (`club_id`,`role_id`,`contact_id`,`date`,`kind`,`field`,`value_before`,`value_after`,`changed_by`,`historical_id`,`is_historical`,`contactlog_id`,`booking_id`,`import_table`,`import_contact`) "
//                . "SELECT frrl.club_id,frrl.role_id,".$queryContactNew.",frrl.date,frrl.kind,frrl.field,frrl.value_before,frrl.value_after,frrl.changed_by,frrl.historical_id,frrl.is_historical,frrl.contactlog_id,frrl.booking_id,frrl.import_table,frrl.import_contact "
//                . "FROM fg_rm_role_log frrl "
//                . "WHERE frrl.contact_id =".$queryContactOld;
//
//        $this->conn->executeQuery($roleLogDuplicateQuery);

//        $functionLogDuplicateQuery = "INSERT INTO fg_rm_function_log (`club_id`,`role_id`,`function_id`,`contact_id`,`date`,`kind`,`field`,`value_before`,`value_after`,`changed_by`) "
//                . "SELECT frfl.club_id,frfl.role_id,frfl.function_id,".$queryContactNew.",frfl.date,frfl.kind,frfl.field,frfl.value_before,frfl.value_after,frfl.changed_by "
//                . "FROM fg_rm_function_log frfl "
//                . "WHERE frfl.contact_id =".$queryContactOld;

//        $this->conn->executeQuery($functionLogDuplicateQuery);
    }

    /**
     * Function to duplicate sf_guard_user_group and sf_guard_user_team entries
     *
     * @param String $type To decide whether fed roles or subfed roles
     */
    public function generateDuplicateSfGuardUserGroup($type)
    {
        $queryContactNew = ($type == 'fed') ? $this->newFedContactId : $this->newSubfedContactId;
        $queryContactOld = ($type == 'fed') ? $this->oldFedContactId : $this->oldSubfedContactId;
        $sfGuardUserDetailsNew = $this->em->getRepository('CommonUtilityBundle:SfGuardUser')->getSfGuardUserDetails($queryContactNew);
        $this->newSfGuardUserId = $sfGuardUserDetailsNew['sfGuardUserId'];
        $sfGuardUserDetailsOld = $this->em->getRepository('CommonUtilityBundle:SfGuardUser')->getSfGuardUserDetails($queryContactOld);
        $this->oldSfGuardUserId = $sfGuardUserDetailsOld['sfGuardUserId'];

        if ($this->newSfGuardUserId != '' && $this->oldSfGuardUserId != '') {
            $sfGuardUserDuplicateQuery = "INSERT INTO sf_guard_user_group (`user_id`,`group_id`,`created_at`,`updated_at`) "
                    . "SELECT " . $this->newSfGuardUserId . ",sgug.group_id,sgug.created_at,sgug.updated_at "
                    . "FROM sf_guard_user_group sgug "
                    . "WHERE sgug.user_id=" . $this->oldSfGuardUserId;

            $this->conn->executeQuery($sfGuardUserDuplicateQuery);

            $sfGuardUserTeamDuplicateQuery = "INSERT INTO sf_guard_user_team (`user_id`,`group_id`,`role_id`,`created_at`) "
                    . "SELECT " . $this->newSfGuardUserId . ",sgut.group_id,sgut.role_id,sgut.created_at "
                    . "FROM sf_guard_user_team sgut "
                    . "WHERE sgut.user_id=" . $this->oldSfGuardUserId;

            $this->conn->executeQuery($sfGuardUserTeamDuplicateQuery);
        }

        $userRightsLogDuplicateQuery = "INSERT INTO fg_cm_change_log (`contact_id`,`club_id`,`date`,`kind`,`field`,`value_before`,`value_after`,`changed_by`,`is_confirmed`,`confirmed_by`,`historical_id`,`is_historical`,`attribute_id`,`newsletter_id`,`confirmed_date`) "
                . "SELECT " . $queryContactNew . ",fccl.club_id,fccl.date,fccl.kind,fccl.field,fccl.value_before,fccl.value_after,fccl.changed_by,fccl.is_confirmed,fccl.confirmed_by,fccl.historical_id,fccl.is_historical,fccl.attribute_id,fccl.newsletter_id,fccl.confirmed_date "
                . "FROM fg_cm_change_log fccl "
                . "WHERE fccl.contact_id=" . $queryContactOld . " AND fccl.kind='user rights'";

        $this->conn->executeQuery($userRightsLogDuplicateQuery);
    }

    /**
     * Function to duplicate fg_cm_change log entries
     *
     * @param String $type To decide whether fed roles or subfed roles
     */
    public function generateDuplicateContactLogEntries($type)
    {
        $queryContactNew = ($type == 'fed') ? $this->newFedContactId : $this->newSubfedContactId;
        $queryContactOld = ($type == 'fed') ? $this->oldFedContactId : $this->oldSubfedContactId;
        $logChangeDuplicateQuery = "INSERT INTO fg_cm_change_log (`contact_id`,`club_id`,`date`,`kind`,`field`,`value_before`,`value_after`,`changed_by`,`is_confirmed`,`confirmed_by`,`historical_id`,`is_historical`,`attribute_id`,`newsletter_id`,`confirmed_date`) "
                . "SELECT " . $queryContactNew . ",fccl.club_id,fccl.date,fccl.kind,fccl.field,fccl.value_before,fccl.value_after,fccl.changed_by,fccl.is_confirmed,fccl.confirmed_by,fccl.historical_id,fccl.is_historical,fccl.attribute_id,fccl.newsletter_id,fccl.confirmed_date "
                . "FROM fg_cm_change_log fccl "
                . "WHERE fccl.contact_id=" . $queryContactOld . " AND fccl.kind IN ('contact status','contact type','system','data')";

        $this->conn->executeQuery($logChangeDuplicateQuery);
    }

    /**
     * Function to duplicate fg_cm_membership log entries and membership history entries
     *
     * @param type $type
     */
    public function generateDuplicateMembershipLogHistoryEntries($type)
    {
        $queryContactNew = ($type == 'fed') ? $this->newFedContactId : $this->newSubfedContactId;
        $queryContactOld = ($type == 'fed') ? $this->oldFedContactId : $this->oldSubfedContactId;

        $membershipLogDuplicateQuery ="INSERT INTO fg_cm_membership_log (`club_id`,`contact_id`,`membership_id`,`date`,`kind`,`field`,`value_before`,`value_after`,`changed_by`) "
                . "SELECT fcml.club_id,".$queryContactNew.",fcml.membership_id,fcml.date,fcml.kind,fcml.field,fcml.value_before,fcml.value_after,fcml.changed_by "
                . "FROM fg_cm_membership_log fcml "
                . "WHERE fcml.contact_id=".$queryContactOld." AND fcml.club_id=".$this->clubId;

        $this->conn->executeQuery($membershipLogDuplicateQuery);

        $membershipHistoryDuplicateQuery = "INSERT INTO fg_cm_membership_history (`contact_id`,`membership_club_id`,`membership_id`,`membership_type`,`joining_date`,`leaving_date`,`changed_by`) "
                . "SELECT ".$queryContactNew.",fcmh.membership_club_id,fcmh.membership_id,fcmh.membership_type,fcmh.joining_date,fcmh.leaving_date,fcmh.changed_by "
                . "FROM fg_cm_membership_history fcmh "
                . "WHERE fcmh.contact_id=".$queryContactOld;

        $this->conn->executeQuery($membershipHistoryDuplicateQuery);
    }
    /**
     * Function to duplicate fg_cm_log_connection entries
     */
    public function generateDuplicateConectionLogEntries()
    {
        $queryContactNew = $this->newFedContactId;
        $queryContactOld = $this->oldFedContactId;
        $logChangeDuplicateQuery = "INSERT INTO fg_cm_log_connection (contact_id,linked_contact_id,assigned_club_id,`date`,connection_type,relation,value_before,value_after,changed_by,`type`) "
                . "SELECT " . $queryContactNew . ", LC.linked_contact_id, LC.assigned_club_id, LC.`date`, LC.connection_type, LC.relation, LC.value_before, LC.value_after, LC.changed_by, LC.`type` "
                . "FROM fg_cm_log_connection LC "
                . "WHERE LC.contact_id=" . $queryContactOld . " AND LC.`type`='global'";

        $this->conn->executeQuery($logChangeDuplicateQuery);
    }
}
