<?php

/**
 * ImportShareController
 *
 * This controller was created for handling import of contacts with sharing for fairgate/pits
 *
 * @package    ClubadminContactBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
namespace Clubadmin\ContactBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Common\UtilityBundle\Controller\FgController as ParentController;
use Symfony\Component\HttpFoundation\Session\Session;
use Common\UtilityBundle\Util\FgUtility;
use Clubadmin\ContactBundle\Util\ImportValidation;
use Common\UtilityBundle\Repository\Pdo\ContactPdo;
use Symfony\Component\HttpFoundation\Request;

/**
 * Import contact controller
 */
class ImportShareController extends ParentController
{

    /**
     * Import contact function - step1 to upload file
     * 
     * @return template
     */
    public function indexAction()
    {
        $this->fgpermission->checkAreaAccess(array('from'=>'importShare'));
        $club = $this->container->get('club');
        $return['clubId'] = $this->clubId;
        $return['clubLanguages'] = $this->clubLanguages;
        $return['clubType'] = $this->clubType;
        $return['fedMembershipMandatory'] = $club->get('fedMembershipMandatory');
        $return['clubMembershipAvailable'] = $club->get('clubMembershipAvailable');

        return $this->render('ClubadminContactBundle:ImportShare:importContact.html.twig', $return);
    }

    /**
     * Import contact file submit action
     * @param string $module (contact or sponsor)
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @return template
     * @throws NotFoundHttpException if page is directly accessed
     */
    public function importFileSubmitAction(Request $request, $module)
    {
        $return['clubId'] = $this->clubId;
        if ($request->getMethod() == 'POST') {
            $importFile = $request->files->get('importFile');
            $uploadedFile = $this->uploadCsv($importFile);
            //load the file contents to temporay table and get its contents
            $csvFields = $this->getCsvRows($request->request->get('csvType'), '', '', $module);

            if ($uploadedFile['status'] && $csvFields) {
                $return['contactCount'] = $csvFields['contactCount'];
                $return['data'] = $csvFields['data'];
                if (!$this->container->get('session')->isStarted()) {
                    $importValues = new Session();
                    $importValues->start();
                } else {
                    $importValues = $this->container->get('session');
                }
                $contactType = $request->request->get('contactType');
                $return['update'] = $request->request->get('update');
                $return['requiredIds'] = $this->getMandatoryFields($contactType);
                $importValues->set('importShareFile' . $module . $this->clubId, array(
                    'contactType' => $contactType,
                    'tableName' => $csvFields['tableName'],
                    'csvType' => $request->request->get('csvType'),
                    'filename' => $uploadedFile['fileName']
                ));
                $fieldType = ($contactType == 'companyWithMain') ? 'Company' : $contactType;
                $clubIdArray = array(
                    'clubId' => $this->clubId,
                    'federationId' => $this->federationId,
                    'subFederationId' => $this->subFederationId,
                    'clubType' => $this->clubType
                );
                $clubIdArray['address'] = $this->get('translator')->trans('CONTACT_FIELD_ADDRESS');
                $clubContactFields = $this->em->getRepository('CommonUtilityBundle:FgCmAttributeset')->getAllClubContactFields($clubIdArray, $this->conn, 0, $fieldType);
                $return['fieldDetails'] = $this->em->getRepository('CommonUtilityBundle:FgCmAttributeset')->fieldItrator($clubContactFields);
                $return['skipFields'] = (count($this->clubLanguages) > 1) ? array() : array(
                    515
                );
                
                $return['skipFields'][] = 'joining_date';
                $return['sysLang'] = $this->clubDefaultLang;
                $return['defSysLang'] = $this->clubDefaultSystemLang;
                $return['invoiceCatId'] = $this->container->getParameter('system_category_invoice');
                $return['correspondanceCatId'] = $this->container->getParameter('system_category_address');
                $return['bookedModules'] = $this->get('club')->get('bookedModulesDet');
                $return['clubMembershipAvailable'] = $this->get('club')->get('clubMembershipAvailable');
                $return['fedMembershipMandatory'] = $this->get('club')->get('fedMembershipMandatory');
                $return['clubtype'] = $this->get('club')->get('type');
                
                return $this->render('ClubadminContactBundle:ImportShare:importDataAssignment.html.twig', $return);
            } else {
                $errorMessage = $uploadedFile['errorMessage'] ? $uploadedFile['errorMessage'] : $this->get('translator')->trans('INVALID_DELIMITER');

                return new JsonResponse(array(
                    'status' => 'ERROR',
                    'message' => $errorMessage
                ));
            }
        } else {
            //throw $this->createNotFoundException();
            $this->fgpermission->checkClubAccess('', 'importcontact');
        }
    }

