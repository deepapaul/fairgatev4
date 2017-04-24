<?php

/**
 * FgCmsContactTableColumnsI18nRepository.
 *
 * @package 	WebsiteCMSBundle
 * @subpackage 	Repository
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 *
 */
namespace Common\UtilityBundle\Repository\Cms;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Entity\FgCmsContactTableColumnsI18n;

/**
 * FgCmsContactTableColumnsI18nRepository
 *
 */
class FgCmsContactTableColumnsI18nRepository extends EntityRepository
{

    /**
     * This function is used to insert or update the table columns i18n
     * 
     * @param int   $tableColumnId The table column id
     * @param array $titleArr      The array of titles in each language
     * @param array $clubLanguages The array of club languages
     */
    public function insertOrUpdateTableColumnsI18n($tableColumnId, $titleArr, $clubLanguages)
    {
        $tableColumnObj = $this->_em->getReference('CommonUtilityBundle:FgCmsContactTableColumns', $tableColumnId);
        foreach ($clubLanguages as $language) {
            if (isset($titleArr[$language])) {
                $title = str_replace('<script', '<scri&nbsp;pt', $titleArr[$language]);
                $tableColumnI18nObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsContactTableColumnsI18n')->findOneBy(array('lang' => $language, 'id' => $tableColumnId));
                if (empty($tableColumnI18nObj)) {
                    $tableColumnI18nObj = new FgCmsContactTableColumnsI18n();
                    $tableColumnI18nObj->setId($tableColumnObj);
                    $tableColumnI18nObj->setLang($language);
                    $tableColumnI18nObj->setTitleLang($title);
                    $this->_em->persist($tableColumnI18nObj);
                } else {
                    $this->updateTableColumnsI18n($tableColumnId, $language, $title);
                }
            }
        }
        $this->_em->flush();
    }

    /**
     * This function is used to update i18n entries corresponding to a stage 3 portrait element
     * 
     * @param int    $tableColumnId The tablecolumn id
     * @param string $language      The language to be updated
     * @param array  $title         The title values in corresp. language
     */
    public function updateTableColumnsI18n($tableColumnId, $language, $title)
    {
        $qb = $this->createQueryBuilder()
            ->update('CommonUtilityBundle:FgCmsContactTableColumnsI18n', 'fi18n')
            ->set('fi18n.titleLang', ":title")
            ->where('fi18n.lang=:lang')
            ->andWhere('fi18n.id =:id')
            ->setParameters(array('id' => $tableColumnId, 'lang' => $language, 'title' => $title))
            ->getQuery();
        $qb->execute();
    }
}
