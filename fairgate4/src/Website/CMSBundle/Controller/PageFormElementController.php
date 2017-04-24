<?php

/**
 * PageFormElementController.
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
use Website\CMSBundle\Util\FgFormElement;
use Common\UtilityBundle\Util\FgUtility;

/**
 * PageFormElementController
 *
 * This controller is used for form element
 */
class PageFormElementController extends Controller
{

    /**
     * This function is used to create a form.
     * 
     * @param Request $request Request object
     * 
     * @return object View Template Render Object
     */
    public function createFormFieldAction(Request $request)
    {
        $returnArray = array();
        $formId = $request->get('formId');
        $pageId = $request->get('pageId');
        $boxId = $request->get('boxId');
        $sortOrder = $request->get('sortOrder');
        $event = $request->get('event', 'create');
        $club = $this->container->get('club');

        $this->checkUserRightsForPages($pageId);

        $returnArray['pageId'] = $pageId;
        $returnArray['boxId'] = $boxId;
        $returnArray['sortOrder'] = $sortOrder;
        $returnArray['breadCrumb'] = array('back' => $this->generateUrl('website_cms_page_edit', array('pageId' => $pageId)));
        $returnArray['event'] = 'create';

        $returnArray['meta']['clubDefaultLang'] = $club->get('club_default_lang');
        $returnArray['meta']['clubLanguages'] = $club->get('club_languages');

        if ($formId != '') {
            $formElementObj = new FgFormElement($this->container);
            $formData = $formElementObj->getFormElementData($formId, $event);
            $firstFormField = current($formData);
            $returnArray['form']['elementArray'] = $formData;
            $returnArray['form']['name'] = '';
            $returnArray['meta']['existing'] = $formId;
            $returnArray['meta']['formId'] = $firstFormField['formId'];
        } else {
            $returnArray['form']['elementArray'] = array();
            $returnArray['form']['name'] = '';
            $returnArray['meta']['existing'] = 0;
            $returnArray['meta']['formId'] = 'new' . rand();
        }
        $returnArray['formStage'] = 'stage1';
        $returnArray['pageTitle'] = $this->get('translator')->trans('CMS_FORM_ELEMENT_CREATE');
        $returnArray['meta']['event'] = 'create';
        //is superadmin/clubadmin/fedadmin
        $returnArray['meta']['hasAdminRights'] = (!empty($this->container->get('contact')->get('mainAdminRightsForFrontend'))) ? 1 : 0;
        $returnArray['meta']['editSignaturePath'] = $this->generateUrl('club_settings_data', array('offset' => 0, 'clubid' => $club->get('id')));
        //default content and subject transltions in all system languages
        $returnArray['meta']['defaultTranslations'] = $this->getDefaultTranslations();

        return $this->render('WebsiteCMSBundle:PageFormElement:formElement.html.twig', $returnArray);
    }

    /**
     * Method to get default content and subject transltions in system languages of each corresponding languages
     * 
     * @return array $returnArray array of subject and content with key as correspondence langauge and value as translation of 
     * subject and content in corresponding system language
     */
    private function getDefaultTranslations()
    {
        $returnArray = array();
        $clubLangDetails = $this->container->get('club')->get('club_languages_det');
        foreach ($clubLangDetails as $correspondenceLang => $clubLangDet) {
            $returnArray[$correspondenceLang]['subject'] = $this->get('translator')->trans('CMS_FORM_SUBJECT_DEFAULT', array(), 'messages', $clubLangDet['systemLang']);
            $returnArray[$correspondenceLang]['content'] = $this->get('translator')->trans('CMS_FORM_CONTENT_DEFAULT', array(), 'messages', $clubLangDet['systemLang']);
        }

        return $returnArray;
    }

