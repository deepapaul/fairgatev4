<?php

/**
 * FormElementInquiriesController
 */
namespace Website\CMSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Website\CMSBundle\Util\FgFormInquiriesSidebar;
use Symfony\Component\HttpFoundation\Request;
use Common\FilemanagerBundle\Util\FgFileManager;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Util\FgSettings;

/**
 * FormElementInquiriesController - For handling for lement inquiries listing
 *
 * @package         FormElementInquiriesController
 * @subpackage      Controller
 * @author          pitsolutions.ch
 * @version         Fairgate V4
 */
class FormElementInquiriesController extends Controller
{

    /**
     * Method to render list inquiry page
     *
     * @return Object View Template Render Object
     */
    public function listFormInquiriesAction()
    {
        $returnArray = array();
        $container = $this->container;
        $club = $container->get('club');
        $returnArray['breadCrumb'] = array();
        $returnArray['actionMenu'] = $this->actionMenuSettings();
        $returnArray['meta']['clubDefaultLang'] = $club->get('club_default_lang');
        $returnArray['meta']['clubLanguages'] = $club->get('club_languages');
        $sidebarDataObj = new FgFormInquiriesSidebar($this->container);
        $sidebarData = $sidebarDataObj->getDataForSidebar();
        $returnArray['sidebarData'] = $sidebarData;
        $returnArray['clubId'] = $club->get('id');
        $returnArray['formUploadDirectory'] = FgUtility::getUploadFilePath($club->get('id'), 'form_uploads');

        return $this->render('WebsiteCMSBundle:FormElementInquiries:formInquiriesList.html.twig', $returnArray);
    }

