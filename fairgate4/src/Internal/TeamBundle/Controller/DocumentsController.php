<?php

/**
 * Documents Controller
 *
 * This controller is used for handling functionalities related to team/workgroup documents in the internal section
 *
 * @package    InternalTeamBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 *
 */
namespace Internal\TeamBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Clubadmin\DocumentsBundle\Util\Documentlist;
use Symfony\Component\HttpFoundation\JsonResponse;
use Common\UtilityBundle\Repository\Pdo\DocumentPdo;
use Symfony\Component\HttpFoundation\File\File;
use Common\UtilityBundle\Util\FgUtility;
use Clubadmin\DocumentsBundle\Util\DocumentDetails;
use Symfony\Component\HttpFoundation\Request;
use Common\UtilityBundle\Util\FgPermissions;

/**
 * This controller is used to manage documents section
 *
 */
class DocumentsController extends Controller
{

    /**
     * This action is used for getting details of documents and its categories to show in personal document sidebar
     *
     * @return JsonResponse
     */
    public function getDocumentsSidebarAction()
    {
        $clubObj = $this->container->get('club');
        $myDocCategories = $this->getDefaultSidebarCategory("ALL");

        //set contact, club and team categories
        $em = $this->getDoctrine()->getManager();
        $contactCategories = $em->getRepository('CommonUtilityBundle:FgDmDocumentCategory')->getDocumentSidebarCategories($clubObj, $clubObj->get('id'), 'CONTACT', $this->container, false);
        $clubCategories = $em->getRepository('CommonUtilityBundle:FgDmDocumentCategory')->getDocumentSidebarCategories($clubObj, $clubObj->get('id'), 'CLUB', $this->container, false);

        $rightWiseRolesTeam = $this->getRightwiseWiseRoles("ALL", "TEAM");
        $adminstrativeTeams = $rightWiseRolesTeam['adminstrativeRoles'];
        $memberTeams = $rightWiseRolesTeam['memberRoles'];
        $teamCategories = array();
        if (count($adminstrativeTeams) > 0 || count($memberTeams) > 0) {
            $teamCategories = $em->getRepository('CommonUtilityBundle:FgDmDocumentCategory')->getDocumentSidebarCategories($clubObj, $clubObj->get('id'), 'TEAM', $this->container, false, $adminstrativeTeams, $memberTeams);
        }

        $rightWiseRolesWorkgroup = $this->getRightwiseWiseRoles("ALL", "WORKGROUP");
        $adminstrativeWorkgroups = $rightWiseRolesWorkgroup['adminstrativeRoles'];
        $memberWorkgroups = $rightWiseRolesWorkgroup['memberRoles'];
        $workgroupCategories = array();
        if (count($adminstrativeWorkgroups) > 0 || count($memberWorkgroups) > 0) {
            $workgroupCategories = $em->getRepository('CommonUtilityBundle:FgDmDocumentCategory')->getDocumentSidebarCategories($clubObj, $clubObj->get('id'), 'WORKGROUP', $this->container, false, $adminstrativeWorkgroups, $memberWorkgroups);
        }
        $categories = array_merge($myDocCategories, $contactCategories, $clubCategories, $teamCategories, $workgroupCategories);

        return new JsonResponse($categories);
    }

    /**
     * Method to get count of new/all documents
     *
     * @param string $doctype       'ALL/TEAM/WORKGROUP'
     * @param string $type          'all/new'
     * @param int    $currentRoleId current team/workgroup id
     *
     * @return int
     */
    private function getDocumentsCount($doctype, $type, $currentRoleId = "")
    {
        $documentlistClass = new Documentlist($this->container, $doctype);
        $documentlistClass->setCountForInternal();
        $conditionType = ($doctype == "ALL") ? "personal" : (($doctype == "TEAM") ? 'team' : 'workgroup');
        $documentlistClass->setConditionForInternal($conditionType, $currentRoleId);
        $documentlistClass->setFromForInternal();

        if ($type == "new") {
            $documentlistClass->addCondition("fdcs.contact_id IS NULL");
        }
        //$documentlistClass->setGroupBy('fdd.id');
        $qry = $documentlistClass->getResult();
        $documentPdo = new DocumentPdo($this->container);
        $results = $documentPdo->executeDocumentsQuery($qry);

        return $results[0]['count'];
    }

