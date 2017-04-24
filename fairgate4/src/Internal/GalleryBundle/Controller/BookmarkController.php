<?php
/**
 * BookmarkController.
 *
 * This controller was handling the bookmarks for  gallery
 *
 * @author     pitsolutions.ch
 *
 * @version    Fairgate V4
 */
namespace Internal\GalleryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * BookmarkController used for managing gallery bookmarks functionalities.
 */
class BookmarkController extends Controller
{

    /**
     * Executes bookmarkList Action.
     *
     * Function get all the bookmarks of user
     *
     * @return boomark template
     */
    public function bookmarklistAction()
    {
        $em = $this->getDoctrine()->getManager();
        $club = $this->get('club');
        $clubId = $club->get('id');
        $contactId = $this->get('contact')->get('id');
        $clubDefaultLang = $club->get('default_lang');
        $backLink = $this->generateUrl('internal_gallery_view');
        $breadCrumb = array(
            'breadcrumb_data' => array(),
            'back' => $backLink,
        );
        $bookmarkDetails = $em->getRepository('CommonUtilityBundle:FgGmBookmarks')->getGalleryBookmarks($clubId, $contactId, $clubDefaultLang);
        $allimageCount = $em->getRepository('CommonUtilityBundle:FgGmItems')->getAllImageCount($clubId);

        return $this->render('InternalGalleryBundle:Bookmark:bookmarklist.html.twig', array('bookmark_details' => $bookmarkDetails, 'breadCrumb' => $breadCrumb, 'backLink' => $backLink, 'clubId' => $clubId, 'contactId' => $contactId, 'allimageCount' => $allimageCount));
    }

    /**
     * Executes bookmarkUpdatet Action.
     * Function is used for updating the bookmark details
     *
     * @param Request $request Request object
     *
     * @return response
     */
    public function bookmarkUpdateAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $result = '';
        if ($request->getMethod() == 'POST') {
            $bookmarkArr = json_decode($request->request->get('bookmarkArr'), true);
            if (count($bookmarkArr) > 0) {
                $result = $em->getRepository('CommonUtilityBundle:FgGmBookmarks')->updateDeleteBookmarkDetails($bookmarkArr);

                return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('GALLERY_BOOKMARK_UPDATE_SUCCESS')));
            }
        }
    }

    /**
     * update sidebar bookmark.
     *
     * @param Request $request Request object
     *
     * @return JsonResponse
     */
    public function sidebarBookmarkUpdateAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $club = $this->get('club');
        $clubId = $club->get('id');
        $contactId = $this->get('contact')->get('id');
        $selectedId = $request->get('selectedId');
        $type = $request->get('type');
        $em->getRepository('CommonUtilityBundle:FgGmBookmarks')->updateBookmark($selectedId, $type, $clubId, $contactId);

        return new JsonResponse(array('status' => 'SUCCESS', 'sync' => 1));
    }
}
