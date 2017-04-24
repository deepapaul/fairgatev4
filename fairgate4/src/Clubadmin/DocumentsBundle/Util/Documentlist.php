<?php

namespace Clubadmin\DocumentsBundle\Util;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Common\UtilityBundle\Util\FgUtility;
use Internal\GeneralBundle\Util\InternalDocumentList;
/**
 * For create document query
 */
class Documentlist {

    private $tableColumns = array();
    private $tablejoin;
    private $orderBy;
    private $where;
    private $from;
    private $limit;
    private $offset;
    private $result;
    private $selectionFields = '';
    public $clubId;
    public $clubtype;
    private $container;
    private $groupBy;
    private $contactType;
    private $conn;
    private $club;
    private $contact;
    private $documentationType;
    public $clubHeirarchy;
    public $onlyHeirarchy = false;
    public $contactId; 
    public $adminRoleIds = '';
    public $memberRoleIds = '';

    public function __construct(ContainerInterface $container, $documentType = 'CLUB') {
        $this->container = $container;
        $this->club = $this->container->get('club');
        $this->clubId = $this->club->get("id");
        $this->clubtype = $this->club->get("type");
        $this->where = '';
        $this->conn = $this->container->get('database_connection');
        $this->documentationType = $documentType;
        $this->clubHeirarchy = $this->club->get('clubHeirarchy');
        $this->contact = $this->container->get('contact');
        $this->contactId = $this->contact->get('id');
    }

    public function setFrom() {
        $this->from = "fg_dm_documents AS fdd INNER JOIN fg_dm_version  on fdd.current_revision = fg_dm_version.id LEFT JOIN fg_dm_documents_i18n AS fdi18 ON fdi18.id=fdd.id AND  fdi18.lang='{$this->club->get("default_lang")}'";
    }

    public function getFrom() {
        return $this->from;
    }

    public function setColumns($columns) {
        $key = 0;

        if (is_array($columns) && count($columns) > 0) {
            if (in_array('docname', $columns)) {
                $key = array_search('docname', $columns);
                $columns[$key] = " IF(fdi18.name_lang IS NULL OR fdi18.name_lang ='' , fdd.name, fdi18.name_lang)  AS docname";
            }
        }
        $this->selectionFields = implode(",", $columns);
        $this->tableColumns = $columns;
    }

    public function setCount() {
        $this->selectionFields = "count(fdd.id) as count";

        return $this->selectionFields;
    }

    /**
     * Set initial condition
     */
    public function setCondition() {
        switch ($this->documentationType) {
            case 'CLUB':
                if ($this->clubtype == 'federation_club' || $this->clubtype == 'sub_federation' || $this->clubtype == 'sub_federation_club') {
                    $clubHeirarchy = implode(",", $this->clubHeirarchy);
                    $onlyHeirarchyCondition = ($this->onlyHeirarchy ? '' : 'fdd.club_id ='.$this->clubId.' OR');
                    $this->where = ' fdd.document_type="'.$this->documentationType.'" AND  ( '.$onlyHeirarchyCondition.' ( (fdd.club_id IN ('.$clubHeirarchy.')) AND ( (fdd.deposited_with="ALL" AND (fdd.id NOT IN (SELECT CDD.id FROM fg_dm_documents CDD JOIN fg_dm_assigment_exclude CDA ON CDA.document_id=CDD.id WHERE CDA.club_id='.$this->clubId.'))) OR (fdd.id IN (SELECT CDD.id FROM fg_dm_documents CDD JOIN fg_dm_assigment CDA ON CDA.document_id=CDD.id WHERE CDA.club_id='.$this->clubId.') ))))';
                } else {
                    $this->where.= " fdd.club_id =" . $this->clubId." AND fdd.document_type='".$this->documentationType."'";
                }
                break;
            case 'CONTACT': case 'TEAM': case 'WORKGROUP':
                        $this->where.= " fdd.club_id =" . $this->clubId." AND fdd.document_type='".$this->documentationType."'" ;
                break;
            case 'ALL':
                $this->where.= " fdd.club_id =" . $this->clubId." " ;
                break;
        }
    }
    
