<?php

/**
 * ExportController.
 *
 * This controller used for managing the export functionality for internal calendar area
 *
 * @author     pitsolutions.ch
 *
 * @version    Fairgate V4
 */
namespace Internal\CalendarBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Internal\CalendarBundle\Util\CalenderEvents;
use Common\UtilityBundle\Repository\Pdo\CalendarPdo;
use Symfony\Component\HttpFoundation\Response;
use Internal\CalendarBundle\Util\Calenderfilter;
use Symfony\Component\HttpFoundation\Request;
use Common\UtilityBundle\Util\FgUtility;
use Internal\CalendarBundle\Util\CalendarFunctions;

/**
 * ExportController.
 *
 * This controller used for managing the export functionality for internal calendar area
 *
 * @author     pitsolutions.ch
 *
 * @version    Fairgate V4
 */
class ExportController extends Controller
{
    /**
     * Function to show export popup box.
     *
     * @param Request $request Request object
     *
     * @return template
     */
    public function calendarexportPopupAction(Request $request)
    {
        $eventData = json_decode($request->get('eventData'), true);
        $eventCount = $eventData['count'];
        $popupTitle = ($eventCount > 1) ? str_replace('%count%', $eventCount, $this->get('translator')->trans('EXPORT_CALENDAR_POPUP_TITlE_PLURAL')) : $this->get('translator')->trans('EXPORT_CALENDAR_POPUP_TITlE_SINGLE');
        $popupDesc = ($eventCount > 1) ? $this->get('translator')->trans('EXPORT_CALENDAR_POPUP_DESCRIPTION_PLURAL') : $this->get('translator')->trans('EXPORT_CALENDAR_POPUP_DESCRIPTION_SINGLE');

        return $this->render('InternalCalendarBundle:Calendar:exportConfirmationPopup.html.twig', array('eventData' => $eventData, 'eventJsonData' => $request->get('eventData'), 'title' => $popupTitle, 'desc' => $popupDesc));
    }

    /**
     * Function for handling export functionality of calendar.
     *
     * @param Request $request Request object
     *
     * @return response
     */
    public function indexAction(Request $request)
    {
        $calendarFunctions = new CalendarFunctions($this->container);
        $eventDetail = $request->get('eventData');
        $eventDetails = json_decode($eventDetail, true);
        $eventIds = implode(',', array_keys($eventDetails['events']));
        $searchVal = $eventDetails['search'];
        $filter = $eventDetails['filter'];
        $exportData = $calendarFunctions->getCalendarEventData($eventIds, $searchVal, $filter);
        $icsGenerate = $this->generateIcsFile($exportData);

        return $icsGenerate;
    }

    /**
     * Function to set response headers based to generate ics file.
     *
     * @param array $exportData event data to be exported
     *
     * @return string
     */
    public function generateIcsFile($exportData)
    {
        $calendarFunctions = new CalendarFunctions($this->container);
        $file = $this->get('translator')->trans('EXPORT_CALENDAR_ICS_FILENAME') . '_' . date('Y-m-d') . '_' . date('H-i-s') . '.ics';
        $filename = str_replace(' ', '%20', $file);
        $response = new Response();
        //Sets the response headers
        $response->setContent(utf8_decode($calendarFunctions->generateIcsData($exportData)));
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'text/calendar; charset=utf-8');
        $response->headers->set('Content-Disposition', 'inline; filename=' . $filename);
        $response->headers->set('Content-Transfer-Encoding', 'binary');

        return $response;
    }
}
