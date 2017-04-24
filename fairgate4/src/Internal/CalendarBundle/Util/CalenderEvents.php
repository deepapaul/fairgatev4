<?php

namespace Internal\CalendarBundle\Util;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Common\UtilityBundle\Util\FgUtility;

/**
 * This class is used to get the base query of events viewable to the logged in user
 * based on his userrights, club and selected date span.
 */
class CalenderEvents
{

    private $container;
    private $club;
    private $contact;
    private $clubId;
    private $contactId;
    public $selectionFields = '';
    public $tableColumns = array();
    public $from;
    public $where = '';
    public $result;
    public $groupBy = '';
    public $orderBy = '';
    public $searchval = '';
    public $having = '';
    public $isPublic;

    /**
     *
     * @var int
     */
    public $conn;

    /**
     *
     * @param ContainerInterface $container Container object
     * @param date               $startDate  Interval start date
     * @param date               $endDate    Interval end date
     * @param date               $strtDtTime Interval start date with time
     */
    public function __construct(ContainerInterface $container, $startDate = '', $endDate = '', $strtDtTime = '', $isPublic = false)
    {
        $this->container = $container;
        $this->club = $this->container->get('club');
        $this->contact = $this->container->get('contact');
        $this->clubId = $this->club->get('id');
        $this->contactId = $contactId;
        $this->startDate = ($startDate != '') ? $startDate . ' 00:00:00' : (($strtDtTime != '') ? $strtDtTime : '');
        $this->endDate = ($endDate != '') ? $endDate . ' 23:59:59' : '';
        $this->conn = $this->container->get('database_connection');
        $this->isPublic = $isPublic;
    }

    /**
     * This function is used to add a column to the existing selected columns
     *
     * @param string $column select statement
     *
     * @return string $this->selectionFields
     */
    public function addColumn($column)
    {
        $this->selectionFields = ',`' . $column . '`';

        return $this->selectionFields;
    }

    /**
     * This function is used to add a column to the existing selected columns
     *
     * @param string $column select statement
     *
     * @return string $this->selectionFields
     */
    public function addHaving($having)
    {
        if ($having != '') {
            $this->having = $having;
        }
    }

