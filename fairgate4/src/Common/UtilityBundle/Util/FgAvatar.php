<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Common\UtilityBundle\Util;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Common\UtilityBundle\Util\FgUtility;
use Common\FilemanagerBundle\Util\FileChecking;

/**
 * Manage profile /company picture.
 *
 * @author jinesh.m <jineshm.pitsolutions.com>
 */
class FgAvatar
{

    /**
     * @var object Container variable 
     */
    public $container;

    /**
     * @var object entity manager variable 
     */
    private $em;

    /**
     *
     * @var service club service 
     */
    public $club;

    /**
     *
     * @var string 
     */
    private $uploadFolder = 'uploads/';

    /**
     *
     * @var type current club id
     */
    private $clubId;

    /**
     *
     * @var array 
     */
    private $invalidMimetype;

    /**
     *
     * @var array 
     */
    private $invalidFileExtension;

    /**
     * To set avast scan status
     * 
     * @var boolean 
     */
    private $avastScan = false;

    /**
     * Constructor of FgAvatar class.
     * @param ContainerInterface $container
     * @param service $club club service
     */
    public function __construct(ContainerInterface $container, $club)
    {
        $this->container = $container;
        $this->em = $this->container->get('doctrine')->getManager();
        $this->club = $club;
        $this->invalidMimetype = $this->container->getParameter('blacklistMimetype');
        $this->invalidFileExtension = $this->container->getParameter('forbiddenFiletypes');
        $this->avastScan = $this->container->getParameter('avast_scan');
    }

    /**
     * @param int $contactId id of a contact
     * @param int $resize sizeof 
     */
    public function getAvatar($contactId, $resize = '',$realPath = false)
    {
        $profileImagefield = $this->container->getParameter('system_field_communitypicture');
        $companyLogofield = $this->container->getParameter('system_field_companylogo');
        //collect contact id detail
        $contactIdDetails = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getAllContactIds($contactId);
        //from the array result index 1 is fed contact id 
        $contactid = ($contactIdDetails[0][1] != '') ? $contactIdDetails[0][1] : $contactId;
        $contactImageDetails = $this->em->getRepository('CommonUtilityBundle:MasterSystem')->getAvatarImage($contactid, $profileImagefield, $companyLogofield);
        $actualType = ($contactImageDetails['isCompany'] == 1) ? "companylogo" : "profilepic";

        if (count($contactImageDetails) > 0 && $contactImageDetails['avatar'] != '') {
            //get the folder path of profile picture/company logo
            $folderpath = $this->getUploadFilePath($actualType);
            //create urlpath
            if ($resize == '' && $realPath)
                $urlpath = "/" . $folderpath . "/" . $this->getProfilePictureFolder($contactImageDetails['isCompany']) . '/' . $contactImageDetails['avatar'];
            else if($resize == '')
                $urlpath = FgUtility::getBaseUrl($this->container) . "/" . $folderpath . "/" . $this->getProfilePictureFolder($contactImageDetails['isCompany']) . '/' . $contactImageDetails['avatar'];
            else
                $urlpath = FgUtility::getBaseUrl($this->container) . "/" . $folderpath . "/width_" . $resize . '/' . $contactImageDetails['avatar'];
        } else {
            $urlpath = '';
        }

        return $urlpath;
    }

    /**
     * 
     * @param type $contactfield id of contact field
     * @param type $baseUrlflag base url setting flag
     * @return string
     */
    public function getContactfieldPath($contactfield, $baseUrlflag = true, $resizeFolder = '')
    {
        $systemfields = $this->container->getParameter('system_fields');
        $filetype = $this->getContactfieldType($contactfield);
        $baseUrl = ($baseUrlflag) ? FgUtility::getBaseUrl($this->container) . '/' : '';
        $fedId = $this->club->get('federation_id');
        //check if the field is system field
        if (in_array($contactfield, $systemfields)) {
            //checking if the contact field is profile/company picture
            $imageFieldArray = array($this->container->getParameter('system_field_communitypicture') => 'profilepic', $this->container->getParameter('system_field_companylogo') => 'companylogo');
            if (array_key_exists($contactfield, $imageFieldArray)) {
                $filetype = $imageFieldArray[$contactfield];
            }
            $this->clubId = ($fedId > 0) ? $fedId : $this->club->get('id');
            $path = $baseUrl . $this->getUploadFilePath($filetype, $resizeFolder, $this->clubId);
        } else {
            $path = $baseUrl . $this->getUploadFilePath($filetype, $resizeFolder, $this->clubId);
        }

        return $path;
    }

