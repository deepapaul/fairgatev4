<?php

namespace Internal\ProfileBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Clubadmin\Util\Contactlist;
use Symfony\Component\Intl\Intl;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Repository\Pdo\ContactPdo;
use Common\UtilityBundle\Util\FgPermissions;
use Clubadmin\ContactBundle\Util\ContactDetailsSave;
use Common\UtilityBundle\Form\FgDataFieldCategoryType;
use Common\UtilityBundle\Repository\Pdo\membershipPdo;

class MydataController extends FgController
{

    /**
     * Action to display the data of user.
     *
     * @Template("InternalProfileBundle:Mydata:index.html.twig")
     *
     * @return array
     */
    public function indexAction()
    {
        $club = $this->get('club');
        $mainAdminRightsForFrontend = $this->container->get('contact')->get('mainAdminRightsForFrontend');
        if (in_array('ROLE_SUPER', $mainAdminRightsForFrontend) || (($club->get('federation_id') != $club->get('id') && in_array('ROLE_FED_ADMIN', $mainAdminRightsForFrontend) ))) {
            $permissionObj = new FgPermissions($this->container);
            $permissionObj->checkUserAccess(0, 'no_access');
        }
        $contact = $this->contactId;
        $formDetails = $this->getFormDetails();
        $dataResult = $formDetails['dataResult'];
        $bookedModulesDet = $this->container->get('club')->get('bookedModulesDet');
        $contactChanges = $this->em->getRepository('CommonUtilityBundle:FgCmChangeToconfirm')->getConfirmChangesOfContact($this->clubId, $contact);
        $form1 = $this->createForm(FgDataFieldCategoryType::class, $formDetails['attrfldDetails'], array('custom_value' => array('dataformValues' => false, 'existingData' => $formDetails['existingData'], 'containerParameters' => $this->container->getParameterBag(), 'bookedModulesDet' => $bookedModulesDet, 'container' => $this->container, 'isIntranet' => true, 'contactChanges' => $contactChanges)));
        $dataResult['form'] = $form1->createView();
        return $dataResult;
    }

    /**
     * Function to get form details.
     *
     * @return array $dataResult Form details
     */
    private function getFormDetails()
    {
        $contact = $this->contactId;
        $this->fedMembers = $mainContactId = '';
        $this->fedMemberships = $catTitlesarray = $otherformFields = array();
        // Get user data.
        $existingData = $this->tempContact($contact, 'contact');
        $contactDetails = array('contactDetails' => $existingData);
        $fieldType = ($existingData[0]['is_company'] == 1) ? 'Company' : 'Single person';

        if ($fieldType == 'Company' && !is_null($existingData[0]['comp_def_contact'])) {
            $contactPdo = new ContactPdo($this->container);
            $existingData[0]['mainContactName'] = $contactPdo->getContactName($existingData[0]['comp_def_contact'], true);
            $existingData[0]['mainContactFunction'] = $existingData[0]['comp_def_contact_fun'];
            $mainContactId = $existingData[0]['comp_def_contact'];
        }
        // Get club details array.
        $isReadOnly = 0;
        $attrfldDetails = $this->getAttributeFieldDetails($fieldType, $isReadOnly, $contact, $existingData);
        //get field category title for tab display
        $catTitlesarray = $this->getCatTitles($attrfldDetails['fieldsArray'], $fieldType);
        $contactName = $this->getCurrentContactName();
        $tabs = array(0 => array("text" => $this->get('translator')->trans('INTERNAL_OVERVIEW_TAB_TITLE'), "url" => $this->generateUrl('internal_dashboard')), 1 => array("text" => $this->get('translator')->trans('INTERNAL_DATA_TAB_TITLE'), "url" => $this->generateUrl('internal_mydata')), 2 => array("text" => $this->get('translator')->trans('INTERNAL_SETTINGS_TAB_TITLE'), "url" => $this->generateUrl('internal_privacy_settings')));
        $resultArray = array('contactType' => $fieldType, 'isReadOnly' => $isReadOnly, 'catTitlesarray' => $catTitlesarray, 'contact' => $contact, 'mainContactId' => $mainContactId, 'activeTab' => '', 'clubId' => $this->clubId, 'clubName' => $this->clubTitle, 'contactName' => $contactName, 'tabs' => $tabs);
        $dataResult = array_merge($this->setDataSetArray($contactDetails, $existingData, $contact, 'contact'), $resultArray);
        if ($dataResult['mainContactVisible']) {
            $dataResult['mainContactId'] = ($dataResult['mainContactVisible'] == '1') ? $mainContactId : $dataResult['mainContactVisible'];
        }
        $formDetails = array('dataResult' => $dataResult, 'existingData' => $existingData, 'attrfldDetails' => $attrfldDetails);

        return $formDetails;
    }

