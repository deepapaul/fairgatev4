<?php

/**
 * 
 * @package 	CommunicationBundle
 * @subpackage 	Util
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 * 
 */

namespace Clubadmin\CommunicationBundle\Util;

/**
 * FgSentNewsletterRecipients
 * 
 * This class is used for getting the recipient details of sent newsletters
 */
class FgSentNewsletterRecipients
{
    /**
     * Entity Manager object.
     *
     * @var object
     */
    private $em;
    
    /**
     * Container object.
     *
     * @var object
     */
    private $container;
    
    /**
     * This function is used to initailize tha class
     * 
     * @param object $container Container object
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->em = $this->container->get('doctrine')->getManager();
    }

    /**
     * This function is used to get the recipients list
     * 
     * @param int    $newsletterId Newsletter id
     * @param string $orderBy      Order by
     * @param string $limit        Limit
     * 
     * @return array $resultArr The result set
     */
    public function getRecipientsList($newsletterId, $orderBy = 'is_bounced DESC', $limit = '0,20')
    {
        $contactIdListQuery = "SELECT GROUP_CONCAT(r.contact_id) as contactIds FROM (SELECT rl.contact_id FROM fg_cn_newsletter_receiver_log rl WHERE rl.newsletter_id = :newsletterId AND rl.contact_id != '' ORDER BY $orderBy LIMIT $limit) r";
        $stmt = $this->em->getConnection()->prepare($contactIdListQuery);
        $stmt->execute(array('newsletterId' => $newsletterId));
        $contactIdsList = $stmt->fetchAll();
        $contactIds = $contactIdsList[0]['contactIds'];

        $receiverListQuery = "SELECT 
                    rl.id AS logId,
                    rl.contact_id AS id, 
                    rl.contact_id AS contactId, 
                    (IF((rl.resent_email IS NULL OR rl.resent_email='' AND rl.is_email_changed=0), (rl.email), (rl.resent_email))) AS email, 
                    (IF(((rl.subscriber_id IS NULL) OR (rl.subscriber_id = '')), (IF((rl.linked_contact_ids IS NULL OR rl.linked_contact_ids = ''), ((SELECT GROUP_CONCAT(b.`fieldname_short_lang` SEPARATOR ', ') FROM `fg_cm_attribute` a LEFT JOIN `fg_cm_attribute_i18n` b ON (b.id=a.id AND (IF((a.`is_system_field` = 1 OR a.`is_fairgate_field` = 1), (b.`lang`='en'), (b.`lang`='en')))) WHERE FIND_IN_SET(a.id, rl.email_field_ids))), (CONCAT((SELECT GROUP_CONCAT(b.`fieldname_short_lang` SEPARATOR ', ') FROM `fg_cm_attribute` a LEFT JOIN `fg_cm_attribute_i18n` b ON (b.id=a.id AND (IF((a.`is_system_field` = 1 OR a.`is_fairgate_field` = 1), (b.`lang`='en'), (b.`lang`='en')))) WHERE FIND_IN_SET(a.id, rl.email_field_ids)), ' (', (SELECT GROUP_CONCAT(contactName(c.`id`) SEPARATOR ', ') FROM `fg_cm_contact` c WHERE FIND_IN_SET(c.`id`, rl.linked_contact_ids)), ')')))), ('E-Mail'))) AS emailField, 
                    rl.email_field_ids, 
                    rl.linked_contact_ids, 
                    (IF(((rl.subscriber_id IS NULL) OR (rl.subscriber_id = '')), '', (subscriberName(rl.subscriber_id, 0)))) AS subscriberName, 
                    rl.salutation AS salutation, 
                    (IF(((rl.subscriber_id IS NULL) OR (rl.subscriber_id = '')), 'contact', 'subscriber')) AS contactType, 
                    (IF(((rl.subscriber_id IS NULL) OR (rl.subscriber_id = '')), '', rl.subscriber_id)) AS subscriberId, 
                    DATE_FORMAT(rl.opened_at,'%Y/%m/%d %H:%i')AS opened, 
                    rl.is_bounced AS isBounce, 
                    rl.bounce_message AS bounceMessage, 
                    rl.is_email_changed AS isEmailChanged, 
                    rl.corres_lang AS lang, 
                    '' AS groupingField 
                    FROM `fg_cn_newsletter_receiver_log` rl 
                    WHERE rl.newsletter_id=:newsletterId 
                    ORDER BY $orderBy LIMIT $limit";
        $stmt = $this->em->getConnection()->prepare($receiverListQuery);
        $stmt->execute(array('newsletterId' => $newsletterId));
        $resultArr[] = $stmt->fetchAll();
        
        if ($contactIds != '') {
            $clubDetailsQuery = "SELECT 
                        GROUP_CONCAT(ct.id) as contactIds, 
                        ct.fed_contact_id, 
                        GROUP_CONCAT(
                            CASE 
                                WHEN ((club.club_type = 'sub_federation_club' OR club.club_type = 'federation_club') AND club.id = ct.main_club_id) THEN CONCAT(club.id,'#mainclub#')  
                                WHEN (club.club_type = 'sub_federation_club' OR club.club_type = 'federation_club') THEN club.id 
                            END
                        ) as clubTitle, 
                        GROUP_CONCAT(CASE WHEN (club.is_sub_federation = 1) THEN club.id END) as subFedTitle 
                        FROM fg_cm_contact ct INNER JOIN fg_club club ON club.id = ct.club_id LEFT JOIN fg_club_i18n clubi18n ON (clubi18n.id = club.id AND clubi18n.lang = '" . $this->container->get('club')->get('default_lang') . "') 
                        WHERE ct.fed_contact_id IN (SELECT DISTINCT c.fed_contact_id FROM fg_cm_contact c WHERE c.id IN (" . $contactIds . "))
                        GROUP BY ct.fed_contact_id";
            $stmt = $this->em->getConnection()->prepare($clubDetailsQuery);
            $stmt->execute();
            $resultArr[] = $stmt->fetchAll();
        }
        
        if ($contactIds != '') {
            $contactDetailsQuery = "SELECT c.id AS contactId, contactName(c.`id`) AS contactName 
                        FROM fg_cm_contact c 
                        WHERE c.id IN (" . $contactIds . ")";
            $stmt = $this->em->getConnection()->prepare($contactDetailsQuery);
            $stmt->execute();
            $resultArr[] = $stmt->fetchAll();
        }
        
        $resultArr = ($contactIds != '') ? $this->getMappedRecipientData($resultArr) : $resultArr[0];

        return $resultArr;
    }
    
