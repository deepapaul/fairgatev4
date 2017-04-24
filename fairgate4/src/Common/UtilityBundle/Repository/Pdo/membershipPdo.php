<?php

namespace Common\UtilityBundle\Repository\Pdo;

/**
 * membershipPdo.
 *
 * This class is used for memebership related pdo queries
 *
 */
class membershipPdo
{

    private $conn;
    private $container;
    private $em;

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
     * Function to get memberships for a perticular Club, Fedration, Fedration Club , subfedration Club or subfedration.
     *
     * @param String  $clubType        Type of Club(Club, Fedration, Fedration Club , subfedration Club or subfedration)
     * @param Integer $clubId          club id
     * @param Integer $subFederationId subfederation Id
     * @param Integer $federationId    Federation Id
     * @param Integer $contactsId      contact Id
     * @param bool    $sortResult      Whether to sort the returning data or not.
     * @param Integer  $club_only       Whether return club only data
     *
     * @return query result or as processed array based on the $exec parameter
     */
    public function getMemberships($clubType = 'standard_club', $clubId = 0, $subFederationId = 0, $federationId = 0, $contactsId = 0, $sortResult = false, $club_only = 0)
    {
        $clubidIn = "'$clubId'";
        $doctrineConfig = $this->em->getConfiguration();
        $doctrineConfig->addCustomStringFunction('FIELD', 'Common\UtilityBundle\Extensions\Field');
        $str_Membershiptype_anc = "(cnt_anc.fed_membership_cat_id = m.id or  cnt_anc.old_fed_membership_id=m.id) AND ";
        $joinStr_anc = $this->setFromMaster($clubType, $clubId, 'cnt_anc');
        $joinStr_anc = '';

        if ($clubType == 'federation') {
            $joinStr = $this->setFromMaster($clubType, $clubId, 'cnt');
            $str_Membershiptype = "cnt.fed_membership_cat_id = m.id AND  (cnt.is_fed_membership_confirmed='0' or(cnt.old_fed_membership_id is not null and cnt.is_fed_membership_confirmed='1' ) ) AND";
        } elseif ($clubType == 'sub_federation') {
            $joinStr_fed = $this->setFromMaster($clubType, $clubId, 'cnt_fed');
            $joinStr = $this->setFromMaster($clubType, $clubId, 'cnt');
            $str_Membershiptype = "cnt.fed_membership_cat_id = m.id AND (cnt.is_fed_membership_confirmed='0' or(cnt.old_fed_membership_id is not null and cnt.is_fed_membership_confirmed='1' ) ) AND ";
        } else {
            $joinStr_fed = $this->setFromMaster($clubType, $clubId, 'cnt_fed');
            $joinStr = $this->setFromMaster($clubType, $clubId, 'cnt');
            $str_Membershiptype = 'cnt.club_membership_cat_id = m.id AND  ';
        }

        $sql = "SELECT m.id, (m.club_id) as clubId, FIELD((m.club_id), $clubidIn) as orderfield, m.title as membershipName,m.sort_order AS sortOrder, mi18.title_lang AS titleLang,
             mi18.lang as language, bm.contact_id AS bookmarked, bm.id AS bookmarkId ,
             (SELECT  count( distinct  cnt.id) FROM fg_cm_contact cnt $joinStr  WHERE $str_Membershiptype  cnt.club_id  =$clubId   ) fnCount";
        if ($club_only == 0) {
            if ($clubType == 'sub_federation_club' || $clubType == 'federation_club') {
                $sql .= " ,(SELECT count(DISTINCT cnt_fed.id)  FROM fg_cm_contact cnt_fed $joinStr_fed WHERE cnt_fed.fed_membership_cat_id= m.id AND cnt_fed.club_id = '$clubId' ) AS cntFed";
            } elseif ($clubType == 'sub_federation') {
                $sql .= " ,(SELECT count(DISTINCT cnt_fed.id)  FROM fg_cm_contact cnt_fed $joinStr_fed WHERE cnt_fed.fed_membership_cat_id= m.id AND (cnt_fed.is_fed_membership_confirmed='0' or(cnt_fed.old_fed_membership_id is not null and cnt_fed.is_fed_membership_confirmed='1' ) ) AND cnt_fed.club_id = '$clubId'  ) AS cntFed";
            }
        }

        if ($clubType == 'federation') {
            $sql .= ", (SELECT  count( distinct  cnt_anc.id) FROM fg_cm_contact cnt_anc $joinStr_anc  WHERE $str_Membershiptype_anc  cnt_anc.club_id  =$clubId  AND cnt_anc.is_fed_membership_confirmed='1'   ) fnCount_anc ";
            $sql .= ", (SELECT COUNT(id) AS applicationPending  FROM `fg_external_application_form` WHERE `fed_membership` = m.id AND `status` = 'pending' AND `club_id` = $clubId) AS pendingAppCount";
        }

        $sql .= ' FROM fg_cm_membership m LEFT JOIN fg_cm_membership_i18n mi18 ON (mi18.id = m.id) '
            . "LEFT JOIN fg_cm_bookmarks bm ON (bm.membership_id = m.id AND bm.club_id = $clubId AND bm.contact_id = $contactsId) ";
        if ($club_only == 0) {
            #to get all membershipdata

            if ($clubType == 'sub_federation_club' || $clubType == 'federation_club') {
                $sql .= "WHERE ( m.club_id =$federationId) OR   m.club_id= $clubId";
            } elseif ($clubType == 'sub_federation') {
                $sql .= "WHERE m.club_id =$federationId";
            } else {
                $sql .= "WHERE m.club_id =$clubId";
            }
        } else {
            $sql .= "WHERE m.club_id =$clubId";
        }
        $sql .= ' ORDER BY orderfield ASC, m.sort_order ASC';
        $dataResult = $this->conn->fetchAll($sql, array());

        if ($sortResult) {
            return $this->sortMembershipData($dataResult);
        } else {
            $result = array();
            $id = '';
            foreach ($dataResult as $key => $arr) {
                if (count($arr) > 0) {
                    if ($arr['id'] == $id) {
                        $result[$id]['allLanguages'][$arr['language']] = array('titleLang' => $arr['titleLang']);
                    } else {
                        $id = $arr['id'];
                        $result[$id] = array(
                            'membershipName' => $arr['membershipName'],
                            'sortOrder' => $arr['sortOrder'],
                            'clubId' => $arr['clubId'],
                            'language' => $arr['language'],
                            'titleLang' => $arr['titleLang'],
                            'bookmarked' => ($arr['bookmarked'] != '') ? '1' : '0',
                            'totalCount' => $arr['fnCount'],
                            'pendingCount' => $arr['fnCount_anc'],
                            'pendingAppCount' => $arr['pendingAppCount'],
                            'bookmarkId' => $arr['bookmarkId'],
                        );
                        if ($clubType == 'sub_federation_club' || $clubType == 'federation_club' || $clubType == 'sub_federation') {
                            $result[$id]['fed'] = $arr['cntFed'];
                        }
                        $result[$id]['allLanguages'][$arr['language']] = array('titleLang' => $arr['titleLang']);
                    }
                }
            }

            return $result;
        }
    }
    
