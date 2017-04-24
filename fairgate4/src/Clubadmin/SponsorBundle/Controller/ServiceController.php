<?php

/**
 * Service Controller.
 *
 * This controller was created for handling services in Sponsor management.
 *
 * @author     pitsolutions.ch
 *
 * @version    Fairgate V4
 */
namespace Clubadmin\SponsorBundle\Controller;

use Common\UtilityBundle\Controller\FgController as ParentController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Clubadmin\ContactBundle\Util\ContactlistData;
use Clubadmin\SponsorBundle\Util\Servicelist;
use Clubadmin\Classes\Contactdatatable;
use Common\UtilityBundle\Util\FgPermissions;

/**
 * This controller was created for handling services in Sponsor management.
 *
 * @author pitsolutions.ch
 */
class ServiceController extends ParentController
{
    /**
     * This action is used for editing data and services of a sponsor category.
     *
     * @param int                                       $catId   Category Id
     * @param \Symfony\Component\HttpFoundation\Request $request Request object
     *
     * @Template("ClubadminSponsorBundle:Service:servicesettings.html.twig")
     *
     * @return array Data array.
     */
    public function editServicesAction($catId, Request $request)
    {
        $categoryObj = $this->em->getRepository('CommonUtilityBundle:FgSmCategory')->find($catId);
        if(!$categoryObj){
            $permissionObj = new FgPermissions($this->container); 
            $permissionObj->checkClubAccess('','not_found');
        } elseif($categoryObj->getClub()->getId()!==$this->clubId){
            $permissionObj = new FgPermissions($this->container); 
            $permissionObj->checkUserAccess('','access_denied');
        }
        $categorySettings = array('id' => $categoryObj->getId(), 'title' => $categoryObj->getTitle());
        $categories = $this->em->getRepository('CommonUtilityBundle:FgSmCategory')->getAllSmCategories($this->clubId);
        $breadCrumb = $this->getBreadcrumbData($catId, $request);
        
        return array('result_data' => $categorySettings, 'clubDefaultLang' => $this->get('club')->get('club_default_lang'), 'clubLanguages' => $this->clubLanguages, 'breadCrumb' => $breadCrumb, 'backLink' => $breadCrumb['back'], 'categories' => $categories,'contactId' => $this->contactId,'clubId' => $this->clubId);
    }

    /**
     * Function to get breadcrumb data of manage services page.
     *
     * @param int    $catId   Category id
     * @param object $request Request object
     *
     * @return array $breadCrumb Breadcrumb data array
     */
    private function getBreadcrumbData($catId, $request)
    {
        $session = $request->getSession();
        $referrerPage = $session->get('sponsor_categorysettings_referrer', 'sidebar');
        $backLink = ($referrerPage == 'sidebar') ? $this->generateUrl('clubadmin_sponsor_homepage') : $this->generateUrl('sponsor_category_edit');

        $categoryData = $this->em->getRepository('CommonUtilityBundle:FgSmCategory')->getAllserviceCategories($this->clubId);
        $catIdArray = array_keys($categoryData);
        $currKey = array_search($catId, $catIdArray);
        $prevCatId = $catIdArray[$currKey - 1];
        $nextCatId = $catIdArray[$currKey + 1];
        $breadCrumb = array(
            'prev' => $prevCatId ? $this->generateUrl('edit_services', array('catId' => $prevCatId)) : '#',
            'next' => $nextCatId ? $this->generateUrl('edit_services', array('catId' => $nextCatId)) : '#',
            'back' => $backLink,
        );

        return $breadCrumb;
    }

    /**
     * This action is used to get Data for Services Listing.
     *
     * @param int $catId Category Id
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse Array of Services List.
     */
    public function getServicesListAction($catId)
    {
        $servicesList = $this->em->getRepository('CommonUtilityBundle:FgSmServices')->getServicesDataOfCategory($catId, $this->clubId, $this->contactId);

        return new JsonResponse($servicesList);
    }

    /**
     * This action is used for getting log of a sponsor service.
     *
     * @param int $serviceId Service Id
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse Array of Service Log.
     */
    public function serviceLogAction($serviceId)
    {
        $logTabs = array('1' => 'assignments', '2' => 'data');
        $logdisplay = $this->em->getRepository('CommonUtilityBundle:FgSmServicesLog')->getServiceLog($serviceId,$this->clubId);
        $jsonData = array('logdisplay' => $logdisplay, 'logTabs' => $logTabs);

        return new JsonResponse($jsonData);
    }

