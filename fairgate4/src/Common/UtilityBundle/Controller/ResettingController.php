<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Common\UtilityBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Util\FgContactSyncDataToAdmin;
/**
 * Controller managing the resetting of the password
 * Overridden from FOSUserBundle
 *
 * @author pitsolutions.ch
 */
class ResettingController extends Controller
{

    /**
     * Request reset user password: show form
     *
     * @return HTML
     */
    public function requestAction()
    {
        $club = $this->container->get('club');
        $this->container->get('translator')->setLocale($club->get('default_system_lang'));
        $session = $this->container->get('session');
        $this->em = $this->getDoctrine()->getManager();
        $loggedClubUserId = $session->get('loggedClubUserId');
        if (isset($loggedClubUserId)) {
            return $this->redirect($this->generateUrl('contact_index'));
        }

        return $this->render('CommonUtilityBundle:Resetting:request.html.twig', array('googleCaptchaSitekey' => $this->container->getParameter('googleCaptchaSitekey')));
    }

    /**
     * Request reset user password: submit form and send email
     *
     * @return HTML
     */
    public function sendEmailAction(Request $request)
    {
        $club = $this->container->get('club');
        $this->container->get('translator')->setLocale($club->get('default_system_lang'));
        $username = $request->request->get('username');
        // set 1 if request from activate account page
        $activateaccount = $request->request->get('activateaccount');

        /** @var $user UserInterface */
        //Check a contact with requsted email exists in club
        $user = $this->container->get('fos_user.user_manager')->findUserByEmailClub($club->get('id'), $username);
        if (null === $user) {
            //Check a superadmin contact with requsted email exists
            $user = $this->get('fos_user.user_manager')->findUserByEmailClub(NULL, $username);
        }

        //google captcha verification
        $isCaptchaValidated = 1;
        $secret = $this->container->getParameter('googleCaptchaSecretkey');
        $recaptcha = new \ReCaptcha\ReCaptcha($secret);
        $resp = $recaptcha->verify($request->get('g-recaptcha-response'), $request->server->get('REMOTE_ADDR')); //$_SERVER['REMOTE_ADDR'], $request->server->get('REMOTE_ADDR')
        if (!$resp->isSuccess() && $activateaccount != 1) { //captch validation failed //not checking captch in activate account page
            $isCaptchaValidated = 0;
        }


        $errorMesssage = ($isCaptchaValidated == 0) ? 'CAPTCHA_VERIFICATION_FAILED' : 'RESET_PASSWORD_INVALID_EMAIL';
        if ($activateaccount == 1 && ((null === $user || $username == '' ) || ($isCaptchaValidated == 0))) { // request is from activate accout
            $clubName = $club->get('title');
            return $this->container->get('templating')->renderResponse('CommonUtilityBundle:Resetting:activateaccount.html.twig', array('error' => $errorMesssage, 'clubName' => $clubName, 'googleCaptchaSitekey' => $this->container->getParameter('googleCaptchaSitekey')));
        } else if ((null === $user || $username == '') || ($isCaptchaValidated == 0)) { // request is not from activate account
            return $this->container->get('templating')->renderResponse('CommonUtilityBundle:Resetting:request.html.twig', array('error' => $errorMesssage, 'googleCaptchaSitekey' => $this->container->getParameter('googleCaptchaSitekey')));
        }

        if ($user->isPasswordRequestNonExpired($this->container->getParameter('fos_user.resetting.token_ttl'))) {
            if ($activateaccount == 1) {
                //backend login- pagetitle and form messages.
                $return['pageTitle'] = $this->get('translator')->trans('INTERNAL_LOGIN_ACTIVATE_ACCOUNT');
                $return['pageFormTitle'] = $this->get('translator')->trans('INTERNAL_LOGIN_ACTIVATE_ACCOUNT');
                $return['pageFormMsg'] = $this->get('translator')->trans('ALREADY_REQUESTED_EMAIL');
            } else {
                $return['pageTitle'] = $this->get('translator')->trans('RESET_PASSWORD_PAGE_TITLE_NEW');
                $return['pageFormTitle'] = $this->get('translator')->trans('RESET_PASSWORD_PAGE_TITLE_NEW');
                $return['pageFormMsg'] = $this->get('translator')->trans('ALREADY_REQUESTED_EMAIL');
            }
            return $this->render('CommonUtilityBundle:Resetting:passwordAlreadyRequested.html.twig', $return);
        }

        if (null === $user->getConfirmationToken()) {
            /** @var $tokenGenerator \FOS\UserBundle\Util\TokenGeneratorInterface */
            $tokenGenerator = $this->get('fos_user.util.token_generator');
            $user->setConfirmationToken($tokenGenerator->generateToken());
        }

        $this->em = $this->getDoctrine()->getManager();

        //set locale with respect to particular contact (for sending notification mail in the corresponding language of contact)
        $rowContactLocale = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getContactLanguageDetails($user->getContact()->getId(), $club->get('id'), $club->get('clubTable'));
        $this->container->get('contact')->setContactLocale($this->container, $request, $rowContactLocale);
        //To set the club TITLE, SIGNATURE based on default language
        $this->container->get('contact')->setClubParamsOnLangauge($this->container->get('club'));
        

        //Get salutation of user for sending send mail
        $salutation = $this->em->getRepository('CommonUtilityBundle:SfGuardUser')->getSalutation($user, $club);
        $clubTitle = $club->get('title');
        if ($activateaccount == 1) {
            $resetPasswordTemplate = 'CommonUtilityBundle:Resetting:activateAccountTemplate.html.twig';
            $subject = $this->get('translator')->trans('INTERNAL_LOGIN_ACTIVATE_ACCOUNT');
        } else {
            $resetPasswordTemplate = 'CommonUtilityBundle:Resetting:resetPassword.html.twig';
            $subject = $this->get('translator')->trans('Resetpassword');
        }
        /*         * Club logo* */
        $clubObj = $this->container->get('club');
        $clubLogo = $clubObj->get('logo');
        $rootPath = FgUtility::getRootPath($this->container);
        $baseurl = FgUtility::getBaseUrl($this->container);
        if ($clubLogo == '' || !file_exists($rootPath . '/' . FgUtility::getUploadFilePath($clubObj->get('id'), 'clublogo', false, $clubLogo))) {
            $clubLogoUrl = '';
        } else {
            $clubLogoUrl = $baseurl . '/' . FgUtility::getUploadFilePath($clubObj->get('id'), 'clublogo', false, $clubLogo);
        }
        $this->get('fos_user.mailer')->setParams(array('resetPasswordRouting' => 'fos_user_resetting_reset', 'resetPasswordTemplate' => $resetPasswordTemplate, 'clubTitle' => $clubTitle, 'salutation' => $salutation, 'clubLogoUrl' => $clubLogoUrl, 'subject' => $subject, 'container' => $this->container ));
        $this->container->get('fos_user.mailer')->sendResettingEmailMessage($user);
        $user->setPasswordRequestedAt(new \DateTime());
        $this->container->get('fos_user.user_manager')->updateUser($user);

        return new RedirectResponse($this->generateUrl('fos_user_resetting_check_email', array('username' => $username, 'activateaccount' => $activateaccount)));
    }

