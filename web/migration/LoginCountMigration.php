<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DocumentCountMigration
 *
 * @author jinesh.m
 */
class LoginCountMigration
{

    /**
     * $em.
     *
     * @var object entitymanager object
     */
    private $conn;
    private $conn2;
    private $log;

    /**
     * Constructor for initial setting.
     *
     * @param type $conn   container
     */
    public function __construct($conn, $conn1)
    {
        $this->conn = $conn;
        $this->conn2 = $conn1;

        $this->log = fopen('logincount_migration_log_' . date('dHis') . '.txt', 'w');
    }

    /**
     * Function to init federation icon migration
     * @throws \Clubadmin\ContactBundle\Util\Exception
     */
    public function InitLoginCountMigration()
    {
        try {
            //$this->conn2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            //$this->conn2->beginTransaction();
            $this->countUpdate();
            $this->writeLog("login count migration executed successfullyyy \n");
            //$this->conn2->commit();
        } catch (Exception $ex) {
            //$this->conn2->rollback();
            echo "Failed: " . $ex->getMessage();
            throw $ex;
        }
    }

    /**
     * Function to migrate fg_sm_bookmarks
     */
    private function countUpdate()
    {
        //Active contact count
        /* ----------------------- UPDATE ACTIVE CONTACT COUNT AND LOGIN COUNT PROCESS START --------------------------------------------- */
        $clubDetailsQuery = mysqli_query($this->conn, "Select fc.id,fc.title,fc.federation_id,fc.sub_federation_id,fc.parent_club_id,fc.club_type from fg_club fc");

        while ($clubDetail = mysqli_fetch_assoc($clubDetailsQuery)) {
            $this->writeLog(" ----UPDATE ACTIVE CONTACT COUNT AND LOGIN COUNT PROCESS START({$clubDetail['id']}) ---- \n");
            if ($clubDetail['id'] != 1) {

                switch ($clubDetail['club_type']) {
                    case 'federation':
                        $from = " master_federation_{$clubDetail['id']} as mc INNER JOIN fg_cm_contact on mc.fed_contact_id = fg_cm_contact.fed_contact_id";
                        break;
                    case 'sub_federation':
                        $from = "master_federation_{$clubDetail['id']} as mc INNER JOIN fg_cm_contact on mc.contact_id = fg_cm_contact.subfed_contact_id";
                        break;
                    default:
                        $from = "master_club_{$clubDetail['id']} as mc INNER JOIN fg_cm_contact on mc.contact_id = fg_cm_contact.id";
                }

                if ($clubDetail['club_type'] != 'federation' && $clubDetail['federation_id'] > 1) {
                    $from .= " LEFT JOIN master_federation_{$clubDetail['federation_id']} as mf on (mf.fed_contact_id = fg_cm_contact.fed_contact_id )";
                }
                //subfederation field is exist or not
                if ($clubDetail['club_type'] != 'sub_federation' && $clubDetail['sub_federation_id'] > 0) {
                    $from .= " LEFT JOIN master_federation_{$clubDetail['sub_federation_id']} as msf on (msf.contact_id =  fg_cm_contact.subfed_contact_id )";
                }
                $from .= " INNER JOIN master_system as ms on ms.fed_contact_id = fg_cm_contact.fed_contact_id";

                $where = " fg_cm_contact.is_permanent_delete=0 	AND fg_cm_contact.is_deleted=0 AND fg_cm_contact.is_draft=0 ";
                if ($clubDetail['club_type'] == 'federation' || $clubDetail['club_type'] == 'sub_federation') {
                    //check if contact has approved fed membership
                    $fedmemberApprovedConditions = " AND (fg_cm_contact.is_fed_membership_confirmed='0' OR (fg_cm_contact.is_fed_membership_confirmed='1' AND fg_cm_contact.old_fed_membership_id IS NOT NULL))  ";
                    $where .= " AND fg_cm_contact.club_id={$clubDetail['id']} AND (fg_cm_contact.main_club_id={$clubDetail['id']} OR fg_cm_contact.fed_membership_cat_id IS NOT NULL){$fedmemberApprovedConditions}";
                } else {
                    $where .= " AND fg_cm_contact.club_id={$clubDetail['id']}";
                }

                $result = mysqli_query($this->conn2, "SELECT count(fg_cm_contact.id) as total,sum(fg_cm_contact.login_count) as loginCount  from {$from} where {$where}");
                $data = mysqli_fetch_assoc($result);
                $activeContactCount = ($data['total'] != '') ? $data['total'] : 0;
                $loginCount = ($data['loginCount'] != '') ? $data['loginCount'] : 0;

                $this->writeLog(" ----UPDATE ACTIVE CONTACT COUNT AND LOGIN COUNT PROCESS END---- \n");

                /* ------------------UPDATE ACTIVE CONTACT COUNT AND LOGIN COUNT PROCESS END----------------------------------- */

                /* -------------------UPDATE ADMIN COUNT AND LAST LOGGIN ADMIN ID PROCESS START--------------------------------- */
                $inCondition = ($clubDetail['club_type'] == 'federation') ? "9,10,11,12,13,15,17,18,19,21" : "9,10,11,12,13,15,18,19,21";
                $query = mysqli_query($this->conn2, "Select count(sfu.id) as admin_count,sfu.contact_id as lastLoginId, max(sfu.last_login) from sf_guard_user sfu inner join sf_guard_user_group sfug on sfu.id =sfug.user_id where sfug.group_id in({$inCondition}) and sfu.club_id={$clubDetail['id']}  group by sfu.club_id ");
                $userdata = mysqli_fetch_assoc($query);
                $adminCount = 0;
                $adminId = 'null';
                if (count($userdata) > 0) {
                    $adminCount = ($userdata['admin_count'] != '') ? $userdata['admin_count'] : 0;
                    $adminId = ($userdata['lastLoginId'] == '') ? null : $userdata['lastLoginId'];
                }
//                if ($clubDetail['id'] == 8516) {
//                    echo "UPDATE fg_club SET active_contact_count=$activeContactCount,contact_login_count=$loginCount,last_login_admin_contact=$adminId,admin_count=$adminCount WHERE id={$clubDetail['id']}";
//                }

                mysqli_query($this->conn, "UPDATE fg_club SET active_contact_count=$activeContactCount,contact_login_count=$loginCount,last_login_admin_contact=$adminId,admin_count=$adminCount WHERE id={$clubDetail['id']}");

                $this->writeLog(" ----UPDATE ADMIN COUNT AND LAST LOGGIN ADMIN ID PROCESS END({$clubDetail['id']})---- \n");
                /* -------------------UPDATE ADMIN COUNT AND LAST LOGGIN ADMIN ID PROCESS END--------------------------------- */
            }
        }

        mysqli_close($this->conn);

        $this->writeLog("actice contact count migration successfully executed \n");
    }

    private function writeLog($msg)
    {
        fwrite($this->log, $msg);
        echo nl2br($msg);
    }
}
