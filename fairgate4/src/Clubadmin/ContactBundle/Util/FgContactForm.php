<?php

/**
 * FgContactForm
 */
namespace Clubadmin\ContactBundle\Util;

use Common\UtilityBundle\Repository\Pdo\CmsPdo;
use Common\UtilityBundle\Util\FgUtility;

/**
 * This class is used for managing the contact application forms and its related functionalities.
 *
 * @author  pitsolutions.ch <pit@solutions.com>
 *
 * @version Release: <v4>
 */
class FgContactForm
{

    /**
     * $container
     *
     * @var object {container object}
     */
    private $container;

    /**
     * $club
     *
     * @var object {club object}
     */
    private $club;

    /**
     * $clubId
     *
     * @var int ClubId
     */
    private $clubId;

    /**
     * $contact
     *
     * @var object {contact object}
     */
    private $contact;

    /**
     * $contactId
     *
     * @var int ContactId
     */
    private $contactId;

    /**
     * $clubDefaultLang
     *
     * @var string Club default language
     */
    private $clubDefaultLang;

    /**
     * $clubLanguages
     *
     * @var array Club languages
     */
    private $clubLanguages;

    /**
     * $em
     *
     * @var object {entitymanager object}
     */
    private $em;

    /**
     * $contactId
     *
     * @var int ContactId
     */
    private $formId;

    /**
     * log error - sending mail
     * @var type 
     */
    private $log;

    /**
     * The constructor function
     *
     * @param object $container container:\Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->club = $this->container->get('club');
        $this->clubId = $this->club->get('id');

        $this->contact = $this->container->get('contact');
        $this->contactId = $this->contact->get('id');

        $this->clubDefaultLang = $this->club->get('club_default_lang');
        $this->clubLanguages = $this->club->get('club_languages');

        $this->em = $this->container->get('doctrine')->getManager();
    }

    /**
     * This function is used to save the form field details in contact application form wizard step 1
     *
     * @param int   $fieldId   Field Id
     * @param array $fieldData Array of field details to save
     */
    private function saveFormFieldData($fieldId, $fieldData)
    {
        $fieldArray = $fieldData;
        $fieldArray['formId'] = $this->formId;
        $fieldArray['formFieldId'] = $fieldId;
        $fieldArray['default_label'] = $fieldData['label'][$this->clubDefaultLang];
        $fieldArray['default_placeholder'] = $fieldData['placeholder'][$this->clubDefaultLang];
        $fieldArray['default_tooltip'] = $fieldData['tooltip'][$this->clubDefaultLang];
        $fieldArray['default_predefined'] = $fieldData['predefined'][$this->clubDefaultLang];
        $fieldArray['contactId'] = $this->contactId;
        $newFieldId = $this->em->getRepository('CommonUtilityBundle:FgCmsPageContentElementFormFields')->saveFormField($fieldArray, $this->formId, $this->clubDefaultLang, $this->clubLanguages);
    }

    /**
     * This function is used to save the contact field details in contact application form wizard step 1
     *
     * @param int   $fieldId   Field Id
     * @param array $fieldData Array of field details to save
     */
    private function saveContactFieldData($fieldId, $fieldData)
    {
        $fieldArray = $fieldData;
        $fieldArray['formId'] = $this->formId;
        $fieldArray['formFieldId'] = $fieldId;
        $fieldArray['default_placeholder'] = (isset($fieldData['placeholder'][$this->clubDefaultLang])) ? $fieldData['placeholder'][$this->clubDefaultLang] : null;
        $fieldArray['default_tooltip'] = (isset($fieldData['tooltip'][$this->clubDefaultLang])) ? $fieldData['tooltip'][$this->clubDefaultLang] : null;
        $fieldArray['default_predefined'] = (isset($fieldData['predefined'][$this->clubDefaultLang])) ? $fieldData['predefined'][$this->clubDefaultLang] : ((isset($fieldData['predefined']) && isset($fieldData['mandatoryfield'])) ? $fieldData['predefined'] : null);
        $fieldArray['contactId'] = $this->contactId;
        $newFieldId = $this->em->getRepository('CommonUtilityBundle:FgCmsPageContentElementFormFields')->saveFormFieldObject($fieldArray, $this->formId, 'contact');
        //insert/update the corresponding I18n values
        $this->em->getRepository('CommonUtilityBundle:FgCmsPageContentElementFormFieldsI18n')->saveElementI18n($newFieldId, $fieldArray, $this->clubLanguages, $this->em->getConnection());
    }

