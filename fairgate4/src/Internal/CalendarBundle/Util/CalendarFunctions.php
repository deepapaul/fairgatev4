<?php

namespace Internal\CalendarBundle\Util;

use Common\UtilityBundle\Util\FgPermissions;
use Internal\CalendarBundle\Util\CalendarRecurrence;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Util\FgSettings;
use Internal\CalendarBundle\Util\CalenderEvents;
use Common\UtilityBundle\Repository\Pdo\CalendarPdo;
use Internal\CalendarBundle\Util\Calenderfilter;

/**
 * The class is used to declare common functions that are used for calender functionalities
 *
 * @author Ravikumar P S
 */
class CalendarFunctions
{

    /**
     * The container object
     * 
     * @var object 
     */
    private $container;

    /**
     * The constructor function
     * 
     * @param Object $container Container object
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * Function to get date data to pass in edit page.
     *
     * @param int $startDate start date
     * @param int $startTime start time
     * @param int $endDate   end date
     * @param int $endTime   end time
     *
     * @return array
     */
    public function getDateDataForEditPage($startDate, $startTime, $endDate, $endTime)
    {
        $dateArray = array();
        $dateArray['startDate'] = $startDate;
        $dateArray['startTime'] = $startTime;
        $dateArray['endDate'] = $endDate;
        $dateArray['endTime'] = $endTime;

        return $dateArray;
    }

    /**
     * Function to get date data details in details page.
     *
     * @param Object $club      Club object
     * @param int    $startDate start date
     * @param int    $startTime start time
     * @param int    $endDate   end date
     * @param int    $endTime   end time
     * @param int    $isAllday  is event a all day event or not
     *
     * @return array
     */
    public function getDateDataForDetailsPage($club, $startDate, $startTime, $endDate, $endTime, $isAllday)
    {
        $dateArray = array();
        if ($isAllday == 1) {
            if ($startDate == $endDate) {
                $dateArray['startDate'] = $club->formatDate($startDate, 'date', 'Y-m-d');
            } else {
                $dateArray['startDate'] = $club->formatDate($startDate, 'date', 'Y-m-d');
                $dateArray['endDate'] = $club->formatDate($endDate, 'date', 'Y-m-d');
            }
        } else {
            if ($startDate == $endDate) {
                $dateArray['startDate'] = $club->formatDate($startDate, 'date', 'Y-m-d') . ', ' . $club->formatDate($startTime, 'time', 'H:i:s') . ' - ' . $club->formatDate($endTime, 'time', 'H:i:s');
            } else {
                $dateArray['startDate'] = $club->formatDate($startDate . ' ' . $startTime, 'datetime');
                $dateArray['endDate'] = $club->formatDate($endDate . ' ' . $endTime, 'datetime');
            }
        }

        return $dateArray;
    }

    /**
     * Function to get  the date and time data for details page.
     *
     * @param array  $eventDetails    event details array
     * @param object $request         request object
     * @param string $applicationArea internal/website
     *
     * @return array
     */
    public function getDateandTimeData($eventDetails, $request, $applicationArea = 'internal')
    {
        $permissionObj = new FgPermissions($this->container);
        $startDateTimeStampValue = $request->get('startTimeStamp');
        $endDateTimeStampValue = $request->get('endTimeStamp');
        //To make timestamp from server side for popover week and month view. FAIR-2689
        if ($request->get('serverTimestamp') == '1') {
            $startDateTimeStampValue = strtotime($request->get('startDateTime'));
            $endDateTimeStampValue = strtotime($request->get('endDateTime'));
        }
        if ((!(is_numeric($startDateTimeStampValue)) || !(is_numeric($endDateTimeStampValue))) && ($applicationArea != 'website')) {
            $permissionObj->checkClubAccess('', '', '');
        }
        $startDateVal = date('Y-m-d H:i:s', $startDateTimeStampValue);
        $endDateVal = date('Y-m-d H:i:s', $endDateTimeStampValue);

        /**
         * FAIR-2441 Time shift on some appointments in popover and detail view kanban
         */
        $startDateValue = date('Y-m-d', strtotime($startDateVal));
        $endDateValue = date('Y-m-d', strtotime($endDateVal));
        $startTimeValue = date('H:i:s', strtotime($startDateVal));
        $endTimeValue = date('H:i:s', strtotime($endDateVal));

        $this->checkDateorTimeisValidorNot($startDateValue, $endDateValue, $startTimeValue, $endTimeValue, $permissionObj);
        if ($eventDetails['eventDetailType'] == '0') { // checks for repeating events
            $calendarObj = new CalendarRecurrence();
            $calendarObj->recurrenceRule = $eventDetails['eventRules'];
            $calendarObj->setStartDate($eventDetails['startDate']);
            if ($eventDetails['endDate']) {
                $calendarObj->setEndDate($eventDetails['endDate']);
            }

            $recurrences = $calendarObj->getRecurrenceStartsAfter($startDateValue, $eventDetails['eventRepeatUntillDate']);
            if (empty($recurrences)) {
                $permissionObj->checkClubAccess('', '', '');
            }
            $recurrenceStartDate = $recurrences['recurrenceStartDate'];
            $recurrenceEndDate = $recurrences['recurrenceEndDate'];
            $startDate = date('Y-m-d', strtotime($recurrenceStartDate));
            $endDate = date('Y-m-d', strtotime($recurrenceEndDate));
            $startTime = date('H:i:s', strtotime($recurrenceStartDate));
            $endTime = date('H:i:s', strtotime($recurrenceEndDate));
        } else {
            $eventstartDate = date('Y-m-d', strtotime($eventDetails['startDate']));
            $eventendDate = date('Y-m-d', strtotime($eventDetails['endDate']));
            $startDate = $startDateValue;
            $startTime = $startTimeValue;
            $endDate = $endDateValue;
            $endTime = $endTimeValue;
            if ($eventDetails['isAllday'] !== '1') {
                if (($eventstartDate != $startDate) || ($eventendDate != $endDate)) {
                    $permissionObj->checkClubAccess('', '', '');
                }
            }
        }

        $dateDetails = $this->getDateDataForEditPage($startDate, $startTime, $endDate, $endTime);

        return $dateDetails;
    }

