<?php

namespace Internal\CalendarBundle\Util;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Common\UtilityBundle\Util\FgUtility;
use Internal\CalendarBundle\Util\CalendarRecurrence;

/**
 * A class to handle all actions in calendar events import functionality
 */
class CalendarImport
{

    private $container;
    private $club;
    private $contact;
    private $clubId;
    private $contactId;
    private $conn;
    private $scope;
    private $categoryIds = array();
    private $areaIds = array();
    private $calendarSelectedAreaValues = array();
    private $calendarSelectedCategoryValues = array();
    private $calendarDetailsI18nValues = array();
    public $totalEventsCount = 0;
    public $importedEventsCount = 0;
    private $clubLanguages = array();
    private $timezone = 'Europe/Zurich';
    private $ruleStr = '';

    /**
     * The constructor function
     * 
     * @param ContainerInterface $container   Container object
     * @param string             $scope       Scope
     * @param array              $areaIds     Area ids
     * @param array              $categoryIds Category ids
     */
    public function __construct(ContainerInterface $container, $scope = '', $areaIds = array(), $categoryIds = array())
    {
        $this->container = $container;
        $this->club = $this->container->get('club');
        $this->contact = $this->container->get('contact');
        $this->clubId = $this->club->get('id');
        $this->contactId = $this->contact->get('id');
        $this->conn = $this->container->get('database_connection');
        $this->scope = $scope;
        $this->categoryIds = $categoryIds;
        $this->areaIds = $areaIds;
        $this->clubLanguages = $this->club->get('club_languages');
        $this->timezone = $this->container->getParameter("calendarGlobalTimeZone");
    }

    /**
     * This function is used to process each events in the .ics file and insert and polpulate corresponding entries.
     * 
     * @param object $eventObj Event obj from .ics file
     */
    public function processEvent($eventObj)
    {
        $this->totalEventsCount++;
        $dtstart = $eventObj->getProperty("dtstart");
        $title = $eventObj->getProperty("summary");

        //insert new event only if title and startdate are not empty
        if ($dtstart !== false && $title !== false) {
            $this->importedEventsCount++;
            $rrule = $eventObj->getProperty('rrule');
            $ruleId = '';
            $untillDate = '';
            //rules are there only for repeating events
            if (count($rrule) > 0) {
                if (isset($rrule['FREQ']) && $rrule['FREQ'] != '' && in_array($rrule['FREQ'], array('DAILY', 'WEEKLY', 'MONTHLY', 'YEARLY'))) {
                    $ruleId = $this->insertToCalendarRule($rrule);
                }
            }
            //check whether the event is an allday event
            $isAllDay = $this->checkIsAllDay($eventObj);
            if (isset($rrule['UNTIL'])) {
                $untillDate = $rrule['UNTIL'];
//              timezone modification added for untill date
                if ($untillDate != '' && $this->timezone != 'UTC' && isset($untillDate['tz'])) {
                    \iCalUtilityFunctions::transformDateTime($untillDate, 'UTC', $this->timezone);
                    \iCalUtilityFunctions::_strDate2arr($untillDate);
                }
                $untillDate = ($this->getDateFormatted($untillDate) == '0000-00-00 00:00:00') ? '' : $this->getDateFormatted($untillDate);
            } else {
                if (count($rrule) > 0) {
                    $calendarRecurrObj = new CalendarRecurrence($this->ruleStr, $this->getDateFormatted($dtstart)); // rule, startdate
                    $recurrences = $calendarRecurrObj->getNextRecurrences($event->startDate);
                    $lastRecurrence = array_pop($recurrences);
                    $untillDate = $lastRecurrence['recurrenceEndDate'];
                }
            }
            //insert to calendar table 
            $calendarId = $this->insertToCalendar($ruleId, $untillDate, $isAllDay);
            //insert to calendar details table
            $calendarDetailsId = $this->insertToCalendarDetails($calendarId, $eventObj, $untillDate, $ruleId, $isAllDay);
            //populate area table data
            $this->populateCalendarSelectedAreaValues($calendarDetailsId);
            //populate category table data
            $this->populateCalendarSelectedCategoryValues($calendarDetailsId);
        }
    }

