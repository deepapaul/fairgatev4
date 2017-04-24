<?php

/**
 * SponsorLogController
 *
 * This controller is used for log entries of sponsor
 *
 * @package    ClubadminSponsorBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */

namespace Clubadmin\SponsorBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Intl\Intl;
use Clubadmin\Util\Contactlist;
use Clubadmin\ContactBundle\Util\ContactDetailsAccess;
use Clubadmin\SponsorBundle\Util\NextpreviousSponsor;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Util\FgPermissions;
use Common\UtilityBundle\Repository\Pdo\ContactPdo;

class SponsorLogController extends FgController {

    /**
     * Function for listing log entries
     *
     * @param int $offset Offset
     * @param int $contact contact id
     *
     * @return template
     */
    public function indexAction($offset, $contact) {

        $accessObj = new ContactDetailsAccess($contact, $this->container, 'sponsor');
        if ($accessObj->accessType == 'NO_ACCESS' || !in_array('log', $accessObj->tabArray)) {
           $permissionObj = new FgPermissions($this->container); 
           $permissionObj->checkClubAccess('','no_access');
        }
        $contactType = $accessObj->contactviewType;
        $isArchiveSponsor = false;
        //$this->session->set('contactType', $contactType);
        if ($accessObj->menuType == 'archive' && $accessObj->module=='sponsor') {
            $this->get('club')->set('moduleMenu', 'archivedsponsor');
            $contactType = 'archivedsponsor';
            $this->session->set('contactType', $contactType);
            $isArchiveSponsor = true;
        }
        
        $contactModuleType = $this->get('club')->get('moduleMenu');
       
        //translate functions
        $logTranslateFields = $this->logTranslateFields();
        $contactData = $this->contactDetails($contact, $contactType);
        $logTabs = array(1 => 'data', 2 => 'services');
        $logTabsCount = count($logTabs);
        $activeTab = '1';
        $isCompany = $contactData['is_company'];
        $contCountDetails = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getContactCountDetails($this->clubId, $contact, $isCompany, $this->clubType, true, true, true,false ,false,false,$isArchiveSponsor, $this->federationId, $this->subFederationId);
        $countryList = Intl::getRegionBundle()->getCountryNames();
        $languageList = Intl::getLanguageBundle()->getLanguageNames();
        $languageAttrIds = array($this->container->getParameter('system_field_corress_lang'));
        $countryAttrIds = array($this->container->getParameter('system_field_corres_land'), $this->container->getParameter('system_field_invoice_land'), $this->container->getParameter('system_field_nationality1'), $this->container->getParameter('system_field_nationality2'));
        $sysAttrTransIds = array($this->container->getParameter('system_field_gender'), $this->container->getParameter('system_field_salutaion'));

        // Generating next and previous data for the next-previous functionality in the overview page
        $nextprevious = new NextpreviousSponsor($this->container);
        $nextPreviousResultset = $nextprevious->nextPreviousSponsorData($this->contactId, $contact, $offset, 'sponsor_log_listing', 'offset', 'contact', $flag = 0);

        //data sets to be passed to twig
        $dataSetArray = array('contactData' => $contactData, 'contactId' => $contact, 'offset' => $offset, 'logTranslateFields' => $logTranslateFields,
            'logTabs' => $logTabs, 'activeTab' => $activeTab, 'languageList' => $languageList, 'countryList' => $countryList,
            'countryAttrIds' => $countryAttrIds, 'languageAttrIds' => $languageAttrIds, 'type' => $contactType, 'sysAttrTransIds' => $sysAttrTransIds,
            'nextPreviousResultset' => $nextPreviousResultset,
            'logTabsCount' => $logTabsCount, 'breadCrumb'=> array('breadcrumb_data' => array(),'back' => ($contactModuleType === 'archivedsponsor') ? $this->generateUrl('view_archived_sponsors') : $this->generateUrl('clubadmin_sponsor_homepage'))
        );
        $dataSet = array_merge($dataSetArray, $contCountDetails);
        $dataSet['federationId'] = ($this->clubType == 'federation') ? $this->clubId : $this->federationId;
        $dataSet['documentsCount'] = $this->em->getRepository('CommonUtilityBundle:FgDmAssigment')->getCountOfAssignedDocuments('CONTACT', $this->clubId, $contact);
        $groupUserDetails = $this->em->getRepository('CommonUtilityBundle:SfGuardGroup')->getGroupDetails($this->conn, $this->clubId, $contact);
        $dataSet['hasUserRights'] = (count($groupUserDetails) > 0) ? 1 : 0;
        $dataSet['servicesCount'] = $this->em->getRepository('CommonUtilityBundle:FgSmBookings')->getCountOfSponsorServices($this->clubId, $contact, $isArchiveSponsor);
        $dataSet['adsCount'] = $this->em->getRepository('CommonUtilityBundle:FgSmSponsorAds')->getCountOfSponsorAds($this->clubId, $contact);
        $contCountDetails['servicesCount'] =   $dataSet['servicesCount'];
        $contCountDetails['documentsCount'] = $dataSet['documentsCount'];
        $contCountDetails['adsCount'] = $dataSet['adsCount'];              
        $dataSet['tabs'] = FgUtility::getTabsArrayDetails($this->container, $accessObj->tabArray, $offset, $contact, $contCountDetails, "log", "sponsor");
        return $this->render('ClubadminSponsorBundle:Log:index.html.twig', $dataSet);
    }

