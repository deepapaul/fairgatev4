<?php

/**
 * AssignmentController
 *
 * @package    ClubadminClubBundle
 * @subpackage Controller
 * @author     neethu.mg
 * @version    Fairgate V4
 *
 */
namespace Clubadmin\ClubBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Common\UtilityBundle\Util\FgUtility;
use Clubadmin\Classes\Clublist;
use Clubadmin\Classes\Clubfilter;
use Clubadmin\ClubBundle\Util\NextpreviousClub;
use Symfony\Component\HttpFoundation\Request;
use Common\UtilityBundle\Repository\Pdo\ClubPdo;

class AssignmentController extends FgController
{

    /**
     * Preexecute function to give access to federation clubs
     */
    public function preExecute()
    {
        parent::preExecute();
        if ($this->clubType != 'federation' && $this->clubType != 'sub_federation') {
            $permissionObj = $this->fgpermission;
            $permissionObj->checkClubAccess(0, "backend_club");
//           throw $this->createNotFoundException($this->clubTitle . ' has no access to this page');
        }
    }

    /**
     * Function to list classification and classes
     * @param int $offset Offset for next previous switching
     * @param int $clubid ClubId
     *
     * @return template
     */
    public function indexAction($offset, $clubid)
    {
        //check if clubid has access
        $clubPdo = new ClubPdo($this->container);
        $sublevelclubs = $clubPdo->getAllSubLevelData($this->clubId);        
        $sublevelclub = array();
        foreach ($sublevelclubs as $key => $value) {
            $sublevelclub[$key] = $value['id'];
        }
        //security check
        $permissionObj = $this->fgpermission;
        $accessCheck = (!in_array($clubid, $sublevelclub)) ? 0 : 1;
        $permissionObj->checkClubAccess($accessCheck, "backend_club");

//        if (!in_array($clubid, $sublevelclub)) {
//            throw $this->createNotFoundException($this->clubTitle . ' has no access to this page');
//        }
        $club = $this->get('club');
        $nextprevious = new NextpreviousClub($this->container);
        $nextPreviousResultset = $nextprevious->nextPreviousClubData($this->contactId, $clubid, $offset, 'club_assignments', 'offset', 'clubid', $flag = 0);
        $breadcrumb = array('back' => '#');
        $clubname = $this->em->getRepository('CommonUtilityBundle:FgClub')->getClubname($clubid, $club->get('default_lang'));
        $assignmentCount = $this->em->getRepository('CommonUtilityBundle:FgClubClassAssignment')->assignmentCount($this->clubType, $this->conn, $clubid);
        $documentsCount = $this->em->getRepository('CommonUtilityBundle:FgDmDocuments')->getCountOfAssignedClubDocuments('CLUB', $this->clubId, $clubid, $this->container);
        $notesCount = $this->em->getRepository('CommonUtilityBundle:FgClubNotes')->getNotesCount($clubid, $this->clubId);
        if (in_array('document', $club->get('bookedModulesDet'))) {
            $tabs = array(0 => "overview", 1 => "data", 2 => "assignment", 3 => "note", 4 => "document", 5 => "log");
        } else {
            $tabs = array(0 => "overview", 1 => "data", 2 => "assignment", 3 => "note", 4 => "log");
        }

        $contCountDetails['asgmntsCount'] = $assignmentCount;
        $contCountDetails['documentsCount'] = $documentsCount;
        $contCountDetails['notesCount'] = $notesCount;
        $tabDetails = FgUtility::getTabsArrayDetails($this->container, $tabs, $offset, $clubid, $contCountDetails, "assignment", "club");
        return $this->render('ClubadminClubBundle:Assignment:index.html.twig', array('breadcrumb' => $breadcrumb, 'clubName' => $clubname[0]['title'], 'clubid' => $clubid, 'offset' => $offset, 'nextPreviousResultset' => $nextPreviousResultset, 'documentsCount' => $documentsCount, 'asgmntsCount' => $assignmentCount, 'notesCount' => $notesCount, 'tabs' => $tabDetails));
    }

