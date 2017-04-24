<?php

namespace Common\UtilityBundle\Repository\Gallery;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Repository\Pdo\GalleryPdo;

/**
 * FgGmAlbumI18nRepository.
 *
 * This class is used for handling album i18n table in gallery in internal area
 */
class FgGmAlbumI18nRepository extends EntityRepository
{
    /**
     * Function to get update details.
     *
     * @param string $lang    Current system langyage
     * @param int    $albumId Category id
     *
     * @return array
     */
    public function getAlbumi18nUpdateDetails($lang, $albumId)
    {
        $query = $this->createQueryBuilder('ci18n')
                ->select('alb.id', 'ci18n.lang')
                ->leftJoin('ci18n.id', 'alb')
                ->where('ci18n.lang=:lang')
                ->andWhere('ci18n.id=:ci18nid')
                ->setParameter('lang', $lang)
                ->setParameter('ci18nid', $albumId);
        $result = $query->getQuery()->getResult();

        return $result;
    }

    /**
     * Function to update language title while editing the abum settings title.
     *
     * @param int    $albumId album id
     * @param string $lang    Current system langyage
     * @param string $title   Title
     *
     * @return bool
     */
    public function updateSingleLang($albumId, $lang, $title)
    {
        $qb = $this->createQueryBuilder();
        $que = $qb->update('CommonUtilityBundle:FgGmAlbumI18n', 'ai18n')
                ->set('ai18n.nameLang', ':title')
                ->where('ai18n.lang=:lang')
                ->andWhere('ai18n.id =:albumId')
                ->setParameter('lang', $lang)
                ->setParameter('albumId', $albumId)
                ->setParameter('title', $title)
                ->getQuery();
        $result = $que->execute();

        return true;
    }

    /**
     * Function to insert album language details.
     *
     * @param int    $albumId  album id
     * @param string $lang     Current system langyage
     * @param string $title    Title
     * @param string $isActive is active flag
     *
     * @return bool
     */
    public function insertAlbumLangDetails($albumId, $lang, $title, $isActive, $container)
    {
        
        $pdoClass = new GalleryPdo($container);
        $pdoClass->insertAlbumLangDetails($albumId, $lang, $title, $isActive); 

        return true;
    }
}
