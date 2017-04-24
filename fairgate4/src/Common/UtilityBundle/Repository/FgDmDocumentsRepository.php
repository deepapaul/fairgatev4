<?php

/**
 * FgDmDocumentsRepository
 *
 * This class was generated for querying document related data`
 */
namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Clubadmin\DocumentsBundle\Util\Documentlist;
use Common\UtilityBundle\Util\FgPermissions;
use Common\UtilityBundle\Repository\Pdo\DocumentPdo;
use Clubadmin\DocumentsBundle\Util\DocumentDetails;

/**
 * FgDmDocumentsRepository
 *
 * This class is used for managing documents
 */
class FgDmDocumentsRepository extends EntityRepository
{

    /**
     * Function to save uploaded/edited document details
     *
     * @param Object $container   Container Object
     * @param array  $docArr      Document details array
     * @param string $docType     Document type club/team/contact/workgroup
     * @param array  $clubDetails Club details array
     * @param int    $currContact Current contact id
     * 
     * @return array
     */
    public function saveDocumentDetails($container, $docArr = array(), $docType = 'club', $clubDetails = array(), $currContact = 1)
    {
        $club = $container->get('club');
        $bookedModuleDetails = $club->get('bookedModulesDet');
        $isFrontend1Booked = (in_array('frontend1', $bookedModuleDetails)) ? 1 : 0;
        $clubId = $clubDetails['clubId'];
        $clubType = $clubDetails['clubType'];
        $clubDefaultLang = $clubDetails['clubDefaultLang'];
        $documentI18nArr = $documentVersionArr = $documentAssignmentsArr = $documentFunctionsArr = $returnArr = array();
        $currContactObj = $this->_em->getReference('CommonUtilityBundle:FgCmContact', $currContact);
        $docDetObj = new DocumentDetails($container);
        $docDetObj->createDocumentsUploadFolder($clubId);
        foreach ($docArr as $doc) {
            $docDetObj->setDocumentLogArr(array());
            $categoryDetails = $this->_em->getRepository('CommonUtilityBundle:FgDmDocumentCategory')->getCategoryClubId($doc['subCategoryId']);
            if (count($categoryDetails) > 0) {
                //check whether uploaded subcategory is of the current club or not
                if ($categoryDetails['clubId'] == $clubId) {
                    $categoryId = $categoryDetails['id'];
                    $isNewObject = $isVisible = 0;
                    $filterData = '';
                    $visibleFor = 'none';
                    $depositedWith = 'NONE';
                    $tmpFileName = $doc['filename'];
                    $docId = (isset($doc['documentId'])) ? $doc['documentId'] : 0;
                    //if new upload or file change move file to specific folder
                    if (($docId != 0) || ($tmpFileName != '')) {
                        if ($tmpFileName != '') {
                            $fileDetails = $docDetObj->moveFileToDocumentsFolder($tmpFileName, $clubId);
                            $fileName = $fileDetails['fileName'];
                            $fileSize = $fileDetails['fileSize'];
                        }
                        switch ($docType) {
                            case 'club':
                                $isVisible = (isset($doc['isVisible'])) ? $doc['isVisible'] : 0;
                                $depositedWith = ($clubType == 'federation' || $clubType == 'sub_federation') ? $doc['depositedWith'] : 'NONE';
                                break;
                            case 'team':
                            case 'workgroup':
                                if (isset($doc['depositedWith'][0])) {
                                    if (($doc['depositedWith'][0] == 'ALL') || ($doc['depositedWith'][0] == 'NONE')) {
                                        $depositedWith = $doc['depositedWith'][0];
                                        $doc['depositedWithSelection'] = array();
                                    } else {
                                        $depositedWith = 'SELECTED';
                                        $doc['depositedWithSelection'] = $doc['depositedWith'];
                                    }
                                }
                                $visibleFor = (isset($doc['visibleFor'])) ? $doc['visibleFor'] : (($docType == 'team') ? 'club_contact_admin' : 'main_document_admin');
                                break;
                            case 'contact':
                                $isVisible = (isset($doc['isVisible'])) ? $doc['isVisible'] : 0;
                                $depositedWith = 'SELECTED';
                                $filterData = (isset($doc['filterData'])) ? (($doc['filterData'] != '{"contact_filter":{}}') ? $doc['filterData'] : '') : '';
                                break;
                            default:
                                break;
                        }
                        
                        $isPublic = (isset($doc['isPublic'])) ? $doc['isPublic'] : 0;
                        $docTransArr = (isset($doc['i18n'][$clubDefaultLang])) ? $doc['i18n'][$clubDefaultLang] : array();
                        $docObj = $this->_em->getRepository('CommonUtilityBundle:FgDmDocuments')->find($docId);
                        if ($docObj === null) {
                            $docObj = new \Common\UtilityBundle\Entity\FgDmDocuments();
                            $isNewObject = 1;
                        }

//                      buildDocumentDataArray
                        $documentData = array('docType' => $docType, 'clubId' => $clubId, 'categoryId' => $categoryId, 'subCategoryId' => $doc['subCategoryId'], 'name' => $docTransArr['name'], 'description' => $docTransArr['description'], 'author' => $docTransArr['author'], 'isVisible' => $isVisible,'isPublic'=> $isPublic, 'filterData' => $filterData, 'visibleFor' => $visibleFor, 'depositedWith' => $depositedWith);
                        //insert document data log entries
                        $docDetObj->populateDocumentDataLogEntries($docObj, $isNewObject, $documentData, $isFrontend1Booked);
                        $oldDepositedWith = $docObj->getDepositedWith();
                        $oldVisibleFor = $docObj->getVisibleFor();
                        //save document data
                        $id = $this->saveDocumentData($docObj, $documentData, $isNewObject);

                        // if change in uploaded file update document version details
                        if ($tmpFileName != '') {
                            $documentVersionArr = array('document_id' => $id, 'filename' => $fileName, 'created_at' => new \DateTime("now"), 'created_by' => $currContactObj, 'updated_at' => new \DateTime("now"), 'updated_by' => $currContactObj, 'size' => $fileSize);
                            $docVerObj = $this->saveDocumentVersionDetails($documentVersionArr);
                            $docObj->setCurrentRevision($docVerObj);
                            $this->_em->persist($docObj);
                        } else {
                            $currentVersionId = $docObj->getCurrentRevision()->getId();
                            $docVerObj = $this->_em->getRepository('CommonUtilityBundle:FgDmVersion')->find($currentVersionId);
                            $docVerObj->setUpdatedAt(new \DateTime("now"));
                            $docVerObj->setUpdatedBy($currContactObj);
                            $this->_em->persist($docVerObj);
                        }
                        $this->_em->flush();
                        $returnArr[] = array('catId' => $categoryId, 'subCatId' => $doc['subCategoryId']);
                        foreach ($doc['i18n'] as $lang => $valArr) {
                            $documentI18nArr[] = array('id' => $id, 'lang' => $lang, 'name' => $valArr['name'], 'description' => $valArr['description'], 'author' => $valArr['author']);
                        }
                        $oldDepositedWithSelectionIds = ($isNewObject == 1) ? '' : $this->_em->getRepository('CommonUtilityBundle:FgDmAssigment')->getPreviousAssignments($id, $docType);
                        $oldFilterDepositedWithSelectionIds = ($isNewObject == 1) ? '' : $this->_em->getRepository('CommonUtilityBundle:FgDmAssigment')->getPreviousAssignments($id, 'contactfilter');
                        $oldDepositedArr = ($oldDepositedWithSelectionIds) ? explode(',', $oldDepositedWithSelectionIds) : array();
                        $oldFilterDepositedArr = ($oldFilterDepositedWithSelectionIds) ? explode(',', $oldFilterDepositedWithSelectionIds) : array();
                        $this->deleteOldRecordsFromTable('FgDmAssigment', 'document', $id);
                        $depositedWithSelectionArr = array();
                        //insert deposited with values and corresponding log entries for club.team.contact,club documents in federation,subfederation,etc..
                        if ($depositedWith == 'SELECTED') {
                            if ($docType != 'contact') {
                                foreach ($doc['depositedWithSelection'] as $val) {
                                    if (empty($val)) {
                                        continue;
                                    }
                                    $selRoleId = ($docType == 'team' || $docType == 'workgroup') ? $val : '';
                                    $selClubId = ($docType == 'club') ? $val : '';
                                    $depositedWithSelectionArr[] = $val;
                                    $documentAssignmentsArr[] = array('document_id' => $id, 'document_type' => $docType, 'club_id' => $selClubId, 'role_id' => $selRoleId);
                                }
                            } else {
                                $depositedWithContactArr = array();
                                foreach ($doc['depositedWithSelection'] as $val) {
                                    if (empty($val)) {
                                        continue;
                                    }
                                    $depositedWithContactArr[] = $val;
                                }
                                $excludedSelection = (is_array($doc['excludedSelection'])) ? $doc['excludedSelection'] : array();
                                $docDetObj->populateContactIncludeAndExcludeLogEntries($id, $isNewObject, $doc['depositedWithSelection'], $excludedSelection);
                                $this->saveDocumentAssignmentDetailsOfContact($container, $docObj, $doc['depositedWithSelection'], $filterData, $excludedSelection, $oldDepositedArr, $depositedWithContactArr, $oldFilterDepositedArr);
                            }
                        }
                        if ($docType == 'club') {
                            $oldDepositedWithSelectionIds = ($oldDepositedWith == 'ALL') ? ($this->_em->getRepository('CommonUtilityBundle:FgDmAssigment')->getPreviousExcludedClubsOrContacts($id, 'club')) : $oldDepositedWithSelectionIds;
                            $this->deleteOldRecordsFromTable('FgDmAssigmentExclude', 'document', $id);
                            if (($clubType == 'federation' || $clubType == 'sub_federation')) {
                                if ($depositedWith == 'ALL') {
                                    $depositedWithSelectionIds = implode(',', $doc['excludedSelection']);
                                    $this->saveDocumentExcludedClubs($id, $doc['excludedSelection']);
                                    $docDetObj->populateDocumentAssignmentLogEntries('deposited_with', (($isNewObject == 1) ? '' : $oldDepositedWith), $depositedWith, $oldDepositedWithSelectionIds, $depositedWithSelectionIds);
                                } else {
                                    $depositedWithSelectionIds = implode(',', $depositedWithSelectionArr);
                                    $docDetObj->populateDocumentAssignmentLogEntries('deposited_with', (($isNewObject == 1) ? '' : $oldDepositedWith), $depositedWith, $oldDepositedWithSelectionIds, $depositedWithSelectionIds);
                                }
                            }
                        }
                        if (($docType == 'workgroup') || ($docType == 'team')) {
                            $depositedWithSelectionIds = implode(',', $depositedWithSelectionArr);
                            $docDetObj->populateDocumentAssignmentLogEntries('deposited_with', (($isNewObject == 1) ? '' : $oldDepositedWith), $depositedWith, $oldDepositedWithSelectionIds, $depositedWithSelectionIds);
                        }
                        if ($docType == 'team') {
                            $oldVisibleForFunctionIds = ($isNew == 1) ? '' : (($docObj->getVisibleFor() == 'team_functions') ? $this->_em->getRepository('CommonUtilityBundle:FgDmAssigment')->getPreviousAssignedFunctions($id) : '');
                            $this->deleteOldRecordsFromTable('FgDmTeamFunctions', 'document', $id);
                            $teamFunctionIdsArr = array();
                            if ($visibleFor == 'team_functions') {
                                foreach ($doc['visibleForSelection'] as $val) {
                                    $documentFunctionsArr[] = array('document_id' => $id, 'function_id' => $val);
                                    $teamFunctionIdsArr[] = $val;
                                }
                            }
                        }
                        //insert visible for log entries for team.workgroup documents
                        if ((($docType == 'workgroup') || ($docType == 'team')) && ($isFrontend1Booked)) {
                            $visibleForFunctionIds = ($docType == 'team') ? implode(',', $teamFunctionIdsArr) : '';
                            $docDetObj->populateDocumentAssignmentLogEntries('visible_for', (($isNewObject == 1) ? '' : $oldVisibleFor), $visibleFor, $oldVisibleForFunctionIds, $visibleForFunctionIds);
                        }
                        //insert corresponding log entries
                        $docDetObj->insertDocumentLogEntries($docType, $id, $currContact);
                    }
                }
            }
        }
        if ((count($documentI18nArr) > 0) || (count($documentAssignmentsArr) > 0) || (count($documentFunctionsArr) > 0)) {
            if (count($documentI18nArr) > 0) {
                $docObj = new DocumentPdo($container);
                $docObj->saveDocumentI18nDetails($documentI18nArr);
                if ($isNewObject != 1) {
                    $docObj->updateMainTable($clubDefaultLang, $docId);
                }
            }
            if (count($documentAssignmentsArr) > 0) {
                $this->saveDocumentAssignmentDetails($documentAssignmentsArr);
            }
            if (count($documentFunctionsArr) > 0) {
                $this->saveDocumentFunctionDetails($documentFunctionsArr);
            }
            $this->_em->flush();
        }

        return $returnArr;
    }

