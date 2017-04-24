<?php

/**
 * This class is used for handling changes to be confirmed by administrator.
 */

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Entity\FgCmChangeToconfirm;
use Common\UtilityBundle\Entity\FgCmChangeToconfirmFunctions;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Repository\Pdo\ContactPdo;
use Common\UtilityBundle\Util\FgSettings;
use Clubadmin\Util\Contactlist;
use Clubadmin\ContactBundle\Util\ContactDetailsSave;
        

/**
 * FgCmChangeToconfirmRepository
 *
 * This class is used for handling changes to be confirmed by administrator.
 */
class FgCmChangeToconfirmRepository extends EntityRepository
{

    /**
     * Function to getting changes to be confirmed.
     *
     * @param int    $clubId    Club id
     * @param object $container Container object
     * @param object $club      Club object
     * @param int    $contactId Contact id
     *
     * @return array $resultArray Array of changes
     */
    public function getChangesToConfirm($clubId, $container, $club, $contactId)
    {
        // Configuring UDF.
        $doctrineConfig = $this->getEntityManager()->getConfiguration();
        $doctrineConfig->addCustomStringFunction('DATE_FORMAT', 'Common\UtilityBundle\Extensions\DateFormat');
        $doctrineConfig->addCustomStringFunction('contactName', 'Common\UtilityBundle\Extensions\FetchContactName');
        $doctrineConfig->addCustomStringFunction('checkActiveContact', 'Common\UtilityBundle\Extensions\CheckActiveContact');
        $clubDql = $this->getClubName();
        //$datetimeFormat = FgSettings::getMysqlDateTimeFormat();
        $datetimeFormat = '%Y-%m-%d';

        $result = $this->createQueryBuilder('ch')
                ->select("ch.id AS confirmId, DATE_FORMAT( ch.date, '$datetimeFormat' ) AS changeDate, IDENTITY(ch.contact) AS contactId, "
                        . "atr.fieldname AS contactField, atr.inputType AS fieldType, IDENTITY(ch.attribute) AS attributeId, ch.value AS valueAfter, contactName(ch.changedBy) AS changedBy, checkActiveContact(ch.changedBy, :clubId) as activeContact,IDENTITY(ch.changedBy) as changedById ")
                ->addSelect('('.$clubDql->getDQL().') AS clubChangedBy ')
                ->leftJoin("CommonUtilityBundle:FgCmAttribute", "atr", "WITH", "atr.id=ch.attribute")
                ->where('ch.club = :clubId')
                ->andWhere('ch.type = :type')
                ->orderBy('ch.id', 'DESC')
                ->setParameters(array('clubId' => $clubId, 'type' => 'change'))
                ->getQuery()
                ->getResult();
        //echo "<pre>";print_r($result);exit;
        $resultArray = $this->loopContactChanges($result, $container, $club, $clubId, $contactId);

        return $resultArray;
    }

    /**
     * Function to set contact attribute values to changes list.
     *
     * @param array  $changes   Changes array
     * @param object $container Container object
     * @param object $club      Club object
     * @param int    $clubId    Club id
     * @param int    $contactId Contact id
     *
     * @return array $resultArray Resulting changes array
     */
    private function loopContactChanges($changes, $container, $club, $clubId, $contactId)
    {
        $contactDetails = $this->getContactDetails($container, $club, $clubId, $contactId);
        $contactFields = $club->get('allContactFields');
        $correspondanceCategory = $container->getParameterBag()->get('system_category_address');
        $invoiceCategory = $container->getParameterBag()->get('system_category_invoice');
        $resultArray = array();
        foreach ($changes as $resultData) {
            $valueBefore = $contactDetails[$resultData['contactId']][$resultData['attributeId']];
            $attributeCatId = $contactFields[$resultData['attributeId']]['category_id'];
            $appendString = '';
            if ($attributeCatId == $correspondanceCategory) {
                $appendString = ' (Korr.)';
            } else if ($attributeCatId == $invoiceCategory) {
                $appendString = ' (Rg.)';
            }
            $resultArray[] = array('changedById'=>$resultData['changedById'],'activeContact'=>$resultData['activeContact'],'clubChangedBy'=>$resultData['clubChangedBy'],'confirmId' => $resultData['confirmId'], 'changeDate' => $resultData['changeDate'], 'contactName' => $contactDetails[$resultData['contactId']]['contactName'],
                'contactField' => $resultData['contactField'] . $appendString, 'valueBefore' => $valueBefore, 'valueAfter' => $resultData['valueAfter'], 'changedBy' => $resultData['changedBy'], 'attributeId' => $resultData['attributeId'], 'fieldType' => $resultData['fieldType']);
            }

        return $resultArray;
    }

    /**
     * Function to get details of given contacts.
     *
     * @param object $container Container object
     * @param object $club      Club object
     * @param int    $clubId    Club id
     * @param int    $contactId Contact id
     *
     * @return array $contactDetails Contact details
     */
    private function getContactDetails($container, $club, $clubId, $contactId)
    {
        $sWhere = " fg_cm_contact.id IN (SELECT ch.`contact_id` FROM `fg_cm_change_toconfirm` ch WHERE ch.`club_id`=$clubId AND ch.`type`='change')";
        $clubType = $club->get('type');
        if (($clubType == 'federation') || ($clubType == 'standard_club')) {           
            $columns = array('contactNameWithComma', 'mc.*', 'ms.*', 'fg_cm_contact.*');
        } else if (($clubType == 'sub_federation') || ($clubType == 'federation_club') || ($clubType == 'sub_federation_club')) {
            $columns = array('contactNameWithComma', 'mc.*', 'ms.*', 'fg_cm_contact.*');
        } else {
            $columns = array('contactNameWithComma', 'mc.*', 'ms.*', 'fg_cm_contact.*', 'mf.*');
        }
        $contactPdo = new ContactPdo($container);
        $contactData = $contactPdo->getContactData($club, $contactId, $sWhere, $columns);

        $contactDetails = array();
        foreach ($contactData as $contactDataArray) {
            $contactDetails[$contactDataArray['id']] = $contactDataArray;
        }

        return $contactDetails;
    }

