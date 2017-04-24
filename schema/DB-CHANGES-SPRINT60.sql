----------------- Update Procedure -------------
--------------------------------------------------
--archiveContactsV4

--------------------------- FAIRDEV-295: Allow removing Fairgate logo from newsletter ------------------------
ALTER TABLE `fg_club` ADD `has_nl_fairgatelogo` TINYINT(1) NOT NULL DEFAULT '1' AFTER `has_promobox`;
--------------------------------------------------------------------------------------------------------------
---FAIRDEV-344  Renaming of themes

UPDATE `fg_tm_theme` SET `title` = 'Vancouver' WHERE `title` = 'Theme 1';
UPDATE `fg_tm_theme` SET `title` = 'London' WHERE `title` = 'Theme 2';
-----------------------------------------------------------------------------------------------------------------
-- FAIRDEV-367 Remove "(Korr.)" appendix from correspondance address fields column heading in contact table element

UPDATE fg_cms_contact_table_columns_i18n 
SET fg_cms_contact_table_columns_i18n.title_lang = REPLACE(fg_cms_contact_table_columns_i18n.title_lang, '(Korr.)', '')
WHERE fg_cms_contact_table_columns_i18n.`id` IN (SELECT fg_cms_contact_table_columns.id FROM `fg_cms_contact_table_columns` INNER JOIN fg_cms_contact_table ON fg_cms_contact_table.id = fg_cms_contact_table_columns.table_id WHERE fg_cms_contact_table.display_type = 'table' AND attribute_id IN (47, 77, 78, 79, 106, 71785) ) AND (fg_cms_contact_table_columns_i18n.`title_lang` LIKE '%(Korr.)%' ) 

UPDATE fg_cms_contact_table_columns_i18n 
SET fg_cms_contact_table_columns_i18n.title_lang = REPLACE(fg_cms_contact_table_columns_i18n.title_lang, '(corr.)', '')
 WHERE fg_cms_contact_table_columns_i18n.`id` IN (SELECT fg_cms_contact_table_columns.id FROM `fg_cms_contact_table_columns` INNER JOIN fg_cms_contact_table ON fg_cms_contact_table.id = fg_cms_contact_table_columns.table_id WHERE fg_cms_contact_table.display_type = 'table' AND attribute_id IN (47, 77, 78, 79, 106, 71785) ) AND (fg_cms_contact_table_columns_i18n.`title_lang` LIKE '%(corr.)%' ) 
 
UPDATE `fg_cms_contact_table_columns` INNER JOIN fg_cms_contact_table ON fg_cms_contact_table.id = fg_cms_contact_table_columns.table_id 
SET fg_cms_contact_table_columns.title = REPLACE(fg_cms_contact_table_columns.title, '(Korr.)', '') 
WHERE `title` LIKE '%Korr%' and fg_cms_contact_table.display_type = 'table'
AND attribute_id IN (47, 77, 78, 79, 106, 71785)

UPDATE `fg_cms_contact_table_columns` INNER JOIN fg_cms_contact_table ON fg_cms_contact_table.id = fg_cms_contact_table_columns.table_id 
SET fg_cms_contact_table_columns.title = REPLACE(fg_cms_contact_table_columns.title, '(corr.)', '') 
WHERE `title` LIKE '%corr%' and fg_cms_contact_table.display_type = 'table'
AND attribute_id IN (47, 77, 78, 79, 106, 71785)

UPDATE fg_cms_contact_table INNER JOIN fg_cms_contact_table_columns ON fg_cms_contact_table.id = fg_cms_contact_table_columns.table_id  
SET fg_cms_contact_table.column_data = REPLACE(fg_cms_contact_table.`column_data`, '(Korr.)', '') 
WHERE fg_cms_contact_table.`column_data` LIKE '%Korr%' and fg_cms_contact_table.display_type = 'table'
AND fg_cms_contact_table_columns.attribute_id IN (47, 77, 78, 79, 106, 71785)

