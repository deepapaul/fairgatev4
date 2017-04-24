<?php

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Repository\Pdo\DocumentPdo;
/**
 * Repository for handling document assignment
 *
 */
class FgDmAssigmentRepository extends EntityRepository {

    /**
     * Function to get document ids assigned to a particular contact
     * get results concatenated with ','
     * 
     * @param Int $contactId Assigned contact
     * @param Int $clubId    Current club
     *
     * @return Array
     */
    public function getContactsDocuments($contactId, $clubId)
    {
        $resultQuery = $this->createQueryBuilder('A')
                ->select('GROUP_CONCAT(DISTINCT D.id) as documents')
                ->innerJoin("CommonUtilityBundle:FgDmDocuments", "D", "WITH", "A.document = D.id AND D.documentType = 'CONTACT' AND A.documentType = 'CONTACT' ")
                ->where("A.documentType = :contact")
                ->andWhere('A.contact = :contactId')
                ->andWhere('D.club = :clubId');
        $resultQuery->setParameters(array('contactId' => $contactId, 'contact' => 'CONTACT', 'clubId' => $clubId));
        $results = $resultQuery->getQuery()->getArrayResult();

        return $results[0];
    }

    /**
     * Function to remove document assignments
     *
     * @param string $assignmentIds Assigned ids are ',' separated  string
     * @param int    $changedBy     Current logged in contact
     */
    public function removeDocumentAssignmentOfContact($assignmentIds, $changedBy = 1)
    {
        $excludeDetailsArr = array();
        $assignmentIds = explode(",", $assignmentIds);
        foreach ($assignmentIds as $key => $assignmentId) {
            $assignment = $this->find($assignmentId);
            $excludeDetailsArr[] = array('id' => $assignment->getContact()->getId(), 'documentId' => $assignment->getDocument()->getId());
            $this->_em->remove($assignment);
        }
        //add removed contacts to excluded  list
        $this->addToExcludedList($excludeDetailsArr, 'contact');
        $this->addContactExcludedLogEntries($excludeDetailsArr, 'contact', $changedBy);
        $this->_em->flush();
    }

    /**
     * Function to remove all document assignments  of a particular contact
     * 
     * @param int $contactId     ContactId
     * @param int $changedBy     Current logged in contact
     * @param int $currentClubId Current club id
     *
     * @return boolean
     */

    public function removeAllContactDocumentAssignments($contactId, $changedBy = 1, $currentClubId)
    {
        $documents = $this->getContactsDocuments($contactId, $currentClubId);
        if ($documents['documents'] != '') {
            $documentIds = explode(',', $documents['documents']);
            $excludeDetailsArr = array();
            foreach ($documentIds as $key => $documentId) {
                $excludeDetailsArr[] = array('id' => $contactId, 'documentId' => $documentId);
            }
            //add removed contacts to excluded  list
            $this->addToExcludedList($excludeDetailsArr, 'contact');
            $this->addContactExcludedLogEntries($excludeDetailsArr, 'contact', $changedBy);
            $this->_em->flush();
            //remove all document assignments for this contact by this current club
            $deleteSql = 'DELETE a FROM fg_dm_assigment a INNER JOIN fg_dm_documents d ON d.id = a.document_id WHERE a.document_type = :docType AND a.contact_id = :contactId AND d.club_id = :currentClubId AND d.document_type = :docType';
            $conn = $this->getEntityManager()->getConnection();
            $conn->executeQuery($deleteSql, array('contactId' => $contactId, 'currentClubId' => $currentClubId, 'docType' => 'CONTACT'));
        }

        return true;
    }