    /**
     * Function to confirm or discard changes.
     *
     * @param string $action                Confirm/Discard
     * @param int    $clubId                Club id
     * @param string $selectedIds           Selected ids
     * @param object $container             Container object
     * @param string $clubDefaultSystemLang Club system language
     * @param object $club                  Club object
     * @param int    $currContactId         Current contact id
     * @param array  $clubIdArray           Club data
     * @param object $terminologyService    Terminology service
     * 
     * @return array $confirmCount confirmation details
     */
    public function confirmOrDiscardChanges($action, $clubId, $selectedIds, $container, $clubDefaultSystemLang, $club, $currContactId, $clubIdArray, $terminologyService)
    {
        $conn = $this->getEntityManager()->getConnection();
        $confirmCount = array();
        $changes = $this->getChangesToConfirmOrDiscard($clubId, $selectedIds);
        if ($action == 'confirm') {
            $confirmCount = $this->confirmChanges($changes, $clubIdArray, $conn, $container, $clubDefaultSystemLang, $terminologyService, $currContactId, $club);
            if(count($confirmCount['changes'])) {
                $delChange = $this->deleteChanges($confirmCount['changes']);
            }
        } else {
            $this->discardChanges($changes, $container, $club, $currContactId, $clubId);
            $confirmCount = array('totalContacts' => count($changes), 'successCount' => count($changes));
            $delChange = $this->deleteChanges($changes);
        }
        
        return $confirmCount;
    }

    /**
     * Function to delete changes.
     *
     * @param array $changes Changes array
     */
    private function deleteChanges($changes)
    {
        foreach ($changes as $changeData) {
            $changeObj = $this->_em->getRepository('CommonUtilityBundle:FgCmChangeToconfirm')->find($changeData['changeId']);
            $this->_em->remove($changeObj);
        }
        $this->_em->flush();
        
        return 'success';
    }

    /**
     * Function to discard changes.
     *
     * @param array  $changes       Changes array
     * @param object $container     Container object
     * @param object $club          Club object
     * @param int    $currContactId Current contact id
     * @param int    $clubId        Club id
     */
    private function discardChanges($changes, $container, $club, $currContactId, $clubId)
    {
        $contactDataArray = array();
        foreach ($changes as $changeData) {
            $contactId = $changeData['contactId'];
            $attributeId = $changeData['attributeId'];
            $changedValue = $changeData['changedValue'];
            $changedDate = $changeData['changedDate']->format('Y-m-d H:i:s');
            if (isset($contactDataArray[$contactId])) {
                $contactData = $contactDataArray[$contactId];
            } else {
                $contactPdo = new ContactPdo($container);
                $contactData = $contactPdo->getContactData($club, $contactId, " fg_cm_contact.id=$contactId ");
                $contactDataArray[$contactId] = $contactData;
            }
            $valueBefore = $contactData[0][$attributeId];
            if ($valueBefore != $changedValue) {
                $attrObj = $this->_em->getRepository('CommonUtilityBundle:FgCmAttribute')->find($attributeId);
                $this->removeContactFile($changedValue, $attrObj, $clubId);
                $dataArray = array('is_confirmed' => '0', 'contactId' => $contactId, 'club_id' => $clubId, 'kind' => 'data', 'field' => $attrObj->getFieldname(), 'value_before' => $valueBefore, 'value_after' => $changedValue, 'changed_date' => $changedDate, 'changed_by' => $changeData['changedBy'], 'attribute_id' => $attributeId);
                $logObj = $this->_em->getRepository('CommonUtilityBundle:FgCmChangeLog')->insertLogEntry($dataArray, $currContactId, false, true);
                $this->_em->persist($logObj);
            }
        }
        $this->_em->flush();
    }

    /**
     * Function to remove contact file.
     *
     * @param string $fileName File name
     * @param object $attrObj  Attribute object
     * @param int    $clubId   Club id
     */
    private function removeContactFile($fileName, $attrObj, $clubId)
    {
        if ($fileName != '') {
            if (($attrObj->getinputType() == 'imageupload') || ($attrObj->getinputType() == 'fileupload')) {
                $subfolder=($attrObj->getinputType() == 'imageupload') ? 'contactfield_image' : 'contactfield_file';
                $logoPath=FgUtility::getUploadFilePath($clubId,$subfolder,false,$fileName);
                if (is_file($logoPath)) {
                    unlink($logoPath);
                }
            }
        }
    }

    /**
     * Function to confirm changes.
     *
     * @param array  $changes               Changes array
     * @param array  $clubIdArray           Club data array
     * @param object $conn                  Connection object
     * @param object $container             Container object
     * @param string $clubDefaultSystemLang Club Default System Language
     * @param object $terminologyService    Terminology service
     * @param int    $currContactId         Current contact id
     * @param int    $club                  Club object
     * 
     * @return int Success Count
     */
    private function confirmChanges($changes, $clubIdArray, $conn, $container, $clubDefaultSystemLang, $terminologyService, $currContactId, $club)
    {
        // Get club contact fields.
        $fieldDetails1 = $this->_em->getRepository('CommonUtilityBundle:FgCmAttributeset')->getAllClubContactFields($clubIdArray, $conn, 0, 0);
        $fieldDetails2 = $this->_em->getRepository('CommonUtilityBundle:FgCmAttributeset')->fieldItrator($fieldDetails1);
        $fieldDetails = $this->setTerminologyTerms($fieldDetails2, $container, $clubDefaultSystemLang, $terminologyService);

        $updateData = $this->getContactUpdateDataArray($changes, $fieldDetails, $container, $club);
        $result['totalContacts'] = count($changes);
        $result['successCount'] = 0;
        foreach ($updateData as $contactId => $updateDetails) {
            //$this->_em->getRepository('CommonUtilityBundle:FgCmAttribute')->saveContact($container, $clubDefaultSystemLang, $contactId, $fieldDetails, $club, $updateDetails['formValues'], array(), $updateDetails['deletedFilesArray'], $updateDetails['contactData'], $currContactId, array(), array(), 1, '1', $updateDetails['changedData']);
            $contactDetails = $this->contactDetails($contactId, $container, $club, $conn);
            $hasFedMembership = ($contactDetails[0]['fedMembershipId'] !='' || $contactDetails[0]['fedMembershipId'] !='NULL') ? 1 : 0;
            //email validation 
            foreach ($updateDetails['formValues'] as $key => $valArr) {
                if(isset($valArr[3])) {
                    $isEmailValid = $this->_em->getRepository('CommonUtilityBundle:FgCmAttribute')->searchEmailExistAndIsMergable($container, $contactId, $valArr[3], $hasFedMembership, 0, 'contact', true , 'Single person');
                    if((count($isEmailValid)) == 0) {
                        $formVal[$key] = $updateDetails['formValues'][$key];
                        $contactSave = new ContactDetailsSave($container, $fieldDetails, $contactDetails, $contactId);
                        $contactSave->saveContact($formVal, $updateDetails['deletedFilesArray'], array(), array(), 0, '1', $updateDetails['changedData'], 0, 0);
                        foreach ($updateDetails['changes'] as $changeKey => $changeVal) {
                            if(($changeKey = array_search($changeVal['changedValue'], $formVal[$key])) !== false) {
                                $result['changes'][] = $changeVal;
                                $result['successCount'] = $result['successCount']+1;
                            }
                        }
                    }
                }
                else {
                    $formValElse[$key] = $updateDetails['formValues'][$key];
                    $contactSave = new ContactDetailsSave($container, $fieldDetails, $contactDetails, $contactId);
                    $contactSave->saveContact($formValElse, $updateDetails['deletedFilesArray'], array(), array(), 0, '1', $updateDetails['changedData'], 0, 0);
                    foreach ($updateDetails['changes'] as $changeKeyElse => $changeValElse) {
                        if(($changeKeyElse = array_search($changeValElse['changedValue'], $formValElse[$key])) !== false) {
                            $result['changes'][] = $changeValElse;
                            $result['successCount'] = $result['successCount']+1;
                        }
                    }
                    unset($formValElse[$key]);
                }
            }
        }
        return $result;
    }
    
