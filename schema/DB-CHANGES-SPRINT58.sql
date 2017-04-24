--new fields for theme2 header
ALTER TABLE `fg_tm_theme_configuration` ADD `header_position` ENUM('full_width','content_area_width','banner_right_aligned','banner_left_aligned') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'theme2 set header position' AFTER `css_filename`;

ALTER TABLE `fg_tm_theme_configuration` ADD `header_logo_position` ENUM('left','center','right') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'theme2 set header logo position' AFTER `header_position`;

UPDATE `fg_tm_theme` SET `is_active` = '1' WHERE `fg_tm_theme`.`id` = 2;

========================================================================== THEME CHANGES =========================================================================================
INSERT INTO `fg_tm_theme_color_scheme` (`id`, `theme_id`, `color_schemes`, `css_filename`, `is_default`, `club_id`) VALUES (NULL, '2', '{"colorPreviewImage":"color-scheme1.jpg",
"TM_BACKGROUND_COLOR":" rgba(60, 113, 191, 1)",
"TM_NAV_BACKGROUND_COLOR":"rgba(33, 43, 64, 0.65)",
"TM_NAV_TEXT_COLOR":"rgba(255, 255, 255, 1)",
"TM_ACTIVE_MENU_COLOR":"rgba(44, 62, 80, 1)",
"TM_BOX_BACKGROUND_COLOR":"rgba(33, 43, 64, 0.5)",
"TM_MAIN_TEXT_COLOR":" rgba(255, 255, 255, 1)",
"TM_LINK_BUTTON_COLOR":"rgba(247, 147, 30, 1)",
"TM_PAGE_TITLE_TEXT_COLOR":"rgba(255, 255, 255, 1)",
"TM_PAGE_TITLE_SHADOW_COLOR":"rgba(0, 0, 0, 0.75)",
"TM_ACCENT_COLOR":"(0, 146, 69, 1.0)"
}', NULL, '1', '1');

UPDATE `fg_tm_theme` SET `theme_options` = '{"themePreviewImage":"theme2.jpg","noOfColorsPerScheme":10,"noOfPresetSchemes":3,"colorLabels":["TM_BACKGROUND_COLOR","TM_NAV_BACKGROUND_COLOR","TM_NAV_TEXT_COLOR","TM_ACTIVE_MENU_COLOR","TM_BOX_BACKGROUND_COLOR","TM_MAIN_TEXT_COLOR","TM_LINK_BUTTON_COLOR","TM_PAGE_TITLE_TEXT_COLOR","TM_PAGE_TITLE_SHADOW_COLOR","TM_ACCENT_COLOR"],"noOfHeaderImages":2,"headerImageLabels":["TM_HEADER_LOGO","TM_DEFAULT_LOGO","TM_MOBILE_SCREEN_LOGO"],"noOfFonts":3,"font_label":{"TM_MAIN_TEXT":{"font_name":"Roboto","font_strength":"normal","is_italic":0,"is_uppercase":0},"TM_HEADINGS":{"font_name":"Montserrat","font_strength":"bold","is_italic":0,"is_uppercase":0},"TM_NAVIGATION":{"font_name":"Montserrat","font_strength":"bold","is_italic":0,"is_uppercase":1}}}' WHERE `fg_tm_theme`.`id` = 2;
=================================================================================================================================================================================================================================

