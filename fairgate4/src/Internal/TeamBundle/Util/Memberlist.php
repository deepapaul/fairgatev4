<?php

namespace Internal\TeamBundle\Util;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Common\UtilityBundle\Util\FgUtility;

/**
 * For set the contact fields.
 */
class Memberlist
{

    public $tableColumns = array();
    public $systemFields = array();
    public $orderBy;
    public $where;
    public $from;
    public $limit;
    public $offset;
    public $result;
    public $selectionFields = '';
    public $clubId;
    public $contactId;
    private $container;
    public $groupBy;
    public $memberlistType;
    private $memberId, $membercategoryId, $conn, $club;
    public $isAdmin = false;
    private $mainContactField;
    public $editContact=0;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container   containre
     * @param type                                                      $contactId   contactId
     * @param type                                                      $club        object of club details
     * @param type                                                      $contactType type   of contact (archived, contact, formarfederation, both, all(all including sponsors), sponsor, archivedsponsor, allsponsor)
     */
    public function __construct(ContainerInterface $container, $contactId, $listType = 'team', $memberId, $memberCategoryId)
    {

        $this->container = $container;
        $this->club = $this->container->get('club');
        $this->clubId = $this->club->get('id');
        $this->clubtype = $this->club->get('type');
        $this->where = '';
        $this->memberlistType = $listType;
        $this->contactId = $contactId;
        $this->conn = $this->container->get('database_connection');
        $this->memberId = $memberId;
        $this->membercategoryId = $memberCategoryId;
        $this->systemFields = array_keys($this->club->get('systemFields'));
    }

    /**
     * @param type $column
     *
     * @return type
     */
    public function addtColumns($column)
    {
        $this->selectionFields = ',`' . $column . '`';

        return $this->selectionFields;
    }

    /**
     * @param type $columns fieldsarray
     *
     * @return type
     */
    public function setColumns($columns)
    {
        $this->tableColumns = $columns;
        $flag = 0;
        $firstfield = '';
        $key = 0;

        if (is_array($columns) && count($columns) > 0) {
            //concatanate two fields
            if (in_array('contactname', $columns)) {
                $key = array_search('contactname', $columns); //
                $firstname = '`' . $this->container->getParameter('system_field_firstname') . '`';
                $lastname = '`' . $this->container->getParameter('system_field_lastname') . '`';
                $newfield = 'contactName(fg_cm_contact.id) as contactname';
                $columns[$key] = $newfield;
                $flag = 1;
            }

            if (in_array('gender', $columns)) {
                $key = array_search('gender', $columns); //

                $columns[$key] = 'ms.`' . $this->container->getParameter('system_field_gender') . '` AS Gender';
            }
            if (in_array('isCompany', $columns)) {
                $key = array_search('isCompany', $columns); //

                $columns[$key] = 'fg_cm_contact.is_company AS Iscompany ';
            }
            if (in_array('contactclubid', $columns)) {
                $key = array_search('contactclubid', $columns); //

                $columns[$key] = 'fg_cm_contact.club_id AS contactclubid ';
            }
            if (in_array('isMember', $columns)) {
                $key = array_search('isMember', $columns); //

                $columns[$key] = '"ismember" AS Ismember';
            }
            //get tthe newly added flag
            array_push($columns, 'fg_cm_contact.is_draft AS newlyaddedFlag');
            //get tthe newly added flag
            array_push($columns, '(Select count(DFRC.id) FROM fg_rm_role_contact as DFRC  LEFT JOIN fg_rm_category_role_function DRF ON DRF.id=DFRC.fg_rm_crf_id WHERE DFRC.contact_id=fg_cm_contact.' . $this->mainContactField . ' AND DRF.role_id=' . $this->memberId . ' AND DFRC.is_removed=0 AND DFRC.assined_club_id=' . $this->clubId . ') AS removedFlag');
            //stealth mode
            array_push($columns, 'fg_cm_contact.is_stealth_mode  AS stealthFlag');
            array_push($columns, ' IF (fg_cm_contact.is_company = 1, `' . $this->container->getParameter('system_field_companylogo') . '`, `' . $this->container->getParameter('system_field_communitypicture') . "`)  AS Profilepic");
            if($this->editContact==1){
               array_push($columns, 'IFNULL((Select CTCT.confirm_status as confirm_status  FROM fg_cm_change_toconfirm  as CTCT WHERE  CTCT.contact_id=fg_cm_contact.' . $this->mainContactField . ' AND CTCT.role_id=' . $this->memberId . '  AND CTCT.type != "change"  order by confirm_status asc  limit 0,1  ) ,"CONFIRMED") AS confirm_status');
           }
            
            /* end dashboard* */
            $this->tableColumns = $columns;

            if ($flag == 1) {
                $firstfield = $columns[$key];
                unset($columns[$key]);
            }
            $this->selectionFields = implode(',', $columns);
            // for remove the contactname field from the actual table column
            if ($flag == 1) {
                $this->selectionFields = $firstfield . ',' . $this->selectionFields;
            }
            $this->selectionFields = str_replace('clubId', 'fg_cm_contact.club_id as clubId', $this->selectionFields);
            $this->selectionFields = str_replace('contactid', 'fg_cm_contact.id', $this->selectionFields);
            $this->selectionFields = ' distinct fg_cm_contact.id,' . $this->selectionFields;
        } else {
            $this->selectionFields = '*';
        }

        return $this->selectionFields;
    }