    /**
     * Function to get current logged-in contact name.
     *
     * @return string $contactName Contact name.
     */
    private function getCurrentContactName()
    {
        $loggedContactId = $this->get('contact')->get('id');
        if ($loggedContactId == 1) {
            $permissionObj = new FgPermissions($this->container);
            $permissionObj->checkUserAccess(0, 'no_access');
        }
        $contactDetails = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getNameOfAContact($loggedContactId);
        $contactName = $contactDetails[0]['name'];

        return $contactName;
    }

    /**
     * Function to get field details of attributes.
     *
     * @param string $fieldType    Contact field type
     * @param bool   $isReadOnly   Whether read only or not
     * @param int    $contact      Contact id
     * @param array  $existingData Contact existing data
     *
     * @return array $attrfldDetails Attribute field details
     */
    private function getAttributeFieldDetails($fieldType, $isReadOnly, $contact, $existingData)
    {
        $clubIdArray = $this->getClubArray();
        $fieldDetails1 = $this->em->getRepository('CommonUtilityBundle:FgCmAttributeset')->getAllClubContactFields($clubIdArray, $this->conn, 0, $fieldType, true, true);
        //to get all the attributes based on club
        $attrfldDetailsArray = $this->em->getRepository('CommonUtilityBundle:FgCmAttributeset')->fieldItrator($fieldDetails1);
        $memberships = $this->getMembershipArray();
        $fieldLanguages = $this->getLanguageArray();
        $selectedMembership['fed'] = $existingData['0']['fed_membership_cat_id'];
        $selectedMembership['club'] = $existingData['0']['club_membership_cat_id'];
        $attrArray = array('dragFiles' => array(), 'deleteddragFiles' => array(), 'deletedFiles' => array(), 'isReadOnly' => $isReadOnly, 'contactId' => $contact, 'fullLanguageName' => $fieldLanguages, 'fedMemberships' => $this->fedMemberships, 'selectedMembership' => $selectedMembership, 'clubIdArray' => $clubIdArray, 'memberships' => $memberships, 'fieldType' => $fieldType);
        $attrfldDetails = array_merge($this->setTerminologyTerms($attrfldDetailsArray), $attrArray);

        return $attrfldDetails;
    }

