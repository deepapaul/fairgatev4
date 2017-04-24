<?php

/**
 * CmsPdo
 */
namespace Common\UtilityBundle\Repository\Pdo;

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
     * Constructor for initial setting.
     *
     * @param object $container Container Object
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->conn = $this->container->get('database_connection');
        $this->em = $this->container->get('doctrine')->getManager();
    }

    /**
     * Function to update club assignment date
     * @param type $clubAssignmentId Club assignment id
     * @param type $column           Column
     * @param type $val              Value
     *
     * @return Null
     */
    public function updateClubAssignmentDate($clubAssignmentId = '', $column = '', $val = '')
    {

        if ($clubAssignmentId != '') {

            $sql = "UPDATE fg_club_assignment ca SET ca." . $column . " = '" . $val . "' WHERE ca.id =:cId";
            $this->conn->executeQuery($sql, array(":cId" => $clubAssignmentId));
        }

        return;
    }

    /**
     * Validate assignment date
     * @param type $contactId        Contact id
     * @param type $dateSqlVal       Date
     * @param type $clubHierarchy    Club
     * @param type $clubAssignmentId Club assignment id
     * @return type
     */
    public function validateAssignmentDate($contactId, $dateSqlVal, $clubHierarchy, $clubAssignmentId)
    {
        $clubAssignment = $this->em->getRepository('CommonUtilityBundle:FgClubAssignment')->find($clubAssignmentId);

        $dateFormat = FgSettings::getMysqlDateFormat();
        $clubId = $clubAssignment->getClub()->getId();
        $fedContactId = $clubAssignment->getFedContact()->getId();
        $query = "SELECT
                (DATE_FORMAT(ca.from_date, '$dateFormat')) as fromDate,
                (DATE_FORMAT(ca.to_date, '$dateFormat')) as toDate
                 FROM fg_club_assignment ca
                 WHERE ca.club_id = $clubId
                 AND ('$dateSqlVal' BETWEEN ca.from_date AND (CASE WHEN (ca.to_date IS NULL OR ca.to_date='') THEN CURDATE() ELSE ca.to_date END))
                 AND ca.id != $clubAssignmentId
                 AND ca.fed_contact_id = $fedContactId";

        $result = $this->conn->fetchAll($query);

        return $result;
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

        $rowClubsArray = $this->conn->fetchAll($sublevelClubsSql, array('defaultLang' => $defaultLang));

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
        $resultArray = $this->conn->fetchAll(
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
        $resultArray = $this->conn->fetchAll(
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
            $this->conn->executeQuery($query);
        }
        if (!empty($insert)) {
            $query = "INSERT INTO fg_club_class_assignment (assined_federation_id, club_id, class_id) VALUES " . implode(',', $insert) . ";";
            $this->conn->executeQuery($query);
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
            $logQuery = "INSERT INTO fg_club_class_log (club_id, class_id, date, kind, field, value_before, value_after, changed_by_contact) VALUES " . implode(',', $log) . ";";
            $this->conn->executeQuery($logQuery);
        }
        if (!empty($clublog)) {
            $logquery = "INSERT INTO fg_club_log (club_id, date, kind, field, value_before, value_after, changed_by, class_log_id) VALUES " . implode(',', $clublog) . ";";
            $this->conn->executeQuery($logquery);
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

        $dataResult = $this->conn->fetchAll($bookmarkSql, array(':contactsId' => $contactId, ':clubId' => $clubId, 'defaultLang' => $defaultLang));

        return $dataResult;
    }

    /**
     * get Terminology Details
     *
     * @param int    $clubid         Club id
     * @param int    $langid         language id
     * @param int    $fedid          Federation id
     * @param string $clubSystemLang Club default system language
     * @param string $domainCacheKey domain Cache Key
     * @param int    $cacheLifeTime  cache Life-Time in ms
     *
     * @return array of terminology details
     */
    public function getTerminologyDetails($clubid, $langid, $fedid, $clubSystemLang, $domainCacheKey, $cacheLifeTime)
    {
        $cacheKey = str_replace('{{cache_area}}', 'terminology', $domainCacheKey) . '_' . $langid . '_' . $clubSystemLang;
        if (($fedid == "") or ( $fedid == " ")) {
            $fedid = 0;
        }
        $sql = "SELECT fg1.id,fg1.default_singular_term as defaultSingularTerm , fg1.default_plural_term as defaultPluralTerm ,
                IF( (fgi18.singular_lang IS NULL) or (fgi18.singular_lang='') , fg1i18.singular_lang , fgi18.singular_lang ) AS singular,
                IF( (fgi18.plural_lang IS NULL) or (fgi18.plural_lang='') , fg1i18.plural_lang , fgi18.plural_lang ) AS plural
                FROM  fg_club_terminology  fg1 LEFT JOIN  fg_club_terminology fg2
                ON fg1.default_singular_term = fg2.default_singular_term
                AND fg2.club_id =$clubid
                LEFT JOIN  fg_club_terminology_i18n fgi18 ON fgi18.id = fg2.id
                AND lang =  '$langid' and fg2.is_federation=0
                LEFT JOIN fg_club_terminology_i18n fg1i18 ON fg1i18.id = fg1.id AND fg1i18.lang =  '$clubSystemLang'
                WHERE fg1.club_id =1 and fg1.is_federation=0

                union all

                SELECT fg1.id,fg1.default_singular_term as defaultSingularTerm , fg1.default_plural_term as defaultPluralTerm ,
                IF( (fgi18.singular_lang IS NULL) or (fgi18.singular_lang='') , fg1i18.singular_lang , fgi18.singular_lang ) AS singular,
                IF( (fgi18.plural_lang IS NULL) or (fgi18.plural_lang='') , fg1i18.plural_lang , fgi18.plural_lang ) AS plural
                FROM  fg_club_terminology  fg1
                LEFT JOIN  fg_club_terminology fg2 ON fg1.default_singular_term = fg2.default_singular_term
                AND fg2.club_id =$fedid
                LEFT JOIN  fg_club_terminology_i18n fgi18 ON fgi18.id = fg2.id
                AND lang =  '$langid' and fg2.is_federation=1
                LEFT JOIN fg_club_terminology_i18n fg1i18 ON fg1i18.id = fg1.id AND fg1i18.lang =  '$clubSystemLang'
                WHERE fg1.club_id =1 and fg1.is_federation=1";

        $stmt = $this->conn->executeQuery($sql, array(), array(), new QueryCacheProfile($cacheLifeTime, $cacheKey));
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt->closeCursor(); // very important, do not forget

        return $result;
    }

    /**
     * Function to update terminology
     *
     * @param int $termid       term id
     * @param string $lang         Language
     * @param string $singularLang singular language
     * @param string $pluralLang   Plural language
     * @param int $isActive     is active
     *
     * @return int
     */
    public function updateterminology($termid, $lang, $singularLang, $pluralLang, $isActive)
    {

        $query = " UPDATE `fg_club_terminology_i18n` SET id = :termid,singular_lang = :singularLang, plural_lang = :pluralLang, lang = :lang, is_active = :isActive WHERE id = :termid AND lang = :lang";
        $this->conn->executeQuery($query, array('termid' => $termid, 'singularLang' => $singularLang, 'pluralLang' => $pluralLang, 'lang' => $lang, 'isActive' => $isActive));

        return true;
    }

    /**
     * Function to insert terminology
     *
     * @param type $termid       Term id
     * @param type $singularLang Singular language
     * @param type $pluralLang   plural language
     * @param type $lang         Language
     * @param type $isActive     is active
     *
     * @return int
     */
    public function insertterminology($termid, $singularLang, $pluralLang, $lang, $isActive)
    {

        $query = "INSERT INTO `fg_club_terminology_i18n` (`id`, `singular_lang`, `plural_lang`, `lang`, `is_active`)
                 VALUES (:termid, :singularLang, :pluralLang, :lang, :isActive)";

        $this->conn->executeQuery($query, array('termid' => $termid, 'singularLang' => $singularLang, 'pluralLang' => $pluralLang, 'lang' => $lang, 'isActive' => $isActive));

        return true;
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
        $sql = "SELECT c.id,c.club_id,c.kind,c.field,c.value_before,c.value_after,c.changed_by,c.date AS dateOriginal,date_format( c.date,'" . $dateFormat . "') AS date, checkActiveContact(c.changed_by, $currentClubId) as activeContact,
                IF((checkActiveContact(c.changed_by, $currentClubId) is null && c.changed_by != 1), CONCAT(contactName(c.changed_by),' (',
                    (select (CASE WHEN (ci18n.title_lang IS NULL OR ci18n.title_lang = '') THEN fc.title ELSE ci18n.title_lang END) as title
                    from fg_cm_contact ct
                    left join fg_club fc on ct.main_club_id = fc.id
                    LEFT JOIN fg_club_i18n ci18n ON ci18n.id = fc.id AND ci18n.lang = '$defaultLang'
                    where ct.id = c.changed_by),')') , contactName(c.changed_by) )as editedBy,
                 (CASE WHEN ((c.value_before IS NOT NULL AND c.value_before != '' AND c.value_before != '-') AND (c.value_after IS NOT NULL AND c.value_after != '' AND c.value_after != '-')) THEN 'changed'
                       WHEN ((c.value_before IS NOT NULL AND c.value_before != '' AND c.value_before != '-') AND (c.value_after IS NULL OR c.value_after = '' OR c.value_after = '-')) THEN 'removed'
                       WHEN ((c.value_before IS NULL OR c.value_before = '' OR c.value_before = '-') AND (c.value_after IS NOT NULL AND c.value_after != '' AND c.value_after != '-')) THEN 'added'
                       ELSE 'none'
                 END) AS status
                 FROM fg_club_log c WHERE c.club_id= :clubId AND c.kind='data'";
        $result = $this->conn->fetchAll($sql, array('clubId' => $clubId));

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
        $sql = "SELECT c.id,c.club_id,c.kind,c.field,c.value_before,c.value_after,c.changed_by,c.date AS dateOriginal,date_format( c.date,'" . $dateFormat . "') AS date, checkActiveContact(c.changed_by, $currentClubId) as activeContact,
                IF((checkActiveContact(c.changed_by, $currentClubId) is null && c.changed_by != 1), CONCAT(contactName(c.changed_by),' (',($clubTitleQuery),')') , contactName(c.changed_by) )as editedBy,
                 (CASE WHEN ((c.value_before IS NOT NULL AND c.value_before != '' AND c.value_before != '-') AND (c.value_after IS NULL OR c.value_after = '' OR c.value_after = '-')) THEN 'removed'
                       WHEN ((c.value_before IS NULL OR c.value_before = '' OR c.value_before = '-') AND (c.value_after IS NOT NULL AND c.value_after != '' AND c.value_after != '-')) THEN 'added'
                       ELSE 'none'
                 END) AS status
                 FROM fg_club_log c " . $join . " WHERE c.club_id= :clubId AND c.kind='assigned club'" . $where;
        $result = $this->conn->fetchAll($sql, array('clubId' => $clubId));

        return $result;
    }

    /**
     * Function to get the log entries of club notes
     *
     * @param int $clubId        ClubId of the logs listed
     * @param int $currentClubId Current club Id
     *
     * @return array $result
     */
    public function getNotesLogEntries($clubId, $currentClubId)
    {
        $dateFormat = FgSettings::getMysqlDateTimeFormat();
        $defaultLang = $this->container->get('club')->get('default_lang');
        $clubTitleQuery = "SELECT COALESCE(NULLIF(Ci18N.title_lang,''),FC.title) AS title FROM fg_cm_contact CT LEFT JOIN fg_club FC ON CT.main_club_id = FC.id LEFT JOIN fg_club_i18n Ci18N ON Ci18N.id = FC.id AND Ci18N.lang = '$defaultLang' WHERE CT.id = c.changed_by";
        $sql = "SELECT c.id,c.value_before,c.value_after,c.changed_by,c.date AS dateOriginal,date_format( c.date,'" . $dateFormat . "') AS date, checkActiveContact(c.changed_by, $currentClubId) as activeContact,
                IF((checkActiveContact(c.changed_by, $currentClubId) is null && c.changed_by != 1), CONCAT(contactName(c.changed_by),' (',($clubTitleQuery),')') , contactName(c.changed_by) )as editedBy,
                 (CASE WHEN ((c.value_before IS NOT NULL AND c.value_before != '' AND c.value_before != '-') AND (c.value_after IS NULL OR c.value_after = '' OR c.value_after = '-')) THEN 'removed'
                       WHEN ((c.value_before IS NULL OR c.value_before = '' OR c.value_before = '-') AND (c.value_after IS NOT NULL AND c.value_after != '' AND c.value_after != '-')) THEN 'added'
                       WHEN ((c.value_before IS NOT NULL AND c.value_before != '' AND c.value_before != '-') AND (c.value_after IS NOT NULL AND c.value_after != '' AND c.value_after != '-')) THEN 'changed'
                       ELSE 'none'
                 END) AS status,
                 (IF((c.value_before IS NOT NULL AND c.value_before != '' AND c.value_before != '-'), c.value_before, '')) AS valueBefore,
                 (IF((c.value_after IS NOT NULL AND c.value_after != '' AND c.value_after != '-'), c.value_after, '')) AS valueAfter
                 FROM fg_club_log_notes c
                 WHERE c.note_club_id=:clubId AND c.type='club' AND c.assigned_club_id=:assignedClubId";
        $result = $this->conn->fetchAll($sql, array('clubId' => $clubId, 'assignedClubId' => $currentClubId));

        return $result;
    }

    /**
     * Function to get all classification Id
     * @param int $classificationId   classification ID

     * * * @return array $result
     */
    public function getAllClassificationId($classificationId)
    {

        return $this->conn->fetchAll("SELECT GROUP_CONCAT(c.id) AS ids FROM fg_club_class c WHERE c.classification_id='$classificationId' ");
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

        $stmt = $this->conn->executeQuery($qry);

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
        $dataResult = $this->conn->fetchAll($sql);

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
        $sql = "SELECT cl.id, cl.club_id, cl.date AS date, cl.kind, cl.field, cl.value_before, cl.value_after,cl.date AS dateOriginal,date_format(cl.date,'" . $dateFormat . "') AS date,
                IF((cl.kind = 'data'), 'data', 'assignments') AS tabGroups, checkActiveContact(cl.changed_by_contact, $clubId) as activeContact,
                IF((checkActiveContact(cl.changed_by_contact, $clubId) is null && cl.changed_by_contact != 1), CONCAT(contactName(cl.changed_by_contact),' (',(select fc.title from fg_cm_contact ct left join fg_club fc on ct.main_club_id = fc.id where ct.id = cl.changed_by_contact),')') , contactName(cl.changed_by_contact) )as editedBy,
                (CASE WHEN ((cl.value_before IS NOT NULL AND cl.value_before != '' AND cl.value_before != '-') AND (cl.value_after IS NULL OR cl.value_after = '' OR cl.value_after = '-')) THEN 'removed'
                       WHEN ((cl.value_before IS NULL OR cl.value_before = '' OR cl.value_before = '-') AND (cl.value_after IS NOT NULL AND cl.value_after != '' AND cl.value_after != '-')) THEN 'added'
                       WHEN ((cl.value_before IS NOT NULL AND cl.value_before != '' AND cl.value_before != '-') AND (cl.value_after IS NOT NULL AND cl.value_after != '' AND cl.value_after != '-') AND (cl.value_before != cl.value_after)) THEN 'changed'
                       ELSE 'none'
                END) AS status,
                (IF((cl.value_after='' OR cl.value_after IS NULL OR cl.value_after='-'),cl.value_before,cl.value_after)) AS columnVal3
                FROM fg_club_class_log cl WHERE cl.kind IN ('data', 'assigned club')
                $cond
                ORDER BY DATE(cl.date) DESC";
        $result = $this->conn->fetchAll($sql, array(':classId' => $classId));

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

        $dataResult = $this->conn->fetchAll($sql);
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




        $dataResult = $this->conn->fetchAll($sql);
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
    /*     * *
     * function to execute Query
     *
     * @param string Query
     */

    public function executeQuery($query)
    {

        $this->conn->executeQuery($query);

        return $this->conn->lastInsertId();
    }

    /**
     * Function add to transaction
     *
     * @param type $updateFullQry update query
     * @param type $delString     delete string
     *
     * @return boolean
     *
     * @throws \Common\UtilityBundle\Repository\Exception
     */
    public function addtotransaction($updateFullQry, $delString = '')
    {
        /*         * ******** BEGIN TRANSACTION ******** */
        $rollback = false;
        if ($updateFullQry !== '' || $delString !== '') {

            try {
                $this->conn->beginTransaction();
                if ($updateFullQry !== '') {
                    $this->conn->executeQuery($updateFullQry);
                }
                $this->conn->commit();
            } catch (Exception $ex) {
                $this->conn->rollback();
                $rollback = true;
                echo "Failed: " . $ex->getMessage();
                throw $ex;
            }
            if ($delString != '' && !$rollback) {
                $stmt = $this->conn->executeQuery($delString);
            }
            $this->conn->close();

            return true;
        }
        /*         * ******** END TRANSACTION ******** */
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
        $rowClubsArray = $this->conn->fetchAll($sublevelClubsSql, array('defaultLang' => $defaultLang));

        return $rowClubsArray;
    }

    /**
     * function to get the club and its level details(federation,subfederation).
     *
     * @param int     $parentClubId   The parent club id
     * @param Integer $domainCacheKey Cachekey used for caching
     * @param Integer $cacheLifeTime  Cache expiry time
     *
     * @return array
     */
    public function getClubLevels($parentClubId, $domainCacheKey = '', $cacheLifeTime = '')
    {
        //$cacheKey = str_replace('{{cache_area}}', 'club_levels', $domainCacheKey);
        $cacheKey = $domainCacheKey . '_clublevels_' . $parentClubId;
        $clubSql = 'SELECT '
            . '@clubid AS Club_id,'
            . '(SELECT is_federation FROM fg_club WHERE id = Club_id) AS is_federation,'
            . '(SELECT title FROM fg_club WHERE id = Club_id) AS title ,'
            . '(SELECT is_sub_federation FROM fg_club WHERE id = Club_id) AS is_sub_federation,'
            . '(SELECT federation_icon FROM fg_club_settings WHERE club_id = @clubid) AS federationIcon,'
            . '(SELECT club_type FROM fg_club WHERE id = Club_id) AS club_type,'
            . '(SELECT subfed_level FROM fg_club WHERE id = Club_id) AS subfed_level,'
            . '(SELECT url_identifier FROM fg_club WHERE id = Club_id) AS url_identifier,'
            . '(SELECT @clubid := parent_club_id FROM fg_club WHERE id = Club_id) AS parent,'
            . '@level := @level+ 1 AS level '
            . "FROM (SELECT @clubid :='" . $parentClubId . "', @level:= 0) vars, fg_club h WHERE @clubid > 1 ORDER BY level DESC";

        if ($domainCacheKey) {
            $stmt = $this->conn->executeQuery($clubSql, array(), array(), new QueryCacheProfile($cacheLifeTime, $cacheKey));
            $resultClubs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $stmt->closeCursor(); // very important, do not forget
        } else {
            $resultClubs = $this->conn->fetchAll($clubSql);
        }

        return $resultClubs;
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
        $clubData = $this->conn->fetchAll($clubDataQuery, array('clubId' => $clubId));
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
        $clubDatai18nArrayResult = $this->conn->fetchAll($clubDetai18nQuery, array('clubId' => $clubId));
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
            $this->conn->beginTransaction();
            $coressQuery = 'UPDATE fg_club_address SET co=:sp_co,street=:sp_street,pobox=:sp_pobox,zipcode=:sp_zipcode,city=:sp_city,state=:sp_state,country=:sp_country,language=:sp_lang,updated_at=NOW() WHERE id=:correspondence_id';
            $this->conn->executeQuery($coressQuery, array('sp_co' => $formData['Correspondence']['sp_co'], 'sp_street' => $formData['Correspondence']['sp_street'], 'sp_pobox' => $formData['Correspondence']['sp_pobox'], 'sp_zipcode' => $formData['Correspondence']['sp_zipcode'], 'sp_city' => $formData['Correspondence']['sp_city'], 'sp_state' => $formData['Correspondence']['sp_state'], 'sp_country' => $formData['Correspondence']['sp_country'], 'sp_lang' => $formData['system']['sp_lang'], 'correspondence_id' => $clubData['correspondence_id']));
            $invoiceId = $clubData['billing_id'];
            if ($clubData['correspondence_id'] != $clubData['billing_id']) {
                if ($sameAs == '1') {
                    $invoiceId = $clubData['correspondence_id'];
                    $deleteQuery = true;
                } else {
                    $coressQuery = 'UPDATE fg_club_address SET co=:in_co,street=:in_street,pobox=:in_pobox,zipcode=:in_zipcode,city=:in_city,state=:in_state,country=:in_country, language= :in_lang, updated_at=NOW() WHERE id=:billing_id';
                    $this->conn->executeQuery($coressQuery, array('in_co' => $formData['Invoice']['in_co'], 'in_street' => $formData['Invoice']['in_street'], 'in_pobox' => $formData['Invoice']['in_pobox'], 'in_zipcode' => $formData['Invoice']['in_zipcode'], 'in_city' => $formData['Invoice']['in_city'], 'in_state' => $formData['Invoice']['in_state'], 'in_country' => $formData['Invoice']['in_country'], 'in_lang' => $formData['system']['sp_lang'], 'billing_id' => $clubData['billing_id']));
                }
            } elseif ($sameAs != '1') {
                $invoiceQuery = 'INSERT INTO fg_club_address (co,street,pobox,city,zipcode,state,country,created_at,updated_at) VALUES (:in_co,:in_street,:in_pobox,:in_city,:in_zipcode,:in_state,:in_country,NOW(),NOW())';
                $this->conn->executeQuery($invoiceQuery, array('in_co' => $formData['Invoice']['in_co'], 'in_street' => $formData['Invoice']['in_street'], 'in_pobox' => $formData['Invoice']['in_pobox'], 'in_zipcode' => $formData['Invoice']['in_zipcode'], 'in_city' => $formData['Invoice']['in_city'], 'in_state' => $formData['Invoice']['in_state'], 'in_country' => $formData['Invoice']['in_country']));
                $invoiceId = $this->conn->lastInsertId();
            }
            $clubNumber = !isset($formData['system']['club_number']) ? $clubData['club_number'] : $formData['system']['club_number'];
            $clubNumber = ($clubNumber == '') ? 'NULL' : $clubNumber;
            $clubQuery = "UPDATE fg_club SET title=:title,year=:year,email=:email,website=:website,billing_id=:billing_id,club_number=$clubNumber WHERE id=:clubId";
            $this->conn->executeQuery($clubQuery, array('title' => $formData['system']['title'], 'year' => $formData['system']['year'], 'email' => $formData['system']['email'], 'website' => $formData['system']['website'], 'billing_id' => $invoiceId, 'clubId' => $clubData['clubId']));
            if ($deleteQuery) {
                $this->conn->executeQuery('DELETE FROM fg_club_address WHERE id=:addrId', array('addrId' => $clubData['billing_id']));
            }
            $fedIcon_query = ($clubData['fedIcon_Visibility']) ? ',federation_icon="' . $clubData['fed_icon'] . '"' : '';
            $clubLogoQuery = "UPDATE fg_club_settings SET signature=:signature,logo=:logo" . $fedIcon_query . " WHERE club_id=:clubId";
            $this->conn->executeQuery($clubLogoQuery, array('signature' => $clubData['signature'], 'logo' => $clubData['logo'], 'clubId' => $clubData['clubId']));

            $this->insertClubDataLog($formData, $clubData, $contactId, $fieldsTitleArray);
            $this->conn->commit();

            //Remove apc cache entries while updating the data
            $club = $container->get('club');
            $cachingEnabled = $club->get('caching_enabled');
            $clubCacheKey   = $club->get('clubCacheKey');
            $prefixName = '_clubdetails_';           
            if ($cachingEnabled) {
                $cacheDriver = $this->em->getConfiguration()->getResultCacheImpl();           
                $cacheDriver->setPrefixValueForDelete($clubCacheKey, $prefixName);
                if ($clubData['is_FediconChanged']) {
                    $prefixName = '_clublevels_';
                    $cacheDriver->setPrefixValueForDelete($clubCacheKey, $prefixName);
                }                                
            }
            //Remove apc cache entries while updating the data
        } catch (Exception $ex) {
            $this->conn->rollback();
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
        $logQuery = 'INSERT INTO fg_club_log (`club_id`,`date`,`kind`,`field`,`value_before`,`value_after`,`changed_by`) VALUES ';
        $logQueryValue = array();
        $sameForOld = ($clubData['correspondence_id'] == $clubData['billing_id']) ? '1' : '0';
        $sameAsNew = $formData['same_invoice_address'] ? '1' : '0';

        foreach ($formData['system'] as $name => $formCorres) {

            if(strpos($name, 'title_') === 0)
                continue;

            if ($formCorres != $clubData[$name]) {
                if ($name == 'sp_lang') {
                    $languages = FgUtility::getClubLanguageNames(array($clubData[$name], $formCorres));
                    $oldValues = $languages[$clubData[$name]];
                    $newValues = $languages[$formCorres];
                } else {
                    $oldValues = FgUtility::getSecuredData($clubData[$name], $this->conn);
                    $newValues = FgUtility::getSecuredData($formCorres, $this->conn);
                }
                $logQueryValue[] = ' (' . $clubData['clubId'] . ",NOW(),'data','" . $fieldsTitleArray[$name] . "','" . $oldValues . "','" . $newValues . "',$contactId)";
            }
        }
        foreach ($formData['Correspondence'] as $name => $formCorres) {
            if ($formCorres != $clubData[$name]) {
                if ($name == 'sp_country') {
                    $country = FgUtility::getCountryList($formCorres);
                    $newValue = empty($formCorres) ? '' : $country[$formCorres];
                    $oldValue = empty($clubData[$name]) ? '' : $country[$clubData[$name]];
                } else {
                    $newValue = FgUtility::getSecuredData($formCorres, $this->conn);
                    $oldValue = FgUtility::getSecuredData($clubData[$name], $this->conn);
                }

                $logQueryValue[] = ' (' . $clubData['clubId'] . ",NOW(),'data','" . $fieldsTitleArray[$name] . "','" . $oldValue . "','" . $newValue . "',$contactId)";
                if ($sameAsNew == '1') {
                    $inField = explode('_', $name, 2);
                    $inField = 'in_' . $inField[1];
                    $logQueryValue[] = ' (' . $clubData['clubId'] . ",NOW(),'data','" . $fieldsTitleArray[$inField] . "','" . $oldValue . "','" . $newValue . "',$contactId)";
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
                    $newVal = FgUtility::getSecuredData($formCorres, $this->conn);
                    $oldVal = FgUtility::getSecuredData($clubData[$name], $this->conn);
                }
                $logQueryValue[] = ' (' . $clubData['clubId'] . ",NOW(),'data','" . $fieldsTitleArray[$name] . "','" . $oldVal . "','" . $newVal . "',$contactId)";
            }
        }
        if ($logQueryValue[0] != '') {
            $this->conn->executeQuery($logQuery . implode(',', $logQueryValue));
        }
    }

    /**
     * function to get the club count.
     *
     * @param string $clubtype            the club type
     * @param int    $clubId              the club id
     * @param array  $upperLevelHeirarchy Higher Club heirarchy
     *
     * @return array
     */
    public function getTopNavigationCount($clubtype, $clubId, $upperLevelHeirarchy)
    {
        $clubCount = '0 as clubCount';
        $condtion = " AND fg_cm_contact.club_id = $clubId ";
        $subscriberCondition = '';
        $table = 'club_' . $clubId;
        $contact = 'mc.contact_id = fg_cm_contact.id';
        $clubObj = $this->container->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgClub')->find($clubId);
        $field = 'acl.club_id';
        //echo $clubtype;exit;
        switch ($clubtype) {
            case 'federation':
                $condtion = " AND fg_cm_contact.club_id = $clubId AND ((fg_cm_contact.club_id = fg_cm_contact.main_club_id) OR  (fg_cm_contact.is_fed_membership_confirmed = '0'  AND (fg_cm_contact.fed_membership_cat_id IS NOT NULL OR fg_cm_contact.fed_membership_cat_id != '') ) ) "; //"AND (mc.club_id = $clubId OR (mc.club_id != $clubId AND mc.is_fed_member =1))";
                $subscriberCondition = " AND c.club_id = $clubId AND ((c.club_id = c.main_club_id OR c.fed_membership_cat_id IS NOT NULL) AND (c.is_fed_membership_confirmed='0' OR (c.is_fed_membership_confirmed='1' AND c.old_fed_membership_id IS NOT NULL)) ) "; //"AND (mc.club_id = $clubId OR (mc.club_id != $clubId AND mc.is_fed_member =1))";
                $clubCount = "clubCount($clubId) as clubCount";
                $table = 'federation_' . $clubId;
                $contact = 'mc.fed_contact_id = fg_cm_contact.fed_contact_id';
                $field = 'acl.federation_club_id';
                $applicationConfirmId = $clubObj->getId();
                break;

            case 'sub_federation':
                $condtion = " AND fg_cm_contact.club_id = $clubId AND ((fg_cm_contact.club_id = fg_cm_contact.main_club_id) OR  (fg_cm_contact.is_fed_membership_confirmed = '0'  AND (fg_cm_contact.fed_membership_cat_id IS NOT NULL OR fg_cm_contact.fed_membership_cat_id != '') ) ) "; //"AND (mc.club_id = $clubId OR (mc.club_id != $clubId AND mc.is_fed_member =1))";
                $subscriberCondition = " AND c.club_id = $clubId AND ((c.club_id = c.main_club_id OR c.fed_membership_cat_id IS NOT NULL) AND (c.is_fed_membership_confirmed='0' OR (c.is_fed_membership_confirmed='1' AND c.old_fed_membership_id IS NOT NULL)) ) "; //"AND (mc.club_id = $clubId OR (mc.club_id != $clubId AND mc.is_fed_member =1))";
                $clubCount = "clubCount($clubId) as clubCount";
                $table = 'federation_' . $clubId;
                $contact = 'mc.contact_id = fg_cm_contact.subfed_contact_id';
                $applicationConfirmId = $clubObj->getFederationId();
                break;

            case 'sub_federation_club':
                $applicationConfirmId = $clubObj->getFederationId();
                break;

            case 'standard_club':
            case 'federation_club':
                $applicationConfirmId = $clubObj->getFederationId();
                break;
        }

        $topNavigationCountSql = "SELECT  $clubCount, "
            . '(SELECT '
            . "(SELECT count(s.id) AS subscriberTotalCount FROM fg_cn_subscriber s WHERE club_id = $clubId) + "
            . "(SELECT count(ms.fed_contact_id) AS ownContactTotalCount FROM fg_cm_contact c INNER JOIN master_system ms ON ms.fed_contact_id = c.fed_contact_id AND ms.`3` IS NOT NULL AND ms.`3` != '' AND c.club_id = $clubId "
            . "WHERE c.is_deleted = 0 AND c.is_permanent_delete = 0 AND c.is_subscriber = 1 AND ms.`3` IS NOT NULL AND c.is_draft = 0 $subscriberCondition)) as subscriberCount,"
            . 'COUNT(CASE WHEN fg_cm_contact.is_deleted=1 THEN 1 END ) as archive, '
            . 'COUNT(CASE WHEN fg_cm_contact.is_deleted=0 THEN 1 END ) as active, '
            . 'COUNT(CASE WHEN (fg_cm_contact.is_sponsor = 1 and fg_cm_contact.is_deleted=0) THEN 1 END ) as sponsorCount,  '
            . 'COUNT(CASE WHEN (fg_cm_contact.is_sponsor = 1 and fg_cm_contact.is_deleted=1) THEN 1 END ) as archivedSponsorCount, '
            . "(SELECT COUNT(cc1.id) FROM `fg_cm_change_toconfirm` cc1 WHERE cc1.club_id = $clubId AND cc1.type='change') AS confirmChanges, "
            . "(SELECT COUNT(cc2.id) FROM `fg_cm_change_toconfirm` cc2 WHERE cc2.club_id = $clubId AND cc2.type='mutation' AND cc2.confirm_status = 'NONE') AS confirmMutations, "
            . "(SELECT COUNT(cc3.id) FROM `fg_cm_change_toconfirm` cc3 WHERE cc3.club_id = $clubId AND cc3.type='creation' AND cc3.confirm_status = 'NONE') AS confirmCreations "
            . ", GROUP_CONCAT(fg_cm_contact.id) as contacts , "
            . "(SELECT COUNT(acl.id) from fg_cm_club_assignment_confirmation_log acl where acl.status='PENDING' and {$field} = $clubId ) as confirmappclubassignment "
            . "from fg_cm_contact
                                  INNER JOIN master_system as ms on ms.fed_contact_id = fg_cm_contact.fed_contact_id
                                  INNER JOIN master_{$table} as mc on {$contact}
                                  WHERE fg_cm_contact.is_permanent_delete=0 AND fg_cm_contact.is_draft=0 $condtion ";

        $countResult = $this->conn->fetchAll($topNavigationCountSql);
        //TOPE NAV - DOCUMENT MODULE COUNT
        if (empty($upperLevelHeirarchy)) {
            $upperLevelHeirarchy = array(0 => $clubId);
        }
        $clubIds = implode(',', $upperLevelHeirarchy);

        $docCount = "SELECT "
            . "COUNT(CASE WHEN d.document_type = 'TEAM' THEN 1 END) as teamCount, "
            . "COUNT(CASE WHEN d.document_type = 'WORKGROUP' THEN 1 END) as workgroupCount, "
            . "COUNT(CASE WHEN d.document_type = 'CONTACT' THEN 1 END) as contactCount, "
            . "COUNT(CASE WHEN d.document_type = 'CLUB' THEN 1 END) as clubCount "
            . "FROM fg_dm_documents d "
            . "WHERE IF ((d.document_type = 'CLUB' AND d.deposited_with <> 'NONE' AND d.club_id <> $clubId), IF ((d.deposited_with = 'ALL'),(d.club_id IN ($clubIds) AND d.id NOT IN (SELECT e.document_id FROM fg_dm_assigment_exclude e WHERE e.document_id = d.id AND e.club_id = $clubId)),d.id IN (SELECT da.document_id FROM fg_dm_assigment da WHERE da.document_type = 'CLUB' AND da.club_id = $clubId)),d.club_id = $clubId) ";
        $docResult = $this->conn->fetchAll($docCount);
        //TOPE NAV - DOCUMENT MODULE COUNT

        $applicationConfirmQry = "SELECT count(f0_.id) AS applicationConfirmCount FROM fg_cm_fedmembership_confirmation_log f0_ LEFT JOIN fg_cm_contact f5_ ON (f0_.contact_id = f5_.fed_contact_id AND f0_.club_id = f5_.club_id) WHERE f0_.federation_club_id = " . $applicationConfirmId . " AND f5_.is_deleted = 0 AND f5_.is_fed_membership_confirmed = '1' AND f0_.status = 'PENDING' AND f0_.is_merging = 0 ";
        if ($clubtype != 'federation') {
            $applicationConfirmQry.= " AND f0_.club_id= $clubId";
        }
        $applicationConfirmCount = $this->conn->fetchAll($applicationConfirmQry);
        $countResult[0]['teamDocCount'] = $docResult[0]['teamCount'];
        $countResult[0]['workgroupDocCount'] = $docResult[0]['workgroupCount'];
        $countResult[0]['contactDocCount'] = $docResult[0]['contactCount'];
        $countResult[0]['clubDocCount'] = $docResult[0]['clubCount'];
        $countResult[0]['applicationConfirmCount'] = $applicationConfirmCount[0]['applicationConfirmCount'];


        return $countResult[0];
    }

    /**
     * function to get hierarchy clubs (sublevel clubs where is fed is 0).
     *
     * @return array hierarchy clubs
     */
    public function getHierarchyClubs()
    {
        $clubId = $this->container->get('club')->get('id');

        return $this->conn->fetchAll("SELECT c.id as id, c.title as title FROM (SELECT sublevelClubs(id) AS id, @level AS level FROM "
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
        $rowClubsArray = $this->conn->fetchAll($sublevelClubsSql);

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
        $this->conn->executeQuery($clubDataQuery, array('clubId' => $clubId));
    }

    /**
     * This function is used to get the terminology of passed terms for contact table element columns
     *
     * @param array $terminologyTerms The array of terms whose terminology is to be fetched
     *
     * @return array $terminolgyArr Terminology result array
     */
    public function getTerminologiesForContactTable($terminologyTerms)
    {
        $terminolgyArr = array();
        $clubIds = $this->container->get('club')->get('federation_id') . ',' . $this->container->get('club')->get('id') . ',1';
        $terminologyTerms = "'" . implode("','", $terminologyTerms) . "'";
        $sql = "SELECT T.id, T.club_id AS clubId, "
            . "T.default_singular_term AS defaultSingularTerm, T.default_plural_term AS defaultPluralTerm, "
            . "T.singular AS singularTerm, Ti18n.singular_lang AS singularTermLang, "
            . "T.plural AS pluralTerm, Ti18n.plural_lang AS pluralTermLang, T.is_federation, "
            . "Ti18n.lang FROM  fg_club_terminology T LEFT JOIN  fg_club_terminology_i18n Ti18n ON Ti18n.id = T.id "
            . "WHERE T.club_id IN ($clubIds) AND (T.default_singular_term IN($terminologyTerms)) ";
        $terminologyDetails = $this->conn->fetchAll($sql);

        foreach ($terminologyDetails as $terminologyDetail) {
            $terminolgyArr[$terminologyDetail['defaultSingularTerm']][$terminologyDetail['clubId']]['term'] = $terminologyDetail['singularTerm'];
            $terminolgyArr[$terminologyDetail['defaultSingularTerm']][$terminologyDetail['clubId']]['termLang'][$terminologyDetail['lang']] = $terminologyDetail['singularTermLang'];

            if ($terminologyDetail['defaultPluralTerm'] != '') {
                $terminolgyArr[$terminologyDetail['defaultPluralTerm']][$terminologyDetail['clubId']]['term'] = $terminologyDetail['pluralTerm'];
                $terminolgyArr[$terminologyDetail['defaultPluralTerm']][$terminologyDetail['clubId']]['termLang'][$terminologyDetail['lang']] = $terminologyDetail['pluralTermLang'];
            }
        }

        return $terminolgyArr;
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
                $this->conn->executeQuery($clubQuery, array('clubId' => $clubId,'title' => $title,'lang' => $language));
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
    
     /**
     * Function to get Bookmarks of a perticular Contact.
     * 
     * @param Integer $contactId         Contact Id
     * @param Integer $clubId            Club Id
     * @param String  $clubType          Club Type
     * @param Integer $clubHeirarchy     Sublevel club ids
     * @param Integer $executiveboardId  Current clubs executiveboard id
     * @param Integer $execBoardTerm     Terminology term of executiveboard
     * @param Integer $staticFilterTrans Translation for static filters(singleperson/company/member/sponsor)
     * @param boolean $countFlag         count Flag
     * @param integer $federationId      federation Id
     * @param string  $corrLang          corrLang
     * 
     * @return query result or as processed array based on the $exec parameter
     */
    public function getContactBookmarks($contactId, $clubId, $clubType, $clubHeirarchy, $executiveboardId, $execBoardTerm, $staticFilterTrans,$countFlag=true,$federationId,$corrLang) {
        $federationId = ($clubType == 'federation')? $clubId : $federationId;
        $doctrineConfig = $this->em->getConfiguration();
        $doctrineConfig->addCustomStringFunction('getClubRoleCount', 'Common\UtilityBundle\Extensions\RoleCount');
        if ($clubType === 'federation' || $clubType === 'sub_federation') {
            $field = ($clubType == 'federation')?'mc.fed_contact_id':'mc.contact_id';
            $tablename = 'master_federation_'.$clubId;
            $where     = 'AND (mc.club_id =:clubId OR (mc.club_id != :clubId AND (fg_cm_contact.fed_membership_cat_id IS NOT NULL AND fg_cm_contact.is_fed_membership_confirmed =1)))';
        } else {
            $field = 'mc.contact_id';
            $tablename = 'master_club_'.$clubId;
            $where     = '';
        }

        //$ids = $this->getSubclubs($clubType, $clubId);
        $fedGroupSql='';
        foreach ($clubHeirarchy as $clubsId) {
            $fedGroupSql .= "WHEN (rc.club_id = $clubsId AND rc.is_fed_category=1) THEN 'FROLES' ";
        }

        $groupSql = "(CASE WHEN (rc.club_id = :clubId AND rc.is_fed_category=0
                AND rc.is_team =0 AND rc.is_workgroup =0 AND rc.contact_assign='manual')
                THEN 'ROLES' WHEN (rc.club_id = :clubId AND rc.is_fed_category=0
                AND rc.is_team =0 AND rc.is_workgroup =0 AND rc.contact_assign='filter-driven')
                THEN 'FILTERROLES'
                $fedGroupSql
                WHEN (rc.club_id = $clubId AND rc.is_fed_category=1) THEN 'FROLES'
                WHEN rc.is_team =1 THEN 'TEAM' WHEN rc.is_workgroup=1
                THEN 'WORKGROUP' END) AS roleType";

        $countSqltext = "(CASE   WHEN  bm.type = 'role' THEN (getClubRoleCount(r.id,:clubId))
                      WHEN   bm.type = 'filter' AND bm.filter_id=1  THEN
                            (SELECT  count(fg_cm_contact.id) from  $tablename as mc
                             INNER JOIN fg_cm_contact on {$field} = fg_cm_contact.id
                             INNER JOIN master_system as ms on ms.fed_contact_id = fg_cm_contact.fed_contact_id
                             WHERE fg_cm_contact.is_permanent_delete=0 AND   (fg_cm_contact.is_company=0)$where)
                      WHEN   bm.type = 'filter' AND bm.filter_id=2  THEN
                            (SELECT  count(fg_cm_contact.id) from  $tablename as mc
                             INNER JOIN fg_cm_contact on {$field} = fg_cm_contact.id
                             INNER JOIN master_system as ms on ms.fed_contact_id = fg_cm_contact.fed_contact_id
                             WHERE fg_cm_contact.is_permanent_delete=0 AND   (fg_cm_contact.is_company=1) $where)
                      WHEN   bm.type = 'filter' AND bm.filter_id=3  THEN
                            (SELECT  count(fg_cm_contact.id) from  $tablename as mc
                             INNER JOIN fg_cm_contact on {$field} = fg_cm_contact.id
                              INNER JOIN master_system as ms on ms.fed_contact_id = fg_cm_contact.fed_contact_id
                             WHERE fg_cm_contact.is_permanent_delete=0 AND   (fg_cm_contact.club_membership_cat_id IS NOT NULL) $where)
                      WHEN   bm.type = 'filter' AND bm.filter_id=4  THEN
                            (SELECT  count(fg_cm_contact.id) from  $tablename as mc
                             INNER JOIN fg_cm_contact on {$field} = fg_cm_contact.id
                              INNER JOIN master_system as ms on ms.fed_contact_id = fg_cm_contact.fed_contact_id
                             WHERE fg_cm_contact.is_permanent_delete=0 AND   (fg_cm_contact.fed_membership_cat_id IS NOT NULL || fg_cm_contact.fed_membership_cat_id !='') $where)
                     WHEN   bm.type = 'membership' THEN (IF(m.club_id = $federationId,(SELECT  count(cnt.id)
                      FROM fg_cm_contact cnt  WHERE (cnt.fed_membership_cat_id = bm.membership_id )
                      AND cnt.club_id = $clubId),(SELECT  count(cnt.id)
                      FROM fg_cm_contact cnt  WHERE ( cnt.club_membership_cat_id = bm.membership_id)
                      AND cnt.club_id = $clubId))) END) AS count";
                             
        $countSql = ($countFlag)? $countSqltext:"'0' AS count";


        // If executiveboard is bookmarked, we have to display the terminology term.
        $titleSql = "(IF (r.id = $executiveboardId, '$execBoardTerm', r.title)) as roletitle";
        $staticfilterSql = "(IF (f.club_id =1 , '1', '0')) as static";

        // If team is bookmarked category id will be team cateogry Id.
        $selCategorySql = "(IF (rc.is_team = 1, tc.id, rc.id)) AS roleCategoryId";
        $filterNameSql = "(CASE f.id WHEN '1' THEN ':trans1' WHEN '2' THEN ':trans2' WHEN '3' THEN ':trans3' WHEN '4' THEN ':trans4' ELSE f.name END) AS filtertitle, IF(f.club_id = 1, 1, 0 ) AS staticFilter";

        $bookmarkSql = "SELECT bm.id as bookMarkIds, bm.contact_id as contactId, IF(bm.type = 'membership', IF(m.club_id = '{$federationId}','fed_membership',bm.type),bm.type) as type,
                        bm.role_id as roleId,bm.filter_id as filterId,
                        bm.membership_id as membershipId,bm.sort_order as sortOrder,
                        $titleSql,  IF(mi18.title_lang !='',mi18.title_lang,m.title) as membershiptitle, $filterNameSql,f.club_id as filterClub,
                        $selCategorySql, f.is_broken as isBroken, $staticfilterSql, f.filter_data as filterData,rc.contact_assign AS contactAssign,
                        rc.club_id AS roleCatClubId, rc.function_assign AS functionAssign, $groupSql,$countSql,
                        (CASE WHEN  ((bm.type = 'role' AND rc.contact_assign='manual') OR bm.type= 'membership' ) THEN 'DRAGGABLE' ELSE 'NOTDRAGGABLE' END) As draggable
                        FROM fg_cm_bookmarks AS bm
                        LEFT JOIN fg_cm_membership AS m ON m.id = bm.membership_id 
                        LEFT JOIN fg_cm_membership_i18n AS mi18 ON mi18.id = m.id AND  mi18.lang = '{$corrLang}'
                        LEFT JOIN fg_rm_role AS r ON r.id = bm.role_id
                        LEFT JOIN fg_rm_category AS rc ON rc.id = r.category_id
                        LEFT JOIN fg_team_category AS tc ON tc.id = r.team_category_id
                        LEFT JOIN fg_filter AS f ON f.id = bm.filter_id
                        WHERE bm.contact_id=:contactsId AND bm.club_id=:clubId
                        ORDER BY bm.sort_order ";

        $dataResult = $this->conn->fetchAll($bookmarkSql, array(':contactsId' => $contactId, ':clubId' => $clubId,
                ':trans1'=>$staticFilterTrans['1'],':trans2'=>$staticFilterTrans['2'],':trans3'=>$staticFilterTrans['3'],':trans4'=>$staticFilterTrans['4']));

        return $dataResult;

    }
}
