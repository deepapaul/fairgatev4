<?php

/**
 * membershipListController
 *
 * This controller was created for handling memberships functionalities
 *
 * @package    ClubadminContactBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
namespace Clubadmin\ContactBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Common\UtilityBundle\Controller\FgController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Common\UtilityBundle\Entity\FgCmMembership;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Util\FgPermissions;
use Symfony\Component\HttpFoundation\Request;
use Common\UtilityBundle\Repository\Pdo\membershipPdo;

/**
 * RoleController used for list memberships functionalities
 */
class MembershipListController extends FgController
{

    /**
     * Executes  the membershiplist action
     *
     * Function to listing all the membership of the perticular club, fedration or a sub fedration
     *
     * @return template
     */
    public function membershiplistAction()
    {
        $breadCrumb = array(
            'breadcrumb_data' => array(
                'Contacts' => 'contact',
                'Our Contacts' => 'contact',
                'Membership' => '#'
            ),
            'back' => '#'
        );
        $this->checkMembershipAccessFunction();
        $clubDefaultLang = $this->get('club')->get('club_default_lang');
        $memCatCount = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getClubMembershipCount($this->clubId);
        if ($this->clubType == 'federation') {
            $pageTitle = $this->get('translator')->trans('FEDMEMBERSHIP_CATEGORY_TITLE');
        } else {
            $pageTitle = $this->get('translator')->trans('MEMBERSHIP_CATEGORY_TITLE');
        }
        return $this->render('ClubadminContactBundle:MembershipList:membershiplist.html.twig', array('clubDefaultLang' => $clubDefaultLang, 'clubLanguages' => $this->clubLanguages, 'clubType' => $this->clubType,
                'clubId' => $this->clubId, 'breadCrumb' => $breadCrumb, 'contactId' => $this->contactId, 'clubMemCatCnt' => $memCatCount,
                'pageTitle' => $pageTitle));
    }

    /**
     * Action to get membership category count of current club.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse Membership category count.
     */
    public function getMembershipCatCountAction()
    {
        $memCatCount = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getClubMembershipCount($this->clubId);

        return new JsonResponse($memCatCount);
    }

    /**
     * Action to get data for listing membership.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse Array of Membership List Data.
     */
    public function getMembershipDataAction()
    {
        $this->checkMembershipAccessFunction();
        $objMembershipPdo = new membershipPdo($this->container);
        $membershipDetails = $objMembershipPdo->getMemberships($this->clubType, $this->clubId, $this->subFederationId, $this->federationId, $this->contactId, true, 1);
        return new JsonResponse($membershipDetails);
    }
    /*
     * Action to check Club Membership Available & club type can access membership setting page
     */

    private function checkMembershipAccessFunction()
    {
        $club_mem_available = $this->container->get('club')->get('clubMembershipAvailable');
        if ($this->clubType == "sub_federation") {
            $club_mem_available = 0;
        } elseif ($this->clubType == "standard_club" || $this->clubType == "federation") {
            $club_mem_available = 1;
        }

        if ($club_mem_available == 0) {
            $permissionObj = new FgPermissions($this->container);
            $permissionObj->checkClubAccess(0, 'membershiplist');
        } else {
            return false;
        }
    }

