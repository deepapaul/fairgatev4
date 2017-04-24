<?php
/**
 * This controller was created for handling gallery view.
 */
namespace Internal\GalleryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Internal\GalleryBundle\Util\GalleryList;
use Common\UtilityBundle\Repository\Pdo\GalleryPdo;
use Symfony\Component\HttpFoundation\Request;

/**
 * DefaultController used for managing Gallery view.
 */
class DefaultController extends Controller
{

    public function indexAction($name)
    {
        return $this->render('GalleryBundle:Default:index.html.twig', array('name' => $name));
    }

    /**
     * Function: view gallery.
     *
     * return template
     */
    public function galleryViewAction()
    {
        $club = $this->get('club');

        $clubId = $club->get('id');
        $contactId = $this->container->get('contact')->get('id');

        $defLang = $club->get('default_lang');
        $clubLang = $club->get('club_languages');

        //get all user rights of current user
        $adminFlag = in_array('ROLE_GALLERY', $this->container->get('contact')->get('availableUserRights')) ? 1 : 0;
        $breadCrumb = array('breadcrumb_data' => array());
        $albumSettingPath = $this->generateUrl('gallery_album_settings', array('type' => '#type#'));

        return $this->render('InternalGalleryBundle:Default:galleryView.html.twig', array('albumSettingPath' => $albumSettingPath, 'breadCrumb' => $breadCrumb, 'isAdmin' => $adminFlag, 'clubId' => $clubId, 'contactId' => $contactId, 'clubLanguageArr' => $clubLang, 'defaultClubLang' => $defLang));
    }

    /**
     * Templete for gallery action menu modal popups (CHANGE_SCOPE/REMOVE_IMAGE/CHANGE_SORT/MOVETO_ALBUM/ASSIGNTO_ALBUM).
     *
     * @return Template
     */
    public function modalPopupAction(Request $request)
    {
        $checkedIds = $request->get('checkedIds'); //selected ids(comma separeted)
        $selected = $request->get('selected');  //can be selected/all
        $modalType = $request->get('modalType');  //can be CHANGE_SCOPE/REMOVE_IMAGE/MOVETO_ALBUM/CHANGE_SORT/ASSIGNTO_ALBUM
        $params = $request->get('params');  //json data of current status like currentScope
        $checkedIdsArray = explode(',', $checkedIds);
        switch ($modalType) {
            //change scope of items
            case 'CHANGE_SORT':
                $albumName = $params['albumName'];
                $buttonSave = $this->get('translator')->trans('SAVE');
                $popupTitle = $this->get('translator')->trans('GALLERY_CHANGE_SORT_TITLE', array('%albumname%' => $albumName));
                break;
            //change scope of items
            case 'CHANGE_SCOPE':
                $buttonSave = $this->get('translator')->trans('SAVE');
                $popupTitle = $this->changeScopeTitlegenerator($checkedIdsArray, $selected);
                break;
            //remove items from albums
            case 'REMOVE_IMAGE':
                $albumName = $params['albumName'];
                $buttonSave = $this->get('translator')->trans('REMOVE');
                $popupDetailsArray = $this->imageRemovemodalTitle($checkedIdsArray, $albumName, $modalType, $selected);
                $popupTitle = $popupDetailsArray['title'];
                $popupText = $popupDetailsArray['text'];
                break;
            //delete items from all images
            case 'DELETE_IMAGE':
                $buttonSave = $this->get('translator')->trans('GALLERY_DELETE');
                $popupDetailsArray = $this->imageRemovemodalTitle($checkedIdsArray, $albumName, $modalType, '');
                $popupTitle = $popupDetailsArray['title'];
                $popupText = $popupDetailsArray['text'];
                break;
            //set cover image for albums
            case 'SET_COVER_IMAGE':
                $buttonSave = $this->get('translator')->trans('COVER_IMAGE_APPLY');
                $popupTitle = $this->get('translator')->trans('GALLERY_SET_COVER_IMG');
                $popupText = $this->get('translator')->trans('SET_COVER_IMAGE_TEXT');
                break;

            //items move to album
            case 'MOVETO_ALBUM':
            case 'ASSIGNTO_ALBUM':
                $albumName = $params['albumName'];
                $popupDetailsArray = $this->assigntoalbumModalPopupTitleDetails($checkedIdsArray, $albumName, $modalType);
                $popupTitle = $popupDetailsArray['title'];
                $buttonSave = $popupDetailsArray['button'];  
                $adminstrativeRoles = $this->getAdministrativeRoles();
                $terminologyService = $this->container->get('fairgate_terminology_service');                
                /* get gallery details */
                $galleryData = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgGmGallery')->getGalleryAlbums($this->container->get('club')->get('id'), $this->container->get('club')->get('default_lang'), $adminstrativeRoles, $terminologyService->getTerminology('Executive Board', $this->container->getParameter('singular')));
                $galleryDetails = $this->formatGalleryArray($galleryData);
                $params['galleryDetails'] = $galleryDetails;

                break;
        }

        $return = array('title' => $popupTitle, 'text' => $popupText, 'checkedIds' => $checkedIds, 'modalType' => $modalType, 'params' => $params, 'paramsJson' => json_encode($params), 'button_val' => $buttonSave);

        return $this->render('InternalGalleryBundle:Default:modalPopup.html.twig', $return);
    }

