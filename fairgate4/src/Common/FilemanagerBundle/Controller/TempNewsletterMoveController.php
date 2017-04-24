<?php

namespace Common\FilemanagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Common\FilemanagerBundle\Util\FileChecking;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Newsletter File migration class
 */
class TempNewsletterMoveController extends Controller
{
    private $conn;
    /**
     * Newsletter File migration
     */
    
    public function galleryResizingAction(){
        $DS=DIRECTORY_SEPARATOR;
        set_time_limit(0);
        //$log=fopen('upload_refactering_newsletter_log_'.rand(1,1000).'.txt','w');
        //fwrite($log, "=========================================\n");
        
        $this->conn=$this->container->get("database_connection");
        $files = $this->conn->fetchAll("SELECT * FROM `fg_gm_items` WHERE `source` != 'gallery' ");
        foreach ($files as $file){
            if(!empty($file['filepath'])){
                if(file_exists(getcwd().$DS."uploads".$DS.$file['club_id'].$DS.'gallery'.$DS.'original'.$DS.$file['filepath'])){                   
                    $source_dir=getcwd().$DS."uploads".$DS.$file['club_id'].$DS.'gallery'.$DS.'original';
                    $destination_dir1=getcwd().$DS."uploads".$DS.$file['club_id'].$DS.'gallery'.$DS.'width_100';
                    copy($source_dir.$DS.$file['filepath'], $destination_dir1.$DS.$file['filepath']);
                    $destination_dir2=getcwd().$DS."uploads".$DS.$file['club_id'].$DS.'gallery'.$DS.'width_1920';
                    copy($source_dir.$DS.$file['filepath'], $destination_dir2.$DS.$file['filepath']);
                    $destination_dir3=getcwd().$DS."uploads".$DS.$file['club_id'].$DS.'gallery'.$DS.'width_300';
                    copy($source_dir.$DS.$file['filepath'], $destination_dir3.$DS.$file['filepath']);
                    echo "copied  ".$source_dir.$DS.$file['filepath'].", ". $destination_dir3.$DS.$file['filepath'] ."<br />";
                    
                    //echo $file['club_id']."/".$file['filepath']."<br />" ;
                } 
            }
        }
                
        exit;
    }
    
