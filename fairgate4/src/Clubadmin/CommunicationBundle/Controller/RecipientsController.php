<?php

/**
 * RecipientsController
 *
 * This controller was created for handling Recipients List in Communication.
 *
 * @package    ClubadminCommunicationBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
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

class RecipientsController extends FgController
{

    /**
     * This action is used for listing Recipients List.
     * 
     * @param Request $request Request object
     *
     * @Template("ClubadminCommunicationBundle:Recipients:RecipientsList.html.twig")
     *
     * @return array Data array.
     */
    public function indexAction(Request $request)
    {
        $list_type = $request->attributes->get('level1');
        $corrLangAttrId = $this->container->getParameter('system_field_corress_lang');
        $clubHeirarchy = $this->get('club')->get('clubHeirarchy');
        $clubDetails = array('clubType' => $this->clubType, 'clubId' => $this->clubId, 'clubHeirarchy' => $clubHeirarchy, 'defaultLang' => $this->clubDefaultLang, 'defaultSystemLang' => $this->clubDefaultSystemLang, 'corrLangAttrId' => $corrLangAttrId, 'clubLanguages' => $this->clubLanguages);
        $emailFields = $this->em->getRepository('CommonUtilityBundle:FgCmAttribute')->getAllContactFields($clubDetails, false, array('email', 'login email'));

        return array('emailFields' => $emailFields, 'listType' => $list_type, 'settings' => true);
    }

    /**
     * Action to get Recipients List.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse Array of Recipients List.
     */
    public function getRecipientsListAction()
    {
        $recipientsList = $this->em->getRepository('CommonUtilityBundle:FgCnRecepients')->getRecipientsList($this->clubId);

        return new JsonResponse($recipientsList);
    }

    /**
     * Action to get Counts (Contacts count, Mandatory Count, Non-Mandatory Count) of Recipients.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse Array of Recipients Count.
     */
    public function getRecipientCountsAction(Request $request)
    {
        $recipientsCounts = $this->em->getRepository('CommonUtilityBundle:FgCnRecepients')->getRecipientsCounts($request->get('recipientListIds'));

        return new JsonResponse($recipientsCounts);
    }

    /**
     * Action to update (Add, Edit, Delete) Recipients List.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse Array of saved status.
     */
    public function updateRecipientsListAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $dataArr = json_decode($request->get('saveData'), true);
            $currRecipientIds = $request->get('currRecipientIds');
            if (count($dataArr) > 0) {
                $this->em->getRepository('CommonUtilityBundle:FgCnRecepients')->updateRecipientsList($dataArr, $currRecipientIds, $this->clubId, $this->container, $this->contactId);
            }

            return new JsonResponse(array('status' => 'SUCCESS', 'noparentload' => true, 'flash' => $this->get('translator')->trans('RECIPIENTS_LIST_UPDATED')));
        }
    }

    /**
     * Action for updating contacts of Recipients List.
     *
     * @param int $recipientId Recipient List Id
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse Array of saved status.
     */
    public function updateRecipientContactsAction($recipientId, Request $request)
    {
        ini_set('max_execution_time', 0);
        ini_set("memory_limit", "2000M");
        $this->em->getRepository('CommonUtilityBundle:FgCnRecepients')->updateRecipientContacts($this->container, $this->contactId, $recipientId, $this->clubDefaultSystemLang);
        $newsletterId = $request->get('newsletterId') ? $request->get('newsletterId') : '';
        if ($newsletterId != '') {
            $this->em->getRepository('CommonUtilityBundle:FgCnNewsletter')->updateNewsletterRecipientsCount($newsletterId, $this->clubId, $this->container, $this->contactId, null, $this->clubDefaultSystemLang);

            return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('RECIPIENT_CONTACTS_UPDATED')));
        } else {
            return new JsonResponse(array('status' => 'SUCCESS', 'noparentload' => true, 'flash' => $this->get('translator')->trans('RECIPIENT_CONTACTS_UPDATED')));
        }
    }

    /**
     * show the recieverlist
     * @param int $filterId
     *
     * @return Template
     */
    public function recieverslistAction($filterId)
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

        $receipientlistColumns = $this->getReceipientColumns($filterId);
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
        
        return $this->render('ClubadminCommunicationBundle:Recievers:RecieverList.html.twig', array('breadCrumb' => $breadCrumb, 'filterId' => $filterId, 'tableColumns' => $receipientlistColumns['columnName'], 'columns' => $receipientlistColumns['column'], 'listname' => $actualListname, 'clubTitle' => $clubTitle, 'subfederationTitle' => $subfederationTitle, 'hasHierarchy' => $hasHierarchy, 'hasContactModuleAccess' => $hasContactModuleAccess, 'clubData' => $clubs));
    }

    /**
     * To get the filter data from the json data
     * @param int    $filterId
     * @param object $contactList
     *
     * @return String
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
     * get the reciever list
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getRecieverListAction(Request $request)
    {
        $filterPostValue = $request->get('filterdata', '');
        $dataTableColumnData = $request->get('columns', '');
        $updateFlag = $request->get('updateflag', false);
        $receipientlistColumns = $this->getReceipientColumns($filterPostValue);
        if ($filterPostValue != '') {
            if ($filterPostValue == '0') {
                $output = array(
                    "iTotalRecords" => 0,
                    "iTotalDisplayRecords" => 0,
                    "aaData" => array()
                );

                return new JsonResponse($output);
            }
        }
        //call a service for collect all relevant data related to the club
        $club = $this->get('club');
        $aColumns = array();
        array_push($aColumns, 'contactid', 'contactnamewithcomma', 'gender', "`" . $this->container->getParameter('system_field_corress_lang') . "` AS CL_lang", "`" . $this->container->getParameter('system_field_salutaion') . "` AS salutation", 'fg_cm_contact.is_subscriber AS Subscriber', "`" . $this->container->getParameter('system_field_primaryemail') . "` AS Email");
        //In level federation and subfederation, add coloums, club and sub-federation
        if ($club->get('type') == 'federation' || $club->get('type') == 'sub_federation') {
            $tableFieldsForClubHierarchies = '{"1":{"id":"clubs","type":"FI","club_id":"' . $this->clubId . '","name":"FIclub"},"2":{"id":"sub_federations","type":"FI"
            ,"club_id":"' . $this->clubId . '","name":"FIsub_federation"}}';
            $table = new Tablesettings($this->container, json_decode($tableFieldsForClubHierarchies, true), $club);
            $ColumnsForClubHierarchies = $table->getColumns();
            $aColumns = array_merge($aColumns, $ColumnsForClubHierarchies);
        }

        $tablecolumns = $aColumns;
        $aColumns = $this->createColumns($receipientlistColumns, $aColumns);
        if (in_array('contactnamewithcomma', $aColumns)) {
            $key = array_search('contactnamewithcomma', $aColumns); //
            unset($tablecolumns[$key]);
            $firstnamekey = array_search($this->container->getParameter('system_field_firstname'), $tablecolumns);
            $secondnamekey = array_search($this->container->getParameter('system_field_lastname'), $tablecolumns);
        }
        if (in_array('contactid', $aColumns)) {
            $key = array_search('contactid', $aColumns); //
            unset($tablecolumns[$key]);
        }
        $columnsArray = array();
        array_push($columnsArray, 'count(distinct fg_cm_contact.fed_contact_id) as count', '`515`');
        $contactlistClass = new Contactlist($this->container, $this->contactId, $club);
        $contactlistClass->setColumns($columnsArray);
        $contactlistClass->setFrom();
        $contactlistClass->setCondition();
        if ($filterPostValue != "contact") {
            $filterdata = $this->getfilterData($filterPostValue, $contactlistClass);
            if ($filterdata != '') {
                $sWhere .= " (" . $filterdata . ")";
                $contactlistClass->addCondition($sWhere);
            }
        }
        $this->extraCondition($filterPostValue, $contactlistClass);
        $searchPostValue = $request->get('search', '');
        if (is_array($searchPostValue) && $searchPostValue['value'] != "") {
            $sWhere = $this->getAddCondition($searchPostValue, $receipientlistColumns);
            $contactlistClass->addCondition($sWhere);
        }
        $languagearrayQuery = $contactlistClass->getResult();
        $languagearrayQuery = $languagearrayQuery . " group by `515`";
        $languageArrayResult = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($languagearrayQuery);
        $totalCount = $this->totalCount($languageArrayResult);

        $languageArray = $this->createAdditionalData($languageArrayResult);
        $contactlistClass->setColumns($aColumns);
        //pagination handling area
        $this->setOrderAndLimit($request, $contactlistClass, $dataTableColumnData);
        $contactlistClass->setGroupBy('fg_cm_contact.`fed_contact_id`');
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
            "iTotalDisplayRecords" => $totalCount
        );
        $output['aaData'] = $contactlistDatas;
        $output['adData'] = $languageArray;

        if ($updateFlag) {
            $this->em->getRepository('CommonUtilityBundle:FgCnRecepients')->updateListCount($filterPostValue, $totalCount, $type = 'recipient');
        }

        return new JsonResponse($output);
    }

    /**
     * For get the receipient email columns
     * @param type $filterId
     *
     * @return array
     */
    private function getReceipientColumns($filterId)
    {
        $fieldValues = $this->em->getRepository('CommonUtilityBundle:FgCnRecepientsEmail')->getReciepientemailfields($filterId);
        $Columns = array();
        $club = $this->get('club');
        $allContactFiledsData = $club->get('contactFields');
        $columnNameArray = array();
        if (is_array($fieldValues) && count($fieldValues) > 0) {
            foreach ($fieldValues as $fieldValue) {
                if ($fieldValue['emailType'] == 'parent_email') {
                    array_push($Columns, 'C_Parent');
                    $columnNameArray[0]['connected parent'] = $this->container->get('translator')->trans('RL_CONNECTED_PARENTS');
                    $columnNameArray[0]['shortname'] = $this->container->get('translator')->trans('RL_CONNECTED_PARENTS');
                } else if ($fieldValue['fieldId'] != 3) {
                    foreach ($allContactFiledsData as $contactFields) {
                        if ($fieldValue['fieldId'] == $contactFields['id']) {
                            $columnNameArray[$fieldValue['fieldId']]['shortname'] = $contactFields['shortName'];
                            $columnNameArray[$fieldValue['fieldId']]['group'] = $contactFields['selectgroup'];
                            if (!in_array($fieldValue['fieldId'], $Columns)) {
                                array_push($Columns, $fieldValue['fieldId']);
                            }
                        }
                    }
                }
            }
        }

        return array('column' => $Columns, 'columnName' => $columnNameArray);
    }

    /**
     * For createt the sort column
     * @param array $sortColumnPostValue sort-Column Post Values
     * @param array $dataTableColumnData dataTableColumn datas
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
     * @param array $searchPostValue
     * @param array $receipientlistColumns
     * @return string
     */
    private function getAddCondition($searchPostValue, $receipientlistColumns)
    {
        $sSearch = $searchPostValue['value'];
        $columns[] = $this->container->getParameter('system_field_firstname');
        $columns[] = $this->container->getParameter('system_field_lastname');
        $columns[] = $this->container->getParameter('system_field_gender');
        $columns[] = $this->container->getParameter('system_field_corress_lang');
        $columns[] = $this->container->getParameter('system_field_salutaion');
        $columns[] = $this->container->getParameter('system_field_primaryemail');
        $columns[] = 'contactname';

        if (is_array($receipientlistColumns['column']) && count($receipientlistColumns['column'])) {
            foreach ($receipientlistColumns['column'] as $emailColumn) {
                if ($emailColumn != 'C_Parent') {
                    $columns[] = $emailColumn;
                }
            }
        }
        if (in_array('contactid', $columns)) {
            $key = array_search('contactid', $columns); //
            unset($columns[$key]);
        }
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
     * create table column fields
     * @param array $receipientlistColumns
     * @param array $aColumns
     *
     * @return array
     */
    private function createColumns($receipientlistColumns, $aColumns)
    {
        $club = $this->get('club');
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
        if (is_array($receipientlistColumns['column']) && count($receipientlistColumns['column'])) {
            foreach ($receipientlistColumns['column'] as $emailColumn) {
                $originalColumn = $emailColumn;
                $emailColumn = is_numeric($emailColumn) ? "`" . $emailColumn . "`" : $emailColumn;
                if ($emailColumn == 'C_Parent') {
                    array_push($aColumns, "(SELECT GROUP_CONCAT(connectedEmail SEPARATOR ';') FROM (SELECT CASE WHEN fcc.is_company THEN CONCAT(RMS.3,' (',RMS.9,') ') ELSE CONCAT(RMS.3,' (',RMS.23,' ',RMS.2,') ') END AS connectedEmail,LC.contact_id AS lcid
                FROM `fg_cm_linkedcontact` LC  INNER JOIN fg_cm_contact fcc on fcc.id=LC.linked_contact_id INNER JOIN  master_system as RMS ON  fcc.fed_contact_id= RMS.fed_contact_id AND (RMS.3 IS NOT NULL AND RMS.3 !='')
                WHERE  LC.club_id={$club->get("id")} AND fcc.is_permanent_delete = 0 AND  LC.relation_id = 2 AND LC.type='household'
                ORDER BY fcc.is_company,connectedEmail ASC ) AS hosehold WHERE lcid= {$mainfgcontactIdField})  AS " . $originalColumn);
                } else {
                    array_push($aColumns, $emailColumn);
                }
            }
        }

        return $aColumns;
    }

    /**
     * For iterate the result
     * @param array $languageArrayResults
     *
     * @return array
     */
    private function createAdditionalData($languageArrayResults)
    {
        $adData = array();
        foreach ($languageArrayResults as $result) {
            $adData[] = $result;
        }

        return $adData;
    }

    /**
     * For create include/exclude query
     * @param int    $filterPostValue
     * @param object $contactlistClass
     */
    private function extraCondition($filterPostValue, $contactlistClass)
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
        $sWhere = "($mainfgcontactIdField  NOT IN(select re.contact_id from fg_cn_recepients_exception re where re.contact_id = $mainfgcontactIdField and re.type = 'excluded' and re.recepient_list_id = $filterPostValue))";
        $contactlistClass->addCondition($sWhere);
        $sWhere = "($mainfgcontactIdField  IN(select re.contact_id from fg_cn_recepients_exception re where re.contact_id = $mainfgcontactIdField and re.type = 'included' and re.recepient_list_id = $filterPostValue))";
        $contactlistClass->orCondition($sWhere);
    }

    /**
     * For set the limit and order in a query
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

    /**
     * create total count from the result
     * @param array $languageArrayResult
     *
     * @return int
     */
    private function totalCount($languageArrayResult)
    {
        $totalCount = 0;
        foreach ($languageArrayResult as $result) {
            $totalCount = $totalCount + $result['count'];
        }

        return $totalCount;
    }

    /**
     * Action to get exception contact names
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse Array of Contact Names.
     */
    public function getExceptionContactNamesAction(Request $request)
    {
        $contactIds = $request->get('contactIds');
        $contactNames = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->contactNameYOB($contactIds, false, $this->get('club'), $this->container, 'contact');

        return new JsonResponse($contactNames);
    }
}