    /**
     * @return type
     */
    public function setCount()
    {
        $this->selectionFields = 'count(distinct fg_cm_contact.id) as count';

        return $this->selectionFields;
    }

    /**
     * @param type $type
     *
     * @return type
     */
    public function setFrom($type = '')
    {
        //set the from table using clubtype
        $this->mainContactField = 'id';
        switch ($this->clubtype) {
            case 'federation':
                $this->from = " master_federation_{$this->clubId} as mc INNER JOIN fg_cm_contact on mc.fed_contact_id = fg_cm_contact.fed_contact_id";
                $this->mainContactField = 'fed_contact_id';
                break;
            case 'sub_federation':
                $this->from = "master_federation_{$this->clubId} as mc INNER JOIN fg_cm_contact on mc.contact_id = fg_cm_contact.subfed_contact_id";
                $this->mainContactField = 'subfed_contact_id';
                break;
            default:
                $this->from = "master_club_{$this->clubId} as mc INNER JOIN fg_cm_contact on mc.contact_id = fg_cm_contact.id";
        }
        $clubHeirarchy = $this->container->get('club')->get('clubHeirarchyDet');
        if (count($clubHeirarchy) > 0) {
            foreach ($clubHeirarchy as $clubid => $heirarchy) {

                $this->from.=($heirarchy['club_type'] == 'federation') ? " LEFT JOIN master_federation_" . $clubid . " as mf ON (mf.fed_contact_id = fg_cm_contact.fed_contact_id )" : " LEFT JOIN master_federation_" . $clubid . " as msf on (msf.contact_id =  fg_cm_contact.subfed_contact_id )";
            }
        }
        $this->from .= ' INNER JOIN master_system as ms on ms.fed_contact_id = fg_cm_contact.fed_contact_id LEFT JOIN fg_rm_role_contact FRC ON FRC.contact_id=fg_cm_contact.' . $this->mainContactField . ' AND FRC.assined_club_id=' . $this->clubId;
        
        return $this->from;
    }

    /**
     * Set initial condition.
     */
    public function setCondition()
    {
        $permenantDelete = ($this->contactType == 'formerfederationmember') ? "1" : "fg_cm_contact.is_permanent_delete=0";
        if ($this->clubtype == 'federation' || $this->clubtype == 'sub_federation') {
            //check if contact has approved fed membership
            $fedmemberApprovedConditions = ($this->confirmedFlag == true) ? " AND (fg_cm_contact.is_fed_membership_confirmed='0' OR (fg_cm_contact.is_fed_membership_confirmed='1' AND fg_cm_contact.old_fed_membership_id IS NOT NULL))" : "";
            $this->where = ($this->contactType != 'formerfederationmember') ? $permenantDelete . " AND fg_cm_contact.club_id={$this->clubId} AND (fg_cm_contact.main_club_id={$this->clubId} OR fg_cm_contact.fed_membership_cat_id IS NOT NULL){$fedmemberApprovedConditions}" : "";
        } else {
            $this->where = $permenantDelete . "  AND fg_cm_contact.club_id={$this->clubId}";
        }
        if ($this->memberlistType == 'team') {
            $this->teamfilter();
        } elseif ($this->memberlistType == 'workgroup') {
            $this->workgroupfilter();
        }
    }

    /**
     * @param type $condition
     *
     * @return type
     */
    public function addCondition($condition = '')
    {
        if ($condition != '') {
            $this->where .= ' AND (' . $condition . ' )';
        }

        return $this->where;
    }

