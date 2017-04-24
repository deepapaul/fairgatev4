<?php

/**
 * TeamMemberController.
 *
 * This class is used for the creation and updation of team members from internal area.
 *
 * @author     pitsolutions.ch
 *
 * @version    Fairgate V4
 */
namespace Internal\TeamBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Common\UtilityBundle\Util\FgUtility;
use Symfony\Component\Intl\Intl;
use Common\UtilityBundle\Repository\Pdo\ContactPdo;
use Clubadmin\Util\Contactlist;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Clubadmin\ContactBundle\Util\ContactDetailsSave;
use Common\UtilityBundle\Form\FgFieldCategoryType;
use Common\UtilityBundle\Repository\Pdo\membershipPdo;

/**
 * This class is used for the creation and updation of team members.
 */
class TeamMemberController extends FgController
{

    /**
     * Function to create/edit team member.
     *
     * @Template("InternalTeamBundle:TeamMember:updateMember.html.twig")
     *
     * @param Request $request Request
     * @param int     $contact Contact id
     *
     * @return array $result Data array
     */
    public function updateMemberAction(Request $request, $type = 'team', $roleId = '0', $contact = false)
    {
        $this->checkUserIsAuthenticated($roleId);
        $objMembershipPdo = new membershipPdo($this->container);
        $contactService = $this->get('contact');
        if($contact!=false){
            $getEditable = $objMembershipPdo->isEditableTeamMember($contact,$roleId ,$contactService);
            if($getEditable==1){
              throw new AccessDeniedException();
            }
         }

        // Get create/edit form details.
        $commonData = $this->getCommonData($request, $type, $roleId, $contact);
        $result = $commonData['result'];
        $assinedRoles = $contactService->get('memberClubRoles');
        $roleType = ($type == 'team' ? 'teams' : 'workgroups');
        $result['roleName'] = ($roleId == '' ? '' : $assinedRoles[$roleType][$roleId]);
        $form = $this->getContactForm($request, $commonData['result']['contactType'], $contact, $commonData['formValues'], $commonData['fieldDetails'], $commonData['editData']);
        $result['form'] = $form->createView();

        return $result;
    }

    /**
     * Function to get common data for create,edit,save.
     *
     * @param object $request Request object
     * @param string $type    Role type
     * @param int    $roleId  Role id
     * @param int    $contact Contact id
     *
     * @return array $commonData Common data
     */
    private function getCommonData($request, $type, $roleId, $contact)
    {
        $federationId = $this->clubType == 'federation' ? $this->clubId : $this->federationId;
        $subfederationId = $this->clubType == 'sub_federation' ? $this->clubId : $this->subFederationId;
        $fieldLanguages = $this->getLanguageArray();
        // Get contact form details.
        $formValues1 = $this->getFormValues($request);
        $deletedFiles = $formValues1['deletedFiles'];
        // Get details of existing contact if editing a contact.
        $formValues2 = $this->getExistingContactDetails($request, $formValues1['fieldType'], $formValues1['formValues'], $contact, $type, $roleId);
        $editData = $formValues2['editData'];
        $fieldType = $formValues2['fieldType'];
        $formValues = $formValues2['formValues'];
        $mainContactId = ($request->getMethod() == 'POST') ? $request->request->get('mainContactId', '') : $formValues2['mainContactId'];
        $contactClubId = $formValues2['contactClubId'];
        $selectedMembership = $formValues2['selectedMembership'];
        // Get details of contact fields.
        $fieldDetails = $this->getContactFieldDetails($fieldType, $fieldLanguages, $selectedMembership, $contact, $deletedFiles, $type, $roleId);
        // Get data to be passed to form template.
        $result = $this->getFormDetails($request, $federationId, $subfederationId, $fieldType, $contact, $editData, $mainContactId, $type, $roleId);
        $commonData = array('contactClubId' => $contactClubId, 'fieldLanguages' => $fieldLanguages, 'fieldDetails' => $fieldDetails, 'editData' => $editData, 'result' => $result, 'formValues' => $formValues);

        return $commonData;
    }