    /**
     * function to get the contact name from its id
     *
     * @param int $contactId the contact id
     * @param int $type      Type of contact
     *
     * @return array
     */
    private function contactDetails($contactId, $container, $club, $conn) {
        $contactlistClass = new Contactlist($container, '', $club, 'contact');
        $contactlistClass->setColumns('*');
        $contactlistClass->setFrom('*');
        $contactlistClass->confirmedFlag=false;
        $contactlistClass->setCondition();
        $sWhere = " fg_cm_contact.id=$contactId";
        $contactlistClass->addCondition($sWhere);
        $listquery = $contactlistClass->getResult();
        $fieldsArray = $conn->fetchAll($listquery);

        return $fieldsArray;
    }

    /**
     * Function to get data array for saving the contact details on confirming.
     *
     * @param array  $changes      Changes to be confirmed
     * @param array  $fieldDetails Contact field details
     * @param object $container    Container object
     * @param object $club         Club object
     *
     * @return array $updateData Array of data to be updated.
     */
    private function getContactUpdateDataArray($changes, $fieldDetails, $container, $club)
    {
        $updateData = $deletedFilesArray = array();
        foreach ($changes as $changeData) {
            $contactId = $changeData['contactId'];
            $attributeId = $changeData['attributeId'];
            $attrCatId = $fieldDetails['attrCatIds'][$attributeId];
            if (($changeData['changedValue'] == '') && in_array($fieldDetails['fieldsArray'][$attrCatId]['values'][$attributeId]['inputType'], array('imageupload', 'fileupload'))) {
                $deletedFilesArray[$contactId][] = $attributeId;
            }
            if (isset($updateData[$contactId])) {
                $updateData[$contactId]['formValues'][$attrCatId][$attributeId] = $changeData['changedValue'];
                $updateData[$contactId]['changedData'][$attrCatId][$attributeId] = array('changed_date' => $changeData['changedDate']->format('Y-m-d H:i:s'), 'changed_by' => $changeData['changedBy']);
            } else {
                $formValueData = array($attrCatId => array($attributeId => $changeData['changedValue']));
                $changedDateArray = array($attrCatId => array($attributeId => array('changed_date' => $changeData['changedDate']->format('Y-m-d H:i:s'), 'changed_by' => $changeData['changedBy'])));
                $contactPdo = new ContactPdo($container);
                $contactData = $contactPdo->getContactData($club, $contactId, " fg_cm_contact.id=$contactId ");
                $updateData[$contactId] = array('contactData' => $contactData, 'formValues' => $formValueData, 'changedData' => $changedDateArray);
            }
            $updateData[$contactId]['deletedFilesArray'] = $deletedFilesArray[$contactId];
            $updateData[$contactId]['changes'][] = $changeData;
            $updateData[$contactId]['validationValue'][] = $changeData['changedValue'];
        }

        return $updateData;
    }

    /**
     * Function to get changes to confirm or discard.
     *
     * @param int $clubId         Club Id
     * @param string $selectedIds Selected Ids
     *
     * @return array $changes Changes array
     */
    private function getChangesToConfirmOrDiscard($clubId, $selectedIds)
    {
        $changes = $this->createQueryBuilder('ch')
                ->select('ch.id AS changeId, IDENTITY(ch.contact) AS contactId, IDENTITY(ch.attribute) AS attributeId, ch.date AS changedDate, ch.value AS changedValue, IDENTITY(ch.changedBy) AS changedBy')
                ->where('ch.club = :clubId')
                ->andWhere('ch.type = :type');
        if (count($selectedIds)) {
            $changes = $changes->andWhere('ch.id IN (:selectedIds)');
            $parameters = array('clubId' => $clubId, 'type' => 'change', 'selectedIds' => $selectedIds);
        } else {
            $parameters = array('clubId' => $clubId, 'type' => 'change');
        }
        $changes = $changes->orderBy('ch.id', 'ASC')
                    ->setParameters($parameters)
                    ->getQuery()
                    ->getResult();

        return $changes;
    }

