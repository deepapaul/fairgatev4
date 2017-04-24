<?php

namespace Clubadmin\ClubBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Common\UtilityBundle\Repository\Pdo\ClubPdo;

/**
 * Category controller
 */
class CategoryController extends FgController
{

    /**
     * Pre exicute function to allow access to federation only
     *
     */
    public function preExecute()
    {
        parent::preExecute();
        if ($this->clubType != 'federation') {
            $permissionObj = $this->fgpermission;
            $permissionObj->checkClubAccess(0, "club_manage_category");

//          throw $this->createNotFoundException($this->clubTitle.' have no access to this page');
        }
    }
//end preExecute()

    /**
     * Get all club filter data
     *
     * @return template
     */
    public function classificationListAction()
    {
        $clubObj = new ClubPdo($this->container);
        $dataArray = $clubObj->getClubClassifications($this->clubId, true);
        
        $clubName = '';
        $breadCrumb = array(
            'breadcrumb_data' => array(
                'Clubs' => $this->generateUrl('club_homepage'),
            ),
            'back' => $this->generateUrl('club_homepage')
        );

        return $this->render('ClubadminClubBundle:classification:editcategory.html.twig', array('result_data' => $dataArray, 'clubName' => $clubName, 'catType' => '', 'breadCrumb' => $breadCrumb, 'clubType' => $this->clubType, 'clubDefaultLang' => $this->get('club')->get('club_default_lang'), 'clubLanguages' => $this->clubLanguages));
    }
//end classificationListAction()

    /**
     * Function is used to manage class
     *
     * @return template
     */
    public function updateClassificationAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $catArr = json_decode($request->request->get('catArr'), true);
            $catType = $request->request->get('catType', 'role');
            if (count($catArr) > 0) {
                $tableName = 'fg_club_classification';
                $successMsg = 'CLASSIFICATION_SORTING_SAVED';
                $this->generatequeryAction($tableName, $catArr, $this->clubId);
            }

            return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans($successMsg)));
        }
    }
//end updateClassificationAction()

    /**
     * Function is used to manage class
     *
     * @param Int $list List value
     *
     * @return template
     */
    public function manageClassAction(Request $request)
    {
        $catId = $request->get('cat_id', '0');
        $clubObj = new ClubPdo($this->container);
        $categorySettings = $clubObj->getClubClassifications($this->clubId, true, $catId);
        if (count($categorySettings) <= 0) {
            $permissionObj = $this->fgpermission;
            $permissionObj->checkClubAccess(0, "backend_club");
//            throw $this->createNotFoundException($this->get('translator')->trans('CATEGORY_NOT_VALID'));
        }
        $categorySettings = current($categorySettings);
        if ($categorySettings['contactCount'] == '') {
            $categorySettings['contactCount'] = 0;
        }

        /* Getting previous and next category ids for linking starts */
        $prevLink = '';
        $nextLink = '';

        $catResult = $this->em->getRepository('CommonUtilityBundle:FgClubClassification')->getClubClassificationIds($this->clubId);
        $catIds = explode(',', $catResult[0]['ids']);
        if (in_array($catId, $catIds)) {
            $currentCatId = current($catIds);
            if ($currentCatId == $catId) {
                $prevCatId = '';
                $nextCatId = next($catIds);
            } else {
                while (($nextCatId = next($catIds)) !== null) {
                    if ($nextCatId == $catId) {
                        break;
                    }
                }
                $prevCatId = prev($catIds);
                $currCatId = next($catIds);
                $nextCatId = next($catIds);
            }
            $prevLink = $prevCatId ? $this->generateUrl('manage_classes', array('cat_id' => $prevCatId)) : '#';
            $nextLink = $nextCatId ? $this->generateUrl('manage_classes', array('cat_id' => $nextCatId)) : '#';
        }
        /* Getting previous and next category ids for linking ends */

        $backLink = $this->generateUrl('classification_list');
        $breadCrumb = array(
            'back' => $backLink
        );
        if (($prevLink != '') || ($nextLink != '')) {
            $breadCrumb['prev'] = $prevLink;
            $breadCrumb['next'] = $nextLink;
        }

        return $this->render('ClubadminClubBundle:classification:categorysettings.html.twig', array('result_data' => $categorySettings, 'clubId' => $this->clubId, 'contactId' => $this->contactId, 'clubIdentifier' => $this->clubIdentifier, 'clubDefaultLang' => $this->get('club')->get('club_default_lang'), 'clubLanguages' => $this->clubLanguages, 'clubType' => $this->clubType, 'breadCrumb' => $breadCrumb, 'clubTitle' => $this->clubTitle, 'backLink' => $backLink));
    }
//end manageClassAction()

    /**
     * Function is used to get get classes
     *
     * @return template
     */
    public function updateClassesAction(Request $request)
    {
        $cacheDomainName = $this->container->getParameter('cache_apc_domain_name');
        if ($request->getMethod() == 'POST') {
            $catArr = json_decode($request->request->get('catArr'), true);
            $fnAssign = $request->request->get('function_assign', 'none');
            $catType = $request->request->get('type', 'role');
            if (count($catArr) > 0) {
                $this->em->getRepository('CommonUtilityBundle:FgClubClassification')->saveClassificationSettings($catArr, $fnAssign, $this->get('club')->get('club_default_lang'), $this->clubLanguages, $this->clubId, $this->contactId, $catType, $cacheDomainName, $this->clubTeamId , $this->container);
            }
            $successMessage = 'CLASSIFICATION_SETTINGS_SAVED';

            return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans($successMessage)));
        }
    }
//end updateClassesAction()

    /**
     * Function is used to get get classes
     *
     * @return json
     */
    public function getClassesAction(Request $request)
    {
        $catId = $request->get('cat_id', '0');
        $categorySettings = $this->em->getRepository('CommonUtilityBundle:FgClubClassification')->getClassificationClasses($this->clubId, $catId, $this->contactId, false);
  
        return new JsonResponse($categorySettings);
    }
//end getClassesAction()

    /**
     * Function is used to get getClassLogsAction
     *
     * @return json
     */
    public function getClassLogsAction(Request $request)
    {
        $catId = $request->get('cat_id', '0');
        $type = $request->get('type', '');
        $logType = '';
        $classId = $request->get('classId', '0'); //role id
        $hierarchyClubIds = array();
        $hierarchyClubIdArr = array();
        if (!in_array($this->clubType, array('federation_club', 'sub_federation_club'))) {
            $clubPdo = new \Common\UtilityBundle\Repository\Pdo\ClubPdo($this->container);
            $resultClubs = $clubPdo->getHierarchyClubs();
            foreach ($resultClubs as $resultClub) {
                $hierarchyClubIds[] = $resultClub['id'];
                $hierarchyClubIdArr[$resultClub['id']] = $resultClub['title'];
            }
        }
        $logTabs = array('1' => 'assignments', '2' => 'data');
        $clubObj = new ClubPdo($this->container);
        $logdisplay = $clubObj->getClassLog($this->clubId, $classId);
        $jsonData = array('logdisplay' => $logdisplay, 'hierarchyClubIdArr' => $hierarchyClubIdArr, 'logTabs' => $logTabs);

        return new JsonResponse($jsonData);
    }
//end getClassLogsAction()
}
