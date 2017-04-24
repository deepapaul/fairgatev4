<?php

/**
 * SponsorServiceExport Controller.
 *
 * This controller was created for handling export in Sponsor  service management.
 *
 * @author     pitsolutions.ch
 *
 * @version    Fairgate V4
 */
namespace Clubadmin\SponsorBundle\Controller;

use Common\UtilityBundle\Controller\FgController as ParentController;
use Clubadmin\ContactBundle\Util\ContactlistData;
use Clubadmin\SponsorBundle\Util\Servicelist;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * This controller was created for handling export functionality in Sponsor service management.
 *
 * @author pitsolutions.ch
 */
class SponsorServiceExportController extends ParentController
{

    /**
     * Function to show export popup.
     *
     *
     * @return template
     */
    public function serviceExportpopupAction(Request $request)
    {
        $actionType = $request->get('actionType');
        $bookedIds = '';
        if ($actionType == 'serviceexportcsv' || $actionType == 'assignmentexportcsv') {
            $bookedIds = $request->get('bookedIds');
            $tabType = $request->get('tabType');
            $searchCount = $request->get('searchCount');
            $bookedIdarray = explode(',', $bookedIds);
            $totalCount = ($bookedIds == '') ? $searchCount : count($bookedIdarray);
            if ($totalCount > 1) {
                $titleText = str_replace('%count%', $totalCount, $this->get('translator')->trans('SM_SERVICE_EXPORT_PLURAL'));
            } else {
                $titleText = $this->get('translator')->trans('SM_SERVICE_EXPORT_SINGULAR');
            }
        }
        $selActionType = $request->get('selActionType') ? $request->get('selActionType') : '';
        $return = array('actionType' => $actionType, 'selActionType' => $selActionType, 'titleText' => $titleText, 'bookedIds' => $bookedIds, 'tabType' => $tabType, 'contactId' => $this->contactId, 'clubId' => $this->clubId);

        $twig = ($actionType == 'assignmentexportcsv') ? 'assignmentOverviewExportPopup.html.twig' : 'serviceExportpopup.html.twig';

        return $this->render('ClubadminSponsorBundle:serviceExport:' . $twig, $return);
    }

