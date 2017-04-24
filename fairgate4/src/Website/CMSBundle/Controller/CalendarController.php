<?php

/**
 * CalendarController.
 */
namespace Website\CMSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Common\FilemanagerBundle\Util\FgFileManager;
use Common\UtilityBundle\Repository\Pdo\CalendarPdo;
use Common\UtilityBundle\Util\FgSettings;
use Common\UtilityBundle\Util\FgUtility;
use Internal\CalendarBundle\Util\CalenderEvents;
use Internal\CalendarBundle\Util\CalendarRecurrence;
use Internal\CalendarBundle\Util\Calenderfilter;
use Website\CMSBundle\Util\FgPageElement;
use Website\CMSBundle\Util\FgWebsite;

/**
 * CalendarController.
 *
 * This controller is used for website calendar public page
 *
 * @package 	Website
 * @subpackage 	CMSBundle
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 *
 */
class CalendarController extends Controller
{
    
    /**
     * The calendar type
     * 
     * @var type 
     */
    private $calendarType;
    
    /**
     * Function to get the calendars for the special page
     *
     * @param Object $request The request object
     *
     * @return Object View Template Render Object
     */
    public function calendarSpecialPageAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $requestArray = $request->query->all();
        $clubObj = $this->container->get('club');
        $currentClubId = $clubObj->get('id');
        $clubHeirarchy = $clubObj->get('clubHeirarchyDet');
        $this->calendarType ='specialpage';

        $returnArray = $requestArray['returnArray'];
        $returnArray['pageId'] = $returnArray['pageDetails'][0]['id'];
        $websiteObj = new FgWebsite($this->container); //To show title for unassigned articles in backend
        $returnArray['calendarPageTitle'] = ($returnArray['pageTitle'] != '') ? $returnArray['pageTitle'] : $websiteObj->getPageTitle($returnArray['mainPageId']);
        $returnArray['timeperiod'] = $this->getYearsForSidebar();

        $clubTitles = array();
        //set the club heirarchy
        foreach ($clubHeirarchy as $clubId => $clubArr) {
            $clubTitles[$clubId]['title'] = ucfirst($clubArr['title']);
            $clubTitles[$clubId]['clubType'] = $clubArr['club_type'];
        }
        $areasAndcategories = $this->container->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->getSpecialPageCalendarDetails($returnArray['pageId']);
        $clubTitles[$this->container->get('club')->get('id')]['title'] = ucfirst($this->container->get('club')->get('title'));
        $clubTitles[$this->container->get('club')->get('id')]['clubType'] = 'club';
        $returnArray['clubTitles'] = $clubTitles;
        $returnArray['clubLogoUrl'] = FgUtility::getClubLogo('#dummy#', $em);
        $returnArray['clubId'] = $currentClubId;
        $returnArray['view'] = in_array($request->get('view', 'month'), array('list','month','agendaWeek'))?$request->get('view', 'month'):'month';
        $returnArray['areaFlag'] = $areasAndcategories['areaFlag'];
        $returnArray['categoryFlag'] = $areasAndcategories['categoryFlag'];
        
