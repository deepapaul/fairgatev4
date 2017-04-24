<?php

/**
 * FgRmRoleRepository.
 *
 * This class is used for roles in role management.
 */
namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Clubadmin\Util\Contactlist;
use Clubadmin\Util\Contactfilter;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Util\FgSettings;

/**
 * FgRmRoleRepository.
 *
 * This class is used for listing, adding, deleting, updating of roles in role management.
 */
class FgRmRoleRepository extends EntityRepository
{
    /**
     * Function to get role ids of a given category.
     *
     * @param int  $categoryId Category Id.
     * @param bool $sortRoles  Whether to sort roles or not
     *
     * @return array $roleIdArr Result array of role ids.
     */
    public function getRoleIds($categoryId, $sortRoles = false)
    {
        $roleIds = $this->createQueryBuilder('r')
                ->select('r.id as roleId')
                ->where('r.category=:categoryId')
                ->setParameter('categoryId', $categoryId);
        if ($sortRoles) {
            $roleIds = $roleIds->orderBy('r.sortOrder', 'ASC');
        }
        $dataResult = $roleIds->getQuery()->getResult();

        $roleIdArr = array();
        foreach ($dataResult as $key => $valArray) {
            $roleIdArr[] = $valArray['roleId'];
        }

        return $roleIdArr;
    }

    /**
     * Function to get the value of a given field from Role table.
     *
     * @param int    $roleId Role Id
     * @param string $field  Field to select
     *
     * @return string $fieldValue Value of field.
     */
    public function getFieldForRoles($roleId, $field = '')
    {
        $conn = $this->getEntityManager()->getConnection();
        $datetimeFormat = FgSettings::getMysqlDateTimeFormat();

        $fieldValue = '';
        if ($field != '') {
            $field = FgUtility::getSecuredData($field, $conn);
            $select = "$field";

            if ($field == 'filter_updated') {
                $select = "DATE_FORMAT(filter_updated, '$datetimeFormat') AS filter_updated";
            }

            $qry = "SELECT $select  "
                    .' FROM fg_rm_role  '
                    ."WHERE id= $roleId ";

            $result = $conn->executeQuery($qry)->fetchAll();
            $fieldValue = $result[0][$field];
        }

        return $fieldValue;
    }