CREATE TABLE `fg_apis` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `identifier` enum('satus','gotcourt') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'satus'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `fg_apis`
--
ALTER TABLE `fg_apis`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `fg_apis`
--
ALTER TABLE `fg_apis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `fg_api_columns` ADD `api_type` INT(11) NOT NULL AFTER `field_name`, ADD INDEX (`api_type`);  

INSERT INTO `fg_apis` (`id`, `name`, `identifier`) VALUES (NULL, 'Satus', 'satus'), (NULL, 'Got Court', 'gotcourt');
	
UPDATE `fg_api_columns` SET api_type = 1; 

ALTER TABLE `fg_api_columns` ADD FOREIGN KEY (`api_type`) REFERENCES `fairgate_migrate`.`fg_apis`(`id`) ON DELETE CASCADE ON UPDATE NO ACTION;
================================================================================================================================================================================================================================

-- Updated stored procedure V4createClub

-- Migration script for default theme configuration 2 for all existing clubs

INSERT INTO `fg_tm_theme_configuration` (`club_id`, `theme_id`, `title`, `header_scrolling`, `created_at`, `updated_at`, `is_active`, `is_default`, `custom_css`, `created_by`, `updated_by`, `bg_image_selection`, `bg_slider_time`, `color_scheme_id`) SELECT id, 2, 'Die Standard-Konfiguration', 0, NULL, NULL, 0, 1, NULL, 1, NULL, 'random', NULL, 5 FROM fg_club


UPDATE `fg_tm_theme` SET `theme_options` = '{"themePreviewImage":"theme2.jpg","noOfColorsPerScheme":10,"noOfPresetSchemes":1,"colorLabels":["TM_BACKGROUND_COLOR","TM_NAV_BACKGROUND_COLOR","TM_NAV_TEXT_COLOR","TM_ACTIVE_MENU_COLOR","TM_BOX_BACKGROUND_COLOR","TM_MAIN_TEXT_COLOR","TM_LINK_BUTTON_COLOR","TM_PAGE_TITLE_TEXT_COLOR","TM_PAGE_TITLE_SHADOW_COLOR","TM_ACCENT_COLOR"],"noOfHeaderImages":2,"headerImageLabels":["TM_HEADER_LOGO","TM_DEFAULT_LOGO","TM_MOBILE_SCREEN_LOGO"],"noOfFonts":3,"font_label":{"TM_MAIN_TEXT":{"font_name":"Roboto","font_strength":"normal","is_italic":0,"is_uppercase":0},"TM_HEADINGS":{"font_name":"Montserrat","font_strength":"bold","is_italic":0,"is_uppercase":0},"TM_NAVIGATION":{"font_name":"Montserrat","font_strength":"bold","is_italic":0,"is_uppercase":1}}}' WHERE `fg_tm_theme`.`id` = 2;


UPDATE `fg_tm_theme_color_scheme` SET `color_schemes` = '{"colorPreviewImage":"color-scheme5.jpg", "TM_BACKGROUND_COLOR":"rgba(60, 113, 191, 1)", "TM_NAV_BACKGROUND_COLOR":"rgba(33, 43, 64, 0.65)", "TM_NAV_TEXT_COLOR":"rgba(255, 255, 255, 1)", "TM_ACTIVE_MENU_COLOR":"rgba(194, 224, 120, 1)", "TM_BOX_BACKGROUND_COLOR":"rgba(33, 43, 64, 0.5)", "TM_MAIN_TEXT_COLOR":"rgba(255, 255, 255, 1)", "TM_LINK_BUTTON_COLOR":"rgba(247, 147, 30, 1)", "TM_PAGE_TITLE_TEXT_COLOR":"rgba(255, 255, 255, 1)", "TM_PAGE_TITLE_SHADOW_COLOR":"rgba(0, 0, 0, 0.75)", "TM_ACCENT_COLOR":"rgba(0, 146, 69, 1.0)" } ' WHERE `fg_tm_theme_color_scheme`.`id` = 5;


=================================================================================================================================================================================================================================
ALTER TABLE `fg_cms_page` CHANGE `page_element` `page_element` TEXT DEFAULT NULL;
=================================================================================================================================================================================================================================
-- For FAIR-219 (Kanban)
ALTER TABLE `fg_cms_page_content_element` ADD `twitter_content_height` INT NULL AFTER `twitter_default_account`;

UPDATE `fg_cms_page_content_element` SET `twitter_content_height`= 450 WHERE `page_content_type_id` = 16
=====================================================================================================================================

ALTER TABLE `fg_cms_contact_table_filter` ADD CONSTRAINT `fg_cms_contact_table_filter_ibfk_2` FOREIGN KEY (`attribute_id`) REFERENCES `fg_cm_attribute`(`id`) ON DELETE CASCADE ON UPDATE NO ACTION;




---------------------------------------------------------------------------------------------- FAIRDEV 174 -----------------------------------------------------------------------------------------------

CREATE TABLE `fg_api_gotcourts` (
  `id` int(11) NOT NULL,
  `club_id` int(11) NOT NULL,
  `apitoken` varchar(255) NOT NULL,
  `status` enum('booked','generated','cancelled') NOT NULL DEFAULT 'booked',
  `is_active` smallint(6) DEFAULT NULL,
  `booked_by` int(11) NOT NULL,
  `booked_on` datetime NOT NULL,
  `generated_by` int(11) DEFAULT NULL,
  `generated_on` datetime DEFAULT NULL,
  `registered_on` datetime DEFAULT NULL,
  `regenerated_by` int(11) DEFAULT NULL,
  `regenerated_on` datetime DEFAULT NULL,
  `cancelled_by` int(11) DEFAULT NULL,
  `cancelled_on` datetime DEFAULT NULL
);

ALTER TABLE `fg_api_gotcourts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `apitoken` (`apitoken`),
  ADD KEY `club_id` (`club_id`),
  ADD KEY `booked_by` (`booked_by`),
  ADD KEY `generated_by` (`generated_by`),
  ADD KEY `regenerated_by` (`regenerated_by`),
  ADD KEY `cancelled_by` (`cancelled_by`);

