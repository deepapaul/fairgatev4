<?php

namespace Clubadmin\ContactBundle\Util;

use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Util\FgSettings;
use Common\UtilityBundle\Util\FgFedMemberships;
use Common\UtilityBundle\Util\FgLogHandler;
use Common\UtilityBundle\Repository\Pdo\ContactPdo;

/**
 * Used to save contact details
 *
 * @author  pitsolutions.ch <pit@solutions.com>
 *
 * @version Release: <v4>
 */
class ContactDetailsSave
{

    /**
     * $em.
     *
     * @var object entitymanager object
     */
    private $em;
    private $translator;
    private $container;
    private $terminology;
    private $club;
    private $contactId = false;
    private $contactClubId;
    private $clubId;
    private $federationId;
    private $subFederationId;
    private $clubType;
    private $subFedContactId = false;
    private $fedContactId = false;

    /**
     *
     * @var string default system language pre-$lang
     */
    private $defaultSystemLang;
    private $fieldDetails = array();
    private $editData = array();
    private $currentContactId;
    private $deletedFilesArray;
    private $contactName;
    private $deleteddragFiles = array();
    private $inlineEditFlag = 0;
    private $isConfirmed = 'NULL';
    private $changedData = array();
    private $isDraft = 0;
    private $contactType = 'Single person';
    private $trans = array();

    /* attribute ids */
    private $systemFields = array();
    private $systmPersonaBothlFields = array();
    private $invoiceCategory;
    private $correspondanceCategory;
    private $systemPersonal;
    private $systemCompany;
    private $systemCompanyLogo;
    private $systemTeamPicture;
    private $systemCommunityPicture;
    private $systemPrimaryEmail;
    private $systemFirstName;
    private $systemLastName;
    private $systemCompanyName;
    private $fedFields = array();
    private $subFedFields = array();
    private $clubFields = array();
    private $systemPrimaryEmailValue = '';
    private $isFedMember = false;
    private $previousMainContactType = '';
    private $previousMainContactId = '';
    private $isPrimaryEmailUpdated = false;
    private $now;
    private $systmPersonalFieldValues = array();
    private $changelogData = array();
    private $connectionlogData = array();

    /* contact edit/inline edit/internal edit/backend confirmation edited field's level */
    private $changedField;

    /* confirm data */
    private $confirmedBy;
    private $confirmedDate;
    private $isConfirmedVal;
    private $logClubId;

    /* Array fg_cm_contact table values */
    private $insertContactSet = array();
    private $insertContactDupSetValues = array();

    /* Array master_system table values */
    private $insertSystemSet = array();
    private $insertSystemSetValues = array();
    private $insertSystemDupSetValues = array();

    /* Array club own contact field values */
    private $insertClubSet = array();
    private $insertClubSetValues = array();
    private $insertClubDupSetValues = array();

    /* Array subfederation contact field values */
    private $insertSubFedSet = array();
    private $insertSubFedSetValues = array();
    private $insertSubFedDupSetValues = array();
    private $insertSubFedQuery = '';

    /* Array federation contact field values */
    private $insertFedSet = array();
    private $insertFedSetValues = array();
    private $insertFedDupSetValues = array();
    private $insertFedQuery = '';

    /* membership */
    private $membershipLog = false;
    private $updateMembershipHistory = '';
    private $insertMembershipHistory = '';
    private $fgCmMembershipLogEntry = array();
    private $mode = 'create';
    private $sameAsInvoice = 0;
    private $mainContactType = 'manual';
    private $mainContactId = '';
    private $isUpdated = false;
    private $isSponsorContact = false;
    private $updateSponsor = false;
    private $isContactSwitched = false;
    public $saveFedMemId = false;
    private $clubEntries = array();
    private $allClubEntries = array();
    public  $overviewSave = 0;

    /* end membership */

    /**
     * Constructor for initial setting.
     *
     * @param type $container   container
     */
    public function __construct($container, $fieldDetails, $editData, $contactId = false)
    {

        $this->container = $container;
        $this->em = $this->container->get('doctrine')->getManager();
        $this->conn = $this->em->getConnection();
        $this->translator = $container->get('translator');
        $this->terminology = $container->get('fairgate_terminology_service');

        $this->contactId = $contactId;
        $this->fieldDetails = $fieldDetails;
        $this->editData = $editData;

        $this->setClassVariables();
    }

    /**
     * Main function to save contact fields
     *
     * @param array  $formValues
     * @param array  $deletedFilesArray
     * @param array  $otherformFields
     * @param array  $deleteddragFiles
     * @param int    $inlineEditFlag
     * @param int    $isConfirmed
     * @param string $changedData
     * @param int    $isDraft
     * @param int    $isInternal  executed from internal or backend
     *
     * @return int contactId
     */
    public function saveContact($formValues, $deletedFilesArray, $otherformFields = array(), $deleteddragFiles = array(), $inlineEditFlag = 0, $isConfirmed = 'NULL', $changedData = array(), $isDraft = 0, $isInternal = 0)
    {
        $this->formValues = $formValues;
        $this->deletedFilesArray = $deletedFilesArray;
        $this->deleteddragFiles = $deleteddragFiles;
        $this->inlineEditFlag = $inlineEditFlag;
        $this->isConfirmed = $isConfirmed;
        $this->changedData = $changedData;
        $this->isDraft = $isDraft;
        $this->contactType = ($inlineEditFlag == 1) ? (($this->editData[0]['is_company']) ? 'Company' : 'Single person') : 'Single person';
        $this->setConfirmedValues();
        if($this->inlineEditFlag == 0){
            $this->formValues = $this->setCheckboxFields($this->formValues, $this->fieldDetails);
        }
        if (count($otherformFields) > 0) {
            $systemdata['system'] = $otherformFields;
            $this->formValues = $systemdata + $this->formValues;
            $this->unsetProfilePicture();
        }
        if ($this->overviewSave == 1){
            if(!isset($this->formValues['system']['contactType'])){
                 $this->formValues['system']['contactType'] = ($this->editData[0]['is_company']) ? 'Company' : 'Single person';
            }
        }
        $this->setCreateEditBase();
        $this->setDefaultLogForCreate();
        $this->formValueItrator();
        $this->handleSameAs();
        $this->insertContactDetails($isInternal);

        return $this->contactId;
    }

    /**
     * Method to add deselected checkbox field Ids to $formValues.
     * If a checkbox is deselected all options and save, it will not contain in $formValues and that will not be saved.
     * This method will overcome that issue.
     *
     * @param array $formValues   formValues
     * @param array $fieldDetails fieldDetails
     *
     * @return array $formValues (with checkbox fileds added)
     */
    private function setCheckboxFields($formValues, $fieldDetails)
    {
        $catIds = array_keys($fieldDetails['fieldsArray']);
        foreach ($catIds as $catId) {
            $checkFields = array_filter($fieldDetails['fieldsArray'][$catId]['values'], function($v) {
                return $v['inputType'] == 'checkbox';
            });
            foreach (array_keys($checkFields) as $checkFieldId) {
                if (!isset($formValues[$catId][$checkFieldId])) {
                    $formValues[$catId][$checkFieldId] = '';
                }
            }
        }

        return $formValues;
    }

