<?php

/**
 *
 * This controller was created for Import/update subscriber controller
 *
 * @package    ClubadminContactBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
namespace Clubadmin\CommunicationBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Common\UtilityBundle\Controller\FgController as ParentController;
use Symfony\Component\HttpFoundation\Session\Session;
use Common\UtilityBundle\Util\FgUtility;
use Symfony\Component\HttpFoundation\Request;

class SubscriberimportController extends ParentController
{

    /**
     * Function to show subscriber import page
     *
     * @param type $type boolean
     *
     * @return template
     */
    public function indexAction($type = false)
    {
        $return['clubId'] = $this->clubId;
        $return['clubLanguages'] = $this->clubLanguages;
        $return['clubType'] = $this->clubType;

        return $this->render('ClubadminCommunicationBundle:Subscriberimport:importContact.html.twig', $return);
    }

    /**
     * Import/update contact file submit action
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @throws type exception
     */
    public function importFileSubmitAction(Request $request)
    {
        $return['clubId'] = $this->clubId;
        if ($request->getMethod() == 'POST') {
            $importFile = $request->files->get('importFile');
            $uploadedFile = $this->uploadCsv($importFile);
            $csvFields = $this->getCsvRows($request->request->get('csvType'));
            if ($uploadedFile['status'] && $csvFields) {
                $return['contactCount'] = $csvFields['contactCount'];
                $return['data'] = json_encode($csvFields['data']);
                if (!$this->container->get('session')->isStarted()) {
                    $importValues = new Session();
                    $importValues->start();
                } else {
                    $importValues = $this->container->get('session');
                }
                $return['requiredIds'] = array('email','correspondance_lang');
                $importValues->set('importFile' . $this->clubId, array('tableName' => $csvFields['tableName'], 'csvType' => $request->request->get('csvType'), 'filename' => $uploadedFile['fileName']));
                $clubIdArray = array('clubId' => $this->clubId, 'federationId' => $this->federationId, 'subFederationId' => $this->subFederationId, 'clubType' => $this->clubType);
                $clubIdArray['address'] = $this->get('translator')->trans('CONTACT_FIELD_ADDRESS');
                $return['skipFields'] = (count($this->clubLanguages) > 1) ? array() : array(515);
                $return['sysLang'] = $this->clubDefaultLang;
                $return['defSysLang'] = $this->clubDefaultSystemLang;
                $return['invoiceCatId'] = $this->container->getParameter('system_category_invoice');

                return $this->render('ClubadminCommunicationBundle:Subscriberimport:importDataAssignment.html.twig', $return);
            } else {
                $errorMessage = $uploadedFile['errorMessage'] ? $uploadedFile['errorMessage'] : $this->get('translator')->trans('INVALID_DELIMITER');

                return new JsonResponse(array('status' => 'ERROR', 'message' => $errorMessage));
            }
        }
        throw $this->createNotFoundException($this->clubTitle . ' have no access to this page');
    }

    /**
     * Function to upload csv file
     *
     * @param type $importFile importFile
     *
     * @return boolean
     */
    private function uploadCsv($importFile)
    {
        $containerParameters = $this->container->getParameterBag();
        $csvMimeTypes = $containerParameters->get('csv_mime_types');
        $this->uploadDir = 'uploads/temp';
        if (empty($importFile)) {
            return array('status' => false, 'errorMessage' => $this->get('translator')->trans('FORM_ERROR_DISPLAY'));
        }
        if (in_array($importFile->getClientMimeType(), $csvMimeTypes)) {
            $this->filename = FgUtility::getFilename($this->uploadDir, $importFile->getClientOriginalName());
            $movedFile = $importFile->move($this->uploadDir, $this->filename);
            /* If the file is other than UTF-8 encoding change to UTF-8 */
            $this->filename = FgUtility::changeFileEncodingToUtf8($this->uploadDir, $this->filename);

            $return = $movedFile->getExtension() ? array('status' => true, 'fileName' => $this->filename) : array('status' => true, 'errorMessage' => 'Error');
        } else {
            $return = array('status' => false, 'errorMessage' => $this->get('translator')->trans('FORM_ERROR_DISPLAY'));
        }

        return $return;
    }

    /**
     * Function to get first two rows of csv
     *
     * @param type   $csvType           csv Type
     * @param string $importTable       Import table
     * @param type   $notImportFirstRow Not import first row
     *
     * @return boolean
     */
    private function getCsvRows($csvType, $importTable = '', $notImportFirstRow = '')
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
                //load the csv content to temporary table
                $importTable = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->temporaryTableForImportContact($this->container, $importTable, $data[0], 'subscriber', FgUtility::getUploadDir(), $this->filename, $delimiter, $lineEnding);
                $this->em->getRepository('CommonUtilityBundle:FgCmContact')->removeFirstRow($importTable, $notImportFirstRow);
                $contactCount = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getCountofDatasToImport($importTable);
            }
        }
        fclose($handle);
        if (count($data[0]) > 0) {
            $data[0] = array_map('utf8_encode', $data[0]);
            $data[1] = array_map('utf8_encode', $data[1]);
            $return = array('data' => $data, 'tableName' => $importTable, 'totalColumn' => $totalColumns, 'contactCount' => $contactCount[0]['count']);
        } else {
            $return = false;
        }

        return $return;
    }

    /**
     * Import/update File Correction Action
     *
     * @return template
     */
    public function importFileCorrectionAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $isCorrectFile = false;
            $step = $request->request->get('step', '');
            $notImportFirstRow = $request->request->get('not_import_first_row', '0');
            $singleLineTextMaxLength = $this->container->getParameter('singleline_max_length');
            $manadatoryErrorRows = $inCorrectValueRows = $inCorrectNumberRows = $inCorrectEmailRows = $inCorrectLanguageRows = array();
            $inCorrectSinglelineRows = array();
            $emailDup = array();
            $errorRows = $return = array();
            $row = '';
            $allStaticFields = $this->getStaticFields();
            $mappingTableInsertQuery = array();
            $importFile = $request->files->get('importFile');
            $session = $this->container->get('session');
            $importValues = $session->get('importFile' . $this->clubId);
            $errorInFile = false;
            $return['dataValue'] = $importValues['filename'];
            $tableName = $importValues['tableName'];
            $return['notImportFirstRow'] = $notImportFirstRow;
            $updateColumns = array();
            if ($step == 'file_correction') {
                if ($importFile) {
                    $uploadedFile = $this->uploadCsv($importFile);
                    $return['dataValue'] = $uploadedFile['fileName'];
                    if ($uploadedFile['status']) {
                        $this->getCsvRows($importValues['csvType'], $tableName, $notImportFirstRow);
                        $importValues['filename'] = $uploadedFile['fileName'];
                    } else {
                        $return['fileError'] = 'Invalid File';
                        $errorInFile = true;
                    }
                } else {
                    $errorInFile = true;
                }
            } elseif ($step == 'data') {
                $mapingFields = $request->request->get('maping');
                $fieldSkipped = $request->request->get('fieldMap', array());
                $importValues['mapingFields'] = $mapingFields;
                $importValues['fieldSkipped'] = array_keys($fieldSkipped);
                $session->set('importFile' . $this->clubId, $importValues);
                if ($notImportFirstRow == '1') {
                    $this->removeFirstRow($tableName);
                }
            }
            $headerRow = $this->conn->fetchAll("SELECT * FROM $tableName WHERE row_id=1");
            $headerRow = $headerRow[0];
            $fixMandatoryFields = array('email','correspondance_lang');
            $availableLanguage = "'" . implode("','", $this->container->get('club')->get('club_languages')) ."'";
            $availableLanguageError = implode(", ", $this->container->get('club')->get('club_languages'));
            foreach ($importValues['mapingFields'] as $key => $field) {
                if ($field == '' || in_array($key, $importValues['fieldSkipped'])) {
                    continue;
                }
                $col = 'column' . $key;
                $staticFields = array_keys($allStaticFields);
                if (in_array($field, $staticFields)) {
                    $inputType = $allStaticFields[$field]['type'];
                    $fieldTitle = $allStaticFields[$field]['title'];
                    $updateColumns[$col] = $field;
                    $mappingTableInsertQuery[] = "('$col', '$field', '" . $allStaticFields[$field]['title'] . "', 'fg_cn_subscriber', '$tableName')";
                }
                if (in_array($field, $fixMandatoryFields)) {
                    $manadatoryErrorRows[] = "SELECT '$fieldTitle' AS column_name,'$headerRow[$col]' AS header_name, GROUP_CONCAT(CONCAT('$row ',row_id) SEPARATOR  ', ') AS err_rows FROM `$tableName` WHERE is_removed=0 AND  (LENGTH(`$col`) = 0 OR `$col` IS NULL)";
                }
                if ($field == 'salutation' || $field == 'gender') {
                    $updateColumns[$col] = ($field == 'salutation') ? 'salutation' : 'gender';
                    $inCorrectValueRows[] = "SELECT '$fieldTitle' AS column_name,'$headerRow[$col]' AS header_name, GROUP_CONCAT(CONCAT('$row ',row_id) SEPARATOR  ', ') AS err_rows FROM `$tableName` WHERE is_removed=0 AND  (REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' ) !='1' AND REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' ) !='2'  AND LENGTH(REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' )) > 0 AND REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' ) IS  NOT NULL AND REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' ) != '')";
                }
                if($field == 'correspondance_lang') {
                    $inCorrectLanguageRows[] = "SELECT '$fieldTitle' AS column_name,'$headerRow[$col]' AS header_name, GROUP_CONCAT(CONCAT('$row ',row_id) SEPARATOR  ', ') AS err_rows FROM `$tableName` WHERE is_removed=0 AND  `$col` NOT IN ($availableLanguage)";
                }
                
                switch ($inputType) {
                    case 'email':
                        $inCorrectEmailRows[] = "SELECT '$fieldTitle' AS column_name,'$headerRow[$col]' AS header_name, GROUP_CONCAT(CONCAT('$row ',row_id) SEPARATOR  ', ') AS err_rows FROM `$tableName` WHERE is_removed=0 AND  (REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' )  NOT REGEXP \"^[A-Z0-9!#$%&*+-/=?^_`{|}~']+@([a-zA-Z0-9._-]+)+[.]([a-zA-Z]{2,5}){1,25}$\" AND LENGTH(REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' )) > 0 AND REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' ) IS  NOT NULL AND REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' ) != '')";
                        $emailDup[] = "SELECT GROUP_CONCAT(CONCAT('$row ',row_id) SEPARATOR  ', ') AS err_rows, TRIM($tableName.`$col`) AS dup_value FROM `$tableName` INNER JOIN (SELECT TRIM(`$col`) AS dupvalue FROM $tableName WHERE is_removed=0 AND  LENGTH(REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' )) > 0 AND REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' ) IS  NOT NULL AND REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' ) != '' GROUP BY dupvalue HAVING count( dupvalue ) >1 )dup ON $tableName.`$col` = dup.dupvalue";
                        break;
                    case 'singleline':
                        $inCorrectSinglelineRows[] = "SELECT '$fieldTitle' AS column_name,'$headerRow[$col]' AS header_name, GROUP_CONCAT(CONCAT('$row ',row_id) SEPARATOR  ', ') AS err_rows FROM `$tableName` WHERE is_removed=0 AND  (LENGTH(`$col`) >" . $singleLineTextMaxLength . " AND LENGTH(REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' )) > 0 AND REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' ) IS  NOT NULL AND REPLACE( REPLACE( `$col` , '\n', '' ) , '\r', '' ) != '')";
                        break;
                }
            }
            if (count($manadatoryErrorRows) > 0) {
                $result = $this->conn->fetchAll(implode(' UNION ', $manadatoryErrorRows));
                if (count($result) > 0) {
                    foreach ($result as $errResult) {
                        if (!is_null($errResult['column_name']) && !is_null($errResult['err_rows'])) {
                            $errorRows[] = array('errorMessage' => $this->get('translator')->trans('IMPORT_NO_MANDATORY_VALUE_IN_ROWS'), 'headerColumn' => $errResult['header_name'], 'fieldname' => $errResult['column_name'], 'rows' => $errResult['err_rows']);
                        }
                    }
                }
            }
            if (count($inCorrectValueRows) > 0) {
                $result = $this->conn->fetchAll(implode(' UNION ', $inCorrectValueRows));
                if (count($result) > 0) {
                    foreach ($result as $errResult) {
                        if (!is_null($errResult['column_name']) && !is_null($errResult['err_rows'])) {
                            $errorRows[] = array('errorMessage' => $this->get('translator')->trans('IMPORT_INCORRECT_VALUE_IN_ROWS'), 'headerColumn' => $errResult['header_name'], 'fieldname' => $errResult['column_name'], 'rows' => $errResult['err_rows']);
                        }
                    }
                }
            }
            if (count($inCorrectLanguageRows) > 0) {
                $result = $this->conn->fetchAll(implode(' UNION ', $inCorrectLanguageRows));
                if (count($result) > 0) {
                    foreach ($result as $errResult) {
                        if (!is_null($errResult['column_name']) && !is_null($errResult['err_rows'])) {
                            $errorRows[] = array('errorMessage' => $this->get('translator')->trans('IMPORT_INCORRECT_LANGUAGE_IN_ROWS', array('%language%' => $availableLanguageError)), 'headerColumn' => $errResult['header_name'], 'fieldname' => $errResult['column_name'], 'rows' => $errResult['err_rows']);
                        }
                    }
                }
            }
            if (count($inCorrectEmailRows) > 0) {
                $result = $this->conn->fetchAll(implode(' UNION ', $inCorrectEmailRows));
                if (count($result) > 0) {
                    foreach ($result as $errResult) {
                        if (!is_null($errResult['column_name']) && !is_null($errResult['err_rows'])) {
                            $errorRows[] = array('errorMessage' => $this->get('translator')->trans('IMPORT_INCORRECT_EMAIL_IN_ROWS'), 'headerColumn' => $errResult['header_name'], 'fieldname' => $errResult['column_name'], 'rows' => $errResult['err_rows']);
                        }
                    }
                }
            }
            if (count($inCorrectSinglelineRows) > 0) {
                $result = $this->conn->fetchAll(implode(' UNION ', $inCorrectSinglelineRows));
                if (count($result) > 0) {
                    foreach ($result as $errResult) {
                        if (!is_null($errResult['column_name']) && !is_null($errResult['err_rows'])) {
                            $errorRows[] = array('errorMessage' => $this->get('translator')->trans('IMPORT_LENGTH_EXCEEDS_IN_ROWS'), 'headerColumn' => $errResult['header_name'], 'fieldname' => $errResult['column_name'], 'rows' => $errResult['err_rows']);
                        }
                    }
                }
            }
            if (count($emailDup) > 0) {
                $result = $this->conn->fetchAll(implode(' UNION ', $emailDup));
                if (count($result) > 0) {
                    foreach ($result as $errResult) {
                        if (!is_null($errResult['dup_value']) && !is_null($errResult['err_rows'])) {
                            $errorRows[] = array('errorMessage' => $this->get('translator')->trans('IMPORT_DUPLICATE_EMAIL_IN_ROWS'), 'headerColumn' => $errResult['header_name'], 'fieldname' => $primaryEmailTitle, 'rows' => $errResult['err_rows']);
                        }
                    }
                }
            }
            if (count($errorRows) == 0) {
                $isCorrectFile = true;
                $importValues['updateColumns'] = $updateColumns;
                $importValues['mappingTableInsertQuery'] = $mappingTableInsertQuery;
                $session->set('importFile' . $this->clubId, $importValues);
                $return['actionPath'] = $this->generateUrl('subscriber_import_duplcate');
            } else {
                $return['actionPath'] = $this->generateUrl('subscriber_import_file_correction');
            }
            $return['isCorrectFile'] = $isCorrectFile;
            $return['errorRows'] = $errorRows;
        }

        return $this->render('ClubadminCommunicationBundle:Subscriberimport:importFileCorrection.html.twig', $return);
    }

    /**
     * Function to get static fields
     *
     * @return array
     */
    private function getStaticFields()
    {
        $staticFields['email'] = array('title' => $this->get('translator')->trans('SUBSCRIBER_IMPORT_EMAIL'), 'type' => 'email');
        $staticFields['last_name'] = array('title' => $this->get('translator')->trans('SUBSCRIBER_IMPORT_SURNAME'), 'type' => 'singleline');
        $staticFields['first_name'] = array('title' => $this->get('translator')->trans('SUBSCRIBER_IMPORT_FORENAME'), 'type' => 'singleline');
        $staticFields['salutation'] = array('title' => $this->get('translator')->trans('SUBSCRIBER_IMPORT_SALUTATION'), 'type' => 'integer');
        $staticFields['gender'] = array('title' => $this->get('translator')->trans('SUBSCRIBER_IMPORT_GENDER'), 'type' => 'number');
        $staticFields['company'] = array('title' => $this->get('translator')->trans('SUBSCRIBER_IMPORT_COMPANY'), 'type' => 'singleline');
        $staticFields['correspondance_lang'] = array('title' => $this->get('translator')->trans('SUBSCRIBER_IMPORT_LANGUAGE'), 'type' => 'singleline');

        return $staticFields;
    }

    /**
     * Function to update import table
     *
     * @param type $federationId Federation Id
     * @param type $tableName    Table name
     * @param type $columns      Column
     */
    private function updateImportTable($federationId, $tableName, $columns)
    {
        $setValues = array();
        foreach ($columns as $column => $columnType) {
            switch ($columnType) {
                case 'salutation':
                    $setValues[] = "$column = IF($column ='1', 'Formal', 'Informal')";
                    break;
                case 'gender':
                    $setValues[] = "$column = IF($column ='1', 'Male', 'Female')";
                    break;
            }
        }
        if (count($setValues) > 0) {
            $this->conn->executeQuery("UPDATE `$tableName` SET " . implode(', ', $setValues));
        }
    }

    /**
     * To update import maping table
     *
     * @param String $tableName    table name
     * @param Array  $insertValues insert value
     */
    private function insertIntoMapingTable($tableName, $insertValues)
    {
        if (count($insertValues) > 0) {
            $this->conn->executeQuery("DELETE FROM import_maping WHERE imp_table ='$tableName'");
            $insertQuery = "INSERT INTO import_maping(col_imp, col_fg, col_title, fg_table, imp_table) VALUES " . implode(',', $insertValues);
            $this->conn->executeQuery($insertQuery);
        }
    }

    /**
     * Import/update duplicate action
     *
     * @return template
     */
    public function importDuplicateAction(Request $request)
    {
        $return = array();
        $session = $this->container->get('session');
        $importValues = $session->get('importFile' . $this->clubId);
        $tableName = $importValues['tableName'];
        $mapingFields = $importValues['mapingFields'];
        $tmpIsCompany = $importValues['contactType'] == 'single' ? false : true;
        $step = $request->request->get('step', '');
        $primaryEmail = $this->container->getParameter('system_field_primaryemail');
        $lastName = $this->container->getParameter('system_field_lastname');
        $firstName = $this->container->getParameter('system_field_firstname');
        $company = $this->container->getParameter('system_field_companyname');
        if ($step == 'file_correction') {
            $emailKey = '';
            $forenameKey = '';
            $surname = '';
            foreach ($mapingFields as $key => $field) {
                $emailKey = $field == 'email' ? $key : $emailKey;
                $forenameKey = $field == 'first_name' ? $key : $forenameKey;
                $surname = $field == 'last_name' ? $key : $surname;
            }
            $tmpEmail = array_search('email', $mapingFields);
            $tmpEmail = (is_numeric($tmpEmail) && !in_array($tmpEmail, $importValues['fieldSkipped']) ? $tmpEmail : false);

            if ($importValues['contactType'] == 'companyNoMain') {
                $tmpType = '3';
            } else {
                $tmpType = $tmpIsCompany ? '2' : '1';
            }
            $club = $this->container->get('club');
            $duplicateQuery = "SELECT tmp.row_id,tmp.column$emailKey AS tmpEmail,"
                . (is_numeric($forenameKey) ? " tmp.column$forenameKey " : "''") . " AS tmpForename,"
                . (is_numeric($surname) ? " tmp.column$surname " : "''") . " AS tmpSurname,"
                . " ms.`$company` AS company,cc.has_main_contact as hasMC,cc.is_company AS isCompany, ms.`$firstName` AS fname, ms.`$lastName` AS lname, ms.`$primaryEmail` AS email, cc.club_id AS clubId, ms.fed_contact_id AS contactId, tmp.row_id AS tmpId "
                . "FROM $tableName AS tmp, master_system AS ms INNER JOIN fg_cm_contact AS cc ON ms.fed_contact_id= cc.fed_contact_id AND cc.is_permanent_delete=0 AND is_draft=0";

            $duplicateWhere = array();
            if (is_numeric($tmpEmail)) {
                $emailEquals = "(tmp.column$emailKey!='' AND lower(tmp.column$emailKey) = lower(ms.`$primaryEmail`))";
                $duplicateWhere[] = $emailEquals;
            }
            if (count($duplicateWhere) > 0) {
                //FAIR-1967 - fed admin email validation
                $result = $this->conn->fetchAll($duplicateQuery . ' WHERE tmp.is_removed=0 AND (' . implode(' OR ', $duplicateWhere) . ') ' . " AND (cc.club_id = $this->clubId OR (cc.club_id = $this->federationId and  cc.is_fed_admin = 1 ) ) ");
            }
            $subscriberEmailQry = "SELECT tmp.row_id,tmp.column$emailKey AS tmpEmail,"
                . (is_numeric($forenameKey) ? " tmp.column$forenameKey " : "''") . " AS tmpForename,"
                . (is_numeric($surname) ? " tmp.column$surname " : "''") . " AS tmpSurname,"
                . "cs.first_name,cs.last_name,cs.email FROM $tableName AS tmp,fg_cn_subscriber cs WHERE tmp.is_removed=0 AND cs.club_id=$this->clubId and lower(cs.email)=lower(tmp.column$emailKey)";
            $subscriberEmailDup = $this->conn->fetchAll($subscriberEmailQry);

            $return['actionPath'] = $this->generateUrl('subscriber_import_submit');
            $return['duplicates'] = $result;
            $return['subscriber_duplicates'] = $subscriberEmailDup;
            $return['clubId'] = $this->clubId;
        }

        return $this->render('ClubadminCommunicationBundle:Subscriberimport:importDuplicate.html.twig', $return);
    }

    /**
     * ImportAssignmentSubmit Action
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws type exception
     */
    public function importSubmitAction(Request $request)
    {
        $duplicateIds = $request->request->get('duplcates', array());
        $duplcatesSubs = $request->request->get('duplcates_subs', array());
        if ($request->getMethod() == 'POST') {
            $importValues = $this->container->get('session');
            $importDetails = $importValues->get('importFile' . $this->clubId);
            $tableName = $importDetails['tableName'];
            $updateColumns = $importDetails['updateColumns'];
            $mappingTableInsertQuery = $importDetails['mappingTableInsertQuery'];
            $club = $this->container->get('club');
            $federationId = $club->get('federation_id');
            foreach ($duplicateIds as $val) {
                $rowIdArray[] = $val;
            }
            foreach ($duplcatesSubs as $val) {
                $rowIdArray[] = $val;
            }
            if (!empty($rowIdArray)) {
                $this->conn->executeQuery("UPDATE `$tableName` SET `is_removed`=1 WHERE `row_id` IN(" . implode(', ', $rowIdArray) . ")");
            }
            $this->updateImportTable($federationId, $tableName, $updateColumns);
            $this->insertIntoMapingTable($tableName, $mappingTableInsertQuery);
            $ownTable = ($this->clubType == 'federation' || $this->clubType == 'sub_federation') ? 'master_federation_' . $this->clubId : 'master_club_' . $this->clubId;
            $this->conn->executeQuery("CALL importSubscribers('" . $importDetails['tableName'] . "','import_maping','" . $this->clubId . "','" . $this->contactId . "','" . "$ownTable')");
            
            return new JsonResponse(array('status' => 'SUCCESS', 'sync' => 1, 'redirect' => $this->generateUrl('subscriber_list'), 'flash' => $this->get('translator')->trans('SUBSCRIBERS_IMPORTED_SUCCESSFULLY')));
        }
        throw $this->createNotFoundException($this->clubTitle . ' have no access to this page');
    }

    /**
     * Function to removeFirstRow
     *
     * @param type $tableName
     */
    private function removeFirstRow($tableName)
    {
        $this->conn->executeQuery("UPDATE `$tableName` SET is_removed = 1 WHERE row_id=1");
    }
}
