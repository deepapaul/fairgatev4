<?php

namespace Admin\UtilityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Intl\Intl;
use Admin\UtilityBundle\Util\CreateClub;
use Symfony\Component\HttpFoundation\Request;
use Clubadmin\ContactBundle\Util\ContactDetailsSave;
use Common\UtilityBundle\Repository\Pdo\membershipPdo;
use Common\UtilityBundle\Util\FgContactSyncDataToAdmin;


class RegisterController extends Controller
{

    /**
     * This method is used to render registration form
     * 
     * @param Request $request Request Object
     * 
     * @return Template
     */
    public function indexAction(Request $request)
    {
//        $this->clubSaveAction();
//        die();
        $return = array();
        $return['corresLang'] = $this->getCorresLang();
        $return['categories'] = $this->getCategories();
        $return['countries'] = $this->getCategories('country');
        $return['voucherCode'] = $request->get('qrc');
        
        return $this->render('AdminUtilityBundle:Register:index.html.twig', $return);
    }
    
    /**
     * This method is used to save club details
     * 
     * @return void
     */
    public function clubSaveAction()
    {
        
        $formData = $this->getTestFormData();
       // $formData = $this->getRegistrationFormData();
        
        
        
        $clubSaveData = $this->getClubSaveData($formData);
        /************************************************************
         * Create club
        ************************************************************/
        $createClubObj = new CreateClub($this->container);
        $clubId = $createClubObj->save($clubSaveData);
        //$clubId = 9163;
        
        /************************************************************
         * Create main Contact
        ************************************************************/
        $contactId = $this->createMainContact($clubId, $formData, $clubSaveData);
        
        $this->sendNotificationMail($request, $clubSaveData['clubTitle'], $clubSaveData['corresLang'], $clubSaveData['urlIdentifier'], $clubSaveData['registrationToken'], $clubId, $contactId);
        $this->sendSalesNotificationMail($clubSaveData['urlIdentifier'], $clubSaveData['clubTitle'], $contactId);
        echo "club = {$clubId}";
        echo '<br>';
        echo "contact = {$contactId}";
        die();
        
    }
    
    /**
     * 
     * @param type $clubId
     * @param type $formData
     * @param type $clubSaveData
     * @return type
     */
    private function createMainContact($clubId, $formData, $clubSaveData)
    {
        $contactDet = $this->getContactSaveData($clubId, $formData);
        
        $clubIdArray = $this->getClubArray($clubId, $clubSaveData);
        $fieldLanguages = $this->getLanguageArray();
        $membershipArray = array();//$this->$clubSaveData($clubId, $clubSaveData);
        $fieldType = 'Single person';
        //build contact field array
        $this->em = $this->container->get('doctrine')->getManager();
        $this->conn = $this->container->get('database_connection');
        $fieldDetails1 = $this->em->getRepository('CommonUtilityBundle:FgCmAttributeset')->getAllClubContactFields($clubIdArray, $this->conn, 0, $fieldType);
        $fieldDetails = $this->em->getRepository('CommonUtilityBundle:FgCmAttributeset')->fieldItrator($fieldDetails1);
        //$fieldDetailArray = array('fieldType' => $fieldType, 'clubIdArray' => $clubIdArray, 'fedMemberships' => array(), 'fullLanguageName' => $fieldLanguages, 'selectedMembership' => '', 'contactId' => false, 'deletedFiles' => array());
        $fieldDetailArray = array('fieldType' => $fieldType, 'memberships' => $$membershipArray, 'clubIdArray' => $clubIdArray, 'fedMemberships' => array(), 'fullLanguageName' => $fieldLanguages, 'selectedMembership' => '', 'contactId' => false, 'deletedFiles' => array());
        $fieldDetails = array_merge($this->setTerminologyTerms($fieldDetails), $fieldDetailArray);   
        $clubVar = array( 'id' => $clubId, 'federationId' => $contactDet['federationId'], 'sub_federation_id' => $contactDet['subFederationId'],
                        'clubType' => $contactDet['clubType'], 'default_system_lang' => $contactDet['defaultSystemLang'] );
        $fedFields = $subFedFields = $clubFields = array();
        /***************************************/
        $contact = new ContactDetailsSave($this->container, $fieldDetails, array(), false, 1);
        $contact->isClubRegister = true;
        //To override club variables
        $contact->setClubVariables($clubVar, $fedFields, $subFedFields, $clubFields);
        //Save Contact
        $contactId = $contact->saveContact($contactDet, array());
        
        return $contactId;
    }