    /**
     * set terminology terms to contact detail array
     *
     * @param array $fieldDetails           Field details array
     * @param object $container             Container object
     * @param string $clubDefaultSystemLang Club default system language
     * @param object $terminologyService    Terminology service
     *
     * @return type
     */
    private function setTerminologyTerms($fieldDetails, $container, $clubDefaultSystemLang, $terminologyService)
    {
        $containerParameters = $container->getParameterBag();
        $profilePictureTeam = $containerParameters->get('system_field_team_picture');
        $profilePictureClub = $containerParameters->get('system_field_communitypicture');
        $termi21 = $terminologyService->getTerminology('Club', $containerParameters->get('singular'));
        $termi5 = $terminologyService->getTerminology('Team', $containerParameters->get('singular'));
        if (isset($fieldDetails['attrTitles'][$profilePictureTeam][$clubDefaultSystemLang])) {
            $fieldDetails['attrTitles'][$profilePictureTeam][$clubDefaultSystemLang]=str_replace('%Team%', ucfirst($termi5), $fieldDetails['attrTitles'][$profilePictureTeam][$clubDefaultSystemLang]);
        }
        if (isset($fieldDetails['attrTitles'][$profilePictureClub][$clubDefaultSystemLang])) {
            $fieldDetails['attrTitles'][$profilePictureClub][$clubDefaultSystemLang]=str_replace('%Club%', ucfirst($termi21), $fieldDetails['attrTitles'][$profilePictureClub][$clubDefaultSystemLang]);
        }

        return $fieldDetails;
    }

    /**
     * Function to get count of changes to be confirmed.
     *
     * @param int $clubId Club id
     *
     * @return int $count Count
     */
    public function getChangesToConfirmCount($clubId)
    {
        $result = $this->createQueryBuilder('ch')
                ->select("COUNT(ch.id) AS confirmCount")
                ->where('ch.club = :clubId')
                ->andWhere('ch.type = :type')
                ->setParameters(array('clubId' => $clubId, 'type' => 'change'))
                ->getQuery()
                ->getResult();
        $count = $result[0]['confirmCount'];

        return $count;
    }

    /**
     * Function to save changes to be confirmed.
     *
     * @param array $saveData Save data array
     * @param int $contactId  Contact id
     * @param int $changedBy  Changed by contact id
     * @param int $clubId     Club id
     */
    public function saveChangesToConfirm($saveData, $contactId, $changedBy, $clubId, $contactFields = array(), $roleId)
    {
        $changedByObj = $this->_em->getReference('CommonUtilityBundle:FgCmContact', $changedBy);
        $clubObj = $this->_em->getReference('CommonUtilityBundle:FgClub', $clubId);
        if (count($saveData)) {
            foreach ($saveData as $catId => $data) {
                foreach ($data as $attributeId => $value) {
                    $contactFieldDetails = ($contactFields[$attributeId]);
                    
                    //When date, insert it in date format.
                    if ($contactFieldDetails['type'] === 'date') {
                        $date = new \DateTime();
                        $value = $date->createFromFormat(FgSettings::getPhpDateFormat(), $value)->format('Y-m-d');
                    } else if ($contactFieldDetails['type'] === 'number') {
                        // If field type is number, insert value in number format.
                        $value = str_replace(FgSettings::getDecimalMarker(), '.', $value);
                    }
                    
                    $this->insertData($contactId, $attributeId, $value, $changedByObj, $clubObj, 'change', $roleId);
                }
            }
            $this->_em->flush();
        }
    }

    /**
     * Function to save mutations.
     *
     * @param int   $contactId        Contact id
     * @param int   $changedBy        Changed by contact id
     * @param int   $clubId           Club id
     * @param int   $roleId           Role id
     * @param array $addedFunctions   Added assignment functions
     * @param array $removedFunctions Removed assignment functions
     * @param int   $roleCatId        Role category id
     */
    public function saveMutations($contactId, $changedBy, $clubId, $roleId, $addedFunctions, $removedFunctions, $roleCatId)
    {
        if (count($addedFunctions)) {
            // Get mutation object.
            $mutationObj = $this->getCreationOrMutationObject($contactId, $roleId, $clubId, $changedBy, 'mutation', 'ADDED');
            $oppositeObj = $this->getChangesArray($contactId, $roleId, $clubId, $changedBy, 'mutation', 'REMOVED');
            $oppositeObjId = count($oppositeObj) ? $oppositeObj['0']['id'] : '';
            foreach ($addedFunctions as $addedFunction) {
                $this->saveAddedOrRemovedMutation($mutationObj, $oppositeObjId, $addedFunction, 'ADDED');
                // Set contact assignment as added.
                $this->_em->getRepository('CommonUtilityBundle:FgRmRoleContact')->updateAssignmentStatus($contactId, $clubId, $roleCatId, $roleId, $addedFunction, '0');
            }
        }
        if (count($removedFunctions)) {
            // Get mutation object.
            $mutationObj = $this->getCreationOrMutationObject($contactId, $roleId, $clubId, $changedBy, 'mutation', 'REMOVED', $removedFunctions);
            $oppositeObj = $this->getChangesArray($contactId, $roleId, $clubId, $changedBy, 'mutation', 'ADDED');
            $oppositeObjId = count($oppositeObj) ? $oppositeObj['0']['id'] : '';
            foreach ($removedFunctions as $removedFunction) {
                $this->saveAddedOrRemovedMutation($mutationObj, $oppositeObjId, $removedFunction, 'REMOVED');
                // Set contact assignment as removed.
                $this->_em->getRepository('CommonUtilityBundle:FgRmRoleContact')->updateAssignmentStatus($contactId, $clubId, $roleCatId, $roleId, $removedFunction, '1');
            }
        }
        $this->_em->flush();
    }

    /**
     * Function to insert added mutation or removed mutation.
     *
     * @param object $mutationObj   Mutation object
     * @param int    $mutationObjId Mutation object id
     * @param int    $functionId    Function id
     * @param string $actionType    Action type (ADDED/REMOVED)
     */
    private function saveAddedOrRemovedMutation($mutationObj, $mutationObjId, $functionId, $actionType)
    {
        if ($mutationObjId == '') {
            $mutationFunObj = false;
        } else {
            $checkActionType = ($actionType == 'ADDED') ? 'REMOVED' : 'ADDED';
            $mutationFunObj = $this->_em->getRepository('CommonUtilityBundle:FgCmChangeToconfirmFunctions')->findBy(array('toconfirm' => $mutationObjId, 'function' => $functionId, 'actionType' => $checkActionType));
        }
        // If there is already an entry with opposite action type then that entry will be deleted, otherwise an entry will be added.
        if ($mutationFunObj) {
            foreach ($mutationFunObj as $funObj) {
                $this->_em->remove($funObj);
            }
        } else {
            $this->_em->getRepository('CommonUtilityBundle:FgCmChangeToconfirmFunctions')->saveMutationFunctions($mutationObj, $functionId, $actionType);
        }
    }

