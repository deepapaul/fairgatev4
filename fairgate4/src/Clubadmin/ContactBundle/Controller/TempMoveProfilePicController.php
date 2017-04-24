<?php

/**
 * TempMoveProfilePicController
 */
namespace Clubadmin\ContactBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Common\UtilityBundle\Util\FgUtility;

/**
 * TempMoveProfilePicController - move already uploaded profile pictures to width_65 folder and resize them  according to FAIR 1996
 *  
 * @package         TempMoveProfilePicController
 * @subpackage      controller
 * @author          pitsolutions.ch
 * @version         Fairgate V4
 */
class TempMoveProfilePicController extends Controller
{

    /**
     * Method to move profile pictures from original folder to width_65 folders for contacts
     */
    public function moveToFolderAction()
    {
        $DS = DIRECTORY_SEPARATOR;
        set_time_limit(0);

        //write log file
        file_put_contents('LogMigrateContactProfilePic.txt', "Migrate contact profile pictures from original folder to width_65 folder and resize them\n\n", FILE_APPEND);

        $this->conn = $this->container->get("database_connection");

        //get profile picture names and club Id from db
        $images = $this->conn->fetchAll("SELECT MS.`21` as imageName, C.club_id as clubId  FROM `master_system` MS INNER JOIN fg_cm_contact C "
            . "ON MS.fed_contact_id = C.id WHERE MS.`21` IS NOT NULL AND C.club_id != 1; "); 
        foreach ($images as $image) {

            $source_dir = getcwd() . $DS . "uploads" . $DS . $image['clubId'] . $DS . 'contact' . $DS . 'profilepic' . $DS . 'original';
            $destination_dir1 = getcwd() . $DS . "uploads" . $DS . $image['clubId'] . $DS . 'contact' . $DS . 'profilepic' . $DS . 'width_65';
            if (!is_dir($destination_dir1)) {
                //make width_65 directory, if not exist
                mkdir($destination_dir1, 0777, true);
            }
            if ((file_exists($source_dir . $DS . $image['imageName'])) &&
                (!file_exists($destination_dir1 . $DS . $image['imageName'])) &&
                (!empty($image['imageName']))) {
                
                //copy files to width_65 folder
                copy($source_dir . $DS . $image['imageName'], $destination_dir1 . $DS . $image['imageName']);

                //write log
                $log = "copied  " . $source_dir . $DS . $image['imageName'] . '   to   ' . $destination_dir1 . $DS . $image['imageName'];
                echo "<br /><br />" . $log . "<br /> ";
                file_put_contents('LogMigrateContactProfilePic.txt', $log . "\n\n", FILE_APPEND);

                //resize image in width 65 according to mrule in 1996
                $imagePath = $destination_dir1 . $DS . $image['imageName'];
                list($width, $height) = getimagesize($imagePath);
                FgUtility::resizeFolderImages($imagePath, $imagePath, $width, $height, 65, 45);
            }
        }

        $this->deleteUnusedFolders();
        exit;
        return true;
    }
    
    /**
     * Method to delete unused folders (thumbnails, width_36, width_130) in each club.
     * This function will executing later
     */
    private function deleteUnusedFolders() {
        $DS = DIRECTORY_SEPARATOR;
        //get all club Ids containing profile pics from db
        $clubs = $this->conn->fetchAll("SELECT C.club_id as clubId  FROM `master_system` MS INNER JOIN fg_cm_contact C "
            . "ON MS.fed_contact_id = C.id WHERE C.club_id != 1 AND C.club_id = 608 GROUP BY C.club_id "); //XXXXXXXXXX delete 608 condition
        foreach ($clubs as $club) {
            $profilepicDir = getcwd() . $DS . "uploads" . $DS . $club['clubId'] . $DS . 'contact' . $DS . 'profilepic' . $DS ;
            
            //delete thumbnails from profilepic
            if (is_dir($profilepicDir.'thumbnails')) {               
                exec('rm -rf ' . escapeshellarg($profilepicDir.'thumbnails'));
            } 
            //delete width_36 from profilepic
            if (is_dir($profilepicDir.'width_36')) {               
                exec('rm -rf ' . escapeshellarg($profilepicDir.'width_36'));
            } 
            //delete width_130 from profilepic
            if (is_dir($profilepicDir.'width_130')) {               
                exec('rm -rf ' . escapeshellarg($profilepicDir.'width_130'));
            } 
            
            $companylogoDir = getcwd() . $DS . "uploads" . $DS . $club['clubId'] . $DS . 'contact' . $DS . 'companylogo' . $DS ;
            
            //delete thumbnails from companylogo
            if (is_dir($companylogoDir.'thumbnails')) {               
                exec('rm -rf ' . escapeshellarg($companylogoDir.'thumbnails'));
            } 
            //delete width_36 from companylogo
            if (is_dir($companylogoDir.'width_36')) {               
                exec('rm -rf ' . escapeshellarg($companylogoDir.'width_36'));
            } 
            //delete width_130 from companylogo
            if (is_dir($companylogoDir.'width_130')) {               
                exec('rm -rf ' . escapeshellarg($companylogoDir.'width_130'));
            } 
        }
    }
    
    
}
