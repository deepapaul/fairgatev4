<?php

namespace Clubadmin\ContactBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Intl\Intl;
use Common\UtilityBundle\Controller\FgController as ParentController;
use Common\UtilityBundle\Repository\Pdo\ContactPdo;
use Clubadmin\Util\Contactlist;
use Clubadmin\ContactBundle\Util\ContactDetailsAccess;
use Clubadmin\ContactBundle\Util\ContactDetailsSave;
use Clubadmin\ContactBundle\Util\FgContactValidator;
use Symfony\Component\HttpFoundation\Request;
use Common\UtilityBundle\Repository\Pdo\membershipPdo;
use Common\UtilityBundle\Util\FgPermissions;

/**
 * contact default controller.
 */
class DefaultController extends ParentController
{

    public $fedMemberships;

    /**
     * default function.
     *
     * @return type
     */
    public function indexAction()
    {
        return $this->render('ClubadminContactBundle:Default:index.html.twig');
    }

    /**
     * Create edit contact action.
     *
     * @param type $contact
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function createContactAction(Request $request, $module, $contact = false)
    {
        $containerParameters = $this->container->getParameterBag();
        $federationId = $this->clubType == 'federation' ? $this->clubId : $this->federationId;
        $subfederationId = $this->clubType == 'sub_federation' ? $this->clubId : $this->subFederationId;
        $bookedModuleDetails = $this->get('club')->get('bookedModulesDet');
        $errorType = $errorArray = $mainContactId = $selectedMembership = '';
        $fieldLanguages = $this->getLanguageArray();
        $contactClubId = $this->clubId;
        $systemCategoryAddress = $containerParameters->get('system_category_address');
        $systemCategoryPersonal = $containerParameters->get('system_category_personal');
        $systemCategoryCompany = $containerParameters->get('system_category_company');
        $editData = false;
        $formValues = $request->request->get('fg_field_category');
        $moduleType = $this->get('club')->get('moduleMenu');     
        $redirectPath =  ($moduleType =='contact') ? $this->generateUrl('contact_index') :$this->generateUrl('clubadmin_sponsor_homepage');
        /* ----- Contact count checking ---------------    */
        $permissionObj = new FgPermissions($this->container);   
        $result['contactCreationPermission'] = ($contact) ? 1 :$permissionObj ->checkContactCount(1);
        $result['popupData'] = $this->renderView('CommonUtilityBundle:Permissionpopup:contactcreationwarningpopup.html.twig',array('redirectPath' => $redirectPath));
         /* ----- Contact count checking end---------------    */
        if ($request->getMethod() == 'POST' && isset($formValues)) {
            $fieldType = $formValues['system']['contactType'];
            $mainContactId = $request->request->get('mainContactId', '');
            $deletedFiles = $request->request->get('deletedFiles');
            if ($contact) {
                //handle edit
                $editData = $this->getEditData($request, $contact, $module);
                $isSwitchable = $editData[0]['created_club_id'] != $this->clubId ? false : true;
                $editData['switchable'] = ($editData[0]['created_club_id'] != $this->clubId && ($this->clubType == 'federation' || $this->clubType == 'sub_federation')) ? false : true;
                $isDeleted = $editData['0']['is_deleted'];
                $contactClubId = $editData[0]['created_club_id'];
                if ($editData['switchable'] == false) {
                    if (($this->clubType != 'federation' && $this->clubType != 'sub_federation') && $this->container->get('club')->get('clubMembershipAvailable')) {
                        $formValues['system']['membership'] = $editData[0]['membership_cat_id'];
                    }
                    $fieldType = ($editData[0]['is_company'] == 1) ? 'Company' : 'Single person';
                    $formValues['system']['contactType'] = $fieldType;
                }
                if ($editData[0]['is_fed_membership_confirmed'] == true || (($this->clubType == 'federation' || $this->clubType == 'sub_federation') && $editData[0]['main_club_id'] != $this->clubId)) {
                    $formValues['system']['fedMembership'] = $editData[0]['fed_membership_cat_id'];
                }
                $request->request->set('fg_field_category', $formValues);
            }
            $formValues[$systemCategoryAddress]['same_invoice_address'] = $request->get('same_invoice_address');
            $formValues[$systemCategoryPersonal]['has_main_contact_address'] = $request->get('has_main_contact_address');
            $typeSwitch = ($request->get('fieldType') == 1) ? true : false;
        } else {
            $formValues = false;
            $fieldType = 'Single person';
            $result['newContact'] = 0;
            if ($contact) {
                //handle edit
                $editData = $this->getEditData($request, $contact, $module);
                $isSwitchable = $editData[0]['created_club_id'] != $this->clubId ? false : true;
                $editData['switchable'] = ($editData[0]['created_club_id'] != $this->clubId && ($this->clubType == 'federation' || $this->clubType == 'sub_federation')) ? false : true;
                $isDeleted = $editData['0']['is_deleted'];
                $fieldType = ($editData[0]['is_company'] == 1) ? 'Company' : 'Single person';
                $contactClubId = $editData[0]['created_club_id'];
                if ($fieldType == 'Company' && !is_null($editData[0]['comp_def_contact'])) {
                    $editData[0]['mainContactName'] = $this->getContactName($editData[0]['comp_def_contact']);
                    $editData[0]['mainContactFunction'] = $editData[0]['comp_def_contact_fun'];
                    $mainContactId = $editData[0]['comp_def_contact'];
                }
            }
        }
        // To keep membership id if membership category drop down is disabled
        $selectedMembership['club'] = ($contact && $editData[0]['created_club_id'] != $this->clubId && in_array($this->clubType, array('federation', 'sub_federation'))) ? $editData[0]['club_membership_cat_id'] : $formValues['system']['membership'];
        $selectedMembership['fed'] = ($contact && $editData[0]['created_club_id'] != $this->clubId && in_array($this->clubType, array('federation', 'sub_federation'))) ? $editData[0]['fed_membership_cat_id'] : $formValues['system']['fedMembership'];
        
