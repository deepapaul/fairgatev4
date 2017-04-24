<?php

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Entity\FgSmBookings;
use Common\UtilityBundle\Repository\Pdo\SponsorPdo;
use Common\UtilityBundle\Util\FgSettings;

/**
 * FgSmBookingsRepository
 *
 * This class is used for handling sponsor bookings in Sponsors Administration.
 *
 * @author pitsolutions.ch
 */
class FgSmBookingsRepository extends EntityRepository
{

    /**
     * Method to check whetehr a contact is a prospect
     * @param string $contactId - $contactId
     * @param string $masterTable - master_club{clubid}/master_federation{clubid}
     * @param object $container       Container Object
     *
     * @return  boolean
     */
    public function isProspect($contactId, $masterTable, $container)
    {
        $pdoClass = new SponsorPdo($container);
        $isProspect = $pdoClass->isProspect($contactId, $masterTable, $container->get('club'));

        return $isProspect;
    }

    /**
     * Function to get booking details.
     *
     * @param int $bookingId Booking Id
     *
     * @return array $result Resulting data array of services.
     */
    public function getbookigDetailsById($bookingId,$clubId=false)
    {   
        $paramArray=array(':bookingId' => $bookingId);
        $resData = $this->createQueryBuilder('b')
                ->select("b.id AS bookingId,IDENTITY(b.category) as categoryId,IDENTITY(b.service) as serviceId,IDENTITY(b.contact) as contactId,b.beginDate,b.endDate,b.paymentPlan,b.firstPaymentDate,b.lastPaymentDate,b.repetitionMonths,b.amount,b.discountType,b.discount")
                ->where('b.id=:bookingId');
        if($clubId != false){
            $resData->andWhere('IDENTITY(b.club)=:clubId');
            $paramArray[':clubId']=$clubId;
        }
        $resData->setParameters($paramArray);
        
        $resData = $resData->getQuery()
                ->getArrayResult();

        return $resData;
    }

    /**
     * Function to get payment plans of a booking.
     *
     * @param int $bookingId Booking Id
     *
     * @return array $result Resulting data array of services.
     */
    public function getPaymentPlansOfBooking($bookingId)
    {
        $resData = $this->createQueryBuilder('b')
                ->select("b.id AS bookingId,pm.id as paymentId,pm.date as paymentDate,pm.amount,pm.discountType,pm.discount")
                ->join('CommonUtilityBundle:FgSmPaymentplans', 'pm', 'WITH', 'pm.booking = b.id')
                ->where('pm.booking=:bookingId')
                ->setParameters(array('bookingId' => $bookingId))
                ->getQuery()
                ->getArrayResult();

        return $resData;
    }

    /**
     * Method to get count of sponser services
     * @param int $clubId           current club Id
     * @param int $contact          contactId
     * @param int $isArchiveSponsor is archive sponsor Flag
     *
     * @return int
     */
    public function getCountOfSponsorServices($clubId, $contact, $isArchiveSponsor= '')
    {
        if($isArchiveSponsor){
            $dateCondition = " (B.endDate < :now)";
        } else {
            $dateCondition = " (B.endDate > :now OR B.endDate IS NULL OR B.endDate = '')";
        }
        $resultQuery = $this->createQueryBuilder('B')
                ->select('COUNT(S.id) as serviceCount')
                ->innerJoin("CommonUtilityBundle:FgSmServices", "S", "WITH", "B.service = S.id AND B.isDeleted = 0")
                ->where("B.contact = :contact AND $dateCondition")
                ->andWhere('B.club = :clubId');
        $resultQuery->setParameters(array('contact' => $contact, 'clubId' => $clubId,'now'=>new \DateTime('now')));
        $results = $resultQuery->getQuery()->getArrayResult();

        return $results[0]['serviceCount'];
    }