    public function changeFileStructureAction(){
        $DS=DIRECTORY_SEPARATOR;
        set_time_limit(0);
        $log=fopen('upload_refactering_newsletter_log_'.rand(1,1000).'.txt','w');
        fwrite($log, "=========================================\n");
        
        $this->conn=$this->container->get("database_connection");
        $clubs=$this->conn->fetchAll("SELECT C.*,CLS.logo FROM fg_club C LEFT JOIN fg_club_settings CLS ON CLS.club_id=C.id WHERE C.id!=1");
        foreach ($clubs as $club){
            //moving newsletter files
            $newsLetters = $this->conn->fetchAll("SELECT N.id,NC.id as NC_id,NC.image_path,N.status,N.newsletter_type,NAM.id as NAM_id,NAM.media_text,NAD.id as NAD_id,NAD.filename FROM fg_cn_newsletter N "
                    . "LEFT JOIN fg_cn_newsletter_content NC ON NC.newsletter_id=N.id AND NC.content_type in('IMAGE','ARTICLE') "
                    . "LEFT JOIN fg_cn_newsletter_article NA ON NC.id=NA.content_id "
                    . "LEFT JOIN fg_cn_newsletter_article_media NAM ON NAM.article_id=NA.id AND NAM.media_type='image' "
                    . "LEFT JOIN fg_cn_newsletter_article_documents NAD ON NAD.newsletter_id=N.id "
                    . "WHERE N.club_id={$club['id']} AND (NC.image_path IS NOT NULL OR NAM.media_text IS NOT NULL OR NAD.filename IS NOT NULL)");
            $txt="MOVING NEWSLETTER CLUB {$club['id']} STARTS\n";
            fwrite($log, $txt);
            echo nl2br($txt);
            if (!is_dir("uploads".$DS.$club['id'].$DS.'content')) {
                mkdir("uploads".$DS.$club['id'].$DS.'content', 0700,true);
            }
            
            foreach ($newsLetters as $newsLetter){
                if(!empty($newsLetter['image_path'])){
                    if(file_exists(getcwd().$DS."uploads".$DS.$club['id'].$DS.'communication'.$DS.$newsLetter['image_path'])){
                        $txt="\tMOVING FULL WIDTH IMAGE {$newsLetter['id']}: {$newsLetter['image_path']}\n";
                        fwrite($log, $txt);
                        echo nl2br($txt);
                        $galleryId = $this->insertToGallery($newsLetter['image_path'],"uploads".$DS.$club['id'].$DS.'communication',$club['id'], 'newsletter-image');
                        $this->updateGalleryId($newsLetter,$galleryId,'image_path');
                    }
                }
                if(!empty($newsLetter['media_text'])){
                    if(file_exists(getcwd().$DS."uploads".$DS.$club['id'].$DS.'communication'.$DS.'article_images'.$DS.'original'.$DS.$newsLetter['media_text'])){
                        $txt="\tMOVING ARTICLE {$newsLetter['id']}: {$newsLetter['media_text']}\n";
                        fwrite($log, $txt);
                        echo nl2br($txt);
                        $galleryId = $this->insertToGallery($newsLetter['media_text'],"uploads".$DS.$club['id'].$DS.'communication'.$DS.'article_images'.$DS.'original',$club['id'], 'newsletter-articleimage');
                        $this->updateGalleryId($newsLetter,$galleryId,'media_text');
                    }
                }
                if(!empty($newsLetter['filename']) && $newsLetter['newsletter_type']=='EMAIL'){
                    if(file_exists(getcwd().$DS."uploads".$DS.$club['id'].$DS.'communication'.$DS.'documents'.$DS.$newsLetter['filename'])){
                        $txt="\tMOVING ATTACHMENT {$newsLetter['id']}: {$newsLetter['filename']}\n";
                        fwrite($log, $txt);
                        echo nl2br($txt);
                        $path=$club['id'].'/communication/documents/'.addslashes($newsLetter['filename']);
                        $filemanId=$this->insertFileMan($newsLetter['filename'],"uploads".$DS.$club['id'].$DS.'communication'.$DS.'documents',$club['id'],$path);
                        $this->updateFileManId($newsLetter,$filemanId,'DOC',$path);
                    }
                }
            }
            
            //moving newsletter header starts
            $newHeaders = $this->conn->fetchAll("SELECT header_image,club_id FROM `fg_cn_newsletter_template` where club_id={$club['id']} AND header_image IS NOT NULL AND header_image!=''");           
            $txt="MOVING NEWSLETTER HEADER STARTS\n";
            fwrite($log, $txt);
            echo nl2br($txt);
            if (!is_dir("uploads".$DS.$club['id'].$DS.'admin'.$DS.'newsletter_header')) {
                mkdir("uploads".$DS.$club['id'].$DS.'admin'.$DS.'newsletter_header', 0700,true);
            }
            foreach ($newHeaders as $newHeader){
                if(!empty($newHeader['header_image'])){
                    if(file_exists(getcwd().$DS."uploads".$DS.$club['id'].$DS.'communication'.$DS.$newHeader['header_image'])){
                        $txt="MOVING NEWSLETTER HEADER {$newHeader['header_image']}\n";
                        fwrite($log, $txt);
                        echo nl2br($txt);
                        $source_dir=getcwd().$DS."uploads".$DS.$club['id'].$DS.'communication';
                        $destination_dir=getcwd().$DS."uploads".$DS.$club['id'].$DS.'admin'.$DS.'newsletter_header';
                        $this->moveFile($source_dir, $destination_dir, $newHeader['header_image']);
                    } 
                }
            }
            $txt="MOVING NEWSLETTER HEADER ENDS\n";
            fwrite($log, $txt);
            echo nl2br($txt);
            //moving newsletter header ends
            
            rename(getcwd().$DS."uploads".$DS.$club['id'].$DS.'communication', getcwd().$DS."uploads".$DS.$club['id'].$DS.'communications');
            foreach(glob(getcwd().$DS."uploads".$DS.$club['id'].$DS.'communications'.$DS.'*.*') as $file){
                unlink($file);
            }
            if (!is_dir(getcwd().$DS."uploads".$DS.$club['id'].$DS.'gallery'.$DS.'171X114')) {
                mkdir(getcwd().$DS."uploads".$DS.$club['id'].$DS.'gallery'.$DS.'171X114', 0777, true);
            }
            foreach(glob(getcwd().$DS."uploads".$DS.$club['id'].$DS.'communications'.$DS.'article_images'.$DS.'171X114'.$DS.'*.*') as $file){
                $path_parts = pathinfo($file);
                $filename = $path_parts['basename'];
                copy($file, getcwd().$DS."uploads".$DS.$club['id'].$DS.'gallery'.$DS.'171X114'.$DS.$filename);
                unlink($file);
            }
            
            chmod(getcwd().$DS."uploads".$DS.$club['id'].$DS.'communications', 0777);
            if(is_dir('uploads'.$DS.$club['id'].$DS.'communications')){
                system("rm -rf ".escapeshellarg("uploads".$DS.$club['id'].$DS.'communications'));
            }          
            //delete communication directory            
            rmdir(getcwd().$DS."uploads".$DS.$club['id'].$DS.'communications');
                    
                    
            $txt="MOVING NEWSLETTER CLUB {$club['id']} ENDS\n";
            fwrite($log, $txt);
            echo nl2br($txt);
            
            
        }
                
        exit;
    }
    
