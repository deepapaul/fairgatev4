<?php

/**
 * FgRmRoleLogRepository
 *
 * This class is used for role log in role management.
 */

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Util\FgSettings;

/**
 * FgRmRoleLogRepository
 *
 * This class is used for listing and inserting role log in role management.
 */
class FgRmRoleLogRepository extends EntityRepository
{

    /**
     * Function to do list the log details of team ,role ,workgroup, team functions,role functions and workgroup functions.
     *
     * @param int    $clubId           Club Iid
     * @param int    $roleId           Role Id
     * @param string $clubType         Club Type
     * @param int    $executiveBoardId Club Executive Board Id
     * @param int    $federationId     Federation Id
     * @param array  $hierarchyClubIds Heirarchy club ids
     * @param string $clubDefaultLang  Heirarchy club ids
     *
     * @return array $result Array of role logs.
     */
    public function getRoleLog($clubId, $roleId, $clubType, $executiveBoardId, $federationId, $hierarchyClubIds, $clubDefaultLang)
    {
        $dateFormat = FgSettings::getMysqlDateTimeFormat();
        $clubId = intval($clubId);
        $roleId = intval($roleId);
        $conn = $this->getEntityManager()->getConnection();
        $cond = " AND r.role_id = $roleId";

        if ($roleId == $executiveBoardId) {
            if ($clubType == 'sub_federation') {

                $fedWrkgrpCatRoleIds = $this->_em->getRepository('CommonUtilityBundle:FgRmCategory')->getExecutiveBoardRoleCatIds($federationId);
                $fedWrkgrpRoleId = $fedWrkgrpCatRoleIds['roleId'];
                if (count($hierarchyClubIds)) {
                    $clubIds = FgUtility::getSecuredData(implode(',', $hierarchyClubIds), $conn);
                    $cond = " AND r.role_id IN ($roleId,$fedWrkgrpRoleId) AND r.club_id IN ($clubId)";
                   // $cond = " AND r.role_id IN ($roleId,$fedWrkgrpRoleId) AND r.club_id IN ($clubId,$clubIds)";
                } else {
                    $cond = " AND r.role_id IN ($roleId,$fedWrkgrpRoleId) AND r.club_id = $clubId";
                }

            } else if (($clubType == 'federation_club') || ($clubType == 'sub_federation_club')) {

                $fedWrkgrpCatRoleIds = $this->_em->getRepository('CommonUtilityBundle:FgRmCategory')->getExecutiveBoardRoleCatIds($federationId);
                $fedWrkgrpRoleId = $fedWrkgrpCatRoleIds['roleId'];
                $cond = " AND r.club_id=$clubId AND r.role_id IN ($roleId,$fedWrkgrpRoleId)";
            }
        }

        $fedConfCheck = '';
        if (($clubType == 'federation') || ($clubType == 'sub_federation')) {
            $fedConfCheck = " AND (
				(r.contact_id IS NULL) 
				OR 
				(r.contact_id IS NOT NULL AND
				    (
					(c.is_fed_membership_confirmed='0') 
					OR 
					(c.is_fed_membership_confirmed='1' AND c.old_fed_membership_id IS NOT NULL)
                                    )
				)
			) ";
        }
        $clubTitleQuery = "SELECT COALESCE(NULLIF(Ci18N.title_lang,''),FC.title) AS title FROM fg_cm_contact CT LEFT JOIN fg_club FC ON CT.main_club_id = FC.id LEFT JOIN fg_club_i18n Ci18N ON Ci18N.id = FC.id AND Ci18N.lang = '$clubDefaultLang' WHERE CT.id = r.changed_by";
        $sql = "SELECT r.id, r.club_id, r.date AS dateOriginal, r.kind, r.field, r.value_before, r.value_after,date_format( r.date,'". $dateFormat ."') AS date,checkActiveContact(r.changed_by, $clubId) as activeContact,r.changed_by,
          IF((checkActiveContact(r.changed_by, $clubId) is null && r.changed_by != 1), CONCAT(contactName(r.changed_by),' ( ',($clubTitleQuery),' )') , contactName(r.changed_by) )as editedBy,
                    IF((r.kind = 'data'), 'data', 'assignments') AS tabGroups,
                    (CASE WHEN ((r.value_before IS NOT NULL AND r.value_before != '' AND r.value_before != '-') AND (r.value_after IS NULL OR r.value_after = '' OR r.value_after = '-')) THEN 'removed'
                           WHEN ((r.value_before IS NULL OR r.value_before = '' OR r.value_before = '-') AND (r.value_after IS NOT NULL AND r.value_after != '' AND r.value_after != '-')) THEN 'added'
                           WHEN ((r.value_before IS NOT NULL AND r.value_before != '' AND r.value_before != '-') AND (r.value_after IS NOT NULL AND r.value_after != '' AND r.value_after != '-') AND (r.value_before != r.value_after)) THEN 'changed'
                           ELSE 'none'
                    END) AS status,
                    (IF((r.value_after='' OR r.value_after IS NULL OR r.value_after='-'),r.value_before,r.value_after)) AS columnVal3
                    FROM fg_rm_role_log r 
                    LEFT JOIN fg_cm_contact c ON r.contact_id=c.id
                    LEFT JOIN fg_rm_role rr ON rr.id=r.role_id
                    LEFT JOIN fg_rm_category rc ON rr.category_id=rc.id
                    LEFT JOIN fg_club cc ON rc.club_id=cc.id
                    WHERE r.kind IN ('data', 'assigned contacts')   $fedConfCheck
                    $cond
                    ORDER BY DATE(r.date) DESC";

        $result = $conn->executeQuery($sql)->fetchAll();
        $conn->close();

        return $result;
    }

}