    /**
     * Function to update filter roles with current filter criteria and exceptions.
     *
     * @param int    $roleId          Role Id
     * @param object $container       Container object
     * @param int    $contactId       Contact Id
     * @param array  $newExcludedCnts Array of contacts to exclude
     * @param array  $delExcludedCnts Array of contacts to delete
     */
    public function updateFilterRoles($roleId, $container, $contactId, $newExcludedCnts = array(), $delExcludedCnts = array())
    {
        $roleId = intval($roleId);
        $contactId = intval($contactId);
        $club = $container->get('club');
        $conn = $this->getEntityManager()->getConnection();
        $qry = 'SELECT (r.id)role_id, (r.title)as role_title, (f.id)filter_id, (rm.id)category_id, (rmcrf.id) rmcrfId,(r.club_id)club_id  '
                .' FROM fg_rm_role r '
                .'LEFT JOIN fg_rm_category rm ON(rm.id = r.category_id)'
                .'LEFT JOIN fg_filter f ON(f.id = r.filter_id)'
                .'LEFT JOIN fg_rm_category_role_function rmcrf ON(rmcrf.role_id = r.id)'
                ."WHERE rm.contact_assign = 'filter-driven' AND r.id= $roleId AND f.type='role'";

        $result = $conn->executeQuery($qry)->fetchAll();

        $filterId = $result[0]['filter_id'];
        $categoryId = $result[0]['category_id'];
        $rmCrfId = $result[0]['rmcrfId'];
        $clubId = intval($result[0]['club_id']);
        $roleTitle = FgUtility::getSecuredData($result[0]['role_title'], $conn);
        $newExcludedCnts = array_unique($newExcludedCnts);
        $delExcludedCnts = array_unique($delExcludedCnts);
        $currentDate = date('Y-m-d H:i:s');
        $removeRoleContIds = implode(',', $newExcludedCnts);
        $addRoleContIds = implode(',', $delExcludedCnts);

        if ($removeRoleContIds != '') {
            $removeContactsQry = "FROM `fg_cm_contact` c WHERE c.`id` IN ($removeRoleContIds)";

            // Insert Role Log.
            $conn->executeQuery('INSERT INTO `fg_rm_role_log` (`club_id`,`role_id`,`date`,`kind`,`field`,`value_before`,`value_after`,`changed_by`) '
                    ."(SELECT '$clubId','$roleId','$currentDate','assigned contacts','',contactName(c.`id`),'-','$contactId' $removeContactsQry)");

            // Insert Assignment Log.
            $conn->executeQuery('INSERT INTO `fg_cm_log_assignment` (`contact_id`,`category_club_id`,`role_type`,`category_id`,`role_id`,`function_id`,`date`,`category_title`,`value_before`,`value_after`,`changed_by`) '
                    ."(SELECT c.`id`,'$clubId','club','$categoryId','$roleId','','$currentDate',(SELECT `title` FROM `fg_rm_category` WHERE `id`=$categoryId),(SELECT `title` FROM `fg_rm_role` WHERE `id`=$roleId),'-','$contactId' $removeContactsQry)");
        }

        if ($addRoleContIds != '') {
            $insertContactsQry = "FROM `fg_cm_contact` c WHERE c.`id` IN ($addRoleContIds)";

            // Insert Role Log.
            $conn->executeQuery('INSERT INTO `fg_rm_role_log` (`club_id`,`role_id`,`date`,`kind`,`field`,`value_before`,`value_after`,`changed_by`) '
                    ."(SELECT '$clubId','$roleId','$currentDate','assigned contacts','','-',contactName(c.`id`),'$contactId' $insertContactsQry)");

            // Insert Assignment Log.
            $conn->executeQuery('INSERT INTO `fg_cm_log_assignment` (`contact_id`,`category_club_id`,`role_type`,`category_id`,`role_id`,`function_id`,`date`,`category_title`,`value_before`,`value_after`,`changed_by`) '
                    ."(SELECT c.`id`,'$clubId','club','$categoryId','$roleId','','$currentDate',(SELECT `title` FROM `fg_rm_category` WHERE `id`=$categoryId),'-',(SELECT `title` FROM `fg_rm_role` WHERE `id`=$roleId),'$contactId' $insertContactsQry)");
        }

        $this->processFilterContactstoRoleContacts($roleId, $filterId, $categoryId, $rmCrfId, $container, $contactId, $club, $clubId, $roleTitle, $delExcludedCnts, $newExcludedCnts);
    }

