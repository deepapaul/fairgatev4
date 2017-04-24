<?php

namespace Clubadmin\Classes;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Common\UtilityBundle\Util\FgUtility;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * For create the contact list query .
 *
 * @author Jinesh.m <jinesh.m@pitsolutions.com>
 */
class Clublist
{

    public $tablejoin;
    public $orderBy;
    public $where;
    public $from;
    public $limit;
    public $offset;
    public $result;
    public $clubId;
    private $container;
    private $conn;
    private $clubadminConn;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container container
     * @param type                                                      $club      object of club details
     */
    public function __construct(ContainerInterface $container, $club)
    {

        $this->clubId = $club->get("id");
        $this->container = $container;
        $this->conn = $this->container->get('database_connection');
        $this->clubadminConn = $this->container->get('fg.admin.connection')->getAdminConnection();
    }

    /**
     * @param type $column addcolumn
     *
     * @return type
     */
    public function addtColumns($column)
    {

        $this->selectionFields = "," . $column;

        return $this->selectionFields;
    }

    /**
     * @param type $columns setColumn
     *
     * @return type
     */
    public function setColumns($columns)
    {
        $this->tableColumns = $columns;

        if (in_array('clubname', $columns)) {

            $key = array_search('clubname', $columns); //
            //$newfield= 'CONCAT_WS(" ",'.$firstname.','. $lastname.' ) as contactname';
            $newfield = 'COALESCE(NULLIF(fci18n.title_lang,""), fc.title) AS clubname';

            $columns[$key] = $newfield;
        }
        $this->tableColumns = $columns;


        $this->selectionFields = implode(",", $columns);

        return $this->selectionFields;
    }

    /**
     * @return type
     */
    public function setCount()
    {
        $this->selectionFields = "count(fc.id) as count";

        return $this->selectionFields;
    }

    /**
     * @param type $type set from
     *
     * @return type
     */
    public function setFrom()
    {
        $this->from = " fg_club AS fc LEFT JOIN fg_club_i18n fci18n ON (fci18n.id = fc.id AND fci18n.lang = '{$this->container->get('club')->get('default_lang')}') ";

        return $this->from;
    }

    /**
     * set condition
     */
    public function setCondition()
    {
        $this->where = "fc.is_deleted=0 AND fc.id IN(SELECT c.id FROM (SELECT sublevelClubs(id) AS id, @level AS level FROM (SELECT @start_with := '{$this->clubId}',@id := @start_with,@level := 0) vars, fg_club WHERE @id IS NOT NULL) ho JOIN fg_club c ON c.id = ho.id)";
    }

    /**
     * @param type $condition addcondition
     *
     * @return type
     */
    public function addCondition($condition = '')
    {
        if ($condition != '') {
            $this->where.= " AND (" . $condition . " )";
        }

        return $this->where;
    }

    /**
     * @return type getCondition
     */
    public function getCondition()
    {
        return $this->where;
    }

    /**
     * @param type $orderColumn addorderby
     *
     * @return type
     */
    public function addOrderBy($orderColumn = '')
    {

        if ($orderColumn != '') {
            $this->orderBy = " " . $orderColumn;
        }

        return $this->orderBy;
    }

    /**
     * @param type $limit set limit
     *
     * @return type
     */
    public function setLimit($limit = '')
    {
        if ($limit != '') {
            $this->limit = " LIMIT " . FgUtility::getSecuredData($limit, $this->clubadminConn);
        }

        return $this->limit;
    }

    /**
     * @param type $offset set offset
     */
    public function setOffset($offset = '')
    {

        if ($offset != '') {

            $this->offset = $offset;
        }
    }

    /**
     * @param type $jointext addjoin
     */
    public function addJoin($jointext)
    {

        $this->from.=$jointext;
    }

    /**
     * @return type getresult
     */
    public function getResult()
    {

        $this->result = "SELECT " . $this->selectionFields . " FROM " . $this->from;
        $this->result.= " WHERE " . $this->where;

        if ($this->addOrderBy() != '') {

            $this->result.= " ORDER BY " . $this->orderBy;
        }
        if ($this->setLimit() != '') {

            $this->result.= $this->limit;
        }
        if ($this->offset != '') {

            $this->result.="," . $this->offset;
        }

        return $this->result;
    }
}
