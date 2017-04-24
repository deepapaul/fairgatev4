<?php

/**
 * This class is used to get the base query of all articles that the logged in user can see
 * based on his userrights, club and selected filters.
 */
namespace Internal\ArticleBundle\Util;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Repository\Pdo\UtilPdo;

/**
 * This class is used to get the base query of all articles that the logged in user can see
 * based on his userrights, club and selected filters.
 * 
 * @package 	Internal
 * @subpackage 	Article
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 * 
 */
class ArticlesList
{

    /**
     * The container object
     * 
     * @var object 
     */
    private $container;

    /**
     * The club object
     * 
     * @var object 
     */
    private $club;

    /**
     * The contact object
     * 
     * @var object 
     */
    private $contact;

    /**
     * The club id
     * 
     * @var int 
     */
    private $clubId;

    /**
     * The contact id
     * 
     * @var int 
     */
    private $contactId;

    /**
     * The selection query part
     * 
     * @var string 
     */
    public $selectionFields = '';

    /**
     * The sidebar filter data
     * 
     * @var array 
     */
    public $tableColumns = array();

    /**
     * The from query part
     * 
     * @var string 
     */
    public $from;

    /**
     * The where string
     * 
     * @var string 
     */
    public $where = '';

    /**
     * The result string
     * 
     * @var string 
     */
    public $result;

    /**
     * The group by query part
     * 
     * @var string  
     */
    public $groupBy = '';

    /**
     * The order by query part
     * 
     * @var string  
     */
    public $orderBy = '';

    /**
     * The having query part
     * 
     * @var array 
     */
    public $having = array();

    /**
     * The connection object
     * 
     * @var object 
     */
    public $conn;

    /**
     * The column data
     * 
     * @var string  
     */
    public $columnData;

    /**
     * The public flag
     * 
     * @var boolean 
     */
    public $isPublic;

    /**
     * Constructor function: Will set the core variables.
     *
     * @param object $container ContainerInterface Container
     * @param String $type      {editorial/article}
     */
    public function __construct(ContainerInterface $container, $type = 'editorial', $isPublic = false)
    {
        $this->container = $container;
        $this->club = $this->container->get('club');
        $this->contact = $this->container->get('contact');
        $this->clubId = $this->club->get('id');
        $this->contactId = $this->contact->get('id');
        $this->conn = $this->container->get('database_connection');
        $this->type = $type;
        $this->terminologyService = $this->container->get('fairgate_terminology_service');
        $this->em = $this->container->get('doctrine')->getManager();

        if ($this->type == 'editorial') {
            $this->defaultlanguage = $this->club->get('club_default_lang');
        } else {
            $this->defaultlanguage = $this->club->get('default_lang');
        }

        $this->selectColumnArray = array();
        $this->joinTablesArray = array();
        $this->now = 'NOW()';
        //isPublic flag is used to check whether it is used for public page or not
        $this->isPublic = $isPublic;
    }