    /**
     * Function to update filter role contacts.
     *
     * @param int    $roleId         Role Id
     * @param int    $filterId       Filter Id
     * @param int    $categoryId     Category Id
     * @param int    $rmCrfId        Cat_role_fun Unique Id
     * @param object $container      Container object
     * @param int    $contactId      Contact Id
     * @param object $club           Club Object
     * @param int    $clubId         Club Id
     * @param string $roleTitle      Role Title
     * @param array  $addRoleLogCnts Role log added contacts
     * @param array  $delRoleLogCnts Role log added contacts
     */
    public function processFilterContactstoRoleContacts($roleId, $filterId, $categoryId, $rmCrfId, $container, $contactId, $club, $clubId, $roleTitle, $addRoleLogCnts, $delRoleLogCnts)
    {
        $conn = $this->getEntityManager()->getConnection();
        $currentDate = date('Y-m-d H:i:s');
        $categoryId = intval($categoryId);
        $roleId = intval($roleId);
        $clubId = intval($clubId);
        $rmCrfId = intval($rmCrfId);
        $contactId = intval($contactId);
        $roleTitle = FgUtility::getSecuredData($roleTitle, $conn);
        $arrFiltercontactsNew = array();
        $roleConArrOld = array();
        $diffArrToadd = array();
        $diffArrTodelete = array();
        $count = 0;
        //for execeptions
        $includedContacts = $this->_em->getRepository('CommonUtilityBundle:FgRmRoleManualContacts')->getexeceptionContactIds($roleId, 'included');
        $excludedContactsArr = $this->_em->getRepository('CommonUtilityBundle:FgRmRoleManualContacts')->getexeceptionContactIds($roleId, 'excluded');
        //ends
        //taking contacts for a particular filter
        $filterDetails = $this->_em->getRepository('CommonUtilityBundle:FgFilter')->getFilterRoledata($roleId);
        $jString = $filterDetails['filterData'];

        if ($jString != '') {
            $club = $container->get('club');
            $contactlistClass = new Contactlist($container, $contactId, $club);
            $contactlistClass->setColumns(array('contactid'));
            $contactlistClass->setFrom();
            $contactlistClass->setCondition();
            $filterarr = json_decode($jString, true);
            if (is_array($filterarr)) {
                $filter = array_shift($filterarr);
                $filterObj = new Contactfilter($container, $contactlistClass, $filter);
                if ($filter) {
                    $sWhere = ' '.$filterObj->generateFilter();
                    $contactlistClass->addCondition($sWhere);
                }
                $totallistquery = $contactlistClass->getResult();

                $totalcontactlistDatas = $this->_em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($totallistquery);
                $count = count($totalcontactlistDatas);
            }
        }

        if ($count) {
            foreach ($totalcontactlistDatas as $key => $val) {
                $arrFiltercontactsNew[$filterId][] = $val['id'];
            }
        } else {
            $arrFiltercontactsNew[$filterId] = array();
        }

        if (count($includedContacts)) {
            foreach ($includedContacts as $key => $val) {
                if (!in_array($val['contact_id'], $arrFiltercontactsNew[$filterId])) {
                    $arrFiltercontactsNew[$filterId][] = $val['contact_id'];
                }
            }
        }

        $excludedContacts = array();
        if (count($excludedContactsArr)) {
            foreach ($excludedContactsArr as $key1 => $val1) {
                $excludedContacts[] = $val1['contact_id'];
                if (in_array($val1['contact_id'], $arrFiltercontactsNew[$filterId])) {
                    $excludeKey = array_search($val1['contact_id'], $arrFiltercontactsNew[$filterId]);
                    unset($arrFiltercontactsNew[$filterId][$excludeKey]);
                }
            }
        }

        $arrFiltercontactsNew[$filterId] = array_unique($arrFiltercontactsNew[$filterId]);

        if (!$rmCrfId) {
            $qry = "INSERT INTO fg_rm_category_role_function (category_id,role_id,club_id) VALUES($categoryId, $roleId, $clubId)";
            $stmt = $conn->executeQuery($qry);
            $qryLastId = "SELECT id as lastid FROM fg_rm_category_role_function WHERE role_id = '$roleId' AND  category_id = '$categoryId' AND  club_id = '$clubId' ORDER BY id DESC;";
            $lastId = $conn->executeQuery($qryLastId)->fetch();
            $rmCrfId = $lastId['lastid'];
        }

        if ($rmCrfId) {
            $fgRmCategoryArr = $this->_em->getRepository('CommonUtilityBundle:FgRmCategoryRoleFunction')->getCategoryRoleDetails($rmCrfId);

            foreach ($fgRmCategoryArr as $fgRmCategory) {
                if (is_null($fgRmCategory['function_id'])) {
                    $roleConObj = $this->_em->getRepository('CommonUtilityBundle:FgRmRoleContact')->getfilterContactsFromCrf($rmCrfId);

                    if (count($roleConObj)) {
                        foreach ($roleConObj as $rolconobj) {
                            $roleConArrOld[$fgRmCategory['id']][] = $rolconobj['contact_id'];
                        }
                    } else {
                        $roleConArrOld[$fgRmCategory['id']][] = '';
                    }

                    if (count($arrFiltercontactsNew[$filterId]) || count($roleConArrOld[$fgRmCategory['id']])) {
                        $diffArrToadd = array_diff($arrFiltercontactsNew[$filterId], $roleConArrOld[$fgRmCategory['id']]);
                        //$roleOldConts = array_diff($roleConArrOld[$fgRmCategory['id']], $excludedContacts);
                        $diffArrTodelete = array_diff($roleConArrOld[$fgRmCategory['id']], $arrFiltercontactsNew[$filterId]);
                        $diffArrToadd = array_unique($diffArrToadd);
                        $diffArrTodelete = array_unique($diffArrTodelete);
                        //$diffArrToadd = array_diff($diffArrToadd, $addRoleLogCnts);
                        //$diffArrTodelete = array_diff($diffArrTodelete, $delRoleLogCnts);
//                        if ($diffArrTodelete != $delRoleLogCnts) {
                        //$diffArrTodelete = array_diff($diffArrTodelete, $delRoleLogCnts);
//                        }
                        if (count($diffArrToadd) > 0) {
                            $this->_em->getRepository('CommonUtilityBundle:FgRmCategoryRoleFunction')->saveFilterRoleAssignments($conn, $clubId, $categoryId, $roleId, $rmCrfId, $diffArrToadd, $contactId, $currentDate, $addRoleLogCnts);
                        }

                        if (count($diffArrTodelete) > 0) {
                            $this->_em->getRepository('CommonUtilityBundle:FgRmCategoryRoleFunction')->removeFilterRoleAssignments($conn, $clubId, $categoryId, $roleId, $rmCrfId, $diffArrTodelete, $contactId, $currentDate, $delRoleLogCnts);
                        }
                    }
                }
            }
        }
        // Update `filter_updated` date of Filter Role.
        $conn->executeQuery("UPDATE `fg_rm_role` SET `filter_updated`='$currentDate' WHERE id = $roleId");
    }