    /**
     * Function to get creation or mutation object.
     *
     * @param int    $contactId  Contact id
     * @param int    $roleId     Role id
     * @param int    $clubId     Club id
     * @param int    $changedBy  Changed by contact id
     * @param string $type       Type (mutation/creation)
     * @param string $actionType Action type (ADDED/REMOVED)
     *
     * @return object $functionsObj Creation/Mutation object
     */
    private function getCreationOrMutationObject($contactId, $roleId, $clubId, $changedBy, $type, $actionType, $removedFunctions = array())
    {
        $changeObj = $this->getChangesArray($contactId, $roleId, $clubId, $changedBy, $type, $actionType);
        if (count($changeObj)) {
            $changeObjId = $changeObj['0']['id'];
            $functionsObj = $this->find($changeObjId);
        } else {
            $changedByObj = $this->_em->getReference('CommonUtilityBundle:FgCmContact', $changedBy);
            $clubObj = $this->_em->getReference('CommonUtilityBundle:FgClub', $clubId);
            $functionsObj = $this->insertData($contactId, '', '', $changedByObj, $clubObj, $type, $roleId, true, $removedFunctions);
        }

        return $functionsObj;
    }

    /**
     * Function to get changes.
     *
     * @param int    $contactId  Contact id
     * @param int    $roleId     Role id
     * @param int    $clubId     Club id
     * @param int    $changedBy  Changed by contact id
     * @param string $type       Type (mutation/creation)
     * @param string $actionType Action type (ADDED/REMOVED)
     *
     * @return type
     */
    private function getChangesArray($contactId, $roleId, $clubId, $changedBy, $type, $actionType)
    {
        $changeObj = $this->createQueryBuilder('ch')
                ->select('ch.id')
                ->leftJoin("CommonUtilityBundle:FgCmChangeToconfirmFunctions", "chf", "WITH", "chf.toconfirm=ch.id")
                ->where('ch.contact = :contactId')
                ->andWhere('ch.roleId = :roleId')
                ->andWhere('ch.club = :clubId')
                ->andWhere('ch.changedBy = :changedBy')
                ->andWhere('ch.type = :type')
                ->andWhere('ch.confirmStatus = :confirmStatus')
                ->andWhere('chf.actionType = :actionType')
                ->setParameters(array('contactId' => $contactId, 'roleId' => $roleId, 'clubId' => $clubId, 'changedBy' => $changedBy, 'type' => $type, 'confirmStatus' => 'NONE', 'actionType' => $actionType))
                ->getQuery()
                ->getResult();

        return $changeObj;
    }

    /**
     * Function to save assignment creations.
     *
     * @param int   $contactId Contact id
     * @param int   $roleId    Role id
     * @param int   $clubId    Club id
     * @param int   $changedBy Changed by contact id
     * @param array $functions Assigned functions
     */
    public function saveAssignmentCreations($contactId, $roleId, $clubId, $changedBy, $functions)
    {
        $totalFunsCount = count($functions);
        // Get creation object.
        $creationObj = $this->getCreationOrMutationObject($contactId, $roleId, $clubId, $changedBy, 'creation', 'ADDED');
        $creations = $this->_em->getRepository('CommonUtilityBundle:FgCmChangeToconfirmFunctions')->findBy(array('toconfirm' => $creationObj->getId()));

        $key = 0;
        foreach ($creations as $creation) {
            if ($key >= $totalFunsCount) {
                // Remove creation entry.
                $this->_em->remove($creation);
            } else {
                // Update creation entry.
                $functionObj = $this->_em->getReference('CommonUtilityBundle:FgRmFunction', $functions[$key]);
                $creation->setFunction($functionObj);
                $this->_em->persist($creation);
                unset($functions[$key]);
            }
            $key++;
        }
        foreach ($functions as $function) {
            // Add creation entry.
            $this->_em->getRepository('CommonUtilityBundle:FgCmChangeToconfirmFunctions')->saveMutationFunctions($creationObj, $function, 'ADDED');
        }
        $this->_em->flush();
    }

    /**
     * Function to insert data to FgCmChangeToconfirm table.
     *
     * @param int $contactId       Contact id
     * @param int $attributeId     Attribute id
     * @param string $value        Changed value
     * @param object $changedByObj Changed by contact object
     * @param object $clubObj      Club object
     * @param string $type         Change data type (Change/Mutation/Creation)
     */
    private function insertData($contactId, $attributeId, $value, $changedByObj, $clubObj, $type = 'change', $roleId = '', $getObj = false, $removedFunctions = array())
    {
        $contactObj = $this->_em->getReference('CommonUtilityBundle:FgCmContact', $contactId);
        $attributObj = ($attributeId == '') ? NULL : $this->_em->getReference('CommonUtilityBundle:FgCmAttribute', $attributeId);
        $changedVal = FgUtility::getSecuredData($value, $this->getEntityManager()->getConnection());
        
        $changeObj = new FgCmChangeToconfirm();
        $changeObj->setContact($contactObj);
        $changeObj->setAttribute($attributObj);
        $changeObj->setRoleId($roleId);
        $changeObj->setDate(new \DateTime("now"));
        $changeObj->setValue($value);
        $changeObj->setChangedBy($changedByObj);
        $changeObj->setClub($clubObj);
        $changeObj->setType($type);
        $changeObj->setConfirmStatus('NONE');

        $this->_em->persist($changeObj);
        
        if(count($removedFunctions) > 0) {
            foreach ($removedFunctions as $removedFunction) {
                $functionObj = $this->_em->getReference('CommonUtilityBundle:FgRmFunction', $removedFunction);
                $changeFunObj = new FgCmChangeToconfirmFunctions();
                $changeFunObj->setToconfirm($changeObj);
                $changeFunObj->setFunction($functionObj);
                $changeFunObj->setActionType('REMOVED');
                
                $this->_em->persist($changeFunObj);
            }
        }
        
        if ($getObj) {
            $this->_em->flush();
            return $changeObj;
        }
    }

