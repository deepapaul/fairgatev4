<?php

/**
 * ServiceListController.
 *
 * This controller was created for handling service listing functionalities
 *
 * @author     pitsolutions.ch
 *
 * @version    Fairgate V4
 */
namespace Clubadmin\ContactBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Clubadmin\SponsorBundle\Util\Servicelist;
use Clubadmin\Util\Contactlist;
use Clubadmin\ContactBundle\Util\ContactlistData;
use Clubadmin\ContactBundle\Util\ContactDetailsAccess;
use Clubadmin\SponsorBundle\Util\NextpreviousSponsor;
use Common\UtilityBundle\Util\FgUtility;
use Symfony\Component\HttpFoundation\Request;

class ServicesListController extends FgController
{

    /**
     * Function to render services tab.
     *
     * @param Int $offset
     * @param Int $contact
     */
    public function servicesAction(Request $request, $offset, $contact)
    {
        $dataSet = $this->getServicePageData($contact, $offset);
        $dataSet['fiscal'] = $this->get('club')->getFiscalYear();
        $dataSet['contactDetails'] = $this->contactDetails($contact);
        $pagetype = $request->get('level1') == 'sponsor' ? 'sponsor' : 'contact';
        $accessObj = new ContactDetailsAccess($contact, $this->container, "sponsor");
        $isArchiveSponsor = false;
        //set menu module
        if ($accessObj->menuType == 'archive' && $pagetype == 'sponsor') {
            $this->get('club')->set('moduleMenu', 'archivedsponsor');
            $isArchiveSponsor = true;
        }
        $dataSet['module'] = $this->get('club')->get('moduleMenu');
        $dataSet['breadCrumb'] = array('breadcrumb_data' => array(), 'back' => ($dataSet['module'] === 'archivedsponsor') ? $this->generateUrl('view_archived_sponsors') : $this->generateUrl('clubadmin_sponsor_homepage'));
        $nextPrevData = $this->getNavigationData($contact, $offset, $isArchiveSponsor);

        $contCountDetails = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getContactCountDetails($this->clubId, $contact, $dataSet['contactDetails']['is_company'], $this->clubType, true, true, true, true, false, false, false, $this->federationId, $this->subFederationId);
        $dataSet = array_merge($dataSet, $contCountDetails, $nextPrevData);
        $contCountDetails['servicesCount'] = $nextPrevData['servicesCount'];
        $contCountDetails['adsCount'] = $nextPrevData['adsCount'];
        $tabs = $dataSet['tabs'];
        $tabsDetails = FgUtility::getTabsArrayDetails($this->container, $tabs, $offset, $contact, $contCountDetails, "services", "sponsor");
        unset($dataSet['tabs']);
        $dataSet['tabs'] = $tabsDetails;
        return $this->render('ClubadminContactBundle:Service:servicesList.html.twig', $dataSet);
    }

    /**
     * For collect the services list data.
     *
     * @param int $contact
     *
     * @return JsonResponse
     */
    public function listServicesAction($contact)
    {
        $sponsorlistData = new ContactlistData($this->contactId, $this->container, 'servicelist');
        $servicelistData = new Servicelist($this->container);
        $servicelistData->serviceType = 'contact';
        $servicelistData->contactId = $contact;
        $result['past'] = $this->getpastservices($servicelistData, $sponsorlistData);
        $result['future'] = $this->getfutureservices($servicelistData, $sponsorlistData);
        $result['activesponsor'] = $this->getactiveservices($servicelistData, $sponsorlistData);

        return new JsonResponse($result);
    }

    /**
     * Function to get past services of a contact.
     *
     * @param object $servicelistData service list object
     * @param object $sponsorlistData sponsor list object
     *
     * @return array
     */
    private function getpastservices($servicelistData, $sponsorlistData)
    {
        $servicelistData->tabType = 'overview_past';
        $jsonColumns = $servicelistData->getServicetableColumns();
        $sponsorlistData->tabledata = json_decode($jsonColumns, true);
        $tableColumns = $sponsorlistData->getTableColumns();
        $servicelistData->columns = $tableColumns;
        $this->servicelistinitialSetting($servicelistData);
        $listQuery = $servicelistData->getResult();
        $result = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($listQuery);

        return $result;
    }