    /**
     * Function for listing log entries
     *
     * @param int    $contactId contact id
     * @param string $type      contact type
     *
     * @return array
     */
    private function contactDetails($contactId, $type = 'contact') {
        $club = $this->get('club');
        $contactlistClass = new Contactlist($this->container, '', $club, $type);
        $contactlistClass->setColumns(array('contactname', 'is_company','fg_cm_contact.fed_contact_id','fg_cm_contact.subfed_contact_id'));
        $contactlistClass->setFrom('*');
        $contactlistClass->setCondition();
        $sWhere = "  fg_cm_contact.id=$contactId";
        $contactlistClass->addCondition($sWhere);
        $listquery = $contactlistClass->getResult();
        $fieldsArray = $this->conn->fetchAll($listquery);

        return $fieldsArray[0];
    }

    /**
     * Function for log translate data
     *
     * @return array
     */
    private function logTranslateFields() {
        $transArray = array(
            'data' => $this->get('translator')->trans('SM_LOG_CONTACT_FIELDS_TAB'), 'services' => $this->get('translator')->trans('SM_LOG_SERVICES_TAB'),
            'Formal' => $this->get('translator')->trans('CM_FORMAL'), 'Informal' => $this->get('translator')->trans('CM_INFORMAL'),
            'Male' => $this->get('translator')->trans('CM_MALE'), 'Female' => $this->get('translator')->trans('CM_FEMALE'), 'assigned' => $this->get('translator')->trans('SM_LOG_FLAG_ASSIGNED'),
            'deleted' => $this->get('translator')->trans('SM_LOG_FLAG_DELETED'), 'changed' => $this->get('translator')->trans('SM_LOG_FLAG_CHANGED'),
            'stopped' => $this->get('translator')->trans('SM_LOG_FLAG_STOPPED'),
            'added' => $this->get('translator')->trans('LOG_FLAG_ADDED'),
            'removed' => $this->get('translator')->trans('LOG_FLAG_REMOVED'), 'changed' => $this->get('translator')->trans('LOG_FLAG_CHANGED'),
            'male' => $this->get('translator')->trans('CM_MALE'), 'female' => $this->get('translator')->trans('CM_FEMALE')
        );
        return $transArray;
    }

    /**
     * Function for getting log entries from database
     *
     * @param int $contact contact id
     *
     * @return response
     */
    public function sponsorLogDataAction($contact) {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $fed_contact_id = $request->get('fed_contact_id');
        $subfed_contact_id = $request->get('subfed_contact_id');
        $club = $this->container->get('club');
        $clubDetails = array('clubId' => $this->clubId, 'clubType' => $this->clubType, 'clubHeirarchy' => $club->get('clubHeirarchy'), 'clubDefaultLang' => $this->clubDefaultLang);
        $contactPdo = new ContactPdo($this->container);
        $logEntriesDataTab = $contactPdo->getContactFieldLogEntries($clubDetails, $contact, $this->container,$fed_contact_id,$subfed_contact_id);        
        foreach ($logEntriesDataTab as $key => $dataFields) {
            $logEntriesDataTab[$key]['value_before'] = htmlentities($dataFields['value_before'], ENT_COMPAT, "UTF-8");
            $logEntriesDataTab[$key]['value_after'] = htmlentities($dataFields['value_after'], ENT_COMPAT, "UTF-8");
        }
        $servicesLogEntries = $this->em->getRepository('CommonUtilityBundle:FgSmSponsorLog')->getAllSponsorLog($contact, $this->clubId);
        $logEntries = array('data' => $logEntriesDataTab, 'services' => $servicesLogEntries, 'contact' => $contact);
        $output['aaData'] = $logEntries;

        return new JsonResponse($output);
    }

}