    /**
     * This function is used to create form field.
     * 
     * @param Request $request Request object
     * 
     * @return object View Template Render Object
     */
    public function editFormFieldAction(Request $request)
    {
        $club = $this->container->get('club');
        $em = $this->getDoctrine()->getManager();
        $event = 'edit';

        $returnArray = array();
        $elementId = $request->get('formId');

        $elementObj = $em->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->find($elementId);

        if ($elementObj) {
            $formObj = $elementObj->getForm();
            $formId = $formObj->getId();
            $returnArray['tabs'] = $this->getTabs($elementId, $request->get('source'));
            $returnArray['event'] = $event;
            $returnArray['meta']['clubDefaultLang'] = $club->get('club_default_lang');
            $returnArray['meta']['clubLanguages'] = $club->get('club_languages');

            $elementDetail = $em->getRepository('CommonUtilityBundle:FgCmsPage')->getElementDetails($elementId);
            $this->checkUserRightsForPages($elementDetail[0]['pageId']);

            $formElementObj = new FgFormElement($this->container);
            $formData = $formElementObj->getFormElementData($formId, $event);
            $firstFormField = current($formData);
            $returnArray['form']['elementArray'] = $formData;
            $returnArray['form']['name'] = $firstFormField['formName'];
            $returnArray['meta']['formId'] = $firstFormField['formId'];
            $returnArray['meta']['event'] = 'edit';

            $returnArray['breadCrumb'] = $this->getBackLink($elementId, $request->get('source'));

            $returnArray['pageTitle'] = $this->get('translator')->trans('CMS_FORM_ELEMENT_EDIT');
            $returnArray['formStage'] = $formObj->getFormStage();
            //is superadmin/clubadmin/fedadmin
            $returnArray['meta']['hasAdminRights'] = (!empty($this->container->get('contact')->get('mainAdminRightsForFrontend'))) ? 1 : 0;
            $returnArray['meta']['editSignaturePath'] = $this->generateUrl('club_settings_data', array('offset' => 0, 'clubid' => $club->get('id')));
            $returnArray['meta']['defaultTranslations'] = $this->getDefaultTranslations();

            return $this->render('WebsiteCMSBundle:PageFormElement:formElement.html.twig', $returnArray);
        } else {
            return $this->render('WebsiteCMSBundle:Website:errorPagePreview.html.twig');
        }
    }

    /**
     * This function is used to save form fields to the DB
     * The same function is been used for create/edit save.
     * 
     * @param Request $request Request object
     * 
     * @return object JSON Response Object
     */
    public function saveFormElementAction(Request $request)
    {
        $clubObj = $this->container->get('club');
        $em = $this->getDoctrine()->getManager();
        $event = $request->get('event', 'create');
        $existingForm = $request->get('existing', 0);
        $clubId = $clubObj->get('id');

        $formId = $request->get('formId');
        $pageId = $request->get('pageId');
        $boxId = $request->get('boxId');
        $sortOrder = $request->get('sortOrder');
        $title = $request->get('formname');

        if (($event == 'create') && ($pageId == '' || $boxId == '' || $sortOrder == '')) {
            return new JsonResponse(array('result' => 'error', 'message' => $this->get('translator')->trans('CMS_FORM_SAVE_ERROR')));
        } else if ($event == 'edit') {
            $elementObj = $em->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->findOneBy(array('form' => $formId));
            $elementId = $elementObj->getId();
            $elementDetail = $em->getRepository('CommonUtilityBundle:FgCmsPage')->getElementDetails($elementId);
            $pageId = $elementDetail[0]['pageId'];
        }

        //check if form already exists
        $duplicateFormCount = $em->getRepository('CommonUtilityBundle:FgCmsForms')->checkIfNameExists('form_field', $clubId, $title, $formId);

        if ($duplicateFormCount > 0) {
            return new JsonResponse(array('result' => 'formerror', 'message' => $this->get('translator')->trans('CMS_FORM_NAME_EXISTS')));
        } else {
            //save the content 
            $formElementObj = new FgFormElement($this->container);
            $dataArray = array();
            $dataArray['boxId'] = $boxId;
            $dataArray['pageId'] = $pageId;
            $dataArray['sortOrder'] = $sortOrder;
            $dataArray['formName'] = $title;
            $dataArray['formFieldData'] = $request->get('formFieldData');
            $dataArray['captchaEnabled'] = $request->get('captchaEnabled', 0);
            $dataArray['formId'] = key($request->get('formFieldData'));
            $dataArray['event'] = $event;
            $formId = $formElementObj->saveForm($dataArray);

            if ($existingForm) {
                //save stage 2 & stage 3
                $optionDataArray[$formId] = $em->getRepository('CommonUtilityBundle:FgCmsForms')->getFormOptions($existingForm, $this->container, $clubObj);
                $em->getRepository('CommonUtilityBundle:FgCmsForms')->saveFormStage2($optionDataArray, $formId, $clubObj);
                $em->getRepository('CommonUtilityBundle:FgCmsForms')->saveFormStage3($optionDataArray, $formId, $clubObj);
            }

            $formData = $formElementObj->getFormElementData($formId);
            $firstFormField = current($formData);
            $returnArray['form']['elementArray'] = $formData;
            $returnArray['form']['name'] = $firstFormField['formName'];
            $returnArray['meta']['formId'] = $firstFormField['formId'];
            $returnArray['result'] = 'success';
            $returnArray['message'] = $this->get('translator')->trans('CMS_FORM_SAVE_SUCCESS');

            return new JsonResponse($returnArray);
        }
    }

