<?php

namespace Internal\TeamBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Clubadmin\Util\Contactlist;
use Common\UtilityBundle\Util\FgPermissions;
use Common\UtilityBundle\Repository\Pdo\ContactPdo;
use Clubadmin\ContactBundle\Util\ContactDetailsSave;
use Symfony\Component\HttpFoundation\Request;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Repository\Pdo\membershipPdo;

/**
 * DefaultController.
 *
 * This controller used for handling login status
 *
 * @author     pitsolutions.ch
 *
 * @version    Fairgate V4
 */
class DefaultController extends Controller
{

    /**
     * Index controller.
     *
     * @param String $name
     *
     * @return template
     */
    public function indexAction($name)
    {
        return $this->render('InternalTeamBundle:Default:index.html.twig', array('name' => $name));
    }

    /**
     * Function is used to track the login status of each team/workgroup.
     *
     * @param Int $role
     *
     * @return template
     */
    public function loginstatusAction($role = '')
    {
        $this->connect = $this->container->get('database_connection');
        $container = $this->container;
        $module = $container->get('club')->get('module');
        $backLink = ($module == 'team') ? $this->generateUrl('team_detail_overview') : $this->generateUrl('workgroup_detail_overview');

        $permissionObj = new FgPermissions($this->container);
        $accessCheckArray = array('from' => 'group', 'id' => $role, 'type' => $module . 's', 'allowedRights' => array('ROLE_GROUP_ADMIN'));
        $permissionObj->checkAreaAccess($accessCheckArray);

        $breadCrumb = array('breadcrumb_data' => array(), 'back' => $backLink);

        $teamName = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgRmCategoryRoleFunction')->getTeamName($this->connect, $role);
        return $this->render('InternalTeamBundle:TeamOverview:teamLoginStatus.html.twig', array('contactId' => $this->contactId, 'roleId' => $role, 'teamName' => $teamName, 'breadCrumb' => $breadCrumb));
    }

    /**
     * Function to show login status details in datatable.
     *
     * @param Request $request Request object
     * @param Int     $role    team/workgroup id
     *
     * @return JsonResponse
     */
    public function teamLoginStatusAction(Request $request, $role = '')
    {
        $container = $this->container;

        $club = $container->get('club');
        $primaryEMail = $container->getParameter('system_field_primaryemail');
        $contactFields = $club->get('allContactFields');
        $emailField = $contactFields[$primaryEMail];
        $isEmailEditable = $emailField['is_changable_teamadmin'];
        $emailVisibility = $emailField['privacy_contact'];
        $canUserSetPrivacy = $emailField['is_set_privacy_itself'];
        $startValue = $request->get('start', '');
        $displayLength = $request->get('length', '');
        $order = $request->get('order', '');
        $columns = $request->get('columns', '');
        $columnName = $columns[$order[0]['column']]['name'];
        $dir = $order[0]['dir'];
        $primaryEmail = $container->getParameter('system_field_primaryemail');
        $orderBy = '';
        if ($columnName == 'contact') {
            $orderBy = " contactname $dir";
        } elseif ($columnName == 'Email') {
            $orderBy = " `$primaryEmail` $dir";
        }

        $contactPdo = new ContactPdo($container);
        $resultSet = $contactPdo->getTeamLoginStatus($canUserSetPrivacy, $primaryEMail, $club->get('id'), $emailVisibility, $isEmailEditable, $role, $orderBy, $startValue, $displayLength);

        $return = array('aaData' => $resultSet['result'], 'iTotalRecords' => count($resultSet['result']), 'iTotalDisplayRecords' => count($resultSet['resultAll']), 'start' => 0, 'isEmailEditable' => $isEmailEditable);

        return new JsonResponse($return);
    }

