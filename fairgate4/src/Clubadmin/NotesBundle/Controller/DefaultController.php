<?php

/**
 * DefaultController
 *
 * This controller was created for handling notes section of contact
 *
 * @package    ClubadminNotesBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */

namespace Clubadmin\NotesBundle\Controller;

use Clubadmin\NotesBundle\Form\contactnoteform;
use Common\UtilityBundle\Controller\FgController as ParentController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Clubadmin\Util\Contactlist;
use Clubadmin\NotesBundle\Form\clubnoteForm;
use Clubadmin\ContactBundle\Util\ContactDetailsAccess;
use Clubadmin\NotesBundle\Form\sponsorcontactnoteform;
use Clubadmin\ClubBundle\Util\NextpreviousClub;
use Clubadmin\ContactBundle\Util\NextpreviousContact;
use Clubadmin\SponsorBundle\Util\NextpreviousSponsor;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Util\FgPermissions;
use Symfony\Component\HttpFoundation\Request;
use Common\UtilityBundle\Repository\Pdo\ClubPdo;
use Admin\UtilityBundle\Classes\SyncFgadmin;

/**
 * DefaultController used for managing contact and club  notes
 *
 * @author pit solutions <pitsolutions.ch>
 */
class DefaultController extends ParentController
{

    /**
     * function to do the execute
     */
    public function preExecute()
    {
        parent::preExecute();
    }

    /**
     * function to do the list the of contact notes
     * 
     * @param Request $request   Request Object
     * @param int     $offset    Offset
     * @param int     $contactid Contact Id
     * @param string  $module    Module name
     * 
     * @return html
     */
    public function contactnoteAction(Request $request, $offset, $contactid, $module = 'contact')
    {
        $accessObj = new ContactDetailsAccess($contactid, $this->container);
        if ($accessObj->accessType == 'NO_ACCESS' || !in_array('note', $accessObj->tabArray)) {
            $fgpermission = new FgPermissions($this->container);
            $fgpermission->checkClubAccess('','backned_contact_note');
        }
        
        $contactType = $accessObj->contactviewType;
        $contactMenuModule = $accessObj->menuType;
        if ($contactMenuModule == 'archive') {
            $this->get('club')->set('moduleMenu', 'contactarchive');
        } else if ($contactMenuModule == 'formerfederationmember') {
            $this->get('club')->set('moduleMenu', 'formerfederationmember');
        }
        $this->session->set('contactType', $contactType);
        $clubId = $this->clubId;
        $contactDetails = $this->contactDetails($contactid, $contactType);
        $contactModuleType = $this->get('club')->get('moduleMenu');
        $commonDetails = $this->contactNotesCommmonSettings($contactid, $clubId, $contactType, $request);
        $form = $this->createForm(contactnoteform::class, array('type' => $contactType), array('custom_value' => array('formsize' => $commonDetails['notesDetails']))); 
        // Generating next and previous data for the next-previous functionality in the overview page
        $nextprevious = new NextpreviousContact($this->container);
        $nextPreviousResultset = $nextprevious->nextPreviousContactData($this->contactId, $contactid, $offset, 'contact_note', 'offset', 'contactid', $flag = 0);
        $return = array('form' => $form->createView(), 'count' => $commonDetails['notestotalCount'], 'limit' => $commonDetails['limit'], 'notesDetails' => $commonDetails['notesDetails'], 'loginnames' => $commonDetails['loginnames'], 'contactname' => $commonDetails['contactname'], 'pages' => $commonDetails['pages'], 'contactid' => $contactid, 'clubId' => $clubId, 'displayContactName' => $contactDetails['contactname'], 'contactName' => $contactDetails['contactName'], 'nextPreviousResultset' => $nextPreviousResultset, 'offset' => $offset, 'type' => $contactType, 'contactModuleType'=> $contactModuleType);

        // Get Connection, Assignments, Notes count of a Contact.
        $return['notesCount'] = $commonDetails['notestotalCount'];
        $isCompany = $contactDetails['is_company'];
        $getAsgmntCount = ($contactType == 'archive' or $contactType == 'archivedsponsor') ? false : true;
        $contCountDetails = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getContactCountDetails($this->clubId, $contactid, $isCompany, $this->clubType, true, $getAsgmntCount, false, false, false, false, false, $this->federationId, $this->subFederationId);
        $return = array_merge($return, $contCountDetails);
        $return['documentsCount'] = $this->em->getRepository('CommonUtilityBundle:FgDmAssigment')->getCountOfAssignedDocuments('CONTACT', $this->clubId, $contactid);
       
        $contCountDetails['notesCount'] = $commonDetails['notestotalCount']; 
        $contCountDetails['documentsCount'] = $return['documentsCount'];
        $return['tabs'] = FgUtility::getTabsArrayDetails($this->container, $accessObj->tabArray, $offset, $contactid, $contCountDetails, "note", "contact");
      
        $return['hasUserRights'] = $commonDetails['hasUserRights'];
        $missingReqAssgn = $this->em->getRepository('CommonUtilityBundle:FgRmCategory')->missingReqFedAssign($contactid, $this->clubId, $this->federationId, $this->subFederationId, $this->clubType, $this->clubDefaultLang, $this->conn);
        $return['missingReqAssgment'] = $missingReqAssgn;
        $return['pageViewType'] = $commonDetails['pageViewType'];
        $return['module'] = $module;  
        $contactType = ($contactDetails['is_permanent_delete']==1) ? 'formerfederationmember' :$contactType;
        $return['contactType'] = $contactType;
        $return['breadCrumb'] = array('breadcrumb_data' => array(),'back' => ($contactModuleType === 'contactarchive') ? $this->generateUrl('archive_index') : $this->generateUrl('contact_index'));
        $return['isReadOnlyContact'] = $this->isReadOnlyContact();
        
        return $this->render('NotesBundle:note:' . $commonDetails['view'], $return);
    }
    
