<?php

/**
 * FgApiAccesslogRepository.
 */
namespace Common\UtilityBundle\Repository\Api;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Entity\FgApiAccesslog;
use Common\UtilityBundle\Util\FgSettings;

/**
 * FgApiAccesslog.
 *
 * The function to save the got court api access log
 */
class FgApiAccesslogRepository extends EntityRepository
{

    /**
     * This method is used to save access log
     * 
     * @param arry $logData   Log data
     * @param int  $clubId    Club id
     * @param int  $apiId     API id  
     * 
     * @return boolean
     */
    public function saveAccessLog($logData, $clubId, $apiId)
    {
        $clubObj = ($clubId > 0) ? $this->_em->getRepository('CommonUtilityBundle:FgClub')->find($clubId) : NULL;
        $apiObj = $this->_em->getRepository('CommonUtilityBundle:FgApis')->find($apiId);
        $logObj = new FgApiAccesslog();
        $logObj->setClub($clubObj);
        $logObj->setApi($apiObj);
        $logObj->setResponseDetail($logData['responseDetails']);
        $logObj->setResponseCode($logData['responseCode']);
        $logObj->setRequestClientip($logData['clientIp']);
        $logObj->setRequestDetail($logData['requestDetails']);
        $logObj->setDate(new \DateTime());
        $logObj->setApiUrl($logData['apiUrl']);
        $this->_em->persist($logObj);
        $this->_em->flush();

        return true;
    }
    
    /**
     * Method to get all logs
     * 
     * @param array $filterArray The filter data
     * 
     * @return array $return of results
     */
    public function getAccessLog($filterArray, $clubDefaultLanguage = 'de')
    {
        $datetimeFormat = FgSettings::getMysqlDateTimeFormat();
        $doctrineConfig = $this->getEntityManager()->getConfiguration();
		$doctrineConfig->addCustomStringFunction('DATE_FORMAT', 'Common\UtilityBundle\Extensions\DateFormat');
            
        $qb = $this->createQueryBuilder('A')
            ->select("(DATE_FORMAT(A.date, '$datetimeFormat')) AS logDisplayDate, COALESCE(NULLIF(ci18n.titleLang, ''), C.title) as title, A.apiUrl, A.requestDetail, A.requestClientip, A.responseDetail, A.responseCode")
            ->leftJoin('CommonUtilityBundle:FgClub', 'C', 'WITH', 'C.id = A.club')
            ->leftJoin('CommonUtilityBundle:FgClubI18n', 'ci18n', 'WITH', "ci18n.id = C.id AND ci18n.lang='{$clubDefaultLanguage}'");
            
        if(isset($filterArray['startDate'])){
           $qb->andWhere('A.date >=:startDate')
               ->setParameter('startDate', new \DateTime($filterArray['startDate']));
        }
        if(isset($filterArray['endDate'])){
            $qb->andWhere('A.date <=:endDate')
               ->setParameter('endDate', new \DateTime($filterArray['endDate']));
        }
        if(isset($filterArray['apiType'])){
            $qb->andWhere('A.api =:apiType')
               ->setParameter('apiType', $filterArray['apiType']);
        }

        if(isset($filterArray['orderColumn'])){
            if($filterArray['orderColumn'] == 'title'){
                $qb->orderBy('C.'.$filterArray['orderColumn'], $filterArray['orderColumnDirection']);
            } else {
                $qb->orderBy('A.'.$filterArray['orderColumn'], $filterArray['orderColumnDirection']);
            }
        } else {
            $qb->orderBy('A.id', 'DESC');
        }
        
        if(isset($filterArray['limitStart'])){
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
     * 
     * @return array $return of results
     */
    public function getAccessLogCount($filterArray)
    {
        $qb = $this->createQueryBuilder('A')
            ->select("COUNT(A.id) AS totalCount");
        
        if(isset($filterArray['startDate'])){
           $qb->andWhere('A.date >=:startDate')
               ->setParameter('startDate', new \DateTime($filterArray['startDate']));
        }
        if(isset($filterArray['endDate'])){
            $qb->andWhere('A.date <=:endDate')
               ->setParameter('endDate', new \DateTime($filterArray['endDate']));
        }
        
        $result = $qb->getQuery()->getSingleResult();
        return $result;
    }
}
