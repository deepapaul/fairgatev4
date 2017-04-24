<?php

namespace Clubadmin\ClubBundle\Controller;

use Common\UtilityBundle\Controller\FgController as ParentController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * ColumnsettingsController
 *
 * This controller was handling the ColumnsettingsController
 *
 * @package    ClubadminClubBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
class ColumnsettingsController extends ParentController
{

    /**
     * Pre exicute function to allow access to federation clubs only
     * @return Exception
     */
    public function preExecute()
    {
        parent::preExecute();

        if ($this->clubType != 'federation' && $this->clubType != 'sub_federation') {
            throw $this->createNotFoundException($this->clubTitle . ' have no access to this page');
        }
    }

    /**
     * Function to modify table settings
     *
     * @return template
     */
    public function indexAction(Request $request)
    {
        $breadCrumb = array('back' => $this->generateUrl('club_homepage'));
        $clubData = array('clubId' => $this->clubId, 'contactId' => $this->contactId);
        $allTableSettings = $this->adminEntityManager->getRepository('AdminUtilityBundle:FgClubTableSettings')->getAllClubTableSettings($this->clubId, $this->contactId);
        $defaultSettings = $this->container->getParameter('default_club_table_settings');
        $selectedSettingId = $request->get('settings_id');
        $selectedSettings = '';
        $terminologyService = $this->get('fairgate_terminology_service');
        $clubs = $terminologyService->getTerminology('Club', $this->container->getParameter('plural'));

        if (!(($selectedSettingId == '') || ($selectedSettingId == '0'))) {
            foreach ($allTableSettings as $tableSettings) {
                if ($tableSettings['id'] == $selectedSettingId) {
                    $selectedSettings = $tableSettings['attributes'];
                    break;
                }
            }
        }

        return $this->render('ClubadminClubBundle:Columnsettings:index.html.twig', array('breadCrumb' => $breadCrumb, 'clubData' => $clubData, 'defaultSettings' => $defaultSettings, 'allTableSettings' => $allTableSettings, 'selectedSettingId' => $selectedSettingId, 'selectedSettings' => $selectedSettings, 'clubs' => $clubs));
    }
//end indexAction()

    /**
     * Function to save/update column settings
     *
     * @return json object
     */
    public function updateClubColumnsettingsAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $tablesettingsData = $request->request->get('tablesettingsData');
            $id = $tablesettingsData['settings_id'];
            $title = $tablesettingsData['settings_name'];
            $attributes = $tablesettingsData['settings_data'];

            if ($tablesettingsData['settings_id'] == '') {
                //insert new entry
                if ($tablesettingsData['save_type'] == 'SAVE') {
                    //save table settings
                    $clubobj = $this->adminEntityManager->getRepository('AdminUtilityBundle:FgClub')->find($this->clubId);
                    $contactobj = $this->adminEntityManager->getRepository('AdminUtilityBundle:FgCmContact')->find($this->contactId);
                    if ($clubobj == "" || $contactobj == "") {
                        return new JsonResponse(array('status' => 'ERROR',
                            'flash' => $this->get('translator')->trans('CLUB_COLUMNSETTING_SAVE_ERROR')));
                    }
                    $this->adminEntityManager->getRepository('AdminUtilityBundle:FgClubTableSettings')->addNewTableSettings($title, $attributes, $contactobj, 0, $clubobj);
                }
            } else {
                //update existing table setting column based on its id
                $tableObj = $this->adminEntityManager->getRepository('AdminUtilityBundle:FgClubTableSettings')->find($id);
                if ($tableObj == "") {
                    return new JsonResponse(array('status' => 'ERROR',
                        'flash' => $this->get('translator')->trans('CLUB_COLUMNSETTING_SAVE_ERROR')));
                }
                $this->adminEntityManager->getRepository('AdminUtilityBundle:FgClubTableSettings')->updateTableSettings($tableObj, $attributes);
            }

            if ($tablesettingsData['save_type'] == 'APPLY') {
                return new JsonResponse(array('status' => 'SUCCESS', 'redirect' => $this->generateUrl('club_homepage'), 'sync' => true));
            } else {
                if ($tablesettingsData['settings_id'] == '') {
                    $lastInserted = $this->conn->executeQuery("SELECT LAST_INSERT_ID() AS settingsId")->fetch();
                    $settingsId = $lastInserted['settingsId'];
                    $flashMsg = $this->get('translator')->trans('COLUMN_SETTINGS_SAVED');
                } else {
                    $settingsId = $tablesettingsData['settings_id'];
                    $flashMsg = $this->get('translator')->trans('COLUMN_SETTINGS_UPDATED');
                }

                return new JsonResponse(array('status' => 'SUCCESS', 'redirect' => $this->generateUrl('clubcolumnsettings', array('settings_id' => $settingsId)), 'flash' => $flashMsg));
            }
        }
    }
//end updateClubColumnsettingsAction()

    /**
     * Function to delete the selected column settings
     * @return json Object
     */
    public function deleteClubColumnsettingsAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            //get the columnsettings id that needs to be deleted
            $settingsId = $request->request->get('settings_id');
            $tableObj = $this->adminEntityManager->getRepository('AdminUtilityBundle:FgClubTableSettings')->find($settingsId);
            if ($tableObj == "") {
                return new JsonResponse(array('status' => 'ERROR', 'flash' => $this->get('translator')->trans('CLUB_COLUMNSETTING_SAVE_ERROR')));
            }
            $this->adminEntityManager->getRepository('AdminUtilityBundle:FgClubTableSettings')->deleteTableSettings($tableObj);

            return new JsonResponse(array('status' => 'SUCCESS', 'redirect' => $this->generateUrl('clubcolumnsettings', array('settings_id' => '0')), 'flash' => $this->get('translator')->trans('COLUMN_SETTINGS_DELETED')));
        }
    }
//end deleteClubColumnsettingsAction()
}

//end class