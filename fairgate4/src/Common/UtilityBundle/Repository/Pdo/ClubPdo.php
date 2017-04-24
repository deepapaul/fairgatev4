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
     * admin database connection
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
        //$clubObj = $this->container->get("fg.admin.connection")->getAdminEntityManager()->getRepository('AdminUtilityBundle:FgClub')->find($clubId);
        $field = 'acl.club_id';
        $clubCountArray = $this->adminManagerconn->fetchAll("Select clubCount($clubId) as clubCount");
        //echo $clubtype;exit;
        switch ($clubtype) {
            case 'federation':
                $condtion = " AND fg_cm_contact.club_id = $clubId AND ((fg_cm_contact.club_id = fg_cm_contact.main_club_id) OR  (fg_cm_contact.is_fed_membership_confirmed = '0'  AND (fg_cm_contact.fed_membership_cat_id IS NOT NULL OR fg_cm_contact.fed_membership_cat_id != '') ) ) "; //"AND (mc.club_id = $clubId OR (mc.club_id != $clubId AND mc.is_fed_member =1))";
                $subscriberCondition = " AND c.club_id = $clubId AND ((c.club_id = c.main_club_id OR c.fed_membership_cat_id IS NOT NULL) AND (c.is_fed_membership_confirmed='0' OR (c.is_fed_membership_confirmed='1' AND c.old_fed_membership_id IS NOT NULL)) ) "; //"AND (mc.club_id = $clubId OR (mc.club_id != $clubId AND mc.is_fed_member =1))";
                $clubCount = "'".$clubCountArray[0]["clubCount"]."' as clubCount";                
                $table = 'federation_' . $clubId;
                $contact = 'mc.fed_contact_id = fg_cm_contact.fed_contact_id';
                $field = 'acl.federation_club_id';
                $applicationConfirmId = $clubObj->getId();
                break;

            case 'sub_federation':
                $condtion = " AND fg_cm_contact.club_id = $clubId AND ((fg_cm_contact.club_id = fg_cm_contact.main_club_id) OR  (fg_cm_contact.is_fed_membership_confirmed = '0'  AND (fg_cm_contact.fed_membership_cat_id IS NOT NULL OR fg_cm_contact.fed_membership_cat_id != '') ) ) "; //"AND (mc.club_id = $clubId OR (mc.club_id != $clubId AND mc.is_fed_member =1))";
                $subscriberCondition = " AND c.club_id = $clubId AND ((c.club_id = c.main_club_id OR c.fed_membership_cat_id IS NOT NULL) AND (c.is_fed_membership_confirmed='0' OR (c.is_fed_membership_confirmed='1' AND c.old_fed_membership_id IS NOT NULL)) ) "; //"AND (mc.club_id = $clubId OR (mc.club_id != $clubId AND mc.is_fed_member =1))";
                $clubCount = "'".$clubCountArray[0]["clubCount"]."' as clubCount";
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
    public function saveClubi18nData($formValues, $logoValues, $clubId, $clubLanguages, $domainCacheKey)
    {
        $clubSettingsObj = $this->em->getRepository('CommonUtilityBundle:FgClubSettings')->findOneBy(array('club' => $clubId));
        if ($clubSettingsObj->getId() != '') {
            $settingsId = $clubSettingsObj->getId();
            foreach ($clubLanguages as $language) {
                $title = $formValues['system']["title_$language"];
                $signature = $formValues['Notification']["signature_$language"];
                $logo = $logoValues["$language"];
                $settingsQuery = "INSERT INTO fg_club_settings_i18n (id,signature_lang,logo_lang,lang) VALUES (:settingsId,:signature,:logo,:lang) ON DUPLICATE KEY UPDATE signature_lang=:signature, logo_lang=:logo";
                $this->conn->executeQuery($settingsQuery, array('settingsId' => $settingsId, 'signature' => $signature, 'logo' => $logo, 'lang' => $language));

                $clubQuery = "INSERT INTO fg_club_i18n (id,title_lang,lang) VALUES (:clubId,:title,:lang) ON DUPLICATE KEY UPDATE title_lang = :title";
                $this->adminManagerconn->executeQuery($clubQuery, array('clubId' => $clubId, 'title' => $title, 'lang' => $language));
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
    public function getContactBookmarks($contactId, $clubId, $clubType, $clubHeirarchy, $executiveboardId, $execBoardTerm, $staticFilterTrans, $countFlag = true, $federationId, $corrLang)
    {
        $federationId = ($clubType == 'federation') ? $clubId : $federationId;
        $doctrineConfig = $this->em->getConfiguration();
        $doctrineConfig->addCustomStringFunction('getClubRoleCount', 'Common\UtilityBundle\Extensions\RoleCount');
        if ($clubType === 'federation' || $clubType === 'sub_federation') {
            $field = ($clubType == 'federation') ? 'mc.fed_contact_id' : 'mc.contact_id';
            $tablename = 'master_federation_' . $clubId;
            $where = 'AND (mc.club_id =:clubId OR (mc.club_id != :clubId AND (fg_cm_contact.fed_membership_cat_id IS NOT NULL AND fg_cm_contact.is_fed_membership_confirmed =1)))';
        } else {
            $field = 'mc.contact_id';
            $tablename = 'master_club_' . $clubId;
            $where = '';
        }

        //$ids = $this->getSubclubs($clubType, $clubId);
        $fedGroupSql = '';
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

        $countSql = ($countFlag) ? $countSqltext : "'0' AS count";


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
            ':trans1' => $staticFilterTrans['1'], ':trans2' => $staticFilterTrans['2'], ':trans3' => $staticFilterTrans['3'], ':trans4' => $staticFilterTrans['4']));

        return $dataResult;
    }

    /**
     * To calculate the federation member count
     * @param integer $clubId current club id
     * @param integer $federationId federation id of current club
     * @return array
     */
    public function getFedCount($clubId, $federationId)
    {
        $fedMemberCount = $this->conn->fetchAll("SELECT getFedMemberCount({$clubId} ,{$federationId}) as fedcount");

        return $fedMemberCount;
    }

    /**
     * To find last updated time of contact in the club
     * @param ineger $clubId
     * @param integer $federationId
     * @param date $dateFormat
     * @return date
     */
    public function getLastContactUpdatedDate($clubId, $federationId, $dateFormat)
    {
        $lastContactUpdated = $this->conn->fetchAll("SELECT date_format(getLastContactUpdate({$clubId},{$federationId}),'{$dateFormat}') as updatedate");
        return $lastContactUpdated;
    }

    /**
     * To find club note count
     * @param integer $clubId
     * @param integer $createdClub created club id
     * @return integer
     */
    public function getClubNoteCount($clubId, $createdClub)
    {
        $clubNoteCount = $this->conn->fetchAll("SELECT COUNT(id)as notecount FROM fg_club_notes WHERE club_id={$clubId} AND created_by_club={$createdClub}");

        return $clubNoteCount;
    }

    /**
     * To find the club document count
     * @param type $clubId
     * @return type
     */
    public function getClubDocumentCount($clubId, $federationId)
    {

        $clubDocumentCount = $this->conn->fetchAll("SELECT COUNT(fg_dm_documents.id) as doccount FROM  fg_dm_documents LEFT JOIN fg_dm_assigment ON fg_dm_documents.id=fg_dm_assigment.document_id  WHERE   fg_dm_documents.club_id = $federationId AND fg_dm_documents.document_type='CLUB'  AND ((fg_dm_documents.deposited_with='SELECTED' AND fg_dm_assigment.club_id=$clubId) OR (fg_dm_documents.deposited_with='ALL' AND $clubId NOT IN (SELECT fg_dm_assigment_exclude.club_id FROM fg_dm_assigment_exclude WHERE fg_dm_assigment_exclude.document_id=fg_dm_documents.id )))");

        return $clubDocumentCount;
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
     * To find own fed member count
     * @param integer $clubId
     * @return integer
     */
    public function getOwnFedMemberCount($clubId)
    {        //
        $ownFedMemberCount = $this->conn->fetchAll("SELECT COUNT(fg_cm_contact.id) as ownfedCount FROM fg_cm_contact WHERE fg_cm_contact.club_id={$clubId} and fg_cm_contact.fed_membership_cat_id is not null and fg_cm_contact.is_fed_membership_confirmed=0");
        return $ownFedMemberCount[0]['ownfedCount'];
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
}