    /**
     * This function is used to add the default columns and other area specific columns to select query
     *
     * @param array $columns select criteria
     *
     * @return string $this->selectionFields
     */
    public function setColumns($columns = array())
    {
        $terminologyService = $this->container->get('fairgate_terminology_service');
        $federationTerm = ucfirst($terminologyService->getTerminology('Federation', $this->container->getParameter('singular')));
        $subfederationTerm = ucfirst($terminologyService->getTerminology('Sub-federation', $this->container->getParameter('singular')));
        $termExecutive = ucfirst($terminologyService->getTerminology('Executive Board', $this->container->getParameter('singular')));

        $defAreaColor = $this->container->getParameter('defaultColorClubOrRole');
        $defFedAreaColor = $this->container->getParameter('defaultColorFedLevel');

        $columnfields = array();
        array_push($columnfields, "C.id AS eventId");
        array_push($columnfields, "C.club_id AS clubId");
        array_push($columnfields, "C.scope AS scope");
        array_push($columnfields, "C.share_with_lower AS shareWithLower");
        array_push($columnfields, "C.is_repeat AS isMasterRepeat");
        array_push($columnfields, "C.is_allday AS isAllday");
        array_push($columnfields, "C.repeat_untill_date AS eventRepeatUntillDate");
        array_push($columnfields, "CD.id AS eventDetailId");
        array_push($columnfields, "CD.location AS location");
        array_push($columnfields, "CD.start_date AS startDate");
        array_push($columnfields, "CD.end_date AS endDate");
        array_push($columnfields, "CD.status AS eventDetailType");
        array_push($columnfields, "CD.untill AS eventDetailUntillDate");
        array_push($columnfields, "'{$this->startDate}' AS intervalStartDate");
        array_push($columnfields, "'{$this->endDate}' AS intervalEndDate");
        array_push($columnfields, "IF(CDI18N.title_lang IS NOT NULL AND CDI18N.title_lang != '', CDI18N.title_lang, CD.title) AS title");
        array_push($columnfields, "IF(CDI18N.desc_lang IS NULL OR CDI18N.desc_lang = '', CD.description, CDI18N.desc_lang) AS description");
        array_push($columnfields, "(SELECT COALESCE(SUM(DISTINCT CSA.is_club), 0) FROM fg_em_calendar_selected_areas AS CSA WHERE CSA.calendar_details_id = CD.id AND CSA.role_id IS NULL) AS isClubAreaSelected");
        array_push($columnfields, "(IF (CL.calendar_color_code IS NOT NULL AND CL.calendar_color_code != '', CL.calendar_color_code, (CASE WHEN CL.club_type = 'federation' THEN '{$defFedAreaColor}' WHEN CL.club_type = 'sub_federation' THEN '{$defFedAreaColor}' ELSE '{$defAreaColor}' END))) AS clubColorCode");
        array_push($columnfields, "(SELECT GROUP_CONCAT(CONCAT_WS('', CSC.category_id), '|@@@|', (CASE WHEN (CL.club_type = 'federation' && C.club_id != {$this->clubId}) THEN '{$federationTerm}' WHEN (CL.club_type = 'sub_federation' && C.club_id != {$this->clubId}) THEN '{$subfederationTerm}' ELSE (IF(CCI18N.title_lang IS NULL OR CCI18N.title_lang = '', CC.title, CCI18N.title_lang)) END) "
            . "ORDER BY CC.sort_order ASC SEPARATOR '|&&&|') FROM fg_em_calendar_selected_categories AS CSC LEFT JOIN fg_em_calendar_category AS CC ON (CC.id = CSC.category_id) "
            . "LEFT JOIN fg_em_calendar_category_i18n AS CCI18N ON (CCI18N.id = CC.id AND CCI18N.lang = '{$this->club->get("default_lang")}') WHERE CSC.calendar_details_id = CD.id GROUP BY CD.id) AS eventCategories");
        array_push($columnfields, "(SELECT GROUP_CONCAT(CSA.role_id, '|@@@|', "
            . "( CASE WHEN R.title = 'Executive Board' THEN '$termExecutive' ELSE IF(RI18N.title_lang IS NOT NULL AND RI18N.title_lang != '', RI18N.title_lang, R.title) END ), '|@@@|', "                
            . "(IF (R.calendar_color_code IS NOT NULL AND R.calendar_color_code != '', R.calendar_color_code, IF (RC.calendar_color_code IS NOT NULL AND RC.calendar_color_code != '', RC.calendar_color_code, '{$defAreaColor}'))) "
            . "ORDER BY R.type ASC,TC.sort_order,R.sort_order ASC SEPARATOR '|&&&|') FROM fg_em_calendar_selected_areas AS CSA LEFT JOIN fg_rm_role R ON (CSA.role_id = R.id) "
            . "LEFT JOIN fg_rm_role_i18n AS RI18N ON (RI18N.id = R.id AND RI18N.lang = '{$this->club->get("default_lang")}') LEFT JOIN fg_rm_category AS RC ON (RC.id = R.category_id) LEFT JOIN fg_team_category AS TC ON (TC.id = R.team_category_id)"
            . "WHERE CSA.calendar_details_id = CD.id AND CSA.is_club = 0 AND CSA.role_id IS NOT NULL AND R.is_active = 1 GROUP BY CD.id) AS eventRoleAreas");
        array_push($columnfields, "(SELECT GROUP_CONCAT(CSA.role_id SEPARATOR '|&&&|') FROM fg_em_calendar_selected_areas AS CSA "
            . "WHERE CSA.calendar_details_id = CD.id AND CSA.is_club = 0 AND CSA.role_id IS NOT NULL GROUP BY CD.id) AS eventRoleIds");
        array_push($columnfields, "(CONCAT(UNIX_TIMESTAMP(CD.start_date), CD.status)) AS eventSortField");
        array_push($columnfields, "(CONCAT(IF(CR.FREQ IS NOT NULL AND CR.FREQ != '', CONCAT('FREQ=', CR.FREQ, ';'), ''), "
            . "IF(CR.INTERVAL IS NOT NULL AND CR.INTERVAL != '', CONCAT('INTERVAL=', CR.INTERVAL, ';'), ''), "
            . "IF(CR.BYDAY IS NOT NULL AND CR.BYDAY != '', CONCAT('BYDAY=', CR.BYDAY, ';'), ''), "
            . "IF(CR.BYMONTH IS NOT NULL AND CR.BYMONTH != '', CONCAT('BYMONTH=', CR.BYMONTH, ';'), ''), "
            . "IF(CR.BYMONTHDAY IS NOT NULL AND CR.BYMONTHDAY != '', CONCAT('BYMONTHDAY=', CR.BYMONTHDAY, ';'), ''))) AS eventRules");
        if (is_array($columns) && count($columns) > 0) {
            foreach ($columns as $column) {
                switch ($column) {
                    default:
                        array_push($columnfields, $column);
                        break;
                }
            }
        }
        $this->tableColumns = $columnfields;
        if (count($this->tableColumns) > 0) {
            $this->selectionFields = implode(', ', $columnfields);
        } else {
            $this->selectionFields = '*';
        }

        return $this->selectionFields;
    }