    /**
     * Method to get profile pictute folder name of contact
     *
     * @param boolean $isCompany isCompany of contact
     *
     * @return string
     */
    public function getProfilePictureFolder($isCompany)
    {
        $folderName = ($isCompany == "1") ? "width_65" : "width_150";

        return $folderName;
    }

    /**
     *
     * @param type $clubId
     * @param type $fileType
     * @param type $resize
     * @param type $fileName
     * @return string
     */
    public function getUploadFilePath($fileType, $resize = false, $clubId = '')
    {
        $this->clubId = ($clubId != '') ? $clubId : $this->club->get('id');
        $fedId = $this->club->get('federation_id');
        switch ($fileType) {
            case 'profilepic':
            case 'companylogo':
                $this->clubId = ($fedId > 0) ? $fedId : $this->clubId;
                $uploadPath = $this->uploadFolder . $this->clubId . '/contact/' . $fileType;
                break;
            case 'contactfield_image':
            case 'contactfield_file':
                $uploadPath = $this->uploadFolder . $this->clubId . '/contact/' . $fileType;
                break;
        }
        if ($resize) {
            $uploadPath .= '/' . $resize;
        }

        return $uploadPath;
    }

    /**
     * To get the type of contact field 
     * @param type $contactfieldId contact field id
     * @return string
     */
    private function getContactfieldType($contactfieldId)
    {
        $allContactFiledsData = $this->club->get('allContactFields');
        switch ($allContactFiledsData[$contactfieldId]['type']) {

            case 'imageupload' :
                $filetype = 'contactfield_image';
                $this->clubId = $allContactFiledsData[$contactfieldId]['club_id'];
                break;
            case 'fileupload' :
                $filetype = 'contactfield_file';
                $this->clubId = $allContactFiledsData[$contactfieldId]['club_id'];
                break;
        }

        return $filetype;
    }

    /**
     *
     * @param string $data Image data string
     * @param inet $clubId The contact club
     * @param string $fileType {profilepic/companylogo}
     * 
     * @return string
     */
    public function saveUserAndCompanyLogo($imageName, $fileType = 'profilepic', $uploadFile)
    {
        $uploadPath = FgUtility::getUploadDir();
        $currentPath = $uploadPath . '/temp/' . $uploadFile;
        $newFileName = '';
            $uploadFolderPath = FgAvatar::getUploadFilePath($fileType, 'original');
            $newFileName = FgUtility::getFilename($uploadFolderPath, $imageName);
            $uploadFilePath = $uploadFolderPath . '/' . $newFileName;
            $this->createFolders(FgAvatar::getUploadFilePath($fileType));
            if (file_exists($currentPath)) {
                copy($currentPath, $uploadFilePath);
            }
            //for image resizing            
            FgUtility::getResizeImages($this->container, FgAvatar::getUploadFilePath($fileType), $newFileName, 'contact');

        return $newFileName;
    }

    /**
     *
     * @param object $fileElement Filelement Object
     * @param int $fieldId The attribute if
     * @param string $fileType {contactfield_image,contactfield_file}
     * 
     * @return string
     */
    public function uploadContactField($fileElement, $fieldId)
    {

        $uploadPath = FgAvatar::getContactfieldPath($fieldId, false);

        mkdir($uploadPath, 0700, true);

        $fileName = $fileElement->getClientOriginalName();
        $FileChecking = new FileChecking($this->container);
        $fileName = $FileChecking->replaceSingleQuotes($fileName);
        $fileName = FgUtility::getFilename($uploadPath, $fileName);

        $fileElement->move($uploadPath, $fileName);

        return $fileName;
    }

