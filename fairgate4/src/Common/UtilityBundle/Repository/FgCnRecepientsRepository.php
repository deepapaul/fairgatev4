<?php

/**
 * This class is used for handling newsletter and simple email recipients in Communication module.
 */
namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Clubadmin\Util\Contactlist;
use Clubadmin\Util\Contactfilter;
use Common\UtilityBundle\Util\FgSettings;
use Common\UtilityBundle\Util\FgUtility;

/**
 * FgCnRecepientsRepository
 *
 * This class is used for handling newsletter and simple email recipients in Communication module.
 */
class FgCnRecepientsRepository extends EntityRepository
{

    private $delRecipientsArray = array();
    private $insertDataArray = array();
    private $updateDataArray = array();
    private $addExceptionsArray = array();
    private $removeExceptionsArray = array();
    private $addEmailSettingsArray = array();
    private $removeMainEmailsArray = array();
    private $removeSubstituteEmailsArray = array();
    private $toUpdateRecipientListContacts = array();

    /**
     * Function to get Recipients List of a Club for a particular Newsletter type.
     *
     * @param int    $clubId         Club Id
     *
     * @return array $recipientsList Array of Recipients List.
     */
    public function getRecipientsList($clubId)
    {
        $dateFormat = FgSettings::getMysqlDateTimeFormat();
        // Configuring Doctrine to get DateFormat Extension
        $doctrineConfig = $this->getEntityManager()->getConfiguration();
        $doctrineConfig->addCustomStringFunction('DATE_FORMAT', 'Common\UtilityBundle\Extensions\DateFormat');
        $recListData = $this->createQueryBuilder('rl')
            ->select("rl.id, rl.name, rl.isAllActive, rl.filterData, rl.sortOrder, rl.contactCount, rl.mandatoryCount, rl.subscriberCount, "
                . "rm.selectionType, rm.emailType, IDENTITY(rm.emailField) AS emailField, DATE_FORMAT( rl.updatedAt,'$dateFormat' ) AS updatedAt")
            ->addSelect("(SELECT GROUP_CONCAT(re1.contact) FROM CommonUtilityBundle:FgCnRecepientsException re1 WHERE re1.recepientList = rl.id AND re1.type = 'included') AS includedContacts")
            ->addSelect("(SELECT GROUP_CONCAT(re2.contact) FROM CommonUtilityBundle:FgCnRecepientsException re2 WHERE re2.recepientList = rl.id AND re2.type = 'excluded') AS excludedContacts")
            ->leftJoin('CommonUtilityBundle:FgCnRecepientsEmail', 'rm', 'WITH', '(rm.recepientList = rl.id)')
            ->where('rl.club=:clubId')
            ->orderBy('rl.isAllActive', 'DESC')
            ->addOrderBy('rl.sortOrder', 'ASC')
            ->setParameter('clubId', $clubId)
            ->getQuery()
            ->getArrayResult();
        $recipientsList = array();
        foreach ($recListData as $recipientList) {
            if (!isset($recipientsList[$recipientList['sortOrder']])) {
                $recipientsList[$recipientList['sortOrder']] = array(
                    'id' => $recipientList['id'],
                    'name' => $recipientList['name'],
                    'isAllActive' => $recipientList['isAllActive'],
                    'sortOrder' => $recipientList['sortOrder'],
                    'filterData' => $recipientList['filterData'],
                    'includedContacts' => $recipientList['includedContacts'],
                    'excludedContacts' => $recipientList['excludedContacts'],
                    'mainEmailIds' => '',
                    'substituteEmailId' => '',
                    'isNew' => false,
                    'contactCount' => $recipientList['contactCount'],
                    'mandatoryCount' => $recipientList['mandatoryCount'],
                    'subscriberCount' => $recipientList['subscriberCount'],
                    'updatedAt' => $recipientList['updatedAt']
                );
            }
            if ($recipientList['selectionType'] == 'main') {
                if ($recipientsList[$recipientList['sortOrder']]['mainEmailIds'] != '') {
                    $recipientsList[$recipientList['sortOrder']]['mainEmailIds'] .= ',';
                }
                $recipientsList[$recipientList['sortOrder']]['mainEmailIds'] .= ($recipientList['emailType'] == 'contact_field') ? $recipientList['emailField'] : $recipientList['emailType'];
            } else if ($recipientList['selectionType'] == 'substitute') {
                $recipientsList[$recipientList['sortOrder']]['substituteEmailId'] = ($recipientList['emailType'] == 'contact_field') ? $recipientList['emailField'] : $recipientList['emailType'];
            }
        }

        return $recipientsList;
    }

    /**
     * Function to get Counts (Contacts count, Mandatory Count, Non-Mandatory Count) of Recipients.
     *
     * @param string $recipientListIds Comma-seperated string of Recipients List Ids
     *
     * @return array $recipientsCounts Counts of Recipients Lists.
     */
    public function getRecipientsCounts($recipientListIds = '')
    {
        $recipientsCounts = array();
        $recipientListIds = explode(',', $recipientListIds);
        foreach ($recipientListIds as $recipientListId) {
            $recipientsCounts[$recipientListId] = array('contactCount' => 155, 'mandatoryCount' => 75, 'nonMandatoryCount' => 55);
        }

        return $recipientsCounts;
    }

    /**
     * Function to get original Ids of Recipient Lists for given temp Ids.
     *
     * @param type $tempIds Temp Ids
     *
     * @return array $ids Array of original ids.
     */
    public function getIdsByTempIds($tempIds = array())
    {
        $ids = array();
        if (count($tempIds) > 0) {
            $recLists = $this->createQueryBuilder('rl')
                ->select('rl.id,rl.tempId')
                ->where('rl.tempId IN (:tempIds)')
                ->setParameter('tempIds', $tempIds)
                ->getQuery()
                ->getArrayResult();

            foreach ($recLists as $recList) {
                $ids[$recList['tempId']] = $recList['id'];
            }
        }

        return $ids;
    }

    /**
     * Function to get exceptions for a given recipient id.
     *
     * @param int $recipientId Recipient Id
     *
     * @return array $exceptions Array of exceptions.
     */
    public function getExceptionsofRecipient($recipientId)
    {
        $exceptionData = $this->createQueryBuilder('rl')
            ->select("rl.id")
            ->addSelect("(SELECT GROUP_CONCAT(re1.contact) FROM CommonUtilityBundle:FgCnRecepientsException re1 WHERE re1.recepientList = rl.id AND re1.type = 'included') AS includedContacts")
            ->addSelect("(SELECT GROUP_CONCAT(re2.contact) FROM CommonUtilityBundle:FgCnRecepientsException re2 WHERE re2.recepientList = rl.id AND re2.type = 'excluded') AS excludedContacts")
            ->where('rl.id=:recipientId')
            ->setParameter('recipientId', $recipientId)
            ->getQuery()
            ->getArrayResult();
        $exceptions = array('included' => $exceptionData['0']['includedContacts'], 'excluded' => $exceptionData['0']['excludedContacts']);

        return $exceptions;
    }

    /**
     * Function to update (Add, Edit, Delete) Recipients List.
     *
     * @param array  $dataArray        Array of data to be updated
     * @param string $currRecipientIds Comma-seperated string of recipient list ids
     * @param int    $clubId           Club Id
     * @param object $container        Container Object
     */
    public function updateRecipientsList($dataArray, $currRecipientIds, $clubId, $container, $currContactId)
    {
        if (count($dataArray) > 0) {
            $clubRecipientIds = explode(',', $currRecipientIds);
            //echo '<pre>';print_r($clubRecipientIds);print_r($dataArray);

            foreach ($dataArray as $recipientId => $recipientArray) {
                if ($recipientId == 'new') {
                    // Add Recipient List.
                    $this->insertDataArray = $recipientArray;
                } else {
                    // Check whether recipient id is valid (is of current club).
//                    if (in_array($recipientId, $clubRecipientIds)) {

                    $delFlag = isset($recipientArray['is_deleted']) ? $recipientArray['is_deleted'] : 0;
                    if (($delFlag == 1) || ($delFlag == '1')) {
                        // Delete Recipient List.
                        $this->delRecipientsArray[] = $recipientId;
                    } else {
                        // Update Recipient List.
                        $this->updateDataArray[$recipientId] = $recipientArray;
                    }
//                    }
                }
            }
            // Execute Queries (Insert, Delete, Update).
            $this->executeQueries($clubId, $container, $currContactId);
        }
    }

    /**
     * Function to Execute Queries for Insertion, Deletion and Updation.
     *
     * @param int    $clubId    Club Id
     * @param object $container Container Object
     */
    private function executeQueries($clubId, $container, $currContactId)
    {
        // Delete Recipients List.
        $this->executeDeleteQuery();

        // Insert Recipients List.
        $this->executeInsertQuery($clubId);

        // Update Recipients List.
        $this->executeUpdateQuery();

        // Remove Exceptions.
        $this->removeExceptions();

        // Add Exceptions.
        $this->addExceptions();

        // Remove Main Emails.
        $this->removeMainEmails();

        // Remove Substitute Emails.
        $this->removeSubstituteEmails();

        // Add Email settings (Add Main Emails & Update Substitute email).
        $this->addEmailSettings();

        // Add Mandatory Recipients.
        //$this->addMandatoryRecipients($container, $currContactId);
        //Insert newly created recipient list's contacts
        $this->updateContactsOfNewlyCreatedRecipientList($container, $currContactId);
    }

