<?php

/**
 * ContactApplicationController
 *
 * This controller is used to handle contact application from website, internal and backend.
 *
 * @package    CommonUtilityBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 *
 */

namespace Common\UtilityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Website\CMSBundle\Util\FgFormElement;
use Clubadmin\ContactBundle\Util\ContactDetailsSave;
use Common\UtilityBundle\Util\FgSettings;
use Clubadmin\ContactBundle\Util\FgContactForm;
use Common\UtilityBundle\Util\FgUtility;
use Symfony\Component\Intl\Intl;
use Website\CMSBundle\Util\FgSafeBrowsing;
use Symfony\Component\HttpFoundation\File\File;
use Common\UtilityBundle\Repository\Pdo\ContactPdo;
use Common\UtilityBundle\Util\FgPermissions;
use Common\UtilityBundle\Repository\Pdo\membershipPdo;

class ContactApplicationController extends Controller {

    /**
     * This function is used to render contact-application-form from the backend and internal login screen.
     * This page can be accessed via backend and internal login screen.
     *
     * @param int|string $formId contact application form id, default value is null
     * 
     * @return object View Template Render Object
     */
    public function contactApplicationAction($formId = '', $type = '')
    {
        $em = $this->getDoctrine()->getManager();
        $clubId = $this->container->get('club')->get('id');
        $formId = base64_decode($formId);
        $retrun = array();
        $retrun['formId'] = $formId;
        $retrun['clubTitle'] = $this->container->get('club')->get('title');
        $retrun['clubLogoUrl'] = $this->getClubLogoUrl();
        $retrun['captchaSitekey'] = $this->container->getParameter('googleCaptchaSitekey');
       
        $deflang = $this->container->get('club')->get('club_default_lang');
        $applicationForms = $em->getRepository('CommonUtilityBundle:FgCmsForms')->getContactApplicationFormList($clubId, $deflang, 1);
        //existing form ids of club
        $existingFormIds = array_column($applicationForms, 'id');  
        //checking current formId present in existing formIds
        if (!in_array($formId, $existingFormIds)) {
            $permissionObj = new FgPermissions($this->container);
            $permissionObj->checkClubAccess(0, 'formAccess');
        }  
        if ($formId == '' || count($formId) == 0) {
            $logoutUrl = $this->generateUrl('fairgate_user_security_logout');
            header('Location:' . $logoutUrl);
            exit;
        }
        $i = 1;
        foreach ($applicationForms as $form) {
            if ($form['id'] == $formId) {
                $retrun['activeTab'] = $i;
                $retrun['currentFormTitle'] = $form['title'];
            }
            $retrun['tabs'][] = array('text' => $form['title'], 'url' => $this->generateUrl('external_contact_application', array('formId' => base64_encode($form['id']), 'type' => '')));
            $i++;
        }
        $retrun['formDetails'] = $this->getFormDetails($formId);
        $retrun['type'] = $type;

        return $this->render('CommonUtilityBundle:ContactApplicationForm:ApplicationForm.html.twig', $retrun);
    }

    /**
     * This is common functionn to save data from contact-application website, internal and backend.
     *
     * @param object $request   \Symfony\Component\HttpFoundation\Request
     *
     * @return object JSON Response Object
     */
    public function contactApplicationSaveAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $formData = $request->get('inquiry');
        $formattedContactData = $this->formatFormContactData($formData['contact']);
        $formContactData = $formattedContactData['formContactData'];
        $contactFiles = $formattedContactData['contactFiles'];