        $result['breadCrumb'] = array('breadcrumb_data' => array(), 'back' => ($moduleType === 'sponsor') ? $this->generateUrl('clubadmin_sponsor_homepage') : (($moduleType == 'archivedsponsor') ? $this->generateUrl('view_archived_sponsors') : (($moduleType === 'contactarchive') ? $this->generateUrl('archive_index') : $this->generateUrl('contact_index'))));
        //get club details as array
        $clubIdArray = $this->getClubArray();
        //get club contact fields
        $fieldDetails1 = $this->em->getRepository('CommonUtilityBundle:FgCmAttributeset')->getAllClubContactFields($clubIdArray, $this->conn, 0, $fieldType);
        //build contact field array
        $fieldDetails = $this->em->getRepository('CommonUtilityBundle:FgCmAttributeset')->fieldItrator($fieldDetails1);

        $fieldDetailArray = array('fieldType' => $fieldType, 'memberships' => $this->getMembershipArray(), 'clubIdArray' => $clubIdArray, 'fedMemberships' => $this->fedMemberships, 'fullLanguageName' => $fieldLanguages, 'selectedMembership' => $selectedMembership, 'contactId' => $request->get('contact', false), 'deletedFiles' => $deletedFiles);
        //set terminology terms in contact field array
        $fieldDetails = array_merge($this->setTerminologyTerms($fieldDetails), $fieldDetailArray);
        $randomAssignNum = $request->request->get('randomAssignNum');
        $assignErrFlag = $request->request->get('assignErrFlag');

        $assignedCatRolFunArray = json_decode($request->request->get('assignedCatRolFunArray'), true);
        $errorAssignmentServerside = 0;
        if ($fieldType == 'Company' && $typeSwitch) {
            $formValues = $this->updateMainContactType($formValues, $systemCategoryCompany, $systemCategoryPersonal, $fieldDetails['fieldsArray'][$systemCategoryPersonal]['values']);
        }
        $form1 = $this->createForm(\Common\UtilityBundle\Form\FgFieldCategoryType::class, $fieldDetails, array('custom_value' => array('submittedData' => $formValues, 'editData' => $editData, 'container' => $this->container, 'bookedModuleDetails' => $bookedModuleDetails, 'federationId' => $federationId, 'subfederationId' => $subfederationId, 'module' => $module)));
        if ($request->getMethod() == 'POST' && $typeSwitch == false) {
            $formValues = $request->request->get($form1->getName());
            $mainId = $request->request->get('mainContactId', '');
            if ($fieldType == 'Company' && empty($mainId)) {
                $formValues[$systemCategoryPersonal]['mainContactName'] = '';
            } elseif ($fieldType == 'Company') {
                $mainContactNameTitle = $request->request->get('mainContactNameTitle', '');
                $formValues[$systemCategoryPersonal]['mainContactName'] = $mainContactNameTitle['title'][0];
            }
            $selectedMembershipId = $formValues['system']['membership'];
            if (!empty($assignedCatRolFunArray)) {
                $errorAssignment = $this->validateAssignments($assignedCatRolFunArray, $this->clubId, $this->get('club'), $this->get('fairgate_terminology_service'), $selectedMembership['fed']);
                $errorAssignmentServerside = 0;
                if (is_array($errorAssignment)) {
                    $errorType = $errorAssignment['errorType'];
                    $errorArray = $errorAssignment['errorArray'];
                    $errorAssignmentServerside = 1;
                }
            }
            $form1->handleRequest($request);
            if ($form1->isSubmitted()) {
                if ($form1->isValid() && $assignErrFlag == 0 && $errorAssignmentServerside == 0) {
                    if (($this->clubType == 'federation_club' || $this->clubType == 'sub_federation_club') && $request->get('merging', false) == false) {
                        $isMergeable = $this->isMergeableContact($formValues, $editData);
                        if ($isMergeable) {
                            $pdo = new ContactPdo($this->container);
                            $mergeableReturn = $pdo->getMergeableContacts($formValues, $fieldType, $request->get('contact', false));
                            if (count($mergeableReturn['duplicates']) > 0 || count($mergeableReturn['mergeEmail']) > 0) {
                                $mergeableReturn['status'] = 'SUCCESS';
                                $mergeableReturn['noparentload'] = true;
                                $mergeableReturn['mergeable'] = true;

                                return new JsonResponse($mergeableReturn);
                            }
                        }
                    }
                    $lastInsertedContactId = $this->saveContactDetails($request, $form1, $contactClubId, $fieldLanguages, $request->get('contact', false), $fieldDetails, $editData, $module);
                    if (!empty($assignedCatRolFunArray)) {
                        $assignedCatRolFunArray = array($lastInsertedContactId => $assignedCatRolFunArray);
                        $contactIdArr = array($lastInsertedContactId);
                        $translationsArray = array('workgroup' => $this->get('translator')->trans('WORKGROUPS'));
                        $resultArray = $this->em->getRepository('CommonUtilityBundle:FgRmRoleContact')->updateContactAssignments($assignedCatRolFunArray, $this->clubId, $contactIdArr, $this->contactId, $this->get('club'), $this->get('fairgate_terminology_service'), $this->container, $translationsArray);
                    }
                    if ($module === 'sponsor') {
                        $redirect = $request->get('oneMore', false) ? $this->generateUrl('create_prospect') : (($moduleType == 'archivedsponsor') ? $this->generateUrl('view_archived_sponsors') : ($contact) ? $this->generateUrl('clubadmin_sponsor_homepage') : $this->generateUrl('render_sponsor_overview', array('offset' => 0, 'sponsor' => $lastInsertedContactId)));
                    } else {
                        $redirect = $request->get('oneMore', false) ? $this->generateUrl('create_contact') : (($moduleType === 'contactarchive') ? $this->generateUrl('archive_index') : ($contact) ? $this->generateUrl('contact_index') : $this->generateUrl('render_contact_overview', array('offset' => 0, 'contact' => $lastInsertedContactId)));
                    }

                    return new JsonResponse(array('status' => 'SUCCESS', 'sync' => 1, 'redirect' => $redirect, 'flash' => $this->get('translator')->trans('CONTACT_DATA_SAVED_SUCCESS')));
                } else {
                    $result['isError'] = true;
                    $result['randomAssignNum'] = $randomAssignNum;
                    $result['assignErrFlag'] = $assignErrFlag;
                    $result['errorType'] = 0;
                    if ($errorAssignmentServerside == 1) {
                        $result['errorType'] = $errorType;
                        if (!empty($errorArray)) {
                            $result['errorArray'] = json_encode($errorArray);
                        }
                    }
                }
            }
        }
        $resultArray = array('randomAssignNum' => $randomAssignNum, 'form' => $form1->createView(), 'fedMembers' => $this->fedMembers . ':', 'ownClub' => false, 'contact' => false, 'mainContactId' => $mainContactId, 'clubId' => $this->clubId, 'federationId' => $federationId, 'subfederationId' => $subfederationId, 'clubType' => $this->clubType, 'loggedContactId' => $this->contactId, 'module' => $module);
        $result = array_merge($result, $resultArray);
        $result['contactType'] = $fieldType;
        $result['mainContactVisible'] = false;
        if ($editData) {
            $result['ownClub'] = ($editData[0]['created_club_id'] == $this->clubId) ? true : false;
            if ($editData[0]['is_company'] == 1 && $editData[0]['has_main_contact'] == 1 && !empty($editData[0]['comp_def_contact'])) {
                $contactpdo = new ContactPdo($this->container);
                $mainContactVisible = $contactpdo->getClubContactId($editData[0]['comp_def_contact'], $this->clubId);
                if ($mainContactVisible) {
                    $result['mainContactVisible'] = $mainContactVisible['id'];
                    $result['mainContactId'] = $mainContactVisible['id'];
                }
            } elseif (empty($editData[0]['comp_def_contact'])) {
                $result['mainContactVisible'] = true;
            }
            $result['contact'] = $contact;
            $result['contactType'] = ($editData[0]['is_company'] == 1) ? 'Company' : 'Single person';
            $result['isSwitchable'] = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->isContactSwitchable($contact) ? 1 : 0;
        }
        
