<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FederationIconMigration
 *
 * @author jaikanth
 */
class FederationIconMigration {

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
        $this->log=fopen('migration_fedicon_log_'.date('dHis').'.txt','w');
    }
     /**
     * Function to init federation icon migration
     * @throws \Clubadmin\ContactBundle\Util\Exception
     */
    public function InitFederationIconMigration(){
        try {
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->beginTransaction();
            $this->moveFederationIcon();
            $this->writeLog("Moved federation icon to  admin/federation_icon Folder and save icon in club settings Table\n");

            $this->conn->commit();
        } catch (Exception $ex) {
            $this->conn->rollback();
            echo "Failed: " . $ex->getMessage();
            throw $ex;
        }
    }
    /**
     * Function to move federation icon migration
     */
    public function moveFederationIcon(){
    $fedClubId = $this->conn->query("SELECT id FROM fg_club WHERE (club_type='federation' OR club_type='sub_federation')");
    $fedClub = $fedClubId->fetchAll(\PDO::FETCH_COLUMN);
    $path = realpath(dirname(__FILE__).'/../');
     foreach ($fedClub as $key => $clubId) {
        $uploadPath = $path.'/uploads/'.$clubId.'/';  
        $destination = $path.'/uploads/' . $clubId . '/admin/federation_icon/';
        if (file_exists($uploadPath . 'club.png')) {
            rename($uploadPath . 'club.png', $destination . 'club.png');
            $this->conn->exec("UPDATE fg_club_settings SET federation_icon='club.png' WHERE club_id=".$clubId);
         }
      }
     
    }

    private function writeLog($msg){
        fwrite($this->log, $msg);
        echo nl2br($msg);
    }

}
