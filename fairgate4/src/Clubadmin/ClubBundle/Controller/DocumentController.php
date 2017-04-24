<?php

namespace Clubadmin\ClubBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Clubadmin\DocumentsBundle\Util\Documentlist;
use Clubadmin\DocumentsBundle\Util\Documenttablesetting;
use Clubadmin\DocumentsBundle\Util\Documentdatatable;
use Common\UtilityBundle\Util\FgUtility;
use Clubadmin\ClubBundle\Util\NextpreviousClub;
use Common\UtilityBundle\Repository\Pdo\DocumentPdo;

/**
 * DocumentController
 *
 * This controller was created for managing documents.
 *
 * @package    ClubadminClubBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
class DocumentController extends FgController
{

    private $tabledatas;
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
             $permissionObj->checkClubAccess(0,"backend_club");
        }

    }

   /**
    * Function is used to display club documents
    *
    * @param int $offset Document list offset value
    * @param int $clubId Current club id
    * 
    * @return template
    */
    public function listDocumentsAction($offset,$clubId)
    {
        $club = $this->get('club');
        $isDocumentModuleBooked = (in_array('document', $club->get('bookedModulesDet'))) ? 1 : 0;
        $hasRights = in_array('document', $club->get('allowedRights')) ? 1 : 0;
        //check if clubId has access
        $clubPdo = new \Common\UtilityBundle\Repository\Pdo\ClubPdo($this->container);
        $sublevelclubs = $clubPdo->getAllSubLevelData($this->clubId);
        $sublevelclub = array();
        foreach ($sublevelclubs as $key => $value) {
            if ($clubId == $value['id']) {
                $sublevelclub[$key] = $value['id'];
                $sublevelclub['is_sub_federation'] = $value['is_sub_federation'];
            }
        }
        
        //security check
        if (!in_array($clubId, $sublevelclub) || !$isDocumentModuleBooked || !$hasRights) {
             $permissionObj = $this->fgpermission;
             $permissionObj->checkClubAccess(0,"backend_club");
        }
        //end check if clubid has access

        $breadCrumb = array('back' => '#');
        $nextprevious = new NextpreviousClub($this->container);
        $nextPreviousResultset = $nextprevious->nextPreviousClubData($this->contactId,$clubId, $offset, 'club_documents', 'offset', 'clubId', $flag = 0);
        $clubName = $this->em->getRepository('CommonUtilityBundle:FgClub')->getClubname($clubId, $club->get('default_lang'));
        $documentCatDetails = $this->em->getRepository('CommonUtilityBundle:FgDmDocumentCategory')->getDocumentSubCategories($this->clubId, 'CLUB', $this->clubDefaultLang);
        $documentsCount = $this->em->getRepository('CommonUtilityBundle:FgDmDocuments')->getCountOfAssignedClubDocuments('CLUB', $this->clubId, $clubId,$this->container);
        $assignmentCount = $this->em->getRepository('CommonUtilityBundle:FgClubClassAssignment')->assignmentCount($this->clubType, $this->conn, $clubId);
        $notesCount = $this->em->getRepository('CommonUtilityBundle:FgClubNotes')->getNotesCount($clubId, $this->clubId);
        if(in_array('document', $club->get('bookedModulesDet'))){
            $tabs = array(0 =>"overview", 1 =>"data", 2=>"assignment", 3=> "note", 4=>"document", 5=>"log"); 
        }else {
            $tabs = array(0 =>"overview", 1 =>"data", 2=>"assignment", 3=> "note", 4=>"log");
        }
       
        $contCountDetails['asgmntsCount'] = $assignmentCount;
        $contCountDetails['documentsCount'] = $documentsCount;
        $contCountDetails['notesCount'] =  $notesCount;
        $tabDetails = FgUtility::getTabsArrayDetails($this->container, $tabs, $offset, $clubId, $contCountDetails, "document","club");
        
        return $this->render('ClubadminClubBundle:Document:index.html.twig', array('breadCrumb' => $breadCrumb,'subclubs' => $sublevelclubs,'bookedModulesDet' => $this->get('club')->get('bookedModulesDet'),'docCategory'=>$documentCatDetails,'contactId' => $this->contactId,'contactName'=>$this->contactName,'clubId' =>$clubId,'clubName' => $clubName[0]['title'],'offset' => $offset,'clubType' => $this->clubType,'nextPreviousResultset'=>$nextPreviousResultset,'clubName' => $clubName[0]['title'],'clubDefaultLang' => $this->clubDefaultLang, 'documentsCount' => $documentsCount,'asgmntsCount'=>$assignmentCount,'notesCount'=>$notesCount, 'tabs'=> $tabDetails));
    }

    /**
    * Function to list all documents of club
    * 
    * @param $request request Object
    * 
    * @return json
    */
    public function clubDocumentListingAjaxAction(Request $request)
    {
        $clubId = $this->clubId;        
        $clubHeirarchy = array($clubId);
        $language = $this->clubDefaultLang;
        $assignedClubId = $request->get('clubId');
        $aColumns = $this->getTableColumns($assignedClubId);
        $mysqldate = $this->get('club')->get('mysqldate');        
        
        $documentlistClass = new Documentlist($this->container, "CLUB");
        $documentPdo = new DocumentPdo($this->container);        
        $totallistquery = $documentPdo->getQueryForClubDocuments($aColumns, $mysqldate, $documentlistClass, $assignedClubId, $clubHeirarchy, $language);
        $documents = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($totallistquery);
        $i = 0;
        foreach($documents as $document) {
            $url = $this->generateUrl('document_settings_club', array('documentId' => $document['documentId'], "offset" => 0));
            $documents[$i]['editPath'] = $url;
            $documents[$i]['fedicon'] = FgUtility::getClubLogo($document['club_id'], $this->em);;
            $i++;
        }
        //iterate the result
        $documentDatatabledata = new Documentdatatable($this->container);
        $documents = $documentDatatabledata->iterateDataTableData($documents);
        $aaDataType = $this->getContactFieldDetails($this->tabledatas);
        $return=json_encode(array("aaData" => $documents, "iTotalRecords" => count($documents), "iTotalDisplayRecords" => count($documents), "aaDataType" => $aaDataType, "start" => 0 ));
        
        return new Response($return,200,array('Content-Type'=>'application/json'));
    }

    /**
     * Function to get document list table columns
     * 
     * @param int $assignedClub Document assigned club id
     *
     * @return array
     */
    public function getTableColumns($assignedClub) {
        $cols='{"1":{"id":"CATEGORY","type":"FO","name":"CL_FO_CATEGORY"},"2":{"id":"SIZE","type":"FO","name":"CL_FO_SIZE"},"3":{"id":"LAST_UPDATED","type":"DO","name":"CL_DO_LAST_UPDATED"},"4":{"id":"AUTHOR","type":"UO","name":"CL_UO_AUTHOR"},"5":{"id":"DEPOSITED_WITH_ASSIGNED","type":"FO","name":"CL_FO_DEPOSITED_WITH_FOR_ASSIGNED"}}';
        $this->tabledatas = json_decode($cols, true);

        if (is_array($this->tabledatas) && count($this->tabledatas) > 0) {
            $table = new Documenttablesetting($this->container, $this->tabledatas, $this->get('club'), 'CLUB', $assignedClub);
            $aColumns = $table->getDocColumns();
        } else {
            $aColumns = array();
       }

        return $aColumns;
    }

    /**
    * Function to assign documents to club
    *
    * @param $request request object
    *
    * @return json string of success
    */
    public function clubDocumentAddAjaxAction(Request $request)
    {
        $currentClubId = $request->get('clubId');
        $documentId = $request->get('documentId');
        $this->em->getRepository('CommonUtilityBundle:FgDmAssigment')->addClubDocumentAssignment($currentClubId, $documentId, $this->contactId);

        return new Response(json_encode("success"),200,array('Content-Type'=>'application/json'));
    }

   /**
    * Function to remove document assignment of club
    *
    * @param $request request objest
    *
    * @return json string of success
    */
    public function clubDocumentRemoveAjaxAction(Request $request)
    {
        $documentId = $request->get('assignmentId');
        $removeclub = $request->get('removeclub');
        $clubId = $request->get('clubId');
        if ($removeclub === "all") {
            $this->em->getRepository('CommonUtilityBundle:FgDmAssigment')->removeAllClubDocumentAssignments($clubId, $this->clubId, $this->container, $this->contactId);
        } else {
            $this->em->getRepository('CommonUtilityBundle:FgDmAssigment')->removeDocumentAssignmentOfClubs($documentId, $clubId, $this->contactId);
        }

        return new Response(json_encode("success"),200,array('Content-Type'=>'application/json'));
    }

    /**
    * Function to show delete popup
    * 
    * @param $request request
    * 
    * @return json string of success
    */
    public function clubDocumentShowDeletePopupAction(Request $request)
    {
        $assignmentId = $request->get('assignmentId');
        $removeclub = $request->get('removeclub');
        $clubId = $request->get('clubId');
        $titleText = $this->get('translator')->trans('CONTACTDOCUMENT_DELETE_HEADER');
        $deleteDesc = 'CONTACTDOCUMENT_DELETE_MESSAGE';
        $return = array( 'deleteDesc' => $this->get('translator')->trans($deleteDesc), 'titleText' => $titleText,
            'assignmentId' => $assignmentId, 'removeclub' => $removeclub, 'clubId' => $clubId);
        
        return $this->render('ClubadminClubBundle:Document:confirmDelete.html.twig', $return);
    }


    /**
    * Function to list other documents which are not assigned to club
    * used in autocomplete field
    * 
    * @param $request request object
    * 
    * @return json
    */
    public function GetOtherExistingClubDocsAjaxAction(Request $request)
    {
        $language = $this->clubDefaultLang;
        $currentClubId = $request->get('clubId');
        $key = FgUtility::getSecuredData($request->get('term'), $this->conn);
        $docPdo = new DocumentPdo($this->container);
        $otherExistingDocuments = $docPdo->getOtherExistingClubDocuments($currentClubId, $this->clubId, $language, $key);
        $return =  json_encode($otherExistingDocuments);
        
        return new Response($return,200,array('Content-Type'=>'application/json'));
    }

     /**
     * For get the type of selected contact fields
     * 
     *
     * @return array
     */
    private function getContactFieldDetails() {
        $output = array();
        $output[] = array("title" => 'docname', "type" => "docname");
        $output[] = array("title" => 'edit', "type" => "edit");
        //club
        $output[] = array("title" => 'CL_FO_SIZE', "type" => "CL_FO_SIZE");
        $output[] = array("title" => 'CL_FO_VISIBLE_TO', "type" => "CL_FO_VISIBLE_TO");
        $output[] = array("title" => 'CL_FO_DESCRIPTION', "type" => "CL_FO_DESCRIPTION");
        $output[] = array("title" => 'CL_FO_DEPOSITED_WITH', "type" => "CL_FO_DEPOSITED_WITH");
        $output[] = array("title" => 'CL_FO_DEPOSITED_WITH_FOR_ASSIGNED', "type" => "CL_FO_DEPOSITED_WITH_FOR_ASSIGNED");
        $output[] = array("title" => 'CL_FO_ISPUBLIC', "type" => "CL_FO_ISPUBLIC");
        //contact
        $output[] = array("title" => 'CO_FO_SIZE', "type" => "CO_FO_SIZE");
        $output[] = array("title" => 'CO_FO_DESCRIPTION', "type" => "CO_FO_DESCRIPTION");
        $output[] = array("title" => 'CO_FO_VISIBLE_TO', "type" => "CO_FO_VISIBLE_TO");
        $output[] = array("title" => 'CO_FO_DEPOSITED_WITH', "type" => "CO_FO_DEPOSITED_WITH");
        $output[] = array("title" => 'CO_FO_DEPOSITED_WITH_FOR_ASSIGNED', "type" => "CO_FO_DEPOSITED_WITH_FOR_ASSIGNED");
        $output[] = array("title" => 'CO_FO_ISPUBLIC', "type" => "CO_FO_ISPUBLIC");
        //workgroup
        $output[] = array("title" => 'WG_FO_SIZE', "type" => "WG_FO_SIZE");
        $output[] = array("title" => 'WG_FO_DEPOSITED_WITH', "type" => "WG_FO_DEPOSITED_WITH");
        $output[] = array("title" => 'WG_FO_VISIBLE_TO', "type" => "WG_FO_VISIBLE_TO");
        $output[] = array("title" => 'WG_FO_DESCRIPTION', "type" => "WG_FO_DESCRIPTION");
        $output[] = array("title" => 'WG_FO_ISPUBLIC', "type" => "WG_FO_ISPUBLIC");
        //team
        $output[] = array("title" => 'T_FO_VISIBLE_TO', "type" => "T_FO_VISIBLE_TO");
        $output[] = array("title" => 'T_FO_SIZE', "type" => "T_FO_SIZE");
        $output[] = array("title" => 'T_FO_DEPOSITED_WITH', "type" => "T_FO_DEPOSITED_WITH");
        $output[] = array("title" => 'T_FO_DESCRIPTION', "type" => "T_FO_DESCRIPTION");
        $output[] = array("title" => 'visibleFor', "type" => "visibleFor");
        $output[] = array("title" => 'IsPublic', "type" => "IsPublic");

        return $output;
    }
}
