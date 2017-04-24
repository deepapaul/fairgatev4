<?php

namespace Internal\TeamBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Internal\TeamBundle\Util\MemberlistData;
use Symfony\Component\Intl\Intl;
use Common\UtilityBundle\Util\FgSettings;
use Symfony\Component\HttpFoundation\Request;
use Common\UtilityBundle\Util\FgUtility;

/**
 * ExportController.
 *
 * To handle the export functionality of the team member data
 *
 * @author     pitsolutions.ch
 *
 * @version    Fairgate V4
 */

/**
 * ExportController.
 */
class ExportController extends Controller
{

    /**
     * Function that is used to do export functionality.
     *
     * @param Request $request Request object
     *
     * @return Response
     */
    public function exportAction(Request $request)
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '2000M');
        if ($request->getMethod() == 'POST') {
            /* Form values */
            $csvType = $request->request->get('CSVtype');
            //Set all request value to its corresponding variables
            $memberListData = new MemberlistData($this->container, $this->contactId);
            $columnsData = array_slice(json_decode($request->get('columns', ''), true), 1);
            array_unshift($columnsData, array('sTitle' => $this->get('translator')->trans('CONTACT'), 'data' => 'contactname'));
            array_unshift($columnsData, array('sTitle' => '', 'data' => ''));

            $orderSettings = json_decode($request->get('order', ''));
            if (!empty($orderSettings)) {
                $memberListData->sortColumnValue[0]['column'] = $orderSettings[0][0];
                $memberListData->sortColumnValue[0]['dir'] = $orderSettings[0][1];
            }

            /*             * ** Remove the fields from the local storage which i don't have visiblity *** */
            $memberId = $request->get('memberId', '');
            $teamRight = $this->container->get('contact')->checkClubRoleRights($memberId, false);
            $userRights = array('ROLE_GROUP_ADMIN', 'ROLE_CONTACT_ADMIN', 'ROLE_FORUM_ADMIN', 'ROLE_DOCUMENT_ADMIN');
            $userrightsIntersect = array_intersect($userRights, $teamRight);
            $adminFlag = 0;
            if (count($userrightsIntersect) > 0 || $this->contactId == 1) {
                $adminFlag = 1;
            }
            $getAllContactFields = $this->container->get('club')->get('allContactFields');
            $hiddenFields = array();
            foreach ($columnsData as $key => $columns) {
                if (false !== strstr($columns['data'], 'CF_')) {
                    $pop = true;
                    $fieldDetails = explode('_', $columns['data']);
                    $fieldsVisibilityDetail = $getAllContactFields[$fieldDetails[1]];
                    //print_r($fieldsVisibilityDetail);
                    if ($fieldsVisibilityDetail['is_set_privacy_itself'] == 1) {
                        $pop = false;
                    } elseif ($fieldsVisibilityDetail['is_visible_teamadmin'] == 1 && $adminFlag) {
                        $pop = false;
                    } elseif ($fieldsVisibilityDetail['privacy_contact'] != 'private' && (in_array('MEMBER', $teamRight))) {
                        $pop = false;
                    }

                    if ($pop) {
                        unset($columnsData[$key]);  //Will remove the un-privelages column form the data array
                        $hiddenFields[] = $fieldDetails[1]; //Need to sent the columns that needed to be hidden from the column list

                        if ($memberListData->sortColumnValue[0]['column'] == $key) {
                            //Remove the hidden column from the sort column, if exixts
                            $memberListData->sortColumnValue[0]['column'] = 1;
                        }//Set with contact name
                    }
                }
            }
            /*             * *************************************************************************** */
            $memberListData->dataTableColumnData = $columnsData;
            $checkedIds = $request->get('checkedIds', '');

            $memberListData->searchval['value'] = $request->get('search', '');
            $memberListData->tableFieldValues = $request->get('tableField', '');
            $memberCategory = $request->get('memberType', '');
            $memberListData->memberId = $request->get('memberId', '');
            $memberListData->memberCategoryId = ($memberCategory == 'team') ? $this->container->get('club')->get('club_team_id') : $this->container->get('club')->get('club_workgroup_id');
            $memberListData->memberlistType = $memberCategory;
            $memberListData->adminFlag = $this->isAdmin($memberListData->memberId);
            if ($checkedIds) {
                $memberListData->extraCond = "fg_cm_contact.id IN ($checkedIds)";
            }

            //For get the member list array
            $memberData = $memberListData->getMemberlistData();
            $columnTitles = array_map(function ($a) {
                return html_entity_decode(strip_tags($a['sTitle']));
            }, $memberListData->dataTableColumnData);
            array_shift($columnTitles);
            $output['aaData'] = $this->iterateDataTableData($memberData['data'], $memberListData->tabledata, $memberCategory, $memberListData->tableFieldValues, $memberListData->adminFlag, $hiddenFields);
            $delimiter = ($csvType == 'commaSep') ? ',' : ';';
            $response = $this->createCsvfile($output['aaData'], $columnTitles, $delimiter, $memberCategory);

            return $response;
        }
    }

    /**
     * Export settings template.
     *
     * @param Request $request Request object
     *
     * @return HTML
     */
    public function exportSettingsAction(Request $request)
    {
        $contactType = '';
        $roleId = $request->get('roleId', '');
        $this->connect = $this->container->get('database_connection');
        $teamName = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgRmCategoryRoleFunction')->getTeamName($this->connect, $roleId);
        $type = $request->get('roleType');
        $count = $request->get('checkedCount');
        if ($count == 0) {
            $count = strtolower($this->get('translator')->trans('ALL'));
        }

        $commonSettings = $this->exportCommonSettings($contactType);

        return $this->container->get('templating')->renderResponse('InternalTeamBundle:TeamOverview:exportSettings.html.twig', array('columnSettings' => $commonSettings['columnSettings'], 'teamName' => $teamName, 'count' => $count, 'type' => $type));
    }

    /**
     * To create csv file.
     *
     * @param array  $finalResultArray selected  club      data
     * @param array  $columnNames      selected  columns
     * @param String $delimiter        delimiter
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function createCsvfile($finalResultArray, $columnNames, $delimiter, $memberCategory)
    {
        /* Creating the file name */
        $filename = $memberCategory . ' members' . '_' . date('Y-m-d') . '_' . date('H-i-s') . '.csv';
        $response = new Response();
        // prints the HTTP headers followed by the content
        $response->setContent(utf8_decode($this->generateCsv($finalResultArray, $columnNames, $delimiter)));
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->headers->set('Content-Transfer-Encoding', 'binary');

        return $response;
    }

    /**
     * Function that is used to generate csv data.
     *
     * @param Array   $dataArray   Data array
     * @param Strings $columnNames Column names
     * @param Int     $delimiter   Delimiter
     *
     * @return String
     */
    private function generateCsv($dataArray, $columnNames, $delimiter)
    {
        $delimiter = '"' . $delimiter . '"';
        $exportColumnArray = $columnNames;
        $content = '"' . implode($delimiter, str_replace('"', '', $exportColumnArray)) . '"';
        $content .= "\n";
        foreach ($dataArray as $value) {
            $content .= '"' . implode($delimiter, str_replace('"', '', $value)) . '"' . "\n";
        }

        return $content;
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
        $commonSettingsArray['columnSettings'] = (($contactType == 'sponsor') || ($contactType == 'archivedsponsor')) ? $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgTableSettings')->getAllTableSettings($this->clubId, $this->contactId, $columnsettingType) : $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgTableSettings')->getAllTableSettings($this->clubId, $this->contactId, 'DATA');
        $commonSettingsArray['backLink'] = ($contactType == 'sponsor') ? $this->generateUrl('clubadmin_sponsor_homepage') : (($contactType == 'archivedsponsor') ? $this->generateUrl('view_archived_sponsors') : $this->generateUrl('contact_index'));

        return $commonSettingsArray;
    }

    /**
     * For iterate the member list data.
     *
     * @param array  $memberlistDatas result data from the base query
     * @param array  $tabledatas      table column details
     * @param string $memberCategory  selected type (team/workgroup)
     *
     * @return type
     */
    public function iterateDataTableData($memberlistDatas, $tabledatas, $memberCategory, $tableFieldValues, $isAdmin, $hiddenFields)
    {
        $output['aaData'] = array();
        $tableFieldValuesArray = json_decode($tableFieldValues, true);
        foreach ($tableFieldValuesArray as $key => $tableFieldValues) {
            if (in_array($tableFieldValues['id'], $hiddenFields)) {
                unset($tableFieldValuesArray[$key]);
            }
        }

        foreach ($memberlistDatas as $memberlistData) {
            $iterateData = array();
            $iterateData[] = $memberlistData['contactname'];
            $iterateData[] = $this->getRollFunctionNames($memberlistData, $isAdmin);
            if (!empty($tableFieldValuesArray)) {
                $iterateData = array_merge($iterateData, $this->iterateContactData($tableFieldValuesArray, $memberlistData, $isAdmin));
            }
            $output['aaData'][] = $iterateData;
        }

        return $output['aaData'];
    }

    /**
     * Function to use set the fields acccording to the type.
     *
     * @param Array $tabledatas     column names
     * @param Array $memberlistData member result array
     */
    private function fieldType($tabledatas, &$memberlistData)
    {
        $club = $this->get('club');
        $allContactFiledsData = $club->get('allContactFields');
        if (is_array($tabledatas) && count($tabledatas) > 0) {
            foreach ($tabledatas as $contactFields) {
                if (array_key_exists($contactFields['id'], $allContactFiledsData)) {
                    switch ($allContactFiledsData[$contactFields['id']]['type']) {
                        case 'date':
                            if ($memberlistData['CF_' . $contactFields['id']] == '' || $memberlistData['CF_' . $contactFields['id']] == '0000-00-00' || $memberlistData['CF_' . $contactFields['id']] == '0000-00-00 00:00:00') {
                                $memberlistData['CF_' . $contactFields['id']] = '-';
                            } else {
                                $memberlistData['CF_' . $contactFields['id']] = $club->formatDate($memberlistData['CF_' . $contactFields['id']], 'date', 'Y-m-d');
                            }

                            break;
                        case 'multiline':
                            if ($memberlistData['CF_' . $contactFields['id']] == '') {
                                $memberlistData['CF_' . $contactFields['id']] = '-';
                            }
                            break;
                    }
                }
            }
        }
    }

    /**
     * Function to check whether admin or not.
     *
     * @param Int $roleId
     *
     * @return Bool
     */
    private function isAdmin($roleId = null)
    {
        $userRights = array('ROLE_GROUP_ADMIN', 'ROLE_CONTACT_ADMIN', 'ROLE_FORUM_ADMIN', 'ROLE_DOCUMENT_ADMIN');
        $teamRight = $this->container->get('contact')->checkClubRoleRights($roleId);

        $userrightsIntersect = array_intersect($userRights, $teamRight);
        if (count($userrightsIntersect) > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Function to set privacy to table field data.
     *
     * @param Array $tableFieldValuesArray
     * @param Array $memberlistData
     * @param Bool  $isAdmin
     *
     * @return Array $result
     */
    private function iterateContactData($tableFieldValuesArray, $memberlistData, $isAdmin)
    {
        $contactFields = $this->get('club')->get('allContactFields'); //echo '<pre>';
        foreach ($tableFieldValuesArray as $tableFieldValue) {
            if ($tableFieldValue['type'] == 'CF') {
                $dataVisible = false;
                // If team admin & team admin visibility =1
                if ($isAdmin && $contactFields[$tableFieldValue['id']]['is_visible_teamadmin'] == '1') {
                    $dataVisible = true;
                } elseif ($contactFields[$tableFieldValue['id']]['privacy_contact'] != 'private') {
                    $dataVisible = true;
                }
                // If contact field is private
                if ($memberlistData[$tableFieldValue['name'] . '_visibility'] == 'community') {
                    $dataVisible = true;
                } elseif ($memberlistData[$tableFieldValue['name'] . '_visibility'] == 'private') {
                    $dataVisible = false;
                }
                //if contact field changed and waiting for admin approval.
                if ($dataVisible) {
                    if ($isAdmin && $memberlistData[$tableFieldValue['name'] . '_Flag'] == 'NONE') {
                        $result = $memberlistData[$tableFieldValue['name'] . '_CHANGED'];
                    } else {
                        $result = $memberlistData[$tableFieldValue['name']];
                    }
                } else {
                    $result = '';
                }
            }  else {
                $result = $memberlistData[$tableFieldValue['name']];
            }
            $output[] = $this->filterContactDetails($result, $tableFieldValue, $memberlistData);
        }

        return $output;
    }

    /**
     * Function to filter Roll function names.
     *
     * @param Array $memberlistData
     * @param Bool  $isAdmin
     *
     * @return String $fnNameString
     */
    private function getRollFunctionNames($memberlistData, $isAdmin)
    {
        if ($isAdmin) {
            $funcArray = explode(',', $memberlistData['Function']);
            $funcAddedArray = explode(',', $memberlistData['Function_ADDED']);
            $funcRemovedArray = explode(',', $memberlistData['Function_REMOVED']);
            $funcMergeArray = array_merge($funcAddedArray, $funcArray);
            $funcResultArray = (!empty($funcRemovedArray)) ? array_diff($funcMergeArray, $funcRemovedArray) : $funcMergeArray;
            $funcResultFilterArray = array_filter($funcResultArray);
            $fnNamesAndIds = implode(',', $funcResultFilterArray);
        } else {
            $fnNamesAndIds = $memberlistData['Function'];
        }
        $fnNamesAndIdsArray = explode(',', $fnNamesAndIds);
        // Split function name from function name and id combination.
        $fnNamesArray = array_map(function ($a) {
            $b = explode('#', $a);

            return $b[0];
        }, $fnNamesAndIdsArray);
        // Create string from function name array
        $fnNameString = implode(', ', $fnNamesArray);

        return $fnNameString;
    }

    /**
     * Function to filter contact field data.
     *
     * @param String $data
     * @param Array  $tableFieldValue
     * @param Array  $memberlistData
     *
     * @return String $result
     */
    private function filterContactDetails($data, $tableFieldValue, $memberlistData)
    {
        $contactFields = $this->get('club')->get('allContactFields');
        $specialFieldsArray = $this->container->getParameter('country_fields');
        $countryList = Intl::getRegionBundle()->getCountryNames();
        $languages = Intl::getLanguageBundle()->getLanguageNames();
        $tableFieldId = $tableFieldValue['id'];
        $cnFields = $contactFields[$tableFieldId];
        $result = str_replace('<', '&lt;', str_replace('>', '&gt;', $data));

        // To change date format
        if ($cnFields['type'] === 'date') {
            $result = ($result != '0000-00-00' && $result != '') ? date(FgSettings::getPhpDateFormat(), strtotime($result)) : '';
        }
        // To change image field
        if ($cnFields['type'] === 'imageupload' || $cnFields['type'] === 'fileupload') {
            $uploadPath = $this->get('fg.avatar')->getContactfieldPath($tableFieldId);
            $result = ($result != '') ? "$result ($uploadPath/$result)" : '';
        }
        // find the actual country from the country code
        if (in_array($tableFieldId, $specialFieldsArray) && $cnFields != '') {
            $result = $countryList[strtoupper($result)];
        }
        //For find the language from the short key
        if ($tableFieldId == $this->container->getParameter('system_field_corress_lang') && $result != '') {
            $result = $languages[strtolower($result)];
        }
        //For age field
        if ($tableFieldValue['name'] == 'Gage' && $result <= 0) {
            $result = '-';
        }
        if ($tableFieldId == $this->container->getParameter('system_field_gender') && $result != '') {
            $result = (strtolower($result) == 'male') ? $this->container->get('translator')->trans('CM_MALE') : $this->container->get('translator')->trans('CM_FEMALE');
        }
        if ($tableFieldId == $this->container->getParameter('system_field_salutaion') && $result != '') {
            $result = (strtolower($result) == 'formal') ? $this->container->get('translator')->trans('CM_FORMAL') : $this->container->get('translator')->trans('CM_INFORMAL');
        }

        return $result;
    }
}
