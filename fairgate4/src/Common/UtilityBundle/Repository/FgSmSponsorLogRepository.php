<?php

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Util\FgSettings;

/**
 * FgSmSponsorLogRepository is used for listing sponsor logs
 *
 * @author pitsolutions.ch
 */
class FgSmSponsorLogRepository extends EntityRepository {

    /**
     * Function for getting log entries for sponsors
     *
     * @param int $contactId contact id
     * @param int $clubId    current club id
     *
     * @return array
     */
    public function getAllSponsorLog($contactId, $clubId) {
        $doctrineConfig = $this->getEntityManager()->getConfiguration();
        $doctrineConfig->addCustomStringFunction('DATE_FORMAT', 'Common\UtilityBundle\Extensions\DateFormat');
        $datetimeFormat = FgSettings::getMysqlDateTimeFormat();
        $qb = $this->createQueryBuilder('sl')
                ->select("DATE_FORMAT(sl.date,'$datetimeFormat') as date, DATE_FORMAT(sl.date,'%Y.%m.%d %h:%i:%s') AS dateOriginal, sl.actionType as action, sc.title as category, sr.title as service, contactName(sl.changedBy) as editedBy")
                ->leftJoin('CommonUtilityBundle:FgSmServices', 'sr', 'WITH', 'sl.service=sr.id')
                ->leftJoin('CommonUtilityBundle:FgSmCategory', 'sc', 'WITH', "sc.id=sl.category")
                ->where('sl.club=:club')
                ->andWhere('sl.contact=:contactId')
                ->setParameter('club', $clubId)
                ->setParameter('contactId', $contactId);
        $result = $qb->getQuery()->getResult();
        return $result;
    }
    
    /**
     * Function to insert log entry.
     *
     * @param int     $clubId      Club Id
     * @param int     $serviceId   Service Id
     * @param int     $currContId  Current Logged-In Contact Id
     * @param array   $logData     Array of log data
     * @param object  $clubObj     Object of Club
     * @param object  $serviceObj  Object of Service
     * @param object  $currContObj Object of Current Logged-In Contact
     * @param boolean $doSave      Whether to save entry or return object
     *
     * @return \Common\UtilityBundle\Entity\FgSmSponsorLog Returns Log Object if '$doSave' is true.
     */
    public function insertLog($clubId, $serviceId, $currContId, $logData, $clubObj = false, $serviceObj = false, $currContObj = false, $doSave = true)
    {
        $currDateTime = new \DateTime("now");
        if (!$clubObj) {
            $clubObj = $this->_em->getRepository('CommonUtilityBundle:FgClub')->find($clubId);
        }
        if (!$serviceObj) {
            $serviceObj = $this->_em->getRepository('CommonUtilityBundle:FgSmServices')->find($serviceId);
        }
        if (!$currContObj) {
            $currContObj = $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->find($currContId);
        }
        $logObj = new \Common\UtilityBundle\Entity\FgSmSponsorLog();
        $logObj->setClub($clubObj)
                ->setService($serviceObj)
                ->setChangedBy($currContObj)
                ->setDate($currDateTime);

        if (isset($logData['kind'])) {
            $logObj->setKind($logData['kind']);
        }       
        if (isset($logData['action_type'])) {
            $logObj->setActionType($logData['action_type']);
        }
        if (isset($logData['sponsor_id'])) {
            $sponsorObj = $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->find($logData['sponsor_id']);
            $logObj->setContact($sponsorObj);
        }
        if (isset($logData['category'])) {
            $categoryObj = $this->_em->getRepository('CommonUtilityBundle:FgSmCategory')->find($logData['category']);
            $logObj->setCategory($categoryObj);
        }
        if ($doSave) {
            $this->_em->persist($logObj);
            $this->_em->flush();
        } else {
            return $logObj;
        }
    }

}