    /**
     * Fuction to get the Contact name.
     *
     * @param object $container Container object
     * @param int    $contactId Contact id
     * @param object $conn      Connection object
     *
     * @return string $contactName  Contact name
     */
    public function getContactName($container, $contactId, $conn)
    {
        if ($contactId) {
            $contactId = intval($contactId);
            $club = $container->get('club');
            $contactlistClass = new Contactlist($container, '', $club);
            $contactlistClass->setColumns(array('contactName', 'contactname', 'contactid', 'clubId', 'is_company', 'hasMainContact', 'fedMembershipId'));
            $contactlistClass->setFrom('*');
            $contactlistClass->setCondition();
            $sWhere = " id=$contactId";
            $contactlistClass->addCondition($sWhere);
            $listquery = $contactlistClass->getResult();
            $fieldsArray = $conn->fetchAll($listquery);
        }
        $contactName = $fieldsArray[0];

        return $contactName;
    }

    /**
     * Function to get filter driven roles to update contact using cron.
     *
     * @param int $categoryId Category Id.
     *
     * @return array $roleIdArr Result array of role ids.
     */
    public function getAllFilterRoles()
    {
        $roleIds = $this->createQueryBuilder('r')
                ->select('r.id as roleId, IDENTITY(rc.club) AS clubId')
                ->innerJoin('CommonUtilityBundle:FgRmCategory', 'rc', 'WITH', 'r.category=rc.id')
                ->where('r.filter IS NOT NULL')
                // ->andWhere('r.id = 4507')
                ->orderBy('rc.club', ' ASC');

        $dataResult = $roleIds->getQuery()->getResult();

        return $dataResult;
    }

