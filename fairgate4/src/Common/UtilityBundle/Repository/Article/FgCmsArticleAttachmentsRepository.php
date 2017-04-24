<?php

/**
 * FgCmsArticleAttachmentsRepository to handle fg_cms_article and related tables.
 */
namespace Common\UtilityBundle\Repository\Article;

use Doctrine\ORM\EntityRepository;

/**
 * FgCmsArticleAttachmentsRepository to handle functionalities related to fg_cms_article and other article tables.
 *
 * @package    InternalArticleBundle
 * @subpackage Repository
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
class FgCmsArticleAttachmentsRepository extends EntityRepository
{

    /**
     * Function to save the attcahments added to an article.
     *
     * @param array $attachmentIdArray array with file manager ids
     * @param int   $articleId         The id of the article
     *
     * @return void
     */
    public function saveArticleAttachment($attachmentIdArray, $articleId)
    {
        $articleObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsArticle')->find($articleId);

        foreach ($attachmentIdArray as $attachment) {
            $articleAttachmentObj = new \Common\UtilityBundle\Entity\FgCmsArticleAttachments();
            $fileObj = $this->_em->getRepository('CommonUtilityBundle:FgFileManager')->find($attachment);
            $articleAttachmentObj->setArticle($articleObj);
            $articleAttachmentObj->setFilemanager($fileObj);
            $articleAttachmentObj->setSortOrder(0);
            $this->_em->persist($articleAttachmentObj);
        }
        $this->_em->flush();

        return;
    }

    /**
     * Function to remove the attcahments added to an article.
     *
     * @param array $attachmentIdArray array with file manager ids
     *
     * @return void
     */
    public function removeArticleAttachment($attachmentIdArray)
    {
        foreach ($attachmentIdArray as $attachment) {
            $articleAttachmentObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsArticleAttachments')->find($attachment);
            if ($articleAttachmentObj) {
                $this->_em->remove($articleAttachmentObj);
            }
        }
        $this->_em->flush();

        return;
    }

    /**
     * Function to get count of article attachments.
     *
     * @param int $articleId article id
     *
     * @return int attachments count
     */
    public function getCountOfAttachments($articleId)
    {
        $qb = $this->createQueryBuilder('ATT')
            ->select('COUNT(ATT.id) as attachmentCount')
            ->innerJoin('CommonUtilityBundle:FgFileManager', 'FILE', 'WITH', 'ATT.filemanager = FILE.id')
            ->where('ATT.article=:articleId')
            ->setParameters(array('articleId' => $articleId));

        return $qb->getQuery()->getSingleScalarResult();
    }
}
