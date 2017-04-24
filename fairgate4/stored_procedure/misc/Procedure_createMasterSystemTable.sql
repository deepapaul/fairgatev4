-- To create and insert data to master system table for all clubs for migration from v3
DROP PROCEDURE `createMasterSystemTable`//
CREATE DEFINER=`admin`@`localhost` PROCEDURE `createMasterSystemTable`()
BEGIN
	DECLARE cc_attribute_id, record_not_found, loop_counter INTEGER DEFAULT 0; 
	DECLARE cc_fieldtype, cc_input_type TEXT DEFAULT "";
	DECLARE club_attribute_cursor CURSOR FOR SELECT id, fieldtype, input_type FROM fg_cm_attribute WHERE is_system_field =1 OR is_fairgate_field = 1;	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET record_not_found = 1;

	START TRANSACTION;
		SET @query = CONCAT('DROP TABLE IF EXISTS `master_system`');
		SET @query1 = CONCAT('CREATE TABLE `master_system`(`club_id` int(11), `contact_id` int(11)');
		SET @alterquery = CONCAT('ALTER TABLE `master_system` ADD FOREIGN KEY (`contact_id`) REFERENCES `fg_cm_contact`(`id`) ON DELETE CASCADE ON UPDATE NO ACTION, ADD FOREIGN KEY (`club_id`) REFERENCES `fg_club`(`id`) ON DELETE CASCADE ON UPDATE NO ACTION');
		SET @query2 = CONCAT(' AS (select `fg_cm_contact`.`club_id` AS club_id, `fg_cm_contact`.`id` AS `contact_id`');
		PREPARE stmt FROM @query;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;	
		
		OPEN club_attribute_cursor;
			loop_attribute: LOOP
				FETCH club_attribute_cursor INTO cc_attribute_id, cc_fieldtype, cc_input_type;				
					
					SET @query2 = CONCAT(@query2, ',');
					
					IF record_not_found THEN
						SET record_not_found = 0;
						LEAVE loop_attribute;
					END IF;
					
					CASE cc_input_type
						WHEN 'multiline' THEN
							BEGIN
								SET @query1 = CONCAT(@query1, ', `', cc_attribute_id, '` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ');
							END;	
						WHEN 'date' THEN
							BEGIN								
								IF (cc_fieldtype = 'date') THEN 
									SET @query1 = CONCAT(@query1, ', `', cc_attribute_id, '` DATETIME NULL DEFAULT NULL');
								ELSE
									SET @query1 = CONCAT(@query1, ', `', cc_attribute_id, '` VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL');
								END IF;							
								
							END;
						WHEN 'fileupload' THEN
							BEGIN	
								SET @query1 = CONCAT(@query1, ', `', cc_attribute_id, '` VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL');
							END;
						WHEN 'imageupload' THEN
							BEGIN	
								SET @query1 = CONCAT(@query1, ', `', cc_attribute_id, '` VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL');
							END;							
						WHEN 'singleline' THEN
							BEGIN
								SET @query1 = CONCAT(@query1, ', `', cc_attribute_id, '` VARCHAR(160) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL');
							END;								
						ELSE
							BEGIN
								SET @query1 = CONCAT(@query1, ', `', cc_attribute_id, '` VARCHAR(160) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL');
							END;
						END CASE;	
					
					CASE cc_fieldtype
						WHEN 'int' THEN
							BEGIN
								SET @query2 = CONCAT(@query2, '(select `fg_cm_attribute_value_int`.`value` AS `value` from `fg_cm_attribute_value_int` where ((`fg_cm_attribute_value_int`.`contact_id` = `fg_cm_contact`.`id`) and (`fg_cm_attribute_value_int`.`attribute_id` = ',cc_attribute_id, '))) AS `',cc_attribute_id,'`');
							END;
						WHEN 'date' THEN
							BEGIN
								SET @query2 = CONCAT(@query2, '(select `fg_cm_attribute_value_date`.`value` AS `value` from `fg_cm_attribute_value_date` where ((`fg_cm_attribute_value_date`.`contact_id` = `fg_cm_contact`.`id`) and (`fg_cm_attribute_value_date`.`attribute_id` = ',cc_attribute_id, '))) AS `',cc_attribute_id,'`');
							END;
						WHEN 'text' THEN
							BEGIN
								SET @query2 = CONCAT(@query2, '(select `fg_cm_attribute_value_text`.`value` AS `value` from `fg_cm_attribute_value_text` where ((`fg_cm_attribute_value_text`.`contact_id` = `fg_cm_contact`.`id`) and (`fg_cm_attribute_value_text`.`attribute_id` = ',cc_attribute_id, '))) AS `',cc_attribute_id,'`');
							END;
					END CASE;			
			END LOOP loop_attribute;	
		CLOSE club_attribute_cursor;
	
		SET @query1 = CONCAT(@query1, ',PRIMARY KEY (`club_id`,`contact_id`)) ENGINE = InnoDB DEFAULT  CHARACTER SET utf8 COLLATE utf8_general_ci ');
		-- SET @query2 = CONCAT(TRIM(BOTH ',' FROM @query2) , ' from `fg_cm_contact` where ((`fg_cm_contact`.`club_id` = ',clubId ,') and (`fg_cm_contact`.`is_permanent_delete` = 0))) ');
		SET @query2 = CONCAT(TRIM(BOTH ',' FROM @query2) , ' from `fg_cm_contact` where (`fg_cm_contact`.`is_permanent_delete` = 0)) ');
		SET @q  = CONCAT(@query1, @query2);
		
		PREPARE stmt FROM @q;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

		PREPARE stmt FROM @alterquery;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
	COMMIT;
END
