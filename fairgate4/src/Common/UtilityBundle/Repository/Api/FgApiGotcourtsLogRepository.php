<?php

/**
 * FgApiGotcourtsLogRepository.
 */
namespace Common\UtilityBundle\Repository\Api;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Entity\FgApiGotcourtsLog;
use Common\UtilityBundle\Util\FgSettings;

/**
 * FgApiGotcourtsLog.
 *
 * The function to save the got court api log
 */
class FgApiGotcourtsLogRepository extends EntityRepository
{

    /**
     * Method to save the connection details to the DB
     * 
     * @param array  $dataArray      The log details that needd to be inserted
     * @param int   $clubId         The current club id
     * @param int   $gotCourtId     The gotcourt id
     * @param int   $changedBy      The current loggedin user
     * 
     * @return boolean
     */
    public function saveServiceLog($dataArray, $clubId, $gotCourtId, $changedBy)
    {
        $clubObj = $this->_em->getRepository('CommonUtilityBundle:FgClub')->find($clubId);
        $gotcourtObj = $this->_em->getRepository('CommonUtilityBundle:FgApiGotcourts')->find($gotCourtId);
        $changedByObj = (is_int($changedBy)) ? $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->find($changedBy) : NULL;

        foreach ($dataArray as $logData) {

            $logObj = new FgApiGotcourtsLog();
            $logObj->setClub($clubObj);
            $logObj->setGotcourt($gotcourtObj);
            $logObj->setChangedBy($changedByObj);
            $logObj->setField($logData['field']);
            $logObj->setDate(new \DateTime());
            $logObj->setValueAfter($logData['value_after']);
            $logObj->setValueBefore($logData['value_before']);
            $this->_em->persist($logObj);
        }
        $this->_em->flush();

        return true;
    }

    /**
     * This method is used to get the GotCourts log details
     * 
     * @param array  $filterArray Filter parameters
     * @param object $club        Club service object
     * 
     * @return array              Log details  
     */
    public function getServiceLog($filterArray, $club)
    {
        $clubDefaultLanguage = $club->get('default_lang');
        $clubId = $club->get('id');
        $datetimeFormat = FgSettings::getMysqlDateTimeFormat();
        $doctrineConfig = $this->getEntityManager()->getConfiguration();
        $doctrineConfig->addCustomStringFunction('DATE_FORMAT', 'Common\UtilityBundle\Extensions\DateFormat');
        $doctrineConfig->addCustomStringFunction('contactNameNoSort', 'Common\UtilityBundle\Extensions\FetchContactNameNoSort');
        $doctrineConfig->addCustomStringFunction('checkActiveContact', 'Common\UtilityBundle\Extensions\CheckActiveContact');

        $selectEvent = "CASE WHEN (A.valueAfter = 'booked') THEN 'booking' ELSE 'keyGeneration' END AS event";
        $qb = $this->createQueryBuilder('A')
            ->select("A.id, (DATE_FORMAT(A.date, '$datetimeFormat')) AS logDisplayDate, $selectEvent, COALESCE(NULLIF(ci18n.titleLang, ''), C.title) as title, A.field, A.valueBefore, A.valueAfter, IDENTITY(A.changedBy) as changedBy, contactNameNoSort(A.changedBy 0) as contact, CheckActiveContact(A.changedBy, :clubId) as activeContactId")
            ->innerJoin('CommonUtilityBundle:FgClub', 'C', 'WITH', 'C.id = A.club')
            ->leftJoin('CommonUtilityBundle:FgClubI18n', 'ci18n', 'WITH', "ci18n.id = C.id AND ci18n.lang='{$clubDefaultLanguage}'");
        $qb->where('A.club =:clubId');
        $qb->setParameter('clubId', $clubId);
        if (isset($filterArray['startDate'])) {
            $qb->andWhere('A.date >=:startDate')
                ->setParameter('startDate', new \DateTime($filterArray['startDate']));
        }
        if (isset($filterArray['endDate'])) {
            $qb->andWhere('A.date <=:endDate')
                ->setParameter('endDate', new \DateTime($filterArray['endDate']));
        }

        if (isset($filterArray['orderColumn'])) {
            if ($filterArray['orderColumn'] == 'title') {
                $qb->orderBy('C.' . $filterArray['orderColumn'], $filterArray['orderColumnDirection']);
            } else {
                $qb->orderBy('A.' . $filterArray['orderColumn'], $filterArray['orderColumnDirection']);
            }
        } else {
            $qb->orderBy('A.id', 'DESC');
        }

        if (isset($filterArray['limitStart'])) {
            $qb->setFirstResult($filterArray['limitStart']);
            $qb->setMaxResults($filterArray['limitLength']);
        }

        $result = $qb->getQuery()->getArrayResult();

        return $result;
    }

    /**
     * Method to get the count of logs
     * 
     * @param array $filterArray The filter data
     * @param int   $clubId      Current club id
     * 
     * @return array Log count
     */
    public function getServiceLogCount($filterArray, $clubId)
    {
        $qb = $this->createQueryBuilder('A')
            ->select("COUNT(A.id) AS totalCount")
            ->where('A.club =:clubId')
            ->setParameter('clubId', $clubId);
        if (isset($filterArray['startDate'])) {
            $qb->andWhere('A.date >=:startDate')
                ->setParameter('startDate', new \DateTime($filterArray['startDate']));
        }
        if (isset($filterArray['endDate'])) {
            $qb->andWhere('A.date <=:endDate')
                ->setParameter('endDate', new \DateTime($filterArray['endDate']));
        }
        $result = $qb->getQuery()->getSingleResult();

        return $result;
    }
}
