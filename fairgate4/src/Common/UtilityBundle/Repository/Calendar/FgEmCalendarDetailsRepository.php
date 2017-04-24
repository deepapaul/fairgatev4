<?php

namespace Common\UtilityBundle\Repository\Calendar;

use Doctrine\ORM\EntityRepository;
use Internal\CalendarBundle\Util\CalendarRecurrence;
use Common\UtilityBundle\Entity\FgEmCalendarDetailsAttachments;
use Common\UtilityBundle\Entity\FgEmCalendar;
use Common\UtilityBundle\Entity\FgEmCalendarSelectedCategories;
use Common\UtilityBundle\Repository\Pdo\CalendarPdo;

/**
 * FgEmCalendarRepository
 *
 * @author pitsolutions
 */
class FgEmCalendarDetailsRepository extends EntityRepository {
    
     public function deleteAppointments($container,$deleteArray,$choice){
         
        $conn = $container->get('database_connection');
        $currDate = new \DateTime("now");
        foreach($deleteArray as $key => $event){ 
            
            $calendarObj = $this->find($event->eventDetailId);
            if($event->eventDetailType == 0 || ($event->eventDetailType == 1 && $event->isMasterRepeat == 1)){
                
                switch($choice){
                    case 'all_series':
                        $deleteObj = $this->_em->getRepository('CommonUtilityBundle:FgEmCalendar')->find($event->eventId);
                        if($deleteObj != ""){
                            $this->_em->remove($deleteObj);
                        }
                        break;
                    case 'instance':
                        $status  = $calendarObj->getStatus();
                        if($status == '1'){
                            $calendarObj ->setStatus('2')
                                         ->setUpdatedAt($currDate);
                            $this->_em->persist($calendarObj);
                            
                        }else{
                           
                            //clone event and change the startdate to next day
                            
                            $newObj = clone $calendarObj;
                            $newObj2 = clone $calendarObj;

                            //NEXT RECURRENCE  //$rule = 'FREQ=YEARLY;INTERVAL=2;BYMONTH=1,2,3;BYMONTHDAY=2,3';
                            $untilDate = ($calendarObj->getUntill()) ? $calendarObj->getUntill()->format('Y-m-d H:i:s') : null;
                            $calendarOccObj = new CalendarRecurrence($event->eventRules,$calendarObj->getStartDate()->format('Y-m-d H:i:s'), $calendarObj->getEndDate()->format('Y-m-d H:i:s'), $untilDate);// rule, startdate, enddate
                            $recurrences = $calendarOccObj->getNextRecurrence($event->startDate); //after
                            
                            $isFirstOccurence = $this->isFirstOccurence($calendarObj, $event->startDate);
                            $isLastOccurence = ($recurrences['recurrenceExist']) ? false : true;
                            
                            if(!$isFirstOccurence  && !$isLastOccurence) { //middle occurance                                                      
                                //insert2
                                $newObj2 ->setStartDate( new \DateTime($recurrences['recurrenceStartDate']))
                                         ->setEndDate( new \DateTime($recurrences['recurrenceEndDate']))
                                         ->setUpdatedAt($currDate);
                                $this->_em->persist($newObj2);
                                $this->_em->flush();

                                //insert to i18n table
                                $CalendarPdo = new CalendarPdo($container);
                                $CalendarPdo->insertToI18n($event->eventDetailId, $newObj2->getId());                            

                                $lastInserted = $conn->executeQuery("SELECT LAST_INSERT_ID() AS newId")->fetch();
                                $newId = $lastInserted['newId'];
                                $this->insertAttachments($event,$newId);
                                $this->insertCategory($event,$newId);
                                $this->insertSelectedAreas($event,$newId);
                            
                                //update until date in current eventdatil object
                                $this->setUntilDateForEventDetail($calendarObj, $event->startDate, $currDate); 
                            } else if($isLastOccurence) {                                      
                                //update until date in current eventdetail object
                                $this->setUntilDateForEventDetail($calendarObj, $event->startDate, $currDate);                            
                            } else if($isFirstOccurence) { //first occurence                                  
                                $calendarObj ->setStartDate( new \DateTime($recurrences['recurrenceStartDate']))
                                         ->setEndDate( new \DateTime($recurrences['recurrenceEndDate']))
                                         ->setUpdatedAt($currDate);
                            }
                            
                            //deleted entry
                            $time1 = new \DateTime($recurrences['recurrenceStartDate']);
                            $time1->modify("-1 sec");
                            //insert1
                            $newObj ->setStartDate(new \DateTime($event->startDate))
                                    ->setEndDate(new \DateTime($event->endDate))
                                    ->setUntill($time1)
                                    ->setStatus('2')
                                    ->setUpdatedAt($currDate);

                            $this->_em->persist($newObj);
                            
                            
                        }
                        break;
                    case 'all_following':
                        //update
                        $time1 = new \DateTime($event->startDate);
                        $time1->modify("-1 sec");
                        $calendarObj->setUntill($time1)
                                    ->setUpdatedAt($currDate);
                        
                        $qry = $this->createQueryBuilder('c')
                                ->select('c.id')
                                ->where('c.startDate > :startDate')
                                ->andWhere('c.calendar = :eventId')
                                ->andWhere('c.id !=  :eventDetailId')
                                ->andWhere('c.status != 2')
                                ->setParameter('startDate', $time1 )
                                ->setParameter('eventDetailId', $event->eventDetailId)
                                ->setParameter('eventId', $event->eventId);
                        $result = $qry->getQuery()->getResult();
                        
                        foreach($result as $key => $value){
                            $remainingObj = $this->find($value['id']);
                            $this->_em->remove($remainingObj);
                        }
                        
                        $this->_em->persist($calendarObj);
                        break;
                }
                $this->_em->flush();
                $this->_em->getRepository('CommonUtilityBundle:FgEmCalendar')->deleteCalendarWithNoDetail($event);
                
            }else{
                $eventObj = $calendarObj->getCalendar();
                if($eventObj != ""){
                    $this->_em->remove($eventObj);
                }
            }
        }
         $this->_em->flush();
         
    }        
    
