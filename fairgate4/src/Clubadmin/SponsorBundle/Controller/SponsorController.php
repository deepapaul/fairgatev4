<?php

/**
 * SponsorController.
 *
 * This controller was created for handling sponsor listing functionalities
 *
 * @author     pitsolutions.ch
 *
 * @version    Fairgate V4
 */
namespace Clubadmin\SponsorBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Clubadmin\Util\Contactlist;
use Clubadmin\Classes\Contactdatatable;
use Clubadmin\ContactBundle\Util\ContactlistData;
use Common\UtilityBundle\Util\FgUtility;
use Symfony\Component\HttpFoundation\Response;
use Common\UtilityBundle\Repository\Pdo\SponsorPdo;
use Symfony\Component\HttpFoundation\Request;

class SponsorController extends FgController
{

    /**
     * For collect the sponsor list data.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listsponsorAction(Request $request)
    {
        $contactType = $request->get('contactType', 'sponsor');
        //Set all request value to its corresponding variables
        $sponsorlistData = new ContactlistData($this->contactId, $this->container, $contactType);
        $sponsorlistData->filterValue = $request->get('filterdata', '');
        //check if the request is valid or not
        if ($sponsorlistData->filterValue != '' && $sponsorlistData->filterValue == '0') {
            $output = array("iTotalRecords" => 0, "iTotalDisplayRecords" => 0, "aaData" => array());

            return new JsonResponse($output);
        }
        $sponsorlistData->dataTableColumnData = $request->get('columns', '');
        $sponsorlistData->sortColumnValue = $request->get('order', '');
        $sponsorlistData->searchval = $request->get('search', '');
        $sponsorlistData->tableFieldValues = $request->get('tableField', '');
        $sponsorlistData->startValue = $request->get('start', '');
        $sponsorlistData->displayLength = $request->get('length', '');
        $sponsorlistData->functionTypeValue = 'none';
        $sponsorlistData->groupByColumn = 'fg_cm_contact.id';

        //For get the contact list array
        $contactData = $sponsorlistData->getContactData();
        $this->session->set('contactType', $contactType);
        //collect total number of records
        $totalrecords = $contactData['totalcount'];
        //For set the datatable json array
        $output = array("iTotalRecords" => $totalrecords, "iTotalDisplayRecords" => $totalrecords, "aaData" => array());
        $this->session->set($this->contactId . $this->clubId, $totalrecords);
        // Section for next and previous functionality
        $sponsorlistData->setSponsorSessionValues($contactData['data']);
        //iterate the result
        $contactDatatabledata = new Contactdatatable($this->container, $this->get('club'));
        $output['aaData'] = $contactDatatabledata->iterateDataTableData($contactData['data'], $this->container->getParameter('country_fields'), $sponsorlistData->tabledata);
        $output['aaDataType'] = $this->getContactFieldDetails($sponsorlistData->tabledata);

        return new JsonResponse($output);
    }

    /**
     * Function to view the contact of a club or federation.
     *
     * @return Template
     */
    public function viewsponsorAction()
    {
        $breadCrumb = array('breadcrumb_data' => array());
        $allTableSettings = $this->em->getRepository('CommonUtilityBundle:FgTableSettings')->getAllTableSettings($this->clubId, $this->contactId, 'SPONSOR');
        $editUrl = $this->generateUrl('edit_contact', array('contact' => 'dummy'), true);
        $container = $this->container;
        $defaultSettings = $container->getParameter('default_sponsor_table_settings');
        $fiscalYear = $this->container->get('club')->getFiscalYear();
        $invAddrFieldIds = array();
        $corrAddrFieldIds = array();
        $contactFields = $this->get('club')->get('contactFields');
        $corrAddrCatId = $container->getParameter('system_category_address');
        $invAddrCatId = $container->getParameter('system_category_invoice');
        foreach ($contactFields as $contactField) {
            if ($contactField['catId'] == $corrAddrCatId) {
                $corrAddrFieldIds[] = $contactField['id'];
            } elseif ($contactField['catId'] == $invAddrCatId) {
                $invAddrFieldIds[] = $contactField['id'];
            }
        }
        $clubHeirarchy = $this->container->get('club')->get('clubHeirarchy');
        $federationClubId = (count($clubHeirarchy) > 0) ? $clubHeirarchy[0] : $this->clubId;
        $tabs = array('activeservice', 'futureservice', 'formerservice');
        $finalTabsArray = FgUtility::getTabsArrayDetails($this->container, $tabs, '', '', '', 'activeservice', 'service');

        return $this->render('ClubadminSponsorBundle:SponsorList:sponsorlist.html.twig', array('breadCrumb' => $breadCrumb, 'clubId' => $this->clubId, 'contactId' => $this->contactId, 'allTableSettings' => $allTableSettings, 'defaultSettings' => $defaultSettings, 'editUrl' => $editUrl, 'contacttype' => 'sponsor', 'urlIdentifier' => $this->clubUrlIdentifier, 'clubType' => $this->clubType, 'corrAddrFieldIds' => $corrAddrFieldIds, 'invAddrFieldIds' => $invAddrFieldIds, 'fiscalYear' => $fiscalYear, 'tabs' => $finalTabsArray, 'fedClubId' => $federationClubId, 'currentClubType' => $this->container->get('club')->get('type')));
    }

