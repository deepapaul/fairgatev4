<?php

namespace Common\UtilityBundle\Listener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

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

            // Need to update the login count and log entries
            $nowdate = strtotime(date('Y-m-d H:i:s'));
            $dateToday = date('Y-m-d H:i:s', $nowdate);
            $em->getRepository('CommonUtilityBundle:FgCmContact')->updateLoginCount($contactId['id']);
            $em->getRepository('CommonUtilityBundle:FgCmChangeLog')->changeLogLogin($this->container, $club->get('id'), $contactId['id'], $dateToday);

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
            return new JsonResponse(array('success' => true ));
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
        list($rolesForPath, $channel) = $this->accessMap->getPatterns($request);//get access_control for this request
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
}
