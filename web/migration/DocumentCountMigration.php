<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DocumentCountMigration
 *
 * @author jinesh.m
 */
class DocumentCountMigration
{

    /**
     * $em.
     *
     * @var object entitymanager object
     */
    private $conn;

    /**
     * Club admin database connection
     * @var type 
     */
    private $conn2;
    private $log;

    /**
     * Constructor for initial setting.
     *
     * @param type $conn   container
     */
    public function __construct($conn, $conn2)
    {
        $this->conn = $conn;
        $this->conn2 = $conn2;
        $this->log = fopen('documentcount_migration_log_' . date('dHis') . '.txt', 'w');
    }

    /**
     * Function to init federation icon migration
     * @throws \Clubadmin\ContactBundle\Util\Exception
     */
    public function InitDocumentCountMigration()
    {
        try {
            //$this->conn2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            //$this->conn2->beginTransaction();
            $this->countUpdate();
            $this->writeLog("document count migration executed \n");
            //$this->conn2->commit();
        } catch (Exception $ex) {
            //$this->conn2->rollback();
            echo "Failed: " . $ex->getMessage();
            throw $ex;
        }
    }

    /**
     * Function to migrate fg_sm_bookmarks
     */
    private function countUpdate()
    {
        //mysqli_select_db('fairgate_admin_ui', $this->conn);
        $clubDetailsQuery = mysqli_query($this->conn,"SELECT id as id,federation_id as fedId,sub_federation_id as subFedId,club_type as clubType,url_identifier as UrlIdentifier FROM fg_club WHERE club_type !=''");
        while ($clubDetail = mysqli_fetch_assoc($clubDetailsQuery)) {
            if($clubDetail['fedId'] > 1) {
              $federationId = $clubDetail['fedId'];  
            } else {
              $federationId = $clubDetail['id'];  
            }
            $clubId = $clubDetail['id']; 
           // mysqli_select_db('fairgate_migrate_new', $this->conn2);
            $docCountQuery = mysqli_query($this->conn2,"SELECT COUNT(fg_dm_documents.id) as doccount FROM  fg_dm_documents LEFT JOIN fg_dm_assigment ON fg_dm_documents.id=fg_dm_assigment.document_id  WHERE   fg_dm_documents.club_id = $federationId AND fg_dm_documents.document_type='CLUB'  AND ((fg_dm_documents.deposited_with='SELECTED' AND fg_dm_assigment.club_id=$clubId) OR (fg_dm_documents.deposited_with='ALL' AND $clubId NOT IN (SELECT fg_dm_assigment_exclude.club_id FROM fg_dm_assigment_exclude WHERE fg_dm_assigment_exclude.document_id=fg_dm_documents.id )))");
            $docCount=mysqli_fetch_array($docCountQuery);           
            $count = $docCount['doccount'];
           // mysqli_select_db('fairgate_admin_ui', $this->conn);
            mysqli_query($this->conn,"UPDATE fg_club SET document_count=$count WHERE id=$clubId");
        }

        mysqli_close($this->conn);
        mysqli_close($this->conn2);
         $this->writeLog("document count migration successfully executed \n");
    }

   

    private function writeLog($msg)
    {
        fwrite($this->log, $msg);
        echo nl2br($msg);
    }
}