    /**
     * This function is used to save the membership field details in contact application form wizard step 1
     *
     * @param int   $fieldId   Field Id
     * @param array $fieldData Array of field details to save
     */
    private function saveMembershipFieldData($fieldId, $fieldData)
    {
        $fieldArray = $fieldData;
        $fieldArray['formId'] = $this->formId;
        $fieldArray['formFieldId'] = $fieldId;
        $fieldArray['default_tooltip'] = $fieldData['tooltip'][$this->clubDefaultLang];
        $fieldArray['contactId'] = $this->contactId;
        //delete already added memberships if any
        $this->em->getRepository('CommonUtilityBundle:FgCmsPageContentElementMembershipSelections')->deleteAllMembershipsOfAField($fieldId);
        $formFieldId = $this->em->getRepository('CommonUtilityBundle:FgCmsPageContentElementFormFields')->saveFormFieldObject($fieldArray, $this->formId, 'club-membership');
        if (!in_array('all', $fieldArray['clubMembershipSelection'])) {
            $this->em->getRepository('CommonUtilityBundle:FgCmsPageContentElementMembershipSelections')->insertMembershipSelectionsOfAField($fieldArray['clubMembershipSelection'], $formFieldId);
        }
    }

    /**
     * This function is used to save step 1 of contact application form set up wizard.
     *
     * @param int   $formId FormId
     * @param array $data   Array of step 1 field settings and details
     */
    private function saveContactFormFieldsAndElementOptions($formId, $data)
    {
        $this->formId = $formId;
        $formObj = $this->em->getRepository('CommonUtilityBundle:FgCmsForms')->find($formId);
        if (!empty($formObj)) {
            foreach ($data['formFieldData'] as $formId => $formData) {
                foreach ($formData as $fieldId => $fieldData) {
                    switch ($fieldData['form_field_type']) {
                        case 'contact':
                            $this->saveContactFieldData($fieldId, $fieldData);
                            break;

                        case 'form':
                            $this->saveFormFieldData($fieldId, $fieldData);
                            break;

                        case 'club-membership':
                            $this->saveMembershipFieldData($fieldId, $fieldData);
                            break;

                        default:
                            break;
                    }
                }
            }
            if (isset($data['captchaEnabled'])) {
                $this->em->getRepository('CommonUtilityBundle:FgCmsPageContentElementFormFields')->saveFormCaptcha($formId, $data['captchaEnabled']);
            }
        }
    }

    /**
     * This function is used to save the contact application form email settings in step 2
     *
     * @param int   $formId FormId
     * @param array $data   Array of form details
     */
    private function saveContactFormEmailSettings($formId, $data)
    {
        $formData = $data['formFieldData'][$formId];
        $formData['contactId'] = $this->contactId;
        $formData['clubDefaultLang'] = $this->clubDefaultLang;
        $formData['clubLanguages'] = $this->clubLanguages;
        $this->em->getRepository('CommonUtilityBundle:FgCmsForms')->saveContactFormStage2($formId, $formData);
        $this->em->getRepository('CommonUtilityBundle:FgCmsFormsI18n')->saveContactFormStage2I18n($formId, $formData, $this->clubLanguages);
    }

    /**
     * This function is used to save the contact application form completion settings in step 2
     *
     * @param int   $formId FormId
     * @param array $data   Array of form details
     */
    private function saveContactFormCompletionSettings($formId, $data)
    {
        $formData = $data['formFieldData'][$formId];
        $defaultSuccessMsg = $formData['successmessage'][$this->clubDefaultLang];
        $this->em->getRepository('CommonUtilityBundle:FgCmsForms')->saveContactFormStage3($formId, $defaultSuccessMsg, $this->contactId);
        $this->em->getRepository('CommonUtilityBundle:FgCmsFormsI18n')->saveContactFormStage3I18n($formId, $formData, $this->clubLanguages);
    }

