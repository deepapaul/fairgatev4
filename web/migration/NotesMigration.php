<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of NotesMigration
 *
 * @author jaikanth
 */
class NotesMigration {
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
        $this->log=fopen('migration_notes_'.date('dHis').'.txt','w');
    }
     /**
     * Function to init Notes migration
     * @throws \Clubadmin\ContactBundle\Util\Exception
     */
    public function InitNotesMigration(){
        try {
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->beginTransaction();
            $this->updateClubNotesContact();
            $this->writeLog("fg_club_notes Table UPDATED\n");
            $this->updateContactNotes();
            $this->writeLog("fg_cm_notes Table UPDATED\n");
            $this->updateNotesLog();
            $this->writeLog("fg_club_log_notes Table UPDATED\n");
            $this->conn->commit();
        } catch (Exception $ex) {
            $this->conn->rollback();
            echo "Failed: " . $ex->getMessage();
            throw $ex;
        }
    }
     /**
     * Function to move club notes contact 
     */
    public function updateClubNotesContact(){
       // Query to update  $created_by 
        $this->conn->exec("UPDATE fg_club_notes n 
            INNER JOIN fg_cm_contact c ON  c.old_contact_id = n.created_by_contact 
            SET n.created_by_contact = c.id
            WHERE n.created_by_club = c.club_id AND n.created_by_contact !=1");
       // Query to update  $edited_by  
        $this->conn->exec("UPDATE fg_club_notes n INNER JOIN fg_cm_contact c ON  c.old_contact_id = n.edited_by_contact SET n.edited_by_contact = c.id WHERE n.edited_by_club = c.club_id AND n.edited_by_contact !=1");
    }
    /**
     * Function to move contacts notes 
     */
    public function updateContactNotes(){
         // Query to update  $contact_id 
        $this->conn->exec("UPDATE fg_cm_notes n INNER JOIN fg_cm_contact c ON  c.old_contact_id = n.contact_id SET n.contact_id = c.id WHERE n.club_id = c.club_id AND n.contact_id !=1");
         // Query to update  $edited_by 
        $this->conn->exec("UPDATE fg_cm_notes n INNER JOIN fg_cm_contact c ON  c.old_contact_id = n.edited_by SET n.edited_by = c.id WHERE n.club_id = c.club_id AND n.edited_by !=1");
        // Query to update  $created_by 
        $this->conn->exec("UPDATE fg_cm_notes n INNER JOIN fg_cm_contact c ON  c.old_contact_id = n.created_by SET n.created_by = c.id WHERE n.club_id = c.club_id AND n.created_by !=1");
    } 
    /**
     * Function to move club log notes contact migration
     */
    public function updateNotesLog(){
         // Query to update  $changed_by
         $this->conn->exec("UPDATE fg_club_log_notes n INNER JOIN fg_cm_contact c ON  c.old_contact_id = n.changed_by SET n.changed_by = c.id WHERE n.assigned_club_id = c.club_id AND n.changed_by !=1");
         // Query to update  $note_contact_id
         $this->conn->exec("UPDATE fg_club_log_notes n INNER JOIN fg_cm_contact c ON  c.old_contact_id = n.note_contact_id SET n.note_contact_id = c.id WHERE n.assigned_club_id = c.club_id AND n.note_contact_id !=1");
      
    } 
    private function writeLog($msg){
        fwrite($this->log, $msg);
        echo nl2br($msg);
    }
}
