<?php

/**
 * Calendar Controller.
 *
 * This controller is used for Calendar section.
 *
 * @author     pitsolutions.ch
 *
 * @version    Fairgate V4
 */
namespace Internal\CalendarBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Common\UtilityBundle\Repository\Pdo\CalendarPdo;
use Common\UtilityBundle\Util\FgSettings;
use Common\UtilityBundle\Util\FgPermissions;
use Internal\CalendarBundle\Util\Calenderfilter;
use Common\UtilityBundle\Util\FgUtility;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Internal\CalendarBundle\Util\CalendarFunctions;

class CalendarController extends Controller
{

    /**
     * Create a Calendar Appointment.
     *
     * @return template
     */
    public function calendarAppointmentCreateAction()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $calendarFunctions = new CalendarFunctions($this->container);
        //CONTACT Details
        $contactId = $this->get('contact')->get('id');
        //CLUB DETAILS
        $club = $this->container->get('club');
        //Date & Time if not available in request, current date and time will be shown
        $dateTime = $calendarFunctions->dateTimeForCreateEdit($request);
        $teamWorkgroups = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgEmCalendar')->getMyClubAndTeamsAndWorkgroups($this->container);
        $clubTitle = (isset($teamWorkgroups['club'])) ? $teamWorkgroups['club'] : '';
        $breadCrumb = array(
            'breadcrumb_data' => array(),
            'back' => $this->generateUrl('internal_calendar_view'),
        );
        //Get all my categories to be listed in category dropdown
        $calendarCategories = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgEmCalendarCategory')->getCalendarCategories($club->get('id'), $club->get('club_default_lang'));

        $return = array('defLang' => $club->get('club_default_lang'), 'category' => json_encode($calendarCategories), 'breadCrumb' => $breadCrumb, 'clubId' => $club->get('id'), 'contactId' => $contactId,
            'clubLanguages' => json_encode($club->get('club_languages')), 'clubLanguageArr' => $club->get('club_languages'),
            'defaultClubLang' => $club->get('club_default_lang'), 'clubTitle' => $clubTitle, 'assignedTeams' => json_encode($teamWorkgroups['teams']),
            'assignedWorkgroups' => json_encode($teamWorkgroups['workgroups']), 'startDate' => $dateTime['startDate'],
            'endDate' => $dateTime['endDate'], 'startTime' => $dateTime['startTime'], 'endTime' => $dateTime['endTime'],
            'allday' => ($request->get('allday')) ? $request->get('allday') : '', 'clubType' => $club->get('type'), 'forbiddenFiletypes' => $this->container->getParameter('forbiddenFiletypes'));