    /**
     * Function to save user data to change confirmation table.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse Array of saved status
     */
    public function saveUserDataAction(Request $request)
    {
        $formDetails = $this->getFormDetails();
        $validData = $this->checkFormValid($request, $formDetails);
        $isFormValid = $validData['isValid'];
        if ($isFormValid) {
            $saveData = json_decode($request->get('saveData'), true);
            $toConfirmData = $this->saveContactData($formDetails, $request, $saveData);
            $contactFields = $this->get('club')->get('allContactFields');
            $this->em->getRepository('CommonUtilityBundle:FgCmChangeToconfirm')->saveChangesToConfirm($toConfirmData, $this->contactId, $this->contactId, $this->clubId, $contactFields);
            $jsonResponse = array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('USER_DATA_SAVED_SUCCESSFULLY'));
            return new JsonResponse($jsonResponse);
        } else {
            $dataResult = $this->setPostFormParams($validData['dataResult'], $request);
            return $this->render('InternalProfileBundle:Mydata:index.html.twig', $dataResult);
        }
    }

    /**
     * Function to save non-confirmation contact fields.
     *
     * @param array  $formDetails   Form details
     * @param object $request       Request object
     * @param array  $toConfirmData Data to be confirmed
     *
     * @return array $toConfirmData Data to be confirmed
     */
    private function saveContactData($formDetails, $request, $toConfirmData)
    {
        $formDataArray = $request->get('fg_field_category');
        if (count($formDataArray)) {
            $otherformFields = array('contactType' => $formDetails['dataResult']['contactType']);

            // Set default form fields.
            $formData = $this->setDefaultFormFields($formDataArray, $formDetails, $request);

            // To handle normal file/image uploads.
            $files = $request->files->get('fg_field_category');
            $deletedFiles = explode(',', $request->request->get('deletedFiles'));
            $formValues = $this->uploadFiles($files, $deletedFiles, $this->clubId, $formData, $request, $formDetails);

            // To handle dropzone file uploads.
            $deleteddragFiles = $request->get('deleteddragFiles');
            $deleteddragFilesArray = ($deleteddragFiles != "") ? explode(',', $deleteddragFiles) : array();
            $dragFiles = $this->saveDropzoneImages($request, $formDetails, $deleteddragFilesArray);
            $otherformFields['dragFiles'] = $dragFiles;

            // Get contact data and toconfirm data for saving.
            $formDataValues = $this->processFormData($request, $formValues, $toConfirmData, $formDetails['attrfldDetails']);
            // Save contact data.
            $contact = new ContactDetailsSave($this->container, $formDetails['attrfldDetails'], $formDetails['existingData'], $this->contactId); //$container,$fieldDetails,$editData,$fedMemberships

            $contact->saveContact($formDataValues['formData'], explode(',', $deletedFiles), $otherformFields, $deleteddragFilesArray, 0, 'NULL', array(), 0, 1);
        }

        return $formDataValues['toConfirmData'];
    }

    /**
     * Function to set default form fields.
     *
     * @param array  $formData    Form data
     * @param array  $formDetails Form widget details
     * @param object $request     Request object
     *
     * @return array $formData Form data array.
     */
    private function setDefaultFormFields($formData, $formDetails, $request)
    {
        $formData[$this->container->getParameter('system_category_personal')]['mainContactName'] = $request->get('mainContactId');
        $formData[$this->container->getParameter('system_category_company')]['has_main_contact_address'] = $request->get('has_main_contact_address');
        $fieldLanguages = $formDetails['attrfldDetails']['fullLanguageName'];
        if (count($fieldLanguages) == 1) {
            $formData[$this->container->getParameter('system_category_communication')][$this->container->getParameter('system_field_corress_lang')] = $this->clubDefaultLang;
        }

        return $formData;
    }

    /**
     * Function to get data to be saved.
     *
     * @param object $request       Request object
     * @param array  $formData      Form data array
     * @param array  $toConfirmData Data to be confirmed
     * @param array  $fieldDetails  Contact field details
     *
     * @return array $formDataValues Array of data to be saved.
     */
    private function processFormData($request, $formData, $toConfirmData, $fieldDetails)
    {
        unset($formData[21]);
        $toConfirmFields = $request->get('toConfirmFields');
        if ($toConfirmFields != '') {
            $confirmFields = array_unique(explode(',', $toConfirmFields));
            foreach ($confirmFields as $confirmField) {
                $fieldData = explode('_', $confirmField);
                $catId = $fieldData[0];
                $attrId = $fieldData[1];
                if (isset($toConfirmData[$catId][$attrId])) {
                    $toConfirmData[$catId][$attrId] = is_array($formData[$catId][$attrId]) ? implode(';', $formData[$catId][$attrId]) : $formData[$catId][$attrId];
                }
                unset($formData[$catId][$attrId]);
            }
        }
        $formDataArray = $this->removeNonEditableFields($formData, $fieldDetails);
        $formDataValues = array('formData' => $formDataArray, 'toConfirmData' => $toConfirmData);
        $containerParameters = $this->container->getParameterBag();
        $systemAddress = $containerParameters->get('system_category_address');
        $formDataValues['formData'][$systemAddress]['same_invoice_address'] = $request->get('same_invoice_address');

        return $formDataValues;
    }

    /**
     * Function to remove non-editable fields from data array for saving.
     *
     * @param array $formData     Data array for saving
     * @param array $fieldDetails Contact field details
     *
     * @return array $formData Data array for saving
     */
    private function removeNonEditableFields($formData, $fieldDetails)
    {
        foreach ($formData as $catId => $formDataArr) {
            if (count($formDataArr) == 0) {
                unset($formData[$catId]);
            } else {
                foreach ($formDataArr as $attrId => $value) {
                    if ($fieldDetails['fieldsArray'][$catId]['values'][$attrId]['isChangableUser'] == '0') {
                        unset($formData[$catId][$attrId]);
                    }
                }
            }
        }

        return $formData;
    }

    /**
     * Function to save dropzone images.
     *
     * @param object $request               Request object
     * @param array  $formDetails           Form details
     * @param array  $deleteddragFilesArray Deleted drag files
     *
     * @return array $dragFileData Details of drag files.
     */
    private function saveDropzoneImages($request, $formDetails, $deleteddragFilesArray)
    {
        $dragFileData = array();
        $fieldType = $formDetails['dataResult']['contactType'];
        $dragFiles = $this->handleDropzoneImages($fieldType, $request);
        if (count($dragFiles) > 0) {
            $dragFileData = $this->uploadDragFiles($request, $dragFiles, $deleteddragFilesArray, $this->clubId, $formDetails['existingData']);
        }

        return $dragFileData;
    }

    /**
     * Upload Files
     *
     * @param array  $files             File array
     * @param array  $deletedFilesArray Array of deleted files
     * @param int    $contactClubId     Contact club id
     * @param array  $formData          Form data array
     * @param object $request           Request object
     * @param array  $formDetail        Contact existing data
     *
     * @return array $fileData Data array
     */
    private function uploadFiles($files, $deletedFilesArray, $contactClubId, $formData, $request, $formDetail)
    {
        $fileNamesArray = array();
        if (count($files) > 0) {
            $this->createContactFolder($contactClubId);
            foreach ($files as $category => $fields) {
                foreach ($fields as $fieldId => $fieldFile) {
                    $formData[$category][$fieldId] = $this->get('fg.avatar')->uploadContactField($fieldFile, $fieldId);
                    $fileNamesArray[$fieldId] = $fieldFile->getClientOriginalName();
                }
            }
        }
        $formDataValues = $this->copyAddressFiles($request, $fileNamesArray, $contactClubId, $formData, $deletedFilesArray, $formDetail);
        unset($formDataValues[21]);

        return $formDataValues;
    }

    /**
     * Function to copy the files in correspondence address category to invoice address category
     * if 'same_as_correspondence_address' is checked.
     *
     * @param object $request           Request object
     * @param array  $fileNamesArray    File names
     * @param int    $contactClubId     Contact club id
     * @param array  $formValues        Form data array
     * @param array  $deletedFilesArray Deleted files
     * @param array  $editData          Contact existing data
     *
     * @return array $formValues Form values
     */
    private function copyAddressFiles($request, $fileNamesArray, $contactClubId, $formValues, $deletedFilesArray, $formDetail)
    {
        $editData = $formDetail['existingData'];
        $duplicateFileAttrs = $request->get('duplicateFileAttrs');
        $invAddressCategory = $this->container->getParameterBag()->get('system_category_invoice');
        if ($duplicateFileAttrs != '') {
            $duplicateAttrs = explode(',', $duplicateFileAttrs);
            foreach ($duplicateAttrs as $duplicateAttr) {
                $duplicateAttrIds = explode('-', $duplicateAttr);
                $corrAttrId = $duplicateAttrIds['0'];
                $invAttrId = $duplicateAttrIds['1'];

                $uploadPathFrom = $this->get('fg.avatar')->getContactfieldPath($corrAttrId);
                $uploadPathTo = $this->get('fg.avatar')->getContactfieldPath($invAttrId);

                $corrFileName = $fileName = $this->getCorrespondenceAttrFilename($corrAttrId, $fileNamesArray, $deletedFilesArray, $editData);
                if ($corrFileName != '') {
                    // Copy files.
                    if (is_file($uploadPathFrom . '/' . $corrFileName)) {
                        $fileName = FgUtility::getFilename($uploadPathFrom, $corrFileName);
                        copy($uploadPathFrom . '/' . $corrFileName, $uploadPathTo . '/' . $fileName);
                    }
                }
                $formValues[$invAddressCategory][$invAttrId] = $fileName;
            }
        }

        return $formValues;
    }

    /**
     * Function to get filename of an attribute having file type.
     *
     * @param int $corrAttrId          Attribute id of correspondence address field
     * @param array $fileNamesArray    Uploaded file names
     * @param array $deletedFilesArray Deleted files
     * @param array $editData          Contact existing data
     *
     * @return string $corrFileName File name
     */
    private function getCorrespondenceAttrFilename($corrAttrId, $fileNamesArray, $deletedFilesArray, $editData)
    {
        $corrFileName = '';
        if (isset($fileNamesArray[$corrAttrId])) {
            $corrFileName = $fileNamesArray[$corrAttrId];
        } elseif (!in_array($corrAttrId, $deletedFilesArray)) {
            $corrFileName = $editData['0'][$corrAttrId];
        }

        return $corrFileName;
    }

    /**
     * Upload drag files.
     *
     * @param type $request
     * @param type $dragFiles
     * @param type $deleteddragFilesArray
     * @param type $contactClubId
     * @param type $existingData
     *
     * @return array drag file details
     */
    private function uploadDragFiles($request, $dragFiles, $deleteddragFilesArray, $contactClubId, $existingData)
    {
        $dragFileData = array();
        $systemCompanyLogo = $this->container->getParameter('system_field_companylogo');
        $contactClubId = $existingData[0]['created_club_id'];
        $this->createContactFolder($contactClubId);

        foreach ($dragFiles as $fileattr => $dragFilename) {
            if (!in_array($fileattr, $deleteddragFilesArray)) {
                if ($dragFilename != $existingData[0][$fileattr]) {
                    $fileType = ($fileattr == $systemCompanyLogo) ? 'companylogo' : 'profilepic';
                    $fileName = $request->get("dropzone_file", '');
                    $dragFileData[$fileattr] = $this->get('fg.avatar')->saveUserAndCompanyLogo($fileName, $fileType, $dragFilename);
                }
            }
        }

        return $dragFileData;
    }

    /**
     * function to create the contact upload folders
     *
     * @param int $clubId the club id
     */
    private function createContactFolder($clubId)
    {
        $folders = array('original', 'width_150', 'width_65');
        foreach ($folders as $folder) {
            $this->get('fg.avatar')->createUploadDirectories('uploads/' . $clubId . '/contact/profilepic/' . $folder, false);
            $this->get('fg.avatar')->createUploadDirectories('uploads/' . $clubId . '/contact/companylogo/' . $folder, false);
        }

        $this->get('fg.avatar')->createUploadDirectories('uploads/' . $clubId . '/contact/contactfield_file', false);
        $this->get('fg.avatar')->createUploadDirectories('uploads/' . $clubId . '/contact/contactfield_image', false);
        $this->get('fg.avatar')->createUploadDirectories('uploads/temp', false);
    }

    /**
     * Function to set form post parameters.
     *
     * @param array  $dataResult Data array
     * @param object $request    Request object
     *
     * @return array $dataResult Data array
     */
    private function setPostFormParams($dataResult, $request)
    {
        $dataResult['activeTab'] = $request->get('active_tab');

        return $dataResult;
    }

    /**
     * Check whether form is valid or not.
     *
     * @param object $request Request object
     *
     * @return bool Valid/not
     */
    private function checkFormValid($request, $formDetails)
    {
        $contact = $this->contactId;
        $dataformValues = $this->setPostFormValues($request, $contact, $formDetails['existingData']);
        $bookedModulesDet = $this->container->get('club')->get('bookedModulesDet');
        $contactChanges = $this->em->getRepository('CommonUtilityBundle:FgCmChangeToconfirm')->getConfirmChangesOfContact($this->clubId, $contact);
        // Contact data form.
        $form1 = $this->createForm(FgDataFieldCategoryType::class, $formDetails['attrfldDetails'], array('custom_value' => array('dataformValues' => $dataformValues, 'existingData' => $formDetails['existingData'], 'containerParameters' => $this->container->getParameterBag(), 'bookedModulesDet' => $bookedModulesDet, 'container' => $this->container, 'isIntranet' => true, 'contactChanges' => $contactChanges)));

        $form1->handleRequest($request);
        if ($form1->isSubmitted()) {
            if ($form1->isValid()) {
                return array('isValid' => true);
            } else {
                $dataResult = $formDetails['dataResult'];
                $dataResult['form'] = $form1->createView();
                return array('isValid' => false, 'dataResult' => $dataResult);
            }
        }

        return false;
    }

    /**
     * Function to set post parameters to form fields.
     *
     * @param object $request      Request object
     * @param int    $contact      Contact id
     * @param array  $existingData Existing data array
     *
     * @return array $dataformValues Form post parameters.
     */
    private function setPostFormValues($request, $contact, $existingData)
    {
        $systemPersonal = $this->container->getParameter('system_category_personal');
        $systemAddress = $this->container->getParameter('system_category_address');
        $dataformValues = $request->get('fg_field_category');
        $mainContactId = $request->request->get('mainContactId', '');
        if ($contact) {
            $fieldType = ($existingData[0]['is_company'] == 1) ? 'Company' : 'Single person';
            if ($fieldType == 'Company' && empty($mainContactId)) {
                $dataformValues[$systemPersonal]['mainContactName'] = '';
            } elseif ($fieldType == 'Company') {
                $mainContactNameTitle = $request->request->get('mainContactNameTitle', '');
                $dataformValues[$systemPersonal]['mainContactName'] = $mainContactNameTitle['title'][0];
            }
        }
        $dataformValues[$systemAddress]['same_invoice_address'] = $request->get('same_invoice_address', 0);
        $dataformValues[$systemPersonal]['has_main_contact_address'] = $request->get('has_main_contact_address');

        return $dataformValues;
    }

    /**
     * Function to get membership array
     *
     * @return type
     */
    private function getMembershipArray()
    {
        //to manage the membership category dropdown based on federation/club/sub-fed
        $objMembershipPdo = new membershipPdo($this->container);
        $membersipFields = $objMembershipPdo->getMemberships($this->clubType, $this->clubId, $this->subFederationId, $this->federationId);
        $this->fedMemberships = array();
        $this->fedMembers = '';
        $clubId = ($this->clubType == 'federation') ? $this->clubId : $this->federationId;
        $memberships = array();
        foreach ($membersipFields as $key => $memberCat) {
            if (($memberCat['clubId'] == $clubId)) {
                $this->fedMemberships[] = $key;
                $this->fedMembers .= ':' . $key;
                $memberships['fed'][$key] = empty($memberCat['titleLang']) ? $memberCat['membershipName'] : $memberCat['titleLang'];
            } else {
                $memberships['club'][$key] = empty($memberCat['titleLang']) ? $memberCat['membershipName'] : $memberCat['titleLang'];
            }
        }

        return $memberships;
    }

    /**
     * set resultset array
     *
     * @param type  $dataResult
     * @param type  $existingData
     * @param int $contact contact id
     * @param string $pagetype sponsor or contact
     * @return type array
     */
    private function setDataSetArray($dataResult, $existingData, $contact, $pagetype)
    {
        $dataResult['ownClub'] = ($existingData[0]['contactClubId'] == $this->clubId) ? true : false;
        $dataResult['clubId'] = $this->clubId;
        $dataResult['isFrontend1Booked'] = $this->isFrontend1Booked($this->clubId);
        $dataResult['fedMembers'] = $this->fedMembers . ':';
        $dataResult['fedmembership'] = $this->fedMemberships;
        $entityManager = $this->getDoctrine()->getManager();
        $dataResult['federationId'] = $this->clubType == "federation" ? $this->clubId : $this->federationId;
        $dataResult['fedlogoPath'] = FgUtility::getClubLogo($dataResult['federationId'], $entityManager);
        $dataResult['subfederationId'] = $this->clubType == "sub_federation" ? $this->clubId : $this->subFederationId;
        $dataResult['subfedlogoPath'] = FgUtility::getClubLogo($dataResult['subfederationId'], $entityManager);
        $dataResult['contactClubId'] = $existingData[0]['contactClubId'];
        $dataResult['isArchive'] = $existingData[0]['is_deleted'];
        $dataResult['is_sponsor'] = $existingData[0]['is_sponsor'];
        $dataResult['is_stealth_mode'] = $existingData[0]['is_stealth_mode'];
        $dataResult['intranet_access'] = $existingData[0]['intranet_access'];
        $dataResult['mainContactVisible'] = false;
        if ($existingData[0]['is_company'] == 1 && $existingData[0]['has_main_contact'] == 1 && !empty($existingData[0]['comp_def_contact'])) {
            $contactpdo = new ContactPdo($this->container);
            $mainContactVisible = $contactpdo->getClubContactId($existingData[0]['comp_def_contact'], $this->clubId);
            if ($mainContactVisible) {
                $dataResult['mainContactVisible'] = $mainContactVisible['id'];
            }
        } elseif (empty($existingData[0]['comp_def_contact'])) {
            $dataResult['mainContactVisible'] = true;
        }
        if ($pagetype == 'contact') {
            $dataResult['missingReqAssgment'] = $this->em->getRepository('CommonUtilityBundle:FgRmCategory')->missingReqFedAssign($contact, $this->clubId, $this->federationId, $this->subFederationId, $this->clubType, $this->clubDefaultLang, $this->conn);
            $groupUserDetails = $this->em->getRepository('CommonUtilityBundle:SfGuardGroup')->getGroupDetails($this->conn, $this->clubId, $contact);
            $dataResult['hasUserRights'] = (count($groupUserDetails) > 0) ? 1 : 0;
        }

        return $dataResult;
    }

    /**
     * Function get category title for tab
     * @param type $fieldsArray table fields
     * @param type $fieldType
     *
     * @return int
     */
    private function getCatTitles($fieldsArray, $fieldType)
    {
        $fedId = $this->clubType == "federation" ? $this->clubId : $this->federationId;
        $subFedId = $this->clubType == "sub_federation" ? $this->clubId : $this->subFederationId;
        $systemPersonal = $this->container->getParameter('system_category_personal');
        $systemCompany = $this->container->getParameter('system_category_company');
        $systemAddress = $this->container->getParameter('system_category_address');
        $systemCompanyLogo = $this->container->getParameter('system_field_companylogo');
        $systemCommunityPicture = $this->container->getParameter('system_field_communitypicture');
        //to handle the tab titles
        $catTitlesarray = $this->loopCatTitles($fieldsArray, $fieldType, $systemPersonal, $fedId, $subFedId, $systemCompany, $systemAddress);
        return $this->setCatTitlesOfFileType($catTitlesarray, $fieldType, $systemCompanyLogo, $systemCommunityPicture);
    }

    /**
     * Function to loop category titles.
     *
     * @param array  $fieldsArray    Contact fields array
     * @param string $fieldType      Contact type
     * @param int    $systemPersonal Personal category id
     * @param int    $fedId          Federation id
     * @param int    $subFedId       Sub federation id
     * @param int    $systemCompany  Company category id
     * @param int    $systemAddress  Address category id
     *
     * @return array $catTitlesarray Array of category titles
     */
    private function loopCatTitles($fieldsArray, $fieldType, $systemPersonal, $fedId, $subFedId, $systemCompany, $systemAddress)
    {
        $catTitlesarray = array();
        foreach ($fieldsArray as $value) {
            if ((count($value['values']) > 0) && !($fieldType == 'Company' && $value['catId'] == $systemPersonal)) {
                if ($value['isSystem'] == 1 || $value['isFairgate'] == 1) {
                    $catTitlesarray[$value['catId']]['title'] = (isset($value['titles'][$this->clubDefaultSystemLang])) ? $value['titles'][$this->clubDefaultSystemLang] : $value['title'];
                } else {
                    $catTitlesarray[$value['catId']]['title'] = (isset($value['titles'][$this->clubDefaultLang])) ? $value['titles'][$this->clubDefaultLang] : $value['title'];
                }
                $catTitlesarray[$value['catId']]['fedFlag'] = $fedId == $value['catClubId'] ? 1 : 0;
                $catTitlesarray[$value['catId']]['subfedFlag'] = $subFedId == $value['catClubId'] ? 1 : 0;

                if ($value['catId'] == $systemCompany) {
                    $catTitlesarray[$value['catId']]['title'] = $this->get('translator')->trans('COMPANY');
                } else if ($value['catId'] == $systemAddress) {
                    $catTitlesarray[$value['catId']]['title'] = $this->get('translator')->trans('CONTACT_FIELD_ADDRESS');
                }
                $catTitlesarray[$value['catId']]['fedFlag'] = 0;
                $catTitlesarray[$value['catId']]['subfedFlag'] = 0;
            }
        }

        return $catTitlesarray;
    }

    /**
     * Function to set category titles for file type fields.
     *
     * @param array  $catTitlesarray         Category title array
     * @param string $fieldType              Contact type
     * @param int    $systemCompanyLogo      Company logo attribute id
     * @param int    $systemCommunityPicture Community pic attribute id
     *
     * @return array $catTitlesarray Category title array
     */
    private function setCatTitlesOfFileType($catTitlesarray, $fieldType, $systemCompanyLogo, $systemCommunityPicture)
    {
        if ($fieldType == 'Company') {
            $catTitlesarray[$systemCompanyLogo]['title'] = $this->get('translator')->trans('DATA_COMPANY_LOGO');
            $catTitlesarray[$systemCompanyLogo]['fedFlag'] = 0;
            $catTitlesarray[$systemCompanyLogo]['subfedFlag'] = 0;
        } else if ($fieldType == 'Single person') {
            $catTitlesarray[$systemCommunityPicture]['title'] = $this->get('translator')->trans('DATA_PROFILE_PICTURES');
            $catTitlesarray[$systemCommunityPicture]['fedFlag'] = 0;
            $catTitlesarray[$systemCommunityPicture]['subfedFlag'] = 0;
        }

        return $catTitlesarray;
    }

    /**
     * set terminology terms to contact detail array
     *
     * @param type $fieldDetails
     *
     * @return array terminology details
     */
    private function setTerminologyTerms($fieldDetails)
    {
        $containerParameters = $this->container->getParameterBag();
        $profilePictureTeam = $containerParameters->get('system_field_team_picture');
        $profilePictureClub = $containerParameters->get('system_field_communitypicture');
        $terminologyService = $this->get('fairgate_terminology_service');
        $termi21 = $terminologyService->getTerminology('Club', $containerParameters->get('singular'));
        $termi5 = $terminologyService->getTerminology('Team', $containerParameters->get('singular'));
        if (isset($fieldDetails['attrTitles'][$profilePictureTeam][$this->clubDefaultSystemLang])) {
            $fieldDetails['attrTitles'][$profilePictureTeam][$this->clubDefaultSystemLang] = str_replace('%Team%', ucfirst($termi5), $fieldDetails['attrTitles'][$profilePictureTeam][$this->clubDefaultSystemLang]);
        }
        if (isset($fieldDetails['attrTitles'][$profilePictureClub][$this->clubDefaultSystemLang])) {
            $fieldDetails['attrTitles'][$profilePictureClub][$this->clubDefaultSystemLang] = str_replace('%Club%', ucfirst($termi21), $fieldDetails['attrTitles'][$profilePictureClub][$this->clubDefaultSystemLang]);
        }

        return $fieldDetails;
    }

    /**
     * get club details array
     *
     * @return type array
     */
    private function getClubArray()
    {
        $container = $this->container->getParameterBag();
        $clubIdArray = array('clubId' => $this->clubId,
            'federationId' => $this->federationId,
            'subFederationId' => $this->subFederationId,
            'clubType' => $this->clubType,
            'correspondanceCategory' => $container->get('system_category_address'),
            'invoiceCategory' => $container->get('system_category_invoice'));
        $clubIdArray['address'] = $this->get('translator')->trans('CONTACT_FIELD_ADDRESS');
        $clubIdArray['sysLang'] = $this->clubDefaultLang;
        $clubIdArray['defSysLang'] = $this->clubDefaultSystemLang;
        $clubIdArray['clubLanguages'] = $this->clubLanguages;

        return $clubIdArray;
    }

    /**
     * Get languages array
     *
     * @return type
     */
    private function getLanguageArray()
    {
        $languages = Intl::getLanguageBundle()->getLanguageNames();
        $fieldLanguages = array();
        foreach ($this->clubLanguages as $shortName) {
            $fieldLanguages[$shortName] = $languages[$shortName];
        }

        return $fieldLanguages;
    }

    /**
     * function to get the contact details of particular contact's
     *
     * @param int    $contactId contact id
     * @param String $type      contact type
     *
     * @return array
     */
    private function tempContact($contactId, $type = 'contact')
    {
        if (!is_numeric($contactId)) {
            return false;
        }
        $club = $this->get('club');
        $contactlistClass = new Contactlist($this->container, $contactId, $club, $type);
        $contactlistClass->setColumns('*');
        $contactlistClass->setFrom('*');
        $contactlistClass->setCondition();
        $sWhere = " fg_cm_contact.id=$contactId";
        $contactlistClass->addCondition($sWhere);
        $listquery = $contactlistClass->getResult();
        $fieldsArray = $this->container->get('database_connection')->fetchAll($listquery);
        $fieldsArray[0]['is_sponsor'] = $fieldsArray[0]['sponsorFlag'];

        return $fieldsArray;
    }

    /**
     * Function to handle dropzone image array
     *
     * @param string $fieldType Contact type
     * @param object $request   Request object
     *
     * @return array $dragFiles Drag files
     */
    private function handleDropzoneImages($fieldType, $request)
    {
        $dragFiles = array();
        $container = $this->container->getParameterBag();
        $systemCompanyLogo = $container->get('system_field_companylogo');
        $profilePictureClub = $container->get('system_field_communitypicture');
        //to handle the dropzone images
        if ($fieldType == 'Company') {
            if ($request->get("picture_$systemCompanyLogo")) {
                $dragFiles[$systemCompanyLogo] = $request->get("picture_$systemCompanyLogo", "");
            }
        } else {
            if ($request->get("picture_$profilePictureClub")) {
                $dragFiles[$profilePictureClub] = $request->get("picture_$profilePictureClub", "");
            }
        }

        return $dragFiles;
    }

    /**
     * Function is used for searching contacts having given text in contact names.
     *
     * @return array $contactsData Contacts array
     */
    public function searchAction(Request $request)
    {
        $term = $request->get('term');
        $contactId = $request->get('contactId', 0);
        $isCompany = $request->get('isCompany', 'ALL');
        $contactType = 'ALL';
        if ($isCompany != 'ALL') {
            $contactType = ($isCompany == '0') ? 'SINGLE' : 'COMPANY';
        }
        $contactPdo = new ContactPdo($this->container);
        $contactsData = $contactPdo->searchContact($term, $this->clubId, $contactId, $contactType);

        return new JsonResponse($contactsData);
    }
}