    /**
     * Method to get readonly status of current contact
     * 
     * @return boolean $isReadOnlyContact
     */
    private function isReadOnlyContact() {
        $allowedModules = $this->container->get('contact')->get('allowedModules');
        if(in_array('readonly_contact', $allowedModules) && !in_array('contact', $allowedModules)) {
            $isReadOnlyContact = 1;
        } else {
            $isReadOnlyContact = 0;
        }       
        
        return $isReadOnlyContact;
    }

    /**
     * function to get the contact name from contact id
     *
     * @param int $contactid the contact id
     *
     * @return array
     */
    private function contactDetails($contactid, $type = 'contact')
    {
        $club = $this->get('club');
        $type ='noCondition';
        $contactlistClass = new Contactlist($this->container, '', $club, $type);
        $contactlistClass->setColumns(array('contactName', 'contactname', 'contactid', 'clubId', 'is_company','fg_cm_contact.is_permanent_delete'));
        $contactlistClass->setFrom('*');
        $contactlistClass->setCondition();
        $sWhere = " fg_cm_contact.id=$contactid";
        $contactlistClass->addCondition($sWhere);
        $listquery = $contactlistClass->getResult();
        $fieldsArray = $this->conn->fetchAll($listquery);

        return $fieldsArray[0];
    }

