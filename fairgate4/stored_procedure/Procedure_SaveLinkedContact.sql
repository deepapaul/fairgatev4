DROP PROCEDURE IF EXISTS `SaveLinkedContact`//
CREATE PROCEDURE `SaveLinkedContact`(IN clubId INTEGER, IN in_contact_id INTEGER, IN currentContactId INTEGER, IN default_club_lang CHAR(2))

BEGIN

DECLARE linked_relation_id INTEGER;
DECLARE record_not_found INTEGER DEFAULT 0; 
DECLARE temp_relation INTEGER;
DECLARE temp_count INTEGER;

DECLARE contact_cursor_value_contact INTEGER;
DECLARE contact_cursor_value_linked_contact INTEGER;
DECLARE contact_cursor_value_relation INTEGER;

DECLARE contact_cursor_value_contact_two INTEGER;
DECLARE contact_cursor_value_linked_contact_two INTEGER;
DECLARE contact_cursor_value_relation_two INTEGER;

DECLARE in_linked_contact_id INTEGER DEFAULT 0;
DECLARE in_relation_id INTEGER DEFAULT 0;

DECLARE connections_cursor CURSOR FOR SELECT `linked_contact_id`, `relation_id` FROM `fg_temp_add_connection` WHERE `club_id`=clubId AND `contact_id`=in_contact_id;

DECLARE contact_cursor CURSOR FOR
    SELECT `contact_id`, `linked_contact_id`, `relation_id` FROM `fg_cm_linkedcontact` WHERE (`contact_id` = in_contact_id  OR `contact_id` = in_linked_contact_id)  AND `type`='household' AND `club_id`=clubId;

DECLARE contact_cursor_one CURSOR FOR
    SELECT `contact_id`, `linked_contact_id`, `relation_id` FROM `fg_cm_linkedcontact` WHERE `contact_id` = in_contact_id AND `type`='household' AND `club_id`=clubId;
DECLARE contact_cursor_two CURSOR FOR
    SELECT `contact_id`, `linked_contact_id`, `relation_id` FROM `fg_cm_linkedcontact` WHERE `contact_id` = in_linked_contact_id AND `type`='household' AND `club_id`=clubId;

DECLARE CONTINUE HANDLER FOR NOT FOUND SET record_not_found = 1;

DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
	ROLLBACK;
END;
	
START TRANSACTION;