    /**
     * Function to check Team Member Editable
     * @param Integer $contact_id      contactid
     * @param Integer $role_id         roleid
    
     * 
     * @return query result or as processed array based on the $exec parameter
     */
    public function isEditableTeamMember($contact_id, $role_id) {
        $clubId = $this->container->get('club')->get('id');
        $query = 'Select CTCT.confirm_status as confirm_status  , CTCT.type as type,FGC.is_draft as  newlyAdded FROM fg_cm_change_toconfirm  as CTCT LEFT JOIN fg_cm_contact FGC ON  CTCT.contact_id = FGC.id  WHERE  CTCT.contact_id= ' . $contact_id . ' AND CTCT.role_id=' . $role_id . ' AND CTCT.type != "change" ';
        $editConfirmData = $this->conn->fetchAll($query);

        $noAccess = 1;
        if ($editConfirmData[0]['confirm_status'] == 'CONFIRMED' && count($editConfirmData[0]) > 0) {
            $noAccess = 0;
        }
        $query = ' Select DFRC.id as removed , DFRC.is_removed as rmFlag FROM fg_rm_role_contact as DFRC  LEFT JOIN fg_rm_category_role_function DRF ON DRF.id=DFRC.fg_rm_crf_id WHERE DFRC.contact_id=' . $contact_id . ' AND DRF.role_id=' . $role_id . '  AND DFRC.assined_club_id=' . $clubId;
        $editAssignment = $this->conn->fetchAll($query);
        $removeVal = 0;
         
        if ((count($editAssignment) == 0) & $editConfirmData[0]['newlyAdded'] == 0 ){
            return 1;
        }
        foreach ($editAssignment as $editData) {
            if ($editData['rmFlag'] == 0) {
                $removeVal = $removeVal + 1;
            }
        }
       

        if (($noAccess == 0) || ($removeVal == 0 && $editConfirmData[0]['newlyAdded'] == 0) || ($removeVal > 0 ) || ($removeVal == 0 && $editConfirmData[0]['newlyAdded'] == 1) ) {
            $noAccess = 0;
        }

        return $noAccess;
    }

