<?php

namespace Clubadmin\ContactBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Common\UtilityBundle\Controller\FgController as ParentController;
use Clubadmin\Util\Contactlist;
use Clubadmin\ContactBundle\Util\ContactlistData;
use Symfony\Component\Intl\Intl;
use Common\UtilityBundle\Util\FgUtility;
use Symfony\Component\HttpFoundation\Request;

/**
 * ExportController.
 *
 * This controller was created for handling Export functionalities.
 *
 * @author     pitsolutions.ch
 *
 * @version    Fairgate V4
 */
class ExportController extends ParentController
{

    /**
     * Function that shows the export index page.
     *
     * @param type $contactType contacttype
     *
     * @return template
     */
    public function indexAction(Request $request, $contactType)
    {
        if ($contactType == 'archive') {
            $this->get('club')->set('moduleMenu', 'contactarchive');
        } elseif ($contactType == 'formerfederationmember') {
            $this->get('club')->set('moduleMenu', 'formerfederationmember');
        }
        $countSession = $this->session->get($this->contactId . $this->clubId);
        $totalCount = '';
        $club = $this->get('club');
        if ($request->getMethod() == 'POST') {
            $selectedIds = $request->request->get('selcontacthidden');
            $searchval = $request->request->get('searchhidden');
            $contactCount = $request->request->get('counthidden');
        }
        /* Setting the total count */
        if (!(empty($contactCount))) {
            $totalCount = $contactCount;
        } elseif (!(empty($selectedIds))) {
            $selectIdCountarray = explode(',', $selectedIds);
            $totalCount = count($selectIdCountarray);
        } elseif (isset($countSession)) {
            $totalCount = $countSession;
        } else {
            $contactlistClass = new Contactlist($this->container, $this->contactId, $club, $contactType);
            $contactlistClass->setCount();
            $contactlistClass->setFrom();
            $contactlistClass->setCondition();
            $countQuery = $contactlistClass->getResult();
            $totalcontactlist = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($countQuery);
            $totalCount = $totalcontactlist[0]['count'];
        }
        /* Ends here */
        $commonSettings = $this->exportCommonSettings($contactType);

        return $this->render('ClubadminContactBundle:Export:index.html.twig', array('columnSettings' => $commonSettings['columnSettings'], 'workGroupId' => $commonSettings['workgroupId'], 'clubId' => $this->clubId, 'contactId' => $this->contactId, 'teamId' => $commonSettings['teamId'], 'selectedIds' => $selectedIds, 'searchval' => $searchval, 'totalContact' => $totalCount, 'corrAddrFieldIds' => $commonSettings['corrAddrFieldIds'], 'invAddrFieldIds' => $commonSettings['invAddrFieldIds'], 'contacttype' => $contactType, 'backLink' => $commonSettings['backLink']));
    }