    /**
     * Function to get id,title of team/workgroup in a category.
     *
     * @param string $type            team or workgroup
     * @param int    $typeId          categoryId
     * @param string $clubDefaultLang clubDefaultLanguage
     * @param bool   $getRolesOnly    Whether to return only roles
     * @param bool   $sortResult      Whether to get resulting array on the basis of sort order
     *
     * @return array $dataResult
     */
    public function getTeamsOrWorkgroupsForDocumentsDepositedOption($type = 'team', $typeId = 0, $clubDefaultLang = 'de', $getRolesOnly = false, $sortResult = false, $container = false)
    {
        $dataResult = array();
        $termExecutive = $container ? ucfirst($container->get('fairgate_terminology_service')->getTerminology('Executive Board', $container->getParameter('singular'))) : 'Executive';
        if ($typeId != 0) {
            $catType = ($type == 'team') ? 'T' : 'W';
            $roleArr = $this->createQueryBuilder('r')
                    ->select("r.id as roleId,(CASE WHEN r.isExecutiveBoard=1 THEN '".$termExecutive."' WHEN (ri18n.titleLang IS NULL OR ri18n.titleLang = '') THEN r.title ELSE ri18n.titleLang END) AS roleTitle")
                    ->leftJoin('CommonUtilityBundle:FgRmRoleI18n', 'ri18n', 'WITH', "ri18n.id=r.id AND ri18n.lang='".$clubDefaultLang."'");
            if ($sortResult && ($type == 'team')) {
                $roleArr = $roleArr->leftJoin('CommonUtilityBundle:FgTeamCategory', 'tc', 'WITH', 'tc.id = r.teamCategory');
            }
            $roleArr = $roleArr->where('r.type=:catType')
                    ->andWhere('r.category=:catId')
                    ->setParameter('catType', $catType)
                    ->setParameter('catId', $typeId);
            if ($sortResult) {
                if ($type == 'team') {
                    $roleArr = $roleArr->orderBy('tc.sortOrder', 'ASC')->addOrderBy('r.sortOrder', 'ASC');
                } else {
                    $roleArr = $roleArr->orderBy('r.sortOrder', 'ASC');
                }
            } else {
                $roleArr = $roleArr->orderBy('r.id', 'ASC');
            }
            $roleArr = $roleArr->getQuery()->getResult();
            if ($getRolesOnly) {
                return $roleArr;
            }
            foreach ($roleArr as $role) {
                $dataResult['roles'][$role['roleId']] = $role['roleTitle'];
            }
            if ($type == 'team') {
                $dataResult['functions'] = $this->_em->getRepository('CommonUtilityBundle:FgRmFunction')->getAllTeamFunctionsOfAClub($typeId, $clubDefaultLang);
            }
        }

        return $dataResult;
    }

    /**
     * Method to retrieve role-category details.
     *
     * @param type $catIds
     *
     * @return array result
     */
    public function getRoleFunctionDetails($catIds)
    {
        $conn = $this->getEntityManager()->getConnection();
        $roleFunctionDetails = $conn->executeQuery('SELECT c.`id` AS category_id, c.`club_id` AS catClubId, c.`title` AS catTitle,'
                        .' c.`is_fed_category` AS isFedCategory, '
                        .'c.`role_assign` AS roleAssign, c.`is_team` AS isTeam, c.`is_workgroup` AS isWrkgrp'
                        .', GROUP_CONCAT(r.`id`) AS roleIds, GROUP_CONCAT(f.`id`) AS functionIds, GROUP_CONCAT(r.`title`) AS roleNames, '
                        .'GROUP_CONCAT(f.`title`) AS functionNames '
                        .'FROM `fg_rm_category` c '
                        .'LEFT JOIN `fg_rm_role` r ON (r.category_id = c.id) '
                        .'LEFT JOIN `fg_rm_function` f ON (f.category_id = c.id) '
                        ."WHERE c.id IN ($catIds) AND c.contact_assign = 'manual' "
                        .'GROUP BY c.id')->fetchAll();

        return $roleFunctionDetails;
    }

    /**
     * get category role of teams.
     *
     * @param int $club clubid
     *
     * @return array
     */
    public function getRoleTeamDetails($club, $container)
    {
        $translator = $container->get('translator');
        $terminologyService = $container->get('fairgate_terminology_service');
        $termExecutive = ucfirst($container->get('fairgate_terminology_service')->getTerminology('Executive Board', $container->getParameter('singular')));
        $conn = $this->getEntityManager()->getConnection();

        $clubRoleTeam = $conn->executeQuery('SELECT r.type,tc.id as teamCatId,r.id as roleId,tc.title as tcTitle, '
                        ."(CASE WHEN r.title = 'Executive Board' THEN '$termExecutive' ELSE r.title END) AS rTitle "
                        .'FROM fg_rm_category c LEFT JOIN fg_rm_role r ON c.id=r.category_id LEFT JOIN fg_team_category tc ON r.team_category_id=tc.id '
                        ."WHERE r.club_id = {$club} AND r.type IN ('W','T') ORDER BY tc.sort_order,r.sort_order")
                ->fetchAll();

        return $clubRoleTeam;
    }