    /**
     * Function to check whether the date and time passed is of correct format or not.
     *
     * @param object $request       request object
     * @param object $permissionObj permission object
     *
     * @return array
     */
    public function checkDateorTimeisValidorNot($startDateValue, $endDateValue, $startTimeValue, $endTimeValue, $permissionObj)
    {
        $startDateFormatObj = \DateTime::createFromFormat('Y-m-d', $startDateValue);
        $startTimeFormatObj = \DateTime::createFromFormat('H:i:s', $startTimeValue);
        $endDateFormatObj = \DateTime::createFromFormat('Y-m-d', $endDateValue);
        $endTimeFormatObj = \DateTime::createFromFormat('H:i:s', $endTimeValue);
        if ((!($startDateFormatObj->format('Y-m-d') == $startDateValue) || !($endDateFormatObj->format('Y-m-d') == $endDateValue) || !($startTimeFormatObj->format('H:i:s') == $startTimeValue) || !($endTimeFormatObj->format('H:i:s') == $endTimeValue))) {
            $permissionObj->checkClubAccess('', '', '');
        }

        return true;
    }

    /**
     * Date & Time if not available in request current date and time will be shown.
     *
     * @param obj $request Request
     *
     * @return array
     */
    public function dateTimeForCreateEdit($request)
    {
        $startEndDateTime = FgUtility::getStartAndEndDateOfEvent();
        $startDateObj = ($request->get('startDate') != '') ? date_create_from_format('Y-m-d', $request->get('startDate')) : date_create_from_format('Y-m-d', $startEndDateTime['start_date']);
        $endDateObj = ($request->get('endDate') != '') ? date_create_from_format('Y-m-d', $request->get('endDate')) : date_create_from_format('Y-m-d', $startEndDateTime['end_date']);
        $startTimeObj = ($request->get('startTime') != '') ? date_create_from_format('H:i:s', $request->get('startTime')) : date_create_from_format('H:i:s', $startEndDateTime['start_time']);
        $endTimeObj = ($request->get('endTime') != '') ? date_create_from_format('H:i:s', $request->get('endTime')) : date_create_from_format('H:i:s', $startEndDateTime['end_time']);

        $startDate = $startDateObj->format(FgSettings::getPhpDateFormat());
        $endDate = $endDateObj->format(FgSettings::getPhpDateFormat());
        $startTime = $startTimeObj->format(FgSettings::getPhpTimeFormat());
        $endTime = $endTimeObj->format(FgSettings::getPhpTimeFormat());

        return array('startDate' => $startDate, 'endDate' => $endDate, 'startTime' => $startTime, 'endTime' => $endTime);
    }

    /**
     * Get all event details for calendar edit.
     *
     * @param int $eventId Event Id
     *
     * @return array
     */
    public function fetchCalendarEditDetails($eventId)
    {
        $calenderEventsObj = new CalenderEvents($this->container);
        $Columns = array("(SELECT GROUP_CONCAT(DISTINCT CSC.category_id SEPARATOR '|&&&|') FROM fg_em_calendar_selected_categories AS CSC WHERE CSC.calendar_details_id = CD.id GROUP BY CD.id) AS eventCategories");
        array_push($Columns, "(SELECT GROUP_CONCAT(CDI18N.lang, '|@@@|', IFNULL(CDI18N.title_lang,'') SEPARATOR '|&&&|') FROM fg_em_calendar_details_i18n AS CDI18N WHERE CDI18N.id = CD.id) AS titleLang");
        array_push($Columns, "(SELECT GROUP_CONCAT(CDI18N.lang, '|@@@|', IFNULL(CDI18N.desc_lang,'') SEPARATOR '|&&&|') FROM fg_em_calendar_details_i18n AS CDI18N WHERE CDI18N.id = CD.id) AS descLang");
        array_push($Columns, "(SELECT GROUP_CONCAT(CDAT.id, '|@@@|', FMV.filename, '|@@@|', FMV.size, '|@@@|', CDAT.file_manager_id,'|@@@|',FM.virtual_filename,'' SEPARATOR '|&&&|') FROM fg_file_manager_version AS FMV LEFT JOIN fg_file_manager AS FM ON (FM.latest_version_id = FMV.id) LEFT JOIN fg_em_calendar_details_attachments AS CDAT ON (CDAT.file_manager_id = FM.id) WHERE CDAT.calendar_detail_id = CD.id) AS attachmentDetails");
        $calenderEventsObj->setColumns(array_merge($Columns, array('CR.FREQ', 'CR.INTERVAL', 'CR.BYDAY', 'CR.BYMONTH', 'CR.BYMONTHDAY', 'CD.location', 'CD.location_latitude', 'CD.location_longitude', 'CD.url', 'CD.is_show_in_googlemap', 'CD.title as titleMain')));
        $calenderEventsObj->setFrom();
        $calenderEventsObj->setCondition();
        $calenderEventsObj->addCondition("CD.id = $eventId");
        $qry = $calenderEventsObj->getResult();
        //execute the qry and get the results
        $calendarPdo = new CalendarPdo($this->container);
        $eventDetails = $calendarPdo->executeQuery($qry);

        return $eventDetails;
    }

