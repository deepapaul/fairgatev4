<?php
/*
 * This class to sync the data from the fairgate DB to FAdmin DB
 * 
 */
namespace Common\UtilityBundle\Util;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Common\UtilityBundle\Util\FgMySqlSyncData;
use Symfony\Component\Process\Process;

/**
 * This class to sync the data from the fairgate DB to FAdmin DB.
 *
 * @author pitsolutions <pitsolutions.ch>
 */
class FgClubSyncDataToAdmin
{

    /**
     * @var object Container variable 
     */
    public $container;

    /**
     * @var object Connection variable for admin connection
     */
    private $adminConnection;

    /**
     * @var object Connection variable for fairgate connection
     */
    private $connection;

    /**
     * @var object Entity variable for fairgate connection
     */
    private $em;

    /**
     * @var string The path where the console application is been set
     */
    private $consolePath;

    /**
     * Constructor of FgAdminConnection class.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->adminConnection = new FgMySqlSyncData($container);
        $this->em = $this->container->get('doctrine')->getManager();
        $this->connection = $this->em->getConnection();
        $this->consolePath = $this->container->get('kernel')->getRootDir()."/../bin/console";
    }

    /**
     * The function to update the name of a contact
     * 
     * @param array/int $clubId
     * 
     * @return \Common\UtilityBundle\Util\FgClubSyncDataToAdmin
     */
    public function updateSubscriberCount($clubId)
    {
//        $process = new Process("php {$this->consolePath}  syncclubdata:common updateSubscriberCountExecute $clubId");
//        $process->start();
        exec("php {$this->consolePath}  syncclubdata:common updateSubscriberCountExecute $clubId");
        return $this;
    }

    /**
     * The function to update the total admins to the Admin DB
     * 
     * @param array/int $clubId
     * 
     * @return \Common\UtilityBundle\Util\FgClubSyncDataToAdmin
     */
    public function updateAdminCount($clubId)
    {
//        $process = new Process("php {$this->consolePath}  syncclubdata:common updateAdminCountExecute $clubId");
//        $process->start();
        exec("php {$this->consolePath}  syncclubdata:common updateAdminCountExecute $clubId");
        return $this;
    }

    /**
     * The function to update the total admins to the Admin DB
     * 
     * @param array/int $clubId
     * 
     * @return \Common\UtilityBundle\Util\FgClubSyncDataToAdmin
     */
    public function updateActiveContactCount($clubId)
    {
//        $process = new Process("php {$this->consolePath}  syncclubdata:common updateActiveContactCountExecute $clubId  >> active.log");
//        $process->start();
        exec("php {$this->consolePath}  syncclubdata:common updateActiveContactCountExecute $clubId  >> active.log");
        
        return $this;
    }

    /**
     * The function to update the name of a contact
     * 
     * @param array/int $clubId
     * 
     * @return \Common\UtilityBundle\Util\FgClubSyncDataToAdmin
     */
    public function updateSubscriberCountExecute($clubId)
    {
        $queryString = $this->getSubscriberCountSyncQuery($clubId);
        if ($queryString != '') {
            $this->adminConnection->executeQuery($queryString);
        }
    }

    /**
     * The function to update the total admins to the Admin DB
     * 
     * @param array/int $clubId
     * 
     * @return \Common\UtilityBundle\Util\FgClubSyncDataToAdmin
     */
    public function updateAdminCountExecute($clubId)
    {
        $queryString = $this->getAdminCountSyncQuery($clubId);
        if ($queryString != '') {
            $this->adminConnection->executeQuery($queryString);
        }
    }

    /**
     * To update active contact count
     * 
     * @param array/int $clubId
     * 
     */
    public function updateActiveContactCountExecute($clubId)
    {
        if ($clubId != '') {
            $this->queryString = $this->getActiveContactCount($clubId);
            if (count($this->queryString) > 0) {
                $queryString = implode('', $this->queryString);
                $this->adminConnection->executeQuery($queryString);
            }
        }
    }

