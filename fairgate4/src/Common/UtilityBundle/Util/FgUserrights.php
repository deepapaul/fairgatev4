<?php

namespace Common\UtilityBundle\Util;

use Clubadmin\Util\Contactlist;
use Common\UtilityBundle\Util\FgUtility;

/**
 * FgUserrights
 *
 * This is used for handling userirghts save
 *
 * @package    CommonUtilityBundle
 * @subpackage Util
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
class FgUserrights
{

    private $conn;
    /* club id */
    private $clubId;
    /* user rights array input */
    private $rightsArray;
    /* $changelogData  Change log entry array */
    private $changelogData = array();
    /* logged contact's id */
    private $loggedContact;
    /* contact ids for updating last updated */
    private $contactForLastUpdated = array();
    /* initially get all the sf guard group names */
    public $groupNames = array();
    public $usersIds = array();
    /* array to keep role names for further use */
    public $roleNames = array();
    public $deleteUserTeam = array();
    public $newUserTeam = array();
    public $dateToday;
    private $deleteUsers = array();
    private $updateSfUserQry = array();
    private $userDelete = array();
    /* array to ids -whether to change fed admin flag in fg cm contact */
    private $updateFedAdmin = array();
    /* array to keep page names for further use */
    public $pageNames = array();
    public $deleteUserPage = array();
    public $newUserPage = array();

    /**
     * userirights construtor
     *
     * @param array $userRightsData formatted userights array
     * @param object $conn connection object
     * @param int $clubId club id
     * @param int $loggedContact logged in user
     * @param object $container object
     */
    public function __construct($userRightsData, $conn, $clubId, $loggedContact, $container)
    {
        $this->container = $container;
        $this->rightsArray = $userRightsData;
        $this->clubId = $clubId;
        $this->loggedContact = $loggedContact;
        $this->conn = $conn;
        $this->em = $this->container->get('doctrine')->getManager();
    }

    /**
     * save userrights - input userrights formatted array
     * @throws \Common\UtilityBundle\Util\Exception
     */
    public function saveUserRights()
    {
        $flag = 0;
        $nowdate = strtotime(date('Y-m-d H:i:s'));
        $this->dateToday = date('Y-m-d H:i:s', $nowdate);
        //get all grp names array for log
        $this->getGroupName();

        try {
            foreach ($this->rightsArray['new'] as $type => $blockData) {
                $flag = 1;
                switch ($type) {
                    case 'teamAdmin':
                        $this->saveGroupAdmin($blockData, 'T');
                        break;
                    case 'pgAdmin':
                        $this->saveGroupAdmin($blockData, 'P');
                        break;
                    case 'wgAdmin':
                        $this->saveGroupAdmin($blockData, 'W');
                        break;
                    case 'teamSection':
                        $this->saveSectionAdmin($blockData, 'T');
                        break;
                    case 'wgSection':
                        $this->saveSectionAdmin($blockData, 'W');
                        break;
                    case 'group':
                        $this->saveGrpContact($blockData);
                        break;
                    case 'teamAdminInt':
                        $this->saveGroupAdmin($blockData, 'T', 'internal');
                        break;
                    case 'wgAdminInt':
                        $this->saveGroupAdmin($blockData, 'W', 'internal');
                        break;
                }
            }
            foreach ($this->rightsArray['delete'] as $type => $blockData) {
                $flag = 1;
                switch ($type) {
                    case 'group':
                        $this->deleteAdmins($blockData);
                        break;
                }
            }

            // Undating sf_guard_user table according to the changes in the administartion admin rights
            if (isset($this->rightsArray['is_security_admin'])) {
                foreach ($this->rightsArray['is_security_admin']['contact'] as $cKey => $cVal) {
                    $this->updateSfUserQry[] = "UPDATE sf_guard_user SET is_security_admin=0 WHERE contact_id=$cKey AND club_id=$this->clubId";
                }
            }
            // Undating sf_guard_user table according to the changes in the readonly admin rights
            if (isset($this->rightsArray['readonly'])) {
                foreach ($this->rightsArray['readonly']['contact'] as $cKey => $cVal) {
                    $this->updateSfUserQry[] = "UPDATE sf_guard_user SET is_readonly_admin=0 WHERE contact_id=$cKey AND club_id=$this->clubId";
                }
            }
            // Undating sf_guard_user table according to the changes in the readonly admin rights/security admin
            if (count($this->userDelete) > 0) {
                $sql = "UPDATE sf_guard_user u left join sf_guard_user_group g on g.user_id=u.id SET u.is_security_admin=0 WHERE u.is_security_admin= 1 AND u.club_id= $this->clubId and u.id NOT IN (select gg.user_id from sf_guard_user_group gg where gg.group_id IN (3,4,5,6) ) AND u.id IN (" . implode(',', $this->userDelete) . "); "
                    . "UPDATE sf_guard_user u left join sf_guard_user_group g on g.user_id=u.id SET u.is_readonly_admin=0 WHERE u.is_readonly_admin= 1 AND u.club_id= $this->clubId and u.id NOT IN (select gg.user_id from sf_guard_user_group gg where gg.group_id IN (7,8) ) AND u.id IN (" . implode(',', $this->userDelete) . ")";
                $this->conn->executeQuery($sql);
            }

            // Inserting user rights in to table
            if (!empty($this->newUserGroup)) {
                $newUserGroupQry = "INSERT INTO sf_guard_user_group (`user_id`,`group_id`,`created_at`,`updated_at`) VALUES " . implode(',', $this->newUserGroup) . " ON DUPLICATE KEY UPDATE `updated_at` = '$this->dateToday';";
                $this->conn->executeQuery($newUserGroupQry);
            }
            if (!empty($this->newUserTeam)) {
                $newUserTeamQry = "INSERT INTO sf_guard_user_team (`user_id`,`group_id`,`role_id`,`created_at`) VALUES " . implode(',', $this->newUserTeam) . ";";
                $this->conn->executeQuery($newUserTeamQry);
            }
            //to insert user pages
            if (!empty($this->newUserPage)) {
                $newUserPageQry = "INSERT INTO sf_guard_user_page (`user_id`,`group_id`,`page_id`,`created_at`) VALUES " . implode(',', $this->newUserPage) . ";";
                $this->conn->executeQuery($newUserPageQry);
            }
            /* execute when userId, teamId, grpId combo is deleted */
            if (!empty($this->deleteUserTeam)) {
                $this->conn->executeQuery(implode(';', $this->deleteUserTeam));
            }
            /* execute when userId, pageId, grpId combo is deleted */
            if (!empty($this->deleteUserPage)) {
                $deleteUserPageQuery = implode(';', $this->deleteUserPage);
                $this->conn->executeQuery($deleteUserPageQuery);
            }
            // Inserting into change log table
            if (count($this->changelogData) > 0) {
                $logHandle = new FgLogHandler($this->container);
                $logHandle->processLogEntryAction('userRights', 'fg_cm_change_log', $this->changelogData);

                //$changelogDataQuery = "INSERT INTO fg_cm_change_log(contact_id, club_id, date, kind, field, value_before, value_after, changed_by) VALUES" . implode(',', $this->changelogData);
                //$this->conn->executeQuery($changelogDataQuery);
            }
            //Insert last updated field in fg_cm_contact
            if (count($this->contactForLastUpdated) > 0) {
                $contactIds = implode(',', $this->contactForLastUpdated);
                $this->em->getRepository('CommonUtilityBundle:FgCmContact')->updateLastUpdated($contactIds, 'id');
            }
            if (count($this->updateFedAdmin) > 0) {
                $contactIds = implode(',', $this->updateFedAdmin);
                $updateFedAdminQuery = " UPDATE fg_cm_contact SET is_fed_admin = CASE WHEN is_fed_admin = 1 THEN 0 ELSE 1 END WHERE id IN ($contactIds) ";
                $this->conn->executeQuery($updateFedAdminQuery);
            }
            //to be execute if any userrights of a team is deleted -
            //delete from sf_guard_user_group if userId, groupId combo doesnot exist anymore
            if (!empty($this->deleteUsers)) {
                $deleteQuery = "DELETE ug FROM sf_guard_user_group ug "
                    . "LEFT JOIN sf_guard_user_team ON sf_guard_user_team.user_id= ug.user_id AND sf_guard_user_team.group_id=ug.group_id "
                    . "LEFT JOIN sf_guard_user_page ON sf_guard_user_page.user_id = ug.user_id AND sf_guard_user_page.group_id = ug.group_id "
                    . "LEFT JOIN sf_guard_group g ON g.id = ug.group_id "
                    . "WHERE g.type IN ('role','page') "
                    . "AND sf_guard_user_team.group_id IS NULL "
                    . "AND sf_guard_user_team.user_id IS NULL "
                    . "AND sf_guard_user_page.group_id IS NULL "
                    . "AND sf_guard_user_page.user_id IS NULL";
                $this->conn->executeQuery($deleteQuery);
            }
            // update sf guard user  table
            if (!empty($this->updateSfUserQry)) {
                $this->conn->executeQuery(implode(';', $this->updateSfUserQry));
            }
        } catch (Exception $ex) {
            $this->conn->rollback();
            $rollback = true;
            throw $ex;
        }

        return $flag;
    }

    /**
     * delete admins - userid grp id combo
     * @param array $blockData userrights array
     */
    private function deleteAdmins($blockData)
    {
        $deleteQuery = '';
        foreach ($blockData as $groupKey => $val) {
            // Generating delete query for each contact id
            // Also generating change log query to undate in the log table for each user right delete
            foreach ($val['user'] as $userKey => $userVal) {
                $this->userDelete[] = $userKey;
                if ($userKey != '') {
                    $deleteQuery.="DELETE FROM sf_guard_user_group WHERE user_id=" . FgUtility::getSecuredData($userKey, $this->conn) . " AND group_id=" . FgUtility::getSecuredData($groupKey, $this->conn) . ";";
                    $contactId = $this->em->getRepository('CommonUtilityBundle:SfGuardUser')->getContactDetails($userKey);
                    if ($groupKey == '17')
                        $this->updateFedAdmin[] = $contactId['id'];
                    // Generating change log query
                    $this->changelogData[] = array('contact_id' => FgUtility::getSecuredData($contactId['id'], $this->conn), 'club_id' => FgUtility::getSecuredData($this->clubId, $this->conn), 'date' => $this->dateToday, 'kind' => 'user rights', 'field' => '', 'value_before' => $this->groupNames[$groupKey], 'value_after' => '');
                    $this->contactForLastUpdated = $contactId['id'];
                }
            }
        }

        // Executing all generated delete query at once
        if ($deleteQuery != '') {
            $this->conn->executeQuery($deleteQuery);
        }
    }

    /**
     * save grp contact combo
     * @param array $blockData userrights array
     */
    private function saveGrpContact($blockData)
    {

        foreach ($blockData as $groupKey => $groupVal) {
            foreach ($groupVal['contact'] as $contactKey => $contactVal) {
                $this->usersIds[$contactKey] = $this->getUserIds($contactKey);
                if ($groupKey == '6' || $groupKey == '3' || $groupKey == '4' || $groupKey == '5') {
                    $this->updateSfUserQry[] = "UPDATE sf_guard_user SET is_security_admin=1 WHERE id=" . $this->usersIds[$contactKey] . " AND club_id=$this->clubId";
                } elseif ($groupKey == '7' || $groupKey == '8') {
                    $this->updateSfUserQry[] = "UPDATE sf_guard_user SET is_readonly_admin=1 WHERE id=" . $this->usersIds[$contactKey] . " AND club_id=$this->clubId";
                } elseif ($groupKey == '17') {
                    $this->updateFedAdmin[] = $contactKey;
                }
                $this->newUserGroup[] = "('" . $this->usersIds[$contactKey] . "','$groupKey','$this->dateToday','$this->dateToday')";
                $this->changelogData[] = array('contact_id' => FgUtility::getSecuredData($contactKey, $this->conn), 'club_id' => FgUtility::getSecuredData($this->clubId, $this->conn), 'date' => $this->dateToday, 'kind' => 'user rights', 'field' => '', 'value_before' => '', 'value_after' => $this->groupNames[$groupKey]);
                $this->contactForLastUpdated[] = $contactKey;
            }
        }
    }

    /**
     * save grp conatct team combo -  grp admins
     *
     * @param array $blockData userirghts array
     * @param string $type team/workgrp
     */
    private function saveGroupAdmin($blockData, $type, $from)
    {

        foreach ($blockData as $groupKey => $groupVal) {
            foreach ($groupVal['contact'] as $contactKey => $contactVal) {
                $this->usersIds[$contactKey] = $this->getUserIds($contactKey);
                if ($from == 'internal') {
                    if (isset($contactVal['delete'])) {
                        foreach ($contactVal['delete'] as $key => $val) {
                            $this->deleteRolesOrPages(array($key), $type, $groupKey, $contactKey);
                        }
                    } else {
                        $this->insertRoles($contactVal['team'], $type, $groupKey, $contactKey);
                    }
                } else {
                    if ($type == 'P') {//Handle Page Admins insertion
                        $existingPages = $this->em->getRepository('CommonUtilityBundle:SfGuardUserPage')->getExistingUserPages($contactKey, $groupKey);
                        $returnArray = $this->compareDeleteInsertPageAdmins($existingPages, $contactVal['team'], $contactKey, $groupKey, $type);
                    } else {
                        $existingRoles = $this->em->getRepository('CommonUtilityBundle:SfGuardUserTeam')->getExistingUserTeams($contactKey, $groupKey, $type);
                        $returnArray = $this->compareDeleteInsertRoleAdmins($existingRoles, $contactVal['team'], $contactKey, $groupKey, $type);
                    }
                }
                $this->contactForLastUpdated[] = $contactKey;
            }
        }
    }

    /**
     * save section admins -  contact team grp combo
     * @param array $blockData userrights array
     * @param string $type team/workgrp
     */
    private function saveSectionAdmin($blockData, $type)
    {

        foreach ($blockData as $contactKey => $contactVal) {
            $this->usersIds[$contactKey] = isset($this->usersIds[$contactKey]) ? $this->usersIds[$contactKey] : $this->getUserIds($contactKey);
            if ($contactVal['deleted'] == 1) {
                $existing = $this->getExistingUserTeam($this->usersIds[$contactKey], $type);
                foreach ($existing as $key => $userTeam) {
                    $this->roleNames[$userTeam['role_id']] = isset($this->roleNames[$userTeam['role_id']]) ? $this->roleNames[$userTeam['role_id']] : $this->roleName($userTeam['role_id']);
                    $groupName = $type . '-' . $this->groupNames[$userTeam['group_id']] . " (" . $this->roleNames[$userTeam['role_id']] . ")";
                    $this->changelogData[] = array('contact_id' => FgUtility::getSecuredData($contactKey, $this->conn), 'club_id' => FgUtility::getSecuredData($this->clubId, $this->conn), 'date' => $this->dateToday, 'kind' => 'user rights', 'field' => '', 'value_before' => $groupName, 'value_after' => '');
                    $this->deleteUserTeam[] = "DELETE FROM sf_guard_user_team WHERE user_id=" . FgUtility::getSecuredData($this->usersIds[$contactKey], $this->conn) . " AND group_id=" . FgUtility::getSecuredData($userTeam['group_id'], $this->conn) . " AND role_id=" . FgUtility::getSecuredData($userTeam['role_id'], $this->conn);
                    $this->deleteUsers[] = $this->usersIds[$contactKey];
                }
                continue;
            }
            foreach ($contactVal['role'] as $roleId => $roleVal) {
                $this->roleNames[$roleId] = isset($this->roleNames[$roleId]) ? $this->roleNames[$roleId] : $this->roleName($roleId);
                $existingGrps = $this->getExistingUserGrps($this->usersIds[$contactKey], $roleId);
                $returnArray = $this->compareDeleteInsertSectionAdmins($existingGrps, $roleVal['groups'], $contactKey, $roleId, $type);
            }
            $this->contactForLastUpdated[] = $contactKey;
        }
    }

    /**
     * Function to check the existing team rights
     * @param type $existingRoles    Existing role array
     * @param type $currentSelection Selected roles
     * @param type $contactKey       Contact Id
     * @param type $groupKey         Group Id
     *
     * @return type Array
     */
    private function compareDeleteInsertRoleAdmins($existingRoles, $currentSelection, $contactKey, $groupKey, $type)
    {

        $deleteRoleId = array();
        $alreadyHave = array();
        foreach ($existingRoles as $roles) {
            if (!in_array($roles['id'], $currentSelection)) {
                $deleteRoleId[] = $roles['id'];
            } else {
                $alreadyHave[] = $roles['id'];
            }
        }
        $newRoles = array_diff($currentSelection, $alreadyHave);


        $this->deleteRolesOrPages($deleteRoleId, $type, $groupKey, $contactKey);
        $this->insertRoles($newRoles, $type, $groupKey, $contactKey);

        return true;
    }

    /**
     * Function to check the existing page rights
     * @param type $existingRoles    Existing role array
     * @param type $currentSelection Selected roles
     * @param type $contactKey       Contact Id
     * @param type $groupKey         Group Id
     *
     * @return type Array
     */
    private function compareDeleteInsertPageAdmins($existingPages, $currentSelection, $contactKey, $groupKey, $type)
    {

        $deletePageId = array();
        $alreadyHave = array();
        foreach ($existingPages as $pages) {
            if (!in_array($pages['page_id'], $currentSelection)) {
                $deletePageId[] = $pages['page_id'];
            } else {
                $alreadyHave[] = $pages['page_id'];
            }
        }
        $newRoles = array_diff($currentSelection, $alreadyHave);
        $this->deleteRolesOrPages($deletePageId, $type, $groupKey, $contactKey);
        $this->insertRoles($newRoles, $type, $groupKey, $contactKey);

        return true;
    }

    /**
     * delete entry from sf guard user team/page
     * @param array     $deleteRoleOrPageId     role id/page id
     * @param string    $type                   role/page
     * @param int       $groupKey               group Id
     * @param int       $contactKey             contactId
     */
    private function deleteRolesOrPages($deleteRoleOrPageId, $type, $groupKey, $contactKey)
    {

        if (!empty($deleteRoleOrPageId)) {
            foreach ($deleteRoleOrPageId as $val) {
                if ($type == 'P') {//type page
                    $this->pageNames[$val] = isset($this->pageName[$val]) ? $this->pageName[$val] : $this->pageName($val);
                    $this->deleteUserPage[] = "DELETE FROM sf_guard_user_page WHERE user_id=" . FgUtility::getSecuredData($this->usersIds[$contactKey], $this->conn) . " AND group_id=" . FgUtility::getSecuredData($groupKey, $this->conn) . "  AND page_id=" . FgUtility::getSecuredData($val, $this->conn);
                    $groupName = $type . '-' . $this->groupNames[$groupKey] . " (" . $this->pageNames[$val] . ")";
                } else {//type team/workgrp
                    $this->roleNames[$val] = isset($this->roleNames[$val]) ? $this->roleNames[$val] : $this->roleName($val);
                    $groupName = $type . '-' . $this->groupNames[$groupKey] . " (" . $this->roleNames[$val] . ")";
                    $this->deleteUserTeam[] = "DELETE FROM sf_guard_user_team WHERE user_id=" . FgUtility::getSecuredData($this->usersIds[$contactKey], $this->conn) . " AND group_id=" . FgUtility::getSecuredData($groupKey, $this->conn) . " AND role_id=" . FgUtility::getSecuredData($val, $this->conn);
                }
                $this->changelogData[] = array('contact_id' => FgUtility::getSecuredData($contactKey, $this->conn), 'club_id' => FgUtility::getSecuredData($this->clubId, $this->conn), 'date' => $this->dateToday, 'kind' => 'user rights', 'field' => '', 'value_before' => $groupName, 'value_after' => '');
                $this->deleteUsers[] = $this->usersIds[$contactKey];
            }
        }
    }

    /**
     * inset entry into sf guard user team
     * @param array $newRoles
     * @param string $type    team/workgrp
     * @param int $groupKey   group Id
     * @param int $contactKey contactId
     */
    private function insertRoles($newRoles, $type, $groupKey, $contactKey)
    {

        foreach ($newRoles as $roleId) {
            if ($type == 'P')
                $this->pageNames[$roleId] = isset($this->pageName[$roleId]) ? $this->pageName[$roleId] : $this->pageName($roleId);
            else
                $this->roleNames[$roleId] = isset($this->roleNames[$roleId]) ? $this->roleNames[$roleId] : $this->roleName($roleId);
            $groupName = ($type == 'P') ? $type . '-' . $this->groupNames[$groupKey] . " (" . $this->pageNames[$roleId] . ")" : $type . '-' . $this->groupNames[$groupKey] . " (" . $this->roleNames[$roleId] . ")";
            $existingUserGroup = $this->getExistingUserGroup($this->usersIds[$contactKey], $groupKey);
            if (!$existingUserGroup) {
                $this->newUserGroup[] = "('" . $this->usersIds[$contactKey] . "','$groupKey','$this->dateToday','$this->dateToday')";
            }
            $this->changelogData[] = array('contact_id' => FgUtility::getSecuredData($contactKey, $this->conn), 'club_id' => FgUtility::getSecuredData($this->clubId, $this->conn), 'date' => $this->dateToday, 'kind' => 'user rights', 'field' => '', 'value_before' => '', 'value_after' => $groupName);
            if ($type == 'P') {
                $this->newUserPage[] = "('" . $this->usersIds[$contactKey] . "','$groupKey','$roleId','$this->dateToday')";
            } else {
                $this->newUserTeam[] = "('" . $this->usersIds[$contactKey] . "','$groupKey','$roleId','$this->dateToday')";
            }
        }
    }

    /**
     * Function to check the existing team rights
     * @param type $existingGrps    Existing grp array
     * @param type $currentSelection Selected roles
     * @param type $contactKey       Contact Id
     * @param type $roleId         Role Id
     *
     * @return type Array
     */
    private function compareDeleteInsertSectionAdmins($existingGrps, $currentSelection, $contactKey, $roleId, $type)
    {

        $deleteGrpId = array();
        $alreadyHave = array();

        foreach ($existingGrps as $grps) {
            if (!in_array($grps['group_id'], $currentSelection)) {
                $deleteGrpId[] = $grps['group_id'];
            } else {
                $alreadyHave[] = $grps['group_id'];
            }
        }
        $newGrps = array_diff($currentSelection, $alreadyHave);
        if (!empty($deleteGrpId)) {
            foreach ($deleteGrpId as $val) {
                $groupName = $type . '-' . $this->groupNames[$val] . " (" . $this->roleNames[$roleId] . ")";
                $this->changelogData[] = array('contact_id' => FgUtility::getSecuredData($contactKey, $this->conn), 'club_id' => FgUtility::getSecuredData($this->clubId, $this->conn), 'date' => $this->dateToday, 'kind' => 'user rights', 'field' => '', 'value_before' => $groupName, 'value_after' => '');
                $this->deleteUserTeam[] = "DELETE FROM sf_guard_user_team WHERE user_id=" . FgUtility::getSecuredData($this->usersIds[$contactKey], $this->conn) . " AND group_id=" . FgUtility::getSecuredData($val, $this->conn) . " AND role_id=" . FgUtility::getSecuredData($roleId, $this->conn);
                $this->deleteUsers[] = $this->usersIds[$contactKey];
            }
        }

        if (!empty($newGrps)) {
            foreach ($newGrps as $grpId) {
                $groupName = $type . '-' . $this->groupNames[$grpId] . " (" . $this->roleNames[$roleId] . ")";
                $existingUserGroup = $this->getExistingUserGroup($this->usersIds[$contactKey], $grpId);
                if (!$existingUserGroup) {
                    $this->newUserGroup[] = "('" . $this->usersIds[$contactKey] . "','$grpId','$this->dateToday','$this->dateToday')";
                }
                $this->changelogData[] = array('contact_id' => FgUtility::getSecuredData($contactKey, $this->conn), 'club_id' => FgUtility::getSecuredData($this->clubId, $this->conn), 'date' => $this->dateToday, 'kind' => 'user rights', 'field' => '', 'value_before' => '', 'value_after' => $groupName);
                $this->newUserTeam[] = "('" . $this->usersIds[$contactKey] . "','$grpId','$roleId','$this->dateToday')";
            }
        }

        return true;
    }

    /**
     * Function to get group name
     *
     * @param int $groupId Group id
     *
     * @return String Group name
     */
    private function getGroupName()
    {

        $grpNameSql = "SELECT name,id FROM sf_guard_group";
        $groupNames11 = $this->conn->executeQuery($grpNameSql)->fetchAll();
        foreach ($groupNames11 as $key => $value) {
            $this->groupNames[$value['id']] = $value['name'];
        }

        return 1;
    }

    /**
     * get user id of a contact in the logged club - return user id
     * if user id is null then insert in user grp and return last inserted id
     *
     * @param int $contactId
     * @return int
     */
    private function getUserIds($contactId)
    {

        $getUserId = "SELECT id FROM sf_guard_user WHERE contact_id=" . FgUtility::getSecuredData($contactId, $this->conn) . " AND club_id=" . FgUtility::getSecuredData($this->clubId, $this->conn);
        $userId = $this->conn->executeQuery($getUserId)->fetchAll();
        if ($userId[0]['id'] == '') {
            $userId = $this->sfUserGroup($contactId);
            return $userId;
        }
        return $userId[0]['id'];
    }

    /**
     * insert in user grp table
     * @param int $contact
     * @return int
     */
    private function sfUserGroup($contact)
    {

        $contactName = $this->contactDetails($contact);
        $firstName = FgUtility::getSecuredData($contactName['firstName'], $this->conn);
        $lastName = FgUtility::getSecuredData($contactName['lastName'], $this->conn);
        $email = FgUtility::getSecuredData($contactName['email'], $this->conn);

        // The function is used to get the password of the user which have the same contact id but from different club
        // In that case, we can use the same password of that user for the newly created user
        $existingPassword = $this->em->getRepository('CommonUtilityBundle:SfGuardUser')->getPasswordEmptyUsers($this->conn, $contact);
        if (!empty($existingPassword)) {
            $existingPassword = $existingPassword[0]['password'];
        } else {
            $existingPassword = '';
        }
        $valueQry[] = "('$firstName','$lastName','$email','$email','$email','$email','$existingPassword','$this->dateToday','$this->dateToday','" . FgUtility::getSecuredData($contact, $this->conn) . "','" . FgUtility::getSecuredData($this->clubId, $this->conn) . "','1','a:0:{}','0','0')";
        // Inserting new user
        $this->em->getRepository('CommonUtilityBundle:SfGuardUser')->insertNewSfUser($this->conn, $valueQry);
        $lastInserted = $this->conn->executeQuery("SELECT LAST_INSERT_ID() AS userId")->fetch();

        return $lastInserted['userId'];
    }

    /**
     * get role names
     * @param string $role
     * @return array
     */
    private function roleName($role)
    {

        $roleNameSql = "SELECT r.title as rTitle FROM fg_rm_role r WHERE r.id =" . $role;
        $roleName = $this->conn->executeQuery($roleNameSql)->fetchAll();
        $returnVal = FgUtility::getSecuredDataString($roleName[0]['rTitle'], $this->conn);

        return $returnVal;
    }

    /**
     * get page names
     * @param string $page
     * @return array
     */
    private function pageName($page)
    {

        $roleNameSql = "SELECT p.title as rTitle FROM fg_cms_page p WHERE p.id =" . $page;
        $roleName = $this->conn->executeQuery($roleNameSql)->fetchAll();
        $returnVal = FgUtility::getSecuredDataString($roleName[0]['rTitle'], $this->conn);

        return $returnVal;
    }

    /**
     * get existing user grp given user id grp id
     * @param int $userId userId
     * @param int $groupId groupId
     * @return int
     */
    private function getExistingUserGroup($userId, $groupId)
    {
        $group = "SELECT g.user_id FROM sf_guard_user_group g WHERE g.user_id=$userId AND g.group_id=$groupId";

        $group = $this->conn->fetchAll($group);
        if (!empty($group)) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * get existing user team given user id grp id and team id
     * @param int $userId user id
     * @param int $teamId team id
     * @return type
     */
    public function getExistingUserGrps($userId, $teamId)
    {
        $sql = "SELECT r.group_id FROM sf_guard_user_team r WHERE r.role_id = $teamId AND r.user_id = $userId AND r.group_id!=9";
        $existingGrp = $this->conn->executeQuery($sql)->fetchAll();
        return $existingGrp;
    }

    /**
     * get existing teams of users
     * @param int $userId user id
     * @param string $type team/workgrp
     * @return array
     */
    private function getExistingUserTeam($userId, $type)
    {

        $sql = "SELECT ut.group_id,ut.role_id FROM sf_guard_user_team ut LEFT JOIN  fg_rm_role r ON ut.role_id=r.id "
            . "LEFT JOIN sf_guard_group g ON ut.group_id=g.id WHERE ut.user_id ='" . $userId . "' AND r.type='" . $type . "' "
            . "AND g.module_type!='all'";
        $existing = $this->conn->fetchAll($sql);

        return $existing;
    }

    /**
     * Function is used to get the contact name using contact id
     *
     * @param int $contactId ContactId
     *
     * @return Array
     */
    private function contactDetails($contactId)
    {

        $club = $this->container->get('club');
        $contactlistClass = new Contactlist($this->container, '', $club); // Calling contact list object
        $contactlistClass->setColumns(array('contactNameYOB', 'contactname', 'ms.`2` as firstName', 'ms.`23` as lastName', 'contactid', 'ms.`3` as email', 'clubId', 'is_company', 'has_main_contact', 'fed_membership_cat_id'));
        $contactlistClass->setFrom('*');
        $contactlistClass->setCondition();
        $sWhere = " fg_cm_contact.id=$contactId";
        $contactlistClass->addCondition($sWhere);
        $listquery = $contactlistClass->getResult();
        $fieldsArray = $this->conn->fetchAll($listquery);

        return $fieldsArray[0];
    }
}