    /**
     * Function to itrate over form value array to set Query
     */
    private function formValueItrator()
    {
        foreach ($this->formValues as $category => $fields) {
            foreach ($fields as $field => $value) {
                $this->changedDate = ($this->isConfirmed == '1') ? $this->changedData[$category][$field]['changed_date'] : $this->now;
                $this->changedBy = ($this->isConfirmed == '1') ? $this->changedData[$category][$field]['changed_by'] : $this->currentContactId;
                $this->oldValue = '';
                switch ($field) {
                    case 'contactType':
                        $this->handleContactType($value);
                        break;
                    case 'attribute':
                        if (is_array($value)) {
                            $this->handleAttributes($value);
                        }
                        break;
                    case 'mainContact':
                        if ($this->contactType == 'Company') {
                            $this->handleMainContactOfCompany($value);
                        }
                        break;
                    case 'same_invoice_address':
                        $this->isUpdated = true;
                        $sameInvoiceAd = (!$this->contactId ? '' : $this->editData[0]['same_invoice_address']);
                        if ($value != $sameInvoiceAd) {
                            $this->changedField = "fed";
                        }
                        if ($value == 1) {
                            $this->sameAsInvoice = 1;
                            $this->allClubEntries['same_invoice_address'] = "1";
                        } else {
                            $this->allClubEntries['same_invoice_address'] = "0";
                        }
                        break;
                    case 'has_main_contact_address':
                        $oldMainAddress = (!$this->contactId ? '' : $this->editData[0]['has_main_contact_address']);
                        if ($value == 1) {
                            if ($oldMainAddress != '1') {
                                $this->allClubEntries['has_main_contact_address'] = "1";
                                $this->changedField = "fed";
                                $this->setContactChangeLog('@fedcontactId', '', 'data', $this->trans['addressBlock'], '', $this->trans['withMainContact'], '', 'NULL', '', '', '', $this->federationId);
                            }
                        } else {
                            if ($oldMainAddress != '0') {
                                $this->allClubEntries['has_main_contact_address'] = "0";
                                $this->changedField = "fed";
                                $this->setContactChangeLog('@fedcontactId', '', 'data', $this->trans['addressBlock'], '', $this->trans['withOutMainContact'], '', 'NULL', '', '', '', $this->federationId);
                            }
                        }
                        break;
                    case 'mainContactName':
                        if ($this->mainContactType == 'existing' && !empty($value)) {
                            $mainContact = $this->conn->fetchAll("SELECT fed_contact_id FROM fg_cm_contact WHERE id='$value'");
                            $this->mainContactId = $mainContact[0]['fed_contact_id'];
                            $this->allClubEntries['comp_def_contact'] = "'{$mainContact[0]['fed_contact_id']}'";
                            $oldCompanydefContact = (!$this->contactId ? '' : $this->editData[0]['comp_def_contact']);
                            if ($value != $oldCompanydefContact) {
                                $this->changedField = "fed";
                                $this->em->getRepository('CommonUtilityBundle:FgCmContact')->updateLastUpdated($mainContact[0]['fed_contact_id'], 'fedContact');
                            }
                            $this->handleMainContactName($mainContact[0]['fed_contact_id'], $fields);
                        }
                        break;
                    case 'mainContactFunction':
                        if ($this->mainContactType == 'existing') {
                            $this->allClubEntries['comp_def_contact_fun'] = "'$value'";
                            $oldCompanydefFun = (!$this->contactId ? '' : $this->editData[0]['comp_def_contact_fun']);
                            if ($value != $oldCompanydefFun) {
                                $this->changedField = "fed";
                            }
                        }
                        break;
                    case 'membership':
                        $value = ($value == 'default') ? '' : $value;
                        $this->handleClubMembership($value);
                        break;
                    case 'fedMembership':
                        $this->formValues[$category]['fedMembership'] = ($value == 'default') ? '' : $value;
                        $oValue = (!$this->contactId ? '' : $this->editData[0]['fed_membership_cat_id']);
                        $oldValue = (is_null($oValue) || $oValue == '') ? '' : $oValue;
                        if ($oldValue != $value) {
                            $this->saveFedMemId = true;
                        }
                        break;
                    case 'dragFiles':
                        $this->handleDragFiles($value);
                        break;
                    default:
                        $this->handleContactFields($category, $field, $value);
                        break;
                }
            }
        }
    }

    /**
     * Function to insert contact details into tables with build values
     *
     * @throws \Clubadmin\ContactBundle\Util\Exception
     */
    private function insertContactDetails($isInternal)
    {
        if (count($this->insertContactSet) > 0 || count($this->insertSystemSet) > 0 || count($this->insertFedSet) > 0 || count($this->insertSubFedSet) > 0 || count($this->insertClubSet) > 0) {
            if ($this->contactId) {
                $this->conn->executeQuery("SELECT '$this->contactId' INTO @contactId;SELECT '$this->fedContactId' INTO @fedcontactId;SELECT '$this->subFedContactId' INTO @subfedcontactId;");
            }
            if ($this->isContactSwitched) {
                $isComp = ($this->contactType == 'Single person') ? '0' : '1';
                $this->conn->executeQuery("UPDATE fg_cm_contact SET is_company=$isComp WHERE fed_contact_id=$this->fedContactId");
                $this->em->getRepository('CommonUtilityBundle:FgCmLinkedcontact')->deleteAllConnections($this->club, $this->fedContactId, $this->contactType, $this->container, $this->defaultSystemLang, $this->now);
                $this->em->getRepository('CommonUtilityBundle:FgCmAttribute')->insertContactSwitchingLog($this->club, $this->fedContactId, $this->conn, $this->now, $this->contactType);
            }
            try {
                $this->conn->beginTransaction();
                $this->insertContactSet();
                if ($this->mode == 'create') {
                    if ($this->federationId && $this->clubType != 'federation') {
                        $this->conn->executeQuery("CALL updateMemberId(" . $this->federationId . ")");
                    }
                    if ($this->subFederationId && $this->clubType != 'sub_federation') {
                        $this->conn->executeQuery("CALL updateMemberId(" . $this->subFederationId . ")");
                    }
                    $this->conn->executeQuery("CALL updateMemberId(" . $this->clubId . ")");
                }
                $this->insertSystemFields();
                $this->insertFedFields();
                $this->insertSubFedFields();
                $this->insertContactLogs();
                $this->insertClubFields();
                $this->insertMembershipLog();
                $this->em->getRepository('CommonUtilityBundle:FgCmAttribute')->deleteSubscribers('@contactId', $this->contactClubId, $this->clubId, $this->container);
                if (count($this->systmPersonalFieldValues) > 0) {
                    $this->em->getRepository('CommonUtilityBundle:FgCmAttribute')->updateLinkedContactDetails($this->conn, $this->contactClubId, $this->systmPersonalFieldValues);
                }
                if ($this->contactType == 'Company') {
                    $this->em->getRepository('CommonUtilityBundle:FgCmAttribute')->updateMainContactDetails($this->container, $this->mainContactId, $this->contactClubId, $this->mainContactType, $this->previousMainContactType, $this->previousMainContactId, $this->now, $this->currentContactId);
                }
                // Insert Sponsor Ads for newly created sponsors.
                if ($this->isSponsorContact) {
                    $newContact = $this->conn->fetchAll('SELECT @contactId');
                    $newContactId = $newContact[0]['@contactId'];
                    $this->em->getRepository('CommonUtilityBundle:FgSmSponsorAds')->insertDefaultSponsorAds($this->clubId, $newContactId);
                }
                $this->conn->commit();
                if ($isInternal == 0 && $this->saveFedMemId) {
                    //Assign/Change/Remove Fed memebership handling
                    $fgFedMembershipObj = new FgFedMemberships($this->container);
                    $fgFedMembershipObj->processFedMembership($this->contactId, $this->formValues['system']['fedMembership']);
                }
            } catch (Exception $ex) {
                $this->conn->rollback();
//                throw $ex;
            }

                $pdo = new ContactPdo($this->container);
                $pdo->insertIntoSfguardUser($this->fedContactId);
        }
    }

