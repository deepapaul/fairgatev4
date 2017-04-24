<?php

/**
 * AnalysisController
 *
 * This controller was created for handling sponsor analysis listing functionalities
 *
 * @package    ClubadminSponsorBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
namespace Clubadmin\SponsorBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Common\UtilityBundle\Repository\Pdo\SponsorPdo;
use Symfony\Component\HttpFoundation\Request;
use Common\UtilityBundle\Util\FgUtility;

class AnalysisController extends FgController
{

    /**
     * To collect the sponsor analysis list data
     * @return JsonResponse
     */
    public function listServiceAnalysisAction()
    {

        $startDate = $this->em->getRepository('CommonUtilityBundle:FgSmBookings')->getServiceStartDate($this->clubId);
        if (empty($startDate['startDate'])) {
            $startDate['startDate'] = date("Y-m-d H:i:s");
        }
        $club = $this->get('club');
        $currentFiscalYear = $club->getFiscalYearStartDate(date("Y-m-d H:i:s"));
        $endCurrentYr = date('Y', strtotime($currentFiscalYear['end']));
        $endDate = preg_replace('~(\d{4})~', ($endCurrentYr + 2), $currentFiscalYear['end']);
        $fiscalYear = $club->getFiscalYearStartDate($startDate['startDate']);
        $fiscalYears = $this->em->getRepository('CommonUtilityBundle:FgSmBookings')->getFiscalYears($startDate['startDate'], $endDate, $startDate['startDate'], $club, $fiscalYear = array());
        $current = count($fiscalYears) - 3;
        $activeTab = $fiscalYears[$current];
        $tab[0] = $fiscalYears[$current];
        $tab[1] = $fiscalYears[$current + 1];
        $tab[2] = $fiscalYears[$current + 2];
        $tab1 = array();
        foreach ($fiscalYears as $key => $value) {
            if ($key < $current) {
                array_push($tab, $value);
                array_push($tab1, $value);
            }
        }
        $sponTrans = $this->get('translator')->trans('SPONSORS');
        $headerTabs = array(0 => 'service', 1 => 'sponsor');
        $trans = array('service' => $this->get('translator')->trans('SM_SERVICES'), 'sponsor' => $sponTrans);
        $baseUrl = FgUtility::getBaseUrl($this->container);
        $catHead = $this->em->getRepository('CommonUtilityBundle:FgSmCategory')->serviceCategory($this->clubId);

        $categoryArray = array();
        foreach ($catHead as $key => $val) {
            array_push($categoryArray, $val['categoryId']);
        }
        $headerColSpan = array_count_values($categoryArray);
        $catHead = json_encode($catHead, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);

        return $this->render('ClubadminSponsorBundle:Analysis:index.html.twig', array('baseUrl' => $baseUrl, 'fiscalYears' => $fiscalYears, 'tabs' => $tab, 'trans' => $trans,
                'contactId' => $this->contactId, 'activeTab' => 0, 'headerTabs' => $headerTabs, 'startDate' => $activeTab['start'],
                'endDate' => $activeTab['end'], 'catHead' => $catHead, 'tab1' => $tab1, 'colSpan' => $headerColSpan));
    }

    /**
     * returns a json for service analysis listing
     * @param type $startDate
     * @param type $endDate
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getListAction($startDate, $endDate)
    {
        $pdo = new SponsorPdo($this->container);
        $club = $this->container->get('club');
        $masterTable = $club->get('clubTable');
        $serviceList = $pdo->listSponsorService($startDate, $endDate, $this->clubId, $masterTable);

        return new JsonResponse(array('aaData' => $serviceList));
    }

    /**
     * function to generate pdf sponsor analysis
     */
    public function sponsorAnalysisPdfAction(Request $request)
    {
        $startDate = $request->request->get('startDate');
        $endDate = $request->request->get('endDate');
        $label = $request->request->get('yearLabel');
        $type = $request->request->get('tabtype');
        $club = $this->get('club');
        $currency = $club->get('clubCurrency');
        $pdo = new SponsorPdo($this->container);
        $title = $this->get('translator')->trans('SM_ANALYSIS');
        $exportData = json_decode($request->request->get('exportData'), true);
        $masterTable = $club->get('clubTable');

        if ($type == 0) {
            $results = $pdo->listSponsorService($startDate, $endDate, $this->clubId, $masterTable);
            $bodyNew = $this->container->get('templating')->render("ClubadminSponsorBundle:Analysis:services-pdf.html.twig", array('results' => $exportData, 'title' => $title, 'label' => $label, 'today' => date($club->get('phpdate')), 'currency' => $currency));
        } else {
            $results = $pdo->sponsorAnalysisPdf($startDate, $endDate, $this->clubId, $masterTable,$this->clubType);
            $bodyNew = $this->container->get('templating')->render("ClubadminSponsorBundle:Analysis:sponsor-pdf.html.twig", array('results' => $results['sponsor'], 'title' => $title, 'label' => $label, 'today' => date($club->get('phpdate')), 'currency' => $currency));
        }
        $bodyNew = html_entity_decode($bodyNew);
        $clubname = ucfirst($this->clubTitle);
        $page = $this->get('translator')->trans('PAGE');
        $pdfEngine = $this->get('knp_snappy.pdf');
        $response = new Response();
        $response->setContent(
            $pdfEngine->getOutputFromHtml(utf8_decode($bodyNew), array('header-left' => $clubname, 'header-line' => true, 'footer-right' => $page . ' ' . '[page]/[topage]', 'footer-line' => true,))
        );
        $filename = $title . '_' . date($club->get('phpdate')) . '_' . date($club->get('phptime')) . '.pdf';
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/pdf; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->headers->set('Content-Transfer-Encoding', 'binary');

        return $response;
    }

    public function getSponsorListAction($startDate, $endDate)
    {
        $pdo = new SponsorPdo($this->container);
        $club = $this->container->get('club');
        $masterTable = $club->get('clubTable');
        $results = $pdo->sponsorAnalysisListing($startDate, $endDate, $this->clubId, $masterTable);

        return new JsonResponse($results);
    }

    /**
     * Functin to show analysis export popup
     *
     * @return template
     */
    public function analysisExportpopupAction(Request $request)
    {
        $actionType = $request->get('actionType');
        if ($actionType == 'sa_export_csv') {
            $startDate = $request->get('startDate');
            $endDate = $request->get('endDate');
            $tabType = $request->get('tabType');
            $yearLabel = $request->get('yearLabel');
            $titleText = $this->get('translator')->trans('SM_SERVICE_EXPORT_SINGULAR');
            $path = ($tabType == 0 ) ? $this->generateUrl('sponsor_analysis_export') : $this->generateUrl('sponsor_analysis_csv');
        }

        $return = array('actionType' => $actionType, 'titleText' => $titleText, 'startDate' => $startDate, 'tabType' => $tabType, 'endDate' => $endDate, 'yearLabel' => $yearLabel, 'path' => $path);

        return $this->render('ClubadminSponsorBundle:Analysis:confirmAnalysisExportpopup.html.twig', $return);
    }

    /**
     * Function to execute analysis export
     *
     * @return response
     */
    public function analysisExportAction(Request $request)
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '2000M');
        $columnData = json_decode($request->get('exportData'), true);
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
        $tabType = $request->get('tabType');
        $tab = ($tabType == 0) ? "services" : "sponsor";
        $csvType = $request->get('csvType');
        $delimiter = ($csvType == "colonSep") ? ";" : ",";

        $columnNames = $this->servicesTabColumnNames();
        $response = $this->createCsvfile($columnData, $columnNames, $delimiter, $tab);
        return $response;
    }

    /**
     * Function to get analysis services tab column names
     *
     * @return array
     */
    public function servicesTabColumnNames()
    {
        $servicesTabColumnNames = array($this->get('translator')->trans('SA_SERVICE_CATEGORY'),
            $this->get('translator')->trans('SA_SERVICE'),
            $this->get('translator')->trans('SA_SPONSORS'),
            $this->get('translator')->trans('SA_PAYMENTS'),
            $this->get('translator')->trans('SA_TOTAL_AMOUNT'),
        );

        return $servicesTabColumnNames;
    }

    /**
     * Function to create the CSV file
     *
     * @param array  $columnData  column data array
     * @param array  $columnNames column names array
     * @param string $delimiter   delimiter
     *
     * @return response
     */
    private function createCsvfile($columnData, $columnNames, $delimiter, $tab, $amountTotal = 0, $catHead = array())
    {
        if ($tab == 'services') {
            $data = $this->servicesCsvData($columnData, $columnNames, $delimiter);
        } else {
            $data = $this->csvDataFormation($columnData, $columnNames, $delimiter, $amountTotal, $catHead);
        }

        $filetitle = $this->get('translator')->trans('SM_ANALYSIS');
        $filename = $filetitle . '_' . date('Y-m-d') . '_' . date('H-i-s') . '.csv';
        $response = new Response();
        $response->setContent(utf8_decode($data));
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $filename);
        $response->headers->set('Content-Transfer-Encoding', 'binary');

        return $response;
    }

    /**
     * Function to generate the services tab CSV data
     *
     * @param array  $columnData column data array
     * @param array  $columnNames column names array
     * @param string $delimiter  delimiter
     *
     * @return string
     */
    public function servicesCsvData($columnData, $columnNames, $delimiter)
    {
        $delimiter = '"' . $delimiter . '"';
        $csv = '';
        $amountTotal = 0;
        $csv = '"' . implode($delimiter, str_replace('"', '', $columnNames)) . '"';
        $csv .= "\n";
        foreach ($columnData as $key => $value) {
            $resultArray = array();
            $resultArray['category'] = $value['category'];
            $resultArray['service'] = $value['service'];
            $resultArray['sponsors'] = $value['sponsors'];
            $resultArray['payments'] = $value['payments'];
            $resultArray['amt'] = $this->get('club')->getAmountWithCurrency(number_format((float) $value['amt'], 2, '.', ''), true);
            $amountTotal += $value['amt'];
            $csv .= '"' . implode($delimiter, str_replace('"', '', $resultArray)) . '"' . "\n";
        }
        $csv .= $this->exportlastRowData($amountTotal, $delimiter);

        return $csv;
    }

    /**
     * Function to get the last row for analysis services tab export
     *
     * @param int    $amountTotal    column data array
     * @param string $delimiter      delimiter
     * @param int    $totalSponsors  total sponsor count
     * @param int    $totalpayments  total payment count
     *
     * @return string
     */
    public function exportlastRowData($amountTotal, $delimiter)
    {
        $lastColumnArray = array();
        $lastColumnArray['category'] = $this->get('translator')->trans('SM_TOTAL');
        $lastColumnArray['service'] = '';
        $lastColumnArray['sponsors'] = '';
        $lastColumnArray['payments'] = '';
        $lastColumnArray['amt'] = $this->get('club')->getAmountWithCurrency(number_format((float) $amountTotal, 2, '.', ''), true);
        $lastcsvRow = '"' . implode($delimiter, str_replace('"', '', $lastColumnArray)) . '"' . "\n";
        return $lastcsvRow;
    }

    /**
     * function to export sponser analysis (csv)
     * @return response
     */
    public function exportCsvSponsorAction(Request $request)
    {
        $csvType = $request->get('csvType');
        $dataArray = json_decode($request->get('exportData'));
        $catHead = $this->em->getRepository('CommonUtilityBundle:FgSmCategory')->serviceCategory($this->clubId);
        $columnNames['contact'] = $this->get('translator')->trans('SPONSOR');
        $finalArray = $amountTotal = array();

        foreach ($catHead as $key => $value) {
            //CREATE COLUMN HEADER ARRAY
            $columnNames[$key] = $value['serviceTitle'];

            //CREATE DATA ARRAY AND CALCULATE TOTAL AMOUNT
            $service = $value['servicesId'];
            $amountTotal[$key][0] = 0;
            foreach ($dataArray as $key1 => $val1) {
                $finalArray[$key1]['contact'] = $val1->contact;
                $fAmt = ($val1->$service !== '') ? ($val1->$service == 0) ? $val1->$service : $val1->$service : '';
                $finalArray[$key1][] = $this->get('club')->formatDecimalMark($fAmt);
                $finalArray[$key1]['rowTotal'] = $this->get('club')->formatDecimalMark($val1->rowTotal);
                $amountTotal[$key][0] = $amountTotal[$key][0] + $val1->$service;
            }
        }
        $response = $this->createCsvfile($finalArray, $columnNames, $csvType, 'sponsor', $amountTotal, $catHead);

        return $response;
    }

    /**
     * function to form data for csv
     * @param array  $finalArray  data Array
     * @param array  $columnNames columnNames
     * @param string $csvType     csv Type
     * @param array $amountTotal  amount Total array
     * @return string
     */
    private function csvDataFormation($finalArray, $columnNames, $csvType, $amountTotal, $catHead)
    {

        $currency = $this->get('club')->get('clubCurrency');
        $delimiter = ($csvType == "colonSep") ? ";" : ",";
        $transTotal = $this->get('translator')->trans('TOTAL');
        //CSV CATEGORY HEADER
        $csv = $this->csvCatHeader($catHead, $delimiter);
        $csv .= '"' . $transTotal . ' (' . $currency . ')' . '"' . "\n";

        //CSV SERVICE HEADER
        $final = '"' . $transTotal . ' (' . $currency . ')' . '"' . $delimiter . '"';
        $delimiter = '"' . $delimiter . '"';
        $csv .= '"' . implode($delimiter, $columnNames) . '"' . "\n";


        //CSV BODY
        foreach ($finalArray as $value) {
            $resultArray = array();
            $resultArray['amt'] = $value['rowTotal'];
            unset($value['rowTotal']);
            $csv .= '"' . implode($delimiter, $value) . $delimiter . $resultArray['amt'] . '"' . "\n";
        }

        //calculate colTotal of total
        $fTotal = 0;
        for ($i = 0; $i < sizeof($amountTotal); $i++) {
            $amt = $amountTotal[$i][0];
            $fTotal += $amountTotal[$i][0];
            $final .= $this->get('club')->formatDecimalMark($amt) . $delimiter;
        }
        $final .= $this->get('club')->formatDecimalMark($fTotal) . $delimiter;
        $csv = $csv . "" . $final;

        return $csv;
    }

    /**
     * function to create csv category header
     * @param array $catHead catogory array
     * @param string $delimiter seperator
     * @return string
     */
    private function csvCatHeader($catHead, $delimiter)
    {
        $csv = $delimiter;
        //CSV CATEGORY HEADER
        $prevCat = '';
        foreach ($catHead as $value) {
            if ($prevCat == $value['categoryId']) {
                $catTitle = '';
            } else {
                $catTitle = $value['catTitle'];
            }
            $csv .= '"' . $catTitle . '"' . $delimiter;
            $prevCat = $value['categoryId'];
        }

        return $csv;
    }
}