    /**
     * Method to change scope of gallery items.
     *
     * @return JsonResponse
     */
    public function changeScopeAction(Request $request)
    {
        $checkedIds = $request->get('checkedIds');
        $scope = $request->get('scope');  //INTERNAL/PUBLIC
        $em = $this->getDoctrine()->getManager();
        $result = $em->getRepository('CommonUtilityBundle:FgGmItems')->setScope($checkedIds, $scope);
        $return = array('status' => $result, 'flash' => $this->get('translator')->trans('GALLERY_SCOPECHANGE_SUCCESS'), 'noparentload' => true, 'scope' => $scope, 'checkedIds' => $checkedIds);

        return new JsonResponse($return);
    }

    /**
     * get sidebar array.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function galleryDataSidebarAction()
    {
        $galleryObj = new GalleryList($this->container, 'sidebar');
        $galleryObj->setColumns();
        $galleryObj->setFrom();
        $galleryObj->setCondition();
        $galleryObj->setGroupBy('A.id');
        $galleryObj->addOrderBy('albumParent,albumSortOrder ASC ');
        $qry = $galleryObj->getMySidebar();
        $galleryMainData = $galleryObj->executeQuery($qry);
        $galleryData = $this->formatArray($galleryMainData);

        return new JsonResponse($galleryData);
    }

    /**
     * get roles based on rights.
     *
     * @return array
     */
    private function getMyTeamsAndWorkgroups()
    {
        $myAdminGroups = array();
        $myMemberGroups = array();
        $myGroups = array();

        $groupRights = $this->container->get('contact')->get('clubRoleRightsGroupWise');

        if (isset($groupRights['ROLE_GROUP_ADMIN']['teams'])) {
            $myMemberGroups = array_merge($myMemberGroups, $groupRights['ROLE_GROUP_ADMIN']['teams']);
            $myAdminGroups = array_merge($myAdminGroups, $groupRights['ROLE_GROUP_ADMIN']['teams']);
        }
        if (isset($groupRights['ROLE_GROUP_ADMIN']['workgroups'])) {
            $myMemberGroups = array_merge($myMemberGroups, $groupRights['ROLE_GROUP_ADMIN']['workgroups']);
            $myAdminGroups = array_merge($myAdminGroups, $groupRights['ROLE_GROUP_ADMIN']['workgroups']);
        }
        if (isset($groupRights['ROLE_GALLERY_ADMIN']['teams'])) {
            $myMemberGroups = array_merge($myMemberGroups, $groupRights['ROLE_GALLERY_ADMIN']['teams']);
            $myAdminGroups = array_merge($myAdminGroups, $groupRights['ROLE_GALLERY_ADMIN']['teams']);
        }
        if (isset($groupRights['ROLE_GALLERY_ADMIN']['workgroups'])) {
            $myMemberGroups = array_merge($myMemberGroups, $groupRights['ROLE_GALLERY_ADMIN']['workgroups']);
            $myAdminGroups = array_merge($myAdminGroups, $groupRights['ROLE_GALLERY_ADMIN']['workgroups']);
        }
        if (isset($groupRights['MEMBER']['teams'])) {
            $myMemberGroups = array_merge($myMemberGroups, $groupRights['MEMBER']['teams']);
        }
        if (isset($groupRights['MEMBER']['workgroups'])) {
            $myMemberGroups = array_merge($myMemberGroups, $groupRights['MEMBER']['workgroups']);
        }

        $myGroups['MEMBER'] = array_unique($myMemberGroups);
        $myGroups['ADMIN'] = array_unique($myAdminGroups);

        return $myGroups;
    }

