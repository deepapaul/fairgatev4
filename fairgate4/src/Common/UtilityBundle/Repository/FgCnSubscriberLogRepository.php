<?php

/**
 * FgCnSubscriberLogRepository
 *
 * This class is used for subscriber log in communication area.
 */

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Util\FgSettings;

/**
 * FgCmChangeLogRepository
 *
 * This class is used for listing and inserting role log in role management.
 */
class FgCnSubscriberLogRepository extends EntityRepository
{
    /**
     * Function to get the subscriber log entries of a subscriber
     *
     * @param int $subscriber Subscriber id
     * @param int $clubId     Club id
     *
     * @return array $result Array of log entries
     */
    public function getSubscriberDataLogEntries($subscriber, $clubId)
    {
        $dateFormat1 = FgSettings::getMysqlDateFormat();
        $dateFormat2 = FgSettings::getMysqlDateTimeFormat();

        $conn = $this->getEntityManager()->getConnection();
        $sql =  "SELECT sl.id,sl.subscriber_id,sl.date,sl.kind,sl.field,sl.date AS dateOriginal,date_format(sl.date,'". $dateFormat2 ."') AS date,sl.value_before,sl.value_after,sl.changed_by as changedBy,contactName(sl.changed_by) as editedBy,
                 (CASE WHEN ((sl.value_before IS NOT NULL AND sl.value_before != '') AND (sl.value_after IS NOT NULL AND sl.value_after != '')) THEN 'changed'
                       WHEN ((sl.value_before IS NOT NULL AND sl.value_before != '') AND (sl.value_after IS NULL OR sl.value_after = '')) THEN 'removed'
                       WHEN ((sl.value_before IS NULL OR sl.value_before = '') AND (sl.value_after IS NOT NULL AND sl.value_after != '')) THEN 'added'
                       ELSE 'none'
                 END) AS status
                 FROM fg_cn_subscriber_log sl LEFT JOIN fg_cn_subscriber s ON sl.subscriber_id = s.id
                 WHERE sl.subscriber_id= :subscriberId AND sl.kind='data' AND sl.club_id=:clubId";
        $result = $conn->fetchAll($sql, array('subscriberId' => $subscriber, 'clubId' => $clubId));

        return $result;
    }

    /**
     * Function to get the subscriber log entries of communication
     *
     * @param int $subscriber Subscriber id
     * @param int $clubId     Club id
     *
     * @return array $result Array of log entries
     */
    public function getSubscriberCommunicationLogEntries($subscriber, $clubId)
    {
        $dateFormat1 = FgSettings::getMysqlDateFormat();
        $dateFormat2 = FgSettings::getMysqlDateTimeFormat();

        $conn = $this->getEntityManager()->getConnection();
        $sql =  "SELECT sl.id,sl.subscriber_id,sl.date,sl.kind,sl.field,sl.date AS dateOriginal,date_format(sl.date,'". $dateFormat2 ."') AS date,sl.value_before,sl.value_after,sl.changed_by as changedBy,contactName(sl.changed_by) as editedBy,cn.subject as type
                 FROM fg_cn_subscriber_log sl
                 LEFT JOIN fg_cn_subscriber s ON sl.subscriber_id = s.id
                 LEFT JOIN fg_cn_newsletter cn ON sl.newsletter_id=cn.id
                 WHERE sl.subscriber_id= :subscriberId AND sl.kind='communication' AND sl.club_id=:clubId";
        $result = $conn->fetchAll($sql, array('subscriberId' => $subscriber, 'clubId' => $clubId));

        return $result;
    }
    /**
     * Function to check subscriber id in a club 
     * @param int $subscriber Subscriber id
     * @param int $clubId     Club id
     * 
     * @return array $result Array subscriber log ids
     */
    public function checkSubscriberInClub($subscriber, $clubId){
        $conn = $this->getEntityManager()->getConnection();
        $sql ="SELECT sl.id FROM fg_cn_subscriber_log sl WHERE subscriber_id=$subscriber AND club_id=$clubId";
        $result = $conn->fetchAll($sql);
        return $result;
        
    }
    
}
