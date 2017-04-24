<?php

/**
 * RoleController
 *
 * This controller was created for handling category role functionalities
 *
 * @package    ClubadminContactBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */

namespace Clubadmin\ContactBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Common\UtilityBundle\Repository\Pdo\ContactPdo;
use Symfony\Component\HttpFoundation\Request;
/**
 * RoleController used for managing contact assignment functionalities
 *
 */
class RoleController extends FgController {

    /**
     * Executes editcategory action
     *
     * Function to edit role category (sorting,deletion)
     *
     * @return template
     */
    public function editcategoryAction(Request $request) {
       
        $session = $request->getSession();
        $session->set('categorysettings_referrer', 'categorylisting'); // For next/prev link in category settings page.
        $catType = $request->get('cat_type', 'club');
        $dataArray = array();
        $cattypeArray=array("club","fed_cat","filter_role","team","club");
        if(!in_array($catType,$cattypeArray)) {
          $this->fgpermission->checkClubAccess('','editrolecategory') ;  
        }
        if ($catType == 'club' || $catType == 'fed_cat' || $catType == 'filter_role') {
            $dataArray = $this->em->getRepository('CommonUtilityBundle:FgRmCategory')->getRoleCatDetails($this->clubId, $catType, '', true);
            $clubName = ($catType == 'club') ? '' : $this->clubTitle;
            $roleType = ($catType == 'club') ? 'Roles' : $clubName . ' ' . 'roles';
            if ($catType == 'filter_role') {
                $roleType = 'Filter roles';
            }
        } elseif ($catType == 'team') {
            $dataArray = $this->em->getRepository('CommonUtilityBundle:FgTeamCategory')->getTeamCatDetails($this->clubId, '', true, '', $this->clubTeamId, true);
            $roleType = 'Teams';
        }
        $breadCrumb = array(
            'back' => '#'
        );
        if(count($dataArray)== 0){
          // $this->fgpermission->checkClubAccess('','editrolecategory') ;
        }
        $clubDefaultLang = $this->get('club')->get('club_default_lang');

        return $this->render('ClubadminContactBundle:Role:editcategory.html.twig', array('result_data' => $dataArray, 'clubName' => $clubName, 'breadCrumb' => $breadCrumb, 'catType' => $catType, 'clubType' => $this->clubType, 'clubDefaultLang' => $clubDefaultLang, 'clubLanguages' => $this->clubLanguages));
    }