    /**
     * This function is used to insert entry in rules table.
     * 
     * @param array $rules Rules array from .ics file
     * 
     * @return int $ruleId Inserted RuleId
     */
    public function insertToCalendarRule($rules = array())
    {
        $ruleQry = "";
        $ruleColumns = array();
        $ruleValues = array();
        if (isset($rules['FREQ']) && $rules['FREQ'] != '') {
            $ruleColumns[] = '`FREQ`';
            $ruleValues[] = $rules['FREQ'];
            $this->ruleStr .= 'FREQ=' . $rules['FREQ'] . ';';
        }
        if (isset($rules['BYDAY'])) {
            $ruleColumns[] = '`BYDAY`';
            $byDayStr = $this->getByDates($rules['BYDAY'], 'BYDAY');
            $ruleValues[] = $byDayStr;
            $this->ruleStr .= 'BYDAY=' . $byDayStr . ';';
        }
        if (isset($rules['INTERVAL']) && $rules['INTERVAL'] != '') {
            $ruleColumns[] = '`INTERVAL`';
            $ruleValues[] = $rules['INTERVAL'];
            $this->ruleStr .= 'INTERVAL=' . $rules['INTERVAL'] . ';';
        }
        if (isset($rules['BYMONTHDAY'])) {
            $ruleColumns[] = '`BYMONTHDAY`';
            $byMonthDayStr = $this->getByDates($rules['BYMONTHDAY'], 'BYMONTHDAY');
            $ruleValues[] = $byMonthDayStr;
            $this->ruleStr .= 'BYMONTHDAY=' . $byMonthDayStr . ';';
        }
        if (isset($rules['BYMONTH'])) {
            $ruleColumns[] = '`BYMONTH`';
            $byMonthStr = $this->getByDates($rules['BYMONTH'], 'BYMONTH');
            $ruleValues[] = $byMonthStr;
            $this->ruleStr .= 'BYMONTH=' . $byMonthStr . ';';
        }
        if (isset($rules['COUNT'])) {
            $this->ruleStr .= 'COUNT=' . $rules['COUNT'] . ';';
        }

        $ruleId = '';
        if (count($ruleColumns) > 0) {
            $ruleQry = "INSERT INTO fg_em_calendar_rules (";
            $ruleQry .= implode(',', $ruleColumns);
            $ruleQry .= ") VALUES ('" . implode("','", $ruleValues) . "')";
            $this->conn->exec($ruleQry);
            $ruleId = $this->conn->lastInsertId();
        }

        return $ruleId;
    }

    /**
     * This function is used to insert to calendar table
     * 
     * @param int  $ruleId     RuleId
     * @param date $untillDate Untilldate
     * @param int  $isAllDay   All day event or not
     * 
     * @return int $calendarId Inserted CalendarId
     */
    public function insertToCalendar($ruleId = '', $untillDate = '', $isAllDay = 0)
    {
        $isRepeat = ($ruleId != '') ? 1 : 0;
        $ruleId = ($ruleId != '') ? $ruleId : "NULL";
        $untillDate = ($untillDate != '') ? "'" . $untillDate . "'" : "NULL";
        $calendarQry = "INSERT INTO fg_em_calendar(club_id, calendar_rules_id, scope, is_allday, is_repeat, repeat_untill_date, created_at, created_by) VALUES (";
        $calendarQry .= "$this->clubId, $ruleId, '$this->scope', $isAllDay, $isRepeat, $untillDate, NOW(), $this->contactId)";
        $this->conn->exec($calendarQry);
        $calendarId = $this->conn->lastInsertId();

        return $calendarId;
    }