    /**
     * Function to get form details.
     *
     * @param object $request         Request object
     * @param int    $federationId    Federation id
     * @param int    $subfederationId Sub-federation id
     * @param string $fieldType       Field type (Company/Single Person)
     * @param int    $contact         Contact id
     * @param array  $editData        Contact data
     * @param int    $mainContactId   Main contact id
     * @param string $type            Role type
     * @param int    $roleId          Role id
     *
     * @return array $result Form details
     */
    private function getFormDetails($request, $federationId, $subfederationId, $fieldType, $contact, $editData, $mainContactId, $type, $roleId)
    {
        $randomAssignNum = $request->request->get('randomAssignNum');
        $result = array('randomAssignNum' => $randomAssignNum, 'fedMembers' => $this->fedMembers . ':', 'ownClub' => false, 'contact' => false, 'mainContactId' => $mainContactId,
            'clubId' => $this->clubId, 'federationId' => $federationId, 'subfederationId' => $subfederationId, 'clubType' => $this->clubType, 'loggedContactId' => $this->contactId, 'module' => 'contact',);
        $result['contactType'] = $fieldType;
        if ($editData) {
            $result['ownClub'] = ($editData[0]['contactClubId'] == $this->clubId) ? true : false;
            $result['contact'] = $contact;
            $result['contactType'] = ($editData[0]['is_company'] == 1) ? 'Company' : 'Single person';
            $result['isSwitchable'] = 0;
        }
        $result['type'] = $type;
        $result['roleId'] = $roleId;

        return $result;
    }

    /**
     * Function to set default data to form values.
     *
     * @param object $request Request object
     *
     * @return array $result Form values array
     */
    private function getFormValues($request)
    {
        if ($request->getMethod() == 'POST') {
            $systemCategoryPersonal = $this->container->getParameterBag()->get('system_category_personal');
            $systemAddress = $this->container->getParameterBag()->get('system_category_address');
            $formValues = $request->request->get('fg_field_category');
            $formValues[$systemCategoryPersonal]['has_main_contact_address'] = $request->get('has_main_contact_address');
            $formValues[$systemAddress]['same_invoice_address'] = $request->get('same_invoice_address');
            $deletedFiles = $request->request->get('deletedFiles');
            $fieldType = $formValues['system']['contactType'];
            if ($fieldType == 'Company') {
                $mainId = $request->request->get('mainContactId', '');
                $mainContactNameTitle = $request->request->get('mainContactNameTitle', '');
                $formValues[$systemCategoryPersonal]['mainContactName'] = (empty($mainId)) ? '' : $mainContactNameTitle['title'][0];
            }
        } else {
            $formValues = false;
            $fieldType = 'Single person';
            $deletedFiles = '';
        }
        $result = array('fieldType' => $fieldType, 'formValues' => $formValues, 'deletedFiles' => $deletedFiles);

        return $result;
    }

    /**
     * Function to get data of existing contacts.
     *
     * @param object $request    Request object
     * @param string $fieldType  Field type
     * @param array  $formValues Form values
     * @param int    $contact    Contact id
     * @param string $type       Role type
     * @param int    $roleId     Role id
     *
     * @return array $result Existing data
     */
    private function getExistingContactDetails($request, $fieldType, $formValues, $contact, $type, $roleId)
    {
        $editData = false;
        $contactClubId = $this->clubId;
        $selectedMembership = '';
        $mainContactId = '';
        if ($contact != '0') {
            $editDetails = $this->tempContact($contact, $this->conn, $type, $roleId);
            $editDetails['switchable'] = false;

            // Function to get company contact details.
            $companyDetails = $this->getCompanyDetails($request, $editDetails, false, $formValues);
            $fieldType = $companyDetails['fieldType'];
            $formValues = $companyDetails['formValues'];
            $mainContactId = $companyDetails['mainContactId'];
            $editData = $companyDetails['editData'];

            $contactClubId = $editData[0]['contactClubId'];
            $selectedMembership['club'] = $editData[0]['club_membership_cat_id'];
            $selectedMembership['fed'] = $editData[0]['fed_membership_cat_id'];
        }
        $result = array('editData' => $editData, 'fieldType' => $fieldType, 'formValues' => $formValues, 'mainContactId' => $mainContactId, 'contactClubId' => $contactClubId, 'selectedMembership' => $selectedMembership);

        return $result;
    }