    /**
     * Function is used to get all the user rights blocks as seperate array.
     *
     * @param Array $groupContacts Group-contact details
     *
     * @return Array
     */
    public function getAllListingBlock($groupContacts)
    {
        $existingUserDetails = array();
        $i = 0;
        // Looping array containing all user rights details
        foreach ($groupContacts as $groupContact) {
            $existingUserDetails = $this->assignValuesToGroup($existingUserDetails, $i, $groupContact);
            $i++;
        }

        return array('existingUserDetails' => $existingUserDetails);
    }

    /**
     * Assign vales to common array.
     *
     * @param Array $assignGroupArray Array to be generated for listing
     * @param Int   $indexVal         Index value
     * @param Array $groupContact     Query resuly value
     *
     * @return Array
     */
    public function assignValuesToGroup($assignGroupArray, $indexVal, $groupContact)
    {
        $assignGroupArray[$indexVal][0]['id'] = $groupContact['contact_id'];
        $assignGroupArray[$indexVal][0]['value'] = $groupContact['contactname'];
        $assignGroupArray[$indexVal][0]['label'] = $groupContact['contactname'];

        return $assignGroupArray;
    }

    /**
     * Function is used to re-arrange user rights array
     * @param array $formatArray     Formated array
     * @param array $userRightsArray User rights array
     * 
     * @return array
     */
    public function formatUserRightsArray($formatArray, $userRightsArray)
    {
        $groupId = $this->container->getParameter('club_calendar_admin');
        $formatArray['new']['group'][$groupId]['contact'] = array();
        foreach ($userRightsArray['calendar'] as $admin => $random) {
            foreach ($random as $key => $val) {
                foreach ($val['contact'] as $key1 => $val1) {
                    $formatArray['new']['group'][$groupId]['contact'][$val1] = $val1;
                }
            }
        }

        return $formatArray;
    }

    /**
     * Function is to generate calendar event query
     * 
     * @param string $startDate       Start date
     * @param string $endDate         End date
     * @param string $filterCondition Filter condition value
     * @param string $searchVal       Search value
     * 
     * @return string
     */
    public function generateCalendarEventQry($startDate, $endDate, $filterCondition, $searchVal)
    {
        //initialize the calendar events class and get the qry.
        $calenderEventsObj = new CalenderEvents($this->container, $startDate, $endDate);
        $calenderEventsObj->setColumns();
        $calenderEventsObj->setFrom();
        $calenderEventsObj->setCondition();
        $where = '1';
        if (count($filterCondition) > 0) {
            $where = implode(' AND ', $filterCondition);
        }
        $calenderEventsObj->addCondition($where);

        if (!empty($searchVal)) {
            $calenderEventsObj->searchval = $searchVal;
            $calenderEventsObj->setSearchFields();
        }
        $calenderEventsObj->setGroupBy('CD.id');
        $calenderEventsObj->addOrderBy('eventSortField ASC');

        $qry = $calenderEventsObj->getResult();
        file_put_contents('query.txt', $qry);

        return $qry;
    }

    /**
     * Method to add recurrence periods of repeating events.
     * Here loop each event detail and if it is repeating, add the recurrence between the interval to the array.
     * Also add 'hasEditRights' to the array & Also check whether assigned role is active, if it is not remove it.
     * Also add startTime and endTime (timestamp) to the array
     *
     * @param Object $em           Entity manager
     * @param array  $eventDetails event details
     *
     * @return array
     */
    public function getRecurrenceDetails($em, $eventDetails)
    {
        $result = array();
        foreach ($eventDetails as $eventDetail) {
            $selRoleAreaIds = explode('|&&&|', $eventDetail['eventRoleIds']);
            $hasEditRights = $em->getRepository('CommonUtilityBundle:FgEmCalendar')->checkHasEditRights($this->container, $eventDetail['clubId'], $eventDetail['isClubAreaSelected'], $selRoleAreaIds);
            $eventDetail['hasEditRights'] = $hasEditRights;
            $filteredRoles = $this->getFilteredRoles($em, $eventDetail['eventRoleIds'], $eventDetail['eventRoleAreas']);
            $eventDetail['eventRoleIds'] = $filteredRoles['eventRoleIds'];
            $eventDetail['eventRoleAreas'] = $filteredRoles['eventRoleAreas'];
            /* Deleted items ($eventDetail['eventDetailType'] == '2' ) are excluded from list */
            if ($eventDetail['eventDetailType'] == '0') { //repeating events
                $calendarObj = new CalendarRecurrence();
                $calendarObj->recurrenceRule = $eventDetail['eventRules'];
                $calendarObj->setStartDate($eventDetail['startDate']);
                if ($eventDetail['endDate']) {
                    $calendarObj->setEndDate($eventDetail['endDate']);
                }
                if ($eventDetail['eventDetailUntillDate']) {
                    $calendarObj->setUntilDate($eventDetail['eventDetailUntillDate']);
                } else { //case until date is null
                    $calendarObj->setUntilDate($eventDetail['intervalEndDate']);
                }

                $recurrences = $calendarObj->getRecurrenceBetween($eventDetail['intervalStartDate'], $eventDetail['intervalEndDate'], $eventDetail['eventRepeatUntillDate']);

                foreach ($recurrences as $recurrence) {
                    $eventDetail['startDate'] = $recurrence['recurrenceStartDate'];
                    $eventDetail['endDate'] = $recurrence['recurrenceEndDate'];
                    //add timestamp also
                    $eventDetail['startTimestamp'] = strtotime($recurrence['recurrenceStartDate']);
                    $eventDetail['endTimestamp'] = strtotime($recurrence['recurrenceEndDate']);
                    $result[] = $eventDetail;
                }
            } elseif ($eventDetail['eventDetailType'] == '1') { //non-repeating events
                   //add timestamp also
                    $eventDetail['startTimestamp'] = strtotime($eventDetail['startDate']);
                    $eventDetail['endTimestamp'] = strtotime($eventDetail['endDate']);
                    $result[] = $eventDetail;
                }
            }

        return $result;
    }
    
