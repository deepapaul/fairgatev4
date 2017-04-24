<?php

namespace Common\UtilityBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Common\UtilityBundle\Controller\FgController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Clubadmin\DocumentsBundle\Util\Documentlist;
use Clubadmin\Util\Contactlist;
use Clubadmin\ContactBundle\Util\ContactDetailsAccess;
use Common\UtilityBundle\Util\FgUtility;
use Clubadmin\DocumentsBundle\Util\DocumentDetails;

/**
 * TopNavigationController
 *
 * This TopNavigationController was created for handling autocomplete methods in top navigation menu
 *
 * @package    CommonUtilityBundle
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
class TopNavigationController extends FgController
{

    /**
     * This method is used for getting all clubs
     * to list in the autocomplete fields in top navigation menu
     *
     * @param Request $request Request object
     *
     * @return Json response
     */
    public function getClubsForSearchAction(Request $request)
    {
        $searchTerm = FgUtility::getSecuredData($request->get('term'), $this->conn);
        $clubPdo = new \Admin\UtilityBundle\Repository\Pdo\ClubPdo($this->container);
        $clubs = $clubPdo->getClubsForSearch($this->clubId, $searchTerm);

        return new JsonResponse($clubs);
    }

    /**
     * This method is used for getting all documents
     * to list in the autocomplete fields in top navigation menu
     *
     * @param Request $request Request object
     *
     * @return Json response
     */
    public function getDocumentsForSearchAction(Request $request)
    {
        $searchTerm = FgUtility::getSecuredData($request->get('term'), $this->conn);
        $clubId = $this->clubId;
        $clubHeirarchy = array($clubId);
        $documentlistClass = new Documentlist($this->container, "ALL");
        $docObj = new DocumentDetails($this->container);
        $finalQuery = $docObj->getQueryForDocumentSearch($documentlistClass, $clubId, $clubHeirarchy, $searchTerm);
        $documents = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($finalQuery);

        return new JsonResponse($documents);
    }

    /**
     * This method is used for getting all contacts or sponsors
     * to list in the autocomplete fields in top navigation menu
     *
     * @param Request $request Request object
     * @param type $module
     *
     * @return JsonResponse
     */
    public function getContactsForSearchAction(Request $request, $module)
    {
        $searchTerm = FgUtility::getSecuredData($request->get('term'), $this->conn);
        $club = $this->get('club');
        $dob = $this->container->getParameter('system_field_dob');
        $contactFlag = ($module === 'contact') ? 'all' : 'allsponsors';
        $contactlistClass = new Contactlist($this->container, '', $club, $contactFlag);
        $contacts = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getContactsForSearch($contactlistClass, $dob, $searchTerm, '', false, 0);
        for ($i = 0; $i < count($contacts); $i++) {
            $contactId = $contacts[$i]['id'];
            $contacts[$i]['path'] = $this->getOverviewPathOfContact($contactId, $module);
        }

        return new JsonResponse($contacts);
    }

    /**
     * To get overview path of contact, if contact is archive or fed format, return data path
     * @param int $contactId
     * @param string $module
     * @return string
     */
    private function getOverviewPathOfContact($contactId, $module)
    {
        $accessObj = new ContactDetailsAccess($contactId, $this->container);
        $contactType = $accessObj->contactviewType;
        if ($contactType == 'contact') {
            if ($module === 'contact') {
                $path = $this->generateUrl('render_contact_overview', array('offset' => '0', 'contact' => $contactId));
            } else {
                $path = $this->generateUrl('render_sponsor_overview', array('offset' => '0', 'sponsor' => $contactId));
            }
        } else { // $contactType may be archive or fedformar
            if ($module === 'contact') {
                $path = $this->generateUrl('contact_data', array('offset' => '0', 'contact' => $contactId));
            } else {
                $path = $this->generateUrl('sponsor_contact_data', array('offset' => '0', 'contact' => $contactId));
            }
        }

        return $path;
    }
}
