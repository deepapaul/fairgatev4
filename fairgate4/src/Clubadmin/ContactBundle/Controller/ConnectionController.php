<?php

/**
 * Connection Controller
 *
 * This controller was created for handling contact connections in Contact management.
 *
 * @package    ClubadminContactBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 *
 */
namespace Clubadmin\ContactBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Common\UtilityBundle\Controller\FgController as ParentController;
use Clubadmin\Util\Contactlist;
use Clubadmin\ContactBundle\Util\ContactDetailsAccess;
use Clubadmin\ContactBundle\Util\NextpreviousContact;
use Clubadmin\SponsorBundle\Util\NextpreviousSponsor;
use Common\UtilityBundle\Util\FgUtility;
use Symfony\Component\HttpFoundation\Request;

/**
 * Connection Controller
 *
 * This controller is used for handling contact connections.
 */
class ConnectionController extends ParentController
{

    /**
     * connection action
     *
     * @param int $offset  offset
     * @param int $contact contact id
     * @param string $module contact or sponsor
     * @return template
     * @throws exception
     */
    public function indexAction($offset, $contact, $module)
    {
        $club = $this->get('club');
        $moduleType = $this->get('club')->get('moduleMenu');
        $return['breadCrumb'] = array('breadcrumb_data' => array(), 'back' => ($moduleType === 'sponsor') ? $this->generateUrl('clubadmin_sponsor_homepage') : (($moduleType == "archivedsponsor") ? $this->generateUrl('view_archived_sponsors') : (($moduleType === 'contactarchive') ? $this->generateUrl('archive_index') : $this->generateUrl('contact_index'))));
        $return['bookedModuleDetails'] = $club->get('bookedModulesDet');
        $return['contactDetails'] = $this->contactDetails($contact);
        $return['contactDetails']['mainClubId'] = (($club->get('type') == 'federation') || ($club->get('type') == 'sub_federation')) ? $return['contactDetails']['mainClubId'] : $return['contactDetails']['clubId'];
        $pageData = $this->getConnectionPageData($contact, $module, $offset);
        $isCompany = $return['contactDetails']['is_company'];
        $contCountDetails = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getContactCountDetails($this->clubId, $contact, $isCompany, $this->clubType, false, true, true, true, false, false, false, $this->federationId, $this->subFederationId);
        $nextPrevData = $this->getNavigationData($module, $contact, $offset);
        $connData = $this->getContactConnectionData($return, $contact);
        $contCountDetails['connectionCount'] = $connData['connectionCount'];
        $isArchiveSponsor = false;
        if ($module == 'sponsor') {
            $return['servicesCount'] = $this->em->getRepository('CommonUtilityBundle:FgSmBookings')->getCountOfSponsorServices($this->clubId, $contact, $isArchiveSponsor);
            $return['adsCount'] = $this->em->getRepository('CommonUtilityBundle:FgSmSponsorAds')->getCountOfSponsorAds($this->clubId, $contact);
            $contCountDetails['servicesCount'] = $return['servicesCount'];
            $contCountDetails['adsCount'] = $return['adsCount'];
        }
        $tabsData = FgUtility::getTabsArrayDetails($this->container, $pageData['tabs'], $offset, $contact, $contCountDetails, "connection", $module);
        unset($pageData['tabs']);
        $pageData['tabs'] = $tabsData;
        $return = array_merge($return, $pageData, $contCountDetails, $nextPrevData, $connData);
        $return['isReadOnlyContact'] = $this->isReadOnlyContact();
        $return['clubType'] = $club->get('type');

        return $this->render('ClubadminContactBundle:Connection:connections.html.twig', $return);
    }

    /**
     * Method to get readonly status of current contact
     *
     * @return boolean $isReadOnlyContact
     */
    private function isReadOnlyContact()
    {
        $allowedModules = $this->container->get('contact')->get('allowedModules');
        if (in_array('readonly_contact', $allowedModules) && !in_array('contact', $allowedModules)) {
            $isReadOnlyContact = 1;
        } else {
            $isReadOnlyContact = 0;
        }

        return $isReadOnlyContact;
    }

