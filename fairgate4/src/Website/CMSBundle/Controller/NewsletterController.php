<?php

/**
 * NewsletterController.
 */
namespace Website\CMSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Util\FgClubSyncDataToAdmin;

/**
 * NewsletterController.
 *
 * This controller is used for website newsletter subscription page
 *
 * @package 	Website
 * @subpackage 	CMSBundle
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 *
 */
class NewsletterController extends Controller
{

    /**
     * Function to get the newsletter subscription form
     *
     * @param Object $request The request object
     *
     * @return object/json
     */
    public function subscriptionFormAction(Request $request)
    {
        $event = $request->get('event');
        $returnArray = $this->getFieldOptions();
        $returnArray['elementId'] = $request->get('elementId');
        $returnArray['saveSubscriptionLinkRoute'] = 'website_subscriptionform_save';

        if ($event == 'edit') {
            return $this->render('WebsiteCMSBundle:Newsletter:subscriptionForm.html.twig', $returnArray);
        } else {
            $jsonResponse['htmlContent'] = $this->renderView('WebsiteCMSBundle:Newsletter:subscriptionForm.html.twig', $returnArray);
            $jsonResponse['elementType'] = 'newsletter-subscription';
            return new JsonResponse($jsonResponse);
        }
    }

    /**
     * Function to save the newsletter subscription form
     *
     * @param Object $request The request object
     *
     * @return object/json
     */
    public function saveSubscriptionFormAction(Request $request)
    {
        $subscriptionData = $request->get('data');
        $subscriptionFilteredData = array();
        $jsonResponse = array();

        foreach ($subscriptionData as $subscription) {
            if ($subscription['value'] != '' && $subscription['name'] != 'g-recaptcha-response') {
                $subscriptionFilteredData[$subscription['name']] = $subscription['value'];
            }
        }

        if ($subscriptionFilteredData['Email'] != '') {
            if (!$this->checkIfSubscriberExists($subscriptionFilteredData['Email'])) {
                $dataArray['clubId'] = $this->container->get('club')->get('id');
                $dataArray['jsonData'] = json_encode($subscriptionFilteredData);
                $dataArray['type'] = 'NEWSLETTER_SUBSCRIPTION';
                $dataArray['uniqueId'] = md5(uniqid() . uniqid());

                //save data to table
                $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgPendingApplications')->savePendingApplicationData($dataArray);
                //send the mail template to the email
                $this->sentActivationMail($subscriptionFilteredData['Email'], $dataArray['uniqueId'], $subscriptionFilteredData);

                $jsonResponse['status'] = true;
                $jsonResponse['message'] = $this->get('translator')->trans('CMS_NEWSLETTER_SUBSCRIPTION_SUCCESS');
            } else {
                $jsonResponse['status'] = false;
                $jsonResponse['message'] = $this->get('translator')->trans('CMS_NEWSLETTER_SUBSCRIPTION_EXISTS_ERROR');
            }
        } else {
            $jsonResponse['status'] = false;
            $jsonResponse['message'] = $this->get('translator')->trans('CMS_NEWSLETTER_SUBSCRIPTION_ERROR');
        }
        return new JsonResponse($jsonResponse);
    }

