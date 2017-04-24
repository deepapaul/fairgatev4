<?php

/**
 * FgCmChangeLogRepository
 *
 * This class is used for contact log in contact management.
 */

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Entity\FgCmChangeLog;
use Common\UtilityBundle\Util\FgSettings;
use Common\UtilityBundle\Util\FgLogHandler;

/**
 * FgCmChangeLogRepository
 *
 * This class is used for listing and inserting role log in role management.
 */
class FgCmChangeLogRepository extends EntityRepository {
    
    /**
     * Function to set the login log entries
     *
     * @param object $container Container object
     * @param int    $clubId    Club id
     * @param int    $contactId Contact Id
     * @param String $dateToday Date
     *
     * @return array $result Array of log entries
     */
    public function changeLogLogin($container, $clubId, $contactId, $dateToday) {
        $contactObj = $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->find($contactId);
        $loginCount = $contactObj->getLoginCount();
        $logValueArray[0] = array('kind' => "login", 'field' => "logged-in", 'value_before' => ($loginCount - 1), 'value_after' => $loginCount, 'changed_by' => $contactId, 'contact_id' => $contactId);
        $logHandlerObj = new FgLogHandler($container);
        $logHandlerObj->processLogEntryAction('contactlogin', 'fg_cm_change_log', $logValueArray);
    }

    /**
     * Function to set the Password log entries
     *
     * @param Object $container    container
     * @param int    $clubId       Club id
     * @param int    $contactId    Contact Id
     * @param String $dateToday    Date
     * @param String $passwordFlag Password flag
     *
     * @return array $result Array of log entries
     */
    public function passwordLogEntry($container, $clubId, $contactId, $dateToday, $passwordFlag) {
        $logValueArray[0] = array('kind' => "password", 'field' => "password reset", 'value_before' => '', 'value_after' => $passwordFlag,'changed_by'=>$contactId,'club_id'=> $clubId,'contact_id' => $contactId);
        $logHandlerObj = new FgLogHandler($container);
        $logHandlerObj->processLogEntryAction('contactlogin', 'fg_cm_change_log', $logValueArray);
    }

    /**
     * Function to get the own contact log entries of communication
     *
     * @param int $contact Contact id
     * @param int $clubId  Club id
     *
     * @return array $result Array of log entries
     */
    public function getOwncontactCommunicationLogEntries($contact, $clubId) {
        $dateFormat1 = FgSettings::getMysqlDateFormat();
        $dateFormat2 = FgSettings::getMysqlDateTimeFormat();

        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT cl.id,cl.contact_id,cl.date,cl.kind,cl.field,cl.date AS dateOriginal,date_format(cl.date,'" . $dateFormat2 . "') AS date,cl.value_before,cl.value_after,cl.changed_by as changedBy,contactName(cl.changed_by) as editedBy,cn.subject as type
                 FROM fg_cm_change_log cl
                 LEFT JOIN fg_cm_contact c ON cl.contact_id = c.id
                 LEFT JOIN fg_cn_newsletter cn ON cl.newsletter_id=cn.id
                 WHERE cl.contact_id= :contactId AND cl.kind='communication' AND cl.club_id=:clubId";
        $result = $conn->fetchAll($sql, array('contactId' => $contact, 'clubId' => $clubId));

        return $result;
    }   
            
