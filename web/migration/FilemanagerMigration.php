<?php

/**
 * This class is used for handling filemanager migration process
 *
 * @author pitsolutions.com
 */
class FilemanagerMigration {

    private $conn;
    private $log;

    /**
     * Constructor for initial setting.
     *
     * @param type $conn   connection object
     */
    public function __construct($conn) {
        $this->conn = $conn;
        $this->log = fopen('migration_filemanager_log_' . date('dHis') . '.txt', 'w');
    }

    /**
     * Function to init filemanager data migration
     *
     * @throws exception
     */
    public function InitFilemanagerMigration() {
        try {
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->beginTransaction();
            $this->changeFilemanagerTable();
            $this->writeLog("Filemanager details migrated");

            $this->conn->commit();
        } catch (Exception $ex) {
            $this->conn->rollback();
            echo "Failed: " . $ex->getMessage();
            throw $ex;
        }
    }

    public function changeFilemanagerTable() {

        $this->conn->exec("UPDATE fg_file_manager_version fv
                            INNER JOIN fg_file_manager f ON f.id = fv.file_manager_id
                            INNER JOIN fg_cm_contact c ON c.old_contact_id = fv.uploaded_by AND f.club_id = c.club_id
                            SET  fv.uploaded_by = c.id");

        $this->conn->exec("UPDATE fg_file_manager_version fv
                            INNER JOIN fg_file_manager f ON f.id = fv.file_manager_id
                            INNER JOIN fg_cm_contact c ON c.old_contact_id = fv.updated_by AND f.club_id = c.club_id
                            SET  fv.updated_by = c.id");

        $this->conn->exec("UPDATE fg_file_manager_log fl
                            INNER JOIN fg_file_manager f ON f.id = fl.file_manager_id
                            INNER JOIN fg_cm_contact c ON c.old_contact_id = fl.changed_by AND f.club_id = c.club_id
                            SET  fl.changed_by = c.id");
    }

    private function writeLog($msg){
        fwrite($this->log, $msg);
        echo nl2br($msg);
    }
}
