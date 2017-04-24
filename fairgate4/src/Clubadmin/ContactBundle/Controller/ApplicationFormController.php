<?php

/**
 * ApplicationFormController.
 *
 * @package 	Clubadmin
 * @subpackage 	ContactBundle
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 *
 */
namespace Clubadmin\ContactBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Clubadmin\ContactBundle\Util\FgContactForm;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Repository\Pdo\membershipPdo;
use Website\CMSBundle\Util\FgFormElement;
use Clubadmin\Util\Contactlist;

/**
 * ApplicationFormController
 *
 * This controller is used for handle contact/membership application form
 */
class ApplicationFormController extends FgController
{

    /**
     * This function is used to create a form.
     * 
     * @param Request $request Request object
     * 
     * @return object View Template Render Object
     */
    public function createApplicationFormFieldAction(Request $request)
    {
        $returnArray = array();
        $em = $this->getDoctrine()->getManager();
        $formId = $request->get('formId');
        $club = $this->container->get('club');
        $formDetails = $em->getRepository('CommonUtilityBundle:FgCmsForms')->getContactFormDetails($formId, $club->get('id'));
        $event = ($formDetails['formStage'] == 'stage0') ? 'create' : 'edit';
        
        $clubType = ['standard_club', 'federation_club', 'sub_federation_club'];
        if (!in_array($club->get('type'), $clubType) || count($formDetails)==0) {
             throw $this->createNotFoundException($club->get('title') . ' have no access to this page');
        }

        // Following data is for handle application form in cms page
        $elementId = $request->get('elementId');
        $boxId = $request->get('boxId');
        $sortOrder = $request->get('sortOrder');
        $pageId = $request->get('pageId');
        $contactFields = $club->get('contactFields');
        $clubmemberShip = array();
        if (($club->get('type') == 'standard_club' || $club->get('type') == 'federation_club' || $club->get('type') == 'sub_federation_club') && $club->get('clubMembershipAvailable') == 1) {
            $objMembershipPdo = new membershipPdo($this->container);
            $clubmemberShip = $objMembershipPdo->getMemberships($club->get('type'), $club->get('id'), 0, 0, $this->container->get('contact')->get("id"), 1);
        }
        //$mandatorySystemfieldsId = $this->getMandatoryFields($formDetails['contactFormType']);
        $selectedType = $formDetails['contactFormType'];
        switch ($selectedType) {
            case 'single_person':
                $mandatorySystemfieldsId = $this->container->getParameter('companyfields');
                break;
            case 'company_with_main_contact':
                $mandatorySystemfieldsId = $this->container->getParameter('companywithmaincontactfields');
                break;
            default:
                $mandatorySystemfieldsId = $this->container->getParameter('companywithoutmaincontactfields');
                break;
        }
        //Remove correspondance language

        if (count($club->get('club_languages')) > 1) {
            array_push($mandatorySystemfieldsId, $this->container->getParameter('system_field_corress_lang'));
        }
      
        $manadatorySystemFieldDetails = $em->getRepository('CommonUtilityBundle:FgCmAttribute')->getSelectedSystemFieldDetails($mandatorySystemfieldsId, $club->get('default_system_lang'));
        $manadatorySystemFieldDetailsNew = array();
        foreach ($mandatorySystemfieldsId as $key => $val) {
            $manadatorySystemFieldDetailsNew[] = $manadatorySystemFieldDetails[$val];
        }
        $returnArray['formId'] = $formId;
        $returnArray['pageId'] = $pageId;
        $returnArray['boxId'] = $boxId;
        $returnArray['elementId'] = $elementId;
        $returnArray['sortOrder'] = $sortOrder;
        $returnArray['breadCrumb'] = array('back' => $this->generateUrl('contact_application_form_list'));
        $returnArray['event'] = $event;
        $returnArray['contactFields'] = $contactFields;
        $returnArray['clubmembership'] = $clubmemberShip;
        $returnArray['clubmembershipAvailable'] = $club->get('clubMembershipAvailable');
        $returnArray['meta']['clubDefaultLang'] = $club->get('club_default_lang');
        $returnArray['meta']['clubLanguages'] = $club->get('club_languages');
        $returnArray['meta']['mandatoryFieldsId'] = $mandatorySystemfieldsId;
        $returnArray['meta']['mandatorySystemfieldDetails'] = $manadatorySystemFieldDetailsNew;
        $returnArray['meta']['systemFieldCorressLang'] = $this->container->getParameter('system_field_corress_lang');
        $returnArray['formName'] = $formDetails['title'];
        $returnArray['form']['elementArray'] = array();
        $returnArray['form']['name'] = $formDetails['title'];
        $returnArray['meta']['existing'] = 0;
        $returnArray['meta']['formId'] = $formId;
        $returnArray['meta']['existing'] = $formId;
        $returnArray['formStage'] = $formDetails['formStage'];
        $returnArray['pageTitle'] = $this->get('translator')->trans('APPLICATION_FORM_TITLE');
        $returnArray['meta']['event'] = 'create';
        $returnArray['contactFormType'] = $formDetails['contactFormType'];
        $returnArray['contactBothFields'] = $this->container->getParameter('system_personal_both');///both company and personal fields
        $returnArray['primaryEmailField'] = $this->container->getParameter('system_field_primaryemail');
        $returnArray['systemCategoryAddress'] = $this->container->getParameter('system_category_address');
        $returnArray['systemCategoryInvoice'] = $this->container->getParameter('system_category_invoice');
        
        return $this->render('ClubadminContactBundle:ApplicationForm:formElement.html.twig', $returnArray);
    }

