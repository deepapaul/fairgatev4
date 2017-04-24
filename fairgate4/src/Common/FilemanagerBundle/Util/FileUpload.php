<?php

namespace Common\FilemanagerBundle\Util;

use Symfony\Component\HttpFoundation\File\File;
use Common\UtilityBundle\Util\FgUtility;

/**
 * Used to upload the file to filemanager.
 *
 * @author pitsolutions.ch
 */
class FileUpload
{
    public $container;

    private $club;

    private $clubId;

    private $contact;

    private $contactId;

    private $em;

    public function __construct($container)
    {
        $this->container = $container;
        $this->em = $this->container->get('doctrine')->getManager();

        $this->club = $this->container->get('club');
        $this->clubId = $this->club->get('id');

        $this->contact = $this->container->get('contact');
        $this->contactId = $this->contact->get('id');
    }

    /*
     * The function to upload the file to the club filemanager folder
     *
     * @param array $fileManagerDetails File details it should contain randFileName,fileName,shaFileName
     *
     */
    public function saveToFilemanager($fileManagerDetails)
    {
        $fileManagerDetails['clubId'] = $this->clubId;
        $fileManagerDetails['contactId'] = $this->contactId;
        $filemanagerIds = $this->em->getRepository('CommonUtilityBundle:FgFileManager')
                                    ->saveFilemanagerFile($fileManagerDetails, $this->container);

        return $filemanagerIds;
    }

    /*
     * The function to move the uploaded file to club folder
     *
     * @param array $randFileNameArray The array of names of which the file in been saved in temp folder
     * @param array $fileNameArray The array of original names of the file
     *
     * @return array $shaFilenameArray The sha name of which the file has been saved in the club folder
     */
    public function movetoClubFilemanagerAction($fileManagerDetails)
    {
        $randFileNameArray = $fileManagerDetails['randFileName'];
        $fileNameArray = $fileManagerDetails['fileName'];

        $uploadDirectory = 'uploads/';
        $this->dirCheck($uploadDirectory);
        $clubDirectory = $uploadDirectory.$this->clubId;
        $this->dirCheck($clubDirectory);
        $clubFilemanagerDirectory = $clubDirectory.'/content';
        $this->dirCheck($clubFilemanagerDirectory);

        $shaFilenameArray = array();
        $filesizeArray = array();
        foreach ($randFileNameArray as $key => $document) {
            $filesize = 0;

            $fileCheck = new FileChecking($this->container);
            $filename = $fileCheck->replaceSingleQuotes($fileNameArray[$key]);
            $fileCheck->filename = mt_rand(9999, 999999).$filename;
            $shaFilename = $fileCheck->sshNameConvertion();
            $avastScan = $this->container->getParameter('avast_scan');
            $rootPath = FgUtility::getRootPath($this->container);
            $uploadPath = $rootPath.'/uploads/temp/';
            if (file_exists($uploadPath.$document)) {
                $attachmentObj = new File($uploadPath.$document, false);
                $attachmentObj->move($clubFilemanagerDirectory, $shaFilename);
                $filesize = filesize($clubFilemanagerDirectory.'/'.$shaFilename);
            }
            $shaFilenameArray[$key] = $shaFilename;
            $filesizeArray[$key] = $filesize;
        }

        $fileManagerDetails['fileSize'] = $filesizeArray;
        $fileManagerDetails['shaFileName'] = $shaFilenameArray;
        $fileManagerDetails['fileCount'] = count($shaFilenameArray);

        return $fileManagerDetails;
    }

    /*
     * The function to check if the directory exists, if not exixts it will be created
     *
     * @param string $directory The directory path
     *
     * @return void
     */
    private function dirCheck($directory)
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        return;
    }
}