    /**
     * Executes updatemembership action
     *
     * Function to Update membership of the perticular club, fedration or a sub fedration
     *
     * @return template
     */
    public function updatemembershipAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $catArr = json_decode($request->request->get('catArr'), true);
            if (count($catArr) > 0) {
                $result = $this->generatequeryAction('fg_cm_membership', $catArr, $this->clubId);
                $result['type'] = 'membership';

                return new JsonResponse(array('status' => 'SUCCESS', 'noparentload' => true, 'flash' => $this->get('translator')->trans('MEMBERSHIP_UPDATE_SUCCESS'), 'result' => $result));
            }
        }

        return new JsonResponse(array('status' => 'SUCCESS', 'noparentload' => true, 'flash' => $this->get('translator')->trans('MEMBERSHIP_UPDATE_FAILED')));
    }

    /**
     * This is used to create new element from slider bar (Membership cateogry, Role, Team, Function etc)
     *
     * @return template
     */
    public function newElementFromSidebarAction()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $elementType = $request->get('elementType');
        $title = $request->get('value');
        $tableArray = array();
        $valueLogArr = array();
        $title1 = $title;
        $title = '"' . FgUtility::getSecuredData(trim($title), $this->conn, false, false) . '"';
        $addRoleFn = 'role';
        $roleType = '';
        $club = $this->get('club');
        $clubDefaultLang = $this->get('club')->get('club_default_lang');
        if ($elementType == 'membership' || $elementType == 'fed_membership') {
            $itemType = ($elementType == 'membership') ? 'membership' : 'fed_membership';
            $elementType = 'membership';
            $tableName = 'fg_cm_membership';
            $sortValue = $this->getMaxSortOrderTable($tableName, $elementType, $this->clubId);
            $tableArray[$tableName]['fields'] = array('club_id', 'title', 'sort_order');
            $tableArray[$tableName]['values'] = array($this->clubId, $title, $sortValue);
            $this->insertIntoTableSidebar($tableArray);
            $where = "club_id = $this->clubId";
            $lastInsertedId = $this->getLastInsertedId($tableName, $elementType, $where);

            //INSERT LOG ENTRY when new membership created
            $valueLogArr['title'] = FgUtility::getSecuredData(trim($request->get('value')), $this->conn, false, false);
            $logQuery = $this->em->getRepository('CommonUtilityBundle:FgRmCategory')->insertLogEntries($tableName, $lastInsertedId, $valueLogArr, true, $clubDefaultLang, $this->clubId, true, $this->contactId);
            //INSERT LOG ENTRY when new membership created


            $i18TableName = 'fg_cm_membership_i18n';
            $i18tableArray[$i18TableName]['fields'] = array('lang', 'title_lang', 'is_active', 'id');
            $i18tableArray[$i18TableName]['values'] = array($title, 1, $lastInsertedId);
            $i18tableArray[$i18TableName]['languages'] = $this->clubLanguages;
            $this->insertIntoTableSidebar($i18tableArray);
            $input[] = array('id' => "$lastInsertedId", 'title' => $title1, 'categoryId' => "", 'itemType' => $itemType, 'count' => '0', 'bookMarkId' => '', 'type' => 'select', 'draggable' => 1, 'draggableClass' => 'fg-dev-draggable');
            return new JsonResponse(array('input' => $input, 'addToJson' => 1));
        } elseif ($elementType == 'rolecategory' || $elementType == 'fedrolecategory' || $elementType == 'subfedrolecategory') {
            $elementType = 'rolecategory';
            $roleType = $request->get('roleType');
            $tableName = 'fg_rm_category';
            $isFedCategory = 0;
            $assignType = 'manual';
            //for filter roles
            if ($roleType == 'filterrole') {
                $assignType = 'filter-driven';
            }
            if ($roleType == 'fedrole' || $roleType == 'subfedrole') {
                $isFedCategory = 1;
            }
            $whereCond = "AND contact_assign = '$assignType' AND is_team = 0 AND is_workgroup = 0 AND is_fed_category = $isFedCategory";
            $sortValue = $this->getMaxSortOrderTable($tableName, $elementType, $this->clubId, $whereCond);
            //ends
            $tableArray[$tableName]['fields'] = array('club_id', 'title', 'sort_order', 'function_assign', 'is_fed_category', 'contact_assign');
            $noneVal = FgUtility::getSecuredDataString(trim('none'), $this->conn);
            $assignType = FgUtility::getSecuredDataString($assignType, $this->conn);
            $tableArray[$tableName]['values'] = array($this->clubId, $title, $sortValue, '"' . $noneVal . '"', $isFedCategory, '"' . $assignType . '"');
            $this->insertIntoTableSidebar($tableArray);
            $where = "club_id = $this->clubId";
            $lastInsertedId = $this->getLastInsertedId($tableName, $elementType, $where);

            $i18TableName = 'fg_rm_category_i18n';
            $i18tableArray[$i18TableName]['fields'] = array('lang', 'title_lang', 'is_active', 'id');
            $i18tableArray[$i18TableName]['values'] = array($title, 1, $lastInsertedId);
            $i18tableArray[$i18TableName]['languages'] = $this->clubLanguages;
            $this->insertIntoTableSidebar($i18tableArray);
            $input[] = array('id' => "$lastInsertedId", 'title' => $title1, 'type' => 'select');

            return new JsonResponse(array('items' => $input));
        } elseif ($elementType == 'teamcategory') {
            $tableName = 'fg_team_category';
            $whereCond = "";
            $sortValue = $this->getMaxSortOrderTable($tableName, $elementType, $this->clubId, $whereCond);
            $tableArray[$tableName]['fields'] = array('club_id', 'title', 'sort_order');
            $tableArray[$tableName]['values'] = array($this->clubId, $title, $sortValue);
            $this->insertIntoTableSidebar($tableArray);
            $where = "club_id = $this->clubId";
            $lastInsertedId = $this->getLastInsertedId($tableName, $elementType, $where);

            $i18TableName = 'fg_team_category_i18n';
            $i18tableArray[$i18TableName]['fields'] = array('lang', 'title_lang', 'is_active', 'id');
            $i18tableArray[$i18TableName]['values'] = array($title, 1, $lastInsertedId);
            $i18tableArray[$i18TableName]['languages'] = $this->clubLanguages;
            $this->insertIntoTableSidebar($i18tableArray);
            $input[] = array('id' => "$lastInsertedId", 'title' => $title1, 'type' => 'select',);

            return new JsonResponse(array('items' => $input));
        } elseif ($elementType == 'executiveFunction') {
            $getCatAndRoleId = $this->em->getRepository('CommonUtilityBundle:FgRmCategory')->getCatAndRoleId($this->clubId);
            $getCatAndRoleId['is_federation'] = 1;
            $lastInsertedId = $this->sidebarCreateFunction($elementType, $getCatAndRoleId, $title);

            $input[] = array('id' => "$lastInsertedId", 'title' => $title1, 'categoryId' => "ceb_function", 'itemType' => 'FI', 'count' => '0', 'bookMarkId' => '', 'type' => 'select');
            return new JsonResponse(array('input' => $input, 'addToJson' => 1));
        } elseif ($elementType == 'role' || $elementType == 'team' || $elementType == 'workgroup') {
            $roleType = $request->get('roleType');
            $tableName = 'fg_rm_role';
            $isFedCategory = 0;
            if ($roleType == 'fedrole') {
                $isFedCategory = 1;
            }
            $fntitle = '"' . FgUtility::getSecuredData(trim($request->get('fnvalue', '')), $this->conn, false, false) . '"';
            $functionType = $request->get('functionType', '');
            $itemType = ($isFedCategory ? 'FROLES-' : 'ROLES-') . $this->clubId;
            if ($elementType == 'role' || $elementType == 'workgroup') {
                $categoryId = $request->get('category_id');
                if ($elementType == 'workgroup') {
                    $categoryId = $club->get('club_workgroup_id');
                    $itemType = 'WORKGROUP';
                }
                $type = ($elementType == 'role') ? '"G"' : '"W"';
                $whereCond = "AND category_id = $categoryId";
                $tableArray[$tableName]['fields'] = array('category_id', 'club_id', 'title', 'sort_order', 'type');
                $sortValue = $this->getMaxSortOrderTable($tableName, $elementType, $this->clubId, $whereCond);
                $tableArray[$tableName]['values'] = array($categoryId, $this->clubId, $title, $sortValue, $type);
                $where = "club_id = $this->clubId AND category_id = $categoryId";
            } elseif ($elementType == 'team') {
                $itemType = 'TEAM';
                $categoryId = $club->get('club_team_id'); //Club team cat Id
                $teamCategoryId = $request->get('category_id');
                $whereCond = "AND team_category_id = $teamCategoryId AND category_id = $categoryId";
                $tableArray[$tableName]['fields'] = array('category_id', 'club_id', 'team_category_id', 'title', 'sort_order', 'type');
                $sortValue = $this->getMaxSortOrderTable($tableName, $elementType, $this->clubId, $whereCond);
                $tableArray[$tableName]['values'] = array($categoryId, $this->clubId, $teamCategoryId, $title, $sortValue, '"T"');
                $where = "club_id = $this->clubId AND category_id = $categoryId AND team_category_id = $teamCategoryId";
            }
            $this->insertIntoTableSidebar($tableArray);
            $lastInsertedId = $this->getLastInsertedId($tableName, $elementType, $where);
            $i18TableName = 'fg_rm_role_i18n';
            $i18tableArray[$i18TableName]['fields'] = array('lang', 'title_lang', 'is_active', 'id');
            $i18tableArray[$i18TableName]['values'] = array($title, 1, $lastInsertedId);
            $i18tableArray[$i18TableName]['languages'] = $this->clubLanguages;
            $this->insertIntoTableSidebar($i18tableArray);

            //INSERT LOG ENTRY IF IT IS A ROLE CREATION
            $valueLogArr['title'] = FgUtility::getSecuredData(trim($request->get('value')), $this->conn, false, false);
            $this->em->getRepository('CommonUtilityBundle:FgRmCategory')->insertLogEntries($tableName, $lastInsertedId, $valueLogArr, true, $clubDefaultLang, $this->clubId, true, $this->contactId, $roleId);
            //INSERT LOG ENTRY IF IT IS A ROLE CREATION

            if ($functionType == 'same') {
                $functionIdArr = $this->conn->fetchAll("SELECT f.id FROM fg_rm_function f WHERE f.category_id = $categoryId");
                $functionIds = FgUtility::getArrayFlatten($functionIdArr);
                foreach ($functionIds as $k => $fnId) {
                    $tbName = 'fg_rm_role_function';
                    $tbNameArray[$tbName]['fields'] = array('function_id', 'role_id');
                    $tbNameArray[$tbName]['values'] = array($fnId, $lastInsertedId);
                    $this->insertIntoTableSidebar($tbNameArray);
                }
            }
            if ($fntitle != '' && $fntitle != '""') {
                $addRoleFn = 'rolefunction';
                $catAndRoleIdArr['catId'] = $categoryId;
                $catAndRoleIdArr['roleId'] = $lastInsertedId;
                $catAndRoleIdArr['is_federation'] = 0;
                $this->sidebarCreateFunction($elementType, $catAndRoleIdArr, $fntitle);
            }
            $filterData = array();
            $filterData['id'] = $itemType;
            $filterData['title'] = $this->get('translator')->trans('CLASSES');
            $filterData['fixed_options'][0][] = array('id' => '', 'title' => "- " . $this->get('translator')->trans('CL_SELECT_CLASSIFICATION') . " -");
            $filterData['fixed_options'][1][] = array('id' => 'any', 'title' => $this->get('translator')->trans('CL_ANY_CLASS'));
            $filterData['fixed_options'][1][] = array('id' => '', 'title' => $this->get('translator')->trans('CL_SELECT_CLASS'));
            $input = array('0' => array('id' => "$lastInsertedId", 'title' => $title1, 'categoryId' => ($elementType == 'team' ? $teamCategoryId : "$categoryId"), 'itemType' => $itemType, 'count' => '0', 'bookMarkId' => '', 'type' => 'select', 'filterData' => $filterData, 'draggable' => 1, 'draggableClass' => 'fg-dev-draggable'));

            return new JsonResponse(array('input' => $input));
        }

        $mainElementId = $elementType . 'id_' . $lastInsertedId;
        if ($elementType == 'role' || $elementType == 'team' || $elementType == 'workgroup') {
            $replaceElementSidebar = $this->render('ClubadminContactBundle:MembershipList:common-replace-element-sidebar.html.twig', array('mainElementId' => $mainElementId, 'parentElementId' => $categoryId, 'elementId' => $lastInsertedId, 'elementType' => $elementType, 'elementTitle' => $request->get('value')))->getContent();
        } else {
            $replaceElementSidebar = $this->render('ClubadminContactBundle:MembershipList:' . $elementType . '_replace-element-sidebar.html.twig', array('mainElementId' => $mainElementId, 'elementId' => $lastInsertedId, 'elementType' => $elementType, 'elementTitle' => $request->get('value'), 'roleType' => $roleType))->getContent();
        }
        if ($roleType != '') {
            return new JsonResponse(array('replaceElementSidebar' => $replaceElementSidebar, 'elementId' => $lastInsertedId, 'addRoleFn' => $addRoleFn, 'roleType' => $roleType));
        } else {
            return new JsonResponse(array('replaceElementSidebar' => $replaceElementSidebar, 'elementId' => $lastInsertedId, 'addRoleFn' => $addRoleFn));
        }
    }

    /**
     * function to add the sidebar
     *
     * @param string $elementType     the type to know from which category
     * @param array  $getCatAndRoleId the array of category id,federation id ,role id
     * @param string $title           the role title
     *
     * @return Integer $lastInsertedId
     */
    public function sidebarCreateFunction($elementType, $getCatAndRoleId, $title)
    {
        $tableName = 'fg_rm_function';
        $clubDefaultLang = $this->get('club')->get('club_default_lang');
        if ($elementType != 'executiveFunction') {
            $sortValue = 1;
        } else {
            $sortValue = $this->getMaxSortOrderTable($tableName, $elementType, $getCatAndRoleId['catId']);
        }
        $tableArray[$tableName]['fields'] = array('category_id', 'title', 'sort_order', 'is_federation');
        $tableArray[$tableName]['values'] = array($getCatAndRoleId['catId'], $title, $sortValue, $getCatAndRoleId['is_federation']);

        $this->insertIntoTableSidebar($tableArray);
        $where = "category_id=" . $getCatAndRoleId['catId'];
        $lastInsertedId = $this->getLastInsertedId($tableName, $elementType, $where);

        $i18TableName = 'fg_rm_function_i18n';
        $i18tableArray[$i18TableName]['fields'] = array('lang', 'title_lang', 'is_active', 'id');
        $i18tableArray[$i18TableName]['values'] = array($title, 1, $lastInsertedId);
        $i18tableArray[$i18TableName]['languages'] = $this->clubLanguages;
        $this->insertIntoTableSidebar($i18tableArray);

        //INSERT LOG ENTRY IF IT IS A FUNCTION CREATION
        $valueLogArr['title'] = trim($title, '"');
        $roleId = $getCatAndRoleId['roleId'];
        $functionLogQuery = $this->em->getRepository('CommonUtilityBundle:FgRmCategory')->insertLogEntries($tableName, $lastInsertedId, $valueLogArr, true, $clubDefaultLang, $this->clubId, true, $this->contactId, $roleId);
        //INSERT LOG ENTRY IF IT IS A FUNCTION CREATION

        $tbName = 'fg_rm_role_function';
        $tbNameArray[$tbName]['fields'] = array('function_id', 'role_id');
        $tbNameArray[$tbName]['values'] = array($lastInsertedId, $getCatAndRoleId['roleId']);
        $this->insertIntoTableSidebar($tbNameArray);
        return $lastInsertedId;
    }

    /**
     * Executes log listing
     *
     * Function to propagate data for membership page
     * @return json
     */
    public function logDataAction(Request $request)
    {
        $type = $request->get('type', '');
        $logType = '';
        $from = '';
        $functionId = 0;
        $startdate = $request->get('startdate', '');
        $enddate = $request->get('enddate', '');
        if ($type == 'membership') {
            $membershipId = $request->get('membershipId', '0');
            $logType = 'membership';
            $logTabs = array('1' => 'assignments', '2' => 'data');
        }

        $hierarchyClubIds = array();
        $hierarchyClubIdArr = array();
        if (!in_array($this->clubType, array('federation_club', 'sub_federation_club'))) {
            $clubPdo = new \Admin\UtilityBundle\Repository\Pdo\ClubPdo($this->container);
            $resultClubs = $clubPdo->getHierarchyClubs();            
            foreach ($resultClubs as $resultClub) {
                $hierarchyClubIds[] = $resultClub['id'];
                $hierarchyClubIdArr[$resultClub['id']] = $resultClub['title'];
            }
        }
        if ($logType == 'membership') {
            //  $membershipLogDetails = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getMembershipLog($this->clubId, $membershipId, $hierarchyClubIds);
            $membershipLogDetails = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getMembershipLog($this->clubId, $membershipId, 0);
        }
        $jsonData = array('logdisplay' => $membershipLogDetails, 'hierarchyClubIdArr' => $hierarchyClubIdArr, 'logTabs' => $logTabs);
        return new JsonResponse($jsonData);
    }
}
