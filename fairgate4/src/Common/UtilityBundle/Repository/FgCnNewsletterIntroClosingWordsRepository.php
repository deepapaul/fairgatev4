<?php

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Entity\FgCnNewsletterIntroClosingWords;

/**
 * FgCnNewsletterIntroClosingWordsRepository
 *
 * This class is basically used for bookmark related functionalities in contact manager.
 */
class FgCnNewsletterIntroClosingWordsRepository extends EntityRepository
{
    /**
     * Function to get the club's intro/closing/signature text
     *
     * @param type $clubId
     * @param type $wordType
     * @param type $newsletterType
     */
    public function getTemplates($clubId, $wordType, $newsletterType)
    {
        if($newsletterType == "newsletter") {
            if($wordType == "intro") {
                $wordTypeCond = 'INTRO';
            } else {
                $wordTypeCond = 'CLOSING';
            }
        } else {
            $wordTypeCond = 'OTHER';
        }

        $resultQuery = $this->createQueryBuilder('t')
                ->select('t.title as title, t.id as id, t.introText as value')
                ->where('t.club=:clubId')
                ->andWhere('t.wordType=:wordType')
                ->setParameter('clubId', $clubId)
                ->setParameter('wordType', $wordTypeCond);
        $result = $resultQuery->getQuery()->getResult();

        return $result;
    }
    /**
     * Function to save the intro text
     *
     * @param Integer $clubId    the club id
     * @param String  $type      the type
     * @param String  $values    the values
     * @param Integer $contactId the values
     *
     */
    public function saveIntroText($clubId, $type, $values, $contactId)
    {
        $clubobj = $this->_em->getRepository('CommonUtilityBundle:FgClub')->find($clubId);
        $contactObj = $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->find($contactId);
        $wordType = 'OTHER';
        if ($type == "intro") {
            $wordType = 'INTRO';
        } else if($type =='closing'){
            $wordType = 'CLOSING';
        }
        $title = isset($values['title'])?$values['title']:"";
        $value = isset($values['value'])?$values['value']:"";

        $introTextObj = new FgCnNewsletterIntroClosingWords();
        $introTextObj->setTitle($title);
        $introTextObj->setIntroText($value);
        $introTextObj->setClub($clubobj);
        $introTextObj->setWordType($wordType);
        $introTextObj->setUpdatedDate(new \DateTime("now"));
        $introTextObj->setsortOrder(0);
        $introTextObj->setUpdatedBy($contactObj);
        $this->getEntityManager()->persist($introTextObj);
        $this->getEntityManager()->flush();
    }
    /**
     * Function to overwrite the existing templates
     *
     * @param int $clubId the club id
     * @param type $type the type (intro or closing)
     * @param array $values the array of values
     * @param int $contactId the logged in contact id
     */
    public function overWriteExisting($clubId, $type, $values, $contactId)
    {
        $wordType = 'OTHER';
        if ($type == "intro") {
            $wordType = 'INTRO';
        } else if($type == 'ending-words') {
            $wordType = 'CLOSING';
        }
        $title = isset($values['title'])?$values['title']:"";
        $value = isset($values['value'])?$values['value']:"";

        $qb = $this->createQueryBuilder();
        $q = $qb->update('CommonUtilityBundle:FgCnNewsletterIntroClosingWords', 'w')
                ->set('w.title', $qb->expr()->literal($title))
                ->set('w.introText', $qb->expr()->literal($value))
                ->where('w.title =:title')
                ->andWhere('w.wordType =:wordType')
                ->andWhere('w.club =:clubId')
                ->setParameters(array('title' => $title, 'wordType' => $wordType, 'clubId' => $clubId))
                ->getQuery();
        $p = $q->execute();
    }
    /**
     * Function to delete intro/closing/signature
     *
     * @param type $id
     *
     */
    public function deleteIntroText($id)
    {
        $deleteObj = $this->_em->getRepository('CommonUtilityBundle:FgCnNewsletterIntroClosingWords')->find($id);
        $this->_em->remove($deleteObj);
        $this->_em->flush();
    }
    /**
     * Function to get the last inserted id
     *
     * @param int $clubId the club id
     * @param string $type the type
     * @return last values
     */
    public function getLastInsertedId($clubId, $type)
    {
         if ($type == "intro") {
            $wordType = 'INTRO';
        } else if($type =='closing'){
            $wordType = 'CLOSING';
        } else {
            $wordType = 'OTHER';
        }

         $resultQuery = $this->createQueryBuilder('t')
                ->select('t.title as title, t.id as id, t.introText as value')
                ->where('t.club=:clubId')
                ->andWhere('t.wordType=:wordType')
                ->setParameter('clubId', $clubId)
                ->setParameter('wordType', $wordType)
                ->orderBy('t.id', 'DESC')
                 ;
        $result = $resultQuery->getQuery()->getResult();

        return $result[0];

    }

}
