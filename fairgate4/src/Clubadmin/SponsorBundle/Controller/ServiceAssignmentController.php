<?php

/**
 * Service Assignment Controller
 *
 * This controller was created for handling service Assignmets
 *
 * @package    ClubadminSponsorBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 *
 */
namespace Clubadmin\SponsorBundle\Controller;

use Common\UtilityBundle\Controller\FgController as ParentController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Clubadmin\Util\Contactlist;
use Clubadmin\ContactBundle\Util\ContactlistData;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Util\FgSettings;
use Symfony\Component\HttpFoundation\Request;

class ServiceAssignmentController extends ParentController
{

    /**
     * Function is used for assign service to contacts
     * @param Request $request   Request object
     * @param type    $bookingId booking id
     *
     * @return template
     */
    public function assignServiceAction(Request $request, $bookingId = false)
    {
        $serviceAssigns = array();
        $serviceCats = $this->em->getRepository('CommonUtilityBundle:FgSmCategory')->getServicesWithCategory($this->clubId);
        if ($bookingId) { //edit booking
            $bookingDetails = $this->em->getRepository('CommonUtilityBundle:FgSmBookings')->getbookigDetailsById($bookingId, $this->clubId);
            $payments = ($bookingDetails[0]['paymentPlan'] == 'custom') ? $this->em->getRepository('CommonUtilityBundle:FgSmBookings')->getPaymentPlansOfBooking($bookingId) : false;
            $deposited = $this->getDepositedWith($bookingDetails[0], $serviceCats);
            $serviceAssigns['contacts'] = $bookingDetails[0]['contactId'];
            $serviceAssigns['serviceId'] = $bookingDetails[0]['serviceId'];
            $serviceAssigns['categoryId'] = $bookingDetails[0]['categoryId'];
        } elseif ($request->getMethod() == 'POST') { //create assignment
            $serviceAssigns['contacts'] = $request->request->get('contactids', '');
            $serviceAssigns['serviceId'] = $request->request->get('dropMenuId');
            $serviceAssigns['categoryId'] = $request->request->get('dropCategoryId');
        }
        //redirect to listing if assign contactId is null
        if (!isset($serviceAssigns['contacts'])) {
            $this->redirect($this->generateUrl('clubadmin_sponsor_homepage'))->sendHeaders();
        }
        $teams = $this->em->getRepository('CommonUtilityBundle:FgRmRole')->getTeamsOrWorkgroupsForDocumentsDepositedOption('team', $this->clubTeamId, $this->clubDefaultLang, false, false, $this->container);
        $contacts = (!empty($serviceAssigns['contacts'])) ? $this->getSelectedContactNames($serviceAssigns['contacts']) : '';

        $pagetitle = ($bookingId) ? $this->get('translator')->trans('SPONSOR_ASSIGNMENT_EDIT_TITLE', array('%contactName%' => $contacts[0]['contactname'])) : $this->getPageTitle($contacts, $serviceAssigns, $serviceCats);

        return $this->render('ClubadminSponsorBundle:ServiceAssignment:serviceAssignment.html.twig', array('categories' => $serviceCats, 'serviceTitle' => $pagetitle, 'teams' => $teams, 'serviceAssigns' => $serviceAssigns, 'currency' => $this->get('club')->clubCurrency, 'contacts' => $contacts, 'bookings' => $bookingDetails[0],
                'deposited' => $deposited, 'payments' => $payments, 'backTo' => $request->request->get('backTo', false)));
    }

    /**
     * Function is used to get contact names
     *
     * @param type $contactIds Connection
     * @param type $getIdOnly  get contact id only
     * @return array
     */
    private function getSelectedContactNames($contactIds = '', $getIdOnly = false)
    {
        $club = $this->get('club');
        $contactlistClass = new Contactlist($this->container, '', $club, 'sponsor');
        if ($getIdOnly == 'idOnly') {
            $contactlistClass->setColumns(array('GROUP_CONCAT(contactid) groupIds'));
        } elseif ($getIdOnly == 'countOnly') {
            $contactlistClass->setColumns(array('count(contactid) total'));
        } else {
            $contactlistClass->setColumns(array('contactid', 'contactname'));
        }
        $contactlistClass->setFrom('*');
        $contactlistClass->setCondition();
        if (!empty($contactIds)) {
            $sWhere = " fg_cm_contact.id in (" . $contactIds . ")";
            $contactlistClass->addCondition($sWhere);
        }
        if (!$getIdOnly) {
            $contactlistClass->addOrderBy('contactname ASC');
        }
        $contactlistClass->setLimit(10);
        $listquery = $contactlistClass->getResult();

        $fieldsArray = $this->conn->fetchAll($listquery);

        return $fieldsArray;
    }