    /**
     * Action to update (Add, Edit, Delete) sponsor services of a category.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse Array of saved status.
     */
    public function updateServicesAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $dataArr = json_decode($request->get('saveData'), true);
            $catId = json_decode($request->get('catId'), true);
            if (count($dataArr) > 0) {
                $this->em->getRepository('CommonUtilityBundle:FgSmServices')->updateSponsorServices($dataArr, $this->clubId, $this->contactId, $this->get('club')->get('club_default_lang'), $this->container);
            }

            return new JsonResponse(array('status' => 'SUCCESS','sync'=>1, 'flash' => $this->get('translator')->trans('SERVICE_SETTINGS_UPDATED'),'redirect'=> $this->generateUrl('edit_services', array('catId' => $catId)) ));
        }
    }
    /**
     * To find the details of active,future and past services.
     *
     * @return JsonResponse
     */
    public function getServiceDetailAction(Request $request)
    {
        $output = array();
        $sponsorlistData = new ContactlistData($this->contactId, $this->container, 'servicelist');
        $servicelistData = new Servicelist($this->container);
        $servicelistData->serviceType = $request->get('serviceType', 'contact');
        $servicelistData->serviceId = $request->get('serviceId', '50');
        $servicepastDetails = $this->getpastserviceData($servicelistData, $sponsorlistData);
        $serviceactiveDetails = $this->getactiveserviceData($servicelistData, $sponsorlistData);
        $servicefutureDetails = $this->getfutureserviceData($servicelistData, $sponsorlistData);
        $output['aaData']['active'] = $serviceactiveDetails;
        $output['aaData']['future'] = $servicefutureDetails;
        $output['aaData']['former'] = $servicepastDetails;

        return new JsonResponse($output);
    }
    /**
     * To find the past service data.
     *
     * @param Object $servicelistData service list class object
     * @param Object $sponsorlistData contact list class object
     *
     * @return type
     */
    private function getpastserviceData($servicelistData, $sponsorlistData)
    {
        $servicelistData->tabType = 'past';
        $jsonColumns = $servicelistData->getServicetableColumns();
        $sponsorlistData->tabledata = json_decode($jsonColumns, true);
        $tableColumns = $sponsorlistData->getTableColumns();
        $servicelistData->columns = $tableColumns;
        $this->servicelistinitialSetting($servicelistData);
        $listQuery = $servicelistData->getResult();
        //file_put_contents('q1.txt', $listQuery);
        $result = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($listQuery);
        $contactDatatabledata = new Contactdatatable($this->container, $this->get('club'));
        $resultdata = $contactDatatabledata->iterateDataTableData($result, $this->container->getParameter('country_fields'), $sponsorlistData->tabledata);
       
        return $resultdata;
    }
    /**
     * To find active service data.
     *
     * @param Object $servicelistData service list class object
     * @param Object $sponsorlistData contact list class object
     *
     * @return type
     */
    private function getactiveserviceData($servicelistData, $sponsorlistData)
    {
        $servicelistData->tabType = 'active';
        $jsonColumns = $servicelistData->getServicetableColumns();
        $sponsorlistData->tabledata = json_decode($jsonColumns, true);
        $tableColumns = $sponsorlistData->getTableColumns();
        $servicelistData->columns = $tableColumns;
        $this->servicelistinitialSetting($servicelistData);
        $listQuery = $servicelistData->getResult();
        //file_put_contents('q2.txt', $listQuery);
        $result = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($listQuery);
        $contactDatatabledata = new Contactdatatable($this->container, $this->get('club'));
        $resultdata = $contactDatatabledata->iterateDataTableData($result, $this->container->getParameter('country_fields'), $sponsorlistData->tabledata);
       
        return $resultdata;
    }
    /**
     * To find future service data.
     *
     * @param Object $servicelistData service list class object
     * @param Object $sponsorlistData contact list class object
     *
     * @return type
     */
    private function getfutureserviceData($servicelistData, $sponsorlistData)
    {
        $servicelistData->tabType = 'future';
        $jsonColumns = $servicelistData->getServicetableColumns();
        $sponsorlistData->tabledata = json_decode($jsonColumns, true);
        $tableColumns = $sponsorlistData->getTableColumns();
        $servicelistData->columns = $tableColumns;
        $this->servicelistinitialSetting($servicelistData);
        $listQuery = $servicelistData->getResult();
        $result = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($listQuery);
        $contactDatatabledata = new Contactdatatable($this->container, $this->get('club'));
        $resultdata = $contactDatatabledata->iterateDataTableData($result, $this->container->getParameter('country_fields'), $sponsorlistData->tabledata);
       
        return $resultdata;
    }
    /**
     * To initialize servicelist settings.
     *
     * @param Object $servicelistData servicelist class object
     */
    private function servicelistinitialSetting($servicelistData)
    {
        $servicelistData->setFrom();
        $servicelistData->setColumns();
        $servicelistData->setCondition();
    }
}