        return $this->render('WebsiteCMSBundle:Calendar:calendarSpecialPage.html.twig', $returnArray);
    }

    /**
     * Method to get the events for a special page
     *
     * @param object $request Request Object
     *
     * @return object JSON Response Object
     */
    public function getEventsAction(Request $request)
    {
        $pageId = $request->get('pageId');
        $filterData = $request->get('filterData', array());
        $startDate = $request->get('startDate', '');
        $endDate = $request->get('endDate', '');
        $areasAndcategories = $this->getSelectedAreasAndCategoriesForCalendar($pageId, 'page');
        $filterArray = array_filter($filterData) + $areasAndcategories;

        $searchVal = str_replace('\\', '\\\\', $request->get('search'));
        if (!empty($searchVal)) {
            $filterArray['search'] = $searchVal;
        }

        $eventDetailsWithRecurrence = $this->getCalendarFilterData($filterArray, $startDate, $endDate);
        $finalData = $this->getOptimizedCalendarData($eventDetailsWithRecurrence);
        $returnArray['calendarData'] = $finalData;
        return new JsonResponse($returnArray);
    }

    /**
     * Function to download article attachments.
     * 
     * @param Request $request Request object
     * 
     * @return Object
     */
    public function downloadArticleAttachmentAction(Request $request)
    {
        $fileManagerId = $request->get('filemanagerId');
        $clubId = $request->get('clubId');
        $fileObj = new FgFileManager($this->container);

        return $fileObj->downloadFileById($fileManagerId, $clubId . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR);
    }

    /**
     * Function to get all calendar events .
     *
     * @param $elementId element id
     *
     * @param $pageId page id
     *
     * @return JsonResponse Event details array
     */
    public function getCalendarElementEventsAction($elementId, $pageId)
    {
        $contactId = $this->container->get('contact')->get('id');
        $isPublic = ($contactId == '' || $contactId == 'NULL') ? 1 : 0;
        $this->calendarType ='element'; 
        $areasAndcategories = $this->getSelectedAreasAndCategoriesForCalendar($elementId, 'element');
        $eventDetailsWithRecurrence = $this->getCalendarFilter($areasAndcategories, $isPublic);
        $finalData = $this->getOptimizedCalendarData($eventDetailsWithRecurrence,5);
        $returnArray['calendarData'] = $finalData;
        $returnArray['elementId'] = $elementId;
        $returnArray['pageId'] = $pageId;
        $columnWidth = $this->container->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->getPageContainerColumnWidth($pageId, $elementId);
        $returnArray['clubDateFormat'] = ($columnWidth > 3) ? 'EEEE' : 'EE';
        $localeArr = FgSettings::getLocaleDetails();
        $locale = ($isPublic) ? $localeArr[$this->get('club')->get('system_lang')][0] : $localeArr[$this->get('contact')->get('default_lang')][0];
        $returnArray['clubLocale'] = $locale;
        $retun['htmlContent'] = $this->renderView('WebsiteCMSBundle:PageContentElements:templateCalendarElement.html.twig', $returnArray);
        $retun['elementType'] = 'calendar';
        $retun['elementId'] = $elementId;

        return new JsonResponse($retun);
    }

    /**
     * Function to get selected areas and categories saved for calendar element/calendar special page
     *
     * @param int    $id    element/page id
     * @param string $type  type to check calendar special page or calendar element
     *
     * @return array        areas and categories
     */
    private function getSelectedAreasAndCategoriesForCalendar($id, $type)
    {
        if ($type == 'element') {
            $selectedAreasandCategories = $this->container->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->getCalendarElementDetails($id);
        } else {
            $selectedAreasandCategories = $this->container->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->getSpecialPageCalendarDetails($id);
            unset($selectedAreasandCategories['areaFlag']);
            unset($selectedAreasandCategories['categoryFlag']);
        }
        $pageElementObj = new FgPageElement($this->container);
        
        return $pageElementObj->getAllcategoriesAndAreasForCalendar($selectedAreasandCategories);
    }
    
     /**
     * Function to get calendar events data
     *
     * @param array   $areasAndcategories   The filter array that will be used to set the events that needs to be displayed
     * @param boolean  $isPublic             whether is public
     *
     * @return array $eventDetails Event details
     */
    private function getCalendarFilter($areasAndcategories, $isPublic)
    {
        $startDate = date('Y-m-d H:i:s');
        $filterClass = new Calenderfilter($this->container);
        $filterClass->filterArray = $areasAndcategories;
        $filterCondition = $filterClass->generateFilter();
        $eventDetails = array();
        //initialize the calendar events class and get the qry.
        $calenderEventsObj = new CalenderEvents($this->container, '', '', $startDate, $isPublic);
        $calenderEventsObj->setColumns();
        $calenderEventsObj->setFrom();
        $calenderEventsObj->setCondition();
        $where = '1';
        if (count($filterCondition) > 0) {
            $where = implode(' AND ', $filterCondition);
        }
        $calenderEventsObj->addCondition($where);
        $calenderEventsObj->setGroupBy('CD.id');
        $calenderEventsObj->addOrderBy('eventSortField ASC');
        $qry = $calenderEventsObj->getResult();
        file_put_contents('query.txt', $qry);
        //execute the qry and get the results
        $calendarPdo = new CalendarPdo($this->container);
        $eventDetails = $calendarPdo->executeQuery($qry);
        //loop the results through recurr class to get all dates of repeating + non repeating events
        return $this->getRecurrenceDetails($eventDetails);
    }


    /**
     * Function to get calendar events data
     *
     * @param array   $filterArray   The filter array that will be used to set the events that needs to be displayed
     * @param string  $startDate     The date from which the events need to be taken
     * @param string  $endDate       The date to which the events need to be taken
     * @param string  $startDateTime The date to which the events need to be taken in datetime format
     *
     * @return array $eventDetails Event details
     */
    private function getCalendarFilterData($filterArray, $startDate = '', $endDate = '', $startDateTime = '')
    {
        $filterClass = new Calenderfilter($this->container);
        $filterClass->filterArray = $filterArray;
        $filterCondition = $filterClass->generateFilter();
        //initialize the calendar events class and get the qry.
        $contactId = $this->container->get('contact')->get('id');
        $isPublic = ($contactId == '' || $contactId == 'NULL') ? 1 : 0;
        $calenderEventsObj = new CalenderEvents($this->container, $startDate, $endDate, $startDateTime, $isPublic);
        $calenderEventsObj->setColumns();
        $calenderEventsObj->setFrom();
        $calenderEventsObj->setCondition();
        $where = '1';

        if (count($filterCondition) > 0) {
            $where = implode(' AND ', $filterCondition);
        }

        if (isset($filterArray['search']) && $filterArray['search'] != '') {
            $calenderEventsObj->searchval = $filterArray['search'];
            $calenderEventsObj->setSearchFields();
        }

        $calenderEventsObj->addCondition($where);
        $calenderEventsObj->setGroupBy('CD.id');
        $calenderEventsObj->addOrderBy('eventSortField ASC');
        $query = $calenderEventsObj->getResult();
        //execute the qry and get the results
        $calendarPdo = new CalendarPdo($this->container);
        $eventDetails = $calendarPdo->executeQuery($query);
        //loop the results through recurr class to get all dates of repeating + non repeating events
        return $this->getRecurrenceDetails($eventDetails);
    }

    /**
     * Method to add recurrence periods of repeating events.
     * Here loop each event detail and if it is repeating, add the recurrence between the interval to the array.
     * Also add 'hasEditRights' to the array & Also check whether assigned role is active, if it is not remove it.
     *
     * @param array $eventDetails event details
     *
     * @return array recurrence evnts array
     */
    private function getRecurrenceDetails($eventDetails)
    {
         $result = array();
        $calendarObj = new CalendarRecurrence();
        foreach ($eventDetails as $eventDetail) {
            /* Deleted items ($eventDetail['eventDetailType'] == '2' ) are excluded from list */
            if ($eventDetail['eventDetailType'] == "0") { //repeating events
                $calendarObj->recurrenceRule = $eventDetail['eventRules'];
                $calendarObj->setStartDate($eventDetail['startDate']);
                if ($eventDetail['endDate']) {
                    $calendarObj->setEndDate($eventDetail['endDate']);
                }
                if ($eventDetail['eventDetailUntillDate']) {
                    $calendarObj->setUntilDate($eventDetail['eventDetailUntillDate']);
                } else { //case until date is null
                }
                $recurrences = $calendarObj->getRecurrenceAfter($eventDetail['intervalStartDate'], $eventDetail['eventRepeatUntillDate']);
                foreach ($recurrences as $recurrence) {
                    $eventDetail['startDate'] = $recurrence['recurrenceStartDate'];
                    $eventDetail['endDate'] = $recurrence['recurrenceEndDate'];
                    $result[] = $eventDetail;
                }
            } elseif ($eventDetail['eventDetailType'] == "1") { //non-repeating events
                $result[] = $eventDetail;
            } else {

            }
        }

        return $result;
    }

    /**
     * Method to get the optimized result data for showing calendar element preview
     *
     * @param array $eventDetails optimized event details
     * @param int   $limit       set limit to number of event to display 
     * 
     * @return array calendar event details
     */
    public function getOptimizedCalendarData($eventDetails, $limit = '')
    {
        foreach ($eventDetails as $key => $details) {
            $eventDetails[$key]['dateDetails'] = $this->getDateDataForDetailsPage($details['startDate'], $details['endDate'], $details['isAllday']);
            $eventDetails[$key]['startDateTimestamp'] = strtotime($details['startDate']);
            $eventDetails[$key]['endDateTimestamp'] = strtotime($details['endDate']);
            unset($eventDetails[$key]['description']);
        }
        //order it by startDateTimestamp
        // reference http://stackoverflow.com/questions/2699086/sort-multi-dimensional-array-by-value
        usort($eventDetails, function ($a, $b) {
            return $a['startDateTimestamp'] - $b['startDateTimestamp'];
        });
        if ($limit != '') {
            $eventDetails = array_slice($eventDetails, 0, $limit);
        }
        return $eventDetails;
    }

    /**
     * Function to get date data details in details page
     *
     * @param int $startDateVal  start date
     * @param int $endDateVal    end date
     * @param int $isAllday      is event a all day event or not
     *
     * @return array date array for calendar events
     */
    public function getDateDataForDetailsPage($startDateVal, $endDateVal, $isAllday)
    {
        $dateArray = array();
        $startDate = date('Y-m-d', strtotime($startDateVal));
        $endDate = date('Y-m-d', strtotime($endDateVal));
        $startTime = date('H:i:s', strtotime($startDateVal));
        $endTime = date('H:i:s', strtotime($endDateVal));
        if ($isAllday == 1) {
            if ($startDate == $endDate) {
                $dateArray['startDate'] = $this->get('club')->formatDate($startDate, 'date', 'Y-m-d');
            } else {
                $dateArray['startDate'] = $this->get('club')->formatDate($startDate, 'date', 'Y-m-d');
                $dateArray['endDate'] = $this->get('club')->formatDate($endDate, 'date', 'Y-m-d');
            }
        } else {
            if ($startDate == $endDate) {
                $dateArray['startDate'] = $this->get('club')->formatDate($startDate, 'date', 'Y-m-d') . ', ' . $this->get('club')->formatDate($startTime, 'time', 'H:i:s') . ' - ' . $this->get('club')->formatDate($endTime, 'time', 'H:i:s');
            } else {
                $dateArray['startDate'] = $this->get('club')->formatDate($startDateVal, 'datetime');
                $dateArray['endDate'] = $this->get('club')->formatDate($endDateVal, 'datetime');
            }
        }

        return $dateArray;
    }

    /**
     * Method get the years for the sidebar.
     *
     *
     * @return array
     */
    private function getYearsForSidebar()
    {
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
        $defaultyearArray['label'] = $this->get('translator')->trans('CL_ONE_YEAR_FROM_TODAY');
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
            $dateArray[] = $tempArray;

            $startDateObj->add($interval);
            $endDateObj->add($interval);

            //Show next two years
            if ($startDateObj > $today) {
                $futureDate++;
            }
        } while ($futureDate <= 2);
        return $dateArray;
    }
}