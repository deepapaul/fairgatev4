<?php

/**
 * FgCmClubAssignmentConfirmationLogRepository
 *
 * This class is used for creating, confirming, discarding applications of sharing of a contact from another club.
 *
 * @package    CommonUtilityBundle
 * @subpackage Repository
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Entity\FgCmClubAssignmentConfirmationLog;
use Common\UtilityBundle\Util\FgSettings;

/**
 * FgCmClubAssignmentConfirmationLogRepository
 */
class FgCmClubAssignmentConfirmationLogRepository extends EntityRepository
{
    /**
     * This function is used to create an application for club sharing confirmation
     * 
     * @param int $clubId        Club id
     * @param int $fedContactId  Federation contact id
     * @param int $fedClubId     Federation club id
     * @param int $modifiedBy    Modified by
     */
    public function createApplicationForConfirmation($clubId, $fedContactId, $fedClubId, $modifiedBy)
    {
        $clubObj = $this->_em->getReference('CommonUtilityBundle:FgClub', $clubId);
        $fedContactObj = $this->_em->getReference('CommonUtilityBundle:FgCmContact', $fedContactId);
        $fedClubObj = $this->_em->getReference('CommonUtilityBundle:FgClub', $fedClubId);
        $modifiedByObj = $this->_em->getReference('CommonUtilityBundle:FgCmContact', $modifiedBy);       
        $existingClubIds = $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->getSharedClubsOfAContact($fedContactId);
        
        $confirmationObj = new FgCmClubAssignmentConfirmationLog();
        $confirmationObj->setClub($clubObj);
        $confirmationObj->setFedContact($fedContactObj);
        $confirmationObj->setFederationClub($fedClubObj);
        $confirmationObj->setExistingClubIds($existingClubIds);
        $confirmationObj->setModifiedDate(new \DateTime("now"));
        $confirmationObj->setModifiedBy($modifiedByObj);
        $confirmationObj->setStatus('PENDING');
        $this->_em->persist($confirmationObj);
        $this->_em->flush();
    }
    
    /**
     * get pending req
     * @param int $clubId
     * @return array
     */
    public function getPendingConfirmLog($clubId){
        $moduleQuery = $this->getEntityManager()->createQueryBuilder();
        $moduleQuery ->select("IDENTITY(cl.fedContact) as id")
                ->from("CommonUtilityBundle:FgCmClubAssignmentConfirmationLog", "cl")
                ->Where("cl.club=:clubId")
                ->andWhere('cl.status =:status')
                ->setParameters(array("clubId" => $clubId,'status'=>'PENDING'));
        $result = $moduleQuery->getQuery()->getResult();

        return $result;
    }
    
