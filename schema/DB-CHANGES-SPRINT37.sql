===============================================================================================================================================================================

CREATE TABLE IF NOT EXISTS `fg_em_calendar_details_attachments` (
  `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `calendar_detail_id` int(11) NOT NULL,
  `file` varchar(999) DEFAULT NULL,
  `size` int(11) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

ALTER TABLE `fg_em_calendar_details_attachments` ADD INDEX(`calendar_detail_id`);
ALTER TABLE `fg_file_manager_log` CHANGE `kind` `kind` ENUM('Changed','Replaced','Added','Flagged','Reverted') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

ALTER TABLE `fg_em_calendar_details_attachments` ADD FOREIGN KEY (`calendar_detail_id`) REFERENCES `fairgate_v4`.`fg_em_calendar_details`(`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

attachment table fileds delete:
ALTER TABLE `fg_em_calendar_details_attachments` DROP `file`, DROP `size`;

filemanager enum:
ALTER TABLE `fg_file_manager` CHANGE `source` `source` ENUM('SIMPLE EMAIL','NEWSLETTER','FILEMANAGER','CALENDAR') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'FILEMANAGER';

alter table:
ALTER TABLE `fg_em_calendar_details_attachments` ADD `file_manager_id` INT(11) NOT NULL AFTER `calendar_detail_id`;
ALTER TABLE `fg_em_calendar_details_attachments` ADD INDEX( `file_manager_id`);
ALTER TABLE `fg_em_calendar_details_attachments` ADD CONSTRAINT `fg_em_calendar_details_attachments_ibfk_2` FOREIGN KEY (`file_manager_id`) REFERENCES `fairgate_v4`.`fg_file_manager`(`id`) ON DELETE CASCADE ON UPDATE NO ACTION;
 
 
FILE MANAGER SCRIPT NEEDS TO RUN