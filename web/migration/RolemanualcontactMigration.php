<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RolemanualcontactMigration.
 *
 * @author jinesh.m
 */
class RolemanualcontactMigration
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
        $this->log = fopen('migration_manualcontact_log_'.date('dHis').'.txt', 'w');
    }
    /**
     * Function to init role manual contact function.
     *
     * @throws \Clubadmin\ContactBundle\Util\Exception
     */
    public function InitManualcontactMigration()
    {
        try {
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->beginTransaction();
            $this->updateManualcontactTablesetting();
            $this->writeLog("Update role manual contacts\n");

            $this->conn->commit();
        } catch (Exception $ex) {
            $this->conn->rollback();
            echo 'Failed: '.$ex->getMessage();
            throw $ex;
        }
    }
    /**
     * Function to update contact ids field in  fg_rm_role_manual_contacts table .
     */
    public function updateManualcontactTablesetting()
    {
        $this->conn->exec('UPDATE fg_rm_role_manual_contacts rmc
                INNER JOIN fg_rm_role r ON r.id = rmc.role_id AND r.filter_id IS NOT NULL
                INNER JOIN fg_cm_contact c ON c.old_contact_id = rmc.contact_id AND rmc.contact_id !=1
                SET rmc.contact_id = c.id WHERE r.club_id = c.club_id');
    }

    private function writeLog($msg)
    {
        fwrite($this->log, $msg);
        echo nl2br($msg);
    }
}
