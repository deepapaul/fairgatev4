<?php

namespace Clubadmin\ContactBundle\Controller;

use Common\UtilityBundle\Controller\FgController as ParentController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Repository\Pdo\membershipPdo;

/**
 * For contact Columnsetting
 */
class ColumnsettingsController extends ParentController {

    /**
     * Function is used to  show column settings
     * @return template
     */
    public function indexAction() {

        $club = $this->get('club');
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $contactType = $request->get('contacttype');
        if ($contactType == 'archive') {
            $this->get('club')->set('moduleMenu', 'contactarchive');
        } elseif ($contactType == 'formerfederationmember') {
            $this->get('club')->set('moduleMenu', 'formerfederationmember');
        }
        $settingsType = 'DATA';
        $selectedSettings = '';
        $allTableSettings = $this->em->getRepository('CommonUtilityBundle:FgTableSettings')->getAllTableSettings($this->clubId, $this->contactId, $settingsType);
        $selectedSettingId = $request->get('settings_id');
        if (!(($selectedSettingId == '') || ($selectedSettingId == '0'))) {
            foreach ($allTableSettings as $tableSettings) {
                if ($tableSettings['id'] == $selectedSettingId) {
                    $selectedSettings = $tableSettings['attributes'];
                    break;
                }
            }
        }
        $breadCrumb = array(
            'back' => '#'
        );
        $container = $this->container;
        $defaultSettings = $container->getParameter('default_table_settings');
        $corrAddrCatId = $container->getParameter('system_category_address');
        $invAddrCatId = $container->getParameter('system_category_invoice');

        $clubData = array('clubId' => $this->clubId, 'clubhierarchy' => $club->get('clubHeirarchy'), 'type' => $club->get('type'), 'contactId' => $this->contactId, 'clubTeamId' => $this->clubTeamId, 'clubWorkgroupId' => $this->clubWorkgroupId, 'clubExecutiveBoardId' => $this->clubExecutiveBoardId, 'corrAddrCatId' => $corrAddrCatId, 'invAddrCatId' => $invAddrCatId);

        return $this->render('ClubadminContactBundle:Columnsettings:index.html.twig', array('breadCrumb' => $breadCrumb, 'defaultSettings' => $defaultSettings, 'selectedSettingId' => $selectedSettingId, 'selectedSettings' => $selectedSettings, 'allTableSettings' => $allTableSettings, 'clubData' => $clubData, 'contacttype' => $contactType));
    }

    /**
     * Function is used to  show column settings
     * @return object   Json array of assignment categories
     */
    public function getTableSettingFieldsAction() {
        $translatorService = $this->get('translator');
        $contactFields = $this->get('club')->get('contactFields');
        $contactField_profile_img = array(
            'id' => 'profile_company_pic',
            'title' => $translatorService->trans('CM_PROFILE_COMPANY_PIC'),
            'selectgroup' => $translatorService->trans('CM_PROFILE_IMG')
        );
        array_push($contactFields, $contactField_profile_img);
        $terminologyService = $this->get('fairgate_terminology_service');
        $executiveBoardTitle = $terminologyService->getTerminology('Executive Board', $this->container->getParameter('singular'));
        
        $objMembershipPdo = new membershipPdo($this->container);
        $assignmentFields = $objMembershipPdo->getAllCategoryRoleFunction($this->get('club'), 'rolesonly', false, $executiveBoardTitle);

        $fields = array('contactFields' => $contactFields, 'assignmentFields' => $assignmentFields);

        return new JsonResponse($fields);
    }

    /**
     * Function is used to   update column settings
     * 
     * @return    Jsonobject
     */
    public function updateColumnsettingsAction(Request $request) {
        if ($request->getMethod() == 'POST') {

            $tablesettingsData = $request->request->get('tablesettingsData');
            $id = $tablesettingsData['settings_id'];
            $columnsettingType = 'DATA';
            $title = FgUtility::getSecuredDataString($tablesettingsData['settings_name'], $this->conn);
            $attributes = $tablesettingsData['settings_data'];
            $tsQry = "";
            if ($tablesettingsData['settings_id'] == '') {
                //insert new entry
                if ($tablesettingsData['save_type'] == 'SAVE') {
                    $clubobj = $this->em->getRepository('CommonUtilityBundle:FgClub')->find($this->clubId);
                    $contactobj = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->find($this->contactId);
                    $settingsId = $this->em->getRepository('CommonUtilityBundle:FgTableSettings')->addNewTableSettings($title, $attributes, $contactobj, 0, $clubobj, $columnsettingType);
                    $flashMsg = $this->get('translator')->trans('COLUMN_SETTINGS_SAVED');
                }
            } else {

                $tableObj = $this->em->getRepository('CommonUtilityBundle:FgTableSettings')->findOneBy(array('id' => $id, 'type' => $columnsettingType));
                if ($tableObj) {

                    $this->em->getRepository('CommonUtilityBundle:FgTableSettings')->updateTableSettings($tableObj, $attributes);
                }


                $settingsId = $tablesettingsData['settings_id'];
                $flashMsg = $this->get('translator')->trans('COLUMN_SETTINGS_UPDATED');
            }


            if ($tablesettingsData['save_type'] == 'APPLY') {

                return new JsonResponse(array('status' => 'SUCCESS', 'redirect' => $this->generateUrl('contact_index'), 'sync' => true));
            } else {


                return new JsonResponse(array('status' => 'SUCCESS', 'redirect' => $this->generateUrl('columnsettings', array('settings_id' => $settingsId, 'contacttype' => 'contact')), 'flash' => $flashMsg));
            }
        }
    }

    /**
     * Function delete column settings
     * 
     * @return    Jsonobject
     */
    public function deleteColumnsettingsAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $settingsId = $request->request->get('settings_id');
            $contacttype = 'DATA';
            $tableObj = $this->em->getRepository('CommonUtilityBundle:FgTableSettings')->findOneBy(array('id' => $settingsId, 'type' => $contacttype));
            if ($tableObj == "") {
                return new JsonResponse(array('status' => 'ERROR', 'flash' => $this->get('translator')->trans('SPONSOR_COLUMNSETTING_SAVE_ERROR')));
            }
            $this->em->getRepository('CommonUtilityBundle:FgTableSettings')->deleteTableSettings($tableObj);

            return new JsonResponse(array('status' => 'SUCCESS', 'redirect' => $this->generateUrl('columnsettings', array('settings_id' => '0', 'contacttype' => 'contact')), 'flash' => $this->get('translator')->trans('COLUMN_SETTINGS_DELETED')));
        }
    }

}
