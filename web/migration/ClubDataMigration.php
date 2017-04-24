<?php

/**
 * Class for making the existing live clubs data adapatable with
 * fed v2 environment.
 *
 * @author swetha.tg
 */
class ClubDataMigration
{

    private $conn;
    private $log;
//    8734 - sun, 8573 -india, 8607 - epl1
//    private $federationIds = array(
//        '8734' => array('clubMembership' => 1, 'fedMembership' => 0, 'assignFedmembership' => 1, 'addExistingFedMember' => 2),
//        '8607' => array('clubMembership' => 0, 'fedMembership' => 1, 'assignFedmembership' => 1, 'addExistingFedMember' => 2),
//        '8573' => array('clubMembership' => 1, 'fedMembership' => 0, 'assignFedmembership' => 0, 'addExistingFedMember' => 0),
//        '8614' => array('clubMembership' => 1, 'fedMembership' => 0, 'assignFedmembership' => 0, 'addExistingFedMember' => 1),
//    );

//    satus => 2, musterverband => 145, scroche => 157, tvunderstrass => 184, 146 => pitsfed, 240 => zuerichtennis
    private $federationIds = array(
            '2' => array('clubMembership' => 1, 'fedMembership' => 0, 'assignFedmembership' => 0, 'addExistingFedMember' => 0),
            '145' => array('clubMembership' => 1, 'fedMembership' => 0, 'assignFedmembership' => 1, 'addExistingFedMember' => 2),
            '146' => array('clubMembership' => 1, 'fedMembership' => 1, 'assignFedmembership' => 1, 'addExistingFedMember' => 2),
            '157' => array('clubMembership' => 0, 'fedMembership' => 1, 'assignFedmembership' => 1, 'addExistingFedMember' => 2),
            '184' => array('clubMembership' => 1, 'fedMembership' => 0, 'assignFedmembership' => 0, 'addExistingFedMember' => 1),
            '240' => array('clubMembership' => 1, 'fedMembership' => 1, 'assignFedmembership' => 1, 'addExistingFedMember' => 2)
    );