    /**
     * Function to upload csv file
     * @param string $importFile
     * @return array Array of upload details
     */
    private function uploadCsv($importFile)
    {
        $containerParameters = $this->container->getParameterBag();
        $csvMimeTypes = $containerParameters->get('csv_mime_types');
        $this->uploadDir = 'uploads/temp';
        if (empty($importFile)) {
            return array(
                'status' => false,
                'errorMessage' => $this->get('translator')->trans('FORM_ERROR_DISPLAY')
            );
        }
        if (in_array($importFile->getClientMimeType(), $csvMimeTypes)) {
            $importTable = 'tp_import_' . strtotime('now').'.csv';
            $this->filename = FgUtility::getFilename($this->uploadDir, $importTable);
            $movedFile = $importFile->move($this->uploadDir, $this->filename);

            /* If the file is other than UTF-8 encoding change to UTF-8 */
            //$this->filename = FgUtility::changeFileEncodingToUtf8($this->uploadDir, $this->filename);

            $return = $movedFile->getExtension() ? array(
                'status' => true,
                'fileName' => $this->filename
                ) : array(
                'status' => true,
                'errorMessage' => 'Error'
            );
        } else {
            $return = array(
                'status' => false,
                'errorMessage' => $this->get('translator')->trans('FORM_ERROR_DISPLAY')
            );
        }

        return $return;
    }

    /**
     * Function to get first two rows of csv
     * @param string  $csvType           Csv Type(Semi column/comma separated)
     * @param string  $importTable       Import table name(temporary table)
     * @param boolean $notImportFirstRow Flag to indicate first row has to be imported or not
     * @param string $module (contact or sponsor)
     * @return boolean
     */
    private function getCsvRows($csvType, $importTable = '', $notImportFirstRow = '', $module)
    {
        $delimiter = ($csvType == 'comma') ? ',' : ';';
        ini_set("auto_detect_line_endings", 1);
        if (($handle = fopen($this->uploadDir . "/" . $this->filename, "r")) !== false) {
            if (($data[0] = fgetcsv($handle, 0, $delimiter)) !== false) {
                $data[1] = fgetcsv($handle, 0, $delimiter);
                $totalColumns = count($data[0]);
                //get the line ending character "\r" for mac or "\n" for linux or "\r\n" for windows
                rewind($handle);
                $headerline = fgets($handle);
                $lineEnding = substr($headerline, -1);
                switch (ord($lineEnding)) {
                    case 10:
                        $lineEnding = "\\n";
                        break;
                    case 13:
                        $lineEnding = "\\r";
                        break;
                    default:
                        $lineEnding = "\\n";
                        break;
                }
                $uploadDir = FgUtility::getUploadDir();
                //load the csv content to temporary table
                $importTable = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->temporaryTableForImportContact($this->container, $importTable, $data[0], $module, $uploadDir, $this->filename, $delimiter, $lineEnding);
                $this->em->getRepository('CommonUtilityBundle:FgCmContact')->removeFirstRow($importTable, $notImportFirstRow);
                $contactCount = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getCountofDatasToImport($importTable);
            }
        }
        fclose($handle);
        if (count($data[0]) > 1) {
            $return = array(
                'data' => $data,
                'tableName' => $importTable,
                'totalColumn' => $totalColumns,
                'contactCount' => $contactCount[0]['count']
            );
        } else {
            $return = false;
        }

        return $return;
    }

    /**
     * Function to get mandatory field of contact
     * @param string $contactType company/singleperson
     * @return array Array of mandatory fields
     */
    private function getMandatoryFields($contactType)
    {
        switch ($contactType) {
            case 'single':
                $mandatory = (count($this->clubLanguages) > 1) ? array(2, 23, 72, 1, 515) : array(2, 23, 72, 1);
                break;
            case 'companyWithMain':
                $mandatory = (count($this->clubLanguages) > 1) ? array(2, 23, 72, 9, 1, 515) : array(2, 23, 72, 9, 1);
                break;
            case 'companyNoMain':
                $mandatory = (count($this->clubLanguages) > 1) ? array(9, 515) : array(9);
                break;
        }
        $mandatory[] = 'fed_membership';
        $mandatory[] = 'club_ids';

        return $mandatory;
    }