    /**
     * Function to get active services of a contact.
     *
     * @param object $servicelistData service list object
     * @param object $sponsorlistData sponsor list object
     *
     * @return array
     */
    private function getactiveservices($servicelistData, $sponsorlistData)
    {
        $servicelistData->tabType = 'overview_active';
        $jsonColumns = $servicelistData->getServicetableColumns();
        $sponsorlistData->tabledata = json_decode($jsonColumns, true);
        $tableColumns = $sponsorlistData->getTableColumns();
        $servicelistData->columns = $tableColumns;
        $this->servicelistinitialSetting($servicelistData);
        $listQuery = $servicelistData->getResult();
        $result = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($listQuery);

        return $result;
    }

    /**
     * Function to get future services of a contact.
     *
     * @param object $servicelistData service list object
     * @param object $sponsorlistData sponsor list object
     *
     * @return array
     */
    private function getfutureservices($servicelistData, $sponsorlistData)
    {
        $servicelistData->tabType = 'overview_future';
        $jsonColumns = $servicelistData->getServicetableColumns();
        $sponsorlistData->tabledata = json_decode($jsonColumns, true);
        $tableColumns = $sponsorlistData->getTableColumns();
        $servicelistData->columns = $tableColumns;
        $this->servicelistinitialSetting($servicelistData);
        $listQuery = $servicelistData->getResult();
        $result = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($listQuery);

        return $result;
    }

    /**
     * function to set values of service list object.
     *
     * @param object $servicelistData service list object
     */
    private function servicelistinitialSetting($servicelistData)
    {
        $servicelistData->setFrom();
        $servicelistData->setColumns();
        $servicelistData->setCondition();
    }

    /**
     * Function to get data in service page of a contact.
     *
     * @param int $contact Contact id
     * @param int $offset  Contact offset value
     *
     * @return array $return Result array
     */
    private function getServicePageData($contact, $offset)
    {
        $accessObj = new ContactDetailsAccess($contact, $this->container, 'sponsor');
        if ($accessObj->accessType == 'NO_ACCESS' || !in_array('services', $accessObj->tabArray)) {
            // throw $this->createNotFoundException($this->clubTitle.' have no access to this page');
            $this->fgpermission->checkClubAccess('', 'servicepage');
        }
//        $contactType = $accessObj->contactviewType;
        $return = array('readOnly' => false, 'contact' => $contact, 'clubId' => $this->clubId, 'offset' => $offset, 'tabs' => $accessObj->tabArray);

        return $return;
    }

    /**
     * Function to get navigation(next/prev) data of a sponsor.
     *
     * @param int     $contact          Contact id
     * @param int     $offset           Contact offset value
     * @param boolean $isArchiveSponsor isarchivedsponsor flag
     *
     * @return array $return Result array
     */
    private function getNavigationData($contact, $offset, $isArchiveSponsor)
    {
        $return['servicesCount'] = $this->em->getRepository('CommonUtilityBundle:FgSmBookings')->getCountOfSponsorServices($this->clubId, $contact, $isArchiveSponsor);
        $return['adsCount'] = $this->em->getRepository('CommonUtilityBundle:FgSmSponsorAds')->getCountOfSponsorAds($this->clubId, $contact);
        $return['offset'] = $offset;
        // Generating next and previous data for the next-previous functionality in the overview page
        $nextprevious = new NextpreviousSponsor($this->container);
        $nextPreviousResultset = $nextprevious->nextPreviousSponsorData($this->contactId, $contact, $offset, 'services_listing', 'offset', 'contact', 0);
        $return['nextPreviousResultset'] = $nextPreviousResultset;

        return $return;
    }

    /**
     * Function to get contact details.
     *
     * @param int $contactId contact id
     *
     * @return array/false
     */
    private function contactDetails($contactId)
    {
        $club = $this->get('club');
        $contactlistClass = new Contactlist($this->container, $this->contactId, $club, 'editable');
        $contactlistClass->setColumns(array('contactName', 'contactname', 'contactid', 'is_household_head', 'is_seperate_invoice', 'is_company', 'has_main_contact', 'comp_def_contact', 'comp_def_contact_fun', 'clubId', '21', '68'));
        $contactlistClass->setFrom('*');
        $contactlistClass->setCondition();
        $sWhere = " fg_cm_contact.id=$contactId";
        $contactlistClass->addCondition($sWhere);
        $listquery = $contactlistClass->getResult();
        $fieldsArray = $this->conn->fetchAll($listquery);

        return isset($fieldsArray[0]) ? $fieldsArray[0] : false;
    }
}
