<?php

/**
 * FgCmsArticleCategoryI18nRepository to handle functionalities related to fg_cms_article and other article tables.
 */
namespace Common\UtilityBundle\Repository\Article;

use Doctrine\ORM\EntityRepository;

/**
 * FgCmsArticleCategoryI18nRepository to handle fg_cms_article and related tables.
 *
 * @package    InternalArticleBundle
 * @subpackage Repository
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
class FgCmsArticleCategoryI18nRepository extends EntityRepository
{

    /**
     * Function to get title details.
     *
     * @param string $lang  Current system langyage
     * @param int    $catId Category id
     *
     * @return array title & id of corresponding language
     */
    public function getUpdateDetails($lang, $catId)
    {
        $query = $this->createQueryBuilder('ci18n')
            ->select('cat.id', 'ci18n.lang')
            ->leftJoin('ci18n.id', 'cat')
            ->where('ci18n.lang=:lang')
            ->andWhere('ci18n.id=:ci18nid')
            ->setParameter('lang', $lang)
            ->setParameter('ci18nid', $catId);

        return $query->getQuery()->getResult();
    }

    /**
     * Function to update language title.
     *
     * @param int    $catId Category id
     * @param string $lang  Current system langyage
     * @param string $title Title
     *
     * @return boolean true or false
     */
    public function updateSingleLang($catId, $lang, $title)
    {
        $qb = $this->createQueryBuilder();
        $que = $qb->update('CommonUtilityBundle:FgCmsArticleCategoryI18n', 'ci18n')
            ->set('ci18n.titleLang', ':title')
            ->where('ci18n.lang=:lang')
            ->andWhere('ci18n.id =:catId')
            ->setParameter('lang', $lang)
            ->setParameter('catId', $catId)
            ->setParameter('title', $title)
            ->getQuery();
        $que->execute();

        return true;
    }
}
