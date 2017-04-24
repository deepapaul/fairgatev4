<?php

/**
 * This class is used for handling manual contacts of newsletter in Communication module.
 */
namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Util\FgSettings;

/**
 * FgCnNewsletterManualContactsRepository
 *
 * This class is used for handling manual contacts of newsletter in Communication module.
 */
class FgCnNewsletterManualContactsRepository extends EntityRepository
{
    /**
     * For collect the manuak selected contact details
     * @param int $newsletterId
     *
     * @return type
     */
 public function getManualySelectedContact($newsletterId)  {
        $dateFormat = FgSettings::getMysqlDateTimeFormat();
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT ms.contact_id as id, contactNameYOB(ms.contact_id) as title

                FROM  fg_cn_newsletter_manual_contacts ms WHERE ms.newsletter_id=:newsletterId";
        $result = $conn->fetchAll($sql, array(':newsletterId'=>$newsletterId));
        $conn->close();

        return $result;

 }

    /**
     * Function to add manually included contacts of a newsletter.
     *
     * @param int    $newsletterId  Newsletter id
     * @param array  $contacts      Contacts array
     * @param object $newsletterObj Newsletter object
     */
    public function addNewsletterManualContacts($newsletterId, $contacts, $newsletterObj = null) {
        $em = $this->getEntityManager();
        if (!$newsletterObj) {
            $newsletterObj = $em->getRepository('CommonUtilityBundle:FgCnNewsletter')->find($newsletterId);
        }
        foreach ($contacts as $contactId) {
            $contactObj = $em->getRepository('CommonUtilityBundle:FgCmContact')->find($contactId);
            $manualContObj = new \Common\UtilityBundle\Entity\FgCnNewsletterManualContacts();
            $manualContObj->setNewsletter($newsletterObj);
            $manualContObj->setContact($contactObj);
            $em->persist($manualContObj);
        }
        $em->flush();
    }

    /**
     * Function to remove manually included contacts of a newsletter.
     *
     * @param int   $newsletterId Newsletter id
     * @param array $contacts     Contacts array
     */
    public function removeNewsletterManualContacts($newsletterId, $contacts) {
        $em = $this->getEntityManager();
        foreach ($contacts as $contactId) {
            $manualContObj = $em->getRepository('CommonUtilityBundle:FgCnNewsletterManualContacts')->findOneBy(array('newsletter' => $newsletterId, 'contact' => $contactId));
            $em->remove($manualContObj);
        }
        $em->flush();
    }

}
