<?php

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * FgSmServicesLogRepository
 *
 * This class is used for handling sponsor services log in Sponsors Administration.
 *
 * @author pitsolutions.ch
 */
class FgSmServicesLogRepository extends EntityRepository
{

    /**
     * Function to get log data of a service.
     *
     * @param int $serviceId Service Id
     *
     * @return array $logData Result array of log data.
     */
    public function getServiceLog($serviceId,$clubId)
    {//
         $doctrineConfig = $this->getEntityManager()->getConfiguration();
         $clubDql = $this->getClubName();
         $doctrineConfig->addCustomStringFunction('checkActiveContact', 'Common\UtilityBundle\Extensions\CheckActiveContact');
        $logData = $this->createQueryBuilder('sl')
                ->select("sl.id, sl.date, sl.kind, sl.field, sl.valueBefore AS value_before, sl.valueAfter AS value_after,  IDENTITY(sl.sponsor) AS sponsorId, contactName(IDENTITY(sl.sponsor)) AS columnVal3,"
                        . "(CASE WHEN (sl.kind = 'data') THEN 'data' ELSE 'assignments' END) AS tabGroups,"
                        . "(CASE WHEN (sl.kind = 'assignment') THEN sl.actionType
                                WHEN ((sl.valueBefore IS NOT NULL AND sl.valueBefore != '' AND sl.valueBefore != '-') AND (sl.valueAfter IS NULL OR sl.valueAfter = '' OR sl.valueAfter = '-')) THEN 'removed'
                                WHEN ((sl.valueBefore IS NULL OR sl.valueBefore = '' OR sl.valueBefore = '-') AND (sl.valueAfter IS NOT NULL AND sl.valueAfter != '' AND sl.valueAfter != '-')) THEN 'added'
                                WHEN ((sl.valueBefore IS NOT NULL AND sl.valueBefore != '' AND sl.valueBefore != '-') AND (sl.valueAfter IS NOT NULL AND sl.valueAfter != '' AND sl.valueAfter != '-') AND (sl.valueBefore != sl.valueAfter)) THEN 'changed'
                                ELSE 'none'
                            END) AS status, IDENTITY(sl.changedBy) as changed_by,contactName(IDENTITY(sl.changedBy)) AS editedBy, checkActiveContact(sl.changedBy, :clubId) as activeContact,IDENTITY(sl.changedBy) as changedById")
                ->addSelect('('.$clubDql->getDQL().') AS clubChangedBy ')            
                ->where('sl.service=:serviceId')
                ->setParameters(array('serviceId' => $serviceId,'clubId'=>$clubId))
                ->getQuery()
                ->getArrayResult();

        return $logData;
    }
    /**
     * Function to get the  club.
     *
     * @return Integer
     */
    private function getClubName()
    {
        $moduleQuery = $this->getEntityManager()->createQueryBuilder();
        $moduleQuery->select('fc.title')
                ->from('CommonUtilityBundle:FgCmContact', 'ct')
                ->innerJoin('CommonUtilityBundle:FgClub', 'fc', 'WITH', 'ct.mainClub = fc.id')
                ->where('ct.id = sl.changedBy');

        return $moduleQuery;
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
     * @return \Common\UtilityBundle\Entity\FgSmServicesLog Returns Log Object if '$doSave' is true.
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
        $logObj = new \Common\UtilityBundle\Entity\FgSmServicesLog();
        $logObj->setClub($clubObj)
                ->setService($serviceObj)
                ->setChangedBy($currContObj)
                ->setDate($currDateTime);

        if (isset($logData['kind'])) {
            $logObj->setKind($logData['kind']);
        }
        if (isset($logData['field'])) {
            $logObj->setField($logData['field']);
        }
        if (isset($logData['value_before'])) {
            $logObj->setValueBefore($logData['value_before']);
        }
        if (isset($logData['value_after'])) {
            $logObj->setValueAfter($logData['value_after']);
        }
        if (isset($logData['action_type'])) {
            $logObj->setActionType($logData['action_type']);
        }
        if (isset($logData['sponsor_id'])) {
            $sponsorObj = $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->find($logData['sponsor_id']);
            $logObj->setSponsor($sponsorObj);
        }
        if ($doSave) {
            $this->_em->persist($logObj);
            $this->_em->flush();
        } else {
            return $logObj;
        }
    }

    /**
     * Function to log the changes done to sponsor services.
     *
     * @param int   $clubId       Club Id
     * @param int   $currContId   Current Logged-In Contact Id
     * @param array $logDataArray Log data array
     */
    public function insertLogData($clubId, $currContId, $logDataArray)
    {
        $clubObj = $this->_em->getRepository('CommonUtilityBundle:FgClub')->find($clubId);
        $currContObj = $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->find($currContId);
        foreach ($logDataArray as $serviceId => $logDataArr) {
            $serviceObj = $this->_em->getRepository('CommonUtilityBundle:FgSmServices')->find($serviceId);
            foreach ($logDataArr as $logData) {
                $logObj = $this->insertLog($clubId, $serviceId, $currContId, $logData, $clubObj, $serviceObj, $currContObj, false);
                $this->_em->persist($logObj);
            }
        }
        $this->_em->flush();
    }

}

