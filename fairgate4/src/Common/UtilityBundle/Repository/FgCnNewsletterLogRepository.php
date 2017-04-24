<?php

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * This repository is used for functions in Dashboard
 */
class FgCnNewsletterLogRepository extends EntityRepository {

    /**
     * Function to get the array result of recipients and openings of newsletters in flot charts
     *
     * @param type $clubId Club id
     *
     * @return Array
     */
    public function getNewsletterRecipientsAndOpenings($clubId) {
        $resultQuery = $this->createQueryBuilder('N')
                ->select('L.id, N.recepients as recepients, N.date as sentdate, count(L.openedAt) as openings, SUM(L.isBounced) as bounces ')
                ->innerJoin("CommonUtilityBundle:FgCnNewsletterReceiverLog", "L", "WITH", "L.newsletter = N.newsletter AND L.club = $clubId AND ( N.newsletterType = 'MANDATORY' OR N.newsletterType = 'SUBSCRIPTION' ) ")
                ->groupBy('N.newsletter')
                ->orderBy('N.date', 'DESC')
                ->setFirstResult(0)
                ->setMaxResults(12);
        $results = $resultQuery->getQuery()->getArrayResult();

        return $results;
    }

    /**
     * Function to get the array result of recipients and openings of simplemails in flot charts
     *
     * @param type $clubId Club id
     *
     * @return type
     */
    public function getSimplemailRecipientsAndOpenings($clubId) {
        $resultQuery = $this->createQueryBuilder('N')
                ->select('L.id, N.recepients as recepients, N.date as sentdate, count(L.openedAt) as openings, SUM(L.isBounced) as bounces ')
                ->innerJoin("CommonUtilityBundle:FgCnNewsletterReceiverLog", "L", "WITH", "L.newsletter = N.newsletter AND L.club = $clubId AND ( N.newsletterType = 'SIMPLE EMAIL' ) ")
                ->groupBy('N.newsletter')
                ->orderBy('N.date', 'DESC')
                ->setFirstResult(0)
                ->setMaxResults(12);
        $results = $resultQuery->getQuery()->getArrayResult();

        return $results;
    }
}