    /**
     * The function that will set the columns that need to be returned.
     */
    public function setColumnData()
    {
        $columnArray = $this->columnData;
        $selectColumnArray[] = 'A.id AS articleId';
        $selectColumnArray[] = 'COALESCE(NULLIF(A_TEXTi18n.title_lang, ""), A_TEXT.title) AS title';
        $selectColumnArray[] = "CASE WHEN( expiry_date <= $this->now) THEN 'archived'  WHEN (is_draft = 1) THEN 'draft' WHEN (publication_date <= $this->now) THEN 'published' WHEN (publication_date > $this->now) THEN 'planned' ELSE '' END AS STATUS";
        $selectColumnArray[] = 'A.expiry_date AS F_ARCHIVING_DATE';
        $selectColumnArray[] = 'contactName(A.archived_by) AS ARCHIVED_BY';
        $selectColumnArray[] = 'A.archived_by AS ARCHIVED_BY_ID';
        $selectColumnArray[] = "checkActiveContact(A.archived_by, $this->clubId) AS ARCHIVED_BY_ACTIVE";
        $selectColumnArray[] = 'A.club_id AS club_id';

        $termExecutive = ucfirst($this->terminologyService->getTerminology('Executive Board', $this->container->getParameter('singular')));

        foreach ($columnArray as $column) {
            switch ($column['id']) {
                case 'PUBLICATION_DATE':
                    $selectColumnArray[] = 'A.publication_date AS PUBLICATION_DATE';
                    break;
                case 'ARCHIVING_DATE':
                    $selectColumnArray[] = 'A.expiry_date AS ARCHIVING_DATE';
                    break;
                case 'SCOPE':
                    $selectColumnArray[] = "(CASE WHEN (scope = 'PUBLIC') THEN 'PUBLIC' WHEN (scope = 'INTERNAL') THEN 'INTERNAL' END) AS SCOPE";
                    break;
                case 'AREAS':
                    $selectColumnArray[] = "(SELECT GROUP_CONCAT((CASE WHEN R.is_executive_board=1 THEN '" . $termExecutive . "' ELSE COALESCE(NULLIF(RI18N.title_lang, ''), R.title) END) ORDER BY RC.sort_order,TC.sort_order,R.sort_order SEPARATOR '*##*') AS roleTitle
                                            FROM fg_cms_article_selectedareas A_SEL_AREA
                                            LEFT JOIN fg_rm_role R ON A_SEL_AREA.role_id = R.id
                                            LEFT JOIN fg_team_category AS TC ON R.team_category_id = TC.id
                                            LEFT JOIN fg_rm_category AS RC ON RC.id = R.category_id
                                            LEFT JOIN fg_rm_role_i18n AS RI18N ON RI18N.id = R.id AND RI18N.lang = '$this->defaultlanguage'
                                            WHERE A_SEL_AREA.article_id = A.id AND R.is_active = 1 GROUP BY A.id) AS AREAS";
                    break;
                case 'CATEGORIES':
                    $selectColumnArray[] = "(SELECT GROUP_CONCAT(COALESCE(NULLIF(AC_TEXTi18n.title_lang, ''), AC.title) ORDER BY AC.sort_order SEPARATOR '*##*') AS categoryTitle
                                            FROM fg_cms_article_selectedcategories A_SEL_CAT
                                            LEFT JOIN fg_cms_article_category AC ON AC.id = A_SEL_CAT.category_id
                                            LEFT JOIN fg_cms_article_category_i18n AC_TEXTi18n ON AC_TEXTi18n.id = AC.id AND AC_TEXTi18n.lang = '$this->defaultlanguage'
                                            WHERE A_SEL_CAT.article_id = A.id GROUP BY A.id) AS CATEGORIES ";
                    break;
                case 'AUTHOR':
                    $selectColumnArray[] = 'A.author AS AUTHOR';
                    break;
                case 'CREATED_AT':
                    $selectColumnArray[] = 'A.created_on AS CREATED_AT';
                    break;
                case 'CREATED_BY':
                    $selectColumnArray[] = 'A.created_by AS CREATED_BY_ID';
                    $selectColumnArray[] = 'contactName(A.created_by) AS CREATED_BY';
                    $selectColumnArray[] = "checkActiveContact(A.created_by, $this->clubId) AS CREATED_BY_ACTIVE";
                    break;
                case 'EDITED_AT':
                    $selectColumnArray[] = 'A.updated_on AS EDITED_AT';
                    break;
                case 'EDITED_BY':
                    $selectColumnArray[] = 'A.updated_by AS EDITED_BY_ID';
                    $selectColumnArray[] = 'contactName(A.updated_by) AS EDITED_BY';
                    $selectColumnArray[] = "checkActiveContact(A.updated_by, $this->clubId) AS EDITED_BY_ACTIVE";
                    break;
                case 'IMAGE_VIDEOS':
                    $selectColumnArray[] = 'COUNT(DISTINCT(A_SEL_MEDIA.id)) AS IMAGE_VIDEOS';
                    break;
                case 'COMMENTS':
                    $selectColumnArray[] = 'COUNT(DISTINCT(A_COMM.id)) AS COMMENTS';
                    break;
                case 'LANGUAGES':
                    $selectColumnArray[] = "UPPER(GROUP_CONCAT( DISTINCT(A_TEXTi18n2.lang) SEPARATOR ', ')) AS LANGUAGES";
                    break;
            }
        }
        $selectColumnArray[] = '(SELECT COUNT(A_SEL_AREA.id) AS isClub FROM fg_cms_article_selectedareas A_SEL_AREA  WHERE A_SEL_AREA.is_club = 1 AND A_SEL_AREA.article_id = A.id GROUP BY A.id) AS isClub';

        if ($this->type == 'article') {
            $selectColumnArray[] = '(CASE WHEN A_TEXTi18n.title_lang IS NOT NULL THEN 1 ELSE 0 END) AS hasLanguage';
            $selectColumnArray[] = 'COALESCE(NULLIF(A_TEXTi18n.teaser_lang, ""), A_TEXT.teaser) AS teaser';
            $selectColumnArray[] = 'COALESCE(NULLIF(A_TEXTi18n.text_lang, ""), A_TEXT.text) AS text';

            $itemCondition = ($this->isPublic) ? "AND ITEM.scope ='PUBLIC'" : '';
            $selectColumnArray[] = "(SELECT ITEM.filepath FROM fg_cms_article_media A_MEDIA INNER JOIN fg_gm_items ITEM ON ITEM.id = A_MEDIA.items_id AND ITEM.type = 'IMAGE' $itemCondition WHERE A_MEDIA.article_id = A.id AND A_MEDIA.sort_order = 1) AS FIRST_IMAGE";
        }

        if ($this->type == 'editorial') {
            $isAdmin = in_array('ROLE_ARTICLE', $this->contact->get('availableUserRights')) ? 1 : 0;
            $myAdminRoles = "''";
            if (!$isAdmin) {
                $myGroups = $this->getMyTeamsAndWorkgroups();
                if (count($myGroups['ADMIN']) > 0) {
                    $myAdminRoles = implode(',', $myGroups['ADMIN']);
                }
            }
            $selectColumnArray[] = '1 AS isEditable';
        }

        $this->selectColumnArray = $selectColumnArray;
    }

    /**
     * The function that will set the tables that need to be joined for getting the result.
     */
    public function setColumnDataFrom()
    {
        $columnArray = $this->columnData;

        $joinTablesArray[] = ' INNER JOIN fg_cms_article_text A_TEXT ON A.textversion_id = A_TEXT.id ';
        $joinTablesArray[] = " LEFT JOIN fg_cms_article_text_i18n A_TEXTi18n ON A_TEXTi18n.id = A_TEXT.id AND A_TEXTi18n.lang = '$this->defaultlanguage'";
        $joinTablesArray[] = ' LEFT JOIN fg_cms_article_selectedareas A_SEL_AREA ON (A_SEL_AREA.article_id = A.id)';
        $joinTablesArray[] = ' LEFT JOIN fg_cms_article_selectedcategories A_SEL_CAT ON A_SEL_CAT.article_id = A.id';

        foreach ($columnArray as $column) {
            switch ($column['id']) {
                case 'IMAGE_VIDEOS':
                    $joinTablesArray[] = ' LEFT JOIN fg_cms_article_media A_SEL_MEDIA ON (A_SEL_MEDIA.article_id = A.id) ';
                    break;
                case 'COMMENTS':
                    $joinTablesArray[] = ' LEFT JOIN fg_cms_article_comments A_COMM ON (A_COMM.article_id = A.id)  ';
                    break;
                case 'LANGUAGES':
                    $joinTablesArray[] = ' LEFT JOIN fg_cms_article_text_i18n A_TEXTi18n2 ON A_TEXTi18n2.id = A_TEXT.id ';
                    break;
            }
        }

        $this->joinTablesArray = $joinTablesArray;
    }

    /**
     * The function that will create the where condition for the resultset.
     *
     * @return string $where The where condition
     */
    public function setWhere()
    {
        $where = ' WHERE ';

        if ($this->type == 'editorial') {
            $where .= $this->getEditorialClubCondition();
            $where .= $this->getAreaCondition();
            $where .= $this->getEditableArticleCondition();
        } elseif ($this->type == 'article') {
            $where .= 'A.id IN ( ' . $this->getMyVisibleArticleIds() . ' )';
        }
        $where .= $this->getFilterCondition();
        $where .= ' AND is_deleted = 0';

        return $where;
    }

    /**
     * This function that will resytn the list of articles according to the usertights
     * and filters procided.
     *
     * @return array $result The list of articles
     */
    public function getArticleData()
    {
        $selectedColumns = implode(',', $this->selectColumnArray);
        $joinedTables = implode(' ', $this->joinTablesArray);
        $query = 'SELECT ' . $selectedColumns . ' FROM fg_cms_article A';
        $query .= $joinedTables;
        $query .= $this->setWhere();

        if ($this->groupBy != '') {
            $query .= ' GROUP BY ' . $this->groupBy;
        }

        if (count($this->having) > 0) {
            $havingString = implode(' AND ', $this->having);
            $query .= ' HAVING (' . $havingString . ')';
        }

        if ($this->orderBy != '') {
            $query .= ' ORDER BY' . $this->orderBy;
        }

        if ($this->limit != '') {
            $query .= $this->limit;
        }
        return $this->executeQuery($query);
    }

    /**
     * This function is used to get the editable article Ids of the logged in user.
     *
     * @param int  $clubId           Current club Id
     * @param bool $allowArchivedIds Whether the result can contain archived article or not
     *
     * @return array $editableArticleArr Editable article Ids
     */
    public function getEditableArticleIds($clubId, $allowArchivedIds)
    {
        $isAdmin = in_array('ROLE_ARTICLE', $this->contact->get('availableUserRights')) ? 1 : 0;
        $allowArchivedQuery = $allowArchivedIds ? '' : 'AND (a.expiry_date IS NULL OR a.expiry_date >= NOW())';
        $myAdminRoles = "''";
        if (!$isAdmin) {
            $myGroups = $this->getMyTeamsAndWorkgroups();
            if (count($myGroups['ADMIN']) > 0) {
                $myAdminRoles = implode(',', $myGroups['ADMIN']);
            }
        }

        $editableArticleQuery = "SELECT
								(CASE WHEN ((sa.IS_CLUB = 1 AND $isAdmin) OR (sa.article_id IS NULL AND $isAdmin)) THEN a.id
                                ELSE
                                    (CASE WHEN (((FIND_SET_IN_SET(GROUP_CONCAT(DISTINCT(sa.role_id)), '$myAdminRoles') > 0 || $isAdmin)) AND (sa.IS_CLUB != 1)) THEN a.id END)
                                END) as articleId
                                FROM `fg_cms_article` a
                                LEFT JOIN `fg_cms_article_selectedareas` sa on sa.article_id = a.id
                                WHERE a.club_id = $clubId AND a.is_deleted = 0 $allowArchivedQuery
                                group by a.id";
        $editableArticleResult = $this->executeQuery($editableArticleQuery);

        return FgUtility::getArrayFlatten($editableArticleResult, array(), true);
    }

    /**
     * This function is used to get the article Ids visible of the logged in user.
     *
     * @return string $visibleArticleIds The list of ids i am visible
     */
    public function getMyVisibleArticleIds()
    {
        $visibleArticleIds = '0';   //Implemented since the '' creating error in Doctine
        $visibleArticleQuery = "SELECT GROUP_CONCAT(DISTINCT A.id) AS id FROM
                                fg_cms_article A
                                INNER JOIN fg_cms_article_text ATEXT ON ATEXT.id = A.textversion_id
                                LEFT JOIN fg_cms_article_text_i18n ATEXTI18n ON ATEXTI18n.id = ATEXT.id AND ATEXTI18n.lang = '$this->defaultlanguage'
                                LEFT JOIN fg_cms_article_selectedareas A_SEL_AREA ON (A_SEL_AREA.article_id = A.id)
                                WHERE (publication_date <= now() AND (expiry_date >= now() OR expiry_date IS NULL) AND (is_draft=0 OR is_draft IS NULL) ) ";

        $visibleArticleQuery .= ' AND ' . $this->getArticleClubCondition();
        $clubSettings = $this->em->getRepository('CommonUtilityBundle:FgCmsArticleClubsetting')->getClubSettings($this->clubId);
        if (count($clubSettings) > 0 && $clubSettings['showMultilanguageVersion'] == 0) {
            $visibleArticleQuery .= ' AND (ATEXTI18n.title_lang IS NOT NULL)';
        }

        $visibleArticleIdResult = $this->executeQuery($visibleArticleQuery);

        if (!empty($visibleArticleIdResult[0]['id'])) {
            $visibleArticleIds = $visibleArticleIdResult[0]['id'];
        }

        return $visibleArticleIds;
    }

    /**
     * This function is used to get the article Ids visible of the logged in user with the given filters.
     *
     * @return string $visibleArticleIds The list of ids i am visible
     */
    public function getMyVisibleArticleIdsWithFilter()
    {
        $visibleArticleIds = '0';   //Implemented since the '' creating error in Doctine
        $visibleArticleQuery = "SELECT GROUP_CONCAT(DISTINCT A.id) AS id FROM
                                fg_cms_article A
                                INNER JOIN fg_cms_article_text ATEXT ON ATEXT.id = A.textversion_id
                                LEFT JOIN fg_cms_article_text_i18n ATEXTI18n ON ATEXTI18n.id = ATEXT.id AND ATEXTI18n.lang = '$this->defaultlanguage'
                                LEFT JOIN fg_cms_article_selectedareas A_SEL_AREA ON (A_SEL_AREA.article_id = A.id)
                                LEFT JOIN fg_cms_article_selectedcategories A_SEL_CAT ON A_SEL_CAT.article_id = A.id
                                WHERE (publication_date <= now() AND (expiry_date >= now() OR expiry_date IS NULL) AND (is_draft=0 OR is_draft IS NULL) ) ";

        $visibleArticleQuery .= ' AND ' . $this->getArticleClubCondition();
        $clubSettings = $this->em->getRepository('CommonUtilityBundle:FgCmsArticleClubsetting')->getClubSettings($this->clubId);
        if (count($clubSettings) > 0 && $clubSettings['showMultilanguageVersion'] == 0) {
            $visibleArticleQuery .= ' AND (ATEXTI18n.title_lang IS NOT NULL)';
        }
        $visibleArticleQuery .= $this->getFilterCondition();

        $visibleArticleIdResult = $this->executeQuery($visibleArticleQuery);

        if (!empty($visibleArticleIdResult[0]['id'])) {
            $visibleArticleIds = $visibleArticleIdResult[0]['id'];
        }

        return $visibleArticleIds;
    }

    /**
     * This function is used to set the area condition according to userrights and filters.
     *
     * @return string $roleCondition
     */
    private function getAreaCondition()
    {
        $isAdmin = in_array('ROLE_ARTICLE', $this->contact->get('availableUserRights')) ? 1 : 0;
        if (!$isAdmin) {
            $myGroups = $this->getMyTeamsAndWorkgroups();
            $myGroupsList = $myGroups['ADMIN'];

            if (count($myGroupsList) == 0) {
                $myGroupsList = array(0);
            }
            $roleCondition = ' AND ( A_SEL_AREA.role_id IN (' . implode(',', $myGroupsList) . ')  OR A_SEL_AREA.is_club = 1)';
        }

        return $roleCondition;
    }

    /**
     * This function is used to set the club condition.
     *
     * @return string $clubCondition
     */
    private function getEditorialClubCondition()
    {
        $clubCondition = "(A.club_id = {$this->clubId} ";
        $clubCondition .= ')';

        return $clubCondition;
    }

    /**
     * This function is used to set the club condition.
     *
     * @return string $clubCondition  This will set the club condition for the editorial list
     */
    private function getEditableArticleCondition()
    {
        $myEditableArticle = $this->getEditableArticleIds($this->clubId, true);
        $myEditableArticleString = '';
        if (count($myEditableArticle) == 0) {
            $myEditableArticle = array(0);
        }

        $myEditableArticleString = ' AND A.id IN(' . implode(',', $myEditableArticle) . ')';

        return $myEditableArticleString;
    }

    /**
     * This function is used to set the club condition for sidebar.
     *
     * @return string $clubCondition This will set the club condition for the article list
     */
    private function getArticleClubCondition()
    {
        $isAdmin = in_array('ROLE_ARTICLE', $this->contact->get('availableUserRights')) ? 1 : 0;
        $clubHeirarchy = $this->club->get('clubHeirarchy');

        $myGroups = $this->getMyTeamsAndWorkgroups();
        $myGroupsList = array_unique(array_merge($myGroups['MEMBER'], $myGroups['ADMIN']));
        if (count($myGroupsList) == 0) {
            $myGroupsList = array(0);
        }
        if ($this->isPublic) {
            $clubCondition = "(A.club_id = $this->clubId AND ( A.scope = 'public') ";
        } else {
            $clubCondition = "(A.club_id = $this->clubId AND ( (A_SEL_AREA.role_id IN ( " . implode(',', $myGroupsList) . " ) OR ($isAdmin = 1)) OR A.scope = 'public' OR A_SEL_AREA.is_club = 1) ";
        }
        if (count($clubHeirarchy) > 0) {
            $clubCondition .= ($this->isPublic) ? ' OR (A.club_id IN (' . implode(',', $clubHeirarchy) . ') AND (A.share_with_lower = 1))' : ' OR (A.club_id IN (' . implode(',', $clubHeirarchy) . ') AND (A.share_with_lower = 1 AND A_SEL_AREA.is_club = 1))';
        }
        $clubCondition .= ')';

        return $clubCondition;
    }

    /**
     * This function to filter the data as per the conditions set.
     *
     * @return string $filterCondition  Will set the filter condition for both article and editorial sidebar
     */
    private function getFilterCondition()
    {
        $filterDataArray = array_filter($this->filterData);
        $filterCondition = '';

        foreach ($filterDataArray as $key => $value) {
            switch ($key) {
                case 'CATEGORIES':
                    if ($value == 'ALL') {
                        $filterCondition .= '';
                    } elseif ($value == 'NONE') {
                        $filterCondition .= ' AND A_SEL_CAT.category_id IS NULL ';
                    } else {
                        //Implemeneted to do the filtering in article sidebar
                        if (isset($filterDataArray['CA_WITHOUT'])) {
                            $withoutCategoryString = " OR (A_SEL_CAT.category_id IS NULL AND A.club_id = $this->clubId)";
                        }

                        if (isset($filterDataArray['CAT_CLUB'])) {
                            $catClubString = ' OR (A.club_id IN(' . $filterDataArray['CAT_CLUB'] . '))';
                        }

                        //To implemenet the AREA_WITHOUT & AREA_CLUB we need to put AREA to NULL
                        if ($value != 'NULL') {
                            $filterCondition .= " AND (A_SEL_CAT.category_id IN ($value) $withoutCategoryString $catClubString) ";
                        } else {
                            $filterCondition .= ' AND (' . substr($withoutCategoryString . $catClubString, 3) . ') ';
                        }
                    }
                    break;
                case 'AREAS':
                    if ($value == 'ALL') {
                        $filterCondition .= '';
                    } elseif ($value == 'NONE') {
                        $filterCondition .= ' AND (A_SEL_AREA.role_id IS NULL AND (A_SEL_AREA.is_club = 0 OR A_SEL_AREA.is_club IS NULL))';
                    } else {
                        //Implemeneted to do the filtering in article sidebar
                        //AREA_WITHOUT & AREA_CLUB only comes in article
                        if (isset($filterDataArray['AREA_WITHOUT'])) {
                            $withoutAreaString = " OR  (A_SEL_AREA.role_id IS NULL AND A.club_id = $this->clubId  AND (A_SEL_AREA.is_club = 0 OR A_SEL_AREA.is_club IS NULL))";
                        }

                        if (isset($filterDataArray['AREA_CLUB'])) {
                            $areaClubString = ' OR (A.club_id IN(' . $filterDataArray['AREA_CLUB'] . '))';
                        }

                        if ($this->type == 'article' && isset($filterDataArray['IS_CLUB'])) {
                            $isClubString = " OR (A_SEL_AREA.is_club = 1 AND A.club_id = $this->clubId)";
                        }

                        //To implemenet the AREA_WITHOUT & AREA_CLUB we need to put AREA to NULL (only comes in article)
                        if ($value != 'NULL') {
                            $filterCondition .= " AND (A_SEL_AREA.role_id IN ($value) $withoutAreaString $areaClubString $isClubString) ";
                        } else {
                            $filterCondition .= ' AND (' . substr($withoutAreaString . $areaClubString . $isClubString, 3) . ') ';
                        }
                    }
                    break;
                case 'START_DATE':
                    $filterCondition .= " AND A.publication_date >= '$value 00:00:00'";
                    break;
                case 'END_DATE':
                    $filterCondition .= " AND A.publication_date <= '$value 23:59:59'";
                    break;
                case 'CREATED_BY':
                    $filterCondition .= " AND A.created_by = '$value'";
                    break;
                case 'IS_CLUB':
                    if ($this->type == 'editorial') {
                        $this->addHaving(array('isClub = 1'));
                    }
                    break;
                case 'STATUS':
                    if ($value != 'ALL') {
                        $this->addHaving(array("STATUS IN($value)"));
                    }
                    break;
                case 'SEARCH':
                    $value = str_replace(array('"', "'"), array('', ''), $value);
                    if ($value != '') {
                        $filterCondition .= "AND ( COALESCE(A_TEXTi18n.title_lang, A_TEXT.title) LIKE '%$value%' "
                            . "OR COALESCE(A_TEXTi18n.teaser_lang, A_TEXT.teaser) LIKE '%$value%' "
                            . "OR COALESCE(A_TEXTi18n.text_lang, A_TEXT.text) LIKE '%$value%' )";
                    }
                    break;
            }
        }

        return $filterCondition;
    }

    /**
     * This function is used to get all teams and workgroups in which the logged in user have rights.
     *
     * @return array $myGroups My teams and workgroups
     */
    private function getMyTeamsAndWorkgroups()
    {
        $myAdminGroups = array();
        $myMemberGroups = array();
        $myGroups = array();

        $groupRights = $this->contact->get('clubRoleRightsGroupWise');

        if (isset($groupRights['ROLE_GROUP_ADMIN']['teams'])) {
            $myMemberGroups = array_merge($myMemberGroups, $groupRights['ROLE_GROUP_ADMIN']['teams']);
            $myAdminGroups = array_merge($myAdminGroups, $groupRights['ROLE_GROUP_ADMIN']['teams']);
        }
        if (isset($groupRights['ROLE_GROUP_ADMIN']['workgroups'])) {
            $myMemberGroups = array_merge($myMemberGroups, $groupRights['ROLE_GROUP_ADMIN']['workgroups']);
            $myAdminGroups = array_merge($myAdminGroups, $groupRights['ROLE_GROUP_ADMIN']['workgroups']);
        }
        if (isset($groupRights['ROLE_ARTICLE_ADMIN']['teams'])) {
            $myMemberGroups = array_merge($myMemberGroups, $groupRights['ROLE_ARTICLE_ADMIN']['teams']);
            $myAdminGroups = array_merge($myAdminGroups, $groupRights['ROLE_ARTICLE_ADMIN']['teams']);
        }
        if (isset($groupRights['ROLE_ARTICLE_ADMIN']['workgroups'])) {
            $myMemberGroups = array_merge($myMemberGroups, $groupRights['ROLE_ARTICLE_ADMIN']['workgroups']);
            $myAdminGroups = array_merge($myAdminGroups, $groupRights['ROLE_ARTICLE_ADMIN']['workgroups']);
        }
        if (isset($groupRights['MEMBER']['teams'])) {
            $myMemberGroups = array_merge($myMemberGroups, $groupRights['MEMBER']['teams']);
        }
        if (isset($groupRights['MEMBER']['workgroups'])) {
            $myMemberGroups = array_merge($myMemberGroups, $groupRights['MEMBER']['workgroups']);
        }

        $myGroups['MEMBER'] = array_unique($myMemberGroups);
        $myGroups['ADMIN'] = array_unique($myAdminGroups);

        return $myGroups;
    }

    /**
     * The function that set the order by condition for the result
     * The default order will be publication date.
     */
    public function addOrderBy($orderColumn = '')
    {
        if ($orderColumn != '') {
            $this->orderBy = ' ' . $orderColumn;
        } else {
            $this->orderBy = ' A.publication_date DESC ';
        }
    }

    /**
     * This function is used to add a column to the existing selected columns.
     *
     * @param array $having
     */
    public function addHaving($having)
    {
        if (count($having) > 0) {
            $this->having = array_merge($this->having, $having);
        }
    }

    /**
     * This function is used to add a group by condition to the qry.
     */
    public function setGroupBy()
    {
        $this->groupBy = 'A.id';
    }

    /**
     * This function is used to set the limit for the query.
     *
     * @param int $pageNo          The page number
     * @param int $paginationCount The pagination count
     */
    public function setLimit($pageNo = 0, $paginationCount = 10)
    {
        $offset = ($pageNo * $paginationCount);
        $this->limit = " LIMIT $offset, $paginationCount";
    }

    /**
     * This function is used to set the limit for the query.
     *
     * @param int $offset The offset
     * @param int $count  The count
     */
    public function setOffset($offset = 0, $count = 10)
    {
        $this->limit = " LIMIT $offset, $count";
    }

    /**
     * Function to execute a sql query.
     *
     * @param string $sql Sql query
     *
     * @return array Result array
     */
    public function executeQuery($sql)
    {
        $utilPdo = new UtilPdo($this->container);

        return $utilPdo->executeQuery($sql);
    }
}
