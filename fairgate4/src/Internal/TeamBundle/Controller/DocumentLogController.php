<?php

/**
 * DocumentLogController
 *
 * This controller was created for managing document log for internal area.
 *
 * @package    ClubadminDocumentsBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
namespace Internal\TeamBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Common\UtilityBundle\Repository\Pdo\DocumentPdo;

/**
 * Manage document log related functionality
 */
class DocumentLogController extends Controller
{

    /**
     * Function to list document log entries
     *
     * @param int $documentId DocumentId
     *
     * @return template
     */
    public function documentLogAction($documentId)
    {
        $club = $this->container->get('club');

        $em = $this->getDoctrine()->getManager();
        //only super admin, club admin, team admin, team document admin have rights to view document log
        $documentDetails = $em->getRepository('CommonUtilityBundle:FgDmDocuments')->getDocumentPermissionDetails($documentId);
        $em->getRepository('CommonUtilityBundle:FgDmDocuments')->checkAdminPermissionForUser($documentDetails, $this->container, $club->get('id'));

        $documentName = (isset($documentDetails['name'])) ? $documentDetails['name'] : '';
        if ($documentType == 'team') {
            $redirect = 'internal_team_document_list';
        } else {
            $redirect = 'internal_workgroup_document_list';
        }
        $dataSet = array('documentId' => $documentId, 'documentName' => $documentName, 'documentType' => $documentType, 'back' => $this->generateUrl($redirect));

        return $this->render('InternalTeamBundle:DocumentLog:documentLog.html.twig', $dataSet);
    }

    /**
     * Function to get Document log entries
     *
     * @param int $documentId DocumentId
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
     * Function to build translation and terminology array
     *
     * @return array $transArr
     */
    private function getTranslations()
    {
        $club = $this->container->get('club');
        $terminologyService = $this->get('fairgate_terminology_service');
        $clubTerminology = $terminologyService->getTerminology('Club', $this->container->getParameter('plural'));
        $subfedTerminology = $terminologyService->getTerminology('Sub-federation', $this->container->getParameter('plural'));
        $teamTerminology = $terminologyService->getTerminology('Team', $this->container->getParameter('plural'));
        $teamTerminologySingular = $terminologyService->getTerminology('Team', $this->container->getParameter('singular'));
        $allClubsText = ($club->get('type') == 'federation') ? $this->get('translator')->trans('LOG_ALL_CLUBS', array('%clubs%' => $clubTerminology)) : (($club->get('type') == 'sub_federation') ? $this->get('translator')->trans('LOG_ALL_SUBFEDS_AND_CLUBS', array('%clubs%' => $clubTerminology, '%subfeds%' => $subfedTerminology)) : '');
        $allClubsExceptText = ($club->get('type') == 'federation') ? $this->get('translator')->trans('LOG_ALL_CLUBS_EXCEPT', array('%clubs%' => $clubTerminology)) : (($club->get('type') == 'sub_federation') ? $this->get('translator')->trans('LOG_ALL_SUBFEDS_AND_CLUBS_EXCEPT', array('%clubs%' => $clubTerminology, '%subfeds%' => $subfedTerminology)) : '');
        $selectionOfClubsText = ($club->get('type') == 'federation') ? $this->get('translator')->trans('LOG_SELECTION_OF_CLUBS', array('%clubs%' => $clubTerminology)) : (($club->get('type') == 'sub_federation') ? $this->get('translator')->trans('LOG_SELECTION_OF_SUBFEDS_AND_CLUBS', array('%clubs%' => $clubTerminology, '%subfeds%' => $subfedTerminology)) : '');
        $transArr = array('added' => $this->get('translator')->trans('LOG_FLAG_ADDED'),
            'removed' => $this->get('translator')->trans('LOG_FLAG_REMOVED'),
            'changed' => $this->get('translator')->trans('LOG_FLAG_CHANGED'),
            'selection_of_clubs' => $selectionOfClubsText,
            'all_clubs' => $allClubsText, 'none' => $this->get('translator')->trans('NONE'),
            'all_clubs_except' => $allClubsExceptText, 'clubs' => $clubTerminology, 'on' => $this->get('translator')->trans('ON'),
            'off' => $this->get('translator')->trans('OFF'), 'teams' => $terminologyService->getTerminology('Team', $this->container->getParameter('plural')),
            'all_teams' => $this->get('translator')->trans('LOG_ALL_TEAMS', array('%teams%' => $teamTerminology)), 'workgroups' => $this->get('translator')->trans('WORKGROUPS'),
            'all_workgroups' => $this->get('translator')->trans('ALL_WORKGROUPS'), 'team' => $this->get('translator')->trans('LOG_TEAM_CONTACT_AND_ADMINS', array('%team%' => $teamTerminologySingular)), 'team_functions' => $this->get('translator')->trans('LOG_TEAM_ADMINS_AND_SPECIFIC_TEAM_FUNCTIONS', array('%team%' => $teamTerminologySingular)),
            'functions' => $this->get('translator')->trans('LOG_TEAM_FUNCTIONS'), 'team_admin' => $this->get('translator')->trans('LOG_TEAM_ADMIN', array('%team%' => $teamTerminologySingular)), 'club_contact_admin' => $this->get('translator')->trans('LOG_CLUB_AND_DOCUMENT_ADMIN'),
            'contacts' => $this->get('translator')->trans('LOG_CONTACTS'), 'workgroup' => $this->get('translator')->trans('LOG_WORKGROUP_CONTACTS_AND_ADMINS'),
            'workgroup_admin' => $this->get('translator')->trans('LOG_WORKGROUP_ADMINS'), 'main_document_admin' => $this->get('translator')->trans('LOG_MAIN_AND_DOCUMENT_ADMINS'),
            'Document name' => $this->get('translator')->trans('LOG_DOCUMENT_NAME'), 'Category' => $this->get('translator')->trans('LOG_CATEGORY'), 'Deposited with' => $this->get('translator')->trans('LOG_DEPOSITED_WITH'),
            'Description' => $this->get('translator')->trans('LOG_DESCRIPTION'), 'Author' => $this->get('translator')->trans('LOG_AUTHOR'), 'Visible for contacts' => $this->get('translator')->trans('LOG_VISIBLE_FOR_CONTACTS'),
            'Filter' => $this->get('translator')->trans('LOG_FILTER'), 'Included contacts' => $this->get('translator')->trans('LOG_INCLUDED_CONTACTS'), 'Excluded contacts' => $this->get('translator')->trans('LOG_EXCLUDED_CONTACTS')
        );

        return $transArr;
    }
}
