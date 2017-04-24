<?php

/**
 * Gallery Controller.
 *
 * This controller is used for Gallery section.
 *
 * @author     pitsolutions.ch
 *
 * @version    Fairgate V4
 */
namespace Internal\GalleryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Common\FilemanagerBundle\Util\FileChecking;
use Internal\GalleryBundle\Util\GalleryList;
use Symfony\Component\HttpFoundation\Request;

class GalleryUploadController extends Controller
{

    /**
     * Upload a gallery image/video.
     * 
     * @param Request $request Request object
     *
     * @return JsonResponse
     */
    public function imageUploadAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        //Club Details
        $club = $this->container->get('club');
        $contact = $this->container->get('contact');
        $clubId = $club->get('id');
        $defLang = $club->get('default_lang');
        $clubLang = $club->get('club_languages');
        $contactId = $contact->get('id');

        $imageDetails = $request->request->all();
        $imageDetails['clubId'] = $clubId;
        $imageDetails['defLang'] = $defLang;
        $imageDetails['clubLang'] = $clubLang;
        $imageDetails['contactId'] = $contactId;
        $galleryListObj = new GalleryList($this->container);
        //Move images/videos to the club gallery folder from temp folder
        $return = $this->moveGalleryItems($imageDetails, $galleryListObj);
        //Insert Image query
        $em->getRepository('CommonUtilityBundle:FgGmItems')->saveGalleryImage($return['imageDetails'], $this->container);

        return new JsonResponse($return['returnArray']);
    }

    /**
     * gallery add video popup.
     *
     * @return Template
     */
    public function uploadVideoPopupAction()
    {
        //Club Details
        $club = $this->container->get('club');
        $defLang = $club->get('default_lang');
        $clubLang = $club->get('club_languages');
        $popupTitle = $this->get('translator')->trans('ADD_VIDEO_POPUP_TITLE');
        $return = array('title' => $popupTitle, 'clubLanguages' => $clubLang, 'clubDefaultLang' => $defLang);

        return $this->render('InternalGalleryBundle:Gallery:uploadVideoPopup.html.twig', $return);
    }

    /**
     * gallery edit desc popup.
     *
     * @return Template
     */
    public function editDescPopupAction()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $itemIds = $request->get('chekedIds');
        $itemCount = $request->get('itemCount');
        //Club Details
        $club = $this->container->get('club');
        $defLang = $club->get('default_lang');
        $clubLang = $club->get('club_languages');
        $popupTitle = ($itemCount > 1) ? $this->get('translator')->trans('EDIT_DESC_POPUP_TITLES') : $this->get('translator')->trans('EDIT_DESC_POPUP_TITLE');

        $editDetails = $this->getAllDesc($itemIds);
        $return = array('title' => $popupTitle, 'clubLanguages' => $clubLang, 'clubDefaultLang' => $defLang, 'descriptionArr' => $editDetails);

        return $this->render('InternalGalleryBundle:Gallery:editDescPopup.html.twig', $return);
    }

    /**
     * get all description for edit.
     *
     * @param string $itemIds item ids
     *
     * @return Array
     */
    public function getAllDesc($itemIds)
    {
        $em = $this->getDoctrine()->getManager();

        $itemId = explode(',', $itemIds);
        $itemDetails = array();
        foreach ($itemId as $items) {
            $itemDet = $em->getRepository('CommonUtilityBundle:FgGmItems')->fetchGalleryDesc($items);
            $itemDetails = $itemDetails + $itemDet;
        }

        return $itemDetails;
    }

    /**
     * save all edited description.
     * 
     * @param Request $request Request object
     *
     * @return JsonResponse
     */
    public function editDescSaveAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $descDet = json_decode($request->get('saveData'), true);

        $club = $this->container->get('club');
        $defLang = $club->get('default_lang');
        $clubLang = $club->get('club_languages');
        if (sizeof($descDet['editDesc']) > 0) {
            $em->getRepository('CommonUtilityBundle:FgGmItems')->editItemDesc($descDet['editDesc'], $defLang, $clubLang, $this->container);
        }

        $return = array('status' => 'SUCCESS', 'sync' => 1, 'noreload' => 1, 'noparentload' => true, 'flash' => $this->get('translator')->trans('GALLERY_DESCRIPTION_EDIT_SUCCESSFULLY'));

        return new JsonResponse($return);
    }

    /**
     * To handle move functionality of image/video
     * 
     * @param array $imageDetails images detail 
     * @param object $galleryListObj gallery list object
     * 
     * @return array of output json and image details
     */
    private function moveGalleryItems($imageDetails, $galleryListObj)
    {
        $clubId = $this->container->get('club')->get('id');
        $return = array();
        if ($imageDetails['type'] === 'IMAGE') {
            $fileCheck = new FileChecking($this->container);
            foreach ($imageDetails['fileName'] as $key => $image) {
                $imageDetails['fileName'][$key] = $fileCheck->replaceSingleQuotes($image);
            }
            $imageDetails = $galleryListObj->movetoclubgallery($imageDetails['uploadedImages'], $imageDetails['fileName'], $clubId, $imageDetails);
            $return = array('status' => 'SUCCESS', 'sync' => 1, 'noreload' => 1, 'flash' => $this->get('translator')->trans('GALLERY_IMAGE_UPLOADED_SUCCESSFULLY'));
        } elseif ($imageDetails['type'] === 'VIDEO') {
            //get the thumbnail of the video and save it to the temp folder
            $imageExtension = end(explode('.', $imageDetails['videoThumb']));
            $content = file_get_contents($imageDetails['videoThumb']);
            $fileName = md5(rand()) . '.' . $imageExtension;

            $fp = fopen('uploads/temp/' . $fileName, 'w');
            fwrite($fp, $content);
            fclose($fp);

            $imageDetails = $galleryListObj->movetoclubgallery(array($fileName), array($fileName), $clubId, $imageDetails);
            $imageDetails['videoThumb'] = $fileName;
            $imageDetails['imgCount'] = 1;
            $return = array('status' => 'SUCCESS', 'sync' => 1, 'noreload' => 1, 'flash' => $this->get('translator')->trans('GALLERY_VIDEO_UPLOADED_SUCCESSFULLY'));
        }

        return array('returnArray' => $return, 'imageDetails' => $imageDetails);
    }
}