    /**
     * Function to activate an subscription
     *
     * @param Object $request The request object
     *
     * @return object/json
     */
    public function activateSubscriptionAction(Request $request)
    {
        $subscriptionCode = $request->get('code');
        $em = $this->getDoctrine()->getManager();

        $clubObj = $this->container->get('club');
        $currentClubObj = $clubObj;
        if ($subscriptionCode != '') {
            $applicationData = $em->getRepository('CommonUtilityBundle:FgPendingApplications')->getPendingApplicationData($clubObj->get('id'), $subscriptionCode, 'NEWSLETTER_SUBSCRIPTION');

            if ($applicationData['subscriberData'] != '') {
                $returnArray = array();
                $returnArray['title'] = $this->get('translator')->trans('CMS_NEWSLETTER_SUBSCRIPTION_LANDINGPAGE_TITLE');

                $subscriberDataArray = json_decode($applicationData['subscriberData'], true);
                $subscriberDataArray['CorresLang'] = ($subscriberDataArray['Language'] != '' && in_array($subscriberDataArray['Language'], $clubObj->get('club_languages'))) ? $subscriberDataArray['Language'] : $clubObj->get('club_default_lang');

                if ($this->checkIfSubscriberExists($subscriberDataArray['Email'])) {
                    $returnArray['status'] = false;
                    $returnArray['message'] = $this->get('translator')->trans('CMS_NEWSLETTER_SUBSCRIPTION_LANDINGPAGE_ERROR');
                } else {
                    //CHECK IF CONTACT WITH EMAIL EXISTS
                    $contactEmails = $em->getRepository('CommonUtilityBundle:FgCmAttribute')->searchEmailExistAndIsMergable($this->container, '', $subscriberDataArray['Email'], true, '', 'contact');

                    if (count($contactEmails) > 0) {
                        $email = $contactEmails[0];
                        //need to check if the contact is archived; if archived need to add the contact as a subscriber
                        $contactDetail = $em->getRepository('CommonUtilityBundle:FgCmContact')->getClubContactId($email['contactId'], $clubObj->get('id'));
                        if ($contactDetail['isDeleted'] == 1) {
                            $clubObj = $em->getRepository('CommonUtilityBundle:FgClub')->find($clubObj->get('id'));
                            $em->getRepository('CommonUtilityBundle:FgCnSubscriber')->newSubscriber($subscriberDataArray, $clubObj);
                        } else {
                            $em->getRepository('CommonUtilityBundle:FgCmContact')->subscribeContact($contactDetail['id'], $clubObj->get('id'));
                        }
                    } else {
                        //Save subscriber
                        $clubObj = $em->getRepository('CommonUtilityBundle:FgClub')->find($clubObj->get('id'));
                        $em->getRepository('CommonUtilityBundle:FgCnSubscriber')->newSubscriber($subscriberDataArray, $clubObj);
                    }

                    /** Update the subscriber count **/
                    $subscriberSyncObject = new FgClubSyncDataToAdmin($this->container);
                    $subscriberSyncObject->updateSubscriberCount($currentClubObj->get('id'));
                    /***********************************************/
                
                    $returnArray['status'] = true;
                    $returnArray['message'] = $this->get('translator')->trans('CMS_NEWSLETTER_SUBSCRIPTION_LANDINGPAGE_TEXT');
                }
                return $this->render('WebsiteCMSBundle:Newsletter:activateSubscription.html.twig', $returnArray);
            } else {
                throw new NotFoundHttpException();
            }
        } else {
            throw new NotFoundHttpException();
        }
    }

    /**
     * Function for subscription form 
     *
     * @param Object $request The request object
     *
     * @return object/json
     */
    public function subscribeAction(Request $request)
    {
        if (!in_array('communication', $this->container->get('club')->get('bookedModulesDet'))) {
            throw new NotFoundHttpException();
        }
        $returnArray = $this->getFieldOptions();
        $returnArray['elementId'] = 0;
        $returnArray['clubLogoUrl'] = $this->getClubLogoUrl($this->container->get('club'), FgUtility::getBaseUrl($this->container));
        $returnArray['googleCaptchaSitekey'] = $this->container->getParameter('googleCaptchaSitekey');
        $returnArray['saveSubscriptionLinkRoute'] = 'website_public_page_newsletter_subscribeform_save';

        return $this->render('WebsiteCMSBundle:Newsletter:subscribe.html.twig', $returnArray);
    }

    /**
     * Function to get the field options for twig
     *
     * @return array
     */
    private function getFieldOptions()
    {
        $returnArray['genderArr'] = array(
            array('id' => '', 'title' => $this->get('translator')->trans('CMS_NEWSLETTER_GENDER_DEFAULT')),
            array('id' => 'Male', 'title' => $this->get('translator')->trans('CM_MALE')),
            array('id' => 'Female', 'title' => $this->get('translator')->trans('CM_FEMALE'))
        );
        $returnArray['salutationArr'] = array(
            array('id' => '', 'title' => $this->get('translator')->trans('CMS_NEWSLETTER_SALUTATION_DEFAULT')),
            array('id' => 'Formal', 'title' => $this->get('translator')->trans('CM_FORMAL')),
            array('id' => 'Informal', 'title' => $this->get('translator')->trans('CM_INFORMAL'))
        );
        $clubLanguages = $this->container->get('club')->get('club_languages');
        $languageNameArray = FgUtility::getAllLanguageNames();
        $returnArray['clubLanguages'][0] = array('id' => '', 'title' => $this->get('translator')->trans('CMS_NEWSLETTER_LANGUAGE_DEFAULT'));
        foreach ($clubLanguages as $language) {
            $returnArray['clubLanguages'][] = array('id' => $language, 'title' => $languageNameArray[$language]);
        }
        return $returnArray;
    }