    /**
     * format array for sidebar.
     *
     * @param array $galleryData
     *
     * @return array
     */
    private function formatArray($galleryData)
    {
        $em = $this->getDoctrine()->getManager();
        $isCluborSuperAdmin = (count($this->container->get('contact')->get('mainAdminRightsForFrontend')) > 0) ? 1 : 0;
        $isClubGalleryAdmin = in_array('ROLE_GALLERY', $this->container->get('contact')->get('availableUserRights')) ? 1 : 0;
        $teams = $this->container->get('contact')->get('teams');
        $workgroups = $this->container->get('contact')->get('workgroups');

        $formatArray = array();

        if ($isCluborSuperAdmin) {
            $teamAdminstrativeRoles = array_keys($teams);
            $workgroupAdminstrativeRoles = array_keys($workgroups);
            $myGroupsList = array_merge($teamAdminstrativeRoles, $workgroupAdminstrativeRoles);
            $formatArray = $this->rightsBasedSubArray($formatArray, $myGroupsList, $teams, $workgroups, 1);
            $formatArray['CG']['adminPrivilege'] = 1;
        } elseif ($isClubGalleryAdmin) {
            $club = $this->container->get('club');
            $workgroupCatId = $club->get('club_workgroup_id');
            $teamCatId = $club->get('club_team_id');
            $assignedRoles = $em->getRepository('CommonUtilityBundle:FgRmRole')->getAllActiveRolesOfAClub($this->container, array($teamCatId, $workgroupCatId));
            $teams = $assignedRoles['teams'];
            $workgroups = $assignedRoles['workgroups'];

            $teamAdminstrativeRoles = array_keys($teams);
            $workgroupAdminstrativeRoles = array_keys($workgroups);
            $myGroupsList = array_merge($teamAdminstrativeRoles, $workgroupAdminstrativeRoles);
            $formatArray = $this->rightsBasedSubArray($formatArray, $myGroupsList, $teams, $workgroups, 1);
            $formatArray['CG']['adminPrivilege'] = 1;
        } else {
            $myGroups = $this->getMyTeamsAndWorkgroups();
            $myGroupsList = array_unique(array_merge($myGroups['MEMBER'], $myGroups['ADMIN']));
            $formatArray = $this->rightsBasedSubArray($formatArray, $myGroups['MEMBER'], $teams, $workgroups, 0);
            $formatArray = $this->rightsBasedSubArray($formatArray, $myGroups['ADMIN'], $teams, $workgroups, 1);
            $formatArray['CG']['adminPrivilege'] = 0;
        }

        $albumParentClub = array();
        $albumParentRole = array();
        $bookmark = $this->galleryItemIteration($galleryData, $formatArray);

        if (empty($bookmark)) {
            $formatArray['Bookmark']['entry'] = array();
        } else {
            ksort($bookmark);
            $formatArray['Bookmark']['entry'] = array_merge($bookmark, array());
        }

        return $formatArray;
    }

    /**
     * create sub array for sidebar.
     *
     * @param array $formatArray   format array
     * @param array $myGroupsList  array to format
     * @param array $teams         team list
     * @param array $workgroups    workgroup list
     * @param int   $rolePrivilege privilege to edit
     *
     * @return array
     */
    private function rightsBasedSubArray($formatArray, $myGroupsList, $teams, $workgroups, $rolePrivilege)
    {
        $containerParameters = $this->container->getParameterBag();
        $terminologyService = $this->get('fairgate_terminology_service');
        $club = $terminologyService->getTerminology('Club', $containerParameters->get('singular'));
        $formatArray['Bookmark']['id'] = 'bookmark';
        $formatArray['Bookmark']['title'] = $this->get('translator')->trans('BOOKMARK');
        $formatArray['CG']['id'] = 'CG';
        $formatArray['CG']['title'] = $this->get('translator')->trans('CLUB_GALLERY', array('%Club%' => $club));
        $formatArray['CG']['entry'] = array();

        foreach ($myGroupsList as $key => $values) {
            $formatArray['R' . $values] = array();
            $formatArray['R' . $values]['id'] = $values;
            $formatArray['R' . $values]['title'] = array_key_exists($values, $teams) ? $teams[$values] : $workgroups[$values];
            $formatArray['R' . $values]['adminPrivilege'] = $rolePrivilege;
            $formatArray['R' . $values]['entry'] = array();
        }

        return $formatArray;
    }

    /**
     * Method to sort galllery items.
     *
     * @return JsonResponse
     */
    public function sortAlbumItemsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $data = $request->get('data');
        $albumId = $request->get('albumId');
        $albumItemIds = array_map(function ($a) {
            return $a['albumItemId'];
        }, $data);
        $sortOrders = array_map(function ($a) {
            return $a['sortOrder'];
        }, $data);
        $sortPosition = min($sortOrders);
        $em->getRepository('CommonUtilityBundle:FgGmGallery')->saveSortOrder($albumItemIds, $sortPosition, $albumId, $this->container);