    /**
     * Function to assign a document to a contact
     *
     * @param int $contactId  AssoignedToContactId
     * @param int $documentId DocumentId
     * @param int $changedBy  Logged in user
     */
    public function addDocumentAssignment($contactId, $documentId, $changedBy) {
        $includedDetailsArr = array();
        $resultQuery = $this->createQueryBuilder('A')
                ->select('COUNT(A.id) as Cnt')
                ->innerJoin("CommonUtilityBundle:FgDmDocuments", "D", "WITH", "A.document = D.id ")
                ->where('A.contact = :contactId')
                ->andWhere('A.document = :document');
        $resultQuery->setParameter('contactId', $contactId);
        $resultQuery->setParameter('document', $documentId);
        $results = $resultQuery->getQuery()->getArrayResult();
        if ($results[0]['Cnt'] == 0) {
            $objDocumentId = $this->_em->getRepository('CommonUtilityBundle:FgDmDocuments')->find($documentId);
            $objContactId = $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->find($contactId);
            $documentType = "CONTACT";
            $docAssignment = new \Common\UtilityBundle\Entity\FgDmAssigment();
            $docAssignment->setDocument($objDocumentId);
            $docAssignment->setDocumentType($documentType);
            $docAssignment->setContact($objContactId);
            $docAssignment->setContactAssignType('MANUAL');
            $this->_em->persist($docAssignment);
            $this->_em->flush();
            $includedDetailsArr[] = array('id' => $contactId, 'documentId' => $documentId);
            $this->addContactIncludedLogEntries($includedDetailsArr, 'contact', $changedBy);
            //update contact last updated field
            $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->updateLastUpdated($contactId, 'id');
        }
    }

    /**
     * Function to assign a document to a club
     *
     * @param int $currentClubId Assigned club
     * @param int $documentId    DocumentId
     * @param int $changedBy     Logged in user
     */
    public function addClubDocumentAssignment($currentClubId, $documentId, $changedBy)
    {
        $includedDetailsArr = array();
        $resultQuery = $this->createQueryBuilder('A')
                ->select('COUNT(A.id) as Cnt')
                ->innerJoin("CommonUtilityBundle:FgDmDocuments", "D", "WITH", "A.document = D.id ")
                ->where('A.club = :currentClubId')
                ->andWhere("A.documentType = 'CLUB' ")
                ->andWhere('A.document = :document');
        $resultQuery->setParameter('currentClubId', $currentClubId);
        $resultQuery->setParameter('document', $documentId);
        $results = $resultQuery->getQuery()->getArrayResult();
        if ($results[0]['Cnt'] == 0) {
            $objClubId = $this->_em->getRepository('CommonUtilityBundle:FgClub')->find($currentClubId);
            $objDocumentId = $this->_em->getRepository('CommonUtilityBundle:FgDmDocuments')->find($documentId);
            if ($objDocumentId->getDepositedWith() != 'ALL') {
                $documentType = "CLUB";
                $docAssignment = new \Common\UtilityBundle\Entity\FgDmAssigment();
                $docAssignment->setDocument($objDocumentId);
                $docAssignment->setDocumentType($documentType);
                $docAssignment->setClub($objClubId);
                $this->_em->persist($docAssignment);
                $this->_em->flush();
            }
            $includedDetailsArr[] = array('id' => $currentClubId, 'documentId' => $documentId);
            $this->addClubIncludedLogEntries($includedDetailsArr, 'club', $changedBy);
        }
    }

    /**
     * Function to execute remove all document assignments of a particular club
     * 
     * @param int    $clubId        Assigned club
     * @param int    $currentClubId Current club id
     * @param object $container     Container object
     * @param int    $changedBy     Current logged in contact
     *
     * @return boolean
     */
    public function removeAllClubDocumentAssignments($clubId, $currentClubId, $container, $changedBy = 1)
    {
        $addToExcludeDetails = $excludeDetailsArr = array();
        $docPdo = new DocumentPdo($container);
        $documents = $docPdo->getClubDocuments($clubId, $currentClubId);
        if ($documents['documents'] != '') {
            $documentIds = explode(',', $documents['documents']);
            foreach ($documentIds as $key => $documentId) {
                //add removed clubs to excluded  list
                $addToExclude = $this->checkWhetherToAddExcludedEntry($documentId);
                if ($addToExclude) {
                    $addToExcludeDetails[] = array('id' => $clubId, 'documentId' => $documentId);
                }
                $excludeDetailsArr[] = array('id' => $clubId, 'documentId' => $documentId);
            }
            if (count($excludeDetailsArr) > 0) {
                $this->addToExcludedList($addToExcludeDetails, 'club');
                $this->addClubExcludedLogEntries($excludeDetailsArr, 'club', $changedBy);
            }
            $this->_em->flush();
            //delete this clubs assignments for a club
            $docPdo->deleteDocumentAssignment($clubId, $currentClubId);

            return true;
        }
    }