    /**
     * This function is used to save the contact application form form fileds and options setup in step 1
     *
     * @param array $data   Array of form details
     */
    public function saveContactForm($data)
    {
        switch ($data['formStage']) {
            case 'stage1':
                $this->saveContactFormFieldsAndElementOptions($data['formId'], $data);
                break;
            case 'stage2':
                $this->saveContactFormEmailSettings($data['formId'], $data);
                break;
            case 'stage3';
                $this->saveContactFormCompletionSettings($data['formId'], $data);
                break;
            default;
                break;
        }
        $this->updateContactForm($data['formId'], $data['formStage'], $data['formName']);
    }

    /**
     * This function is used to update the stage and updated_data of contact form
     *
     * @param int    $formId    The form id
     * @param string $formStage The form stage
     */
    private function updateContactForm($formId, $formStage, $formName = '')
    {
        $formObj = $this->em->getRepository('CommonUtilityBundle:FgCmsForms')->find($formId);
        if (!empty($formObj)) {
            if ($formName != '') {
                $formObj->setTitle($formName);
            }
            $formStage = $this->getFormStage($formObj->getFormStage(), $formStage);
            $formObj->setFormStage($formStage);
            $formObj->setUpdatedAt(new \DateTime("now"));
            $contactObj = $this->em->getReference('CommonUtilityBundle:FgCmContact', $this->contactId);
            $formObj->setUpdatedBy($contactObj);
            $this->em->persist($formObj);
            $this->em->flush();
        }
    }

    /**
     * This function is used to find the current stage to be updated in create/edit appln form wizard 
     * 
     * @param string $orgFormStage Original form stage saved
     * @param string $formStage    Current form stage to be saved
     * 
     * @return string $formStage Form stage to be updated
     */
    private function getFormStage($orgFormStage, $formStage)
    {
        switch ($orgFormStage) {
            case 'stage3':
                $formStage = $orgFormStage;
                break;
            case 'stage2':
                $formStage = ($formStage != 'stage1') ? $formStage : $orgFormStage;
                break;
            case 'stage1':
            default:
                break;
        }

        return $formStage;
    }

    /**
     * This function is used to duplicate an existing contact application form.
     *
     * @param int $formId FormId
     */
    public function duplicateExistingContactForm($formId)
    {
        $status = 'success';
        $this->em->getConnection()->beginTransaction();
        try {
            //duplicate form data
            $newFormId = $this->em->getRepository('CommonUtilityBundle:FgCmsForms')->duplicateContactFormData($formId, $this->contactId, array('copyOfTrans' => $this->container->get('translator')->trans('CONTACT_APPLICATION_FORM_COPY_OF')));

            $cmsObj = new CmsPdo($this->container);

            //duplicate form i18n data
            $cmsObj->duplicateContactFormI18nData($formId, $newFormId);
            $formStage = $this->em->getRepository('CommonUtilityBundle:FgCmsForms')->find($newFormId)->getFormStage($newFormId);
            if ($formStage != 'stage0') {
                //duplicate all fields of a form
                $insertedDetails = $cmsObj->duplicateContactFormFieldsData($formId, $newFormId, $this->contactId);

                //get a mapping array of old and new filed ids
                $mappedFieldIdsArr = $this->mapOldAndNewFieldIds($insertedDetails);

                //create a temporary table for ease of joining
                $cmsObj->createTemporaryMappingTableOfFormFields($mappedFieldIdsArr);

                //duplicate contact form fields i18n data
                $cmsObj->duplicateContactFormFieldsI18nData($formId);

                //duplicate contact form fields options data
                $cmsObj->duplicateContactFormFieldOptionsData($formId);

                //duplicate contact form fields options i18n data
                $cmsObj->duplicateContactFormFieldOptionsI18nData($formId);

                //duplicate contact form membership selections if any
                $cmsObj->duplicateContactFormMembershipSelectionData($formId);

                //drop temporary mapping table
                $cmsObj->dropTemporaryMappingTableOfFormFields();
            }
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollback();
            $status = 'error';
//            echo $e->getMessage();
//            throw $e;
        }

        return $status;
    }

