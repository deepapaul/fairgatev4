<?php

namespace Clubadmin\SponsorBundle\Controller;

use Common\UtilityBundle\Controller\FgController as ParentController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * ColumnsettingsController
 *
 * This controller was handling the ColumnsettingsController
 *
 * @package    ClubadminSponsorBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
class ColumnsettingsController extends ParentController {

    /**
     * Pre exicute function to allow access to clubs with  booked module sponsor
     * @return Exception
     */
    public function preExecute() {

        parent::preExecute();
        if (!in_array('sponsor', $this->get('club')->get('bookedModulesDet'))) {
            throw $this->createNotFoundException($this->clubTitle . ' have no access to this page');
        }
    }

    /**
     * Function to modify table settings
     *
     * @return template
     */
    public function indexAction() {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $contactType = $request->get('contacttype');
        $back = ($contactType == 'archivedsponsor') ? $this->generateUrl('view_archived_sponsors') : $this->generateUrl('clubadmin_sponsor_homepage');
        if ($contactType == 'archivedsponsor') {
            $this->get('club')->set('moduleMenu', 'archivedsponsor');
        }
        $breadCrumb = array('back' => $back);
        $corrAddrCatId = $this->container->getParameter('system_category_address');
        $invAddrCatId = $this->container->getParameter('system_category_invoice');
        $data = array('clubId' => $this->clubId, 'contactId' => $this->contactId, 'corrAddrCatId' => $corrAddrCatId, 'invAddrCatId' => $invAddrCatId);
        $columnsettingType = strtoupper($contactType);
        $allTableSettings = $this->em->getRepository('CommonUtilityBundle:FgTableSettings')->getAllTableSettings($this->clubId, $this->contactId, $columnsettingType);
        $defaultSettings = $this->container->getParameter('default_sponsor_table_settings');
        $selectedSettingId = $request->get('settings_id');
        $selectedSettings = '';
        if (!(($selectedSettingId == '') || ($selectedSettingId == '0'))) {
            foreach ($allTableSettings as $tableSettings) {
                if ($tableSettings['id'] == $selectedSettingId) {
                    $selectedSettings = $tableSettings['attributes'];
                    break;
                }
            }
        }

        return $this->render('ClubadminSponsorBundle:Columnsettings:index.html.twig', array('breadCrumb' => $breadCrumb, 'clubData' => $data, 'defaultSettings' => $defaultSettings, 'allTableSettings' => $allTableSettings, 'selectedSettingId' => $selectedSettingId, 'selectedSettings' => $selectedSettings, 'contacttype' => $contactType));
    }

    /**
     * Function to save/update column settings
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateSponsorColumnsettingsAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $tablesettingsData = $request->request->get('tablesettingsData');
            $id = $tablesettingsData['settings_id'];
            $title = $tablesettingsData['settings_name'];
            $attributes = $tablesettingsData['settings_data'];
            $contacttype = $tablesettingsData['contact_type'];
            $columnsettingType = strtoupper($contacttype);

            if ($tablesettingsData['save_type'] == 'APPLY') {
                return new JsonResponse(array('status' => 'SUCCESS', 'redirect' => $this->generateUrl('clubadmin_sponsor_homepage'), 'sync' => true));
            } else if ($tablesettingsData['settings_id'] == '') {
                //save table settings
                $clubobj = $this->em->getRepository('CommonUtilityBundle:FgClub')->find($this->clubId);
                $contactobj = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->find($this->contactId);
                $settingsId = $this->em->getRepository('CommonUtilityBundle:FgTableSettings')->addNewTableSettings($title, $attributes, $contactobj, 0, $clubobj, $columnsettingType);
                $flashMsg = $this->get('translator')->trans('COLUMN_SETTINGS_SAVED');
            } else {
                //update existing table setting column based on its id
                $tableObj = $this->em->getRepository('CommonUtilityBundle:FgTableSettings')->findOneBy(array('id' => $id, 'type' => $columnsettingType));
                $this->em->getRepository('CommonUtilityBundle:FgTableSettings')->updateTableSettings($tableObj, $attributes);

                $settingsId = $tablesettingsData['settings_id'];
                $flashMsg = $this->get('translator')->trans('COLUMN_SETTINGS_UPDATED');
            }
            return new JsonResponse(array('status' => 'SUCCESS', 'redirect' => $this->generateUrl('sponsor_columnsettings', array('settings_id' => $settingsId, 'contacttype' => $contacttype)), 'flash' => $flashMsg));
        }
    }

    /**
     * Function to delete the selected column settings
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function deleteSponsorColumnsettingsAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            //get the columnsettings id that needs to be deleted
            $settingsId = $request->request->get('settings_id');
            $contacttype = $request->request->get('contacttype');
            $tableObj = $this->em->getRepository('CommonUtilityBundle:FgTableSettings')->findOneBy(array('id' => $settingsId, 'type' => $contacttype));
            if ($tableObj == "") {
                return new JsonResponse(array('status' => 'ERROR', 'flash' => $this->get('translator')->trans('SPONSOR_COLUMNSETTING_SAVE_ERROR')));
            }
            $this->em->getRepository('CommonUtilityBundle:FgTableSettings')->deleteTableSettings($tableObj);

            return new JsonResponse(array('status' => 'SUCCESS', 'redirect' => $this->generateUrl('sponsor_columnsettings', array('settings_id' => '0', 'contacttype' => $contacttype)), 'flash' => $this->get('translator')->trans('COLUMN_SETTINGS_DELETED')));
        }
    }

}