    /**
     * 
     * @param type $formData
     */
    private function validateForm($formData)
    {
        
        
    }


    /**
     * This method is used to get categories
     * 
     * @param int $type activity/country
     * 
     * @return array
     */
    public function getCategories($type)
    {
        $catTypeId = $type == 'country' ? 1: 2;
        $result = array();
        $this->adminEntityManager = $this->container->get("fg.admin.connection")->getAdminEntityManager();
        $catDetails = $this->adminEntityManager->getRepository('AdminUtilityBundle:FgSubsubcategorisation')->getCategories($catTypeId);
        foreach($catDetails as $catDet) {
            $result[$catDet['subCatId']]['id'] = $catDet['subCatId'];
            $result[$catDet['subCatId']]['name'] = $catDet['subCategory'];
            $result[$catDet['subCatId']]['values'][] = array('subSubCatId'=>$catDet['subSubCatId'], 'subSubCategory'=>$catDet['subSubCategory']);
        }
       
        return $result;
    }
    
    /**
     * This method is used to get Correspondence language
     * 
     * @return array
     */
    public function getCorresLang()
    {
        $result = array();
        $result['de'] = 'German';
        $result['fr'] = 'French';
        $result['it'] = 'Italian';
        $result['en'] = 'English';
        
        return $result;
    }

    /**
     * This method is used to build club details for registration from request 
     * 
     * @return array 
     */
    public function getRegistrationFormData(Request $request)
    {
        $data = array();
        /*******General*******/
        $data['clubTitle']= $request->get('fg_reg_club_name');
        $data['urlIdentifier']= $request->get('fg_reg_club_identifier');
        $data['occupation']= $request->get('fg_reg_occupation');
        $data['corresLang']= $request->get('fg_reg_corres_lang');
        $data['noOfContacts']= $request->get('fg_reg_no_contacts');
        $data['voucherCode']= $request->get('fg_reg_voucher_code');
        
        /*****Correspondence Address********/
        $data['corrOrgName'] = $request->get('fg_reg_corr_org_name');
        $data['corrCo'] = $request->get('fg_reg_corr_co');
        $data['corrStreet'] = $request->get('fg_reg_corr_street');
        $data['corrPostalCode'] = $request->get('fg_reg_corr_postalcode');
        $data['corrCity'] = $request->get('fg_reg_corr_city');
        $data['corrCountry'] = $request->get('fg_reg_corr_country');
        $data['corrAddressLang'] = $request->get('fg_reg_corr_lang');
        
        /*****Billing Address********/
        $data['billOrgName'] = $request->get('fg_reg_bill_org_name');
        $data['billCo'] = $request->get('fg_reg_bill_co');
        $data['billStreet'] = $request->get('fg_reg_bill_street');
        $data['billPostalCode'] = $request->get('fg_reg_bill_postalcode');
        $data['billCity'] = $request->get('fg_reg_bill_city');
        $data['billCountry'] = $request->get('fg_reg_bill_country');
        $data['billAddressLang'] = $request->get('fg_reg_bill_lang');
        
        /*******Personal******/
        $data['firstName'] = $request->get('fg_reg_first_name');
        $data['surName'] = $request->get('fg_reg_sur_name');
        $data['salutation'] = $request->get('fg_reg_salutation');
        $data['gender'] = $request->get('fg_reg_gender');
        
        /*******Misc******/
        $data['hearAboutFairgate'] = '';
        
        /*******Logi details******/
        $data['email'] = $request->get('fg_reg_email');
        $data['password'] = $request->get('fg_reg_password');
        $data['confirm_password'] = $request->get('fg_reg_confirm_password');
        $data['fg_reg_accept_legalterms'] = $request->get('fg_reg_accept_legalterms');
        
        return $data;
    }
    