    /**
     * Function to save assignment booking
     *
     * @param type $bookingId  booking id
     * @param type $contactIds contact ids for assignment
     * @param type $club       Club object
     * @param type $dataArray  Form data array
     * @throws \Common\UtilityBundle\Repository\Exception
     */
    public function saveBooking($bookingId, $contactIds, $club, $dataArray)
    {
        $conn = $this->getEntityManager()->getConnection();
        $clubId = $club->get('id');
        $contactId = $club->get('contactId');
        $currentId = time();
        $serviceId = isset($dataArray['service_id']) ? $dataArray['service_id'] : 'psb.service_id';
        $condition = ($bookingId) ? "psb.id=$bookingId" : "psb.timestamp=$currentId";
        $paymentValues = $smBookingQueryDup = $smBookingFields = $smBookingValues = array();
        $logQuery = $depositedFields = $depositedValues = '';
        $paymentModified=false;
        try {
            $conn->beginTransaction();
            //itrate over form elements
            foreach ($dataArray as $key => $value) {
                if($bookingId && in_array($key, array('begin_date','end_date', 'first_payment_date', 'last_payment_date','repetition_months','discount','discount_type'))) {
                    $paymentModified=true;
                }
                if(in_array($key, array('discount','amount'))){
                    $value = str_replace(FgSettings::getDecimalMarker(), '.', $value);
                }
                //set contact deposited with
                if ($key == 'depositedWithSelection') {
                    if (empty($value)) {
                        continue;
                    }
                    $valueArray = json_decode($value);
                    if (count($valueArray) == 0) {
                        $this->getEntityManager()->getRepository('CommonUtilityBundle:FgSmBookingDeposited')->deleteDepositedOfBooking($conn,$bookingId);
                        continue;
                    }
                    $depositedFields = "fg_sm_booking_deposited.booking_id,fg_sm_booking_deposited.contact_id";
                    $depositedValues = "SELECT psb.id,C.id FROM fg_sm_bookings psb,fg_cm_contact C WHERE $condition AND C.id in(" . implode(',', $valueArray) . ")"; //"$value";
                    continue;
                } elseif ($key == 'depositedWith') { //role deposited with updation
                    if(count($value)==0 || empty($value)){
                        $this->getEntityManager()->getRepository('CommonUtilityBundle:FgSmBookingDeposited')->deleteDepositedOfBooking($conn,$bookingId);
                        continue;
                    }
                    $depositedFields="fg_sm_booking_deposited.booking_id,fg_sm_booking_deposited.role_id";
                    $depositedValues="SELECT psb.id,R.id FROM fg_sm_bookings psb,fg_rm_role R WHERE $condition AND R.id in('".implode("','",$value)."')";
                    continue;
                } elseif ($key == 'payment_plan') { //payment plan switching
                    if ($value == 'regular' && !$bookingId) {
                        $this->getEntityManager()->getRepository('CommonUtilityBundle:FgSmBookings')->setQueryForRegularType($dataArray,$club,$condition,$paymentValues);
                    } else if($bookingId && $value == 'custom'){
                        $conn->executeQuery("DELETE FROM fg_sm_paymentplans WHERE booking_id=$bookingId");
                    }
                } elseif ($key == 'custom') {
                    $this->getEntityManager()->getRepository('CommonUtilityBundle:FgSmBookings')->setQueryForCustomType($conn,$value,$paymentValues,$condition);
                    continue;
                }
                $this->getEntityManager()->getRepository('CommonUtilityBundle:FgSmBookings')->setDateFieldForBooking($key,$value);
                if ($key === 'discount_type') {
                    $value = ($dataArray['discount'] > 0) ? $dataArray['discount_type'] : 'N';
                }
                $smBookingFields[] = "`$key`";
                $smBookingValues[] = $value=='NULL' ? "NULL" :"'$value'";
                $smBookingQueryDup[] = "`$key` = VALUES(`$key`)";
            }

            //insert into booking table
            if (count($smBookingFields)) {
                $this->getEntityManager()->getRepository('CommonUtilityBundle:FgSmBookings')->setBookingDefaultValues($bookingId, $contactId, $clubId, $currentId, $smBookingFields, $smBookingValues, $smBookingQueryDup);
                $smBookingQuery = "INSERT INTO fg_sm_bookings (" . implode(',', $smBookingFields) . ") (SELECT " . implode(',', $smBookingValues) . " FROM fg_cm_contact C where C.id in($contactIds) ) ON DUPLICATE KEY UPDATE " . implode(',', $smBookingQueryDup);
                $conn->executeQuery($smBookingQuery);
            }
            //update deposited with
            if ($depositedFields != '') {
                $conn->executeQuery("DELETE FROM fg_sm_booking_deposited WHERE booking_id in (SELECT psb.id FROM fg_sm_bookings psb WHERE $condition) ");
                $conn->executeQuery("INSERT INTO fg_sm_booking_deposited (" . $depositedFields . ") ($depositedValues) ");
            }
            //inert each payment plan for all sponsors
            if (count($paymentValues)) {
                foreach ($paymentValues as $paymentQuery) {
                    $conn->executeQuery("INSERT INTO fg_sm_paymentplans (booking_id,`date`,`amount`,discount,discount_type) $paymentQuery ");
                }
            }
            if($paymentModified && $bookingId){
                $this->getEntityManager()->getRepository('CommonUtilityBundle:FgSmBookings')->updatePaymentPlanOnEdit($bookingId,$club,$conn);
            }
            $actionType = ($bookingId) ? 'changed' : 'assigned';
            $logQuery = "(SELECT $clubId,{$serviceId},NOW(),'assignment',$contactId,'$actionType',psb.contact_id FROM fg_sm_bookings psb WHERE $condition)";
            $conn->executeQuery("INSERT INTO fg_sm_services_log(club_id,service_id,date,kind,changed_by,action_type,sponsor_id) $logQuery");
            $sponsorLogQuery = "(SELECT $clubId,psb.category_id,{$serviceId},NOW(),'assignment',$contactId,'$actionType',psb.contact_id FROM fg_sm_bookings psb WHERE $condition)";
            $conn->executeQuery("INSERT INTO fg_sm_sponsor_log(club_id,category_id,service_id,date,kind,changed_by,action_type,contact_id) $sponsorLogQuery");
            $conn->commit();
        } catch (Exception $ex) {
            $conn->rollback();
            $rollback = true;
            throw $ex;
        }
    }


