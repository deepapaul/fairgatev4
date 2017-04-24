<?php

/**
 * ContactController.
 *
 * This controller was created for handling contact listing functionalities
 *
 * @author     pitsolutions.ch
 *
 * @version    Fairgate V4
 */
namespace Clubadmin\ContactBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Clubadmin\Util\Contactlist;
use Clubadmin\Util\Contactfilter;
use Clubadmin\Classes\Contactdatatable;
use Clubadmin\ContactBundle\Util\ContactlistData;
use Symfony\Component\HttpFoundation\Request;
use Common\UtilityBundle\Repository\Pdo\membershipPdo;

/**
 * Manage contact listing related functionality.
 */
class ContactController extends FgController
{

    /**
     * Execute all the contact related to the particular club/federation action.
     *
     * @param type $contactType contacttype
     *
     * @return json
     */
    public function listcontactAction(Request $request, $contactType)
    {
        //Set all request value to its corresponding variables
        $contactlistData = new ContactlistData($this->contactId, $this->container, $contactType);
        $contactlistData->filterValue = $request->get('filterdata', '');
        //check if the request is valid or not
        if ($contactlistData->filterValue != '' && $contactlistData->filterValue == '0') {
            $output = array('iTotalRecords' => 0, 'iTotalDisplayRecords' => 0, 'aaData' => array());

            return new JsonResponse($output);
        }
        $contactlistData->functionTypeValue = $request->get('functionType', 'none');
        $contactlistData->dataTableColumnData = $request->get('columns', '');
        $contactlistData->sortColumnValue = $request->get('order', '');
        $contactlistData->searchval = $request->get('search', '');
        $contactlistData->tableFieldValues = $request->get('tableField', '');
        $contactlistData->startValue = $request->get('start', '');
        $contactlistData->roleFilter = $request->get('filterrole', '');
        $contactlistData->displayLength = $request->get('length', '');
        //For get the contact list array
        $contactData = $contactlistData->getContactData();

        $this->session->set('contactType', $contactType);
        //collect total number of records
        $totalrecords = $contactData['totalcount'];
        //For set the datatable json array
        $output = array('iTotalRecords' => $totalrecords, 'iTotalDisplayRecords' => $totalrecords, 'aaData' => array());
        $this->session->set($this->contactId . $this->clubId, $totalrecords);
        // Section for next and previous functionality
        $contactlistData->setSessionValues($contactData['data']);
        //iterate the result
        $contactDatatabledata = new Contactdatatable($this->container);
        $output['aaData'] = $contactDatatabledata->iterateDataTableData($contactData['data'], $this->container->getParameter('country_fields'), $contactlistData->tabledata);
        $output['aaDataType'] = $this->getContactFieldDetails($contactlistData->tabledata);

        return new JsonResponse($output);
    }

    /**
     * Function to view the contact of a club or federation.
     *
     * @return Json array
     */
    public function viewcontactAction()
    {
        $breadCrumb = array(
            'breadcrumb_data' => array(),
        );
        $settingsType = 'DATA';
        $allTableSettings = $this->em->getRepository('CommonUtilityBundle:FgTableSettings')->getAllTableSettings($this->clubId, $this->contactId, $settingsType);
        $club = $this->get('club');
        $workgroupId = $club->get('club_workgroup_id');
        $teamId = $club->get('club_team_id');

        $editUrl = $this->generateUrl('edit_contact', array('contact' => 'dummy'));
        $container = $this->container;
        $defaultSettings = $container->getParameter('default_table_settings');
        $corrAddrCatId = $container->getParameter('system_category_address');
        $invAddrCatId = $container->getParameter('system_category_invoice');
        $clubHeirarchy = $club->get('clubHeirarchy');
        $federationClubId = (count($clubHeirarchy) > 0) ? $clubHeirarchy[0] : $this->clubId;
        $corrAddrFieldIds = array();
        $invAddrFieldIds = array();
        $contactFields = $this->get('club')->get('contactFields');
        $type = 'contact';
        foreach ($contactFields as $contactField) {
            if ($contactField['catId'] == $corrAddrCatId) {
                $corrAddrFieldIds[] = $contactField['id'];
            } elseif ($contactField['catId'] == $invAddrCatId) {
                $invAddrFieldIds[] = $contactField['id'];
            }
        }
        /* Required assignment error of Club Executive Board Members - starts */
        $reqExecBoardFunError = false;
        $isClubAdministrator = $this->isClubAdministrator();
        $isSuperAdmin = $this->get('contact')->get('isSuperAdmin');
        if (!in_array($this->clubType, array('federation', 'standard_club')) && $isClubAdministrator) {
            $execBoardFunsCount = $this->em->getRepository('CommonUtilityBundle:FgRmFunction')->getRequiredExecBoardFunctionsCount($this->clubWorkgroupId, $this->clubExecutiveBoardId, $this->clubId, $this->federationId);
            foreach ($execBoardFunsCount as $funId => $execBoardFunCount) {
                if ($execBoardFunCount == 0) {
                    $reqExecBoardFunError = true;
                }
            }
        }
        $isReadOnlyContact = $this->isReadOnlyContact();

        return $this->render('ClubadminContactBundle:ContactList:contactlist.html.twig', array('addExistingFedMemberClub' => $club->get('addExistingFedMemberClub'), 'clubMembershipAvailable' => $club->get('clubMembershipAvailable'), 'breadCrumb' => $breadCrumb, 'isReadOnlyContact' => $isReadOnlyContact, 'clubId' => $this->clubId, 'contactId' => $this->contactId, 'allTableSettings' => $allTableSettings, 'defaultSettings' => $defaultSettings, 'teamId' => $teamId, 'workgroupId' => $workgroupId, 'editUrl' => $editUrl, 'corrAddrFieldIds' => $corrAddrFieldIds, 'invAddrFieldIds' => $invAddrFieldIds, 'reqExecBoardFunError' => $reqExecBoardFunError, 'contacttype' => $type, 'urlIdentifier' => $this->clubUrlIdentifier, 'clubType' => $this->clubType, 'fedClubId' => $federationClubId,'isSuperAdmin'=>$isSuperAdmin));
    }