    /**
     * Function to get company details.
     *
     * @param object $request      Request object
     * @param array  $editData     Contact data
     * @param bool   $isSwitchable Whether contact type switchable or not
     * @param array  $formValues   Form values
     *
     * @return array $result Company details
     */
    private function getCompanyDetails($request, $editData, $isSwitchable, $formValues)
    {
        $fieldType = ($editData[0]['is_company'] == 1) ? 'Company' : 'Single person';
        if ($request->getMethod() == 'POST') {
            if ($isSwitchable) {
                $fieldType = $formValues['system']['contactType'];
            } else {
                $formValues['system']['contactType'] = $fieldType;
            }
            $mainContactId = $request->request->get('mainContactId', '');
        } else {
            if ($fieldType == 'Company' && !is_null($editData[0]['comp_def_contact'])) {
                $contactPdo = new ContactPdo($this->container);
                $editData[0]['mainContactName'] = $contactPdo->getContactName($editData[0]['comp_def_contact'], true);
                $editData[0]['mainContactFunction'] = $editData[0]['comp_def_contact_fun'];
                $mainContactId = $editData[0]['comp_def_contact'];
            }
        }
        $result = array('fieldType' => $fieldType, 'formValues' => $formValues, 'mainContactId' => $mainContactId, 'editData' => $editData);

        return $result;
    }

    /**
     * Function to get create/edit form.
     *
     * @param object $request      Request object
     * @param string $fieldType    Field type
     * @param int    $contact      Contact id
     * @param array  $formValues   Form values
     * @param array  $fieldDetails Field details
     * @param array  $editData     Contact existing data
     *
     * @return object $form Create/Edit form
     */
    private function getContactForm($request, $fieldType, $contact, $formValues, $fieldDetails, $editData)
    {
        $federationId = $this->clubType == 'federation' ? $this->clubId : $this->federationId;
        $subfederationId = $this->clubType == 'sub_federation' ? $this->clubId : $this->subFederationId;
        $typeSwitch = (($request->getMethod() == 'POST') && ($request->get('fieldType') == 1)) ? true : false;
        if (($fieldType == 'Company') && $typeSwitch) {
            $formValues = $this->updateMainContactType($formValues, $fieldDetails);
        }
        $groupRights = $this->get('contact')->get('clubRoleRightsGroupWise');
        $isSuperAdmin = ($this->get('contact')->get('isSuperAdmin') || (($this->get('contact')->get('isFedAdmin')) && ($this->container->get('club')->get('type') != 'federation'))) ? 1 : 0;
        $adminRights = $this->get('contact')->get('availableUserRights');
        $isClubAdmin = (in_array('ROLE_USERS', $adminRights)) ? 1 : 0;
        if (($contact != '0') && (array_key_exists('ROLE_GROUP_ADMIN', $groupRights) || array_key_exists('ROLE_CONTACT_ADMIN', $groupRights) || $isSuperAdmin || $isClubAdmin)) {
            $contactChanges = $this->em->getRepository('CommonUtilityBundle:FgCmChangeToconfirm')->getConfirmChangesOfContact($this->clubId, $contact);
        } else {
            $contactChanges = array();
        }
        $bookedModuleDetails = $this->get('club')->get('bookedModulesDet');
        $form = $this->createForm(FgFieldCategoryType::class, $fieldDetails, array('custom_value' => array('submittedData' => $formValues, 'editData' => $editData, 'container' => $this->container, 'bookedModuleDetails' => $bookedModuleDetails, 'federationId' => $federationId, 'subfederationId' => $subfederationId, 'module' => 'contact', 'contactChanges' => $contactChanges, 'isIntranet' => true)));

        return $form;
    }