    /**
     * This function is used to insert entry in calendar details table.
     * 
     * @param int    $calendarId CalendarId
     * @param object $eventObj   Event obj
     * @param date   $untillDate Untill date
     * @param int    $ruleId     RuleId
     * @param int    $isAllDay   All day event or not
     * 
     * @return int $calendarDetailsId Inserted calendar details id
     */
    public function insertToCalendarDetails($calendarId, $eventObj, $untillDate = '', $ruleId = '', $isAllDay = 0)
    {
        $title = ($eventObj->getProperty("summary")) ? $eventObj->getProperty("summary") : '';
        $title = str_replace("'", "\'", $title);
//      timezone modification added for start date
        $dtstart = ($eventObj->getProperty("dtstart")) ? $eventObj->getProperty("dtstart") : '';
        if ($dtstart != '' && $this->timezone != 'UTC' && isset($dtstart['tz'])) {
            //  \iCalUtilityFunctions::transformDateTime( $dtstart, 'UTC', $this->timezone);
            \iCalUtilityFunctions::_strDate2arr($dtstart);
        }
        $dtstart = ($dtstart != '') ? $this->getDateFormatted($dtstart) : '';
//      timezone modification added for end date    
        $dtend = ($eventObj->getProperty("dtend")) ? $eventObj->getProperty("dtend") : '';
        if ($dtend != '' && $this->timezone != 'UTC' && isset($dtend['tz'])) {
            // \iCalUtilityFunctions::transformDateTime( $dtend, 'UTC', $this->timezone);
            \iCalUtilityFunctions::_strDate2arr($dtend);
        }
        if(($dtend != '')){
            if(strtotime($dtstart) > strtotime($dtend)){
                //if start date greater than end date -  add 1hr  to start date 
                 $dtend = date('Y-m-d H:i:s', (strtotime($dtstart) + 3600));
            }else{
                $dtend = $this->getDateFormatted($dtend) ;
            }
        }else{
             //if end date is null, add 1 hr to start date and save it as end date
            $dtend = date('Y-m-d H:i:s', (strtotime($dtstart) + 3600));
        }
        //if all day then subtract 1 s for end date
        if ($isAllDay) {
            $dtendObj = \DateTime::createFromFormat('Y-m-d H:i:s', $dtend);
            $dtendObj->modify("-1 second");
            $dtend = $dtendObj->format('Y-m-d H:i:s');
        }
        $location = $eventObj->getProperty("LOCATION");
        $location = str_replace("'", "\'", $location);
        $description = $eventObj->getProperty("description");
        $description = str_replace("\\n", "<br/>", $description);
        $description = str_replace("'", "\'", $description);
        $status = ($ruleId != '') ? 0 : 1;
        $untillDate = ($untillDate != '') ? "'" . $untillDate . "'" : "NULL";

        $calendarDetailsQry = "INSERT INTO fg_em_calendar_details(calendar_id, title, start_date, end_date, untill, location, description, created_by, created_at, status) VALUES (";
        $calendarDetailsQry .= "$calendarId, '$title', '$dtstart', '$dtend', $untillDate, '$location', '$description', $this->contactId, NOW(), $status)";
        if ($calendarDetailsQry != '') {
            $this->conn->exec($calendarDetailsQry);
            $calendarDetailsId = $this->conn->lastInsertId();
        }
        //populate calendar details i18n table entries
        $this->populateCalendarDetailsI18nValues($calendarDetailsId, $title, $description);

        return $calendarDetailsId;
    }

    /**
     * This function is used to insert data in calendar details i18n table.
     */
    public function insertToCalendarDetailsI18n()
    {
        if (count($this->calendarDetailsI18nValues) > 0) {
            $calendarDetailsI18nQry = "INSERT INTO fg_em_calendar_details_i18n(id, title_lang, lang, desc_lang) VALUES ";
            $calendarDetailsI18nQry .= implode(',', $this->calendarDetailsI18nValues);
            $this->conn->exec($calendarDetailsI18nQry);
        }
    }

    /**
     * This function is used to insert data in calendar selected areas table.
     */
    public function insertToCalendarSelectedAreas()
    {
        if (count($this->calendarSelectedAreaValues) > 0) {
            $calendarAreaQry = "INSERT INTO fg_em_calendar_selected_areas(calendar_details_id, role_id, is_club) VALUES ";
            $calendarAreaQry .= implode(',', $this->calendarSelectedAreaValues);
            $this->conn->exec($calendarAreaQry);
        }
    }

    /**
     * This function is used to insert data in calendar selected categories table.
     */
    public function insertToCalendarSelectedCategories()
    {
        if (count($this->calendarSelectedCategoryValues) > 0) {
            $calendarCategoryQry = "INSERT INTO fg_em_calendar_selected_categories(calendar_details_id, category_id) VALUES ";
            $calendarCategoryQry .= implode(',', $this->calendarSelectedCategoryValues);
            $this->conn->exec($calendarCategoryQry);
        }
    }

    /**
     * This function is used to populate calendar details i18n data
     * 
     * @param int    $calendarDetailsId Calendar details id
     * @param string $title             Title
     * @param string $description       Description
     */
    private function populateCalendarDetailsI18nValues($calendarDetailsId, $title, $description)
    {
        foreach ($this->clubLanguages as $clubLanguage) {
            $this->calendarDetailsI18nValues[] = "($calendarDetailsId, '$title', '$clubLanguage', '$description')";
        }
    }

