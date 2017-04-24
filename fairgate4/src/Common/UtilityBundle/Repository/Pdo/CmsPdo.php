<?php

/**
 * CmsPdo
 */
namespace Common\UtilityBundle\Repository\Pdo;

use Doctrine\DBAL\Cache\QueryCacheProfile;

/**
 * Used to handling different CMS functions.
 *
 * @author  pitsolutions.ch <pit@solutions.com>
 */
class CmsPdo
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
     * Function to update element table
     * @param array $clipboardDetails Element details
     */
    public function saveElementFromClipboard($clipboardDetails)
    {
        $boxId = $clipboardDetails['boxId'];
        $elementId = $clipboardDetails['elementId'];
        $sortOrder = $clipboardDetails['sortOrder'];
        $updateElement = "UPDATE fg_cms_page_content_element pc SET pc.box_id = $boxId, pc.deleted_at = NULL,pc.sort_order=$sortOrder WHERE pc.id = $elementId";
        $this->conn->executeQuery($updateElement);
    }

    /**
     * Function to get Gallery Images for special page
     * @param array $gallerydetails 
     */
    public function getGalleryDetails($club_id, $roleType, $scopeChecking, $lang = 'en')
    {

        $terminologyService = $this->container->get('fairgate_terminology_service');

        $clubTerm = ucfirst($terminologyService->getTerminology('Club', $this->container->getParameter('singular')));
        $termExecutive = $this->container ? ucfirst($terminologyService->getTerminology('Executive Board', $this->container->getParameter('singular'))) : 'Executive';
        $scopecondition = "";
        if ($scopeChecking == true) {
            $scopecondition = "AND GI.scope='public'";
        }
        $condition = $scopecondition;
        if ($roleType != 'ALL') {
            $roleArray = array();
            $clubFlag = 0;
            foreach ($roleType as $roles => $roleID) {

                if ($roleID[1] != 'CLUB')
                    $roleArray[] = $roleID[1];
                else
                    $clubFlag = 1;
            }

            if ($clubFlag == 1) {
                $condition .= " AND (G1.type = 'CLUB' AND G1.club_id = {$club_id})";
            }

            if (count($roleArray) > 0) {
                $roleCondition = " AND R.is_active=1  AND G1.role_id IN (" . implode(',', $roleArray) . ") ";
                if ($clubFlag == 1)
                    $condition .= "OR (G1.type = 'ROLE' $roleCondition AND G1.club_id = {$club_id})";
                else
                    $condition .= "AND (G1.type = 'ROLE' $roleCondition AND G1.club_id = {$club_id})";
            }
        }
        if ($roleType == 'ALL') {

            $condition = $scopecondition . ' AND (G1.role_id IS NOT NULL AND R.is_active=1 OR  G1.role_id  is null)';
        }

        $sql = "SELECT GI.id, G1.album_id, IF(G1.role_id is NULL,$club_id,G1.role_id) as role_id, IF(R.type is NULL, 'C', R.type) as role_type,
                CASE WHEN R.title is NULL THEN '$clubTerm' WHEN R.is_executive_board='1' THEN '" . $termExecutive . "' WHEN RI.title_lang  is NOT NULL  THEN RI.title_lang
                ELSE R.title  END AS title,
                CASE  WHEN G1.parent_id=0 THEN G1.sort_order * 10 ELSE G1.sort_order + G2.sort_order * 10 END AS parentSort,
                CASE WHEN AIT2.name_lang IS NOT NULL AND AIT2.name_lang !='' THEN AIT2.name_lang
                WHEN AI2.name IS NULL AND AIT2.name_lang IS  NULL AND AIT.name_lang IS NOT NULL AND AIT.name_lang !=''  THEN  AIT.name_lang
                WHEN AI2.name IS NULL AND AIT.name_lang IS NULL THEN AI.name 
                ELSE AI.name END AS parentname,G1.type,IF(G1.parent_id=0,G1.album_id,G1.parent_id) as parent_id,
           IF(G1.role_id is NULL,0,R.sort_order)as galsortorder ,G1.sort_order as albumOrder,IF(AIT.name_lang IS NOT NULL,AIT.name_lang,AI.name) as name,
           IF(G2.album_id=G1.parent_id,'0',G2.album_id)  as subAlbumID,
           GAI.is_cover_image ,GI.type, 
           GI.filepath,
           IF(GI.type='IMAGE',GI.file_name, GI.video_thumb_url) as file_name, 
           GI.video_thumb_url,
           GI.scope,GAI.sort_order as albumItemSortOrder, 
           IF(GITN.description_lang IS NOT NULL AND GITN.description_lang != '',GITN.description_lang, '') as description 
           FROM fg_gm_gallery G1
           Left Join fg_gm_gallery G2 ON G1.parent_id=G2.album_id AND  G2.club_id=$club_id
           LEFT Join fg_rm_role R ON R.id=G1.role_id and R.club_id=$club_id
           LEFT Join fg_rm_role_i18n RI ON RI.id=R.id and RI.lang='$lang'
           INNER JOIN fg_gm_album AI ON AI.id=G1.album_id 
           LEFT Join fg_gm_album_i18n AIT ON AIT.id=AI.id and AIT.lang='$lang'
           LEFT JOIN fg_gm_album AI2 ON AI2.id=G1.parent_id
           LEFT Join fg_gm_album_i18n AIT2 ON AIT2.id=AI2.id and AIT2.lang='$lang'
           INNER JOIN `fg_gm_album_items` GAI ON AI.id=GAI.album_id
           INNER JOIN `fg_gm_items` GI ON GAI.items_id=GI.id 
           LEFT JOIN fg_gm_item_i18n GITN ON GI.id=GITN.id and GITN.lang='$lang' 
           Where G1.club_id=$club_id $condition
           ORDER BY `G1`.`role_id` ASC,`R`.`type` ASC, `galsortorder` ASC,parentSort ASC,albumItemSortOrder Asc";

        $result = $this->conn->fetchAll($sql);

        return $result;
    }

    /**
     * This function is used to get the navigation heirarchy with full details
     *
     * @param int $clubId Id of the viewing club
     * 
     * @return array navigation details of the club
     */
    public function getNavigationHeirarchy($clubId, $domainCacheKey, $cacheLifeTime, $cachingEnabled)
    {
        $cacheKey = $domainCacheKey . '_navigation';
        $setVariables = "SET @start_with := 1 ,@id := @start_with,@level := 0;";
        $this->conn->executeQuery($setVariables);
        $subLevelSql = "SELECT n.id, ni18n.lang ,ni18n.title_lang, ho.level, 
                        n.page_id, n.is_active, n.is_public, n.sort_order, n.title, 
                        n.parent_id, n.navigation_url, n.club_id, n.external_link, n.type, n.edited_at  
                        FROM (SELECT  sublevelNavs(id, $clubId) AS id, @level AS level
                        FROM (SELECT  @start_with := 1 ,@id := @start_with,@level := 0) vars, 
                        fg_cms_navigation WHERE @id IS NOT NULL) ho 
                        JOIN fg_cms_navigation n ON n.id = ho.id AND n.is_active = 1
                        LEFT JOIN fg_cms_navigation_i18n ni18n ON ni18n.id = n.id;";
        if ($domainCacheKey && $cachingEnabled) {
            $stmt = $this->conn->executeQuery($subLevelSql, array(), array(), new QueryCacheProfile($cacheLifeTime, $cacheKey));
            $navResult = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $stmt->closeCursor(); // very important, do not forget
        } else {
            $navResult = $this->conn->fetchAll($subLevelSql);
        }

        $currId = 0;
        $arrCount = 0;

        foreach ($navResult as $i => $subNavArr) {
            if ($currId == $subNavArr['id']) {
                $navHeirarchyArray[$index]['langTitle'][$subNavArr['lang']] = array('title_lang' => $subNavArr['title_lang']);
                if (isset($publicNavHeirarchyArray[$index])) {
                    $publicNavHeirarchyArray[$index]['langTitle'][$subNavArr['lang']] = array('title_lang' => $subNavArr['title_lang']);
                }
            } else {
                /*                 * ********************* Build separate the Title array inside the main array ************ */
                $currId = $subNavArr['id'];
                $index = $arrCount;
                $titleArr = array('lang' => $subNavArr['lang'], 'title_lang' => $subNavArr['title_lang']);
                unset($subNavArr['title_lang']);
                unset($subNavArr['lang']);
                $navHeirarchyArray[$arrCount] = $subNavArr;
                $navHeirarchyArray[$arrCount]['langTitle'][$titleArr['lang']] = array('title_lang' => $titleArr['title_lang']);

                /* Setting up publically viewable menu array */
                if ($subNavArr['is_public'] == 1) {
                    if ($publicPageUrl == '' && $subNavArr['page_id'] != '') {
                        $publicPageUrl = $subNavArr['navigation_url'];
                    }
                    $publicNavHeirarchyArray[] = $navHeirarchyArray[$arrCount];
                }
                /*                 * ********************* Build separate the Title array inside the main array ************ */
                $arrCount++;
            }
            /* Setting up home page URL */
            if ($homePageUrl == '' && $subNavArr['page_id'] != '') {
                $homePageUrl = $subNavArr['navigation_url'];
            }
        }
        //echo '<pre>';print_r($navHeirarchyArray);exit;
        $navHeirarchyArray['homePageUrl'] = $homePageUrl;
        $navHeirarchyArray['publicPageUrl'] = $publicPageUrl;
        $navHeirarchyArray['publicPages'] = $publicNavHeirarchyArray;

        return $navHeirarchyArray;
    }

    /**
     * This function is used to update the navigation title value in main table with club's
     * current default language entries in corresponding i18n table
     * 
     * @param array $params Array of parameters
     */
    public function updateNavigationTitle($params)
    {
        $query = 'UPDATE fg_cms_navigation N INNER JOIN fg_cms_navigation_i18n N18n '
            . 'ON N.id = N18n.id AND N18n.lang = :clubDefaultLang '
            . 'SET N.title = N18n.title_lang WHERE N.club_id = :clubId';
        $this->conn->executeQuery($query, $params);
    }

    /**
     * This function is used to save element log details
     * 
     * @param type $logArray
     * 
     * @return void
     */
    public function saveLog($logArray)
    {
        if (count($logArray) > 0) {
            $sql = "INSERT INTO fg_cms_page_content_element_log (element_id, page_id, type, action, value_before, value_after, date, changed_by) VALUES ";
            $sql .= implode(',', $logArray);
            $this->conn->executeQuery($sql);
        }
    }

    /**
     * This function is used to delete from sf_guard_user_group if userId, groupId combo doesnot exist anymore.
     * It will excecute when the page is deleted and user rigths of the pageadmin get removed.
     * 
     * @return void
     */
    public function deleteUserRightsGroup()
    {
        $deleteQuery = "DELETE ug FROM sf_guard_user_group ug "
            . "LEFT JOIN sf_guard_user_team ON sf_guard_user_team.user_id= ug.user_id AND sf_guard_user_team.group_id=ug.group_id "
            . "LEFT JOIN sf_guard_user_page ON sf_guard_user_page.user_id = ug.user_id AND sf_guard_user_page.group_id = ug.group_id "
            . "LEFT JOIN sf_guard_group g ON g.id = ug.group_id "
            . "WHERE g.type IN ('role','page') "
            . "AND sf_guard_user_team.group_id IS NULL "
            . "AND sf_guard_user_team.user_id IS NULL "
            . "AND sf_guard_user_page.group_id IS NULL "
            . "AND sf_guard_user_page.user_id IS NULL";

        $this->conn->executeQuery($deleteQuery);
    }

    /**
     * This function is used to find page-admin ids of particular pages.
     * 
     * @param array $pageIds array of page ids
     * 
     * @return array $result
     */
    public function getPageAdminIds($pageIds)
    {
        $pageIdsString = implode(',', $pageIds);
        $selectQuery = "SELECT DISTINCT(u.contact_id) "
            . "FROM sf_guard_user_page up JOIN sf_guard_user u ON u.id= up.user_id "
            . "WHERE up.page_id IN ($pageIdsString)";
        $result = $this->conn->fetchAll($selectQuery);

        return $result;
    }

    /**
     * This function is used to duplicate contact form details
     * 
     * @param int $formId    The Form id to duplicate
     * @param int $newFormId The new form id 
     */
    public function duplicateContactFormI18nData($formId, $newFormId)
    {
        if ($formId != '' && $newFormId != '') {
            $query = "INSERT INTO fg_cms_forms_i18n (id, lang, confirmation_email_subject_lang, confirmation_email_content_lang, acceptance_email_subject_lang, acceptance_email_content_lang, "
                . "dismissal_email_subject_lang, dismissal_email_content_lang, success_message_lang) SELECT :newFormId, lang, confirmation_email_subject_lang, confirmation_email_content_lang, "
                . "acceptance_email_subject_lang, acceptance_email_content_lang, dismissal_email_subject_lang, dismissal_email_content_lang, success_message_lang FROM fg_cms_forms_i18n WHERE "
                . "id = :formId";
            $this->conn->executeQuery($query, array('formId' => $formId, 'newFormId' => $newFormId));
        }
    }

    /**
     * This function is used to duplicate the contact application form fields data
     * 
     * @param int $formId    The form id
     * @param int $newFormId The new form id
     * @param int $contactId The contact id
     * 
     * @return array $return The return array with duplicated details
     */
    public function duplicateContactFormFieldsData($formId, $newFormId, $contactId)
    {
        if ($formId != '' && $newFormId != '') {
            $query = "INSERT INTO fg_cms_page_content_element_form_fields (form_id, form_field_type, attribute_id, is_field_hidden_with_default_value, fieldname, field_type, predefined_value, "
                . "placeholder_value, tooltip_value, is_required, sort_order, is_active, created_at, created_by, updated_at, updated_by, number_min_value, number_max_value, number_step_value, "
                . "date_min, date_max, show_selection_values_inline, is_multi_selectable, use_mail_for_notification, is_deleted, deleted_at, club_membership_selection, default_club_membership) "
                . "SELECT :newFormId, form_field_type, attribute_id, is_field_hidden_with_default_value, fieldname, field_type, predefined_value, placeholder_value, tooltip_value, is_required, "
                . "sort_order, is_active, :now, :contactId, null, null, number_min_value, number_max_value, number_step_value, date_min, date_max, show_selection_values_inline, is_multi_selectable, "
                . "use_mail_for_notification, is_deleted, :now, club_membership_selection, default_club_membership FROM fg_cms_page_content_element_form_fields WHERE form_id = :formId "
                . "ORDER by id ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute(array('formId' => $formId, 'newFormId' => $newFormId, 'now' => date('Y-m-d H:i:s'), 'contactId' => $contactId));
            $rowCount = $stmt->rowCount();
            $lastInsertId = $this->conn->lastInsertId();
            $return = array('lastInsertedId' => $lastInsertId, 'insertedRowsCount' => $rowCount, 'oldFieldIds' => $this->getAllFieldIdsOfAForm($formId));

            return $return;
        }
    }

    /**
     * This function is used to duplicate the contact application form fields i18n entries
     */
    public function duplicateContactFormFieldsI18nData()
    {
        $query = "INSERT INTO fg_cms_page_content_element_form_fields_i18n (id, lang, fieldname_lang, predefined_value_lang, placeholder_value_lang, tooltip_value_lang) SELECT m.new_field_id, "
            . "fi18n.lang, fi18n.fieldname_lang, fi18n.predefined_value_lang, fi18n.placeholder_value_lang, fi18n.tooltip_value_lang FROM fg_cms_page_content_element_form_fields_i18n fi18n "
            . "INNER JOIN temp_field_ids_mapping_table m ON (m.old_field_id = fi18n.id) ";
        $this->conn->executeQuery($query);
    }

    /**
     * This function is used to duplicate the contact application form fields option details
     */
    public function duplicateContactFormFieldOptionsData()
    {
        $query = "INSERT INTO fg_cms_page_content_element_form_field_options (field_id, is_active, selection_value_name, sort_order, is_deleted) SELECT m.new_field_id, fo.is_active, "
            . "fo.selection_value_name, fo.sort_order, fo.is_deleted FROM fg_cms_page_content_element_form_field_options fo INNER JOIN temp_field_ids_mapping_table m ON "
            . "(m.old_field_id = fo.field_id)";
        $this->conn->executeQuery($query);
    }

    /**
     * This function is used to duplicate the contact form field options i18n data
     */
    public function duplicateContactFormFieldOptionsI18nData()
    {
        $query = "INSERT INTO fg_cms_page_content_element_form_field_options_i18n (id, lang, selection_value_name_lang) "
            . " SELECT NEWOPTIONS.id, foi18n.lang, foi18n.selection_value_name_lang "
            . " FROM fg_cms_page_content_element_form_field_options_i18n foi18n "
            . " INNER JOIN fg_cms_page_content_element_form_field_options OPTIONS ON OPTIONS.id = foi18n.id "
            . " INNER JOIN fg_cms_page_content_element_form_fields OLDFIELDS ON OLDFIELDS.id = OPTIONS.field_id "
            . " INNER JOIN temp_field_ids_mapping_table TEMP ON TEMP.old_field_id = OLDFIELDS.id "
            . " INNER JOIN fg_cms_page_content_element_form_fields NEWFIELDS ON NEWFIELDS.id = TEMP.new_field_id "
            . " INNER JOIN fg_cms_page_content_element_form_field_options NEWOPTIONS ON (NEWOPTIONS.field_id = NEWFIELDS.id  AND NEWOPTIONS.sort_order = OPTIONS.sort_order ) "
            . " GROUP BY NEWOPTIONS.id, foi18n.lang ";
        $this->conn->executeQuery($query);
    }

    /**
     * This function is used to duplicate the contact form membership selection data
     */
    public function duplicateContactFormMembershipSelectionData()
    {
        $query = "INSERT INTO fg_cms_page_content_element_membership_selections (field_id, membership_id) SELECT m.new_field_id, ms.membership_id FROM "
            . "fg_cms_page_content_element_membership_selections ms INNER JOIN temp_field_ids_mapping_table m ON (m.old_field_id = ms.field_id)";
        $this->conn->executeQuery($query);
    }

    /**
     * This function is used to get all field ids in a form
     * 
     * @param int $formId The form id
     * 
     * @return array $result The result set 
     */
    private function getAllFieldIdsOfAForm($formId)
    {
        $query = "SELECT f.id FROM fg_cms_page_content_element_form_fields f WHERE f.form_id = :formId ORDER by id ASC";
        $result = $this->conn->fetchAll($query, array('formId' => $formId));

        return $result;
    }

    /**
     * This function is used to create temporary mapping table of form fields
     * 
     * @param array $mappedFieldIds Array of old and new form ids
     */
    public function createTemporaryMappingTableOfFormFields($mappedFieldIds)
    {
        if (count($mappedFieldIds) > 0) {
            $query = "CREATE TEMPORARY TABLE temp_field_ids_mapping_table (old_field_id INT, new_field_id INT);";
            $query .= "INSERT INTO temp_field_ids_mapping_table VALUES ";
            foreach ($mappedFieldIds as $mappedFieldId) {
                $query .= "(" . $mappedFieldId['oldFieldId'] . ", " . $mappedFieldId['newFieldId'] . "),";
            }
            $query = rtrim($query, ',');
            $this->conn->executeQuery($query);
        }
    }

    /**
     * This function is used to drop the temporary mapping table after duplication
     */
    public function dropTemporaryMappingTableOfFormFields()
    {
        $this->conn->executeQuery('DROP TEMPORARY TABLE IF EXISTS temp_field_ids_mapping_table');
    }

    /**
     * Method to save default club language entries to main table. To handle scenarios when club default languages changes.
     *
     * @param int    $websettingsId       websettingsId
     * @param string $clubDefaultLanguage club-default-lang
     *
     * @return void
     */
    public function saveWebSettingsDefaultLang($websettingsId, $clubDefaultLanguage)
    {
        $sql = 'UPDATE fg_web_settings T INNER JOIN fg_web_settings_i18n TL ON (T.id = TL.settings_id AND TL.lang = :clubDefaultLanguage AND T.id = :websettingsId) ' .
            'SET T.site_description = TL.description_lang';
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue('websettingsId', $websettingsId);
        $stmt->bindValue('clubDefaultLanguage', $clubDefaultLanguage);
        $stmt->execute();

        return;
    }

    /**
     * This function is used to change the portrait container columns for table column datas
     * 
     * @param int $oldColumnId The old column id
     * @param int $newColumnId The new column id
     * @param int $sortOrder   The sort_order to start incrementing 1
     */
    public function changePortraitContainerColumnForSelectedData($oldColumnId, $newColumnId, $sortOrder)
    {
        $query = "SET @a = $sortOrder; UPDATE `fg_cms_contact_table_columns` CTC SET CTC.`column_id` = $newColumnId, CTC.`sort_order` = @a:=@a+1  WHERE CTC.`column_id` = $oldColumnId  ORDER BY CTC.`sort_order` ASC";
        $this->conn->executeQuery($query);
    }
    
    /**
     * This function is used to delete all table column i18n entries corresponding to deleted table column entries
     * 
     * @param int $tableId The table id
     */
    public function deleteTableColumnI18nEntriesInATable($tableId)
    {
        $query = "DELETE TCI18n FROM `fg_cms_contact_table_columns_i18n` TCI18n WHERE TCI18n.id IN ("
            . "SELECT TC.id FROM `fg_cms_contact_table_columns` TC WHERE TC.table_id=$tableId AND TC.is_deleted = 1)";

        $this->conn->executeQuery($query);
    }
}