    /**
     * Function to save document data
     *
     * @param object $docObj       Document object
     * @param array  $documentData Data
     * @param int    $isNewObject  Create/edit
     *
     * @return int $id Saved document object id
     */
    private function saveDocumentData($docObj, $documentData, $isNewObject)
    {
        $categoryObj = $this->_em->getReference('CommonUtilityBundle:FgDmDocumentCategory', $documentData['categoryId']);
        $subCategoryObj = $this->_em->getReference('CommonUtilityBundle:FgDmDocumentSubcategory', $documentData['subCategoryId']);
        if ($isNewObject == 1) {
            $clubObj = $this->_em->getReference('CommonUtilityBundle:FgClub', $documentData['clubId']);
            $docObj->setClub($clubObj);
            $docObj->setDocumentType($documentData['docType']);
        }
        $name = str_replace('<script', '<scri&nbsp;pt', $documentData['name']);
        $description = str_replace('<script', '<scri&nbsp;pt', $documentData['description']);
        $author = str_replace('<script', '<scri&nbsp;pt', $documentData['author']);
        $docObj->setCategory($categoryObj);
        $docObj->setSubcategory($subCategoryObj);
        $docObj->setName($name);
        $docObj->setDescription($description);
        $docObj->setAuthor($author);
        $docObj->setIsVisibleToContact($documentData['isVisible']);
        $docObj->setFilter($documentData['filterData']);
        $docObj->setVisibleFor($documentData['visibleFor']);
        $docObj->setDepositedWith($documentData['depositedWith']);
        $docObj->setIsPublishLink($documentData['isPublic']);
        $this->_em->persist($docObj);
        $this->_em->flush();
        $id = $docObj->getId();

        return $id;
    }

    /**
     * Function to save excluded deposited with  clubs
     *
     * @param int   $documentId      Document Id
     * @param array $excludeClubsArr Array of excluded clubids
     */
    private function saveDocumentExcludedClubs($documentId, $excludeClubsArr = array())
    {
        $docObj = $this->_em->getReference('CommonUtilityBundle:FgDmDocuments', $documentId);
        foreach ($excludeClubsArr as $key => $val) {
            if (!empty($val)) {
                $clubObj = $this->_em->getReference('CommonUtilityBundle:FgClub', $val);
                $excludeClubObj = new \Common\UtilityBundle\Entity\FgDmAssigmentExclude();
                $excludeClubObj->setDocument($docObj);
                $excludeClubObj->setClub($clubObj);
                $this->_em->persist($excludeClubObj);
            }
        }
    }

    /**
     * Function to save document version details
     *
     * @param array $documentVersionArr Document version details array
     *
     * @return \Common\UtilityBundle\Entity\FgDmVersion
     */
    protected function saveDocumentVersionDetails($documentVersionArr)
    {
        $docObj = $this->_em->getReference('CommonUtilityBundle:FgDmDocuments', $documentVersionArr['document_id']);

        $documentObj = $this->_em->getRepository('CommonUtilityBundle:FgDmContactSighted')->findOneBy(array('document' => $documentVersionArr['document_id']));
        if ($documentObj) {
            $this->_em->remove($documentObj);
            $this->_em->flush();
        }

        $docVerObj = new \Common\UtilityBundle\Entity\FgDmVersion();
        $docVerObj->setDocument($docObj);
        $docVerObj->setFile($documentVersionArr['filename']);
        $docVerObj->setCreatedAt($documentVersionArr['created_at']);
        $docVerObj->setCreatedBy($documentVersionArr['created_by']);
        $docVerObj->setUpdatedAt($documentVersionArr['updated_at']);
        $docVerObj->setUpdatedBy($documentVersionArr['updated_by']);
        $docVerObj->setSize($documentVersionArr['size']);
        $this->_em->persist($docVerObj);
        $this->_em->flush();

        return $docVerObj;
    }

    /**
     * Function to save document assignments details
     * 
     * @param array $documentAssignmentsArr Document assignmebnts details
     * 
     */
    protected function saveDocumentAssignmentDetails($documentAssignmentsArr)
    {
        foreach ($documentAssignmentsArr as $key => $valArr) {
            $docObj = $this->_em->getReference('CommonUtilityBundle:FgDmDocuments', $valArr['document_id']);
            $docAssignObj = new \Common\UtilityBundle\Entity\FgDmAssigment();
            $docAssignObj->setDocument($docObj);
            $docAssignObj->setDocumentType($valArr['document_type']);
            if ($valArr['club_id'] != '') {
                $clubObj = $this->_em->getReference('CommonUtilityBundle:FgClub', $valArr['club_id']);
                $docAssignObj->setClub($clubObj);
            }
            if ($valArr['contact_id'] != '') {
                $contactObj = $this->_em->getReference('CommonUtilityBundle:FgCmContact', $valArr['contact_id']);
                $docAssignObj->setContact($contactObj);
            }
            if ($valArr['role_id'] != '') {
                $roleObj = $this->_em->getReference('CommonUtilityBundle:FgRmRole', $valArr['role_id']);
                $docAssignObj->setRole($roleObj);
            }
            $this->_em->persist($docAssignObj);
        }
    }

    /**
     * Function to save document team function details
     * 
     * @param array $documentFunctionsArr Document team function details
     * 
     */
    protected function saveDocumentFunctionDetails($documentFunctionsArr)
    {
        foreach ($documentFunctionsArr as $key => $valArr) {
            $docObj = $this->_em->getReference('CommonUtilityBundle:FgDmDocuments', $valArr['document_id']);
            $functionObj = $this->_em->getReference('CommonUtilityBundle:FgRmFunction', $valArr['function_id']);
            $docFunctionObj = new \Common\UtilityBundle\Entity\FgDmTeamFunctions();
            $docFunctionObj->setDocument($docObj);
            $docFunctionObj->setFunction($functionObj);
            $this->_em->persist($docFunctionObj);
        }
    }