    /**
     * Method to add recurrence periods of repeating events for event listing.
     * Filter the events with in the given interval.
     *
     * @param Object  $em                  Entity manager
     * @param array   $eventDetails        event details
     * @param string  $intervalStartDate   Interval start date
     * @param string  $intervalEndDate     Interval end date
     *
     * @return array  $result              Recurrence details array
     */
    public function getRecurrenceDetailsForInterval($em, $eventDetails, $intervalStartDate, $intervalEndDate)
    {
        $result = array();
        $filterStartDateObj = new \DateTime($intervalStartDate);
        $filterEndDateObj = new \DateTime($intervalEndDate);
        foreach ($eventDetails as $eventDetail) {
            $eventDetail['hasEditRights'] = $em->getRepository('CommonUtilityBundle:FgEmCalendar')->checkHasEditRights($this->container, $eventDetail['clubId'], $eventDetail['isClubAreaSelected'], explode('|&&&|', $eventDetail['eventRoleIds']));
            $filteredRoles = $this->getFilteredRoles($em, $eventDetail['eventRoleIds'], $eventDetail['eventRoleAreas']);
            $eventDetail['eventRoleIds'] = $filteredRoles['eventRoleIds'];
            $eventDetail['eventRoleAreas'] = $filteredRoles['eventRoleAreas'];
            if ($eventDetail['eventDetailType'] == '0') { //repeating events
                $calendarObj = new CalendarRecurrence();
                $calendarObj->recurrenceRule = $eventDetail['eventRules'];
                $calendarObj->setStartDate($eventDetail['startDate']);
                if ($eventDetail['endDate']) {
                    $calendarObj->setEndDate($eventDetail['endDate']);
                }
                $eventDetail['eventDetailUntillDate'] ? $calendarObj->setUntilDate($eventDetail['eventDetailUntillDate']) : $calendarObj->setUntilDate($eventDetail['intervalEndDate']);
                $recDetails = $calendarObj->getRecurrenceBetween($eventDetail['intervalStartDate'], $eventDetail['intervalEndDate'], $eventDetail['eventRepeatUntillDate']);
                $formattedRecData = $this->formatRecDetails($recDetails, $eventDetail, $filterStartDateObj, $filterEndDateObj);
                $result = array_merge($result, $formattedRecData);
            } elseif ($eventDetail['eventDetailType'] == '1') { //non-repeating events
                  $endDateObj = new \DateTime($eventDetail['endDate']);
                  $startDateObj = new \DateTime($eventDetail['startDate']);
                  if($filterEndDateObj >= $startDateObj && $endDateObj >= $filterStartDateObj) {
                    //add timestamp also
                    $eventDetail['startTimestamp'] = strtotime($eventDetail['startDate']);
                    $eventDetail['endTimestamp'] = strtotime($eventDetail['endDate']);
                    $result[] = $eventDetail;
                  } 
            }
        }
        
        return $result;
    }

    /**
     * This function is used to format recurrence events for repeating events.
     * 
     * @param array $recDetails         Recurrence details
     * @param array $eventDetail        Events details array
     * @param array $filterStartDateObj Interval start date object
     * @param array $filterEndDateObj   Interval end date object
     * 
     * @return array $recResult         Filtered recurrence events
     */
    private function formatRecDetails($recDetails, $eventDetail, $filterStartDateObj, $filterEndDateObj) {
        $recResult = array();
        foreach ($recDetails as $recurrence) {
            
            $startDateObj = new \DateTime($recurrence['recurrenceStartDate']);
            $endDateObj = new \DateTime($recurrence['recurrenceEndDate']);
            if ($filterEndDateObj >= $startDateObj && $endDateObj >= $filterStartDateObj) {
                //add timestamp also
                $eventDetail['startDate'] = $recurrence['recurrenceStartDate'];
                $eventDetail['endDate'] = $recurrence['recurrenceEndDate'];
                $eventDetail['startTimestamp'] = strtotime($recurrence['recurrenceStartDate']);
                $eventDetail['endTimestamp'] = strtotime($recurrence['recurrenceEndDate']);
                $recResult[] = $eventDetail;
            }
        }
        
        return $recResult;
    }

    /**
     * Function is used to generate recurrence details result array
     * 
     * @param array $eventDetail Event details array
     * 
     * @return array
     */
    public function getRecurrenceDetailsResultArray($eventDetail)
    {
        if ($eventDetail['eventDetailType'] == '0') { //repeating events
            $calendarObj = new CalendarRecurrence();
            $calendarObj->recurrenceRule = $eventDetail['eventRules'];
            $calendarObj->setStartDate($eventDetail['startDate']);
            if ($eventDetail['endDate']) {
                $calendarObj->setEndDate($eventDetail['endDate']);
            }
            if ($eventDetail['eventDetailUntillDate']) {
                $calendarObj->setUntilDate($eventDetail['eventDetailUntillDate']);
            } else { //case until date is null
                $calendarObj->setUntilDate($eventDetail['intervalEndDate']);
            }

            $recurrences = $calendarObj->getRecurrenceBetween($eventDetail['intervalStartDate'], $eventDetail['intervalEndDate'], $eventDetail['eventRepeatUntillDate']);

            foreach ($recurrences as $recurrence) {
                $eventDetail['startDate'] = $recurrence['recurrenceStartDate'];
                $eventDetail['endDate'] = $recurrence['recurrenceEndDate'];
                //add timestamp also
                $eventDetail['startTimestamp'] = strtotime($recurrence['recurrenceStartDate']);
                $eventDetail['endTimestamp'] = strtotime($recurrence['recurrenceEndDate']);
                $result[] = $eventDetail;
            }
        } elseif ($eventDetail['eventDetailType'] == '1') { //non-repeating events
            //add timestamp also
            $eventDetail['startTimestamp'] = strtotime($eventDetail['startDate']);
            $eventDetail['endTimestamp'] = strtotime($eventDetail['endDate']);
            $result[] = $eventDetail;
        }

        return $result;
    }