    /**
     * Executes editcategory action
     *
     * Function to update role category (sorting, visibility, deletion)
     *
     * @return template
     */
    public function updatecategoryAction(Request $request) {
        
        if ($request->getMethod() == 'POST') {
            $catArr = json_decode($request->request->get('catArr'), true);
            $catType = $request->request->get('catType', 'role');
            $terminologyService = $this->get('fairgate_terminology_service');
            //executiveboard
            if (count($catArr) > 0) {
                if ($catType == 'team') {
                    $tableName = 'fg_team_category';
                    $successMsg = $this->get('translator')->trans('TEAMCATEGORY_SORTING_SAVED', array("%Team%" => ucfirst($terminologyService->getTerminology('Team', $this->container->getParameter('singular')))));
                } elseif ($catType == 'club' || $catType == 'fed_cat' || $catType == 'filter_role') {
                    $tableName = 'fg_rm_category';
                    $successMsg = $this->get('translator')->trans('ROLECATEGORY_SORTING_SAVED');
                    if ($catType == 'filter_role') {
                        $successMsg = $this->get('translator')->trans('FILTER_ROLE_CATEGORY_SORTING_SAVED');
                    }
                }
                $result = $this->generatequeryAction($tableName, $catArr, $this->clubId);
                $result['type'] = 'category-'.$catType;
            }

            return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $successMsg,'result' => $result));
        }
    }

    /**
     * Executes categorysettings action
     *
     * Function to update role category, role and functions (add, edit, delete, translate)
     *
     * @return template
     */
    public function categorysettingsAction(Request $request) {

        
        $session = $request->getSession();
        $catId = $request->get('cat_id', '0');
        $bookedModuleDetails = $this->get('club')->get('bookedModulesDet');
        $club = $this->get('club');
        $clubType = $club->get('type');
        $categorySettings = $this->em->getRepository('CommonUtilityBundle:FgRmCategory')->getRoleCatDetails($this->clubId, '', $catId);
        if (count($categorySettings) <= 0) {
            $this->fgpermission->checkClubAccess('','rolecategorysetting',$this->get('translator')->trans('CATEGORY_NOT_VALID'));
        }
        $categorySettings = current($categorySettings);
        if ($categorySettings['contactCount'] == '') {
            $categorySettings['contactCount'] = 0;
        }

        /* Getting previous and next category ids for linking starts */
        $prevLink = '';
        $nextLink = '';
        $referrerPage = $session->get('categorysettings_referrer', 'sidebar');
        $checkActive = ($referrerPage == 'sidebar') ? true : false;
        
        $catType = $categorySettings['isFedCategory'] ? 'fed_cat' : 'club';
        $pageTitle = $catType;
        $terminologyService = $this->get('fairgate_terminology_service');
        if($catType=='fed_cat' && $clubType=='sub_federation'){
           $pageTitle = $this->get('translator')->trans('BREADCRUMB_TITLE_SUBFED_ROLECATEGORY', array("%subfederation%" => ucfirst($terminologyService->getTerminology('Subfederation', $this->container->getParameter('singular'))))); 
        }
         else{
             $pageTitle = $this->get('translator')->trans('BREADCRUMB_TITLE_FED_ROLECATEGORY', array("%Federation%" => ucfirst($terminologyService->getTerminology('Federation', $this->container->getParameter('singular')))));
         }
            
        $roleCatResult = $this->em->getRepository('CommonUtilityBundle:FgRmCategory')->getCategoryIds($this->clubId, $catType, $checkActive);
        $roleCatIds = array();
        foreach ($roleCatResult as $roleCatData) {
            $roleCatIds[] = $roleCatData['roleCatId'];
        }
        if (in_array($catId, $roleCatIds)) {
            $currentCatId = current($roleCatIds);
            if ($currentCatId == $catId) {
                $prevCatId = '';
                $nextCatId = next($roleCatIds);
            } else {
                while (($nextCatId = next($roleCatIds)) !== null) {
                    if ($nextCatId == $catId) {
                        break;
                    }
                }
                $prevCatId = prev($roleCatIds);
                $currCatId = next($roleCatIds);
                $nextCatId = next($roleCatIds);
            }
            $prevLink = $prevCatId ? $this->generateUrl('role_category_settings', array('cat_id' => $prevCatId)) : '#';
            $nextLink = $nextCatId ? $this->generateUrl('role_category_settings', array('cat_id' => $nextCatId)) : '#';
        }
        /* Getting previous and next category ids for linking ends */

        $backLink = ($referrerPage == 'sidebar') ? '#' : $this->generateUrl('edit_role_category', array('cat_type' => $catType));
        $breadCrumb = array(
            'breadcrumb_data' => array(
                'Contacts' => $this->generateUrl('contact_index'),
                'Active Contacts' => $this->generateUrl('contact_index'),
                $categorySettings['title'] => '#'
            ),
            'back' => $backLink
        );
        if (($prevLink != '') || ($nextLink != '')) {
            $breadCrumb['prev'] = $prevLink;
            $breadCrumb['next'] = $nextLink;
        }
        $clubDefaultLang = $this->get('club')->get('club_default_lang');

        return $this->render('ClubadminContactBundle:Role:categorysettings.html.twig', array('result_data' => $categorySettings, 'clubId' => $this->clubId, 'contactId' => $this->contactId, 'clubIdentifier' => $this->clubIdentifier, 'clubDefaultLang' => $clubDefaultLang, 'clubLanguages' => $this->clubLanguages, 'clubType' => $this->clubType, 'breadCrumb' => $breadCrumb, 'clubTitle' => $this->clubTitle, 'backLink' => $backLink, 'bookedModules' => $bookedModuleDetails,'pageTitleCat'=>$pageTitle));
    }

    /**
     * Executes rolefunctiondata action
     *
     * Function to propagate data for category role settings page
     *
     * @return template
     */
    public function rolefunctiondataAction(Request $request) {
       
        $catId = $request->get('cat_id', '0');
        $categorySettings = $this->em->getRepository('CommonUtilityBundle:FgRmCategory')->getCategorySettingsDetails($this->clubId, $catId, $this->contactId, $this->clubType);

        return new JsonResponse($categorySettings);
    }

    /**
     * Executes saverolefunction action
     *
     * Function to update role category settings page
     *
     * @return template
     */
    public function saverolefunctionAction(Request $request) {
        $terminologyService = $this->get('fairgate_terminology_service');
        $exeBoard = ucfirst($terminologyService->getTerminology('Executive Board', $this->container->getParameter('singular')));
        $team = ucfirst($terminologyService->getTerminology('Team', $this->container->getParameter('singular')));
        $clubDefaultLang = $this->get('club')->get('club_default_lang');
        
        if ($request->getMethod() == 'POST') {
            $catArr = json_decode($request->request->get('catArr'), true);
            $catid = json_decode($request->request->get('catid'), true);
            $fnAssign = $request->request->get('function_assign', 'none');
            $catType = $request->request->get('type', 'role');
            $catFinalArr = array();
            $catFinalArr[$catid] = $catArr;
            $catFinalArr = $catid ? $catFinalArr : $catArr;

            if (count($catFinalArr) > 0) {
                $translations = array('Description' => $this->get('translator')->trans('TEAMCATEGORY_SETTINGS_DESCRIPTION'), 'Category' => $this->get('translator')->trans('TEAM_CATEGORY'), 'isActiveField' => $this->get('translator')->trans('TEAM_LOG_ISACTIVE_FIELD'),'visibleForAll' => $this->get('translator')->trans('VISIBILITY_FOREIGN_CONTACTS'), 'isActiveYes' => $this->get('translator')->trans('YES'), 'isActiveNo' => $this->get('translator')->trans('NO'));
                $categorySettingsResult = $this->em->getRepository('CommonUtilityBundle:FgRmCategory')->saverolecatsettingsAction($catFinalArr, $fnAssign, $clubDefaultLang, $this->clubLanguages, $this->clubId, $this->contactId, $catType, $this->clubTeamId, $this->container, $translations);
                $categorySettingsResult['type'] = $catType;
                $categorySettingsResult['catid'] = $catid;
            }
            $userrightsArr  = json_decode($request->request->get('userrightsArr'),true);
            if (count($userrightsArr) > 0) {
                $save = $this->saveUserRights($userrightsArr);
            }
            $successMessage = ($catType == 'role') ? 'ROLECATEGORY_SETTINGS_SAVED' : (($catType == 'workgroup') ? 'WORKGROUP_SETTINGS_SAVED' : (($catType == 'team') ? 'TEAM_SETTINGS_SAVED' : (($catType == 'executiveboard') ? 'EXECUTIVEBOARD_SETTINGS_SAVED' : 'SETTINGS_SAVED')));

            return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans($successMessage, array('%executiveboard%' => $exeBoard, '%teams%' => $team)), 'result' => $categorySettingsResult));
        }
    }

    /**
     * function to display the team settings page
     *
     * @return array
     */
    public function teamcategorysettingsAction(Request $request) {
        
        $session = $request->getSession();
        $catId = $request->get('cat_id', '0');
        $frontendBooked = true;
        $club = $this->get('club');
        $bookedModuleDetails = $club->get('bookedModulesDet');
        $categorySettings = $this->em->getRepository('CommonUtilityBundle:FgTeamCategory')->getTeamCatDetails($this->clubId, '', true, true, $this->clubTeamId);
       
        if (count($categorySettings[$catId]) <= 0) {
            //throw $this->createNotFoundException($this->get('translator')->trans('TEAMCATEGORY_NOT_VALID'));
            $this->fgpermission->checkClubAccess('','teamcategorysetting');
        }
        if (array_key_exists('functions', $categorySettings)) {
            $functions = array_unique($categorySettings['functions']);
            unset($categorySettings['functions']);
        }
        //for getting all functions in team section display
        if (array_key_exists('team_functions', $categorySettings)) {
            $allFunctions = $categorySettings['team_functions'];
            unset($categorySettings['team_functions']);
        }
        //for previous and next links
        $prevLink = '';
        $nextLink = '';
        $categoryIds = array();
        foreach ($categorySettings as $key => $val) {
            $categoryIds[] = $val['id'];
        }
        if (in_array($catId, $categoryIds)) {
            $currentCatId = current($categoryIds);
            if ($currentCatId == $catId) {
                $prevCatId = '';
                $nextCatId = next($categoryIds);
            } else {
                while (($nextCatId = next($categoryIds)) !== null) {
                    if ($nextCatId == $catId) {
                        break;
                    }
                }
                $key = array_search($catId, $categoryIds);
                $nextkey = $key + 1;
                $nextCatId = $categoryIds[$nextkey];
                $prevCatId = prev($categoryIds);
            }
            $prevLink = $prevCatId ? $this->generateUrl('team_category_settings', array('cat_id' => $prevCatId)) : '#';
            $nextLink = $nextCatId ? $this->generateUrl('team_category_settings', array('cat_id' => $nextCatId)) : '#';
        }
        //ends
        $breadCrumb = array(
            'breadcrumb_data' => array(
                'Contacts' => $this->generateUrl('contact_index'),
                'Active Contacts' => $this->generateUrl('contact_index')
            ),
            'back' => '#'
        );
        if (($prevLink != '') || ($nextLink != '')) {
            $breadCrumb['prev'] = $prevLink;
            $breadCrumb['next'] = $nextLink;
        }
        $clubDefaultLang = $this->get('club')->get('club_default_lang');
        $contactNameUrl = str_replace('25','',$this->generateUrl('userrightscontact_name_search',array('term' => '%QUERY')));

        return $this->render('ClubadminContactBundle:Role:teamcategorysettings.html.twig', array('contactNameUrl'=>$contactNameUrl,'teamtitle' => $categorySettings[$catId]['title'], 'result_data' => $categorySettings, 'clubIdentifier' => $this->clubIdentifier, 'clubDefaultLang' => $clubDefaultLang, 'clubLanguages' => $this->clubLanguages, 'clubType' => $this->clubType, 'breadCrumb' => $breadCrumb, 'catId' => $catId, 'frontendBooked' => $frontendBooked, 'functions' => $functions, 'all_functions' => $allFunctions, 'teamCatId' => $this->clubTeamId, 'clubId' => $this->clubId, 'contactId' => $this->contactId, 'bookedModules' => $bookedModuleDetails));
    }

    /**
     * Executes rolefunctiondata action
     *
     * Function to propagate data for category role settings page
     * @return json
     */
    public function teamfunctiondataAction(Request $request) {
       
        $catId = $request->get('cat_id', '0');
        $teamCatId = $request->get('teamCatId', '0');
        $categorySettings = $this->em->getRepository('CommonUtilityBundle:FgRmCategory')->getTeamDetailsofCategory($this->clubId, $catId, $this->contactId, $teamCatId, $this->clubType);

        return new JsonResponse($categorySettings);
    }

    /**
     * Executes execboardfunctionsettings action
     *
     * @return template
     */
    public function execboardfunctionsettingsAction() {
        $terminologyService = $this->get('fairgate_terminology_service');
        $dataArray = array();
        if ($this->clubType == 'federation') {
            $dataArray = $this->em->getRepository('CommonUtilityBundle:FgRmFunction')->getExecutiveBoardFunctions($this->clubId, $this->clubId, $this->clubType);
        } else {
            //throw $this->createNotFoundException($this->get('translator')->trans('%CLUBNAME%_HAVE_NO_ACCESS_TO_PAGE', array('%CLUBNAME%' => $this->clubTitle)));
            $this->fgpermission->checkClubAccess('','executiveboardsetting');
        }
        $catId = $this->clubWorkgroupId;
        $roleId = $this->clubExecutiveBoardId;
        $breadCrumb = array(
            'breadcrumb_data' => array(
                'Contacts' => $this->generateUrl('contact_index'),
                'Active Contacts' => $this->generateUrl('contact_index'),
                $terminologyService->getTerminology('Executive board', $this->container->getParameter('singular')) . ' functions' => '#'
            )
        );
        $clubDefaultLang = $this->get('club')->get('club_default_lang');

        return $this->render('ClubadminContactBundle:Role:execboardfunctionsettings.html.twig', array('result_data' => json_encode($dataArray), 'breadCrumb' => $breadCrumb, 'clubLanguages' => $this->clubLanguages, 'clubDefaultLang' => $clubDefaultLang, 'catId' => $catId, 'roleId' => $roleId, 'contactId' => $this->contactId));
    }

    /**
     * Executes execboardfunctionsettings action
     *
     * Action to list, add, edit, delete executive board functions
     *
     * @return template
     */
    public function workgroupsettingsAction() {
        $bookedModuleDetails = $this->get('club')->get('bookedModulesDet');
        $workgroupId= $this->get('club')->get('club_workgroup_id');
        $execBoardId= $this->get('club')->get('club_executiveboard_id');
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $wgId = $request->get('wgId');
        $exbrdId = $request->get('exbdId');
        if(!(($wgId==1 && $exbrdId==1) || ($wgId == $workgroupId && $exbrdId == $execBoardId))) {
            $this->fgpermission->checkClubAccess('','') ;   
        }       
        $breadCrumb = array(
            'breadcrumb_data' => array(
                'Contacts' => $this->generateUrl('contact_index'),
                'Active Contacts' => $this->generateUrl('contact_index'),
                'Workgroups' => '#'
            ),
            'back' => '#'
        );
        $clubDefaultLang = $this->get('club')->get('club_default_lang');
        $contactNameUrl = str_replace('25','',$this->generateUrl('userrightscontact_name_search',array('term' => '%QUERY')));
        
        return $this->render('ClubadminContactBundle:Role:workgroupsettings.html.twig', array('contactNameUrl'=>$contactNameUrl,'clubIdentifier' => $this->clubIdentifier, 'clubLanguages' => $this->clubLanguages, 'clubDefaultLang' => $clubDefaultLang, 'breadCrumb' => $breadCrumb, 'catId' => $this->clubWorkgroupId, 'executiveBoardId' => $this->clubExecutiveBoardId, 'federationId' => $this->federationId, 'clubType' => $this->clubType, 'clubId' => $this->clubId, 'contactId' => $this->contactId, 'bookedModules' => $bookedModuleDetails, 'wgId'=>$wgId, 'execbrdId'=>$exbrdId));
    }

    /**
     * Function to get workgroup details
     *
     * @return template
     */
    public function workgroupdetailsAction() {
        $catId = $this->clubWorkgroupId;
        $dataArray = array();
        
        if ($catId > 0) {
            $dataArray['rolefunctiondata'] = $this->em->getRepository('CommonUtilityBundle:FgRmCategory')->getCategorySettingsDetails($this->clubId, $catId, $this->contactId, $this->clubType);
            $dataArray['execboardfunctions'] = '';
            
            if ($this->federationId <> $this->clubId ) {
                $dataArray['execboardfunctions'] = $this->em->getRepository('CommonUtilityBundle:FgRmFunction')->getExecutiveBoardFunctions($this->federationId, $this->clubId, $this->clubType);
            }
        }

        return new JsonResponse($dataArray);
    }

    /**
     * Executes log listing
     *
     * Function to propagate data for category role settings page
     * @return json
     */
    public function logdataAction(Request $request) {
       
        $type = $request->get('type', '');
        $logType = '';
        $from = '';
        $functionId = 0;
        $startdate = $request->get('startdate', '');
        $enddate = $request->get('enddate', '');
        if ($type == 'team') {
            $catId = $request->get('teamCatId', '0'); //team_category_id
            $roleId = $request->get('roleId', '0'); //team id
            $logType = 'role';
            $logTabs = array('1' => 'assignments', '2' => 'data');
        } elseif ($type == 'role') {
            $catId = $request->get('CatId', '0'); //category_id
            $roleId = $request->get('roleId', '0'); //role id
            $logType = 'role';
            $logTabs = array('1' => 'assignments', '2' => 'data');
        } elseif ($type == 'function') {
            //flag to know whether team function,role function or workgroup function
            $logType = 'function';
            $functionId = $request->get('roleId'); //function id
            $logTabs = array('1' => 'assignments', '2' => 'data');
        }
        $hierarchyClubIds = array();
        $hierarchyClubIdArr = array();
        if (!in_array($this->clubType, array('federation_club', 'sub_federation_club'))) {
            $conn = $this->em->getConnection();
            $resultClubs = $conn->fetchAll("SELECT c.id as id, c.title as title FROM (SELECT sublevelClubs(id) AS id, @level AS level FROM (SELECT  @start_with :='{$this->clubId}',@id := @start_with,@level := 0) vars, fg_club WHERE @id IS NOT NULL) c1 JOIN fg_club c ON c.id = c1.id AND (c.is_federation = 0)");
            $conn->close();
            foreach ($resultClubs as $resultClub) {
                $hierarchyClubIds[] = $resultClub['id'];
                $hierarchyClubIdArr[$resultClub['id']] = $resultClub['title'];
            }
        }
        if ($logType == 'role') {
            $logdisplay = $this->em->getRepository('CommonUtilityBundle:FgRmRoleLog')->getRoleLog($this->clubId, $roleId, $this->clubType, $this->clubExecutiveBoardId, $this->federationId, $hierarchyClubIds, $this->clubDefaultLang);
        } elseif ($logType == 'function') {
            $logdisplay = $this->em->getRepository('CommonUtilityBundle:FgRmFunctionLog')->getFunctionLog($functionId, $this->clubId, $this->clubType, $this->clubDefaultLang);
        }
        $jsonData = array('logdisplay' => $logdisplay, 'hierarchyClubIdArr' => $hierarchyClubIdArr, 'logTabs' => $logTabs);

        return new JsonResponse($jsonData);
    }

    /**
     * Executes Filter role settings action
     *
     * @return template
     */
    public function filterrolesettingsAction(Request $request) {
      
        $catId = $request->get('cat_id', '0');
        $session = $request->getSession();
        $categorySettings = $this->em->getRepository('CommonUtilityBundle:FgRmCategory')->getRoleCatDetails($this->clubId, 'filter_role', $catId);
        if(count($categorySettings)==0) {
         $this->fgpermission->checkClubAccess('','filterrolesetting');   
        }
        $categorySettings = current($categorySettings);
        if (count($categorySettings) <= 0) {
            //throw $this->createNotFoundException($this->get('translator')->trans('CATEGORY_NOT_VALID'));
            $this->fgpermission->checkClubAccess('','filterrolesetting');
        }
        if ($categorySettings['contactCount'] == '') {
            $categorySettings['contactCount'] = 0;
        }

        /* Getting previous and next category ids for linking starts */
        $prevLink = '';
        $nextLink = '';
        $referrerPage = $session->get('categorysettings_referrer', 'sidebar');
        $checkActive = ($referrerPage == 'sidebar') ? true : false;
        $catType = 'club';
        $roleCatResult = $this->em->getRepository('CommonUtilityBundle:FgRmCategory')->getCategoryIds($this->clubId, $catType, $checkActive, 'filterrole');
        $roleCatIds = array();
        foreach ($roleCatResult as $roleCatData) {
            $roleCatIds[] = $roleCatData['roleCatId'];
        }
        if (in_array($catId, $roleCatIds)) {
            $currentCatId = current($roleCatIds);
            if ($currentCatId == $catId) {
                $prevCatId = '';
                $nextCatId = next($roleCatIds);
            } else {
                while (($nextCatId = next($roleCatIds)) !== null) {
                    if ($nextCatId == $catId) {
                        break;
                    }
                }
                $prevCatId = prev($roleCatIds);
                $currCatId = next($roleCatIds);
                $nextCatId = next($roleCatIds);
            }
            $prevLink = $prevCatId ? $this->generateUrl('filter_role_settings', array('cat_id' => $prevCatId)) : '#';
            $nextLink = $nextCatId ? $this->generateUrl('filter_role_settings', array('cat_id' => $nextCatId)) : '#';
        }
        /* Getting previous and next category ids for linking ends */

        $backLink = ($referrerPage == 'sidebar') ? '#' : $this->generateUrl('edit_role_category', array('cat_type' => 'filter_role'));
        $breadCrumb = array(
            'breadcrumb_data' => array(
                'Contacts' => $this->generateUrl('contact_index'),
                'Active Contacts' => $this->generateUrl('contact_index'),
                $categorySettings['title'] => '#'
            ),
            'back' => $backLink
        );
        if (($prevLink != '') || ($nextLink != '')) {
            $breadCrumb['prev'] = $prevLink;
            $breadCrumb['next'] = $nextLink;
        }
        $clubDefaultLang = $this->get('club')->get('club_default_lang');

        return $this->render('ClubadminContactBundle:Role:filterrolesettings.html.twig', array('result_data' => $categorySettings, 'clubId' => $this->clubId, 'contactId' => $this->contactId, 'clubIdentifier' => $this->clubIdentifier, 'clubDefaultLang' => $clubDefaultLang, 'clubLanguages' => $this->clubLanguages, 'breadCrumb' => $breadCrumb, 'clubTitle' => $this->clubTitle, 'backLink' => $backLink, 'contactId' => $this->contactId));
    }

    /**
     * Executes rolefunctiondata action
     *
     * Function to propagate data for category role settings page
     *
     * @return template
     */
    public function filterroledataAction(Request $request) {
       
        $catId = $request->get('cat_id', '0');
        $filterRoles = $this->em->getRepository('CommonUtilityBundle:FgRmCategory')->getFilterRoleDetails($this->clubId, $catId, $this->contactId);

        return new JsonResponse($filterRoles);
    }

    /**
     * Executes rolefunctiondata action
     *
     * Function to propagate data for category role settings page
     *
     * @return template
     */
    public function roleexceptioncontactAction(Request $request) {
        
        $roleId = $request->get('role_id', '0');
        $exceptionContacts[] = $this->em->getRepository('CommonUtilityBundle:FgRmRoleManualContacts')->getExceptionContactsOfRole($roleId);

        return new JsonResponse($exceptionContacts);
    }

    /**
     * Function to save filter data
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function savefilterroleAction(Request $request) {
        
        if ($request->getMethod() == 'POST') {
            $catArr = json_decode($request->get('saveData'), true);
            $fnAssign = 'none';
            $catType = 'filterrole';
            $clubDefaultLang = $this->get('club')->get('club_default_lang');
            if (count($catArr) > 0) {
                $result = $this->em->getRepository('CommonUtilityBundle:FgRmCategory')->saverolecatsettingsAction($catArr, $fnAssign, $clubDefaultLang, $this->clubLanguages, $this->clubId, $this->contactId, $catType, $this->clubTeamId, $this->container);
                $result['type'] = $catType;
                $result['catid'] = array_keys($catArr)[0];
                
            }
            $successMessage = 'FILTERROLECATEGORY_SETTINGS_SAVED';

            return new JsonResponse(array('status' => 'SUCCESS', 'noparentload' => true, 'flash' => $this->get('translator')->trans($successMessage),'result' => $result));
        }
    }

    /**
     * Function to get the sponsored details of team
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function teamsponsoredbyAction(Request $request) {
        
        $roleId = $request->get('roleId', '0');
        $sponsorDetails = $this->em->getRepository('CommonUtilityBundle:FgSmBookingDeposited')->getAllServicesDetailsOfContact($this->clubId, $roleId, "team");
        $contactpath = $this->generateUrl('render_contact_overview', array('offset' => 'dummyOffset', 'contact' => 'dummyContactId'));
        $jsonData = array('sponsordata' => $sponsorDetails, 'contactpath' => $contactpath);

        return new JsonResponse($jsonData);
    }

     /**
     * role userrights page
     * @param int $role role id
     * @return template
     */
    public function roleUserrightsAction(Request $request){

       
        $role = $request->get('role');
        $mod = $request->get('type');
        $type = ($mod == 'team')?'T':'W';

        //get team admin list
        $club = $this->container->get('club');
        $masterTable = $club->get('clubTable');
        $grpAdmins = $this->em->getRepository('CommonUtilityBundle:SfGuardGroup')->teamAdminList($this->conn, $this->clubId, 0,$role,$masterTable,$club);
        $autocompleteExclude = json_encode($this->getAllListingBlock($grpAdmins),JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_SLASHES);
        $grpAdmins = json_encode($grpAdmins, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);

        $sectionDropdown = $this->em->getRepository('CommonUtilityBundle:SfGuardGroup')->getAllTeamGroups();
        $sectionDropdown = json_encode($this->formatRights($sectionDropdown));

        return new JsonResponse(
                array('type'=>$type,'roleId'=>$role,'clubId'=>$this->clubId,
                    'contactId'=>$this->contactId,'groupAdmin' => $grpAdmins,
                    'loggedContactId' => $this->contactId,  'exclude' => $autocompleteExclude,
                    'admins' => $sectionDropdown
                   ));

    }

    /**
     * Function is used to get all the user rights blocks as seperate array.
     *
     * @param Array $groupContacts Group-contact details
     *
     * @return Array
     */
    private function getAllListingBlock($groupContacts)
    {
        $existingUserDetails = array();
        $existingSectionUser = array();
        $existingWGUserDetails = array();
        $existingWGSectionUser = array();
        $i = 0;
        $j = 0;
        $k = 0;
        $l = 0;
        $prevContactId = '';
        // Looping array containing all user rights details
        foreach ($groupContacts as $groupContact) {
            if ($groupContact['module_type'] == 'all' && $groupContact['type'] == 'role' && $groupContact['roleType'] == 'T') {
                $existingUserDetails = $this->assignValuesToGroup($existingUserDetails, $i, $groupContact);
                $i++;
            }
            if ($groupContact['module_type'] == 'all' && $groupContact['type'] == 'role' && $groupContact['roleType'] == 'W') {
                $existingWGUserDetails = $this->assignValuesToGroup($existingWGUserDetails, $k, $groupContact);
                $k++;
            }

            if ($groupContact['module_type'] != 'all' && $groupContact['type'] == 'role' && $groupContact['roleType'] == 'T') {
                $existingSectionUser = $this->assignValuesToGroup($existingSectionUser, $j, $groupContact);
                $j++;
            }
            if ($groupContact['module_type'] != 'all' && $groupContact['type'] == 'role' && $groupContact['roleType'] == 'W') {
                $existingWGSectionUser = $this->assignValuesToGroup($existingWGSectionUser, $l, $groupContact);
                $l++;
            }
        }

        return array('existingUserDetails' => $existingUserDetails, 'existingSectionUser' => $existingSectionUser,
            'existingWGUserDetails' => $existingWGUserDetails, 'existingWGSectionUser' => $existingWGSectionUser, );
    }

    /**
     * Assign vales to common array.
     *
     * @param Array $assignGroupArray Array to be generated for listing
     * @param Int   $indexVal         Index value
     * @param Array $groupContact     Query resuly value
     *
     * @return Array
     */
    private function assignValuesToGroup($assignGroupArray, $indexVal, $groupContact)
    {
        $assignGroupArray[$indexVal][0]['id'] = $groupContact['contact_id'];
        $assignGroupArray[$indexVal][0]['value'] = $groupContact['contactname'];
        $assignGroupArray[$indexVal][0]['label'] = $groupContact['contactname'];

        return $assignGroupArray;
    }

    /**
     * function to format userrights dropdown.
     *
     * @param array $adminSection drop down list of section rights
     *
     * @return array
     */
    private function formatRights($adminSection)
    {
        foreach ($adminSection as $key => $value) {
            switch ($value['name']) {
                case 'ContactAdmin': $adminSection[$key]['transName'] = $this->get('translator')->trans('UR_CONTACT_ADMIN');
                    break;
                case 'ForumAdmin': $adminSection[$key]['transName'] = $this->get('translator')->trans('UR_FORUM_ADMIN');
                    break;
                case 'DocumentAdmin': $adminSection[$key]['transName'] = $this->get('translator')->trans('UR_DOCUMENT_ADMIN');
                    break;
                case 'CalendarAdmin': $adminSection[$key]['transName'] = $this->get('translator')->trans('UR_CALENDAR_ADMIN');
                    break;
                case 'GalleryAdmin': $adminSection[$key]['transName'] = $this->get('translator')->trans('UR_GALLERY_ADMIN');
                    break;
                case 'ArticleAdmin': $adminSection[$key]['transName'] = $this->get('translator')->trans('UR_ARTICLE_ADMIN');
                    break;
            }
        }

        return $adminSection;
    }

    /**
     * Function is used to save user rights
     *
     * @return JSON
     */
    private function saveUserRights($userRightsArray)
    {
        $formatArray['new'] = array();

        if (isset($userRightsArray['teamAdminInt'])) {
            $formatArray['new']['teamAdminInt'] = $formatArray['new']['teamAdminInt'][9] = $formatArray['new']['teamAdminInt'][9]['contact'] = array();
            foreach ($userRightsArray['teamAdminInt'] as $val) {
                $formatArray = $this->formatRoleAdmin($userRightsArray, $formatArray, $val, 'T');
            }
        }

        if (isset($userRightsArray['wgAdminInt'])) {
            $formatArray['new']['wgAdminInt'] = $formatArray['new']['wgAdminInt'][9] = $formatArray['new']['wgAdminInt'][9]['contact'] = array();
            foreach ($userRightsArray['wgAdminInt'] as $val) {
                $formatArray = $this->formatRoleAdmin($userRightsArray, $formatArray, $val, 'W');
            }
        }

        if (isset($userRightsArray['teamSection'])) {
            $formatArray['new']['teamSection'] = $formatArray['new']['teamSection']['contact'] = array();
            $formatArray = $this->formatRoleSection($userRightsArray, $formatArray, 'teamSection');
        }
        if (isset($userRightsArray['wgSection'])) {
            $formatArray['new']['wgSection'] = $formatArray['new']['wgSection']['contact'] = array();
            $formatArray = $this->formatRoleSection($userRightsArray, $formatArray, 'wgSection');
        }

        // Calling the function to save user rights
        $this->em->getRepository('CommonUtilityBundle:SfGuardGroup')->saveUserRights($this->conn, $formatArray, $this->clubId, $this->contactId, $this->container);
        return 1;
    }

    /**
     * format role section area for save.
     *
     * @param array  $userRightsArray user rights array
     * @param array  $formatArray     format array
     * @param string $type            roletype
     *
     * @return array
     */
    private function formatRoleSection($userRightsArray, $formatArray, $type)
    {
        if (isset($userRightsArray[$type]['new'])) {
            foreach ($userRightsArray[$type]['new'] as $userRightsSubArray) {
                if (isset($userRightsSubArray['contact'])) {
                    foreach ($userRightsSubArray['contact'] as $contact => $value) {
                        $contactId = $contact;
                    }
                    if (isset($userRightsSubArray['modules'])) {
                        $formatArray['new'][$type][$contactId]['role'][$userRightsSubArray['modules']['teams']]['groups'] = $userRightsSubArray['modules']['groups'];
                    }
                }
            }
        }
        //existing team admin modules
        if (isset($userRightsArray[$type]['existing'])) {
            foreach ($userRightsArray[$type]['existing'] as $contactId => $teams) {
                if ($teams['deleted'] == 1) {
                    $formatArray['new'][$type][$contactId]['deleted'] = 1;
                    continue;
                }
                foreach ($teams as $teamId => $modules) {
                    if (is_array($modules)) {
                        if ($modules['modules'] == '') {
                            $formatArray['new'][$type][$contactId]['role'][$teamId]['groups'] = 'deleted';
                        } else {
                            $formatArray['new'][$type][$contactId]['role'][$teamId]['groups'] = $modules['modules'];
                        }
                    } elseif ($modules == 1) {
                        $formatArray['new'][$type][$contactId]['role'][$teamId]['groups'] = 'deleted';
                    }
                }
            }
        }

        return $formatArray;
    }

    /**
     * function to form array  structure for userrights save.
     *
     * @param array $userRightsArray user rights array
     * @param array $formatArray     format array
     * @param array $val             contact array
     *
     * @return array
     */
    private function formatRoleAdmin($userRightsArray, $formatArray, $val, $roleType)
    {
        $groupId = $this->container->getParameter('group_admin');
        $role = 'teams';
        if ($roleType == 'T') {
            $grp = 'teamAdminInt';
        } else {
            $grp = 'wgAdminInt';
        }

        if (isset($userRightsArray[$grp]['delete'])) {
            foreach ($userRightsArray[$grp]['delete'] as $contactId => $team) {
                $formatArray['new'][$grp][$groupId]['contact'][$contactId]['delete'] = $team['team'];
            }
        }
        if (isset($val['contact'])) {
            foreach ($val['contact'] as $value) {
                $contactId = $value;
            }
            if ($val[$role] == '') {
                $formatArray['new'][$grp][$groupId]['contact'][$contactId]['team'] = 'deleted';
            } else {
                if(isset($formatArray['new'][$grp][$groupId]['contact'][$contactId]['team'])){
                    foreach($val[$role] as $key=>$team){
                        array_push($formatArray['new'][$grp][$groupId]['contact'][$contactId]['team'], $team);
                    }
                }else{
                    $formatArray['new'][$grp][$groupId]['contact'][$contactId]['team'] = $val[$role];
                }
            }
        }

        return $formatArray;
    }
    /**
     * calculate userrights count
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function calcuRightsCountAction(Request $request){ 

        
        $role = $request->get('roleId');
        $ContactPdo = new ContactPdo($this->container);
        $count = $ContactPdo->getAdministratorCount($role);

         return new JsonResponse(array('count'=>$count));
    }

}
