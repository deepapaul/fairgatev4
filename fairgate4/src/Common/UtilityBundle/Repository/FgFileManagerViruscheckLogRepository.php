<?php

/**
 * 
 * @package 	CommonUtilityBundle
 * @subpackage 	Repository
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 * 
 */

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Util\FgSettings;

/**
 * FgFileManagerViruscheckLogRepository
 *
 * @author pitsolutions
 */
class FgFileManagerViruscheckLogRepository extends EntityRepository
{

    /**
     * Function to save the log entry
     *
     * @param array $logData The log data to be inserted
     * @param int   $logId  The log ID for updating
     *
     * @return int $result Id of log entry
     */
    public function saveVirusLogData($logData, $logId)
    {
        if (is_null($logId)) {
            $logObj = new \Common\UtilityBundle\Entity\FgFileManagerViruscheckLog;
            $logObj->setClub($this->_em->getRepository('CommonUtilityBundle:FgClub')->find($logData['club']));
            $logObj->setContact($this->_em->getRepository('CommonUtilityBundle:FgCmContact')->find($logData['contact']));
            $logObj->setFileDetails($logData['fileDetails']);
            $logObj->setFileName($logData['fileName']);
            $logObj->setLogDate(new \DateTime('now'));
            $logObj->setRequestSenton($logData['sentOn']);
            $logObj->setResponseStatus('not_responding');
            $logObj->setAvastscanOption($logData['avastscanOption']);
        } else {
            $logObj = $this->_em->getRepository('CommonUtilityBundle:FgFileManagerViruscheckLog')->find($logId);
            $logObj->setResponseDetail($logData['responseDetail']);
            $logObj->setResponseReceivedon($logData['responseReceivedon']);
            $logObj->setResponseStatus($logData['responseStatus']);
        }
        $this->_em->persist($logObj);
        $this->_em->flush();

        return $logObj->getId();
    }
    
    /**
     * Method to get all logs
     * 
     * @param array $filterArray The filter data
     * 
     * @return array $return of results
     */
    public function getVirusLogs($filterArray)
    {
        $datetimeFormat = FgSettings::getMysqlDateTimeFormat();
        $doctrineConfig = $this->getEntityManager()->getConfiguration();
		$doctrineConfig->addCustomStringFunction('DATE_FORMAT', 'Common\UtilityBundle\Extensions\DateFormat');
		$doctrineConfig->addCustomStringFunction('contactName', 'Common\UtilityBundle\Extensions\FetchContactName');
            
        $qb = $this->createQueryBuilder('V')
            ->select("(DATE_FORMAT(V.logDate, '$datetimeFormat')) AS logDisplayDate, C.title, contactName(CC.id) AS contact, V.fileName,V.fileDetails,V.responseStatus,V.responseDetail,V.avastscanOption, IDENTITY(V.club) AS clubId, IDENTITY(V.contact) AS contactId")
            ->leftJoin('CommonUtilityBundle:FgClub', 'C', 'WITH', 'C.id = V.club')
            ->leftJoin('CommonUtilityBundle:FgCmContact', 'CC', 'WITH', 'CC.id = V.contact');
        
        
        if(isset($filterArray['startDate'])){
           $qb->andWhere('V.logDate >=:startDate')
               ->setParameter('startDate', new \DateTime($filterArray['startDate']));
        }
        if(isset($filterArray['endDate'])){
            $qb->andWhere('V.logDate <=:endDate')
               ->setParameter('endDate', new \DateTime($filterArray['endDate']));
        }
        
        if(count($filterArray['responseStatus']) > 0){
            $qb->andWhere('V.responseStatus IN(:responseStatus)')
               ->setParameter('responseStatus', $filterArray['responseStatus']);
        }
        
        if(isset($filterArray['orderColumn'])){
            $qb->orderBy('V.'.$filterArray['orderColumn'], $filterArray['orderColumnDirection']);
        } else {
            $qb->orderBy('V.id', 'DESC');
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
    public function getVirusLogCount($filterArray)
    {
        $qb = $this->createQueryBuilder('V')
            ->select("COUNT(V.id) AS totalCount");
        
        if(isset($filterArray['startDate'])){
           $qb->andWhere('V.logDate >=:startDate')
               ->setParameter('startDate', new \DateTime($filterArray['startDate']));
        }
        if(isset($filterArray['endDate'])){
            $qb->andWhere('V.logDate <=:endDate')
               ->setParameter('endDate', new \DateTime($filterArray['endDate']));
        }
        if(isset($filterArray['responseStatus'])){
            $qb->andWhere('V.responseStatus IN(:responseStatus)')
               ->setParameter('responseStatus', $filterArray['responseStatus']);
        }
        
        $result = $qb->getQuery()->getSingleResult();
        return $result;
    }
}