    /**
     * Function to list all assignments
     * @param Int $clubid Club Id
     *
     * @return json array
     */
    public function listAllAssignmentsAction($clubid)
    {
        $terminologyService = $this->get('fairgate_terminology_service');
        $clubIdArray = array('clubId' => $this->clubId, 'federationId' => $this->federationId, 'clubType' => $this->clubType, 'defaultClubLang' => $this->clubDefaultLang);
        $clubObj = new ClubPdo($this->container);
        $getAllAssigned = $clubObj->getAllAssignedAssignments($clubIdArray, $this->conn, $clubid, 0);
        $resultArray = array();
        foreach ($getAllAssigned as $key => $val) {
            if ($val['clubType'] == 'federation' && $clubid == $val['clubId']) {
                $resultArray['Classification'][] = $val;
            } else if ($val['clubType'] == 'sub_federation' && $clubid == $val['clubId']) {
                $resultArray['Classification'][] = $val;
            }
        }
        $resultArray['clubType'] = $this->clubType;
        $resultArray['contactId'] = $this->contactId;
        $resultArray['clubUrlIdentifier'] = $this->clubUrlIdentifier;
        $resultArray['loggedclubId'] = $this->clubId;
        $resultArray['club'] = $terminologyService->getTerminology('Club', $this->container->getParameter('singular'));
        $resultArray['clubs'] = $terminologyService->getTerminology('Club', $this->container->getParameter('plural'));

        return new JsonResponse($resultArray);
    }
//end listAllAssignmentsAction()

    /*
     * Function to get drop down datas
     *
     * @return json_object
     */

    public function getDropdownValuesAction()
    {

        $clubIdArray = array('clubId' => $this->clubId, 'federationId' => $this->federationId, 'clubType' => $this->clubType);
        $clubObj = new ClubPdo($this->container);
	$getAllClassificationClass = $clubObj->getAllClassificationClass($this->conn, $clubIdArray, $this->clubDefaultLang);

        return new JsonResponse(array('resultArray' => $getAllClassificationClass));
    }
//end getDropdownValuesAction()

    /**
     * Executes updateClubAssignments action
     *
     * Function to add new, delete assignments
     *
     * @return json_object
     */
    public function updateClubAssignmentAction(Request $request)
    {
        $clubid = ($this->clubType == 'sub_federation') ? $this->federationId : $this->clubId;
        $clubObj = new ClubPdo($this->container);
        if ($request->getMethod() == 'POST') {
            $classificationArr = json_decode($request->request->get('classificationArr'), true);
            if (count($classificationArr) > 0) {
                $resultArray = $this->em->getRepository('CommonUtilityBundle:FgClubClassAssignment')->updateClubAssignments($classificationArr, $clubid, $this->get('club'), $this->contactId, $this->clubId,$clubObj);
                $errorArray = $resultArray['errorArray'];
                $from = $request->get('from');
            }
            if ($from == 'clublist') {
                $terminologyService = $this->get('fairgate_terminology_service');
                $termClub = $terminologyService->getTerminology('Club', $this->container->getParameter('plural'));
                $actionType = $request->get('actionType');
                $totalCount = $request->get('totalClubs');
                $flashMsg = '';
                if ($actionType == 'assign') {
                    $selCount = $resultArray['insertCount'];
                    $flashMsg = '%selcount%_OUT_OF_%totalcount%_CLUBS_ASSIGNED_SUCCESSFULLY';
                } elseif ($actionType == 'move') {
                    $selCount = $resultArray['insertCount'];
                    $flashMsg = '%selcount%_OUT_OF_%totalcount%_CLUBS_MOVED_SUCCESSFULLY';
                } elseif ($actionType == 'remove') {
                    $selCount = $resultArray['deleteCount'];
                    $flashMsg = '%selcount%_OUT_OF_%totalcount%_CLUBS_REMOVED_SUCCESSFULLY';
                }
                $redirect = $this->generateUrl('club_homepage');

                return new JsonResponse(array('status' => 'SUCCESS', 'sync' => 1, 'redirect' => $redirect, 'flash' => $this->get('translator')->trans($flashMsg, array('%selcount%' => $selCount, '%totalcount%' => $totalCount, '%clubs%' => $termClub))));
            } else {
                if (!empty($errorArray)) {
                    return new JsonResponse(array('flash' => $this->get('translator')->trans('NO_MULTIPLE_ASSIGNMENT_POSSIBLE')));
                } else {
                    return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('CLUB_ASSIGNMENT_SAVED')));
                }
            }
        }
    }