    /**
     * Function to get all roles in team and workgroup category of a club.
     *
     * @param object $container    Container Object
     * @param array  $roleCatIdArr Category Id Array
     *
     * @return array $roles RolesArray
     */
    public function getAllActiveRolesOfAClub($container = false, $roleCatIdArr = array())
    {
        $clubDefaultLang = $container ? $container->get('club')->get('default_lang') : 'de';
        $termExecutive = $container ? ucfirst($container->get('fairgate_terminology_service')->getTerminology('Executive Board', $container->getParameter('singular'))) : 'Executive';
        $query = $this->createQueryBuilder('r')
                ->select("r.id as roleId, (CASE WHEN r.isExecutiveBoard=1 THEN '".$termExecutive."' WHEN (ri18n.titleLang IS NULL OR ri18n.titleLang = '') THEN r.title ELSE ri18n.titleLang END) AS roleTitle, r.type AS roleType")
                ->leftJoin('CommonUtilityBundle:FgRmRoleI18n', 'ri18n', 'WITH', "ri18n.id=r.id AND ri18n.lang='".$clubDefaultLang."'");
        $query = $query->leftJoin('CommonUtilityBundle:FgTeamCategory', 'tc', 'WITH', 'tc.id = r.teamCategory');

        $query = $query->add('where', $query->expr()->andX($query->expr()->in('r.category', ':categoryIdArr'), $query->expr()->eq('r.isActive', ':isActive')));
        $query = $query->setParameters(array('categoryIdArr' => $roleCatIdArr, 'isActive' => 1));

        $query = $query->orderBy('tc.sortOrder', 'ASC')->addOrderBy('r.sortOrder', 'ASC');

        $result = $query->getQuery()->getResult();

        $teams = array();
        $workgroups = array();
        foreach ($result as $key => $valArray) {
            if ($valArray['roleType'] == 'T') {
                $teams[$valArray['roleId']] = strip_tags($valArray['roleTitle']);
            } elseif ($valArray['roleType'] == 'W') {
                $workgroups[$valArray['roleId']] = strip_tags($valArray['roleTitle']);
        }
        }
        $roles = array('teams' => $teams, 'workgroups' => $workgroups);

        return $roles;
    }

    /**
     * Function to get team ids of a given category in order.
     *
     * @param int $categoryId Category id
     *
     * @return array $teamIds Team ids
     */
    public function getTeamIds($categoryId)
    {
        $query = $this->createQueryBuilder('r')
                ->select('GROUP_CONCAT(r.id ORDER BY tc.sortOrder ASC, r.sortOrder ASC) as teamIds')
                ->leftJoin('CommonUtilityBundle:FgTeamCategory', 'tc', 'WITH', 'tc.id = r.teamCategory')
                ->where('r.category=:categoryId')
                ->andWhere('r.isActive=:isActive')
                ->setParameters(array('categoryId' => $categoryId, 'isActive' => 1))
                ->getQuery()
                ->getResult();

        $teamIds = ($query[0]['teamIds'] == '') ? array() : explode(',', $query[0]['teamIds']);

        return $teamIds;
    }

    /**
     * Function to check if the role is owned by given club.
     *
     * @param int $role RoleId
     *
     * @return
     */
    public function checkRole($roleId, $clubId)
    {
        $role = $this->createQueryBuilder('r')
                ->select('r.id as roleId')
                ->where('r.club=:clubId')
                ->andWhere('r.id=:role')
                ->setParameters(array('clubId' => $clubId, 'role' => $roleId));

        $dataResult = $role->getQuery()->getResult();

        return $dataResult[0]['roleId'];
    }

