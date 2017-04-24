<?php

/**
 * This class is used for handling document migration process
 *
 * @author pitsolutions.com
 */
class GalleryMigration {

    private $conn;
    private $log;

    /**
     * Constructor for initial setting.
     *
     * @param type $conn   connection object
     */
    public function __construct($conn) {
        $this->conn = $conn;
        $this->log = fopen('migration_gallery_log_' . date('dHis') . '.txt', 'w');
    }

    /**
     * Function to init Gallery data migration
     *
     * @throws \Clubadmin\ContactBundle\Util\Exception
     */
    public function InitGalleryMigration() {
        try {
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->beginTransaction();
            $this->changeGalleryTable();
            $this->writeLog("Gallery details has been changed\n");

            $this->conn->commit();
        } catch (Exception $ex) {
            $this->conn->rollback();
            echo "Failed: " . $ex->getMessage();
            throw $ex;
        }
    }

    

    /**
     * Function to change gallery item and gallery bookmark tables
     *
     *
     */
    public function changeGalleryTable() {
        /* To update existing created_by entries */
        $this->conn->exec("UPDATE fg_gm_items g
                          INNER JOIN fg_cm_contact c ON c.old_contact_id = g.created_by AND g.club_id = c.club_id 
                          SET  g.created_by = c.id WHERE c.id !=1");
        /* To update existing updated_by entries */
        $this->conn->exec("UPDATE fg_gm_items g
                          INNER JOIN fg_cm_contact c ON c.old_contact_id = g.updated_by AND g.club_id = c.club_id 
                          SET  g.updated_by = c.id WHERE c.id !=1");
        /* To update existing fg_gm_bookmarks table entries */
        $this->conn->exec("UPDATE fg_gm_bookmarks b
                          INNER JOIN fg_cm_contact c ON c.old_contact_id = b.contact_id AND b.club_id = c.club_id
                          SET  b.contact_id = c.id WHERE c.id !=1");
    }

    private function writeLog($msg){
        fwrite($this->log, $msg);
        echo nl2br($msg);
    }
}
