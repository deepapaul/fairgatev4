<?php

/**
 * BookmarkController
 *
 * This controller was handling the bookmarks for a contact
 *
 * @package    ClubadminContactBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
namespace Clubadmin\ContactBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Clubadmin\Util\Contactlist;
use Common\UtilityBundle\Repository\Pdo\ClubPdo;

/**
 * BookmarkController used for managing contact bookmarks functionalities
 *
 */
class BookmarkController extends FgController
{

    /**
     * Executes bookmarkList Action
     *
     * Function get all the bookmarks of user
     *
     * @return boomark template
     */
    public function bookmarkListAction()
    {
        $breadCrumb = array(
            'breadcrumb_data' => array(
                'Contacts' => 'contact',
                'Active Contacts' => 'contact',
                'Bookmark' => '#'
            ),
            'back' => '#'
        );
        $contact = $this->get('contact');
        $club = $this->get('club');
        $clubHeirarchy = $club->get('clubHeirarchy');
        $terminologyService = $this->get('fairgate_terminology_service');
        $execBoardTerm = ucfirst($terminologyService->getTerminology('Executive Board', $this->container->getParameter('singular')));
        $staticFilterTrans = $this->getStaticFilterTrans();
        
        $objClubPdo = new ClubPdo($this->container);
        $bookmarkDetails = $objClubPdo->getContactBookmarks($this->contactId, $this->clubId, $this->clubType, $clubHeirarchy, $this->clubExecutiveBoardId, $execBoardTerm, $staticFilterTrans, true, $this->federationId, $contact->get('corrLang'));
        $activeContacts = $this->activeContactscount();

        return $this->render('ClubadminContactBundle:Bookmark:bookmarklist.html.twig', array('bookmark_details' => $bookmarkDetails, 'activeContacts' => $activeContacts, 'breadCrumb' => $breadCrumb, 'contactId' => $this->contactId));
    }

    /**
     * Executes Updatbookmarks Action
     *
     * Function Update the bookmarks
     *
     * @return array message array
     */
    public function updatebookmarkAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $bookmarkArr = json_decode($request->request->get('bookmarkArr'), true);
            if (count($bookmarkArr) > 0) {
                $this->em->getRepository('CommonUtilityBundle:FgCmBookmarks')->updatebookmark($bookmarkArr);

                return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('BOOKMARK_UPDATE')));
            }
        } else {
            return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('BOOKMARK_NO_UPDATE')));
        }
    }

    /**
     * Executes createdeletebookmark Action
     *
     * Function to create and delete a bookmark from sidebar
     *
     * @return array message array
     */
    public function createdeletebookmarkAction(Request $request)
    {
        $type = $request->get('type');
        $selectedId[] = $request->get('selectedId');
        if (count($selectedId) && $type) {
            $this->em->getRepository('CommonUtilityBundle:FgCmBookmarks')->createDeletebookmark($type, $selectedId, $this->clubId, $this->contactId);

            return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('BOOKMARK_DELETE')));
        } else {
            return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('BOOKMARK_NO_UPDATE')));
        }
    }

    /**
     * Function is used to get all Active contacts
     *
     * @return Int active contact count
     */
    public function activeContactscount()
    {
        $club = $this->get('club');
        $contactlistClass = new Contactlist($this->container, $this->contactId, $club);
        $contactlistClass->setCount();
        $contactlistClass->setFrom();
        $contactlistClass->setCondition();
        $countQuery = $contactlistClass->getResult();
        $totalcontactlist = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($countQuery);
        $count = $totalcontactlist[0]['count'];

        return $count;
    }

    /**
     * Function to get Translated terms of static filters(singleperson/company/member/sponsor
     *
     * @return Array Translated terms of static filters(singleperson/company/member/sponsor
     */
    private function getStaticFilterTrans()
    {
        $terminologyService = $this->get('fairgate_terminology_service');
        return array(
            '1' => $this->get('translator')->trans('SINGLE_PERSONE'),
            '2' => $this->get('translator')->trans('CONTACT_PROPERTIES_COMPANIES'),
            '3' => $this->get('translator')->trans('MEMBERS'),
            '4' => ucfirst($terminologyService->getTerminology('Federation member', $this->container->getParameter('plural'))),
        );
    }
}