    /**
     * Function to get the color codes for all roles in area colors page(team/workgroup).
     *
     * @param array $roleIds list of team and workgroup ids
     *
     * @return array
     */
    public function getAllRolesColorCode($roleIds)
    {
        $role = $this->createQueryBuilder('r')
                ->select('r.id as roleId, r.calendarColorCode as colorCode, r.isExecutiveBoard')
                ->where('r.id IN (:ids)')
                ->setParameter('ids', $roleIds);

        $dataResult = $role->getQuery()->getResult();
        $finalResult = array();
        foreach ($dataResult as $key => $value) {
            $finalResult[$value['roleId']] = $value;
        }

        return $finalResult;
    }

    /**
     * Function to save calendar color codes in area colours page.
     *
     * @param array $colourCodeArray colour code array
     * @param int   $clubId          current club id
     *
     * @return bool
     */
    public function saveCalendarColourCodes($colourCodeArray, $clubId)
    {
        if (array_key_exists('club', $colourCodeArray)) {
            $clubobj = $this->_em->getRepository('CommonUtilityBundle:FgClub')->find($clubId);
            $clubobj->setCalendarColorCode($colourCodeArray['club'][$clubId]);
            $this->_em->persist($clubobj);
            $this->_em->flush();
        }
        if (array_key_exists('category', $colourCodeArray)) {
            foreach ($colourCodeArray['category'] as $catId => $colourCode) {
                $categoryObj = $this->_em->getRepository('CommonUtilityBundle:FgRmCategory')->find($catId);
                $categoryObj->setCalendarColorCode($colourCode);
                $this->_em->persist($categoryObj);
            }
            $this->_em->flush();
        }
        if (array_key_exists('role', $colourCodeArray)) {
            foreach ($colourCodeArray['role'] as $roleId => $colourCode) {
                $roleObj = $this->_em->getRepository('CommonUtilityBundle:FgRmRole')->find($roleId);
                $roleObj->setCalendarColorCode($colourCode);
                $this->_em->persist($roleObj);
            }
            $this->_em->flush();
        }

        return true;
    }

    /**
     * Function to get maximum sort order of teams.
     *
     * @param int $clubId         Club id
     * @param int $teamCategoryId Team category id
     *
     * @return int $maxSortOrder Maximum sort order.
     */
    public function getMaxSortOrderOfTeams($clubId, $teamCategoryId)
    {
        $maxSortData = $this->createQueryBuilder('r')
                ->select('MAX(r.sortOrder) AS sortOrder')
                ->where('r.club = :clubId')
                ->andWhere('r.teamCategory = :teamCategoryId')
                ->setParameters(array('clubId' => $clubId, 'teamCategoryId' => $teamCategoryId))
                ->getQuery()
                ->getArrayResult();

        $maxSortOrder = $maxSortData[0]['sortOrder'];
        if ($maxSortOrder == '') {
            $maxSortOrder = '0';
        }

        return $maxSortOrder;
    }

    /**
     * Method to get inactive roles of a club as comma separated string.
     *
     * @param int $clubId Current club Id
     *
     * @return string comma separated inactiveRoles
     */
    public function getInactiveRolesOfClub($clubId)
    {
        $inactiveRoles = $this->createQueryBuilder('R')
                ->select('GROUP_CONCAT(R.id)  as roleIds')
                ->where('R.club = :clubId')
                ->andWhere('R.isActive = 0')
                ->setParameters(array('clubId' => $clubId))
                ->getQuery()
                ->getArrayResult();

        return $inactiveRoles[0]['roleIds'];
    }

    /**
     * Function to get the name of a role team or workgroup for album settings page.
     *
     * @param int $clubId      club id
     * @param int $roleId      role id
     * @param int $defaultLang default language
     *
     * @return string or int
     */
    public function getRoleName($clubId, $roleId, $defaultLang)
    {
        $role = $this->createQueryBuilder('r')
                ->select("(CASE WHEN (ri18n.titleLang IS NULL OR ri18n.titleLang = '') THEN r.title ELSE ri18n.titleLang END) AS roleName, r.isExecutiveBoard")
                ->leftJoin('CommonUtilityBundle:FgRmRoleI18n', 'ri18n', 'WITH', 'ri18n.id = r.id AND ri18n.lang=:defaultLang')
                ->where('r.club=:clubId')
                ->andWhere('r.id=:roleId')
                ->setParameters(array('clubId' => $clubId, 'roleId' => $roleId, 'defaultLang' => $defaultLang));
        $result = $role->getQuery()->getResult();

        return $result[0];
    }