   /**
     * function to delete the membership log
     *
     * @param int  $contactId    Contact id
     * @param int  $clubId       The club id
     */
     public function deleteContactMemebershipComplete($contactId, $clubId) {
        $conn = $this->getEntityManager()->getConnection();
        $sql_log = "DELETE FROM fg_cm_membership_log  WHERE contact_id =$contactId and club_id=$clubId ";
        $conn->executeQuery($sql_log);
        $sql_history = "DELETE FROM fg_cm_membership_history  WHERE contact_id =$contactId  and membership_club_id =$clubId ";
        $conn->executeQuery($sql_history);
      }
      /**
     * function to delete the membership log
     *
     * @param int  $contactId    Contact id
     * @param int  $clubId       The club id
     */
     public function deleteContactMemebership($contactId, $clubId,$membershipId) {
        $conn = $this->getEntityManager()->getConnection();
        $sql_log = "DELETE FROM fg_cm_membership_log  WHERE contact_id =$contactId and  club_id=$clubId and membership_id=$membershipId order by id desc limit 1 ";
        $conn->executeQuery($sql_log);
      }
    
    
    /**
     * Method to insert log entries when a contect is assigned as sponsor
     * @param array $contactsArray array of contect ids
     * @param int   $changedBy     logged in user id
     * @return null
     */
    public function inserLogOnAssigningSponsors($contactsArray, $changedBy) {
        foreach($contactsArray as $contactId) {
            $objLog = new \Common\UtilityBundle\Entity\FgCmChangeLog();
            $insertValues[] = "( $contactId, '$now', 'contact type', '', 'Sponsor', $changedBy )";
            $contactobj = $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->find($contactId);
            $changedByobj = $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->find($changedBy);
            $objLog->setContact($contactobj);
            $objLog->setDate(new \DateTime("now"));
            $objLog->setKind("contact type");
            $objLog->setValueBefore('');
            $objLog->setValueAfter('Sponsor');
            $objLog->setChangedBy($changedByobj);
            $objLog->setIsHistorical(0);
            $this->_em->persist($objLog);
            $this->_em->flush();
        }
    }

    /**
     * Function to get log of confirmed and discarded changes.
     *
     * @param int $clubId               Club id
     * @param string clubDefaultLang    The default language of the club
     *
     * @return array $resultArray Result array
     */
    public function getConfirmationLog($clubId, $clubDefaultLang)
    {
        // Configuring UDF.
        $doctrineConfig = $this->getEntityManager()->getConfiguration();
        $doctrineConfig->addCustomStringFunction('DATE_FORMAT', 'Common\UtilityBundle\Extensions\DateFormat');
        $doctrineConfig->addCustomStringFunction('contactName', 'Common\UtilityBundle\Extensions\FetchContactName');
        $doctrineConfig->addCustomStringFunction('checkActiveContact', 'Common\UtilityBundle\Extensions\CheckActiveContact');
        $clubDql = $this->getClubName($clubDefaultLang);
        $clubDecidedDql = $this->getClubNameDecidedBy($clubDefaultLang);
        //$datetimeFormat = FgSettings::getMysqlDateTimeFormat();
        $datetimeFormat = '%Y-%m-%d';
      
        $result = $this->createQueryBuilder('cl')
                ->select("cl.isConfirmed AS isConfirmed, DATE_FORMAT( cl.confirmedDate, '$datetimeFormat' ) AS decisionDate,checkActiveContact(cl.confirmedBy, :clubId) as activeContactDecided,IDENTITY(cl.confirmedBy) as confirmedBy, contactName(cl.confirmedBy) AS decidedBy, cl.attributeId AS attributeId, atr.inputType AS fieldType, "
                        . "DATE_FORMAT( cl.date, '$datetimeFormat' ) AS changeDate, contactName(cl.contact) AS contact, cl.field AS contactField, cl.valueBefore AS valueBefore, cl.valueAfter AS valueAfter, contactName(cl.changedBy) AS changedBy, checkActiveContact(cl.changedBy, :clubId) as activeContact,IDENTITY(cl.changedBy) as changedById")
                ->addSelect('('.$clubDql->getDQL().') AS clubChangedBy ')
                ->addSelect('('.$clubDecidedDql->getDQL().') AS clubDecidedBy ')
                ->innerJoin("CommonUtilityBundle:FgCmContact", "c", "WITH", "(c.fedContact = cl.contact AND c.club = :clubId )")
                ->leftJoin("CommonUtilityBundle:FgCmAttribute", "atr", "WITH", "atr.id = cl.attributeId")
                ->where('cl.isConfirmed = :isConfirmed1')
                ->orWhere('cl.isConfirmed = :isConfirmed2')
                ->andWhere("cl.kind = :kind")
                ->setParameters(array('isConfirmed1' => '0', 'isConfirmed2' => '1', 'clubId' => $clubId, 'kind' => 'data'))
                ->orderBy('cl.id','DESC')
                ->groupBy('cl.id, c.fedContact')
                ->getQuery()
                ->getResult();

        return $result;
    }
    
