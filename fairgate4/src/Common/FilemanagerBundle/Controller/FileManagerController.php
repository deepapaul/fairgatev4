<?php

/**
 * File manager Controller.
 *
 * This controller is used for file manager section.
 *
 * @author     pitsolutions.ch
 *
 * @version    Fairgate V4
 */
namespace Common\FilemanagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Common\FilemanagerBundle\Util\FileChecking;
use Common\UtilityBundle\Util\FgUtility;
use Common\FilemanagerBundle\Util\FgFileManager;
use Symfony\Component\HttpFoundation\Request;

class FileManagerController extends Controller
{

    /**
     *
     * @param type $type type of view(image/document)
     * @return type
     */
    public function fileManagerViewAction($type, $module)
    {
        $breadCrumb = array('breadcrumb_data' => array());
        $clubId = $this->container->get('club')->get('id');
        $baseUrl = FgUtility::getBaseUrl($this->container);
        $moduleName = $module;

        $adminFlag = 0;
        $userRights = array('ROLE_USERS', 'ROLE_CMS_ADMIN', 'ROLE_CALENDAR_ADMIN', 'ROLE_GROUP_ADMIN', 'ROLE_CALENDAR');
        $contactId = $this->container->get('contact')->get('id');
        $Grouprights = $this->container->get('contact')->get('clubRoleRightsRoleWise');
        $allGrouprights = array();
        foreach ($Grouprights as $key => $grts) {
            $allGrouprights[$key] = $grts['rights'][0];
        }

        $userrightsIntersect = array_intersect($userRights, $allGrouprights);
        $cmAdmin = $this->container->get('contact')->get('allowedModules');
        $commAdmin = in_array('communication', $cmAdmin) ? 1 : 0;
        $isAdmin = (count($this->container->get('contact')->get('mainAdminRightsForFrontend')) > 0) ? 1 : 0;
        if (count($userrightsIntersect) > 0 || $isAdmin == 1 || $commAdmin == 1) {
            $adminFlag = 1;
        }
        $forbiddenFiletypes = $this->container->getParameter('forbiddenFiletypes');

        return $this->render('CommonFilemanagerBundle:FileManager:fileManagerView.html.twig', array('breadCrumb' => $breadCrumb, 'clubId' => $clubId, 'viewtype' => $type, 'baseUrl' => $baseUrl, 'adminFlag' => $adminFlag, 'contactId' => $contactId, 'module' => $moduleName, 'forbiddenFiletypes' => implode(',', $forbiddenFiletypes)));
    }

    /**
     * To get the file listing.
     *
     * @param Request $request Request Object
     * @param type    $listType
     *
     * @return JsonResponse
     */
    public function listFiledetailsAction(Request $request, $listType)
    {
        $club = $this->container->get('club');
        $clubId = $club->get('id');
        $em = $this->getDoctrine()->getManager();
        $searchValue = $request->get('search', '');
        $startValue = $request->get('start', '');
        $displayLength = $request->get('length', '');
        $sortColumnValue = $request->get('order', '');
        $dataTableColumnData = $request->get('columns', '');
        //check search value is exist or not
        $searchCondition = $this->setSearchCondition($searchValue);
        //check sorting
        $sortCondition = $this->sortCondition($sortColumnValue);
        //to check listing count is same as  query getting count after doing the file existance check
        //do {
        $totalfilelistDatas = $em->getRepository('CommonUtilityBundle:FgFileManager')->getTotalCount($clubId, $listType, $searchCondition);
        $resultdata = $em->getRepository('CommonUtilityBundle:FgFileManager')->getFileDetails($clubId, $sortCondition, $searchCondition, $startValue, $displayLength, $listType);
        //$existingFiles = $this->checkclubFileExistance($resultdata);
        $resultdata = $this->includeFileType($resultdata);
        // } while (count($resultdata) == count($existingFiles));
        $output = array('iTotalRecords' => 0, 'iTotalDisplayRecords' => 0, 'aaData' => array());
        $output['iTotalRecords'] = $totalfilelistDatas[0]['Count'];
        $output['iTotalDisplayRecords'] = $totalfilelistDatas[0]['Count'];

        $output['aaData'] = $resultdata;

        return new JsonResponse($output);
    }

    /**
     * To set the sorting.
     *
     * @param array $sortColumnValue     sorting details
     * @param array $dataTableColumnData column  data
     *
     * @return string
     */
    public function sortCondition($sortColumnValue)
    {
        $columns = $this->getColumnFields();
        $sortString = '';
        if ($sortColumnValue != '') {
            $sSortDirVal = $sortColumnValue[0]['dir'];
            $sortColumn = $columns[$sortColumnValue[0]['column'] - 1];

            if ((strpos($sortColumn, 'uploadedOn') !== false)) {
                $modifiiedsortColumn = 'FMV.uploaded_at';
                $sortColumnValue = ' (CASE WHEN ' . $modifiiedsortColumn . ' IS NULL then 4 WHEN ' . $modifiiedsortColumn . "='' then 3 WHEN " . $modifiiedsortColumn . "='0000-00-00 00:00:00' then 2 WHEN " . $modifiiedsortColumn . "='-' then 1 ELSE 0 END)," . $modifiiedsortColumn . ' ' . $sSortDirVal;
            } else {
                $sortColumnValue = ' (CASE WHEN ' . $sortColumn . ' IS NULL then 4 WHEN ' . $sortColumn . "='' then 3 WHEN " . $sortColumn . "='0000-00-00 00:00:00' then 2 WHEN " . $sortColumn . "='-' then 1 ELSE 0 END)," . $sortColumn . ' ' . $sSortDirVal;
            }
            $sortString = $sortColumnValue;
        }

        return $sortString;
    }

    /**
     * to set the search condition.
     *
     * @param array $searchValue
     *
     * @return string
     */
    public function setSearchCondition($searchValue)
    {
        $conn = $this->container->get('database_connection');
        $sWhere = '';
        if (is_array($searchValue) && $searchValue['value'] != '') {
            $sWhere = '(';
            $searchcolumns = $this->getColumnFields();
            $searchVal = FgUtility::getSecuredDataString($searchValue['value'], $conn);
            $sWhere .= 'FMV.filename' . " LIKE '%" . $searchVal . "%' ";
            //set all column as search column
//            foreach ($searchcolumns as $columns) {
//                $sWhere .= $columns . " LIKE '%" . mysql_escape_string($searchValue['value']) . "%' OR ";
//            }
            $sWhere .= ')';
        }

        return $sWhere;
    }

    /**
     * get the column field.
     *
     * @return string
     */
    private function getColumnFields()
    {
        $fields = array('filename', 'Size', 'uploadedOn', 'uploadedBy', 'Source');

        return $fields;
    }

    /**
     * to check given result set contain file is exist or not.
     *
     * @param array $filelist
     *
     * @return array
     */
    private function checkclubFileExistance($filelist)
    {
        $existingFileArray = array();
        $nonexistingFileArray = array();
        if (count($filelist) > 0) {
            $fileChecking = new FileChecking($this->container);
            foreach ($filelist as $list) {
                $fileChecking->filename = $list['filename'];
                $fileChecking->clubId = $list['clubId'];
                if ($fileChecking->checkFileExist('uploads/' . $this->clubId . '/content/')) {
                    array_push($existingFileArray, $list['fileManagerId']);
                } else {
                    array_push($nonexistingFileArray, $list['fileManagerId']);
                }
            }
        }

        return $existingFileArray;
    }

    /**
     * Function to show file rename popup
     *
     * @param Request $request Request Object
     *
     * @return JsonResponse
     */
    public function renameFilePopupAction(Request $request)
    {
        $filename = $request->get('filename');
        $fileId = $request->get('fileId');

        return $this->render('CommonFilemanagerBundle:FileManager:fileManagerRenamePopup.html.twig', array('fileName' => $filename, 'fileId' => $fileId));
    }

    /**
     * Function to rename  the existing file name
     *
     * @param Request $request Request Object
     *
     * @return JsonResponse
     */
    public function renameFileAction(Request $request)
    {
        $contactId = $this->container->get('contact')->get('id');
        $fileName = $request->get('filename');
        $oldfileName = $request->get('oldFilename');
        $club = $this->container->get('club');
        $clubId = $club->get('id');
        $fileId = $request->get('fileId');
        //$filename = $this->renameFileinFolder($fileName, $oldfileName);
        $fileName = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgFileManager')->checkFilenameExist($clubId, $fileName, $fileId);

        $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgFileManager')->renameFile($fileId, $contactId, $fileName);

        return new JsonResponse(array('status' => 'SUCCESS', 'noparentload' => 1, 'flash' => $this->get('translator')->trans('FILE_MANAGER_RENAME_SUCCESS_MESSAGE')));
    }

    /**
     * Method to list files of contact/users
     *
     * @return Template
     */
    public function listModuleFilesAction()
    {
        $breadCrumb = array('breadcrumb_data' => array());
        $transltor = $this->get('translator');
        $baseUrl = FgUtility::getBaseUrl($this->container);
        $adminFlag = 0;
        $userRights = array('ROLE_USERS', 'ROLE_CMS_ADMIN');
        $contactId = $this->container->get('contact')->get('id');
        $Grouprights = $this->container->get('contact')->get('clubRoleRightsRoleWise');
        $allGrouprights = array();
        $forbiddenFiletypes = $this->container->getParameter('forbiddenFiletypes');
        foreach ($Grouprights as $key => $grts) {
            $allGrouprights[$key] = $grts['rights'][0];
        }

        $userrightsIntersect = array_intersect($userRights, $allGrouprights);
        $isAdmin = (count($this->container->get('contact')->get('mainAdminRightsForFrontend')) > 0) ? 1 : 0;
        if (count($userrightsIntersect) > 0 || $isAdmin == 1) {
            $adminFlag = 1;
        }

        $isSuperAdmin = $this->container->get('contact')->get('isSuperAdmin');
        $tabs = array('overview' => array('text' => $transltor->trans('FILE_OVERVIEW'), 'url' => ''),
            'admin' => array('text' => $transltor->trans('FILE_ADMINISTRATIVE'), 'url' => $this->generateUrl('filemanager_list', array('module' => 'admin'))),
            'contact' => array('text' => $transltor->trans('FILE_CONTACTS'), 'url' => $this->generateUrl('filemanager_list', array('module' => 'contact'))),
            'content' => array('text' => $transltor->trans('FILE_CONTENT'), 'url' => ''),
            'documents' => array('text' => $transltor->trans('FILE_DOCUMENTS'), 'url' => $this->generateUrl('filemanager_list', array('module' => 'documents'))),
            'gallery' => array('text' => $transltor->trans('FILE_GALLERY'), 'url' => $this->generateUrl('filemanager_gallery_list_data')),
            'users' => array('text' => $transltor->trans('FILE_USERS'), 'url' => $this->generateUrl('filemanager_list', array('module' => 'users'))));
        $nodataMessage = $transltor->trans('DB_NODATA');
        $allowedModules = $this->container->get('contact')->get('allowedModules');
        $bookedModules = $this->container->get('club')->get('bookedModulesDet');
        if (!in_array("document", $allowedModules))
            unset($tabs['documents']);
        if (!in_array("frontend1", $bookedModules))
            unset($tabs['gallery']);
        
        return $this->render('CommonFilemanagerBundle:FileManager:listModuleFiles.html.twig', array('breadCrumb' => $breadCrumb, 'tabs' => $tabs, 'baseUrl' => $baseUrl, 'nodataMessage' => $nodataMessage, 'contactId' => $contactId, 'adminFlag' => $adminFlag, 'isSuperAdmin' => $isSuperAdmin,'forbiddenFiletypes' => $forbiddenFiletypes));
    }

    /**
     * Method to get files in each modules
     *
     * @param string $module module name can be contact/users/admin
     *
     * @return JsonResponse
     */
    public function filesListAction($module)
    {
        $clubId = $this->container->get('club')->get('id');
        $directory = FgUtility::getUploadDir() . "/$clubId/$module";
        $clubDefaultLang = $this->container->get('club')->get('default_lang');
        switch ($module) {
            case 'contact':
                /* Files from contactfield_file/contactfield_image/contact_application_file */
                $files1 = glob($directory . '/{contactfield_file,contactfield_image,contact_application_file}/*.*', GLOB_BRACE);
                /* Files from ad/profilepic/companylogo */
                $files2 = glob($directory . "/{ad,companylogo,profilepic}/original/*.*", GLOB_BRACE);
                $files = array_merge($files1, $files2);
                break;
            case 'users':
                $files = glob($directory . '/{form_uploads,messages}/*.*', GLOB_BRACE);
                break;
            case 'admin':
                /* Files from clublogo/invoice_header/newsletter_header/website_bg/website_header */
                $files = glob($directory . "/{clublogo,invoice_header,newsletter_header,website_bg,website_header,federation_icon,website_settings,website_portrait}/*.*", GLOB_BRACE);
                break;
        }
        $filesArray = ($module == "documents") ? $this->getDocuments($clubId, $clubDefaultLang) : $this->getFilesStructure($files, $module);

        return new JsonResponse(array("iTotalRecords" => count($filesArray), 'iTotalDisplayRecords' => count($filesArray), 'aaData' => $filesArray));
    }
    
    /**
     * Method to get array of all documents in that club
     * 
     * @param int    $clubId          clubId
     * @param string $clubDefaultLang club's DefaultLang
     * 
     * @return array of all documents in that club
     */
    private function getDocuments($clubId, $clubDefaultLang)
    {
        $filesArray = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgDmDocuments')->getAllDocumentsForFilemanager($clubId, $clubDefaultLang);
        //add uploadPath to array
        foreach ($filesArray as $key => $detail) {
            $documentUploadFolder = FgUtility::getUploadFilePath($detail['ClubId'], 'documents');
            $filesArray[$key]['uploadPath'] = $documentUploadFolder . "/" . $detail['file'];
        }

        return $filesArray;
    }

    /**
     * Method to create array of (name/extension/filesize/updatedAt/updatedAt) of each files
     *
     * @param array  $files files array
     * @param string $module module name can be contact/users/admin
     *
     * @return array $filesArray file details array
     */
    private function getFilesStructure($files, $module)
    {
        $filesArray = array();
        foreach ($files as $file) {
            $path_parts = pathinfo($file);
            $filePathArray = explode('/', $file);
            $index = (array_search($module, $filePathArray) + 1); // $key of source;
            $sourceName = $filePathArray[$index];
            //for handling 2 types structures.  eg1: contact/companylogo/original/filename, eg2: users/messages/filename
            $fullPath = (in_array($sourceName, array('companylogo', 'profilepic', 'ad'))) ? $this->generateUrl('filemanager_download_contact_files', array('module' => $module, 'source' => $sourceName, 'name' => 'original', 'filename' => $path_parts['basename'])) : $this->generateUrl('filemanager_download_files', array('module' => $module, 'source' => $sourceName, 'name' => $path_parts['basename']));
            $filesArray[] = array('name' => $path_parts['basename'],
                'extension' => $path_parts['extension'],
                'filesize' => filesize($file),
                'updatedAt' => date("Y-m-d H:i", filemtime($file)),
                'source' => $sourceName,
                'fullPath' => $fullPath,
                'uploadPath' => substr($file, strpos($file, '/uploads/')));
        }

        return $filesArray;
    }

    /**
     * Function to get gallery data for listing
     *
     * @return JsonResponse
     */
    public function fileManagerGalleryDataAction()
    {
        $clubId = $this->container->get('club')->get('id');
        $result = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgGmItems')->getAllItemsForFilemanager($clubId, 'IMAGE');
        //include uploadPath in array
        foreach ($result as $key => $val) {
            $galleryUploadFolder = FgUtility::getUploadFilePath($val['club'], 'gallery');
            $result[$key]['uploadPath'] = "/".$galleryUploadFolder . '/width_100/'.$val['filepath'];
        }
        $output = array('iTotalRecords' => 0, 'iTotalDisplayRecords' => 0, 'aaData' => array());
        $output['iTotalRecords'] = count($result);
        $output['iTotalDisplayRecords'] = count($result);
        $output['aaData'] = $result;
        return new JsonResponse($output);
    }