    /**
     * For handle archive contact.
     *
     * @return type twig
     */
    public function viewArchivecontactAction()
    {
        $breadCrumb = array(
            'breadcrumb_data' => array('title' => 'Archived '),
        );
        $settingsType = 'DATA';
        $allTableSettings = $this->em->getRepository('CommonUtilityBundle:FgTableSettings')->getAllTableSettings($this->clubId, $this->contactId, $settingsType);
        $club = $this->get('club');
        $workgroupId = $club->get('club_workgroup_id');
        $teamId = $club->get('club_team_id');
        $clubHeirarchy = $club->get('clubHeirarchy');
        $federationClubId = (count($clubHeirarchy) > 0) ? $clubHeirarchy[0] : $this->clubId;
        $editUrl = $this->generateUrl('edit_contact', array('contact' => 'dummy'), true);
        $container = $this->container;
        $defaultSettings = $container->getParameter('default_table_settings');
        $corrAddrCatId = $container->getParameter('system_category_address');
        $invAddrCatId = $container->getParameter('system_category_invoice');
        $corrAddrFieldIds = array();
        $invAddrFieldIds = array();
        $contactFields = $this->get('club')->get('contactFields');
        $type = 'archive';
        foreach ($contactFields as $contactField) {
            if ($contactField['catId'] == $corrAddrCatId) {
                $corrAddrFieldIds[] = $contactField['id'];
            } elseif ($contactField['catId'] == $invAddrCatId) {
                $invAddrFieldIds[] = $contactField['id'];
            }
        }
        $isReadOnlyContact = $this->isReadOnlyContact();

        /* Required assignment error of Club Executive Board Members - starts */
        $reqExecBoardFunError = false;

        /* Required assignment error of Club Executive Board Members - ends */

        return $this->render('ClubadminContactBundle:ContactList:contactlist.html.twig', array('addExistingFedMemberClub' => $club->get('addExistingFedMemberClub'), 'clubMembershipAvailable' => $club->get('clubMembershipAvailable'), 'breadCrumb' => $breadCrumb, 'isReadOnlyContact' => $isReadOnlyContact, 'clubId' => $this->clubId, 'contactId' => $this->contactId, 'allTableSettings' => $allTableSettings, 'defaultSettings' => $defaultSettings, 'teamId' => $teamId, 'workgroupId' => $workgroupId, 'editUrl' => $editUrl, 'corrAddrFieldIds' => $corrAddrFieldIds, 'invAddrFieldIds' => $invAddrFieldIds, 'reqExecBoardFunError' => $reqExecBoardFunError, 'contacttype' => $type, 'urlIdentifier' => $this->clubUrlIdentifier, 'clubType' => $this->clubType, 'fedClubId' => $federationClubId));
    }

