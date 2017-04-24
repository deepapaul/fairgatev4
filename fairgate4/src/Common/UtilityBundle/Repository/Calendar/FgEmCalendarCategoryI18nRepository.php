<?php

namespace Common\UtilityBundle\Repository\Calendar;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Entity\FgEmCalendarCategory;
use Common\UtilityBundle\Util\FgUtility;


class FgEmCalendarCategoryI18nRepository extends EntityRepository{
     /**
     * Function to get title details
     *
     * @param string $lang  Current system langyage
     * @param int    $catId Category id
     *
     * @return array
     */
    public function getupdateDetails($lang, $catId) {
   
        $query = $this->createQueryBuilder('ci18n')
                ->select('cat.id', 'ci18n.lang')
                ->leftJoin('ci18n.id', 'cat')
                ->where('ci18n.lang=:lang')
                ->andWhere('ci18n.id=:ci18nid')
                ->setParameter('lang', $lang)
                ->setParameter('ci18nid', $catId);
        $result = $query->getQuery()->getResult();

        return $result;
    }
    
        /**
     * Function to update language title
     *
     * @param int    $catId Category id
     * @param string $lang  Current system langyage
     * @param string $title Title
     *
     * @return boolean
     */
    public function updateSingleLang($catId, $lang, $title) {
        
        $qb = $this->createQueryBuilder();
        $que = $qb->update('CommonUtilityBundle:FgEmCalendarCategoryI18n', 'ci18n')
                ->set('ci18n.titleLang', ":title")
                ->where('ci18n.lang=:lang')
                ->andWhere('ci18n.id =:catId')
                ->setParameter('lang', $lang)
                ->setParameter('catId', $catId)
                ->setParameter('title', $title)
                ->getQuery();
        $result = $que->execute();

        return true;
    }
}
