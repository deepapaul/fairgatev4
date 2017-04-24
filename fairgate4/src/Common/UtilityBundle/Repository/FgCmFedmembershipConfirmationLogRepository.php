<?php

/**
 * FgCmFedmembershipConfirmationLogRepository.
 *
 * This class is used for creating, confirmaing, discarding applications for fed membership
 * assignment of a contact.
 *
 * @author     pitsolutions.ch
 *
 * @version    Fairgate V4
 */
namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Entity\FgCmFedmembershipConfirmationLog;
use Common\UtilityBundle\Util\FgSettings;

/**
 * FgCmFedmembershipConfirmationLogRepository.
 */
class FgCmFedmembershipConfirmationLogRepository extends EntityRepository
{

    /**
     * This function is used to create an application for federation membership confirmation.
     *
     * @param int $clubId          Club id
     * @param int $contactId       Contact id
     * @param int $fedClubId       Federation club id
     * @param int $modifiedBy      Modified by
     * @param int $oldMembershipId Old membership id
     * @param int $newMembershipId New membership id
     * @param int $isMerging       Whether contact is to be merged or not
     */
    public function createApplicationForConfirmation($clubId, $contactId, $fedClubId, $modifiedBy, $oldMembershipId, $newMembershipId, $isMerging = 0)
    {
        $clubObj = $this->_em->getReference('CommonUtilityBundle:FgClub', $clubId);
        $contactObj = $this->_em->getReference('CommonUtilityBundle:FgCmContact', $contactId);
        $fedClubObj = $this->_em->getReference('CommonUtilityBundle:FgClub', $fedClubId);
        $modifiedByObj = $this->_em->getReference('CommonUtilityBundle:FgCmContact', $modifiedBy);
        $newMembershipObj = $this->_em->getReference('CommonUtilityBundle:FgCmMembership', $newMembershipId);
        $sharedClubIds = $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->getSharedClubsOfAContact($contactId);
        $existingClubIds = ($sharedClubIds != '') ? $sharedClubIds : $clubId;

        $confirmationObj = new FgCmFedmembershipConfirmationLog();
        $confirmationObj->setContact($contactObj);
        $confirmationObj->setClub($clubObj);
        $confirmationObj->setFederationClub($fedClubObj);
        $confirmationObj->setExistingClubIds($existingClubIds);
        $confirmationObj->setModifiedDate(new \DateTime('now'));
        $confirmationObj->setModifiedBy($modifiedByObj);
        if ($oldMembershipId != null && $oldMembershipId != '') {
            $oldMembershipObj = $this->_em->getReference('CommonUtilityBundle:FgCmMembership', $oldMembershipId);
            $confirmationObj->setFedmembershipValueBefore($oldMembershipObj);
        }
        $confirmationObj->setFedmembershipValueAfter($newMembershipObj);
        $confirmationObj->setStatus('PENDING');
        $confirmationObj->setIsMerging($isMerging);
        $this->_em->persist($confirmationObj);
        $this->_em->flush();
    }

