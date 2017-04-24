<?php

namespace Clubadmin\ClubBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Clubadmin\Classes\Clublist;
use Symfony\Component\HttpFoundation\Request;
use Admin\UtilityBundle\Repository\Pdo\ClubPdo;

/**
 * BookmarkController
 *
 * This controller was handling the bookmarks for a club
 *
 * @package    ClubadminClubBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
class BookmarkController extends FgController
{

    /**
     * Pre exicute function to allow access to federation only
     *
     */
    public function preExecute()
    {
        parent::preExecute();
        if ($this->clubType != 'federation' && $this->clubType != 'sub_federation') {
            throw $this->createNotFoundException($this->clubTitle . ' have no access to this page');
        }
    }

    /**
     * Executes bookmarkList Action
     *
     * Function get all the bookmarks of user
     * @return template
     */
    public function bookmarkListAction()
    {
        $breadCrumb = array(
            'breadcrumb_data' => array(
                'Contacts' => 'contact',
                'Active Contacts' => 'contact',
                'Bookmark' => '#'
            ),
            'back' => $this->generateUrl('club_homepage')
        );
        $club = $this->get('club');
        $clubObj = new ClubPdo($this->container);
        $bookmarkDetails = $clubObj->getClubBookmarks($this->contactId, $this->clubId, $this->clubType);
        /* Active clubs count */
        $clublistClass = new Clublist($this->container, $club);
        $clublistClass->setCount();
        $clublistClass->setFrom();
        $clublistClass->setCondition();
        $countQuery = $clublistClass->getResult();
        $totalclublist = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($countQuery);
        $activeClubs = $totalclublist[0]['count'];
        /* Ends here */

        return $this->render('ClubadminClubBundle:Bookmark:bookmarklist.html.twig', array('bookmark_details' => $bookmarkDetails, 'activeClubs' => $activeClubs, 'breadCrumb' => $breadCrumb, 'contactId' => $this->contactId));
    }
//end bookmarkListAction()

    /**
     * Executes Updatbookmarks Action
     *
     * Function Update the bookmarks
     * @return template
     */
    public function updatebookmarkAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $bookmarkArr = json_decode($request->request->get('bookmarkArr'), true);
            if (count($bookmarkArr) > 0) {
                $adminConnection = $this->container->get('fg.admin.connection')->getAdminConnection();
                $this->em->getRepository('CommonUtilityBundle:FgCmBookmarks')->updatebookmark($bookmarkArr, 'fg_club_bookmarks', $adminConnection);

                return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('BOOKMARK_UPDATE')));
            }
        } else {

            return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('BOOKMARK_NO_UPDATE')));
        }
    }
//end updatebookmarkAction()
    /**
     * Executes createdeletebookmark Action
     *
     * Function to create and delete a bookmark from sidebar
     * @return template
     */

    public function handleBookmarkAction()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $type = $request->get('type');
        $selectedId = $request->get('selectedId', false);
        if ($selectedId && $type) {
            $adminEm = $this->container->get("fg.admin.connection")->getAdminEntityManager();
            $adminEm->getRepository('AdminUtilityBundle:FgClubBookmarks')->handleBookmark($type, array($selectedId), $this->clubId, $this->contactId);

            return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('BOOKMARK_UPDATE')));
        } else {

            return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('BOOKMARK_NO_UPDATE')));
        }
    }
//end handleBookmarkAction()

    /**
     * Executes getBookmarks Action
     *
     * @return template
     */
    public function getBookmarksAction()
    {
        $clubObj = new ClubPdo($this->container);
        $bookmarkDetails = $clubObj->getClubBookmarks($this->contactId, $this->clubId, $this->clubType);

        return new JsonResponse($bookmarkDetails);
    }
//end getBookmarksAction()
}

//end class