    /**
     * 
     * @param int $clubId
     * @return string
     */
    private function getSubscriberCountSyncQuery($clubId)
    {
        $queryString = '';

        $queryStatement = $this->connection->executeQuery('SELECT club_type,id,federation_id,sub_federation_id FROM `fg_club` WHERE id = (?)', array($clubId), array(\PDO::PARAM_INT));
        $clubData = $queryStatement->fetch(\PDO::FETCH_NUM);

        if (is_array($clubData)) {
            list($clubType, $clubId, $federationId, $subFederationId) = $clubData;

            $subscriberContact = 'SELECT COUNT(id) AS subscriberCount  FROM `fg_cn_subscriber` WHERE `club_id` = ' . $clubId;
            $ownContact = 'SELECT count(fg_cm_contact.id) as subscriberCount';
            $ownContactWhere = '';

            switch ($clubType) {
                case 'federation':
                    $ownContact .= " FROM master_federation_{$clubId} as mc INNER JOIN fg_cm_contact on mc.fed_contact_id = fg_cm_contact.fed_contact_id";
                    $ownContactWhere = " WHERE fg_cm_contact.is_permanent_delete=0 AND fg_cm_contact.is_deleted=0 AND fg_cm_contact.club_id={$clubId} AND (fg_cm_contact.main_club_id={$clubId} OR fg_cm_contact.fed_membership_cat_id IS NOT NULL) AND (fg_cm_contact.is_fed_membership_confirmed='0' OR (fg_cm_contact.is_fed_membership_confirmed='1' AND fg_cm_contact.old_fed_membership_id IS NOT NULL)) AND fg_cm_contact.is_draft=0 AND ( fg_cm_contact.is_subscriber=1 AND (TRIM(`3`) != '' AND `3` IS NOT NULL))";
                    break;
                case 'sub_federation':
                    $ownContact .= " FROM master_federation_{$clubId} as mc INNER JOIN fg_cm_contact on mc.contact_id = fg_cm_contact.subfed_contact_id";
                    $ownContactWhere = " WHERE fg_cm_contact.is_permanent_delete=0 AND fg_cm_contact.is_deleted=0 AND fg_cm_contact.club_id={$clubId} AND (fg_cm_contact.main_club_id={$clubId} OR fg_cm_contact.fed_membership_cat_id IS NOT NULL) AND (fg_cm_contact.is_fed_membership_confirmed='0' OR (fg_cm_contact.is_fed_membership_confirmed='1' AND fg_cm_contact.old_fed_membership_id IS NOT NULL)) AND fg_cm_contact.is_draft=0 AND ( fg_cm_contact.is_subscriber=1 AND (TRIM(`3`) != '' AND `3` IS NOT NULL))";
                    break;
                default:
                    $ownContact .= " FROM master_club_{$clubId} as mc INNER JOIN fg_cm_contact on mc.contact_id = fg_cm_contact.id";
                    $ownContactWhere = " WHERE fg_cm_contact.is_permanent_delete=0 AND fg_cm_contact.is_deleted=0 AND fg_cm_contact.club_id={$clubId} AND fg_cm_contact.is_draft=0 AND ( fg_cm_contact.is_subscriber=1 AND (TRIM(`3`) != '' AND `3` IS NOT NULL))";
                    break;
            }

            $ownContact .= ' INNER JOIN master_system as ms on ms.fed_contact_id = fg_cm_contact.fed_contact_id';

            if ($clubType != 'federation' && $federationId > 1) {
                $ownContact .= " LEFT JOIN master_federation_{$federationId} as mf on (mf.fed_contact_id = fg_cm_contact.fed_contact_id )";
            }
            if ($clubType != 'sub_federation' && $subFederationId > 1) {
                $ownContact .= " LEFT JOIN master_federation_{$subFederationId} as msf on (msf.contact_id =  fg_cm_contact.subfed_contact_id )";
            }

            $finalQueryString = "SELECT SUM(subscriberCount) AS totalSubscribers FROM ( " . $ownContact . $ownContactWhere . ' UNION ' . $subscriberContact . ') countResult';
            $totalSubscribers = $this->connection->executeQuery($finalQueryString)->fetch();
            $queryString = "UPDATE fg_club SET newsletter_subscriber_count = " . $totalSubscribers['totalSubscribers'] . " WHERE id = " . $clubId . ";";
        }
        return $queryString;
    }