    /**
     * This function is used to get the mapped final recipeints data 
     * 
     * @param array $resultArr The input result
     * 
     * @return array $recipientListData The output result
     */
    private function getMappedRecipientData($resultArr)
    {
        $recipientData = $resultArr[0];
        $clubData = $resultArr[1];
        $contactData = $resultArr[2];
        $recipientListData = array();
        foreach ($recipientData as $recipient)
        {
            $contactIds = explode(',', $recipient['contactId']);
            $contactClub = array();
            $contactSubFederation = array();
            $contactNames = array();
            foreach ($contactIds as $contactId) {
                $clubContactIds = array_column($clubData, 'contactIds');
                foreach ($clubContactIds as $key => $clubContactId) {
                    if (in_array($contactId, explode(',', $clubContactId))) {
                        if ($clubData[$key]['clubTitle'] != '' && $clubData[$key]['clubTitle'] != null) {
                            $contactClub[] = $clubData[$key]['clubTitle'];
                        }
                        if ($clubData[$key]['subFedTitle'] != '' && $clubData[$key]['subFedTitle'] != null) {
                            $contactSubFederation[] = $clubData[$key]['subFedTitle'];
                        }
                    }
                }
                
                $key = array_search($contactId, array_column($contactData, 'contactId'));
                if ($contactData[$key]['contactName'] != '' && $contactData[$key]['contactName'] != null) {
                    $contactNames[] = $contactData[$key]['contactName'];
                }
            }
            $recipient['contactClub'] = implode('<br>', $contactClub);
            $recipient['contactSubFederation'] = implode('<br>', $contactSubFederation);
            $recipient['contactNames'] = implode('<br>', $contactNames);
            
            $recipientListData[] = $recipient;
        }

        return $recipientListData;
    }
}
