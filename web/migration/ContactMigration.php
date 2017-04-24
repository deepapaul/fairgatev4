<?php

/**
 * Alter db tables and migrate contact to FedV2
 *
 * @author  pitsolutions.ch <pit@solutions.com>
 *
 * @version Release: <v4>
 */
class ContactMigration
{
    /**
     * $em.
     *
     * @var object entitymanager object
     */
    private $conn;
    private $log;


    /**
     * Constructor for initial setting.
     *
     * @param type $conn   container
     */
    public function __construct($conn){
        $this->conn = $conn;
        $this->log=fopen('migration_contact_log_'.date('dHis').'.txt','w');
    }
    /**
     * Function to init contact migration
     * @throws \Clubadmin\ContactBundle\Util\Exception
     */
    public function InitMigration($step){
        try {
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->beginTransaction();
            switch($step){
                CASE 1:
                    $this->copyContactTable();
                    $this->writeLog("CONTACT TABLE COPIED\n");
                    break;
                CASE 2:
                    $this->addColumnToContactTable();
                    $this->addColumnToContactTable('new');
                    $this->writeLog("ADDED COLUMN CONTACT TABLE\n");
                    break;
                CASE 3:
                    $this->addMigrationValues();
                    $this->addMigrationValues('new');
                    $this->writeLog("MIGRATION VALUE ADDED IN CONTACT TABLE\n");
                    break;
                CASE 4:
                    $this->addContractEntries();
                    $this->writeLog("ADDED ENTRIES FOR FED AND SUBFED\n");
                    break;
                CASE 5:
                    $this->updateFedContactIds();
                    break;
                CASE 6:
                    $this->updateMembership();
                    $this->writeLog("UPDATED MEMBERSHIP\n");
//                    $this->updateJoinLeavingDates();
//                    $this->writeLog("JOINING/LEAVING DATES UPDATED\n");
                    break;
                CASE 7:
                    $this->migrateMasterSystem();
                    $this->writeLog("MASTER SYSTEM MIGRATED\n");
                    break;
                CASE 8:
                    $this->AlterClubTables();
                    $this->writeLog("CLUB TABLES UPDATED\n");
                    break;
                CASE 9:
                    $this->changeLogContactId();
                    $this->writeLog("CHANGE LOG CONTACT_ID UPDATED\n");
                    break;
                CASE 10:
                    $this->migrateChangeLogAttributes();
                    $this->writeLog("CHANGE LOG CONTACT_ID UPDATED FOR ATTRIBUTES\n");
                    break;
                CASE 11:
                    $this->updateSfGuardUser();
                    $this->writeLog("UPDATED SF_GUARD_USER\n");
                    break;
                CASE 12:
                    $this->dropColumnContactTable();
                    $this->writeLog("DROP COLUMNS FROM CONTACT TABLE\n");
                    break;
                CASE 13:
                    $this->addConnectionToContactTable();
                    $this->writeLog("ADDED CONNECTION TO CONTACT TABLE\n");
                    break;
                CASE 14:
                    $this->migrateAttributes();
                    $this->writeLog("ATTRIBUTE TABLE MIGRATED\n");
                    break;
            }
            $this->conn->commit();
        } catch (Exception $ex) {
                //$this->conn->rollback();
                echo "Failed: " . $ex->getMessage();
                throw $ex;
            }

    }

