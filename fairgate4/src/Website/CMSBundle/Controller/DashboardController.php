<?php

/**
 * DashboardController.
 * 
 */
namespace Website\CMSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * DashboardController
 *
 * This controller is used for displaying the website dashboard page
 * 
 * @package 	Website
 * @subpackage 	CMSBundle
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 */
class DashboardController extends Controller
{

    /**
     * This function is used to display index.
     * 
     * @return Object View Template Render Object
     */
    public function indexAction()
    {
        return $this->redirect($this->generateUrl('website_public_home_page'));
    }
}