    /**
     * Import/update File Correction Action - To validate imported data
     * @param string $module (contact or sponsor)
     * @return template
     */
    public function importFileCorrectionAction(Request $request, $module)
    {
        if ($request->getMethod() == 'POST') {
            $isCorrectFile = false;
            $step = $request->request->get('step', '');
            $notImportFirstRow = $request->request->get('not_import_first_row', '0');
            $countryList = FgUtility::getCountryList();
            $update = $request->request->get('update', '0');
            $importFile = $request->files->get('importFile');
            $session = $this->container->get('session');
            $importValues = $session->get('importShareFile' . $module . $this->clubId);
            $return = array(
                'dataValue' => $importValues['filename'],
                'notImportFirstRow' => $notImportFirstRow
            );
            /* if the cation from file correction page */
            if ($step == 'file_correction') {
                /* Upload corrected file */
                $return = $this->uploadCorrectedFile($importFile, $importValues, $notImportFirstRow, $return, $module);
                $importValues['filename'] = $return['dataValue'];
            } elseif ($step == 'data') {
                /* if the cation from file data page */
                $importValues['mapingFields'] = $request->request->get('maping');
                $importValues['fieldSkipped'] = array_keys($request->request->get('fieldMap', array()));
                $session->set('importShareFile' . $module . $this->clubId, $importValues);
                /* If first row in the CSV is skipped, remove that from import table */
                $this->em->getRepository('CommonUtilityBundle:FgCmContact')->removeFirstRow($importValues['tableName'], $notImportFirstRow);
            }
            /* Validate imported data */
            $validationObj = new ImportValidation($importValues, $this->container);
            $validationObj->fixMandatoryFields = $this->getMandatoryFields($importValues['contactType']);
            $validationObj->countryCodes = '"' . implode('","', array_keys($countryList)) . '"';
            $validationObj->validateData();
            /* Get data for further processing */
            $validationData = $validationObj->validationData;
            /* If there are no validation errors , do next actions */
            if (count($validationData['errorRows']) == 0) {
                $this->processValidData($update, $importValues, $validationObj->allFixedFields, $validationData, $module);
                $isCorrectFile = true;
                $return['actionPath'] = $this->generateUrl('import_share_duplcate');
            } else {
                $return['actionPath'] = $this->generateUrl('import_share_file_correction');
            }
            $return['isCorrectFile'] = $isCorrectFile;
            $return['errorRows'] = $validationData['errorRows'];
        }

        return $this->render('ClubadminContactBundle:ImportShare:importFileCorrection.html.twig', $return);
    }

    /**
     * To update import maping table which contains details imported table column vs original table column
     *
     * @param String $tableName           Import temporary table name
     * @param Array  $insertValues        Maping details imported table column vs original table column
     * @param Array  $corresAddressFields Array of contact fields in correspondence address category
     */
    private function insertIntoMapingTable($tableName, $insertValues, $corresAddressFields)
    {
        $club = $this->container->get('club');
        $federationId = $club->get('federation_id');
        $clubId = $club->get('id');
        if (count($corresAddressFields) > 0) {
            $invoiceFields = $this->em->getRepository('CommonUtilityBundle:FgCmAttribute')->getInvoiceFields(array_keys($corresAddressFields));
            foreach ($invoiceFields as $rowField) {
                $rowValues = $corresAddressFields[$rowField['address_id']];
                $club_id = ($rowValues['table'] == 'master_system') ? $federationId : $clubId;
                $insertValues[] = "('" . $rowValues['column'] . "', '{$rowField['id']}', '" . $rowField['fieldTitle'] . "', '" . $rowValues['table'] . "', '" . $tableName . "', '$club_id')";
            }
        }
        if (count($insertValues) > 0) {
            $this->em->getRepository('CommonUtilityBundle:FgCmContact')->insertIntoMappingTable($tableName, $insertValues);
            
        }
        $this->em->getRepository('CommonUtilityBundle:FgCmContact')->insertIntoClubTableToMappingTable($tableName);
    }