    /**
     * 
     * @param int $clubId
     * @return string
     */
    private function getAdminCountSyncQuery($clubId)
    {
        $adminCountQuery = "SELECT SUM(admins) AS adminCount FROM ( 
                                SELECT COUNT(DISTINCT c.id) AS admins FROM fg_cm_contact c
                                INNER JOIN sf_guard_user ON sf_guard_user.contact_id = c.id
                                INNER JOIN sf_guard_user_group ON sf_guard_user_group.user_id = sf_guard_user.id
                                WHERE  c.is_permanent_delete=0 AND c.is_deleted=0 AND c.club_id={$clubId} AND c.is_draft=0 AND sf_guard_user_group.group_id  NOT IN (1,7,8)
                                UNION
                                SELECT COUNT(id) AS admins FROM fg_cm_contact WHERE is_fed_admin = 1 AND club_id = {$clubId}
                        ) countResult";
        $totalAdmins = $this->connection->executeQuery($adminCountQuery)->fetch();
        $queryString = "UPDATE fg_club SET admin_count = " . $totalAdmins['adminCount'] . " WHERE id = " . $clubId . ";";
        return $queryString;
    }

    /**
     * 
     * @param type $clubId current club Id
     * @param type $clubType current club type
     */
    private function getActiveContactCount($clubId)
    {
       echo "##################################################". date("d-m-Y H:i")." ##################################################\n"; 
       echo "CLubid".$clubId;
        $sql = "SELECT id,federation_id,sub_federation_id,parent_club_id,club_type FROM fg_club  WHERE id =:Id";
        $stmt = $this->adminConnection->adminConnection->executeQuery($sql, array('Id' => $clubId));
        $clubDetails = $stmt->fetchAll();
        $queryString = array();
        $clubHeirarchy = array();
        if ($clubDetails[0]['club_type'] == 'federation') {
            $fedsql = "SELECT id,federation_id,sub_federation_id,parent_club_id,club_type FROM fg_club  WHERE federation_id =:fedId";
            $fedstmt = $this->adminConnection->adminConnection->executeQuery($fedsql, array('fedId' => $clubId));
            $clubDetails = $fedstmt->fetchAll();
        }
        foreach ($clubDetails as $clublevelDetail) {
            if ($clublevelDetail['id'] != 1) {

                if ($clublevelDetail['federation_id'] > 1) {
                    $clubHeirarchy[$clublevelDetail['federation_id']] = array('id' => $clublevelDetail['federation_id'], 'federation_id' => $clublevelDetail['federation_id'], 'sub_federation_id' => $clublevelDetail['sub_federation_id'], 'club_type' => 'federation');
                }
                if ($clublevelDetail['sub_federation_id'] > 0) {
                    $clubHeirarchy[$clublevelDetail['sub_federation_id']] = array('id' => $clublevelDetail['sub_federation_id'], 'federation_id' => $clublevelDetail['federation_id'], 'sub_federation_id' => $clublevelDetail['sub_federation_id'], 'club_type' => 'sub_federation');
                }
                $clubHeirarchy[$clublevelDetail['id']] = array('id' => $clublevelDetail['id'], 'federation_id' => $clublevelDetail['federation_id'], 'sub_federation_id' => $clublevelDetail['sub_federation_id'], 'club_type' => $clublevelDetail['club_type']);
            }

            foreach ($clubHeirarchy as $clubDetail) {

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

                $result = $this->connection->executeQuery("SELECT count(fg_cm_contact.id) as total  FROM {$from} WHERE {$where}");
                $data = $result->fetchAll();
                echo "updatedClub with count is ".$clubDetail['id']."@@@". $data[0]['total']."\n";
                file_put_contents('activecontactcount.txt', "UPDATE fg_club SET active_contact_count = " . $data[0]['total'] . " WHERE id = " . $clubDetail['id'] . "; \n", FILE_APPEND);
                $queryString[] = "UPDATE fg_club SET active_contact_count = " . $data[0]['total'] . " WHERE id = " . $clubDetail['id'] . ";";
            }
        }
echo "$$$$$$$$$$$$$$$$$$$ END$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$\n";
        return $queryString;
    }
}
