<?php

namespace Internal\TeamBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Internal\TeamBundle\Util\MemberlistData;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Common\UtilityBundle\Repository\Pdo\ContactPdo;
use Symfony\Component\Intl\Intl;
use Common\UtilityBundle\Repository\Pdo\MessagePdo;
use Common\UtilityBundle\Util\FgPermissions;
use Common\UtilityBundle\Util\FgSettings;

/**
 * TeamOverviewController.
 *
 * This controller used for handling team s overview
 *
 * @author     pitsolutions.ch
 *
 * @version    Fairgate V4
 */
class TeamOverviewController extends FgController
{

    /**
     * Function to handle team overview page display.
     *
     * @return template
     */
    public function teamOverviewAction()
    {
        $permissionObj = new FgPermissions($this->container);
        $accessCheckArray = array('from' => 'overview', 'type' => 'teams');
        $allowedTabs = $permissionObj->checkAreaAccess($accessCheckArray);

        return $this->render('InternalTeamBundle:TeamOverview:teamOverview.html.twig', array('clubId' => $this->clubId, 'contactId' => $this->contactId, 'tabs' => $allowedTabs, 'teamCount' => count($allowedTabs), 'type' => 'team', 'url' => $this->generateUrl('get_team_overview_content', array('id' => 'dummyId', 'type' => 'team'))));
    }

    /**
     * Action to handle display details of team.
     *
     * @return template
     */
    public function teamdetailoverviewAction()
    {
        $permissionObj = new FgPermissions($this->container);
        $accessCheckArray = array('from' => 'memberlist', 'type' => 'teams');
        $allowedTabs = $permissionObj->checkAreaAccess($accessCheckArray);
        $workgroupId = $this->container->get('club')->get('club_workgroup_id');
        $teamId = $this->container->get('club')->get('club_team_id');
        $defaultColumns = $this->container->getParameter('default_team_table_settings');
        $corrAddrCatId = $this->container->getParameter('system_category_address');
        $invAddrCatId = $this->container->getParameter('system_category_invoice');
        $corrAddrFieldIds = array();
        $invAddrFieldIds = array();
        $contactFields = $this->container->get('club')->get('contactFields');
        $columnsUrl = $this->generateUrl('get_team_member_columnsettings');
        foreach ($contactFields as $contactField) {
            if ($contactField['catId'] == $corrAddrCatId) {
                $corrAddrFieldIds[] = $contactField['id'];
            } elseif ($contactField['catId'] == $invAddrCatId) {
                $invAddrFieldIds[] = $contactField['id'];
            }
        }

        return $this->render('InternalTeamBundle:TeamOverview:teamdetailOverview.html.twig', array('contactId' => $this->contactId, 'tabs' => $allowedTabs, 'teamCount' => count($allowedTabs), 'type' => 'team', 'url' => $this->generateUrl('get_member_data', array('memberId' => 'dummyId', 'memberCategory' => 'team')), 'clubteamId' => $teamId, 'clubworkgroupId' => $workgroupId, 'clubId' => $this->clubId, 'defaultSetting' => $defaultColumns, 'corrAddrFieldIds' => $corrAddrFieldIds, 'invAddrFieldIds' => $invAddrFieldIds, 'columnsUrl' => $columnsUrl));
    }

    /**
     * To collect the details of team/workgroup.
     *
     * @param Request $request        Request object
     * @param Int     $memberId       team/workgroup id
     * @param String  $memberCategory team/workgroup
     *
     * @return JsonResponse
     */
    public function memberlistDetailAction(Request $request, $memberId, $memberCategory)
    {
        $output = array('iTotalRecords' => 0, 'iTotalDisplayRecords' => 0, 'aaData' => array(), 'aaDataType' => array());
        $teamRight = $this->container->get('contact')->checkClubRoleRights($memberId, false);

        //Set all request value to its corresponding variables
        $memberListData = new MemberlistData($this->container, $this->contactId);
        $memberListData->dataTableColumnData = $request->get('columns', '');
        $memberListData->sortColumnValue = $request->get('order', '');
        $memberListData->searchval = $request->get('search', '');
        $memberListData->tableFieldValues = $request->get('tableField', '');
        $memberListData->startValue = $request->get('start', '');
        $memberListData->displayLength = $request->get('length', '');
        $memberListData->memberId = $request->get('memberId', $memberId);
        $memberListData->memberCategoryId = ($memberCategory == 'team') ? $this->container->get('club')->get('club_team_id') : $this->container->get('club')->get('club_workgroup_id');
        $memberListData->memberlistType = $memberCategory;
        //check the admin flag
        $userRights = array('ROLE_GROUP_ADMIN', 'ROLE_CONTACT_ADMIN', 'ROLE_FORUM_ADMIN', 'ROLE_DOCUMENT_ADMIN');
        $userrightsIntersect = array_intersect($userRights, $teamRight);
        $adminFlag = 0;
        $isAdmin = ($this->container->get('contact')->get('isSuperAdmin') || (($this->container->get('contact')->get('isFedAdmin')) && ($this->container->get('club')->get('type') != 'federation'))) ? 1 : 0;

        if (count($userrightsIntersect) > 0 || $isAdmin == 1) {
            $adminFlag = 1;
        }
        $output['adminflag'] = $adminFlag;
        $actionMenu = $this->getActionMenu($memberListData->memberId, $memberCategory, $adminFlag, $userrightsIntersect);
        //handle no access exception
        if (count($teamRight) == 0) {
            $output['actionMenu'] = $actionMenu;

            return new JsonResponse($output);
        }
        $memberListData->adminFlag = ($adminFlag == 1) ? true : false;

        //Remove the fields from the local storage which i don't have visiblity *** */
        $memberListDataUpdated = $this->removeInvisibleFields($memberListData, $adminFlag, $teamRight);
        $memberListData->editConf =1;
       
       
        //For get the contact list array 
        $memberData = $memberListDataUpdated->getMemberlistData();

        //collect total number of records
        $totalrecords = $memberData['totalcount'];
        //For set the datatable json array
        $output['iTotalRecords'] = $totalrecords;
        $output['iTotalDisplayRecords'] = $totalrecords;
        $output['aaData'] = $this->iterateDataTableData($memberData['data'], $this->container->getParameter('country_fields'), $memberListData->tabledata, $memberCategory, $memberId);
        $output['aaDataType'] = $this->getmemberFieldDetails($memberListData->tabledata);
        $output['aaDataHide'] = $memberListDataUpdated->hiddenFields;
        $output['actionMenu'] = $actionMenu;

        return new JsonResponse($output);
    }