    /*
     * Function to get count of assigned documents of club or contact
     * 
     * @param $type (CONTACT OR CLUB)
     * @param $clubId - club of document
     * @param $assignedTo - primary id of assigned club or contact
     */
    public function getCountOfAssignedDocuments($type, $clubId, $assignedTo) {
        if ($type === 'CONTACT') {
            $resultQuery = $this->createQueryBuilder('A')
                    ->select('COUNT(A.id) as Cnt')
                    ->innerJoin("CommonUtilityBundle:FgDmDocuments", "D", "WITH", "A.document = D.id AND A.documentType = :type AND D.documentType = :type ")
                    ->where("D.club = :club");
            $resultQuery->andWhere("A.contact = :assignedTo");
            $resultQuery->setParameter('type', $type);
            $resultQuery->setParameter('club', $clubId);
            $resultQuery->setParameter('assignedTo', $assignedTo);
            $results = $resultQuery->getQuery()->getArrayResult();

            return $results[0]['Cnt'];
        }
    }

    /**
     * Function to remove heirarchy level documents when federation membership is removed
     *
     * @param Integer $clubId    Club id
     * @param Integer $contactId Contact Id
     */
    public function removeFedDocsForContact($clubId = 0, $contactId = 0)
    {
        if ($clubId != 0 && $contactId != 0) {
            $resultQuery = $this->createQueryBuilder('a')
                    ->delete()
                    ->where("a.contact = :contactId")
                    ->andWhere("a.document IN (SELECT d.id FROM CommonUtilityBundle:FgDmDocuments d WHERE d.club = :clubId AND d.documentType = 'CONTACT')")
                    ->setParameters(array('contactId' => $contactId, 'clubId' => $clubId));
            $resultQuery->getQuery()->execute();
        }
    }

    /**
     * Function to get previous deposited with selection ids for document log entry
     *
     * @param int    $documentId   DocumentId
     * @param string $documentType DocumentType (club/contact/team/workgroup)
     *
     * @return string $ret Comma seperated ids
     */
    public function getPreviousAssignments($documentId, $documentType = 'club')
    {
        $qb = $this->createQueryBuilder('a');
        $qb = ($documentType == 'club') ? $qb->select('GROUP_CONCAT(DISTINCT a.club) AS ids') : ((($documentType == 'contact') || ($documentType == 'contactfilter')) ? $qb->select('GROUP_CONCAT(DISTINCT a.contact) AS ids') : $qb->select('GROUP_CONCAT(DISTINCT a.role) AS ids'));
        $qb = $qb->where("a.document IN (:documentId)");
        if ($documentType == 'contact') {
            $qb = $qb->andWhere('a.contactAssignType = :assignType')
                     ->setParameter('assignType', 'MANUAL');
        }
        else if ($documentType == 'contactfilter') {
            $qb = $qb->andWhere('a.contactAssignType = :assignType')
                     ->setParameter('assignType', 'FILTER');
        }
        $qb = $qb->setParameter('documentId', $documentId);

        $results = $qb->getQuery()->getArrayResult();
        
        return $results[0]['ids'];
    }

    /**
     * Function to get previous visible for selected team function ids for document log entry
     *
     * @param int $documentId DocumentId
     *
     * @return string $ret Comma seperated ids
     */
    public function getPreviousAssignedFunctions($documentId)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->select('a.id, GROUP_CONCAT(DISTINCT a.function) AS functionIds')
                ->from('CommonUtilityBundle:FgDmTeamFunctions', 'a')
                ->where("a.document = :documentId")
                ->setParameter('documentId', $documentId);
        $results = $qb->getQuery()->getArrayResult();
        
