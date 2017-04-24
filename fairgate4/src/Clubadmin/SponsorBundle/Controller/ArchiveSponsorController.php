<?php

/**
 * Archive Sponsor Controller.
 *
 * This controller was created for handling archived sponsors details.
 *
 * @author     pitsolutions.ch
 *
 * @version    Fairgate V4
 */
namespace Clubadmin\SponsorBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Common\UtilityBundle\Util\FgUtility;
use Symfony\Component\HttpFoundation\Response;

class ArchiveSponsorController extends FgController{
   /**
    * To list the archived sponsor data
    * @return template
    */    
    public function viewarchivedSponsorAction() {
        $breadCrumb = array('breadcrumb_data' => array());
        $allTableSettings = $this->em->getRepository('CommonUtilityBundle:FgTableSettings')->getAllTableSettings($this->clubId, $this->contactId, 'ARCHIVEDSPONSOR');
        $editUrl = $this->generateUrl('edit_contact', array('contact' => 'dummy'));
        $container = $this->container;
        $defaultSettings = $container->getParameter('default_sponsor_table_settings');
        $fiscalYear  = $this->container->get('club')->getFiscalYear();
        $invAddrFieldIds = array();
        $corrAddrFieldIds = array();
        $contactFields = $this->get('club')->get('contactFields');
        $corrAddrCatId = $container->getParameter('system_category_address');
        $invAddrCatId = $container->getParameter('system_category_invoice');
        $this->get('club')->set('moduleMenu', 'archivedsponsor');
        $this->session->set('contactType','archivedsponsor');
        foreach ($contactFields as $contactField) {
            if ($contactField['catId'] == $corrAddrCatId) {
                $corrAddrFieldIds[] = $contactField['id'];
            } elseif ($contactField['catId'] == $invAddrCatId) {
                $invAddrFieldIds[] = $contactField['id'];
            }
        }
        //collect federation id of a club
        $clubHeirarchy = $this->container->get('club')->get('clubHeirarchy');
        $federationClubId = (count($clubHeirarchy) > 0) ? $clubHeirarchy[0] : $this->clubId;
        return $this->render('ClubadminSponsorBundle:ArchivedSponsor:archivedsponsorList.html.twig', array('breadCrumb' => $breadCrumb, 'clubId' => $this->clubId, 'contactId' => $this->contactId, 'allTableSettings' => $allTableSettings, 'defaultSettings' => $defaultSettings, 'editUrl' => $editUrl, 'contacttype' => 'archivedsponsor', 'urlIdentifier' => $this->clubUrlIdentifier, 'clubType' => $this->clubType, 'corrAddrFieldIds' => $corrAddrFieldIds, 'invAddrFieldIds' => $invAddrFieldIds,'fiscalYear' => $fiscalYear, 'fedClubId' => $federationClubId));
    }
}
