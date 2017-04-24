<?php

/**
 * FgRmRoleContactRepository
 *
 * This class is used for contact assignments in contact administration.
 */

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Util\FgLogHandler;

/**
 * FgRmRoleContactRepository
 *
 * This class is used for getting contact assignments and
 * for adding and removing contact assignments.
 */
class FgRmRoleContactRepository extends EntityRepository
{
    private $asgmntLogFields = "(`contact_id`,`category_club_id`,`role_type`,`category_id`,`role_id`,`function_id`,`date`,`category_title`,`value_before`,`value_after`,`changed_by`)";
    private $asgmntLogValues = array();
    private $roleLogFields = "(`club_id`,`role_id`,`date`,`kind`,`field`,`value_before`,`value_after`,`changed_by`)";
    private $roleLogValues = array();
    private $clubLogFields = "(`club_id`,`date`,`kind`,`field`,`value_before`,`value_after`,`changed_by`,`rolelog_id`)";
    private $clubLogValues = array();
    private $functionLogFields = "(`club_id`,`role_id`,`function_id`,`date`,`kind`,`field`,`value_before`,`value_after`,`changed_by`)";
    private $functionLogValues = array();
    private $contAssignmentQry = "";
    private $deleteAssignmentQry = "";
    private $updateContactChangeDates = array();
    private $crfInsertQry = "";
    private $crfRoleIds = array();
    private $crfInsertFields = "(`category_id`,`role_id`,`function_id`,`club_id`)";
    private $crfInsertValues = array();
    private $importAsigmntQry = "";
    private $logEntriesQry = "";
    private $clubLogQry = "";
    private $teamRosterQry = "";