    private function moveFile($source_dir,$destination_dir,$file){
        $DS=DIRECTORY_SEPARATOR;
        if (!file_exists($destination_dir.DS.$file)){
            // destination is not a source's subfolder?
            if(strpos($destination_dir.$DS.$file, $source_dir.$DS.$file.$DS) !== false){
                exit("destination is not a sources subfolder");
            }
            rename($source_dir.$DS.$file, $destination_dir.$DS.$file);
        }
    }
    /**
     * Function to update filemanager id for draft and update temp table for sent newsletters
     * @param String $newsLetter
     * @param Int    $filemanId
     * @param String $fileType
     * @param String $path
     */
    private function updateFileManId($newsLetter,$filemanId,$fileType,$path){
        $DS=DIRECTORY_SEPARATOR;
        switch($fileType){
            case 'DOC':
                $fManLogQuery="UPDATE fg_cn_newsletter_article_documents SET file_manager_id=$filemanId WHERE id={$newsLetter['NAD_id']} ";
                $this->conn->executeQuery($fManLogQuery);
                break;
        }
        if($newsLetter['status']!='draft'){
            $fMansQuery="INSERT INTO fg_cn_newsletter_file_map (file_pattern,file_manager_id) VALUES('$path','$filemanId')";
            $this->conn->executeQuery($fMansQuery);
        }
    }
    /**
     * Function to insert filemanager entry.
     * 
     * @param string $fileName
     * @param string $source
     * @param int $clubId
     * @return int
     */
    private function insertFileMan($fileName,$source,$clubId){
        $DS=DIRECTORY_SEPARATOR;
        $filepath = 'uploads'.$DS.$clubId.$DS.'content'.$DS;
        $fileCheck = new FileChecking($this->container);
        $fileCheck->filename = mt_rand(9999, 999999). $fileName;       
        $shaFilename = $fileCheck->sshNameConvertion();
        $fileCheck->filepath=$source.$DS;
        $fileCheck->filename=$fileName;
        $mmType = $fileCheck->get_mime_type();
        $mimeType = explode(';', $mmType);
        $attachmentObj = new File($source.$DS.$fileName, false);
        $attachmentObj->move($filepath, $shaFilename);
        $size= filesize($source.$DS.$fileName);
        //insert into FgFileManagerexit;
        $randFileName=  md5(time().rand(1000, 9999));
        $fileName=addslashes($fileName);
        $fManQuery="INSERT INTO fg_file_manager (club_id,virtual_filename,encrypted_filename,is_removed,source)VALUES($clubId,'$randFileName','$shaFilename','0','NEWSLETTER')";
        $this->conn->executeQuery($fManQuery);
        $filemanId = $this->conn->lastInsertId();
        $fManVerQuery="INSERT INTO fg_file_manager_version(filename,file_manager_id,`size`,uploaded_at,updated_at,uploaded_by,updated_by,mime_type) VALUES('$fileName',$filemanId,'$size',NOW(),NOW(),1,1,'{$mimeType[0]}')";
        $this->conn->executeQuery($fManVerQuery);
        $filemanVerId = $this->conn->lastInsertId();
        $this->conn->executeQuery("UPDATE fg_file_manager SET latest_version_id='$filemanVerId' WHERE id='$filemanId'");
        $fManLogQuery="INSERT INTO fg_file_manager_log(file_manager_id,kind,field,changed_by,value_after,date) VALUES($filemanId,'Added','file added',1,'$fileName',NOW())";
        $this->conn->executeQuery($fManLogQuery);
        
        return $filemanId;

    }
    
    /**
     * Function to insert gallery entry.
     * 
     * @param string $fileName
     * @param string $source
     * @param int $clubId
     * @return int
     */
    private function insertToGallery($fileName,$source,$clubId, $type){
        $DS=DIRECTORY_SEPARATOR;        
        $size= filesize($source.$DS.$fileName);
        
        $fileCheck = new FileChecking($this->container);
        $fileCheck->filepath=$source.$DS;
        $fileCheck->filename=$fileName;
        $mmType = $fileCheck->get_mime_type();
        $mimeType = explode(';', $mmType);
        
        $filepath = 'uploads'.$DS.$clubId.$DS.'gallery'.$DS.'original'.$DS; 
        $attachmentObj = new File($source.$DS.$fileName, false);
        $attachmentObj->move($filepath, $fileName);   
        
        //insert into gallery
        $galQuery="INSERT INTO fg_gm_items "
                . "(club_id, scope, type, filepath, file_name, mime_type, file_size, created_by, updated_by, created_on, updated_on, source) VALUES "
                . "($clubId, 'PUBLIC', 'IMAGE', '$fileName', '$fileName', '$mimeType', '$size', '1', '1', NOW(), NOW(), '$type')";
        $this->conn->executeQuery($galQuery);
        $galleryId = $this->conn->lastInsertId();
        
        return $galleryId;        

    }
        
    /**
     * Function to update gallery id for draft and update temp table for sent newsletters
     * @param String $newsLetter
     * @param Int    $galleryId
     * @param String $fileType
     */
    private function updateGalleryId($newsLetter,$galleryId,$fileType){
        switch($fileType){
            case 'image_path':
                $query1="UPDATE fg_cn_newsletter_content SET items_id=$galleryId WHERE id={$newsLetter['NC_id']} ";
                $this->conn->executeQuery($query1);
                break;
            case 'media_text':
                $query2="UPDATE fg_cn_newsletter_article_media SET gallery_item_id = $galleryId WHERE id={$newsLetter['NAM_id']} ";
                $this->conn->executeQuery($query2);
                break;            
        }
        
    }

}