    /**
     * Function to get the type of roles .
     * @param int $roleIds  role id
     *
     * @return array $result
     */
    public function getRoleType($roleIds)
    {
        $typeSelect = " , (CASE WHEN (r.type= 'T') THEN 'team' WHEN (r.type='W') THEN 'workgroup' ELSE 'G' END ) AS type";
        $role = $this->createQueryBuilder('r')
            ->select("r.id $typeSelect")
            ->Where('r.id IN (' . $roleIds . ')');

        $result = $role->getQuery()->getResult();

        return $result;
    }

    /**
     * Function to get all roles in team and workgroup category of a club.
     *
     * @param object $container    Container Object
     * @param array  $roleCatIdArr Category Id Array
     *
     * @return array $roles RolesArray
     */
    public function getAllActiveRolesIdsOfAClub($container = false, $roleCatIdArr = array())
    {
        $clubDefaultLang = $container ? $container->get('club')->get('default_lang') : 'de';
        $termExecutive = $container ? ucfirst($container->get('fairgate_terminology_service')->getTerminology('Executive Board', $container->getParameter('singular'))) : 'Executive';
        $query = $this->createQueryBuilder('r')
            ->select("r.id as roleId, (CASE WHEN r.isExecutiveBoard=1 THEN '" . $termExecutive . "' WHEN (ri18n.titleLang IS NULL OR ri18n.titleLang = '') THEN r.title ELSE ri18n.titleLang END) AS roleTitle, r.type AS roleType")
            ->leftJoin('CommonUtilityBundle:FgRmRoleI18n', 'ri18n', 'WITH', "ri18n.id=r.id AND ri18n.lang='" . $clubDefaultLang . "'");
        $query = $query->leftJoin('CommonUtilityBundle:FgTeamCategory', 'tc', 'WITH', 'tc.id = r.teamCategory');

        $query = $query->add('where', $query->expr()->andX($query->expr()->in('r.category', ':categoryIdArr'), $query->expr()->eq('r.isActive', ':isActive')));
        $query = $query->setParameters(array('categoryIdArr' => $roleCatIdArr, 'isActive' => 1));

        $query = $query->orderBy('tc.sortOrder', 'ASC')->addOrderBy('r.sortOrder', 'ASC');

        $result = $query->getQuery()->getResult();

        $roles = array();
        foreach ($result as $valArray) {
            $roles[] = $valArray['roleId'];
        }
        $roleIds = implode(',', $roles);

        return $roleIds;
    }

    /**
     * get Visible for foreign contact roles
     * 
     * @param int       $clubId         clubId
     * @param string    $defaultLang    defaultLang
     * @return array
     */
    public function getVisibleForForeignContactRoles($clubId,$defaultLang){
        $role = $this->createQueryBuilder('r')
            ->leftJoin('CommonUtilityBundle:FgRmRoleI18n', 'r18n','WITH','r.id=r18n.id AND r18n.lang=:defaultLang ')
            ->select("r.id,r.type,(CASE WHEN (r18n.titleLang IS NULL OR r18n.titleLang = '') THEN r.title ELSE r18n.titleLang END) AS roleTitle")
            ->where('r.isActive= :isActive')
            ->andWhere('r.visibleForAll =:visibleForAll')
            ->andWhere('r.club =:club')
            ->setParameters(array('visibleForAll'=>1,'isActive'=>1,'club'=>$clubId,'defaultLang'=>$defaultLang));
        $result = $role->getQuery()->getResult();
        $teams = array();
        $workgroups = array();
        foreach ($result as $val) {
            if ($val['type'] == 'T') {
                $teams[$val['id']] = strip_tags($val['roleTitle']);
            } elseif ($val['type'] == 'W') {
                $workgroups[$val['id']] = strip_tags($val['roleTitle']);
            }
        }
        $roles = array('teams' => $teams, 'workgroups' => $workgroups);

        return $roles;
    }
}
