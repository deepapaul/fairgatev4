<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
 namespace Common\UtilityBundle\Repository\Message;

use Doctrine\ORM\EntityRepository;
/**
 * This repository is used for handling newsletter content manipulation
 *
 *
 */
class FgMailMessageRepository extends EntityRepository
{
    /**
     * 
     * @param int $cronInstance 1/2/3/4
     * @param int $order        Priority ASC/DESC
     * @param int $messageLimit MessageLimit in each cron
     * 
     * @return array $result MessgaesArr
     */
    public function getSpooledMessages($cronInstance = 1, $order = 0, $messageLimit = '500')
    {
        $priority = ($order == 1) ? 'ASC' : 'DESC';
        $resultQuery = $this->createQueryBuilder('m')
                ->select('m.id,IDENTITY(m.newsletter) AS newsletterId,IDENTITY(m.receiverLog) AS receiverLogId, m.email, n.corresLang, n.contactId AS contactIds, IDENTITY(n.subscriber) AS subscriberId, n.salutation AS salutation, n.systemLanguage AS systemLanguage, n.sendDate AS sendDate')
                ->leftJoin('CommonUtilityBundle:FgCnNewsletterReceiverLog', 'n', 'WITH', 'm.receiverLog = n.id')
                ->where('m.cronInstance=:cronInstance')
                ->setMaxResults($messageLimit)
                ->addOrderBy('m.priority', $priority)
                ->addOrderBy('m.id', 'ASC')
                ->setParameters(array('cronInstance' => $cronInstance));
        $result = $resultQuery->getQuery()->getArrayResult();

        return $result;
    }
}
