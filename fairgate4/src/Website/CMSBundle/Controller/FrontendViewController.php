<?php

/**
 * FrontendViewController
 */
namespace Website\CMSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Util\FgSettings;
use Symfony\Component\HttpFoundation\Request;
use Website\CMSBundle\Util\FgSafeBrowsing;
use Symfony\Component\HttpFoundation\File\File;

/**
 * FrontendViewController - For handling for frontend view
 * 
 * @package         FrontendViewController
 * @subpackage      controller
 * @author          pitsolutions.ch
 * @version         Fairgate V4
 */
class FrontendViewController extends Controller
{

    /**
     * @var object entity manager 
     */
    public $em;

    /**
     * Method to confirmation mails and notification mails after a form submission
     * 
     * @param object $request   request object
     * @param int    $inquiryId inquiryId
     * @param int    $elementId elementId
     * @param json   $formData  formData
     * @param string $menu      page menu (in url)
     * @param int    $pageId    current pageId
     * 
     * @return boolean
     */
    private function sendNotificationMails($request, $inquiryId, $elementId, $formData, $menu, $pageId)
    {
        $this->em = $this->getDoctrine()->getManager();
        $elementObj = $this->em->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->find($elementId);
        $emailSettings = $this->em->getRepository('CommonUtilityBundle:FgCmsForms')->getFormOptions($elementObj->getForm()->getId(), $this->container, $this->container->get('club'));
        $formFields = $this->em->getRepository('CommonUtilityBundle:FgCmsPageContentElementFormFields')->getFormFieldNames($this->container->get('club')->get('id'), $elementId);
        $formDatas = json_decode($formData, true);
        $formValues = $this->formatInquiries($formFields, $formDatas);
        $rootPath = FgUtility::getBaseUrl($this->container);
        $formUploadFolder = FgUtility::getUploadFilePath($this->container->get('club')->get('id'), 'form_uploads');
        $baseUrlArr = FgUtility::getMainDomainUrl($this->container, $this->container->get('club')->get('id'));
        $baseUrl = $baseUrlArr['baseUrl']; //Fair-2484         
        //parameters to build mail template
        $templateParameters = array(            
            'baseUrl' => $baseUrl,            
            'uploadPath' => "$rootPath/$formUploadFolder/",
            'formValues' => $formValues
        );

        //conirmation mails
        $confirmationEmails = $this->getConfirmationEmails($elementId, $formDatas);
        if (count($confirmationEmails) > 0) {
            $this->sendMailsToContacts('confirmation', $emailSettings, $templateParameters, implode(',', $confirmationEmails));
        }
        //notification mails
        if ($emailSettings['recipients']) {
            $this->sendNotifications($request, $emailSettings, $pageId, $inquiryId, $templateParameters, $menu);
        }

        return true;
    }

    /**
     * Method to send notification emails
     * 
     * @param object $request            request object
     * @param array  $emailSettings      email settings in form options(2nd step)
     * @param int    $pageId             current pageId
     * @param int    $inquiryId          inquiryId
     * @param array  $templateParameters parameters to buld mail template
     * @param string $menu               page menu (in url)
     */
    private function sendNotifications($request, $emailSettings, $pageId, $inquiryId, $templateParameters, $menu)
    {
        //notification mails
        $recipientDetails = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getContactLanguageAndEmailDetails($emailSettings['recipients']);
        $clubId = $this->container->get('club')->get('id');
        $guestTrans = $this->container->get('translator')->trans('CMS_GUEST');
        $formInquiryDetails = $this->em->getRepository('CommonUtilityBundle:FgCmsPageContentElementFormInquiries')->getFormInquiryDetails($clubId, $guestTrans, $inquiryId);
        $formInquiryDetails['pageTitles'] = $this->em->getRepository('CommonUtilityBundle:FgCmsPage')->getPageTite($pageId);
        $formInquiryDetails['menu'] = $menu;
        $currentLocateSettings = array(0 => array('default_lang' => $this->container->get('club')->get('default_lang'), 'default_system_lang' => $this->container->get('club')->get('default_system_lang')));
        foreach ($recipientDetails as $recipientDetail) {
            //set locale with respect to particular contact
            $this->container->get('contact')->setContactLocale($this->container, $request, array($recipientDetail));
            //To set the club TITLE, SIGNATURE based on default language
            $this->container->get('contact')->setClubParamsOnLangauge($this->container->get('club'));
            if ($recipientDetail['primaryEmail']) {
                $this->sendMailsToContacts('notification', $emailSettings, $templateParameters, $recipientDetail['primaryEmail'], $formInquiryDetails);
            }
        }
        //reset contact locale with respect to logged in contact
        $this->container->get('contact')->setContactLocale($this->container, $request, $currentLocateSettings);
        //To set the club TITLE, SIGNATURE based on default language
        $this->container->get('contact')->setClubParamsOnLangauge($this->container->get('club'));
    }

