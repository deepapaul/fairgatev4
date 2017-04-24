<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * This repository is used for handling newsletter functionality
 *
 *
 */
class FgCnNewsletterPublishLangRepository extends EntityRepository {

    /**
     * Get newsletter language data
     *
     * @param Integer $newsletterId Newsletter id
     *
     * @return array
     */
    public function getNewsletterLanguageData($newsletterId) {
        $langQuery = $this->createQueryBuilder('nlang')
                ->select('nlang.languageCode')
                ->leftJoin('nlang.newsletter', 'nl')
                ->where('nl.id=:newsletterId')
                ->setParameter('newsletterId', $newsletterId);

        $langresult = $langQuery->getQuery()->getResult();
        foreach ($langresult as $key => $langData) {
            $finalArray[] = $langData['languageCode'];
        }

        return $finalArray;
    }
}
