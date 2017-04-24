<?php

namespace Common\UtilityBundle\Repository\Gallery;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Repository\Pdo\GalleryPdo;

/**
 * fgGmItemI18nRepository.
 *
 * This class is used for handling item i18n table in gallery in internal area
 */
class FgGmItemI18nRepository extends EntityRepository
{
    /**
     * Function to update language description while editing the item description.
     *
     * @param int    $itemId album id
     * @param string $lang   Current system langyage
     * @param string $desc   Title
     *
     * @return bool
     */
    public function updateLangDesc($itemId, $lang, $desc)
    {
        $qb = $this->createQueryBuilder();
        $que = $qb->update('CommonUtilityBundle:FgGmItemI18n', 'ii18n')
                ->set('ii18n.descriptionLang', ':desc')
                ->where('ii18n.lang=:lang')
                ->andWhere('ii18n.id =:itemId')
                ->setParameter('lang', $lang)
                ->setParameter('itemId', $itemId)
                ->setParameter('desc', $desc)
                ->getQuery();
        $result = $que->execute();

        return true;
    }
    /**
     * Function to insert language description while inserting/editing the item description.
     *
     * @param int    $itemId album id
     * @param string $lang   Current system langyage
     * @param string $desc   Title
     *
     * @return bool
     */
    public function insertLangDesc($itemId, $lang, $desc, $container)
    {
        $desc = addslashes($desc);      
        
        $pdoClass = new GalleryPdo($container);        
        $pdoClass->updateItemDescriptioniI18n($desc, $itemId, $lang); 
        

        return true;
    }
}