    /**
     * The fucntion to get the form data, if stage is provided the stage data will be sent
     * 
     * @param Request $request Request object
     * 
     * @return object JSON Response Object
     */
    public function getFormDataAction(Request $request)
    {
        $stage = $request->get('stage', '');
        $formId = $request->get('formId');
        $em = $this->getDoctrine()->getManager();
        $club = $this->container->get('club');

        $formObj = $em->getRepository('CommonUtilityBundle:FgCmsForms')->find($formId);
        if ($formObj) {
            $returnArray['form'] = array();
            switch ($stage) {
                case '1':
                    $formElementObj = new FgFormElement($this->container);
                    $formData = $formElementObj->getFormElementData($formId);
                    $firstFormField = current($formData);
                    $returnArray['form']['stage1']['form']['elementArray'] = $formElementObj->getFormElementData($formId, 'edit');
                    $returnArray['form']['stage1']['form']['name'] = $firstFormField['formName'];
                    $returnArray['meta']['formStage'] = $formObj->getFormStage();
                    break;
                case '2':
                    $dataArray = $em->getRepository('CommonUtilityBundle:FgCmsForms')->getFormOptions($formId, $this->container, $club);
                    $returnArray['form']['stage2'] = $dataArray;
                    $returnArray['meta']['formStage'] = $formObj->getFormStage();
                    break;
                case '3':
                    $dataArray = $em->getRepository('CommonUtilityBundle:FgCmsForms')->getFormOptions($formId, $this->container, $club);
                    $returnArray['form']['stage3'] = $dataArray;
                    $returnArray['meta']['formStage'] = $formObj->getFormStage();
                    break;
                default:
                    break;
            }
        } else {
            $returnArray['error'] = true;
        }

        $returnArray['meta']['clubDefaultLang'] = $club->get('club_default_lang');
        $returnArray['meta']['clubLanguages'] = $club->get('club_languages');

        return new JsonResponse($returnArray);
    }
    /* This function is used to get the form element log of a form.
     * 
     * @param Request $request Request object
     * 
     * @return object View Template Render Object
     */

    public function formElementLogAction(Request $request)
    {
        $clubId = $this->container->get('club')->get('id');
        $elementId = $request->get('formId');

        if ($request->isXmlHttpRequest()) {
            $returnArray['aaData'] = $this->container->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElementLog')->getLogData($elementId, $clubId);
            return new JsonResponse($returnArray);
        } else {

            $elementDetail = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->getElementDetails($elementId);
            $this->checkUserRightsForPages($elementDetail[0]['pageId']);

            $returnArray['pageTitle'] = $this->get('translator')->trans('CMS_FORM_ELEMENT_EDIT');
            $returnArray['breadCrumb'] = $this->getBackLink($elementId, $request->get('source'));
            $returnArray['tabs'] = $this->getTabs($elementId, $request->get('source'));
            $returnArray['elementId'] = $elementId;
            return $this->render('WebsiteCMSBundle:PageFormElement:formElementLog.html.twig', $returnArray);
        }
    }