    /**
     * Function to insert/update into fg_cm_contact
     */
    private function insertContactSet()
    {
        if (count($this->insertContactSet) > 0) {
            if (count($this->changelogData) > 0 || $this->isUpdated) {
                //$this->setInsertContactArray(array('last_updated'=>"'$this->now'"));
                if ($this->changedField == 'fed') {
                    $this->allClubEntries['last_updated'] = "'$this->now'";
                } else if ($this->changedField == 'club') {
                    $this->clubEntries['last_updated'] = "'$this->now'";
                }
            }
            if ($this->formValues['merging'] == 'save') {
                $this->setInsertContactArray(array('merge_to_contact_id' => "'{$this->formValues['mergeTo']}'", 'allow_merging' => '1'));
            }
            if ($this->mode == 'create') {
                $this->setInsertContactArray(array('club_id' => '#CLUBID#', 'created_club_id' => $this->clubId, 'main_club_id' => $this->clubId, 'is_subscriber' => "(SELECT default_contact_subscription FROM fg_club WHERE id=#CLUBID# )"));
                if (count($this->allClubEntries) > 0) {
                    $this->setInsertContactArray($this->allClubEntries);
                }
                if ($this->federationId && $this->clubType != 'federation') {
                    $insertContactQuery = 'INSERT INTO fg_cm_contact SET ' . implode(',', $this->insertContactSet) . ' ON DUPLICATE KEY UPDATE ' . implode(',', $this->insertContactDupSetValues);
                    $insertContactQuery = str_replace('#CLUBID#', $this->federationId, $insertContactQuery);
                    $this->conn->executeQuery($insertContactQuery, $this->insertClubSetValues);
                    $this->fedContactId = $this->conn->lastInsertId();
                    $this->conn->executeQuery("UPDATE fg_cm_contact SET fed_contact_id='$this->fedContactId' WHERE id=$this->fedContactId");
                    $this->addNewsletterSubscriptionLog("@fedcontactId", $this->federationId);
                }
                if ($this->fedContactId) {
                    $this->setInsertContactArray(array('fed_contact_id' => $this->fedContactId));
                }
                if ($this->subFederationId && $this->clubType != 'sub_federation') {
                    $insertContactQuery = 'INSERT INTO fg_cm_contact SET ' . implode(',', $this->insertContactSet) . ' ON DUPLICATE KEY UPDATE ' . implode(',', $this->insertContactDupSetValues);
                    $insertContactQuery = str_replace('#CLUBID#', $this->subFederationId, $insertContactQuery);
                    $this->conn->executeQuery($insertContactQuery, $this->insertClubSetValues);
                    $this->subFedContactId = $this->conn->lastInsertId();
                    $this->conn->executeQuery("UPDATE fg_cm_contact SET subfed_contact_id='$this->subFedContactId' WHERE id=$this->subFedContactId");
                    $this->addNewsletterSubscriptionLog("@subfedcontactId", $this->subFederationId);
                }
                if ($this->subFedContactId) {
                    $this->setInsertContactArray(array('subfed_contact_id' => $this->subFedContactId));
                }
                if ($this->updateSponsor != false) {
                    $this->setInsertContactArray(array('is_sponsor' => "$this->updateSponsor"));
                    $valueBefore = $this->updateSponsor == '1' ? '' : $this->translator->trans('SPONSOR');
                    $valueAfter = $this->updateSponsor == '0' ? '' : $this->translator->trans('SPONSOR');
                    $this->setContactChangeLog('@contactId', '', 'contact type', '', $valueBefore, $valueAfter, '', '', '', '', '', $this->clubId);
                }
                if (count($this->clubEntries) > 0) {
                    $this->setInsertContactArray($this->clubEntries);
                }
                $insertContactQuery = 'INSERT INTO fg_cm_contact SET ' . implode(',', $this->insertContactSet) . ' ON DUPLICATE KEY UPDATE ' . implode(',', $this->insertContactDupSetValues);
                $insertContactQuery = str_replace('#CLUBID#', $this->clubId, $insertContactQuery);
                $this->conn->executeQuery($insertContactQuery, $this->insertClubSetValues);
                $this->contactId = $this->conn->lastInsertId();
                //for standared club set contact id as fed_contact_id
                if (!$this->fedContactId) {
                    $this->fedContactId = "$this->contactId";
                    $this->conn->executeQuery("UPDATE fg_cm_contact SET fed_contact_id='$this->contactId' WHERE id=$this->contactId");
                }
                if ($this->clubType == 'sub_federation') {
                    $this->conn->executeQuery("UPDATE fg_cm_contact SET subfed_contact_id='$this->contactId' WHERE id=$this->contactId");
                }
                $this->addNewsletterSubscriptionLog("@contactId", $this->clubId);
            } else {
                if (count($this->clubEntries) > 0) {
                    $this->setInsertContactArray($this->clubEntries);
                }
                if ($this->changedField == 'subfed') {
                    $this->conn->executeQuery("UPDATE fg_cm_contact SET last_updated='$this->now' WHERE subfed_contact_id=$this->subFedContactId");
                }
                if ($this->contactId) {
                    $this->conn->executeQuery("SELECT '$this->contactId' INTO @contactId;SELECT '$this->fedContactId' INTO @fedcontactId;SELECT '$this->subFedContactId' INTO @subfedcontactId;");
                }
                $insertContactQuery = 'INSERT INTO fg_cm_contact SET ' . implode(',', $this->insertContactSet) . ' ON DUPLICATE KEY UPDATE ' . implode(',', $this->insertContactDupSetValues);
                $this->conn->executeQuery($insertContactQuery);
                if (count($this->allClubEntries) > 0 && $this->fedContactId) {
                    $this->insertContactSet = array();
                    $this->setInsertContactArray($this->allClubEntries);
                    $this->conn->executeQuery("UPDATE fg_cm_contact SET " . implode(',', $this->insertContactSet) . " WHERE fed_contact_id={$this->fedContactId}");
                }
            }
            if ($this->mode == 'create') {
                $this->conn->executeQuery('SELECT LAST_INSERT_ID() INTO @contactId; SELECT "' . $this->fedContactId . '" INTO @fedcontactId;SELECT ' . $this->subFedContactId . ' INTO @subfedcontactId;');
            }
        }
    }

    /**
     * Function to insert system field
     */
    private function insertSystemFields()
    {
        if (count($this->insertSystemSet) > 0) {
            $this->insertSystemSet[] = "fed_contact_id = '$this->fedContactId'";
            $this->insertSystemDupSetValues[] = '`fed_contact_id` = VALUES(`fed_contact_id`)';
            if ($this->mode == 'create') {
                $this->insertSystemSet[] = "id = ''";
                $this->insertSystemQuery = 'INSERT INTO master_system SET ' . implode(',', $this->insertSystemSet) . ' ON DUPLICATE KEY UPDATE ' . implode(',', $this->insertSystemDupSetValues);
                $this->conn->executeQuery($this->insertSystemQuery, $this->insertSystemSetValues);
            } elseif ($this->fedContactId) {
                $this->insertSystemQuery = 'UPDATE master_system SET ' . implode(',', $this->insertSystemSet) . ' WHERE fed_contact_id=' . $this->fedContactId;
                $this->conn->executeQuery($this->insertSystemQuery, $this->insertSystemSetValues);
            }
        }
    }

    /**
     * Function to insert Federation contact fields to federation table
     */
    private function insertFedFields()
    {
        if ($this->federationId != 0) {
            $this->insertFedSet[] = "club_id = '$this->federationId'";
            $this->insertFedDupSetValues[] = '`club_id` = VALUES(`club_id`)';
            $this->insertFedSet[] = "fed_contact_id = $this->fedContactId";
            $this->insertFedDupSetValues[] = '`fed_contact_id` = VALUES(`fed_contact_id`)';
            if ($this->mode == 'edit') {
                $this->insertFedQuery = "UPDATE master_federation_{$this->federationId} SET " . implode(',', $this->insertFedSet) . " WHERE fed_contact_id = $this->fedContactId";
            } else {
                $this->insertFedQuery = "INSERT INTO master_federation_{$this->federationId} SET " . implode(',', $this->insertFedSet) . ' ON DUPLICATE KEY UPDATE ' . implode(',', $this->insertFedDupSetValues);
            }
            $this->conn->executeQuery($this->insertFedQuery, $this->insertFedSetValues);
        }
    }

