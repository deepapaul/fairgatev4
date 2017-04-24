<?php
namespace Internal\UserBundle\Controller;
/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use FOS\UserBundle\Controller\SecurityController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * SecurityController
 *
 * This SecurityController was created to habdle user login
 *
 * @package    InternalUserBundle
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */

class SecurityController extends BaseController
{
    /**
     * User login section. Overrided function from FOSUserBundle
     *
     * @param Request $request
     *
     * @return HTML
     */
    public function loginAction(Request $request)
    {
        $session = $this->container->get('session');
        $loggedClubUserId=$session->get('loggedClubUserId');
        if (isset($loggedClubUserId)) {
            return $this->redirect($this->generateUrl('internal_dashboard'));
        }

        $response = parent::loginAction($request);

        return $response;
    }

    /**
     * Renders the login template with the given parameters.
     * Overrided function from FOSUserBundle
     *
     * @param array $data
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function renderLogin(array $data)
    {
        if ($data['last_username']=='' && $data['error'] ) {
            $data['error']= $this->get('translator') ->trans('LOGIN_ENTER_USERNAME_PASSWORD_ERROR');
        } else if ($data['last_username'] !='' && $data['error'] ) {
            if($data['error']->getMessage() == "HAS_NO_INTRANET_ACCESS") {
                $data['error'] = $this->get('translator') ->trans('INTERNAL_LOGIN_DENIED_ACCESS');
            } else if($data['error']->getMessage() == "LOGIN_ACCOUNT_NOT_ACTIVATED") {
                $data['error'] = $this->get('translator') ->trans('LOGIN_ACCOUNT_NOT_ACTIVATED');
            } else {
                $data['error'] = $this->get('translator') ->trans('LOGIN_INVALID_USERNAME_PASSWORD_ERROR');
            }
        }
         //set locate
        $club=$this->container->get('club');
        $this->container->get('translator')->setLocale($club->get('default_system_lang'));
        $requestStack = $this->container->get('request_stack');
        $request = $requestStack->getCurrentRequest();
        $data['clubName'] = $club->get('title');
        //checking 'frontend1' module is booked
        $data['hasInternal'] = in_array("frontend1", $club->get('bookedModulesDet') ) ? true : false;

        $data['clubTitle'] = $this->container->get('club')->get('title');
        $regForms = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsForms')->getContactApplicationFormList($club->get('id'), $club->get('club_default_lang'), 1);
        $regFormId = '';
        $clubMembershipAvailable =  $this->container->get('club')->get('clubMembershipAvailable');
        $clubType = $this->container->get('club')->get('type');
        if (count($regForms) > 0 && $clubMembershipAvailable && ($clubType !='federation' || $clubType !='sub_federation')) {
            $regFormId = base64_encode($regForms[0]['id']);
        }
        $data['regFormId'] = $regFormId;
        //when login from website login (ajax request)
        if ($request->isXmlHttpRequest() && $data['error']) {
            return new JsonResponse(array('error' => $data['error']));
        }
        return $this->render('InternalUserBundle:Login:Login.html.twig', $data);
    }

}