    /**
     * For get the type of selected contact fields
     * @param array $tabledatas
     *
     * @return array
     */
    private function getContactFieldDetails($tabledatas)
    {
        $originalTitlesArray = $this->container->getParameter('country_fields');
        $originalTitlesArray[] = $this->container->getParameter('system_field_corress_lang');
        $originalTitlesArray[] = $this->container->getParameter('system_field_salutaion');
        $originalTitlesArray[] = $this->container->getParameter('system_field_gender');
        $output = $this->getOutputArray($tabledatas);
        $output['aaDataType'][] = array("title" => 'contactname', "type" => "contactname");
        $output['aaDataType'][] = array("title" => 'edit', "type" => "edit");
        $output['aaDataType'][] = array("title" => "joining_date", "type" => "joining_date", "currentClubId" => $this->clubId);
        $output['aaDataType'][] = array("title" => "leaving_date", "type" => "leaving_date", "currentClubId" => $this->clubId);
        $output['aaDataType'][] = array("title" => "SAactive_assignments", "type" => "SAactive_assignments", 'fieldname' => 'activeServices');
        $output['aaDataType'][] = array("title" => "SAfuture_assignments", "type" => "SAfuture_assignments", 'fieldname' => 'futureServices');
        $output['aaDataType'][] = array("title" => "SApast_assignments", "type" => "SApast_assignments", 'fieldname' => 'pastServices');

        $output['aaDataType'][] = array("title" => "payments_nex", "type" => "SApayments_nex", 'fieldname' => 'Nextpayments');
        $output['aaDataType'][] = array("title" => "payments_curr", "type" => "SApayments_curr", 'fieldname' => 'Currentpayments');
        $output['aaDataType'][] = array("title" => "Gprofile_company_pic", "type" => "Gprofile_company_pic");

        $this->servicetypeDetails($tabledatas, $output);

        return $output['aaDataType'];
    }