    /**
     * For handle former federation member contact.
     *
     * @return type twig
     */
    public function viewformerfederationMemberAction()
    {
        if (($this->clubType != 'federation') && ($this->clubType != 'sub_federation')) {
            //throw $this->createNotFoundException($this->clubTitle . ' have no access to this page');
            $this->fgpermission->checkClubAccess('', 'viewformerfederation');
        }
        $breadCrumb = array(
            'breadcrumb_data' => array('title' => 'Former Federation member'),
        );
        $settingsType = 'DATA';
        $allTableSettings = $this->em->getRepository('CommonUtilityBundle:FgTableSettings')->getAllTableSettings($this->clubId, $this->contactId, $settingsType);
        $club = $this->get('club');
        $workgroupId = $club->get('club_workgroup_id');
        $teamId = $club->get('club_team_id');
        $clubHeirarchy = $club->get('clubHeirarchy');
        $federationClubId = (count($clubHeirarchy) > 0) ? $clubHeirarchy[0] : $this->clubId;
        $editUrl = $this->generateUrl('edit_contact', array('contact' => 'dummy'), true);
        $container = $this->container;
        $defaultSettings = $container->getParameter('default_table_settings');
        $corrAddrCatId = $container->getParameter('system_category_address');
        $invAddrCatId = $container->getParameter('system_category_invoice');
        $corrAddrFieldIds = array();
        $invAddrFieldIds = array();
        $contactFields = $this->get('club')->get('contactFields');
        $type = 'formerfederationmember';
        foreach ($contactFields as $contactField) {
            if ($contactField['catId'] == $corrAddrCatId) {
                $corrAddrFieldIds[] = $contactField['id'];
            } elseif ($contactField['catId'] == $invAddrCatId) {
                $invAddrFieldIds[] = $contactField['id'];
            }
        }
        $isReadOnlyContact = $this->isReadOnlyContact();

        /* Required assignment error of Club Executive Board Members - starts */
        $reqExecBoardFunError = false;

        /* Required assignment error of Club Executive Board Members - ends */
        return $this->render('ClubadminContactBundle:ContactList:contactlist.html.twig', array('addExistingFedMemberClub' => $club->get('addExistingFedMemberClub'), 'clubMembershipAvailable' => $club->get('clubMembershipAvailable'), 'breadCrumb' => $breadCrumb, 'isReadOnlyContact' => $isReadOnlyContact, 'clubId' => $this->clubId, 'contactId' => $this->contactId, 'allTableSettings' => $allTableSettings, 'defaultSettings' => $defaultSettings, 'teamId' => $teamId, 'workgroupId' => $workgroupId, 'editUrl' => $editUrl, 'corrAddrFieldIds' => $corrAddrFieldIds, 'invAddrFieldIds' => $invAddrFieldIds, 'reqExecBoardFunError' => $reqExecBoardFunError, 'contacttype' => $type, 'urlIdentifier' => $this->clubUrlIdentifier, 'clubType' => $this->clubType, 'fedClubId' => $federationClubId));
    }

    /**
     * Executes sidebarFilter Action.
     *
     * Function specifies the Filter for the club
     *
     * @return Json array
     */
    public function sidebarFilterAction()
    {
        $allSavedFilter = $this->em->getRepository('CommonUtilityBundle:FgFilter')->getSavedSidebarFilter($this->contactId, $this->clubId);

        return new JsonResponse(array('allSavedFilter' => $allSavedFilter));
    }

    /**
     * Execute Savesd filter listings.
     *
     * Function to list the Savesd filters
     *
     * @return HTML
     */
    public function savedfilterAction()
    {
        $breadCrumb = array(
            'breadcrumb_data' => array(
                'Active Contacts' => '#',
                'Saved Filter' => '#',
            ),
            'back' => $this->generateUrl('contact_index'),
        );
        $allSavedFilter = $this->em->getRepository('CommonUtilityBundle:FgFilter')->getSavedSidebarFilter($this->contactId, $this->clubId);

        return $this->render('ClubadminContactBundle:ContactList:savedfilter.html.twig', array('breadCrumb' => $breadCrumb, 'allSavedFilter' => $allSavedFilter, 'allSavedFilter' => $allSavedFilter, 'clubId' => $this->clubId, 'contactId' => $this->contactId));
    }

