<?php

/**
 * FgCmsArticleCommentsRepository to handle the article comments functionality.
 */
namespace Common\UtilityBundle\Repository\Article;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Util\FgSettings;
use Common\UtilityBundle\Util\FgUtility;

/**
 * FgCmsArticleCommentsRepository to handle the article comments functionality.
 *
 * @package    InternalArticleBundle
 * @subpackage Repository
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
class FgCmsArticleCommentsRepository extends EntityRepository
{

    /**
     * Function to get all the comments of an article.
     *
     * @param int $articleId article id
     *
     * @return array article comments data
     */
    public function getCommentsOfArticle($articleId)
    {
        $datetimeFormat = FgSettings::getMysqlDateTimeFormat();
        $doctrineConfig = $this->getEntityManager()->getConfiguration();
        $doctrineConfig->addCustomStringFunction('DATE_FORMAT', 'Common\UtilityBundle\Extensions\DateFormat');
        $doctrineConfig->addCustomStringFunction('contactNameNoSort', 'Common\UtilityBundle\Extensions\FetchContactNameNoSort');
        $comments = $this->createQueryBuilder('c')
            ->select("c.id, a.id as articleId, c.comment, IDENTITY(c.updatedBy) as updatedId, IDENTITY(c.createdBy) as createdId, a.commentAllow, contactNameNoSort(c.updatedBy 0) as updatedBy, contactNameNoSort(c.createdBy 0) as createdBy, (DATE_FORMAT(c.createdOn, '$datetimeFormat')) as createdDate, (DATE_FORMAT(c.updatedOn, '$datetimeFormat')) as updatedDate, c.guestUserName,"
                . "CASE WHEN (CON.isCompany = 1 ) THEN M.companyLogo ELSE M.clubProfilePicture END as creatorProfileImg, CON.isCompany as creatorIsCompany ")
            ->leftJoin('CommonUtilityBundle:FgCmsArticle', 'a', 'WITH', 'a.id = c.article')
            ->leftJoin('CommonUtilityBundle:FgCmContact', 'CON', 'WITH', 'CON.id = c.createdBy')
            ->leftJoin('CommonUtilityBundle:MasterSystem', 'M', 'WITH', 'M.fedContact = CON.fedContact')
            ->where('c.article=:articleId')
            ->orderBy('c.id', 'DESC')
            ->setParameters(array('articleId' => $articleId));


        return $comments->getQuery()->getArrayResult();
    }

    /**
     * Function to save a comment for an article.
     *
     * @param int        $articleId        article id
     * @param int|string $commentId        comment id
     * @param string     $comment          comment to be saved
     * @param int        $contactId        current contact id
     * @param string     $guestContactName guest contact name
     *
     * @return array $returnArray article created on date
     */
    public function saveComments($articleId, $commentId, $comment, $contactId, $guestContactName = '')
    {
        $returnArray = array();
        $contactObj = ($contactId) ? $this->_em->getReference('CommonUtilityBundle:FgCmContact', $contactId) : null;
        $articleObj = $this->_em->getReference('CommonUtilityBundle:FgCmsArticle', $articleId);
        if ($commentId == 'new') {
            $commentObj = new \Common\UtilityBundle\Entity\FgCmsArticleComments();
            $commentObj->setContact($contactObj);
            $commentObj->setCreatedOn(new \DateTime('now'));
            $commentObj->setCreatedBy($contactObj);
            $conn = $this->_em->getConnection();
            $guestUser = FgUtility::getSecuredData($guestContactName, $conn, false);
            $commentObj->setGuestUserName($guestUser);
        } else {
            $commentObj = $this->_em->getReference('CommonUtilityBundle:FgCmsArticleComments', $commentId);
            $commentObj->setUpdatedOn(new \DateTime('now'));
            $commentObj->setUpdatedBy($contactObj);
        }

        $commentObj->setComment($comment);
        $commentObj->setArticle($articleObj);
        $this->_em->persist($commentObj);
        $this->_em->flush();

        $dateTimeObj = $commentObj->getCreatedOn();
        $datetimeFormat = FgSettings::getPhpDateTimeFormat();
        $dateTime = $dateTimeObj->format($datetimeFormat);
        $returnArray['date'] = $dateTime;

        return $returnArray;
    }

    /**
     * Function to delete a particular comment of an article.
     *
     * @param int $commentId comment id
     *
     * @return void
     */
    public function deleteComments($commentId)
    {
        $commentObj = $this->_em->getReference('CommonUtilityBundle:FgCmsArticleComments', $commentId);
        $this->_em->remove($commentObj);
        $this->_em->flush();

        return;
    }

    /**
     * Function to find the total number of comments for an article.
     *
     * @param int $articleId article id
     *
     * @return int comments count
     */
    public function getCommentsTotal($articleId)
    {
        $comments = $this->createQueryBuilder('c')
            ->select('count(c.id) AS commentCount')
            ->leftJoin('CommonUtilityBundle:FgCmsArticle', 'a', 'WITH', 'a.id = c.article')
            ->where('c.article=:articleId')
            ->setParameters(array('articleId' => $articleId));

        $dataResult = $comments->getQuery()->getResult();

        return empty($dataResult) ? 0 : $dataResult[0]['commentCount'];
    }
}