    /**
     * This function is used for building the from condition
     *
     * @return string $this->from From condition qry
     */
    public function setFrom()
    {
        $this->from = "fg_em_calendar AS C "
            . "LEFT JOIN fg_em_calendar_details AS CD ON (CD.calendar_id = C.id) "
            . "LEFT JOIN fg_em_calendar_details_i18n AS CDI18N ON (CDI18N.id = CD.id AND CDI18N.lang = '{$this->club->get("default_lang")}') "
            . "LEFT JOIN fg_em_calendar_rules AS CR ON (CR.id = C.calendar_rules_id) "
            . "LEFT JOIN fg_em_calendar_selected_areas AS CSA ON (CSA.calendar_details_id = CD.id) "
            . "LEFT JOIN fg_rm_role AS ROLE ON (CSA.role_id = ROLE.id) "
            . "LEFT JOIN fg_em_calendar_selected_categories AS CSC ON (CSC.calendar_details_id = CD.id) "
            . "LEFT JOIN fg_em_calendar_category AS CC ON (CC.id = CSC.category_id) "
            . "LEFT JOIN fg_em_calendar_category_i18n AS CCI18N ON (CCI18N.id = CC.id AND CCI18N.lang = '{$this->club->get("default_lang")}') "
            . "LEFT JOIN fg_club AS CL ON (CL.id = C.club_id)";

        return $this->from;
    }

    /**
     * This function is used to set the initial where condition
     *
     * @param string $condition Condition qry
     */
    public function setCondition($condition = '')
    {
        if ($condition != '') {
            $this->where = $condition;
        } else {
            $this->where = $this->getDateCondition();
            $this->where .= ' AND ' . $this->getClubCondition();
            $this->where .= ' AND ' . $this->getScopeCondition();
            $this->where .= ' AND (ROLE.is_active = 1 OR ROLE.is_active IS NULL) ';
        }
    }

    /**
     * This function is used to build the club condition for events.
     * All events of that club + All events of higher level clubs which are shared with lower should be available.
     *
     * @return string $where The where condition
     */
    private function getClubCondition()
    {
        $clubtype = $this->club->get('type');
        $where = "(C.club_id = {$this->clubId} ";
        if (in_array($clubtype, array('federation_club', 'sub_federation', 'sub_federation_club'))) {
            $clubHeirarchy = implode(",", $this->club->get('clubHeirarchy'));
            $where .= "OR (C.club_id IN ({$clubHeirarchy}) AND C.share_with_lower = 1)";
        }
        $where .= ")";

        return $where;
    }

