<?php

namespace Clubadmin\DocumentsBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Common\UtilityBundle\Util\FgUtility;
use Clubadmin\DocumentsBundle\Util\DocumentData;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Common\UtilityBundle\Repository\Pdo\ContactPdo;
use Common\UtilityBundle\Repository\Pdo\DocumentPdo;
use Common\FilemanagerBundle\Util\FileChecking;
use Common\UtilityBundle\Repository\Pdo\ClubPdo;
use Common\UtilityBundle\Util\FgPermissions;
/**
 * DocumentsController
 *
 * This controller was created for managing documents.
 *
 * @package    ClubadminDocumentsBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
class DocumentsController extends FgController
{

    /**
     * This action is used for listing Club Documents.
     *
     * @Template("ClubadminDocumentsBundle:Documents:index.html.twig")
     *
     * @return array Data array.
     */
    public function clubDocumentsAction()
    {
        $clubPdo = new ClubPdo($this->container);
        $subclubs = $clubPdo->getAllSubLevelData($this->clubId);
        $columnSettingsPath = $this->generateUrl('document_column_settings_club');
        $defaultColumnSetting = $this->container->getParameter('default_doc_club_table_settings');

        return array('type' => 'CLUB', 'subclubs' => $subclubs, 'bookedModulesDet' => $this->get('club')->get('bookedModulesDet'), 'clubType' => $this->clubType, 'clubDefaultLang' => $this->get('club')->get('club_default_lang'), 'clubId' => $this->clubId, 'contactName' => $this->contactName, 'contactId' => $this->contactId, 'columnSettings' => $columnSettingsPath, 'defaultColumnSetting' => $defaultColumnSetting);
    }

    /**
     * This action is used for listing Team Documents.
     *
     * @Template("ClubadminDocumentsBundle:Documents:index.html.twig")
     *
     * @return array Data array.
     */
    public function teamDocumentsAction()
    {
        $documentDetails = $this->em->getRepository('CommonUtilityBundle:FgRmRole')->getTeamsOrWorkgroupsForDocumentsDepositedOption('team', $this->clubTeamId, $this->clubDefaultLang, false, false, $this->container);
        $columnSettingsPath = $this->generateUrl('document_column_settings_team');
        $defaultColumnSetting = $this->container->getParameter('default_doc_team_table_settings');

        return array('type' => 'TEAM', 'teams' => $documentDetails, 'clubDefaultLang' => $this->get('club')->get('club_default_lang'), 'contactName' => $this->contactName, 'clubId' => $this->clubId, 'contactId' => $this->contactId, 'columnSettings' => $columnSettingsPath, 'defaultColumnSetting' => $defaultColumnSetting);
    }

    /**
     * This action is used for listing Workgroup Documents.
     *
     * @Template("ClubadminDocumentsBundle:Documents:index.html.twig")
     *
     * @return array Data array.
     */
    public function workgroupDocumentsAction()
    {
        $documentDetails = $this->em->getRepository('CommonUtilityBundle:FgRmRole')->getTeamsOrWorkgroupsForDocumentsDepositedOption('workgroup', $this->clubWorkgroupId, $this->clubDefaultLang, false, false, $this->container);
        $columnSettingsPath = $this->generateUrl('document_column_settings_workgroup');
        $defaultColumnSetting = $this->container->getParameter('default_doc_workgroup_table_settings');

        return array('type' => 'WORKGROUP', 'workgroups' => $documentDetails, 'clubDefaultLang' => $this->get('club')->get('club_default_lang'), 'contactName' => $this->contactName, 'clubId' => $this->clubId, 'contactId' => $this->contactId, 'columnSettings' => $columnSettingsPath, 'defaultColumnSetting' => $defaultColumnSetting);
    }

    /**
     * This action is used for listing Contact Documents.
     *
     * @Template("ClubadminDocumentsBundle:Documents:index.html.twig")
     *
     * @return array Data array.
     */
    public function contactDocumentsAction()
    {
        $clubPdo = new ClubPdo($this->container);
        $subclubs = $clubPdo->getAllSubLevelData($this->clubId);
        $columnSettingsPath = $this->generateUrl('document_column_settings_contact');
        $defaultColumnSetting = $this->container->getParameter('default_doc_contact_table_settings');

        return array('type' => 'CONTACT', 'subclubs' => $subclubs, 'bookedModulesDet' => $this->get('club')->get('bookedModulesDet'), 'clubDefaultLang' => $this->get('club')->get('club_default_lang'), 'clubId' => $this->clubId, 'contactName' => $this->contactName, 'contactId' => $this->contactId, 'columnSettings' => $columnSettingsPath, 'defaultColumnSetting' => $defaultColumnSetting);
    }

    /**
     * This action is used for getting details like document categories, sub-categories, bookmarks etc.
     * 
     * @param string $type Document type (club,contact,team,workgroup)
     * 
     * @return JsonResponse
     */
    public function getDocumentsDataAction($type)
    {
        $documentsData = $this->em->getRepository('CommonUtilityBundle:FgDmDocuments')->getDocumentsFilterData($this->get('club'), $this->clubId, $this->contactId, $this->container, strtoupper($type), $this->clubTeamId, $this->clubWorkgroupId, $this->clubDefaultLang, $this->clubType);

        return new JsonResponse($documentsData);
    }

    /**
     * Handle edit functionality of a document
     *
     * @param int    $documentId Document Id for edit
     * @param int    $offset     Offset
     * @param string $module     module name as files/document
     *
     * @return template
     */
    public function documentSettingsAction($documentId, $offset, $module)
    {
        $documentDetails = $this->em->getRepository('CommonUtilityBundle:FgDmDocuments')->getDocumentDetails($documentId);
        $documentDetails = current($documentDetails);
        $documentType = strtolower($documentDetails['documentType']);
        $permissionObj = $this->fgpermission;
        $accessCheck = ($this->clubId != $documentDetails['clubId']) ? 0 : 1;
        $permissionObj->checkClubAccess($accessCheck, "backend_document_edit");
        $nextPreviousResultset = $this->nextPreviousBtnDocumentAction($documentId, $offset, 'document_settings_' . $documentType, 'documentId', 'offset', 0);
        $dataSet = $this->getDocumentSettingsOfType($documentType, $documentDetails);
        $redirect = $dataSet['backLink'];
        $dataSet['imagePath'] = false;
        if (in_array(strtolower(end(explode('.', $documentDetails['filename']))), array('jpg', 'jpeg', 'png', 'gif', 'ico', 'bmp'))) {
            $dataSet['imagePath'] = "/uploads/{$this->clubId}/documents/" . $documentDetails['filename'];
        } else {
            $dataSet['iconPath'] = FgUtility::getDocumentIcon($documentDetails['filename']);
        }
        $dataSet = $dataSet + array('documentId'=>$documentId, 'offset'=>$offset, 'bookedModulesDet'=>$this->get('club')->get('bookedModulesDet'), 'docNextPrevious'=>$nextPreviousResultset,'module'=>$module );
        $dataSet['backLink'] = ($module == 'files') ? $this->generateUrl('filemanager_listModuleFiles') : $this->generateUrl($redirect);
        $dataSet['subCategories'] = $this->em->getRepository('CommonUtilityBundle:FgDmDocumentCategory')->getDocumentSubCategories($this->clubId, $documentType, $this->clubDefaultLang);
        $dataSet['breadCrumb'] = array('back' => ($module == 'files') ? $this->generateUrl('filemanager_listModuleFiles') : $this->generateUrl($redirect));
        
        return $this->render('ClubadminDocumentsBundle:Documents:documentSettings.html.twig', $dataSet);
    }
    
    /**
     * Function to get settings array of document type for document settings page
     * 
     * @param string $documentType    Document type (club,contact,team,workgroup)
     * @param array  $documentDetails Array containing details of current document id
     * 
     * @return array
     */
    private function getDocumentSettingsOfType($documentType, $documentDetails){
        $return = array('contactId' => $this->contactId, 'clubId' => $this->clubId, 'clubType' => $this->clubType, 'clubLanguages' => $this->clubLanguages, 'documentType'=>$documentType);
        if ($documentType == 'club') {
            $clubPdo = new ClubPdo($this->container);
            $return['subclubs'] = $clubPdo->getAllSubLevelData($this->clubId);
            $return['backLink'] = 'club_documents_listing';
        } elseif ($documentType == 'team') {
            $documentTeams = $this->em->getRepository('CommonUtilityBundle:FgRmRole')->getTeamsOrWorkgroupsForDocumentsDepositedOption('team', $this->clubTeamId, $this->clubDefaultLang, false, false, $this->container);
            $return['teams'] = $documentTeams;
            $return['backLink'] = 'team_documents_listing';
        } elseif ($documentType == 'workgroup') {
            $documentWorkgroups = $this->em->getRepository('CommonUtilityBundle:FgRmRole')->getTeamsOrWorkgroupsForDocumentsDepositedOption('workgroup', $this->clubWorkgroupId, $this->clubDefaultLang, false, false, $this->container);
            $return['workgroups'] = $documentWorkgroups;
            $return['backLink'] = 'workgroup_documents_listing';
        } elseif ($documentType == 'contact') {
            $return['contactSelected'] = (!empty($documentDetails['contactAssignments'])) ? $this->getContactAssignments($documentDetails['contactAssignments']) : $contactSelected;
            $return['contactExcluded'] = (!empty($documentDetails['contactExclude'])) ? $this->getContactAssignments($documentDetails['contactExclude']):'';
            $return['backLink'] = 'contact_documents_listing';
        }
        $return['clubDefaultLang'] = $this->get('club')->get('club_default_lang');
        $return['dataSet'] = $documentDetails;
        
        return $return;
    }

    /**
     * Function to get the different versions of a document
     *
     * @param int $documentId
     *
     * @return JsonResponse
     */
    public function versionListAction($documentId)
    {
        $versionsList = $this->em->getRepository('CommonUtilityBundle:FgDmVersion')->getDocumentVersionDetails($documentId);
        $return['aaData'] = $versionsList;

        return new JsonResponse($return);
    }

    /**
     * Function to get include/exclude list of contacts
     * 
     * @param array $contactsList Array of contact Ids
     * 
     * @return array
     */
    private function getContactAssignments($contactsList)
    {
        $contact = new ContactPdo($this->container);
        $where = " fg_cm_contact.id IN ({$contactsList})";
        
        return $contact->getContactData($this->get('club'), '', $where, array('contactid', 'contactNameYOB'), 'contact');
    }

    /**
     * Function to invoke save document function
     * 
     * @param Request $request      Request object
     * @param String  $documentType Document type(club,contact,team,workgroup)
     * 
     * @return JsonResponse
     */
    public function saveDocumentAction(Request $request, $documentType)
    {
        $documentArr = json_decode($request->get('documentArr'), true);
        $documentType = strtolower($documentType);
        $clubDetails = array('clubId' => $this->clubId, 'clubType' => $this->clubType, 'clubDefaultLang' => $this->get('club')->get('club_default_lang'), 'clubLanguages' => $this->clubLanguages);
        $clubDefaultLang = $this->get('club')->get('club_default_lang');
        // for replacing space with (-) and  removing SingleQuotes from filename
        $fileCheck = new FileChecking($this->container);
        foreach ($documentArr as $key => $value) {
            $documentArr[$key]['i18n'][$clubDefaultLang]['name'] = $fileCheck->replaceSingleQuotes($value['i18n'][$clubDefaultLang]['name']);
        }
        $updateTopNavCountArr = $this->em->getRepository('CommonUtilityBundle:FgDmDocuments')->saveDocumentDetails($this->container, $documentArr, $documentType, $clubDetails, $this->contactId);
        $updatedCount = count($updateTopNavCountArr);
        $msg = ($updatedCount > 1) ? $this->get('translator')->trans('DOCUMENT_UPLOAD_SUCCESS_MSG_PLURAL', array('%count%' => $updatedCount)) : $this->get('translator')->trans('DOCUMENT_UPLOAD_SUCCESS_MSG_SINGLE');

        return new JsonResponse(array('status' => true, 'flash' => $msg, 'noparentload' => 1, 'updateTopNavCountArr' => $updateTopNavCountArr, 'totalCount' => sizeof($updateTopNavCountArr)));
    }

    /**
     * For list the all document data
     * 
     * @param Request $request Request object
     * @param string  $doctype Document type(club,contact,team,workgroup)
     * 
     * @return JsonResponse
     */
    public function listDocumentAction(Request $request, $doctype)
    {
        
        $docObj = new DocumentData($this->container, $doctype);
        $output = $docObj->getDocumentList($request);

        return new JsonResponse($output);
    }

    /**
     * Function to download documents
     * 
     * @param string $docId     Document id of the document to download
     * @param int    $versionId Document version id
     *
     * @return response
     */
    public function downloadDocumentsAction($docId, $versionId)
    {
        $applicationArea = $this->container->get('club')->get('applicationArea');
        $contactId = $this->container->get('contact')->get('id');
        $documentId = array($docId);
        if ($applicationArea != "backend") {
            if (!empty($contactId)) {
                $result = $this->em->getRepository('CommonUtilityBundle:FgDmContactSighted')->findOneBy(array('contact' => $contactId, 'document' => $documentId));
                $docObj = $this->em->getRepository('CommonUtilityBundle:FgDmDocuments')->find($docId);
                if (!$result) {
                    if ($docObj) {
                        $this->em->getRepository('CommonUtilityBundle:FgDmContactSighted')->documentSighted($contactId, $documentId);
                    }
                }
            }
        }
        $docObj = $this->em->getRepository('CommonUtilityBundle:FgDmDocuments')->find($docId);
        $permissionObj = new FgPermissions($this->container);
        $docAccess = false;
        if($docObj){
           
	  $docAccess = $permissionObj->checkDocumentAccess($docObj->getIsPublishLink(), $contactId, $docObj->getIsVisibleToContact() ,$docId );
        }else{
             $permissionObj->checkClubAccess('');
        }
        if ($docAccess == false) {
            $permissionObj->checkUserAccess(0);    
        }
        
        ini_set('max_execution_time', 0);
        ini_set("memory_limit", "2000M");
        $rootPath = FgUtility::getRootPath($this->container);
        $clubDefaultLang = $this->get('club')->get('default_lang');
        // change the path to fit your websites document structure
        $result = $this->em->getRepository('CommonUtilityBundle:FgDmDocuments')->getVersionDoc($docId, $versionId, $clubDefaultLang);
        $downloadPath = $rootPath . '/uploads/' . $result[0]['club'] . '/documents/';
        $dlFile = filter_var($result[0]['file'], FILTER_SANITIZE_STRING); // Remove (more) invalid characters
        $fullPath = $downloadPath . $dlFile;  
        $fileInfo = new \SplFileInfo($dlFile);
        /* get file extension */
        $fileExtension = pathinfo($fileInfo->getFilename(), PATHINFO_EXTENSION);
        if ($file = fopen($fullPath, "r")) {
            $fsize = filesize($fullPath);
            $response = new Response();
            $response->setStatusCode(200);
            $response->headers->set('Content-Type', mime_content_type($fullPath) . '; charset=utf-8;');
            $response->headers->set('Content-Disposition', 'attachment; filename= "' . $result[0]['name'] . '";');
            $response->headers->set('Content-Transfer-Encoding', 'utf-8');
            $response->headers->set("Content-length: $fsize");
            $response->headers->set("Cache-control: private"); //use this to open files directly
            $response->sendHeaders();
            /* In case of docx files, if we use readfile it will add some extra numerals, so using fread */
            //$response->setContent(readfile($fullPath));
            $response->setContent(fread($file,filesize($fullPath)) );
            fclose($file);

            return $response;
        } else {
            throw $this->createNotFoundException($this->get('translator')->trans('FILE_NOT_EXIST'));
        }
    }

    /**
     * Function to modify table settings
     *
     * @return template
     */
    public function columnSettingsAction(Request $request)
    {
        $type = $request->get('level1');
        switch ($type) {
            case "club": $redirect = 'club_documents_listing';
                $defaultSettings = $this->container->getParameter('default_doc_club_table_settings');
                break;
            case "team": $redirect = 'team_documents_listing';
                $defaultSettings = $this->container->getParameter('default_doc_team_table_settings');
                break;
            case "workgroup": $redirect = 'workgroup_documents_listing';
                $defaultSettings = $this->container->getParameter('default_doc_workgroup_table_settings');
                break;
            case "contact": $redirect = 'contact_documents_listing';
                $defaultSettings = $this->container->getParameter('default_doc_contact_table_settings');
                break;
        }
        $breadCrumb = array('back' => $this->generateUrl($redirect));
        $clubData = array('clubId' => $this->clubId, 'contactId' => $this->contactId);

        return $this->render('ClubadminDocumentsBundle:Columnsettings:index.html.twig', array('breadCrumb' => $breadCrumb, 'clubData' => $clubData, 'defaultSettings' => $defaultSettings, 'redirect' => $redirect, 'type' => $type));
    }

    /**
     * Function to show delete popup
     *
     * @return template
     */
    public function deletePopupAction(Request $request)
    {
        $actionType = $request->get('actionType');
        $docIds = '';
        if ($actionType == 'documentdelete') {
            $contactCount = $request->get('contactCount');
            $docIds = $request->get('selectIds');
            if ($contactCount > 1) {
                $titleText = str_replace('%count%', $contactCount, $this->get('translator')->trans('DC_DELETE_POPUP_HEADER_PLURAL'));
            } else {
                $titleText = $this->get('translator')->trans('DC_DELETE_POPUP_HEADER_SINGULAR');
            }
            $deleteDesc = 'DC_DELETE_MESSAGE';
        } elseif ($actionType == 'documentVersionDelete') {
            $titleText = $this->get('translator')->trans('DOCUMENT_DELETE_VERSION_POPUP_HEADER');
            $deleteDesc = 'DOCUMENT_VERSION_DELETE_MESSAGE';
        } elseif ($actionType == 'documentOldVersionDelete') {
            $titleText = $this->get('translator')->trans('DOCUMENT_DELETE_ALL_VERSION_POPUP_HEADER');
            $deleteDesc = 'DOCUMENT_VERSION_DELETE_ALL_MESSAGE';
            $docIds = $request->get('documentId');
        }
        $selActionType = $request->get('selActionType') ? $request->get('selActionType') : '';

        $return = array('actionType' => $actionType, 'clubId' => $this->clubId, 'clubType' => $this->clubType, 'selActionType' => $selActionType, 'deleteDesc' => $this->get('translator')->trans($deleteDesc), 'titleText' => $titleText, 'docIds' => $docIds);

        return $this->render('ClubadminDocumentsBundle:Documents:confirmDelete.html.twig', $return);
    }

    /**
     * Function used to delete a document
     *
     * @return JSON
     */
    public function deleteDocumentAction(Request $request)
    {
        $actionType = $request->get('actionType', '');
        $selectedId = json_decode($request->get('selectedId', '0'));
        if ($actionType == 'documentdelete') {
            $selectedId = explode(",", $selectedId);
        }
        if ($request->getMethod() == 'POST') {
            $flashMsg = $countDetails = $idCount = $type = '';
            if (count($selectedId) > 0) {
                if ($actionType == 'documentdelete') {
                    $countDetails = $this->em->getRepository('CommonUtilityBundle:FgDmDocuments')->getsubCategoryDeleteCount($selectedId, $this->clubId);
                    $type = $countDetails[0]['documentType'];
                    $idCount = count($selectedId);
                    $deleteDetails = $this->em->getRepository('CommonUtilityBundle:FgDmDocuments')->deleteDocuments($selectedId, 'document', $this->clubId);
                    $flashMsg = ($idCount > 1) ? 'DC_DELETE_SUCCESS_MESSAGE_PLURAL':'DC_DELETE_SUCCESS_MESSAGE_SINGULAR';
                } elseif ($actionType == 'documentVersionDelete') {
                    $deleteDetails = $this->em->getRepository('CommonUtilityBundle:FgDmDocuments')->deleteDocuments($selectedId, 'version', $this->clubId);
                    $flashMsg = (count($selectedId) > 1) ? 'DOCUMENT_DELETE_VERSION_SUCCESS_MESSAGE_PLURAL':'DOCUMENT_DELETE_VERSION_SUCCESS_MESSAGE_SINGULAR';
                }

                return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans($flashMsg), 'noparentload' => 1, 'countdata' => $countDetails, 'count' => $idCount, 'type' => $type));
            }
        }
    }

    /**
     * Function to show delete popup in edit page
     *
     * @return template
     */
    public function editPagedeletePopupAction(Request $request)
    {
        $actionType = $request->get('actionType');
        $docId = $request->get('docid');
        $docType = $request->get('docType');
        $titleText = $this->get('translator')->trans('DC_DELETE_POPUP_HEADER_SINGULAR');
        $deleteDesc = 'DC_DELETE_MESSAGE';
        $selActionType = $request->get('selActionType') ? $request->get('selActionType') : '';
        $return = array('actionType' => $actionType, 'clubId' => $this->clubId, 'clubType' => $this->clubType, 'selActionType' => $selActionType, 'deleteDesc' => $this->get('translator')->trans($deleteDesc), 'titleText' => $titleText, 'docId' => $docId, 'docType' => $docType);

        return $this->render('ClubadminDocumentsBundle:Documents:deletepopupEditpage.html.twig', $return);
    }

    /**
     * Function used to delete a document from edit page
     * 
     * @param Request $request Request object
     * 
     * @return JsonResponse
     */
    public function deleteDocumentFromEditPageAction(Request $request)
    {
        $docId = $request->get('docId', '0');
        $docType = $request->get('docType', '');
        $documentId = array($docId);
        $this->em->getRepository('CommonUtilityBundle:FgDmDocuments')->deleteDocuments($documentId, 'document', $this->clubId);
        if ($docType == 'club') {
            $redirectUrl = $this->generateUrl('club_documents_listing');
        } elseif ($docType == 'contact') {
            $redirectUrl = $this->generateUrl('contact_documents_listing');
        } elseif ($docType == 'team') {
            $redirectUrl = $this->generateUrl('team_documents_listing');
        } else {
            $redirectUrl = $this->generateUrl('workgroup_documents_listing');
        }
        
        return new JsonResponse(array('status' => 'SUCCESS', 'redirect' => $redirectUrl, 'sync' => 1));
    }

    /**
     * Function to create document category or sub category
     *
     * @param Request $request Request object
     *
     * @return JsonResponse
     */
    public function createCategoryAction(Request $request)
    {
        $title = $request->get('value');
        $catType = $request->get('elementType');
        $docType = $request->get('docType');
        $categoryId = $request->get('category_id');
        $translator = $this->get('translator');
        if ($catType == 'category') {
            $maxSortOrder = $this->em->getRepository('CommonUtilityBundle:FgDmDocumentCategory')->getMaxSortOrderofCategories($this->clubId, $docType) + 1;
            $dataArray = array('0' => array('title' => array($this->clubDefaultLang => $title), 'catType' => $docType, 'sortOrder' => $maxSortOrder));
            $lastInsertedId = $this->em->getRepository('CommonUtilityBundle:FgDmDocumentCategory')->categorySave($this->clubId, $this->clubDefaultLang, $dataArray, $this->clubLanguages, true);
            $return = array('items' => array('0' => array('id' => $lastInsertedId, 'title' => $title, 'type' => 'select', "draggable" => 1)));
        } elseif ($catType == 'subcategory') {
            $maxSortOrder = $this->em->getRepository('CommonUtilityBundle:FgDmDocumentSubcategory')->getMaxSortOrderofSubcategories($categoryId) + 1;
            $dataArray = array('0' => array('title' => array($this->clubDefaultLang => $title), 'catType' => $docType, 'sortOrder' => $maxSortOrder));
            $lastInsertedId = $this->em->getRepository('CommonUtilityBundle:FgDmDocumentSubcategory')->subcategorySave($this->clubId, $this->clubDefaultLang, $dataArray, $this->clubLanguages, $categoryId, true);
            $filterData = array( 'id' => 'DOCS-' . $this->clubId, 'title' => $translator->trans('DOCUMENTS'),
                'fixed_options' => array( '0' => array('0' => array('id' => '', 'title' => "- " . $translator->trans('DOCUMENT_SELECT_CATEGORY') . " -")),
                    '1' => array( '0' => array('id' => 'any', 'title' => $translator->trans('DOCUMENT_ANY_SUBCATEGORY')), '1' => array('id' => '', 'title' => $translator->trans('DOCUMENT_SELECT_SUBCATEGORY'))  ) ));
            $return = array('input' => array('0' => array('id' => "$lastInsertedId", 'title' => $title, 'categoryId' => "$categoryId", 'itemType' => 'DOCS-' . $this->clubId, 'count' => 0, 'bookMarkId' => '', 'type' => 'select', 'filterData' => $filterData, "draggable" => 1)));
        }

        return new JsonResponse($return);
    }

    
    /**
     * Function to update a document
     * 
     * @param Request $request      Request object
     * @param string  $documentType Document type CLUB/CONTACT/TEAM/WORKGROUP
     * @param int     $documentId   Document id to update
     * @param int     $offset       Table offset
     * @param string  $module       Module name file/document
     * 
     * @return JsonResponse
     */
    public function updateDocumentAction(Request $request, $documentType, $documentId, $offset, $module)
    {
        $documentArr = json_decode($request->get('documentArr'), true);
        $fileCheck = new FileChecking($this->container);
        foreach ($documentArr[$documentId]['i18n'] as $key => $value) {
            $documentArr[$documentId]['i18n'][$key]['name'] = $fileCheck->replaceSingleQuotes($value['name']);
        }
        $documentType = strtolower($documentType);
        $clubDetails = array('clubId' => $this->clubId, 'clubType' => $this->clubType, 'clubDefaultLang' => $this->get('club')->get('club_default_lang'), 'clubLanguages' => $this->clubLanguages);
        $this->em->getRepository('CommonUtilityBundle:FgDmDocuments')->saveDocumentDetails($this->container, $documentArr, $documentType, $clubDetails, $this->contactId);
        if ($documentType == 'club') {
            $redirectUrl = ($module == 'files') ? $this->generateUrl('document_settings_club_files', array('documentId' => $documentId, 'offset' => $offset)) : $this->generateUrl('document_settings_club', array('documentId' => $documentId, 'offset' => $offset));
        } elseif ($documentType == 'contact') {
            $redirectUrl = ($module == 'files') ? $this->generateUrl('document_settings_contact_files', array('documentId' => $documentId, 'offset' => $offset)) : $this->generateUrl('document_settings_contact', array('documentId' => $documentId, 'offset' => $offset));
        } elseif ($documentType == 'team') {
            $redirectUrl = ($module == 'files') ? $this->generateUrl('document_settings_team_files', array('documentId' => $documentId, 'offset' => $offset)) : $this->generateUrl('document_settings_team', array('documentId' => $documentId, 'offset' => $offset));
        } elseif ($documentType == 'workgroup') {
            $redirectUrl = ($module == 'files') ? $this->generateUrl('document_settings_workgroup_files', array('documentId' => $documentId, 'offset' => $offset)) : $this->generateUrl('document_settings_workgroup', array('documentId' => $documentId, 'offset' => $offset));
        }

        return new JsonResponse(array('status' => true, 'flash' => $this->get('translator')->trans('DOCUMENT_UPDATE_SUCCESS_MSG'), 'redirect' => $redirectUrl, 'sync' => 1));
    }

    /**
     * Delete old versions of a document
     * 
     * @param Request $request Request object
     * 
     * @return JsonResponse
     */
    public function deleteOldVersionsAction(Request $request)
    {
        $documentId = $request->get('documentId', '0');
        if ($documentId != 0) {
            $this->em->getRepository('CommonUtilityBundle:FgDmDocuments')->deleteOldVersionsOfADocument($documentId, $this->clubId);
        }

        return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('DOCUMENT_DELETE_OLD_VERSIONS_SUCCESS_MESSAGE'), 'noparentload' => 1));
    }

    /**
     * Action to move the document to another category
     * 
     * @param Request $request Request object
     * 
     * @return JsonResponse
     */
    public function updateDocumentAssignAction(Request $request)
    {
        $documentIds = json_decode($request->get('documentId'));
        $documentId = implode(",", $documentIds);
        $dropedCategoryId = $request->get('dropedCategory');
        $dropedSubCategoryId = $request->get('dropedSubCategory');
        $dropValue = $request->get('dropValue');
        $clubId = $this->get('club')->get('id');
        $docType = $request->get('docType');
        $displayCount = count($documentIds);
        $totalCount = $displayCount;
        $objDocumentPdo = new DocumentPdo($this->container);
        $affectedRows = $objDocumentPdo->movedocumentCategory($documentId, $dropedCategoryId, $dropedSubCategoryId, $dropValue, $clubId, $this->contactId, $docType);
        $flashMsg = '%selcount%_OUT_OF_%totalcount%_DOCUMENTS_MOVED_SUCCESSFULLY';
        $jsonResponse = array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans($flashMsg, array('%selcount%' => $affectedRows, '%totalcount%' => $totalCount)));

        return new JsonResponse($jsonResponse);
    }
}