    /**
     * Function to insert Sub federation contact fields to sub federation table
     */
    private function insertSubFedFields()
    {
        if ($this->subFederationId != 0) {
            $this->insertSubFedSet[] = "club_id = '$this->subFederationId'";
            $this->insertSubFedDupSetValues[] = '`club_id` = VALUES(`club_id`)';
            $this->insertSubFedSet[] = "contact_id = $this->subFedContactId";
            $this->insertSubFedDupSetValues[] = '`contact_id` = VALUES(`contact_id`)';
            if ($this->mode == 'edit') {
                $this->insertSubFedQuery = "UPDATE master_federation_{$this->subFederationId} SET " . implode(',', $this->insertSubFedSet) . " WHERE contact_id = $this->subFedContactId";
            } else {
                $this->insertSubFedQuery = "INSERT INTO master_federation_{$this->subFederationId} SET " . implode(',', $this->insertSubFedSet) . ' ON DUPLICATE KEY UPDATE ' . implode(',', $this->insertSubFedDupSetValues);
            }
            $this->conn->executeQuery($this->insertSubFedQuery, $this->insertSubFedSetValues);
        }
    }

    /**
     * Function to insert club contact fields to club table
     */
    private function insertClubFields()
    {
        $field = ($this->clubType == 'federation') ? 'fed_contact_id' : 'contact_id';
        $this->insertClubSet[] = "$field =@contactId";
        $this->insertClubDupSetValues[] = "`$field` = VALUES(`$field`)";
        $insertClubTable = "master_club_$this->clubId";
        if ($this->clubType == 'federation' || $this->clubType == 'sub_federation') {
            $insertClubTable = "master_federation_$this->clubId";
            $this->insertClubSet[] = "club_id = '$this->clubId'";
            $this->insertClubDupSetValues[] = '`club_id` = VALUES(`club_id`)';
        }
        if ($this->mode == 'edit') {
            $insertClubQuery = 'UPDATE ' . $insertClubTable . ' SET ' . implode(',', $this->insertClubSet) . " WHERE $field = @contactId";
        } else {
            $insertClubQuery = 'INSERT INTO ' . $insertClubTable . ' SET ' . implode(',', $this->insertClubSet) . ' ON DUPLICATE KEY UPDATE ' . implode(',', $this->insertClubDupSetValues);
        }
        $this->conn->executeQuery($insertClubQuery, $this->insertClubSetValues);
    }

    /**
     * Function to insert contact Log entries
     */
    private function insertContactLogs()
    {
        if (count($this->changelogData) > 0) {
            $logObj = new FgLogHandler($this->container);
            $logType = ($this->isConfirmed == 1) ? 'contact_field_confirm' : 'contact_field';
            $logObj->processLogEntryAction($logType, 'fg_cm_change_log', $this->changelogData);
        }
        if (count($this->connectionlogData) > 0) {
            $this->connectionlogDataQuery = 'INSERT INTO fg_cm_log_connection(contact_id, linked_contact_id, assigned_club_id, date, connection_type, relation, value_before, value_after, changed_by,type) VALUES' . implode(',', $this->connectionlogData);
            $this->conn->executeQuery($this->connectionlogDataQuery);
        }
    }

    /**
     * Function to insert club membership log
     */
    private function insertMembershipLog()
    {
        if ($this->membershipLog) {
            $cntactId = '@contactId';
            if ($this->insertMembershipHistory != '') {
                $membershipLogHistoryQuery = "INSERT INTO fg_cm_membership_history (contact_id,changed_by,membership_club_id,membership_id,membership_type,joining_date) VALUES ( $cntactId, " . $this->insertMembershipHistory . ",'" . $this->now . "')";
                $this->conn->executeQuery($membershipLogHistoryQuery);
            }
            if ($this->updateMembershipHistory != '') {
                $membershipLogUpdateHistory = "UPDATE `fg_cm_membership_history` SET leaving_date = '$this->now',changed_by = '$this->currentContactId' WHERE contact_id = " . $cntactId . " AND $this->updateMembershipHistory AND leaving_date IS NULL";
                $this->conn->executeQuery($membershipLogUpdateHistory);
            }
            if (!empty($this->fgCmMembershipLogEntry)) {
                $sql = 'INSERT INTO fg_cm_membership_log (club_id, contact_id, membership_id, date, kind, field, value_before, value_after, changed_by) VALUES ' . implode(',', $this->fgCmMembershipLogEntry);
                $this->conn->executeQuery($sql);
            }
        }
    }

    /**
     * Function Handle 'Same for invoice address'
     */
    private function handleSameAs()
    {
        if($this->inlineEditFlag == 1){
            $this->sameAsInvoice = $this->editData[0]['same_invoice_address'];
        }
        if ($this->sameAsInvoice == 1) {
            $fieldsArray = $this->fieldDetails['fieldsArray'][$this->invoiceCategory]['values'];
            foreach ($fieldsArray as $field => $fieldRow) {
                $inputType = $this->fieldDetails['fieldsArray'][$this->invoiceCategory]['values'][$field]['inputType'];
                if (!is_null($fieldRow['addressId']) && $fieldRow['addressId'] != '' && isset($this->formValues[$this->correspondanceCategory][$fieldRow['addressId']])) {
                    $value = $this->formValues[$this->correspondanceCategory][$fieldRow['addressId']];
                    $valueBefore = (!$this->contactId ? '' : FgUtility::getSecuredData($this->editData[0][$fieldRow['addressId']], $this->conn));
                    if ($inputType == 'date') {
                        $valueBefore = ($valueBefore == '0000-00-00' ? '' : $valueBefore);
                    }
                    if (in_array($field, $this->systemFields)) {
                        $contact = '@fedcontactId';
                        $club = $this->federationId;
                        $this->insertSystemSet[] = "`$field` = '$value'";
                        $this->insertSystemDupSetValues[] = "`$field` = VALUES(`$field`)";
                    } elseif (in_array($field, $this->fedFields)) {
                        $contact = '@fedcontactId';
                        $club = $this->federationId;
                        $this->insertFedSet[] = "`$field` = '$value'";
                        $this->insertFedDupSetValues[] = "`$field` = VALUES(`$field`)";
                    } elseif (in_array($field, $this->subFedFields)) {
                        $contact = '@subfedcontactId';
                        $club = $this->subFederationId;
                        $this->insertSubFedSet[] = "`$field` = '$value'";
                        $this->insertSubFedDupSetValues[] = "`$field` = VALUES(`$field`)";
                    } elseif (in_array($field, $this->clubFields)) {
                        $contact = '@contactId';
                        $club = $this->clubId;
                        $this->insertClubSet[] = "`$field` = '$value'";
                        $this->insertClubDupSetValues[] = "`$field` = VALUES(`$field`)";
                    }
                    if ($value != $valueBefore) {
                        $fieldName = $this->fieldDetails['fieldsArray'][$this->correspondanceCategory]['values'][$fieldRow['addressId']]['fieldname'];
                        $this->setContactChangeLog($contact, '', 'data', $fieldName . ' (Rg.)', $valueBefore, $value, '', $field, '', '', '', $this->clubId);
                    }
                }
            }
        }
    }

