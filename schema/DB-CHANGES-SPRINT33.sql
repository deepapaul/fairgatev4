-- FAIR-1171
ALTER TABLE `fg_cn_newsletter` 
ADD COLUMN `template_updated` DATETIME NULL AFTER `resent_status`


-- FAIR-1294
-----------------------------------------------------------------------------------------------------------------------------------
-- update handleAttributes()
ALTER TABLE `fg_cm_club_attribute`  ADD `availability_contact` ENUM('changable','visible','not_available') NULL DEFAULT 'changable'  AFTER `is_required_fedmember_club`,  ADD `availability_groupadmin` ENUM('changable','visible','not_available') NULL DEFAULT 'changable'  AFTER `availability_contact`;
ALTER TABLE `fg_temp_attribute` DROP `isChangableContact`, DROP `isChangableTeamadmin`;
ALTER TABLE `fg_temp_attribute` CHANGE `isVisibleContact` `availabilityContact` ENUM('changable','visible','not_available') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'changable';
ALTER TABLE `fg_temp_attribute` CHANGE `isVisibleTeamadmin` `availabilityGroupadmin` ENUM('changable','visible','not_available') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'changable';
-- UPDATE existing entries
UPDATE fg_cm_club_attribute SET availability_contact = (CASE WHEN (is_visible_contact=1 AND is_changable_contact=1) THEN 'changable' WHEN is_visible_contact=1 THEN 'visible' ELSE 'not_available' END);
UPDATE fg_cm_club_attribute SET availability_groupadmin = (CASE WHEN (is_visible_teamadmin=1 AND is_changable_teamadmin=1) THEN 'changable' WHEN is_visible_teamadmin=1 THEN 'visible' ELSE 'not_available' END);
UPDATE fg_cm_club_attribute SET privacy_contact = (CASE WHEN (availability_groupadmin='not_available' OR availability_contact='not_available') THEN 'private' ELSE privacy_contact END);



-- FAIR-1352, FAIR-1320
-------------------------------------------------------------------------------------------------------------------------------------
ALTER TABLE `fg_cm_change_log` CHANGE `kind` `kind` ENUM('data','assignment','linked contacts','user rights','notes','documents','login','emails','contact type','contact status','password','communication','system') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'data';
-- update importContact()
-- insertSubscriberLog() from Rejith - execute thi stored procedure to correct already existing lg entries

-- IMPORT STORED PROcedure to be updated
==========================================================================================================================================================================================
ALTER TABLE `fg_cn_newsletter_content` CHANGE COLUMN `content_type` `content_type` ENUM('INTRO','CLOSING','NEWS','TEAM NEWS','ARTICLE','IMAGE','OTHER','SPONSOR', 'SPONSOR ABOVE','SPONSOR BOTTOM') NULL DEFAULT NULL  

ALTER TABLE `fg_cn_newsletter_template` 
CHANGE COLUMN `created_by` `created_by` INT(11) NULL DEFAULT NULL  AFTER `edited_by` , 
ADD COLUMN `sender_name` VARCHAR(255) NULL DEFAULT NULL  AFTER `article_display` , 
ADD COLUMN `sender_email` VARCHAR(255) NULL DEFAULT NULL  AFTER `sender_name` , 
ADD COLUMN `salutation_type` ENUM('INDIVIDUAL','SAME','NONE') NULL DEFAULT 'INDIVIDUAL'  AFTER `sender_email` , 
ADD COLUMN `salutation` VARCHAR(255) NULL DEFAULT NULL  AFTER `salutation_type` , 
ADD COLUMN `language_selection` ENUM('ALL','SELECTED') NULL DEFAULT 'SELECTED'  AFTER `salutation` 

CREATE  TABLE IF NOT EXISTS `fg_cn_newsletter_template_lang` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `language_code` VARCHAR(2) NULL DEFAULT NULL ,
  `template_id` INT(11) NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_fg_cn_newsletter_publish_lang_fg_club_languages1` (`language_code` ASC) ,
  INDEX `fk_fg_cn_newsletter_template_lang_fg_cn_newsletter_template_idx` (`template_id` ASC) ,
  CONSTRAINT `fk_fg_cn_newsletter_template_lang_fg_cn_newsletter_template1`
    FOREIGN KEY (`template_id` )
    REFERENCES `fg_cn_newsletter_template` (`id` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 0
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci
==========================================================================================================================================================================================
CREATE TABLE IF NOT EXISTS `fg_cn_newsletter_template_sponsor` (
`id` int(11) NOT NULL,
  `template_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT NULL,
  `position` enum('ABOVE','BOTTOM','CONTENT') DEFAULT 'ABOVE',
  `sponsor_ad_area_id` int(11) DEFAULT NULL,
  `sponsor_ad_width` varchar(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

ALTER TABLE `fg_cn_newsletter_template_sponsor`
 ADD PRIMARY KEY (`id`), ADD KEY `sponsor_ad_area_id` (`sponsor_ad_area_id`), ADD KEY `template_id` (`template_id`);

ALTER TABLE `fg_cn_newsletter_template_sponsor` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `fg_cn_newsletter_template_sponsor`
ADD CONSTRAINT `fg_cn_newsletter_template_sponsor_ibfk_2` FOREIGN KEY (`sponsor_ad_area_id`) REFERENCES `fg_sm_ad_area` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION,
ADD CONSTRAINT `fg_cn_newsletter_template_sponsor_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `fg_cn_newsletter_template` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;
==========================================================================================================================================================================================

CREATE  TABLE IF NOT EXISTS `fg_cn_newsletter_template_services` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `template_sponsor_id` INT(11) NOT NULL ,
  `services_id` INT(11) NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_fg_cn_newsletter_template_services_fg_cn_newsletter_temp_idx` (`template_sponsor_id` ASC) ,
  INDEX `fk_fg_cn_newsletter_template_services_fg_sm_services1_idx` (`services_id` ASC) ,
  CONSTRAINT `fk_fg_cn_newsletter_template_services_fg_cn_newsletter_templa1`
    FOREIGN KEY (`template_sponsor_id` )
    REFERENCES `fg_cn_newsletter_template_sponsor` (`id` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_fg_cn_newsletter_template_services_fg_sm_services1`
    FOREIGN KEY (`services_id` )
    REFERENCES `fg_sm_services` (`id` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 0
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci
==========================================================================================================================================================================================