<?php
/**
 * DefaultController
 *
 * This controller is used to handle defaults functionalities
 *
 * @package    CommonUtilityBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
namespace Common\UtilityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Common\UtilityBundle\Controller\FgController;

class DefaultController extends FgController
{
    /**
    * This function is used to render the default template
    *
    * @return HTML
    */
    public function indexAction()
    {
        return $this->render('CommonUtilityBundle:Default:index.html.twig');
    }

    /**
     * Function to declare javascript variables to be included in layout
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getDynamicJSDataAction()
    {
        $forbiddenFiletypes = $this->container->getParameter('forbiddenFiletypes');
        $rendered = $this->renderView('CommonUtilityBundle:Default:fgDynamicJSData.js.twig', array('forbiddenFiletypes' => implode(',', $forbiddenFiletypes)));
        $response = new \Symfony\Component\HttpFoundation\Response($rendered);
        $response->headers->set( 'Content-Type', 'application/x-javascript');
        $response->setPublic();
        $cacheLifeTime = $this->container->getParameter('cache_lifetime');
        $response->setMaxAge($cacheLifeTime);

        return $response;
    }
}
