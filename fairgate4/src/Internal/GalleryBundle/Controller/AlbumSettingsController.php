<?php

/**
 * AlbumSettingsController.
 *
 * This controller used for managing the album settings page
 *
 * @author     pitsolutions.ch
 *
 * @version    Fairgate V4
 */
namespace Internal\GalleryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Common\UtilityBundle\Util\FgPermissions;
use Symfony\Component\HttpFoundation\Request;

class AlbumSettingsController extends Controller
{

    /**
     * Method to show gallery album settings page.
     *
     * @param Request $request Request object
     *
     * @return template
     */
    public function indexAction(Request $request)
    {
        $galleryType = $request->get('type');
        $permissionObj = new FgPermissions($this->container);
        $isAdmin = $this->getUserRightsForAlbumSettings($galleryType, $permissionObj);
        if ($isAdmin == 0) {
            $permissionObj->checkUserAccess('', '', '');
        }
        $club = $this->get('club');
        $clubId = $club->get('id');
        $contactId = $this->get('contact')->get('id');
        $clubDefaultLang = $club->get('club_default_lang');
        $clubLanguages = $club->get('club_languages');
        $galleryTitle = $this->getgalleryPageTitle($clubId, $galleryType, $clubDefaultLang);
        $breadCrumbData = array('back' => $this->generateUrl('internal_gallery_view'));

        $result = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgGmAlbum')->getAlbumdetails($clubId, $contactId, $galleryType, isAdmin);

        return $this->render('InternalGalleryBundle:AlbumSettings:index.html.twig', array('clubLanguages' => $clubLanguages, 'clubDefaultLang' => $clubDefaultLang, 'resultData' => $result, 'galleryType' => $galleryType, 'galleryTitle' => $galleryTitle, 'isAdmin' => $isAdmin, 'breadCrumb' => $breadCrumbData));
    }

    /**
     * Method to save gallery album settings page.
     *
     * @param Request $request Request object
     *
     * @return JsonResponse
     */
    public function saveAlbumAction(Request $request)
    {
        $dataArray = json_decode($request->request->get('catArr'), true);
        $galleryType = $request->get('type');
        $club = $this->get('club');
        $clubId = $club->get('id');
        $contactId = $this->get('contact')->get('id');
        $clubDefaultLang = $club->get('club_default_lang');
        $clubLanguages = $club->get('club_languages');
        $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgGmAlbum')->saveAlbumsettings($dataArray, $clubId, $contactId, $galleryType, $clubDefaultLang, $clubLanguages, $this->container);

        return new JsonResponse(array('status' => 'SUCCESS', 'sync' => 1, 'flash' => $this->get('translator')->trans('GALLERY_ALBUM_SETTINGS_SUCCESS_MESSAGE')));
    }

    /**
     * Method to get gallery album settings page title.
     *
     * @param int        $clubId          club Id
     * @param int/string $galleryType     club or role id
     * @param int        $clubDefaultLang club default language
     *
     * @return string
     */
    public function getgalleryPageTitle($clubId, $galleryType, $clubDefaultLang)
    {
        $roleDetails = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgRmRole')->getRoleName($clubId, $galleryType, $clubDefaultLang);
        $terminologyService = $this->get('fairgate_terminology_service');
        $titleText = ($galleryType == 'club') ? str_replace('%title%', $terminologyService->getTerminology('Club', $this->container->getParameter('singular')), $this->get('translator')->trans('GALLERY_ALBUM_SETTINGS_TITLE')) : (($roleDetails['isExecutiveBoard'] == 1) ? str_replace('%title%', $terminologyService->getTerminology('Executive Board', $this->container->getParameter('singular')), $this->get('translator')->trans('GALLERY_ALBUM_SETTINGS_TITLE')) : str_replace('%title%', $roleDetails['roleName'], $this->get('translator')->trans('GALLERY_ALBUM_SETTINGS_TITLE')));

        return $titleText;
    }

    /**
     * Method to get user rights for album settings page.
     *
     * @param int/string $galleryType   club or role id
     * @param object     $permissionObj permission object
     *
     * @return int
     */
    public function getUserRightsForAlbumSettings($galleryType, $permissionObj)
    {
        $contact = $this->container->get('contact');
        $isAdmin = in_array('ROLE_GALLERY', $contact->get('availableUserRights')) ? 1 : 0;     //Check if clubadmin
        $myGroups = array();
        if ($galleryType != 'club') {
            $roleExistobj = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgRmRole')->find($galleryType);
            if (empty($roleExistobj)) {
                $permissionObj->checkClubAccess('', '', '');
            }

            $groupRights = $contact->get('clubRoleRightsGroupWise');

            if (isset($groupRights['ROLE_GROUP_ADMIN']['teams'])) {
                $myGroups = array_merge($myGroups, $groupRights['ROLE_GROUP_ADMIN']['teams']);
            }

            if (isset($groupRights['ROLE_GROUP_ADMIN']['workgroups'])) {
                $myGroups = array_merge($myGroups, $groupRights['ROLE_GROUP_ADMIN']['workgroups']);
            }

            if (isset($groupRights['ROLE_GALLERY_ADMIN']['teams'])) {
                $myGroups = array_merge($myGroups, $groupRights['ROLE_GALLERY_ADMIN']['teams']);
            }

            if (isset($groupRights['ROLE_GALLERY_ADMIN']['workgroups'])) {
                $myGroups = array_merge($myGroups, $groupRights['ROLE_GALLERY_ADMIN']['workgroups']);
            }

            if (in_array($galleryType, $myGroups)) {
                $isAdmin = 1;
            }
        }

        return $isAdmin;
    }
}
