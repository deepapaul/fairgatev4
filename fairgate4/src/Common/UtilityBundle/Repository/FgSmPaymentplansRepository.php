<?php

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * FgSmPaymentplansRepository
 *
 * This class is used for handling payment plans in Sponsors Administration.
 *
 * @author pitsolutions.ch
 */
class FgSmPaymentplansRepository extends EntityRepository
{

    /**
     * Function to delete future payments
     *
     * @param int $bookingId Booking Id
     *
     * @return null
     */
    public function deletefuturePayments($bookingId)
    {
        $date = date('Y-m-d H:i:s');
        $conn = $this->getEntityManager()->getConnection();
        $conn->executeQuery("DELETE FROM fg_sm_paymentplans  where booking_id = $bookingId AND date > '$date' ");
    }

    /**
     * Function to find  the total past payments
     *
     * @param int $bookingId Booking Id
     *
     * @return int
     */
    public function findpastpayments($bookingId)
    {
        $resData = $this->createQueryBuilder('pm')
                ->select("COUNT(pm.id) as pastCount")
                ->where('pm.booking=:bookingId')
                ->andWhere('pm.date < :now')
                ->setParameters(array('bookingId' => $bookingId, 'now' => date('Y-m-d H:i:s')))
                ->getQuery()
                ->getArrayResult();

        return $resData[0]['pastCount'];
    }

}