-- Connections loop starts --
OPEN connections_cursor;
loop_connections: LOOP

	FETCH connections_cursor INTO in_linked_contact_id, in_relation_id;
    
    	IF record_not_found THEN
		SET record_not_found = 0;
        	LEAVE loop_connections;
    	END IF;

    	SELECT `first_level_relation_id` INTO linked_relation_id FROM `fg_cm_relation_first_level` WHERE `relation_id` = in_relation_id;

	OPEN contact_cursor;
	loop_contact: LOOP

	FETCH contact_cursor INTO
	    contact_cursor_value_contact,
	    contact_cursor_value_linked_contact,
	    contact_cursor_value_relation;
	    
	    IF record_not_found THEN
		SET record_not_found = 0;
		        LEAVE loop_contact;
	    END IF;
	    
		IF (contact_cursor_value_contact = in_contact_id) THEN
	    
		    SELECT `second_level_relation_id` INTO temp_relation FROM `fg_cm_relation_second_level` 
		                WHERE  `relation_id` = linked_relation_id AND `first_level_relation_id` = contact_cursor_value_relation;
	    
				INSERT INTO `fg_cm_linkedcontact` (`id`, `contact_id`, `linked_contact_id`, `relation_id`, `type`, `club_id`) 
		        VALUES (NULL, in_linked_contact_id, contact_cursor_value_linked_contact, temp_relation, 'household', clubId);
		        
		    INSERT INTO `fg_cm_linkedcontact` (`id`, `contact_id`, `linked_contact_id`, `relation_id`, `type`, `club_id`) 
		        VALUES (NULL, contact_cursor_value_linked_contact, in_linked_contact_id, 
		        (SELECT `first_level_relation_id` FROM `fg_cm_relation_first_level` WHERE `relation_id` = temp_relation)
		        , 'household', clubId);

		    -- Connection Log Entry --
		    INSERT INTO `fg_cm_log_connection` (`contact_id`,`linked_contact_id`,`assigned_club_id`,`date`,`connection_type`,`relation`,`value_before`,`value_after`,`changed_by`) VALUES 
                    (in_linked_contact_id, contact_cursor_value_linked_contact, clubId, NOW(), 'Household contact', (SELECT IF(Ri18n.`title_lang`='',r.`name`,Ri18n.`title_lang`) as name FROM `fg_cm_relation` r LEFT JOIN `fg_cm_relation_i18n` Ri18n ON Ri18n.`id`=r.`id` AND Ri18n.`lang`=default_club_lang WHERE r.`id`=temp_relation), '-', contactName(contact_cursor_value_linked_contact), currentContactId);
                    INSERT INTO `fg_cm_log_connection` (`contact_id`,`linked_contact_id`,`assigned_club_id`,`date`,`connection_type`,`relation`,`value_before`,`value_after`,`changed_by`) VALUES 
                    (contact_cursor_value_linked_contact, in_linked_contact_id, clubId, NOW(), 'Household contact', (SELECT IF(Ri18n.`title_lang`='',r.`name`,Ri18n.`title_lang`) as name FROM `fg_cm_relation` r LEFT JOIN `fg_cm_relation_i18n` Ri18n ON Ri18n.`id`=r.`id` AND Ri18n.`lang`=default_club_lang WHERE r.`id`=(SELECT `first_level_relation_id` FROM `fg_cm_relation_first_level` WHERE `relation_id` = temp_relation)), '-', contactName(in_linked_contact_id), currentContactId);
				
		ELSE 
		        
		    SELECT `second_level_relation_id` INTO temp_relation FROM `fg_cm_relation_second_level` 
		                WHERE  `relation_id` = in_relation_id AND `first_level_relation_id` = contact_cursor_value_relation;
	    
				INSERT INTO `fg_cm_linkedcontact` (`id`, `contact_id`, `linked_contact_id`, `relation_id`, `type`, `club_id`) 
		        VALUES (NULL, in_contact_id, contact_cursor_value_linked_contact, temp_relation, 'household', clubId);
		        
		    INSERT INTO `fg_cm_linkedcontact` (`id`, `contact_id`, `linked_contact_id`, `relation_id`, `type`, `club_id`) 
		        VALUES (NULL, contact_cursor_value_linked_contact, in_contact_id, 
		        (SELECT `first_level_relation_id` FROM `fg_cm_relation_first_level` WHERE `relation_id` = temp_relation)
		        , 'household', clubId);

		    -- Connection Log Entry --
		    INSERT INTO `fg_cm_log_connection` (`contact_id`,`linked_contact_id`,`assigned_club_id`,`date`,`connection_type`,`relation`,`value_before`,`value_after`,`changed_by`) VALUES 
(in_contact_id, contact_cursor_value_linked_contact, clubId, NOW(), 'Household contact', (SELECT IF(Ri18n.`title_lang`='',r.`name`,Ri18n.`title_lang`) as name FROM `fg_cm_relation` r LEFT JOIN `fg_cm_relation_i18n` Ri18n ON Ri18n.`id`=r.`id` AND Ri18n.`lang`=default_club_lang WHERE r.`id`=temp_relation), '-', contactName(contact_cursor_value_linked_contact), currentContactId);
		    INSERT INTO `fg_cm_log_connection` (`contact_id`,`linked_contact_id`,`assigned_club_id`,`date`,`connection_type`,`relation`,`value_before`,`value_after`,`changed_by`) VALUES 
(contact_cursor_value_linked_contact, in_contact_id, clubId, NOW(), 'Household contact', (SELECT IF(Ri18n.`title_lang`='',r.`name`,Ri18n.`title_lang`) as name FROM `fg_cm_relation` r LEFT JOIN `fg_cm_relation_i18n` Ri18n ON Ri18n.`id`=r.`id` AND Ri18n.`lang`=default_club_lang WHERE r.`id`=(SELECT `first_level_relation_id` FROM `fg_cm_relation_first_level` WHERE `relation_id` = temp_relation)), '-', contactName(in_contact_id), currentContactId);

		        
		END IF;		
	    
	END LOOP loop_contact;
	CLOSE contact_cursor;


	INSERT INTO `fg_cm_linkedcontact` (`id`, `contact_id`, `linked_contact_id`, `relation_id`, `type`, `club_id`) 
										VALUES (NULL, in_contact_id, in_linked_contact_id, in_relation_id, 'household', clubId);
	INSERT INTO `fg_cm_linkedcontact` (`id`, `contact_id`, `linked_contact_id`, `relation_id`, `type`, `club_id`) 
										VALUES (NULL, in_linked_contact_id, in_contact_id, linked_relation_id, 'household', clubId);

	-- Connection Log Entry --
	INSERT INTO `fg_cm_log_connection` (`contact_id`,`linked_contact_id`,`assigned_club_id`,`date`,`connection_type`,`relation`,`value_before`,`value_after`,`changed_by`) VALUES 