    /**
     * function to display the club notes
     * 
     * @param Request $request Request Object
     * @param int     $offset  Offset value
     * @param int     $clubid  Club id
     * 
     * @return html
     */
    public function clubnotesAction(Request $request, $offset, $clubid)
    {
        $clubmodule = $this->get('club')->get('module');
        if (($this->clubType != 'federation' && $this->clubType != 'sub_federation') || $clubmodule != 'club') {
//            throw $this->createNotFoundException($this->get('translator')->trans('%CLUBNAME%_HAVE_NO_ACCESS_TO_PAGE', array('%CLUBNAME%' => $this->clubTitle)));
              $permissionObj = $this->fgpermission;
              $permissionObj->checkClubAccess(0,"backned_contact_note");
            }
        $breadCrumb = array('back' => $this->generateUrl('club_homepage'));
        //For handling  pagination
        $assignmentCount = $this->adminEntityManager->getRepository('AdminUtilityBundle:FgClubClassAssignment')->assignmentCount($this->clubType, $this->conn, $clubid);

        $limit = $this->container->getParameter('pagelimit');
        $notestotalCount = $this->em->getRepository('CommonUtilityBundle:FgClubNotes')->getNotesCount($clubid, $this->clubId);
        $pages = ceil($notestotalCount / $limit);
        if ($request->get('page')) {
            $currentpage = $request->get('page');
            $noteOffset = ($currentpage - 1) * $limit;
            $view = 'notepagination.html.twig';
        } else {
            $noteOffset = $this->container->getParameter('start_offset');
            $view = 'clubnote.html.twig';
        }
        $club = $this->get('club');
        $clubname = $this->adminEntityManager->getRepository('AdminUtilityBundle:FgClub')->getClubname($clubid, $club->get('default_lang'));
        $clubName = $clubname[0]['title'];
        $notesDetails = $this->em->getRepository('CommonUtilityBundle:FgClubNotes')->getNotesDetails($noteOffset, $limit, $clubid, $this->clubId);
        
        $nextprevious = new NextpreviousClub($this->container);
        $nextPreviousResultset = $nextprevious->nextPreviousClubData($this->contactId, $clubid, $offset, 'club_note', 'offset', 'clubid', $flag = 0);

        $form = $this->createForm(clubnoteForm::class, null, array('custom_value' => array('formsize' => $notesDetails)));
        $loginnames = $this->em->getRepository('CommonUtilityBundle:FgCmNotes')->getContactname($this->contactId);
        $documentsCount = $this->em->getRepository('CommonUtilityBundle:FgDmDocuments')->getCountOfAssignedClubDocuments('CLUB', $this->clubId, $clubid, $this->container);
         if(in_array('document', $club->get('bookedModulesDet'))){
            $tabs = array(0 =>"overview", 1 =>"data", 2=>"assignment", 3=> "note", 4=>"document", 5=>"log"); 
        }else {
            $tabs = array(0 =>"overview", 1 =>"data", 2=>"assignment", 3=> "note", 4=>"log");
        }
       
        $contCountDetails['asgmntsCount'] = $assignmentCount;
        $contCountDetails['documentsCount'] = $documentsCount;
        $contCountDetails['notesCount'] =  $notestotalCount;
        $tabDetails = FgUtility::getTabsArrayDetails($this->container, $tabs, $offset, $clubid, $contCountDetails, "note","club");
        return $this->render('NotesBundle:note:' . $view, array('form' => $form->createView(), 'count' => $notestotalCount, 'limit' => $limit, 'notesDetails' => $notesDetails, 'loginnames' => $loginnames, 'pages' => $pages, 'contactid' => $this->contactId, 'clubId' => $clubid, 'offset' => $offset, 'clubName' => $clubName, 'breadCrumb' => $breadCrumb, 'nextPreviousResultset' => $nextPreviousResultset, 'documentsCount' => $documentsCount, 'asgmntsCount' => $assignmentCount, 'tabs'=> $tabDetails));
    }

