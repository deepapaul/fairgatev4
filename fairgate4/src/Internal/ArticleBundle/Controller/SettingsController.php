<?php

/**
 * Settings Controller
 */
namespace Internal\ArticleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Settings Controller
 *
 * This controller was created for handling the article settings pages in the Article area.
 *
 * @package    InternalArticleBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 *
 * @version    Fairgate V4
 *
 */
class SettingsController extends Controller
{

    /**
     * Function to show either comments or multi language article settings page
     *
     * @param object $request \Symfony\Component\HttpFoundation\Request
     *
     * @return object View Template Render Object
     */
    public function settingsAction(Request $request)
    {
        $settingsVal = $request->get('level2');
        $settingsType = ($settingsVal == 'articlecomments') ? 'comments' : 'multilanguage';
        $clubId = $this->get('club')->get('id');

        $currentSettings = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsArticleClubsetting')->getArticleClubSettings($clubId, $settingsType);

        return $this->render('InternalArticleBundle:Settings:settings.html.twig', array('currentSettings' => $currentSettings, 'settingsType' => $settingsType));
    }

    /**
     * Function to save article comments or multi language settings
     *
     * @param object $request \Symfony\Component\HttpFoundation\Request
     *
     * @return object JSON Response Object
     */
    public function settingsSaveAction(Request $request)
    {
        $settingsData = $request->get('settingsVal');
        $settingsType = $request->get('settingsType');
        $clubId = $this->get('club')->get('id');
        $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsArticleClubsetting')->saveArticleSettings($clubId, $settingsData, $settingsType);
        $successMessageTrans = ($settingsType == "comments") ? 'ARTICLE_SETTINGS_COMMENTS_SUCCESS_MESSAGE' : 'ARTICLE_SETTINGS_LANGUAGE_SUCCESS_MESSAGE';

        return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->container->get('translator')->trans($successMessageTrans)));
    }

    /**
     * Method to render article userrights page
     * 
     * @return object View Template Render Object
     */
    public function articleUserRightsAction()
    {
        $em = $this->getDoctrine()->getManager();

        $returnArray['breadCrumb'] = array();
        $clubId = $this->container->get('club')->get('id');
        $contactId = $this->container->get('contact')->get('id');
        $returnArray['contactId'] = $returnArray['loggedContactId'] = $contactId;
        $returnArray['clubId'] = $clubId;

        $articleAdmin = $em->getRepository('CommonUtilityBundle:SfGuardGroup')->getArticleAdmin($clubId);
        $excludeList = $this->getAllListingBlock($articleAdmin);
        $returnArray['exclude'] = json_encode($excludeList['existingUserDetails'], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);

        $returnArray['articleAdmin'] = json_encode($articleAdmin, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
        $dropdownList = $em->getRepository('CommonUtilityBundle:FgRmRole')->getRoleTeamDetails($clubId, $this->container);
        $returnArray['dropdown'] = json_encode($dropdownList, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_HEX_AMP);

        $articleRoleAdmin = $em->getRepository('CommonUtilityBundle:SfGuardUserTeam')->getArticleRoleAdmin($clubId);
        $returnArray['roleAdminExclude'] = json_encode($this->getAllListingBlock($articleRoleAdmin), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
        $returnArray['groupAdmin'] = json_encode($articleRoleAdmin, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_HEX_AMP);
        $returnArray['contactNameUrl'] = str_replace('25', '', $this->generateUrl('contact_names_userrights', array('term' => '%QUERY')));

        return $this->render('InternalArticleBundle:Settings:userrights.html.twig', $returnArray);
    }

    /**
     * Function is used to get all the user rights blocks as seperate array(existingUserDetails|existingWGUserDetails|existingTeamUserDetails)
     *
     * @param array $groupContacts Group-contact details
     *
     * @return array array(existingUserDetails|existingWGUserDetails|existingTeamUserDetails)
     */
    private function getAllListingBlock($groupContacts)
    {
        $existingUserDetails = array();
        $existingWGUserDetails = array();
        $existingTeamUserDetails = array();
        $i = 0;
        $j = 0;
        $k = 0;

        // Looping array containing all user rights details
        foreach ($groupContacts as $groupContact) {
            if ($groupContact['module_type'] == 'article' && $groupContact['type'] == 'club') {
                $existingUserDetails = $this->assignValuesToGroup($existingUserDetails, $i, $groupContact);
                $i++;
            }
            if ($groupContact['module_type'] == 'article' && $groupContact['type'] == 'role' && $groupContact['roleType'] == 'W') {
                $existingWGUserDetails = $this->assignValuesToGroup($existingWGUserDetails, $k, $groupContact);
                $k++;
            }

            if ($groupContact['module_type'] == 'article' && $groupContact['type'] == 'role' && $groupContact['roleType'] == 'T') {
                $existingTeamUserDetails = $this->assignValuesToGroup($existingTeamUserDetails, $j, $groupContact);
                $j++;
            }
        }

        return array('existingUserDetails' => $existingUserDetails, 'existingWGUserDetails' => $existingWGUserDetails, 'existingTeamUserDetails' => $existingTeamUserDetails);
    }

    /**
     * Assign vales to common array.
     *
     * @param Array $assignGroupArray Array to be generated for listing
     * @param Int   $indexVal         Index value
     * @param Array $groupContact     Query resuly value
     *
     * @return Array $assignGroupArray array of (id, value, label)
     */
    private function assignValuesToGroup($assignGroupArray, $indexVal, $groupContact)
    {
        $assignGroupArray[$indexVal][0]['id'] = $groupContact['contact_id'];
        $assignGroupArray[$indexVal][0]['value'] = $groupContact['contactname'];
        $assignGroupArray[$indexVal][0]['label'] = $groupContact['contactname'];

        return $assignGroupArray;
    }

    /**
     * function to form array  structure for userrights save
     * 
     * @param array  $userRightsArray user rights array
     * @param array  $formatArray     format array
     * @param array  $contacts        contact array
     * @param string $roleType        T/W
     * 
     * @return array $formatArray array in specific format
     */
    private function formatTeamAdmin($userRightsArray, $formatArray, $contacts, $roleType)
    {
        $groupId = $this->container->getParameter('role_article_admin');
        $role = 'teams';
        if ($roleType == 'T') {
            $grp = 'teamAdmin';
        } else {
            $grp = 'wgAdmin';
        }
        //existing team admin modules
        if (isset($userRightsArray['teams'][$grp]['existing'])) {
            foreach ($userRightsArray['teams'][$grp]['existing'] as $contact => $teams) {
                if (is_array($teams[$role])) {
                    $formatArray['new'][$grp][$groupId]['contact'][$contact]['team'] = $teams[$role];
                }
                if ($teams[$role] == '') {
                    $formatArray['new'][$grp][$groupId]['contact'][$contact]['team'] = 'deleted';
                }
            }
        }
        if (isset($userRightsArray['teams'][$grp]['delete'])) {
            foreach ($userRightsArray['teams'][$grp]['delete'] as $contactId => $true) {
                $formatArray['new'][$grp][$groupId]['contact'][$contactId]['team'] = 'deleted';
            }
        }
        if (isset($contacts['contact'])) {
            foreach ($contacts['contact'] as $value) {
                $contactId = $value;
            }
            if ($contacts[$role] == '') {
                $formatArray['new'][$grp][$groupId]['contact'][$contactId]['team'] = 'deleted';
            } else {
                $formatArray['new'][$grp][$groupId]['contact'][$contactId]['team'] = $contacts[$role];
            }
        }

        return $formatArray;
    }

    /**
     * Function is used to save user rights from user rights setting page.
     *
     * @return object JSON Response Object
     */
    public function saveRoleUserRightsAction()
    {
        $groupId = $this->container->getParameter('club_article_admin');
        $roleGroupId = $this->container->getParameter('role_article_admin');
        $clubId = $this->container->get('club')->get('id');
        $contactId = $this->container->get('contact')->get('id');
        $conn = $this->container->get('database_connection');
        $em = $this->getDoctrine()->getManager();
        $request = $this->container->get('request_stack')->getCurrentRequest();
        // Getting save values from setting page in JSON format
        $userRightsArray = json_decode($request->get('postArr'), true);
        $formatArray['new'] = array();

        // Generating array to delete article admin group of a contact
        if (!empty($userRightsArray['article']['delete'])) {
            $formatArray['delete']['group'][$groupId]['user'] = $userRightsArray['article']['delete'];
        }
        // Generating array when a new group is added to an already existing contact in listing
        if (!empty($userRightsArray['article']['admin'])) {
            $formatArray['new']['group'][$groupId]['contact'] = array();
            foreach ($userRightsArray['article'] as $random) {
                foreach ($random as $val) {
                    foreach ($val['contact'] as $val1) {
                        $formatArray['new']['group'][$groupId]['contact'][$val1] = $val1;
                    }
                }
            }
        }

        if (isset($userRightsArray['teams']['teamAdmin'])) {
            $formatArray['new']['teamAdmin'] = $formatArray['new']['teamAdmin'][$roleGroupId] = $formatArray['new']['teamAdmin'][$roleGroupId]['contact'] = array();
            foreach ($userRightsArray['teams']['teamAdmin'] as $val) {
                $formatArray = $this->formatTeamAdmin($userRightsArray, $formatArray, $val, 'T');
            }
        }

        if (isset($userRightsArray['teams']['wgAdmin'])) {
            $formatArray['new']['wgAdmin'] = $formatArray['new']['wgAdmin'][$roleGroupId] = $formatArray['new']['wgAdmin'][$roleGroupId]['contact'] = array();
            foreach ($userRightsArray['teams']['wgAdmin'] as $val) {
                $formatArray = $this->formatTeamAdmin($userRightsArray, $formatArray, $val, 'W');
            }
        }

        // Calling the function to save user rights
        $em->getRepository('CommonUtilityBundle:SfGuardGroup')->saveUserRights($conn, $formatArray, $clubId, $contactId, $this->container);

        return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('USER_RIGHTS_SAVED_SUCCESS')));
    }
}
