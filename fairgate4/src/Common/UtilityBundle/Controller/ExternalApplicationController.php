<?php

/**
 * ExternalApplicationController
 *
 * This controller is used to handle external application functionalities
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
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Collection;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Repository\Pdo\membershipPdo;

class ExternalApplicationController extends Controller
{

    /**
     * Function to show  the external application form
     *
     * @return object View Template Render Object
     */
    public function externalApplicationFormAction()
    {
        $club = $this->container->get('club');
        $clubId = $club->get('id');
        $clubTitle = $club->get('title');
        $extApplAvailableClubs = $this->container->getParameter('external_application_clubids');

        if (!in_array($clubId, $extApplAvailableClubs)) {
            $logoutUrl = $this->generateUrl('fairgate_user_security_logout');
            header('Location:' . $logoutUrl);
            exit;
        }

        $getAllClubs = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgClub')
            ->getAllClubDetailsForExternalApplication($clubId, $club->get('default_lang'));

        $contactFields = $this->container->get('club')->get('contactFields');
        $contactFieldsExternal = $this->container->getParameter('external_application_system_fields');
        foreach ($contactFieldsExternal as $key => $fieldId) {
            foreach ($contactFields as $fieldData) {
                if ($fieldData['id'] == $fieldId) {
                    $contactFieldsExternal[$key] = $fieldData['title'];
                }
            }
        }

        $clubLogoUrl = $this->getClubLogoForExternalApplication($this->container->get('club'));
        $return = array('clubNames' => $getAllClubs, 'membershipNames' => $this->getMembershipArray(),
            'contactFields' => $contactFieldsExternal, 'clubTitle' => $clubTitle, 'clubLogoUrl' => $clubLogoUrl);

        return $this->render('CommonUtilityBundle:ExternalApplication:externalApplicationForm.html.twig', $return);
    }

    /**
     * Function to save external application form
     *
     * @param object $request   \Symfony\Component\HttpFoundation\Request
     * @param object $context ExecutionContext object
     *
     * @return object JSON Response Object
     */
    public function externalApplicationSaveAction(Request $request)
    {
        $clubId = $this->container->get('club')->get('id');
        $formData = $request->request->all();
        $valueConstraints = new Callback(array('callback' => array($this, 'validEmail')));
        $emailData = array('value' => $formData['Email']);
        $collectionConstraint = new Collection(array(
            'value' => $valueConstraints,
        ));

        $errors = $this->container->get('validator')->validate($emailData, $collectionConstraint);
        if (count($errors) > 0) {
            return new JsonResponse(array('status' => 0, 'errorArray' => 1));
        } else {
            $senderEmail = $this->container->getParameter('external_application_sender_email');
            $subject = $this->container->get('translator')->trans('EXTERNAL_APPLICATION_FORM_SUBJECT');
            $senderName = $this->container->getParameter('external_application_mail_sender_name');
            $extId = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgExternalApplicationForm')
                ->saveExternalApplicationForm($clubId, $formData);
            $body = $this->getNotificationMailBodyContent($extId);
            $this->sendSwiftMesage($body, $formData['Email'], $senderEmail, $subject);

            return new JsonResponse(array('status' => 'SUCCESS', 'sync' => 1,
                'redirect' => $this->generateUrl('external_application'),
                'flash' => $this->container->get('translator')->trans('EXTERNAL_APPLICATION_FORM_SUCCESS_SAVE')));
        }
    }

    /**
     * Function to get membership array for external form application
     *
     * @return array
     */
    private function getMembershipArray()
    {
        $objMembershipPdo = new membershipPdo($this->container);
        $membersipFields = $objMembershipPdo->getMemberships($this->container->get('club')->get('type'), $this->container->get('club')->get('id'), $this->container->get('club')->get('sub_federation_id'), $this->container->get('club')->get('federation_id'));
        $clubId = $this->container->get('club')->get('id');
        $clubDefaultLang = $this->container->get('club')->get('default_lang');
        foreach ($membersipFields as $key => $memberCat) {
            $title = $memberCat['allLanguages'][$clubDefaultLang]['titleLang'] != '' ? $memberCat['allLanguages'][$clubDefaultLang]['titleLang'] : $memberCat['membershipName'];
            if (($memberCat['clubId'] == $clubId)) {
                $memberships[$key] = $title;
            }
        }

        return $memberships;
    }

    /**
     * Function to send mail after saving the external application form
     *
     * @param string $email   Email addresss to send
     * @param object $context ExecutionContext object
     *
     * @return null
     */
    public function validEmail($email, ExecutionContext $context)
    {

        $clubId = $this->container->get('club')->get('id');
        $mimeTypesMessage = $this->container->get('translator')->trans('EXTERNAL_APPLICATION_EMAIL_EXISTS');

        $emailCount = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgExternalApplicationForm')
            ->checkEmailExistsForExternalApplication($email, $clubId);

        if ($emailCount > 0) {
            $context->addViolation($mimeTypesMessage);
        }
    }

    /**
     * Function to send mail after saving the external application form
     *
     * @param string $bodyNew     body content
     * @param string $email       Email addresss to send
     * @param string $senderEmail Sender email
     * @param string $subject     Email subject
     * @param string $senderName  Sender name
     *
     * @return null
     */
    public function sendSwiftMesage($bodyNew, $email, $senderEmail, $subject, $senderName)
    {
        $mailer = $this->get('mailer');
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom(array($senderEmail => $senderName))
            ->setTo($email)
            ->setBody(stripslashes($bodyNew), 'text/html');

        $message->setCharset('utf-8');
        $mailer->send($message);
    }

    /**
     * Function to get the body content for notification mail in external application form
     *
     * @param int $extId   external form id
     *
     * @return object View Template Render Object
     */
    public function getNotificationMailBodyContent($extId)
    {

        $externalApplData = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgExternalApplicationForm')
            ->getExternalApplicationDataforPopup($extId);

        $contactFieldsFinal = $this->getExternalApplicationFieldsForMail();
        $clubObj = $this->container->get('club');
        $clubTitle = $clubObj->get('title');
        $clubLogoUrl = $this->getClubLogoForExternalApplication($clubObj);
        $salutation = $this->getSalutationofContactForMail($externalApplData['gender'], $clubObj->get('id'));
        $rendered = $this->renderView('CommonUtilityBundle:ExternalApplication:notificationMailApplicationTemplate.html.twig', array(
            'clubTitle' => $clubTitle,
            'salutation' => $salutation,
            'logoURL' => $clubLogoUrl,
            'externalApplData' => $externalApplData,
            'contactFields' => $contactFieldsFinal,
            'signature' => $clubObj->get('signature')
        ));

        return $rendered;
    }

    /**
     * Function to get the contatc fields details for  notification mail in external application form
     *
     * @return array $contactFieldsFinal contact fields array
     */
    public function getExternalApplicationFieldsForMail()
    {

        $terminologyService = $this->container->get('fairgate_terminology_service');
        //Creates the contact system fields array needed for external application mail
        $contactFields = $this->container->get('club')->get('contactFields');

        $contactFieldsExternal = $this->container->getParameter('external_application_system_fields');
        foreach ($contactFieldsExternal as $key => $fieldId) {
            foreach ($contactFields as $fieldData) {
                if ($fieldData['id'] == $fieldId) {
                    $contactFieldsExternal[$key] = $fieldData['title'];
                }
            }
        }

        //Completing the contact fields array creation using other fields available for external application mail
        $contactFieldsExtra = array_slice($contactFieldsExternal, 0, 10, true) +
            array("relatives" => $this->container->get('translator')->trans('EXTERNAL_APPLICATION_RELATIVES')) +
            array_slice($contactFieldsExternal, 10, count($contactFieldsExternal) - 1, true);

        $otherFields = array('membershipTitle' => $terminologyService->getTerminology('Fed membership', $this->container->getParameter('singular')),
            'selectedClubs' => $this->container->get('translator')->trans('EXTERNAL_APPLICATION_FORM_CLUB_CHOICE'),
            'comment' => $this->container->get('translator')->trans('EXTERNAL_APPLICATION_COMMENT'));

        $contactFieldsFinal = array_merge($contactFieldsExtra, $otherFields);

        return $contactFieldsFinal;
    }

    /**
     * Function to get the salutation for notification mail in external application form
     *
     * @param string $gender  gender of contact
     * @param int    $clubId  current club id
     *
     * @return string $salutation salutation value
     */
    public function getSalutationofContactForMail($gender, $clubId)
    {
        $salutation = '';
        $defSysLang = $this->container->get('club')->get('default_system_lang');
        $salutationObj = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgClubSalutationSettings')
            ->findOneBy(array('club' => $clubId));
        if ($salutationObj) {
            $salutationLangObj = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgClubSalutationSettingsI18n')
                ->findOneBy(array('id' => $salutationObj->getId(), 'lang' => $defSysLang));
            if($salutationLangObj){
                $salutation = ($gender == 'male') ? $salutationLangObj->getMaleFormalLang() : $salutationLangObj->getFemaleFormalLang();
            }else{
                $salutation = ($gender == 'male') ? $salutationObj->getMaleFormal() : $salutationObj->getFemaleFormal();
            }
        }
        if ($salutation == '') {
            $salutationObj = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgClubSalutationSettings')
                ->findOneBy(array('club' => 1));
            $salutationLangObj = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgClubSalutationSettingsI18n')
                ->findOneBy(array('id' => $salutationObj->getId(), 'lang' => $defSysLang));
            $salutation = ($gender == 'male') ? $salutationLangObj->getMaleFormalLang() : $salutationLangObj->getFemaleFormalLang();
        }

        return $salutation;
    }

    /**
     * Function to get the salutation for notification mail in external application form
     *
     * @param object $clubObj  club listener object
     *
     * @return string|null  $clubLogoUrl club logo url
     */
    public function getClubLogoForExternalApplication($clubObj)
    {
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
}