    /**
     * Method to get mapping positions of fields
     * If it is not mapped return false
     * @param string $fieldName
     * @param array $mapingFields -  mapped fields
     * @param array $skippedFields -  skipped fields
     * @return int
     */
    private function getMappingPosition($fieldName, $mapingFields, $skippedFields)
    {
        $fieldPosition = array_search($fieldName, $mapingFields);
        $return = (is_numeric($fieldPosition) && !in_array($fieldPosition, $skippedFields) ? $fieldPosition : false);

        return $return;
    }

    /**
     * Import/update action to find primary email in imported contacts exists in the club
     * @param string $module (contact or sponsor)
     * @return template
     */
    public function importDuplicateAction(Request $request, $module)
    {
        $return = array();
        $session = $this->container->get('session');
        $importValues = $session->get('importShareFile' . $module . $this->clubId);
        $tableName = $importValues['tableName'];
        $mapingFields = $importValues['mapingFields'];
        $tmpIsCompany = $importValues['contactType'] == 'single' ? false : true;
        $step = $request->request->get('step', '');
        $update = $request->request->get('update', '0');
        $club = $this->container->get('club'); 
        if ($step == 'file_correction') {
            $primaryEmail = $this->container->getParameter('system_field_primaryemail');
            $dob = $this->container->getParameter('system_field_dob');
            $lastName = $this->container->getParameter('system_field_lastname');
            $companyName = $this->container->getParameter('system_field_companyname');
            $firstName = $this->container->getParameter('system_field_firstname');
            $company = $this->container->getParameter('system_field_companyname');
            $mappedPosition['tmpContactId'] = array_search('contact_id', $mapingFields);
            $mappedPosition['tmpEmail'] = $this->getMappingPosition($primaryEmail, $mapingFields, $importValues['fieldSkipped']);
            $mappedPosition['tmpDob'] = $this->getMappingPosition($dob, $mapingFields, $importValues['fieldSkipped']);
            $mappedPosition['tmpFirstName'] = $this->getMappingPosition($firstName, $mapingFields, $importValues['fieldSkipped']);
            $mappedPosition['tmpLastName'] = $this->getMappingPosition($lastName, $mapingFields, $importValues['fieldSkipped']);
            $mappedPosition['tmpFirma'] = $this->getMappingPosition($companyName, $mapingFields, $importValues['fieldSkipped']);
            $mappedPosition['tmpFederation'] = $this->getMappingPosition('fed_membership', $mapingFields, $importValues['fieldSkipped']);
            $mappedPosition['tmpClubIds'] = $this->getMappingPosition('club_ids', $mapingFields, $importValues['fieldSkipped']);
            if (($club->get('clubMembershipAvailable')) && ($club->get('type') == 'federation_club' || $club->get('type') == 'sub_federation_club' || $club->get('type') == 'standard_club')) {
                $mappedPosition['tmpMembership'] = $this->getMappingPosition('member_category', $mapingFields, $importValues['fieldSkipped']);
            }
            if ($importValues['contactType'] === 'companyNoMain') {
                $mappedPosition['tmpType'] = '3';
            } else {
                $mappedPosition['tmpType'] = $tmpIsCompany ? '2' : '1';
            }

            $return['duplicates'] = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getDuplicateContacts($club, $mappedPosition, $tmpIsCompany, $primaryEmail, $dob, $lastName, $firstName, $company, $tableName, $update, 1);

            $return['actionPath'] = $this->generateUrl('import_share_submit');
            $return['clubId'] = $this->clubId;
        }
        $return['module'] = $module;
        return $this->render('ClubadminContactBundle:ImportShare:importDuplicate.html.twig', $return);
    }

