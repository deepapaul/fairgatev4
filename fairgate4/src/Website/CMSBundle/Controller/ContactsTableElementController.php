<?php

/**
 * ContactsTableElementController.
 *
 * @package 	Website
 * @subpackage 	CMSBundle
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 *
 */
namespace Website\CMSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Website\CMSBundle\Util\FgContactTable;
use Common\UtilityBundle\Repository\Pdo\CmsPdo;
use Common\UtilityBundle\Util\FgUtility;

/**
 * ContactsTableElementController
 *
 * This controller is used for handling various functionalities of contacts table element and contact portraits element
 */
class ContactsTableElementController extends Controller
{

    /**
     * This function is used to create a contacts table and contacts portrait element.
     *
     * @param Request $request Request object
     *
     * @return object View Template Render Object
     */
    public function contactsTableStage1Action(Request $request)
    {
        $returnArray = array();
        $pageId = $request->get('pageId');
        $boxId = $request->get('boxId');
        $sortOrder = $request->get('sortOrder');
        $elementId = $request->get('elementId');
        $colSize = $request->get('colSize');
        $elementType = $request->get('elementType');
        $tableId = 0;
        $clubObj = $this->container->get('club');
        $em = $this->getDoctrine()->getManager();
        if ($this->hasRightsToAccessPage($pageId)) {
            if ($elementId != 0) {
                $tableId = $em->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->find($elementId)->getTable()->getId();
            }
            if ($tableId != 0) {
                //edit
                $returnArray['tableId'] = $tableId;
                $returnArray['event'] = 'edit';
                $returnArray['log'] = '1';
                $returnArray['elementId'] = $elementId;
                $returnArray['pageTitle'] = ($elementType === 'table') ? $this->get('translator')->trans('EDIT_CONTACTS_TABLE_ELEMENT') : $this->get('translator')->trans('EDIT_CONTACT_PORTRAITS_ELEMENT');
                $returnArray['pageId'] = $pageId;
                $returnArray['tabs'] = $this->getTabs($elementId, $elementType);
            } else {
                //create
                $returnArray['tableId'] = 'new' . rand();
                $returnArray['pageId'] = $pageId;
                $returnArray['boxId'] = $boxId;
                $returnArray['sortOrder'] = $sortOrder;
                $returnArray['elementId'] = $elementId;
                $returnArray['event'] = 'create';
                $returnArray['log'] = '0';
                $returnArray['pageTitle'] = ($elementType === 'table') ? $this->get('translator')->trans('CREATE_CONTACTS_TABLE_ELEMENT') : $this->get('translator')->trans('CREATE_CONTACT_PORTRAITS_ELEMENT');
            }
            $returnArray['breadCrumb'] = array('back' => $this->generateUrl('website_cms_page_edit', array('pageId' => $pageId)));
            $returnArray['clubDefaultLang'] = $clubObj->get('club_default_lang');
            $returnArray['contactLang'] = $clubObj->get('default_lang');
            $returnArray['systemLang'] = $clubObj->get('default_system_lang');
            $returnArray['clubLangDetails'] = $clubObj->get('club_languages_det');
            $returnArray['clubLanguages'] = $clubObj->get('club_languages');
            $returnArray['data'] = $this->getContactsTableStage1Data($tableId);
            $returnArray['columnData'] = $this->getContactTableColumnOptions();
            $returnArray['filterData'] = $this->getContactTableFilterOptions();
            $returnArray['trans'] = $this->getStaticTranslations();
            $returnArray['wizardStage'] = $returnArray['data']['stage'];
            $returnArray['colSize'] = $colSize;
            $returnArray['contactFieldDetails'] = $this->container->get('club')->get('contactFields');
            if ($elementType !== 'table') {
                $returnArray['uploadPath']['fileuploadPath'] = FgUtility::getUploadFilePath('**clubId**', 'contactfield_file');
                $returnArray['uploadPath']['imageuploadPath'] = FgUtility::getUploadFilePath('**clubId**', 'contactfield_image');
                $returnArray['uploadPath']['profilePic'] = FgUtility::getUploadFilePath('**clubId**', 'profilepic');
                $returnArray['uploadPath']['companyLogo'] = FgUtility::getUploadFilePath('**clubId**', 'companylogo');
                $returnArray['uploadPath']['placeholderImage'] = FgUtility::getUploadFilePath('**clubId**', 'cms_portrait_placeholder');
            }

            $template = ($elementType === 'table') ? 'WebsiteCMSBundle:ContactsTableElement:wizard.html.twig' : 'WebsiteCMSBundle:ContactPortraitsElement:wizard.html.twig';

            return $this->render($template, $returnArray);
        }
    }