    /**
     * updateClubNotes function
     * 
     * @param Request $request Request Object
     * @param int     $clubid  Club id
     * 
     * @return JsonResponse
     */
    public function updateClubNotesAction(Request $request, $clubid)
    {
        $log = array();
        if ($request->getMethod() == 'POST') {
            $count = 0;
            $attributes = json_decode($request->request->get('attributes'), true);
            ksort($attributes);
            $clubobj = $this->em->getRepository('CommonUtilityBundle:FgClub')->find($clubid);
            $loginclubobj = $this->em->getRepository('CommonUtilityBundle:FgClub')->find($this->clubId);
            if ($clubobj == "") {
                return new JsonResponse(array('status' => 'ERROR', 'flash' => $this->get('translator')->trans('CLUB_NOTE_ERROR')));
            }
            $fgcreatedcontact = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->find($this->contactId);
            foreach ($attributes as $key => $item) {
                $now = date("Y-m-d H:i:s");
                //find note by id
                $notesObj = $this->em->getRepository('CommonUtilityBundle:FgClubNotes')->find($key);
                if (is_array($item)) {
                    if (array_key_exists('isDeleted', $item)) {
                        $valueBefore = FgUtility::getSecuredDataString($notesObj->getNote(), $this->conn);
                        $this->em->getRepository('CommonUtilityBundle:FgClubNotes')
                                ->deleteNote($notesObj);
                        $log[$count++] = array('note_club_id' => $clubid,'assigned_club_id' => $this->clubId,'type' => 'club','value_before' => $valueBefore,'value_after' => '-');
                    }
                } else {
                    //save new note
                    if ($notesObj == "") {
                        //new note added
                        $this->em->getRepository('CommonUtilityBundle:FgClubNotes')
                                ->addNewNote($item, $clubobj, $fgcreatedcontact, $loginclubobj);
                        $item = FgUtility::getSecuredDataString($item, $this->conn);
                        $log[$count++] = array('note_club_id' => $clubid,'assigned_club_id' => $this->clubId,'type' => 'club','value_before' => '-','value_after' => $item);
                    } else {
                        $valueBefore = FgUtility::getSecuredDataString($notesObj->getNote(), $this->conn);
                        //change in note content updation
                        $this->em->getRepository('CommonUtilityBundle:FgClubNotes')
                                ->updateClubNote($notesObj, $item, $clubobj, $fgcreatedcontact, $loginclubobj);
                        $item = FgUtility::getSecuredDataString($item, $this->conn);
                        $log[$count++] = array('note_club_id' => $clubid,'assigned_club_id' => $this->clubId,'type' => 'club','value_before' => $valueBefore,'value_after' => $item);
                    }
                }
            }

            $this->em->flush();
            $SyncFgadmin = new SyncFgadmin($this->container);
            $SyncFgadmin->syncClubNoteCount($clubid,$this->clubId);
            $this->em->getRepository('CommonUtilityBundle:FgClubNotes')->logEntry($log, 'club', $this->container);

            return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('CLUB_NOTE_SETTINGS_SAVED')));
        }
    }

    /**
     * Function to list the of sponsor contact notes
     * 
     * @param Request $request   Request Object
     * @param int     $offset    Offset
     * @param int     $contactid Contact id
     * @param string  $module    Module name
     * 
     * @return html
     */
    public function sponsornoteAction(Request $request, $offset, $contactid, $module)
    {
        $accessObj = new ContactDetailsAccess($contactid, $this->container, 'sponsor');
        if ($accessObj->accessType == 'NO_ACCESS' || !in_array('note', $accessObj->tabArray)) {
            $this->fgpermission->checkClubAccess('','sponsor_contact_note');
        }
        $isArchiveSponsor = false;
        $contactType = $accessObj->contactviewType;
        $contactMenuModule = $accessObj->menuType;
        if ($contactMenuModule == 'archive') {
            $contactType = 'archivedsponsor';
            $this->get('club')->set('moduleMenu', 'archivedsponsor');
            $this->session->set('contactType', $contactType);
            $isArchiveSponsor = true;
        } else if ($contactMenuModule == 'formerfederationmember') {
            $this->get('club')->set('moduleMenu', 'formerfederationmember');
        }
        $contactModuleType = $this->get('club')->get('moduleMenu');
        $contactViewType = 'sponsor';
        $clubId = $this->clubId;
        $contactDetails = $this->contactDetails($contactid, $contactType);
        $commonDetails = $this->contactNotesCommmonSettings($contactid, $clubId, $contactViewType, $request);
        $form = $this->createForm(sponsorcontactnoteform::class, array('type' => $contactType), array('custom_value' => array('notesData' => $commonDetails['notesDetails'])));
        $returnArray = array('form' => $form->createView(), 'count' => $commonDetails['notestotalCount'], 'limit' => $commonDetails['limit'], 'notesDetails' => $commonDetails['notesDetails'], 'loginnames' => $commonDetails['loginnames'], 'contactname' => $commonDetails['contactname'], 'pages' => $commonDetails['pages'], 'contactid' => $contactid, 'clubId' => $clubId, 'displayContactName' => $contactDetails['contactname'], 'contactName' => $contactDetails['contactName'], 'offset' => $offset, 'type' => $contactType, 'contactModuleType'=> $contactModuleType);
        $isCompany = $contactDetails['is_company'];
        $contCountDetails = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getContactCountDetails($this->clubId, $contactid, $isCompany, $this->clubType, true, true, false, false, false, false, false, $this->federationId, $this->subFederationId);

        // Generating next and previous data for the next-previous functionality in the overview page
        $nextprevious = new NextpreviousSponsor($this->container);
        $nextPreviousResultset = $nextprevious->nextPreviousSponsorData($this->contactId, $contactid, $offset, 'sponsor_note', 'offset', 'contactid', $flag = 0);

        $return = array_merge($returnArray, $contCountDetails);
        $return['notesCount'] = $commonDetails['notestotalCount'];
        $return['nextPreviousResultset'] = $nextPreviousResultset;
        $return['pageViewType'] = $commonDetails['pageViewType'];
        $return['documentsCount'] = $this->em->getRepository('CommonUtilityBundle:FgDmAssigment')->getCountOfAssignedDocuments('CONTACT', $this->clubId, $contactid);
        $return['servicesCount'] = $this->em->getRepository('CommonUtilityBundle:FgSmBookings')->getCountOfSponsorServices($this->clubId, $contactid, $isArchiveSponsor);
        $return['adsCount'] = $this->em->getRepository('CommonUtilityBundle:FgSmSponsorAds')->getCountOfSponsorAds($this->clubId, $contactid);
        $return['module'] = $module;
        $return['contactType'] = $contactType;
        $contCountDetails['notesCount'] = $commonDetails['notestotalCount']; 
        $contCountDetails['documentsCount'] = $return['documentsCount'];
        $contCountDetails['servicesCount'] =  $return['servicesCount'];
        $contCountDetails['adsCount'] =  $return['adsCount'];   
        $return['tabs'] = FgUtility::getTabsArrayDetails($this->container, $accessObj->tabArray, $offset, $contactid, $contCountDetails, "note", "sponsor");
        $return['breadCrumb'] = array('breadcrumb_data' => array(),'back' => ($contactModuleType === 'archivedsponsor') ? $this->generateUrl('view_archived_sponsors') : $this->generateUrl('clubadmin_sponsor_homepage'));

        return $this->render('NotesBundle:note:' . $commonDetails['view'], $return);
    }
    
    /**
     * This function is used to update the contact notes-both sponsor and contact
     * 
     * @param Request $request   Request Object
     * @param int     $contactid Contact id
     * @param int     $clubId    Club id
     * 
     * @return JsonResponse
     */
    public function contactNotesCommonUpdateAction(Request $request, $contactid, $clubId)
    {
        if ($request->getMethod() == 'POST') {
            $attributes = json_decode($request->request->get('attributes'), true);
            ksort($attributes);
            $fgcontact = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->find($contactid);
            if ($fgcontact == "") {
                return new JsonResponse(array('status' => 'ERROR', 'flash' => $this->get('translator')->trans('CONTACT_NOTE_ERROR')));
            }
            $this->em->getRepository('CommonUtilityBundle:FgCmNotes')->saveNotes($contactid, $clubId, $attributes, $this->contactId, $this->container);
            return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('CONTACT_NOTE_SAVED')));
        }
    }

    /**
     * This function that handles common features for both contact and sponsor notes
     *
     * @param int    $contactid   contact id
     * @param int    $clubId      current club id
     * @param string $contactType contact type
     * @param object $request     request object
     *
     * @return response
     */
    public function contactNotesCommmonSettings($contactid, $clubId, $contactType, $request)
    {
        $commonDataArray = array();
        $commonDataArray['loginnames'] = $this->em->getRepository('CommonUtilityBundle:FgCmNotes')->getContactname($this->contactId);
        $commonDataArray['contactname'] = $this->em->getRepository('CommonUtilityBundle:FgCmNotes')->getContactname($contactid);
        $limit = $this->container->getParameter('pagelimit');
        $notestotalCount = $this->em->getRepository('CommonUtilityBundle:FgCmNotes')->getNotesCount($contactid, $clubId);
        $pages = ceil($notestotalCount / $limit);

        if ($request->get('page')) {
            $currentpage = $request->get('page');
            $noteOffset = ($currentpage - 1) * $limit;
            $view = 'notepagination.html.twig';
        } else {
            $noteOffset = $this->container->getParameter('start_offset');
            $view = 'contactnote.html.twig';
        }
        $commonDataArray['limit'] = $limit;
        $commonDataArray['pages'] = $pages;
        $commonDataArray['notestotalCount'] = $notestotalCount;
        $commonDataArray['view'] = $view;
        $commonDataArray['pageViewType'] = $contactType;
        $commonDataArray['notesDetails'] = $this->em->getRepository('CommonUtilityBundle:FgCmNotes')->getNotesDetails($contactid, $noteOffset, $limit, $clubId);
        $groupUserDetails = $this->em->getRepository('CommonUtilityBundle:SfGuardGroup')->getGroupDetails($this->conn, $this->clubId, $contactid);
        $commonDataArray['hasUserRights'] = (count($groupUserDetails) > 0) ? 1 : 0;
        return $commonDataArray;
    }

}
