<?php

namespace Clubadmin\SponsorBundle\Controller;

use Common\UtilityBundle\Controller\FgController as ParentController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Clubadmin\ContactBundle\Util\ContactlistData;
use Common\UtilityBundle\Repository\Pdo\SponsorPdo;
use Symfony\Component\HttpFoundation\Request;

/**
 * BookmarkController.
 *
 * This controller was created for handling bookmark in sponsor mangement
 *
 * @author     pitsolutions.ch
 *
 * @version    Fairgate V4
 */
class BookmarkController extends ParentController
{

    /**
     * Function to create and delete a bookmark from sidebar.
     *
     * @return template
     */
    public function createdeleteBookmarkAction()
    {        
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $type = $request->get('type');
        $selectedId = $request->get('selectedId');
        //FIX - type changed as overview for listing
        if ($type == 'overview') {
            $type = $selectedId;
        }
        $this->em->getRepository('CommonUtilityBundle:FgSmBookmarks')->createDeletebookmark($type, $selectedId, $this->clubId, $this->contactId);

        return new JsonResponse(array('status' => 'SUCCESS'));
    }

    /**
     * Function to show all the bookmarks of the user in sponsor management pages in sorting page.
     *
     * @return boomark template
     */
    public function bookmarkListAction()
    {
        $translatearray = array('prospect' => $this->get('translator')->trans('PROSPECTS'),
            'future_sponsor' => $this->get('translator')->trans('FUTURE_SPONSORS'),
            'active_sponsor' => $this->get('translator')->trans('ACTIVE_SPONSORS'),
            'former_sponsor' => $this->get('translator')->trans('FORMER_SPONSORS'),
            'single_person' => $this->get('translator')->trans('SINGLE_PERSONE'),
            'company' => $this->get('translator')->trans('COMPANIES'),
            'active_assignments' => $this->get('translator')->trans('ACTIVE_ASSIGNMENTS'),
            'future_assignments' => $this->get('translator')->trans('FUTURE_ASSIGNMENTS'),
            'recently_ended' => $this->get('translator')->trans('RECENTLY_ENDED'),
            'former_assignments' => $this->get('translator')->trans('FORMER_ASSIGNMENTS'),
        );
        $backLink = $this->generateUrl('clubadmin_sponsor_homepage');
        $bookmarkDetails = $this->em->getRepository('CommonUtilityBundle:FgSmBookmarks')->getSponsorBookmarkslisting($this->clubId, $this->contactId, $this->clubType, $this->container);
        //print_r($bookmarkDetails);die;
        $sponsorlistData = new ContactlistData($this->contactId, $this->container, 'sponsor');
        $contactData = $sponsorlistData->getContactData();
        //for collecting the assignment
        $sponsorPdo = new SponsorPdo($this->container);
        $assignmentCount = $sponsorPdo->assignmentOverviewCount($this->clubId, $this->clubType);
        $assignmentsCount = array();
        foreach ($assignmentCount[0] as $key => $value) {
            $assignmentsCount[$key] = $this->categoriesCountArray($value);
        }

        return $this->render('ClubadminSponsorBundle:Bookmark:bookmarklist.html.twig', array('bookmark_details' => $bookmarkDetails, 'translateArray' => $translatearray, 'backlink' => $backLink, 'clubId' => $this->clubId, 'contactId' => $this->contactId, 'allSponsors' => $contactData['totalcount'], 'assignmentsCount' => $assignmentsCount));
    }

    /**
     * Function is used for updating the bookmark details.
     *
     * @return response
     */
    public function bookmarkUpdateAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $bookmarkArr = json_decode($request->request->get('bookmarkArr'), true);
            if (count($bookmarkArr) > 0) {
                $this->em->getRepository('CommonUtilityBundle:FgSmBookmarks')->updateDeleteBookmarkDetails($bookmarkArr);

                return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('BOOKMARK_UPDATE')));
            }
        }
    }

    /**
     * function to get array structure for assignments count.
     *
     * @param int $count count sidebar
     *
     * @return string
     */
    private function categoriesCountArray($count)
    {
        $sponsorCount['sidebarCount'] = ($count != null) ? $count : 0;

        return $sponsorCount;
    }

    /**
     * Executes createdeletesponsorbookmark Action
     *
     * Function to create and delete a sponsor bookmark from sidebar
     *
     * @return array message array
     */
    public function createdeletesponsorbookmarkAction()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $type = $request->get('type');
        $selectedId = array($request->get('selectedId'));
        if (count($selectedId) && $type) {
            $this->em->getRepository('CommonUtilityBundle:FgSmBookmarks')->createDeletebookmark($type, $selectedId, $this->clubId, $this->contactId);

            return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('BOOKMARK_DELETE')));
        } else {
            return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('BOOKMARK_NO_UPDATE')));
        }
    }
}