    /**
     * @param type $condition
     *
     * @return type
     */
    public function orCondition($condition = '')
    {
        if ($condition != '') {
            $this->where .= ' OR (' . $condition . ' )';
        }
        $this->where = '(' . $this->where . ')';

        return $this->where;
    }

    /**
     * @return condition
     */
    public function getCondition()
    {
        return $this->where;
    }

    /**
     * @param type $orderColumn column order
     *
     * @return type
     */
    public function addOrderBy($orderColumn = '')
    {
        if ($orderColumn != '') {
            $this->orderBy = ' ' . $orderColumn;
        }

        return $this->orderBy;
    }

    /**
     * @param type $limit
     *
     * @return type
     */
    public function setLimit($limit = '')
    {
        if ($limit != '') {
            $this->limit = ' limit ' . FgUtility::getSecuredData($limit, $this->conn);
        }

        return $this->limit;
    }

    /**
     * @param type $offset offset
     */
    public function setOffset($offset = '')
    {
        if ($offset != '') {
            $this->offset = $offset;
        }
    }

    /**
     * @return groupby
     */
    public function setGroupBy($col = '')
    {
        if ($col != '') {
            $this->groupBy = $col;
        }

        return $this->groupBy;
    }

    /**
     * @param type $jointext join text
     */
    public function addJoin($jointext)
    {
        $this->from .= $jointext;
    }

    /**
     * @return result
     */
    public function getResult()
    {
        $this->result = 'SELECT ' . $this->selectionFields . ' FROM ' . $this->from;
        $this->result .= ' WHERE ' . $this->where;
        if ($this->setGroupBy() != '') {
            $this->result .= ' GROUP BY ' . $this->groupBy;
        }
        if ($this->addOrderBy() != '') {
            $this->result .= ' ORDER BY' . $this->orderBy;
        }
        if ($this->setLimit() != '') {
            $this->result .= $this->limit;
        }
        if ($this->offset != '') {
            $this->result .= ',' . $this->offset;
        }

        return $this->result;
    }

    /**
     * For create team where condition.
     */
    private function teamfilter()
    {
        $changesToConfirmQuery = '';
        $where = array();
        $where[] = "dcrf.role_id = '{$this->memberId}'";
        $where[] = "dcrf.category_id = '" . $this->club->get('club_team_id') . "'";
        if (!$this->isAdmin) {
            $where[] = "drc.is_removed = 0";
        }
        $subQuery = '(SELECT drc.contact_id FROM fg_rm_role AS drr INNER JOIN fg_rm_category_role_function AS dcrf ON drr.id = dcrf.role_id  INNER JOIN fg_rm_role_contact AS drc ON dcrf.id=drc.fg_rm_crf_id WHERE (' . implode(' AND ', $where) . '))';
        if ($this->isAdmin) {
            $changesToConfirmQuery = " OR (fg_cm_contact.id  IN (SELECT ctc.contact_id FROM fg_cm_change_toconfirm AS ctc WHERE ctc.role_id = '{$this->memberId}' AND ctc.type != 'change' AND ctc.confirm_status = 'NONE'))";
        }
        $this->where .= ' AND (fg_cm_contact.id  IN ' . $subQuery . $changesToConfirmQuery . ')';
    }

    /**
     * For create the workgroup where condition.
     */
    private function workgroupfilter()
    {

        $changesToConfirmQuery = '';
        $where = array();
        $where[] = "dcrf.category_id = '" . $this->club->get('club_workgroup_id') . "'";
        $where[] = "dcrf.role_id = '{$this->memberId}'";
        $where[] = "drc.assined_club_id = '" . $this->club->get('id') . "'";
        if (!$this->isAdmin) {
            $where[] = "drc.is_removed = 0";
        }
        $subQuery = '(SELECT drc.contact_id FROM fg_rm_category_role_function AS dcrf INNER JOIN fg_rm_role_contact AS drc ON dcrf.id=drc.fg_rm_crf_id WHERE (' . implode(' AND ', $where) . '))';

        if ($this->isAdmin) {
            $changesToConfirmQuery = " OR (fg_cm_contact.id  IN (SELECT ctc.contact_id FROM fg_cm_change_toconfirm AS ctc WHERE ctc.role_id = '{$this->memberId}' AND ctc.type != 'change' AND ctc.confirm_status = 'NONE'))";
        }
        $this->where .= ' AND (fg_cm_contact.id  IN ' . $subQuery . $changesToConfirmQuery . ')';
    }
}