    /**
     *
     * @param type $tabledatas contact field details
     *
     * @return array
     */
    private function getOutputArray($tabledatas)
    {
        $output['aaDataType'] = array();
        $club = $this->get('club');
        $allContactFiledsData = $club->get('allContactFields');
        //service for contact field/profile image path
        $pathService = $this->container->get('fg.avatar');
        foreach ($tabledatas as $contactFields) {
            if (array_key_exists($contactFields['id'], $allContactFiledsData) && $contactFields['type'] == 'CF') {
                switch ($allContactFiledsData[$contactFields['id']]['type']) {
                    case "login email":case "email":case "Email":
                        $output['aaDataType'][] = array("title" => "CF_" . $contactFields['id'], "type" => "email", 'attrId' => $contactFields['id'], 'category_id' => $allContactFiledsData[$contactFields['id']]['category_id'], 'is_editable' => $allContactFiledsData[$contactFields['id']]['is_editable'], 'is_required' => $allContactFiledsData[$contactFields['id']]['is_required'], 'is_company' => $allContactFiledsData[$contactFields['id']]['is_company'], 'is_personal' => $allContactFiledsData[$contactFields['id']]['is_personal'], 'is_system_field' => $allContactFiledsData[$contactFields['id']]['is_system_field'], 'addres_type' => $allContactFiledsData[$contactFields['id']]['addres_type'], 'address_id' => $allContactFiledsData[$contactFields['id']]['address_id']);
                        break;
                    case "imageupload":
                        $output['aaDataType'][] = array("title" => "CF_" . $contactFields['id'], "type" => "imageupload", "uploadPath" => $pathService->getContactfieldPath($contactFields['id']));
                        break;
                    case "fileupload":
                        $output['aaDataType'][] = array("title" => "CF_" . $contactFields['id'], "type" => "fileupload", "uploadPath" => $pathService->getContactfieldPath($contactFields['id']));
                        break;
                    case "url":case "multiline":case "singleline":
                        $output['aaDataType'][] = array("title" => "CF_" . $contactFields['id'], "type" => $allContactFiledsData[$contactFields['id']]['type'], 'attrId' => $contactFields['id'], 'category_id' => $allContactFiledsData[$contactFields['id']]['category_id'], 'is_editable' => $allContactFiledsData[$contactFields['id']]['is_editable'], 'is_required' => $allContactFiledsData[$contactFields['id']]['is_required'], 'is_company' => $allContactFiledsData[$contactFields['id']]['is_company'], 'is_personal' => $allContactFiledsData[$contactFields['id']]['is_personal'], 'is_system_field' => $allContactFiledsData[$contactFields['id']]['is_system_field'], 'addres_type' => $allContactFiledsData[$contactFields['id']]['addres_type'], 'address_id' => $allContactFiledsData[$contactFields['id']]['address_id']);
                        break;
                    case "select":
                        if (in_array($contactFields['id'], $originalTitlesArray)) {
                            $output['aaDataType'][] = array("title" => "CF_" . $contactFields['id'], "type" => "select", "originalTitle" => "CF_" . $contactFields['id'] . "_original", 'attrId' => $contactFields['id'], 'category_id' => $allContactFiledsData[$contactFields['id']]['category_id'], 'is_editable' => $allContactFiledsData[$contactFields['id']]['is_editable'], 'is_required' => $allContactFiledsData[$contactFields['id']]['is_required'], 'is_company' => $allContactFiledsData[$contactFields['id']]['is_company'], 'is_personal' => $allContactFiledsData[$contactFields['id']]['is_personal'], 'is_system_field' => $allContactFiledsData[$contactFields['id']]['is_system_field'], 'addres_type' => $allContactFiledsData[$contactFields['id']]['addres_type'], 'address_id' => $allContactFiledsData[$contactFields['id']]['address_id']);
                        } else {
                            $output['aaDataType'][] = array("title" => "CF_" . $contactFields['id'], "type" => "select", 'attrId' => $contactFields['id'], 'category_id' => $allContactFiledsData[$contactFields['id']]['category_id'], 'is_editable' => $allContactFiledsData[$contactFields['id']]['is_editable'], 'is_required' => $allContactFiledsData[$contactFields['id']]['is_required'], 'is_company' => $allContactFiledsData[$contactFields['id']]['is_company'], 'is_personal' => $allContactFiledsData[$contactFields['id']]['is_personal'], 'is_system_field' => $allContactFiledsData[$contactFields['id']]['is_system_field'], 'addres_type' => $allContactFiledsData[$contactFields['id']]['addres_type'], 'address_id' => $allContactFiledsData[$contactFields['id']]['address_id']);
                        }
                        break;
                    default:
                        $output['aaDataType'][] = array("title" => "CF_" . $contactFields['id'], "type" => $allContactFiledsData[$contactFields['id']]['type'], 'attrId' => $contactFields['id'], 'category_id' => $allContactFiledsData[$contactFields['id']]['category_id'], 'is_editable' => $allContactFiledsData[$contactFields['id']]['is_editable'], 'is_required' => $allContactFiledsData[$contactFields['id']]['is_required'], 'is_company' => $allContactFiledsData[$contactFields['id']]['is_company'], 'is_personal' => $allContactFiledsData[$contactFields['id']]['is_personal'], 'is_system_field' => $allContactFiledsData[$contactFields['id']]['is_system_field'], 'addres_type' => $allContactFiledsData[$contactFields['id']]['addres_type'], 'address_id' => $allContactFiledsData[$contactFields['id']]['address_id']);
                        break;
                }
            }
        }

        return $output;
    }

