<?php

/**
 * FgCmsArticleSelectedareasRepository for managing the fg_cms_article table.
 */
namespace Common\UtilityBundle\Repository\Article;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Entity\FgCmsArticleSelectedareas;

/**
 * FgCmsArticleSelectedareasRepository for managing the fg_cms_article table functionalities.
 *
 * @package    InternalArticleBundle
 * @subpackage Repository
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
class FgCmsArticleSelectedareasRepository extends EntityRepository
{

    /**
     * Function to save the selected area to the article.
     *
     * @param int $textVersionId The version of the text
     * @param int $articleId     The id of the article, when given that article will be updated
     *
     * @return void
     */
    public function saveArticleTextVersion($textVersionId, $articleId)
    {
        $textVersionObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsArticleText')->find($textVersionId);
        $articleObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsArticle')->find($articleId);
        $articleObj->setTextversion($textVersionObj);
        $this->_em->persist($articleObj);
        $this->_em->flush();

        return;
    }

    /**
     * Function to save the selected area to the article.
     *
     * @param array $areas     areas array
     * @param int   $articleId articleId
     * @param bool  $assign    assign flag
     * 
     * @return void
     */
    public function saveArticleAreas($areas, $articleId, $assign = false)
    {
        //delete selected areas if any
        if (!$assign) {
            $this->deleteAreasOfArticle($articleId);
        }
        foreach ($areas as $areaId) {
            $selectedAreaObj = new FgCmsArticleSelectedareas();
            $articleObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsArticle')->find($articleId);
            if ($areaId != 'Club') {
                $areaObj = $this->_em->getRepository('CommonUtilityBundle:FgRmRole')->find($areaId);
            }
            if ($articleObj) {
                $selectedAreaObj->setArticle($articleObj);
                if ($areaId != 'Club') {
                    $selectedAreaObj->setRole($areaObj);
                    $selectedAreaObj->setIsClub(0);
                } else {
                    $selectedAreaObj->setIsClub(1);
                }

                $this->_em->persist($selectedAreaObj);
            }
        }
        $this->_em->flush();

        return;
    }

    /**
     * Method to delete selected areas of an article.
     *
     * @param int $articleId article Id
     * 
     * @return void
     */
    private function deleteAreasOfArticle($articleId)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->delete('CommonUtilityBundle:FgCmsArticleSelectedareas', 'SL');
        $qb->where('SL.article = :articleId');
        $qb->setParameter('articleId', $articleId);
        $query = $qb->getQuery();
        $query->execute();

        return;
    }

    /**
     * Method to get roleIds and isClub of an article.
     *
     * @param int $articleId article Id
     *
     * @return array array of roles and isClub
     */
    public function getRolesOfArticle($articleId)
    {
        $qb = $this->createQueryBuilder('A')
            ->select("GROUP_CONCAT(ROLE.id) as areaIds, CASE WHEN (SUM(DISTINCT A.isClub) > 0) THEN 'Club' ELSE '' END as  areaClub ")
            ->leftJoin('CommonUtilityBundle:FgRmRole', 'ROLE', 'WITH', 'A.role = ROLE.id')
            ->where('A.article=:articleId')
            ->setParameters(array('articleId' => $articleId));
        $result = $qb->getQuery()->getArrayResult();
        $return['roles'] = explode(',', $result[0]['areaIds']);
        if ($result[0]['areaClub']) {
            $return['isClub'] = $result[0]['areaClub'];
        }

        return $return;
    }

    /**
     * Method to get roleIds and isClub of an article.
     *
     * @param int $articleId article Id
     *
     * @return array Role Ids
     */
    public function getRolesIdsFromArticle($articleId)
    {
        $qb = $this->createQueryBuilder('A')
            ->select('DISTINCT(A.role), A.isClub')
            ->where('A.article=:articleId')
            ->setParameters(array('articleId' => $articleId));
        $result = $qb->getQuery()->getResult();

        return array_map(function ($a) {
            return ($a['isClub'] == 1) ? 'Club' : $a[1];
        }, $result);
    }

    /**
     * Function to save article area log.
     *
     * @param int    $articleId  article id
     * @param String $areasNames area names
     * @param Object $container  container object
     * 
     * @return void
     */
    public function saveArticleAreaLog($articleId, $areasNames, $container)
    {
        $clubId = $container->get('club')->get('id');
        $contactId = $container->get('contact')->get('id');
        $logArray[] = "($clubId,$articleId,now(),'areas','area','$areasNames','',$contactId)";
        $this->_em->getRepository('CommonUtilityBundle:FgCmsArticleLog')->saveLog($logArray);

        return;
    }
}