    /**
     * Function to Execute Delete Query.
     */
    private function executeDeleteQuery()
    {
        if (count($this->delRecipientsArray)) {
            $em = $this->getEntityManager();
            foreach ($this->delRecipientsArray as $delRecipient) {
                $delRecipients = $em->getRepository('CommonUtilityBundle:FgCnRecepients')->find($delRecipient);
                $em->remove($delRecipients);
            }
            $em->flush();
        }
    }

    /**
     * Function to Execute Insert Query.
     *
     * @param int $clubId Club Id
     */
    private function executeInsertQuery($clubId)
    {
        if (count($this->insertDataArray)) {
            $em = $this->getEntityManager();
            $clubObj = $em->getRepository('CommonUtilityBundle:FgClub')->find($clubId);
            $currDateTime = new \DateTime("now");
            $tempIds = array();
            foreach ($this->insertDataArray as $tempId => $insertDataArr) {
                $tempIds[] = $tempId;
                $filterData = ($insertDataArr['filter_data'] == '') ? '' : $insertDataArr['filter_data'];
                $recipientObj = new \Common\UtilityBundle\Entity\FgCnRecepients();
                $recipientObj->setName($insertDataArr['name'])
                    ->setClub($clubObj)
                    ->setUpdatedAt($currDateTime)
                    ->setFilterData($filterData)
                    ->setIsAllActive('0')
                    ->setContactCount('0')
                    ->setMandatoryCount('0')
                    ->setSubscriberCount('0')
                    ->setSortOrder($insertDataArr['sort_order'])
                    ->setTempId($tempId);
                $em->persist($recipientObj);
            }
            $em->flush();
            // Get original ids and save Exceptions and Email settings.
            $rlIds = $this->getIdsByTempIds($tempIds);
            foreach ($rlIds as $tempId => $recipientId) {
                $this->generateExceptionContactsArray($recipientId, $this->insertDataArray[$tempId]);
                $this->generateEmailSettingsArray($recipientId, $this->insertDataArray[$tempId], true);
                $this->toUpdateRecipientListContacts[] = $recipientId;
            }
        }
    }

    /**
     * Function to Execute Update Query.
     */
    private function executeUpdateQuery()
    {
        if (count($this->updateDataArray)) {
            $em = $this->getEntityManager();
            $currDateTime = new \DateTime("now");
            foreach ($this->updateDataArray as $recipientId => $recipientData) {
                $recipientObj = $em->getRepository('CommonUtilityBundle:FgCnRecepients')->find($recipientId);
                if (isset($recipientData['name'])) {
                    $recipientObj->setName($recipientData['name']);
                }
                if (isset($recipientData['filter_data'])) {
                    $recipientObj->setFilterData($recipientData['filter_data']);
                }
                if (isset($recipientData['sort_order'])) {
                    $recipientObj->setSortOrder($recipientData['sort_order']);
                }
//                $recipientObj->setUpdatedAt($currDateTime);
                $this->generateExceptionContactsArray($recipientId, $recipientData);
                $this->generateEmailSettingsArray($recipientId, $recipientData);
            }
            $em->flush();
        }
    }

    /**
     * Function to generate array for updating Exception Contacts.
     *
     * @param int   $rlId          Recipient Id
     * @param array $recipientData Array of Recipient Data to be saved
     */
    private function generateExceptionContactsArray($rlId, $recipientData)
    {
        $includedContacts = $excludedContacts = array();
        // Add/Remove Included Contacts.
        if (isset($recipientData['included_contacts'])) {
            $includedContacts = ($recipientData['included_contacts'] == '') ? array() : explode(',', $recipientData['included_contacts']);
            $includedContacts = array_unique($includedContacts);
            $this->removeExceptionsArray['included'][] = $rlId;
        }
        // Add/Remove Excluded Contacts.
        if (isset($recipientData['excluded_contacts'])) {
            $excludedContacts = ($recipientData['excluded_contacts'] == '') ? array() : explode(',', $recipientData['excluded_contacts']);
            $excludedContacts = array_unique($excludedContacts);
            $this->removeExceptionsArray['excluded'][] = $rlId;
        }
        // If same contacts are added in included and excluded list, remove them.
        if (count($includedContacts) && count($excludedContacts)) {
            $commonContacts = array_intersect($includedContacts, $excludedContacts);
            $includedContacts = array_diff($includedContacts, $commonContacts);
            $excludedContacts = array_diff($excludedContacts, $commonContacts);
        }
        if (count($includedContacts)) {
            $this->addExceptionsArray[$rlId][] = array('type' => 'included', 'contacts' => $includedContacts);
        }
        if (count($excludedContacts)) {
            $this->addExceptionsArray[$rlId][] = array('type' => 'excluded', 'contacts' => $excludedContacts);
        }
    }

    /**
     * Function to generate array for updating Email Settings.
     *
     * @param int     $rlId          Recipient Id
     * @param array   $recipientData Array of Recipient Data to be saved
     * @param boolean $isNew         Check whether new Recipient list.
     */
    private function generateEmailSettingsArray($rlId, $recipientData, $isNew = false)
    {
        if ($isNew && !isset($recipientData['email'])) {
            $recipientData['email']['main'] = array('3');
        }
        // Email Settings.
        if (isset($recipientData['email'])) {
            if ($isNew && !isset($recipientData['email']['main'])) {
                $recipientData['email']['main'] = array('3');
            }
            if (isset($recipientData['email']['main'])) {
                $this->removeMainEmailsArray[] = $rlId;
                // Add main emails.
                if (count($recipientData['email']['main'])) {
                    $this->addEmailSettingsArray[$rlId]['main'] = $recipientData['email']['main'];
                }
            }
            // Add substitute email.
            if (isset($recipientData['email']['substitute'])) {
                $this->removeSubstituteEmailsArray[] = $rlId;
                if ($recipientData['email']['substitute'] != '') {
                    $this->addEmailSettingsArray[$rlId]['substitute'] = array($recipientData['email']['substitute']);
                }
            }
        }
    }

    /**
     * Function for removing Exception Contacts.
     */
    private function removeExceptions()
    {
        if (count($this->removeExceptionsArray)) {
            foreach ($this->removeExceptionsArray as $type => $recipientIds) {
                $recepientIdStr = implode(',', $recipientIds);
                $conn = $this->getEntityManager()->getConnection();
                $conn->executeQuery("DELETE FROM `fg_cn_recepients_exception` WHERE `recepient_list_id` IN ($recepientIdStr) AND `type`='$type'");
            }
        }
    }

    /**
     * Function for adding Exception Contacts.
     */
    private function addExceptions()
    {
        if (count($this->addExceptionsArray)) {
            $em = $this->getEntityManager();
            foreach ($this->addExceptionsArray as $recipientId => $exceptions) {
                $recipientObj = $em->getRepository('CommonUtilityBundle:FgCnRecepients')->find($recipientId);
                $currExceptions = $em->getRepository('CommonUtilityBundle:FgCnRecepients')->getExceptionsofRecipient($recipientId);
                $currIncludedContacts = ($currExceptions['included'] == '') ? array() : explode(',', $currExceptions['included']);
                $currExcludedContacts = ($currExceptions['excluded'] == '') ? array() : explode(',', $currExceptions['excluded']);
                foreach ($exceptions as $exception) {
                    $type = $exception['type'];
                    $currExceptionContacts = ($type == 'included') ? $currExcludedContacts : $currIncludedContacts;
                    foreach ($exception['contacts'] as $contactId) {
                        // If same contact is there in included and excluded list, it should be removed.
                        if (in_array($contactId, $currExceptionContacts)) {
                            $delType = ($type == 'included') ? 'excluded' : 'included';
                            $delExceptions = $em->getRepository('CommonUtilityBundle:FgCnRecepientsException')->findOneBy(array('recepientList' => $recipientId, 'contact' => $contactId, 'type' => $delType));
                            $em->remove($delExceptions);
                        } else {
                            $contactObj = $em->getRepository('CommonUtilityBundle:FgCmContact')->find($contactId);
                            $exceptionsObj = new \Common\UtilityBundle\Entity\FgCnRecepientsException();
                            $exceptionsObj->setRecepientList($recipientObj)
                                ->setContact($contactObj)
                                ->setType($type);
                            $em->persist($exceptionsObj);
                        }
                    }
                }
            }
            $em->flush();
        }
    }

    /**
     * Function for removing main emails.
     */
    private function removeMainEmails()
    {
        if (count($this->removeMainEmailsArray)) {
            $recepientIdStr = implode(',', $this->removeMainEmailsArray);
            $conn = $this->getEntityManager()->getConnection();
            $conn->executeQuery("DELETE FROM `fg_cn_recepients_email` WHERE `recepient_list_id` IN ($recepientIdStr) AND `selection_type`='main'");
        }
    }

    /**
     * Function for removing main emails.
     */
    private function removeSubstituteEmails()
    {
        if (count($this->removeSubstituteEmailsArray)) {
            $recepientIdStr = implode(',', $this->removeSubstituteEmailsArray);
            $conn = $this->getEntityManager()->getConnection();
            $conn->executeQuery("DELETE FROM `fg_cn_recepients_email` WHERE `recepient_list_id` IN ($recepientIdStr) AND `selection_type`='substitute'");
        }
    }