    /**
     * This function is used to build the date condition for events.
     * All non repeating, repeating events within the interval date span should be available.
     *
     * @return string $where The where condition
     */
    private function getDateCondition()
    {
        $where = "1";
        if ($this->startDate != '' && $this->endDate != '') {
            $where = "(CD.start_date <= '{$this->endDate}' AND ((C.repeat_untill_date IS NOT NULL AND CD.untill >= '{$this->startDate}' AND C.repeat_untill_date >= '{$this->startDate}') OR (C.repeat_untill_date IS NULL) OR (CD.end_date >= '{$this->startDate}')))";
        } elseif ($this->startDate != '' && $this->endDate == '') {
            // $where = "(((CD.start_date >= '{$this->startDate}' OR CD.end_date >= '{$this->startDate}') AND C.is_repeat = 0) OR (C.is_repeat = 1 AND C.repeat_untill_date IS NULL) OR (C.repeat_untill_date IS NOT NULL AND C.repeat_untill_date >= '{$this->startDate}' AND CD.untill >= '{$this->startDate}'))";
            $where = "( ((CD.start_date >= '{$this->startDate}' OR CD.end_date >= '{$this->startDate}') AND CD.status = 1 ) OR (CD.status = 0 AND ( (CD.untill IS NULL) OR (CD.untill IS NOT NULL AND CD.untill >= '{$this->startDate}' ) ) ) )";
        }

        return $where;
    }

    /**
     * This function is used to build the scope condition for events.
     * All events for which the logged in person is within its scope should be available.
     *
     * @return string $where The where condition
     */
    private function getScopeCondition()
    {
        $isClubCalendarAdmin = in_array('ROLE_CALENDAR', $this->contact->get('availableUserRights')) ? 1 : 0;
        if (!$isClubCalendarAdmin) {
            if ($this->isPublic) {  // for showing public events in website
                $where = "(C.scope = 'PUBLIC' "; 
            } else {
                $where = "(C.scope = 'PUBLIC' OR C.scope = 'INTERNAL' OR (C.SCOPE = 'GROUP' AND CSA.is_club = 1)";
                $myGroups = $this->getMyTeamsAndWorkgroups();
                if (count($myGroups) > 0) {
                    $myGroupIds = implode(',', $myGroups);
                    $where .= " OR (C.SCOPE = 'GROUP' AND CSA.role_id IN ({$myGroupIds}))";
                }
            }
            //For not showing the events without category for non-admins
            $this->having = ' HAVING ((eventRoleIds != "" OR isClubAreaSelected = 1)  AND eventCategories != "" )';
            
            $where .= ")";
        } else {
            $where = "1";
        }

        return $where;
    }

    /**
     * This function is used to get all teams and workgroups in which the logged in user have calendar 'SCOPE = GROUP' rights
     *
     * @return array $myGroupsUnique My teams and workgroups
     */
    private function getMyTeamsAndWorkgroups()
    {
        $myGroups = array();
        $groupRights = $this->contact->get('clubRoleRightsGroupWise');
        if (isset($groupRights['ROLE_GROUP_ADMIN']['teams'])) {
            $myGroups = array_merge($myGroups, $groupRights['ROLE_GROUP_ADMIN']['teams']);
        }
        if (isset($groupRights['ROLE_GROUP_ADMIN']['workgroups'])) {
            $myGroups = array_merge($myGroups, $groupRights['ROLE_GROUP_ADMIN']['workgroups']);
        }
        if (isset($groupRights['ROLE_CALENDAR_ADMIN']['teams'])) {
            $myGroups = array_merge($myGroups, $groupRights['ROLE_CALENDAR_ADMIN']['teams']);
        }
        if (isset($groupRights['ROLE_CALENDAR_ADMIN']['workgroups'])) {
            $myGroups = array_merge($myGroups, $groupRights['ROLE_CALENDAR_ADMIN']['workgroups']);
        }
        if (isset($groupRights['MEMBER']['teams'])) {
            $myGroups = array_merge($myGroups, $groupRights['MEMBER']['teams']);
        }
        if (isset($groupRights['MEMBER']['workgroups'])) {
            $myGroups = array_merge($myGroups, $groupRights['MEMBER']['workgroups']);
        }
        $myGroupsUnique = array_unique($myGroups);

        return $myGroupsUnique;
    }

    /**
     * This function is used to add a andWhere condition to the existing query.
     *
     * @param string $condition Condition qry
     *
     * @return string $this->where The where condition
     */
    public function addCondition($condition = '')
    {
        if ($condition != '') {
            $this->where .= ' AND (' . $condition . ' )';
        }

        return $this->where;
    }

