<?php

/**
 * Documents Controller
 *
 * This controller is used for handling functionalities related to documents in the internal personal section
 *
 * @package    InternalTeamBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 *
 */
namespace Internal\ProfileBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Clubadmin\DocumentsBundle\Util\Documentlist;
use Internal\GeneralBundle\Util\InternalDocumentColumnSettings;
use Symfony\Component\HttpFoundation\JsonResponse;
use Common\UtilityBundle\Repository\Pdo\DocumentPdo;
use Symfony\Component\HttpFoundation\Request;

/**
 * This controller is used to manage documents section
 *
 */
class DocumentsController extends Controller
{

    /**
     * This action is used for listing All Documents.
     *
     * @return Template.
     */
    public function allDocumentsAction()
    {
        return $this->render('InternalProfileBundle:Documents:personalDocuments.html.twig', array('tabs' => '', 'title' => $this->get('translator')->trans('ALL_DOCUMENTS'), 'section' => 'all'));
    }

    /**
     * This action is used for listing personal Documents.
     *
     * @return Template.
     */
    public function personalDocumentsAction()
    {
        $urlArray = array('url' => $this->generateUrl('documents_read_all'), 'title' => $this->get('translator')->trans('MARK_ALL_AS_SEEN'));

        return $this->render('InternalProfileBundle:Documents:personalDocuments.html.twig', array('tabs' => '', 'title' => $this->get('translator')->trans('PERSONAL'), 'section' => 'personal', 'urlArray' => $urlArray));
    }

    /**
     * Function to get document details for listing
     *
     * @param Request $request Request object
     *
     * @return JsonResponse
     */
    public function getDocumentsListAction(Request $request, $type)
    {
        $roleId = $request->get('roleId', '');
        $tableSettingFields = $request->get('columns', '');
        $tableColumns = ($tableSettingFields != '') ? json_decode($tableSettingFields, true) : $this->container->getParameter('default_internal_documents_table_settings');
        $menuType = $request->get('menuType'); //'NEW' or 'ALLDOCUMENTS' or others
        $doctype = ($type == 'team') ? 'TEAM' : (($type == 'workgroup') ? 'WORKGROUP' : 'ALL');
        //get columns to be displayed in datatable
        $columnSettings = new InternalDocumentColumnSettings($this->container, $tableColumns, $doctype);
        $aColumns = $columnSettings->getDocColumns();
        //get document query to get column data
        $documentlistClass = new Documentlist($this->container, $doctype);
        $documentlistClass->setColumnsForInternal($aColumns);
        $documentlistClass->setConditionForInternal($type, $roleId);
        $documentlistClass->setFromForInternal();

        if ($menuType == 'NEW') {
            $documentlistClass->addCondition("fdcs.contact_id IS NULL");
        }
        if ($menuType != 'NEW' && $menuType != 'ALLDOCUMENTS') {
            $categoryId = $request->get('categoryId');
            $subCategoryId = $request->get('subCategoryId');
            if ($categoryId != '' && $subCategoryId != '') {
                $documentlistClass->addCondition("fdd.category_id = $categoryId AND fdd.subcategory_id = $subCategoryId");
            }
        }
        $documentlistClass->setGroupBy('fdd.id');
        $documentlistClass->addOrderBy('fdv.updated_at DESC');
        $qry = $documentlistClass->getResult();
        //execute document query
        $documentPdo = new DocumentPdo($this->container);
        $results = $documentPdo->executeDocumentsQuery($qry);
        //populate data for datatable
        $output['aaData'] = $results;
        $output['aaDataType'] = $this->getColumnDataTypes();
        //handle actionmenu only in team/workgroup sections
        if ($type != 'personal') {
            //Need to check if admin
            $contact = $this->get('contact');
            $rightsArray = $contact->checkClubRoleRights($roleId);
            $isAdmin = (in_array('ROLE_GROUP_ADMIN', $rightsArray) || in_array('ROLE_DOCUMENT_ADMIN', $rightsArray)) ? 1 : 0;
            $output['actionMenu'] = $this->getTeamActionMenu($roleId, $type, $isAdmin);
        }

        return new JsonResponse($output);
    }

    /**
     * Function to get column data types to be shown in datatable
     * 
     * @return array $aaDataType Column data types array
     */
    private function getColumnDataTypes()
    {
        $aaDataType = array();
        $aaDataType[] = array('title' => 'edit', 'type' => 'edit');
        $aaDataType[] = array('title' => 'T_DO_LAST_UPDATED', 'type' => 'T_DO_LAST_UPDATED');
        $aaDataType[] = array('title' => 'WG_DO_LAST_UPDATED', 'type' => 'WG_DO_LAST_UPDATED');
        $aaDataType[] = array('title' => 'T_FO_DEPOSITED_WITH', 'type' => 'T_FO_DEPOSITED_WITH');
        $aaDataType[] = array('title' => 'WG_FO_DEPOSITED_WITH', 'type' => 'WG_FO_DEPOSITED_WITH');
        $aaDataType[] = array('title' => 'T_FO_SIZE', 'type' => 'T_FO_SIZE');
        $aaDataType[] = array('title' => 'WG_FO_SIZE', 'type' => 'WG_FO_SIZE');
        $aaDataType[] = array('title' => 'documentName', 'type' => 'documentName');
        $aaDataType[] = array('title' => 'T_FO_VISIBLE_TO', 'type' => 'T_FO_VISIBLE_TO');
        $aaDataType[] = array('title' => 'WG_FO_VISIBLE_TO', 'type' => 'WG_FO_VISIBLE_TO');
        $aaDataType[] = array('title' => 'T_FO_ISPUBLIC', 'type' => 'T_FO_ISPUBLIC');
        $aaDataType[] = array('title' => 'WG_FO_ISPUBLIC', 'type' => 'WG_FO_ISPUBLIC');

        return $aaDataType;
    }

