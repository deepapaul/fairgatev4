<?php

/**
 * LogController
 *
 * This controller was created for listing logentries
 *
 * @package    ClubadminContactBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
namespace Clubadmin\CommunicationBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Clubadmin\Util\Contactlist;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Intl\Intl;

/**
 * Manage subscriber log activities
 */
class SubscriberlogController extends FgController
{

    /**
     * Function for listing log entries of subscriber
     * @param int $offset     Offset
     * @param int $subscriber Subscriber id
     *
     * @return template
     */
    public function indexAction(Request $request, $offset, $subscriber)
    {
        $checkSubscriber = $this->em->getRepository('CommonUtilityBundle:FgCnSubscriberLog')->checkSubscriberInClub($subscriber, $this->clubId);
        $isAccess = count($checkSubscriber) > 0 ? 1 : 0;
        $accessCheckArray = array('from' => 'newsletter', 'isAccess' => $isAccess);
        $this->fgpermission->checkAreaAccess($accessCheckArray);

        $tabType = $request->get('tab', '');
        $logTabs = array(1 => 'data', 2 => 'communication');
        $transKindFields = array('data' => 'SUBSCRIBER_TAB_DATA', 'communication' => 'SUBSCRIBER_TAB_COMMUNICATION', 'Formal' => 'CM_FORMAL', 'Informal' => 'CM_INFORMAL', 'Male' => 'CM_MALE', 'Female' => 'CM_FEMALE', 'added' => 'LOG_FLAG_ADDED', 'removed' => 'LOG_FLAG_REMOVED', 'changed' => 'LOG_FLAG_CHANGED', 'male' => 'CM_MALE', 'female' => 'CM_FEMALE', 'company' => 'SUBSCRIBER_IMPORT_COMPANY', 'salutation' => 'SUBSCRIBER_IMPORT_SALUTATION', 'gender' => 'SUBSCRIBER_IMPORT_GENDER', 'first_name' => 'SUBSCRIBER_IMPORT_FORENAME', 'last_name' => 'SUBSCRIBER_IMPORT_SURNAME', 'email' => 'SUBSCRIBER_IMPORT_EMAIL','correspondance_lang'=> 'CL_CORRESPOND_LANG');
        $transKindFields = array_merge($transKindFields, $this->getLanguageArray());
        $storedprocedure = $this->conn->prepare("SELECT subscriberName($subscriber, 1) as subscriberName");
        $storedprocedure->execute();
        $results = $storedprocedure->fetchAll();
        $subscriberName = $results[0]['subscriberName'];
        $logEntriesDataTab = $this->em->getRepository('CommonUtilityBundle:FgCnSubscriberLog')->getSubscriberDataLogEntries($subscriber, $this->clubId);


        $logEntriesCommunicationTab = $this->em->getRepository('CommonUtilityBundle:FgCnSubscriberLog')->getSubscriberCommunicationLogEntries($subscriber, $this->clubId);
        $logEntries = array('data' => $logEntriesDataTab, 'communication' => $logEntriesCommunicationTab);
        if ($tabType == "menu") {
            $activeTab = '1';
        } else {
            $activeTab = '2';
        }
        $dataSet = array('logEntries' => $logEntries, 'subscriberId' => $subscriber, 'transKindFields' => $transKindFields, 'offset' => $offset, 'logTabs' => $logTabs, 'activeTab' => $activeTab, 'subscriberName' => $subscriberName);

        return $this->render('ClubadminCommunicationBundle:Subscriberlog:index.html.twig', $dataSet);
    }

    /**
     * Function for listing log entries of subscriber
     * @param int $offset  Offset
     * @param int $contact Subscriber id
     *
     * @return template
     */
    public function owncontactlogAction($offset, $contact)
    {
        $contactType = 'contact';
        $contactData = $this->contactDetails($contact, $contactType);
        $contactName = $contactData['contactName'];
        $logTabs = array(1 => 'communication');
        $transKindFields = array('data' => 'SUBSCRIBER_TAB_DATA', 'communication' => 'SUBSCRIBER_TAB_COMMUNICATION', 'Formal' => 'CM_FORMAL', 'Informal' => 'CM_INFORMAL', 'Male' => 'CM_MALE', 'Female' => 'CM_FEMALE', 'added' => 'LOG_FLAG_ADDED', 'removed' => 'LOG_FLAG_REMOVED', 'changed' => 'LOG_FLAG_CHANGED', 'male' => 'CM_MALE', 'female' => 'CM_FEMALE', 'company' => 'SUBSCRIBER_IMPORT_COMPANY', 'salutation' => 'SUBSCRIBER_IMPORT_SALUTATION', 'gender' => 'SUBSCRIBER_IMPORT_GENDER', 'first_name' => 'SUBSCRIBER_IMPORT_FORENAME', 'last_name' => 'SUBSCRIBER_IMPORT_SURNAME', 'email' => 'SUBSCRIBER_IMPORT_EMAIL');
        $logEntriesCommunicationTab = $this->em->getRepository('CommonUtilityBundle:FgCmChangeLog')->getOwncontactCommunicationLogEntries($contact, $this->clubId);
        $logEntries = array('communication' => $logEntriesCommunicationTab);
        $activeTab = '1';
        $dataSet = array('logEntries' => $logEntries, 'contactId' => $contact, 'offset' => $offset, 'transKindFields' => $transKindFields, 'contactName' => $contactName, 'logTabs' => $logTabs, 'activeTab' => $activeTab);

        return $this->render('ClubadminCommunicationBundle:Subscriberlog:ownindex.html.twig', $dataSet);
    }

    /**
     * Function to get contact name
     * @param int $contactId Contact Id
     * @param string $type  (contact/archive/formarfederation)
     *
     * @return array
     */
    private function contactDetails($contactId, $type = 'contact')
    {
        $club = $this->get('club');
        $contactlistClass = new Contactlist($this->container, '', $club, $type);
        $contactlistClass->setColumns(array('contactname', 'contactName', 'is_company'));
        $contactlistClass->setFrom('*');
        $contactlistClass->setCondition();
        $sWhere = " fg_cm_contact.id=$contactId";
        $contactlistClass->addCondition($sWhere);
        $listquery = $contactlistClass->getResult();
        $fieldsArray = $this->conn->fetchAll($listquery);

        return $fieldsArray[0];
    }

    /**
     * function to get language array.
     *
     * @return type
     */
    private function getLanguageArray()
    {
        $languages = Intl::getLanguageBundle()->getLanguageNames();
        $fieldLanguages = array();
        foreach ($this->clubLanguages as $shortName) {
            $fieldLanguages[$shortName] = $languages[$shortName];
        }

        return $fieldLanguages;
    }
}