    /**
     * This function is used to map old and new field ids
     *
     * @param array $insertedDetails Array of old field ids and last_inserted_id
     *
     * @return array $mappedArr Array of new and old field ids
     */
    private function mapOldAndNewFieldIds($insertedDetails)
    {
        $mappedArr = array();
        $newId = $insertedDetails['lastInsertedId'];
        //In multiple inserts we can get all newly inserted ids by last_insert_id + 1
        foreach ($insertedDetails['oldFieldIds'] as $val) {
            $mappedArr[] = array('oldFieldId' => $val['id'], 'newFieldId' => $newId);
            $newId += 1;
        }

        return $mappedArr;
    }

    /**
     * compile data for mail
     * 
     * @param int       $formId
     * @param string    $action
     * @param string    $contactLang
     * 
     * @return array
     */
    public function compileMailDatas($formId, $action, $contactLang)
    {
        $mailDetails = array();
        $mailData = $this->em->getRepository('CommonUtilityBundle:FgCmsForms')->getContactApplicationFormMailSettings($formId, $this->container, $this->club);
        switch ($action) {
            case 'acceptance':
                if ($mailData['isAcceptanceEmailActive'] == 1) {
                    $senderEmail = $mailData['acceptanceSenderEmail'];
                    $subject = ($mailData['acceptanceSubject'][$contactLang]) ? $mailData['acceptanceSubject'][$contactLang] : $mailData['acceptanceSubject']['default'];
                    $body = ($mailData['acceptanceContent'][$contactLang]) ? $mailData['acceptanceContent'][$contactLang] : $mailData['acceptanceContent']['default'];
                    $mailDetails = array('introText' => $body, 'senderEmail' => $senderEmail, 'subject' => $subject, 'formValues' => array(),'sendMail'=>1);
                }else{
                    $mailDetails = array('introText' => '', 'senderEmail' => '', 'subject' => '', 'formValues' => array(),'sendMail'=>0);
                }
                break;
            case 'dismissal':
                if ($mailData['isDismissalEmailActive'] == 1) {
                    $senderEmail = $mailData['dismissalSenderEmail'];
                    $subject = ($mailData['dismissalSubject'][$contactLang]) ? $mailData['dismissalSubject'][$contactLang] : $mailData['dismissalSubject']['default'];
                    $body = ($mailData['dismissalContent'][$contactLang]) ? $mailData['dismissalContent'][$contactLang] : $mailData['dismissalContent']['default'];
                    $mailDetails = array('introText' => $body, 'senderEmail' => $senderEmail, 'subject' => $subject, 'formValues' => array(),'sendMail'=>1);
                }else{
                    $mailDetails = array('introText' => '', 'senderEmail' => '', 'subject' => '', 'formValues' => array(),'sendMail'=>0);
                }
                break;
            case 'notification':
                if ($mailData['recipients'] != '' && $mailData['recipients'] != null) {
                    $mailDetails = array('recipients' => $mailData['recipients'], 'senderEmail' => 'noreply@fairgate.ch');
                }
                break;
            case 'confirmation':
                $senderEmail = $mailData['confirmationSenderEmail'];
                $subject = ($mailData['confirmationSubject'][$contactLang]) ? $mailData['confirmationSubject'][$contactLang] : $mailData['confirmationSubject']['default'];
                $body = ($mailData['confirmationContent'][$contactLang]) ? $mailData['confirmationContent'][$contactLang] : $mailData['confirmationContent']['default'];
                $mailDetails = array('introText' => $body, 'senderEmail' => $senderEmail, 'subject' => $subject);
                break;
        }

        return $mailDetails;
    }

