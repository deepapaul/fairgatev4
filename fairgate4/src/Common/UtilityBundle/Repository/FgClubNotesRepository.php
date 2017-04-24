<?php

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Entity\FgClubNotes;
use Common\UtilityBundle\Util\FgSettings;
use Common\UtilityBundle\Util\FgLogHandler;

/**
 * FgClubNotesRepository
 */
class FgClubNotesRepository extends EntityRepository {

    /**
     * Function to get total club notes count
     *
     * @param type $clubid        Club id
     * @param type $createdclubid created club id
     *
     * @return type
     */
    public function getNotesCount($clubid, $createdclubid) {

        $qry = $this->createQueryBuilder('n')
                ->select('COUNT(n.id) AS cnt')
                ->where('n.club=:clubId')
                ->andWhere('n.createdByClub=:createdClub')
                ->setParameter('clubId', $clubid)
                ->setParameter('createdClub', $createdclubid);
        $result = $qry->getQuery()->getResult();

        return $result[0]['cnt'];
    }

    /**
     * Function to get clubnotes details
     *
     * @param int $offset        offset
     * @param int $limit         limit
     * @param int $clubid        clubId
     * @param int $createdclubid crated club id
     *
     * @return array
     */
    public function getNotesDetails($offset, $limit, $clubid, $createdclubid) {
        // Configuring UDF.
        $doctrineConfig = $this->getEntityManager()->getConfiguration();
        $doctrineConfig->addCustomStringFunction('DATE_FORMAT', 'Common\UtilityBundle\Extensions\DateFormat');
        $doctrineConfig->addCustomStringFunction('contactNameNoSort', 'Common\UtilityBundle\Extensions\FetchContactNameNoSort');
        
        $dateFormat = FgSettings::getMysqlDateFormat();
        $timeFormat = FgSettings::getMysqlTimeFormat();
        $datetimeFormat = FgSettings::getMysqlDateTimeFormat();
        //
        $qry = $this->createQueryBuilder('d')
                ->select("d.id, d.date as fullDate, IDENTITY(d.club), DATE_FORMAT( d.date,'$dateFormat' ) AS ctdate, DATE_FORMAT( d.date, '$timeFormat' ) AS cttime, DATE_FORMAT( d.editedOn,'$dateFormat' ) AS edate, DATE_FORMAT( d.editedOn, '$timeFormat' ) AS etime, d.note,contactNameNoSort(d.createdByContact 0) as createdname,
                contactNameNoSort(d.editedByContact 0) as editedname")
                ->where('d.club=:clubId')
                ->andWhere('d.createdByClub=:createdClub')
                ->orderBy('d.date', 'desc')
                ->setParameter('clubId', $clubid)
                ->setParameter('createdClub', $createdclubid)
                ->setFirstResult($offset)
                ->setMaxResults($limit);
        $result = $qry->getQuery()->getResult();

        return $result;
    }

    /**
     * Function to delete note
     *
     * @param object $notesObj
     *
     * @return boolean
     */
    public function deleteNote($notesObj) {
        $this->_em->remove($notesObj);
    }

    /**
     * Function to add new note
     *
     * @param string $item               note
     * @param object $clubobj            club object
     * @param int    $fgcreatedbycontact created contact id
     * @param int    $fgcreatedbyclub    Created club id
     */
    public function addNewNote($item, $clubobj, $fgcreatedbycontact, $fgcreatedbyclub) {
        $clubnotes = new FgClubNotes();
        $clubnotes->setNote($item);
        $clubnotes->setDate(new \DateTime("now"));
        $clubnotes->setClub($clubobj);
        $clubnotes->setCreatedByContact($fgcreatedbycontact);
        $clubnotes->setCreatedByClub($fgcreatedbyclub);
        $clubnotes->setEditedByContact(null);
        $clubnotes->setEditedByClub(null);
        $this->_em->persist($clubnotes);
    }

    /**
     * Function to update club note
     *
     * @param object $notesObj         Note object
     * @param string $item             Note value
     * @param object $clubobj          Club object
     * @param int    $fgcreatedcontact Created contact id
     * @param int    $loginclubobj     Created club id
     */
    public function updateClubNote($notesObj, $item, $clubobj, $fgcreatedcontact, $loginclubobj) {
        $notesObj->setNote($item);
        $notesObj->setEditedOn(new \DateTime("now"));
        $notesObj->setEditedByContact($fgcreatedcontact);
        $notesObj->setEditedByClub($loginclubobj);
        $this->_em->persist($notesObj);
    }

    

    /**
     * Function to make log entry
     *
     * @param array  $log  logarray
     * @param string $from club/contact
     */
    public function logEntry($log, $from, $container) {

        if (!empty($log)) {
            $logHandle = new FgLogHandler($container);
            $logHandle->processLogEntryAction('contactOverview_notes', 'fg_club_log_notes', $log);
        }
    }
}
