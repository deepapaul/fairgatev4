<?php

namespace Clubadmin\ContactBundle\Util;

use Common\UtilityBundle\Util\FgLogHandler;
use Common\UtilityBundle\Repository\Pdo\ContactPdo;

/**
 * To confirm or discard fed membership and merge contact.
 *
 * @author  pitsolutions.ch <pit@solutions.com>
 *
 * @version Release: <v4>
 */
class FedMemApplication
{

    /**
     * $em.
     *
     * @var object entitymanager object
     */
    private $em;
    private $conn;
    private $club;
    private $container;
    private $contactId;
    private $fedContactId;
    private $federationId;
    private $subFederationId;
    private $primaryEmail;
    private $confirmDetails;
    private $fedMembership;
    private $contactDet;
    private $fedMemConfirmed = '0';
    private $isMerging = 0;
    private $mergingWithApplication = 0;

    /**
     * Constructor for initial setting.
     *
     * @param type $container container
     */
    public function __construct($container, $contactId = false, $fedContactId = false)
    {
        $this->container = $container;
        $this->contactId = $contactId;
        $this->fedContactId = $fedContactId;
        $this->club = $this->container->get('club');
        $this->federationId = $this->club->get('federation_id');
        $this->subFederationId = $this->club->get('sub_federation_id');
        $this->primaryEmail = $this->container->getParameter('system_field_primaryemail');

        $this->em = $this->container->get('doctrine')->getManager();
        $this->conn = $this->em->getConnection();
    }

    /**
     * Confirm fed membership.
     *
     * @param type $confirmId
     *
     * @return string
     */
    public function confirmFedMembership($confirmId)
    {
        $this->mergingWithApplication = 1;
        $confirmQuery = "SELECT ML.*,CC.id as club_contact_id FROM fg_cm_fedmembership_confirmation_log ML INNER JOIN fg_cm_contact CC ON CC.fed_contact_id=ML.contact_id AND ML.club_id=CC.club_id WHERE ML.id=$confirmId";
        $this->confirmDetails = $this->conn->fetchAll($confirmQuery);
        $this->contactId = $this->confirmDetails[0]['club_contact_id'];
        $decidedBy = $this->container->get('contact')->get('id');
        $oldFedContactId = $this->confirmDetails[0]['contact_id'];
        if ($this->validateFedMem()) {
            $currentDate = date('Y-m-d H:i:s');
            $this->conn->executeQuery("UPDATE fg_cm_contact C SET is_fed_membership_confirmed='{$this->fedMemConfirmed}',is_former_fed_member='0',resigned_on=NULL, last_updated='$currentDate' WHERE C.fed_contact_id={$this->fedContactId}"); //
            $mergeUpdateQry = "";
            if ($this->isMerging == 1) {
                $newClubs = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getClubsOfAContactBeforeMerging($this->fedContactId, array($this->confirmDetails[0]['club_id']));
                $mergeUpdateQry = ",contact_id = {$this->fedContactId},existing_club_ids = '{$newClubs}'";
            }
            $this->conn->executeQuery("UPDATE fg_cm_fedmembership_confirmation_log SET status='CONFIRMED',decided_date=NOW(),decided_by=$decidedBy $mergeUpdateQry WHERE id=$confirmId");
            $this->conn->executeQuery("UPDATE fg_club_assignment CA SET CA.is_approved=1 WHERE CA.fed_contact_id={$this->fedContactId} AND CA.club_id={$this->confirmDetails[0]['club_id']} AND CA.is_approved=0");
            if ($this->isMerging == 0) {
                $membershipProcess = (empty($this->contactDet['old_fed_membership_id'])) ? 'assign' : 'change';
                if ($membershipProcess == 'assign') {
                    $this->em->getRepository('CommonUtilityBundle:FgCmContact')->updateJoiningOrLeavingDatesOfFedMembership($this->fedContactId, 'joiningDate', '', $membershipProcess);
                }
                //insert to fg_cm_membership history
                $this->em->getRepository('CommonUtilityBundle:FgCmMembershipHistory')->insertFedMembershipHistory($this->federationId, $this->fedContactId, $this->fedMembership, $this->container->get('contact')->get('id'));
                $this->writeFedMembershipLog();
            } else {
                $this->conn->executeQuery("DELETE FROM fg_cm_contact WHERE fed_contact_id={$oldFedContactId}");
            }
            //insert sf_guard entry for fed and sub fed level
            $pdo = new ContactPdo($this->container);
            $pdo->insertIntoSfguardUser($this->fedContactId);
        } else {
            return false;
        }

        return true;
    }

