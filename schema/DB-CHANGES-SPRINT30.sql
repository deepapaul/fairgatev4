-- ===================================== FAIR-828 ==================================================--
ALTER TABLE `fg_table_settings` CHANGE `type` `type` ENUM('DATA','SPONSOR','INVOICE','PAIDINVOICE','ARCHIVEDSPONSOR') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

-- ===================================== STORED PROCEDURE TO UPDATE ======================================== --
-- archiveContactsV4
-- handleAttributes
-- V4createClub - add stealthmode and intranetaccess
-- importContacts
-- check sf guard user for already existing contact for heir level entries and insert sf_guard user entry for all users with primary email
-- V4MasterTableChangeFieldTemp
CREATE TRIGGER `lastLoginTrigger` AFTER UPDATE ON `sf_guard_user`
 FOR EACH ROW BEGIN
	UPDATE `fg_cm_contact` 
    SET `last_login`=NEW.`last_login` 
    WHERE NEW.`contact_id`=`id`;
END

--===================================== TO INSERT DEFAULT AD FOR EXISTING CONTACTS   ======================================== --
-- INSERT INTO `fg_sm_sponsor_ads` (`club_id`, `contact_id`, `is_default`, `sort_order`) 
-- SELECT '{clubId}', `contact_id`, '1', '1' FROM `master_club_{clubId}` WHERE `is_sponsor` = 1 AND `contact_id` NOT IN (SELECT `contact_id` FROM `fg_sm_sponsor_ads` WHERE `is_default` = 1)


-- Replace {clubId} with corresponding club id and 'master_club_' with 'master_federation_' for federation
-- =====================================  ======================================== --

ALTER TABLE `fg_cm_change_toconfirm` ADD `club_id` INT(11) NOT NULL , ADD `type` ENUM('change','mutation','creation') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ;
ALTER TABLE `fg_cm_change_log` ADD `confirmed_date` DATETIME NULL DEFAULT NULL ;
ALTER TABLE `fg_cm_change_toconfirm` ADD INDEX(`club_id`);
ALTER TABLE `fg_cm_change_toconfirm` ADD  FOREIGN KEY (`club_id`) REFERENCES `fg_club`(`id`) ON DELETE CASCADE ON UPDATE NO ACTION;
ALTER TABLE `fg_cm_contact_privacy` ADD UNIQUE( `contact_id`, `attribute_id`);

INSERT INTO `sf_guard_group` (`id`, `name`, `description`, `created_at`, `updated_at`, `type`, `sort_order`, `roles`, `module_type`, `is_readonly`) VALUES
(9, 'TeamAdmin', 'TeamAdmin', '2015-07-20 00:00:00', '2015-07-20 00:00:00', 'team', '2', 'a:1:{i:0;s:15:"ROLE_TEAM_ADMIN";}', 'all', '0'),
(10, 'TeamContactAdmin', 'TeamContactAdmin', '2015-07-20 00:00:00', '2015-07-20 00:00:00', 'team', '2', 'a:1:{i:0;s:23:"ROLE_TEAM_CONTACT_ADMIN";}', 'all', '0'),
(11, 'TeamGalleryAdmin', 'TeamGalleryAdmin', '2015-07-20 00:00:00', '2015-07-20 00:00:00', 'team', '2', 'a:1:{i:0;s:23:"ROLE_TEAM_GALLERY_ADMIN";}', 'all', '0'),
(12, 'TeamCmsAdmin', 'TeamCmsAdmin', '2015-07-20 00:00:00', '2015-07-20 00:00:00', 'team', '2', 'a:1:{i:0;s:19:"ROLE_TEAM_CMS_ADMIN";}', 'all', '0'),
(13, 'TeamMessageAdmin', 'TeamMessageAdmin', '2015-07-20 00:00:00', '2015-07-20 00:00:00', 'team', '2', 'a:1:{i:0;s:23:"ROLE_TEAM_MESSAGE_ADMIN";}', 'all', '0'),
(14, 'TeamArticleAdmin', 'TeamArticleAdmin', '2015-07-20 00:00:00', '2015-07-20 00:00:00', 'team', '2', 'a:1:{i:0;s:23:"ROLE_TEAM_ARTICLE_ADMIN";}', 'all', '0'),
(15, 'TeamCalendarAdmin', 'TeamCalendarAdmin', '2015-07-20 00:00:00', '2015-07-20 00:00:00', 'team', '2', 'a:1:{i:0;s:24:"ROLE_TEAM_CALENDAR_ADMIN";}', 'all', '0');
-- ========================================== FAIR-1070 ======================================== --
-- ALTER TABLE `fg_sm_bookings`  ADD `is_stopped` INT(1) NOT NULL DEFAULT '0' ;
ALTER TABLE `fg_sm_bookings`  ADD `is_skipped` INT(1) NOT NULL DEFAULT '0' ;
ALTER TABLE `fg_sm_bookings` CHANGE `is_deleted` `is_deleted` TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE `fg_sm_services_log` CHANGE `action_type` `action_type` ENUM('assigned','changed','stopped','deleted','skipped') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `fg_sm_bookmarks` CHANGE `type` `type` ENUM('service','prospect','future_sponsor','active_sponsor','former_sponsor','single_person','company','active_assignments','former_assignments','future_assignments','recently_ended') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