    /**
     * Function to delete old multiple records from a table
     * 
     * @param string $table   Table Name to delete from
     * @param string $colName Table column Name to check
     * @param int    $id      Table column Value to check
     */
    protected function deleteOldRecordsFromTable($table = '', $colName = '', $id = '')
    {
        $col = 'd.' . $colName;
        $qb = $this->createQueryBuilder('d');
        $qb->delete('CommonUtilityBundle:' . $table, 'd');
        $qb->where($qb->expr()->eq($col, ':key'));
        $qb->setParameter(':key', $id);
        $q = $qb->getQuery();
        $q->execute();
    }

    /**
     * Function to get the details of a document
     *
     * @param int $documentId Document Id to get the details
     *
     * @return array $result Array of details
     */
    public function getDocumentDetails($documentId)
    {
        $q = $this->createQueryBuilder('d')
            ->select("d.id, IDENTITY(d.club) AS clubId, d.name, d.description, d.author, di18.nameLang, di18.descriptionLang, di18.authorLang, di18.lang, v.file, d.depositedWith, d.visibleFor, d.isVisibleToContact , d.isPublishLink, d.documentType, IDENTITY(d.subcategory) AS subCategoryId, d.filter, IDENTITY(d.currentRevision) AS versionId")
            ->addSelect("(SELECT GROUP_CONCAT(DISTINCT da1.club ORDER BY da1.club ASC) FROM CommonUtilityBundle:FgDmAssigment da1 WHERE da1.document=d.id AND da1.documentType='CLUB') AS clubAssignments")
            ->addSelect("(SELECT GROUP_CONCAT(DISTINCT dae1.club ORDER BY dae1.club ASC) FROM CommonUtilityBundle:FgDmAssigmentExclude dae1 WHERE dae1.document=d.id) AS clubExclude")
            ->addSelect("(SELECT GROUP_CONCAT(da2.role) FROM CommonUtilityBundle:FgDmAssigment da2 WHERE da2.document=d.id AND da2.documentType='TEAM') AS teamAssignments")
            ->addSelect("(SELECT GROUP_CONCAT(da3.role) FROM CommonUtilityBundle:FgDmAssigment da3 WHERE da3.document=d.id AND da3.documentType='WORKGROUP') AS workgroupAssignments")
            ->addSelect("(SELECT GROUP_CONCAT(DISTINCT da4.contact ORDER BY da4.contact ASC) FROM CommonUtilityBundle:FgDmAssigment da4 WHERE da4.document=d.id AND da4.documentType='CONTACT' AND da4.contactAssignType ='MANUAL') AS contactAssignments")
            ->addSelect("(SELECT GROUP_CONCAT(DISTINCT dae.contact ORDER BY dae.contact ASC) FROM CommonUtilityBundle:FgDmAssigmentExclude dae WHERE dae.document=d.id ) AS contactExclude")
            ->addSelect("(SELECT GROUP_CONCAT(da5.contact) FROM CommonUtilityBundle:FgDmAssigment da5 WHERE da5.document=d.id AND da5.documentType='CONTACT' AND da5.contactAssignType ='FILTER') AS filterContacts")
            ->addSelect("(SELECT GROUP_CONCAT(df.function) FROM CommonUtilityBundle:FgDmTeamFunctions df WHERE df.document=d.id) AS teamfunctionAssignments")
            ->leftJoin('CommonUtilityBundle:FgDmDocumentsI18n', 'di18', 'WITH', 'di18.id = d.id')
            ->leftJoin('CommonUtilityBundle:FgDmVersion', 'v', 'WITH', 'v.id = d.currentRevision AND v.document = d.id')
            ->where('d.id=:documentId')
            ->setParameter('documentId', $documentId);
        $docArr = $q->getQuery()->getArrayResult();
        $result = array();
        $id = 0;
        foreach ($docArr as $key => $arr) {
            if (count($arr) > 0) {
                if ($arr['id'] == $id) {
                    $result[$id]['nameLang'][$arr['lang']] = $arr['nameLang'];
                    $result[$id]['descriptionLang'][$arr['lang']] = $arr['descriptionLang'];
                    $result[$id]['authorLang'][$arr['lang']] = $arr['authorLang'];
                } else {
                    $id = $arr['id'];
                    $result[$id] = array('id' => $arr['id'], 'clubId' => $arr['clubId'], 'name' => $arr['name'], 'description' => $arr['description'], 'author' => $arr['author'], 'filename' => $arr['file'], 'depositedWith' => $arr['depositedWith'], 'documentType' => $arr['documentType'], 'subCategoryId' => $arr['subCategoryId'], 'filterData' => $arr['filter'], 'contactExclude' => $arr['contactExclude'], 'clubExclude' => $arr['clubExclude'],
                        'visibleFor' => $arr['visibleFor'], 'isVisibleToContact' => $arr['isVisibleToContact'],'isPublic'=>$arr['isPublishLink'], 'clubAssignments' => $arr['clubAssignments'], 'teamAssignments' => $arr['teamAssignments'], 'workgroupAssignments' => $arr['workgroupAssignments'], 'contactAssignments' => $arr['contactAssignments'], 'teamfunctionAssignments' => $arr['teamfunctionAssignments'], 'filterContacts' => $arr['filterContacts'], 'versionId' => $arr['versionId']);
                    $result[$id]['nameLang'][$arr['lang']] = $arr['nameLang'];
                    $result[$id]['descriptionLang'][$arr['lang']] = $arr['descriptionLang'];
                    $result[$id]['authorLang'][$arr['lang']] = $arr['authorLang'];
                }
            }
        }

        return $result;
    }

    /**
     * Function to get filter data for document listing.
     *
     * @param object $club            Club object
     * @param int    $clubId          Club id of document 
     * @param object $container       Container object
     * @param string $type            Document type club/team/contact/workgroup
     * @param int    $clubTeamId      Team Category id
     * @param int    $clubWorkgroupId Workgroup Category id
     * @param string $clubDefaultLang Club Default Language
     * @param string $clubType        Club type
     *
     * @return array $resultData Resulting filter data.
     */
    public function getDocumentsFilterData($club, $clubId, $contactId, $container, $type, $clubTeamId, $clubWorkgroupId, $clubDefaultLang, $clubType)
    {
        $docObj = new DocumentDetails($container);
        $documentsData = array(
            'FILE' => $docObj->getFilterDataofFileType($type, $clubTeamId, $clubWorkgroupId, $clubDefaultLang, $clubType, $club),
            'DATE' => $docObj->getFilterDataofDateType(),
            'USER' => $docObj->getFilterDataofUserType()
        );
        $categories = $this->_em->getRepository('CommonUtilityBundle:FgDmDocumentCategory')->getAllCategoryDetails($club, $clubId, $type, $container);
        $resultData = (count($categories) > 0) ? array_merge($documentsData, $categories) : $documentsData;
     
        $bookmarkDetails = $this->_em->getRepository('CommonUtilityBundle:FgDmBookmarks')->getBookmarksOfDocument($clubId, $contactId, $type, $club);
        $resultData['bookmark'] = array('show_filter' => '0', 'id' => 'bookmark', 'entry' => $bookmarkDetails);

        return $resultData;
    }

    /**
     * Function to get doc for specific version /lang
     * 
     * @param int    $docId           Document id
     * @param int    $versionId       Document version id
     * @param string $clubDefaultLang Club default language
     * 
     * @return array
     */
    public function getVersionDoc($docId, $versionId, $clubDefaultLang)
    {
        $qb = $this->createQueryBuilder('d')
            ->select('dv.file, d.name,d.isPublishLink as isPublic, (CASE WHEN (di18.nameLang IS NULL) THEN d.name ELSE di18.nameLang END) as name,'
                . ' d.name, IDENTITY(d.club) as club')
            ->innerJoin('CommonUtilityBundle:FgDmVersion', 'dv', 'WITH', 'dv.document=d.id')
            ->leftJoin('CommonUtilityBundle:FgDmDocumentsI18n', 'di18', 'WITH', 'di18.id = d.id AND di18.lang =:langDef')
            ->where('d.id=:docId')
            ->andWhere('dv.id=:verId')
            ->setParameters(array(':docId' => $docId, ':verId' => $versionId, ':langDef' => $clubDefaultLang));
        $result = $qb->getQuery()->getArrayResult();

        return $result;
    }

    /**
     * Function to delete documents
     *
     * @param array  $docIds Document id
     * @param string $type   Delete document/version
     *
     * @return boolean
     */
    public function deleteDocuments($docIds, $type, $clubId)
    {
        if ($type == 'document') {
            $docIdseperated = implode(",", $docIds);
            $oldDepositedWithSelectionIds = $this->_em->getRepository('CommonUtilityBundle:FgDmAssigment')->getPreviousAssignments($docIdseperated, 'contact');
            if ($oldDepositedWithSelectionIds) {
                $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->updateLastUpdated($oldDepositedWithSelectionIds, 'id');
            }
            $oldDepositedArr = ($oldDepositedWithSelectionIds) ? explode(',', $oldDepositedWithSelectionIds) : array();
            foreach ($docIds as $id) {
                $versionIds = $this->getVersionIdsFromDocid($id);
                foreach ($versionIds as $version) {
                    $this->unlinkDocuments($version, $clubId);
                }

                $document = $this->find($id);
                $this->_em->remove($document);
            }
        } elseif ($type == 'version') {
            foreach ($docIds as $id) {
                $this->unlinkDocuments($id, $clubId);
                $this->deleteOldRecordsFromTable('FgDmVersion', 'id', $id);
            }
        }
        $this->_em->flush();

        return true;
    }

