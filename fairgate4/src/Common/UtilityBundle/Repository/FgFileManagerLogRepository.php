<?php

namespace Common\UtilityBundle\Repository;

use Common\UtilityBundle\Entity\FgFileManagerLog;
use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Util\FgSettings;

/**
 * FgFgFileManagerLogRepository
 *
 * @author pitsolutions
 */

class FgFileManagerLogRepository extends EntityRepository {
    
   /**
     * Function to get the log entries of a file
     *
     * @param int    $filemanagerId file manager Id
     * @param object $container  Container object
     *
     * @return array $result Array of log entries
     */
    public function getFileManagerLogData($filemanagerId, $container) {
        $doctrineConfig = $this->getEntityManager()->getConfiguration();
        $doctrineConfig->addCustomStringFunction('DATE_FORMAT', 'Common\UtilityBundle\Extensions\DateFormat');
        $doctrineConfig->addCustomStringFunction('contactName', 'Common\UtilityBundle\Extensions\FetchContactName');
        $dateFormat = FgSettings::getMysqlDateTimeFormat();

        $clubId = $container->get('club')->get('id');

        $resultQuery = $this->createQueryBuilder('c')
                ->select("Identity(c.fileManager) as fileManagerId,Identity(fm.club) as club,c.field,c.valueBefore, c.valueAfter,c.kind AS status,Identity(c.changedBy) AS changedBy,c.date AS dateOriginalold, DATE_FORMAT(c.date,'%Y-%m-%d %H:%i') AS dateOriginal,contactName(c.changedBy) AS editedBy ")
                ->leftJoin('CommonUtilityBundle:FgFileManager', 'fm', 'WITH', 'fm.id = c.fileManager')
                ->where('c.fileManager=:filemanagerId')
                ->andWhere('fm.club=:clubId')
                ->setParameters(array('filemanagerId' => $filemanagerId, 'clubId' => $clubId));
        $result = $resultQuery->getQuery()->getArrayResult();
        return $result;
    }
    /**
     * Method to enter delete/Restore log details 
     * 
     * @param  $filemanagerIds filemanager ids
     * 
     * @param $contactId contact id
     * 
     * @return 
     */
    public function logDetailEntry($filemanagerIds, $contactId, $type) {
        $fileIds = explode(",", $filemanagerIds);
        $contactObj = $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->find($contactId);
        $kindValue = ($type == 'delete') ? 'Flagged' : 'Reverted';
        $fieldValue = ($type == 'delete') ? 'File removed' : 'File restored';       
        foreach ($fileIds as $Id) {
            $fileObj = $this->_em->getRepository('CommonUtilityBundle:FgFileManager')->find($Id);
            $fileLogobj = new FgFileManagerLog();
            $fileLogobj->setFileManager($fileObj);
            $fileLogobj->setKind($kindValue);
            $fileLogobj->setField($fieldValue);
            $fileLogobj->setDate(new \DateTime("now"));
            $fileLogobj->setChangedBy($contactObj);
            $this->_em->persist($fileLogobj);
        }

        $this->_em->flush();

        return true;
    }

}
