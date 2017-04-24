-- phpMyAdmin SQL Dump
-- version 4.2.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jan 13, 2016 at 09:15 AM
-- Server version: 5.5.38-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `fairgate_v4`
--

-- --------------------------------------------------------

--
-- Table structure for table `fg_file_manager`
--

CREATE TABLE IF NOT EXISTS `fg_file_manager` (
`id` int(11) NOT NULL,
  `club_id` int(11) NOT NULL,
  `virtual_filename` varchar(255) NOT NULL,
  `encrypted_filename` varchar(255) NOT NULL,
  `is_removed` tinyint(1) DEFAULT '0',
  `source` enum('SIMPLE EMAIL','NEWSLETTER','FILEMANAGER') DEFAULT 'FILEMANAGER',
  `latest_version_id` int(11) DEFAULT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=34 ;

-- --------------------------------------------------------

--
-- Table structure for table `fg_file_manager_log`
--

CREATE TABLE IF NOT EXISTS `fg_file_manager_log` (
`id` int(11) NOT NULL,
  `file_manager_id` int(11) NOT NULL,
  `kind` enum('Changed','Replaced','Added','Flagged') NOT NULL,
  `field` varchar(255) NOT NULL,
  `changed_by` int(11) NOT NULL,
  `value_after` text,
  `value_before` text,
  `date` datetime DEFAULT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=32 ;

-- --------------------------------------------------------

--
-- Table structure for table `fg_file_manager_version`
--

CREATE TABLE IF NOT EXISTS `fg_file_manager_version` (
`id` int(11) NOT NULL,
  `file_manager_id` int(11) NOT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `size` int(11) NOT NULL DEFAULT '0',
  `uploaded_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=21 ;

-- --------------------------------------------------------

--
-- Table structure for table `fg_gm_album`
--

CREATE TABLE IF NOT EXISTS `fg_gm_album` (
`id` int(11) NOT NULL,
  `club_id` int(11) NOT NULL,
  `name` varchar(45) DEFAULT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1168 ;

-- --------------------------------------------------------

--
-- Table structure for table `fg_gm_album_i18n`
--

CREATE TABLE IF NOT EXISTS `fg_gm_album_i18n` (
  `id` int(11) NOT NULL,
  `lang` char(2) NOT NULL,
  `name_lang` varchar(45) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fg_gm_album_items`
--

CREATE TABLE IF NOT EXISTS `fg_gm_album_items` (
`id` int(11) NOT NULL,
  `album_id` int(11) NOT NULL,
  `items_id` int(11) NOT NULL,
  `sort_order` int(11) DEFAULT NULL,
  `is_cover_image` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=499 ;

-- --------------------------------------------------------

--
-- Table structure for table `fg_gm_bookmarks`
--

CREATE TABLE IF NOT EXISTS `fg_gm_bookmarks` (
`id` int(11) NOT NULL,
  `club_id` int(11) NOT NULL,
  `album_id` int(11) NOT NULL,
  `contact_id` int(11) NOT NULL,
  `sort_order` int(11) DEFAULT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=511 ;

-- --------------------------------------------------------

--
-- Table structure for table `fg_gm_gallery`
--

CREATE TABLE IF NOT EXISTS `fg_gm_gallery` (
`id` int(11) NOT NULL,
  `album_id` int(11) NOT NULL,
  `role_id` int(11) DEFAULT NULL,
  `club_id` int(11) NOT NULL,
  `type` enum('CLUB','ROLE') DEFAULT 'ROLE',
  `parent_id` int(11) DEFAULT NULL,
  `sort_order` int(11) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=286 ;

-- --------------------------------------------------------

--
-- Table structure for table `fg_gm_items`
--

CREATE TABLE IF NOT EXISTS `fg_gm_items` (
`id` int(11) NOT NULL,
  `club_id` int(11) NOT NULL,
  `scope` enum('PUBLIC','INTERNAL') NOT NULL DEFAULT 'PUBLIC',
  `type` enum('IMAGE','VIDEO') NOT NULL DEFAULT 'IMAGE',
  `filepath` varchar(140) NOT NULL,
  `description` varchar(145) DEFAULT NULL,
  `video_thumb_url` varchar(140) DEFAULT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=720 ;

-- --------------------------------------------------------

--
-- Table structure for table `fg_gm_item_i18n`
--

CREATE TABLE IF NOT EXISTS `fg_gm_item_i18n` (
  `id` int(11) NOT NULL,
  `lang` char(2) NOT NULL,
  `description_lang` varchar(145) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `fg_file_manager`
--
ALTER TABLE `fg_file_manager`
 ADD PRIMARY KEY (`id`), ADD KEY `fk_fg_file_manager_fg_club1_idx` (`club_id`), ADD KEY `fk_fg_file_manager_fg_file_manager_version1_idx` (`latest_version_id`);

--
-- Indexes for table `fg_file_manager_log`
--
ALTER TABLE `fg_file_manager_log`
 ADD PRIMARY KEY (`id`), ADD KEY `fk_fg_file_manager_log_fg_file_manager1_idx` (`file_manager_id`), ADD KEY `changed_by` (`changed_by`);

--
-- Indexes for table `fg_file_manager_version`
--
ALTER TABLE `fg_file_manager_version`
 ADD PRIMARY KEY (`id`), ADD KEY `file_manager_id` (`file_manager_id`), ADD KEY `updated_by` (`updated_by`), ADD KEY `uploaded_by` (`uploaded_by`), ADD KEY `file_manager_id_2` (`file_manager_id`,`uploaded_by`,`updated_by`);

--
-- Indexes for table `fg_gm_album`
--
ALTER TABLE `fg_gm_album`
 ADD PRIMARY KEY (`id`), ADD KEY `fk_fg_gm_album_fg_club1_idx` (`club_id`);

--
-- Indexes for table `fg_gm_album_i18n`
--
ALTER TABLE `fg_gm_album_i18n`
 ADD PRIMARY KEY (`id`,`lang`), ADD KEY `fk_fg_gm_album_i18n_fg_gm_album1_idx` (`id`);

--
-- Indexes for table `fg_gm_album_items`
--
ALTER TABLE `fg_gm_album_items`
 ADD PRIMARY KEY (`id`), ADD KEY `album_id` (`album_id`), ADD KEY `items_id` (`items_id`);

--
-- Indexes for table `fg_gm_bookmarks`
--
ALTER TABLE `fg_gm_bookmarks`
 ADD PRIMARY KEY (`id`), ADD KEY `fk_fg_gm_bookmarks_fg_club1_idx` (`club_id`), ADD KEY `fk_fg_gm_bookmarks_fg_gm_album1_idx` (`album_id`), ADD KEY `fk_fg_gm_bookmarks_fg_cm_contact1_idx` (`contact_id`);

--
-- Indexes for table `fg_gm_gallery`
--
ALTER TABLE `fg_gm_gallery`
 ADD PRIMARY KEY (`id`), ADD KEY `fk_fg_gm_album_fg_rm_role1_idx` (`role_id`), ADD KEY `fk_fg_gm_gallery_fg_gm_album1_idx` (`album_id`), ADD KEY `club_id` (`club_id`), ADD KEY `id` (`id`), ADD KEY `album_id` (`album_id`), ADD KEY `role_id` (`role_id`), ADD KEY `club_id_2` (`club_id`), ADD KEY `id_2` (`id`);

--
-- Indexes for table `fg_gm_items`
--
ALTER TABLE `fg_gm_items`
 ADD PRIMARY KEY (`id`), ADD KEY `club_id` (`club_id`);

--
-- Indexes for table `fg_gm_item_i18n`
--
ALTER TABLE `fg_gm_item_i18n`
 ADD PRIMARY KEY (`id`,`lang`), ADD KEY `fk_fg_gm_album_i18n_copy1_fg_gm_items1_idx` (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `fg_file_manager`
--
ALTER TABLE `fg_file_manager`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=34;
--
-- AUTO_INCREMENT for table `fg_file_manager_log`
--
ALTER TABLE `fg_file_manager_log`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=32;
--
-- AUTO_INCREMENT for table `fg_file_manager_version`
--
ALTER TABLE `fg_file_manager_version`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=21;
--
-- AUTO_INCREMENT for table `fg_gm_album`
--
ALTER TABLE `fg_gm_album`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1168;
--
-- AUTO_INCREMENT for table `fg_gm_album_items`
--
ALTER TABLE `fg_gm_album_items`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=499;
--
-- AUTO_INCREMENT for table `fg_gm_bookmarks`
--
ALTER TABLE `fg_gm_bookmarks`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=511;
--
-- AUTO_INCREMENT for table `fg_gm_gallery`
--
ALTER TABLE `fg_gm_gallery`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=286;
--
-- AUTO_INCREMENT for table `fg_gm_items`
--
ALTER TABLE `fg_gm_items`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=720;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `fg_file_manager`
--
ALTER TABLE `fg_file_manager`
ADD CONSTRAINT `fk_fg_file_manager_fg_club1` FOREIGN KEY (`club_id`) REFERENCES `fg_club` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_fg_file_manager_fg_file_manager_version1` FOREIGN KEY (`latest_version_id`) REFERENCES `fg_file_manager_version` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION;

--
-- Constraints for table `fg_file_manager_log`
--
ALTER TABLE `fg_file_manager_log`
ADD CONSTRAINT `fg_file_manager_log_ibfk_1` FOREIGN KEY (`changed_by`) REFERENCES `fg_cm_contact` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `fk_fg_file_manager_log_fg_file_manager1` FOREIGN KEY (`file_manager_id`) REFERENCES `fg_file_manager` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `fg_file_manager_version`
--
ALTER TABLE `fg_file_manager_version`
ADD CONSTRAINT `fg_file_manager_version_ibfk_1` FOREIGN KEY (`file_manager_id`) REFERENCES `fg_file_manager` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
ADD CONSTRAINT `fg_file_manager_version_ibfk_3` FOREIGN KEY (`updated_by`) REFERENCES `fg_cm_contact` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION,
ADD CONSTRAINT `fg_file_manager_version_ibfk_4` FOREIGN KEY (`uploaded_by`) REFERENCES `fg_cm_contact` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION;

--
-- Constraints for table `fg_gm_album`
--
ALTER TABLE `fg_gm_album`
ADD CONSTRAINT `fk_fg_gm_album_fg_club1` FOREIGN KEY (`club_id`) REFERENCES `fg_club` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `fg_gm_album_i18n`
--
ALTER TABLE `fg_gm_album_i18n`
ADD CONSTRAINT `fk_fg_gm_album_i18n_fg_gm_album1` FOREIGN KEY (`id`) REFERENCES `fg_gm_album` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `fg_gm_album_items`
--
ALTER TABLE `fg_gm_album_items`
ADD CONSTRAINT `fg_gm_album_items_ibfk_1` FOREIGN KEY (`album_id`) REFERENCES `fg_gm_album` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
ADD CONSTRAINT `fg_gm_album_items_ibfk_2` FOREIGN KEY (`items_id`) REFERENCES `fg_gm_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fg_gm_bookmarks`
--
ALTER TABLE `fg_gm_bookmarks`
ADD CONSTRAINT `fk_fg_gm_bookmarks_fg_club1` FOREIGN KEY (`club_id`) REFERENCES `fg_club` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_fg_gm_bookmarks_fg_cm_contact1` FOREIGN KEY (`contact_id`) REFERENCES `fg_cm_contact` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_fg_gm_bookmarks_fg_gm_album1` FOREIGN KEY (`album_id`) REFERENCES `fg_gm_album` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `fg_gm_gallery`
--
ALTER TABLE `fg_gm_gallery`
ADD CONSTRAINT `fg_gm_gallery_ibfk_1` FOREIGN KEY (`club_id`) REFERENCES `fg_club` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_fg_gm_album_fg_rm_role10` FOREIGN KEY (`role_id`) REFERENCES `fg_rm_role` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_fg_gm_gallery_fg_gm_album1` FOREIGN KEY (`album_id`) REFERENCES `fg_gm_album` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `fg_gm_items`
--
ALTER TABLE `fg_gm_items`
ADD CONSTRAINT `fg_gm_items_ibfk_1` FOREIGN KEY (`club_id`) REFERENCES `fg_club` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `fg_gm_item_i18n`
--
ALTER TABLE `fg_gm_item_i18n`
ADD CONSTRAINT `fg_gm_item_i18n_ibfk_1` FOREIGN KEY (`id`) REFERENCES `fg_gm_items` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;



INSERT INTO `sf_guard_group` (`id`, `name`, `description`, `created_at`, `updated_at`, `type`, `sort_order`, `roles`, `module_type`, `is_readonly`) VALUES (15, 'GalleryAdmin', 'GalleryAdmin', '2015-11-26 00:00:00', '2015-11-26 00:00:00', 'role', '5', 'a:1:{i:0;s:18:"ROLE_GALLERY_ADMIN";}', 'gallery', '0');

INSERT INTO `sf_guard_group` (`id`, `name`, `description`, `created_at`, `updated_at`, `type`, `sort_order`, `roles`, `module_type`, `is_readonly`) VALUES (16, 'Gallery', 'Gallery', '2015-11-26 00:00:00', '2015-11-26 00:00:00', 'club', '4', 'a:1:{i:0;s:12:"ROLE_GALLERY";}', 'gallery', '0');

THERE CAN BE ISSUE WHILE MOVING TO DB IF ENTRY EXIST IN TABLE
================================================================
ALTER TABLE `fg_em_calendar_selected_categories` DROP FOREIGN KEY `fg_em_calendar_selected_categories_ibfk_2`;
ALTER TABLE `fg_em_calendar_selected_categories` CHANGE `category_id` `category_id` INT(11) NULL DEFAULT NULL;
ALTER TABLE `fg_em_calendar_selected_categories` ADD CONSTRAINT `fk_fg_calendar_selected_areas_fg_calendar_category1_idx` FOREIGN KEY (`category_id`) REFERENCES `fg_em_calendar_category`(`id`) ON DELETE SET NULL ON UPDATE NO ACTION;

ALTER TABLE `fg_file_manager_version` ADD `mime_type` TEXT NULL AFTER `filename`;
=========================================================================================
ALTER TABLE `fg_gm_items`  ADD `file_name` VARCHAR(255) NULL  AFTER `video_thumb_url`,  
ADD `mime_type` VARCHAR(255) NULL  AFTER `file_name`,  
ADD `file_size` INT(11) NULL  AFTER `mime_type`,  
ADD `created_by` INT NULL  AFTER `file_size`,  
ADD `updated_by` INT NULL  AFTER `created_by`,  
ADD `created_on` DATETIME NULL DEFAULT NULL  AFTER `updated_by`,  
ADD `updated_on` DATETIME NULL DEFAULT NULL  AFTER `created_on`, 
ADD   INDEX  (`created_by`, `updated_by`) ;
 
ALTER TABLE `fg_gm_items` ADD FOREIGN KEY (`created_by`) REFERENCES `fg_cm_contact`(`id`) ON DELETE CASCADE ON UPDATE NO ACTION; ALTER TABLE `fg_gm_items` ADD FOREIGN KEY (`updated_by`) REFERENCES `fg_cm_contact`(`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

