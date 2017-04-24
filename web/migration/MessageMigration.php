<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MessageMigration
 *
 * @author jaikanth
 */
class MessageMigration {
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
        $this->log=fopen('migration_message_'.date('dHis').'.txt','w');
    }
    /**
     * Function to init Message migration
     * @throws \Clubadmin\ContactBundle\Util\Exception
     * 
     */
    public function InitMessageMigration(){
        try {
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->beginTransaction();
            $this->updateMessageContact();
            $this->writeLog("fg_message Table UPDATED\n");
            $this->updateMessageReceiverContact();
            $this->writeLog("fg_message_receiver Table UPDATED\n");
            $this->updateMessageDataSender();
            $this->writeLog("fg_message_data Table UPDATED\n");
            $this->conn->commit();
        } catch (Exception $ex) {
            $this->conn->rollback();
            echo "Failed: " . $ex->getMessage();
            throw $ex;
        }
    }
    /**
     * Function to update message created/updated contacts
     * 
     */
    public function updateMessageContact(){
       // Query to update message $created_by 
        $this->conn->exec("UPDATE fg_message n 
                INNER JOIN fg_cm_contact c ON  c.old_contact_id = n.created_by
                AND n.club_id = c.club_id AND n.created_by !=1
                SET n.created_by = c.id ");
                
        
       // Query to update message $update_by  
        $this->conn->exec("UPDATE fg_message n 
                INNER JOIN fg_cm_contact c ON c.old_contact_id = n.update_by
                AND n.club_id = c.club_id AND n.update_by !=1
                SET n.update_by = c.id ");
                
    }
    /**
     * Function to update message receiver contacts 
     * 
     */
    public function updateMessageReceiverContact(){
         // Query to update  Receiver $contact_id 
        $this->conn->exec("UPDATE fg_message_receivers n 
                INNER JOIN fg_message m ON m.id = n.message_id
                INNER JOIN fg_cm_contact c ON c.old_contact_id = n.contact_id
                AND c.club_id = m.club_id AND n.contact_id !=1
                SET n.contact_id = c.id ");
                

    }
    /**
     * Function to update message data sender id
     * 
     */
    public function updateMessageDataSender(){
         // Query to update Meesage data Sender
         $this->conn->exec("UPDATE fg_message_data n 
                  INNER JOIN fg_message m ON m.id = n.message_id
                  INNER JOIN fg_cm_contact c ON c.old_contact_id = n.sender_id
                  AND c.club_id = m.club_id AND n.sender_id !=1
                  SET n.sender_id = c.id ");
                  

    } 
    
    private function writeLog($msg){
        fwrite($this->log, $msg);
        echo nl2br($msg);
    }
}