    /**
     * Tell the user to check his email provider
     *
     * @return HTML
     */
    public function checkEmailAction(Request $request)
    {
        $club = $this->container->get('club');
        $this->container->get('translator')->setLocale($club->get('default_system_lang'));
        $username = $request->query->get('username');
        // set 1 if request from activate account page
        $activateaccount = $request->query->get('activateaccount');
        if (empty($username)) {
            // the user does not come from the sendEmail action
            return new RedirectResponse($this->container->get('router')->generate('fos_user_resetting'));
        }
        $session = $this->container->get('session');
        $loggedClubUserId = $session->get('loggedClubUserId');

        if (isset($loggedClubUserId)) {
            return $this->redirect($this->generateUrl('show_dashboard'));
        }
        $return = array();
        if ($activateaccount == 1) {
            //backend login- pagetitle and form messages.
            $return['pageTitle'] = $this->get('translator')->trans('INTERNAL_LOGIN_ACTIVATE_ACCOUNT');
            $return['pageFormTitle'] = $this->get('translator')->trans('INTERNAL_LOGIN_ACTIVATE_ACCOUNT');
            $return['pageFormMsg'] = $this->get('translator')->trans('INTERNAL_LOGIN_ACTIVATE_ACCOUNT_CHECK');
        } else {
            $return['pageTitle'] = $this->get('translator')->trans('RESET_PASSWORD_PAGE_TITLE_NEW');
            $return['pageFormTitle'] = $this->get('translator')->trans('RESET_PASSWORD_PAGE_TITLE_NEW');
            $return['pageFormMsg'] = $this->get('translator')->trans('RESET_PASSWORD_EMAIL_SEND_SUCCESS');
        }
        return $this->container->get('templating')->renderResponse('CommonUtilityBundle:Resetting:checkEmail.html.twig', $return);
    }