    /**
     * Function to sort membership data.
     *
     * @param array $dataResult Membership data
     *
     * @return array $result Sorted membership data
     */
    private function sortMembershipData($dataResult)
    {
        $result = array();
        $idSortorderArray = array();
        $sortOrder = 1;
        foreach ($dataResult as $data) {
            if (array_key_exists($data['id'], $idSortorderArray)) {
                $result[$idSortorderArray[$data['id']]]['allLanguages'][$data['language']] = array('titleLang' => $data['titleLang']);
            } else {
                $idSortorderArray[$data['id']] = $sortOrder;
                $result[$sortOrder] = array(
                    'id' => $data['id'],
                    'membershipName' => $data['membershipName'],
                    'clubId' => $data['clubId'],
                    'bookmarked' => ($data['bookmarked'] != '') ? '1' : '0',
                    'totalCount' => $data['fnCount'],
                    'pendingCount' => $data['fnCount_anc'],
                    'pendingAppCount' => $data['pendingAppCount'],
                );
                $result[$sortOrder]['allLanguages'][$data['language']] = array('titleLang' => $data['titleLang']);
                $sortOrder++;
            }
        }

        return $result;
    }
    /**
     * Function to join master table.
     *
     * @return string $joinstr
     */
    private function setFromMaster($clubType, $clubId, $contacttbl)
    {
        if ($clubType == 'federation') {
            $joinStr = "inner join master_federation_{$clubId} as mc on mc.fed_contact_id = $contacttbl.fed_contact_id  ";
        } elseif ($clubType == 'sub_federation') {
            $joinStr = " inner join  master_federation_{$clubId} as mc on mc.contact_id = $contacttbl.subfed_contact_id";
        } else {
            $joinStr = " inner join master_club_{$clubId} as mc on mc.contact_id = $contacttbl.id  ";
        }

        $joinStr .= " and $contacttbl.is_deleted=0 and  $contacttbl.is_permanent_delete=0";

        return $joinStr;
    }

    /**
     * get all team categories with details
     *
     * @param object $club club object
     *
     * @return array
     */
    public function getAllTeamCategryDeatails($club)
    {
        $defaultLang = $club->get('default_lang');
        $clubId = $club->get('id');
        $contactId = $club->get('contactId');
        $groupSql = "'TEAM' AS groupTitle,'1' AS draggable";

        $categoryIdSql = " tc.id  AS categoryId";
        $categoryTitleSql = " IF(tci18n.title_lang IS NULL OR tci18n.title_lang='', tc.title, tci18n.title_lang) AS categoryTitle";
        $roleIdSql = "rmr.id AS roleId, rmr.is_active AS isRoleActive";
        $roleTitleSql = "(CASE  WHEN rmri18n.title_lang IS NULL OR rmri18n.title_lang='' THEN rmr.title ELSE rmri18n.title_lang END) AS roleTitle";
        $selectFields = "$groupSql, $categoryIdSql, $categoryTitleSql, fcb.club_id AS bclub,fcb.type AS btype,fcb.id AS bookMarkId, tc.club_id AS catClubId,  $roleIdSql, $roleTitleSql, rmf.id AS functionId,rmr.category_id as category, IF(rmfi18n.title_lang IS NULL OR rmfi18n.title_lang='', rmf.title, rmfi18n.title_lang) AS functionTitle, 'same' AS functionAssign ";
        $joinSql = " LEFT JOIN fg_rm_role AS rmr ON rmr.team_category_id = tc.id "
            . " LEFT JOIN fg_rm_role_i18n AS rmri18n ON rmri18n.id=rmr.id AND rmri18n.lang = '$defaultLang'"
            . " LEFT JOIN fg_rm_role_function AS rmrf ON rmrf.role_id=rmr.id"
            . " LEFT JOIN fg_rm_function AS rmf ON rmf.id = rmrf.function_id AND tc.club_id ='$clubId'   "
            . " LEFT JOIN fg_rm_function_i18n AS rmfi18n ON rmfi18n.id = rmf.id AND rmfi18n.lang = '$defaultLang'"
            . " LEFT JOIN fg_cm_bookmarks AS fcb ON fcb.role_id = rmr.id AND fcb.club_id='$clubId' AND fcb.contact_id='$contactId' AND fcb.type='role'";
        $condSql = "WHERE tc.club_id ='$clubId' ";
        $groupBySql = "";
        $orderBySql = "ORDER BY  tc.sort_order ASC, rmr.sort_order ASC, rmr.id ASC, rmf.is_federation DESC, rmf.sort_order ASC";

        $roleSql = "SELECT $selectFields "
            . "FROM fg_team_category AS tc "
            . " LEFT JOIN fg_team_category_i18n AS tci18n ON tci18n.id = tc.id AND tci18n.lang = '$defaultLang' "
            . "$joinSql "
            . "$condSql "
            . "$groupBySql "
            . "$orderBySql";

        $clubRoleArray = $this->conn->fetchAll($roleSql);

        return $clubRoleArray;
    }

