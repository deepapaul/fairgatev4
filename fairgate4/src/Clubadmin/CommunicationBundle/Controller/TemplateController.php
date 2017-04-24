<?php

/**
 * TemplateController
 *
 * This controller used for managing the newsletter template design
 *
 * @package    ClubadminCommunicationBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
namespace Clubadmin\CommunicationBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Common\UtilityBundle\Util\FgUtility;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Intl\Intl;
use Common\UtilityBundle\Repository\Pdo\SponsorPdo;
use Common\FilemanagerBundle\Util\FileChecking;
use Symfony\Component\HttpFoundation\Request;

class TemplateController extends FgController
{

    /**
     * Function is used for create and edit design templates
     *
     * @return template
     * @throws type
     */
    public function indexAction(Request $request)
    {
        $templateId = $request->get('id', '0');
        $club = $this->get('club');
        $bookedModuleDetails = $club->get('bookedModulesDet');
        $clubId = $this->clubId;
        $backlink = $this->generateUrl('template_list');
        $errFilename = '';
        $breadCrumb = array(
            'back' => $backlink
        );
        if ($templateId != 0) {
            $templateExistinClub = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletterTemplate')->checkTemplateExist($templateId, $this->clubId);
            $permissionObj = $this->fgpermission;
            $accessCheck = $templateExistinClub;
            $permissionObj->checkClubAccess($accessCheck, "nl_template");

            $start = $this->session->get('template_start');
            $length = $this->session->get('template_length');
            $orderAs = $this->session->get('template_order_as');
            $orderBy = $this->session->get('template_order_by');
            $editPath = $this->session->get('template_editPath');
            if (isset($start) && isset($length) && isset($orderAs) && isset($orderBy) && isset($editPath)) {
                $listtemplateDetails = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletterTemplate')->getTemplateList($this->clubId, $start, $length, $orderBy, $orderAs, $editPath);
                $countArray = count($listtemplateDetails);
                foreach ($listtemplateDetails as $key => $value) {
                    if ($value[0] == $templateId) {
                        if (($key - 1) >= 0) {
                            $pre = $listtemplateDetails[$key - 1][0];
                        } else {
                            $pre = '';
                        }
                        if (($key + 1) <= $countArray) {
                            $next = $listtemplateDetails[$key + 1][0];
                        } else {
                            $next = '';
                        }
                        $templateBreadCrumb['previous'] = $pre;
                        $templateBreadCrumb['next'] = $next;
                    }
                }
            }

            $editdetails = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletterTemplate')->edittemplatedetails($templateId, $clubId);

            if (is_array($editdetails)) {
                $detailsarray = array('editdetails' => $editdetails, 'templateid' => $templateId, 'clubId' => $clubId, 'backLink' => $backlink, 'templateBreadCrumb' => $templateBreadCrumb, 'breadCrumb' => $breadCrumb, 'bookedModule' => $bookedModuleDetails);
                $detailsarray['headerfilepath'] = FgUtility::getUploadFilePath($clubId, 'newsletter_header') . "/" . $editdetails['headerImage'];
                $existingfileName = $editdetails['headerImage'];
            } else {
                throw $this->createNotFoundException($this->clubTitle . ' ' . $this->get('translator')->trans('EXCEPTION_HAVE_NO_ACCESS'));
            }
        } else {

            $detailsarray = array('templateid' => $templateId, 'clubId' => $clubId, 'backLink' => $backlink, 'breadCrumb' => $breadCrumb, 'bookedModule' => $bookedModuleDetails);
            $existingfileName = '';
        }

        if ($request->getMethod() == 'POST') {
            $dataArray = json_decode($request->get('catArr'), true);
            $result = $this->saveTemplate($dataArray, $existingfileName, $clubId, $templateId);
            if (isset($result["SUCCESS"])) {
                return new JsonResponse($result["SUCCESS"]);
            } else {
                $errFilename = $this->get('translator')->trans('DESIGN_TEMPLATE_IMAGE_ERROR');
                $detailsarray['editdetails'] = (isset($detailsarray['editdetails'])) ? array_merge($detailsarray['editdetails'], $result["FAILURE"]) : $result["FAILURE"];
            }
        }
        $detailsarray['errFilename'] = $errFilename;

        $fieldLanguages = array();
        $languages = Intl::getLanguageBundle()->getLanguageNames();
        foreach ($this->clubLanguages as $shortName) {
            $fieldLanguages[$shortName] = $languages[$shortName];
        }
        $detailsarray['general']['languages'] = $fieldLanguages;
        $detailsarray['general']['totallanguageCount'] = count($this->clubLanguages);
        $contactObj = $this->container->get("contact");
        $detailsarray['general']['senderEmail'] = $contactObj->get('email');
        $detailsarray['general']['contactname'] = $contactObj->get('nameNoSort');
        $objSponsorPdo = new SponsorPdo($this->container);
        $detailsarray['sponsorServices'] = $objSponsorPdo->getSponsorsServices($this->clubId, $this->clubDefaultLang);
        $detailsarray['sponsorAdAreas'] = $this->em->getRepository('CommonUtilityBundle:FgSmAdArea')->getAdAreas($this->clubId);

        return $this->render('ClubadminCommunicationBundle:Template:createTemplate.html.twig', $detailsarray);
    }

    /**
     * Method to save template
     * 
     * @param array  $formdata            dataArray to in insert
     * @param string $existingfileName    uploaded file name
     * @param int    $clubId              Club Id
     * @param int    $templateId          template Id
     * 
     * @return array
     */
    private function saveTemplate($formdata, $existingfileName, $clubId, $templateId)
    {
        $return = array();
        $uploadDir = FgUtility::getUploadDir();

        $fileOrginalName = $formdata['picture_88'];
        $fileCheck = new FileChecking($this->container);
        $currentPath = $uploadDir . '/temp/' . $fileOrginalName;
        $filename = ($formdata['dropzone_file']) ? $fileCheck->replaceSingleQuotes($formdata['dropzone_file']) : '';
        if ($filename) {
            $uploadPath = $uploadDir . '/' . $clubId . '/admin/newsletter_header';
            $filename = FgUtility::getFilename($uploadPath, $filename);
        }
        $langCount = count($this->clubLanguages);
        if ($langCount == 1) {
            $lang = $this->clubLanguages;
            $singleLang = $lang[0];
        } else {
            $singleLang = '';
        }
        if ($langCount > 1) {
            if (isset($formdata['language'])) {
                $languages = $formdata['language'];
                $formdata['language_selection'] = in_array('selectall', $languages) ? 'ALL' : 'SELECTED';
                if (in_array('selectall', $languages)) {
                    unset($languages[0]);
                }
                $formdata['language'] = $languages;
            }
        } else {
            $formdata['language'] = array($singleLang);
            $formdata['language_selection'] = 'ALL';
        }

        if ($filename != '' || ($existingfileName != '' && $filename == '')) {
            /* Area for handling the upload and naming of images */
            if (($filename != '') && ($existingfileName == '' || $existingfileName != $filename)) {
                $uploadPath = $uploadDir . '/' . $clubId . '/admin/newsletter_header';

                $this->get('fg.avatar')->createUploadDirectories($uploadPath);
                $filename = FgUtility::getFilename($uploadPath, $filename);

                if (file_exists($uploadDir . '/temp/' . $fileOrginalName)) {
                    copy($currentPath, $uploadPath . '/' . $filename);
                    unlink($currentPath);
                }


                if ($existingfileName != '') {
                    unlink($uploadPath . '/' . $existingfileName);
                }
                FgUtility::getResizeImages($this->container, $uploadPath, $filename, 'communication');
                $formdata['picture_88'] = $filename;
            }
            /* Ends here */
            $contactId = $this->contactId;
            if ($templateId > 0) {
                $updatetemplate = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletterTemplate')->updatetemplate($contactId, $formdata, $templateId);
                $redirecturl = $this->generateUrl('template_edit', array('id' => $templateId));
                $tempId = $templateId;
            } else {
                $createTemplate = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletterTemplate')->createtemplate($clubId, $contactId, $formdata);
                $redirecturl = $this->generateUrl('template_edit', array('id' => $createTemplate));
                $tempId = $createTemplate;
            }
            $return["SUCCESS"] = array('status' => 'SUCCESS', 'noparentload' => 1, 'templateId' => $tempId, 'flash' => $this->get('translator')->trans('DESIGN_TEMPLATE_SAVED_SUCCESS'));
        } else {
            $detailsarray['headerImage'] = '';
            $detailsarray['title'] = $formdata['template_name'];
            $detailsarray['articleDisplay'] = $formdata['displayType'];
            $detailsarray['colorBg'] = $formdata['background'];
            $detailsarray['colorStdText'] = $formdata['general_text'];
            $detailsarray['colorTitleText'] = $formdata['heading_text'];
            $detailsarray['colorTocBg'] = $formdata['background_table'];
            $detailsarray['colorTocText'] = $formdata['text_table'];
            $return["FAILURE"] = $detailsarray;
        }

        return $return;
    }

    /**
     * Function is used for setting listing templates parameters in datatable
     *
     * @return JsonResponse
     */
    public function getemplateListAction(Request $request)
    {
        $start = $request->get('start', 0);
        $length = $request->get('length', 50);
        $order = $request->get('order');
        $columns = $request->get('columns');
        $orderAs = $order[0]['dir'];
        $orderBy = $columns[$order[0]['column']]['name'];
        $editPath = $this->generateUrl('template_edit', array('id' => 'id'));
        $this->removeprevNextSessionSet();
        $this->prevNextSessionSet($start, $length, $orderBy, $orderAs, $editPath);
        $totalTemplateCount = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletterTemplate')->getTemplateCount($this->clubId);
        $listtemplateDetails = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletterTemplate')->getTemplateList($this->clubId, $start, $length, $orderBy, $orderAs, $editPath);
        $return['aaData'] = $listtemplateDetails;
        $return["iTotalRecords"] = $totalTemplateCount ? $totalTemplateCount : 0;
        $return["iTotalDisplayRecords"] = $totalTemplateCount ? $totalTemplateCount : 0;

        return new JsonResponse($return);
    }

    /**
     * Function is used for listing templates
     *
     * @return template
     */
    public function listAction()
    {
        $breadCrumb = array(
            'breadcrumb_data' => array()
        );
        $totalTemplates = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletterTemplate')->getTemplateCount($this->clubId);

        return $this->render('ClubadminCommunicationBundle:Template:templateList.html.twig', array('breadCrumb' => $breadCrumb, 'totalTemplates' => $totalTemplates));
    }

    /**
     * Template for duplicate template
     *
     * @return template
     */
    public function duplicateteDeletemplateAction(Request $request)
    {
        $actionType = $request->get('actionType');
        if ($actionType === 'templateduplicate') {
            $templateDesc = 'CONFIRM_DUPLICATE_TEMPLATE';
            $templateTitle = 'CONFIRM_DUPLICATE_TEMPLATE_TITLE';
        } else if ($actionType === 'templatedelete') {
            $templateDesc = 'CONFIRM_DELETE_TEMPLATE';
            $templateTitle = 'CONFIRM_DELETE_TEMPLATE_TITLE';
        }
        $selActionType = $request->get('selActionType') ? $request->get('selActionType') : '';
        $return = array('actionType' => $actionType, 'clubId' => $this->clubId, 'clubType' => $this->clubType, 'selActionType' => $selActionType, 'transDesc' => $this->get('translator')->trans($templateDesc), 'templateTitle' => $this->get('translator')->trans($templateTitle));

        return $this->render('ClubadminCommunicationBundle:Template:confirmDuplicate.html.twig', $return);
    }

    /**
     * Duplicate template save
     *
     * @return template
     */
    public function saveDuplicateDeleteTemplateAction(Request $request)
    {
        $selectedId = json_decode($request->get('selectedId', '0'));
        $fromPage = $request->get('fromPage', '');
        $actionType = $request->get('actionType', '');
        $copyText = $this->get('translator')->trans('DUPLICATE_TEMPLATE_COPY');
        if ($request->getMethod() == 'POST') {
            $flashMsg = '';
            if (count($selectedId) > 0) {
                if ($actionType === 'templateduplicate') {
                    $duplicateDetails = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletterTemplate')->getDuplicateDetails($selectedId[0], $copyText, $this->clubId, $this->contactId);
                    $flashMsg = 'TEMPLATE_DUPLICATED_SUCCESSFULLY';
                } else if ($actionType === 'templatedelete') {
                    $idCount = count($selectedId);
                    $deleteDetails = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletterTemplate')->deleteTemplate($selectedId, $this->clubId);
                    if ($idCount > 1) {
                        $flashMsg = 'TEMPLATES_DELETED_SUCCESSFULLY';
                    } else {
                        $flashMsg = 'TEMPLATE_DELETED_SUCCESSFULLY';
                    }
                }
                if ($fromPage == 'template_list') {
                    $redirect = $this->generateUrl('template_list');

                    return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans($flashMsg), 'noparentload' => 1));
                }
            }
        }
    }

    /**
     * Function for previous next in template edit page
     *
     * @param int $start Start offset
     * @param int $length Limit
     * @param string $orderBy Order parameter
     * @param string $orderAs Order parameter
     * @param string $editPath Edit path
     *
     */
    public function prevNextSessionSet($start, $length, $orderBy, $orderAs, $editPath)
    {
        $this->session->set('template_start', $start);
        $this->session->set('template_length', $length);
        $this->session->set('template_order_by', $orderBy);
        $this->session->set('template_order_as', $orderAs);
        $this->session->set('template_editPath', $editPath);
    }

    /**
     * Function for remove session for previous next functionality in template edit page
     *
     */
    public function removeprevNextSessionSet()
    {
        $this->session->remove('template_start');
        $this->session->remove('template_length');
        $this->session->remove('template_order_by');
        $this->session->remove('template_order_as');
        $this->session->remove('template_editPath');
    }

    /**
     * Function to get the sponsor contents
     *
     * @param int $templateId templateId 
     * 
     * @return JsonResponse
     */
    public function getSponsorContentAction($templateId)
    {
        if ($templateId != '') {
            $templateSponsorContents = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletterTemplate')->getNewsletterTemplateSponsorContents($this->clubId, $templateId, true);
        }

        return new JsonResponse($templateSponsorContents);
    }
}
