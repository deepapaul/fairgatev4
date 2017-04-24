
CREATE TABLE `fg_cms_article` (
  `id` int(11) NOT NULL,
  `club_id` int(11) NOT NULL,
  `textversion_id` int(11) DEFAULT NULL,
  `publication_date` datetime DEFAULT NULL,
  `expiry_date` datetime DEFAULT NULL,
  `author` varchar(45) DEFAULT NULL,
  `scope` enum('PUBLIC','INTERNAL') NOT NULL DEFAULT 'PUBLIC' COMMENT '1=>Public, 2=>Internal',
  `position` enum('left_column','right_column','top_slider','bottom_slider') NOT NULL DEFAULT 'left_column',
  `is_draft` smallint(6) DEFAULT '0' COMMENT '0=> Not Draft 1=> Is Draft',
  `comment_allow` smallint(6) DEFAULT '0' COMMENT '0=>No,1=>From logged-in users only,2=>From everybody',
  `created_by` int(11) NOT NULL,
  `created_on` datetime DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_on` datetime DEFAULT NULL,
  `archived_by` int(11) DEFAULT NULL,
  `is_deleted` smallint(6) DEFAULT '0' COMMENT '0=> not deleted 1=>deleted',
  `share_with_lower` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `fg_cms_article_attachments`
--

CREATE TABLE `fg_cms_article_attachments` (
  `id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `filemanager_id` int(11) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `fg_cms_article_category`
--

CREATE TABLE `fg_cms_article_category` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT NULL,
  `club_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `fg_cms_article_category_i18n`
--

CREATE TABLE `fg_cms_article_category_i18n` (
  `id` int(11) NOT NULL,
  `lang` char(2) NOT NULL,
  `title_lang` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `fg_cms_article_clubsetting`
--

CREATE TABLE `fg_cms_article_clubsetting` (
  `id` int(11) NOT NULL,
  `club_id` int(11) NOT NULL,
  `comment_active` smallint(6) DEFAULT '1' COMMENT 'Global activation 0 => OFF 1=> ON',
  `show_multilanguage_version` smallint(6) DEFAULT '1' COMMENT 'Show articles even language version isn''t provided 0=> No 1=>Yes',
  `timeperiod_start_day` int(11) DEFAULT NULL,
  `timeperiod_start_month` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `fg_cms_article_comments`
--

CREATE TABLE `fg_cms_article_comments` (
  `id` int(11) NOT NULL,
  `contact_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `comment` text,
  `created_by` int(11) NOT NULL,
  `created_on` datetime DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_on` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `fg_cms_article_log`
--

CREATE TABLE `fg_cms_article_log` (
  `id` int(11) NOT NULL,
  `club_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `date` datetime DEFAULT NULL,
  `field` varchar(255) DEFAULT NULL,
  `kind` enum('image','video','attachment','area','category','data') DEFAULT NULL,
  `value_after` varchar(255) DEFAULT NULL,
  `value_before` varchar(255) DEFAULT NULL,
  `changed_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `fg_cms_article_media`
--

CREATE TABLE `fg_cms_article_media` (
  `id` int(11) NOT NULL,
  `items_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `fg_cms_article_selectedareas`
--

CREATE TABLE `fg_cms_article_selectedareas` (
  `id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `role_id` int(11) DEFAULT NULL,
  `is_club` tinyint(4) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `fg_cms_article_selectedcategories`
--

CREATE TABLE `fg_cms_article_selectedcategories` (
  `id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `fg_cms_article_text`
--

CREATE TABLE `fg_cms_article_text` (
  `id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `teaser` varchar(255) DEFAULT NULL,
  `text` text,
  `last_editedby` int(11) NOT NULL,
  `last_editedon` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `fg_cms_article_text_i18n`
--

CREATE TABLE `fg_cms_article_text_i18n` (
  `id` int(11) NOT NULL,
  `lang` char(2) NOT NULL,
  `title_lang` varchar(255) DEFAULT NULL,
  `teaser_lang` varchar(255) DEFAULT NULL,
  `text_lang` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `fg_cms_article`
--
ALTER TABLE `fg_cms_article`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_fg_cms_article_fg_club1_idx` (`club_id`),
  ADD KEY `fk_fg_cms_article_fg_cm_contact1_idx` (`created_by`),
  ADD KEY `fk_fg_cms_article_fg_cm_contact2_idx` (`updated_by`),
  ADD KEY `textversion_id` (`textversion_id`),
  ADD KEY `archived_by` (`archived_by`);

--
-- Indexes for table `fg_cms_article_attachments`
--
ALTER TABLE `fg_cms_article_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_fg_cms_article_attachments_fg_cms_article1_idx` (`article_id`),
  ADD KEY `fk_fg_cms_article_attachments_fg_file_manager1_idx` (`filemanager_id`);

--
-- Indexes for table `fg_cms_article_category`
--
ALTER TABLE `fg_cms_article_category`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_fg_cms_article_category_fg_club1_idx` (`club_id`);

--
-- Indexes for table `fg_cms_article_category_i18n`
--
ALTER TABLE `fg_cms_article_category_i18n`
  ADD PRIMARY KEY (`lang`,`id`),
  ADD KEY `fk_fg_cms_article_category_fg_cms_article_category_i18n_idx` (`id`);

--
-- Indexes for table `fg_cms_article_clubsetting`
--
ALTER TABLE `fg_cms_article_clubsetting`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_fg_file_manager_fg_club1_idx` (`club_id`);

--
-- Indexes for table `fg_cms_article_comments`
--
ALTER TABLE `fg_cms_article_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_fg_cms_article_comments_fg_cms_article1_idx` (`article_id`),
  ADD KEY `fk_fg_cms_article_comments_fg_cm_contact1_idx` (`contact_id`),
  ADD KEY `fk_fg_cms_article_comments_fg_cm_contact2_idx` (`updated_by`),
  ADD KEY `fk_fg_cms_article_comments_fg_cm_contact3_idx` (`created_by`);

--
-- Indexes for table `fg_cms_article_log`
--
ALTER TABLE `fg_cms_article_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_fg_cms_article_log_fg_cm_contact1_idx` (`changed_by`),
  ADD KEY `fk_fg_cms_article_log_fg_cms_article1_idx` (`article_id`),
  ADD KEY `fk_fg_cms_article_log_fg_club1_idx` (`club_id`);

--
-- Indexes for table `fg_cms_article_media`
--
ALTER TABLE `fg_cms_article_media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_fg_cms_article_media_fg_gm_items1_idx` (`items_id`),
  ADD KEY `fk_fg_cms_article_media_fg_cms_article2_idx` (`article_id`);

--
-- Indexes for table `fg_cms_article_selectedareas`
--
ALTER TABLE `fg_cms_article_selectedareas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_fg_cms_article_selectedareas_fg_cms_article1_idx` (`article_id`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `fg_cms_article_selectedcategories`
--
ALTER TABLE `fg_cms_article_selectedcategories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_fg_cms_article_selectedcategories_fg_cms_article1_idx` (`article_id`),
  ADD KEY `fk_fg_cms_article_selectedcategories_fg_cms_article_categor_idx` (`category_id`);

--
-- Indexes for table `fg_cms_article_text`
--
ALTER TABLE `fg_cms_article_text`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_fg_cms_article_text_fg_cms_article1_idx` (`article_id`),
  ADD KEY `fk_fg_cms_article_text_fg_cm_contact1_idx` (`last_editedby`);

--
-- Indexes for table `fg_cms_article_text_i18n`
--
ALTER TABLE `fg_cms_article_text_i18n`
  ADD PRIMARY KEY (`lang`,`id`),
  ADD KEY `id` (`id`),
  ADD KEY `lang` (`lang`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `fg_cms_article`
--
ALTER TABLE `fg_cms_article`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=450;
--
-- AUTO_INCREMENT for table `fg_cms_article_attachments`
--
ALTER TABLE `fg_cms_article_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=315;
--
-- AUTO_INCREMENT for table `fg_cms_article_category`
--
ALTER TABLE `fg_cms_article_category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1235;
--
-- AUTO_INCREMENT for table `fg_cms_article_clubsetting`
--
ALTER TABLE `fg_cms_article_clubsetting`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;
--
-- AUTO_INCREMENT for table `fg_cms_article_comments`
--
ALTER TABLE `fg_cms_article_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=156;
--
-- AUTO_INCREMENT for table `fg_cms_article_log`
--
ALTER TABLE `fg_cms_article_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3639;
--
-- AUTO_INCREMENT for table `fg_cms_article_media`
--
ALTER TABLE `fg_cms_article_media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=613;
--
-- AUTO_INCREMENT for table `fg_cms_article_selectedareas`
--
ALTER TABLE `fg_cms_article_selectedareas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1243;
--
-- AUTO_INCREMENT for table `fg_cms_article_selectedcategories`
--
ALTER TABLE `fg_cms_article_selectedcategories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=953;
--
-- AUTO_INCREMENT for table `fg_cms_article_text`
--
ALTER TABLE `fg_cms_article_text`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=774;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `fg_cms_article`
--
ALTER TABLE `fg_cms_article`
  ADD CONSTRAINT `fg_cms_article_ibfk_1` FOREIGN KEY (`textversion_id`) REFERENCES `fg_cms_article_text` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fg_cms_article_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `fg_cm_contact` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fg_cms_article_ibfk_3` FOREIGN KEY (`updated_by`) REFERENCES `fg_cm_contact` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fg_cms_article_ibfk_4` FOREIGN KEY (`archived_by`) REFERENCES `fg_cm_contact` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_fg_cms_article_fg_club1` FOREIGN KEY (`club_id`) REFERENCES `fg_club` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `fg_cms_article_attachments`
--
ALTER TABLE `fg_cms_article_attachments`
  ADD CONSTRAINT `fg_cms_article_attachments_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `fg_cms_article` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fg_cms_article_attachments_ibfk_2` FOREIGN KEY (`filemanager_id`) REFERENCES `fg_file_manager` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `fg_cms_article_category`
--
ALTER TABLE `fg_cms_article_category`
  ADD CONSTRAINT ` fg_cms_article_category_ibfk_1` FOREIGN KEY (`club_id`) REFERENCES `fg_club` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `fg_cms_article_category_i18n`
--
ALTER TABLE `fg_cms_article_category_i18n`
  ADD CONSTRAINT `fk_fg_cms_article_category_fg_cms_article_category_i18n` FOREIGN KEY (`id`) REFERENCES `fg_cms_article_category` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `fg_cms_article_clubsetting`
--
ALTER TABLE `fg_cms_article_clubsetting`
  ADD CONSTRAINT `fk_fg_file_manager_fg_club10` FOREIGN KEY (`club_id`) REFERENCES `fg_club` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `fg_cms_article_comments`
--
ALTER TABLE `fg_cms_article_comments`
  ADD CONSTRAINT `fk_fg_cms_article_comments_fg_cm_contact1` FOREIGN KEY (`contact_id`) REFERENCES `fg_cm_contact` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_fg_cms_article_comments_fg_cm_contact2` FOREIGN KEY (`updated_by`) REFERENCES `fg_cm_contact` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_fg_cms_article_comments_fg_cm_contact3` FOREIGN KEY (`created_by`) REFERENCES `fg_cm_contact` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_fg_cms_article_comments_fg_cms_article1` FOREIGN KEY (`article_id`) REFERENCES `fg_cms_article` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `fg_cms_article_log`
--
ALTER TABLE `fg_cms_article_log`
  ADD CONSTRAINT `fk_fg_cms_article_log_fg_club1` FOREIGN KEY (`club_id`) REFERENCES `fg_club` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_fg_cms_article_log_fg_cm_contact1` FOREIGN KEY (`changed_by`) REFERENCES `fg_cm_contact` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_fg_cms_article_log_fg_cms_article1` FOREIGN KEY (`article_id`) REFERENCES `fg_cms_article` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `fg_cms_article_media`
--
ALTER TABLE `fg_cms_article_media`
  ADD CONSTRAINT `fk_fg_cms_article_media_fg_cms_article2` FOREIGN KEY (`article_id`) REFERENCES `fg_cms_article` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_fg_cms_article_media_fg_gm_items1` FOREIGN KEY (`items_id`) REFERENCES `fg_gm_items` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `fg_cms_article_selectedareas`
--
ALTER TABLE `fg_cms_article_selectedareas`
  ADD CONSTRAINT `fg_cms_article_selectedareas_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `fg_rm_role` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_fg_cms_article_selectedcategories_fg_cms_article10` FOREIGN KEY (`article_id`) REFERENCES `fg_cms_article` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `fg_cms_article_selectedcategories`
--
ALTER TABLE `fg_cms_article_selectedcategories`
  ADD CONSTRAINT `fk_fg_cms_article_selectedcategories_fg_cms_article1` FOREIGN KEY (`article_id`) REFERENCES `fg_cms_article` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_fg_cms_article_selectedcategories_fg_cms_article_category1` FOREIGN KEY (`category_id`) REFERENCES `fg_cms_article_category` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `fg_cms_article_text`
--
ALTER TABLE `fg_cms_article_text`
  ADD CONSTRAINT `fk_fg_cms_article_text_fg_cm_contact1` FOREIGN KEY (`last_editedby`) REFERENCES `fg_cm_contact` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_fg_cms_article_text_fg_cms_article1` FOREIGN KEY (`article_id`) REFERENCES `fg_cms_article` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `fg_cms_article_text_i18n`
--
ALTER TABLE `fg_cms_article_text_i18n`
  ADD CONSTRAINT `fg_cms_article_text_i18n_ibfk_1` FOREIGN KEY (`id`) REFERENCES `fg_cms_article_text` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


=================================================================================================================================================================================================
ALTER TABLE `fg_file_manager` CHANGE `source` `source` ENUM('SIMPLE EMAIL','NEWSLETTER','FILEMANAGER','CALENDAR','ARTICLE') CHARACTER SET utf8mb4 COLLATEutf8mb4_unicode_ci NULL DEFAULT 'FILEMANAGER';
ALTER TABLE `fg_gm_items` CHANGE `source` `source` ENUM('gallery','newsletter-image','newsletter-articleimage','article') CHARACTER SET utf8mb4 COLLATEutf8mb4_unicode_ci NOT NULL DEFAULT 'gallery';

ALTER TABLE `fg_cms_article_media` ADD `sort_order` INT NOT NULL DEFAULT '1' AFTER `article_id`;


ALTER TABLE `fg_cms_article_attachments` ADD `sort_order` INT NOT NULL DEFAULT '1' AFTER `filemanager_id`;


INSERT INTO `sf_guard_group` (`id`, `name`, `description`, `created_at`, `updated_at`, `type`, `sort_order`, `roles`, `module_type`, `is_readonly`) VALUES
(19, 'ArticleAdmin', 'ArticleAdmin', '2015-11-27 00:00:00', '2015-11-27 00:00:00', 'role', 5, 'a:1:{i:0;s:18:"ROLE_ARTICLE_ADMIN";}', 'article', 0),
(20, 'Article', 'Aticle', '2015-11-26 00:00:00', '2015-11-26 00:00:00', 'club', 4, 'a:1:{i:0;s:12:"ROLE_ARTICLE";}', 'article', 0);


INSERT INTO fg_cms_article_category (title,sort_order,club_id)
SELECT 'Kategorie',1,C.id FROM `fg_club` C
LEFT JOIN fg_cms_article_category AC ON AC.club_id = C.id
WHERE `is_deleted` = 0 AND AC.id IS NULL;

INSERT INTO fg_cms_article_category_i18n (id,lang,title_lang)
SELECT AC.id,'de',AC.title FROM fg_cms_article_category AC
LEFT JOIN fg_cms_article_category_i18n ACi18 ON AC.id = ACi18.id
WHERE AC.title = 'Kategorie' AND AC.sort_order = 1 AND ACi18.id IS NULL;

=================================================================================================================================================================================================