    /**
     * Function to save contact document assignments
     *
     * @param Object $container             Container Object
     * @param Object $documentObj           Document Object
     * @param array  $contactArr            Array of contactIds
     * @param string $filterData            Filter data
     * @param array  $excludeContactsArr    Contact ids to exclude
     * @param array  $oldDepositedArr       Deposited with contacts old value
     * @param array  $newDepositedArr       New list of contact to Depositedwith
     * @param array  $oldFilterDepositedArr Filter deposited contacts old value array 
     */
    public function saveDocumentAssignmentDetailsOfContact($container, $documentObj, $contactArr = array(), $filterData = '', $excludeContactsArr = array(), $oldDepositedArr = array(), $newDepositedArr = array(), $oldFilterDepositedArr = array())
    {
        $documentId = $documentObj->getId();
        $excludeContactForLastUpdate = array();
        $this->deleteOldRecordsFromTable('FgDmAssigment', 'document', $documentId);
        $oldExcludeContactsStr = $this->_em->getRepository('CommonUtilityBundle:FgDmAssigment')->getPreviousExcludedClubsOrContacts($documentId, 'contact');
        $oldExcludeContactsArr = explode(',', $oldExcludeContactsStr);
        $excludeContactForUpdate = array_merge($excludeContactsArr, $oldExcludeContactsArr);
        foreach ($excludeContactForUpdate as $excludeVal) {
            if (in_array($excludeVal, $oldFilterDepositedArr)) {
                $excludeContactForLastUpdate[] = $excludeVal;
            }
        }
        $this->deleteOldRecordsFromTable('FgDmAssigmentExclude', 'document', $documentId);
        if (count($contactArr) > 0) {
            foreach ($contactArr as $key => $contact) {
                if (($contact != '') && (!in_array($contact, $excludeContactsArr))) {
                    $docAssignObj = new \Common\UtilityBundle\Entity\FgDmAssigment();
                    $docAssignObj->setDocument($documentObj);
                    $docAssignObj->setDocumentType('CONTACT');
                    $contactObj = $this->_em->getReference('CommonUtilityBundle:FgCmContact', $contact);
                    $docAssignObj->setContact($contactObj);
                    $docAssignObj->setContactAssignType('MANUAL');
                    $this->_em->persist($docAssignObj);
                }
            }
        }
        //update contact last updated field for only unique fields
        $array1 = array_diff($oldDepositedArr, $newDepositedArr);
        $array2 = array_diff($newDepositedArr, $oldDepositedArr);
        $contactForLastUpdate = array_merge($array2, $array1);
        if (count($contactForLastUpdate) > 0) {
            $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->updateLastUpdated($contactForLastUpdate, 'id');
        }
        if (count($excludeContactsArr) > 0) {
            foreach ($excludeContactsArr as $key => $contact) {
                if ($contact != '') {
                    $docExcludeObj = new \Common\UtilityBundle\Entity\FgDmAssigmentExclude();
                    $docExcludeObj->setDocument($documentObj);
                    $contactObj = $this->_em->getReference('CommonUtilityBundle:FgCmContact', $contact);
                    $docExcludeObj->setContact($contactObj);
                    $this->_em->persist($docExcludeObj);
                }
            }
        }
        //insert last updated for excluded contacts
        if (count($excludeContactForLastUpdate) > 0) {
            $exContactIdsString = implode(',', $excludeContactForLastUpdate);
            $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->updateLastUpdated($exContactIdsString, 'id');
        }
        $this->_em->flush();
        if ($filterData != '') {
            $docPdo = new DocumentPdo($container);
            $docPdo->updateDocumentFilterContacts($documentId, $filterData, $oldFilterDepositedArr);
        } elseif (count($oldFilterDepositedArr) > 0) {
            $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->updateLastUpdated($oldFilterDepositedArr, 'id');
        }
    }

    /**
     * Function to unlink documents
     *
     * @param int $versionId Document_versionId
     * @param int $clubId    Document club Id
     *
     * @return boolean
     */
    public function unlinkDocuments($versionId, $clubId)
    {
        $versionobj = $this->_em->getReference('CommonUtilityBundle:FgDmVersion', $versionId);
        $filename = $versionobj->getFile();
        $uploadPath = 'uploads/' . $clubId . '/documents/';
        unlink($uploadPath . $filename);
    }

    /**
     * Function to get the versionids
     *
     * @param int $docId Document Id
     *
     * @return array
     */
    public function getVersionIdsFromDocid($docId)
    {
        $qb = $this->createQueryBuilder('d')
            ->select('dv.id')
            ->innerJoin('CommonUtilityBundle:FgDmVersion', 'dv', 'WITH', 'dv.document=d.id')
            ->where('d.id=:docId')
            ->setParameters(array(':docId' => $docId));
        $result = $qb->getQuery()->getArrayResult();

        return $result;
    }

    /**
     * Function to get category delete count for count update
     *
     * @param array $docIds Document Ids
     * @param int   $clubId club id
     *
     * @return array
     */
    public function getsubCategoryDeleteCount($docIds, $clubId)
    {
        $ids = implode(",", $docIds);

        $qb = $this->createQueryBuilder('d')
            ->select('c.id as categoryId,sc.id as subCatId, count(d.id) as sidebarCount,d.documentType,cb.id as catClubId')
            ->leftJoin('CommonUtilityBundle:FgDmDocumentSubcategory', 'sc', 'WITH', 'sc.id=d.subcategory')
            ->leftJoin('CommonUtilityBundle:FgDmDocumentCategory', 'c', 'WITH', 'c.id=d.category')
            ->leftJoin('CommonUtilityBundle:FgClub', 'cb', 'WITH', 'cb.id=c.club')
            ->andWhere('d.id IN (' . $ids . ')')
            ->groupBy('d.subcategory');

        $result = $qb->getQuery()->getArrayResult();

        return $result;
    }

    /**
     * Function to delete contact document filtercontacts
     *
     * @param int    $documentId    Document id
     * @param string $assignmntType Assignment Type Default FILTER
     */
    public function deleteDocumentFilterContacts($documentId, $assignmntType = 'FILTER')
    {
        $qb = $this->createQueryBuilder('d');
        $qb->delete('CommonUtilityBundle:FgDmAssigment', 'd');
        $qb->where($qb->expr()->eq('d.document', ':key'));
        $qb->andWhere($qb->expr()->eq('d.documentType', ':assignmntType'));
        $qb->setParameters(array('key' => $documentId, 'assignmntType' => $assignmntType));
        $q = $qb->getQuery();
        $q->execute();

        return;
    }

    /**
     * Function to get cpount of assigned club documents used to get count in tabs  
     * 
     * @param string $type         Document type club/team/contact/workgroup
     * @param int    $clubId       Current club id
     * @param int    $assignedTo   Assigned to club id
     * @param object $containerObj Container object
     * 
     * @return int
     */
    public function getCountOfAssignedClubDocuments($type, $clubId, $assignedTo, $containerObj)
    {
        if ($type === 'CLUB') {
            $clubHeirarchy = array($clubId);
            $documentlistClass = new Documentlist($containerObj, "CLUB");
            $documentlistClass->clubId = $assignedTo;
            $documentlistClass->clubHeirarchy = $clubHeirarchy;
            $documentlistClass->clubtype = 'sub_federation_club';
            $documentlistClass->onlyHeirarchy = true;
            $documentlistClass->setColumns(array('docname', 'fdd.id as documentId'));
            $documentlistClass->setCount();
            $documentlistClass->setFrom();
            $documentlistClass->setCondition();
            $documentlistClass->addCondition("fdd.deposited_with != 'NONE'");
            $documentlistClass->addJoin(" LEFT JOIN fg_dm_assigment AS fda ON fdd.id = fda.document_id AND fda.club_id = $assignedTo AND fda.document_type = 'CLUB' ");
            $documentlistClass->addJoin(" INNER JOIN fg_dm_document_subcategory as sbcat ON  sbcat.id = fdd.subcategory_id ");
            $documentlistClass->addJoin(" INNER JOIN fg_dm_document_category as cat ON  cat.id = sbcat.category_id ");
            $totallistquery = $documentlistClass->getResult();
            $conn = $this->getEntityManager()->getConnection();
            $result = $conn->executeQuery($totallistquery)->fetchAll();

            return $result[0]['count'];
        }
    }

    /**
     * Function to filter assigned contact documents.
     *
     * @return array $dataResult Result array documents.
     */
    public function getContactFilterDocuments()
    {
        $docQuery = $this->createQueryBuilder('d')
            ->select('d.id, d.filter, IDENTITY(d.club) AS clubId')
            ->where('d.filter IS NOT NULL')
            ->andWhere("d.filter != ''")
            ->orderBy('d.club', ' ASC');
        $dataResult = $docQuery->getQuery()->getResult();

        return $dataResult;
    }

    /**
     * Function to delete old versions of a documents
     *
     * @param int $documentId Document Id
     * @param int $clubId     Current club Id
     */
    public function deleteOldVersionsOfADocument($documentId, $clubId)
    {
        $qb = $this->createQueryBuilder('d')
            ->select('v.id')
            ->innerJoin('CommonUtilityBundle:FgDmVersion', 'v', 'WITH', 'v.document=d.id AND v.id != d.currentRevision')
            ->where('d.id = :documentId')
            ->setParameters(array('documentId' => $documentId));
        $versionIds = $qb->getQuery()->getArrayResult();
        if (count($versionIds) > 0) {
            foreach ($versionIds as $versionId) {
                $this->unlinkDocuments($versionId['id'], $clubId);
                $versionObj = $this->_em->getRepository('CommonUtilityBundle:FgDmVersion')->find($versionId['id']);
                $this->_em->remove($versionObj);
            }
            $this->_em->flush();
        }
    }