    /**
     * Function to getting changes to be confirmed of a single contact.
     *
     * @param int $clubId    Club id
     * @param int $contact   Contact id
     * @param int $changedBy Changed by contact
     *
     * @return array $resultArray Array of changes
     */
    public function getConfirmChangesOfContact($clubId, $contact, $changedBy = '')
    {
        $result = $this->createQueryBuilder('ch')
                ->select("IDENTITY(ch.attribute) AS attributeId, ch.value AS valueAfter, IDENTITY(ch.changedBy) AS changedBy")
                ->where('ch.club = :clubId')
                ->andWhere('ch.contact = :contact')
                ->andWhere('ch.type = :type');

        if ($changedBy == '') {
            $parameters = array('clubId' => $clubId, 'contact' => $contact, 'type' => 'change');
        } else {
            $result = $result->andWhere('ch.changedBy = :changedBy');
            $parameters = array('clubId' => $clubId, 'contact' => $contact, 'type' => 'change', 'changedBy' => $changedBy);
        }
        $result = $result->orderBy('ch.id', 'ASC')
                         ->setParameters($parameters)
                         ->getQuery()
                         ->getResult();

        $resultArray = array();
        foreach ($result as $resultData) {
            $resultArray[$resultData['attributeId']] = array('value' => $resultData['valueAfter'], 'changedBy' => $resultData['changedBy']);
        }

        return $resultArray;
    }

    /**
     * Function to get all mutations or creations to be confirmed by an admin
     *
     * @param int    $clubId    ClubId
     * @param object $container Container Object
     * @param string $type      Confirmation type (creation or mutation)
     *
     * @return array $result Data array
     */
    public function getAssignmentsToConfirm($clubId, $container, $type = 'mutation')
    {
        // Configuring UDF.
        $doctrineConfig = $this->getEntityManager()->getConfiguration();
        $doctrineConfig->addCustomStringFunction('checkActiveContact', 'Common\UtilityBundle\Extensions\CheckActiveContact');
        $doctrineConfig->addCustomStringFunction('DATE_FORMAT', 'Common\UtilityBundle\Extensions\DateFormat');
        $doctrineConfig->addCustomStringFunction('contactName', 'Common\UtilityBundle\Extensions\FetchContactName');
        
        $termExecutive = $container ? ucfirst($container->get('fairgate_terminology_service')->getTerminology('Executive Board', $container->getParameter('singular'))) : 'Executive';
        $clubDefaultLanguage = $container ? $container->get('club')->get('default_lang') : 'de';

        //$datetimeFormat = FgSettings::getMysqlDateTimeFormat();
        $datetimeFormat = '%Y-%m-%d';
        $clubDql = $this->getClubName();
        
        $result = $this->createQueryBuilder('ch')
                ->select("fcc.isFedAdmin,ch.id AS confirmId, DATE_FORMAT( ch.date, '$datetimeFormat' ) AS changeDate, IDENTITY(ch.contact) AS contactId, contactName(ch.contact) AS contactName, "
                        . "(SELECT (CASE WHEN r.isExecutiveBoard=1 THEN '" . $termExecutive . "' WHEN (ri18n.titleLang IS NULL OR ri18n.titleLang = '') THEN r.title ELSE ri18n.titleLang END) FROM "
                        . "CommonUtilityBundle:FgRmRole r INNER JOIN CommonUtilityBundle:FgRmRoleI18n ri18n WITH ri18n.id=r.id AND ri18n.lang='" . $clubDefaultLanguage . "' WHERE r.id = ch.roleId) AS roleTitle, "
                        . "(GROUP_CONCAT(f.title SEPARATOR ', ')) AS functionTitle, chf.actionType AS actionFlag, "
                        . "IDENTITY(ch.changedBy) AS changedById, checkActiveContact(ch.changedBy, :clubId) as activeContact, "
                        . "contactName(ch.changedBy) As changedBy ")
                ->addSelect('('.$clubDql->getDQL().') AS clubChangedBy ')
                ->leftJoin("CommonUtilityBundle:FgCmChangeToconfirmFunctions", "chf", "WITH", "chf.toconfirm=ch.id")
                ->leftJoin("CommonUtilityBundle:FgRmFunction", "f", "WITH", "f.id=chf.function")
                ->leftJoin("CommonUtilityBundle:FgCmContact", "fcc", "WITH", "fcc.id=ch.changedBy")
                ->where('ch.club = :clubId')
                ->andWhere('ch.type = :type')
                ->andWhere('ch.confirmStatus = :confirmStatus')
                ->groupBy('ch.id')
                ->orderBy('ch.id', 'DESC')
                ->setParameters(array('clubId' => $clubId, 'type' => $type, 'confirmStatus' => 'NONE'))
                ->getQuery()
                ->getResult();
                       
        return $result;
    }
    
    /**
     * Function to get the  club.
     *
     * @return Integer
     */
    private function getClubName()
    {
        $moduleQuery = $this->getEntityManager()->createQueryBuilder();
        $moduleQuery->select('fc.title')
                ->from('CommonUtilityBundle:FgCmContact', 'ct')
                ->innerJoin('CommonUtilityBundle:FgClub', 'fc', 'WITH', 'ct.mainClub = fc.id')
                ->where('ct.id = ch.changedBy');

        return $moduleQuery;
    }

