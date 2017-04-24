<?php

namespace Common\FilemanagerBundle\Util;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * File Manager
 */
class FgFileManager
{
    /**
     * Container variable
     */
    public $container;
    /**
     * @var string content dir path
     */
    public $cwd;
    /**
     * @var string directory seperator
     */
    private $DS = DIRECTORY_SEPARATOR;
    /**
     * Constructor for initial setting.
     *
     * @param object $container Container Object
     */
    public function __construct($container) {
        $this->container = $container;
        $this->em = $this->container->get('doctrine')->getManager();
        $this->cwd=$this->getCwd();
    }
    /**
     * Function to zip selected files
     * @param type $input_files
     * @param type $destination
     * @return type
     */
    public function zipFiles($input_files, $zipName,$location=false, $replace_filename){
        if (!extension_loaded('zip')) {
            exit('Zip PHP module is not installed on this server');
        }
        if(!$location){
            $location="{$this->DS}uploads{$this->DS}temp";
        }
       $destination = getcwd().$location.$this->DS.$zipName;
        if (substr($destination, -4, 4) != '.zip'){
                $destination = $destination.'.zip';
        }

        $zip = new \ZipArchive();
        if (!$zip->open($destination, \ZIPARCHIVE::CREATE)) {
            exit('Archive could not be created');
        }
        $startdir = str_replace('\\', '/', $this->cwd);
        foreach ($input_files as $key => $source){
                $source = $this->cwd.$this->DS.$source;
                $originalName = isset($replace_filename[$key])?$replace_filename[$key]:basename($source);

                $source = str_replace('\\', '/', $source);
                if(!file_exists($source)){
                    continue;
                }
                if (is_dir($source) === true) {
                    $subdir = str_replace($startdir.'/', '', $source) . '/';
                    $zip->addEmptyDir($subdir);
                    $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source), \RecursiveIteratorIterator::SELF_FIRST);
                    foreach ($files as $file) {
                        $file = str_replace('\\', '/', $file);
                        // Ignore "." and ".." folders
                        if( in_array(substr($file, strrpos($file, '/')+1), array('.', '..')) ){
                            continue;
                        }
                        if (is_dir($file) === true) {
                            $zip->addEmptyDir($subdir . str_replace($source . '/', '', $file . '/'));
                        }
                        else if (is_file($file) === true) {
                            $zip->addFile($file, $subdir . str_replace($source . '/', '', $file));
                        }
                    }
                }
                else if (is_file($source) === true) {
                    //$originalName = utf8_decode($originalName);

                    $originalName = iconv("UTF-8","ISO-8859-1//TRANSLIT",$originalName);
                    $zip->addFile($source, $originalName);
                }
        }
        $zip->close();

        return;
    }

    /**
     *
     * @param String $downloadFile
     * @param String $originalFileName  The original name of the file to which download happens
     * @param String $fileLocation      The folder where the image is saved
     */
    public function downloadFile($downloadFileName, $originalFileName = '', $fileLocation = '', $mimeType = 'application/octet-stream', $inline = false){
        // download file
        if($originalFileName == ''){
            $originalFileName = $downloadFileName;
        }

        $baseUploadDir = $this->getUploadRealpath();
        $downloadFile = $baseUploadDir.$fileLocation.$downloadFileName;
        $originalFileName = $this->filename($originalFileName);

        if(file_exists($downloadFile)){
            $options = array(
                            'serve_filename' => $originalFileName,
                            'absolute_path' => true,
                            'inline' => $inline,
                            'factory' => 'xsendfile',
                        );

            try{
                $response = $this->container->get('common_file_serve.response_factory')
                                            ->create($downloadFile, $mimeType, $options);
                return $response;
            } catch (Exception $ex) {
                 throw new NotFoundHttpException("File $downloadFile Not Found");
            }

        } else {
          throw  new NotFoundHttpException("File $downloadFile Not Found");
        }
    }


    /**
     *
     * @param int $fileId
     * @param String $fileLocation  The folder where the image is saved
     */
    public function downloadFileById($fileId, $fileLocation, $inline){
        $fileDetails = $this->em->getRepository('CommonUtilityBundle:FgFileManager')->getFileForDownloadById($fileId);
        return $this->downloadFile($fileDetails['encryptedFilename'], $fileDetails['originalFileName'], $fileLocation, 'application/octet-stream', $inline);
    }

    /**
     *
     * @param String $downloadFile
     * @param String $originalFileName  The original name of the file to which download happens
     * @param String $fileLocation      The folder where the image is saved
     */
    public function downloadFileByName($virtualName, $fileLocation, $inline){
        $fileDetails = $this->em->getRepository('CommonUtilityBundle:FgFileManager')->getFileForDownloadByName($virtualName);
        return $this->downloadFile($fileDetails['encryptedFilename'], $fileDetails['originalFileName'], $fileLocation, 'application/octet-stream', $inline);
    }


    /**
     *
     * filter user's input
     */
    public function filterInput($string, $strict = true){
            // bad chars
            $strip = array("..", "*", "\n");

            // we need this sometimes
            if ($strict){
                array_push($strip, "/", "\\");
            }
            $clean = trim(str_replace($strip, "_", strip_tags($string)));

            return $clean;
    }
    /**
     *
     * @return string
     */
    public function getCwd($absalutePath=true){
        $path = ($absalutePath) ? getcwd():'';

        return $path."{$this->DS}uploads{$this->DS}".$this->container->get('club')->get('id')."{$this->DS}content";
    }
    /**
     * Function to set current working directory
     * @param $path
     */
    public function setCwd($path=false){
        if(!$path){
            $path="{$this->DS}uploads{$this->DS}";
        }
        $this->cwd = getcwd().$path;
    }

    /**
     *
     * The function to get the real path of the upload directory
     *
     * return String
     */
    private function getUploadRealpath(){
        $DS = $this->DS;
        return realpath('').$DS."uploads".$DS;
    }

    /**
     * purify filename by removing unsuported filesistem chars: \ / : * ? " < > |
     * @param  varchar $fielname
     * @return varchar
     */
    private function filename($filename)
    {
        // replace more spaces with empty
        $filename = preg_replace('/\s/', '', $filename);
        // replace not allowed chars
        $filename = trim(preg_replace('/[\\\\\/:\*\?"<>|\n\r\t]/', '', $filename));
        return $filename;
    }
}