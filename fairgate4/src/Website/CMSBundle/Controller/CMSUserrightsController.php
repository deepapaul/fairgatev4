<?php

/**
 * CMSUserrightsController
 *
 */
namespace Website\CMSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Common\UtilityBundle\Util\FgPermissions;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * CMSUserrightsController
 *
 * This controller was created for handling User rights
 *
 * @package 	Website
 * @subpackage 	CMSBundle
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 */
class CMSUserrightsController extends Controller
{

    /**
     * page access restriction function
     *
     * @return Boolean
     */
    private function hasPageAccess()
    {
        $adminRights = $this->container->get('contact')->get('mainAdminRightsForFrontend');
        $availableUserRights = $this->container->get('contact')->get('availableUserRights');
        $isClubAdmin = (in_array('ROLE_USERS', $adminRights)) ? 1 : 0;
        $isCmsAdmin = (in_array('ROLE_CMS_ADMIN', $availableUserRights)) ? 1 : 0;
        $isSuperOrFedAdmin = ($this->container->get('contact')->get('isSuperAdmin') || (($this->container->get('contact')->get('isFedAdmin')) && ($this->container->get('club')->get('type') != 'federation'))) ? 1 : 0;
        if (!(($isCmsAdmin) || ($isClubAdmin) || ($isSuperOrFedAdmin))) {
            $permissionObj = new FgPermissions($this->container);
            $permissionObj->checkUserAccess(0, 'cmsuserrights');
        }

        return true;
    }

