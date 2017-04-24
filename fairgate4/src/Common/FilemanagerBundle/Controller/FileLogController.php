<?php

/**
 * File manager log Controller
 *
 * This controller was created for managing the listing of logentries for each file
 *
 * @package    CommonFilemanagerBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */

namespace Common\FilemanagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class FileLogController extends Controller {
    
    /**
     * Function to display filemanager log
     *
     * @param int $filemanagerId filemanager id
     *
     * @return template
     */
    public function fileLogAction($filemanagerId) { 
        
        $fileName = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgFileManager')->getFilename($filemanagerId);
        $backLink = $this->generateUrl('filemanager_listModuleFiles');
        $breadCrumb = array(
            'breadcrumb_data' => array(),
            'back' => $backLink,
        );
        $transArr = $this->getTranslations();
        
        return $this->render('CommonFilemanagerBundle:FileManager:fileManagerLog.html.twig', array('breadCrumb' => $breadCrumb, 'filemanagerId' => $filemanagerId,'filename' => $fileName, 'transArray' => json_encode($transArr)));
    }
    /**
     * Function to get file log entries
     *
     * @param int $filemanagerId filemanager id
     *
     * @return json response
     */
    public function getFileLogDataAction($filemanagerId) {
        
        $logEntries = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgFileManagerLog')->getFileManagerLogData($filemanagerId, $this->container);
        $return['aaData'] = $logEntries;
        $return['iTotalDisplayRecords'] = count($logEntries);
        $return['iTotalRecords'] = count($logEntries);

        return new JsonResponse($return);
    }
    /**
     * Function to get translation of file status
     *
     * @return array
     */
    private function getTranslations(){ 
        
        $transArr = array(
                    'Added' => $this->get('translator')->trans('FILE_LOG_FLAG_ADDED'),
                    'Changed' => $this->get('translator')->trans('FILE_LOG_FLAG_CHANGED'),
                    'Replaced' => $this->get('translator')->trans('FILE_LOG_FLAG_REPLACED'),
                    'Flagged' => $this->get('translator')->trans('FILE_LOG_FLAG_FLAGGED'),
                    'Reverted' => $this->get('translator')->trans('FILE_LOG_FLAG_RESTORED'),
                );
        
        return $transArr;
    }

}
