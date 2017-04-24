<?php

/**
 * SponsorAdsController.
 *
 * This controller was created for handling sponsor ads.
 *
 * @author     pitsolutions.ch
 *
 * @version    Fairgate V4
 */
namespace Clubadmin\SponsorBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Clubadmin\ContactBundle\Util\ContactDetailsAccess;
use Clubadmin\Util\Contactlist;
use Clubadmin\SponsorBundle\Util\NextpreviousSponsor;
use Clubadmin\Classes\FgFileUploadHandler;
use Common\UtilityBundle\Util\FgUtility;
use Common\FilemanagerBundle\Util\FileChecking;

/**
 * SponsorAds controller is used for handling (listing, creating, editing, deleting) sponsor ads.
 *
 * @author PITSolutions <pit@pitsolutions.com>
 */
class SponsorAdsController extends FgController
{

    /**
     * This function is used to display Sponsor ads.
     *
     * @param int $offset  Offset
     * @param int $contact Contact id
     *
     * @Template("ClubadminSponsorBundle:SponsorAds:index.html.twig")
     *
     * @return array $return Array of data passed to template.
     */
    public function indexAction(Request $request, $offset, $contact)
    {
        if (!(in_array('communication', $this->bookedModulesDet) || in_array('frontend1', $this->bookedModulesDet))) {
            throw $this->createNotFoundException($this->get('translator')->trans('%CLUBNAME%_HAVE_NO_ACCESS_TO_PAGE', array('%CLUBNAME%' => $this->clubTitle)));
        }
        $this->session->set('contactType', 'sponsor');
        $pagetype = $request->get('level1') == 'sponsor' ? 'sponsor' : 'contact';
        $accessObj = new ContactDetailsAccess($contact, $this->container, 'sponsor');
        $allowedMod = $this->get('contact')->get('allowedModules');
        $isReadOnly = in_array('sponsor', $allowedMod) ? false : true;
        $isArchiveSponsor = false;
        $contactType = $accessObj->contactviewType;
        //set menu module
        if ($accessObj->menuType == 'archive' && $pagetype == 'sponsor') {
            $this->get('club')->set('moduleMenu', 'archivedsponsor');
            $contactType = 'archivedsponsor';
            $this->session->set('contactType', $contactType);
            $isArchiveSponsor = true;
        }
        $contactModuleType = $this->get('club')->get('moduleMenu');
        $contactData = $this->contactDetails($contact, $contactType);
        $adAreas = $this->em->getRepository('CommonUtilityBundle:FgSmAdArea')->getAdAreas($this->clubId);
        $addDataArray = $this->getNavigationAndCountDetails($contact, $contactData['is_company'], $offset, $isArchiveSponsor);
        $tabsDetails = FgUtility::getTabsArrayDetails($this->container, $accessObj->tabArray, $offset, $contact, $addDataArray, "ads", "sponsor");

        $dataArray = array('type' => $contactType, 'contactData' => $contactData, 'isArchiveSponsor' => $isArchiveSponsor, 'tabs' => $tabsDetails, 'isReadOnly' => $isReadOnly, 'offset' => $offset, 'adAreas' => $adAreas, 'clubId' => $this->clubId, 'breadCrumb' => array('breadcrumb_data' => array(), 'back' => ($contactModuleType === 'archivedsponsor') ? $this->generateUrl('view_archived_sponsors') : $this->generateUrl('clubadmin_sponsor_homepage')));
        $return = array_merge($addDataArray, $dataArray);
        return $return;
    }

    /**
     * Function to get navigation (next/prev contact) data and count data in sponsor panel tabs.
     *
     * @param int     $contactId        Contact id
     * @param bool    $isCompany        Whether contact is company or not
     * @param int     $offset           Offset value
     * @param boolean $isArchiveSponsor archivedSponsor flag
     *
     * @return array $details Details array
     */
    private function getNavigationAndCountDetails($contactId, $isCompany, $offset, $isArchiveSponsor)
    {
        // Get Connection, Assignments, Notes, Services, Ads count of a Sponsor.
        $details = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getContactCountDetails($this->clubId, $contactId, $isCompany, $this->clubType, true, true, true, true, true, true, $isArchiveSponsor, $this->federationId, $this->subFederationId);
        // Get navigation details.
        $nextprevious = new NextpreviousSponsor($this->container);
        $nextPreResSet = $nextprevious->nextPreviousSponsorData($this->contactId, $contactId, $offset, 'sponsor_ads', 'offset', 'contact', 0);
        $details['nextPreviousResultset'] = $nextPreResSet;

        return $details;
    }

    /**
     * This action is used for getting ads of a sponsor.
     *
     * @param int $contact Contact Id
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse Array of Service Ads.
     */
    public function getSponsorAdsAction($contact)
    {
        $sponsorAds = $this->em->getRepository('CommonUtilityBundle:FgSmSponsorAds')->getSponsorAds($contact, $this->clubId, true);

        return new JsonResponse($sponsorAds);
    }

    /**
     * Action to update (Add, Edit, Delete) sponsor ads of a contact.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse Array of saved status.
     */
    public function updateSponsorAdsAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $dataArr = json_decode($request->get('saveData'), true);

            foreach ($dataArr['new'] as $key => $value) {
                $fileCheck = new FileChecking($this->container);
                $dataArr['new'][$key]['oldimage_name'] = $dataArr['new'][$key]['image'];
                $dataArr['new'][$key]['image'] = $fileCheck->replaceSingleQuotes($dataArr['new'][$key]['image']);
            }
            $sponsorId = $request->get('sponsorId');
            if (count($dataArr) > 0) {
                $this->em->getRepository('CommonUtilityBundle:FgSmSponsorAds')->updateSponsorAds($dataArr, $this->clubId, $sponsorId, $this->container);
            }

            return new JsonResponse(array('status' => 'SUCCESS', 'noparentload' => true, 'flash' => $this->get('translator')->trans('SPONSOR_ADS_UPDATED')));
        }
    }

    /**
     * Function for getting contact details.
     *
     * @param int    $contactId Contact id
     * @param string $type      Contact type
     *
     * @return array $fieldsArray Contact details
     */
    private function contactDetails($contactId, $type = 'contact')
    {
        $club = $this->get('club');
        $contactlistClass = new Contactlist($this->container, '', $club, $type);
        $website = $this->container->getParameter('system_field_website');
        $companylogo = $this->container->getParameter('system_field_companylogo');
        $contactlistClass->setColumns(array('contactname', 'is_company', 'ms.`2` as firstName', 'ms.`23` as lastName', 'ms.`9` as companyName', 'contactclubid', "ms.`$website` as website", "ms.`$companylogo` as companylogo"));
        $contactlistClass->setFrom('*');
        $contactlistClass->setCondition();
        $sWhere = " fg_cm_contact.id=$contactId";
        $contactlistClass->addCondition($sWhere);
        $listquery = $contactlistClass->getResult();
        $fieldsArray = $this->conn->fetchAll($listquery);

        return $fieldsArray[0];
    }

    /**
     * Function to handle ads file upload.
     *
     * @return JsonResponse File upload status
     */
    public function uploadFileAction(Request $request)
    {
        $upload = new FgFileUploadHandler($request, $this->container);
        $upload->setErrorMessages(array('invalidType' => $this->get('translator')->trans('DATA_DROP_IMAGE_INVALID')));
        $upload->setFileType('image');

        return new JsonResponse($upload->initialize());
    }
}