    /**
     * The fucntion to get the form data, if stage is provided the stage data will be sent
     * 
     * @param Request $request Request object
     * 
     * @return object JSON Response Object
     */
    public function getApplicationFormDataAction(Request $request)
    {
        $stage = $request->get('stage', '');
        $formId = $request->get('formId');
        $em = $this->getDoctrine()->getManager();
        $club = $this->container->get('club');
        $formObj = $em->getRepository('CommonUtilityBundle:FgCmsForms')->find($formId);
        if ($formObj) {
            $returnArray['form'] = array();
            switch ($stage) {
                case '1':
                    $formElementObj = new FgFormElement($this->container);
                    $formData = $formElementObj->getContactFormElementData($formId, 'edit');
                    $firstFormData = $formData;
                    $firstFormField = current($firstFormData);
                    $objMembershipPdo = new membershipPdo($this->container);
                    $clubmemberShip = $objMembershipPdo->getMemberships($club->get('type'), $club->get('id'), 0, 0, $this->container->get('contact')->get("id"), 1);
                    $returnArray['form']['stage1']['form']['elementArray'] = $formData;
                    $returnArray['form']['stage1']['form']['name'] = $firstFormField['formName'];
                    $returnArray['meta']['formStage'] = $formObj->getFormStage();
                    $selectedType = $firstFormField['contactFormType'];
                    switch ($selectedType) {
                        case 'single_person':
                            $mandatorySystemfieldsId = $this->container->getParameter('companyfields');
                            break;
                        case 'company_with_main_contact':
                            $mandatorySystemfieldsId = $this->container->getParameter('companywithmaincontactfields');
                            break;
                        default:
                            $mandatorySystemfieldsId = $this->container->getParameter('companywithoutmaincontactfields');
                            break;
                    }
                    //Remove correspondance language
                    if (count($club->get('club_languages')) > 1) {
                        array_push($mandatorySystemfieldsId, $this->container->getParameter('system_field_corress_lang'));
                    }
                    $returnArray['meta']['mandatoryFieldsId'] = $mandatorySystemfieldsId;


                    break;
                case '2':
                    $dataArray = $em->getRepository('CommonUtilityBundle:FgCmsForms')->getContactApplicationFormMailSettings($formId, $this->container, $club);
                    $returnArray['form']['stage2'] = $dataArray;
                    $returnArray['meta']['formStage'] = $formObj->getFormStage();
                    //is superadmin/clubadmin/fedadmin
                    $returnArray['meta']['hasAdminRights'] = (in_array('clubAdmin', $this->container->get('contact')->get('allowedModules'))) ? 1 : 0;
                    $returnArray['meta']['editSignaturePath'] = $this->generateUrl('club_settings_data', array('offset' => 0, 'clubid' => $club->get('id')));
                    //default content and subject translations in all system languages
                    $returnArray['meta']['defaultTranslations'] = $this->getDefaultTranslations();
                    break;
                case '3':
                    $dataArray = $em->getRepository('CommonUtilityBundle:FgCmsForms')->getFormOptions($formId, $this->container, $club);
                    $returnArray['form']['stage3'] = $dataArray;
                    $returnArray['meta']['formStage'] = $formObj->getFormStage();
                    break;
                default:
                    break;
            }
        } else {
            $returnArray['error'] = true;
        }

        $returnArray['meta']['clubDefaultLang'] = $club->get('club_default_lang');
        $returnArray['meta']['clubLanguages'] = $club->get('club_languages');

        return new JsonResponse($returnArray);
    }

