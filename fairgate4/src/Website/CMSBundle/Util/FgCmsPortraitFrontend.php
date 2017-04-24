<?php
/**
 * FgCmsPortrait 
 */
namespace Website\CMSBundle\Util;

use Website\CMSBundle\Util\FgCmsPortrait;
use Website\CMSBundle\Util\FgTablesettings;

/**
 * FgCmsPortraitFrontend - The wrapper class to handle functionalities on contact portrait element frontend view
 *
 * @package         Website
 * @subpackage      CMS
 * @author          pitsolutions.ch
 * @version         Fairgate V4
 */
class FgCmsPortraitFrontend
{

    /**
     * The constructor function
     *
     * @param object $container container:\Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->club = $this->container->get('club');

        $this->contact = $this->container->get('contact');
        $this->contactId = $this->contact->get('id');

        $this->em = $this->container->get('doctrine')->getManager();
    }

    /**
     * Function to get portrait element data .
     *
     * @param int $elementId element id
     * 
     * @return JsonResponse portrait details array
     */
    public function getPortraitElementDetails($elementId)
    {
        $elementObj = $this->em->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->find($elementId);
        if ($elementObj) {
            $portraitObj = $elementObj->getTable();
            $portraitId = $portraitObj->getId();
        }
        $stage = $this->em->getRepository('CommonUtilityBundle:FgCmsContactTable')->find($portraitId)->getStage();
        $data = $this->em->getRepository('CommonUtilityBundle:FgCmsContactTable')->getPortraitColumnData($portraitId);
        $portraitContainerData = new FgCmsPortrait($this->container);
        $reorderData = $portraitContainerData->formatContainerData($portraitId, $data);
        $reorderData['stage'] = $stage;

        return $reorderData;
    }

