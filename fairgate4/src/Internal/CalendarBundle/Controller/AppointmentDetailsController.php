<?php

/**
 * AppointmentDetailsController.
 *
 * This controller used for managing the appointment details page for internal calendar area
 *
 * @author     pitsolutions.ch
 *
 * @version    Fairgate V4
 */
namespace Internal\CalendarBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Internal\CalendarBundle\Util\CalenderEvents;
use Common\UtilityBundle\Repository\Pdo\CalendarPdo;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Util\FgPermissions;
use Common\FilemanagerBundle\Util\FgFileManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Website\CMSBundle\Util\FgWebsite;
use Common\UtilityBundle\Util\FgSettings;
use Internal\CalendarBundle\Util\CalendarFunctions;

class AppointmentDetailsController extends Controller
{

    /**
     * This function is used to display event details page.
     *
     * @param int $eventId event Id
     *
     * @return template
     */
    public function detailsAction($eventId, Request $request)
    {
        $calendarFunctions = new CalendarFunctions($this->container);
        $club = $this->container->get('club');
        $applicationArea = $request->get('applicationArea', 'internal');
        $eventDetails = $this->getCalendarEventData($eventId);
        $this->checkingRights($eventId, $eventDetails, $applicationArea);
        $currentClubId = $this->get('club')->get('id');
        $dateData = $calendarFunctions->getDateandTimeData($eventDetails, $request, $applicationArea);
        $dateDetailsForEditpage = $calendarFunctions->getDateDataForEditPage($dateData['startDate'], $dateData['startTime'], $dateData['endDate'], $dateData['endTime']);
        $dateDetails = $calendarFunctions->getDateDataForDetailsPage($club, $dateData['startDate'], $dateData['startTime'], $dateData['endDate'], $dateData['endTime'], $eventDetails['isAllday']);
        $selRoleAreaIds = explode('|&&&|', $eventDetails['eventRoleIds']);
        $hasEditRights = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgEmCalendar')->checkHasEditRights($this->container, $eventDetails['clubId'], $eventDetails['isClubAreaSelected'], $selRoleAreaIds);
        $getArrayFlag = ($applicationArea == 'internal') ? false : true;
        $eventRoleNames = ($eventDetails['eventRoleAreas']) ? $this->getseperatedCategoryandRoleData($eventDetails['eventRoleAreas'], false, $getArrayFlag) : '';
        $eventCategoryNames = ($eventDetails['eventCategories']) ? $this->getseperatedCategoryandRoleData($eventDetails['eventCategories'], false, $getArrayFlag) : '';   
        $breadCrumbData = array('back' => $this->generateUrl('internal_calendar_view'));
        $eventDetails['description'] = FgUtility::correctCkEditorUrl($eventDetails['description'], $this->container, $currentClubId, 0, $eventDetails['clubId']);
        $decriptionData = html_entity_decode($eventDetails['description'], ENT_COMPAT, 'UTF-8');
        $eventAttachments = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgEmCalendarDetails')->getCalendarDetailsAttachments($eventId);
        $contactId = $this->get('contact')->get('id');
        $returnArray = array('contactId' => $contactId, 'eventDetail' => json_encode($eventDetails), 'eventData' => $eventDetails, 'roleNames' => $eventRoleNames, 'categoryNames' => $eventCategoryNames, 'clubDetails' => $this->getclubDetailsData(), 'breadCrumb' => $breadCrumbData, 'dateDetails' => $dateDetails, 'detailsId' => $eventId, 'hasEditRights' => $hasEditRights, 'description' => trim($decriptionData), 'dateDetailsForEditpage' => $dateDetailsForEditpage, 'currentClubId' => $currentClubId, 'eventAttachments' => $eventAttachments);
        if ($applicationArea == 'website') {
            $resultArray = $this->getParametersForWebsiteTemplate($returnArray, $eventDetails['title'], $request->get('navIdentifier'), $currentClubId, $contactId);
            $resultArray['view'] = $request->get('view', 'month');
            $resultArray['pageTitle'] = $eventDetails['title'];
            $resultArray['submitButtonTemplate'] = $this->container->get('cms.themes')->getViewPage('formSubmitButtonTemplate');
            $resultArray['formCaptchaTemplate'] = $this->container->get('cms.themes')->getViewPage('formCaptchaTemplate');
            $resultArray['contactLang'] = $this->container->get('club')->get('default_lang');
            $websiteObj = new FgWebsite($this->container);
            $resultArray['pageData'] = $websiteObj->getOnPageLoadElementData($resultArray['footerId'],$resultArray['currentNavigationId'],$resultArray['pagecontentData']['pageElementsArray']);
            return $this->render('WebsiteCMSBundle:SpecialPages:appointmentDetails.html.twig', $resultArray);
        } else {

            return $this->render('InternalCalendarBundle:Default:appointmentDetails.html.twig', $returnArray);
        }
    }