    /**
     * Method to change the document 'DEPOSITED WITH' from 'NONE' to 'SELECTED' and reverse
     * Call when a 'NONE' document is assigned to a club
     * Call when document is removed from assignment
     * 
     * @param int    $documentId FgDmDocuments id
     * @param string $changeTo   'NONE'/'SELECTED'
     */
    public function changeDocumentDepositedWith($documentId, $changeTo)
    {
        $objDoc = $this->_em->getRepository('CommonUtilityBundle:FgDmDocuments')->find($documentId);
        if ($objDoc) {
            if ($changeTo == 'SELECTED') {
                $objDoc->setDepositedWith('SELECTED');
            }
            if ($changeTo == 'NONE') {
                $objDoc->setDepositedWith('NONE');
            }
            $this->_em->flush();
        }
    }

    /**
     * Method to get document 'DEPOSITED WITH' and count of assignments of a particular document
     *
     * @param int $documentId  Document Id
     *
     * return array Array of deposited with and assignment count details
     */
    public function getDocumentDepositedWithDetails($documentId)
    {
        $resultQuery = $this->createQueryBuilder('D')
            ->select('D.depositedWith')
            ->addSelect('count(A.id)')
            ->leftJoin("CommonUtilityBundle:FgDmAssigment", "A", "WITH", "A.document = D.id AND D.documentType = 'CLUB' AND A.documentType = 'CLUB' ")
            ->where('D.id = :documentId')
            ->andWhere("D.documentType = 'CLUB' ");
        $resultQuery->setParameter('documentId', $documentId);
        $results = $resultQuery->getQuery()->getArrayResult();

        return $results;
    }

    /**
     * Method to get count of documents in a subcategory
     *
     * @param int    $subcategoryId      subcategory Id
     * @param string $documentType       CONTACT/CLUB/TEAM/WORKGROUP
     * @param object $container          Container object
     * @param int    $currentRoleId      current team/workgroup Id
     * @param array  $adminstrativeRoles workgroups/teams which the contact have administrative roles
     * @param array  $memberRoles        workgroups/teams which the contact have member roles
     *
     * @return int
     */
    public function getSubcategoryDocumentsCount($subcategoryId, $documentType, $container, $currentRoleId = "", $adminstrativeRoles = array(), $memberRoles = array())
    {
        $contactId = $container->get('contact')->get("id");
        $clubId = $container->get('club')->get("id");
        $isFedMember = $container->get('contact')->get('isFedMember');
        //For federation members only, documents categories from hierarchy level is needed
        $clubHeirarchy = ($isFedMember == 1) ? $container->get('club')->get('clubHeirarchy') : array();

        $clubHeirarchy[] = $clubId;
        $teamCategory = $container->get('club')->get('club_team_id');

        switch ($documentType) {
            case "CONTACT":
                $results = $this->getContactDocsCount($contactId, $subcategoryId);
                break;
            case "CLUB":
                $results = $this->getClubDocsCount($clubId, $subcategoryId, $clubHeirarchy);
                break;
            case "TEAM":
                if (count($adminstrativeRoles) == 0 && count($memberRoles) == 0) {
                    break;
                }
                $results = $this->getTeamDocsCount($contactId, $subcategoryId, $adminstrativeRoles, $memberRoles, $teamCategory, $currentRoleId);
                break;

            case "WORKGROUP":
                if (count($adminstrativeRoles) == 0 && count($memberRoles) == 0) {
                    break;
                }
                $results = $this->geWorkgroupDocsCount($subcategoryId, $adminstrativeRoles, $memberRoles, $currentRoleId);
                break;
        }

        return $results[0]['docCount'];
    }

    /**
     * Method to get document 'DEPOSITED WITH' and count of assignments of a particular document
     *
     * @param int $documentId  Document Id
     *
     * return array Array of deposited with and assignment count details
     */
    public function saveDocumentFrontend($documentDetails, $documentId, $currentUserId, $clubDetails, $container)
    {
        //  Need to get the category id
        $documentDetails['categoryId'] = $this->_em->getRepository('CommonUtilityBundle:FgDmDocumentSubcategory')->getCategoryIdfromSubcatid($documentDetails['subCategoryId']);
        $documentDetails['created_at'] = new \DateTime("now");
        $documentDetails['updated_at'] = new \DateTime("now");
        $currentContactObj = $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->find($currentUserId);
        $documentDetails['created_by'] = $currentContactObj;
        $documentDetails['updated_by'] = $currentContactObj;
        $clubLanguages = $clubDetails->get('club_languages');
        $clubDefaultLang = $clubDetails->get('club_default_lang');
        $docDetail = new DocumentDetails($container);
        //  Insert into documents
        if ($documentId) {
            $isNewObject = false;
            $docObj = $this->_em->getRepository('CommonUtilityBundle:FgDmDocuments')->find($documentId);
            $oldDepositedWith = $docObj->getDepositedWith();
            $oldVisibleFor = $docObj->getVisibleFor();
        } else {
            $isNewObject = true;
            $docObj = new \Common\UtilityBundle\Entity\FgDmDocuments();
        }

        //buildDocumentDataArray
        $documentData = array('docType' => $documentDetails['docType'], 'categoryId' => $documentDetails['categoryId'], 'subCategoryId' => $documentDetails['subCategoryId'], 'name' => ($isNewObject ? $documentDetails['name'] : $documentDetails['name'][$clubDefaultLang]), 'description' => ($isNewObject ? $documentDetails['description'] : $documentDetails['description'][$clubDefaultLang]),'isPublic'=> $documentDetails['isPublic'], 'author' => ($isNewObject ? $documentDetails['author'] : $documentDetails['author'][$clubDefaultLang]));
        //insert document data log entries
        $docDetail->populateDocumentDataLogEntries($docObj, $isNewObject, $documentData, 0);

        if ($documentId) {
            $this->updateRoleDocument($docObj, $documentDetails, $clubDefaultLang);
            $documentDetails['document_id'] = $documentId;
        } else {
            $documentDetails['document_id'] = $this->saveDocumentData($docObj, $documentDetails, $isNewObject);
            $docObj = $this->_em->getRepository('CommonUtilityBundle:FgDmDocuments')->find($documentDetails['document_id']);
        }
        
        //  Insert into version
        if (isset($documentDetails['filename'])) {
            $versionObj = $this->saveDocumentVersionDetails($documentDetails);
            //  Update the current version
            $docObj->setCurrentRevision($versionObj);
            $this->_em->persist($docObj);
            $this->_em->flush();
            // Insert entry in contact sighted table to make document seen for uploaded contact
            $this->_em->getRepository('CommonUtilityBundle:FgDmContactSighted')->documentSighted($currentUserId, array($documentDetails['document_id']));
        } else {
            //set last updated in case of any chnages
            $currentVersionId = $docObj->getCurrentRevision()->getId();
            $currentVersionObj = $this->_em->getRepository('CommonUtilityBundle:FgDmVersion')->find($currentVersionId);
            $currentVersionObj->setUpdatedAt(new \DateTime("now"));
            $currentVersionObj->setUpdatedBy($currentContactObj);
            $this->_em->persist($currentVersionObj);
            $this->_em->flush();
        }
        //  Insert into documents i18n
        if ($isNewObject) {
            $documentI18nArr[] = array(
                'id' => $documentDetails['document_id'], 'lang' => $clubDefaultLang, 'name' => $documentDetails['name'],
                'description' => $documentDetails['description'], 'author' => $documentDetails['author']);
        } else {
            foreach ($clubLanguages as $lang) {
                $documentI18nArr[] = array(
                    'id' => $documentDetails['document_id'], 'lang' => $lang,
                    'name' => ((isset($documentDetails['name'][$lang])) ? $documentDetails['name'][$lang] : ''),
                    'description' => ((isset($documentDetails['description'][$lang])) ? $documentDetails['description'][$lang] : ''),
                    'author' => ((isset($documentDetails['author'][$lang])) ? $documentDetails['author'][$lang] : '')
                );
            }
        }
        $documentObj = new DocumentPdo($container);
        $documentObj->saveDocumentI18nDetails($documentI18nArr);
        if (!$isNewObject) {
            $documentObj->updateMainTable($clubDefaultLang, $documentId);
        }

        $oldDepositedWithSelectionIds = ($isNewObject == 1) ? '' : $this->_em->getRepository('CommonUtilityBundle:FgDmAssigment')->getPreviousAssignments($documentDetails['document_id'], $documentDetails['docType']);
        $newDepositedWithIdsArr = array();
        if ($documentDetails['depositedWith'] === 'SELECTED') {
            if ($isNewObject) {
                //  Insert into assignments (deposited with)
                $documentAssignmentsArr = array();
                foreach ($documentDetails['depositedWithSelection'] as $entity) {
                    $documentAssignmentsArr[] = array('document_id' => $documentDetails['document_id'], 'document_type' => $documentDetails['docType'], 'club_id' => '', 'role_id' => $entity);
                }
                if (count($documentAssignmentsArr) > 0) {
                    $this->saveDocumentAssignmentDetails($documentAssignmentsArr);
                }
            } else {
                if (count($documentDetails['depositedWithOptions']) > 1) {
                    foreach ($documentDetails['depositedWithOptions'] as $roleId) {
                        $assignmentObj = $this->_em->getRepository('CommonUtilityBundle:FgDmAssigment')->findOneBy(array('document' => $documentId, 'role' => $roleId));
                        if ($assignmentObj) {
                            $this->_em->remove($assignmentObj);
                        }
                    }
                    //  Insert into assignments (deposited with)
                    $documentAssignmentsArr = array();
                    foreach ($documentDetails['depositedWithSelection'] as $roleId) {
                        $documentAssignmentsArr[] = array('document_id' => $documentDetails['document_id'], 'document_type' => $documentDetails['docType'], 'role_id' => $roleId);
                    }
                    if (count($documentAssignmentsArr) > 0) {
                        $this->saveDocumentAssignmentDetails($documentAssignmentsArr);
                    }
                    $this->_em->flush();

                    $depositedWithRoles = $this->_em->getRepository('CommonUtilityBundle:FgDmAssigment')->findBy(array('document' => $documentId, 'documentType' => $documentDetails['docType']));
                    foreach ($depositedWithRoles as $key => $val) {
                        $newDepositedWithIdsArr[] = $val->getRole()->getId();
                    }
                    if (count($depositedWithRoles) == 0) {
                        $docObj->setDepositedWith('NONE');
                        $this->_em->flush();
                    }
                }
            }
        }

        if ((count($documentDetails['depositedWithOptions']) > 1) || $isNewObject) {
            $depositedWith = $docObj->getDepositedWith();
            $depositedWithSelectionIds = ($isNewObject) ? implode(',', $documentDetails['depositedWithSelection']) : implode(',', $newDepositedWithIdsArr);
            $docDetail->populateDocumentAssignmentLogEntries('deposited_with', (($isNewObject == 1) ? '' : $oldDepositedWith), $depositedWith, $oldDepositedWithSelectionIds, $depositedWithSelectionIds);
        }

        $oldVisibleForFunctionIds = ($isNewObject == 1) ? '' : $this->_em->getRepository('CommonUtilityBundle:FgDmAssigment')->getPreviousAssignedFunctions($documentId);
        if (!$isNewObject) {
            $this->deleteOldRecordsFromTable('FgDmTeamFunctions', 'document', $documentId);
        }
        //  Insert into team functions
        $documentFunctionArray = array();
        if ($documentDetails['visibleFor'] === 'team_functions') {
            $teamFunctionsArr = ($isNewObject == 1) ? $documentDetails['functions'] : $documentDetails['visibleForSelection'];
            foreach ($teamFunctionsArr as $function) {
                $documentFunctionArray[] = array('document_id' => $documentDetails['document_id'], 'function_id' => $function);
            }
            if (count($documentFunctionArray) > 0) {
                $this->saveDocumentFunctionDetails($documentFunctionArray);
            }
        }
        $this->_em->flush();

        $visibleForFunctionIds = ($documentDetails['docType'] == 'TEAM') ? implode(',', $teamFunctionsArr) : '';
        $docDetail->populateDocumentAssignmentLogEntries('visible_for', (($isNewObject == 1) ? '' : $oldVisibleFor), $documentDetails['visibleFor'], $oldVisibleForFunctionIds, $visibleForFunctionIds);
        //  Insert into document log
        $docDetail->insertDocumentLogEntries($documentDetails['docType'], $documentDetails['document_id'], $currentUserId);

        return $documentDetails['document_id'];
    }

