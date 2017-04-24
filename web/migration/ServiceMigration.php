<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FederationIconMigration
 *
 * @author rinu.rk
 */
class ServiceMigration {

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
        $this->log=fopen('service_migration_log_'.date('dHis').'.txt','w');
    }
     /**
     * Function to init federation icon migration
     * @throws \Clubadmin\ContactBundle\Util\Exception
     */
    public function InitServiceMigration(){
        try {
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->beginTransaction();
            $this->moveServiceBookMarks();
            $this->moveServiceLogs();
            $this->moveSponsorAds();
            $this->moveSponsorLogs();
            $this->moveSponsorBookings();
            $this->moveSmBookingsDeposited();
            $this->writeLog("service migration executed \n");
            $this->conn->commit();
        } catch (Exception $ex) {
            $this->conn->rollback();
            echo "Failed: " . $ex->getMessage();
            throw $ex;
        }
    }
    /**
     * Function to migrate fg_sm_bookmarks
     */
    private function moveServiceBookMarks(){
        $bookmarkDetailsQuery = $this->conn->query("SELECT BM.id, BM.contact_id as bm_contact, CN.id as contact FROM  fg_sm_bookmarks BM JOIN fg_cm_contact CN ON CN.old_contact_id = BM.contact_id AND BM.club_id = CN.club_id ");
        $bookmarkDetails = $bookmarkDetailsQuery->fetchAll();
        //echo "<pre>"; print_r($bookmarkDetails); exit;
        $this->writeLog("changes in fg_sm_bookmarks \n");
        foreach ($bookmarkDetails as $bookmarkDetail) { 
            if($bookmarkDetail['bm_contact'] != $bookmarkDetail['contact'] ) {
                $this->writeLog("'contact_id' changed from ".$bookmarkDetail['bm_contact']." => ".$bookmarkDetail['contact']." in id = ".$bookmarkDetail['id']." \n");
                $this->conn->exec("UPDATE fg_sm_bookmarks SET contact_id='".$bookmarkDetail['contact']."' WHERE id=".$bookmarkDetail['id']);
            }
        }
     
    }
    
    /**
     * Function to migrate fg_sm_services_log
     */
    private function moveServiceLogs(){
        //sponsor_id change in fg_sm_services_log
        $detailsQuery = $this->conn->query("SELECT SL.id, SL.sponsor_id as sl_contact, CN.id as contact FROM fg_sm_services_log SL JOIN fg_cm_contact CN ON CN.old_contact_id = SL.sponsor_id AND SL.club_id = CN.club_id ");
        $details = $detailsQuery->fetchAll();
        //echo "<pre>"; print_r($bookmarkDetails); exit;
        $this->writeLog("changes in fg_sm_services_log \n");
        foreach ($details as $detail) { 
            if($detail['sl_contact'] != $detail['contact'] ) {
                $this->writeLog("'sponsor_id' changed from ".$detail['sl_contact']." => ".$detail['contact']." in id = ".$detail['id']." \n");
                $this->conn->exec("UPDATE fg_sm_services_log SET sponsor_id='".$detail['contact']."' WHERE id=".$detail['id']);
            }
        }
        
        //changed_by change in fg_sm_services_log
        $detailsQuery = $this->conn->query("SELECT SL.id, SL.changed_by as sl_contact, CN.id as contact FROM fg_sm_services_log SL JOIN fg_cm_contact CN ON CN.old_contact_id = SL.changed_by AND SL.club_id = CN.club_id ");
        $details = $detailsQuery->fetchAll();
        $this->writeLog("changes 2 in fg_sm_services_log \n");
        foreach ($details as $detail) { 
            if($detail['sl_contact'] != $detail['contact'] ) {
                $this->writeLog("'changed_by' changed from ".$detail['sl_contact']." => ".$detail['contact']." in id = ".$detail['id']." \n");
                $this->conn->exec("UPDATE fg_sm_services_log SET changed_by='".$detail['contact']."' WHERE id=".$detail['id']);
            }
        }
     
    }
    
    /**
     * Function to migrate fg_sm_sponsor_ads
     */
    private function moveSponsorAds(){
        //contact_id change in fg_sm_sponsor_ads
        $detailsQuery = $this->conn->query("SELECT SL.id, SL.contact_id as sl_contact, CN.id as contact FROM fg_sm_sponsor_ads SL JOIN fg_cm_contact CN ON CN.old_contact_id = SL.contact_id AND SL.club_id = CN.club_id ");
        $details = $detailsQuery->fetchAll();
        //echo "<pre>"; print_r($bookmarkDetails); exit;
        $this->writeLog("changes in fg_sm_sponsor_ads \n");
        foreach ($details as $detail) { 
            if($detail['sl_contact'] != $detail['contact'] ) {
                $this->writeLog("'contact_id' changed from ".$detail['sl_contact']." => ".$detail['contact']." in id = ".$detail['id']." \n");
                $this->conn->exec("UPDATE fg_sm_sponsor_ads SET contact_id ='".$detail['contact']."' WHERE id = ".$detail['id']);
            }
        }     
    }
    
    /**
     * Function to migrate fg_sm_sponsor_log
     */
    private function moveSponsorLogs(){
        //contact_id change in fg_sm_sponsor_log
        $detailsQuery = $this->conn->query("SELECT SL.id, SL.contact_id as sl_contact, CN.id as contact FROM fg_sm_sponsor_log SL JOIN fg_cm_contact CN ON CN.old_contact_id = SL.contact_id AND SL.club_id = CN.club_id ");
        $details = $detailsQuery->fetchAll();
        //echo "<pre>"; print_r($bookmarkDetails); exit;
        $this->writeLog("changes in fg_sm_sponsor_log \n");
        foreach ($details as $detail) { 
            if($detail['sl_contact'] != $detail['contact'] ) {
                $this->writeLog("'contact_id' changed from ".$detail['sl_contact']." => ".$detail['contact']." in id = ".$detail['id']." \n");
                $this->conn->exec("UPDATE fg_sm_sponsor_log SET contact_id ='".$detail['contact']."' WHERE id = ".$detail['id']);
            }
        }  
        
        //changed_by change in fg_sm_sponsor_log
        $detailsQuery = $this->conn->query("SELECT SL.id, SL.changed_by as sl_contact, CN.id as contact FROM fg_sm_sponsor_log SL JOIN fg_cm_contact CN ON CN.old_contact_id = SL.changed_by AND SL.club_id = CN.club_id ");
        $details = $detailsQuery->fetchAll();
        //echo "<pre>"; print_r($bookmarkDetails); exit;
        $this->writeLog("changes 2 in fg_sm_sponsor_log \n");
        foreach ($details as $detail) { 
            if($detail['sl_contact'] != $detail['contact'] ) {
                $this->writeLog("'changed_by' changed from ".$detail['sl_contact']." => ".$detail['contact']." in id = ".$detail['id']." \n");
                $this->conn->exec("UPDATE fg_sm_sponsor_log SET changed_by ='".$detail['contact']."' WHERE id = ".$detail['id']);
            }
        }
    }
    
    /**
     * Function to migrate fg_sm_bookings
     */
    private function moveSponsorBookings(){
        //contact_id change in fg_sm_bookings
        $detailsQuery = $this->conn->query("SELECT SL.id, SL.contact_id as sl_contact, CN.id as contact FROM fg_sm_bookings SL JOIN fg_cm_contact CN ON CN.old_contact_id = SL.contact_id AND SL.club_id = CN.club_id ");
        $details = $detailsQuery->fetchAll();
        //echo "<pre>"; print_r($bookmarkDetails); exit;
        $this->writeLog("changes in fg_sm_bookings \n");
        foreach ($details as $detail) { 
            if($detail['sl_contact'] != $detail['contact'] ) {
                $this->writeLog("'contact_id' changed from ".$detail['sl_contact']." => ".$detail['contact']." in id = ".$detail['id']." \n");
                $this->conn->exec("UPDATE fg_sm_bookings SET contact_id ='".$detail['contact']."' WHERE id = ".$detail['id']);
            }
        }  
        
        //created_by change in fg_sm_bookings
        $detailsQuery = $this->conn->query("SELECT SL.id, SL.created_by as sl_contact, CN.id as contact FROM fg_sm_bookings SL JOIN fg_cm_contact CN ON CN.old_contact_id = SL.created_by AND SL.club_id = CN.club_id ");
        $details = $detailsQuery->fetchAll();
        //echo "<pre>"; print_r($bookmarkDetails); exit;
        $this->writeLog("changes 2 in fg_sm_bookings \n");
        foreach ($details as $detail) { 
            if($detail['sl_contact'] != $detail['contact'] ) {
                $this->writeLog("'created_by' changed from ".$detail['sl_contact']." => ".$detail['contact']." in id = ".$detail['id']." \n");
                $this->conn->exec("UPDATE fg_sm_bookings SET created_by ='".$detail['contact']."' WHERE id = ".$detail['id']);
            }
        }
        
        //updated_by change in fg_sm_bookings
        $detailsQuery = $this->conn->query("SELECT SL.id, SL.updated_by as sl_contact, CN.id as contact FROM fg_sm_bookings SL JOIN fg_cm_contact CN ON CN.old_contact_id = SL.updated_by AND SL.club_id = CN.club_id ");
        $details = $detailsQuery->fetchAll();
        //echo "<pre>"; print_r($bookmarkDetails); exit;
        $this->writeLog("changes 3 in fg_sm_bookings \n");        
        foreach ($details as $detail) { 
            if($detail['sl_contact'] != $detail['contact'] ) {
                $this->writeLog("'updated_by' changed from ".$detail['sl_contact']." => ".$detail['contact']." in id = ".$detail['id']." \n");
                $this->conn->exec("UPDATE fg_sm_bookings SET updated_by ='".$detail['contact']."' WHERE id = ".$detail['id']);
            } 
        }
    }
    
    /**
     * Function to migrate fg_sm_booking_deposited
     */
    private function moveSmBookingsDeposited(){
        //contact_id change in fg_sm_sponsor_ads
        $detailsQuery = $this->conn->query("SELECT SL.id, SL.contact_id as sl_contact, CN.id as contact FROM fg_sm_booking_deposited SL "
                . "JOIN fg_sm_bookings SB ON SB.id = SL.booking_id "
                . "JOIN fg_cm_contact CN ON CN.old_contact_id = SL.contact_id AND SB.club_id = CN.club_id ");
        $details = $detailsQuery->fetchAll();        
        $this->writeLog("changes in fg_sm_booking_deposited \n");
        foreach ($details as $detail) { 
            if($detail['sl_contact'] != $detail['contact'] ) {
                $this->writeLog("'contact_id' changed from ".$detail['sl_contact']." => ".$detail['contact']." in id = ".$detail['id']." \n");
                $this->conn->exec("UPDATE fg_sm_booking_deposited SET contact_id ='".$detail['contact']."' WHERE id = ".$detail['id']);
            }
        }     
    }         
    
    private function writeLog($msg){
        fwrite($this->log, $msg);
        echo nl2br($msg);
    }

}