    /**
     * Function for adding Email settings (Main emails and Substitute email).
     */
    private function addEmailSettings()
    {
        if (count($this->addEmailSettingsArray)) {
            $em = $this->getEntityManager();
            foreach ($this->addEmailSettingsArray as $recipientId => $emailSettings) {
                $recipientObj = $em->getRepository('CommonUtilityBundle:FgCnRecepients')->find($recipientId);
                foreach ($emailSettings as $selectionType => $emails) {
                    foreach ($emails as $email) {
                        $emailType = ($email == 'parent_email') ? 'parent_email' : 'contact_field';
                        $emailsObj = new \Common\UtilityBundle\Entity\FgCnRecepientsEmail();
                        $emailsObj->setRecepientList($recipientObj)
                            ->setSelectionType($selectionType)
                            ->setEmailType($emailType);
                        if ($emailType == 'contact_field') {
                            $emailField = $em->getRepository('CommonUtilityBundle:FgCmAttribute')->find($email);
                            $emailsObj->setEmailField($emailField);
                        }
                        $em->persist($emailsObj);
                    }
                }
            }
            $em->flush();
        }
    }

    /**
     * Function for updating Recipient Contacts.
     *
     * @param object $container      Container object
     * @param int    $currContactId  Current contact id
     * @param int    $recipientId    Recipient id
     * @param string $clubSystemLang Club default system language
     */
    public function updateRecipientContacts($container, $currContactId, $recipientId, $clubSystemLang)
    {
        $em = $this->getEntityManager();
        $conn = $em->getConnection();
        $systemPrimaryEmail = $container->getParameter('system_field_primaryemail');
        $systemCorrLang = $container->getParameter('system_field_corress_lang');
        $clubCorresLang = $container->get('club')->get('default_lang');

        $recipientObj = $em->getRepository('CommonUtilityBundle:FgCnRecepients')->find($recipientId);
        $exceptions = $em->getRepository('CommonUtilityBundle:FgCnRecepients')->getExceptionsofRecipient($recipientId);
        $recipientEmailsObj = $em->getRepository('CommonUtilityBundle:FgCnRecepientsEmail')->getEmailSettingsofRecipients($recipientId);

        $isAllActive = $recipientObj->getIsAllActive();
        $filterData = $recipientObj->getFilterData();
        $includedContacts = $exceptions['included'];
        $excludedContacts = $exceptions['excluded'];
        $mainEmailIds = $recipientEmailsObj['main'];
        $substituteEmail = $recipientEmailsObj['substitute'];

        $addCondition = "";
        $orCondition = "";
        $club = $container->get('club');
        $clubId = $club->get('id');
        if ($includedContacts != '') {
            $orCondition = " (fg_cm_contact.id IN ($includedContacts)) ";
        }
        if ($excludedContacts != '') {
            $addCondition = " (fg_cm_contact.id NOT IN ($excludedContacts)) ";
        }
        $clubType = $container->get('club')->get('type');
        $fedContactFieldName = ($clubType == 'federation') ? "mc.fed_contact_id" : "mc.contact_id";
        if (($filterData != "") || ($isAllActive == '1')) {
            $columns = array("'$recipientId' AS recipientListId", "salutationTextOwnLocale($fedContactFieldName, $clubId, '$clubSystemLang', '$clubCorresLang', fg_cm_contact.system_language) AS salutation", "`$systemCorrLang` AS corrLang", "fg_cm_contact.`system_language` AS systemLang");
            $filterContsQry = $this->getFilteredContacts($container, $currContactId, $columns, $filterData, $addCondition, $orCondition, "", true, 'contact', true);
            $selectQuery = $filterContsQry['select'];
            $fromQuery = $filterContsQry['from'];
            $whereQuery = $filterContsQry['where'];
            $parentEmailJoinQuery = '" INNER JOIN fg_cm_linkedcontact AS lc ON fg_cm_contact.id = lc.linked_contact_id AND lc.relation_id = 3' . '"';

            $conn->executeQuery("call updateRecipientContacts('$recipientId', $selectQuery, $fromQuery, $whereQuery, $parentEmailJoinQuery, '$mainEmailIds', '$substituteEmail', '$systemPrimaryEmail')");
        }
    }

    /**
     * Function to get contacts for a given filter conditions.
     *
     * @param object $container     Container Object
     * @param int    $currContactId Current Contact Id
     * @param array  $columns       Array of columns to select
     * @param string $filterData    Filter Criteria
     * @param string $addCondition  Add Condition
     * @param string $orCondition   Or Condition
     * @param string $join          Join Condition
     *
     * @return array $contacts Array of Contacts.
     */
    private function getFilteredContacts($container, $currContactId, $columns, $filterData, $addCondition, $orCondition, $join = "", $getQuery = false, $contType = 'contact', $update = false)
    {
        $club = $container->get('club');
        $contactlistClass = new Contactlist($container, $currContactId, $club, $contType);
        $contactlistClass->fedContactSelectionFlag = false;
        $contactlistClass->setColumns($columns);
        $contactlistClass->setFrom();
        $contactlistClass->setCondition();
        if ($filterData != '') {
            $filterarr = json_decode($filterData, true);
        } else {
            $filterarr = array();
        }
        $filter = array_shift($filterarr);
        $filterObj = new Contactfilter($container, $contactlistClass, $filter, $club);
        if ($filter) {
            $sWhere = " " . $filterObj->generateFilter();
            $contactlistClass->addCondition($sWhere);
        }
        if ($addCondition != '') {
            $contactlistClass->addCondition($addCondition);
        }
        if ($orCondition != '') {
            $contactlistClass->orCondition($orCondition);
        }
        if ($join != '') {
            $contactlistClass->addJoin($join);
        }
        $listquery = $contactlistClass->getResult();
        if ($update) {
            $select = '"SELECT ' . $contactlistClass->selectionFields . ' "';
            $from = '"FROM ' . $contactlistClass->from . '"';
            $where = '"WHERE ' . $contactlistClass->where . '"';

            return array('select' => $select, 'from' => $from, 'where' => $where, 'query' => $listquery);
        }

        if ($getQuery) {
            $contacts = $listquery;
        } else {
            $contacts = $this->_em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($listquery);
        }
        return $contacts;
    }

    /**
     * For get collect the filter data
     * @param int $filterId
     *
     * @return type
     */
    public function getFilterValue($filterId)
    {
        $filterData = $this->createQueryBuilder('rs')
            ->select("rs.filterData AS filterData")
            ->where('rs.id=:filterId')
            ->setParameter('filterId', $filterId)
            ->getQuery()
            ->getArrayResult();

        return $filterData;
    }

    /**
     * Get the mandatory list
     * @param int    $filterId
     * @param string $where
     * @param string $orderByandLimit
     *
     * @return array
     */
    public function getMandatoryList($filterId, $where = '', $orderByandLimit = '')
    {

        $conn = $this->getEntityManager()->getConnection();
        $contNameQry1 = "(SELECT (CASE WHEN (c.is_company = 1) THEN (IF ((c.has_main_contact = 1), (SELECT CONCAT(m.`9`, ' (', m.`23`, ' ', m.`2`, ')') FROM `master_system` m LEFT JOIN fg_cm_contact c ON  m.`fed_contact_id`=c.fed_contact_id WHERE c.id = crm.contact_id ), (SELECT m.`9` FROM `master_system` m LEFT JOIN fg_cm_contact c ON  m.`fed_contact_id`=c.fed_contact_id WHERE c.id = crm.`contact_id`))) ELSE (SELECT CONCAT(m.`23`, ' ', m.`2`) FROM `master_system` m LEFT JOIN fg_cm_contact c ON  m.`fed_contact_id`=c.fed_contact_id WHERE c.id = crm.`contact_id`) END) FROM `fg_cm_contact` c WHERE c.`id` = crm.`contact_id`)";
        $contNameQry2 = "(SELECT (CASE WHEN (c.is_company = 1) THEN (IF ((c.has_main_contact = 1), (SELECT CONCAT(m.`9`, ' (', m.`23`, ' ', m.`2`, ')') FROM `master_system` m LEFT JOIN fg_cm_contact c ON  m.`fed_contact_id`=c.fed_contact_id WHERE c.id = crm.linked_contact_id), (SELECT m.`9` FROM `master_system` m LEFT JOIN fg_cm_contact c ON  m.`fed_contact_id`=c.fed_contact_id WHERE c.id = crm.linked_contact_id))) ELSE (SELECT CONCAT(m.`23`, ' ', m.`2`) FROM `master_system` m LEFT JOIN fg_cm_contact c ON  m.`fed_contact_id`=c.fed_contact_id WHERE c.id =crm.linked_contact_id) END) FROM `fg_cm_contact` c WHERE c.`id` = crm.linked_contact_id)";
        $clubField = "(SELECT GROUP_CONCAT(IF(fgc.id = dfc.main_club_id,CONCAT(fgc.id,'#mainclub#'),fgc.id) SEPARATOR ',') FROM fg_cm_contact AS dfc INNER JOIN fg_club fgc ON (fgc.id = dfc.club_id) WHERE dfc.fed_contact_id=c.fed_contact_id AND fgc.club_type IN('standard_club','sub_federation_club','federation_club'))";
        $subfederationField = "(SELECT GROUP_CONCAT(dfgc.sub_federation_id SEPARATOR ',') FROM fg_cm_contact AS dfc INNER JOIN fg_club dfgc ON (dfgc.id = dfc.club_id) WHERE dfc.fed_contact_id=c.fed_contact_id AND dfgc.club_type IN('standard_club','sub_federation_club','federation_club'))";
        $sql = "SELECT crm.contact_id as id,crm.`email` AS email,mas.`515` AS CL_lang,crm.`salutation` AS salutation,GROUP_CONCAT($contNameQry1) AS recipients, GROUP_CONCAT(crm.`contact_id`) AS recipientsId,
        GROUP_CONCAT( CASE WHEN crm.linked_contact_id IS NULL THEN cma.fieldname ELSE CONCAT(cma.fieldname, ' (' , $contNameQry2, ') ' ) END) AS emailFields,
        CONCAT(crm.`email`,crm.`salutation`) AS groupingField, GROUP_CONCAT($clubField SEPARATOR ';') AS FIclub, GROUP_CONCAT($subfederationField SEPARATOR ';') AS FIsub_federation
        FROM `fg_cn_recepients_mandatory` crm
        LEFT JOIN `fg_cm_attribute` cma ON crm.email_field_id = cma.id
        LEFT JOIN fg_cm_contact c ON c.id = crm.contact_id
        INNER JOIN `master_system` mas ON mas.fed_contact_id=c.fed_contact_id
        WHERE crm.`recepient_list_id` = " . $filterId;
        $sql = ($where != '') ? $sql . " AND " . $where . " GROUP BY groupingField" : $sql . " GROUP BY groupingField";
        $sql = ($orderByandLimit != '') ? $sql . " " . $orderByandLimit : $sql;
        $result = $conn->fetchAll($sql);

        return $result;
    }