    /**
     * For iterate the member list data.
     *
     * @param array  $memberlistDatas    result data from the base query
     * @param array  $specialFieldsArray language and country array
     * @param array  $tabledatas         table column details
     * @param string $memberCategory     selected type (team/workgroup)
     * @param Int    $memberId           team/workgroup id
     *
     * @return type
     */
    public function iterateDataTableData($memberlistDatas, $specialFieldsArray, $tabledatas, $memberCategory, $memberId)
    {
        $output['aaData'] = array();
        $countryList = Intl::getRegionBundle()->getCountryNames();
        $languages = Intl::getLanguageBundle()->getLanguageNames();

        //check for find the type of contact field
        $allContactFiledsData = $this->container->get('club')->get('allContactFields');

        foreach ($memberlistDatas as $memberKey => $memberlistData) {
            // find the actual country from the country code
            foreach ($memberlistData as $key => $cnFields) {
                //$memberlistData[$key] = htmlentities($cnFields);
                $memberlistData[$key] = str_replace('<', '&lt;', str_replace('>', '&gt;', $cnFields));

                $contactFieldId = preg_replace('/[^0-9]/', '', $key);
                if ($contactFieldId != '') {
                    $contactFieldDate = $allContactFiledsData[$contactFieldId];

                    if ($contactFieldDate['type'] == 'date') {
                        //change the format from Y-m-d to the required fomrat
                        $changedDateFields = $memberlistData['CF_' . $contactFieldId . '_CHANGED'];
                        if (date_create_from_format('Y-m-d', $changedDateFields)) {
                            $dateObj = new \DateTime();
                            $formattedDate = $dateObj->createFromFormat('Y-m-d', $changedDateFields)->format(FgSettings::getPhpDateFormat());
                            $memberlistData['CF_' . $contactFieldId . '_CHANGED'] = $formattedDate;
                        }
                    }
                }

                list($text, $val) = explode('CF_', $key);
                if (in_array($val, $specialFieldsArray) && $cnFields != '') {
                    $memberlistData[$key] = $countryList[strtoupper($memberlistData[$key])];
                    $memberlistData[$key . '_original'] = strtoupper($memberlistData[$key]);
                }
                //For find the language from the short key
                if ($key == 'CF_' . $this->container->getParameter('system_field_corress_lang') && $memberlistData[$key] != '') {
                    $memberlistData[$key] = $languages[strtolower($memberlistData[$key])];
                    $memberlistData[$key . '_original'] = strtolower($memberlistData[$key]);
                }
                if ($key == 'Gage' && $memberlistData[$key] <= 0) {
                    $memberlistData[$key] = '-';
                }

                if ($key == 'CF_' . $this->container->getParameter('system_field_gender') && $memberlistData[$key] != '') {
                    $memberlistData[$key . '_original'] = $memberlistData[$key];
                    if (strtolower($memberlistData[$key]) == 'male') {
                        $memberlistData[$key] = $this->container->get('translator')->trans('CM_MALE');
                    } else {
                        $memberlistData[$key] = $this->container->get('translator')->trans('CM_FEMALE');
                    }
                }
                if ($key == 'CF_' . $this->container->getParameter('system_field_salutaion') && $memberlistData[$key] != '') {
                    $memberlistData[$key . '_original'] = $memberlistData[$key];
                    if (strtolower($memberlistData[$key]) == 'formal') {
                        $memberlistData[$key] = $this->container->get('translator')->trans('CM_FORMAL');
                    } else {
                        $memberlistData[$key] = $this->container->get('translator')->trans('CM_INFORMAL');
                    }
                }
                if ($key == 'Profilepic' && $memberlistData[$key] != '') {
                    if ($memberlistData[$key] != '') {
                        $memberlistData[$key] = $this->container->get('fg.avatar')->getAvatar($memberlistData['id'], 65);
                        $memberlistData[$key . 'Exists'] = file_exists(str_replace('\\', '/', realpath('')) . $this->container->get('fg.avatar')->getAvatar($memberlistData['id'], '', true));
                        if ($memberlistData['Iscompany'] && ($memberlistData[$key . 'Exists'])) {
                            //calculate width of profile picture when its height is 35 (to .list in team member listing)
                            list($width, $height) = getimagesize($memberlistData[$key]);
                            $memberlistData['imageWidth'] = (int) ceil($width * 35 / $height);
                           
                        }
                    }
                }
            }

            //check the field type
            $this->fieldType($tabledatas, $memberlistData);

            $memberlistData['edit_url'] = $this->container->get('router')->generate('edit_' . $memberCategory . 'member', array('type' => $memberCategory, 'roleId' => $memberId, 'contact' => $memberlistData['id']));
            $sponsorIcon = false;
            $memberlistData['SponsorIcon'] = $sponsorIcon;
            $memberlistData['Function'] = $memberlistData['Function'];
            //to find contact name click url
            $clickUrl = $this->container->get('router')->generate('internal_community_profile', array('contactId' => $memberlistData['id']));
            $memberlistData['click_url'] = $clickUrl;
            $output['aaData'][] = $memberlistData;
        }

        return $output['aaData'];
    }