    /**
     * This function is used to save the stage 2 form field.
     * 
     * @param Request $request Request object
     * 
     * @return object JSON Response Object
     */
    public function saveFormStage2Action(Request $request)
    {
        $formData = $request->get('formData');
        $formId = $request->get('formId');
        $clubObj = $this->container->get('club');

        //check if he has access for the form
        $em = $this->getDoctrine()->getManager();
        $em->getRepository('CommonUtilityBundle:FgCmsForms')->saveFormStage2($formData, $formId, $clubObj);
        $em->getRepository('CommonUtilityBundle:FgCmsForms')->saveFormElementStage($formId, 'stage2');
        return new JsonResponse(array());
    }

    /**
     * This function is used to save the stage 2 form field.
     * 
     * @param Request $request Request object
     * 
     * @return object JSON Response Object
     */
    public function saveFormStage3Action(Request $request)
    {
        $formData = $request->get('formData');
        $formId = $request->get('formId');
        $clubObj = $this->container->get('club');

        //check if he has access for the form
        $em = $this->getDoctrine()->getManager();
        $em->getRepository('CommonUtilityBundle:FgCmsForms')->saveFormStage3($formData, $formId, $clubObj);
        $em->getRepository('CommonUtilityBundle:FgCmsForms')->saveFormElementStage($formId, 'stage3');

        return new JsonResponse(array());
    }

    /**
     * This function is used to list form inquiries.
     * 
     * @param Request $request Request object
     * 
     * @return object View Template Render Object
     */
    public function inquiryListAction(Request $request)
    {
        $club = $this->container->get('club');
        $returnArray = array();
        $elementId = $request->get('formId');
        $elementObj = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->find($elementId);

        if ($elementObj) {
            $elementDetail = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->getElementDetails($elementId);
            $this->checkUserRightsForPages($elementDetail[0]['pageId']);

            $returnArray['meta']['clubDefaultLang'] = $club->get('club_default_lang');
            $returnArray['meta']['clubLanguages'] = $club->get('club_languages');

            $returnArray['tabs'] = $this->getTabs($elementId, $request->get('source'));
            $returnArray['breadCrumb'] = $this->getBackLink($elementId, $request->get('source'));
            $returnArray['pageTitle'] = $this->get('translator')->trans('CMS_FORM_ELEMENT_EDIT');
            $returnArray['actionMenu'] = $this->actionMenuSettings();
            $returnArray['formId'] = $elementId;

            $returnArray['formUploadDirectory'] = FgUtility::getUploadFilePath($club->get('id'), 'form_uploads');
            return $this->render('WebsiteCMSBundle:PageFormElement:formInquiryList.html.twig', $returnArray);
        } else {
            return $this->render('WebsiteCMSBundle:Website:errorPagePreview.html.twig');
        }
    }

    /**
     * This function is used to create action menu.
     *
     * @return Array
     */
    private function actionMenuSettings()
    {
        //none selection begins
        $noneSelectedText['exportCsv'] = array('isVisibleAlways' => 'true', 'title' => $this->get('translator')->trans('CMS_EXPORT_CSV'), 'dataUrl' => '', 'isActive' => 'true', 'className' => 'fg-inquiry-export-csv');
        $noneSelectedText['exportInquiryAttachments'] = array('title' => $this->get('translator')->trans('CMS_EXPORT_ATTACHMENTS'), 'dataUrl' => '', 'isActive' => 'true');
        $noneSelectedText['deleteInquiry'] = array('title' => $this->get('translator')->trans('DELETE'), 'dataUrl' => '', 'isActive' => 'true');
        //single selection begins
        $singleSelectedText['exportCsv'] = array('isVisibleAlways' => 'true', 'title' => $this->get('translator')->trans('CMS_EXPORT_CSV'), 'dataUrl' => '', 'isActive' => 'true', 'className' => 'fg-inquiry-export-csv');
        $singleSelectedText['exportInquiryAttachments'] = array('title' => $this->get('translator')->trans('CMS_EXPORT_ATTACHMENTS'), 'dataUrl' => '', 'isActive' => 'true');
        $singleSelectedText['deleteInquiry'] = array('title' => $this->get('translator')->trans('DELETE'), 'dataUrl' => '', 'isActive' => 'true');
        //multiple selection begins
        $multipleSelectedText['exportCsv'] = array('isVisibleAlways' => 'true', 'title' => $this->get('translator')->trans('CMS_EXPORT_CSV'), 'dataUrl' => '', 'isActive' => 'true', 'className' => 'fg-inquiry-export-csv');
        $multipleSelectedText['exportInquiryAttachments'] = array('title' => $this->get('translator')->trans('CMS_EXPORT_ATTACHMENTS'), 'dataUrl' => '', 'isActive' => 'true');
        $multipleSelectedText['deleteInquiry'] = array('title' => $this->get('translator')->trans('DELETE'), 'dataUrl' => '', 'isActive' => 'true');

        return array('none' => $noneSelectedText, 'single' => $singleSelectedText, 'multiple' => $multipleSelectedText);
    }