    /**
     * Function to execute sponsor service export functionality.
     *
     * @return response
     */
    public function serviceExportAction(Request $request)
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '2000M');
        $tabType = $request->get('tabType', '');
        $exportData = json_decode($request->get('filterdata'), true);
        $columnData = $this->optimizeColumnData($exportData);
        $tabTitle = '';
        $serviceId = $request->get('serviceId', '0');
        $serviceObj = $this->em->getRepository('CommonUtilityBundle:FgSmServices')->find($serviceId);
        $serviceType = $serviceObj->getServiceType();
        if ($tabType == 'activeservice') {
            $tab = 'active';
            $columnNames = $this->activeColumnsNameData($serviceType);
            $tabTitle = $this->get('translator')->trans('SM_ACTIVE_SERVICE');
        } elseif ($tabType == 'futureservice') {
            $tab = 'future';
            $columnNames = $this->futureColumnsNameData();
            $tabTitle = $this->get('translator')->trans('SM_FUTURE_SERVICE');
        } else {
            $tab = 'past';
            $columnNames = $this->formerColumnsNameData();
            $tabTitle = $this->get('translator')->trans('SM_FORMER_SERVICE');
        }
        $csvType = $request->get('csvType', '');
        $delimiter = ($csvType == 'colonSep') ? ';' : ',';
        $response = $this->createCsvfile($columnData, $columnNames, $delimiter, $tabTitle, $serviceObj, $serviceType);

        return $response;
    }

    /**
     * Function to get the active sponsors column names.
     *
     * @param string $serviceType service type
     *
     * @return array
     */
    private function activeColumnsNameData($serviceType)
    {
        $fiscalData = $this->fiscalYearDetails();
        $activeColumnNames = array($this->get('translator')->trans('SM_START'),
            $this->get('translator')->trans('SM_END'),
            $this->get('translator')->trans('SM_CONTACT'),
            $this->get('translator')->trans('SM_DEPOSITED_WITH'),
            $this->get('translator')->trans('SM_PAYMENT_PLAN'),
            $this->get('translator')->trans('SM_NEXTPAYMENT_DATE'),
            str_replace('%yr%', $fiscalData['currentFiscalLabel'], $this->get('translator')->trans('SM_PAYMENTS_CURR')) . ' (' . $this->get('club')->get('clubCurrency') . ')',
            str_replace('%yr%', $fiscalData['nextFiscalLabel'], $this->get('translator')->trans('SM_PAYMENTS_NEX')) . ' (' . $this->get('club')->get('clubCurrency') . ')',
            $this->get('translator')->trans('SM_TOTAL_PAYMENT') . ' (' . $this->get('club')->get('clubCurrency') . ')',
        );
        if ($serviceType == 'club') {
            unset($activeColumnNames[3]);
        }

        return $activeColumnNames;
    }

    /**
     * Function to get the future sponsors column names.
     *
     * @return array
     */
    private function futureColumnsNameData()
    {
        $fiscalData = $this->fiscalYearDetails();
        $futureColumnNames = array($this->get('translator')->trans('SM_START'),
            $this->get('translator')->trans('SM_END'),
            $this->get('translator')->trans('SM_CONTACT'),
            $this->get('translator')->trans('SM_PAYMENT_PLAN'),
            $this->get('translator')->trans('SM_NEXTPAYMENT_DATE'),
            str_replace('%yr%', $fiscalData['currentFiscalLabel'], $this->get('translator')->trans('SM_PAYMENTS_CURR')) . ' (' . $this->get('club')->get('clubCurrency') . ')',
            str_replace('%yr%', $fiscalData['nextFiscalLabel'], $this->get('translator')->trans('SM_PAYMENTS_NEX')) . ' (' . $this->get('club')->get('clubCurrency') . ')',
            $this->get('translator')->trans('SM_TOTAL_PAYMENT') . ' (' . $this->get('club')->get('clubCurrency') . ')',
        );

        return $futureColumnNames;
    }

    /**
     * Function to get the former sponsors column names.
     *
     * @return array
     */
    private function formerColumnsNameData()
    {
        $fiscalData = $this->fiscalYearDetails();
        $formerColumnNames = array($this->get('translator')->trans('SM_START'),
            $this->get('translator')->trans('SM_END'),
            $this->get('translator')->trans('SM_CONTACT'),
            $this->get('translator')->trans('SM_PAYMENT_PLAN'),
            str_replace('%yr%', $fiscalData['currentFiscalLabel'], $this->get('translator')->trans('SM_PAYMENTS_CURR')) . ' (' . $this->get('club')->get('clubCurrency') . ')',
            $this->get('translator')->trans('SM_TOTAL_PAYMENT') . ' (' . $this->get('club')->get('clubCurrency') . ')',
        );

        return $formerColumnNames;
    }

    /**
     * Function to get the fiscal year details for column names.
     *
     * @return array
     */
    private function fiscalYearDetails()
    {
        $fiscalYearDetails = $this->get('club')->getFiscalyear();
        $currentFiscalLabel = $fiscalYearDetails['current']['label'];
        $nextFiscalLabel = $fiscalYearDetails['next']['label'];

        $returnData = array('currentFiscalLabel' => $currentFiscalLabel, 'nextFiscalLabel' => $nextFiscalLabel);

        return $returnData;
    }

    /**
     * Function to generate the CSV data.
     *
     * @param array  $columnData                column data array
     * @param array  $columnNames               column names array
     * @param string $delimiter                 delimiter
     * @param array  $paymentPlantranslateArray payment plan translate array
     * @param string $serviceType               service type
     *
     * @return string
     */
    public function generateCsvData($columnData, $columnNames, $delimiter, $paymentPlantranslateArray, $serviceType)
    {
        $terminologyService = $this->get('fairgate_terminology_service');
        $delimiter = '"' . $delimiter . '"';
        $csv = '';
        $paymentPlan = '';
        $currentPaymentTotal = '';
        $lastColumnArray = array();
        $nextPaymentTotal = '';
        $csv = '"' . implode($delimiter, str_replace('"', '', $columnNames)) . '"';
        $csv .= "\n";
        /* Iterates the result data to generate csv data */
        foreach ($columnData as $key => $value) {
            $resultArray = array();
            $paymentPlan = $this->paymentPlanOptimize($value, $paymentPlantranslateArray);
            $resultArray['SA_paymentstartdate'] = $value['SA_paymentstartdate'];
            $lastColumnArray['SA_paymentstartdate'] = $this->get('translator')->trans('SM_TOTAL');
            $resultArray['SA_paymentenddate'] = ($value['SA_paymentenddate'] == 'null') ? '' : $value['SA_paymentenddate'];
            $lastColumnArray['SA_paymentenddate'] = '';
            $resultArray['contactname'] = $value['contactname'];
            $lastColumnArray['contactname'] = '';

            if ((array_key_exists('SA_depositedwith', $value)) && $serviceType != 'club') {
                $depositedVal = $this->depositedwithOptimize($value, $terminologyService, $serviceType);
                $resultArray['SA_depositedwith'] = $depositedVal;
                $lastColumnArray['SA_depositedwith'] = '';
            }

            $resultArray['SA_paymentplan'] = $paymentPlan;
            $lastColumnArray['SA_paymentplan'] = '';

            if (array_key_exists('SA_paymentDate', $value)) {
                $paymentDateexportData = '';
                if ($value['SA_paymentDate'] != '') {
                    $paymentDate = explode('|', $value['SA_paymentDate']);
                    $paymentDateexportData = $paymentDate[0] . ' (' . $this->get('club')->getAmountWithCurrency($paymentDate[1], true) . ')';
                    $paymentDateexportData = str_replace(array('&#8239;', '&#8217;'), array('', '`'), $paymentDateexportData);
                }
                $resultArray['SA_paymentDate'] = $paymentDateexportData;
                $lastColumnArray['SA_paymentDate'] = '';
            }

            if (array_key_exists('SA_paymentCurr', $value)) {
                $resultArray['SA_paymentCurr'] = ($value['SA_paymentCurr'] == '') ? '' : $this->get('club')->formatDecimalMark($value['SA_paymentCurr']);
                $currentPaymentTotal += $value['SA_paymentCurr'];
                $lastColumnArray['SA_paymentCurr'] = $this->get('club')->formatDecimalMark($currentPaymentTotal);
            }

            if (array_key_exists('SA_paymentNext', $value)) {
                $resultArray['SA_paymentNext'] = ($value['SA_paymentNext'] == '') ? '' : $this->get('club')->formatDecimalMark($value['SA_paymentNext']);
                $nextPaymentTotal += $value['SA_paymentNext'];
                $lastColumnArray['SA_paymentNext'] = $this->get('club')->formatDecimalMark($nextPaymentTotal);
            }

            $totalPayment = $this->totalpaymentOptimize($value);
            $resultArray['SA_totalPayment'] = $this->get('club')->formatDecimalMark($totalPayment);
            $lastColumnArray['SA_totalPayment'] = '';
            $csv .= '"' . implode($delimiter, str_replace('"', '', $resultArray)) . '"' . "\n";
        }
        /* Ends here */
        $csv .= '"' . implode($delimiter, str_replace('"', '', $lastColumnArray)) . '"' . "\n";

        return $csv;
    }

    /**
     * Function to create the CSV file.
     *
     * @param array  $columnData  column data array
     * @param array  $columnNames column names array
     * @param string $delimiter   delimiter
     * @param string $tabTitle    tab title
     * @param object $serviceObj  service Id object
     *
     * @return response
     */
    private function createCsvfile($columnData, $columnNames, $delimiter, $tabTitle, $serviceObj, $serviceType)
    {
        $paymentPlantranslateArray = array('regular' => $this->get('translator')->trans('SM_REGULAR'),
            'custom' => $this->get('translator')->trans('SM_CUSTOM'),
            'none' => $this->get('translator')->trans('SM_NONE'),);

        $filetitle = $serviceObj->getTitle() . ' (' . $tabTitle . ')' . '_' . date('Y-m-d') . '_' . date('H-i-s');
        $string = str_replace(' ', '%20', $filetitle);
        $filename = $string . '.csv';
        $response = new Response();
        // prints the HTTP headers followed by the content
        $response->setContent(utf8_decode($this->generateCsvData($columnData, $columnNames, $delimiter, $paymentPlantranslateArray, $serviceType)));
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $filename);
        $response->headers->set('Content-Transfer-Encoding', 'binary');

        return $response;
    }

    /**
     * Function to generate export pdf.
     *
     * @return template
     */
    public function exportPdfAction(Request $request)
    {
        $exportData = json_decode($request->request->get('exportData'), true);
        $columnData = $this->optimizeColumnData($exportData);
        $serviceName = $request->request->get('serviceName');
        $serviceId = $request->request->get('serviceId');
        $serviceObj = $this->em->getRepository('CommonUtilityBundle:FgSmServices')->find($serviceId);
        $serviceType = $serviceObj->getServiceType();
        $tabType = $request->request->get('datatableListtype'); // Getting service name from id
        $tabTitle = '';
        $paymentPlantranslateArray = array('regular' => $this->get('translator')->trans('SM_REGULAR'),
            'custom' => $this->get('translator')->trans('SM_CUSTOM'),
            'none' => $this->get('translator')->trans('SM_NONE'),);
        // Checking the type of service
        // Export pdf is only for active and future services
        if ($tabType == 'activeservice') {
            $tab = 'active';
            $columnNames = $this->activeColumnsNameData($serviceType);
            $tabTitle = $this->get('translator')->trans('SM_ACTIVE_SERVICE');
        } elseif ($tabType == 'futureservice') {
            $tab = 'future';
            $columnNames = $this->futureColumnsNameData();
            $tabTitle = $this->get('translator')->trans('SM_FUTURE_SERVICE');
        }
        $fiscalYear = $this->container->get('club')->getFiscalYear(); // Getting current fiscal year
        $club = $this->get('club');
        // Section to generate export pdf from array
        if (!empty($columnData)) {
            $title = trim($serviceName) . ' (' . $tabTitle . ')';
            // Rendered PDF contents
            $bodyNew = $this->container->get('templating')->render('ClubadminSponsorBundle:serviceExport:sponsorServicesPdf.html.twig', array('results' => $columnData, 'title' => $title, 'today' => date($club->get('phpdate')), 'fiscalYear' => $fiscalYear, 'paymentTranslateArray' => $paymentPlantranslateArray));
            $bodyNew = html_entity_decode($bodyNew);
            $clubname = ucfirst($this->clubTitle);
            $page = $this->get('translator')->trans('PAGE');
            $pdfEngine = $this->get('knp_snappy.pdf');
            $response = new Response();
            $response->setContent(
                $pdfEngine->getOutputFromHtml(
                    utf8_decode($bodyNew), array(
                    'header-left' => $clubname,
                    'header-line' => true,
                    'footer-right' => $page . ' ' . '[page]/[topage]',
                    'footer-line' => true,
                    'orientation' => 'Landscape',
                    )
                )
            );
            $filename = $title . '_' . date($club->get('phpdate')) . '_' . date($club->get('phptime')) . '.pdf';
            $response->setStatusCode(200);
            $response->headers->set('Content-Type', 'application/pdf; charset=utf-8;');
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '";');
            $response->headers->set('Content-Transfer-Encoding', 'binary');

            return $response;
        }
    }

    /**
     * Function to get optimized payment plan data for export.
     *
     * @param array $columnData                column data array
     * @param array $paymentPlantranslateArray payment plan translate array
     *
     * @return string
     */
    public function paymentPlanOptimize($columnData, $paymentPlantranslateArray)
    {
        if ($columnData['SA_paymentplan'] == 'regular' && $columnData['SA_paymentplan'] != '') {
            $monthData = explode('|', $columnData['paymentplanDetails']);
            $paymentPlan = $paymentPlantranslateArray[$columnData['SA_paymentplan']] . ' (' . str_replace('%month%', $monthData[1], $this->get('translator')->trans('SM_EVERY_MONTHS')) . ')';
        } elseif ($columnData['SA_paymentplan'] == 'custom' && $columnData['SA_paymentplan'] != '') {
            $paymentData = explode('|', $columnData['paymentplanDetails']);
            $paymentPlan = ($paymentData[1] == 1) ? $paymentPlantranslateArray[$columnData['SA_paymentplan']] . ' (' . str_replace('%count%', $paymentData[1], $this->get('translator')->trans('SM_SERVICE_EXPORT_CUSTOM_PAYMENT_SINGULAR')) . ')' : $paymentPlantranslateArray[$columnData['SA_paymentplan']] . ' (' . str_replace('%count%', $paymentData[1], $this->get('translator')->trans('SM_SERVICE_EXPORT_CUSTOM_PAYMENT_PLURAL')) . ')';
        } else {
            $paymentPlan = $columnData['SA_paymentplan'];
        }

        return $paymentPlan;
    }

    /**
     * Function to get optimized deposited with data for export.
     *
     * @param array  $columnData         column data array
     * @param object $terminologyService terminology service
     * @param array  $serviceType        service type
     *
     * @return string or null
     */
    public function depositedwithOptimize($columnData, $terminologyService, $serviceType)
    {
        if ($columnData['SA_depositedwith'] == '') {
            $depositedVal = '';
        } else {
            $depositedJson = '[' . $columnData['SA_depositedwith'] . ']';
            $depositedArray = json_decode($depositedJson, true);
            if (count($depositedArray) == 1) {
                $depositedVal = $depositedArray[0]['name'];
            } else {
                $depositedVal = ($serviceType == 'team') ? count($depositedArray) . ' ' . $terminologyService->getTerminology('Team', $this->container->getParameter('plural')) : count($depositedArray) . ' ' . $this->get('translator')->trans('SM_SERVICE_EXPORT_CONTACTS');
            }
        }

        return $depositedVal;
    }

    /**
     * Function to get optimized total payment value  data for export.
     *
     * @param array $columnData column data array
     *
     * @return string or null
     */
    public function totalpaymentOptimize($columnData)
    {
        if (($columnData['SA_paymentenddate'] == 'null' && ($columnData['SA_last_payment_date'] == 'null' || $columnData['SA_last_payment_date'] == '' )) && $columnData['SA_paymentplan'] == 'regular') {
            $totalPayment = '-';
        } elseif ($columnData['SA_totalPayment'] == '') {
            $totalPayment = '';
        } else {
            $totalPayment = $columnData['SA_totalPayment'];
        }

        return $totalPayment;
    }

    /**
     * Function to execute assignment overview csv export functionality.
     *
     * @return response
     */
    public function assignmentOverviewExportCsvAction(Request $request)
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '2000M');
        $assignmentType = $request->get('tabType', '');

        if ($assignmentType == 'activeassignments') {
            $tabtype = 'active_assignments';
            $columnNames = $this->activeassignmentsColumnsNameData();
            $tabTitle = $this->get('translator')->trans('SM_ACTIVE_ASSIGNMENTS');
        } elseif ($assignmentType == 'futureassignments') {
            $tabtype = 'future_assignments';
            $columnNames = $this->activeassignmentsColumnsNameData();
            $tabTitle = $this->get('translator')->trans('SM_FUTURE_ASSIGNMENTS');
        } elseif ($assignmentType == 'formerassignments') {
            $tabtype = 'former_assignments';
            $columnNames = $this->formerassignmentsColumnsNameData();
            $tabTitle = $this->get('translator')->trans('FORMER_ASSIGNMENTS');
        } else {
            $tabtype = 'recently_ended';
            $columnNames = $this->formerassignmentsColumnsNameData();
            $tabTitle = $this->get('translator')->trans('RECENTLY_ENDED');
        }

        $exportData = json_decode($request->get('exportData'), true);
        $columnData = $this->optimizeColumnData($exportData);
        $csvType = $request->get('csvType', '');
        $delimiter = ($csvType == 'colonSep') ? ';' : ',';
        $response = $this->assignmentexportCsvFile($columnData, $delimiter, $tabTitle, $columnNames, $tabtype);

        return $response;
    }

    /**
     * Function to get the active and future assignments column names.
     *
     * @return array
     */
    private function activeassignmentsColumnsNameData()
    {
        $fiscalData = $this->fiscalYearDetails();
        $activeColumnNames = array($this->get('translator')->trans('SM_START'),
            $this->get('translator')->trans('SM_END'),
            $this->get('translator')->trans('SM_CONTACT'),
            $this->get('translator')->trans('SM_SERVICE'),
            $this->get('translator')->trans('SM_PAYMENT_PLAN'),
            $this->get('translator')->trans('SM_NEXTPAYMENT_DATE'),
            str_replace('%yr%', $fiscalData['currentFiscalLabel'], $this->get('translator')->trans('SM_PAYMENTS_CURR')) . ' (' . $this->get('club')->get('clubCurrency') . ')',
            str_replace('%yr%', $fiscalData['nextFiscalLabel'], $this->get('translator')->trans('SM_PAYMENTS_NEX')) . ' (' . $this->get('club')->get('clubCurrency') . ')',
            $this->get('translator')->trans('SM_TOTAL_PAYMENT') . ' (' . $this->get('club')->get('clubCurrency') . ')',
        );

        return $activeColumnNames;
    }

    /**
     * Function to get the former and recently deleted assignments column names.
     *
     * @return array
     */
    private function formerassignmentsColumnsNameData()
    {
        $fiscalData = $this->fiscalYearDetails();
        $formerColumnNames = array($this->get('translator')->trans('SM_START'),
            $this->get('translator')->trans('SM_END'),
            $this->get('translator')->trans('SM_CONTACT'),
            $this->get('translator')->trans('SM_SERVICE'),
            $this->get('translator')->trans('SM_PAYMENT_PLAN'),
            str_replace('%yr%', $fiscalData['currentFiscalLabel'], $this->get('translator')->trans('SM_PAYMENTS_CURR')) . ' (' . $this->get('club')->get('clubCurrency') . ')',
            $this->get('translator')->trans('SM_TOTAL_PAYMENT') . ' (' . $this->get('club')->get('clubCurrency') . ')',
        );

        return $formerColumnNames;
    }

    /**
     * Function to get the former sponsors column names.
     *
     * @return array
     */
    public function getAssignmentlistdata($tabtype, $search, $selectedIds)
    {
        $sponsorlistData = new ContactlistData($this->contactId, $this->container, 'servicelist');
        $servicelistData = new Servicelist($this->container);
        $servicelistData->tabType = $tabtype;
        $servicelistData->searchval = $search;
        if ($selectedIds != 0) {
            $servicelistData->bookedIds = $selectedIds;
        }

        $jsonColumns = $servicelistData->getServicetableColumns();
        $sponsorlistData->tabledata = json_decode($jsonColumns, true);
        $tableColumns = $sponsorlistData->getTableColumns();
        $servicelistData->columns = $tableColumns;
        $servicelistData->setFrom();
        $servicelistData->setColumns();
        $servicelistData->setCondition();
        $listQuery = $servicelistData->getResult();

        $result = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($listQuery);

        return $result;
    }

    /**
     * Function to generate the CSV data for assignment overview export.
     *
     * @param array  $columnData  column data array
     * @param string $delimiter   delimiter
     * @param string $tabtype     tab type
     * @param array  $columnNames column names array
     *
     * @return string
     */
    private function generateAssignmentCsvData($columnData, $delimiter, $tabtype, $columnNames)
    {
        $csv = '';
        $delimiter = '"' . $delimiter . '"';
        $csv = '"' . implode($delimiter, str_replace('"', '', $columnNames)) . '"';
        $csv .= "\n";
        $paymentPlan = '';
        $currentPaymentTotal = '';
        $nextPaymentTotal = '';
        $paymentPlantranslateArray = array('regular' => $this->get('translator')->trans('SM_REGULAR'),
            'custom' => $this->get('translator')->trans('SM_CUSTOM'),
            'none' => $this->get('translator')->trans('SM_NONE'),);

        foreach ($columnData as $key => $value) {
            $resultArray = array();
            $paymentPlan = $this->paymentPlanOptimize($value, $paymentPlantranslateArray);
            $resultArray['SA_paymentstartdate'] = $value['SA_paymentstartdate'];
            $resultArray['SA_paymentenddate'] = ($value['SA_paymentenddate'] == 'null') ? '' : $value['SA_paymentenddate'];
            $resultArray['contactname'] = $value['contactname'];
            $resultArray['SA_serviceName'] = $value['SA_serviceName'];
            $resultArray['SA_paymentplan'] = $paymentPlan;

            if (array_key_exists('SA_paymentDate', $value)) {
                $paymentDateexportData = '';
                if ($value['SA_paymentDate'] != '') {
                    $paymentDate = explode('|', $value['SA_paymentDate']);
                    $paymentDateexportData = $paymentDate[0] . ' (' . $this->get('club')->getAmountWithCurrency($paymentDate[1], true) . ')';
                    $paymentDateexportData = str_replace(array('&#8239;', '&#8217;'), array('', '`'), $paymentDateexportData);
                }
                $resultArray['SA_paymentDate'] = $paymentDateexportData;
            }

            if (array_key_exists('SA_paymentCurr', $value)) {
                $resultArray['SA_paymentCurr'] = ($value['SA_paymentCurr'] == '') ? '' : $this->get('club')->formatDecimalMark($value['SA_paymentCurr']);
                $currentPaymentTotal += $value['SA_paymentCurr'];
            }

            if (array_key_exists('SA_paymentNext', $value)) {
                $resultArray['SA_paymentNext'] = ($value['SA_paymentNext'] == '') ? '' : $this->get('club')->formatDecimalMark($value['SA_paymentNext']);
                $nextPaymentTotal += $value['SA_paymentNext'];
            }

            $totalPayment = $this->totalpaymentOptimize($value);
            $resultArray['SA_totalPayment'] = $this->get('club')->formatDecimalMark($totalPayment);

            $csv .= '"' . implode($delimiter, str_replace('"', '', $resultArray)) . '"' . "\n";
        }
        $csv .= $this->lastRowAssignmentCsv($currentPaymentTotal, $nextPaymentTotal, $tabtype, $delimiter);

        return $csv;
    }

    /**
     * Function to get the last row  for assignment overview csv export.
     *
     * @param float  $currentPaymentTotal current total payment
     * @param float  $nextPaymentTotal    next payment toral
     * @param string $tabtype             tab type
     * @param string $delimiter           delimiter
     *
     * @return string
     */
    private function lastRowAssignmentCsv($currentPaymentTotal, $nextPaymentTotal, $tabtype, $delimiter)
    {
        $lastColumnArray = array();
        $lastColumnArray['SA_paymentstartdate'] = $this->get('translator')->trans('SM_TOTAL');
        $lastColumnArray['SA_paymentenddate'] = '';
        $lastColumnArray['contactname'] = '';
        $lastColumnArray['SA_serviceName'] = '';
        $lastColumnArray['SA_paymentplan'] = '';
        if ($tabtype == 'active_assignments' || $tabtype == 'future_assignments') {
            $lastColumnArray['SA_paymentDate'] = '';
        }

        $lastColumnArray['SA_paymentCurr'] = $this->get('club')->formatDecimalMark($currentPaymentTotal);
        if ($tabtype == 'active_assignments' || $tabtype == 'future_assignments') {
            $lastColumnArray['SA_paymentNext'] = $this->get('club')->formatDecimalMark($nextPaymentTotal);
        }
        $lastColumnArray['SA_totalPayment'] = '';

        $csv = '"' . implode($delimiter, str_replace('"', '', $lastColumnArray)) . '"' . "\n";

        return $csv;
    }

    /**
     * Function to get the assignment overview csv file
     *
     * @param array  $columnData  column data
     * @param string $delimiter   delimiter
     * @param string $tabTitle    translated tab title
     * @param array  $columnNames column names
     * @param string $tabtype     tab type
     *
     * @return response
     */
    private function assignmentexportCsvFile($columnData, $delimiter, $tabTitle, $columnNames, $tabtype)
    {
        $filetitle = $tabTitle . '_' . date('Y-m-d') . '_' . date('H-i-s');
        $string = str_replace(' ', '%20', $filetitle);
        $filename = $string . '.csv';
        $response = new Response();
        // prints the HTTP headers followed by the content
        $response->setContent(utf8_decode($this->generateAssignmentCsvData($columnData, $delimiter, $tabtype, $columnNames)));
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $filename);
        $response->headers->set('Content-Transfer-Encoding', 'binary');

        return $response;
    }

    /**
     * Function to get the assignment overview pdf file
     *
     * @return response
     */
    public function assignmentOverviewExportPdfAction(Request $request)
    {
        $exportData = json_decode($request->request->get('exportData'), true);
        $columnData = $this->optimizeColumnData($exportData);
        $assignmentType = $request->request->get('datatableListtype');
        if ($assignmentType == 'activeassignments') {
            $tabtype = 'active_assignments';
            $tabTitle = $this->get('translator')->trans('SM_ACTIVE_ASSIGNMENTS');
        } elseif ($assignmentType == 'futureassignments') {
            $tabtype = 'future_assignments';
            $tabTitle = $this->get('translator')->trans('SM_FUTURE_ASSIGNMENTS');
        }

        $paymentPlantranslateArray = array('regular' => $this->get('translator')->trans('SM_REGULAR'),
            'custom' => $this->get('translator')->trans('SM_CUSTOM'),
            'none' => $this->get('translator')->trans('SM_NONE'),);
        $fiscalYear = $this->container->get('club')->getFiscalYear(); // Getting current fiscal year
        $club = $this->get('club');
        // Section to generate export pdf from array
        $bodyNew = $this->container->get('templating')->render('ClubadminSponsorBundle:serviceExport:assignmentOverviewPdf.html.twig', array('results' => $columnData, 'title' => $tabTitle, 'today' => date($club->get('phpdate')), 'fiscalYear' => $fiscalYear, 'paymentTranslateArray' => $paymentPlantranslateArray));
        $bodyNew = html_entity_decode($bodyNew);
        $clubname = ucfirst($this->clubTitle);
        $page = $this->get('translator')->trans('PAGE');
        $pdfEngine = $this->get('knp_snappy.pdf');
        $response = new Response();
        $response->setContent(
            $pdfEngine->getOutputFromHtml(
                utf8_decode($bodyNew), array(
                'header-left' => $clubname,
                'header-line' => true,
                'footer-right' => $page . ' ' . '[page]/[topage]',
                'footer-line' => true,
                'orientation' => 'Landscape',
                )
            )
        );
        $filename = $tabTitle . '_' . date($club->get('phpdate')) . '_' . date($club->get('phptime')) . '.pdf';
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/pdf; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->headers->set('Content-Transfer-Encoding', 'binary');

        return $response;
    }

    /**
     * Function to get the optimized column data.
     *
     * @param array $columnData column data
     *
     * @return array
     */
    public function optimizeColumnData($columnData)
    {
        if (array_key_exists('context', $columnData)) {
            unset($columnData['context']);
        }
        if (array_key_exists('length', $columnData)) {
            unset($columnData['length']);
        }
        if (array_key_exists('selector', $columnData)) {
            unset($columnData['selector']);
        }
        if (array_key_exists('ajax', $columnData)) {
            unset($columnData['ajax']);
        }

        return $columnData;
    }
}