        return $results[0]['functionIds'];
    }

    /**
     * Function to get previous comma seperated deposited with excluded clubs
     *
     * @param int    $documentId   DocumentId
     * @param string $documentType DocumentType (club/contact)
     *
     * @return string $ret Comma seperated ids
     */
    public function getPreviousExcludedClubsOrContacts($documentId, $documentType = 'club')
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb = ($documentType == 'club') ? $qb->select('GROUP_CONCAT(DISTINCT a.club) AS ids') : $qb->select('GROUP_CONCAT(DISTINCT a.contact) AS ids');
        $qb = $qb->from('CommonUtilityBundle:FgDmAssigmentExclude', 'a')
                ->where("a.document = :documentId")
                ->setParameter('documentId', $documentId);
        $results = $qb->getQuery()->getArrayResult();
        
        return $results[0]['ids'];
    }

    /**
     * Function to add excluded clubs/contacts
     *
     * @param array  $excludedDetailsArr Excluded Details array
     * @param string $docType            Document type club/contact
     */
    private function addToExcludedList($excludedDetailsArr = array(), $docType = 'club')
    {
        $id = '';
        foreach ($excludedDetailsArr as $key => $val) {
            if ($id == '') {
                $id = $val['id'];
                $clubObj = ($docType == 'club') ? $this->_em->getReference('CommonUtilityBundle:FgClub', $id) : null;
                $contactObj = ($docType == 'contact') ? $this->_em->getReference('CommonUtilityBundle:FgCmContact', $id) : null;
            }
            $docObj = $this->_em->getReference('CommonUtilityBundle:FgDmDocuments', $val['documentId']);
            $excludeClubObj = new \Common\UtilityBundle\Entity\FgDmAssigmentExclude();
            $excludeClubObj->setDocument($docObj);
            $excludeClubObj->setClub($clubObj);
            $excludeClubObj->setContact($contactObj);
            $this->_em->persist($excludeClubObj);
        }
    }

    /**
     * Function to add excluded contacts log entries
     *
     * @param array  $excludedDetailsArr Excluded Details array
     * @param string $docType            Document type club/team/contact/workgroup
     * @param int    $changedBy          current logged in contact
     */
    private function addContactExcludedLogEntries($excludedDetailsArr = array(), $docType = 'contact', $changedBy = 1)
    {
        $clubId = '';
        $contactObj = $this->_em->getReference('CommonUtilityBundle:FgCmContact', $changedBy);
        foreach ($excludedDetailsArr as $key => $val) {
            $documentId = $val['documentId'];
            $contactId = $val['id'];
            $docObj = $this->_em->getRepository('CommonUtilityBundle:FgDmDocuments')->find($documentId);
            if ($clubId != $docObj->getClub()->getId()) {
                $clubId = $docObj->getClub()->getId();
                $clubObj = $this->_em->getReference('CommonUtilityBundle:FgClub', $docObj->getClub()->getId());
            }
            $isIncluded = $this->checkWhetherAlreadyAssignedOrNot($contactId, $documentId, $docType, 'included');
            if ($isIncluded) {
                $this->deleteIncludedAssignment($documentId, $contactId, $docType);
                $lastIncludedLogEntry = $this->getLastLogEntry($documentId, 'included');
                $valueBeforeId = $lastIncludedLogEntry['valueAfterId'];
                $valueBeforeIdArr = explode(',', $valueBeforeId);
                $valueAfterIdArr = array_diff($valueBeforeIdArr, array($contactId));
                $valueAfterId = implode(',', $valueAfterIdArr);
                $this->insertDocumentLogEntry($docObj, strtoupper($docType), '', '', 'included', 'Included contacts', $clubObj, $valueBeforeId, $valueAfterId, $contactObj);
            }
            $lastExcludedLogEntry = $this->getLastLogEntry($documentId, 'excluded');
            $valueAfterId = (count($lastExcludedLogEntry) > 0) ? ($lastExcludedLogEntry['valueAfterId'] == '' || is_null($lastExcludedLogEntry['valueAfterId'])) ? $contactId : $lastExcludedLogEntry['valueAfterId'] . ',' . $contactId : $contactId;
            $valueBeforeId = (count($lastExcludedLogEntry) > 0) ? $lastExcludedLogEntry['valueAfterId'] : '';
            $this->insertDocumentLogEntry($docObj, strtoupper($docType), '', '', 'excluded', 'Excluded contacts', $clubObj, $valueBeforeId, $valueAfterId, $contactObj);
        }
    }

    /**
     * Function to add excluded clubs log entries
     *
     * @param array  $excludedDetailsArr Excluded Details array
     * @param string $docType            Document type club/team/contact/workgroup
     * @param int    $changedBy          current logged in contact
     */
    private function addClubExcludedLogEntries($excludedDetailsArr = array(), $docType = 'club', $changedBy = 1)
    {
        $clubId = '';
        $contactObj = $this->_em->getReference('CommonUtilityBundle:FgCmContact', $changedBy);
        foreach ($excludedDetailsArr as $key => $val) {
            $documentId = $val['documentId'];
            $removedClubId = $val['id'];
            $docObj = $this->_em->getRepository('CommonUtilityBundle:FgDmDocuments')->find($documentId);
            $valueAfter = $docObj->getDepositedWith();
            if ($valueAfter == 'SELECTED') {
                $assignmentIds = $this->getPreviousAssignments($documentId, $docType);
                $assignmentIdArr = explode(',', $assignmentIds);
                if (count($assignmentIdArr) <= 1) {
                    $valueAfter = 'NONE';
                    $this->_em->getRepository('CommonUtilityBundle:FgDmDocuments')->changeDocumentDepositedWith($documentId, 'NONE');
                }
            }
            if ($clubId != $docObj->getClub()->getId()) {
                $clubId = $docObj->getClub()->getId();
                $clubObj = $this->_em->getReference('CommonUtilityBundle:FgClub', $docObj->getClub()->getId());
            }
            $lastLogEntry = $this->getLastLogEntry($documentId, 'deposited_with');
            $valueBefore = (count($lastLogEntry) > 0) ? $lastLogEntry['valueAfter'] : '';
            $valueBeforeId = (count($lastLogEntry) > 0) ? $lastLogEntry['valueAfterId'] : '';
            if ($valueAfter == 'ALL') {
                $valueAfterId = (($valueBeforeId == '') || (is_null($valueBeforeId))) ? $removedClubId : ($valueBeforeId . ',' . $removedClubId);
            } else {
                $valueBeforeIdArr = explode(',', $valueBeforeId);
                $valueAfterIdArr = array_diff($valueBeforeIdArr, array($removedClubId));
                $valueAfterId = implode(',', $valueAfterIdArr);
            }
            $this->insertDocumentLogEntry($docObj, strtoupper($docType), $valueBefore, $valueAfter, 'deposited_with', 'Deposited with', $clubObj, $valueBeforeId, $valueAfterId, $contactObj);
        }
    }

    /**
     * Function to add log entries on assigning documents to contacts
     *
     * @param array  $includedDetailsArr Included Contact Details
     * @param string $docType            DocumentType
     * @param int    $changedBy          Logged in contact
     */
    private function addContactIncludedLogEntries($includedDetailsArr = array(), $docType = 'contact', $changedBy = 1)
    {
        $clubId = '';
        $contactObj = $this->_em->getReference('CommonUtilityBundle:FgCmContact', $changedBy);
        foreach ($includedDetailsArr as $key => $val) {
            $documentId = $val['documentId'];
            $contactId = $val['id'];
            $docObj = $this->_em->getRepository('CommonUtilityBundle:FgDmDocuments')->find($documentId);
            if ($clubId != $docObj->getClub()->getId()) {
                $clubId = $docObj->getClub()->getId();
                $clubObj = $this->_em->getReference('CommonUtilityBundle:FgClub', $docObj->getClub()->getId());
            }
            $isExcluded = $this->checkWhetherAlreadyAssignedOrNot($contactId, $documentId, $docType, 'excluded');
            if ($isExcluded) {
                $this->deleteExcludedAssignment($documentId, $contactId, $docType);
                $lastExcludedLogEntry = $this->getLastLogEntry($documentId, 'excluded');
                $valueBeforeId = $lastExcludedLogEntry['valueAfterId'];
                $valueBeforeIdArr = explode(',', $valueBeforeId);
                $valueAfterIdArr = array_diff($valueBeforeIdArr, array($contactId));
                $valueAfterId = implode(',', $valueAfterIdArr);
                $this->insertDocumentLogEntry($docObj, strtoupper($docType), '', '', 'excluded', 'Excluded contacts', $clubObj, $valueBeforeId, $valueAfterId, $contactObj);
            }
            $lastLogEntryIncluded = $this->getLastLogEntry($val['documentId'], 'included');
            $valueAfterId = (count($lastLogEntryIncluded) > 0) ? ($lastLogEntryIncluded['valueAfterId'] == '' || is_null($lastLogEntryIncluded['valueAfterId'])) ? $contactId : $lastLogEntryIncluded['valueAfterId'] . ',' . $contactId : $contactId;
            $valueBeforeId = (count($lastLogEntryIncluded) > 0) ? $lastLogEntryIncluded['valueAfterId'] : '';
            $this->insertDocumentLogEntry($docObj, strtoupper($docType), '', '', 'included', 'Included contacts', $clubObj, $valueBeforeId, $valueAfterId, $contactObj);
        }
    }

    /**
     * Function to add log entries on assigning documents to club
     *
     * @param array  $includedDetailsArr Included Club Details
     * @param string $docType            DocumentType
     * @param int    $changedBy          Logged in contact
     */
    private function addClubIncludedLogEntries($includedDetailsArr = array(), $docType = 'club', $changedBy = 1)
    {
        $clubId = '';
        $contactObj = $this->_em->getReference('CommonUtilityBundle:FgCmContact', $changedBy);
        foreach ($includedDetailsArr as $key => $val) {
            $documentId = $val['documentId'];
            $assignedClubId = $val['id'];
            $docObj = $this->_em->getRepository('CommonUtilityBundle:FgDmDocuments')->find($documentId);
            if ($clubId != $docObj->getClub()->getId()) {
                $clubId = $docObj->getClub()->getId();
                $clubObj = $this->_em->getReference('CommonUtilityBundle:FgClub', $docObj->getClub()->getId());
            }
            $valueBefore = $docObj->getDepositedWith();
            $valueAfter = 'SELECTED';
            $lastLogEntry = $this->getLastLogEntry($documentId, 'deposited_with');
            switch ($valueBefore) {
                case 'NONE':
                    $this->_em->getRepository('CommonUtilityBundle:FgDmDocuments')->changeDocumentDepositedWith($documentId, 'SELECTED');
                    $valueBeforeId = (count($lastLogEntry) > 0) ? $lastLogEntry['valueAfterId'] : '';
                    $valueAfterId = (count($lastLogEntry) > 0) ? ($lastLogEntry['valueAfterId'] == '' || is_null($lastLogEntry['valueAfterId'])) ? $assignedClubId : $lastLogEntry['valueAfterId'] . ',' . $assignedClubId : $assignedClubId;
                    $this->insertDocumentLogEntry($docObj, strtoupper($docType), $valueBefore, $valueAfter, 'deposited_with', 'Deposited with', $clubObj, $valueBeforeId, $valueAfterId, $contactObj);
                    break;

                case 'SELECTED':
                    $valueBeforeId = (count($lastLogEntry) > 0) ? $lastLogEntry['valueAfterId'] : '';
                    $valueAfterId = (count($lastLogEntry) > 0) ? ($lastLogEntry['valueAfterId'] == '' || is_null($lastLogEntry['valueAfterId'])) ? $assignedClubId : $lastLogEntry['valueAfterId'] . ',' . $assignedClubId : $assignedClubId;
                    $this->insertDocumentLogEntry($docObj, strtoupper($docType), $valueBefore, $valueAfter, 'deposited_with', 'Deposited with', $clubObj, $valueBeforeId, $valueAfterId, $contactObj);
                    break;

                case 'ALL':
                    $valueAfter = 'ALL';
                    $isExcluded = $this->checkWhetherAlreadyAssignedOrNot($assignedClubId, $documentId, $docType, 'excluded');
                    if ($isExcluded) {
                        $this->deleteExcludedAssignment($documentId, $assignedClubId, $docType);
                    }
                    $valueBeforeId = (count($lastLogEntry) > 0) ? $lastLogEntry['valueAfterId'] : '';
                    $valueBeforeIdArr = explode(',', $valueBeforeId);
                    $valueAfterIdArr = array_diff($valueBeforeIdArr, array($assignedClubId));
                    $valueAfterId = implode(',', $valueAfterIdArr);
                    $this->insertDocumentLogEntry($docObj, strtoupper($docType), $valueBefore, $valueAfter, 'deposited_with', 'Deposited with', $clubObj, $valueBeforeId, $valueAfterId, $contactObj);
                    break;

                default:
                    break;
            }
        }
    }

    /**
     * Function to save a document log object
     *
     * @param object $docObj        Document object
     * @param string $docType       Document type club/team/workgroup/contact
     * @param string $valueBefore   Value Before
     * @param string $valueAfter    Value After
     * @param string $kind          Action enum
     * @param string $field         Action
     * @param object $clubObj       Club Object
     * @param string $valueBeforeId Value Before Ids
     * @param string $valueAfterId  Value After Ids
     * @param object $contactObj    Contact object
     */
    public function insertDocumentlogEntry($docObj, $docType, $valueBefore, $valueAfter, $kind, $field, $clubObj, $valueBeforeId, $valueAfterId, $contactObj)
    {
        $logObj = new \Common\UtilityBundle\Entity\FgDmDocumentLog();
        $logObj->setDocuments($docObj);
        $logObj->setDocumentType(strtoupper($docType));
        $logObj->setDate(new \DateTime("now"));
        $logObj->setValueBefore($valueBefore);
        $logObj->setValueAfter($valueAfter);
        $logObj->setKind($kind);
        $logObj->setField($field);
        $logObj->setClub($clubObj);
        $logObj->setValueBeforeId($valueBeforeId);
        $logObj->setValueAfterId($valueAfterId);
        $logObj->setChangedBy($contactObj);
        $this->_em->persist($logObj);
        $this->_em->flush();
    }

    /**
     * Function to get last log entry of specific kind
     *
     * @param int    $documentId Document Id
     * @param string $kind       log Type
     *
     * @return array $ret log entry row data
     */
    public function getLastLogEntry($documentId, $kind = 'excluded')
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb = $qb->select('a.valueBefore, a.valueAfter, a.valueBeforeId, a.valueAfterId')
                 ->from('CommonUtilityBundle:FgDmDocumentLog', 'a')
                 ->where("a.documents = :documentId")
                 ->andWhere('a.kind = :kind')
                 ->orderBy('a.id', 'DESC')
                 ->setMaxResults(1)
                 ->setParameter('documentId', $documentId)
                 ->setParameter('kind', $kind);

        $results = $qb->getQuery()->getArrayResult();
        
        return (count($results) > 0) ? $results[0] : array();
    }

    /**
     * Function to check whether last deposited with log entry has 'all clubs' selected
     *
     * @param int $documentId DocumentId
     *
     * @return int $addExclude 0/1
     */
    private function checkWhetherToAddExcludedEntry($documentId)
    {
        $addExclude = 0;
        $documentObj = $this->_em->getRepository('CommonUtilityBundle:FgDmDocuments')->find($documentId);
        $depositedWithAfter = $documentObj->getDepositedWith();
        $addExclude = ($depositedWithAfter == 'ALL') ? 1 : 0;

        return $addExclude;
    }

    /**
     * Function to get excluded documents for a contact/club
     *
     * @param int    $id      excluded contactid/clubid
     * @param string $docType Document type club/contact
     *
     * @return string $results Comma seperated documentIds
     */
    public function getExcludedDocuments($id, $docType = 'contact')
    {
        $resultQuery = $this->getEntityManager()->createQueryBuilder()
                ->select('GROUP_CONCAT(DISTINCT D.id) as documents')
                ->from('CommonUtilityBundle:FgDmAssigmentExclude', 'E')
                ->innerJoin("CommonUtilityBundle:FgDmDocuments", "D", "WITH", "E.document = D.id AND D.documentType = :documentType");
        $resultQuery = ($docType == 'club') ? $resultQuery->andWhere('E.club = :id') : $resultQuery->andWhere('E.contact = :id');
        $resultQuery->setParameter('id', $id)
                    ->setParameter('documentType', strtoupper($docType));
        $results = $resultQuery->getQuery()->getSingleScalarResult();

        return $results;
    }

    /**
     * Function to remove document assignments of club
     *
     * @param string $documentIdsStr DocumentIds are ',' separated string
     * @param int    $clubId         Current club Id
     * @param int    $changedBy      Current logged in contact
     */
    public function removeDocumentAssignmentOfClubs($documentIdsStr = '', $clubId = 0, $changedBy = 1)
    {
        $toExcludeArr = $excludeDetailsArr = array();
        $documentIds = explode(",", $documentIdsStr);
        foreach ($documentIds as $key => $documentId) {
            $documentObj = $this->_em->getRepository('CommonUtilityBundle:FgDmDocuments')->find($documentId);
            if ($documentObj->getDepositedWith() == 'ALL') {
                $toExcludeArr[] = array('id' => $clubId, 'documentId' => $documentId);
            }
            $excludeDetailsArr[] = array('id' => $clubId, 'documentId' => $documentId);
            $assignments = $this->findBy(array('document' => $documentId, 'club' => $clubId));
            foreach ($assignments as $assignment) {
                $this->_em->remove($assignment);
            }
        }
        //add removed clubs to excluded  list
        $this->addToExcludedList($toExcludeArr, 'club');
        $this->addClubExcludedLogEntries($excludeDetailsArr, 'club', $changedBy);
        $this->_em->flush();
    }

    /**
     * Function to check whether contact or club assignment already exists or not
     *
     * @param int    $id         Club/Contact Id
     * @param int    $documentId DocumentId
     * @param string $docType    Document Type club/contact
     * @param string $type       Included/Excluded
     *
     * @return int $result 0/1
     */
    public function checkWhetherAlreadyAssignedOrNot($id, $documentId, $docType = 'club', $type = 'included')
    {
        $resultQuery = $this->getEntityManager()->createQueryBuilder()
            ->select('count(a.id)');
        if ($type == 'excluded') {
            $resultQuery = $resultQuery->from('CommonUtilityBundle:FgDmAssigmentExclude', 'a');
        } else {
            $resultQuery = $resultQuery->from('CommonUtilityBundle:FgDmAssigment', 'a');
        }
        $resultQuery = $resultQuery->where('a.document = :documentId');
        if ($docType == 'club') {
            $resultQuery = $resultQuery->andWhere('a.club = :id');
        } else {
            $resultQuery = $resultQuery->andWhere('a.contact = :id');
        }
        if ($type == 'included') {
            $resultQuery = $resultQuery->andWhere('a.documentType = :documentType')
                ->setParameter('documentType', strtoupper($docType));
            if ($docType == 'contact') {
                $resultQuery = $resultQuery->andWhere('a.contactAssignType = :contactAssignType')
                    ->setParameter('contactAssignType', 'MANUAL');
            }
        }
        $resultQuery = $resultQuery->setParameter('documentId', $documentId)
            ->setParameter('id', $id);
        $result = $resultQuery->getQuery()->getSingleScalarResult();
        $result = ($result > 0) ? 1 : 0;

        return $result;
    }

    /**
     * Function to delete  excluded assignment of club/contact
     *
     * @param int    $documentId DocumentId
     * @param int    $id         Club/contact id
     * @param string $docType    DocumentType club/contact
     */
    private function deleteExcludedAssignment($documentId = 0, $id = 0, $docType = 'club')
    {
        if (($documentId != 0) && ($id != 0)) {
            $resultQuery = $this->getEntityManager()->createQueryBuilder()
                ->delete()
                ->from('CommonUtilityBundle:FgDmAssigmentExclude', 'a');
            $resultQuery = ($docType == 'contact') ? $resultQuery->where("a.contact = :id") : $resultQuery->where("a.club = :id");
            $resultQuery = $resultQuery->andWhere("a.document = :documentId")
                                       ->setParameter('id', $id)
                                       ->setParameter('documentId', $documentId);
            $resultQuery->getQuery()->execute();
        }
    }

    /**
     * Function to delete assignment of contact/club
     *
     * @param int    $documentId DocumentId
     * @param int    $id         Club/contact id
     * @param string $docType    DocumentType club/contact
     */
    private function deleteIncludedAssignment($documentId = 0, $id = 0, $docType = 'club')
    {
        if (($documentId != 0) && ($id != 0)) {
            $resultQuery = $this->getEntityManager()->createQueryBuilder()
                ->delete()
                ->from('CommonUtilityBundle:FgDmAssigment', 'a');
            $resultQuery = ($docType == 'contact') ? $resultQuery->where("a.contact = :id") : $resultQuery->where("a.club = :id");
            $resultQuery = $resultQuery->andWhere("a.document = :documentId")
                                       ->setParameter('id', $id)
                                       ->setParameter('documentId', $documentId);
            $resultQuery->getQuery()->execute();
        }
    }

    /**
     * Function to get deposited count of documents
     *
     * @param int    $documentId DocumentId
     * @param string $docType    DocumentType team/workgroup
     *
     * @return array $finalResultArray
     */
    public function getDepositedcountOfDocuments($documentId, $docType)
    {
        $finalResultArray = array();
        $qb = $this->createQueryBuilder('da')
                ->select('R.id')
                ->innerJoin("CommonUtilityBundle:FgDmDocuments", "D", "WITH", "da.document = D.id AND D.documentType = :documentType")
                ->innerJoin("CommonUtilityBundle:FgRmRole", "R", "WITH", "da.role = R.id AND D.documentType = :documentType")
                ->where("da.document = :documentId")
                ->setParameter('documentId', $documentId)
                ->setParameter('documentType', $docType);

        $result = $qb->getQuery()->getArrayResult();
        foreach ($result as $key => $value) {
            $finalResultArray[$key] = $value['id'];
        }
        
        return $finalResultArray;
    }

}