<?php

namespace Internal\TeamBundle\Util;

use Common\UtilityBundle\Util\FgSettings;

/**
 * For get actual column names from the given  data.
 */
class Tablesettings
{
    private $columnDatas;
    private $mysqlDateFormat;
    private $mysqlDateTimeFormat;
    private $club;
    private $container;
    private $conn;
    private $columnArray = array();
    private $key;
    private $changeflag;
    private $transYes;
    private $transNo;
    private $adminFlag = 'false';
    private $memberId = 0;
    private $membertype = 'team';
    private $assignedContactId = '';

    /**
     * @param object $container Container Object
     * @param array  $columns   all columns
     * @param object $club      service
     */
    public function __construct($container, $columns, $adminFlag, $memberId = 0, $membertype)
    {
        $this->container = $container;
        $this->columnDatas = $columns;
        $this->mysqlDateFormat = FgSettings::getMysqlDateFormat();
        $this->mysqlDateTimeFormat = FgSettings::getMysqlDateTimeFormat();
        $this->club = $this->container->get('club');
        $this->conn = $this->container->get('database_connection');
        $this->fiscalYearDetails = $this->club->getFiscalYear();
        $this->adminFlag = $adminFlag;
        $this->memberId = $memberId;
        $this->contactFields = $this->club->get('allContactFields');
        $this->membertype = $membertype;
    }

    /**
     * For get  all selected actual column.
     *
     * @return columns
     */
    public function getColumns()
    {
        $this->transYes = $this->container->get('translator')->trans('YES');
        $this->transNo = $this->container->get('translator')->trans('NO');
        foreach ($this->columnDatas as $key => $columndata) {
            switch ($columndata['type']) {
                case 'G':
                    $this->genericField($columndata);
                    break;
                case 'R': case 'Fn': case 'RF':
                    $this->roleFunctionField($columndata);
                    break;
                case 'CF':
                    $this->contactField($columndata);
                    break;
            }
        }

        return $this->columnArray;
    }