    /**
     * Method to set locale with respect to a particular contact.
     *
     * @param array  $rowContactLocale array of (id, default_lang, default_system_lang)
     * 
     * @return array $contactLocale contact locale details
     */
    public function getContactLocale($rowContactLocale)
    {
        $contactLocale = $rowContactLocale[0];
        unset($contactLocale['id']);
        $club = $this->container->get('club');
        $clubLanguages = $club->get('club_languages');
        $clubLanguagesDet = $club->get('club_languages_det');
        $contactCorrespondanceLang = $rowContactLocale[0]['default_lang'];
        $contactSytemLang = $rowContactLocale[0]['default_system_lang'];
        // If the correspondance language of the conntact is there in the club languages list, set that language as correspondance language
        if (in_array($contactCorrespondanceLang, $clubLanguages)) {
            // If sytem language is set as default , set that to system lanugage of the correspondance language
            if ($contactSytemLang == 'default' || $contactSytemLang == '') {
                $contactLocale['default_system_lang'] = $clubLanguagesDet[$contactCorrespondanceLang]['systemLang'];
            }
        } else {
            $contactLocale['default_lang'] = $club->get('default_lang');
            if ($contactSytemLang == 'default' || $contactSytemLang == '') {
                $contactLocale['default_system_lang'] = $club->get('default_system_lang');
            }
        }
        return $contactLocale;
    }

    public function getNotificationMailParams($formId, $action, $contactLang = '')
    {
        $emailSettings = $this->compileMailDatas($formId, $action, $contactLang);
        $defaultMailSettings = $this->getMailTemplateParameters();
        $finalMailSettings = array_merge($defaultMailSettings, $emailSettings);

        return $finalMailSettings;
    }

    /**
     * Function to send swift mail
     *
     * @param string $emailBody   body content
     * @param string $email       Email addresss to send
     * @param string $senderEmail Sender email
     * @param string $subject     Email subject
     */
    public function sendSwiftMesage($emailBody, $email, $senderEmail, $subject)
    {
        $mailer = $this->container->get('mailer');
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($senderEmail)
            ->setTo($email)
            ->setBody(stripslashes($emailBody), 'text/html');

        $message->setCharset('utf-8');
        $mailer->send($message);
    }

    /**
     * This function is used to get the default parameters for email
     * 
     * @param string $action Mail action
     * 
     * @return array $mailTemplateParameters Mail template parameters
     */
    public function getMailTemplateParameters($action)
    {
        $clubHasDomain = $this->em->getRepository('CommonUtilityBundle:FgDnClubDomains')->checkClubHasDomain($this->club->get('id'));
        $rootPath = ($action != 'confirmation') ? FgUtility::getBaseUrl($this->container, $this->clubId) : FgUtility::getBaseUrl($this->container);
        $formUploadFolder = FgUtility::getUploadFilePath($this->club->get('id'), 'form_uploads');
        $mailTemplateParameters = array();
        $mailTemplateParameters['uploadPath'] = $rootPath . "/" . $formUploadFolder . "/";
        $mailTemplateParameters['logoURL'] = $this->getClubLogoUrl($rootPath);
        $mailTemplateParameters['baseUrl'] = $rootPath;
        $mailTemplateParameters['pageLink'] = FgUtility::generateUrlForHost($this->container, $this->club->get('url_identifier'), 'confirmations_creations_appform', $clubHasDomain);        
        $mailTemplateParameters['clubTitle'] = $this->club->get('title');
        $mailTemplateParameters['signature'] = $this->club->get('signature');

        return $mailTemplateParameters;
    }

    /**
     * Function to get club logo url
     * 
     * @param object $clubObj club listener object
     * @param string $rootPath Base url string
     * 
     * @return string|null  $clubLogoUrl club logo url
     */
    private function getClubLogoUrl($rootPath)
    {
        $clubLogo = $this->club->get('logo');
        if ($clubLogo == '' || !(file_exists(FgUtility::getUploadFilePath($this->club->get('id'), 'clublogo', false, $clubLogo)))) {
            $clubLogoUrl = '';
        } else {
            $clubLogoUrl = $rootPath . '/' . FgUtility::getUploadFilePath($this->club->get('id'), 'clublogo', false, $clubLogo);
        }

        return $clubLogoUrl;
    }
}
