<?php

/**
 * InternalTeamPdo
 *
 * This class is used for handling team/workgroup related queries in internal section.
 *
 * @package    CommonUtilityBundle
 * @subpackage Repository
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 *
 */

namespace Common\UtilityBundle\Repository\Pdo;

use Common\UtilityBundle\Util\FgSettings;

/**
 * InternalTeamPdo
 *
 * @author pitsolutions.ch
 */
class InternalTeamPdo
{

    /**
     * Connection variable
     */
    public $conn;

    /**
     * Container variable
     */
    public $container;

    /**
     * Constructor for initial setting.
     *
     * @param object $container Container Object
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->conn = $this->container->get('database_connection');
        $this->em = $this->container->get('doctrine')->getManager();
    }

    /**
     * Function to get the details of a topic and its post
     *
     * @param int $clubId  ClubId
     * @param int $topicId TopicId
     * @param int $page    Page number
     * @param int $limit   Posts per page
     *
     * @return array $result Topic details
     */
    public function getTopicDetails($clubId, $topicId, $page = 1, $limit = 20)
    {
        $datetimeFormat = FgSettings::getMysqlDateTimeFormat();
        $offset = ($page == 1) ? 0 : $limit * ($page - 1);
        $masterTable = $this->container->get('club')->get('clubTable');
        $clubType = $this->container->get('club')->get('type');
        $contactId = $this->container->get('contact')->get('id');
        $profileImgField = $this->container->getParameter('system_field_communitypicture');
        $companyLogoField = $this->container->getParameter('system_field_companylogo');
        $contactField = ($clubType == 'federation') ? 'mc.fed_contact_id' : 'mc.contact_id';
        
        $sql = "SELECT DISTINCT(f.id) as topicId,  f.title, (CASE WHEN f.replies = 'allowed' THEN '1' ELSE '0' END) AS isRepliesAllowed, f.is_important as isImportant, (CASE WHEN f.is_closed = 1 THEN '1' ELSE '0' END) as isClosed, (CASE WHEN r.is_deactivated_forum = 1 THEN '1' ELSE '0' END) as isDeactivated, "
                . "f.group_id AS roleId, d.post_content as content, (DATE_FORMAT(d.created_at, '$datetimeFormat')) as createdDate, contactNameNoSort(d.created_by, 0) as createdBy, d.created_by as createdById, "
                . "(DATE_FORMAT(d.updated_at, '$datetimeFormat')) as updatedDate, contactNameNoSort(d.updated_by, 0) as updatedBy, d.unique_post_id as uniqueId, d.id as magicId, r.type as roleType, "
                . "d.id as topicDataId, (SELECT COUNT(td.id) FROM fg_forum_topic_data td WHERE td.forum_topic_id=:topicId) as postCount, "
                . "CASE WHEN (c.is_company = 1) THEN ms.$companyLogoField ELSE ms.$profileImgField END as profileImg, c.is_stealth_mode as isStealthMode, c.club_id as contactClubId, c.created_club_id as createdClubId, c.is_company as isCompany, u.is_super_admin as isSuperAdmin, c.is_fed_admin as isFedAdmin,  "
                . " CASE WHEN (fc.is_notification_enabled IS NULL) THEN 0 ELSE fc.is_notification_enabled END as isFollower "
                . "FROM fg_forum_topic f "
                . "LEFT JOIN fg_forum_topic_data d ON d.forum_topic_id = f.id "
                . "LEFT JOIN fg_rm_role r ON r.id = f.group_id AND r.is_active = 1 AND r.club_id = f.club_id "
                . "LEFT JOIN $masterTable mc ON $contactField = d.created_by "
                . "LEFT JOIN fg_cm_contact c ON c.id = d.created_by "
                . "LEFT JOIN master_system ms ON ms.fed_contact_id = c.fed_contact_id "
                . "LEFT JOIN sf_guard_user u ON (u.contact_id = c.id AND (u.is_super_admin = 1 OR u.is_super_admin != 1 AND u.club_id = c.club_id)) "
                . "LEFT JOIN fg_forum_contact_details fc ON (fc.forum_topic_id = f.id  AND fc.contact_id = :contactId) "
                . "WHERE f.id = :topicId AND f.club_id = :clubId "
                . "ORDER BY d.unique_post_id, d.created_at ASC "
                . "LIMIT $offset, $limit";
        $result = $this->conn->fetchAll($sql, array('topicId' => $topicId, 'clubId' => $clubId, 'contactId' => $contactId));

        return $result;
    }