-- ========================================== FAIR-1101 ======================================== --
ALTER TABLE `fg_club_settings` 
ADD COLUMN `majority_age` INT(2) NOT NULL DEFAULT 18 AFTER `currency_position`,
ADD COLUMN `profile_access_age` INT(2) NOT NULL DEFAULT 16 AFTER `majority_age`;
-- ========================================== FAIR-1088 ======================================== --
ALTER TABLE `fg_cm_contact` 
ADD COLUMN `is_stealth_mode` INT(11) NOT NULL DEFAULT 0 AFTER `import_id`;
ALTER TABLE `fg_cm_contact` CHANGE `community_status` `community_status` ENUM('open','blocked') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'open';

-- =================================FAIR-683, FAIR-1055========================================== --
ALTER TABLE `fg_cm_club_attribute`  ADD `is_required_fedmember_subfed` TINYINT(1) NOT NULL DEFAULT '0'  AFTER `is_confirm_teamadmin`,  
ADD `is_required_fedmember_club` TINYINT(1) NOT NULL DEFAULT '0'  AFTER `is_required_fedmember_subfed`,  
ADD `is_active` TINYINT(1) NOT NULL DEFAULT '1'  AFTER `is_required_fedmember_club`;
ALTER TABLE `fg_cm_attribute`  ADD `is_crucial_system_field` TINYINT(1) NOT NULL DEFAULT '0'  AFTER `is_system_field`;

ALTER TABLE `fg_cm_club_attribute` CHANGE `is_required_fedmember_subfed` `is_required_fedmember_subfed` TINYINT(1) NULL DEFAULT '0', CHANGE `is_required_fedmember_club` `is_required_fedmember_club` TINYINT(1) NULL DEFAULT '0', CHANGE `is_active` `is_active` TINYINT(1) NULL DEFAULT '1';
UPDATE `fg_cm_attribute` SET `is_crucial_system_field`=1 WHERE id IN(9,23,2,1,72,3,515);
UPDATE fg_cm_attribute SET `fed_profile_status` =1, is_visible_subfed=1, is_editable_subfed=1, is_visible_club=1,is_editable_club=1 WHERE is_system_field=1;

--UPDATE fg_cm_club_attribute SET `is_required_fedmember_subfed` =1 WHERE attribute_id IN (SELECT id FROM fg_cm_attribute WHERE is_system_field=1);

UPDATE fg_cm_club_attribute SET `is_active` =1 WHERE attribute_id IN (SELECT id FROM fg_cm_attribute WHERE is_system_field=1);

UPDATE fg_cm_club_attribute SET is_active = 0 WHERE attribute_id IN (SELECT id FROM fg_cm_attribute WHERE is_active = 0 AND is_system_field=0);


-- =================================FAIR-1095========================================== --


