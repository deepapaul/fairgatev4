<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Internal\UserBundle\Controller;

use FOS\UserBundle\Controller\ResettingController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Common\UtilityBundle\Util\FgUtility;
use Website\CMSBundle\Util\FgWebsite;
use ReCaptcha\ReCaptcha;

/**
 * Controller managing the resetting of the password
 * Overridden from FOSUserBundle.
 *
 * @author pitsolutions.ch
 */
class ResettingController extends BaseController
{

    /**
     * Request reset user password: show form.
     */
    public function requestAction()
    {
        $session = $this->container->get('session');
        $loggedClubUserId = $session->get('loggedClubUserId');
        if (isset($loggedClubUserId)) {
            return $this->redirect($this->generateUrl('internal_dashboard'));
        }

        return $this->render('InternalUserBundle:Login:resetpasswordrequest.html.twig', array('googleCaptchaSitekey' => $this->container->getParameter('googleCaptchaSitekey')));
    }

    /**
     * Tell the user to check his email provider.
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
            return new RedirectResponse($this->container->get('router')->generate('internal_user_rest_password_request'));
        }
        $session = $this->container->get('session');
        $loggedClubUserId = $session->get('loggedClubUserId');

        if (isset($loggedClubUserId)) {
            return $this->redirect($this->generateUrl('internal_dashboard'));
        }
        $return = array();
        if ($activateaccount == 1) {
            $return['pageTitle'] = $this->get('translator')->trans('INTERNAL_LOGIN_ACTIVATE_ACCOUNT');
            $return['pageFormTitle'] = $this->get('translator')->trans('INTERNAL_LOGIN_ACTIVATE_ACCOUNT');
            $return['pageFormMsg'] = $this->get('translator')->trans('INTERNAL_LOGIN_ACTIVATE_ACCOUNT_CHECK');
        } else {
            $return['pageTitle'] = $this->get('translator')->trans('LOGIN_TITLE');
            $return['pageFormTitle'] = $this->get('translator')->trans('RESET_PASSWORD_PAGE_TITLE_NEW');
            $return['pageFormMsg'] = $this->get('translator')->trans('RESET_PASSWORD_EMAIL_SEND_SUCCESS');
        }

        return $this->container->get('templating')->renderResponse('InternalUserBundle:Login:checkEmail.html.twig', $return);
    }

    /**
     * Request reset user password: submit form and send email.
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
        $intranetAccess = '0';
        //Check a contact with requsted email exists in club
        $user = $this->container->get('fos_user.user_manager')->findUserByEmailClub($club->get('id'), $username);
        if (null === $user) {
            //Check a superadmin contact with requsted email exists
            $user = $this->get('fos_user.user_manager')->findUserByEmailClub(null, $username);
            if ($user && $user->getIsSuperAdmin() == 1) {
                $intranetAccess = '1';
            }
        } else {
            //Get intranet access flag of the conact to allow access
            $intranetAccess = $user->getContact()->getIntranetAccess();
        }

        //google captcha verification
        $isCaptchaValidated = 1;
        $secret = $this->container->getParameter('googleCaptchaSecretkey');
        $recaptcha = new ReCaptcha($secret);
        $resp = $recaptcha->verify($request->get('g-recaptcha-response'), $request->server->get('REMOTE_ADDR'));
        if (!$resp->isSuccess()) { //captch validation failed 
            $isCaptchaValidated = 0;
        }

        if ($isCaptchaValidated == 0) {
            $errorMesssage = 'CAPTCHA_VERIFICATION_FAILED';
        } else {
            $errorMesssage = ($intranetAccess == '0' && ($user)) ? 'INTERNAL_LOGIN_DENIED_ACCESS' : 'RESET_PASSWORD_INVALID_EMAIL';
        }
        //when login from website login (ajax request)
        if ((null === $user || $username == '' || $intranetAccess == '0' || $isCaptchaValidated == 0) && ($request->isXmlHttpRequest() )) {
            $errorTitle = ($activateaccount == 1) ? 'errorActivateAccount' : 'error';

            return new JsonResponse(array($errorTitle => $this->get('translator')->trans($errorMesssage)));
        } else if ($activateaccount == 1 && ((null === $user || $username == '' || $intranetAccess == '0' || $isCaptchaValidated == 0))) {
            $clubName = $club->get('title');

            return $this->container->get('templating')->renderResponse('InternalUserBundle:Login:activateaccount.html.twig', array('error' => $errorMesssage, 'clubName' => $clubName, 'googleCaptchaSitekey' => $this->container->getParameter('googleCaptchaSitekey')));
        } else if (null === $user || $username == '' || $intranetAccess == '0' || $isCaptchaValidated == 0) {

            return $this->container->get('templating')->renderResponse('InternalUserBundle:Login:resetpasswordrequest.html.twig', array('error' => $errorMesssage, 'googleCaptchaSitekey' => $this->container->getParameter('googleCaptchaSitekey')));
        }

        if ($user->isPasswordRequestNonExpired($this->container->getParameter('fos_user.resetting.token_ttl'))) {
            if ($activateaccount == 1) {
                $return['pageTitle'] = $this->get('translator')->trans('INTERNAL_LOGIN_ACTIVATE_ACCOUNT');
                $return['pageFormTitle'] = $this->get('translator')->trans('INTERNAL_LOGIN_ACTIVATE_ACCOUNT');
                $return['pageFormMsg'] = $this->get('translator')->trans('ALREADY_REQUESTED_EMAIL');
            } else {
                $return['pageTitle'] = $this->get('translator')->trans('LOGIN_TITLE');
                $return['pageFormTitle'] = $this->get('translator')->trans('RESET_PASSWORD_PAGE_TITLE_NEW');
                $return['pageFormMsg'] = $this->get('translator')->trans('ALREADY_REQUESTED_EMAIL');
            }
            //when login from website login (ajax request)
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(array('passwordAlreadyRequested' => 'passwordAlreadyRequested', 'messages' => $return));
            }

            return $this->render('InternalUserBundle:Login:passwordAlreadyRequested.html.twig', $return);
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
            $resetPasswordTemplate = 'InternalUserBundle:Resetting:activateAccountTemplate.html.twig';
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
        $logoPath = FgUtility::getUploadFilePath($clubObj->get('id'), 'clublogo', false, $clubLogo);
        if ($clubLogo == '' || !file_exists($rootPath . '/' . $logoPath)) {
            $clubLogoUrl = '';
        } else {
            $clubLogoUrl = $baseurl . '/' . $logoPath;
        }
        //when login from website login (ajax request)
        $resetPasswordRouting = ($request->isXmlHttpRequest() ) ? 'website_reset_password' : 'internal_user_resetting_reset';
        $this->get('fos_user.mailer')->setParams(array('resetPasswordRouting' => $resetPasswordRouting, 'resetPasswordTemplate' => $resetPasswordTemplate, 'clubTitle' => $clubTitle, 'salutation' => $salutation, 'clubLogoUrl' => $clubLogoUrl, 'subject' => $subject, 'container' => $this->container));
        $this->get('fos_user.mailer')->sendResettingEmailMessage($user);
        $user->setPasswordRequestedAt(new \DateTime());
        $this->container->get('fos_user.user_manager')->updateUser($user);
        //when login from website login (ajax request)
        if ($request->isXmlHttpRequest()) {
            if ($activateaccount == 1) {
                $returnMsg['pageFormTitle'] = $this->get('translator')->trans('INTERNAL_LOGIN_ACTIVATE_ACCOUNT');
                $returnMsg['pageFormMsg'] = $this->get('translator')->trans('INTERNAL_LOGIN_ACTIVATE_ACCOUNT_CHECK');
            } else {
                $returnMsg['pageFormTitle'] = $this->get('translator')->trans('RESET_PASSWORD_PAGE_TITLE_NEW');
                $returnMsg['pageFormMsg'] = $this->get('translator')->trans('RESET_PASSWORD_EMAIL_SEND_SUCCESS');
            }
            return new JsonResponse(array('emailSendSuccess' => 'success', 'messages' => $returnMsg));
        }

        return new RedirectResponse($this->generateUrl('internal_user_checkmail', array('username' => $username, 'activateaccount' => $activateaccount)));
    }

    /**
     * Change password template.
     *
     * @return HTML
     */
    public function changePasswordAction()
    {
        return $this->container->get('templating')->renderResponse('InternalUserBundle:Resetting:changePassword.html.twig');
    }