    /**
     * Method to get form inquiry data for sidebar.
     *
     * @return JsonResponse object
     */
    public function getSidebarDataAction()
    {
        $sidebarDataObj = new FgFormInquiriesSidebar($this->container);
        $result = $sidebarDataObj->getDataForSidebar();

        return new JsonResponse($result);
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
     * Method to get list of form inquiries as json array
     *
     * @param int|null $elementId form Id (when element Id is set, details of that element is returned or details of all forms of that club is returned)
     *
     * @return JsonResponse object
     */
    public function getFormInquiriesAction($elementId = null)
    {
        $em = $this->getDoctrine()->getManager();
        $clubId = $this->container->get('club')->get('id');
        $guestTrans = $this->container->get('translator')->trans('CMS_GUEST');
        $contactLang = $this->container->get('club')->get('default_lang');
        $formattedInquiries = $formInquiries = $em->getRepository('CommonUtilityBundle:FgCmsPageContentElementFormInquiries')->getFormInquiries($clubId, $guestTrans, $elementId, $contactLang);
        $result = array();
        if ($elementId) {
            $formFields = $em->getRepository('CommonUtilityBundle:FgCmsPageContentElementFormFields')->getFormFields($clubId, $contactLang, $elementId);
            $result['formFields'] = array_values($formFields);
            $inquiryDatas = $this->iterateInquiries($formFields, $formInquiries);
            $formattedInquiries = $inquiryDatas['formattedInquiries'];
            $result['hasAttatchments'] = $inquiryDatas['hasAttatchments'];
            $formDetails = $em->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->getFormTitleAndActive($elementId, $contactLang);
            $result['isActiveForm'] = $formDetails['isActive'];
        }
        $result['formTitle'] = ($elementId) ? $formDetails['formTitle'] : '';
        $result['aaData'] = $formattedInquiries;
        $result['iTotalDisplayRecords'] = count($formattedInquiries);
        $result['iTotalRecords'] = count($formattedInquiries);

        return new JsonResponse($result);
    }

    /**
     * Method to get attachments and tile of a particular form
     *
     * @param int    $elementId  elementId
     * @param string $inquiryIds inquiryIds comma seperated ids
     *
     * @return JsonResponse array of form tile and attachments
     */
    private function getFormAttachments($elementId, $inquiryIds)
    {

        $em = $this->getDoctrine()->getManager();
        $clubId = $this->container->get('club')->get('id');
        $contactLang = $this->container->get('club')->get('default_lang');
        $dataArray = array();
        $formFields = $em->getRepository('CommonUtilityBundle:FgCmsPageContentElementFormFields')->getFormFields($clubId, $contactLang, $elementId, true, 'fileupload');
        if (count($formFields) > 0) {
            $formInquiries = $em->getRepository('CommonUtilityBundle:FgCmsPageContentElementFormInquiries')->getFormInquiryDatas($clubId, $elementId, $contactLang, $inquiryIds);
            $formTitle = $formInquiries[0]['formTitle'];
            foreach ($formInquiries as $formInquiry) {
                $formDatas = json_decode($formInquiry['formData'], true);
                //mapping attachment field values
                foreach (array_keys($formFields) as $key) {
                    ($formDatas[$key]) ? ($dataArray[] = $formDatas[$key]) : '';
                }
            }
        }

        return array('attachments' => $dataArray, 'formTitle' => $formTitle);
    }

    /**
     * Method to download attachments from particular form
     *
     * @param Request $request Request object
     * @param int $elementId elementId
     *
     * @return resource $fileObj
     */
    public function downloadFormAttachmentsAction(Request $request, $elementId)
    {
        $inquiryIds = $request->get('inquiryIds');
        $clubId = $this->container->get('club')->get('id');
        $formDatas = $this->getFormAttachments($elementId, $inquiryIds);
        $attachments = $formDatas['attachments'];
        $formTitle = $formDatas['formTitle'];
        $fileObj = new FgFileManager($this->container);
        $fileObj->setCwd("\uploads\\" . $clubId . "\users\\form_uploads");
        $zipFilename = $formTitle . '.zip';
        $randomFilename = substr(md5(rand()), 0, 7) . '.zip';
        $fileObj->zipFiles($attachments, $randomFilename);

        return $fileObj->downloadFile($randomFilename, $zipFilename, 'temp' . DIRECTORY_SEPARATOR);
    }

    /**
     * Method to get datas of a particular inquiry as json array
     *
     * @param int $inquiryId inquiry-Id (when element Id is set, details of that element is returned or details of all forms of that club is returned)
     *
     * @return JsonResponse object
     */
    public function getFormInquiryDatasAction($inquiryId)
    {
        $em = $this->getDoctrine()->getManager();
        $clubId = $this->container->get('club')->get('id');
        $contactLang = $this->container->get('club')->get('default_lang');
        $formattedData = array();
        $formInquiryObj = $em->getRepository('CommonUtilityBundle:FgCmsPageContentElementFormInquiries')->find($inquiryId);
        if ($formInquiryObj) {
            $formDatas = json_decode($formInquiryObj->getFormData(), true);
            $formFields = $em->getRepository('CommonUtilityBundle:FgCmsPageContentElementFormFields')->getFormFields($clubId, $contactLang, $formInquiryObj->getElement()->getId());
            $formattedData = $this->formatInquiries($formFields, $formDatas, false);
        }

        return new JsonResponse($formattedData['formattedData']);
    }

    /**
     * Method to iterate inquiries and format form content of form inquiries (add field names to the array)
     *
     * @param array $formFields    form-Fields
     * @param array $formInquiries form-Inquiries
     *
     * @return array formatted array
     */
    private function iterateInquiries($formFields, $formInquiries)
    {
        $hasAttatchments = 0;
        foreach ($formInquiries as $inquiryKey => $formInquiry) {
            $formDatas = json_decode($formInquiry['formData'], true);
            $formattedData = $this->formatInquiries($formFields, $formDatas, true);
            $formInquiries[$inquiryKey]['formData'] = $formattedData['formattedData'];
            $hasAttatchments += $formattedData['hasAttatchments'];
        }

        return array('formattedInquiries' => $formInquiries, 'hasAttatchments' => $hasAttatchments);
    }

    /**
     * Method to format form content of form inquiries (add field names to the array)
     *
     * @param array   $formFields      form-Fields
     * @param array   $formDatas       form-Inquiries-data
     * @param boolean $showEmptyValues if true, empty values also listed, else avoid that
     *
     * @return array formatted array
     */
    private function formatInquiries($formFields, $formDatas, $showEmptyValues)
    {
        $formattedData = array();
        $hasAttatchments = 0;
        $dateObj = new \DateTime();
        $phpDateFormat = FgSettings::getPhpDateFormat();
        $phpTimeFormat = FgSettings::getPhpTimeFormat();
        foreach ($formDatas as $key => $formData) {
            $fieldValue = '';
            if ($formFields[$key]['fieldId']) {
                switch ($formFields[$key]['fieldType']) {
                    case 'select':
                    case 'radio':
                    case 'checkbox':
                        $fieldValue = $this->getFieldValue($formFields, $formData, $key);
                        break;
                    case 'date':
                        $fieldValue = ($formData) ? $dateObj->createFromFormat('Y-m-d', $formData)->format($phpDateFormat) : '';
                        break;
                    case 'time':
                        $fieldValue = ($formData) ? $dateObj->createFromFormat('H:i', $formData)->format($phpTimeFormat) : '';
                        break;
                    default:
                        $fieldValue = $formData;
                        break;
                }
                if (($showEmptyValues) || ($fieldValue)) {
                    $formattedData[$formFields[$key]['fieldId']] = array('fieldName' => $formFields[$key]['fieldname'],
                        'fieldValue' => str_replace('<script', '<scri&nbsp;pt', (strip_tags($fieldValue))),
                        'fieldValueForPopup' => str_replace('<script', '<scri&nbsp;pt', nl2br(strip_tags($fieldValue))),
                        'fieldType' => $formFields[$key]['fieldType'],
                        'fieldSortOrder' => $formFields[$key]['sortOrder']
                    );
                }
                if ($formFields[$key]['fieldType'] == 'fileupload' && $formData != '') {
                    $hasAttatchments++;
                }
            }
        }

        return array('formattedData' => $formattedData, 'hasAttatchments' => $hasAttatchments);
    }

    /**
     * Method to get field value of checkbox/select field/ radio button
     * 
     * @param array   $formFields form-Fields
     * @param array   $formDatas  form-Inquiries-data
     * @param string  $key        key
     * 
     * @return string field value of checkbox/select field/ radio button
     */
    private function getFieldValue($formFields, $formData, $key)
    {
        $fieldValues = array();
        if (is_array($formData)) {
            foreach ($formData as $formDataOne) {
                ($formFields[$key]['fieldoptions'][$formDataOne]['title']) ? ($fieldValues[$formFields[$key]['fieldoptions'][$formDataOne]['sort']] = $formFields[$key]['fieldoptions'][$formDataOne]['title']) : '';
            }
            ksort($fieldValues);
            $fieldValue = implode(', ', $fieldValues);
        } else {
            $fieldValue = $formFields[$key]['fieldoptions'][$formData]['title'];
        }

        return $fieldValue;
    }

    /**
     * Function is used to delete form inquiry
     *
     * @param Request $request Request object
     *
     * @return object JSON Response Object
     */
    public function deleteInquiryAction(Request $request)
    {
        $inquiryIds = $request->get('inquiryIds');
        $elementId = $request->get('elementId');
        if ($inquiryIds == 'ALL') {
            $inquiryIdArray = array();
        } else {
            $inquiryIdArray = explode(',', $inquiryIds);
        }
        $inquiryIdsString = $inquiryIds == 'ALL' ? '' : $inquiryIds;
        $formDatas = $this->getFormAttachments($elementId, $inquiryIdsString);

        $inquiryCount = count($inquiryIdArray);
        $flash = $flash = ($inquiryCount > 1) ? $this->get('translator')->trans('CMS_FORM_INQUIRY_DELETE_SUCCESS_PLURAL', array('%count%' => $inquiryCount)) : $this->get('translator')->trans('CMS_FORM_INQUIRY_DELETE_SUCCESS_SINGULAR');
        $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElementFormInquiries')->deleteFormInquiries($inquiryIdArray, $elementId);
        //To remove attachment files
        $this->removeAllAttachments($formDatas['attachments']);
        $return = array('status' => 'SUCCESS', 'flash' => $flash, 'noparentload' => true);

        return new JsonResponse($return);
    }

    /**
     * Method is used to delete form attachments files
     *
     * @param Array $attachments array of attachments files
     *
     * @return void
     */
    private function removeAllAttachments($attachments)
    {
        $clubId = $this->container->get('club')->get('id');
        $dir = FgUtility::getUploadFilePath($clubId, 'form_uploads');
        foreach ($attachments as $file) {
            unlink($dir . '\\' . $file);
        }
    }

    /**
     * Function to save form enquires
     *
     * @param Request $request The request object
     * 
     * @return JsonResponse $validation The validated result set
     */
    public function saveFormInquiryAction(Request $request)
    {
        $inquiry = $request->get('inquiry');
        $validation = $this->validateForm($inquiry);
        if ($validation['status'] == 'success') {
            foreach ($validation['files'] as $fieldId => $fileName) {
                $fileNameReq = $this->uploadToFilemanager($fileName);
                $validation['inquiry'][$fieldId] = $fileNameReq;
            }
            $inquiry = json_encode($validation['inquiry']);
            $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsPageContentElementFormInquiries')->saveFormInquiry($inquiry, $validation['elementId']);
        }

        return new JsonResponse($validation);
    }

    /**
     * Validate form inquiry data
     * 
     * @param array $inquiry
     *
     * @return array
     */
    private function validateForm($inquiry)
    {
        $return['status'] = 'success';
        $em = $this->getDoctrine()->getManager();
        foreach ($inquiry as $elementId => $fields) {
            $return['elementId'] = $elementId;
            foreach ($fields as $fieldId => $fieldValue) {
                $return['inquiry'][$fieldId] = $fieldValue;
                $fieldObj = $em->getRepository('CommonUtilityBundle:FgCmsPageContentElementFormFields')->find($fieldId);
                switch ($fieldObj->getFieldType()) {
                    case 'fileupload':
                        $return['files'][$fieldId] = $fieldValue;
                        break;
                    case 'singleline':
                        $return['inquiry'][$fieldId] = str_replace(array("\n", "\t"), '', $fieldValue);
                        break;
                    case 'email':
                        if (filter_var($fieldValue, FILTER_VALIDATE_EMAIL) === false) {
                            $return['status'] = 'error';
                            $return['error'][] = 'invalid email';
                        }
                        break;
                }
            }
        }

        return $return;
    }

    /**
     * Method to upload file and save to file
     *
     * @param string $fileName Temp filename of uploaded file
     *
     * @return string saved file name
     */
    private function uploadToFilemanager($fileName)
    {
        $fileNameopt = explode('#-#', $fileName);
        $tempFileName = $fileNameopt[1];
        $clubId = $this->container->get('club')->get('id');
        $fileNameOriginal = str_replace($fileNameopt[0] . '--', '', $tempFileName);
        $formUploadFolder = FgUtility::getUploadFilePath($clubId, 'form_uploads');
        if (!is_dir($formUploadFolder)) {
            mkdir($formUploadFolder, 0700, true);
        }
        $rootPath = FgUtility::getRootPath($this->container);
        $fileNameReq = FgUtility::getFilename("$rootPath/$formUploadFolder", $fileNameOriginal);

        $fs = new Filesystem();
        $fs->copy("$rootPath/uploads/temp/$tempFileName", "$rootPath/$formUploadFolder/$fileNameReq");

        return $fileNameReq;
    }

    /**
     * This function is used to download attachment files from inquiry listing.
     * This action will execute when user click to attachement link from inquiry list / inquiry details popup. 
     * 
     * @param string $fileName attachment file name
     * 
     * @return resource $fileObj
     */
    public function downloadFilesAction($fileName)
    {
        $fileObj = new FgFileManager($this->container);
        $clubId = $this->container->get('club')->get('id');
        $fileLocation = $clubId . DIRECTORY_SEPARATOR . 'users' . DIRECTORY_SEPARATOR . 'form_uploads' . DIRECTORY_SEPARATOR;

        return $fileObj->downloadFile($fileName, $fileName, $fileLocation);
    }
}
