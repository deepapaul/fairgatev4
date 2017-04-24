<?php

namespace Clubadmin\CommunicationBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Common\UtilityBundle\Util\FgUtility;
use Symfony\Component\HttpFoundation\Request;
use Common\UtilityBundle\Repository\Pdo\ClubPdo;

/**
 * MandatoryRecipientsController.
 *
 * This controller was created for handling Recipients List in Communication.
 *
 * @author     pitsolutions.ch
 *
 * @version    Fairgate V4
 */
class MandatoryRecipientsController extends FgController
{

    /**
     * This action is used for listing Recipients List.
     *
     * @param int $filterId filter id
     *
     * @return array Data array.
     */
    public function indexAction($filterId)
    {
        $backLink = $this->generateUrl('recipents_list');
        $breadCrumb = array(
            'breadcrumb_data' => array(),
            'back' => $backLink,
        );
        $resList = $this->em->getRepository('CommonUtilityBundle:FgCnRecepients')->getRecipientInClub($filterId, $this->clubId);
        $isAccess = count($resList) > 0 ? 1 : 0;
        $accessCheckArray = array('from' => 'newsletter', 'isAccess' => $isAccess);
        $this->fgpermission->checkAreaAccess($accessCheckArray);

        $listname = $this->em->getRepository('CommonUtilityBundle:FgCnRecepients')->getRecipientListName($filterId);
        $actualListname = ($listname[0]['listname'] == 'All Contacts') ? $this->get('translator')->trans('ACTIVE_CONTACTS') : $listname[0]['listname'];
        $terminologyService = $this->container->get('fairgate_terminology_service');
        $clubTitle = $terminologyService->getTerminology('Club', $this->container->getParameter('singular'));
        $subfederationTitle = $terminologyService->getTerminology('Sub-federation', $this->container->getParameter('singular'));
        $hasHierarchy = ($this->clubType == 'federation' || $this->clubType == 'sub_federation') ? 1 : 0;
        $hasContactModuleAccess = (count(array_intersect(array('contact', 'readonly_contact'), $this->container->get('contact')->get('allowedModules'))) > 0) ? 1 : 0;
        //get club titles for displaying in listing
        $clubObj = new ClubPdo($this->container);
        $clubData = $clubObj->getAllSubLevelData($this->federationId);
        $clubs = array_column($clubData, 'title', 'id');

        return $this->render('ClubadminCommunicationBundle:Recievers:MandatoryRecieverList.html.twig', array('breadCrumb' => $breadCrumb, 'filterId' => $filterId, 'listname' => $actualListname, 'clubTitle' => $clubTitle, 'subfederationTitle' => $subfederationTitle, 'hasHierarchy' => $hasHierarchy, 'hasContactModuleAccess' => $hasContactModuleAccess, 'clubData' => $clubs));
    }