//end updateClubAssignmentAction()

    /**
     * Function to assign multi clubs to single assignment
     *
     */
    public function updateAssignmentAction(Request $request)
    {

        $terminologyService = $this->get('fairgate_terminology_service');
        $clubterminology = ucfirst($terminologyService->getTerminology('Club', $this->container->getParameter('plural')));
        $actionType = $request->get('actionType') ? $request->get('actionType') : 'assign';
        $selActionType = $request->get('selActionType') ? $request->get('selActionType') : '';
        $assignmentData = $request->get('assignmentData');
        $dragClassification = isset($assignmentData['dragCategoryId']) ? $assignmentData['dragCategoryId'] : '';
        $dragClass = isset($assignmentData['dragMenuId']) ? $assignmentData['dragMenuId'] : '';
        $dropClassification = isset($assignmentData['dropCategoryId']) ? $assignmentData['dropCategoryId'] : '';
        $dropClass = isset($assignmentData['dropMenuId']) ? $assignmentData['dropMenuId'] : '';
        $classificationLabelText = array('class' => $this->get('translator')->trans('CLASS'));
        $reqArray = array('actionType' => $actionType, 'dragClassification' => $dragClassification, 'dragClass' => $dragClass, 'dropClassification' => $dropClassification, 'dropClass' => $dropClass, 'clubId' => $this->clubId, 'clubType' => $this->clubType, 'selActionType' => $selActionType, 'labelArr' => $classificationLabelText, 'clubterminology' => $clubterminology);
        if ($actionType == 'remove') {
            $reqArray['dragClassTitle'] = trim($assignmentData['dragMenuTitle']);
            $reqArray['dragClfnName'] = '';
            $isAllowedAsgmnt = '';
            if ($this->clubType == 'sub_federation') {
                $clfnObj = $this->em->getRepository('CommonUtilityBundle:FgClubClassification')->find($dragClassification);
                $clfnTitle = $clfnObj->getTitle();
                $reqArray['dragClfnName'] = $clfnTitle;
                $isAllowedAsgmnt = $clfnObj->getSublevelAssign();
            }
            $reqArray['isAllowedAsgmnt'] = $isAllowedAsgmnt ? $isAllowedAsgmnt : 'assign';
            // print_r($reqArray);die;
            return $this->render('ClubadminClubBundle:Assignment:removeassignments.html.twig', $reqArray);
        } else {
            return $this->render('ClubadminClubBundle:Assignment:assignclubs.html.twig', $reqArray);
        }
    }

    /**
     * function to validate multi club assignment
     */
    public function validatorAction(Request $request)
    {
        $clubIds = $request->get('clubIds');
        $clfnId = $request->get('clfnId');
        $classId = $request->get('classId');

        $assignments = $this->em->getRepository('CommonUtilityBundle:FgClubClassAssignment')->getClubAssignmentsOfClfn($clubIds, $clfnId, $classId);

        return new JsonResponse($assignments);
    }

    /**
     * Function to get all the filtered club ids for assignment
     * Or selected or all clubs of the sidebar::active category
     *
     * @return Json
     */
    public function getAllClubIdsHandlerAction(Request $request)
    {
        //Get the POST data
        $selectedIds = $request->get('selItemIds', 'all');
        $searchval = $request->get('searchVal', '');
        $formdataValues = $request->get('filterData', '');
        $contactIds = $this->getClubIdsAction($selectedIds, $searchval, $formdataValues);

        return new JsonResponse(array('itemIds' => $contactIds));
    }

    /**
     * Get the club Id's either selected or filtered list
     * @param array  $selectedIds    Selected ids
     * @param String $searchval      Search value
     * @param array  $formdataValues Form values
     *
     * @return array  contactIds
     */
    public function getClubIdsAction($selectedIds, $searchval, $formdataValues)
    {
        if ($selectedIds == 'all') {
            //PREPARE CLUB_IDS FROM FILTER
            $filter = json_decode(($formdataValues), true);
            $club = $this->get('club');
            $aColumns = array();
            array_push($aColumns, 'fc.id', 'clubname');
            $clublistClass = new Clublist($this->container, $club);
            $clublistClass->setFrom();
            $clublistClass->setCondition();

            if (!(empty($searchval))) {
                $sSearch = $searchval;
                $columns = FgUtility::getSearchFields();
                $sWhere = "(";
                foreach ($columns as $column) {
                    $sSearchVal = FgUtility::getSecuredDataString($sSearch, $this->conn);
                    $sWhere .= $column . " LIKE '%" . $sSearchVal . "%' OR ";
                }
                $sWhere = substr_replace($sWhere, "", -3);
                $sWhere .= ')';
                $clublistClass->addCondition($sWhere);
            }

            if (!(empty($filter))) {
                $jsonArray = $filter;
                $filterData = array_shift($filter);
                $filterObj = new Clubfilter($this->container, $clublistClass, $filterData, $club);
                if (!(empty($searchval))) {
                    $sWhere .= " AND (" . $filterObj->generateClubFilter() . ")";
                } else {
                    $sWhere .= " (" . $filterObj->generateClubFilter() . ")";
                }
                $clublistClass->addCondition($sWhere);
            }

            $clublistClass->setColumns($aColumns);
            //call query for collect the data
            $listquery = $clublistClass->getResult();
            file_put_contents("query.txt", $listquery . "\n");
            $clublistDatas = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($listquery);
            $clubIds = $clublistDatas;
        } else {
            $clubIds = explode(',', $selectedIds);
        }

        return $clubIds;
    }
}

//end class