    /**
     * Function to validate and update changed password of a contact.
     *
     * @param Request $request Request object
     *
     * @return JsonResponse
     */
    public function updatePasswordAction(Request $request)
    {
        $currentTypedPassword = $request->get('currentpassword');
        $newPassword = $request->get('newpassword');
        $retypedNewPassword = $request->get('repeatpassword');

        if ($this->container->get('security.token_storage')->getToken()->getUser()) {
            //get current user object from context
            $user = $this->container->get('security.token_storage')->getToken()->getUser();
            $currentSavedPasswordEncoded = $user->getPassword();

            $encoderFactory = $this->get('security.encoder_factory');
            $encoder = $encoderFactory->getEncoder($user);
            $currentTypedPasswordEncoded = $encoder->encodePassword($currentTypedPassword, $user->getSalt());

            //if saved current password and typed current password are same then have privilage to change password
            if ($currentTypedPasswordEncoded == $currentSavedPasswordEncoded) {
                if ($currentTypedPassword != $newPassword) {
                    if ($newPassword == $retypedNewPassword) {
                        $this->em = $this->getDoctrine()->getManager();
                        $this->conn = $this->container->get('database_connection');
                        //update password in db
                        $user->setPlainPassword($newPassword);
                        $this->em->flush();
                        $userManager = $this->get('fos_user.user_manager');
                        $userManager->updatePassword($user);
                        // insert corresponding log entries
                        $club = $this->container->get('club');
                        $clubId = $club->get('id');
                        $contactId = $user->getContact()->getId();
                        $this->em->getRepository('CommonUtilityBundle:FgCmChangeLog')->passwordLogEntry($this->container, $clubId, $contactId, date('Y-m-d H:i:s'), 'Changed');
                        //update password for users with federation membership
                        $this->em->getRepository('CommonUtilityBundle:SfGuardUser')->alterPasswordByContactId($this->conn, $user->getId());

                        return new JsonResponse(array('status' => 'SUCCESS', 'noparentload' => true, 'flash' => $this->get('translator')->trans('PASSWORD_UPDATE_SUCCESS_MSG')));
                    } else {
                        $errorArray = array('newpassword' => '', 'repeatpassword' => $this->get('translator')->trans('PASSWORDS_DONT_MATCH_MSG'));
                    }
                } else {
                    $errorArray = array('newpassword' => '', 'repeatpassword' => $this->get('translator')->trans('PASSWORDS_ARE_SAME_MSG'));
                }
            } else {
                $errorArray = array('currentpassword' => $this->get('translator')->trans('CURRENT_PASSWORD_INCORRECT_MSG'));
            }

            return new JsonResponse(array('errorArray' => $errorArray));
        }
    }