    /**
     * Function to list application log.
     *
     * @param int    $fedClubId          ClubId
     * @param string $clubType           Club type
     * @param int    $currentClub        Current club
     * @param type   $isMergeApplication Merge application flag 0 or 1
     * @param type   $defaultLang        Club default language
     *
     * @return array $result Apllication log details
     */
    public function getApplicationConfirmationLog($fedClubId, $clubType, $currentClub, $isMergeApplication = 0, $defaultLang)
    {
        $doctrineConfig = $this->getEntityManager()->getConfiguration();
        $doctrineConfig->addCustomStringFunction('contactName', 'Common\UtilityBundle\Extensions\FetchContactName');
        $doctrineConfig->addCustomStringFunction('DATE_FORMAT', 'Common\UtilityBundle\Extensions\DateFormat');
        $doctrineConfig->addCustomStringFunction('checkActiveContact', 'Common\UtilityBundle\Extensions\CheckActiveContact');
        $dateFormat = FgSettings::getMysqlDateTimeFormat();
        $existingClubQuery = $this->getEntityManager()->createQueryBuilder();
        $existingClubQuery = $existingClubQuery->select("GROUP_CONCAT(fgc.id SEPARATOR ', ')")
                ->from('CommonUtilityBundle:FgClub', 'fgc')
                ->innerJoin('CommonUtilityBundle:FgClubI18n', 'fgcI8n', 'WITH', 'fgc.id = fgcI8n.id AND fgcI8n.lang =:defaultLang ')
                ->where('FIND_IN_SET(fgc.id, cl.existingClubIds) != 0')
                ->andWhere('fgc.clubType NOT IN (:clubTypes)');
       
        
        $clubDql = $this->getClubNameDQL($defaultLang);
        
        $clubDqlQuery = $this->getEntityManager()->createQueryBuilder();
        $clubDqlQuery->select("COALESCE(NULLIF(C1i18N.titleLang,''),f1c.title)")
            ->from('CommonUtilityBundle:FgCmContact', 'c1t')
            ->innerJoin('CommonUtilityBundle:FgClub', 'f1c', 'WITH', 'c1t.mainClub = f1c.id')
            ->leftJoin('CommonUtilityBundle:FgClubI18n', 'C1i18N', 'WITH', "C1i18N.id = f1c.id AND C1i18N.lang = '$defaultLang'")
            ->where('c1t.id = d.id');
        
        $moduleQuery = $this->getEntityManager()->createQueryBuilder();
        $moduleQuery->select("fm.id as fedCategoryId, fm.title as valueAfter, MS.gender, IDENTITY(cc.clubMembershipCat) as clubMembershipCat, cc.isCompany, fmb.title as valueBefore,fm.id as valueAfterId, fmb.id as valueBeforeId, (DATE_FORMAT(cl.modifiedDate, '%Y-%m-%d %H:%i:%s') as modifiedDate, c.id,COALESCE(NULLIF(ci18n.titleLang, ''), c.title)  as createdClubName, contactName(m.id) as modifiedBy,m.id as modifiedById, contactName(cc.id) as contactName, cc.id as contactId, cl.status, contactName(d.id) as decidedBy, d.id as decidedById, (DATE_FORMAT(cl.decidedDate, '%Y-%m-%d %H:%i:%s')) as decidedDate, checkActiveContact(cc.id, $currentClub) as isActiveContact, checkActiveContact(m.id, $currentClub) as isActiveModifiedContact, checkActiveContact(d.id, $currentClub) as isActiveDecidedContact, COALESCE(NULLIF(ci18n.titleLang, ''), c.title) as club")
            ->addSelect('(' . $existingClubQuery->getDQL() . ') as existingClubs')
            ->addSelect('(' . $clubDql->getDQL() . ') AS clubChangedBy ')
            ->addSelect('(' . $clubDqlQuery->getDQL() . ') AS clubDecidedBy ')
            ->addSelect('(select fgcc.title from CommonUtilityBundle:FgClub fgcc where fgcc.id = cc.mainClub ) as mainClub')
            ->from('CommonUtilityBundle:FgCmFedmembershipConfirmationLog', 'cl')
            ->innerJoin('CommonUtilityBundle:MasterSystem', 'MS', 'WITH', 'MS.fedContact = cl.contact')
            ->leftJoin('cl.club', 'c')
            ->leftJoin('CommonUtilityBundle:FgClubI18n', 'ci18n', 'WITH', 'c.id = ci18n.id AND ci18n.lang =:defaultLang')    
            ->leftJoin('cl.contact', 'cc')
            ->leftJoin('cl.decidedBy', 'd')
            ->leftJoin('cl.fedmembershipValueAfter', 'fm')
            ->leftJoin('cl.fedmembershipValueBefore', 'fmb')
            ->leftJoin('cl.modifiedBy', 'm')
            ->Where('cl.federationClub=:clubId')
            ->andWhere("cl.status != 'PENDING'")
            ->andWhere('cl.isMerging='.$isMergeApplication)
            ->setParameter('defaultLang', $defaultLang);    
                ;
        if ($clubType != 'federation') {
            $moduleQuery->andWhere('cl.club=:currentClub')
                ->setParameter('currentClub', $currentClub);
        }
        $moduleQuery->setParameter('clubId', $fedClubId);
        $moduleQuery->setParameter('clubTypes', array('federation', 'sub_federation'));
        $result = $moduleQuery->getQuery()->getResult();

        return $result;
    }

    /**
     * Function to get the  club.
     *
     * @return Integer
     */
    private function getClubNameDQL($lang)
    {
        $moduleQuery = $this->getEntityManager()->createQueryBuilder();
        $moduleQuery->select("COALESCE(NULLIF(Ci18N.titleLang,''),fc.title) AS title")
            ->from('CommonUtilityBundle:FgCmContact', 'ct')
            ->innerJoin('CommonUtilityBundle:FgClub', 'fc', 'WITH', 'ct.mainClub = fc.id')
            ->leftJoin('CommonUtilityBundle:FgClubI18n', 'Ci18N', 'WITH', "Ci18N.id = fc.id AND Ci18N.lang = '$lang'")
            ->where('ct.id = m.id');

        return $moduleQuery;
    }

    /**
     * This function is used to get the id of a pending application of a contact in a club.
     *
     * @param int $contactId Contact id
     * @param int $clubId    Club id
     *
     * @return int $confirmId FgCmFedmembershipConfirmationLog id
     */
    public function getPendingApplications($contactId, $clubId)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('cl.id')
            ->from('CommonUtilityBundle:FgCmFedmembershipConfirmationLog', 'cl')
            ->where('cl.club = :clubId')
            ->andWhere('cl.contact = :contactId')
            ->andWhere('cl.status = :status')
            ->setParameters(array('clubId' => $clubId, 'contactId' => $contactId, 'status' => 'PENDING'));
        $result = $qb->getQuery()->getResult();
        $confirmId = (count($result) > 0) ? $result[0]['id'] : 0;

        return $confirmId;
    }
}
