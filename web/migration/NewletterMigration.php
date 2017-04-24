<?php

/**
 * This class is used for handling newsletter migration process
 *
 * @author pitsolutions.com
 */
class NewsletterMigration {

    private $conn;
    private $log;

    /**
     * Constructor for initial setting.
     *
     * @param type $conn   connection object
     */
    public function __construct($conn) {
        $this->conn = $conn;
        $this->log = fopen('migration_newsletter_log_' . date('dHis') . '.txt', 'w');
    }

    /**
     * Function to init newsletter data migration
     *
     * @throws exception
     */
    public function InitNewsletterMigration() {
        try {
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->beginTransaction();
            $this->changeFgCbSubscriber();
            $this->writeLog("fg_cn_subscriber Table migrated");
            $this->changeFgCbSubscriberLog();
            $this->writeLog("fg_cn_subscriber_log Table migrated");
            $this->changeFgCnRecepientsException();
            $this->writeLog("fg_cn_recepients_exception Table migrated");
            $this->changeFgCnNewsletterTemplate();
            $this->writeLog("fg_cn_newsletter_template Table migrated");
            $this->changeFgCnNewsletterLog();
            $this->writeLog("fg_cn_newsletter_log Table migrated");
            $this->changeFgCnNewsletterIntroClosingWords();
            $this->writeLog("fg_cn_newsletter_intro_closing_words Table migrated");
            $this->changeFgCnNewsletter();
            $this->writeLog("fg_cn_newsletter Table migrated");
            $this->changeFgCnNewsletterManualContacts();
            $this->writeLog("fg_cn_newsletter_manual_contacts Table migrated");
            $this->changeFgCnRecepientsMandatory();
            $this->writeLog("fg_cn_recepients_mandatory Table migrated");
            
            
            $this->conn->commit();
        } catch (Exception $ex) {
            $this->conn->rollback();
            echo "Failed: " . $ex->getMessage();
            throw $ex;
        }
    }