    /**
     * Get the count of mandatory list
     * @param int $filterId
     *
     * @return type
     */
    public function getMandatoryReceiverListCount($filterId)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT  distinct crm.contact_id, mas.`515`,count(crm.contact_id) as count
        FROM `fg_cn_recepients_mandatory` crm
        LEFT JOIN `fg_cm_attribute` cma ON crm.email_field_id = cma.id
        LEFT JOIN fg_cm_contact c ON c.id = crm.contact_id
        INNER JOIN `master_system` mas ON mas.fed_contact_id=c.fed_contact_id
        WHERE crm.`recepient_list_id` = $filterId
        GROUP BY mas.`515`";

        $result = $conn->fetchAll($sql);

        return $result;
    }

    public function getTotalRecords($filterId)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT   CONCAT(crm.`email`,crm.`salutation`) AS groupingField
        FROM `fg_cn_recepients_mandatory` crm
        LEFT JOIN `fg_cm_attribute` cma ON crm.email_field_id = cma.id
        LEFT JOIN fg_cm_contact c ON c.id = crm.contact_id
        INNER JOIN `master_system` mas ON mas.fed_contact_id=c.fed_contact_id
        WHERE crm.`recepient_list_id` = " . $filterId . "  GROUP BY groupingField";

        $result = $conn->fetchAll($sql);

        return $result;
    }

    /**
     * Get the name of the list
     * @param int $filterId
     *
     * @return array
     */
    public function getRecipientListName($filterId)
    {
        $filterData = $this->createQueryBuilder('rs')
            ->select("rs.name AS listname")
            ->where('rs.id=:filterId')
            ->setParameter('filterId', $filterId)
            ->getQuery()
            ->getResult();

        return $filterData;
    }

    /**
     * For update the contact /non mandatory recipient list
     * @param type $filterId
     * @param type $totalCount
     * @param type $type
     */
    public function updateListCount($filterId, $totalCount, $type = 'recipient')
    {
        $qb = $this->createQueryBuilder();
        $qb->update('CommonUtilityBundle:FgCnRecepients', 'rs');
        if ($type == 'recipient') {
            $qb->set('rs.contactCount', $qb->expr()->literal($totalCount));
        } else {
            $qb->set('rs.subscriberCount', $qb->expr()->literal($totalCount));
        }
        $q = $qb->where('rs.id =:filterId')
            ->setParameter('filterId', $filterId)
            ->getQuery();
        $p = $q->execute();
    }

    /**
     * Function for getting newsletter recipients.
     *
     * @param string  $recType              Mandatory/Non-mandatory Recipients
     * @param string  $status               Newsletter Status
     * @param int     $newsletterId         Newsletter Id
     * @param int     $clubId               Club Id
     * @param object  $container            Container Object
     * @param int     $currContactId        Current Logged-In Contact Id
     * @param boolean $getQryForSendingMail Whether to get recipients query for sending newsletter/simple mail
     * @param boolean $getNlStep2Recievers  Whether to get all contacts without removing excluded contacts
     * @param array   $passedSettings       Array of passed settings
     * @param boolean $getUniqueCount       Whether to return unique recipients count
     * @param string  $clubSystemLang       Club default system language
     *
     * @return array $recipients   Result array of Recipients.
     */
    public function getNewsletterRecipients($recType, $status, $newsletterId, $clubId, $container, $currContactId, $getQryForSendingMail = false, $getNlStep2Recievers = false, $passedSettings = array(), $getUniqueCount = false, $clubSystemLang = 'de', $getUniqueData = false, $orderByResult = false)
    {
        $em = $this->getEntityManager();
        $conn = $em->getConnection();
        $orderBy = " ORDER BY isBounce DESC ";
        $systemPrimaryEmail = $container->getParameter('system_field_primaryemail');
        $clubType = $container->get('club')->get('type');
        $recipients = array();
        if (($status == 'draft') || ($status == 'scheduled') || ($status == 'sending')) {
            $newsletterObj = $em->getRepository('CommonUtilityBundle:FgCnNewsletter')->find($newsletterId);
            $getFormerMembers = ($newsletterObj->getIncludeFormerMembers() == '1') ? true : false;
            $getSubscribers = ($newsletterObj->getIsSubscriberSelection() == '1') ? true : false;
            $langSelection = $newsletterObj->getLanguageSelection();
            $salutationSetting = $newsletterObj->getSalutationType();
            if ($getNlStep2Recievers) {
                $getFormerMembers = $getSubscribers = false;
            }
            // Note: (lang = '-') condition added for subscriber contacts.
            $langCondition = ($langSelection == 'SELECTED') ? "WHERE (lang IN (SELECT `language_code` FROM `fg_cn_newsletter_publish_lang` WHERE `newsletter_id`=:newsletterId))" : "";
            if ($recType == 'mandatory') {
                if ($getNlStep2Recievers && (isset($passedSettings['mainEmails']) || isset($passedSettings['substituteEmail']))) {
                    $mainEmails = $passedSettings['mainEmails'];
                    $substituteEmail = $passedSettings['substituteEmail'];
                } else {
                    // Get email settings of Newsletter.
                    $emailSettings = $em->getRepository('CommonUtilityBundle:FgCnNewsletterManualContactsEmail')->getEmailSettingsofNewsletter($newsletterId);
                    $mainEmails = $emailSettings['main'];
                    $substituteEmail = $emailSettings['substitute'];
                }
            } else if ($recType == 'nonmandatory') {
                $mainEmails = array($systemPrimaryEmail);
                $substituteEmail = '';
            }
            if ($getNlStep2Recievers && isset($passedSettings['manual_contacts'])) {
                $manualContactIds = $passedSettings['manual_contacts'];
                if ($manualContactIds == '') {
                    $manualContactIds = '0';
                }
                $includeCondition = "(fg_cm_contact.id IN ($manualContactIds))";
            } else {
                $includeCondition = "(fg_cm_contact.id IN (SELECT inc.contact_id FROM `fg_cn_newsletter_manual_contacts` inc WHERE inc.newsletter_id=:newsletterId))";
            }
            $recipientsQry = "";
            $sendingMailQuery = "";
            $mandListSalutation = "''";
            $nonMandListSalutation = "''";
            $inclListSalutation = "''";
            $fedContactFieldName = ($clubType == 'federation') ? "mc.fed_contact_id" : "mc.contact_id";

            if ($salutationSetting == 'SAME') {
                $conn = $this->_em->getConnection();
                $salutation = FgUtility::getSecuredDataString($newsletterObj->getSalutation(), $conn);
                $mandListSalutation = $nonMandListSalutation = $inclListSalutation = "'" . $salutation . "'";
            } else if ($salutationSetting == 'INDIVIDUAL') {
                $mandListSalutation = 'crm.`salutation`';
                if ($getQryForSendingMail) {
                    $clubDefaultLang = $container->get('club')->get('default_lang');
                    $nonMandListSalutation = "salutationTextOwnLocale($fedContactFieldName, $clubId, '$clubSystemLang', '$clubDefaultLang', fg_cm_contact.system_language)";
                    $inclListSalutation = "salutationTextOwnLocale($fedContactFieldName, $clubId, '$clubSystemLang', '$clubDefaultLang', fg_cm_contact.system_language)";
                } else {
                    $nonMandListSalutation = "salutationText($fedContactFieldName, $clubId, '$clubSystemLang', NULL)";
                    $inclListSalutation = "salutationText($fedContactFieldName, $clubId, '$clubSystemLang', NULL)";
                }
            }
            $recipientId = '';
            if ($newsletterObj->getRecepientList()) {
                $recipientId = $newsletterObj->getRecepientList()->getId();
            }
            if ($getNlStep2Recievers && isset($passedSettings['recipientId'])) {
                $recipientId = $passedSettings['recipientId'];
            }
            // Query for getting contacts of selected recipient list.
            $selRecipientListQueries = $this->getRecipientListContactsQuery($recType, $container, $currContactId, $recipientId, $nonMandListSalutation, $salutationSetting, $getFormerMembers, $getSubscribers, $getQryForSendingMail, $clubId, $mandListSalutation, $recipientsQry, $sendingMailQuery);
            $recipientsQry = $selRecipientListQueries['recipientsQry'];
            $sendingMailQuery = $selRecipientListQueries['sendingMailQuery'];

            // Query for getting manually added contacts.
            $manualContsQry = $this->getNLIncludedRecipientsQuery($mainEmails, $substituteEmail, $container, $clubId, $currContactId, $inclListSalutation, $includeCondition, $getQryForSendingMail, $recType);

            // Return query for sending newsletter if needed.
            if ($getQryForSendingMail) {
                if ($manualContsQry != "") {
                    $sendingMailQuery = ($sendingMailQuery == "") ? $manualContsQry : "$sendingMailQuery  UNION $manualContsQry";
                }
                $sendingMailQuery = str_replace(':newsletterId', $newsletterId, $sendingMailQuery);
                return $sendingMailQuery;
            }
            if ($manualContsQry != "") {
                $recipientsQry = ($recipientsQry == "") ? $manualContsQry : " $recipientsQry UNION $manualContsQry";
            }
            if ($recipientsQry != "") {
                if ($getNlStep2Recievers) {
                    $selectFields = "email, emailField AS emailfield, contact AS name, salutation, emailId";
                    $havingCondition = "";

                    if (isset($passedSettings['clubAndSubFedIds']) && ($clubType == 'federation')) {
                        $selectFields .= ", (SELECT GROUP_CONCAT(IF(CL.id = C1.main_club_id, CONCAT(CL.id,'#mainclub#'), CL.id) SEPARATOR ',') FROM fg_cm_contact C1 INNER JOIN fg_cm_contact C2 ON (C2.fed_contact_id=C1.fed_contact_id) INNER JOIN fg_club CL ON (CL.id=C2.club_id AND CL.is_federation=0 AND CL.is_sub_federation=0) WHERE C1.id= contactId AND C1.is_permanent_delete=0) AS contactClub";
                        $selectFields .= ", (SELECT GROUP_CONCAT(CL.sub_federation_id SEPARATOR ',') FROM fg_cm_contact C1 INNER JOIN fg_cm_contact C2 ON (C2.fed_contact_id=C1.fed_contact_id) INNER JOIN fg_club CL ON (CL.id=C2.club_id AND CL.is_federation=0 AND CL.is_sub_federation=0) WHERE C1.id= contactId AND C1.is_permanent_delete=0) AS contactSubFederation";
                    }
                    if (isset($passedSettings['clubAndSubFedIds']) && ($clubType == 'sub_federation')) {
                        $selectFields .= ", (SELECT GROUP_CONCAT(IF(CL.id = C1.main_club_id, CONCAT(CL.id,'#mainclub#'), CL.id) SEPARATOR ',') FROM fg_cm_contact C1 INNER JOIN fg_cm_contact C2 ON (C2.fed_contact_id=C1.fed_contact_id) INNER JOIN fg_club CL ON (CL.id=C2.club_id AND CL.is_federation=0 AND CL.is_sub_federation=0) WHERE C1.id= contactId AND C1.is_permanent_delete=0) AS contactClub";
                    }
                } else {
                    $selectFields = "id, contactId, email, emailField, contact, contactType, salutation, opened, isBounce, bounceMessage, lang, groupingField";
                    $havingCondition = "HAVING groupingField NOT IN (SELECT CONCAT(`email`,'-',`salutation`) FROM `fg_cn_newsletter_exclude_contacts` WHERE `newsletter_id`=:newsletterId)";

                    if (isset($passedSettings['clubAndSubFedIds']) && ($clubType == 'federation')) {
                        $selectFields .= ", (SELECT GROUP_CONCAT(IF(CL.id = C1.main_club_id, CONCAT(CL.id,'#mainclub#'), CL.id) SEPARATOR ',') FROM fg_cm_contact C1 INNER JOIN fg_cm_contact C2 ON (C2.fed_contact_id=C1.fed_contact_id) INNER JOIN fg_club CL ON (CL.id=C2.club_id AND CL.is_federation=0 AND CL.is_sub_federation=0) WHERE C1.id= contactId AND C1.is_permanent_delete=0) AS contactClub";
                        $selectFields .= ", (SELECT GROUP_CONCAT(CL.sub_federation_id SEPARATOR ',') FROM fg_cm_contact C1 INNER JOIN fg_cm_contact C2 ON (C2.fed_contact_id=C1.fed_contact_id) INNER JOIN fg_club CL ON (CL.id=C2.club_id AND CL.is_federation=0 AND CL.is_sub_federation=0) WHERE C1.id= contactId AND C1.is_permanent_delete=0) AS contactSubFederation";
                    }
                    if (isset($passedSettings['clubAndSubFedIds']) && ($clubType == 'sub_federation')) {
                        $selectFields .= ", (SELECT GROUP_CONCAT(IF(CL.id = C1.main_club_id, CONCAT(CL.id,'#mainclub#'), CL.id) SEPARATOR ',') FROM fg_cm_contact C1 INNER JOIN fg_cm_contact C2 ON (C2.fed_contact_id=C1.fed_contact_id) INNER JOIN fg_club CL ON (CL.id=C2.club_id AND CL.is_federation=0 AND CL.is_sub_federation=0) WHERE C1.id= contactId AND C1.is_permanent_delete=0) AS contactClub";
                    }
                }

                $recipientsQry = "SELECT $selectFields "
                    . "FROM ($recipientsQry) recipients "
                    . "$langCondition";

                if ($getUniqueCount) {
                    $recipientsQry = "$recipientsQry GROUP BY groupingField $havingCondition";
                    $recipientsQry = "SELECT COUNT(id) AS recipientsCount FROM ($recipientsQry) recCount";
                } else if ($getUniqueData) {
                    $recipientsQry = "$recipientsQry GROUP BY groupingField $havingCondition $orderBy";
                } else if ($orderByResult) {
                    if (isset($passedSettings['getCount'])) {
                        $recipientsQry = "$recipientsQry $havingCondition";
                        $recipientsQry = "SELECT COUNT(id) AS recipientsCount FROM ($recipientsQry) recCount";
                    } else {
                        $orderByLimit = (isset($passedSettings['orderByLimit'])) ? $passedSettings['orderByLimit'] : $orderBy;
                        $recipientsQry = "(" . $recipientsQry . " " . $havingCondition . ")" . $orderByLimit;
                    }
                } else {
                    $recipientsQry = "$recipientsQry $havingCondition $orderBy";
                }
                $recipients = $conn->executeQuery($recipientsQry, array('recipientId' => $recipientId, 'newsletterId' => $newsletterId, 'clubId' => $clubId))->fetchAll();
            }
        } else if ($status == 'sent') {
            $recipientsQry = $this->getSentRecipientsQuery($container, $orderBy);
            $recipients = $conn->executeQuery($recipientsQry, array('newsletterId' => $newsletterId))->fetchAll();
        }
        if ($getUniqueCount) {
            $recipients = $recipients[0]['recipientsCount'];
        }
        return $recipients;
    }

    /**
     * Function to get email field names
     * @param $linkedContact linked contact id
     * @param $emailFieldId email field id
     * @return string email field name
     */
    public function getEmailFieldsNames($linkedContact, $emailFieldId, $container)
    {
        $club = $container->get('club');
        $defaultLang = $club->get('default_lang');
        $defaultSystemLang = $club->get('default_system_lang');
        $fieldName = "SELECT b.`fieldname_short_lang`  FROM `fg_cm_attribute` a LEFT JOIN `fg_cm_attribute_i18n` b ON (b.id=a.id AND (IF((a.`is_system_field` = 1 OR a.`is_fairgate_field` = 1), (b.`lang`='$defaultSystemLang'), (b.`lang`='$defaultLang')))) WHERE a.id = '$emailFieldId' ";
        $emailField = "SELECT CONCAT( ($fieldName), ' (', (SELECT GROUP_CONCAT(contactName(c.`id`) SEPARATOR ', ') FROM `fg_cm_contact` c WHERE c.`id` =  $linkedContact ), ')') as fieldname_short_lang ";
        if ($linkedContact) {
            $query = $emailField;
        } else {
            $query = $fieldName;
        }
        $conn = $this->getEntityManager()->getConnection();
        $emailFieldName = $conn->executeQuery($query)->fetchAll();

        return $emailFieldName[0]['fieldname_short_lang'];
    }

    /**
     * Function to get query for getting contacts having value for given email field.
     *
     * @param object  $container            Container Object
     * @param int     $clubId               Club Id
     * @param int     $currContactId        Current Contact Id
     * @param int     $fieldId              Email Field Id
     * @param string  $salutation           Salutation
     * @param string  $filterData           Filter Criteria
     * @param string  $includeCondition     Condition for including contacts
     * @param string  $orCondition          Or Condition
     * @param string  $join                 Join Query
     * @param string  $extraCondition       Extra Condition
     * @param boolean $getQryForSendingMail Whether to get recipients query for sending newsletter/simple mail
     * @param string  $defaultSystemLang    Club Default System Language
     * @param string  $defaultLang          Club Default Language
     *
     * @return string $addRecipientQry Result Query.
     */
    private function getRecipientQueryForEmail($container, $clubId, $currContactId, $fieldId, $salutation, $filterData, $includeCondition, $orCondition, $join, $extraCondition = "", $getQryForSendingMail = false, $defaultSystemLang = 'de', $defaultLang = 'de')
    {
        $systemCorrLang = $container->getParameter('system_field_corress_lang');
        $addExtraCondition = ($extraCondition == "") ? "" : " AND ($extraCondition) ";
        $addCondition = " ($includeCondition AND (`$fieldId` IS NOT NULL AND `$fieldId`!='') $addExtraCondition) ";
        $clubType = $container->get('club')->get('type');
        $fedContactFieldName = ($clubType == 'federation') ? "mc.fed_contact_id" : "mc.contact_id";
        if (!$getQryForSendingMail) {
            $fieldName = "(SELECT `fieldname_short_lang` FROM `fg_cm_attribute` a LEFT JOIN `fg_cm_attribute_i18n` b ON (b.id=a.id AND(IF((a.`is_system_field` = 1 OR a.`is_fairgate_field` = 1), (b.`lang`='$defaultSystemLang'), (b.`lang`='$defaultLang')))) WHERE a.id=$fieldId)";
            $columns = array("fg_cm_contact.id AS contactId", "`$fieldId` AS email", "`$fieldId` AS emailId", "$fieldName AS emailField", "contactName($fedContactFieldName) AS contact", " 'contact' as contactType",
                "$salutation AS salutation", "'' AS opened", "'0' AS isBounce", "'' AS bounceMessage", "`$systemCorrLang` AS lang", "CONCAT(`$fieldId`,'-',$salutation) AS groupingField", "fg_cm_contact.club_id AS contactClubId");
        } else {
            $columns = array("`$systemCorrLang` AS lang", "$clubId AS clubid", ":newsletterId as newsletterId", "fg_cm_contact.id AS contactId", "`$fieldId` AS email", "$salutation AS salutation", "$fieldId AS emailFieldId", "NULL AS linkedContactId", "fg_cm_contact.system_language AS systemLanguage");
        }
        $addRecipientQry = $this->getFilteredContacts($container, $currContactId, $columns, $filterData, $addCondition, $orCondition, $join, true);

        return $addRecipientQry;
    }

    /**
     * Function to get query for getting contacts having parent emails.
     *
     * @param object  $container            Container Object
     * @param int     $clubId               Club Id
     * @param int     $currContactId        Current Contact Id
     * @param string  $parentEmail          Query for getting parent email
     * @param string  $salutation           Salutation
     * @param string  $filterData           Filter Criteria
     * @param string  $includeCondition     Condition for including contacts
     * @param string  $orCondition          Or Condition
     * @param string  $join                 Join Query
     * @param boolean $getQryForSendingMail Whether to get recipients query for sending newsletter/simple mail
     * @param string  $extraCondition       Extra Condition
     * @param string  $defaultSystemLang    Club Default System Language
     * @param string  $defaultLang          Club Default Language
     *
     * @return string $addRecipientQry Result Query.
     */
    private function getRecipientQueryForParentEmail($container, $clubId, $currContactId, $parentEmail, $salutation, $filterData, $includeCondition, $orCondition, $join, $getQryForSendingMail = false, $extraCondition = "", $defaultSystemLang = 'de', $defaultLang = 'de')
    {
        $systemPrimaryEmail = $container->getParameter('system_field_primaryemail');
        $systemCorrLang = $container->getParameter('system_field_corress_lang');
        //$translator = $container->get('translator');
        //$parentEmailTrans = $translator->trans('NL_PARENTS_EMAIL');
        $addExtraCondition = ($extraCondition == "") ? "" : " AND ($extraCondition) ";
        $join = " INNER JOIN fg_cm_linkedcontact AS lc ON fg_cm_contact.id = lc.linked_contact_id AND lc.relation_id = 3 ";
        $addCondition = " ($includeCondition AND ($parentEmail IS NOT NULL AND $parentEmail!='') $addExtraCondition) ";
        //$email = $getQryForSendingMail ? "$parentEmail" : "CONCAT($parentEmail, ' (', contactName(lc.contact_id), ')')";
        //$fieldName = "(SELECT `fieldname` FROM `fg_cm_attribute` WHERE `id`=$systemPrimaryEmail)";
        $fieldName = "(SELECT b.`fieldname_short_lang` FROM `fg_cm_attribute` a LEFT JOIN `fg_cm_attribute_i18n` b ON (b.id=a.id AND (IF((a.`is_system_field` = 1 OR a.`is_fairgate_field` = 1), (b.`lang`='$defaultSystemLang'), (b.`lang`='$defaultLang')))) WHERE a.id=$systemPrimaryEmail)";
        $emailField = "CONCAT($fieldName, ' (', contactName(lc.contact_id), ')')";
        $email = "$parentEmail";
        $clubType = $container->get('club')->get('type');
        $fedContactFieldName = ($clubType == 'federation') ? "mc.fed_contact_id" : "mc.contact_id";
        if (!$getQryForSendingMail) {
            $columns = array("$fedContactFieldName AS contactId", "$email AS email", "$parentEmail AS emailId", "$emailField AS emailField",
                "contactName($fedContactFieldName) AS contact", "'contact' as contactType", "$salutation AS salutation", "'' AS opened", "'0' AS isBounce", "'' AS bounceMessage", "`$systemCorrLang` AS lang", "CONCAT($parentEmail,'-',$salutation) AS groupingField", "fg_cm_contact.club_id AS contactClubId");
        } else {
            $columns = array("`$systemCorrLang` AS lang", "$clubId AS clubid", ":newsletterId AS newsletterId", "$fedContactFieldName AS contactId", "$email AS email", "$salutation AS salutation", "'$systemPrimaryEmail' AS emailFieldId", "lc.contact_id AS linkedContactId", "fg_cm_contact.system_language AS systemLanguage");
        }
        $addRecipientQry = $this->getFilteredContacts($container, $currContactId, $columns, $filterData, $addCondition, $orCondition, $join, true);
        //$addRecipientQry = $addRecipientQry . " GROUP BY contactId";

        return $addRecipientQry;
    }

    /**
     * Function to get query for getting saved mandatory recipients.
     *
     * @param string $mandListSalutation Text for selecting salutation
     * @param string $defaultSystemLang  Club Default System Language
     * @param string $defaultLang        Club Default Language
     *
     * @return string $resultQry Result Query
     */
    private function getMandatoryRecipientsQuery($mandListSalutation, $defaultSystemLang = 'de', $defaultLang = 'de')
    {
        $resultQry = "SELECT crm.`contact_id` AS id, crm.`contact_id` AS contactId, crm.`email` AS email, crm.`email` AS emailId, (CASE WHEN crm.linked_contact_id IS NULL THEN cmai18n.`fieldname_short_lang` ELSE CONCAT(cmai18n.`fieldname_short_lang`, ' (' , contactName(crm.linked_contact_id), ') ' ) END) AS emailField,
            contactName(crm.`contact_id`) AS contact, 'contact' as contactType, $mandListSalutation AS salutation, '' AS opened, '0' AS isBounce, '' AS bounceMessage, crm.`corres_lang` AS lang, CONCAT(crm.`email`,'-',$mandListSalutation) AS groupingField, cmc.club_id AS contactClubId 
            FROM `fg_cn_recepients_mandatory` crm
            LEFT JOIN `fg_cm_attribute` cma ON crm.email_field_id = cma.id
            LEFT JOIN `fg_cm_attribute_i18n` cmai18n ON (cmai18n.id=cma.id AND (IF((cma.`is_system_field` = 1 OR cma.`is_fairgate_field` = 1), (cmai18n.`lang`='$defaultSystemLang'), (cmai18n.`lang`='$defaultLang'))))
            LEFT JOIN `fg_cm_contact` cmc ON cmc.id=crm.contact_id 
            INNER JOIN `master_system` mas ON mas.fed_contact_id=cmc.fed_contact_id 
            WHERE crm.`recepient_list_id` =:recipientId";

        return $resultQry;
    }

    /**
     * Function to get query for getting non-mandatory recipient list.
     *
     * @param object  $container            Container Object
     * @param int     $currContactId        Current Contact Id
     * @param int     $recipientId          Recipient Id
     * @param string  $salutation           Salutation Text
     * @param string  $salutationSetting    Salutation Setting
     * @param boolean $getFormerMembers     Whether to return query for getting former federation members
     * @param boolean $getSubscribers       Whether to return query for getting subscribers
     * @param boolean $getQryForSendingMail Whether to get recipients query for sending newsletter/simple mail
     * @param int     $clubId               Club Id
     * @param string  $clubSystemLang       Club default system language
     * @param string  $defaultLang          Club Default Language
     *
     * @return array $resultQryArray Array of Queries (filter, former federation members, subscribers)
     */
    private function getNonMandatoryRecipientsQuery($container, $currContactId, $recipientId, $salutation, $salutationSetting, $getFormerMembers, $getSubscribers, $getQryForSendingMail = false, $clubId = 0, $clubSystemLang = 'de', $defaultLang = 'de')
    {
        $em = $this->getEntityManager();
        $translator = $container->get('translator');
        $systemPrimaryEmail = $container->getParameter('system_field_primaryemail');
        $systemCorrLang = $container->getParameter('system_field_corress_lang');
        $emailField = $translator->trans('GN_EMAIL');
        $clubType = $container->get('club')->get('type');
        $fedContactFieldName = ($clubType == 'federation') ? "mc.fed_contact_id" : "mc.contact_id";
        // echo $fedContactFieldName;exit;
        $includedContacts = $excludedContacts = '';
        $filterQuery = $formerMembersQry = $subscribersQry = "";
        if ($recipientId != '') {
            $recipientObj = $em->getRepository('CommonUtilityBundle:FgCnRecepients')->find($recipientId);
            $exceptions = $em->getRepository('CommonUtilityBundle:FgCnRecepients')->getExceptionsofRecipient($recipientId);
            $filterData = $recipientObj->getFilterData();
            $includedContacts = $exceptions['included'];
            $excludedContacts = $exceptions['excluded'];
        }
        $commonCondition = "(fg_cm_contact.is_subscriber = 1) AND (`$systemPrimaryEmail` IS NOT NULL AND `$systemPrimaryEmail`!='')";
        $addCondition = "";
        $orCondition = "";
        if ($includedContacts != '') {
            $orCondition = " (fg_cm_contact.id IN ($includedContacts)) ";
        }
        if ($excludedContacts != '') {
            $addCondition = " (fg_cm_contact.id NOT IN ($excludedContacts)) ";
        }
        $addCondition = ($addCondition == "") ? "$commonCondition" : "($addCondition AND $commonCondition)";
        if ($orCondition != "") {
            $orCondition = "($orCondition AND $commonCondition)";
        }
        if (!$getQryForSendingMail) {
            $fieldName = "(SELECT b.`fieldname_short_lang` FROM `fg_cm_attribute` a LEFT JOIN `fg_cm_attribute_i18n` b ON (b.id=a.id AND (IF((a.`is_system_field` = 1 OR a.`is_fairgate_field` = 1), (b.`lang`='$clubSystemLang'), (b.`lang`='$defaultLang')))) WHERE a.id=$systemPrimaryEmail)";
            $columns = array("fg_cm_contact.id AS contactId", "`$systemPrimaryEmail` AS email", "`$systemPrimaryEmail` AS emailId", "$fieldName AS emailField", "contactName($fedContactFieldName) AS contact", "'contact' as contactType",
                "$salutation AS salutation", "'' AS opened", "'0' AS isBounce", "'' AS bounceMessage", "`$systemCorrLang` AS lang", "CONCAT(`$systemPrimaryEmail`,'-',$salutation) AS groupingField", "fg_cm_contact.club_id AS contactClubId");
        } else {
            $columns = array("`$systemCorrLang` AS lang", "$clubId AS clubid", ":newsletterId as newsletterId", "fg_cm_contact.id AS contactId", "`$systemPrimaryEmail` AS email", "$salutation AS salutation", "$systemPrimaryEmail AS emailFieldId", "NULL AS linkedContactId", "fg_cm_contact.system_language AS systemLanguage");
        }
        // Query for getting contacts of selected recipient list.
        if ($recipientId != '') {
            $filterQuery = $this->getFilteredContacts($container, $currContactId, $columns, $filterData, $addCondition, $orCondition, "", true, 'contact');
        }

        // Query for getting former federation members.
        if ($getFormerMembers) {
            $formerMembersQry = $this->getFilteredContacts($container, $currContactId, $columns, '', $addCondition, $orCondition, "", true, 'formerfederationmember');
        }
        // Query for getting subscribers.
        if ($getSubscribers) {
            $salutation = ($salutationSetting == 'INDIVIDUAL') ? "subscriberSalutationText(`id`, $clubId, '$clubSystemLang', '$defaultLang')" : $salutation;
            $subscribersQry = "SELECT `id` AS id, `id` AS contactId, `email` AS email, `email` AS emailId, '$emailField' AS emailField, subscriberName(`id`, 0) AS contact, 'subscriber' as contactType, $salutation AS salutation, '' AS opened, '0' AS isBounce, '' AS bounceMessage, `correspondance_lang` AS lang, CONCAT(`email`,'-',$salutation) AS groupingField, '' AS contactClubId "
                . "FROM `fg_cn_subscriber` WHERE `club_id`=:clubId";
        }

        $resultQryArray = array('filterQuery' => $filterQuery, 'formerMembersQry' => $formerMembersQry, 'subscribersQry' => $subscribersQry);

        return $resultQryArray;
    }

    /**
     * Function to get query for getting recipients of sent newsletter.
     *
     * @param object $container Container object
     * @param string $orderBy Order by condition
     *
     * @return string $resultQry Result Query
     */
    private function getSentRecipientsQuery($container, $orderBy)
    {
        $dateFormat = FgSettings::getMysqlDateTimeFormat();
        $emailText = $container->get('translator')->trans('EMAIL');
        $club = $container->get('club');
        $defaultLang = $club->get('default_lang');
        $defaultSystemLang = $club->get('default_system_lang');
        $fieldName = "(SELECT GROUP_CONCAT(b.`fieldname_short_lang` SEPARATOR ', ') FROM `fg_cm_attribute` a LEFT JOIN `fg_cm_attribute_i18n` b ON (b.id=a.id AND (IF((a.`is_system_field` = 1 OR a.`is_fairgate_field` = 1), (b.`lang`='$defaultSystemLang'), (b.`lang`='$defaultLang')))) WHERE FIND_IN_SET(a.id, rl.email_field_ids))";
        $emailField = "CONCAT($fieldName, ' (', (SELECT GROUP_CONCAT(contactName(c.`id`) SEPARATOR ', ') FROM `fg_cm_contact` c WHERE FIND_IN_SET(c.`id`, rl.linked_contact_ids)), ')')";
        $resultQry = "SELECT rl.id AS logId,rl.contact_id AS id, rl.contact_id AS contactId, "
            . "(SELECT GROUP_CONCAT( CONCAT( c.`id`, '|---|', contactName(c.`id`) ) SEPARATOR '|&&&|') FROM `fg_cm_contact` c "
            . "WHERE FIND_IN_SET(c.`id`, rl.contact_id)) as contactNames, "
            . "(IF((rl.resent_email IS NULL OR rl.resent_email='' AND rl.is_email_changed=0), (rl.email), (rl.resent_email))) AS email, "
            . "(IF(((rl.subscriber_id IS NULL) OR (rl.subscriber_id = '')), "
            . "(IF((rl.linked_contact_ids IS NULL OR rl.linked_contact_ids = ''), ($fieldName), ($emailField))), "
            . "('$emailText'))) AS emailField, "
            . " rl.email_field_ids, rl.linked_contact_ids, "
            . "(IF(((rl.subscriber_id IS NULL) OR (rl.subscriber_id = '')), '', (subscriberName(rl.subscriber_id, 0)))) AS subscriberName, "
            . "rl.salutation AS salutation, "
            . "(IF(((rl.subscriber_id IS NULL) OR (rl.subscriber_id = '')), 'contact', 'subscriber')) AS contactType, "
            . "(IF(((rl.subscriber_id IS NULL) OR (rl.subscriber_id = '')), '', rl.subscriber_id)) AS subscriberId, "
            . "DATE_FORMAT(rl.opened_at,'$dateFormat')AS opened, rl.is_bounced AS isBounce, rl.bounce_message AS bounceMessage, rl.is_email_changed AS isEmailChanged, rl.corres_lang AS lang, '' AS groupingField, n.resent_status AS resendStatus, "
            . "(SELECT GROUP_CONCAT(IF(fg_club.title !='', if(fg_club.id = C1.main_club_id,CONCAT(fg_club.title,'#mainclub#'),fg_club.title),'') SEPARATOR ', ') FROM fg_cm_contact C1 LEFT JOIN fg_cm_contact C2 ON (C2.fed_contact_id=C1.fed_contact_id) INNER JOIN fg_club ON (fg_club.id = C2.club_id AND fg_club.is_federation=0 AND fg_club.is_sub_federation=0) "
            . "WHERE FIND_IN_SET(C1.`id`, rl.contact_id)) as contactClub, "
            . "(SELECT GROUP_CONCAT(IF(fg_club.title !='', fg_club.title,'') SEPARATOR ', ') FROM fg_cm_contact C1 LEFT JOIN fg_cm_contact C2 ON (C2.fed_contact_id=C1.fed_contact_id) INNER JOIN fg_club ON (fg_club.id = C2.club_id AND fg_club.club_type='sub_federation') "
            . "WHERE FIND_IN_SET(C1.`id`, rl.contact_id)) as contactSubFederation "
            . "FROM `fg_cn_newsletter_receiver_log` rl "
            . "LEFT JOIN `fg_cn_newsletter` n ON rl.newsletter_id=n.id "
            . "WHERE rl.newsletter_id=:newsletterId $orderBy";

        return $resultQry;
    }

    /**
     * Function to get query for getting contacts of a recipient list.
     *
     * @param string  $recType               Recipient type (mandatory/nonmandatory)
     * @param object  $container             Container object
     * @param int     $currContactId         Current logged-in contact id
     * @param int     $recipientId           Recipient list id
     * @param string  $nonMandListSalutation Non-mandatory salutation text
     * @param string  $salutationSetting     Salutation setting of newsletter
     * @param boolean $getFormerMembers      Whether to include former federation members also or not
     * @param boolean $getSubscribers        Whether to include subscribers also or not
     * @param boolean $getQryForSendingMail  Whether to get query for sending mail
     * @param int     $clubId                Club id
     * @param string  $mandListSalutation    Mandatory salutation text
     * @param string  $recipientsQry         Query for getting recipients
     * @param string  $sendingMailQuery      Query for sending mail
     *
     * @return array $resultArray Resulting array of queries (query for getting recipients and query for sending mail)
     */
    private function getRecipientListContactsQuery($recType, $container, $currContactId, $recipientId, $nonMandListSalutation, $salutationSetting, $getFormerMembers, $getSubscribers, $getQryForSendingMail, $clubId, $mandListSalutation, $recipientsQry, $sendingMailQuery)
    {
        $club = $container->get('club');
        $defaultLang = $club->get('default_lang');
        $defaultSystemLang = $club->get('default_system_lang');
        if ($recType == 'mandatory') {
            $recipientListQry = $this->getMandatoryRecipientsQuery($mandListSalutation, $defaultSystemLang, $defaultLang);
            $recipientsQry = "($recipientListQry)";
        } else if ($recType == 'nonmandatory') {
            $nonMandQryArray = $this->getNonMandatoryRecipientsQuery($container, $currContactId, $recipientId, $nonMandListSalutation, $salutationSetting, $getFormerMembers, $getSubscribers, $getQryForSendingMail, $clubId, $defaultSystemLang, $defaultLang);
            if ($nonMandQryArray['filterQuery'] != "") {
                $recipientsQry = ($recipientsQry == "") ? $nonMandQryArray['filterQuery'] : "$recipientsQry UNION (" . $nonMandQryArray['filterQuery'] . ")";
                $sendingMailQuery = ($sendingMailQuery == "") ? $nonMandQryArray['filterQuery'] : "$sendingMailQuery UNION (" . $nonMandQryArray['filterQuery'] . ")";
            }
            if ($nonMandQryArray['formerMembersQry'] != "") {
                $recipientsQry = ($recipientsQry == "") ? $nonMandQryArray['formerMembersQry'] : "$recipientsQry UNION (" . $nonMandQryArray['formerMembersQry'] . ")";
                $sendingMailQuery = ($sendingMailQuery == "") ? $nonMandQryArray['formerMembersQry'] : "$sendingMailQuery UNION (" . $nonMandQryArray['formerMembersQry'] . ")";
            }
            if ($nonMandQryArray['subscribersQry'] != "") {
                $recipientsQry = ($recipientsQry == "") ? $nonMandQryArray['subscribersQry'] : "$recipientsQry UNION (" . $nonMandQryArray['subscribersQry'] . ")";
            }
        }
        $resultArray = array('recipientsQry' => $recipientsQry, 'sendingMailQuery' => $sendingMailQuery);

        return $resultArray;
    }

    /**
     * Function to get query for getting manually included contacts of a newsletter.
     *
     * @param array   $mainEmails           Array of main emails
     * @param string  $substituteEmail      Substitute email
     * @param object  $container            Container object
     * @param int     $clubId               Club id
     * @param int     $currContactId        Current logged-in contact id
     * @param string  $inclListSalutation   Salutation text
     * @param string  $includeCondition     Including condition
     * @param boolean $getQryForSendingMail Whether to get query for sending mail
     * @param string  $recType              Recipient type (mandatory/nonmandatory)
     *
     * @return string $manualContsQry Query for getting manually included contacts.
     */
    private function getNLIncludedRecipientsQuery($mainEmails, $substituteEmail, $container, $clubId, $currContactId, $inclListSalutation, $includeCondition, $getQryForSendingMail, $recType)
    {
        $systemPrimaryEmail = $container->getParameter('system_field_primaryemail');
        $club = $container->get('club');
        $defaultLang = $club->get('default_lang');
        $defaultSystemLang = $club->get('default_system_lang');
        $orCondition = "";
        $join = "";
        $filterData = "";
        $condForSubstitute = "";
        $manualContsQry = "";
        // Query for getting data of manually included contacts with selected main emails.
        foreach ($mainEmails as $mainEmail) {
            if ($mainEmail == 'parent_email') {
                $parentEmail = "(SELECT `$systemPrimaryEmail` FROM master_system INNER JOIN fg_cm_contact on master_system.fed_contact_id=fg_cm_contact.fed_contact_id WHERE fg_cm_contact.id=lc.contact_id) ";
                $addRecipientQry = $this->getRecipientQueryForParentEmail($container, $clubId, $currContactId, $parentEmail, $inclListSalutation, $filterData, $includeCondition, $orCondition, $join, $getQryForSendingMail, "", $defaultSystemLang, $defaultLang);
                $condForSubstitute .= ($condForSubstitute == "") ? "($parentEmail IS NULL OR $parentEmail='')" : " AND ($parentEmail IS NULL OR $parentEmail='')";
            } else {
                $join = "";
                $subsExtraCond = ($recType == 'nonmandatory') ? "(fg_cm_contact.is_subscriber = 1)" : "";
                $addRecipientQry = $this->getRecipientQueryForEmail($container, $clubId, $currContactId, $mainEmail, $inclListSalutation, $filterData, $includeCondition, $orCondition, $join, $subsExtraCond, $getQryForSendingMail, $defaultSystemLang, $defaultLang);
                $condForSubstitute .= ($condForSubstitute == "") ? "(`$mainEmail` IS NULL OR `$mainEmail`='')" : " AND (`$mainEmail` IS NULL OR `$mainEmail`='')";
            }
            $manualContsQry = ($manualContsQry == "") ? "($addRecipientQry)" : "$manualContsQry UNION ($addRecipientQry)";
        }
        // Query for getting data of manually included contacts with selected substitute email.
        if (($substituteEmail != '') && !in_array($substituteEmail, $mainEmails)) {
            if ($substituteEmail == 'parent_email') {
                $parentEmail = "(SELECT `$systemPrimaryEmail` FROM master_system INNER JOIN fg_cm_contact on master_system.fed_contact_id=fg_cm_contact.fed_contact_id WHERE fg_cm_contact.id=lc.contact_id)";
                $addRecipientQry = $this->getRecipientQueryForParentEmail($container, $clubId, $currContactId, $parentEmail, $inclListSalutation, $filterData, $includeCondition, $orCondition, $join, $getQryForSendingMail, $condForSubstitute, $defaultSystemLang, $defaultLang);
            } else {
                $join = in_array('parent_email', $mainEmails) ? " LEFT JOIN fg_cm_linkedcontact AS lc ON fg_cm_contact.id = lc.linked_contact_id AND lc.relation_id = 3 " : "";
                $addRecipientQry = $this->getRecipientQueryForEmail($container, $clubId, $currContactId, $substituteEmail, $inclListSalutation, $filterData, $includeCondition, $orCondition, $join, $condForSubstitute, $getQryForSendingMail, $defaultSystemLang, $defaultLang);
            }
            $manualContsQry = ($manualContsQry == "") ? "($addRecipientQry)" : "$manualContsQry UNION ($addRecipientQry)";
        }

        return $manualContsQry;
    }

    /**
     * Function to insert contacts of newly created recipient list
     * @param object $container Container Object
     * @param int    $contactId Current contact Id
     */
    private function updateContactsOfNewlyCreatedRecipientList($container, $contactId)
    {
        if (count($this->toUpdateRecipientListContacts) > 0) {
            ini_set('max_execution_time', 0);
            ini_set("memory_limit", "2000M");
            $club = $container->get('club');
            $clubDefaultSystemLang = $club->get('default_system_lang');
            foreach ($this->toUpdateRecipientListContacts as $recipientListId) {
                $this->updateRecipientContacts($container, $contactId, $recipientListId, $clubDefaultSystemLang);
            }
        }
    }

    /**
     * Get the reciepient list
     * @param int $filterId
     * @param int $clubId
     * @return array
     */
    public function getRecipientInClub($filterId, $clubId)
    {
        $em = $this->getEntityManager();
        $clubObj = $em->getRepository('CommonUtilityBundle:FgClub')->find($clubId);
        $filterData = $this->createQueryBuilder('rs')
            ->select("rs.id")
            ->where('rs.id=:filterId')
            ->andWhere('rs.club=:clubObj')
            ->setParameters(array('clubObj' => $clubObj, 'filterId' => $filterId))
            ->getQuery()
            ->getResult();
        return $filterData;
    }

    /**
     * This function is used to get the club data of recipients in status send, sending, scheduled etc. 
     * 
     * @param int    $newsletterId Newsletter id
     * @param string $status       Newsletter status
     * 
     * @return array $recipientsClubData Array of club details
     */
    public function getClubDataOfRecipients($newsletterId, $status = 'sent')
    {

        $qry = "SELECT GROUP_CONCAT(ct.id) as contactIds, GROUP_CONCAT(CASE WHEN (club.title !='' AND (club.club_type = 'sub_federation_club' OR club.club_type = 'federation_club')) THEN club.title END) as clubTitle, "
            . "GROUP_CONCAT(CASE WHEN (club.title !='' AND club.is_sub_federation = 1) THEN club.title END) as subFedTitle, "
            . "ct.fed_contact_id FROM fg_club club INNER JOIN fg_cm_contact ct ON ct.club_id = club.id AND ct.fed_contact_id IN "
            . "(SELECT fed_contact_id FROM fg_cm_contact c WHERE c.id IN (select contact_id FROM fg_cn_newsletter_receiver_log rl WHERE rl.newsletter_id = :newsletterId AND contact_id != '') "
            . "GROUP BY c.fed_contact_id ) GROUP BY ct.fed_contact_id";

        $stmt = $this->_em->getConnection()->prepare($qry);
        $stmt->execute(array('newsletterId' => $newsletterId));
        $recipientsClubData = $stmt->fetchAll();

        return $recipientsClubData;
    }

    /**
     * This function is used to get the newsletter recipients
     * 
     * @param object $container    Container object
     * @param int    $newsletterId Newsletter id
     * @param string $orderBy      Order by
     * @param string $limit        Limit
     * 
     * @return array $recipientsData Recipients details
     */
    public function getRecipientsOfSentNewsletter($container, $newsletterId, $orderByLimit)
    {
        $qry = $this->getSentRecipientsQuery($container, $orderByLimit);
        $stmt = $this->_em->getConnection()->prepare($qry);
        $stmt->execute(array('newsletterId' => $newsletterId));
        $recipientsData = $stmt->fetchAll();

        return $recipientsData;
    }
}
