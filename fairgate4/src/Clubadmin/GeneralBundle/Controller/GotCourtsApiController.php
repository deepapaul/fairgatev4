<?php

/**
 * GotCourtsApiController
 * This controller is used to handle GotCourts Api service.
 * @package    CommonUtilityBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
namespace Clubadmin\GeneralBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FairgateApiBundle\Util\GotCourtsApiDetails;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Repository\Pdo\ClubPdo;
use Common\UtilityBundle\Repository\Pdo\ApiPdo;
use Symfony\Component\Intl\Intl;

class GotCourtsApiController extends FgController
{

    /**
     * This method is used to book GotCourts api service.
     * 
     * @return JsonResponse
     */
    public function bookGotCourtsApiServiceAction()
    {
        $clubId = $this->container->get('club')->get('id');
        $contactId = $this->container->get('contact')->get('id');
        $gcApiDet = $this->em->getRepository('CommonUtilityBundle:FgApiGotcourts')->getGotCourtsApi($clubId);
        if (empty($gcApiDet)) {//To check gotcourts api service already exists.
            $apiId = $this->em->getRepository('CommonUtilityBundle:FgApiGotcourts')->bookGotCourtsApi($clubId, $contactId);
            $logArray = array();
            $logArray[] = array('field' => 'status', 'value_after' => 'booked', 'value_before' => '');
            $this->em->getRepository('CommonUtilityBundle:FgApiGotcourtsLog')->saveServiceLog($logArray, $clubId, $apiId, $contactId);
        }
        $gcApiUtil = new GotCourtsApiDetails($this->container);
        $apiData = $gcApiUtil->getGotCourtsApiDetails();
        $this->sendBookingAndCancellationMail('booking', $apiData);

        return new JsonResponse(array('status' => 'SUCCESS', 'noparentload' => true, 'data' => $apiData, 'flash' => $this->get('translator')->trans('SETTINGS_BOOK_GCAPI_SERVICE_SUCCESS')));
    }

    /**
     * This method is used to generate GotCourts api token.
     * 
     * @param Request $request Request Object
     * 
     * @return JsonResponse
     */
    public function generateApiTokenAction(Request $request)
    {
        $apiId = $request->get('apiId');
        $clubId = $this->container->get('club')->get('id');
        $contactId = $this->container->get('contact')->get('id');
        $data = $logArray = array();
        $data['status'] = 'generated';
        $data['type'] = $request->get('type'); //create a new token or re-generate token(new or null)
        $data['contactId'] = $contactId;
        $gcApiDetails = new GotCourtsApiDetails($this->container, $request);
        $data['token'] = $gcApiDetails->createClubToken($clubId);
        $currentGcApiData = $this->em->getRepository('CommonUtilityBundle:FgApiGotcourts')->find($apiId);
        $logArray[] = array('field' => 'status', 'value_after' => $data['type'] == 'new' ? 'generated' : 'regenerated', 'value_before' => $currentGcApiData->getStatus());
        $logArray[] = array('field' => 'token', 'value_after' => $data['token'], 'value_before' => $currentGcApiData->getApitoken());
        $this->em->getRepository('CommonUtilityBundle:FgApiGotcourts')->updateGotCourtsApi($apiId, $data, $data['type'] == 'new' ? 'generated' : 'regenerated');
        $this->em->getRepository('CommonUtilityBundle:FgApiGotcourtsLog')->saveServiceLog($logArray, $clubId, $apiId, $contactId);
        $apiData = $gcApiDetails->getGotCourtsApiDetails();

        return new JsonResponse(array('status' => 'SUCCESS', 'noparentload' => true, 'data' => $apiData, 'flash' => ($data['type'] == 'new') ? $this->get('translator')->trans('SETTINGS_GENERATE_GC_TOKEN_SUCCESS') : $this->get('translator')->trans('SETTINGS_REGENERATE_GC_TOKEN_SUCCESS')));
    }

    /**
     * This method is used to book GotCourts api connection.
     * 
     * @return JsonResponse
     */
    public function cancelGotCourtsApiServiceAction(Request $request)
    {
        $apiId = $request->get('apiId');
        $clubId = $this->container->get('club')->get('id');
        $contactId = $this->container->get('contact')->get('id');
        $data = array();
        $data['contactId'] = $contactId;
        $data['status'] = 'cancelled';
        $currentGcApiData = $this->em->getRepository('CommonUtilityBundle:FgApiGotcourts')->find($apiId);
        $logArray = array();
        $logArray[] = array('field' => 'status', 'value_after' => $data['status'], 'value_before' => $currentGcApiData->getStatus());
        $this->em->getRepository('CommonUtilityBundle:FgApiGotcourtsLog')->saveServiceLog($logArray, $clubId, $apiId, $contactId);
        $this->em->getRepository('CommonUtilityBundle:FgApiGotcourts')->updateGotCourtsApi($apiId, $data, 'cancelled');
        $apiData = $this->em->getRepository('CommonUtilityBundle:FgApiGotcourts')->getGotCourtsApiById($apiId);
        $this->sendBookingAndCancellationMail('cancel', $apiData);

        return new JsonResponse(array('status' => 'SUCCESS', 'noparentload' => true, 'data' => $apiData, 'flash' => $this->get('translator')->trans('SETTINGS_CANCEL_GCAPI_SERVICE_SUCCESS')));
    }

    /**
     * This method is used to render booking mail template.
     * 
     * @param string $mailType Type of mail(booking or cancel)
     * 
     * @return type
     */
    private function sendBookingAndCancellationMail($mailType, $apiData)
    {
        $conn = $this->container->get('database_connection');
        $clubDetails = $this->getClubDetails();
        if (isset($clubDetails['adminDetails'])) {
            foreach ($clubDetails['adminDetails'] as $adminData) {
                $templateParameters1 = $this->getEmailParameters($mailType . '_confirm', $adminData['lang']);
                $templateParameters1['street'] = $clubDetails['street'];
                $templateParameters1['zipcode'] = $clubDetails['zipcode'];
                $templateParameters1['location'] = $clubDetails['location'];
                $countryList = Intl::getRegionBundle()->getCountryNames($adminData['lang']);
                $templateParameters1['country'] = $countryList[$clubDetails['country']];
                $templateParameters1['clubCurrency'] = $clubDetails['clubCurrency'];
                $templateParameters1['salutation'] = $adminData['salutation'];
                $templateParameters1['correslang'] = $adminData['lang'];
                $templateParameters1['apiDetails'] = $apiData;
                $templateParameters1['body'] = $this->renderView('ClubadminGeneralBundle:GotCourtsApi:mailTemplate.html.twig', $templateParameters1);
                $this->sendSwiftMail($adminData['email'], $templateParameters1);
            }
        }
        $salesMailId = $this->container->getParameter('gc_api_sales_email');
        $clubDefaultLang = $this->container->get('club')->get('club_default_lang');
        $templateParameters2 = $this->getEmailParameters($mailType . '_notification', $clubDefaultLang);
        $templateParameters2['street'] = $clubDetails['street'];
        $templateParameters2['zipcode'] = $clubDetails['zipcode'];
        $templateParameters2['location'] = $clubDetails['location'];
        $countryList = Intl::getRegionBundle()->getCountryNames($clubDefaultLang);
        $templateParameters2['country'] = $countryList[$clubDetails['country']];
        $apiPdo = new ApiPdo($this->container);
        $templateParameters2['salutation'] = $apiPdo->getDefaultSalutaion($conn, $this->container->get('club')->get('id'), $clubDefaultLang);
        $templateParameters2['correslang'] = $clubDefaultLang;
        $templateParameters2['clubCurrency'] = $clubDetails['clubCurrency'];
        $templateParameters2['apiDetails'] = $apiData;
        $templateParameters2['body'] = $this->renderView('ClubadminGeneralBundle:GotCourtsApi:mailTemplate.html.twig', $templateParameters2);
        $this->sendSwiftMail($salesMailId, $templateParameters2);
    }

    /**
     * This method is used to get club details
     * 
     * @return array club details
     */
    private function getClubDetails()
    {
        $club = $this->container->get('club');
        $conn = $this->container->get('database_connection');
        $clubDetails = array();
        $clubPdo = new ClubPdo($this->container);
        $clubData = $clubPdo->getClubData($club->get('id'));
        $clubDetails['clubTitle'] = $clubData['title'];
        $clubDetails['street'] = $clubData['sp_street'];
        $clubDetails['zipcode'] = $clubData['sp_zipcode'];
        $clubDetails['location'] = $clubData['sp_city'];
        $clubDetails['country'] = $clubData['sp_country'];
        $clubDetails['clubCurrency'] = $this->container->get('club')->get('clubCurrency');
        $corrLangId = $this->container->getParameter('system_field_corress_lang');
        $roleFedAdminId = $this->container->getParameter('fed_admin');
        $roleClubAdminId = $this->container->getParameter('club_admin');
        $adminRoleIds = array($roleFedAdminId, $roleClubAdminId);
        $clubHeirarchy = $club->get('clubHeirarchy');
        $clubIds = array_merge(array($club->get('id')), $clubHeirarchy);
        $clubSystemLang = $club->get('default_system_lang');
        $apiPdo = new ApiPdo($this->container);
        $clubDetails['adminDetails'] = $apiPdo->getMainAdminDetails($conn, $adminRoleIds, $clubIds, $clubSystemLang, $corrLangId);

        return $clubDetails;
    }

    /**
     * This method is used to send emails.
     * 
     * @param string $email              Email addresss to send
     * @param array  $templateParameters Parameters used to render mail content.
     * 
     * @return Void
     */
    private function sendSwiftMail($email, $templateParameters)
    {
        try {
            $this->sendSwiftMessage($templateParameters['body'], $email, $templateParameters['senderEmail'], $templateParameters['subject']);
        } catch (\Exception $e) {
            
        }
    }

    /**
     * This method is used to get email parameters to render mail template.
     * 
     * @param string $type Type of email template
     * 
     * @return array       Template parameter array 
     */
    private function getEmailParameters($type, $lang)
    {
        $clubId = $this->container->get('club')->get('id');
        $baseurlArr = FgUtility::getMainDomainUrl($this->container, $clubId);
        $clubDetails = $this->container->get('club')->get('club_details');
        $logoUploadPath = FgUtility::getUploadFilePath($clubId, 'clublogo');
        $clubLogoPath = $baseurlArr['baseUrl'] . '/' . $logoUploadPath;
        $clubDefaultLang = $this->container->get('club')->get('club_default_lang');
        $templateParameters['clubTitle'] = $clubDetails[$lang]['title'];
        $templateParameters['signature'] = $this->container->get('translator')->trans('GC_EMAIL_FAIRGATE_SIGNATURE', array(), 'messages', $lang);
        $templateParameters['logoURL'] = $clubDetails[$lang]['logo'] != "" ? $clubLogoPath . '/' . $clubDetails[$lang]['logo'] : $clubLogoPath . '/' . $clubDetails[$clubDefaultLang]['logo'];
        $templateParameters['contactName'] = $this->container->get('contact')->get('nameNoSort');
        $templateParameters['mailType'] = $type;
        $templateParameters['senderEmail'] = 'noreply@fairgate.ch';

        if ($type == 'booking_confirm') {
            $templateParameters['subject'] = $this->container->get('translator')->trans('GN_SETTINGS_GCAPI_BOOKING_EMAIL_SUBJECT', array('%clubTitle%' => $templateParameters['clubTitle']), 'messages', $lang);
            $templateParameters['content'] = $this->container->get('translator')->trans('GN_SETTINGS_GCAPI_BOOKING_CONFIRM_EMAIL_CONTENT', array(), 'messages', $lang);
        } elseif ($type == 'booking_notification') {
            $templateParameters['subject'] = $this->container->get('translator')->trans('GN_SETTINGS_GCAPI_BOOKING_EMAIL_SUBJECT', array('%clubTitle%' => $templateParameters['clubTitle']), 'messages', $lang);
            $templateParameters['content'] = $this->container->get('translator')->trans('GN_SETTINGS_GCAPI_BOOKING_NOTIF_EMAIL_CONTENT', array(), 'messages', $lang);
        } elseif ($type == 'cancel_confirm') {
            $templateParameters['subject'] = $this->container->get('translator')->trans('GN_SETTINGS_GCAPI_CANCEL_EMAIL_SUBJECT', array('%clubTitle%' => $templateParameters['clubTitle']), 'messages', $lang);
            $templateParameters['content'] = $this->container->get('translator')->trans('GN_SETTINGS_GCAPI_CANCEL_CONFIRM_EMAIL_CONTENT', array(), 'messages', $lang);
        } elseif ($type == 'cancel_notification') {
            $templateParameters['subject'] = $this->container->get('translator')->trans('GN_SETTINGS_GCAPI_CANCEL_EMAIL_SUBJECT', array('%clubTitle%' => $templateParameters['clubTitle']), 'messages', $lang);
            $templateParameters['content'] = $this->container->get('translator')->trans('GN_SETTINGS_GCAPI_CANCEL_NOTIF_EMAIL_CONTENT', array(), 'messages', $lang);
        } else {
            
        }

        return $templateParameters;
    }

    /**
     * Function to send swift mail
     *
     * @param string $emailBody   body content
     * @param string $email       Email addresss to send
     * @param string $senderEmail Sender email
     * @param string $subject     Email subject
     */
    public function sendSwiftMessage($emailBody, $email, $senderEmail, $subject)
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
}