    /**
     * Function to add or delete contact assignments
     *
     * @param array   $catArr                  Assignments array
     * @param int     $clubId                  Current club id
     * @param array   $contactIdArr            Contact ids array
     * @param int     $currentContactId        Current contact id
     * @param object  $clubService             Club service
     * @param object  $terminologyService      Terminology service
     * @param object  $container               Container
     * @param array   $translationsArray       Translations Array
     * @param string  $currentDate             Date to be set as current date
     * @param int     $insertRoleFunctionLog   Flag to determine whether role, function log is to be inserted or not
     * @param boolean $fromRemoveFedMembership Flag to determine whether function is invoked from remove fed membership
     *
     * @return array $resultArray Array containing errors if any
     */
    public function updateContactAssignments($catArr, $clubId, $contactIdArr, $currentContactId, $clubService, $terminologyService, $container, $translationsArray = array(), $currentDate = '', $insertRoleFunctionLog = 1, $fromRemoveFedMembership = false, $contactType = 'contact')
    {
        $this->asgmntLogValues = array();
        $this->roleLogValues = array();
        $this->clubLogValues = array();
        $this->functionLogValues = array();
        $this->contAssignmentQry = "";
        $this->deleteAssignmentQry = "";
        $this->updateContactChangeDates = array();
        $this->crfInsertQry = "";
        $this->crfRoleIds = array();
        $this->crfInsertValues = array();
        $this->importAsigmntQry = "";
        $this->logEntriesQry = "";
        $this->clubLogQry = "";
        $this->teamRosterQry = "";

        if (count($catArr) > 0) {
            $conn = $this->getEntityManager()->getConnection();
            $currentDate = ($currentDate != '') ? $currentDate : date('Y-m-d H:i:s');
            $contactIdStr = FgUtility::getSecuredData(implode(',', $contactIdArr), $conn);
            $clubId = intval($clubId);
            $clubWorkgroupId = $clubService->get('club_workgroup_id');
            $clubExecutiveBoardId = $clubService->get('club_executiveboard_id');
            $clubWrkgrpId = $clubService->get('club_workgroup_id');
            $clubExecBrdId = $clubService->get('club_executiveboard_id');
            $currClubId = $clubService->get('id');
            $currFederationId = $clubService->get('federation_id');
            $currSubFederationId = $clubService->get('sub_federation_id');
            $clubType = $clubService->get('type');
            $hasError = false;
            $errorType = '';
            $errorArray = array();
            $contactIdArrNew = array();

            //Get role ids and function ids of all categories.
            $catIdsArr = array();
            $contactIdsForQuery = '';
            $seperator = '';
            foreach ($catArr as $contactId => $catArray) {
                foreach ($catArray as $catId => $catArrDetail) {
                    $catIdsArr[] = $catId;
                }
            }

            // Checking whether the category array contains federation and subfederation categories
            $catIdStr = implode(',', $catIdsArr);
            $catIdContactArray = $this->_em->getRepository('CommonUtilityBundle:FgRmCategory')->checkFederationCategory($catIdStr);
            $newCatArray = array();

            // Generating new array with fedcontact is and subfed contact id for corresponding fed and subfed categories
            foreach ($catArr as $conId => $conArray) {
                $contactObj = $this->_em->find('CommonUtilityBundle:FgCmContact', $conId);
                foreach ($conArray as $conArrayKey => $conArrayVal) {

                    if (array_key_exists($conArrayKey, $catIdContactArray['federation'])) {
                        $newCatArray[$contactObj->getFedContact()->getId()][$conArrayKey] = $conArrayVal;
                        $contactIdArrNew[$contactObj->getFedContact()->getId()] = $conId;
                    } else if (array_key_exists($conArrayKey, $catIdContactArray['sub_federation'])) {
                        $newCatArray[$contactObj->getSubfedContact()->getId()][$conArrayKey] = $conArrayVal;
                        $contactIdArrNew[$contactObj->getSubfedContact()->getId()] = $conId;
                    } else {
                        $newCatArray[$conId][$conArrayKey] = $conArrayVal;
                        $contactIdArrNew[$conId] = $conId;
                    }
                }
            }

            $catArr = $newCatArray;
            foreach ($catArr as $contactId => $catArray) {
                $contactIdsForQuery .= $seperator.$contactId;
                $seperator = ',';
            }

            if (count($catIdsArr) > 0) {
                //Get club ids and federation membership ids of contacts.
                $contactDetails = $conn->fetchAll("SELECT c.`id`, c.`club_id`, c.`fed_contact_id`, c.`subfed_contact_id`, m.`id` AS membership_cat_id "
                        . "FROM `fg_cm_contact` c LEFT JOIN `fg_cm_membership` m ON (m.id=c.fed_membership_cat_id AND c.fed_membership_cat_id IS NOT NULL AND c.fed_membership_cat_id != '') "
                        . "WHERE c.id IN ($contactIdsForQuery) GROUP BY c.id");

                $contactClubIds = array();
                $federationMembers = array();
                foreach ($contactDetails as $contactDetail) {
                    $contactClubIds[$contactDetail['id']] = $contactDetail['club_id'];
                    $fedContactIds[$contactDetail['id']] = $contactDetail['fed_contact_id'];
                    $subFedContatIds[$contactDetail['id']] = $contactDetail['subfed_contact_id'];
                    if ($contactDetail['membership_cat_id'] != '') {
                        $federationMembers[] = $contactDetail['id'];
                    }
                }

                //Get contact names.
                $contactNames = $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->getContactName($contactIdArr, $conn, $clubService, $container, $contactType, $fromRemoveFedMembership);

                $fedWrkgrpCatId = 0;
                $fedWrkgrpRoleId = 0;
                if (($currFederationId > 0) && in_array($clubId, array($currClubId, $currSubFederationId))) {
                    $fedWrkgrpCatRoleIds = $this->_em->getRepository('CommonUtilityBundle:FgRmCategory')->getExecutiveBoardRoleCatIds($currFederationId);
                    $fedWrkgrpCatId = $fedWrkgrpCatRoleIds['catId'];
                    $fedWrkgrpRoleId = $fedWrkgrpCatRoleIds['roleId'];
                    if (in_array($fedWrkgrpCatId, $catIdsArr)) {
                        $catIdsArr[] = $clubWorkgroupId;
                    }
                }

                // On deleting sub-federation assignments from club level (on removing federation membership of contact)
                if ($clubId == $currSubFederationId) {
                    $subfedWrkgrpCatRoleIds = $this->_em->getRepository('CommonUtilityBundle:FgRmCategory')->getExecutiveBoardRoleCatIds($currSubFederationId);
                    $clubWorkgroupId = $subfedWrkgrpCatRoleIds['catId'];
                    $clubExecutiveBoardId = $subfedWrkgrpCatRoleIds['roleId'];
                    $clubWrkgrpId = $clubWorkgroupId;
                    $clubExecBrdId = $clubExecutiveBoardId;
                } else if ($clubId == $currFederationId) {
                    $fedWrkgpCatRoleIds = $this->_em->getRepository('CommonUtilityBundle:FgRmCategory')->getExecutiveBoardRoleCatIds($currFederationId);
                    $clubWrkgrpId = $fedWrkgpCatRoleIds['catId'];
                    $clubExecBrdId = $fedWrkgpCatRoleIds['roleId'];
                }

                //Remove category id from array if team category.
                $hasTeamCat = false;
                foreach ($catIdsArr as $catIdVal) {
                    if (strpos($catIdVal, 'team') !== false) {
                        $hasTeamCat = true;
                        unset($catIdsArr[array_search($catIdVal, $catIdsArr)]);
                    }
                }

//                //Get team roster ids.
                if ($hasTeamCat) {
                    $catIdsArr[] = $clubService->get('club_team_id');
//                    //get team rosters for adding or removing assignments
//                    $teamRosters = $conn->fetchAll("SELECT a.`id` AS rosterId, a.`team_id` AS teamId, a.`is_default` AS isDefault "
//                            . "FROM `fg_team_roster_category` a "
//                            . "WHERE a.club_id=$clubId AND a.category_type='TEAM'");
//                    $teamRosterIds = array();
//                    $teamDefaultRosterIds = array();
//                    foreach ($teamRosters as $teamRoster) {
//                        $teamRosterIds[$teamRoster['teamId']][] = $teamRoster['rosterId'];
//                        if ($teamRoster['isDefault'] == '1') {
//                            $teamDefaultRosterIds[$teamRoster['teamId']] = $teamRoster['rosterId'];
//                        }
//                    }
                }

                //Get details of roles and functions of selected categories.
                $catIds = FgUtility::getSecuredData(implode(',', $catIdsArr), $conn);
                $roleFunctionDetails = $conn->fetchAll("SELECT c.`id` AS category_id, c.`club_id` AS catClubId, c.`title` AS catTitle, c.`is_fed_category` AS isFedCategory, c.`role_assign` AS roleAssign, c.`is_team` AS isTeam, c.`is_workgroup` AS isWrkgrp"
                        . ", GROUP_CONCAT(r.`id`) AS roleIds, GROUP_CONCAT(f.`id`) AS functionIds, GROUP_CONCAT(r.`title` SEPARATOR '#*ROLE*NAME*#') AS roleNames, GROUP_CONCAT(f.`title` SEPARATOR '#*FUN*NAME*#') AS functionNames "
                        . "FROM `fg_rm_category` c "
                        . "LEFT JOIN `fg_rm_role` r ON (r.category_id = c.id) "
                        . "LEFT JOIN `fg_rm_function` f ON (f.category_id = c.id) "
                        . "WHERE c.id IN ($catIds) AND c.contact_assign = 'manual' "
                        . "GROUP BY c.id");

                /* Generate arrays for getting the details of roles and functions of selected categories - starts */
                $catRoleFunctionDetails = array();
                $categoryTitles = array();
                $roleTitles = array();
                $functionTitles = array();
                $clubExecBoardFunctionIds = array();
                foreach ($roleFunctionDetails as $roleFunctionDetail) {
                    $catId = $roleFunctionDetail['category_id'];
                    if ($catId == $fedWrkgrpCatId) {
                        $catId = $clubWorkgroupId;
                        $roleFunctionDetail['catClubId'] = $currClubId;
                    }
                    $catRoleFunctionDetails[$catId]['club_id'] = $roleFunctionDetail['catClubId'];
                    $catRoleFunctionDetails[$catId]['is_fed_category'] = $roleFunctionDetail['isFedCategory'];
                    $catRoleFunctionDetails[$catId]['role_assign'] = $roleFunctionDetail['roleAssign'];
                    $catRoleFunctionDetails[$catId]['cat_type'] = 'R';
                    if ($roleFunctionDetail['isTeam']) {
                        $catRoleFunctionDetails[$catId]['cat_type'] = 'T';
                    } else if ($roleFunctionDetail['isWrkgrp']) {
                        $catRoleFunctionDetails[$catId]['cat_type'] = 'W';
                    }
                    $roleIds = explode(',', $roleFunctionDetail['roleIds']);
                    $roleNames = explode('#*ROLE*NAME*#', $roleFunctionDetail['roleNames']);
                    $functionIds = ($roleFunctionDetail['functionIds'] != '') ? explode(',', $roleFunctionDetail['functionIds']) : array();
                    $functionNames = ($roleFunctionDetail['functionNames'] != '') ? explode('#*FUN*NAME*#', $roleFunctionDetail['functionNames']) : array();
                    if (($catId == $clubWorkgroupId) && ($currFederationId > 0)) { //add club-executive-board functions also
                        $roleIds[] = $clubExecutiveBoardId;
                        $roleNames[] = ucfirst($terminologyService->getTerminology('Executive Board', $container->getParameter('singular')));
                        $clubExecBoardFunctions = $this->_em->getRepository('CommonUtilityBundle:FgRmFunction')->getClubExecBoardFunctionIds($currFederationId, $conn, true);
                        $clubExecBoardFunctionIds = array_keys($clubExecBoardFunctions);
                        if ((count($functionIds) > 0) && (count($clubExecBoardFunctionIds) > 0)) {
                            $functionIds = array_merge($functionIds, $clubExecBoardFunctionIds);
                            $functionNames = array_merge($functionNames, $clubExecBoardFunctions);
                        } else {
                            $functionIds = (count($functionIds) > 0) ? $functionIds : $clubExecBoardFunctionIds;
                            $functionNames = (count($functionNames) > 0) ? $functionNames : $clubExecBoardFunctions;
                        }
                    }
                    $roles = isset($catRoleFunctionDetails[$catId]['roles']) ? array_merge($catRoleFunctionDetails[$catId]['roles'], array_unique($roleIds)) : array_unique($roleIds);
                    $catRoleFunctionDetails[$catId]['roles'] = $roles;
                    $functionTitles[$catId] = count($functionIds) ? array_combine($functionIds, $functionNames) : array();
                    $functions = isset($catRoleFunctionDetails[$catId]['functions']) ? array_merge($catRoleFunctionDetails[$catId]['functions'], array_unique($functionIds)) : array_unique($functionIds);
                    $catRoleFunctionDetails[$catId]['functions'] = $functions;
                    if ($roleFunctionDetail['isTeam']) {
                        $categoryName = ucfirst($terminologyService->getTerminology('Team', $container->getParameter('plural'), $clubId));
                        $categoryTitles[$catId] = $categoryName;
                    } else if ($roleFunctionDetail['isWrkgrp']) {
                        $categoryTitles[$catId] = isset($translationsArray['workgroup']) ? $translationsArray['workgroup'] : $roleFunctionDetail['catTitle'];
                    } else {
                        $categoryTitles[$catId] = $roleFunctionDetail['catTitle'];
                    }
                    $roleTitles[$catId] = array_combine($roleIds, $roleNames);
                    if ($catId == $clubWrkgrpId) {
                        if (isset($roleTitles[$catId][$clubExecBrdId])) {
                            $roleTitles[$catId][$clubExecBrdId] = ucfirst($terminologyService->getTerminology('Executive Board', $container->getParameter('singular'), $clubId));
                        }
                    }
                }
                /* Generate arrays for getting the details of roles and functions of selected categories - ends */

                //Get cat-role-func unique id for given category ids.
                $crfRoles = $conn->fetchAll("SELECT `category_id`, `role_id`, `club_id` "
                        . "FROM `fg_rm_category_role_function` "
                        . "WHERE `category_id` IN ($catIds) AND `function_id` IS NULL");

                foreach ($crfRoles as $crfRole) {
                    $catId = $crfRole['category_id'];
                    $this->crfRoleIds[$catId][$crfRole['club_id']][] = $crfRole['role_id'];
                }

                //Get already existing assignments of contacts.
                $contCurrAsgmnts = $conn->fetchAll("SELECT rc.contact_id AS contactId, crf.category_id AS categoryId, crf.role_id AS roleId, crf.function_id AS functionId, c.role_assign, c.contact_assign "
                        . "FROM `fg_rm_role_contact` rc "
                        . "LEFT JOIN `fg_rm_category_role_function` crf ON (crf.id=rc.fg_rm_crf_id) "
                        . "LEFT JOIN `fg_rm_category` c ON (c.id=crf.category_id) "
                        . "WHERE rc.contact_id IN (SELECT cc.id FROM fg_cm_contact cc WHERE cc.fed_contact_id IN(SELECT fcm.fed_contact_id FROM fg_cm_contact fcm WHERE fcm.id IN ($contactIdStr)))");

                //Generate array for existing assignments and single assignments.
                $existingAsgmnts = array();
                $noMultiAssignmentsOfContact = array();
                foreach ($contCurrAsgmnts as $contCurrAsgmnt) {
                    if (($contCurrAsgmnt['functionId'] == '') || ($contCurrAsgmnt['functionId'] == NULL)) {
                        $existingAsgmnts[$contCurrAsgmnt['contactId']][$contCurrAsgmnt['categoryId']][$contCurrAsgmnt['roleId']] = '1';
                    } else {
                        $existingAsgmnts[$contCurrAsgmnt['contactId']][$contCurrAsgmnt['categoryId']][$contCurrAsgmnt['roleId']][$contCurrAsgmnt['functionId']] = '1';
                    }
                    if (($contCurrAsgmnt['role_assign'] == 'single') && ($contCurrAsgmnt['contact_assign'] == 'manual')) {
                        $noMultiAssignmentsOfContact[$contCurrAsgmnt['contactId']][$contCurrAsgmnt['categoryId']]['roleid'] = $contCurrAsgmnt['roleId'];
                        $noMultiAssignmentsOfContact[$contCurrAsgmnt['contactId']][$contCurrAsgmnt['categoryId']]['functionids'][] = $contCurrAsgmnt['functionId'];
                    }
                }

                $addRosterAssignments = array();
                $removeRosterAssignments = array();
                $sidebarArray=array();
                foreach ($catArr as $contactId => $catArray) {
                    foreach ($catArray as $catId => $catArrDetail) {

                        if (strpos($catId, 'team') !== false) {
                            $catId = $clubService->get('club_team_id');
                            $sidebarArray[$contactId]['cat_type']='team';
                        } else if ($catId == $fedWrkgrpCatId) {
                            $catId = $clubWorkgroupId;

                        }
                        if ($clubService->get('club_workgroup_id') == $catId) {
                            $sidebarArray[$contactId]['cat_type']='workgroup';
                        }

                        $isFederationCat = $catRoleFunctionDetails[$catId]['is_fed_category'];
                        $roleAssign = $catRoleFunctionDetails[$catId]['role_assign'];
                        $catClubId = $catRoleFunctionDetails[$catId]['club_id'];
                        $catType = $catRoleFunctionDetails[$catId]['cat_type'];
                        if ($isFederationCat) {
                            $sidebarArray[$contactId]['cat_type']='federation';
                            $sidebarArray[$contactId]['cat_clubId']=$catClubId;

                        }
                        //If federation role category check whether contact is federation member.
                        if ($isFederationCat && !in_array($contactId, $federationMembers)) {
                            $hasError = true;
                            $errorType = 'NOT_FED_MEMBER';
                            continue;
                        } else {
                            if (isset($catArrDetail['role'])) {
                                foreach ($catArrDetail['role'] as $roleId => $roleArrDetail) {

                                    if ($roleId == $fedWrkgrpRoleId) {
                                        $roleId = $clubExecutiveBoardId;
                                        $sidebarArray[$contactId]['type']='executive';
                                    }
                                    $doInsert = false;
                                    $doDelete = false;
                                    //Check whether roles and functions of category are correct.
                                    if (!in_array($roleId, $catRoleFunctionDetails[$catId]['roles'])) {
                                        $hasError = true;
                                        $errorType = 'NOT_VALID_ROLE';
                                        continue;
                                    } else {

                                        $sidebarArray[$contactId]['category_id']=$catId;
                                        $sidebarArray[$contactId]['subcategory_id']=$roleId;

                                        //If category have functions.
                                        if (isset($roleArrDetail['function'])) {
                                            foreach ($roleArrDetail['function'] as $functionId => $functionArrDetail) {
                                                $doInsert = false;
                                                $doDelete = false;
                                                //Check whether function is valid.
                                                if (!in_array($functionId, $catRoleFunctionDetails[$catId]['functions'])) {
                                                    $hasError = true;
                                                    $errorType = 'NOT_VALID_FUNCTION';
                                                    continue;
                                                }
                                                if (isset($functionArrDetail['is_new'])) {
                                                    $sidebarArray[$contactId]['change_type']='add';
                                                    $sidebarArray[$contactId]['function_id']=$functionId;
                                                    $newSidebarArray[$contactId]['new']=$sidebarArray[$contactId];
                                                    $doInsert = true;
                                                }
                                                if (isset($functionArrDetail['is_deleted'])) {
                                                    $doDelete = true;
                                                    $sidebarArray[$contactId]['change_type']='remove';
                                                    $sidebarArray[$contactId]['function_id']=$functionId;
                                                    $newSidebarArray[$contactId]['delete']=$sidebarArray[$contactId];
                                                }
                                                if ($doInsert) {
                                                    $assignedFunctions = array();
                                                    $assignedCatIds = isset($noMultiAssignmentsOfContact[$contactId]) ? array_keys($noMultiAssignmentsOfContact[$contactId]) : array();

                                                    //If no-multiple-assignment category, check whether assignment already exists.
                                                    if (in_array($catId, $assignedCatIds)) {
                                                        if ($roleId == $noMultiAssignmentsOfContact[$contactId][$catId]['roleid']) {
                                                            $assignedFunctions = $noMultiAssignmentsOfContact[$contactId][$catId]['functionids'];
                                                        } else {
                                                            $multiAsgnmntError = $this->checkMultiAssignmentError($catArrDetail, $noMultiAssignmentsOfContact, $contactId, $catId);
                                                            if ($multiAsgnmntError) {
                                                                $hasError = true;
                                                                $errorType = 'NO_MULTI_ASSIGNMENT_POSSIBLE';
                                                                $errorArray[$catId] = $roleId;
                                                                continue;
                                                            }
                                                        }
                                                    }
                                                }
                                                $generateQry = true;
                                                if ($doInsert && in_array($functionId, $assignedFunctions)) {
                                                    $generateQry = false;
                                                }
                                                if ($doInsert) {
                                                    $isAsgmntToDelete = isset($catArr[$contactId][$catId]['role'][$roleId]['function'][$functionId]['is_deleted']) ? $catArr[$contactId][$catId]['role'][$roleId]['function'][$functionId]['is_deleted'] : '0';
                                                    if (isset($existingAsgmnts[$contactId][$catId][$roleId][$functionId]) && (($isAsgmntToDelete == 0) || ($isAsgmntToDelete == '0'))) {
                                                        $generateQry = false;
                                                    }
                                                }
                                                if ($generateQry) {
                                                    if ($doInsert && ($roleAssign == 'single')) {
                                                        $noMultiAssignmentsOfContact[$contactId][$catId]['roleid'] = $roleId;
                                                        $noMultiAssignmentsOfContact[$contactId][$catId]['functionids'][] = $functionId;
                                                    }
                                                    if (!$hasError) {
                                                        if ($doInsert) {
                                                            $this->generateAssignmentsQuery($clubId, $contactId, $catId, $roleId, $functionId, $doInsert, false, $contactClubIds, $categoryTitles, $roleTitles[$catId], $functionTitles[$catId], $clubExecBoardFunctionIds, $contactNames, $currentDate, $currentContactId, $conn, $clubExecutiveBoardId, $isFederationCat, $fedWrkgrpRoleId, $catClubId, $catType, $currFederationId, $currSubFederationId, $fedContactIds, $subFedContatIds, $container, $clubType, $contactIdArrNew);
                                                        }
                                                        if ($doDelete) {
                                                            $this->generateAssignmentsQuery($clubId, $contactId, $catId, $roleId, $functionId, false, $doDelete, $contactClubIds, $categoryTitles, $roleTitles[$catId], $functionTitles[$catId], $clubExecBoardFunctionIds, $contactNames, $currentDate, $currentContactId, $conn, $clubExecutiveBoardId, $isFederationCat, $fedWrkgrpRoleId, $catClubId, $catType, $currFederationId, $currSubFederationId, $fedContactIds, $subFedContatIds, $container, $clubType, $contactIdArrNew);
                                                        }
                                                    }
                                                    if ($doInsert) {
                                                        /* Adding team roster assignment starts */
                                                        if (array_key_exists($roleId, $teamDefaultRosterIds)) {
                                                            $rosterId = $teamDefaultRosterIds[$roleId];
                                                            $addRosterAssignments[] = "($rosterId, $contactId)";
                                                        }
                                                        /* Adding team roster assignment ends */
                                                    }
                                                    if ($doDelete) {
                                                        /* Removing team roster assignment starts */
                                                        if (array_key_exists($roleId, $teamRosterIds)) {
                                                            foreach ($teamRosterIds[$roleId] as $rosterId) {
                                                                $removeRosterAssignments[] = "($rosterId, $contactId)";
                                                            }
                                                        }
                                                        /* Removing team roster assignment ends */
                                                    }
                                                }
                                            }
                                        } else {
                                            //If category have no functions.
                                            $functionId = NULL;
                                            if (isset($roleArrDetail['is_new'])) {
                                                $doInsert = true;
                                                $sidebarArray[$contactId]['change_type']='add';
                                                $newSidebarArray[$contactId]['new']=$sidebarArray[$contactId];
                                            }
                                            if (isset($roleArrDetail['is_deleted'])) {
                                                $doDelete = true;
                                                $sidebarArray[$contactId]['change_type']='remove';
                                                $newSidebarArray[$contactId]['delete']=$sidebarArray[$contactId];
                                            }
                                            if ($doInsert) {
                                                $assignedRole = 0;
                                                $assignedCatIds = isset($noMultiAssignmentsOfContact[$contactId]) ? array_keys($noMultiAssignmentsOfContact[$contactId]) : array();
                                                //If no-multiple-assignment category, check whether assignment already exists.
                                                if (in_array($catId, $assignedCatIds)) {
                                                    if ($roleId == $noMultiAssignmentsOfContact[$contactId][$catId]['roleid']) {
                                                        $assignedRole = $roleId;
                                                    } else {
                                                        $multiAsgnmntError = $this->checkMultiAssignmentError($catArrDetail, $noMultiAssignmentsOfContact, $contactId, $catId);
                                                        if ($multiAsgnmntError) {
                                                            $hasError = true;
                                                            $errorType = 'NO_MULTI_ASSIGNMENT_POSSIBLE';
                                                            $errorArray[$catId] = $roleId;
                                                            continue;
                                                        }
                                                    }
                                                }
                                            }
                                            $generateQry = true;
                                            if ($doInsert && ($roleId == $assignedRole)) {
                                                $generateQry = false;
                                            }
                                            if ($doInsert) {
                                                $isAsgmntToDelete = isset($catArr[$contactId][$catId]['role'][$roleId]['is_deleted']) ? $catArr[$contactId][$catId]['role'][$roleId]['is_deleted'] : '0';
                                                if (isset($existingAsgmnts[$contactId][$catId][$roleId]) && (($isAsgmntToDelete == 0) || ($isAsgmntToDelete == '0'))) {
                                                    $generateQry = false;
                                                }
                                            }
                                            if ($generateQry) {
                                                if ($doInsert && ($roleAssign == 'single')) {
                                                    $noMultiAssignmentsOfContact[$contactId][$catId]['roleid'] = $roleId;
                                                    $noMultiAssignmentsOfContact[$contactId][$catId]['functionids'] = array();
                                                }
                                                if (!$hasError) {
                                                    if ($doInsert) {
                                                        $this->generateAssignmentsQuery($clubId, $contactId, $catId, $roleId, $functionId, $doInsert, false, $contactClubIds, $categoryTitles, $roleTitles[$catId], $functionTitles[$catId], $clubExecBoardFunctionIds, $contactNames, $currentDate, $currentContactId, $conn, $clubExecutiveBoardId, $isFederationCat, $fedWrkgrpRoleId, $catClubId, $catType, $currFederationId, $currSubFederationId, $fedContactIds, $subFedContatIds, $container, $clubType, $contactIdArrNew);
                                                    }
                                                    if ($doDelete) {
                                                        $this->generateAssignmentsQuery($clubId, $contactId, $catId, $roleId, $functionId, false, $doDelete, $contactClubIds, $categoryTitles, $roleTitles[$catId], $functionTitles[$catId], $clubExecBoardFunctionIds, $contactNames, $currentDate, $currentContactId, $conn, $clubExecutiveBoardId, $isFederationCat, $fedWrkgrpRoleId, $catClubId, $catType, $currFederationId, $currSubFederationId, $fedContactIds, $subFedContatIds, $container, $clubType, $contactIdArrNew);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                if (!$hasError) {
                    /* Delete assignment starts */
                    if ($this->deleteAssignmentQry != "") {
                        $this->deleteAssignmentQry .= ");";
                        $conn->executeQuery($this->deleteAssignmentQry);
                    }
//                    //Remove roster assignment.
//                    if (count($removeRosterAssignments)) {
//                        $removeRosterValues = implode(',', $removeRosterAssignments);
//                        $conn->executeQuery("DELETE FROM `fg_team_roster_category_contacts` WHERE (`roster_id`,`contact_id`) IN ($removeRosterValues)");
//                    }
//                    /* Delete assignment ends */

                    /* Save assignment starts */
                    if ($this->crfInsertQry != "") {
                        $this->crfInsertQry .= " ON DUPLICATE KEY UPDATE `category_id`=VALUES(`category_id`), `role_id`=VALUES(`role_id`), `function_id`=VALUES(`function_id`), `club_id`=VALUES(`club_id`);";
                        $conn->executeQuery($this->crfInsertQry);
                    }
                    if ($this->contAssignmentQry != "") {
                        $this->contAssignmentQry .= " ON DUPLICATE KEY UPDATE `contact_id`=VALUES(`contact_id`), `fg_rm_crf_id`=VALUES(`fg_rm_crf_id`), `update_count`=`update_count`+1, `update_time`='$currentDate';";
                        $conn->executeQuery($this->contAssignmentQry);
                    }
//                    //Add roster assignment.
//                    if (count($addRosterAssignments)) {
//                        $addRosterValues = implode(',', $addRosterAssignments);
//                        $conn->executeQuery("INSERT INTO `fg_team_roster_category_contacts` (`roster_id`,`contact_id`) VALUES $addRosterValues");
//                    }
                    /* Save assignment ends */

                    //Insert log entries.
                    $this->insertAssignmentLogEntries($conn, $container, $insertRoleFunctionLog);

                    /* update contact last_updated date starts */
                    if (count($this->updateContactChangeDates)) {
                        $updateContactIds['fedContact'] = ($this->updateContactChangeDates['fedRoleAssignment']) ? $this->updateContactChangeDates['fedRoleAssignment'] : '';
                        $updateContactIds['subfedContact'] = ($this->updateContactChangeDates['subFedRoleAssignment']) ? $this->updateContactChangeDates['subFedRoleAssignment'] : '';
                        $updateContactIds['id'] = ($this->updateContactChangeDates['roleAssignment']) ? $this->updateContactChangeDates['roleAssignment'] : '';
                        foreach($updateContactIds as $fieldKey => $contactVal) {
                            if($contactVal) {
                                $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->updateLastUpdated($contactVal, $fieldKey);
                            }
                        }
                    }
                    /* update contact last_updated date ends */
                }

//                $conn->close();
            }
            $resultArray = array('errorType' => $errorType, 'errorArray' => $errorArray, 'sidebarArray' => $newSidebarArray, 'existingAsgmnts' => $existingAsgmnts);
        } else {
            $resultArray = array();
        }

        return $resultArray;
    }

    /**
     * Function to generate query for add or delete assignments, log entry etc
     *
     * @param int $clubId                           Club Id
     * @param int $contactId                        Contact Id
     * @param int $catId                            Cat Id
     * @param int $roleId                           Role Id
     * @param int $functionId                       Function Id
     * @param boolean $doInsert                     Whether add assignment
     * @param boolean $doDelete                     Whether delete assignment
     * @param array $contactClubIds                 Array of Contact Club Ids
     * @param array $categoryTitles                 Array of Category Titles
     * @param array $roleTitles                     Array of Role Titles
     * @param array $functionTitles                 Array of Function Titles
     * @param array $clubExecBoardFunctionIds       Array of Club Executive Board Function Ids
     * @param array $contactNames                   Array of Contact Names
     * @param string $currentDate                   Current Date
     * @param int $currentContactId                 Current Contact id
     * @param object $conn                          Connection object
     * @param int $clubExecutiveBoardId             Club Executive Board (Role) Id
     * @param boolean $isFedCategory                Whether federation category or not
     * @param int $fedWrkgrpRoleId                  Executive Board Role Id of Federation
     * @param int $catClubId                        Club Id of Category
     * @param string $catType                       Category Type
     */
    public function generateAssignmentsQuery($clubId, $contactId, $catId, $roleId, $functionId, $doInsert, $doDelete, $contactClubIds, $categoryTitles, $roleTitles, $functionTitles, $clubExecBoardFunctionIds, $contactNames, $currentDate, $currentContactId, $conn, $clubExecutiveBoardId, $isFedCategory, $fedWrkgrpRoleId, $catClubId, $catType, $currFederationId = '', $currSubFederationId = '', $fedContactIds = '', $subFedContatIds = '', $container = '', $clubType = '', $contactIdArrNew = array())
    {
        $contactId = intval($contactId);
        $catId = intval($catId);
        $roleId = intval($roleId);
        //$functionId = intval($functionId);
        $currentContactId = intval($currentContactId);
        $contactClubId = $contactClubIds[$contactId];
        if($isFedCategory == '1') {
            if($clubType == 'federation') {
                if($clubId == $catClubId) {
                    $logType = 'fedRoleAssignment';
                    $contact = $fedContactIds[$contactId];
                    $crfClubId = $clubId;
                } else if($currSubFederationId == $catClubId) {
                    $logType = 'subFedRoleAssignment';
                    $contact = $subFedContatIds[$contactId];
                    $crfClubId = $currSubFederationId;
                }
            }
            else if($clubType == 'sub_federation') {
                if($currFederationId == $catClubId) {
                    $logType = 'fedRoleAssignment';
                    $contact = $fedContactIds[$contactId];
                    $crfClubId = $currFederationId;
                } else if($clubId == $catClubId) {
                    $logType = 'subFedRoleAssignment';
                    $contact = $subFedContatIds[$contactId];
                    $crfClubId = $clubId;
                }
            }
            else {
                if($currFederationId == $catClubId) {
                    $logType = 'fedRoleAssignment';
                    $contact = $fedContactIds[$contactId];
                    $crfClubId = $currFederationId;
                } else if($currSubFederationId == $catClubId) {
                    $logType = 'subFedRoleAssignment';
                    $contact = $subFedContatIds[$contactId];
                    $crfClubId = $currSubFederationId;
                }
            }
        }
        else {
            $logType = 'roleAssignment';
            $contact = $contactId;
            $crfClubId = $clubId;
        }
        //$crfClubId = (($catType == 'R') && $isFedCategory) ? $contactClubId : $clubId;
        $functionCond = ($functionId == NULL) ? "`function_id` IS NULL" : "`function_id`=$functionId";
        $logKey = ($functionId == NULL) ? 'role_'.$roleId : 'function_'.$functionId;
        $roleName = isset($roleTitles[$roleId]) ? $roleTitles[$roleId] : '';
        $functionName = (($functionId == NULL) || !(isset($functionTitles[$functionId]))) ? '' : $functionTitles[$functionId];
        $assignmentName = ($functionId == NULL) ? $roleName : "$roleName ($functionName)";
        $conn = $this->getEntityManager()->getConnection();
        //Add assignments.
        if ($doInsert) {
            $crfInsertValues = "";
            if ($functionId == NULL) {
                $catCrfRoleIds = isset($this->crfRoleIds[$catId][$crfClubId]) ? $this->crfRoleIds[$catId][$crfClubId] : array();
                if (!in_array($roleId, $catCrfRoleIds)) {
                    $crfInsertValues = "($catId,$roleId,NULL,$crfClubId)";
                    $this->crfRoleIds[$catId][$clubId][] = $roleId;
                }
            } else {
                $crfInsertValues = "($catId,$roleId,$functionId,$crfClubId)";
            }
            if ($crfInsertValues) {
                $this->crfInsertQry .= ($this->crfInsertQry == "") ? "INSERT INTO `fg_rm_category_role_function` (`category_id`,`role_id`,`function_id`,`club_id`) VALUES $crfInsertValues" : ",$crfInsertValues";
            }
            $updateType = ($functionId == NULL) ? 'R' : 'F';

            if ($this->contAssignmentQry == "") {
                $this->contAssignmentQry .= "INSERT INTO `fg_rm_role_contact` (`contact_id`,`fg_rm_crf_id`,`assined_club_id`,`contact_club_id`,`update_type`) VALUES "
                        . "('$contact',(SELECT `id` FROM `fg_rm_category_role_function` WHERE `category_id`=$catId AND `role_id`=$roleId AND $functionCond AND `club_id`=$crfClubId limit 1),'$clubId','$crfClubId','$updateType')";
            } else {
                $this->contAssignmentQry .= ",('$contact',(SELECT `id` FROM `fg_rm_category_role_function` WHERE `category_id`=$catId AND `role_id`=$roleId AND $functionCond AND `club_id`=$crfClubId limit 1),'$clubId','$crfClubId','$updateType')";
            }
        }

        //Remove assignments.
        if ($doDelete) {
            if ($this->deleteAssignmentQry == "") {
                $this->deleteAssignmentQry .= "DELETE FROM `fg_rm_role_contact` WHERE (`contact_id`,`fg_rm_crf_id`) IN (($contact,(SELECT `id` FROM `fg_rm_category_role_function` WHERE `category_id`=$catId AND `role_id`=$roleId AND $functionCond AND `club_id`=$crfClubId limit 1))";
            } else {
                $this->deleteAssignmentQry .= ",($contact,(SELECT `id` FROM `fg_rm_category_role_function` WHERE `category_id`=$catId AND `role_id`=$roleId AND $functionCond AND `club_id`=$crfClubId limit 1))";
            }
        }

        /* Insertion of log entries starts */
        $asgmntLogValBefore = $doInsert ? '-' : FgUtility::getSecuredData($assignmentName, $conn);
        $asgmntLogValAfter = $doInsert ? FgUtility::getSecuredData($assignmentName, $conn) : '-';
    $roleLogValBefore = $doInsert ? '-' : FgUtility::getSecuredData($contactNames[$contactIdArrNew[$contactId]], $conn);
        $roleLogValAfter = $doInsert ? FgUtility::getSecuredData($contactNames[$contactIdArrNew[$contactId]], $conn) : '-';
        $funLogValBefore = $doInsert ? '-' : FgUtility::getSecuredData($contactNames[$contactIdArrNew[$contactId]], $conn);
        $funLogValAfter = $doInsert ? FgUtility::getSecuredData($contactNames[$contactIdArrNew[$contactId]], $conn) : '-';
        $clubLogValBefore = $doInsert ? '-' : FgUtility::getSecuredData($contactNames[$contactIdArrNew[$contactId]], $conn);
        $clubLogValAfter = $doInsert ? FgUtility::getSecuredData($contactNames[$contactIdArrNew[$contactId]], $conn) : '-';
        $catTitle = FgUtility::getSecuredData($categoryTitles[$catId], $conn);
        $functionName = FgUtility::getSecuredData($functionName, $conn);

        // Assignment log.
        $roleType = ($isFedCategory == '1') ? 'fed' : 'club';
        $this->asgmntLogValues[] = array($contact,$catClubId,$roleType,$catId,$roleId,$functionId,$currentDate,$catTitle,$asgmntLogValBefore,$asgmntLogValAfter,$currentContactId,$logType);
        $this->updateContactChangeDates[$logType] = $contact;

        //Role log (If contact is assigned to executive board function, add club log also).
        if ($roleId == $clubExecutiveBoardId) {
            $fedWrkgrpRoleId = $fedWrkgrpRoleId ? $fedWrkgrpRoleId : $roleId;
            $rleLogValues = array($clubId,$fedWrkgrpRoleId,$currentDate,'assigned contacts',$functionName,$roleLogValBefore,$roleLogValAfter,$currentContactId);
            $roleLogId = $this->insertRoleLog($conn, $rleLogValues, $container);
            //Club log.
            $this->clubLogValues[] = array($clubId,$currentDate,'executive board',$functionName,$clubLogValBefore,$clubLogValAfter,$currentContactId,$roleLogId);
        } else {
            $rleLogValues = array($clubId,$roleId,$contact,$currentDate,'assigned contacts',$functionName,$roleLogValBefore,$roleLogValAfter,$currentContactId);
            $this->roleLogValues[] = $rleLogValues;
        }

        //Function log.
        if ($functionId != NULL) {
            $this->functionLogValues[] = array($clubId,$roleId,$functionId,$contact,$currentDate,'assigned contacts',$functionName,$funLogValBefore,$funLogValAfter,$currentContactId);
        }
        /* Insertion of log entries ends */
    }

    /**
     * Function to insert Assignment Log entries.
     *
     * @param object $conn                  Connection variable
     * @param object $container             Container object
     * @param int    $insertRoleFunctionLog Insert to role, function log flag
     */
    public function insertAssignmentLogEntries($conn, $container, $insertRoleFunctionLog = 1)
    {
        if (count($this->asgmntLogValues)) {
            foreach($this->asgmntLogValues as $insertValAssc) {
                $valueStringAssc = array();
                $valueStringAssc[] = array('contact_id' => $insertValAssc[0], 'category_club_id' => $insertValAssc[1], 'role_type' => $insertValAssc[2], 'category_id' => $insertValAssc[3], 'role_id' => $insertValAssc[4], 'function_id' => $insertValAssc[5], 'category_title' => $insertValAssc[7], 'value_before' => $insertValAssc[8], 'value_after' => $insertValAssc[9]);
                $this->insertLogEntries($insertValAssc[11], 'fg_cm_log_assignment', $valueStringAssc, $container);
            }
        }
        if (count($this->clubLogValues)) {
            foreach($this->clubLogValues as $insertValClub) {
                $valueStringClub[] = array('club_id' => $insertValClub[0], 'kind' => $insertValClub[2], 'field' => $insertValClub[3], 'value_before' => $insertValClub[4], 'value_after' => $insertValClub[5], 'rolelog_id' => $insertValClub[7]);
            }
            $this->insertLogEntries('assignment_club', 'fg_club_log', $valueStringClub, $container);
        }
        if (count($this->roleLogValues) && $insertRoleFunctionLog) {
            foreach($this->roleLogValues as $insertValRole) {
                $valueStringRole[] = array('club_id' => $insertValRole[0], 'role_id' => $insertValRole[1], 'contact_id' => $insertValRole[2], 'kind' => $insertValRole[4], 'field' => $insertValRole[5], 'value_before' => $insertValRole[6], 'value_after' => $insertValRole[7]);
            }
            $this->insertLogEntries('assignment_role', 'fg_rm_role_log', $valueStringRole, $container);
        }
        if (count($this->functionLogValues) && $insertRoleFunctionLog) {
            foreach($this->functionLogValues as $insertValFun) {
                $valueStringFun[] = array('club_id' => $insertValFun[0], 'role_id' => $insertValFun[1], 'function_id' => $insertValFun[2], 'contact_id' => $insertValFun[3], 'kind' => $insertValFun[5], 'field' => $insertValFun[6], 'value_before' => $insertValFun[7], 'value_after' => $insertValFun[8]);
            }
            $this->insertLogEntries('assignment_function', 'fg_rm_function_log', $valueStringFun, $container);
        }
    }

    /**
     * Function to insert Log entries.
     *
     * @param object $conn Connection variable
     */
    public function insertLogEntries($logCase, $table, $valueString, $container)
    {
        $logHandle = new FgLogHandler($container);
        $logHandle->processLogEntryAction($logCase, $table, $valueString);
    }

    /**
     * Function to insert Role Log.
     *
     * @param object $conn      Connection variable
     * @param string $valuesStr String of values to be inserted
     * @param object $container Container object
     *
     * @return int $logId Id of inserted log entry.
     */
    public function insertRoleLog($conn, $valuesStr, $container)
    {
        $valueString[] = array('club_id' => $valuesStr[0], 'role_id' => $valuesStr[1], 'kind' => $valuesStr[3], 'field' => $valuesStr[4], 'value_before' => $valuesStr[5], 'value_after' => $valuesStr[6]);
        $this->insertLogEntries('assignment_role', 'fg_rm_role_log', $valueString, $container);
        //$conn->executeQuery("INSERT INTO `fg_rm_role_log` $this->roleLogFields VALUES $valuesStr");
        $roleLog = $conn->fetchAll("SELECT LAST_INSERT_ID() AS logId");
        $logId = $roleLog['0']['logId'];

        return $logId;
    }

    /**
     * Function to check whether contact is already assigned to another role of multi-assignment category.
     *
     * @param array $catArrDetail                Category array
     * @param array $noMultiAssignmentsOfContact Array of existing assignments
     * @param int   $contactId                   Contact id
     * @param int   $catId                       Category id
     *
     * @return boolean $multiAsgnmntError Whether multiple assignment error exists or not.
     */
    public function checkMultiAssignmentError($catArrDetail, $noMultiAssignmentsOfContact, $contactId, $catId)
    {
        $multiAsgnmntError = true;
        foreach ($catArrDetail['role'] as $checkRoleId => $checkRoles) {
            if (isset($checkRoles['is_deleted'])) {
                if ($checkRoleId == $noMultiAssignmentsOfContact[$contactId][$catId]['roleid']) {
                    $multiAsgnmntError = false;
                    break;
                }
            } else {
                foreach ($checkRoles['function'] as $checkFunctions) {
                    if (isset($checkFunctions['is_deleted'])) {
                        if ($checkRoleId == $noMultiAssignmentsOfContact[$contactId][$catId]['roleid']) {
                            $multiAsgnmntError = false;
                            break;
                        }
                    }
                }
            }
        }

        return $multiAsgnmntError;
    }

    /**
     * Function to delete the federation assignments of a contact.
     *
     * @param array  $clubIdArray           Array of club details
     * @param int    $contactId             Contact Id
     * @param object $container             Container Object
     * @param array  $translationsArray     Translations Array
     * @param array  $deleteByClubLevel     Whether to delete federation, sub-federation assignments as a whole or level wise
     * @param array  $clubLevel             Assignment club level ('federation' or 'sub_federation')
     * @param int    $insertRoleFunctionLog Role and function log is to be inserted or not
     */
    public function deleteFederationAssignmentOfContact($clubIdArray, $contactId, $container, $translationsArray = array(), $deleteByClubLevel = false, $clubLevel = 'federation', $insertRoleFunctionLog = 1)
    {
        $em = $this->getEntityManager();
        $conn = $em->getConnection();
        $resultArray = array();
        $currContactId = $container->get('contact')->get('id');
        $terminologyService = $container->get('fairgate_terminology_service');
        $club = $container->get('club');

        $assignedRoles = $em->getRepository('CommonUtilityBundle:FgRmCategory')->getAllAssignedCategories($clubIdArray, $conn, $contactId, 1, true);
        if (count($assignedRoles) > 0) {
            foreach ($assignedRoles as $key => $val) {
                if ($deleteByClubLevel) {
                    $toDelete = ($clubLevel == 'sub_federation') ? (($val['clubId'] ==  $clubIdArray['subFederationId']) ? 1 : 0) : (($val['clubId'] ==  $clubIdArray['federationId']) ? 1 : 0);
                } else {
                    $toDelete = (in_array($val['clubId'], array($clubIdArray['federationId'], $clubIdArray['subFederationId']))) ? 1 : 0;
                }
                if (($val['is_fed_category'] == '1') && $toDelete) {
                    if ($val['functionId']) {
                        $resultArray[$val['clubId']][$contactId][$val['rmCatId']]['role'][$val['roleId']]['function'][$val['functionId']]['is_deleted'] = 1;
                    } else {
                        $resultArray[$val['clubId']][$contactId][$val['rmCatId']]['role'][$val['roleId']]['is_deleted'] = 1;
                    }
                }
            }
            $contactIdArr = array($contactId);
            foreach ($resultArray as $asgnClubId => $asgmntArray) {
                $em->getRepository('CommonUtilityBundle:FgRmRoleContact')->updateContactAssignments($asgmntArray, $asgnClubId, $contactIdArr, $currContactId, $club, $terminologyService, $container, $translationsArray, '', $insertRoleFunctionLog, true);
            }
        }
    }
    
    /**
     * Function to get fed and sub fed contact ids.
     *
     * @param int    $contactId             Contact Id
     * @param array  $dragTypeArr           Cat type and club id
     * @param int    $clubId                Club id
     * @param string $clubType              Club type
     * @param int    $federationId          federation id
     * @param int    $subFederationId       sub_federation id
     */
    private function getFedSubfedContactIds($contactIds, $dragTypeArr, $clubId, $clubType, $federationId, $subFederationId) {
        $contactsArr = explode(',', $contactIds);
        $seperator = '';
        $fedContacts = '';
        $subFedContacts = '';
        foreach($contactsArr as $contact) {
            $em = $this->getEntityManager();
            $fedSubfedContact = $em->getRepository('CommonUtilityBundle:FgCmContact')->getFederationContactId($contact);
            $fedContacts .= $seperator.$fedSubfedContact['fedContactId'];
            $subFedContacts .= $seperator.$fedSubfedContact['subFedContactId'];
            $seperator = ',';
            $contactArray[$contact]['fedContactId'] = $fedSubfedContact['fedContactId'];
            $contactArray[$contact]['subFedContactId'] = $fedSubfedContact['subFedContactId'];
        }
        if($dragTypeArr[0] == 'FEDROLE') {
            if($clubType == 'federation') {
                if($clubId == $dragTypeArr[1]) {
                    $contactIds = $fedContacts;
                    foreach($contactsArr as $contact) {
                        unset($contactArray[$contact]['subFedContactId']);
                        $contactArray[$contact] = $contactArray[$contact]['fedContactId'];
                    }
                }
                elseif ($subFederationId == $dragTypeArr[1]) {
                    $contactIds = $subFedContacts;
                    foreach($contactsArr as $contact) {
                        unset($contactArray[$contact]['fedContactId']);
                        $contactArray[$contact] = $contactArray[$contact]['subFedContactId'];
                    }
                }
            }
            elseif($clubType == 'sub_federation') {
                if($clubId == $dragTypeArr[1]) {
                    $contactIds = $subFedContacts;
                    foreach($contactsArr as $contact) {
                        unset($contactArray[$contact]['fedContactId']);
                        $contactArray[$contact] = $contactArray[$contact]['subFedContactId'];
                    }
                }
                elseif ($federationId == $dragTypeArr[1]) {
                    $contactIds = $fedContacts;
                    foreach($contactsArr as $contact) {
                        unset($contactArray[$contact]['subFedContactId']);
                        $contactArray[$contact] = $contactArray[$contact]['fedContactId'];
                    }
                }
            }
            else {
                if($federationId == $dragTypeArr[1]) {
                    $contactIds = $fedContacts;
                    foreach($contactsArr as $contact) {
                        unset($contactArray[$contact]['subFedContactId']);
                        $contactArray[$contact] = $contactArray[$contact]['fedContactId'];
                    }
                }
                elseif ($subFederationId == $dragTypeArr[1]) {
                    $contactIds = $subFedContacts;
                    foreach($contactsArr as $contact) {
                        unset($contactArray[$contact]['fedContactId']);
                        $contactArray[$contact] = $contactArray[$contact]['subFedContactId'];
                    }
                }
            }
        }
        else {
            $contactIds = $contactIds;
        }

        return array('contactIdStr' => $contactIds,'contactArray' => $contactArray);;
    }
    /**
     * Function to get assignments of contacts for a given category.
     *
     * @param string/array $contactIds         Contact ids string/ Contact ids array
     * @param int          $catId              Category id
     * @param int          $excludeRoleId      Role id to exclude from result
     * @param int          $includeRoleId      Role id to include in result
     * @param object       $conn               Connection object
     * @param boolean      $isFrontend         Whether to get data in frontend or not
     * @param boolean      $remInheritedAsgmnt Whether to avoid inherited assignment or not
     *
     * @return array $assignments Result array of assignments.
     */
    public function getContactAssignmentsOfCat($contactIds, $catId, $excludeRoleId = '', $includeRoleId = '', $conn = false, $isFrontend = false, $remInheritedAsgmnt = false, $clubId, $clubType, $federationId, $subFederationId, $dragCatType = '', $actionType= '')
    {
        $hasConnection = $conn ? true : false;
        if (!$hasConnection) {
            $conn = $this->getEntityManager()->getConnection();
        }
        //check if fed or subfed roles
        $catResultArray = $conn->fetchAll("SELECT rc.club_id AS clubId, rc.is_fed_category AS isFedCat FROM `fg_rm_category` rc WHERE rc.id = $catId");
        $dragType = ($catResultArray[0]['isFedCat'] == 1) ? 'FEDROLE-'.$catResultArray[0]['clubId'] : 'OTHERROLE-'.$catResultArray[0]['clubId'];
        $dragTypeArr = explode('-', $dragType);
        //check if fed or subfed roles
        $contactResultArray = $this->getFedSubfedContactIds($contactIds, $dragTypeArr, $clubId, $clubType, $federationId, $subFederationId);
        
        $assignments = array();
        $copyContactIds = $contactIds;
        $contactIds = $contactResultArray['contactIdStr'];
        $contactIdStr = is_array($contactIds) ? implode(',', $contactIds) : $contactIds;
        $contactIdStr = FgUtility::getSecuredData($contactIdStr, $conn);
        $catId = intval($catId);
        $excludeRoleId = intval($excludeRoleId);
        $includeRoleId = intval($includeRoleId);

        $roleCond = "";
        if ($includeRoleId != '') {
            $roleCond .= " AND crf.role_id = $includeRoleId";
        }
        if ($excludeRoleId != '') {
            $roleCond .= " AND crf.role_id <> $excludeRoleId";
        }
        $frontendCond = "";
        if ($isFrontend) {
            $frontendCond = " AND rc.is_removed=0";
        }
        $remInheritedJoin = "";
        $remInheritedCond = "";
        if ($remInheritedAsgmnt) {
            $remInheritedJoin = " LEFT JOIN `fg_rm_function` f ON (f.id=crf.function_id) ";
            $remInheritedCond = " AND f.is_federation != 1";
        }
        if ($actionType =='assign') {
            $roleCond ="";
            $roleAssCond .= " AND crf.role_id <> $includeRoleId";
        } else if (($actionType =='move_req_assn') || ($actionType =='remove_req_assn')) {
            $contactIdStr = is_array($copyContactIds) ? implode(',', $copyContactIds) : $copyContactIds;
        }
        $contAssignments = $conn->fetchAll("SELECT rc.contact_id AS contactId, crf.role_id AS roleId, crf.function_id AS functionId "
                . "FROM `fg_rm_role_contact` rc "
                . "LEFT JOIN `fg_rm_category_role_function` crf ON (crf.id=rc.fg_rm_crf_id) "
                . $remInheritedJoin
                . "WHERE rc.contact_id IN ($contactIdStr) AND crf.category_id=$catId $roleAssCond $roleCond $frontendCond $remInheritedCond");

        $assignments = array();
        foreach ($contAssignments as $contAssignment) {
            $assignments[$contAssignment['contactId']]['roleid'] = $contAssignment['roleId'];
            $assignments[$contAssignment['contactId']]['functionids'][] = $contAssignment['functionId'];
        }

        if (!$hasConnection) {
            $conn->close();
        }
        
        $newAssignmentArray = array();
        foreach ($contactResultArray['contactArray'] as $key => $con) {
            if (array_key_exists($con, $assignments)) {
                $newAssignmentArray[$key] = $assignments[$con];
            }
        }
        if (count($newAssignmentArray) == 0) {
            $newAssignmentArray = $assignments;
        }
        return $newAssignmentArray;
    }

    /**
     * Function to return filter contacts (add included contacts and subtract excluded contacts).
     *
     * @param int $filterId Filter id
     * @param int $roleId   Role id
     * @param int $rmCrfId  Cat_role_func unique id
     *
     * @return array $currentRoleContacts Array of filter role contacts.
     */
    public function getFilterRoleContacts($filterId, $roleId, $rmCrfId)
    {
        $rmCrfId = intval($rmCrfId);
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT  contact_id
                FROM fg_rm_role_contact
                WHERE  fg_rm_crf_id = $rmCrfId";

        $filterContacts = $conn->executeQuery($sql)->fetchAll();

        $includedContacts = $this->_em->getRepository('CommonUtilityBundle:FgRmRoleManualContacts')->getexeceptionContactIds($roleId, 'included');
        $excludedContacts = $this->_em->getRepository('CommonUtilityBundle:FgRmRoleManualContacts')->getexeceptionContactIds($roleId, 'excluded');

        $currentRoleContacts = array();
        foreach ($filterContacts as $val1) {
            $currentRoleContacts[$val1['contact_id']] = $val1['contact_id'];
        }

        foreach ($includedContacts as $val2) {
            $currentRoleContacts[$val2['contact_id']] = $val2['contact_id'];
        }

        foreach ($excludedContacts as $val3) {
            unset($currentRoleContacts[$val3['contact_id']]);
        }

        return $currentRoleContacts;
    }

    /**
     * Function to get the contacts with given category_role_function_id.
     *
     * @param int $rmCrfId Cat_role_func unique id.
     *
     * @return array $results Result array of contacts.
     */
    public function getfilterContactsFromCrf($rmCrfId)
    {
        $rmCrfId = intval($rmCrfId);
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT  contact_id
                FROM fg_rm_role_contact
                WHERE  fg_rm_crf_id = $rmCrfId";

        $results = $conn->executeQuery($sql)->fetchAll();

        return $results;
    }

    /**
     * Function to Save Import Assignments.
     *
     * @param array  $asgmntDataArray      Array of Assignment Categories
     * @param string $importTable          Import Table Name
     * @param int    $clubId               Club Id
     * @param int    $federationId         Federation Id
     * @param int    $clubWorkgroupId      Club Workgroup Id
     * @param int    $clubExecBoardId      Club Executive Board Role Id
     * @param int    $clubTeamId           Club Team Id
     * @param int    $currentContactId     Current Contact Id
     * @param array  $fedWrkgrpCatRoleIds  Federation Workgroup Id
     * @param array  $teamDefaultRosterIds Array of Team Roster Ids
     */
    public function saveImportAssignments($asgmntDataArray, $importTable, $clubId, $clubType, $federationId, $clubWorkgroupId, $clubExecBoardId, $clubTeamId, $currentContactId, $fedWrkgrpCatRoleIds, $teamDefaultRosterIds, $terminologyService, $container)
    {
        $conn = $this->getEntityManager()->getConnection();
        $currentDate = date('Y-m-d H:i:s');
        $catIds = implode(',', array_keys($asgmntDataArray));
        $catIdContactArray = $this->_em->getRepository('CommonUtilityBundle:FgRmCategory')->checkFederationCategory($catIds);
        $fedWrkgrpCatId = isset($fedWrkgrpCatRoleIds['catId']) ? $fedWrkgrpCatRoleIds['catId'] : '';
        $fedExecBrdRoleId = isset($fedWrkgrpCatRoleIds['roleId']) ? $fedWrkgrpCatRoleIds['roleId'] : '';
        $hasClubCat = false;
        $hasFedCat = false;

        //Get cat-role-func unique id for given category ids and roles having no functions.
        $crfRoles = $conn->fetchAll("SELECT `category_id`, `role_id`, `club_id` FROM `fg_rm_category_role_function` WHERE `category_id` IN ($catIds) AND `function_id` IS NULL");
        $noFunctionCrfIds = array();
        foreach ($crfRoles as $crfRole) {
            if ($crfRole['club_id'] == $clubId) {
                $noFunctionCrfIds[$crfRole['category_id']][] = $crfRole['role_id'];
            }
        }

        foreach ($asgmntDataArray as $catId => $catArrDetail) {

            if (strpos($catId, 'team') !== false) {
                $catId = $clubTeamId;
            } else if ($catId == $fedWrkgrpCatId) {
                $catId = $clubWorkgroupId;
            }

            if (isset($catArrDetail['role'])) {
                foreach ($catArrDetail['role'] as $roleId => $roleArrDetail) {

                    if ($roleId == $fedExecBrdRoleId) {
                        $roleId = $clubExecBoardId;
                    }

                    if (isset($roleArrDetail['function'])) {
                        foreach ($roleArrDetail['function'] as $functionId => $functionArrDetail) {

                            $isFedCat = $functionArrDetail['is_fed_cat'];
                            $this->generateImportAsignmentQueries($catId, $isFedCat, $roleId, $functionId, $clubId, $clubType, $federationId, $clubExecBoardId, $clubTeamId, $fedExecBrdRoleId, $currentContactId, $importTable, $currentDate, $terminologyService, $container, $noFunctionCrfIds, $teamDefaultRosterIds, $catIdContactArray);

                            if ($isFedCat == '1') {
                                $hasFedCat = true;
                            } else {
                                $hasClubCat = true;
                            }
                        }
                    } else {
                        $isFedCat = $roleArrDetail['is_fed_cat'];
                        $functionId = null;
                        $this->generateImportAsignmentQueries($catId, $isFedCat, $roleId, $functionId, $clubId, $clubType, $federationId, $clubExecBoardId, $clubTeamId, $fedExecBrdRoleId, $currentContactId, $importTable, $currentDate, $terminologyService, $container, $noFunctionCrfIds, $teamDefaultRosterIds, $catIdContactArray);

                        if ($isFedCat == '1') {
                            $hasFedCat = true;
                        } else {
                            $hasClubCat = true;
                        }
                    }
                }
            }
        }
        // Execute Queries for Assigning Contacts to selected Categories.
        $this->executeImportAsignmentQueries($conn, $hasClubCat, $hasFedCat, $federationId, $importTable, $currentDate, $clubId, $clubType);
    }

    /**
     * Function to generate Queries for Import Assignment.
     *
     * @param int     $catId                Category Id
     * @param boolean $isFedCat             Whether the category is federation category or not
     * @param int     $roleId               Role Id
     * @param int     $functionId           Function Id
     * @param int     $clubId               Club Id
     * @param int     $federationId         Federation Id
     * @param int     $clubExecBoardId      Club Executive Board Role Id
     * @param int     $clubTeamId           Club Team Id
     * @param int     $fedExecBrdRoleId     Federation Executive Board Role Id
     * @param int     $currentContactId     Current Contact Id
     * @param string  $importTable          Import Table Name
     * @param string  $currentDate          Current Date
     * @param array   $noFunctionCrfIds     Array of crf ids of selected Categories having no Functions
     * @param array   $teamDefaultRosterIds Array of Team Roster Ids
     * @param array   $catIdContactArray    Array of category contact ids
     */
    public function generateImportAsignmentQueries($catId, $isFedCat, $roleId, $functionId, $clubId, $clubType, $federationId, $clubExecBoardId, $clubTeamId, $fedExecBrdRoleId, $currentContactId, $importTable, $currentDate, $terminologyService, $container, $noFunctionCrfIds = array(), $teamDefaultRosterIds = array(), $catIdContactArray = array())
    {
        $clubObj = $container->get('club');
        $contactsQry = "";
        if ($functionId == null) {
            $crfQry = "(SELECT `id` FROM `fg_rm_category_role_function` WHERE `category_id`=$catId AND `role_id`=$roleId AND `function_id` IS NULL AND `club_id`=$clubId)";
        } else {
            $crfQry = "(SELECT `id` FROM `fg_rm_category_role_function` WHERE `category_id`=$catId AND `role_id`=$roleId AND `function_id`=$functionId AND `club_id`=$clubId)";
        }
        if ($isFedCat == '1') {
            if (array_key_exists($catId, $catIdContactArray['federation'])) {
                $contactsQry = "FROM `fg_cm_contact` c WHERE c.`import_table`='$importTable' AND c.fed_membership_cat_id IS NOT NULL AND c.fed_membership_cat_id != '' AND c.club_id=$federationId";
            } else if (array_key_exists($catId, $catIdContactArray['sub_federation'])) {
                if ($clubType == 'sub_federation') {
                    $impClubId = $clubObj->get('id');
                } else {
                    $impClubId = $clubObj->get('sub_federation_id');
                }
                $contactsQry = "FROM `fg_cm_contact` c WHERE c.`import_table`='$importTable' AND c.fed_membership_cat_id IS NOT NULL AND c.fed_membership_cat_id != '' AND c.club_id=".$impClubId;
            }
        } else {
            $contactsQry = "FROM `fg_cm_contact` c WHERE c.`import_table`='$importTable' and c.club_id = $clubId";
        }
        $catName = "(SELECT `title` FROM `fg_rm_category` WHERE `id`=$catId)";
        $roleName = "(SELECT `title` FROM `fg_rm_role` WHERE `id`=$roleId)";
        $functionName = ($functionId == null) ? "(SELECT '')" : "(SELECT `title` FROM `fg_rm_function` WHERE `id`=$functionId)";

        // Insert into `fg_rm_category_role_function` table.
        $insertCrfEntry = true;
        if ($functionId == null) {
            if (isset($noFunctionCrfIds[$catId])) {
                if (in_array($roleId, $noFunctionCrfIds[$catId])) {
                    $insertCrfEntry = false;
                }
            }
        }
        if ($insertCrfEntry) {
            $this->crfInsertValues[] = ($functionId == null) ? "($catId,$roleId,NULL,$clubId)" : "($catId,$roleId,$functionId,$clubId)";
        }

        // Insert into `fg_rm_role_contact` table.
        $selectQry = "SELECT c.`id`,$crfQry,'$clubId','$clubId'";
        $this->importAsigmntQry .= "INSERT INTO `fg_rm_role_contact` (`contact_id`,`fg_rm_crf_id`,`assined_club_id`,`contact_club_id`) ($selectQry $contactsQry) ON DUPLICATE KEY UPDATE `contact_id`=VALUES(`contact_id`), `fg_rm_crf_id`=VALUES(`fg_rm_crf_id`);";

//        // Insert into `fg_team_roster_category_contacts` table.
//        if (($catId == $clubTeamId) && array_key_exists($roleId, $teamDefaultRosterIds)) {
//            $rosterId = $teamDefaultRosterIds[$roleId];
//            $selectQry = "SELECT '$rosterId',c.`id`";
//            $this->teamRosterQry .= "INSERT INTO `fg_team_roster_category_contacts` (`roster_id`,`contact_id`) ($selectQry $contactsQry);";
//        }

        // Insert Role Log.
        $logRoleId = (($roleId == $clubExecBoardId) && ($fedExecBrdRoleId != '')) ? $fedExecBrdRoleId : $roleId;
        $selectQry = "SELECT '$clubId','$logRoleId','$currentDate','assigned contacts',$functionName,'-',contactName(c.`id`),'$currentContactId','$importTable',c.`id`";
        $this->logEntriesQry .= "INSERT INTO `fg_rm_role_log` (`club_id`,`role_id`,`date`,`kind`,`field`,`value_before`,`value_after`,`changed_by`,`import_table`,`import_contact`) ($selectQry $contactsQry);";

        // Insert Club Log if Club Executive Board Assignment.
        if ($roleId == $clubExecBoardId) {
            $this->clubLogQry .= "INSERT INTO `fg_club_log` (`club_id`,`date`,`kind`,`field`,`value_before`,`value_after`,`changed_by`,`rolelog_id`) "
                    . "(SELECT '$clubId','$currentDate','executive board',$functionName,'-',contactName(rl.`import_contact`),'$currentContactId',rl.`id` FROM `fg_rm_role_log` rl WHERE rl.import_table='$importTable' AND rl.role_id='$logRoleId' AND rl.date='$currentDate');";
            $execBrdTerminology = ucfirst($terminologyService->getTerminology('Executive Board', $container->getParameter('singular'), $clubId));
            $roleName = "'$execBrdTerminology'";
        }

        // Get terminology term of team if team-category is selected.
        if ($catId == $clubTeamId) {
            $teamTerminology = ucfirst($terminologyService->getTerminology('Team', $container->getParameter('plural'), $clubId));
            $catName = "'$teamTerminology'";
        }

        // Insert Function Log.
        if ($functionId != null) {
            $selectQry = "SELECT '$clubId','$roleId','$functionId','$currentDate','assigned contacts',$functionName,'-',contactName(c.`id`),'$currentContactId'";
            $this->logEntriesQry .= "INSERT INTO `fg_rm_function_log` (`club_id`,`role_id`,`function_id`,`date`,`kind`,`field`,`value_before`,`value_after`,`changed_by`) ($selectQry $contactsQry);";
        }

        // Insert Contact Assignment Log.
        $catClubId = ($isFedCat == '1') ? "(SELECT `club_id` FROM `fg_rm_category` WHERE `id`=$catId)" : "'$clubId'";
        $roleType = ($isFedCat == '1') ? 'fed' : 'club';
        $asgmntLogValAfter = ($functionId == null) ? "$roleName" : "(SELECT CONCAT($roleName,' (',$functionName,')'))";
        $selectQry = "SELECT c.`id`,$catClubId,'$roleType','$catId','$roleId','$functionId','$currentDate',$catName,'-',$asgmntLogValAfter,'$currentContactId'";
        $this->logEntriesQry .= "INSERT INTO `fg_cm_log_assignment` (`contact_id`,`category_club_id`,`role_type`,`category_id`,`role_id`,`function_id`,`date`,`category_title`,`value_before`,`value_after`,`changed_by`) ($selectQry $contactsQry);";
    }

    /**
     * Function to execute Queries of Import Assignments.
     *
     * @param object  $conn         Connection Object
     * @param boolean $hasClubCat   Whether selected Categories contain Club Category
     * @param boolean $hasFedCat    Whether selected Categories contain Federation Category
     * @param int     $federationId Federation Id
     * @param string  $importTable  Import Table Name
     * @param string  $currentDate  Current Date
     */
    public function executeImportAsignmentQueries($conn, $hasClubCat, $hasFedCat, $federationId, $importTable, $currentDate, $clubId, $clubType)
    {
        $conn->beginTransaction();

        // Insert Category-Role-Function Mapping Entries.
        if (count($this->crfInsertValues) > 0) {
            $valueString = implode(',', $this->crfInsertValues);
            $crfInsertQry = "INSERT INTO `fg_rm_category_role_function` " . $this->crfInsertFields ." VALUES $valueString "
                    . "ON DUPLICATE KEY UPDATE `category_id`=VALUES(`category_id`), `role_id`=VALUES(`role_id`), `function_id`=VALUES(`function_id`), `club_id`=VALUES(`club_id`);";

            $conn->executeQuery($crfInsertQry);
        }

        // Insert Contact Assignments.
        if ($this->importAsigmntQry != "") {
            $conn->executeQuery($this->importAsigmntQry);
        }

        // Insert Team Roster Entries.
        if ($this->teamRosterQry != "") {
            $conn->executeQuery($this->teamRosterQry);
        }

        // Log Entries.
        if ($this->logEntriesQry != "") {
            $conn->executeQuery($this->logEntriesQry);
        }

        // Insert Club Log if Club Executive Board is selected.
        if ($this->clubLogQry != "") {
            $conn->executeQuery($this->clubLogQry);
        }

        // Update Contacts' last_updated_date.
        if ($hasClubCat) {
            // Update `last_updated_date` of all contacts if club category is selected.
            $conn->executeQuery("UPDATE `fg_cm_contact` SET `last_updated`='$currentDate' WHERE `import_table`='$importTable'");
        } else if ($hasFedCat) {
            // Update `last_updated_date` of contacts having federation membership if all selected categories are federation categories.
            $memberClubId = ($clubType == 'federation') ? $clubId : $federationId;
            $conn->executeQuery("UPDATE `fg_cm_contact` c LEFT JOIN $importTable imp ON (c.import_id=imp.row_id) SET c.`last_updated`='$currentDate' WHERE c.`import_table`='$importTable' AND imp.member_clubid=$memberClubId");
        }

        // Delete Import Table.
        $conn->executeQuery("DROP TABLE $importTable");

        $conn->commit();
    }
    /**
     * Function to get different roles(team or workgroup) of a contact
     *
     * @param int    $contactId ContactId
     * @param object $container Container Object
     *
     * @return array $roles Array of assigned teams and workgroups
     */
    public function getAllRolesOfAContact($container = false, $contactId)
    {
        $clubDefaultLanguage = $container ? $container->get('club')->get('default_lang') : 'de';
        $clubId = $container->get('club')->get('id');
        $termExecutive = $container ? ucfirst($container->get('fairgate_terminology_service')->getTerminology('Executive Board', $container->getParameter('singular'))) : 'Executive';

        $result = $this->createQueryBuilder('rc')
                  ->select("r.id as roleId, (CASE WHEN r.isExecutiveBoard=1 THEN '" . $termExecutive . "' WHEN (ri18n.titleLang IS NULL OR ri18n.titleLang = '') THEN r.title ELSE ri18n.titleLang END) AS roleTitle, r.type AS roleType")
                  ->leftJoin("CommonUtilityBundle:FgRmCategoryRoleFunction", "crf", "WITH", "crf.id=rc.fgRmCrf")
                  ->innerJoin("CommonUtilityBundle:FgRmCategory", "c", "WITH", "c.id=crf.category AND c.club = :clubId")
                  ->innerJoin("CommonUtilityBundle:FgRmRole", "r", "WITH", "r.id=crf.role")
                  ->leftJoin("CommonUtilityBundle:FgRmRoleI18n", "ri18n", "WITH", "ri18n.id=r.id AND ri18n.lang='" . $clubDefaultLanguage . "'")
                  ->where('rc.contact = :contactId')
                  ->andWhere('r.type = :team OR r.type = :workgroup')
                  ->andWhere('r.isActive = :isActive')
                  ->groupBy('crf.role')
                  ->orderBy('r.id', 'ASC')
                  ->setParameters(array('clubId' => $clubId, 'contactId' => $contactId, 'team' => 'T', 'workgroup' => 'W', 'isActive' => 1))
                  ->getQuery()
                  ->getResult();
        $teams = array();
        $workgroups = array();
        foreach ($result as $val) {
            if ($val['roleType'] == 'T') {
                $teams[$val['roleId']] = strip_tags($val['roleTitle']);
            } elseif ($val['roleType'] == 'W') {
                $workgroups[$val['roleId']] = strip_tags($val['roleTitle']);
            }
        }
        $roles = array('teams' => $teams, 'workgroups' => $workgroups);

        return $roles;
    }
    /**
     * Function to get different member contacts of roles(team or workgroup)
     *
     * @param int    $roleIds RoleType (Team/Workgroup)
     * @param string $clubId  Club Id
     */
    public function getContactsOfRoles($roleIds,$clubId)
    {
        $result = $this->createQueryBuilder('rc')
                  ->select("GROUP_CONCAT(c.id) contacts")
                  ->leftJoin("CommonUtilityBundle:FgRmCategoryRoleFunction", "crf", "WITH", "crf.id=rc.fgRmCrf")
                  ->leftJoin("rc.contact", "c")
                  ->where("crf.role in ($roleIds)")
                  ->andWhere('rc.assinedClub = :clubId')
                  ->setParameter('clubId', $clubId)
                  ->getQuery()->getOneOrNullResult();

        return $result;
    }

    /**
     * Function to set contact assignments as removed/added.
     *
     * @param int    $contactId  Contact id
     * @param int    $clubId     Club id
     * @param int    $catId      Category id
     * @param int    $roleId     Role id
     * @param int    $functionId Function id
     * @param string $status     1/0 (Removed/Added)
     */
    public function updateAssignmentStatus($contactId, $clubId, $catId, $roleId, $functionId, $status)
    {
        $crfObj = $this->_em->getRepository('CommonUtilityBundle:FgRmCategoryRoleFunction')->findOneBy(array('category' => $catId, 'role' => $roleId, 'function' => $functionId, 'club' => $clubId));
        if ($crfObj) {
            $crfId = $crfObj->getId();
            $roleContactObj = $this->_em->getRepository('CommonUtilityBundle:FgRmRoleContact')->findOneBy(array('contact' => $contactId, 'fgRmCrf' => $crfId, 'assinedClub' => $clubId, 'contactClub' => $clubId));
            if ($roleContactObj) {
                $roleContactObj->setIsRemoved($status);
                 $this->_em->flush();
            }
        }
    }

    /**
     * Function to get role wise functions of a contact
     *
     * @param int $clubId    Club id
     * @param int $contactId Contact Id
     *
     * @return array Function id array
     */
    public function getRolewiseFunctionsOfAContact($clubId, $contactId)
    {
        $result = $this->createQueryBuilder('rc')
                  ->select("IDENTITY(crf.role) AS roleId, IDENTITY(crf.function) AS functionId")
                  ->leftJoin("CommonUtilityBundle:FgRmCategoryRoleFunction", "crf", "WITH", "crf.id=rc.fgRmCrf")
                  ->innerJoin("CommonUtilityBundle:FgRmRole", "r", "WITH", "r.id=crf.role")
                  ->where('rc.contact = :contactId')
                  ->andWhere('rc.assinedClub = :clubId')
                  ->andWhere('r.type = :team')
                  ->andWhere('r.isActive = :isActive')
                  ->orderBy('r.id', 'ASC')
                  ->setParameters(array('clubId' => $clubId, 'contactId' => $contactId, 'team' => 'T', 'isActive' => 1))
                  ->getQuery()
                  ->getArrayResult();
        $funDetails = array();
        foreach ($result as $val) {
            $funDetails[$val['roleId']][] = $val['functionId'];
        }

        return $funDetails;
    }
}
