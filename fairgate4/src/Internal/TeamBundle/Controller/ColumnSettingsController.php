<?php

namespace Internal\TeamBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Common\UtilityBundle\Repository\Pdo\membershipPdo;

class ColumnSettingsController extends Controller
{

    /**
     * Function to display the column settings page for team and workgroup members in internal area
     *
     * @return template
     */
    public function indexAction()
    {
        $translatorService = $this->get('translator');
        $terminologyService = $this->get('fairgate_terminology_service');
        $module = $this->container->get('request_stack')->getCurrentRequest()->get('module');
        $teamSingle = ucfirst($terminologyService->getTerminology('Team', $this->container->getParameter('singular')));
        $titleText = ($module == "team") ? str_replace('%team%', $teamSingle, $this->get('translator')->trans('TEAM_COLUMN_SETTINGS_TITLE')) : $this->get('translator')->trans('WORKGROUP_COLUMN_SETTINGS_TITLE');
        $fixedFields = $this->getFixedFields($translatorService, $terminologyService);
        $container = $this->container;
        $breadCrumb = array(
            'back' => ($module == "team") ? $this->generateUrl('team_detail_overview') : $this->generateUrl('workgroup_detail_overview')
        );
        $defaultSettings = array(); //Visibility needed to checked for default fields too
        $corrAddrCatId = $container->getParameter('system_category_address');
        $clubData = array('clubId' => $this->container->get('club')->get('id'), 'contactId' => $this->get('contact')->get('id'), 'clubTeamId' => $this->container->get('club')->get('club_team_id'), 'clubWorkgroupId' => $this->container->get('club')->get('club_workgroup_id'), 'clubExecutiveBoardId' => $this->container->get('club')->get('club_executiveboard_id'), 'corrAddrCatId' => $corrAddrCatId);

        return $this->render('InternalTeamBundle:Columnsettings:index.html.twig', array('breadCrumb' => $breadCrumb, 'fixedFields' => $fixedFields, 'defaultSettings' => $defaultSettings, 'clubData' => $clubData, 'module' => $module, 'titletext' => $titleText));
    }

    /**
     * Function to get all the table settings values for team and workgroup members in internal area
     *
     * @param string $module module type either team or workgroup
     *
     * @return JsonResponse
     */
    public function getAllinternaltablesetingsAction($module)
    {
        $contactFields = $this->get('club')->get('contactFields');
        $executiveBoardTitle = $this->get('fairgate_terminology_service')->getTerminology('Executive Board', $this->container->getParameter('singular'));
        $objMembershipPdo = new membershipPdo($this->container);
        $assignmentFields = $objMembershipPdo->getAllCategoryRoleFunction($this->get('club'), 'rolesonly', false, $executiveBoardTitle);
        $invAddrCatId = $this->container->getParameter('system_category_invoice');
        $contact = $this->get('contact');
        $roleIds = ($module == "team") ? array_keys($contact->get('teams')) : array_keys($contact->get('workgroups'));
        $finalAssignmentFields = $this->getAssignmentFieldsData($assignmentFields, $roleIds);
        $moduleIndex = ($module == "team") ? "teams" : "workgroups";
        $groupRights = $contact->get('clubRoleRightsGroupWise');
        $isSuperAdmin = ($contact->get('isSuperAdmin') || (($contact->get('isFedAdmin')) && ($this->get('club')->get('type') != 'federation'))) ? 1 : 0;
        $defaultArray = $this->getDefaultsettingsidArray();
        if (array_key_exists('ROLE_GROUP_ADMIN', $groupRights) || array_key_exists('ROLE_CONTACT_ADMIN', $groupRights)) {
            $adminRights = array();
            if (array_key_exists('ROLE_GROUP_ADMIN', $groupRights))
                $adminRights = $adminRights + $groupRights['ROLE_GROUP_ADMIN'][$moduleIndex];
            if (array_key_exists('ROLE_CONTACT_ADMIN', $groupRights))
                $adminRights = $adminRights + $groupRights['ROLE_CONTACT_ADMIN'][$moduleIndex];
            $finalcontactFields = (count($adminRights) > 0) ? $this->getOptimizeContactFieldsforAdmin($contactFields, $defaultArray, $invAddrCatId) : $this->getOptimizeContactFields($contactFields, $defaultArray, $invAddrCatId);
        } elseif ($isSuperAdmin == 1) {
            $finalcontactFields = $this->getOptimizeContactFieldsforAdmin($contactFields, $defaultArray, $invAddrCatId);
        } else {
            $finalcontactFields = $this->getOptimizeContactFields($contactFields, $defaultArray, $invAddrCatId);
        }

        return new JsonResponse(array('contactFields' => $finalcontactFields, 'assignmentFields' => $finalAssignmentFields));
    }

