<?php

namespace Common\FilemanagerBundle\Util;

/**
 * Used to check the validation of a file
 *
 * @author jinesh.m
 */
class FileChecking
{

    //put your code here
    public $filename;
    private $container;
    private $em;
    private $club;
    public $clubId;
    private $contact;
    public $contactId;
    public $filepath = '';
    private $invalidMimetype = array(
        'application/vnd.microsoft.portable-executable',
    );

    public function __construct($container)
    {
        $this->container = $container;

        $this->club = $this->container->get('club');
        $this->clubId = $this->club->get('id');

        $this->contact = $this->container->get('contact');
        $this->contactId = $this->contact->get('id');

        $this->invalidMimetype = $this->container->getParameter('blacklistMimetype');

        $this->em = $this->container->get('doctrine')->getManager();
    }

    /**
     * check the mime type and binary check of a file
     * @return boolean
     */
    public function mimeChecking()
    {
        $checkStatus = true;
        $mtypeString = $this->get_mime_type();
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
    public function get_mime_type()
    {
        $mtype = false;
        $file_info = new \finfo(FILEINFO_MIME); // object oriented approach!
        $mtype = $file_info->buffer(file_get_contents($this->filepath . $this->filename));
        return $mtype;
    }

    // encrypted the name of file
    public function sshNameConvertion()
    {

        $filenameSplit = explode(".", $this->filename);
        $ext = pathinfo($this->filename, PATHINFO_EXTENSION);
        $shaname = hash('sha256', $filenameSplit[0] . mt_rand(9999, 9999999));
        $actualShaname = ($ext != '') ? $shaname . "." . $ext : $shaname;
        return $actualShaname;
    }

//check file exist or not
    public function checkFileExist($path = '')
    {
        if ($path == '') {
            $path = 'uploads/' . $this->clubId . '/content/';
        }
        $existFlag = false;
        if (file_exists($path . $this->filename)) {
            $existFlag = true;
        }
        return $existFlag;
    }

    //Get all mime types of file category
    public function getMimetypesOfCategory($type)
    {
        $mimeTypes = array();
        switch ($type) {
            case 'image':
                $mimeTypes = array('image/gif', 'image/jpeg', 'image/png', 'image/psd', 'image/bmp', 'image/x-icon');
                break;
        }
        return $mimeTypes;
    }

    /**
     * virus scan - execute command - avast
     * @param string $path path to sh file
     * @param string $file filename
     * @param string $avastPhpPath path
     * 
     */
    public function virusScanning( $path, $file, $originalFilename)
    {
        $success = 0;
        $avastScan = $this->container->getParameter('avast_scan');
        $avastPhpPath = $this->container->getParameter('root_server_avast_phpfile');
        $result=0;
        try {
            $paramsArray = array();
            $paramsArray['filename'] = $file;
            $paramsArray['option'] = ' -a -i';

            $logId = $this->logVirusScanRequest($originalFilename, $path.$file, $paramsArray['option']);
                //if avast scan enabled then using curl call the sh file ,send the file name to be scanned as param, return the scanoutput
                $curlOptions = array(
                    CURLOPT_URL => $avastPhpPath,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => 1,
                    CURLOPT_POSTFIELDS => $paramsArray,
                );

                $ch = curl_init();
                curl_setopt_array($ch, $curlOptions);
                $result = curl_exec($ch);
                curl_close($ch);
            //delete file if virus present
            if (strstr($result, '[OK]') !== FALSE) {
                $success = 1;               
            } else if(strstr($result, '[ERROR]') !== FALSE){
                unlink($avastPhpPath.$file);
                $success = 0;
            } else {
               unlink($avastPhpPath.$file);
                $success = -1;
            }
            
            $this->logVirusScanResponse($success, $result, $logId);
        } catch (\Exception $e) {
        }

        return $success;
    }

    /**
     * Remove single quotes, dots and replace spaces with '-' from file name
     * @param String $filename 
     * @return String $newFileName
     */
    public function replaceSingleQuotes($filename)
    {
        $ext = (false === $pos = strrpos($filename, '.')) ? '' : substr($filename, $pos);

        $fileNameDestination1 = (false === $pos = strrpos($filename, '.')) ? $filename : substr($filename, 0, $pos);
        //replace spacial characters from file name
        $removeChars = array('"', "'", '!', '$', '`', '%', '^', '*', '+', '=', '|', "\\", '{', '}', '[', ']', ':', ';', '<', '>', '?', '/', '#', '&', ',');
        $fileNameDestination2 = str_replace($removeChars, '', $fileNameDestination1);
        //replace space with '-' from file name
        $newFileName1 = str_replace(" ", "-", $fileNameDestination2);
        //remove starting dots from filename
        $newFileName = (ltrim($newFileName1, '.')) ? ltrim($newFileName1, '.') : (($ext != '') ? 'FILE' : '');

        return $newFileName . $ext;
    }

    /**
     * 
     * @param type $fileName
     * @param string $path
     * @param string $scanOption
     * @return int $logId The id of the row
     */
    private function logVirusScanRequest($fileName, $path, $scanOption)
    {
        $requestDetails = array();
        $fileInfo = new \finfo();
        $fileDetails = $fileInfo->file($path, FILEINFO_NONE);
        
        $requestDetails['club'] = $this->clubId;
        //check whether login or not        
        $requestDetails['contact'] = ($this->contactId!='') ? $this->contactId: '1';
        $requestDetails['fileName'] = $fileName;
        $requestDetails['fileDetails'] = $fileDetails;
        $requestDetails['sentOn'] = new \DateTime('now');
        $requestDetails['avastscanOption'] = $scanOption;

        $logId = $this->em->getRepository('CommonUtilityBundle:FgFileManagerViruscheckLog')->saveVirusLogData($requestDetails);

        return $logId;
    }

    /**
     * 
     * @param string $response
     * @param int $logId
     * 
     * @return void
     */
    private function logVirusScanResponse($response, $result, $logId)
    {
        if ($response === 1) {
            $status = 'safe';
        } else if ($response === -1) {
            $status = 'exception';
        } else if ($response === 0) {
            $status = 'unsafe';
        }
        
        $requestDetails['responseDetail'] = $result;
        $requestDetails['responseStatus'] = $status;
        $requestDetails['responseReceivedon'] = new \DateTime('now');

        $this->em->getRepository('CommonUtilityBundle:FgFileManagerViruscheckLog')->saveVirusLogData($requestDetails, $logId);
    }
}