        return $this->render('ClubadminContactBundle:Default:createContact.html.twig', $result);
    }

    /**
     * Function is used for search.
     *
     * @return array
     */
    public function searchAction(Request $request)
    {
        $term = $request->get('term');
        $page = $request->get('page');
        $contactId = $request->get('contactId', 0);

        return new JsonResponse($this->searchContact($term, $contactId, $page));
    }

    /**
     * get contact data for edit.
     *
     * @param object $request Request object
     * @param type   $contact
     * @param type   $module
     *
     * @return type
     */
    private function getEditData($request, $contact, $module)
    {
        $editData = $this->tempContact($request, $this->conn);
        if ($editData['0']['is_deleted']) {
            $this->get('club')->set('moduleMenu', 'contactarchive');
        }
        $this->setContactModuleMenu($contact, $module);

        return $editData;
    }

    /**
     * save contact details.
     *
     * @param type $request
     * @param type $form1
     * @param type $contactClubId
     * @param type $fieldLanguages
     * @param type $contactId
     * @param type $fieldDetails
     * @param type $editData
     *
     * @return type
     */
    private function saveContactDetails($request, $form1, $contactClubId, $fieldLanguages, $contactId, $fieldDetails, $editData, $module)
    {
        $container = $this->container->getParameterBag();
        $systemCategoryAddress = $container->get('system_category_address');
        $systemCategoryCommunication = $container->get('system_category_communication');
        $systemCategoryPersonal = $container->get('system_category_personal');
        $systemCategoryCompany = $container->get('system_category_company');
        $formValues = $request->request->get($form1->getName());
        $files = $request->files->get($form1->getName());
        $deletedFiles = $request->request->get('deletedFiles');
        $formValues = $this->uploadFiles($files, $deletedFiles, $contactClubId, $formValues, $fieldDetails);
        $formValues[$systemCategoryPersonal]['mainContactName'] = $request->request->get('mainContactId');
        $formValues[$systemCategoryAddress]['same_invoice_address'] = $request->request->get('same_invoice_address');
        $formValues[$systemCategoryCompany]['has_main_contact_address'] = $request->request->get('has_main_contact_address');
        if ($request->get('mergeTo', false) && $request->get('mergeTo', false) != 'fed_mem') {
            $formValues['merging'] = $request->get('merging', false);
            $formValues['mergeTo'] = $request->get('mergeTo', false);
            $formValues['typeMer'] = $request->get('typeMer', false);
        }
        if (count($fieldLanguages) == 1) {
            $formValues[$systemCategoryCommunication][$container->get('system_field_corress_lang')] = $this->clubDefaultLang;
        }

        $formValues = $this->setCustomFields($formValues, $module, $contactId, $editData, $editData);
        $contact = new ContactDetailsSave($this->container, $fieldDetails, $editData, $contactId); //$container,$fieldDetails,$editData,$fedMemberships
        $contactIdNew = $contact->saveContact($formValues, explode(',', $deletedFiles));
        return $contactIdNew;
    }



    /**
     * Method to set custom fie;ds in Form values.
     *
     * @param array  $formValues Form values
     * @param string $module     contact/sponsor
     * @param int    $contactId  contactId
     * @param array  $editData   existingData
     *
     * @return array
     */
    private function setCustomFields($formValues, $module, $contactId, $editData)
    {
        // If sponsor module, save contact as sponsor
        if ($module === 'sponsor') {
            $formValues['system']['attribute'][] = 'Sponsor';
            $formValues['system']['attribute'][] = 'Intranet access';
            // in edit sponsor, retain values of Intranet access and stealth mode
            if ($contactId) {
                if ($editData[0]['intranet_access'] != '0') {
                    $formValues['system']['attribute'][] = 'Intranet access';
                }
                if ($editData[0]['is_stealth_mode'] != '0') {
                    $formValues['system']['attribute'][] = 'Stealth mode';
                }
            }
        } else {
            if ($contactId) {
                if ($editData[0]['is_sponsor'] != '0') {
                    $formValues['system']['attribute'][] = 'Sponsor';
                }
                //To handle case  when unchecking all checkbox in  attribute  (to set array['system']['attribute'])
                $formValues['system']['attribute'][] = '';
            } else {
                if (!in_array('Intranet access', $formValues['system']['attribute'])) {
                    $formValues['system']['attribute'][] = '';
                }
            }
        }

        return $formValues;
    }

    /**
     * set terminology terms to contact detail array.
     *
     * @param type $fieldDetails
     *
     * @return type
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
     * upload files.
     *
     * @param type $files
     * @param type $deletedFiles
     * @param type $contactClubId
     * @param type $formValues
     *
     * @return type array
     */
    private function uploadFiles($files, $deletedFiles, $contactClubId, $formValues, $fieldDetails = false)
    {
        $containerParameters = $this->container->getParameterBag();
        $profilePictureTeam = $containerParameters->get('system_field_team_picture');
        $profilePictureClub = $containerParameters->get('system_field_communitypicture');
        $systemCompanyLogo = $containerParameters->get('system_field_companylogo');
        $deletedFilesArray = explode(',', $deletedFiles);
        if (count($files) > 0) {
            $this->createContactFolders($contactClubId);
            foreach ($files as $category => $fields) {
                foreach ($fields as $fieldId => $fieldFile) {
                    if (!in_array($fieldId, $deletedFilesArray)) {
                        $formValues[$category][$fieldId] = $this->get('fg.avatar')->uploadContactField($fieldFile, $fieldId);
                    }
                }
            }
        }

        return $formValues;
    }

    /**
     * set contac tmodule menu.
     *
     * @param type $contactId
     */
    private function setContactModuleMenu($contactId, $module)
    {
        $accessObj = new ContactDetailsAccess($contactId, $this->container, $module);
        $contactType = $accessObj->contactviewType;
        $contactMenuModule = $accessObj->menuType;

        if ($contactMenuModule == 'archive' && $accessObj->module == 'contact') {
            $this->get('club')->set('moduleMenu', 'contactarchive');
        } elseif ($contactMenuModule == 'archive' && $accessObj->module == 'sponsor') {
            $this->get('club')->set('moduleMenu', 'archivedsponsor');
        } elseif ($contactMenuModule == 'formerfederationmember') {
            $this->get('club')->set('moduleMenu', 'formerfederationmember');
        }
    }

    /**
     * function to get language array.
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
     * Function to get membership array.
     *
     * @return type
     */
    private function getMembershipArray()
    {
        $objMembershipPdo = new membershipPdo($this->container);
        $membersipFields = $objMembershipPdo->getMemberships($this->clubType, $this->clubId, $this->subFederationId, $this->federationId);
        $this->fedMemberships = array();
        $this->fedMembers = '';
        $clubId = ($this->clubType == 'federation') ? $this->clubId : $this->federationId;
        $clubDefaultLang = $this->get('club')->get('default_lang');
        foreach ($membersipFields as $key => $memberCat) {
            $title = $memberCat['allLanguages'][$clubDefaultLang]['titleLang'] != '' ? $memberCat['allLanguages'][$clubDefaultLang]['titleLang'] : $memberCat['membershipName'];
            if (($memberCat['clubId'] == $clubId)) {
                $this->fedMemberships[] = $key;
                $this->fedMembers .= ':' . $key;
                $memberships['fed'][$key] = $title;
            } else {
                $memberships['club'][$key] = $title;
            }
        }

        return $memberships;
    }

    /**
     * get club details array.
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
            'invoiceCategory' => $container->get('system_category_invoice'),);
        $clubIdArray['address'] = $this->get('translator')->trans('CONTACT_FIELD_ADDRESS');
        $clubIdArray['sysLang'] = $this->clubDefaultLang;
        $clubIdArray['defSysLang'] = $this->clubDefaultSystemLang;
        $clubIdArray['clubLanguages'] = $this->clubLanguages;

        return $clubIdArray;
    }

    /**
     * create requireds folders for contact section.
     *
     * @param type $clubId clubId
     */
    public function createContactFolders($clubId)
    {
        if (!is_dir('uploads')) {
            mkdir('uploads', 0700);
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
        if (!is_dir('uploads/' . $clubId . '/contact/contactfield_file')) {
            mkdir('uploads/' . $clubId . '/contact/contactfield_file', 0700, true);
        }
        if (!is_dir('uploads/' . $clubId . '/contact/contactfield_image')) {
            mkdir('uploads/' . $clubId . '/contact/contactfield_image', 0700, true);
        }
        if (!is_dir('uploads/temp')) {
            mkdir('uploads/temp', 0700);
        }
    }

    /**
     * Function is used for temp contact.
     *
     * @param type $request
     * @param type $conn
     *
     * @return int|boolean
     */
    private function tempContact($request, $conn)
    {
        $contactId = $request->get('contact');
        if (!is_numeric($contactId)) {
            return false;
        }
        $club = $this->get('club');
        $contactlistClass = new Contactlist($this->container, $contactId, $club, 'editable');
        $contactlistClass->setColumns('*');
        $contactlistClass->setFrom('*');
        $contactlistClass->setCondition();
        $sWhere = " fg_cm_contact.id=$contactId";
        $contactlistClass->addCondition($sWhere);
        $listquery = $contactlistClass->getResult();
        $fieldsArray = $conn->fetchAll($listquery);
        $fieldsArray[0]['is_sponsor'] = $fieldsArray[0]['sponsorFlag'];
        $fieldsArray[0]['is_fed_member'] = 0;
        if (!empty($fieldsArray[0]['fed_membership_cat_id'])) {
            $fieldsArray[0]['is_fed_member'] = 1;
        }

        return $fieldsArray;
    }

    /**
     * Function is used to search contact.
     *
     * @param string $search        search query
     * @param int    $contactId     contact id 
     * @param string $page          page type
     * 
     * @return array
     */
    private function searchContact($search, $contactId = 0,$page = '')
    {
        if ($search == '') {
            $listquery = '';
        } else {
            $searchTerm = explode(' ', trim($search), 2);
            if (sizeof($searchTerm) > 1) {
                $listquery = " AND ((S.`2` LIKE '$searchTerm[0]%' OR S.`23` LIKE '$searchTerm[0]%' OR S.`9` LIKE '$searchTerm[0]%') AND (S.`2` LIKE '$searchTerm[1]%' OR S.`23` LIKE '$searchTerm[1]%' OR S.`9` LIKE '$searchTerm[1]%'))";
            } else {
                $listquery = " AND (S.`2` LIKE '$search%' OR S.`23` LIKE '$search%' OR S.`9` LIKE '$search%')";
            }
        }
        $listquery .= " AND (C.main_club_id=C.club_id OR (C.fed_membership_cat_id IS NOT NULL AND (C.old_fed_membership_id IS NOT NULL OR C.is_fed_membership_confirmed='0')) ) ";
        $excludeIds = $contactId == 0 ? '' : " AND C.id != $contactId";
        $fieldsArray = $this->conn->fetchAll("SELECT IF('$page' = 'contactEdit',C.id,C.fed_contact_id) AS id,CONCAT(contactName(C.fed_contact_id),IF(DATE_FORMAT(`4`,'%Y') = '0000' OR `4` is NULL OR `4` ='','',CONCAT(' (',DATE_FORMAT(`4`,'%Y'),')'))) AS title, contactName(C.id) AS name FROM fg_cm_contact C left join master_system S on C.fed_contact_id=S.fed_contact_id where C.is_company=0 AND C.is_deleted=0 AND C.is_permanent_delete=0 AND C.club_id='{$this->clubId}' $listquery $excludeIds ORDER BY name");

        return $fieldsArray;
    }

    /**
     * Function is used to get contact name.
     *
     * @param int $contactId Contact Id
     *
     * @return array
     */
    private function getContactName($contactId)
    {
        //$fieldsArray = $this->conn->fetchAll("SELECT CONCAT(`23`,' ',`2`) AS name FROM fg_cm_contact C left join master_system S on C.id=S.contact_id where C.id='$contactId'");
        $fieldsArray = $this->conn->fetchAll("SELECT contactNameYOB($contactId)");

        return $fieldsArray[0]['contactNameYOB(' . $contactId . ')'];
    }

    /**
     * Function is used to validate assignments.
     *
     * @param type $catArr               CatArray
     * @param type $clubId               ClubId
     * @param type $clubService          Club service
     * @param type $terminologyService   Terminology
     * @param type $selectedMembershipId Seleted membership id
     *
     * @return int
     */
    public function validateAssignments($catArr, $clubId, $clubService, $terminologyService, $selectedMembershipId)
    {
        $catIdsArr = array_keys($catArr);
        $clubService = $this->get('club');
        if ($clubService->federationId > 0) {
            $fedWrkgrpCatRoleIds = $this->em->getRepository('CommonUtilityBundle:FgRmCategory')->getExecutiveBoardRoleCatIds($clubService->federationId);
            $fedWrkgrpCatId = $fedWrkgrpCatRoleIds['catId'];
            $fedWrkgrpRoleId = $fedWrkgrpCatRoleIds['roleId'];
            if (in_array($fedWrkgrpCatId, $catIdsArr)) {
                $catIdsArr[] = $clubService->get('club_workgroup_id');
            }
        }
        $hasTeamCat = false;
        foreach ($catIdsArr as $catIdVal) {
            if (strpos($catIdVal, 'team') !== false) {
                $hasTeamCat = true;
                unset($catIdsArr[array_search($catIdVal, $catIdsArr)]);
            }
        }
        if ($hasTeamCat) {
            $catIdsArr[] = $clubService->get('club_team_id');
        }

        $catIds = implode(',', $catIdsArr);
        $conn = $this->container->get('database_connection');
        $roleFunctionDetails = $conn->fetchAll('SELECT c.`id` AS category_id, c.`club_id` AS catClubId, c.`title` AS catTitle, c.`is_fed_category` AS isFedCategory, c.`role_assign` AS roleAssign, c.`is_team` AS isTeam, c.`is_workgroup` AS isWrkgrp'
            . ', GROUP_CONCAT(r.`id`) AS roleIds, GROUP_CONCAT(f.`id`) AS functionIds, GROUP_CONCAT(r.`title`) AS roleNames, GROUP_CONCAT(f.`title`) AS functionNames '
            . 'FROM `fg_rm_category` c '
            . 'LEFT JOIN `fg_rm_role` r ON (r.category_id = c.id) '
            . 'LEFT JOIN `fg_rm_function` f ON (f.category_id = c.id) '
            . "WHERE c.id IN ($catIds) AND c.contact_assign = 'manual' "
            . 'GROUP BY c.id');

        $catRoleFunctionDetails = array();
        $categoryTitles = array();
        $roleTitles = array();
        $functionTitles = array();
        $clubExecBoardFunctionIds = array();
        foreach ($roleFunctionDetails as $roleFunctionDetail) {
            $catId = $roleFunctionDetail['category_id'];
            if ($catId == $fedWrkgrpCatId) {
                $catId = $clubService->get('club_workgroup_id');
            }
            $catRoleFunctionDetails[$catId]['club_id'] = $roleFunctionDetail['catClubId'];
            $catRoleFunctionDetails[$catId]['is_fed_category'] = $roleFunctionDetail['isFedCategory'];
            $catRoleFunctionDetails[$catId]['role_assign'] = $roleFunctionDetail['roleAssign'];
            $catRoleFunctionDetails[$catId]['cat_type'] = 'R';
            if ($roleFunctionDetail['isTeam']) {
                $catRoleFunctionDetails[$catId]['cat_type'] = 'T';
            } elseif ($roleFunctionDetail['isWrkgrp']) {
                $catRoleFunctionDetails[$catId]['cat_type'] = 'W';
            }
            $roleIds = explode(',', $roleFunctionDetail['roleIds']);
            $functionIds = ($roleFunctionDetail['functionIds'] != '') ? explode(',', $roleFunctionDetail['functionIds']) : array();
            $functionNames = explode(',', $roleFunctionDetail['functionNames']);
            if (($catId == $clubService->get('club_workgroup_id')) && ($clubService->federationId > 0)) {
                //add club-executive-board functions also
                $roleIds[] = $clubService->get('club_executiveboard_id');
                $clubExecBoardFunctions = $this->em->getRepository('CommonUtilityBundle:FgRmFunction')->getClubExecBoardFunctionIds($clubService->federationId, $conn, true);
                $clubExecBoardFunctionIds = array_keys($clubExecBoardFunctions);
                $functionIds = array_merge($functionIds, $clubExecBoardFunctionIds);
                $functionNames = array_merge($functionNames, $clubExecBoardFunctions);
            }
            $catRoleFunctionDetails[$catId]['roles'] = $roleIds;
            $functionTitles[$catId] = count($functionIds) ? array_combine($functionIds, $functionNames) : array();
            $catRoleFunctionDetails[$catId]['functions'] = $functionIds;
            if ($catId == $clubService->get('club_team_id')) {
                $categoryName = ucfirst($terminologyService->getTerminology('Team', $this->container->getParameter('singular')));
                $categoryTitles[$catId] = $categoryName;
            } else {
                $categoryTitles[$catId] = $roleFunctionDetail['catTitle'];
            }
            $roleNames = explode(',', $roleFunctionDetail['roleNames']);
            $roleTitles[$catId] = array_combine($roleIds, $roleNames);
        }

        $hasError = false;
        $errorType = '';
        $errorArray = array();
        $noMultiAssignments = array();
        $resultArray = array();
        foreach ($catArr as $catId => $catArrDetail) {
            if (strpos($catId, 'team') !== false) {
                $catId = $clubService->get('club_team_id');
            } elseif ($catId == $fedWrkgrpCatId) {
                $catId = $clubService->get('club_workgroup_id');
            }
            $isFederationCat = $catRoleFunctionDetails[$catId]['is_fed_category'];
            $roleAssign = $catRoleFunctionDetails[$catId]['role_assign'];
            //if federation role category check whether contact is federation member
            if ($isFederationCat && $selectedMembershipId == '') {
                $hasError = true;
                $errorType = $errorType = $this->get('translator')->trans('CREATE_CONTACT_NOT_FED_MEMBER', array('%federation_members%' => $terminologyService->getTerminology('Federation member', $this->container->getParameter('plural'))));
                $errorArray[$catId] = $catId;
                continue;
            } else {
                if (isset($catArrDetail['role'])) {
                    foreach ($catArrDetail['role'] as $roleId => $roleArrDetail) {
                        if ($roleId == $fedWrkgrpRoleId) {
                            $roleId = $clubService->get('club_executiveboard_id');
                        }
                        $doInsert = false;
                        $doDelete = false;
                        //check whether roles and functions of category are correct
                        if (!in_array($roleId, $catRoleFunctionDetails[$catId]['roles'])) {
                            //check whether role is valid
                            $hasError = true;
                            $errorType = 'NOT_VALID_ROLE';
                            continue;
                        } else {
                            if (isset($roleArrDetail['function'])) {
                                //if category have functions
                                foreach ($roleArrDetail['function'] as $functionId => $functionArrDetail) {
                                    if (!in_array($functionId, $catRoleFunctionDetails[$catId]['functions'])) {
                                        //check whether function is valid
                                        $hasError = true;
                                        $errorType = 'NOT_VALID_FUNCTION';
                                        continue;
                                    }
                                    if (isset($functionArrDetail['is_new'])) {
                                        $doInsert = true;
                                    } elseif (isset($functionArrDetail['is_deleted'])) {
                                        $doDelete = true;
                                    }

                                    if ($roleAssign == 'single') {
                                        if (!array_key_exists($catId, $noMultiAssignments)) {
                                            $noMultiAssignments[$catId]['roleid'] = $roleId;
                                            $noMultiAssignments[$catId]['functionids'][] = $functionId;
                                        }

                                        if ($roleId == $noMultiAssignments[$catId]['roleid']) {
                                            $noMultiAssignments[$catId]['functionids'][] = $functionId;
                                        } else {
                                            $hasError = true;
                                            $errorType = $this->get('translator')->trans('CREATE_CONTACT_NO_MULTI_ASSIGNMENT_POSSIBLE');
                                            $errorArray[$catId] = $catId;
                                            continue;
                                        }
                                    }
                                }
                            } else {
                                $functionId = null;
                                if (isset($roleArrDetail['is_new'])) {
                                    $doInsert = true;
                                } elseif (isset($roleArrDetail['is_deleted'])) {
                                    $doDelete = true;
                                }
                                if ($roleAssign == 'single') {
                                    if (!array_key_exists($catId, $noMultiAssignments)) {
                                        $noMultiAssignments[$catId]['roleid'] = $roleId;
                                    }
                                    if ($roleId != $noMultiAssignments[$catId]['roleid']) {
                                        $hasError = true;
                                        $errorType = $this->get('translator')->trans('CREATE_CONTACT_NO_MULTI_ASSIGNMENT_POSSIBLE');
                                        $errorArray[$catId] = $catId;
                                        continue;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($hasError) {
            $resultArray = array('errorType' => $errorType, 'errorArray' => $errorArray);

            return $resultArray;
        } else {
            $error = 0;

            return $error;
        }
    }

    /**
     * Function to update main contact type.
     *
     * @param array $formValues       Form values
     * @param type  $categoryCompany  Company category id
     * @param type  $categoryPersonal Personal category id
     * @param type  $personalFields   Fields if personal category
     *
     * @return string
     */
    private function updateMainContactType($formValues, $categoryCompany, $categoryPersonal, $personalFields)
    {
        foreach ($formValues[$categoryPersonal] as $fieldId => $personalValue) {
            if (trim($personalValue) != '' && $personalFields[$fieldId]['isCompany']) {
                return $formValues;
            }
        }
        $formValues[$categoryCompany]['mainContact'] = 'noMain';

        return $formValues;
    }

    /**
     * Function to render the assignment popup.
     *
     * @return HTML
     */
    public function renderAssignmentPopupAction(Request $request)
    {
        $pendingAssignments = $request->get('pendingAssignments');
        $resultArray = array();
        $preCatId = '';
        foreach ($pendingAssignments as $key => $val) {
            if ($preCatId != $val['catId']) {
                $preCatId = $val['catId'];
                $resultArray[$val['catId']][] = $val;
            } else {
                $resultArray[$val['catId']][] = $val;
            }
        }

        return $this->render('ClubadminContactBundle:Default:assignmentPopup.html.twig', array('pendingAssignments' => $resultArray, 'assignmentsArray' => json_encode($pendingAssignments)));
    }

    /**
     * Function to handle inline edit save and validation.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function saveContactAction(Request $request)
    {
        $systemCategoryAddress = $this->container->getParameter('system_category_address');
        $systemCategoryInvoice = $this->container->getParameter('system_category_invoice');
        $contactId = $_POST['rowId'];
        $attributeId = $_POST['colId'];
        $exptraParam = $_POST['emailIds'];
        $exptraParam = explode('<=>', $exptraParam);
        $aciveTabId = (is_array($exptraParam)) ? $exptraParam[0] : '';
        $selectedIds = (is_array($exptraParam)) ? explode(',', $exptraParam[1]) : '';
        $value = $_POST['value'];
        $value = (is_array($value)) ? $value : trim($value);
        $request->request->set('contact', $contactId);
        $contactData = $this->tempContact($request, $this->conn);
        $club = $this->container->get('club');
        $clubDetails = array('clubId' => $this->clubId, 'federationId' => $this->federationId, 'subFederationId' => $this->subFederationId, 'clubType' => $this->clubType);

        $contactValidatorObj = new FgContactValidator($this->container, $contactId, $attributeId, $value, $contactData[0], $clubDetails);
        $output = $contactValidatorObj->checkIsValid();

        if ($output['valid'] == 'true') {
            switch ($attributeId) {
                case 'CMfirst_joining_date':
                case 'FMfirst_joining_date':
                    if ($value != '') {
                        $date = strtotime($value);
                        if ($date !== false) {
                            $joiningDate = date('Y-m-d H:i:s', $date);
                            $type = ($attributeId == 'CMfirst_joining_date') ? 'club' : 'federation';
                            $contactId = ($attributeId == 'CMfirst_joining_date') ? $contactId : $contactData[0]['fed_contact_id'];
                            $this->em->getRepository('CommonUtilityBundle:FgCmContact')->updateContactData($contactId, 'firstJoiningDate', $joiningDate, $type);
                            $this->em->getRepository('CommonUtilityBundle:FgCmMembershipHistory')->updateMembershipHistoryOfContact($contactId, 'joining_date', $joiningDate, 'ASC');
                            $this->em->getRepository('CommonUtilityBundle:FgCmMembershipLog')->updateMembershipLogOfContact($contactId, 'joining_date', $joiningDate, 'ASC');
                            if ($attributeId == 'FMfirst_joining_date') {
                                $this->em->getRepository('CommonUtilityBundle:FgCmContact')->updateLastUpdated($contactId, 'fedContact');
                            }
                        }
                    }
                    break;

                case 'CMjoining_date':
                case 'FMjoining_date':
                    if ($value != '') {
                        $date = strtotime($value);
                        if ($date !== false) {
                            $joiningDate = date('Y-m-d H:i:s', $date);
                            $type = ($attributeId == 'CMjoining_date') ? 'club' : 'federation';
                            $contactId = ($attributeId == 'CMjoining_date') ? $contactId : $contactData[0]['fed_contact_id'];
                            $this->em->getRepository('CommonUtilityBundle:FgCmContact')->updateContactData($contactId, 'joiningDate', $joiningDate, $type);
                            $this->em->getRepository('CommonUtilityBundle:FgCmMembershipHistory')->updateMembershipHistoryOfContact($contactId, 'joining_date', $joiningDate);
                            $this->em->getRepository('CommonUtilityBundle:FgCmMembershipLog')->updateMembershipLogOfContact($contactId, 'joining_date', $joiningDate);
                            if ($attributeId == 'FMfirst_joining_date') {
                                $this->em->getRepository('CommonUtilityBundle:FgCmContact')->updateLastUpdated($contactId, 'fedContact');
                            }
                        }
                    }
                    break;

                case 'CMleaving_date':
                case 'FMleaving_date':
                    if ($value != '') {
                        $date = strtotime($value);
                        if ($date !== false) {
                            $leavingDate = date('Y-m-d H:i:s', $date);
                            $type = ($attributeId == 'CMleaving_date') ? 'club' : 'federation';
                            $contactId = ($attributeId == 'CMleaving_date') ? $contactId : $contactData[0]['fed_contact_id'];
                            $this->em->getRepository('CommonUtilityBundle:FgCmContact')->updateContactData($contactId, 'leavingDate', $leavingDate, $type);
                            $this->em->getRepository('CommonUtilityBundle:FgCmMembershipHistory')->updateMembershipHistoryOfContact($contactId, 'leaving_date', $leavingDate);
                            $this->em->getRepository('CommonUtilityBundle:FgCmMembershipLog')->updateMembershipLogOfContact($contactId, 'leaving_date', $leavingDate);
                            if ($attributeId == 'FMfirst_joining_date') {
                                $this->em->getRepository('CommonUtilityBundle:FgCmContact')->updateLastUpdated($contactId, 'fedContact');
                            }
                        }
                    }
                    break;

                case 'Function':
                    if (!empty($value)) {
                        $value = array_filter($value, function ($v) {
                            return is_numeric($v);
                        });
                        $deletedIds = array_diff($selectedIds, $value);
                    } else {
                        $value = '';
                        $deletedIds = $selectedIds;
                    }
                    $contactIdArr = array($contactId);
                    $assignedCatRolFunArray = $this->createAssignmentArray($contactId, $value, $aciveTabId, $deletedIds);
                    $translationsArray = array('workgroup' => $this->get('translator')->trans('WORKGROUPS'));
                    $this->em->getRepository('CommonUtilityBundle:FgRmRoleContact')->updateContactAssignments($assignedCatRolFunArray, $this->clubId, $contactIdArr, $this->contactId, $this->get('club'), $this->get('fairgate_terminology_service'), $this->container, $translationsArray);
                    break;

                default:
                    $formValues = array();
                    $clubDetails = array('clubId' => $this->clubId, 'federationId' => $this->federationId, 'subFederationId' => $this->subFederationId, 'clubType' => $this->clubType, 'clubHeirarchy' => $club->get('clubHeirarchy'), 'clubDefaultLang' => $this->clubDefaultLang, 'clubDefaultSystemLang' => $this->clubDefaultSystemLang, 'correspondanceCategory' => $systemCategoryAddress, 'invoiceCategory' => $systemCategoryInvoice);
                    $attrDetails1 = $this->em->getRepository('CommonUtilityBundle:FgCmAttributeset')->getAttributeDetails($attributeId, $clubDetails, $this->conn, 1);
                    $attrDetails = $this->em->getRepository('CommonUtilityBundle:FgCmAttributeset')->groupContactFieldsByCategory($attrDetails1);
                    $categoryId = $attrDetails1[0]['currentCatId'];
                    $formValues[$categoryId][$attributeId] = $value;

                    $contact = new ContactDetailsSave($this->container, $attrDetails, $contactData, $contactId);
                    $contactIdNew = $contact->saveContact($formValues, array(), array(), array(), 1);
                    break;
            }
        }

        return new JsonResponse($output);
    }

    /**
     * Function to create array structure for assignment function.
     *
     * @param int    $contactId
     * @param array  $value
     * @param string $aciveTabId
     * @param array  $deletedIds
     *
     * @return array
     */
    private function createAssignmentArray($contactId, $value, $aciveTabId, $deletedIds)
    {
        $aciveTabId = str_replace('_li', '', $aciveTabId);
        $aciveTabId = str_replace('ROLES', 'ROLE', $aciveTabId);
        $aciveTabId = str_replace('FROLE', 'ROLE', $aciveTabId);

        $tabIdArray = explode('_', $aciveTabId);
        $assignedCatRolFunArray = array();
        if (is_array($tabIdArray)) {
            if (strpos($tabIdArray[0], 'ROLE') !== false) {
                $selectionType = explode('-', $tabIdArray[0]);
                $selectionType = strtolower($selectionType[0]);
            } else {
                $selectionType = strtolower($tabIdArray[0]);
            }
            $roleCatId = $tabIdArray[1];
            $roleId = $tabIdArray[2];
        }
        if ($selectionType == 'team') {
            $roleCatId = 'team' . $roleCatId;
            $selectionType = 'role';
        } elseif ($selectionType == 'workgroup') {
            $selectionType = 'role';
        }
        foreach ($value as $functionId) {
            $assignedCatRolFunArray[$contactId][$roleCatId][$selectionType][$roleId]['function'][$functionId]['is_new'] = $functionId;
        }
        foreach ($deletedIds as $functionId) {
            $assignedCatRolFunArray[$contactId][$roleCatId][$selectionType][$roleId]['function'][$functionId]['is_deleted'] = $functionId;
        }

        return $assignedCatRolFunArray;
    }

    /**
     * Check create/edit contact is mergeable.
     *
     * @param type $formValues
     * @param type $editData
     *
     * @return bool
     */
    private function isMergeableContact($formValues, $editData)
    {
        $mergeable = false;
        if ($formValues['system']['fedMembership'] != '' && $formValues['system']['fedMembership'] != 'default' && ($editData[0]['fed_membership_cat_id'] == '' || $editData[0]['fed_membership_cat_id'] == 'NULL')) {
            //if(!empty($formValues[$catCommun][$primaryEmail])){
            $mergeable = true;
            //}
        }

        return $mergeable;
    }
}