    /**
     * This method is used to build data for club save
     * 
     * @param type $data registration data from request
     * 
     * @return array
     */
    private function getClubSaveData($data)
    {
        
        //Validate Form data
        $validate = $this->validateForm($formData);
        if($validate) {
            return true;
        }
        
        $result = array();
        $result['clubTitle'] = $data['clubTitle'];
        $result['urlIdentifier'] = $data['urlIdentifier'];
        $fedDetails = $this->getFedDetailsFromVoucherCode($data['voucherCode']);
        $result['parentClubId'] = $fedDetails['parentClubId'];
        $result['federationId'] = $fedDetails['federationId'];
        $result['clubType'] = $fedDetails['clubType'];
        $result['hasSubfederation'] = 0;

        $result['website'] = 1;
        $result['year'] = '';
        $result['isActive'] = 1;
        $result['responsibleContactId'] = '';
        $result['firstContactTypeId'] = '';
        $result['assignmentCountry'] = 1;
        $result['assignmentState'] = 2;
        $result['assignmentActivity'] = 4;
        $result['assignmentSubactivity'] = 8;
        $result['corresLang'] = $data['corresLang'];

        //For club settings
        $result['currency'] = $this->getCurrency($data['corresLang']);
        $result['fiscalYear'] = '';
        $result['signature'] = '';
        $result['registrationYoken'] = '';
        $result['hearAboutFairgate'] = $data['hearAboutFairgate'];
        $result['numberOfContacts'] = $data['noOfContacts'];

        return $result;
    }
    
    /**
     * This function is used to get currency type from country
     * 
     * @param type $country
     * 
     * @return string
     */
    private function getCurrency($country) 
    {
        if (in_array($country, array(1, 6))) {
            $currency = 'CHF';
        } elseif (in_array($country, array(2,3))) {
            $currency = 'Euro';
        } else {
            $currency = 'USD';
        }

        return $currency;
    }


    /**
     * This method is used to generate federation
     * 
     * @param type $voucherCode
     * 
     * @return type
     */
    private function getFedDetailsFromVoucherCode($voucherCode) 
    {
        
        //FgVoucherCode
        
//        $this->adminEntityManager = $this->container->get("fg.admin.connection")->getAdminEntityManager();
//        $aa = $this->adminEntityManager->getRepository('FgAdminCommonBundle:FgVoucherCode')->fingBy(array('voucher_code'=>$voucherCode));
//        
//        echo $aa;
//        die();
        
        return array(
            'clubType' => 'federation_club',
            'parentClubId' => 608,
            'federationId' => 608,
            'subFederationId' => 0,
            'hasSubfederation' => false
        );
        
    }
    