    /**
     * To create the column setting data
     *
     * @param object    $tableObj   table Object
     * @param array     $list       list of columns
     *
     */
    private function columnDetailsJson($columnDatas)
    {
        $clubId = $this->container->get('club')->get('id');
        $fedId = $this->container->get('club')->get('federation_id');
        $arr = array('columnData' => '', 'separateListing' => 0);
        //separate listing enable/disable flag
        $separateListing = 0;
        //Set separate listing column name
        $separateListingColumn = '';
        //Set separate listing setting function details
        $separateListingFunc = '';
        foreach ($columnDatas as $key => $value) {
            switch ($value['selectedFieldType']) {
                case 'contact_name':
                    $arr['columnData'][$key] = array('id' => 'contactname', 'type' => 'contactname', 'linkUrl' => $value['selectedField'], 'club_id' => $clubId, 'name' => $value['id'], 'withOutComma' => true);
                    break;
                case 'contact_field':
                    $arr['columnData'][$key] = array('id' => $value['selectedField'], 'type' => 'CF', 'club_id' => $clubId, 'name' => $value['id']);
                    break;
                case 'membership_info':
                    if ($value['columnSubType'] == 'member_years') {
                        $value['columnSubType'] = 'club_' . $value['columnSubType'];
                    }
                    $arr['columnData'][$key] = array('id' => $value['columnSubType'], 'type' => 'CM', 'club_id' => $clubId, 'name' => $value['id']);
                    break;
                case 'fed_membership_info':
                    $arr['columnData'][$key] = array('id' => 'fed_' . $value['columnSubType'], 'type' => 'FM', 'club_id' => $clubId, 'name' => $value['id']);
                    break;
                case 'federation_info':
                    $arr['columnData'][$key] = array('id' => $value['columnSubType'], 'type' => 'FI', 'club_id' => $fedId, 'name' => $value['id'], 'sub_ids' => $value['functionIds']);
                    break;
                case 'analysis_field':
                    $arr['columnData'][$key] = array('id' => $value['columnSubType'], 'type' => 'AF', 'club_id' => $clubId, 'name' => $value['id']);
                    break;
                case 'workgroup_assignments':
                    $arr['columnData'][$key] = array('id' => $value['selectedField'], 'type' => 'WA', 'club_id' => $clubId, 'name' => $value['id'], 'sub_ids' => 'all');
                    $separateListing = ($separateListing == 0) ? $value['separateListing'] : 1;
                    $separateListingColumn = ($separateListingColumn == '' && $separateListing == 1) ? 'WA_' . $value['id'] : $separateListingColumn;
                    break;
                case 'team_assignments':
                    $arr['columnData'][$key] = array('id' => $value['functionIds'], 'type' => 'TA', 'club_id' => $clubId, 'name' => $value['id'], 'sub_ids' => $value['functionIds']);
                    $separateListing = ($separateListing == 0) ? $value['separateListing'] : 1;
                    $separateListingColumn = ($separateListingColumn == '' && $separateListing == 1) ? 'TA_' . $value['id'] : $separateListingColumn;
                    if ($value['functionIds'] != null) {
                        $separateListingFunc = ($separateListingColumn == 'TA_' . $value['id'] && $separateListing == 1) ? $value['functionIds'] : $separateListingFunc;
                    }

                    break;
                case 'role_category_assignments':
                    $arr['columnData'][$key] = array('id' => $value['cat'], 'type' => 'RCA', 'club_id' => $clubId, 'name' => $value['id']);
                    $separateListing = ($separateListing == 0) ? $value['separateListing'] : 1;
                    $separateListingColumn = ($separateListingColumn == '' && $separateListing == 1) ? 'RCA_' . $value['id'] : $separateListingColumn;
                    $separateListingFunc = ($separateListingColumn == 'RCA_' . $value['id'] && $separateListing == 1) ? $value['cat'] : $separateListingFunc;
                    break;
                case 'fed_role_category_assignments':
                    $arr['columnData'][$key] = array('id' => $value['cat'], 'type' => 'FRCA', 'club_id' => $fedId, 'name' => $value['id'], 'is_fed_cat' => 1);
                    $separateListing = ($separateListing == 0) ? $value['separateListing'] : 1;
                    $separateListingColumn = ($separateListingColumn == '' && $separateListing == 1) ? 'FRCA_' . $value['id'] : $separateListingColumn;
                    $separateListingFunc = ($separateListingColumn == 'FRCA_' . $value['id'] && $separateListing == 1) ? $value['cat'] : $separateListingFunc;
                    break;
                case 'sub_fed_role_category_assignments':
                    $arr['columnData'][$key] = array('id' => $value['cat'], 'type' => 'SFRCA', 'club_id' => $fedId, 'name' => $value['id'], 'is_fed_cat' => 1);
                    $separateListing = ($separateListing == 0) ? $value['separateListing'] : 1;
                    $separateListingColumn = ($separateListingColumn == '' && $separateListing == 1) ? 'SFRCA_' . $value['id'] : $separateListingColumn;
                    $separateListingFunc = ($separateListingColumn == 'SFRCA_' . $value['id'] && $separateListing == 1) ? $value['cat'] : $separateListingFunc;
                    break;
                case 'common_role_functions':
                    $arr['columnData'][$key] = array('id' => $value['cat'], 'type' => 'CRF', 'club_id' => $clubId, 'name' => $value['id']);
                    $separateListing = ($separateListing == 0) ? $value['separateListing'] : 1;
                    $separateListingColumn = ($separateListingColumn == '' && $separateListing == 1) ? 'CRF_' . $value['id'] : $separateListingColumn;
                    $separateListingFunc = ($separateListingColumn == 'CRF_' . $value['id'] && $separateListing == 1) ? $value['cat'] : $separateListingFunc;
                    break;
                case 'filter_role_assignments':
                    $arr['columnData'][$key] = array('id' => $value['selectedField'], 'type' => 'FRA', 'club_id' => $clubId, 'name' => $value['id']);
                    $separateListing = ($separateListing == 0) ? $value['separateListing'] : 1;
                    $separateListingColumn = ($separateListingColumn == '' && $separateListing == 1) ? 'FRA_' . $value['id'] : $separateListingColumn;
                    break;
                case 'team_functions':
                    $arr['columnData'][$key] = array('id' => $value['selectedField'], 'type' => 'TF', 'club_id' => $clubId, 'name' => $value['id']);
                    $separateListing = ($separateListing == 0) ? $value['separateListing'] : 1;
                    $separateListingColumn = ($separateListingColumn == '' && $separateListing == 1) ? 'TF_' . $value['id'] : $separateListingColumn;
                    break;
                case 'workgroup_functions':
                    $arr['columnData'][$key] = array('id' => $value['role'], 'type' => 'WF', 'club_id' => $clubId, 'name' => $value['id']);
                    $separateListing = ($separateListing == 0) ? $value['separateListing'] : 1;
                    $separateListingColumn = ($separateListingColumn == '' && $separateListing == 1) ? 'WF_' . $value['id'] : $separateListingColumn;
                    $separateListingFunc = ($separateListingColumn == 'WF_' . $value['id'] && $separateListing == 1) ? $value['role'] : $separateListingFunc;
                    break;
                case 'common_fed_role_functions':
                    $arr['columnData'][$key] = array('id' => $value['cat'], 'type' => 'CFRF', 'club_id' => $fedId, 'name' => $value['id'], 'is_fed_cat' => 1);
                    $separateListing = ($separateListing == 0) ? $value['separateListing'] : 1;
                    $separateListingColumn = ($separateListingColumn == '' && $separateListing == 1) ? 'CFRF_' . $value['id'] : $separateListingColumn;
                    $separateListingFunc = ($separateListingColumn == 'CFRF_' . $value['id'] && $separateListing == 1) ? $value['cat'] : $separateListingFunc;
                    break;
                case 'individual_role_functions':
                    $arr['columnData'][$key] = array('id' => $value['role'], 'type' => 'IRF', 'club_id' => $clubId, 'name' => $value['id']);
                    $separateListing = ($separateListing == 0) ? $value['separateListing'] : 1;
                    $separateListingColumn = ($separateListingColumn == '' && $separateListing == 1) ? 'IRF_' . $value['id'] : $separateListingColumn;
                    $separateListingFunc = ($separateListingColumn == 'IRF_' . $value['id'] && $separateListing == 1) ? $value['role'] : $separateListingFunc;
                    break;
                case 'individual_fed_role_functions':
                    $arr['columnData'][$key] = array('id' => $value['role'], 'type' => 'IFRF', 'club_id' => $fedId, 'name' => $value['id'], 'is_fed_cat' => 1);
                    $separateListing = ($separateListing == 0) ? $value['separateListing'] : 1;
                    $separateListingColumn = ($separateListingColumn == '' && $separateListing == 1) ? 'IFRF_' . $value['id'] : $separateListingColumn;
                    $separateListingFunc = ($separateListingColumn == 'IFRF_' . $value['id'] && $separateListing == 1) ? $value['role'] : $separateListingFunc;
                    break;
                case 'individual_sub_fed_role_functions':
                    $arr['columnData'][$key] = array('id' => $value['role'], 'type' => 'ISFRF', 'club_id' => $fedId, 'name' => $value['id'], 'is_fed_cat' => 1);
                    $separateListing = ($separateListing == 0) ? $value['separateListing'] : 1;
                    $separateListingColumn = ($separateListingColumn == '' && $separateListing == 1) ? 'ISFRF_' . $value['id'] : $separateListingColumn;
                    $separateListingFunc = ($separateListingColumn == 'ISFRF_' . $value['id'] && $separateListing == 1) ? $value['role'] : $separateListingFunc;
                    break;
                case 'common_sub_fed_role_functions':
                    $arr['columnData'][$key] = array('id' => $value['cat'], 'type' => 'CSFRF', 'club_id' => $fedId, 'name' => $value['id'], 'is_fed_cat' => 1);
                    $separateListing = ($separateListing == 0) ? $value['separateListing'] : 1;
                    $separateListingColumn = ($separateListingColumn == '' && $separateListing == 1) ? 'CSFRF_' . $value['id'] : $separateListingColumn;
                    $separateListingFunc = ($separateListingColumn == 'CSFRF_' . $value['id'] && $separateListing == 1) ? $value['cat'] : $separateListingFunc;
                    break;
                case 'profile_pic':
                    $arr['columnData'][$key] = array('id' => 'profilepic', 'type' => 'PROFILE_PIC', 'club_id' => $clubId, 'name' => $value['id'], 'linkUrl' => $value['selectedField']);
                    break;
            }
        }

        $arr['separateListing'] = $separateListing;
        $arr['separateListingColumn'] = $separateListingColumn;
        $arr['separateListingFunc'] = $separateListingFunc;
        return $arr;
    }