    /**
     * Function to use set the fields acccording to the type.
     *
     * @param type $tabledatas     column names
     * @param type $memberlistData member result array
     */
    private function fieldType($tabledatas, &$memberlistData)
    {
        $club = $this->get('club');
        $allContactFiledsData = $club->get('allContactFields');
        if (is_array($tabledatas) && count($tabledatas) > 0) {
            foreach ($tabledatas as $key => $contactFields) {
                if (array_key_exists($contactFields['id'], $allContactFiledsData)) {
                    switch ($allContactFiledsData[$contactFields['id']]['type']) {
                        case 'date':
                            if ($memberlistData['CF_' . $contactFields['id']] == '' || $memberlistData['CF_' . $contactFields['id']] == '0000-00-00' || $memberlistData['CF_' . $contactFields['id']] == '0000-00-00 00:00:00') {
                                $memberlistData['CF_' . $contactFields['id']] = '-';
                            } else {
                                $newdate = date_create($memberlistData['CF_' . $contactFields['id']]);
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
     * For get the type of selected contact fields.
     *
     * @param array $tabledatas
     *
     * @return array
     */
    private function getmemberFieldDetails($tabledatas)
    {
        $club = $this->container->get('club');
        $allContactFiledsData = $club->get('allContactFields');
        $output['aaDataType'] = array();
        $originalTitlesArray = $this->container->getParameter('country_fields');
        $originalTitlesArray[] = $this->container->getParameter('system_field_corress_lang');
        $originalTitlesArray[] = $this->container->getParameter('system_field_salutaion');
        $originalTitlesArray[] = $this->container->getParameter('system_field_gender');
        //service for contact field/profile image path
        $pathService = $this->container->get('fg.avatar');
        foreach ($tabledatas as $key => $contactFields) {
            if (array_key_exists($contactFields['id'], $allContactFiledsData)) {
                switch ($allContactFiledsData[$contactFields['id']]['type']) {
                    case 'login email':case 'email':case 'Email':
                        $output['aaDataType'][] = array('title' => 'CF_' . $contactFields['id'], 'type' => 'email', 'attrId' => $contactFields['id'], 'category_id' => $allContactFiledsData[$contactFields['id']]['category_id'], 'is_editable' => $allContactFiledsData[$contactFields['id']]['is_editable'], 'is_required' => $allContactFiledsData[$contactFields['id']]['is_required'], 'is_company' => $allContactFiledsData[$contactFields['id']]['is_company'], 'is_personal' => $allContactFiledsData[$contactFields['id']]['is_personal'], 'is_system_field' => $allContactFiledsData[$contactFields['id']]['is_system_field'], 'addres_type' => $allContactFiledsData[$contactFields['id']]['addres_type'], 'address_id' => $allContactFiledsData[$contactFields['id']]['address_id']);
                        break;
                    case 'imageupload':
                        $output['aaDataType'][] = array('title' => 'CF_' . $contactFields['id'], 'type' => 'imageupload', 'uploadPath' => $pathService->getContactfieldPath($contactFields['id']));
                        break;
                    case 'fileupload':
                        $output['aaDataType'][] = array('title' => 'CF_' . $contactFields['id'], 'type' => 'fileupload', 'uploadPath' => $pathService->getContactfieldPath($contactFields['id']));
                        break;
                    case 'url':
                        $output['aaDataType'][] = array('title' => 'CF_' . $contactFields['id'], 'type' => 'url', 'attrId' => $contactFields['id'], 'category_id' => $allContactFiledsData[$contactFields['id']]['category_id'], 'is_editable' => $allContactFiledsData[$contactFields['id']]['is_editable'], 'is_required' => $allContactFiledsData[$contactFields['id']]['is_required'], 'is_company' => $allContactFiledsData[$contactFields['id']]['is_company'], 'is_personal' => $allContactFiledsData[$contactFields['id']]['is_personal'], 'is_system_field' => $allContactFiledsData[$contactFields['id']]['is_system_field'], 'addres_type' => $allContactFiledsData[$contactFields['id']]['addres_type'], 'address_id' => $allContactFiledsData[$contactFields['id']]['address_id']);
                        break;
                    case 'multiline':
                        $output['aaDataType'][] = array('title' => 'CF_' . $contactFields['id'], 'type' => 'multiline', 'attrId' => $contactFields['id'], 'category_id' => $allContactFiledsData[$contactFields['id']]['category_id'], 'is_editable' => $allContactFiledsData[$contactFields['id']]['is_editable'], 'is_required' => $allContactFiledsData[$contactFields['id']]['is_required'], 'is_company' => $allContactFiledsData[$contactFields['id']]['is_company'], 'is_personal' => $allContactFiledsData[$contactFields['id']]['is_personal'], 'is_system_field' => $allContactFiledsData[$contactFields['id']]['is_system_field'], 'addres_type' => $allContactFiledsData[$contactFields['id']]['addres_type'], 'address_id' => $allContactFiledsData[$contactFields['id']]['address_id']);
                        break;
                    case 'singleline':
                        $output['aaDataType'][] = array('title' => 'CF_' . $contactFields['id'], 'type' => 'singleline', 'attrId' => $contactFields['id'], 'category_id' => $allContactFiledsData[$contactFields['id']]['category_id'], 'is_editable' => $allContactFiledsData[$contactFields['id']]['is_editable'], 'is_required' => $allContactFiledsData[$contactFields['id']]['is_required'], 'is_company' => $allContactFiledsData[$contactFields['id']]['is_company'], 'is_personal' => $allContactFiledsData[$contactFields['id']]['is_personal'], 'is_system_field' => $allContactFiledsData[$contactFields['id']]['is_system_field'], 'addres_type' => $allContactFiledsData[$contactFields['id']]['addres_type'], 'address_id' => $allContactFiledsData[$contactFields['id']]['address_id']);
                        break;
                    case 'select':
                        if (in_array($contactFields['id'], $originalTitlesArray)) {
                            $output['aaDataType'][] = array('title' => 'CF_' . $contactFields['id'], 'type' => 'select', 'originalTitle' => 'CF_' . $contactFields['id'] . '_original', 'attrId' => $contactFields['id'], 'category_id' => $allContactFiledsData[$contactFields['id']]['category_id'], 'is_editable' => $allContactFiledsData[$contactFields['id']]['is_editable'], 'is_required' => $allContactFiledsData[$contactFields['id']]['is_required'], 'is_company' => $allContactFiledsData[$contactFields['id']]['is_company'], 'is_personal' => $allContactFiledsData[$contactFields['id']]['is_personal'], 'is_system_field' => $allContactFiledsData[$contactFields['id']]['is_system_field'], 'addres_type' => $allContactFiledsData[$contactFields['id']]['addres_type'], 'address_id' => $allContactFiledsData[$contactFields['id']]['address_id']);
                        } else {
                            $output['aaDataType'][] = array('title' => 'CF_' . $contactFields['id'], 'type' => 'select', 'attrId' => $contactFields['id'], 'category_id' => $allContactFiledsData[$contactFields['id']]['category_id'], 'is_editable' => $allContactFiledsData[$contactFields['id']]['is_editable'], 'is_required' => $allContactFiledsData[$contactFields['id']]['is_required'], 'is_company' => $allContactFiledsData[$contactFields['id']]['is_company'], 'is_personal' => $allContactFiledsData[$contactFields['id']]['is_personal'], 'is_system_field' => $allContactFiledsData[$contactFields['id']]['is_system_field'], 'addres_type' => $allContactFiledsData[$contactFields['id']]['addres_type'], 'address_id' => $allContactFiledsData[$contactFields['id']]['address_id']);
                        }
                        break;
                    default:
                        $output['aaDataType'][] = array('title' => 'CF_' . $contactFields['id'], 'type' => $allContactFiledsData[$contactFields['id']]['type'], 'attrId' => $contactFields['id'], 'category_id' => $allContactFiledsData[$contactFields['id']]['category_id'], 'is_editable' => $allContactFiledsData[$contactFields['id']]['is_editable'], 'is_required' => $allContactFiledsData[$contactFields['id']]['is_required'], 'is_company' => $allContactFiledsData[$contactFields['id']]['is_company'], 'is_personal' => $allContactFiledsData[$contactFields['id']]['is_personal'], 'is_system_field' => $allContactFiledsData[$contactFields['id']]['is_system_field'], 'addres_type' => $allContactFiledsData[$contactFields['id']]['addres_type'], 'address_id' => $allContactFiledsData[$contactFields['id']]['address_id']);
                        break;
                }
            }
        }

        $output['aaDataType'][] = array('title' => 'contactname', 'type' => 'contactname');
        $output['aaDataType'][] = array('title' => 'edit', 'type' => 'edit');
        $output['aaDataType'][] = array('title' => 'Function', 'type' => 'Function');

        return $output['aaDataType'];
    }

    /**
     * Function to get details to display in role overview members box.
     *
     * @param Request $request Request Object
     *
     * @return JsonResponse
     */
    public function getMemberDetailsAction(Request $request)
    {
        $roleType = $request->get('roleType');
        $roleId = $request->get('roleId');
        $contactPdo = new ContactPdo($this->container);
        $memberDetails = $contactPdo->getMemberDetails($roleId, $roleType, $this->get('translator')->trans('TEAM_OVERVIEW_RESIDENCES_OTHERS'), $this->get('translator')->trans('TEAM_OVERVIEW_RESIDENCES_NOT_SPECIFIED'));

        return new JsonResponse($memberDetails);
    }

    /**
     * role userrights page.
     *
     * @param Request $request Request object
     * @param int     $role    role id
     *
     * @return template
     */
    public function roleUserrightsAction(Request $request, $role)
    {
        $mod = $request->get('module');
        $type = ($mod == 'team') ? 'teams' : 'workgroups';
        $permissionObj = new FgPermissions($this->container);
        $accessCheckArray = array('from' => 'group', 'id' => $role, 'type' => $type, 'allowedRights' => array('ROLE_GROUP_ADMIN', 'ROLE_FED_ADMIN'));
        $permissionObj->checkAreaAccess($accessCheckArray);

        $backLink = ($mod == 'team') ? $this->generateUrl('team_detail_overview') : $this->generateUrl('workgroup_detail_overview');
        $breadCrumb = array('breadcrumb_data' => array(), 'back' => $backLink);

        $teamName = $this->em->getRepository('CommonUtilityBundle:FgRmCategoryRoleFunction')->getTeamName($this->conn, $role);

        //$isClubOrSuperAdmin ->logged contact
        $contactService = $this->container->get('contact');
        $isClubOrSuperAdminOrFedAdmin = json_encode($contactService->get('availableUserRights'), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);

        //get club admins of team
        $clubAdmins = json_encode($this->em->getRepository('CommonUtilityBundle:sfGuardGroup')->getClubAdmins($this->clubId, $this->conn), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);

        //get club admins of team
        $fedAdmins = json_encode($this->em->getRepository('CommonUtilityBundle:sfGuardGroup')->getFedAdmins($this->federationId, $this->conn), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);

        //get team admin list
        $club = $this->container->get('club');
        $masterTable = $club->get('clubTable');
        $grpAdmins = $this->em->getRepository('CommonUtilityBundle:SfGuardGroup')->teamAdminList($this->conn, $this->clubId, 0, $role, $masterTable, $club);
        $autocompleteExclude = json_encode($this->getAllListingBlock($grpAdmins), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);

        $grpAdmins = json_encode($grpAdmins, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
        $isTeamMemberArray = $this->em->getRepository('CommonUtilityBundle:sfGuardGroup')->isTeamMember($role, $this->contactId, $this->conn);
        $isTeamMember = json_encode($isTeamMemberArray, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);

        $dropdownListArray = $this->em->getRepository('CommonUtilityBundle:FgRmRole')->getRoleTeamDetails($this->clubId, $this->container);
        $dropdownList = json_encode($dropdownListArray, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);

        $sectionDropdownArray = $this->em->getRepository('CommonUtilityBundle:SfGuardGroup')->getAllTeamGroups();
        $sectionDropdown = json_encode($this->formatRights($sectionDropdownArray));

        return $this->render('InternalTeamBundle:TeamOverview:indexUserrights.html.twig', array('teamName' => $teamName, 'type' => ($mod == 'team') ? 'T' : 'W', 'roleId' => $role, 'clubId' => $this->clubId,
                'contactId' => $this->contactId, 'groupAdmin' => $grpAdmins, 'contactNameUrl' => str_replace('25', '', $this->generateUrl('contact_names_userrights', array('term' => '%QUERY'))),
                'loggedContactId' => $this->contactId, 'dropdown' => $dropdownList, 'exclude' => $autocompleteExclude, 'admins' => $sectionDropdown, 'fedAdmins' => $fedAdmins, 'clubAdmins' => $clubAdmins,
                'isClubOrSuperAdminOrFedAdmin' => $isClubOrSuperAdminOrFedAdmin, 'isTeamMember' => $isTeamMember, 'backLink' => $backLink, 'breadCrumb' => $breadCrumb,));
    }

    /**
     * Function is used to get all the user rights blocks as seperate array.
     *
     * @param Array $groupContacts Group-contact details
     *
     * @return Array
     */
    private function getAllListingBlock($groupContacts)
    {
        $existingUserDetails = $existingSectionUser = $existingWGUserDetails = $existingWGSectionUser = array();
        $i = $j = $k = $l = 0;

        // Looping array containing all user rights details
        foreach ($groupContacts as $groupContact) {
            if ($groupContact['module_type'] == 'all' && $groupContact['type'] == 'role' && $groupContact['roleType'] == 'T') {
                $existingUserDetails = $this->assignValuesToGroup($existingUserDetails, $i, $groupContact);
                $i++;
            }

            if ($groupContact['module_type'] == 'all' && $groupContact['type'] == 'role' && $groupContact['roleType'] == 'W') {
                $existingWGUserDetails = $this->assignValuesToGroup($existingWGUserDetails, $k, $groupContact);
                $k++;
            }

            if ($groupContact['module_type'] != 'all' && $groupContact['type'] == 'role' && $groupContact['roleType'] == 'T') {
                $existingSectionUser = $this->assignValuesToGroup($existingSectionUser, $j, $groupContact);
                $j++;
            }

            if ($groupContact['module_type'] != 'all' && $groupContact['type'] == 'role' && $groupContact['roleType'] == 'W') {
                $existingWGSectionUser = $this->assignValuesToGroup($existingWGSectionUser, $l, $groupContact);
                $l++;
            }
        }

        return array('existingUserDetails' => $existingUserDetails, 'existingSectionUser' => $existingSectionUser, 'existingWGUserDetails' => $existingWGUserDetails, 'existingWGSectionUser' => $existingWGSectionUser);
    }

    /**
     * Assign vales to common array.
     *
     * @param Array $assignGroupArray Array to be generated for listing
     * @param Int   $indexVal         Index value
     * @param Array $groupContact     Query resuly value
     *
     * @return Array
     */
    private function assignValuesToGroup($assignGroupArray, $indexVal, $groupContact)
    {
        $assignGroupArray[$indexVal][0]['id'] = $groupContact['contact_id'];
        $assignGroupArray[$indexVal][0]['value'] = $groupContact['contactname'];
        $assignGroupArray[$indexVal][0]['label'] = $groupContact['contactname'];

        return $assignGroupArray;
    }

    /**
     * Contact autocomplete action.
     *
     * @param Request $request       Request object
     * @param string  $term          search parameter
     * @param string  $passedColumns Extra columns to be taken
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function contactNamesAction(Request $request, $term, $passedColumns = '')
    {
        $roleId = $request->get('roleId');
        $contactService = $this->container->get('contact');
        $teamRight = $contactService->get('mainAdminRightsForFrontend');

        $exclude = $request->get('exclude');
        $isCompany = $request->get('isCompany');
        $contType = $request->get('type');

        if (empty($teamRight)) {
            $include = $roleId;
        }
        $contactsArray = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getAutocompleteContacts($exclude, $isCompany, $contType, $this->container, $this->clubId, $this->clubType, $passedColumns, $term, 0, $include);

        return new JsonResponse($contactsArray);
    }

    /**
     * function to format userrights dropdown.
     *
     * @param array $adminSection drop down list of section rights
     *
     * @return array
     */
    private function formatRights($adminSection)
    {
        foreach ($adminSection as $key => $value) {
            switch ($value['name']) {
                case 'ContactAdmin': $adminSection[$key]['transName'] = $this->get('translator')->trans('UR_CONTACT_ADMIN');
                    break;
                case 'ForumAdmin': $adminSection[$key]['transName'] = $this->get('translator')->trans('UR_FORUM_ADMIN');
                    break;
                case 'DocumentAdmin': $adminSection[$key]['transName'] = $this->get('translator')->trans('UR_DOCUMENT_ADMIN');
                    break;
                case 'CalendarAdmin': $adminSection[$key]['transName'] = $this->get('translator')->trans('UR_CALENDAR_ADMIN');
                    break;
                case 'GalleryAdmin': $adminSection[$key]['transName'] = $this->get('translator')->trans('UR_GALLERY_ADMIN');
                    break;
                case 'ArticleAdmin': $adminSection[$key]['transName'] = $this->get('translator')->trans('UR_ARTICLE_ADMIN');
                    break;
            }
        }

        return $adminSection;
    }

    /**
     * Function is used to save user rights from user rights setting page.
     *
     * @return JSON
     */
    public function saveRoleUserRightsAction()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $userRightsArray = json_decode($request->get('postArr'), true); // Getting save values from setting page in JSON format
        $formatArray['new'] = array();
        $groupAdmin = $this->container->getParameter('group_admin');
        if (isset($userRightsArray['teams']['teamAdminInt'])) {
            $formatArray['new']['teamAdminInt'] = $formatArray['new']['teamAdminInt'][$groupAdmin] = $formatArray['new']['teamAdminInt'][$groupAdmin]['contact'] = array();
            foreach ($userRightsArray['teams']['teamAdminInt'] as $val) {
                $formatArray = $this->formatRoleAdmin($userRightsArray, $formatArray, $val, 'T');
            }
        }

        if (isset($userRightsArray['teams']['wgAdminInt'])) {
            $formatArray['new']['wgAdminInt'] = $formatArray['new']['wgAdminInt'][$groupAdmin] = $formatArray['new']['wgAdminInt'][$groupAdmin]['contact'] = array();
            foreach ($userRightsArray['teams']['wgAdminInt'] as $val) {
                $formatArray = $this->formatRoleAdmin($userRightsArray, $formatArray, $val, 'W');
            }
        }

        if (isset($userRightsArray['teams']['teamSection'])) {
            $formatArray['new']['teamSection'] = $formatArray['new']['teamSection']['contact'] = array();
            $formatArray = $this->formatRoleSection($userRightsArray, $formatArray, 'teamSection');
        }
        if (isset($userRightsArray['teams']['wgSection'])) {
            $formatArray['new']['wgSection'] = $formatArray['new']['wgSection']['contact'] = array();
            $formatArray = $this->formatRoleSection($userRightsArray, $formatArray, 'wgSection');
        }

        // Calling the function to save user rights
        $this->em->getRepository('CommonUtilityBundle:SfGuardGroup')->saveUserRights($this->conn, $formatArray, $this->clubId, $this->contactId, $this->container);

        return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('USER_RIGHTS_SAVED_SUCCESS')));
    }

    /**
     * format role section area for save.
     *
     * @param array  $userRightsArray user rights array
     * @param array  $formatArray     format array
     * @param string $type            roletype
     *
     * @return array
     */
    private function formatRoleSection($userRightsArray, $formatArray, $type)
    {
        if (isset($userRightsArray['teams'][$type]['new'])) {
            foreach ($userRightsArray['teams'][$type]['new'] as $userRightsSubArray) {
                if (isset($userRightsSubArray['contact'])) {
                    foreach ($userRightsSubArray['contact'] as $contact => $value) {
                        $contactId = $contact;
                    }
                    if (isset($userRightsSubArray['modules'])) {
                        $formatArray['new'][$type][$contactId]['role'][$userRightsSubArray['modules']['teams']]['groups'] = $userRightsSubArray['modules']['groups'];
                    }
                }
            }
        }
        //existing team admin modules
        if (isset($userRightsArray['teams'][$type]['existing'])) {
            foreach ($userRightsArray['teams'][$type]['existing'] as $contactId => $teams) {
                if ($teams['deleted'] == 1) {
                    $formatArray['new'][$type][$contactId]['deleted'] = 1;
                    break;
                }
                foreach ($teams as $teamId => $modules) {
                    if (is_array($modules)) {
                        if ($modules['modules'] == '') {
                            $formatArray['new'][$type][$contactId]['role'][$teamId]['groups'] = 'deleted';
                        } else {
                            $formatArray['new'][$type][$contactId]['role'][$teamId]['groups'] = $modules['modules'];
                        }
                    } elseif ($modules == 1) {
                        $formatArray['new'][$type][$contactId]['role'][$teamId]['groups'] = 'deleted';
                    }
                }
            }
        }

        return $formatArray;
    }

    /**
     * function to form array  structure for userrights save.
     *
     * @param array $userRightsArray user rights array
     * @param array $formatArray     format array
     * @param array $val             contact array
     *
     * @return array
     */
    private function formatRoleAdmin($userRightsArray, $formatArray, $val, $roleType)
    {
        $groupId = $this->container->getParameter('group_admin');
        $role = 'teams';
        if ($roleType == 'T') {
            $grp = 'teamAdminInt';
        } else {
            $grp = 'wgAdminInt';
        }

        if (isset($userRightsArray['teams'][$grp]['delete'])) {
            foreach ($userRightsArray['teams'][$grp]['delete'] as $contactId => $team) {
                $formatArray['new'][$grp][$groupId]['contact'][$contactId]['delete'] = $team['team'];
            }
        }
        if (isset($val['contact'])) {
            foreach ($val['contact'] as $value) {
                $contactId = $value;
            }
            if ($val[$role] == '') {
                $formatArray['new'][$grp][$groupId]['contact'][$contactId]['team'] = 'deleted';
            } else {
                $formatArray['new'][$grp][$groupId]['contact'][$contactId]['team'] = $val[$role];
            }
        }

        return $formatArray;
    }

    /**
     * Function for create the action menu.
     *
     * @param int $memberId            teamid/workgroupid
     * @param int $memberType          team/workgroup
     * @param int $adminflag           team/workgroup
     * @param int $userrightsIntersect team/workgroup
     *
     * @return array action menu array
     */
    private function getActionMenu($memberId, $memberType = 'team', $adminflag = 0, $userrightsIntersect)
    {
        if ($adminflag == 1) {
            $actionMenuNoneSelectedTextDummy = array('memberCreate' => array('isVisibleAlways' => 'true', 'title' => $this->get('translator')->trans('CREATE_MEMBER'), 'dataUrl' => $this->container->get('router')->generate('create_' . $memberType . 'member', array('type' => $memberType, 'roleId' => 'roleId')), 'localStorageName' => $memberType . '_' . $this->clubId . '_' . $this->contactId, 'isActive' => 'true'),
                'addnonexistingMembers' => array('isVisibleAlways' => 'true', 'title' => $this->get('translator')->trans('IN_ACTION_MENU_ADD'), 'dataUrl' => $this->container->get('router')->generate('assign_role_internal', array('roleId' => $memberId, 'roleType' => $memberType))),
                'memberEdit' => array('title' => $this->get('translator')->trans('EDIT_MEMBER'), 'dataUrl' => '', 'hrefLink' => '', 'isActive' => 'false'),
                'removeMember' => array('title' => $this->get('translator')->trans('REMOVE_TEAM_MEMBER'), 'dataUrl' => '', 'isActive' => 'false', 'divider' => '1', 'localStorageName' => $memberType . '_' . $this->clubId . '_' . $this->contactId),
                'Export' => array('title' => $this->get('translator')->trans('EXPORT'), 'dataUrl' => $this->container->get('router')->generate('export_settings')),
                'sendPersonalMessage' => array('title' => $this->get('translator')->trans('MESSAGE_SENT_PERSONAL'), 'dataUrl' => '', 'hrefLink' => '', 'isActive' => 'false'),
                'sendGroupMessage' => array('title' => $this->get('translator')->trans('MESSAGE_SENT_GROUP'), 'dataUrl' => '', 'hrefLink' => $this->generateUrl('internal_create_message_step1_' . $memberType, array('mr' => $memberId)), 'isActive' => 'true', 'divider' => '1'),
            );

            $actionMenuSingleSelectedTextDummy = array('memberCreate' => array('title' => $this->get('translator')->trans('CREATE_MEMBER'), 'dataUrl' => $this->container->get('router')->generate('create_' . $memberType . 'member', array('type' => $memberType, 'roleId' => 'roleId')), 'localStorageName' => $memberType . '_' . $this->clubId . '_' . $this->contactId, 'isActive' => 'false'),
                'addnonexistingMembers' => array('title' => $this->get('translator')->trans('IN_ACTION_MENU_ADD'), 'dataUrl' => $this->container->get('router')->generate('assign_role_internal', array('roleId' => $memberId, 'roleType' => $memberType)), 'isActive' => 'false'),
                'memberEdit' => array('title' => $this->get('translator')->trans('EDIT_MEMBER'), 'dataUrl' => $this->container->get('router')->generate('edit_' . $memberType . 'member', array('type' => $memberType, 'roleId' => 'roleId', 'contact' => 'contactId')), 'localStorageName' => $memberType . '_' . $this->clubId . '_' . $this->contactId, 'isActive' => 'true'),
                'removeMember' => array('title' => $this->get('translator')->trans('REMOVE_TEAM_MEMBER'), 'dataUrl' => '', 'isActive' => 'true', 'divider' => '1', 'localStorageName' => $memberType . '_' . $this->clubId . '_' . $this->contactId),
                'Export' => array('title' => $this->get('translator')->trans('EXPORT'), 'dataUrl' => $this->container->get('router')->generate('export_settings')),
                'sendPersonalMessage' => array('title' => $this->get('translator')->trans('MESSAGE_SENT_PERSONAL'), 'dataUrl' => $this->generateUrl('internal_create_message_step1_contact'), 'isActive' => 'true'),
                'sendGroupMessage' => array('title' => $this->get('translator')->trans('MESSAGE_SENT_GROUP'), 'dataUrl' => '', 'hrefLink' => '', 'isActive' => 'false'),
            );

            $actionMenuMultipleSelectedTextDummy = array('memberCreate' => array('title' => $this->get('translator')->trans('CREATE_MEMBER'), 'dataUrl' => $this->container->get('router')->generate('create_' . $memberType . 'member', array('type' => $memberType, 'roleId' => 'roleId')), 'localStorageName' => $memberType . '_' . $this->clubId . '_' . $this->contactId, 'isActive' => 'false'),
                'addnonexistingMembers' => array('title' => $this->get('translator')->trans('IN_ACTION_MENU_ADD'), 'dataUrl' => $this->container->get('router')->generate('assign_role_internal', array('roleId' => $memberId, 'roleType' => $memberType)), 'isActive' => 'false'),
                'memberEdit' => array('title' => $this->get('translator')->trans('EDIT_MEMBER'), 'dataUrl' => '', 'hrefLink' => '', 'isActive' => 'false'),
                'removeMember' => array('title' => $this->get('translator')->trans('REMOVE_TEAM_MEMBER'), 'dataUrl' => '', 'isActive' => 'true', 'divider' => '1', 'localStorageName' => $memberType . '_' . $this->clubId . '_' . $this->contactId),
                'Export' => array('title' => $this->get('translator')->trans('EXPORT'), 'dataUrl' => $this->container->get('router')->generate('export_settings')),
                'sendPersonalMessage' => array('title' => $this->get('translator')->trans('MESSAGE_SENT_PERSONAL'), 'dataUrl' => $this->generateUrl('internal_create_message_step1_contact'), 'isActive' => 'true'),
                'sendGroupMessage' => array('title' => $this->get('translator')->trans('MESSAGE_SENT_GROUP'), 'dataUrl' => '', 'hrefLink' => '', 'isActive' => 'false', 'divider' => '1'),);

            $isAdmin = ($this->container->get('contact')->get('isSuperAdmin')) ? 1 : 0;
            if ($isAdmin == 1 || in_array('ROLE_GROUP_ADMIN', $userrightsIntersect)) {
                $actionMenuNoneSelectedText = array_merge($actionMenuNoneSelectedTextDummy, array('teamUserRight' => array('isVisibleAlways' => 'true', 'title' => $this->get('translator')->trans('USER_RIGHTS_PAGE_TITLE'), 'dataUrl' => ($memberType == 'team') ? $this->generateUrl('team_userrights') : $this->generateUrl('workgroup_userrights'), 'isActive' => 'true', 'localStorageName' => ($memberType == 'team') ? 'team_' . $this->clubId . '_' . $this->contactId : 'workgroup_' . $this->clubId . '_' . $this->contactId), 'teamloginstatus' => array('isVisibleAlways' => 'true', 'title' => $this->get('translator')->trans('LOGIN_STATUS'), 'dataUrl' => ($memberType == 'team') ? $this->container->get('router')->generate('team_loginstatus') : $this->container->get('router')->generate('workgroup_loginstatus'), 'isActive' => 'true', 'localStorageName' => ($memberType == 'team') ? 'team_' . $this->clubId . '_' . $this->contactId : 'workgroup_' . $this->clubId . '_' . $this->contactId)));
                $actionMenuSingleSelectedText = array_merge($actionMenuSingleSelectedTextDummy, array('teamUserRight' => array('title' => $this->get('translator')->trans('USER_RIGHTS_PAGE_TITLE'), 'dataUrl' => ($memberType == 'team') ? $this->generateUrl('team_userrights') : $this->generateUrl('workgroup_userrights'), 'isActive' => 'false', 'localStorageName' => ($memberType == 'team') ? 'team_' . $this->clubId . '_' . $this->contactId : 'workgroup_' . $this->clubId . '_' . $this->contactId),
                    'teamloginstatus' => array('title' => $this->get('translator')->trans('LOGIN_STATUS'), 'dataUrl' => ($memberType == 'team') ? $this->container->get('router')->generate('team_loginstatus') : $this->container->get('router')->generate('workgroup_loginstatus'), 'isActive' => 'false', 'localStorageName' => ($memberType == 'team') ? 'team_' . $this->clubId . '_' . $this->contactId : 'workgroup_' . $this->clubId . '_' . $this->contactId),));
                $actionMenuMultipleSelectedText = array_merge($actionMenuMultipleSelectedTextDummy, array('teamUserRight' => array('title' => $this->get('translator')->trans('USER_RIGHTS_PAGE_TITLE'), 'dataUrl' => ($memberType == 'team') ? $this->generateUrl('team_userrights') : $this->generateUrl('workgroup_userrights'), 'isActive' => 'false', 'localStorageName' => ($memberType == 'team') ? 'team_' . $this->clubId . '_' . $this->contactId : 'workgroup_' . $this->clubId . '_' . $this->contactId),
                    'teamloginstatus' => array('title' => $this->get('translator')->trans('LOGIN_STATUS'), 'dataUrl' => ($memberType == 'team') ? $this->container->get('router')->generate('team_loginstatus') : $this->container->get('router')->generate('workgroup_loginstatus'), 'isActive' => 'false', 'localStorageName' => ($memberType == 'team') ? 'team_' . $this->clubId . '_' . $this->contactId : 'workgroup_' . $this->clubId . '_' . $this->contactId),));
            } else {
                $actionMenuNoneSelectedText = $actionMenuNoneSelectedTextDummy;
                $actionMenuSingleSelectedText = $actionMenuSingleSelectedTextDummy;
                $actionMenuMultipleSelectedText = $actionMenuMultipleSelectedTextDummy;
            }
        } else {
            $actionMenuNoneSelectedText = array('Export' => array('title' => $this->get('translator')->trans('EXPORT'), 'divider' => '1', 'dataUrl' => $this->container->get('router')->generate('export_settings')),
                'sendPersonalMessage' => array('title' => $this->get('translator')->trans('MESSAGE_SENT_PERSONAL'), 'dataUrl' => '', 'hrefLink' => '', 'isActive' => 'false'),
                'sendGroupMessage' => array('title' => $this->get('translator')->trans('MESSAGE_SENT_GROUP'), 'dataUrl' => '', 'hrefLink' => $this->generateUrl('internal_create_message_step1_' . $memberType, array('mr' => $memberId)), 'isActive' => 'true', 'divider' => '1'),
            );
            $actionMenuSingleSelectedText = array('Export' => array('title' => $this->get('translator')->trans('EXPORT'), 'divider' => '1', 'dataUrl' => $this->container->get('router')->generate('export_settings')),
                'sendPersonalMessage' => array('title' => $this->get('translator')->trans('MESSAGE_SENT_PERSONAL'), 'dataUrl' => $this->generateUrl('internal_create_message_step1_contact'), 'isActive' => 'true'),
                'sendGroupMessage' => array('title' => $this->get('translator')->trans('MESSAGE_SENT_GROUP'), 'dataUrl' => '', 'hrefLink' => '', 'isActive' => 'false'),
            );
            $actionMenuMultipleSelectedText = array('Export' => array('title' => $this->get('translator')->trans('EXPORT'), 'divider' => '1', 'dataUrl' => $this->container->get('router')->generate('export_settings')),
                'sendPersonalMessage' => array('title' => $this->get('translator')->trans('MESSAGE_SENT_PERSONAL'), 'dataUrl' => $this->generateUrl('internal_create_message_step1_contact'), 'isActive' => 'true'),
                'sendGroupMessage' => array('title' => $this->get('translator')->trans('MESSAGE_SENT_GROUP'), 'dataUrl' => '', 'hrefLink' => '', 'isActive' => 'false', 'divider' => '1')
            );
        }

        $menuArray = array('none' => $actionMenuNoneSelectedText, 'single' => $actionMenuSingleSelectedText, 'multiple' => $actionMenuMultipleSelectedText, 'adminFlag' => $adminflag);

        return $menuArray;
    }

    /**
     * Templete for confirmation pupup.
     *
     * @param Request $request Request object
     *
     * @return Template
     */
    public function removeconfirmationPopupAction(Request $request)
    {
        $memberIds = $request->get('memberIds');
        $selected = $request->get('selected');
        $teamTitle = $request->get('titleText');
        $type = $request->get('type');
        $roleId = $request->get('roleId');

        if ($memberIds) {
            $memberIdsArray = explode(',', $memberIds);
            if ($selected === 'all') {
                $popupTitle = str_replace('%team%', $teamTitle, $this->get('translator')->trans('MEMBER_DELETE_TITLE_ALL'));
                $popupText = str_replace('%team%', $teamTitle, $this->get('translator')->trans('MEMBER_DELETE_TEXT_ALL'));
            } elseif (count($memberIdsArray) > 1) {
                $popupTitle = str_replace('%team%', $teamTitle, $this->get('translator')->trans('MEMBER_DELETE_TITLE_MULTIPLE'));
                $popupText = str_replace('%team%', $teamTitle, $this->get('translator')->trans('MEMBER_DELETE_TEXT_MULTIPLE'));
            } else {
                $popupTitle = str_replace('%team%', $teamTitle, $this->get('translator')->trans('MEMBER_DELETE_TITLE_SINGLE'));
                $popupText = str_replace('%team%', $teamTitle, $this->get('translator')->trans('MEMBER_DELETE_TEXT_SINGLE'));
            }
        }
        $return = array('title' => $popupTitle, 'text' => $popupText, 'memberIds' => $memberIds, 'teamTitle' => $teamTitle, 'type' => $type, 'roleId' => $roleId);

        return $this->render('InternalTeamBundle:TeamOverview:removeconfirmationPopup.html.twig', $return);
    }

    /**
     * Function to delete a member.
     *
     * @param Request $request Request object
     *
     * @return JsonResponse
     */
    public function deleteMemberAction(Request $request)
    {
        $memberIds = $request->get('memberIds');
        $clubId = $this->container->get('club')->get('id');
        $type = $request->get('type');
        $roleCatId = ($type == 'workgroup') ? $this->container->get('club')->get('club_workgroup_id') : $this->container->get('club')->get('club_team_id');
        $roleId = $request->get('roleId');
        $objMessage = new MessagePdo($this->container);
        $contactId = $this->container->get('contact')->get('id');
        $objMessage->removeTeamMember($memberIds, $roleId, $roleCatId, $clubId, $contactId);

        if (count($memberIds) == 1) {
            return new JsonResponse(array('noparentload' => true, 'status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('REMOVE_TEAM_MEMBER_SUCCESS_SINGLE')));
        } else {
            return new JsonResponse(array('noparentload' => true, 'status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('REMOVE_TEAM_MEMBER_SUCCESS_MULTIPLE')));
        }
    }

    /**
     * Function to remove the fields from the local storage which i don't have visiblity
     *
     * @param object $memberListData The memberData class object
     * @param int    $adminFlag      flag to check whether user is admin
     * @param array  $teamRight      array of team right (MEMBER/ADMIN ..)
     * 
     * @return object
     */
    public function removeInvisibleFields($memberListData, $adminFlag, $teamRight)
    {
        $getAllContactFields = $this->container->get('club')->get('allContactFields');
        $dataTableColumnData = $memberListData->dataTableColumnData;
        $hiddenFields = array();
        foreach ($dataTableColumnData as $key => $columns) {
            if (false !== strstr($columns['data'], 'CF_')) {
                $pop = true;
                $fieldDetails = explode('_', $columns['data']);
                $fieldsVisibilityDetail = $getAllContactFields[$fieldDetails[1]];
                if ($fieldsVisibilityDetail['is_set_privacy_itself'] == 1) {
                    $pop = false;
                } elseif ($fieldsVisibilityDetail['is_visible_teamadmin'] == 1 && $adminFlag) {
                    $pop = false;
                } elseif ($fieldsVisibilityDetail['privacy_contact'] != 'private' && (in_array('MEMBER', $teamRight))) {
                    $pop = false;
                }

                if ($pop) {
                    unset($dataTableColumnData[$key]);  //Will remove the un-privelages column form the data array
                    $hiddenFields[] = $fieldDetails[1]; //Need to sent the columns that needed to be hidden from the column list
                    if ($memberListData->sortColumnValue[0]['column'] == $key) {
                        //Remove the hidden column from the sort column, if exixts
                        $memberListData->sortColumnValue[0]['column'] = 1; //Set with contact name
                        $memberListData->sortColumnValue[0]['dir'] = 'asc';
                    }
                }
            }
        }
        $memberListData->dataTableColumnData = $dataTableColumnData;
        $memberListData->hiddenFields = $hiddenFields;

        return $memberListData;
    }
}
