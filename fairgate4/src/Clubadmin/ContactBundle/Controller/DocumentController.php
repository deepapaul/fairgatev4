<?php

/**
 * DocumentController.
 *
 * This controller was created for listing contact documents
 *
 * @author     pitsolutions.ch
 *
 * @version    Fairgate V4
 */

namespace Clubadmin\ContactBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Clubadmin\Util\Contactlist;
use Clubadmin\ContactBundle\Util\ContactDetailsAccess;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Clubadmin\DocumentsBundle\Util\Documentlist;
use Clubadmin\DocumentsBundle\Util\Documenttablesetting;
use Clubadmin\DocumentsBundle\Util\Documentdatatable;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Repository\Pdo\DocumentPdo;

/**
 * Manage contact document listing related functionality.
 */
class DocumentController extends FgController
{

    private static $tabledatas;

    /**
     * Function for listing documents.
     *
     * @param int    $offset  Offset
     * @param int    $contact Contact Id
     * @param string $module  contact or sponsor
     *
     * @return type
     */
    public function indexAction($offset, $contact, $module)
    {
        $accessObj = new ContactDetailsAccess($contact, $this->container, $module);
        if ($accessObj->accessType == 'NO_ACCESS' || !in_array('document', $accessObj->tabArray)) {           
            $this->fgpermission->checkClubAccess('','backend_contact_document');
        }
        $contactType = $accessObj->contactviewType;
        $isArchiveSponsor = false;
        $contactMenuModule = $accessObj->menuType;
        if ($contactMenuModule == 'archive' && $accessObj->module == 'contact') {
            $this->get('club')->set('moduleMenu', 'contactarchive');
        } elseif ($contactMenuModule == 'archive' && $accessObj->module == 'sponsor') {
            $this->get('club')->set('moduleMenu', 'archivedsponsor');
            $isArchiveSponsor = true;
        } elseif ($contactMenuModule == 'formerfederationmember') {
            $this->get('club')->set('moduleMenu', 'formerfederationmember');
        }
        $this->session->set('contactType', $contactType);
        $moduleType = $this->get('club')->get('moduleMenu');
        $contactData = $this->contactDetails($contact, $contactType);
        $documentCatDetails = $this->em->getRepository('CommonUtilityBundle:FgDmDocumentCategory')->getDocumentSubCategories($this->clubId, 'CONTACT', $this->clubDefaultLang);
        $dataSetArray = array('contactData' => $contactData, 'contactId' => $contact, 'offset' => $offset, 'activeTab' => '', 'type' => $contactType,
             'docCategory' => $documentCatDetails, 'contactName' => $this->contactName, 'clubDefaultLang' => $this->clubDefaultLang, 'clubId' => $this->clubId,);
        $isCompany = $contactData['is_company'];
        $contCountDetails = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getContactCountDetails($this->clubId, $contact, $isCompany, $this->clubType, true, true, true, false, false, false, false, $this->federationId, $this->subFederationId);
        $dataSet = array_merge($dataSetArray, $contCountDetails);
        $dataSet['bookedModulesDet'] = $this->get('club')->get('bookedModulesDet');
        $dataSet['documentsCount'] = $this->em->getRepository('CommonUtilityBundle:FgDmAssigment')->getCountOfAssignedDocuments('CONTACT', $this->clubId, $contact);
        $dataSet['module'] = $module;
        $dataSet['moduleType'] = $moduleType;
        $contCountDetails['documentsCount'] =   $dataSet['documentsCount'];
        if ($module == 'sponsor') {
            $dataSet['servicesCount'] = $this->em->getRepository('CommonUtilityBundle:FgSmBookings')->getCountOfSponsorServices($this->clubId, $contact, $isArchiveSponsor);
            $dataSet['adsCount'] = $this->em->getRepository('CommonUtilityBundle:FgSmSponsorAds')->getCountOfSponsorAds($this->clubId, $contact);
            $contCountDetails['documentsCount'] = $dataSet['documentsCount'];
            $contCountDetails['adsCount'] = $dataSet['adsCount'];
            $contCountDetails['servicesCount'] = $dataSet['servicesCount'];
        } else {
            $groupUserDetails = $this->em->getRepository('CommonUtilityBundle:SfGuardGroup')->getGroupDetails($this->conn, $this->clubId, $contact);
            $hasUserRights = (count($groupUserDetails) > 0) ? 1 : 0;
            $dataSet['hasUserRights'] = $hasUserRights;
            $missingReqAssgn = $this->em->getRepository('CommonUtilityBundle:FgRmCategory')->missingReqFedAssign($contact, $this->clubId, $this->federationId, $this->subFederationId, $this->clubType, $this->clubDefaultLang, $this->conn);
            $dataSet['missingReqAssgment'] = $missingReqAssgn;
        }
        $dataSet['tabs'] = FgUtility::getTabsArrayDetails($this->container, $accessObj->tabArray, $offset, $contact, $contCountDetails, "document",$module);
        $dataSet['isReadOnlyContact'] = $this->isReadOnlyContact();
       
        return $this->render('ClubadminContactBundle:Document:index.html.twig', $dataSet);
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
     * Function to get contact name.
     *
     * @param int $contactId Contact Id
     *
     * @return template
     */
    private function contactDetails($contactId, $type = 'contact')
    {
        $club = $this->get('club');
        $contactlistClass = new Contactlist($this->container, '', $club, $type);
        $contactlistClass->setColumns(array('contactname', 'contactName', 'is_company', 'fedMembershipId', 'fg_cm_contact.club_id'));
        $contactlistClass->setFrom('*');
        $contactlistClass->setCondition();
        $sWhere = " fg_cm_contact.id=$contactId";
        $contactlistClass->addCondition($sWhere);
        $listquery = $contactlistClass->getResult();
        $fieldsarray = $this->conn->fetchAll($listquery);

        return $fieldsarray[0];
    }

    /**
     * Function to list all documents of contact.
     *
     * @param object $request request object
     *
     * @return json
     */
    public function documentListingAjaxAction(Request $request)
    {
        $language = $this->clubDefaultLang;
        $clubId = $this->clubId;
        $contactId = $request->get('contact');
        $aColumns = $this->getTableColumns($contactId);
        $mysqldate = $this->get('club')->get('mysqldate');
        $documentlistClass = new Documentlist($this->container, 'CONTACT');
        $documentPdo = new DocumentPdo($this->container);   
        $totallistquery = $documentPdo->getQueryForContactDocuments($aColumns, $mysqldate, $documentlistClass, $language, $clubId, $contactId);
        $documents = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($totallistquery);
        $i = 0;
        foreach ($documents as $document) {
            $url = $this->generateUrl('document_settings_contact', array('documentId' => $document['documentId'], 'offset' => 0));
            $documents[$i]['editPath'] = $url;
            $i++;
        }
        //iterate the result
        $documentDatatabledata = new Documentdatatable($this->container);
        $documents = $documentDatatabledata->iterateDataTableData($documents);
        $aaDataType = $this->getContactFieldDetails($this->tabledatas);
        $return = json_encode(array('aaData' => $documents, 'iTotalRecords' => count($documents), 'iTotalDisplayRecords' => count($documents), 'aaDataType' => $aaDataType, 'start' => 0));

        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }

    /**
     * @param array  $this->tabledatas
     * @param object $club
     * @param string $doctype
     *
     * @return array
     */
    public function getTableColumns($contactId)
    {
        $cols = '{"1":{"id":"CATEGORY","type":"FO","name":"CO_FO_CATEGORY"},"2":{"id":"SIZE","type":"FO","name":"CO_FO_SIZE"},"3":{"id":"LAST_UPDATED","type":"DO","name":"CO_DO_LAST_UPDATED"},"4":{"id":"AUTHOR","type":"UO","name":"CO_UO_AUTHOR"},"5":{"id":"DEPOSITED_WITH_ASSIGNED","type":"FO","name":"CO_FO_DEPOSITED_WITH_FOR_ASSIGNED"}}';
        $this->tabledatas = json_decode($cols, true);
        if (is_array($this->tabledatas) && count($this->tabledatas) > 0) {
            $table = new Documenttablesetting($this->container, $this->tabledatas, $this->get('club'), 'CONTACT', $contactId);
            $aColumns = $table->getDocColumns();
        } else {
            $aColumns = array();
        }

        return $aColumns;
    }

    /**
     * Function to assign documents to contact.
     *
     * @param object $request request object
     *
     * @return json string of success
     */
    public function documentAddAjaxAction(Request $request)
    {
        $contactId = $request->get('contact');
        $documentId = $request->get('documentId');
        $this->em->getRepository('CommonUtilityBundle:FgDmAssigment')->addDocumentAssignment($contactId, $documentId, $this->contactId);

        return new Response(json_encode('success'), 200, array('Content-Type' => 'application/json'));
    }

    /**
     * Function to remove document assignment of contact.
     *
     * @param object $request request object
     *
     * @return json string of success
     */
    public function documentRemoveAjaxAction(Request $request)
    {
        $assignmentId = $request->get('assignmentId');
        $removecontact = $request->get('removecontact');
        $contactId = $request->get('contact');
        if ($removecontact === 'all') {
            $this->em->getRepository('CommonUtilityBundle:FgDmAssigment')->removeAllContactDocumentAssignments($contactId, $this->contactId, $this->clubId);
        } else {
            $this->em->getRepository('CommonUtilityBundle:FgDmAssigment')->removeDocumentAssignmentOfContact($assignmentId, $this->contactId);
        }

        return new Response(json_encode('success'), 200, array('Content-Type' => 'application/json'));
    }

    /**
     * Function to list other documents which are not assigned to contact
     * used in autocomplete field.
     *
     * @param object $request request object
     *
     * @return json
     */
    public function getOtherExistingDocsAjaxAction(Request $request)
    {
        $language = $this->clubDefaultLang;
        $contactId = $request->get('contact');
        $key = FgUtility::getSecuredData($request->get('term'), $this->conn);
        $docObj = new DocumentPdo($this->container);
        $otherExistingDocuments = $docObj->getOtherExistingDocuments($contactId, $this->clubId, $language, $key);
        $return = json_encode($otherExistingDocuments);

        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }

    /**
     * Function to show delete popup.
     *
     * @param object $request request object
     *
     * @return json string of success
     */
    public function documentShowDeletePopupAction(Request $request, $module)
    {
        $assignmentId = $request->get('assignmentId');
        $removecontact = $request->get('removecontact');
        $contactId = $request->get('contact');
        $titleText = $this->get('translator')->trans('CONTACTDOCUMENT_DELETE_HEADER');
        $deleteDesc = 'CONTACTDOCUMENT_DELETE_MESSAGE';
        $return = array('deleteDesc' => $this->get('translator')->trans($deleteDesc), 'titleText' => $titleText,
            'assignmentId' => $assignmentId, 'removecontact' => $removecontact, 'contactId' => $contactId, 'module' => $module);

        return $this->render('ClubadminClubBundle:Document:confirmDelete.html.twig', $return);
    }

    /**
     * For get the type of selected contact fields.
     *
     * @param object $request request object
     *
     * @return array
     */
    private function getContactFieldDetails()
    {
        $output = array();
        $output[] = array('title' => 'docname', 'type' => 'docname');
        $output[] = array('title' => 'edit', 'type' => 'edit');
        //club
        $output[] = array('title' => 'CL_FO_SIZE', 'type' => 'CL_FO_SIZE');
        $output[] = array('title' => 'CL_FO_VISIBLE_TO', 'type' => 'CL_FO_VISIBLE_TO');
        $output[] = array('title' => 'CL_FO_DESCRIPTION', 'type' => 'CL_FO_DESCRIPTION');
        $output[] = array('title' => 'CL_FO_DEPOSITED_WITH', 'type' => 'CL_FO_DEPOSITED_WITH');
        $output[] = array('title' => 'CL_FO_DEPOSITED_WITH_FOR_ASSIGNED', 'type' => 'CL_FO_DEPOSITED_WITH_FOR_ASSIGNED');
        //contact
        $output[] = array('title' => 'CO_FO_SIZE', 'type' => 'CO_FO_SIZE');
        $output[] = array('title' => 'CO_FO_DESCRIPTION', 'type' => 'CO_FO_DESCRIPTION');
        $output[] = array('title' => 'CO_FO_VISIBLE_TO', 'type' => 'CO_FO_VISIBLE_TO');
        $output[] = array('title' => 'CO_FO_DEPOSITED_WITH', 'type' => 'CO_FO_DEPOSITED_WITH');
        $output[] = array('title' => 'CO_FO_DEPOSITED_WITH_FOR_ASSIGNED', 'type' => 'CO_FO_DEPOSITED_WITH_FOR_ASSIGNED');
        //workgroup
        $output[] = array('title' => 'WG_FO_SIZE', 'type' => 'WG_FO_SIZE');
        $output[] = array('title' => 'WG_FO_DEPOSITED_WITH', 'type' => 'WG_FO_DEPOSITED_WITH');
        $output[] = array('title' => 'WG_FO_VISIBLE_TO', 'type' => 'WG_FO_VISIBLE_TO');
        $output[] = array('title' => 'WG_FO_DESCRIPTION', 'type' => 'WG_FO_DESCRIPTION');
        //team
        $output[] = array('title' => 'T_FO_VISIBLE_TO', 'type' => 'T_FO_VISIBLE_TO');
        $output[] = array('title' => 'T_FO_SIZE', 'type' => 'T_FO_SIZE');
        $output[] = array('title' => 'T_FO_DEPOSITED_WITH', 'type' => 'T_FO_DEPOSITED_WITH');
        $output[] = array('title' => 'T_FO_DESCRIPTION', 'type' => 'T_FO_DESCRIPTION');
        $output[] = array('title' => 'visibleFor', 'type' => 'visibleFor');

        return $output;
    }

}