    /**
     * Method to get default content and subject translations in system languages of each corresponding languages
     * 
     * @return array $transArray Array of subject and content with key as correspondence langauge and value as translation of 
     * subject and content in corresponding system language
     */
    private function getDefaultTranslations()
    {
        $transArray = array();
        $clubLangDetails = $this->container->get('club')->get('club_languages_det');
        foreach($clubLangDetails as $lang => $detail) {
            $transArray[$lang]['confirmationMailSubject'] = $this->get('translator')->trans('CMS_CONTACT_FORM_CONFIRMATION_MAIL_SUBJECT_DEFAULT', array(), 'messages', $detail['systemLang']);
            $transArray[$lang]['confirmationMailContent'] = $this->get('translator')->trans('CMS_CONTACT_FORM_CONFIRMATION_MAIL_CONTENT_DEFAULT', array(), 'messages', $detail['systemLang']);
            $transArray[$lang]['acceptanceMailSubject'] = $this->get('translator')->trans('CMS_CONTACT_FORM_ACCEPTANCE_MAIL_SUBJECT_DEFAULT', array(), 'messages', $detail['systemLang']);
            $transArray[$lang]['acceptanceMailContent'] = $this->get('translator')->trans('CMS_CONTACT_FORM_ACCEPTANCE_MAIL_CONTENT_DEFAULT', array(), 'messages', $detail['systemLang']);
            $transArray[$lang]['dismissalMailSubject'] = $this->get('translator')->trans('CMS_CONTACT_FORM_DISMISSAL_MAIL_SUBJECT_DEFAULT', array(), 'messages', $detail['systemLang']);
            $transArray[$lang]['dismissalMailContent'] = $this->get('translator')->trans('CMS_CONTACT_FORM_DISMISSAL_MAIL_CONTENT_DEFAULT', array(), 'messages', $detail['systemLang']);
        }
        
        return $transArray;
    }
    
    /**
     * This method is used to list all contact application forms.
     * 
     * @return object View Template Render Object
     */
    public function listAction()
    {
        $clubMembershipAvailable =  $this->container->get('club')->get('clubMembershipAvailable');
        $clubType = $this->container->get('club')->get('type');
        if (!$clubMembershipAvailable || $clubType =='federation' || $clubType =='sub_federation' ) {
            throw $this->createNotFoundException($this->container->get('club')->get('title') . ' have no access to this page');
        }
        $retrun = array();
        $em = $this->getDoctrine()->getManager();
        $clubId = $this->container->get('club')->get('id');
        $deflang = $this->container->get('club')->get('club_default_lang');
        $applicationForms = $em->getRepository('CommonUtilityBundle:FgCmsForms')->getContactApplicationFormList($clubId, $deflang);
        $retrun['applicationForms'] = array_map(function($a) {
            return $a + array('encId' => base64_encode($a['id']));
        }, $applicationForms);

        return $this->render('ClubadminContactBundle:ApplicationForm:list.html.twig', $retrun);
    }

