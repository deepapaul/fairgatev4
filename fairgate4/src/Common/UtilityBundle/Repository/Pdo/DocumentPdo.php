<?php

namespace Common\UtilityBundle\Repository\Pdo;

use Common\UtilityBundle\Util\FgSettings;
use Common\UtilityBundle\Util\FgUtility;
use Clubadmin\Util\Contactlist;
use Clubadmin\Util\Contactfilter;

/**
 * Used to handling different document module functions.
 *
 * @author  pitsolutions.ch <pit@solutions.com>
 */
class DocumentPdo
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
     * Entity manager object
     */
    public $em;

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
     * function to update the table and also insert log entries
     * @param Int    $documentId comma se[arated document id
     * @param Int    $movecategoryId dropped category id
     * @param Int    $moveSubCategoryId dropped subcategory id
     * @param String $dropValue dropped details
     * @param Int    $clubId clubId
     * @param Int    $contactId logged user id
     * @param String $docType logged user id
     *
     *  @return int
     */
    public function movedocumentCategory($documentId, $movecategoryId, $moveSubCategoryId, $dropValue, $clubId, $contactId, $docType)
    {   
        //To find the total number of documents to be moved
        $affectedRowQuery = "SELECT count(id) as docCount FROM `fg_dm_documents` WHERE id IN (" . $documentId . ") AND subcategory_id NOT IN (". $moveSubCategoryId .")";
        $affectedCountResult =  $this->conn->fetchAll($affectedRowQuery);
        $affectedCount = $affectedCountResult[0]['docCount']; 

        $insertQuery = "INSERT INTO  fg_dm_document_log (`date`,`club_id`, `changed_by`, `kind`, `field`, `value_before`, `value_after`,`documents_id`,`document_type` ) SELECT NOW(),'" . $clubId . "',  '" . $contactId . "','data','Category',(SELECT CONCAT(CAT.title,'-',SUB.title) FROM `fg_dm_documents` DOC JOIN fg_dm_document_category CAT ON CAT.id = DOC.`category_id`
                        JOIN fg_dm_document_subcategory SUB ON SUB.id =  DOC.`subcategory_id`  WHERE DOC.id = doc.id ),:dropValue, doc.id, '" . $docType . "'  FROM fg_dm_documents doc where doc.id IN (" . $documentId . ")";
        $updatequery = "UPDATE fg_dm_documents DOC INNER JOIN fg_dm_version VER ON DOC.current_revision = VER.id "
                . " SET DOC.category_id = $movecategoryId, DOC.subcategory_id = $moveSubCategoryId, VER.updated_at = now(), VER.updated_by = $contactId "
                . " WHERE DOC.id IN (" . $documentId . ")";

        $this->conn->executeQuery($insertQuery, array(":dropValue" => $dropValue));
        $result = $this->conn->executeQuery($updatequery);

         return  $affectedCount;
    }

    /**
     * Function to get the contact documents for personal overview
     *
     * @param int    $contactId    contact Id
     * @param string $lang         club language
     * @param string $contactTitle contact title
     *
     * @return array
     */
    public function getcontactDocumentsOverview($contactId, $lang, $contactTitle)
    {
        $dateFormat = FgSettings::getMysqlDateFormat();
        $sql = "SELECT distinct(d.id), d.document_type, v.id AS versionId,DATE_FORMAT(  v.updated_at,'$dateFormat' ) AS updatedDate,v.updated_at,
                DATE_FORMAT( v.updated_at,'%H:%i' ) AS updatedTime, v.size, v.file,
                (CASE WHEN (di18n.name_lang IS NULL OR di18n.name_lang = '') THEN d.name ELSE di18n.name_lang END) AS docName,
                '$contactTitle' AS roletitle
                FROM `fg_dm_assigment` a
                LEFT JOIN fg_dm_documents d ON a.document_id = d.id
                LEFT JOIN fg_dm_documents_i18n di18n ON di18n.id = d.id AND di18n.lang = '$lang'
                LEFT JOIN fg_dm_version v ON v.id = d.current_revision
                WHERE a.document_type = 'CONTACT' AND a.contact_id = $contactId
                AND d.is_visible_to_contact = 1
                AND d.id NOT IN (SELECT document_id FROM fg_dm_assigment_exclude WHERE contact_id = $contactId)
                GROUP BY v.document_id
                ORDER BY v.updated_at DESC LIMIT 7";


        $result = $this->conn->fetchAll($sql);        //echo '<pre>';print_r($result);exit;
        return $result;
    }

    /**
     * Function to get all the team and workgroup documents in personal, team and workgroup overview
     *
     * @param int    $clubId       club Id
     * @param int    $contactId    contact Id
     * @param string $allTrans     translated text
     * @param int    $roleIds      role ids
     * @param string $lang         club language
     * @param string $contactTitle contact title
     * @param string $clubTerm     club terminology term
     * @param int    $roleId       roleId
     * @param string $roleType     roletype team or workgroup
     * @param int    $isClubAdmin  clubAdminFlag    
     * @param int    $clubTeamId   clubTeamId   
     *
     * @return array
     */
    public function getteamworkgroupdocumentsOverview($clubId, $contactId, $allTrans, $roleIds, $lang, $roleType = '', $groupRights = array(), $isClubAdmin, $clubTeamId)
    {
        $teamFunctionCondition = '';
        $teamMemberCondition = '';
        $teamAdminCondition = '';
        $docTypeCondition = ($roleType == '') ? " AND (dd.`document_type` = 'TEAM' OR dd.`document_type` = 'WORKGROUP') " : "AND (dd.`document_type` = '" . strtoupper($roleType) . "')";
//        if ($userRightsTeamAndWorkgroupIds != '') {
//            $userRightCondition = "OR (((dd.deposited_with = 'ALL') OR (dd.deposited_with = 'SELECTED' AND da.role_id IN ('$userRightsTeamAndWorkgroupIds'))) AND dd.visible_for IN('team_admin','club_contact_admin','workgroup_admin'))";
//        }
        //teams of which i am admin
        $adminTeamIds = array();
        $memberteamIds = array();
        $adminWorkgroupIds = array();
        $workgroupIds = array();
        $conditions = '';
        if ($isClubAdmin != 1) {
            if (count($groupRights) > 0) {
                $adminTeamIds = (isset($groupRights['ROLE_GROUP_ADMIN']['teams'])) ? $groupRights['ROLE_GROUP_ADMIN']['teams'] : array();
                $adminWorkgroupIds = (isset($groupRights['ROLE_GROUP_ADMIN']['workgroups'])) ? $groupRights['ROLE_GROUP_ADMIN']['workgroups'] : array();
                $memberteamIds = (isset($groupRights['MEMBER']['teams'])) ? $groupRights['MEMBER']['teams'] : array();
                $workgroupIds = (isset($groupRights['MEMBER']['workgroups'])) ? $groupRights['MEMBER']['workgroups'] : array();
                if ($roleType == '') {
                    if (count($adminTeamIds) == 0 && count($memberteamIds) == 0) {
                        $docTypeCondition = " AND (dd.`document_type` = 'WORKGROUP') ";
                    } else if (count($adminWorkgroupIds) == 0 && count($workgroupIds) == 0) {
                        $docTypeCondition = " AND (dd.`document_type` = 'TEAM') ";
                    }
                }
            }

            $checkIfExistInTeam = 0;
            $checkIfExistInWorkgroup = 0;

            if (count($memberteamIds) > 0 || count($workgroupIds) > 0) {
                $checkIfExistInTeam = ($roleType == 'team') ? ((in_array($roleIds, $memberteamIds)) ? 1 : 0) : 0;
                $checkIfExistInWorkgroup = ($roleType == 'workgroup') ? ((in_array($roleIds, $workgroupIds)) ? 1 : 0) : 0;
                $teamMemberCondition = ($roleType == '' || ($roleType == 'team' && $checkIfExistInTeam == 1) || ($roleType == 'workgroup' && $checkIfExistInWorkgroup == 1)) ? " dd.visible_for IN ('team', 'workgroup')" : '';
            }
            if (count($adminTeamIds) > 0 || count($adminWorkgroupIds) > 0) {
                $checkIfExistInTeam = ($roleType == 'team') ? ((in_array($roleIds, $adminTeamIds)) ? 1 : 0) : 0;
                $checkIfExistInWorkgroup = ($roleType == 'workgroup') ? ((in_array($roleIds, $adminWorkgroupIds)) ? 1 : 0) : 0;
                $teamAdminCondition = ($roleType == '' || ($roleType == 'team' && $checkIfExistInTeam == 1) || ($roleType == 'workgroup' && $checkIfExistInWorkgroup == 1)) ? " dd.visible_for IN ('team', 'team_admin', 'workgroup', 'workgroup_admin')" : '';
            }

            if ($roleType != 'workgroup') {
                if ($roleType == 'team') {
                    $teamFunctionCondition = " (dd.visible_for = 'team_functions'
                            AND 
                                (df.function_id IN
                                    (SELECT crf.function_id from fg_rm_category_role_function crf
                                    LEFT JOIN fg_rm_role_contact rc on crf.id = rc.fg_rm_crf_id
                                    WHERE  crf.club_id = $clubId AND crf.category_id = $clubTeamId AND crf.role_id = $roleIds AND rc.contact_id = $contactId)
                                )
                                )";
                } else {
                    $teamFunctionCondition = " (dd.visible_for = 'team_functions'
                            AND (
                                (df.function_id IN
                                    (SELECT crf.function_id from fg_rm_category_role_function crf
                                    LEFT JOIN fg_rm_role_contact rc on crf.id = rc.fg_rm_crf_id
                                    WHERE  crf.club_id = $clubId AND crf.category_id = $clubTeamId AND crf.role_id = da.role_id AND rc.contact_id = $contactId)
                                ) 
                                OR 
                                (df.function_id IN
                                    (SELECT crf2.function_id from fg_rm_category_role_function crf2
                                    LEFT JOIN fg_rm_role_contact rc2 on crf2.id = rc2.fg_rm_crf_id
                                    WHERE crf2.club_id = $clubId AND crf2.category_id = $clubTeamId AND rc2.contact_id = $contactId AND rc2.assined_club_id = $clubId)
                                )
                                )
                                )";
                }
            }

            $conditions .= $teamMemberCondition;
            $conditions .= (($teamMemberCondition != '' && $teamAdminCondition != '') ? ' OR ' . $teamAdminCondition : (($teamMemberCondition == '' && $teamAdminCondition != '') ? $teamAdminCondition : ''));
            $conditions .= (($conditions != '' && $teamFunctionCondition != '') ? ' OR ' . $teamFunctionCondition : (($conditions == '' && $teamFunctionCondition != '') ? $teamFunctionCondition : ''));
        } else {
            if ($roleType == '') {
                $assignedRoles = $this->em->getRepository('CommonUtilityBundle:FgRmRoleContact')->getAllRolesOfAContact($this->container, $contactId);
                $teams = (isset($assignedRoles['teams'])) ? array_keys($assignedRoles['teams']) : array();
                $workgroups = (isset($assignedRoles['workgroups'])) ? array_keys($assignedRoles['workgroups']) : array();
                $roleId = array_merge($teams, $workgroups);
                $roleIds = implode(',', $roleId);
            }
            $conditions = 1;
        }
        $dateFormat = FgSettings::getMysqlDateFormat();
        if ($roleIds != '') {
            $sql = "SELECT dd.id, dd.document_type, da.role_id  AS roleId, v.id AS versionId,DATE_FORMAT(  v.updated_at,'$dateFormat' ) AS updatedDate,v.updated_at, DATE_FORMAT( v.updated_at,'%H:%i' ) AS updatedTime, v.size, v.file,
                   (CASE WHEN dd.deposited_with='NONE' THEN 'NONE' ELSE (SELECT GROUP_CONCAT((CASE WHEN (rolei18n.title_lang IS NULL OR rolei18n.title_lang = '') THEN rl.title ELSE rolei18n.title_lang END) SEPARATOR '~#~' ) FROM fg_rm_role rl LEFT JOIN fg_rm_role_i18n rolei18n ON rolei18n.id = rl.id AND rolei18n.lang = '$lang' WHERE rl.id IN (SELECT distinct(dma.role_id) FROM fg_dm_assigment dma WHERE dma.document_id=dd.id AND dma.role_id IN ($roleIds))) END )  AS roletitle,
                   (CASE WHEN (di18n.name_lang IS NULL OR di18n.name_lang = '') THEN dd.name ELSE di18n.name_lang END) AS docName
                    FROM `fg_dm_documents` dd
                    LEFT JOIN fg_dm_documents_i18n di18n ON di18n.id = dd.id AND di18n.lang = '$lang'
                    LEFT JOIN fg_dm_version v ON v.id = dd.current_revision
                    LEFT JOIN fg_dm_assigment da ON da.document_id = dd.id
                    LEFT JOIN fg_dm_team_functions df ON dd.id = df.document_id
                    LEFT JOIN fg_rm_role rr ON da.role_id = rr.id AND rr.is_active = 1
                    WHERE ((dd.deposited_with = 'ALL') OR (dd.deposited_with = 'SELECTED' AND da.role_id IN ($roleIds))) AND
                    ( $conditions )
                    AND dd.club_id = $clubId  $docTypeCondition
                    GROUP BY v.document_id
                    ORDER BY v.updated_at DESC LIMIT 7 ";

            $result = $this->conn->fetchAll($sql);
        } else {
            $result = array();
        }
     
        return $result;
    }

    /**
     * Function to get the club documents for personal overview
     *
     * @param int    $clubId            club Id
     * @param string $lang              club language
     * @param string $clubTerm          club terminology term
     * @param string $clubHierarachyIds hierarchy club ids
     *
     * @return array
     */
    public function getclubdocsOverview($clubId, $lang, $clubTerm, $clubHierarachyIds)
    {
        $dateFormat = FgSettings::getMysqlDateFormat();

        $sql = "SELECT distinct(d.id),d.document_type,v.id AS versionId,DATE_FORMAT(  v.updated_at,'$dateFormat' ) AS updatedDate,v.updated_at, DATE_FORMAT( v.updated_at,'%H:%i' ) AS updatedTime, v.size, v.file,
                 (CASE WHEN (di18n.name_lang IS NULL OR di18n.name_lang = '') THEN d.name ELSE di18n.name_lang END) AS docName, '$clubTerm' AS roletitle
                 FROM fg_dm_documents d
                 LEFT JOIN fg_dm_documents_i18n di18n ON di18n.id = d.id AND di18n.lang = '$lang'
                 INNER JOIN fg_dm_version v ON v.id = d.current_revision
                 LEFT JOIN fg_dm_assigment a ON a.document_id = d.id
                 LEFT JOIN fg_dm_assigment_exclude ae ON ae.document_id = d.id 
                 WHERE d.document_type = 'CLUB'
                 AND ((d.deposited_with = 'ALL' AND d.is_visible_to_contact = 1 AND ae.club_id NOT IN (select ae2.club_id FROM fg_dm_assigment_exclude ae2 where ae2.document_id = d.id))
                 OR (d.deposited_with = 'SELECTED' AND d.is_visible_to_contact = 1 AND a.club_id = $clubId)
                 OR (d.deposited_with = 'NONE' AND d.is_visible_to_contact = 1 AND d.club_id = $clubId))
                 AND d.club_id IN ($clubHierarachyIds)    
                 GROUP BY v.document_id
                 ORDER BY v.updated_at DESC LIMIT 7";

        $result = $this->conn->fetchAll($sql);


        return $result;
    }

    /**
     * Function to get all the documents for superadmin
     *
     * @param int    $clubId       club Id
     * @param string $lang         club language
     * @param string $allTrans     translated text
     * @param string $contactTitle contact title translated text
     * @param string $clubTerm     club terminology term
     * @param int    $roleId       roleId
     * @param string $roleType     roletype team or workgroup
     *
     * @return array
     */
    public function getdocumentsforSuperadmin($clubId, $lang, $allTrans, $contactTitle, $clubTerm, $roleId = '', $roleType = '')
    {
        if ($roleType == '') {
            $roleTitleCondition = "(CASE WHEN dd.document_type = 'CLUB' THEN '$clubTerm' WHEN dd.document_type = '$contactTitle' THEN 'CONTACT' WHEN (dd.document_type = 'TEAM' OR dd.document_type = 'WORKGROUP') THEN
                                  (CASE WHEN dd.deposited_with='NONE' THEN 'NONE' WHEN dd.deposited_with='ALL' THEN '$allTrans'
                                   ELSE (SELECT GROUP_CONCAT(  (CASE WHEN (rolei18n.title_lang IS NULL OR rolei18n.title_lang = '') THEN rl.title ELSE rolei18n.title_lang END) SEPARATOR '~#~' ) FROM fg_rm_role rl LEFT JOIN fg_rm_role_i18n rolei18n ON rolei18n.id = rl.id AND rolei18n.lang = '$lang' WHERE rl.id IN (SELECT distinct(dma.role_id) FROM fg_dm_assigment dma WHERE dma.document_id=dd.id)) END ) ELSE 'CMS' END) AS roletitle";

            $visibilityCondition = "  (CASE WHEN (dd.document_type = 'TEAM' OR dd.document_type = 'WORKGROUP') THEN dd.visible_for != 'none' WHEN (dd.document_type = 'CLUB' OR dd.document_type = 'CONTACT') THEN dd.is_visible_to_contact = 1 ELSE dd.is_visible_to_contact = 1 END)";
        } else {
            $roleTitleCondition = "(CASE WHEN dd.deposited_with='NONE' THEN 'NONE' WHEN dd.deposited_with='ALL' THEN '$allTrans' ELSE
                                  (SELECT GROUP_CONCAT(  (CASE WHEN (rolei18n.title_lang IS NULL OR rolei18n.title_lang = '') THEN rl.title ELSE rolei18n.title_lang END) SEPARATOR '~#~' )
                                   FROM fg_rm_role rl LEFT JOIN fg_rm_role_i18n rolei18n ON rolei18n.id = rl.id AND rolei18n.lang = '$lang' WHERE rl.id IN (SELECT distinct(dma.role_id) FROM fg_dm_assigment dma WHERE dma.document_id=dd.id)) END ) AS roletitle";

            $visibilityCondition = "  dd.visible_for != 'none' AND dd.document_type = '" . strtoupper($roleType) . "'";
        }

        $roleCondition  = '';
        if ($roleId != '') {
            $roleCondition = " AND da.role_id IN ($roleId)";
        }

        $dateFormat = FgSettings::getMysqlDateFormat();

        $sql = "SELECT dd.id, dd.document_type, da.role_id AS roleId, dv.id AS versionId , DATE_FORMAT(  dv.updated_at,'$dateFormat' ) AS updatedDate, dv.updated_at, DATE_FORMAT(dv.updated_at,'%H:%i') AS updatedTime, dv.size, dv.file,
                (CASE WHEN (di18n.name_lang IS NULL OR di18n.name_lang = '') THEN dd.name ELSE di18n.name_lang END) AS docName,
                $roleTitleCondition
                FROM fg_dm_documents dd
                LEFT JOIN fg_dm_version dv ON dv.document_id = dd.id
                LEFT JOIN fg_dm_assigment da ON da.document_id = dd.id
                LEFT JOIN fg_dm_documents_i18n di18n ON di18n.id = dd.id AND di18n.lang = '$lang'
                LEFT JOIN fg_rm_role rr ON da.role_id = rr.id AND rr.is_active = 1
                WHERE ((dd.deposited_with = 'ALL') OR (dd.deposited_with = 'SELECTED' $roleCondition))  AND dd.club_id = $clubId AND $visibilityCondition
                GROUP BY dv.document_id
                ORDER BY dv.updated_at DESC LIMIT 7";

        $result = $this->conn->fetchAll($sql);

        return $result;
    }

    /**
     * Function to execute a sql query
     * 
     * @param string $sql Build sql query
     * 
     * @return array $result Document details array
     */
    public function executeDocumentsQuery($sql)
    {
        $result = array();
        if ($sql != '') {
            $result = $this->conn->fetchAll($sql);
        }

        return $result;
    }
        
    /**
     * Function to get the top navigation count for team and workgroup overview
     *
     * @param int    $clubId       curent club id
     * @param Int    $contactId    logged contact id
     * @param array  $roles        roles id array
     * @param array  $groupRights  groups rights array
     * @param int    $isClubAdmin  clubadmin flag
     * @param int    $clubTeamId   club team id
     * @param int    $isTeam       role type team or workgroup
     * @param int    $isSuperAdmin superadmin flag    
     * 
     * @return array
     */
    public function topNavTeamandWorkgroupCount($clubId, $contactId, $roles, $groupRights = array(), $isClubAdmin, $clubTeamId, $isTeam, $isSuperAdmin)
    {
        $roleIds = implode(", ", $roles);
        $roleType = ($isTeam == 1) ? 'team' : 'workgroup';
        $teamFunctionCondition = '';
        $teamMemberCondition = '';
        $teamAdminCondition = '';
        $docTypeCondition = " AND (dd.`document_type` = '" . strtoupper($roleType) . "')";
        $visibilityCondition = ($isSuperAdmin == 1 || $isClubAdmin == 1) ? " AND (dd.visible_for != 'none' AND dd.visible_for != 'club_contact_admin' AND dd.visible_for != 'main_document_admin')" : '' ;
        $adminTeamIds = array();
        $memberteamIds = array();
        $adminWorkgroupIds = array();
        $workgroupIds = array();
        $docadminTeamIds  = array();
        $docadminWorkgroupIds  = array();       
        $conditions = '';
        if ($isClubAdmin != 1 && $isSuperAdmin == 0) {
            if (count($groupRights) > 0) {  
                $docadminTeamIds = (isset($groupRights['ROLE_DOCUMENT_ADMIN']['teams'])) ? $groupRights['ROLE_DOCUMENT_ADMIN']['teams'] : array();                           
                $docadminWorkgroupIds = (isset($groupRights['ROLE_DOCUMENT_ADMIN']['workgroups'])) ? $groupRights['ROLE_DOCUMENT_ADMIN']['workgroups'] : array();
                $adminTeamIds = (isset($groupRights['ROLE_GROUP_ADMIN']['teams'])) ? $groupRights['ROLE_GROUP_ADMIN']['teams'] : array();
                $adminWorkgroupIds = (isset($groupRights['ROLE_GROUP_ADMIN']['workgroups'])) ? $groupRights['ROLE_GROUP_ADMIN']['workgroups'] : array();
                $memberteamIds = (isset($groupRights['MEMBER']['teams'])) ? $groupRights['MEMBER']['teams'] : array();
                $workgroupIds = (isset($groupRights['MEMBER']['workgroups'])) ? $groupRights['MEMBER']['workgroups'] : array();
            }
            $checkIfExistInTeam = 0;
            $checkIfExistInWorkgroup = 0;

            if (count($memberteamIds) > 0 || count($workgroupIds) > 0) {
                $checkIfExistInTeam = ($roleType == 'team') ? ((in_array($roleIds, $memberteamIds)) ? 1 : 0) : 0;
                $checkIfExistInWorkgroup = ($roleType == 'workgroup') ? ((in_array($roleIds, $workgroupIds)) ? 1 : 0) : 0;
                $teamMemberCondition = ($roleType == '' || ($roleType == 'team' && $checkIfExistInTeam == 1) || ($roleType == 'workgroup' && $checkIfExistInWorkgroup == 1)) ? " dd.visible_for IN ('team', 'workgroup')" : '';
            }         
            if (count($adminTeamIds) > 0 || count($adminWorkgroupIds) > 0 || count($docadminTeamIds) > 0 || count($docadminWorkgroupIds) > 0) {
                $checkIfExistInTeam = ($roleType == 'team') ? ((in_array($roleIds, $adminTeamIds) || in_array($roleIds, $docadminTeamIds)) ? 1 : 0) : 0;
                $checkIfExistInWorkgroup = ($roleType == 'workgroup') ? ((in_array($roleIds, $adminWorkgroupIds) || in_array($roleIds, $docadminWorkgroupIds)) ? 1 : 0) : 0;
                $teamAdminCondition = ($roleType == '' || ($roleType == 'team' && $checkIfExistInTeam == 1) || ($roleType == 'workgroup' && $checkIfExistInWorkgroup == 1)) ? " dd.visible_for IN ('team', 'team_admin', 'workgroup', 'workgroup_admin')" : '';
            }
            if ($roleType == 'team') {
                $teamFunctionCondition = " (dd.visible_for = 'team_functions'
                            AND
                                (df.function_id IN
                                    (SELECT crf.function_id from fg_rm_category_role_function crf
                                    LEFT JOIN fg_rm_role_contact rc on crf.id = rc.fg_rm_crf_id
                                    WHERE  crf.club_id = $clubId AND crf.category_id = $clubTeamId AND crf.role_id IN ($roleIds) AND rc.contact_id = $contactId)
                                )
                                )";
            }

            $conditions .= $teamMemberCondition;
            $conditions .= (($teamMemberCondition != '' && $teamAdminCondition != '') ? ' OR ' . $teamAdminCondition : (($teamMemberCondition == '' && $teamAdminCondition != '') ? $teamAdminCondition : ''));
            $conditions .= (($conditions != '' && $teamFunctionCondition != '') ? ' OR ' . $teamFunctionCondition : (($conditions == '' && $teamFunctionCondition != '') ? $teamFunctionCondition : ''));
            if($conditions == ''){
               $conditions = 1; 
            }
            } else {
            $conditions = 1;
        }

        $sql = "SELECT COUNT(distinct(dd.id)) AS DocCount
                FROM `fg_dm_documents` dd
                LEFT JOIN fg_dm_version v ON v.id = dd.current_revision
                LEFT JOIN fg_dm_assigment da ON da.document_id = dd.id
                LEFT JOIN fg_dm_team_functions df ON dd.id = df.document_id
                LEFT JOIN fg_rm_role rr ON da.role_id = rr.id AND rr.is_active = 1
                LEFT JOIN fg_dm_contact_sighted cs ON cs.document_id = dd.id AND  cs.contact_id = $contactId
                WHERE ((dd.deposited_with = 'ALL') OR (dd.deposited_with = 'SELECTED' AND da.role_id IN ($roleIds))) AND
                ( $conditions )
                AND dd.club_id = $clubId  $docTypeCondition $visibilityCondition
                AND cs.contact_id IS NULL
                ";
        
        $result = $this->conn->fetchAll($sql);
        $count = ($result) ? $result[0]['DocCount'] : 0;    
        return $count;
    }
    
    /**
     * Method to get query for club documents listing
     * 
     * @param array  $aColumns          array for set coloums
     * @param string $mysqldate         data format
     * @param object $documentlistClass Object
     * @param int    $assignedClubId    assigned ClubId
     * @param array  $clubHeirarchy     club Heirarchy array
     * @param string $language          Language
     * 
     * @return string
     */
    public function getQueryForClubDocuments($aColumns, $mysqldate, $documentlistClass, $assignedClubId, $clubHeirarchy, $language) {
        array_push($aColumns, 'docname', 'fdd.id as documentId','fdd.is_publish_link as isPublic', 'fdd.is_visible_to_contact as isVisibleToContact','fdd.club_id','fg_dm_version.file as fileName',
                "IF(fg_dm_version.size, IF(ROUND( (fg_dm_version.size/1048576), 2 ) < 0.1, ' < 0.10 MB', CONCAT(ROUND( (fg_dm_version.size/1048576), 2 ), ' MB' )) ,'') AS CL_FO_SIZE", 
                'fg_dm_version.id as versionId',"DATE_FORMAT(fg_dm_version.created_at,'$mysqldate') as uploadedOn",
                " (CASE WHEN (fda.id IS NULL) THEN '0' ELSE fda.id  END) as assignmentId ",
                ' (CASE WHEN (fdi18.name_lang IS NULL) THEN fdd.name ELSE fdi18.name_lang END) as documentName',
                ' (CASE WHEN (fdi18.author_lang IS NULL) THEN fdd.author ELSE fdi18.author_lang END) as author',
                '(CASE WHEN (sbcati18.title_lang IS NULL) THEN sbcat.title ELSE sbcati18.title_lang END) as subcategory',
                '(CASE WHEN (cati18n.title_lang IS NULL) THEN cat.title ELSE cati18n.title_lang END) as category  ',
                " 'Club' as Type "
                );
        $documentlistClass->clubId = $assignedClubId;
        $documentlistClass->clubHeirarchy = $clubHeirarchy;
        $documentlistClass->clubtype = 'sub_federation_club';
        $documentlistClass->onlyHeirarchy = true;
        $documentlistClass->setColumns($aColumns);
        $documentlistClass->setFrom();
        $documentlistClass->setCondition();
        $documentlistClass->addCondition( "fdd.deposited_with != 'NONE'");
        $documentlistClass->addJoin(" LEFT JOIN fg_dm_assigment AS fda ON fdd.id = fda.document_id AND fda.club_id = $assignedClubId AND fda.document_type = 'CLUB' ");
        $documentlistClass->addJoin(" INNER JOIN fg_dm_document_subcategory as sbcat ON  sbcat.id = fdd.subcategory_id ");
        $documentlistClass->addJoin(" LEFT JOIN fg_dm_document_subcategory_i18n as sbcati18 ON  sbcat.id = sbcati18.id AND sbcati18.lang = '$language' ");
        $documentlistClass->addJoin(" INNER JOIN fg_dm_document_category as cat ON  cat.id = sbcat.category_id ");
        $documentlistClass->addJoin(" LEFT JOIN fg_dm_document_category_i18n as cati18n ON  cat.id = cati18n.id AND cati18n.lang = '$language' ");
        $documentlistClass->addOrderBy(" docname ASC ");

        $totallistquery = $documentlistClass->getResult();
        
        return $totallistquery;
        
    }
    
    /**
     * Method to get query for contact documents listing
     * 
     * @param array  $aColumns          array for set coloums
     * @param string $mysqldate         data format
     * @param object $documentlistClass Object     
     * @param string $language          Language
     * @param int    $clubId            Current clubId
     * @param int  $contactId           Current contact Id
     * 
     * @return string
     */  
    public function getQueryForContactDocuments($aColumns, $mysqldate, $documentlistClass, $language, $clubId, $contactId) {
        array_push($aColumns, 'docname', 'fdd.id as documentId', 'fdd.is_visible_to_contact as isVisibleToContact','fdd.is_publish_link as isPublic', 'fdd.club_id',
                "IF(fg_dm_version.size, IF(ROUND( (fg_dm_version.size/1048576), 2 ) < 0.1, ' < 0.10 MB', CONCAT(ROUND( (fg_dm_version.size/1048576), 2 ), ' MB' )) ,'') AS CO_FO_SIZE",                 
                'fg_dm_version.id as versionId', "DATE_FORMAT(fg_dm_version.created_at,'$mysqldate') as uploadedOn", 'fda.id as assignmentId', ' (CASE WHEN (fdi18.name_lang IS NULL) THEN fdd.name ELSE fdi18.name_lang END) as documentName', ' (CASE WHEN (fdi18.author_lang IS NULL) THEN fdd.author ELSE fdi18.author_lang END) as author', '(CASE WHEN (sbcati18.title_lang IS NULL) THEN sbcat.title ELSE sbcati18.title_lang END) as subcategory', '(CASE WHEN (cati18n.title_lang IS NULL) THEN cat.title ELSE cati18n.title_lang END) as category  ', "'Contact' as Type");
        $documentlistClass->setColumns($aColumns);
        $documentlistClass->setFrom();
        $documentlistClass->setCondition();
        $documentlistClass->addCondition("fdd.club_id = $clubId");
        $documentlistClass->addJoin(" INNER JOIN fg_dm_assigment AS fda ON fdd.id = fda.document_id AND fda.contact_id = $contactId AND fda.document_type = 'CONTACT' AND  fdd.document_type = 'CONTACT'");
        $documentlistClass->addJoin(' INNER JOIN fg_dm_document_subcategory as sbcat ON  sbcat.id = fdd.subcategory_id ');
        $documentlistClass->addJoin(" LEFT JOIN fg_dm_document_subcategory_i18n as sbcati18 ON  sbcat.id = sbcati18.id AND sbcati18.lang = '$language' ");
        $documentlistClass->addJoin(' INNER JOIN fg_dm_document_category as cat ON  cat.id = sbcat.category_id ');
        $documentlistClass->addJoin(" LEFT JOIN fg_dm_document_category_i18n as cati18n ON  cat.id = cati18n.id AND cati18n.lang = '$language' ");
        $documentlistClass->addOrderBy(' docname ASC ');

        $totallistquery = $documentlistClass->getResult();
        
        return $totallistquery;
    }
    
    /**
     * Function to get the log entries of a document
     *
     * @param int    $documentId Document Id
     *
     * @return array $result Array of log entries
     */
    public function getDocumentLogEntries($documentId)
    {
        $dateFormat = FgSettings::getMysqlDateTimeFormat();
        $defaultLang = $this->container->get('club')->get('default_lang');
        $clubDefaultLang = $this->container->get('club')->get('default_system_lang');
        $clubId = $this->container->get('club')->get('id');
        $firstname = "`" . $this->container->getParameter('system_field_firstname') . "`";
        $lastname = "`" . $this->container->getParameter('system_field_lastname') . "`";

        $sql = "SELECT d.id, d.club_id, d.documents_id, d.document_type AS documentType, d.kind, d.field, d.value_before, d.value_after, d.value_after_id, d.value_before_id, d.changed_by, d.date AS dateOriginal, date_format( d.date,'" . $dateFormat . "') AS date, checkActiveContact(d.changed_by, $clubId) as activeContact,"
               . "IF((checkActiveContact(d.changed_by, $clubId) is null && d.changed_by != 1), CONCAT(contactName(d.changed_by),' (',(select fc.title from fg_cm_contact ct left join fg_club fc on ct.main_club_id = fc.id where ct.id = d.changed_by),')') , contactName(d.changed_by) )as editedBy, "
               . " (CASE WHEN ((d.value_before IS NOT NULL AND d.value_before != '' AND d.value_before != '-') AND (d.value_after IS NULL OR d.value_after = '' OR d.value_after = '-')) THEN 'removed'"
               .    " WHEN ((d.value_before IS NULL OR d.value_before = '' OR d.value_before = '-') AND (d.value_after IS NOT NULL AND d.value_after != '' AND d.value_after != '-')) THEN 'added'"
               .    " WHEN ((d.value_before IS NOT NULL AND d.value_before != '') AND (d.value_after IS NOT NULL AND d.value_after != '')) THEN 'changed'"
               .    " WHEN ((d.value_before_id IS NOT NULL AND d.value_before_id != '') AND (d.value_after_id IS NOT NULL AND d.value_after_id != '')) THEN 'changed'"
               .    " WHEN ((d.value_before_id IS NOT NULL AND d.value_before_id != '') AND (d.value_after_id IS NULL OR d.value_after_id = '')) THEN 'removed'"
               .    " WHEN ((d.value_before_id IS NULL OR d.value_before_id = '') AND (d.value_after_id IS NOT NULL AND d.value_after_id != '')) THEN 'added'"
               .    " ELSE 'none'"
               . " END) AS status,"
               . "(CASE WHEN ((d.kind = 'deposited_with') AND (d.document_type = 'CLUB')) THEN (SELECT GROUP_CONCAT(COALESCE(NULLIF(fci18n.title_lang,''), fc.title) ORDER BY fc.title ASC SEPARATOR '#$$$#') FROM fg_club fc LEFT JOIN fg_club_i18n fci18n ON (fci18n.id = fc.id AND fci18n.lang= '". $defaultLang ."') WHERE FIND_IN_SET(fc.id, d.value_before_id))"
               .    " WHEN ((d.kind = 'deposited_with') AND (d.document_type = 'TEAM')) THEN (SELECT GROUP_CONCAT(IF(fri18n.title_lang != '' AND fri18n.title_lang IS NOT NULL, fri18n.title_lang, fr.title) ORDER BY fr.title ASC SEPARATOR '#$$$#') FROM fg_rm_role fr  LEFT JOIN fg_rm_role_i18n fri18n ON fri18n.id=fr.id AND fri18n.lang= '" . $clubDefaultLang . "' WHERE FIND_IN_SET(fr.id, d.value_before_id))"
               .    " WHEN ((d.kind = 'deposited_with') AND (d.document_type = 'WORKGROUP')) THEN (SELECT GROUP_CONCAT(IF(fri18n.title_lang != '' AND fri18n.title_lang IS NOT NULL, fri18n.title_lang, fr.title) ORDER BY fr.title ASC SEPARATOR '#$$$#') FROM fg_rm_role fr  LEFT JOIN fg_rm_role_i18n fri18n ON fri18n.id=fr.id AND fri18n.lang= '" . $clubDefaultLang . "' WHERE FIND_IN_SET(fr.id, d.value_before_id))"
               .    " ELSE ''"
               . " END) AS depositedWithBeforeIds,"
               . " (CASE WHEN ((d.kind = 'deposited_with') AND (d.document_type = 'CLUB')) THEN (SELECT GROUP_CONCAT(COALESCE(NULLIF(fci18n.title_lang,''), fc.title) ORDER BY fc.title ASC SEPARATOR '#$$$#') FROM fg_club fc LEFT JOIN fg_club_i18n fci18n ON (fci18n.id = fc.id AND fci18n.lang= '". $defaultLang ."') WHERE FIND_IN_SET(fc.id, d.value_after_id))"
               .    " WHEN ((d.kind = 'deposited_with') AND (d.document_type = 'TEAM')) THEN (SELECT GROUP_CONCAT(IF(fri18n.title_lang!='' AND fri18n.title_lang IS NOT NULL,fri18n.title_lang, fr.title) ORDER BY fr.title ASC SEPARATOR '#$$$#') FROM fg_rm_role fr  LEFT JOIN fg_rm_role_i18n fri18n ON fri18n.id=fr.id AND fri18n.lang= '" . $clubDefaultLang . "' WHERE FIND_IN_SET(fr.id, d.value_after_id))"
               .    " WHEN ((d.kind = 'deposited_with') AND (d.document_type = 'WORKGROUP')) THEN (SELECT GROUP_CONCAT(IF(fri18n.title_lang != '' AND fri18n.title_lang IS NOT NULL, fri18n.title_lang, fr.title) ORDER BY fr.title ASC SEPARATOR '#$$$#') FROM fg_rm_role fr  LEFT JOIN fg_rm_role_i18n fri18n ON fri18n.id=fr.id AND fri18n.lang= '" . $clubDefaultLang . "' WHERE FIND_IN_SET(fr.id, d.value_after_id))"
               .    "  ELSE ''"
               . " END) AS depositedWithAfterIds,"
               . "(CASE WHEN ((d.kind = 'visible_for') AND (d.document_type = 'TEAM')) THEN (SELECT GROUP_CONCAT(IF(fi18n.title_lang != '' AND fi18n.title_lang IS NOT NULL, fi18n.title_lang,  f.title) ORDER BY f.title ASC SEPARATOR '#$$$#' ) FROM fg_rm_function f LEFT JOIN fg_rm_function_i18n fi18n ON fi18n.id=f.id AND fi18n.lang='" . $clubDefaultLang . "' WHERE FIND_IN_SET(f.id, d.value_before_id))"
               .    " ELSE ''"
               . " END) AS selectedFunctionsBefore,"
               . "(CASE WHEN ((d.kind = 'visible_for') AND (d.document_type = 'TEAM')) THEN (SELECT GROUP_CONCAT(IF(fi18n.title_lang != '' AND fi18n.title_lang IS NOT NULL, fi18n.title_lang,  f.title) ORDER BY f.title ASC SEPARATOR '#$$$#' ) FROM fg_rm_function f LEFT JOIN fg_rm_function_i18n fi18n ON fi18n.id=f.id AND fi18n.lang='" . $clubDefaultLang . "' WHERE FIND_IN_SET(f.id, d.value_after_id))"
               .    "  ELSE ''"
               . " END) AS selectedFunctionsAfter,"
               . "(CASE WHEN ((d.kind = 'included') OR (d.kind = 'excluded')) THEN (SELECT GROUP_CONCAT(DISTINCT(IF (fc.is_company=0 ,CONCAT(" . $lastname . ",' '," . $firstname . "), `9` )) SEPARATOR '#$$$#') FROM fg_cm_contact fc LEFT JOIN master_system ms ON ms.fed_contact_id=fc.fed_contact_id WHERE FIND_IN_SET(fc.id, d.value_before_id))"
               .    " ELSE ''"
               . " END) AS selectedContactsBefore,"
               . "(CASE WHEN ((d.kind = 'included') OR (d.kind = 'excluded')) THEN (SELECT GROUP_CONCAT(DISTINCT(IF (fc.is_company=0 ,CONCAT(" . $lastname . ",' '," . $firstname . "), `9` )) SEPARATOR '#$$$#') FROM fg_cm_contact fc LEFT JOIN master_system ms ON ms.fed_contact_id=fc.fed_contact_id WHERE FIND_IN_SET(fc.id, d.value_after_id))"
               .    " ELSE ''"
               . " END) AS selectedContactsAfter"
               . " FROM fg_dm_document_log d"
               . " WHERE d.documents_id= :documentId ORDER BY dateOriginal DESC";

        $result = $this->conn->fetchAll($sql, array('documentId' => $documentId));

        return $result;
    }
    
    /**
     * Function to save I18n details of documents
     *
     * @param array $documentI18nArr DocumentI18n details array
     */
    public function saveDocumentI18nDetails($documentI18nArr)
    {
        $qry = "INSERT INTO fg_dm_documents_i18n(id, lang, name_lang, description_lang, author_lang) VALUES ";
        $cnt = 0;
        foreach ($documentI18nArr as $key => $valArr) {
            if ($cnt != 0) {
                $qry .= ', ';
            }
            $name = ($valArr['name'] != '') ? FgUtility::getSecuredData($valArr['name'], $this->conn, true) : "''";
            $author = ($valArr['author'] != '') ? FgUtility::getSecuredData($valArr['author'], $this->conn, true) : "''";
            $description = ($valArr['description'] != '') ? FgUtility::getSecuredData($valArr['description'], $this->conn, true) : "''";

            $qry .= "(" . $valArr['id'] . ",'" . $valArr['lang'] . "'," . $name . "," . $description . "," . $author . ")";
            $cnt++;
        }
        $qry .= ' ON DUPLICATE KEY UPDATE name_lang=VALUES(name_lang),description_lang=VALUES(description_lang),author_lang=VALUES(author_lang)';
        $this->conn->executeQuery($qry);
        
        return;
    }
    
    /**
     * Function to get other documents not assined to the contacts and not excluded for this contact in that club Used in autocomplete
     * 
     * @param int    $contactId Assigned contactId
     * @param int    $clubId    Club of document
     * @param string $language  Language key
     * @param string $key       Searching key
     * 
     * @return array
     */
    public function getOtherExistingDocuments($contactId, $clubId, $language, $key)
    {
        $documents = $this->em->getRepository('CommonUtilityBundle:FgDmAssigment')->getContactsDocuments($contactId, $clubId);
        // documents already assigned to a contact are excluded from the search
        $notInDocuments = ($documents['documents']) ? $documents['documents'] : '';
        if ($notInDocuments == '') {
            $notInDocuments = 0;
        }
        $sql = "SELECT D.id as id, (CASE WHEN (DL.name_lang IS NULL OR DL.name_lang = '') THEN D.name ELSE DL.name_lang END) as title "
                . "FROM fg_dm_documents D "
                . "LEFT JOIN fg_dm_documents_i18n DL ON D.id = DL.id AND DL.lang = :language "
                . "WHERE D.club_id = :clubId AND D.document_type = :contact "
                . "AND D.id NOT IN (  $notInDocuments ) AND (DL.name_lang LIKE '$key%' OR D.name LIKE '$key%' ) "
                . "GROUP BY D.id ORDER BY title ASC";
        $results = $this->conn->fetchAll($sql, array('clubId' => $clubId, 'language' => $language, 'contact' => 'CONTACT'));

        return $results;
    }
    
    /**
     * Function to get other documents not assined to the club
     * Used in autocomplete
     * 
     * @param int    $currentClubId assigned club
     * @param int    $clubId        club of document
     * @param string $language      language key
     * @param string $key           searching key
     */
    public function getOtherExistingClubDocuments($currentClubId, $clubId, $language, $key)
    {
        $documents = $this->getClubDocuments($currentClubId, $clubId);
        // documents already assigned to a club are excluded from the search
        $notInDocuments = ($documents['documents']) ? $documents['documents'] : '';
        // documents excluded for this club are excluded from the search
        $excludedDocuments = $this->em->getRepository('CommonUtilityBundle:FgDmAssigment')->getExcludedDocuments($currentClubId, 'club');
        if ($notInDocuments == '') {
            $notInDocuments = 0;
        }
        if ($excludedDocuments == '') {
            $excludedDocuments = 0;
        }
        $sql = "SELECT DISTINCT D.id as id, (CASE WHEN (DL.name_lang IS NULL OR DL.name_lang = '') THEN D.name ELSE DL.name_lang END) as title "
                . "FROM fg_dm_documents D "
                . "LEFT JOIN fg_dm_documents_i18n DL ON D.id = DL.id AND DL.lang = :language "
                . "WHERE D.club_id = :clubId AND D.document_type = :club "
                . "AND D.id NOT IN (  $notInDocuments ) AND (DL.name_lang LIKE '$key%'  OR D.name LIKE '$key%' ) "
                . "AND ( D.deposited_with != 'ALL' OR (D.deposited_with = 'ALL' AND D.id IN (  $excludedDocuments ) ) ) "
                . "GROUP BY D.id ORDER BY title ASC";
        $results = $this->conn->fetchAll($sql, array('clubId' => $clubId, 'language' => $language, 'club' => 'CLUB'));

        return $results;
    }
    
    /**
     * Function to update document filter contacts
     * 
     * @param int     $documentId            Document Id
     * @param string  $filterData            Filter data
     * @param array   $oldFilterDepositedArr Filter deposited with array
     * @param boolean $isCronJob             From cron or not
     * 
     * @return boolean
     */
    public function updateDocumentFilterContacts($documentId, $filterData = '', $oldFilterDepositedArr = array(), $isCronJob = false)
    {
        $documentQry = '';
        $contactForUpdate = array();
        //if running through cron job
        if ($isCronJob) {
            $oldFilterDepositedWithSelectionIds = $this->em->getRepository('CommonUtilityBundle:FgDmAssigment')->getPreviousAssignments($documentId, 'contactfilter');
            $oldFilterDepositedArr = ($oldFilterDepositedWithSelectionIds) ? explode(',',$oldFilterDepositedWithSelectionIds) : array();
        }
        // delete filter contact assignments
        $this->em->getRepository('CommonUtilityBundle:FgDmDocuments')->deleteDocumentFilterContacts($documentId, 'FILTER');
        $club = $this->container->get('club');
        $clubType = $club->get('type');   
        $contactlistClass = new Contactlist($this->container, 1, $club, 'contact');
        $contactlistClass->fedContactSelectionFlag = false;
        $contactlistClass->setColumns(array(0 => $documentId, 1 => "'CONTACT'", 2 => "'FILTER'"));
        $contactlistClass->setFrom();
        $contactlistClass->setCondition();
        $filterarr = ($filterData != '') ? json_decode($filterData, true): array();
        $filter = array_shift($filterarr);
        $filterObj = new Contactfilter($this->container, $contactlistClass, $filter, $club);
        if ($filter) {
            $sWhere = " " . $filterObj->generateFilter();
            $contactlistClass->addCondition($sWhere);
        }
        // excluded contacts are ommitted from filter
        $excludeContactsQry = 'SELECT contact_id FROM fg_dm_assigment_exclude WHERE document_id =' . $documentId;
        $sWhere = ($clubType=='federation' || $clubType=='sub_federation') ? " mc.fed_contact_id NOT IN (" . $excludeContactsQry . ")" : " mc.contact_id NOT IN (" . $excludeContactsQry . ")";
        $contactlistClass->addCondition($sWhere);
        //  manual selected contacts are ommitted from filter
        $sWhere = ($clubType=='federation' || $clubType=='sub_federation') ? " mc.fed_contact_id NOT IN (" . $excludeContactsQry . ")" : " mc.contact_id NOT IN (" . $excludeContactsQry . ")";
        $contactlistClass->addCondition($sWhere);
        $documentQry = $contactlistClass->getResult();
        // to update last updated date for filter 
        $docFilterRes = $this->conn->fetchAll($documentQry);
        if(count($docFilterRes)) {
            foreach ($docFilterRes as $filterRes) {
                $contactForUpdate[] = $filterRes['id'];
            }
        }
        if ($documentQry != '') {
            $documentQry = 'INSERT INTO `fg_dm_assigment`(`contact_id`, `document_id`, `document_type`, `contact_assign_type`) ' . $documentQry;
        }
        $this->conn->executeQuery($documentQry);
        //update contact last updated field for only unique filter fields
        $array1 = array_diff($oldFilterDepositedArr, $contactForUpdate);
        $array2 = array_diff($contactForUpdate, $oldFilterDepositedArr);
        $contactForLastUpdate = array_merge($array2, $array1);
        if(count($contactForLastUpdate)) {
            $contactIdsString = implode(',', $contactForLastUpdate);
            $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->updateLastUpdated($contactIdsString, 'id');
        }

        return;
    }
    
    /**
     * Function to update maintable entry with clubdefault language entry
     *
     * @param string $clubDefaultLang Club default language
     * @param int    $docId           Document id 
     * 
     * @return boolean
     */
    public function updateMainTable($clubDefaultLang, $docId)
    {
        $mainFileds = array('name','description','author');
        $i18Fields = array('name_lang','description_lang','author_lang');
        $fieldsList = array( 'mainTable' => 'fg_dm_documents',
            'i18nTable' => 'fg_dm_documents_i18n',
            'mainField' => $mainFileds,
            'i18nFields' => $i18Fields
            
        );
        $where = 'A.id = '.$docId;
        $updateMainTable = FgUtility::updateDefaultTable($clubDefaultLang, $fieldsList, $where);
        $this->conn->executeQuery($updateMainTable);
        
        return true;
    }
    
    /**
     * Function to get all document bookmark count.
     *
     * @param int    $clubId    Club id
     * @param String $upperLevelHeirarchy Higher Club heirarchy
     *
     * @return array.
     */
    public function getDocumentBookmarksCount($clubId ,$upperLevelHeirarchy )
    {
        $countResult = array();
        if (empty($upperLevelHeirarchy)) {
            $upperLevelHeirarchy = array(0 => $clubId);
        }
        $clubIds = implode(',', $upperLevelHeirarchy);

        $docCount = "SELECT "
                . "COUNT(CASE WHEN d.document_type = 'TEAM' THEN 1 END) as teamCount, "
                . "COUNT(CASE WHEN d.document_type = 'WORKGROUP' THEN 1 END) as workgroupCount, "
                . "COUNT(CASE WHEN d.document_type = 'CONTACT' THEN 1 END) as contactCount, "
                . "COUNT(CASE WHEN d.document_type = 'CLUB' THEN 1 END) as clubCount "
                . "FROM fg_dm_documents d "
                . "WHERE IF ((d.document_type = 'CLUB' AND d.deposited_with <> 'NONE' AND d.club_id <> $clubId), IF ((d.deposited_with = 'ALL'),(d.club_id IN ($clubIds) AND d.id NOT IN (SELECT e.document_id FROM fg_dm_assigment_exclude e WHERE e.document_id = d.id AND e.club_id = $clubId)),d.id IN (SELECT da.document_id FROM fg_dm_assigment da WHERE da.document_type = 'CLUB' AND da.club_id = $clubId)),d.club_id = $clubId) ";
        $docResult = $this->conn->fetchAll($docCount);
        //TOPE NAV - DOCUMENT MODULE COUNT

        $countResult[0]['teamDocCount'] = $docResult[0]['teamCount'];
        $countResult[0]['workgroupDocCount'] = $docResult[0]['workgroupCount'];
        $countResult[0]['contactDocCount'] = $docResult[0]['contactCount'];
        $countResult[0]['clubDocCount'] = $docResult[0]['clubCount'];

        return $countResult[0];
    }
    
    /**
     * Function to get document ids assigned to a particular club
     * get results concatenated with ','
     *
     * @param Int $clubId        assigned club id
     * @param Int $currentClubId current club id
     *
     * @return Array Result array
     */
    public function getClubDocuments($clubId, $currentClubId)
    {
        $sql = 'SELECT GROUP_CONCAT(DISTINCT d.id) as documents'
                . ' FROM fg_dm_documents d'
                . ' WHERE (d.document_type = :docType AND d.club_id = :currentClubId AND d.deposited_with <> "NONE" AND '
                . ' (CASE WHEN (d.deposited_with = "ALL") THEN d.id NOT IN (SELECT e.document_id FROM fg_dm_assigment_exclude e WHERE e.document_id=d.id AND e.club_id=:clubId)'
                . ' WHEN (d.deposited_with = "SELECTED") THEN d.id IN (SELECT e.document_id FROM fg_dm_assigment e WHERE e.document_id=d.id AND e.club_id=:clubId)'
                . ' ELSE "" END))';
        $results = $this->conn->fetchAll($sql, array('clubId' => $clubId, 'currentClubId' => $currentClubId, 'docType' => 'CLUB'));

        return $results[0];
    }
    
    /**
     * Function to delete clubs assignments for a club
     * 
     * @param int $clubId        club id to delete document assignment
     * @param int $currentClubId Current club id
     */
    public function deleteDocumentAssignment($clubId, $currentClubId){
        $deleteSql = 'DELETE a FROM fg_dm_assigment a INNER JOIN fg_dm_documents d ON d.id = a.document_id WHERE a.document_type = :docType AND a.club_id = :clubId AND d.club_id = :currentClubId AND d.document_type = :docType';
        $this->conn->executeQuery($deleteSql, array('clubId' => $clubId, 'currentClubId' => $currentClubId, 'docType' => 'CLUB'));
    }
}
