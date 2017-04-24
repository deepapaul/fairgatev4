<?php

/**
 * DocumentLogController.
 *
 * This controller was created for managing document log entries.
 *
 * @author     pitsolutions.ch
 *
 * @version    Fairgate V4
 */
namespace Clubadmin\DocumentsBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Common\UtilityBundle\Repository\Pdo\DocumentPdo;

/**
 * Manage document log related functionality.
 */
class DocumentLogController extends FgController
{

    /**
     * Function to list document log entries.
     *
     * @param int $documentId DocumentId
     *
     * @return template
     */
    public function documentLogAction($documentId)
    {
        $documentDetails = $this->em->getRepository('CommonUtilityBundle:FgDmDocuments')->getDocumentPermissionDetails($documentId);
        $permissionObj = $this->fgpermission;
        $accessCheck = ($this->clubId != $documentDetails['clubId']) ? 0 : 1;
        $permissionObj->checkClubAccess($accessCheck, 'backend_document_edit');
        $documentName = ($documentDetails['name']) ? $documentDetails['name'] : '';
        $documentType = ($documentDetails['documentType']) ? strtolower($documentDetails['documentType']) : 'club';
        if ($documentType == 'team') {
            $redirect = 'team_documents_listing';
        } elseif ($documentType == 'workgroup') {
            $redirect = 'workgroup_documents_listing';
        } elseif ($documentType == 'contact') {
            $redirect = 'contact_documents_listing';
        } else {
            $redirect = 'club_documents_listing';
        }
        $dataSet = array('documentId' => $documentId, 'documentName' => $documentName, 'documentType' => $documentType, 'back' => $this->generateUrl($redirect));

        return $this->render('ClubadminDocumentsBundle:DocumentLog:documentLog.html.twig', $dataSet);
    }

    /**
     * Function to get Document log entries of a document.
     *
     * @param int $documentId Document Id to of log entries 
     *
     * @return JsonResponse
     */
    public function getDocumentLogEntriesAction($documentId)
    {
        $docObj = new DocumentPdo($this->container);
        $logEntries = $docObj->getDocumentLogEntries($documentId);
        if (count($logEntries) > 0) {
            $transArr = $this->getTranslations();
            $logEntries[0]['transArr'] = $transArr;
        }
        $return['aaData'] = $logEntries;

        return new JsonResponse($return);
    }

    /**
     * Function to build translation and terminology array.
     *
     * @return array $transArr
     */
    private function getTranslations()
    {
        $terminologyService = $this->get('fairgate_terminology_service');
        $clubTerminology = $terminologyService->getTerminology('Club', $this->container->getParameter('plural'));
        $subfedTerminology = $terminologyService->getTerminology('Sub-federation', $this->container->getParameter('plural'));
        $teamTerminology = $terminologyService->getTerminology('Team', $this->container->getParameter('plural'));
        $teamTerminologySingular = $terminologyService->getTerminology('Team', $this->container->getParameter('singular'));
        $allClubsText = ($this->clubType == 'federation') ? $this->get('translator')->trans('LOG_ALL_CLUBS', array('%clubs%' => $clubTerminology)) : (($this->clubType == 'sub_federation') ? $this->get('translator')->trans('LOG_ALL_SUBFEDS_AND_CLUBS', array('%clubs%' => $clubTerminology, '%subfeds%' => $subfedTerminology)) : '');
        $allClubsExceptText = ($this->clubType == 'federation') ? $this->get('translator')->trans('LOG_ALL_CLUBS_EXCEPT', array('%clubs%' => $clubTerminology)) : (($this->clubType == 'sub_federation') ? $this->get('translator')->trans('LOG_ALL_SUBFEDS_AND_CLUBS_EXCEPT', array('%clubs%' => $clubTerminology, '%subfeds%' => $subfedTerminology)) : '');
        $selectionOfClubsText = ($this->clubType == 'federation') ? $this->get('translator')->trans('LOG_SELECTION_OF_CLUBS', array('%clubs%' => $clubTerminology)) : (($this->clubType == 'sub_federation') ? $this->get('translator')->trans('LOG_SELECTION_OF_SUBFEDS_AND_CLUBS', array('%clubs%' => $clubTerminology, '%subfeds%' => $subfedTerminology)) : '');
        $transArr = array('added' => $this->get('translator')->trans('LOG_FLAG_ADDED'), 'removed' => $this->get('translator')->trans('LOG_FLAG_REMOVED'), 'changed' => $this->get('translator')->trans('LOG_FLAG_CHANGED'),
            'selection_of_clubs' => $selectionOfClubsText, 'all_clubs' => $allClubsText, 'none' => $this->get('translator')->trans('NONE'), 'all_clubs_except' => $allClubsExceptText, 'clubs' => $clubTerminology, 'on' => $this->get('translator')->trans('ON'),
            'off' => $this->get('translator')->trans('OFF'), 'teams' => $terminologyService->getTerminology('Team', $this->container->getParameter('plural')),
            'all_teams' => $this->get('translator')->trans('LOG_ALL_TEAMS', array('%teams%' => $teamTerminology)), 'workgroups' => $this->get('translator')->trans('WORKGROUPS'),
            'all_workgroups' => $this->get('translator')->trans('ALL_WORKGROUPS'), 'team' => $this->get('translator')->trans('LOG_TEAM_CONTACT_AND_ADMINS', array('%team%' => $teamTerminologySingular)), 'team_functions' => $this->get('translator')->trans('LOG_TEAM_ADMINS_AND_SPECIFIC_TEAM_FUNCTIONS', array('%team%' => $teamTerminologySingular)),
            'functions' => $this->get('translator')->trans('LOG_TEAM_FUNCTIONS'), 'team_admin' => $this->get('translator')->trans('LOG_TEAM_ADMIN', array('%team%' => $teamTerminologySingular)), 'club_contact_admin' => $this->get('translator')->trans('LOG_CLUB_AND_DOCUMENT_ADMIN'),
            'contacts' => $this->get('translator')->trans('LOG_CONTACTS'), 'workgroup' => $this->get('translator')->trans('LOG_WORKGROUP_CONTACTS_AND_ADMINS'),
            'workgroup_admin' => $this->get('translator')->trans('LOG_WORKGROUP_ADMINS'), 'main_document_admin' => $this->get('translator')->trans('LOG_MAIN_AND_DOCUMENT_ADMINS'),
            'Document name' => $this->get('translator')->trans('LOG_DOCUMENT_NAME'), 'Category' => $this->get('translator')->trans('LOG_CATEGORY'), 'Deposited with' => $this->get('translator')->trans('LOG_DEPOSITED_WITH'),
            'Description' => $this->get('translator')->trans('LOG_DESCRIPTION'), 'Author' => $this->get('translator')->trans('LOG_AUTHOR'), 'Visible for contacts' => $this->get('translator')->trans('LOG_VISIBLE_FOR_CONTACTS'),
            'Filter' => $this->get('translator')->trans('LOG_FILTER'), 'Included contacts' => $this->get('translator')->trans('LOG_INCLUDED_CONTACTS'), 'Excluded contacts' => $this->get('translator')->trans('LOG_EXCLUDED_CONTACTS') , 'isPublic'=>$this->get('translator')->trans('DM_PUBLIC_VISIBILITY'), );

        return $transArr;
    }
}