    public function setCountForInternal()
    {
        $this->selectionFields = "count(DISTINCT fdd.id) as count";

        return $this->selectionFields;
    }
    
    public function setFromForInternal()
    {
        $this->from = "fg_dm_documents AS fdd INNER JOIN fg_dm_version AS fdv on fdd.current_revision = fdv.id LEFT JOIN fg_dm_documents_i18n AS fdi18 ON fdi18.id=fdd.id AND fdi18.lang='{$this->club->get("default_lang")}'";
        $this->from .= " LEFT JOIN fg_dm_assigment fda ON fda.document_id = fdd.id";
        $this->from .= " LEFT JOIN fg_dm_assigment_exclude fdae ON fdae.document_id = fdd.id LEFT JOIN fg_dm_team_functions fdtf ON fdtf.document_id = fdd.id LEFT JOIN fg_dm_contact_sighted AS fdcs ON (fdcs.document_id = fdd.id AND fdcs.contact_id = {$this->contactId})";
    }
    
    public function setFromForRoles()
    {
        $this->from = "fg_dm_documents AS fdd INNER JOIN fg_dm_version AS fdv on fdd.current_revision = fdv.id LEFT JOIN fg_dm_documents_i18n AS fdi18 ON fdi18.id=fdd.id AND fdi18.lang='{$this->club->get("default_lang")}'";
        $roleIds = implode(',', array_merge($this->adminRoleIds, $this->memberRoleIds));
        if (count($roleIds) > 0) {
            $this->from .= " LEFT JOIN fg_dm_assigment fda ON (fda.document_id = fdd.id AND fda.role_id IN (" . $roleIds . ")) LEFT JOIN fg_dm_team_functions fdtf ON fdtf.document_id = fdd.id LEFT JOIN fg_dm_contact_sighted AS fdcs ON (fdcs.document_id = fdd.id AND fdcs.contact_id = {$this->contactId})";
            $this->from .= " LEFT JOIN fg_rm_role frm ON (IF(fdd.deposited_with = 'ALL', frm.id IN (" . $roleIds . "), (frm.id IN (" . $roleIds . ") AND frm.id = fda.role_id)))"; 
        }
    }
    
    public function setColumnsForInternal($aColumns = array())
    {
        if (count($aColumns) > 0) {
            $this->selectionFields = implode(",", $aColumns);
        } else {
            $this->selectionFields = 'fdd.id';
        }
    }
    