    /**
     * Function to get column data for document listing in internal area.
     *
     * @param object $container Container object
     * @param string $type      Document type club/team/contact/workgroup
     *
     * @return array $resultData Resulting column data.
     */
    public function getInternalDocumentsFilterData($container, $type)
    {
        $docObj = new DocumentDetails($container);
        $documentsData = array(
            'FILE' => $docObj->getDocColumnsForFileType($type),
            'DATE' => $docObj->getFilterDataofDateType(),
            'USER' => $docObj->getFilterDataofUserType()
        );

        return $documentsData;
    }

    /**
     * Method to get count of contact documents when subcategory-id is given
     *
     * @param int $contactId     contact Id
     * @param int $subcategoryId subcategory Id
     *
     * @return array
     */
    private function getContactDocsCount($contactId, $subcategoryId)
    {
        $docCountQry = $this->createQueryBuilder('D')
            ->select("COUNT(DISTINCT D.id) as docCount")
            ->leftJoin("CommonUtilityBundle:FgDmAssigment", "ASS", "WITH", "(ASS.documentType = :documentType AND ASS.document = D.id AND ASS.contact = :contactId)")
            ->leftJoin("CommonUtilityBundle:FgDmAssigmentExclude", "EX", "WITH", " (EX.document = D.id AND EX.contact = :contactId)")
            ->where("D.id IS NOT NULL AND D.isVisibleToContact = 1 AND ASS.id IS NOT NULL AND EX.id IS NULL AND D.subcategory = :subcategoryId");
        $parametersArray = array("documentType" => 'CONTACT', "contactId" => $contactId, "subcategoryId" => $subcategoryId);
        $docCountQry->setParameters($parametersArray);
        $results = $docCountQry->getQuery()->getArrayResult();

        return $results;
    }

    /**
     * Method to get count of club documents when subcategory-id is given
     *
     * @param int   $clubId        club Id
     * @param int   $subcategoryId subcategory Id
     * @param array $clubHeirarchy clubHeirarchy array
     *
     * @return array
     */
    private function getClubDocsCount($clubId, $subcategoryId, $clubHeirarchy)
    {
        $docCountQry = $this->createQueryBuilder('D')
            ->select("COUNT(DISTINCT D.id) as docCount")
            ->leftJoin("CommonUtilityBundle:FgDmAssigment", "ASS", "WITH", "(ASS.documentType = :documentType AND ASS.document = D.id AND ASS.club = :clubId)")
            ->leftJoin("CommonUtilityBundle:FgDmAssigmentExclude", "EX", "WITH", " (EX.document = D.id AND EX.club = :clubId)")
            ->where("D.isVisibleToContact = 1 AND D.id IS NOT NULL AND D.subcategory = :subcategoryId "
            . "AND ((D.depositedWith = 'SELECTED' AND ASS.id IS NOT NULL) OR (D.depositedWith = 'ALL' AND EX.id IS NULL) OR (D.depositedWith = 'NONE' AND D.club = :clubId)) "
            . "AND  D.club IN (:clubHeirarchy) ");
        $parametersArray = array("documentType" => 'CLUB', "clubId" => $clubId, "subcategoryId" => $subcategoryId, 'clubHeirarchy' => $clubHeirarchy);
        $docCountQry->setParameters($parametersArray);
        $results = $docCountQry->getQuery()->getArrayResult();

        return $results;
    }

    /**
     * Method to get count of team documents when subcategory-id is given
     *
     * @param int   $contactId          contact Id
     * @param int   $subcategoryId      subcategory Id
     * @param array $adminstrativeTeams Teams which the contact has administrative roles
     * @param array $memberTeams        Teams which the contact has member roles
     * @param int   $teamCategory       Team category
     * @param int   $currentRoleId      current Team Id
     *
     * @return array
     */
    private function getTeamDocsCount($contactId, $subcategoryId, $adminstrativeTeams, $memberTeams, $teamCategory, $currentRoleId = "")
    {
        $parametersArray = array();
        if (( count($adminstrativeTeams) > 0 && $currentRoleId == "") || (in_array($currentRoleId, $adminstrativeTeams))) { //in case team_functions and team admin
            $functionDQL = "";

            $assignmentFunctionDQL = "SELECT dm_doc2.id FROM CommonUtilityBundle:FgDmDocuments dm_doc2 JOIN CommonUtilityBundle:FgDmAssigment doc_ass WITH (doc_ass.document = dm_doc2.id AND doc_ass.documentType = :documentType AND doc_ass.role IN (:roles) )";
            $roles = ($currentRoleId) ? array($currentRoleId) : $adminstrativeTeams;
        } else { //in case team_functions and team member
            $functionDQL = " AND D.id IN  (SELECT dm_doc.id FROM CommonUtilityBundle:FgDmDocuments dm_doc JOIN CommonUtilityBundle:FgDmTeamFunctions tm_fn WITH  tm_fn.document = dm_doc.id JOIN CommonUtilityBundle:FgRmCategoryRoleFunction cat_role_fun WITH (cat_role_fun.function = tm_fn.function AND cat_role_fun.category = :teamCategory) JOIN CommonUtilityBundle:FgRmRoleContact role_cont WITH (cat_role_fun.id = role_cont.fgRmCrf AND role_cont.contact = :contactId ) )";

            $functionDQL2 = "SELECT dm_doc2.id FROM CommonUtilityBundle:FgDmDocuments dm_doc2 JOIN CommonUtilityBundle:FgDmTeamFunctions tm_fn2 WITH  tm_fn2.document = dm_doc2.id JOIN CommonUtilityBundle:FgRmCategoryRoleFunction cat_role_fun2 WITH (cat_role_fun2.function = tm_fn2.function AND cat_role_fun2.category = :teamCategory) JOIN CommonUtilityBundle:FgRmRoleContact role_cont2 WITH (cat_role_fun2.id = role_cont2.fgRmCrf AND role_cont2.contact = :contactId ) ";
            $assignmentFunctionDQL = $functionDQL2 . " JOIN CommonUtilityBundle:FgDmAssigment doc_ass WITH (doc_ass.document = dm_doc2.id AND doc_ass.documentType = :documentType AND doc_ass.role = cat_role_fun2.role  AND doc_ass.role IN (:roles) )";
            $roles = ($currentRoleId) ? array($currentRoleId) : $memberTeams;
            $parametersArray = array("contactId" => $contactId, "teamCategory" => $teamCategory);
        }
        $roleTeamSql = "doc_ass3.role IN (:rolesTeam) ";
        $roleTeamAdminSql = "doc_ass4.role IN (:rolesTeamAdmin) ";
        // visibleFor = 'team'
        $assignmentFunctionDQL2 = "SELECT dm_doc3.id FROM CommonUtilityBundle:FgDmDocuments dm_doc3 JOIN CommonUtilityBundle:FgDmAssigment doc_ass3 WITH (doc_ass3.document = dm_doc3.id AND doc_ass3.documentType = :documentType AND ( $roleTeamSql ) )";
        // visibleFor = 'team_admin'
        $assignmentFunctionDQL3 = "SELECT dm_doc4.id FROM CommonUtilityBundle:FgDmDocuments dm_doc4 JOIN CommonUtilityBundle:FgDmAssigment doc_ass4 WITH (doc_ass4.document = dm_doc4.id AND doc_ass4.documentType = :documentType AND ( $roleTeamAdminSql ) )";
        $groups = ((count($adminstrativeTeams) > 0 && $currentRoleId == "") || (in_array($currentRoleId, $adminstrativeTeams)) ) ? "'team','team_admin'" : "'team'";
        $docCountQry = $this->createQueryBuilder('D')
            ->select("COUNT(DISTINCT D.id) as docCount")
            ->where("D.id IS NOT NULL AND D.subcategory = :subcategoryId AND D.documentType = :documentType")
            ->andWhere(" ( D.depositedWith='ALL' AND D.visibleFor IN ($groups)  ) OR "
            . " ( D.depositedWith='ALL' AND D.visibleFor ='team_functions'   $functionDQL  ) OR "
            . " ( D.depositedWith='SELECTED' AND ( "
            . " D.visibleFor = 'team_functions' AND D.id IN  ( $assignmentFunctionDQL ) OR "
            . " D.visibleFor = 'team'  AND D.id IN  ( $assignmentFunctionDQL2 ) OR "
            . " D.visibleFor = 'team_admin' AND D.id IN  ( $assignmentFunctionDQL3 ) "
            . " )  )  "
        );

        $parametersArray = array_merge($parametersArray, array("documentType" => 'TEAM', "subcategoryId" => $subcategoryId, "rolesTeam" => ($currentRoleId) ? array($currentRoleId) : array_merge($adminstrativeTeams, $memberTeams), "roles" => $roles));
        //if !($currentRoleId) then $adminstrativeTeams else ( if ($currentRoleId in $adminstrativeTeams) then $currentRoleId else empty array )
        $parametersArray['rolesTeamAdmin'] = (!$currentRoleId) ? ( ($adminstrativeTeams) ? $adminstrativeTeams : array(0) ) : (in_array($currentRoleId, $adminstrativeTeams) ? array($currentRoleId) : array(0));
        $docCountQry->setParameters($parametersArray);
        $results = $docCountQry->getQuery()->getArrayResult();

        return $results;
    }