    /**
     * Function to update payment plan entries in edit
     *
     * @param int $bookingId Booking id
     * @param object $club   Club object
     * @param object $conn   Connection object
     */
    public function  updatePaymentPlanOnEdit($bookingId,$club,$conn){
        $bookingObj = $this->find($bookingId);
        if($bookingObj->getPaymentPlan()=='regular'){
            $fPayDate = $bookingObj->getFirstPaymentDate();
            $lPayDate = $bookingObj->getLastPaymentDate();
            $sEndDate = $bookingObj->getEndDate();
            $repMonths = round($bookingObj->getRepetitionMonths());
            $phpDateFormat = FgSettings::getPhpDateFormat();
            $paymentArray=array();
            
            //insert payment entry for next 4 years if no payment end date and service end date
            //Get payment dates
            $firstPaymentDate = $fPayDate->format($phpDateFormat);
            if($lPayDate != ''){
                $lastDate = $lPayDate->format($phpDateFormat);
            } else if($sEndDate != '') {
                $lastDate = $sEndDate->format($phpDateFormat);
            } else {
                $lastDate = '';
            }
            
            $pDateArray = $this->getPaymentDateForInsertion($firstPaymentDate, $lastDate, $repMonths, $club);
            
            $discountType = ($bookingObj->getDiscount() > 0) ? $bookingObj->getDiscountType() : 'N';
            //insert payment entries for regular plan
            foreach ($pDateArray as $regularDate) {
                $paymentArray[] = "($bookingId,'$regularDate','{$bookingObj->getAmount()}','{$bookingObj->getDiscount()}','{$discountType}')";
            }
            
             //inert each payment plan for all sponsors
            if (count($paymentArray)) {
                $conn->executeQuery("DELETE FROM fg_sm_paymentplans WHERE booking_id=$bookingId");
                $conn->executeQuery("INSERT INTO fg_sm_paymentplans (booking_id,`date`,`amount`,discount,discount_type) VALUES". implode(',', $paymentArray));
            }
        } elseif($bookingObj->getPaymentPlan()=='none'){
            $conn->executeQuery("DELETE FROM fg_sm_paymentplans WHERE booking_id=$bookingId");
        }
    }

