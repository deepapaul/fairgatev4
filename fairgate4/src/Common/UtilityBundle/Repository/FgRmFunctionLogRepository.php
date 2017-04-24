<?php

/**
 * FgRmFunctionLogRepository
 *
 * This class is used for function log in role management.
 */

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Util\FgSettings;

/**
 * FgRmFunctionLogRepository
 *
 * This class is used for listing and adding function log in role management.
 */
class FgRmFunctionLogRepository extends EntityRepository
{

    /**
     * Function to get Log of a given function.
     *
     * @param int   $functionId
     * @param int   $clubId
     * @param string $clubType
     * @param string $clubDefaultLang
     *
     * @return array $result Result array of function log.
     */
    public function getFunctionLog($functionId, $clubId, $clubType, $clubDefaultLang)
    {
        $dateFormat = FgSettings::getMysqlDateTimeFormat();
        $functionId = intval($functionId);
        $conn = $this->getEntityManager()->getConnection();
        $clubTitleQuery = "SELECT COALESCE(NULLIF(Ci18N.title_lang,''),FC.title) AS title FROM fg_cm_contact CT LEFT JOIN fg_club FC ON CT.main_club_id = FC.id LEFT JOIN fg_club_i18n Ci18N ON Ci18N.id = FC.id AND Ci18N.lang = '$clubDefaultLang' WHERE CT.id = r.changed_by";
        $fedConfCheck = (($clubType == 'federation') || ($clubType == 'sub_federation')) ? " ((r.contact_id IS NULL) OR (r.contact_id IS NOT NULL AND ((c.is_fed_membership_confirmed='0') OR (c.is_fed_membership_confirmed='1' AND c.old_fed_membership_id IS NOT NULL)))) " : "r.contact_id IS NULL OR r.contact_id IS NOT NULL";
        $sql = "SELECT r.id, r.club_id, r.date AS dateOriginal, r.kind, r.field, r.value_before, r.value_after,date_format( r.date,'". $dateFormat ."') AS date, checkActiveContact(r.changed_by, $clubId) as activeContact,r.changed_by,
            IF((checkActiveContact(r.changed_by, $clubId) is null && r.changed_by != 1), CONCAT(contactName(r.changed_by),' ( ',($clubTitleQuery),' )') , contactName(r.changed_by) )as editedBy,
                    IF((r.kind = 'data'), 'data', 'assignments') AS tabGroups,
                    (CASE WHEN ((r.value_before IS NOT NULL AND r.value_before != '' AND r.value_before != '-') AND (r.value_after IS NULL OR r.value_after = '' OR r.value_after = '-')) THEN 'removed'
                           WHEN ((r.value_before IS NULL OR r.value_before = '' OR r.value_before = '-') AND (r.value_after IS NOT NULL AND r.value_after != '' AND r.value_after != '-')) THEN 'added'
                           WHEN ((r.value_before IS NOT NULL AND r.value_before != '' AND r.value_before != '-') AND (r.value_after IS NOT NULL AND r.value_after != '' AND r.value_after != '-') AND (r.value_before != r.value_after)) THEN 'changed'
                           ELSE 'none'
                    END) AS status,
                    (IF((r.value_after='' OR r.value_after IS NULL OR r.value_after='-'),r.value_before,r.value_after)) AS columnVal3
                    FROM fg_rm_function_log r 
                    LEFT JOIN fg_cm_contact c ON r.contact_id=c.id 
                    LEFT JOIN fg_club cc ON r.club_id=cc.id
                    LEFT JOIN fg_rm_role rr ON r.role_id=rr.id
                    WHERE r.function_id = $functionId AND r.kind IN ('data', 'assigned contacts')
                    AND IF ((rr.is_executive_board = 0),$fedConfCheck,IF((r.club_id = $clubId),((r.contact_id IS NULL) OR (r.contact_id IS NOT NULL) AND r.club_id = $clubId),((r.contact_id IS NULL) OR (r.contact_id IS NOT NULL))))
                    ORDER BY DATE(r.date) DESC";
        $result = $conn->executeQuery($sql)->fetchAll();

        return $result;
    }

}