    /**
     * Method to send mails to contacts in their correspondence language
     * 
     * @param string $mailType           notification/confirmation
     * @param array  $emailSettings      email settings in form options(2nd step)
     * @param array  $templateParameters parameters to buld mail template
     * @param string $emails             comma separated emails to send
     * @param array  $formInquiryDetails array of menu, pagetitle, form title, contact name, timestamp
     */
    private function sendMailsToContacts($mailType, $emailSettings, $templateParameters, $emails, $formInquiryDetails = array())
    {
        $this->em = $this->getDoctrine()->getManager();
        //contact correspondence lang if contact is logged-in, else club-correspondence lang
        $contactLang = $this->container->get('club')->get('default_lang');
        //parameters to build mail template
        if ($mailType == 'notification') {
            $checkClubHasDomain = $this->em->getRepository('CommonUtilityBundle:FgDnClubDomains')->checkClubHasDomain($this->container->get('club')->get('id'));
            if ($checkClubHasDomain) {
                $rootPath = $checkClubHasDomain['domain'];
            } else {
                $rootPath = FgUtility::getBaseUrl($this->container);
            }
            $pageLink = FgUtility::generateUrlForHost($this->container, $this->container->get('club')->get('url_identifier'), 'website_public_page_menus', $checkClubHasDomain, array('menu' => $formInquiryDetails['menu']));
            $formName = ($formInquiryDetails['formTitle'][$contactLang]) ? $formInquiryDetails['formTitle'][$contactLang] : $formInquiryDetails['formTitle']['default'];
            $pageTitle = ($formInquiryDetails['pageTitles'][$contactLang]) ? $formInquiryDetails['pageTitles'][$contactLang] : $formInquiryDetails['pageTitles']['default'];
            $dateObj = new \DateTime();
            $phpDateFormat = FgSettings::getPhpDateFormat();
            $phpTimeFormat = FgSettings::getPhpTimeFormat();
            $dateString = $dateObj->createFromFormat('Y-m-d H:i:s', $formInquiryDetails['timestamp'])->format($phpDateFormat);
            $timeString = $dateObj->createFromFormat('Y-m-d H:i:s', $formInquiryDetails['timestamp'])->format($phpTimeFormat);
            $formUploadFolder = FgUtility::getUploadFilePath($this->container->get('club')->get('id'), 'form_uploads');
            $templateParameters['uploadPath'] = "$rootPath/$formUploadFolder/";            
            $templateParameters['mailContent'] = $this->container->get('translator')->trans('CMS_FORM_NOTIFICATION_MAILCONTENT', array('%formName%' => $formName, '%dateString%' => $dateString, '%timeString%' => $timeString, '%contactName%' => $formInquiryDetails['contactName'], '%pageTitle%' => $pageTitle, '%pageLink%' => $pageLink));
            $subject = $this->container->get('translator')->trans('CMS_FORM_NOTIFICATION_SUBJECT', array('%formName%' => $formName, '%clubName%' => $this->container->get('club')->get('title')));
            $senderEmail = 'noreply@fairgate.ch';
            $parameter = array('fileName' => '**FILENAME**');
            $templateParameters['attachmentPath'] = FgUtility::generateUrlForHost($this->container, $this->container->get('club')->get('url_identifier'), 'website_cms_inquiriy_attachment_download', $checkClubHasDomain, $parameter);            
        } else {
            $templateParameters['mailContent'] = ($emailSettings['content'][$contactLang]) ? $emailSettings['content'][$contactLang] : $emailSettings['content']['default'];
            $subject = ($emailSettings['subject'][$contactLang]) ? $emailSettings['subject'][$contactLang] : $emailSettings['subject']['default'];
            $senderEmail = $emailSettings['senderemail'];
            $parameter = array('fileName' => '**FILENAME**');            
            $checkClubHasDomain = $this->em->getRepository('CommonUtilityBundle:FgDnClubDomains')->checkClubHasDomain($createdClub);
            $templateParameters['attachmentPath'] = FgUtility::generateUrl($this->container, $this->container->get('club')->get('url_identifier'), 'website_cms_inquiriy_attachment_download', $parameter);
        }
        $templateParameters['contactLang'] = $contactLang;        
        $clubLogoPath = $this->container->get('club')->getClubLogoPath(false);        
        $templateParameters['logoURL'] = ($clubLogoPath == '') ? '' : $templateParameters['baseUrl'] . '/' . $clubLogoPath;
        $templateParameters['signature'] = $this->container->get('club')->get('signature');
        $templateParameters['clubTitle'] = $this->container->get('club')->get('title');
        $template = $this->renderView('WebsiteCMSBundle:FrontendView:formInquiryMailTemplate.html.twig', $templateParameters);
        $this->sendSwiftMesage($template, $emails, $senderEmail, $subject);
    }

