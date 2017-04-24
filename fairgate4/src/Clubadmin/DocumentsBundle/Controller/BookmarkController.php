<?php

/**
 * BookmarkController
 *
 * This controller was created for handling bookmarks in document mangement
 *
 * @package    ClubadminDocumentsBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 *
 */

namespace Clubadmin\DocumentsBundle\Controller;

use Common\UtilityBundle\Controller\FgController as ParentController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Common\UtilityBundle\Repository\Pdo\DocumentPdo;

class BookmarkController extends ParentController {

    /**
     * Function is used for listing the document bookmarks
     * 
     * @param Request $request Request object
     * 
     * @return template
     */
    public function bookmarkListAction(Request $request) {      
        $catType = $request->get('level1');
        $bookmarkDetails = $this->em->getRepository('CommonUtilityBundle:FgDmBookmarks')->getDocumentBookmarks($this->clubId, $this->contactId, strtoupper($catType));
        $club = $this->get('club');
        $docObj = new DocumentPdo($this->container);
        $bookmarkDocCount = $docObj->getDocumentBookmarksCount($club->get('id'),$club->get('clubHeirarchy'));
        if ($catType == 'club') {
            $allDocs = $bookmarkDocCount['clubDocCount'];
            $backLink = $this->generateUrl('club_documents_listing');
        } else if ($catType == 'contact') {
            $allDocs = $bookmarkDocCount['contactDocCount'];
            $backLink = $this->generateUrl('contact_documents_listing');
        } else if ($catType == 'team') {
            $allDocs = $bookmarkDocCount['teamDocCount'];
            $backLink = $this->generateUrl('team_documents_listing');
        } else {
            $allDocs = $bookmarkDocCount['workgroupDocCount'];
            $backLink = $this->generateUrl('workgroup_documents_listing');
        }
        $breadCrumb = array( 'back' => $backLink );
        
        return $this->render('ClubadminDocumentsBundle:Bookmark:bookmarklist.html.twig', array('bookmark_details' => $bookmarkDetails, 'breadCrumb' => $breadCrumb, 'clubId' => $this->clubId, 'contactId' => $this->contactId, 'docType' => strtoupper($catType), 'allDocs' => $allDocs, 'backLink' => $backLink));
    }

    /**
     * Function is used for updating the bookmark details
     * 
     * @param Request $request Request object
     * 
     * @return JsonResponse
     */
    public function bookmarkUpdateAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $bookmarkArr = json_decode($request->request->get('bookmarkArr'), true);

            if (count($bookmarkArr) > 0) {
                $this->em->getRepository('CommonUtilityBundle:FgDmBookmarks')->updateDeleteBookmarkDetails($bookmarkArr);
                
                return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('BOOKMARK_UPDATE')));
            }
        }
    }

    /**
     * Function to create and delete a bookmark from sidebar
     *
     * @return template
     */
    public function createdeleteBookmarkAction() {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $type = $request->get('type');
        if ($request->get('docType')) {
            $type = $request->get('docType');
        }
        $selectedId[] = $request->get('selectedId');
        if (count($selectedId) && $type) {
            $this->em->getRepository('CommonUtilityBundle:FgDmBookmarks')->createDeletebookmark($type, $selectedId, $this->clubId, $this->contactId);

            return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('BOOKMARK_DELETE')));
        } else {
            
            return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('BOOKMARK_NO_UPDATE')));
        }
    }
}