    /**
     * Add migration values like old_contact_id
     */
    private function addMigrationValues($table=''){
        $table = ($table=='') ? 'fg_cm_contact':'fg_cm_contact_new';
        //set old contact id and club Type
        $this->conn->exec("UPDATE $table C INNER JOIN fg_club CL ON CL.id=C.club_id SET C.old_contact_id=C.id,C.main_club_id=C.club_id,C.created_club_id=C.club_id,C.club_type=CL.club_type");
        //update current id as fed_contact_id for standard_club and federation
        $this->conn->exec("UPDATE $table C SET C.fed_contact_id=C.id WHERE C.club_type='standard_club' OR C.club_type='federation' OR C.club_id=1");
        //duplicate entry

    }
    /**
     * Add duplicate entries in fg_cm_contact for club contacts
     */
    private function addContractEntries(){

        $duplicateSelectQuery = "SELECT CL1.id as club_id,C.`main_club_id`, C.`fed_contact_id`, C.`subfed_contact_id`, C.`is_company`, C.`is_deleted`, C.`system_language`, C.`is_permanent_delete`,
            C.`is_draft`, C.`is_fairgate`, C.`created_club_id`, C.`comp_def_contact`, C.`comp_def_contact_fun`, C.`last_updated`, C.`dispatch_type_invoice`, C.`dispatch_type_dun`,
            C.`has_main_contact`, C.`created_at`, C.`joining_date`, C.`leaving_date`, C.`login_count`,C.membership_cat_id,C.fed_membership_assigned_club_id ,C.`last_login`,C.id,CL1.club_type,
            C.is_former_fed_member,C.archived_on,C.resigned_on
        FROM fg_cm_contact_new C
        INNER JOIN fg_club CL ON CL.id=C.club_id
        LEFT JOIN fg_club CL1 ON CL1.id=CL.parent_club_id OR CL.federation_id=CL1.id
        WHERE CL.club_type IN ('sub_federation_club','federation_club','sub_federation') AND CL.id !=1 ";
        $duplicateInsertQuery="INSERT INTO fg_cm_contact (`club_id`,`main_club_id`,`fed_contact_id`,`subfed_contact_id`,`is_company`,`is_deleted`,`system_language`,`is_permanent_delete`,
	`is_draft`,`is_fairgate`,`created_club_id`,`comp_def_contact`,`comp_def_contact_fun`,`last_updated`,`dispatch_type_invoice`,`dispatch_type_dun`,
	`has_main_contact`,`created_at`,`joining_date`,`leaving_date`,`login_count`,`membership_cat_id`,`fed_membership_assigned_club_id`,`last_login`,`old_contact_id`,club_type,
        is_former_fed_member,archived_on,resigned_on) ";
        $this->conn->exec($duplicateInsertQuery.$duplicateSelectQuery);
    }
    /**
     * Update fed_contact_id for federation contact etry and sub_fed_contact_id for subfed
     */
    private function updateFedContactIds(){
        $this->conn->exec("DROP TABLE fg_cm_contact_new;");
        $this->copyContactTable();
        $this->writeLog("CONTACT TABLE COPIED\n");
        $this->conn->exec("ALTER TABLE `fg_cm_contact` ADD INDEX( `old_contact_id`),ADD INDEX( `club_type`);");
        $this->conn->exec("ALTER TABLE `fg_cm_contact_new` ADD INDEX( `old_contact_id`),ADD INDEX( `club_type`);");
        $updateContactIds1 = "UPDATE fg_cm_contact C
            INNER JOIN fg_cm_contact_new C2 ON C2.old_contact_id=C.old_contact_id AND C2.club_type ='federation'
            SET C.fed_contact_id=C2.id
                WHERE C.club_type IN ('sub_federation_club','federation','federation_club','sub_federation')";
        $this->conn->exec($updateContactIds1);
        $this->writeLog("UPDATED CONTACT_ID ENTRIES FOR FED\n");

        $updateContactIds2 = "UPDATE fg_cm_contact C
            INNER JOIN fg_cm_contact_new C2 ON C2.old_contact_id=C.old_contact_id AND C2.club_type ='sub_federation'
            SET C.subfed_contact_id=C2.id
                WHERE C.club_type IN ('sub_federation_club','sub_federation')";
        $this->conn->exec($updateContactIds2);
        $this->writeLog("UPDATED CONTACT_ID ENTRIES FOR  SUBFED\n");
    }
    /**
     * Update membership id in fg_cm_contact
     */
    private function updateMembership(){
        $updateMemberQuery="INSERT INTO fg_cm_contact (id,fed_membership_cat_id,club_membership_cat_id,is_fed_membership_confirmed,fed_membership_assigned_club_id)
        (SELECT C.id, IF (CL.club_type ='federation',FM.id,NULL) as fed_membership_cat_id,
        IF(CL.club_type!='federation',FM.id,NULL) as club_membership_cat_id,'0',IF (CL.club_type ='federation',C.main_club_id,NULL) as fed FROM fg_cm_contact C
        INNER JOIN fg_cm_membership FM ON FM.id = C.membership_cat_id
        INNER JOIN fg_club CL ON CL.id=FM.club_id)
        ON DUPLICATE KEY UPDATE fed_membership_cat_id=VALUES(fed_membership_cat_id),club_membership_cat_id=VALUES(club_membership_cat_id),
        is_fed_membership_confirmed=VALUES(is_fed_membership_confirmed),fed_membership_assigned_club_id=VALUES(fed_membership_assigned_club_id)";
        $this->conn->exec($updateMemberQuery);
    }
    /**
     * Update joining date and leaving date in all levels
     */
    private function updateJoinLeavingDates(){
        $updateDateQuery = "UPDATE fg_cm_contact C
            SET C.joining_date='0000-00-00 00:00:00',C.leaving_date='0000-00-00 00:00:00'
            WHERE C.club_type='sub_federation' OR
                (C.club_type='federation' AND C.fed_membership_cat_id IS NULL) OR
                (C.club_membership_cat_id IS NULL AND (C.club_type='sub_federation_club' OR C.club_type='federation_club'))";
        $this->conn->exec($updateDateQuery);
    }

    /**
     * Update master_system table and update entries
     */
    private function migrateMasterSystem(){
//        $this->conn->exec("ALTER TABLE `master_system` ADD `fed_contact_id` int(11) DEFAULT NULL AFTER contact_id");
        $this->conn->exec("ALTER TABLE `master_system` ADD INDEX(`club_id`);ALTER TABLE `master_system` DROP PRIMARY KEY;ALTER TABLE `master_system` ADD `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;");
        $this->conn->exec("UPDATE master_system m INNER JOIN fg_cm_contact C ON C.old_contact_id=m.contact_id "
        . "SET m.contact_id=C.id WHERE C.club_type ='federation'");
        $this->conn->exec("ALTER TABLE `master_system` DROP FOREIGN KEY master_system_ibfk_2;");
        $this->conn->exec("ALTER TABLE `master_system` DROP `club_id`;");
        $this->conn->exec("ALTER TABLE `master_system` CHANGE `contact_id` `fed_contact_id` INT(11) NOT NULL DEFAULT '0'");
    }
    /**
     * Itrate over clubs,copy system values like 'intranet access' to fg_cm_contact,Change field name in federation and Drop columns
     */
    private function AlterClubTables(){
        $this->conn->exec("CALL V2MasterTableMigrationTemp();");
    }
    /**
     * Update sf_guard_user table with new contact id and club id
     */
    private function updateSfGuardUser(){
        $sfGuardInsertSql = "UPDATE sf_guard_user S INNER JOIN fg_cm_contact C ON C.old_contact_id=S.contact_id AND C.club_id=S.club_id SET S.contact_id=C.id";

        $this->conn->exec($sfGuardInsertSql);
    }
    /**
     * Change log contact ids for login and system fields
     */
    private function changeLogContactId(){
        $this->conn->exec("UPDATE fg_cm_change_log CL INNER JOIN fg_cm_contact C ON C.old_contact_id=CL.contact_id AND CL.club_id=C.club_id SET CL.contact_id=C.id WHERE (kind='password' OR kind='login' OR kind='system')");
    }