    /**
     * Method to return parametrs to build website calendar detail view template
     * 
     * @param array  $returnArray   existing parameter array
     * @param string $pageTitle     event title
     * @param string $navIdentifier menu (string after club Identifier in url)
     * @param int    $currentClubId current clubId
     * @param int    $contactId
     * 
     * @return array of parameters
     */
    private function getParametersForWebsiteTemplate($returnArray, $pageTitle, $navIdentifier, $currentClubId, $contactId)
    {
        $websiteObj = new FgWebsite($this->container);
        $returnArray['pagetitle'] = $pageTitle;
        $returnArray['navIdentifier'] = $returnArray['menu'] = $menu = $navIdentifier;
        $pageDetails = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsNavigation')->getPageDetails($currentClubId, $menu);
        $returnArray['mainPageId'] = $pageDetails[0]['id'];
        $localeArr = FgSettings::getLocaleDetails();
        $isPublic = (!$contactId ) ? 1 : 0;
        $locale = ($isPublic) ? $localeArr[$this->container->get('club')->get('club_default_lang')][0] : $localeArr[$this->container->get('contact')->get('default_lang')][0];
        $returnArray['clubLocale'] = $locale;
        $returnArray['clubDateFormat'] = 'EEEE';

        return $websiteObj->getParametesForWebsiteLayout($returnArray);
    }

    /**
     * This function is used to get event details data.
     *
     * @param int $eventId event Id/detail id
     *
     * @return array
     */
    public function getCalendarEventData($eventId)
    {
        $calenderEventsObj = new CalenderEvents($this->container);
        $calenderEventsObj->setColumns(array('CD.location', 'CD.location_latitude', 'CD.location_longitude', 'CD.url', 'CD.is_show_in_googlemap'));
        $calenderEventsObj->setFrom();
        $calenderEventsObj->setCondition("CD.id = $eventId");
        $qry = $calenderEventsObj->getResult();

        //execute the qry and get the results
        $calendarPdo = new CalendarPdo($this->container);
        $eventDetails = $calendarPdo->executeQuery($qry);

        return $eventDetails[0];
    }

    /**
     * This function is used to get event details data.
     *
     * @param string $eventDetails event details containing category data and role data
     * @param bool   $getIdFlag    flag to obtain role id array or not for user rights checking
     * @param bool   $getArrayFlag flag to details as array or not (if set, return array of color and name with id as key)
     *
     * @return string/array
     */
    private function getseperatedCategoryandRoleData($eventDetails, $getIdFlag, $getArrayFlag = false)
    {
        $details = explode('|&&&|', $eventDetails);
        $splitDetails = $splitIdDetails = $roleDetails = array();
        foreach ($details as $key => $data) {
            $splitDetailsFinal = explode('|@@@|', $data);
            $splitDetails[$key] = $splitDetailsFinal[1];
            $splitIdDetails[$key] = $splitDetailsFinal[0];
            $roleDetails[$splitDetailsFinal[0]]['name'] = $splitDetailsFinal[1];
            (isset($splitDetailsFinal[2])) ? ($roleDetails[$splitDetailsFinal[0]]['color'] = $splitDetailsFinal[2] ) : '';
        }

        $splitDetails = array_unique($splitDetails);
        $returnData = ($getIdFlag) ? $splitIdDetails : ( ($getArrayFlag) ? $roleDetails : implode(', ', $splitDetails));

        return $returnData;
    }

