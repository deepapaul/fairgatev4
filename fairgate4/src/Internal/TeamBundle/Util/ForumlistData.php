<?php

namespace Internal\TeamBundle\Util;

use Common\UtilityBundle\Util\FgUtility;

/**
 * Used to handling different  contact list functions.
 *
 * @author  pitsolutions.ch <pit@solutions.com>
 *
 * @version Release: <v4>
 */
class ForumlistData
{

    /**
     * $em.
     *
     * @var object entitymanager object
     */
    private $em;
    private $contactId;
    private $clubId;

    /**
     * $club.
     *
     * @var Service {clubservice}
     */
    private $club;
    private $container;
    private $session;

    /**
     * json decoded table column array.
     *
     * @var array
     */
    public $tabledata = array();
    public $sColumns = array();
    public $startValue = '';
    public $groupByColumn = '';

    /**
     * direct setting of sorting details as a string.
     *
     * @var String
     */
    public $sortColumnDetails = '';

    /**
     * column field array.
     *
     * @var array
     */
    public $aoColumns = array();
    public $filterValue = '';

    /**
     * Contains the detail to create the sorting.
     *
     * @var array
     */
    public $sortColumnValue;

    /**
     * json array values.
     *
     * @var string
     */
    public $tableFieldValues = '';
    public $displayLength = '10';
    public $groupId;
    public $grouplistType = 'team';
    public $adminFlag;

    /**
     * Default system language.
     *
     * @var String
     */
    public $defaultSystemLang = '';

    /**
     * search value.
     *
     * @var string
     */
    public $searchVal = '';
    public $searchFlag = 0;
    public $cColumns = array();

    /**
     * sort field identifier.
     *
     * @var type
     */
    public $sortname;

    /**
     *
     * @var int 
     */
    public $conn;

    /**
     * Constructor for initial setting.
     *
     * @param type $contactId   contact   id
     * @param type $container   container
     * @param type $contactType (active/archive/formerfederationmember)
     */
    public function __construct($container, $contactId)
    {
        $this->contactId = $contactId;
        $this->container = $container;
        $this->club = $this->container->get('club');
        $this->em = $this->container->get('doctrine')->getManager();
        $this->session = $this->container->get('session');
        $this->clubId = $this->club->get('id');
        $this->conn = $this->container->get('database_connection');
    }

    /**
     * To create list query according to the settings and give result.
     *
     * @param type $isQuery       Return query flag
     * @param type $nextPreFlag   Next previous flag
     * @param type $currentOffset Current offset value of contact
     *
     * @return array
     */
    public function getForumlistData()
    {
        //to initialize contact list class
        $forumlistClass = $this->initializeMemberList();
        $totallistquery = $forumlistClass->getResult();
        $totaltopiclistDatas = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($totallistquery);
        //pagination handling area
        if ($this->searchFlag) {
            $sortColumnValue = ' createdAt DESC';
        } else {
            $sortColumnValue = ' priority ASC';
        }

        if ($this->sortColumnValue != '') {
            $sortvalue = $this->getSortColumn($this->sortColumnValue);
            if ($sortvalue != '') {
                $sortColumnValue .= ',' . $sortvalue;
            }
        }

        $forumlistClass->addOrderBy($sortColumnValue);
        $this->session->set($this->grouplistType . '-sort-order' . $this->contactId . $this->clubId, $sortColumnValue);

        if ($this->startValue != '') {
            $forumlistClass->setLimit($this->startValue);
            $forumlistClass->setOffset($this->displayLength);
        }
        //direct value setting to order by condition

        if ($this->groupByColumn != '') {
            $forumlistClass->setGroupBy($this->groupByColumn);
        }
//        if ($this->searchVal != '') {
//            $sWhere = $this->setSearchFields();
//            $forumlistClass->addCondition($sWhere);
//        }
        if ($this->sortColumnDetails != '') {
            $forumlistClass->addOrderBy($this->sortColumnDetails);
        }
        $forumlistClass->setColumns($this->aoColumns, $this->searchFlag);
        //call query for collect the data
        $listquery = $forumlistClass->getResult();

        file_put_contents('query.txt', $listquery . "\n");

        $results = array('totalcount' => 0, 'data' => '');

        $results['data'] = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($listquery);
        if (is_array($totaltopiclistDatas) && count($totaltopiclistDatas) > 0) {
            $results['totalcount'] = $totaltopiclistDatas[0]['count'];
        }

        return $results;
    }

    /**
     * To initialize the contact class.
     *
     * @return \Clubadmin\Classes\contactlist
     */
    public function initializeMemberList()
    {
        $forumlistClass = new Forumlist($this->container, $this->contactId, $this->grouplistType, $this->groupId);
        $forumlistClass->isAdmin = $this->adminFlag;
        $forumlistClass->setCount();
        $forumlistClass->setFrom();
        $forumlistClass->setCondition();
        if ($this->searchVal != '') {
            $sWhere = $this->setSearchFields();
            $forumlistClass->addCondition($sWhere);
        }

        return $forumlistClass;
    }

    /**
     * To creating the serach field condition from the search value setting.
     *
     * @return string
     */
    public function setSearchFields()
    {
        $this->sColumns = array(
            '0' => 'title',
            '1' => 'post_content'
        );

        $searchArray = explode(' ', $this->searchVal);
        foreach ($searchArray as $searchTerm) {
            $sWhere .= '( ';
            foreach ($this->sColumns as $column) {
                $addslash = "";
                //handle wildcard
                if ($searchTerm == '_') {
                    $addslash = "\\";
                }
                $searchTerm = FgUtility::getSecuredDataString($searchTerm, $this->conn);
                $sWhere .= $column . " LIKE '%{$addslash}" . $searchTerm . "%'  OR ";
            }
            $sWhere = substr_replace($sWhere, '', -3);
            $sWhere .= " ) AND ";
        }
        $sWhere = substr_replace($sWhere, '', -4);

        return $sWhere;
    }

    /**
     * Function to identify the sortcolumn.
     *
     * @param type $sortcolumnIdentifier
     *
     * @return string
     */
    private function getSortColumn($sortcolumnIdentifier)
    {
        $sortvalue = '';
        switch ($sortcolumnIdentifier[0]['column']) {
            case 1:
                $sortvalue = 'lastReply DESC';
                break;
            case 2:
                $sortvalue = 'createdDate DESC';
                break;
            case 3:
                $sortvalue = 'replies DESC';
                break;
            case 4:
                $sortvalue = 'views DESC';
                break;
        }

        return $sortvalue;
    }
}
