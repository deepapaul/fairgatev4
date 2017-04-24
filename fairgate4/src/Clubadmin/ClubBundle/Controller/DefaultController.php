<?php

namespace Clubadmin\ClubBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{    /**
     * Function Index action
     *
     * @return Type
     */
    public function indexAction($name)
    {
        return $this->render('ClubadminClubBundle:Default:index.html.twig', array('name' => $name));
    }
}