    /**
     * to display website section backend
     *
     * @return Object View Template Render Object
     */
    public function displayCmsUserrightAction()
    {
        $this->hasPageAccess();
        $this->em = $this->getDoctrine()->getManager();
        $club = $this->container->get('club'); // Club object
        $clubId = $club->get('id');
        $clubType = $club->get('type');
        $contact = $this->container->get('contact'); //contact Obj
        $contactId = $contact->get('id');
        $lang = $contact->get('corrLang');
        $cmsAdmin = $this->container->getParameter('cms_admin');

        // Function to get all assigned user rights with user details
        $existingUserCms = $this->em->getRepository('CommonUtilityBundle:SfGuardGroup')->getGroupDetailsAllUSerByGroup($clubId, $cmsAdmin);
        $existingUserPage = $this->em->getRepository('CommonUtilityBundle:SfGuardUserPage')->getPageAdmins($clubId);
        // Translation for default texts displayed in the settings page.
        // When a new block needed in the settings page, need to add the translation in this array for the block'
        $existingUserArray = $this->excludeAdmins($existingUserCms);
        $existingUserDetails = json_encode($existingUserArray['existingUserDetails'], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
        $existingPageAdminArray = $this->excludeAdmins($existingUserPage);
        $existingPageAdminDet = json_encode($existingPageAdminArray['existingUserDetails'], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
        $dropdownPages = $this->em->getRepository('CommonUtilityBundle:FgCmsPage')->getPagesList($clubId, $lang);
        $dropdownJson = json_encode($dropdownPages);

        // Function to get all user rights groups
        $groupUserDetails = json_encode($existingUserCms);
        $groupPageUserDetails = json_encode($existingUserPage);

        $return = array('groupPageUserDetails' => $groupPageUserDetails, 'dropdownJson' => $dropdownJson, 'pageList' => $dropdownJson,
            'clubType' => $clubType, 'existingUserDetails' => $existingUserDetails, 'loggedContactId' => $contactId,
            'groupUserDetails' => $groupUserDetails, 'existingPageAdminDet' => $existingPageAdminDet, settings => true);
        $return['contactNameUrl'] = str_replace('25', '', $this->generateUrl('contact_names_userrights', array('term' => '%QUERY')));

        return $this->render('WebsiteCMSBundle:CMSUserrights:displayCmsUserrights.html.twig', $return);
    }

    /**
     * Function is used to get all the user rights blocks as seperate array.
     *
     * @param Array $groupContacts Group-contact details
     *
     * @return Array existing user details
     */
    private function excludeAdmins($groupContacts)
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
     * Assign vales to common array
     *
     * @param Array $assignGroupArray Array to be generated for listing
     * @param Int   $indexVal         Index value
     * @param Array $groupContact     Query resuly value
     *
     * @return Array assign group array
     */
    private function assignValuesToGroup($assignGroupArray, $indexVal, $groupContact)
    {
        $assignGroupArray[$indexVal][0]['id'] = $groupContact['contact_id'];
        $assignGroupArray[$indexVal][0]['value'] = $groupContact['contactname'];
        $assignGroupArray[$indexVal][0]['label'] = $groupContact['contactname'];

        return $assignGroupArray;
    }

    /**
     * Function is used to save user rights from user rights setting page
     *
     * @return JSON json response object
     */
    public function saveUserRightsAction()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $this->em = $this->getDoctrine()->getManager();
        $conn = $this->container->get('database_connection');
        $clubId = $this->container->get('club')->get('id');
        $contactId = $this->container->get('contact')->get('id');
        $userRightsArray = json_decode($request->get('postArr'), true); // Getting save values from setting page in JSON format
        $userRightsArray = $this->formatUserRightsArray($userRightsArray); // Calling function to format the result array
        // Calling common save function to save the user rights
        $resultSuccess = $this->em->getRepository('CommonUtilityBundle:SfGuardGroup')->saveUserRights($conn, $userRightsArray, $clubId, $contactId, $this->container);
        if ($resultSuccess) {
            return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('USER_RIGHTS_SAVED_SUCCESS')));
        } else {
            return new JsonResponse(array('status' => 'SUCCESS'));
        }
    }

    /**
     * Function is used to format array for common save function
     *
     * @param Array $userRightsArray user rights array
     *
     * @return Array user right array
     */
    private function formatUserRightsArray($userRightsArray)
    {

        $finalArray = array();
        $cmsAdmin = $this->container->getParameter('cms_admin');
        $pgAdmin = $this->container->getParameter('page_admin');


        // Checking and generating formated array incase of single delete of assigned group of a contact
        if (!empty($userRightsArray['delete']['admin'])) {
            foreach ($userRightsArray['delete']['admin']['group'] as $gpKey => $gpVal) {
                foreach ($gpVal['user'] as $usKey => $usVal) {
                    $finalArray['delete']['group'][$gpKey]['user'][$usKey] = 1;
                }
            }
        }
        /*         * ******** CMS ADMIN ********** */
        // Generating array to delete cms admin group of a contact
        if (!empty($userRightsArray['delete']['cmsAdmin'])) {
            $finalArray['delete']['group'][$cmsAdmin]['user'] = $userRightsArray['delete']['cmsAdmin']['user'];
        }

        // Generating array when a new cms admin is added
        if (!empty($userRightsArray['new']['cmsAdmin'])) {
            foreach ($userRightsArray['new']['cmsAdmin']['contact'] as $cKey => $cVal) {
                if ($cKey == "undefined") {
                    continue;
                }
                foreach ($cVal['group'] as $gpKey => $gpVal) {
                    $finalArray['new']['group'][$gpKey]['contact'][$cKey] = $gpVal;
                    $finalArray['new_users']['cmsAdmin'][$cKey]['id'] = $cKey;
                    $finalArray['new_users']['cmsAdmin'][$cKey]['type'] = 'cmsAdmin';
                }
            }
        }
        /*         * ******** END CMS ADMIN ********** */
        /*         * ******** BEGIN Page ADMIN ********** */
        if (isset($userRightsArray['cms']['pgAdmin']['delete'])) {
            foreach ($userRightsArray['cms']['pgAdmin']['delete'] as $contactId => $true) {
                $finalArray['new']['pgAdmin'][$pgAdmin]['contact'][$contactId]['team'] = 'deleted';
            }
        }
        if (isset($userRightsArray['cms']['pgAdmin']['existing'])) {
            foreach ($userRightsArray['cms']['pgAdmin']['existing'] as $contact => $teams) {
                if (is_array($teams['pages'])) {
                    $finalArray['new']['pgAdmin'][$pgAdmin]['contact'][$contact]['team'] = $teams['pages'];
                }
                if ($teams['pages'] == '') {
                    $finalArray['new']['pgAdmin'][$pgAdmin]['contact'][$contact]['team'] = 'deleted';
                }
            }
        }

        foreach ($userRightsArray['cms']['pgAdmin'] as $val) {

            if (isset($val['contact'])) {
                foreach ($val['contact'] as $value) {
                    $contactId = $value;
                }
                if ($contactId == "") {
                    continue;
                }
                if ($val['pages'] == '') {
                    $finalArray['new']['pgAdmin'][$pgAdmin]['contact'][$contactId]['team'] = 'deleted';
                } else {
                    $finalArray['new']['pgAdmin'][$pgAdmin]['contact'][$contactId]['team'] = $val['pages'];
                }
            }
        }

        return $finalArray;
    }
}
