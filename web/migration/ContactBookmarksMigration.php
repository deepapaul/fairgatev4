<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ContactBookmarksMigration
 *
 * @author Ravikumar
 */
class ContactBookmarksMigration
{

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
    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->log = fopen('migration_contact_bookmark_' . date('dHis') . '.txt', 'w');
    }

    /**
     * Function to init Contact Bookmark migration
     * @throws \Clubadmin\ContactBundle\Util\Exception
     */
    public function InitContactBookmarkMigration()
    {
        try {
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->beginTransaction();
            $this->updateClubBookmarkContact();
            $this->writeLog("fg_club_bookmarks Table UPDATED\n");
            $this->updateContactBookmarkNotes();
            $this->writeLog("fg_cm_bookmarks Table UPDATED\n");
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
    public function updateClubBookmarkContact()
    {
        // Query to update  $created_by 
        $this->conn->exec("UPDATE fg_club_bookmarks b 
            INNER JOIN fg_cm_contact c ON  c.old_contact_id = b.contact_id 
            SET b.contact_id = c.id
            WHERE b.club_id = c.club_id AND b.contact_id !=1");
        
    }

    /**
     * Function to move contacts notes 
     */
    public function updateContactBookmarkNotes()
    {
        // Query to update  $contact_id 
        $this->conn->exec("UPDATE fg_cm_bookmarks b INNER JOIN fg_cm_contact c ON  c.old_contact_id = b.contact_id SET b.contact_id = c.id WHERE b.club_id = c.club_id AND b.contact_id !=1");
    }

    /**
     * Function to write log
     * @param type $msg Message
     */
    private function writeLog($msg)
    {
        fwrite($this->log, $msg);
        echo nl2br($msg);
    }

}