    /**
     * Function to create sponsor category or sub category
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createCategoryAction(Request $request)
    {
        $terminologyService = $this->get('fairgate_terminology_service');
        $title = $request->get('value');
        $elementType = $request->get('elementType');

        if ($elementType == 'category') {
            $lastInsertedId = $this->em->getRepository('CommonUtilityBundle:FgSmCategory')->saveCategorySidebar($this->clubId, $title);
            $return = array('items' => array('0' => array('id' => $lastInsertedId, 'title' => str_replace('"', '', stripslashes($title)), 'type' => 'select', 'draggable' => 1)));
        } elseif ($elementType == 'service') {
            $categoryId = $request->get('category_id');
            $lastInsertedId = $this->em->getRepository('CommonUtilityBundle:FgSmCategory')->createServiceSidebar($this->clubId, $categoryId, $title, $this->container, $this->contactId, $terminologyService);
            $return = array('input' => array('0' => array('categoryId' => $categoryId, "draggableClass" => "fg-dev-draggable", 'id' => $lastInsertedId, 'itemType' => 'service', 'count' => 0, 'title' => str_replace('"', '', stripslashes($title)), 'type' => 'select', 'draggable' => 1, 'bookMarkId' => '')));
        }
        return new JsonResponse($return);
    }

    /**
     * Function to render popup page for add prospects from existing contacts
     * @return Template
     */
    public function popupAddExistingProspectAction()
    {

        return $this->render('ClubadminSponsorBundle:SponsorList:addProspectsFromExistingPopup.html.twig');
    }

    /**
     * This method is used for getting all active contacts who are not sponsors
     * to list in the autocomplete fields to add sponsors from existing contacts
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getContactsForSponsorsAction(Request $request)
    {
        $searchTerm = FgUtility::getSecuredData($request->get('term'), $this->conn);
        $club = $this->get('club');
        $dob = $this->container->getParameter('system_field_dob');
        $contactlistClass = new Contactlist($this->container, '', $club, 'contact');
        $contacts = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getContactsForSearch($contactlistClass, $dob, $searchTerm, 'sponsor');
        return new JsonResponse($contacts);
    }

    /**
     * This method is used for assigning contacts as sponsors
     * used in add prospects from existing contacts
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function assignContactstoSponsorsAction(Request $request)
    {
        $contactIds = FgUtility::getSecuredData($request->get('contactIds'), $this->conn);
        $club = $this->container->get('club');
        $masterTable = $club->get('clubTable');
        $changedBy = $this->contactId;
        $this->em->getRepository('CommonUtilityBundle:FgCmContact')->assignContactsAsSponsors($contactIds, $masterTable, $changedBy, $this->clubId, $this->container);
        return new JsonResponse(array("flash" => $this->get('translator')->trans('SPONSOR_ADDED_SUCCESSFULLY')));
    }

    /**
     * This method is for removing prospects
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function removeProspectsAction(Request $request)
    {
        $contactIds = FgUtility::getSecuredData($request->get('contactids'), $this->conn);
        $totalcount = FgUtility::getSecuredData($request->get('totalcount'), $this->conn);
        $club = $this->container->get('club');
        $masterTable = $club->get('clubTable');
        $changedBy = $this->contactId;
        $removedProspectsCount = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->removeProspects($contactIds, $masterTable, $changedBy, $this->container);
        return new JsonResponse(array("flash" => $this->get('translator')->trans('SPONSOR_REMOVED_SUCCESSFULLY', array("%removedCount%" => $removedProspectsCount, "%totalCount%" => $totalcount))));
    }

    /**
     * Function to render popup page for remove prospects
     *
     * @param Request $request
     *
     * @return type
     */
    public function removeProspectsPopupAction(Request $request)
    {
        if ($request->get('actionType') === 'all') {
            $contactIds = $request->get('contactids');
        } else {
            $strContactIds = FgUtility::getSecuredData($request->get('contactids'), $this->conn);
            $contactIds = explode(",", $strContactIds);
        }
        $count = count($contactIds);
        $notProspects = array();
        $sponsors = array();
        $club = $this->container->get('club');
        $masterTable = $club->get('clubTable');
        $contactNames = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getContactName($contactIds, '', $club, $this->container);
        if ($count > 0) {
            foreach ($contactIds as $contact) {
                $sponsors[$contact] = $contactNames[$contact];
                $isProspect = $this->em->getRepository('CommonUtilityBundle:FgSmBookings')->isProspect($contact, $masterTable, $this->container);
                if (!$isProspect) {
                    $notProspects[$contact] = $contactNames[$contact];
                }
            }
        }
        $prospects = array_diff($sponsors, $notProspects);
        return $this->render('ClubadminSponsorBundle:SponsorList:removeProspectsPopup.html.twig', array("sponsors" => $sponsors, "count" => $count, "notProspects" => $notProspects, "prospects" => implode(",", array_keys($prospects)), "prospectsCount" => count($prospects)));
    }

