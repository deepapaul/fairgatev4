<?php

/**
 * FgCmsArticleMediaRepository for managing the fg_cms_article table.
 */
namespace Common\UtilityBundle\Repository\Article;

use Doctrine\ORM\EntityRepository;

/**
 * FgCmsArticleMediaRepository for managing the fg_cms_article table.
 *
 * @package    InternalArticleBundle
 * @subpackage Repository
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
class FgCmsArticleMediaRepository extends EntityRepository
{

    /**
     * Function to save the media added to an article.
     *
     * @param array   $itemIdArray array with gallery item ids
     * @param Integer $articleId   article id
     *
     * @return void
     */
    public function saveArticleMedia($itemIdArray, $articleId)
    {
        $articleObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsArticle')->find($articleId);

        foreach ($itemIdArray as $item) {
            $articleMediaObj = new \Common\UtilityBundle\Entity\FgCmsArticleMedia();
            $itemObj = $this->_em->getRepository('CommonUtilityBundle:FgGmItems')->find($item['itemId']);
            $articleMediaObj->setArticle($articleObj);
            $articleMediaObj->setItems($itemObj);
            $articleMediaObj->setSortOrder($item['sortOrder']);
            $this->_em->persist($articleMediaObj);
        }
        $this->_em->flush();

        return;
    }

    /**
     * Function to update sort order.
     *
     * @param array $mediaArray array to save
     *
     * @return void
     */
    public function updateArticleMediaSortOrder($mediaArray)
    {
        foreach ($mediaArray as $media) {
            if ($media['sort_order'] && $media['mediaid'] && $media['itemid']) {
                $mediaObj = $this->find($media['mediaid']);
                $mediaObj->setSortOrder($media['sort_order']);
                $this->_em->persist($mediaObj);
                $this->_em->flush();
            }
        }

        return;
    }

    /**
     * Function to remove the media added to an article.
     *
     * @param array $mediaIdArray array with Article-media Ids
     *
     * @return void
     */
    public function removeArticleAttachment($mediaIdArray)
    {
        foreach ($mediaIdArray as $mediaId) {
            $articleMediaObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsArticleMedia')->find($mediaId);
            if ($articleMediaObj) {
                $this->_em->remove($articleMediaObj);
            }
        }
        $this->_em->flush();

        return;
    }

    /**
     * Function to get count of article media.
     *
     * @param int    $articleId article id
     * @param string $type      gallery item type
     *
     * @return int count of article media
     */
    public function getCountOfMedia($articleId, $type = '')
    {
        $qb = $this->createQueryBuilder('AM')
            ->select('COUNT(AM.id) as attachmentCount')
            ->innerJoin('CommonUtilityBundle:FgGmItems', 'GI', 'WITH', 'AM.items = GI.id')
            ->where('AM.article=:articleId')
            ->setParameters(array('articleId' => $articleId));
        if ($type != '') {
            $qb->andWhere('GI.type=:type')->setParameter('type', $type);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }
}