    /**
     * Executes sidebarFilterCount Action.
     *
     * Function to get the count of each filter
     *
     * @return Nill
     */
    public function sidebarFilterCountAction(Request $request)
    {
        try {
            $id = $request->request->get('filterId', '0');
            if ($id == 0) {
                $request = $this->container->get('request_stack')->getCurrentRequest();
                $id = $request->get('filter_id');
            }
            //call a service for collect all relevant data related to the club
            $club = $this->get('club');
            $clubtype = $club->get('type');
            $type = $request->get('type', 'filter');
            $singleSavedFilter = $this->em->getRepository('CommonUtilityBundle:FgFilter')->getSingleSavedSidebarFilter($id, $this->contactId, $this->clubId, $type);
            $filterdata = $singleSavedFilter[0]['filterData'];
            $contactlistClass = new Contactlist($this->container, $this->contactId, $club);

            $contactlistClass->setCount();
            $contactlistClass->setFrom();
            $contactlistClass->setCondition();

            $filterarr = json_decode($filterdata, true);

            $filter = array_shift($filterarr);
            $filterObj = new Contactfilter($this->container, $contactlistClass, $filter, $club);
            $sWhere .= ' ' . $filterObj->generateFilter();
            $contactlistClass->addCondition(trim($sWhere));

            $mc_contact_field = 'mc.contact_id';
            if ($clubtype == 'federation') {
                $mc_contact_field = 'mc.fed_contact_id';
            }
            if ($type == 'role') {
                $filterRoleId = $request->get('role_id');
                $isFilterrole = $request->get('from');

                $sWhere = "($mc_contact_field NOT IN(select mr1.contact_id from fg_rm_role_manual_contacts mr1 where mr1.contact_id = $mc_contact_field and mr1.type = 'excluded' and mr1.role_id = $filterRoleId))";
                $contactlistClass->addCondition($sWhere);

                $sWhere = "($mc_contact_field  IN(select mr.contact_id from fg_rm_role_manual_contacts mr where mr.contact_id = $mc_contact_field and mr.type = 'included' and mr.role_id = $filterRoleId))";
                $contactlistClass->orCondition($sWhere);

                //updation of filter roles
                $this->em->getRepository('CommonUtilityBundle:FgRmRole')->updateFilterRoles($filterRoleId, $this->container, $this->contactId);
                $lastUpdated = $this->em->getRepository('CommonUtilityBundle:FgRmRole')->getFieldForRoles($filterRoleId, 'filter_updated');
                //ends
            }
            //call query for collect the data
            $totallistquery = $contactlistClass->getResult();

            $totalcontactlistDatas = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($totallistquery);
            if ($isFilterrole == 'filterrole') {
                $successMessage = 'FILTER_CONTACTS_UPDATED';

                return new JsonResponse(array('status' => 'SUCCESS', 'noparentload' => true, 'flash' => $this->get('translator')->trans($successMessage)));
            } else {
                return new Response($totalcontactlistDatas[0]['count']);
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            //print_r($e->getMessage());
            $singleSavedFilter = $this->em->getRepository('CommonUtilityBundle:FgFilter')->updateBorkenFilter($id, '1');

            return new Response(-1);
        }
    }

    /**
     * Function to set session related to Next and previous links.
     *
     * @param type $columnsArray   tablecolumns
     * @param type $sSearch        searchValue
     * @param type $filterdata     filterdata
     * @param type $iDisplayLength displaylength
     * @param type $iSortColVal    columnSortValue
     * @param type $mDataProp      sortOrder
     * @param type $sSortDirVal    sortOrder
     * @param type $tableField     tablefield
     */
    public function setSessionForNextPre($columnsArray, $sSearch, $filterdata, $iDisplayLength, $iSortColVal, $mDataProp, $sSortDirVal, $tableField)
    {
        if (isset($tableField) && $tableField != '') {
            $this->session->set('tableField', $tableField);
        }
        $this->session->set('columnsArray', $columnsArray);
        if (isset($sSearch) && $sSearch != '') {
            $this->session->set('filteredContactDetailsSearch', $sSearch);
        }
        if (isset($filterdata) && $filterdata != 'contact' && $filterdata != '') {
            $this->session->set('filteredContactDetailsFilterdata', $filterdata);
        }
        if (isset($iDisplayLength) && $iDisplayLength != '') {
            $this->session->set('filteredContactDetailsDisplayLength', $iDisplayLength);
        }
        if (isset($iSortColVal) && $iSortColVal != '' && $mDataProp != 'edit') {
            $this->session->set('filteredContactDetailsiSortCol_0', $iSortColVal);
            $this->session->set('filteredContactDetailsmDataProp', $mDataProp);
            $this->session->set('filteredContactDetailsSortDir_0', $sSortDirVal);
        }
    }

    /**
     * Function to remove session related to Next and previous links.
     */
    public function removeNextPrevSession()
    {
        $this->session->remove('columnsArray');
        $this->session->remove('filteredContactDetailsiSortCol_0');
        $this->session->remove('filteredContactDetailsmDataProp');
        $this->session->remove('filteredContactDetailsSortDir_0');
        $this->session->remove('filteredContactDetailsSearch');
        $this->session->remove('filteredContactDetailsFilterdata');
        $this->session->remove('nextPreviousContactListData');
        $this->session->remove('tableField');
    }

    /**
     * Function to check whether the Logged-in Contact is Club Administrator.
     *
     * @return bool Whether club administrator or not.
     */
    private function isClubAdministrator()
    {
        return true;
    }

    /**
     * For get the type of selected contact fields.
     *
     * @param array $tabledatas
     *
     * @return array
     */
    private function getContactFieldDetails($tabledatas)
    {
        $club = $this->get('club');
        $allContactFiledsData = $club->get('allContactFields');
        $output['aaDataType'] = array();
        $originalTitlesArray = $this->container->getParameter('country_fields');
        $originalTitlesArray[] = $this->container->getParameter('system_field_corress_lang');
        $originalTitlesArray[] = $this->container->getParameter('system_field_salutaion');
        $originalTitlesArray[] = $this->container->getParameter('system_field_gender');
        //service for contact field/profile image path
        $pathService = $this->container->get('fg.avatar');
        foreach ($tabledatas as $key => $contactFields) {
            if (array_key_exists($contactFields['id'], $allContactFiledsData)) {
                switch ($allContactFiledsData[$contactFields['id']]['type']) {
                    case 'login email':case 'email':case 'Email':
                        $output['aaDataType'][] = array('title' => 'CF_' . $contactFields['id'], 'type' => 'email', 'attrId' => $contactFields['id'], 'category_id' => $allContactFiledsData[$contactFields['id']]['category_id'], 'is_editable' => $allContactFiledsData[$contactFields['id']]['is_editable'], 'is_required' => $allContactFiledsData[$contactFields['id']]['is_required'], 'is_company' => $allContactFiledsData[$contactFields['id']]['is_company'], 'is_personal' => $allContactFiledsData[$contactFields['id']]['is_personal'], 'is_system_field' => $allContactFiledsData[$contactFields['id']]['is_system_field'], 'addres_type' => $allContactFiledsData[$contactFields['id']]['addres_type'], 'address_id' => $allContactFiledsData[$contactFields['id']]['address_id']);
                        break;
                    case 'imageupload':
                        $output['aaDataType'][] = array('title' => 'CF_' . $contactFields['id'], 'type' => 'imageupload', 'uploadPath' => $pathService->getContactfieldPath($contactFields['id']), 'contactfieldId' => $contactFields['id']);
                        break;
                    case 'fileupload':
                        $output['aaDataType'][] = array('title' => 'CF_' . $contactFields['id'], 'type' => 'fileupload', 'uploadPath' => $pathService->getContactfieldPath($contactFields['id']), 'contactfieldId' => $contactFields['id']);
                        break;
                    case 'url':
                        $output['aaDataType'][] = array('title' => 'CF_' . $contactFields['id'], 'type' => 'url', 'attrId' => $contactFields['id'], 'category_id' => $allContactFiledsData[$contactFields['id']]['category_id'], 'is_editable' => $allContactFiledsData[$contactFields['id']]['is_editable'], 'is_required' => $allContactFiledsData[$contactFields['id']]['is_required'], 'is_company' => $allContactFiledsData[$contactFields['id']]['is_company'], 'is_personal' => $allContactFiledsData[$contactFields['id']]['is_personal'], 'is_system_field' => $allContactFiledsData[$contactFields['id']]['is_system_field'], 'addres_type' => $allContactFiledsData[$contactFields['id']]['addres_type'], 'address_id' => $allContactFiledsData[$contactFields['id']]['address_id']);
                        break;
                    case 'multiline':
                        $output['aaDataType'][] = array('title' => 'CF_' . $contactFields['id'], 'type' => 'multiline', 'attrId' => $contactFields['id'], 'category_id' => $allContactFiledsData[$contactFields['id']]['category_id'], 'is_editable' => $allContactFiledsData[$contactFields['id']]['is_editable'], 'is_required' => $allContactFiledsData[$contactFields['id']]['is_required'], 'is_company' => $allContactFiledsData[$contactFields['id']]['is_company'], 'is_personal' => $allContactFiledsData[$contactFields['id']]['is_personal'], 'is_system_field' => $allContactFiledsData[$contactFields['id']]['is_system_field'], 'addres_type' => $allContactFiledsData[$contactFields['id']]['addres_type'], 'address_id' => $allContactFiledsData[$contactFields['id']]['address_id']);
                        break;
                    case 'singleline':
                        $output['aaDataType'][] = array('title' => 'CF_' . $contactFields['id'], 'type' => 'singleline', 'attrId' => $contactFields['id'], 'category_id' => $allContactFiledsData[$contactFields['id']]['category_id'], 'is_editable' => $allContactFiledsData[$contactFields['id']]['is_editable'], 'is_required' => $allContactFiledsData[$contactFields['id']]['is_required'], 'is_company' => $allContactFiledsData[$contactFields['id']]['is_company'], 'is_personal' => $allContactFiledsData[$contactFields['id']]['is_personal'], 'is_system_field' => $allContactFiledsData[$contactFields['id']]['is_system_field'], 'addres_type' => $allContactFiledsData[$contactFields['id']]['addres_type'], 'address_id' => $allContactFiledsData[$contactFields['id']]['address_id']);
                        break;
                    case 'select':
                        if (in_array($contactFields['id'], $originalTitlesArray)) {
                            $output['aaDataType'][] = array('title' => 'CF_' . $contactFields['id'], 'type' => 'select', 'originalTitle' => 'CF_' . $contactFields['id'] . '_original', 'attrId' => $contactFields['id'], 'category_id' => $allContactFiledsData[$contactFields['id']]['category_id'], 'is_editable' => $allContactFiledsData[$contactFields['id']]['is_editable'], 'is_required' => $allContactFiledsData[$contactFields['id']]['is_required'], 'is_company' => $allContactFiledsData[$contactFields['id']]['is_company'], 'is_personal' => $allContactFiledsData[$contactFields['id']]['is_personal'], 'is_system_field' => $allContactFiledsData[$contactFields['id']]['is_system_field'], 'addres_type' => $allContactFiledsData[$contactFields['id']]['addres_type'], 'address_id' => $allContactFiledsData[$contactFields['id']]['address_id']);
                        } else {
                            $output['aaDataType'][] = array('title' => 'CF_' . $contactFields['id'], 'type' => 'select', 'attrId' => $contactFields['id'], 'category_id' => $allContactFiledsData[$contactFields['id']]['category_id'], 'is_editable' => $allContactFiledsData[$contactFields['id']]['is_editable'], 'is_required' => $allContactFiledsData[$contactFields['id']]['is_required'], 'is_company' => $allContactFiledsData[$contactFields['id']]['is_company'], 'is_personal' => $allContactFiledsData[$contactFields['id']]['is_personal'], 'is_system_field' => $allContactFiledsData[$contactFields['id']]['is_system_field'], 'addres_type' => $allContactFiledsData[$contactFields['id']]['addres_type'], 'address_id' => $allContactFiledsData[$contactFields['id']]['address_id']);
                        }
                        break;
                    default:
                        $output['aaDataType'][] = array('title' => 'CF_' . $contactFields['id'], 'type' => $allContactFiledsData[$contactFields['id']]['type'], 'attrId' => $contactFields['id'], 'category_id' => $allContactFiledsData[$contactFields['id']]['category_id'], 'is_editable' => $allContactFiledsData[$contactFields['id']]['is_editable'], 'is_required' => $allContactFiledsData[$contactFields['id']]['is_required'], 'is_company' => $allContactFiledsData[$contactFields['id']]['is_company'], 'is_personal' => $allContactFiledsData[$contactFields['id']]['is_personal'], 'is_system_field' => $allContactFiledsData[$contactFields['id']]['is_system_field'], 'addres_type' => $allContactFiledsData[$contactFields['id']]['addres_type'], 'address_id' => $allContactFiledsData[$contactFields['id']]['address_id']);
                        break;
                }
            }
        }
        $output['aaDataType'][] = array('title' => 'CNhousehold_contact', 'type' => 'CNhousehold_contact');
        $output['aaDataType'][] = array('title' => 'Function', 'type' => 'Function', 'is_editable' => 1, 'currentClubId' => $this->clubId);
        $output['aaDataType'][] = array('title' => 'Gnotes', 'type' => 'Gnotes');
        $output['aaDataType'][] = array('title' => 'Gdocuments', 'type' => 'Gdocuments');
        $output['aaDataType'][] = array('title' => 'contactname', 'type' => 'contactname');
        $output['aaDataType'][] = array('title' => 'edit', 'type' => 'edit');
        $output['aaDataType'][] = array('title' => 'CMfirst_joining_date', 'type' => 'CMfirst_joining_date', 'clubType' => $this->clubType);
        $output['aaDataType'][] = array('title' => 'CMjoining_date', 'type' => 'CMjoining_date', 'clubType' => $this->clubType);
        $output['aaDataType'][] = array('title' => 'CMleaving_date', 'type' => 'CMleaving_date', 'clubType' => $this->clubType);
        $output['aaDataType'][] = array('title' => 'FMfirst_joining_date', 'type' => 'FMfirst_joining_date', 'clubType' => $this->clubType);
        $output['aaDataType'][] = array('title' => 'FMjoining_date', 'type' => 'FMjoining_date', 'clubType' => $this->clubType);
        $output['aaDataType'][] = array('title' => 'FMleaving_date', 'type' => 'FMleaving_date', 'clubType' => $this->clubType);
        $output['aaDataType'][] = array('title' => 'Gprofile_company_pic', 'type' => 'Gprofile_company_pic');
        $output['aaDataType'][] = array('title' => 'fed_membership_category', 'type' => 'fed_membership_category');
        $output['aaDataType'][] = array('title' => 'FIclub', 'type' => 'FIclub');

        return $output['aaDataType'];
    }

    /**
     * For collect all the side bar counts.
     */
    public function getAlLSidebarCountAction()
    {
        $club = $this->get('club');

        $clubHeirarchy = $club->get('clubHeirarchy');
        $activeCountDetails = $this->activeContactscount();
        //collect club role count details
        $roleType = 'clubrole';
        $isFedRole = ($roleType == 'fedrole' || $roleType == 'subfedrole') ? true : false;
        $allClubRolesCountDetails = $this->em->getRepository('CommonUtilityBundle:FgRmCategory')->getAllRole($this->clubId, $this->clubType, $this->contactId, $isFedRole, $this->federationId, $this->subFederationId, $roleType, $this->clubDefaultLang);
        $clubRolesCount = $this->rolesCountIterator($allClubRolesCountDetails, 'ROLES');
        //collect fed role count details
        $roleType = 'fedrole';
        $isFedRole = ($roleType == 'fedrole' || $roleType == 'subfedrole') ? true : false;
        $allFedRolesCountDetails = $this->em->getRepository('CommonUtilityBundle:FgRmCategory')->getAllRole($this->clubId, $this->clubType, $this->contactId, $isFedRole, $this->federationId, $this->subFederationId, $roleType, $this->clubDefaultLang);
        $fedRolesCount = $this->rolesCountIterator($allFedRolesCountDetails, 'FROLES');
        //collect sub fed role count details
        $roleType = 'subfedrole';
        $isFedRole = ($roleType == 'fedrole' || $roleType == 'subfedrole') ? true : false;
        $allSubFedRolesCountDetails = $this->em->getRepository('CommonUtilityBundle:FgRmCategory')->getAllRole($this->clubId, $this->clubType, $this->contactId, $isFedRole, $this->federationId, $this->subFederationId, $roleType, $this->clubDefaultLang);
        $subfedRolesCount = $this->rolesCountIterator($allSubFedRolesCountDetails, 'FROLES');
        //collect filter role count details
        $allFilterRolesCountDetails = $this->em->getRepository('CommonUtilityBundle:FgRmCategory')->getFilterRolesForSidebar($this->clubId, $this->contactId, $this->clubDefaultLang);
        $filterRolesCount = $this->rolesCountIterator($allFilterRolesCountDetails, 'FILTERROLES');
        //collect team count details
        $allTeamsCountDetails = $this->em->getRepository('CommonUtilityBundle:FgTeamCategory')->getAllTeamCountDetails($this->clubId, $this->clubTeamId);
        $teamCount = $this->rolesCountIterator($allTeamsCountDetails, 'TEAM');
        //collect executive board count details
        $isFedRole = true;
        $allFedWorkgroupsCountDetails = $this->em->getRepository('CommonUtilityBundle:FgRmCategory')->getWorkgroupCategories($this->clubId, $isFedRole, $this->federationId, $this->clubType);
        $executiveboardCount = $this->rolesCountIterator($allFedWorkgroupsCountDetails, 'FI');
        //collect sub  workgroup count details
        $isFedRole = false;
        $allWorkgroupsCountDetails = $this->em->getRepository('CommonUtilityBundle:FgRmCategory')->getWorkgroupCategories($this->clubId, $isFedRole, $this->federationId, $this->clubType);
        $workgroupCount = $this->rolesCountIterator($allWorkgroupsCountDetails, 'WORKGROUP');
        $contactCount = $this->getStaticmemberCount();
        //combine all array
        $allCountArray = array_merge($activeCountDetails, $contactCount, $workgroupCount, $executiveboardCount, $teamCount, $filterRolesCount, $fedRolesCount, $clubRolesCount, $subfedRolesCount);

        return new JsonResponse($allCountArray);
    }

    /**
     * Function is used to get all Active contacts.
     *
     * @return template
     */
    private function activeContactscount()
    {
        $club = $this->get('club');
        $contactlistClass = new Contactlist($this->container, $this->contactId, $club, $this->contactType);
        $contactlistClass->setCount();
        $contactlistClass->setFrom();
        $contactlistClass->setCondition();
        $countQuery = $contactlistClass->getResult();
        $totalcontactlist = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($countQuery);
        $contactCount[0]['categoryId'] = '';
        $contactCount[0]['subCatId'] = '';
        $contactCount[0]['dataType'] = 'allActive';
        $contactCount[0]['sidebarCount'] = $totalcontactlist[0]['count'];
        $contactCount[0]['action'] = 'show';

        return $contactCount;
    }

    /**
     * @param array  $rolesCountArray
     * @param String $type
     *
     * @return array
     */
    private function rolesCountIterator($rolesCountArray, $type)
    {
        $iteratedArray = array();
        foreach ($rolesCountArray as $key => $roleCount) {
            $iteratedArray[$key]['categoryId'] = ($type == 'FI') ? 'ceb_function' : $roleCount['roleCatId'];
            $iteratedArray[$key]['subCatId'] = ($type == 'FI') ? $roleCount['functionId'] : $roleCount['roleId'];
            $iteratedArray[$key]['sidebarCount'] = ($type == 'FI') ? $roleCount['fnCount'] : $roleCount['rolecount'];
            $iteratedArray[$key]['action'] = 'show';
            if ($type == 'ROLES' || $type == 'FROLES' || $type == 'FILTERROLES') {
                $iteratedArray[$key]['dataType'] = $type . '-' . $roleCount['clubId'];
            } else {
                $iteratedArray[$key]['dataType'] = $type;
            }
        }

        return $iteratedArray;
    }

    /**
     * collect static field(contact sidebar ) count.
     */
    private function getStaticmemberCount()
    {
        $federationId = ($this->clubType == 'federation') ? $this->clubId : $this->federationId;
        $objMembershipPdo = new membershipPdo($this->container);
        $membershipDetails = $objMembershipPdo->getMemberships($this->clubType, $this->clubId, $this->subFederationId, $this->federationId, $this->contactId);
        $membershipCounts = array();

        foreach ($membershipDetails as $key => $membershipDetail) {
            $membershipCounts[$key]['subCatId'] = $key;
            if ($membershipDetail['clubId'] == $federationId) {
                $membershipCounts[$key]['dataType'] = 'fed_membership';
                if ($this->clubType == 'sub_federation_club' || $this->clubType == 'federation_club' || $this->clubType == 'sub_federation') {
                    $membershipCounts[$key]['sidebarCount'] = $membershipDetail['fed'];
                } else {
                    $membershipCounts[$key]['sidebarCount'] = $membershipDetail['totalCount'];
                }
            } else {
                $membershipCounts[$key]['dataType'] = 'membership';
                $membershipCounts[$key]['sidebarCount'] = $membershipDetail['totalCount'];
            }

            $membershipCounts[$key]['action'] = 'show';
            $membershipCounts[$key]['categoryId'] = '';
        }
        $contactDetails = $this->em->getRepository('CommonUtilityBundle:FgFilter')->getSidebarFilterCount($this->contactId, $this->clubId, $this->clubType);
        $contactCounts = array_merge($membershipCounts, $contactDetails);

        return $contactCounts;
    }

    /**
     * Method to get readonly status of current contact.
     *
     * @return bool $isReadOnlyContact
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

    public function testcallAction(Request $request)
    {
        //Set all request value to its corresponding variables
        $contactlistData = new ContactlistData($this->contactId, $this->container, $contactType);
        $contactlistData->filterValue = $request->get('filterdata', '');
        //check if the request is valid or not
        if ($contactlistData->filterValue != '' && $contactlistData->filterValue == '0') {
            $output = array('iTotalRecords' => 0, 'iTotalDisplayRecords' => 0, 'aaData' => array());

            return new JsonResponse($output);
        }
        $contactlistData->functionTypeValue = $request->get('functionType', 'none');
        $contactlistData->dataTableColumnData = array();
        array_push($contactlistData->dataTableColumnData, array('data' => 'edit', 'name' => '', 'orderable' => false, 'search' => array('value' => ''), 'searchable' => true));
        array_push($contactlistData->dataTableColumnData, array('data' => 'contactname', 'name' => '', 'orderable' => false, 'search' => array('value' => ''), 'searchable' => true));
        array_push($contactlistData->dataTableColumnData, array('data' => 'Function', 'name' => '', 'orderable' => false, 'search' => array('value' => ''), 'searchable' => true));

        $contactlistData->sortColumnValue = $request->get('order', '');
        $contactlistData->searchval = $request->get('search', '');
        $contactlistData->tableFieldValues = '
{"1":{"id":"household_contact","type":"CN","club_id":"608","name":"CNhousehold_contact"},"2":{"id":"join_leave_dates"
,"type":"G","club_id":"608","name":"Gjoin_leave_dates"},"3":{"id":"age","type":"G","club_id":"608","name"
:"Gage"},"4":{"id":"birth_year","type":"G","club_id":"608","name":"Gbirth_year"},"5":{"id":"contact_id"
,"type":"G","club_id":"608","name":"Gcontact_id"},"6":{"id":"4","type":"R","club_id":"608","name":"R4_6"
,"sub_ids":"5973,3610","team_rolecat_ids":{"470":"5973,3610"},"is_fed_cat":"0"},"7":{"id":"119","type"
:"R","club_id":"608","name":"R119_7","sub_ids":"all","is_fed_cat":"0"},"8":{"id":"4","type":"RF","club_id"
:"608","name":"RF4_8","sub_ids":"5973,3610,5875","team_rolecat_ids":{"470":"5973,3610,5875"},"is_fed_cat"
:"0"}}';
        $contactlistData->startValue = $request->get('start', '');
        $contactlistData->roleFilter = $request->get('filterrole', '');
        $contactlistData->displayLength = $request->get('length', '');
        //For get the contact list array
        $contactData = $contactlistData->getContactData();
        echo '<pre>';
        print_r($contactData);
        exit;

        return new JsonResponse($output);
    }
}