    /**
     * insert category - instance
     * @param obj $event
     * @param int $newId new event detail
     * @return boolean
     */
    private function insertCategory($event,$newId){
        $eventDetailObj = $this->find($newId);
        $qry = $this->createQueryBuilder('c')
                    ->select('IDENTITY(sc.category) as category')
                    ->leftJoin('CommonUtilityBundle:FgEmCalendarSelectedCategories', 'sc', 'WITH', '(c.id = sc.calendarDetails)')
                    ->where('c.calendar = :eventId')
                    ->andWhere('c.id =  :eventDetailId')
                    ->setParameter('eventDetailId', $event->eventDetailId)
                    ->setParameter('eventId', $event->eventId);
        $result = $qry->getQuery()->getResult();

        foreach($result as $key => $value){
            if($value['category'] != ''){
                $categoryObj = new FgEmCalendarSelectedCategories();
                $category = $this->_em->getRepository('CommonUtilityBundle:FgEmCalendarCategory')->find($value['category']);
                $categoryObj    ->setCalendarDetails($eventDetailObj)
                                ->setCategory($category);
                $this->_em->persist($categoryObj);
            }
        }
        $this->_em->flush();
        
        return true;
    }
    
    /**
     * insert selected areas
     * @param obj $event eventdetails
     * @param obj $newId new event detail
     * @return boolean
     */
    private function insertSelectedAreas($event,$newId){
        $eventDetailObj = $this->find($newId);
        $qry = $this->createQueryBuilder('c')
                    ->select('sa.id as id')
                    ->leftJoin('CommonUtilityBundle:FgEmCalendarSelectedAreas', 'sa', 'WITH', '(c.id = sa.calendarDetails)')
                    ->where('c.calendar = :eventId')
                    ->andWhere('c.id =  :eventDetailId')
                    ->setParameter('eventDetailId', $event->eventDetailId)
                    ->setParameter('eventId', $event->eventId);
        $result = $qry->getQuery()->getResult();

        foreach($result as $key => $value){
            $areaObj = $this->_em->getRepository('CommonUtilityBundle:FgEmCalendarSelectedAreas')->find($value['id']);
            $areaObj1 = clone $areaObj;
            $areaObj1   ->setCalendarDetails($eventDetailObj);
            $this->_em->persist($areaObj1);
        }
        $this->_em->flush();
        
        return true;
    }
      /**
     * insert attachment - instance
     * @param obj $event eventdetails
     * @param int $newId new event detail
     * @return boolean
     */
    private function insertAttachments($event,$newId){
        
        $eventDetailObj = $this->find($newId);
        $qry = $this->createQueryBuilder('c')
                    ->select('IDENTITY(ca.fileManager) as fileManager')
                    ->leftJoin('CommonUtilityBundle:FgEmCalendarDetailsAttachments', 'ca', 'WITH', '(c.id = ca.calendarDetail)')
                    ->where('c.calendar = :eventId')
                    ->andWhere('c.id =  :eventDetailId')
                    ->setParameter('eventDetailId', $event->eventDetailId)
                    ->setParameter('eventId', $event->eventId);
        $result = $qry->getQuery()->getResult();

        foreach($result as $key => $value){
            if($value['fileManager'] != ''){
                $attachObj = new FgEmCalendarDetailsAttachments();
                $fileManager = $this->_em->getRepository('CommonUtilityBundle:FgFileManager')->find($value['fileManager']);
                $attachObj   ->setCalendarDetail($eventDetailObj)
                             ->setFileManager($fileManager);
                $this->_em->persist($attachObj);
            }
        }
        $this->_em->flush();
        
        return true;
    }
    