    /**
     * Method to get count of all role documents
     *
     * @param string $type       'TEAM/WORKGROUP'
     *
     * @return JsonResponse
     */
    public function getDocumentCountOfRolesAction($type = 'TEAM')
    {
        $em = $this->getDoctrine()->getManager();
        $contact = $this->get('contact');
        $aColumns = array('DISTINCT fdd.id', 'fdd.subcategory_id AS subCategoryId', 'fdd.category_id AS categoryId', 'IF(fdcs.contact_id IS NULL, 1, 0) AS isUnread', 'frm.id AS roleId');
        $documentlistClass = new Documentlist($this->container, $type);
        $condition = ($type == 'WORKGROUP') ? 'workgroupTabCount' : 'teamTabCount';
        $documentlistClass->setConditionForInternal($condition);
        $documentlistClass->setColumnsForInternal($aColumns);

        //get admin team results
        $documentlistClass->setFromForRoles();
        $subqry = $documentlistClass->getResult();
        $results = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgDmDocuments')->getTeamOrWorkgroupDocumentCountDetails($this->container, $subqry);

        $docDetails = new DocumentDetails($this->container);
        $isClubAdmin = (in_array('clubAdmin', $contact->get('allowedModules'))) ? 1 : 0;
        $myAdminTeams = array_keys($docDetails->getMyDocumentRoles('team'));
        $myAdminWorkgroups = array_keys($docDetails->getMyDocumentRoles('workgroup'));
        $groupRights = $contact->get('clubRoleRightsGroupWise');
        $myMemberTeams = (isset($groupRights['MEMBER']['teams'])) ? $groupRights['MEMBER']['teams'] : array();
        $myMemberWorkgroups = (isset($groupRights['MEMBER']['workgroups'])) ? $groupRights['MEMBER']['workgroups'] : array();
        $clubId = $this->get('club')->get('id');
        $contactId = $contact->get('id');
        $myMemberFunctions = $em->getRepository('CommonUtilityBundle:FgRmRoleContact')->getRolewiseFunctionsOfAContact($clubId, $contactId);

        $return = array('data' => $results, 'isClubAdmin' => $isClubAdmin, 'myAdminTeams' => $myAdminTeams, 'myAdminWorkgroups' => $myAdminWorkgroups, 'myMemberTeams' => $myMemberTeams, 'myMemberWorkgroups' => $myMemberWorkgroups, 'myMemberFunctions' => $myMemberFunctions);

        return new JsonResponse($return);
    }

    /**
     * This action is used for getting details of team documents and its categories to show in sidebar
     *
     * @param Request $request Request object
     * @param string  $type    TEAM/WORKGROUP
     *
     * @return JsonResponse
     */
    public function getRoleDocumentsSidebarAction(Request $request, $type)
    {
        $clubObj = $this->container->get('club');
        $em = $this->getDoctrine()->getManager();
        $currentRoleId = $request->get('roleId'); //team/workgroup Id
        $rightWiseRoles = $this->getRightwiseWiseRoles($type, $type);
        $adminstrativeRoles = $rightWiseRoles['adminstrativeRoles'];
        $memberRoles = $rightWiseRoles['memberRoles'];
        $isAdmin = ( in_array($currentRoleId, $adminstrativeRoles)) ? 1 : 0;
        $myDocCategories = $this->getDefaultSidebarCategory($type, $currentRoleId);

        $roleCategories = array();
        if (count($adminstrativeRoles) > 0 || count($memberRoles) > 0) {
            $roleCategories = $em->getRepository('CommonUtilityBundle:FgDmDocumentCategory')->getDocumentSidebarCategories($clubObj, $clubObj->get('id'), $type, $this->container, $isAdmin, $adminstrativeRoles, $memberRoles, $currentRoleId);
        }
        $categories = array_merge($myDocCategories, $roleCategories);

        return new JsonResponse($categories);
    }