    /**
     * Method to check whether event roleId contains inactive roles, If exist remove it from eventRoleIds & eventRoleAreas.
     *
     * @param object $em           Entity manager
     * @param string $strRoleIds   eventRoleIds in imploded with '|&&&|'
     * @param string $strRoleAreas Roleareadetail in the format 'roleId|@@@|roleTitle|@@@|roleColor' imploded with '|&&&|'
     *
     * @return $result array of filtered eventRoleIds & eventRoleAreas
     */
    public function getFilteredRoles($em, $strRoleIds, $strRoleAreas)
    {
        $result = array();
        /* get inactive roles of club */
        $inactiveRoles = $em->getRepository('CommonUtilityBundle:FgRmRole')->getInactiveRolesOfClub($this->container->get('club')->get('id'));
        //checking whether event roleId contains inactive roles, If exist remove it from eventRoleIds & eventRoleAreas
        $eventRoleIds = explode('|&&&|', $strRoleIds);
        $eventRoleAreas = explode('|&&&|', $strRoleAreas);
        foreach ($eventRoleIds as $key => $eventRoleId) {
            if (in_array($eventRoleId, explode(',', $inactiveRoles))) {
                unset($eventRoleIds[$key]);
            }
        }
        foreach ($eventRoleAreas as $indx => $eventRoleArea) {
            $eventRoleAreaArray = explode('|@@@|', $eventRoleArea);
            if (in_array($eventRoleAreaArray[0], explode(',', $inactiveRoles))) {
                unset($eventRoleAreas[$indx]);
            }
        }
        $result['eventRoleIds'] = implode('|&&&|', $eventRoleIds);
        $result['eventRoleAreas'] = implode('|&&&|', $eventRoleAreas);

        return $result;
    }

    /**
     * Function for create the action menu.
     *
     * @param int $adminflag
     *
     * @return array action menu array
     */
    public function getActionMenu($calendarImportEventsUrl, $adminflag = 0)
    {
        if ($adminflag == 1) {
            $actionMenuNoneSelectedText = array(
                'create' => array('isVisibleAlways' => 'true', 'title' => $this->container->get('translator')->trans('CL_CREATE'), 'hrefLink' => $this->container->get('router')->generate('calendar_appointment_create'), 'isActive' => 'true'),
                'calendarEdit' => array('title' => $this->container->get('translator')->trans('CL_EDIT'), 'hrefLink' => '', 'isActive' => 'false'),
                'duplicate' => array('title' => $this->container->get('translator')->trans('CL_DUPLICATE'), 'hrefLink' => '', 'isActive' => 'false'),
                'import' => array('isVisibleAlways' => 'true', 'title' => $this->container->get('translator')->trans('CL_IMPORT'), 'hrefLink' => $calendarImportEventsUrl, 'isActive' => 'true'),
                'calendarDelete' => array('title' => $this->container->get('translator')->trans('CL_DELETE'), 'hrefLink' => '', 'isActive' => 'false'),
            );
            $actionMenuSingleSelectedText = array(
                'create' => array('title' => $this->container->get('translator')->trans('CL_CREATE'), 'hrefLink' => $this->container->get('router')->generate('calendar_appointment_create'), 'isActive' => 'false'),
                'calendarEdit' => array('title' => $this->container->get('translator')->trans('CL_EDIT'), 'dataUrl' => '', 'isActive' => 'true'),
                'duplicate' => array('title' => $this->container->get('translator')->trans('CL_DUPLICATE'), 'dataUrl' => '', 'isActive' => 'true'),
                'import' => array('title' => $this->container->get('translator')->trans('CL_IMPORT'), 'hrefLink' => $calendarImportEventsUrl, 'isActive' => 'false'),
                'calendarDelete' => array('title' => $this->container->get('translator')->trans('CL_DELETE'), 'dataUrl' => '', 'isActive' => 'true'),
            );
            $actionMenuMultipleSelectedText = array(
                'create' => array('title' => $this->container->get('translator')->trans('CL_CREATE'), 'hrefLink' => $this->container->get('router')->generate('calendar_appointment_create'), 'isActive' => 'false'),
                'calendarEdit' => array('title' => $this->container->get('translator')->trans('CL_EDIT'), 'dataUrl' => '', 'isActive' => 'true'),
                'duplicate' => array('title' => $this->container->get('translator')->trans('CL_DUPLICATE'), 'hrefLink' => '', 'isActive' => 'false'),
                'import' => array('title' => $this->container->get('translator')->trans('CL_IMPORT'), 'hrefLink' => $calendarImportEventsUrl, 'isActive' => 'false'),
                'calendarDelete' => array('title' => $this->container->get('translator')->trans('CL_DELETE'), 'dataUrl' => '', 'isActive' => 'true'),
            );
        } else {
            $actionMenuNoneSelectedText = array();
            $actionMenuSingleSelectedText = array();
            $actionMenuMultipleSelectedText = array();
        }

        $menuArray = array('none' => $actionMenuNoneSelectedText, 'single' => $actionMenuSingleSelectedText, 'multiple' => $actionMenuMultipleSelectedText);

        return $menuArray;
    }

