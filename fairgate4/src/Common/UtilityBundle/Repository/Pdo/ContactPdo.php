<?php

namespace Common\UtilityBundle\Repository\Pdo;

use Clubadmin\Util\Contactlist;
use Common\UtilityBundle\Util\FgSettings;
use Common\UtilityBundle\Util\FgFedMemberships;

/**
 * Used to handling different contact functions.
 *
 * @author  pitsolutions.ch <pit@solutions.com>
 */
class ContactPdo
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
     * Function to insert login entries of imported user.
     *
     * @param type $club
     * @param type $importTable
     */
    public function insertLoginEntriesForImortedContact($club, $importTable, $contactId, $clubType = '')
    {
        $yes = $this->container->get('translator')->trans('YES');
        $no = $this->container->get('translator')->trans('NO');
        $parentId = $club->get('federation_id');
        $clubId = $club->get('id');
        $primaryEmail = $this->container->getParameter('system_field_primaryemail');
        $sfGuardInsertSql = 'INSERT INTO `sf_guard_user` (`username`, `username_canonical`, `email`, `email_canonical`,`created_at`, `updated_at`, `contact_id`, `club_id`) ';
        $sfGuardInsertSql .= "SELECT M.`$primaryEmail`, M.`$primaryEmail`, M.`$primaryEmail`, M.`$primaryEmail`, NOW(), '0000-00-00 00:00:00',C.id, C.club_id FROM fg_cm_contact C INNER JOIN master_system M ON "
            . "M.fed_contact_id=C.fed_contact_id WHERE  C.import_table='$importTable'  AND (C.main_club_id=C.club_id OR (C.fed_membership_cat_id IS NOT NULL AND (C.old_fed_membership_id IS NOT NULL OR C.is_fed_membership_confirmed='0')) )"
            . ' ON DUPLICATE KEY UPDATE email = VALUES(email), email_canonical = VALUES(email_canonical), username = VALUES(username), username_canonical = VALUES(username_canonical) ';
        $this->conn->executeQuery($sfGuardInsertSql);
        $sfGuardUpdateSql = "UPDATE `sf_guard_user` S INNER JOIN fg_cm_contact C ON C.id=S.contact_id INNER JOIN master_system M ON M.fed_contact_id=C.fed_contact_id SET S.`username` = M.`$primaryEmail`, S.`username_canonical`=M.`$primaryEmail`, S.`email`=M.`$primaryEmail`, S.`email_canonical`=M.`$primaryEmail`
            WHERE C.import_table='$importTable' AND C.fed_membership_cat_id is not null AND M.`$primaryEmail` IS NOT NULL AND M.`$primaryEmail`!=''";
        $this->conn->executeQuery($sfGuardUpdateSql, array());
        $updateSubscriber = "Update fg_cm_contact  C INNER JOIN `fg_cn_subscriber` S ON C.club_id=S.club_id  INNER JOIN master_system M ON M.fed_contact_id=C.fed_contact_id and S.`email`=M.`$primaryEmail` SET C.is_subscriber=1 where  C.import_table='$importTable' ";
        $this->conn->executeQuery("$updateSubscriber");
        $delSubscriber = "Delete S from `fg_cn_subscriber` S JOIN fg_cm_contact C ON C.club_id=S.club_id INNER JOIN master_system M ON M.fed_contact_id=C.fed_contact_id and S.`email`=M.`$primaryEmail` where C.import_table='$importTable'  ";
        $this->conn->executeQuery("$delSubscriber");
        $this->conn->executeQuery("INSERT INTO fg_cm_change_log(contact_id, date, kind, field, value_before, value_after, changed_by, club_id) (select C.id,now(),'system','stealth mode','',IF(C.is_stealth_mode = '1', '$yes', '$no') as is_stealth_mode ,'$contactId',C.club_id  from fg_cm_contact C "
            . " WHERE C.import_table='$importTable' ) ");
        $this->conn->executeQuery("INSERT INTO fg_cm_change_log(contact_id, date, kind, field, value_before, value_after, changed_by, club_id) (select C.id,now(),'system','intranet access','',IF(C.intranet_access = '1', '$yes', '$no') as intranet_access ,'$contactId',C.club_id  from fg_cm_contact C "
            . " WHERE C.import_table='$importTable' ) ");
        $this->conn->executeQuery("INSERT INTO fg_cm_change_log(contact_id, date, kind, field, value_before, value_after, changed_by, club_id) (select C.id,now(),'system','newsletter','','subscribed' ,'$contactId',C.club_id  from fg_cm_contact C "
            . " WHERE C.import_table='$importTable' and  C.is_subscriber=1) ");
    }

    /**
     * Function to save all privacy settings of the logged in user.
     *
     * @param Array  $settings  Settings array
     * @param Int    $contactId Contact id
     * @param Object $contactId Club object
     *
     * @return bool
     */
    public function savePrivacySettings($settings, $contactId, $club)
    {
        $insertValues = array();
        $clubTable = $club->get('clubTable');
        // Generating the insert/update query depending on whether the value already exists or not
        foreach ($settings['privacy']['catId'] as $key => $attributes) {
            foreach ($attributes['fieldId'] as $attrKey => $attrValue) {
                //if attribute is of address category and is of 'both' type then update correspondence and invoice field
                if ($key == 2) {
                    $attributeObj = $this->em->getRepository('CommonUtilityBundle:FgCmAttribute')->findOneBy(array('address' => $attrKey));
                    if ($attributeObj) {
                        $attrId = $attributeObj->getId();
                        $insertValues[] = "('$contactId','$attrId','$attrValue')";
                    }
                }
                //insert/update privacy setting of attribute
                $insertValues[] = "('$contactId','$attrKey','$attrValue')";
            }
        }
        if (count($insertValues) > 0) {
            $insertQry = 'INSERT INTO fg_cm_contact_privacy (`contact_id`,`attribute_id`,`privacy`) VALUES ';
            $insertQry = $insertQry . implode(',', $insertValues) . ' ON DUPLICATE KEY UPDATE `privacy` = VALUES(privacy);';
            $this->conn->executeQuery($insertQry);
        }
        // Saving the stealth mode flag in the fg_cm_contact
        if (isset($settings['is_stealth_mode'])) {
            $stealthMode = $settings['is_stealth_mode'];
            $updateContact = "UPDATE $clubTable SET is_stealth_mode=$stealthMode WHERE contact_id=$contactId";
            $this->conn->executeQuery($updateContact);
        }
        if (isset($settings['languageSettings'])) {
            $this->updateContactSystemLanguage($contactId, $clubTable, $settings['languageSettings']);
        }
        if (isset($settings['subscriberSetting'])) {
            $this->updateContactSubscription($contactId, $clubTable, $settings['subscriberSetting']);
        }

        return true;
    }

    /**
     * Function to get stealth mode of a contact.
     *
     * @param int    $contactId   Contact Id
     * @param string $masterTable Mastertable
     * @param string $clubType    ClubType
     *
     * @return string
     */
    public function getStealthmodeOfContact($contactId, $masterTable, $clubType)
    {
        $query = " SELECT C.is_stealth_mode from fg_cm_contact C JOIN $masterTable M ";
        switch ($clubType) {
            case 'federation':
                $query .= ' on M.fed_contact_id = C.fed_contact_id  AND C.id = :contactId';
                break;
            case 'sub_federation':
                $query .= ' on M.contact_id = C.subfed_contact_id  AND C.id = :contactId';
                break;
            default:
                $query .= 'on M.contact_id = C.id  AND C.id = :contactId';
        }
        $result = $this->conn->fetchAll($query, array(':contactId' => $contactId));

        return $result[0]['is_stealth_mode'];
    }

    /**
     * Function to get logged details for displating it in internal overview profile block.
     *
     * @param array  $resultFieldIds result field ids
     * @param int    $contactId      Contact id
     * @param string $contacttype    Contact type
     *
     * @return array
     */
    public function getLoggedProfileDetails($resultFieldIds, $fedcontactId, $contactId, $contacttype)
    {
        $select = 1;
        $picAttrId = ($contacttype == 'Company') ? $this->container->getParameter('system_field_companylogo') : $this->container->getParameter('system_field_communitypicture');
        $seperator = ',';
        foreach ($resultFieldIds as $alias => $ids) {
            $select .= $seperator . 'ms.' . $ids . " AS $alias";
        }
        $query = "SELECT c.created_club_id As createdclub , ms.$picAttrId AS profilepicture, $select  FROM `master_system` ms join fg_cm_contact c on ms.fed_contact_id=c.fed_contact_id  WHERE ms.fed_contact_id = $fedcontactId and c.id=$contactId ";
        $result = $this->conn->fetchAll($query);

        return $result[0];
    }

    /**
     * Function to get all household and other connections of a contact.
     *
     * @param int $contactId ContactId
     *
     * @return array $result Connection details array
     */
    public function getMyConnections($contactId)
    {
        $club = $this->container->get('club');
        $clubId = $club->get('id');
        $clubType = $club->get('type');
        $clubDefaultLang = $club->get('default_system_lang');
        $maincontactIdField = 'contact_id';

        switch ($clubType) {
            case 'federation':
                $joinCondition = ' on mc.fed_contact_id = fg_cm_contact.fed_contact_id';
                $maincontactIdField = 'fed_contact_id';
                break;
            case 'sub_federation':
                $joinCondition = 'on mc.contact_id = fg_cm_contact.subfed_contact_id';
                break;
            default:
                $joinCondition = ' on mc.contact_id = fg_cm_contact.id';
        }

        $clubTable = 'master_' . (($clubType == 'federation' || $clubType == 'sub_federation') ? 'federation' : 'club') . '_' . $clubId;
        $sql = "SELECT LC.linked_contact_id AS contactId, contactName(LC.linked_contact_id) AS contactName, (IF(LC.relation_id IS NOT NULL, (IF(Ri18n.title_lang != '' AND Ri18n.title_lang IS NOT NULL, Ri18n.title_lang, R.name)), LC.relation)) AS relationName, "
            . "LC.type AS connectionType, CASE C.is_company WHEN 1 THEN MS.68 ELSE MS.21 END AS profilePic, C.is_company AS isCompany, C.club_id AS clubId, C.is_stealth_mode AS isStealthMode, IF(LC.type = 'household', 1, 3) AS type "
            . 'FROM `fg_cm_linkedcontact` LC LEFT JOIN fg_cm_relation R ON LC.relation_id = R.id '
            . 'LEFT JOIN fg_cm_relation_i18n Ri18n ON R.id = Ri18n.id AND Ri18n.lang = :clubDefaultLang '
            . 'LEFT JOIN fg_cm_contact C ON LC.linked_contact_id = C.id '
            . 'LEFT JOIN master_system MS ON C.fed_contact_id = MS.fed_contact_id '
            . "LEFT JOIN $clubTable MC ON MC.$maincontactIdField = LC.linked_contact_id "
            . 'WHERE LC.contact_id = :contactId AND LC.club_id = :clubId AND C.is_permanent_delete = 0 ORDER BY type,contactName ASC';

        $result = $this->conn->fetchAll($sql, array('contactId' => $contactId, 'clubId' => $clubId, 'clubDefaultLang' => $clubDefaultLang));

        return $result;
    }

    /**
     * Function to get all companies of a contact.
     *
     * @param int $contactId ContactId
     *
     * @return array $result Connection details array
     */
    public function getCompaniesOfAContact($contactId)
    {
        $clubTable = $this->container->get('club')->get('clubTable');
        $clubType = $this->container->get('club')->get('type');
        $maincontactIdField = 'contact_id';
        switch ($clubType) {
            case 'federation':
                $joinCondition = ' on MC.fed_contact_id = C.fed_contact_id AND C.id = C.fed_contact_id';
                $maincontactIdField = 'fed_contact_id';
                break;
            case 'sub_federation':
                $joinCondition = 'on MC.contact_id = C.subfed_contact_id AND C.id = C.subfed_contact_id';
                break;
            default:
                $joinCondition = ' on MC.contact_id = C.id';
        }
        $sql = "SELECT C.id AS contactId, contactName(C.id) AS contactName, C.comp_def_contact_fun AS relationName, 'maincontact' AS connectionType, "
            . 'MS.68 AS profilePic,C.is_company AS isCompany, C.club_id AS clubId, C.is_stealth_mode AS isStealthMode, 2 AS type '
            . 'FROM fg_cm_contact C LEFT JOIN master_system MS ON MS.fed_contact_id = C.fed_contact_id '
            . "INNER JOIN $clubTable MC  $joinCondition WHERE C.comp_def_contact = $contactId "
            . 'AND C.is_deleted=0 AND C.is_permanent_delete = 0   ORDER BY contactName ASC';

        $result = $this->conn->fetchAll($sql, array('contactId' => $contactId));

        return $result;
    }

    /**
     * Function to get my maincontact.
     *
     * @param int $contactId ContactId
     *
     * @return array $result MainContact details array
     */
    public function getMainContact($contactId)
    {
        $clubTable = $this->container->get('club')->get('clubTable');
        $club = $this->container->get('club');
        $clubType = $this->container->get('club')->get('type');
        $maincontactIdField = 'MC.contact_id';
        if ($clubType == 'federation') {
            $maincontactIdField = 'MC.fed_contact_id';
        }
        $profilePicId = $this->container->getParameter('system_field_communitypicture');
        $sql = "SELECT C.comp_def_contact AS contactId, contactName(C.comp_def_contact) AS contactName, C.comp_def_contact_fun AS relationName, 'maincontact' AS connectionType, "
            . "MS.$profilePicId AS profilePic, 0 AS isCompany, C.club_id AS clubId, C.is_stealth_mode AS isStealthMode, 2 AS type "
            . 'FROM fg_cm_contact C LEFT JOIN master_system MS ON MS.fed_contact_id = C.comp_def_contact '
            . "INNER JOIN $clubTable MC ON $maincontactIdField = C.comp_def_contact WHERE C. id = :contactId AND C.comp_def_contact IS NOT NULL AND C.comp_def_contact != '' AND "
            . 'C.is_deleted=0 AND C.is_permanent_delete = 0 ORDER BY contactName ASC';
        $result = $this->conn->fetchAll($sql, array('contactId' => $contactId));

        return $result;
    }

    public function checkWhetherContactHasAccessToClubFrontend($contactId, $clubId, $fedContactId, $subfedContactId, $clubTable = '', $clubType)
    {
        $hasAccess = 0;
        $clubBookedModules = $this->container->get('club')->get('bookedModulesDet');
        
        if ($clubTable != '') {
            $contactField = 'id';
            switch ($clubType) {
                case 'federation':
                    $contactId = $fedContactId;
                    break;
                case 'sub_federation':
                    $contactId = $subfedContactId;
                    break;
                default:
                    $contactId = $fedContactId;
                    $contactField = 'fed_contact_id';
            }

            if(!in_array('frontend1', $clubBookedModules)) {
                $hasAccess = 0;
            } else {
                $sql = 'SELECT C.intranet_access AS intranetAccess FROM fg_cm_contact C INNER JOIN sf_guard_user S ON S.contact_id = C.id '
                        . "AND S.club_id = $clubId WHERE C.$contactField = $contactId AND C.is_deleted = 0 AND C.is_permanent_delete = 0";

                $result = $this->conn->fetchAll($sql, array('contactId' => $contactId, 'clubId' => $clubId));
                $hasAccess = (count($result) > 0) ? $result[0]['intranetAccess'] : 0;
            }
        }

        return $hasAccess;
    }

    /**
     * Method to get profile image of a contact.
     *
     * @param int    $contactId
     * @param string $profileImgField  Company logo Field Name
     * @param string $companyLogoField Company logo Field Name
     *
     * @return string
     */
    public function getProfileImage($contactId, $profileImgField, $companyLogoField)
    {
        $query = " SELECT CASE WHEN (CON.is_company = 1 ) THEN MS.$companyLogoField ELSE MS.$profileImgField END as profileImg,CON.is_company "
            . 'FROM fg_cm_contact CON JOIN master_system MS ON MS.fed_contact_id = CON.fed_contact_id AND CON.id = :contactId ';
        $result = $this->conn->fetchAll($query, array(':contactId' => $contactId));

        return $result[0];
    }

    /**
     * Function to get contact name.
     *
     * @param int $contactId the contact id
     * @param int $setyob    Object
     *
     * @return string contact name
     */
    public function getContactName($contactId, $setyob = false, $isReverse = false)
    {
        //$clubtype = $this->container->get('club')->trans('type');
        //$clubid   = $this->container->get('club')->trans('id');
        if ($setyob) {
            $fieldsArray = $this->conn->fetchAll("SELECT contactNameYOB(C.id) AS name FROM fg_cm_contact C left join master_system S on C.fed_contact_id=S.fed_contact_id where C.id='$contactId'");
        } elseif ($isReverse) {
            $fieldsArray = $this->conn->fetchAll("SELECT contactNameNoSort(C.id,0) AS name FROM fg_cm_contact C left join master_system S on C.fed_contact_id=S.fed_contact_id where C.id='$contactId'");
        } else {
            $fieldsArray = $this->conn->fetchAll("SELECT CONCAT(`23`,' ',`2`) AS name FROM fg_cm_contact C left join master_system S on C.fed_contact_id=S.fed_contact_id where C.id='$contactId'");
        }

        return $fieldsArray[0]['name'];
    }

    public function searchContact($term, $clubId, $contactId = 0, $contactType = 'ALL')
    {
        $firstname = '`' . $this->container->getParameter('system_field_firstname') . '`';
        $lastname = '`' . $this->container->getParameter('system_field_lastname') . '`';
        $clubType = $this->container->get('club')->get('type');
        $joins = '';
        $activeC = " AND (C.main_club_id=C.club_id OR (C.fed_membership_cat_id IS NOT NULL AND (C.old_fed_membership_id IS NOT NULL OR C.is_fed_membership_confirmed='0')))";

        if ($clubType == 'federation') {
            $sWhere = "C.is_permanent_delete=0 and C.club_id = '{$clubId}' $activeC ";
            $joinsTab = " master_federation_{$clubId} AS mc LEFT JOIN ";
            $joinsOn = 'ON mc.fed_contact_id = C.fed_contact_id ';
        } elseif ($clubType == 'sub_federation') {
            $sWhere = "C.is_permanent_delete=0 and C.club_id = '{$clubId}' $activeC";
            $joinsTab = " master_federation_{$clubId} AS mc LEFT JOIN ";
            $joinsOn = ' ON mc.contact_id = C.id ';
        } else {
            $sWhere = "C.is_permanent_delete=0 and C.club_id = '{$clubId}'";
            $joinsTab = " master_club_{$clubId} AS mc LEFT JOIN ";
            $joinsOn = 'ON mc.contact_id = C.id';
        }
        if ($contactType != 'ALL') {
            $sWhere .= ($contactType == 'COMPANY') ? " AND C.is_company=1 " : " AND C.is_company=0 ";
        }
        $excludeIds = $contactId == 0 ? '' : " AND C.id NOT IN($contactId) ";
        $newfield = "CONCAT(contactName(C.id),IF(DATE_FORMAT(`4`,'%Y') = '0000' OR `4` is NULL OR `4` ='','',CONCAT(' (',DATE_FORMAT(`4`,'%Y'),')'))) AS title, contactName(C.id) AS name";

        if ($term == '') {
            $listquery = "SELECT C.id, $newfield FROM $joinsTab fg_cm_contact C $joinsOn LEFT JOIN master_system S on C.fed_contact_id=S.fed_contact_id where  $sWhere AND C.is_deleted=0 AND C.club_id = '{$clubId}' ORDER BY name";
        } else {
            $search = explode(' ', trim($term), 2);
            if (sizeof($search) > 1) {
                $listquery = "SELECT C.id, $newfield FROM $joinsTab fg_cm_contact C $joinsOn LEFT JOIN master_system S on C.fed_contact_id=S.fed_contact_id $joins where  $sWhere AND C.is_deleted=0 AND C.club_id = '{$clubId}' AND (S.$firstname LIKE '$search[0]%' OR S.$lastname LIKE '$search[0]%' OR S.`9` LIKE '$search[0]%') AND (S.$firstname LIKE '$search[1]%' OR S.$lastname LIKE '$search[1]%' OR S.`9` LIKE '$search[1]%') ORDER BY name";
            } else {
                $listquery = "SELECT C.id, $newfield FROM $joinsTab fg_cm_contact C $joinsOn LEFT JOIN master_system S on C.fed_contact_id=S.fed_contact_id $joins where  $sWhere AND C.is_deleted=0 AND C.club_id = '{$clubId}' AND (S.$firstname LIKE :search OR S.$lastname LIKE :search OR S.`9` LIKE :search) ORDER BY name";
            }
        }

        $contactsArray = $this->conn->fetchAll($listquery, array(':search' => $term . '%'));

        return $contactsArray;
    }

    /**
     * Function to get members count, function title of different functions of a role.
     *
     * @param int    $roleId            RoleId
     * @param string $roleType          RoleType
     * @param string $transOthers       Others translation text
     * @param string $transNotSpecified NotSpecified translation text
     *
     * @return array $memberDetails Member details
     */
    public function getMemberDetails($roleId, $roleType = 'team', $transOthers = 'Others', $transNotSpecified = 'Not Specified')
    {
        $memberDetails = array();
        $clubDefaultLanguage = $this->container ? $this->container->get('club')->get('default_lang') : 'de';
        $roleType = ($roleType == 'team') ? 'T' : 'W';
        $clubId = $this->container->get('club')->get('id');
        $sql = " SELECT count(rc.id) AS funMembersCount, (CASE WHEN (fi18n.title_lang IS NULL OR fi18n.title_lang = '') THEN f.title ELSE fi18n.title_lang END) AS functionTitle "
            . 'FROM fg_rm_role_contact rc LEFT JOIN fg_rm_category_role_function crf ON crf.id = rc.fg_rm_crf_id '
            . 'LEFT JOIN fg_rm_role r ON r.id = crf.role_id LEFT JOIN fg_rm_function f ON crf.function_id = f.id '
            . "LEFT JOIN fg_rm_function_i18n fi18n ON fi18n.id = f.id AND fi18n.lang = '" . $clubDefaultLanguage . "' WHERE crf.role_id = :roleId AND r.type = :roleType AND r.is_active=1 AND rc.assined_club_id = :clubId GROUP BY crf.function_id";
        $result = $this->conn->fetchAll($sql, array('roleId' => $roleId, 'roleType' => $roleType, 'clubId' => $clubId));
        $functions = array();
        foreach ($result as $val) {
            $functions[] = array('label' => $val['functionTitle'], 'data' => $val['funMembersCount']);
        }
        $memberDetails['functions'] = $functions;
        $memberCityDetails = $this->getCityDetailsOfRoleMembers($clubId, $roleId, $roleType, $transOthers, $transNotSpecified);
        $memberDetails['residences'] = $memberCityDetails['residences'];
        $memberDetails['memberCount'] = $memberCityDetails['memberCount'];

        return $memberDetails;
    }

    /**
     * Function to get city details of role members.
     *
     * @param int    $clubId            ClubId
     * @param int    $roleId            RoleId
     * @param string $roleType          RoleType
     * @param string $transOthers       Others translation text
     * @param string $transNotSpecified NotSpecified translation text
     *
     * @return array $result   Array of city details
     */
    public function getCityDetailsOfRoleMembers($clubId, $roleId, $roleType = 'T', $transOthers = 'Others', $transNotSpecified = 'Not Specified')
    {
        $systemAttrIdOrt = $this->container->getParameter('system_field_corres_ort');
        $sql = " SELECT COUNT(DISTINCT rc.contact_id) AS cnt, (IF ((ms.$systemAttrIdOrt IS NOT NULL AND ms.$systemAttrIdOrt != ''), ms.$systemAttrIdOrt, '$transNotSpecified')) AS city "
            . 'FROM fg_rm_role r LEFT JOIN fg_rm_category_role_function crf ON crf.role_id = r.id '
            . 'LEFT JOIN fg_rm_role_contact rc ON rc.fg_rm_crf_id = crf.id '
            . 'LEFT JOIN fg_cm_contact cc ON cc.id = rc.contact_id '
            . 'LEFT JOIN master_system ms ON ms.fed_contact_id = cc.fed_contact_id '
            . 'WHERE r.id = :roleId AND r.type = :roleType AND r.is_active=1 AND rc.assined_club_id = :clubId GROUP BY city ORDER BY cnt DESC';
        $result = $this->conn->fetchAll($sql, array('roleId' => $roleId, 'roleType' => $roleType, 'clubId' => $clubId));
        $residences = array();
        $memberCount = 0;
        $limit = 0;
        $othersCount = 0;
        foreach ($result as $val) {
            if (($limit < 6) && ($val['city'] != $transNotSpecified)) {
                $residences[] = array('label' => $val['city'], 'data' => $val['cnt']);
                $limit = $limit + 1;
            } else {
                if ($val['city'] == $transNotSpecified) {
                    $residences[] = array('label' => $val['city'], 'data' => $val['cnt']);
                } else {
                    $othersCount += $val['cnt'];
                }
            }
            $memberCount += $val['cnt'];
        }
        if ($othersCount > 0) {
            $residences[] = array('label' => $transOthers, 'data' => $othersCount);
        }
        $return['residences'] = $residences;
        $return['memberCount'] = $memberCount;

        return $return;
    }

    /**
     * Function to update contact log entries time to confirmed date.
     *
     * @param array $selectedIdsArr Array of confirm ids
     */
    public function updateConfirmedTimeOfContactLogEntries($selectedIdsArr)
    {
        if (count($selectedIdsArr) > 0) {
            $selectedIds = implode(',', $selectedIdsArr);
            $updateContactLog = "UPDATE fg_cm_change_log l LEFT JOIN fg_cm_change_toconfirm c ON c.contact_id = l.contact_id LEFT JOIN fg_cm_mutation_log m ON m.toconfirm_id = c.id SET l.date=m.confirmed_date WHERE c.id IN ($selectedIds)";
            $this->conn->executeQuery($updateContactLog);
        }
    }

    /**
     * calculate userrights count for each role.
     *
     * @param string $roles concatinated string of rle ids
     *
     * @return array
     */
    public function getAdministratorCount($roles)
    {
        $sql = 'SELECT r.id,count(DISTINCT(t.user_id)) as count FROM fg_rm_role r '
            . 'LEFT JOIN sf_guard_user_team t ON r.id=t.role_id '
            . 'LEFT JOIN sf_guard_group g ON t.group_id=g.id '
            . "WHERE t.role_id IN ( $roles ) AND g.type ='role' "
            . 'GROUP BY r.id';
        $count = $this->conn->fetchAll($sql);

        return $count;
    }

    /**
     * Function is used for getting details of a contact.
     *
     * @param object $club      Club object
     * @param int    $contactId Contact id
     * @param string $sWhere    Where condition
     * @param array  $columns   Columns to select
     * @param string $listType  contact list type (editable,contact,archive etc)
     *
     * @return array $fieldsArray Contact details
     */
    public function getContactData($club, $contactId, $sWhere, $columns = '*', $listType = 'editable')
    {
        if (!is_numeric($contactId)) {
            return false;
        }
        $contactlistClass = new Contactlist($this->container, $contactId, $club, $listType);
        $contactlistClass->setColumns($columns);
        $contactlistClass->setFrom('*');
        $contactlistClass->setCondition();
        $contactlistClass->addCondition($sWhere);
        $listquery = $contactlistClass->getResult();
        $fieldsArray = $this->conn->fetchAll($listquery);

        return $fieldsArray;
    }

    /**
     * Function to update contact draft status to 0 on confirmaing creations.
     *
     * @param array  $selectedIdsArr Array of confirm ids
     * @param string $action         Confirm or Discard
     */
    public function updateContactStatus($selectedIdsArr, $action)
    {
        if (count($selectedIdsArr) > 0) {
            $selectedIds = implode(',', $selectedIdsArr);
            $updateContactQry = ($action == 'confirm') ? 'c.is_draft=0' : 'c.is_deleted=1, c.is_permanent_delete=1';
            $result = $this->conn->fetchAll("SELECT c.fed_contact_id FROM fg_cm_contact c LEFT JOIN fg_cm_change_toconfirm cm ON cm.contact_id = c.id WHERE cm.id IN ($selectedIds)");
            foreach ($result as $val) {
                $contactIds .= ',' . $val['fed_contact_id'];
            }
            $contactIds = ltrim($contactIds, ',');
            $updateContact = 'UPDATE fg_cm_contact c SET ' . $updateContactQry . ", c.created_at = now() WHERE c.fed_contact_id IN ($contactIds)";
            $this->conn->executeQuery($updateContact);
        }
    }

    /**
     * Function to update role contact status on dicarding removed functions.
     *
     * @param array  $selectedIdsArr Array of confirm ids
     * @param int    $clubId         Club Id
     * @param string $action         Confirm or discard
     */
    public function updateRoleContactFunctionStatus($selectedIdsArr, $clubId, $action = 'discard')
    {
        if (count($selectedIdsArr) > 0) {
            $selectedIds = implode(',', $selectedIdsArr);
            $confirmFunctionsQry = "SELECT r.category_id, cf.function_id, c.role_id, c.contact_id FROM fg_cm_change_toconfirm_functions cf LEFT JOIN fg_cm_change_toconfirm c ON cf.toconfirm_id = c.id LEFT JOIN fg_rm_role r ON r.id = c.role_id WHERE cf.toconfirm_id IN ($selectedIds) AND cf.action_type='REMOVED'";
            $confirmFunctions = $this->conn->fetchAll($confirmFunctionsQry);
            $updateQry = '';
            foreach ($confirmFunctions as $confirm) {
                $updateQry .= 'UPDATE fg_rm_role_contact c LEFT JOIN fg_rm_category_role_function crf ON crf.id = c.fg_rm_crf_id SET is_removed = 0 WHERE c.contact_id ='
                    . $confirm['contact_id'] . ' AND c.fg_rm_crf_id = (SELECT crf2.id FROM fg_rm_category_role_function crf2 WHERE crf2.category_id = ' . $confirm['category_id'] . ' AND crf2.function_id = '
                    . $confirm['function_id'] . ' AND crf2.role_id = ' . $confirm['role_id'] . ' AND crf2.club_id =' . $clubId . ') AND c.assined_club_id = ' . $clubId . ';';
            }
            if ($updateQry != '') {
                $this->conn->executeQuery($updateQry);
            }
        }
    }

    /**
     * Function to check whether a given contact is household head.
     *
     * @param int    $contactId Contact id
     * @param string $tableName Table name
     *
     * @return bool $householdHead Is household head or not
     */
    public function checkHouseholdHead($contactId, $tableName = '')
    {
        $result = $this->conn->fetchAll("SELECT is_household_head FROM fg_cm_contact WHERE id=$contactId");
        $householdHead = ($result['0']['is_household_head'] == '1') ? true : false;

        return $householdHead;
    }

    /**
     * Function to update role contact status on dicarding removed functions.
     *
     * @param string $type      contact type
     * @param int    $contactId contact id
     *
     * @return array
     */
    public function getContactDetailsForMembershipDetails($type, $contactId)
    {
        if (!is_numeric($contactId)) {
            return false;
        }
        $club = $this->container->get('club');
        $contactlistClass = new Contactlist($this->container, $contactId, $club, $type);
        $firstname = '`' . $this->container->getParameter('system_field_firstname') . '`';
        $lastname = '`' . $this->container->getParameter('system_field_lastname') . '`';
        $systemCompanyName = '`' . $this->container->getParameter('system_field_companyname') . '`';
        $systemPrimaryEmail = '`' . $this->container->getParameter('system_field_primaryemail') . '`';
        $systemDob = '`' . $this->container->getParameter('system_field_dob') . '`';
        $systemCorresOrt = '`' . $this->container->getParameter('system_field_corres_ort') . '`';
        $contactlistClass->setColumns(array($systemCompanyName, $firstname, $lastname, $systemPrimaryEmail, $systemDob, $systemCorresOrt, "(Select IF(fg_cm_membership_i18n.title_lang !='' AND fg_cm_membership_i18n.title_lang IS NOT NULL, fg_cm_membership_i18n.title_lang, fg_cm_membership.title) FROM fg_cm_membership LEFT JOIN fg_cm_membership_i18n  ON fg_cm_membership.id=fg_cm_membership_i18n.id AND fg_cm_membership_i18n.lang='" . $club->get('default_lang') . "'  WHERE fg_cm_membership.id=fg_cm_contact.club_membership_cat_id  ) AS  clubMembershipTitle", 'fcm.id as clubMembershipId', 'isMemberTitle', 'gender', 'isCompany', 'fedmembershipType', 'clubmembershipType', 'fedMembershipId', 'fg_cm_contact.is_sponsor', 'fg_cm_contact.is_subscriber', 'fg_cm_contact.intranet_access', '`68`', '`5`', '`21`', 'stealthMode', 'contactclubid', 'fg_cm_contact.fed_contact_id', 'fg_cm_contact.subfed_contact_id', 'fg_cm_contact.created_club_id', 'fg_cm_contact.fed_membership_cat_id,fg_cm_contact.is_fed_membership_confirmed', 'fg_cm_contact.fed_membership_cat_id', 'contactName(fg_cm_contact.fed_contact_id) AS contactName', 'fg_cm_contact.is_company'));
        $contactlistClass->setFrom('*');
        $contactlistClass->setCondition();
        $sWhere = " fg_cm_contact.id=$contactId";
        $contactlistClass->addCondition($sWhere);
        $contactlistClass->addJoin(' LEFT JOIN fg_cm_membership on fg_cm_contact.fed_membership_cat_id= fg_cm_membership.id');
        $contactlistClass->addJoin(' LEFT JOIN fg_cm_membership as fcm on fg_cm_contact.club_membership_cat_id= fcm.id');
        $listquery = $contactlistClass->getResult();
        $fieldsArray = $this->conn->fetchAll($listquery);

        return $fieldsArray;
    }

    /**
     * Function to return correspondence slanguage of contact.
     *
     * @param int $contactId Contact Id
     *
     * @return string
     */
    public function getContactLanguages($contactId)
    {
        $corrLang = '`' . $this->container->getParameter('system_field_corress_lang') . '`';
        $query = "SELECT M.$corrLang as corrLang from master_system M WHERE M.contact_id = :contactId";
        $result = $this->conn->fetchAll($query, array(':contactId' => $contactId));

        return $result[0];
    }

    /**
     * Method to get system language of a contact from master club table.
     *
     * @param int    $contactId contactId
     * @param string $clubTable master-club-table
     *
     * @return string
     */
    public function getContactSystemLanguage($contactId, $clubTable)
    {
        $result = $this->conn->fetchAll("SELECT `system_language` FROM fg_cm_contact WHERE id = $contactId");

        return $result['0']['system_language'];
    }

    /**
     * Method to update system language of a contact from master club table.
     *
     * @param int    $contactId  contactId
     * @param string $clubTable  master-club-table
     * @param string $systemLang language-code to update
     *
     * @return bool
     */
    public function updateContactSystemLanguage($contactId, $clubTable, $systemLang)
    {
        $updateQuery = "UPDATE fg_cm_contact SET `system_language`= '$systemLang' where id =$contactId";
        $result = $this->conn->executeQuery("$updateQuery");

        return true;
    }

    /**
     * Method to get emails of Forum followers.
     *
     * @param int $clubId    Current club-id
     * @param int $contactId Current contact-id
     * @param int $roleId    teamId/workgroupId
     *
     * @return array
     */
    public function getForumFollowerEmails($clubId, $contactId, $roleId)
    {
        $emailFieldName = $this->container->getParameter('system_field_primaryemail');
        $query = " SELECT c.id as id, `$emailFieldName` as email from fg_forum_followers FF "
            . 'JOIN fg_cm_contact c ON c.id = FF.contact_id '
            . 'JOIN master_system MASTER ON c.fed_contact_id = MASTER.fed_contact_id '
            . 'WHERE FF.club_id = :clubId AND FF.group_id = :roleId AND FF.contact_id != :contactId '
            . 'AND FF.is_follow_forum = 1';
        $result = $this->conn->executeQuery($query, array(':clubId' => $clubId, ':roleId' => $roleId, ':contactId' => $contactId));
        $resultArray = $result->fetchAll(\PDO::FETCH_GROUP);

        return $resultArray;
    }

    /**
     * Method to get emails of Forum topic followers (handled case when last-notification-send date should < read at date).
     *
     * @param int $topicId   Forum-topic-id
     * @param int $contactId Current contact-id
     *
     * @return array
     */
    public function getTopicFollowerEmails($topicId, $contactId)
    {
        $emailFieldName = $this->container->getParameter('system_field_primaryemail');
        $query = " SELECT C.id as id, `$emailFieldName` as email from fg_forum_contact_details FC "
            . 'JOIN fg_cm_contact C ON C.id = FC.contact_id '
            . 'JOIN master_system MASTER ON C.fed_contact_id = MASTER.fed_contact_id '
            . 'WHERE FC.contact_id != :contactId AND FC.forum_topic_id = :topicId AND is_notification_enabled  = 1 '
            . 'AND (FC.last_notification_send IS NULL OR FC.last_notification_send < FC.read_at)';
        $result = $this->conn->executeQuery($query, array(':topicId' => $topicId, ':contactId' => $contactId));
        $resultArray = $result->fetchAll(\PDO::FETCH_GROUP);

        return $resultArray;
    }

    /**
     * Method to get array of id, default_lang, default_system_lang of a particular contact.
     *
     * @param int    $contactId   contact for which details to be taken
     * @param int    $clubId      current club
     * @param string $masterTable club table
     *
     * @return array $resultArray array of id, default_lang, default_system_lang
     */
    public function getContactLanguageDetails($contactId, $clubId, $masterTable, $clubType = '')
    {
        $contact_field = ($clubType == 'federation') ? 'mc.fed_contact_id' : 'mc.contact_id';
        $corrLangField = $this->container->getParameter('system_field_corress_lang');
        $query = "SELECT DISTINCT fg_cm_contact.id,`$corrLangField` AS `default_lang`, fg_cm_contact.system_language AS `default_system_lang` "
            . "FROM $masterTable AS mc INNER JOIN fg_cm_contact ON $contact_field = fg_cm_contact.id "
            . 'INNER JOIN master_system as ms on ms.fed_contact_id = fg_cm_contact.fed_contact_id '
            . "WHERE $contact_field = :contactId  ";
        $result = $this->conn->executeQuery($query, array(':contactId' => $contactId));
        $resultArray = $result->fetchAll();

        return $resultArray;
    }

    /**
     * Method to get details of login status in datatable. It returns all result set and result with limit.
     *
     * @param bool   $canUserSetPrivacy Can user set privacy
     * @param string $primaryEMail      primary email field
     * @param int    $clubId            current    clubId
     * @param string $emailVisibility   field name
     * @param string $isEmailEditable   field name
     * @param int    $roleId            team/workgroup id
     * @param string $orderBy           order by string
     * @param int    $startValue        offset
     * @param int    $displayLength     limit of datas
     *
     * @return array of resultAll and result
     */
    public function getTeamLoginStatus($canUserSetPrivacy, $primaryEMail, $clubId, $emailVisibility, $isEmailEditable, $roleId, $orderBy, $startValue, $displayLength)
    {
        $corrLangField = $this->container->getParameter('system_field_corress_lang');
        $clubSystemLang = $this->container->get('club')->get('default_system_lang');
        $clubCorrLang = $this->container->get('club')->get('default_lang');
        switch ($this->container->get('club')->get('type')) {
            case 'federation':
                $mainfgcontactIdField = 'fed_contact_id';
                break;
            case 'sub_federation':
                $mainfgcontactIdField = 'subfed_contact_id';
                break;
            default:
                $mainfgcontactIdField = 'contact_id';
        }
        //echo $this->container->get('club')->get('type');exit;
        if ($canUserSetPrivacy == 1) {
            $columnArray = array('contactid', 'contactnamewithcomma', '`' . $primaryEMail . '` AS `email`', 'fg_cm_contact.last_login', "salutationTextOwnLocale(mc.{$mainfgcontactIdField}, $clubId, '$clubSystemLang', '$clubCorrLang', fg_cm_contact.system_language ) AS salutation_text, cp.privacy, gu.last_reminder, '$emailVisibility' as emailVisibility , '$isEmailEditable' as isEmailEditable");
        } else {
            $columnArray = array('contactid', 'contactnamewithcomma', '`' . $primaryEMail . '` AS `email`', 'fg_cm_contact.last_login', "salutationTextOwnLocale(mc.{$mainfgcontactIdField}, $clubId, '$clubSystemLang', '$clubCorrLang', fg_cm_contact.system_language ) AS salutation_text, gu.last_reminder, '$emailVisibility' as emailVisibility , '$isEmailEditable' as isEmailEditable");
        }
        array_push($columnArray, 'stealthMode');
        $contactlistClass = new Contactlist($this->container, $this->container->get('contact')->get('id'), $this->container->get('club'));
        $contactlistClass->setColumns($columnArray);
        $contactlistClass->setFrom();
        if ($canUserSetPrivacy == 1) {
            $contactlistClass->addJoin(" LEFT JOIN fg_cm_contact_privacy AS cp ON fg_cm_contact.id = cp.contact_id AND cp.attribute_id = $primaryEMail");
        }
        $contactlistClass->addJoin(" LEFT JOIN sf_guard_user AS gu ON gu.contact_id = fg_cm_contact.id AND gu.club_id = $clubId");
        $contactlistClass->setCondition();
        $contactlistClass->addCondition("mc.{$mainfgcontactIdField} IN(SELECT rc.contact_id FROM fg_rm_role AS r INNER JOIN fg_rm_category_role_function AS rcrf ON r.id = rcrf.role_id  INNER JOIN fg_rm_role_contact AS rc ON rcrf.id=rc.fg_rm_crf_id WHERE r.id=$roleId AND rc.assined_club_id=$clubId )");
        if ($orderBy) {
            $contactlistClass->addOrderBy($orderBy);
        }
        //Query without limit
        $listAllQuery = $contactlistClass->getResult();
        $resultAll = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($listAllQuery);
        //Query with limit
        $contactlistClass->setLimit("$startValue, $displayLength"); // "limit $startValue, $displayLength";
        $listQuery = $contactlistClass->getResult();

        $result = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($listQuery);

        return array('resultAll' => $resultAll, 'result' => $result);
    }

    /**
     * Method to get system_language, is_subscriber from clubTable and correspondence_language from master_system table of a contact.
     *
     * @param int    $contactId current contact-id
     * @param string $clubTable master_table of a club
     *
     * @return array array of system_language, is_subscriber, correspondence_lang
     */
    public function getContactSettingsDetails($contactId, $clubTable)
    {
        $fieldName = $this->container->getParameter('system_field_corress_lang');
        $sql = "SELECT fg_cm_contact.system_language, fg_cm_contact.`is_subscriber`, MAS.`$fieldName` as correspondence_lang FROM fg_cm_contact  "
            . "JOIN master_system MAS ON MAS.fed_contact_id = fg_cm_contact.fed_contact_id  WHERE fg_cm_contact.id = $contactId";
        $result = $this->conn->fetchAll("$sql");

        return $result[0];
    }

    /**
     * Method to update newsletter subscription of a contact from master club table.
     *
     * @param int    $contactId              contactId
     * @param string $clubTable              master-club-table
     * @param int    $newsletterSubscription 0/1
     *
     * @return bool
     */
    public function updateContactSubscription($contactId, $clubTable, $newsletterSubscription)
    {
        $updateQuery = "UPDATE fg_cm_contact SET is_subscriber= '$newsletterSubscription' where id =$contactId";
        $result = $this->conn->executeQuery("$updateQuery");

        return true;
    }

    /**
     * Update main table value with i18n of default language.
     *
     * @param type $defLang
     * @param type $clubId
     */
    public function updateContactAttributeDefault($defLang, $clubId)
    {
        $this->conn->executeQuery("INSERT INTO fg_cm_attribute (id,fieldname)(SELECT A.id,AI.fieldname_lang FROM fg_cm_attribute A INNER JOIN fg_cm_attribute_i18n AI ON A.id=AI.id AND AI.lang='$defLang' WHERE A.club_id=$clubId AND AI.fieldname_lang IS NOT NULL AND AI.fieldname_lang!='') ON DUPLICATE KEY UPDATE `fieldname`=VALUES(`fieldname`) ");
        $this->conn->executeQuery("INSERT INTO fg_cm_attributeset (id,title)(SELECT A.id,ASI.title_lang FROM fg_cm_attributeset A INNER JOIN fg_cm_attributeset_i18n ASI ON A.id=ASI.id AND ASI.lang='$defLang' WHERE A.club_id=$clubId AND ASI.title_lang IS NOT NULL AND ASI.title_lang!='') ON DUPLICATE KEY UPDATE `title`=VALUES(`title`) ");
    }

    /**
     * @param type $formValues
     * @param type $fieldType
     */
    public function getMergeableContacts($formValues, $fieldType, $contactId = false)
    {
        $primaryEmail = $this->container->getParameter('system_field_primaryemail');
        $catCommun = $this->container->getParameter('system_category_communication');
        $catPerson = $this->container->getParameter('system_category_personal');
        $clubId = $this->container->get('club')->get('id');
        $mergeableEContacts = $mergeableDuplicateQuery = array();
        $fedId = $this->container->get('club')->get('federation_id');
        $company = ($fieldType == 'Single person') ? '0' : '1';
        if (!empty($formValues[$catCommun][$primaryEmail])) {
            $mergeableEmailQuery = "SELECT fcm.title as fedTitle, C.*,MS.*,contactName(C.fed_contact_id) AS contactName,(SELECT GROUP_CONCAT(IF(CL.title !='', if(CL.id = CS.main_club_id,CONCAT(CL.title,'#mainclub#'),CL.title),'') SEPARATOR ', ') FROM fg_cm_contact CS INNER JOIN fg_club CL ON CL.id=CS.club_id WHERE CS.fed_contact_id=MS.fed_contact_id AND CL.club_type != 'federation' AND CL.club_type != 'sub_federation') as clubs FROM master_system MS "
                . 'INNER JOIN fg_cm_contact C ON C.id=MS.fed_contact_id '
                . 'LEFT JOIN fg_cm_membership fcm ON fcm.id=C.fed_membership_cat_id '
                . "WHERE lower(MS.`$primaryEmail`)=lower('{$formValues[$catCommun][$primaryEmail]}') AND C.is_company=$company AND (C.fed_membership_cat_id IS NOT NULL AND (C.old_fed_membership_id IS NOT NULL OR C.is_fed_membership_confirmed='0')) AND C.created_club_id IN (SELECT id FROM fg_club WHERE federation_id = $fedId AND id != $fedId AND id != $clubId AND club_type != 'sub_federation')";

            $mergeableEContacts = $this->conn->fetchAll($mergeableEmailQuery);
        }
        if ($fieldType == 'Single person' && count($mergeableEContacts) < 1) {
            $firstname = $this->container->getParameter('system_field_firstname');
            $lastname = $this->container->getParameter('system_field_lastname');
            $dob = $this->container->getParameter('system_field_dob');
            $land = $this->container->getParameter('system_field_corres_ort');
            $corrCat = $this->container->getParameter('system_category_address');
            $firstnameVal = $formValues[$catPerson][$firstname];
            $lastnameVal = $formValues[$catPerson][$lastname];
            $dobVal = $formValues[$catPerson][$dob];
            if ($dobVal != '') {
                if (!date_create_from_format('Y-m-d', $dobVal)) {
                    $date = new \DateTime();
                    $dobVal = $date->createFromFormat(FgSettings::getPhpDateFormat(), $dobVal)->format('Y-m-d');
                }
            }
            $landVal = $formValues[$corrCat][$land];
            $dobNull = " MS.`$dob`!='' AND MS.`$dob` IS NOT NULL AND MS.`$dob`!='0000-00-00' ";
            $mergeableDuplicateQuery = "SELECT fcm.title as fedTitle,C.*,MS.*,contactName(C.fed_contact_id) AS contactName, "
                . "(SELECT GROUP_CONCAT( IF(CL.title !='', if(CL.id = CS.main_club_id,CONCAT(CL.title,'#mainclub#'),CL.title),'') "
                . " SEPARATOR ', ') FROM fg_cm_contact CS INNER JOIN fg_club CL ON CL.id=CS.club_id WHERE CS.fed_contact_id=MS.fed_contact_id "
                . " AND CL.club_type != 'federation' AND CL.club_type != 'sub_federation') as clubs  FROM master_system MS "
                . 'INNER JOIN fg_cm_contact C ON C.id=MS.fed_contact_id '
                . 'LEFT JOIN fg_cm_membership fcm ON fcm.id=C.fed_membership_cat_id '
                . 'WHERE C.is_company=0 AND '
                . "((MS.`$firstname`='$firstnameVal' AND MS.`$lastname`='$lastnameVal' AND MS.`$dob`='$dobVal' AND $dobNull ) OR "
                . "(MS.`$firstname`='$firstnameVal' AND MS.`$lastname`='$lastnameVal' AND MS.`$land`='$landVal' AND MS.`$land`!='') OR "
                . "(MS.`$firstname`='$firstnameVal' AND MS.`$dob`='$dobVal' AND MS.`$land`='$landVal' AND MS.`$land`!='' AND $dobNull ) OR "
                . "(MS.`$lastname`='$lastnameVal' AND MS.`$dob`='$dobVal' AND MS.`$land`='$landVal' AND MS.`$land`!='' AND $dobNull)) AND "
                . "C.created_club_id IN (SELECT id FROM fg_club WHERE federation_id = $fedId AND id != $fedId AND id != $clubId AND club_type != 'sub_federation')"
                . " AND C.fed_membership_cat_id IS NOT NULL AND (C.old_fed_membership_id IS NOT NULL OR C.is_fed_membership_confirmed='0') AND '$clubId' NOT IN (SELECT club_id FROM fg_cm_contact C1 WHERE MS.fed_contact_id= C1.fed_contact_id)";
            $mergeableDuplContacts = $this->conn->fetchAll($mergeableDuplicateQuery);
            //exclude mergable contact from list if email already exist for club contact.
            if (count($mergeableDuplContacts) > 0) {
                foreach ($mergeableDuplContacts as $key => $merg) {
                    if (!empty($merg[3])) {
                        if ($contactId) {
                            $currentContactQ = "AND CT.fed_contact_id !=(SELECT fed_contact_id FROM fg_cm_contact WHERE id=$contactId limit 1) ";
                        }
                        $mergeableDupliEmailQ = "SELECT M.fed_contact_id FROM master_system M INNER JOIN fg_cm_contact CT ON CT.fed_contact_id=M.fed_contact_id AND CT.club_id=$clubId WHERE lower(M.`$primaryEmail`)= lower('{$merg[3]}') $currentContactQ";
                        $mergDuplEmails = $this->conn->fetchAll($mergeableDupliEmailQ);
                        if (count($mergDuplEmails) > 0) {
                            unset($mergeableDuplContacts[$key]);
                        }
                    }
                }
            }
        }
        return array('duplicates' => $mergeableDuplContacts, 'mergeEmail' => $mergeableEContacts);
    }

    /**
     * This function is used to insert entry in fg_cm_contact table.
     *
     * @param array $insertContactSet       Array of values to be updated
     * @param array $insertContactSetValues Array of data to be bound
     *
     * @return int $contactId The inserted contact id
     */
    public function insertToContactTable($insertContactSet, $insertContactSetValues)
    {
        $insertContactQuery = 'INSERT INTO fg_cm_contact SET ' . implode(',', $insertContactSet); // . ' ON DUPLICATE KEY UPDATE ' . implode(',', $this->insertContactDupSetValues);
        $this->conn->executeQuery($insertContactQuery, $insertContactSetValues);
        $contactId = $this->conn->lastInsertId();

        return $contactId;
    }

    /**
     * This function is used to insert entry in sf_guard_user table.
     *
     * @param int $clubId       Club id
     * @param int $newClubId    New club id
     * @param int $contactId    Contact id
     * @param int $newContactId New contact id
     */
    public function insertToUserTable($clubId, $newClubId, $contactId, $newContactId)
    {
        $insertUserQuery = 'INSERT INTO `sf_guard_user`(`username`, `username_canonical`, `email`, `email_canonical`, '
            . '`salt`, `password`, `created_at`, `contact_id`, `club_id`) SELECT `username`, `username_canonical`, '
            . '`email`, `email_canonical`, `salt`, `password`, :date, :newContactId, :newClubId FROM `sf_guard_user` '
            . 'WHERE `contact_id` = :contactId AND `club_id` = :clubId';
        $insertUserValues = array(':clubId' => $clubId, ':newClubId' => $newClubId, ':contactId' => $contactId, ':newContactId' => $newContactId, ':date' => date('Y-m-d H:i:s'));
        $this->conn->executeQuery($insertUserQuery, $insertUserValues);
    }

    /**
     * This function is used to insert entry in master table.
     *
     * @param string $masterTable           Master table name
     * @param array  $insertMasterSet       Array of values to be updated
     * @param array  $insertMasterSetValues Array of data to be bound
     */
    public function insertToMasterTable($masterTable, $insertMasterSet, $insertMasterSetValues)
    {
        $insertMasterQuery = "INSERT INTO {$masterTable} SET " . implode(',', $insertMasterSet);
        $this->conn->executeQuery($insertMasterQuery, $insertMasterSetValues);
    }

    public function copySfGuardUserEntry($clubId, $contactId, $newContactId)
    {
        //copy sf_guard_user_entries
        $insertUserQuery = 'INSERT INTO `sf_guard_user`(`first_name`, `last_name`, `username`, `username_canonical`, `email`, '
            . '`email_canonical`, `algorithm`, `salt`, `password`, `is_active`, `is_super_admin`, `last_login`, `created_at`, '
            . '`updated_at`, `contact_id`, `club_id`, `is_security_admin`, `is_readonly_admin`, `is_team_admin`, `is_team_section_admin`, '
            . '`last_reminder`, `enabled`, `plain_password`, `locked`, `expired`, `expires_at`, `confirmation_token`, `password_requested_at`, '
            . '`roles`, `credentials_expired`, `credentials_expire_at`, `has_full_permission`) SELECT `first_name`, `last_name`, `username`, '
            . '`username_canonical`, `email`, `email_canonical`, `algorithm`, `salt`, `password`, `is_active`, `is_super_admin`, `last_login`, '
            . '`created_at`, `updated_at`, :newContactId , `club_id`, `is_security_admin`, `is_readonly_admin`, `is_team_admin`, `is_team_section_admin`, '
            . '`last_reminder`, `enabled`, `plain_password`, `locked`, `expired`, `expires_at`, `confirmation_token`, `password_requested_at`, '
            . '`roles`, `credentials_expired`, `credentials_expire_at`, `has_full_permission` FROM `sf_guard_user` WHERE `contact_id` = :contactId AND `club_id` = :clubId';
        $insertUserValues = array(':clubId' => $clubId, ':contactId' => $contactId, ':newContactId' => $newContactId);
        $this->conn->executeQuery($insertUserQuery, $insertUserValues);
    }

    public function copyMasterSystemEntry($fedContactId, $newfedContactId)
    {
        $profilePicture = $this->container->getParameter('systemCommunityPicture');
        $companyLogo = $this->container->getParameter('systemcompanyLogo');
        $systemFieldIds = array_keys($this->container->get('club')->get('systemFields'));
        $systemFieldsQry = implode('`,`', $systemFieldIds);

        $profilePicDetails = $this->em->getRepository('CommonUtilityBundle:MasterSystem')->getProfilePicDetails($fedContactId);
        $fileType = ($profilePicDetails['isCompany']) ? 'companylogo' : 'profilepic';
        $attributeId = ($profilePicDetails['isCompany']) ? $companyLogo : $profilePicture;
        $fgAvatarService = $this->container->get('fg.avatar');
        $newProfilePicName = ($profilePicDetails['fileName'] != '') ? $fgAvatarService->copyFilesOfContactOnRemovingFedMembership($attributeId, $profilePicDetails['fileName'], $fileType) : '';
        $profilePicQry = ($profilePicDetails['isCompany']) ? $companyLogo : $profilePicture;

        $insertMasterSystemQuery = 'INSERT INTO `master_system` (`fed_contact_id`, `' . $systemFieldsQry . '`,`' . $profilePicQry . '`) SELECT :newFedContactId, `' . $systemFieldsQry . '`,"' . $newProfilePicName . '" FROM `master_system` WHERE `fed_contact_id` = :fedContactId';
        $insertMasterSystemValues = array(':fedContactId' => $fedContactId, ':newFedContactId' => $newfedContactId);
        $this->conn->executeQuery($insertMasterSystemQuery, $insertMasterSystemValues);
    }

    public function copyMasterFederationEntry($clubId, $fedContactId, $newfedContactId, $clubType = 'federation')
    {
        $fileFields = $this->container->get('club')->get('fileFields');
        if ($clubType == 'sub_federation') {
            $masterFederationFieldIds = array_keys($this->container->get('club')->get('subFedFields'));
            $insertMasterFederationQuery = 'INSERT INTO `master_federation_' . $clubId . '` (`club_id`, `contact_id`';
        } else {
            $masterFederationFieldIds = array_keys($this->container->get('club')->get('fedFields'));
            $insertMasterFederationQuery = 'INSERT INTO `master_federation_' . $clubId . '` (`club_id`, `fed_contact_id`';
        }
        $fgAvatarService = $this->container->get('fg.avatar');
        $fedFileFieldIds = array_intersect($fileFields, $masterFederationFieldIds);
        $toUpdateFieldDetails = array();
        if (count($fedFileFieldIds) > 0) {
            $masterFederationFieldIds = array_diff($masterFederationFieldIds, $fedFileFieldIds);
            $fileFieldDetails = $this->getFileFieldDetails($clubId, $clubType, $fedFileFieldIds, $fedContactId);
            foreach ($fedFileFieldIds as $fedFileFieldId) {
                $filename = $fileFieldDetails[$fedFileFieldId];
                $newFileName = ($filename != '') ? $fgAvatarService->copyFilesOfContactOnRemovingFedMembership($fedFileFieldId, $filename, '', $clubId) : '';
                $toUpdateFieldDetails[$fedFileFieldId] = $newFileName;
            }
        }
        $masterFederationFieldIdsQry .= (count($masterFederationFieldIds) > 0) ? ', `' . implode('`,`', $masterFederationFieldIds) . '`' : '';
        $insertMasterFederationQuery .= (count($toUpdateFieldDetails) > 0) ? ', `' . implode('`,`', array_keys($toUpdateFieldDetails)) . '`' : '';
        $insertMasterFederationQuery .= $masterFederationFieldIdsQry . ') SELECT :clubId, :newFedContactId';
        $insertMasterFederationQuery .= (count($toUpdateFieldDetails) > 0) ? (',"' . implode('","', array_values($toUpdateFieldDetails)) . '"') : '';
        $insertMasterFederationQuery .= $masterFederationFieldIdsQry . ' FROM `master_federation_' . $clubId . '` WHERE ';
        $insertMasterFederationQuery .= ($clubType == 'sub_federation') ? '`contact_id` = :fedContactId' : '`fed_contact_id` = :fedContactId';
        $insertMasterFederationValues = array(':clubId' => $clubId, ':fedContactId' => $fedContactId, ':newFedContactId' => $newfedContactId);
        $this->conn->executeQuery($insertMasterFederationQuery, $insertMasterFederationValues);
    }

    /**
     * This function is used to get the contact names corresponding to contact ids.
     *
     * @param array $contactIds Contact ids
     *
     * @return array $contactNames Array of contact names
     */
    public function getContactNames($contactIds)
    {
        $contactIdsStr = implode(',', $contactIds);
        $result = $this->conn->fetchAll("SELECT C.id, contactNameNoSort(C.id, 0) AS contactname FROM fg_cm_contact C left join master_system S on C.fed_contact_id=S.fed_contact_id where C.id IN ($contactIdsStr)");
        foreach ($result as $data) {
            $contactNames[$data['id']] = $data['contactname'];
        }

        return $contactNames;
    }

    /**
     * To set the FFM flag of archived contact.
     *
     * @param array $contactDetailarray contact details
     */
    public function setFormerfederationflag($clubId, $contactDetailarray)
    {
        $currentDate = date('Y-m-d H:i:s');
        //check if the array contain sub fed array
        if (isset($contactDetailarray['SubfedContactId']) && $contactDetailarray['SubfedContactId'] != 0) {
            $updateQuery1 = "UPDATE fg_cm_contact c1 LEFT JOIN fg_cm_contact c2 ON c1.id=c2.subfed_contact_id AND c2.club_id !={$clubId} AND c1.id!=c2.id SET c1.is_former_fed_member=1, c1.resigned_on='{$currentDate}' WHERE c2.id IS NULL AND c1.id=" . $contactDetailarray['SubfedContactId'];
            $result = $this->conn->executeQuery("$updateQuery1");
        }
        //checking for fed own member or not
        $subPart = ($contactDetailarray['SubfedContactId'] != '') ? " AND c2.id!={$contactDetailarray['SubfedContactId']}" : '';
        $updateQuery2 = "UPDATE  fg_cm_contact c1 LEFT JOIN fg_cm_contact c2 ON c1.id=c2.fed_contact_id AND c2.club_id !={$clubId} AND c1.id!=c2.id {$subPart} SET c1.is_former_fed_member=1, c1.resigned_on='{$currentDate}' WHERE c2.id IS NULL  AND  c1.id=" . $contactDetailarray['FedContactId'];
        $result = $this->conn->executeQuery("$updateQuery2");
    }

    /**
     * To set the FFM flag of reacivated contact.
     *
     * @param array $fedcontactId federation contact id
     */
    public function setFormerfederationflagFromreacivate($fedcontactId)
    {
        $updateQuery = 'UPDATE fg_cm_contact SET is_former_fed_member=0 WHERE fed_contact_id =' . $fedcontactId;
        $result = $this->conn->executeQuery("$updateQuery");
    }

    /**
     * Function to update sfGuard User entries while update email.
     *
     * @param Integer $fedContactId Contact id
     */
    public function updateSfguardUser($fedContactId)
    {
        $primaryEmail = $this->container->getParameter('system_field_primaryemail');
        $sfGuardUpdateSql = "UPDATE `sf_guard_user` S INNER JOIN fg_cm_contact C ON C.id=S.contact_id INNER JOIN master_system M ON M.fed_contact_id=C.fed_contact_id SET S.`username` = M.`$primaryEmail`, S.`username_canonical`=M.`$primaryEmail`, S.`email`=M.`$primaryEmail`, S.`email_canonical`=M.`$primaryEmail`
                WHERE C.fed_contact_id=:contactId ";

        $this->conn->executeQuery($sfGuardUpdateSql, array(':contactId' => $fedContactId));
    }

    /**
     * Function to insert sfGuard User entries while creating.
     *
     * @param Integer $fedContactId Contact id
     */
    public function insertIntoSfguardUser($fedContactId)
    {
        $primaryEmail = $this->container->getParameter('system_field_primaryemail');
        $sfGuardInsertSql = 'INSERT INTO `sf_guard_user` (`username`, `username_canonical`, `email`, `email_canonical`,`created_at`, `updated_at`, `contact_id`, `club_id`) ';

        $sfGuardInsertSql .= "SELECT M.`$primaryEmail`, M.`$primaryEmail`, M.`$primaryEmail`, M.`$primaryEmail`, NOW(), '0000-00-00 00:00:00',C.id, C.club_id FROM fg_cm_contact C INNER JOIN master_system M ON M.fed_contact_id=C.fed_contact_id WHERE C.fed_contact_id=$fedContactId AND (C.main_club_id=C.club_id OR (C.fed_membership_cat_id IS NOT NULL AND (C.old_fed_membership_id IS NOT NULL OR C.is_fed_membership_confirmed='0')) )"
            . ' ON DUPLICATE KEY UPDATE email = VALUES(email), email_canonical = VALUES(email_canonical), username = VALUES(username), username_canonical = VALUES(username_canonical) ';
        $this->conn->executeQuery($sfGuardInsertSql);
    }

    /**
     * Function to get contact_id in current club.
     *
     * @param type $fedContactId
     * @param type $clubId
     *
     * @return type
     */
    public function getClubContactId($fedContactId, $clubId, $type = 'active')
    {
        $condition = ($type == 'active') ? "AND (C.main_club_id=C.club_id OR (C.fed_membership_cat_id IS NOT NULL AND (C.old_fed_membership_id IS NOT NULL OR C.is_fed_membership_confirmed='0')))" : '';
        $result = $this->conn->fetchAll("SELECT C.* FROM fg_cm_contact C WHERE C.fed_contact_id={$fedContactId} AND C.club_id={$clubId} " . $condition);

        return (count($result) > 0) ? $result[0] : false;
    }

    /**
     * Function to update main contact when removing fed membership or archiving contact.
     *
     * @param type $oldFedContactId Old fed contact id before spliting
     * @param type $newFedContactId New fed contact id after spliting same as old fed contact id if not shared
     * @param type $clubId          Current club id
     * @param type $actionType      archive/remove
     */
    public function sharedMainContactUpdation($oldFedContactId, $newFedContactId, $clubId, $actionType = 'archive')
    {
        $currContact = $this->container->get('contact')->get('id');
        $result = $this->conn->fetchAll("SELECT C.* FROM fg_cm_contact C WHERE C.id={$oldFedContactId} ");
        $activeC = "AND (C.main_club_id=C.club_id OR (C.fed_membership_cat_id IS NOT NULL AND (C.old_fed_membership_id IS NOT NULL OR C.is_fed_membership_confirmed='0')))";
        if (count($result) > 0) {
            //company contact
            if ($result[0]['is_company'] && !empty($result[0]['comp_def_contact'])) {
                $mainContact = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->findOneBy(array('fedContact' => $result[0]['comp_def_contact'], 'club' => $clubId));
                //not shared contact
                if ($oldFedContactId == $newFedContactId) {
                    //main contact is own contact of fed/subfed
                    if (!$mainContact) {
                        //change to manuel contact, companies in current club
                        $this->changeToNoMainContact($newFedContactId, $currContact);
                        //update last updated of main contact
                        $this->conn->executeQuery("UPDATE fg_cm_contact C SET C.last_updated=NOW() WHERE C.fed_contact_id={$result[0]['comp_def_contact']}");
                    }
                } else { //shared contact
                    //if main contact exist in current club
                    if ($mainContact) {
                        //main contact is club own contact,with out fed membership, change main contact type
                        $mainFedMem = $mainContact->getFedMembershipCat();
                        $mainOldFedMem = $mainContact->getOldFedMembership();
                        if (empty($mainFedMem) || (empty($mainOldFedMem) && $mainContact->getIsFedMembershipConfirmed() == '1')) {
                            $this->changeToNoMainContact($oldFedContactId, $currContact);
                        }
                    } else { //if main contact not in current club
                        //main contact not in current club, change main contact of new contact
                        $this->changeToNoMainContact($newFedContactId, $currContact);
                        $clubObj = $this->em->getRepository('CommonUtilityBundle:FgClub')->findOneBy(array('id' => $clubId));
                        if ($clubObj->getClubType() == 'sub_federation_club') {
                            //check whether the old company contact exist in subfederation after spliting
                            $subfedCompanyContact = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->findOneBy(array('fedContact' => $oldFedContactId, 'club' => $clubObj->getParentClubId()));
                            //check whether the main contact exist in subfederation
                            $subfedMainContact = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->findOneBy(array('fedContact' => $result[0]['comp_def_contact'], 'club' => $clubObj->getParentClubId()));
                            //if company contact not in sub federation and main contact contact is in subfederation
                            if ($subfedMainContact && !$subfedCompanyContact) {
                                //if main contact is sub fed own contact with out fed membership
                                $subMainFedMem = $subfedMainContact->getFedMembershipCat();
                                $subOldFedMem = $subfedMainContact->getOldFedMembership();
                                if (empty($subMainFedMem) || (empty($subOldFedMem) && $subfedMainContact->getIsFedMembershipConfirmed() == '1')) {
                                    $this->changeToNoMainContact($oldFedContactId, $currContact);
                                    //update last updated of main contact
                                    $this->conn->executeQuery("UPDATE fg_cm_contact C SET C.last_updated=NOW() WHERE C.fed_contact_id={$result[0]['comp_def_contact']}");
                                }
                            }
                        }
                    }
                }
            } else { //single person contact
                //not shared contact
                if ($oldFedContactId == $newFedContactId) {
                    //get company contacts with main contact as current contact in current club
                    $companyContact = $this->conn->fetchAll("SELECT GROUP_CONCAT(fed_contact_id) as company FROM fg_cm_contact C WHERE C.comp_def_contact={$result[0]['fed_contact_id']} AND C.club_id=$clubId $activeC");
                    $companyCond = (empty($companyContact[0]['company']) || $actionType == 'archive') ? '' : "AND C.fed_contact_id NOT IN ({$companyContact[0]['company']})";
                    //change to manuel contact, companies not in current club
                    $this->changeToNoMainContact($result[0]['fed_contact_id'], $currContact, $companyCond, 'comp_def_contact');
                } else { //shared contact
                    $mainClub[] = $clubId;
                    //check for sub federation club
                    $clubObj = $this->em->getRepository('CommonUtilityBundle:FgClub')->findOneBy(array('id' => $clubId));
                    if ($clubObj->getClubType() == 'sub_federation_club') {
                        //check whether the old contact exist in subfederation after spliting
                        $subfedContact = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->findOneBy(array('fedContact' => $oldFedContactId, 'club' => $clubObj->getParentClubId()));
                        //if old contact not exist in sub federation then push su federation id to main club id
                        if (!$subfedContact) {
                            $mainClub[] = $clubObj->getParentClubId();
                        }
                    }
                    //change to manuel contact, club/subfed own companies with out fed membership
                    $this->changeToNoMainContact($result[0]['fed_contact_id'], $currContact, "AND C.main_club_id IN (" . implode(',', $mainClub) . ") AND (C.fed_membership_cat_id='' OR C.fed_membership_cat_id IS NULL)", 'comp_def_contact');
                }
            }
        }
    }

    /**
     * Change a contact to no main contact option.
     *
     * @param int    $fedContactId Fed contact id
     * @param int    $currContact  Login contact id
     * @param string $condition    Condition for updaton (if any)
     * @param string $field        fed_contact_id/comp_def_contact
     */
    public function changeToNoMainContact($fedContactId, $currContact, $condition = '', $field = 'fed_contact_id')
    {
        $this->conn->executeQuery("INSERT INTO `fg_cm_log_connection` (`contact_id`,`linked_contact_id`,`assigned_club_id`,`date`,`connection_type`,`relation`,`value_before`,`value_after`,`changed_by`,`type`)  "
            . "(SELECT C.id,C.comp_def_contact,NULL,NOW(),'Main contact',C.comp_def_contact_fun,(SELECT contactname(S.fed_contact_id) from master_system S where S.fed_contact_id=C.fed_contact_id limit 1),'-',$currContact,'global' FROM fg_cm_contact C WHERE C.$field=$fedContactId AND C.id=C.fed_contact_id $condition)");
        $this->conn->executeQuery("INSERT INTO `fg_cm_log_connection` (`contact_id`,`linked_contact_id`,`assigned_club_id`,`date`,`connection_type`,`relation`,`value_before`,`value_after`,`changed_by`,`type`)  "
            . "(SELECT C.comp_def_contact,C.id,NULL,NOW(),'Main contact of company',C.comp_def_contact_fun,(SELECT contactname(S.fed_contact_id) from fg_cm_contact S where S.id=C.comp_def_contact limit 1),'-',$currContact,'global' FROM fg_cm_contact C WHERE C.$field=$fedContactId AND C.id=C.fed_contact_id $condition)");
        $this->conn->executeQuery("UPDATE master_system m INNER JOIN fg_cm_contact C ON C.fed_contact_id=m.fed_contact_id SET m.`1`=NULL,m.`2`=NULL,m.`4`=NULL,m.`23`=NULL,m.`70`=NULL,m.`107`=NULL,m.`72`=NULL,m.`76`=NULL WHERE C.$field=$fedContactId $condition");
        $this->conn->executeQuery("UPDATE fg_cm_contact C SET C.last_updated=NOW(),C.comp_def_contact=NULL,C.comp_def_contact_fun=NULL,C.has_main_contact=0 WHERE C.$field=$fedContactId $condition");
    }

    /**
     * This function is used to update the salt, password of a contact after merging to another contact.
     *
     * @param int $fedContactId Fed contact id
     */
    public function updateSfguardUserPassword($fedContactId)
    {
        $sql = 'UPDATE sf_guard_user t, (SELECT DISTINCT salt, password FROM sf_guard_user WHERE contact_id = :fedContactId) t1 '
            . 'SET t.salt = t1.salt, t.password = t1.password WHERE t.contact_id IN (SELECT id FROM fg_cm_contact WHERE fed_contact_id = :fedContactId)';
        $this->conn->executeQuery($sql, array(':fedContactId' => $fedContactId));
    }

    /**
     * Function to check whether the user is a fed admin and the checking the club id is the federation.
     *
     * @param type $userId        Logging user id
     * @param type $currentClubId ClubId
     * @param type $federationId  Federation id
     *
     * @return bool
     */
    public function checkFedAdminContact($userId, $currentClubId, $federationId)
    {
        $sql = 'SELECT sfu.club_id,sgg.roles, c.federation_id'
            . ' FROM sf_guard_user sfu'
            . ' LEFT JOIN sf_guard_user_group sgug ON sgug.user_id=sfu.id'
            . ' LEFT JOIN sf_guard_group sgg ON sgg.id=sgug.group_id'
            . ' LEFT JOIN fg_club c ON c.id = sfu.club_id'
            . " WHERE sfu.id = $userId";
        $results = $this->conn->fetchAll($sql);
        $hasFedAdmin = false;

        foreach ($results as $val) {
            $userClubId = $val['club_id'];
            $userFederationId = $val['federation_id'];
            if (strpos($val['roles'], 'ROLE_FED_ADMIN') == true) {
                $hasFedAdmin = true;
            }
        }
        if ($hasFedAdmin) {
            if (($federationId == $userFederationId) || ($federationId == $userClubId)) {
                $hasFedAdmin = true;
            } else {
                $hasFedAdmin = false;
            }
        }

        return $hasFedAdmin;
    }

    /**
     * Function to update entries as subscriber  of imported user.
     *
     * @param type $club
     * @param type $importTable
     */
    public function updateImortedContactSubscriber($club, $importTable)
    {
        $clubId = $club->get('id');
        $primaryEmail = $this->container->getParameter('system_field_primaryemail');
        $updateSubscriber = "Update fg_cm_contact  C INNER JOIN `fg_cn_subscriber` S ON C.club_id=S.club_id  INNER JOIN master_system M ON M.fed_contact_id=C.fed_contact_id and lower(S.`email`)=lower(M.`$primaryEmail`) INNER JOIN $importTable T ON T.fed_contact_id=M.fed_contact_id SET C.is_subscriber=1 where C.fed_contact_id=T.fed_contact_id ";
        $this->conn->executeQuery("$updateSubscriber");
        $delSubscriber = "Delete S from `fg_cn_subscriber` S JOIN fg_cm_contact C ON C.club_id=S.club_id INNER JOIN master_system M ON M.fed_contact_id=C.fed_contact_id and lower(S.`email`)=lower(M.`$primaryEmail`) INNER JOIN $importTable T ON T.fed_contact_id=M.fed_contact_id  where C.fed_contact_id=T.fed_contact_id  ";
        $this->conn->executeQuery("$delSubscriber");
        
        $sql = "SELECT GROUP_CONCAT(contact_id) As idList FROM $importTable";
        $results = $this->conn->fetchAll($sql);
        $contactIdArray = explode(',',$results[0]['idList']);
        return $contactIdArray;
    }

    /**
     * Function to reorder sort value after deletion of a row
     *
     * @param String $tableName      Table name of sort table
     * @param String $joinFieldName  Field name of table
     * @param String $joinFieldValue Field value
     * @param String $sortField      Sort field name
     *
     * @return boolean
     */
    public function reorderSortPosition($tableName, $joinFieldName, $joinFieldValue, $sortField)
    {
        $query = "SET @a = 0; UPDATE $tableName AI SET AI.`sort_order` = @a:=@a+1  WHERE $joinFieldName = $joinFieldValue  ORDER BY AI.`$sortField` ASC";
        $this->conn->executeQuery($query);

        return true;
    }

    public function getFileFieldDetails($clubId, $clubtype, $fieldIdsArr = array(), $contactId)
    {
        if (count($fieldIdsArr) > 0) {
            $table = 'master_federation_' . $clubId;
            $contactColumn = ($clubtype == 'federation') ? 'fed_contact_id' : 'contact_id';
            $selectqry = implode('`,`', $fieldIdsArr);
            $sql = 'SELECT `' . $selectqry . '` FROM ' . $table . ' WHERE ' . $contactColumn . ' = :contactId';
            $result = $this->conn->fetchAll($sql, array(':contactId' => $contactId));

            return $result[0];
        }
    }

    /**
     * Function to share imported contacts
     *
     * @param string $importTable Temporary table for import
     * @param object $log         Log file object
     */
    public function shareImortedContact($importTable, $log)
    {
        $selectShareContactQuery = "SELECT C.*,T.share_club_ids FROM fg_cm_contact C INNER JOIN  {$importTable} T ON C.import_id=T.row_id AND T.main_club_id = C.club_id WHERE T.share_club_ids !='' AND C.import_table='$importTable' ";
        $selectShareContact = $this->conn->fetchAll("$selectShareContactQuery");
        fwrite($log, "Sharing started for imported contacts");
        foreach ($selectShareContact as $key => $contacts) {
            $selectShareClubs = $this->conn->fetchAll("SELECT id, federation_id, sub_federation_id, club_type  FROM fg_club C WHERE C.id IN ({$contacts['share_club_ids']}) ");
            fwrite($log, "\n contacts-{$contacts['fed_contact_id']} ");
            foreach ($selectShareClubs as $key => $clubs) {
                $fedmemObj = new FgFedMemberships($this->container);
                $cluVar = array('id' => $clubs['id'], 'federationId' => $clubs['federation_id'], 'sub_federation_id' => $clubs['sub_federation_id'], 'clubType' => $clubs['club_type']);
                $cluVar['fedContactId'] = $contacts['fed_contact_id'];
                if (!$fedmemObj->shareImportedContact($cluVar)) {
                    fwrite($log, "\n\tsharing to {$clubs['id']} failed");
                }
            }
        }
        fwrite($log, "\nAll contacts are successfully shared\n ");
    }

    /**
     * This function is used to get the contact fields to be populated in
     * contact table element second step 'select columns'
     *
     * @return array $fieldsArray Array of all contact fields available in this club with details
     */
    public function getContactFieldsForContactTableColumns()
    {
        $club = $this->container->get('club');
        $defaultLang = $club->get('default_lang');
        $defaultSystemLang = $club->get('default_system_lang');
        $clubId = $club->get('id');
        $clubHeirarchy = implode(',', $club->get('clubHeirarchy'));

        $sort = "( CASE cas.club_id WHEN 1 THEN '1' %s WHEN '$clubId' THEN '" . (count($club->get('clubHeirarchy')) + 2) . "' END ) AS sort";
        $sortSub = '';
        foreach ($club->get('clubHeirarchy') as $key => $rowHierchyClub) {
            $sortSub .= "WHEN '{$rowHierchyClub}' THEN '" . ($key + 2) . "' ";
        }
        $sort = sprintf($sort, $sortSub);


        switch ($club->get('type')) {
            case 'standard_club':
            case 'federation':
                $fieldVisibility = "ca.club_id=$clubId ";
                break;
            case 'federation_club':
                $fieldVisibility = "(ca.club_id IN ($clubHeirarchy) AND ca.availability_club!='not_available') OR (ca.club_id=$clubId) ";
                break;
            case 'sub_federation': case 'sub_sub_federation':
                $fieldVisibility = "(ca.club_id IN ($clubHeirarchy) AND ca.availability_sub_fed!='not_available') OR (ca.club_id=$clubId) ";
                break;
            case 'sub_federation_club': case 'sub_level_club':
                $fieldVisibility = "(ca.club_id IN ($clubHeirarchy) AND ca.availability_club!='not_available') OR (ca.club_id=$clubId)";
                break;
        }

        $contactFieldsSql = "SELECT ca.id AS id, cai18n.lang as attrLang, ca.fieldname AS fieldName, cai18n.fieldname_lang AS fieldNameLang, ca.fieldname AS title, cai18n.fieldname_short_lang as shortNameLang, ca.fieldname_short as shortName, ca.input_type AS type, IF(casi18n.title_lang IS NULL OR casi18n.title_lang='', cas.title, casi18n.title_lang) AS selectgroup, "
            . "ca.club_id, cas.id AS catId, $sort, ca.addres_type, ca.address_id, ca.is_system_field AS isSystemField "
            . 'FROM fg_cm_club_attribute AS cca '
            . 'LEFT JOIN fg_cm_attribute AS ca ON cca.attribute_id = ca.id '
            . "LEFT JOIN fg_cm_attribute_i18n AS cai18n ON cai18n.id = ca.id "
            . 'LEFT JOIN fg_cm_attributeset AS cas ON ca.attributeset_id = cas.id '
            . "LEFT JOIN fg_cm_attributeset_i18n AS casi18n ON casi18n.id = cas.id AND (CASE WHEN (cas.is_system=1) THEN (casi18n.lang = '$defaultSystemLang') ELSE (casi18n.lang = '$defaultLang') END) "
            . "WHERE (cca.club_id = '$clubId' AND ((ca. is_system_field  = 1 OR ca.is_fairgate_field = 1) OR ($fieldVisibility) ) AND cca.is_active=1) "
            . 'ORDER BY sort ASC, cas.sort_order ASC, cas.id ASC, cca.sort_order ASC, cca.attribute_id ASC';

        $fieldsArray = $this->conn->fetchAll($contactFieldsSql);

        return $fieldsArray;
    }

    /**
     * This function is used to get the role categories and its details available in a club for
     * contact table element columns
     *
     * @param boolean $filterRole Flag for identifying filter role or manual role category
     *
     * @return array $result Result array
     */
    public function getRoleCategoriesForContactTableColumns($filterRole = false)
    {
        $categoryVisibilityArray = $this->categoryVisibilityCondition();
        $categoryVisibility = $categoryVisibilityArray[0];
        $clubIds = $categoryVisibilityArray[1];

        if ($filterRole) {
            $contactAssign = 'filter-driven';
        } else {
            $contactAssign = 'manual';
        }

        $sql = "SELECT C.id AS roleCatId, C.club_id AS clubId, C.title AS roleCatTitle, Ci18n.title_lang AS roleCatTitleLang, Ci18n.lang AS roleCatLang, "
            . "C.function_assign AS functionAssignType, C.is_fed_category AS isFedCategory, C.sort_order AS roleCatSortOrder, "
            . "R.id AS roleId, R.title AS roleTitle, Ri18n.title_lang AS roleTitleLang, Ri18n.lang AS roleLang, R.sort_order AS roleSortOrder "
            . "FROM fg_rm_category C "
            . "LEFT JOIN fg_rm_category_i18n Ci18n ON (Ci18n.id = C.id) "
            . "LEFT JOIN fg_rm_role R ON (R.category_id = C.id) "
            . "LEFT JOIN fg_rm_role_i18n Ri18n ON (Ri18n.id = R.id) "
            . "WHERE C.club_id IN ($clubIds) AND C.is_active = 1 AND Ci18n.is_active = 1 AND R.type = 'G' AND "
            . "R.is_active = 1 AND Ri18n.is_active = 1 AND C.contact_assign='$contactAssign' "
            . "$categoryVisibility"
            . "ORDER BY C.sort_order, R.sort_order";

        $result = $this->conn->fetchAll($sql);

        return $result;
    }

    /**
     * This function get the filter role category count
     *
     * @param boolean $filterRole Flag for identifying filter role or manual role category
     *
     * @return array $result Result array
     */
    public function getRoleCategoriesCountContactTableColumns($filterRole = false)
    {
        $categoryVisibilityArray = $this->categoryVisibilityCondition();
        $categoryVisibility = $categoryVisibilityArray[0];
        $clubIds = $categoryVisibilityArray[1];

        if ($filterRole) {
            $contactAssign = 'filter-driven';
        } else {
            $contactAssign = 'manual';
        }

        $sql = "SELECT COUNT(R.id) AS roleCatCount "
            . "FROM fg_rm_category C "
            . "LEFT JOIN fg_rm_role R ON (R.category_id = C.id) "
            . "WHERE C.club_id IN ($clubIds) AND C.is_active = 1 AND R.type = 'G' AND R.is_active = 1 AND C.contact_assign='$contactAssign' "
            . "$categoryVisibility "
            . "ORDER BY C.sort_order, R.sort_order";
        $result = $this->conn->fetchAll($sql);

        return $result;
    }

    /**
     * This function to create the category visibility condition
     *
     *
     * @return string $result
     */
    private function categoryVisibilityCondition()
    {
        $club = $this->container->get('club');
        $clubId = $club->get('id');
        $federationId = $club->get('federation_id');
        $subFederationId = $club->get('sub_federation_id');
        switch ($club->get('type')) {
            case 'federation':
            case 'standard_club':
                $clubIds = "$clubId";
                $categoryVisibility = "";
                break;

            case 'federation_club':
            case 'sub_federation':
                $clubIds = "$clubId, $federationId";
                $categoryVisibility = " AND (CASE WHEN (C.club_id = $federationId) THEN (C.is_fed_category = 1) ELSE 1 END) ";
                break;

            case 'sub_federation_club':
                $clubIds = "$clubId, $federationId, $subFederationId";
                $categoryVisibility = " AND (CASE WHEN (C.club_id = $federationId || C.club_id = $subFederationId) THEN (C.is_fed_category = 1) ELSE 1 END) ";
                break;
        }

        return array($categoryVisibility, $clubIds);
    }

    /**
     * This function is used ti get the team functions and workgroups available in a club for
     * contact table element columns
     *
     * @return array $result Result array
     */
    public function getTeamAndWorkgroupDetailsForContactTableColumns()
    {
        $club = $this->container->get('club');
        $clubTeamCategoryId = $club->get('club_team_id');
        $clubWorkgroupCategoryid = $club->get('club_workgroup_id');
        $clubDefaultLang = $club->get('default_lang');
        $sql = "SELECT R.id AS roleId, R.title AS roleTitle, Ri18n.title_lang AS roleTitleLang, Ri18n.lang AS roleLang, R.sort_order AS roleSortOrder, R.type AS roleType, R.is_executive_board AS isExeBoard, "
            . "F.id AS functionId, IF ((Fi18n.title_lang IS NOT NULL AND Fi18n.title_lang != ''), Fi18n.title_lang, F.title) AS functionTitle, F.sort_order AS functionSortOrder "
            . "FROM fg_rm_role R "
            . "LEFT JOIN fg_rm_role_i18n Ri18n ON (Ri18n.id = R.id) "
            . "LEFT JOIN fg_rm_function F ON (F.category_id = R.category_id) "
            . "LEFT JOIN fg_rm_function_i18n Fi18n ON (Fi18n.id = F.id AND Fi18n.lang = '$clubDefaultLang' AND F.is_active = 1) "
            . "WHERE R.category_id IN ($clubTeamCategoryId, $clubWorkgroupCategoryid) AND "
            . "R.is_active = 1 AND Ri18n.is_active = 1 "
            . "ORDER BY R.type, R.is_executive_board DESC, R.sort_order, F.sort_order";
        $result = $this->conn->fetchAll($sql);

        return $result;
    }

    /**
     * This function is used to update contact field value in master_system, master_club and master_federation tables.
     *
     * @param int       $attrId     Contact field attribute id
     * @param int       $contactId  Contact id
     * @param string    $value      Upadating value
     *
     * @return void
     */
    public function updateContactField($attrId, $contactId, $value)
    {
        $clubFields = $this->container->get('club')->get('clubFields');
        $fedFields = $this->container->get('club')->get('fedFields');
        $subFedFields = $this->container->get('club')->get('subFedFields');
        $systemFields = $this->container->get('club')->get('systemFields');
        $updateQry = '';
        if (array_key_exists($attrId, $clubFields)) {
            $clubId = $clubFields[$attrId]['club_id'];
            $updateQry = "UPDATE `master_club_{$clubId}` C SET `$attrId` = '$value' WHERE C.`contact_id` = $contactId";
        }

        if (array_key_exists($attrId, $fedFields)) {
            $clubId = $fedFields[$attrId]['club_id'];
            $updateQry = "UPDATE `master_federation_{$clubId}` C SET `$attrId` = '$value' WHERE C.`fed_contact_id` = $contactId";
        }

        if (array_key_exists($attrId, $subFedFields)) {
            $clubId = $subFedFields[$attrId]['club_id'];
            $updateQry = "UPDATE `master_federation_{$clubId}` C SET `$attrId` = '$value' WHERE C.`contact_id` = $contactId";
        }

        if (array_key_exists($attrId, $systemFields)) {
            $updateQry = "UPDATE `master_system` C SET `$attrId` = '$value' WHERE C.`fed_contact_id` = $contactId";
        }
        if ($updateQry != '') {
            $this->conn->executeQuery($updateQry);
        }
    }

    /**
     * function to get the contact details of particular contact's.
     *
     * @param \Clubadmin\ContactBundle\Controller\Request $request
     * @param obj                                         $conn    the     connection
     * @param String                                      $type    contact type
     *
     * @return array
     */
    public function tempContact($contactId, $type = 'contact')
    {
        if (!is_numeric($contactId)) {
            return false;
        }
        $club = $this->container->get('club');
        //$type = 'allVisible';
        $contactlistClass = new Contactlist($this->container, $contactId, $club, $type);
        $contactlistClass->setColumns('*');
        $contactlistClass->setFrom('*');
        $contactlistClass->setCondition();
        $sWhere = " fg_cm_contact.id=$contactId";
        $contactlistClass->addCondition($sWhere);
        $listquery = $contactlistClass->getResult();
        $fieldsArray = $this->conn->fetchAll($listquery);
        $fieldsArray[0]['is_sponsor'] = $fieldsArray[0]['sponsorFlag'];
        $fieldsArray[0]['Iscompany'] = $fieldsArray[0]['is_company'];

        return $fieldsArray;
    }

    /**
     * Function to get next birthdays bithday details
     *
     * @param object $container        Object of container
     * @param string $roleType         team or workgroup
     * @param int    $roleId           individual role id
     * @param int    $categoryId       team id or workgroup id
     * @param int    $clubId           logged club id
     *
     * @return array
     */
    public function getNextBirthDaysFromContactList($container, $roleType = '', $roleId = '', $categoryId)
    {
        $club = $container->get('club');
        $clubId = $club->get('id');
        $clubType = $club->get('type');
        if ($roleType != '' && $roleId == '') {
            return array();
        }
        $select = '';
        if ($roleId != '') {
            $select = "SELECT DISTINCT(fg_cnt.contact_id) FROM `fg_rm_role_contact` fg_cnt LEFT JOIN `fg_rm_category_role_function` fcat ON fcat.id = fg_cnt.fg_rm_crf_id LEFT JOIN fg_rm_role r ON fcat.role_id= r.id WHERE fcat.role_id = $roleId AND fg_cnt.assined_club_id = $clubId AND fcat.category_id = $categoryId AND r.is_active=1";
        }
        $dob = $container->getParameter('system_field_dob');
        $cName = $container->getParameter('system_field_companyname');
        $fName = $container->getParameter('system_field_firstname');
        $lName = $container->getParameter('system_field_lastname');

        $query = "SELECT "
            . "DATE_ADD(`" . $dob . "`, INTERVAL IF(DAYOFYEAR(`" . $dob . "`) >= DAYOFYEAR(CURDATE()), YEAR(CURDATE())-YEAR(`" . $dob . "`), YEAR(CURDATE())-YEAR(`" . $dob . "`)+1 ) YEAR ) AS `nextDate`, "
            . "GROUP_CONCAT( CONCAT( IF((fg_cm_contact.is_company = 1 AND fg_cm_contact.has_main_contact = 1),concat('`" . $cName . "` (', `" . $fName . "`, ' ', `" . $lName . "`, ')'), concat(`" . $fName . "`, ' ', `" . $lName . "`)) ,'~',YEAR(NOW()) - YEAR(`" . $dob . "`) - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(`" . $dob . "`, '00-%m-%d')) ,'~',fg_cm_contact.id,'~',fg_cm_contact.is_stealth_mode) separator ',') AS contacts "
            . "FROM fg_cm_contact "
            . "INNER JOIN master_system as ms on ms.fed_contact_id = fg_cm_contact.fed_contact_id "
            . "WHERE fg_cm_contact.is_permanent_delete = 0 AND "
            . "fg_cm_contact.is_deleted = 0 AND "
            . "fg_cm_contact.club_id = " . $clubId . " AND "
            . "(fg_cm_contact.main_club_id =" . $clubId . "  OR fg_cm_contact.fed_membership_cat_id IS NOT NULL) AND ";
         
        if($clubType == 'federation' || $clubType == 'sub_federation'){
            $query .= "(fg_cm_contact.is_fed_membership_confirmed = '0' OR "
                . "(fg_cm_contact.is_fed_membership_confirmed = '1' AND fg_cm_contact.old_fed_membership_id IS NOT NULL)) AND ";
        }
        
        $query .= "fg_cm_contact.is_draft=0  ";
        if ($roleId != '') {
            $query .= "AND fg_cm_contact.id IN ($select)  ";
        }
        $query .= "AND (`" . $dob . "` IS NOT NULL AND DATE(`" . $dob . "`) != '0000-00-00' AND DATE(`" . $dob . "`) <= CURDATE() ) AND "
            . "(fg_cm_contact.is_company = 0 OR "
            . "(fg_cm_contact.is_company = 1 AND fg_cm_contact.`comp_def_contact` IS NULL AND fg_cm_contact.`has_main_contact` = 1) ) ";

        if ($roleId == '') {
            $query .= " AND fg_cm_contact.is_stealth_mode != 1 ";
        }
        $query .= "GROUP BY nextDate "
            . "ORDER BY nextDate, contacts "
            . "ASC LIMIT 6";

        $nextBirthDays = $this->conn->fetchAll($query);
        //group Next Birthday In Desired Format
        $nextBirthDay = $this->groupNextBirthdayDesiredFormat($nextBirthDays, 'internal');

        return $nextBirthDay;
    }
    /*
     * Function to get next birthdays of active contacts in dashboard
     * @param $container             Object of container
     *
     * return array $nextBirthDay
     */

    public function getNextBirthDaysFromContactListBackend($container)
    {
        $club = $container->get('club');
        $clubId = $club->get('id');
        $clubType = $club->get('type');

        $dob = $container->getParameter('system_field_dob');
        $cName = $container->getParameter('system_field_companyname');
        $fName = $container->getParameter('system_field_firstname');
        $lName = $container->getParameter('system_field_lastname');

        $query = "SELECT "
            . "DATE_ADD(`" . $dob . "`, INTERVAL IF(DAYOFYEAR(`" . $dob . "`) >= DAYOFYEAR(CURDATE()), YEAR(CURDATE())-YEAR(`" . $dob . "`), YEAR(CURDATE())-YEAR(`" . $dob . "`)+1 ) YEAR ) AS `nextDate`, "
            . "GROUP_CONCAT( CONCAT( IF((fg_cm_contact.is_company = 1 AND fg_cm_contact.has_main_contact = 1),concat('`" . $cName . "` (', `" . $fName . "`, ' ', `" . $lName . "`, ')'), concat(`" . $fName . "`, ' ', `" . $lName . "`)) ,'~',YEAR(NOW()) - YEAR(`" . $dob . "`) - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(`" . $dob . "`, '00-%m-%d')) ,'~',fg_cm_contact.id) separator ',') AS contacts "
            . "FROM fg_cm_contact "
            . "INNER JOIN master_system as ms on ms.fed_contact_id = fg_cm_contact.fed_contact_id "
            . "WHERE "
            . "fg_cm_contact.is_permanent_delete = 0 AND "
            . "fg_cm_contact.is_deleted = 0 AND "
            . "fg_cm_contact.club_id = " . $clubId . " AND "
            . "(fg_cm_contact.main_club_id =" . $clubId . "  OR fg_cm_contact.fed_membership_cat_id IS NOT NULL) AND ";
        
        if($clubType == 'federation' || $clubType == 'sub_federation'){
            $query .= "(fg_cm_contact.is_fed_membership_confirmed = '0' OR "
                . "(fg_cm_contact.is_fed_membership_confirmed = '1' AND fg_cm_contact.old_fed_membership_id IS NOT NULL)) AND ";
        }
        
        $query .= "fg_cm_contact.is_draft=0 AND "
            . "(`" . $dob . "` IS NOT NULL AND DATE(`" . $dob . "`) != '0000-00-00' AND DATE(`" . $dob . "`) <= CURDATE() ) AND "
            . "(fg_cm_contact.is_company = 0 OR "
            . "(fg_cm_contact.is_company = 1 AND fg_cm_contact.`comp_def_contact` IS NULL AND fg_cm_contact.`has_main_contact` = 1) ) "
            . "GROUP BY nextDate "
            . "ORDER BY nextDate, contacts "
            . "ASC LIMIT 7";


        $nextBirthDays = $this->conn->fetchAll($query);

        //group Next Birthday In Desired Format
        $nextBirthDay = $this->groupNextBirthdayDesiredFormat($nextBirthDays, 'backend');

        return $nextBirthDay;
    }

    /**
     * group Next Birthday In Desired Format
     *
     * @param array $nextBirthDays  next BirthDay list
     *
     * @return array
     */
    private function groupNextBirthdayDesiredFormat($nextBirthDays, $from)
    {
        $clubObj = $this->container->get('club');
        $userRights = $clubObj->get('allowedRights');
        $contactRights = (in_array('contact', $userRights) || in_array('readonly_contact', $userRights)) ? 1 : 0;
        for ($i = 0; $i < count($nextBirthDays); $i++) {
            $nextBirthDays[$i]['contactRights'] = $contactRights;
            $contactsIdsArray = explode(",", $nextBirthDays[$i]['contacts']);
            if ($nextBirthDays[$i]['nextDate'] === date('Y-m-d')) {
                $contactsArray = $this->getArrayOfContactDetails($contactsIdsArray, 'today', $from);
                $nextBirthDays[$i]['nextBirthDay'] = $this->container->get('translator')->trans('DASHBOARD_TODAY');
            } else {
                $contactsArray = $this->getArrayOfContactDetails($contactsIdsArray, 'nottoday', $from);
                $nextBirthDays[$i]['nextBirthDay'] = $clubObj->formatDate($nextBirthDays[$i]['nextDate'], 'date', 'Y-m-d');
            }
            $nextBirthDays[$i]['contacts'] = $contactsArray;
            $nextBirthDays[$i]['contactsNumber'] = count($contactsArray);
        }

        return $nextBirthDays;
    }
    /*
     * Function to return array of name, contact-overview url, age of each contacts
     * param $contactsIdsArray array of string for each contact
     * that string is in the format contactname~age~contactId
     * $return  array
     */

    private function getArrayOfContactDetails($contactsIdsArray, $when, $from)
    {
        $contactsArray = array();
        $c = 0;
        foreach ($contactsIdsArray as $contact) {
            $c++;
            $classname = ($c <= 5) ? "" : "fg-bithday-contact hide";
            $contactDetails = explode("~", $contact);
            if ($from == 'backend') {
                $path = $this->container->get('router')->generate('render_contact_overview', array('offset' => '0', 'contact' => $contactDetails[2]));
            } else {
                $path = $this->container->get('router')->generate('internal_community_profile', array('contactId' => $contactDetails[2]));
            }
            array_push($contactsArray, array("name" => $contactDetails[0], "path" => $path, "age" => $when == 'nottoday' ? $contactDetails[1] + 1 : $contactDetails[1], 'classname' => $classname, 'isStealthMode' => $contactDetails[3]));
        }

        return $contactsArray;
    }

    /**
     * Function to get contact name in log controller
     *
     * @param int $contactId Contact Id
     *
     * @return array of contact details
     */
    public function contactDetails($contactId)
    {
        $club = $this->container->get('club');
        $type = 'noCondition';
        $contactlistClass = new Contactlist($this->container, '', $club, $type);
        $contactlistClass->setColumns(array('contactname', 'contactName', 'is_company', 'fedMembershipId', 'clubMembershipId', 'fg_cm_contact.club_id', 'fg_cm_contact.fed_contact_id', 'fg_cm_contact.subfed_contact_id', 'fedmembershipApprove', 'fg_cm_contact.old_fed_membership_id', 'fg_cm_contact.is_permanent_delete'));
        $contactlistClass->setFrom('*');
        $contactlistClass->setCondition();
        $sWhere = " fg_cm_contact.id=$contactId";
        $contactlistClass->addCondition($sWhere);
        $listquery = $contactlistClass->getResult();
        $fieldsArray = $this->conn->fetchAll($listquery);

        return $fieldsArray[0];
    }

    /**
     * Function to get the assignment log entries of a contact
     *
     * @param array $clubDetails Club details
     * @param int   $contactId   Contact Id
     *
     * @return array $result Array of log entries
     */
    public function getAssignmentLogEntries($clubDetails, $contactId, $fed_contact_id, $subfed_contact_id)
    {
        $dateFormat = FgSettings::getMysqlDateTimeFormat();
        $clubId = $clubDetails['clubId'];
        $clubType = $clubDetails['clubType'];
        $clubHeirarchy = implode(',', $clubDetails['clubHeirarchy']);
        $defaultLang = $clubDetails['clubDefaultLang'];
        switch ($clubType) {
            case 'standard_club':
            case 'federation':
                $where = "c.category_club_id=:clubId ";
                $id = $contactId;
                break;
            case 'federation_club':
            case 'sub_federation':
                $where = "((c.category_club_id IN ($clubHeirarchy) AND c.role_type='fed') OR (c.category_club_id=:clubId))";
                $id = $fed_contact_id . "," . $contactId;
                break;
            case 'sub_federation_club':
                $where = "((c.category_club_id IN ($clubHeirarchy) AND c.role_type='fed') OR (c.category_club_id=:clubId))";
                $id = $fed_contact_id . "," . $subfed_contact_id . "," . $contactId;
                break;
        }
        $clubTitleQuery = "SELECT COALESCE(NULLIF(Ci18N.title_lang,''),FC.title) AS title FROM fg_cm_contact CT LEFT JOIN fg_club FC ON CT.main_club_id = FC.id LEFT JOIN fg_club_i18n Ci18N ON Ci18N.id = FC.id AND Ci18N.lang = '$defaultLang' WHERE CT.id = c.changed_by";
        $sql = "SELECT c.id,c.contact_id,c.category_title AS columnVal2,'assignments' AS kind,c.value_before,c.value_after,c.changed_by, c.date AS dateOriginal,date_format( c.date,'" . $dateFormat . "') AS date,
                IF((checkActiveContact(c.changed_by, $clubId) IS NULL && c.changed_by != 1), CONCAT(contactName(c.changed_by),' (',($clubTitleQuery),')'),contactName(c.changed_by) )as editedBy,
                 (CASE WHEN ((c.value_before IS NOT NULL AND c.value_before != '' AND c.value_before != '-') AND (c.value_after IS NULL OR c.value_after = '' OR c.value_after = '-')) THEN 'removed'
                       WHEN ((c.value_before IS NULL OR c.value_before = '' OR c.value_before = '-') AND (c.value_after IS NOT NULL AND c.value_after != '' AND c.value_after != '-')) THEN 'added'
                       ELSE 'none'
                 END) AS status,checkActiveContact(c.changed_by, $clubId) as activeContact,c.changed_by,
                 (IF((c.value_after='' OR c.value_after IS NULL OR c.value_after='-'),c.value_before,c.value_after)) AS columnVal3
                 FROM fg_cm_log_assignment c LEFT JOIN fg_rm_role_log r ON r.id=c.role_id
                 WHERE c.contact_id IN ($id) AND " . $where;
        $result = $this->conn->fetchAll($sql, array('clubId' => $clubId));

        return $result;
    }

    /**
     * Function to get the connection log entries of a contact
     *
     * @param array $clubDetails Club details
     * @param int   $contactId   Contact Id
     *
     * @return array $result Array of log entries
     */
    public function getConnectionLogEntries($clubDetails, $contactId)
    {
        $dateFormat = FgSettings::getMysqlDateTimeFormat();
        $clubId = $clubDetails['clubId'];
        $defaultLang = $clubDetails['clubDefaultLang'];
        $clubTitleQuery = "SELECT COALESCE(NULLIF(Ci18N.title_lang,''),FC.title) AS title FROM fg_cm_contact CT LEFT JOIN fg_club FC ON CT.main_club_id = FC.id LEFT JOIN fg_club_i18n Ci18N ON Ci18N.id = FC.id AND Ci18N.lang = '$defaultLang' WHERE CT.id = c.changed_by";
        $sql = "SELECT c.id,c.contact_id, c.connection_type AS connectionType, CONCAT('(', c.relation, ')') AS columnVal2,'connections' AS kind,c.value_before,c.value_after,c.changed_by,c.date AS dateOriginal,date_format( c.date,'" . $dateFormat . "') AS date,
                 IF((checkActiveContact(c.changed_by, $clubId) IS NULL && c.changed_by != 1), CONCAT(contactName(c.changed_by),' (',($clubTitleQuery),')'),contactName(c.changed_by) )as editedBy,
                 (CASE WHEN ((c.value_before IS NOT NULL AND c.value_before != '' AND c.value_before != '-') AND (c.value_after IS NULL OR c.value_after = '' OR c.value_after = '-')) THEN 'removed'
                       WHEN ((c.value_before IS NULL OR c.value_before = '' OR c.value_before = '-') AND (c.value_after IS NOT NULL AND c.value_after != '' AND c.value_after != '-')) THEN 'added'
                       ELSE 'none'
                 END) AS status,checkActiveContact(c.changed_by, $clubId) as activeContact,c.changed_by,
                 contactName(c.linked_contact_id) AS columnVal3
                 FROM fg_cm_log_connection c
                 WHERE (c.contact_id= :contactId AND (c.assigned_club_id= :clubId OR c.assigned_club_id IS NULL)) OR (c.contact_id = (SELECT fed_contact_id FROM fg_cm_contact WHERE id=:contactId) AND c.linked_contact_id!=0 AND c.`type`='global')";
        $result = $this->conn->fetchAll($sql, array('contactId' => $contactId, 'clubId' => $clubId));

        return $result;
    }

    /**
     * Function to get the  log entries of communication in cotactOverview page
     *
     * @param int   $contact     Contact id
     * @param Array $clubDetails Club details
     *
     * @return array $result Array of log entries
     */
    public function getCommunicationLogEntries($contact, $clubDetails)
    {

        $dateFormat = FgSettings::getMysqlDateTimeFormat();
        $clubId = $clubDetails['clubId'];
        $defaultLang = $clubDetails['clubDefaultLang'];
        $clubTitleQuery = "SELECT COALESCE(NULLIF(Ci18N.title_lang,''),FC.title) AS title FROM fg_cm_contact CT LEFT JOIN fg_club FC ON CT.main_club_id = FC.id LEFT JOIN fg_club_i18n Ci18N ON Ci18N.id = FC.id AND Ci18N.lang = '$defaultLang' WHERE CT.id = cl.changed_by";
        $query = "SELECT cl.id,cl.contact_id,cl.kind,cl.field,cl.date AS dateOriginal,date_format(cl.date,'" . $dateFormat . "') AS date,cl.changed_by as changedBy,cn.subject as sending,cn.newsletter_type as type,cn.template_id as templateId,cn.id as newsletterId,checkActiveContact(cl.changed_by, $clubId) as activeContact,cl.changed_by,
                   IF((checkActiveContact(cl.changed_by, $clubId) IS NULL && cl.changed_by != 1), CONCAT(contactName(cl.changed_by),' (',($clubTitleQuery),')'),contactName(cl.changed_by) )as editedBy
                   FROM fg_cm_change_log cl
                   LEFT JOIN fg_cm_contact c ON cl.contact_id = c.id
                   LEFT JOIN fg_cn_newsletter cn ON cl.newsletter_id=cn.id
                   WHERE cl.contact_id= :contactId AND cl.kind='communication' AND cl.club_id=:clubId";
        $result = $this->conn->fetchAll($query, array('contactId' => $contact, 'clubId' => $clubId));

        return $result;
    }

    /**
     * Function to get the system log entries of a contact
     *
     * @param array $clubDetails Club details
     * @param int   $contactId   Contact Id
     *
     * @return array $result Array of log entries
     */
    public function getSystemLogEntries($clubDetails, $contactId, $fedcontactId, $club)
    {
        $bookedModuleDetails = $club->get('bookedModulesDet');
        $condition = (in_array('communication', $bookedModuleDetails) ) ? "" : " AND c.field != 'newsletter' ";
        $condition1 = (in_array('frontend1', $bookedModuleDetails) ) ? "" : " AND   c.field !='stealth mode' AND c.field !='intranet access'";

        $dateFormat = FgSettings::getMysqlDateTimeFormat();
        $clubId = $clubDetails['clubId'];
        $defaultLang = $clubDetails['clubDefaultLang'];
        $clubTitleQuery = "SELECT COALESCE(NULLIF(Ci18N.title_lang,''),FC.title) AS title FROM fg_cm_contact CT LEFT JOIN fg_club FC ON CT.main_club_id = FC.id LEFT JOIN fg_club_i18n Ci18N ON Ci18N.id = FC.id AND Ci18N.lang = '$defaultLang' WHERE CT.id = c.changed_by";
        $sql = "SELECT c.id,c.contact_id,c.kind AS columnVal2,c.kind,c.field,c.value_before,c.value_after,c.changed_by,c.date AS dateOriginal,date_format( c.date,'" . $dateFormat . "') AS date,
                IF((checkActiveContact(c.changed_by, $clubId) IS NULL && c.changed_by != 1), CONCAT(contactName(c.changed_by),' (',($clubTitleQuery),')') ,contactName(c.changed_by))as editedBy,
                  if(c.kind = 'system' AND c.club_id =:clubId,
                    (CASE WHEN ((c.value_before IS NOT NULL AND c.value_before != '') AND (c.value_after IS NOT NULL AND c.value_after != '')) THEN 'changed'
                          ELSE 'none'
                    END)

                    ,(CASE WHEN ((c.value_before IS NOT NULL AND c.value_before != '' AND c.value_before != '-') AND (c.value_after IS NULL OR c.value_after = '' OR c.value_after = '-')) THEN 'removed'
                          WHEN ((c.value_before IS NULL OR c.value_before = '' OR c.value_before = '-') AND (c.value_after IS NOT NULL AND c.value_after != '' AND c.value_after != '-')) THEN 'added'
                          ELSE 'none'
                    END)
                 ) AS status,checkActiveContact(c.changed_by, $clubId) as activeContact,c.changed_by,
                 (IF( (c.kind = 'user rights' OR c.kind = 'contact type' ),IF((c.value_after='' OR c.value_after IS NULL OR c.value_after= '-'),c.value_before,c.value_after), c.value_after)) AS columnVal3
                 FROM fg_cm_change_log c
                 WHERE ((c.contact_id= :contactId) OR ((c.kind = 'contact status' OR c.kind = 'contact type' ) AND (c.contact_id= :fedcontactId))) AND (c.is_confirmed = 1 OR c.is_confirmed IS NULL) AND (IF(c.kind IN ('user rights','system'), c.club_id=:clubId, '1'))
                 AND ( c.kind IN ('contact status','contact type', 'login', 'password', 'user rights','system')
                 OR (c.kind = 'contact type' AND (c.value_before = 'Sponsor' OR c.value_after = 'Sponsor' ) )  ) $condition $condition1";
        $result = $this->conn->fetchAll($sql, array('contactId' => $contactId, 'clubId' => $clubId, 'fedcontactId' => $fedcontactId));

        return $result;
    }

    /**
     * function to delete the membership log
     *
     * @param int  $contactId    Contact id
     * @param int  $clubId       The club id
     * @param int  $membershipId The membership id
     * @param date $fromDate     Joining date
     * @param date $toDate       Leaving date
     */
    public function deleteMemebershipLog($contactId, $clubId, $membershipId, $fromDate = "", $toDate = "")
    {
        //to check whether the deleted membership id should be of the club from which it gets delete
        $checkCurrentClubsMemId = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->checkClubMembership($clubId, $membershipId);
        //if ($checkCurrentClubsMemId) {
        $membershipHistoryarrs = $this->em->getRepository('CommonUtilityBundle:FgCmMembershipHistory')->getMembershipLogs('delete', $membershipId, $contactId, $fromDate, $toDate);
        if (isset($membershipHistoryarrs)) {
            if ($membershipHistoryarrs['joining_date'] != '') {
                $joiningDate = $membershipHistoryarrs['joining_date'];
                $sql = "DELETE FROM fg_cm_membership_log  WHERE contact_id =:contactId  and membership_id =:membershipId and date = '$joiningDate'";
                $this->conn->executeQuery($sql, array(":contactId" => $contactId, ":membershipId" => $membershipId));
            }
            if ($membershipHistoryarrs['leaving_date'] != '') {
                $leavingDate = $membershipHistoryarrs['leaving_date'];
                $sql = "DELETE FROM fg_cm_membership_log  WHERE contact_id =:contactId  and membership_id =:membershipId and date = '$leavingDate'";
                $this->conn->executeQuery($sql, array(":contactId" => $contactId, ":membershipId" => $membershipId));
            }
            $joiningDate = $membershipHistoryarrs['joining_date'];
            $leavingDate = $membershipHistoryarrs['leaving_date'];
            $sql = "DELETE FROM fg_cm_membership_history  WHERE contact_id =$contactId  and membership_id =$membershipId and joining_date = '$joiningDate' and leaving_date = '$leavingDate'";
            $this->conn->executeQuery($sql);
        }
    }

    /**
     * Function to get the contact field log entries of a contact
     *
     * @param array $clubDetails Club details
     * @param int   $contactId   Contact Id
     *
     * @return array $result Array of log entries
     */
    public function getContactFieldLogEntries($clubDetails, $contactId, $container, $fed_contact_id, $subfed_contact_id)
    {
        $picContactFields = array($container->getParameter('system_field_companylogo'), $container->getParameter('system_field_team_picture'), $container->getParameter('system_field_communitypicture'));
        $dateFormat1 = FgSettings::getMysqlDateTimeFormat();
        $dateFormat2 = FgSettings::getMysqlDateFormat();
        $clubId = $clubDetails['clubId'];
        $clubType = $clubDetails['clubType'];
        $defaultLang = $clubDetails['clubDefaultLang'];


        $clubHeirarchy = implode(',', $clubDetails['clubHeirarchy']);
        switch ($clubType) {
            case 'standard_club':
            case 'federation':
                $fieldVisibility = "a.club_id=:clubId ";
                $id = $contactId;
                break;
            case 'sub_federation_club':
                $fieldVisibility = "(a.club_id IN ($clubHeirarchy) AND a.availability_club IN ('changable', 'visible')) OR (a.club_id=:clubId)";
                $id = $fed_contact_id . "," . $subfed_contact_id . "," . $contactId;
                break;
            case 'sub_federation':
                $fieldVisibility = "(a.club_id IN ($clubHeirarchy) AND a.availability_sub_fed IN ('changable', 'visible')) OR (a.club_id=:clubId)";
                $id = $fed_contact_id . "," . $contactId;
                break;
            case 'federation_club':
                $fieldVisibility = "(a.club_id IN ($clubHeirarchy) AND a.availability_club IN ('changable', 'visible')) OR (a.club_id=:clubId)";
                $id = $fed_contact_id . "," . $contactId;
                break;
        }
        $ClubLangCount = count($container->get('club')->get('club_languages'));
        $systemFieldCorressLang = $container->getParameter('system_field_corress_lang');
        //if only one club language is there, no need to show the log of correspondence language
        $condition = ($ClubLangCount > 1) ? "" : " AND c.attribute_id != '$systemFieldCorressLang'";
        $clubTitleQuery = "SELECT COALESCE(NULLIF(Ci18N.title_lang,''),FC.title) AS title FROM fg_cm_contact CT LEFT JOIN fg_club FC ON CT.main_club_id = FC.id LEFT JOIN fg_club_i18n Ci18N ON Ci18N.id = FC.id AND Ci18N.lang = '$defaultLang' WHERE CT.id = c.changed_by";
        $sql = "SELECT  c.id,c.contact_id,c.kind,c.field,c.value_before,c.value_after,c.changed_by,a.input_type,c.attribute_id,
                        c.date AS dateOriginal,date_format( c.date,'" . $dateFormat1 . "') AS date,
                        IF((checkActiveContact(c.changed_by, $clubId) is null && c.changed_by != 1), CONCAT(contactName(c.changed_by),' (',($clubTitleQuery),')') , contactName(c.changed_by) )as editedBy,
                        (CASE WHEN ((c.value_before IS NOT NULL AND c.value_before != '') AND (c.value_after IS NOT NULL AND c.value_after != '')) THEN 'changed'
                              WHEN ((c.value_before IS NOT NULL AND c.value_before != '') AND (c.value_after IS NULL OR c.value_after = '')) THEN 'removed'
                              WHEN ((c.value_before IS NULL OR c.value_before = '') AND (c.value_after IS NOT NULL AND c.value_after != '')) THEN 'added'
                              ELSE 'none'
                        END) AS status,checkActiveContact(c.changed_by, $clubId) as activeContact,c.changed_by,
                        IF(ai18n.fieldname_lang IS NULL OR ai18n.fieldname_lang='', a.fieldname, ai18n.fieldname_lang) AS contact_field_title,
                        IF((a.input_type='date' AND c.value_before IS NOT NULL AND c.value_before != '' AND c.value_before != '-'), date_format(c.value_before,'" . $dateFormat2 . "'),c.value_before) AS value_before,
                        IF((a.input_type='date' AND c.value_after IS NOT NULL AND c.value_after != '' AND c.value_after != '-'), date_format(c.value_after,'" . $dateFormat2 . "'),c.value_after) AS value_after
                 FROM fg_cm_change_log c
                 LEFT JOIN fg_cm_attribute a ON c.attribute_id = a.id
                 LEFT JOIN fg_cm_club_attribute fca ON fca.attribute_id=a.id AND fca.club_id=:clubId
                 LEFT JOIN fg_cm_attribute_i18n ai18n ON ai18n.id = a.id AND ai18n.lang = '" . $defaultLang . "'
                 WHERE c.contact_id IN ($id) AND c.kind='data' AND
                       (c.is_confirmed = 1 OR c.is_confirmed IS NULL) AND
                       (((a.is_system_field = 1 OR a.is_fairgate_field = 1) OR (" . $fieldVisibility . ")) OR
                         (a.id IN (" . implode(',', $picContactFields) . "))
                       )
                       AND (a.is_crucial_system_field=1 OR fca.is_active=1) $condition";

        $result = $this->conn->fetchAll($sql, array('clubId' => $clubId));

        return $result;
    }

    /**
     * Function to get the notes log entries of a contact
     *
     * @param array $clubDetails Club details
     * @param int   $contactId   Contact Id
     *
     * @return array $result Array of log entries
     */
    public function getNotesLogEntries($clubDetails, $contactId)
    {
        $dateFormat = FgSettings::getMysqlDateTimeFormat();
        $clubId = $clubDetails['clubId'];
        $defaultLang = $clubDetails['clubDefaultLang'];
        $clubTitleQuery = "SELECT COALESCE(NULLIF(Ci18N.title_lang,''),FC.title) AS title FROM fg_cm_contact CT LEFT JOIN fg_club FC ON CT.main_club_id = FC.id LEFT JOIN fg_club_i18n Ci18N ON Ci18N.id = FC.id AND Ci18N.lang = '$defaultLang' WHERE CT.id = c.changed_by";
        $sql = "SELECT c.id,c.value_before,c.value_after,c.changed_by,c.date AS dateOriginal,date_format( c.date,'" . $dateFormat . "') AS date,
                IF((checkActiveContact(c.changed_by, $clubId) IS NULL && c.changed_by != 1), CONCAT(contactName(c.changed_by),' (',($clubTitleQuery),')'),contactName(c.changed_by) )as editedBy,
                 (CASE WHEN ((c.value_before IS NOT NULL AND c.value_before != '' AND c.value_before != '-') AND (c.value_after IS NULL OR c.value_after = '' OR c.value_after = '-')) THEN 'removed'
                       WHEN ((c.value_before IS NULL OR c.value_before = '' OR c.value_before = '-') AND (c.value_after IS NOT NULL AND c.value_after != '' AND c.value_after != '-')) THEN 'added'
                       WHEN ((c.value_before IS NOT NULL AND c.value_before != '' AND c.value_before != '-') AND (c.value_after IS NOT NULL AND c.value_after != '' AND c.value_after != '-')) THEN 'changed'
                       ELSE 'none'
                 END) AS status,checkActiveContact(c.changed_by, $clubId) as activeContact,c.changed_by,
                 (IF((c.value_before IS NOT NULL AND c.value_before != '' AND c.value_before != '-'), c.value_before, '')) AS valueBefore,
                 (IF((c.value_after IS NOT NULL AND c.value_after != '' AND c.value_after != '-'), c.value_after, '')) AS valueAfter
                 FROM fg_club_log_notes c
                 WHERE c.note_contact_id=:contactId AND c.type='contact' ";
        $result = $this->conn->fetchAll($sql, array('contactId' => $contactId, 'clubId' => $clubId));

        return $result;
    }

    /**
     * Function to get the membership log entries of a contact
     *
     * @param array $clubDetails Club details
     * @param int   $contactId   Contact Id
     *
     * @return array $result Array of log entries
     */
    public function getMembershipLogEntries($clubDetails, $contactId, $federationId)
    {
        $dateFormat = FgSettings::getMysqlDateFormat();
        $clubId = $clubDetails['clubId'];
        $defaultLang = $clubDetails['clubDefaultLang'];
        $where = "mh.contact_id=:contactId AND IF((m.club_id =:clubId),1,(m.club_id = $federationId))";
        $clubTitleQuery = "SELECT COALESCE(NULLIF(Ci18N.title_lang,''),FC.title) AS title FROM fg_cm_contact CT LEFT JOIN fg_club FC ON CT.main_club_id = FC.id LEFT JOIN fg_club_i18n Ci18N ON Ci18N.id = FC.id AND Ci18N.lang = '$defaultLang' WHERE CT.id = mh.changed_by";
        $sql = "SELECT mh.id,mh.joining_date AS dateFromOriginal, mh.leaving_date AS dateToOriginal, m.title, mh.changed_by, date_format(mh.joining_date,'" . $dateFormat . "') AS MembershipFrom, date_format(mh.leaving_date,'" . $dateFormat . "') AS MembershipTo,
                IF((checkActiveContact(mh.changed_by, $clubId) is null && mh.changed_by != 1), CONCAT(contactName(mh.changed_by),' (',($clubTitleQuery),')') ,contactName(mh.changed_by) )as editedBy,
                 IF(mi18n.title_lang IS NULL OR mi18n.title_lang='', m.title, mi18n.title_lang) AS Membership, m.id AS membershipId,
                 IF(mh.leaving_date IS NULL,1,0) AS isActiveMembership,mh.membership_id AS membershipId,checkActiveContact(mh.changed_by, $clubId) as activeContact,mh.changed_by
                 FROM fg_cm_membership_history mh LEFT JOIN fg_cm_membership m ON mh.membership_id = m.id LEFT JOIN fg_cm_membership_i18n mi18n ON mi18n.id = m.id AND mi18n.lang = '" . $defaultLang . "'
                 LEFT JOIN fg_cm_contact c ON mh.contact_id=c.id
                 WHERE " . $where . " ORDER BY mh.joining_date DESC";
        $result = $this->conn->fetchAll($sql, array('contactId' => $contactId, 'clubId' => $clubId));

        return $result;
    }

    /**
     * Function to get the membership log entries of a contact
     *
     *
     * @param int   $contactId   Contact Id
     * @param array $defaultLang default lang
     * @param array $federationId federation id
     * @param int   $clubId       current club id
     *
     * @return array $result Array of log entries
     */
    public function getFedMembershipLogEntries($contactId, $defaultLang, $federationId, $clubId)
    {
        $dateFormat = FgSettings::getMysqlDateFormat();
        $where = "mh.contact_id=:contactId AND (m.club_id = $federationId)";
        $clubTitleQuery = "SELECT COALESCE(NULLIF(Ci18N.title_lang,''),FC.title) AS title FROM fg_cm_contact CT LEFT JOIN fg_club FC ON CT.main_club_id = FC.id LEFT JOIN fg_club_i18n Ci18N ON Ci18N.id = FC.id AND Ci18N.lang = '$defaultLang' WHERE CT.id = mh.changed_by";
        $sql = "SELECT mh.id,mh.joining_date AS dateFromOriginal, mh.leaving_date AS dateToOriginal, m.title, mh.changed_by,
                date_format(mh.joining_date,'" . $dateFormat . "') AS MembershipFrom,
                date_format(mh.leaving_date,'" . $dateFormat . "') AS MembershipTo,checkActiveContact(mh.changed_by, $clubId) as activeContact,mh.changed_by,
                IF((checkActiveContact(mh.changed_by, $clubId) is null && mh.changed_by != 1) , CONCAT(contactName(mh.changed_by),' (',($clubTitleQuery),')') ,contactName(mh.changed_by) )as editedBy,
                 IF(mi18n.title_lang IS NULL OR mi18n.title_lang='', m.title, mi18n.title_lang) AS Membership, m.id AS membershipId,
                 IF(mh.leaving_date IS NULL,1,0) AS isActiveMembership,mh.membership_id AS membershipId
                 FROM fg_cm_membership_history mh LEFT JOIN fg_cm_membership m ON mh.membership_id = m.id LEFT JOIN fg_cm_membership_i18n mi18n ON mi18n.id = m.id AND mi18n.lang = '" . $defaultLang . "'
                 LEFT JOIN fg_cm_contact c ON mh.contact_id=c.id
                 WHERE " . $where . " ORDER BY mh.joining_date DESC";

        $result = $this->conn->fetchAll($sql, array('contactId' => $contactId));

        return $result;
    }
    
    /**
 * 
 * @param string $contactIds comma separated contact ids
 * @return array
 */
    public function getContactIdDetails($contactIds)
    {
        $sql = "SELECT c.id,c.fed_contact_id,c.subfed_contact_id FROM fg_cm_contact c  WHERE c.id  IN($contactIds)";
        $details = $this->conn->fetchAll($sql);
        
        return $details;
    }
    
}