    /**
     * Function to set date field
     * @param string $key   array key
     * @param string $value data array value
     */
    public function setDateFieldForBooking($key,&$value){
        //format if date field
        if ($value != '' && in_array($key, array('begin_date', 'first_payment_date', 'last_payment_date'))) {
            $date = new \DateTime();
            $value = $date->createFromFormat(FgSettings::getPhpDateFormat(), $value)->format('Y-m-d');
        } elseif($value != '' && $key==='end_date'){ //set end date time as 23:59:59
            $date = new \DateTime();
            $value = $date->createFromFormat(FgSettings::getPhpDateFormat(), $value)->format('Y-m-d');
            $value = $value.'  23:59:59';
        }
        if($value=='00.00.0000' || $value==''){
            $value='NULL';
        }
    }
    /**
     * create query to save payment entries for regular type
     *
     * @param array $dataArray     form data array
     * @param object $club         club object
     * @param string $condition    condition string
     * @param array $paymentValues payment query array
     */
    public function setQueryForRegularType($dataArray,$club,$condition,&$paymentValues){
        $dataArray['repetition_months'] = round($dataArray['repetition_months']);
        $lastDate = empty($dataArray['last_payment_date']) ? $dataArray['end_date'] : $dataArray['last_payment_date'];
        $pDateArray = $this->getEntityManager()
                            ->getRepository('CommonUtilityBundle:FgSmBookings')
                            ->getPaymentDateForInsertion($dataArray['first_payment_date'], $lastDate, $dataArray['repetition_months'], $club);
        $discountType = ($dataArray['discount'] > 0) ? $dataArray['discount_type'] : 'N';
        
        //insert payment entries for regular plan
        foreach ($pDateArray as $regularDate) {
            $paymentValues[] = "(SELECT psb.id,'$regularDate','{$dataArray['amount']}','{$dataArray['discount']}','{$discountType}' FROM fg_sm_bookings psb WHERE $condition )";
        }
    }
    /**
     * Function to set query for custom payment in save booking
     * @param object $conn          Connection object
     * @param array $value          value array
     * @param array $paymentValues  payment query array
     * @param string $condition     query condition
     */
    public function setQueryForCustomType($conn,$value,&$paymentValues,$condition){
        //update custom payment plans
        foreach ($value as $paymentId => $payments) {
            $paymentUpdate = array();
            if (\is_numeric($paymentId)) { //update existing payment
                foreach ($payments as $field => $value) {
                    if ($field == 'isDeleted') {
                        $conn->executeQuery("DELETE FROM fg_sm_paymentplans WHERE id=$paymentId ");
                        continue;
                    } else { //update existing payment
                        if ($value != '' && $field == 'date') {
                            $date = new \DateTime();
                            $value = $date->createFromFormat(FgSettings::getPhpDateFormat(), $value)->format('Y-m-d');
                        } elseif($value != '' && in_array($field, array('discount','amount'))){
                            $value = str_replace(FgSettings::getDecimalMarker(), '.', $value);
                        }
                        $paymentUpdate[] = "$field='$value'";
                    }
                }
                if (count($paymentUpdate)) {
                    $conn->executeQuery("UPDATE fg_sm_paymentplans SET " . implode(',', $paymentUpdate) . " WHERE id=$paymentId ");
                }
            } else { //create new payment plan $currentId
                $date = new \DateTime();
                $payDate = $date->createFromFormat(FgSettings::getPhpDateFormat(), $payments['date'])->format('Y-m-d');
                            
                $payDiscountType = ($payments['discount'] > 0) ? $payments['discount_type'] : 'N';
                $paymentValues[] = "(SELECT psb.id,'$payDate','{$payments['amount']}','{$payments['discount']}','{$payDiscountType}' FROM fg_sm_bookings psb WHERE $condition )";
            }
        }
    }

