<?php

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Entity\FgSmBookingDeposited;
use Common\UtilityBundle\Util\FgSettings;

/**
 * FgSmBookingDepositedRepository
 *
 * This class is used for handling sponsor bookings in Sponsors Administration.
 *
 * @author pitsolutions.ch
 */
class FgSmBookingDepositedRepository extends EntityRepository
{
    /**
     * Function to get deposited of a booking
     * 
     * @param int $bookingid
     * @return array
     */
    public function getDepositsOfBooking($bookingid)
    {
        $result = $this->createQueryBuilder('b')
                ->select("GROUP_CONCAT(b.contact) as contacts, GROUP_CONCAT(b.role) AS roleId")
                ->where('b.booking=:bookingId')->groupBy('b.booking')
                ->setParameters(array('bookingId' => $bookingid))
                ->getQuery()
                ->getArrayResult();

        return $result[0];
    }
    /**
     * Function to delete deposited of a booking
     * 
     * @param object $conn
     * @param int $bookingid
     * 
     * @return boolean
     */
    public function deleteDepositedOfBooking($conn,$bookingid){
        if($bookingid){
            $conn->executeQuery("DELETE FROM fg_sm_booking_deposited WHERE booking_id = $bookingid ");
        }
        return true;
    }

    /**
     * This function is used to get the sponsorship details of a particular contact
     * to display in contact overview sponsored by box and team sponsor details in team settings page
     * 
     * @param int    $club        current club id
     * @param int    $depositedId depositedid-either contact id or role id
     * @param string $serviceType service type-either contact or team
     *
     * @return array Sponsor details array
     */
    public function getAllServicesDetailsOfContact($club, $depositedId, $serviceType = "contact")
    {
        $doctrineConfig = $this->getEntityManager()->getConfiguration();
        $doctrineConfig->addCustomStringFunction('DATE_FORMAT', 'Common\UtilityBundle\Extensions\DateFormat');
        $doctrineConfig->addCustomStringFunction('contactName', 'Common\UtilityBundle\Extensions\FetchContactName');

        $dateFormat = FgSettings::getMysqlDateFormat();

        $result = $this->createQueryBuilder('bd')
                ->select("DATE_FORMAT(b.beginDate,'$dateFormat') AS startDate, DATE_FORMAT(b.endDate,'$dateFormat') AS endDate, IDENTITY(b.contact) AS sponsorId, contactName(b.contact) AS sponsorName, s.title AS serviceName")
                ->leftJoin('CommonUtilityBundle:FgSmBookings', 'b', 'WITH', 'bd.booking = b.id')
                ->leftJoin('CommonUtilityBundle:FgSmServices', 's', 'WITH', 'b.service = s.id');
        $result = ($serviceType == "team") ? $result->where('bd.role = :depositedId') : $result->where('bd.contact = :depositedId');
        $result = $result->andWhere('b.club = :clubId')
                ->andWhere('b.endDate IS NULL OR b.endDate > :now')
                ->andWhere('s.serviceType = :serviceType')
                ->andWhere('s.club = :clubId')
                ->groupBy('bd.booking')
                ->orderBy('b.beginDate', 'ASC')
                ->setParameters(array('clubId' => $club, 'depositedId' => $depositedId, 'now' => date('Y-m-d H:i:s'), 'serviceType' => $serviceType))
                ->getQuery()
                ->getArrayResult();
        return $result;
    }

}
