<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ForumMigration
 *
 * @author jaikanth
 */
class ForumMigration {
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
        $this->log=fopen('migration_forum_'.date('dHis').'.txt','w');
    }
    /**
     * Function to init Forum migration
     * @throws \Clubadmin\ContactBundle\Util\Exception
     * 
     */
        public function InitForumMigration(){
        try {
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->beginTransaction();
            $this->updateForumTopicContact();
            $this->writeLog("fg_forum_topic UPDATED\n");
            $this->updateForumTopicDataContact();
            $this->writeLog("fg_forum_topic_data Table UPDATED\n");
            $this->updateForumFollowers();
            $this->writeLog("fg_forum_followers Table UPDATED\n");
            $this->updateForumContactDetails();
            $this->writeLog("fg_forum_contact_details Table UPDATED\n");
            $this->conn->commit();
        } catch (Exception $ex) {
            $this->conn->rollback();
            echo "Failed: " . $ex->getMessage();
            throw $ex;
        }
    }
    
    /**
     * Function to update forum topic created/updated contacts
     * 
     */
    public function updateForumTopicContact(){
       // Query to update forum topic $created_by 
        $this->conn->exec("UPDATE fg_forum_topic n 
                INNER JOIN fg_cm_contact c ON  c.old_contact_id = n.created_by
                AND n.club_id = c.club_id AND n.created_by !=1
                SET n.created_by = c.id ");
                
        
       // Query to update forum topic $updated_by  
        $this->conn->exec("UPDATE fg_forum_topic n 
                INNER JOIN fg_cm_contact c ON c.old_contact_id = n.updated_by
                AND n.club_id = c.club_id AND n.updated_by !=1
                SET n.updated_by = c.id ");
                
    }
    /**
     * Function to update forum topic data contacts 
     * 
     */
    public function updateForumTopicDataContact(){
         // Query to update forum Topic data  $created_by
        $this->conn->exec("UPDATE fg_forum_topic_data n 
                INNER JOIN fg_forum_topic m ON m.id = n.forum_topic_id
                INNER JOIN fg_cm_contact c ON c.old_contact_id = n.created_by
                AND c.club_id = m.club_id AND n.created_by !=1
                SET n.created_by = c.id ");
                
       // Query to update forum Topic data  $updated_by
        $this->conn->exec("UPDATE fg_forum_topic_data n 
                INNER JOIN fg_forum_topic m ON m.id = n.forum_topic_id
                INNER JOIN fg_cm_contact c ON c.old_contact_id = n.updated_by
                AND c.club_id = m.club_id AND n.updated_by !=1
                SET n.updated_by = c.id ");
    }
    /**
     * Function to update forum followers contacts 
     * 
     */
    public function updateForumFollowers(){
         // Query to update forum folowers  $contact_id
        $this->conn->exec("UPDATE fg_forum_followers n 
                INNER JOIN fg_cm_contact c ON  c.old_contact_id = n.contact_id
                AND n.club_id = c.club_id AND n.contact_id !=1
                SET n.contact_id = c.id ");
                
    }
    /**
     * Function to update forum followers contacts 
     * 
     */
    public function updateForumContactDetails(){
         // Query to update forum contact details  $contact_id
        $this->conn->exec("UPDATE fg_forum_contact_details n 
                INNER JOIN fg_forum_topic m ON m.id = n.forum_topic_id
                INNER JOIN fg_cm_contact c ON c.old_contact_id = n.contact_id
                AND c.club_id = m.club_id AND n.contact_id !=1
                SET n.contact_id = c.id ");
                
    }
    private function writeLog($msg){
        fwrite($this->log, $msg);
        echo nl2br($msg);
    }
}

        
