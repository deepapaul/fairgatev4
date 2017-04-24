<?php

namespace Common\UtilityBundle\Listener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Common\UtilityBundle\Util\FgContactSyncDataToAdmin;
use Common\UtilityBundle\Repository\Pdo\ContactPdo;

// The success handler is used to handle some basic functions that must do immediately after the login
class LoginSuccessHandler extends DefaultAuthenticationSuccessHandler
{

    /**
     * Container object.
     *
     * @var Object
     */
    protected $container;

    /**
     * Access map object.
     *
     * @var Object
     */
    protected $accessMap;

    /**
     * Application area variable.
     *
     * @var string
     */
    protected $applicationArea;

    public function __construct(ContainerInterface $container, HttpUtils $httpUtils, $accessMap, array $options)
    {
        parent::__construct($httpUtils, $options); // Common variables derived from default success handler

        $this->accessMap = $accessMap;
        $this->container = $container;
        $club = $container->get('club');
        $this->applicationArea = $club->get('applicationArea');  //internal/backend
        // The option array might be null when extending dafault handler. That is the default behaviour.
        // To override the option array, values taken from the parameter.yml file which is also used in the security.yml.
        //Handling option according to applicationArea
        if ($this->applicationArea === 'internal') {
            $this->options = array(
                'always_use_default_target_path' => false,
                'default_target_path' => $this->container->getParameter('internal_default_target_path'),
                'login_path' => $this->container->getParameter('internal_user_login_path'),
                'target_path_parameter' => '_target_path',
                'use_referer' => false,
            );
            // Setting the provider as used in the security.yml
            $this->providerKey = 'internal';
        } else {
            $this->options = array(
                'always_use_default_target_path' => false,
                'default_target_path' => $this->container->getParameter('fg_backend_default_target_path'),
                'login_path' => $this->container->getParameter('fg_backend_login_path'),
                'target_path_parameter' => '_target_path',
                'use_referer' => false,
            );
            // Setting the provider as used in the security.yml
            $this->providerKey = 'backend';
        }
    }

    /**
     * Executes immediately after user login.
     *
     * @param InteractiveLoginEvent $event
     *
     * @return URL
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();
        $request = $event->getRequest();

        $session = $this->container->get('session'); // Getting session object
        $em = $this->container->get('doctrine')->getManager(); // Getting entity manager object
        if ($user) { // Checking whether user is currently logged in or not
            $club = $this->container->get('club');
            $contactId = $em->getRepository('CommonUtilityBundle:SfGuardUser')->getContactDetails($user->getId());
            $em->getRepository('CommonUtilityBundle:SfGuardUser')->customLogoutTrigger($this->container, $session, $this->container->get('router')->generate('fairgate_user_security_logout'), $request, $user);
            $currentRoles = $user->getRoles();
            $this->getFgAdminUpdates(array('contact' => $contactId, 'currentRoles' => $currentRoles));
            $em->getRepository('CommonUtilityBundle:FgCmContact')->updateLoginCount($contactId['id']);
            $em->getRepository('CommonUtilityBundle:FgCmChangeLog')->changeLogLogin($this->container, $club->get('id'), $contactId['id'], $dateToday);

            //update club status to active when club's main contact loggin at first
            $this->getClubActivated($club, $contactId['id']);

            // Saving contact id and contact name into session
            $session->set('loggedClubUserId', $contactId['id']);
            $session->set('loggedContactName', $contactId['contactname']);
        }
    }

    /**
     * Executes immediately after user login.
     *
     * @param Request        $request Request object
     * @param TokenInterface $token   Token object
     *
     * @return URL
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        //when login from website ajax request
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(array('success' => true));
        }
        $defaultTargetPath = $this->determineTargetUrl($request);
        if ($defaultTargetPath == $this->container->getParameter('fg_backend_default_target_path')) {
            $request = new Request();
            $defaultPathName = $this->container->getParameter('fg_backend_default_target_path');
            $defaultTargetPath = $this->httpUtils->generateUri($request, $defaultPathName);
        }

        if ($this->applicationArea === 'backend') {  //case backend
            $hasAccess = $this->hasAccessInPath($defaultTargetPath, $request);
            if (!$hasAccess) { //case where cantact has no access in default url
                //get backend overview path
                $returnPath = $this->getBackendDefaultPath($request);
                if ($returnPath == '') { //case where cantact has no access in backend overview path
                    $returnPath = $this->getFrontendDefaultPath($request);
                }
                if ($returnPath == '') { //case where cantact has no access in internal overview path && backend overview path
                    //then the default url is shown with access denied message
                    $returnPath = $defaultTargetPath;
                }
                $defaultTargetPath = $returnPath;
            }
        }

        return $this->httpUtils->createRedirectResponse($request, $defaultTargetPath);
    }

    /**
     * Method to return internal-dashboard path, if the logged in contact have intranet access.
     * Otherwise return null string.
     *
     * @param object $request Request object
     *
     * @return string
     */
    private function getFrontendDefaultPath($request)
    {
        $returnPath = '';
        $intranetAccess = $this->getIntranetAccess();
        if ($intranetAccess == 1) {
            $returnPathName = $this->container->getParameter('internal_default_target_path');
            $returnPath = $this->httpUtils->generateUri($request, $returnPathName);
        }

        return $returnPath;
    }

