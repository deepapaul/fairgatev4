<?php

/*
 *  This class is used for handling newsletter and simple email recipients log in Communication module.
 */
 namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * This repository is used for handling newsletter functionality
 *
 *
 */
class FgCnNewsletterReceiverLogRepository extends EntityRepository
{
    /**
     * function to edit the bounced email
     *
     * @param string $emailNewVal the new edited email
     * @param int    $logId       the newsletter log id
     */
    public function updateBouncedEmail($emailNewVal, $logId)
    {
        $qb = $this->createQueryBuilder();
        $q = $qb->update('CommonUtilityBundle:FgCnNewsletterReceiverLog', 'n')
                ->set('n.resentEmail', ':emailNewVal')
                ->set('n.isEmailChanged', 1)
                ->where('n.id =:logId')
                ->setParameter('logId', $logId)
                ->setParameter('emailNewVal', $emailNewVal)
                ->getQuery();

        $p = $q->execute();
    }

    /**
     * function to set the bounced emails
     *
     * @param string $newsletterId the primary id
     * @param string $bouncedMessage the mail body of bounced message
     */
    public function updateBouncedEmailStatus($newsletterId, $bouncedMessage)
    {
        $em = $this->getEntityManager();
        $newsletter = $em->getRepository('CommonUtilityBundle:FgCnNewsletterReceiverLog')->find($newsletterId);
        if (!$newsletter) {
            //NOT FOUND
        } else {
            $bounceCount = $newsletter->getBounceCount();
            $newsletter->setBounceMessage($bouncedMessage);
            $newsletter->setIsBounced(1);
            $newsletter->setBounceCount($bounceCount + 1);
            $em->flush();
        }
    }
    /**
     * function to get the bounce messages
     *
     * @param Integer $logId Receiver log id
     *
     * @return array
     */
    public function getBounceMessages($logId)
    {
        $resultQuery = $this->createQueryBuilder('l')
                ->select('l.bounceMessage as bounceMessage, l.contactId as contactId, l.linkedContactIds')
                ->where('l.id=:logId')
                ->setParameter('logId', $logId);
        $result = $resultQuery->getQuery()->getResult();

        return $result;

    }
    /**
     * Function to insert resent bounce mails to spool
     *
     * @return null
     */
    public function resentBounceMails()
    {
        $cronInstanceSql = "SELECT 1 INTO @cronInstance;";
        $this->getEntityManager()->getConnection()->executeQuery($cronInstanceSql);
        $insertMailsSql = "INSERT INTO `fg_mail_message` (`newsletter_id`, `receiver_log_id`, `email`, `cron_instance`, `priority`) SELECT rl.newsletter_id, rl.id, IF(rl.resent_email<>'', rl.resent_email, rl.email), MOD((@cronInstance:=@cronInstance+1), 4)+1, '3' FROM fg_cn_newsletter n INNER JOIN fg_cn_newsletter_receiver_log rl ON n.id = rl.newsletter_id WHERE n.resent_status=1 AND rl.is_bounced=1 AND ((rl.is_email_changed = 1 AND rl.resent_email<>'') OR rl.is_email_changed = 0)";
        $this->getEntityManager()->getConnection()->executeQuery($insertMailsSql);
        $updateLogSql = "UPDATE fg_cn_newsletter SET resent_status = 2 WHERE resent_status=1 AND id IN(SELECT m.newsletter_id FROM fg_mail_message m INNER JOIN fg_cn_newsletter_receiver_log rl ON m.receiver_log_id = rl.id WHERE rl.is_bounced=1)";
        $this->getEntityManager()->getConnection()->executeQuery($updateLogSql);
    }
    
    /**
     * This function is used to get the newsletter receivers count
     * 
     * @param int $newsletterId Newsletter id
     * 
     * @return int $count Count
     */
    public function getNewsletterReceiversCount($newsletterId)
    {
        $qry = $this->createQueryBuilder('rl')
                ->select('count(rl.id) as receiverCount')
                ->where('rl.newsletter=:newsletterId')
                ->setParameter('newsletterId', $newsletterId)
                ->getQuery()
                ->getArrayResult();
        $count = ($qry[0]['receiverCount']) ? $qry[0]['receiverCount'] : 0;
        
        return $count;
    }
}