UPDATE fg_cms_contact_table INNER JOIN fg_cms_contact_table_columns ON fg_cms_contact_table.id = fg_cms_contact_table_columns.table_id  
SET fg_cms_contact_table.column_data = REPLACE(fg_cms_contact_table.`column_data`, '(corr.)', '') 
WHERE fg_cms_contact_table.`column_data` LIKE '%corr%' and fg_cms_contact_table.display_type = 'table'
AND fg_cms_contact_table_columns.attribute_id IN (47, 77, 78, 79, 106, 71785)


==============================================================================================================================
==============================================================================================================================
-------------------------------------ADMIN DATABASE CHANGES--------------------------------------------------------------
==============================================================================================================================
==============================================================================================================================
----------------------------------------------------sublevelClubs -------------------------------------------
DELIMITER $$
CREATE FUNCTION `clubCount`(`clubId` INT) RETURNS int(11)
BEGIN

        DECLARE clubTotalCount int DEFAULT 0;
        SELECT count(c.id) INTO  clubTotalCount FROM (SELECT  sublevelClubs(id) AS id, @level AS level FROM (SELECT  @start_with := clubId,@id := @start_with,@level := 0) vars, fg_club WHERE @id IS NOT NULL) ho JOIN fg_club c ON c.id = ho.id WHERE c.is_deleted=0;
        RETURN clubTotalCount;
          
END$$
DELIMITER ;
-------------------------------------------------getClassAssignmentCount--------------------------------------------------------------------
DELIMITER $$
CREATE FUNCTION `getClassAssignmentCount`(classId INT, clubId INT) RETURNS int(11)
BEGIN
	DECLARE assignmentCount  INTEGER DEFAULT 0;

	select count(cca.id) INTO assignmentCount from fg_club_class_assignment AS cca WHERE cca.class_id =classId AND (cca.club_id IN(SELECT c.id FROM (SELECT  sublevelClubs(id) AS id, @level AS level FROM (SELECT  @start_with := clubId,@id := @start_with,@level := 0) vars, fg_club WHERE @id IS NOT NULL) ho JOIN fg_club c ON c.id = ho.id ));
	RETURN assignmentCount;
END$$
DELIMITER ;
-----------------------------------------sublevelClubs-----------------------------------------------------
DELIMITER $$
CREATE DEFINER=`admin`@`localhost` FUNCTION `sublevelClubs`(value INT) RETURNS int(11)
    READS SQL DATA
BEGIN
        DECLARE _id INT;
        DECLARE _parent INT;
        DECLARE _next INT;
        DECLARE CONTINUE HANDLER FOR NOT FOUND SET @id = NULL;

        SET _parent = @id;
        SET _id = -1;

        IF @id IS NULL THEN
                RETURN NULL;
        END IF;

        LOOP
                SELECT  MIN(id)
                INTO    @id
                FROM    fg_club
                WHERE   parent_club_id = _parent
                        AND id > _id;
                IF @id IS NOT NULL OR _parent = @start_with THEN
                        SET @level = @level + 1;
                        RETURN @id;
                END IF;
                SET @level := @level - 1;
                SELECT  id, parent_club_id
                INTO    _id, _parent
                FROM    fg_club
                WHERE   id = _parent;
        END LOOP;       
END$$
DELIMITER ;

=============================================
Solution Admin
INSERT INTO `sf_guard_group` (`id`, `name`, `description`, `created_at`, `updated_at`, `type`, `sort_order`, `roles`, `module_type`, `is_readonly`) VALUES (22, 'Solution Admin', 'SolutionAdmin', '2017-03-27 00:00:00', '2017-03-27 00:00:00', 'fairgate', '3', 'a:1:{i:0;s:19:"ROLE_SOLUTION_ADMIN";}', 'all', '0');
New procedure createSolutionAdmin
