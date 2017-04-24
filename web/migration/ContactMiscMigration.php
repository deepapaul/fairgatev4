<?php

/**
 * This class is used for handling contact migration process in various areas
 *
 * @author pitsolutions.com
 */
class ContactMiscMigration {

    private $conn;
    private $log;

    /**
     * Constructor for initial setting.
     *
     * @param type $conn   connection object
     */
    public function __construct($conn) {
        $this->conn = $conn;
        $this->log = fopen('migration_contact_misc_log_' . date('dHis') . '.txt', 'w');
    }

    /**
     * Function to init contact data migration
     *
     * @throws \Clubadmin\ContactBundle\Util\Exception
     */
    public function InitContactMiscMigration() {
        try {
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->beginTransaction();
            $this->changeClubModules();
            $this->changeCmMutationLog();
            $this->changeClubLog();
            $this->changeClubBookmarks();
            $this->changeClubClassLog();
            $this->changeCmChangeLog();
            $this->changeCmChangeToConfirm();
            $this->changeCmContactPrivacy();

            $this->writeLog("Contact were migrated successfully in various areas\n");

            $this->conn->commit();
        } catch (Exception $ex) {
            $this->conn->rollback();
            echo "Failed: " . $ex->getMessage();
            throw $ex;
        }
    }

    /**
     * Function to change club modules table
     *
     *
     */
    public function changeClubModules() {

        $this->conn->exec("UPDATE fg_mb_club_modules cm
                           INNER JOIN fg_cm_contact c ON c.old_contact_id = cm.signed_by  AND cm.club_id = c.club_id
                           SET  cm.signed_by = c.id");
    }

    /**
     * Function to change cm mutation log
     *
     *
     */
    public function changeCmMutationLog() {

        $this->conn->exec("UPDATE fg_cm_mutation_log m
                           INNER JOIN fg_cm_change_toconfirm cc ON m.toconfirm_id = cc.id
                           INNER JOIN fg_cm_contact c ON c.old_contact_id = m.contact_id  AND cc.club_id = c.club_id
                           SET  m.contact_id = c.id");

        $this->conn->exec("UPDATE fg_cm_mutation_log m
                           INNER JOIN fg_cm_change_toconfirm cc ON m.toconfirm_id = cc.id
                           INNER JOIN fg_cm_contact c ON c.old_contact_id = m.confirmed_by  AND cc.club_id = c.club_id
                           SET  m.confirmed_by = c.id");
    }

    /**
     * Function to change club log
     *
     *
     */
    public function changeClubLog() {

        $this->conn->exec("UPDATE fg_club_log cl
                           INNER JOIN fg_cm_contact c ON c.old_contact_id = cl.changed_by  AND cl.club_id = c.club_id
                           SET  cl.changed_by = c.id");
    }

    /**
     * Function to change club bookmarks
     *
     *
     */
    public function changeClubBookmarks() {

        $this->conn->exec("UPDATE fg_club_bookmarks cb
                           INNER JOIN fg_cm_contact c ON c.old_contact_id = cb.contact_id  AND cb.club_id = c.club_id
                           SET  cb.contact_id = c.id");
    }

    /**
     * Function to change club class log
     *
     *
     */
    public function changeClubClassLog() {

        $this->conn->exec("UPDATE fg_club_class_log cl
                           INNER JOIN fg_cm_contact c ON c.old_contact_id = cl.changed_by_contact  AND cl.club_id = c.club_id
                           SET  cl.changed_by_contact = c.id");
    }

    /**
     * Function to change cm change log
     *
     *
     */
    public function changeCmChangeLog() {

        $this->conn->exec("UPDATE fg_cm_change_log CL 
                           INNER JOIN fg_cm_contact C ON C.old_contact_id=CL.contact_id AND CL.club_id=C.club_id
                           SET CL.contact_id=C.id
                           WHERE (kind !='password' OR kind !='login' OR kind !='system' OR kind !='contact type' OR kind !='contact status' OR attribute_id=NULL OR attribute_id IS NULL)");
        
        $this->conn->exec("UPDATE fg_cm_change_log CL 
                           INNER JOIN fg_cm_contact C ON C.old_contact_id=CL.contact_id 
                           SET CL.contact_id=C.fed_contact_id
                           WHERE kind IN('contact type', 'contact status')");
       
        $this->conn->exec("UPDATE fg_cm_change_log CL 
                           INNER JOIN fg_cm_contact C ON C.old_contact_id=CL.changed_by AND CL.club_id=C.club_id
                           SET CL.changed_by=C.id");
        
        $this->conn->exec("UPDATE fg_cm_change_log CL 
                           INNER JOIN fg_cm_contact C ON C.old_contact_id=CL.changed_by AND CL.club_id=C.club_id
                           SET CL.changed_by=C.id");
     
    }

    /**
     * Function to change cm change to confirm
     *
     *
     */
    public function changeCmChangeToConfirm() {

        $this->conn->exec("UPDATE fg_cm_change_toconfirm CC
                           INNER JOIN fg_cm_contact C ON C.old_contact_id = CC.contact_id AND CC.club_id=C.club_id
                           SET  CC.contact_id = C.id");

        $this->conn->exec("UPDATE fg_cm_change_toconfirm CC
                           INNER JOIN fg_cm_contact C ON C.old_contact_id = CC.changed_by AND CC.club_id=C.club_id
                           SET  CC.changed_by = C.id");
    }
    
     /**
     * Function to change cm change contact privacy 
     *
     *
     */
    public function changeCmContactPrivacy(){
        
        $this->conn->exec("UPDATE fg_cm_contact_privacy p
                           INNER JOIN fg_cm_contact c ON c.old_contact_id = p.contact_id 
                           SET  p.contact_id = c.id");
    }
    
    private function writeLog($msg){
        fwrite($this->log, $msg);
        echo nl2br($msg);
    }

}