    /**
     * This function is used to club details data.
     *
     * @return array
     */
    public function getclubDetailsData()
    {
        $club = $this->container->get('club');
        $clubHeirarchy = $club->get('clubHeirarchyDet');
        $clubTitles = array();
        $entityManager = $this->getDoctrine()->getManager();
        foreach ($clubHeirarchy as $clubId => $clubArr) {
            $clubTitles[$clubId]['title'] = ucfirst($clubArr['title']);
            $clubTitles[$clubId]['clubType'] = $clubArr['club_type'];
            $clubTitles[$clubId]['clubLogoPath'] = FgUtility::getClubLogo($clubId, $entityManager);
        }
        $clubTitles[$club->get('id')]['title'] = ucfirst($club->get('title'));
        $clubTitles[$club->get('id')]['clubType'] = 'club';
        $clubTitles[$club->get('id')]['clubLogoPath'] = FgUtility::getClubLogo($this->container->get('club')->get('id'), $entityManager);

        return $clubTitles;
    }

    /**
     * This function is used for checking the user rights for detail page.
     *
     * @param string $eventId         eventId detail id
     * @param array  $eventDetails    event Details array
     * @param string $applicationArea internal/website
     *
     * @return bool
     */
    private function checkingRights($eventId, $eventDetails, $applicationArea)
    {
        $eventExistobj = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgEmCalendarDetails')->find($eventId);
        $permissionObj = new FgPermissions($this->container);
        if (empty($eventExistobj)) {
            $permissionObj->checkClubAccess('', '', '');
        } else {
            //in website if the user is not logged in, he should see only public events
            $this->checkScopeInWebsite($eventDetails['scope'], $applicationArea, $permissionObj);
            $contact = $this->get('contact');
            /* Admin flag is set for Super admin, Club admin and Club Calendar admin */
            $isMainAdmin = in_array('ROLE_CALENDAR', $contact->get('availableUserRights')) ? 1 : 0; //Check if club calendar admin
            /* Ends here */
            $teams = array_keys($contact->get('teams'));
            $workgroups = array_keys($contact->get('workgroups'));
            $roleIds = array_merge($teams, $workgroups);
            $eventRoles = $this->getseperatedCategoryandRoleData($eventDetails['eventRoleAreas'], true);
            $acessibleAreaCount = ($isMainAdmin == 1) ? 1 : count(array_intersect($roleIds, $eventRoles));
            if ($eventDetails['clubId'] == $this->container->get('club')->get('id')) {
                $this->groupEventAccessCheck($eventDetails['scope'], $acessibleAreaCount, $permissionObj);
            } else {
                $this->checkNonClubAccess($eventDetails, $acessibleAreaCount, $permissionObj);
            }
        }

        return true;
    }

    /**
     * This function is used for checking the user rights for detail page.
     * 
     * @param array  $eventDetails       Event details
     * @param int    $acessibleAreaCount Area count
     * @param object $permissionObj      Permission object
     * 
     * @return boolean
     */
    private function checkNonClubAccess($eventDetails, $acessibleAreaCount, $permissionObj)
    {
        if ($eventDetails['shareWithLower'] == 1) {
            $clubType = $this->container->get('club')->get('type');
            $fedId = $this->container->get('club')->get('federation_id');
            $subFedId = $this->container->get('club')->get('sub_federation_id');
            if ($clubType == 'sub_federation_club') {
                if (!(($eventDetails['clubId'] == $fedId) || ($eventDetails['clubId'] == $subFedId))) {
                    $permissionObj->checkUserAccess('', '', '');
                } else {
                    $this->groupEventAccessCheck($eventDetails['scope'], $acessibleAreaCount, $permissionObj);
                }
            } elseif ($clubType == 'federation_club' || $clubType == 'sub_federation') {
                if (!($eventDetails['clubId'] == $fedId)) {
                    $permissionObj->checkUserAccess('', '', '');
                } else {
                    $this->groupEventAccessCheck($eventDetails['scope'], $acessibleAreaCount, $permissionObj);
                }
            }
        }

        return true;
    }

