-- PROCEDURES AND FUNCTIONS
------------------------------------------------------
-- salutationText()
-- salutationTextOwnLocale()
-- insertNewsletterContactsToSpoolv4()
-- archiveContactsV4()
-- updateRecipientContacts()
-- updateContacts();
-- importContacts()

--FAIR-1407
UPDATE `fg_cm_attribute` SET `fieldname` = 'Profilbild', `fieldname_short` = 'Pic' WHERE `fg_cm_attribute`.`id` = 21;

UPDATE `fg_cm_attribute_i18n` SET `fieldname_lang` = 'Profilbild', `fieldname_short_lang` = 'Pic' WHERE `fg_cm_attribute_i18n`.`id` = 21 AND `fg_cm_attribute_i18n`.`lang` = 'de';

UPDATE `fg_cm_attribute_i18n` SET `fieldname_lang` = 'Profilbild', `fieldname_short_lang` = 'Pic' WHERE `fg_cm_attribute_i18n`.`id` = 21 AND `fg_cm_attribute_i18n`.`lang` = 'en';

UPDATE `fg_cm_attribute_i18n` SET `fieldname_lang` = 'Image de profil', `fieldname_short_lang` = 'Image' WHERE `fg_cm_attribute_i18n`.`id` = 21 AND `fg_cm_attribute_i18n`.`lang` = 'fr';

UPDATE `fg_cm_attribute_i18n` SET `fieldname_lang` = 'Immagine del profilo', `fieldname_short_lang` = 'Pic' WHERE `fg_cm_attribute_i18n`.`id` = 21 AND `fg_cm_attribute_i18n`.`lang` = 'it';


-- FAIR-1513
---------------------------------------------
ALTER TABLE `fg_cn_newsletter_receiver_log` ADD `system_language` VARCHAR(8) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'de' ;

ALTER TABLE `fg_mail_message` DROP `user_culture`;
ALTER TABLE `fg_mail_message` DROP `admin_receiver_id`;
ALTER TABLE `fg_mail_message` DROP `email_flag`;
-- ALTER TABLE fg_mail_message DROP INDEX newsletter_id_2;

-- ALTER TABLE `fg_mail_message` DROP `salutation`;
-- add new unique index on newsletter_id, email
ALTER TABLE `fg_cn_recepients_mandatory` ADD `system_lang` VARCHAR(8) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ;


-------------------------------- CALENDAR DB CHANGES ---------------------------------


INSERT INTO `sf_guard_group` (`id`, `name`, `description`, `created_at`, `updated_at`, `type`, `sort_order`, `roles`, `module_type`, `is_readonly`) VALUES (NULL, 'CalendarAdmin', 'CalendarAdmin', '2015-11-26 00:00:00', '2015-11-26 00:00:00', 'role', '5', 'a:1:{i:0;s:19:"ROLE_CALENDAR_ADMIN";}', 'calender', '0');
INSERT INTO `sf_guard_group` (`id`, `name`, `description`, `created_at`, `updated_at`, `type`, `sort_order`, `roles`, `module_type`, `is_readonly`) VALUES (NULL, 'Calendar', 'Calendar', '2015-11-26 00:00:00', '2015-11-26 00:00:00', 'club', '4', 'a:1:{i:0;s:13:"ROLE_CALENDAR";}', 'calender', '0');

ALTER TABLE  `fg_club` ADD  `calendar_color_code` VARCHAR( 10 ) NULL DEFAULT NULL AFTER  `settings_updated`;
ALTER TABLE  `fg_rm_role` ADD  `calendar_color_code` VARCHAR( 10 ) NULL DEFAULT NULL AFTER  `is_deactivated_forum`;
ALTER TABLE  `fg_rm_category` ADD  `calendar_color_code` VARCHAR( 10 ) NULL DEFAULT NULL AFTER  `is_fed_category`;

-- phpMyAdmin SQL Dump
-- version 4.2.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 15, 2015 at 10:04 AM
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
-- Table structure for table `fg_em_calendar`
--

