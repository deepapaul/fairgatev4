<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ProfilepicMigration.
 *
 * @author jaikanth
 */
class ProfilepicMigration {

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
    public function __construct($conn) {
        $this->conn = $conn;
        $this->log = fopen('migration_profilepic_log_' . date('dHis') . '.txt', 'w');
    }

    /**
     * Function to init profile pic and contact filed  migration.
     *
     * @throws \Clubadmin\ContactBundle\Util\Exception
     */
    public function InitProfilePicMigration() {
        try {
            $this->moveProfilePic();
            $this->writeLog("Moved profile pic/company logo to corresponding federation \n");
            $this->moveContactField();
            $this->writeLog("Moved contact fields to corresponding created clubs \n");
        } catch (Exception $ex) {
            echo 'Failed: ' . $ex->getMessage();
            throw $ex;
        }
    }

    /**
     * Function to move profile pic from club to corresponding federation.
     */
    public function moveProfilePic() {
        $clubArray = $this->conn->query("SELECT id,federation_id FROM fg_club WHERE club_type IN ('federation_club','sub_federation','sub_federation_club')");
        $clubDetails = $clubArray->fetchAll(\PDO::FETCH_ASSOC);
        $path = realpath(dirname(__FILE__) . '/../');
        foreach ($clubDetails as $key => $value) {
            // to move profile pic
            $src = $path . '/uploads/' . $value['id'] . '/contact/profilepic';
            $dst = $path . '/uploads/' . $value['federation_id'] . '/contact/profilepic';
            if (file_exists($src)) {
                $this->recurse_copy($src, $dst);
            }
            // to move company logo  
            $src = $path . '/uploads/' . $value['id'] . '/contact/companylogo';
            $dst = $path . '/uploads/' . $value['federation_id'] . '/contact/companylogo';
            if (file_exists($src)) {
                $this->recurse_copy($src, $dst);
            }
        }
    }

    /**
     * Function to copy profile pic/company logo files recursevly from club and creaty new directory/copy it to federation folders.
     *
     * @param string $src source directory
     * @param string $dst destination directory
     */
    private function recurse_copy($src, $dst) {
        $dir = opendir($src);
        @mkdir($dst, 0700, true);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->recurse_copy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    rename($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    /**
     * Function to move contact field from club to corresponding federation.
     */
    public function moveContactField() {
        $clubId = $this->conn->query("SELECT ci.id FROM fg_club ci WHERE ci.club_type IN ('federation','sub_federation')");
        $clubIds = $clubId->fetchAll(\PDO::FETCH_COLUMN);
        $attributeArray = array();
        $path = realpath(dirname(__FILE__) . '/../');
        foreach ($clubIds as $keys => $clbid) {
            $attribute = $this->conn->query('SELECT a.id AS attrId,a.input_type FROM fg_cm_attribute a WHERE a.club_id = ' . $clbid . ' AND a.input_type IN ("imageupload","fileupload")');
            $attributeArray = $attribute->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($attributeArray as $keys => $value) {
                $location = ($value['input_type'] == 'imageupload') ? 'contactfield_image' : 'contactfield_file';
                $query = 'SELECT `' . $value['attrId'] . '`,club_id FROM master_federation_' . $clbid .
                        ' WHERE `' . $value['attrId'] . '` IS NOT NULL AND `' . $value['attrId'] . '` != "" AND club_id != ' . $clbid;
                $files = $this->conn->query($query);
                $filesList = $files->fetchAll(\PDO::FETCH_ASSOC);
                foreach ($filesList as $file) {
                    $src = $path . '/uploads/' . $file['club_id'] . '/contact/' . $location . '/' . $file[$value['attrId']];
                    $dst = $path . '/uploads/' . $clbid . '/contact/' . $location . '/' . $file[$value['attrId']];
                    $destination = $path . '/uploads/' . $clbid . '/contact/' . $location . '/';
                    if (!is_dir($destination)) {
                        @mkdir($destination, 0700, true);
                    }
                    if (file_exists($src)) {
                        rename($src, $dst);
                    }
                }
            }
        }
    }

    /**
     * Function to write log details.
     */
    private function writeLog($msg) {
        fwrite($this->log, $msg);
        echo nl2br($msg);
    }

}