    /**
     * For find the actual general column.
     *
     * @param array $columndata generic column
     * @param int   $key        index
     */
    public function genericField($columndata)
    {
        switch ($columndata['id']) {
            case 'age':
                if ($this->adminFlag) {
                    $query = "IF((SELECT confirm_status FROM fg_cm_change_toconfirm AS CT WHERE CT.club_id={$this->club->get('id')} AND CT.contact_id=fg_cm_contact.id AND CT.attribute_id={$this->container->getParameter('system_field_dob')} LIMIT 1) ='NONE',(SELECT IF(EXTRACT(YEAR FROM CN.value) = 0, '', DATE_FORMAT(FROM_DAYS(DATEDIFF(CURRENT_DATE,CN.value )),'%y')) FROM fg_cm_change_toconfirm AS CN WHERE CN.club_id={$this->club->get('id')} AND CN.contact_id=fg_cm_contact.id AND CN.attribute_id={$this->container->getParameter('system_field_dob')} LIMIT 1) , (IF(EXTRACT(YEAR FROM `".$this->container->getParameter('system_field_dob')."`) = 0, '', DATE_FORMAT(FROM_DAYS(DATEDIFF(CURRENT_DATE,`".$this->container->getParameter('system_field_dob')."` )),'%y')) )) AS {$columndata['name']}_CHANGED";
                    array_push($this->columnArray, $query);
                }
                array_push($this->columnArray, 'IF(EXTRACT(YEAR FROM `'.$this->container->getParameter('system_field_dob')."`) = 0, '', DATE_FORMAT(FROM_DAYS(DATEDIFF(CURRENT_DATE,`".$this->container->getParameter('system_field_dob')."` )),'%y')) AS {$columndata['name']}");
                array_push($this->columnArray, "(SELECT confirm_status FROM fg_cm_change_toconfirm AS CT WHERE CT.club_id={$this->club->get('id')} AND CT.contact_id=fg_cm_contact.id AND CT.attribute_id=".$this->container->getParameter('system_field_dob')." LIMIT 1) AS {$columndata['name']}_Flag");
                if ($this->adminFlag && $this->contactFields[$this->container->getParameter('system_field_dob')]['is_visible_teamadmin'] == 1) {
                    array_push($this->columnArray, "'team'  AS {$columndata['name']}_visibility");
                } elseif ($this->contactFields[$this->container->getParameter('system_field_dob')]['is_set_privacy_itself'] == 0) {
                    array_push($this->columnArray, "'{$this->contactFields[$this->container->getParameter('system_field_dob')]['privacy_contact']}'  AS {$columndata['name']}_visibility");
                } else {
                    array_push($this->columnArray, "(SELECT IF(PS.privacy IS NULL,'{$this->contactFields['privacy_contact']}',PS.privacy)  FROM fg_cm_contact_privacy AS PS WHERE PS.contact_id=fg_cm_contact.id AND PS.attribute_id=".$this->container->getParameter('system_field_dob').")  AS {$columndata['name']}_visibility");
                }
                break;
            case 'birth_year':
                if ($this->adminFlag) {
                    $query = "IF((SELECT DCC.confirm_status FROM fg_cm_change_toconfirm DCC WHERE DCC.club_id={$this->club->get('id')} AND DCC.contact_id=fg_cm_contact.id AND DCC.attribute_id={$this->container->getParameter('system_field_dob')} LIMIT 1) ='NONE',(SELECT IF(EXTRACT(YEAR FROM CN.value) = 0, '', EXTRACT(YEAR FROM CN.value)) FROM fg_cm_change_toconfirm AS CN WHERE CN.club_id={$this->club->get('id')} AND CN.contact_id=fg_cm_contact.id AND CN.attribute_id={$this->container->getParameter('system_field_dob')} LIMIT 1) , (IF(EXTRACT(YEAR FROM `".$this->container->getParameter('system_field_dob')."`) = 0, '', EXTRACT(YEAR FROM `".$this->container->getParameter('system_field_dob')."`)) )) AS {$columndata['name']}_CHANGED";
                    array_push($this->columnArray, $query);
                }
                array_push($this->columnArray, 'IF(EXTRACT(YEAR FROM `'.$this->container->getParameter('system_field_dob')."`) = 0, '', EXTRACT(YEAR FROM `".$this->container->getParameter('system_field_dob')."`)) AS {$columndata['name']}");
                array_push($this->columnArray, "(SELECT confirm_status AS CT FROM fg_cm_change_toconfirm AS CT WHERE CT.club_id={$this->club->get('id')} AND CT.contact_id=fg_cm_contact.id AND CT.attribute_id=".$this->container->getParameter('system_field_dob')." LIMIT 1)  AS {$columndata['name']}_Flag");

                if ($this->adminFlag && $this->contactFields[$this->container->getParameter('system_field_dob')]['is_visible_teamadmin'] == 1) {
                    array_push($this->columnArray, "'team'  AS {$columndata['name']}_visibility");
                } elseif ($this->contactFields[$this->container->getParameter('system_field_dob')]['is_set_privacy_itself'] == 0) {
                    array_push($this->columnArray, "'{$this->contactFields[$this->container->getParameter('system_field_dob')]['privacy_contact']}'  AS {$columndata['name']}_visibility");
                } else {
                    array_push($this->columnArray, "(SELECT IF(PS.privacy IS NULL,'{$this->contactFields['privacy_contact']}',PS.privacy)  FROM fg_cm_contact_privacy AS PS WHERE PS.contact_id=fg_cm_contact.id AND PS.attribute_id=".$this->container->getParameter('system_field_dob').")  AS {$columndata['name']}_visibility");
                }

                break;
            case 'salutation_text':
                array_push($this->columnArray, "salutationText(fg_cm_contact.id, {$this->club->get('id')}, '{$this->club->get('default_system_lang')}', NULL) AS {$columndata['name']}");
                array_push($this->columnArray, "'team' AS {$columndata['name']}_visibility");
                break;
            case 'fedmembership_category':
                array_push($this->columnArray, "(Select IF(fg_cm_membership_i18n.title_lang !='' AND fg_cm_membership_i18n.title_lang IS NOT NULL, fg_cm_membership_i18n.title_lang, fg_cm_membership.title) FROM fg_cm_membership LEFT JOIN fg_cm_membership_i18n  ON fg_cm_membership.id=fg_cm_membership_i18n.id AND fg_cm_membership_i18n.lang='{$this->club->get('default_lang')}'  WHERE fg_cm_membership.id=fg_cm_contact.fed_membership_cat_id  ) AS {$columndata['name']}");
                array_push($this->columnArray, " fg_cm_contact.is_fed_membership_confirmed AS fedmembershipApprove");
                if ($this->adminFlag) {
                    array_push($this->columnArray, "'team'  AS {$columndata['name']}_visibility");
                } else {
                    array_push($this->columnArray, "'private'  AS {$columndata['name']}_visibility");
                }
                break;
            case 'membership_category':
                array_push($this->columnArray, "(Select IF(fg_cm_membership_i18n.title_lang !='' AND fg_cm_membership_i18n.title_lang IS NOT NULL, fg_cm_membership_i18n.title_lang, fg_cm_membership.title) FROM fg_cm_membership LEFT JOIN fg_cm_membership_i18n  ON fg_cm_membership.id=fg_cm_membership_i18n.id AND fg_cm_membership_i18n.lang='{$this->club->get('default_lang')}'  WHERE fg_cm_membership.id=fg_cm_contact.club_membership_cat_id  ) AS {$columndata['name']}");
                 if ($this->adminFlag) {
                    array_push($this->columnArray, "'team'  AS {$columndata['name']}_visibility");
                } else {
                    array_push($this->columnArray, "'private'  AS {$columndata['name']}_visibility");
                }
                break;
            case 'intranet_access':
                array_push($this->columnArray, "fg_cm_contact.intranet_access AS {$columndata['name']}");
                array_push($this->columnArray, "'team' AS {$columndata['name']}_visibility");
                break;
        }
    }