    /**
     *
     * @param array $tabledatas selected column  fields
     * @param array $output     newly    created array
     */
    private function servicetypeDetails($tabledatas, &$output)
    {
        foreach ($tabledatas as $tabledata) {
            if ($tabledata['type'] == 'SS') {
                $output['aaDataType'][] = array("title" => $tabledata['name'], "type" => "SS");
            }
        }
    }

    public function pdfgenerationAction()
    {

        $bodyNew = $this->container->get('templating')->render("ClubadminSponsorBundle:SponsorList:test.html.twig");
        $bodyNew = html_entity_decode($bodyNew);
        $pdfEngine = $this->get('knp_snappy.pdf');
        $response = new Response();
        $response->setContent(
            $pdfEngine->getOutputFromHtml(
                $bodyNew, array(
                'header-left' => '[title]',
                'header-center' => 'nothing again',
                'header-right' => '[page]',
                'header-line' => true,
                'footer-left' => 'nothing',
                'footer-center' => 'nothing again',
                'footer-right' => '[page]/[topage]',
                )
            )
        );
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/pdf; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="file.pdf"');
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        return $response;
    }

    /**
     * function to get sidebar count
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getSidebarCountAction()
    {
        $sponsorCount = $this->activeSponsorCount();
        $pdo = new SponsorPdo($this->container);
        $sponsorType = $pdo->getSidebarCount($this->clubId, $this->clubType);
        $club = $this->container->get('club');
        $masterTable = $club->get('clubTable');

        $i = 1;
        foreach ($sponsorType[0] as $type => $count) {
            $sponsorCount[$i] = $this->sidebarCountArray('', $type, $type, $count);
            $i++;
        }
        $sponsorPdo = new SponsorPdo($this->container);
        $serviceCount = $sponsorPdo->sponsorServiceCount($this->clubId, $masterTable, array(), $this->clubType);

        foreach ($serviceCount as $key => $type) {
            $sponsorCount[$i] = $this->sidebarCountArray($type['catId'], $type['serviceId'], 'service', $type['cnt']);
            $i++;
        }

        $sponsorPdo = new SponsorPdo($this->container);
        $assignmentCount = $sponsorPdo->assignmentOverviewCount($this->clubId, $this->clubType);
        foreach ($assignmentCount[0] as $key => $value) {
            $sponsorCount[$i] = $this->sidebarCountArray("", $key, 'overview', $value);
            $i++;
        }

        return new JsonResponse($sponsorCount);
    }

    /**
     * Function is used to get count of all Active sponsors
     *
     * @return template
     */
    public function activeSponsorCount()
    {
        //Set all request value to its corresponding variables
        $sponsorlistData = new ContactlistData($this->contactId, $this->container, 'sponsor');
        //For get the contact list array
        $contactData = $sponsorlistData->getContactData();
        //collect total number of records
        $sponsorCount[0] = $this->sidebarCountArray("", "", 'allActive', $contactData['totalcount']);
        return $sponsorCount;
    }

    /**
     * function to get array structure for sidebar count
     * @param int $catId categoryId
     * @param int $serviceId serviceId
     * @param string $type type
     * @param int $count count sidebar
     * @return string
     */
    private function sidebarCountArray($catId, $serviceId, $type, $count)
    {
        $sponsorCount['categoryId'] = $catId;
        $sponsorCount['subCatId'] = $serviceId;
        $sponsorCount['dataType'] = $type;
        $sponsorCount['sidebarCount'] = (($count != null) && ($count >= 0)) ? $count : 0;
        $sponsorCount['action'] = 'show';

        return $sponsorCount;
    }
}