    /**
     * Function to check whether an user have access to a page
     *
     * @param int $pageId The id of the page to check user access
     *
     * @return int 1 if user has access else throws exception
     * @throws AccessDeniedException
     */
    private function hasRightsToAccessPage($pageId)
    {
        $em = $this->getDoctrine()->getManager();
        $clubObj = $this->container->get('club');
        $contactObj = $this->container->get('contact');

        if ($pageId != '') {
            $pageObj = $em->getRepository('CommonUtilityBundle:FgCmsPage')->getPageData($pageId);
            if ($pageObj['clubId'] == $clubObj->get('id')) {
                //check pagewise access checking
                $availableUserRights = $contactObj->get('availableUserRights');
                $isSuperAdmin = ($contactObj->get('isSuperAdmin') || $contactObj->get('isFedAdmin')) ? 1 : 0;
                if ((!in_array('ROLE_CMS_ADMIN', $availableUserRights)) && (!in_array('ROLE_USERS', $availableUserRights)) && (!$isSuperAdmin)) {
                    $tempAccessPageArray = array();
                    $myPageAndNavigation = $em->getRepository('CommonUtilityBundle:SfGuardUserPage')->getMyPageAndNavigation($clubObj->get('id'), $contactObj->get('id'));
                    foreach ($myPageAndNavigation as $page) {
                        $tempAccessPageArray[] = $page['page_id'];
                    }
                    if (!in_array($pageId, $tempAccessPageArray)) {
                        throw new AccessDeniedException();
                    }
                }
            }
        }

        return 1;
    }

    /**
     * This function is used to get the filter details
     *
     * @param Request $request The request object
     *
     * @return JsonResponse $filterData Array of filter ids and names
     */
    public function getFiltersAction(Request $request)
    {
        $filterType = ($request->get('filter_type') == 'contact') ? 'general' : 'sponsor';
        $filterData = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgFilter')->getSavedFilterdata($this->container->get('club')->get('id'), $filterType);

        return new JsonResponse($filterData);
    }

    /**
     * This function is used to get the contacts table and contacts portrait element stage 1 data
     * 
     * @param Request $request The request object
     * 
     * @return JsonResponse Get data for populating wizrd stage 1
     */
    public function contactsTableGetStage1Action(Request $request)
    {
        $tableId = $request->get('tableId');
        $tableDetails = $this->getContactsTableStage1Data($tableId);

        return new JsonResponse($tableDetails);
    }