    /**
     * 
     * @param integer $elementId id of the element
     * @return array
     */
    public function getPortraitElementInitailData($elementId)
    {
        $elementObj = $this->em->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->find($elementId);
        if ($elementObj) {
            $portraitObj = $elementObj->getTable();
            $portraitId = $portraitObj->getId();
        }
        $data = $this->em->getRepository('CommonUtilityBundle:FgCmsContactTable')->getPortraitColumnData($portraitId);
        $iteratedData = $this->iterateColumnData($data);
        $columnDetailsArray = $this->columnDetailsJson($iteratedData["columns"]["data"]);
        $columnDetails = json_encode($columnDetailsArray['columnData']);
        $sortColumnDetails = $this->getSortingColumn($portraitObj->getInitialSortingDetails(), $columnDetailsArray); 
        $stage = $portraitObj->getStage();
        $sortColumnValue = '';
        if ($sortColumnDetails != '') {
            $sortColumnValue = $sortColumnDetails . " " . $portraitObj->getInitialSortOrder(); //
        }
        $filterCriteria = $this->em->getRepository('CommonUtilityBundle:FgFilter')->find($data[0]['filterId'])->getFilterData();
        $filterType = ($data[0]['filterType'] == 'sponsor' ) ? 'sponsor' : 'contact';

        return array('column' => $columnDetails, 'displayLength' => $iteratedData["columns"]["displayLength"], 'includeIds' => $portraitObj->getIncludeContacts(), 'excludedIds' => $portraitObj->getExcludeContacts(), 'sort' => $sortColumnValue, 'filterCriteria' => $filterCriteria, 'stage' => $stage, 'filterType' => $filterType, 'separateListing' => $columnDetailsArray['separateListing'], 'separateListingColumn' => $columnDetailsArray['separateListingColumn'], 'separateListingFunc' => $columnDetailsArray['separateListingFunc']);
    }