    /**
     * Handle club membership
     * @param type $value
     */
    private function handleClubMembership($value)
    {
        $oValue = (!$this->contactId ? '' : $this->editData[0]['club_membership_cat_id']);
        $oldValue = (is_null($oValue) || $oValue == '') ? '' : $oValue;
        if ($oldValue != $value) {
            $this->membershipLog = true;
            if ($value != '') {
                $newValue = $value;
                $this->clubEntries['club_membership_cat_id'] = "'$value'";
                if ($this->mode == 'create') {
                    $this->clubEntries['first_joining_date'] = 'NOW()';
                } elseif ($this->contactId) {
                    $membersipFields = $this->em->getRepository('CommonUtilityBundle:FgCmMembershipHistory')->getMembershipHistory($this->contactId, 'club');
                    if (count($membersipFields) == 0) {
                        $this->clubEntries['first_joining_date'] = 'NOW()';
                    }
                }
                if ($oldValue == '') {
                    $this->clubEntries['joining_date'] = 'NOW()';
                    $this->clubEntries['leaving_date'] = "'0000-00-00 00:00:00'";
                } else {
                    //oldvalue value
                    $this->updateMembershipHistory = "membership_id = '$oldValue'";
                    $this->fgCmMembershipLogEntry[] = "('$this->clubId',@contactId,'$oldValue','$this->now','assigned contacts','','$this->contactName','-','$this->currentContactId')";
                }

                $this->insertMembershipHistory = "'$this->currentContactId','$this->clubId','$newValue','club'";
                $this->fgCmMembershipLogEntry[] = "('$this->clubId',@contactId,'$newValue','$this->now','assigned contacts','','-','$this->contactName','$this->currentContactId')";
            } else {
                if ($oldValue != '') {
                    $this->updateMembershipHistory = "membership_id = '$oldValue'";
                    $this->fgCmMembershipLogEntry[] = "('$this->clubId',@contactId,'$oldValue','$this->now','assigned contacts','','$this->contactName','-','$this->currentContactId')";
                    $this->clubEntries['club_membership_cat_id'] = "NULL";
                    $this->clubEntries['leaving_date'] = 'NOW()';
                }
            }
            $this->isUpdated = true;
            $this->changedField = "club";
        }
    }

    /**
     * Function to set club/fed/sub fed contact fields
     *
     * @param String $category
     * @param String $field
     * @param String $value
     */
    private function handleContactFields($category, $field, $value)
    {
        $inputType = '';
        $valueBefore = (!$this->contactId ? '' : FgUtility::getSecuredData($this->editData[0][$field], $this->conn));
        $value = (is_array($value) ? implode(';', $value) : ($value != '' ? trim($value) : ''));
        if ($this->contactId && ($this->contactType == 'Single person') && in_array($field, $this->systmPersonaBothlFields)) {
            $this->systmPersonalFieldValues[$field] = $value;
        }
        if ($this->contactType == 'Company') {
            if ($this->mainContactType == 'no' && in_array($field, $this->systmPersonaBothlFields)) {
                $value = '';
            } elseif ($this->mainContactType == 'existing' && in_array($field, $this->systmPersonaBothlFields)) {
                return;
            }
        }
        $value = FgUtility::getSecuredData($value, $this->conn);
        if ($field == $this->systemPrimaryEmail) {
            $this->systemPrimaryEmailValue = $value;
            $this->isPrimaryEmailUpdated = true;
        }
        if (isset($this->fieldDetails['fieldsArray'][$category]['values'][$field])) {
            $inputType = $this->fieldDetails['fieldsArray'][$category]['values'][$field]['inputType'];
            if ($inputType == 'date') {
                $valueBefore = ($valueBefore == '0000-00-00' ? '' : $valueBefore);
                if ($value != '') {
                    if (date_create_from_format('Y-m-d', $value)) {
                        $value = $value;
                    } else {
                        $date = new \DateTime();
                        $value = $date->createFromFormat(FgSettings::getPhpDateFormat(), $value)->format('Y-m-d');
                    }
                }
            } elseif ($this->contactId && $this->editData[0][$field] != '' && ($inputType == 'imageupload' || $inputType == 'fileupload')) {

                $uploadedPath = $this->container->get('fg.avatar')->getContactfieldPath($field, false) . '/';

                if (in_array($field, $this->deletedFilesArray)) {
                    if (is_file($uploadedPath . $this->editData[0][$field])) {
                        unlink($uploadedPath . $this->editData[0][$field]);
                    }
                    $value = '';
                } elseif ($value != $this->editData[0][$field]) {
                    if ($value != '') {
                        if (is_file($uploadedPath . $this->editData[0][$field])) {
                            unlink($uploadedPath . $this->editData[0][$field]);
                        }
                    } else {
                        $value = $this->editData[0][$field];
                    }
                }
            } elseif ($inputType == 'number') {
                $value = str_replace(FgSettings::getDecimalMarker(), '.', $value);
            }
        }
        // To enter log entry Existing main contact changed to main manul contact
        $valueBefore = (($this->previousMainContactType == 'existing') && in_array($field, $this->systmPersonaBothlFields)) ? '' : $valueBefore;
        $value = (($this->previousMainContactType == 'manual' && $this->mainContactType == 'no') && in_array($field, $this->systmPersonaBothlFields)) ? '' : $value;

        if (in_array($field, $this->systemFields)) {
            $contact = '@fedcontactId';
            $club = $this->federationId ? $this->federationId : $this->clubId;
            $this->insertSystemSet[] = "`$field` = '$value'";
            $this->insertSystemDupSetValues[] = "`$field` = VALUES(`$field`)";
            if ($value != $valueBefore) {
                $this->changedField = "fed";
            }
        } elseif (in_array($field, $this->fedFields)) {
            $contact = '@fedcontactId';
            $club = $this->federationId ? $this->federationId : $this->clubId;
            $this->insertFedSet[] = "`$field` = '$value'";
            $this->insertFedDupSetValues[] = "`$field` = VALUES(`$field`)";
            if ($value != $valueBefore) {
                $this->changedField = "fed";
            }
        } elseif (in_array($field, $this->subFedFields)) {
            $contact = '@subfedcontactId';
            $club = $this->subFederationId;
            $this->insertSubFedSet[] = "`$field` = '$value'";
            $this->insertSubFedDupSetValues[] = "`$field` = VALUES(`$field`)";
            if ($value != $valueBefore) {
                $this->changedField = "subfed";
            }
        } elseif (in_array($field, $this->clubFields)) {
            $contact = '@contactId';
            $club = $this->clubId;
            $this->insertClubSet[] = "`$field` = '$value'";
            $this->insertClubDupSetValues[] = "`$field` = VALUES(`$field`)";
            if ($value != $valueBefore) {
                $this->changedField = "club";
            }
        }
        if ($value != $valueBefore) {
            $fieldName = $this->fieldDetails['fieldsArray'][$category]['values'][$field]['fieldname'] . ($category == $this->correspondanceCategory ? ' (Korr.)' : '') . ($category == $this->invoiceCategory ? ' (Rg.)' : '');
            $this->setContactChangeLog($contact, '', 'data', $fieldName, $valueBefore, $value, '', $field, '', '', '', $this->clubId);
        }
        $this->formValues[$category][$field] = $value;
    }

    /**
     * Function to handle main contact name
     *
     * @param String $value
     * @param array $fields
     */
    private function handleMainContactName($value, $fields)
    {
        $oldCompanydefContact = (!$this->contactId ? '' : $this->editData[0]['comp_def_contact']);
        if ($value != $oldCompanydefContact) {
            if ($oldCompanydefContact == '') {
                $this->previousMainContactType = ($this->editData[0]['has_main_contact'] == 1) ? 'manual' : 'no';
            } else {
                $this->previousMainContactType = 'existing';
                $oldCompanydefFun = FgUtility::getSecuredData($this->editData[0]['comp_def_contact_fun'], $this->conn);
                $olddefContactName = $this->em->getRepository('CommonUtilityBundle:FgCmAttribute')->getContactName($oldCompanydefContact);
                $defContactName = FgUtility::getSecuredData($olddefContactName, $this->conn);
                $this->connectionlogData[] = "('$oldCompanydefContact', @fedcontactId, NULL, '$this->now', '{$this->trans['mainContactOf']}', '$oldCompanydefFun', '$this->contactName', '', '$this->currentContactId','global')";
                $this->connectionlogData[] = "(@fedcontactId, '$oldCompanydefContact', NULL, '$this->now', '{$this->trans['mainContact']}', '$oldCompanydefFun', '$defContactName', '', '$this->currentContactId','global')";
            }
            $olddefContactName = $this->em->getRepository('CommonUtilityBundle:FgCmAttribute')->getContactName($value);
            $defContactName = FgUtility::getSecuredData($olddefContactName, $this->conn);
            $this->connectionlogData[] = "(@fedcontactId,'$value', NULL, '$this->now', '{$this->trans['mainContact']}', '{$fields['mainContactFunction']}', '','$this->contactName', '$this->currentContactId','global')";
            $this->connectionlogData[] = "('$value',@fedcontactId, NULL, '$this->now', '{$this->trans['mainContactOf']}', '{$fields['mainContactFunction']}', '','$this->contactName', '$this->currentContactId','global')";
        }
    }