    /**
     * Method to get administrative (teams/workgroups) and member (teams/workgroups)
     * handled: in case of personal documents. Here clubadmin have no privilages
     *
     * @param string $type         TEAM/WORKGROUP/ALL  (ALL in case of personal docs)
     * @param string $documentType TEAM/WORKGROUP
     *
     * @return array
     */
    private function getRightwiseWiseRoles($type, $documentType)
    {
        $clubRoleRights = $this->container->get('contact')->get('clubRoleRightsGroupWise');
        $adminstrativeRoles = array();
        $memberRoles = array();
        $adminRights = $this->container->get('contact')->get('mainAdminRightsForFrontend'); //clubadmin or superadmin
        if ($documentType === "TEAM") {
            if ((count($adminRights) > 0) && ($type != "ALL")) {
                $clubAdminTeams = array_keys($this->container->get('contact')->get('teams'));
                $adminstrativeRoles = $clubAdminTeams;
            } else {
                /* Teams which the contact have adminstrative role */
                $adminstrativeRoles = (count($clubRoleRights['ROLE_GROUP_ADMIN']['teams']) > 0) ? $clubRoleRights['ROLE_GROUP_ADMIN']['teams'] : array();
                if ($clubRoleRights['ROLE_DOCUMENT_ADMIN']['teams']) {
                    $adminstrativeRoles = array_merge($adminstrativeRoles, $clubRoleRights['ROLE_DOCUMENT_ADMIN']['teams']);
                }
            }
            /* Teams which the contact is a member of */
            $memberRoles = (count($clubRoleRights['MEMBER']['teams']) > 0) ? $clubRoleRights['MEMBER']['teams'] : array();
        } else if ($documentType === "WORKGROUP") {
            if ((count($adminRights) > 0) && ($type != "ALL")) {
                $clubAdminWorkgroups = array_keys($this->container->get('contact')->get('workgroups'));
                $adminstrativeRoles = $clubAdminWorkgroups;
            } else {
                /* Workgroups which the contact have adminstrative role */
                $adminstrativeRoles = (count($clubRoleRights['ROLE_GROUP_ADMIN']['workgroups']) > 0) ? $clubRoleRights['ROLE_GROUP_ADMIN']['workgroups'] : array();
                if ($clubRoleRights['ROLE_DOCUMENT_ADMIN']['workgroups']) {
                    $adminstrativeRoles = array_merge($adminstrativeRoles, $clubRoleRights['ROLE_DOCUMENT_ADMIN']['workgroups']);
                }
            }
            /* Workgroups which the contact is a member of */
            $memberRoles = (count($clubRoleRights['MEMBER']['workgroups']) > 0) ? $clubRoleRights['MEMBER']['workgroups'] : array();
        }

        return array("adminstrativeRoles" => $adminstrativeRoles, "memberRoles" => $memberRoles);
    }

    /**
     * Method used for updating counts
     *
     * @param Request $request Request object
     *
     * @return JsonResponse
     */
    public function SidebarCountsAction(Request $request)
    {
        $categories = $request->get("category");
        $type = $request->get("type", "ALL");  //$type can be 'ALL/TEAM/WORKGROUP'
        $currentRoleId = $request->get("currentRoleId", "");
        $em = $this->getDoctrine()->getManager();
        $resultArray = array();
        foreach ($categories as $key => $categoryArray) {
            switch ($key) {
                case 'MYDOCS':
                    if (count($categoryArray['entry']) == 2) { //contains 'new' and 'alldocuments'
                        $newDcoumentsCount = $this->getDocumentsCount($type, "new", $currentRoleId);
                        $resultArray[] = array("categoryId" => "", "subCatId" => "NEW", "dataType" => "NEW", "sidebarCount" => $newDcoumentsCount, "action" => 'show');
                    }  //for 'alldocuments'
                    $allDcoumentsCount = $this->getDocumentsCount($type, "all", $currentRoleId);
                    $resultArray[] = array("categoryId" => "", "subCatId" => "ALLDOCUMENTS", "dataType" => "ALLDOCUMENTS", "sidebarCount" => $allDcoumentsCount, "action" => 'show');
                    break;
                case 'CONTACT':
                case 'TEAM':
                case 'WORKGROUP':
                default : //FOR CLUB
                    $documentType = (!in_array($key, array('CONTACT', 'TEAM', 'WORKGROUP')) ) ? "CLUB" : $key;
                    foreach ($categoryArray['entry'] as $catArray) {
                        foreach ($catArray['input'] as $subcatArray) {
                            $rightWiseRoles = $this->getRightwiseWiseRoles($type, $documentType);
                            $documentsCount = $em->getRepository('CommonUtilityBundle:FgDmDocuments')->getSubcategoryDocumentsCount($subcatArray['id'], $documentType, $this->container, $currentRoleId, $rightWiseRoles['adminstrativeRoles'], $rightWiseRoles['memberRoles']);
                            $resultArray[] = array("categoryId" => $subcatArray['categoryId'], "subCatId" => $subcatArray['id'], "dataType" => $documentType, "sidebarCount" => $documentsCount, "action" => 'show', "type" => $key);
                        }
                    }
                    break;
            }
        }

        return new JsonResponse($resultArray);
    }

