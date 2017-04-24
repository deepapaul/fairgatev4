<?php

namespace Clubadmin\SponsorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('ClubadminSponsorBundle:Default:index.html.twig', array('name' => $name));
    }
}
