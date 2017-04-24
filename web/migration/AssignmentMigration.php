<?php

/**
 * Alter db tables and migrate assignment of contacts to FedV2
 *
 * @author  pitsolutions.ch <pit@solutions.com>
 *
 * @version Release: <v4>
 */
class AssignmentMigration {
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
        $this->log=fopen('assignment_migration_log_'.date('dHis').'.txt','w');
    }
    /**
     * Function to init contact migration
     * @throws \Clubadmin\ContactBundle\Util\Exception
     */
    public function InitAssignmentMigration($step){
        try {
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->beginTransaction();
            switch($step){
                CASE 1:
                    $this->updateContactInRoleContact();
                    $this->writeLog("ROLE CONTACT TABLE UPDATED\n");
                    break;
                CASE 2:
                    $this->updateAssignmentLog();
                    $this->writeLog("ASSIGNMENT LOG TABLE UPDATED\n");
                    break;
//            $this->updateFunctionLog();
//            $this->writeLog("FUNCTION LOG TABLE UPDATED\n");
//                CASE 3:
//                    $this->addColumnToAssignmentLogTable();
//                    $this->writeLog("ADDED CONTACT COLUMN IN LOG TABLE\n");
//                    break;
//                case 4:
//                    $this->addConnectionToLogTable();
//                    $this->writeLog("ADDED CONNECTION TO LOG TABLE\n");
//                    break;
                default:
                    break;
            }
            $this->conn->commit();
        } catch (Exception $ex) {
            $this->conn->rollback();
            echo "Failed: " . $ex->getMessage();
            throw $ex;
        }
    }
    /**
     * Function to add contact column in log table
     */
    public function addColumnToAssignmentLogTable() {
        $addRoleFieldQuery ="ALTER TABLE `fg_rm_role_log` "
                . "ADD `contact_id` int(11) DEFAULT NULL AFTER role_id;";
        $this->conn->exec($addRoleFieldQuery);
        $addRoleIndexQuery="ALTER TABLE `fg_rm_role_log`
                            ADD KEY `contact_id` (`contact_id`);";
        $this->conn->exec($addRoleIndexQuery);
        $addFunctionFieldQuery ="ALTER TABLE `fg_rm_function_log` "
                . "ADD `contact_id` int(11) DEFAULT NULL AFTER function_id;";
        $this->conn->exec($addFunctionFieldQuery);
        $addFunctionIndexQuery="ALTER TABLE `fg_rm_function_log`
                            ADD KEY `contact_id` (`contact_id`);";
        $this->conn->exec($addFunctionIndexQuery);
    }
    /**
     * Add connection to log table
     */
    private function addConnectionToLogTable(){
        $addRoleConnQuery="ALTER TABLE `fg_rm_role_log`
                ADD CONSTRAINT `fg_rm_role_log_ibfk_5` FOREIGN KEY (`contact_id`) REFERENCES `fg_cm_contact` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION";
        $this->conn->exec($addRoleConnQuery);
        $addFunctionConnQuery="ALTER TABLE `fg_rm_function_log`
                ADD CONSTRAINT `fg_rm_function_log_ibfk_4` FOREIGN KEY (`contact_id`) REFERENCES `fg_cm_contact` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION";
        $this->conn->exec($addFunctionConnQuery);
    }
    /**
     * Function to update role contact table
     */
    public function updateContactInRoleContact() {
        $this->conn->exec("UPDATE fg_rm_role_contact rc "
                . "INNER JOIN fg_cm_contact c ON rc.contact_id=c.old_contact_id "
                . "INNER JOIN fg_rm_category_role_function crf ON rc.fg_rm_crf_id=crf.id "
                . "INNER JOIN fg_rm_category ca ON crf.category_id=ca.id "
                . "INNER JOIN fg_club fc ON ca.club_id=fc.id "
                . "SET rc.contact_id=c.id, "
                . "rc.contact_club_id=ca.club_id "
                . " WHERE ca.club_id=c.club_id");
    }
    /**
     * Function to update assignment contact table
     */
    public function updateAssignmentLog() {
        $this->conn->exec("UPDATE fg_cm_log_assignment la "
                . "INNER JOIN fg_cm_contact c ON la.contact_id=c.old_contact_id "
                . "INNER JOIN fg_club fc ON la.category_club_id=fc.id "
                . "SET la.contact_id=c.id "
                . " WHERE la.category_club_id=c.club_id");
    }
    private function writeLog($msg){
        fwrite($this->log, $msg);
        echo nl2br($msg);
    }
    /**
     * Function to update role log
     */
//    public function updateRoleLog() {
//        $this->conn->exec("UPDATE fg_rm_role_log rc "
//                . "INNER JOIN fg_cm_contact c ON rc.contact_id=c.old_contact_id "
//                . "INNER JOIN fg_rm_category_role_function crf ON rc.fg_rm_crf_id=crf.id "
//                . "INNER JOIN fg_rm_category ca ON crf.category_id=ca.id "
//                . "INNER JOIN fg_club fc ON ca.club_id=fc.id"
//                . "SET rc.contact_id=c.id, "
//                . "rc.contact_club_id=ca.club_id"
//                . "WHERE ca.club_id=c.club_id");
//    }
//    /**
//     * Function to update function log
//     */
//    public function updateFunctionLog() {
//        $this->conn->exec("UPDATE fg_rm_function_log rc "
//                . "INNER JOIN fg_cm_contact c ON rc.contact_id=c.old_contact_id "
//                . "INNER JOIN fg_rm_category_role_function crf ON rc.fg_rm_crf_id=crf.id "
//                . "INNER JOIN fg_rm_category ca ON crf.category_id=ca.id "
//                . "INNER JOIN fg_club fc ON ca.club_id=fc.id"
//                . "SET rc.contact_id=c.id, "
//                . "rc.contact_club_id=ca.club_id"
//                . "WHERE ca.club_id=c.club_id");
//    }
}