        $return = array('status' => true, 'flash' => $this->get('translator')->trans('GALLERY_SORT_SUCCESS'), 'noparentload' => true);

        return new JsonResponse($return);
    }

    /**
     * Method to sort all album items.
     *
     * @return JsonResponse
     */
    public function reorderAlbumItemsAction(Request $request)
    {
        $sType = $request->get('sortTtype');
        $albumId = $request->get('albumId');
        switch ($sType) {
            case 'REV_CURRENT' : $sortingType = 'SORT_ORDER_DESC';
                break;
            case 'OLDEST_TOP' : $sortingType = 'OLDER_FIRST';
                break;
            case 'NEWEST_TOP' : $sortingType = 'NEWER_FIRST';
                break;
            default : $sortingType = 'NEWER_FIRST';
                break;
        }

        $em = $this->getDoctrine()->getManager();
        $em->getRepository('CommonUtilityBundle:FgGmAlbumItems')->reorderAllAlbumItems($albumId, $sortingType, $this->container);

        $return = array('status' => true, 'flash' => $this->get('translator')->trans('GALLERY_SORT_SUCCESS'), 'noparentload' => true);

        return new JsonResponse($return);
    }

    /**
     * Method to get album details dynamically.
     *
     * @return JsonResponse
     */
    public function getAlbumDetailsAjaxAction(Request $request)
    {
        $albumId = $request->get('albumId');

        $galleryObj = new GalleryList($this->container, 'gallery');

        if ($albumId == 'ORPHAN') {
            $galleryObj->albumId = 'NULL';
        } elseif ($albumId == 'EXTERNAL') {
            $galleryObj->albumId = 'EXTERNAL';
        } else {
            $galleryObj->albumId = (int) $albumId;
        }

        if ($albumId == 'ALL' || $albumId == 'ORPHAN') {
            $galleryObj->addOrderBy('itemId DESC');
        } else {
            $galleryObj->addOrderBy('albumItemSortOrder ASC');
        }

        $galleryObj->setColumns();
        $galleryObj->setFrom();
        $galleryObj->setCondition();
        $imageQuery = $galleryObj->getMyImages();

        //execute the qry and get the results
        $galleryPdo = new GalleryPdo($this->container);
        $imagesDetails = $galleryPdo->executeQuery($imageQuery);
        foreach ($imagesDetails as $key => $imageData) {
            $imagesDetails[$key]['itemDescription'] = htmlentities($imageData['itemDescription'], ENT_QUOTES | ENT_IGNORE, 'UTF-8');
        }
        $imgPath = '/uploads/' . $this->get('club')->get('id') . '/gallery/';
        $isAdmin = $this->getAlbumUserrights($albumId);
        $result = array('data' => $imagesDetails, 'imgPath' => $imgPath, 'isAdmin' => $isAdmin);

        return new JsonResponse($result);
    }

    /**
     * Function to create sponsor category or sub category.
     *
     * @return $return JsonResponse
     */
    public function newElementAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $club = $this->get('club');
        $clubId = $club->get('id');
        $clubdefLang = $club->get('club_default_lang');
        $title = $request->get('value');
        $elementType = $request->get('elementType');

        if ($elementType == 'album') {
            $roleType = $request->get('roleType');
            $type = ($roleType == 'CG') ? 'CLUB' : 'ROLE';
            $itemType = ($roleType == 'CG') ? 'CG' : 'RG';
            $lastInsertedId = $em->getRepository('CommonUtilityBundle:FgGmGallery')->createAlbum($clubId, $title, $roleType, $clubdefLang, $this->container);
            $return = array('hasImage' => 0, 'items' => array('0' => array('itemType' => $itemType, 'roleId' => $roleType, 'nocount' => 1, 'id' => $lastInsertedId, 'title' => str_replace('"', '', stripslashes($title)), 'type' => $type, 'draggable' => 1)));
        } else {
            $type = ($elementType == 'CG') ? 'CLUB' : 'ROLE';
            $itemType = ($elementType == 'CG') ? 'CG' : 'RG';
            $categoryId = $request->get('category_id');
            $lastInsertedId = $em->getRepository('CommonUtilityBundle:FgGmGallery')->createSubAlbum($clubId, $categoryId, $title, $type, $elementType, $clubdefLang, $this->container);
            $return = array('hasImage' => 0, 'input' => array('0' => array('categoryId' => $categoryId, 'nocount' => 1, 'draggableClass' => 'fg-dev-draggable', 'id' => $lastInsertedId, 'itemType' => $itemType, 'dataType' => ($elementType == 'CG') ? $elementType : 'R' . $elementType, 'count' => 0, 'title' => str_replace('"', '', stripslashes($title)), 'type' => 'select', 'draggable' => 1, 'bookMarkId' => '')));
        }

        return new JsonResponse($return);
    }

    /**
     * Method to remove of gallery items.
     *
     * @return JsonResponse
     */
    public function removeItemAction(Request $request)
    {
        $checkedIds = $request->get('checkedIds');
        $em = $this->getDoctrine()->getManager();
        $affectedCount = $em->getRepository('CommonUtilityBundle:FgGmAlbumItems')->removeItemsFromAlbum($checkedIds);
        $successMsg = ($affectedCount > 1) ? $this->get('translator')->trans('GALLERY_REMOVED_SUCCESS', array('%count%' => $affectedCount)) : $this->get('translator')->trans('GALLERY_REMOVED_SINGLE_SUCCESS', array('%count%' => $affectedCount));
        $return = array('status' => 'true', 'flash' => $successMsg, 'noparentload' => true, 'affectedCount' => $affectedCount, 'checkedIds' => $checkedIds);

        return new JsonResponse($return);
    }

    /**
     * Method to get Album user rights.
     *
     * @return JsonResponse
     */
    private function getAlbumUserrights($albumId)
    {
        $contact = $this->container->get('contact');
        $isAdmin = in_array('ROLE_GALLERY', $contact->get('availableUserRights')) ? 1 : 0;     //Check if clubadmin
        if ($albumId > 0 && $isAdmin == 0) {
            $em = $this->getDoctrine()->getManager();
            $albumData = $em->getRepository('CommonUtilityBundle:FgGmGallery')->getAlbumDetails($albumId);
            if ($albumData['type'] != 'CLUB') {
                $groupRights = $this->container->get('contact')->get('clubRoleRightsGroupWise');
                $myGroups = array();
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

                if (in_array($albumData['roleId'], $myGroups)) {
                    $isAdmin = 1;
                }
            }
        }

        return $isAdmin;
    }

    /**
     * Method to set album cover image.
     *
     * @return JsonResponse
     */
    public function setCoverImageAction(Request $request)
    {
        $checkedIds = $request->get('checkedIds');
        $em = $this->getDoctrine()->getManager();
        $em->getRepository('CommonUtilityBundle:FgGmAlbumItems')->setCoverImageForAlbum($checkedIds);

        return new JsonResponse(array('status' => 'true', 'flash' => $this->get('translator')->trans('GALLERY_COVERIMAGE_UPDATE_SUCCESS'), 'checkedIds' => $checkedIds, 'noparentload' => true));
    }

    /**
     * Method to get roles (teams&workgroups) which the contact have adminstrative rights.
     *
     * @return array
     */
    private function getAdministrativeRoles()
    {
        $clubRoleRights = $this->container->get('contact')->get('clubRoleRightsGroupWise');
        $teamAdminstrativeRoles = $workgroupAdminstrativeRoles = array();
        $adminRights = $this->container->get('contact')->get('mainAdminRightsForFrontend'); //clubadmin or superadmin
        $isClubGalleryAdmin = in_array('ROLE_GALLERY', $this->container->get('contact')->get('availableUserRights')) ? 1 : 0;
        if (count($adminRights) > 0) {
            /* Teams & workgroups which the contact have adminstrative role */
            $teamAdminstrativeRoles = array_keys($this->container->get('contact')->get('teams'));
            $workgroupAdminstrativeRoles = array_keys($this->container->get('contact')->get('workgroups'));
        } elseif ($isClubGalleryAdmin) { //handled case club gallery admin
            $club = $this->container->get('club');
            $workgroupCatId = $club->get('club_workgroup_id');
            $teamCatId = $club->get('club_team_id');
            $em = $this->getDoctrine()->getManager();
            $assignedRoles = $em->getRepository('CommonUtilityBundle:FgRmRole')->getAllActiveRolesOfAClub($this->container, array($teamCatId, $workgroupCatId));
            $teams = $assignedRoles['teams'];
            $workgroups = $assignedRoles['workgroups'];
            $teamAdminstrativeRoles = array_keys($teams);
            $workgroupAdminstrativeRoles = array_keys($workgroups);
        } else {
            /* Teams which the contact have adminstrative role */
            $teamAdminstrativeRoles = (count($clubRoleRights['ROLE_GROUP_ADMIN']['teams']) > 0) ? $clubRoleRights['ROLE_GROUP_ADMIN']['teams'] : array();
            if ($clubRoleRights['ROLE_GALLERY_ADMIN']['teams']) {
                $teamAdminstrativeRoles = array_merge($teamAdminstrativeRoles, $clubRoleRights['ROLE_GALLERY_ADMIN']['teams']);
            }

            /* Workgroups which the contact have adminstrative role */
            $workgroupAdminstrativeRoles = (count($clubRoleRights['ROLE_GROUP_ADMIN']['workgroups']) > 0) ? $clubRoleRights['ROLE_GROUP_ADMIN']['workgroups'] : array();
            if ($clubRoleRights['ROLE_GALLERY_ADMIN']['workgroups']) {
                $workgroupAdminstrativeRoles = array_merge($workgroupAdminstrativeRoles, $clubRoleRights['ROLE_GALLERY_ADMIN']['workgroups']);
            }
        }

        return array_merge($teamAdminstrativeRoles, $workgroupAdminstrativeRoles);
    }

    /**
     * Method to items to albums.
     *
     * @return JsonResponse
     */
    public function moveItemAction(Request $request)
    {
        $checkedIds = $request->get('checkedIds');
        $albumId = $request->get('albumId');
        $em = $this->getDoctrine()->getManager();
        $affectedCount = $em->getRepository('CommonUtilityBundle:FgGmAlbumItems')->moveItemsToAlbum($checkedIds, $albumId, $this->container);
        $successMsg = ($affectedCount > 1) ? $this->get('translator')->trans('GALLERY_MOVED_SUCCESS', array('%count%' => $affectedCount)) : $this->get('translator')->trans('GALLERY_MOVED_SINGLE_SUCCESS', array('%count%' => $affectedCount));
        $return = array('status' => 'true', 'flash' => $successMsg, 'noparentload' => true, 'affectedCount' => $affectedCount, 'checkedIds' => $checkedIds);

        return new JsonResponse($return);
    }

    /**
     * Method to delete gallery items.
     *
     * @return JsonResponse
     */
    public function deleteItemAction(Request $request)
    {
        $checkedIds = $request->get('checkedIds');
        $selectedAlbum = $request->get('selectedAlbum');
        $source = ($selectedAlbum == 'EXTERNAL') ? 'EXTERNAL' : 'GALLERY';
        $em = $this->getDoctrine()->getManager();
        $affectedItems = $em->getRepository('CommonUtilityBundle:FgGmItems')->deleteItems($checkedIds, $source);
        $successMsg = (count($affectedItems) > 1) ? $this->get('translator')->trans('GALLERY_DELETED_SUCCESS', array('%count%' => count($affectedItems))) : $this->get('translator')->trans('GALLERY_DELETED_SINGLE_SUCCESS', array('%count%' => count($affectedItems)));
        $return = array('status' => 'true', 'flash' => $successMsg, 'noparentload' => true, 'affectedCount' => count($affectedItems), 'checkedIds' => implode(',', $affectedItems));

        return new JsonResponse($return);
    }

    /**
     * Method to format gallery array with respect to parent child relation.
     *
     * @param array $galleryData gallery data
     *
     * @return array $formatArray formatted array
     */
    private function formatGalleryArray($galleryData)
    {
        $formatArray = array();
        $containerParameters = $this->container->getParameterBag();
        $terminologyService = $this->get('fairgate_terminology_service');
        $club = $terminologyService->getTerminology('Club', $containerParameters->get('singular'));
        foreach ($galleryData as $value) {
            //handling single quotes
            $value['albumName'] = htmlspecialchars($value['albumName'], ENT_QUOTES);
            $value['roleName'] = htmlspecialchars($value['roleName'], ENT_QUOTES);
            if ($value['albumType'] == 'CLUB') {
                $formatArray['CG']['id'] = 'CG';
                $formatArray['CG']['title'] = $this->get('translator')->trans('CLUB_GALLERY', array('%Club%' => $club));
                if (array_key_exists($value['albumParent'], $formatArray['CG']['entry'])) {
                    $subArray = array('id' => $value['albumId'], 'title' => $value['albumName'], 'itemType' => 'CG');
                    $formatArray['CG']['entry'][$value['albumParent']]['input'][] = $subArray;
                } else {
                    $formatArray['CG']['entry'][$value['albumId']] = array('id' => $value['albumId'], 'title' => $value['albumName'], 'itemType' => 'CG');
                }
            } else {
                $formatArray[$value['albumRole']]['id'] = $value['albumRole'];
                $formatArray[$value['albumRole']]['title'] = $value['roleName'];
                if (array_key_exists($value['albumParent'], $formatArray[$value['albumRole']]['entry'])) {
                    $subArray = array('id' => $value['albumId'], 'title' => $value['albumName'], 'itemType' => 'RG');
                    $formatArray[$value['albumRole']]['entry'][$value['albumParent']]['input'][] = $subArray;
                } else {
                    $formatArray[$value['albumRole']]['entry'][$value['albumId']] = array('id' => $value['albumId'], 'title' => $value['albumName'], 'itemType' => 'RG');
                }
            }
        }

        return $formatArray;
    }

    /**
     * TO iterate the gallery item array
     * @param array $galleryData gallery item details
     * @param array $formatArray new array format
     * @return array newly created bookmark array
     */
    private function galleryItemIteration($galleryData, &$formatArray)
    {
        $albumParentClub = array();
        $albumParentRole = array();
        $bookmark = array();
        foreach ($galleryData as $value) {
            if ($value['bookMarkId'] != null) {
                $type = ($value['albumType'] == 'CLUB') ? 'CG' : 'RG';
                $bookmark[$value['bookMarkSort']] = array('id' => $value['albumId'], 'title' => $value['albumName'], 'itemType' => $type,
                    'categoryId' => $value['albumParent'], 'bookMarkId' => $value['bookMarkId'], 'nocount' => 1,
                    'sortOrder' => $value['bookMarkSort'], 'adminPrivilege' => $value['albumPrivilege'],);
            }

            if ($value['albumType'] == 'CLUB') {
                $subArray = array('id' => $value['albumId'], 'title' => $value['albumName'], 'nocount' => 1, 'bookMarkId' => $value['bookMarkId'], 'draggable' => $formatArray['CG']['adminPrivilege'], 'itemType' => 'CG', 'adminPrivilege' => $value['albumPrivilege']);
                if ($value['albumParent'] == 0) {
                    if ($value['imageCount'] > 0 || $formatArray['CG']['adminPrivilege']) {
                        //This ia a parent Album
                        $index = count($albumParentClub);
                        $formatArray['CG']['entry'][$index] = array();
                        $formatArray['CG']['entry'][$index] = $subArray;
                        $formatArray['CG']['entry'][$index]['hasImage'] = ($value['imageCount'] > 0) ? 1 : 0;
                        $albumParentClub[$value['albumId']] = $index;
                    }
                } else {
                    if ($value['imageCount'] > 0 || $formatArray['CG']['adminPrivilege']) {
                        //This is a sub album
                        $myParentAlbumId = $value['albumParent'];
                        $subArray ['hasImage'] = ($value['imageCount'] > 0) ? 1 : 0;
                        $formatArray['CG']['entry'][$albumParentClub[$myParentAlbumId]]['input'][] = $subArray;
                        $formatArray['CG']['entry'][$albumParentClub[$myParentAlbumId]]['hasImage'] = ($value['imageCount'] > 0) ? 1 : $formatArray['CG']['entry'][$albumParentClub[$myParentAlbumId]]['hasImage'];
                    }
                }
            } else {
                $subArray = array('id' => $value['albumId'], 'title' => $value['albumName'], 'bookMarkId' => $value['bookMarkId'], 'nocount' => 1,
                    'draggable' => $formatArray['R' . $value['albumRole']]['adminPrivilege'], 'itemType' => 'RG', 'adminPrivilege' => $value['albumPrivilege'],);

                if ($value['albumParent'] == 0) {
                    if ($value['imageCount'] > 0 || $formatArray['R' . $value['albumRole']]['adminPrivilege']) {
                        //This ia a parent Album
                        $index = count($albumParentRole[$value['albumRole']]);
                        $formatArray['R' . $value['albumRole']]['entry'][$index] = $subArray;
                        $formatArray['R' . $value['albumRole']]['entry'][$index]['hasImage'] = ($value['imageCount'] > 0) ? 1 : 0;
                        $albumParentRole[$value['albumRole']][$value['albumId']] = $index;
                    }
                } else {
                    if ($value['imageCount'] > 0 || $formatArray['R' . $value['albumRole']]['adminPrivilege']) {
                        //This is a sub album
                        $myParentAlbumId = $value['albumParent'];
                        $parentIndex = $albumParentRole[$value['albumRole']][$myParentAlbumId];
                        $subArray ['hasImage'] = ($value['imageCount'] > 0) ? 1 : 0;
                        $formatArray['R' . $value['albumRole']]['entry'][$parentIndex]['input'][] = $subArray;
                        $formatArray['R' . $value['albumRole']]['entry'][$parentIndex]['hasImage'] = ($value['imageCount'] > 0) ? 1 : $formatArray['R' . $value['albumRole']]['entry'][$parentIndex]['hasImage'];
                    }
                }
            }
        }
        return $bookmark;
    }

    /**
     * To generatet the title
     * @param array $checkedIdsArray contains selected ids
     * @param string $selected selection type
     * @return string title of the modal pop up
     */
    private function changeScopeTitlegenerator($checkedIdsArray, $selected)
    {
        $popupTitle = '';
        if ($selected === 'all') {
            $popupTitle = $this->get('translator')->trans('GALLERY_CHANGESCOPE_TITLE_MULTIPLE', array('%count%' => 'all'));
        } elseif (count($checkedIdsArray) > 1) {
            $popupTitle = $this->get('translator')->trans('GALLERY_CHANGESCOPE_TITLE_MULTIPLE', array('%count%' => count($checkedIdsArray)));
        } else {
            $popupTitle = $this->get('translator')->trans('GALLERY_CHANGESCOPE_TITLE_SINGLE');
        }

        return $popupTitle;
    }

    /**
     * To generatet the title
     * @param array $checkedIdsArray contains selected ids
     * @param string $albumName name of the album
     * @param string $modalType type of the pop up
     * @param string $selected selection type
     * @return array popup title details
     */
    private function imageRemovemodalTitle($checkedIdsArray, $albumName, $modalType, $selected)
    {
        $popupDetailsArray = array();
        switch ($modalType) {
            case "REMOVE_IMAGE" :
                if ($selected === 'all') {
                    $popupDetailsArray['title'] = $this->get('translator')->trans('GALLERY_REMOVE_TITLE_MULTIPLE', array('%count%' => 'all', '%albumName%' => $albumName));
                    $popupDetailsArray['text'] = $this->get('translator')->trans('GALLERY_REMOVE_TEXT_MULTIPLE');
                } elseif (count($checkedIdsArray) > 1) {
                    $popupDetailsArray['title'] = $this->get('translator')->trans('GALLERY_REMOVE_TITLE_MULTIPLE', array('%count%' => count($checkedIdsArray), '%albumName%' => $albumName));
                    $popupDetailsArray['text'] = $this->get('translator')->trans('GALLERY_REMOVE_TEXT_MULTIPLE');
                } else {
                    $popupDetailsArray['title'] = $this->get('translator')->trans('GALLERY_REMOVE_TITLE_SINGLE', array('%albumName%' => $albumName));
                    $popupDetailsArray['text'] = $this->get('translator')->trans('GALLERY_REMOVE_TEXT_SINGLE');
                }
                break;

            case "DELETE_IMAGE" :
                if (count($checkedIdsArray) > 1) {
                    $popupDetailsArray['title'] = $this->get('translator')->trans('GALLERY_DELETE_TITLE_MULTIPLE', array('%count%' => count($checkedIdsArray)));
                    $popupDetailsArray['text'] = $this->get('translator')->trans('GALLERY_DELETE_TEXT_MULTIPLE');
                } else {
                    $popupDetailsArray['title'] = $this->get('translator')->trans('GALLERY_DELETE_TITLE_SINGLE');
                    $popupDetailsArray['text'] = $this->get('translator')->trans('GALLERY_DELETE_TEXT_SINGLE');
                }
                break;
        }

        return $popupDetailsArray;
    }

    /**
     * To generatet the title
     * @param array $checkedIdsArray contains selected ids
     * @param string $albumName name of the album
     * @param string $modalType type of the pop up
     * @return array popup title details
     */
    private function assigntoalbumModalPopupTitleDetails($checkedIdsArray, $albumName, $modalType)
    {
        $popupDetailsArray = array();
        if ($modalType == 'MOVETO_ALBUM') {
            $popupDetailsArray['button'] = $this->get('translator')->trans('MOVE');
            if (count($checkedIdsArray) > 1) {
                $popupDetailsArray['title'] = $this->get('translator')->trans('GALLERY_MOVE_TITLE_MULTIPLE', array('%count%' => count($checkedIdsArray), '%albumName%' => $albumName));
            } else {
                $popupDetailsArray['title'] = $this->get('translator')->trans('GALLERY_MOVE_TITLE_SINGLE', array('%albumName%' => $albumName));
            }
        } else {
            $popupDetailsArray['button'] = $this->get('translator')->trans('ASSIGN');
            if (count($checkedIdsArray) > 1) {
                $popupDetailsArray['title'] = $this->get('translator')->trans('GALLERY_ASSIGN_TITLE_MULTIPLE', array('%count%' => count($checkedIdsArray)));
            } else {
                $popupDetailsArray['title'] = $this->get('translator')->trans('GALLERY_ASSIGN_TITLE_SINGLE');
            }
        }

        return $popupDetailsArray;
    }
}