    /**
     * Function to send swift mail
     *
     * @param string $emailBody   body content
     * @param string $email       Email addresss to send
     * @param string $senderEmail Sender email
     * @param string $subject     Email subject
     */
    private function sendSwiftMesage($emailBody, $email, $senderEmail, $subject)
    {
        $mailer = $this->get('mailer');
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($senderEmail)
            ->setTo($email)
            ->setBody(stripslashes($emailBody), 'text/html');

        $message->setCharset('utf-8');
        $mailer->send($message);
    }

    /**
     * Method to get array of emails for sending confirmation mail after submitting a form (emails fields which is set to send notiications in the form)
     * 
     * @param int   $elementId form-Id
     * @param array $formDatas form-datas
     * 
     * @return array of emails
     */
    private function getConfirmationEmails($elementId, $formDatas)
    {
        $clubId = $this->container->get('club')->get('id');
        $contactLang = $this->container->get('club')->get('default_lang');
        $emailFormFields = $this->em->getRepository('CommonUtilityBundle:FgCmsPageContentElementFormFields')->getFormFields($clubId, $contactLang, $elementId, true, 'confirmationemail');
        $confirmationEmails = array();
        //mapping email field values
        foreach ($emailFormFields as $key => $fieldDetails) {
            ($formDatas[$key]) ? $confirmationEmails[] = $formDatas[$key] : '';
        }

        return $confirmationEmails;
    }

    /**
     * Method to format form content of form inquiries (add field names to the array)
     * 
     * @param array $formFields form-Fields
     * @param array $formDatas  form-Inquiries-data
     * 
     * @return array of form values
     */
    private function formatInquiries($formFields, $formDatas)
    {
        $dataForNotification = array();
        //foreach ($formDatas as $key => $formData) {    
        foreach ($formFields as $key => $formField) {
            $formData = $formDatas[$key];
            if ((in_array($formField['fieldType'], array('select', 'radio', 'checkbox'))) && ($formData)) {
                $fieldValue = array();
                if (is_array($formData)) {
                    foreach ($formData as $formDataOne) {
                        $fieldValue[] = $formField['fieldoptions'][$formDataOne];
                    }
                } else {
                    $fieldValue[] = $formField['fieldoptions'][$formData];
                }
            } else {
                $fieldValue = $formData;
            }
            $dataForNotification[$formField['fieldId']] = array('fieldName' => $formField['fieldname'],
                'fieldValue' => $fieldValue,
                'fieldType' => $formField['fieldType']);
        }

        return $dataForNotification;
    }

    /**
     * Function to get club logo url
     * 
     * @param object $clubObj club listener object
     * @param string $baseurl Base url string
     * 
     * @return string|null  $clubLogoUrl club logo url
     */
    private function getClubLogoUrl($clubObj, $baseurl)
    {
        $clubLogo = $clubObj->get('logo');
        $rootPath = FgUtility::getRootPath($this->container);
        if ($clubLogo == '' || !file_exists($rootPath . '/' . FgUtility::getUploadFilePath($clubObj->get('id'), 'clublogo', false, $clubLogo))) {
            $clubLogoUrl = '';
        } else {
            $clubLogoUrl = $baseurl . '/' . FgUtility::getUploadFilePath($clubObj->get('id'), 'clublogo', false, $clubLogo);
        }

        return $clubLogoUrl;
    }

