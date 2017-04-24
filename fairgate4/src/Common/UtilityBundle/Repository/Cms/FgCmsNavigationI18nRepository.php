<?php
/**
 * FgCmsNavigationI18nRepository.
 * 
 * @package 	WebsiteCMSBundle
 * @subpackage 	Repository
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 * 
 */
namespace Common\UtilityBundle\Repository\Cms;

use Doctrine\ORM\EntityRepository;

/**
 * FgCmsNavigationI18nRepository
 *
 * This class is used for handling data update in FgCmsNavigationI18n table.
 */
class FgCmsNavigationI18nRepository extends EntityRepository
{

    /**
     * This function is used to update title for an existing I18n entry
     * 
     * @param int    $id    The navigation id
     * @param string $lang  Language short code
     * @param string $title The title
     */
    public function updateNavigationI18n($id, $lang, $title)
    {
        $qb = $this->createQueryBuilder();
        $query = $qb->update('CommonUtilityBundle:FgCmsNavigationI18n', 'ni18n')
            ->set('ni18n.titleLang', ":title")
            ->where('ni18n.lang=:lang')
            ->andWhere('ni18n.id =:id')
            ->setParameter('lang', $lang)
            ->setParameter('id', $id)
            ->setParameter('title', $title)
            ->getQuery();
        $query->execute();
    }
}
