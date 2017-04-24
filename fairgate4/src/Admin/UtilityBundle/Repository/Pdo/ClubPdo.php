<?php

/**
 * CmsPdo
 */
namespace Admin\UtilityBundle\Repository\Pdo;

use Common\UtilityBundle\Util\FgSettings;
use Common\UtilityBundle\Util\FgUtility;
use Doctrine\DBAL\Cache\QueryCacheProfile;

/**
 * Used to handling different CMS functions.
 *
 * @author  pitsolutions.ch <pit@solutions.com>
 */
class ClubPdo
{

    /**
     * Conatiner Object.
     *
     * @var object
     */
    protected $container;

    /**
     * Connection Object.
     *
     * @var object
     */
    protected $conn;

    /**
     * Entity manager Object.
     *
     * @var object
     */
    protected $em;
   /**
    *admin database connection
    * @var type 
    */ 
    protected $adminManagerconn;
    /**
     * admin database entity manager
     * @var type 
     */        
    protected $adminEntityManager;

    /**
     * Constructor for initial setting.
     *
     * @param object $container Container Object
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->conn = $this->container->get('database_connection');
        $this->em = $this->container->get('doctrine')->getManager();
        $this->adminEntityManager = $this->container->get("fg.admin.connection")->getAdminEntityManager();
        $this->adminManagerconn = $this->container->get("fg.admin.connection")->getAdminConnection();
    }

    
    /**
     * Function to All sub level clubs.
     *
     * @param Integer $clubId Club id
     *
     * @return Array $resultArray Array of id and title
     */
    public function getAllSubLevelData($clubId)
    {
        $defaultLang = $this->container->get('club')->get('default_lang');
        $sublevelClubsSql = "SELECT c.id, COALESCE(NULLIF(ci18n.title_lang,''), c.title) as title , is_sub_federation "
            . "FROM (SELECT  sublevelClubs(id) AS id, @level AS level "
            . "FROM (SELECT  @start_with := '$clubId',@id := @start_with,@level := 0) vars, "
            . "fg_club WHERE @id IS NOT NULL) ho JOIN fg_club c ON c.id = ho.id"
            . " LEFT JOIN fg_club_i18n ci18n ON ci18n.id = c.id AND ci18n.lang = :defaultLang";

        $rowClubsArray = $this->adminManagerconn->fetchAll($sublevelClubsSql, array('defaultLang' => $defaultLang));

        return $rowClubsArray;
    }

