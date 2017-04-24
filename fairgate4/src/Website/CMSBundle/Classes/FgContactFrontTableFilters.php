<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Website\CMSBundle\Classes;

use Common\UtilityBundle\Util\FgSettings;

/**
 * Description of test
 *
 * @author jinesh.m
 */
class FgContactFrontTableFilters
{

    /**
     * Array of filter condiitons
     * @var array 
     */
    private $where;

    /**
     * Data from filter 
     * @var array 
     */
    private $filterDatas;

    /**
     * Current setting date formatt
     * @var Date 
     */
    private $mysqlDateFormat;

    /**
     * Object of the club
     * @var Object 
     */
    private $club;

    /**
     * Container object
     * @var object 
     */
    private $container;

    /**
     * Connection object
     * @var object 
     */
    private $conn;

    /**
     * final where condition
     * @var string 
     */
    private $whereCondition = '';

    /**
     * The constructor function
     * 
     * @param object $container   The container object
     * @param array  $filterArray filterdata
     */
    public function __construct($container, $filterArray)
    {
        $this->filterDatas = $filterArray;
        $this->where = array();
        $this->whereCondition = '';
        $this->mysqlDateFormat = FgSettings::getMysqlDateFormat();
        $this->club = $container->get('club');
        $this->container = $container;
        $this->conn = $this->container->get('database_connection');
    }

    /**
     * For create the where condition
     *
     * @return type
     */
    public function generateFilter()
    {
        foreach ($this->filterDatas as $filters) {
            list($filterType) = explode('-', $filters['type']);
            //check the type of filter data
            if (is_array($filters['value']) && count($filters['value']) > 0) {
                switch ($filterType) {
                    case "CF" :
                        $this->contactfieldFilter($filters);
                        break;
                    case "ROLES" : case "FROLES":
                        $this->roleFilter($filters);
                        break;
                    case "FILTERROLES":
                        $this->filterRoleFilter($filters);
                        break;
                    case "TEAM" :
                        $this->teamFilter($filters);
                        break;
                    case "WORKGROUP" :
                        $this->workgroupFilter($filters);
                        break;
                    case "CM" : case "FM" :
                        $this->membershipFilter($filters);
                        break;
                }
            }
        }
        //For create the final where condition from the array of filter conditions
        if (is_array($this->where) && count($this->where) > 1) {
            $this->whereCondition = '((' . implode(") AND (", $this->where) . "))";
        } else if (is_array($this->where) && count($this->where) == 1) {
            $this->whereCondition = '(' . $this->where[0] . ')';
        }

        return $this->whereCondition;
    }

    /**
     * For create the contact filter where condition
     * @param type $filters contact filter values
     */
    public function contactfieldFilter($filters)
    {

        $getAllContactFields = $this->club->get('allContactFields');

        if ($filters['value'] != '') {
            if ($getAllContactFields[$filters['id']]['type'] == 'checkbox') {
                $whereValue = '';
                foreach ($filters['value'] as $iterateValue) {
                    $whereValue.= " FIND_IN_SET('" . $iterateValue . "',  REPLACE(`" . $filters['id'] . "`,';',',') )  OR ";
                }
                if ($whereValue != '') {
                    $this->where[] = substr($whereValue, 0, -3);
                }
            } else {
                $this->where[] = " `" . $filters['id'] . "` IN ('" . implode("','", $filters['value']) . "')";
            }
        }
    }

    /**
     * For create the workgroup where condition
     * @param type $filters workgroup data
     */
    public function workgroupFilter($filters)
    {
        $where = array();
        $filters['entry'] = $this->club->get('club_workgroup_id');
        if ($filters['entry'] != '') {
            $where[] = "dcrf.category_id = '" . $filters['entry'] . "'";
            if ($filters['id'] != '') {
                $where[] = "dcrf.role_id IN ('" . implode("','", $filters['value']) . "')";
            }
            $where[] = "drc.assined_club_id = '" . $this->club->get('id') . "'";
        }

        if (count($where) > 0) {
            $subQuery = "(SELECT drc.contact_id FROM fg_rm_category_role_function AS dcrf INNER JOIN fg_rm_role_contact AS drc ON dcrf.id=drc.fg_rm_crf_id WHERE (" . implode(' AND ', $where) . "))";
            $this->where[] = '( fg_cm_contact.id IN ' . $subQuery . ')';
        }
    }

    /**
     * For create team where condition
     * @param type $filters team filter data
     */
    public function teamFilter($filters)
    {
        $where = array();
        $filters['entry'] = $this->club->get('club_team_id');
        if ($filters['id'] != '') {
            $where[] = "drr.team_category_id = '" . $filters['id'] . "'";
            $where[] = "dcrf.category_id = '" . $filters['entry'] . "'";
            if ($filters['input1'] != 'any') {
                $where[] = "dcrf.role_id IN ('" . implode("','", $filters['value']) . "')";
            }
        }

        if (count($where) > 0) {
            $subQuery = "(SELECT drc.contact_id FROM fg_rm_role AS drr INNER JOIN fg_rm_category_role_function AS dcrf ON drr.id = dcrf.role_id  INNER JOIN fg_rm_role_contact AS drc ON dcrf.id=drc.fg_rm_crf_id WHERE (" . implode(' AND ', $where) . "))";
            $this->where[] = '( fg_cm_contact.id IN ' . $subQuery . ')';
        }
    }

    /**
     * For create the role filter where condition
     * @param type $filters role filter datas
     */
    public function roleFilter($filters)
    {
        $where = array();
        if ($filters['id'] != '') {
            $where[] = "dcrf.category_id = '" . $filters['id'] . "'";
            if ($filters['value'] != '') {
                $where[] = "dcrf.role_id IN  ('" . implode("','", $filters['value']) . "')";
            }
        }

        if (count($where) > 0) {
            $subQuery = "(SELECT drc.contact_id FROM fg_rm_category_role_function AS dcrf INNER JOIN fg_rm_role_contact AS drc ON dcrf.id=drc.fg_rm_crf_id WHERE (" . implode(' AND ', $where) . "))";
            $this->where[] = '( fg_cm_contact.id IN (' . $subQuery . '))';
        }
    }

    /**
     * For create the filter role filter where condition
     * @param type $filters role filter datas
     */
    public function filterRoleFilter($filters)
    {
        $where = array();

        if ($filters['value'] != '') {
            $where[] = "dcrf.role_id IN  ('" . implode("','", $filters['value']) . "')";
        }

        if (count($where) > 0) {
            $subQuery = "(SELECT drc.contact_id FROM fg_rm_category_role_function AS dcrf INNER JOIN fg_rm_role_contact AS drc ON dcrf.id=drc.fg_rm_crf_id WHERE (" . implode(' AND ', $where) . "))";
            $this->where[] = '( fg_cm_contact.id IN (' . $subQuery . '))';
        }
    }

    /**
     * For create the membership filter where condition
     * @param type $filters membership filter datas
     */
    public function membershipFilter($filters)
    {
        switch ($filters['id']) {
            case 'fed_membership':
                if ($filters['value'] != '') {
                    $this->where[] = "fg_cm_contact.fed_membership_cat_id IN('" . implode("','", $filters['value']) . "')";
                }
                break;
            case 'club_membership':
            case 'membership':
                if ($filters['value'] != '') {
                    $this->where[] = "fg_cm_contact.club_membership_cat_id IN('" . implode("','", $filters['value']) . "')";
                }
                break;
        }
    }
}