     /**
     * Function to get the  club.
     *
     * @param string clubDefaultLang    The default language of the club
     *  
     * @return Integer
     */
    private function getClubName($clubDefaultLang)
    {
        $moduleQuery = $this->getEntityManager()->createQueryBuilder();
        $moduleQuery->select("COALESCE(NULLIF(fci18n.titleLang,''),fc.title)")
                ->from('CommonUtilityBundle:FgCmContact', 'ct')
                ->innerJoin('CommonUtilityBundle:FgClub', 'fc', 'WITH', 'ct.mainClub = fc.id')
                ->leftJoin('CommonUtilityBundle:FgClubI18n', 'fci18n', 'WITH', "fci18n.id = fc.id AND fci18n.lang = '$clubDefaultLang'")
                ->where('ct.id = cl.changedBy');
        return $moduleQuery;
    }
    /**
     * Function to get the  club.
     *
     * @param string clubDefaultLang    The default language of the club
     *  
     * @return Integer
     */
    private function getClubNameDecidedBy($clubDefaultLang)
    {
        $moduleQuery = $this->getEntityManager()->createQueryBuilder();
        $moduleQuery->select("COALESCE(NULLIF(fci18n1.titleLang,''),fc1.title)")
                ->from('CommonUtilityBundle:FgCmContact', 'ct1')
                ->innerJoin('CommonUtilityBundle:FgClub', 'fc1', 'WITH', 'ct1.mainClub = fc1.id')
                ->leftJoin('CommonUtilityBundle:FgClubI18n', 'fci18n1', 'WITH', "fci18n1.id = fc1.id AND fci18n1.lang = '$clubDefaultLang'")
                ->where('ct1.id = cl.confirmedBy');

        return $moduleQuery;
    }
    /**
     * Function to insert log entry.
     *
     * @param array $dataArray    Data array
     * @param int   $currContact  Current contact id
     * @param bool  $doExec       Whether to execute query or not
     * @param bool  $returnObject Whether to return object or not
     *
     * @return FgCmChangeLog $objLog Change log object
     */
    public function insertLogEntry($dataArray, $currContact, $doExec = true, $returnObject = false)
    {
        $contactObj = $this->_em->getReference('CommonUtilityBundle:FgCmContact', $dataArray['contactId']);
        $currContactObj = $this->_em->getReference('CommonUtilityBundle:FgCmContact', $currContact);
        $objLog = new \Common\UtilityBundle\Entity\FgCmChangeLog();
        $objLog->setContact($contactObj);
        if (isset($dataArray['club_id'])) {
            $clubObj = $this->_em->getReference('CommonUtilityBundle:FgClub', $dataArray['club_id']);
            $objLog->setClub($clubObj);
        }
        $objLog->setKind($dataArray['kind']);
        $objLog->setField($dataArray['field']);
        $objLog->setValueBefore($dataArray['value_before']);
        $objLog->setValueAfter($dataArray['value_after']);
        $objLog->setIsHistorical(0);
        if (isset($dataArray['is_confirmed'])) {
            $objLog->setDate(new \DateTime($dataArray['changed_date']));
            $changedByObj = $this->_em->getReference('CommonUtilityBundle:FgCmContact', $dataArray['changed_by']);
            $objLog->setChangedBy($changedByObj);
            $objLog->setIsConfirmed($dataArray['is_confirmed']);
            $objLog->setConfirmedBy($currContactObj);
            $objLog->setConfirmedDate(new \DateTime("now"));
        } else {
            $objLog->setDate(new \DateTime("now"));
            $objLog->setChangedBy($currContactObj);
        }
        if (isset($dataArray['attribute_id'])) {
            $objLog->setAttributeId($dataArray['attribute_id']);
        }
        if ($returnObject) {
            return $objLog;
        }
        $this->_em->persist($objLog);
        if ($doExec) {
            $this->_em->flush();
        }
    }
    

}