    /**
     * Discard fed membership.
     *
     * @param type $confirmId
     */
    public function discardFedMembership($confirmId)
    {
        
       //C.fed_membership_mandatory
        $confirmQuery = "SELECT FCL.*,C.id as ClubId,C.club_type,C.federation_id,C.parent_club_id,CC.id as club_contact_id "
            . "FROM fg_cm_fedmembership_confirmation_log FCL "
            . "INNER JOIN fg_cm_contact CC ON CC.fed_contact_id=FCL.contact_id AND FCL.club_id = CC.club_id "
            . "INNER JOIN fg_club C ON C.id=FCL.club_id WHERE FCL.id=$confirmId";    
        $this->confirmDetails = $this->conn->fetchAll($confirmQuery);
        $fedMembershipMandatory = $this->container->get('fg.admin.connection')->getAdminConnection()->fetchAll("SELECT fed_membership_mandatory  FROM fg_club WHERE id= {$this->confirmDetails[0]['ClubId']}");
        $this->contactId = $this->confirmDetails[0]['club_contact_id'];
        $contactDetails = $this->getContactDetails();
        $decidedBy = $this->container->get('contact')->get('id');
        if (empty($contactDetails['old_fed_membership_id'])) {
            $this->conn->executeQuery("UPDATE fg_cm_contact C SET C.first_joining_date=NULL WHERE C.joining_date=C.first_joining_date AND C.id= {$this->fedContactId}");
            $this->conn->executeQuery("UPDATE fg_cm_contact C SET C.joining_date=NULL WHERE C.id= {$this->fedContactId}");
            $history = $this->conn->fetchAll("SELECT * FROM fg_cm_membership_history WHERE contact_id={$this->fedContactId} AND membership_type='federation' AND joining_date IS NOT NULL AND leaving_date IS NOT NULL ORDER BY joining_date DESC limit 1");
            if (count($history) > 0) {
                $this->conn->executeQuery("UPDATE fg_cm_contact C SET C.joining_date='{$history[0]['joining_date']}',C.leaving_date='{$history[0]['leaving_date']}' WHERE C.id= {$this->fedContactId}");
            }
        }
        if ($fedMembershipMandatory[0]['fed_membership_mandatory'] == 1 && empty($contactDetails['old_fed_membership_id'])) {
            //archive contact
            $this->conn->executeQuery("UPDATE fg_cm_fedmembership_confirmation_log SET status='DECLINED',decided_date=NOW(),decided_by=$decidedBy WHERE id=$confirmId");
            $contactPdo = new ContactPdo($this->container);
            //remove company connection in club level
            $contactPdo->sharedMainContactUpdation($this->fedContactId, $this->fedContactId, $this->confirmDetails[0]['club_id'], 'archive');
            $this->archiveContact();
            $this->conn->executeQuery("UPDATE fg_cm_contact C SET is_fed_membership_confirmed='0',fed_membership_cat_id=NULL WHERE C.fed_contact_id= {$this->fedContactId}");
        } else {
            $membership = empty($contactDetails['old_fed_membership_id']) ? 'NULL' : $contactDetails['old_fed_membership_id'];
            $this->conn->executeQuery("UPDATE fg_cm_contact C SET is_fed_membership_confirmed='0',fed_membership_cat_id=$membership WHERE C.fed_contact_id= {$this->fedContactId}");
            $this->conn->executeQuery("UPDATE fg_cm_fedmembership_confirmation_log SET status='DECLINED',decided_date=NOW(),decided_by=$decidedBy WHERE id=$confirmId");
            if (empty($contactDetails['old_fed_membership_id'])) {
                $this->removeAssignments();
            }
        }
        $this->conn->executeQuery("DELETE CA FROM fg_club_assignment CA WHERE CA.fed_contact_id={$this->fedContactId} AND CA.is_approved=0");
    }

