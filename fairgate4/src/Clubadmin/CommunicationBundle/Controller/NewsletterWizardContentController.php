<?php

namespace Clubadmin\CommunicationBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Common\UtilityBundle\Repository\Pdo\SponsorPdo;
use Common\FilemanagerBundle\Util\FileChecking;
use Common\UtilityBundle\Util\FgUtility;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use Internal\ArticleBundle\Util\ArticleData;

/**
 * RecipientsController
 *
 * Controller for newsletter wizard contents handling
 *
 * @package    ClubadminCommunicationBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
class NewsletterWizardContentController extends FgController
{

    /**
     * This action is used for listing Recipients List.
     *
     * @return array Data array.
     */
    public function indexAction(Request $request)
    {
        $newsletterId = $request->get('newsletterId');
        $club = $this->get('club');
        $data = array();
        $bookedModuleDetails = $club->get('bookedModulesDet');
        $newsletterObj = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletter')->findOneBy(array('id' => $newsletterId, 'club' => $this->clubId));
        if (!$newsletterObj) {
            throw $this->createNotFoundException($this->clubTitle);
        }
        $wizardStep = $newsletterObj->getStep();
        $objSponsorPdo = new SponsorPdo($this->container);
        $sponsorServices = $objSponsorPdo->getSponsorsServices($this->clubId, $this->clubDefaultLang);
        $sponsorAdAreas = $this->getAdAreasDetails();
        $bookedModules = $this->container->get('club')->parameters['bookedModulesDet'];
        $hasInternalRights = ( in_array('frontend1', $bookedModules) ) ? 1 : 0;
        if ($newsletterObj->getNewsletterType() == 'EMAIL') { //simplemail
            $data['content'] = $newsletterObj->getEmailContent();
            $content = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletterContent')->findOneByNewsletter($newsletterId);
            if ($content) {
                $data['signature'] = $content->getContentType() == 'OTHER' ? $content->getIntroClosingWords() : '';
            }
            $backUrl = $this->generateUrl('nl_simplemail_recepients', array('newsletterId' => $newsletterId));
            $breadCrumb = array('breadcrumb_data' => array(), 'back' => $this->generateUrl('newsletter_simplemailings'));

            return $this->render('ClubadminCommunicationBundle:Newsletterwizard:simplemailContent.html.twig', array('step' => 3, 'pageType' => 'simplemail', 'newsletterId' => $newsletterId, 'bookedModule' => $bookedModuleDetails, 'data' => $data, 'backUrl' => $backUrl, 'breadCrumb' => $breadCrumb, 'wizardStep' => $wizardStep, "sponsorServices" => $sponsorServices, "sponsorAdAreas" => $sponsorAdAreas, 'hasInternalRights' => $hasInternalRights, 'clubId' => $this->clubId));
        } else {
            $backUrl = $this->generateUrl('nl_newsletter_recepients', array('newsletterId' => $newsletterId));
            $breadCrumb = array('breadcrumb_data' => array(), 'back' => $this->generateUrl('newsletter_mailings'));

            return $this->render('ClubadminCommunicationBundle:Newsletterwizard:newsletterContent.html.twig', array('step' => 3, 'pageType' => 'newsletter', 'newsletterId' => $newsletterId, 'bookedModule' => $bookedModuleDetails, 'data' => $data, 'backUrl' => $backUrl, 'breadCrumb' => $breadCrumb, 'wizardStep' => $wizardStep, "sponsorServices" => $sponsorServices, "sponsorAdAreas" => $sponsorAdAreas, 'hasInternalRights' => $hasInternalRights, 'clubId' => $this->clubId));
        }
    }

    /**
     * Method to get Ad areas details
     * @return array $adAreas
     */
    private function getAdAreasDetails()
    {
        $adAreas = $this->em->getRepository('CommonUtilityBundle:FgSmAdArea')->getAdAreas($this->clubId);
        if (count($adAreas) > 0) {
            for ($i = 0; $i < count($adAreas); $i++) {
                $adAreas[$i]['adTitle'] = $adAreas[$i]['isSystem'] == 1 ? $this->get('translator')->trans('SM_AD_AREA_GENERAL') : $adAreas[$i]['adTitle'];
            }
        }

        return $adAreas;
    }

    /**
     * This action is used to save newsletter contents.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function saveContentsAction(Request $request)
    {
        $type = $request->get('level1');
        if ($request->getMethod() == 'POST') {
            $data['newsletterId'] = $request->get('newsletterId');
            $showNext = $request->get('showNext');
            $data['clubId'] = $this->clubId;
            $dataArray = json_decode($request->get('catArr'), true);
            $fileCheck = new FileChecking($this->container);
            foreach ($dataArray['images'] as $key => $image) {
                $dataArray['images'][$key]['filename'] = $fileCheck->replaceSingleQuotes($image['filename']);
            }
            if ($type == 'simplemail') {
                $this->em->getRepository('CommonUtilityBundle:FgCnNewsletterContent')->saveSimplemailContent($dataArray, $data, $this->container);
            } else {
                $this->em->getRepository('CommonUtilityBundle:FgCnNewsletterContent')->saveNewsletterContent($dataArray, $data, $this->container);
            }
            $result = array('status' => true);
            if ($showNext == 'true') {
                if ($type == 'newsletter') {
                    //$result['redirect'] = (in_array('sponsor', $this->get('club')->get('bookedModulesDet')) ) ? $this->generateUrl('nl_step_sidebar', array('newsletterId' => $data['newsletterId'])) : $this->generateUrl('nl_design', array('newsletterId' => $data['newsletterId']));
                    //temporary avoiding step 4
                    $result['redirect'] = $this->generateUrl('nl_design', array('newsletterId' => $data['newsletterId']));
                } else {
                    $result['redirect'] = $this->generateUrl('sm_design', array('newsletterId' => $data['newsletterId']));
                }
            } else {
                $result['redirect'] = ($type == 'newsletter') ? $this->generateUrl('newsletter_step_content', array('newsletterId' => $data['newsletterId'])) : $this->generateUrl('simplemail_step_content', array('newsletterId' => $data['newsletterId']));
            }
            $result['sync'] = 1;
            $result['flash'] = $this->get('translator')->trans('NEWSLETTER_WIZARD_SAVED');
        }

        return new JsonResponse($result);
    }

    /**
     * Function to handle upload images
     * 
     * @param string  $type    Type of file
     * 
     * @return JsonResponse
     */
    public function uploadFileAction($type)
    {
        $check = true; //Flag whether to check mimetype/extension ( for domain verification file, html file is uploaded which is in the list of forbidden file types)
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $uploadDir = FgUtility::getUploadDir();
        $uploadedDirectory = ($this->container->getParameter('avast_scan')) ? $this->container->getParameter('avast_scan_upload_folder') : $uploadDir . "/temp/";
        $containerParameters = $this->container->getParameterBag();
        if ($type == 'simplemail' || $type == 'newsletterattachments') {
            $mimeTypes = $containerParameters->get('unlimited_mime_types');
        } else {
            $mimeTypes = $containerParameters->get('image_mime_types');
        }
        $title = $request->get('title', false);
        switch ($type) {
            case 'article':
            case 'cmstextelement':
            case 'dropzone':
            case 'websitelogo': //from website settings page
                $files = $request->files->get('image-uploader');
                break;
            case 'websitefavicon': //from website settings page
                $check = false; // file can be ico which is in the list forbidden files
                $files = $request->files->get('favicon-uploader');
                break;
            case 'websiteogimage': //from website settings page (open graph fall back image)
                $files = $request->files->get('ogimg-uploader');
                break;
            case 'domainverification': //from website settings page 
                $check = false; // file is html which is in the list forbidden files
                $files = $request->files->get('file-uploader');
                break;
            default:
                $files = $request->files->get('upl');
                break;
        }
        $fileName = ($title) ? $title : $files->getClientOriginalName();
        $fileMimeType = $files->getMimeType();

        //if file alreaduy exist in temp folder, append 1, 2 .. to filename
        $fileName = FgUtility::getFilename($uploadedDirectory, $fileName);
        //move file from php temporary folder to project temporary upload folder 
        $files->move($uploadedDirectory, $fileName);
        // virus/mimetype/extension checking functioin
        $return = $this->fileChecking($files, $fileName, $fileMimeType, $mimeTypes, $check);

        return new JsonResponse($return);
    }

    /**
     * Function to get the newsletter contents
     *
     * @param type $newsletterId
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     */
    public function getNewsletterContentAction($newsletterId = '')
    {
        if ($newsletterId == '') {
            $newsletter[] = array('type' => 'salutation', 'text' => '');
            $newsletter[] = array('type' => 'intro', 'text' => '');
            $newsletter[] = array('type' => 'closing', 'text' => '');
        } else {
            $newsletter = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletter')->getNewsletterDetailsForPreview($this->clubId, $newsletterId, true);
            $club = $this->get('club');
            $newsletter = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletter')->newsletterPreviewArrayBulider($newsletter, $this->container, true, $this->clubId, $club);
            $newsletter[0]['text'] = ($newsletter[0]['text'] == 'NL_PERSONAL_SALUTATION') ? $this->get('translator')->trans('NL_PERSONAL_SALUTATION') : $newsletter[0]['text'];
        }

        return new JsonResponse($newsletter);
    }

    /**
     * Function to get the editor contents
     *
     * @param string $type         the word type(intro or closing)
     * @param int    $newsletterId the newsletter id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getEditorContentsAction(Request $request, $type, $newsletterId)
    {
        $newsletterType = $request->get('level1');
        $newText = ($newsletterType != "newsletter") ? $this->get('translator')->trans('CREATE_NEWSLETTER_TEMPLATE_NEWSIGNATURE') : $this->get('translator')->trans('CREATE_NEWSLETTER_TEMPLATE_NEW');
        $content = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletterContent')->findOneBy(array('newsletter' => $newsletterId, 'contentType' => strtoupper($type)));
        $clubId = $this->container->get('club')->get('id');
        if ($content) {
            $text = ($content->getIntroClosingWords()) ? $content->getIntroClosingWords() : "";
        }
        $text = FgUtility::correctCkEditorUrl($text, $this->container, $clubId);

        $templateDefault[] = array('title' => $newText, 'id' => '0', 'value' => $text, 'active' => 1);
        $results = array();

        if ($newsletterType == "newsletter") {
            if ($type == 'intro') {
                $templateText = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletterIntroClosingWords')->getTemplates($this->clubId, $type, 'newsletter');
            } else {
                $templateText = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletterIntroClosingWords')->getTemplates($this->clubId, $type, 'newsletter');
            }
        } else {
            $templateText = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletterIntroClosingWords')->getTemplates($this->clubId, $type, 'simpleemail');
        }
        $results = array_merge($templateDefault, $templateText);

        return new JsonResponse($results);
    }

    /**
     * Function to save the editor content
     *
     * @param string $from     check overwrite text
     * @param string $wordType intro/closing/signature
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function saveEditorContentAction(Request $request, $from, $wordType)
    {
        $title = $request->get('title');
        $introtext = $request->get('value');
        $introType = $request->get('type');
        $values = array('title' => $title, 'value' => $introtext);
        $return = array();
        $introType = ($introType == "default") ? $wordType : $introType;
        if ($from == "overwrite") {
            $id = $request->get('id');
            $values['id'] = $id;
            $this->em->getRepository('CommonUtilityBundle:FgCnNewsletterIntroClosingWords')->overWriteExisting($this->clubId, $introType, $values, $this->contactId);
            $return = array('id' => $id, 'value' => $introtext, 'title' => $title, 'status' => 'SUCCESS');
            if ($wordType == "intro" || $wordType == 'closing') {
                $return['flash'] = $this->get('translator')->trans('EDITOR_TEMPLATE_OVERWRITE_SUCCESS');
            } else {
                $return['flash'] = $this->get('translator')->trans('EDITOR_SIGNATURE_OVERWRITE_SUCCESS');
            }
        } else {
            if ($title != "") {
                $this->em->getRepository('CommonUtilityBundle:FgCnNewsletterIntroClosingWords')->saveIntroText($this->clubId, $wordType, $values, $this->contactId);
                $return = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletterIntroClosingWords')->getLastInsertedId($this->clubId, $wordType);
                $return['status'] = 'SUCCESS';
                if ($wordType == "intro" || $wordType == 'closing') {
                    $return['flash'] = $this->get('translator')->trans('EDITOR_TEMPLATE_ADDED_SUCCESS');
                } else {
                    $return['flash'] = $this->get('translator')->trans('EDITOR_SIGNATURE_ADDED_SUCCESS');
                }
            }
        }

        return new JsonResponse($return);
    }

    /**
     * Function to delete the template
     *
     * @param string $type the type
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteEditorContentAction(Request $request, $type)
    {
        $id = $request->get('id');
        $return = array();
        if ($id) {
            $this->em->getRepository('CommonUtilityBundle:FgCnNewsletterIntroClosingWords')->deleteIntroText($id);
            if ($type == "intro" || $type == 'closing') {
                $return['flash'] = $this->get('translator')->trans('EDITOR_TEMPLATE_DELETED_SUCCESS');
            } else {
                $return['flash'] = $this->get('translator')->trans('EDITOR_SIGNATURE_DELETED_SUCCESS');
            }
        }

        return new JsonResponse($return);
    }

    /**
     * function to get the simple mail details to display in step 3
     *
     * @param int $newsletterId the newsletter id
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getNLsimpleContentAction($newsletterId = '')
    {
        $newsletterArr = array();
        $clubId = $this->container->get('club')->get('id');
        if ($newsletterId == '') {
            $newsletter[] = array('type' => 'signature', 'text' => '');
            $newsletter[] = array('type' => 'filename', 'text' => '');
        } else {
            $newsletterObj = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletter')->find($newsletterId);
            $salutation = '';
            if ($newsletterObj->getSalutationType() == 'INDIVIDUAL') {
                $salutation = $this->get('translator')->trans('NL_PERSONAL');
            } elseif ($newsletterObj->getSalutationType() == 'SAME') {
                $salutation = $newsletterObj->getSalutation();
            } else {
                $salutation = '';
            }
            $newsletter['text'] = $salutation;
            $newsletter['content'] = $newsletterObj->getEmailContent();
            $content = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletterContent')->findOneByNewsletter($newsletterId);
            if ($content) {
                $newsletter['signature'] = $content->getContentType() == 'OTHER' ? $content->getIntroClosingWords() : '';
            }
            $filenames = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletterArticleDocuments')->getSimpleNlAttachments($newsletterId, $this->clubId, $this->container);
        }
        $totalsize = $filenames['totalsize'];
        $formattedSize = $filenames['formattedSize'];
        $newsletter['content'] = FgUtility::correctCkEditorUrl($newsletter['content'], $this->container, $currentClubId);
        $newsletter['signature'] = FgUtility::correctCkEditorUrl($newsletter['signature'], $this->container, $currentClubId);
        unset($filenames['totalsize']);
        unset($filenames['formattedSize']);
        if ($salutation != "") {
            $newsletterArr[$newsletterId . '1']['text'] = $newsletter['text'];
            $newsletterArr[$newsletterId . '1']['type'] = 'salutation';
            $newsletterArr[$newsletterId . '1']['id'] = $newsletterId . '1';
            $newsletterArr[$newsletterId . '1']['newsletterId'] = $newsletterId;
        }
        $newsletterArr[$newsletterId . '2']['content'] = $newsletter['content'];
        $newsletterArr[$newsletterId . '2']['type'] = 'content';
        $newsletterArr[$newsletterId . '2']['id'] = $newsletterId . '2';
        $newsletterArr[$newsletterId . '2']['newsletterId'] = $newsletterId;
        $newsletterArr[$newsletterId . '3']['signature'] = $newsletter['signature'];
        $newsletterArr[$newsletterId . '3']['type'] = 'signature';
        $newsletterArr[$newsletterId . '3']['id'] = $newsletterId . '3';
        $newsletterArr[$newsletterId . '3']['newsletterId'] = $newsletterId;
        $newsletterArr[$newsletterId . '4']['files'] = $filenames;
        $newsletterArr[$newsletterId . '4']['type'] = 'filename';
        $newsletterArr[$newsletterId . '4']['id'] = $newsletterId . '4';
        $newsletterArr[$newsletterId . '4']['totalsize'] = $totalsize;
        $newsletterArr[$newsletterId . '4']['formattedSize'] = $formattedSize;
        $newsletterArr[$newsletterId . '4']['newsletterId'] = $newsletterId;
        $newsletters = array(0 => $newsletterArr);

        return new JsonResponse($newsletters);
    }

    /**
     * To check mimetype/extension /virus
     * @param Object  $files          uploaded file object
     * @param string  $fileName       uploaded file name
     * @param boolean $checkExtension whetehr to check extension (if false only virus checking will be done)
     * 
     * @return array checking result
     */
    private function fileChecking($files, $fileName, $fileMimeType, $mimeTypes, $checkExtension)
    {
        $uploadDir = FgUtility::getUploadDir();
        $uploadedDirectory = ($this->container->getParameter('avast_scan')) ? $this->container->getParameter('avast_scan_upload_folder') : $uploadDir . "/temp/";
        $return = array('status' => "success", 'filename' => $fileName);
        if (isset($files) && $files->getError() == 0) {
            //checking file mimetype is in allowed mimetypes and file extension not in forbidden file extensions
            $fileCheckStatus = $this->container->get('fg.avatar')->isForbidden($uploadedDirectory, $fileName, '', '', $checkExtension);
            if ((!in_array($fileMimeType, $mimeTypes) && ($checkExtension)) || count($fileCheckStatus) > 1) {
                $invalid = $this->get('translator')->trans('FILEMANAGER_UPLOAD_FILETYPE_ERROR');
                // Virus checking message translation
                if ($fileCheckStatus['error'] == 'INVALID_VIRUS_FILE' || $fileCheckStatus['error'] == 'VIRUS_FILE_CONTACT') {
                    $invalid = $this->get('translator')->trans('VIRUS_FILE_CONTACT');
                }
                $return['error'] = $invalid;
                $return['status'] = 'error';
                $return['name'] = $files->getClientOriginalName();
                unlink($uploadedDirectory . $fileName);
            }
        } else if (isset($files) && $files->getError() == 1) {
            $return = array("status" => "error", 'message' => $this->get('translator')->trans('GALLERY_UPLOAD_FILESIZELIMIT_ERROR'), 'name' => $files->getClientOriginalName());
            unlink($uploadedDirectory . $fileName);
        }
        //only working if antivirus checking is enabled
        if ($this->container->getParameter('avast_scan') && file_exists($uploadedDirectory . $fileName) && $return['status'] == 'success') {
            $attachmentObj = new File($uploadedDirectory . $fileName, false);
            $attachmentObj->move($uploadDir . "/temp/", $fileName);
        }

        return $return;
    }

    /**
     * The function to get Article titles 
     * @param  object $request 
     * 
     * @return response 
     */
    public function getInternalArticleAction(Request $request)
    {
        $term = $request->get('term');
        $return = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsArticle')->getNewsletterArticles($this->container, $term);

        return new JsonResponse($return);
    }

    /**
     * The function to get Article Images and Attachment Count  
     * @param  object $request 
     * 
     * @return response 
     */
    public function getInternalArticleDetailsAction(Request $request)
    {
        $articleId = $request->get('articleId');
        $articleLang = $request->get('lang');
        $articleObj = new ArticleData($this->container);
        $dataText = $articleObj->getArticleText($articleId);
        $articleDetails = array();
        $articleDetails['text']['title'] = ($dataText['article']['text'][$articleLang]['title']) ? $dataText['article']['text'][$articleLang]['title'] : $dataText['article']['text']['default']['title'];
        $articleDetails['text']['teaser'] = ($dataText['article']['text'][$articleLang]['teaser']) ? $dataText['article']['text'][$articleLang]['teaser'] : $dataText['article']['text']['default']['teaser'];
        $articleDetails['text']['text'] = ($dataText['article']['text'][$articleLang]['text']) ? $dataText['article']['text'][$articleLang]['text'] : $dataText['article']['text']['default']['text'];
        $articleDetails['attachment'] = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsArticleAttachments')
            ->getCountOfAttachments($articleId);
        $articleDetails['image'] = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsArticleMedia')
            ->getCountOfMedia($articleId, 'image');

        return new JsonResponse($articleDetails);
    }
}
