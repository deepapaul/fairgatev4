<?php

namespace Clubadmin\CommunicationBundle\Util;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class for handling newsletter spooling functionality
 *
 * @author pits
 */
class FgSendNewsletters
{
    /**
     * $container
     * @var object {container object}
     */
    public $container;

    /**
     * $club
     * @var Service {clubservice}
     */
    private $club;

    /**
     * $currContactId
     * @var int CurContactId
     */
    private $currContactId;

    /**
     * $newsletterArr
     * @var array Newsletter details Array
     */
    private $newsletterArr;

    /**
     * $newsletterId
     * @var int NewsletterId
     */
    private $newsletterId;

    /**
     * $clubId
     * @var int ClubId
     */
    private $clubId;

    /**
     * $conn
     * @var object {connection object}
     */
    private $conn;

    /**
     * $em
     * @var object {entitymanager object}
     */
    private $em;

    /**
     * $clubDefaultSystemLang
     * @var string Club default system language
     */
    private $clubDefaultSystemLang;
    
    /**
     * $masterTable
     * @var string Mastertable name 
     */
    private $masterTable;
    
    /**
     * $clubDefaultLang
     * @var string Club default language
     */
    private $clubDefaultLang;
    
    /**
     * Function to construct FgSendNewsletters Class
     * @param object $container     Container Object
     * @param array  $newsletterArr Newsletter Array
     */
    public function __construct($container, $newsletterArr)
    {
        $this->container = $container;
        $this->club = $this->container->get('club');
        $this->em = $this->container->get('doctrine')->getManager();
        $this->currContactId = 1; //superadmin
        $this->newsletterArr = $newsletterArr;
        $this->newsletterId = $newsletterArr['id'];
        $this->clubId = $this->club->get('id');
        $this->conn = $this->em->getConnection();
        $this->clubDefaultSystemLang = $this->club->get('default_system_lang');
        $this->clubDefaultLang = $this->club->get('default_lang');
        $this->masterTable = $this->club->get('clubTable');
    }

    /**
     * Function to update newsletter status and content
     */
    public function updateNewsletterStatusAndContent()
    {
        $newsletterType = $this->newsletterArr['newsletterType'];
        $templateId = $this->newsletterArr['templateId'];
        $this->container->set('request', new Request());
        if ($newsletterType == 'GENERAL') {
            $result = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletterContent')->getNewsletterContentDetails($this->container, $this->clubId, $this->newsletterId, $templateId, 'cron');
             $content = $this->container->get('templating')->render("ClubadminCommunicationBundle:Preview:newsletter-preview.html.twig", $result);
        } else {
            $result = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletterContent')->getSimplemailContentDetails($this->container, $this->clubId, $this->newsletterId, $this->currContactId, $this->club->get('title'), 'cron');
            $content = $this->container->get('templating')->render("ClubadminCommunicationBundle:Preview:simpleMail-preview.html.twig", $result);
        }
        $newsletterObj = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletter')->find($this->newsletterId);
        $newsletterObj->setStatus('sending');
        $newsletterObj->setIsCron(1);
        $newsletterObj->setNewsletterContent($content);
        $this->em->flush();
    }

    /**
     * Function to insert newsletter contacts to spool
     */
    public function insertNewsletterContactsToSpool()
    {
        $manualContactsQry = '';
        $nonMandatoryQry = '';
        $newsletterPublishType = $this->newsletterArr['publishType'];
        $recipientListId = $this->newsletterArr['recipientListId'];
        if ($newsletterPublishType == 'MANDATORY') {
            if ($recipientListId != '') {
                $this->updateRecipientList();
            }
            $manualContactsQry = $this->getManualContactsEmailSelectionQuery();
        } else {
            $nonMandatoryQry = $this->getNonMandatoryReceiverSelectionQuery();
        }

        $this->invokeStoredProcedure($manualContactsQry, $nonMandatoryQry);
    }

    /**
     * Function to get mandatory manual contacts email selection query
     *
     * @return text $manualContactsQry
     */
    private function getManualContactsEmailSelectionQuery()
    {
        $newsletterStatus = $this->newsletterArr['status'];
        $manualContactsQry = $this->em->getRepository('CommonUtilityBundle:FgCnRecepients')->getNewsletterRecipients('mandatory', $newsletterStatus, $this->newsletterId, $this->clubId, $this->container, $this->currContactId, true, false, array(), false, $this->clubDefaultSystemLang);

        return $manualContactsQry;
    }

    /**
     * Function to get non mandatory query(filter criteria query+ FFM query + manual contact email selection query)
     *
     * @return text $nonMandatoryQry
     */
    private function getNonMandatoryReceiverSelectionQuery()
    {
        $newsletterStatus = $this->newsletterArr['status'];
        $nonMandatoryQry = $this->em->getRepository('CommonUtilityBundle:FgCnRecepients')->getNewsletterRecipients('nonmandatory', $newsletterStatus, $this->newsletterId, $this->clubId, $this->container, $this->currContactId, true, false, array(), false, $this->clubDefaultSystemLang);

        return $nonMandatoryQry;
    }

    /**
     * Function to update recipient list
     */
    private function updateRecipientList()
    {
        $recipientListId = $this->newsletterArr['recipientListId'];
        $this->em->getRepository('CommonUtilityBundle:FgCnRecepients')->updateRecipientContacts($this->container, $this->currContactId, $recipientListId, $this->clubDefaultSystemLang);
    }

    /**
     * Function to invoke stored procedute to insert entries in spool and receiver log table
     * @param text $manualContactsQry Manual contacts query
     * @param text $nonMandatoryQry   Non mandatory query
     */
    private function invokeStoredProcedure($manualContactsQry, $nonMandatoryQry)
    {
      
        
        try {
            echo 'CALL insertNewsletterContactsToSpoolv4(' . $this->newsletterId . ',"' . $manualContactsQry . '","' . $nonMandatoryQry . '", "' . $this->clubDefaultLang . '","' . $this->clubDefaultSystemLang . '", "' . $this->masterTable . '")';
            $this->conn->executeQuery('CALL insertNewsletterContactsToSpoolv4(' . $this->newsletterId . ',"' . $manualContactsQry . '","' . $nonMandatoryQry . '", "' . $this->clubDefaultLang . '","' . $this->clubDefaultSystemLang . '", "' . $this->masterTable . '")');
        } catch (Exception $e) {
            echo "\nError :: " . $e . " NewsletterId ::" . $this->newsletter_id;
            $newsletterObj = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletter')->find($this->newsletterId);
            $newsletterObj->setIsCron(0);
            $this->em->flush();
        }
    }
}
