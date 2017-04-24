<?php

/**
 * FgCmsArticleSelectedcategoriesRepository for managing the fg_cms_article table.
 */
namespace Common\UtilityBundle\Repository\Article;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Entity\FgCmsArticleSelectedcategories;

/**
 * FgCmsArticleSelectedcategoriesRepository for managing the fg_cms_article table.
 *
 * @package    InternalArticleBundle
 * @subpackage Repository
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
class FgCmsArticleSelectedcategoriesRepository extends EntityRepository
{

    /**
     * Function to save the selected categories to the article.
     *
     * @param array $categories category array
     * @param int   $articleId  article Id
     * @param bool  $assign     assign flag
     * 
     * @return void
     */
    public function saveArticleCategories($categories, $articleId, $assign = false)
    {
        //delete selected areas if any
        if (!$assign) {
            $this->deleteCategoriesOfArticle($articleId);
        }
        foreach ($categories as $categoryId) {
            $selectedCategoryObj = new FgCmsArticleSelectedcategories();
            $articleObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsArticle')->find($articleId);
            $categoryObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsArticleCategory')->find($categoryId);
            $selectedCategoryObj->setArticle($articleObj);
            $selectedCategoryObj->setCategory($categoryObj);
            $this->_em->persist($selectedCategoryObj);
        }
        $this->_em->flush();

        return;
    }

    /**
     * Method to delete selected categories of an article.
     *
     * @param int $articleId article Id
     * 
     * @return void
     */
    private function deleteCategoriesOfArticle($articleId)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->delete('CommonUtilityBundle:FgCmsArticleSelectedcategories', 'C');
        $qb->where('C.article = :articleId');
        $qb->setParameter('articleId', $articleId);
        $query = $qb->getQuery();
        $query->execute();

        return;
    }

    /**
     * Method to get Category Ids of an article.
     *
     * @param int $articleId article Id
     *
     * @return array Category Ids
     */
    public function getCategoryIdsFromArticle($articleId)
    {
        $qb = $this->createQueryBuilder('C')
            ->select('DISTINCT(C.category)')
            ->where('C.article=:articleId')
            ->setParameters(array('articleId' => $articleId));
        $result = $qb->getQuery()->getResult();

        return array_map(function ($a) {
            return $a[1];
        }, $result);
    }

    /**
     * Function to save article Category log.
     *
     * @param int    $articleId article id
     * @param String $catNames  category names
     * @param Object $container container object
     * 
     * @return void
     */
    public function saveArticleCategoryLog($articleId, $catNames, $container)
    {
        $clubId = $container->get('club')->get('id');
        $contactId = $container->get('contact')->get('id');
        $logArray[] = "($clubId,$articleId,now(),'areas','area','$catNames','',$contactId)";
        $this->_em->getRepository('CommonUtilityBundle:FgCmsArticleLog')->saveLog($logArray);

        return;
    }
}
