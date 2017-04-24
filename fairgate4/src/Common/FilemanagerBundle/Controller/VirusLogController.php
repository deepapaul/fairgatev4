<?php

/**
 * Viruslog Controller
 *
 * This controller is used for listing the virus logs
 *
 * @package    CommonFilemanagerBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
namespace Common\FilemanagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Common\UtilityBundle\Util\FgSettings;
use Common\UtilityBundle\Util\FgPermissions;

class VirusLogController extends Controller
{

    /**
     * Method to view the virus logs.
     *
     *
     * @return object View Template Render Object
     */
    public function indexAction()
    {
        $isAdmin = $this->container->get('contact')->get('isSuperAdmin');
        if (!$isAdmin) {
            $permissionObj = new FgPermissions($this->container);
            $permissionObj->checkUserAccess(0, 'virusLog');
        }

        $statusTranslationArray['all'] = $this->get('translator')->trans('FILE_RESPONSE_STATUS_ALL');
        $statusTranslationArray['safe'] = $this->get('translator')->trans('FILE_RESPONSE_STATUS_SAFE');
        $statusTranslationArray['unsafe'] = $this->get('translator')->trans('FILE_RESPONSE_STATUS_UNSAFE');
        $statusTranslationArray['exception'] = $this->get('translator')->trans('FILE_RESPONSE_STATUS_EXCEPTION');
        $statusTranslationArray['not_responding'] = $this->get('translator')->trans('FILE_RESPONSE_STATUS_NOTRESPONDING');

        $breadCrumb = array('breadCrumb' => array('back' => $this->generateUrl('filemanager_listModuleFiles')));

        return $this->render('CommonFilemanagerBundle:FileManager:virusLog.html.twig', array('breadCrumb' => $breadCrumb, 'statusTranslationArray' => $statusTranslationArray));
    }

    /**
     * Method to view the virus logs.
     *
     *
     * @return JSON Objectt
     */
    public function listAction()
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->container->get('request_stack')->getCurrentRequest();

        $columnData = $request->get('columns');
        $orderData = $request->get('order');
        $orderColumnRef = $orderData[0]['column'];
        if ($orderColumnRef != '') {
            $filterArray['orderColumn'] = $columnData[$orderColumnRef]['name'];
            $filterArray['orderColumnDirection'] = $orderData[0]['dir'];
        }

        $startDate = $request->get('startDate');
        if ($startDate != '') {
            $date = new \DateTime();
            $filterArray['startDate'] = $date->createFromFormat(FgSettings::getPhpDateFormat(), $startDate)->format('Y-m-d');
            $filterArray['startDate'] = $filterArray['startDate'] . ' 00:00:00';
        }

        $endDate = $request->get('endDate');
        if ($endDate != '') {
            $date = new \DateTime();
            $filterArray['endDate'] = $date->createFromFormat(FgSettings::getPhpDateFormat(), $endDate)->format('Y-m-d');
            $filterArray['endDate'] = $filterArray['endDate'] . ' 23:59:59';
        }

        $status = $request->get('status');
        if (is_array($status)) {
            if (($key = array_search('all', $status)) !== false) {
                unset($status[$key]);
            }
            if (count($status) > 0) {
                $filterArray['responseStatus'] = $status;
            }
        }

        $filterArray['limitStart'] = $request->get('start', 0);
        $filterArray['limitLength'] = $request->get('length', 20);

        $logList = $em->getRepository('CommonUtilityBundle:FgFileManagerViruscheckLog')->getVirusLogs($filterArray);
        $totalCount = $em->getRepository('CommonUtilityBundle:FgFileManagerViruscheckLog')->getVirusLogCount($filterArray);

        $return['aaData'] = $logList;
        $return['iTotalDisplayRecords'] = $totalCount['totalCount'];
        $return['iTotalRecords'] = $totalCount['totalCount'];

        return new JsonResponse($return);
    }
    
}