    /**
     * Method to get count of workgroup documents when subcategory-id is given
     *
     * @param int $subcategoryId             subcategory Id
     * @param array $adminstrativeWorkgroups Workgroups which the contact has administrative roles
     * @param array $memberWorkgroups        Workgroups which the contact has member roles
     * @param int $currentRoleId             current Workgroup Id
     *
     * @return array
     */
    private function geWorkgroupDocsCount($subcategoryId, $adminstrativeWorkgroups, $memberWorkgroups, $currentRoleId = "")
    {
        $groups = ((count($adminstrativeWorkgroups) > 0 && $currentRoleId == "") || (in_array($currentRoleId, $adminstrativeWorkgroups)) ) ? "'workgroup','workgroup_admin'" : "'workgroup'";
        $roleWorkgroupSql = "doc_ass3.role IN (:rolesWorkgroup) ";
        $roleWorkgroupAdminSql = "doc_ass4.role IN (:rolesWorkgroupAdmin) ";
        $assignmentFunctionDQL2 = "SELECT dm_doc3.id FROM CommonUtilityBundle:FgDmDocuments dm_doc3 JOIN CommonUtilityBundle:FgDmAssigment doc_ass3 WITH (doc_ass3.document = dm_doc3.id AND doc_ass3.documentType = :documentType AND ( $roleWorkgroupSql ) )";
        $assignmentFunctionDQL3 = "SELECT dm_doc4.id FROM CommonUtilityBundle:FgDmDocuments dm_doc4 JOIN CommonUtilityBundle:FgDmAssigment doc_ass4 WITH (doc_ass4.document = dm_doc4.id AND doc_ass4.documentType = :documentType AND ( $roleWorkgroupAdminSql ) )";

        $docCountQry = $this->createQueryBuilder('D')
            ->select("COUNT(DISTINCT D.id) as docCount")
            ->where("D.id IS NOT NULL AND D.subcategory = :subcategoryId AND D.documentType = :documentType")
            ->andWhere(" ( D.depositedWith='ALL' AND D.visibleFor IN ($groups)  ) "
            . " OR ( D.depositedWith='SELECTED' AND ( "
            . " D.visibleFor = 'workgroup' AND D.id IN  ( $assignmentFunctionDQL2 ) OR "
            . " D.visibleFor = 'workgroup_admin'  AND D.id IN  ( $assignmentFunctionDQL3 ) "
            . ") )");

        $parametersArray = array("documentType" => 'WORKGROUP', "subcategoryId" => $subcategoryId, "rolesWorkgroup" => ($currentRoleId) ? array($currentRoleId) : array_merge($adminstrativeWorkgroups, $memberWorkgroups));
        //if $currentRoleId is not set  then $adminstrativeWorkgroups else ( if ($currentRoleId in $adminstrativeWorkgroups) then $currentRoleId else empty array )
        $parametersArray['rolesWorkgroupAdmin'] = (!$currentRoleId) ? ( ($adminstrativeWorkgroups) ? $adminstrativeWorkgroups : array(0) ) : (in_array($currentRoleId, $adminstrativeWorkgroups) ? array($currentRoleId) : array(0));
        $docCountQry->setParameters($parametersArray);
        $results = $docCountQry->getQuery()->getArrayResult();

        return $results;
    }

    /**
     * Function to remove documents from internal area
     *
     * @param int    $roleId    role id
     * @param array  $docsArray document array
     * @param string $type      document type
     * @param int    $clubId    current club Id
     * @param int    $contactId logged contact Id
     *
     * @return int   $counter   total removed documents
     */
    public function removeDocumentsInternal($roleId, $docsArray, $type, $clubId, $contactId, $container)
    {
        $logEntry = array();
        $depositedCount = $counter = 0;
        foreach ($docsArray as $key => $id) {
            $docObj = $this->_em->getRepository('CommonUtilityBundle:FgDmDocuments')->find($id);
            if ($docObj->getDepositedWith() == 'SELECTED') {
                $counter ++;
                $depositedCount = $this->_em->getRepository('CommonUtilityBundle:FgDmAssigment')->getDepositedcountOfDocuments($id, $type);
                $logEntry[$id]['depositedWith'] = $depositedCount;
                $assignmentObj = $this->_em->getRepository('CommonUtilityBundle:FgDmAssigment')->findOneBy(array('documentType' => $type, 'document' => $id, 'role' => $roleId));
                if ($assignmentObj) {
                    $this->_em->remove($assignmentObj);
                    if (count($depositedCount) == 1) {
                        $docObj->setDepositedWith('NONE');
                    }
                }
            }
        }

        $this->_em->flush();
        if (!empty($logEntry)) {
            $finalLogEntryArray = $this->createDocumentRemoveLogEntries($logEntry, $type);
            foreach ($finalLogEntryArray as $key => $value) {
                $docDetail = new DocumentDetails($container);
                $docDetail->setDocumentLogArr(array());
                $docDetail->populateDocumentAssignmentLogEntries('deposited_with', 'SELECTED', $value['depositedWithNew'], $value['depositedWithOldSelectedIds'], $value['depositedWithnewSelectedIds']);
                $docDetail->insertDocumentLogEntries($type, $key, $contactId);
            }
        }

        return $counter;
    }

    /**
     * Function to update team/workgroup document
     *
     * @param object $docObj          Document object
     * @param array  $documentData    Document array
     * @param string $clubDefaultLang Club default language
     */
    private function updateRoleDocument($docObj, $documentData, $clubDefaultLang)
    {
        $categoryObj = $this->_em->getReference('CommonUtilityBundle:FgDmDocumentCategory', $documentData['categoryId']);
        $subCategoryObj = $this->_em->getReference('CommonUtilityBundle:FgDmDocumentSubcategory', $documentData['subCategoryId']);
        $name = str_replace('<script', '<scri&nbsp;pt', $documentData['name'][$clubDefaultLang]);
        $description = str_replace('<script', '<scri&nbsp;pt', $documentData['description'][$clubDefaultLang]);
        $author = str_replace('<script', '<scri&nbsp;pt', $documentData['author'][$clubDefaultLang]);
        $docObj->setCategory($categoryObj);
        $docObj->setSubcategory($subCategoryObj);
        $docObj->setName($name);
        $docObj->setDescription($description);
        $docObj->setAuthor($author);
        $docObj->setVisibleFor($documentData['visibleFor']);
        $docObj->setIsPublishLink($documentData['isPublic']);
        $docObj->setDepositedWith($documentData['depositedWith']);
        $this->_em->persist($docObj);
        $this->_em->flush();
    }

