<?php

/**
 * ApiLogController
 * This controller is used to list the access log of all Api service.
 * @package    CommonUtilityBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
namespace Clubadmin\GeneralBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Common\UtilityBundle\Util\FgSettings;
use Common\UtilityBundle\Util\FgPermissions;

class ApiLogController extends FgController
{

    /**
     * This method is used to book GotCourts api service.
     * 
     * @return Template
     */
    public function accesslogAction()
    {
        $clubId = $this->container->get('club')->get('id');
        $contactId = $this->container->get('contact')->get('id');
        $isAdmin = $this->container->get('contact')->get('isSuperAdmin');
        if (!$isAdmin) {
            $permissionObj = new FgPermissions($this->container);
            $permissionObj->checkUserAccess(0, 'virusLog');
        }

        $breadCrumb = array('breadCrumb' => array('back' => $this->generateUrl('filemanager_listModuleFiles')));
        $responseCode = array(401, 403, 404, 409, 500);

        return $this->render('ClubadminGeneralBundle:ApiLog:accesslog.html.twig', array('breadCrumb' => $breadCrumb, 'responseCode' => $responseCode));
    }

    /**
     * This method is used to generate GotCourts api token.
     * 
     * @param Request $request Request Object
     * 
     * @return JsonResponse
     */
    public function getLogsAction(Request $request)
    {
        $clubObj = $this->container->get('club');
        $filterArray['apiType'] = 2; //Id for gotcout api only, need to change it when api's selectpicker is added
        
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
        
        $columnData = $request->get('columns');
        $orderData = $request->get('order');
        $orderColumnRef = $orderData[0]['column'];
        if ($orderColumnRef != '') {
            $filterArray['orderColumn'] = $columnData[$orderColumnRef]['name'];
            $filterArray['orderColumnDirection'] = $orderData[0]['dir'];
        }
        
        if($request->get('resCode')){
            $filterArray['resCode'] = $request->get('resCode'); 
        }
        
        $filterArray['limitStart'] = $request->get('start', 0);
        $filterArray['limitLength'] = $request->get('length', 20);
        
        $logList = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgApiAccesslog')->getAccessLog($filterArray, $clubObj->get('default_lang'));
        $totalCount = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgApiAccesslog')->getAccessLogCount($filterArray);

        $return['aaData'] = $logList;
        $return['iTotalDisplayRecords'] = $totalCount['totalCount'];
        $return['iTotalRecords'] = $totalCount['totalCount'];

        return new JsonResponse($return);
        
    }
}