    /**
     * Function to get all role categories ,roles and functions of a club
     *
     * Used in filter
     * @param Object  $club                Club id
     * @param string  $queryType           category/ roles only
     * @param Boolean $getFilterRoles      Whether to return filter roles
     * @param String  $executiveBoardTitle Executiveboard title
     *
     * @return array $resultArray
     */
    public function getAllCategoryRoleFunction($club, $queryType = 'rolesonly', $getFilterRoles = false, $executiveBoardTitle = '')
    {
        $defaultLang = $club->get('default_lang');
        $clubType = $club->get('type');
        $clubId = $club->get('id');
        $contactId = $club->get('contactId');
        $clubWorkgroupId = $club->get('club_workgroup_id');
        $clubExecutiveBoardId = $club->get('club_executiveboard_id');
        $clubHeirarchyArray = $club->get('clubHeirarchy');
        $clubHeirarchy = implode(',', $clubHeirarchyArray);
        $fedSortSql = '';
        $fedGroupSql = '';
        $sort = 1;
        foreach ($clubHeirarchyArray as $key => $clubsId) {
            $sort = $sort + 1;
            $fedSortSql .= "WHEN (rmc.club_id = '$clubsId' AND rmc.is_fed_category=1) THEN $sort ";
            $fedGroupSql .= "WHEN (rmc.club_id = '$clubsId' AND rmc.is_fed_category=1) THEN 'FROLES-$clubsId' ";
        }
        if ($queryType == 'rolesonly') {
            if (in_array($clubType, array('federation', 'sub_federation'))) {
                $sort = $sort + 1;
                $fedSortSql .= "WHEN (rmc.club_id = '$clubId' AND rmc.is_fed_category=1) THEN $sort ";
            }
        }
        $sortsql = "(CASE WHEN (rmc.club_id = '$clubId' AND rmc.is_fed_category=0 AND rmc.is_team =0 AND rmc.is_workgroup =0 AND rmc.contact_assign = 'manual') THEN 1 $fedSortSql WHEN (rmc.club_id = '$clubId' AND rmc.is_fed_category=1) THEN " . ($sort + 1) . " "
            . "WHEN (rmc.club_id = '$clubId' AND rmc.is_fed_category=0 AND rmc.is_team =0 AND rmc.is_workgroup =0 AND rmc.contact_assign = 'filter-driven') THEN " . ($sort + 2) . " "
            . "WHEN rmc.is_team =1 THEN " . ($sort + 3) . " WHEN rmc.is_workgroup=1 THEN " . ($sort + 4) . " END) AS sort";
        $groupSql = "(CASE WHEN (rmc.club_id = '$clubId' AND rmc.is_fed_category=0 AND rmc.is_team =0 AND rmc.is_workgroup =0 AND rmc.contact_assign = 'manual') THEN 'ROLES-$clubId' $fedGroupSql WHEN (rmc.club_id = '$clubId' AND rmc.is_fed_category=1) THEN 'FROLES-$clubId' "
            . "WHEN (rmc.club_id = '$clubId' AND rmc.is_fed_category=0 AND rmc.is_team =0 AND rmc.is_workgroup =0 AND rmc.contact_assign = 'filter-driven') THEN 'FILTERROLES-$clubId' WHEN rmc.is_team =1 THEN 'TEAM' WHEN rmc.is_workgroup=1 THEN 'WORKGROUP' END) AS groupTitle";
        $draggableSql = "(CASE WHEN (rmc.contact_assign = 'manual') THEN 1 WHEN  (rmc.contact_assign = 'filter-driven') THEN 0  END) AS draggable";
        $filterRoleSql = $getFilterRoles ? "" : "AND rmc.contact_assign = 'manual'"; //for filter roles condition
        if ($queryType == 'rolesonly') {
            $categoryIdSql = "(CASE WHEN (rmc.club_id <>'$clubId' AND rmr.is_executive_board=1) THEN '$clubWorkgroupId' ELSE rmc.id END) AS categoryId";
            $teamCatIdSql = "(CASE WHEN (rmr.type = 'T') THEN rmr.team_category_id ELSE '' END) AS teamCatId";
            $categoryTitleSql = "IF(rmci18n.title_lang IS NULL OR rmci18n.title_lang='', rmc.title, rmci18n.title_lang) AS categoryTitle";
            $roleIdSql = "(CASE WHEN (rmc.club_id !='$clubId' AND rmr.is_executive_board=1) THEN '$clubExecutiveBoardId' ELSE rmr.id END) AS roleId";
            $selectFields = "$categoryIdSql, $teamCatIdSql, $categoryTitleSql,rmr.category_id as category, IF((rmc.is_team OR rmc.is_workgroup),($clubId),(rmc.club_id)) AS catClubId, rmc.function_assign AS functionAssign, $roleIdSql, IF(rmri18n.title_lang IS NULL OR rmri18n.title_lang='', rmr.title, rmri18n.title_lang) AS roleTitle,  rmc.is_fed_category AS isFedCat,$sortsql ";
            $joinSql = "LEFT JOIN fg_team_category AS tc ON rmr.team_category_id = tc.id "
                . "LEFT JOIN fg_rm_role_function AS rmrf ON rmrf.role_id = rmr.id ";
            $condSql = "WHERE (rmc.club_id ='$clubId' " . ($clubHeirarchy != '' ? "OR (rmc.club_id IN ($clubHeirarchy) AND ((rmc.club_id !='$clubId' AND rmr.is_executive_board=1 AND rmc.is_fed_category = 0) OR rmc.is_fed_category=1)) " : '') . ") AND rmc.is_active=1 AND rmr.id IS NOT NULL AND ((rmc.function_assign!= 'none' AND rmrf.id IS NOT NULL) OR rmc.function_assign= 'none')";
            $groupBySql = "GROUP BY categoryId, roleId ";
            $orderBySql = "ORDER BY rmc.is_team DESC, rmc.is_workgroup DESC, sort ASC, rmc.sort_order ASC, tc.sort_order ASC, rmr.sort_order ASC";
        } else if ($queryType == 'filteronly') {
            $categoryIdSql = "(CASE  WHEN (rmc.club_id <>'$clubId' AND rmr.is_executive_board=1 AND rmf.is_federation = 1) THEN '$clubWorkgroupId' ELSE rmc.id END) AS categoryId";
            $categoryTitleSql = "IF(rmci18n.title_lang IS NULL OR rmci18n.title_lang='', rmc.title, rmci18n.title_lang) AS categoryTitle";
            $roleIdSql = "(CASE WHEN (rmc.club_id !='$clubId' AND rmr.is_executive_board=1 AND rmf.is_federation = 1) THEN '$clubExecutiveBoardId' ELSE rmr.id END) AS roleId";
            $roleTitleSql = "(CASE WHEN (rmr.is_executive_board=1) THEN '$executiveBoardTitle' WHEN rmri18n.title_lang IS NULL OR rmri18n.title_lang='' THEN rmr.title ELSE rmri18n.title_lang END) AS roleTitle";
            $selectFields = "$groupSql, $categoryIdSql, $draggableSql, $categoryTitleSql, rmr.category_id as category,fcb.id AS bookMarkId,rmc.club_id AS catClubId, rmc.role_assign AS roleAssign, rmc.function_assign AS functionAssign, rmc.is_required_fedmember_subfed AS isReqOnSubfed, rmc.is_required_fedmember_club AS isReqOnClub, rmc.is_allowed_fedmember_subfed AS isAllowedOnSubfed, rmc.is_allowed_fedmember_club AS isAllowedOnClub, rmr.is_active as isRoleActive,$roleIdSql, $roleTitleSql, rmf.id AS functionId, IF(rmfi18n.title_lang IS NULL OR rmfi18n.title_lang='', rmf.title, rmfi18n.title_lang) AS functionTitle,$sortsql ";
            $joinSql = "LEFT JOIN fg_team_category AS tc ON rmr.team_category_id = tc.id "
                . "LEFT JOIN fg_team_category_i18n AS tci18n ON tci18n.id = tc.id AND tci18n.lang = '$defaultLang' "
                . "LEFT JOIN fg_rm_role_function AS rmrf ON rmrf.role_id = rmr.id "
                . "LEFT JOIN fg_rm_function AS rmf ON rmf.id = rmrf.function_id AND ( (rmc.club_id ='$clubId' AND rmf.is_federation=0) OR (rmc.club_id !='$clubId' AND ((rmr.is_executive_board=1 AND rmf.is_federation = 1) OR rmf.is_federation = 0 ) )  ) "
                . "LEFT JOIN fg_rm_function_i18n AS rmfi18n ON rmfi18n.id = rmf.id AND rmfi18n.lang = '$defaultLang'"
                . "LEFT JOIN fg_cm_bookmarks AS fcb ON fcb.role_id = rmr.id AND fcb.club_id='$clubId' AND fcb.contact_id='$contactId' AND fcb.type='role'";
            $condSql = "WHERE (rmc.club_id ='$clubId' " . ($clubHeirarchy != '' ? "OR (rmc.club_id IN ($clubHeirarchy) AND ((rmc.club_id !='$clubId' AND rmr.is_executive_board=1 AND rmc.is_fed_category = 0 AND rmf.is_federation = 1) OR rmc.is_fed_category=1)) " : '') . ") AND rmc.is_team=0 AND rmc.is_active=1 ";
            $groupBySql = "";
            $orderBySql = "ORDER BY sort ASC, rmc.sort_order ASC, rmc.id ASC, tc.sort_order ASC, tc.id ASC, rmr.sort_order ASC, rmr.id ASC, rmf.is_federation DESC, rmf.sort_order ASC, rmf.id ASC";
        } else {
            $categoryIdSql = "(CASE WHEN (rmr.type = 'T') THEN rmr.team_category_id WHEN (rmc.club_id <>'$clubId' AND rmr.is_executive_board=1 AND rmf.is_federation = 1) THEN '$clubWorkgroupId' ELSE rmc.id END) AS categoryId";
            $categoryTitleSql = "IF(rmr.type = 'T', IF(tci18n.title_lang IS NULL OR tci18n.title_lang='', tc.title, tci18n.title_lang) , IF(rmci18n.title_lang IS NULL OR rmci18n.title_lang='', rmc.title, rmci18n.title_lang)) AS categoryTitle";
            $roleIdSql = "(CASE WHEN (rmc.club_id !='$clubId' AND rmr.is_executive_board=1 AND rmf.is_federation = 1) THEN '$clubExecutiveBoardId' ELSE rmr.id END) AS roleId";
            $roleTitleSql = "(CASE WHEN (rmr.is_executive_board=1) THEN '$executiveBoardTitle' WHEN rmri18n.title_lang IS NULL OR rmri18n.title_lang='' THEN rmr.title ELSE rmri18n.title_lang END) AS roleTitle";
            $selectFields = "$groupSql,$draggableSql,$categoryIdSql, $categoryTitleSql, rmc.club_id AS catClubId,rmr.category_id as category, rmc.role_assign AS roleAssign, rmc.is_required_fedmember_subfed AS isReqOnSubfed, rmc.is_required_fedmember_club AS isReqOnClub, rmc.is_allowed_fedmember_subfed AS isAllowedOnSubfed, rmc.is_allowed_fedmember_club AS isAllowedOnClub, $roleIdSql, $roleTitleSql, rmf.id AS functionId, IF(rmfi18n.title_lang IS NULL OR rmfi18n.title_lang='', rmf.title, rmfi18n.title_lang) AS functionTitle,$sortsql ";
            $joinSql = "LEFT JOIN fg_team_category AS tc ON rmr.team_category_id = tc.id "
                . "LEFT JOIN fg_team_category_i18n AS tci18n ON tci18n.id = tc.id AND tci18n.lang = '$defaultLang' "
                . "LEFT JOIN fg_rm_role_function AS rmrf ON rmrf.role_id = rmr.id "
                . "LEFT JOIN fg_rm_function AS rmf ON rmf.id = rmrf.function_id AND ( (rmc.club_id ='$clubId' AND rmf.is_federation=0) OR (rmc.club_id !='$clubId' AND ((rmr.is_executive_board=1 AND rmf.is_federation = 1) OR rmf.is_federation = 0 ) )  ) "
                . "LEFT JOIN fg_rm_function_i18n AS rmfi18n ON rmfi18n.id = rmf.id AND rmfi18n.lang = '$defaultLang'";
            $condSql = "WHERE (rmc.club_id ='$clubId' " . ($clubHeirarchy != '' ? "OR (rmc.club_id IN ($clubHeirarchy) AND ((rmc.club_id !='$clubId' AND rmr.is_executive_board=1 AND rmc.is_fed_category = 0 AND rmf.is_federation = 1) OR rmc.is_fed_category=1)) " : '') . ") AND rmc.is_active=1 AND rmr.id IS NOT NULL AND ((rmc.function_assign!= 'none' AND rmrf.id IS NOT NULL) OR rmc.function_assign= 'none')";
            $groupBySql = "";
            $orderBySql = "ORDER BY sort ASC, rmc.sort_order ASC, tc.sort_order ASC, rmr.sort_order ASC, rmr.id ASC, rmf.is_federation DESC, rmf.sort_order ASC";
        }
        $joinQuery = ($queryType == 'filteronly') ? " LEFT JOIN " : " INNER JOIN ";
        $roleSql = "SELECT $selectFields "
            . "FROM fg_rm_category AS rmc "
            . "LEFT JOIN fg_rm_category_i18n AS rmci18n ON rmci18n.id = rmc.id AND rmci18n.lang = '$defaultLang' "
            . "$joinQuery fg_rm_role AS rmr ON rmr.category_id = rmc.id "
            . "LEFT JOIN fg_rm_role_i18n AS rmri18n ON rmri18n.id = rmr.id AND rmri18n.lang = '$defaultLang' "
            . "$joinSql "
            . "$condSql "
            . "$filterRoleSql "
            . "$groupBySql "
            . "$orderBySql";
        //echo $roleSql;exit;
        $clubRoleArray = $this->conn->fetchAll($roleSql);

        return $clubRoleArray;
    }