    /**
     * Function to change FgCbSubscriber database entries
     */
    public function changeFgCbSubscriber() {

        $this->conn->exec("UPDATE fg_cn_subscriber fcs
                            INNER JOIN fg_cm_contact c ON c.old_contact_id = fcs.created_by AND fcs.club_id = c.club_id
                            SET fcs.created_by = c.id");
        
        $this->conn->exec("UPDATE fg_cn_subscriber fcs
                            INNER JOIN fg_cm_contact c ON c.old_contact_id = fcs.edited_by AND fcs.club_id = c.club_id
                            SET fcs.edited_by = c.id");    
    }
    
    /**
     * Function to change FgCbSubscriberLog database entries
     */
    public function changeFgCbSubscriberLog() {

        $this->conn->exec("UPDATE fg_cn_subscriber_log fcsl
                            INNER JOIN fg_cm_contact c ON c.old_contact_id = fcsl.changed_by AND fcsl.club_id = c.club_id
                            SET fcsl.changed_by = c.id");
    }
    
    /**
     * Function to change FgCnRecepientsException database entries
     */
    public function changeFgCnRecepientsException() {

        $this->conn->exec("UPDATE fg_cn_recepients_exception fcre
                            LEFT JOIN fg_cn_recepients fcr ON fcr.id = fcre.recepient_list_id
                            INNER JOIN fg_cm_contact c ON c.old_contact_id = fcre.contact_id AND fcr.club_id = c.club_id
                            SET fcre.contact_id = c.id");
    }
    
    /**
     * Function to change FgCnNewsletterTemplate database entries
     */
    public function changeFgCnNewsletterTemplate() {

        $this->conn->exec("UPDATE fg_cn_newsletter_template fcnt
                            INNER JOIN fg_cm_contact c ON c.old_contact_id = fcnt.edited_by AND fcnt.club_id = c.club_id
                            SET fcnt.edited_by = c.id");
        
        $this->conn->exec("UPDATE fg_cn_newsletter_template fcnt
                            INNER JOIN fg_cm_contact c ON c.old_contact_id = fcnt.created_by AND fcnt.club_id = c.club_id
                            SET fcnt.created_by = c.id");    
    }
    
    /**
     * Function to change FgCnNewsletterLog database entries
     */
    public function changeFgCnNewsletterLog() {

        $this->conn->exec("UPDATE fg_cn_newsletter_log fcnl
                            INNER JOIN fg_cm_contact c ON c.old_contact_id = fcnl.sent_by AND fcnl.club_id = c.club_id
                            SET fcnl.sent_by = c.id");
    }
    
    /**
     * Function to change FgCnNewsletterIntroClosingWords database entries
     */
    public function changeFgCnNewsletterIntroClosingWords() {

        $this->conn->exec("UPDATE fg_cn_newsletter_intro_closing_words fcniw
                            INNER JOIN fg_cm_contact c ON c.old_contact_id = fcniw.updated_by AND fcniw.club_id = c.club_id
                            SET fcniw.updated_by = c.id");
    }
    
    /**
     * Function to change FgCnNewsletter database entries
     */
    public function changeFgCnNewsletter() {

        $this->conn->exec("UPDATE fg_cn_newsletter fcn
                            INNER JOIN fg_cm_contact c ON c.old_contact_id = fcn.updated_by AND fcn.club_id = c.club_id
                            SET fcn.updated_by = c.id ");
        
        $this->conn->exec("UPDATE fg_cn_newsletter fcn
                            INNER JOIN fg_cm_contact c ON c.old_contact_id = fcn.created_by AND fcn.club_id = c.club_id
                            SET fcn.created_by = c.id ");
          
    }
    /**
     * Function to change FgCnNewsletterManualContacts database entries
     */
    public function changeFgCnNewsletterManualContacts() {

        $this->conn->exec("UPDATE fg_cn_newsletter_manual_contacts fcnmc
                            INNER JOIN fg_cn_newsletter fcn ON fcnmc.newsletter_id = fcn.id
                            INNER JOIN fg_cm_contact c ON c.old_contact_id = fcnmc.contact_id AND fcn.club_id = c.club_id
                            SET fcnmc.contact_id = c.id");
        
          
    }
    /**
     * Function to change FgCnRecepientsMandatory database entries
     */
    public function changeFgCnRecepientsMandatory() {
        $this->conn->exec("UPDATE fg_cn_recepients_mandatory fcrm
                            INNER JOIN fg_cn_recepients fcn ON fcrm.recepient_list_id = fcn.id
                            INNER JOIN fg_cm_contact c ON c.old_contact_id = fcrm.contact_id AND fcn.club_id = c.club_id
                            SET fcrm.contact_id = c.id");
        
//        $this->conn->exec(" UPDATE fg_cn_recepients_mandatory AS M
//                            INNER JOIN(
//                                SELECT id AS mandatorylog, A.linkedcontacts AS linkedcontacts  
//                                FROM fg_cn_recepients_mandatory d
//                                
//
//                                LEFT JOIN (
//                                     SELECT d.id as logid,GROUP_CONCAT( ca.id  SEPARATOR ',') AS linkedcontacts
//                                     FROM fg_cn_recepients_mandatory d
//                                     INNER JOIN fg_cm_contact ca ON FIND_IN_SET( ca.old_contact_id, d.linked_contact_ids) AND d.club_id = ca.club_id
//                                     INNER JOIN fg_club c on c.id = d.club_id
//                                     GROUP BY d.id) A on A.logid = d.id
//                            ) AS temp on temp.mandatorylog = M.id
//                            SET M.contact_id = temp.contacts, M.linked_contact_ids = temp.linkedcontacts");
    }
    
    /**
     * Function to change FgCnNewsletterReceiverLog database entries
     */
//     public function FgCnNewsletterReceiverLog(){
//        $this->conn->exec(" UPDATE fg_cn_newsletter_receiver_log AS L
//                            INNER JOIN(
//                                SELECT id AS receiverlog, B.contacts AS contacts,A.linkedcontacts AS linkedcontacts  
//                                FROM fg_cn_newsletter_receiver_log d
//                                LEFT JOIN (
//                                     SELECT cnr.id as logid,GROUP_CONCAT( cb.id  SEPARATOR ',') AS contacts
//                                     FROM fg_cn_newsletter_receiver_log cnr
//                                     INNER JOIN fg_cm_contact cb ON FIND_IN_SET(cb.old_contact_id, cnr.contact_id) AND cnr.club_id = cb.club_id
//                                     INNER JOIN fg_club c on c.id = cnr.club_id
//                                     GROUP BY cnr.id) B on B.logid = d.id
//
//                                LEFT JOIN (
//                                     SELECT d.id as logid,GROUP_CONCAT( ca.id  SEPARATOR ',') AS linkedcontacts
//                                     FROM fg_cn_newsletter_receiver_log d
//                                     INNER JOIN fg_cm_contact ca ON FIND_IN_SET( ca.old_contact_id, d.linked_contact_ids) AND d.club_id = ca.club_id
//                                     INNER JOIN fg_club c on c.id = d.club_id
//                                     GROUP BY d.id) A on A.logid = d.id
//                            ) AS temp on temp.receiverlog = L.id
//                            SET L.contact_id = temp.contacts, L.linked_contact_ids = temp.linkedcontacts");
//     }
     
        private function writeLog($msg){
            fwrite($this->log, $msg);
            echo nl2br($msg);
        }
}