    /**
     * Reset user password.
     *
     * @param string $token           Password token
     * @param string $applicationArea internal/website
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
        $applicationArea = $request->get('applicationArea');
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
            $this->em->getRepository('CommonUtilityBundle:FgCmContact')->updateLoginCount($contactId);
            $this->em->getRepository('CommonUtilityBundle:FgCmChangeLog')->changeLogLogin($this->container, $club->get('id'), $contactId, $dateToday);
            $session = $this->container->get('session');
            $session->set('loggedClubUserId', $contactId);
            $this->em->getRepository('CommonUtilityBundle:SfGuardUser')->alterPasswordByContactId($this->conn, $user->getId());
            if (null === $response = $event->getResponse()) {
                $url = ($applicationArea == 'website') ? $this->generateUrl('website_public_home_page') : $this->generateUrl('internal_dashboard');
                $response = new RedirectResponse($url);
            }

            $dispatcher->dispatch(FOSUserEvents::RESETTING_RESET_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

            return $response;
        }

        $retArray = array('token' => $token, 'form' => $form->createView());
        if ($applicationArea == 'website') {
            $websiteObj = new FgWebsite($this->container);
            $retArray['pagetitle'] = $this->container->get('translator')->trans('RESET_PASSWORD_PAGE_TITLE_NEW');
            $returnArray = $websiteObj->getParametesForWebsiteLayout($retArray);

            return $this->render('WebsiteCMSBundle:Website:websiteResetPassword.html.twig', $returnArray);
        } else {
            return $this->render('InternalUserBundle:Login:passwordreset.html.twig', $retArray);
        }
    }

    /**
     * First time login activate account.
     *
     * @return Template
     */
    public function activateaccountAction()
    {
        $club = $this->container->get('club');
        $clubName = $club->get('title');

        return $this->render('InternalUserBundle:Login:activateaccount.html.twig', array('clubName' => $clubName, 'googleCaptchaSitekey' => $this->container->getParameter('googleCaptchaSitekey')));
    }