    /**
     * This action is used for listing Documents.
     *
     * @param Request $request Request object
     * @param string  $type    team/workgroup
     *
     * @return Template.
     */
    public function documentsListAction(Request $request)
    {
        $urlArray = array('url' => $this->generateUrl('group_documents_read_all', array('type' => '|type|', 'roleId' => '|roleId|')), 'title' => $this->get('translator')->trans('MARK_ALL_AS_SEEN'));
        $type = $request->get('module');
        $defaultColumnSetting = $this->container->getParameter('default_internal_documents_table_settings');

        $assignedRoles = ($type == 'team') ? $this->getAllowedTabs('teams') : $this->getAllowedTabs('workgroups');

        $permissionObj = new FgPermissions($this->container);
        $permissionObj->checkClubAccess($assignedRoles, 'document');

        $clubId = $this->get('club')->get('id');
        $contactId = $this->get('contact')->get('id');
        $clubData = array('clubId' => $clubId, 'contactId' => $contactId);

        $parameterArray = array('type' => $type,
            'tabs' => $assignedRoles,
            'teamCount' => count($assignedRoles),
            'clubData' => $clubData,
            'defaultColumnSetting' => $defaultColumnSetting,
            'url' => $this->generateUrl('get_documents_list', array('type' => $type, 'roleId' => 'dummyId')),
            'columnsUrl' => ($type == "team") ? $this->generateUrl('documents_columnsettings_team') : $this->generateUrl('documents_columnsettings_workgroup'),
            'urlArray' => $urlArray
        );

        if ($type == 'team' || $type == 'workgroup') {
            $uploadFunctionalityArray = $this->uploadFunctionality($request, $type);
            $parameterArray = array_merge($parameterArray, $uploadFunctionalityArray);
        }

        return $this->render('InternalTeamBundle:Documents:documentsList.html.twig', $parameterArray);
    }

    /**
     * This action show the documentt upload interface
     *
     * @param Request $request Request object
     *
     * @return array
     */
    private function uploadFunctionality($request, $docType)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $request->get('entity');
        $catId = $request->get('subCategoryId', '');

        $clubDetails = $this->get('club');
        $contactDetails = $this->get('contact');

        //get functions
        $parameters['functions'] = $em->getRepository('CommonUtilityBundle:FgRmFunction')->getAllTeamFunctionsOfAClub($clubDetails->get('club_team_id'), $clubDetails->get('default_lang'));
        $parameters['docType'] = $docType;
        $docDetails = new DocumentDetails($this->container);
        $parameters['deposited'] = $docDetails->getMyDocumentRoles($docType);

        if ($docType == 'team') {
            $parameters['saveurl'] = 'internal_team_document_upload_save';
            //get document category
            $parameters['subCategories'] = $em->getRepository('CommonUtilityBundle:FgDmDocumentCategory')->getDocumentSubCategories($clubDetails->get('id'), 'TEAM', $clubDetails->get('default_lang'));
        } else if ($docType == 'workgroup') {
            $parameters['saveurl'] = 'internal_workgroup_document_upload_save';
            //get document category
            $parameters['subCategories'] = $em->getRepository('CommonUtilityBundle:FgDmDocumentCategory')->getDocumentSubCategories($clubDetails->get('id'), 'WORKGROUP', $clubDetails->get('default_lang'));
        }

        $parameters['selectedentities'] = array($entity);
        $parameters['selectedcategory'] = $catId;
        $parameters['currentuser'] = $contactDetails->get('name');

