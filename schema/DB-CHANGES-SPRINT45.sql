
CREATE TABLE `fg_cms_navigation` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `page_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `sort_order` int(11) DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT '1',
  `club_id` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `edited_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `edited_at` datetime DEFAULT NULL,
  `type` enum('page','external','article','gallery','calender') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `external_link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `navigation_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fg_cms_navigation_i18n`
--

CREATE TABLE `fg_cms_navigation_i18n` (
  `id` int(11) NOT NULL,
  `title_lang` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `lang` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fg_cms_page`
--

CREATE TABLE `fg_cms_page` (
  `id` int(11) NOT NULL,
  `club_id` int(11) NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `type` enum('page','footer','sidebar') NOT NULL,
  `sidebar_type` enum('wide','small') NOT NULL,
  `sidebar_area` enum('left','right') NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `edited_by` int(11) NOT NULL,
  `edited_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fg_cms_page_container`
--

CREATE TABLE `fg_cms_page_container` (
  `id` int(11) NOT NULL,
  `page_id` int(11) NOT NULL,
  `sort_order` int(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fg_cms_page_container_box`
--

CREATE TABLE `fg_cms_page_container_box` (
  `id` int(11) NOT NULL,
  `column_id` int(11) NOT NULL,
  `sort_order` int(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fg_cms_page_container_column`
--

CREATE TABLE `fg_cms_page_container_column` (
  `id` int(11) NOT NULL,
  `container_id` int(11) NOT NULL,
  `width_value` int(11) NOT NULL,
  `sort_order` int(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fg_cms_page_content_element`
--

CREATE TABLE `fg_cms_page_content_element` (
  `id` int(11) NOT NULL,
  `page_content_type_id` int(11) NOT NULL,
  `box_id` int(11) DEFAULT NULL,
  `club_id` int(11) NOT NULL,
  `sort_order` int(3) NOT NULL,
  `deleted_at` datetime NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `header_element_size` enum('large','medium','small','mini','nano') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fg_cms_page_content_element_i18n`
--

CREATE TABLE `fg_cms_page_content_element_i18n` (
  `id` int(11) NOT NULL,
  `title_lang` varchar(255) NOT NULL,
  `lang` char(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fg_cms_page_content_element_log`
--

CREATE TABLE `fg_cms_page_content_element_log` (
  `id` int(11) NOT NULL,
  `element_id` int(11) NOT NULL,
  `page_id` int(11) NOT NULL,
  `type` enum('element','page') NOT NULL,
  `action` enum('added','changed','deleted') NOT NULL,
  `value_before` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `value_after` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `date` datetime NOT NULL,
  `changed_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fg_cms_page_content_type`
--

CREATE TABLE `fg_cms_page_content_type` (
  `id` int(11) NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `logo_name` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `table_name` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `sort_order` int(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fg_cms_page_i18n`
--

CREATE TABLE `fg_cms_page_i18n` (
  `id` int(11) NOT NULL,
  `title_lang` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `lang` char(2) CHARACTER SET utf8mb4 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `fg_cms_navigation`
--
ALTER TABLE `fg_cms_navigation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`),
  ADD KEY `id` (`id`),
  ADD KEY `page_id` (`page_id`),
  ADD KEY `club_id` (`club_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `edited_by` (`edited_by`);

--
-- Indexes for table `fg_cms_navigation_i18n`
--
ALTER TABLE `fg_cms_navigation_i18n`
  ADD PRIMARY KEY (`lang`,`id`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `fg_cms_page`
--
ALTER TABLE `fg_cms_page`
  ADD PRIMARY KEY (`id`),
  ADD KEY `club_id` (`club_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `edited_by` (`edited_by`);

--
-- Indexes for table `fg_cms_page_container`
--
ALTER TABLE `fg_cms_page_container`
  ADD PRIMARY KEY (`id`),
  ADD KEY `page_id` (`page_id`);

--
-- Indexes for table `fg_cms_page_container_box`
--
ALTER TABLE `fg_cms_page_container_box`
  ADD PRIMARY KEY (`id`),
  ADD KEY `column_id` (`column_id`);

--
-- Indexes for table `fg_cms_page_container_column`
--
ALTER TABLE `fg_cms_page_container_column`
  ADD PRIMARY KEY (`id`),
  ADD KEY `container_id` (`container_id`);

--
-- Indexes for table `fg_cms_page_content_element`
--
ALTER TABLE `fg_cms_page_content_element`
  ADD PRIMARY KEY (`id`),
  ADD KEY `page_content_type_id` (`page_content_type_id`),
  ADD KEY `box_id` (`box_id`),
  ADD KEY `club_id` (`club_id`);

--
-- Indexes for table `fg_cms_page_content_element_i18n`
--
ALTER TABLE `fg_cms_page_content_element_i18n`
  ADD PRIMARY KEY (`id`,`lang`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `fg_cms_page_content_element_log`
--
ALTER TABLE `fg_cms_page_content_element_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `element_id` (`element_id`),
  ADD KEY `changed_by` (`changed_by`),
  ADD KEY `page_id` (`page_id`);

--
-- Indexes for table `fg_cms_page_content_type`
--
ALTER TABLE `fg_cms_page_content_type`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fg_cms_page_i18n`
--
ALTER TABLE `fg_cms_page_i18n`
  ADD PRIMARY KEY (`id`,`lang`),
  ADD KEY `id` (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `fg_cms_navigation`
--
ALTER TABLE `fg_cms_navigation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=181;
--
-- AUTO_INCREMENT for table `fg_cms_page`
--
ALTER TABLE `fg_cms_page`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `fg_cms_page_container`
--
ALTER TABLE `fg_cms_page_container`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `fg_cms_page_container_box`
--
ALTER TABLE `fg_cms_page_container_box`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `fg_cms_page_container_column`
--
ALTER TABLE `fg_cms_page_container_column`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `fg_cms_page_content_element`
--
ALTER TABLE `fg_cms_page_content_element`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `fg_cms_page_content_element_i18n`
--
ALTER TABLE `fg_cms_page_content_element_i18n`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `fg_cms_page_content_element_log`
--
ALTER TABLE `fg_cms_page_content_element_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `fg_cms_page_content_type`
--
ALTER TABLE `fg_cms_page_content_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `fg_cms_navigation`
--
ALTER TABLE `fg_cms_navigation`
  ADD CONSTRAINT `fg_cms_navigation_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `fg_cms_navigation` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fg_cms_navigation_ibfk_2` FOREIGN KEY (`page_id`) REFERENCES `fg_cms_page` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fg_cms_navigation_ibfk_3` FOREIGN KEY (`club_id`) REFERENCES `fg_club` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fg_cms_navigation_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `fg_cm_contact` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fg_cms_navigation_ibfk_5` FOREIGN KEY (`edited_by`) REFERENCES `fg_cm_contact` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `fg_cms_navigation_i18n`
--
ALTER TABLE `fg_cms_navigation_i18n`
  ADD CONSTRAINT `fg_cms_navigation_i18n_ibfk_1` FOREIGN KEY (`id`) REFERENCES `fg_cms_navigation` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `fg_cms_page`
--
ALTER TABLE `fg_cms_page`
  ADD CONSTRAINT `fg_cms_page_ibfk_1` FOREIGN KEY (`club_id`) REFERENCES `fg_club` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fg_cms_page_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `fg_cm_contact` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fg_cms_page_ibfk_3` FOREIGN KEY (`edited_by`) REFERENCES `fg_cm_contact` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `fg_cms_page_container`
--
ALTER TABLE `fg_cms_page_container`
  ADD CONSTRAINT `fg_cms_page_container_ibfk_1` FOREIGN KEY (`page_id`) REFERENCES `fg_cms_page` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `fg_cms_page_container_box`
--
ALTER TABLE `fg_cms_page_container_box`
  ADD CONSTRAINT `fg_cms_page_container_box_ibfk_1` FOREIGN KEY (`column_id`) REFERENCES `fg_cms_page_container_column` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `fg_cms_page_container_column`
--
ALTER TABLE `fg_cms_page_container_column`
  ADD CONSTRAINT `fg_cms_page_container_column_ibfk_1` FOREIGN KEY (`container_id`) REFERENCES `fg_cms_page_container` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `fg_cms_page_content_element`
--
ALTER TABLE `fg_cms_page_content_element`
  ADD CONSTRAINT `fg_cms_page_content_element_ibfk_1` FOREIGN KEY (`page_content_type_id`) REFERENCES `fg_cms_page_content_type` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fg_cms_page_content_element_ibfk_2` FOREIGN KEY (`box_id`) REFERENCES `fg_cms_page_container_box` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fg_cms_page_content_element_ibfk_3` FOREIGN KEY (`club_id`) REFERENCES `fg_club` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `fg_cms_page_content_element_i18n`
--
ALTER TABLE `fg_cms_page_content_element_i18n`
  ADD CONSTRAINT `fg_cms_page_content_element_i18n_ibfk_1` FOREIGN KEY (`id`) REFERENCES `fg_cms_page_content_element` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `fg_cms_page_content_element_log`
--
ALTER TABLE `fg_cms_page_content_element_log`
  ADD CONSTRAINT `fg_cms_page_content_element_log_ibfk_1` FOREIGN KEY (`element_id`) REFERENCES `fg_cms_page_content_element` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fg_cms_page_content_element_log_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `fg_cm_contact` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fg_cms_page_content_element_log_ibfk_3` FOREIGN KEY (`page_id`) REFERENCES `fg_cms_page` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `fg_cms_page_i18n`
--
ALTER TABLE `fg_cms_page_i18n`
  ADD CONSTRAINT `fg_cms_page_i18n_ibfk_1` FOREIGN KEY (`id`) REFERENCES `fg_cms_page` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
====================================================================================================================================================================================

INSERT INTO `sf_guard_group` (`id`, `name`, `description`, `created_at`, `updated_at`, `type`, `sort_order`, `roles`, `module_type`, `is_readonly`) 
VALUES (18, 'CmsAdmin', 'Cms administrators', '2016-05-30 00:00:00', '2016-05-30 00:00:00', 'club', '6', 'a:1:{i:0;s:14:"ROLE_CMS_ADMIN";}', 'cms', '0');

INSERT INTO `sf_guard_group` (`id`, `name`, `description`, `created_at`, `updated_at`, `type`, `sort_order`, `roles`, `module_type`, `is_readonly`) VALUES
(21, 'PageAdmin', 'PageAdmin', '2016-05-27 00:00:00', '2016-05-27 00:00:00', 'page', 8, 'a:1:{i:0;s:15:"ROLE_PAGE_ADMIN";}', 'cms', 0);

====================================================================================================================================================================================


CREATE TABLE IF NOT EXISTS `fg_filemanager_viruschecklog` (
`id` int(11) NOT NULL,
  `club_id` int(11) NOT NULL,
  `contact_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_details` varchar(255) NOT NULL,
  `request_senton` datetime NOT NULL,
  `response_status` enum('safe','unsafe','exception','not_responding') NOT NULL DEFAULT 'not_responding',
  `response_receivedon` datetime NULL,
  `response_detail` varchar(255) NULL,
  `avastscan_option` varchar(255) NULL,
  `log_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


ALTER TABLE `fg_filemanager_viruschecklog` ADD PRIMARY KEY (`id`), ADD KEY `club_id` (`club_id`,`contact_id`), ADD KEY `contact_id` (`contact_id`);
ALTER TABLE `fg_filemanager_viruschecklog` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `fg_filemanager_viruschecklog`
ADD CONSTRAINT `fg_filemanager_viruschecklog_ibfk_1` FOREIGN KEY (`club_id`) REFERENCES `fg_club` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
ADD CONSTRAINT `fg_filemanager_viruschecklog_ibfk_2` FOREIGN KEY (`contact_id`) REFERENCES `fg_cm_contact` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;