    /**
     * Constructor for initial setting.
     *
     * @param object $conn Connection object
     */
    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->log = fopen('clubdata_migration_log_' . date('dHis') . '.txt', 'w');
    }

    /**
     * Function to init club data migration
     *
     * @throws Exception
     */
    public function initClubDataMigration()
    {
        try {
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->beginTransaction();
            $this->addClubTableColumns();
            $this->updateClubCustomizationValues();
            $this->writeLog("----- CLUB DATA MIGRATION COMPLETED ----- \n");
            $this->conn->commit();
        } catch (Exception $ex) {
            $this->conn->rollback();
            echo "Failed: " . $ex->getMessage();
            throw $ex;
        }
    }

    /**
     * This function is used to alter table columns in fg_club
     */
    private function addClubTableColumns()
    {
        $this->conn->exec("ALTER TABLE `fg_club` ADD `sub_federation_id` INT(11) NOT NULL DEFAULT '0' AFTER `federation_id`;");

        $this->conn->exec("ALTER TABLE `fg_club` ADD `club_membership_available` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '0-no, 1- yes' AFTER `calendar_color_code`;");
        $this->conn->exec("ALTER TABLE `fg_club` ADD `fed_membership_mandatory` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '0-non-mandatory, 1- mandatory' AFTER `club_membership_available`;");
        $this->conn->exec("ALTER TABLE `fg_club` ADD `assign_fedmembership_with_application` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '0-without application, 1- with application' AFTER `fed_membership_mandatory`;");
        $this->conn->exec("ALTER TABLE `fg_club` ADD `add_existing_fed_member_club` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '0 - not possible, 1- possible without application, 2 - possible with application' AFTER `assign_fedmembership_with_application`;");
        $this->conn->exec("ALTER TABLE `fg_club` ADD `fed_admin_access` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '0-no, 1- yes' AFTER `add_existing_fed_member_club`;");

        $this->conn->exec("ALTER TABLE `fg_club_settings` ADD `federation_icon` VARCHAR(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL AFTER `logo`;");
        $this->writeLog("----- CLUB COLUMNS ADDED ---- \n");
    }

    /**
     * This function is used to update the customization values of federations
     */
    private function updateClubCustomizationValues()
    {
        // update sub_federation_id for all sub_fed_clubs.
        $this->conn->exec("UPDATE `fg_club` SET `sub_federation_id` = `parent_club_id` WHERE `club_type` = 'sub_federation_club';");
        $this->writeLog("\n ----- SUB-FEDERATION ID COLUMN UPDATED ----- \n");

        foreach ($this->federationIds as $federationId => $fedv2Customizations) {
            // update customization values for all clubs under this federation
            $updateQry1 = "UPDATE `fg_club` SET `club_membership_available` = " . $fedv2Customizations['clubMembership'] . ", `fed_membership_mandatory` = " . $fedv2Customizations['fedMembership'] . ", "
                    . "`assign_fedmembership_with_application` = " . $fedv2Customizations['assignFedmembership'] . ", `add_existing_fed_member_club` = " . $fedv2Customizations['addExistingFedMember'] . " "
                    . "WHERE (`id` = " . $federationId . " OR `federation_id` = " . $federationId . ");";
            $this->conn->exec($updateQry1);
            $this->writeLog("----- CLUB CUSTOMIZATIONS UPDATED ----- \n");

            //delete fed-own and sub-fed own memberships
            $this->removeFedAndSubfedOwnMembershipData($federationId);

            foreach ($fedv2Customizations as $key => $value) {
                switch ($key) {
                    case 'clubMembership' :
                        if ($value == 0) {
                            $this->removeClubMembershipRelatedData($federationId);
                        }
                        break;

                    case 'fedMembership' :
                        if ($value == 1) {
                            $this->makeFedMembershipMandatory($federationId);
                        }
                        break;

                    default:
                        break;
                }
            }
        }
    }

    /**
     * This function is used to remove fed own and sub-fed own memberships of clubs
     *
     * @param int $federationId
     */
    private function removeFedAndSubfedOwnMembershipData($federationId)
    {
        //update joining date, leaving date fields of entries in fg_cm_contact
        $updateQry2 = "UPDATE `fg_cm_contact` C INNER JOIN `fg_cm_membership` M ON (M.`id` = C.`membership_cat_id` AND M.`is_fed_category` = 0) "
                . "INNER JOIN `fg_club` CL ON (CL.`id` = C.`club_id` AND (CL.`id` = " . $federationId . " OR (CL.`parent_club_id` = " . $federationId . " AND CL.`is_sub_federation` = 1))) "
                . "SET C.`joining_date` = NULL, C.`leaving_date` = NULL;";
        $this->conn->exec($updateQry2);
        $this->writeLog("----- CONTACT JOINING N LEAVING DATES SET TO NULL FOR OWN MEMBERSHIPS ---- \n");

        //delete entries from fg_cm_membership table for clubs under this federation
        $deleteQry1 = "DELETE M FROM `fg_cm_membership` M INNER JOIN `fg_club` CL ON (M.`club_id` = CL.`id`) WHERE M.`is_fed_category` = 0 AND "
                . "(CL.`id` = " . $federationId . " OR (`parent_club_id` = " . $federationId . " AND CL.`is_sub_federation` = 1));";
        $this->conn->exec($deleteQry1);
        $this->writeLog("----- FED N SUB-FED OWN MEMBERSHIPS DELETED ---- \n");
    }

    /**
     * This function is used to remove club memberships for clubs with C1 OFF
     *
     * @param int $federationId
     */
    private function removeClubMembershipRelatedData($federationId)
    {
        //update joining date, leaving date fields of entries in fg_cm_contact
        $updateQry3 = "UPDATE `fg_cm_contact` C INNER JOIN `fg_cm_membership` M ON (M.`id` = C.`membership_cat_id` AND M.`is_fed_category` = 0) "
                . "INNER JOIN `fg_club` CL ON (CL.`id` = C.`club_id` AND (CL.`federation_id` = " . $federationId . " AND CL.`is_sub_federation` = 0)) "
                . "SET C.`joining_date` = NULL, C.`leaving_date` = NULL;";
        $this->conn->exec($updateQry3);
        $this->writeLog("----- CONTACT JOINING N LEAVING DATES SET TO NULL ---- \n");
        
        //delete entries from fg_cm_membership table for clubs under this federation
        $deleteQry1 = "DELETE M FROM `fg_cm_membership` M INNER JOIN `fg_club` CL ON (M.`club_id` = CL.`id`) WHERE M.`is_fed_category` = 0 AND "
                . "(CL.`federation_id` = " . $federationId . " AND CL.`is_sub_federation` = 0);";
        $this->conn->exec($deleteQry1);
        $this->writeLog("----- CLUB N FED OWN MEMBERSHIPS DELETED ---- \n");
    }

    /**
     * This function is used to make federation membership mandatory for a federation
     *
     * @param int $federationId
     */
    private function makeFedMembershipMandatory($federationId)
    {
        $statement1 = $this->conn->query("SELECT GROUP_CONCAT(`id`) FROM `fg_club` WHERE (`federation_id` = " . $federationId . "  OR `id` = " . $federationId . ");");
        $clubIdsStr = $statement1->fetchAll(\PDO::FETCH_COLUMN);
        $clubIds = explode(',', $clubIdsStr[0]);

        $selectQry = "SELECT COUNT(C.`id`) FROM `fg_cm_contact` C WHERE C.`club_id` IN ( " . $clubIdsStr[0]
                . ") AND (C.`membership_cat_id` IS NULL OR C.`membership_cat_id` NOT IN (SELECT M.`id` FROM fg_cm_membership M WHERE M.`club_id` = " . $federationId . " AND M.is_fed_category = 1));";
        $statement = $this->conn->query($selectQry);
        $result = $statement->fetchAll(\PDO::FETCH_COLUMN);
        $contactExistsWithoutFedMembershipInFederation = $result[0];

        if ($contactExistsWithoutFedMembershipInFederation) {
            $this->writeLog("----- Contacts without fed membership exist in federation ----- \n");
            $defaultLang = 'en';
            //create a default membership
            $insertQry1 = "INSERT INTO `fg_cm_membership` (`club_id`, `title`, `sort_order`, `is_fed_category`) VALUES (" . $federationId . ", 'Default mandatory fed membership', (SELECT MAX(`sort_order`) + 1 FROM `fg_cm_membership` M WHERE M.`club_id` = " . $federationId . "), '1');";
            $this->conn->exec($insertQry1);
            $this->writeLog("----- Membership entry inserted ----- \n");

            //get new fed membership id, its title and insert entry in i18n table
            $insertQry2 = "SELECT LAST_INSERT_ID() INTO @fedmembershipId; SELECT `title` FROM `fg_cm_membership` WHERE `id` = @fedmembershipId INTO @membershipTitle; INSERT INTO `fg_cm_membership_i18n` (`id`, `lang`, `title_lang`, `is_active`) VALUES (@fedmembershipId, '" . $defaultLang . "', @membershipTitle, 1);";
            $this->conn->exec($insertQry2);
            $this->writeLog("----- Membership i18n entry inserted ----- \n");

            //update membership data log entries
            $insertQry3 = "INSERT INTO `fg_cm_membership_log` (`club_id`, `membership_id`, `date`, `kind`, `field`, `value_before`, `value_after`, `changed_by`) VALUES (" . $federationId . ", @fedmembershipId, '" . date('Y-m-d H:i:s')
                    . "', 'data', 'Name(" . $defaultLang . ")', '', @membershipTitle, '1'); ";
            $this->conn->exec($insertQry3);
            $this->writeLog("----- Membership data log entry inserted ----- \n");

            foreach ($clubIds as $clubId) {
                //check whether contact exist in this club without fed membership
                $selectQry1 = "SELECT COUNT(C.`id`) FROM `fg_cm_contact` C WHERE C.`club_id` = " . $clubId
                        . " AND (C.`membership_cat_id` IS NULL OR C.`membership_cat_id` NOT IN (SELECT M.`id` FROM fg_cm_membership M WHERE M.`club_id` = " . $federationId . " AND M.is_fed_category = 1));";
                $statement2 = $this->conn->query($selectQry1);
                $result2 = $statement2->fetchAll(\PDO::FETCH_COLUMN);
                $contactExistsWithoutFedMembership = $result2[0];
                $this->writeLog("----- Contacts without fed membership exist in club " . $clubId . "----- \n");

                if ($contactExistsWithoutFedMembership) {
                    //insert log entries corresponding to membership assignment
                    $insertQry4 = "INSERT INTO `fg_cm_membership_log` (`club_id`, `contact_id`, `membership_id`, `date`, `kind`, `value_before`, `value_after`, `changed_by`) SELECT " . $clubId . ", C.`id`, @fedmembershipId, '" . date('Y-m-d H:i:s') . "', 'assigned contacts', '', contactName(C.`id`), '1' "
                            . "FROM `fg_cm_contact` C WHERE C.`club_id` = " . $clubId . " AND (C.`membership_cat_id` IS NULL OR C.`membership_cat_id` NOT IN (SELECT M.`id` FROM `fg_cm_membership` M WHERE M.`club_id` = " . $federationId . " AND M.is_fed_category = 1));";
                    $this->conn->exec($insertQry4);
                    $this->writeLog("----- Membership log entries inserted ----- \n");

                    //insert log entries in membership history
                    $insertQry5 = "INSERT INTO `fg_cm_membership_history` (`contact_id`, `membership_club_id`, `membership_id`, `membership_type`, `joining_date`, `changed_by`) SELECT C.`id`, " . $federationId . ", @fedmembershipId, 'federation', '" . date('Y-m-d H:i:s') . "', '1' "
                            . "FROM `fg_cm_contact` C WHERE C.`club_id` = " . $clubId . " AND (C.`membership_cat_id` IS NULL OR C.`membership_cat_id` NOT IN (SELECT M.`id` FROM `fg_cm_membership` M WHERE M.`club_id` = " . $federationId . " AND M.is_fed_category = 1));";
                    $this->conn->exec($insertQry5);
                    $this->writeLog("----- Membership history entries inserted ----- \n");

                    //assign all contacts without fed membership to this default fed membership
                    $insertQry6 = "UPDATE `fg_cm_contact` C SET C.`membership_cat_id` = @fedmembershipId, C.`joining_date` = '" . date('Y-m-d H:i:s') . "' WHERE C.`club_id` = " . $clubId
                        . " AND (C.`membership_cat_id` IS NULL OR C.`membership_cat_id` NOT IN (SELECT M.`id` FROM fg_cm_membership M WHERE M.`club_id` = " . $federationId . " AND M.`is_fed_category` = 1));";
                    $this->conn->exec($insertQry6);
                    $this->writeLog("----- Membership_cat_id & joining_date updated in fg_cm_contact ----- \n");
                }
            }
        }
    }

//    private function insertClubAssignmentEntries($federationId)
//    {
//        //insert club assignment entries corresponding to fed membership
//        $insertQry4 = "INSERT INTO `fg_cm_membership_log` (`club_id`, `fed_contact_id`, `membership_id`, `date`, `kind`, `value_before`, `value_after`, `changed_by`) SELECT " . $clubId . ", C.`id`, @fedmembershipId, '" . date('Y-m-d H:i:s') . "', 'assigned contacts', '', contactName(C.`id`), '1' "
//                . "FROM `fg_cm_contact` C WHERE C.`club_id` = " . $clubId . " AND (C.`membership_cat_id` IS NULL OR C.`membership_cat_id` NOT IN (SELECT M.`id` FROM `fg_cm_membership` M WHERE M.`club_id` = " . $federationId . " AND M.is_fed_category = 1));";
//        $this->conn->exec($insertQry4);
//        $this->writeLog("----- Membership log entries inserted ----- \n");
//    }

    /**
     * This function is used to write text to log file
     *
     * @param string $msg
     */
    private function writeLog($msg)
    {
        fwrite($this->log, $msg);
        echo nl2br($msg);
    }
}