    /**
     * This function is used to get the contacts table and contacts portrait element stage 1 data
     * 
     * @param int $tableId Contact table id
     * 
     * @return array $returnArray Data for building wizard stage 1 content
     */
    private function getContactsTableStage1Data($tableId)
    {
        $em = $this->getDoctrine()->getManager();
        $clubObj = $this->container->get('club');
        $contactObj = $this->container->get('contact');
        $availableUserRights = $contactObj->get('availableUserRights');
        $returnArray = $em->getRepository('CommonUtilityBundle:FgCmsContactTable')->getContactTableStage1Data($this->container, $clubObj, $tableId);

        $returnArray['contactFilters'] = $em->getRepository('CommonUtilityBundle:FgFilter')->getSavedFilterdata($clubObj->get('id'));
        $returnArray['sponsorFilters'] = $em->getRepository('CommonUtilityBundle:FgFilter')->getSavedFilterdata($clubObj->get('id'), 'sponsor');
        $returnArray['hasContactModuleRights'] = (in_array('ROLE_CONTACT', $availableUserRights)) ? 1 : 0;
        $returnArray['hasSponsorModuleRights'] = (in_array('ROLE_SPONSOR', $availableUserRights)) ? 1 : 0;
        $returnArray['hasSponsorModule'] = (in_array('sponsor', $clubObj->get('bookedModulesDet'))) ? 1 : 0;

        return $returnArray;
    }

    /**
     * This function is used to save the contact table and contacts portrait element stage 1 table contacts 
     * 
     * @param Request $request The request object
     * 
     * @return JsonResponse Wizard stage 1 save status
     */
    public function saveTableContactsAction(Request $request)
    {
        $dataArray = $request->get('contactData');
        $dataArray['tableId'] = $request->get('tableId');
        $dataArray['pageId'] = $request->get('pageId');
        $dataArray['boxId'] = $request->get('boxId');
        $dataArray['elementId'] = $request->get('elementId');
        $dataArray['sortOrder'] = $request->get('sortOrder');
        $dataArray['event'] = $request->get('event');
        $dataArray['elementType'] = $request->get('elementType');

        if (($dataArray['event'] == 'create') && ($dataArray['pageId'] == '' || $dataArray['boxId'] == '' || $dataArray['sortOrder'] == '')) {
            return new JsonResponse(array('result' => 'error', 'message' => $this->get('translator')->trans('CMS_CONTACTS_TABLE_ELEMENT_SAVE_ERROR')));
        }

        //save the content
        $contactTableObj = new FgContactTable($this->container);
        $tableId = $contactTableObj->saveContactTableStage1($dataArray);

        $returnArray['data'] = $this->getContactsTableStage1Data($tableId);
        $returnArray['tableId'] = $tableId;
        $returnArray['result'] = 'success';
        $returnArray['message'] = ($dataArray['elementType'] == 'table') ? $this->get('translator')->trans('CMS_CONTACTS_TABLE_ELEMENT_SAVE_SUCCESS') : $this->get('translator')->trans('CMS_PORTRAIT_ELEMENT_CONTACTS_SAVED_SUCCESSFULLY');

        return new JsonResponse($returnArray);
    }

    /**
     * This function is used to get the contact table column stage2
     * 
     * @param Request $request The request object
     *
     * @return JsonResponse Data for populating wizard stage 2
     */
    public function contactsTableGetStage2Action(Request $request)
    {
        $tableId = $request->get('tableId');
        $em = $this->getDoctrine()->getManager();
        $data = $em->getRepository('CommonUtilityBundle:FgCmsContactTableColumns')->getColumnData($tableId);
        $stage = $em->getRepository('CommonUtilityBundle:FgCmsContactTable')->find($tableId)->getStage();
        $return = array('data' => $data, 'stage' => $stage);

        return new JsonResponse($return);
    }

