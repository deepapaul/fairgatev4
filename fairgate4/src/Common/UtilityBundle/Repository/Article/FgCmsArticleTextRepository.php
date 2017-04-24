<?php

/**
 * FgCmsArticleTextRepository for managing the fg_cms_article table.
 */
namespace Common\UtilityBundle\Repository\Article;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Entity\FgCmsArticleText;
use Common\UtilityBundle\Util\FgSettings;

/**
 * FgCmsArticleTextRepository for managing the fg_cms_article table.
 *
 * @package    InternalArticleBundle
 * @subpackage Repository
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
class FgCmsArticleTextRepository extends EntityRepository
{

    /**
     * Function to create the text entry and text18n for article. This function will also update the text-version of the article.
     *
     * @param array  $textArray           The array with the details to be saved
     * @param int    $contactId           Current user id
     * @param int    $articleId           The id of the article
     * @param string $clubDefaultLanguage the default language of the club
     *
     * @return int Id of the article version
     */
    public function saveArticleText($textArray, $contactId, $articleId, $clubDefaultLanguage)
    {
        $articleTextObj = new FgCmsArticleText();
        $articleTextObj->setArticle($this->_em->getRepository('CommonUtilityBundle:FgCmsArticle')->find($articleId));

        $articleTextObj->setLastEditedby($this->_em->getRepository('CommonUtilityBundle:FgCmContact')->find($contactId));
        $articleTextObj->setLastEditedon(new \DateTime('now'));

        (isset($textArray[$clubDefaultLanguage]['title'])) ? $articleTextObj->setTitle(str_replace('<script', '<scri&nbsp;pt', $textArray[$clubDefaultLanguage]['title'])) : '';
        (isset($textArray[$clubDefaultLanguage]['teaser'])) ? $articleTextObj->setTeaser(str_replace('<script', '<scri&nbsp;pt', $textArray[$clubDefaultLanguage]['teaser'])) : '';
        (isset($textArray[$clubDefaultLanguage]['text'])) ? $articleTextObj->setText(str_replace('<script', '<scri&nbsp;pt', $textArray[$clubDefaultLanguage]['text'])) : '';

        $this->_em->persist($articleTextObj);
        $this->_em->flush();

        return $articleTextObj->getId();
    }

    /**
     * Method to save default club language entries to main table. To handle scenarios when club default languages changes.
     *
     * @param int    $articleId           articleId
     * @param string $clubDefaultLanguage club-default-lang
     *
     * @return void
     */
    public function saveDefaultLang($articleId, $clubDefaultLanguage)
    {
        $sql = 'UPDATE fg_cms_article_text T INNER JOIN fg_cms_article_text_i18n TL ON (T.id = TL.id AND TL.lang = :clubDefaultLanguage) ' .
            'INNER JOIN fg_cms_article A ON (A.id = T.article_id AND A.id = :articleId AND A.textversion_id = T.id) ' .
            'SET T.title = TL.title_lang, T.teaser = TL.teaser_lang, T.text = TL.text_lang';
        $stmt = $this->_em->getConnection()->prepare($sql);
        $stmt->bindValue('articleId', $articleId);
        $stmt->bindValue('clubDefaultLanguage', $clubDefaultLanguage);
        $stmt->execute();

        return;
    }

    /**
     * Function to get article text history.
     *
     * @param int    $articleId articleId
     * @param string $language  club-default-lang
     *
     * @return array article text history array
     */
    public function getArticleTextHistory($articleId, $language)
    {
        $datetimeFormat = FgSettings::getMysqlDateTimeFormat();
        $doctrineConfig = $this->getEntityManager()->getConfiguration();
        $doctrineConfig->addCustomStringFunction('DATE_FORMAT', 'Common\UtilityBundle\Extensions\DateFormat');
        $doctrineConfig->addCustomStringFunction('contactNameNoSort', 'Common\UtilityBundle\Extensions\FetchContactName');
        $qb = $this->createQueryBuilder('T')
            ->select("T.id, IDENTITY(T.article), IDENTITY(T.article) as article,T.title as articleTitle,T.teaser,T.text,DATE_FORMAT(T.lastEditedon, '$datetimeFormat') as  lastEdited,contactName(T.lastEditedby) as updatedBy")
            ->innerJoin('CommonUtilityBundle:FgCmsArticle', 'A', 'WITH', 'T.article = A.id')
            ->leftJoin('CommonUtilityBundle:FgCmsArticleTextI18n', 'ATL', 'WITH', 'ATL.id = T.id')
            ->where('T.article=:articleId')
            ->andWhere('T.id <> A.textversion');
        if (isset($language)) {
            $qb->andWhere('ATL.lang =:clubDefaultLanguage');
        }
        $qb->setParameter('articleId', $articleId);
        if (isset($language)) {
            $qb->setParameter('clubDefaultLanguage', $language);
        }
        $qb->orderBy('T.lastEditedon', 'DESC');

        return $qb->getQuery()->getArrayResult();
    }
}