    /**
     * Function to set default booking values
     *
     * @param type $bookingId
     * @param type $contactId
     * @param type $clubId
     * @param type $currentId
     * @param type $smBookingFields
     * @param type $smBookingValues
     * @param type $smBookingQueryDup
     */
    public function setBookingDefaultValues($bookingId, $contactId, $clubId, $currentId, &$smBookingFields, &$smBookingValues, &$smBookingQueryDup)
    {
        if ($bookingId) { //edit booking
            $smBookingFields[] = "fg_sm_bookings.id";
            $smBookingValues[] = "$bookingId";
            $smBookingQueryDup[] = "fg_sm_bookings.id = LAST_INSERT_ID( fg_sm_bookings.id )";

            $smBookingFields[] = "fg_sm_bookings.updated_at";
            $smBookingValues[] = "NOW()";

            $smBookingFields[] = "fg_sm_bookings.updated_by";
            $smBookingValues[] = "$contactId";
        } else { //add new booking
            $smBookingFields[] = "fg_sm_bookings.id";
            $smBookingValues[] = "''";
            $smBookingQueryDup[] = "fg_sm_bookings.id = LAST_INSERT_ID( fg_sm_bookings.id )";

            $smBookingFields[] = "fg_sm_bookings.created_at";
            $smBookingValues[] = "NOW()";

            $smBookingFields[] = "fg_sm_bookings.created_by";
            $smBookingValues[] = "$contactId";
        }
        $smBookingFields[] = "`contact_id`";
        $smBookingValues[] = "C.id";
        $smBookingQueryDup[] = "`contact_id` = VALUES(`contact_id`)";

        $smBookingFields[] = "fg_sm_bookings.club_id";
        $smBookingValues[] = "$clubId";
        $smBookingQueryDup[] = "fg_sm_bookings.club_id = VALUES(fg_sm_bookings.club_id)";

        $smBookingFields[] = "fg_sm_bookings.timestamp";
        $smBookingValues[] = $currentId;
    }

    /**
     * Method to get contact ids of particular services
     *
     * @param string $services      comma separated service ids
     * @param int $clubId           current club id
     *
     * @return string               comma separated contact ids
     */
    public function getContactsofServices($services, $clubId)
    {
        $qb = $this->createQueryBuilder('B')
                ->select('GROUP_CONCAT(C.id )as contact')
                ->innerJoin("CommonUtilityBundle:FgCmContact", "C", "WITH", "B.contact = C.id")
                ->where("B.service IN ($services)")
                ->andWhere('B.club = :club')
                ->andWhere('B.beginDate <= :now')
                ->andWhere('B.endDate >= :now OR B.endDate IS NULL')
                ->andWhere('B.isDeleted = 0')
                ->setParameter('club', $clubId)
                ->setParameter('now', new \DateTime(), \Doctrine\DBAL\Types\Type::DATETIME);
        $result = $qb->getQuery()->getArrayResult();

        return $result[0]['contact'];
    }

    /**
     * Get earliest start date of a service booking payment
     * @param int $clubId
     *
     * @return array
     */
    public function getServiceStartDate($clubId)
    {

        $qb = $this->createQueryBuilder('b')
                ->select('Min(b.beginDate) as startDate')
                ->where('b.club=:clubId')
                ->setParameter('clubId', $clubId);
        $result = $qb->getQuery()->getArrayResult();
        return $result[0];
    }

    /**
     * Recursive function to get fiscal years of all bookings
     * @param date $initialStartDate earliest start date of a service booking payment
     * @param date $endDate endDate
     * @param type $startDate startDate
     * @param obj $club club obj
     * @param pointer $fiscalYear pointer to previous fiscal year array
     *
     * @return array
     */
    public function getFiscalYears($initialStartDate, $endDate, $startDate, $club, &$fiscalYear)
    {
        if ($endDate >= $startDate) {
            $fiscalYear[] = $club->getFiscalYearStartDate($startDate);
            $startDate = date('Y-m-d H:i:s', strtotime($startDate . ' +1 year'));
            $this->getEntityManager()->getRepository('CommonUtilityBundle:FgSmBookings')->getFiscalYears($initialStartDate, $endDate, $startDate, $club, $fiscalYear);
        }
        return $fiscalYear;
    }

