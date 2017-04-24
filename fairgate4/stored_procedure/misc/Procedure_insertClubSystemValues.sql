-- To insert default values for a club
DROP PROCEDURE `insertClubSystemValues`//
CREATE DEFINER=`fairgate`@`localhost` PROCEDURE `insertClubSystemValues`(IN club_ids TEXT)
BEGIN

	DECLARE clubId VARCHAR(255) DEFAULT "";
	DECLARE record_not_found INTEGER DEFAULT 0;	

	DECLARE mysql_prepare_exception CONDITION FOR 1615;
	DECLARE clubCursor CURSOR FOR  SELECT id FROM `fg_club` WHERE FIND_IN_SET(id, club_ids);
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET record_not_found = 1;
	SET autocommit=0;
	SET SESSION tmp_table_size = 67108864;
	SET SESSION max_heap_table_size = 67108864;
	START TRANSACTION;
		OPEN clubCursor;
			clubCursorLoop: LOOP
				FETCH clubCursor INTO clubId;
				IF record_not_found THEN
					SET record_not_found = 0;
					LEAVE clubCursorLoop;
				END IF;	
				
				 INSERT INTO `fg_club_languages` ( `club_id`, `short_name`, `system_language_code`, `is_default`, `is_active`) VALUES ( clubId, 'sq', 'de', 1, 1);
				 INSERT INTO `fg_club_languages` ( `club_id`, `short_name`, `system_language_code`, `is_default`, `is_active`) VALUES ( clubId, 'fr', 'en', 0, 1);
				INSERT INTO `fg_cm_club_attribute` (`attribute_id`, `club_id`) SELECT `attribute_id`, clubId  FROM fg_cm_club_attribute WHERE club_id = 1;				
				
			END LOOP clubCursorLoop;
		CLOSE clubCursor;
    COMMIT;
END
