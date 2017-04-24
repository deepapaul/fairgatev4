<?php

/*
 *  Membership related migration
 */

class membershipRelatedMigration {
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
     * @param object $conn   container
     */
    public function __construct($conn){
        $this->conn = $conn;
        $this->log = fopen('membership_related_migration_'.date('dH').'.txt','w');
    }

    public function initMigration(){

        try {
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->beginTransaction();
            $this->membershipLogInit();
            $this->membershipHistoryInit();
            $this->lastJoiningLeavingDate();
            $this->firstJoiningDate();
            $this->conn->commit();
        } catch (Exception $ex) {
            $this->conn->rollback();
            echo "Failed: " . $ex->getMessage();
            throw $ex;
        }

    }

    /**
     * UPDATED MEMBERSHIP LOG TABLE- CONTACT_ID's WITH resp FED_CONTACT's IN CASE OF FED MEMBERSHIP ENTRIES
     */
    public function membershipLogInit(){

        $this->conn->exec("UPDATE fg_cm_membership_log fcml "
                . "INNER JOIN fg_cm_membership m ON m.id = fcml.membership_id "
                . "INNER JOIN fg_club fc ON m.club_id = fc.id "
                . "INNER JOIN fg_cm_contact c ON fcml.contact_id = c.old_contact_id AND c.club_id = fc.id "
                . "SET fcml.contact_id= c.fed_contact_id "
                . "WHERE fc.club_type = 'federation'");

        $this->writeLog("UPDATED MEMBERSHIP LOG TABLE- CONTACT_ID's WITH resp FED_CONTACT's IN CASE OF FED MEMBERSHIP ENTRIES \n");
    }

    /**
     * UPDATED MEMBERSHIP HISTROY TABLE- CONTACT_ID's WITH resp FED_CONTACT's IN CASE OF FED MEMBERSHIP ENTRIES
     *
     */
    public function membershipHistoryInit(){
         $this->conn->exec("UPDATE fg_cm_membership_history fcmh "
                . "INNER JOIN fg_cm_membership m ON m.id = fcmh.membership_id "
                . "INNER JOIN fg_club fc ON m.club_id = fc.id "
                . "INNER JOIN fg_cm_contact c ON fcmh.contact_id = c.old_contact_id AND c.club_id = fc.id "
                . "SET fcmh.contact_id= c.fed_contact_id,fcmh.membership_type = 'federation' "
                . "WHERE fc.club_type = 'federation'");

        $this->writeLog("UPDATED MEMBERSHIP HISTROY TABLE- CONTACT_ID's WITH resp FED_CONTACT's IN CASE OF FED MEMBERSHIP ENTRIES \n");
    }

    /**
     * UPDATED FG_CM_CONTACT TABLE- leaving date,joining date WITH resp leaving, joining date
     */
    public function lastJoiningLeavingDate(){
         $this->conn->exec("UPDATE fg_cm_contact c
                INNER JOIN fg_cm_membership_history fcmh ON c.id = fcmh.contact_id  AND fcmh.id IN
                  (select id FROM fg_cm_membership_history mh
                    WHERE mh.contact_id = fcmh.contact_id and mh.joining_date =
                    (select MAX(joining_date) FROM fg_cm_membership_history WHERE contact_id = mh.contact_id))
                INNER JOIN fg_cm_membership m ON m.id = fcmh.membership_id
                INNER JOIN fg_club fc ON fc.id = m.club_id
                SET c.joining_date = fcmh.joining_date,c.leaving_date = fcmh.leaving_date"
               );

         $this->writeLog("UPDATED FG_CM_CONTACT TABLE- leaving date,joining date WITH resp leaving, joining date \n");
    }

    /**
     * updating First joining date of contacts with membership
     */
    public function firstJoiningDate(){

         $this->conn->exec("UPDATE fg_cm_contact c
                INNER JOIN fg_cm_membership_history fcmh ON c.id = fcmh.contact_id  AND fcmh.id IN
                  (select id FROM fg_cm_membership_history mh
                    WHERE mh.contact_id = fcmh.contact_id and mh.joining_date =
                    (select MIN(joining_date) FROM fg_cm_membership_history WHERE contact_id = mh.contact_id))
                INNER JOIN fg_cm_membership m ON m.id = fcmh.membership_id
                INNER JOIN fg_club fc ON fc.id = m.club_id
                SET c.first_joining_date = fcmh.joining_date"
               );
         $this->writeLog("updating First joining date of contacts with membership \n");

    }

    private function writeLog($msg){
        fwrite($this->log, $msg);
        echo nl2br($msg);
    }


}