<?php
use Common\UtilityBundle\Util\FgUtility;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PortraitMigration.
 *
 * @author snehaj
 */
class PortraitMigration {

    /**
     * $em.
     *
     * @var object entitymanager object
     */
    private $conn;
    private $log;
    private $resizewidth;

    /**
     * Constructor for initial setting.
     *
     * @param type $conn container
     */
    public function __construct($conn) {
        $this->conn = $conn;
        $this->log = fopen('migration_portraitpic_log_' . date('dHis') . '.txt', 'w');
    }

    /**
     * Function to init profile pic and contact filed  migration.
     *
     * @throws \Clubadmin\ContactBundle\Util\Exception
     */
    public function InitPortraitMigration() {
        try {
            $this->movePortraitPic();
            $this->writeLog("Moved profile pic/company logo to corresponding federation \n");
            
        } catch (Exception $ex) {
            echo 'Failed: ' . $ex->getMessage();
            throw $ex;
        }
    }
      /**
     * Function to move profile pic and contact filed  migration.
     *
     * @throws \Clubadmin\ContactBundle\Util\Exception
     */
    public function InitProfilePicMigration() {
        try {
            $this->moveProfilePic();
            $this->writeLog("Moved profile pic/company logo to corresponding federation \n");
            
        } catch (Exception $ex) {
            echo 'Failed: ' . $ex->getMessage();
            throw $ex;
        }
    }
    
    /**
     * Function to move profile pic from club to corresponding federation.
     */
    public function movePortraitPic() {
        $clubArray = $this->conn->query("SELECT id,federation_id FROM fg_club ");
        $clubDetails = $clubArray->fetchAll(\PDO::FETCH_ASSOC);
        $path = realpath(dirname(__FILE__) . '/../');
        foreach ($clubDetails as $key => $value) {
            // to move profile pic
            $src = $path . '/uploads/' . $value['id'] . '/admin/website_portrait';
            $dst1 = $path . '/uploads/' . $value['id'] . '/admin/website_portrait_580';
            $dst2 = $path . '/uploads/' . $value['id'] . '/admin/website_portrait_300';
            $this->createUploadDirectories($dst1);
            $this->createUploadDirectories($dst2);
            if (file_exists($src)) {
                $this->resizewidth = 580;
                $this->recurse_copy($src, $dst1);
                $this->resizewidth = 300;
                $this->recurse_copy($src, $dst2);
                
            }
            
        }
    }
    /**
     * Function to move profile pic from club to corresponding federation.
     */
    public function moveProfilePic() {
        $clubArray = $this->conn->query("SELECT id,federation_id FROM fg_club");
        $clubDetails = $clubArray->fetchAll(\PDO::FETCH_ASSOC);
        $path = realpath(dirname(__FILE__) . '/../');
       
        foreach ($clubDetails as $key => $value) {
            // to move profile pic
            $src = $path . '/uploads/' . $value['id'] . '/contact/profilepic/original';
            $dst1 = $path . '/uploads/' . $value['id'] . '/contact/profilepic/width_580';
            $dst2 = $path . '/uploads/' . $value['id'] . '/contact/profilepic/width_300';
            $this->createUploadDirectories($dst1);
            $this->createUploadDirectories($dst2);
            if (file_exists($src)) {
                $this->resizewidth = 580;
                $this->recurse_copy($src, $dst1);
                $this->resizewidth = 300;
                $this->recurse_copy($src, $dst2);
                
            }
            
        }
        foreach ($clubDetails as $key => $value) {
            // to move profile pic
            $src = $path . '/uploads/' . $value['id'] . '/contact/companylogo/original';
            $dst1 = $path . '/uploads/' . $value['id'] . '/contact/companylogo/width_580';
            $dst2 = $path . '/uploads/' . $value['id'] . '/contact/companylogo/width_300';
            $this->createUploadDirectories($dst1);
            $this->createUploadDirectories($dst2);
            if (file_exists($src)) {
                $this->resizewidth = 580;
                $this->recurse_copy($src, $dst1);
                $this->resizewidth = 300;
                $this->recurse_copy($src, $dst2);
                
            }
            
        }
    }
    
    /**
     * Function to copy profile pic/company logo files recursevly from club and creaty new directory/copy it to federation folders.
     *
     * @param string $src source directory
     * @param string $dst destination directory
     */
    private function recurse_copy($src, $dst ,$width ) {
        $dir = opendir($src);
        @mkdir($dst, 0700, true);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->recurse_copy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    $this->resizeImage($src . '/' . $file, $dst . '/' . $file, $this->resizewidth);
                    //rename($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
  
    

    /**
     * Function to resize image with given data with given data using shell command.
     *
     * @param string $imagePath Original image path
     * @param string $savePath  Save image path
     * @param int $width        Resize width
     * @param int $height       Resize height
     */
    public static function resizeImage($imagePath, $savePath, $width, $height = '')
    {
        list($orgWidth, $orgHeight, $type, $attr) = getimagesize($imagePath);
        if ($height == '') {
            $height = $orgHeight * ($width / $orgWidth);
        }
        if (end(explode('.', $imagePath)) == 'gif') {
            $tempFileName = substr($imagePath, 0, strrpos($imagePath, ".gif")) . "temp.gif";
            $importCommand = "gm convert '" . $imagePath . "' -coalesce '" . $tempFileName . "'";
            $importCommand .= "; gm convert -auto-orient -size " . $orgWidth . "x" . $orgHeight . " '" . $tempFileName . "' +dither -resize " . $width . "x" . $height . " '" . $savePath . "';";
            $importCommand .= " rm -f '" . $tempFileName . "'";
        } else {
            $importCommand = "gm convert -auto-orient '" . $imagePath . "' +dither -resize " . $width . 'x' . $height . " '" . $savePath . "' ";
        }
        $proc = popen($importCommand, 'w');
        pclose($proc);
        
    }
     /**
     * Function to create Image.
     *
     * @param string $imagePath Original image path
     * @param string $savePath  Save image path
     * @param int $width        Resize width
     * @param int $height       Resize height
     */
   public function createUploadDirectories($upload_path = null, $loopall = true)
    {
        if ($upload_path == null)
            return false;
        if ($loopall == true) {
            $upload_directories = explode('/', $upload_path);
            $createDirectory = array();
            foreach ($upload_directories as $upload_directory) {
                $createDirectory[] = $upload_directory;
                $createDirectoryPath = implode('/', $createDirectory);
                if (!is_dir($createDirectoryPath)) {
                    $old = umask(0);
                    mkdir($createDirectoryPath, 0700);
                }
            }
        } else {
            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0700);
            }
        }
        return true;
    }

    /**
     * Function to write log details.
     */
    private function writeLog($msg) {
        fwrite($this->log, $msg);
        echo nl2br($msg);
    }

}