    /**
     * Function to stop/delete a particular service
     *
     * @param array $bookedIds      booked ids array
     * @param string $actionType    (deleteservice/stopservice/ deleteserviceofsponsor/ stopserviceofsponsor)
     * @param obj $club             club service obj
     *
     * @return null
     */
    public function stopServices($bookedIds, $actionType, $club)
    {
        foreach ($bookedIds as $id) {
            $deletePayments = $this->_em->getRepository('CommonUtilityBundle:FgSmPaymentplans')->deletefuturePayments($id);
            $bookingObj = $this->find($id);
            $bookingObj->setEndDate(new \DateTime("now"));
            $lastPaymentDate =  $bookingObj->getLastPaymentDate();
            if($lastPaymentDate) {
                $bookingObj->setLastPaymentDate(new \DateTime("now"));
            }
            $pastPayments = $this->_em->getRepository('CommonUtilityBundle:FgSmPaymentplans')->findpastpayments($id);
            if ($pastPayments < 1) {
                $bookingObj->setPaymentPlan("none");
            }
            if ($actionType == "deleteservice" || $actionType == "deleteserviceofsponsor") {
                $bookingObj->setIsDeleted(1);
            }
            $this->_em->persist($bookingObj);
            $this->_em->flush();
            //insert log entry
            $this->insertLogForServiceStop($id, $actionType, $club);
        }
    }
    /**
     * Function to get regular bookings with out end date and missing payment plan for cron updation
     *
     * @return array
     */
    public function getBookingsForPaymentUpdate(){
        $conn = $this->getEntityManager()->getConnection();
        $nextCountQuery="(select count(SPP.id) from fg_sm_paymentplans SPP WHERE SPP.booking_id=SB.id AND SPP.date>now())";
        $selQuery ="select SB.*, $nextCountQuery as nextPayments,(SELECT MAX(date) FROM fg_sm_paymentplans SPP WHERE SPP.booking_id=SB.id) as maxdate FROM fg_sm_bookings SB "
                . "WHERE (48/SB.repetition_months > $nextCountQuery OR $nextCountQuery < 5) AND SB.payment_plan='regular' AND (SB.end_date='null' OR SB.end_date is NULL) AND (SB.last_payment_date is NULL OR SB.last_payment_date ='null') AND SB.is_deleted=0 ";

         return $conn->fetchAll($selQuery);
    }
    /**
     * Function to add missing payments for next 4 years
     * @param type $bookingDetails
     * @return boolean
     */
    public function updatePaymentForNextYear($bookingDetails){
        $conn = $this->getEntityManager()->getConnection();
        $reqPayments= floor(48/$bookingDetails['repetition_months']);
        $reqCount=($reqPayments>5) ? $reqPayments: 5;
        $paymentArray=array();
        $lastDate=  empty($bookingDetails['maxdate']) ? time():strtotime($bookingDetails['maxdate']);
        if($bookingDetails['repetition_months']<1){
            return false;
        }
        for($count=$bookingDetails['nextPayments'];$count<=$reqCount;$count++){
            $lastDate=  strtotime("+{$bookingDetails['repetition_months']} months", $lastDate);
            $regDate = date('Y-m-d',$lastDate);
            $paymentArray[] = "({$bookingDetails['id']},'$regDate','{$bookingDetails['amount']}','{$bookingDetails['discount']}','{$bookingDetails['discount_type']}')";
        }
        if(count($paymentArray)){
            $conn->executeQuery("INSERT INTO fg_sm_paymentplans (booking_id,`date`,`amount`,discount,discount_type) VALUES". implode(',', $paymentArray));
        }
    }
    /**
     * Method to insert log on service delete or stop
     * @param int $bookedId         primary key of fg_sm_bookings
     * @param string $actionType    (deleteservice/stopservice/ deleteserviceofsponsor/ stopservicefsponsor)
     * @param obj $club             club service obj
     *
     * @return null
     */
    public function insertLogForServiceStop($bookedId, $actionType, $club)
    {
        $qb = $this->createQueryBuilder('B')
                ->select('CLUB.id as club, SERVICE.id as service, CONTACT.id  as contact, CATEGORY.id as category')
                ->innerJoin("CommonUtilityBundle:FgClub", "CLUB", "WITH", "CLUB.id = B.club")
                ->innerJoin("CommonUtilityBundle:FgSmServices", "SERVICE", "WITH", "SERVICE.id = B.service")
                ->innerJoin("CommonUtilityBundle:FgSmCategory", "CATEGORY", "WITH", "CATEGORY.id = SERVICE.category")
                ->innerJoin("CommonUtilityBundle:FgCmContact", "CONTACT", "WITH", "CONTACT.id = B.contact")
                ->where('B.id=:bookedId')
                ->setParameter('bookedId', $bookedId);
        $result = $qb->getQuery()->getArrayResult();
        $contactId = $club->get('contactId');
        $action = ($actionType === "deleteservice" || $actionType == "deleteserviceofsponsor") ? "deleted" : "stopped";
        $logData = array("kind" => "assignment", "action_type" => $action, "sponsor_id" => $result[0]['contact'], "category" => $result[0]['category'] );
        $this->_em->getRepository('CommonUtilityBundle:FgSmServicesLog')->insertLog($result[0]['club'], $result[0]['service'], $contactId, $logData, false, false, false, true);
        $this->_em->getRepository('CommonUtilityBundle:FgSmSponsorLog')->insertLog($result[0]['club'], $result[0]['service'], $contactId, $logData, false, false, false, true);
    }

