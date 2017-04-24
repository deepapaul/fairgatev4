<?php

/**
 * FgCmsArticleTextI18nRepository for managing the fg_cms_article table.
 */
namespace Common\UtilityBundle\Repository\Article;

use Doctrine\ORM\EntityRepository;

/**
 * FgCmsArticleTextI18nRepository for managing the fg_cms_article table.
 *
 * @package    InternalArticleBundle
 * @subpackage Repository
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
class FgCmsArticleTextI18nRepository extends EntityRepository
{

    /**
     * Function to create the text entry and text18n for article. This function will also update the text-version of the article.
     *
     * @param array  $textArray     The array with the details to be saved
     * @param int    $textVersionId The current text version
     * @param array  $clubLanguages The array of languages available to the user
     *
     * @return void
     */
    public function saveArticleTexti18n($textArray, $textVersionId, $clubLanguages)
    {
        foreach ($clubLanguages as $language) {
            //No need to insert if all values is empty
            if (count(array_filter($textArray[$language]))) {
                $articleTexti18nObj = new \Common\UtilityBundle\Entity\FgCmsArticleTextI18n();
                $textVersionIdObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsArticleText')->find($textVersionId);

                $articleTexti18nObj->setId($textVersionIdObj);
                (isset($textArray[$language]['title'])) ? $articleTexti18nObj->setTitleLang(str_replace('<script', '<scri&nbsp;pt', $textArray[$language]['title'])) : $articleTexti18nObj->setTitleLang('');
                (isset($textArray[$language]['teaser'])) ? $articleTexti18nObj->setTeaserLang(str_replace('<script', '<scri&nbsp;pt', $textArray[$language]['teaser'])) : $articleTexti18nObj->setTeaserLang('');
                (isset($textArray[$language]['text'])) ? $articleTexti18nObj->setTextLang(str_replace('<script', '<scri&nbsp;pt', $textArray[$language]['text'])) : $articleTexti18nObj->setTextLang('');

                $articleTexti18nObj->setLang($language);
                $this->_em->persist($articleTexti18nObj);
                $this->_em->flush();
            }
        }

        return;
    }
}