    /**
     * If the user is not logged in, hshould see only public events in website. Otherwise show 403 forbidden
     * 
     * @param string $scope           scope of event
     * @param string $applicationArea internal/website
     * @param object $permissionObj   permissionCheck object
     */
    private function checkScopeInWebsite($scope, $applicationArea, $permissionObj)
    {
        $contactId = $this->container->get('session')->get('loggedClubUserId', 0);
        if ((!($contactId) || !($this->container->get('security.token_storage')->getToken()) ) && ($scope != 'PUBLIC') && ($applicationArea == 'website')) {
            $permissionObj->checkClubAccess('', '', '');
        }
    }

    /**
     * Function to check user rights for events with scope group.
     *
     * @param string $scope              event scope
     * @param int    $acessibleAreaCount accessbile area count for logged in user
     * @param object $permissionObj      permissions object
     *
     * @return bool
     */
    private function groupEventAccessCheck($scope, $acessibleAreaCount, $permissionObj)
    {
        if ($scope == 'GROUP') {
            if (!$acessibleAreaCount > 0) {
                $permissionObj->checkUserAccess('', '', '');
            }
        }

        return true;
    }

    /**
     * Function to download calendar attachments.
     *
     * @return response
     */
    public function downloadCalendarAttachmentAction()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $fileName = $request->get('filename');
        $encrypted = $request->get('encrypted');
        $eventClubId = $request->get('eventclubId');
        $fileObj = new FgFileManager($this->container);

        return $fileObj->downloadFile($encrypted, $fileName, $eventClubId . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR);
    }

    /**
     * This function is used to get event details in json format.
     *
     * @param int $eventId event Id/detail id
     *
     * @return JSON Response
     */
    public function getEventDataAction($eventId)
    {
        $calendarFunctions = new CalendarFunctions($this->container);
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $eventDetails = $this->getCalendarEventData($eventId);
        $dateData = $calendarFunctions->getDateandTimeData($eventDetails, $request);
        $eventDetails['dateDetails'] = $calendarFunctions->getDateDataForDetailsPage($this->container->get('club'), $dateData['startDate'], $dateData['startTime'], $dateData['endDate'], $dateData['endTime'], $eventDetails['isAllday']);
        $eventDetails['dateDetails']['startTimestamp'] = strtotime($dateData['startDate'].' '.$dateData['startTime']);
        $eventDetails['dateDetails']['endTimestamp'] = strtotime($dateData['endDate'].' '.$dateData['endTime']);
        $eventDetails['roleNames'] = ($eventDetails['eventRoleAreas']) ? $this->getseperatedCategoryandRoleData($eventDetails['eventRoleAreas'], false) : '';
        $eventDetails['categoryNames'] = ($eventDetails['eventCategories']) ? $this->getseperatedCategoryandRoleData($eventDetails['eventCategories'], false) : '';
        $eventDetails['clubDetails'] = $this->getclubDetailsData();
        $eventDetails['currentClubId'] = $this->get('club')->get('id');
        $eventDetails['description'] = FgUtility::correctCkEditorUrl($eventDetails['description'], $this->container, $eventDetails['currentClubId'], 0, $eventDetails['clubId']);
        $descData = html_entity_decode(strip_tags($eventDetails['description']), ENT_COMPAT, 'UTF-8');
        $newData = '';
        $maxChars = 120;
        if ($descData !== '' && strlen($descData) > $maxChars) {
            $newData = preg_replace('/\s+?(\S+)?$/', '', substr($descData, 0, $maxChars));
            if (strlen($newData) < strlen($descData)) {
                $newData .= '...';
            }
        }
        $eventDetails['descriptionData'] = $newData;
        $eventDetails['eventAttachments'] = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgEmCalendarDetails')->getCalendarDetailsAttachments($eventId);

        return new JsonResponse($eventDetails);
    }
}