    /**
     * Method to get count of sponser services
     * @param type $clubId  current club Id
     * @param type $contact contactId
     * @return int
     */
    public function getCountOfAllServiceAssignments($clubId, $contact)
    {
        $conn = $this->getEntityManager()->getConnection();
        $resultQuery = "SELECT sb.contact_id,(SELECT COUNT(s.id) as serviceCount FROM fg_sm_bookings b "
                . "LEFT JOIN fg_sm_services s ON b.service_id = s.id AND b.is_deleted = 0 "
                . "WHERE b.contact_id = sb.contact_id AND (b.end_date >= NOW() OR b.end_date IS NULL OR b.end_date = '')"
                . " AND b.club_id = $clubId) as serviceCount FROM fg_sm_bookings sb WHERE sb.contact_id IN($contact) GROUP BY sb.contact_id";

        return $conn->fetchAll($resultQuery);
    }

    /**
     * Function to skip a particular service
     *
     * @param array $bookedIds      booked ids array
     * @param obj $club             club service obj
     *
     * @return null
     */
    public function skipServices($bookedIds, $club)
    {
        foreach ($bookedIds as $bookedId) {
            $id =  preg_replace('/[^0-9\-]/', '', $bookedId);
            $bookingObj = $this->find(intval($id));
            if($bookingObj) {
                $bookingObj->setIsSkipped(1);
                $this->_em->persist($bookingObj);
                //insert log entry
                $this->insertLogForServiceSkip($id, $club);
            }
        }
        $this->_em->flush();
    }

    /**
     * Method to insert log on service skip
     * @param int $bookedId         primary key of fg_sm_bookings
     * @param obj $club             club service obj
     *
     * @return null
     */
    private function insertLogForServiceSkip($bookedId, $club) {
        $qb = $this->createQueryBuilder('B')
                ->select('CLUB.id as club, SERVICE.id as service, CONTACT.id  as contact, CATEGORY.id as category')
                ->innerJoin("CommonUtilityBundle:FgClub", "CLUB", "WITH", "CLUB.id = B.club")
                ->innerJoin("CommonUtilityBundle:FgSmServices", "SERVICE", "WITH", "SERVICE.id = B.service")
                ->innerJoin("CommonUtilityBundle:FgSmCategory", "CATEGORY", "WITH", "CATEGORY.id = SERVICE.category")
                ->innerJoin("CommonUtilityBundle:FgCmContact", "CONTACT", "WITH", "CONTACT.id = B.contact")
                ->where('B.id=:bookedId')
                ->setParameter('bookedId', $bookedId);
        $result = $qb->getQuery()->getArrayResult();
        $contactId = $club->get('contactId');
        $logData = array("kind" => "assignment", "action_type" => "skipped", "sponsor_id" => $result[0]['contact'], "category" => $result[0]['category'] );
        $this->_em->getRepository('CommonUtilityBundle:FgSmServicesLog')->insertLog($result[0]['club'], $result[0]['service'], $contactId, $logData, false, false, false, true);
    }

