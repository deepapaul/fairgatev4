<?php

/**
 * ApiPdo
 */
namespace Common\UtilityBundle\Repository\Pdo;


/**
 * Used to handling different API functions.
 *
 * @author  pitsolutions.ch <pit@solutions.com>
 */
class ApiPdo
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
     * Function is used to get the contact details for different apis
     *
     * @param array   $clubData Array of details contains federation Id
     * @param array  $options   Array of details contains days, result count , sdate ,lcrt and contact id
     *
     * @return array
     */
    public function getContactsofClub($clubData, $options = array('days' => '', 'resultCount' => 20, 'contactId' => '', 'countFlag' => '', 'sdate' => '', 'lcrt' => ''))
    {
        $fId = $clubData['fId'];
        $conn = $this->container->get('database_connection');
        $where = '';

        $select = "c.id as clubId, cc.created_at, cc.last_updated,"
            . "c.title as clubName, "
            . "ca.street as clubStrasse, ca.zipcode as clubPLZ, cc.member_id AS KontaktID,"
            . "ca.pobox as clubPostfach, ca.city as clubOrt, cc.id as contactId, m.`2` as Vorname, m.`23` as Nachname, m.`4` as Geburtsdatum,"
            . "m.`72` as Geschlecht,m.`71785` as Postfach, fcm.title as SATUSMitgliedschaften, m.`3` as EMail,m.`47` as Strasse,m.`79` as PLZ, m.`77` as Ort, "
            . "mf.`" . $this->container->getParameter('api_satus_telefon') . "` as Telefon, m.`86` as Natel, mf.`" . $this->container->getParameter('api_satus_magazin') . "` as Magazin,
                ccf.joining_date as Eintrittsdatum,function.ExecutiveBoardFunctions AS ExecutiveBoardFunctions,function.PrimaereSportart AS PrimaereSportart ";

        if ($options['sdate'] != '') {
            $sdate = $options['sdate'];
            $select .=" , CASE WHEN (cc.is_permanent_delete=1) THEN 'Deleted'    WHEN (cc.is_deleted=1) THEN 'Archive'   WHEN (cc.is_deleted=0 AND cc.is_permanent_delete=0 ) THEN 'Active' END AS ExpirationStatus  ";
            $where = " WHERE  (c.federation_id = $fId OR  (c.federation_id = 0 AND c.id=$fId) )  AND c.id =  cc.club_id AND cc.last_updated >= '$sdate'
                      GROUP BY cc.id ,cc.club_id  ";
        } else if ($options['lcrt'] != '' && $options['countFlag'] != '') {
            $lcrt = '-' . $options['lcrt'];
            $select = "COUNT(DISTINCT cc.id) as contactCount";
            $where = " WHERE  (c.federation_id = $fId OR  (c.federation_id = 0 AND c.id=$fId) ) AND c.id =  cc.club_id AND  cc.created_at >= DATE_ADD(CURRENT_DATE(),INTERVAL $lcrt DAY)
                        AND cc.is_deleted=0 AND cc.is_permanent_delete=0 AND cc.is_draft=0
                        AND cc.fed_membership_cat_id != '' AND cc.fed_membership_cat_id IS NOT NULL  Limit 0,1";
        } else if ($options['lcrt'] != '') {
            $lcrt = '-' . $options['lcrt'];
            $resultCount = $options['resultCount'];
            $where = " WHERE  (c.federation_id = $fId OR  (c.federation_id = 0 AND c.id=$fId) ) AND c.id =  cc.club_id AND  cc.created_at >= DATE_ADD(CURRENT_DATE(),INTERVAL $lcrt DAY)
                        AND cc.is_deleted=0 AND cc.is_permanent_delete=0 AND cc.is_draft=0
                        AND cc.fed_membership_cat_id != '' AND cc.fed_membership_cat_id IS NOT NULL
                        GROUP BY cc.id ,cc.club_id LIMIT $resultCount ";
        } else if ($options['days'] != '' && $options['countFlag'] != '') {
            $days = '-' . $options['days'];
            $select = "COUNT(DISTINCT cc.id) as contactCount";
            $where = "WHERE  (c.federation_id = $fId OR  (c.federation_id = 0 AND c.id=$fId) ) AND c.id =  cc.club_id AND cc.last_updated >= DATE_ADD(CURRENT_DATE(),INTERVAL $days DAY) ";
        } else if ($options['days'] != '') {
            $days = '-' . $options['days'];
            $select .=" , CASE WHEN (cc.is_permanent_delete=1) THEN 'Deleted'    WHEN (cc.is_deleted=1) THEN 'Archive'   WHEN (cc.is_deleted=0 AND cc.is_permanent_delete=0 ) THEN 'Active' END AS ExpirationStatus  ";
            $resultCount = $options['resultCount'];
            $where = "WHERE (c.federation_id = $fId OR  (c.federation_id = 0 AND c.id=$fId) ) AND c.id =  cc.club_id AND cc.last_updated >= DATE_ADD(CURRENT_DATE(),INTERVAL $days DAY)
                      GROUP BY cc.id ,cc.club_id  LIMIT $resultCount ";
        } else if ($options['countFlag'] != '') {
            $select = "COUNT(DISTINCT cc.id) as contactCount";
            $where = "WHERE (c.federation_id = $fId OR  (c.federation_id = 0 AND c.id=$fId) )  AND c.id =  cc.club_id
                    AND cc.is_deleted=0 AND cc.is_permanent_delete=0 AND cc.is_draft=0
                    AND cc.fed_membership_cat_id != '' AND cc.fed_membership_cat_id IS NOT NULL ";
        } else {
            $resultCount = "LIMIT " . $options['resultCount'];
            if ($options['resultCount'] == 'all') {
                $resultCount = '';
            }
            $where = "WHERE  (c.federation_id = $fId OR  (c.federation_id = 0 AND c.id=$fId) ) AND  c.id =  cc.club_id
                      AND cc.is_deleted=0 AND cc.is_permanent_delete=0 AND cc.is_draft=0
                      AND cc.fed_membership_cat_id != '' AND cc.fed_membership_cat_id IS NOT NULL
                      GROUP BY cc.id ,cc.club_id   $resultCount ";
        }

        if ($options['contactId'] != '') {
            $contactId = $options['contactId'];
            $where = "WHERE c.id = $fId AND cc.id = $contactId AND cc.is_deleted=0 AND cc.is_permanent_delete=0 AND cc.is_draft=0
                    AND cc.fed_membership_cat_id != '' AND cc.fed_membership_cat_id IS NOT NULL
                    GROUP BY cc.id";
        }

        if ($options['countFlag'] != '') {
            $extraJoinsForSelect = '';
        } else {
            $extraJoinsForSelect = "LEFT JOIN (
                                            SELECT c.fed_contact_id, 
                                            GROUP_CONCAT(CASE WHEN r.is_executive_board = 0 THEN r.title ELSE NULL END) as PrimaereSportart,
                                            GROUP_CONCAT(DISTINCT CASE WHEN r.is_executive_board!=1 THEN NULL WHEN f.id IN (390,393,684) THEN f.title ELSE 'Kontaktadresse' END) AS ExecutiveBoardFunctions
                                            FROM  fg_rm_role_contact crc
                                            INNER JOIN fg_cm_contact c ON c.id = crc.contact_id
                                            INNER JOIN fg_club club ON club.id = crc.contact_club_id
                                            INNER JOIN fg_rm_category_role_function crf ON crf.id=crc.fg_rm_crf_id
                                            LEFT JOIN fg_rm_role r ON r.id=crf.role_id
                                            LEFT JOIN fg_rm_function f ON f.id=crf.function_id
                                            WHERE (club.federation_id = $fId OR club.id = $fId) AND (r.category_id=681 OR (r.is_executive_board=1 AND f.is_active=1))
                                            GROUP BY c.fed_contact_id
                                        ) AS function ON (function.fed_contact_id = cc.fed_contact_id) ";
        }
        $sql = "SELECT $select
                FROM fg_club c
                LEFT JOIN fg_club_address ca ON ca.id = c.correspondence_id
                LEFT JOIN fg_cm_contact cc ON cc.club_id = c.id
                INNER JOIN fg_cm_contact ccf ON ccf.id = cc.fed_contact_id
                LEFT JOIN fg_cm_membership fcm ON fcm.id =cc.fed_membership_cat_id
                INNER JOIN master_system m ON m.fed_contact_id = cc.fed_contact_id
                LEFT JOIN master_federation_" . $fId . " as mf ON mf.fed_contact_id = cc.fed_contact_id
                $extraJoinsForSelect $where";
        try {
            return $conn->fetchAll($sql);
        } catch (\Exception $pdo_ex) {

            return;
        }
    }

    /**
     * Function is used to get the last updated contact details according to the days value of a federaion club
     *
     * @param array   $clubData       Array of details contains federation Id
     * @param array  $options   Array of details contains days, result count ,sdate ,lcrt and contact id
     *
     * @return array
     */
    public function getFedClubContacts($clubData, $options = array('days' => '', 'resultCount' => 20, 'contactId' => '', 'countFlag' => '', 'sdate' => '', 'lcrt' => ''))
    {
        $fId = $clubData['fId'];
        $cId = $clubData['cId'];
        $conn = $this->container->get('database_connection');
        $select = "c.id as clubId,cc.created_at, cc.last_updated,"
            . "c.title as clubName, "
            . "ca.street as clubStrasse, ca.zipcode as clubPLZ,cc.member_id AS KontaktID,"
            . "ca.pobox as clubPostfach, ca.city as clubOrt, cc.id as contactId, m.`2` as Vorname, m.`23` as Nachname, m.`4` as Geburtsdatum,"
            . "m.`72` as Geschlecht,m.`71785` as Postfach,  m.`3` as EMail,m.`47` as Strasse,m.`79` as PLZ, m.`77` as Ort,"
            . "mf.`" . $this->container->getParameter('api_satus_telefon') . "` as Telefon,fcm.title as SATUSMitgliedschaften, m.`86` as Natel, mf.`" . $this->container->getParameter('api_satus_magazin') . "` as Magazin,
                ccf.joining_date as Eintrittsdatum,function.ExecutiveBoardFunctions AS ExecutiveBoardFunctions,function.PrimaereSportart AS PrimaereSportart ";

        $where = '';
        if ($options['sdate'] != '') {
            $sdate = $options['sdate'];
            $select .=" , CASE WHEN (cc.is_permanent_delete=1) THEN 'Deleted'    WHEN (cc.is_deleted=1) THEN 'Archive'   WHEN (cc.is_deleted=0 AND cc.is_permanent_delete=0 ) THEN 'Active' END AS ExpirationStatus  ";
            $where = "WHERE c.id = $cId AND c.federation_id = $fId  AND cc.last_updated >= '$sdate'";
        } else if ($options['lcrt'] != '' && $options['countFlag'] != '') {
            $lcrt = '-' . $options['lcrt'];
            $select = "COUNT(DISTINCT cc.id) as contactCount";
            $where = "WHERE c.id = $cId AND c.federation_id = $fId AND cc.is_deleted=0 AND cc.is_permanent_delete=0 AND cc.is_draft=0 AND cc.created_at >= DATE_ADD(CURRENT_DATE(),INTERVAL $lcrt DAY)
                      AND cc.fed_membership_cat_id != '' AND cc.fed_membership_cat_id IS NOT NULL
                       LIMIT 0,1";
        } else if ($options['lcrt'] != '') {
            $lcrt = '-' . $options['lcrt'];
            $resultCount = $options['resultCount'];
            $where = "WHERE c.id = $cId AND c.federation_id = $fId AND cc.is_deleted=0 AND cc.is_permanent_delete=0 AND cc.is_draft=0 AND cc.created_at >= DATE_ADD(CURRENT_DATE(),INTERVAL $lcrt DAY)
                      AND cc.fed_membership_cat_id != '' AND cc.fed_membership_cat_id IS NOT NULL LIMIT $resultCount";
        } else if ($options['days'] != '' && $options['countFlag'] != '') {
            $days = '-' . $options['days'];
            $select = "COUNT(DISTINCT cc.id) as contactCount";
            $where = "WHERE c.id = $cId AND c.federation_id = $fId AND cc.last_updated >= DATE_ADD(CURRENT_DATE(),INTERVAL $days DAY) ";
        } else if ($options['days'] != '') {
            $days = '-' . $options['days'];
            $resultCount = $options['resultCount'];
            $select .=" , CASE WHEN (cc.is_permanent_delete=1) THEN 'Deleted'    WHEN (cc.is_deleted=1) THEN 'Archive'   WHEN (cc.is_deleted=0 AND cc.is_permanent_delete=0 ) THEN 'Active' END AS ExpirationStatus  ";
            $where = "WHERE c.id = $cId AND c.federation_id = $fId AND cc.last_updated >= DATE_ADD(CURRENT_DATE(),INTERVAL $days DAY) LIMIT $resultCount";
        } else if ($options['countFlag'] != '') {
            $select = "COUNT(DISTINCT cc.id) as contactCount";
            $where = "WHERE c.id = $cId AND c.federation_id = $fId
                      AND cc.is_deleted=0 AND cc.is_permanent_delete=0 AND cc.is_draft=0
                      AND cc.fed_membership_cat_id != '' AND cc.fed_membership_cat_id IS NOT NULL ";
        } else {
            $resultCount = $options['resultCount'];
            $where = "WHERE c.id = $cId AND c.federation_id = $fId AND cc.is_deleted=0 AND cc.is_permanent_delete=0 AND cc.is_draft=0
                      AND cc.fed_membership_cat_id != '' AND cc.fed_membership_cat_id IS NOT NULL LIMIT $resultCount";
        }

        if ($options['contactId'] != '') {
            $contactId = $options['contactId'];
            $where = "WHERE c.id = $cId AND c.federation_id = $fId AND cc.id = $contactId AND cc.fed_membership_cat_id != '' AND cc.fed_membership_cat_id IS NOT NULL GROUP BY cc.id";
        }

        if ($options['countFlag'] != '') {
            $extraJoinsForSelect = '';
        } else {
            $extraJoinsForSelect = "LEFT JOIN (
                                            SELECT c.fed_contact_id, 
                                            GROUP_CONCAT(CASE WHEN r.is_executive_board = 0 THEN r.title ELSE NULL END) as PrimaereSportart,
                                            GROUP_CONCAT(DISTINCT CASE WHEN r.is_executive_board!=1 THEN NULL WHEN f.id IN (390,393,684) THEN f.title ELSE 'Kontaktadresse' END) AS ExecutiveBoardFunctions
                                            FROM  fg_rm_role_contact crc
                                            INNER JOIN fg_cm_contact c ON c.id = crc.contact_id
                                            INNER JOIN fg_club club ON club.id = crc.contact_club_id
                                            INNER JOIN fg_rm_category_role_function crf ON crf.id=crc.fg_rm_crf_id
                                            LEFT JOIN fg_rm_role r ON r.id=crf.role_id
                                            LEFT JOIN fg_rm_function f ON f.id=crf.function_id
                                            WHERE (club.federation_id = $fId OR club.id = $fId OR club.id = $cId) AND (r.category_id=681 OR (r.is_executive_board=1 AND f.is_active=1))
                                            GROUP BY c.fed_contact_id
                                        ) AS function ON (function.fed_contact_id = cc.fed_contact_id) ";
        }
        $sql = "SELECT $select
                    FROM fg_club c
                    INNER JOIN fg_club_address ca ON ca.id = c.correspondence_id
                    INNER JOIN fg_cm_contact cc ON cc.club_id = c.id
                    INNER JOIN fg_cm_contact ccf ON ccf.id = cc.fed_contact_id
                    LEFT JOIN fg_cm_membership fcm ON fcm.id =cc.fed_membership_cat_id
                    INNER JOIN master_system m ON m.fed_contact_id = cc.fed_contact_id
                    INNER JOIN master_federation_" . $fId . " as mf ON mf.fed_contact_id = cc.fed_contact_id
                    $extraJoinsForSelect $where";
        try {
            return $conn->fetchAll($sql);
        } catch (\Exception $e) {
            return;
        }
    }

    /**
     * Function to get all clubs and its details under a federation club
     *
     * @param int $fId Federation club id
     *
     * @return array
     */
    public function getAllClubsUnderFederation($fId)
    {
        $conn = $this->container->get('database_connection');
        try {
            $resultClubIds = $conn->fetchAll("SELECT c.parent_club_id as federationId, c.id as clubId, c.title as clubName, ca.street as clubStrasse, ca.zipcode as clubPLZ, ca.pobox as clubPostfach, ca.city as clubOrt FROM (SELECT sublevelClubs(id) AS id, @level AS level FROM (SELECT  @start_with :='{$fId}',@id := @start_with,@level := 0) vars, fg_club WHERE @id IS NOT NULL) c1 JOIN fg_club c ON c.id = c1.id LEFT JOIN fg_club_address ca ON ca.id = c.correspondence_id");

            return $resultClubIds;
        } catch (\Exception $pdo_ex) {

            return;
        }
    }
    
    /**
     * This method is used to get admin details(fed admin, club admin).
     * 
     * @param onject $conn Doctrine  connection
     * @param array  $adminRoleIds   Admin role ids 
     * @param array  $clubIds        Fed and Club ids.
     * @param sring  $clubSystemLang club system language 
     * @param string $corrLangId     Correspondace Language id
     * @return array                 admin details
     */
    public function getMainAdminDetails($conn, $adminRoleIds, $clubIds, $clubSystemLang, $corrLangId)
    {   
        $adminRoles =  implode(',', $adminRoleIds);
        $clubs =  implode(',', $clubIds);
        $clubCorresLang = "ms.`{$corrLangId}`";
        $sql = "SELECT su.email, {$clubCorresLang} AS lang, salutationText(c.id, c.club_id, '{$clubSystemLang}', {$clubCorresLang}) AS salutation FROM `sf_guard_group` sg
                INNER JOIN sf_guard_user_group sug on sug.group_id = sg.id
                INNER JOIN sf_guard_user su on su.id = sug.user_id
                INNER JOIN fg_cm_contact c on c.id = su.contact_id
                INNER JOIN master_system ms on ms.fed_contact_id = c.fed_contact_id
                WHERE sg.id IN ({$adminRoles}) AND su.email !='' AND su.club_id IN({$clubs})";
        $result = $conn->fetchAll($sql);

        return $result;
    }

    /**
     * This method is used to get default salutation text.
     * 
     * @param object $conn        Doctrine  connection
     * @param int    $clubId      Current club id
     * @param string $corresLang  Correspondance language
     * 
     * @return string             Salutation text
     */
    public function getDefaultSalutaion($conn, $clubId, $corresLang)
    {
        $sql = "SELECT (CASE WHEN si18n.company_no_maincontact_lang = '' THEN s.company_no_maincontact ELSE si18n.company_no_maincontact_lang END) AS salutaion FROM `fg_club_salutation_settings` s
                INNER JOIN fg_club_salutation_settings_i18n si18n on si18n.id = s.id AND si18n.lang ='{$corresLang}'
                WHERE s.club_id IN ({$clubId}, 1) order by s.club_id desc limit 0,1";
        $result = $conn->fetchAll($sql);

        return $result[0]['salutaion'];
    }
    
    /**
     * This method to get the memberships for a club
     * 
     * @param int    $clubId    Current club id
     * @param string $lang      The language client requests
     * @param string $lmod      The lmod in data 
     * 
     * @return array             The membership detail array
     */
    public function getPlayerCategories($clubId, $lang, $lmod = '' )
    {
        $conn = $this->container->get('database_connection');
        
        $clubData = $conn->fetchAssoc('SELECT id,federation_id FROM fg_club WHERE id =:clubId LIMIT 0,1', array('clubId' => $clubId));
        $membershipLogQuery = $membershipLogSelect = '';
        if($lmod != ''){
            $membershipLogSelect = ',membershiphistory.logData';
            $membershipLogQuery = " INNER JOIN (SELECT 
                                    membership_id,
                                    GROUP_CONCAT(CONCAT_WS('####',field,date,IFNULL(value_before,''),IFNULL(value_after,'')) SEPARATOR '|--|') AS logData
                                    FROM fg_cm_membership_log ml
                                    WHERE `kind` = 'data' AND (ml.club_id =:clubId OR ml.club_id =:federationId) AND (ml.date >= (CURRENT_DATE() - INTERVAL :days DAY)) AND (field LIKE '%($lang)' OR field LIKE 'Name')
                                    GROUP BY membership_id) membershiphistory ON membershiphistory.membership_id =  m.id ";
        }
        
        $titleQuery = "IFNULL(mi18n.title_lang,'') AS title";
        $membershipQuery = "SELECT m.id,$titleQuery $membershipLogSelect FROM `fg_cm_membership` m ".
                            " LEFT JOIN fg_cm_membership_i18n mi18n ON mi18n.id = m.id AND mi18n.lang =:lang".
                            $membershipLogQuery.
                            " WHERE (m.club_id =:clubId OR m.club_id =:federationId)";
        $membershipList = $conn->fetchAll($membershipQuery, array('clubId' => $clubData['id'],'federationId' => $clubData['federation_id'],'lang' => $lang,'days' => $lmod));

        return $membershipList;
    }
}