    /**
     * Function to handle main contact of company contact
     *
     * @param type $value
     */
    private function handleMainContactOfCompany($value)
    {
        $oldHasMainContact = (!$this->contactId ? '' : $this->editData[0]['has_main_contact']);
        $oldCompanydefContact = (!$this->contactId ? '' : $this->editData[0]['comp_def_contact']);
        switch ($value) {
            case 'noMain':
                $this->mainContactType = 'no';
                $this->allClubEntries += array('has_main_contact' => '0', 'comp_def_contact' => 'NULL', 'comp_def_contact_fun' => "''");
                $this->setConnectionLog($oldCompanydefContact, $oldHasMainContact);
                break;
            case 'withMain':
                $this->mainContactType = 'manual';
                $this->allClubEntries += array('has_main_contact' => '1', 'comp_def_contact' => 'NULL', 'comp_def_contact_fun' => "''");
                $this->setConnectionLog($oldCompanydefContact, $oldHasMainContact);
                break;
            case 'existing':
                $this->mainContactType = 'existing';
                $this->allClubEntries += array('has_main_contact' => '1');
                $this->setConnectionLog($oldCompanydefContact, $oldHasMainContact);
                break;
        }
    }

    /**
     * Function to handle attributes like 'is sponsor','intranet_access','is_stealth_mode'
     *
     * @param string $value
     */
    private function handleAttributes($value)
    {
        $oldIsSposor = (!$this->contactId ? '0' : $this->editData[0]['is_sponsor']);
        $oldCommunityStatus = (!$this->contactId ? '' : $this->editData[0]['intranet_access']);
        $oldStealthMode = (!$this->contactId ? '' : $this->editData[0]['is_stealth_mode']);
        if (in_array('Sponsor', $value)) {
            if ($oldIsSposor == '0') {
                $this->updateSponsor = '1';
                $this->isSponsorContact = true;
                $this->changedField = "club";
            }
        } else {
            //unset the value if value changed to 1 to 0,since form value not pass when unchecked
            if ($oldIsSposor == '1') {
                $this->updateSponsor = '0';
                $this->changedField = "club";
            }
        }
        if (in_array('Intranet access', $value)) {
            if ($oldCommunityStatus != '1') {
                $this->setInsertContactArray(array('intranet_access' => '1'));
                $valueAfter = $this->translator->trans('YES');
                $valueBefore = ($oldCommunityStatus == '0' ? $this->translator->trans('NO') : '');
                $this->setAttrbuteChangeLog('intranet access', $valueBefore, $valueAfter);
                $this->changedField = "club";
            }
        } else {
            //unset the value if value changed to 1 to 0,since form value not pass when unchecked
            if ($oldCommunityStatus == 1 || !$this->contactId) {
                $this->setInsertContactArray(array('intranet_access' => '0'));
                $valueBefore = $oldCommunityStatus == '1' ? $this->translator->trans('YES') : '';
                $valueAfter = $this->translator->trans('NO');
                $this->setAttrbuteChangeLog('intranet access', $valueBefore, $valueAfter);
                $this->changedField = "club";
            }
        }

        if (in_array('Stealth mode', $value)) {
            if ($oldStealthMode != '1') {
                $this->setInsertContactArray(array('is_stealth_mode' => '1'));
                $valueAfter = $this->translator->trans('YES');
                $valueBefore = ($oldStealthMode == '0' ? $this->translator->trans('NO') : '');
                $this->setAttrbuteChangeLog('stealth mode', $valueBefore, $valueAfter);
                $this->changedField = "club";
            }
        } else {
            //unset the value if value changed to 1 to 0,since form value not pass when unchecked
            if ($oldStealthMode == 1 || !$this->contactId) {
                $this->setInsertContactArray(array('is_stealth_mode' => '0'));
                $valueBefore = $oldStealthMode == '1' ? $this->translator->trans('YES') : '';
                $valueAfter = $this->translator->trans('NO');
                $this->setAttrbuteChangeLog('stealth mode', $valueBefore, $valueAfter);
                $this->changedField = "club";
            }
        }
    }

    /**
     * Function to handle contact Type
     * @param type $value
     */
    private function handleContactType($value)
    {
        $oldValue = (!$this->contactId ? '' : $this->editData[0]['is_company']);
        if ($this->contactId && (($this->editData[0]['is_company'] && $value == 'Single person') || ($this->editData[0]['is_company'] == 0 && $value == 'Company'))) {
            $this->isContactSwitched = true;
        }
        if ($value == 'Single person') {
            $this->contactType = $value;
            $this->allClubEntries += array('is_company' => '0', 'has_main_contact' => '0', 'comp_def_contact' => 'NULL', "comp_def_contact_fun" => "''");
            if ($oldValue != '0') {
                $value = $this->translator->trans('CM_SINGLE_PERSON');
                $valueBefore = ($oldValue == '') ? '' : $this->translator->trans('CM_COMPANY');
                $this->changedField = "fed";
                $this->setContactChangeLog('@fedcontactId', '', 'contact type', '', $valueBefore, $value, '', 'NULL', '', '', '', $this->federationId);
            }
        } elseif ($value == 'Company') {
            $this->contactType = $value;
            $this->allClubEntries += array('is_company' => '1');
            if ($oldValue != '1') {
                $value = $this->translator->trans('CM_COMPANY');
                $valueBefore = ($oldValue == '') ? '' : $this->translator->trans('CM_SINGLE_PERSON');
                $this->changedField = "fed";
                $this->setContactChangeLog('@fedcontactId', '', 'contact type', '', $valueBefore, $value, '', 'NULL', '', '', '', $this->federationId);
            }
        }
    }

    /**
     * Handle drag file uploads
     * @param array $value
     */
    private function handleDragFiles($value)
    {
        //only for data tab of contact
        if (isset($this->deleteddragFiles)) {
            foreach ($this->deleteddragFiles as $atrdrag) {
                $todeletefile = $this->editData[0][$atrdrag];
                $valueinsert = '';
                $this->deleteAllResizedFiles($todeletefile);
                $fieldName = $this->getFieldName($atrdrag);
                $this->setContactChangeLog('@fedcontactId', '', 'data', "$fieldName", $todeletefile, '', '', $atrdrag, '', '', '', $this->clubId);
                $this->insertSystemSet[] = "`$atrdrag` = '$valueinsert'";
                $this->insertSystemDupSetValues[] = "`$atrdrag` = VALUES(`$atrdrag`)";
            }
        }
        if (is_array($value)) {
            $valueinserted = '';
            foreach ($value as $atr => $val) {
                $oldValue = $this->editData[0][$atr];
                if ($val != $oldValue) {
                    $this->deleteAllResizedFiles($oldValue);
                    $fieldName = $this->getFieldName($atr);
                    $this->setContactChangeLog('@fedcontactId', '', 'data', "$fieldName", $oldValue, $val, '', $atr, '', '', '', $this->clubId);
                    $valueinserted = $val;
                } else {
                    $valueinserted = (in_array($atr, $this->deleteddragFiles)) ? '' : $oldValue;
                }
                $valueinserted = FgUtility::getSecuredData($valueinserted, $this->conn);
                $this->insertSystemSet[] = "`$atr` = '$valueinserted'";
                $this->insertSystemDupSetValues[] = "`$atr` = VALUES(`$atr`)";
            }
        }
    }