    /**
     * ImportAssignmentSubmit Action - to save contacts and role assignments of imported contacts
     * @param string $module (contact or sponsor)
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws NotFoundHttpException  if page is directly accessed
     */
    public function importConfirmSubmitAction(Request $request, $module)
    {
        if ($request->getMethod() == 'POST') {
            set_time_limit(0);
            $importValues = $this->container->get('session');
            $importDetails = $importValues->get('importShareFile' . $module . $this->clubId);
            $tableName = $importDetails['tableName'];
            $updateColumns = $importDetails['updateColumns'];
            $insertColumns = $importDetails['insertColumns'];
            $mappingTableInsertQuery = $importDetails['mappingTableInsertQuery'];
            $corresFieldsToImport = $importDetails['corresFieldsToImport'];
            $club = $this->container->get('club');
            $federationId = $club->get('federation_id');
            if (count($insertColumns) > 0) {
                $this->em->getRepository('CommonUtilityBundle:FgCmContact')->alterImportTable($tableName, $insertColumns);
            }
            $this->em->getRepository('CommonUtilityBundle:FgCmContact')->updateImportTable($federationId, $tableName, $updateColumns, $this->clubLanguages, $this->clubDefaultLang);
            $this->insertIntoMapingTable($tableName, $mappingTableInsertQuery, $corresFieldsToImport);
            $duplicates = $this->getDuplicateIds($request);
            $primaryEmail = $this->container->getParameter('system_field_primaryemail');
            $this->em->getRepository('CommonUtilityBundle:FgCmContact')->removeDuplicate($importDetails, $duplicates, $primaryEmail);
            $this->importContacts($module);
            $redirectPath =  $this->generateUrl('contact_index');
            $flashMessage =  $this->get('translator')->trans('CONTACT_IMPORTED_SUCCESSFULLY');
            return new JsonResponse(array(
                'status' => 'SUCCESS',
                'sync' => 1,
                'redirect' => $redirectPath,
                'flash' => $flashMessage
            ));
        } 
    }

    /**
     * Method for updating or importing contacts and deleting duplicated subscribers
     * 
     * @param string $module contact
     */
    private function importContacts($module)
    {
        $importValues = $this->container->get('session');
        $importDetails = $importValues->get('importShareFile' . $module . $this->clubId);
        $log = fopen('import_share_log_'.date('Y_m_d_H_is').'.txt','w');
        fwrite($log, "Importing contacts from ".$importDetails['tableName']."\n");
        $importQuery = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->callImportShareContacts($importDetails['tableName'], $this->clubId, $this->contactId, $importDetails['contactType'], $this->clubType);
        $clubs = $this->em->getRepository('CommonUtilityBundle:FgClub')->findBy(array('federationId' => $this->clubId));
        foreach ($clubs as $club){
            $this->conn->executeQuery("CALL updateMemberId(".$club->getId().")");
        }
        $this->conn->executeQuery("CALL updateMemberId(".$this->clubId.")");
        fwrite($log, "Contacts imported to main club\n");
        $ContactPdo = new ContactPdo($this->container);
        $ContactPdo->insertLoginEntriesForImortedContact($this->get('club'), $importDetails['tableName'], $this->contactId);
        fwrite($log, "Imported contact detail updated.\n");
        $ContactPdo->shareImortedContact($importDetails['tableName'], $log);
    }

