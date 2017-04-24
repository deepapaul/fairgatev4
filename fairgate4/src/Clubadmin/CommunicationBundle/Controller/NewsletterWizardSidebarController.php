<?php

namespace Clubadmin\CommunicationBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Common\UtilityBundle\Repository\Pdo\SponsorPdo;

/**
 * NewsletterWizardSidebarController
 *
 * Controller for handling wizard Sidebar
 *
 * @package    ClubadminCommunicationBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
class NewsletterWizardSidebarController extends FgController {

    /**
     * This action is used for create newsletter step6.
     *
     * @Template("ClubadminCommunicationBundle:Newsletterwizard:newsletterSidebar.html.twig")
     *
     * @return array Data array.
     */
    public function indexAction($newsletterId) {
        $club = $this->get('club');
        $bookedModuleDetails = $club->get('bookedModulesDet');
        $backUrl = $this->generateUrl('newsletter_step_content', array('newsletterId' => $newsletterId));
        $wizardStep = 4;
        $breadCrumb = array(
            'breadcrumb_data' => array(),
            'back' => $backUrl
        );
        $objSponsorPdo = new SponsorPdo($this->container);
        $sponsorServices = $objSponsorPdo->getSponsorsServices($this->clubId, $this->clubDefaultLang);
        $sponsorAdAreas = $this->getAdAreasDetails();

        return array('status' => '',
            'pageType' => '', 'step' => 4, 'newsletterId' => $newsletterId,
            'backUrl' => $backUrl,
            'bookedModule' => $bookedModuleDetails, 'breadCrumb' => $breadCrumb, 'wizardStep' => $wizardStep, 
            "sponsorServices" => $sponsorServices, "sponsorAdAreas" => $sponsorAdAreas);
    }
    
     /**
     * Method to get Ad areas details
     * @return array $adAreas
     */
    private function getAdAreasDetails() {
        $adAreas = $this->em->getRepository('CommonUtilityBundle:FgSmAdArea')->getAdAreas($this->clubId); 
        if(count($adAreas) > 0) {
            for($i = 0; $i < count($adAreas); $i++) {
               $adAreas[$i]['adTitle'] = $adAreas[$i]['isSystem'] == 1 ? $this->get('translator')->trans('SM_AD_AREA_GENERAL') : $adAreas[$i]['adTitle']; 
            }
        }
        
        return $adAreas;
    }

    /**
     * Save sidebar sidebar content
     * @param int $newsletterId     newsletterId 
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function saveSidebarAction(Request $request, $newsletterId) {
        $type = $request->get('level1');
        if ($request->getMethod() == 'POST') {
            $data['newsletterId'] = $newsletterId;
            $showNext = $request->get('showNext');
            $data['clubId'] = $this->clubId;            
            $dataArray = json_decode($request->get('catArr'), true);              
            $this->em->getRepository('CommonUtilityBundle:FgCnNewsletterSidebar')->saveSidebarContent($dataArray, $data, $this->container);
            $result = array('status' => true);
            if ($showNext == 'true') {
                $result['redirect'] = $this->generateUrl('nl_design', array('newsletterId' => $data['newsletterId']));
            } else {
                $result['redirect'] = ($type == 'newsletter') ? $this->generateUrl('nl_step_sidebar', array('newsletterId' => $data['newsletterId'])) : $this->generateUrl('simplemail_step_content', array('newsletterId' => $data['newsletterId']));
            }
            $result['sync'] = 1;
            $result['flash'] = $this->get('translator')->trans('NEWSLETTER_WIZARD_SAVED');
        }
        
        return new JsonResponse($result);
    }
    
    /**
     * Function to get the newsletter contents
     *
     * @param int $newsletterId     newsletterId 
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     */
    public function getSidebarContentAction($newsletterId) {
        if ($newsletterId != '') {
            $newsletter = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletter')->getNewsletterSidebarContents($this->clubId, $newsletterId, true);   
            $club = $this->get('club');            
            $newsletter = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletter')->newsletterSidebarPreviewArrayBulider($newsletter, $this->container, $this->clubId, $club);            
            $newsletter[0]['text'] = ($newsletter[0]['text'] == 'NL_PERSONAL_SALUTATION') ? $this->get('translator')->trans('NL_PERSONAL_SALUTATION') : $newsletter[0]['text'];
        }

        return new JsonResponse($newsletter);
    }
    
    
}