    /**
     * Function that is used to do export functionality.
     *
     * @return csv
     */
    public function exportAction(Request $request)
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '2000M');
        $delimiter = '';
        $contactType = $request->request->get('contactType');
        $contactlistData = new ContactlistData($this->contactId, $this->container, $contactType);
        if ($request->getMethod() == 'POST') {
            /* Form values */
            $csvType = $request->request->get('CSVtype');
            if ($contactType == 'sponsor' || $contactType == 'archivedsponsor') {
                $isOptimizeImport = '';
            } else {
                $isOptimizeImport = $request->request->get('isOptimzeImport');
            }
            /* setting the CSV type delimiter */
            $delimiter = ($csvType == 'colonSep') ? ';' : ',';
            /* Ends here */
            $datarray = json_decode($request->get('formhidden'), true);
            $columnNames = $datarray['columnNames'];
            $this->setContactlistVariables($contactlistData, $datarray, $contactType);
        }
        //set the selected clubid for export
        if (!(empty($datarray['selectIds']))) {
            $contactlistData->selectedIds = $datarray['selectIds'];
        }
        if ($contactType == 'sponsor') {
            $contactlistData->groupByColumn = 'id';
        }
        if ($datarray['exportflag'] == 1) {
            $extraSearchColumn = $datarray['extraSearch'];
            $contactlistData->tabledata = array_merge($datarray['extraSearch'], $datarray['columnType']);
            $contactlistData->exportFlag = true;
            $contactlistData->searchTableData = $datarray['extraSearch'];
            $contactlistData->exportSearchColumns = $contactlistData->getTableColumns();
            $contactlistData->exportFlag = false;
            $contactlistData->aoColumns = $contactlistData->getTableColumns();
        }

        $contactslistDatas = $contactlistData->getContactData();
        if ($extraSearchColumn) {
            $filteredData = $this->iterateExtraSearch($contactslistDatas, $columnNames);
        } else {
            $filteredData = $contactslistDatas['data'];
        }

        $response = $this->createCsvfile($filteredData, $columnNames, $delimiter, $isOptimizeImport, $contactType);

        return $response;
    }

    /**
     * Function that is used to generate csv data.
     *
     * @param Array  $dataArray        Data array
     * @param Array  $columnNames      Columns names
     * @param String $delimiter        Delimiter
     * @param Int    $isOptimizeImport Check flag
     *
     * @return template
     */
    public function generateCsv($dataArray, $columnNames, $delimiter, $isOptimizeImport, $contactType)
    {
        $delimiter = '"' . $delimiter . '"';
        $columnFixedFieldArray = array(1 => $this->get('translator')->trans('EXPORT_CONTACTS'));
        /* Column names are differentiated based on import optimized check */
        if ($isOptimizeImport != '') {
            $columnNames = $this->optimzedImportColumnNames($columnNames, $contactType);
        } else {
            if (!(empty($columnNames))) {
                //To remove the first index from the array and also remove the $nbsp character from the title section
                array_walk($columnNames, array($this, 'removeFirstColumn'));
                //remove first column title and 'Function' column title
                $columnNames = $this->unsetColumns($columnNames, $contactType);
            }
        }
        /* Ends here */
        $finalColumnArray = ($isOptimizeImport == '') ? array_merge($columnFixedFieldArray, $columnNames) : $columnNames;
        $exportColumnArray = ($isOptimizeImport != '') ? array_merge(array(0 => $this->get('translator')->trans('CONTACT_ID')), $finalColumnArray) : $finalColumnArray;
        $content = '"' . implode($delimiter, str_replace('"', '', $exportColumnArray)) . '"';
        $content .= "\n";
        $skippedFieldsArray = array('activeServices', 'futureServices', 'pastServices', 'Currentpayments', 'Nextpayments', 'fed_contact_id', 'subfed_contact_id', 'resigned_on');
        array_walk($dataArray, array($this, 'removeColumn'), $delimiter);
        foreach ($dataArray as $value) {
            //to handle case when exporting, company logo and profile pic should have url
            if (array_key_exists('Gprofile_company_pic', $value)) {

                //to find index of key 'Gprofile_company_pic' in array
                $indexProfilePic = array_search('Gprofile_company_pic', array_keys($value));

                $em = $this->getDoctrine()->getManager();
                $contactObj = $em->getRepository('CommonUtilityBundle:FgCmContact')->find($value['id']);
                if ($contactObj->getIsCompany() == 1) {
                    //insert key 'Gcompany_pic' after Gprofile_company_pic
                    $resultArray = array_slice($value, 0, $indexProfilePic, true) +
                        array('Gcompany_pic' => array('Gprofile_company_pic' => $value['Gprofile_company_pic'], 'clubId' => $contactObj->getClub()->getId())) +
                        array_slice($value, $indexProfilePic, count($value) - $indexProfilePic, true);
                } else {
                    //insert key 'Gprofile_pic' after Gprofile_company_pic
                    $resultArray = array_slice($value, 0, $indexProfilePic, true) +
                        array('Gprofile_pic' => array('Gprofile_company_pic' => $value['Gprofile_company_pic'], 'clubId' => $contactObj->getClub()->getId())) +
                        array_slice($value, $indexProfilePic, count($value) - $indexProfilePic, true);
                }
                $value = $resultArray;
                unset($value['Gprofile_company_pic']);
            }
            //to optimise the result array using optimizeContactData function
            array_walk($value, array($this, 'optimizeContactData'), array('delimiter' => $delimiter, 'isOptimzeImport' => $isOptimizeImport));

            if (($contactType == 'sponsor') || ($contactType == 'archivedsponsor')) {
                $this->skipColumns($value, $skippedFieldsArray);
            } else {
                $this->skipColumns($value, array('fed_contact_id', 'subfed_contact_id'));
            }
            if ($isOptimizeImport == '' && array_key_exists('id', $value)) {
                unset($value['id']);
            }
            if ($isOptimizeImport != '' && array_key_exists('contactname', $value)) {
                unset($value['contactname']);
            }
            unset($value['Function']);
            unset($value['resigned_on']);
            unset($value['archived_on']);
            $content .= '"' . implode($delimiter, str_replace('"', '', $value)) . '"' . "\n";
        }

        return $content;
    }

    /**
     * To create csv file.
     *
     * @param array  $finalResultArray selected  club      data
     * @param array  $columnNames      selected  columns
     * @param String $delimiter        delimiter
     * @param Int    $isOptimizeImport is checked optimize flag
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function createCsvfile($finalResultArray, $columnNames, $delimiter, $isOptimizeImport, $contactType)
    {
        $terminologyService = $this->get('fairgate_terminology_service');
        /* Creating the file name */
        if ($contactType == 'formerfederationmember') {
            $contactname = $this->get('translator')->trans('EXPORT_FORMERFEDMEMBERS', array('%federation_members%' => ucfirst($terminologyService->getTerminology('Federation member', $this->container->getParameter('plural')))));
        } elseif ($contactType == 'archive') {
            $contactname = $this->get('translator')->trans('EXPORT_ARCHIVE');
        } elseif ($contactType == 'sponsor') {
            $contactname = $this->get('translator')->trans('EXPORT_SPONSOR');
        } elseif ($contactType == 'archivedsponsor') {
            $contactname = $this->get('translator')->trans('SM_EXPORT_ARCHIVED_SPONSOR');
        } else {
            $contactname = $this->get('translator')->trans('EXPORT_CONTACTS');
        }
        $filename = $contactname . '_' . date('Y-m-d') . '_' . date('H-i-s') . '.csv';
        //   $filename = str_replace(' ', '_', $filename);
        $response = new Response();
        // prints the HTTP headers followed by the content
        $response->setContent(utf8_decode($this->generateCsv($finalResultArray, $columnNames, $delimiter, $isOptimizeImport, $contactType)));
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $filename);
        $response->headers->set('Content-Transfer-Encoding', 'binary');

        return $response;
    }

    /**
     * To set the clublist class variables.
     *
     * @param object $clublistData clublist class object
     * @param array  $datarray     data     from  the     post
     * @param String $contactType  Type     of    Contact
     */
    private function setContactlistVariables($contactlistData, $datarray, $contactType)
    {
        $contactlistData->filterValue = json_decode($datarray['filterdata'], true);
        $contactlistData->dataTableColumnData = '';
        $contactlistData->sortColumnValue = '';
        $searchValue['value'] = $datarray['searchvalue'];
        $contactlistData->searchval = $searchValue;
        $contactlistData->tabledata = $datarray['columnType'];
        $contactlistData->functionTypeValue = 'none';
        $contactlistData->isExtraColumn = false;
        $contactlistData->sortColumnValue = (($contactType == 'sponsor') || ($contactType == 'archivedsponsor')) ? $this->session->get('filteredSponsorDetailsiSortCol_0') : $this->session->get('filteredContactDetailsiSortCol_0');
        $contactlistData->dataTableColumnData[$contactlistData->sortColumnValue[0]['column']]['data'] = (($contactType == 'sponsor') || ($contactType == 'archivedsponsor')) ? $this->session->get('filteredSponsorDetailsmDataProp') : $this->session->get('filteredContactDetailsmDataProp');
        $contactlistData->sortColumnValue[0]['dir'] = (($contactType == 'sponsor') || ($contactType == 'archivedsponsor')) ? $this->session->get('filteredSponsorDetailsSortDir_0') : $this->session->get('filteredContactDetailsSortDir_0');
    }

    /**
     * For array_walk callback function.
     *
     * @param type $classnamearray array values
     * @param type $key            array index
     */
    private function removeFirstColumn(&$classnamearray, $key)
    {
        if ($key != 0 && array_key_exists('sTitle', $classnamearray)) {
            $classnamearray = str_replace('&nbsp;', ' ', $classnamearray['sTitle']);
        }
    }

    /**
     * To optimise the result array.
     *
     * @param array $contactlistDatas selected  contact id detail
     * @param type  $key              array     index
     * @param type  $delimiter        delimiter
     */
    private function optimizeContactData(&$contactlistDatas, $key, $params)
    {
        if (strpos($key, 'SS') !== false) {
            $key = 'SS';
        }
        switch ($key) {
            case 'CF_' . $this->container->getParameter('system_field_gender'):
                if ($contactlistDatas != '') {
                    if ($params['isOptimzeImport'] == '') {
                        $contactlistDatas = ($contactlistDatas == 'Male') ? $this->container->get('translator')->trans('CM_MALE') : $this->container->get('translator')->trans('CM_FEMALE');
                    } else {
                        $contactlistDatas = ($contactlistDatas == 'Male') ? 1 : 2;
                    }
                }
                break;
            case 'CF_' . $this->container->getParameter('system_field_salutaion'):
                if ($contactlistDatas != '') {
                    if ($params['isOptimzeImport'] == '') {
                        $contactlistDatas = ($contactlistDatas == 'Formal') ? $this->container->get('translator')->trans('CM_FORMAL') : $this->container->get('translator')->trans('CM_INFORMAL');
                    } else {
                        $contactlistDatas = ($contactlistDatas == 'Formal') ? 1 : 2;
                    }
                }
                break;
            case 'CF_' . $this->container->getParameter('system_field_corres_land'): case 'CF_' . $this->container->getParameter('system_field_nationality1'): case 'CF_' . $this->container->getParameter('system_field_nationality2'): case 'CF_' . $this->container->getParameter('system_field_invoice_land'):
                if ($contactlistDatas != '') {
                    $countryList = Intl::getRegionBundle()->getCountryNames();
                    $contactlistDatas = ($params['isOptimzeImport'] == '') ? $countryList[$contactlistDatas] : $contactlistDatas;
                }
                break;
            case 'CF_' . $this->container->getParameter('system_field_corress_lang'):
                if ($contactlistDatas != '') {
                    $languages = Intl::getLanguageBundle()->getLanguageNames();
                    $contactlistDatas = ($params['isOptimzeImport'] == '') ? $languages[$contactlistDatas] : $contactlistDatas;
                }
                break;
            case 'SS':
                if ($contactlistDatas != '') {
                    $seperator = '';
                    $jsonData = '[' . $contactlistDatas . ']';
                    $result = json_decode($jsonData);
                    foreach ($result as $data) {
                        $servicename .= $seperator . $data->serviceName;
                        $seperator = ($params['delimiter'] == ';') ? ',' : ';';
                    }
                    $contactlistDatas = $servicename;
                }
                break;
            case 'CNhousehold_contact':
                if ($contactlistDatas != '') {
                    $pipeSeperatorArray = explode(';', $contactlistDatas);
                    $houseHolddata = '';
                    $seperator = '';
                    foreach ($pipeSeperatorArray as $pipeseperatorValue) {
                        list($ptext, $pid) = explode('|', $pipeseperatorValue);
                        $houseHolddata .= $seperator . $ptext;
                        $seperator = ($params['delimiter'] == ';') ? ',' : ';';
                    }
                    $contactlistDatas = $houseHolddata;
                }
                break;
            case 'Gcreated_at':case 'Glast_updated':case 'Glast_login':case 'archived_on':case 'resigned_on': case 'CMfirst_joining_date':case 'CMjoining_date':case 'CMleaving_date':case 'FMfirst_joining_date':case 'FMjoining_date':case 'FMleaving_date':
                if ($contactlistDatas == '0000-00-00 00:00:00' || $contactlistDatas == null) {
                    $contactlistDatas = '';
                } else {
                    $contactlistDatas = $this->get('club')->formatDate($contactlistDatas, 'date');
                }
                break;
            case 'SApayments_curr':case 'SApayments_nex':
                if ($contactlistDatas != '') {
                    $currency = $this->get('club')->getAmountWithCurrency($contactlistDatas, true);
                    $contactlistDatas = $currency;
                }
                break;
            //to handle case when exporting, company logo should have url
            case 'Gcompany_pic':
                $contactlistData = $contactlistDatas['Gprofile_company_pic'];
                $rootPath = FgUtility::getRootPath($this->container);
                $uploadPath = $this->container->get('fg.avatar')->getContactfieldPath(68, false, 'width_65');
                if ($contactlistData != '' && file_exists("$rootPath/$uploadPath/$contactlistData")) {
                    $baseUrl = FgUtility::getBaseUrl($this->container);
                    $contactlistDatas = "$contactlistData ($baseUrl/$uploadPath/$contactlistData)";
                } else {
                    $contactlistDatas = '';
                }
                break;
            //to handle case when exporting, profile pic should have url
            case 'Gprofile_pic':
                $contactlistData = $contactlistDatas['Gprofile_company_pic'];
                $rootPath = FgUtility::getRootPath($this->container);
                $uploadPath = $this->container->get('fg.avatar')->getContactfieldPath(21, false, 'width_130');
                if ($contactlistData != '' && file_exists("$rootPath/$uploadPath/$contactlistData")) {
                    $baseUrl = FgUtility::getBaseUrl($this->container);
                    $contactlistDatas = "$contactlistData ($baseUrl/$uploadPath/$contactlistData)";
                } else {
                    $contactlistDatas = '';
                }
                break;
            case "FIclub":
                if ($contactlistDatas != '') {
                    $myarr = explode(', ', $contactlistDatas);
                    for ($loc = 0; $loc < sizeof($myarr); $loc++) {
                        $myarr[$loc] = str_replace("#mainclub#", "", $myarr[$loc]);
                    }
                    $contactlistDatas = implode(', ', $myarr);
                }
                break;
            default:
                //for show links for images and files
                $club = $this->get('club');
                foreach ($club->get('allContactFields') as $contactFields) {
                    if ('CF_' . $contactFields['id'] == $key) {
                        switch ($contactFields['type']) {
                            case 'imageupload':
                            case 'fileupload':
                                if ($contactlistDatas != '') {
                                    $baseUrl = FgUtility::getBaseUrl($this->container);
                                    $uploadPath = $this->get('fg.avatar')->getContactfieldPath($contactFields['id']);
                                    $contactlistDatas = "$contactlistDatas ($uploadPath/$contactlistDatas)";
                                }
                                break;
                        }
                    }
                }
                break;
        }
    }

    /**
     * To remove the unwanted columns and format the column.
     *
     * @param type $contactlistDatas selected  contact id detail
     * @param type $key              array     index
     * @param type $delimiter        delimiter
     */
    private function removeColumn(&$contactlistDatas, $key, $delimiter)
    {
        $club = $this->get('club');
        foreach ($club->get('allContactFields') as $key => $contactFields) {
            if (array_key_exists('CF_' . $contactFields['id'], $contactlistDatas)) {
                switch ($contactFields['type']) {
                    case 'checkbox':
                        if ($contactlistDatas['CF_' . $contactFields['id']] != '') {
                            $isOptimizeImportbxData = explode(';', $contactlistDatas['CF_' . $contactFields['id']]);
                            $seperator = ($delimiter == ';') ? ',' : ';';
                            $isOptimizeImportbxVal = implode($seperator, $isOptimizeImportbxData);
                            $contactlistDatas['CF_' . $contactFields['id']] = $isOptimizeImportbxVal;
                        }
                        break;
                    case 'date':
                        if ($contactlistDatas['CF_' . $contactFields['id']] == '' || $contactlistDatas['CF_' . $contactFields['id']] == '0000-00-00' || $contactlistDatas['CF_' . $contactFields['id']] == '0000-00-00 00:00:00') {
                            $contactlistDatas['CF_' . $contactFields['id']] = '';
                        } else {
                            $contactlistDatas['CF_' . $contactFields['id']] = $this->get('club')->formatDate($contactlistDatas['CF_' . $contactFields['id']], 'date', 'Y-m-d');
                        }
                        break;
                    case 'number':
                        if ($contactlistDatas['CF_' . $contactFields['id']] == '') {
                            $contactlistDatas['CF_' . $contactFields['id']] = '';
                        } else {
                            $contactlistDatas['CF_' . $contactFields['id']] = $this->container->get('club')->formatDecimalMark($contactlistDatas['CF_' . $contactFields['id']]);
                        }
                        break;
                }
            }
        }
    }

    /**
     * Function to show sponsor export page.
     *
     * @param type $contactType contacttype
     *
     * @return template
     */
    public function sponsorExportAction($contactType)
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        if ($contactType == 'archivedsponsor') {
            $this->get('club')->set('moduleMenu', 'archivedsponsor');
            $this->session->set('contactType', 'archivedsponsor');
        }
        $totalCount = '';
        if ($request->getMethod() == 'POST') {
            $selectedIds = $request->request->get('selcontacthidden');
            $searchval = $request->request->get('searchhidden');
            $contactCount = $request->request->get('counthidden');
        }
        /* Setting the total count */
        if (!(empty($contactCount))) {
            $totalCount = $contactCount;
        } elseif (!(empty($selectedIds))) {
            $selectIdCountarray = explode(',', $selectedIds);
            $totalCount = count($selectIdCountarray);
        } else {
            $contactlistData = new ContactlistData($this->contactId, $this->container, $contactType);
            $contactslistDatas = $contactlistData->getContactData();
            $totalCount = $contactslistDatas['totalcount'];
        }
        /* Ends here */
        $commonSettings = $this->exportCommonSettings($contactType);

        return $this->render('ClubadminContactBundle:Export:index.html.twig', array('columnSettings' => $commonSettings['columnSettings'], 'workGroupId' => $commonSettings['workgroupId'], 'clubId' => $this->clubId, 'contactId' => $this->contactId, 'teamId' => $commonSettings['teamId'], 'selectedIds' => $selectedIds, 'searchval' => $searchval, 'totalContact' => $totalCount, 'corrAddrFieldIds' => $commonSettings['corrAddrFieldIds'], 'invAddrFieldIds' => $commonSettings['invAddrFieldIds'], 'contacttype' => $contactType, 'backLink' => $commonSettings['backLink']));
    }

    /**
     * Function that handles common features for contact andd sponsor export.
     *
     * @param type $contactType contacttype
     *
     * @return array
     */
    private function exportCommonSettings($contactType)
    {
        $commonSettingsArray = array();
        $commonSettingsArray['workgroupId'] = $this->get('club')->get('club_workgroup_id');
        $commonSettingsArray['teamId'] = $this->get('club')->get('club_team_id');
        $corrAddrCatId = $this->container->getParameter('system_category_address');
        $invAddrCatId = $this->container->getParameter('system_category_invoice');
        $corrAddrFieldIds = array();
        $invAddrFieldIds = array();
        $contactFields = $this->get('club')->get('contactFields');
        foreach ($contactFields as $contactField) {
            if ($contactField['catId'] == $corrAddrCatId) {
                $corrAddrFieldIds[] = $contactField['id'];
            } elseif ($contactField['catId'] == $invAddrCatId) {
                $invAddrFieldIds[] = $contactField['id'];
            }
        }
        $commonSettingsArray['corrAddrFieldIds'] = $corrAddrFieldIds;
        $commonSettingsArray['invAddrFieldIds'] = $invAddrFieldIds;
        $columnsettingType = strtoupper($contactType);
        $commonSettingsArray['columnSettings'] = (($contactType == 'sponsor') || ($contactType == 'archivedsponsor')) ? $this->em->getRepository('CommonUtilityBundle:FgTableSettings')->getAllTableSettings($this->clubId, $this->contactId, $columnsettingType) : $this->em->getRepository('CommonUtilityBundle:FgTableSettings')->getAllTableSettings($this->clubId, $this->contactId, 'DATA');
        $commonSettingsArray['backLink'] = ($contactType == 'sponsor') ? $this->generateUrl('clubadmin_sponsor_homepage') : (($contactType == 'archivedsponsor') ? $this->generateUrl('view_archived_sponsors') : (($contactType == 'formerfederationmember') ? $this->generateUrl('former_federation_member_index') : $this->generateUrl('contact_index')));

        return $commonSettingsArray;
    }

    /**
     * To skip the unwanted columns in sponsor export.
     *
     * @param array $dataArray          selected contact id detail
     * @param array $skippedFieldsArray columns array containing skipped columns
     */
    private function skipColumns(&$dataArray, $skippedFieldsArray)
    {
        foreach ($skippedFieldsArray as $key => $value) {
            if (array_key_exists($value, $dataArray)) {
                unset($dataArray[$value]);
            }
        }
    }

    /**
     * To get the column names for optimized import.
     *
     * @param array  $columnNames selected column names array
     * @param string $contactType contact type
     *
     * return array
     */
    public function optimzedImportColumnNames($columnNames, $contactType)
    {
        $contactFields = $this->container->get('club')->get('allContactFields');
        $invAddrCatId = $this->container->getParameter('system_category_invoice');
        $corrAddrCatId = $this->container->getParameter('system_category_address');
        $columnNamesArray = array();
        //$i is declared as 1 since the zeroth index would be contact id
        $i = 1;
        $finalcolumnNames = $this->unsetColumns($columnNames, $contactType);
        foreach ($finalcolumnNames as $key => $value) {
            if (strpos($value['mData'], 'CF_') !== false) {
                $id = str_replace('CF_', '', $value['mData']);
                $catTypeId = $contactFields[$id]['category_id'];
                $columnNamesArray[$i] = ($catTypeId == $invAddrCatId) ? $contactFields[$id]['title'] . ' ' . '(' . $this->get('translator')->trans('CL_INVOICE') . ')' : (($catTypeId == $corrAddrCatId) ? $contactFields[$id]['title'] . ' ' . '(' . $this->get('translator')->trans('CL_CORRESPONDENCE') . ')' : $contactFields[$id]['title']);
            } else {
                $columnNamesArray[$i] = str_replace('&nbsp;', ' ', $value['sTitle']);
            }
            $i = $i + 1;
        }

        return $columnNamesArray;
    }

    /**
     * To get the column names after unsetting the unwanted columns.
     *
     * @param array  $columnNames selected column names array
     * @param string $contactType contact type
     *
     * return array
     */
    private function unsetColumns($columnNames, $contactType)
    {
        unset($columnNames[0]);
        if ($contactType != 'sponsor') {
            unset($columnNames[1]);
        }
        if ($contactType == 'archive') {
            unset($columnNames[2]);
        }
        if ($contactType == 'formerfederationmember') {
            unset($columnNames[2]);
        }

        return $columnNames;
    }

    /**
     * Function to eliminate extra fields from export.
     *
     * @param type array $contactslistDatas
     * @param type array $columnNames
     *                                      return array $newData
     */
    private function iterateExtraSearch($contactslistDatas, $columnNames)
    {
        $rColumnNames = array_map(function ($a) {
            return $a['mData'];
        }, $columnNames);
        unset($rColumnNames[0]);
        array_unshift($rColumnNames, 'id', 'contactname');
        $newData = array();
        foreach ($contactslistDatas['data'] as $conListData) {
            $newCont = array();
            foreach ($rColumnNames as $rCols) {
                $newCont[$rCols] = $conListData[$rCols];
            }
            $newData[] = $newCont;
            unset($newCont);
        }

        return $newData;
    }
}
