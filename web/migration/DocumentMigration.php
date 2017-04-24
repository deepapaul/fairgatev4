<?php

/**
 * This class is used for handling document migration process
 *
 * @author pitsolutions.com
 */
class DocumentMigration {

    private $conn;
    private $log;

    /**
     * Constructor for initial setting.
     *
     * @param type $conn   connection object
     */
    public function __construct($conn) {
        $this->conn = $conn;
        $this->log = fopen('migration_document_log_' . date('dHis') . '.txt', 'w');
    }

    /**
     * Function to init document data migration
     *
     * @throws \Clubadmin\ContactBundle\Util\Exception
     */
    public function InitDocumentMigration() {
        try {
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->beginTransaction();
            $this->changeDocumentLogTable();
            $this->changeDocumentAssignmentTable();
            $this->changeDocumentSightedTable();
            $this->changeDocumentBookmarkandVersionTable();
            $this->writeLog("Documents contact id has been changed\n");

            $this->conn->commit();
        } catch (Exception $ex) {
            $this->conn->rollback();
            echo "Failed: " . $ex->getMessage();
            throw $ex;
        }
    }

    /**
     * Function to change document log table
     *
     *
     */
    public function changeDocumentLogTable() {
        $this->conn->exec(" UPDATE fg_dm_document_log AS L
                           INNER JOIN(
                           SELECT id AS doclog, B.valuebefore AS valuebefore,A.valueafter AS valueafter,C.changedby AS changedby  FROM
                           fg_dm_document_log d
                           LEFT JOIN (
                                SELECT  d.id AS logid,GROUP_CONCAT(cb.id SEPARATOR ',') AS valuebefore
                                FROM fg_dm_document_log d
                                INNER JOIN fg_cm_contact cb ON FIND_IN_SET( cb.old_contact_id, d.value_before_id) AND d.club_id = cb.club_id
                                WHERE d.document_type='CONTACT' AND (d.kind='excluded' OR d.kind='included')
                                GROUP BY d.id) B on B.logid = d.id

                           LEFT JOIN (
                                SELECT  d.id AS logid,GROUP_CONCAT(ca.id SEPARATOR ',') AS valueafter
                                FROM fg_dm_document_log d
                                INNER JOIN fg_cm_contact ca ON FIND_IN_SET( ca.old_contact_id, d.value_after_id) AND d.club_id = ca.club_id
                                WHERE d.document_type='CONTACT' AND (d.kind='excluded' OR d.kind='included')
                                GROUP BY d.id) A on A.logid = d.id

                           LEFT JOIN (
                                SELECT  d.id AS logid,COALESCE(c.id,1) AS changedby
                                FROM fg_dm_document_log d
                                LEFT JOIN fg_cm_contact c ON c.old_contact_id = d.changed_by AND d.club_id = c.club_id
                                GROUP BY d.id) C ON C.logid = d.id
                           ) AS temp on temp.doclog = L.id
                           SET L.value_before_id = temp.valuebefore, L.value_after_id = temp.valueafter, L.changed_by = temp.changedby");
    }

    /**
     * Function to change document assignment tables
     *
     *
     */
    public function changeDocumentAssignmentTable() {
        $assignmentTablesArray = array('fg_dm_assigment', 'fg_dm_assigment_exclude');
        foreach ($assignmentTablesArray as $key => $tablename) {
            $this->conn->exec("UPDATE $tablename d
                                   INNER JOIN fg_dm_documents dm ON dm.id = d.document_id AND dm.document_type ='CONTACT'
                                   INNER JOIN fg_cm_contact c ON c.old_contact_id = d.contact_id AND c.club_id = dm.club_id
                                   SET  d.contact_id = c.id");
        }
    }

    /**
     * Function to change document sighted table
     *
     *
     */
    public function changeDocumentSightedTable() {
        $this->conn->exec("UPDATE fg_dm_contact_sighted d
                          INNER JOIN fg_dm_documents dm ON dm.id = d.document_id
                          INNER JOIN fg_cm_contact c ON c.old_contact_id = d.contact_id AND c.club_id=dm.club_id
                          SET  d.contact_id = c.id");
    }

    /**
     * Function to change document bookmarks and version tables
     *
     *
     */
    public function changeDocumentBookmarkandVersionTable() {
        /* To update existing document bookmarks entries */

        $this->conn->exec("UPDATE fg_dm_bookmarks d
                          INNER JOIN fg_cm_contact c ON c.old_contact_id = d.contact_id AND d.club_id = c.club_id
                          SET  d.contact_id = c.id");



        /* To update existing document version table entries */
        $this->conn->exec("UPDATE fg_dm_version d
                           INNER JOIN fg_dm_documents dm ON dm.id = d.document_id
                           INNER JOIN fg_cm_contact c ON c.old_contact_id = d.created_by AND c.club_id = dm.club_id
                           SET  d.created_by = c.id");

        $this->conn->exec("UPDATE fg_dm_version d
                           INNER JOIN fg_dm_documents dm ON dm.id = d.document_id
                           INNER JOIN fg_cm_contact c ON c.old_contact_id = d.updated_by AND c.club_id = dm.club_id
                           SET  d.updated_by = c.id");
    }

    private function writeLog($msg){
        fwrite($this->log, $msg);
        echo nl2br($msg);
    }
}
