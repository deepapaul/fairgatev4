<?php
/**
 * ContactTableController.
 */
namespace Website\CMSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Clubadmin\Classes\Contactdatatable;
use Clubadmin\ContactBundle\Util\ContactlistData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Intl\Intl;
use Website\CMSBundle\Controller\ContactsTableElementController;
use Website\CMSBundle\Util\FgContactFilterSettings;
/**
 * ContactTableController.
 *
 * This controller is used for handle contact table  *
 * @package 	Website
 * @subpackage 	CMSBundle
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 *
 */
class ContactTableController extends Controller
{

    /**
     * Execute all the contact related to the particular club/federation action.
     *
     * @param object $request     The request object
     * @param type   $contactType contacttype
     *
     * @return json
     */
    public function listcontactAction(Request $request, $contactType)
    {
        //Set all request value to its corresponding variables
        $contactlistData = new ContactlistData($this->contactId, $this->container, $contactType, 'website');
        $contactlistData->filterValue = $request->get('filterdata', '');
        //check if the request is valid or not
        if ($contactlistData->filterValue != '' && $contactlistData->filterValue == '0') {
            $output = array('iTotalRecords' => 0, 'iTotalDisplayRecords' => 0, 'aaData' => array());

            return new JsonResponse($output);
        }
        $contactlistData->filterValue = json_decode($contactlistData->filterValue, true);
        $contactlistData->functionTypeValue = $request->get('functionType', 'none');
        $contactlistData->dataTableColumnData = $request->get('columns', '');
        $contactlistData->sortColumnValue = $request->get('order', '');
        $contactlistData->searchval = $request->get('search', '');
        $contactlistData->tableFieldValues = $request->get('tableField', '');
        $contactlistData->startValue = $request->get('start', '');
        $contactlistData->roleFilter = $request->get('filterrole', '');
        $contactlistData->displayLength = $request->get('length', '');
        $contactlistData->includedIds = $request->get('includedIds', '');
        $contactlistData->excludedIds = $request->get('excludedIds', '');
        $contactlistData->specialFilter = $request->get('specialFilter', '');
        $contactlistData->groupByColumn = 'fg_cm_contact.id';
        //For get the contact list array
        $contactData = $contactlistData->getContactData();
        //collect total number of records
        $totalrecords = $contactData['totalcount'];
        //For set the datatable json array
        $output = array('iTotalRecords' => $totalrecords, 'iTotalDisplayRecords' => $totalrecords, 'aaData' => array());
        //iterate the result
        $contactDatatabledata = new Contactdatatable($this->container);

        $output['aaData'] = $contactDatatabledata->iterateDataTableData($contactData['data'], $this->container->getParameter('country_fields'), $contactlistData->tabledata, 'website');
        $output['elementType'] = 'contacts-table';

        return new JsonResponse($output);
    }

    /**
     * get initial data for contact table element
     *
     * @param integer $elementId id of element
     * 
     * @return JsonResponse $returnData Data for contact table element
     */
    public function getTableInitialDataAction($elementId)
    {
        $em = $this->getDoctrine()->getManager();
        $club = $this->container->get('club');
        $returnData = array();
        $elementObj = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->find($elementId);
        if ($elementObj != '') {
            $tableObj = $elementObj->getTable();
            if (/* $tableObj->getIsActive() != '1' || */ $tableObj->getIsDeleted() == '1') {
                return new JsonResponse(0);
            }
            $tableId = $tableObj->getId();
            $tableInitialData = $em->getRepository('CommonUtilityBundle:FgCmsContactTable')->getTableSettingData($tableId);
            $tableColumnDatas = json_decode($tableInitialData['columnData'], true);
            //optimize contactfields/role functions in column settings
            $finalColumnData = $this->iterateColumnSettingData($tableColumnDatas);
            $contactId = $this->container->get('session')->get("loggedClubUserId", 0);
            $filterData = $this->getFilterData($tableId);
            $clubDetails = array('clubId' => $club->get('id'), 'federationId' => $club->get('federation_id'), 'subFederationId' => $club->get('sub_federation_id'));
            if (count($tableInitialData) > 0) {
                $tableInitialData['contactId'] = $contactId;
                $clubDetails['countryList'] = Intl::getRegionBundle()->getCountryNames($club->get('club_default_lang'));
                $clubDetails['langList'] = Intl::getLanguageBundle()->getLanguageNames($club->get('club_default_lang'));
                $clubDetails['countryFields'] = $this->container->getParameter('country_fields');
                $clubDetails['corresLangField'] = $this->container->getParameter('system_field_corress_lang');
                $returnData = array('columnData' => $finalColumnData, 'tableInitialData' => $tableInitialData, 'filterData' => $filterData, 'clubDetails' => $clubDetails, 'elementType' => 'contacts-table', 'contactId' => $contactId);
            } else {
                $returnData = array();
            }
        }

        return new JsonResponse($returnData);
    }

