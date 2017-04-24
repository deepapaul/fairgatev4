<?php

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Util\FgSettings;
use Common\UtilityBundle\Util\FgUtility;

/**
 * FgCmNotesRepository
 *
 * @author shyamgopal.cs <shyamgopal.cs@pitsolutions.com>
 */
class FgCmNotesRepository extends EntityRepository {

    /**
     * Function to get notes details
     *
     * @param int $contactid contact id
     * @param int $offset    offset
     * @param int $limit     limit
     * @param int $clubId    club id
     *
     * @return array
     */
    public function getNotesDetails($contactid, $offset, $limit, $clubId) {
        $conn = $this->getEntityManager()->getConnection();
        $dateFormat = FgSettings::getMysqlDateFormat();
        $timeFormat = FgSettings::getMysqlTimeFormat();
        $datetimeFormat = FgSettings::getMysqlDateTimeFormat();
        $sql = "SELECT d.id,d.date as fullDate, d.contact_id, date_format( date,'$dateFormat' ) AS ctdate, TIME_FORMAT( date, '$timeFormat' ) AS cttime, date_format( edited_on,'$dateFormat' ) AS edate, TIME_FORMAT( edited_on, '$timeFormat' ) AS etime, note,
                contactNameNoSort(d.created_by, 0) as createdname  ,
                contactNameNoSort(d.edited_by, 0) as editedname
                FROM fg_cm_notes as d where d.contact_id=:contactid AND d.club_id=:clubId order by date desc,d.id desc LIMIT $offset,$limit";
        $result = $conn->fetchAll($sql, array('contactid' => $contactid, 'clubId' => $clubId));

        return $result;
    }

    /**
     * Function to get total notes count
     *
     * @param int $contactid contact id
     * @param int $clubId    club id
     *
     * @return type
     */
    public function getNotesCount($contactid, $clubId) {

        $qry = $this->createQueryBuilder('n')
                ->select('COUNT(n.id) AS cnt')
                ->where('n.club=:clubId')
                ->andWhere('n.contact=:contactId')
                ->setParameter('clubId', $clubId)
                ->setParameter('contactId', $contactid);
        $result = $qry->getQuery()->getResult();

        return $result[0]['cnt'];
    }

    /**
     * Function to get contact name
     *
     * @param type $contactid
     *
     * @return type
     */
    public function getContactname($contactid) {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT  contactNameNoSort($contactid, 0) AS contactName ";
        $result = $conn->fetchAll($sql);

        return $result[0]['contactName'];
    }

    /**
     * function to get notes details
     *
     * @param type $clubId
     *
     * @return type
     */
    public function getDeatils($clubId) {

        $result = $this->createQueryBuilder('n')
                ->select('n.id')
                ->where('n.club=:clubId')
                ->setParameter('clubId', $clubId);
        $dataResult = $result->getQuery()->getResult();

        return $dataResult;
    }

    /**
     * Function to get all Notes
     *
     * @param int $clubId    Club id
     * @param int $contactId Contact id
     *
     * @return array $resultArray
     */
    public function getAllNotes($clubId, $contactId) {
        $conn = $this->getEntityManager()->getConnection();
        $datetimeFormat = FgSettings::getMysqlDateTimeFormat();
        
        $sql = "SELECT n.id,n.date as fullDate,date_format( n.date,'$datetimeFormat' ) AS date,n.note,c.id as contactId
                FROM fg_cm_notes as n
                LEFT JOIN fg_cm_contact c on c.id=n.created_by
                WHERE n.contact_id =:contactId
                AND n.club_id = :clubId
                ORDER BY n.id desc
                LIMIT 0,3";
        $result = $conn->fetchAll($sql, array('contactId' => $contactId, 'clubId' => $clubId));

        return $result;
    }

    /**
     * Function to save contact notes-both contact and sponsor
     *
     * @param int   $contactid    contact id
     * @param int   $clubId       current club id
     * @param array $notesData    notes details array
     * @param int   $loginContact logged in contact id
     *
     * @return null
     */
    public function saveNotes($contactid, $clubId, $notesData, $loginContact, $container) {
        $clubobj = $this->_em->getRepository('CommonUtilityBundle:FgClub')->find($clubId);
        $contactobj = $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->find($contactid);
        $loginContactobj = $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->find($loginContact);
        $conn = $container->get('database_connection');
        $count = 0;
        $log = array();
        foreach ($notesData as $id => $data) {
            $now = date("Y-m-d H:i:s");
            $notes = $this->find($id);
            if (is_array($data)) {

                if (array_key_exists('isDeleted', $data)) {
                    $valueBefore = FgUtility::getSecuredDataString($notes->getNote(), $conn);
                    $this->deleteCategory($notes);
                    $log[$count++] = array('note_contact_id' => $contactid,'assigned_club_id' => $clubId,'type' => 'contact','value_before' => $valueBefore,'value_after' => '-');
                    continue;
                }
            } else {
                if (empty($notes)) {
                    $notes = new \Common\UtilityBundle\Entity\FgCmNotes();
                    $notes->setNote($data);
                    $notes->setDate(new \DateTime("now"));
                    $notes->setContact($contactobj);
                    $notes->setCreatedBy($loginContactobj);
                    $notes->setEditedBy($loginContact);
                    $notes->setClub($clubobj);
                    $valueBefore = FgUtility::getSecuredDataString($notes->getNote(), $conn);
                    $log[$count++] = array('note_contact_id' => $contactid,'assigned_club_id' => $clubId,'type' => 'contact','value_before' => '-','value_after' => $data);
                } else {
                    $valueBefore = FgUtility::getSecuredDataString($notes->getNote(), $conn);
                    $notes->setNote($data);
                    $notes->setContact($contactobj);
                    $notes->setEditedOn(new \DateTime("now"));
                    $notes->setEditedBy($loginContact);
                    $notes->setClub($clubobj);

                    $data = FgUtility::getSecuredDataString($data, $conn);
                    $log[$count++] = array('note_contact_id' => $contactid,'assigned_club_id' => $clubId,'type' => 'contact','value_before' => $valueBefore,'value_after' => $data);
                }
            }
            $this->_em->persist($notes);
            $this->_em->flush();
        }
        //Update ladt updated in fg_cm_contact
        $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->updateLastUpdated($contactid, 'id');
        $this->_em->getRepository('CommonUtilityBundle:FgClubNotes')->logEntry($log, 'contact', $container);
    }

    /**
     * Function to delete a particular contact and sponsor notes
     *
     * @param object $notesobj notes object
     *
     * @return null
     */
    public function deleteCategory($notesobj) {
        $this->_em->remove($notesobj);
        $this->_em->flush();
    }

}
?>

