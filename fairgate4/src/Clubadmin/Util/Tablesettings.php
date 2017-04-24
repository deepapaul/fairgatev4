<?php

namespace Clubadmin\Util;

use Common\UtilityBundle\Util\FgSettings;

/**
 * For get actual column names from the given  data.
 */
class Tablesettings {

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
    private $fiscalYearDetails;
    public $mastercontactField;

    /**
     * @param object $container Container Object
     * @param array  $columns   all columns
     * @param object $club      service
     */
    public function __construct($container, $columns, $club) {
        $this->columnDatas = $columns;
        $this->mysqlDateFormat = FgSettings::getMysqlDateFormat();
        $this->mysqlDateTimeFormat = FgSettings::getMysqlDateTimeFormat();
        $this->club = $club;
        $this->container = $container;
        $this->conn = $this->container->get('database_connection');
        $this->fiscalYearDetails = $this->club->getFiscalYear();
        $this->mastercontactField = ($this->club->get('type') == 'federation') ? 'fed_contact_id' : 'contact_id';
    }

    /**
     * For get  all selected actual column.
     *
     * @return columns
     */
    public function getColumns() {
        $this->transYes = $this->container->get('translator')->trans('YES');
        $this->transNo = $this->container->get('translator')->trans('NO');
        foreach ($this->columnDatas as $key => $columndata) {
            switch ($columndata['type']) {
                case 'G'://General fields
                    $this->genericField($columndata);
                    break;
                case 'R': case 'Fn': //Role/Function
                    $this->roleFunctionField($columndata);
                    break;
                case 'CF'://contact field
                    $this->contactField($columndata);
                    break;
                case 'CN'://house hold connection
                    $this->householdField($columndata);
                    break;
                case 'RF'://Role with function
                    $this->rolefunctionField($columndata);
                    break;
                case 'FI'://federation info fields
                    $this->federationInfoField($columndata);
                    break;
                case 'SS': //sponsor service fields
                    $this->sponsorserviceField($columndata);
                    break;
                case 'SA': //sponsor analysis fields
                    $this->sponsoranalysisField($columndata);
                    break;
                case 'CO': //contact option  fields
                    $this->contactoptionField($columndata);
                    break;
                case 'CM': //clubmembership field
                    $this->clubMembership($columndata);
                    break;
                case 'FM': //federation membership
                    $this->fedMembership($columndata);
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
    private function genericField($columndata) {
        switch ($columndata['id']) {
            case 'last_updated':case 'created_at':
                array_push($this->columnArray, "fg_cm_contact.{$columndata['id']} AS {$columndata['name']}");
                break;
            case 'last_login':
                array_push($this->columnArray, "fg_cm_contact.{$columndata['id']} AS {$columndata['name']}");
                break;
            case 'age':
                array_push($this->columnArray, 'IF(EXTRACT(YEAR FROM `' . $this->container->getParameter('system_field_dob') . "`) = 0, '', DATE_FORMAT(FROM_DAYS(DATEDIFF(CURRENT_DATE,`" . $this->container->getParameter('system_field_dob') . "` )),'%y')) AS {$columndata['name']}");
                break;
            case 'birth_year':
                array_push($this->columnArray, 'IF(EXTRACT(YEAR FROM `' . $this->container->getParameter('system_field_dob') . "`) = 0, '', EXTRACT(YEAR FROM `" . $this->container->getParameter('system_field_dob') . "`))AS {$columndata['name']}");
                break;
            case 'salutation_text':
                array_push($this->columnArray, "salutationText(mc.{$this->mastercontactField}, {$this->club->get('id')}, '{$this->club->get('default_system_lang')}', NULL) AS {$columndata['name']}");
                break;
            case 'contact_id':
                array_push($this->columnArray, "fg_cm_contact.member_id AS {$columndata['name']}");
                break;
            case 'last_invoice_sending':
                array_push($this->columnArray, "'-' AS {$columndata['name']}");
                break;
            case 'profile_company_pic':
                array_push($this->columnArray, ' IF (fg_cm_contact.is_company = 1, `' . $this->container->getParameter('system_field_companylogo') . '`, `' . $this->container->getParameter('system_field_communitypicture') . "`)  AS {$columndata['name']}");
                break;
            case 'membership_category':
                array_push($this->columnArray, "(Select IF(fg_cm_membership_i18n.title_lang !='' AND fg_cm_membership_i18n.title_lang IS NOT NULL, fg_cm_membership_i18n.title_lang, fg_cm_membership.title) FROM fg_cm_membership LEFT JOIN fg_cm_membership_i18n  ON fg_cm_membership.id=fg_cm_membership_i18n.id AND fg_cm_membership_i18n.lang='{$this->club->get('default_lang')}'  WHERE fg_cm_membership.id=fg_cm_contact.club_membership_cat_id  ) AS {$columndata['name']}");
                break;
            case 'nl_subscriber':
                array_push($this->columnArray, "IF(fg_cm_contact.is_subscriber =1, '$this->transYes', '$this->transNo')  AS {$columndata['name']}");
                break;
            case 'intranet_access':
                array_push($this->columnArray, "IF(fg_cm_contact.intranet_access = 1, '$this->transYes', '$this->transNo') AS {$columndata['name']}");
                break;
            case 'dispatch_type_invoice':
                array_push($this->columnArray, "'-' AS {$columndata['name']}");
                break;
            case 'dispatch_type_dun':
                array_push($this->columnArray, "'-' AS {$columndata['name']}");
                break;
            case 'notes':
                array_push($this->columnArray, "(Select count(id) FROM  fg_cm_notes WHERE fg_cm_notes.contact_id=fg_cm_contact.id AND fg_cm_notes.club_id={$this->club->get('id')}) AS {$columndata['name']}");
                break;
            case 'documents':
                array_push($this->columnArray, "(Select count(fg_dm_assigment.id) FROM  fg_dm_documents INNER JOIN fg_dm_assigment ON fg_dm_documents.id=fg_dm_assigment.document_id  WHERE  fg_dm_documents.document_type='CONTACT'  AND fg_dm_documents.club_id={$this->club->get('id')} AND fg_dm_assigment.contact_id=fg_cm_contact.id GROUP BY fg_dm_assigment.club_id ) AS {$columndata['name']}");
                break;
            case 'no_of_logins':
                array_push($this->columnArray, "fg_cm_contact.login_count AS {$columndata['name']}");
                $this->columnArray[$this->key] = "fg_cm_contact.login_count AS {$columndata['name']}";
                break;

            case 'community_status':
                array_push($this->columnArray, "mc.intranet_access AS {$columndata['name']}");
                break;
            case 'join_leave_dates':
                $this->changeflag = 1;
                array_push($this->columnArray, 'fg_cm_contact.joining_date   AS joining_date');
                array_push($this->columnArray, 'fg_cm_contact.leaving_date  AS leaving_date');
                break;
            case 'sponsor':

                /*  if condition :sponsor=1
                 *  true   if condition:there is entry in sm_bookings table
                 *              true: prospect
                 *              false: (active,future,former sponsor)
                 *  false  not sponsor
                 */
                array_push($this->columnArray, 'IF (fg_cm_contact.is_sponsor = 1,'
                        . ' IF((select count(fg_sm_bookings.contact_id) from fg_sm_bookings where fg_sm_bookings.contact_id=fg_cm_contact.id AND fg_sm_bookings.is_deleted = 0 )= 0,'
                        . "'{$this->container->get('translator')->trans('PROSPECT')}',"
                        . '(Select CASE'
                        . " WHEN (fg_sm_bookings.begin_date > now() AND fg_sm_bookings.is_deleted = 0 AND fg_sm_bookings.club_id={$this->club->get('id')}) THEN '{$this->container->get('translator')->trans('FUTURE_SPONSOR')}'"
                        . " WHEN (fg_sm_bookings.begin_date <= now() AND fg_sm_bookings.is_deleted = 0 AND (fg_sm_bookings.end_date >= now() OR fg_sm_bookings.end_date IS NULL) AND fg_sm_bookings.club_id={$this->club->get('id')}) THEN '{$this->container->get('translator')->trans('ACTIVE_SPONSOR')}'"
                        . " WHEN (fg_sm_bookings.end_date < now() AND fg_sm_bookings.is_deleted = 0 AND fg_sm_bookings.club_id={$this->club->get('id')}) THEN '{$this->container->get('translator')->trans('FORMER_SPONSOR')}' END AS Gsponsor FROM fg_sm_bookings WHERE fg_sm_bookings.contact_id=fg_cm_contact.id "
                        . " ORDER BY  (CASE WHEN  Gsponsor='{$this->container->get('translator')->trans('ACTIVE_SPONSOR')}' then 1 WHEN Gsponsor='{$this->container->get('translator')->trans('FUTURE_SPONSOR')}' THEN 2 WHEN Gsponsor='{$this->container->get('translator')->trans('FORMER_SPONSOR')}' then 3 ELSE 4 END) asc limit 0,1) ),"
                        . "'' ) AS {$columndata['name']}");

                break;
            case 'is_stealth_mode':
                array_push($this->columnArray, "IF(fg_cm_contact.is_stealth_mode = 1, '$this->transYes', '$this->transNo') AS {$columndata['name']}");
                break;
            case 'fed_membership_category':
                array_push($this->columnArray, "(Select IF(fg_cm_membership_i18n.title_lang !='' AND fg_cm_membership_i18n.title_lang IS NOT NULL, fg_cm_membership_i18n.title_lang, fg_cm_membership.title) FROM fg_cm_membership LEFT JOIN fg_cm_membership_i18n  ON fg_cm_membership.id=fg_cm_membership_i18n.id AND fg_cm_membership_i18n.lang='{$this->club->get('default_lang')}'  WHERE fg_cm_membership.id=fg_cm_contact.fed_membership_cat_id  ) AS {$columndata['name']}");
                break;
            case 'member_years':
                array_push($this->columnArray, "(Select sum(IF(MH.leaving_date IS NOT NULL,ROUND(DATEDIFF(MH.leaving_date,MH.joining_date)/365.25,1),ROUND(DATEDIFF(CURDATE(),MH.joining_date)/365.25,1))) AS fed_membership_years from fg_cm_membership_history MH where MH.membership_type='club' and MH.contact_id=fg_cm_contact.id group by MH.contact_id) AS {$columndata['name']}");
                break;
            case 'fed_member_years':
                array_push($this->columnArray, "(Select sum(IF(MH.leaving_date IS NOT NULL,ROUND(DATEDIFF(MH.leaving_date,MH.joining_date)/365.25,1),ROUND(DATEDIFF(CURDATE(),MH.joining_date)/365.25,1))) AS fed_membership_years from fg_cm_membership_history MH where MH.membership_type='federation' and MH.contact_id=fg_cm_contact.fed_contact_id group by MH.contact_id) AS {$columndata['name']}");
                break;
        }
    }

    /**
     * For find the actual contact fields column.
     *
     * @param array $columndata generic column
     */
    private function contactField($columndata) {
        switch ($columndata['id']) {
            default:
                array_push($this->columnArray, '`' . $columndata['id'] . "` AS {$columndata['name']}");
        }
    }

    /**
     * For find the actual household column.
     *
     * @param type $columndata generic column
     * @param type $key        index
     */
    private function householdField($columndata) {
        switch ($columndata['id']) {
            case 'household_main_contact':
                array_push($this->columnArray, "IF(fg_cm_contact.is_household_head =1, '$this->transYes', '$this->transNo')  AS {$columndata['name']}");
                break;
            case 'household_contact':

                if ($this->club->get('type') == 'federation' || $this->club->get('type') == 'sub_federation') {
                    $clubType = "master_federation_{$this->club->get('id')}";
                } else {
                    $clubType = " master_club_{$this->club->get('id')}";
                }
                array_push($this->columnArray, "(SELECT GROUP_CONCAT(relationName SEPARATOR '; ') FROM (SELECT CASE WHEN C.is_company THEN CONCAT(dms.9,' (',IF(Ri18n.title_lang !='' AND Ri18n.title_lang IS NOT NULL,Ri18n.title_lang,R.name),') ','|',LC.linked_contact_id) ELSE CONCAT(ContactName(LC.linked_contact_id),' (',IF(Ri18n.title_lang!='' AND Ri18n.title_lang IS NOT NULL,Ri18n.title_lang,R.name),') ','|',LC.linked_contact_id) END AS relationName,LC.contact_id AS lcid,LC.linked_contact_id AS lid,LC.relation_id,R.id
                FROM `fg_cm_linkedcontact` LC
                LEFT JOIN fg_cm_relation R ON LC.relation_id = R.id
                LEFT JOIN fg_cm_relation_i18n Ri18n ON R.id=Ri18n.id AND Ri18n.lang='{$this->club->get('default_system_lang')}'
                LEFT JOIN fg_cm_contact C ON LC.linked_contact_id = C.id
                LEFT JOIN master_system dms ON C.fed_contact_id = dms.fed_contact_id LEFT JOIN {$clubType} AS dmc ON dmc.{$this->mastercontactField}=LC.linked_contact_id
                WHERE  LC.club_id={$this->club->get('id')} AND C.is_permanent_delete = 0 AND LC.type='household' GROUP BY lid,R.id,LC.contact_id,LC.relation_id
                ORDER BY C.is_company,relationName ASC ) AS hosehold WHERE lcid=fg_cm_contact.id) AS {$columndata['name']}");
                break;
            case 'household_contact_withoutlink':
                if ($this->club->get('type') == 'federation' || $this->club->get('type') == 'sub_federation') {
                    $clubType = "master_federation_{$this->club->get('id')}";
                } else {
                    $clubType = " master_club_{$this->club->get('id')}";
                }
                array_push($this->columnArray, "(SELECT GROUP_CONCAT(relationName SEPARATOR ';') FROM (SELECT CASE WHEN C.is_company THEN CONCAT(dms.9,'(',IF(Ri18n.title_lang !='' AND Ri18n.title_lang IS NOT NULL,Ri18n.title_lang,R.name),')','|',LC.linked_contact_id) ELSE CONCAT(ContactNameNoSort(LC.linked_contact_id, 0),'(',IF(Ri18n.title_lang!='' AND Ri18n.title_lang IS NOT NULL,Ri18n.title_lang,R.name),')','|',LC.linked_contact_id) END AS relationName,LC.contact_id AS lcid,LC.linked_contact_id AS lid,LC.relation_id,R.id
                FROM `fg_cm_linkedcontact` LC
                LEFT JOIN fg_cm_relation R ON LC.relation_id = R.id
                LEFT JOIN fg_cm_relation_i18n Ri18n ON R.id=Ri18n.id AND Ri18n.lang='{$this->club->get('default_system_lang')}'
                LEFT JOIN fg_cm_contact C ON LC.linked_contact_id = C.id
                LEFT JOIN master_system dms ON C.fed_contact_id = dms.fed_contact_id LEFT JOIN {$clubType} AS dmc ON dmc.{$this->mastercontactField}=LC.linked_contact_id
                WHERE  LC.club_id={$this->club->get('id')} AND C.is_permanent_delete = 0 AND LC.type='household' GROUP BY lid,R.id,LC.contact_id,LC.relation_id
                ORDER BY C.is_company,relationName ASC ) AS hosehold WHERE lcid=fg_cm_contact.id) AS {$columndata['name']}");
                break;
        }
    }

    /**
     * to find the actual role column.
     *
     * @param array $columndata generic column
     */
    private function rolefunctionField($columndata) {
        $categoryId = $columndata['id'];
        $terminologyService = $this->container->get('fairgate_terminology_service');
        //For getting the terminology of excecutive board from the terminology service
        $executiveBoardTitle = $terminologyService->getTerminology('Executive Board', $this->container->getParameter('singular'));
        //Get all memberships
        $extraCondition = '';
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
//to find the master field according to the type of role()fed role/subfed role/clubrole)
        $assignedContact = $this->getMastercontactField($columndata['club_id']);

        switch ($columndata['type']) {
            case 'R':
                $query = '';
                if ($columndata['sub_ids'] != 'all') {
                    $query = " AND crf.role_id IN ({$columndata['sub_ids']}) ";
                }
                array_push($this->columnArray, "(SELECT GROUP_CONCAT(DISTINCT((IF(rr.is_executive_board =1, '{$executiveBoardTitle}',IF(rri18n.title_lang!='' AND rri18n.title_lang IS NOT NULL ,rri18n.title_lang,rr.title)))) SEPARATOR ', ' )   FROM fg_rm_role_contact AS rc INNER JOIN fg_rm_category_role_function AS crf ON rc.fg_rm_crf_id = crf.id AND crf.category_id= {$categoryId} {$query}
                                                    INNER JOIN fg_rm_role AS rr  ON crf.role_id= rr.id LEFT JOIN fg_rm_role_i18n AS rri18n ON rri18n.id=rr.id AND rri18n.lang='{$this->club->get('default_lang')}' WHERE rc.contact_id = fg_cm_contact.{$assignedContact}  {$extraCondition} GROUP BY rc.contact_id) AS {$columndata['name']} ");
                break;
            case 'RF':
                $query = '';
                if ($columndata['sub_ids'] != 'all') {
                    $query = " AND crf.role_id IN ({$columndata['sub_ids']}) ";
                }

                array_push($this->columnArray, "(SELECT GROUP_CONCAT(Drolefun SEPARATOR ', ') FROM
                                                (SELECT rc.contact_id AS rcid,  CONCAT( (IF(rr.is_executive_board =1, '{$executiveBoardTitle}',IF(rri18n.title_lang!='' AND rri18n.title_lang IS NOT NULL,rri18n.title_lang ,rr.title))), ' (', GROUP_CONCAT(IF(rfi18n.title_lang!='' AND rfi18n.title_lang IS NOT NULL,rfi18n.title_lang,rf.title) SEPARATOR ', '), ')') AS Drolefun
                                                FROM fg_rm_role_contact AS rc
                                                INNER JOIN fg_rm_category_role_function AS crf ON rc.fg_rm_crf_id = crf.id AND crf.category_id= {$categoryId} {$query}
                                                INNER JOIN fg_rm_role AS rr ON crf.role_id= rr.id
                                                LEFT JOIN fg_rm_role_i18n AS rri18n ON rri18n.id=rr.id AND rri18n.lang='{$this->club->get('default_lang')}'
                                                INNER JOIN fg_rm_function AS rf ON rf.id = crf.function_id
                                                LEFT JOIN fg_rm_function_i18n AS rfi18n ON rf.id=rfi18n.id AND rfi18n.lang='{$this->club->get('default_lang')}'
                                                WHERE  {$extraCondition} GROUP BY rc.contact_id,crf.role_id) AS resultrf WHERE rcid=fg_cm_contact.{$assignedContact})  AS {$columndata['name']}");
                break;
            case 'Fn':
                $query = " AND crf.role_id IN ({$columndata['sub_ids']}) ";
                if ($columndata['fnType'] == 'TEAM') {
                    $categoryId = $this->club->get('club_team_id');
                }
                list($roletype, $clubId) = explode('-', $columndata['fnType']);
                //to find the master field according to the type of role()fed role/subfed role/clubrole)
                $assignedContact = $this->getMastercontactField($clubId);

                array_push($this->columnArray, "(SELECT GROUP_CONCAT(Drolefun SEPARATOR ', ') FROM
                                                (SELECT rc.contact_id AS rcid, CONCAT( GROUP_CONCAT(IF(rfi18n.title_lang!='' AND rfi18n.title_lang IS NOT NULL,rfi18n.title_lang,rf.title) ORDER BY rf.sort_order ASC SEPARATOR ', ') ) AS Drolefun
                                                FROM fg_rm_role_contact AS rc
                                                INNER JOIN fg_rm_category_role_function AS crf ON rc.fg_rm_crf_id = crf.id AND crf.category_id= {$categoryId} {$query}
                                                INNER JOIN fg_rm_role AS rr ON crf.role_id= rr.id
                                                LEFT JOIN fg_rm_role_i18n AS rri18n ON rri18n.id=rr.id AND rri18n.lang='{$this->club->get('default_lang')}'
                                                INNER JOIN fg_rm_function AS rf ON rf.id = crf.function_id
                                                LEFT JOIN fg_rm_function_i18n AS rfi18n ON rf.id=rfi18n.id AND rfi18n.lang='{$this->club->get('default_lang')}'
                                                WHERE  {$extraCondition} GROUP BY rc.contact_id, crf.role_id ) AS resultrf WHERE rcid=fg_cm_contact.{$assignedContact})  AS {$columndata['name']}");
                break;
            default:
                array_push($this->columnArray, $columndata['id']);
        }
    }

    /**
     * To find the actual federation column.
     *
     * @param array $columndata generic column
     */
    private function federationInfoField($columndata) {
        $this->key = $key;
        switch ($columndata['id']) {
            case 'clubs':
                array_push($this->columnArray, "(SELECT GROUP_CONCAT(if(fgc.id = fg_cm_contact.main_club_id,CONCAT(fgc.id,'#mainclub#'),fgc.id) SEPARATOR ',') FROM fg_cm_contact AS dfc INNER JOIN fg_club fgc ON (fgc.id = dfc.club_id) WHERE dfc.fed_contact_id=fg_cm_contact.fed_contact_id AND fgc.club_type IN('standard_club','sub_federation_club','federation_club'))  AS {$columndata['name']}");
                break;
            case 'sub_federations':
                array_push($this->columnArray, "(SELECT GROUP_CONCAT(dfgc.sub_federation_id SEPARATOR ',') FROM fg_cm_contact AS dfc INNER JOIN fg_club dfgc ON (dfgc.id = dfc.club_id) WHERE dfc.fed_contact_id=fg_cm_contact.fed_contact_id AND dfgc.club_type IN('standard_club','sub_federation_club','federation_club'))  AS {$columndata['name']}");
                break;
            case 'club':
                array_push($this->columnArray, "(SELECT GROUP_CONCAT(IF(fgc.title !='', if(fgc.id = fg_cm_contact.main_club_id,CONCAT(coalesce(NULLIF(fci18n.title_lang,''),fgc.title),'#mainclub#'),coalesce(NULLIF(fci18n.title_lang,''),fgc.title)),'') SEPARATOR ', ')  FROM fg_club fgc LEFT JOIN fg_club_i18n fci18n ON fci18n.id=fgc.id  AND fci18n.lang ='{$this->club->get('default_lang')}'  WHERE fgc.club_type IN('standard_club','sub_federation_club','federation_club') AND fgc.id IN (SELECT distinct dfc.club_id FROM fg_cm_contact AS dfc WHERE dfc.fed_contact_id=fg_cm_contact.fed_contact_id))  AS {$columndata['name']}");
                break;
            case 'sub_federation':
                array_push($this->columnArray, "(SELECT GROUP_CONCAT(IF(dfgc.title !='', coalesce(NULLIF(dfci18n.title_lang,'') ,dfgc.title),'') SEPARATOR ', ')  FROM fg_club dfgc LEFT JOIN fg_club_i18n dfci18n ON dfci18n.id=dfgc.id  AND dfci18n.lang ='{$this->club->get('default_lang')}'  WHERE dfgc.is_sub_federation=1 AND dfgc.id IN (SELECT distinct dfc.club_id FROM fg_cm_contact AS dfc WHERE dfc.fed_contact_id=fg_cm_contact.fed_contact_id))  AS {$columndata['name']}");
                break;
            case 'ceb_function':
                $checkContactField = ($this->club->get('type') == "federation") ? "fed_contact_id" : "subfed_contact_id";
                array_push($this->columnArray, "(SELECT GROUP_CONCAT(drf.title SEPARATOR '; ' )  FROM fg_rm_category_role_function AS dcrf  INNER JOIN fg_rm_role AS frr ON frr.category_id=dcrf.category_id AND frr.is_executive_board=1 INNER JOIN fg_rm_function AS drf ON dcrf.function_id=drf.id  INNER JOIN fg_rm_role_contact AS drc ON dcrf.id=drc.fg_rm_crf_id INNER JOIN fg_cm_contact DFC ON DFC.id=drc.contact_id  WHERE  fg_cm_contact.id = fg_cm_contact.{$checkContactField} AND fg_cm_contact.club_id={$this->club->get('id')} AND DFC.{$checkContactField}=fg_cm_contact.id) AS {$columndata['name']} ");
                break;
            case 'fedmemberyears': case 'membership_years':
                array_push($this->columnArray, "(Select sum(IF(MH.leaving_date IS NOT NULL,ROUND(DATEDIFF(MH.leaving_date,MH.joining_date)/365.25,1),ROUND(DATEDIFF(CURDATE(),MH.joining_date)/365.25,1))) AS fed_membership_years from fg_cm_membership_history MH where MH.membership_type='federation' and MH.contact_id=fg_cm_contact.fed_contact_id group by MH.contact_id) AS {$columndata['name']}");
                break;
        }
    }

    /**
     * To find the service details.
     *
     * @param array $columndata contains column details
     */
    private function sponsorserviceField($columndata) {
        $query = ($columndata['sub_ids'] != 'all') ? " AND SB.service_id IN ({$columndata['sub_ids']}) " : " AND FSS.category_id= {$columndata['id']} ";
        array_push($this->columnArray, "(SELECT GROUP_CONCAT(Servicedetails SEPARATOR ', ') FROM
                                        (SELECT SB.contact_id AS contactId,SB.service_id, CONCAT('{\"serviceName\":\"',FSS.title,'\" ,\"booking\":[',GROUP_CONCAT(CONCAT('{\"start\":\"',IF (SB.begin_date IS NULL,'',DATE_FORMAT(SB.begin_date,'" . $this->mysqlDateFormat . "')), '\",\"end\":\"',IF (SB.end_date IS NULL,'',DATE_FORMAT(SB.end_date,'" . $this->mysqlDateFormat . "')),'\",\"amount\":',IF(SB.payment_plan='none','\"\"',IF(getTotalServiceAmount(SB.id,IF (SB.first_payment_date IS NULL,'',SB.first_payment_date),IF (SB.last_payment_date IS NULL,null,SB.last_payment_date)) IS NULL,'\"null\"',getTotalServiceAmount(SB.id,IF (SB.first_payment_date IS NULL,'',SB.first_payment_date),IF (SB.last_payment_date IS NULL,null,SB.last_payment_date)))),',\"plan\":\"',SB.payment_plan,'\",\"lastpaymentDate\":\"',IF(SB.last_payment_date IS NULL,\"null\",SB.last_payment_date),'\"}')),']}') AS Servicedetails
                                         FROM fg_cm_contact AS FC
                                         INNER JOIN fg_sm_bookings  AS SB ON SB.contact_id = FC.id AND SB.is_deleted = 0 AND SB.club_id={$this->club->get('id')}
                                         INNER JOIN fg_sm_services AS FSS  ON FSS.id= SB.service_id {$query}
					 GROUP BY SB.contact_id,SB.service_id) AS resultrf WHERE contactId=fg_cm_contact.id) AS {$columndata['name']}");
    }

    /**
     * To find the sponsor analysis details.
     *
     * @param array $columndata contains column details
     */
    private function sponsoranalysisField($columndata) {
        $serviceIdCondition = ($columndata['service_id'] == 0) ? '1' : "SB.service_id={$columndata['service_id']}";
        switch ($columndata['id']) {
            case 'active_assignments':
                array_push($this->columnArray, '(SELECT count(BT.id) FROM fg_cm_contact AS BT LEFT JOIN fg_sm_bookings AS SB ON SB.contact_id = BT.id AND SB.is_deleted = 0 AND SB.club_id=' . $this->club->get('id') . " WHERE SB.contact_id=fg_cm_contact.id AND SB.begin_date <= now() AND (SB.end_date >= now() OR SB.end_date IS NULL OR SB.end_date = '')) AS {$columndata['name']}");
                array_push($this->columnArray, "(SELECT GROUP_CONCAT(serviceName SEPARATOR '; ') FROM (SELECT SB.contact_id as contactId ,CONCAT (FSS.title,'|',IF(SB.begin_date IS NULL,'',DATE_FORMAT(SB.begin_date,'" . $this->mysqlDateFormat . "')),'|',IF(SB.end_date IS NULL,'',DATE_FORMAT(SB.end_date,'" . $this->mysqlDateFormat . "'))) AS serviceName FROM fg_cm_contact AS BT LEFT JOIN fg_sm_bookings AS SB ON SB.contact_id = BT.id AND SB.is_deleted = 0 AND SB.club_id=" . $this->club->get('id') . " LEFT JOIN fg_sm_services  FSS ON FSS.id= SB.service_id  WHERE  SB.begin_date <= now() AND (SB.end_date >= now() OR SB.end_date IS NULL OR SB.end_date = '')) as activeService WHERE contactId = fg_cm_contact.id) AS 'activeServices'");
                break;
            case 'future_assignments':
                array_push($this->columnArray, '(SELECT count(BT.id) FROM fg_cm_contact AS BT LEFT JOIN fg_sm_bookings AS SB ON SB.contact_id = BT.id AND SB.is_deleted = 0 AND SB.club_id=' . $this->club->get('id') . " WHERE SB.contact_id=fg_cm_contact.id AND SB.begin_date > now())   AS {$columndata['name']}");
                array_push($this->columnArray, "(SELECT GROUP_CONCAT(serviceName SEPARATOR '; ') FROM (SELECT SB.contact_id as contactId ,CONCAT (FSS.title,'|',DATE_FORMAT(SB.begin_date,'" . $this->mysqlDateFormat . "'),'|',IF(SB.end_date IS NULL,'',DATE_FORMAT(SB.end_date,'" . $this->mysqlDateFormat . "'))) AS serviceName FROM fg_cm_contact AS BT LEFT JOIN fg_sm_bookings AS SB ON SB.contact_id = BT.id AND SB.is_deleted = 0 AND SB.club_id=" . $this->club->get('id') . " LEFT JOIN fg_sm_services  FSS ON FSS.id= SB.service_id   WHERE  SB.begin_date > now()) as futureService WHERE contactId = fg_cm_contact.id) AS 'futureServices'");
                break;
            case 'past_assignments':
                array_push($this->columnArray, '(SELECT count(BT.id) FROM fg_cm_contact AS BT LEFT JOIN fg_sm_bookings AS SB ON SB.contact_id = BT.id AND SB.is_deleted = 0 AND SB.club_id=' . $this->club->get('id') . " WHERE SB.contact_id=fg_cm_contact.id AND SB.end_date <= now())   AS {$columndata['name']}");
                array_push($this->columnArray, "(SELECT GROUP_CONCAT(serviceName SEPARATOR '; ') FROM (SELECT SB.contact_id as contactId ,CONCAT (FSS.title,'|',DATE_FORMAT(SB.begin_date,'" . $this->mysqlDateFormat . "'),'|',DATE_FORMAT(SB.end_date,'" . $this->mysqlDateFormat . "')) AS serviceName FROM fg_cm_contact AS BT LEFT JOIN fg_sm_bookings AS SB ON SB.contact_id = BT.id AND SB.is_deleted = 0 AND SB.club_id=" . $this->club->get('id') . " LEFT JOIN fg_sm_services  FSS ON FSS.id= SB.service_id   WHERE  SB.end_date <= now()) as pastService WHERE contactId = fg_cm_contact.id) AS 'pastServices'");
                break;
            case 'payments_curr':
                $startYear = $this->fiscalYearDetails['current']['start'];
                $endYear = $this->fiscalYearDetails['current']['end'];
                $individualService = isset($columndata['individual']) ? ' AND SB.id=MSB.id' : '';
                $allService = isset($columndata['individual']) ? ' AND bookingId=MSB.id' : '';
                array_push($this->columnArray, '(SELECT SUM(getPaymentAmount(PP.amount,PP.discount_type,PP.discount)) FROM fg_sm_paymentplans AS PP LEFT JOIN fg_sm_bookings AS SB ON SB.id = PP.booking_id AND SB.is_deleted = 0 AND SB.club_id=' . $this->club->get('id') . "  WHERE SB.contact_id=fg_cm_contact.id AND PP.date >='{$startYear}' AND (CASE WHEN (((SB.last_payment_date IS NULL) OR (SB.last_payment_date = '')) AND ((SB.end_date IS NOT NULL) OR (SB.end_date != ''))) THEN PP.date <=SB.end_date ELSE PP.date <='{$endYear}' END) AND SB.payment_plan !='none' {$individualService}  GROUP BY SB.contact_id)   AS {$columndata['name']}");
                array_push($this->columnArray, "(SELECT GROUP_CONCAT(Paymentdetails SEPARATOR ', ') FROM
                                                (SELECT SB.contact_id AS contactId,SB.id as bookingId, CONCAT('{\"date\":\"',DATE_FORMAT(PP.date,'" . $this->mysqlDateFormat . "'),'\",\"service\":\"',SS.title,'\",\"amount\":\"',getPaymentAmount(PP.amount,PP.discount_type,PP.discount),'\"}') AS Paymentdetails
                                                FROM fg_sm_paymentplans AS PP LEFT JOIN fg_sm_bookings AS SB ON SB.id = PP.booking_id AND SB.is_deleted = 0 AND SB.club_id={$this->club->get('id')} INNER JOIN fg_sm_services AS SS ON SS.id=SB.service_id WHERE  PP.date >='{$startYear}' AND (CASE WHEN (((SB.last_payment_date IS NULL) OR (SB.last_payment_date = '')) AND ((SB.end_date IS NOT NULL) OR (SB.end_date != ''))) THEN PP.date <=SB.end_date ELSE PP.date <='{$endYear}' END) AND SB.payment_plan !='none' ORDER BY PP.date ASC
                                                ) AS resultrf WHERE contactId=fg_cm_contact.id {$allService}) AS Currentpayments");
                break;
            case 'payments_nex':
                $startYear = $this->fiscalYearDetails['next']['start'];
                $endYear = $this->fiscalYearDetails['next']['end'];
                $individualService = isset($columndata['individual']) ? ' AND SB.id=MSB.id' : '';
                $allService = isset($columndata['individual']) ? ' AND bookingId=MSB.id' : '';
                array_push($this->columnArray, '(SELECT SUM(getPaymentAmount(PP.amount,PP.discount_type,PP.discount)) FROM fg_sm_paymentplans AS PP LEFT JOIN fg_sm_bookings AS SB ON SB.id = PP.booking_id AND SB.is_deleted = 0 AND SB.club_id=' . $this->club->get('id') . "  WHERE SB.contact_id=fg_cm_contact.id AND PP.date >='{$startYear}' AND (CASE WHEN (((SB.last_payment_date IS NULL) OR (SB.last_payment_date = '')) AND ((SB.end_date IS NOT NULL) OR (SB.end_date != ''))) THEN PP.date <=SB.end_date ELSE PP.date <='{$endYear}' END) AND SB.payment_plan !='none' {$individualService}  GROUP BY SB.contact_id)   AS {$columndata['name']}");
                array_push($this->columnArray, "(SELECT GROUP_CONCAT(Paymentdetails SEPARATOR ', ') FROM
                                                (SELECT SB.contact_id AS contactId,SB.id as bookingId, CONCAT('{\"date\":\"',DATE_FORMAT(PP.date,'" . $this->mysqlDateFormat . "'),'\",\"service\":\"',SS.title,'\",\"amount\":\"',getPaymentAmount(PP.amount,PP.discount_type,PP.discount),'\"}') AS Paymentdetails
                                                FROM fg_sm_paymentplans AS PP LEFT JOIN fg_sm_bookings AS SB ON SB.id = PP.booking_id AND SB.is_deleted = 0 AND SB.club_id={$this->club->get('id')} INNER JOIN fg_sm_services AS SS ON SS.id=SB.service_id WHERE  PP.date >='{$startYear}' AND (CASE WHEN (((SB.last_payment_date IS NULL) OR (SB.last_payment_date = '')) AND ((SB.end_date IS NOT NULL) OR (SB.end_date != ''))) THEN PP.date <=SB.end_date ELSE PP.date <='{$endYear}' END) AND SB.payment_plan !='none' ORDER BY PP.date ASC
                                                ) AS resultrf WHERE contactId=fg_cm_contact.id {$allService}) AS Nextpayments");
                break;
            case 'payment_plan':
                $subwhere = $this->extraWhereCondition($columndata['assign_type']);
                array_push($this->columnArray, "MSB.payment_plan AS {$columndata['name']}");
                array_push($this->columnArray, "( CONCAT(MSB.payment_plan,'|',CASE WHEN MSB.payment_plan='custom' THEN (SELECT COUNT(SP.id) FROM fg_sm_paymentplans AS SP INNER JOIN fg_sm_bookings FSB ON FSB.id=SP.booking_id AND FSB.is_deleted = 0 WHERE SP.booking_id=MSB.id AND FSB.contact_id=fg_cm_contact.id) WHEN MSB.payment_plan='regular' THEN MSB.repetition_months END)   ) AS paymentplanDetails");
                break;
            case 'payment_start_date':
                array_push($this->columnArray, "(DATE_FORMAT(MSB.begin_date,'" . $this->mysqlDateFormat . "')) AS {$columndata['name']}");
                break;
            case 'payment_end_date':
                $subwhere = $this->extraWhereCondition($columndata['assign_type']);
                array_push($this->columnArray, "( IF(MSB.end_date IS NULL,'null',DATE_FORMAT(MSB.end_date,'" . $this->mysqlDateFormat . "'))) AS {$columndata['name']}");
                break;
            case 'service_start_date':
                array_push($this->columnArray, "(DATE_FORMAT(MSB.begin_date,'" . $this->mysqlDateFormat . "')) AS {$columndata['name']}");
                break;
            case 'service_end_date':
                $subwhere = $this->extraWhereCondition($columndata['assign_type']);
                array_push($this->columnArray, "( IF(MSB.end_date IS NULL,'null',DATE_FORMAT(MSB.end_date,'" . $this->mysqlDateFormat . "'))) AS {$columndata['name']}");
                break;
            case 'service_title':
                array_push($this->columnArray, "(SELECT CONCAT(SE.title,' (',SC.title,')') FROM fg_sm_bookings AS SMB LEFT JOIN `fg_sm_services` SE ON SE.id=SMB.service_id LEFT JOIN fg_sm_category SC ON SC.id=SE.category_id WHERE SMB.id=MSB.id) AS {$columndata['name']}");
                break;
            case 'payment_deposited_with':
                $subwhere = $this->extraWhereCondition($columndata['assign_type']);
                if ($columndata['service_type'] == 'contact') {
                    array_push($this->columnArray, "(SELECT GROUP_CONCAT(depositedWith SEPARATOR ', ') FROM (SELECT SB.contact_id as contactId ,SB.id as bookingId, CONCAT('{\"name\":\"',contactName(SBD.contact_id),'\",\"type\":\"contact\"}')  AS depositedWith FROM  fg_cm_contact AS BT LEFT JOIN fg_sm_bookings AS SB ON SB.contact_id = BT.id AND SB.is_deleted = 0 AND SB.club_id=" . $this->club->get('id') . "  LEFT JOIN fg_sm_booking_deposited AS SBD ON SBD.booking_id=SB.id WHERE  SBD.contact_id IS NOT NULL AND SB.service_id={$columndata['service_id']} {$subwhere} ) AS deposit WHERE  contactId=fg_cm_contact.id AND bookingId = @MSB_BOOKING_ID) AS {$columndata['name']}");
                } elseif ($columndata['service_type'] == 'team') {
                    array_push($this->columnArray, "(SELECT GROUP_CONCAT(depositedWith SEPARATOR ', ') FROM (SELECT SB.contact_id as contactId ,SB.id as bookingId, CONCAT('{\"name\":\"', (SELECT IF(rri18n.title_lang IS NULL,rr.title,rri18n.title_lang) AS SSS FROM fg_rm_role AS rr LEFT JOIN fg_rm_role_i18n  AS rri18n ON rr.id=rri18n.id AND rri18n.lang='{$this->club->get('default_system_lang')}' WHERE  rr.id=SBD.role_id) ,'\"',',\"type\":\"team\"','}') AS depositedWith FROM  fg_cm_contact AS BT LEFT JOIN fg_sm_bookings AS SB ON SB.contact_id = BT.id  AND SB.is_deleted = 0  AND SB.club_id=" . $this->club->get('id') . "  LEFT JOIN fg_sm_booking_deposited SBD ON SBD.booking_id=SB.id WHERE   SB.service_id={$columndata['service_id']} {$subwhere} ) AS deposit WHERE  contactId=fg_cm_contact.id AND bookingId = @MSB_BOOKING_ID) AS {$columndata['name']}");
                } else {
                    array_push($this->columnArray, "'-' AS {$columndata['name']}");
                }
                break;
            case 'next_payment_date':
                $subwhere = $this->extraWhereCondition($columndata['assign_type']);
                array_push($this->columnArray, "(SELECT CONCAT(DATE_FORMAT(PP.date,'" . $this->mysqlDateFormat . "'),'|',getPaymentAmount(PP.amount, PP.discount_type, PP.discount)) FROM fg_cm_contact AS BT LEFT JOIN fg_sm_bookings AS SB ON SB.contact_id = BT.id  AND SB.is_deleted = 0  AND SB.club_id=" . $this->club->get('id') . " INNER JOIN fg_sm_paymentplans PP ON SB.id=PP.booking_id WHERE MSB.id=SB.id AND SB.contact_id=fg_cm_contact.id AND $serviceIdCondition AND PP.date > now() AND (CASE WHEN (((SB.last_payment_date IS NULL) OR (SB.last_payment_date = '')) AND ((SB.end_date IS NOT NULL) OR (SB.end_date != ''))) THEN PP.date <=SB.end_date ELSE 1 END) {$subwhere} ORDER BY PP.date ASC LIMIT 1 ) AS {$columndata['name']}");
                break;
            case 'total_payment':
                array_push($this->columnArray, "getTotalServiceAmount(MSB.id, null,null)  AS {$columndata['name']}");
                array_push($this->columnArray, "(SELECT GROUP_CONCAT(Paymentdetails SEPARATOR ', ') FROM
                                                (SELECT SB.contact_id AS contactId,SB.id as bookingId, CONCAT('{\"date\":\"',DATE_FORMAT(PP.date,'" . $this->mysqlDateFormat . "'),'\",\"service\":\"',SS.title,'\",\"amount\":\"',getPaymentAmount(PP.amount,PP.discount_type,PP.discount),'\"}') AS Paymentdetails
                                                FROM fg_sm_paymentplans AS PP LEFT JOIN fg_sm_bookings AS SB ON SB.id = PP.booking_id  AND SB.is_deleted = 0  AND SB.club_id={$this->club->get('id')} INNER JOIN fg_sm_services AS SS ON SS.id=SB.service_id WHERE  SB.payment_plan !='none' AND (SB.payment_plan='custom' OR (SB.payment_plan='regular' AND PP.date < DATE_ADD(SB.first_payment_date, INTERVAL SB.repetition_months*6 MONTH)))ORDER BY PP.date ASC
                                                ) AS resultrf WHERE contactId=fg_cm_contact.id AND bookingId=MSB.id) AS Totalpayments");
                array_push($this->columnArray, "(DATE_FORMAT(MSB.last_payment_date,'" . $this->mysqlDateFormat . "'))  AS SA_last_payment_date");
                break;
            case 'booking_id':
                array_push($this->columnArray, "MSB.id AS {$columndata['name']}");
                break;
            case 'service_name':
                array_push($this->columnArray, "(SELECT IF(FSI18.title_lang!='' AND FSI18.title_lang IS NOT NULL,FSI18.title_lang,FSS.title) FROM fg_sm_services AS FSS LEFT JOIN fg_sm_services_i18n AS FSI18 ON FSS.id=FSI18.id AND FSI18.lang='{$this->club->get('default_lang')}' WHERE FSS.club_id={$this->club->get('id')} AND FSS.id=MSB.service_id)   AS {$columndata['name']}");
                break;
            case 'service_id':
                array_push($this->columnArray, " MSB.service_id  AS {$columndata['name']}");
                break;
            case 'service_type':
                array_push($this->columnArray, " (SELECT FSS.service_type FROM fg_sm_services AS FSS  WHERE  FSS.id=MSB.service_id)  AS {$columndata['name']}");
                break;
            case 'service_category':
                array_push($this->columnArray, " (SELECT FSS.category_id FROM fg_sm_services AS FSS  WHERE  FSS.id=MSB.service_id)  AS {$columndata['name']}");
                break;
        }
    }

    /**
     * To create the extra where condition according to service type.
     *
     * @param type $servicetype
     *
     * @return string
     */
    private function extraWhereCondition($servicetype) {
        $subwhere = '';
        switch ($servicetype) {
            case 'past':
                $subwhere = 'AND SB.end_date <= now()';
                break;
            case 'future':
                $subwhere = 'AND SB.begin_date > now()';
                break;
            default:
                $subwhere = "AND SB.begin_date <= now() AND (SB.end_date >= now() OR SB.end_date IS NULL OR SB.end_date = '')";
                break;
        }

        return $subwhere;
    }

    /**
     * For find the actual contact option column.
     *
     * @param array $columndata generic column
     * @param int   $key        index
     */
    private function contactoptionField($columndata) {
        switch ($columndata['id']) {
            case 'membership':
                array_push($this->columnArray, "(Select IF(fg_cm_membership_i18n.title_lang !='' AND fg_cm_membership_i18n.title_lang IS NOT NULL, fg_cm_membership_i18n.title_lang, fg_cm_membership.title) FROM fg_cm_membership LEFT JOIN fg_cm_membership_i18n  ON fg_cm_membership.id=fg_cm_membership_i18n.id AND fg_cm_membership_i18n.lang='{$this->club->get('default_lang')}'  WHERE fg_cm_membership.id=fg_cm_contact.club_membership_cat_id  ) AS {$columndata['name']}");
                break;
            case 'fed_membership':
                array_push($this->columnArray, "(Select IF(fg_cm_membership_i18n.title_lang !='' AND fg_cm_membership_i18n.title_lang IS NOT NULL, fg_cm_membership_i18n.title_lang, fg_cm_membership.title) FROM fg_cm_membership LEFT JOIN fg_cm_membership_i18n  ON fg_cm_membership.id=fg_cm_membership_i18n.id AND fg_cm_membership_i18n.lang='{$this->club->get('default_lang')}'  WHERE fg_cm_membership.id=fg_cm_contact.fed_membership_cat_id  ) AS {$columndata['name']}");
                break;
        }
    }

    /**
     * to find the master field according to the type of role()fed role/subfed role/clubrole)
     * @param type $clubId
     * @return string
     */
    private function getMastercontactField($clubId) {
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
     * To handle club membership details
     * @param type $columndata
     */
    private function clubMembership($columndata) {
        switch ($columndata['id']) {
            case 'leaving_date':
                array_push($this->columnArray, "fg_cm_contact.leaving_date  AS {$columndata['name']}");
                break;
            case 'joining_date' :
                array_push($this->columnArray, "fg_cm_contact.joining_date   AS {$columndata['name']}");
                break;
            case 'first_joining_date':
                array_push($this->columnArray, "fg_cm_contact.first_joining_date    AS {$columndata['name']}");
                break;
            case 'membership':
                array_push($this->columnArray, "(Select IF(fg_cm_membership_i18n.title_lang !='' AND fg_cm_membership_i18n.title_lang IS NOT NULL, fg_cm_membership_i18n.title_lang, fg_cm_membership.title) FROM fg_cm_membership LEFT JOIN fg_cm_membership_i18n  ON fg_cm_membership.id=fg_cm_membership_i18n.id AND fg_cm_membership_i18n.lang='{$this->club->get('default_lang')}'  WHERE fg_cm_membership.id=fg_cm_contact.club_membership_cat_id  ) AS {$columndata['name']}");
                break;
            case 'club_member_years':
                array_push($this->columnArray, "(Select sum(IF(MH.leaving_date IS NOT NULL,ROUND(DATEDIFF(MH.leaving_date,MH.joining_date)/365.25,1),ROUND(DATEDIFF(CURDATE(),MH.joining_date)/365.25,1))) AS fed_membership_years from fg_cm_membership_history MH where MH.membership_type='club' and MH.contact_id=fg_cm_contact.id group by MH.contact_id) AS {$columndata['name']}");
                break;
        }
    }

    /**
     * To handle federation membership details 
     * @param type $columndata
     */
    private function fedMembership($columndata) {
        switch ($columndata['id']) {
            case 'leaving_date':
                array_push($this->columnArray, "(SELECT DFC.leaving_date FROM fg_cm_contact AS DFC WHERE DFC.id=fg_cm_contact.fed_contact_id AND fg_cm_contact.fed_contact_id IS NOT NULL) AS  {$columndata['name']}");
                break;
            case 'joining_date' :
                array_push($this->columnArray, "(SELECT DFC.joining_date FROM fg_cm_contact AS DFC WHERE DFC.id=fg_cm_contact.fed_contact_id AND fg_cm_contact.fed_contact_id IS NOT NULL) AS  {$columndata['name']}");
                break;
            case 'first_joining_date':
                array_push($this->columnArray, "(SELECT DFC.first_joining_date FROM fg_cm_contact AS DFC WHERE DFC.id=fg_cm_contact.fed_contact_id AND fg_cm_contact.fed_contact_id IS NOT NULL) AS  {$columndata['name']}");
                break;
            case 'fed_membership':
                array_push($this->columnArray, "(Select IF(fg_cm_membership_i18n.title_lang !='' AND fg_cm_membership_i18n.title_lang IS NOT NULL, fg_cm_membership_i18n.title_lang, fg_cm_membership.title) FROM fg_cm_membership LEFT JOIN fg_cm_membership_i18n  ON fg_cm_membership.id=fg_cm_membership_i18n.id AND fg_cm_membership_i18n.lang='{$this->club->get('default_lang')}'  WHERE fg_cm_membership.id=fg_cm_contact.fed_membership_cat_id  ) AS {$columndata['name']}");
                break;
            case 'fed_member_years':
                array_push($this->columnArray, "(Select sum(IF(MH.leaving_date IS NOT NULL,ROUND(DATEDIFF(MH.leaving_date,MH.joining_date)/365.25,1),ROUND(DATEDIFF(CURDATE(),MH.joining_date)/365.25,1))) AS fed_membership_years from fg_cm_membership_history MH where MH.membership_type='federation' and MH.contact_id=fg_cm_contact.fed_contact_id group by MH.contact_id) AS {$columndata['name']}");
                break;
        }
    }

}