    /**
     * Function to get data in connection page of a contact.
     *
     * @param int    $contact Contact id
     * @param string $module  Current module
     * @param int    $offset  Contact offset value
     *
     * @return array $return Result array
     */
    private function getConnectionPageData($contact, $module, $offset)
    {
        $accessObj = new ContactDetailsAccess($contact, $this->container, $module);
        if ($accessObj->accessType == 'NO_ACCESS' || !in_array('connection', $accessObj->tabArray)) {
            //throw $this->createNotFoundException($this->clubTitle . ' have no access to this page');
            $this->fgpermission->checkClubAccess('', 'contactconnection');
        }
        $contactType = $accessObj->contactviewType;
        $contactMenuModule = $accessObj->menuType;
        if ($contactMenuModule == 'archive') {
            $this->get('club')->set('moduleMenu', 'contactarchive');
        } elseif ($contactMenuModule == 'formerfederationmember') {
            $this->get('club')->set('moduleMenu', 'formerfederationmember');
        }
        $this->session->set('contactType', $contactType);
        $return = array('readOnly' => false, 'clubId' => $this->clubId, 'module' => $module, 'offset' => $offset, 'tabs' => $accessObj->tabArray);

        return $return;
    }

    /**
     * Function to get connection details of a contact.
     *
     * @param array $return  Data array
     * @param int   $contact Contact id
     *
     * @return array $return Result array
     */
    private function getContactConnectionData($return, $contact)
    {
        $connectionCount = 0;
        if ($return['contactDetails']['is_company']) {
            $return['companyContacts'] = array();
            if ($return['contactDetails']['has_main_contact'] && !empty($return['contactDetails']['comp_def_contact'])) {
                $return['companyContacts'] = $this->em->getRepository('CommonUtilityBundle:FgCmLinkedcontact')->getMainContact($return['contactDetails']['comp_def_contact'], $this->clubId);
            }
            $return['relation'] = $this->em->getRepository('CommonUtilityBundle:FgCmRelation')->getRelations($this->clubId, 'other', $this->clubDefaultSystemLang);
            if (count($return['companyContacts']) > 0) {
                $connectionCount = $connectionCount + 1;
            }
        } else {
            $return['householdContacts'] = $this->em->getRepository('CommonUtilityBundle:FgCmLinkedcontact')->getLinkedContacts($this->clubId, $contact, true, $this->clubType, $this->clubDefaultSystemLang);
            $return['relation'] = $this->em->getRepository('CommonUtilityBundle:FgCmRelation')->getRelations($this->clubId, 'both', $this->clubDefaultSystemLang);
            $return['companyContacts'] = $this->em->getRepository('CommonUtilityBundle:FgCmLinkedcontact')->getLinkedDefaultContacts($contact, $this->clubType, $this->clubId);
            $connectionCount = $connectionCount + count($return['householdContacts']) + count($return['companyContacts']);
        }
        $return['otherContacts'] = $this->em->getRepository('CommonUtilityBundle:FgCmLinkedcontact')->getLinkedContacts($this->clubId, $contact, false, $this->clubType, $this->clubDefaultSystemLang);
        $return['connectionCount'] = $connectionCount + count($return['otherContacts']);

        return $return;
    }

