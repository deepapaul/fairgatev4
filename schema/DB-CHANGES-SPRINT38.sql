=====================================================Sprint 38=====================================================
ALTER TABLE `fg_cn_newsletter_article_media` ADD `file_manager_id` INT(11) NULL DEFAULT NULL ;
ALTER TABLE `fg_cn_newsletter_article_media` 
  ADD CONSTRAINT `fk_fg_file_manager`
  FOREIGN KEY (`file_manager_id` )
  REFERENCES `fg_file_manager` (`id` )
  ON DELETE NO ACTION
  ON UPDATE NO ACTION
, ADD INDEX `fk_fg_file_manager_idx` (`file_manager_id` ASC) ;


ALTER TABLE `fg_cn_newsletter_content` ADD `file_manager_id` INT(11) NULL DEFAULT NULL ;
ALTER TABLE `fg_cn_newsletter_content` 
  ADD CONSTRAINT `fk_fg_file_manager_image`
  FOREIGN KEY (`file_manager_id` )
  REFERENCES `fg_file_manager` (`id` )
  ON DELETE NO ACTION
  ON UPDATE NO ACTION
, ADD INDEX `fk_fg_file_manager_image_idx` (`file_manager_id` ASC) ;


ALTER TABLE `fg_cn_newsletter_article_documents` ADD `file_manager_id` INT(11) NULL DEFAULT NULL ;
ALTER TABLE `fg_cn_newsletter_article_documents` 
  ADD CONSTRAINT `fk_fg_file_manager_document`
  FOREIGN KEY (`file_manager_id` )
  REFERENCES `fg_file_manager` (`id` )
  ON DELETE NO ACTION
  ON UPDATE NO ACTION
, ADD INDEX `fk_fg_file_manager_document_idx` (`file_manager_id` ASC) ;


CREATE  TABLE `fg_cn_newsletter_file_map` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `file_pattern` VARCHAR(250) NULL ,
  `file_manager_id` INT(11) NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_newsletter_file_map_idx` (`file_manager_id` ASC) ,
  CONSTRAINT `fk_newsletter_file_map`
    FOREIGN KEY (`file_manager_id` )
    REFERENCES `fg_file_manager` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);

ALTER TABLE `fg_cn_newsletter_article_media` CHANGE `media_type` `media_type` ENUM('gallery','image','video','attachments') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;

Call Procedure_V4MasterTableChangeField;
================================================================================ HOTFIX ======================================================================================

