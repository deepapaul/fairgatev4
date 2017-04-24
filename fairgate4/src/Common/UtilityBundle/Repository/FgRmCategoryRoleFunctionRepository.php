<?php

/**
 * FgRmCategoryRoleFunctionRepository
 *
 * This class is basically used for managing CategoryRoleFunction related functionalities
 */

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Clubadmin\Classes\Contactfilter;
use Clubadmin\Util\Contactlist;

/**
 * FgRmCategoryRoleFunctionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class FgRmCategoryRoleFunctionRepository extends EntityRepository
{

    /**
     * function to do save the role contacts in fg_rm_role_contacts
     *
     * @param Integer $roleId     the role id
     * @param Integer $categoryId the category id
     * @param Integer $clubId     the club id
     * @param Object  $container  the club service
     * @param Integer $contactId  the contact id
     */
    public function saveContactsForFilterRole($roleId, $categoryId, $clubId, $container, $contactId)
    {
        $currentDate = date('Y-m-d H:i:s');
        $filterDetails = $this->_em->getRepository('CommonUtilityBundle:FgFilter')->getFilterRoledata($roleId);
        $jString = $filterDetails['filterData'];
        $conn = $this->getEntityManager()->getConnection();
        $club = $container->get('club');
        //for execeptions.
        $included_contacts = $this->_em->getRepository('CommonUtilityBundle:FgRmRoleManualContacts')->getexeceptionContactIds($roleId, 'included');
        $excluded_contacts = $this->_em->getRepository('CommonUtilityBundle:FgRmRoleManualContacts')->getexeceptionContactIds($roleId, 'excluded');
        //for filter.
        $contactlistClass = new Contactlist($container, $contactId, $club);
        $contactlistClass->setColumns('contactid');
        $contactlistClass->setFrom();
        $contactlistClass->setCondition();
        $filterarr = json_decode($jString, true);
        $filter = array_shift($filterarr);
        $filterObj = new Contactfilter($container, $contactlistClass, $filter, $club);
        $sWhere .= " " . $filterObj->generateFilter();
        $contactlistClass->addCondition($sWhere);
        $totallistquery = $contactlistClass->getResult();
        if ($jString) {
            $totalcontactlistDatas = $this->_em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($totallistquery);
            if (count($totalcontactlistDatas)) {
                foreach ($totalcontactlistDatas as $key => $val) {
                    $contactIds[] = $val['id'];
                }
            }
        }
        $current_role_contacts = array();
        foreach ($contactIds as $val1) {
            $current_role_contacts[$val1] = $val1;
        }

        foreach ($included_contacts as $val2) {
            $current_role_contacts[$val2['contact_id']] = $val2['contact_id'];
        }

        foreach ($excluded_contacts as $val3) {
            unset($current_role_contacts[$val3['contact_id']]);
        }

        if (count($current_role_contacts)) {
            $qry = "INSERT INTO fg_rm_category_role_function (category_id,role_id,club_id) VALUES($categoryId, $roleId, $clubId)";
            $stmt = $conn->executeQuery($qry);
            $qry_last_id = "SELECT id as lastid FROM fg_rm_category_role_function WHERE role_id = '$roleId' AND  category_id = '$categoryId' AND  club_id = '$clubId' ORDER BY id DESC;";
            $last_id = $conn->executeQuery($qry_last_id)->fetch();
            $rm_crf_id = $last_id['lastid'];
            $this->saveFilterRoleAssignments($conn, $clubId, $categoryId, $roleId, $rm_crf_id, $current_role_contacts, $contactId, $currentDate);
        }
    }

    /**
     * function to get the details of category role function
     *
     * @param Integer $id crf id
     *
     * @return Array
     */
    public function getCategoryRoleDetails($id)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT  *
                FROM fg_rm_category_role_function
                WHERE id = $id ";
        $resultArray = $conn->executeQuery($sql)->fetchAll();

        return $resultArray;
    }

    /**
     * Function to save Contact Assignments of Filter Role.
     *
     * @param object $conn                Connection Object
     * @param int    $clubId              Club Id
     * @param int    $catId               Category Id
     * @param int    $roleId              Role Id
     * @param int    $crfId               Unique Id of Category-Role-Function of a Club
     * @param array  $currentRoleContacts Array of Filter Role Contacts
     * @param int    $currentContactId    Current Logged-in Contact Id
     * @param string $currentDate         Current Date Time
     */
    public function saveFilterRoleAssignments($conn, $clubId, $catId, $roleId, $crfId, $currentRoleContacts, $currentContactId, $currentDate, $logAddedContacts = array())
    {
        $conn->beginTransaction();

        $roleContactIds = implode(',', $currentRoleContacts);
        if (count($logAddedContacts) > 0) {
            $logContacts = array_diff($currentRoleContacts, $logAddedContacts);
            $logContactIds = implode(',', $logContacts);
        } else {
            $logContactIds = $roleContactIds;
        }

        if ($roleContactIds != '') {
            $contactsQry = "FROM `fg_cm_contact` c WHERE c.`id` IN ($roleContactIds)";
            // Assign contacts to Filter Role.
            $conn->executeQuery("INSERT INTO `fg_rm_role_contact` (`contact_id`,`fg_rm_crf_id`,`assined_club_id`,`contact_club_id`,`update_time`) "
                    . "(SELECT c.`id`,'$crfId','$clubId','$clubId','$currentDate' $contactsQry) ON DUPLICATE KEY UPDATE `contact_id`=VALUES(`contact_id`), `fg_rm_crf_id`=VALUES(`fg_rm_crf_id`)");
            //update contact last updated field
            $currentDate = date('Y-m-d H:i:s');
            $this->_em->getConnection()->executeQuery("UPDATE fg_cm_contact SET last_updated='$currentDate' WHERE id IN ($roleContactIds)");
        }
        if ($logContactIds != '') {
            $contactsQry = "FROM `fg_cm_contact` c WHERE c.`id` IN ($logContactIds)";
            // Insert Role Log.
            $conn->executeQuery("INSERT INTO `fg_rm_role_log` (`club_id`,`role_id`,`date`,`kind`,`field`,`value_before`,`value_after`,`changed_by`) "
                    . "(SELECT '$clubId','$roleId','$currentDate','assigned contacts','','-',contactName(c.`id`),'$currentContactId' $contactsQry)");
            // Insert Assignment Log.
            $conn->executeQuery("INSERT INTO `fg_cm_log_assignment` (`contact_id`,`category_club_id`,`role_type`,`category_id`,`role_id`,`function_id`,`date`,`category_title`,`value_before`,`value_after`,`changed_by`) "
                    . "(SELECT c.`id`,'$clubId','club','$catId','$roleId','','$currentDate',(SELECT `title` FROM `fg_rm_category` WHERE `id`=$catId),'-',(SELECT `title` FROM `fg_rm_role` WHERE `id`=$roleId),'$currentContactId' $contactsQry)");
        }
        // Update `filter_updated` date of Filter Role.
        $conn->executeQuery("UPDATE `fg_rm_role` SET `filter_updated`='$currentDate' WHERE id = $roleId");

        $conn->commit();
    }

    /**
     * Function to remove Contacts Assignments if Filter Role.
     *
     * @param object $conn               Connection Object
     * @param int    $clubId             Club Id
     * @param int    $catId              Category Id
     * @param int    $roleId             Role Id
     * @param int    $crfId              Unique Id of Category-Role-Function of a Club
     * @param array  $removeRoleContacts Array of Contacts to Remove
     * @param int    $currentContactId   Current Contact Id
     * @param string $currentDate        Current Date Time
     */
    public function removeFilterRoleAssignments($conn, $clubId, $catId, $roleId, $crfId, $removeRoleContacts, $currentContactId, $currentDate, $logAddedContacts = array())
    {
        $roleContactIds = implode(',', $removeRoleContacts);
        if (count($logAddedContacts) > 0) {
            $logContacts = array_diff($removeRoleContacts, $logAddedContacts);
            $logContactIds = implode(',', $logContacts);
        } else {
            $logContactIds = $roleContactIds;
        }
        $conn->beginTransaction();
        if ($roleContactIds != '') {
            $contactsQry = "FROM `fg_cm_contact` c WHERE c.`id` IN ($roleContactIds)";
            // Remove Contacts from Filter Role.
            $conn->executeQuery("DELETE FROM `fg_rm_role_contact` WHERE `contact_id` IN ($roleContactIds) AND `fg_rm_crf_id` = $crfId");
            //update contact last updated field
            $currentDate = date('Y-m-d H:i:s');
            $this->_em->getConnection()->executeQuery("UPDATE fg_cm_contact SET last_updated='$currentDate' WHERE id IN ($roleContactIds)");
        }
        if ($logContactIds != '') {
            $contactsQry = "FROM `fg_cm_contact` c WHERE c.`id` IN ($logContactIds)";
            // Insert Role Log.
            $conn->executeQuery("INSERT INTO `fg_rm_role_log` (`club_id`,`role_id`,`date`,`kind`,`field`,`value_before`,`value_after`,`changed_by`) "
                    . "(SELECT '$clubId','$roleId','$currentDate','assigned contacts','',contactName(c.`id`),'-','$currentContactId' $contactsQry)");
            // Insert Assignment Log.
            $conn->executeQuery("INSERT INTO `fg_cm_log_assignment` (`contact_id`,`category_club_id`,`role_type`,`category_id`,`role_id`,`function_id`,`date`,`category_title`,`value_before`,`value_after`,`changed_by`) "
                    . "(SELECT c.`id`,'$clubId','club','$catId','$roleId','','$currentDate',(SELECT `title` FROM `fg_rm_category` WHERE `id`=$catId),(SELECT `title` FROM `fg_rm_role` WHERE `id`=$roleId),'-','$currentContactId' $contactsQry)");
        }
        $conn->commit();
    }
    
    /**
     * teamname
     * @param obj $conn
     * @param int $roleId
     * @return string
     */
    public function getTeamName($conn,$roleId){
        $team =  $conn->executeQuery("select r.title from fg_rm_role r where r.id = $roleId ")->fetchAll();
        return $team[0]['title'];
    }

}