    /**
     * Function to get all confirmations log
     *
     * @param int    $clubId    ClubId
     * @param object $container Container Object
     * @param string $type      Confirmation type (creation or mutation)
     *
     * @return array $result Data array
     */
    public function getConfirmationsLog($clubId, $container, $type = 'mutation')
    {
        // Configuring UDF.
        $doctrineConfig = $this->getEntityManager()->getConfiguration();
        $doctrineConfig->addCustomStringFunction('checkActiveContact', 'Common\UtilityBundle\Extensions\CheckActiveContact');
        $doctrineConfig->addCustomStringFunction('DATE_FORMAT', 'Common\UtilityBundle\Extensions\DateFormat');
        $doctrineConfig->addCustomStringFunction('contactName', 'Common\UtilityBundle\Extensions\FetchContactName');
        $termExecutive = $container ? ucfirst($container->get('fairgate_terminology_service')->getTerminology('Executive Board', $container->getParameter('singular'))) : 'Executive';
        $clubDefaultLanguage = $container ? $container->get('club')->get('default_lang') : 'de';
        $clubDql = $this->getClubName();
        $clubDecidedDql = $this->getClubNameDecidedBy();
        //$datetimeFormat = FgSettings::getMysqlDateTimeFormat();
        $datetimeFormat = '%Y-%m-%d';
        
        $result = $this->createQueryBuilder('ch')
                ->select("fcc.isFedAdmin,ch.id AS confirmId, DATE_FORMAT( ch.date, '$datetimeFormat' ) AS changeDate, IDENTITY(ch.contact) AS contactId, contactName(ch.contact) AS contactName, ch.confirmStatus, IDENTITY(l.confirmedBy) AS decidedById, contactName(l.confirmedBy) AS decidedBy, DATE_FORMAT( l.confirmedDate, '$datetimeFormat' ) AS decisionDate, "
                        . "(SELECT (CASE WHEN r.isExecutiveBoard=1 THEN '" . $termExecutive . "' WHEN (ri18n.titleLang IS NULL OR ri18n.titleLang = '') THEN r.title ELSE ri18n.titleLang END) FROM "
                        . "CommonUtilityBundle:FgRmRole r INNER JOIN CommonUtilityBundle:FgRmRoleI18n ri18n WITH ri18n.id=r.id AND ri18n.lang='" . $clubDefaultLanguage . "' WHERE r.id = ch.roleId) AS roleTitle, "
                        . "(GROUP_CONCAT(f.title SEPARATOR ', ')) AS functionTitle, chf.actionType AS actionFlag,checkActiveContact(l.confirmedBy, :clubId) as activeContactDecided, "
                        . "contactName(ch.changedBy) AS changedBy, IDENTITY(ch.changedBy) AS changedById, checkActiveContact(ch.changedBy, :clubId) as activeContact ")
                ->addSelect('('.$clubDql->getDQL().') AS clubChangedBy ')
                ->addSelect('('.$clubDecidedDql->getDQL().') AS clubDecidedBy ')
                ->leftJoin("CommonUtilityBundle:FgCmChangeToconfirmFunctions", "chf", "WITH", "chf.toconfirm=ch.id")
                ->leftJoin("CommonUtilityBundle:FgRmFunction", "f", "WITH", "f.id=chf.function")
                ->leftJoin("CommonUtilityBundle:FgCmMutationLog", "l", "WITH", "l.toconfirm=ch.id")
                 ->leftJoin("CommonUtilityBundle:FgCmContact", "fcc", "WITH", "fcc.id=ch.changedBy")
                ->where('ch.club = :clubId')
                ->andWhere('ch.type = :type')
                ->andWhere('ch.confirmStatus != :confirmStatus')
                ->groupBy('ch.id')
                ->orderBy('ch.id', 'DESC')
                ->setParameters(array('clubId' => $clubId, 'type' => $type, 'confirmStatus' => 'NONE'))
                ->getQuery()
                ->getResult();

        return $result;
    }
    /**
     * Function to get the  club.
     *
     * @return Integer
     */
    private function getClubNameDecidedBy()
    {
        $moduleQuery = $this->getEntityManager()->createQueryBuilder();
        $moduleQuery->select('fc1.title')
                ->from('CommonUtilityBundle:FgCmContact', 'ct1')
                ->innerJoin('CommonUtilityBundle:FgClub', 'fc1', 'WITH', 'ct1.mainClub = fc1.id')
                ->where('ct1.id = l.confirmedBy');

        return $moduleQuery;
    }

/**
     * Function to get the contacts added in a list of confirmation requests.
     *
     * @param array $selectedConfirmationIds
     *
     * @return array $resultArray Array of contacts
     */
    public function getContactInConfirmation($selectedConfirmationIds)
    {
        $result = $this->createQueryBuilder('ch')
                ->select("ch.id AS id, IDENTITY(ch.contact) AS contactid, contactname(ch.contact) AS name, IDENTITY(ch.changedBy) AS changedBy")
                ->where('ch.id IN( :selectedConfirmationIds)')
                ->setParameters(array('selectedConfirmationIds' => $selectedConfirmationIds))
                ->groupBy('contactid');
       $resultantArrayObj = $result->getQuery()->getResult();
       foreach($resultantArrayObj as $resultant)
           $resultantArray[$resultant['contactid']] = array($resultant['name'],$resultant['changedBy'],$resultant['id']);

       return $resultantArray;
    }

    /**
     * Function to save non exisitng contact to team.
     *
     * @param array $insertArray common id array
     *
     * @return Object $request  request object
     */
    public function addNonExistingContact($insertArray, $request)
    {
        $contactArray = $request->get('non_memberlist');
        $functionArray = $request->get('functions');
        $roleId = $request->get('teamId');
        $changedByObj = $this->_em->getReference('CommonUtilityBundle:FgCmContact', $insertArray['userId']);
        $clubObj = $this->_em->getReference('CommonUtilityBundle:FgClub', $insertArray['clubId']);
        foreach ($contactArray as $contactAttrs) {
            $contactObj = $this->_em->getReference('CommonUtilityBundle:FgCmContact', $contactAttrs);
            $changeObj = new FgCmChangeToconfirm();
            $changeObj->setContact($contactObj);
            $changeObj->setRoleId($roleId);
            $changeObj->setDate(new \DateTime('now'));
            $changeObj->setChangedBy($changedByObj);
            $changeObj->setClub($clubObj);
            $changeObj->setType('mutation');
            $changeObj->setConfirmStatus('NONE');
            $this->_em->persist($changeObj);
            $this->_em->flush();
            //$insertId = $changeObj->getId();
            foreach ($functionArray as $functionAttrs) {
                    $this->_em->getRepository('CommonUtilityBundle:FgCmChangeToconfirmFunctions')->saveMutationFunctions($changeObj, $functionAttrs, 'ADDED');
            }
            $this->_em->flush();
        }
    }

    /**
     * Function to discard the selected mutations or creations
     *
     * @param int    $clubId      ClubId
     * @param int    $contactId   ContactId
     * @param array  $selectedIds Selected Confirm Ids
     * @param string $page        Creations or mutations
     */
    public function discardSelectedConfirmations($container, $clubId, $contactId, $selectedIds, $page)
    {
        $this->updateConfirmStatusAndInsertLog($clubId, $contactId, $selectedIds, 'discard');
        $contactPdo = new ContactPdo($container);
        if ($page == 'creations') {
            $contactPdo->updateContactStatus($selectedIds, 'discard');
        } else {
            $contactPdo->updateRoleContactFunctionStatus($selectedIds, $clubId);
        }
    }