    /**
     * Function to update main contact type.
     *
     * @param array $formValues   Form values
     * @param type  $fieldDetails Contact field details
     *
     * @return array $formValues Form values
     */
    private function updateMainContactType($formValues, $fieldDetails)
    {
        $containerParameterBag = $this->container->getParameterBag();
        $categoryCompany = $containerParameterBag->get('system_category_company');
        $categoryPersonal = $containerParameterBag->get('system_category_personal');
        $personalFields = $fieldDetails['fieldsArray'][$categoryPersonal]['values'];
        foreach ($formValues[$categoryPersonal] as $fieldId => $personalValue) {
            if (trim($personalValue) != '' && $personalFields[$fieldId]['isCompany']) {
                return $formValues;
            }
        }
        $formValues[$categoryCompany]['mainContact'] = 'noMain';

        return $formValues;
    }

    /**
     * Function to create/edit team member.
     *
     * @Template("InternalTeamBundle:TeamMember:updateMember.html.twig")
     *
     * @param Request $request Request
     * @param string  $type    Role type
     * @param int     $roleId  Role id
     * @param int     $contact Contact id
     *
     * @return JsonResponse/Template JsonResponse if successfully saved/Template if there is any error
     */
    public function saveMemberAction(Request $request, $type = 'team', $roleId = '0', $contact = false)
    {
        // Get create/edit form details.
        $commonData = $this->getCommonData($request, $type, $roleId, $contact);
        $result = $commonData['result'];
        $contactService = $this->get('contact');
        $assinedRoles = $contactService->get('memberClubRoles');
        $roleType = ($type == 'team' ? 'teams' : 'workgroups');
        $result['roleName'] = ($roleId == '' ? '' : $assinedRoles[$roleType][$roleId]);
        $form = $this->getContactForm($request, $commonData['result']['contactType'], $contact, $commonData['formValues'], $commonData['fieldDetails'], $commonData['editData']);
        $typeSwitch = (($request->getMethod() == 'POST') && ($request->get('fieldType') == 1)) ? true : false;
        if ($typeSwitch == false) {
            $form->handleRequest($request);
            if ($form->isSubmitted()) {
                if ($form->isValid()) {
                    $saveData = $this->saveContactDetails($request, $form, $commonData['contactClubId'], $commonData['fieldLanguages'], $contact, $commonData['fieldDetails'], $commonData['editData'], $roleId);
                    // Update assignment creations and mutations.
                    $this->updateCreationsAndMutations($contact, $saveData, $commonData, $request->get('currRoleId'), $type);

                    return new JsonResponse(array('status' => 'SUCCESS'));
                } else {
                    $result['isError'] = true;
                    $result['errorType'] = 0;
                }
            }
        }
        $result['form'] = $form->createView();

        return $result;
    }

    /**
     * Function to save contact assignment creations and mutations.
     *
     * @param int   $contact    Contact id
     * @param array $saveData   Data to be saved
     * @param array $commonData Common form data
     * @param int   $currRoleId Currently selected team id
     * @param int   $type       Role type (team/workgroup)
     */
    private function updateCreationsAndMutations($contact, $saveData, $commonData, $currRoleId, $type)
    {
        $isDraft = ($contact != '0') ? (($commonData['editData'][0]['is_draft'] == '1') ? 1 : 0) : 1;
        if ($isDraft) {
            $insertedContactId = $saveData['contactId'];
            $passedTeamFunctions = $saveData['teamfunctions'];
            // Save assignment creations.
            $this->em->getRepository('CommonUtilityBundle:FgCmChangeToconfirm')->saveAssignmentCreations($insertedContactId, $currRoleId, $this->clubId, $this->contactId, $passedTeamFunctions);
        } else {
            // Save mutation assignments.
            $this->saveMutationAssignments($contact, $saveData, $commonData, $currRoleId, $type);
        }
    }

