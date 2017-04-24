-- Table for api
CREATE TABLE `fg_api_columns` (
 `id` int(11) NOT NULL,
 `field_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `fg_api_columns`

INSERT INTO `fg_api_columns` (`id`, `field_name`) VALUES
(1, 'Vorname'),
(2, 'Nachname'),
(3, 'Geburtsdatum'),
(4, 'Postfach'),
(5, 'Geschlecht'),
(6, 'EMail'),
(7, 'Strasse'),
(8, 'PLZ'),
(9, 'Ort'),
(10, 'Telefon'),
(11, 'Natel'),
(12, 'Magazin'),
(13, 'Eintrittsdatum'),
(14, 'ExecutiveBoardFunctions'),
(15, 'PrimaereSportart');
================================================================ API ======================================================================
ALTER TABLE `fg_api_columns` ADD PRIMARY KEY (`id`);
ALTER TABLE `fg_api_columns` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
ALTER TABLE `sf_guard_user` ADD `auth_code` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL AFTER `has_full_permission`;
============================================================================================================================================


================================================================ NEWSLETTER SUBSCRIPTION n NEWSLETTER ARCHIVE ======================================================================

INSERT INTO `fg_cms_page_content_type` (`id`, `type`, `logo_name`, `label`, `table_name`, `sort_order`) VALUES (NULL, 'newsletter-subscription', 'fa-at', 'CMS_NEWSLETTER_SUBSCRIPTION', NULL, '14'), (NULL, 'newsletter-archive', 'fa-archive', 'CMS_NEWSLETTER_ARCHIVE', NULL, '15');


ALTER TABLE `fg_cn_subscriber` ADD `correspondance_lang` CHAR(2) NOT NULL AFTER `salutation`;
ALTER TABLE `fg_cn_subscriber` ADD `lang_updated` INT(1) NOT NULL DEFAULT '0' AFTER `import_id`;

-- change db check connection and run script
===================== IMPORTANT ====== 
REMOVE LIMIT IN development_s3/web/migration/subscriberUpdates.php BEFORE RUNNING BELOW SCRIPT

http://localhost:8080/migration/migratecontact.php

-- update archive contacts  v4 procedure.
archiveContactsV4
insertNewsletterContactsToSpoolV4

-- add new table fg_pending_applications
CREATE TABLE `fg_pending_applications` (
  `id` int(11) NOT NULL,
  `club_id` int(11) NOT NULL,
  `unique_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `json_data` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL,
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `fg_pending_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `club_id` (`club_id`);

ALTER TABLE `fg_pending_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `fg_pending_applications`
  ADD CONSTRAINT `fg_pending_applications_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `fg_cm_contact` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  ADD CONSTRAINT `fg_pending_applications_ibfk_2` FOREIGN KEY (`club_id`) REFERENCES `fg_club` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

================================================================ NEWSLETTER SUBSCRIPTION n NEWSLETTER ARCHIVE =============================================================================



============================================================================================================================================
-- Insert api admin User Role

============================================================================================================================================
ALTER TABLE `sf_guard_group` CHANGE `module_type` `module_type` ENUM('contact','communication','sponsor','invoice','events','all','gallery','cms','message','article','calendar','forum','document','api') 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;

INSERT INTO `sf_guard_group` (`id`, `name`, `description`, `created_at`, `updated_at`, `type`, `sort_order`, `roles`, `module_type`, `is_readonly`) VALUES
(23, 'Api admin', 'Api admin', '2016-11-29 00:00:00', '2016-11-29 00:00:00', 'fairgate', 2, 'a:1:{i:0;s:8:"ROLE_API";}', 'api', 0);


============================================================================================================================================
