<?php

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * FgClubTerminologyI18nRepository
 *
 */
class FgClubTerminologyI18nRepository extends EntityRepository
{

    /**
     * Function to get Terminologyi18n update Details
     * @param type $lang    language
     * @param type $ci18nId id
     *
     * @return type
     */
    public function getTerminologyi18nupdateDetails($lang, $ci18nId)
    {

        $query = $this->createQueryBuilder('ci18n')
            ->select('term.id', 'ci18n.lang', 'ci18n.singularLang', 'ci18n.pluralLang')
            ->leftJoin('ci18n.id', 'term')
            ->where('ci18n.lang=:lang')
            ->andWhere('ci18n.id=:ci18nid')
            ->setParameter('lang', $lang)
            ->setParameter('ci18nid', $ci18nId);
        $result = $query->getQuery()->getResult();

        return $result;
    }
}