    /**
     * Function to save assignments to mutation table.
     *
     * @param int   $contact    Contact id
     * @param array $saveData   Data to be saved
     * @param array $commonData Common form data
     * @param int   $currRoleId Currently selected team id
     * @param int   $type       Role type (team/workgroup)
     */
    private function saveMutationAssignments($contact, $saveData, $commonData, $currRoleId, $type)
    {
        $insertedContactId = $saveData['contactId'];
        $passedTeamFunctions = $saveData['teamfunctions'];
        $currentTeamFunctions = ($contact != '0') ? $commonData['editData']['0']['teamfunctions'] : array();
        $addedFunctions = array_diff($passedTeamFunctions, $currentTeamFunctions);
        $removedFunctions = array_diff($currentTeamFunctions, $passedTeamFunctions);
        $roleCatId = ($type == 'team') ? $this->clubTeamId : $this->clubWorkgroupId;
        if (count($addedFunctions) || count($removedFunctions)) {
            // Do save contact assignment and removal.
            $this->em->getRepository('CommonUtilityBundle:FgCmChangeToconfirm')->saveMutations($insertedContactId, $this->contactId, $this->clubId, $currRoleId, $addedFunctions, $removedFunctions, $roleCatId);
        }
    }

    /**
     * Save contact details.
     *
     * @param object $request        Request object
     * @param object $form           Create/edit form
     * @param int    $contactClubId  Contact club id
     * @param array  $fieldLanguages Club languages
     * @param int    $contactId      Contact id
     * @param array  $fieldDetails   Contact field details
     * @param array  $editData       Contact existing data
     *
     * @return array $return Result of saving
     */
    private function saveContactDetails($request, $form, $contactClubId, $fieldLanguages, $contactId, $fieldDetails, $editData, $roleId)
    {
        $formData = $request->request->get($form->getName());
        $files = $request->files->get($form->getName());
        $deletedFiles = $request->request->get('deletedFiles');
        $isDraft = $contactId ? (($editData[0]['is_draft'] == '1') ? 1 : 0) : 1;
        $uploadFormValues = $this->uploadFiles($files, $deletedFiles, $contactClubId, $formData, $request, $editData, $fieldDetails);
        $formDataValues = $this->setDefaultFormValues($request, $uploadFormValues, $fieldLanguages, $contactId, $editData);
        $formDataArray = array('formData' => $formDataValues, 'toConfirmData' => array());
        if ($isDraft == 0) {
            $formDataArray = $this->processFormData($request, $formDataValues);
        }
        $formValues = $formDataArray['formData'];

        $teamfunctions = $formValues['system']['teamfunctions'];
        unset($formValues['system']['teamfunctions']);
        //$this->em->getRepository('CommonUtilityBundle:FgCmAttribute')->saveContact($this->container, $this->clubDefaultSystemLang, $contactId, $fieldDetails, $this->get('club'), $formValues, $this->fedMemberships, explode(',', $deletedFiles), $editData, $this->contactId, array(), array(), 0, 'NULL', array(), $isDraft);
        $contact = new ContactDetailsSave($this->container, $fieldDetails, $editData, $contactId); //$container,$fieldDetails,$editData,$fedMemberships
        $contactIdNew = $contact->saveContact($formValues, explode(',', $deletedFiles), array(), array(), 0, 'NULL', array(), 1, 1);
        // Save contact data changes to be confirmed.
        if (count($formDataArray['toConfirmData'])) {
            $contactFields = $this->get('club')->get('allContactFields');
            $this->em->getRepository('CommonUtilityBundle:FgCmChangeToconfirm')->saveChangesToConfirm($formDataArray['toConfirmData'], $contactIdNew, $this->contactId, $this->clubId, $contactFields, $roleId);
        }
        $return = array('contactId' => $contactIdNew, 'teamfunctions' => $teamfunctions);

        return $return;
    }

    /**
     * Function to get data to be saved.
     *
     * @param object $request  Request object
     * @param array  $formData Form data array
     *
     * @return array $formDataValues Array of data to be saved.
     */
    private function processFormData($request, $formData)
    {
        unset($formData[21]);
        $toConfirmData = json_decode($request->get('toConfirmData'), true);
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
        foreach ($formData as $catId => $formDataArr) {
            if (count($formDataArr) == 0) {
                unset($formData[$catId]);
            }
        }
        $formDataValues = array('formData' => $formData, 'toConfirmData' => $toConfirmData);

        return $formDataValues;
    }