    /**
     * Function to get all the attachments for a particular event
     *
     * @param int $eventId  event id
     *
     * @return array
     */
    public function getCalendarDetailsAttachments($eventId)
    {
        $qry = $this->createQueryBuilder('a')
                ->select('fmv.filename , fmv.size,fm.encryptedFilename')
                ->innerJoin('CommonUtilityBundle:FgEmCalendarDetailsAttachments', 'at', 'WITH', 'at.calendarDetail = a.id')
                ->leftJoin('CommonUtilityBundle:FgFileManager', 'fm', 'WITH', '(fm.id = at.fileManager)')
                ->innerJoin("CommonUtilityBundle:FgFileManagerVersion", "fmv", "WITH", "( fm.latestVersion = fmv.id) ")
                ->where('(a.id = :eventId )')
                ->setParameter('eventId', $eventId);

        $result = $qry->getQuery()->getArrayResult();

        return $result;
    }
    
    /**
     * Method to get whether the event is the first occurence in the repeated series
     * 
     * @param object $calendarObj calendar eventDetail object
     * @param string $startDate   selected events startDate 
     * 
     * @return boolean 
     */
    private function isFirstOccurence($calendarObj, $startDate) {              
        $qry = $this->createQueryBuilder('CD')
                ->select('count(CD.id) AS cnt')
                ->where('(CD.calendar = :calendarId )')
                ->andWhere('CD.startDate < :startDate and CD.status != 2 ')
                ->setParameters(array('calendarId' => $calendarObj->getCalendar()->getId(), 'startDate' => $startDate));

        $results = $qry->getQuery()->getArrayResult();
        $return = ($results[0]['cnt'] == 0) ? true : false;
        
        return $return;
    }
        
    /**
     * Method update until date in current eventdatil object. set until date as -1 second of next start date
     * 
     * @param object $calendarObj calendat object
     * @param string $startDate   next start date
     * @param object $currDate    current date object
     */
    private function setUntilDateForEventDetail($calendarObj, $startDate, $currDate) { 
        $time2 = new \DateTime($startDate);
        $time2->modify("-1 sec");            
        if($time2 > $calendarObj->getStartDate()) {                            
            $calendarObj->setUntill($time2)
                        ->setUpdatedAt($currDate);
            $this->_em->persist($calendarObj);
        } else { //else delete that entry
            $this->_em->remove($calendarObj);
        }
    } 
}