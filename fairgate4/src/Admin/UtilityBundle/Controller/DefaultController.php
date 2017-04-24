<?php

namespace Admin\UtilityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('AdminUtilityBundle:Default:index.html.twig');
    }
}