    /**
     * Method to return backend-dashboard path, if the logged in contact have backend access
     * Otherwise return null string.
     *
     * @param object $request Request object
     *
     * @return string
     */
    private function getBackendDefaultPath($request)
    {
        $returnPath = '';
        $defaultPathName = $this->container->getParameter('fg_backend_default_target_path');
        $defaultPath = $this->httpUtils->generateUri($request, $defaultPathName);
        $hasBackendDefaultAccess = $this->hasAccessInPath($defaultPath, $request);
        if ($hasBackendDefaultAccess) {
            $returnPath = $defaultPath;
        }

        return $returnPath;
    }

    /**
     * Method to know if logged-in contact has access in a url path.
     *
     * @param string $path       Url path
     * @param object $requestObj Request object
     *
     * @return booelan
     */
    private function hasAccessInPath($path, $requestObj)
    {
        $request = $requestObj::create($path, 'GET');
        list($rolesForPath, $channel) = $this->accessMap->getPatterns($request); //get access_control for this request
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $currentRoles = $user->getRoles();
        $resultRoles = array_intersect($currentRoles, $rolesForPath);
        $return = (count($resultRoles) > 0) ? true : false;

        return $return;
    }

    /**
     * Method to know if logged-in contact has intranet access.
     *
     * @return booelan 0/1
     */
    private function getIntranetAccess()
    {
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $intranetAccess = $user->getContact()->getIntranetAccess();

        return $intranetAccess;
    }

    /**
     * Method to update FgCLUB changes
     *
     * @return booelan 0/1
     */
    private function getFgAdminUpdates($datas)
    {
        $contactId = $datas['contact']['id'];
        $currentRoles = $datas['currentRoles'];
        $club = $this->container->get('club');
        $adminRoles = array('ROLE_USERS', 'ROLE_SUPER', 'ROLE_FED_ADMIN', 'ROLE_GROUP_ADMIN', 'ROLE_CONTACT_ADMIN', 'ROLE_FORUM_ADMIN', 'ROLE_DOCUMENT_ADMIN', 'ROLE_CALENDAR_ADMIN', 'ROLE_CALENDAR', 'ROLE_GALLERY_ADMIN', 'ROLE_GALLERY', 'ROLE_ARTICLE_ADMIN', 'ROLE_ARTICLE', 'ROLE_CMS_ADMIN', 'ROLE_PAGE_ADMIN');
        if ((in_array('ROLE_FED_ADMIN', $currentRoles)) && ($club->get('type') != 'federation')) {
            $adminRoles = array_diff($adminRoles, array('ROLE_FED_ADMIN'));
        }
        $adminRights = array_intersect($adminRoles, $currentRoles);
        $syncData = new FgContactSyncDataToAdmin($this->container);
        if (count($adminRights) > 0) {
            $syncData->getLastAdminLoggedInUpdate($club->get('id'), $contactId)->executeQuery();
            // Need to update the login count and log entries
            $nowdate = strtotime(date('Y-m-d H:i:s'));
            $dateToday = date('Y-m-d H:i:s', $nowdate);
            $syncData->updateLastAdminLogged($club->get('id'), $dateToday)->executeQuery();
        }

        $isMainContact = $syncData->checkSolutionContact($contactId);
        if (count($isMainContact) > 0) {
            $syncData->getMainContactLoginCount($club->get('id'))->executeQuery();
        }
        if ($contactId != 1) {
            $syncData->getAllContactLoginCount($club->get('id'))->executeQuery();
        }

        return;
    }

    /**
     * Update club status to 'active' when club's main contact loggin at first, and send sailes notification mail
     * 
     * @param object $club      club service
     * @param int    $contactId contactId
     */
    private function getClubActivated($club, $contactId)
    {
        $adminEntityManager = $this->container->get('fg.admin.connection')->getAdminManager();
        $clubObj = $adminEntityManager->getRepository('AdminUtilityBundle:FgClub')->find($club->get('id'));
        $clubStatus = $clubObj->getStatus();
        $clubsMainContact = ($clubObj->getFairgateSolutionContact()) ? $clubObj->getFairgateSolutionContact()->getId() : null;
        if ($clubsMainContact == $contactId && $clubStatus == 'Confirmed') { //main contact loggin on first time
            $adminEntityManager->getRepository('AdminUtilityBundle:FgClub')->activateClub($clubObj);
            $this->sendSalesNotificationMail($adminEntityManager, $contactId);
        }
    }

    /**
     * Method to send sales  notification message on main-contact's first login
     * 
     * @param object $adminEntityManager admin EntityManager 
     * @param int    $contactId          contact Id
     */
    private function sendSalesNotificationMail($adminEntityManager, $contactId)
    {
        $urlIdentifier = $this->container->get('club')->get('clubUrlIdentifier');
        $contactObj = $adminEntityManager->getRepository('AdminUtilityBundle:FgCmContact')->find($contactId);
        $userName = $contactObj->getName();
        $userEmail = $contactObj->getEmail();
        $clubName = $this->container->get('club')->get('title');
        $dateTime = date('d.m.Y H:i');
        $templateParameters['notificationMessage'] = $this->container->get('translator')->trans('SALES_NOTIFICATION_MESSAGE_LOGIN', array('%userName%' => $userName, '%userEmail%' => $userEmail, '%clubName%' => $clubName, '%dateTime%' => $dateTime));
        $templateParameters['organisationDetailPagePath'] = $this->container->getParameter('organisationDetailPagePath');
        $templateParameters['backendPath'] = $this->container->get('router')->generate('fos_user_security_login', array('url_identifier' => $urlIdentifier), 0);
        $template = $this->container->get('templating')->render('AdminUtilityBundle:Register:salesNotificationMailTemplate.html.twig', $templateParameters);
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
}