    /**
     * This function is used to add a orWhere condition to the existing query.
     *
     * @param string $condition Condition qry
     *
     * @return string $this->where The where condition
     */
    public function orCondition($condition = '')
    {
        if ($condition != '') {
            $this->where .= ' OR (' . $condition . ' )';
        }

        return $this->where;
    }

    /**
     * This function is used to get the where condition
     *
     * @return string $this->where The where condition
     */
    public function getCondition()
    {
        return $this->where;
    }

    /**
     * This function is used to add a order by condition to the qry.
     *
     * @param string $orderColumn Qrder column
     */
    public function addOrderBy($orderColumn = '')
    {
        if ($orderColumn != '') {
            $this->orderBy = ' ' . $orderColumn;
        }
    }

    /**
     * This function is used to add a group by condition to the qry.
     *
     * @param string $col
     */
    public function setGroupBy($column = '')
    {
        if ($column != '') {
            $this->groupBy = $column;
        }
    }

    /**
     * This function is used to get all event related details within the given date interval.
     *
     * @return string $this->result Final query string.
     */
    public function getResult()
    {
        $this->result = 'SELECT ' . $this->selectionFields . ' FROM ' . $this->from;
        $this->result .= ' WHERE ' . $this->where;


        if ($this->searchval != "") {
            $this->result .= ' AND (' . $this->setSearchFields() . ')';
        }
        if ($this->groupBy != '') {
            $this->result .= ' GROUP BY ' . $this->groupBy;
        }
        if ($this->having != '') {
            $this->result .= ' ' . $this->having;
        }
        if ($this->orderBy != '') {
            $this->result .= ' ORDER BY' . $this->orderBy;
        }
        return $this->result;
    }

    /**
     * The function to get events withoutout categories
     *
     * @return querystring.
     */
    public function getEventsWithoutCategory()
    {
        $where = $this->getClubCondition();
        $where .= ' AND ' . $this->getScopeCondition();
        $where .= ' AND  category_id IS NULL';

        $eventSelectionFields .= "COUNT(DISTINCT C.id) AS eventCount";

        $eventFrom = "fg_em_calendar AS C "
            . "INNER JOIN fg_em_calendar_details AS CD ON (CD.calendar_id = C.id AND CD.status != 2) "
            . "LEFT JOIN fg_em_calendar_selected_areas AS CSA ON (CSA.calendar_details_id = CD.id) "
            . "LEFT JOIN fg_em_calendar_selected_categories AS CSC ON (CSC.calendar_details_id = CD.id) ";
        $eventListQuery = 'SELECT ' . $eventSelectionFields . ' FROM ' . $eventFrom . ' WHERE ' . $where;
        return $eventListQuery;
    }

    /**
     * The function to get all teams/workgroups to which an event has been added ever
     *
     * @return querystring.
     */
    public function getRoleOptions()
    {
        $executiveBoardTerm = ucfirst($this->container->get('fairgate_terminology_service')->getTerminology('Executive Board', $this->container->getParameter('singular'))) ;
        $where = $this->getClubCondition();
        $where .= ' AND ' . $this->getScopeCondition();
        $where .=' AND R.is_active=1';
        $where .=" AND R.club_id=" . $this->club->get('id');
        $roleSelectionFields = "R.id, R.type,CASE WHEN R.is_executive_board=1 THEN '" . $executiveBoardTerm . "' ELSE IF(RI18N.title_lang IS NULL OR RI18N.title_lang = '', R.title, RI18N.title_lang) END AS title, is_executive_board, R.type";
        $roleSelectionFields .= ", (COALESCE(SUM(DISTINCT CSA.is_club), 0)) AS isClubAreaSelected";

        $roleFrom = "fg_em_calendar AS C "
            . "INNER JOIN fg_em_calendar_details AS CD ON (CD.calendar_id = C.id) "
            . "INNER JOIN fg_em_calendar_selected_areas AS CSA ON (CSA.calendar_details_id = CD.id) "
            . "INNER JOIN fg_rm_role R ON (CSA.role_id = R.id) "
            . "LEFT JOIN fg_rm_role_i18n AS RI18N ON (RI18N.id = R.id AND RI18N.lang = '{$this->club->get("default_lang")}')"
            . "LEFT JOIN fg_team_category tc ON R.team_category_id=tc.id "
            . "INNER JOIN fg_club AS CL ON (CL.id = C.club_id)";
        $roleGroupBy = ' GROUP BY (R.id)';
        $orderBy = ' ORDER BY tc.sort_order,R.sort_order ASC';
        $roleListQuery = 'SELECT ' . $roleSelectionFields . ' FROM ' . $roleFrom . ' WHERE ' . $where . $roleGroupBy . $orderBy;

        return $roleListQuery;
    }