    /**
     * Function to get navigation(next/prev) data of a contact.
     *
     * @param string $module  Current module
     * @param int    $contact Contact id
     * @param int    $offset  Contact offset value
     *
     * @return array $return Result array
     */
    private function getNavigationData($module, $contact, $offset)
    {
        if ($module == "sponsor") {
            $return['servicesCount'] = $this->em->getRepository('CommonUtilityBundle:FgSmBookings')->getCountOfSponsorServices($this->clubId, $contact);
            $return['adsCount'] = $this->em->getRepository('CommonUtilityBundle:FgSmSponsorAds')->getCountOfSponsorAds($this->clubId, $contact);
            // Generating next and previous data for the next-previous functionality in the overview page
            $nextprevious = new NextpreviousSponsor($this->container);
            $nextPreviousResultset = $nextprevious->nextPreviousSponsorData($this->contactId, $contact, $offset, 'sponsor_connection', 'offset', 'contact', $flag = 0);
        } else {
            $groupUserDetails = $this->em->getRepository('CommonUtilityBundle:SfGuardGroup')->getGroupDetails($this->conn, $this->clubId, $contact);
            $hasUserRights = (count($groupUserDetails) > 0) ? 1 : 0;
            $return['hasUserRights'] = $hasUserRights;
            $missingReqAssgn = $this->em->getRepository('CommonUtilityBundle:FgRmCategory')->missingReqFedAssign($contact, $this->clubId, $this->federationId, $this->subFederationId, $this->clubType, $this->clubDefaultLang, $this->conn);
            $return['missingReqAssgment'] = $missingReqAssgn;
            // Generating next and previous data for the next-previous functionality in the overview page
            $nextprevious = new NextpreviousContact($this->container);
            $nextPreviousResultset = $nextprevious->nextPreviousContactData($this->contactId, $contact, $offset, 'contact_connection', 'offset', 'contact', $flag = 0);
        }
        $return['nextPreviousResultset'] = $nextPreviousResultset;

        return $return;
    }

    /**
     * Contact autocomplete action
     *
     * @param string $term          search parameter
     * @param string $passedColumns Extra columns to be taken
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function contactNamesAction(Request $request, $term, $passedColumns = "")
    {
        $exclude = $request->get('exclude');
        $isComany = $request->get('isCompany');
        $contType = $request->get('type');
        $contactsArray = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getAutocompleteContacts($exclude, $isComany, $contType, $this->container, $this->clubId, $this->clubType, $passedColumns, $term,0,0,0);

        return new JsonResponse($contactsArray);
    }

    /**
     * Function to get implications of a contact connection
     *
     * @param int $contact contact id
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getImplicationsAction(Request $request, $contact)
    {
        $linkedContactId = $request->get('linked_contact_id');
        $relationId = $request->get('relation_id');
        $relationType = $request->get('relation_type');
        $implications = $this->em->getRepository('CommonUtilityBundle:FgCmLinkedcontact')->getImplications($this->clubId, $contact, $linkedContactId, $relationId, $relationType, $this->get('club'), $this->container, $this->clubDefaultSystemLang);

        return new JsonResponse($implications);
    }

    /**
     * Function to get contact details
     *
     * @param int $contactId contact id
     *
     * @return array/false
     */
    private function contactDetails($contactId)
    {
        $club = $this->get('club');
        $contactlistClass = new Contactlist($this->container, '', $club);
        $contactlistClass->setColumns(array('contactNameWithComma', 'contactname', 'contactid', 'is_household_head', 'is_seperate_invoice', 'is_company', 'has_main_contact', 'comp_def_contact', 'comp_def_contact_fun', 'clubId', '`21`', '`68`', 'mainClubId'));
        $contactlistClass->setFrom('*');
        $contactlistClass->setCondition();
        $sWhere = " fg_cm_contact.id=$contactId";
        $contactlistClass->addCondition($sWhere);
        $listquery = $contactlistClass->getResult();
        $fieldsArray = $this->conn->fetchAll($listquery);

        return isset($fieldsArray[0]) ? $fieldsArray[0] : false;
    }