    /**
     * This method is used to delete contact application forms.
     * 
     * @param Request $request Request object
     * 
     * @return object JSON Response Object
     */
    public function deleteFormAction(Request $request)
    {
        $formId = $request->get('formId');
        $em = $this->getDoctrine()->getManager();
        $em->getRepository('CommonUtilityBundle:FgCmsForms')->deleteConatactApplicationForm($formId);
        $return = array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('CMS_DELETE_CONTACT_APPLICATION_FORM_SUCCESS'), 'noparentload' => true, 'formId' => $formId);

        return new JsonResponse($return);
    }

    /**
     * This method is used to activate/deativate contact application forms.
     * 
     * @param Request $request Request object
     * 
     * @return object JSON Response Object
     */
    public function activateFormAction(Request $request)
    {
        $formId = $request->get('formId');
        $em = $this->getDoctrine()->getManager();
        $returnArray = $em->getRepository('CommonUtilityBundle:FgCmsForms')->activateConatactApplicationForm($formId);
        $flash = ($returnArray['isActive'] == 1) ? $this->get('translator')->trans('CMS_ACTIVATE_CONTACT_APPLICATION_FORM_SUCCESS') : $this->get('translator')->trans('CMS_DEACTIVATE_CONTACT_APPLICATION_FORM_SUCCESS');
        $return = array('status' => 'SUCCESS', 'flash' => $flash, 'noparentload' => true, 'dataArray' => $returnArray);

        return new JsonResponse($return);
    }

    /**
     * This method is used to show delete theme configuration popup
     *  
     * @param Request $request request object
     * 
     * @return object View Template Render Object
     */
    public function deleteFormPopupAction(Request $request)
    {
        $popupTitle = $this->get('translator')->trans('CMS_DELETE_CONTACT_APPLICATION_FORM_POPUP_TITLE');
        $popupText = $this->get('translator')->trans('CMS_DELETE_CONTACT_APPLICATION_FORM_MESSAGE');
        $pageCount = '';
        $pageArray = array();
        $data['formId'] = $request->get('formId');
        $return = array("title" => $popupTitle, 'text' => $popupText, 'pageArray' => $pageArray, 'data' => $data, 'pageCount' => $pageCount, 'buttonText' => $this->get('translator')->trans('DELETE_BUTTON_TEXT'), 'type' => 'delete');

        return $this->render('ClubadminContactBundle:ApplicationForm:ConfirmationPopup.html.twig', $return);
    }

    /**
     * This function is used to save the contact application form settings
     * 
     * @param Request $request Request object
     * 
     * @return object JSON Response Object
     */
    public function saveContactFormWizardAction(Request $request)
    {
        $dataArray = array();
        $formId = $request->get('formId');
        $formName = trim($request->get('formname'));
        $dataArray['formFieldData'] = $request->get('formFieldData');
        $dataArray['captchaEnabled'] = $request->get('captchaEnabled', 0);
        $dataArray['formStage'] = $request->get('stage');
        $dataArray['formId'] = $formId;
        if ($request->getMethod() == 'POST') {
            if ($formId != '') {
                //check form name is unique or not
                $isUnique = ($dataArray['formStage'] == 'stage1') ? $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsForms')->checkWhetherFormNameIsUniqueInAClub($this->container->get('club')->get('id'), $formName, $formId) : 1;
                if ($isUnique) {
                    $dataArray['formName'] = $formName;
                    $contactFormObj = new FgContactForm($this->container);
                    $contactFormObj->saveContactForm($dataArray);
                    $return = array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('CONTACT_APPLICATION_FORM_SAVE_SUCCESS'), 'meta' => array('formId' => $formId));
                } else {
                    $return = array('status' => 'FAILURE', 'error' => $this->get('translator')->trans('FORM_DUPLICATE_MESSAGE'), 'errorArray' => array('error' => true));
                }
            }
        } else {
            $return = array('status' => 'FAILURE', 'error' => $this->get('translator')->trans('CONTACT_APPLICATION_FORM_SAVE_FAILED'), 'errorArray' => array('error' => true));
        }

        return new JsonResponse($return);
    }

    /**
     * 
     * @return template
     */
    public function createContactapplicationFormAction()
    {
        $club = $this->container->get('club');
        $clubType = ['standard_club', 'federation_club', 'sub_federation_club'];
        if (!in_array($club->get('type'), $clubType)) {
             throw $this->createNotFoundException($club->get('title') . ' have no access to this page');
        } else {
            $returnArray = array();
            $returnArray['breadCrumb'] = array('back' => $this->generateUrl('contact_application_form_list'));
            $returnArray['pageTitle'] = $this->get('translator')->trans('APPLICATION_FORM_TITLE');

            return $this->render('ClubadminContactBundle:ApplicationForm:Createcontactform.html.twig', $returnArray);
        }
    }

    /**
     * To save the contact form details stage0
     * 
     * @param Request $request
     */
    public function ContactFormsaveAction(Request $request)
    {
        $formTitle = $request->get('formName');
        $formType = $request->get('formType');
        $club = $this->container->get('club');
        $em = $this->getDoctrine()->getManager();
        //check form name is exist or not
        $noDuplicate = $em->getRepository('CommonUtilityBundle:FgCmsForms')->checkWhetherFormNameIsUniqueInAClub($club->get('id'), $formTitle);

        if ($noDuplicate) {
            $formId = $em->getRepository('CommonUtilityBundle:FgCmsForms')->saveContactForm($formTitle, $club->get('id'), $this->container->get('contact')->get('id'), $formType);
            $returnArray = array('status' => true, 'sync' => true, 'flash' => $this->container->get('translator')->trans('FORM_CREATE_SUCCESS'), 'redirect' => $this->container->get('router')->generate('contact_application_form_create', array('formId' => $formId)));
        } else {
            $returnArray = array('status' => false, 'error' => $this->container->get('translator')->trans('FORM_DUPLICATE_MESSAGE'), 'errorArray' => array('error' => true));
        }

        return new JsonResponse($returnArray);
    }
    
    /**
     * Function to get all contacts to be listed in intranet top navigation autocomplete search.
     *
     * @param Request $request Request object
     *
     * @return Json response
     */
    public function searchContactsAction(Request $request)
    {
        $conn = $this->container->get('database_connection');
        $em = $this->getDoctrine()->getManager();
        $searchTerm = FgUtility::getSecuredData($request->get('term'), $conn);
        $club = $this->get('club');
        $dobAttrId = $this->container->getParameter('system_field_dob');
        $contactlistClass = new Contactlist($this->container, '', $club, 'contact');
        $contactlistClass->contactType = 'all';
        $contacts = $em->getRepository('CommonUtilityBundle:FgCmContact')->getContactsForSearch($contactlistClass, $dobAttrId, $searchTerm, '', true, 0, 1);
        for ($i = 0; $i < count($contacts); $i++) {
            $contactId = $contacts[$i]['id'];
        }

        return new JsonResponse($contacts);
    }
    
    /**
     * This function is used to duplicate an existing contact application form
     * 
     * @param Request $request The request object
     * 
     * @return Object Json Response Object
     */
    public function duplicateFormAction(Request $request)
    {
        $formId = $request->get('formId');
        $contactFormObj = new FgContactForm($this->container);
        $status = $contactFormObj->duplicateExistingContactForm($formId);
        if ($status == 'success') {
            $return = array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('CONTACT_APPLICATION_FORM_DUPLICATE_SUCCESS'));
        } else {
            $return = array('status' => 'FAILURE', 'flash' => $this->get('translator')->trans('CONTACT_APPLICATION_FORM_DUPLICATE_FAILED'), 'noparentload' => true, 'error' => true);
        }

        return new JsonResponse($return);
    }
}
