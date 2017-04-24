<?php

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Util\FgSettings;

/**
 * FgCmMembershipLogRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class FgCmMembershipLogRepository extends EntityRepository
{
    /**
     * Function to update membership log of contact on update of joining date/leaving date.
     *
     * @param int    $contactId ContactId
     * @param string $dateType  joining_date/leaving_date
     * @param date   $newDate   New date
     * @param string $sortOrder Sort order ASC/DESC
     *
     * @return
     */
    public function updateMembershipLogOfContact($contactId = '', $dateType = 'leaving_date', $newDate, $sortOrder = 'DESC')
    {
        if ($contactId != '' && $newDate != '') {
            if ($dateType == 'joining_date') {
                $where = "(((m.value_before = '') || (m.value_before = '-') || (m.value_before IS NULL)) && ((m.value_after != '') && (m.value_after IS NOT NULL)))";
            } elseif ($dateType == 'leaving_date') {
                $where = "(((m.value_before != '') && (m.value_before IS NOT NULL)) && ((m.value_after = '') || (m.value_after = '-') || (m.value_after IS NULL)))";
            }
            $conn = $this->getEntityManager()->getConnection();
            $sql = 'UPDATE fg_cm_membership_log m SET m.date = "'.$newDate.'" WHERE m.contact_id =:contactId AND '.$where.' AND m.kind="assigned contacts" ORDER BY m.date '.$sortOrder.' LIMIT 1';
            $conn->executeQuery($sql, array(':contactId' => $contactId));
        }

        return;
    }

    public function updateMembershipLogEntryOfContact($contactId = '', $membershipId = '', $field, $value)
    {
        if ($contactId != '' && $membershipId != '') {
            switch ($field) {
                case 'joining_date':
                    $set = 'm.date = :value';
                    $where = 'm.date=mh.joining_date';
                    break;
                case 'leaving_date':
                    $set = 'm.date = :value';
                    $where = 'm.date=mh.leaving_date';
                    break;
                case 'membership':
                    $set = 'm.membership_id = :value';
                    $where = '((m.date=mh.joining_date) OR (m.date=mh.leaving_date))';
                    break;
                default:
                    break;
            }
            $conn = $this->getEntityManager()->getConnection();
            $sql = 'UPDATE fg_cm_membership_log m JOIN fg_cm_membership_history mh ON (m.membership_id=mh.membership_id AND m.contact_id=mh.contact_id AND '.$where.') SET '.$set.' WHERE m.contact_id =:contactId AND mh.id =:mId AND '.$where;
            $conn->executeQuery($sql, array(':contactId' => $contactId, ':mId' => $membershipId, ':value' => $value));
        }

        return;
    }

    public function updateClubMembershipInLog($clubId, $contactId, $membershipId, $transferDate = '', $newMembershipId = '')
    {
        $conn = $this->getEntityManager()->getConnection();
        $set = '';
        $params = array();
        if ($newMembershipId != '') {
            $set .= 'm.membership_id = :newMembershipId';
            $params['newMembershipId'] = $newMembershipId;
        }
        if ($transferDate != '') {
            $dateObj = new \DateTime();
            $transferDate = $dateObj->createFromFormat(FgSettings::getPhpDateFormat(), $transferDate)->format('Y-m-d H:i:s');
            $set .= ', m.date = :transferDate';
            $params['transferDate'] = $transferDate;
        }
        $params['clubId'] = $clubId;
        $params['contactId'] = $contactId;
        $params['membershipId'] = $membershipId;
        $sql = 'UPDATE fg_cm_membership_log m SET '.$set.' WHERE m.contact_id =:contactId AND m.club_id = :clubId AND m.membership_id = :membershipId AND m.kind="assigned contacts" ORDER BY m.date DESC LIMIT 1';
        $conn->executeQuery($sql, $params);

//        $params = array();
//        $q = $this->createQueryBuilder()
//            ->update('CommonUtilityBundle:FgCmMembershipLog', 'C');
//        if ($newMembershipId != '') {
//            $q = $q->set('C.membership', ':newMembershipId');
//            $params['newMembershipId'] = $newMembershipId;
//        }
//        if ($transferDate != '') {
//            $dateObj = new \DateTime();
//            $transferDate = $dateObj->createFromFormat(FgSettings::getPhpDateFormat(), $transferDate)->format('Y-m-d H:i:s');
//            $q = $q->set('C.date', ':transferDate');
//            $params['transferDate'] = $transferDate;
//        }
//        $params['clubId'] = $clubId;
//        $params['contactId'] = $contactId;
//        $params['membershipId'] = $membershipId;
//        $q = $q->where("C.contact =:contactId")
//            ->andWhere("C.membership = :membershipId")
//            ->andWhere("C.club = :clubId")
////            ->addOrderBy('C.date', ':DESC')
//            ->setMaxResults(1)
//            ->setParameters($params)
//            ->getQuery();
//
//        $res = $q->execute();
    }
}