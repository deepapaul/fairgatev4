<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TablefilterMigration.
 *
 * @author jinesh.m
 */
class TablefilterMigration
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
     * @param type $conn container
     */
    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->log = fopen('migration_filtersetting_log_'.date('dHis').'.txt', 'w');
    }
    /**
     * Function to init filter table migration.
     *
     * @throws \Clubadmin\ContactBundle\Util\Exception
     */
    public function InitFilterMigration()
    {
        try {
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->beginTransaction();
            $this->updateFilterTable();
            $this->writeLog("Update Filter table\n");
            $this->conn->commit();
        } catch (Exception $ex) {
            $this->conn->rollback();
            echo 'Failed: '.$ex->getMessage();
            throw $ex;
        }
    }
    /**
     * Function to update contact ids field in fg_filter table and fg_club_filter table.
     */
    public function updateFilterTable()
    {
        // Query to update  $contact_id  in fg_club_table_setting
        $this->conn->exec('UPDATE fg_filter ff INNER JOIN fg_cm_contact c ON  c.old_contact_id = ff.contact_id SET ff.contact_id = c.id WHERE ff.club_id = c.club_id AND ff.contact_id !=1');
         // Query to update  $contact_id  in fg_table_setting
        $this->conn->exec('UPDATE fg_club_filter cf INNER JOIN fg_cm_contact c ON  c.old_contact_id = cf.contact_id SET cf.contact_id = c.id WHERE cf.club_id = c.club_id AND cf.contact_id !=1');
    }

    private function writeLog($msg)
    {
        fwrite($this->log, $msg);
        echo nl2br($msg);
    }
}
