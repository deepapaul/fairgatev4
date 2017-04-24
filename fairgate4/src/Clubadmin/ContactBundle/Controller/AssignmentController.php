<?php
/**
 * AssignmentController
 *
 * This controller was created for handling Assignment functionalities
 *
 * @package    ClubadminContactBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
namespace Clubadmin\ContactBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Clubadmin\Util\Contactlist;
use Clubadmin\ContactBundle\Util\ContactlistData;
use Clubadmin\ContactBundle\Util\ContactDetailsAccess;
use Common\UtilityBundle\Util\FgUtility;
use Clubadmin\ContactBundle\Util\NextpreviousContact;
use Common\UtilityBundle\Repository\Pdo\ContactPdo;
use Clubadmin\ContactBundle\Util\ContactDetailsSave;
use Common\UtilityBundle\Util\FgFedMemberships;
use Symfony\Component\HttpFoundation\Request;
use Common\UtilityBundle\Repository\Pdo\membershipPdo;
use Common\UtilityBundle\Util\FgPermissions;

/**
 * This controller was created for handling Assignment functionalities
 *
 * @author     pitsolutions.ch
 */
class AssignmentController extends FgController
{

    /**
     * Function is used to get all assignment values
     * @param Int $offset  Offset value
     * @param Int $contact Contact Id
     *
     * @return template
     */
    public function indexAction($offset, $contact)
    {
        $accessObj = new ContactDetailsAccess($contact, $this->container);
        if ($accessObj->accessType == 'NO_ACCESS' || !in_array('assignment', $accessObj->tabArray)) {
            $this->fgpermission->checkClubAccess('', 'contactassignments');
        }
        $contactMenuModule = $accessObj->menuType;
        if ($contactMenuModule == 'archive') {
            $this->get('club')->set('moduleMenu', 'contactarchive');
        } elseif ($contactMenuModule == 'formerfederationmember') {
            $this->get('club')->set('moduleMenu', 'formerfederationmember');
        }
        $this->session->set('contactType', $accessObj->contactviewType);
        // Generating next and previous data for the next-previous functionality in the overview page
        $nextprevious = new NextpreviousContact($this->container);
        $nextPreviousResultset = $nextprevious->nextPreviousContactData($this->contactId, $contact, $offset, 'contact_assignments', 'offset', 'contact', $flag = 0);

        $contactDetails = $this->getContactDetails($contact);
        $tabsData = FgUtility::getTabsArrayDetails($this->container, $accessObj->tabArray, $offset, $contact, $contactDetails, "assignment", "contact");

        $dataArray = array('nextPreviousResultset' => $nextPreviousResultset, 'offset' => $offset, 'tabs' => $tabsData);
        $return = array_merge($dataArray, $contactDetails);
        return $this->render('ClubadminContactBundle:Assignment:index.html.twig', $return);
    }

    /**
     * Function is used to get json array of all assignment values
     *
     * @return template
     */
    public function listAllAssignmentsAction()
    {
        $clubIdArray = array('clubId' => $this->clubId, 'federationId' => $this->federationId, 'subFederationId' => $this->subFederationId, 'clubType' => $this->clubType, 'defaultClubLang' => $this->clubDefaultLang);
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $contactId = $request->get('contactId');
        $federationMember = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->checkFederationMember($contactId);
        $fedMember = (!$federationMember['isFedCategory'] || !count($federationMember)) ? FALSE : TRUE;
        //SECURITY FIX
        $clubMember = true;
        if (!$federationMember['isFedCategory'] || !count($federationMember)) {
            $clubContact = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->checkClubContact($contactId, $this->clubId);
            if (count($clubContact) <= 0) {
                $clubMember = false;
            }
        }
        $assignmentDetails = $this->getAssignmentsDetails($clubIdArray, $contactId);
        $dataArray = array('filterPath' => $this->generateUrl('filter_role_settings', array('cat_id' => '#dummy#')), 'clubType' => $this->clubType, 'is_federation_member' => $fedMember, 'is_club_member' => $clubMember);
        $resultArray = array_merge($assignmentDetails, $dataArray);

        return new JsonResponse($resultArray);
    }

    /**
     * Function is used to get json array of all assignment values
     *
     * @return template
     */
    public function allAssignmentsAction()
    {
        $clubIdArray = array('clubId' => $this->clubId, 'federationId' => $this->federationId, 'subFederationId' => $this->subFederationId, 'clubType' => $this->clubType, 'defaultClubLang' => $this->clubDefaultLang);
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $contactId = $request->get('contactId');
        //SECURITY FIX
        $getAllAssignedCategories = $this->em->getRepository('CommonUtilityBundle:FgRmCategory')->getAllAssignedCategories($clubIdArray, $this->conn, $contactId);

        return new JsonResponse($getAllAssignedCategories);
    }

    /**
     * Function is used to get all drop down values for a perticular type
     *
     * @return template
     */
    public function getDropdownValuesAction()
    {
        $clubIdArray = array('clubId' => $this->clubId, 'federationId' => $this->federationId, 'subFederationId' => $this->subFederationId, 'clubType' => $this->clubType);
        $getAllCategoryRoleFunction = $this->em->getRepository('CommonUtilityBundle:FgRmCategory')->getAllCategoryRoleFunctionAssignment($this->conn, $clubIdArray, $this->clubDefaultLang);

        return new JsonResponse(array('resultArray' => $getAllCategoryRoleFunction, 'clubId' => $this->clubId, 'federationId' => $this->federationId,
            'subFederationId' => $this->subFederationId, 'clubType' => $this->clubType));
    }