ALTER TABLE `fg_api_gotcourts` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `fg_api_gotcourts`
  ADD CONSTRAINT `fg_api_gotcourts_ibfk_1` FOREIGN KEY (`club_id`) REFERENCES `fg_club` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fg_api_gotcourts_ibfk_2` FOREIGN KEY (`booked_by`) REFERENCES `fg_cm_contact` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fg_api_gotcourts_ibfk_3` FOREIGN KEY (`generated_by`) REFERENCES `fg_cm_contact` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fg_api_gotcourts_ibfk_4` FOREIGN KEY (`regenerated_by`) REFERENCES `fg_cm_contact` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fg_api_gotcourts_ibfk_5` FOREIGN KEY (`cancelled_by`) REFERENCES `fg_cm_contact` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;



CREATE TABLE `fg_api_gotcourts_log` (
  `id` int(11) NOT NULL,
  `club_id` int(11) NOT NULL,
  `gotcourt_id` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `field` enum('token','status') NOT NULL DEFAULT 'token',
  `value_after` varchar(255) DEFAULT NULL,
  `value_before` varchar(255) DEFAULT NULL,
  `changed_by` int(11) DEFAULT NULL
);

ALTER TABLE `fg_api_gotcourts_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `club_id` (`club_id`),
  ADD KEY `gotcourt_id` (`gotcourt_id`),
  ADD KEY `changed_by` (`changed_by`);

ALTER TABLE `fg_api_gotcourts_log` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `fg_api_gotcourts_log`
  ADD CONSTRAINT `fg_api_gotcourts_log_ibfk_1` FOREIGN KEY (`club_id`) REFERENCES `fg_club` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fg_api_gotcourts_log_ibfk_2` FOREIGN KEY (`gotcourt_id`) REFERENCES `fg_api_gotcourts` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fg_api_gotcourts_log_ibfk_3` FOREIGN KEY (`changed_by`) REFERENCES `fg_cm_contact` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION; 
  
ALTER TABLE `fg_api_gotcourts` CHANGE `apitoken` `apitoken` VARCHAR(255) NULL; 



CREATE TABLE `fg_api_accesslog` (
  `id` int(11) NOT NULL,
  `api_id` int(11) NOT NULL,
  `club_id` int(11) NOT NULL,
  `api_url` text NOT NULL,
  `date` datetime NOT NULL,
  `request_detail` text NOT NULL,
  `request_clientip` varchar(160) NOT NULL,
  `response_detail` text NOT NULL
);


ALTER TABLE `fg_api_accesslog` ADD PRIMARY KEY (`id`), ADD KEY `api_id` (`api_id`), ADD KEY `club_id` (`club_id`);

ALTER TABLE `fg_api_accesslog` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `fg_api_accesslog`
  ADD CONSTRAINT `fg_api_accesslog_ibfk_1` FOREIGN KEY (`api_id`) REFERENCES `fg_apis` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fg_api_accesslog_ibfk_2` FOREIGN KEY (`club_id`) REFERENCES `fg_club` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `fg_api_accesslog` CHANGE `club_id` `club_id` INT(11) NULL;  
-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
UPDATE `fg_tm_theme_color_scheme` SET `is_default` = 0 where club_id='1' AND theme_id ='2' AND id !=5
==================================================================================================
@changes in v4createclub
==================================================================================================