        return $this->render('InternalCalendarBundle:Calendar:createAppointment.html.twig', $return);
    }

    /**
     * Edit a Calendar Appointment.
     *
     * @param int $eventId   Event Detailed id
     * @param int $duplicate duplicate id
     *
     * @return template
     */
    public function calendarAppointmentEditAction($eventId, $duplicate = '')
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $calendarFunctions = new CalendarFunctions($this->container);
        //Date & Time if not available in request, current date and time will be shown
        $dateTime = $calendarFunctions->dateTimeForCreateEdit($request);
        //CLUB DETAILS
        $club = $this->container->get('club');
        $breadCrumb = array('breadcrumb_data' => array(), 'back' => $this->generateUrl('internal_calendar_view'));
        //get my club, teams, workgroup to be listed in areas dropdown
        $teamWorkgroups = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgEmCalendar')->getMyClubAndTeamsAndWorkgroups($this->container);
        //Get all event details for calendar edit
        $eventDetails = $calendarFunctions->fetchCalendarEditDetails($eventId);
        $eventDetails[0]['description'] = FgUtility::correctCkEditorUrl($eventDetails[0]['description'], $this->container, $club->get('id'));
        $eventDetails[0]['descLang'] = FgUtility::correctCkEditorUrl($eventDetails[0]['descLang'], $this->container, $club->get('id'));

        if ($eventDetails[0]['eventRepeatUntillDate'] == '' || is_null($eventDetails[0]['eventRepeatUntillDate'])) {
            $untilDate = '';
        } else {
            $untilDt = explode(' ', $eventDetails[0]['eventRepeatUntillDate']);
            $untilDate = date_create_from_format('Y-m-d', $untilDt[0]);
            $untilDate = $untilDate->format(FgSettings::getPhpDateFormat());
        }
        //Get all my categories to be listed in category dropdown
        $calendarCategories = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgEmCalendarCategory')->getCalendarCategories($club->get('id'), $club->get('club_default_lang'));
        $return = array('clubTitle' => $teamWorkgroups['club'], 'assignedTeams' => json_encode($teamWorkgroups['teams']), 'assignedWorkgroups' => json_encode($teamWorkgroups['workgroups']),
            'breadCrumb' => $breadCrumb, 'defLang' => $club->get('club_default_lang'), 'category' => json_encode($calendarCategories),
            'clubId' => $club->get('id'), 'contactId' => $this->get('contact')->get('id'), 'clubLanguages' => json_encode($club->get('club_languages')),
            'clubLanguageArr' => $club->get('club_languages'), 'defaultClubLang' => $club->get('club_default_lang'), 'untilDate' => $untilDate, 'StartDateTime' => $request->get('startDate') . ' ' . $request->get('startTime'), 'endDateTime' => $request->get('endDate') . ' ' . $request->get('endTime'),
            'duplicate' => $duplicate, 'clubType' => $club->get('type'));

        return $this->render('InternalCalendarBundle:Calendar:editAppointment.html.twig', array('forbiddenFiletypes' => $this->container->getParameter('forbiddenFiletypes'), 'eventId' => $eventId, 'startDate' => $dateTime['startDate'], 'startTime' => $dateTime['startTime'], 'endDate' => $dateTime['endDate'], 'endTime' => $dateTime['endTime'], 'retArr' => $return, 'editArr' => $eventDetails[0]));
    }

    /**
     *  userrights page.
     *
     * @param int $role role id
     *
     * @return template
     */
    public function userrightsAction()
    {
        $permissionObj = new FgPermissions($this->container);
        $calendarFunctions = new CalendarFunctions($this->container);
        $calendarAdmin = in_array('calendar', $this->get('contact')->get('allowedModules')) ? 1 : 0;
        $newTabs = $permissionObj->checkUserAccess('calendaruserrights', $calendarAdmin);
        $breadCrumb = array('breadcrumb_data' => array());
        $clubId = $this->container->get('club')->get('id');
        $contactId = $this->container->get('contact')->get('id');
        $calendarAdmin = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:SfGuardGroup')->getCalendarAdmin($this->container->get('database_connection'), $clubId, $this->container->get('club')->get('clubTable'));
        $exclude = json_encode($calendarFunctions->getAllListingBlock($calendarAdmin), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
        $calendarAdmin = json_encode($calendarAdmin, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);

        return $this->render('InternalCalendarBundle:Calendar:indexUserrights.html.twig', array('clubId' => $clubId,
                'contactId' => $contactId, 'calendarAdmin' => $calendarAdmin, 'exclude' => $exclude,
                'loggedContactId' => $contactId,
                'breadCrumb' => $breadCrumb, 'contactNameUrl' => str_replace('25', '', $this->generateUrl('contact_names_userrights', array('term' => '%QUERY')))));
    }

    /**
     * Function is used to save user rights from user rights setting page.
     *
     * @return JSON
     */
    public function saveRoleUserRightsAction()
    {
        $calendarFunctions = new CalendarFunctions($this->container);
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $userRightsArray = json_decode($request->get('postArr'), true); // Getting save values from setting page in JSON format
        // Generating array to delete calendar admin group of a contact
        if (!empty($userRightsArray['calendar']['delete'])) {
            $formatArray['delete']['group'][$this->container->getParameter('club_calendar_admin')]['user'] = $userRightsArray['calendar']['delete'];
        }
        // Generating array when a new group is added to an already existing contact in listing
        if (!empty($userRightsArray['calendar']['admin'])) {
            $formatArray = $calendarFunctions->formatUserRightsArray($formatArray, $userRightsArray);
        }

        // Calling the function to save user rights
        $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:SfGuardGroup')->saveUserRights($this->container->get('database_connection'), $formatArray, $this->container->get('club')->get('id'), $this->container->get('contact')->get('id'), $this->container);

        return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('USER_RIGHTS_SAVED_SUCCESS')));
    }

    /**
     * Function to get all calendar events within a specified interval for the current user.
     *
     * @param Request $request Request object
     *
     * @return JsonResponse Event details array
     */
    public function getCalendarEventsAction(Request $request)
    {
        $calendarFunctions = new CalendarFunctions($this->container);
        $startDate = $request->get('startDate');
        $endDate = $request->get('endDate');
        $searchVal = str_replace('\\', '\\\\', $request->get('search'));
        $filterClass = new Calenderfilter($this->container);
        $filterClass->filterArray = json_decode($request->get('filter'), true);
        $filterCondition = $filterClass->generateFilter();
        $eventDetails = $eventDetailsWithRecurrence = array();
        if ($startDate != '' && $endDate != '') {
            $qry = $calendarFunctions->generateCalendarEventQry($startDate, $endDate, $filterCondition, $searchVal);
//execute the qry and get the results
            $calendarPdo = new CalendarPdo($this->container);
            $eventDetails = $calendarPdo->executeQuery($qry);
            //loop the results through recurr class to get all dates of repeating + non repeating events
            $eventDetailsWithRecurrence = $calendarFunctions->getRecurrenceDetailsForInterval($this->getDoctrine()->getManager(), $eventDetails, $startDate, $endDate);
        }

        return new JsonResponse($eventDetailsWithRecurrence);
    }

    /**
     * confirmation pop up for delete appoinment.
     *
     * @return template
     */
    public function confirmDeleteAppPopUpAction()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $jsonRowId = $request->get('jsonRowId');
        $finalArray = $request->get('finalArray', '{}');
        $from = $request->get('from', 'listing');

        $translation = array('popupTitleSingleNon' => $this->get('translator')->trans('CALENDAR_DELETE_APP_TITLE'), 'popupTextSingleNon' => $this->get('translator')->trans('CALENDAR_DELETE_APP_TEXT'),
            'popupTitleMultiNon' => $this->get('translator')->trans('CALENDAR_DELETE_MULTI_APP_TITLE'), 'popupTextMultiNon' => $this->get('translator')->trans('CALENDAR_DELETE_MULTI_APP_TEXT'),
            'popupTitleSingleRep' => $this->get('translator')->trans('CALENDAR_DELETE_REP_APP_TITLE'), 'popupTextSingleRep' => $this->get('translator')->trans('CALENDAR_DELETE_REP_APP_TEXT'),
            'popupTitleMultiRep' => $this->get('translator')->trans('CALENDAR_DELETE_REP_MULTI_APP_TITLE'), 'popupTextMultiRep' => $this->get('translator')->trans('CALENDAR_DELETE_REP_MULTI_APP_TEXT'),);

        $return = array('jsonRowId' => $jsonRowId, 'translation' => $translation, 'button_val' => $this->get('translator')->trans('DELETE'), 'from' => $from, 'finalArray' => $finalArray,);

        return $this->render('InternalCalendarBundle:Calendar:deleteconfirmationPopup.html.twig', $return);
    }

    /**
     * edit popup.
     *
     * @return array
     */
    public function editPopupAction()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $count = $request->get('count', 1);
        $resultArr = $request->get('editArr');

        if ($count > 1) {
            $return = array('text' => $this->get('translator')->trans('CALENDAR_EDIT_REP_APP_TEXT'), 'button_val' => $this->get('translator')->trans('CALENDAR_SAVE'),
                'title' => $this->get('translator')->trans('CALENDAR_EDIT_REP_APP_TITLE'), 'count' => $count, 'resultArr' => $resultArr,);
        } else {
            $return = array('text' => $this->get('translator')->trans('CALENDAR_EDIT_TEXT'), 'button_val' => $this->get('translator')->trans('CALENDAR_SAVE'),
                'title' => $this->get('translator')->trans('CALENDAR_EDIT_TITLE'), 'count' => $count, 'resultArr' => $resultArr,);
        }

        return $this->render('InternalCalendarBundle:Calendar:editPopup.html.twig', $return);
    }

    /**
     * Delete save.
     *
     * @return template
     */
    public function saveAppDeleteAction()
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $deleteArray = json_decode($request->get('deleteArray'));
        $choice = $request->get('choice', 'all_series');
        $from = $request->get('choice', 'listing');
        $save = $em->getRepository('CommonUtilityBundle:FgEmCalendarDetails')->deleteAppointments($this->container, $deleteArray, $choice);
        if ($from == 'listing') {
            return new JsonResponse(array('noparentload' => true, 'status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('CALENDAR_DELETED_SUCCESS')));
        } else {
            return new JsonResponse(array('sync' => 1, 'redirect' => $this->generateUrl('internal_calendar_view'), 'status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('CALENDAR_DELETED_SUCCESS')));
        }
    }

    /**
     * Templete for save category pupup.
     *
     * @param Request $request Request object
     *
     * @return Template
     */
    public function calendarCategorySaveAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $catId = $request->get('catId');
        $noParentLoad = $request->get('noParentLoad', false);
        $sidebarCreate = $request->get('sidebarCreate', false);
        $defaultLang = $request->get('defaultLang');
        $clubId = $this->container->get('club')->get('id');
        $clubLang = $this->container->get('club')->get('club_languages');
        $sortOrder = $em->getRepository('CommonUtilityBundle:FgEmCalendarCategory')->getCategoriesSortOrder($clubId);
        $popovertitle = $this->get('translator')->trans('CREATE_CALENDAR_CATEGORY_POPOVER_TITLE');
        $popovertext = $this->get('translator')->trans('CREATE_CALENDAR_CATEGORY_POPOVER_TEXT');
        $return = array('title' => $popovertitle, 'text' => $popovertext, 'catId' => $catId, 'defaultLang' => $defaultLang, 'sortOrder' => $sortOrder, 'clubLanguages' => $clubLang, 'noParentLoad' => $noParentLoad, 'sidebarCreate' => $sidebarCreate);

        return $this->render('InternalCalendarBundle:Calendar:CategorySavePopup.html.twig', $return);
    }

    /**
     * edit multiple appointment.
     *
     * @return template
     */
    public function multiEditAppAction()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $jsonRowIds = $request->get('jsonRowId');
        $calendarDatas = $request->get('calandarData');
        $club = $this->container->get('club');
        $defLang = $club->get('default_lang');
        $contact = $this->get('contact');
        $count = count((array) json_decode($jsonRowIds), 0);
        if ($count < 1) {
            $url = $this->generateUrl('internal_calendar_view');

            return new RedirectResponse($url);
        }
        $calendarAdmin = in_array('calendar', $contact->get('allowedModules')) ? 1 : 0;

        $backLink = $this->generateUrl('internal_calendar_view');
        $breadCrumb = array('breadcrumb_data' => array(), 'back' => $backLink);
        $areas = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgEmCalendar')->getMyClubAndTeamsAndWorkgroups($this->container);

        $return = array('jsonRowIds' => $jsonRowIds, 'backLink' => $backLink, 'assignedTeams' => json_encode($areas['teams']),
            'defLang' => $defLang, 'count' => $count, 'breadCrumb' => $breadCrumb, 'isAdmin' => $calendarAdmin,
            'calendarDatas' => $calendarDatas, 'assignedWorkgroups' => json_encode($areas['workgroups']), 'clubTerminology' => json_encode($areas['club']),);

        return $this->render('InternalCalendarBundle:Calendar:multiEditApp.html.twig', $return);
    }

    /**
     * common function to get  categories.
     *
     * @return array
     */
    public function getCategoriesAction()
    {
        $club = $this->container->get('club');
        $clubId = $club->get('id');
        $defLang = $club->get('default_lang');
        $calendarCategories = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgEmCalendarCategory')->getCalendarCategories($clubId, $defLang);

        return new JsonResponse($calendarCategories);
    }
}
