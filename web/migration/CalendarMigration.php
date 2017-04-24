<?php

/**
 * This class is used for handling document migration process
 *
 * @author pitsolutions.com
 */
class CalendarMigration {

    private $conn;
    private $log;

    /**
     * Constructor for initial setting.
     *
     * @param type $conn   connection object
     */
    public function __construct($conn) {
        $this->conn = $conn;
        $this->log = fopen('migration_calendar_log_' . date('dHis') . '.txt', 'w');
    }

    /**
     * Function to init document data migration
     *
     * @throws \Clubadmin\ContactBundle\Util\Exception
     */
    public function InitCalendarMigration() {
        try {
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->beginTransaction();
            $this->changeCalendarTable();
            $this->writeLog("Calendar details has been changed\n");

            $this->conn->commit();
        } catch (Exception $ex) {
            $this->conn->rollback();
            echo "Failed: " . $ex->getMessage();
            throw $ex;
        }
    }

    

    /**
     * Function to change document bookmarks and version tables
     *
     *
     */
    public function changeCalendarTable() {
        /* To update existing created by entries */

        $this->conn->exec("UPDATE fg_em_calendar d
                          INNER JOIN fg_cm_contact c ON c.old_contact_id = d.created_by AND d.club_id = c.club_id
                          SET  d.created_by = c.id");

        /* To update existing created by entries */

        $this->conn->exec("UPDATE fg_em_calendar d
                          INNER JOIN fg_cm_contact c ON c.old_contact_id = d.updated_by AND d.club_id = c.club_id
                          SET  d.updated_by = c.id");



        /* To update existing fg_em_calendar_details table entries */
        $this->conn->exec("UPDATE fg_em_calendar_details d
                           INNER JOIN fg_em_calendar dm ON dm.id = d.calendar_id
                           INNER JOIN fg_cm_contact c ON c.old_contact_id = d.created_by AND c.club_id = dm.club_id  
                           SET  d.created_by = c.id");

        $this->conn->exec("UPDATE fg_em_calendar_details d
                           INNER JOIN fg_em_calendar dm ON dm.id = d.calendar_id
                           INNER JOIN fg_cm_contact c ON c.old_contact_id = d.updated_by AND c.club_id = dm.club_id  
                           SET  d.updated_by = c.id");
        
    }

    private function writeLog($msg){
        fwrite($this->log, $msg);
        echo nl2br($msg);
    }
}