    /**
     * To create column data from the portrait elemnt details
     * @param type $containerDetails
     * @return type
     */
    private function iterateColumnData($containerDetails)
    {
        $previousDataId = 0;
        $newPortraitContainerArray = array();
        if (count($containerDetails) > 0) {
            $newPortraitContainerArray = array("columns" => array('id' => 1));
            //sidebar details

            $newPortraitContainerArray['columns']['displayLength'] = ($containerDetails[0]['portraitPerRow'] * $containerDetails[0]['rowPerpage']);


            foreach ($containerDetails as $detailsValue) {

                //assign data details
                if ($detailsValue['dataId'] != '') {
                    $newPortraitContainerArray['columns']['data'][$detailsValue['dataId']]['id'] = $detailsValue['dataId'];
                    $newPortraitContainerArray['columns']['data'][$detailsValue['dataId']]['selectedField'] = $detailsValue['attributeId'];
                    $newPortraitContainerArray['columns']['data'][$detailsValue['dataId']]['selectedFieldType'] = $detailsValue['columnType'];
                    $newPortraitContainerArray['columns']['data'][$detailsValue['dataId']]['fieldType'] = "{$detailsValue['fieldType']}";
                    $newPortraitContainerArray['columns']['data'][$detailsValue['dataId']]['role'] = $detailsValue['role'];
                    $newPortraitContainerArray['columns']['data'][$detailsValue['dataId']]['cat'] = $detailsValue['roleCategory'];
                    $newPortraitContainerArray['columns']['data'][$detailsValue['dataId']]['columnSubType'] = $detailsValue['columnSubType'];
                    $newPortraitContainerArray['columns']['data'][$detailsValue['dataId']]['separateListing'] = $detailsValue['separateListing'];
                    if ($detailsValue['functionIds'] != '') {
                        $newPortraitContainerArray['columns']['data'][$detailsValue['dataId']]['functionIds'] = $detailsValue['functionIds'];
                    }
                    $newPortraitContainerArray['columns']['data'][$detailsValue['dataId']]['fieldDisplayType'] = $detailsValue['fieldDisplayType'];
                }
                $previousDataId = $detailsValue['dataId'];
            }
        }

        return $newPortraitContainerArray;
    }

