<?php
/**
 * This controller was created for handling gallery Browser.
 */
namespace Internal\GalleryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Internal\GalleryBundle\Util\GalleryList;
use Common\UtilityBundle\Repository\Pdo\GalleryPdo;

/**
 * DefaultController used for managing Gallery Browser.
 */
class GalleryBrowserController extends Controller
{

    /**
     * Function to create Gallery browser.
     * 
     * @param int|null $hasAddVideoLink flag- hasAddVideoLink
     */
    public function indexAction($hasAddVideoLink = null)
    {
        $bookedModules = $this->container->get('club')->parameters['bookedModulesDet'];
        $hasInternalRights = (in_array('frontend1', $bookedModules)) ? 1 : 0;
        $clubId = $this->container->get('club')->get('id');
        $contactLang = $this->container->get('club')->get('default_lang');  
        $galleryAlbum = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgGmAlbum')->getClubAlbumdetails($clubId, $contactLang);
        $terminologyService = $this->get('fairgate_terminology_service');
        $clubExecutiveBoard = $this->container->get('club')->get('club_executiveboard_id');;
       
        
        return $this->render('InternalGalleryBundle:GalleryBrowser:galleryBrowser.html.twig', array('hasInternalAccess' => $hasInternalRights,'executiveTitle' =>ucfirst($terminologyService->getTerminology('Executive Board', $this->container->getParameter('singular'))), 'hasAddVideoLink' => $hasAddVideoLink, 'allAlbums' => $galleryAlbum , 'clubExecutive' => $clubExecutiveBoard));
    }

    /**
     * Method to get gallery details dynamically.
     *
     * @return JsonResponse
     */
    public function getGalleryDetailsAjaxAction()
    {
        $galleryObj = new GalleryList($this->container, 'media_browser');
        $galleryPdo = new GalleryPdo($this->container);
        $galleryObj->mediaType = array('IMAGE');
        $galleryObj->setColumns();
        $galleryObj->setFrom();
        $galleryObj->setCondition();
        $galleryObj->addOrderBy('G.parent_id');
        $galleryObj->addOrderBy('G.sort_order'); //order by album sort order
        $galleryObj->addOrderBy('AIT.sort_order'); //order by item sort order

        $imageQuery = $galleryObj->getMyImages();
        $isCluborSuperAdmin = (count($this->container->get('contact')->get('mainAdminRightsForFrontend')) > 0) ? 1 : 0;
        $isClubGalleryAdmin = in_array('ROLE_GALLERY', $this->container->get('contact')->get('availableUserRights')) ? 1 : 0;
        $bookedModules = $this->container->get('club')->parameters['bookedModulesDet'];
        $hasInternalRights = (in_array('frontend1', $bookedModules)) ? 1 : 0;
        $imagesDetails = $galleryPdo->executeQuery($imageQuery);
        //To create imageDetails 
        $result = $this->createImageDetails($imagesDetails, $isCluborSuperAdmin, $isClubGalleryAdmin, $hasInternalRights);

        return new JsonResponse($result);
    }

    /**
     * To create imagedetails array
     * @param array $imagesDetails image details from database
     * @param type $isCluborSuperAdmin superadmin check
     * @param type $isClubGalleryAdmin gallery admin check
     * @param type $hasInternalRights internal right check
     */
    private function createImageDetails($imagesDetails, $isCluborSuperAdmin, $isClubGalleryAdmin, $hasInternalRights)
    {
        $result = array();
        foreach ($imagesDetails as $imageData) {
            if (!isset($result[$imageData['albumType']]) && !empty($imageData['albumType'])) {
                $result[$imageData['albumType']] = array();
            }

            if ($imageData['albumType'] == 'CLUB') {
                $albumRole = $imageData['albumClub'];
                $albumTitle = $this->get('translator')->trans('CLUB_GALLERY', array('%Club%' => $this->container->get('club')->get('title')));
            } else {
                $albumRole = $imageData['albumRole'];
                $albumTitle = $imageData['albumRoleName'];
                $sortOrder = (int) ($imageData['catSort'] . $imageData['roleSort']);
                $roleType = $imageData['roleType'];
            }
            if (!empty($imageData['albumType']) && !empty($albumRole)) {
                $result[$imageData['albumType']][$albumRole]['roleId'] = $albumRole;
                $result[$imageData['albumType']][$albumRole]['roleTitle'] = $albumTitle;
                $result[$imageData['albumType']][$albumRole]['sortOrder'] = $sortOrder;
                $result[$imageData['albumType']][$albumRole]['roleType'] = $roleType;
            }

            if ($imageData['albumParent'] == 0 && $imageData['albumId'] != '') {
                //is parentalbum
                if ($hasInternalRights == 0) {
                    continue;
                }

                $result[$imageData['albumType']][$albumRole][$imageData['albumId']]['detail'] = array('albumId' => $imageData['albumId'], 'albumName' => $imageData['albumName']);

                $result[$imageData['albumType']][$albumRole][$imageData['albumId']]['images'][] = array('itemId' => $imageData['itemId'], 'filePath' => $imageData['filepath'], 'fileSize' => $imageData['fileSize'], 'itemDescription' => $imageData['itemDescription'], 'sortOrder' => $imageData['albumItemSortOrder'], 'scope' => $imageData['scope'], 'isCoverImage' => $imageData['albumItemIsCoverImage']);
            } elseif ($imageData['albumParent'] > 0 && $imageData['albumId'] != '') {
                // is sub-album
                if ($hasInternalRights == 0) {
                    continue;
                }

                if (!is_array($result[$imageData['albumType']][$albumRole][$imageData['albumParent']])) {
                    //This condition will happen when main album doesn't have any images
                    //then we have to add the parent album details
                    $result[$imageData['albumType']][$albumRole][$imageData['albumParent']]['detail'] = array('albumId' => $imageData['albumParent']);
                }

                $result[$imageData['albumType']][$albumRole][$imageData['albumParent']]['subalbums'][$imageData['albumId']]['detail'] = array('albumId' => $imageData['albumId'], 'albumName' => $imageData['albumName'], 'parentId' => $imageData['albumParent']);

                $result[$imageData['albumType']][$albumRole][$imageData['albumParent']]['subalbums'][$imageData['albumId']]['images'][] = array('itemId' => $imageData['itemId'], 'filePath' => $imageData['filepath'], 'fileSize' => $imageData['fileSize'], 'itemDescription' => $imageData['itemDescription'], 'sortOrder' => $imageData['albumItemSortOrder'], 'scope' => $imageData['scope'], 'isCoverImage' => $imageData['albumItemIsCoverImage']);
            } elseif ($imageData['albumId'] == '') {
                //Orphan and external images
                if ($imageData['itemSource'] != 'gallery' && ($isClubGalleryAdmin || $isCluborSuperAdmin)) {
                    $result['EXTERNAL'][0]['roleId'] = 0;
                    $result['EXTERNAL'][0]['roleTitle'] = $this->get('translator')->trans('EXTERNAL_IMAGES');
                    $result['EXTERNAL'][0][0]['detail'] = array('albumId' => 0, 'albumName' => 'External Album');
                    $result['EXTERNAL'][0][0]['images'][] = array('itemId' => $imageData['itemId'], 'filePath' => $imageData['filepath'], 'fileSize' => $imageData['fileSize'], 'itemDescription' => $imageData['itemDescription'], 'sortOrder' => $imageData['albumItemSortOrder'], 'scope' => $imageData['scope']);
                }
            }
        }

        return $result;
    }
}
