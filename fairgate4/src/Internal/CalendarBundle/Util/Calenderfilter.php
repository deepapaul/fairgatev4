<?php

namespace Internal\CalendarBundle\Util;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Util\FgSettings;
use Common\UtilityBundle\Repository\Pdo\CalendarPdo;

/**
 * For set the filter condition.
 *
 * @author jinesh.m
 */
class Calenderfilter
{

    private $container;
    private $clubId;
    private $clubtype;
    private $where;
    private $contactId;
    private $conn;
    private $mysqlDateFormat;
    public $filterArray;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->club = $this->container->get('club');
        $this->clubId = $this->club->get('id');
        $this->clubtype = $this->club->get('type');
        $this->where = '';
        $this->andWhere = '';
        $this->havewhere = "";
        $this->contactId = $this->container->get('contact')->get('id');
        $this->conn = $this->container->get('database_connection');
        $this->mysqlDateFormat = FgSettings::getMysqlDateFormat();
    }

    /**
     * To create where condition.
     *
     * @return type
     */
    public function generateFilter()
    {
        foreach ($this->filterArray as $filter) {
            switch ($filter['type']) {
                case 'team':
                case 'workgroup':
                    $this->groupFilter($filter);
                    break;
                case 'CLUB':
                    $this->clubFilter($filter);
                    break;
                case 'FED':
                    $this->clubFilter($filter);
                    break;
                case 'SUBFED':
                    $this->clubFilter($filter);
                    break;
                case 'CA':// for category filer
                    $this->categoryFilter($filter);
                    break;
                case 'CA_LEVELS':
                    $this->categoryFilter($filter, true);
                    break;
                case 'year':
                    $this->yearfilter($filter); //get event with in a range
                    break;
                case 'CATEGORY_WITHOUT':
                    $this->nonCategoryFilter($filter); //get unassigned category
                    break;
                case 'GEN_WITHOUT_AREA':
                    $this->nonRoleFilter($filter); //get unassigned role
                    break;
            }
        }
        $sWhere = substr($this->where, 3);
        $andwhere = substr($this->andWhere, 3);
        $whereArray = array();
        if ($sWhere != '') {
            $sWhere = '(' . $sWhere . ')';
            array_push($whereArray, $sWhere);
        }
        if ($andwhere != '') {
            $andwhere = '(' . $andwhere . ')';
            array_push($whereArray, $andwhere);
        }

        return $whereArray;
    }

    /**
     * create where  condition for role.
     *
     * @param type $filter
     */
    private function groupFilter($filter)
    {
        $this->where.= ' OR (CSA.role_id=' . $filter['id'] . ')';
    }

    /**
     * create where condition for heirrchy level shared events.
     *
     * @param type $filter
     */
    private function clubFilter($filter)
    {
        $this->where.=($this->clubId == $filter['id']) ? ' OR (C.club_id=' . $filter['id'] . ' AND is_club = 1)' : ' OR (C.club_id=' . $filter['id'] . ' AND C.share_with_lower = 1 AND is_club = 1)';
    }

    /**
     * create category related where condition.
     *
     * @param type $filter
     * @param type $sublevels
     */
    private function categoryFilter($filter, $sublevels = false)
    {
        if ($sublevels) {
            $where = '(CSC.category_id IN (' . $filter['value'] . '))';
            $federationId = $this->container->get('club')->get('federation_id');
            if ($federationId != 0 && $filter['id'] == "CA_federation") {
                $where .= ' OR (CSC.category_id IS NULL AND C.club_id=' . $federationId . ')';
            }
            $subFederationId = $this->container->get('club')->get('sub_federation_id');
            if ($subFederationId != 0 && $filter['id'] == "CA_sub_federation") {
                $where .= ' OR (CSC.category_id IS NULL AND C.club_id=' . $subFederationId . ')';
            }
            $this->andWhere .= ' OR (' . $where . ')';
        } else {
            $this->andWhere .= ' OR (CSC.category_id=' . $filter['id'] . ')';
        }
    }

    /**
     * create date range where condition.
     *
     * @param type $filter
     */
    private function yearfilter($filter)
    {
        $dateValues = explode('#', $filter['value']);
        if ($this->where == '') {
            $this->where .= " OR 1=1) AND (date(CD.start_date) <= STR_TO_DATE('" . FgUtility::getSecuredData($dateValues[1], $this->conn) . "', '%Y-%m-%d')
AND ((C.repeat_untill_date IS NOT NULL AND date(CD.untill) >= STR_TO_DATE('" . FgUtility::getSecuredData($dateValues[0], $this->conn) . "', '%Y-%m-%d') AND date(C.repeat_untill_date) >= STR_TO_DATE('" . FgUtility::getSecuredData($dateValues[0], $this->conn) . "', '%Y-%m-%d')) OR (C.repeat_untill_date IS NULL) OR (date(CD.end_date) >= STR_TO_DATE('" . FgUtility::getSecuredData($dateValues[0], $this->conn) . "', '%Y-%m-%d')))";
        } else {
            $this->where .= " ) AND (date(CD.start_date) <= STR_TO_DATE('" . FgUtility::getSecuredData($dateValues[1], $this->conn) . "', '%Y-%m-%d')
AND ((C.repeat_untill_date IS NOT NULL AND date(CD.untill) >= STR_TO_DATE('" . FgUtility::getSecuredData($dateValues[0], $this->conn) . "', '%Y-%m-%d') AND date(C.repeat_untill_date) >= STR_TO_DATE('" . FgUtility::getSecuredData($dateValues[0], $this->conn) . "', '%Y-%m-%d')) OR (C.repeat_untill_date IS NULL) OR (date(CD.end_date) >= STR_TO_DATE('" . FgUtility::getSecuredData($dateValues[0], $this->conn) . "', '%Y-%m-%d')))";
        }
    }

    /**
     * create unassigned category related where condition.
     *
     * @param type $filter
     */
    private function nonCategoryFilter($filter)
    {
        $this->andWhere .=' OR (CSC.category_id IS NULL AND C.club_id=' . $this->clubId . ')';
    }

    /**
     * create unassigned role related where condition.
     *
     * @param type $filter
     */
    private function nonRoleFilter($filter)
    {
//        $calendarPdo = new CalendarPdo($this->container);
//        //select all inactive roles of current club
//        $roleIdselectionQuery = "SELECT id FROM fg_rm_role WHERE club_id=".$this->clubId." AND is_active=0";
//        $nonActiveRoleArray = $calendarPdo->executeQuery($roleIdselectionQuery);
//
//        $nonActiveRoles = array();
//        $inIds = '';
//             if(count($nonActiveRoleArray)>0) {
//               foreach($nonActiveRoleArray as $nonActiveRoleIds ) {
//                   array_push($nonActiveRoles,$nonActiveRoleIds['id']);
//              }
//         $inIds = implode(',', $nonActiveRoles);
//        }
        //   $this->where.= ($inIds=='')? ' OR (CSA.role_id IS NULL AND (CSA.is_club=0 OR CSA.is_club IS NULL) )' :' OR ((CSA.role_id IS NULL AND (CSA.is_club=0 OR CSA.is_club IS NULL)) OR CSA.role_id IN ('.$inIds.')  )';

        $this->where.= ' OR (CSA.role_id IS NULL AND (CSA.is_club=0 OR CSA.is_club IS NULL) )';
    }
}