    /**
     * This function is used to populate calendar selected area data
     * 
     * @param int $calendarDetailsId Calendar details id
     */
    private function populateCalendarSelectedAreaValues($calendarDetailsId)
    {
        foreach ($this->areaIds as $areaId) {
            $roleId = ($areaId == 0) ? 'NULL' : $areaId;
            $is_club = ($areaId == 0) ? 1 : 0;
            $this->calendarSelectedAreaValues[] = "($calendarDetailsId, $roleId, $is_club)";
        }
    }

    /**
     * This function is used to populate calendar selected category data
     * 
     * @param int $calendarDetailsId Calendar details id
     */
    private function populateCalendarSelectedCategoryValues($calendarDetailsId)
    {
        foreach ($this->categoryIds as $categoryId) {
            $this->calendarSelectedCategoryValues[] = "($calendarDetailsId, $categoryId)";
        }
    }

    /**
     * This function is used to check whether the event is an all day event ie. with no time.
     * 
     * @param object $eventObj Event object
     * 
     * @return int $isAllDay
     */
    public function checkIsAllDay($eventObj)
    {
        $isAllDay = 0;
        $dateArr = ($eventObj->getProperty("dtstart"));
        $isAllDay = (isset($dateArr['hour'])) ? 0 : 1;

        return $isAllDay;
    }

    /**
     * This function is used to get the count of actual and total imported events.
     * 
     * @return array $countDetails 
     */
    public function getImportedEventsCount()
    {
        $countDetails = array('totalCnt' => $this->totalEventsCount, 'importedCount' => $this->importedEventsCount);

        return $countDetails;
    }

    /**
     * This function is used to get the date in correct format
     * 
     * @param array $dateArr Date array with values of year, month etc.
     * 
     * @return string $date Date
     */
    private function getDateFormatted($dateArr = array())
    {
        $date = '0000-00-00 00:00:00';
        if (count($dateArr) > 0) {
            $date = $dateArr['year'] . '-' . $dateArr['month'] . '-' . $dateArr['day'];
            if (isset($dateArr['hour'])) {
                $date .= ' ' . $dateArr['hour'] . ':' . $dateArr['min'] . ':' . $dateArr['sec'];
            } else {
                $date .= ' 00:00:00';
            }
        }

        return $date;
    }
    private function checkValidDtEnd($stDateArr,$enDateArr){
        if (count($stDateArr) > 0 && count($enDateArr) > 0 ) {
            if($stDateArr['year'] > $enDateArr['year']){
                $flag = 0;
            }else if($stDateArr['year'] < $enDateArr['year']){
                $flag = 1;
            }else{
                if($stDateArr['month'] > $enDateArr['month']){
                    $flag = 0;
                }else if($stDateArr['month'] < $enDateArr['month']){
                    $flag = 1;
                }else{
                    if($stDateArr['day'] > $enDateArr['day']){
                        $flag = 0;
                    }else if($stDateArr['day'] < $enDateArr['day']){
                        $flag = 1;
                    }else{
                        
                    }
                }
            }
        }
    }
    /**
     * This function is used to get the dates comma seperated to inserted in rules table
     * 
     * @param array  $byDates  Array of data
     * @param string $type     ByDay/ByMonth/ByMonthDay
     * 
     * @return string $byDate Comma seperated days.
     */
    private function getByDates($byDates, $type = 'BYDAY')
    {
        switch ($type) {
            case 'BYDAY':
                if (!isset($byDates['DAY'])) {
                    foreach ($byDates as $val) {
                        $val['DAY'] = (in_array($val['DAY'], array('SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA'))) ? $val['DAY'] : 'SU';
                        $day = (isset($val[0])) ? $val[0] : '';
                        $day .= $val['DAY'];
                        $byDateArr[] = $day;
                    }
                } else {
                    $byDates['DAY'] = (in_array($byDates['DAY'], array('SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA'))) ? $byDates['DAY'] : 'SU';
                    $day = (isset($byDates[0])) ? $byDates[0] : '';
                    $day .= $byDates['DAY'];
                    $byDateArr[] = $day;
                }
                break;
            case 'BYMONTH':
            case 'BYMONTHDAY':
                if (is_array($byDates)) {
                    foreach ($byDates as $val) {
                        $byDateArr[] = $val;
                    }
                } else {
                    $byDateArr[] = $byDates[$key];
                }
                break;
            default:
                break;
        }
        $byDate = implode(',', $byDateArr);

        return $byDate;
    }
    
}