    /**
     * Method get the areas/category of current club for the sidebar.
     * 
     * @param Object $em Entity Manager
     * 
     * @return Array
     */
    public function getSidebarOptions($em)
    {
        $resultArray = array();
        $index = 0;

        //////////////////////////// Get Categories to be displayed in filter by hierarchywise //////////////////////////
        $categoryArray = $this->getcategoriesForSidebar($em, $index);
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////// Decide if Federation/Sub-federation/Club option need to be shown //////////////////////////
        $index += count($categoryArray);
        $hierarchyDetails = $this->getHierarchyForSidebar($index);
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //////////////////////////// Get areas to be displayed in the sidebar //////////////////////////
        $index += count($hierarchyDetails);
        $areaDetails = $this->getAreasForSidebar($index);

        $resultArray['general'] = array_merge($hierarchyDetails['general'], $areaDetails['general']);
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////// Find the years that needed to be displayed in the filter /////////////////////////////
        $index += count($areaDetails);
        $yearDetails = $this->getYearsForSidebar($index);
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //////// Get the count of events without area in current club to check whether 'without area' filter is to be shown or not /////
        $clubId = $this->container->get('club')->get('id');
        $withoutAreaEventsCount = $em->getRepository('CommonUtilityBundle:FgEmCalendar')->getCountOfEventsWithoutArea($clubId);
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //Merge all the above details
        $resultArray = array_merge($resultArray, $categoryArray, $yearDetails, $withoutAreaEventsCount);

        return $resultArray;
    }

    /**
     * Method get the areas/category of current club for the sidebar. Get the categories of each clubs in the hierarchy
     * 
     * @param Object $em    Entity Manager
     * @param int    $index Index value
     * 
     * @return array
     */
    private function getcategoriesForSidebar($em, $index)
    {
        $resultArray = array();
        $index++;

        $calendarPdo = new CalendarPdo($this->container);
        $calenderDataObj = new CalenderEvents($this->container);
        $clubObj = $this->container->get('club');
        $clubId = $clubObj->get('id');
        $defLang = $clubObj->get('default_lang');

        $clubHeirarchyDet = $clubObj->get('clubHeirarchyDet');
        $currentClubType = $clubObj->get('type');
        foreach ($clubHeirarchyDet as $club => $clubDetail) {
            $calendarCategories = $em->getRepository('CommonUtilityBundle:FgEmCalendarCategory')->getCalendarCategoriesWithEvents($club, $defLang, true);
            if (count($calendarCategories) > 0) {
                $resultArray['category'][$index]['title'] = $clubDetail['title'];
                $resultArray['category'][$index]['type'] = $clubDetail['club_type'];
                $resultArray['category'][$index]['id'] = $club;
                //get the categories for the club
                $resultArray['category'][$index]['subItems'] = $calendarCategories;
                $index++;
            }
        }

        //Get categories in my club
        //get the categories for the club
        $calendarCategories = $em->getRepository('CommonUtilityBundle:FgEmCalendarCategory')->getCalendarCategoriesWithEvents($clubId, $defLang);
        if (count($calendarCategories) > 0) {
            $resultArray['category'][$index]['title'] = $clubObj->get('title');
            $resultArray['category'][$index]['type'] = $currentClubType;
            $resultArray['category'][$index]['id'] = $clubId;
            $resultArray['category'][$index]['subItems'] = $calendarCategories;
            $index++;
        }

        //Events without categories
        $eventWithoutCategoryQuery = $calenderDataObj->getEventsWithoutCategory();
        $eventWithoutCategory = $calendarPdo->executeQuery($eventWithoutCategoryQuery);
        if ($eventWithoutCategory[0]['eventCount'] > 0) {
            $resultArray['category'][$index]['title'] = 'Without category';
            $resultArray['category'][$index]['type'] = 'withoutcategory';
            $resultArray['category'][$index]['id'] = 'withoutcategory';
        }

        return $resultArray;
    }

    /**
     * Method get the areas/category of current club for the sidebar.
     *
     * @param int $index current size of the array
     *
     * @return array
     */
    private function getHierarchyForSidebar($index)
    {
        $resultArray = array('general' => array());
        $index++;

        $calendarPdo = new CalendarPdo($this->container);
        $calenderDataObj = new CalenderEvents($this->container);
        $clubObj = $this->container->get('club');

        $clubHer = $clubObj->get('clubHeirarchyDet');
        $clubTypeTerminlogy['sub_federation_club'] = $clubObj->get('title');
        foreach ($clubHer as $key => $value) {
            $clubTypeTerminlogy[$value['club_type']] = $value['title'];
        }
        $clubId = $clubObj->get('id');

        $clubHeirarchyDet = $clubObj->get('clubHeirarchyDet');
        $currentClubType = $clubObj->get('type');

        $clubHeirarchyIncludingMyClub = $clubHeirarchyDet;
        $clubHeirarchyIncludingMyClub[$clubId] = array('club_type' => $currentClubType);
        $eventCountQuery = $calenderDataObj->getClubOptions($clubHeirarchyIncludingMyClub);
        //echo $eventCountQuery;exit;
        $eventCounts = $calendarPdo->executeQuery($eventCountQuery);
        $eventCounts = $eventCounts[0];

        if ($eventCounts['sub_federation_club'] != '' && $eventCounts['sub_federation_club'] != 0) {
            $resultArray['general'][$index]['title'] = $clubTypeTerminlogy['sub_federation_club'];
            $resultArray['general'][$index]['id'] = $eventCounts['sub_federation_club_id'];
            $resultArray['general'][$index]['type'] = 'CLUB';
            $index++;
        }
        if ($eventCounts['standard_club'] != '' && $eventCounts['standard_club'] != 0) {
            $resultArray['general'][$index]['title'] = $clubTypeTerminlogy['sub_federation_club'];
            $resultArray['general'][$index]['id'] = $eventCounts['standard_club_id'];
            $resultArray['general'][$index]['type'] = 'CLUB';
            $index++;
        }
        if ($eventCounts['federation_club'] != '' && $eventCounts['federation_club'] != 0) {
            $resultArray['general'][$index]['title'] = $clubTypeTerminlogy['sub_federation_club'];
            $resultArray['general'][$index]['id'] = $eventCounts['federation_club_id'];
            $resultArray['general'][$index]['type'] = 'CLUB';
            $index++;
        }

        if ($eventCounts['sub_federation'] != '' && $eventCounts['sub_federation'] != 0) {
            $resultArray['general'][$index]['title'] = ($currentClubType == 'sub_federation') ? $clubTypeTerminlogy['sub_federation_club'] : $clubTypeTerminlogy['sub_federation'];
            $resultArray['general'][$index]['id'] = $eventCounts['sub_federation_id'];
            $resultArray['general'][$index]['type'] = 'SUBFED';
            $index++;
        }
        if ($eventCounts['federation'] != '' && $eventCounts['federation'] != 0) {
            $resultArray['general'][$index]['title'] = ($currentClubType == 'federation') ? $clubTypeTerminlogy['sub_federation_club'] : $clubTypeTerminlogy['federation'];
            $resultArray['general'][$index]['id'] = $eventCounts['federation_id'];
            $resultArray['general'][$index]['type'] = 'FED';
            $index++;
        }

        return $resultArray;
    }