    /**
     * Function to set default values to form data.
     *
     * @param object $request        Request object
     * @param array  $formValues     Form values
     * @param array  $fieldLanguages Languages
     * @param int    $contactId      Contact id
     * @param array  $editData       Contact existing data
     *
     * @return array $formValues Form values
     */
    private function setDefaultFormValues($request, $formValues, $fieldLanguages, $contactId, $editData)
    {
        $container = $this->container->getParameterBag();
        $systemCategoryCommunication = $container->get('system_category_communication');
        $systemCategoryPersonal = $container->get('system_category_personal');
        $systemCategoryCompany = $container->get('system_category_company');
        $systemAddress = $container->get('system_category_address');

        $formValues[$systemCategoryPersonal]['mainContactName'] = $request->request->get('mainContactId');
        $formValues[$systemCategoryCompany]['has_main_contact_address'] = $request->request->get('has_main_contact_address');
        $formValues[$systemAddress]['same_invoice_address'] = $request->request->get('same_invoice_address', 0);
        if (count($fieldLanguages) == 1) {
            $formValues[$systemCategoryCommunication][$container->get('system_field_corress_lang')] = $this->clubDefaultLang;
        }
        if ($contactId) {
            $formValues['system']['contactType'] = ($editData[0]['is_company'] == 1) ? 'Company' : 'Single person';
        }

        return $formValues;
    }

