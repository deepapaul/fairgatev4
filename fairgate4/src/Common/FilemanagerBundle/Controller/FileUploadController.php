<?php

/**
 * File upload Controller.
 *
 * This controller is used for Uploading Files in file manager section.
 *
 * @author     pitsolutions.ch
 *
 * @version    Fairgate V4
 */
namespace Common\FilemanagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\File;
use Common\UtilityBundle\Util\FgUtility;
use Symfony\Component\HttpFoundation\JsonResponse;
use Common\FilemanagerBundle\Util\FileChecking;
use Clubadmin\Classes\FgFileUploadHandler;
use Symfony\Component\HttpFoundation\Request;

class FileUploadController extends Controller
{

    /**
     * Upload a file in file manager.
     * 
     * @param Request $request Request object
     *
     * @return JsonResponse
     */
    public function fileUploadSaveAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        //Club & Contact Details
        $club = $this->container->get('club');
        $contact = $this->container->get('contact');
        $clubId = $club->get('id');
        $contactId = $contact->get('id');

        $fileManagerDetails = $request->request->all();
        $fileManagerDetails['clubId'] = $clubId;
        $fileManagerDetails['contactId'] = $contactId;
        $fileCheck = new FileChecking($this->container);
        //Move images/videos to the club gallery folder from temp folder
        $shaFileNameArray = $this->movetoClubFilemanagerAction($fileManagerDetails['randFileName'], $fileManagerDetails['fileName']);

        //Insert Filemanager query
        $fileManagerDetails['shaFileName'] = $shaFileNameArray;
        $galleryItemId = $em->getRepository('CommonUtilityBundle:FgFileManager')->saveFilemanagerFile($fileManagerDetails, $this->container);
        $return = array('status' => 'SUCCESS', 'sync' => 1, 'noreload' => 1, 'flash' => $this->get('translator')->trans('FILEMANAGER_UPLOADED_SUCCESSFULLY'));

        return new JsonResponse($return);
    }

    /**
     * The function to upload the file to the club filemanager folder
     *
     * @param array $fileManagerDetails File details
     *
     */
    public function movetoClubFilemanagerAction($randFileNameArray, $fileNameArray)
    {
        $uploadDirectory = FgUtility::getUploadDir();
        $this->dirCheck($uploadDirectory);
        $clubDirectory = $uploadDirectory ."/". $this->get('club')->get('id');
        $this->dirCheck($clubDirectory);
        $clubFilemanagerDirectory = $clubDirectory ."/". '/content';
        $this->dirCheck($clubFilemanagerDirectory);
        $shaFilenameArray = array();
        $uploadPath =  $uploadDirectory."/temp/";
        foreach ($randFileNameArray as $key => $document) {
            $fileCheck = new FileChecking($this->container);
            //set file name
            $fileCheck->filename =$document;
            //set file path
            $fileCheck->filepath =$uploadPath;
            if($fileCheck->mimeChecking()) {
            
            $filename = $fileCheck->replaceSingleQuotes($fileNameArray[$key]);
            $fileCheck->filename = mt_rand(9999, 999999) . $filename;
            $shaFilename = $fileCheck->sshNameConvertion();                     

            if (file_exists($uploadPath . $document)) {
                $attachmentObj = new File($uploadPath . $document, false);
                $attachmentObj->move($clubFilemanagerDirectory, $shaFilename);
            }
            $shaFilenameArray[$key] = $shaFilename;
            }
        }
        return $shaFilenameArray;
    }

    /**
     * The function to check if directory exist else add a directory
     *
     * @param string $directory Directory name
     *
     */
    private function dirCheck($directory)
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
    }

    /**
     * function for mime type and binary check
     * 
     * @param Request $request Request object
     * @param type $type
     * 
     * @return JsonResponse
     */
    public function fileUploadCheckAction(Request $request, $type)
    {
        $avastScan = $this->container->getParameter('avast_scan');
        $fileCheck = new FileChecking($this->container);
        $upload = new FgFileUploadHandler($request, $this->container);
        $uploadDirectory = FgUtility::getUploadDir();
        $uploadedDirectory = ($avastScan) ? $this->container->getParameter('avast_scan_upload_folder') : $uploadDirectory."/temp/";
        $fileCheck->filepath = $uploadedDirectory;        
        $return = $upload->initialize();

        return new JsonResponse($return);
    }

    /**
     * Function to replace a file in file manager
     * 
     * @param Request $request Request object
     * 
     * @return JsonResponse
     */
    public function replaceFileAction(Request $request)
    {
        $clubId = $this->container->get('club')->get('id');
        $contactId = $this->container->get('contact')->get('id');
        $fileManagerDetails = $request->request->all();
        $newFileNameArray = $this->movetoClubFilemanagerAction($fileManagerDetails['randFileName'], $fileManagerDetails['fileName']);
        $fileManagerDetails['shafilename'] = $newFileNameArray[0];
        $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgFileManager')->setFileManagerReplace($fileManagerDetails, $this->container, $contactId, $clubId);
        $return = array('status' => 'SUCCESS', 'sync' => 1, 'noreload' => 1, 'flash' => $this->get('translator')->trans('FILE_REPLACE_SUCCESS_MESSAGE'));

        return new JsonResponse($return);
    }
}