CREATE TABLE IF NOT EXISTS `fg_em_calendar` (
`id` int(11) NOT NULL,
  `club_id` int(11) NOT NULL,
  `calendar_rules_id` int(11) DEFAULT NULL,
  `scope` enum('PUBLIC','INTERNAL','GROUP') DEFAULT 'PUBLIC',
  `share_with_lower` tinyint(1) NOT NULL DEFAULT '0',
  `is_allday` tinyint(1) DEFAULT '0',
  `is_repeat` tinyint(1) NOT NULL DEFAULT '0',
  `repeat_untill_date` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `fg_em_calendar_category`
--

CREATE TABLE IF NOT EXISTS `fg_em_calendar_category` (
`id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `club_id` int(11) NOT NULL,
  `sort_order` int(11) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `fg_em_calendar_category_i18n`
--

CREATE TABLE IF NOT EXISTS `fg_em_calendar_category_i18n` (
  `id` int(11) NOT NULL,
  `title_lang` varchar(255) DEFAULT NULL,
  `lang` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fg_em_calendar_details`
--

CREATE TABLE IF NOT EXISTS `fg_em_calendar_details` (
`id` int(11) NOT NULL,
  `calendar_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `untill` datetime DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `location_latitude` double DEFAULT NULL,
  `location_longitude` double DEFAULT NULL,
  `is_show_in_googlemap` tinyint(1) DEFAULT '0',
  `url` varchar(45) DEFAULT NULL,
  `description` text,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0-REPEATED, 1-NONREPEATED, 2- DELETED'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `fg_em_calendar_details_i18n`
--

CREATE TABLE IF NOT EXISTS `fg_em_calendar_details_i18n` (
  `id` int(11) NOT NULL,
  `title_lang` varchar(255) DEFAULT NULL,
  `lang` varchar(45) NOT NULL,
  `desc_lang` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fg_em_calendar_rules`
--

CREATE TABLE IF NOT EXISTS `fg_em_calendar_rules` (
`id` int(11) NOT NULL,
  `FREQ` varchar(45) DEFAULT NULL,
  `BYDAY` varchar(45) DEFAULT NULL,
  `INTERVAL` varchar(45) DEFAULT NULL,
  `BYMONTHDAY` varchar(45) DEFAULT NULL,
  `BYMONTH` varchar(45) DEFAULT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `fg_em_calendar_selected_areas`
--

CREATE TABLE IF NOT EXISTS `fg_em_calendar_selected_areas` (
`id` int(11) NOT NULL,
  `calendar_details_id` int(11) NOT NULL,
  `role_id` int(11) DEFAULT NULL,
  `is_club` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `fg_em_calendar_selected_categories`
--

CREATE TABLE IF NOT EXISTS `fg_em_calendar_selected_categories` (
`id` int(11) NOT NULL,
  `calendar_details_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `fg_em_calendar`
--
ALTER TABLE `fg_em_calendar`
 ADD PRIMARY KEY (`id`), ADD KEY `fk_fg_calendar_fg_club1_idx` (`club_id`), ADD KEY `fk_fg_calendar_fg_cm_contact1_idx` (`created_by`), ADD KEY `fk_fg_calendar_fg_cm_contact2_idx` (`updated_by`), ADD KEY `fk_fg_em_calendar_fg_em_calendar_rules1_idx` (`calendar_rules_id`);

--
-- Indexes for table `fg_em_calendar_category`
--
ALTER TABLE `fg_em_calendar_category`
 ADD PRIMARY KEY (`id`), ADD KEY `fk_fg_calendar_category_fg_club1_idx` (`club_id`);

--
-- Indexes for table `fg_em_calendar_category_i18n`
--
ALTER TABLE `fg_em_calendar_category_i18n`
 ADD PRIMARY KEY (`id`,`lang`), ADD KEY `fk_fg_calendar_category_i18n_fg_calendar_category1_idx` (`id`);

--
-- Indexes for table `fg_em_calendar_details`
--
ALTER TABLE `fg_em_calendar_details`
 ADD PRIMARY KEY (`id`), ADD KEY `fk_fg_calendar_fg_cm_contact2_idx` (`updated_by`), ADD KEY `fk_fg_em_calendar_details_fg_em_calendar1_idx` (`calendar_id`), ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `fg_em_calendar_details_i18n`
--
ALTER TABLE `fg_em_calendar_details_i18n`
 ADD PRIMARY KEY (`lang`,`id`), ADD KEY `fk_fg_em_calendar_details_i18n_fg_em_calendar_details1_idx` (`id`);

--
-- Indexes for table `fg_em_calendar_rules`
--
ALTER TABLE `fg_em_calendar_rules`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fg_em_calendar_selected_areas`
--
ALTER TABLE `fg_em_calendar_selected_areas`
 ADD PRIMARY KEY (`id`), ADD KEY `fk_fg_calendar_selected_areas_fg_calendar1_idx` (`calendar_details_id`), ADD KEY `fk_fg_calendar_selected_areas_fg_rm_role1_idx` (`role_id`);

--
-- Indexes for table `fg_em_calendar_selected_categories`
--
ALTER TABLE `fg_em_calendar_selected_categories`
 ADD PRIMARY KEY (`id`), ADD KEY `fk_fg_calendar_selected_areas_fg_calendar1_idx` (`calendar_details_id`), ADD KEY `fk_fg_calendar_selected_areas_fg_calendar_category1_idx` (`category_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `fg_em_calendar`
--
ALTER TABLE `fg_em_calendar`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `fg_em_calendar_category`
--
ALTER TABLE `fg_em_calendar_category`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `fg_em_calendar_details`
--
ALTER TABLE `fg_em_calendar_details`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `fg_em_calendar_rules`
--
ALTER TABLE `fg_em_calendar_rules`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `fg_em_calendar_selected_areas`
--
ALTER TABLE `fg_em_calendar_selected_areas`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `fg_em_calendar_selected_categories`
--
ALTER TABLE `fg_em_calendar_selected_categories`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `fg_em_calendar`
--
ALTER TABLE `fg_em_calendar`
ADD CONSTRAINT `fg_em_calendar_ibfk_2` FOREIGN KEY (`calendar_rules_id`) REFERENCES `fg_em_calendar_rules` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
ADD CONSTRAINT `fg_em_calendar_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `fg_cm_contact` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_fg_em_calendar_fg_club10` FOREIGN KEY (`club_id`) REFERENCES `fg_club` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_fg_em_calendar_fg_cm_contact1` FOREIGN KEY (`created_by`) REFERENCES `fg_cm_contact` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `fg_em_calendar_category`
--
ALTER TABLE `fg_em_calendar_category`
ADD CONSTRAINT `fk_fg_em_calendar_category_fg_club1` FOREIGN KEY (`club_id`) REFERENCES `fg_club` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `fg_em_calendar_category_i18n`
--
ALTER TABLE `fg_em_calendar_category_i18n`
ADD CONSTRAINT `fk_fg_em_calendar_category_i18n_fg_em_calendar_category1` FOREIGN KEY (`id`) REFERENCES `fg_em_calendar_category` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `fg_em_calendar_details`
--
ALTER TABLE `fg_em_calendar_details`
ADD CONSTRAINT `fk_fg_em_calendar_details_fg_em_calendar1` FOREIGN KEY (`calendar_id`) REFERENCES `fg_em_calendar` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
ADD CONSTRAINT `fg_em_calendar_details_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `fg_cm_contact` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
ADD CONSTRAINT `fg_em_calendar_details_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `fg_cm_contact` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `fg_em_calendar_details_i18n`
--
ALTER TABLE `fg_em_calendar_details_i18n`
ADD CONSTRAINT `fk_fg_em_calendar_details_i18n_fg_em_calendar_details1` FOREIGN KEY (`id`) REFERENCES `fg_em_calendar_details` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `fg_em_calendar_selected_areas`
--
ALTER TABLE `fg_em_calendar_selected_areas`
ADD CONSTRAINT `fg_em_calendar_selected_areas_ibfk_1` FOREIGN KEY (`calendar_details_id`) REFERENCES `fg_em_calendar_details` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_fg_em_calendar_selected_areas_fg_rm_role1` FOREIGN KEY (`role_id`) REFERENCES `fg_rm_role` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `fg_em_calendar_selected_categories`
--
ALTER TABLE `fg_em_calendar_selected_categories`
ADD CONSTRAINT `fg_em_calendar_selected_categories_ibfk_3` FOREIGN KEY (`calendar_details_id`) REFERENCES `fg_em_calendar_details` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
ADD CONSTRAINT `fg_em_calendar_selected_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `fg_em_calendar_category` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