    /**
     * Executes updateContactAssignments action
     *
     * Function to add or delete contact assignments
     *
     * @return json_object
     */
    public function updateContactAssignmentsAction(Request $request)
    {
        $contactId = $request->get('contact_id', '0');
        $fromPage = $request->get('fromPage', '');
        $actionType = $request->get('actionType', '');
        $selCount = $request->get('selCount', '');
        $totalCount = $request->get('totalCount', '');
        if ($request->getMethod() == 'POST') {
            $catArr = json_decode($request->request->get('catArr'), true);
            if (count($catArr) > 0) {
                $contactIdArr = ($contactId == '') ? array() : explode(',', $contactId);
                $translationsArray = array('workgroup' => $this->get('translator')->trans('WORKGROUPS'));
                $resultArray = $this->em->getRepository('CommonUtilityBundle:FgRmRoleContact')->updateContactAssignments($catArr, $this->clubId, $contactIdArr, $this->contactId, $this->get('club'), $this->get('fairgate_terminology_service'), $this->container, $translationsArray);
                $errorType = $resultArray['errorType'];
                $errorArray = $resultArray['errorArray'];
            }
            $finalSidebarArray = array();
            $dispalyAddCount = 0;
            $dispalyRemoveCount = 0;
            $finalSidebarArray = $this->getSidebarJsonArray($resultArray, $selCount, $resultArray['existingAsgmnts']);
            if ($actionType != 'remove') {
                if (isset($finalSidebarArray['addCount'])) {
                    $dispalyAddCount = $finalSidebarArray['addCount'];
                }
                unset($finalSidebarArray['addCount']);
            } else {
                if (isset($finalSidebarArray['removeCount'])) {
                    $dispalyRemoveCount = $finalSidebarArray['removeCount'];
                }
                unset($finalSidebarArray['removeCount']);
            }
            if ($fromPage == 'contactlist') {
                $flashMsg = $this->getFlashMessageForAssignment($actionType);

                return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans($flashMsg, array('%selcount%' => ($actionType != 'remove') ? $dispalyAddCount : $dispalyRemoveCount, '%totalcount%' => $totalCount)), 'sidebarCountArray' => $finalSidebarArray, 'assignedCount' => $dispalyAddCount, 'noparentload' => 1));
            } else {
                if ($errorType) {
                    $errorType = ($errorType == 'NO_MULTI_ASSIGNMENT_POSSIBLE') ? $this->get('translator')->trans('CREATE_CONTACT_NO_MULTI_ASSIGNMENT_POSSIBLE') : $errorType;

                    return new JsonResponse(array('flash' => $this->get('translator')->trans($errorType), 'errorArray' => $errorArray));
                } else {
                    return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('ASSIGNMENT_SAVED')));
                }
            }
        }
    }

    /**
     * For getting final array
     * @param type $resultArray json result array
     * @param type $selCount
     * @param type $existingAsgmnts
     *
     * @return int count
     */
    private function getSidebarJsonArray($resultArray, $selCount, $existingAsgmnts)
    {
        $finalSidebarArray = array();
        $i = 0;
        $addCount = 0;
        $removeCount = 0;
        $newCatIdArray = array();
        $deleteCatIdArray = array();
        foreach ($resultArray['sidebarArray'] as $key => $val) {
            $alreadyExistFlag = 0;
            if (isset($val['new']['function_id'])) {
                if (isset($existingAsgmnts[$key][$val['new']['category_id']][$val['new']['subcategory_id']][$val['new']['function_id']])) {
                    $alreadyExistFlag = 1;
                }
            } else {
                if (isset($existingAsgmnts[$key][$val['new']['category_id']][$val['new']['subcategory_id']])) {
                    $alreadyExistFlag = 1;
                }
            }
            if ($alreadyExistFlag != 1) {
                if (isset($val['new'])) {
                    $addCount++;
                    if (!in_array($val['new']['category_id'], $newCatIdArray)) {
                        $newCatIdArray[] = $val['new']['category_id'];
                        $finalSidebarArray[$i] = $this->getSidebarStructure($val['new'], $selCount);
                        $i++;
                    }
                }
            }
            if (isset($val['delete'])) {
                $removeCount++;
                if (!in_array($val['delete']['category_id'], $deleteCatIdArray)) {
                    $deleteCatIdArray[] = $val['delete']['category_id'];
                    $finalSidebarArray[$i] = $this->getSidebarStructure($val['delete'], $selCount);
                    $i++;
                }
            }
        }
        foreach ($finalSidebarArray as $key => $val) {
            if ($val['action'] == 'remove') {
                $finalSidebarArray[$key]['sidebarCount'] = $removeCount;
            } elseif ($val['action'] == 'add') {
                $finalSidebarArray[$key]['sidebarCount'] = $addCount;
            }
        }
        $finalSidebarArray['addCount'] = $addCount;
        $finalSidebarArray['removeCount'] = $removeCount;

        return $finalSidebarArray;
    }

    /**
     * Array structure for side bar common array
     * @param Int $val      Array values
     * @param Int $selCount Total count
     *
     * @return template
     */
    private function getSidebarStructure($val, $selCount)
    {
        $returnArray = array();
        $returnArray['categoryId'] = $val['category_id'];
        $returnArray['subCatId'] = $val['subcategory_id'];
        if (isset($val['cat_type']) && $val['cat_type'] == 'federation') {
            $returnArray['dataType'] = 'FROLES-' . $val['cat_clubId'];
        } elseif (isset($val['cat_type']) && $val['cat_type'] == 'team') {
            $returnArray['dataType'] = 'TEAM';
        } elseif (isset($val['cat_type']) && $val['cat_type'] == 'workgroup') {
            $returnArray['dataType'] = 'WORKGROUP';
        } elseif (!isset($val['cat_type'])) {
            $returnArray['dataType'] = 'ROLES-' . $this->clubId;
        }
        $returnArray['action'] = $val['change_type'];
        $returnArray['sidebarCount'] = $selCount;

        return $returnArray;
    }

    /**
     * Template for assigning contacts
     * @param type $contactId
     * @param type $contactType
     *
     * @return array contact data
     */
    private function contactDetails($contactId, $contactType = 'contact')
    {
        $club = $this->get('club');
        $contactlistClass = new Contactlist($this->container, '', $club);
        $contactlistClass->setColumns(array('contactName', 'contactid', 'clubId', 'contactname', 'is_company', 'has_main_contact'));
        $contactlistClass->setFrom('*');
        $contactlistClass->setCondition();
        $sWhere = " fg_cm_contact.id=$contactId";
        $contactlistClass->addCondition($sWhere);
        $listquery = $contactlistClass->getResult();
        $fieldsArray = $this->conn->fetchAll($listquery);

        return $fieldsArray[0];
    }

    /**
     * Template for assigning contacts
     *
     * @return template
     */
    public function updateassignmentsAction(Request $request)
    {
        $actionType = $request->get('actionType') ? $request->get('actionType') : 'assign';
        $selActionType = $request->get('selActionType') ? $request->get('selActionType') : '';
        $assignmentData = $request->get('assignmentData');
        $dragCat = isset($assignmentData['dragCategoryId']) ? $assignmentData['dragCategoryId'] : '';
        $dragRole = isset($assignmentData['dragMenuId']) ? $assignmentData['dragMenuId'] : '';
        $dropCat = isset($assignmentData['dropCategoryId']) ? $assignmentData['dropCategoryId'] : '';
        $dropRole = isset($assignmentData['dropMenuId']) ? $assignmentData['dropMenuId'] : '';
        $dropCatFnType = isset($assignmentData['dropCategoryFnType']) ? $assignmentData['dropCategoryFnType'] : '';
        $selectedFun = isset($assignmentData['dropFunctionId']) ? $assignmentData['dropFunctionId'] : '';
        $dragCatType = isset($assignmentData['dragCatType']) ? $assignmentData['dragCatType'] : 'ROLE';
        $dropCatType = isset($assignmentData['dropCatType']) ? $assignmentData['dropCatType'] : 'ROLE';
        $terminologyService = $this->get('fairgate_terminology_service');
        if ($dragCatType == 'TEAM') {
            $dragCat = $this->clubTeamId;
        }
        $categoryLabelText = array('role' => $this->get('translator')->trans('ROLE'), 'team' => ucfirst($terminologyService->getTerminology('Team', $this->container->getParameter('singular'))), 'workgroup' => $this->get('translator')->trans('WORKGROUP'));
        $return = array('actionType' => $actionType, 'dragCat' => $dragCat, 'dragCatType' => $dragCatType, 'dragRole' => $dragRole, 'dropCat' => $dropCat, 'dropRole' => $dropRole, 'dropCatType' => $dropCatType, 'dropCatFnType' => $dropCatFnType, 'clubId' => $this->clubId, 'clubType' => $this->clubType, 'clubTeamId' => $this->clubTeamId, 'clubWorkgroupId' => $this->clubWorkgroupId, 'clubExecBoardId' => $this->clubExecutiveBoardId, 'selActionType' => $selActionType, 'selectedFun' => $selectedFun, 'labelArr' => json_encode($categoryLabelText));
        if ($actionType == 'remove') {
            $return['dragMenuTitle'] = trim($assignmentData['dragMenuTitle']);
            $isRequiredAsgmnt = 0;
            $isAllowedAsgmnt = 1;
            $dragCatName = '';
            if (in_array($this->clubType, array('sub_federation', 'federation_club', 'sub_federation_club')) && !in_array($dragCatType, array('TEAM', 'WORKGROUP'))) {
                $catObj = $this->em->getRepository('CommonUtilityBundle:FgRmCategory')->find($dragCat);
                $catClubId = $catObj->getClub()->getId();
                $dragCatName = $catObj->getTitle();
                if (($this->clubType == 'sub_federation') && ($catClubId != $this->clubId)) {
                    $isRequiredAsgmnt = $catObj->getIsRequiredFedmemberSubfed();
                    $isAllowedAsgmnt = $catObj->getIsAllowedFedmemberSubfed();
                } elseif ((($this->clubType == 'federation_club') || ($this->clubType == 'sub_federation_club')) && ($catClubId != $this->clubId)) {
                    $isRequiredAsgmnt = $catObj->getIsRequiredFedmemberClub();
                    $isAllowedAsgmnt = $catObj->getIsAllowedFedmemberClub();
                }
            }
            $return['isRequiredAsgmnt'] = $isRequiredAsgmnt ? $isRequiredAsgmnt : 0;
            $return['isAllowedAsgmnt'] = $isAllowedAsgmnt ? $isAllowedAsgmnt : 0;
            $return['dragCatName'] = $dragCatName;

            return $this->render('ClubadminContactBundle:Assignment:removeassignments.html.twig', $return);
        } else {
            return $this->render('ClubadminContactBundle:Assignment:assigncontacts.html.twig', $return);
        }
    }

    /**
     * Action to get assignment categories, roles and functions
     *
     * @return object Json object
     */
    public function getAssignmentsAction()
    {
        $terminologyService = $this->get('fairgate_terminology_service');
        $executiveBoardTitle = $terminologyService->getTerminology('Executive Board', $this->container->getParameter('singular'));
        $objMembershipPdo = new membershipPdo($this->container);
        $assignments = $objMembershipPdo->getAllCategoryRoleFunction($this->get('club'), '', false, $executiveBoardTitle);

        return new JsonResponse($assignments);
    }

    /**
     * Function to get assigned function id of a contact
     *
     * @return object Json object
     */
    public function getassignedfunctionAction(Request $request)
    {
        $contactId = $request->get('contactId');
        $catId = $request->get('catId');
        $roleId = $request->get('roleId');
        $dragCatType = $request->get('dragCatType');
        $assignments = $this->em->getRepository('CommonUtilityBundle:FgRmRoleContact')->getContactAssignmentsOfCat($contactId, $catId, '', $roleId, false, false, false, $this->clubId, $this->clubType, $this->federationId, $this->subFederationId, $dragCatType);

        return new JsonResponse($assignments);
    }

    /**
     * Function to check whether contacts are already assigned to selected single assignment category
     *
     * @return object Json object
     */
    public function validateassignmentsAction(Request $request)
    {
        $contactIds = $request->get('contactIds');
        $catId = $request->get('catId');
        $roleId = $request->get('roleId');
        $dragCatType = $request->get('dragCatType');
        $actionType = $request->get('actionType');
        $assignments = $this->em->getRepository('CommonUtilityBundle:FgRmRoleContact')->getContactAssignmentsOfCat($contactIds, $catId, '', $roleId, false, false, false, $this->clubId, $this->clubType, $this->federationId, $this->subFederationId, $dragCatType, $actionType);

        return new JsonResponse($assignments);
    }

    /**
     * Function to remove the assignment of selected or all contact of the sidebar::active category
     *
     * @return Json
     */
    public function getAllAssignmentHandlerAction(Request $request)
    {
        //Get the POST data
        $selectedIds = $request->get('selItemIds', 'all');
        $searchval = $request->get('searchVal', '');
        $formdataValues = $request->get('filterData', '');
        $contactIds = $this->getContactIdsAction($request, $selectedIds, $searchval, $formdataValues);

        return new JsonResponse(array('itemIds' => $contactIds));
    }

    /**
     * Get the contact Id's either selected or filtered list
     * @param array  $selectedIds    Selected ids
     * @param String $searchval      Search value
     * @param array  $formdataValues Form values
     *
     * @return array  contactIds
     */
    public function getContactIdsAction($request, $selectedIds, $searchval, $formdataValues)
    {
        if ($selectedIds == 'all') {
            //Set all request value to its corresponding variables
            $contactlistData = new ContactlistData($this->contactId, $this->container, 'contact');
            $aColumns = array();
            array_push($aColumns, 'contactid', 'contactname', 'membershipType', 'clubId');
            $contactlistData->filterValue = json_decode($formdataValues, true);
            $contactlistData->dataTableColumnData = $request->get('columns', $aColumns);
            $contactlistData->searchval['value'] = $request->get('search', $searchval);
            $contactlistData->tableFieldValues = $request->get('columns', $aColumns);
            $contactData = $contactlistData->getContactData();
            $contactIds = $contactData['data'];
        } else {
            $contactIds = explode(',', $selectedIds);
        }

        return $contactIds;
    }

    /**
     * Function to return template for Adding/Removing Executive Board Members.
     *
     * @return template Template for Adding/Removing Executive Board Members.
     */
    public function editExecutiveBoardMembersAction()
    {
        $workgroupId = $this->get('club')->get('club_workgroup_id');
        $execBoardId = $this->get('club')->get('club_executiveboard_id');
        // Get Executive Board Function Details of Club.
        $execBoardFuncDetails = $this->em->getRepository('CommonUtilityBundle:FgRmFunction')->getExecBoardFunctionDetailsOfClub($this->clubId, $this->clubDefaultLang, $this->get('club')->get('clubHeirarchy'));
        $fedIcon = FgUtility::getClubLogo($this->federationId, $this->em);
        $return = array('workgroupId' => $workgroupId, 'execBoardId' => $execBoardId, 'execBoardFuncDetails' => $execBoardFuncDetails, 'federationId' => $this->federationId, 'settings' => true, 'fedIcon' => $fedIcon);


        return $this->render('ClubadminContactBundle:Assignment:editexecutiveboardmembers.html.twig', $return);
    }

    /**
     * Action for Adding/Removing Executive Board Members.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function updateExecutiveBoardMembersAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $dataArr = json_decode($request->get('dataArr'), true);
            $asigmentArray = $this->getExecutiveBoardAsigmentData($dataArr, $this->clubWorkgroupId, $this->clubExecutiveBoardId);
            $asigmntArray = $asigmentArray['asigmntArray'];
            $errorType = '';
            if (count($asigmntArray) > 0) {
                $translationsArray = array('workgroup' => $this->get('translator')->trans('WORKGROUPS'));
                $resultArray = $this->em->getRepository('CommonUtilityBundle:FgRmRoleContact')->updateContactAssignments($asigmntArray, $this->clubId, $asigmentArray['contactIdArr'], $this->contactId, $this->get('club'), $this->get('fairgate_terminology_service'), $this->container, $translationsArray);
                $errorType = $resultArray['errorType'];
                $errorArray = $resultArray['errorArray'];
            }
            if ($errorType) {
                $errorType = ($errorType == 'NO_MULTI_ASSIGNMENT_POSSIBLE') ? 'Multiple Assignment Not Possible' : $errorType;
                $jsonResponse = array('flash' => $this->get('translator')->trans($errorType), 'errorArray' => $errorArray);
            } else {
                $terminologyService = $this->get('fairgate_terminology_service');
                $executiveBoardTitle = $terminologyService->getTerminology('Executive Board', $this->container->getParameter('singular'));
                $jsonResponse = array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('EXECUTIVE_BOARD_ASSIGNMENT_SAVED', array('%a%' => $executiveBoardTitle)));
            }

            return new JsonResponse($jsonResponse);
        }
    }

    /**
     * Function for getting missing assignments
     *
     * @param String $type Type
     *
     * @return template
     */
    public function sidebarMissingAssignmentsAction()
    {
        $allMissingAssignRoles = $this->em->getRepository('CommonUtilityBundle:FgRmCategory')->getMissingAssignmentsDetails($this->clubId, $this->clubType, $this->federationId, $this->subFederationId);

        return new JsonResponse($allMissingAssignRoles);
    }

    /**
     * Function to get details of a contact
     * @param int $contactId Contact Id
     *
     * @return array $returnArray Result array of contact details.
     */
    private function getContactDetails($contactId)
    {
        $contactName = $this->contactDetails($contactId);
        $federationId = $this->clubType == "federation" ? $this->clubId : $this->federationId;
        $subFederationId = $this->clubType == "sub_federation" ? $this->clubId : $this->subFederationId;
        $data1Array = array('contactId' => $contactId, 'contactName' => $contactName['contactName'], 'displayedUserName' => $contactName['contactname'], 'loggedContactId' => $this->contactId, 'federationId' => $federationId, 'subFederationId' => $subFederationId, 'fedLogoPath' => FgUtility::getClubLogo($federationId, $this->em), 'subfedLogoPath' => FgUtility::getClubLogo($subFederationId, $this->em));
        // Get Connection, Assignments, Notes count of a Contact.
        $contCountDetails = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getContactCountDetails($this->clubId, $contactId, $contactName['is_company'], $this->clubType, true, true, true, false, false, false, false, $federationId, $subFederationId);
        $groupUserDetails = $this->em->getRepository('CommonUtilityBundle:SfGuardGroup')->getGroupDetails($this->conn, $this->clubId, $contactId);
        $data2Array = array(
            'documentsCount' => $this->em->getRepository('CommonUtilityBundle:FgDmAssigment')->getCountOfAssignedDocuments('CONTACT', $this->clubId, $contactId),
            'hasUserRights' => ((count($groupUserDetails) > 0) ? 1 : 0),
            'missingReqAssgment' => $this->em->getRepository('CommonUtilityBundle:FgRmCategory')->missingReqFedAssign($contactId, $this->clubId, $this->federationId, $this->subFederationId, $this->clubType, $this->clubDefaultLang, $this->conn),
            'missingReqAssgnments' => json_encode($this->em->getRepository('CommonUtilityBundle:FgRmCategory')->missingReqFedAssign($contactId, $this->clubId, $this->federationId, $this->subFederationId, $this->clubType, $this->clubDefaultLang, $this->conn, 'assignment')),
            'isReadOnlyContact' => $this->isReadOnlyContact()
        );
        $returnArray = array_merge($data1Array, $contCountDetails, $data2Array);

        return $returnArray;
    }

    /**
     * Method to get readonly status of current contact
     *
     * @return boolean $isReadOnlyContact
     */
    private function isReadOnlyContact()
    {
        $allowedModules = $this->container->get('contact')->get('allowedModules');
        if (in_array('readonly_contact', $allowedModules) && !in_array('contact', $allowedModules)) {
            $isReadOnlyContact = 1;
        } else {
            $isReadOnlyContact = 0;
        }

        return $isReadOnlyContact;
    }

    /**
     * Function to get assignment details of contact
     * @param array $clubIdArray Club details
     * @param int   $contactId   Contact id
     *
     * @return array $resultArray Result array of assignments.
     */
    private function getAssignmentsDetails($clubIdArray, $contactId)
    {
        $getAllAssignedCategories = $this->em->getRepository('CommonUtilityBundle:FgRmCategory')->getAllAssignedCategories($clubIdArray, $this->conn, $contactId);
        $resultArray = array();
        foreach ($getAllAssignedCategories as $key => $val) {
            if ($val['is_workgroup'] == 1) {
                $resultArray['Workgroup'][] = $val;
            } elseif ($val['is_team'] == 1) {
                $resultArray['Team'][] = $val;
            } else if ($val['contact_assign'] == 'manual') {
                switch ($val['clubType']) {
                    case 'federation':
                        if ($val['is_fed_category'] == 1) {
                            $resultArray['Federation'][] = $val;
                        } else {
                            if ($this->clubId == $val['clubId']) {
                                $resultArray['NormalRoles'][] = $val;
                            }
                        }
                        break;
                    case 'sub_federation':
                        if ($val['is_fed_category'] == 1) {
                            $resultArray['Subfederation'][] = $val;
                        } else {
                            if ($this->clubId == $val['clubId']) {
                                $resultArray['NormalRoles'][] = $val;
                            }
                        }
                        break;
                }
                if (($this->clubId == $val['clubId']) && in_array($val['clubType'], array('sub_federation_club', 'standard_club', 'federation_club'))) {
                    $resultArray['NormalRoles'][] = $val;
                }
            } else {
                $resultArray['filterRole'][] = $val;
            }
        }

        return $resultArray;
    }

    /**
     * Function to generate array for saving executive board assignment.
     * @param array $dataArr Data array
     * @param int   $catId   Category Id
     * @param int   $roleId  Role Id
     *
     * @return array $resultArray Result array of assignment data.
     */
    private function getExecutiveBoardAsigmentData($dataArr, $catId, $roleId)
    {
        $asigmntArray = array();
        $contactIdArr = array();
        foreach ($dataArr as $functionId => $contactsArr) {
            if (isset($contactsArr['add_contacts'])) {
                $addContacts = explode(',', $contactsArr['add_contacts']);
                foreach ($addContacts as $addContact) {
                    if ($addContact != 'undefined') {
                        $asigmntArray[$addContact][$catId]['role'][$roleId]['function'][$functionId]['is_new'] = 1;
                        $contactIdArr[] = $addContact;
                    }
                }
            }
            if (isset($contactsArr['delete_contacts'])) {
                $delContacts = explode(',', $contactsArr['delete_contacts']);
                foreach ($delContacts as $delContact) {
                    if ($delContact != 'undefined') {
                        $asigmntArray[$delContact][$catId]['role'][$roleId]['function'][$functionId]['is_deleted'] = 1;
                        $contactIdArr[] = $delContact;
                    }
                }
            }
        }
        $resultArray = array('asigmntArray' => $asigmntArray, 'contactIdArr' => $contactIdArr);

        return $resultArray;
    }

    /**
     * Function to get flash message for updating assignments.
     * @param string $actionType Action type
     * @return string $flashMsg  Flash message
     */
    private function getFlashMessageForAssignment($actionType)
    {
        $flashMsg = '';
        switch ($actionType) {
            case 'assign':
                $flashMsg = 'selcount_OUT_OF_totalcount_CONTACTS_ASSIGNED_SUCCESSFULLY';
                break;
            case 'move':
                $flashMsg = 'selcount_OUT_OF_totalcount_CONTACTS_MOVED_SUCCESSFULLY';
                break;
            case 'remove':
                $flashMsg = 'selcount_OUT_OF_totalcount_CONTACTS_REMOVED_SUCCESSFULLY';
                break;
        }

        return $flashMsg;
    }

    /**
     * Template for assigning contacts to membership[
     *
     * @return template
     */
    public function dragDropMembershipAssignmentsAction(Request $request)
    {

        /* ----- Contact count checking ---------------    */
        $permissionObj = new FgPermissions($this->container);
        if (!$permissionObj->checkContactCount()) {
            return $this->render('CommonUtilityBundle:Permissionpopup:contactcreationwarningpopup.html.twig');
        } else {
            $terminologyService = $this->get('fairgate_terminology_service');
            $actionType = $request->get('actionType') ? $request->get('actionType') : 'assign_membership';
            $selActionType = $request->get('selActionType') ? $request->get('selActionType') : '';
            $assignmentData = $request->get('assignmentData');
            $dragCat = isset($assignmentData['dragMenuId']) ? $assignmentData['dragMenuId'] : '';
            $dragCatType = isset($assignmentData['dragCatType']) ? $assignmentData['dragCatType'] : 'membership';
            $dropCat = isset($assignmentData['dropMenuId']) ? $assignmentData['dropMenuId'] : '';
            $dropCatType = isset($assignmentData['dropCatType']) ? $assignmentData['dropCatType'] : 'membership';
            $clubService = $this->container->get('club');
            $fedMembershipMandatory = $clubService->get('fedMembershipMandatory');
            $fedmemTrans = $terminologyService->getTerminology('Fed membership', $this->container->getParameter('singular'));
            $return = array('actionType' => $actionType, 'dropCat' => $dropCat, 'dragCat' => $dragCat,
                'dragCatType' => $dragCatType,
                'dropCatType' => $dropCatType, 'clubId' => $this->clubId, 'clubTeamId' => $this->clubTeamId, 'fedmemTrans' => $fedmemTrans,
                'selActionType' => $selActionType, 'type' => $this->clubType, 'clubMembershipAvailable' => $this->get('club')->get('clubMembershipAvailable'), 'fedMembershipMandatory' => $fedMembershipMandatory);

            return $this->render('ClubadminContactBundle:Assignment:assigncontacttomembership.html.twig', $return);
        }
    }

    /**
     * save membership in contacts-dragdrop
     * @return JsonResponse
     */
    public function saveMembershipAssignmentsAction(Request $request)
    {
        $type = $request->get('type');
        $contactIds = $request->get('contact_id') ? $request->get('contact_id') : '';
        $contact = explode(',', $contactIds);
        $membrshipArray = array();
        $membrshipAdd = array();
        $mergeData = $request->get('mergeData', array());
        $mergableContacts = array_keys($mergeData);
        if ($contact[0] != '') {
            foreach ($contact as $key => $contactId) {
                if ($type == 'fed_membership') {
                    $fedMembershipId = $request->get('membership');
                    $merge = $request->get('merge', false);
                    if ($merge == 'multiple' && in_array($contactId, $mergableContacts)) {
                        if ($mergeData[$contactId]['applymer'] != 'fed_mem') {
                            $contactUpdateStr = "UPDATE fg_cm_contact SET merge_to_contact_id=" . $mergeData[$contactId]['applymer'] . ",allow_merging=1, is_deleted=0 WHERE id ='" . $contactId . "'";
                            $this->conn->executeQuery($contactUpdateStr);
                            $contactObj = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->find($mergeData[$contactId]['applymer']);
                            $fgFedMembershipObj = new FgFedMemberships($this->container);
                            $fgFedMembershipObj->processFedMembership($contactId, $fedMembershipId);
                            $fedMem = $contactObj->getFedMembershipCat()->getId();
                            $membrshipAdd[$fedMem] = $membrshipAdd[$fedMem] + 1;
                            continue;
                        }
                    }
                } else if ($type == 'membership') {
                    $clubMembershipId = $request->get('membership');
                }
                $editData = $this->getContactData($contactId, 'contact');
                $memberships = $this->getMembershipArray($contactId);
                $federationMemberships = array_keys($memberships['fed']);
                $clubMemberships = array_keys($memberships['club']);
                $membershipId = $request->get('membership');

                if ($type != 'fed_membership') {
                    if (isset($membrshipArray[$editData[0]['clubMembershipId']])) {
                        $membrshipArray[$editData[0]['clubMembershipId']] +=1;
                    } else {
                        $membrshipArray[$editData[0]['clubMembershipId']] = 1;
                    }
                } else {
                    $membrshipAdd[$membershipId] = $membrshipArray[$membershipId] + 1;
                    if (isset($membrshipArray[$editData[0]['fed_membership_cat_id']])) {
                        $membrshipArray[$editData[0]['fed_membership_cat_id']] +=1;
                    } else {
                        $membrshipArray[$editData[0]['fed_membership_cat_id']] = 1;
                    }
                }


                $fieldType = ($editData[0]['is_company'] == 1) ? 'Company' : 'Single person';
                $clubIdArray = $this->getClubArray();

                $fieldDetails1 = $this->em->getRepository('CommonUtilityBundle:FgCmAttributeset')->getAllClubContactFields($clubIdArray, $this->conn, 0, $fieldType);
                $fieldDetails = $this->em->getRepository('CommonUtilityBundle:FgCmAttributeset')->fieldItrator($fieldDetails1);
                $fieldDetailArray = array('fieldType' => $fieldType, 'memberships' => $memberships, 'clubIdArray' => $clubIdArray, 'fedMemberships' => $federationMemberships, 'clubMemberships' => $clubMemberships, 'selectedFedMembership' => $fedMembershipId, 'selectedClubMembership' => $clubMembershipId, 'contactId' => $contactId);
                $fieldDetailsFinal = array_merge($fieldDetails, $fieldDetailArray);

                $formArray = array();
                if ($type == 'fed_membership') {
                    $formArray = array("system" => array("contactType" => $fieldType, "fedMembership" => $fedMembershipId));
                } else if ($type == 'membership') {
                    $formArray = array("system" => array("contactType" => $fieldType, "membership" => $clubMembershipId));
                }

                $contact = new ContactDetailsSave($this->container, $fieldDetailsFinal, $editData, $contactId);
                $contact->saveContact($formArray, array());
            }
        }
        $selCount = $request->get('selCount');
        $totalCount = $request->get('totalCount');
        $dragCatType = $request->get('dragCatType');
        $dragCat = $request->get('dragCat');
        $flashMsg = 'selcount_OUT_OF_totalcount_CONTACTS_ASSIGNED_SUCCESSFULLY';
        $count = 0;
        $countArr = array();
        foreach ($membrshipArray as $key => $value) {
            $countArr[$count]['id'] = $key;
            $countArr[$count]['count'] = $value;
            $count++;
        }


        return new JsonResponse(array('status' => 'SUCCESS', 'noparentload' => true, 'flash' => $this->get('translator')->trans($flashMsg, array('%selcount%' => $selCount, '%totalcount%' => $totalCount)),
            'dragCatType' => $dragCatType, 'membrshipArray' => $countArr, 'membrshipAdd' => $membrshipAdd, 'dragCat' => $dragCat, 'selcount' => $selCount, 'membership' => $membershipId, 'type' => $type));
    }

    /**
     * Function to get membership array
     *
     * @return type
     */
    private function getMembershipArray($contactId)
    {
        //to manage the membership category dropdown based on federation/club/sub-fed
        $objMembershipPdo = new membershipPdo($this->container);
        $membersipFields = $objMembershipPdo->getMemberships($this->clubType, $this->clubId, $this->subFederationId, $this->federationId, $contactId);
        $this->fedMemberships = array();
        $this->fedMembers = '';
        $club = $this->get('club');
        $clubDefaultLang = $club->get('default_lang');
        foreach ($membersipFields as $key => $memberCat) {
            $title = $memberCat['allLanguages'][$clubDefaultLang]['titleLang'] != '' ? $memberCat['allLanguages'][$clubDefaultLang]['titleLang'] : $memberCat['membershipName'];
            if ($this->federationId == $memberCat['clubId']) {
                $this->fedMemberships[] = $key;
                $this->fedMembers .= ':' . $key;
                $memberships['fed'][$key] = $title;
            } else {
                $memberships['club'][$key] = $title;
            }
        }

        return $memberships;
    }

    /**
     * Function to get contact data
     *
     * @param int    $contactId contact id
     * @param string $module    contact module

     * @return type array
     */
    private function getContactData($contactId, $module)
    {
        $pdo = new ContactPdo($this->container);
        $editData = $pdo->getContactDetailsForMembershipDetails($type, $contactId);
        if ($editData['0']['is_deleted']) {
            $this->get('club')->set('moduleMenu', 'contactarchive');
        }
        $this->setContactModuleMenu($contactId, $module);
        $editData[0]['contactClubId'] = $editData[0]['contactclubid'];
        $editData[0]['is_stealth_mode'] = $editData[0]['stealthFlag'];

        return $editData;
    }

    /**
     * set ContactModuleMenu and prevent access
     *
     * @param type $contact
     *
     * @return \Clubadmin\ContactBundle\Util\ContactDetailsAccess
     * @throws type
     */
    private function setContactModuleMenu($contact, $type)
    {
        $accessObj = new ContactDetailsAccess($contact, $this->container, $type);
        if ($accessObj->accessType == 'NO_ACCESS' || !in_array('data', $accessObj->tabArray)) {
            $this->fgpermission->checkClubAccess('', 'contactmodulemenu');
        }
        $contactType = ($accessObj->module == 'sponsor') ? 'sponsor' : $accessObj->contactviewType;
        $contactMenuModule = $accessObj->menuType;

        if ($contactMenuModule == 'archive' && $accessObj->module == 'contact') {
            $this->get('club')->set('moduleMenu', 'contactarchive');
        } else if ($contactMenuModule == 'archive' && $accessObj->module == 'sponsor') {
            $this->get('club')->set('moduleMenu', 'archivedsponsor');
        } elseif ($contactMenuModule == 'formerfederationmember') {
            $this->get('club')->set('moduleMenu', 'formerfederationmember');
        }
        $this->session->set('contactType', $contactType);

        return $accessObj;
    }

    /**
     * Get club details array
     *
     * @return array club array
     */
    private function getClubArray()
    {
        $container = $this->container->getParameterBag();
        $clubIdArray = array('clubId' => $this->clubId,
            'federationId' => $this->federationId,
            'subFederationId' => $this->subFederationId,
            'clubType' => $this->clubType,
            'correspondanceCategory' => $container->get('system_category_address'),
            'invoiceCategory' => $container->get('system_category_invoice'));
        $clubIdArray['address'] = $this->get('translator')->trans('CONTACT_FIELD_ADDRESS');
        $clubIdArray['sysLang'] = $this->clubDefaultLang;
        $clubIdArray['defSysLang'] = $this->clubDefaultSystemLang;
        $clubIdArray['clubLanguages'] = $this->clubLanguages;

        return $clubIdArray;
    }

    /**
     * validate email of contact while assigning fedmebership at club level
     */
    public function validateEmailFedmembershipAction(Request $request)
    {
        $contacts = $request->get('contacts', '');
        $fedMembershipVal = $request->get('amp;fedmembership', '');
        $selectedIds = explode(',', $contacts);
        $pdo = new ContactPdo($this->container);
        $primaryEmail = $this->container->getParameter('system_field_primaryemail');

        if ($contacts != '') {
            $hasFedmembership = ($fedMembershipVal) ? true : false;
            $emails = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getEmailField($contacts, $primaryEmail);
            $status = '';
            $updateIds = '';
            $duplicateEmailId = '';
            $updateIdsCount = 0;
            $subscriberIds = '';
            $contactErrorArray = array();

            // Checking the count of reactivate contact and the contact has the federation membership
            if ($hasFedmembership && count($selectedIds) > 1) {
                $meargable = array();
                foreach ($selectedIds as $key => $contactid) {
                    $contactObj = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->find($contactid);
                    $contactData = $pdo->getContactDetailsForMembershipDetails('editable', $contactid);
                    $contactData = $contactData[0];
                    $contactData['fedMembershipId'] = $fedMembershipVal;
                    $fieldType = ($contactData['Iscompany'] == 1) ? 'Company' : 'Single person';

                    // Email validations for the contact
                    if ($emails[$contactObj->getFedContact()->getId()] != '')
                        $result = $this->em->getRepository('CommonUtilityBundle:FgCmAttribute')->searchEmailExistAndIsMergable($this->container, $contactid, $emails[$contactObj->getFedContact()->getId()], $hasFedmembership, $subscriberId = 0, $from = 'contact', $excluMerge = true, $fieldType);
                    else
                        $result = array();
                    if (count($result) > 0) {
                        unset($selectedIds[$key]);
                        continue;
                    } else {
                        $pdo = new ContactPdo($this->container);
                        $isMergeable = $this->isMergeableContact($contactData, $fedMembershipVal);
                        if ($isMergeable) {
                            $contactDataInMergableFormat = $this->convertDataToMergableFormat($contactData);
                            $mergeableReturn = $pdo->getMergeableContacts($contactDataInMergableFormat, $fieldType, $contactid);

                            // Checking the contact is mergable
                            if (count($mergeableReturn['duplicates']) > 0 || count($mergeableReturn['mergeEmail']) > 0) {
                                $meargable[$contactid]['currentContactData'] = $contactData;
                                $meargable[$contactid]['meargable'] = $mergeableReturn;
                                //unset($selectedIds[$key]);
                                continue;
                            }
                        }
                    }
                }
                $return['noparentload'] = true;
                $return['mergeable'] = false;
                $return['status'] = 'NORMAL';
                if (count($meargable) > 0) {
                    $return['mergeable'] = true;
                    $return['mergableContacts'] = $meargable;
                    $return['status'] = 'MERGE';
                }
                $return['contacts'] = array_values($selectedIds);

                return new JsonResponse($return);
            } else if ($hasFedmembership && count($selectedIds) == 1) {
                $contactObj = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->find($selectedIds[0]);
                $contactData = $pdo->getContactDetailsForMembershipDetails('editable', $selectedIds[0]);
                $contactData = $contactData[0];
                $contactData['fedMembershipId'] = $fedMembershipVal;
                $fieldType = ($contactData['Iscompany'] == 1) ? 'Company' : 'Single person';
                if ($emails[$contactObj->getFedContact()->getId()] != '')
                    $result = $this->em->getRepository('CommonUtilityBundle:FgCmAttribute')->searchEmailExistAndIsMergable($this->container, $selectedIds[0], $emails[$contactObj->getFedContact()->getId()], $hasFedmembership, $subscriberId = 0, $from = 'contact', $excluMerge = true, $fieldType);
                else
                    $result = array();
                if (count($result) > 0) {
                    unset($selectedIds[0]);
                    $return['status'] = 'FAILURE';
                    $return['flash'] = $this->get('translator')->trans('ASSIGNMENT_FAILED');
                    $return['noparentload'] = true;

                    return new JsonResponse($return);
                } else {
                    $isMergeable = $this->isMergeableContact($contactData, $fedMembershipVal);
                    if ($isMergeable) {
                        $contactDataInMergableFormat = $this->convertDataToMergableFormat($contactData);
                        $mergeableReturn = $pdo->getMergeableContacts($contactDataInMergableFormat, $fieldType, $selectedIds[0]);
                        // If the contact is mergable, then merging popup will displayed
                        if (count($mergeableReturn['duplicates']) > 0 || count($mergeableReturn['mergeEmail']) > 0) {
                            $mergeableReturn['status'] = 'MERGE';
                            $mergeableReturn['noparentload'] = true;
                            $mergeableReturn['mergeable'] = true;
                            $mergeableReturn['currentContactData'] = $contactData;

                            return new JsonResponse($mergeableReturn);
                        } else {

                            $return['noparentload'] = true;
                            $return['mergable'] = false;
                            $return['status'] = 'NORMAL';
                            $return['contacts'] = $selectedIds[0];

                            return new JsonResponse($return);
                        }
                    } else {
                        $return['noparentload'] = true;
                        $return['mergable'] = false;
                        $return['status'] = 'NORMAL';
                        $return['contacts'] = $selectedIds[0];

                        return new JsonResponse($return);
                    }
                }
            }
        } else {
            $status = array('Count' => '', 'status' => 'FAILURE');

            return new JsonResponse($status);
        }
    }

    /**
     * Check create/edit contact is mergeable
     * @param type $formValues
     * @param type $editData
     * @return boolean
     */
    private function isMergeableContact($formValues, $editData)
    {
        $mergeable = false;
        if (($formValues['fed_membership_cat_id'] == '' || $formValues['fed_membership_cat_id'] == null) && ($editData != '' || $editData != null)) {
            $mergeable = true;
        }
        return $mergeable;
    }

    /**
     * Function to merge contacts and give fed membership
     *
     * @return JsonResponse
     */
    public function saveFedmembershipContactAction(Request $request)
    {
        $contactData = $request->get('contactData', '');
        $mergeTo = $request->get('mergeTo', '');
        $merging = $request->get('merging', '');
        $flag = 0;
        if ($merging == 'save') {

            //Assign/Change/Remove Fed memebership handling
            $flashMsg = 'FED_MEMBERSHIP_ASSIGN_SUCCESS_MESSAGE';
            if ($merging == 'save' && $mergeTo != 'fed_mem') {
                $contactUpdateStr = "UPDATE fg_cm_contact SET merge_to_contact_id=" . $mergeTo . ",allow_merging=1, is_deleted=0 WHERE id ='" . $contactData['id'] . "'";
                $this->conn->executeQuery($contactUpdateStr);
                $flashMsg = 'MERGE_SUCCESS_MESSAGE';
                $contactObj = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->find($mergeTo);
                $flag = 1;
            }

            $fgFedMembershipObj = new FgFedMemberships($this->container);
            $fgFedMembershipObj->processFedMembership($contactData['id'], $contactData['fedMembershipId']);
            $fedMem = $flag ? $contactObj->getFedMembershipCat()->getId() : $contactData['fedMembershipId'];

            $status = array('id' => $contactData['id'], 'status' => 'SUCCESS', 'newFedId' => $fedMem, 'oldFedId' => $contactData['fed_membership_cat_id'], 'totalCount' => 1, 'flash' => $this->get('translator')->trans($flashMsg, array('%totalcount%' => 1, '%updatecount%' => 1)));

            return new JsonResponse($status);
        } else if ($merging == 'cancel') {
            $flashMsg = 'MERGE_NOT_SUCCESS_MESSAGE';
            $status = array('status' => 'FAILURE', 'totalCount' => 1, 'flash' => $this->get('translator')->trans($flashMsg, array('%totalcount%' => 1, '%updatecount%' => 1)));

            return new JsonResponse($status);
        }
    }

    /**
     * Function to convert array format
     *
     * @param array $contactData Current contact data
     *
     * @return Array
     */
    private function convertDataToMergableFormat($contactData)
    {
        $primaryEmail = $this->container->getParameter('system_field_primaryemail');
        $catCommun = $this->container->getParameter('system_category_communication');
        $catPerson = $this->container->getParameter('system_category_personal');
        $firstname = $this->container->getParameter('system_field_firstname');
        $lastname = $this->container->getParameter('system_field_lastname');
        $dob = $this->container->getParameter('system_field_dob');
        $land = $this->container->getParameter('system_field_corres_ort');
        $corrCat = $this->container->getParameter('system_category_personal');

        //$newContactData = $contactData;
        $newContactData = array();
        $newContactData[$catCommun][$primaryEmail] = $contactData[$primaryEmail];
        $newContactData[$catPerson][$firstname] = $contactData[$firstname];
        $newContactData[$catPerson][$lastname] = $contactData[$lastname];
        $newContactData[$catPerson][$dob] = $contactData[$dob];
        $newContactData[$corrCat][$land] = $contactData[$land];

        return $newContactData;
    }

    /**
     * Quit membership - club
     * @return type
     */
    public function quitMembershipAction()
    {
        $actionType = 'quit';
        $return = array('actionType' => $actionType,
            'clubId' => $this->clubId, 'type' => $this->clubType,
            'clubMembershipAvailable' => $this->get('club')->get('clubMembershipAvailable'));

        return $this->render('ClubadminContactBundle:Assignment:quitMembershipPopup.html.twig', $return);
    }

    /**
     * Quit membership - club
     * @return type
     */
    public function quitfedMembershipAction()
    {
        $actionType = 'quit';
        $return = array('actionType' => $actionType,
            'clubId' => $this->clubId, 'type' => $this->clubType,
            'fedMembershipAvailable' => $this->get('club')->get('fedMembershipMandatory'));

        return $this->render('ClubadminContactBundle:Assignment:quitfedMembershipPopup.html.twig', $return);
    }

    /**
     * save quit membership - club
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function saveQuitMembershipAction(Request $request)
    {
        $contactIds = $request->get('contact_id', '');
        $contacts = explode(',', $contactIds);
        $leavingDate = $request->get('leavingDate');
        $criteria = $request->get('criteria', 1);
        $totalCount = $request->get('totalCount');

        $this->em->getRepository('CommonUtilityBundle:FgCmContact')->setContainer($this->container);
        $quitMembership = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->quitContactClubMembership($contacts, $this->clubId, $criteria, $leavingDate, $this->contactId);
        $membershipArray = array();
        foreach ($quitMembership as $key => $membership) {
            if (!isset($membershipArray[$membership]) && $key != 0) {
                $membershipArray[$membership] = 0;
            }
            if ($key != 0) {
                $membershipArray[$membership] = $membershipArray[$membership] + 1;
            } else {
                //total success count
                $selCount = $membership;
            }
        }

        $flashMsg = 'selcount_OUT_OF_totalcount_CONTACTS_REMOVED_SUCCESSFULLY';

        return new JsonResponse(array('status' => 'SUCCESS', 'noparentload' => true, 'flash' => $this->get('translator')->trans($flashMsg, array('%selcount%' => $selCount, '%totalcount%' => $totalCount)),
            'membrshipArray' => $membershipArray, 'selcount' => $selCount));
    }

    /**
     * save quit Fed membership - club
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function saveQuitFedMembershipAction(Request $request)
    {
        $contactIds = $request->get('contact_id', '');
        $contacts = explode(',', $contactIds);
        $exludeContacts = $request->get('excluded_id', '');
        $exludeContactIds = explode(',', $exludeContacts);
        $memberShip = $request->get('contact_mem', '');
        $membershipArr = explode(',', $memberShip);
        $totalCount = $request->get('totalCount');
        $fgFedMembership = new FgFedMemberships($this->container);
        $membershipArray = array();
        $selCount = 0;
        foreach ($contacts as $selcontactId) {
            $membership = $membershipArr[$selCount];
            $fgFedMembership->processFedMembership($selcontactId);
            $membershipArray[$membership] = $membershipArray[$membership] + 1;
            $selCount++;
        }
        if ($selCount == 1) {
            $flashMsg = 'MESSAGE_SUCCESSFUL_FEDMEMBERS_TO_QUIT';
        } else {
            $flashMsg = 'MESSAGE__MULTIPLE_SUCCESSFUL_FEDMEMBERS_TO_QUIT';
        }


        return new JsonResponse(array('status' => 'SUCCESS', 'noparentload' => true, 'flash' => $this->get('translator')->trans($flashMsg, array('%selcount%' => $selCount, '%totalcount%' => $totalCount)),
            'membrshipArray' => $membershipArray, 'selcount' => $selCount));
    }

    /**
     * save club membership - drag drop and action menu
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function saveClubMembershipAction(Request $request)
    {
        $contactIds = $request->get('contactids', '');
        $contacts = explode(',', $contactIds);
        $joiningDate = $request->get('joiningDate');
        $transferDate = $request->get('transferDate');
        $criteria = $request->get('criteria', 1);
        $totalCount = $request->get('totalCount');
        $membership1 = $request->get('membership');
        $membershipArray = array();
        $this->em->getRepository('CommonUtilityBundle:FgCmContact')->setContainer($this->container);
        $assigned = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->assignClubMembership($this->container, $contacts, $membership1, $joiningDate, $criteria, $transferDate);

        foreach ($assigned as $key => $membership) {

            if (!isset($membershipArray[$membership['oldMembershipId']])) {
                $membershipArray[$membership['oldMembershipId']] = 0;
            }
            $membershipArray[$membership['oldMembershipId']] = $membershipArray[$membership['oldMembershipId']] + 1;
        }
        //total success count
        $selCount = count($assigned);

        $flashMsg = 'selcount_OUT_OF_totalcount_CONTACTS_ASSIGNED_SUCCESSFULLY';
        return new JsonResponse(array('status' => 'SUCCESS', 'noparentload' => true, 'flash' => $this->get('translator')->trans($flashMsg, array('%selcount%' => $selCount, '%totalcount%' => $totalCount)),
            'selcount' => $selCount, 'membership' => $membership1));
    }

    /**
     * validateTransferDate
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function validateTransferDateAction(Request $request)
    {
        $contactIds = $request->get('contactids', '');
        $isnull = $request->get('amp;isnull', 0);
        $joiningDate = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->validateTransferDate($contactIds, $this->clubId, $isnull);

        return new JsonResponse(array('joining1' => $joiningDate['joining_date1']));
    }

    /**
     * validateJoiningDate
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function validateJoiningDateAction(Request $request)
    {
        $contactIds = $request->get('contactids', '');
        $isnull = $request->get('amp;isnull', 0);
        $joiningDate = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->validateJoiningDate($contactIds, $this->clubId, $isnull);

        return new JsonResponse(array('leaving1' => $joiningDate['leaving_date1']));
    }

    /**
     * validate first joining date
     * @return JsonResponse
     */
    public function getFirstJoiningAction(Request $request)
    {
        $contactIds = $request->get('contactids', '');
        $firstJoining = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getJoiningDate($contactIds, $this->clubId);

        return new JsonResponse(array('firstjoiningdate' => $firstJoining['firstjoiningdate']));
    }
}
