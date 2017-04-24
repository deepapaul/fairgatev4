<?php

namespace Clubadmin\ClubBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Clubadmin\ClubBundle\Util\NextpreviousClub;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Repository\Pdo\ClubPdo;

/**
 * Clublog Controller
 *
 * @package    ClubadminClubBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
class LogController extends FgController
{

    /**
     * Pre execute function to allow access to federation and subfed clubs only
     * 
     * @return Exception
     */
    public function preExecute()
    {
        parent::preExecute();

        if ($this->clubType != 'federation' && $this->clubType != 'sub_federation') {
            $permissionObj = $this->fgpermission;
            $permissionObj->checkClubAccess(0, "backend_club");
        }
    }

    /**
     * Function for listing club log entries
     * @param int $offset
     * @param int $clubId
     * 
     * @return type
     */
    public function indexAction($offset, $clubId)
    {
        $clubPdo = new ClubPdo($this->container);
        $sublevelclubs = $clubPdo->getAllSubLevelData($this->clubId);
        $sublevelclub = array();
        foreach ($sublevelclubs as $key => $value) {
            if ($clubId == $value['id']) {
                $sublevelclub[$key] = $value['id'];
                $sublevelclub['is_sub_federation'] = $value['is_sub_federation'];
            }
        }
        //security check
        if (!in_array($clubId, $sublevelclub)) {
            $permissionObj = $this->fgpermission;
            $permissionObj->checkClubAccess(0, "backend_club");
//            throw $this->createNotFoundException($this->clubTitle . ' has no access to this page');
        }
        $breadcrumb = array('back' => '#');
        $clubData = $clubPdo->getClubData($clubId);
        $club = $this->get('club');
        $nextprevious = new NextpreviousClub($this->container);
        $nextPreviousResultset = $nextprevious->nextPreviousClubData($this->contactId, $clubId, $offset, 'club_log', 'offset', 'clubId', $flag = 0);
        $logTabs = array(1 => 'data', 2 => 'classifications', 3 => 'notes');
        $transKindFields = array('data' => 'LOG_DATA_FIELDS', 'classifications' => 'LOG_CLASSIFICATIONS', 'notes' => 'GN_NOTES', 'added' => 'LOG_FLAG_ADDED', 'removed' => 'LOG_FLAG_REMOVED', 'changed' => 'LOG_FLAG_CHANGED');
        $logEntriesDataFieldTab = $clubPdo->getDataFieldLogEntries($clubId, $this->clubId);
        $logEntriesClassificationTab = $clubPdo->getClassificationLogEntries($clubId, $this->clubType, $this->clubId);
        $logEntriesNotesTab = $clubPdo->getNotesLogEntries($clubId, $this->clubId);

        foreach ($logEntriesNotesTab as $key => $noteFields) {
            $noteFields['value_after'] = str_replace("\"", "#~#", $noteFields['valueAfter']);
            $noteFields['value_before'] = str_replace("\"", "#~#", $noteFields['valueBefore']);
            $noteFields['value_after'] = str_replace("<", "#~~#", $noteFields['value_after']);
            $noteFields['value_before'] = str_replace("<", "#~~#", $noteFields['value_before']);
            $logEntriesNotesTab[$key]['value_before'] = htmlentities($noteFields['value_before'], ENT_COMPAT, "UTF-8");
            $logEntriesNotesTab[$key]['value_after'] = htmlentities($noteFields['value_after'], ENT_COMPAT, "UTF-8");
        }
        $logEntries = array('data' => $logEntriesDataFieldTab, 'classifications' => $logEntriesClassificationTab, 'notes' => $logEntriesNotesTab);
        $activeTab = '1';
        $dataSet = array('logEntries' => $logEntries, 'breadcrumb' => $breadcrumb, 'clubName' => $clubData['title'], 'clubId' => $clubId, 'offset' => $offset, 'nextPreviousResultset' => $nextPreviousResultset, 'transKindFields' => $transKindFields, 'logTabs' => $logTabs, 'activeTab' => $activeTab);
        $dataSet['documentsCount'] = $this->em->getRepository('CommonUtilityBundle:FgDmDocuments')->getCountOfAssignedClubDocuments('CLUB', $this->clubId, $clubId, $this->container);
        $dataSet['asgmntsCount'] = $this->em->getRepository('CommonUtilityBundle:FgClubClassAssignment')->assignmentCount($this->clubType, $this->conn, $clubId);
        $dataSet['notesCount'] = $this->em->getRepository('CommonUtilityBundle:FgClubNotes')->getNotesCount($clubId, $this->clubId);
        if (in_array('document', $club->get('bookedModulesDet'))) {
            $tabs = array(0 => "overview", 1 => "data", 2 => "assignment", 3 => "note", 4 => "document", 5 => "log");
        } else {
            $tabs = array(0 => "overview", 1 => "data", 2 => "assignment", 3 => "note", 4 => "log");
        }

        $contCountDetails['asgmntsCount'] = $dataSet['asgmntsCount'];
        $contCountDetails['documentsCount'] = $dataSet['documentsCount'];
        $contCountDetails['notesCount'] = $dataSet['notesCount'];
        $dataSet['tabs'] = FgUtility::getTabsArrayDetails($this->container, $tabs, $offset, $clubId, $contCountDetails, "log", "club");

        return $this->render('ClubadminClubBundle:Log:index.html.twig', $dataSet);
    }
}