    /**
     * The function to the number of events where share_with_lower is set in my events
     *
     * @return querystring.
     */
     public function getClubOptions($clubHierarchy)
    {
        $eventSelectionFields = " COUNT(C.id) AS eventCount";
        $eventSelectionFields .= ", (COALESCE(SUM(DISTINCT CSA.is_club), 0)) AS isClubAreaSelected";
        foreach ($clubHierarchy as $clubId => $clubDetail) {
            $clubType = $clubDetail['club_type'];
            $eventSelectionFields.= ", SUM(IF(club_id = $clubId AND (( ('$clubType' IN ('federation', 'sub_federation')) AND share_with_lower = 1) OR ( ('$clubType' IN ('federation', 'sub_federation')) AND '$clubId'='$this->clubId') OR('$clubType' IN ( 'sub_federation_club','standard_club','federation_club') )), 1, 0)) AS $clubType";
            $eventSelectionFields.= ", $clubId AS $clubType" . "_id";
        }

        $where = $this->getClubCondition();
        $where .= ' AND ' . $this->getScopeCondition();

        $eventFrom = "fg_em_calendar AS C ";
        $eventFrom .= "INNER JOIN fg_em_calendar_details AS CD ON (CD.calendar_id = C.id) ";
        $eventFrom .= "INNER JOIN fg_em_calendar_selected_areas AS CSA ON (CSA.calendar_details_id = CD.id) ";

        $eventListQuery = 'SELECT ' . $eventSelectionFields . ' FROM ' . $eventFrom . ' WHERE ' . $where;
        return $eventListQuery;
    }

    /**
     * The function to the number of events where share_with_lower is set in my events
     *
     * @return querystring.
     */
    public function getFirstEvent()
    {
        $where = $this->getClubCondition();
        $where .= ' AND ' . $this->getScopeCondition();
        $where .= ' AND start_date IS NOT NULL AND start_date != "0000-00-00 00:00:00"';

        $eventSelectionFields = 'start_date';
        $eventSelectionFields .= ", (COALESCE(SUM(DISTINCT CSA.is_club), 0)) AS isClubAreaSelected";

        $eventFrom = "fg_em_calendar AS C ";
        $eventFrom .= "INNER JOIN fg_em_calendar_details AS CD ON (CD.calendar_id = C.id) ";
        $eventFrom .= "INNER JOIN fg_em_calendar_selected_areas AS CSA ON (CSA.calendar_details_id = CD.id) ";

        $eventGroupBy = ' GROUP BY (CD.id) ';

        $eventListQuery = 'SELECT ' . $eventSelectionFields . ' FROM ' . $eventFrom . ' WHERE '
            . $where . $eventGroupBy . ' ORDER BY start_date ASC LIMIT 1 ';
        return $eventListQuery;
    }

    /**
     * For set the search column fields
     * @return array column fields array
     */
    private function getSearchFields()
    {
        $searchColumns[] = 'CD.title';
        $searchColumns[] = "CDI18N.title_lang";
        $searchColumns[] = "CD.description";
        $searchColumns[] = "CDI18N.desc_lang";
        $searchColumns[] = "CD.location";
        return $searchColumns;
    }

    /**
     * To creating the serach field condition from the search value setting
     * @return string
     */
    public function setSearchFields()
    {
        $sWhere = '';
        if ($this->searchval != "") {
            $columns = $this->getSearchFields();
            $sWhere = "(";
            foreach ($columns as $column) {
                $searchVal = FgUtility::getSecuredDataString($this->searchval, $this->conn);
                $sWhere.= $column . " LIKE '%" . $searchVal . "%' OR ";
            }
            $sWhere = substr_replace($sWhere, "", -3);
            $sWhere .= ")";
        }
        return $sWhere;
    }
}