    /**
     * Reset user password
     * @param String $token Password token
     *
     * @return String
     */
    public function resetAction(Request $request, $token)
    {
        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->get('fos_user.resetting.form.factory');
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');
        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $club = $this->container->get('club');
        $this->container->get('translator')->setLocale($club->get('default_system_lang'));
        $user = $this->container->get('fos_user.user_manager')->findUserByConfirmationToken($token);
        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with "confirmation token" does not exist for value "%s"', $token));
        }

        if ($user->getIsSuperAdmin() != 1) {
            $user = $this->container->get('fos_user.user_manager')->findUserByConfirmationTokenAndClub($token, $club->get('id'));
        }
        $this->em = $this->getDoctrine()->getManager();
        $this->conn = $this->container->get('database_connection');

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::RESETTING_RESET_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $formFactory->createForm();
        $form->setData($user);

        $qryResult = $this->em->getRepository('CommonUtilityBundle:SfGuardUser')->checkNullPassword($this->conn, $user->getId());
        if ($qryResult[0]['password'] == '' || $qryResult[0]['password'] == 'NULL') {
            $passwordFlag = 'passwordRequest';
        } else {
            $passwordFlag = 'passwordChange';
        }
        $contactId = $qryResult[0]['contact_id'];
        $nowdate = strtotime(date('Y-m-d H:i:s'));
        $dateToday = date('Y-m-d H:i:s', $nowdate);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::RESETTING_RESET_SUCCESS, $event);
            $userManager->updateUser($user);
            if ($passwordFlag == 'passwordRequest') {
                $qryResult = $this->em->getRepository('CommonUtilityBundle:FgCmChangeLog')->passwordLogEntry($this->container, $club->get('id'), $contactId, $dateToday, 'Requested');
            } else {
                $qryResult = $this->em->getRepository('CommonUtilityBundle:FgCmChangeLog')->passwordLogEntry($this->container, $club->get('id'), $contactId, $dateToday, 'Changed');
            }
            $currentRoles = $user->getRoles();
            $adminArray = array_intersect(array('ROLE_USERS'), $currentRoles); 
            if(count($adminArray) > 0){
                $syncData = new FgContactSyncDataToAdmin($this->container);
                $syncData->updateLastAdminLogged($club->get('id'),$dateToday)->executeQuery();
            }
            $this->em->getRepository('CommonUtilityBundle:FgCmContact')->updateLoginCount($contactId);
            $this->em->getRepository('CommonUtilityBundle:FgCmChangeLog')->changeLogLogin($this->container, $club->get('id'), $contactId, $dateToday);
            $session = $this->container->get('session');
            $session->set('loggedClubUserId', $contactId);
            $clubId = $this->container->get('club')->get('id');
            $contactId = $session->get('loggedClubUserId');
            $session->set('quickWindow_' . $clubId . '_' . $contactId, true);
            $this->em->getRepository('CommonUtilityBundle:SfGuardUser')->alterPasswordByContactId($this->conn, $user->getId());
            if (null === $response = $event->getResponse()) {
                $url = $this->generateUrl('dashboard');
                $response = new RedirectResponse($url);
            }

            $dispatcher->dispatch(FOSUserEvents::RESETTING_RESET_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

            return $response;
        }

        return $this->render('CommonUtilityBundle:Resetting:reset.html.twig', array(
                'token' => $token,
                'form' => $form->createView(),
        ));
    }

    /**
     * First time login activate account
     * @return Template
     */
    public function activateacctAction()
    {
        $club = $this->container->get('club');
        $clubName = $club->get('title');

        return $this->render('CommonUtilityBundle:Resetting:activateaccount.html.twig', array("clubName" => $clubName, 'googleCaptchaSitekey' => $this->container->getParameter('googleCaptchaSitekey')));
    }
}