    /**
     * Function to confirm the selected mutations or creations
     *
     * @param object $container   Container object
     * @param object $club        Club object
     * @param int    $clubId      ClubId
     * @param int    $contactId   ContactId
     * @param array  $selectedIds Selected Confirm Ids
     * @param string $page        Creations or mutations
     */
    public function confirmSelectedConfirmations($container, $club, $clubId, $contactId, $selectedIds, $page)
    {
        $this->updateConfirmStatusAndInsertLog($clubId, $contactId, $selectedIds, 'confirm');
        $this->insertorUpdateAssignments($container, $club, $clubId, $selectedIds);
        if ($page == 'creations') {
            $contactPdo = new ContactPdo($container);
            $contactPdo->updateContactStatus($selectedIds, 'confirm');
            $contactPdo->updateConfirmedTimeOfContactLogEntries($selectedIds);
        }
    }

    /**
     * Function to discard the selected mutations or creations
     *
     * @param int    $clubId      ClubId
     * @param int    $contactId   ContactId
     * @param array  $selectedIds Selected Confirm Ids
     * @param string $action      confirm or discard
     */
    public function updateConfirmStatusAndInsertLog($clubId, $contactId, $selectedIds, $action)
    {
        //update confirm status in FgCmChangeToconfirm table
        $confirmStatus = ($action == 'confirm') ? 'CONFIRMED' : 'DISCARDED';
        $qb = $this->createQueryBuilder();
        $q = $qb->update('CommonUtilityBundle:FgCmChangeToconfirm', 'c')
                ->set('c.confirmStatus', ':confirmStatus')
                ->where('c.id IN (:confirmIds)')
                ->andWhere('c.club = :clubId')
                ->setParameters(array('confirmIds' => $selectedIds, 'clubId' => $clubId, 'confirmStatus' => $confirmStatus))
                ->getQuery();
        $p = $q->execute();
        //insert log entry in FgCmMutationLog table
        $contactObj = $this->_em->getReference('CommonUtilityBundle:FgCmContact', $contactId);
        foreach ($selectedIds as $selectedId) {
            $confirmObj = $this->_em->getReference('CommonUtilityBundle:FgCmChangeToconfirm', $selectedId);
            $logObj = new \Common\UtilityBundle\Entity\FgCmMutationLog();
            $logObj->setToconfirm($confirmObj);
            $logObj->setConfirmedBy($contactObj);
            $logObj->setConfirmedDate(new \DateTime("now"));
            $logObj->setContact($contactObj);
            $this->_em->persist($logObj);
        }
        $this->_em->flush();
    }

    /**
     * Function to insert and update assignments on confirmation
     *
     * @param object $container   Container object
     * @param object $club        Club object
     * @param int    $clubId      Club id
     * @param array  $selectedIds Selected Ids
     */
    public function insertorUpdateAssignments($container, $club, $clubId, $selectedIds)
    {
        $workgroupCatId = $club->get('club_workgroup_id');
        // Configuring UDF.
        $doctrineConfig = $this->getEntityManager()->getConfiguration();
        $doctrineConfig->addCustomStringFunction('DATE_FORMAT', 'Common\UtilityBundle\Extensions\DateFormat');

        $result = $this->createQueryBuilder('ch')
                ->select("ch.id AS confirmId, DATE_FORMAT(ch.date, '%Y-%m-%d %H:%i:%s') AS changeDate1, IDENTITY(ch.contact) AS contactId, ch.roleId AS roleId, "
                        . "GROUP_CONCAT(chf.function) AS functionIds, GROUP_CONCAT(chf.actionType) AS functionActions, r.type, IDENTITY(r.teamCategory) AS roleCatId, "
                        . "IDENTITY(ch.changedBy) AS changedBy, (SELECT DATE_FORMAT(m.confirmedDate, '%Y-%m-%d %H:%i:%s') FROM CommonUtilityBundle:FgCmMutationLog m WHERE m.toconfirm = ch.id) AS changeDate")
                ->leftJoin("CommonUtilityBundle:FgRmRole", "r", "WITH", "ch.roleId=r.id")
                ->leftJoin("CommonUtilityBundle:FgCmChangeToconfirmFunctions", "chf", "WITH", "chf.toconfirm=ch.id")
                ->where('ch.club = :clubId')
                ->andWhere('ch.id IN (:confirmIds)')
                ->groupBy('ch.id')
                ->orderBy('ch.id', 'DESC')
                ->setParameters(array('clubId' => $clubId, 'confirmIds' => $selectedIds))
                ->getQuery()
                ->getResult();

        $translationsArray = array('workgroup' => $container->get('translator')->trans('WORKGROUPS'));
        //build array for saving assignments
        foreach ($result as $val) {
            $assignments = array();
            $roleCatId = ($val['type'] == 'W') ? $workgroupCatId : 'team' . $val['roleCatId'];
            $functionIdsArr = explode(',', $val['functionIds']);
            $functionActionsArr = explode(',', $val['functionActions']);
            foreach ($functionIdsArr as $key => $functionId) {
                $actionFlag = ($functionActionsArr[$key] == 'ADDED') ? 'is_new' : 'is_deleted';
                $actionValue = ($functionActionsArr[$key] == 'ADDED') ? $functionId : 1;
                $assignments[$val['contactId']][$roleCatId]['role'][$val['roleId']]['function'][$functionId][$actionFlag] = $actionValue;
            }
            if (count($assignments) > 0) {
                $contactIdArr = explode(',', $val['contactId']);
                $resultArray = $this->_em->getRepository('CommonUtilityBundle:FgRmRoleContact')->updateContactAssignments($assignments, $clubId, $contactIdArr, $val['changedBy'], $club, $container->get('fairgate_terminology_service'), $container, $translationsArray, $val['changeDate'], 1, false, 'draft');
            }
        }
    }

    /**
     * Function to get count of mutations or creations to be confirmed.
     *
     * @param int    $clubId Club id
     * @param string $type   mutation / creation
     *
     * @return int   $count  Count
     */
    public function getConfirmationsCount($clubId, $type)
    {
        $result = $this->createQueryBuilder('ch')
                ->select("COUNT(ch.id) AS confirmCount")
                ->where('ch.club = :clubId')
                ->andWhere('ch.type = :type')
                ->andWhere('ch.confirmStatus = :confirmStatus')
                ->setParameters(array('clubId' => $clubId, 'type' => $type, 'confirmStatus' => 'NONE'))
                ->getQuery()
                ->getResult();
        $count = $result[0]['confirmCount'];

        return $count;
    }
}
