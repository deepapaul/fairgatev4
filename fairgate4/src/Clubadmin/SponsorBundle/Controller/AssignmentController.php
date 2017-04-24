<?php

/**
 * Service Controller.
 *
 * This controller was created for handling services in Sponsor management.
 *
 * @author     pitsolutions.ch
 *
 * @version    Fairgate V4
 */
namespace Clubadmin\SponsorBundle\Controller;

use Common\UtilityBundle\Controller\FgController as ParentController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Clubadmin\ContactBundle\Util\ContactlistData;
use Clubadmin\SponsorBundle\Util\Servicelist;
use Clubadmin\Classes\Contactdatatable;
use Symfony\Component\HttpFoundation\Request;

/**
 * This controller was created for handling service assignment list of club .
 *
 * @author pitsolutions.ch
 */
class AssignmentController extends ParentController
{

    /**
     * To get the assignment details of contact(active/future/past/recently deleted).
     *
     * @return JsonResponse
     */
    public function getAssignmentlistAction(Request $request)
    {
        $sponsorlistData = new ContactlistData($this->contactId, $this->container, 'servicelist');
        $servicelistData = new Servicelist($this->container);
        $servicelistData->tabType = $request->get('assignmentType', 'active_assignments');
        $servicelistData->searchval = $request->get('search', '');

        $jsonColumns = $servicelistData->getServicetableColumns();
        $sponsorlistData->tabledata = json_decode($jsonColumns, true);
        $tableColumns = $sponsorlistData->getTableColumns();
        $servicelistData->columns = $tableColumns;
        $this->servicelistinitialSetting($servicelistData);
        $listQuery = $servicelistData->getResult();
        //file_put_contents('q1.txt', $listQuery);
        $result = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($listQuery);
        //collect total number of records
        $totalrecords = count($result);
        //For set the datatable json array
        $output = array('iTotalRecords' => $totalrecords, 'iTotalDisplayRecords' => $totalrecords, 'aaData' => array());
        $contactDatatabledata = new Contactdatatable($this->container, $this->get('club'));
        $resultdata = $contactDatatabledata->iterateDataTableData($result, $this->container->getParameter('country_fields'), $sponsorlistData->tabledata);
        $output['aaData'] = $resultdata;

        return new JsonResponse($output);
    }

    /**
     * To initialize servicelist settings.
     *
     * @param Object $servicelistData servicelist class object
     */
    private function servicelistinitialSetting($servicelistData)
    {
        $servicelistData->setFrom();
        $servicelistData->setColumns();
        $servicelistData->setCondition();
    }
}