    /**
     * Function to delete all resized files from the folder
     */
    private function deleteAllResizedFiles($todeletefile)
    {
        $uploadPath = 'uploads/' . $this->contactClubId . '/contact';
        $uploadPath .= ($this->contactType == 'Company') ? '/companylogo' : '/profilepic';
        $resize = array('original', 'width_150', 'width_65');
        foreach ($resize as $folder) {
            $folder = "/$folder/";
            if (is_file($uploadPath . $folder . $todeletefile)) {
                unlink($uploadPath . $folder . $todeletefile);
            }
        }
    }

    /**
     * Function to set attribute change log for each level of club
     *
     * @param type $fieldType
     * @param type $valueBefore
     * @param type $valueAfter
     */
    public function setAttrbuteChangeLog($fieldType, $valueBefore, $valueAfter)
    {
        if (isset($this->formValues['system']['fedMembership']) && $this->mode == 'create') {
            if ($this->federationId > 0 && $this->clubType != 'federation') {
                $this->setContactChangeLog('@fedcontactId', '', 'system', $fieldType, $valueBefore, $valueAfter, '', 'NULL', '', '', '', $this->clubId);
            }
            if ($this->subFederationId > 0) {
                $this->setContactChangeLog('@subfedcontactId', '', 'system', $fieldType, $valueBefore, $valueAfter, '', 'NULL', '', '', '', $this->clubId);
            }
            $this->setContactChangeLog('@contactId', '', 'system', $fieldType, $valueBefore, $valueAfter, '', 'NULL', '', '', '', $this->clubId);
        } else {
            $this->setContactChangeLog('@contactId', '', 'system', $fieldType, $valueBefore, $valueAfter, '', 'NULL', '', '', '', $this->clubId);
        }
    }

    /**
     * Function to set contact change log
     *
     * @param int    $contactId
     * @param date   $changedDate
     * @param string $logType
     * @param string $fieldType
     * @param string $valueBefore
     * @param string $valueAfter
     * @param int    $changedBy
     * @param int    $attrId
     * @param string $isConfirmedVal
     * @param string $confirmedBy
     * @param date   $confirmedDate
     * @param int    $clubId
     */
    private function setContactChangeLog($contactId, $changedDate, $logType, $fieldType, $valueBefore, $valueAfter, $changedBy = '', $attrId = 'NULL', $isConfirmedVal = '', $confirmedBy = '', $confirmedDate = '', $clubId = '')
    {
        $clubId = ($clubId == '' || $clubId == 'NULL') ? $this->clubId : $clubId;
        $changedDate = ($changedDate == '') ? $this->changedDate : $changedDate;
        $changedBy = ($changedBy == '') ? $this->changedBy : $changedBy;
        $isConfirmedVal = ($isConfirmedVal == '') ? $this->isConfirmedVal : $isConfirmedVal;
        $confirmedBy = ($confirmedBy == '') ? $this->confirmedBy : $confirmedBy;
        $confirmedDate = ($confirmedDate == '') ? $this->confirmedDate : $confirmedDate;
        //array(0 => 'date', 1 => 'kind', 2 => 'field', 3 => 'value_before', 4 => 'value_after', 5 => 'changed_by', 6 => 'club_id','contact_id','attribute_id');
        $keys = array('date' => $changedDate, 'kind' => $logType, 'field' => $fieldType, 'value_before' => $valueBefore,
            'value_after' => $valueAfter, 'changed_by' => $changedBy, 'club_id' => $clubId, 'contact_id' => $contactId, 'attribute_id' => $attrId); //, 'is_confirmed', 'confirmed_by', 'confirmed_date');
        if ($this->isConfirmed == 1) {
            $keys['is_confirmed'] = $this->isConfirmedVal;
            $keys['confirmed_by'] = $this->confirmedBy;
            $keys['confirmed_date'] = $this->confirmedDate;
        }
        //$values=array($changedDate,$logType,$fieldType,$valueBefore, $valueAfter, $changedBy, $clubId,$contactId);//, $attrId, $isConfirmedVal, $confirmedBy, $confirmedDate);
        $this->changelogData[] = $keys;
    }

    /**
     * Function to set connection log
     *
     * @param type $oldCompanydefContact
     * @param type $oldHasMainContact
     */
    private function setConnectionLog($oldCompanydefContact, $oldHasMainContact)
    {
        if ($oldHasMainContact == '1') {
            if ($oldCompanydefContact != '' && !is_null($oldCompanydefContact) && $oldCompanydefContact != 0) {
                $this->previousMainContactType = 'existing';
                $this->previousMainContactId = $oldCompanydefContact;
                if ($this->mainContactType == 'manual' || $this->mainContactType == 'no') {
                    $olddefContactName = $this->em->getRepository('CommonUtilityBundle:FgCmAttribute')->getContactName($oldCompanydefContact);
                    $oldCompanydefFun = FgUtility::getSecuredData($this->editData[0]['comp_def_contact_fun'], $this->conn);
                    $defContactName = FgUtility::getSecuredData($olddefContactName, $this->conn);
                    $this->connectionlogData[] = "(@fedcontactId, '$oldCompanydefContact', NULL, '$this->now', '{$this->trans['mainContact']}', '$oldCompanydefFun', '$defContactName', '', '$this->currentContactId','global')";
                    $this->connectionlogData[] = "($oldCompanydefContact, @fedcontactId, NULL, '$this->now', '{$this->trans['mainContactOf']}', '$oldCompanydefFun', '$this->contactName', '', '$this->currentContactId','global')";
                }
            } else {
                $this->previousMainContactType = 'manual';
            }
        } else {
            $this->previousMainContactType = 'no';
        }
    }

    /**
     * Function to set contact data array for query insertion to fg_cm_contact
     * @param type $fieldValue
     */
    private function setInsertContactArray($fieldValue = array())
    {
        foreach ($fieldValue as $key => $value) {
            $this->insertContactSet[] = "$key = $value";
            $this->insertContactDupSetValues[] = "$key = VALUES($key)";
        }
    }

    /**
     * Function to set contact basic details
     */
    private function setCreateEditBase()
    {
        if (!$this->contactId) {
            $this->contactClubId = $this->club->get('id');
            $this->insertContactSet[] = "id = ''";
            $this->insertContactDupSetValues[] = 'id = LAST_INSERT_ID( id )';
            $this->insertContactSet[] = 'created_at = NOW()';
            $this->insertContactDupSetValues[] = 'created_at = VALUES(created_at)';
            $value = 'ACTIVE';
            $this->setContactChangeLog('@fedcontactId', '', 'contact status', '', '', $value, $this->currentContactId, '', '', '', '', $this->federationId);
            if ($this->isDraft) {
                $this->insertContactSet[] = 'is_draft = 1';
            }
        } else {
            $this->mode = 'edit';
            $this->fedContactId = $this->editData[0]['fed_contact_id'];
            $this->subFedContactId = $this->editData[0]['subfed_contact_id'];
            $this->contactClubId = $this->editData[0]['created_club_id'];
            $this->insertContactSet[] = "id = '$this->contactId'";
            $this->insertContactDupSetValues[] = 'id = LAST_INSERT_ID( id )';
            //if front end module not enabled keep value of intranet access to 1
            if (!in_array('frontend1', $this->club->get('bookedModulesDet')) && !in_array('Intranet access', $this->formValues['system']['attribute'])) {
                $this->formValues['system']['attribute'][] = 'Intranet access';
            }
        }
        $this->contactName = FgUtility::getSecuredData($this->getUpdatedContactName(), $this->conn);
        $this->sameAsInvoice = ($this->inlineEditFlag == 1) ? $this->editData[0]['same_invoice_address'] : $this->sameAsInvoice;
    }