    /**
     * Method to get count of skipped services from given services
     * @param  string $bookedIds Comma separated ids (fg_sm_bookings.id)
     * @return int
     */
    public function getSkippedServicesCount($bookedIds) {
        if($bookedIds) {
            $qb = $this->createQueryBuilder('B')
                ->select("count(B.id) as cnt")
                ->where("B.id IN ($bookedIds)")
                ->andWhere(" B.isSkipped = 1 ");
            $result = $qb->getQuery()->getArrayResult();

            return $result[0]['cnt'];
        }
    }

    /**
     * Method to get the payment month between the days
     * @param  string $firstDate Should be in the Locale setting date format
     * @param  string $endDate or Service end date Should be in the Locale setting date format
     * @param  string $endPaymentDate Should be in the Locale setting date format
     * @param  int $repetitionMonths
     * @param  obj $clubObj
     * 
     * @return $paymentDateArray format Y-m-d
     */
    public function getPaymentDateForInsertion($firstDate, $endDate, $repetitionMonths, $clubObj){
        
        $paymentDateArray = array();
        $futureDateCount = 0;
        $dateObj = new \DateTime();
        
        $firstDateObj = \DateTime::createFromFormat(FgSettings::getPhpDateFormat(), $firstDate);
        $intervalString = "P".$repetitionMonths."M";
        
        //$x = $clubObj->getFiscalYear();
        //echo '$firstDate '.$firstDate.' With repetation '.$repetitionMonths. ' upto '.$endDate.' on fiscal year '.$x['current']['end'].'</br>';
        
        if($firstDateObj !== false){
            $paymentDateArray[] = $firstDateObj->format('Y-m-d');

            if($repetitionMonths > 0){
                //Now add dates to 
                //Last payment date specified
                if(empty($endDate)){
                    $fiscal = $clubObj->getFiscalYear();    //Should be in Y-m-d format

                    $fiscalEndDateObj = new \DateTime();
                    $fiscalEnd = $fiscalEndDateObj->createFromFormat('Y-m-d', $fiscal['current']['end']);

                    if($fiscalEnd !== false){
                        $lastDateObj = new \DateTime();
                        $lastDateObj->createFromFormat('Y-m-d', $fiscal['current']['end']);
                        $lastDate = date_add ( $lastDateObj , new \DateInterval('P4Y') );
                        $lastDateTimeStamp = $lastDate->format('U');

                        //Add month till 4 years with the repetation dates
                        for($i = 0; ($firstDateObj->format('U') <= $lastDateTimeStamp); $i++){
                            $paymentDateArray[] = $firstDateObj->add(new \DateInterval($intervalString))->format('Y-m-d');
                            
                            if($firstDateObj->format('U') > $dateObj->format('U')){
                                $futureDateCount++;
                            }
                        }

                        //If the total is not 4 add until 4 payments
                        if (count($paymentDateArray) < 4){
                            for($i = 0; (count($paymentDateArray) <= 4); $i++){
                                $paymentDateArray[] = $firstDateObj->add(new \DateInterval($intervalString))->format('Y-m-d');
                                
                                if($firstDateObj->format('U') > $dateObj->format('U')){
                                    $futureDateCount++;
                                }
                            }
                        }
                        
                        //If the total future paymenst is not 4 add until 4 payments
                        if($futureDateCount < 4){
                            for($i = 0; $futureDateCount <= 4; $i++){
                                $paymentDateArray[] = $firstDateObj->add(new \DateInterval($intervalString))->format('Y-m-d');
                                
                                if($firstDateObj->format('U') > $dateObj->format('U')){
                                    $futureDateCount++;
                                }
                            }
                        }
                        
                    }
                } else {
                    $endDateObj = \DateTime::createFromFormat(FgSettings::getPhpDateFormat(), $endDate);
                    $endDateTimeStamp = $endDateObj->format('U');

                    //Add till enddate
                    for($i = 0; ($firstDateObj->format('U') <= $endDateTimeStamp); $i++){
                        $firstDateObj->add(new \DateInterval($intervalString));
                        $firstDateTimestamp = $firstDateObj->format('U');
                        if($firstDateTimestamp <= $endDateTimeStamp){
                            $paymentDateArray[] = $firstDateObj->format('Y-m-d');
                        }

                    }
                }
            }
        }

        //print_r($paymentDateArray);
        return $paymentDateArray;
    }
    
}