    /**
     * Function to get the tabs for the page
     * 
     * @param string $formId The form Id
     * @param int    $sourcePage The field to identify from where the page has come from
     * 
     * @return array
     */
    private function getTabs($formId, $sourcePage)
    {
        $parameterArray = array('formId' => $formId);
        if ($sourcePage == 'page') {
            $parameterArray['source'] = 'page';
        }

        $tabs['cmsFormElementContent'] = array('text' => $this->get('translator')->trans('CMS_TAB_CONTENT'), 'url' => $this->generateUrl('website_cms_form_element_edit', $parameterArray));
        $tabs['cmsFormElementList'] = array('text' => $this->get('translator')->trans('CMS_TAB_FORM_INQUIRIES'), 'url' => $this->generateUrl('website_cms_form_inquiry_form', $parameterArray));
        $tabs['cmsFormElementLog'] = array('text' => $this->get('translator')->trans('CMS_TAB_LOG'), 'url' => $this->generateUrl('website_cms_form_element_log_list', $parameterArray));

        return $tabs;
    }

    /**
     * Function to get the backbutton for the page
     * 
     * @param string $formId The form Id
     * @param int    $sourcePage The field to identify from where the page has come from
     * 
     * @return array
     */
    private function getBackLink($formId, $sourcePage = 'list')
    {
        if ($sourcePage == 'page') {
            $elementDetail = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPage')->getElementDetails($formId);
            $returnArray = array('back' => $this->generateUrl('website_cms_page_edit', array('pageId' => $elementDetail[0]['pageId'])));
        } else {
            $returnArray = array('back' => $this->generateUrl('website_cms_form_inquiry'));
        }

        return $returnArray;
    }

    /**
     * Function to  check an user have access to a page
     *
     * @param Int $pageId    The id of the page that the user have accesses
     *
     * @return void
     */
    private function checkUserRightsForPages($pageId)
    {
        $em = $this->getDoctrine()->getManager();
        $clubObj = $this->container->get('club');
        $contactObj = $this->container->get('contact');

        if ($pageId == '') {
            throw new AccessDeniedException();
        } else {
            $pageObj = $em->getRepository('CommonUtilityBundle:FgCmsPage')->getPageData($pageId);

            if ($pageObj['clubId'] == $clubObj->get('id')) {
                //check pagewise access checking 
                $availableUserRights = $contactObj->get('availableUserRights');
                $adminRights = $clubObj->get('mainAdminRightsForFrontend');
                $isClubAdmin = (in_array('ROLE_USERS', $adminRights)) ? 1 : 0;
                $isCMSAdmin = (in_array('ROLE_CMS_ADMIN', $availableUserRights)) ? 1 : 0;
                $isSuperAdmin = ($contactObj->get('isSuperAdmin') || (($contactObj->get('isFedAdmin')) && ($contactObj->get('type') != 'federation'))) ? 1 : 0;
                if (($isCMSAdmin) || ($isClubAdmin) || ($isSuperAdmin)) {
                    return;
                } else {
                    $tempAccessPageArray = array();
                    $myPageAndNavigation = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:SfGuardUserPage')->getMyPageAndNavigation($clubObj->get('id'), $contactObj->get('id'));
                    foreach ($myPageAndNavigation as $page) {
                        $tempAccessPageArray[] = $page['page_id'];
                    }
                    if (!in_array($pageId, $tempAccessPageArray)) {
                        throw new AccessDeniedException();
                    } else {
                        return;
                    }
                }
            } else {
                throw new AccessDeniedException();
            }
        }
    }
}
