<?php

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Util\FgUtility;
use Doctrine\DBAL\Cache\QueryCacheProfile;
use Common\UtilityBundle\Util\FgSettings;

/**
 * FgCmAttributes Repository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class FgCmAttributeRepository extends EntityRepository {

    public $systmPersonaBothlFields;

    /**
     * Function to get properties.
     *
     * @param int $attributeId
     *
     * @return array
     */
    public function getProperties($attributeId) {
        $qb = $this->createQueryBuilder('ca')
                ->select('cas.title as categoryName', 'cas.id as categoryId', 'ca.fieldname', 'ca.fieldnameShort', 'cai18n.fieldnameLang', 'cai18n.fieldnameShortLang', 'cai18n.lang', 'ca.inputType', 'ca.fieldtype', 'ca.isSystemField', 'ca.isFairgateField', 'ca.predefinedValue', 'ca.isPersonal', 'ca.isCompany', 'ca.isSingleEdit', 'ca.addresType', 'c.id as clubId')
                ->leftJoin('ca.attributeset', 'cas')
                ->leftJoin('CommonUtilityBundle:FgCmAttributeI18n', 'cai18n', 'WITH', 'cai18n.id=ca.id')
                ->leftJoin('ca.club', 'c')
                ->where('ca.id=:attributeId')
                ->setParameter('attributeId', $attributeId);

        $result = $qb->getQuery()->getResult();

        return $result;
    }

    /**
     * Function to get Federation field properties.
     *
     * @param int $attributeId
     * @param int $clubId
     * @return array
     */
    public function getFedClubPermissionDetails($attributeId, $clubId) {
        $qb = $this->createQueryBuilder('ca')
                ->select('ca.isSystemField, ca.availabilitySubFed', 'ca.availabilityClub', 'cca.isRequiredFedmemberSubfed', 'cca.isRequiredFedmemberClub')
                ->leftJoin('CommonUtilityBundle:FgCmClubAttribute', 'cca', 'WITH', 'cca.attribute=ca.id')
                ->where('ca.id=:attributeId')
                ->andWhere('cca.club=:clubId')
                ->setParameters(array('attributeId' => $attributeId, 'clubId' => $clubId));
        $result = $qb->getQuery()->getResult();

        return $result[0];
    }

    /**
     * Function to insert temporary Tables and call stored procedure to save contact fields.
     *
     * @param array  $tableValues
     * @param int    $random
     * @param int    $clubId
     * @param string $clubType
     * @param int    $correspondanceCategory
     * @param int    $invoiceCategory
     * @param string $clubDefaultLang Club default
     */
    public function insertIntoTempTable($tableValues, $random, $clubId, $clubType, $correspondanceCategory, $invoiceCategory, $clubDefaultLang) {
        $query = array();
        foreach ($tableValues as $table => $values) {
            if (count($values) > 0) {
                $query[] = "INSERT INTO $table VALUES " . implode(',', $values);
            }
        }
        if (count($query) > 0) {
            $conn = $this->getEntityManager()->getConnection();
            $conn->executeQuery(implode(';', $query));
            //Call stored procedure to save contact fields
            $conn->executeQuery("call handleAttributes('$random', $clubId, '$clubType', '$correspondanceCategory', '$invoiceCategory', '$clubDefaultLang');");
        }

    }

    /**
     * Update Personal category fields of company contact with selected linked conatct.
     *
     * @param Object  $conn                      Connection
     * @param Integer $mainContactId             Main contact id
     * @param Integer $clubId                    Club id
     * @param string  $mainContactType           Main contact type
     * @param string  $previousMainContactType   Previous main contact type
     * @param Integer $previousMainContactId     Previous main contact id
     * @param String  $now                       Current time
     * @param Integer $currentContactId          Contact id
     */
    public function updateMainContactDetails($container, $mainContactId, $clubId, $mainContactType, $previousMainContactType, $previousMainContactId, $now, $currentContactId) {
        // Update personal fields for company if main contsct is existing
        $em = $container->get('doctrine')->getManager();
        $conn = $em->getConnection();
        $systmPersonaBothlFields = $container->getParameter('system_personal_both');// echo 'updateMainCon'.$mainContactId.'--'.$previousMainContactId;
        if ($mainContactType == 'existing' && $mainContactId != '' && $mainContactId != $previousMainContactId) {
            $duplicateSet = array();
            $result = $conn->fetchAll("SELECT m.id,C.fed_contact_id FROM fg_cm_contact C LEFT JOIN master_system m ON m.fed_contact_id = C.fed_contact_id WHERE C.id=@contactId limit 1");
            foreach ($systmPersonaBothlFields as $field) {
                $duplicateSet[] = "`$field` = VALUES(`$field`)";
            }
            if($result[0]['id']){
                $insertFields = implode('`,`', $systmPersonaBothlFields);
                $insertQuery = "INSERT INTO master_system(`id`, `fed_contact_id`,`$insertFields`) SELECT {$result[0]['id']}, {$result[0]['fed_contact_id']}, `$insertFields` FROM master_system WHERE fed_contact_id =(SELECT fed_contact_id FROM fg_cm_contact WHERE id='$mainContactId' limit 1) " . ' ON DUPLICATE KEY UPDATE ' . implode(',', $duplicateSet);
                $conn->executeQuery($insertQuery);
            }
        }
        //If the contact was comapny contact with manual main contact, add log entries for removed persoanl fields
        if ($previousMainContactType=='manual' && ($mainContactType=='existing')) {
            $insertLogQuery = "INSERT INTO fg_cm_change_log(attribute_id, contact_id, date, kind, field, value_before, value_after, changed_by) SELECT  attribute_id, contact_id, '$now', kind, field, value_before, '', '$currentContactId' FROM fg_cm_change_log WHERE contact_id = @contactId AND attribute_id IN(" . implode(',', $systmPersonaBothlFields) . ') AND is_confirmed != 0 GROUP BY attribute_id ORDER BY id DESC';
            $conn->executeQuery($insertLogQuery);
        }
    }

    /**
     * Update Personalcategory field details if there are linked company contacts for aSingle person.
     *
     * @param Object  $conn                     Connection
     * @param Integer $clubId                   Club id
     * @param Array   $systmPersonalFieldValues Contact fields as keys and data to be saved as values
     */
    public function updateLinkedContactDetails($conn, $clubId, $systmPersonalFieldValues) {
        $insertFieldsSet = $duplicateSet = array();
        $insertFields = implode('`,`', array_keys($systmPersonalFieldValues));
        $result = $conn->fetchAll("SELECT `$insertFields` FROM master_system LEFT JOIN fg_cm_contact ON fg_cm_contact.fed_contact_id=master_system.fed_contact_id WHERE club_id = '$clubId' AND fg_cm_contact.id = @contactId ");
        foreach ($result[0] as $field => $value) {
            $duplicateSet[":field$field"] = $value;
            $insertFieldsSet[] = "`$field` = :field$field";
        }
        if(count($insertFieldsSet)>0){
           $insertQuery = "UPDATE master_system LEFT JOIN fg_cm_contact C ON C.fed_contact_id=master_system.fed_contact_id SET ".implode(',', $insertFieldsSet)." WHERE C.club_id = '$clubId' AND C.comp_def_contact = @contactId";
           $conn->executeQuery($insertQuery,$duplicateSet);
        }
    }

    /**
     * Function search an email already exists.
     *
     * @param Object  $conn                  Connection
     * @param Object  $club                  Club
     * @param String  $primaryEmail          Primary email
     * @param String  $value                 Email value
     * @param Integer $contactId             Contact id
     * @param Integer $hasFedMembership      Has Federation Membership
     * @param Integer $subscriberId          Subscriber id
     * @param String  $from                  Module area
     * @param bool    $checkPermDeletedConts Whether to search email exists in permanently deleted contacts also or not
     *
     * @return array
     */
    public function searchEmailExists($conn, $club, $primaryEmail, $value, $contactId, $hasFedMembership, $subscriberId = 0, $from = 'contact', $checkPermDeletedConts = false) {
        $federationId = $club->get('federation_id');
        $subfederationId = $club->get('sub_federation_id');
        $clubId = $club->get('id');
        $clubType = $club->get('type');
        $contactId = is_array($contactId) ? implode(',', $contactId) : $contactId;
        $conn = $this->_em->getConnection();
        $contactId = FgUtility::getSecuredDataString($contactId, $conn);
        $permanentDeleteCond1 = ($checkPermDeletedConts) ? "" : " cc.is_permanent_delete=0 AND ";
        $condtion = "";
        
        switch ($clubType) {
            case 'standard_club':
                // search email : All club contacts
                $sql = "SELECT cc.id,cc.club_id AS clubId,
                            (select cl.title from fg_club cl where cl.id=cc.main_club_id) AS clubTitle 
                        FROM fg_cm_contact AS cc 
                        LEFT JOIN master_system AS m 
                            ON m.fed_contact_id= cc.fed_contact_id
                        WHERE $permanentDeleteCond1 cc.club_id = '$clubId' AND m.`$primaryEmail` ='$value' " . ($contactId ? " AND cc.id NOT IN ($contactId)" : '');
                break;
            default:
                if (($hasFedMembership == 1)) {
                    $fedclubId = $clubId;
                    $subfedClbid = $clubId;
                    if ($clubType != 'federation')
                        $fedclubId = $federationId;
                    elseif ($clubType != 'sub_federation')
                        $subfedClbid = $subfederationId;

                    $condtion .= "(( cc.club_id= $fedclubId AND ((cc.main_club_id= $fedclubId) or (cc.fed_membership_cat_id IS NOT NULL )  or (cc.fed_membership_cat_id IS NULL ) ) ) ";
                    if ($clubType == 'sub_federation_club' || $clubType == 'federation_club')
                        $condtion .= "OR (cc.club_id= $clubId) ";
                    $condtion .= ' )';
                }
                else {
                    $condtion .= " (cc. club_id= $clubId and  cc.fed_membership_cat_id IS NULL)";
                }
                $sql = "SELECT ms.fed_contact_id AS contactId,cc.club_id AS clubId,		   
                            (select cl.title from fg_club cl where cl.id=cc.club_id) AS clubTitle
                        FROM master_system AS ms 
                        INNER JOIN fg_cm_contact AS cc 
                            ON ms.fed_contact_id= cc.fed_contact_id 
                        WHERE $permanentDeleteCond1 $condtion
                           
                           AND ms.`$primaryEmail` ='$value' " . ($contactId ? " AND ms.fed_contact_id NOT IN (SELECT fed_contact_id FROM fg_cm_contact WHERE id IN ($contactId))" : '');
                //echo $sql;
                break;
        }
        if ($from == 'subscriber') {
            $sql = $sql . " Union (Select s.id AS contactId,s.club_id AS clubId,(select cl.title from fg_club cl where cl.id=s.club_id) AS clubTitle FROM fg_cn_subscriber AS s Where s.email = '$value' AND s.club_id = $clubId " . ($subscriberId > 0 ? " AND s.id!='$subscriberId'" : '') . ' )';
        }
        
           
        $result = $conn->fetchAll($sql);

        return $result;
    }

    /**
     * Function to get name of the created/edited conatct.
     *
     * @param Array   $formValues        updated Details
     * @param Integer $systemPersonal    Personal category id
     * @param Integer $systemCompany     Comapny category id
     * @param Integer $systemFirstName   First name id
     * @param Integer $systemLastName    Last name id
     * @param Integer $systemCompanyName Comapny name id
     *
     * @return String
     */
    public function getUpdatedContactName($formValues, $systemPersonal, $systemCompany, $systemFirstName, $systemLastName, $systemCompanyName) {
        $contactName = '';
        if ($formValues['system']['contactType'] == 'Company') {
            $contactName = $formValues[$systemCompany][$systemCompanyName];
        } else {
            $contactName = $formValues[$systemPersonal][$systemLastName] . ', ' . $formValues[$systemPersonal][$systemFirstName];
        }

        return $contactName;
    }

    /**
     * Function to get Contact name.
     *
     * @param type $contactId contact Id
     *
     * @return String Contactname
     */
    public function getContactName($contactId) {
        $conn = $this->getEntityManager()->getConnection();
        $contactnameSql = "SELECT contactName($contactId) AS cname";
        $resultArray = $conn->fetchAll($contactnameSql);

        return $resultArray[0]['cname'];
    }

    /**
     * Function to insert sfGuard User entries while creating.
     *
     * @param int     $conn              Connection
     * @param Integer $contactId         Contact id
     * @param Integer $clubId            Club id
     * @param Integer $primaryEmailValue primary email value
     * @param array   $clubHeirarchy     Club Hierarchy
     * @param boolean $isFedMember       Is fedmember
     * @param boolean $useParamContact   Use passed param $contactId for insert
     */
    public function insertIntoSfguardUser($conn, $contactId, $primaryEmail, $fedContactId, $isFedMember, $useParamContact = false)
    {
        $sfGuardInsertSql = "INSERT INTO `sf_guard_user` (`username`, `username_canonical`, `email`, `email_canonical`,`created_at`, `updated_at`, `contact_id`, `club_id`) ";

        $sfGuardInsertSql.="SELECT M.`$primaryEmail`, M.`$primaryEmail`, M.`$primaryEmail`, M.`$primaryEmail`, NOW(), '0000-00-00 00:00:00',C.id, C.club_id FROM fg_cm_contact C INNER JOIN master_system M ON M.fed_contact_id=C.fed_contact_id WHERE C.fed_contact_id=$fedContactId AND M.`$primaryEmail` IS NOT NULL AND M.`$primaryEmail`!='' AND (C.main_club_id=C.club_id OR (C.fed_membership_cat_id IS NOT NULL AND (C.old_fed_membership_id IS NOT NULL OR C.is_fed_membership_confirmed='0')) )"
                . " ON DUPLICATE KEY UPDATE email = VALUES(email), email_canonical = VALUES(email_canonical), username = VALUES(username), username_canonical = VALUES(username_canonical) ";
        $conn->executeQuery($sfGuardInsertSql);
        if(!$isFedMember){
            $sfGuardUpdateSql = "UPDATE `sf_guard_user` S INNER JOIN fg_cm_contact C ON C.id=S.contact_id INNER JOIN master_system M ON M.fed_contact_id=C.fed_contact_id SET S.`username` = M.`$primaryEmail`, S.`username_canonical`=M.`$primaryEmail`, S.`email`=M.`$primaryEmail`, S.`email_canonical`=M.`$primaryEmail` 
                    WHERE C.id=:contactId AND M.`$primaryEmail` IS NOT NULL AND M.`$primaryEmail`!=''";
            $conn->executeQuery($sfGuardUpdateSql, array(':contactId' => $contactId));
        }

    }

    /**
     * Function to get all contact fields for a club. Used in filter.
     *
     * @param Object  $club            Club object
     * @param Boolean $fileUpload      set to skip file/image fields
     * @param array   $getFieldsofType Field types array
     *
     * @return array
     */
    public function getAllContactFields($club, $fileUpload = true, $getFieldsofType = array()) {
        $clubType = $club['clubType'];
        $defaultLang = $club['defaultLang'];
        $defaultSystemLang = $club['defaultSystemLang'];
        $clubId = $club['clubId'];
        $corrLangAttrId = $club['corrLangAttrId'];
        $clubLanguages = $club['clubLanguages'];
        $cacheLifeTime = $club['cacheLifeTime'];
        $clubCacheKey = str_replace('{{cache_area}}', 'contactfield', $club['clubCacheKey']);
        $clubHeirarchy = implode(',', $club['clubHeirarchy']);
        $sort = "( CASE cas.club_id WHEN 1 THEN '1' %s WHEN '$clubId' THEN '" . (count($club['clubHeirarchy']) + 2) . "' END ) AS sort";
        $sortSub = '';

        foreach ($club['clubHeirarchy'] as $key => $rowHierchyClub) {
            $sortSub .= "WHEN '{$rowHierchyClub}' THEN '" . ($key + 2) . "' ";
        }
        $sort = sprintf($sort, $sortSub);
        switch ($clubType) {
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
        $fileUploadSql = (!$fileUpload ? "AND (ca.input_type!='fileupload') AND (ca.input_type!= 'imageupload')" : '');
        $corrLangCond = (count($clubLanguages) <= 1) ? "AND ca.id <> $corrLangAttrId " : '';
        $fieldTypeCond = '';
        if (count($getFieldsofType) > 0) {
            $fieldTypes = "'" . implode("','", $getFieldsofType) . "'";
            $fieldTypeCond = " AND ca.input_type IN ($fieldTypes) ";
        }

        $conn = $this->getEntityManager()->getConnection();
        $contactFieldsSql = "SELECT ca.id AS id, IF(cai18n.fieldname_lang IS NULL OR cai18n.fieldname_lang='', ca.fieldname, cai18n.fieldname_lang) AS title, IF(cai18n.fieldname_short_lang IS NULL OR cai18n.fieldname_short_lang='', ca.fieldname_short, cai18n.fieldname_short_lang) AS shortName, ca.input_type AS type, IF(casi18n.title_lang IS NULL OR casi18n.title_lang='', cas.title, casi18n.title_lang) AS selectgroup, ca.predefined_value AS fieldValue, ca.club_id, cas.id AS catId, $sort,"
                . 'cca.is_required_type AS isRequiredType, (CASE WHEN cca.availability_contact != \'not_available\' THEN 1 ELSE 0 END) AS is_visible_contact, cca.privacy_contact, cca.is_set_privacy_itself, (CASE WHEN cca.availability_groupadmin = \'changable\' THEN 1 ELSE 0 END) AS is_changable_teamadmin, '
                . '(CASE WHEN cca.availability_groupadmin != \'not_available\' THEN 1 ELSE 0 END) AS is_visible_teamadmin, ca.availability_sub_fed,ca.availability_club,ca.is_company,ca.is_personal,ca.is_system_field,ca.addres_type,ca.address_id,cai18n.fieldname_short_lang AS short_name,cca.is_confirm_teamadmin,cca.is_confirm_contact '
                . 'FROM fg_cm_club_attribute AS cca '
                . 'LEFT JOIN fg_cm_attribute AS ca ON cca.attribute_id = ca.id '
                . "LEFT JOIN fg_cm_attribute_i18n AS cai18n ON cai18n.id = ca.id AND cai18n.lang = '$defaultLang'"
                . 'LEFT JOIN fg_cm_attributeset AS cas ON ca.attributeset_id = cas.id '
                . "LEFT JOIN fg_cm_attributeset_i18n AS casi18n ON casi18n.id = cas.id AND (CASE WHEN (cas.is_system=1) THEN (casi18n.lang = '$defaultSystemLang') ELSE (casi18n.lang = '$defaultLang') END)"
                . "WHERE (cca.club_id = '$clubId' AND ((ca. is_system_field  = 1 OR ca.is_fairgate_field = 1) OR ($fieldVisibility) ) AND cca.is_active=1 $fileUploadSql ) $corrLangCond $fieldTypeCond "
                . 'ORDER BY sort ASC, cas.sort_order ASC, cas.id ASC, cca.sort_order ASC, cca.attribute_id ASC';
//        $fieldsArray = $conn->fetchAll($contactFieldsSql);

        $fieldsArray = $conn->executeQuery($contactFieldsSql, array(), array(), new QueryCacheProfile($cacheLifeTime, $clubCacheKey));
        $resultContactfields = $fieldsArray->fetchAll(\PDO::FETCH_ASSOC);
        $fieldsArray->closeCursor(); // very important, do not forget

        return $resultContactfields;
    }

    
    /**
     * Function to get Contact name.
     *
     * @param array $corresIds
     *
     * @return array
     */
    public function getInvoiceFields($corresIds = array()) {
        $fieldsArray = array();
        if (count($corresIds) > 0) {
            $conn = $this->getEntityManager()->getConnection();
            $contactFieldsSql = 'SELECT id, address_id, fieldname_short as fieldTitle FROM fg_cm_attribute WHERE address_id IN(' . implode(',', $corresIds) . ')';
            $fieldsArray = $conn->fetchAll($contactFieldsSql);
        }

        return $fieldsArray;
    }

    /**
     * Function to Delete subscribers.
     *
     * @param int    $contactId      Contact id
     * @param int    $conatactClubId Club contact id
     * @param int    $clubId         Club id
     * @param object $container      container object
     *
     * @return array
     */
    public function deleteSubscribers($contactId, $conatactClubId, $clubId, $container) {
        $conn = $this->getEntityManager()->getConnection();
        $clubPdo = new \Common\UtilityBundle\Repository\Pdo\ClubPdo($container);
        $clubLevels = $clubPdo->getClubLevels($conatactClubId);
        foreach ($clubLevels as $clubdetail) {
            if ($clubdetail['club_type'] == 'federation' || $clubdetail['club_type'] == 'sub_federation') {
                $clubTable = 'master_federation_' . $clubdetail['Club_id'];
                $fedCkecking = "AND (c.is_former_fed_member =1 OR (c.fed_membership_cat_id IS NOT NULL AND is_fed_membership_confirmed=1) OR c.created_club_id={$clubdetail['Club_id']})";
            } else {
                $clubTable = 'master_club_' . $clubdetail['Club_id'];
                $fedCkecking = "AND c.club_id={$clubdetail['Club_id']}";
            }
            $fedContactId=($clubdetail['club_type'] == 'federation') ? 'mf.fed_contact_id':'mf.contact_id';
            $updateQuery = "UPDATE $clubTable mf "
                    . 'LEFT JOIN master_system ms ON ( ms.fed_contact_id='.$fedContactId.') '
                    . "LEFT JOIN fg_cm_contact c on ms.fed_contact_id=c.fed_contact_id AND c.is_deleted=0 AND c.is_permanent_delete='0' "
                    . "LEFT JOIN fg_cn_subscriber cs ON lower(trim(cs.email))=lower(trim(ms.`3`)) AND cs.club_id={$clubdetail['Club_id']} "
                    . "SET `is_subscriber`=1 WHERE lower(trim(cs.email))=lower(trim(ms.`3`)) AND c.id=$contactId AND c.is_deleted=0 AND c.is_permanent_delete='0' $fedCkecking";
            $deleteQuery = 'DELETE cs FROM fg_cn_subscriber cs '
                    . "INNER JOIN master_system ms ON ( lower(trim(cs.email))=lower(trim(ms.`3`)) AND cs.club_id={$clubdetail['Club_id']}) "
                    . "INNER JOIN $clubTable mf on ms.fed_contact_id=$fedContactId "
                    . "INNER JOIN fg_cm_contact c on ms.fed_contact_id=c.id AND c.is_deleted=0 AND c.is_permanent_delete='0' "
                    . " WHERE lower(trim(cs.email))=lower(trim(ms.`3`)) AND ms.fed_contact_id=$contactId AND c.is_deleted=0 AND c.is_permanent_delete='0' $fedCkecking";
            $conn->executeQuery($updateQuery);
            $conn->executeQuery($deleteQuery);
        }

        return true;
    }

    /**
     * Function to all contact fields and separate fields for all heirarchy level.
     *
     * @param int   $corrLangAttrId
     * @param array $clubDetails
     *
     * @return array Contact fields array
     */
    public function getContactFieldsForRouting($corrLangAttrId, $clubDetails, $parameters) {
        $fedFields = $subFedFields = $clubFields = $systemFields = $subSubFedFields = array();
        //for collecting all contact fields under each type(feeration/subfederation etc.)
        $rowFieldsArray = $this->getAllContactFields($clubDetails);
        $allContactFields = $emailFields = $fileFields = array();

        foreach ($rowFieldsArray as $rowField) {
            $isEditable = 1;
            $fieldTable = '';
            switch ($clubDetails['clubType']) {
                case 'federation_club':
                case 'sub_federation_club':
                    $fieldTable = 'master_club_';
                    $isEditable = ($rowField['availability_club'] != 'changable' && $rowField['club_id'] != $clubDetails['clubId']) ? 0 : 1;
                    break;
                case 'sub_federation':
                    $fieldTable = 'master_federation_';
                    $isEditable = ($rowField['availability_sub_fed'] != 'changable' && $rowField['club_id'] != $clubDetails['clubId']) ? 0 : 1;
                    break;
                default:
                    $fieldTable = $clubDetails['clubType'] == 'federation' ? 'master_federation_' : 'master_club_';
                    break;
            }
            $isRequired = ($rowField['isRequiredType'] == 'all_contacts') ? 1 : 0;

            $selectedField = array('id' => $rowField['id'], 'title' => $rowField['title'], 'type' => $rowField['type'], 'club_id' => $rowField['club_id'], 'category_id' => $rowField['catId'], 'is_editable' => $isEditable, 'is_required' => $isRequired, 'is_company' => $rowField['is_company'], 'is_personal' => $rowField['is_personal'], 'is_system_field' => $rowField['is_system_field'], 'addres_type' => $rowField['addres_type'], 'address_id' => $rowField['address_id'], 'is_visible_contact' =>  $rowField['is_visible_contact'],'privacy_contact' => $rowField['privacy_contact'],'is_set_privacy_itself' => $rowField['is_set_privacy_itself'], 'is_changable_teamadmin' => $rowField['is_changable_teamadmin'], 'is_visible_teamadmin' => $rowField['is_visible_teamadmin'], 'is_confirm_teamadmin' => $rowField['is_confirm_teamadmin'], 'is_confirm_contact' => $rowField['is_confirm_contact'],'is_required' => $rowField['isRequiredType']);
            if ($rowField['club_id'] == '1') { //system fields
                $selectedField['field_table'] = 'master_system';
            } elseif ($rowField['club_id'] == $clubDetails['clubId']) { //club fields
                $selectedField['field_table'] = $fieldTable . $rowField['club_id'];
            } else { //federation field
                $selectedField['field_table'] = 'master_federation_' . $rowField['club_id'];
            }

            //To get the email attributes; used in messages
            if($rowField['type'] == 'email' || $rowField['type'] == 'login email'  ){
                $emailFields[] = $rowField['id'];
            }
            //To get the filetype and imagetype attributes; used in remove fed membership after sharing
            if (($rowField['type'] == 'imageupload') || ($rowField['type'] == 'fileupload')) {
                $fileFields[] = $rowField['id'];
            }

            $allContactFields[$rowField['id']] = $selectedField;
            switch ($rowField['club_id']) {
                case $parameters['federation_id']:
                    $fedFields[$rowField['id']] = $selectedField;
                    break;
                case $parameters['sub_federation_id']:
                    $subFedFields[$rowField['id']] = $selectedField;
                    break;
                case $clubDetails['clubId']:
                    $clubFields[$rowField['id']] = $selectedField;
                    break;
                case '1':
                    $systemFields[$rowField['id']] = $selectedField;
                    break;
                default:
                    $subSubFedFields[$rowField['id']] = $selectedField;
                    break;
            }
        }

        return array('contactFields' => $rowFieldsArray, 'allContactFields' => $allContactFields,
            'allContactFields' => $allContactFields, 'clubFields' => $clubFields, 'fedFields' => $fedFields,
            'subFedFields' => $subFedFields, 'subSubFedFields' => $subSubFedFields, 'systemFields' => $systemFields,
            'emailFields' => $emailFields, 'fileFields' => $fileFields);
    }

    /**
     * Function to add log entries when switching contact type (single person/company)
     *
     * @param object $club
     * @param int $contact_id
     * @param object $conn
     * @param object $now
     *
     * @return boolean
     */
    public function insertContactSwitchingLog($club, $fedContactId, $conn, $now, $contactType){
        $federationId = $club->get('federation_id');
        $clubIds = $conn->fetchAll("SELECT GROUP_CONCAT(club_id) as clubIds FROM fg_cm_contact WHERE fed_contact_id=$fedContactId");
        $contactType=($contactType=='Company') ? 'Single person':'Company';
        $fieldDetails = $this->getEntityManager()->getRepository('CommonUtilityBundle:FgCmAttributeset')->getAttributeIdsByType($clubIds[0]['clubIds'], $contactType);
        $conn->executeQuery("INSERT INTO fg_cm_change_log(contact_id, date, kind, field, value_before, value_after, changed_by, attribute_id)"
                . " (SELECT L.contact_id,'$now', 'data',L.field,(SELECT LL.value_after FROM fg_cm_change_log LL WHERE LL.attribute_id=L.attribute_id AND L.contact_id=LL.contact_id AND LL.date=MAX(L.date) AND (LL.is_confirmed = 1 OR LL.is_confirmed IS NULL OR LL.is_confirmed ='') ORDER BY LL.date DESC LIMIT 1) as valueBefore,'','{$club->get('contactId')}',L.attribute_id FROM fg_cm_change_log L WHERE L.attribute_id IN ({$fieldDetails['attributes']}) AND L.contact_id IN (SELECT id FROM fg_cm_contact WHERE fed_contact_id=$fedContactId) GROUP BY L.attribute_id HAVING valueBefore IS NOT NULL AND valueBefore !='')");
        $attrIds = explode(',', $fieldDetails['attributes']);
        $attrIdClub = explode(',', $fieldDetails['catAttribute']);
        $contactFields = $club->get('allContactFields');
        foreach ($attrIds as $key=>$attrId) {
            if (in_array($attrId, array(5, 21, 68))) {
                $updateQuery['master_system'][] = "T.`$attrId`=''";
            } elseif (!empty($contactFields[$attrId]['field_table'])) {
                $updateQuery[$contactFields[$attrId]['field_table']][] = "T.`$attrId`=''";
            } else { //attribute id not visible in clurrent club update on federation level
                $catkey=explode('-',$attrIdClub[$key]);
                if($catkey[0]==$attrId && !empty($catkey[1])){
                   $updateQuery['master_federation_'.$catkey[1]][] = "T.`$attrId`=''";
                }
            }
        }
        foreach ($updateQuery as $table => $values) {
            $contactField= ($table=='master_system' ||$table=='master_federation_'.$federationId) ? 'fed_contact_id' :'contact_id';
            $conn->executeQuery("UPDATE $table T INNER JOIN fg_cm_contact C ON C.id=T.$contactField SET " . implode(',', $values) . " WHERE C.fed_contact_id=$fedContactId");
        }

        return true;
    }
    
    /**
     * This function is used to check whether an email exist with an existing fed member or contact in the same club
     * 
     * @param type $container
     * @param type $contact
     * @param type $emailValue
     * @param type $hasFedMembership
     * @param type $subscriberId
     * @param type $from
     * @param type $excludeMergableContacts Exclude mergeble contacts from search (for allow merging case value should be true else false)
     * 
     * @return type
     */
    public function searchEmailExistAndIsMergable($container,$contact,$emailValue,$hasFedMembership,$subscriberId = 0, $from = 'contact', $excludeMergableContacts = false, $typeOfContact='Single person') {
        if(empty($emailValue)){
            return array();
        }
        $club = $container->get('club');
        $federationId = $club->get('federation_id');
        $subfederationId = $club->get('sub_federation_id');
        $conn = $this->getEntityManager()->getConnection();
        $primaryEmail = $container->getParameter('system_field_primaryemail');
        $clubId = $club->get('id');
        $clubType = $club->get('type');
        $contactId = is_array($contact) ? implode(',', $contact) : $contact;
        $contactId = FgUtility::getSecuredDataString($contactId, $conn);
        $permanentDeleteCond1 = " cc.is_permanent_delete=0 AND ";
        $mode = $condtion = "";
        $excluMergeQry='';
        //exclude mergable contacts
        if(!is_array($contact) && $contact){
            $currentMem = $conn->fetchAll("SELECT id,fed_contact_id FROM fg_cm_contact WHERE id=$contact AND (fed_membership_cat_id IS NULL OR fed_membership_cat_id ='')");
        }
        if($excludeMergableContacts && ($clubType=='sub_federation_club' || $clubType=='federation_club') ){
            //if created/edited contact is single person always show company contact with same email
            $showCompany = ($typeOfContact=='Single person') ? '1':'0';
            if(empty($contactId)){
                $excluMergeQry = " AND (cc.is_company=$showCompany OR cc.club_id=$clubId OR cc.created_club_id IN (SELECT id FROM fg_club WHERE (federation_id=$federationId OR id=$federationId) AND club_type IN ('sub_federation','federation') ))";
            } elseif(!is_array($contact)){
                if($currentMem[0]['id']){
                   $excluMergeQry = " AND ( cc.is_company=$showCompany OR cc.club_id=$clubId OR cc.created_club_id IN (SELECT id FROM fg_club WHERE (federation_id=$federationId OR id=$federationId) AND club_type IN ('sub_federation','federation' )) )";
                }
            }
        }
        if(!is_array($contact) && $contact && $hasFedMembership==1){
            $clubIdQuery="(SELECT club_id FROM fg_cm_contact WHERE fed_contact_id=(SELECT fed_contact_id FROM fg_cm_contact WHERE id=$contact limit 1) )";
        } else {
            $mode='new';
            $clubIdQuery="($clubId)";
        }
        switch ($clubType) {
            case 'standard_club':
                // search email : All club contacts
                $clubIdQuery="($clubId)";
                break;
            case 'sub_federation_club':
                if (($hasFedMembership == 1)) { //all club contact,sub fed own contact,fed own contact+fed members
                    $clubIdQuery = ($mode=='new') ? "($clubId,$subfederationId,$federationId)":$clubIdQuery;
                } else {
                    $clubIdQuery="($clubId)";
                }
                break;
            case 'federation_club':
                if (($hasFedMembership == 1)) { //all club contact,fed own contact+fed members
                    $clubIdQuery = ($mode=='new') ? "($clubId,$federationId)":$clubIdQuery;
                } else {
                    $clubIdQuery="($clubId)";
                }
                break;
            case 'federation'://fed own contact+fed members
                
               
                if (($hasFedMembership == 1) && ($mode !='new') ) {
                    $clubIdQuery = $clubIdQuery;
                } else {
                    $clubIdQuery="($clubId)";
                }
                if(!is_array($contact) && $contactId!='' && $contactId!=0){
                     $isFedAdmin = $this->getEntityManager()->getRepository('CommonUtilityBundle:FgCmContact')->find($contactId);
                     if($isFedAdmin->getIsFedAdmin() == 1){
                         $result = $this->emailDuplicateCheckForFedAdmin($conn,$federationId,$primaryEmail,$emailValue,$contactId,$from,$clubId,$subscriberId);
                         return $result;
                     }
                }
                break;
            case 'sub_federation':
                if (($hasFedMembership == 1)) {//sub fed own contact,fed own contact+fed members
                    $clubIdQuery = ($mode=='new') ? "($clubId,$federationId)":$clubIdQuery;
                } else {
                    $clubIdQuery="($clubId)";
                }
                
                break;
        }
        $sql = "SELECT ms.fed_contact_id AS contactId,cc.club_id AS clubId,'contact' AS type,(select cl.title from fg_club cl where cl.id=cc.club_id) AS clubTitle
                        FROM master_system AS ms 
                        INNER JOIN fg_cm_contact AS cc 
                            ON ms.fed_contact_id= cc.fed_contact_id 
                        WHERE $permanentDeleteCond1 
                            (cc.club_id IN $clubIdQuery OR (cc.is_fed_admin = 1 AND cc.club_id = $federationId ))AND 
                            ((cc.main_club_id=cc.club_id) OR (cc.fed_membership_cat_id IS NOT NULL AND (cc.old_fed_membership_id IS NOT NULL OR cc.is_fed_membership_confirmed='0')) )
                            $excluMergeQry 
                            AND lower(ms.`$primaryEmail`) =lower('$emailValue') " . ($contactId ? " AND ms.fed_contact_id NOT IN (SELECT fed_contact_id FROM fg_cm_contact WHERE id IN ($contactId))" : '');
                
        if ($from == 'subscriber') {
            $sql = "(" . $sql . ") Union (Select s.id AS contactId,s.club_id AS clubId,'subscriber' AS type,(select cl.title from fg_club cl where cl.id=s.club_id) AS clubTitle FROM fg_cn_subscriber AS s Where lower(s.email) = lower('$emailValue') AND s.club_id = $clubId " . ($subscriberId > 0 ? " AND s.id!='$subscriberId'" : '') . ' )';
        }


        $result = $conn->fetchAll($sql);

        return $result;
    }
    /**
     * check email duplicate check for fedadmins
     * @param object $conn
     * @param int $federationId         federationId
     * @param string $primaryEmail      primaryEmail
     * @param string $emailValue        emailValue
     * @param int $contactId            contactId
     * @param string $from              from
     * @param int $clubId               clubId
     * @param int $subscriberId         subscriberId
     * 
     * @return array
     */
    public function emailDuplicateCheckForFedAdmin($conn,$federationId,$primaryEmail,$emailValue,$contactId,$from,$clubId,$subscriberId){
        $permanentDeleteCond1 = " cc.is_permanent_delete=0 AND ";
        $sql = "SELECT ms.fed_contact_id AS contactId,cc.club_id AS clubId,		   
                            (select cl.title from fg_club cl where cl.id=cc.club_id) AS clubTitle
                        FROM master_system AS ms 
                        INNER JOIN fg_cm_contact AS cc 
                            ON ms.fed_contact_id= cc.fed_contact_id 
                        INNER JOIN fg_club c ON c.id = cc.club_id
                        WHERE $permanentDeleteCond1 c.federation_id = $federationId and cc.club_id=cc.main_club_id
                            AND lower(ms.`$primaryEmail`) =lower('$emailValue') " . ($contactId ? " AND ms.fed_contact_id NOT IN (SELECT fed_contact_id FROM fg_cm_contact WHERE id IN ($contactId))" : '');
                
//        if ($from == 'subscriber') {
//            $sql = "(" . $sql . ") Union (Select s.id AS contactId,s.club_id AS clubId,(select cl.title from fg_club cl where cl.id=s.club_id) AS clubTitle FROM fg_cn_subscriber AS s Where s.email = '$emailValue' AND s.club_id = $clubId " . ($subscriberId > 0 ? " AND s.id!='$subscriberId'" : '') . ' )';
//        }
//

        $result = $conn->fetchAll($sql);

        return $result;
    }
    /**
     * 
     * @param array $idArray system fields id
     * @param string $defaultLang club default language
     * 
     * @return array $result system field details
     */
     
   public function getSelectedSystemFieldDetails($idArray, $defaultLang) {
       $qb = $this->createQueryBuilder('a')
                ->select('a.id,COALESCE(ai18.fieldnameLang,a.fieldname) as fieldnameLang, a.inputType,a.predefinedValue')  
                ->leftJoin('CommonUtilityBundle:FgCmAttributeI18n', 'ai18', 'WITH', 'ai18.id = a.id')
                ->where('ai18.lang = :clubSystemLang')
                ->andWhere('a.id IN (:idArray)')
               ->andWhere('ai18.isActive=1 ')
                ->setParameters(array('idArray' => $idArray,'clubSystemLang' => $defaultLang));
        $result = $qb->getQuery()->getResult();

        $nwArray = array();
        foreach($result as $val) {
            $nwArray[$val['id']] = $val;
        }
        return $nwArray; 
    }

}