    /**
     * For find the actual contact fields column.
     *
     * @param array $columndata generic column
     */
    public function contactField($columndata)
    {
        switch ($columndata['id']) {
            default:

                //if the loggin user is admin get changed value otherwise normal value from the normal table
                if ($this->adminFlag && $this->contactFields[$columndata['id']]['is_changable_teamadmin'] == 1 && $this->contactFields[$columndata['id']]['is_confirm_teamadmin'] == 1) {
                    $query = "IF((SELECT CS.confirm_status FROM fg_cm_change_toconfirm CS WHERE CS.club_id={$this->club->get('id')} AND CS.contact_id=fg_cm_contact.id AND CS.attribute_id={$columndata['id']} AND CS.confirm_status='NONE' LIMIT 1) ='NONE',(SELECT value FROM fg_cm_change_toconfirm CTC WHERE CTC.contact_id=fg_cm_contact.id AND CTC.club_id={$this->club->get('id')} AND CTC.attribute_id={$columndata['id']} ORDER BY CTC.id DESC LIMIT 1) , `".$columndata['id']."`) AS {$columndata['name']}_CHANGED";
                    array_push($this->columnArray, $query);
                }

                if ($this->adminFlag && $this->contactFields[$columndata['id']]['is_visible_teamadmin'] == 1) {
                    array_push($this->columnArray, "'team'  AS {$columndata['name']}_visibility");
                } elseif ($this->contactFields[$columndata['id']]['is_set_privacy_itself'] == 0) {
                    array_push($this->columnArray, "'{$this->contactFields[$columndata['id']]['privacy_contact']}'  AS {$columndata['name']}_visibility");
                } else {
                    array_push($this->columnArray, "(SELECT COALESCE(MIN(privacy), '{$this->contactFields[$columndata['id']]['privacy_contact']}') FROM `fg_cm_contact_privacy` WHERE `contact_id` = fg_cm_contact.id AND attribute_id={$columndata['id']})  AS {$columndata['name']}_visibility");
                }
                array_push($this->columnArray, '`'.$columndata['id']."` AS {$columndata['name']}");

                if ($this->adminFlag && $this->contactFields[$columndata['id']]['is_changable_teamadmin'] == 1 && $this->contactFields[$columndata['id']]['is_confirm_teamadmin'] == 1) {
                    array_push($this->columnArray, "(SELECT confirm_status AS CT FROM fg_cm_change_toconfirm AS CT WHERE CT.club_id={$this->club->get('id')} AND CT.contact_id=fg_cm_contact.id AND CT.attribute_id={$columndata['id']} LIMIT 1) AS {$columndata['name']}_Flag");
                } elseif ($this->adminFlag) {
                    array_push($this->columnArray, "'CONFIRMED' AS {$columndata['name']}_Flag");
                }
        }
    }