        $formId = $request->get('formId');
        $scCommunication = $this->container->getParameter('system_category_communication');
        $sfPrimaryemail = $this->container->getParameter('system_field_primaryemail');
        if($this->checkEmailExist('', $formContactData[$scCommunication][$sfPrimaryemail])) {
            $return['status'] = 'error';
              $return['element'] = $scCommunication.''.$scCommunication;
            $return['emailExists'] = 1;
            $return['msg'] = $this->container->get('translator')->trans('EMAIL_EXITS');
            return new JsonResponse($return);
        }
        $contactFormType = $em->getRepository('CommonUtilityBundle:FgCmsForms')->find($formId)->getContactFormType();
        $formContactData['system']['contactType'] = ($contactFormType == 'single_person') ? 'Single person' : 'Company';
        $categoryCompany = $this->container->getParameter('system_category_company');
        $formContactData['system']['mainContact'] = ($contactFormType == 'company_with_main_contact') ? 'withMain' : 'noMain';
        $formContactData['system']['same_invoice_address'] = 0; //Temp fix to Bug 37570-Issue:2
        //reset -Returns the value of the first array element,
        $formContactData['system']['membership'] = reset($formData['club-membership']);         
        $fieldDetailsArray = $this->getFieldDetailsArray($formContactData['system']['contactType']);
        $contact = new ContactDetailsSave($this->container, $fieldDetailsArray, array(), false);
        $contactId = $contact->saveContact($formContactData, array(), array(), array(), 0, 'NULL', array(), 1, 1);
        
        $validation = $this->validate($formData);
        if ($validation['status'] == 'success')
        {
            $updatedFormData = $this->uploadFormFieldFiles($validation);
            $validation = $this->uploadContactFormFieldFiles($contactFiles, $updatedFormData, $contactId);
            $formValuesJson = json_encode($validation['data']);
            $contactDetails = $em->getRepository('CommonUtilityBundle:FgCmContact')->getNameOfAContact($contactId);
            $contactName = $contactDetails[0]['name'];
            $applicationId = $em->getRepository('CommonUtilityBundle:FgCmsContactFormApplications')->saveFormDetails($formValuesJson, $formId, $contactName, $contactId);
            $this->sendConfirmationAndNotificationMails($request, $applicationId);
        }