    /**
     * Function to create the folders for a contact 
     *
     * @param string $uploadPath The folder path to which the fileis uploaded
     *
     * @return string $baseUrl BaseUrl
     */
    private function createFolders($uploadPath)
    {

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0700, true);
        }
        $folders = array('/original', '/width_150', '/width_65');
        foreach ($folders as $folder) {
            if (!is_dir($uploadPath . $folder)) {
                mkdir($uploadPath . $folder, 0700);
            }
            if (!is_dir($uploadPath . $folder)) {
                mkdir($uploadPath . $folder, 0700);
            }
        }
    }

    /**
     * This function is used to copy the profile pic/company logo and contact fileds of type file/image 
     * on removing fed membership of a contact shared in mutiple clubs
     * 
     * @param int    $attributeId Attribute id of company logo/profile picture
     * @param string $fileName    Filename
     * @param string $fileType    company logo/profile picture
     * @param int    $clubId      Club id
     * 
     * @return string $newFileName The new filename
     */
    public function copyFilesOfContactOnRemovingFedMembership($attributeId, $fileName, $fileType = '', $clubId = '')
    {
        $rootPath = FgUtility::getRootPath($this->container);
        $fileType = ($fileType != '') ? $fileType : FgAvatar::getContactfieldType($attributeId);
        $uploadFolderPath = FgAvatar::getUploadFilePath($fileType, false, $clubId);
        $uploadOrgFolderPath = (($fileType == 'profilepic') || ($fileType == 'companylogo')) ? ($uploadFolderPath . '/original') : $uploadFolderPath;
        //get new filename
        $newFileName = FgUtility::getFilename($rootPath . '/' . $uploadOrgFolderPath, $fileName);
        //copy files in original folders
        $sourceFilePath = $rootPath . '/' . $uploadOrgFolderPath . '/' . $fileName;
        $destinationFilePath = $rootPath . '/' . $uploadOrgFolderPath . '/' . $newFileName;
        if (is_file($sourceFilePath)) {
            copy($sourceFilePath, $destinationFilePath);
        }
        //copy files in all subfolders too
        if (($fileType == 'profilepic') || ($fileType == 'companylogo')) {
            $folders = array('width_150', 'width_65');
            foreach ($folders as $folder) {
                $sourceFilePath = $rootPath . '/' . $uploadFolderPath . '/' . $folder . '/' . $fileName;
                $destinationFilePath = $rootPath . '/' . $uploadFolderPath . '/' . $folder . '/' . $newFileName;
                if (is_file($sourceFilePath)) {
                    copy($sourceFilePath, $destinationFilePath);
                }
            }
        }
        unlink($sourceFilePath);
        return $newFileName;
    }
    /*     * *
      This function is used to copy the cms theme header
     * @param int    $themeConfId Configuration Id
     * @param array $data   headerDetails
     * @param int    $clubId      Club id
     * 
     * @return boolean;
     * */

    public function moveWebsiteHeader($themeConfId, $data, $clubId, $edit = 0)
    {
        $uploadDir = FgUtility::getUploadDir();
       
        if (count($data['headerLogos']) > 0) {
            $uploadPath = $uploadDir . FgUtility::getUploadFilePath($this->container->get('club')->get('id'), 'cms_header_up');
            $uploadPaththeme1920 = $uploadDir . FgUtility::getUploadFilePath($this->container->get('club')->get('id'), 'cms_header1920');
            $uploadPaththeme1170 = $uploadDir . FgUtility::getUploadFilePath($this->container->get('club')->get('id'), 'cms_header1170');
            $themeConfigObj = $this->em->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->find($themeConfId);
            $themeId = $themeConfigObj->getTheme()->getId();
            $this->createUploadDirectories($uploadPath);
            if ($themeId == 2) {
                $this->createUploadDirectories($uploadPaththeme1170);
                $this->createUploadDirectories($uploadPaththeme1920);
            }
            $dataFiles = $deletedFiles = array();
            $i = $k = 0;

            foreach ($data['headerLogos'] as $key => $fileDetails) {
                if ($fileDetails['fileName'] != '') {
                    $fileName = FgUtility::getFilename($uploadPath, $fileDetails['fileName']);
                    $currentPath = $uploadDir . '/temp/' . $fileDetails['randomName'];
                    $fileDetails['headerDeleted'] = ($fileDetails['headerDeleted'] == '1' ? '1' : '0');
                    if ($fileDetails['headerChanged'] == 1 && $fileDetails['headerDeleted'] == 0) {
                        $dataFiles[$i]['id'] = ($edit == 1) ? $fileDetails['headerId'] : '';
                        $dataFiles[$i]['Changed'] = $changedFlag;
                        $dataFiles[$i]['label'] = $key;
                        $dataFiles[$i]['fileName'] = $fileName;
                        if (file_exists($currentPath)) {
                            copy($currentPath, $uploadPath . '/' . $fileName);
                            if ($themeId == 2) {
                                FgUtility::resizeImage($currentPath, $uploadPaththeme1170 . '/' . $fileName, '1170');
                                FgUtility::resizeImage($currentPath, $uploadPaththeme1920 . '/' . $fileName, '1920');
                            }
                            unlink($currentPath);
                        }
                        $i++;
                    } else if ($fileDetails['headerDeleted'] == 1 && $edit == 1) {
                        $deletedFiles[$k]['id'] = $fileDetails['headerId'];
                        $k++;
                    }
                }
            }

            $this->em->getRepository('CommonUtilityBundle:FgTmThemeHeaders')->saveHeaderConfigurations($themeConfId, $dataFiles, $edit);
            
            if ($k > 0)
                $this->em->getRepository('CommonUtilityBundle:FgTmThemeHeaders')->deleteHeaders($deletedFiles);
        }
    
        return true;
    }
    /*     * *
      This function is used to give permission
     * @param string $upload_path    fileuploadpath
     * @loopall for the specified filepath
     * 
     * @return boolean ;
     * */

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
     * To check the forbidden file type and mime types and also the virus checking part is done through below function
     * @param String $filepath current path of the checking file
     * @param String $fileName file name of the checking file
     * @param String $extension extension of the file
     * @param String $originalFilename original file name of the file (some cases file convert to tempory file)
     * @param boolean $checkExtension whetehr to check extension
     * 
     * @return boolean true/false
     */
    public function isForbidden($filepath, $fileName, $extension = '', $originalFilename = '', $checkExtension = true)
    {
        $fileExtension = ($extension == '') ? strtolower(end(explode('.', $fileName))) : $extension;
        $result = array('checking' => true);
        $status = true;
        //For checking the invalid file extension
        if ($this->avastScan) {
            $result = $this->isVirus($fileName, $originalFilename);
        }
        //Only after antivirus checking is false
        if (count($result) == 1 && ($checkExtension)) {
            //checking extension and mime type
            if (in_array($fileExtension, $this->invalidFileExtension)) {
                $status = false;
                $result = array('status' => 'error', 'error' => 'invalid extension', 'checking' => false);
            }

            if ($status == true && !$this->mimeChecking($filepath, $fileName)) {
                $status = false;
                $result = array('status' => 'error', 'error' => 'invalid mime type', 'checking' => false);
            }
        }
        $var = "filepath:" . $filepath . "#### filenmae:" . $fileName . "### extension:" . $extension . " $$$$ originalFilename:" . $originalFilename . "@@@Status:" . $status . "@@@mime:" . $this->get_mime_type($filepath, $fileName);
        file_put_contents('viruscheck.txt', $var);

        return $result;
    }

    /**
     * To check the mime type
     * @param String $filepath current path of the checking file
     * @param String $fileName checking file name
     * 
     * @return boolean true/false
     */
    private function mimeChecking($filepath, $fileName)
    {
        $checkStatus = true;
        $mtypeString = $this->get_mime_type($filepath, $fileName);
        $mtypeArray = explode(";", $mtypeString);
        $mtype = (count($mtypeArray) > 1) ? $mtypeArray[0] : $mtypeArray[0];
        //check our mime type with black listed mime type
        if (in_array($mtype, $this->invalidMimetype)) {
            $checkStatus = false;
        }

        return $checkStatus;
    }

    /**
     * get the actual mime type of a file
     * @return type
     */
    private function get_mime_type($filepath, $fileName)
    {
        $mtype = false;
        $file_info = new \finfo(FILEINFO_MIME); // object oriented approach!
        $mtype = $file_info->buffer(file_get_contents($filepath . $fileName));
        return $mtype;
    }

    /**
     * To check uploaded file is virus or not
     * @param String $filename file name of the testing file
     * @param String $originalFilename original file name of the testing file
     * @return \Common\UtilityBundle\Util\JsonResponse
     */
    private function isVirus($filename, $originalName = '')
    {
        $avastPhpPath = $this->container->getParameter('root_server_avast_phpfile');
        $fileCheck = new FileChecking($this->container);
        $fileCheck->filename = $filename;
        $fileCheck->filepath = $this->container->getParameter('avast_scan_upload_folder');
        $originalFilename = ($originalName != '') ? $originalName : $filename;
        $result = $fileCheck->virusScanning($fileCheck->filepath, $fileCheck->filename, $originalFilename);
        $return = array('checking' => true);
        if ($result != 1) {
            if ($result === 0) {
                $error = 'INVALID_VIRUS_FILE';
            } else if ($result === -1) {
                $error = 'VIRUS_FILE_CONTACT';
            }
            $return = array('status' => 'error', 'error' => $error, 'checking' => false);
        }

        return $return;
    }
}