    /**
     * to find the actual role column.
     *
     * @param array $columndata generic column
     */
    public function rolefunctionField($columndata)
    {
        $categoryId = $columndata['id'];

        $membersidArray = ($this->membertype == 'team') ? array_keys($this->container->get('contact')->get('teams')) : array_keys($this->container->get('contact')->get('workgroups'));

        $terminologyService = $this->container->get('fairgate_terminology_service');
        //For getting the terminology of excecutive board from the terminology service
        $executiveBoardTitle = $terminologyService->getTerminology('Executive Board', $this->container->getParameter('singular'));
        //Get all memberships
        $extraCondition = '';

        //to find the master field according to the type of role()fed role/subfed role/clubrole)
        if ($columndata['club_id'] === $this->club->get('federation_id')) {
            $assignedContact = 'fed_contact_id';
        } elseif ($columndata['club_id'] === $this->club->get('sub_federation_id')) {
            $assignedContact = 'subfed_contact_id';
        } else {
            $assignedContact = 'id';
        }

        if ($columndata['sub_ids'] == 'all') {
            $columndata['sub_ids'] = implode(',', $membersidArray);
        }

        if (isset($columndata['is_fed_cat']) && $columndata['is_fed_cat'] == 0) {
            switch ($columndata['type']) {
                case 'R':
                    $extraCondition = " AND  rc.assined_club_id={$this->club->get('id')}";
                    break;
                case 'RF':
                    $extraCondition = " rc.assined_club_id={$this->club->get('id')}";
                    break;
            }
        } else {
            switch ($columndata['type']) {
                case 'R':
                    $extraCondition = ' AND  1 ';
                    break;
                case 'RF':
                    $extraCondition = ' 1 ';
                    break;
                case 'Fn':
                    $extraCondition = ' 1 ';
                    break;
            }
        }
        switch ($columndata['type']) {
            case 'R':
                $query = '';
                if ($columndata['sub_ids'] != 'all') {
                    $query = " AND crf.role_id IN ({$columndata['sub_ids']}) ";
                }
                array_push($this->columnArray, "(SELECT GROUP_CONCAT(DISTINCT((IF(rr.is_executive_board =1, '{$executiveBoardTitle}',IF(rri18n.title_lang='' OR rri18n.title_lang IS NULL,rr.title,rri18n.title_lang)))) SEPARATOR ', ' )   FROM fg_rm_role_contact AS rc INNER JOIN fg_rm_category_role_function AS crf ON rc.fg_rm_crf_id = crf.id AND crf.category_id= {$categoryId} {$query}
                                                    INNER JOIN fg_rm_role AS rr  ON crf.role_id= rr.id LEFT JOIN fg_rm_role_i18n AS rri18n ON rri18n.id=rr.id AND rri18n.lang='{$this->club->get('default_lang')}' WHERE rc.contact_id = fg_cm_contact.{$assignedContact}  {$extraCondition} GROUP BY rc.contact_id) AS {$columndata['name']} ");
                break;
            case 'RF': 
                $query = '';
                if ($columndata['sub_ids'] != 'all') {
                    $query = " AND crf.role_id IN ({$columndata['sub_ids']}) ";
                }
                array_push($this->columnArray, "(SELECT GROUP_CONCAT(Drolefun SEPARATOR ', ') FROM
                                                (SELECT rc.contact_id AS rcid, CONCAT( (IF(rr.is_executive_board =1, '{$executiveBoardTitle}',IF(rri18n.title_lang='' OR rri18n.title_lang IS NULL,rr.title,rri18n.title_lang))), ' (', GROUP_CONCAT(IF(rfi18n.title_lang='' OR rfi18n.title_lang IS NULL,rf.title,rfi18n.title_lang) SEPARATOR ', '), ')') AS Drolefun
                                                FROM fg_rm_role_contact AS rc
                                                INNER JOIN fg_rm_category_role_function AS crf ON rc.fg_rm_crf_id = crf.id AND crf.category_id= {$categoryId} {$query}
                                                INNER JOIN fg_rm_role AS rr ON crf.role_id= rr.id
                                                LEFT JOIN fg_rm_role_i18n AS rri18n ON rri18n.id=rr.id AND rri18n.lang='{$this->club->get('default_lang')}'
                                                INNER JOIN fg_rm_function AS rf ON rf.id = crf.function_id
                                                LEFT JOIN fg_rm_function_i18n AS rfi18n ON rf.id=rfi18n.id AND rfi18n.lang='{$this->club->get('default_lang')}'
                                                WHERE  {$extraCondition} GROUP BY rc.contact_id, crf.role_id) AS resultrf WHERE rcid=fg_cm_contact.{$assignedContact})  AS {$columndata['name']}");
                break;
            case 'Fn':
                $query = " AND crf.role_id IN ({$columndata['sub_ids']}) ";
                if ($columndata['fnType'] == 'team') {
                    $categoryId = $this->club->get('club_team_id');
                } elseif ($columndata['fnType'] == 'workgroup') {
                    $categoryId = $this->club->get('club_workgroup_id');
                }

                array_push($this->columnArray, "(SELECT count(CT.id) AS CT FROM fg_cm_change_toconfirm AS CT INNER JOIN  fg_cm_change_toconfirm_functions CTF ON CTF.toconfirm_id=CT.id AND CT.club_id={$this->club->get('id')} WHERE CT.role_id={$this->memberId} AND CT.contact_id=fg_cm_contact.id AND CT.confirm_status='NONE') AS {$columndata['name']}_Flag");

                array_push($this->columnArray, "(SELECT GROUP_CONCAT(Drolefun SEPARATOR ', ') FROM
                                                (SELECT DCT.contact_id AS rcid,  GROUP_CONCAT(CONCAT(IF(rfi18n.title_lang='' OR rfi18n.title_lang IS NULL,rf.title,rfi18n.title_lang),'#',rf.id) ORDER BY rf.sort_order ASC SEPARATOR ',')  AS Drolefun
                                                FROM fg_cm_change_toconfirm AS DCT
                                                INNER JOIN fg_cm_change_toconfirm_functions CTF ON  CTF.toconfirm_id=DCT.id AND DCT.club_id={$this->club->get('id')} AND DCT.role_id={$this->memberId} AND DCT.confirm_status='NONE' AND CTF.action_type='ADDED'
                                                INNER JOIN fg_rm_function AS rf ON rf.id = CTF.function_id
                                                LEFT JOIN fg_rm_function_i18n AS rfi18n ON rf.id=rfi18n.id AND rfi18n.lang='{$this->club->get('default_lang')}'
                                                WHERE  {$extraCondition} GROUP BY DCT.contact_id, DCT.role_id ) AS resultrf WHERE rcid=fg_cm_contact.id)  AS {$columndata['name']}_ADDED");
                array_push($this->columnArray, "(SELECT GROUP_CONCAT(Drolefun SEPARATOR ', ') FROM
                                                (SELECT DCT.contact_id AS rcid,  GROUP_CONCAT(CONCAT(IF(rfi18n.title_lang='' OR rfi18n.title_lang IS NULL,rf.title,rfi18n.title_lang),'#',rf.id) ORDER BY rf.sort_order ASC SEPARATOR ',')  AS Drolefun
                                                FROM fg_cm_change_toconfirm AS DCT
                                                INNER JOIN fg_cm_change_toconfirm_functions CTF ON  CTF.toconfirm_id=DCT.id AND DCT.club_id={$this->club->get('id')} AND DCT.role_id={$this->memberId} AND DCT.confirm_status='NONE' AND CTF.action_type='REMOVED'
                                                INNER JOIN fg_rm_function AS rf ON rf.id = CTF.function_id
                                                LEFT JOIN fg_rm_function_i18n AS rfi18n ON rf.id=rfi18n.id AND rfi18n.lang='{$this->club->get('default_lang')}'
                                                WHERE  {$extraCondition} GROUP BY DCT.contact_id, DCT.role_id ) AS resultrf WHERE rcid=fg_cm_contact.id)  AS {$columndata['name']}_REMOVED");

                array_push($this->columnArray, "(SELECT GROUP_CONCAT(Drolefun SEPARATOR ', ') FROM
                                                (SELECT rc.contact_id AS rcid, GROUP_CONCAT(CONCAT(IF(rfi18n.title_lang='' OR rfi18n.title_lang IS NULL,rf.title,rfi18n.title_lang),'#',rf.id) ORDER BY rf.sort_order ASC SEPARATOR ',') AS Drolefun
                                                FROM fg_rm_role_contact AS rc
                                                INNER JOIN fg_rm_category_role_function AS crf ON rc.fg_rm_crf_id = crf.id AND crf.category_id= {$categoryId} {$query}{$where}
                                                INNER JOIN fg_rm_role AS rr ON crf.role_id= rr.id AND rr.is_active =1
                                                LEFT JOIN fg_rm_role_i18n AS rri18n ON rri18n.id=rr.id AND rri18n.lang='{$this->club->get('default_lang')}'
                                                INNER JOIN fg_rm_function AS rf ON rf.id = crf.function_id 
                                                LEFT JOIN fg_rm_function_i18n AS rfi18n ON rf.id=rfi18n.id AND rfi18n.lang='{$this->club->get('default_lang')}'
                                                WHERE  {$extraCondition} GROUP BY rc.contact_id, crf.role_id ) AS resultrf WHERE rcid=fg_cm_contact.id)  AS {$columndata['name']}");
                break;
            default:
                array_push($this->columnArray, $columndata['id']);
        }
    }
}