    /**
     * Function to get the page title of service assignment page
     *
     * @param type $contacts    Contacts list array
     * @param type $assignArray Details array for assignment
     * @param type $services    Services list
     */
    private function getPageTitle($contacts, $assignArray, $services, $duplicate = false)
    {
        if ($duplicate == true) {
            $contactName = $contacts[0]['contactname'];

            return $this->get('translator')->trans('SPONSOR_ASSIGNMENT_TITLE_DUPLICATE', array('%contactName%' => $contactName));
        } else {
            if (empty($assignArray['contacts'])) {
                $contactCount = $this->getSelectedContactNames('', 'countOnly');
                $contactCount = $contactCount[0]['total'];
            } else {
                $contactCount = count(explode(',', $assignArray['contacts']));
            }
            if ($contactCount === 1) {
                $contactName = $contacts[0]['contactname'];

                return $this->get('translator')->trans('SPONSOR_ASSIGNMENT_TITLE_SC_SERVICE', array('%contactName%' => $contactName));
            } elseif ($contactCount > 1) {

                return $this->get('translator')->trans('SPONSOR_ASSIGNMENT_TITLE_MC_SERVICE', array('%count%' => $contactCount));
            } else {

                return $this->get('translator')->trans('SPONSOR_ASSIGNMENT_TITLE_ALL', array('%count%' => $contactCount));
            }
        }
    }

    /**
     * Function to get deposited with values of booking
     *
     * @param type $bookingDetails
     * @param type $services
     * @return type
     */
    private function getDepositedWith($bookingDetails, $services)
    {
        $categoryId = $bookingDetails['categoryId'];
        $serviceId = $bookingDetails['serviceId'];
        $deposited = $this->em->getRepository('CommonUtilityBundle:FgSmBookingDeposited')->getDepositsOfBooking($bookingDetails['bookingId']);
        $return['serviceType'] = $services[$categoryId]['services'][$serviceId]['serviceType'];
        if ($services[$categoryId]['services'][$serviceId]['serviceType'] === 'contact' && !empty($deposited['contacts'])) {
            $contactlistClass = new Contactlist($this->container, '', $this->get('club'), 'contact');
            $contactlistClass->setColumns(array('contactid', 'contactNameYOB'));
            $contactlistClass->setFrom('*');
            $contactlistClass->setCondition();
            $where = " fg_cm_contact.id IN ({$deposited['contacts']})";
            $contactlistClass->addCondition($where);
            $listquery = $contactlistClass->getResult();
            $return['deposits'] = $this->conn->fetchAll($listquery);
        } else {
            $return['deposits'] = $deposited;
        }

        return $return;
    }

    /**
     * Function to save service assignment
     *
     * @param Request $request Request object
     *
     * @return JsonResponse
     */
    public function saveAssignmentAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $data = $request->get('data');
            $backTo = $request->get('backTo', $this->generateUrl('clubadmin_sponsor_homepage'));
            $bookingId = $data['bookingId'];
            if (empty($data['contactIds'])) {
                $contacts = $this->getSelectedContactNames('', 'idOnly');
                $contacts = $contacts[0]['groupIds'];
            } else {
                $contacts = $data['contactIds'];
            }
            unset($data['contactIds']);
            unset($data['bookingId']);
            $payPlan = isset($data['payment_plan']) ? $data['payment_plan'] : '';
            $endDate = isset($data['end_date']) ? $data['end_date'] : '';
            //set payment end date if service end date is not null
            if ($payPlan == 'regular' && !empty($endDate)) {
                $lastPaymt = isset($data['last_payment_date']) ? $data['last_payment_date'] : '';
                if (!empty($lastPaymt)) {
                    $payLastDate = new \DateTime();
                    $endDateObj = new \DateTime();

                    $payLastDateTimestamp = $payLastDate->createFromFormat(FgSettings::getPhpDateFormat(), $lastPaymt)->format('U');
                    $endDateTimestamp = $endDateObj->createFromFormat(FgSettings::getPhpDateFormat(), $endDate)->format('U');

                    //reset payment end date if its greater than service end date
                    if ($payLastDateTimestamp > $endDateTimestamp) {
                        $data['last_payment_date'] = $endDate;
                    }
                }
            }
            $this->em->getRepository('CommonUtilityBundle:FgSmBookings')->savebooking($bookingId, $contacts, $this->get('club'), $data);