    /**
     * Function to craete log entry data for document remove
     *
     * @param array  $logEntry log entry data array
     * @param string $type     document type club/team/contact/workgroup
     *
     * @return array
     */
    public function createDocumentRemoveLogEntries($logEntry, $type)
    {
        $finalLogEntryArray = array();
        foreach ($logEntry as $key => $value) {
            $docObj = $this->_em->getRepository('CommonUtilityBundle:FgDmDocuments')->find($key);
            $finalLogEntryArray[$key]['depositedWithNew'] = $docObj->getDepositedWith();
            $finalLogEntryArray[$key]['depositedWithOldSelectedIds'] = implode(",", $value['depositedWith']);
            $finalLogEntryArray[$key]['depositedWithnewSelectedIds'] = implode(",", $this->_em->getRepository('CommonUtilityBundle:FgDmAssigment')->getDepositedcountOfDocuments($key, $type));
        }

        return $finalLogEntryArray;
    }

    /**
     * Function to check whether the logged in user has admin(edit, log) permission for this document.
     *
     * @param array  $documentDetails Document details array
     * @param object $container       Container obj
     * @param int    $clubId          Club id
     */
    public function checkAdminPermissionForUser($documentDetails, $container, $clubId)
    {
        $visibleFor = $documentDetails['visibleFor'];
        $depositedWith = $documentDetails['depositedWith'];
        $documentType = (isset($documentDetails['documentType'])) ? strtolower($documentDetails['documentType']) : 'team';
        $depositedRoles = ($depositedWith == 'SELECTED') ? (($documentType == 'team') ? explode(',', $documentDetails['teamAssignments']) : explode(',', $documentDetails['workgroupAssignments'])) : array();
        $docDetails = new DocumentDetails($container);
        $myAdminRoles = $docDetails->getMyDocumentRoles($documentType);
        $isAdmin = 0;
        foreach ($myAdminRoles as $key => $val) {
            $isAdmin = ((in_array($key, $depositedRoles) || $depositedWith == 'ALL') && (in_array($visibleFor, array('team', 'team_admin', 'team_functions', 'workgroup', 'workgroup_admin')))) ? 1 : 0;
            if ($isAdmin == 1) {
                break;
            }
        }
        if (($clubId != $documentDetails['clubId']) || !($documentType == 'team' || $documentType == 'workgroup') || (!$isAdmin)) {
            $permissionObj = new FgPermissions($container);
            $permissionObj->checkUserAccess('', 'no_access', array('message' => 'does not have edit rights for this document in internal area'));
        }
    }

    /**
     * Function to get the details of a document
     *
     * @param int $documentId DocumentId
     *
     * @return array $result Array of details
     */
    public function getDocumentPermissionDetails($documentId)
    {
        $q = $this->createQueryBuilder('d')
            ->select("d.id, IDENTITY(d.club) AS clubId, d.name, d.visibleFor, d.documentType, d.depositedWith")
            ->addSelect("(SELECT GROUP_CONCAT(da2.role) FROM CommonUtilityBundle:FgDmAssigment da2 WHERE da2.document=d.id AND da2.documentType='TEAM') AS teamAssignments")
            ->addSelect("(SELECT GROUP_CONCAT(da3.role) FROM CommonUtilityBundle:FgDmAssigment da3 WHERE da3.document=d.id AND da3.documentType='WORKGROUP') AS workgroupAssignments")
            ->where('d.id=:documentId')
            ->setParameter('documentId', $documentId);
        $result = $q->getQuery()->getArrayResult();

        return (count($result) > 0) ? $result[0] : array();
    }

    /**
     * Function to check whether the logged in user has permission for viewing this document.
     *
     * @param array  $documentDetails Document details array
     * @param object $container       Container obj
     * @param int    $clubId          Club id
     */
    public function checkDocumentAccessForUser($documentDetails, $container, $clubId)
    {
        $contact = $container->get('contact');
        $visibleFor = $documentDetails['visibleFor'];
        $depositedWith = $documentDetails['depositedWith'];
        $documentType = (isset($documentDetails['documentType'])) ? strtolower($documentDetails['documentType']) : 'team';
        $depositedRoles = ($depositedWith == 'SELECTED') ? (($documentType == 'team') ? explode(',', $documentDetails['teamAssignments']) : explode(',', $documentDetails['workgroupAssignments'])) : array();
        $docDetails = new DocumentDetails($container);
        $myAdminRoles = $docDetails->getMyDocumentRoles($contact, $documentType);
        
        $user_right = array('ROLE_DOCUMENT', 'ROLE_CONTACT', 'ROLE_SUPER', 'ROLE_USERS', 'ROLE_READONLY_CONTACT', 'ROLE_READONLY_SPONSOR', 'ROLE_FED_ADMIN','ROLE_DOCUMENT_ADMIN');
        $groupRightsArray = $contact->get('availableUserRights');
        $isAdmin = 0;
        foreach ($myAdminRoles as $key => $val) {
            $isAdmin = ((in_array($key, $depositedRoles) || $depositedWith == 'ALL') && (in_array($visibleFor, array('team', 'team_admin', 'team_functions', 'workgroup', 'workgroup_admin')))) ? 1 : 0;
            if ($isAdmin == 1) {
                
                break;
            }
        }
        
        $groupRights = $contact->get('clubRoleRightsGroupWise');
        $contactId = $container->get('contact')->get('id');
        if(array_intersect($user_right , $groupRightsArray)){
            
            return true;
        }else if ($documentType == 'club' && $documentDetails['isVisibleToContact']==1 ) {
            
            return true;
        }else if( ($documentType == 'contact' && $documentDetails['isVisibleToContact']==1 )||(  in_array($contactId , array($documentDetails['contactAssignments'])) || $depositedWith=='ALL'   || in_array($contactId , array($documentDetails['filterContacts'])) ) ) {
            
            return true;
        }
        else if ($documentType == 'team') {
            $myMemberTeams = (isset($groupRights['MEMBER']['teams'])) ? $groupRights['MEMBER']['teams'] : array();
            foreach ($myMemberTeams as $key => $val) {
                $isMember = ((in_array($val, $depositedRoles) || $depositedWith == 'ALL') && (in_array($visibleFor, array('team', 'team_functions')))) ? 1 : 0;
                if ($isMember == 1) {
                    break;
                }
            }
        } else {
            $myMemberWorkgroups = (isset($groupRights['MEMBER']['workgroups'])) ? $groupRights['MEMBER']['workgroups'] : array();
            foreach ($myMemberWorkgroups as $key => $val) {
                $isMember = ((in_array($val, $depositedRoles) || $depositedWith == 'ALL') && (in_array($visibleFor, array('workgroup')))) ? 1 : 0;
                if ($isMember == 1) {
                    break;
                }
            }
        }
        if (($clubId != $documentDetails['clubId']) || !($documentType == 'team' || $documentType == 'workgroup') || !($isAdmin || $isMember)) {
            $permissionObj = new FgPermissions($container);
            $permissionObj->checkUserAccess('', 'no_access', array('message' => 'does not have rights for viewing this document in internal area'));
        }
        return true;
    }

    /**
     * Function to get all the documents for a particular club to show it in file manager
     *
     * @param int    $clubId          Club id of document
     * @param string $clubDefaultLang Club default lang
     * @param string $checkedIds      Checked document ids
     * 
     * @return array $documents documents array
     */
    public function getAllDocumentsForFilemanager($clubId, $clubDefaultLang, $checkedIds = '')
    {

        $doctrineConfig = $this->getEntityManager()->getConfiguration();
        $doctrineConfig->addCustomStringFunction('DATE_FORMAT', 'Common\UtilityBundle\Extensions\DateFormat');
        $doctrineConfig->addCustomStringFunction('contactName', 'Common\UtilityBundle\Extensions\FetchContactName');

        $documents = $this->createQueryBuilder('d')
            ->select("v.file, d.id as documentId,v.id as versionId, sf.isSuperAdmin as isSuperAdmin, DATE_FORMAT(v.updatedAt,'%Y-%m-%d %H:%i') as date,v.size as filesize, contactName(Identity(v.createdBy)) as uploadedBy, d.documentType as docType, Identity(v.createdBy) as authorId, (CASE WHEN (di18.nameLang = '' OR di18.nameLang IS NULL) THEN d.name ELSE di18.nameLang END) as filename, cnt.isDeleted, IDENTITY(d.club) as ClubId ")
            ->innerJoin('CommonUtilityBundle:FgDmVersion', 'v', 'WITH', "v.id = d.currentRevision")
            ->leftJoin('CommonUtilityBundle:FgDmDocumentsI18n', 'di18', 'WITH', "di18.id = d.id AND di18.lang = '$clubDefaultLang'")
            ->leftJoin('CommonUtilityBundle:SfGuardUser', 'sf', 'WITH', 'sf.contact = v.createdBy')
            ->leftJoin('CommonUtilityBundle:FgCmContact', 'cnt', 'WITH', 'cnt.id = v.createdBy')
            ->where('d.club=:clubId');
        if ($checkedIds != '') {
            $documents->andWhere("d.id IN($checkedIds)");
        }
        $documents->setParameter('clubId', $clubId);
        $result = $documents->getQuery()->getArrayResult();
        
        return $result;
    }

    /**
     * This function is used to get the team or workgroup document details
     * 
     * @param object $container Container object
     * @param string $subqry    Subquery condition
     * 
     * @return array $results Document count details
     */
    public function getTeamOrWorkgroupDocumentCountDetails($container, $subqry)
    {
        $qry = 'SELECT COUNT(DISTINCT T.id) AS docCount, T.subCategoryId, T.categoryId, T.roleId, (SUM(T.isUnread)) AS unreadCount FROM (' . $subqry . ') AS T GROUP BY T.roleId, T.subCategoryId';

        $documentPdo = new DocumentPdo($container);
        $results = $documentPdo->executeDocumentsQuery($qry);

        return $results;
    }
}