    /**
     * Function to check if unread posts are there or not for this topic
     *
     * @param int $topicId   Topic id
     * @param int $contactId Contact Id
     *
     * @return array $unreadCount Unread count
     */
    public function checkIfUnreadPostsAreThere($topicId, $contactId)
    {
        $sql = "SELECT COUNT(F.id) AS unreadCount FROM fg_forum_topic_data F LEFT JOIN fg_forum_contact_details FD ON (FD.forum_topic_id = F.forum_topic_id AND FD.contact_id = $contactId) WHERE F.forum_topic_id = $topicId AND (FD.id IS NULL OR (F.created_at > FD.read_at)) ORDER BY F.created_at DESC LIMIT 1";
        $result = $this->conn->fetchAll($sql, array('topicId' => $topicId, 'contactId' => $contactId));
        $unreadCount = $result[0]['unreadCount'];

        return $unreadCount;
    }
    
    /**
     * Method to get first 7 forums details to display in overview (PERSONAL/TEAM/WORKGROUP) in the order of updation
     * In case of team and workgroup overview, that specific team/workgroup-id is there in $administrativeRoles or $memberRoles
     * 
     * @param string $executiveBoardTitle executive board title translation
     * @param array  $administrativeRoles teams/workgroups which the contact has administration roles
     * @param array  $memberRoles         teams/workgroups which the contact has member roles (administrative roles are excluded from this)
     * 
     * @return array $result forums details array
     */
    public function getPersonalForumList($executiveBoardTitle, $administrativeRoles, $memberRoles)
    {
        $contactId = $this->container->get('contact')->get('id');
        $clubId  = $this->container->get('club')->get('id');
        $masterTable = $this->container->get('club')->get('clubTable'); 
        $clubtype = $this->container->get('club')->get('type');
        $mainfgcontactIdField = 'mc.contact_id';
        switch ($clubtype) {
            case 'federation':
                $mcfrom = "  INNER JOIN fg_cm_contact on mc.fed_contact_id = fg_cm_contact.fed_contact_id";
                $mainfgcontactIdField = 'mc.fed_contact_id';
                break;
            case 'sub_federation':
                $mcfrom = "INNER JOIN fg_cm_contact on mc.contact_id = fg_cm_contact.subfed_contact_id";
               break;
            default:
                $mcfrom = " INNER JOIN fg_cm_contact on mc.contact_id = fg_cm_contact.id";
        }
        
        $sql = "SELECT FT.id, FT.title, FD.unique_post_id as postId, FT.created_by as forumCreatedById, contactNameNoSort(FT.created_by, 0) AS forumCreatedby, "
                . "CASE WHEN R.type = 'W' THEN 'workgroup' ELSE 'team' END AS forumType, R.id AS roleId, "
                . "CASE WHEN R.is_executive_board = 1 THEN '$executiveBoardTitle' ELSE R.title END AS roleTitle, "
                . "CASE WHEN (FD.updated_at IS NULL OR FD.updated_at = '') THEN FD.created_at ELSE FD.updated_at END AS updatedAt,"
                . "CASE WHEN (FD.created_at > FC.read_at OR FC.read_at IS NULL ) THEN '1' ELSE '0' END AS isUnread, "
                . "CASE WHEN (fg_cm_contact.is_stealth_mode = 1 OR SF.is_super_admin = 1) THEN 1 ELSE 0 END AS hideProfile, fg_cm_contact.is_stealth_mode "
                . "FROM fg_forum_topic FT "
                . "LEFT JOIN fg_forum_topic_data FD ON (FD.forum_topic_id = FT.id AND FD.id = (SELECT MAX(id) FROM fg_forum_topic_data WHERE forum_topic_id = FT.id)) "
                . "LEFT JOIN fg_rm_role R ON R.id = FT.group_id AND R.is_active = 1 AND R.club_id = FT.club_id "
                . "LEFT JOIN fg_forum_contact_details FC ON (FC.forum_topic_id = FT.id  AND FC.contact_id = :contactId) "
                . "LEFT JOIN sf_guard_user SF ON SF.contact_id = FT.created_by "
                . "LEFT JOIN $masterTable mc ON $mainfgcontactIdField = FT.created_by "
                    . " $mcfrom "
                . "WHERE FT.club_id = :clubId "
                . "AND (R.id IN ( ".implode(',',$administrativeRoles)." )   "
                . "OR ( R.id IN (".implode(',',$memberRoles).") AND (R.is_deactivated_forum != 1 OR R.is_deactivated_forum IS NULL ) AND (FT.is_closed != 1 OR FT.is_closed IS NULL OR FT.created_by = :contactId ) )"//                
                . " ) "
                . "GROUP BY FT.id "
                . "ORDER BY updatedAt DESC "
                . "LIMIT 0,7 ";
       
        $result = $this->conn->fetchAll($sql, array('contactId' => $contactId, 'clubId' => $clubId ));

        return $result;
    }
}