    /**
     * Set initial condition to handle visibility conditions from internal
     * 
     * @param string $documentPage personal/team/workgroup
     * @param int    $roleId
     */
    public function setConditionForInternal($documentPage = 'personal', $roleId = '')
    {
        switch ($documentPage) {
            case 'personal':
                $internalDocumentlist = new InternalDocumentList($this->container);
                $this->where = $internalDocumentlist->getPersonalOverviewCondition();
                $adminTeamIds = $internalDocumentlist->getMyAdminRoleIds('TEAM');
                $memberTeamIds = $internalDocumentlist->getMyMemberRoleIds('TEAM');
                $adminWorkgroupIds = $internalDocumentlist->getMyAdminRoleIds('WORKGROUP');
                $memberWorkgroupIds = $internalDocumentlist->getMyMemberRoleIds('WORKGROUP');
                $this->adminRoleIds = array_merge($adminTeamIds, $adminWorkgroupIds);
                $this->memberRoleIds = array_merge($memberTeamIds, $memberWorkgroupIds);
                break;
            
            case 'team':
                $internalDocumentlist = new InternalDocumentList($this->container, 'TEAM', $roleId);
                $this->where = $internalDocumentlist->getTeamOverviewCondition();
                $this->adminRoleIds = $internalDocumentlist->getMyAdminRoleIds('TEAM');
                $this->memberRoleIds = $internalDocumentlist->getMyMemberRoleIds('TEAM');
                break;
            
            case 'workgroup':
                $internalDocumentlist = new InternalDocumentList($this->container, 'WORKGROUP', $roleId);
                $this->where = $internalDocumentlist->getWorkgroupOverviewCondition();
                $this->adminRoleIds = $internalDocumentlist->getMyAdminRoleIds('WORKGROUP');
                $this->memberRoleIds = $internalDocumentlist->getMyMemberRoleIds('WORKGROUP');
                break;
            
            case 'teamTopNavCount':
                $internalDocumentlist = new InternalDocumentList($this->container);
                $this->where = $internalDocumentlist->getTeamOverviewCondition();
                $this->adminRoleIds = $internalDocumentlist->getMyAdminRoleIds('TEAM');
                $this->memberRoleIds = $internalDocumentlist->getMyMemberRoleIds('TEAM');
                break;
            
            case 'workgroupTopNavCount':
                $internalDocumentlist = new InternalDocumentList($this->container);
                $this->where = $internalDocumentlist->getWorkgroupOverviewCondition();
                $this->adminRoleIds = $internalDocumentlist->getMyAdminRoleIds('WORKGROUP');
                $this->memberRoleIds = $internalDocumentlist->getMyMemberRoleIds('WORKGROUP');
                break;
            
            case 'teamTabCount':
                $internalDocumentlist = new InternalDocumentList($this->container, 'ALL', '', true);
                $this->where = $internalDocumentlist->getTeamOverviewCondition();
                $this->adminRoleIds = $internalDocumentlist->getMyAdminRoleIds('TEAM');
                $this->memberRoleIds = $internalDocumentlist->getMyMemberRoleIds('TEAM');
                break;
            
            case 'workgroupTabCount':
                $internalDocumentlist = new InternalDocumentList($this->container, 'ALL', '', true);
                $this->where = $internalDocumentlist->getWorkgroupOverviewCondition();
                $this->adminRoleIds = $internalDocumentlist->getMyAdminRoleIds('WORKGROUP');
                $this->memberRoleIds = $internalDocumentlist->getMyMemberRoleIds('WORKGROUP');
                break;
            
            default:
                break;
        }
    }
    
    /**
     * @param type $condition
     *
     * @return string where condition
     */
    public function addCondition($condition = '') {
        if ($condition != '') {
            $this->where.= " AND (" . $condition . " )";
        }

        return $this->where;
    }

    /**
     * @param type $condition
     *
     * @return String where condition
     */
    public function orCondition($condition = '') {
        if ($condition != '') {
            $this->where.= " OR (" . $condition . " )";
        }
        $this->where = '(' . $this->where . ')';

        return $this->where;
    }

    /**
     * @return condition
     */
    public function getCondition() {


        return $this->where;
    }

    /**
     * @param type $orderColumn column order
     *
     * @return type
     */
    public function addOrderBy($orderColumn = '') {

        if ($orderColumn != '') {
            $this->orderBy = " " . $orderColumn;
        }

        return $this->orderBy;
    }

    /**
     * @param type $limit
     *
     * @return type
     */
    public function setLimit($limit = '') {

        if ($limit != '') {
            $this->limit = " LIMIT " . FgUtility::getSecuredData($limit, $this->conn);
        }

        return $this->limit;
    }

    /**
     * @param type $offset offset
     */
    public function setOffset($offset = '') {

        if ($offset != '') {
            $this->offset = $offset;
        }
    }

    /**
     * @return groupby
     */
    public function setGroupBy($col = '') {
        if ($col != '') {
            $this->groupBy = $col;
        }
        return $this->groupBy;
    }

    /**
     * @param type $jointext join text
     */
    public function addJoin($jointext) {

        $this->from.=$jointext;
    }

    /**
     * @return result
     */
    public function getResult() {

        $this->result = "SELECT " . $this->selectionFields . " FROM " . $this->from;
        $this->result.= " WHERE " . $this->where;
        if ($this->setGroupBy() != '') {
            $this->result.= " GROUP BY " . $this->groupBy;
        }
        if ($this->addOrderBy() != '') {
            $this->result.= " ORDER BY" . $this->orderBy;
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