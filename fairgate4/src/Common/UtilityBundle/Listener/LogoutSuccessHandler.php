<?php

namespace Common\UtilityBundle\Listener;

use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;


class LogoutSuccessHandler implements LogoutSuccessHandlerInterface
{
    use ContainerAwareTrait;

    /**
     * Service Container
     * @var Obeject
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onLogoutSuccess(Request $request)
    {
        $clubId = $this->container->get('club')->get('id');
        $contactId = $request->getSession()->get('loggedClubUserId');

        $request->getSession()->remove('windowVisibility_'.$clubId.'_'.$contactId);
        $request->getSession()->remove('loggedClubUserId');
        $request->getSession()->remove('superAdminFlag');
        $request->getSession()->remove('HasFullPermission');
        $request->getSession()->remove('sfGuardId');
        $request->getSession()->remove('parentId');
        $request->getSession()->remove('parentName');
        $club = $this->container->get('club');
        $applicationArea = $club->get("applicationArea");  //internal/backend

        //For Ajax Requests : return 403 response(On getting 403 response from an ajax page, main page page will be refreshed - handled in internal/backend.js/fg-website.js)
        if ($request->isXmlHttpRequest()) {
            $applicationArea = $this->container->get('club')->get('applicationArea');
            return new Response('', 403);
        }

        //Handling option according to applicationArea
        $loginPath = ($applicationArea === "internal") ? "internal_user_login" : "fos_user_security_login";

        return new RedirectResponse($this->container->get('router')->generate($loginPath));
    }
}