    /**
     * Function to remove assignments.
     */
    private function removeAssignments()
    {
        $this->terminology = $this->container->get('fairgate_terminology_service');

        $clubIdArray = $this->getClubArray();
        if ($clubIdArray['federationId'] > 0) {
            $clubs[] = $clubIdArray['federationId'];
        }
        if ($clubIdArray['subFederationId'] != '') {
            $clubs[] = $clubIdArray['subFederationId'];
        }
        $contactId = 'crc.contact_id=' . $this->fedContactId;
        if (!empty($this->contactDet['subfed_contact_id'])) {
            $contactId .= ' OR crc.contact_id=' . $this->contactDet['subfed_contact_id'];
        }
        if (count($clubs) > 0) {
            //add log entries for fed roles
            $decidedBy = $this->container->get('contact')->get('id');
            $logQuery = "INSERT INTO `fg_cm_log_assignment`(contact_id,category_club_id,role_type,category_id,role_id,function_id,`date`,category_title,value_before,value_after,changed_by) ";
            $logQuery .= "SELECT crc.contact_id,crf.club_id,'fed',rc.id,crf.role_id,crf.function_id,NOW(),rc.title,IF(rf.title IS NULL OR rf.title = '',rl.title,CONCAT(rf.title,' (',rl.title,')')),'-',$decidedBy  FROM fg_rm_role_contact crc
                INNER JOIN fg_rm_category_role_function crf ON crf.id=crc.fg_rm_crf_id
                INNER JOIN fg_rm_category rc ON rc.id=crf.category_id AND rc.is_active=1
				INNER JOIN fg_rm_role rl ON rl.id = crf.role_id
				LEFT JOIN fg_rm_function rf ON rf.id = crf.function_id
                WHERE rc.is_fed_category=1 AND crf.club_id IN (" . implode(',', $clubs) . ") AND ($contactId)";
            $this->conn->executeQuery($logQuery);
            //add remove log enrties for fed role entries
            $rolelogQ = "INSERT INTO `fg_rm_role_log`(club_id,role_id,contact_id,`date`,kind,field,value_before,value_after,changed_by)	"
                . "SELECT crf.club_id,crf.role_id,crc.contact_id,NOW(),'assigned contacts',rl.title,contactNameNoSort(crc.contact_id,0),'-',$decidedBy  FROM fg_rm_role_contact crc "
                . "INNER JOIN fg_rm_category_role_function crf ON crf.id=crc.fg_rm_crf_id "
                . "INNER JOIN fg_rm_category rc ON rc.id=crf.category_id AND rc.is_active=1 "
                . "INNER JOIN fg_rm_role rl ON rl.id = crf.role_id "
                . "WHERE rc.is_fed_category=1 AND crf.club_id IN (" . implode(',', $clubs) . ") AND ($contactId)";
            $this->conn->executeQuery($rolelogQ);
            //add remove log enrties for fed role function entries
            $funcLogQ = "INSERT INTO `fg_rm_function_log`(club_id,role_id,function_id,contact_id,`date`,kind,field,value_before,value_after,changed_by)	"
                . "SELECT crf.club_id,crf.role_id,crf.function_id,crc.contact_id,NOW(),'assigned contacts',rf.title,contactNameNoSort(crc.contact_id,0),'-',$decidedBy  FROM fg_rm_role_contact crc "
                . "INNER JOIN fg_rm_category_role_function crf ON crf.id=crc.fg_rm_crf_id "
                . "INNER JOIN fg_rm_category rc ON rc.id=crf.category_id AND rc.is_active=1 "
                . "INNER JOIN fg_rm_function rf ON rf.id = crf.function_id "
                . "WHERE rc.is_fed_category=1 AND crf.club_id IN (" . implode(',', $clubs) . ") AND ($contactId)";
            $this->conn->executeQuery($funcLogQ);
            //delete fed roles
            $this->conn->executeQuery('DELETE crc FROM fg_rm_role_contact crc
                INNER JOIN fg_rm_category_role_function crf ON crf.id=crc.fg_rm_crf_id
                INNER JOIN fg_rm_category rc ON rc.id=crf.category_id AND rc.is_active=1
                WHERE rc.is_fed_category=1 AND crf.club_id IN (' . implode(',', $clubs) . ") AND ($contactId)");
        }
    }

    /**
     * get club details array.
     *
     * @return type array
     */
    private function getClubArray()
    {
        $details = $this->conn->fetchAll('SELECT CL.* FROM fg_club CL WHERE CL.id=' . $this->confirmDetails[0]['club_id']);
        $subFed = $details[0]['club_type'] == 'sub_federation_club' ? $details[0]['parent_club_id'] : '';
        $container = $this->container->getParameterBag();
        $clubIdArray = array('clubId' => $this->confirmDetails[0]['club_id'],
            'federationId' => $details[0]['federation_id'],
            'subFederationId' => $subFed,
            'clubType' => $details[0]['club_type'],
            'correspondanceCategory' => $container->get('system_category_address'),
            'invoiceCategory' => $container->get('system_category_invoice'),);

        return $clubIdArray;
    }

    /**
     * Get Contact details.
     *
     * @return type
     */
    private function getContactDetails()
    {
        $details = $this->conn->fetchAll("SELECT C.*,MS.*,contactName(C.id) AS contactName FROM fg_cm_contact C INNER JOIN master_system MS ON MS.fed_contact_id=C.fed_contact_id WHERE C.id={$this->contactId}");
        $this->fedContactId = $details[0]['fed_contact_id'];
        $this->fedMembership = $details[0]['fed_membership_cat_id'];
        $this->contactDet = $details[0];

        return $details[0];
    }

    /**
     * Validate fed mem details.
     *
     * @return bool
     */
    private function validateFedMem()
    {
        $contactDetails = $this->getContactDetails();
        if (count($contactDetails) > 0) {
            if ($contactDetails['allow_merging'] == '1' && !empty($contactDetails['merge_to_contact_id'])) {
                $mergeToDetailsQuery = "SELECT C.*,M.* FROM fg_cm_contact C INNER JOIN master_system M ON M.fed_contact_id=C.fed_contact_id WHERE C.id={$contactDetails['merge_to_contact_id']}";
                $mergeToDetails = $this->conn->fetchAll($mergeToDetailsQuery);
                $emailDupliInClub = $this->conn->fetchAll('SELECT C.id FROM fg_cm_contact C '
                    . 'INNER JOIN master_system MS ON MS.fed_contact_id=C.fed_contact_id '
                    . "WHERE C.club_id={$contactDetails['created_club_id']} AND C.is_permanent_delete=0 AND MS.`$this->primaryEmail`!='' AND lower(MS.`$this->primaryEmail`)=lower('{$mergeToDetails[0][$this->primaryEmail]}') AND MS.fed_contact_id !={$contactDetails['fed_contact_id']} AND ((C.main_club_id=C.club_id) OR (C.fed_membership_cat_id IS NOT NULL AND C.is_fed_membership_confirmed='0'))");
                //duplicate email found in club level with mergeTo contact email
                if (count($emailDupliInClub) > 0 && !empty($mergeToDetails[0][$this->primaryEmail])) {
                    return false;
                } else {
                    $clubIdArray = $this->getClubArray();
                    if ($clubIdArray['subFederationId'] != '') {
                        $this->subFederationId = $clubIdArray['subFederationId'];
                    }
                    //if the contact already merged with current club contact
                    $merged = $this->conn->fetchAll("SELECT id FROM fg_cm_contact WHERE club_id={$this->confirmDetails[0]['club_id']} AND fed_contact_id={$contactDetails['merge_to_contact_id']}");
                    if (count($merged) > 0) {
                        return false;
                    }

                    return $this->mergeContact($contactDetails['merge_to_contact_id']);
                }
            } else { //No merging is checked
                //validate fed and sub fed contacts for duplication email for chaned email in club of current contact
                if ($this->validateFedEmails($contactDetails['fed_contact_id'], $contactDetails[$this->primaryEmail]) || empty($contactDetails[$this->primaryEmail])) {
                    if (!empty($contactDetails['old_fed_membership_id'])) {
                        $this->em->getRepository('CommonUtilityBundle:FgCmMembershipHistory')->updateFedMembershipHistory($this->fedContactId, $contactDetails['old_fed_membership_id']);
                    }

                    return true;
                } else {
                    return false;
                }
            }
        }
    }

    /**
     * Validate email in fed and sub fed for duplicate emails.
     *
     * @param type $fedContactId
     *
     * @return bool
     */
    private function validateFedEmails($fedContactId, $email)
    {
        $clubId = $this->container->get('club')->get('id');

        //find sub federation contact id if any
        $contactDetails = $this->conn->fetchAll('SELECT C.* FROM fg_cm_contact C '
            . 'INNER JOIN fg_club CL ON CL.id=C.club_id '
            . "WHERE CL.club_type='sub_federation' AND C.fed_contact_id={$fedContactId} limit 1");
        //check sub federation own contact having same email of club contact
        if (count($contactDetails) > 0) {
            $clubs = "({$contactDetails[0]['club_id']},$clubId)";
        } else {
            $clubs = "($clubId)";
        }
        //check whether fed members having same email of club contact
        $emailExistFedQuery = 'SELECT C.id FROM master_system MS INNER JOIN fg_cm_contact C ON MS.fed_contact_id=C.fed_contact_id '
            . "WHERE lower(MS.`$this->primaryEmail`)=lower('$email') "
            . "AND C.fed_contact_id !='{$this->fedContactId}' AND C.is_permanent_delete='0' AND (C.club_id IN $clubs AND ((C.main_club_id=C.club_id) OR (C.fed_membership_cat_id IS NOT NULL AND (C.old_fed_membership_id IS NOT NULL OR C.is_fed_membership_confirmed='0')))) ";
        $emailExistFed = $this->conn->fetchAll($emailExistFedQuery);
        //return error if duplicate found
        if (count($emailExistFed) > 0) {
            return false;
        }

        return true;
    }

    /**
     * Merge contact.
     *
     * @param type $mergeToContactId
     */
    public function mergeContact($mergeToContactId)
    {
        $contactDetails = $this->getContactDetails();

        $subEntry = "SELECT id,fed_membership_cat_id FROM fg_cm_contact WHERE fed_contact_id={$mergeToContactId} AND club_id={$this->subFederationId}";
        $newContact = $this->conn->fetchAll($subEntry);
        $mergeToDetailsQu = "SELECT id,main_club_id,fed_membership_cat_id,created_club_id,is_fed_membership_confirmed,is_company,has_main_contact,comp_def_contact,comp_def_contact_fun,old_fed_membership_id,fed_membership_assigned_club_id FROM fg_cm_contact WHERE id={$mergeToContactId}";
        $mergeToDetails = $this->conn->fetchAll($mergeToDetailsQu);
        if (empty($mergeToDetails[0]['fed_membership_cat_id']) || $contactDetails['is_company'] != $mergeToDetails[0]['is_company']) {
            return false;
        }
        if ($contactDetails['is_company'] == '0') { //single person
            $this->updateMainContactDetailsOnMerge($this->fedContactId, $mergeToContactId);
        } elseif (!empty($contactDetails['comp_def_contact'])) {
            $this->removeMainContactLog($contactDetails['comp_def_contact']);
        }
        $oldFedMembershipId = empty($mergeToDetails[0]['old_fed_membership_id']) ? 'NULL' : $mergeToDetails[0]['old_fed_membership_id'];
        $fedMembershipAssignedClubId = empty($mergeToDetails[0]['fed_membership_assigned_club_id']) ? 'NULL' : $mergeToDetails[0]['fed_membership_assigned_club_id'];
        $companyMainFields = "has_main_contact='{$mergeToDetails[0]['has_main_contact']}',comp_def_contact='{$mergeToDetails[0]['comp_def_contact']}',comp_def_contact_fun='{$mergeToDetails[0]['comp_def_contact_fun']}'";
        if (count($newContact) > 0) { //Both contacts are from same sub federation
            $this->conn->executeQuery("UPDATE fg_cm_contact SET created_club_id={$mergeToDetails[0]['created_club_id']},is_former_fed_member='0',old_fed_membership_id={$oldFedMembershipId},main_club_id={$mergeToDetails[0]['main_club_id']},fed_contact_id=$mergeToContactId,subfed_contact_id={$newContact[0]['id']},fed_membership_cat_id={$newContact[0]['fed_membership_cat_id']},$companyMainFields,merge_to_contact_id=NULL,allow_merging=false,fed_membership_assigned_club_id={$fedMembershipAssignedClubId} WHERE id={$this->contactId}");
        } else {
            //Two contacts are from two sub federations
            //Update club/subfederation contact details with federation member contact
            $this->conn->executeQuery("UPDATE fg_cm_contact SET created_club_id={$mergeToDetails[0]['created_club_id']},is_former_fed_member='0',main_club_id={$mergeToDetails[0]['main_club_id']},fed_contact_id=$mergeToContactId,fed_membership_cat_id={$mergeToDetails[0]['fed_membership_cat_id']},$companyMainFields,merge_to_contact_id=NULL,allow_merging=false,is_fed_membership_confirmed='0',old_fed_membership_id={$oldFedMembershipId},fed_membership_assigned_club_id={$fedMembershipAssignedClubId} WHERE (club_id={$this->subFederationId} AND fed_contact_id={$this->fedContactId}) OR id={$this->contactId}");
        }
        //updating last_updated date for all shared levels
        $this->em->getRepository('CommonUtilityBundle:FgCmContact')->updateLastUpdated($mergeToContactId, 'fedContact');
        $pdo = new ContactPdo($this->container);
        $pdo->updateSfguardUser($mergeToContactId);
        $pdo->updateSfguardUserPassword($mergeToContactId);
        //delete federation contact details of current club contact
        $oldFedContact = $this->fedContactId;
        if (!$this->mergingWithApplication) {
            $this->conn->executeQuery("DELETE FROM fg_cm_contact WHERE fed_contact_id={$oldFedContact}");
        }
        $this->fedContactId = $mergeToContactId;
        $this->fedMembership = $mergeToDetails[0]['fed_membership_cat_id'];
        $this->fedMemConfirmed = $mergeToDetails[0]['is_fed_membership_confirmed'];
        $this->isMerging = 1;
        $this->em->getRepository('CommonUtilityBundle:FgClubAssignment')->addClubAssignment($mergeToContactId, $contactDetails['main_club_id'], 1);

        return true;
    }

    /**
     * This function is used to insert the federation membership log on assigning or changing fed membership.
     */
    private function writeFedMembershipLog()
    {
        $logArr = array();
        //insert to fg_cm_membership log
        if (!empty($this->fedMembership)) {
            $logArr[] = array('kind' => 'assigned contacts', 'membership_id' => $this->fedMembership, 'value_after' => $this->contactDet['contactName'], 'contact_id' => $this->contactId);
        }
        if (!empty($this->contactDet['old_fed_membership_id'])) {
            $logArr[] = array('kind' => 'assigned contacts', 'membership_id' => $this->contactDet['old_fed_membership_id'], 'value_before' => $this->contactDet['contactName'], 'contact_id' => $this->contactId);
        }
        if (count($logArr) > 0) {
            $logHandlerObj = new FgLogHandler($this->container);
            $logHandlerObj->processLogEntryAction('fedMembershipAssignment', 'fg_cm_membership_log', $logArr);
        }
    }

    /**
     * Archive contact when disarding fed membership from required clubs.
     */
    private function archiveContact()
    {
        $nowdate = strtotime(date('Y-m-d H:i:s'));
        $dateToday = date('Y-m-d H:i:s', $nowdate);
        $clubHeirarchy = array();
        $loginContactId = $this->container->get('contact')->get('id');
        $clubId = $this->confirmDetails[0]['club_id'];
        $clubType = $this->confirmDetails[0]['club_type'];
        $insert = "( '" . $this->contactId . "','" . $clubId . "','" . $loginContactId . "','" . $dateToday . "'),";
        $teamTerminology = ucfirst($this->container->get('fairgate_terminology_service')->getTerminology('Team', $this->container->getParameter('singular')));
        if ($this->confirmDetails[0]['federation_id'] > 0) {
            $clubHeirarchy[] = $this->confirmDetails[0]['federation_id'];
        }
        if ($this->confirmDetails[0]['parent_club_id'] > 0 && $clubType == 'sub_federation_club') {
            $clubHeirarchy[] = $this->confirmDetails[0]['parent_club_id'];
        }

        $this->em->getRepository('CommonUtilityBundle:FgCmContact')->archiveContact($this->contactId, $loginContactId, $insert, $dateToday, $clubId, $this->container->getParameter('system_field_firstname'), $this->container->getParameter('system_field_lastname'), $this->container->getParameter('system_field_companyname'), $this->container->getParameter('system_field_dob'), $teamTerminology, $clubHeirarchy, $clubType, $this->container->getParameter('system_field_primaryemail'), $this->container->getParameter('system_field_salutaion'), $this->container->getParameter('system_field_gender'), $this->container->getParameter('system_field_corress_lang'), date('Y-m-d H:i:s'));
    }

    /**
     * Update main contact details of club contact if merging single persone.
     *
     * @param type $oldFedContact
     * @param type $newFedContact
     */
    public function updateMainContactDetailsOnMerge($oldFedContact, $newFedContact)
    {
        $systmPersonaBothlFields = $this->container->getParameter('system_personal_both');
        $insertFieldsSet = $duplicateSet = array();
        $insertFields = implode('`,`', $systmPersonaBothlFields);
        $result = $this->conn->fetchAll("SELECT `$insertFields` FROM master_system LEFT JOIN fg_cm_contact ON fg_cm_contact.id=master_system.fed_contact_id WHERE fg_cm_contact.id = $newFedContact ");
        foreach ($result[0] as $field => $value) {
            $duplicateSet[":field$field"] = $value;
            $insertFieldsSet[] = "M.`$field` = :field$field";
        }
        if (count($insertFieldsSet) > 0) {
            $insertQuery = 'UPDATE master_system M INNER JOIN fg_cm_contact C ON C.id=M.fed_contact_id SET ' . implode(',', $insertFieldsSet) . " WHERE C.comp_def_contact = $oldFedContact AND C.is_company=1";
            $this->conn->executeQuery($insertQuery, $duplicateSet);
        }
        $this->conn->executeQuery("UPDATE fg_cm_contact C SET C.comp_def_contact=$newFedContact WHERE comp_def_contact=$oldFedContact AND C.is_company=1");
    }

    /**
     * Remove main contact log for old federation contact before merging
     * @param type $mainContact
     */
    public function removeMainContactLog($mainContact)
    {
        $this->conn->executeQuery("DELETE FROM fg_cm_log_connection WHERE (linked_contact_id={$mainContact} OR contact_id = {$mainContact}) AND type='global'");
    }
}
