<?php

/**
 * Alter db tables and migrate assignment of contacts to FedV2
 *
 * @author  pitsolutions.ch <pit@solutions.com>
 *
 * @version Release: <v4>
 */
class ContactConnectionMigration {
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
        $this->log=fopen('connection_migration_log_'.date('dHis').'.txt','w');
    }
    /**
     * Function to init contact connection migration
     * @throws \Clubadmin\ContactBundle\Util\Exception
     */
    public function initContactConnectionMigration(){
        try {
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->beginTransaction();
            
            $this->updateMainContact();
            $this->writeLog("fg_cm_contact Table UPDATED\n");
            $this->updateLinkedContact();
            $this->writeLog("fg_cm_linkedcontact Table UPDATED\n");
            $this->updateLogConnection();
            $this->writeLog("fg_cm_log_connection Table UPDATED\n");
            
            $this->conn->commit();
        } catch (Exception $ex) {
            $this->conn->rollback();
            echo "Failed: " . $ex->getMessage();
            throw $ex;
        }
    }
    
    /**
     * Function to migrate main contact in contact table 
     */
    public function updateMainContact()
    {
    $this->conn->exec("UPDATE fg_cm_contact c "
            . "INNER JOIN fg_cm_contact cc ON c.comp_def_contact = cc.old_contact_id AND cc.club_type = 'federation'"
            . " SET c.comp_def_contact = cc.id");
    }
    /**
     * Function to migrate linked contact table 
     */
    public function updateLinkedContact()
    {
        // Query to update  contact_id 
        $this->conn->exec("UPDATE fg_cm_linkedcontact l 
            INNER JOIN fg_cm_contact c ON  c.old_contact_id = l.contact_id 
            SET l.contact_id = c.id
            WHERE l.club_id = c.club_id");
        // Query to update  linked_contact_id 
        $this->conn->exec("UPDATE fg_cm_linkedcontact l 
            INNER JOIN fg_cm_contact c ON  c.old_contact_id = l.linked_contact_id 
            SET l.linked_contact_id = c.id
            WHERE l.club_id = c.club_id");
    }
    /**
     * Function to migrate connection log table 
     */
    public function updateLogConnection()
    {
        // Query to update  contact_id 
        $this->conn->exec("UPDATE fg_cm_log_connection l 
            INNER JOIN fg_cm_contact c ON  c.old_contact_id = l.contact_id 
            SET l.contact_id = c.id
            WHERE l.assigned_club_id = c.club_id AND l.assigned_club_id IS NOT NULL");
        // Query to update  linked_contact_id 
        $this->conn->exec("UPDATE fg_cm_log_connection l 
            INNER JOIN fg_cm_contact c ON  c.old_contact_id = l.linked_contact_id 
            SET l.linked_contact_id = c.id
            WHERE l.assigned_club_id = c.club_id AND l.assigned_club_id IS NOT NULL");
        // Query to update  linked_contact_id 
        $this->conn->exec("UPDATE fg_cm_log_connection l 
            INNER JOIN fg_cm_contact c ON  c.old_contact_id = l.changed_by 
            SET l.changed_by = c.id
            WHERE l.assigned_club_id = c.club_id AND l.assigned_club_id IS NOT NULL");
    }
    private function writeLog($msg){
        fwrite($this->log, $msg);
        echo nl2br($msg);
    }
}