    /**
     * This function is used to save table columns
     * 
     * @param Request $request The request object
     *
     * @return JsonResponse Wizard stage 2 save status
     */
    public function saveTableColumnsAction(Request $request)
    {
        $table = $request->get('table');
        $formatArray = $request->get('jsonData');

        $resultSuccess = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsContactTableColumns')->saveContactTableColumns($formatArray, $table, $this->container);

        $tableObj = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->findOneBy(array('table' => $table));
        $elementId = $tableObj->getId();
        $pageId = $tableObj->getBox()->getColumn()->getContainer()->getPage()->getId();
        $contactId = $this->container->get('contact')->get('id');
        $logArray[] = "('$elementId', '$pageId', 'element', 'changed', '', '', now(), $contactId)";

        $cmsPdo = new CmsPdo($this->container);
        $cmsPdo->saveLog($logArray);

        if ($resultSuccess) {
            return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('COLUMNS_SAVED_SUCCESSFULLY')));
        } else {
            return new JsonResponse(array('status' => 'SUCCESS'));
        }
    }

    /**
     * This function is used to get the contact table column stage3
     * 
     * @param Request $request The request object
     *
     * @return JsonResponse Data for populating wizard stage 3
     */
    public function contactsTableGetStage3Action(Request $request)
    {
        $tableId = $request->get('tableId');
        $em = $this->getDoctrine()->getManager();
        $club = $this->container->get('club');
        $clubLanguage = $club->get('club_default_lang');
        $data = $em->getRepository('CommonUtilityBundle:FgCmsContactTableFilter')->getTableFilterDataArray($tableId, $clubLanguage);
        $stage = $em->getRepository('CommonUtilityBundle:FgCmsContactTable')->find($tableId)->getStage();
        $return = array('data' => $data, 'stage' => $stage);

        return new JsonResponse($return);
    }

    /**
     * This function is used to save table filters
     * 
     * @param Request $request The request object
     *
     * @return JsonResponse Wizard stage 3 save status
     */
    public function saveTableFiltersAction(Request $request)
    {
        $table = $request->get('table');
        $formatArray = $request->get('jsonData');
        $stage = $request->get('stage');
        $elementType = $request->get('elementType');

        $resultSuccess = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsContactTableFilter')->saveContactFilterData($formatArray, $table, $this->container, $stage);

        $tableObj = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->findOneBy(array('table' => $table));
        $elementId = $tableObj->getId();
        $pageId = $tableObj->getBox()->getColumn()->getContainer()->getPage()->getId();
        $contactId = $this->container->get('contact')->get('id');
        $logArray[] = "('$elementId', '$pageId', 'element', 'changed', '', '', now(), $contactId)";

        $cmsPdo = new CmsPdo($this->container);
        $cmsPdo->saveLog($logArray);

        if ($resultSuccess) {
            $flashMsgTrans = ($elementType == 'table') ? 'CMS_CONTACTS_TABLE_FILTERS_SAVED_SUCCESSFULLY' : 'CMS_PORTRAIT_ELEMENT_CONTACTS_SAVED_SUCCESSFULLY';
            return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans($flashMsgTrans)));
        } else {
            return new JsonResponse(array('status' => 'ERROR'));
        }
    }

    /**
     * This function is used to get the json for populating contact table 
     * element column options
     * 
     * @return array $data The json data
     */
    public function getContactTableColumnOptions()
    {
        $contactTableObj = new FgContactTable($this->container);

        return $contactTableObj->getContactTableColumnOptions();
    }

    /**
     * This function is used to get the contact table column stage4
     * 
     * @param Request $request The request object
     *
     * @return JsonResponse Data for populating wizard stage 4
     */
    public function contactsTableGetStage4Action(Request $request)
    {
        $tableId = $request->get('tableId');
        $em = $this->getDoctrine()->getManager();
        $data = $em->getRepository('CommonUtilityBundle:FgCmsContactTable')->getContactTableAppearance($tableId);
        $stage = $em->getRepository('CommonUtilityBundle:FgCmsContactTable')->find($tableId)->getStage();
        $return = array('data' => $data, 'stage' => $stage);

        return new JsonResponse($return);
    }

    /**
     * This function is used to save table appearance
     * 
     * @param Request $request The request object
     *
     * @return JsonResponse Wizard stage 4 save status
     */
    public function saveTableAppearanceAction(Request $request)
    {
        $formArray = $request->request->all();
        $table = $request->get('tableId');
        $tableId = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsContactTable')->saveContactTableAppearance($formArray, $table);

        $tableObj = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->findOneBy(array('table' => $table));
        $elementId = $tableObj->getId();
        $pageId = $tableObj->getBox()->getColumn()->getContainer()->getPage()->getId();
        $contactId = $this->container->get('contact')->get('id');
        $logArray[] = "('$elementId', '$pageId', 'element', 'changed', '', '', now(), $contactId)";

        $cmsPdo = new CmsPdo($this->container);
        $cmsPdo->saveLog($logArray);

        return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('CONTACT_TABLE_APPEARANCE_SAVED_SUCCESSFULLY'), 'tableId' => $tableId));
    }
    /* This function is used to get the form element log of a contact table and contact portrait element.
     * 
     * @param Request $request Request object
     * 
     * @return object View Template Render Object
     */

    public function contacttableElementLogAction(Request $request)
    {
        $clubId = $this->container->get('club')->get('id');
        $elementId = $request->get('elementId');
        $elementType = $request->get('elementType');

        if ($request->isXmlHttpRequest()) {
            $returnArray['aaData'] = $this->container->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElementLog')->getLogData($elementId, $clubId);
            return new JsonResponse($returnArray);
        } else {
            $elementDetail = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->getElementDetails($elementId);
            $returnArray['breadCrumb'] = array('back' => $this->generateUrl('website_cms_page_edit', array('pageId' => $elementDetail[0]['pageId'])));

            if ($this->hasRightsToAccessPage($elementDetail[0]['pageId'])) {
                $returnArray['tabs'] = $this->getTabs($elementId, $elementType);
                $returnArray['elementId'] = $elementId;
                $returnArray['pageId'] = $elementDetail[0]['pageId'];
                $returnArray['pageTitle'] = ($elementType == 'table') ? $this->get('translator')->trans('EDIT_CONTACTS_TABLE_ELEMENT') : $this->get('translator')->trans('EDIT_CONTACT_PORTRAITS_ELEMENT');
                $returnArray['elementType'] = $elementType;

                return $this->render('WebsiteCMSBundle:ContactsTableElement:ContactsTableElementLog.html.twig', $returnArray);
            }
        }
    }

    /**
     * This function is used to get the json for populating contact table 
     * element filter options
     * 
     * @return array $data The json data
     */
    private function getContactTableFilterOptions()
    {
        $contactTableObj = new FgContactTable($this->container);
        
        return $contactTableObj->getContactTableFilterOptions();
    }

    /**
     * This function is used to get static translations for the contact table wizard
     * 
     * @return array $data
     */
    private function getStaticTranslations()
    {
        $translatableTerms = array('CMS_CONTACT_TABLE_FILTER_CONTACTFIELD_SELECT_PLACEHOLDER');
        $returnArray = array();

        $clubLangDetails = $this->container->get('club')->get('club_languages_det');
        foreach ($translatableTerms as $term) {
            foreach ($clubLangDetails as $detail) {
                $returnArray[$term][$detail['systemLang']] = $this->get('translator')->trans($term, array('%field%' => '**placeholder**'), 'messages', $detail['systemLang']);
            }
        }
        return $returnArray;
    }

    /**
     * Function to get the tabs for contacts table and contacts portrait element edit page
     * 
     * @param int    $elementId   The Id of the contact table element
     * @param string $elementType Element type 'table' or 'portrait'
     * 
     * @return array $tabs The tab details
     */
    private function getTabs($elementId, $elementType = 'table')
    {
        $tabs['cmsFormElementContent'] = array('text' => $this->get('translator')->trans('CMS_TAB_CONTENT'), 'url' => '#');
        $logUrl = ($elementType == 'table') ? $this->generateUrl('website_cms_contacttable_element_log_list', array('elementId' => $elementId)) : $this->generateUrl('website_cms_contactportrait_element_log_list', array('elementId' => $elementId));
        $tabs['cmsFormElementLog'] = array('text' => $this->get('translator')->trans('CMS_TAB_LOG'), 'url' => $logUrl);

        return $tabs;
    }
}