    /**
     * This function is used to format the column settings data
     * 
     * @param array $tableColumnDatas table column settings
     * 
     * @return array $finalColumnData Formatted result set
     */
    private function iterateColumnSettingData($tableColumnDatas)
    {
        $club = $this->container->get('club');
        $clubLanguage = $club->get('club_default_lang');
        $getAllContactFields = $club->get('allContactFields');
        $activeContactFields = array_keys($getAllContactFields);
        $finalColumnData = array();
        //get all active fields
        $contactTableElementObj = new ContactsTableElementController;
        $contactTableElementObj->setContainer($this->container);
        $result2 = $contactTableElementObj->getContactTableColumnOptions();

        foreach ($tableColumnDatas as $columns) {
            if (count($columns['title']) >= 1) {
                $columns['title'] = $columns['title'][$clubLanguage];
            }
            if (in_array($columns['id'], $activeContactFields) == true && $columns['type'] == 'CF' && $getAllContactFields[$columns['id']]['is_visible_contact'] == 1) {
                $columns['is_set_privacy_itself'] = $getAllContactFields[$columns['id']]['is_set_privacy_itself'];
                $columns['itemType'] = $getAllContactFields[$columns['id']]['type'];
                $columns['club_id'] = $getAllContactFields[$columns['id']]['club_id'];
                $finalColumnData[] = $columns;
            } elseif ($columns['type'] != 'CF') {
                switch ($columns['type']) {
                    case "TA"://Team assignments
                        $finalColumnData[] = $columns;
                        break;
                    case "TF"://Team function
                        $finalColumnData[] = $columns;
                        break;
                    case "WA"://work group assignment
                        $finalColumnData[] = $columns;
                        break;
                    case "WF"://work group function
                        if (isset($result2["WORKGROUP_FUNCTIONS"]['fieldValue'])) {
                            //collect the array of keys
                            $activeWorkfunctionKeys = array_keys($result2["WORKGROUP_FUNCTIONS"]['fieldValue']);
                            if (in_array('f-' . $columns['id'], $activeWorkfunctionKeys)) {
                                //set the column details
                                $finalColumnData[] = $columns;
                            }
                        }

                        break;
                    case "RCA"://Role category assignments
                        if (isset($result2["ROLE_CATEGORY_ASSIGNMENTS"]['fieldValue'])) {
                            //collect the array of keys
                            $activeRolefunctionKeys = array_keys($result2["ROLE_CATEGORY_ASSIGNMENTS"]['fieldValue']);
                            if (in_array('r-' . $columns['id'], $activeRolefunctionKeys)) {
                                //set the column details
                                $finalColumnData[] = $columns;
                            }
                        }
                        break;
                    case "CRF"://common role function
                        if (isset($result2["COMMON_ROLE_FUNCTIONS"]['fieldValue'])) {
                            //collect the array of keys
                            $activeRolefunctionKeys = array_keys($result2["COMMON_ROLE_FUNCTIONS"]['fieldValue']);
                            if (in_array('r-' . $columns['id'], $activeRolefunctionKeys)) {
                                //set the column details
                                $finalColumnData[] = $columns;
                            }
                        }
                        break;
                    case "IRF"://Individual role functions
                        if (isset($result2["INDIVIDUAL_ROLE_FUNCTIONS"]['fieldValue'])) {
                            //collect the array of keys
                            $activeRolefunctionKeys = array_keys($result2["INDIVIDUAL_ROLE_FUNCTIONS"]['fieldValue']);
                            if (in_array('r-' . $columns['id'], $activeRolefunctionKeys)) {
                                //set the column details
                                $finalColumnData[] = $columns;
                            }
                        }
                        break;
                    case "FRA"://Filter role assignments
                        $finalColumnData[] = $columns;
                        break;
                    case "FRCA":
                        if (isset($result2["FED_ROLE_CATEGORY_ASSIGNMENTS"]['fieldValue'])) {
                            //collect the array of keys
                            $activeRolefunctionKeys = array_keys($result2["FED_ROLE_CATEGORY_ASSIGNMENTS"]['fieldValue']);
                            if (in_array('r-' . $columns['id'], $activeRolefunctionKeys)) {
                                //set the column details
                                $finalColumnData[] = $columns;
                            }
                        }
                        break;
                    case "CFRF":
                        if (isset($result2["COMMON_FED_ROLE_FUNCTIONS"]['fieldValue'])) {
                            //collect the array of keys
                            $activeRolefunctionKeys = array_keys($result2["COMMON_FED_ROLE_FUNCTIONS"]['fieldValue']);
                            if (in_array('r-' . $columns['id'], $activeRolefunctionKeys)) {
                                //set the column details
                                $finalColumnData[] = $columns;
                            }
                        }
                        break;
                    case "IFRF":
                        if (isset($result2["INDIVIDUAL_FED_ROLE_FUNCTIONS"]['fieldValue'])) {
                            //collect the array of keys
                            $activeRolefunctionKeys = array_keys($result2["INDIVIDUAL_FED_ROLE_FUNCTIONS"]['fieldValue']);
                            if (in_array('r-' . $columns['id'], $activeRolefunctionKeys)) {
                                //set the column details
                                $finalColumnData[] = $columns;
                            }
                        }
                        break;
                    case "SFRCA":
                        if (isset($result2["SUB_FED_ROLE_CATEGORY_ASSIGNMENTS"]['fieldValue'])) {
                            //collect the array of keys
                            $activeRolefunctionKeys = array_keys($result2["SUB_FED_ROLE_CATEGORY_ASSIGNMENTS"]['fieldValue']);
                            if (in_array('r-' . $columns['id'], $activeRolefunctionKeys)) {
                                //set the column details
                                $finalColumnData[] = $columns;
                            }
                        }
                        break;

                    case "CM":
                        if ($columns['id'] == 'club_member_years') {
                            $columns['itemType'] = 'number';
                        }
                        $finalColumnData[] = $columns;
                        break;
                    case "FM":
                        if ($columns['id'] == 'fed_member_years') {
                            $columns['itemType'] = 'number';
                        }
                        $finalColumnData[] = $columns;
                        break;

                    default:
                        if ($columns['type'] == 'contactname') {
                            $columns['itemType'] = $columns['type'];
                        }
                        $finalColumnData[] = $columns;
                        break;
                }
            }
        }

        return $finalColumnData;
    }

    /**
     * This method is used to fetch filter data for contact table
     *
     * @param int $tableId Contact table id
     *
     * @return array $filterData
     */
    public function getFilterData($tableId)
    {
        $fgContactFilterSettings = new FgContactFilterSettings($this->container);
        $filterSettings = $fgContactFilterSettings->getFilterData($tableId);

        return $filterSettings;
    }
}
