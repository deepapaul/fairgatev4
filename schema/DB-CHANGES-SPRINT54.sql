-- Added columns for stage4 data
ALTER TABLE `fg_cms_contact_table`
   	ADD `row_perpage` INT NULL AFTER `filter_data`,
    ADD `overflow_behavior` ENUM('horizontal','toggle') NULL AFTER `row_perpage`,
    ADD `row_highlighting` BOOLEAN NULL AFTER `overflow_behavior`,
    ADD `table_search` BOOLEAN NULL AFTER `row_highlighting`,
    ADD `table_export` ENUM('none','all','loggedin') NULL AFTER `table_search`;
ALTER TABLE `fg_cms_contact_table` DROP `filter_data`;

-- Added new enum type
ALTER TABLE `fg_cms_contact_table_filter` CHANGE `filter_type` `filter_type` ENUM('contact_field','memberships','fed_memberships','filter_roles','workgroups','team_category','role_category','fed_role_category','subfed_role_category') NOT NULL;

ALTER TABLE `fg_cms_contact_table` CHANGE `column_data` `column_data` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;
*********************************************** contact table element *********************************************************************************

ALTER TABLE `fg_cms_contact_table_columns` CHANGE `column_type` `column_type` ENUM('contact_name','contact_field','membership_info','fed_membership_info','analysis_field','team_assignments','team_functions','workgroup_assignments','workgroup_functions','role_category_assignments','common_role_functions','individual_role_functions','filter_role_assignments','fed_role_category_assignments','common_fed_role_functions','individual_fed_role_functions','sub_fed_role_category_assignments','common_sub_fed_role_functions','individual_sub_fed_role_functions','federation_info') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;

ALTER TABLE `fg_cms_contact_table_columns` CHANGE `column_subtype` `column_subtype` ENUM('membership','member_years','age','birth_year','clubs','sub_federations','clubs_executive_board_functions') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;

*********************************************** contact table element *********************************************************************************
------------------------------FAIR-2681-------------------------------------- 
ALTER TABLE `fg_cn_newsletter_content` ADD `article_lang` VARCHAR(4) NULL AFTER `article_id`;

------------------------------FAIR-2506-------------------------------------- 
Drop table`archivecontactsto`;
-------------------------------------------------------------------------------------------
update in 
gallery_migrate.sh and resize_gallery_server.sh

update in procedure `archiveContactsV4`;

-- KANBAN TICKETS DB CHANGES --
--FAIR-2598--
DELETE FROM `fg_club_terminology` WHERE `default_singular_term` LIKE 'Oganizer'
--FAIR-2645--
ALTER TABLE `fg_cms_page_content_element` CHANGE `image_element_click_type` `image_element_click_type` ENUM('detail','link','none') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL

