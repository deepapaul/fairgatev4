<?php

namespace Clubadmin\Classes;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Common\UtilityBundle\Util\FgUtility;
use Symfony\Component\HttpFoundation\File\File;

/**
 * For image upload
 */
class FgFileUploadHandler {

    protected $options;
    protected $errorMessages;
    protected $request;
    protected $container;
    protected $fileType;

    /**
     * initialisation
     * @param object $request   Request object
     * @param object $container Container object
     */
    function __construct($request, $container) {
        $this->request = $request;
        $this->container = $container;
        $this->setDefaultOptions();
    }

    /**
     * set upload options array
     *
     * @param type $options
     */
    public function setOptions($options) {
        $this->options = $options + $this->options;
    }

    /**
     * set error messages
     *
     * @param type $errorMessages
     */
    public function setErrorMessages($errorMessages) {
        $this->errorMessages = $errorMessages + $this->errorMessages;
    }

    /**
     * Setting the type of file which is allowed (image,doc etc).
     * 
     * @param string $fileType Type of file
     */
    public function setFileType($fileType) {
        $this->fileType = $fileType;
    }

    /**
     * initialize upload
     *
     * @return type
     */
    public function initialize() {
        if ($this->request->getMethod() == 'POST') {
            return $this->uploadFiles();
        }
    }

    /**
     * upload files to upload_dir
     *
     * @return type
     */
    private function uploadFiles() {        
        $avastScan = $this->container->getParameter('avast_scan');
        $uploadDirectory = ($avastScan) ? $this->container->getParameter('avast_scan_upload_folder') : FgUtility::getUploadDir() . "/temp/";
         $i=0;
        foreach ($this->request->files as $cat => $file) {
            $type = $file->getMimeType();
            $fileName = $file->getFileName();
            $extin = strtolower(end(explode('.', $this->request->get('title'))));
            // Allow only image files if image type is set.
            if($this->fileType =='image' && !in_array($extin, array('gif', 'png', 'jpeg', 'jpg', 'bmp')) ) {
               return array('status' => 'error', 'filename' =>  $file->getClientOriginalName(),  'type' => $file->getMimeType(), 'error' => $this->errorMessages['invalidType']);  
            }    
            if (in_array($extin, array('gif', 'png', 'jpeg', 'jpg', 'bmp')) && !in_array($file->getMimeType(), $this->container->getParameter('image_mime_types'))) {
                return array('status' => 'error', 'filename' =>  $file->getClientOriginalName(),  'type' => $file->getMimeType(), 'error' => $this->errorMessages['invalidType']);
            } 
                      
            //prevent forbiddenFiletypes file type
            $forbiddenFiletypes = $this->container->getParameter('forbiddenFiletypes');
            //set path of the upload directory depends on the avast scan check
            if ($avastScan) {
                $this->options['upload_dir'] = $this->container->getParameter('avast_scan_upload_folder');
            }
            //checking for file chunk
            if ($this->request->server->get('HTTP_CONTENT_RANGE')) {
                $contentRangeHeader = $this->request->server->get('HTTP_CONTENT_RANGE');
                $contentRange = $contentRangeHeader ? preg_split('/[^0-9]+/', $contentRangeHeader) : null;
                //file_put_contents($this->options['upload_dir'].$this->request->get('title'), fopen($file->getPathName(), 'r'),FILE_APPEND);
            } else {
                file_put_contents('filename.txt',$this->request->get('title').$i."filetype:".$this->fileType);               
                $file->move($this->options['upload_dir'], $this->request->get('title'));
            }

            //all file related checking(mimetype, extension and virus ) is done through below service
            $fileCheckStatus = $this->container->get('fg.avatar')->isForbidden($uploadDirectory, $this->request->get('title'), $extin); 
            if (count($fileCheckStatus) > 1) {
                $error = $this->errorMessages['invalidType'];
                if (($fileCheckStatus['error'] == 'INVALID_VIRUS_FILE') || ($fileCheckStatus['error'] == 'VIRUS_FILE_CONTACT')) {
                    $error = $this->container->get('translator')->trans('VIRUS_FILE_CONTACT') ;
                }
                unlink($uploadDirectory . $file->getFileName());
                return array('status' => 'error', 'type' => $extin, 'error' => $error, 'filename' =>  $file->getClientOriginalName());
            } else if ($avastScan && $fileCheckStatus['checking']===true &&  file_exists($uploadDirectory .$this->request->get('title'))) {
                //only working if antivirus checking is enabled
               
                $attachmentObj = new File($uploadDirectory . $this->request->get('title'), false);
                $attachmentObj->move(FgUtility::getUploadDir() . "/temp/", $this->request->get('title'));
            }
        }
        //$file=$this->request->files->get('upl');
        return array('status' => 'success', 'type' => end(explode('.', $this->request->get('title'))), 'name' => $this->request->get('fileName'));
    }

    /**
     * function to set default upload options
     */
    private function setDefaultOptions() {
        $this->options = array(
            //'script_url' => $this->get_full_url().'/',
            'upload_dir' => 'uploads/temp/',
            // 'upload_url' => $this->get_full_url().'/files/',
            'user_dirs' => false,
            'mkdir_mode' => 0755,
            'param_name' => 'files',
            // Read files in chunks to avoid memory limits when download_via_php
            // is enabled, set to 0 to disable chunked reading of files:
            'readfile_chunk_size' => 10 * 1024 * 1024, // 10 MiB
            // Defines which files can be displayed inline when downloaded:
            'inline_file_types' => '/\.(gif|jpe?g|png)$/i',
            // The php.ini settings upload_max_filesize and post_max_size
            // take precedence over the following max_file_size setting:
            'max_file_size' => null,
            'min_file_size' => 1,
            // The maximum number of files for the upload directory:
            'max_number_of_files' => null,
            // Defines which files are handled as image files:
            'image_file_types' => '/\.(gif|jpe?g|png)$/i',
            //array mime types default php,js,shell files
            'fileTypes' => array('text/php', 'text/x-php', 'application/php', 'application/x-shar', 'application/x-csh',
                'application/x-php', 'application/x-httpd-php', 'application/x-httpd-php-source', 'application/javascript', 'text/javascript', 'application/x-sh'),
            //file type checking 0=> no check, 'include' => in array, 'exclude'
            'typeCheck' => 'exclude',
            // Image resolution restrictions:
            'max_width' => null,
            'max_height' => null,
            'min_width' => 1,
            'min_height' => 1,
            // Set the following option to false to enable resumable uploads:
            'discard_aborted_uploads' => true,
            // Set to 0 to use the GD library to scale and orient images,
            // set to 1 to use imagick (if installed, falls back to GD),
            // set to 2 to use the ImageMagick convert binary directly:
            'image_library' => 1
        );
        $this->setErrorMessage();
        $this->fileType = 'all';
    }

    /**
     * function to set default error message
     */
    private function setErrorMessage() {
        $errorMessages = array();
        $errorMessages['invalidType'] = $this->container->get('translator')->trans('FILEMANAGER_UPLOAD_FILETYPE_ERROR');
        $errorMessages['INVALID_VIRUS_FILE'] = $this->container->get('translator')->trans('INVALID_VIRUS_FILE');
        $errorMessages['VIRUS_FILE_CONTACT'] = $this->container->get('translator')->trans('VIRUS_FILE_CONTACT');
        $this->errorMessages = $errorMessages;
    }

}