    /**
     * Function to edit email field from login status list.
     *
     * @param Request $request Request object
     *
     * @return JsonResponse
     */
    public function emailEditAjaxAction(Request $request)
    {
        $emailNewVal = $request->get('value');
        $contactId = $request->get('contactId');
        $preEmailVal = $request->get('prevVal');

        $this->em = $this->getDoctrine()->getManager();
        $this->connect = $this->container->get('database_connection');
        $hasFedMembership = false;
        $fedContactId = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->find($contactId)->getFedContact()->getId();
        $fedObj = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->find($fedContactId)->getFedMembershipCat();
        if (!empty($fedObj)) {
            $hasFedMembership = in_array($fedObj->getId(), $this->getFedMemberships());
        }

        $result = $this->em->getRepository('CommonUtilityBundle:FgCmAttribute')->searchEmailExistAndIsMergable($this->container, $fedContactId, $emailNewVal, $hasFedMembership);

        if (count($result) > 0) { // Email already exists.
            return new JsonResponse(false);
        } else {
            // update email field
            if ($emailNewVal != $preEmailVal) {
                $this->updateEmailField($emailNewVal, $contactId);
            }

            return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->container->get('translator')->trans('CONTACT_EMAIL_EDIT_SUCCESS')));
        }

        return new JsonResponse(true);
    }

    /**
     * Function to get federation membership array.
     *
     * @return array $fedMemberships Federation membership array
     */
    private function getFedMemberships()
    {
        $this->em = $this->getDoctrine()->getManager();
        $club = $this->get('club');
        $this->clubType = $club->get('type');
        $this->clubId = $club->get('id');
        $this->federationId = $club->get('federation_id');
        $this->subFederationId = $club->get('sub_federation_id');

        $objMembershipPdo = new membershipPdo($this->container);
        $membersipFields = $objMembershipPdo->getMemberships($this->clubType, $this->clubId, $this->subFederationId, $this->federationId);
        $fedMemberships = array();
        foreach ($membersipFields as $key => $memberCat) {
            if ($memberCat['clubId'] == $this->federationId) {
                $fedMemberships[] = $key;
            }
        }

        return $fedMemberships;
    }

    /**
     * Function to get club details array.
     *
     * @return array $clubIdArray
     */
    private function getClubArray()
    {
        $club = $this->get('club');
        $clubIdArray = array('clubId' => $club->get('id'),
            'federationId' => $club->get('federation_id'),
            'subFederationId' => $club->get('sub_federation_id'),
            'clubType' => $club->get('type'),
            'correspondanceCategory' => $this->container->getParameter('system_category_address'),
            'invoiceCategory' => $this->container->getParameter('system_category_invoice'),);
        $clubIdArray['address'] = $this->get('translator')->trans('CONTACT_FIELD_ADDRESS');
        $clubIdArray['sysLang'] = $club->get('default_lang');
        $clubIdArray['defSysLang'] = $club->get('default_system_lang');
        $clubIdArray['clubLanguages'] = $club->get('club_languages');

        return $clubIdArray;
    }

    /**
     * Function used to get contact data.
     *
     * @param int    $contactId Connection
     * @param object $conn      Connection
     *
     * @return array $fieldsArray Result array
     */
    private function tempContact($contactId, $conn)
    {
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

        return $fieldsArray;
    }

    /**
     * Send Swift Mail Nl/SM.
     *
     * @param Html   $bodyNew
     * @param String $emails
     * @param String $email
     * @param String $subject
     * @param Array  $attachments
     *
     * @return email
     */
    public function sendSwiftMesage($bodyNew, $emails, $email, $subject, $senderName, $attachments = array())
    {
        $mailer = $this->get('mailer');
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom(array($email => $senderName))
            ->setTo($emails)
            ->setBody(stripslashes($bodyNew), 'text/html');

        foreach ($attachments as $value) {
            $message->attach(\Swift_Attachment::fromPath($value['filePath'])->setFilename($value['fileTitle']));
        }
        $message->setCharset('utf-8');
        $mailer->send($message);
    }

    /**
     * Function used to send reminder mail.
     *
     * @param Request $request Request object
     *
     * @return JsonResponse
     */
    public function sendReminderAction(Request $request)
    {
        $container = $this->container;
        $club = $container->get('club');
        $clubId = $club->get('id');
        $contactIds = $request->get('contactIds');
        $emails = $request->get('emails');
        $names = $request->get('names');
        $salutations = $request->get('salutations');
        
        //FAIR-2489
        $checkClubHasDomain = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgDnClubDomains')->checkClubHasDomain($clubId);
        
        $contactPdo = new ContactPdo($container);
        if (!empty($emails)) {
            $currentLocateSettings = array(0 => array('id' => $container->get('contact')->get('id'), 'default_lang' => $this->container->get('club')->get('default_lang'), 'default_system_lang' => $this->container->get('club')->get('default_system_lang')));
            foreach ($emails as $key => $email) {
                //set locale with respect to particular contact
                $rowContactLocale = $contactPdo->getContactLanguageDetails($contactIds[$key], $clubId, $club->get('clubTable'), $club->get('type'));
                $this->container->get('contact')->setContactLocale($container, $request, $rowContactLocale);
                //To set the club TITLE, SIGNATURE based on default language
                $this->container->get('contact')->setClubParamsOnLangauge($this->container->get('club'));

                $subject = str_replace('%club%', $club->get('title'), $container->get('translator')->trans('REMINDER_MAIL_NEW'));
                $activatePath = FgUtility::generateUrlForHost($this->container, $this->container->get('club')->get('url_identifier'), 'internal_user_activate_account', $checkClubHasDomain);
                $baseurlArr = FgUtility::getMainDomainUrl($this->container, $this->container->get('club')->get('id')); //FAIR-2489
                $baseurl = $baseurlArr['baseUrl']; //Fair-2484   
                $clubLogoPath = $this->container->get('club')->getClubLogoPath(false);
                $bodyNew = $this->renderView('InternalGeneralBundle:MailTemplate:notificationMail.html.twig', array(
                    'name' => $names[$key],
                    'adminName' => $container->get('contact')->get('name'),
                    'clubTitle' => $club->get('title'),
                    'salutation' => $salutations[$key],
                    'logoURL' => ($clubLogoPath == '') ? '' : $baseurl . '/' . $clubLogoPath,
                    'signature' => $club->get('signature'),
                    'conversationUrl' => $activatePath,
                    'inboxUrl' => '',
                ));

                $this->sendSwiftMesage($bodyNew, $email, 'noreply@fairgate.ch', $subject, 'Fairgate');
                // Update last reminder
                $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:SfGuardUser')->updateLastReminder($contactIds[$key], $clubId);
            }

            //reset contact locale with respect to logged in contact
            $this->container->get('contact')->setContactLocale($this->container, $request, $currentLocateSettings);
            //To set the club TITLE, SIGNATURE based on default language
            $this->container->get('contact')->setClubParamsOnLangauge($this->container->get('club'));
        }

        return new JsonResponse(array('status' => 'SUCCESS'));
    }

    /**
     * Function used to update email field.
     *
     * @param String $emailNewVal
     * @param Int    $contactId
     */
    private function updateEmailField($emailNewVal, $contactId)
    {
        // Get club contact fields.
        $primaryEmail = $this->container->getParameter('system_field_primaryemail');
        $clubIdArray = $this->getClubArray();
        $fieldDetails1 = $this->em->getRepository('CommonUtilityBundle:FgCmAttributeset')->getAllClubContactFields($clubIdArray, $this->connect, 0, 0, false, false, array($primaryEmail));
        $fieldDetails = $this->em->getRepository('CommonUtilityBundle:FgCmAttributeset')->fieldItrator($fieldDetails1);
        $clubContFields = $this->get('club')->get('allContactFields');

        // Save email fields of contact.
        $attributeId = $primaryEmail;
        if (isset($clubContFields[$attributeId])) {
            $attrCatId = $fieldDetails['attrCatIds'][$attributeId];
            $formValues = array($attrCatId => array($attributeId => $emailNewVal));
            $contactData = $this->tempContact($contactId, $this->connect);
            $contactDetails = new ContactDetailsSave($this->container, $fieldDetails, $contactData, $contactId);
            $contactDetails->saveContact($formValues, array(), array(), array(), 1);
        }
    }
}