    /**
     * Function to get Bookmarks of a perticular Contact.
     *
     * @param Integer $contactId         Contact Id
     * @param Integer $clubId            Club Id
     * @param String  $clubType          Club Type
     * @param Integer $clubHeirarchy     Sublevel club ids
     * @param Integer $executiveboardId  Current clubs executiveboard id
     * @param Integer $execBoardTerm     Terminology term of executiveboard
     * @param Integer $staticFilterTrans Translation for static filters(singleperson/company/member/sponsor)
     * @param boolean $countFlag         count Flag
     * @param integer $federationId      federation Id
     * @param string  $corrLang          corrLang
     *
     * @return query result or as processed array based on the $exec parameter
     */
    public function getContactBookmarks($contactId, $clubId, $clubType, $clubHeirarchy, $executiveboardId, $execBoardTerm, $staticFilterTrans, $countFlag = true, $federationId, $corrLang)
    {
        $federationId = ($clubType == 'federation') ? $clubId : $federationId;
        $doctrineConfig = $this->em->getConfiguration();
        $doctrineConfig->addCustomStringFunction('getClubRoleCount', 'Common\UtilityBundle\Extensions\RoleCount');
        if ($clubType === 'federation' || $clubType === 'sub_federation') {
            $field = ($clubType == 'federation') ? 'mc.fed_contact_id' : 'mc.contact_id';
            $tablename = 'master_federation_' . $clubId;
            $where = 'AND (mc.club_id =:clubId OR (mc.club_id != :clubId AND (fg_cm_contact.fed_membership_cat_id IS NOT NULL AND fg_cm_contact.is_fed_membership_confirmed =1)))';
        } else {
            $field = 'mc.contact_id';
            $tablename = 'master_club_' . $clubId;
            $where = '';
        }

        //$ids = $this->getSubclubs($clubType, $clubId);
        $fedGroupSql = '';
        foreach ($clubHeirarchy as $clubsId) {
            $fedGroupSql .= "WHEN (rc.club_id = $clubsId AND rc.is_fed_category=1) THEN 'FROLES' ";
        }


        $groupSql = "(CASE WHEN (rc.club_id = :clubId AND rc.is_fed_category=0
                AND rc.is_team =0 AND rc.is_workgroup =0 AND rc.contact_assign='manual')
                THEN 'ROLES' WHEN (rc.club_id = :clubId AND rc.is_fed_category=0
                AND rc.is_team =0 AND rc.is_workgroup =0 AND rc.contact_assign='filter-driven')
                THEN 'FILTERROLES'
                $fedGroupSql
                WHEN (rc.club_id = $clubId AND rc.is_fed_category=1) THEN 'FROLES'
                WHEN rc.is_team =1 THEN 'TEAM' WHEN rc.is_workgroup=1
                THEN 'WORKGROUP' END) AS roleType";

        $countSqltext = "(CASE   WHEN  bm.type = 'role' THEN (getClubRoleCount(r.id,:clubId))
                      WHEN   bm.type = 'filter' AND bm.filter_id=1  THEN
                            (SELECT  count(fg_cm_contact.id) from  $tablename as mc
                             INNER JOIN fg_cm_contact on {$field} = fg_cm_contact.id
                             INNER JOIN master_system as ms on ms.fed_contact_id = fg_cm_contact.fed_contact_id
                             WHERE fg_cm_contact.is_permanent_delete=0 AND   (fg_cm_contact.is_company=0)$where)
                      WHEN   bm.type = 'filter' AND bm.filter_id=2  THEN
                            (SELECT  count(fg_cm_contact.id) from  $tablename as mc
                             INNER JOIN fg_cm_contact on {$field} = fg_cm_contact.id
                             INNER JOIN master_system as ms on ms.fed_contact_id = fg_cm_contact.fed_contact_id
                             WHERE fg_cm_contact.is_permanent_delete=0 AND   (fg_cm_contact.is_company=1) $where)
                      WHEN   bm.type = 'filter' AND bm.filter_id=3  THEN
                            (SELECT  count(fg_cm_contact.id) from  $tablename as mc
                             INNER JOIN fg_cm_contact on {$field} = fg_cm_contact.id
                              INNER JOIN master_system as ms on ms.fed_contact_id = fg_cm_contact.fed_contact_id
                             WHERE fg_cm_contact.is_permanent_delete=0 AND   (fg_cm_contact.club_membership_cat_id IS NOT NULL) $where)
                      WHEN   bm.type = 'filter' AND bm.filter_id=4  THEN
                            (SELECT  count(fg_cm_contact.id) from  $tablename as mc
                             INNER JOIN fg_cm_contact on {$field} = fg_cm_contact.id
                              INNER JOIN master_system as ms on ms.fed_contact_id = fg_cm_contact.fed_contact_id
                             WHERE fg_cm_contact.is_permanent_delete=0 AND   (fg_cm_contact.fed_membership_cat_id IS NOT NULL || fg_cm_contact.fed_membership_cat_id !='') $where)
                     WHEN   bm.type = 'membership' THEN (IF(m.club_id = $federationId,(SELECT  count(cnt.id)
                      FROM fg_cm_contact cnt  WHERE (cnt.fed_membership_cat_id = bm.membership_id )
                      AND cnt.club_id = $clubId),(SELECT  count(cnt.id)
                      FROM fg_cm_contact cnt  WHERE ( cnt.club_membership_cat_id = bm.membership_id)
                      AND cnt.club_id = $clubId))) END) AS count";

        $countSql = ($countFlag) ? $countSqltext : "'0' AS count";


        // If executiveboard is bookmarked, we have to display the terminology term.
        $titleSql = "(IF (r.id = $executiveboardId, '$execBoardTerm', r.title)) as roletitle";
        $staticfilterSql = "(IF (f.club_id =1 , '1', '0')) as static";

        // If team is bookmarked category id will be team cateogry Id.
        $selCategorySql = "(IF (rc.is_team = 1, tc.id, rc.id)) AS roleCategoryId";
        $filterNameSql = "(CASE f.id WHEN '1' THEN ':trans1' WHEN '2' THEN ':trans2' WHEN '3' THEN ':trans3' WHEN '4' THEN ':trans4' ELSE f.name END) AS filtertitle, IF(f.club_id = 1, 1, 0 ) AS staticFilter";

        $bookmarkSql = "SELECT bm.id as bookMarkIds, bm.contact_id as contactId, IF(bm.type = 'membership', IF(m.club_id = '{$federationId}','fed_membership',bm.type),bm.type) as type,
                        bm.role_id as roleId,bm.filter_id as filterId,
                        bm.membership_id as membershipId,bm.sort_order as sortOrder,
                        $titleSql,  IF(mi18.title_lang !='',mi18.title_lang,m.title) as membershiptitle, $filterNameSql,f.club_id as filterClub,
                        $selCategorySql, f.is_broken as isBroken, $staticfilterSql, f.filter_data as filterData,rc.contact_assign AS contactAssign,
                        rc.club_id AS roleCatClubId, rc.function_assign AS functionAssign, $groupSql,$countSql,
                        (CASE WHEN  ((bm.type = 'role' AND rc.contact_assign='manual') OR bm.type= 'membership' ) THEN 'DRAGGABLE' ELSE 'NOTDRAGGABLE' END) As draggable
                        FROM fg_cm_bookmarks AS bm
                        LEFT JOIN fg_cm_membership AS m ON m.id = bm.membership_id
                        LEFT JOIN fg_cm_membership_i18n AS mi18 ON mi18.id = m.id AND  mi18.lang = '{$corrLang}'
                        LEFT JOIN fg_rm_role AS r ON r.id = bm.role_id
                        LEFT JOIN fg_rm_category AS rc ON rc.id = r.category_id
                        LEFT JOIN fg_team_category AS tc ON tc.id = r.team_category_id
                        LEFT JOIN fg_filter AS f ON f.id = bm.filter_id
                        WHERE bm.contact_id=:contactsId AND bm.club_id=:clubId
                        ORDER BY bm.sort_order ";

        $dataResult = $conn->fetchAll($bookmarkSql, array(':contactsId' => $contactId, ':clubId' => $clubId,
            ':trans1' => $staticFilterTrans['1'], ':trans2' => $staticFilterTrans['2'], ':trans3' => $staticFilterTrans['3'], ':trans4' => $staticFilterTrans['4']));

        return $dataResult;
    }
}
