<?php

/**
 * File manager Document Controller
 *
 * This controller was created for managing the document listing in file manager
 *
 * @package    CommonFilemanagerBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */

namespace Common\FilemanagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class FileDocumentsController extends Controller
{

    /**
     * Function to show documents datatable details in file manager
     *
     * @return template
     */
    public function documentListAction()
    {
        $breadCrumb = array('breadcrumb_data' => array());
        $clubId = $this->container->get('club')->get('id');

        return $this->render('CommonFilemanagerBundle:FileManager:fileMangerDocuments.html.twig', array('breadCrumb' => $breadCrumb, 'clubId' => $clubId));
    }

    /**
     * Function to get documents data for listing it in documents datatable
     *
     * @return JsonResponse
     */
    public function fileManagerDocumentsDataAction()
    {
        $clubId = $this->container->get('club')->get('id');
        $result = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgDmDocuments')->getAllDocumentsForFilemanager($clubId);
        $output = array('iTotalRecords' => 0, 'iTotalDisplayRecords' => 0, 'aaData' => array());
        $output['iTotalRecords'] = count($result);
        $output['iTotalDisplayRecords'] = count($result);
        $output['aaData'] = $result;

        return new JsonResponse($output);
    }

}