    /**
     * Method get the areas/category of current club for the sidebar.
     *
     * @param int $index current size of the array
     *
     * @return array
     */
    private function getAreasForSidebar($index)
    {
        $resultArray['general'] = array();
        $index++;

        $calendarPdo = new CalendarPdo($this->container);
        $calenderDataObj = new CalenderEvents($this->container);
        $terminologyService = $this->container->get('fairgate_terminology_service');
        $contactObj = $this->container->get('contact');

        $workgroupTitle = $this->container->get('translator')->trans('WORKGROUPS');
        $teamTitle = ucfirst($terminologyService->getTerminology('Team', $this->container->getParameter('plural')));

        //get the areas to be displayed
        $myOwnAreas = array_keys($contactObj->get('teamsExcludeForeignContactVisibility') + $contactObj->get('workgroupsExcludeForeignContactVisibility'));

        //get all teams/workgroup since the first event
        $tempArray = array();
        $eventRole = $calendarPdo->executeQuery($calenderDataObj->getRoleOptions());
        if (count($eventRole) > 0) {
            foreach ($eventRole as $role) {
                $roleIndex = ($role['type'] == 'T') ? 'team' : 'workgroup';
                $tempArray[$roleIndex][] = array('title' => $role['title'], 'id' => $role['id'], 'own' => (in_array($role['id'], $myOwnAreas) ? true : false));
            }
        }
        //if there is any entry in team
        if (count($tempArray['team']) > 0) {
            $resultArray['general'][$index]['title'] = $teamTitle;
            $resultArray['general'][$index]['id'] = 'team';
            $resultArray['general'][$index]['categoryType'] = 'group';
            $resultArray['general'][$index]['subItems'] = $tempArray['team'];
            $index++;
        }

        //if there is any entry in workgroup
        if (count($tempArray['workgroup']) > 0) {
            $resultArray['general'][$index]['title'] = $workgroupTitle;
            $resultArray['general'][$index]['id'] = 'workgroup';
            $resultArray['general'][$index]['categoryType'] = 'group';
            $resultArray['general'][$index]['subItems'] = $tempArray['workgroup'];
            $index++;
        }

        return $resultArray;
    }

    /**
     * Method get the years for the sidebar.
     *
     * @param int $index current size of the array
     *
     * @return array
     */
    private function getYearsForSidebar($index)
    {
        $resultArray = array();
        $index++;

        $calendarPdo = new CalendarPdo($this->container);
        $calenderDataObj = new CalenderEvents($this->container);
        $clubObj = $this->container->get('club');

        //Get the first event for the current user
        $firstEventArray = $calendarPdo->executeQuery($calenderDataObj->getFirstEvent());
        $firstEvent = $firstEventArray[0];
        $today = new \DateTime();
        $dateArray = array();
        $defaultyearArray['start'] = date('Y-m-d');
        $defaultyearArray['end'] = date('Y-m-d', strtotime('+1 years'));
        $defaultyearArray['label'] = 'defaultyear';
        $defaultyearArray['currentyear'] = $this->container->get('translator')->trans('CL_ONE_YEAR_FROM_TODAY');
        $dateArray[] = $defaultyearArray;

        $date = ($firstEvent['start_date'] == '') ? $today->format('Y-m-d') : $firstEvent['start_date'];
        $fiscalDateArray = $clubObj->getFiscalYearStartDate($date);
        $firstStartDate = $fiscalDateArray['start'];
        $endStartDate = $fiscalDateArray['end'];

        $startDateObj = new \DateTime($firstStartDate);
        $endDateObj = new \DateTime($endStartDate);
        $interval = new \DateInterval('P1Y');   //Add one year
        $futureDate = 0;

        //Loop from the first date of the event to the future two days
        do {
            $tempArray = array();
            $tempArray['start'] = $startDateObj->format('Y-m-d');
            $tempArray['end'] = $endDateObj->format('Y-m-d');
            $tempArray['label'] = ($startDateObj->format('Y') == $endDateObj->format('Y')) ? $startDateObj->format('Y') : $startDateObj->format('Y') . '/' . $endDateObj->format('Y');
            $tempArray['currentyear'] = 'no';
            $dateArray[] = $tempArray;

            $startDateObj->add($interval);
            $endDateObj->add($interval);

            //Show next two years
            if ($startDateObj > $today) {
                $futureDate++;
            }
        } while ($futureDate <= 2);
        $resultArray['years'] = $dateArray;

        return $resultArray;
    }