    /**
     * Function to add or remove contact connections
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function updateConnectionsAction(Request $request)
    {
        $contactId = $request->get('contactId', '0');
        $isCompany = $request->get('isCompany', '0');
        $currHouseholdCntIds = $request->get('currHouseholdCntIds', '0');
        $connArray = json_decode($request->get('connArr'), true);
        $this->em->getRepository('CommonUtilityBundle:FgCmLinkedcontact')->updateLinkedConnections($connArray, $this->clubId, $contactId, $isCompany, $currHouseholdCntIds, $this->contactId, $this->container, $this->clubType, $this->get('club'), $this->clubDefaultSystemLang);
        $redirectPage = true;
        if ($request->get('redirectPage') == 'false') {
            $redirectPage = false;
        }
        if ($redirectPage) {
            return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('CONNECTIONS_UPDATED')));
        } else {
            return new JsonResponse(array('status' => 'SUCCESS', 'noparentload' => true, 'flash' => $this->get('translator')->trans('CONNECTIONS_UPDATED')));
        }
    }

    /**
     * Template for connecting two contacts
     *
     * @return template
     */
    public function connectcontactsAction(Request $request)
    {
        $contactData = $request->get('contactData');
        $cntDataArr = explode('*#*##*#*', $contactData);
        $cont1DataArr = explode('%#-#%', $cntDataArr[0]);
        $cont2DataArr = explode('%#-#%', $cntDataArr[1]);
        $cont1DataArr[2] = urldecode($cont1DataArr[2]); //for decoding contact name
        $cont2DataArr[2] = urldecode($cont2DataArr[2]); //for decoding contact name
        $type = $this->getConnectionType($cont1DataArr[1], $cont2DataArr[1]);
        $connectionsArray = $this->getCurrentConnections($cont2DataArr);
        $validationArr = $this->checkAlreadyConnected($type, $cont1DataArr, $cont2DataArr, $connectionsArray);
        $hasError = $validationArr['hasError'];
        $disableFields = $validationArr['disableFields'];
        if ($hasError) {
            return new JsonResponse(array('status' => 'FAILURE', 'flash' => $this->get('translator')->trans('CONTACTS_ALREADY_CONNECTED')));
        } else {
            $relationsArray = array();
            $householdContacts = array();
            if ($type == 'SS') {
                $relationsArray = $this->em->getRepository('CommonUtilityBundle:FgCmRelation')->getRelations($this->clubId, 'both', $this->clubDefaultSystemLang);
                $householdContacts = array_unique($connectionsArray['household']);
            }
            $return = array('cont1DataArr' => $cont1DataArr, 'cont2DataArr' => $cont2DataArr, 'type' => $type, 'relationsArray' => $relationsArray, 'householdContacts' => $householdContacts, 'disableFields' => $disableFields);

            return $this->render('ClubadminContactBundle:Connection:connectcontacts.html.twig', $return);
        }
    }

    /**
     * Function to get connection type of two contacts.
     *
     * @param int $cnt1IsComp Whether first contact is a company contact
     * @param int $cnt2IsComp Whether second contact is a company contact
     *
     * @return string $type Connection type
     */
    private function getConnectionType($cnt1IsComp, $cnt2IsComp)
    {
        $type = '';
        if ($cnt1IsComp == 0) {
            $type = ($cnt2IsComp == 0) ? 'SS' : 'SC';
        } else {
            $type = ($cnt2IsComp == 0) ? 'CS' : 'CC';
        }

        return $type;
    }

    /**
     * Function for getting connections to be disabled while connecting two contacts.
     *
     * @param string $type         Connection type
     * @param array  $cont1DataArr Data array of first contact
     * @param array  $cont2DataArr Data array of second contact
     * @param int    $cnt1IsComp   Whether first contact is company contact
     *
     * @return array $disableFields Array of disable fields
     */
    private function getFieldsToDisable($type, $cont1DataArr, $cont2DataArr, $cnt1IsComp)
    {
        $disableFields = array();
        if (($type == 'SC') || ($type == 'CS')) {
            //check whether the contacts are of same heirarchy
            if (($cont1DataArr[3] != $this->clubId) || ($cont2DataArr[3] != $this->clubId)) {
                $disableFields[] = 'company';
            } else {
                //get main contact
                $compContId = $cnt1IsComp ? $cont1DataArr[0] : $cont2DataArr[0];
                $personalContId = $cnt1IsComp ? $cont2DataArr[0] : $cont1DataArr[0];
                $mainContactId = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->find($compContId)->getCompDefContact();
                if ($mainContactId == $personalContId) {
                    $disableFields[] = 'company';
                }
            }
        }

        return $disableFields;
    }