    /**
     * This method is used to build data to save contact details
     * 
     * @param type $voucherCode
     * 
     * @return void
     */
    private function getContactSaveData($clubId, $clubData)
    {
        $container = $this->container->getParameterBag();
        $personalCat = $container->get('system_category_personal');
        $communicationCat = $container->get('system_category_communication');
        $categoryCorrespondance = $container->get('system_category_address');
        $corress_lang = $container->get('system_field_corress_lang');
//        $fedFieldCat = 6;
//        $telgField = 72585;
//        $employerCat = $container->get('external_application_fedfield_category');
//        $employerField = $container->get('external_application_system_fields')['employer'];
//        $personalNumberField = $container->get('external_application_system_fields')['personalNumber'];
        
        $salutation = $container->get('system_field_salutaion');
        $array_fields["$personalCat"] = array('firstname' => $container->get('system_field_firstname'), 'lastname' => $container->get('system_field_lastname'), 'gender' => $container->get('system_field_gender'), 'dob' => $container->get('system_field_dob'));
        $array_fields["$communicationCat"] = array('email' => $container->get('system_field_primaryemail'), 'tel_m' => $container->get('system_field_mobile1'));
        $array_fields["$categoryCorrespondance"] = array('street' => $container->get('system_field_corres_strasse'), 'zipcode' => $container->get('system_field_corres_plz'), 'location' => $container->get('system_field_corres_ort'));
//        $array_fields["$fedFieldCat"]['telg'] = $telgField;
//        $array_fields["$employerCat"]['employer'] = $employerField;
//        $array_fields["$employerCat"]['personal_number'] = $personalNumberField;

        $form_array = array();
        $form_array['system']['contactType'] = 'Single person';
        $form_array['system']['attribute'] = array('0' => 'Intranet access');
        $form_array["$personalCat"]["$salutation"] = 'Formal';
        $form_array["$communicationCat"]["$corress_lang"] = $clubData['corresLang'];
        $form_array["$personalCat"][$array_fields["$communicationCat"]["email"]] = $clubData["email"];
        $form_array["$personalCat"][$array_fields["$communicationCat"]["firstname"]] = $clubData["firstname"];
        $form_array["$personalCat"][$array_fields["$communicationCat"]["lastname"]] = $clubData["surName"];
        $form_array["$personalCat"][$array_fields["$communicationCat"]["gender"]] = $clubData["gender"];
        $form_array["$personalCat"][$salutation] = $clubData["salutation"];
        
        return $form_array;
    }

   /**
     * Method to send confirmation mail after registering club
     * 
     * @param object $request            request object
     * @param string $clubTitle          clubTitle
     * @param string $correspondenceLang correspondence Language 
     * @param string $urlIdentifier      urlIdentifier
     * @param string $registrationToken  club registrationToken
     * @param int    $clubId             clubId
     * @param int    $contactId          contactId
     */
    public function sendNotificationMail($request, $clubTitle, $correspondenceLang, $urlIdentifier, $registrationToken, $clubId, $contactId)
    {        
        $templateParameters['clubTitle'] = $clubTitle;
        //Get salutation of user for sending send mail
        $em = $this->getDoctrine()->getManager();
        $userObj = $em->getRepository('CommonUtilityBundle:SfGuardUser')->findOneBy(array('contact' => $contactId));  
        $userEmail = $userObj->getEmail();
        $templateParameters['salutation'] = $em->getRepository('CommonUtilityBundle:SfGuardUser')->getSalutation($userObj, $this->container->get('club'));        
        $templateParameters['clubUrlPath'] = $this->container->get('router')->generate('admin_confirmation', array('token' => $registrationToken, 'urlIdentifier' => $urlIdentifier, 'clubId' => $clubId), 0);
        //set locale in respective correspondance lang selected in step 1
        $this->container->get('contact')->setContactLocale($this->container, $request, array(array('default_lang' => $correspondenceLang, 'default_system_lang' => $correspondenceLang)));
        $template = $this->renderView('AdminUtilityBundle:Register:registrationMailTemplate.html.twig', $templateParameters);
        $subject = $this->container->get('translator')->trans('CONFIRM_SUBJECT');
        $senderEmail = $this->container->getParameter('noreplyEmail');
        $this->sendSwiftMesage($template, $userEmail, $senderEmail, $subject);
    }
    
