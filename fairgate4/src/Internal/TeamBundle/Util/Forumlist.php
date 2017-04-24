<?php

namespace Internal\TeamBundle\Util;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Common\UtilityBundle\Util\FgUtility;

/**
 * For set the contact fields.
 */
class Forumlist
{

    public $tableColumns = array();
    public $orderBy;
    public $where;
    public $from;
    public $limit;
    public $offset;
    public $result;
    public $clubId;
    public $contactId;
    private $container;
    public $groupBy;
    public $grouplistType;
    public $selectionFields = '';
    private $groupId, $conn, $club;
    public $isAdmin = false;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container   containre
     * @param type                                                      $contactId   contactId
     * @param type                                                      $club        object of club details
     * @param type                                                      $contactType type   of contact (archived, contact, formarfederation, both, all(all including sponsors), sponsor, archivedsponsor, allsponsor)
     */
    public function __construct(ContainerInterface $container, $contactId, $listType = 'team', $groupId)
    {

        $this->container = $container;
        $this->club = $this->container->get('club');
        $this->clubId = $this->club->get('id');
        $this->clubtype = $this->club->get('type');
        $this->where = '';
        $this->grouplistType = $listType;
        $this->contactId = $contactId;
        $this->conn = $this->container->get('database_connection');
        $this->groupId = $groupId;
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
    public function setColumns($columns, $searchFlag)
    {
        $this->tableColumns = $columns;
        switch ($this->clubtype) {
            case 'federation':
                $stealthtable = " master_federation_{$this->clubId}";

                break;
            case 'sub_federation':
                $stealthtable = " master_federation_{$this->clubId}";

                break;
            default:
                $stealthtable = " master_club_{$this->clubId}";
        }

        $columnfields = array();
        array_push($columnfields, "distinct(FT.id) AS forumId");
        array_push($columnfields, "FT.is_important AS isImportant");
        array_push($columnfields, "FT.is_closed AS isClosed");
        array_push($columnfields, ' (CASE WHEN FT.is_important=1 THEN 1 ELSE 2 END)  AS priority ');
        array_push($columnfields, "(SELECT count(DFTD.id) FROM fg_forum_topic_data AS DFTD WHERE DFTD.forum_topic_id=FT.id) AS totalReply");
        array_push($columnfields, "FT.replies AS isReply");
        array_push($columnfields, "IFNULL(FCD.is_notification_enabled,null) AS isFollow");
        array_push($columnfields, "(SELECT IF( (FFCD.read_at >= DFTD.created_at),1,0)  FROM fg_forum_topic_data AS DFTD LEFT JOIN fg_forum_contact_details AS FFCD ON FFCD.forum_topic_id= DFTD.forum_topic_id WHERE DFTD.forum_topic_id=FT.id AND FFCD.contact_id= {$this->contactId} order by DFTD.id DESC LIMIT 1) AS isRead");
        array_push($columnfields, "(SELECT LFTD.id FROM  fg_forum_topic_data AS LFTD  WHERE LFTD.forum_topic_id=FT.id ORDER BY LFTD.id DESC LIMIT 1) AS latestPost");
        //array_push($columnfields, "IF(((FT.is_closed=1 AND FT.created_by={$this->contactId}) OR (FT.is_closed=0)),1,0) AS isShow");

        if (is_array($columns) && count($columns) > 0) {
            foreach ($columns as $column) {
                switch ($column['id']) {
                    case "title":
                        $query = "FT.title AS {$column['name']}";
                        array_push($columnfields, $query);
                        break;
                    case "author":
                        $newfield = "contactNameNoSort(FT.created_by,0) AS {$column['name']}";
                        array_push($columnfields, $newfield);
                        array_push($columnfields, 'FT.created_at AS createdAt');
                        array_push($columnfields, 'FT.created_by AS createdAuthor');
                        array_push($columnfields, '( SELECT MC.is_stealth_mode FROM fg_cm_contact AS MC WHERE MC.id=FT.created_by) AS author_stealth');
                        array_push($columnfields, "(SELECT IF(SF.is_super_admin=1,1,0) AS ADMINUSER FROM fg_forum_topic AS SDFT LEFT JOIN sf_guard_user AS SF ON SF.contact_id=SDFT.created_by  WHERE SDFT.id=FT.id LIMIT 1) AS isAuthorSupAdmin");
                        array_push($columnfields, "(SELECT IF(fgc.is_fed_admin=1,1,0) AS isAuthorAdmin FROM fg_forum_topic AS SDFT LEFT JOIN fg_cm_contact fgc ON fgc.id=SDFT.created_by  WHERE SDFT.id=FT.id LIMIT 1)  AS isRepliedFedAdmin");

                        break;
                    case "replies":
                        $query = "(SELECT COUNT(DFTD.id) FROM fg_forum_topic_data AS DFTD WHERE DFTD.forum_topic_id=FT.id) AS {$column['name']}";
                        array_push($columnfields, $query);
                        break;
                    case "views":
                        $query = "FT.views AS {$column['name']}";
                        array_push($columnfields, $query);
                        break;
                    case "last_reply":
                        $query = "(SELECT  DFTD.created_at FROM fg_forum_topic_data AS DFTD WHERE DFTD.forum_topic_id=FT.id ORDER BY DFTD.created_at DESC LIMIT 1) AS {$column['name']}";
                        $contactnamequery = "(SELECT contactNameNoSort(DFTD.created_by, 0)FROM fg_forum_topic AS DFT INNER JOIN fg_forum_topic_data AS DFTD ON DFT.id=DFTD.forum_topic_id WHERE DFTD.forum_topic_id=FT.id ORDER BY DFTD.created_at DESC LIMIT 1) AS " . $column['name'] . "_CONTACT";
                        $contactIdquery = "(SELECT DFTD.created_by  FROM fg_forum_topic AS DFT INNER JOIN fg_forum_topic_data AS DFTD ON DFT.id=DFTD.forum_topic_id WHERE DFTD.forum_topic_id=FT.id ORDER BY DFTD.created_at DESC LIMIT 1) AS repliedUser ";
                        array_push($columnfields, $query);
                        array_push($columnfields, $contactnamequery);
                        array_push($columnfields, $contactIdquery);
                        array_push($columnfields, '(SELECT MC.is_stealth_mode  FROM fg_forum_topic AS DFT INNER JOIN fg_forum_topic_data AS DFTD ON DFT.id=DFTD.forum_topic_id INNER JOIN fg_cm_contact AS MC ON MC.id=DFTD.created_by WHERE DFTD.forum_topic_id=FT.id ORDER BY DFTD.created_at DESC LIMIT 1) AS replied_stealth');
                        array_push($columnfields, "(SELECT IF(SF.is_super_admin=1,1,0) AS USER FROM fg_forum_topic AS DFT INNER JOIN fg_forum_topic_data AS DFTD ON DFT.id=DFTD.forum_topic_id  LEFT JOIN sf_guard_user AS SF ON SF.contact_id=DFTD.created_by WHERE DFTD.forum_topic_id=FT.id  ORDER BY DFTD.created_at DESC LIMIT 1)  AS isRepliedUserSupAdmin");
                        array_push($columnfields, "(SELECT IF(fcc.is_fed_admin=1,1,0) AS isFedAdmin FROM fg_forum_topic AS DFT INNER JOIN fg_forum_topic_data AS DFTD ON DFT.id=DFTD.forum_topic_id  LEFT JOIN fg_cm_contact fcc ON fcc.id=DFTD.created_by WHERE DFTD.forum_topic_id=FT.id  ORDER BY DFTD.created_at DESC LIMIT 1)  AS isRepliedFedAdmin");
                        break;
                    case 'first_post_content':
                        $query = "(SELECT DFTD.post_content FROM fg_forum_topic_data AS DFTD WHERE DFTD.forum_topic_id=FT.id ORDER BY DFTD.id ASC LIMIT 1) AS {$column['name']}";
                        array_push($columnfields, $query);
                        break;
                    case 'post_content':
                        $query = "FTD.post_content AS {$column['name']}";
                        array_push($columnfields, $query);
                        break;
                    case 'created_date':
                        $query = "FT.created_at AS {$column['name']}";
                        array_push($columnfields, $query);
                        break;
                }
            }
            /* end dashboard* */
            $this->tableColumns = $columnfields;

            $this->selectionFields = implode(', ', $columnfields);
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
        $this->selectionFields = 'count(distinct FT.id) AS count';

        return $this->selectionFields;
    }

    /**
     * @param type $type
     *
     * @return type
     */
    public function setFrom($type = '')
    {

        $this->from = "fg_forum_topic AS FT INNER JOIN fg_forum_topic_data AS FTD ON FT.id = FTD.forum_topic_id AND FT.club_id='{$this->clubId}' LEFT JOIN fg_forum_contact_details AS FCD ON FCD.forum_topic_id=FT.id AND FCD.contact_id={$this->contactId} ";

        return $this->from;
    }

    /**
     * Set initial condition.
     */
    public function setCondition()
    {
        if (!$this->isAdmin) {
            $this->where = "FT.club_id={$this->clubId} AND FT.group_id={$this->groupId} AND ((FT.is_closed=1 AND FT.created_by={$this->contactId}) OR (FT.is_closed=0 OR FT.is_closed IS NULL))";
        } else {
            $this->where = "FT.club_id={$this->clubId} AND FT.group_id={$this->groupId}";
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
}