            return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('SM_ASSIGNMENT_SAVED'), 'redirect' => $backTo, 'sync' => 1));
        }
    }

    /**
     * Get the contact Id's either selected or filtered list
     *
     * @param Request $request Request object
     *
     * @return array  contactIds
     */
    public function getAllSponsorContactIdsAction(Request $request)
    {
        $searchval = $request->get('searchVal', '');
        $formdataValues = $request->get('filterData', '');

        //Set all request value to its corresponding variables
        $contactlistData = new ContactlistData($this->contactId, $this->container, 'sponsor');
        $aColumns = array();
        array_push($aColumns, 'contactid', 'contactname', 'membershipType', 'clubId');
        $contactlistData->filterValue = json_decode($formdataValues, true);
        $contactlistData->dataTableColumnData = $request->get('columns', $aColumns);
        $contactlistData->searchval['value'] = $request->get('search', $searchval);
        $contactlistData->tableFieldValues = $request->get('columns', $aColumns);
        $contactData = $contactlistData->getContactData();
        $contacts = $contactData['data'];
        $contactIds = FgUtility::getArrayColumn($contacts, 'id');
        return new JsonResponse(array('contactIds' => $contactIds));
    }

    /**
     * Function to show stop popup (used in stop/delete sertvice and remove from recently ended list)
     *
     * @param Request $request Request object
     *
     * @return template
     */
    public function stopServicePopupAction(Request $request)
    {
        $actionType = $request->get('actionType');
        $pageType = $request->get('pageType');
        $bookedIds = '';
        if ($actionType != "") {
            $bookedIds = $request->get('bookedIds');
            $servicesCount = count(explode(",", $bookedIds));
            $skippedServicesCount = $this->em->getRepository('CommonUtilityBundle:FgSmBookings')->getSkippedServicesCount($bookedIds);
            if ($actionType == 'deleteservice' || $actionType == 'deleteserviceofsponsor') { //delete sevice
                $titleText = ($servicesCount > 1) ? $this->get('translator')->trans('SM_DELETE_ASSIGNMENT_PLURAL', array('%count%' => $servicesCount)) : $this->get('translator')->trans('SM_DELETE_ASSIGNMENT_SINGULAR');
                $stopDesc = ($servicesCount > 1) ? $this->get('translator')->trans('SM_DELETE_ASSIGNMENT_MESSAGE_PLURAL', array('%count%' => $servicesCount)) : $this->get('translator')->trans('SM_DELETE_ASSIGNMENT_MESSAGE_SINGULAR');
                $buttonText = $this->get('translator')->trans('DELETE');
            } else if ($actionType == 'skipAssignment') {  //remove serice from recently ended
                $titleText = ($servicesCount > 1) ? $this->get('translator')->trans('SM_SKIP_ASSIGNMENT_PLURAL', array('%count%' => $servicesCount)) : $this->get('translator')->trans('SM_SKIP_ASSIGNMENT_SINGULAR');
                $stopDesc = ($servicesCount > 1) ? $this->get('translator')->trans('SM_SKIP_ASSIGNMENT_MESSAGE_PLURAL', array('%count%' => $servicesCount)) : $this->get('translator')->trans('SM_SKIP_ASSIGNMENT_MESSAGE_SINGULAR');
                $buttonText = $this->get('translator')->trans('REMOVE');
            } else { //stop service
                $titleText = ($servicesCount > 1) ? $this->get('translator')->trans('SM_STOP_ASSIGNMENT_PLURAL', array('%count%' => $servicesCount)) : $this->get('translator')->trans('SM_STOP_ASSIGNMENT_SINGULAR');
                $stopDesc = ($servicesCount > 1) ? $this->get('translator')->trans('SM_STOP_ASSIGNMENT_MESSAGE_PLURAL', array('%count%' => $servicesCount)) : $this->get('translator')->trans('SM_STOP_ASSIGNMENT_MESSAGE_SINGULAR');
                $buttonText = $this->get('translator')->trans('SM_ACTION_STOP');
            }
        }
        $selActionType = $request->get('selActionType') ? $request->get('selActionType') : '';
        $return = array('actionType' => $actionType, 'selActionType' => $selActionType, 'stopDesc' => $stopDesc, 'titleText' => $titleText, 'buttonText' => $buttonText, 'bookedIds' => $bookedIds, "pageType" => $pageType, "servicesCount" => $servicesCount, "skippedServicesCount" => $skippedServicesCount);

        return $this->render('ClubadminSponsorBundle:ServiceAssignment:confirmStop.html.twig', $return);
    }

    /**
     * Function used to stop/delete a service
     * @param Request $request Request object
     * @return JsonResponse
     */
    public function stopServiceAction(Request $request)
    {
        $actionType = $request->get('actionType', '');
        $CurrentContactId = $request->get('CurrentContactId', '0');
        $selectedId = json_decode($request->get('selectedId', '0'));
        $selectedId = explode(",", $selectedId);
        if ($request->getMethod() == 'POST') {
            $flashMsg = '';
            $idCount = '';

            if (count($selectedId) > 0) {
                if ($actionType != "") {
                    $idCount = count($selectedId);
                    $club = $this->get('club');
                    $stopServices = $this->em->getRepository('CommonUtilityBundle:FgSmBookings')->stopServices($selectedId, $actionType, $club);
                    if ($CurrentContactId) {
                        $activeServicesCount = $this->em->getRepository('CommonUtilityBundle:FgSmBookings')->getCountOfSponsorServices($this->clubId, $CurrentContactId);
                    }
                    if ($idCount > 1) {
                        $flashMsg = ($actionType == 'deleteservice' || $actionType == 'deleteserviceofsponsor') ? 'SM_DELETE_ASSIGNMENT_SUCCESS_MESSAGE_PLURAL' : 'SM_STOP_ASSIGNMENT_SUCCESS_MESSAGE_PLURAL';
                    } else {
                        $flashMsg = ($actionType == 'deleteservice' || $actionType == 'deleteserviceofsponsor') ? 'SM_DELETE_ASSIGNMENT_SUCCESS_MESSAGE_SINGULAR' : 'SM_STOP_ASSIGNMENT_SUCCESS_MESSAGE_SINGULAR';
                    }
                }
                return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans($flashMsg), "activeServicesCount" => $activeServicesCount));
            }
        }
    }

    /**
     * Method for duplicating sponsor service
     * @param Request $request Request object
     * @return type
     */
    public function duplicateContactServiceAction(Request $request)
    {
        $bookedId = $request->request->get('bookedId', '');
        $bookingDetails = $this->em->getRepository('CommonUtilityBundle:FgSmBookings')->getbookigDetailsById($bookedId);
        $serviceAssigns = array("contacts" => $bookingDetails[0]['contactId'], "serviceId" => $bookingDetails[0]['serviceId'], "categoryId" => $bookingDetails[0]['categoryId']);
        $serviceCats = $this->em->getRepository('CommonUtilityBundle:FgSmCategory')->getServicesWithCategory($this->clubId);
        $teams = $this->em->getRepository('CommonUtilityBundle:FgRmRole')->getTeamsOrWorkgroupsForDocumentsDepositedOption('team', $this->clubTeamId, $this->clubDefaultLang, false, false, $this->container);
        $contacts = (!empty($serviceAssigns['contacts'])) ? $this->getSelectedContactNames($serviceAssigns['contacts']) : '';
        $pagetitle = $this->getPageTitle($contacts, $serviceAssigns, $serviceCats, true);
        //If the duplicate action is coming from recently ended listing page, skipped flag is needed to set to the previouly booked id

        return $this->render('ClubadminSponsorBundle:ServiceAssignment:serviceAssignment.html.twig', array('categories' => $serviceCats, 'serviceTitle' => $pagetitle, 'teams' => $teams, 'serviceAssigns' => $serviceAssigns, 'currency' => $this->get('club')->clubCurrency, 'contacts' => $contacts, 'bookings' => array(),
                'deposited' => $deposited, 'payments' => $payments, 'backTo' => $request->request->get('backTo', false)));
    }

    /**
     * Function used to skip a service
     *
     * @param Request $request Request object
     *
     * @return JsonResponse
     */
    public function skipServiceAction(Request $request)
    {
        $actionType = $request->get('actionType', '');
        $selectedIds = json_decode($request->get('selectedId', '0'));
        if (!$selectedIds) {
            $selectedIds = $request->get('selectedId', '0');
        }
        $selectedId = explode(",", $selectedIds);
        if ($request->getMethod() == 'POST') {
            if (count($selectedId) > 0 && $actionType === "skipAssignment") {
                $idCount = count($selectedId);
                $club = $this->get('club');
                //skip services
                $this->em->getRepository('CommonUtilityBundle:FgSmBookings')->skipServices($selectedId, $club);
                $flashMsg = ($idCount > 1) ? 'SM_SKIP_ASSIGNMENT_SUCCESS_MESSAGE_PLURAL' : 'SM_SKIP_ASSIGNMENT_SUCCESS_MESSAGE_PLURAL';

                return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans($flashMsg)));
            }
        }
    }
}
