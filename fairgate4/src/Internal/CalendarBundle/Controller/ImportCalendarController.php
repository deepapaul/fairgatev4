<?php

/**
 * ImportCalendar Controller.
 *
 * This controller is used for Import Calendar section.
 *
 * @author     pitsolutions.ch
 *
 * @version    Fairgate V4
 */
namespace Internal\CalendarBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Internal\CalendarBundle\Util\CalendarImport;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Util\FgPermissions;
use Symfony\Component\HttpFoundation\Request;

/**
 * ImportCalendar Controller.
 *
 * This controller is used for Import Calendar section.
 *
 * @author     pitsolutions.ch
 *
 * @version    Fairgate V4
 */
class ImportCalendarController extends Controller
{

    /**
     * This function is used to render the import events template.
     *
     * @return template
     */
    public function importCalendarAction()
    {
        $em = $this->getDoctrine()->getManager();
        $dataSet['myAreas'] = $em->getRepository('CommonUtilityBundle:FgEmCalendar')->getMyClubAndTeamsAndWorkgroups($this->container);
        $dataSet['breadCrumb'] = array('back' => $this->generateUrl('internal_calendar_view'));
        $dataSet['clubDefaultLanguage'] = $this->container->get('club')->get('default_lang');

        return $this->render('InternalCalendarBundle:ImportCalendar:calendarImport.html.twig', $dataSet);
    }

    /**
     * This function is used to upload , process and save imported events.
     *
     * @return JsonResponse
     *
     * @throws \Internal\CalendarBundle\Controller\Exception
     */
    public function importFileSubmitAction(Request $request)
    {
        ini_set('max_execution_time', 300);
        $conn = $this->container->get('database_connection');
        $uploadDir = 'uploads/temp';
        if ($request->getMethod() == 'POST') {
            $importFile = $request->files->get('importFile');
            $scope = $request->get('fg-event-scope');
            $categoryIds = $request->get('fg-event-categories');
            $areaIds = ($scope == 'GROUP') ? $request->get('fg-event-areas-groups') : $request->get('fg-event-areas-others');
            $uploadedFile = $this->uploadIcs($importFile);
            if ($uploadedFile['status']) {
                $filePath = $uploadDir . '/' . $uploadedFile['fileName'];
                $config = array('filename' => $filePath);
                $v = new \vcalendar($config); // initiate new CALENDAR
                if ($v->parse()) {
                    $calendarImportObj = new CalendarImport($this->container, $scope, $areaIds, $categoryIds);
                    try {
                        $conn->beginTransaction();
                        while ($vevent = $v->getComponent('vevent')) {
                            $calendarImportObj->processEvent($vevent);
                        }
                        $calendarImportObj->insertToCalendarDetailsI18n();
                        $calendarImportObj->insertToCalendarSelectedAreas();
                        $calendarImportObj->insertToCalendarSelectedCategories();
                        $conn->commit();
                    } catch (Exception $ex) {
                        $conn->rollback();
                        throw $ex;
                    }
                    $countDetails = $calendarImportObj->getImportedEventsCount();
                    $message = ($countDetails['importedCount'] > 0) ? $this->get('translator')->trans('IMPORT_EVENTS_SUCCESS_MESSAGE', array('%a%' => $countDetails['importedCount'], '%b%' => $countDetails['totalCnt'])) : $this->get('translator')->trans('NO_EVENTS_IMPORTED_MESSAGE');

                    return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $message, 'sync' => true, 'redirect' => $this->generateUrl('internal_calendar_view')));
                } else {
                    return new JsonResponse(array('status' => 'ERROR', 'message' => $this->get('translator')->trans('ERROR_PARSING_ICS_FILE')));
                }
            } else {
                $errorMessage = $uploadedFile['errorMessage'] ? $uploadedFile['errorMessage'] : $this->get('translator')->trans('FILE_NOT_FOUND');

                return new JsonResponse(array('status' => 'ERROR', 'message' => $errorMessage));
            }
        } else {
            $permissionObj = new FgPermissions($this->container);
            $permissionObj->checkClubAccess(null, '', $this->get('translator')->trans('IMPORT_FILE_SUBMIT_NOT_POST'));
        }
    }

    /**
     * Function to upload ics file.
     *
     * @param file $importFile importFile
     *
     * @return array
     */
    private function uploadIcs($importFile)
    {
        $containerParameters = $this->container->getParameterBag();
        $maxFileSize = $containerParameters->get('max_file_size');
        $allowedExtension = array('ics');
        $mimeTypesMessage = $this->get('translator')->trans('NOT_VALID_TYPE');
        $maxSizeMessage = 'SIZE_EXCEED';
        $uploadDir = 'uploads/temp';
        if (empty($importFile)) {
            return array('status' => false, 'errorMessage' => $this->get('translator')->trans('VALIDATION_THIS_FIELD_REQUIRED'));
        }
        $filename = $importFile->getClientOriginalName();
        $fileInfo = explode('.', $filename);
        $ext = array_pop($fileInfo);
        if (in_array($ext, $allowedExtension)) {
            $filename = FgUtility::getFilename($uploadDir, $filename);
            $movedFile = $importFile->move($uploadDir, $filename);
            $return = $movedFile->getExtension() ? array('status' => true, 'fileName' => $filename) : array('status' => true, 'errorMessage' => 'Error');
        } else {
            $return = array('status' => false, 'errorMessage' => $this->get('translator')->trans('ALLOWED_FILE_TYPE_ICS_ERROR_MSG'));
        }

        return $return;
    }
}
