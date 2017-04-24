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

use Symfony\Component\Security\Core\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * SecurityController
 *
 * This SecurityController was created to habdle user login
 *
 * @package    CommonUtilityBundle
 * @subpackage Form
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
class SecurityController extends Controller
{
    /**
     * User login section
     *
     * @return HTML
     */
    public function loginAction(Request $request)
    { 
        $club=$this->container->get('club');
        $this->container->get('translator')->setLocale($club->get('default_system_lang'));
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $session = $this->container->get('session');
        $logoutPath=$this->container->get('router')->generate('fairgate_user_security_logout');
        $this->em = $this->getDoctrine()->getManager();
        //$this->em->getRepository('CommonUtilityBundle:SfGuardUser')->customLogoutTrigger($this->container, $session, $logoutPath, $request);
        $loggedClubUserId=$session->get('loggedClubUserId');
        if (isset($loggedClubUserId)) {
        
            return $this->redirect($this->generateUrl('contact_index'));
        }
        /** @var $session \Symfony\Component\HttpFoundation\Session\Session */
        $session = $request->getSession();

        // get the error if any (works with forward and redirect -- see below)
        if ($request->attributes->has(Security::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(Security::AUTHENTICATION_ERROR);
        } elseif (null !== $session && $session->has(Security::AUTHENTICATION_ERROR)) {
            $error = $session->get(Security::AUTHENTICATION_ERROR);
            $session->remove(Security::AUTHENTICATION_ERROR);
        } else {
            $error = null;
        }

        if (!$error instanceof AuthenticationException) {
            $error = null; // The value does not come from the security component.
        }
        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get(Security::LAST_USERNAME);

        if ($lastUsername=='' && $error ) {
            $error= $this->get('translator') ->trans('LOGIN_ENTER_USERNAME_PASSWORD_ERROR');
        } else if ($lastUsername !='' && $error ) {
            if($error->getMessage() == "HAS_NO_INTRANET_ACCESS") {
                $error = $this->get('translator') ->trans('INTERNAL_LOGIN_DENIED_ACCESS');
            } else if($error->getMessage() == "LOGIN_ACCOUNT_NOT_ACTIVATED") {
                $error = $this->get('translator') ->trans('LOGIN_ACCOUNT_NOT_ACTIVATED');
            } else {
                $error = $this->get('translator') ->trans('LOGIN_INVALID_USERNAME_PASSWORD_ERROR');
            }   
        }
        $csrfToken = $this->has('security.csrf.token_manager')
            ? $this->get('security.csrf.token_manager')->getToken('authenticate')->getValue()
            : null;
        $clubTitle = $this->container->get('club')->get('title');
        $regForms = $this->em->getRepository('CommonUtilityBundle:FgCmsForms')->getContactApplicationFormList($club->get('id'), $club->get('club_default_lang'), 1);
        $regFormId = '';
        $clubMembershipAvailable = $this->container->get('club')->get('clubMembershipAvailable');
        $clubType = $this->container->get('club')->get('type');
        if (count($regForms) > 0 && $clubMembershipAvailable && ($clubType != 'federation' || $clubType != 'sub_federation')) {
            $regFormId = base64_encode($regForms[0]['id']);
        }

        return $this->renderLogin(array(
                    'last_username' => $lastUsername,
                    'error' => $error,
                    'csrf_token' => $csrfToken,
                    'clubTitle' => $clubTitle,
                    'regFormId' => $regFormId
        ));
    }

    /**
     * Renders the login template with the given parameters. Overwrite this function in
     * an extended controller to provide additional data for the login template.
     *
     * @param array $data
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function renderLogin(array $data)
    {
        $template = sprintf('CommonUtilityBundle:Security:login.html.%s', 'twig');

        return $this->container->get('templating')->renderResponse($template, $data);
    }

    /**
     * Check user
     *
     */
    public function checkAction()
    {
        throw new \RuntimeException('You must configure the check path to be handled by the firewall using form_login in your security firewall configuration.');
    }

    /**
     * Logout page
     *
     */
    public function logoutAction()
    {
        throw new \RuntimeException('You must activate the logout in your security firewall configuration.');
    }
}