    /**
     * This function is used to delete the pending club assignment applications on fed membership removal
     * 
     * @param int $fedContactId
     */
    public function removePendingApplications($fedContactId)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->delete('CommonUtilityBundle:FgCmClubAssignmentConfirmationLog', 'CL');
        $qb->where('CL.fedContact = :fedContactId');
        $qb->setParameter('fedContactId', $fedContactId);
        $query = $qb->getQuery();
        $query->execute();
    }
    
    /**
     * This function is used to get the club assignment applications for a contact for a particular club in status 'pending'
     * 
     * @param int $fedContactId Fed contact id
     * @param int $clubId       Club id
     * 
     * @return $result Count of applications
     */
    public function getPendingApplicationsCount($fedContactId, $clubId)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select("COUNT(cl.id) as pendingCount")
                ->from("CommonUtilityBundle:FgCmClubAssignmentConfirmationLog", "cl")
                ->Where("cl.club = :clubId")
                ->andWhere('cl.fedContact = :fedContact')
                ->andWhere('cl.status = :status')
                ->setParameters(array('clubId' => $clubId, 'fedContact' => $fedContactId, 'status' => 'PENDING'));
        $result = $qb->getQuery()->getSingleScalarResult();

        return $result;
    }
    
    /**
     *  Function to get Club Assignment Confirmation Log Data - listing and log.
     *
     * @param string $clubType
     * @param int    $clubId
     * @param int    $tab
     * @param bool   $countFlag
     * @param string $defaultLang  default club language
     *
     * @return array or int
     */
    public function getClubAssignmentConfirmationLog($clubType, $clubId, $tab, $countFlag = false, $defaultLang)
    {
        // Configuring UDF.
        $doctrineConfig = $this->getEntityManager()->getConfiguration();
        $doctrineConfig->addCustomStringFunction('contactName', 'Common\UtilityBundle\Extensions\FetchContactName');
        $doctrineConfig->addCustomStringFunction('DATE_FORMAT', 'Common\UtilityBundle\Extensions\DateFormat');
        $doctrineConfig->addCustomStringFunction('FIND_IN_SET', 'Common\UtilityBundle\Extensions\FindInSet');
        $doctrineConfig->addCustomStringFunction('checkActiveContact', 'Common\UtilityBundle\Extensions\CheckActiveContact');
        $dateFormat = FgSettings::getMysqlDateTimeFormat();

        $qb2 = $this->getEntityManager()->createQueryBuilder();
        $qb2 = $qb2->select("GROUP_CONCAT(fgc.id SEPARATOR ', ')")
                        ->from('CommonUtilityBundle:FgClub', 'fgc')
                        ->leftJoin('CommonUtilityBundle:FgClubI18n', 'dcci18n', 'WITH', "dcci18n.id = fgc.id AND dcci18n.lang=:defaultLang")
                        ->where('FIND_IN_SET(fgc.id, cl.existingClubIds) != 0')->andWhere('fgc.clubType NOT IN (:clubTypes)');
        $qb3 = $this->getEntityManager()->createQueryBuilder();
                 $qb3->select("COALESCE(NULLIF(dci18n.titleLang,'') , fgcc.title)")
                        ->from('CommonUtilityBundle:FgClub', 'fgcc')
                        ->leftJoin('CommonUtilityBundle:FgClubI18n', 'dci18n', 'WITH', "dci18n.id = fgcc.id AND dci18n.lang=:defaultLang")
                        ->where('fgcc.id = cc.mainClub');
                 
        $clubDql = $this->getClubNameOfModifiedBy($defaultLang);
        $clubDecidedDql = $this->getClubNameOfDecidedBy($defaultLang);
        $moduleQuery = $this->getEntityManager()->createQueryBuilder();
        $columns = "cl.id as confirmId,COALESCE( NULLIF(ci18n.titleLang,'') , c.title) as newClub, cl.status, cc.isCompany, ms.gender, contactName(cl.modifiedBy) as modifiedBy,IDENTITY(cl.modifiedBy) as modifiedById,"
                ."(DATE_FORMAT(cl.decidedDate, '$dateFormat')) as decidedDate,(DATE_FORMAT(cl.decidedDate, '%Y-%m-%d %H:%i:%s')) as decidedDate1,contactName(cl.decidedBy) as decidedBy,checkActiveContact(cl.decidedBy, $clubId) as isActiveDecidedBy,"
                ."IDENTITY(cl.decidedBy) as decidedById,cl.status, (DATE_FORMAT(cl.modifiedDate, '$dateFormat')) as modifiedDate, (DATE_FORMAT(cl.modifiedDate, '%Y-%m-%d %H:%i:%s')) as modifiedDate1,"
                ." c.id,COALESCE(NULLIF(ci18n.titleLang,'') , c.title) as createdClubName,  contactName(cc.id) as contactName, cc.id as contactId,m.title as fedMembership, checkActiveContact(cc.id, $clubId) as isActiveContact , checkActiveContact(cl.modifiedBy, $clubId) as isActiveModifiedContact";

        $moduleQuery->select($columns)
                ->addSelect('('.$qb2->getDQL().') as existingClubs')
                ->addSelect('('.$clubDql->getDQL().') AS clubChangedBy ')
                ->addSelect('('.$clubDecidedDql->getDQL().') AS clubDecidedBy ')
                ->addSelect( '('.$qb3->getDQL().') AS  mainClub')
                ->from('CommonUtilityBundle:FgCmClubAssignmentConfirmationLog', 'cl')
                ->innerJoin('CommonUtilityBundle:FgClub', 'c', 'WITH', 'cl.club = c.id ')
                ->leftJoin('CommonUtilityBundle:FgClubI18n', 'ci18n', 'WITH', "ci18n.id = c.id AND ci18n.lang=:defaultLang")
                ->leftJoin('CommonUtilityBundle:FgCmContact', 'cc', 'WITH', 'cl.fedContact = cc.id ')
                ->innerJoin('CommonUtilityBundle:MasterSystem', 'ms', 'WITH', 'ms.fedContact = cl.fedContact')
                ->innerJoin('CommonUtilityBundle:FgCmMembership', 'm', 'WITH', 'cc.fedMembershipCat = m.id');

        if ($clubType == 'federation') {
            $moduleQuery->Where('cl.federationClub=:clubId');
        } elseif ($clubType == 'federation_club' || $clubType == 'sub_federation_club') {
            $moduleQuery->Where('cl.club=:clubId');
        }

        if ($tab == 'log') {
            $moduleQuery->andWhere('cl.status !=:status');
        } else {
            $moduleQuery->andWhere('cl.status =:status');
        }

        $moduleQuery->andWhere('cc.isDeleted=0')
                ->andWhere("cc.isFedMembershipConfirmed='0' OR (cc.isFedMembershipConfirmed='1' and cc.oldFedMembership IS NOT NULL)")
                ->setParameters(array('clubId' => $clubId, 'status' => 'PENDING', 'clubTypes' => array('federation', 'sub_federation'),'defaultLang' => $defaultLang));
        $result = $moduleQuery->getQuery()->getResult();
   
        if ($countFlag) {
            return count($result);
        } else {
            return $result;
        }
    }
    
    /**
     * Function to get the  club.
     * @param string $defaultLang  default club language
     *
     * @return Integer
     */
    private function getClubNameOfModifiedBy($defaultLang)
    {
        $moduleQuery = $this->getEntityManager()->createQueryBuilder();
        $moduleQuery->select("COALESCE(NULLIF(mci18n.titleLang,''), fc.title)")
                ->from('CommonUtilityBundle:FgCmContact', 'ct')
                ->innerJoin('CommonUtilityBundle:FgClub', 'fc', 'WITH', 'ct.mainClub = fc.id')
                ->leftJoin('CommonUtilityBundle:FgClubI18n', 'mci18n', 'WITH', "mci18n.id = fc.id AND mci18n.lang=:defaultLang")
                ->where('ct.id = cl.modifiedBy');
               

        return $moduleQuery;
    }
    /**
     * Function to get the  club.
     * @param string $defaultLang  default club language
     *
     * @return Integer
     */
    private function getClubNameOfDecidedBy($defaultLang)
    {
        $moduleQuery = $this->getEntityManager()->createQueryBuilder();
        $moduleQuery->select("COALESCE(NULLIF(dfci18n.titleLang,'') , fc1.title)")
                ->from('CommonUtilityBundle:FgCmContact', 'ct1')
                ->innerJoin('CommonUtilityBundle:FgClub', 'fc1', 'WITH', 'ct1.mainClub = fc1.id')
                ->leftJoin('CommonUtilityBundle:FgClubI18n', 'dfci18n', 'WITH', "dfci18n.id = fc1.id AND dfci18n.lang=:defaultLang")
                ->where('ct1.id = cl.decidedBy');
               
        return $moduleQuery;
    }
}