    /**
     * For get the mandatory reciever list.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getMandatoryRecieverListAction(Request $request)
    {
        $filterPostValue = $request->get('filterdata', '');
        $dataTableColumnData = $request->get('columns', '');
        $sWhere = '';
        if ($filterPostValue != '') {
            if ($filterPostValue == '0') {
                $output = array(
                    'iTotalRecords' => 0,
                    'iTotalDisplayRecords' => 0,
                    'aaData' => array(),
                );

                return new JsonResponse($output);
            }
        }
        $searchPostValue = $request->get('search', '');
        if (is_array($searchPostValue) && $searchPostValue['value'] != '') {
            $sWhere = $this->getAddCondition($searchPostValue);
        }
        //pagination handling area
        $orderByandLimit = $this->setOrderAndLimit($request, $dataTableColumnData);
        $totalrecordlist = $this->em->getRepository('CommonUtilityBundle:FgCnRecepients')->getTotalRecords($filterPostValue);
        $contactlistDatas = $this->em->getRepository('CommonUtilityBundle:FgCnRecepients')->getMandatoryList($filterPostValue, $sWhere, $orderByandLimit);
        $output = array(
            'iTotalRecords' => count($totalrecordlist),
            'iTotalDisplayRecords' => count($totalrecordlist),
            'aaData' => $contactlistDatas,
        );

        return new JsonResponse($output);
    }

    /**
     * For create the sort column.
     *
     * @param array $sortColumnPostValue sort column post value
     * @param array $dataTableColumnData dataTable column data
     *
     * @return string
     */
    private function getSortColumnValue($sortColumnPostValue, $dataTableColumnData)
    {
        $club = $this->get('club');
        $mDataProp = $dataTableColumnData[$sortColumnPostValue[0]['column']]['name'];
        $sSortDirVal = $sortColumnPostValue[0]['dir'];
        $sortColumn = $mDataProp;
        $splitColumn = explode('_', $sortColumn);
        $sortColumnValue = '';
        foreach ($club->get('allContactFields') as $key => $sortFields) {
            if (in_array('CF', $splitColumn) && $splitColumn[1] == $sortFields['id']) {
                switch ($sortFields['type']) {
                    case 'number':
                        $sortColumn = 'CAST(`' . $sortColumn . '` as DECIMAL(10,5))';
                        break;
                }
            }
        }

        if (is_numeric($sortColumn)) {
            $sortColumn = '`' . $sortColumn . '`';
        }
        if ($sortColumn == 'FIclub' || $sortColumn == 'FIsub_federation') {
            $sortColumnValue = ' (CASE WHEN ' . $sortColumn . ' IS NULL then 3 WHEN ' . $sortColumn . "='' then 2 WHEN " . $sortColumn . "='0000-00-00 00:00:00' then 1 ELSE 0 END)," . $sortColumn . ' ' . $sSortDirVal;
        } else {
            $sortColumnValue = $sortColumnValue = " $sortColumn $sSortDirVal";
        }

        return $sortColumnValue;
    }

    /**
     * For get the additional where condition.
     *
     * @param array $searchPostValue search post value
     *
     * @return string
     */
    private function getAddCondition($searchPostValue)
    {
        $sSearch = $searchPostValue['value'];
        $columns[] = $this->container->getParameter('system_field_firstname');
        $columns[] = $this->container->getParameter('system_field_lastname');
        $columns[] = $this->container->getParameter('system_field_corress_lang');
        $columns[] = $this->container->getParameter('system_field_primaryemail');
        $columns[] = 'contactname';
        $columns[] = 'email'; //crm.`email`
        if (in_array('contactname', $columns)) {
            $key = array_search('contactname', $columns); //
            unset($columns[$key]);
            $columns[] = '`2`';
            $columns[] = '`23`';
            $columns[] = '`9`';
        }
        if (in_array('email', $columns)) {
            $key = array_search('email', $columns); //
            unset($columns[$key]);
            $columns[] = 'crm.`email`';
        }
        $sWhere = '  (';
        foreach ($columns as $column) {
            $column = is_numeric($column) ? '`' . $column . '`' : $column;
            $searchVal = FgUtility::getSecuredDataString($searchPostValue['value'], $this->conn);
            $sWhere .= $column . " LIKE '%" . $searchVal . "%' OR ";
        }
        $sWhere = substr_replace($sWhere, '', -3);
        $sWhere .= ')';

        return $sWhere;
    }

    /**
     * For set the limit and order in a query.
     *
     * @param object $request             request     object
     * @param array  $dataTableColumnData dataTable column data
     *
     * @return string
     */
    private function setOrderAndLimit($request, $dataTableColumnData)
    {
        $displayStartPostValue = $request->get('start', '');
        $orderByandLimit = '';
        $sortColumnPostValue = $request->get('order', '');
        if ($sortColumnPostValue != '' && $dataTableColumnData[$sortColumnPostValue[0]['column']]['name'] != 'edit') {
            $sortColumnValue = $this->getSortColumnValue($sortColumnPostValue, $dataTableColumnData);
            $orderByandLimit = ' order by' . $sortColumnValue;
        }
        if ($displayStartPostValue != '') {
            $iDisplayLength = $request->get('length');
            $orderByandLimit .= ' limit ' . FgUtility::getSecuredData($displayStartPostValue, $this->conn);
            $orderByandLimit .= ',' . $iDisplayLength;
        }

        return $orderByandLimit;
    }
}