        return new JsonResponse($validation);
    }
    
    /**
     * This function is used get contact-application-form details for internal and backend.
     * This page can be accessed via backend and internal login screen.
     * 
     * @param int $formId contact application form id
     * 
     * @return object JSON Response Object
     */
    private function getFormDetails($formId = '')
    {
        $em = $this->getDoctrine()->getManager();
        $club = $this->container->get('club');
        $formObj = $em->getRepository('CommonUtilityBundle:FgCmsForms')->find($formId);
        $formElementObj = new FgFormElement($this->container);
        $result['formData'] = $formElementObj->getFormElementDataForView($formId, 2);
        $result['formOption'] = $em->getRepository('CommonUtilityBundle:FgCmsForms')->getFormOptions($formId, $this->container, $club);
        $result['defLang'] = $this->container->get('club')->get('club_default_lang');
        
        $result['formStage'] = $formObj->getFormStage();
        $objMembershipPdo = new membershipPdo($this->container);
        $clubMemberships = $objMembershipPdo->getMemberships($club->get('type'), $club->get('id'));
        $result['contactFormOptions'] = array(
            'allClubMemberships' => $this->formatMembership($clubMemberships),
            'countryFields' => $this->container->getParameter('country_fields'),
            'systemFieldGender' => $this->container->getParameter('system_field_gender'),
            'systemFieldCorrLang' => $this->container->getParameter('system_field_corress_lang'),
            'systemFieldSalutation' => $this->container->getParameter('system_field_salutaion'),
            'countryList' => FgUtility::getCountryListchanged(),
            'clubLanguages' => $this->getLanguageArray(),
            'formId' => $formId
        );

        return $result;
    }
    
    /**
     * This method is used to validate all form field values.
     * 
     * @param array $data array of form data
     * 
     * @return array $validate
     */
    private function validate($data)
    {
        $formData = $this->formatContactFormdata($data);
        $validateContactForm = $this->validateForm($formData, 'contact');    
        $validateForm = $this->validateForm($data['form'], 'form');
        $validate = array();
        $validate['status'] = 'success'; 
        if($validateForm['status'] != 'success' || $validateContactForm['status'] != 'success'){
            $validate['status'] = 'error'; 
        }
        $validate['data']['contact'] = $validateContactForm['inquiry'];
        $validate['data']['form'] = $validateForm['inquiry'];
        $validate['data']['club-membership'] = $data['club-membership'];
        
        return $validate;
    }
    
    /**
     * This method is used to remove one level from array hierarchy of contact form array.
     * 
     * @param array $data Array of form data to be formatted
     * 
     * @return array $result
     */
    private function formatContactFormdata($data)
    {
        $result = array();
        foreach ($data['contact'] as $value) {
            $result = $result + $value;
        }

        return $result;
    }

    /**
     * This method is used to validate contact application data.
     * 
     * @param  array $formData Type of form (form/contact)
     * 
     * @return array $return
     */
    private function validateForm($formData, $formType = 'form')
    {
        $return['status'] = 'success';
        foreach ($formData as $fieldId => $fieldValue) {
            $return['inquiry'][$fieldId] = $fieldValue;
            $fieldType = $this->getFieldType($fieldId, $formType);
            switch ($fieldType) {
                case 'fileupload':
                case 'imageupload':
                    if ($fieldValue != '') {
                        $return['inquiry']['files'][$fieldId] = $fieldValue;
                    }
                    break;
                case 'singleline':
                    $return['inquiry'][$fieldId] = str_replace(array("\n", "\t"), '', $fieldValue);
                    break;
                case 'email':
                    if (filter_var($fieldValue, FILTER_VALIDATE_EMAIL) === false) {
                        $return['status'] = 'error';
                        $return['error'][] = 'invalid email';
                    }
                    break;
                case 'date':
                    if ($fieldValue != '') {
                        if (date_create_from_format('Y-m-d', $fieldValue)) {
                            $return['inquiry'][$fieldId] = $fieldValue;
                        } else {
                            $date = new \DateTime();
                            $return['inquiry'][$fieldId] = $date->createFromFormat(FgSettings::getPhpDateFormat(), $fieldValue)->format('Y-m-d');
                        }
                    }
                    break;
                case 'time':
                    if ($fieldValue != '') {
                        $date = new \DateTime();
                        $return['inquiry'][$fieldId] = $date->createFromFormat(FgSettings::getPhpTimeFormat(), $fieldValue)->format('H:i');
                    }
                    break;
                case 'url':
                    if ($fieldValue != '') {
                        $safeBrowsing = new FgSafeBrowsing();
                        $check = $safeBrowsing->validateUrl($fieldValue);
                        $return['URL'] = $check;
                        if (!empty($check->matches)) {
                            $return['status'] = 'error';
                        }
                    }
                    break;
            }
        }

        return $return;
    }
    
    /**
     * This method is used to get type of form field. 
     * 
     * @param type $fieldId Form field is
     * @param type $type    Form type - contact or form
     * 
     * @return string $fieldType
     */
    private function getFieldType($fieldId, $type = 'form')
    {
        $fieldType = '';
        if ($type == 'contact') {
            $fieldObj = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmAttribute')->find($fieldId);
            $fieldType = $fieldObj->getInputType();
        } else {
            $fieldObj = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElementFormFields')->find($fieldId);
            $fieldType = $fieldObj->getFieldType();
        }

        return $fieldType;
    }
    
    /**
     * Method to upload file and save to file
     * 
     * @param string $fileName Temp filename of uploaded file
     * @param string $formFieldType Type of form field type. (form_field, contact_file_field, contact_image_field)
     * @param int $clubId club id where the file to be uploaded
     * 
     * @return string saved file name
     */
    private function uploadToFilemanager($fileName, $formFieldType, $clubId)
    {
        $source = ($formFieldType == 'contact_file_field') ? 'contactfield_file' : (  ($formFieldType == 'contact_image_field') ? 'contactfield_image' : 'contact_application_file');
        $fileNameopt = explode('#-#', $fileName);
        $tempFileName = $fileNameopt[1];
        $fileNameOriginal = str_replace($fileNameopt[0] . '--', '', $tempFileName);
         $formUploadFolder = FgUtility::getUploadFilePath($clubId, $source);
        if (!is_dir($formUploadFolder)) {
            mkdir($formUploadFolder, 0700, true);
        }
        $rootPath = FgUtility::getRootPath($this->container);
        $fileNameReq =  FgUtility::getFilename("$rootPath/$formUploadFolder", $fileNameOriginal);
        $filepath = FgUtility::getUploadDir()."/temp/";
        //move file from temporary location to actual path       
         if (file_exists($filepath . $tempFileName)) {
                $attachmentObj = new File($filepath . $tempFileName, false);
                $attachmentObj->move($rootPath."/".$formUploadFolder, $fileNameReq);
          }
          
        return $fileNameReq;
    }
    
    /**
     * This function is used to format $memberships array
     * 
     * @param array $memberships club memebership array
     * 
     * @return array $result formatted array output
     */
    private function formatMembership($memberships) {
        $result = array();
        $defLang = $this->container->get('club')->get('club_default_lang');
        foreach ($memberships as $key => $value) {
            $result[] = array(
                'id' => $key,
                'titleLang' => ($value['allLanguages'][$defLang]['titleLang'] != '') ? $value['allLanguages'][$defLang]['titleLang'] : $value['titleLang']
            );
        }
        return $result;
    }
    /**
     * This function is used to get club language array.
     *
     * @return array $fieldLanguages
     */
    private function getLanguageArray()
    {
        $languages = Intl::getLanguageBundle()->getLanguageNames();
        $fieldLanguages = array();
        $clubLanguages = $this->container->get('club')->get('club_languages');
        foreach ($clubLanguages as $shortName) {
            $fieldLanguages[$shortName] = $languages[$shortName];
        }

        return $fieldLanguages;
    }

    private function sendConfirmationAndNotificationMails($request, $applicationId)
    {
        $em = $this->getDoctrine()->getManager();
        $applicationObj = $em->getRepository('CommonUtilityBundle:FgCmsContactFormApplications')->find($applicationId);
        $jsonData = json_decode($applicationObj->getFormData(), true);
        
        $clubService = $this->get('club');
        $clubId = $clubService->get('id');
        
        $fields = $em->getRepository('CommonUtilityBundle:FgCmsPageContentElementFormFields')->getContactFormFieldsForMailContent($clubId, $applicationObj->getForm()->getId());
        $membership = array();
        if (is_array($jsonData['club-membership'])) {
            $objMembershipPdo = new membershipPdo($this->container);
            $membershipFields = $objMembershipPdo->getMemberships($clubService->get('clubType'), $clubId, $clubService->get('subFederationId'), $clubService->get('federationId'));
            foreach ($membershipFields as $key => $memberCat) {
                $memberships[$key]['default'] = $memberCat['membershipName'];
                foreach ($memberCat['allLanguages'] as $lang => $titleArr) {
                    $memberships[$key][$lang] = $titleArr['titleLang'];
                }
            }
            $membership = $this->formatArray($fields, $jsonData['club-membership']);
        }
        $formFields = $this->formatArray($fields, $jsonData['form']);
        $contactFields = $this->formatArray($fields, $jsonData['contact']);
        $data = $contactFields + $membership + $formFields;
        ksort($data);
        $contactFormObj = new FgContactForm($this->container);
        $templateParameters = $contactFormObj->getNotificationMailParams($applicationObj->getForm()->getId(), 'notification');
        $templateParameters['formValues'] = $data;
        $templateParameters['clubMemberships'] = $memberships;
        $templateParameters['attributeIds'] = array('gender' => $this->container->getParameter('system_field_gender'), 'salutation' => $this->container->getParameter('system_field_salutaion'));
        $baseurlArr = FgUtility::getMainDomainUrl($this->container, $this->container->get('club')->get('id')); //FAIR-2489
        $baseurl = $baseurlArr['baseUrl']; //Fair-2484   

        if (isset($templateParameters['recipients'])) {
            $recipientDetails = $em->getRepository('CommonUtilityBundle:FgCmContact')->getContactLanguageAndEmailDetails($templateParameters['recipients']);        
            $currentLocateSettings = array(0 => array('default_lang' => $clubService->get('default_lang'), 'default_system_lang' => $clubService->get('default_system_lang')));
            $dateObj = new \DateTime();
            $createdAt = date_format($applicationObj->getCreatedAt(), 'Y-m-d H:i:s');
            foreach ($recipientDetails as $recipientDetail) {
                    if ($recipientDetail['primaryEmail']) {
                        $templateParameters['contactLang'] = $recipientDetail['default_lang'];
                        $formName = $em->getRepository('CommonUtilityBundle:FgCmsForms')->find($applicationObj->getForm()->getId())->getTitle();
                        //set locale with respect to particular contact
                        $this->container->get('contact')->setContactLocale($this->container, $request, array($recipientDetail));
                        //To set the club TITLE, SIGNATURE based on default language
                        $this->container->get('contact')->setClubParamsOnLangauge($this->container->get('club'));
                        $phpDateFormat = FgSettings::getPhpDateFormat();
                        $phpTimeFormat = FgSettings::getPhpTimeFormat();
                        $dateString = $dateObj->createFromFormat('Y-m-d H:i:s', $createdAt)->format($phpDateFormat);
                        $timeString = $dateObj->createFromFormat('Y-m-d H:i:s', $createdAt)->format($phpTimeFormat);
                        $pageLink = '<a href="' . $templateParameters['pageLink'] . '" target="_blank">';
                        $templateParameters['introText'] = $this->container->get('translator')->trans('CONTACT_APPLICATION_FORM_NOTIFICATION_MAIL_CONTENT', array('%date%' => $dateString, '%time%' => $timeString, '%a%' => $pageLink, '%b%' => '</a>'));
                        $templateParameters['subject'] = $this->container->get('translator')->trans('CONTACT_APPLICATION_FORM_NOTIFICATION_MAIL_SUBJECT', array('%formName%' => $formName, '%clubName%' => $clubService->get('title')));   
                        $templateParameters['clubMembershipTitle'] = $this->container->get('translator')->trans('CONTACT_APPLICATION_FORM_NOTIFICATION_MAIL_CLUB_MEMBERSHIP');
                        //set logo, title and signature of club
                        $clubLogoPath = $this->container->get('club')->getClubLogoPath(false);
                        $templateParameters['logoURL'] = ($clubLogoPath == '') ? '' : $baseurl . '/' . $clubLogoPath; 
                        $templateParameters['clubTitle'] = $this->container->get('club')->get('title');
                        $templateParameters['signature'] = $this->container->get('club')->get('signature');
                        $templateParameters['body'] = $this->renderView('ClubadminContactBundle:ContactConfirmation:contactFormMailTemplate.html.twig', $templateParameters);
                        try {
                            $contactFormObj->sendSwiftMesage($templateParameters['body'], $recipientDetail['primaryEmail'], $templateParameters['senderEmail'], $templateParameters['subject']);
                        } catch (\Exception $e) {
                            $error = $e;
                        }
                    }
            }
            //reset contact locale with respect to logged in contact
            $this->container->get('contact')->setContactLocale($this->container, $request, $currentLocateSettings);
            //To set the club TITLE, SIGNATURE based on default language
            $this->container->get('contact')->setClubParamsOnLangauge($this->container->get('club'));
        }
        $contactDetailsArr = $em->getRepository('CommonUtilityBundle:FgCmContact')->getContactLanguageDetails($applicationObj->getClubContact()->getId());
        $contactDetails = $contactDetailsArr[0];
        $toEmails = array_unique($this->toEmails($clubId, $applicationObj, $jsonData));
        if (count($toEmails) > 0) {
            $mailTemplateParameters = $contactFormObj->getNotificationMailParams($applicationObj->getForm()->getId(), 'confirmation', $contactDetails['default_lang']);
            $mailTemplateParameters['formValues'] = $data;
            $mailTemplateParameters['clubMemberships'] = $memberships;
            $mailTemplateParameters['attributeIds'] = array('gender' => $this->container->getParameter('system_field_gender'), 'salutation' => $this->container->getParameter('system_field_salutaion'));
            //set locale with respect to particular contact
            $this->container->get('contact')->setContactLocale($this->container, $request, $contactDetails);
            //To set the club TITLE, SIGNATURE based on default language
            $this->container->get('contact')->setClubParamsOnLangauge($this->container->get('club'));
            $mailTemplateParameters['contactLang'] = $contactDetails['default_lang'];
            $mailTemplateParameters['clubMembershipTitle'] = $this->container->get('translator')->trans('CONTACT_APPLICATION_FORM_NOTIFICATION_MAIL_CLUB_MEMBERSHIP');
            //set logo, title and signature of club
            $clubLogoPath = $this->container->get('club')->getClubLogoPath(false);
            $mailTemplateParameters['logoURL'] = ($clubLogoPath == '') ? '' : $baseurl . '/' . $clubLogoPath; 
            $mailTemplateParameters['clubTitle'] = $this->container->get('club')->get('title');
            $mailTemplateParameters['signature'] = $this->container->get('club')->get('signature');
            $mailTemplateParameters['body'] = $this->renderView('ClubadminContactBundle:ContactConfirmation:contactFormMailTemplate.html.twig', $mailTemplateParameters);
            try {
                $contactFormObj->sendSwiftMesage($mailTemplateParameters['body'], $toEmails, $mailTemplateParameters['senderEmail'], $mailTemplateParameters['subject']);
            } catch (\Exception $e) {
                $error = $e;
            }
        }
    }
    
    private function toEmails($clubId, $appObj, $jsonData){
        $emailFields = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElementFormFields')->getAllEmailFieldsForContact($appObj->getForm()->getId(), $clubId);
        $emails = array();
        foreach($emailFields as $emailField) {
            if($emailField['attribute'] > 0){
                $flag = array_key_exists($emailField['attribute'], $jsonData['contact']);
                if($flag > 0){
                    $emails[]= $jsonData['contact'][$emailField['attribute']];
                }
            }else{
                $flag = array_key_exists($emailField['formfield'], $jsonData['form']);
                if($flag > 0){
                    $emails[]= $jsonData['form'][$emailField['formfield']];
                }
            }
        } 
        return $emails;
        
    }
    
    private function formatArray($fields, $jsonDatas)
    {
        $formattedData = array();
        foreach ($jsonDatas as $key => $jsonData) {
            if ($key != 'files') {
                $key = 'a' . $key;
                $fieldValue = '';
                switch ($fields[$key]['fieldType']) {
                    case 'select':
                    case 'radio':
                    case 'checkbox':
                        if ($key == 'a' . $this->container->getParameter('system_field_corress_lang')) {
                            $jsonData = Intl::getLanguageBundle()->getLanguageName($jsonData);
                        }
                        if ($key == 'a' . $this->container->getParameter('system_field_nationality1') || $key == 'a' . $this->container->getParameter('system_field_nationality2')) {
                            $jsonData = Intl::getRegionBundle()->getCountryName($jsonData);
                        }
                        if (is_array($jsonData)) {
                            $fieldValue = implode(', ', $jsonData);
                        } else {
                            $fieldValue = $jsonData;
                        }
                        break;
                    default:
                        $fieldValue = $jsonData;
                        break;
                }

                if ($fieldValue != '') {
                    $sortOrder = $fields[$key]['sortOrder'];
                    $formattedData[$sortOrder] = array(
                        'fieldNameLang' => $fields[$key]['fieldnameLang'],
                        'fieldName' => $fields[$key]['fieldname'],
                        'fieldValue' => str_replace('<script', '<scri&nbsp;pt', (strip_tags($fieldValue))),
                        'fieldType' => $fields[$key]['fieldType'],
                        'attributeId' => $fields[$key]['attribute'],
                        'formFieldType' => $fields[$key]['formFieldType'],
                        'fieldOptions' => $fields[$key]['fieldOptions'],
                    );
                    if($fields[$key]['fieldType'] == 'fileupload' || $fields[$key]['fieldType'] == 'imageupload')
                    {
                        $createdClubId = 0;
                        if ($fields[$key]['formFieldType'] == 'contact') {
                            $attrObj = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmAttribute')->find($fields[$key]['attribute']);
                            $createdClubId = $attrObj->getClub()->getId();
                        }
                        
                        $params = array( 'folder' => ($fields[$key]['formFieldType'] == 'contact' ? ($fields[$key]['fieldType'] == 'fileupload' ? 'contactfield_file' : 'contactfield_image') : 'contact_application_file'), 'file' => str_replace('<script', '<scri&nbsp;pt', (strip_tags($fieldValue['fileNameNew']))));
                        $formattedData[$sortOrder]['fileUrl'] = FgUtility::generateUrlForSharedClub($this->container, 'filemanager_download_contact_application', $createdClubId, $params);
                        $formattedData[$sortOrder]['fieldValue'] = str_replace('<script', '<scri&nbsp;pt', (strip_tags($fieldValue['fileNameOriginal'])));
                    }             
                }
            }
        }
        
        return $formattedData;
    }
    
    /**
    * Function to get clublogo url
    *
    * @return string|null  $clubLogoUrl club logo url
    */
   private function getClubLogoUrl()
   {
        $clubObj = $this->container->get('club');
        $clubLogo = $clubObj->get('logo');
        $rootPath = FgUtility::getRootPath($this->container);
        $baseurl = FgUtility::getBaseUrl($this->container);
        if ($clubLogo == '' || !file_exists($rootPath . '/' . FgUtility::getUploadFilePath($clubObj->get('id'), 'clublogo', false, $clubLogo))) {
                $clubLogoUrl = '';
        } else {
                $clubLogoUrl = $baseurl . '/' . FgUtility::getUploadFilePath($clubObj->get('id'), 'clublogo', false, $clubLogo);
        }
        return $clubLogoUrl;
   }

   /**
     * For check the email is exist or not.
     *
     * @param Int    $contactId Contact id
     * @param String $email     Email
     *
     * @return bool
     */
    private function checkEmailExist($contactId, $email)
    {
        $club = $this->container->get('club');
        $conn = $this->container->get('database_connection');
        $primaryEmail = $this->container->getParameter('system_field_primaryemail');
        if ($email == '') {
            return false;
        }
        $em = $this->getDoctrine()->getManager();
        $result = $em->getRepository('CommonUtilityBundle:FgCmAttribute')->searchEmailExists($conn, $club, $primaryEmail, $email, $contactId, false);
        
        return count($result) > 0 ? true : false;
    }

    /**
     * get club details array
     *
     * @return type array
     */
    private function getFieldDetailsArray($fieldType)
    {
        $em = $this->getDoctrine()->getManager();
        $clubObj = $this->container->get('club');
        $container = $this->container->getParameterBag();
        $clubIdArray = array('clubId' => $clubObj->get('id'),
            'federationId' => $clubObj->get('federation_id'),
            'subFederationId' => $clubObj->get('sub_federation_id'),
            'clubType' => $clubObj->get('type'),
            'correspondanceCategory' => $container->get('system_category_address'),
            'invoiceCategory' => $container->get('system_category_invoice'));
        $clubIdArray['address'] = $this->get('translator')->trans('CONTACT_FIELD_ADDRESS');
        $clubIdArray['sysLang'] = $clubObj->get('default_system_lang');
        $clubIdArray['defSysLang'] = $clubObj->get('club_default_lang');
        $clubIdArray['clubLanguages'] = $clubObj->get('club_languages');

        $fieldDetails1 = $em->getRepository('CommonUtilityBundle:FgCmAttributeset')->getAllClubContactFields($clubIdArray, $this->container->get('database_connection'), 0);
        $fieldDetailArray = array('fieldType' => $fieldType, 'memberships' => $this->getMembershipArray(), 'clubIdArray' => $clubIdArray, 'fedMemberships' => $this->fedMemberships, 'fullLanguageName' => $this->getLanguageArray(), 'selectedMembership' => '', 'contactId' => false, 'deletedFiles' => array());
        
        $fieldDetails = $em->getRepository('CommonUtilityBundle:FgCmAttributeset')->fieldItrator($fieldDetails1);
        $fieldDetails = array_merge($this->setTerminologyTerms($fieldDetails), $fieldDetailArray);
        
        return $fieldDetails;
    }
    
        /**
     * Function to get membership array.
     *
     * @return type
     */
    private function getMembershipArray()
    {
        $clubObj = $this->container->get('club');
        $em = $this->getDoctrine()->getManager();
        $objMembershipPdo = new membershipPdo($this->container);
        $membersipFields = $objMembershipPdo->getMemberships($clubObj->get('type'), $clubObj->get('id'), $clubObj->get('sub_federation_id'), $clubObj->get('federation_id'));
        $this->fedMemberships = array();
        $this->fedMembers = '';
        $clubId = ($clubObj->get('type') == 'federation') ? $clubObj->get('id') : $clubObj->get('federation_id');
        $clubDefaultLang = $clubObj->get('default_lang');
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
     * This function is used to form field files and update form data. 
     * 
     * @param array $data form data
     * 
     * @return array $data
     */
    private function uploadFormFieldFiles($data)
    {
        foreach ($data['data']['form']['files'] as $fieldId => $fileName) {
            $clubId = $this->container->get('club')->get('id');
            $fileNameNew = $this->uploadToFilemanager($fileName, 'form_field', $clubId);
            $fileNameopt = explode('#-#', $fileName);
            $fileNameOriginal = str_replace($fileNameopt[0] . '--', '', $fileNameopt[1]);
            $data['data']['form'][$fieldId] = array('fileNameNew' => $fileNameNew, 'fileNameOriginal' => $fileNameOriginal);
            $data['data']['form']['files'][$fieldId] = $fileNameOriginal;
        }

        return $data;
    }

    /**
     * This function is used to contact field files and update form data. 
     * 
     * @param array $contactFiles array of files to be uploaded
     * @param array $data contact form data
     * @param array $contactId created contact id
     * 
     * @return array $data
     */
    private function uploadContactFormFieldFiles($contactFiles, $data, $contactId)
    {
        $em = $this->getDoctrine()->getManager();
        $contactPdo = new ContactPdo($this->container);
        foreach ($contactFiles as $fieldId => $fileData) {
            $fileName = $fileData['name'];
            $formFieldType = $fileData['type'] == 'fileupload' ? 'contact_file_field' : 'contact_image_field';
            $attrObj = $em->getRepository('CommonUtilityBundle:FgCmAttribute')->find($fieldId);
            $attrClubId = $attrObj->getClub()->getId();
            $fileNameNew = $this->uploadToFilemanager($fileName, $formFieldType, $attrClubId);
            $fileNameopt = explode('#-#', $fileName);
            $fileNameOriginal = str_replace($fileNameopt[0] . '--', '', $fileNameopt[1]);
            $data['data']['contact'][$fieldId] = array('fileNameNew' => $fileNameNew, 'fileNameOriginal' => $fileNameOriginal);
            $data['data']['contact']['files'][$fieldId] = $fileNameOriginal;
            
            $contactObj = $em->getRepository('CommonUtilityBundle:FgCmContact')->find($contactId);
            $fedContactId = $contactObj->getFedContact()->getId();
            $contactDetails =$em->getRepository('CommonUtilityBundle:FgCmContact')->findBy(array('fedContact' => $fedContactId));
            foreach ($contactDetails as $value)
            {
                $contactPdo->updateContactField($fieldId, $value->getId(), $fileNameNew);
            }
            
        }

        return $data;
    }
    
    /**
     * This function is used to format contact data by input field type 
     * 
     * @param array $formContactData data to be formatted
     * 
     * @return array $result
     */
    private function formatFormContactData($formContactData)
    {
        $em = $this->getDoctrine()->getManager();
        $contactFiles = array();
        $result = array();
        foreach ($formContactData as $formKey => $formValue) {
            foreach ($formValue as $key => $value) {
                $data = $em->getRepository('CommonUtilityBundle:FgCmAttribute')->getProperties($key);
                if ($data[0]['inputType'] == 'date') {
                    $date = new \DateTime();
                    $formateDate = $date->createFromFormat(FgSettings::getPhpDateFormat(), $formContactData[$formKey][$key]);
                    $formContactData[$formKey][$key] = $formateDate->format('Y-m-d');
                }
                if ($data[0]['inputType'] == 'fileupload' || $data[0]['inputType'] == 'imageupload') {
                    $contactFiles[$key]['name'] = $value;
                    $contactFiles[$key]['type'] = $data[0]['inputType'];
                    $fileNameopt = explode('#-#', $value);
                    $fileNameOriginal = str_replace($fileNameopt[0] . '--', '', $fileNameopt[1]);
                    $formContactData[$formKey][$key] = $fileNameOriginal;
                }
            }
        }
        $result['formContactData'] = $formContactData;
        $result['contactFiles'] = $contactFiles;

        return $result;
    }

}