(in_contact_id, in_linked_contact_id, clubId, NOW(), 'Household contact', (SELECT IF(Ri18n.`title_lang`='',r.`name`,Ri18n.`title_lang`) as name FROM `fg_cm_relation` r LEFT JOIN `fg_cm_relation_i18n` Ri18n ON Ri18n.`id`=r.`id` AND Ri18n.`lang`=default_club_lang WHERE r.`id`=in_relation_id), '-', contactName(in_linked_contact_id), currentContactId);
	INSERT INTO `fg_cm_log_connection` (`contact_id`,`linked_contact_id`,`assigned_club_id`,`date`,`connection_type`,`relation`,`value_before`,`value_after`,`changed_by`) VALUES 
(in_linked_contact_id, in_contact_id, clubId, NOW(), 'Household contact', (SELECT IF(Ri18n.`title_lang`='',r.`name`,Ri18n.`title_lang`) as name FROM `fg_cm_relation` r LEFT JOIN `fg_cm_relation_i18n` Ri18n ON Ri18n.`id`=r.`id` AND Ri18n.`lang`=default_club_lang WHERE r.`id`=linked_relation_id), '-', contactName(in_contact_id), currentContactId);

		                            
		                            
	OPEN contact_cursor_one;
	loop_contact_one: LOOP

	FETCH contact_cursor_one INTO
	    contact_cursor_value_contact,
	    contact_cursor_value_linked_contact,
	    contact_cursor_value_relation;
	    
	    IF record_not_found THEN
		    SET record_not_found = 0;
		                LEAVE loop_contact_one;
	    END IF;
	
		OPEN contact_cursor_two;
		loop_contact_two: LOOP
	
		FETCH contact_cursor_two INTO
			contact_cursor_value_contact_two,
			contact_cursor_value_linked_contact_two,
			contact_cursor_value_relation_two;
		
		IF record_not_found THEN
		    SET record_not_found = 0;
		                LEAVE loop_contact_two;
		END IF;
		
		IF(contact_cursor_value_linked_contact != in_linked_contact_id) THEN 
		    IF(contact_cursor_value_linked_contact_two != in_contact_id) THEN 
		        IF(contact_cursor_value_linked_contact != contact_cursor_value_linked_contact_two) THEN 
		    
		    
		            SELECT `second_level_relation_id` INTO temp_relation FROM `fg_cm_relation_second_level` WHERE  `relation_id` = (
		                SELECT `second_level_relation_id` FROM `fg_cm_relation_second_level` WHERE  `relation_id` = (
		                    SELECT `first_level_relation_id` FROM `fg_cm_relation_first_level` WHERE `relation_id` = contact_cursor_value_relation
		                ) AND `first_level_relation_id` = in_relation_id
		            ) AND `first_level_relation_id` = contact_cursor_value_relation_two;
		            
		            SELECT count(*)  INTO temp_count FROM `fg_cm_linkedcontact` 
		                WHERE `contact_id` = contact_cursor_value_linked_contact AND `linked_contact_id` = contact_cursor_value_linked_contact_two AND `club_id`=clubId;
		                
		            IF(temp_count = 0) THEN 
		            
		                INSERT INTO `fg_cm_linkedcontact` (`id`, `contact_id`, `linked_contact_id`, `relation_id`, `type`, `club_id`) 
										VALUES (NULL, contact_cursor_value_linked_contact, contact_cursor_value_linked_contact_two, temp_relation, 'household', clubId);

				-- Connection Log Entry --
				INSERT INTO `fg_cm_log_connection` 
(`contact_id`,`linked_contact_id`,`assigned_club_id`,`date`,`connection_type`,`relation`,`value_before`,`value_after`,`changed_by`) VALUES 
(contact_cursor_value_linked_contact, contact_cursor_value_linked_contact_two, clubId, NOW(), 'Household contact', (SELECT IF(Ri18n.`title_lang`='',r.`name`,Ri18n.`title_lang`) as name FROM `fg_cm_relation` r LEFT JOIN `fg_cm_relation_i18n` Ri18n ON Ri18n.`id`=r.`id` AND Ri18n.`lang`=default_club_lang WHERE r.`id`=temp_relation), '-', contactName(contact_cursor_value_linked_contact_two), currentContactId);

		            END IF;
		                            
		            
		                            
		        END IF;
		    END IF;
		END IF;
	    
		END LOOP loop_contact_two;
	    CLOSE contact_cursor_two;	
	
		
	END LOOP loop_contact_one;
	CLOSE contact_cursor_one;

-- Connections loop ends --
END LOOP loop_connections;
CLOSE connections_cursor;

COMMIT;

DELETE FROM `fg_temp_add_connection` WHERE `club_id`=clubId AND `contact_id`=in_contact_id;

END