    /**
     * Function to generate calendar events data.
     *
     * @param string $eventIds  event ids
     * @param string $searchVal search value
     * @param json   $filter    filter data
     *
     * @return array
     */
    public function getCalendarEventData($eventIds, $searchVal, $filter)
    {
        if (!empty($filter)) {
            $filterClass = new Calenderfilter($this->container);
            $filterClass->filterArray = $filter;
            $filterCondition = $filterClass->generateFilter();
        }
        $calenderEventsObj = new CalenderEvents($this->container);
        $calenderEventsObj->setColumns(array('CD.location', 'CD.location_latitude', 'CD.location_longitude', 'CD.url'));
        $calenderEventsObj->setFrom();
        $calenderEventsObj->setCondition("C.id IN($eventIds)");
        if (!empty($filter)) {
            $where = '1';
            if (count($filterCondition) > 0) {
                $where = implode(' AND ', $filterCondition);
            }
            $calenderEventsObj->addCondition($where);
        }
        if ($searchVal != '') {
            $calenderEventsObj->searchval = $searchVal;
            $calenderEventsObj->setSearchFields();
        }
        $calenderEventsObj->setGroupBy('CD.id');
        $calenderEventsObj->addOrderBy('C.id ASC');

        $qry = $calenderEventsObj->getResult();

        $calendarPdo = new CalendarPdo($this->container);
        $eventDetails = $calendarPdo->executeQuery($qry);

        return $eventDetails;
    }

    /**
     * Function to generate data for the ics file.
     *
     * @param array $exportContent event data to be exported
     *
     * @return string
     */
    public function generateIcsData($exportContent)
    {
        $ics .= 'BEGIN:VCALENDAR';
        $ics .= "\nPRODID:-//" . FgUtility::getBaseUrl($this->container) . '//NONSGML iCalcreator 2.6//';
        $ics .= "\nVERSION:2.0\n";
        $uIdarray = array();

        foreach ($exportContent as $exportData) {
            $uIdarray[$exportData['eventId']] = base64_encode($exportData['eventId']);
            $ics .= "\nBEGIN:VEVENT";
            $ics .= (array_key_exists($exportData['eventId'], $uIdarray)) ? "\nUID:" . $uIdarray[$exportData['eventId']] . '' : "\nUID:" . base64_encode($exportData['eventId']) . '';
            $ics .= ($exportData['location']) ? "\nLOCATION:" . $exportData['location'] . '' : '';
            $ics .= "\nSUMMARY:" . strip_tags(trim($exportData['title'])) . '';
            if ($exportData['eventCategories'] != '') {
                $ics .= $this->getseperatedCategory($exportData['eventCategories']);
            }
            if ($exportData['eventRules']) {
                $rRule = ($exportData['eventRepeatUntillDate']) ? $exportData['eventRules'] . 'UNTIL=' . date('Ymd', strtotime($exportData['eventRepeatUntillDate'])) : $exportData['eventRules'];
                $ics .= "\nRRULE:" . $rRule . '';
            }
            $endDate = $exportData['endDate'];
            if ($exportData['isAllday'] == 1) {
                $dtendObj = \DateTime::createFromFormat('Y-m-d H:i:s', $endDate);
                $dtendObj->modify('+1 second');
                $endDate = $dtendObj->format('Y-m-d H:i:s');
            }
            $newcontent = preg_replace("/<p[^>]*?>/", "", $exportData['description']);
            $newcontent = strip_tags(implode('\n', array_map('trim', explode('</p>', $newcontent))));
            $newcontent = $this->normalizeICS($newcontent);

            $ics .= ($exportData['isAllday'] == 1) ? "\nDTSTART;VALUE=DATE:" . date('Ymd', strtotime($exportData['startDate'])) . '' : "\nDTSTART;VALUE=DATE-TIME:" . date('Ymd\THis', strtotime($exportData['startDate'])) . '';
            $ics .= ($exportData['isAllday'] == 1) ? "\nDTEND;VALUE=DATE:" . date('Ymd', strtotime($exportData['startDate'])) . '' : "\nDTEND;VALUE=DATE-TIME:" . date('Ymd\THis', strtotime($endDate)) . '';
            $ics .= ($exportData['description']) ? "\nDESCRIPTION:" . $newcontent . '' : '';
            $ics .= "\nEND:VEVENT\n";
        }
        $ics .= "\nEND:VCALENDAR";

        return $ics;
    }

    /**
     * This function is used to get event details categories for generating ics data.
     *
     * @param string $eventDetails event details containing category data
     *
     * @return string
     */
    private function getseperatedCategory($eventDetails)
    {
        $details = explode('|&&&|', $eventDetails);
        $splitDetails = array();
        foreach ($details as $key => $data) {
            $splitDetailsFinal = explode('|@@@|', $data);
            $splitDetails[$key] = $splitDetailsFinal[1];
        }

        foreach ($splitDetails as $categories) {
            $ics .= "\nCATEGORIES:" . $categories . '';
        }

        return $ics;
    }

    /**
     * Function to normalize text of ics
     * 
     * @param string $text
     * 
     * @return string
     */
    private function normalizeICS($text)
    {
        // Normalize line endings using Global
        // Convert all line-endings to UNIX format
        $text = str_replace("\r\n", "\\n", $text);
        $text = str_replace("\r", "\\n", $text);
        // Don't allow out-of-control blank lines
        $text = preg_replace("/\n{2,}/", "\\n" . "\\n", $text);

        return $text;
    }
}