    /**
     * Steps to do if imported data is valid, set up session variables
     * @param boolean $update To check whether the action is import or update
     * @param array $importValues import details
     * @param array $allStaticFields Array statis fiels tobe used in import
     * @param array $validationData Data returned after validation
     * @param string $module (contact or sponsor)
     */
    private function processValidData($update, $importValues, $allStaticFields, $validationData, $module)
    {
        $mappingTableInsertQuery = $validationData['mappingTableInsertQuery'];
        $updateColumns = $validationData['updateColumns'];
        $corresFieldsToImport = $validationData['corresFieldsToImport'];
        $session = $this->container->get('session');
        $insertColumns = array();
        $club = $this->container->get('club');
        $clubId = $club->get('id');

        $federationId = ($this->federationId <> 0) ? $this->federationId : $this->clubId;
        $contactFields = $club->get('allContactFields');
        $corresLang = $this->container->getParameter('system_field_corress_lang');
        //Add FedContactId fields to mapping Table
        $mappingTableInsertQuery[] = "('', 'fed_contact_id', 'federation', 'master_federation_$federationId', '{$importValues['tableName']}','$federationId')";
        
        /* If maembership category column is there to import and no joining date column, add a joindate field with today's date */
        $isMemberCategory = array_search('member_category', $importValues['mapingFields']);
        $isJoiningDate = array_search('joining_date', $importValues['mapingFields']);
        if ($isMemberCategory && (!$isJoiningDate || ($isJoiningDate && in_array($isJoiningDate, $importValues['fieldSkipped'])))) {
            $updateColumns['joining_date'] = 'joining_date';
            $insertColumns[] = 'ADD `joining_date` DATE NOT NULL';
            $mappingTableInsertQuery[] = "('joining_date', 'joining_date', '" . $this->get('translator')->trans('CM_JOINING_DATE') . "', 'fg_cm_contact', '{$importValues['tableName']}','$clubId' )";
        }
        
        /* If if only correspondance language column is not there to import , add correspondance language field default club language */
        $isCorresLang = array_search($corresLang, $importValues['mapingFields']);
        if (!array_key_exists($isCorresLang, $importValues['mapingFields']) && $update == '0') {
            $updateColumns['corres_lang'] = 'corres_lang';
            $insertColumns[] = 'ADD `corres_lang` CHAR(2) NOT NULL';
            $corresLangTitle = ($contactFields[$corresLang]['title']) ? $contactFields[$corresLang]['title'] : $this->getCorrespondenceLangTitle();
            $mappingTableInsertQuery[] = "('corres_lang', '$corresLang', '" . $corresLangTitle . "', 'master_system', '{$importValues['tableName']}','$federationId')";
        } elseif ($update == '0' || ($update == '1' && is_numeric($isCorresLang))) {
            $updateColumns['column' . $isCorresLang] = 'corres_lang';
        }
        /* If atlaest one invoice category fields are there in the import  , set same_invoice_address flag for contact */
        if (!$validationData['isSameAsInvoice']) {
            $corresFieldsToImport = array();
            $insertColumns[] = "ADD `same_invoice_address` TINYINT(1) NULL DEFAULT '0'";
            $mappingTableInsertQuery[] = "(0, 'same_invoice_address', 'Same as invoice address', 'fg_cm_contact', '{$importValues['tableName']}','$clubId')";
        }

        /* if newsletter subbscription column is not there to import, add a column to put default subscription values  */
        if ($validationData['subscriberColumn'] == '') {
            $mappingTableInsertQuery[] = "('', 'is_subscriber', '{$allStaticFields['is_newsletter_subscriber']['title']}', 'fg_cm_contact', '{$importValues['tableName']}','$clubId')";
        }
        
        $importValues['updateColumns'] = $updateColumns;
        $importValues['insertColumns'] = $insertColumns;
        $importValues['mappingTableInsertQuery'] = $mappingTableInsertQuery;
        $importValues['corresFieldsToImport'] = $corresFieldsToImport;
        $session->set('importShareFile' . $module . $this->clubId, $importValues);
    }

    /**
     * Method to get correspondence language title in club's system language.
     *
     * @return String $correspondenceLangTitle
     */
    private function getCorrespondenceLangTitle()
    {
        $club = $this->container->get('club');
        $clubLanguages = $club->get('club_languages');
        $clubLanguagesDet = $club->get('club_languages_det');
        $clubSystemLang = $clubLanguagesDet[$clubLanguages[0]]['systemLang'];
        $systemFieldCorressLang = $this->container->getParameter('system_field_corress_lang');
        $correspondenceLangTitle = $this->em->getRepository('CommonUtilityBundle:FgCmAttributeI18n')->getCorrespondenceLangTitle($systemFieldCorressLang, $clubSystemLang);
        return $correspondenceLangTitle;
    }

    /**
     *
     * @param object  $importFile        Uploaded file
     * @param array   $importValues      Import details
     * @param boolean $notImportFirstRow Firstrow in CSV is skipped or not
     * @param array   $returnArray       Array to be returned to tempalte
     *
     * @return array Array to be returned to tempalte
     */
    private function uploadCorrectedFile($importFile, $importValues, $notImportFirstRow, $returnArray, $module)
    {
        if ($importFile) {
            $uploadedFile = $this->uploadCsv($importFile);
            $returnArray['dataValue'] = $uploadedFile['fileName'];
            if ($uploadedFile['status']) {
                $this->getCsvRows($importValues['csvType'], $importValues['tableName'], $notImportFirstRow, $module);
            } else {
                $returnArray['fileError'] = 'Invalid File';
            }
        }

        return $returnArray;
    }
    /**
     * Function to get duplicate ids
     * @param object $request
     * @return array
     */
    private function getDuplicateIds($request)
    {
        $toImportIds = $request->request->get('toimport', array());
        $emailDupIds = $request->request->get('email_dup', array());
        $duplicateIds = $request->request->get('duplcates', array());

        $removeIds = array_diff($duplicateIds, $toImportIds);
        $removeEmailIds = array_intersect($emailDupIds, $toImportIds);
        $removedIds = array_diff($removeIds, $removeEmailIds);

        return array(
            'removeIds' => $removedIds,
            'removeEmailIds' => $removeEmailIds
        );
    }
}