    /**
     * Function to get all optimized contact fields for group admin or superadmin
     *
     * @param array $contactFields contact fields array
     * @param array $defaultArray  default fields id array
     * @param int   $invAddrCatId  invoice address category id
     * 
     * @return array
     */
    public function getOptimizeContactFieldsforAdmin($contactFields, $defaultArray, $invAddrCatId)
    {
        $finalFieldsArray = array();
        foreach ($contactFields as $key => $value) {
            if ((($contactFields[$key]['is_visible_teamadmin'] == 1) || ($contactFields[$key]['privacy_contact'] != 'private')) && ($contactFields[$key]['catId'] != $invAddrCatId)) {
                $finalFieldsArray[$key] = $value;
            }
        }

        return $finalFieldsArray;
    }

    /**
     * Function to get all assignment fields either team or workgroup fields
     *
     * @param array $assignmentFields contact fields array
     * @param array $roleIds          workgroup or team ids of logged in contact
     *
     * @return array
     */
    public function getAssignmentFieldsData($assignmentFields, $roleIds)
    {
        $finalFields = array();
        foreach ($assignmentFields as $key => $value) {
            if (in_array($assignmentFields[$key]['roleId'], $roleIds)) {
                $finalFields[$key] = $value;
            }
        }

        return $finalFields;
    }

    /**
     * Function to get the default column ids array
     *
     * @return array
     */
    private function getDefaultsettingsidArray()
    {
        $container = $this->container;
        $defaultSettingsArray = array($container->getParameter('system_field_primaryemail'),
            $container->getParameter('system_field_corres_strasse'),
            $container->getParameter('system_field_corres_plz'),
            $container->getParameter('system_field_corres_ort'),
            $container->getParameter('system_field_mobile1'));

        return $defaultSettingsArray;
    }

    /**
     * Function to get optimized  contact fields for team or workgroup members
     *
     * @param array $contactFields contact fields array
     * @param array $defaultArray  default fields id array
     * @param int   $invAddrCatId  invoice address category id
     * 
     * @return array
     */
    public function getOptimizeContactFields($contactFields, $defaultArray, $invAddrCatId)
    {
        $finalFieldsArray = array();
        foreach ($contactFields as $key => $value) {
            if ((($contactFields[$key]['privacy_contact'] != 'private')) && ($contactFields[$key]['catId'] != $invAddrCatId)) {
                $finalFieldsArray[$key] = $value;
            }
        }

        return $finalFieldsArray;
    }

    /**
     * Function to get fixed fields for team or workgroup members
     *
     * @param service $translatorService translator service
     * @param service $terminologyService  terminology service
     * 
     * @return array
     */
    private function getFixedFields($translatorService, $terminologyService)
    {
        $club = $this->get('club');
        $bookedModuleDetails = $club->get('bookedModulesDet');
        $fixedFields = array(
            'contact_options' => array(
                'title' => $translatorService->trans('CM_CONTACT_OPTIONS')
            ),
            'analysis_fields' => array(
                'title' => $translatorService->trans('CM_ANALYSIS_FIELDS'),
                'fields' => array('age' => $translatorService->trans('CM_AGE'), 'birth_year' => $translatorService->trans('YEAR_OF_BIRTH'), 'salutation_text' => $translatorService->trans('SALUTATION_TEXT'))
            ),
        );
        if (($club->get('type') != 'federation' && $club->get('type') != 'sub_federation') && $club->get('clubMembershipAvailable')) {
            $fixedFields['contact_options']['fields']['membership_category'] = $translatorService->trans('CM_MEMBERSHIP');
        }
        if ($club->get('type') != 'standard_club') {
            $fixedFields['contact_options']['fields']['fedmembership_category'] = ucfirst($terminologyService->getTerminology('Fed membership', $this->container->getParameter('plural')));
        }
        
        return $fixedFields;
    }
}