    /**
     * Method to send confirmation mail after registering club
     * 
     * @param string $urlIdentifier urlIdentifier
     * @param string $clubTitle     clubTitle
     * @param int    $contactId     contactId
     */
    public function sendSalesNotificationMail($urlIdentifier, $clubTitle, $contactId)
    {
        $adminEntityManager = $this->container->get('fg.admin.connection')->getAdminManager();
        $contactObj = $adminEntityManager->getRepository('AdminUtilityBundle:FgCmContact')->find($contactId);
        $userName = $contactObj->getName();
        $userEmail = $contactObj->getEmail();
        
//        $em = $this->getDoctrine()->getManager();
//        $userObj = $em->getRepository('CommonUtilityBundle:SfGuardUser')->findOneBy(array('contact' => $contactId));  
//        $userEmail = $userObj->getEmail();
//        $userName = 'XXXXXXXXXXXXXXX';
        
        $dateTime = date('d.m.Y H:i');
        $templateParameters['notificationMessage'] = $this->container->get('translator')->trans('SALES_NOTIFICATION_MESSAGE', array('%userName%' => $userName, '%userEmail%'=> $userEmail, '%clubName%'=> $clubTitle, '%dateTime%'=> $dateTime));         
        $templateParameters['organisationDetailPagePath'] =   $this->container->getParameter('organisationDetailPagePath');  
        $templateParameters['backendPath'] = $this->container->get('router')->generate('fos_user_security_login', array('url_identifier' => $urlIdentifier), 0);
        $template = $this->renderView('AdminUtilityBundle:Register:salesNotificationMailTemplate.html.twig', $templateParameters);        
        $subject = $this->container->get('translator')->trans('REGISTRATION_REPORT');
        $senderEmail = $this->container->getParameter('noreplyEmail');
        $sailsNotificationEmail = $this->container->getParameter('sailsNotificationEmail');
        $this->sendSwiftMesage($template, $sailsNotificationEmail, $senderEmail, $subject);
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
     * Method to confirm email after club registration
     * 
     * @param string $urlIdentifier club urlIdentifier
     * @param int    $clubId        clubId
     * @param string $token         club token
     * 
     * @return Object View Template Render Object
     */
    public function confirmMailAction($urlIdentifier, $clubId, $token)
    {      
        $em = $this->getDoctrine()->getManager();
        $adminEntityManager = $this->container->get('fg.admin.connection')->getAdminManager();
        $clubObj = $adminEntityManager->getRepository('AdminUtilityBundle:FgClub')->find($clubId);
        $clubUrlIdentifier = $clubObj->getUrlIdentifier();
        $clubToken = $clubObj->getRegistrationToken();
        $registrationDate = $clubObj->getRegistrationDate();
        $expiringDate = $registrationDate->modify('+7 day');
        $todaysDate = new \DateTime("now");
        $mainContactEmail = $clubObj->getFairgateSolutionContact()->getEmail();
        if ($clubUrlIdentifier == $urlIdentifier && $clubToken == $token && ($todaysDate <= $expiringDate)) {
            
            return $this->showConfirmationSuccess($mainContactEmail, $clubId);
        } else {
            
            return $this->showConfirmationError($mainContactEmail);
        }
    }
    
    /**
     * Method to show email verification success
     * 
     * @param string $mainContactEmail mainContacts's email
     * @param int    $clubId           clubId
     * 
     * @return Object View Template Render Object
     */
    private function showConfirmationSuccess($mainContactEmail, $clubId)
    {
        //get club confirmed
        $adminEntityManager = $this->container->get('fg.admin.connection')->getAdminManager();
        $adminEntityManager->getRepository('AdminUtilityBundle:FgClub')->getClubConfirmed($clubId);
        $clubObj = $adminEntityManager->getRepository('AdminUtilityBundle:FgClub')->find($clubId);
        
        //save default dats on club confirmation
        $createClubObj = new CreateClub($this->container);
        $createClubObj->saveDataOnClubConfirmation($clubId);
        
        $clubsMainContact = ($clubObj->getFairgateSolutionContact()) ? $clubObj->getFairgateSolutionContact()->getId() : null;
        //provide 'clubadmin' user right for club's main contact
        $this->makeMainContactClubAdmin($clubId, $clubsMainContact);
        $backendLoginPath = $this->container->get('router')->generate('fos_user_security_login'); 

        return $this->render('AdminUtilityBundle:Register:confirmationSuccess.html.twig', array('userEmail' => $mainContactEmail, 'backendLoginPath' => $backendLoginPath));
    }
        
    /**
     * Method to show email verification success
     * 
     * param string $mainContactEmail mainContacts's email 
     * 
     * @return Object View Template Render Object
     */
    private function showConfirmationError($mainContactEmail)
    {

        return $this->render('AdminUtilityBundle:Register:confirmationError.html.twig', array('userEmail' => $mainContactEmail));
    }

    /**
     * get club details array.
     *
     * @return type array
     */
    private function getClubArray($clubId, $data)
    {
        //echo $data['clubId'];die();
        $container = $this->container->getParameterBag();
        $clubIdArray = array('clubId' => $clubId,
            'federationId' => $data['federationId'],
            'subFederationId' => $data['subFederationId'],
            'clubType' => $data['clubType'],
            'correspondanceCategory' => $container->get('system_category_address'),
            'invoiceCategory' => $container->get('system_category_invoice'),);
        $clubIdArray['address'] = $this->get('translator')->trans('CONTACT_FIELD_ADDRESS');
        $clubIdArray['sysLang'] = $data['corresLang'];//$this->clubDefaultLang;
        $clubIdArray['defSysLang'] = $data['corresLang'];//$this->clubDefaultSystemLang;
        $clubIdArray['clubLanguages'] = $data['corresLang'];//$this->clubLanguages;

        return $clubIdArray;
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
    }
    
    /**
     * set terminology terms to contact detail array.
     *
     * @param array $fieldDetails
     *
     * @return array
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
     * Provide 'clubadmin' user right for club's main contact
     * 
     * @param int $clubId           clubId
     * @param int $clubsMainContact club's MainContact Id
     */
    private function makeMainContactClubAdmin($clubId, $clubsMainContact) {
        $em = $this->getDoctrine()->getManager();
        $userRightsArray = array('new' => array('group' => array(2 => array('contact' => array( $clubsMainContact => 1)))));
        $conn = $this->container->get('database_connection');
        // Calling common save function to save the user rights
        $em->getRepository('CommonUtilityBundle:SfGuardGroup')->saveUserRights($conn, $userRightsArray, $clubId, 1, $this->container);
        
        /** Sync the contact name data to the Admin DB **/
        $userRightContacts = array(0 => $clubsMainContact);
        $contactSyncObject = new FgContactSyncDataToAdmin($this->container);
        $contactSyncObject->updateUserRights($userRightContacts)->updateLastUpdated($clubId)->executeQuery();
    }
 
    
    /**************************************************
    /**************************************************
     * 
     * test method
     */
    private function getTestFormData($request)
    {
        
       $data = array();
        /*******General*******/
        $data['clubTitle']= 'FC Test Club 1';
        $data['urlIdentifier']= 'fc_test_club1';
        $data['occupation']= '';
        $data['corresLang']= 'de';
        $data['noOfContacts']= 25;
        $data['voucherCode']= 'xxxx-xxxx-xxx';
        
        /*****Correspondence Address********/
        $data['corrOrgName'] = 'FC Test Club';
        $data['corrCo'] = 'dummy CO';
        $data['corrStreet'] = 'dummy Street';
        $data['corrPostalCode'] = 'dummy po';
        $data['corrCity'] = 'dummy city';
        $data['corrCountry'] = 'dummy Country';
        $data['corrLang'] = 'dummy Lang';
        
        /*****Billing Address********/
        $data['billOrgName'] = 'dummy org name';
        $data['billCo'] = 'dummy bill CO';
        $data['billStreet'] = 'dummy bill street';
        $data['billPostalCode'] = 'dummy bill po';
        $data['billCity'] = 'dummy bill city';
        $data['billCountry'] = 'dummy bill Country';
        $data['billLang'] = 'dummy bill lang';
        
        /*******Personal******/
        $data['firstName'] = 'dummy First name';
        $data['surName'] = 'dummy sur name';
        $data['salutation'] = 'dummy salutation';
        $data['gender'] = 'dummy gender';
        
        /*******Misc******/
        $data['hearAboutFairgate'] = '1111';
        
        /*******Logi details******/
        $data['email'] = 'dummyTest@yopmail.com';
        $data['password'] = 'test';
        $data['confirm_password'] = 'test';
        $data['fg_reg_accept_legalterms'] = '1';
        
        
        return $data;
    }

}