CREATE TABLE IF NOT EXISTS `sf_guard_user_team` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT(20) NOT NULL,
  `group_id` BIGINT(20) NOT NULL,
  `role_id` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_sf_guard_user_team_fg_rm_role1` (`role_id` ASC),
  INDEX `fk_sf_guard_user_team_fg_cm_contact1` (`user_id` ASC),
  INDEX `fk_sf_guard_user_team_sf_guard_group1` (`group_id` ASC),
  CONSTRAINT `sf_guard_user_team_ibfk_1`
    FOREIGN KEY (`user_id`)
    REFERENCES `sf_guard_user` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `sf_guard_user_team_ibfk_2`
    FOREIGN KEY (`group_id`)
    REFERENCES `sf_guard_group` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `sf_guard_user_team_ibfk_3`
    FOREIGN KEY (`role_id`)
    REFERENCES `fg_rm_role` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


ALTER TABLE `sf_guard_user` 
CHANGE COLUMN `created_at` `created_at` DATETIME NULL DEFAULT NULL ,
CHANGE COLUMN `updated_at` `updated_at` DATETIME NULL DEFAULT NULL ,
ADD COLUMN `is_team_admin` INT(1) NOT NULL AFTER `is_readonly_admin`,
ADD COLUMN `is_team_section_admin` INT(1) NOT NULL AFTER `is_team_admin`;



-- ===================== DROPPED TABLES WHICH ARE NOT USED FOR NOW ===================== --

-- relations edited for - fg_club :sales_contact, fg_cn_newsletter_content:news_id, fg_cn_newsletter_article_documents:gallery_image_id
-- doubts - fg_cm_contact_emails, fg_cm_change_email, fg_first_contact_type
-- 20 Fg_team_*
-- 1 fg_club_salescontact
-- 2 fg_club_salutation_*
-- 3 fg_am_*
-- 1 fg_clubregistration_code
-- 3 fg_cm_attribute_value_*
-- 1 fg_cm_change_log_old
-- 74 fg_cms_
-- 1 fg_confirm_pending_data
-- 1 fg_collaborative_tools
-- 10 fg_em_*
-- 42 fg_im_*
-- 4 fg_imp_*
-- 1  fg_filter_contacts
-- 3 fg_gm_*
-- 20 fg_mb(exclude fg_mb_modules, fg_mb_club_modules)
-- 3  fg_migration
-- 2  fg_info_*
-- 6  fg_news_*
-- 2  fg_notification
-- 1  fg_rm_collaborative_tools
-- 2  fg_sales_*
-- 1  fg_session
-- 1  fg_settings
-- 1  fg_system_languages
-- 1  fg_table_settings_(exclude fg_table_settings)
-- 1  fg_tasks
-- 1  fg_team_external_links_i18n
-- 8  fg_tm_
-- 4  fg_userguide_*
-- 1  fg_vat
-- 1  sf_combine
-- 1 sf_guard_user_permission
-- 1 sf_guard_forgot_password
-- 1 sf_guard_group_permission
-- 1 sf_guard_remember_key
-- 1 sf_guard_permission

-- ================================ DELETED TABLES, RELATED FOREIGN KEYS ================ --

ALTER TABLE `fg_club` 
DROP FOREIGN KEY `fg_club_ibfk_25`;

ALTER TABLE `fg_cn_newsletter_article_media` 
DROP FOREIGN KEY `fg_cn_newsletter_article_media_ibfk_1`;

ALTER TABLE `fg_cn_newsletter_content` 
DROP FOREIGN KEY `fg_cn_newsletter_content_ibfk_1`;

ALTER TABLE `fg_club` 
DROP COLUMN `sales_state_id`,
DROP INDEX `sales_state_id` ;

ALTER TABLE `fg_cn_newsletter_article_media` 
DROP INDEX `gallery_item_id` ;

ALTER TABLE `fg_cn_newsletter_content` 
DROP INDEX `fk_fg_cn_newsletter_content_fg_cms_news1` ;

DROP TABLE IF EXISTS `fg_security_admin` ;


DROP TABLE IF EXISTS `sf_guard_user_permission` ;

DROP TABLE IF EXISTS `sf_guard_remember_key` ;

DROP TABLE IF EXISTS `sf_guard_group_permission` ;

DROP TABLE IF EXISTS `sf_guard_forgot_password` ;

DROP TABLE IF EXISTS `sf_combine` ;

DROP TABLE IF EXISTS `fg_vat` ;

DROP TABLE IF EXISTS `fg_userguide_pages_i18n` ;

DROP TABLE IF EXISTS `fg_userguide_pages` ;

DROP TABLE IF EXISTS `fg_userguide_pagelinks` ;

DROP TABLE IF EXISTS `fg_userguide_modules` ;

DROP TABLE IF EXISTS `fg_tm_theme_settings_sliding_headerimage` ;

DROP TABLE IF EXISTS `fg_tm_theme_settings_color` ;

DROP TABLE IF EXISTS `fg_tm_theme_settings_bg` ;

DROP TABLE IF EXISTS `fg_tm_theme_settings` ;

DROP TABLE IF EXISTS `fg_tm_theme_bg` ;

DROP TABLE IF EXISTS `fg_tm_theme` ;

DROP TABLE IF EXISTS `fg_tm_selection` ;

DROP TABLE IF EXISTS `fg_tm_club_theme` ;

DROP TABLE IF EXISTS `fg_team_widgets_text_i18n` ;

DROP TABLE IF EXISTS `fg_team_widgets_text` ;

DROP TABLE IF EXISTS `fg_team_widgets_calendar_i18n` ;

DROP TABLE IF EXISTS `fg_team_widgets_calendar` ;

DROP TABLE IF EXISTS `fg_team_widgets_article_i18n` ;

DROP TABLE IF EXISTS `fg_team_widgets_article_categories` ;

DROP TABLE IF EXISTS `fg_team_widgets_article` ;

DROP TABLE IF EXISTS `fg_team_widgets` ;

DROP TABLE IF EXISTS `fg_team_widget_calendar_category` ;

DROP TABLE IF EXISTS `fg_team_roster_template_feilds` ;

DROP TABLE IF EXISTS `fg_team_roster_template` ;

DROP TABLE IF EXISTS `fg_team_roster_category_i18n` ;

DROP TABLE IF EXISTS `fg_team_roster_category_contacts` ;

DROP TABLE IF EXISTS `fg_team_roster_category` ;

DROP TABLE IF EXISTS `fg_team_image_caption` ;

DROP TABLE IF EXISTS `fg_team_external_links_i18n` ;

DROP TABLE IF EXISTS `fg_team_external_links` ;

DROP TABLE IF EXISTS `fg_team_details_i18n` ;

DROP TABLE IF EXISTS `fg_team_details` ;

DROP TABLE IF EXISTS `fg_tasks` ;

DROP TABLE IF EXISTS `fg_table_settings_roles` ;

DROP TABLE IF EXISTS `fg_table_settings_items` ;

DROP TABLE IF EXISTS `fg_table_settings_columns` ;

DROP TABLE IF EXISTS `fg_table_settings_accounts` ;

DROP TABLE IF EXISTS `fg_system_languages` ;

DROP TABLE IF EXISTS `fg_settings` ;

DROP TABLE IF EXISTS `fg_sessions` ;

DROP TABLE IF EXISTS `fg_sale_states_history` ;

DROP TABLE IF EXISTS `fg_sale_states` ;

DROP TABLE IF EXISTS `fg_rm_collaborative_tools` ;

DROP TABLE IF EXISTS `fg_notification_contacts` ;

DROP TABLE IF EXISTS `fg_notification` ;

DROP TABLE IF EXISTS `fg_news_receivers` ;

DROP TABLE IF EXISTS `fg_news_read` ;

DROP TABLE IF EXISTS `fg_news_languages` ;

DROP TABLE IF EXISTS `fg_news_filter` ;

DROP TABLE IF EXISTS `fg_news_documents` ;

DROP TABLE IF EXISTS `fg_news` ;

DROP TABLE IF EXISTS `fg_migration_inv_address` ;

DROP TABLE IF EXISTS `fg_migration_contacts_25000_data` ;

DROP TABLE IF EXISTS `fg_migration_activities` ;

DROP TABLE IF EXISTS `fg_mb_purchase_log` ;

DROP TABLE IF EXISTS `fg_mb_packages_info_i18n` ;

DROP TABLE IF EXISTS `fg_mb_packages_info` ;

DROP TABLE IF EXISTS `fg_mb_packages_i18n` ;

DROP TABLE IF EXISTS `fg_mb_packages_federation` ;

DROP TABLE IF EXISTS `fg_mb_packages` ;

DROP TABLE IF EXISTS `fg_mb_package_individual_price` ;

DROP TABLE IF EXISTS `fg_mb_module_info_i18n` ;

DROP TABLE IF EXISTS `fg_mb_module_info` ;

DROP TABLE IF EXISTS `fg_mb_module_individual_price` ;

DROP TABLE IF EXISTS `fg_mb_module_i18n` ;

DROP TABLE IF EXISTS `fg_mb_module_federation` ;

DROP TABLE IF EXISTS `fg_mb_module_discount_info` ;

DROP TABLE IF EXISTS `fg_mb_diskspace_packages` ;

DROP TABLE IF EXISTS `fg_mb_diskspace_federation_i18n` ;

DROP TABLE IF EXISTS `fg_mb_diskspace_federation` ;

DROP TABLE IF EXISTS `fg_mb_club_plan` ;

DROP TABLE IF EXISTS `fg_mb_club_adfree` ;

DROP TABLE IF EXISTS `fg_mb_adfree_info_i18n` ;

DROP TABLE IF EXISTS `fg_mb_adfree_info` ;

DROP TABLE IF EXISTS `fg_info_i18n` ;

DROP TABLE IF EXISTS `fg_info` ;

DROP TABLE IF EXISTS `fg_imp_club_messages_receivers` ;

DROP TABLE IF EXISTS `fg_imp_club_messages_read` ;

DROP TABLE IF EXISTS `fg_imp_club_messages_documents` ;

DROP TABLE IF EXISTS `fg_imp_club_messages` ;

DROP TABLE IF EXISTS `fg_im_transaction_description` ;

DROP TABLE IF EXISTS `fg_im_template` ;

DROP TABLE IF EXISTS `fg_im_tax` ;

DROP TABLE IF EXISTS `fg_im_sponsor_invoice_settings` ;

DROP TABLE IF EXISTS `fg_im_settings` ;

DROP TABLE IF EXISTS `fg_im_paymentslip_margin` ;

DROP TABLE IF EXISTS `fg_im_module_item_ind_price` ;

DROP TABLE IF EXISTS `fg_im_module_invoice_settings` ;

DROP TABLE IF EXISTS `fg_im_mail_info` ;

DROP TABLE IF EXISTS `fg_im_mail_delivery` ;

DROP TABLE IF EXISTS `fg_im_journal_export` ;

DROP TABLE IF EXISTS `fg_im_journal` ;

DROP TABLE IF EXISTS `fg_im_item` ;

DROP TABLE IF EXISTS `fg_im_invoice_template` ;

DROP TABLE IF EXISTS `fg_im_invoice_run_log` ;

DROP TABLE IF EXISTS `fg_im_invoice_run_iteration` ;

DROP TABLE IF EXISTS `fg_im_invoice_run_item` ;

DROP TABLE IF EXISTS `fg_im_invoice_run_contacts` ;

DROP TABLE IF EXISTS `fg_im_invoice_run` ;

DROP TABLE IF EXISTS `fg_im_invoice_payment` ;

DROP TABLE IF EXISTS `fg_im_invoice_log` ;

DROP TABLE IF EXISTS `fg_im_invoice_item_payment` ;

DROP TABLE IF EXISTS `fg_im_invoice_item` ;

DROP TABLE IF EXISTS `fg_im_invoice_dun` ;

DROP TABLE IF EXISTS `fg_im_invoice_contacts` ;

DROP TABLE IF EXISTS `fg_im_invoice_clients` ;

DROP TABLE IF EXISTS `fg_im_invoice_client_payment` ;

DROP TABLE IF EXISTS `fg_im_invoice_client_item_payment` ;

DROP TABLE IF EXISTS `fg_im_invoice_client_item` ;

DROP TABLE IF EXISTS `fg_im_invoice_cancellation` ;

DROP TABLE IF EXISTS `fg_im_invoice_accounts` ;

DROP TABLE IF EXISTS `fg_im_invoice` ;

DROP TABLE IF EXISTS `fg_im_intro_pattern` ;

DROP TABLE IF EXISTS `fg_im_fiscalyear` ;

DROP TABLE IF EXISTS `fg_im_financial_institution` ;

DROP TABLE IF EXISTS `fg_im_creditnote` ;

DROP TABLE IF EXISTS `fg_im_bankinterface_report` ;

DROP TABLE IF EXISTS `fg_im_bankinterface` ;

DROP TABLE IF EXISTS `fg_im_bank_report_clients` ;

DROP TABLE IF EXISTS `fg_im_address_position` ;

DROP TABLE IF EXISTS `fg_im_accouting` ;

DROP TABLE IF EXISTS `fg_im_account` ;

DROP TABLE IF EXISTS `fg_gm_images` ;

DROP TABLE IF EXISTS `fg_gm_gallery_settings` ;

DROP TABLE IF EXISTS `fg_gm_album` ;

DROP TABLE IF EXISTS `fg_filter_contacts` ;

DROP TABLE IF EXISTS `fg_em_calender_settings` ;

DROP TABLE IF EXISTS `fg_em_calendar_settings_data_i18n` ;

DROP TABLE IF EXISTS `fg_em_calendar_settings_data` ;

DROP TABLE IF EXISTS `fg_em_calendar_selected` ;

DROP TABLE IF EXISTS `fg_em_calendar_i18n` ;

DROP TABLE IF EXISTS `fg_em_calendar_filter_type` ;

DROP TABLE IF EXISTS `fg_em_calendar_filter_category` ;

DROP TABLE IF EXISTS `fg_em_calendar_category_i18n` ;

DROP TABLE IF EXISTS `fg_em_calendar_category` ;

DROP TABLE IF EXISTS `fg_em_calendar` ;

DROP TABLE IF EXISTS `fg_confirm_pending_data` ;

DROP TABLE IF EXISTS `fg_collaborative_tools` ;

DROP TABLE IF EXISTS `fg_cms_widgets_type` ;

DROP TABLE IF EXISTS `fg_cms_widgets_link_image` ;

DROP TABLE IF EXISTS `fg_cms_widgets_i18n` ;

DROP TABLE IF EXISTS `fg_cms_widgets_has_sm_media_label` ;

DROP TABLE IF EXISTS `fg_cms_widgets_has_rm_role` ;

DROP TABLE IF EXISTS `fg_cms_widgets_has_gm_album` ;

DROP TABLE IF EXISTS `fg_cms_widgets_has_cms_news_category` ;

DROP TABLE IF EXISTS `fg_cms_widgets_calendar_category` ;

DROP TABLE IF EXISTS `fg_cms_widgets_calendar` ;

DROP TABLE IF EXISTS `fg_cms_widgets_area` ;

DROP TABLE IF EXISTS `fg_cms_widgets` ;

DROP TABLE IF EXISTS `fg_cms_text_content_i18n` ;

DROP TABLE IF EXISTS `fg_cms_text_content` ;

DROP TABLE IF EXISTS `fg_cms_team_link_details_settings` ;

DROP TABLE IF EXISTS `fg_cms_team_link_details_rights` ;

DROP TABLE IF EXISTS `fg_cms_team_link_details` ;

DROP TABLE IF EXISTS `fg_cms_standard_content_media_i18n` ;

DROP TABLE IF EXISTS `fg_cms_standard_content_media` ;

DROP TABLE IF EXISTS `fg_cms_standard_content` ;

DROP TABLE IF EXISTS `fg_cms_sponsorlist_services` ;

DROP TABLE IF EXISTS `fg_cms_sponsorlist_i18n` ;

DROP TABLE IF EXISTS `fg_cms_sponsorlist_contact` ;

DROP TABLE IF EXISTS `fg_cms_sponsorlist_attribute` ;

DROP TABLE IF EXISTS `fg_cms_sponsorlist` ;

DROP TABLE IF EXISTS `fg_cms_sbo_text_i18n` ;

DROP TABLE IF EXISTS `fg_cms_sbo_text` ;

DROP TABLE IF EXISTS `fg_cms_sbo_map_contacts` ;

DROP TABLE IF EXISTS `fg_cms_sbo_filter_contacts` ;

DROP TABLE IF EXISTS `fg_cms_sbo_filter` ;

DROP TABLE IF EXISTS `fg_cms_news_settings` ;

DROP TABLE IF EXISTS `fg_cms_news_media_i18n` ;

DROP TABLE IF EXISTS `fg_cms_news_media` ;

DROP TABLE IF EXISTS `fg_cms_news_i18n` ;

DROP TABLE IF EXISTS `fg_cms_news_editor_rights` ;

DROP TABLE IF EXISTS `fg_cms_news_comments` ;

DROP TABLE IF EXISTS `fg_cms_news_category_i18n` ;

DROP TABLE IF EXISTS `fg_cms_news_category` ;

DROP TABLE IF EXISTS `fg_cms_news` ;

DROP TABLE IF EXISTS `fg_cms_membershipform_i18n` ;

DROP TABLE IF EXISTS `fg_cms_membershipform_fields` ;

DROP TABLE IF EXISTS `fg_cms_membershipform` ;

DROP TABLE IF EXISTS `fg_cms_map_i18n` ;

DROP TABLE IF EXISTS `fg_cms_map` ;

DROP TABLE IF EXISTS `fg_cms_link_details_i18n` ;

DROP TABLE IF EXISTS `fg_cms_link_details` ;

DROP TABLE IF EXISTS `fg_cms_link_category` ;

DROP TABLE IF EXISTS `fg_cms_form_request_data` ;

DROP TABLE IF EXISTS `fg_cms_form_request` ;

DROP TABLE IF EXISTS `fg_cms_form_receivers_i18n` ;

DROP TABLE IF EXISTS `fg_cms_form_receivers` ;

DROP TABLE IF EXISTS `fg_cms_form_i18n` ;

DROP TABLE IF EXISTS `fg_cms_form_fields_i18n` ;

DROP TABLE IF EXISTS `fg_cms_form_fields` ;

DROP TABLE IF EXISTS `fg_cms_form_confirmation_message_i18n` ;

DROP TABLE IF EXISTS `fg_cms_form_confirmation_message` ;

DROP TABLE IF EXISTS `fg_cms_form` ;

DROP TABLE IF EXISTS `fg_cms_documents` ;

DROP TABLE IF EXISTS `fg_cms_document_content_i18n` ;

DROP TABLE IF EXISTS `fg_cms_document_content` ;

DROP TABLE IF EXISTS `fg_cms_content_userrights` ;

DROP TABLE IF EXISTS `fg_cms_content_section_type` ;

DROP TABLE IF EXISTS `fg_cms_content_section_news_category` ;

DROP TABLE IF EXISTS `fg_cms_content_section_membershipform` ;

DROP TABLE IF EXISTS `fg_cms_content_section_memberlist_i18n` ;

DROP TABLE IF EXISTS `fg_cms_content_section_memberlist` ;

DROP TABLE IF EXISTS `fg_cms_content_section_i18n` ;

DROP TABLE IF EXISTS `fg_cms_content_section` ;

DROP TABLE IF EXISTS `fg_cms_contact_list_selection` ;

DROP TABLE IF EXISTS `fg_cms_contact_list_i18n` ;

DROP TABLE IF EXISTS `fg_cms_contact_list` ;

DROP TABLE IF EXISTS `fg_cms_contact_dp_roles` ;

DROP TABLE IF EXISTS `fg_cms_contact_dp_category` ;

DROP TABLE IF EXISTS `fg_cms_contact_dp_attribute` ;

DROP TABLE IF EXISTS `fg_cms_category_news` ;

DROP TABLE IF EXISTS `fg_cm_change_log_old` ;

DROP TABLE IF EXISTS `fg_cm_attribute_value_text` ;

DROP TABLE IF EXISTS `fg_cm_attribute_value_int` ;

DROP TABLE IF EXISTS `fg_cm_attribute_value_date` ;

DROP TABLE IF EXISTS `fg_clubregistration_code` ;

DROP TABLE IF EXISTS `fg_club_salutation_i18n` ;

DROP TABLE IF EXISTS `fg_club_salutation` ;

DROP TABLE IF EXISTS `fg_club_salescontact` ;

DROP TABLE IF EXISTS `fg_am_ads_type` ;

DROP TABLE IF EXISTS `fg_am_ads_filter` ;

DROP TABLE IF EXISTS `fg_am_ads` ;


DROP TABLE IF EXISTS `sf_guard_permission` ;