    /**
     * Function to set default log entry for create
     */
    private function setDefaultLogForCreate()
    {
        if (!$this->contactId) {
            $no = $this->translator->trans('NO');
            $yes = $this->translator->trans('YES');
            if (!array_key_exists('attribute', $this->formValues['system'])) {
                $this->setContactChangeLog('@fedcontactId', '', 'system', 'stealth mode', '', $no, $this->currentContactId, 'NULL', '', '', '', $this->federationId);
                $this->setContactChangeLog('@fedcontactId', '', 'system', 'intranet access', '', $no, $this->currentContactId, 'NULL', '', '', '', $this->federationId);
            } else {
                if ($this->federationId > 0) {
                    $this->setContactChangeLog('@fedcontactId', '', 'system', 'intranet access', '', $yes, $this->currentContactId, 'NULL', '', '', '', $this->federationId);
                    $this->setContactChangeLog('@fedcontactId', '', 'system', 'stealth mode', '', $no, $this->currentContactId, 'NULL', '', '', '', $this->federationId);
                }
                if ($this->subFederationId > 0) {
                    $this->setContactChangeLog('@subfedcontactId', '', 'system', 'intranet access', '', $yes, $this->currentContactId, 'NULL', '', '', '', $this->subFederationId);
                    $this->setContactChangeLog('@subfedcontactId', '', 'system', 'stealth mode', '', $no, $this->currentContactId, 'NULL', '', '', '', $this->subFederationId);
                }
            }
        }
    }

    /**
     * Function to set confirmed values
     */
    private function setConfirmedValues()
    {
        $this->confirmedBy = ($this->isConfirmed == '1') ? "$this->currentContactId" : 'NULL';
        $this->confirmedDate = ($this->isConfirmed == '1') ? "$this->now" : 'NULL';
        $this->isConfirmedVal = ($this->isConfirmed == '1') ? "1" : 'NULL';
        $this->logClubId = ($this->isConfirmed == '1') ? "$this->clubId" : 'NULL';
    }

    /**
     * Function to set class variables
     */
    private function setClassVariables()
    {
        $this->club = $this->container->get('club');
        $this->clubId = $this->club->get('id');
        $this->federationId = $this->club->get('federation_id');
        $this->subFederationId = $this->club->get('sub_federation_id');
        $this->clubType = $this->club->get('type');
        $this->currentContactId = ($this->container->get('contact')->get('id')) ? $this->container->get('contact')->get('id') : 1;
        $this->defaultSystemLang = $this->club->get('default_system_lang');

        $this->fedFields = array_keys($this->club->get('fedFields'));
        $this->subFedFields = array_keys($this->club->get('subFedFields'));
        $this->clubFields = array_keys($this->club->get('clubFields'));
        $this->now = date('Y-m-d H:i:s');

        $this->setAttributeIds();
        $this->setTranslationArray();
    }

    /**
     * Function to change club variable
     */
    public function setClubVariables($cluVar, $fedFields, $subFedFields, $clubFields)
    {

        //$this->club = $cluVar;
        $this->clubId = $cluVar['id'];
        $this->federationId = $cluVar['federationId'];
        $this->subFederationId = $cluVar['sub_federation_id'];
        $this->clubType = $cluVar['clubType'];
        $this->defaultSystemLang = $cluVar['default_system_lang'];

        $this->fedFields = array_keys($fedFields);
        $this->subFedFields = array_keys($subFedFields);
        $this->clubFields = array_keys($clubFields);
        $this->now = date('Y-m-d H:i:s');
    }

    /**
     * Function to set attribute ids
     */
    private function setAttributeIds()
    {
        $this->systemFields = $this->container->getParameter('system_fields');
        $this->systmPersonaBothlFields = $this->container->getParameter('system_personal_both');
        $this->invoiceCategory = $this->container->getParameter('system_category_invoice');
        $this->correspondanceCategory = $this->container->getParameter('system_category_address');
        $this->systemPersonal = $this->container->getParameter('system_category_personal');
        $this->systemCompany = $this->container->getParameter('system_category_company');
        $this->systemCompanyLogo = $this->container->getParameter('system_field_companylogo');
        $this->systemTeamPicture = $this->container->getParameter('system_field_team_picture');
        $this->systemCommunityPicture = $this->container->getParameter('system_field_communitypicture');
        $this->systemPrimaryEmail = $this->container->getParameter('system_field_primaryemail');
        $this->systemFirstName = $this->container->getParameter('system_field_firstname');
        $this->systemLastName = $this->container->getParameter('system_field_lastname');
        $this->systemCompanyName = $this->container->getParameter('system_field_companyname');
    }

    /**
     * Set translation array
     */
    private function setTranslationArray()
    {
        $this->trans['mainContact'] = $this->translator->trans('CM_MAIN_CONTACT');
        $this->trans['mainContactOf'] = $this->translator->trans('LOG_MAIN_CONTACT_OF');
        $this->trans['withMainContact'] = $this->translator->trans('WITH_MAIN_CONTACT');
        $this->trans['addressBlock'] = $this->translator->trans('ADDRESS_BLOCK');
        $this->trans['withOutMainContact'] = $this->translator->trans('WITHOUT_MAIN_CONTACT');
    }

    /**
     * Function to unset profile picture
     */
    private function unsetProfilePicture()
    {
        if (array_key_exists($this->systemCompanyLogo, $this->formValues[$this->systemCompany])) {
            unset($this->formValues[$this->systemCompany][$this->systemCompanyLogo]);
        }
        if (array_key_exists($this->systemTeamPicture, $this->formValues[$this->systemPersonal])) {
            unset($this->formValues[$this->systemPersonal][$this->systemTeamPicture]);
        }
        if (array_key_exists($this->systemCommunityPicture, $this->formValues[$this->systemPersonal])) {
            unset($this->formValues[$this->systemPersonal][$this->systemCommunityPicture]);
        }
    }

    /**
     * Function to get name of the created/edited conatct.
     *
     * @return String
     */
    public function getUpdatedContactName()
    {
        $contactName = '';
        if ($this->formValues['system']['contactType'] == 'Company') {
            $contactName = $this->formValues[$this->systemCompany][$this->systemCompanyName];
        } else {
            $contactName = $this->formValues[$this->systemPersonal][$this->systemLastName] . ', ' . $this->formValues[$this->systemPersonal][$this->systemFirstName];
        }

        return $contactName;
    }

    /**
     * Function to get pic field name.
     *
     * @param int $fieldId Field id
     *
     * @return String fieldname
     */
    private function getFieldName($fieldId)
    {
        $fieldName = '';
        switch ($fieldId) {
            case $this->systemCompanyLogo:
                $fieldName = $this->fieldDetails['attrTitles'][$this->systemCompanyLogo][$this->defaultSystemLang];
                break;
            case $this->systemTeamPicture:
                $fieldName = $this->fieldDetails['attrTitles'][$this->systemTeamPicture][$this->defaultSystemLang];
                break;
            case $this->systemCommunityPicture:
                $fieldName = $this->fieldDetails['attrTitles'][$this->systemCommunityPicture][$this->defaultSystemLang];
                break;
        }

        return FgUtility::getSecuredData($fieldName, $this->conn);
    }

    /**
     * Add newsletter subscription log when creating contact based on club settings
     * @param type $contactId
     * @param type $clubId
     */
    private function addNewsletterSubscriptionLog($contactId, $clubId)
    {
        $defSubscription = $this->conn->fetchAll('SELECT default_contact_subscription FROM fg_club WHERE id=' . $clubId);
        if ($defSubscription[0]['default_contact_subscription'] == '1') {
            $this->setContactChangeLog($contactId, '', 'system', 'newsletter', '', 'subscribed', $this->currentContactId, '', '', '', '', $clubId);
        }
    }
}