    /**
     * Function to sent the activation mail to the specified email
     * 
     * @param array $email The subscription email
     * @param array $uniqueId The unique activation code id
     * @param array $dataArray The subscription data user specified
     * 
     * @return void
     */
    private function sentActivationMail($email, $uniqueId, $dataArray)
    {
        $templateParameters['clubTitle'] = $this->container->get('club')->get('title');
        $templateParameters['salutation'] = $this->getSalutation($dataArray);
        $templateParameters['logoURL'] = $this->getClubLogoUrl($this->container->get('club'), FgUtility::getBaseUrl($this->container));
        $templateParameters['signature'] = $this->container->get('club')->get('signature');

        $templateParameters['mailContent'] = $this->container->get('translator')->trans('CMS_NEWSLETTER_SUBSCRIPTION_MAILCONTENT', array('%linkstart%' => '<a href="##LINK##">', '%linkend%' => '<a>'));
        $activationLink = FgUtility::generateUrlForSharedClub($this->container, 'website_public_page_newsletter_activation_link', 0, array('code' => $uniqueId));

        $templateParameters['mailContent'] = str_replace('##LINK##', $activationLink, $templateParameters['mailContent']);

        $emailBody = $this->renderView('WebsiteCMSBundle:Newsletter:activationMailTemplate.html.twig', $templateParameters);

        $mailer = $this->get('mailer');
        $message = \Swift_Message::newInstance()
            ->setSubject($this->container->get('translator')->trans('CMS_NEWSLETTER_SUBSCRIPTION_SUBJECT'))
            ->setFrom('noreply@fairgate.ch')
            ->setTo($email)
            ->setBody(stripslashes($emailBody), 'text/html');
        $message->setCharset('utf-8');
        $mailer->send($message);
    }

    /**
     * Function to get the salutation for the email template
     *
     * @param array $dataArray The subscription data user specified
     *  
     * @return string
     */
    private function getSalutation($dataArray)
    {
        $salutation = '';
        if ($dataArray['Gender'] == '' || $dataArray['Salutation'] == '' || $dataArray['FirstName'] == '' || $dataArray['LastName'] == '') {
            $salutation = $this->get('translator')->trans('CMS_NEWSLETTER_SUBSCRIPTION_SALUTATION');
        } else {  
            $salutationArray = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgClubSalutationSettings')->getSalutationForNewletterSubscription($this->container);
            if ($dataArray['Salutation'] == 'Formal' && $dataArray['LastName'] != '') {
                $salutation = (($dataArray['Gender'] == 'Male') ? $salutationArray['maleFormal'] : $salutationArray['femaleFormal']) . ' ' . $dataArray['LastName'];
            } else if ($dataArray['Salutation'] == 'Informal' && $dataArray['FirstName'] != '') {
                $salutation = (($dataArray['Gender'] == 'Male') ? $salutationArray['maleInformal'] : $salutationArray['femaleInformal']) . ' ' . $dataArray['FirstName'];
            } else {
                $salutation = $this->get('translator')->trans('CMS_NEWSLETTER_SUBSCRIPTION_SALUTATION');
            }
            $salutation .= ',';
        }

        return $salutation;
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
     * Function to check if the subscriber exists
     * 
     * @param string $email The email of the subs   criber
     * 
     * @return boolean  $exists
     */
    private function checkIfSubscriberExists($email)
    {
        $emailDuplicate = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmAttribute')->searchEmailExistAndIsMergable($this->container, '', $email, true, '', 'subscriber');

        $exists = false;
        foreach ($emailDuplicate as $email) {
            if ($email['type'] == 'subscriber') {
                $exists = true;
                break;
            } else {
                //$email['contactId'] will be the fed contact id, need to check if the contact is a subscriber for this club
                if ($this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmContact')->checkIfEmailIsSubscribedInClub($email['contactId'], $this->container->get('club')->get('id'))) {
                    $exists = true;
                    break;
                }
            }
        }

        return $exists;
    }
}