//    private function updateFedMemHistory(){
//        $this->conn->exec("UPDATE `fg_cm_membership_history` MH INNER JOIN fg_cm_contact C ON C.old_contact_id=MH.contact_id AND C.club_type ='federation' AND C.`fed_membership_cat_id` IS NOT NULL SET MH.contact_id=C.id");
//    }
    private function addClubAssignment(){
        $this->conn->exec("INSERT fg_club_assignment SELECT fed_contact_id,main_club_id,created_at,NULL,'1' FROM fg_cm_contact WHERE club_type='federation' AND fed_membership_cat_id IS NOT NULL");
    }
    /**
     * change attribute ids for contact data log
     */
    private function migrateChangeLogAttributes(){
        $this->conn->exec("UPDATE fg_cm_change_log L INNER JOIN fg_cm_attribute A ON A.id=L.attribute_id "
        . "INNER JOIN fg_cm_contact C ON C.old_contact_id=L.contact_id "
        . "SET L.contact_id=C.id "
        . "WHERE C.club_id=A.club_id OR (A.club_id=1 AND C.club_type='federation')");
    }
    /**
     * Function to update attribute table visibility and changability
     */
    private function migrateAttributes(){
        $this->conn->exec("UPDATE `fg_cm_attribute`
        SET availability_sub_fed = IF(is_editable_subfed=1,'changable',IF(is_visible_subfed=1,'visible','not_available')),
        availability_club= IF(is_editable_club=1,'changable',IF(is_visible_club=1,'visible','not_available'))
        WHERE club_id != 1");
        $this->conn->exec("ALTER TABLE `fg_cm_attribute`
        DROP `is_visible_subfed`,
        DROP `is_editable_subfed`,
        DROP `is_visible_club`,
        DROP `is_editable_club`;");
    }

    /**
     * Copy fg_cm_contact table
     */
    private function copyContactTable(){
        $this->conn->exec("CREATE TABLE fg_cm_contact_new LIKE fg_cm_contact;");
        $this->conn->exec("INSERT fg_cm_contact_new SELECT * FROM fg_cm_contact;");
    }
    /**
     * Add new column to fg_cm_contact table
     */
    private function addColumnToContactTable($table=''){
        $table = ($table=='') ? 'fg_cm_contact':'fg_cm_contact_new';
        $addFieldQuery ="ALTER TABLE `$table` "
                . "ADD `main_club_id` int(11) DEFAULT NULL AFTER club_id,"
                . "ADD `fed_contact_id` int(11) DEFAULT NULL AFTER club_id,"
                . "ADD `subfed_contact_id` int(11) DEFAULT NULL AFTER club_id,"
                . "ADD `fed_membership_cat_id` int(11) DEFAULT NULL,"
                . "ADD `club_membership_cat_id` int(11) DEFAULT NULL,"
                . "ADD `is_sponsor` tinyint(1) NOT NULL DEFAULT '0',"
                . "ADD `is_stealth_mode` tinyint(1) NOT NULL DEFAULT '0',"
                . "ADD `intranet_access` tinyint(1) NOT NULL DEFAULT '1',"
                . "ADD `is_subscriber` tinyint(1) NOT NULL DEFAULT '0',"
                . "ADD `system_language` varchar(8) COLLATE utf8mb4_unicode_ci DEFAULT 'default',"
                . "ADD `is_household_head` tinyint(1) NOT NULL DEFAULT '0',"
//                . "ADD `is_seperate_invoice` tinyint(1) NOT NULL DEFAULT '0',"
//                . "ADD `is_postal_address` tinyint(1) NOT NULL DEFAULT '0',"
                . "ADD `old_fed_membership_id` int(11) DEFAULT NULL,"
                . "ADD `is_fed_membership_confirmed` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '0 - Confirmed, 1 - Waiting for confirmation',"
                . "ADD `is_club_assignment_confirmed` tinyint(1) DEFAULT NULL,"
                . "ADD `fed_membership_assigned_club_id` int(11) DEFAULT NULL,"
                . "ADD `created_club_id` int(11) NOT NULL,"
                . "ADD `allow_merging` tinyint(1) NOT NULL DEFAULT '1',"
                . "ADD `merge_to_contact_id` int(11) DEFAULT NULL,"
                . "ADD `quickwindow_visibilty` TINYINT(1) NOT NULL DEFAULT '1',"
                . "ADD `old_contact_id` int(11) DEFAULT NULL,"
                . "ADD `club_type` varchar(50) DEFAULT NULL,"
                . "ADD `first_joining_date` DATETIME NOT NULL AFTER `leaving_date`,"
                . "ADD `is_fed_admin` TINYINT(1) NOT NULL DEFAULT '0' AFTER `quickwindow_visibilty`;";

        $this->conn->exec($addFieldQuery);
        $addIndexQuery="ALTER TABLE `$table`
                            ADD KEY `club_membership_cat_id` (`club_membership_cat_id`),
                            ADD KEY `fed_membership_cat_id` (`fed_membership_cat_id`),
                            ADD KEY `old_fed_membership_id` (`old_fed_membership_id`),
                            ADD KEY `fed_membership_assigned_club_id` (`fed_membership_assigned_club_id`),
                            ADD KEY `created_club_id` (`created_club_id`),
                            ADD KEY `fed_contact_id` (`fed_contact_id`),
                            ADD KEY `subfed_contact_id` (`subfed_contact_id`),
                            ADD KEY `main_club_id` (`main_club_id`),
                            ADD KEY `merge_to_contact_id` (`merge_to_contact_id`);";
        $this->conn->exec($addIndexQuery);

    }
    /**
     * Add connection to contact table
     */
    private function addConnectionToContactTable(){
        $addConnQuery="ALTER TABLE `fg_cm_contact`
                ADD CONSTRAINT `fg_cm_contact_ibfk_20` FOREIGN KEY (`main_club_id`) REFERENCES `fg_club` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
                ADD CONSTRAINT `fg_cm_contact_ibfk_21` FOREIGN KEY (`merge_to_contact_id`) REFERENCES `fg_cm_contact` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
                ADD CONSTRAINT `fg_cm_contact_ibfk_23` FOREIGN KEY (`club_membership_cat_id`) REFERENCES `fg_cm_membership` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION,
                ADD CONSTRAINT `fg_cm_contact_ibfk_24` FOREIGN KEY (`fed_membership_cat_id`) REFERENCES `fg_cm_membership` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION,
                ADD CONSTRAINT `fg_cm_contact_ibfk_25` FOREIGN KEY (`old_fed_membership_id`) REFERENCES `fg_cm_membership` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION,
                ADD CONSTRAINT `fg_cm_contact_ibfk_26` FOREIGN KEY (`fed_membership_assigned_club_id`) REFERENCES `fg_club` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION,
                ADD CONSTRAINT `fg_cm_contact_ibfk_27` FOREIGN KEY (`created_club_id`) REFERENCES `fg_club` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
                ADD CONSTRAINT `fg_cm_contact_ibfk_28` FOREIGN KEY (`fed_contact_id`) REFERENCES `fg_cm_contact` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
                ADD CONSTRAINT `fg_cm_contact_ibfk_29` FOREIGN KEY (`subfed_contact_id`) REFERENCES `fg_cm_contact` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;";
        $this->conn->exec($addConnQuery);
    }
    /**
     * Drop columns from fg_cm_contact table
     */
    private function dropColumnContactTable(){
        $dropQuery="ALTER TABLE `fg_cm_contact`
                DROP FOREIGN KEY fg_cm_contact_ibfk_2,
                DROP FOREIGN KEY fg_cm_contact_ibfk_3,
                DROP `is_member`,
                DROP `language_code`,
                DROP `own_lang`,
                DROP `is_conversation_reply`,
                DROP `origin`,
                DROP `team_id`,
                DROP `is_searchable`,
                DROP `export_data_sharing`,
                DROP `loginCountFlag`,
                DROP `is_newsletter_subscriber`,
                DROP `membership_cat_id`;";
        $this->conn->exec($dropQuery);

    }

    private function writeLog($msg){
        fwrite($this->log, $msg);
        echo nl2br($msg);
    }
}