    /**
     * To generate actual sorting column details from the saved data
     * @param String $columnDetails
     */
    public function getSortingColumn($columnDetails, $columnDetailsArray)
    {
        $club = $this->container->get('club');
        $clubId = $club->get('id');
        $fedId = $club->get('federation_id');
        $details = json_decode($columnDetails, true);
        $details['type'] = strtolower($details['type']);
        $key = 0;   
        $defaultSorting = true;  // false if sorting with workgroup assignments/team assignments/workgroup functions/team functions
        switch ($details['type']) {
            case 'contact_name':
                $arr[$key] = array('id' => 'contactname', 'type' => 'contactname', 'club_id' => $clubId, 'name' => $details['type'], 'withOutComma' => true);
                break;
            case 'contact_field':
                $arr[$key] = array('id' => $details['name'], 'type' => 'CF', 'club_id' => $clubId, 'name' => $details['type']);
                break;
            case 'membership_info':
                if ($details['name'] == 'member_years') {
                    $details['columnSubType'] = 'club_' . $details['name'];
                    $details['name'] = 'club_member_years';
                }
                $arr[$key] = array('id' => $details['name'], 'type' => 'CM', 'club_id' => $clubId, 'name' => $details['type']);
                break;
            case 'fed_membership_info':
                $arr[$key] = array('id' => 'fed_' . $details['name'], 'type' => 'FM', 'club_id' => $clubId, 'name' => $details['type']);
                break;
            case 'federation_info':
                $arr[$key] = array('id' => $details['name'], 'type' => 'FI', 'club_id' => $fedId, 'name' => $details['type']);
                break;
            case 'analysis_field':
                $arr[$key] = array('id' => $details['name'], 'type' => 'AF', 'club_id' => $clubId, 'name' => $details['type']);
                break;
            case 'workgroup_assignments':
                $arr[$key] = array('id' => $details['name'], 'type' => 'WA', 'club_id' => $clubId, 'name' => $details['type'], 'sub_ids' => 'all');
                $defaultSorting = false;
                break;
            case 'team_assignments':
                $arr[$key] = array('id' => '', 'type' => 'TA', 'club_id' => $clubId, 'name' => $details['type'], 'sub_ids' => '');
                $defaultSorting = false;
                break;
            case 'role_category_assignments':                 
                $arr[$key] = array('id' => $details['name'], 'type' => 'RCA', 'club_id' => $clubId, 'name' => $details['type']);
                $defaultSorting = false;
                break;
            case 'fed_role_category_assignments':                
                $arr[$key] = array('id' => $details['name'], 'type' => 'FRCA', 'club_id' => $fedId, 'name' => $details['type'], 'is_fed_cat' => 1);
                $defaultSorting = false;
                break;
            case 'sub_fed_role_category_assignments':                
                $arr[$key] = array('id' => $details['name'], 'type' => 'SFRCA', 'club_id' => $fedId, 'name' => $details['type'], 'is_fed_cat' => 1);
                $defaultSorting = false;
                break;
            case 'common_role_functions':                
                $arr[$key] = array('id' => $details['name'], 'type' => 'CRF', 'club_id' => $clubId, 'name' => $details['type']);
                $defaultSorting = false;
                break;
            case 'filter_role_assignments':
                $arr[$key] = array('id' => $details['name'], 'type' => 'FRA', 'club_id' => $clubId, 'name' => $details['type']);
                $defaultSorting = false;
                break;
            case 'team_functions':
                $arr[$key] = array('id' => $details['name'], 'type' => 'TF', 'club_id' => $clubId, 'name' => $details['type']);
                $defaultSorting = false;
                break;
            case 'workgroup_functions':
                $arr[$key] = array('id' => $details['name'], 'type' => 'WF', 'club_id' => $clubId, 'name' => $details['type']);
                $defaultSorting = false;
                break;
            case 'common_fed_role_functions':
                $arr[$key] = array('id' => $details['name'], 'type' => 'CFRF', 'club_id' => $fedId, 'name' => $details['type'], 'is_fed_cat' => 1);
                $defaultSorting = false;
                break;
            case 'individual_role_functions':                
                $arr[$key] = array('id' => $details['name'], 'type' => 'IRF', 'club_id' => $clubId, 'name' => $details['type']);
                $defaultSorting = false;
                break;
            case 'individual_fed_role_functions':
                $arr[$key] = array('id' => $details['name'], 'type' => 'IFRF', 'club_id' => $fedId, 'name' => $details['type'], 'is_fed_cat' => 1);
                $defaultSorting = false;
                break;
            case 'individual_sub_fed_role_functions':
                $arr[$key] = array('id' => $details['name'], 'type' => 'ISFRF', 'club_id' => $fedId, 'name' => $details['type'], 'is_fed_cat' => 1);
                $defaultSorting = false;
                break;
            case 'common_sub_fed_role_functions':
                $arr[$key] = array('id' => $details['name'], 'type' => 'CSFRF', 'club_id' => $fedId, 'name' => $details['type'], 'is_fed_cat' => 1);
                $defaultSorting = false;
                break;
        }
        //Check separatelisting is enable or not     
        if (count($arr) > 0 && $columnDetailsArray['separateListingColumn'] != '') {
            list($separateColumn, $columnId) = explode('_', $columnDetailsArray['separateListingColumn']);
            $arr[0]['name'] = $columnId;
        }

        $table = new FgTablesettings($this->container, $arr, $club);
        $table->dependColumns = $this->getDependColumnsDetails($columnDetailsArray['separateListingColumn']);
        $table->separateListColumn = $columnDetailsArray['separateListingColumn'];
        $table->separateListing = ($columnDetailsArray['separateListing'] == 1) ? true : false;        
        
        $aColumns = $table->getColumns();
        //remove AS part area 
        
        if($defaultSorting == false) {             
            $splitArray = ($columnDetailsArray['separateListing']) ? explode(" AS `" . $arr[0]['name'] . "_sortorder`", $aColumns[1]) : explode(" AS `" . $details['type'] . "_sortorder`", $aColumns[1]);        
        } else {
            $splitArray = ($columnDetailsArray['separateListing']) ? explode(" AS `" . $arr[0]['name'] . "`", $aColumns[0]) : explode(" AS `" . $details['type'] . "`", $aColumns[0]);        
        }
        $sortingColumn = $splitArray[0];
        
        $sortColumnValue = ' (CASE WHEN ' . $sortingColumn . ' IS NULL then 3 WHEN ' . $sortingColumn . "='' then 2 WHEN " . $sortingColumn . "='0000-00-00 00:00:00' then 1 ELSE 0 END)," . $sortingColumn ;
            
        return $sortColumnValue;
    }

    /**
     * To find depended columns
     * @param string $separateListColumn
     * @return array depend columns
     */
    public function getDependColumnsDetails($separateListColumn)
    {
        list($separateColumn) = explode('_', $separateListColumn);
        $dependArray = array();
        $dependArray["TA"] = array('TF');
        $dependArray["TF"] = array('TA');
        $dependArray["WA"] = array('WF');
        // $dependArray["WF"] = array('WA');
        $dependArray["RCA"] = array('CRF', 'IRF', 'ISFRF', 'IFRF', 'CSFRF', 'CFRF');
        $dependArray["FRCA"] = array('CRF', 'IRF', 'ISFRF', 'IFRF', 'CSFRF', 'CFRF');
        $dependArray["SFRCA"] = array('CRF', 'IRF', 'ISFRF', 'IFRF', 'CSFRF', 'CFRF');

        $dependArray["CRF"] = array("RCA");

        return ($separateListColumn != '') ? $dependArray[$separateColumn] : array();
    }
}
