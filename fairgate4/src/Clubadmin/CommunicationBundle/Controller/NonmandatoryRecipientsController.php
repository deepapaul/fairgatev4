<?php

namespace Clubadmin\CommunicationBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Clubadmin\Util\Contactfilter;
use Clubadmin\Util\Contactlist;
use Clubadmin\Util\Tablesettings;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Repository\Pdo\ClubPdo;

/**
 * NonmandatoryRecipientsController
 *
 * This controller was created for handling Recipients List in Communication.
 *
 * @package    ClubadminCommunicationBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
class NonmandatoryRecipientsController extends FgController
{

    /**
     * This action is used for listing Recipients List.
     * @param type $filterId - newsletter id
     *
     * @Template("ClubadminCommunicationBundle:Recipients:RecipientsList.html.twig")
     *
     * @return array Data array.
     */
    public function indexAction($filterId)
    {
        $backLink = $this->generateUrl('recipents_list');
        $breadCrumb = array(
            'breadcrumb_data' => array(),
            'back' => $backLink
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

        return $this->render('ClubadminCommunicationBundle:Recievers:NonmandatoryRecieverList.html.twig', array('breadCrumb' => $breadCrumb, 'filterId' => $filterId, 'listname' => $actualListname, 'clubTitle' => $clubTitle, 'subfederationTitle' => $subfederationTitle, 'hasHierarchy' => $hasHierarchy, 'hasContactModuleAccess' => $hasContactModuleAccess, 'clubData' => $clubs));
    }

    /**
     * For get the filter data from the json data
     *
     * @param int    $filterId - newsletter id
     * @param object $contactList
     *
     * @return string
     */
    private function getfilterData($filterId, $contactList)
    {
        $filterValues = $this->em->getRepository('CommonUtilityBundle:FgCnRecepients')->getFilterValue($filterId);
        $filterCondition = '';
        if (is_array($filterValues) && count($filterValues) > 0) {
            $filterData = json_decode($filterValues[0]['filterData'], true);
            $club = $this->get('club');
            $contactFilter = new Contactfilter($this->container, $contactList, $filterData['contact_filter'], $club);
            $filterCondition = $contactFilter->generateFilter();
        }

        return $filterCondition;
    }

    /**
     * For get the non mandatory list
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getNonmandatoryRecieverListAction(Request $request)
    {
        switch ($this->clubType) {
            case 'federation':
                $mainfgcontactIdField = 'mc.fed_contact_id';
                break;
            default:
                $mainfgcontactIdField = " mc.contact_id ";
        }
        $filterPostValue = $request->get('filterdata', '');
        $dataTableColumnData = $request->get('columns', '');
        $manualAddedContact = $request->get('manualSelectedIds', '');
        $newsletterId = $request->get('newsletterId', '');
        $updateFlag = $request->get('updateflag', false);
        if (($newsletterId != '' && $manualAddedContact == '' && $filterPostValue == '') || ($filterPostValue != '' && $filterPostValue == '0')) {
            $output = array(
                "iTotalRecords" => 0,
                "iTotalDisplayRecords" => 0,
                "aaData" => array()
            );

            return new JsonResponse($output);
        }
        //call a service for collect all relevant data related to the club
        $club = $this->get('club');
        $aColumns = array();
        array_push($aColumns, 'contactnamewithcomma', "`" . $this->container->getParameter('system_field_corress_lang') . "` AS CL_lang", "salutationText($mainfgcontactIdField, {$this->clubId}, '{$club->get('default_system_lang')}', NULL) AS salutation", "`" . $this->container->getParameter('system_field_primaryemail') . "` AS Email");
        $tablecolumns = $aColumns;
        if (in_array('contactnamewithcomma', $aColumns)) {
            $key = array_search('contactnamewithcomma', $aColumns); //
            unset($tablecolumns[$key]);
        }
        $columnsArray = array();
        array_push($columnsArray, 'count(fg_cm_contact.id) as count');
        array_push($columnsArray, '`515`');
        $contactlistClass = new Contactlist($this->container, $this->contactId, $club);
        $contactlistClass->setColumns($columnsArray);
        $contactlistClass->setFrom();
        $contactlistClass->setCondition();
        $contactlistClass->addCondition("fg_cm_contact.is_subscriber = 1");
        $contactlistClass->addCondition("(`" . $this->container->getParameter('system_field_primaryemail') . "` IS NOT NULL AND `" . $this->container->getParameter('system_field_primaryemail') . "` !='')");
        if ($filterPostValue != "contact" && $filterPostValue != "") {
            $filterdata = $this->getfilterData($filterPostValue, $contactlistClass);
            if ($filterdata != '') {
                $sWhere .= " (" . $filterdata . ")";
                $contactlistClass->addCondition($sWhere);
            }
        }
        $this->extraCondition($filterPostValue, $contactlistClass);
        if ($manualAddedContact != '') {
            $sWhere = "($mainfgcontactIdField  IN($manualAddedContact))";
            $contactlistClass->orCondition($sWhere);
        }
        $searchPostValue = $request->get('search', '');
        if (is_array($searchPostValue) && $searchPostValue['value'] != "") {
            $sWhere = $this->getAddCondition($searchPostValue);
            $contactlistClass->addCondition($sWhere);
        }
        $contactlistClass->setGroupBy('fg_cm_contact.`fed_contact_id`');
        $recipientListQuery = $contactlistClass->getResult();

        $recipientListQueryResult = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($recipientListQuery);
        $totalCount = count($recipientListQueryResult);
        //In level federation and subfederation, add coloums, club and sub-federation
        if ($club->get('type') == 'federation' || $club->get('type') == 'sub_federation') {
            $tableFieldsForClubHierarchies = '{"1":{"id":"clubs","type":"FI","club_id":"' . $this->clubId . '","name":"FIclub"},"2":{"id":"sub_federations","type":"FI"
            ,"club_id":"' . $this->clubId . '","name":"FIsub_federation"}}';
            $table = new Tablesettings($this->container, json_decode($tableFieldsForClubHierarchies, true), $club);
            $ColumnsForClubHierarchies = $table->getColumns();
            $aColumns = array_merge($aColumns, $ColumnsForClubHierarchies);
        }
        $contactlistClass->setColumns($aColumns);
        //pagination handling area
        $this->setOrderAndLimit($request, $contactlistClass, $dataTableColumnData);
        //call query for collect the data
        $listquery = $contactlistClass->getResult();
        file_put_contents("query.txt", $listquery . "\n");
        $contactlistDatas = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($listquery);
        //collect total number of records
        if (is_array($contactlistDatas) && count($contactlistDatas) > 0) {
            $totalrecords = $totalCount;
        } else {
            $totalrecords = 0;
        }
        $output = array(
            "iTotalRecords" => $totalCount,
            "iTotalDisplayRecords" => $totalCount,
            "aaData" => $contactlistDatas
        );
        if ($updateFlag) {
            $this->em->getRepository('CommonUtilityBundle:FgCnRecepients')->updateListCount($filterPostValue, $totalCount, $type = 'non-mandatory-recipient');
        }

        return new JsonResponse($output);
    }

    /**
     * For createt the sort column
     *
     * @param array $sortColumnPostValue
     * @param array $dataTableColumnData
     * @return string
     */
    private function getSortColumnValue($sortColumnPostValue, $dataTableColumnData)
    {
        $club = $this->get('club');
        $mDataProp = $dataTableColumnData[$sortColumnPostValue[0]['column']]['name'];
        $sSortDirVal = $sortColumnPostValue[0]['dir'];
        $sortColumn = $mDataProp;
        $splitColumn = explode("_", $sortColumn);
        $sortColumnValue = '';
        foreach ($club->get('allContactFields') as $key => $sortFields) {
            if (in_array('CF', $splitColumn) && $splitColumn[1] == $sortFields['id']) {
                switch ($sortFields['type']) {
                    case "number":
                        $sortColumn = "CAST(`" . $sortColumn . "` as DECIMAL(10,5))";
                        break;
                }
            }
        }
        if (is_numeric($sortColumn)) {
            $sortColumn = "`" . $sortColumn . "`";
        }
        if ($sortColumn == 'Subscriber') {
            $sortColumnValue = "CAST(" . $sortColumn . " as DECIMAL(10,5)) " . $sSortDirVal;
        } else {
            $sortColumnValue = " (CASE WHEN " . $sortColumn . " IS NULL then 3 WHEN " . $sortColumn . "='' then 2 WHEN " . $sortColumn . "='0000-00-00 00:00:00' then 1 ELSE 0 END)," . $sortColumn . " " . $sSortDirVal;
        }

        return $sortColumnValue;
    }

    /**
     * For get the additional where condition
     *
     * @param array $searchPostValue
     *
     * @return string
     */
    private function getAddCondition($searchPostValue)
    {
        $columns[] = $this->container->getParameter('system_field_firstname');
        $columns[] = $this->container->getParameter('system_field_lastname');
        $columns[] = $this->container->getParameter('system_field_corress_lang');
        $columns[] = $this->container->getParameter('system_field_primaryemail');
        $columns[] = 'contactname';
        if (in_array('contactname', $columns)) {
            $key = array_search('contactname', $columns); //
            unset($columns[$key]);
            $columns[] = 'IF (fg_cm_contact.is_company=0 ,CONCAT_WS(" ",`23`,`2` ), IF(has_main_contact=1,CONCAT(`9`," (",`23`," ",`2`,")"),`9` ) )';
        }
        $sWhere = "  (";
        foreach ($columns as $column) {
            $column = is_numeric($column) ? "`" . $column . "`" : $column;
            $searchVal = FgUtility::getSecuredDataString($searchPostValue['value'], $this->conn);
            $sWhere .= $column . " LIKE '%" . $searchVal . "%' OR ";
        }
        $sWhere = substr_replace($sWhere, "", -3);
        $sWhere .= ')';

        return $sWhere;
    }

    /**
     * For create include/exclude query
     *
     * @param int    $filterPostValue
     * @param object $contactlistClass
     * @param boolean $extraCondition 
     */
    private function extraCondition($filterPostValue, $contactlistClass, $extraCondition = true)
    {
        switch ($this->clubType) {
            case 'federation':
                $mainfgcontactIdField = 'mc.fed_contact_id';
                break;
            case 'sub_federation':
                $mainfgcontactIdField = 'fg_cm_contact.subfed_contact_id';
                break;
            default:
                $mainfgcontactIdField = " mc.contact_id ";
        }
        if ($filterPostValue != '') {
            if ($extraCondition) {
                $sWhere = "($mainfgcontactIdField  NOT IN(select re.contact_id from fg_cn_recepients_exception re where re.contact_id = $mainfgcontactIdField and re.type = 'excluded' and re.recepient_list_id = $filterPostValue))";
                $contactlistClass->addCondition($sWhere);
                $commonCond = " AND fg_cm_contact.is_subscriber = 1 AND (`" . $this->container->getParameter('system_field_primaryemail') . "` IS NOT NULL AND `" . $this->container->getParameter('system_field_primaryemail') . "` !='')";
                $sWhere1 = "($mainfgcontactIdField  IN(select re.contact_id from fg_cn_recepients_exception re where re.contact_id = $mainfgcontactIdField and re.type = 'included' and re.recepient_list_id = $filterPostValue) $commonCond)";
                $contactlistClass->orCondition($sWhere1);
            } else {
                $sWhere = "($mainfgcontactIdField  IN(select fcn.contact_id from fg_cn_newsletter_manual_contacts fcn where fcn.contact_id = $mainfgcontactIdField  and fcn.newsletter_id = $newsletterId))";
                $contactlistClass->orCondition($sWhere);
            }
        }
    }

    /**
     * For set the limit and order in a query
     *
     * @param object $request
     * @param object $contactlistClass
     * @param array  $dataTableColumnData
     */
    private function setOrderAndLimit($request, $contactlistClass, $dataTableColumnData)
    {
        $displayStartPostValue = $request->get('start', '');
        if ($displayStartPostValue != '') {
            $iDisplayLength = $request->get('length');
            $contactlistClass->setLimit($displayStartPostValue);
            $contactlistClass->setOffset($iDisplayLength);
        }
        $sortColumnPostValue = $request->get('order', '');
        if ($sortColumnPostValue != "" && $dataTableColumnData[$sortColumnPostValue[0]['column']]['name'] != 'edit') {
            $sortColumnValue = $this->getSortColumnValue($sortColumnPostValue, $dataTableColumnData);
            $contactlistClass->addOrderBy($sortColumnValue);
            $this->session->set("sort-order" . $this->contactId . $this->clubId, $sortColumnValue);
        }
    }
}
