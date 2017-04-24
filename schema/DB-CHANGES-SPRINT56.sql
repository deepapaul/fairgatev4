ALTER TABLE `fg_cms_page` ADD `page_element` VARCHAR(255) NULL DEFAULT NULL AFTER `hide_title`;
ALTER TABLE `fg_cms_page_content_media` ADD `link_open_type` ENUM('self','blank') NULL AFTER `image_element_link_type`;


--
-- Table structure for table `fg_web_settings`
--

CREATE TABLE `fg_web_settings` (
  `id` int(11) NOT NULL,
  `club_id` int(11) NOT NULL,
  `default_logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `favicon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fallback_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `site_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `domain_verification_filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `google_analytics_trackId` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `fg_web_settings`
--
ALTER TABLE `fg_web_settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `club_id` (`club_id`);

--
-- Constraints for dumped tables
--

--
-- AUTO_INCREMENT for table `fg_web_settings`
--
ALTER TABLE `fg_web_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;
--
-- Constraints for dumped tables
--



--
-- Constraints for table `fg_web_settings`
--
ALTER TABLE `fg_web_settings`
  ADD CONSTRAINT `fg_web_settings_ibfk_1` FOREIGN KEY (`club_id`) REFERENCES `fg_club` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


--
-- Table structure for table `fg_web_settings_i18n`
--

CREATE TABLE `fg_web_settings_i18n` (
  `id` int(11) NOT NULL,
  `settings_id` int(11) NOT NULL,
  `description_lang` text NOT NULL,
  `remove` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `fg_web_settings_i18n`
--
ALTER TABLE `fg_web_settings_i18n`
  ADD PRIMARY KEY (`id`),
  ADD KEY `settings_id` (`settings_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `fg_web_settings_i18n`
--
ALTER TABLE `fg_web_settings_i18n`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `fg_web_settings_i18n`
--
ALTER TABLE `fg_web_settings_i18n`
  ADD CONSTRAINT `fg_web_settings_i18n_ibfk_1` FOREIGN KEY (`settings_id`) REFERENCES `fg_web_settings` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;




ALTER TABLE `fg_web_settings_i18n` CHANGE `remove` `lang` VARCHAR(255) NULL;

INSERT INTO `fg_cms_page_content_type` (`id`, `type`, `logo_name`, `label`, `table_name`, `sort_order`) VALUES (NULL, 'twitter', 'fa-twitter', 'CMS_TWITTER', NULL, '16');

=====================================API ADMIN CREATION ===============================================================================================================
INSERT INTO `fg_cm_contact` (`id`, `club_id`, `subfed_contact_id`, `fed_contact_id`, `main_club_id`, `is_company`, `comp_def_contact`, `comp_def_contact_fun`, `is_draft`, `is_deleted`, `is_permanent_delete`, `is_seperate_invoice`, `is_fairgate`, `last_updated`, `is_new`, `member_id`, `dispatch_type_invoice`, `dispatch_type_dun`, `has_main_contact`, `has_main_contact_address`, `is_postal_address`, `created_at`, `joining_date`, `leaving_date`, `first_joining_date`, `archived_on`, `resigned_on`, `same_invoice_address`, `login_count`, `is_former_fed_member`, `last_login`, `map`, `import_table`, `import_id`, `fed_membership_cat_id`, `club_membership_cat_id`, `is_sponsor`, `is_stealth_mode`, `intranet_access`, `is_subscriber`, `system_language`, `is_household_head`, `old_fed_membership_id`, `is_fed_membership_confirmed`, `is_club_assignment_confirmed`, `fed_membership_assigned_club_id`, `created_club_id`, `allow_merging`, `merge_to_contact_id`, `quickwindow_visibilty`, `is_fed_admin`, `old_contact_id`, `club_type`) VALUES
(396029, 1, NULL, NULL, 1, 0, NULL, NULL, 0, 0, 0, 0, 1, '0000-00-00 00:00:00', 0, 0, 'POST', 'POST', 1, 0, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL, 1, 0, 0, NULL, 1, NULL, NULL, NULL, NULL, 0, 0, 1, 0, 'default', 0, NULL, '0', NULL, NULL, 1, 1, NULL, 1, 0, NULL, NULL);


INSERT INTO `sf_guard_user` (`id`, `first_name`, `last_name`, `username`, `username_canonical`, `email`, `email_canonical`, `algorithm`, `salt`, `password`, `is_active`, `is_super_admin`, `last_login`, `created_at`, `updated_at`, `contact_id`, `club_id`, `is_security_admin`, `is_readonly_admin`, `is_team_admin`, `is_team_section_admin`, `last_reminder`, `enabled`, `plain_password`, `locked`, `expired`, `expires_at`, `confirmation_token`, `password_requested_at`, `roles`, `credentials_expired`, `credentials_expire_at`, `has_full_permission`, `auth_code`) VALUES
(103171, 'ribcosinus', 'ribcosinus', 'ribcosinus', 'ribcosinus', 'ribcosinus@yopmail.com', 'ribcosinus@yopmail.com', 'sha1', '', '35vFyhYyrBiizdQiMf4ZAjFwRVLZH3X1Id5Vn7emgVqci3Hl6gtn0T1gnEX0nmnEjjlmrFoM+2JIRn4DFx/MQg==', 1, 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 396029, NULL, 0, 0, 0, 0, NULL, 1, '', 0, 0, NULL, NULL, NULL, 'a:1:{i:0;s:10:"ROLE_SUPER";}', 0, NULL, 1, 'VGcrUUV2TUk4SHViYWllL0IxNTFWZ1pKZWlhVlBaMnVscWZNNTRJSk5ETVc2UmdsT1M3MTJmNCsxd1UzakszRQ== ');


INSERT INTO `sf_guard_user_group` (`user_id`, `group_id`, `created_at`, `updated_at`) VALUES
(103171, 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00');
=======================API SUPERADMIN=================================================
UPDATE `sf_guard_user` SET `auth_code` = '' WHERE `sf_guard_user`.`id` = 1;
=====================================API ADMIN CREATION ================================================================================================================

settings.php updated for google analytics tracker Id (UA-34074203-41)
update in robots.txt
=====================================================================================================================================================
ALTER TABLE `fg_cms_page_content_element` ADD `twitter_default_account` VARCHAR(255) NULL AFTER `sponsor_ad_area_id`;

ALTER TABLE `fg_cms_page_content_element_i18n` ADD `twitter_accountname_lang` VARCHAR(255) NULL AFTER `lang`;
ALTER TABLE `fg_cms_page` ADD `opengraph_details` VARCHAR(255) NULL AFTER `page_element`;

===========================================================================
 
ALTER TABLE fg_cms_page_content_media DROP COLUMN link_open_type;
ALTER TABLE `fg_cms_page_content_element` ADD `image_element_link_opentype` ENUM('self', 'blank') NULL  AFTER `image_element_click_type`;
ALTER TABLE `fg_cms_page` CHANGE `opengraph_details` `opengraph_details` TEXT;
=============================================== FAIRDEV-23 =============================================================================================================

-- MYSQL FUNCTION subscriberSalutationText()
-- MYSQL PROCEDURE insertNewsletterContactsToSpoolv4()

=============================================== FAIRDEV-23 =============================================================================================================

=============================================== FAIRDEV-21 =============================================================================================================
ALTER TABLE fg_cms_page_content_media DROP COLUMN link_open_type;
ALTER TABLE `fg_cms_page_content_element` ADD `image_element_link_opentype` ENUM('self', 'blank') NULL  AFTER `image_element_click_type`;
ALTER TABLE `fg_cms_page` CHANGE `opengraph_details` `opengraph_details` TEXT;
=============================================== FAIRDEV-21 =============================================================================================================

==================================================FAIRDEV-64====================================================================
ALTER TABLE `fg_tm_theme_fonts` CHANGE `font_strength` `font_strength` SET('normal','bold','lighter') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;

UPDATE `fg_tm_theme` SET `theme_options` = '{"themePreviewImage":"theme1.jpg","noOfColorsPerScheme":5,"noOfPresetSchemes":4,"colorLabels":["TM_BACKGROUND_COLOR","TM_INVERSE_COLOR","TM_ACCENT_COLOR","TM_LINKSBUTTONS_COLOR","TM_HIGHLIGHTING_COLOR"],"noOfHeaderImages":3,"headerImageLabels":["TM_DEFAULT_LOGO","TM_SHRINKED_LOGO","TM_MOBILE_SCREEN_LOGO"],"noOfFonts":3,"font_label":{"TM_MAIN_TEXT":{"font_name":"Roboto Condensed","font_strength":"lighter","is_italic":0,"is_uppercase":0},"TM_HEADINGS":{"font_name":"Roboto Condensed","font_strength":"bold","is_italic":0,"is_uppercase":1},"TM_NAVIGATION":{"font_name":"Roboto Condensed","font_strength":"normal","is_italic":0,"is_uppercase":1}}}' WHERE `fg_tm_theme`.`id` = 1;


UPDATE `fg_tm_theme` SET `theme_options` = '{"themePreviewImage":"theme2.jpg","noOfColorsPerScheme":4,"noOfPresetSchemes":3,"colorLabels":["TM_BACKGROUND_COLOR","TM_LINKSBUTTONS_COLOR","TM_HIGHLIGHTING_COLOR","TM_SIDEBAR_COLOR"],"noOfHeaderImages":3,"headerImageLabels":["TM_DEFAULT_LOGO","TM_SHRINKED_LOGO","TM_MOBILE_SCREEN_LOGO"],"noOfFonts":2,"font_label":{"TM_MAIN_TEXT":{"font_name":"Roboto Condensed","font_strength":"lighter","is_italic":1,"is_uppercase":0},"TM_HEADINGS":{"font_name":"Oswald","font_strength":"bold","is_italic":0,"is_uppercase":1}}}' WHERE `fg_tm_theme`.`id` = 2;

UPDATE `fg_tm_theme` SET `theme_options` = '{"themePreviewImage":"theme3.jpg","noOfColorsPerScheme":2,"noOfPresetSchemes":3,"colorLabels":["TM_BACKGROUND_COLOR","TM_BUTTON_COLOR"],"noOfHeaderImages":2,"headerImageLabels":["TM_DEFAULT_LOGO","TM_MOBILE_SCREEN_LOGO"],"noOfFonts":3,"font_label":{"TM_MAIN_TEXT":{"font_name":"Roboto Condensed","font_strength":"lighter","is_italic":1,"is_uppercase":0},"TM_HEADINGS":{"font_name":"Oswald","font_strength":"bold","is_italic":0,"is_uppercase":1},"TM_NAVIGATION":{"font_name":"Roboto Condensed","font_strength":"normal","is_italic":1,"is_uppercase":1}}}' WHERE `fg_tm_theme`.`id` = 3;
==================================================FAIRDEV-64====================================================================

-----------------------------------------------------------------------------------------------------------------------
-----------------------------------------------------------------------------------------------------------------------
-----------------------------------------------------------------------------------------------------------------------
-----------------------------------------------------------------------------------------------------------------------
--------------MIGRATION CORRECTIONS  FOR CONTACT FIELD SAVE -STARTS--------------------------------------------
-----------------------------------------------------------------------------------------------------------------------
-----------------------------------------------------------------------------------------------------------------------
-----------------------------------------------------------------------------------------------------------------------
-----------------------------------------------------------------------------------------------------------------------


call handleAttributes() -- Update stored procedure
DROP TABLE `fg_temp_attribute`, `fg_temp_attributeset`, `fg_temp_attributeset_i18n`, `fg_temp_attribute_i18n`, `fg_temp_attribute_required` ;
--
-- Table structure for table `fg_temp_attribute`
--

CREATE TABLE `fg_temp_attribute` (
  `uid` int(11) NOT NULL,
  `id` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cat_id` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attributeset_id` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fieldname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fieldname_short` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `input_type` enum('multiline','singleline','checkbox','select','date','email','url','fileupload','imageupload','login email','radio','number') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isCompany` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isPersonal` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `predefined_value` mediumtext COLLATE utf8mb4_unicode_ci,
  `is_single_edit` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_required_fedmember_subfed` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_required_fedmember_club` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `addres_type` enum('correspondance','invoice','both') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `availabilityContact` enum('changable','visible','not_available') COLLATE utf8mb4_unicode_ci DEFAULT 'changable',
  `is_setPrivacyItself` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `privacyContact` enum('community','team','private') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `availabilityGroupadmin` enum('changable','visible','not_available') COLLATE utf8mb4_unicode_ci DEFAULT 'changable',
  `isConfirmContact` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_required_type` enum('not_required','all_contacts','all_club_members','all_fed_members','selected_members') COLLATE utf8mb4_unicode_ci DEFAULT 'not_required',
  `isConfirmTeamadmin` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isDeleted` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isActive` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `club_id` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `availability_sub_fed` enum('changable','visible','not_available') COLLATE utf8mb4_unicode_ci DEFAULT 'not_available',
  `availability_club` enum('changable','visible','not_available') COLLATE utf8mb4_unicode_ci DEFAULT 'not_available',
  `random` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fg_temp_attributeset`
--

CREATE TABLE `fg_temp_attributeset` (
  `uid` int(11) NOT NULL,
  `id` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isDeleted` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `club_id` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `random` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fg_temp_attributeset_i18n`
--

CREATE TABLE `fg_temp_attributeset_i18n` (
  `uid` int(11) NOT NULL,
  `id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lang` char(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title_lang` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `random` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fg_temp_attribute_i18n`
--

CREATE TABLE `fg_temp_attribute_i18n` (
  `uid` int(11) NOT NULL,
  `id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lang` char(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fieldname_lang` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fieldname_short_lang` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `random` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fg_temp_attribute_required`
--

CREATE TABLE `fg_temp_attribute_required` (
  `uid` int(11) NOT NULL,
  `attribute_id` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `club_id` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `membership_id` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `random` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `fg_temp_attribute`
--
ALTER TABLE `fg_temp_attribute`
  ADD PRIMARY KEY (`uid`),
  ADD KEY `random` (`random`),
  ADD KEY `club_id` (`club_id`),
  ADD KEY `id` (`id`),
  ADD KEY `cat_id` (`cat_id`);

--
-- Indexes for table `fg_temp_attributeset`
--
ALTER TABLE `fg_temp_attributeset`
  ADD PRIMARY KEY (`uid`),
  ADD KEY `club_id` (`club_id`),
  ADD KEY `random` (`random`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `fg_temp_attributeset_i18n`
--
ALTER TABLE `fg_temp_attributeset_i18n`
  ADD PRIMARY KEY (`uid`),
  ADD KEY `random` (`random`),
  ADD KEY `id` (`id`),
  ADD KEY `lang` (`lang`);

--
-- Indexes for table `fg_temp_attribute_i18n`
--
ALTER TABLE `fg_temp_attribute_i18n`
  ADD PRIMARY KEY (`uid`),
  ADD KEY `random` (`random`),
  ADD KEY `id` (`id`),
  ADD KEY `lang` (`lang`);

--
-- Indexes for table `fg_temp_attribute_required`
--
ALTER TABLE `fg_temp_attribute_required`
  ADD PRIMARY KEY (`uid`),
  ADD KEY `random` (`random`),
  ADD KEY `attribute_id` (`attribute_id`),
  ADD KEY `club_id` (`club_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `fg_temp_attribute`
--
ALTER TABLE `fg_temp_attribute`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `fg_temp_attributeset`
--
ALTER TABLE `fg_temp_attributeset`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `fg_temp_attributeset_i18n`
--
ALTER TABLE `fg_temp_attributeset_i18n`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `fg_temp_attribute_i18n`
--
ALTER TABLE `fg_temp_attribute_i18n`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `fg_temp_attribute_required`
--
ALTER TABLE `fg_temp_attribute_required`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;

-----------------------------------------------------------------------------------------------------------------------
-----------------------------------------------------------------------------------------------------------------------
-----------------------------------------------------------------------------------------------------------------------
-----------------------------------------------------------------------------------------------------------------------
--------------MIGRATION CORRECTIONS  FOR CONTACT FIELD SAVE -ENDS--------------------------------------------
-----------------------------------------------------------------------------------------------------------------------
-----------------------------------------------------------------------------------------------------------------------
-----------------------------------------------------------------------------------------------------------------------
-----------------------------------------------------------------------------------------------------------------------