    /**
     * Function to save form enquires
     * 
     * @param Request $request The request object
     * 
     * @return JsonResponse $validation The validated status
     */
    public function saveFormInquiryAction(Request $request)
    {
        $inquiry = $request->get('inquiry');
        $menu = $request->get('menu');
        $mainPageId = $request->get('mainPageId');
        $validation = $this->validateForm($inquiry);
        if ($validation['status'] == 'success') {
            foreach ($validation['files'] as $fieldId => $fileName) {
                $fileNameReq = $this->uploadToFilemanager($fileName);
                $validation['inquiry'][$fieldId] = $fileNameReq;
            }
            $inquiry = json_encode($validation['inquiry']);
            $contactId = $this->container->get('contact')->get('id') ? $this->container->get('contact')->get('id') : 0;
            $inquiryId = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElementFormInquiries')->saveFormInquiry($inquiry, $validation['elementId'], $contactId);

            if ($inquiryId) {
                $this->sendNotificationMails($request, $inquiryId, $validation['elementId'], $inquiry, $menu, $mainPageId);
            }
        }

        return new JsonResponse($validation);
    }

    /**
     * Validate form inquiry data
     * 
     * @param array $inquiry
     * 
     * @return array
     */
    private function validateForm($inquiry)
    {
        $return['status'] = 'success';
        $em = $this->getDoctrine()->getManager();
        foreach ($inquiry as $elementId => $fields) {
            $return['elementId'] = $elementId;
            foreach ($fields as $fieldId => $fieldValue) {
                $return['inquiry'][$fieldId] = $fieldValue;
                $fieldObj = $em->getRepository('CommonUtilityBundle:FgCmsPageContentElementFormFields')->find($fieldId);
                switch ($fieldObj->getFieldType()) {
                    case 'fileupload':
                        if ($fieldValue != '') {
                            $return['files'][$fieldId] = $fieldValue;
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
                            $return['inquiry'][$fieldId] = $this->getDateField($fieldValue);
                        }
                        break;
                    case 'time':
                        if ($fieldValue != '') {
                            $date = new \DateTime();
                            $format = (FgSettings::getPhpTimeFormat() == 'h:i A') ? 'h:i a' : 'H:i';
                            $return['inquiry'][$fieldId] = $date->createFromFormat($format, $fieldValue)->format('H:i');
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
        }

        return $return;
    }

    /**
     * Method to get date field in particular format
     * 
     * @param string $fieldValue fieldvalue
     * 
     * @return string of date field in particular format
     */
    private function getDateField($fieldValue)
    {
        if (date_create_from_format('Y-m-d', $fieldValue)) {
            $returnValue = $fieldValue;
        } else {
            $date = new \DateTime();
            $returnValue = $date->createFromFormat(FgSettings::getPhpDateFormat(), $fieldValue)->format('Y-m-d');
        }

        return $returnValue;
    }

    /**
     * Method to upload file and save to file
     * 
     * @param string $fileName Temp filename of uploaded file
     * 
     * @return string saved file name
     */
    private function uploadToFilemanager($fileName)
    {
        $fileNameopt = explode('#-#', $fileName);
        $tempFileName = $fileNameopt[1];
        $clubId = $this->container->get('club')->get('id');
        $fileNameOriginal = str_replace($fileNameopt[0] . '--', '', $tempFileName);
        $formUploadFolder = FgUtility::getUploadFilePath($clubId, 'form_uploads');
        if (!is_dir($formUploadFolder)) {
            mkdir($formUploadFolder, 0700, true);
        }
        $rootPath = FgUtility::getRootPath($this->container);
        $fileNameReq = FgUtility::getFilename("$rootPath/$formUploadFolder", $fileNameOriginal);
        $filepath = FgUtility::getUploadDir() . "/temp/";
        //move file from temporary location to actual path       
        if (file_exists($filepath . $tempFileName)) {
            $attachmentObj = new File($filepath . $tempFileName, false);
            $attachmentObj->move($rootPath . "/" . $formUploadFolder, $fileNameReq);
        }


        return $fileNameReq;
    }
}
