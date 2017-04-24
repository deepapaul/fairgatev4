<?php

/**
 * FgCmsArticleLogRepository for managing the fg_cms_article table.
 */
namespace Common\UtilityBundle\Repository\Article;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Util\FgSettings;

/**
 * FgCmsArticleLogRepository for managing the fg_cms_article table.
 *
 * @package    InternalArticleBundle
 * @subpackage Repository
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
class FgCmsArticleLogRepository extends EntityRepository
{

    /**
     * Function to insert the article lo.
     *
     * @param array $logArray log data array
     *
     * @return void
     */
    public function saveLog($logArray)
    {
        if (count($logArray) > 0) {
            $sql = 'INSERT INTO fg_cms_article_log (club_id,article_id,date,field,kind,value_after,value_before,changed_by) VALUES ';
            $sql .= implode(',', $logArray);
            $stmt = $this->_em->getConnection()->prepare($sql);
            $stmt->execute();
        }

        return;
    }

    /**
     * Function to get the article log entries.
     *
     * @param int $articleId article id
     * @param int $clubId    current club id
     *
     * @return array article log details
     */
    public function getLogDetailsofArticle($articleId, $clubId)
    {
        $datetimeFormat = FgSettings::getMysqlDateTimeFormat();
        $doctrineConfig = $this->getEntityManager()->getConfiguration();
        $doctrineConfig->addCustomStringFunction('DATE_FORMAT', 'Common\UtilityBundle\Extensions\DateFormat');
        $doctrineConfig->addCustomStringFunction('contactNameNoSort', 'Common\UtilityBundle\Extensions\FetchContactNameNoSort');
        $statusSql = ", (CASE WHEN ((l.valueBefore IS NULL OR l.valueBefore = '') AND (l.valueAfter IS NOT NULL OR l.valueAfter <> '')) THEN 'added' WHEN ((l.valueAfter IS NULL OR l.valueAfter = '') AND (l.valueBefore IS NOT NULL OR l.valueBefore <> '')) THEN 'removed' ELSE 'changed' END ) AS status";

        $log = $this->createQueryBuilder('l')
            ->select("l.field, l.kind , l.valueAfter, IDENTITY(l.changedBy) as changedBy, l.valueBefore, contactNameNoSort(l.changedBy 0) as contact, (DATE_FORMAT(l.date, '$datetimeFormat')) as date $statusSql")
            ->leftJoin('CommonUtilityBundle:FgCmsArticle', 'a', 'WITH', 'a.id = l.article')
            ->where('l.article=:articleId')
            ->andWhere('l.club=:clubId')
            ->orderBy('l.id', 'DESC')
            ->setParameters(array('articleId' => $articleId, 'clubId' => $clubId));

        return $log->getQuery()->getArrayResult();
    }
}
