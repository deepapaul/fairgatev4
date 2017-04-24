<?php

/**
 * SitemapsController.
 *
 * @package 	Website
 * @subpackage 	CMSBundle
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 *
 */
namespace Website\CMSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SitemapsController
 *
 * @property  container
 * @package Website\CMSBundle\Controller
 *
 */
class SitemapsController extends Controller
{

    /**
     * This function is used to dynamically render the public navigation urls in website 
     * as a site map which will only be available in main domains
     * 
     * @return Response object The view template
     */
    public function sitemapDisplayAction()
    {
        $isDomain = $this->checkIsDomain();
        if ($isDomain) {
            $navigationDetails = $this->container->get('club')->get('navigationHeirarchy');
            $publicUrls = (isset($navigationDetails['publicPages'])) ? $navigationDetails['publicPages'] : [];
            if (count($publicUrls) > 0) {
                $baseUrl = 'http://' . $this->container->get('request_stack')->getCurrentRequest()->getHttpHost();
                $returnArray = ['urls' => $publicUrls, 'host' => $baseUrl];
                $rendered = $this->renderView('WebsiteCMSBundle:Sitemaps:sitemap.xml.twig', $returnArray);
                $response = new Response($rendered);
                $response->headers->set('Content-Type', 'xml');

                return $response;
            }
        }

        return $this->render('WebsiteCMSBundle:Website:errorPagePreview.html.twig');
    }

    /**
     * This function is used to ensure that the 'sitemap' routing is accessed only in domain environments
     * 
     * @return int O/1 Whether currently in domain environment or not
     */
    private function checkIsDomain()
    {
        $env = $this->container->getParameter("kernel.environment");

        return ($env == 'domain') ? 1 : 0;
    }
}