    /**
     * Function to get all assigned assignments of a club
     *
     * @param array  $clubArray ClubArray
     * @param object $conn      Connection
     * @param int    $clubid    Club id
     * @param int    $sortOrder Sortorder
     *
     * @return array $resultArray
     */
    public function getAllAssignedAssignments($clubArray, $conn, $clubid, $sortOrder = "CCCL")
    {
        $defaultLang = $isData ? $clubArray['defSysLang'] : $clubArray['defaultClubLang'];
        $lClubId = $clubArray['clubId'];

        switch ($clubArray['clubType']) {
            case 'federation':
                $clubType = "'federation' AS clubType";
                $addWhere = "";
                break;

            case 'sub_federation':
                $clubType = "'sub_federation' AS clubType";
                $addWhere = " AND fcl.sublevel_assign !='not visible' ";
                break;
        }
        if ($sortOrder == "CL") {
            $order = "CLorder,CCorder";
        } else {
            $order = "CCorder,CLorder";
        }
        $resultArray = $this->adminManagerconn->fetchAll(
            "SELECT a.club_id as clubId,a.assined_federation_id, a.class_id, fcl.sublevel_assign as subLevel, fcl.class_assign as classAssign, fcl.id as classificationId,'federation' AS clubType,fcc.id as classId, fcl.sort_order as CLorder, fcc.sort_order as CCorder,
            IF(fcl18n.title_lang IS NULL OR fcl18n.title_lang='', fcl.title, fcl18n.title_lang) AS classificationTitle,
            IF(fcc18n.title_lang IS NULL OR fcc18n.title_lang='', fcc.title, fcc18n.title_lang) AS classTitle, fcl.federation_id, getClassAssignmentCount(a.class_id, $lClubId) as clubCount
            FROM fg_club_class_assignment a LEFT JOIN fg_club_class fcc ON fcc.id =a.class_id AND fcc.is_active='1' LEFT JOIN fg_club_classification fcl ON fcc.classification_id= fcl.id
            LEFT JOIN fg_club_classification_i18n fcl18n ON fcl18n.id=fcl.id AND fcl18n.lang='$defaultLang' LEFT JOIN fg_club_class_i18n fcc18n ON fcc18n.id=fcc.id AND fcc18n.lang='$defaultLang'
            WHERE a.club_id=:clubId " . $addWhere . "ORDER BY " . $order . " ASC", array(':clubId' => $clubid));

        return $resultArray;
    }

    /**
     * Function to get all Classification class under logged club
     *
     * @param object $conn        Connection
     * @param int    $clubArray   Club array
     * @param string $defaultLang default language
     *
     * @return array $resultArray
     */
    public function getAllClassificationClass($conn, $clubArray, $defaultLang)
    {
        switch ($clubArray['clubType']) {
            case 'federation':
                $clubIds = $clubArray['clubId'];
                $clubType = "'federation' AS clubType";
                $addWhere = "";
                break;
            case 'sub_federation':
                $clubIds = $clubArray['federationId'];
                $clubType = "'sub_federation' AS clubType";
                $addWhere = " AND fcl.sublevel_assign ='assign' ";
                break;
        }
        $resultArray = $this->adminManagerconn->fetchAll(
            "SELECT fcl.sublevel_assign as subLevel, fcl.class_assign as classAssign, fcl.id as classificationId, " . $clubType . ", fcc.id as classId, fcl.sort_order as CLorder,  CONCAT('cfn',fcl.id) as clsfnId, CONCAT('c',fcc.id) as clsId, fcc.sort_order as CCorder,
            IF(fcl18n.title_lang IS NULL OR fcl18n.title_lang='', fcl.title, fcl18n.title_lang) AS classificationTitle,
            IF(fcc18n.title_lang IS NULL OR fcc18n.title_lang='', fcc.title, fcc18n.title_lang) AS classTitle
            FROM fg_club_classification fcl INNER JOIN fg_club_class fcc ON fcl.id = fcc.classification_id AND fcc.is_active='1'
            LEFT JOIN fg_club_classification_i18n fcl18n ON fcl18n.id=fcl.id AND fcl18n.lang='$defaultLang'
            LEFT JOIN fg_club_class_i18n fcc18n ON fcc18n.id=fcc.id AND fcc18n.lang='$defaultLang' WHERE fcl.federation_id='$clubIds' AND fcl.is_active=1 " . $addWhere . "ORDER BY CLorder,classificationId ASC, CCorder,classId ASC");

        return $resultArray;
    }

    /**
     * Function to Execute assignments
     *
     * @param array  $delete Delete id
     * @param object $insert Insert id
     *
     * @return boolean
     */
    public function executeAssignments($delete, $insert)
    {

        if (!empty($delete)) {
            $query = "DELETE FROM fg_club_class_assignment WHERE (assined_federation_id, club_id, class_id) IN (" . implode(',', $delete) . ");";
            $this->adminManagerconn->executeQuery($query);
        }
        if (!empty($insert)) {
            $query = "INSERT INTO fg_club_class_assignment (assined_federation_id, club_id, class_id) VALUES " . implode(',', $insert) . ";";
            $this->adminManagerconn->executeQuery($query);
        }

        return true;
    }

    /**
     * Function to insert log entry
     *
     * @param String $log     Log entry values
     * @param String $clublog Club log entry values
     *
     * @return array $resultArray       Array containing errors if any
     */
    public function logEntry($log, $clublog)
    {

        if (!empty($log)) {
            $logQuery = "INSERT INTO fg_club_class_log (club_id, class_id, date, kind, field, value_before, value_after, changed_by_contact, changed_by_contact_name) VALUES " . implode(',', $log) . ";";
            $this->adminManagerconn->executeQuery($logQuery);
        }
        if (!empty($clublog)) {
            $logquery = "INSERT INTO fg_club_log (club_id, date, kind, field, value_before, value_after, changed_by, class_log_id, changed_by_name) VALUES " . implode(',', $clublog) . ";";
            $this->adminManagerconn->executeQuery($logquery);
        }

        return true;
    }

    /**
     * Function to get Bookmarks for a perticular club.
     *
     * @param Integer $contactId Contact Id
     * @param Integer $clubId    Club Id
     * @param String  $clubType  Club Type
     *
     * @return query result or as processed array based on the $exec parameter
     */
    public function getClubBookmarks($contactId, $clubId, $clubType)
    {
        $defaultLang = $this->container->get('club')->get('default_lang');
        $bookmarkSql = "SELECT bm.id as bookMarkId, bm.contact_id as contactId,bm.type as itemType,
                        (CASE WHEN bm.type = 'class' THEN bm.class_id WHEN bm.type = 'filter' THEN bm.filter_id ELSE bm.subfed_id END ) AS id,
                        bm.sort_order as sortOrder,
                        (CASE WHEN bm.type = 'class' THEN cc.title WHEN bm.type = 'filter' THEN cf.name ELSE
                         COALESCE(NULLIF(ci18n.title_lang,''), c.title)
                        END ) AS title,
                        cc.classification_id AS categoryId, cf.is_broken AS isBroken,cf.filter_data as filterData,
                        (CASE WHEN bm.class_id !='' AND bm.class_id IS NOT NULL
                                THEN getClassAssignmentCount(cc.id, $clubId)
                            WHEN bm.subfed_id !='' AND bm.subfed_id IS NOT NULL
                                THEN (SELECT clubCount(bm.subfed_id))
                        END ) AS count,
                        (CASE WHEN bm.type='class' THEN 1 ELSE 0 END) AS draggable
                        FROM fg_club_bookmarks bm
                        LEFT JOIN fg_club_class cc ON bm.class_id=cc.id
                        LEFT JOIN fg_club_filter cf ON bm.filter_id=cf.id
                        LEFT JOIN fg_club c ON bm.subfed_id=c.id
                        LEFT JOIN fg_club_i18n ci18n ON ci18n.id = c.id AND ci18n.lang = :defaultLang
                        WHERE bm.club_id=:clubId AND bm.contact_id=:contactsId order by bm.sort_order";

        $dataResult = $this->adminManagerconn->fetchAll($bookmarkSql, array(':contactsId' => $contactId, ':clubId' => $clubId, 'defaultLang' => $defaultLang));

        return $dataResult;
    }    
    

    /**
     * Function to get the data field log entries
     *
     * @param int $clubId        ClubId in db
     * @param int $currentClubId current Club Id
     *
     * @return array $result
     */
    public function getDataFieldLogEntries($clubId, $currentClubId)
    {
        $dateFormat = FgSettings::getMysqlDateTimeFormat();
        $defaultLang = $this->container->get('club')->get('default_lang');
        
        $checkActiveContactCondition =  "(cm.club_id = $currentClubId && cm.is_active = 1 && c.changed_by != 1)";
        $clubTitleQuery = "SELECT COALESCE(NULLIF(Ci18N.title_lang,''),FC.title) AS title FROM fg_cm_contact CT LEFT JOIN fg_club FC ON CT.main_club_id = FC.id LEFT JOIN fg_club_i18n Ci18N ON Ci18N.id = FC.id AND Ci18N.lang = '$defaultLang' WHERE CT.id = c.changed_by";
       
        $sql = "SELECT c.id,c.club_id,c.kind,c.field,c.value_before,c.value_after,c.changed_by,c.date AS dateOriginal,date_format( c.date,'" . $dateFormat . "') AS date, 
                IF( $checkActiveContactCondition, c.changed_by, NULL) AS activeContact,
                IF( $checkActiveContactCondition, CONCAT(cm.name,' (',($clubTitleQuery),')') , cm.name )as editedBy, 
                 (CASE WHEN ((c.value_before IS NOT NULL AND c.value_before != '' AND c.value_before != '-') AND (c.value_after IS NOT NULL AND c.value_after != '' AND c.value_after != '-')) THEN 'changed'
                       WHEN ((c.value_before IS NOT NULL AND c.value_before != '' AND c.value_before != '-') AND (c.value_after IS NULL OR c.value_after = '' OR c.value_after = '-')) THEN 'removed'
                       WHEN ((c.value_before IS NULL OR c.value_before = '' OR c.value_before = '-') AND (c.value_after IS NOT NULL AND c.value_after != '' AND c.value_after != '-')) THEN 'added'
                       ELSE 'none'
                 END) AS status,
                 c.changed_by_name AS changedByName
                 FROM fg_club_log c 
                 LEFT JOIN fg_cm_contact cm ON cm.id = c.changed_by
                 WHERE c.club_id= :clubId AND c.kind='data'";
        $result = $this->adminManagerconn->fetchAll($sql, array('clubId' => $clubId));

        return $result;
    }

    /**
     * Function to get the classification log entries
     *
     * @param int    $clubId        ClubId of the logs listed
     * @param string $clubType      ClubType
     * @param int    $currentClubId current ClubId
     *
     * @return array $result
     */
    public function getClassificationLogEntries($clubId, $clubType, $currentClubId)
    {
        $dateFormat = FgSettings::getMysqlDateTimeFormat();
        $defaultLang = $this->container->get('club')->get('default_lang');
        $clubTitleQuery = "SELECT COALESCE(NULLIF(Ci18N.title_lang,''),FC.title) AS title FROM fg_cm_contact CT LEFT JOIN fg_club FC ON CT.main_club_id = FC.id LEFT JOIN fg_club_i18n Ci18N ON Ci18N.id = FC.id AND Ci18N.lang = '$defaultLang' WHERE CT.id = c.changed_by";
        $join = ($clubType == 'sub_federation') ? ' LEFT JOIN fg_club_class cl ON cl.id=c.class_log_id LEFT JOIN fg_club_classification cls ON cls.id=cl.classification_id ' : '';
        $where = ($clubType == 'sub_federation') ? " AND cls.sublevel_assign != 'not visible'" : '';
        $checkActiveContactCondition =  "(cm.club_id = $currentClubId && cm.is_active = 1 && c.changed_by != 1)";
        $sql = "SELECT c.id,c.club_id,c.kind,c.field,c.value_before,c.value_after,c.changed_by,c.date AS dateOriginal,date_format( c.date,'" . $dateFormat . "') AS date, 
                IF( $checkActiveContactCondition, c.changed_by, NULL) AS activeContact,    
                IF( $checkActiveContactCondition, CONCAT(cm.name,' (',($clubTitleQuery),')') , cm.name )as editedBy,    
                 (CASE WHEN ((c.value_before IS NOT NULL AND c.value_before != '' AND c.value_before != '-') AND (c.value_after IS NULL OR c.value_after = '' OR c.value_after = '-')) THEN 'removed'
                       WHEN ((c.value_before IS NULL OR c.value_before = '' OR c.value_before = '-') AND (c.value_after IS NOT NULL AND c.value_after != '' AND c.value_after != '-')) THEN 'added'
                       ELSE 'none'
                 END) AS status,
                 c.changed_by_name AS changedByName
                 FROM fg_club_log c " . $join . 
                " LEFT JOIN fg_cm_contact cm ON cm.id = c.changed_by  ".
                " WHERE c.club_id= :clubId AND c.kind='assigned club'" . $where;
        $result = $this->adminManagerconn->fetchAll($sql, array('clubId' => $clubId));

        return $result;
    }


    /**
     * Function to get all classification Id
     * @param int $classificationId   classification ID

     * * * @return array $result
     */
    public function getAllClassificationId($classificationId)
    {

        return $this->adminManagerconn->fetchAll("SELECT GROUP_CONCAT(c.id) AS ids FROM fg_club_class c WHERE c.classification_id='$classificationId' ");
    }

    /**
     * Function to save club filter
     *
     * @param string  name   name
     * @param int contactId   contact
     * @param int clubId   clubid
     * @param string jString   string
     * @param string operation   Insert or update
     *
     * * * @return boolean
     */
    public function saveClubFilter($name, $contactId, $clubId, $jString, $operation)
    {

        if ($operation == 'INSERT') {
            $qry = "INSERT INTO fg_club_filter (sort_order, name, table_attributes, table_rows, contact_id, club_id, updated_at, filter_data, is_broken)
                    SELECT 1 + coalesce((SELECT max(sort_order) FROM fg_club_filter), 0), '$name', NULL , '10', '$contactId', '$clubId', now(), '" . $jString . "', 0 ;";
        } else {
            $qry = "UPDATE fg_club_filter SET name = '$name', filter_data='" . $jString . "', updated_at=now(),is_broken =0 WHERE name = '$name' AND  contact_id = '$contactId' AND  club_id = '$clubId'";
        }

        $stmt = $this->adminManagerconn->executeQuery($qry);

        return true;
    }

    /**
     * Function to get classifications of a club
     *
     * @param Integer $clubId club id
     * @param Boolean $exec   (true or false value decides whether pass query result or as processed array )
     * @param Integer $catId  category id
     *
     * @return query result or as processed array based on the $exec parameter
     */
    public function getClubClassifications($clubId, $exec = true, $catId = 0)
    {
        $catSql = $catId ? " AND c.id=$catId" : '';
        $sql = "SELECT c.id,c.title,c.sort_order, c.is_active, ci18.title_lang, ci18.lang,c.sublevel_assign,c.class_assign,
                (select count(id) from fg_club_class WHERE `classification_id`=c.id) as classCount,
                (select count(id) from fg_club_class_assignment WHERE class_id in (select id FROM fg_club_class WHERE `classification_id`=c.id)) as clubCount FROM fg_club_classification c
                LEFT JOIN fg_club_classification_i18n ci18 ON c.id=ci18.id
                WHERE c.federation_id='$clubId' $catSql";
        $dataResult = $this->adminManagerconn->fetchAll($sql);

        if ($exec) {
            $result = array();
            $id = '';
            foreach ($dataResult as $key => $arr) {
                if (count($arr) > 0) {
                    if ($arr['id'] == $id) {
                        $result[$id]['titleLang'][$arr['lang']] = $arr['title_lang'];
                    } else {
                        $id = $arr['id'];
                        $result[$id] = array('id' => $arr['id'], 'title' => $arr['title'], 'sortOrder' => $arr['sort_order'], 'isActive' => $arr['is_active'], 'clubCount' => $arr['clubCount'], 'classCount' => $arr['classCount'],
                            'subLevelAssign' => $arr['sublevel_assign'], 'classAssign' => $arr['class_assign']);
                        $result[$id]['titleLang'][$arr['lang']] = $arr['title_lang'];
                    }
                }
            }

            return $result;
        } else {

            return $dataResult;
        }
    }

    /**
     * Function to get class log
     *
     * @param int $clubId  Club id
     * @param int $classId Class id
     *
     * @return type
     */
    public function getClassLog($clubId, $classId)
    {
        $dateFormat = FgSettings::getMysqlDateTimeFormat();
        $cond = " AND cl.class_id = :classId";
        $checkActiveContactCondition =  "(cm.club_id = $clubId && cm.is_active = 1 && cl.changed_by_contact != 1)";
        $defaultLang = $this->container->get('club')->get('default_lang');
        $clubTitleQuery = "SELECT COALESCE(NULLIF(Ci18N.title_lang,''),FC.title) AS title FROM fg_cm_contact CT LEFT JOIN fg_club FC ON CT.main_club_id = FC.id LEFT JOIN fg_club_i18n Ci18N ON Ci18N.id = FC.id AND Ci18N.lang = '$defaultLang' WHERE CT.id = cl.changed_by_contact";
        $sql = "SELECT cl.id, cl.club_id, cl.date AS date, cl.kind, cl.field, cl.value_before, cl.value_after,cl.date AS dateOriginal,date_format(cl.date,'" . $dateFormat . "') AS date,
                IF((cl.kind = 'data'), 'data', 'assignments') AS tabGroups, 
                IF( $checkActiveContactCondition, cl.changed_by_contact, NULL) AS activeContact,
                IF( $checkActiveContactCondition, CONCAT(cm.name,' (',($clubTitleQuery),')') , cm.name )as editedBy, 
                (CASE WHEN ((cl.value_before IS NOT NULL AND cl.value_before != '' AND cl.value_before != '-') AND (cl.value_after IS NULL OR cl.value_after = '' OR cl.value_after = '-')) THEN 'removed'
                       WHEN ((cl.value_before IS NULL OR cl.value_before = '' OR cl.value_before = '-') AND (cl.value_after IS NOT NULL AND cl.value_after != '' AND cl.value_after != '-')) THEN 'added'
                       WHEN ((cl.value_before IS NOT NULL AND cl.value_before != '' AND cl.value_before != '-') AND (cl.value_after IS NOT NULL AND cl.value_after != '' AND cl.value_after != '-') AND (cl.value_before != cl.value_after)) THEN 'changed'
                       ELSE 'none'
                END) AS status,
                c.changed_by_name AS changedByName,
                (IF((cl.value_after='' OR cl.value_after IS NULL OR cl.value_after='-'),cl.value_before,cl.value_after)) AS columnVal3
                FROM fg_club_class_log cl 
                LEFT JOIN fg_cm_contact cm ON cm.id = cl.changed_by_contact 
                WHERE cl.kind IN ('data', 'assigned club')
                $cond
                ORDER BY DATE(cl.date) DESC";
        $result = $this->adminManagerconn->fetchAll($sql, array(':classId' => $classId));

        return $result;
    }

    /**
     * For collecct the classification details
     *
     * @param int $fedarationId Federation id
     * @param int $clubId       Club id
     * @param string $language     Language
     * @param string $clubType     Club Type
     * @param boolean $exec         Flag set true to process data
     *
     * @return array query result or as processed array based on the $exec parameter
     */
    public function getClubClassificationsTitle($fedarationId, $clubId, $language, $clubType, $exec = true)
    {

        $addWhere = " ";
        if ($clubType != 'federation') {
            $addWhere = " AND c.sublevel_assign !='not visible' ";
        }
        $sql = "SELECT c.id,IF(ci18.title_lang IS NULL OR ci18.title_lang='', c.title, ci18.title_lang) AS title, c.sublevel_assign, c.federation_id, c.sort_order, c.is_active,
                (select count(id) from fg_club_class WHERE `classification_id`=c.id) as classCount,
                (select count(id) from fg_club_class_assignment WHERE class_id in (select id FROM fg_club_class WHERE `classification_id`=c.id)) as clubCount FROM fg_club_classification c
                LEFT JOIN fg_club_classification_i18n ci18 ON c.id=ci18.id AND ci18.lang='$language' WHERE c.federation_id='$fedarationId'" . $addWhere . "  ORDER BY c.sort_order ASC";

        $dataResult = $this->adminManagerconn->fetchAll($sql);
        if ($exec) {
            $result = array();
            $id = '';
            foreach ($dataResult as $key => $arr) {
                if (count($arr) > 0) {
                    if ($arr['id'] == $id) {
                        $result[$id]['titleLang'] = $arr['title_lang'];
                    } else {
                        $id = $arr['id'];
                        $fgDraggable = 0;
                        if (($arr['sublevel_assign'] == 'assign') || ($arr['federation_id'] == $clubId)) {
                            $fgDraggable = 1;
                        }
                        $result[$id] = array('id' => $arr['id'], 'title' => $arr['title'], 'sortOrder' => $arr['sort_order'], 'isActive' => $arr['is_active'], 'clubCount' => $arr['clubCount'], 'classCount' => $arr['classCount'], 'draggable' => $fgDraggable);
                        $result[$id]['titleLang'] = $arr['title_lang'];
                    }
                }
            }

            return $result;
        } else {

            return $dataResult;
        }
    }

    /**
     * For collect the class details under the classifcation
     *
     * @param int $fedarationId fedarationId
     * @param int $clubId       clubId
     * @param int $contactId    contactId
     * @param int $catId        catId
     * @param string $language     language
     * @param boolean $exec         exec
     *
     * @return type
     */
    public function getClassificationClassesTitle($fedarationId, $clubId, $contactId, $catId, $language, $exec = true)
    {

        $sql = "SELECT c.id, IF(ci18.title_lang IS NULL OR ci18.title_lang='', c.title, ci18.title_lang) AS title, c.classification_id AS categoryId, c.sort_order, fcb.id AS bookMarkId,  getClassAssignmentCount(c.id, $clubId) as clubCount "
            . " FROM fg_club_class c "
            . "LEFT JOIN fg_club_class_i18n ci18 ON c.id=ci18.id AND ci18.lang='" . $language . "' "
            . "LEFT JOIN fg_club_bookmarks AS fcb ON fcb.club_id='$clubId' AND fcb.contact_id='$contactId' AND fcb.class_id=c.id "
            . "WHERE c.federation_id='$fedarationId'  AND c.classification_id=" . $catId . " ORDER BY c.sort_order ASC";




        $dataResult = $this->adminManagerconn->fetchAll($sql);
        if ($exec) {
            $result = array();
            $id = '';
            foreach ($dataResult as $key => $arr) {
                if (count($arr) > 0) {
                    if ($arr['id'] == $id) {

                    } else {
                        $id = $arr['id'];

                        $result[$id] = array('id' => $arr['id'], 'title' => $arr['title'], 'categoryId' => $arr['categoryId'], 'count' => $arr['clubCount'], 'itemType' => 'class', 'bookMarkId' => $arr['bookMarkId']);
                    }
                }
            }

            return $result;
        } else {

            return $dataResult;
        }
    }
    

    /**
     * Function to All sub level clubs.
     *
     * @param Integer $clubId      Club id
     * @param Integer $contactId   contact id
     * @param Integer $andConditon Condition for the query
     * @param Integer $isCount     to mention count or id needed in the query
     * @param String  $defaultLang Contact default language in backend
     *
     * @return array $resultArray Array of id and title
     */
    public function getAllSubLevelClubs($clubId, $contactId, $andConditon = '', $isCount = 0, $defaultLang = 'de')
    {
        $selectColumn = ($isCount == 1) ? 'count(c.id) as count' : "c.id, COALESCE(NULLIF(ci18n.title_lang,''), c.title) as title, is_sub_federation, fcb.id AS bookMarkId,(SELECT clubCount(c.id)) as count";
        $sublevelClubsSql = "SELECT $selectColumn FROM (SELECT  sublevelClubs(id) AS id, @level AS level FROM (SELECT  @start_with := '$clubId',@id := @start_with,@level := 0) vars, fg_club WHERE @id IS NOT NULL) ho JOIN fg_club c ON c.id = ho.id AND (c.club_type='sub_federation' OR c.club_type='federation')
        LEFT JOIN fg_club_i18n ci18n ON (ci18n.id = c.id AND ci18n.lang = ':defaultLang') LEFT JOIN fg_club_bookmarks AS fcb ON c.id = fcb.subfed_id AND fcb.club_id = '$clubId' AND fcb.contact_id='$contactId'";
        if ($andConditon != '') {
            $sublevelClubsSql .= ' AND ' . $andConditon;
        }
        $sublevelClubsSql .= ' ORDER BY title ASC';
        $rowClubsArray = $this->adminManagerconn->fetchAll($sublevelClubsSql, array('defaultLang' => $defaultLang));

        return $rowClubsArray;
    }

    

    /**
     * Function to get club data.
     *
     * @param type $clubId
     *
     * @return array
     */
    public function getClubData($clubId)
    {
        $clubDataQuery = 'SELECT C.id AS clubId,C.title,C.year,C.email,C.billing_id,website,C.correspondence_id,'
            . 'CA.company,CA.street AS sp_street,CA.pobox as sp_pobox,CA.city as sp_city,CA.zipcode as sp_zipcode,'
            . 'CA.state as sp_state,CA.country as sp_country,CA.language as sp_lang,CA.co as sp_co,'
            . ' IA.street AS in_street,IA.pobox as in_pobox,IA.city as in_city,IA.zipcode as in_zipcode,IA.state as in_state,'
            . ' IA.country as in_country,IA.language as in_lang,IA.co as in_co, C.club_number FROM fg_club C'
            . ' LEFT JOIN fg_club_address CA ON CA.id=C.correspondence_id'
            . ' LEFT JOIN fg_club_address IA ON IA.id=C.billing_id'
            . ' WHERE C.id=:clubId';
        $clubData = $this->adminManagerconn->fetchAll($clubDataQuery, array('clubId' => $clubId));
        $clubData[0]['i18n'] = $this->getClubI18Settings($clubId);

        return $clubData[0];
    }

    /**
     * Method to get club settings (name, logo, signature) in all correspondence language
     *
     * @param int $clubId clubId
     *
     * @return array of club settings (name, logo, signature) in all correspondence language
     */
    public function getClubI18Settings($clubId)
    {
        $clubDetai18nArray = array();

        $clubDetai18nQuery = 'SELECT Si18n.signature_lang, Si18n.logo_lang, Si18n.lang FROM `fg_club_settings` S INNER JOIN fg_club_settings_i18n Si18n ON Si18n.id = S.id WHERE S.club_id=:clubId';
        $clubDatai18nArrayResult = $this->conn->fetchAll($clubDetai18nQuery, array('clubId' => $clubId));
        foreach ($clubDatai18nArrayResult as $clubDatai18n) {
            $clubDetai18nArray['signature'][$clubDatai18n['lang']] = $clubDatai18n['signature_lang'];
            $clubDetai18nArray['logo'][$clubDatai18n['lang']] = $clubDatai18n['logo_lang'];
        }

        $clubDetai18nQuery = 'SELECT CI18n.title_lang,CI18n.lang FROM fg_club_i18n CI18n WHERE CI18n.id=:clubId ';
        $clubDatai18nArrayResult = $this->adminManagerconn->fetchAll($clubDetai18nQuery, array('clubId' => $clubId));
        foreach ($clubDatai18nArrayResult as $clubDatai18n) {
            $clubDetai18nArray['title'][$clubDatai18n['lang']] = $clubDatai18n['title_lang'];
        }
        return $clubDetai18nArray;
    }

    /**
     * Function to save club data.
     *
     * @param type $formData         Updated form data
     * @param type $clubData         current club data
     * @param type $contactId        Contact id
     * @param type $fieldsTitleArray Fields title array
     * @param type $domainCacheKey   Cache key used for caching
     */
    public function saveClubData($formData, $clubData, $contactId, $fieldsTitleArray, $domainCacheKey, $container)
    {
        $sameAs = $formData['same_invoice_address'] ? '1' : '0';
        $deleteQuery = false;
        try {
            $this->adminManagerconn->beginTransaction();
            $coressQuery = 'UPDATE fg_club_address SET co=:sp_co,street=:sp_street,pobox=:sp_pobox,zipcode=:sp_zipcode,city=:sp_city,state=:sp_state,country=:sp_country,language=:sp_lang,updated_at=NOW() WHERE id=:correspondence_id';
            $this->adminManagerconn->executeQuery($coressQuery, array('sp_co' => $formData['Correspondence']['sp_co'], 'sp_street' => $formData['Correspondence']['sp_street'], 'sp_pobox' => $formData['Correspondence']['sp_pobox'], 'sp_zipcode' => $formData['Correspondence']['sp_zipcode'], 'sp_city' => $formData['Correspondence']['sp_city'], 'sp_state' => $formData['Correspondence']['sp_state'], 'sp_country' => $formData['Correspondence']['sp_country'], 'sp_lang' => $formData['system']['sp_lang'], 'correspondence_id' => $clubData['correspondence_id']));
            $invoiceId = $clubData['billing_id'];
            if ($clubData['correspondence_id'] != $clubData['billing_id']) {
                if ($sameAs == '1') {
                    $invoiceId = $clubData['correspondence_id'];
                    $deleteQuery = true;
                } else {
                    $coressQuery = 'UPDATE fg_club_address SET co=:in_co,street=:in_street,pobox=:in_pobox,zipcode=:in_zipcode,city=:in_city,state=:in_state,country=:in_country, language= :in_lang, updated_at=NOW() WHERE id=:billing_id';
                    $this->adminManagerconn->executeQuery($coressQuery, array('in_co' => $formData['Invoice']['in_co'], 'in_street' => $formData['Invoice']['in_street'], 'in_pobox' => $formData['Invoice']['in_pobox'], 'in_zipcode' => $formData['Invoice']['in_zipcode'], 'in_city' => $formData['Invoice']['in_city'], 'in_state' => $formData['Invoice']['in_state'], 'in_country' => $formData['Invoice']['in_country'], 'in_lang' => $formData['system']['sp_lang'], 'billing_id' => $clubData['billing_id']));
                }
            } elseif ($sameAs != '1') {
                $invoiceQuery = 'INSERT INTO fg_club_address (co,street,pobox,city,zipcode,state,country,created_at,updated_at) VALUES (:in_co,:in_street,:in_pobox,:in_city,:in_zipcode,:in_state,:in_country,NOW(),NOW())';
                $this->adminManagerconn->executeQuery($invoiceQuery, array('in_co' => $formData['Invoice']['in_co'], 'in_street' => $formData['Invoice']['in_street'], 'in_pobox' => $formData['Invoice']['in_pobox'], 'in_zipcode' => $formData['Invoice']['in_zipcode'], 'in_city' => $formData['Invoice']['in_city'], 'in_state' => $formData['Invoice']['in_state'], 'in_country' => $formData['Invoice']['in_country']));
                $invoiceId = $this->adminManagerconn->lastInsertId();
            }
            $clubNumber = !isset($formData['system']['club_number']) ? $clubData['club_number'] : $formData['system']['club_number'];
            $clubNumber = ($clubNumber == '') ? 'NULL' : $clubNumber;
            $clubQuery = "UPDATE fg_club SET title=:title,year=:year,email=:email,website=:website,billing_id=:billing_id,last_updated=NOW(),club_number=$clubNumber WHERE id=:clubId";
            $this->adminManagerconn->executeQuery($clubQuery, array('title' => $formData['system']['title'], 'year' => $formData['system']['year'], 'email' => $formData['system']['email'], 'website' => $formData['system']['website'], 'billing_id' => $invoiceId, 'clubId' => $clubData['clubId']));
            if ($deleteQuery) {
                $this->adminManagerconn->executeQuery('DELETE FROM fg_club_address WHERE id=:addrId', array('addrId' => $clubData['billing_id']));
            }
            $fedIcon_query = ($clubData['fedIcon_Visibility']) ? ',federation_icon="' . $clubData['fed_icon'] . '"' : '';
            $clubLogoQuery = "UPDATE fg_club_settings SET signature=:signature,logo=:logo" . $fedIcon_query . " WHERE club_id=:clubId";
            $this->conn->executeQuery($clubLogoQuery, array('signature' => $clubData['signature'], 'logo' => $clubData['logo'], 'clubId' => $clubData['clubId']));

            $this->insertClubDataLog($formData, $clubData, $contactId, $fieldsTitleArray);
            $this->adminManagerconn->commit();
            
            //Remove apc cache entries while updating the data
            $club = $container->get('club');
            $cachingEnabled = $club->get('caching_enabled');
            $clubCacheKey   = $club->get('clubCacheKey');
            $prefixName = '_clubdetails_';           
            if ($cachingEnabled) {
                $cacheDriver = $this->adminEntityManager->getConfiguration()->getResultCacheImpl();           
                $cacheDriver->setPrefixValueForDelete($clubCacheKey, $prefixName);
                if ($clubData['is_FediconChanged']) {
                    $prefixName = '_clublevels_';
                    $cacheDriver->setPrefixValueForDelete($clubCacheKey, $prefixName);
                }                                
            }
            //Remove apc cache entries while updating the data
        } catch (Exception $ex) {
            $this->adminManagerconn->rollback();
            throw $ex;
        }
    }

    /**
     * Function to insert club data log.
     *
     * @param type $formData         updated form data
     * @param type $clubData         current club data
     * @param type $contactId        Contact id
     * @param type $fieldsTitleArray Fields title array
     */
    private function insertClubDataLog($formData, $clubData, $contactId, $fieldsTitleArray)
    {
        $logQuery = 'INSERT INTO fg_club_log (`club_id`,`date`,`kind`,`field`,`value_before`,`value_after`,`changed_by_name`,`changed_by`) VALUES ';
        $logQueryValue = array();
        $sameForOld = ($clubData['correspondence_id'] == $clubData['billing_id']) ? '1' : '0';
        $sameAsNew = $formData['same_invoice_address'] ? '1' : '0';
        
        $contactTable = $this->adminEntityManager->getRepository('AdminUtilityBundle:FgCmContact')->find($contactId);
        $contactName = $contactTable->getName();
        foreach ($formData['system'] as $name => $formCorres) {

            if(strpos($name, 'title_') === 0)
                continue;

            if ($formCorres != $clubData[$name]) {
                if ($name == 'sp_lang') {
                    $languages = FgUtility::getClubLanguageNames(array($clubData[$name], $formCorres));
                    $oldValues = $languages[$clubData[$name]];
                    $newValues = $languages[$formCorres];
                } else {
                    $oldValues = FgUtility::getSecuredData($clubData[$name], $this->adminManagerconn);
                    $newValues = FgUtility::getSecuredData($formCorres, $this->adminManagerconn);
                }
                $logQueryValue[] = ' (' . $clubData['clubId'] . ",NOW(),'data','" . $fieldsTitleArray[$name] . "','" . $oldValues . "','" . $newValues . "','" . $contactName . "',$contactId)";
            }
        }
        foreach ($formData['Correspondence'] as $name => $formCorres) {
            if ($formCorres != $clubData[$name]) {
                if ($name == 'sp_country') {
                    $country = FgUtility::getCountryList($formCorres);
                    $newValue = empty($formCorres) ? '' : $country[$formCorres];
                    $oldValue = empty($clubData[$name]) ? '' : $country[$clubData[$name]];
                } else {
                    $newValue = FgUtility::getSecuredData($formCorres, $this->adminManagerconn);
                    $oldValue = FgUtility::getSecuredData($clubData[$name], $this->adminManagerconn);
                }

                $logQueryValue[] = ' (' . $clubData['clubId'] . ",NOW(),'data','" . $fieldsTitleArray[$name] . "','" . $oldValue . "','" . $newValue . "','" . $contactName . "',$contactId)";
                if ($sameAsNew == '1') {
                    $inField = explode('_', $name, 2);
                    $inField = 'in_' . $inField[1];
                    $logQueryValue[] = ' (' . $clubData['clubId'] . ",NOW(),'data','" . $fieldsTitleArray[$inField] . "','" . $oldValue . "','" . $newValue . "','" . $contactName . "',$contactId)";
                }
            }
        }
        foreach ($formData['Invoice'] as $name => $formCorres) {
            if ($formCorres != $clubData[$name]) {
                if ($name == 'in_country') {
                    $country = FgUtility::getCountryList($formCorres);
                    $newVal = empty($formCorres) ? '' : $country[$formCorres];
                    $oldVal = empty($clubData[$name]) ? '' : $country[$clubData[$name]];
                } else {
                    $newVal = FgUtility::getSecuredData($formCorres, $this->adminManagerconn);
                    $oldVal = FgUtility::getSecuredData($clubData[$name], $this->adminManagerconn);
                }
                $logQueryValue[] = ' (' . $clubData['clubId'] . ",NOW(),'data','" . $fieldsTitleArray[$name] . "','" . $oldVal . "','" . $newVal . "','" . $contactName . "',$contactId)";
            }
        }
        if ($logQueryValue[0] != '') {
            $this->adminManagerconn->executeQuery($logQuery . implode(',', $logQueryValue));
        }
    }

    

    /**
     * function to get hierarchy clubs (sublevel clubs where is fed is 0).
     *
     * @return array hierarchy clubs
     */
    public function getHierarchyClubs()
    {
        $clubId = $this->container->get('club')->get('id');

        return $this->adminManagerconn->fetchAll("SELECT c.id as id, c.title as title FROM (SELECT sublevelClubs(id) AS id, @level AS level FROM "
                . "(SELECT  @start_with :='{$clubId}',@id := @start_with,@level := 0) vars, fg_club WHERE @id IS NOT NULL) c1 JOIN fg_club c ON c.id = c1.id AND (c.is_federation = 0)");
    }

    /**
     * Function to All sub level clubs for serching from top navigation menu.
     *
     * @param Integer $clubId Club id
     * @param string  $term   Term for search
     *
     * @return Array $rowClubsArray Array of id and title
     */
    public function getClubsForSearch($clubId, $term)
    {
        $sublevelClubsSql = "SELECT c.id, c.title, is_sub_federation "
            . "FROM (SELECT  sublevelClubs(id) AS id, @level AS level FROM (SELECT  @start_with := '$clubId',@id := @start_with,@level := 0) vars, "
            . "fg_club"
            . " WHERE @id IS NOT NULL) ho JOIN fg_club c ON c.id = ho.id WHERE c.title like '$term%'";
        $rowClubsArray = $this->adminManagerconn->fetchAll($sublevelClubsSql);

        return $rowClubsArray;
    }

    /**
     * Function to update the settings updated date as current date.
     *
     * @param int   $clubId Club Id.
     *
     * @return int subscribed boolean true/false .
     */
    public function saveSettingsUpdatedDate($clubId)
    {
        //Get the clubs below the current club
        $clubDataQuery = 'UPDATE fg_club SET settings_updated = now() WHERE federation_id = :clubId OR id = :clubId';
        $this->adminManagerconn->executeQuery($clubDataQuery, array('clubId' => $clubId));
    }

    
    /**
     *
     * @param array $formValues     The array that contains the title and signature i18n data
     * @param array $logoValues     The array that contains the lclub logo data
     * @param array $clubId         The club which is been edited
     * @param array $clubLanguages  The languages of the club
     * @param array $domainCacheKey The cache key of this domain
     *
     * return void
     */
    public function saveClubi18nData($formValues, $logoValues, $clubId, $clubLanguages, $domainCacheKey){
        $clubSettingsObj = $this->em->getRepository('CommonUtilityBundle:FgClubSettings')->findOneBy(array('club' => $clubId));
        if($clubSettingsObj->getId() != ''){
            $settingsId = $clubSettingsObj->getId();
            foreach($clubLanguages as $language){
                $title = $formValues['system']["title_$language"];
                $signature = $formValues['Notification']["signature_$language"];
                $logo = $logoValues["$language"];
                $settingsQuery = "INSERT INTO fg_club_settings_i18n (id,signature_lang,logo_lang,lang) VALUES (:settingsId,:signature,:logo,:lang) ON DUPLICATE KEY UPDATE signature_lang=:signature, logo_lang=:logo";
                $this->conn->executeQuery($settingsQuery, array('settingsId' => $settingsId,'signature' => $signature,'logo' => $logo,'lang' => $language));

                $clubQuery = "INSERT INTO fg_club_i18n (id,title_lang,lang) VALUES (:clubId,:title,:lang) ON DUPLICATE KEY UPDATE title_lang = :title";
                $this->adminManagerconn->executeQuery($clubQuery, array('clubId' => $clubId,'title' => $title,'lang' => $language));
            }
        }

        //Remove apc cache entries while updating the data
        $cachingEnabled = $this->container->get('club')->get('caching_enabled');
        $prefixName = 'club_language';
        if ($cachingEnabled) {
            $cacheDriver = $this->em->getConfiguration()->getResultCacheImpl();
            $cacheDriver->setPrefixValueForDelete($domainCacheKey, $prefixName);
            $cacheKey = $this->container->getParameter('database_name') . '_clubdetails_' . ($this->container->get('club')->get('clubUrlIdentifier') != '' ? $this->container->get('club')->get('clubUrlIdentifier') : $this->container->get('club')->get('id'));
            $cacheDriver->deleteByPrefix($cacheKey);
        }
        //Remove apc cache entries while updating the data

        return;
    }
}
