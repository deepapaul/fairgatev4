DROP PROCEDURE IF EXISTS `alterMasterClubTableFields`//
CREATE PROCEDURE `alterMasterClubTableFields`(IN fieldIds TEXT, IN clubId INTEGER, IN clubType VARCHAR(15), IN action VARCHAR(15))
BEGIN
	DECLARE fieldId, fieldInputType VARCHAR(30) DEFAULT 0;
	DECLARE record_not_found INTEGER DEFAULT 0;
	DECLARE deleteFieldIds, deleteFieldText, createFieldText, updateFieldText, fieldTypeText, alterTableText, test TEXT DEFAULT "";
-- 	Assign name for Mysql exception "General error: 1615 Prepared statement needs to be re-prepared"
	DECLARE mysql_prepare_exception CONDITION FOR 1615;
	DECLARE fieldCursor CURSOR FOR  SELECT id, input_type FROM `fg_cm_attribute` WHERE club_id = clubId AND FIND_IN_SET(id, fieldIds);
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET record_not_found = 1;	
	OPEN fieldCursor;
		fieldCursorLoop: LOOP
			FETCH fieldCursor INTO fieldId, fieldInputType;
			IF record_not_found THEN
				SET record_not_found = 0;
				LEAVE fieldCursorLoop;
			END IF;	
			SET test = CONCAT(test,'loop');
			IF (action = 'delete') THEN
				SET deleteFieldIds = CONCAT(deleteFieldIds, IF(deleteFieldIds ='', '', ','), fieldId);
				SET deleteFieldText = CONCAT(deleteFieldText, IF(deleteFieldText ='', '', ','), 'DROP `',fieldId,'`');				
			ELSE
				CASE 
					WHEN fieldInputType = 'multiline' OR fieldInputType = 'checkbox' OR fieldInputType = 'radio' OR fieldInputType = 'select' THEN
						BEGIN
							SET fieldTypeText = CONCAT('`', fieldId, '` TEXT NULL DEFAULT NULL ');
						END;	
					WHEN fieldInputType = 'date' THEN
						BEGIN								
							SET fieldTypeText = CONCAT('`', fieldId, '` date DEFAULT NULL DEFAULT NULL');
						END;
					WHEN fieldInputType = 'fileupload' OR fieldInputType = 'imageupload' THEN
						BEGIN	
							SET fieldTypeText = CONCAT('`', fieldId, '` VARCHAR(200) NULL DEFAULT NULL');
						END;						
					ELSE
						BEGIN
							SET fieldTypeText = CONCAT('`', fieldId, '` VARCHAR(160) NULL DEFAULT NULL');
						END;
					END CASE;
			END IF;
			IF (action = 'create') THEN
				SET test=CONCAT('create ', createFieldText);
				SET createFieldText = CONCAT(createFieldText, IF(createFieldText ='', '', ','), 'ADD ',fieldTypeText);
				SET test=CONCAT(test,'create ', createFieldText, '---',fieldTypeText);
			END IF;
			IF (action = 'update') THEN
				SET updateFieldText = CONCAT(updateFieldText, IF(updateFieldText ='', '', ','), 'CHANGE  `', fieldId, '` ',fieldTypeText);
			END IF;	
		END LOOP fieldCursorLoop;
	CLOSE fieldCursor;
	IF(deleteFieldIds!='')THEN
		DELETE FROM fg_cm_attribute WHERE FIND_IN_SET(id, deleteFieldIds);
	END IF;
	IF(clubType = 'federation' OR clubType ='sub_federation') THEN
		SET alterTableText = CONCAT('ALTER TABLE master_federation_', clubId);
	ELSE
		SET alterTableText = CONCAT('ALTER TABLE master_club_', clubId);
	END IF;
	
	IF(deleteFieldText <> '') THEN
		SET @dynamicSQL=CONCAT(alterTableText, ' ', deleteFieldText);
		PREPARE exec_qry FROM @dynamicSQL;
		EXECUTE exec_qry ;
		DEALLOCATE PREPARE exec_qry;	
	END IF;
	IF(createFieldText <> '') THEN
		SET test=CONCAT(test,alterTableText, ' ', createFieldText);
		SET @dynamicSQL=CONCAT(alterTableText, ' ', createFieldText);
		PREPARE exec_qry FROM @dynamicSQL;
		EXECUTE exec_qry ;
		DEALLOCATE PREPARE exec_qry;	
	END IF;		
	IF(updateFieldText <> '') THEN
		SET test=CONCAT(test,'---',alterTableText, ' ', updateFieldText);
		
		SET @dynamicSQL=CONCAT(alterTableText, ' ', updateFieldText);
		PREPARE exec_qry FROM @dynamicSQL;
		EXECUTE exec_qry ;
		DEALLOCATE PREPARE exec_qry;	
		
	END IF;	
	
		 -- SELECT test;
END