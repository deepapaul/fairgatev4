CREATE TABLE `fg_club_settings_i18n` (
  `settings_id` int(11) NOT NULL,
  `signature_lang` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `logo_lang` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lang` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `fg_club_settings_i18n`
--
ALTER TABLE `fg_club_settings_i18n`
  ADD PRIMARY KEY (`settings_id`,`lang`),
  ADD KEY `settings_id` (`settings_id`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `fg_club_settings_i18n`
--
ALTER TABLE `fg_club_settings_i18n`
  ADD CONSTRAINT `fg_club_settings_i18n_ibfk_1` FOREIGN KEY (`settings_id`) REFERENCES `fg_club_settings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
######################################################################################################################################################

ALTER TABLE `fg_cms_contact_table` 
ADD COLUMN `display_type` ENUM('table','portrait') NULL DEFAULT 'table' AFTER `is_deleted`,
ADD COLUMN `portrait_per_row` TINYINT(2) NULL DEFAULT NULL AFTER `display_type`,
ADD COLUMN `initial_sorting_details` VARCHAR(255) NULL DEFAULT NULL AFTER `portrait_per_row`,
ADD COLUMN `initial_sort_order` VARCHAR(100) NULL DEFAULT NULL AFTER `initial_sorting_details`;



ALTER TABLE `fg_cms_contact_table_columns` 
ADD COLUMN `field_display_type` ENUM('icon','value','icon_and_value','linked_label','icon_with_popover','image') NULL DEFAULT NULL AFTER `is_deleted`,
ADD COLUMN `line_break_before` TINYINT(2) NULL DEFAULT NULL AFTER `field_display_type`,
ADD COLUMN `empty_value_display` ENUM('skip_line','em_dash','n/v') NULL DEFAULT NULL AFTER `line_break_before`,
ADD COLUMN `separate_listing` ENUM('0','1') NULL DEFAULT NULL AFTER `empty_value_display`,
ADD COLUMN `column_id` INT(11) NULL DEFAULT NULL AFTER `separate_listing`,
ADD COLUMN `profile_image` VARCHAR(250) NULL DEFAULT NULL AFTER `column_id`;

ALTER TABLE `fg_cms_contact_table_columns` CHANGE `column_type` `column_type` ENUM('contact_name','contact_field','membership_info','fed_membership_info','analysis_field','team_assignments','team_functions','workgroup_assignments','workgroup_functions','role_category_assignments','common_role_functions','individual_role_functions','filter_role_assignments','fed_role_category_assignments','common_fed_role_functions','individual_fed_role_functions','sub_fed_role_category_assignments','common_sub_fed_role_functions','individual_sub_fed_role_functions','federation_info','profile_pic') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;

-- made title nullable for portrait
ALTER TABLE `fg_cms_contact_table_columns` CHANGE `title` `title` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;



CREATE TABLE `fg_cms_portrait_container` (
  `id` int(11) NOT NULL,
  `portrait_id` int(11) NOT NULL,
  `size` int(6) NOT NULL,
  `sort_order` int(6) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `fg_cms_portrait_container`
--
ALTER TABLE `fg_cms_portrait_container`
  ADD PRIMARY KEY (`id`),
  ADD KEY `portrait_id` (`portrait_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `fg_cms_portrait_container`
--
ALTER TABLE `fg_cms_portrait_container`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `fg_cms_portrait_container`
--
ALTER TABLE `fg_cms_portrait_container`
  ADD CONSTRAINT `fg_cms_portrait_container_ibfk_1` FOREIGN KEY (`portrait_id`) REFERENCES `fg_cms_contact_table` (`id`);





--
-- Table structure for table `fg_cms_portrait_container_column`
--

CREATE TABLE `fg_cms_portrait_container_column` (
  `id` int(11) NOT NULL,
  `container_id` int(11) NOT NULL,
  `size` int(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `fg_cms_portrait_container_column`
--
ALTER TABLE `fg_cms_portrait_container_column`
  ADD PRIMARY KEY (`id`),
  ADD KEY `container_id` (`container_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `fg_cms_portrait_container_column`
--
ALTER TABLE `fg_cms_portrait_container_column`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `fg_cms_portrait_container_column`
--
ALTER TABLE `fg_cms_portrait_container_column`
ADD CONSTRAINT `fg_cms_portrait_container_column_ibfk_1` FOREIGN KEY (`container_id`) REFERENCES `fg_cms_portrait_container` (`id`);

 ALTER TABLE `fg_cms_contact_table_columns` ADD INDEX(`column_id`);

 ALTER TABLE `fg_cms_contact_table_columns` ADD FOREIGN KEY (`column_id`) REFERENCES `fg_cms_portrait_container_column`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `fg_cms_contact_table_columns` ADD CONSTRAINT `fg_cms_contact_table_columns_ibfk_5` FOREIGN KEY (`column_id`) REFERENCES `fg_cms_portrait_container_column`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
  
ALTER TABLE `fg_cms_portrait_container_column` DROP FOREIGN KEY `fg_cms_portrait_container_column_ibfk_1`; 
ALTER TABLE `fg_cms_portrait_container_column` ADD CONSTRAINT `fg_cms_portrait_container_column_ibfk_1` FOREIGN KEY (`container_id`) REFERENCES `fg_cms_portrait_container`(`id`) ON DELETE CASCADE ON UPDATE NO ACTION;  


ALTER TABLE `fg_cms_portrait_container_column` ADD `sort_order` INT(6) NOT NULL DEFAULT '1' AFTER `size`;


################################################################### CHANGE#########################################
ALTER TABLE `fg_cms_contact_table_columns` CHANGE `line_break_before` `line_break_before` INT(2) NULL DEFAULT NULL;



------------------------------------------------------------------------------------------------------------------------------------
--- FAIRDEV-57 Task 39782:Performance tests with the API, with RibCosinus and Password update for RibCosinus (20 long) -------------

UPDATE `sf_guard_user` SET `password` = '5tHipmP/JrO4VY3wWtAAO1INDXcJ5s5NRNbBnJ3qnpL7o53n5bTqZleZbEazCea6VkG8z5qfC/qlB/qm0tZaXg==', `auth_code`  = 'aDdwMERBQTZtTEkxYWRGWWZXY3NBd1VTRmlZcXNlRFl6WVJKU0JybTBKS1oySG91S3FkMUM1YlcrV0JIY01EcGh0MjNObDRRZGxXaXdMY3k1WTBmN0E9PQ==' WHERE `username` = 'ribcosinus'
INSERT INTO `fg_api_columns` (`id`, `field_name`) VALUES (NULL, 'SATUSMitgliedschaften');
INSERT INTO `fg_api_columns` (`id`, `field_name`) VALUES (NULL, 'ExpirationStatus');
INSERT INTO `fg_api_columns` (`id`, `field_name`) VALUES (NULL, 'KontaktID');
INSERT INTO `fg_api_columns` (`id`, `field_name`) VALUES (NULL,'contactId'); -- FAIRDEV - 203
------------------------------------------------------------------------------------------------------------------------------------

------------------------------------------ FAIRDEV-29 --------------------------------------------------------------------
-- Updated stored procedure V4createClub
-- Migrate Club title --
-- Migrate Club title --
INSERT INTO fg_club_i18n
SELECT C.id AS id, C.title AS title, COALESCE(CL.correspondance_lang, 'de') AS lang, 1 AS active FROM fg_club  C
LEFT JOIN fg_club_language CL ON CL.club_id = C.id
LEFT JOIN fg_club_language_settings  CLS ON CLS.club_language_id = CL.id
WHERE (CLS.is_active = 1 OR CLS.is_active IS NULL)
GROUP BY C.id
ORDER BY CLS.sort_order ASC
ON DUPLICATE KEY UPDATE title_lang=VALUES(title_lang);

-- Migrate signature & logo --
INSERT INTO fg_club_settings_i18n
SELECT C.id AS id, C.signature AS signature, C.logo AS logo,COALESCE(CL.correspondance_lang, 'de') AS lang FROM fg_club_settings  C
LEFT JOIN fg_club_language CL ON CL.club_id = C.club_id
LEFT JOIN fg_club_language_settings  CLS ON CLS.club_language_id = CL.id
WHERE (CLS.is_active = 1 OR CLS.is_active IS NULL)
GROUP BY C.club_id
ORDER BY CLS.sort_order ASC
ON DUPLICATE KEY UPDATE signature_lang=VALUES(signature_lang),logo_lang=VALUES(logo_lang);
----------------------------------------------------------------------------------------------------------------------------------
ALTER TABLE `fg_club_settings_i18n` CHANGE `signature_lang` `signature_lang` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;
ALTER TABLE `fg_club_settings_i18n` CHANGE `logo_lang` `logo_lang` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;
----------------------------------------------------------------------------------------------------------------------------------

##################################################################################################################################################
  
ALTER TABLE `fg_dm_documents` ADD `is_publish_link` INT(1) NOT NULL DEFAULT '0' AFTER `function_sel`;
====================== Document Changes ==================================================

ALTER TABLE `fg_dm_document_log` CHANGE `kind` `kind` ENUM('data','deposited_with','visible_for','visible_for_contact','included','excluded','filter','ispublic') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;
 ############################################################################################

added a new yml file  for the  document settings 

           doc_param.yml

===================Document Changes ==================================================

##################### ADD PORTRAIT ELEMENT TO fg_cms_page_content_type#######################################################################

INSERT INTO `fg_cms_page_content_type` (`id`, `type`, `logo_name`, `label`, `table_name`, `sort_order`) VALUES (17, 'portrait-element','fa-user-circle-o', 'CMS_PORTRAIT-ELEMENT', '', '17');

================================================= Club setting change ===================================================================
ALTER TABLE `fg_club_settings_i18n` CHANGE `settings_id` `id` INT(11) NOT NULL;