    /**
     * Function to mark all document as seen
     * 
     * @return JsonResponse
     */
    public function markAllReadAction()
    {
        $contactId = $this->container->get('contact')->get('id');
        $documentlistClass = new Documentlist($this->container, 'ALL');
        $documentlistClass->setConditionForInternal('personal');
        $documentlistClass->setColumnsForInternal();
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
        $redirect = $this->generateUrl('documents_personal_list');

        return new JsonResponse(array('status' => 'SUCCESS', 'sync' => 1, 'redirect' => $redirect, 'flash' => $this->get('translator')->trans('ALL_DOCUMENTS_MARKED_AS_SEEN')));
    }

    /**
     * Function for create the action menu.
     *
     * @param int $memberId   teamid/workgroupid
     * @param int $memberType team/workgroup
     *
     * @return array action menu array
     */
    private function getTeamActionMenu($memberId, $memberType = 'team', $adminflag = 0)
    {
        $contactId = $this->get('contact')->get('id');
        $clubId = $this->get('club')->get('id');
        if ($adminflag == 1) {
            $actionMenuNoneSelectedText = array(
                'uploadDocument' => array('isVisibleAlways' => 'true', 'title' => $this->get('translator')->trans('DM_UPLOAD'), 'dataUrl' => ''),
                'editdocument' => array('title' => $this->get('translator')->trans('DOCUMENT_EDIT'), 'dataUrl' => $this->generateUrl('edit_' . $memberType . '_document', array('documentId' => 'documentId')), 'isActive' => 'false'),
                'moveto' => array('title' => $this->get('translator')->trans('DOCUMENT_MOVE_TO'), 'dataUrl' => '', 'hrefLink' => '', 'isActive' => 'false'),
                'documentlog' => array('title' => $this->get('translator')->trans('LOG'), 'dataUrl' => $this->generateUrl('internal_' . $memberType . '_document_log', array('documentId' => 'documentId')), 'isActive' => 'false'),
                'documentdelete' => array('title' => $this->get('translator')->trans('DOCUMENTS_ACTION_MENU_REMOVE'), 'dataUrl' => '', 'hrefLink' => '', 'isActive' => 'false')
            );
            $actionMenuSingleSelectedText = array(
                'uploadDocument' => array('title' => $this->get('translator')->trans('DM_UPLOAD'), 'dataUrl' => '', 'isActive' => 'false'),
                'editdocument' => array('title' => $this->get('translator')->trans('DOCUMENT_EDIT'), 'dataUrl' => $this->generateUrl('edit_' . $memberType . '_document', array('documentId' => 'documentId'))),
                'documentmove' => array('title' => $this->get('translator')->trans('DOCUMENT_MOVE_TO'), 'dataUrl' => ''),
                'documentlog' => array('title' => $this->get('translator')->trans('LOG'), 'dataUrl' => $this->generateUrl('internal_' . $memberType . '_document_log', array('documentId' => 'documentId'))),
                'documentdelete' => array('title' => $this->get('translator')->trans('DOCUMENTS_ACTION_MENU_REMOVE'), 'dataUrl' => '', 'localStorageName' => $memberType . '_' . $clubId . '_' . $contactId)
            );
            $actionMenuMultipleSelectedText = array(
                'uploadDocument' => array('title' => $this->get('translator')->trans('DM_UPLOAD'), 'dataUrl' => '', 'isActive' => 'false'),
                'editdocument' => array('title' => $this->get('translator')->trans('DOCUMENT_EDIT'), 'dataUrl' => $this->generateUrl('edit_' . $memberType . '_document', array('documentId' => 'documentId')), 'isActive' => 'false'),
                'documentmove' => array('title' => $this->get('translator')->trans('DOCUMENT_MOVE_TO'), 'dataUrl' => ''),
                'documentlog' => array('title' => $this->get('translator')->trans('LOG'), 'dataUrl' => $this->generateUrl('internal_' . $memberType . '_document_log', array('documentId' => 'documentId')), 'isActive' => 'false'),
                'documentdelete' => array('title' => $this->get('translator')->trans('DOCUMENTS_ACTION_MENU_REMOVE'), 'dataUrl' => '', 'localStorageName' => $memberType . '_' . $clubId . '_' . $contactId)
            );
        } else {
            $actionMenuNoneSelectedText = $actionMenuSingleSelectedText = $actionMenuMultipleSelectedText = array();
        }
        $menuArray = array('none' => $actionMenuNoneSelectedText, 'single' => $actionMenuSingleSelectedText, 'multiple' => $actionMenuMultipleSelectedText, 'adminFlag' => $adminflag);

        return $menuArray;
    }
}
