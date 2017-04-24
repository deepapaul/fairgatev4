<?php
/**
 * CreateAppointmentController.
 *
 * This controller used for creating appointments
 *
 * @author     pitsolutions.ch
 *
 * @version    Fairgate V4
 */
namespace Internal\CalendarBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Common\UtilityBundle\Repository\Pdo\CalendarPdo;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * CreateAppointmentController.
 *
 * This controller used for creating appointments
 *
 * @author     pitsolutions.ch
 *
 * @version    Fairgate V4
 */
class CreateAppointmentController extends Controller
{
    /**
     * Function to save appointment.
     * 
     * @return JsonResponse
     */
    public function saveCalenderDataAction()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $requestData = json_decode($request->get('saveData'), true);
        if (is_array($requestData['calendar_details'])) {
            $calendarDetails = $requestData['calendar_details'];
            foreach ($calendarDetails as $detailId) {
                if (isset($detailId['id']) && isset($detailId['edit_start_date']) && isset($detailId['edit_end_date'])) {
                    $requestData['calendar_detail_id'] = $detailId['id'];
                    $requestData['edit_start_date'] = $detailId['edit_start_date'];
                    $requestData['edit_end_date'] = $detailId['edit_end_date'];
                    $pdo = new CalendarPdo($this->container);
                    $pdo->saveAppointment($requestData);
                }
            }
        } else {
            $pdo = new CalendarPdo($this->container);
            $pdo->saveAppointment($requestData);
        }

        return new JsonResponse(array('status' => 'success', 'flash' => $this->container->get('translator')->trans('CALENDAR_APPOINTMENT_UPDATE_SUCCESS'), 'sync' => 1, 'redirect' => $this->generateUrl('internal_calendar_view')));
    }
}
