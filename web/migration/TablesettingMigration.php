<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TablesettingMigration.
 *
 * @author jinesh.m<jinesh.m@pitsolutions.com>
 */
class TablesettingMigration
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
        $this->log = fopen('migration_tablesetting_log_'.date('dHis').'.txt', 'w');
    }
    /**
     * Function to init table column setting function.
     *
     * @throws \Clubadmin\ContactBundle\Util\Exception
     */
    public function InitTablesettingMigration()
    {
        try {
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->beginTransaction();
            $this->updateContactTablesetting();
            $this->writeLog("Update TableSetting\n");

            $this->conn->commit();
        } catch (Exception $ex) {
            $this->conn->rollback();
            echo 'Failed: '.$ex->getMessage();
            throw $ex;
        }
    }
    /**
     * Function to update contact ids field in  fg_club_table_settings table and fg_table_settings table.
     */
    public function updateContactTablesetting()
    {
        // Query to update  $contact_id  in fg_club_table_setting
        $this->conn->exec('UPDATE fg_club_table_settings cts INNER JOIN fg_cm_contact c ON  c.old_contact_id = cts.contact_id SET cts.contact_id = c.id WHERE cts.club_id = c.club_id AND cts.contact_id !=1');
         // Query to update  $contact_id  in fg_table_setting
        $this->conn->exec('UPDATE fg_table_settings ts INNER JOIN fg_cm_contact c ON  c.old_contact_id = ts.contact_id SET ts.contact_id = c.id WHERE ts.club_id = c.club_id AND ts.contact_id !=1');
    }

    private function writeLog($msg)
    {
        fwrite($this->log, $msg);
        echo nl2br($msg);
    }
}