    /**
     * Function to download files from gallery
     *
     */
    public function downloadGalleryFilesAction()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $checkedIds = $request->get('checkedIds');
        $zipFilename = $request->get('filename');
        $isZipFile = $request->get('isZipFile');
        $clubId = $this->container->get('club')->get('id');
        $result = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgGmItems')->getAllItemsForFilemanager($clubId, 'IMAGE', $checkedIds);
        $galleryFiles = array_map(function($a) {
            return $a['filepath'];
        }, $result);
        $galleryFilesFiltered = array_filter($galleryFiles);
        if ($isZipFile == '0') {
            $fileObj = new FgFileManager($this->container);
            return $fileObj->downloadFile($galleryFilesFiltered[0], $galleryFilesFiltered[0], $clubId . DIRECTORY_SEPARATOR . "gallery" . DIRECTORY_SEPARATOR . "original" . DIRECTORY_SEPARATOR);
        } else {
            $fileObj = new FgFileManager($this->container);
            $fileObj->setCwd("\uploads\\" . $clubId . "\gallery\original");
            $randomFilename = substr(md5(rand()), 0, 7) . '.zip';
            $fileObj->zipFiles($galleryFilesFiltered, $randomFilename);
            return $fileObj->downloadFile($randomFilename, $zipFilename, 'temp' . DIRECTORY_SEPARATOR);
        }
    }

    /**
     * Function to download files from admin as .zip
     *
     */
    public function downloadAdminFilesAction()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $zipFilename = $request->get('zipName');
        $checkedIds = $request->get('fileNames');
        $source = $request->get('source');
        $type = $request->get('type');
        $clubId = $this->container->get('club')->get('id');
        //if no files selected, download all files from admin folder
        if ($checkedIds === '') {
            $directory = FgUtility::getUploadDir() . "/$clubId/$type";
            switch ($type) {
                case 'admin':
                    $files = glob($directory . "/{clublogo,invoice_header,newsletter_header,website_bg,website_header,website_settings,website_portrait}/*.*", GLOB_BRACE);
                    break;
                case 'contact':
                    $files1 = glob($directory . '/{contactfield_file,contactfield_image}/*.*', GLOB_BRACE);
                    /* Files from ad/profilepic/companylogo */
                    $files2 = glob($directory . "/{ad,companylogo,profilepic}/original/*.*", GLOB_BRACE);
                    $files = array_merge($files1, $files2);
                    break;
                case 'users':
                    $files = glob($directory . '/{form_uploads,messages}/*.*', GLOB_BRACE);
                    break;
            }

            $adminFiles = $this->getFilesStructure($files, $type);
            $splitter = '';
            foreach ($adminFiles as $file) {
                $checkedIds .= $splitter . $file['name'];
                $source .= $splitter . $file['source'];
                $splitter = ',';
            }
        }
        $result = explode(',', $checkedIds);
        $sourceName = explode(',', $source);

        $checkingFolder = array('ad', 'profilepic', 'companylogo');
        foreach ($result as $key => $fileName) {
            if (($type == 'contact') && ( in_array($sourceName[$key], $checkingFolder))) {
                $inputFiles[] = $sourceName[$key] . DIRECTORY_SEPARATOR . 'original' . DIRECTORY_SEPARATOR . $fileName;
            } else {
                $inputFiles[] = $sourceName[$key] . DIRECTORY_SEPARATOR . $fileName;
            }
        }
        $adminFilesFiltered = array_filter($adminFiles);
        $fileObj = new FgFileManager($this->container);
        switch ($type) {
            case 'admin':
                $fileObj->setCwd("\uploads\\" . $clubId . "\admin");
                break;
            case 'contact':
                $fileObj->setCwd("\uploads\\" . $clubId . "\contact");
                break;
            case 'users':
                $fileObj->setCwd("\uploads\\" . $clubId . "\users");
                break;
        }
        //Temporary filename for zip file
        $tempFilename = date_timestamp_get(date_create()) . '.zip';
        $fileObj->zipFiles($inputFiles, $tempFilename);
        return $fileObj->downloadFile($tempFilename, $zipFilename, 'temp' . DIRECTORY_SEPARATOR);
    }

    /**
     * Template for Filemanager action menu modal popups
     *
     * @param Request $request Request Object
     *
     * @return Template
     */
    public function modalPopupAction(Request $request)
    {
        $popupTitle = $this->get('translator')->trans('FILE_DOWNLOAD_ZIP_TITLE');
        $popupText = 'popupText';
        $checkedIds = $request->get('checkedIds'); //selected ids(comma separeted)
        $selected = $request->get('selected');  //can be selected/all
        $modalType = $request->get('modalType');  //can be gallery,admin etc
        $params = $request->get('params');  //json data of current status like currentScope
        $source = $request->get('source'); //source folder in case of admin
        $buttonSave = $this->get('translator')->trans('DOWNLOAD');

        $return = array("title" => $popupTitle, 'text' => $popupText, 'checkedIds' => $checkedIds, 'modalType' => $modalType, 'params' => $params, 'button_val' => $buttonSave, 'source' => $source);
        return $this->render('CommonFilemanagerBundle:FileManager:modalPopup.html.twig', $return);
    }

    /**
     * Method to create download zip popup
     *
     * @param array  $files files array
     *
     * @return JsonResponse
     */
    public function filedownloadZipAction($type)
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $filearray = $request->get('fileIds');
        $searchValue = $request->get('searchValue');
        $clubId = $this->container->get('club')->get('id');

        if ($filearray == '') {
            $filearray = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgFileManager')->getAllDownloaddata($clubId, $type, $searchValue);
        }
        $return = array('text' => $this->get('translator')->trans('FILE_DOWNLOAD_ZIP_TEXT'), 'button_val' => $this->get('translator')->trans('DOWNLOAD_ZIP_FILE'),
            'title' => $this->get('translator')->trans('FILE_DOWNLOAD_ZIP_TITLE'), 'filearray' => $filearray);

        return $this->render('CommonFilemanagerBundle:FileManager:downloadZipfilePopup.html.twig', $return);
    }

    /**
     * Method to zip download selected files
     *
     * @param array  $filename selected file names
     *
     * @return JsonResponse
     */
    public function zipDownloadAction()
    {        
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $type = $request->get('type');
        $filename = $request->get('filename');
        $selectedId = $request->get('fieldIds');
        $result = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgFileManager')->getFileArrayName($selectedId);
        $inputfileArray = array();
        $originalnameArray = array();
        foreach ($result as $key => $fileName) {
            array_push($inputfileArray, $fileName['encryptedFilename']);
            array_push($originalnameArray, $fileName['originalFileName']);
        }

        $file = rand(0, 1000);
        $fileObj = new FgFileManager($this->container);
        $originalnameArray = $this->changeDuplicateFileName($originalnameArray);
        $zip = $fileObj->zipFiles($inputfileArray, $file . '.zip', false, $originalnameArray);
        $response = $fileObj->downloadFile($file . '.zip', $filename, 'temp' . DIRECTORY_SEPARATOR);

        return $response;
    }

    /**
     * Method to download files of modules contact/users/admin
     *
     * @param string $module   contact/users/admin
     * @param string $source   source folder name
     * @param string $name     filename or folder name(original)
     * @param string $filename filename (it can be null)
     *
     * @return download dialog box
     */
    public function downloadFilesAction($module, $source, $name, $filename = '')
    {
        $fileObj = new FgFileManager($this->container);
        $clubId = $this->container->get('club')->get('id');
        //for handling 2 types structures.  eg1: contact/companylogo/original/filename, eg2: users/messages/filename
        //in 2nd case filename is null
        if ($filename == '') {
            $fileLocation = rtrim($clubId . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . $source . DIRECTORY_SEPARATOR);
            $downloadFileName = $originalFileName = $name;
            //for handling message attachments
            if ($source == 'messages') {
                $filenameArray = explode('~~__~~', $name);
                $originalFileName = $filenameArray[1];
            }
        } else {
            $fileLocation = rtrim($clubId . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . $source . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR);
            $downloadFileName = $originalFileName = $filename;
        }
        $response = $fileObj->downloadFile($downloadFileName, $originalFileName, $fileLocation);

        return $response;
    }

    public function documentsZipAction()
    {        
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $checkedIds = $request->get('checkedIds');
        $zipFilename = $request->get('filename');
        $clubId = $this->container->get('club')->get('id');
        $clubDefaultLang = $this->container->get('club')->get('default_lang');
        $result = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgDmDocuments')->getAllDocumentsForFilemanager($clubId, $clubDefaultLang, $checkedIds);

        $documentFiles = array_map(function($a) {
            return $a['file'];
        }, $result);

        $documentOriginalFiles = array_map(function($a) {
            return $a['filename'];
        }, $result);

        $fileObj = new FgFileManager($this->container);

        $fileObj->setCwd(DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . $clubId . DIRECTORY_SEPARATOR . "documents");
        $randomFilename = substr(md5(rand()), 0, 7) . '.zip';
        $fileObj->zipFiles($documentFiles, $randomFilename, false, $documentOriginalFiles);
        return $fileObj->downloadFile($randomFilename, $zipFilename, 'temp' . DIRECTORY_SEPARATOR);
    }

    /**
     * Method to mark delete file
     *
     * @param array  $files files array
     *
     * @return JsonResponse
     */
    public function markForDeleteAction()
    {        
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $filearray = $request->get('fileIds');
        $contactId = $this->container->get('contact')->get('id');
        $searchValue = $request->get('searchValue');
        $type = 'delete';
        $clubId = $this->container->get('club')->get('id');
        if ($filearray == '') {
            $filearray = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgFileManager')->getAllFileId($clubId, $searchValue);
        }
        $result = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgFileManager')->fileMarkForDelete($filearray);
        $log = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgFileManagerLog')->logDetailEntry($filearray, $contactId, $type);

        return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('FILE_DELETE_SUCCESS')));
    }

    /**
     * Method to restore  deleted file
     *
     * @param array  $files files array
     *
     * @return JsonResponse
     */
    public function restoreFileAction()
    {        
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $filearray = $request->get('fileIds');
        $contactId = $this->container->get('contact')->get('id');
        $type = 'restore';
        $searchValue = $request->get('searchValue');
        $clubId = $this->container->get('club')->get('id');
        if ($filearray == '') {
            $filearray = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgFileManager')->getAllFileId($clubId, $searchValue);
        }
        $result = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgFileManager')->restoreMarkedFile($filearray);
        $log = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgFileManagerLog')->logDetailEntry($filearray, $contactId, $type);
        $return = array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('FILE_RESTORE_SUCCESS'));

        return new JsonResponse($return);
    }

    /**
     * Method to show delete file popup
     *
     * @param Request $request Request Object
     *
     * @return JsonResponse
     */
    public function deleteFilePopupAction(Request $request)
    {
        $filearray = $request->get('fileIds');
        $Arraycount = count(explode(",", $filearray));

        if ($Arraycount == 1) {
            $popupTitle = $this->get('translator')->trans('FILE_DELETE_POPUP_TITLE_SINGLE');
            $popupText = $this->get('translator')->trans('FILE_DELETE_POPUP_TEXT_SINGLE');
        } else {
            $popupTitle = $this->get('translator')->trans('FILE_DELETE_POPUP_TITLE_MULTIPLE');
            $popupText = $this->get('translator')->trans('FILE_DELETE_POPUP_TEXT_MULTIPLE');
        }

        $return = array('text' => $popupText, 'button_val' => $this->get('translator')->trans('DELETE_FILE_BUTTON_TEXT'),
            'title' => $popupTitle, 'filearray' => $filearray);

        return $this->render('CommonFilemanagerBundle:FileManager:deleteFilePopup.html.twig', $return);
    }

    /**
     * Method to delete selected files
     *
     * @param Request $request Request Object
     *
     * @return JsonResponse
     */
    public function deleteFileAction(Request $request)
    {
        $filearray = $request->get('fileIds');
        $clubId = $this->container->get('club')->get('id');

        if ($filearray == '') {
            $filearray = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgFileManager')->getAllFileId($clubId, '');
        }

        $result = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgFileManager')->fileDelete($filearray, $clubId);

        return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('FILE_DELETE_SUCCESS')));
    }

    /**
     * Function to change duplicate filenames from an array(like filename.jpg, filename(1).jpg ...)
     * @param Array $fileNames Description
     * @return Array $res
     */
    private function changeDuplicateFileName($fileNames)
    {
        $z = array();
        $res = array();
        foreach ($fileNames as $val) {
            if (!isset($z[md5($val)])) {
                $z[md5($val)] = 1;
                $res[] = $val;
            } else {
                $z[md5($val)]+=1;
                $path_parts = pathinfo($val);
                $res[] = $path_parts['filename'] . '(' . ($z[md5($val)] - 1) . ')' . '.' . $path_parts['extension'];
            }
        }
        return $res;
    }

    /**
     * Function  to push file type and upload path into an array
     * 
     * @param Array $existingFiles
     * 
     * @return Array $existingFiles
     */
    private function includeFileType($existingFiles)
    {
        foreach ($existingFiles as $key => $val) {
            $path = 'uploads/' . $val['clubId'] . '/content/';
            $existingFiles[$key]['fileType'] = pathinfo($path . $val['encryptedName'], PATHINFO_EXTENSION);
            $existingFiles[$key]['uploadPath'] = "/".$path . $val['encryptedName'];
        }
        return $existingFiles;
    }
}
