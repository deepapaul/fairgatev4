<?php

/**
 * ContactTableController.
 */
namespace Website\CMSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * NewsletterArchiveController.
 *
 * This controller is used for handle Newsletter Archive data  *
 * @package 	Website
 * @subpackage 	CMSBundle
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 *
 */
class NewsletterArchiveController extends Controller
{

    /**
     * get initial data for newsletter archive element
     *
     * @param integer $elementId id of element
     * 
     * @return JsonResponse $returnData The initial data for listing
     */
    public function getTableInitialDataAction($elementId)
    {
        $returnData = array();
        $elementObj = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->find($elementId);
        if ($elementObj != '') {
            $returnData = array('columnData' => array('date', 'title'), 'elementType' => 'newsletter-archive');
        }

        return new JsonResponse($returnData);
    }

    /**
     * Function to get all archived newsletter data to display in website
     *
     * @return JsonResponse $output The data for listing
     */
    public function listnewsletterAction()
    {
        $club = $this->container->get('club');
        $em = $this->getDoctrine()->getManager();
        //For set the datatable json array
        $newsletterData = $em->getRepository('CommonUtilityBundle:FgCnNewsletter')->getNewsletterForWebsiteArchive($club->get('id'));
        $output = array('iTotalRecords' => count($newsletterData), 'iTotalDisplayRecords' => count($newsletterData), 'aaData' => $newsletterData, 'elementType' => 'newsletter-archive');

        return new JsonResponse($output);
    }

    /**
     * Function to show newsletter preview
     * 
     * @param Request $request The request object
     * 
     * @Template("ClubadminCommunicationBundle:Preview:newsletter-preview.html.twig")
     */
    public function newsletterPreviewAction(Request $request)
    {
        $templateId = 0;
        $newsletterId = $request->get('newsletterid', '');
        $mode = $request->get('mode', 'designpreview');
//      since default salutation for subscriber from settings page is used pass contact id as 0
        $contactId = 0;
        $clubId = $this->container->get('club')->get('id');
        $contactLang = $this->container->get('club')->get('default_lang');

        return $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCnNewsletterContent')->getNewsletterContentDetails($this->container, $clubId, $newsletterId, $templateId, $mode, $contactId, $contactLang);
    }
}