    /**
     * Function to get current connections between two contacts.
     *
     * @param array $cont2DataArr Data array of second contact.
     *
     * @return array $connectionsArray Connections array.
     */
    private function getCurrentConnections($cont2DataArr)
    {
        $connections = $this->em->getRepository('CommonUtilityBundle:FgCmLinkedcontact')->getAllConnections($this->clubId, $cont2DataArr[0], $this->clubDefaultSystemLang, 1);
        $connectionsArray = array();
        $connectionsArray['household'] = $connectionsArray['otherpersonal'] = $connectionsArray['othercompany'] = array();
        foreach ($connections as $connection) {
            $connectionsArray[$connection['type']][] = $connection['linked_contact_id'];
        }

        return $connectionsArray;
    }

    /**
     * Function to check whether two contacts are already connected.
     *
     * @param string $type              Connection type
     * @param array  $cont1DataArr      Data array of first contact
     * @param array  $cont2DataArr      Data array of second contact
     * @param array  $connectionsArray  Current connections array
     *
     * @return array Result array
     */
    private function checkAlreadyConnected($type, $cont1DataArr, $cont2DataArr, $connectionsArray)
    {
        $cnt1IsComp = $cont1DataArr[1];
        $disableFields = $this->getFieldsToDisable($type, $cont1DataArr, $cont2DataArr, $cnt1IsComp);
        $hasError = false;
        switch ($type) {
            case 'CC':
                if (in_array($cont1DataArr[0], $connectionsArray['othercompany'])) {
                    $hasError = true;
                }
                break;
            case 'CS':
                if (in_array($cont1DataArr[0], $connectionsArray['othercompany'])) {
                    if (in_array('company', $disableFields)) {
                        $hasError = true;
                    } else {
                        $disableFields[] = 'othercompany';
                    }
                }
                break;
            case 'SC':
                if (in_array($cont1DataArr[0], $connectionsArray['otherpersonal'])) {
                    if (in_array('company', $disableFields)) {
                        $hasError = true;
                    } else {
                        $disableFields[] = 'othercompanypersonal';
                    }
                }
                break;
            case 'SS':
                if (in_array($cont1DataArr[0], $connectionsArray['household'])) {
                    $disableFields[] = 'household';
                }
                if (in_array($cont1DataArr[0], $connectionsArray['otherpersonal'])) {
                    if (in_array('household', $disableFields)) {
                        $hasError = true;
                    } else {
                        $disableFields[] = 'otherpersonal';
                    }
                }
                break;
            default:
                break;
        }

        return array('hasError' => $hasError, 'disableFields' => $disableFields);
    }

    /**
     * Function to get all contacts of club.
     *
     * @return array $contacts Json Array of Contacts.
     */
    public function getAllContactNamesAction()
    {
        $firstname = "`" . $this->container->getParameter('system_field_firstname') . "`";
        $lastname = "`" . $this->container->getParameter('system_field_lastname') . "`";
        $passedColumns = "IF (C.is_company=0 ,CONCAT($lastname,' ',$firstname,IF(DATE_FORMAT(`4`,'%Y') = '0000' OR `4` is NULL OR `4` ='','',CONCAT(' (',DATE_FORMAT(`4`,'%Y'),')'))),IF(has_main_contact=1,CONCAT(`9`,' (',$lastname,' ',$firstname,')'),`9`)) as value";
        $contacts = $this->contactNamesAction('', $passedColumns);

        return $contacts;
    }
}
