<?php

namespace Common\FilemanagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class TempFileMoveController extends Controller
{
    private $conn;
    
    public function changeFileStructureAction(){
        $DS=DIRECTORY_SEPARATOR;
        set_time_limit(0);
        $log=fopen('upload_refactering_log.txt','w');
        fwrite($log, "======================================================");
        $this->conn=$this->container->get("database_connection");
        $clubs=$this->conn->fetchAll("SELECT C.*,CLS.logo FROM fg_club C LEFT JOIN fg_club_settings CLS ON CLS.club_id=C.id WHERE C.id!=1");
        $profilePicture = $this->container->getParameter('system_field_communitypicture');
        $companyLogo = $this->container->getParameter('system_field_companylogo');
        foreach ($clubs as $club){
            //contact module moving starts
            $txt="REFACTERING CLUB ID {$club['id']} STARTS\n";
            fwrite($log, $txt);
            echo nl2br($txt);
            $clubTable = ($club['club_type']=='federation'||$club['club_type']=='sub_federation') ? "master_federation_{$club['id']}":"master_club_{$club['id']}"; 
            $fileFields =$this->conn->fetchAll("SELECT GROUP_CONCAT(CONCAT('mc.`',f1.id,'`')) as clubFields,GROUP_CONCAT(f1.input_type) as inputType,GROUP_CONCAT(f1.id) as ids FROM fg_cm_attribute f1 
            LEFT JOIN fg_cm_club_attribute f5 ON f5.attribute_id = f1.id AND f5.club_id = {$club['id']}
            WHERE f1.input_type in ('imageupload','fileupload') AND  f1.club_id = {$club['id']}");
            $clubFields = (!empty($fileFields[0]['clubFields'])) ? ",".$fileFields[0]['clubFields']:'';
            $contacts = $this->conn->fetchAll("SELECT C.id,ms.`$profilePicture`,ms.`$companyLogo` $clubFields FROM fg_cm_contact C LEFT JOIN master_system ms ON ms.contact_id=C.id"
                    . " LEFT JOIN $clubTable mc ON mc.contact_id=C.id");
            $fieldType=  explode(',', $fileFields[0]['inputType']);
            $fieldIds=  explode(',', $fileFields[0]['ids']);
            $fields=array_combine($fieldIds, $fieldType);
            $this->createDir($club['id']);
//            echo "<pre/>".$fileFields[0]['inputType']; print_r($fields);
//            print_r($fileFields);exit;
            $txt="MOVING CONTACT STARTS\n";
            fwrite($log, $txt);
            echo nl2br($txt);
            foreach ($contacts as $contact){
                foreach ($contact as $field=>$fValue){
                    if(!empty($fValue)){
                        if(file_exists(getcwd().$DS."uploads".$DS.$club['id'].$DS."contact".$DS.$fValue)){
                            $source_dir=getcwd().$DS."uploads".$DS.$club['id'].$DS."contact";
                            if($fields[$field]=='imageupload'){
                                $destination_dir=$source_dir."contactfield_image";
                                $this->moveFile($source_dir, $destination_dir, $fValue);
                            } elseif($fields[$field]=='fileupload'){
                                $destination_dir=$source_dir."contactfield_file";
                                $this->moveFile($source_dir, $destination_dir, $fValue);
                            } elseif($field==$profilePicture){
                                $destination_dir=$source_dir.$DS.'profilepic'.$DS."original";
                                $this->moveFile($source_dir, $destination_dir, $fValue);
                                $this->moveResized($source_dir, 'profilepic', $fValue);
                            } elseif($field==$companyLogo){
                                $destination_dir=$source_dir.$DS.'companylogo'.$DS."original";
                                $this->moveFile($source_dir, $destination_dir, $fValue);
                                $this->moveResized($source_dir, 'companylogo', $fValue);
                            }
                            $txt="MOVING {$contact['id']}-$fValue\n";
                            fwrite($log, $txt);
                            echo nl2br($txt);
                        }
                    }
                }
            }
            foreach(glob($source_dir.$DS.'*.*') as $file){
                unlink($file);
            }
            $folders = array('thumbnails', 'width_36', 'width_130', 'width_150', 'width_65');
            foreach ($folders as $folder) {
                $source_d='uploads'.$DS.$club['id'].$DS."contact".$DS.$folder;
                if(is_dir($source_d)){
                    foreach(glob($source_d.$DS.'*.*') as $file){
                        unlink($file);
                    }
                    rmdir($source_d);
                }
            }
            
            //contact module moving ends
            //club logo moving starts
            if(!empty($club['logo'])){
                if (!is_dir('uploads/'.$club['id'].'/admin/clublogo')) {
                    mkdir('uploads/'.$club['id'].'/admin/clublogo', 0700,true);
                }
                if(file_exists(getcwd().$DS."uploads".$DS.$club['id'].$DS.$club['logo'])){
                    $txt="MOVING CLUB LOGO {$club['id']}\n";
                    fwrite($log, $txt);
                    echo nl2br($txt);
                    $source_dir=getcwd().$DS."uploads".$DS.$club['id'];
                    $destination_dir=$source_dir.$DS.'admin'.$DS.'clublogo';
                    $this->moveFile($source_dir, $destination_dir, $club['logo']);
                }
                foreach(glob(getcwd().$DS."uploads".$DS.$club['id'].$DS.'*.*') as $file){
                    unlink($file);
                }
            }
            //club logo moving ends

            //moving message attachements starts
            if (is_dir('uploads'.$DS.$club['id'].$DS.'message')) {
                $txt="MOVING MESSAGE ATTACHMENTS \n";
                fwrite($log, $txt);
                echo nl2br($txt);
                if (!is_dir("uploads".$DS.$club['id'].$DS.'users')) {
                    mkdir("uploads".$DS.$club['id'].$DS.'users', 0700,true);
                }
                $source_dir=getcwd().$DS."uploads".$DS.$club['id'].$DS.'message';
                $destination_dir=getcwd().$DS."uploads".$DS.$club['id'].$DS.'users'.$DS.'messages';
                if (is_dir('uploads'.$DS.$club['id'].$DS.'users'.$DS.'messages')) {
                    foreach(glob($source_dir.$DS.'*.*') as $file){
                        $fileDest=  str_replace($DS.'message',$DS.'users'.$DS.'messages',$file);
                        rename($file, $fileDest);
                    }
                    rmdir("uploads".$DS.$club['id'].$DS.'message');
                } else {
                    rename($source_dir, $destination_dir);
                }
                system("rm -rf ".escapeshellarg("uploads".$DS.$club['id'].$DS.'message'));
            }
            //moving message attachements ends
            //move sponsor ads starts
            if (is_dir('uploads'.$DS.$club['id'].$DS.'sponsor'.$DS.'ads')) {
                $txt="MOVING SPONSOR ADS \n";
                fwrite($log, $txt);
                echo nl2br($txt);
                $source_dir=getcwd().$DS."uploads".$DS.$club['id'].$DS.'sponsor'.$DS.'ads';
                $destination_dir=getcwd().$DS."uploads".$DS.$club['id'].$DS.'contact'.$DS.'ad';
                rename($source_dir, $destination_dir);
                rmdir("uploads".$DS.$club['id'].$DS.'sponsor');
                if(is_dir('uploads'.$DS.$club['id'].$DS.'sponsor')){
                    system("rm -rf ".escapeshellarg("uploads".$DS.$club['id'].$DS.'sponsor'));
                }
                foreach(glob($destination_dir.$DS.'*.*') as $file){
                    $fileDest=  str_replace($DS.'ad',$DS.'ad'.$DS.'original',$file);
                    rename($file, $fileDest);
                }
            }
            //move sponsor ads ends
            $txt="REFACTERING CLUB ID {$club['id']} ENDS\n";
            fwrite($log, $txt);
            echo nl2br($txt);
        }
        exit;
    }
    private function moveResized($source_dir,$subFolder,$fValue){
        $DS=DIRECTORY_SEPARATOR;
        $folders = array('thumbnails', 'width_36', 'width_130', 'width_150', 'width_65');
        foreach ($folders as $folder) {
            $source=$source_dir.$DS.$folder;
            $fileName=($folder=='thumbnails') ? 'thumb_'.$fValue:$fValue;
            if (is_file($source.$DS.$fileName)) {
                $destination=$source_dir.$DS.$subFolder.$DS.$folder;
                $this->moveFile($source, $destination, $fileName);
            }
        }
    }

    private function createDir($clubId){
        if (!is_dir('uploads/' . $clubId . '/contact/profilepic')) {
            mkdir('uploads/' . $clubId . '/contact/profilepic', 0700,true);
        }
        if (!is_dir('uploads/' . $clubId . '/contact/companylogo')) {
            mkdir('uploads/' . $clubId . '/contact/companylogo', 0700,true);
        }
        if (!is_dir('uploads/' . $clubId . '/contact/contactfield_file')) {
            mkdir('uploads/' . $clubId . '/contact/contactfield_file', 0700);
        }
        if (!is_dir('uploads/' . $clubId . '/contact/contactfield_image')) {
            mkdir('uploads/' . $clubId . '/contact/contactfield_image', 0700);
        }
        $folders = array('original','thumbnails', 'width_36', 'width_130', 'width_150', 'width_65');
        foreach ($folders as $folder) {
            if (!is_dir('uploads/' . $clubId . '/contact/profilepic/' . $folder)) {
                mkdir('uploads/' . $clubId . '/contact/profilepic/' . $folder, 0700);
            }
            if (!is_dir('uploads/' . $clubId . '/contact/companylogo/' . $folder)) {
                mkdir('uploads/' . $clubId . '/contact/companylogo/' . $folder, 0700);
            }
        }
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
}
