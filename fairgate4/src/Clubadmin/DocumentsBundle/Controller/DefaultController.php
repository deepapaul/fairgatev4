<?php

namespace Clubadmin\DocumentsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Clubadmin\Classes\FgFileUploadHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
/**
 * DefaultController
 *
 * This controller handles common functionalities like file upload of document mangement 
 *
 * @package    ClubadminDocumentsBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
class DefaultController extends Controller
{
    /**
     * Function to handle file upload
     *
     * @param Request $request Request object with file to upload
     */
    public function uploadFileAction(Request $request)
    {
        $upload = new FgFileUploadHandler($request, $this->container);
        //set custom error messages.
        $upload->setErrorMessages(array('invalidType' => $this->get('translator')->trans('FILEMANAGER_UPLOAD_FILETYPE_ERROR')));

        return new JsonResponse($upload->initialize());
    }
}