    /**
     * Function to accomplish switch user functionality.
     *
     * @param Request $request Request object
     */
    public function switchUserAction(Request $request)
    {
        $switchableUsers = $this->getSwitchableUsers();

        $switchContactId = $request->get('contactId');
        //If switch contact Id is not in switchable users, throw exception
        if (!in_array($switchContactId, $switchableUsers)) {
            throw new AccessDeniedException();
        }
        $club = $this->container->get('club');
        $clubId = $club->get('id');
        $parentContactId = $this->container->get('contact')->get('id');
        $parentName = $this->container->get('contact')->get('name');
        $this->em = $this->getDoctrine()->getManager();
        $session = $this->container->get('session');
        if ($switchContactId) {
            $userObj = $this->em->getRepository('CommonUtilityBundle:SfGuardUser')->findOneBy(array('contact' => $switchContactId, 'club' => $clubId));
            if ($userObj) {
                //create token instance
                //2nd argument is password, but empty string is accepted
                //3rd argument is "firewall" name(be careful, not a "provider" name!!! though UsernamePasswordToken.php names it as "providerKey")
                $token = new UsernamePasswordToken($userObj, null, 'internal', $userObj->getRoles());
                //set token instance to security context
                $this->container->get('security.token_storage')->setToken($token);
                $this->get('session')->set('_security_internal', serialize($token));

                //fire a login event
                $event = new InteractiveLoginEvent($request, $token);
                $this->get('event_dispatcher')->dispatch('security.interactive_login', $event);
            }
            if (!$session->has('parentId')) {
                $session->set('parentId', $parentContactId);
                $session->set('parentName', $parentName);
            }
            //fallback
            return $this->redirect($this->generateUrl('internal_dashboard'));
        }
    }

    /**
     * Method to get array of switcable users of the logged contact.
     *
     * @return array $switchableUsers
     */
    private function getSwitchableUsers()
    {
        $switchableUsers = array();
        $contactId = $this->container->get('contact')->get('id');
        $isCompany = $this->container->get('contact')->get('isCompany');
        //change user possibility only for single person or if parent is logged in
        $session = $this->container->get('session');
        $em = $this->getDoctrine()->getManager();
        if (($isCompany == 0) || ($session->has('parentId'))) {
            $parentContactId = ($session->has('parentId')) ? $session->get('parentId') : $contactId;
            $childRelations = $em->getRepository('CommonUtilityBundle:FgCmLinkedcontact')->getChildrensHavingProfileAccessForParents($parentContactId, $contactId, $this->container);
            foreach ($childRelations as $childRelation) {
                if ($childRelation['id'] != $contactId) {
                    $switchableUsers[] = $childRelation['id'];
                }
            }

            //companies for which this contact is the maincontact
            $myCompanies = $em->getRepository('CommonUtilityBundle:FgCmContact')->getCompaniesOfAContact($parentContactId, $this->container);
            foreach ($myCompanies as $myCompany) {
                if ($myCompany['id'] != $contactId) {
                    $switchableUsers[] = $myCompany['id'];
                }
            }
            if (($session->get('parentId')) && ($session->get('parentId') != $contactId)) {
                $switchableUsers[] = $session->get('parentId');
            }
        }

        return $switchableUsers;
    }
}