    /**
     * Function to upload files.
     *
     * @param array  $files         Uploaded files
     * @param array  $deletedFiles  Deleted files
     * @param int    $contactClubId Contact club id
     * @param array  $formValues    Form values
     * @param object $request       Request object
     * @param array  $editData      Contact existing data
     *
     * @return array $formDataValues Form values
     */
    private function uploadFiles($files, $deletedFiles, $contactClubId, $formValues, $request, $editData, $fieldDetails)
    {
        $deletedFilesArray = explode(',', $deletedFiles);
        $systemCompanyLogo = $this->container->getParameterBag()->get('system_field_companylogo');
        $fileNamesArray = array();
        if (count($files) > 0) {
            $this->createContactFolders($contactClubId);
            foreach ($files as $category => $fields) {
                foreach ($fields as $fieldId => $fieldFile) {
                    $formValues[$category][$fieldId] = $this->get('fg.avatar')->uploadContactField($fieldFile, $fieldId);
                    $fileNamesArray[$fieldId] = $fieldFile->getClientOriginalName();
                }
            }
        }
        $formDataValues = $this->copyAddressFiles($request, $fileNamesArray, $contactClubId, $formValues, $deletedFilesArray, $editData, $fieldDetails);

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
    private function copyAddressFiles($request, $fileNamesArray, $contactClubId, $formValues, $deletedFilesArray, $editData, $fieldDetails)
    {
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
                        $fileName = FgUtility::getFilename($uploadPath, $corrFileName);
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
     * @param int   $corrAttrId        Attribute id of correspondence address field
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
     * Create required folders for contact section.
     *
     * @param int $clubId Club id
     */
    public function createContactFolders($clubId)
    {
        if (!is_dir('uploads')) {
            mkdir('uploads', 0700);
        }
        if (!is_dir('uploads/temp')) {
            mkdir('uploads/temp', 0700);
        }
        if (!is_dir('uploads/' . $clubId)) {
            mkdir('uploads/' . $clubId, 0700);
        }
        if (!is_dir('uploads/' . $clubId . '/contact')) {
            mkdir('uploads/' . $clubId . '/contact', 0700);
        }
        if (!is_dir('uploads/' . $clubId . '/contact/profilepic')) {
            mkdir('uploads/' . $clubId . '/contact/profilepic', 0700);
        }
        if (!is_dir('uploads/' . $clubId . '/contact/companylogo')) {
            mkdir('uploads/' . $clubId . '/contact/companylogo', 0700);
        }
        $folders = array('width_150', 'width_65');
        foreach ($folders as $folder) {
            if (!is_dir('uploads/' . $clubId . '/contact/profilepic/' . $folder)) {
                mkdir('uploads/' . $clubId . '/contact/profilepic/' . $folder, 0700);
            }
            if (!is_dir('uploads/' . $clubId . '/contact/companylogo/' . $folder)) {
                mkdir('uploads/' . $clubId . '/contact/companylogo/' . $folder, 0700);
            }
        }
    }

    /**
     * Function to get language array.
     *
     * @return array $fieldLanguages Languages
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
     * Function to get contact field details.
     *
     * @param string $fieldType          Contact type
     * @param array  $fieldLanguages     Languages
     * @param int    $selectedMembership Selected membership
     * @param int    $contact            Contact id
     * @param array  $deletedFiles       Deleted files
     * @param string $type               Role type
     * @param int    $roleId             Role id
     *
     * @return array $fieldDetails Contact field details
     */
    private function getContactFieldDetails($fieldType, $fieldLanguages, $selectedMembership, $contact, $deletedFiles, $type, $roleId)
    {
        // Get club details as array.
        $clubIdArray = $this->getClubArray();
        // Get club contact fields.
        $fieldDetails1 = $this->em->getRepository('CommonUtilityBundle:FgCmAttributeset')->getAllClubContactFields($clubIdArray, $this->conn, 0, $fieldType);
        //build contact field array
        $fieldDetails2 = $this->em->getRepository('CommonUtilityBundle:FgCmAttributeset')->fieldItrator($fieldDetails1);
        if ($type == 'team') {
            $teamFunctions = $this->em->getRepository('CommonUtilityBundle:FgRmFunction')->getAllTeamFunctionsOfAClub($this->clubTeamId, $this->clubDefaultLang, false, true);
        } else {
            $roleObj = $this->em->getRepository('CommonUtilityBundle:FgRmRole')->find($roleId);
            if($roleObj->getIsExecutiveBoard()){
                $dataResult = $this->em->getRepository('CommonUtilityBundle:FgRmFunction')->getExecBoardFunctionDetailsOfClub($this->clubId, $this->clubDefaultLang, $this->get('club')->get('clubHeirarchy'));
                $functionIdArr = array();
                foreach ($dataResult as $valArray) {

                    $functionIdArr[$valArray['id']] = $valArray['title'];
                }
                $teamFunctions = $functionIdArr;
            }else{
                $teamFunctions = $this->em->getRepository('CommonUtilityBundle:FgRmRoleFunction')->getRoleFunctions($roleId, $this->clubDefaultLang, false, true, true);

            }

        }
        $fieldDetailArray = array('fieldType' => $fieldType, 'memberships' => $this->getMembershipArray(), 'clubIdArray' => $clubIdArray, 'fedMemberships' => $this->fedMemberships, 'fullLanguageName' => $fieldLanguages, 'selectedMembership' => $selectedMembership, 'contactId' => $contact, 'deletedFiles' => $deletedFiles, 'teamFunctions' => $teamFunctions, 'currContactId' => $this->contactId);
        //set terminology terms in contact field array
        $fieldDetails = array_merge($this->setTerminologyTerms($fieldDetails2), $fieldDetailArray);

        return $fieldDetails;
    }

    /**
     * Function to set terminology terms to contact detail array.
     *
     * @param array $fieldDetails Field details
     *
     * @return array $fieldDetails Field details
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
     * Function to get membership array.
     *
     * @return array $memberships Memberships
     */
    private function getMembershipArray()
    {
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
     * Function to get club details array.
     *
     * @return array $clubIdArray Club details
     */
    private function getClubArray()
    {
        $container = $this->container->getParameterBag();
        $clubIdArray = array('clubId' => $this->clubId,
            'federationId' => $this->federationId,
            'subFederationId' => $this->subFederationId,
            'clubType' => $this->clubType,
            'correspondanceCategory' => $container->get('system_category_address'),
            'invoiceCategory' => $container->get('system_category_invoice'),);
        $clubIdArray['address'] = $this->get('translator')->trans('CONTACT_FIELD_ADDRESS');
        $clubIdArray['sysLang'] = $this->clubDefaultLang;
        $clubIdArray['defSysLang'] = $this->clubDefaultSystemLang;
        $clubIdArray['clubLanguages'] = $this->clubLanguages;

        return $clubIdArray;
    }

    /**
     * Function to get details of existing contact.
     *
     * @param int    $contactId Contact id
     * @param object $conn      Connection object
     * @param string $type      Role type
     * @param int    $roleId    Role id
     *
     * @return array $fieldsArray Contact data array
     */
    private function tempContact($contactId, $conn, $type, $roleId)
    {
        $club = $this->get('club');
        $contactlistClass = new Contactlist($this->container, $contactId, $club, 'draft');
        $contactlistClass->setColumns('*');
        $contactlistClass->setFrom('*');
        $contactlistClass->setCondition();
        $sWhere = " fg_cm_contact.id=$contactId";
        $contactlistClass->addCondition($sWhere);
        $listquery = $contactlistClass->getResult();
        $fieldsArray = $conn->fetchAll($listquery);
        $fieldsArray[0]['is_sponsor'] = $fieldsArray[0]['sponsorFlag'];

        // Get contact assigned functions of given role.
        $dataArray = $this->getAssignedFunctionDetails($fieldsArray, $contactId, $type, $roleId, $fieldsArray[0]['is_draft']);

        return $dataArray;
    }

    /**
     * Function to get assigned function details of a contact for a given role.
     *
     * @param array  $fieldsArray    Contact data array
     * @param int    $contactId      Contact id
     * @param string $type           Role type (team/workgroup)
     * @param int    $roleId         Role id
     * @param bool   $isDraftContact Whether contact is draft or not
     *
     * @return array $fieldsArray Data array
     */
    private function getAssignedFunctionDetails($fieldsArray, $contactId, $type, $roleId, $isDraftContact)
    {
        $mutationType = $isDraftContact ? 'creation' : 'mutation';
        if ($type == 'team') {
            $contactAssignments = $this->em->getRepository('CommonUtilityBundle:FgRmRoleContact')->getContactAssignmentsOfCat($contactId, $this->clubTeamId, '', $roleId, false, true);
            $savedAssignments = $this->em->getRepository('CommonUtilityBundle:FgRmRoleContact')->getContactAssignmentsOfCat($contactId, $this->clubTeamId, '', $roleId, '', true);
        } else {
            $contactAssignments = $this->em->getRepository('CommonUtilityBundle:FgRmRoleContact')->getContactAssignmentsOfCat($contactId, $this->clubWorkgroupId, '', $roleId, false, true, false);
            $savedAssignments = $this->em->getRepository('CommonUtilityBundle:FgRmRoleContact')->getContactAssignmentsOfCat($contactId, $this->clubWorkgroupId, '', $roleId, false, false, false);
        }
        $teamFunctions = is_array($contactAssignments[$contactId]['functionids']) ? $contactAssignments[$contactId]['functionids'] : array();
        $savedFunctions = is_array($savedAssignments[$contactId]['functionids']) ? $savedAssignments[$contactId]['functionids'] : array();
        $mutationFunctions = $this->em->getRepository('CommonUtilityBundle:FgCmChangeToconfirmFunctions')->getMutationFunctions($contactId, $roleId, $this->clubId, $this->contactId, $mutationType);
        $assignments = array_merge($teamFunctions, $mutationFunctions);
        $fieldsArray[0]['teamfunctions'] = $assignments;
        $fieldsArray[0]['savedFunctions'] = $savedFunctions;

        return $fieldsArray;
    }

    /**
     * Function to check whether the logged-in user have the right to create/edit team/workgroup member.
     *
     * @param int $roleId Selected team/workgroup id
     *
     * @throws AccessDeniedException
     */
    private function checkUserIsAuthenticated($roleId)
    {
        if ($this->contactId != 1) {
            $userRights = array('ROLE_GROUP_ADMIN', 'ROLE_CONTACT_ADMIN');
            $roleRights = $this->container->get('contact')->checkClubRoleRights($roleId, false);
            $userRightsIntersect = array_intersect($userRights, $roleRights);
            if (count($userRightsIntersect) == 0) {
                throw new AccessDeniedException();
            }
        }
    }
}
