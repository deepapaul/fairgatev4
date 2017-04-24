<?php
/**
 * Contact Table Element - Table Settings
 *
 */
namespace Website\CMSBundle\Util;

/**
 * FgTablesettings
 *
 * This class is used for handling table settings for contact table element
 *
 * @package    CommonUtilityBundle
 * @subpackage Util
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
class FgTablesettings
{

    private $conn;
    private $container;
    private $club;
    private $columnDatas;
    private $columnArray = array();
    private $contactLang;

    /**
     * The columns in which text is to be searched
     * 
     * @var array 
     */
    private $searchableColumns = array();
    /**
     * depend column details
     * @var array 
     */
    public $dependColumns = array();
    /**
     * Separate listing enabled column
     * @var String 
     */
    public $separateListColumn = '';
    /**
     * Separate listing is enable or not
     * @var boolean 
     */
    public $separateListing = false;
    /**
     * Separate listing functions
     * @var String 
     */
    public $separateListFun ='';

    public function __construct($container, $columns, $club)
    {
        $this->columnDatas = $columns;
        $this->club = $club;
        $this->container = $container;
        $this->conn = $this->container->get('database_connection');
        $this->contactFields = $this->club->get('allContactFields');
        $this->contactLang = $club->get('default_lang');
    }

    /**
     * get the table element columns
     *
     * @return array
     */
    public function getColumns()
    {
        foreach ($this->columnDatas as $columnData) {
            switch ($columnData['type']) {
                case 'contactname':
                    $this->contactName($columnData);
                    break;
                case 'CF':
                    $this->contactFields($columnData);
                    break;
                case 'AF':
                    $this->analysisFields($columnData);
                    break;
                case 'CM':
                    $this->clubMembership($columnData);
                    break;
                case 'FM':
                    $this->fedMembership($columnData);
                    break;
                case 'WA':
                case 'TA':
                case 'FRA':
                case 'RCA':
                case 'FRCA':
                case 'SFRCA' :
                    $this->assignmentFields($columnData);
                    break;
                case 'TF':
                case 'WF':
                case 'CRF':
                case 'CSFRF':
                case 'CFRF' :
                case 'IFRF':
                case 'ISFRF':
                case 'IRF' :
                    $this->functionFields($columnData);
                    break;
                case 'FI':
                    $this->federationInfo($columnData);
                    break;
                case 'PROFILE_PIC':
                    $this->getProfilePic($columnData);
            }
        }
        return $this->columnArray;
    }

    /**
     * CONTACT NAME COLUMN
     * @param ARRAY $columnData
     */
    private function contactName($columnData)
    {
        if (isset($columnData['withOutComma']) && $columnData['withOutComma'] == true) {
            $contactNameQry = "(CASE WHEN (fg_cm_contact.is_company = 1) THEN IF ((fg_cm_contact.has_main_contact = 1), CONCAT(ms.`9`, ' (', ms.`2`, ' ', ms.`23`, ')'), ms.`9`) ELSE CONCAT(ms.`2`, ' ', ms.`23`) END)";
            array_push($this->columnArray, $contactNameQry . " AS `{$columnData['name']}`");
            array_push($this->searchableColumns, $contactNameQry);
        } else {
            array_push($this->columnArray, "(CASE WHEN (fg_cm_contact.is_company = 1) THEN IF ((fg_cm_contact.has_main_contact = 1), CONCAT(ms.`9`, ' (', ms.`23`, ',', ms.`2`, ')'), ms.`9`) ELSE CONCAT(ms.`23`, ', ', ms.`2`) END) AS `{$columnData['name']}`");
        }

        $showPic = ($columnData['showProfilePicture'] == 0) ? 0 : $columnData['showProfilePicture'];
        if ($columnData['linkUrl'] == '' || $columnData['linkUrl'] == 'null') {
            $linkUrl = "'-'";
        } else if ($this->container->getParameter('system_field_website') == $columnData['linkUrl']) {
            $linkUrl = '(SELECT ms.`' . $columnData['linkUrl'] . '`FROM fg_cm_attribute a WHERE a.id =' . $columnData['linkUrl'] . ')';
        } else {
            $linkUrl = '(SELECT mc.`' . $columnData['linkUrl'] . '`FROM fg_cm_attribute a WHERE a.id =' . $columnData['linkUrl'] . ')';
        }
        array_push($this->columnArray, " '{$showPic}' AS `{$columnData['name']}_showProfilePicture`");
        if ($showPic == 1) {
            array_push($this->columnArray, ' IF (fg_cm_contact.is_company = 1, `' . $this->container->getParameter('system_field_companylogo') . '`, `' . $this->container->getParameter('system_field_communitypicture') . "`)  AS Gprofile_company_pic");
        }


        array_push($this->columnArray, " {$linkUrl} AS `{$columnData['name']}_linkUrl`");
    }

    /**
     * handle generic fields
     *
     * @param array $columnData
     */
    private function analysisFields($columnData)
    {
        $cfDobId = $this->container->getParameter('system_field_dob');
        switch ($columnData['id']) {
            case 'age':
                $ageQry = 'IF(EXTRACT(YEAR FROM `' . $cfDobId . "`) = 0, '', DATE_FORMAT(FROM_DAYS(DATEDIFF(CURRENT_DATE,`" . $cfDobId . "` )),'%y'))";
                array_push($this->columnArray, $ageQry . " AS `{$columnData['name']}`");
                array_push($this->searchableColumns, $ageQry);

                array_push($this->columnArray, "(SELECT confirm_status FROM fg_cm_change_toconfirm AS CT WHERE CT.club_id={$this->club->get('id')} AND CT.contact_id=fg_cm_contact.id AND CT.attribute_id=" . $cfDobId . " LIMIT 1) AS `{$columnData['name']}_flag`");

                if ($this->contactFields[$cfDobId]['is_set_privacy_itself'] == 0) {
                    array_push($this->columnArray, "'{$this->contactFields[$cfDobId]['privacy_contact']}'  AS `{$columnData['name']}_visibility`");
                } else {
                    array_push($this->columnArray, "(SELECT IF(PS.privacy IS NULL,'{$this->contactFields['privacy_contact']}',PS.privacy)  FROM fg_cm_contact_privacy AS PS WHERE PS.contact_id=fg_cm_contact.id AND PS.attribute_id=" . $cfDobId . ")  AS `{$columnData['name']}_visibility`");
                }

                break;
            case 'birth_year':
                $birthYearQry = 'IF(EXTRACT(YEAR FROM `' . $cfDobId . "`) = 0, '', EXTRACT(YEAR FROM `" . $cfDobId . "`))";
                array_push($this->columnArray, $birthYearQry . " AS `{$columnData['name']}`");
                array_push($this->searchableColumns, $birthYearQry);

                array_push($this->columnArray, "(SELECT confirm_status AS CT FROM fg_cm_change_toconfirm AS CT WHERE CT.club_id={$this->club->get('id')} AND CT.contact_id=fg_cm_contact.id AND CT.attribute_id=" . $cfDobId . " LIMIT 1)  AS `{$columnData['name']}_flag`");

                if ($this->contactFields[$cfDobId]['is_set_privacy_itself'] == 0) {
                    array_push($this->columnArray, "'{$this->contactFields[$cfDobId]['privacy_contact']}'  AS `{$columnData['name']}_visibility`");
                } else {
                    array_push($this->columnArray, "(SELECT IF(PS.privacy IS NULL,'{$this->contactFields['privacy_contact']}',PS.privacy)  FROM fg_cm_contact_privacy AS PS WHERE PS.contact_id=fg_cm_contact.id AND PS.attribute_id=" . $cfDobId . ")  AS `{$columnData['name']}_visibility`");
                }

                break;
        }
    }

    /**
     * For find the actual contact fields column.
     *
     * @param array $columnData
     */
    private function contactFields($columnData)
    {
        $contactFieldQry = '`' . $columnData['id'] . "`";
        switch ($columnData['id']) {
            default:
                array_push($this->columnArray, $contactFieldQry . " AS `{$columnData['name']}`");
                array_push($this->searchableColumns, $contactFieldQry);
                $type = $this->contactFields[$columnData['id']]['type'];
                $contactFieldsId = $columnData['id'];
                array_push($this->columnArray, " '{$contactFieldsId}' AS `{$columnData['name']}_fieldId`");
                array_push($this->columnArray, " '{$type}' AS `{$columnData['name']}_type`");
                if ($this->contactFields[$columnData['id']]['type'] == 'imageupload' || $this->contactFields[$columnData['id']]['type'] == 'fileupload') {
                    $clubId = $this->contactFields[$columnData["id"]]["club_id"];
                    array_push($this->columnArray, " {$clubId} AS `{$columnData['name']}_clubid`");
                }

                if ($this->contactFields[$columnData['id']]['is_set_privacy_itself'] == 0) {
                    array_push($this->columnArray, "'{$this->contactFields[$columnData['id']]['privacy_contact']}'  AS `{$columnData['name']}_visibility`");
                } else {
                    array_push($this->columnArray, "(SELECT COALESCE(MIN(privacy), '{$this->contactFields[$columnData['id']]['privacy_contact']}') FROM `fg_cm_contact_privacy` WHERE `contact_id` = fg_cm_contact.id AND attribute_id={$columnData['id']})  AS `{$columnData['name']}_visibility`");
                }
                break;
        }
    }

    /**
     * federation infos
     *
     * @param array $columnData
     */
    private function federationInfo($columnData)
    {
        switch ($columnData['id']) {
            case 'clubs':
                if ($columnData['sub_ids'] != null) {
                    array_push($this->columnArray, "(SELECT GROUP_CONCAT(IF(fg_club.id = fg_cm_contact.main_club_id, CONCAT(COALESCE(NULLIF(fg_club_i18n.title_lang,''), fg_club.title),'#mainclub#'), COALESCE(NULLIF(fg_club_i18n.title_lang,''), fg_club.title)) SEPARATOR ', ') FROM fg_club LEFT JOIN fg_club_i18n ON (fg_club.id = fg_club_i18n.id AND fg_club_i18n.lang='{$this->contactLang}') WHERE fg_club.club_type IN('standard_club','sub_federation_club','federation_club') AND fg_club.id IN (SELECT distinct rrc.assined_club_id FROM fg_rm_role_contact rrc INNER JOIN fg_rm_category_role_function rcrf ON rcrf.id = rrc.fg_rm_crf_id INNER JOIN fg_cm_contact AS dfc ON rrc.contact_id=dfc.id WHERE dfc.fed_contact_id=fg_cm_contact.fed_contact_id AND rcrf.function_id IN ({$columnData['sub_ids']}))) AS `{$columnData['name']}`");
                    $clubsQry = "(SELECT GROUP_CONCAT(COALESCE(NULLIF(fg_club_i18n.title_lang,''), fg_club.title) SEPARATOR ', ') FROM fg_club LEFT JOIN fg_club_i18n ON (fg_club.id = fg_club_i18n.id AND fg_club_i18n.lang='{$this->contactLang}') WHERE fg_club.club_type IN('standard_club','sub_federation_club','federation_club') AND fg_club.id IN (SELECT distinct rrc.assined_club_id FROM fg_rm_role_contact rrc INNER JOIN fg_rm_category_role_function rcrf ON rcrf.id = rrc.fg_rm_crf_id INNER JOIN fg_cm_contact AS dfc ON rrc.contact_id=dfc.id  WHERE dfc.fed_contact_id=fg_cm_contact.fed_contact_id AND rcrf.function_id IN ({$columnData['sub_ids']})))";
                    array_push($this->searchableColumns, $clubsQry);
                } else {
                    array_push($this->columnArray, "(SELECT GROUP_CONCAT(IF(fg_club.id = fg_cm_contact.main_club_id, CONCAT(COALESCE(NULLIF(fg_club_i18n.title_lang,''), fg_club.title),'#mainclub#'), COALESCE(NULLIF(fg_club_i18n.title_lang,''), fg_club.title)) SEPARATOR ', ') FROM fg_club LEFT JOIN fg_club_i18n ON (fg_club.id = fg_club_i18n.id AND fg_club_i18n.lang='{$this->contactLang}') WHERE fg_club.club_type IN('standard_club','sub_federation_club','federation_club') AND fg_club.id IN (SELECT distinct dfc.club_id FROM fg_cm_contact AS dfc WHERE dfc.fed_contact_id=fg_cm_contact.fed_contact_id)) AS `{$columnData['name']}`");
                    $clubsQry = "(SELECT GROUP_CONCAT(COALESCE(NULLIF(fg_club_i18n.title_lang,''), fg_club.title) SEPARATOR ', ') FROM fg_club LEFT JOIN fg_club_i18n ON (fg_club.id = fg_club_i18n.id AND fg_club_i18n.lang='{$this->contactLang}') WHERE fg_club.club_type IN('standard_club','sub_federation_club','federation_club') AND fg_club.id IN (SELECT distinct dfc.club_id FROM fg_cm_contact AS dfc WHERE dfc.fed_contact_id=fg_cm_contact.fed_contact_id))";
                    array_push($this->searchableColumns, $clubsQry);
                }
                $type = 'FI';
                array_push($this->columnArray, " '{$type}' AS `{$columnData['name']}_type`");
                break;
            case 'sub_federations':
                $subFederationQry = "(SELECT GROUP_CONCAT(COALESCE(NULLIF(fg_club_i18n.title_lang,''), fg_club.title) SEPARATOR ', ') FROM fg_club LEFT JOIN fg_club_i18n ON (fg_club.id = fg_club_i18n.id AND fg_club_i18n.lang='{$this->contactLang}') WHERE fg_club.is_sub_federation=1 AND fg_club.id IN (SELECT distinct dfc.club_id FROM fg_cm_contact AS dfc WHERE dfc.fed_contact_id=fg_cm_contact.fed_contact_id))";
                array_push($this->columnArray, $subFederationQry . " AS `{$columnData['name']}`");
                array_push($this->searchableColumns, $subFederationQry);
                break;
            case 'clubs_executive_board_functions':
                $checkContactField = ($this->club->get('type') == "federation") ? "fed_contact_id" : "subfed_contact_id";
                $clubExeBoardFnQry = "(SELECT GROUP_CONCAT(DISTINCT IF(drfi18n.title_lang='' OR drfi18n.title_lang IS NULL,drf.title,drfi18n.title_lang) ORDER BY drf.sort_order ASC SEPARATOR ', ')  FROM fg_rm_category_role_function AS dcrf  "
                    . "INNER JOIN fg_rm_role AS frr ON frr.category_id=dcrf.category_id AND frr.is_executive_board=1 "
                    . "INNER JOIN fg_rm_function AS drf ON dcrf.function_id=drf.id AND drf.is_federation =1 "
                    . "LEFT JOIN fg_rm_function_i18n AS drfi18n ON drf.id=drfi18n.id AND drfi18n.lang='{$this->contactLang}' "
                    . "INNER JOIN fg_rm_role_contact AS drc ON dcrf.id=drc.fg_rm_crf_id "
                    . "INNER JOIN fg_cm_contact DFC ON DFC.id=drc.contact_id  "
                    . "WHERE  fg_cm_contact.id = fg_cm_contact.{$checkContactField} AND fg_cm_contact.club_id={$this->club->get('id')} AND DFC.{$checkContactField}=fg_cm_contact.id)";
                array_push($this->columnArray, $clubExeBoardFnQry . " AS `{$columnData['name']}`");
                array_push($this->searchableColumns, $clubExeBoardFnQry);
                break;
        }
    }

    /**
     * To handle club membership details
     *
     * @param array $columnData
     */
    private function clubMembership($columnData)
    {
        switch ($columnData['id']) {
            case 'membership':
                $membershipQry = "(Select IF(fg_cm_membership_i18n.title_lang !='' AND fg_cm_membership_i18n.title_lang IS NOT NULL, fg_cm_membership_i18n.title_lang, fg_cm_membership.title) FROM fg_cm_membership LEFT JOIN fg_cm_membership_i18n  ON fg_cm_membership.id=fg_cm_membership_i18n.id AND fg_cm_membership_i18n.lang='{$this->contactLang}'  WHERE fg_cm_membership.id=fg_cm_contact.club_membership_cat_id  )";
                array_push($this->columnArray, $membershipQry . " AS `{$columnData['name']}`");
                array_push($this->searchableColumns, $membershipQry);
                break;
            case 'club_member_years':
                $memberYearsQry = "(Select sum(IF(MH.leaving_date IS NOT NULL,ROUND(DATEDIFF(MH.leaving_date,MH.joining_date)/365.25,1),ROUND(DATEDIFF(CURDATE(),MH.joining_date)/365.25,1))) AS fed_membership_years from fg_cm_membership_history MH where MH.membership_type='club' and MH.contact_id=fg_cm_contact.id group by MH.contact_id)";
                array_push($this->columnArray, $memberYearsQry . " AS `{$columnData['name']}`");
                array_push($this->searchableColumns, $memberYearsQry);
                array_push($this->columnArray, " 'number' AS `{$columnData['name']}_type`");
                break;
        }
    }

    /**
     * To handle federation membership details
     *
     * @param array $columnData
     */
    private function fedMembership($columnData)
    {
        switch ($columnData['id']) {
            case 'fed_membership':
                $fedMembershipQry = "(Select IF(fg_cm_membership_i18n.title_lang !='' AND fg_cm_membership_i18n.title_lang IS NOT NULL, fg_cm_membership_i18n.title_lang, fg_cm_membership.title) FROM fg_cm_membership LEFT JOIN fg_cm_membership_i18n  ON fg_cm_membership.id=fg_cm_membership_i18n.id AND fg_cm_membership_i18n.lang='{$this->contactLang}'  WHERE IF(fg_cm_contact.is_fed_membership_confirmed = '0',fg_cm_membership.id = fg_cm_contact.fed_membership_cat_id,fg_cm_membership.id = fg_cm_contact.old_fed_membership_id)  AND fg_cm_contact.fed_membership_cat_id IS NOT NULL )";
                array_push($this->columnArray, $fedMembershipQry . " AS `{$columnData['name']}`");
                array_push($this->searchableColumns, $fedMembershipQry);
                break;
            case 'fed_member_years':
                $fedMemberYearsQry = "(Select sum(IF(MH.leaving_date IS NOT NULL,ROUND(DATEDIFF(MH.leaving_date,MH.joining_date)/365.25,1),ROUND(DATEDIFF(CURDATE(),MH.joining_date)/365.25,1))) AS fed_membership_years from fg_cm_membership_history MH where MH.membership_type='federation' and MH.contact_id=fg_cm_contact.fed_contact_id group by MH.contact_id)";
                array_push($this->columnArray, $fedMemberYearsQry . " AS `{$columnData['name']}`");
                array_push($this->searchableColumns, $fedMemberYearsQry);
                array_push($this->columnArray, " 'number' AS `{$columnData['name']}_type`");
                break;
        }
    }

    /**
     * assignment fields
     *
     * @param array $columnData
     */
    private function assignmentFields($columnData)
    {
        $query = '';
        $query1 = '';
        $extraCondition = '';
        list($separateColumn,$columnId) = explode('_', $this->separateListColumn);
        // Separate listing column handle
        if ($this->separateListing && $columnData['type'] == $separateColumn && $columnId==$columnData['name']) {

            switch ($columnData['type']) {
                case 'TA':
                case 'WA':
                case 'RCA':
                case 'FRCA':
                case 'SFRCA' :
                case 'FRA':
                    $assignmentQry = "COALESCE(NULLIF(mrri18n.title_lang,''), mrr.title)";
                    array_push($this->columnArray, $assignmentQry . " AS `{$columnData['name']}`");
                    array_push($this->searchableColumns, $assignmentQry);
                    $sortQuery = " (SELECT CONCAT(CATSORT.sort_order,IF(TEAMCATSORT.sort_order IS NULL, '', TEAMCATSORT.sort_order),'.',ROLESORT.sort_order) FROM fg_rm_role ROLESORT "
                        . "INNER JOIN fg_rm_category CATSORT ON CATSORT.id = ROLESORT.category_id "
                        . "LEFT JOIN fg_team_category TEAMCATSORT ON TEAMCATSORT.id = ROLESORT.team_category_id "
                        . "WHERE ROLESORT.id = mrr.id)";
                    array_push($this->columnArray, $sortQuery . " AS `{$columnData['name']}_sortorder`");
                    break;
            }
        } else if (in_array($columnData['type'], $this->dependColumns) && $columnData['id']==$this->separateListFun) { // Separate listing dependend column handle area           
            switch ($columnData['type']) {
                case 'TA':
                case 'WA':
                case 'RCA':
                case 'FRCA':
                case 'SFRCA' :
                case 'FRA':                   
                    $functionQry = "GROUP_CONCAT(COALESCE(NULLIF(mrri18n.title_lang,''), mrr.title) ORDER BY mrr.sort_order ASC SEPARATOR ', ')";
                    array_push($this->columnArray, $functionQry . " AS `{$columnData['name']}`");
                    array_push($this->searchableColumns, "COALESCE(NULLIF(mrri18n.title_lang,''), mrr.title)");
                    $sortQuery = " (SELECT CONCAT(CATSORT.sort_order,IF(TEAMCATSORT.sort_order IS NULL, '', TEAMCATSORT.sort_order),'.',ROLESORT.sort_order) FROM fg_rm_role ROLESORT "
                        . "INNER JOIN fg_rm_category CATSORT ON CATSORT.id = ROLESORT.category_id "
                        . "LEFT JOIN fg_team_category TEAMCATSORT ON TEAMCATSORT.id = ROLESORT.team_category_id "
                        . "WHERE ROLESORT.id = mrr.id)";
                    array_push($this->columnArray, $sortQuery . " AS `{$columnData['name']}_sortorder`");
                    break;
                
            }
        } else {   //Normal column handle area
            if (isset($columnData['is_fed_cat']) && $columnData['is_fed_cat'] == 0) {
                $extraCondition = " AND rc.assined_club_id={$this->club->get('id')}";
            } else {
                $extraCondition =  " AND rc.assined_club_id={$this->club->get('id')}";
            }

            switch ($columnData['type']) {
                case 'WA':
                    $categoryId = $this->club->get('club_workgroup_id');
                    $query = " AND crf.category_id= " . $categoryId;
                    break;
                case 'TA':
                    $categoryId = $this->club->get('club_team_id');
                    if ($columnData['sub_ids'] != null) {
                        $query = " AND crf.category_id={$categoryId} AND crf.function_id IN ({$columnData['sub_ids']})  ";
                    } else {
                        $query = " AND crf.category_id={$categoryId}  ";
                    }
                    break;
                case 'RCA':
                case 'FRCA':
                case 'SFRCA' :
                    $categoryId = $columnData['id'];
                    $query = " AND crf.category_id={$categoryId}";
                    break;
                case 'FRA':
                    $query1 = " AND rr.filter_id IS NOT NULL  ";
                    break;
            }

            $terminologyService = $this->container->get('fairgate_terminology_service');
            //For getting the terminology of excecutive board from the terminology service
            $executiveBoardTitle = $terminologyService->getTerminology('Executive Board', $this->container->getParameter('singular'));

            //to find the master field according to the type of role()fed role/subfed role/clubrole)
            $assignedContact = $this->getMastercontactField($columnData['club_id']);

            $assignmentQry = "(SELECT GROUP_CONCAT(DISTINCT((IF(rr.is_executive_board =1, '{$executiveBoardTitle}',IF(rri18n.title_lang!='' AND rri18n.title_lang IS NOT NULL ,rri18n.title_lang,rr.title)))) ORDER BY tc.sort_order ASC, rr.sort_order ASC SEPARATOR ', ' )   "
                . "FROM fg_rm_role_contact AS rc "
                . "INNER JOIN fg_rm_category_role_function AS crf ON rc.fg_rm_crf_id = crf.id {$query} "
                . "INNER JOIN fg_rm_role AS rr  ON crf.role_id= rr.id AND rr.is_active = 1 {$query1} "
                . "LEFT JOIN fg_team_category AS tc ON tc.id= rr.team_category_id "
                . "LEFT JOIN fg_rm_role_i18n AS rri18n ON rri18n.id=rr.id AND rri18n.lang='{$this->contactLang}' "
                . "WHERE rc.contact_id = fg_cm_contact.{$assignedContact}  {$extraCondition} "
                . "GROUP BY rc.contact_id )";
               
            $sortOrderQry = "(SELECT CONCAT(IF(tc.sort_order,tc.sort_order,0), '.', rr.sort_order) "
                . "FROM fg_rm_role_contact AS rc "
                . "INNER JOIN fg_rm_category_role_function AS crf ON rc.fg_rm_crf_id = crf.id {$query} "
                . "INNER JOIN fg_rm_role AS rr  ON crf.role_id= rr.id AND rr.is_active = 1 {$query1} "
                . "LEFT JOIN fg_team_category AS tc ON tc.id= rr.team_category_id "
                . "LEFT JOIN fg_rm_role_i18n AS rri18n ON rri18n.id=rr.id AND rri18n.lang='{$this->contactLang}' "
                . "WHERE rc.contact_id = fg_cm_contact.{$assignedContact}  {$extraCondition} "
                . "ORDER BY tc.sort_order,rr.sort_order ASC LIMIT 0,1)";
            array_push($this->columnArray, $assignmentQry . " AS `{$columnData['name']}`");
            array_push($this->columnArray, $sortOrderQry . " AS `{$columnData['name']}_sortorder`");
            array_push($this->searchableColumns, $assignmentQry);
        }
    }

    /**
     * functions fields selection
     *
     * @param array $columnData
     */
    private function functionFields($columnData)
    {
        $query = '';
        $extraCondition = '';
        list($separateColumn,$columnId) = explode('_', $this->separateListColumn);

        // Separate listing dependency columns handle
        if (in_array($columnData['type'], $this->dependColumns) && $columnData['id']==$this->separateListFun) {
            switch ($columnData['type']) {
                case 'TF':
                case 'WF':
                    $functionQry = ($columnData['id'] !='') ? "IF( mrr.id={$columnData['id']},GROUP_CONCAT(COALESCE(NULLIF(mrfi18n.title_lang,''), mrf.title) ORDER BY mrf.sort_order ASC SEPARATOR ', '), '')" : "GROUP_CONCAT(COALESCE(NULLIF(mrfi18n.title_lang,''), mrf.title) SEPARATOR ', ')";
                    array_push($this->columnArray, $functionQry . " AS `{$columnData['name']}`");
                    array_push($this->searchableColumns, "COALESCE(NULLIF(mrfi18n.title_lang,''), mrf.title)");
                    $sortQuery = " CONCAT(IF(mrf.is_federation = 1, 0, 1),mrf.sort_order) ";
                    array_push($this->columnArray, $sortQuery . " AS `{$columnData['name']}_sortorder`");
                    break;  
                    
                case 'CRF':
                case 'CSFRF':
                case 'CFRF' :
                case 'IRF'  :
                case 'ISFRF':
                case 'IFRF':    
                    $functionQry = "GROUP_CONCAT(COALESCE(NULLIF(mrfi18n.title_lang,''), mrf.title) SEPARATOR ', ')";
                    array_push($this->columnArray, $functionQry . " AS `{$columnData['name']}`");
                    array_push($this->searchableColumns, "COALESCE(NULLIF(mrfi18n.title_lang,''), mrf.title)");
                    break;
            }
        } else if ($this->separateListing && $columnData['type'] == $separateColumn && $columnId ==$columnData['name']) { //Sepearte listing column handle area 
            switch ($columnData['type']) {
                case 'TF':
                case 'WF':
                case 'CRF':
                case 'CSFRF':
                case 'CFRF' :
                case 'IRF'  :
                case 'ISFRF':
                case 'IFRF':
                    $functionQry = "COALESCE(NULLIF(mrfi18n.title_lang,''), mrf.title)";
                    array_push($this->columnArray, $functionQry . " AS `{$columnData['name']}`");
                    array_push($this->searchableColumns, $functionQry);
                    $sortQuery = " CONCAT(IF(mrf.is_federation = 1, 0, 1),mrf.sort_order) ";
                    array_push($this->columnArray, $sortQuery . " AS `{$columnData['name']}_sortorder`");
                    break;
            }
        } else {  // Normal column handle area

            if (isset($columnData['is_fed_cat']) && $columnData['is_fed_cat'] == 0) {
                $extraCondition = " rc.assined_club_id={$this->club->get('id')}";
            } else {
                $extraCondition = " 1";
            }

            switch ($columnData['type']) {
                case 'WF':
                    $categoryId = $this->club->get('club_workgroup_id');
                    $query = ' AND crf.category_id = ' . $categoryId . ' AND crf.role_id IN (' . $columnData['id'] . ')';
                    break;
                case 'TF':
                    $categoryId = $this->club->get('club_team_id');
                    $query = ' AND crf.category_id = ' . $categoryId;
                    break;
                case 'CRF':
                case 'CSFRF':
                case 'CFRF' :
                    $query = ' AND crf.category_id = ' . $columnData['id'];
                    break;
                case 'IFRF':
                case 'ISFRF':
                case 'IRF' :
                    $query = ' AND crf.role_id = ' . $columnData['id'];
                    break;
            }

            //to find the master field according to the type of role()fed role/subfed role/clubrole)
            $assignedContact = $this->getMastercontactField($columnData['club_id']);

            $functionQry = "(SELECT GROUP_CONCAT(Drolefun  ORDER BY isExecutiveBoard DESC, sort_order ASC, roleFunctionId ASC SEPARATOR ', ') FROM "
                . "(SELECT rf.sort_order, rf.is_federation as isExecutiveBoard, rf.id as roleFunctionId, rc.contact_id AS rcid, GROUP_CONCAT(DISTINCT IF(rfi18n.title_lang='' OR rfi18n.title_lang IS NULL,rf.title,rfi18n.title_lang) ORDER BY rf.sort_order ASC, rf.id ASC SEPARATOR ', ') AS Drolefun "
                . "FROM fg_rm_role_contact AS rc INNER JOIN fg_rm_category_role_function AS crf ON rc.fg_rm_crf_id = crf.id  {$query} "
                . "INNER JOIN fg_rm_role AS rr ON crf.role_id= rr.id AND rr.is_active = 1 "
                . "INNER JOIN fg_rm_function AS rf ON rf.id = crf.function_id "
                . "LEFT JOIN fg_rm_function_i18n AS rfi18n ON rf.id=rfi18n.id AND rfi18n.lang='{$this->contactLang}' "
                . "WHERE  {$extraCondition} GROUP BY rc.contact_id, crf.function_id ) AS resultrf "
                . "WHERE rcid = fg_cm_contact.{$assignedContact})";
            
              $sortOrderQry = "(SELECT fnSortOrder FROM "
                . "(SELECT CONCAT(IF(rf.is_federation = 1, 0, 1),rf.sort_order,'.',rr.sort_order,rf.id) AS fnSortOrder, rc.contact_id AS rcid "
                . "FROM fg_rm_role_contact AS rc INNER JOIN fg_rm_category_role_function AS crf ON rc.fg_rm_crf_id = crf.id  {$query} "
                . "INNER JOIN fg_rm_role AS rr ON crf.role_id= rr.id AND rr.is_active = 1 "
                . "INNER JOIN fg_rm_function AS rf ON rf.id = crf.function_id "
                . "LEFT JOIN fg_rm_function_i18n AS rfi18n ON rf.id=rfi18n.id AND rfi18n.lang='{$this->contactLang}' "
                . "WHERE  {$extraCondition} GROUP BY rc.contact_id, crf.function_id ) AS resultrf "
                . "WHERE rcid = fg_cm_contact.{$assignedContact} ORDER BY fnSortOrder ASC LIMIT 0,1)";
             
            array_push($this->columnArray, $functionQry . " AS `{$columnData['name']}`");
            array_push($this->columnArray, $sortOrderQry . " AS `{$columnData['name']}_sortorder`");
            array_push($this->searchableColumns, $functionQry);
        }
    }

    /**
     * to find the master field according to the type of role()fed role/subfed role/clubrole)
     * @param type $clubId
     *
     * @return string
     */
    private function getMastercontactField($clubId)
    {
        if ($clubId === $this->club->get('federation_id')) {
            $assignedContact = 'fed_contact_id';
        } elseif ($clubId === $this->club->get('sub_federation_id')) {
            $assignedContact = 'subfed_contact_id';
        } else {
            $assignedContact = 'id';
        }
        return $assignedContact;
    }

    /**
     * To get the profile picture details
     */
    private function getProfilePic($columnData)
    {
        $profilePicQry = " (CASE WHEN (fg_cm_contact.is_company = 1) THEN `" . $this->container->getParameter('system_field_companylogo') . "` ELSE `" . $this->container->getParameter('system_field_communitypicture') . "` END)";
        array_push($this->searchableColumns, $profilePicQry);
        array_push($this->columnArray, ' IF (fg_cm_contact.is_company = 1, `' . $this->container->getParameter('system_field_companylogo') . '`, `' . $this->container->getParameter('system_field_communitypicture') . "`)  AS `{$columnData['name']}`");
        array_push($this->columnArray, ' IF (fg_cm_contact.is_company = 1, `' . $this->container->getParameter('system_field_companylogo') . '`, `' . $this->container->getParameter('system_field_communitypicture') . "`)  AS Gprofile_company_pic");
        if ($columnData['linkUrl'] == '' || $columnData['linkUrl'] == 'null') {
            $linkUrl = "'-'";
        } else if ($this->container->getParameter('system_field_website') == $columnData['linkUrl']) {
            $linkUrl = '(SELECT ms.`' . $columnData['linkUrl'] . '` FROM fg_cm_attribute a WHERE a.id =' . $columnData['linkUrl'] . ')';
            array_push($this->columnArray, " {$linkUrl} AS `{$columnData['name']}_linkUrl`");
        } else {
            $linkUrl = '(SELECT mc.`' . $columnData['linkUrl'] . '` FROM fg_cm_attribute a WHERE a.id =' . $columnData['linkUrl'] . ')';
            array_push($this->columnArray, " {$linkUrl} AS `{$columnData['name']}_linkUrl`");
        }
    }

    /**
     * To get the searchable columns
     */
    public function getSearchableColumns()
    {
        return $this->searchableColumns;
    }
}