        return $parameters;
    }

    /**
     * The action save the added document
     *
     * @param Request $request Request object
     *
     * @return JSONResponse
     */
    public function saveuploadAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $contactDetails = $this->get('contact');
        $clubDetails = $this->get('club');

        $documentDetails = $request->request->all();
        $visibilityDataArray = array_values($documentDetails["docvisibility"]);
        $visibilityKeyArray = array_keys($documentDetails["docvisibility"]);
        $visibilityFunctionArray = $documentDetails['docvisibility_2_for'];
        $depositedWithDataArray = array_values($documentDetails['deposited']);
        $publicValue = array_values($documentDetails['isPublic']);
        $docType = $request->get('doctype', 'TEAM');
        $docDetails = new DocumentDetails($this->container);
        $myEntities = ($docType == 'TEAM') ? $docDetails->getMyDocumentRoles('team') : $docDetails->getMyDocumentRoles('workgroup');
        foreach ($documentDetails['uploaded_documents'] as $key => $document) {
            //Move the uploaded files to the club folder
            $this->moveDocToClubFolder($document);

            $documentDetailsArray = array();
            $documentDetailsArray['name'] = $documentDetails['docname'][$key];
            $documentDetailsArray['description'] = $documentDetails['docdesc'][$key];
            $documentDetailsArray['subCategoryId'] = $documentDetails['doccategory'][$key];
            $documentDetailsArray['clubId'] = $this->get('club')->get('id');
            $documentDetailsArray['author'] = $documentDetails['docauthor'][$key];
            $documentDetailsArray['isVisible'] = 1;
            $documentDetailsArray['depositedWithSelection'] = array_intersect(array_keys($myEntities), $depositedWithDataArray[$key]);
            $documentDetailsArray['depositedWith'] = 'SELECTED';
            $documentDetailsArray['visibleFor'] = $visibilityDataArray[$key];
            $documentDetailsArray['docType'] = $docType;
            $documentDetailsArray['isPublic'] = $publicValue[$key];
            $documentDetailsArray['filename'] = $document;
            $documentDetailsArray['size'] = $documentDetails['uploaded_documents_size'][$key];
            $documentDetailsArray['functions'] = $visibilityFunctionArray[$visibilityKeyArray[$key]];
            $em->getRepository('CommonUtilityBundle:FgDmDocuments')->saveDocumentFrontend($documentDetailsArray, '', $contactDetails->get('id'), $clubDetails, $this->container);
        }
        return new JsonResponse(array('status' => 'SUCCESS', 'sync' => 1, 'noreload' => 1, 'flash' => $this->get('translator')->trans('DOCUMENT_UPLOADED_SUCCESSFULLY')));
        // return new JsonResponse(array('noreload' => true));
    }

    /**
     * The function to upload the file to the club folder
     */
    private function moveDocToClubFolder($document)
    {
        $uploadDirectory = FgUtility::getUploadDir() . "/";
        $clubDirectory = $uploadDirectory . $this->get('club')->get('id');
        $uploadedDirectory = $uploadDirectory . "/temp/";
        if (!is_dir($clubDirectory)) {
            mkdir($clubDirectory, 0777, true);
        }
        $clubDocumentDirectory = $clubDirectory . '/documents';
        if (!is_dir($clubDocumentDirectory)) {
            mkdir($clubDocumentDirectory, 0777, true);
        }

        //when the image is submitted with no change the image will not be there in the temp folder
        //beacuse it will already been moved common condition on edit
        if (file_exists($uploadedDirectory . $document)) {
            $attachmentObj = new File($uploadedDirectory . $document, false);
            $attachmentObj->move($clubDocumentDirectory, $document);
        }
    }

    /**
     * This action is used for getting columnsettings for team and workgroup docs
     *
     * @param Request $request Request object
     *
     * @return template
     */
    public function columnSettingsAction(Request $request)
    {
        $type = $request->get('module');
        switch ($type) {
            case "team":
                $redirect = 'internal_team_document_list';
                $defaultSettings = $this->container->getParameter('default_internal_documents_table_settings');
                break;
            case "workgroup":
                $redirect = 'internal_workgroup_document_list';
                $defaultSettings = $this->container->getParameter('default_internal_documents_table_settings');
                break;
        }
        $clubId = $this->get('club')->get('id');
        $contactId = $this->get('contact')->get('id');
        $breadCrumb = array('back' => $this->generateUrl($redirect));
        $clubData = array('clubId' => $clubId, 'contactId' => $contactId);

        return $this->render('InternalTeamBundle:Columnsettings:documentindex.html.twig', array('breadCrumb' => $breadCrumb, 'clubData' => $clubData, 'defaultSettings' => $defaultSettings, 'redirect' => $redirect, 'type' => $type, 'isFrontend' => true));
    }

    /**
     * This action is used for getting columnsettings data
     *
     * @return JsonResponse
     */
    public function getDocumentsColumnDetailsAction($type)
    {
        $documentsData = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgDmDocuments')->getInternalDocumentsFilterData($this->container, strtoupper($type));

        return new JsonResponse($documentsData);
    }

    /**
     * Method to get array for MYDOCS category in document sidebar
     *
     * @param string $docType       ALL/TEAM/WORKGROUP
     * @param int    $currentRoleId current team/workgroup id  (null in case of personal documents)
     *
     * @return array
     */
    private function getDefaultSidebarCategory($docType, $currentRoleId = "")
    {
        //set my doc category
        $newDcoumentsCount = $this->getDocumentsCount($docType, "new", $currentRoleId);
        $myDocsArray = array();
        if ($newDcoumentsCount > 0) {
            $myDocsArray[] = array("id" => "NEW", "title" => ucfirst($this->get('translator')->trans('DOC_NEW_DOCUMENTS')), "itemType" => "NEW");
        }
        $roles = ($docType == "TEAM") ? $this->container->get('contact')->get('teams') : $this->container->get('contact')->get('workgroups');
        //For personal documents title is 'My Documents' and for team/workgroup documents it is team/workgroup title
        $categoryTitle = ($currentRoleId == "") ? ucfirst($this->get('translator')->trans('DOC_MY_DOCUMENTS')) : ucfirst($roles[$currentRoleId]);
        $myDocsArray[] = array("id" => "ALLDOCUMENTS", "title" => ucfirst($this->get('translator')->trans('DOC_ALL_DOCUMENTS')), "itemType" => "ALLDOCUMENTS");
        $myDocCategories = array("MYDOCS" => array("id" => "MYDOCS", "title" => $categoryTitle, "entry" => $myDocsArray));

        return $myDocCategories;
    }

    /**
     * function to get drop down values category and subcategory
     *
     * @param string $typeval
     *
     * @return JsonResponse
     */
    public function getDropdownValuesAction($typeval)
    {
        $type = strtoupper($typeval);
        $clubId = $this->get('club')->get('id');
        $defaultLang = $this->get('club')->get('default_lang');
        $dataArray = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgDmDocumentCategory')->getAllCategoriesSubCategories($clubId, $type, $defaultLang);

        return new JsonResponse(array('resultArray' => $dataArray));
    }

    /**
     * Function to show remove documents popup
     *
     * @param Request $request Request object
     *
     * @return template
     */
    public function removeDocumentpopupAction(Request $request)
    {
        $docIds = $request->get('docIds');
        $teamTitle = $request->get('titleText');
        $type = $request->get('type');
        $roleId = $request->get('roleId');
        if ($docIds) {
            $popupTitle = str_replace('%team%', $teamTitle, $this->get('translator')->trans('REMOVE_DOCUMENTS_POPUP_TITLE'));
            $popupText = str_replace('%team%', $teamTitle, $this->get('translator')->trans('REMOVE_DOCUMENTS_POPUP_TEXT'));
        }

        $return = array("title" => $popupTitle, 'text' => $popupText, 'docIds' => $docIds, 'teamTitle' => $teamTitle, 'type' => $type, 'roleId' => $roleId);

        return $this->render('InternalTeamBundle:Documents:removeDocumentpopup.html.twig', $return);
    }

    /**
     * Function to remove documents from internal area
     *
     * @param Request $request Request object
     *
     * @return JsonResponse
     */
    public function removeDocumentAction(Request $request)
    {
        $docIds = $request->get('docIds');
        $docsArray = explode(",", $docIds);
        $totalDocs = count($docsArray);
        $clubId = $this->get('club')->get('id');
        $contactId = $this->get('contact')->get('id');
        $type = $request->get('type');
        $roleId = $request->get('roleId');
        $removedDocuments = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgDmDocuments')->removeDocumentsInternal($roleId, $docsArray, strtoupper($type), $clubId, $contactId, $this->container);

        return new JsonResponse(array('noparentload' => TRUE, 'status' => 'SUCCESS', 'flash' => str_replace(array("%removedDoc%", "%totalDoc%"), array($removedDocuments, $totalDocs), $this->get('translator')->trans('REMOVE_DOCUMENTS_SUCCESS'))));
    }

    /**
     * Handle edit functionality of a document
     *
     * @param int $documentId DocumentId
     *
     * @return template
     */
    public function editDocumentAction($documentId)
    {
        $em = $this->getDoctrine()->getManager();
        $club = $this->get('club');
        $contact = $this->get('contact');
        $clubId = $club->get('id');
        $contactId = $contact->get('id');
        $clubDefaultLang = $club->get('club_default_lang');
        //get document details for populating edit screen
        $documentDetails = current($em->getRepository('CommonUtilityBundle:FgDmDocuments')->getDocumentDetails($documentId));
        $documentType = strtolower($documentDetails['documentType']);

        //only super admin, club admin, team admin, team document admin have edit document rights
        $em->getRepository('CommonUtilityBundle:FgDmDocuments')->checkAdminPermissionForUser($documentDetails, $this->container, $clubId);

        $documentDetails['subCategories'] = $em->getRepository('CommonUtilityBundle:FgDmDocumentCategory')->getDocumentSubCategories($clubId, $documentType, $clubDefaultLang);
        $dataSet = array('documentId' => $documentId, 'documentType' => $documentType, 'clubId' => $clubId, 'contactId' => $contactId);
        $documentTeams = $documentWorkgroups = array();
        //deposited with option is available for edit only if deposited with selection is 'SELECTED'
        if ($documentDetails['depositedWith'] == 'SELECTED') {
            $docDetails = new DocumentDetails($this->container);
            $documentTeams = $docDetails->getMyDocumentRoles('team');
            $documentWorkgroups = $docDetails->getMyDocumentRoles('workgroup');
        }
        //get team functions to be populated in team document edit
        if ($documentType == 'team') {
            $clubTeamId = $club->get('club_team_id');
            $documentDetails['functions'] = $em->getRepository('CommonUtilityBundle:FgRmFunction')->getAllTeamFunctionsOfAClub($clubTeamId, $clubDefaultLang);
        }
        $documentDetails['teams'] = $documentTeams;
        $documentDetails['workgroups'] = $documentWorkgroups;

        $documentDetails['imagePath'] = false;
        //if image get image path else show default type icon
        if (in_array(strtolower(end(explode('.', $documentDetails['filename']))), array('jpg', 'jpeg', 'png', 'gif', 'ico', 'bmp'))) {
            $documentDetails['imagePath'] = "/uploads/{$clubId}/documents/" . $documentDetails['filename'];
        } else {
            $documentDetails['iconPath'] = FgUtility::getDocumentIcon($documentDetails['filename']);
        }
        $documentDetails['clubLanguages'] = $club->get('club_languages');
        $documentDetails['clubDefaultLang'] = $clubDefaultLang;
        $redirect = ($documentType == 'team') ? 'internal_team_document_list' : 'internal_workgroup_document_list';
        $dataSet['backLink'] = $this->generateUrl($redirect);
        $dataSet['breadCrumb'] = array('back' => $this->generateUrl($redirect));
        $dataSet['dataSet'] = $documentDetails;
       
        return $this->render('InternalTeamBundle:Documents:documentSettings.html.twig', $dataSet);
    }

    /**
     * Function to mark all document as read
     *
     * @param string $type   team/workgroup
     * @param int    $roleId teamid/workgroupid
     *
     * @return JsonResponse
     */
    public function markAllasReadAction($type, $roleId)
    {
        $contactId = $this->container->get('contact')->get('id');
        $documentlistClass = new Documentlist($this->container, strtoupper($type));
        $documentlistClass->setColumnsForInternal();
        $documentlistClass->setConditionForInternal($type, $roleId);
        $documentlistClass->setFromForInternal();

        $documentlistClass->addCondition("fdcs.contact_id IS NULL");
        $documentlistClass->setGroupBy('fdd.id');
        $query = $documentlistClass->getResult();

        $documentPdo = new DocumentPdo($this->container);
        $results = $documentPdo->executeDocumentsQuery($query);
        foreach ($results as $key => $value) {
            $results[$key] = $value['id'];
        }
        $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgDmContactSighted')->documentSighted($contactId, $results);

        $redirect = ($type == "workgroup") ? $this->generateUrl('internal_workgroup_document_list') : $this->generateUrl('internal_team_document_list');

        return new JsonResponse(array('status' => 'SUCCESS', 'sync' => 1, 'redirect' => $redirect, 'flash' => $this->get('translator')->trans('ALL_DOCUMENTS_MARKED_AS_SEEN')));
    }

    /**
     * To update a team/workgroup document
     *
     * @param Request $request    Request object
     * @param int     $documentId DocumentId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function updateDocumentAction(Request $request, $documentId)
    {
        $em = $this->getDoctrine()->getManager();
        $contactDetails = $this->get('contact');
        $club = $this->get('club');
        //get all edited document details
        $documentDetails = $request->request->all();
        $clubLanguages = $club->get('club_languages');
        //build array for saving document data
        $documentArr = array();
        $documentArr['document_id'] = $documentDetails[$documentId . '_documentId'];
        $documentArr['subCategoryId'] = $documentDetails[$documentId . '_subCategoryId'];
        $documentArr['docType'] = $documentDetails[$documentId . '_documentType'];
        $documentArr['depositedWith'] = $documentDetails[$documentId . '_depositedWith'];
        $documentArr['visibleFor'] = $documentDetails[$documentId . '_visibleFor'];
        $documentArr['isPublic'] = $documentDetails[$documentId . '_isPublic'];
        $documentArr['depositedWithSelection'] = $documentDetails[$documentId . '_depositedWithSelection'];
        $documentArr['visibleForSelection'] = $documentDetails[$documentId . '_visibleForSelection'];
        $documentArr['depositedWithOptions'] = explode(',', $documentDetails[$documentId . '_depositedWithOptions']);
        //save file details if new file is uploaded
        if ($documentDetails['uniquename'] != '') {
            $documentArr['filename'] = $documentDetails['uniquename'];
            $documentArr['size'] = $documentDetails['size'];

            //Move the uploaded files to the club folder
            $this->moveDocToClubFolder($documentArr['filename']);
        }
        //build document data to be saved in main and i18n table
        foreach ($clubLanguages as $lang) {
            $documentArr['name'][$lang] = $documentDetails[$documentId . '_i18n_' . $lang . '_name'];
            $documentArr['author'][$lang] = $documentDetails[$documentId . '_i18n_' . $lang . '_author'];
            $documentArr['description'][$lang] = $documentDetails[$documentId . '_i18n_' . $lang . '_description'];
        }
        //save edited document data
        $em->getRepository('CommonUtilityBundle:FgDmDocuments')->saveDocumentFrontend($documentArr, $documentId, $contactDetails->get('id'), $club, $this->container);
        $redirectUrl = ($documentArr['docType'] == 'TEAM') ? $this->generateUrl('edit_team_document', array('documentId' => $documentId)) : $this->generateUrl('edit_workgroup_document', array('documentId' => $documentId));

        return new JsonResponse(array('status' => true, 'flash' => $this->get('translator')->trans('DOCUMENT_UPDATE_SUCCESS_MSG'), 'redirect' => $redirectUrl, 'sync' => 1));
    }

    /**
     * To remove all team/workgroup which has no access.
     *
     * @param String $type group type
     *
     * @return array
     */
    private function getAllowedTabs($type = 'teams')
    {
        $assignedGroups = $this->container->get('contact')->get($type);
        $groupRights = $this->container->get('contact')->get('clubRoleRightsGroupWise');
        $allRights = array_keys($groupRights);
        $groups = array_keys($assignedGroups);
        $mainRights = $this->container->get('contact')->get('mainAdminRightsForFrontend');
        foreach ($groups as $group) {
            $flag = 0;
            if (!empty($mainRights)) {
                $flag = 1;
            } elseif (in_array('MEMBER', $allRights) && in_array($group, $groupRights['MEMBER'][$type])) {
                $flag = 1;
            } elseif (in_array('ROLE_GROUP_ADMIN', $allRights) && in_array($group, $groupRights['ROLE_GROUP_ADMIN'][$type])) {
                $flag = 1;
            } elseif (in_array('ROLE_DOCUMENT_ADMIN', $allRights) && in_array($group, $groupRights['ROLE_DOCUMENT_ADMIN'][$type])) {
                $flag = 1;
            }
            if ($flag == 0) {
                unset($assignedGroups[$group]);
            }
        }
        return $assignedGroups;
    }
}
