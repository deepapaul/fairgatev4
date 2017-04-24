DROP PROCEDURE IF EXISTS `DisplayImplications`//
CREATE PROCEDURE `DisplayImplications`(IN `clubId` INT, IN `in_contact_id` INT, IN `in_linked_contact_id` INT, IN `in_relation_id` INT, IN `default_club_lang` CHAR(2))
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

DECLARE contact_cursor CURSOR FOR
    SELECT `contact_id`, `linked_contact_id`, `relation_id` FROM `fg_cm_linkedcontact` WHERE (`contact_id` = in_contact_id  OR `contact_id` = in_linked_contact_id)  AND `type`='household' AND `club_id`=clubId;

DECLARE contact_cursor_one CURSOR FOR
    SELECT `contact_id`, `linked_contact_id`, (SELECT `id` FROM `fg_cm_relation` WHERE `name`=`linkedcontact_implications`.`relation`) AS relation_id  FROM `linkedcontact_implications` WHERE `contact_id` = in_contact_id;

DECLARE contact_cursor_two CURSOR FOR
    SELECT `contact_id`, `linked_contact_id`,  (SELECT `id` FROM `fg_cm_relation` WHERE `name`=`linkedcontact_implications`.`relation`) AS relation_id FROM `linkedcontact_implications` WHERE `contact_id` = in_linked_contact_id ;
DECLARE CONTINUE HANDLER FOR NOT FOUND SET record_not_found = 1;

DROP TEMPORARY TABLE IF EXISTS `linkedcontact_implications`;
CREATE TEMPORARY TABLE `linkedcontact_implications` (
  `contact_id` int(11),
  `linked_contact_id` int(11),
  `relation` varchar(40)
);

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
   
    INSERT INTO `linkedcontact_implications` (`contact_id`, `linked_contact_id`, `relation`) VALUES (contact_cursor_value_linked_contact, contact_cursor_value_contact,
					(
						SELECT IF(Ri18n.`title_lang`='',r.`name`,Ri18n.`title_lang`) as name
						FROM `fg_cm_relation` r
						LEFT JOIN `fg_cm_relation_i18n` Ri18n ON Ri18n.`id`=r.`id` AND Ri18n.`lang`=default_club_lang
						WHERE r.`id` = contact_cursor_value_relation
					)
				);
	
	IF (contact_cursor_value_contact = in_contact_id) THEN
            
            SELECT `second_level_relation_id` INTO temp_relation FROM `fg_cm_relation_second_level` 
                        WHERE  `relation_id` = linked_relation_id AND `first_level_relation_id` = contact_cursor_value_relation;
		
			
			INSERT INTO `linkedcontact_implications` (`contact_id`, `linked_contact_id`, `relation`) VALUES (contact_cursor_value_linked_contact, in_linked_contact_id,
					(
						SELECT IF(Ri18n.`title_lang`='',r.`name`,Ri18n.`title_lang`) as name
						FROM `fg_cm_relation` r
						LEFT JOIN `fg_cm_relation_i18n` Ri18n ON Ri18n.`id`=r.`id` AND Ri18n.`lang`=default_club_lang 
						WHERE r.`id` = temp_relation
					)
				);
			
			INSERT INTO `linkedcontact_implications` (`contact_id`, `linked_contact_id`, `relation`) VALUES (in_linked_contact_id,contact_cursor_value_linked_contact,
					(
						SELECT IF(Ri18n.`title_lang`='',r.`name`,Ri18n.`title_lang`) as name
						FROM `fg_cm_relation_first_level` 
						JOIN `fg_cm_relation` r
						ON(r.`id`=`fg_cm_relation_first_level`.`first_level_relation_id`)
						LEFT JOIN `fg_cm_relation_i18n` Ri18n ON Ri18n.`id`=r.`id` AND Ri18n.`lang`=default_club_lang 
						 
						WHERE `relation_id` = temp_relation
					)
				);

	 ELSE 
             
            SELECT `second_level_relation_id` INTO temp_relation FROM `fg_cm_relation_second_level` 
                        WHERE  `relation_id` = in_relation_id AND `first_level_relation_id` = contact_cursor_value_relation;
			
			INSERT INTO `linkedcontact_implications` (`contact_id`, `linked_contact_id`, `relation`) VALUES (contact_cursor_value_linked_contact, in_contact_id,
					(
						SELECT IF(Ri18n.`title_lang`='',r.`name`,Ri18n.`title_lang`) as name
						FROM `fg_cm_relation` r
						LEFT JOIN `fg_cm_relation_i18n` Ri18n ON Ri18n.`id`=r.`id` AND Ri18n.`lang`=default_club_lang  
						WHERE r.`id` = temp_relation
					)
				);
				
			INSERT INTO `linkedcontact_implications` (`contact_id`, `linked_contact_id`, `relation`) VALUES (in_contact_id, contact_cursor_value_linked_contact,
					(
						SELECT IF(Ri18n.`title_lang`='',r.`name`,Ri18n.`title_lang`) as name
						FROM `fg_cm_relation_first_level` 
						JOIN `fg_cm_relation` r
						ON(r.`id`=`fg_cm_relation_first_level`.`first_level_relation_id`) 
						LEFT JOIN `fg_cm_relation_i18n` Ri18n ON Ri18n.`id`=r.`id` AND Ri18n.`lang`=default_club_lang 
						WHERE `relation_id` = temp_relation
					)
				);
	END IF;		

END LOOP loop_contact;
CLOSE contact_cursor;


INSERT INTO `linkedcontact_implications` (`contact_id`, `linked_contact_id`, `relation`) VALUES (in_linked_contact_id,in_contact_id,
		(
			SELECT IF(Ri18n.`title_lang`='',r.`name`,Ri18n.`title_lang`) as name 
			FROM `fg_cm_relation` r
			LEFT JOIN `fg_cm_relation_i18n` Ri18n ON Ri18n.`id`=r.`id` AND Ri18n.`lang`=default_club_lang 
			WHERE r.`id` = in_relation_id
		)
	);           
            
INSERT INTO `linkedcontact_implications` (`contact_id`, `linked_contact_id`, `relation`) VALUES (in_contact_id, in_linked_contact_id,
		(
			SELECT IF(Ri18n.`title_lang`='',r.`name`,Ri18n.`title_lang`) as name
			FROM `fg_cm_relation` r
			LEFT JOIN `fg_cm_relation_i18n` Ri18n ON Ri18n.`id`=r.`id` AND Ri18n.`lang`=default_club_lang
			WHERE r.`id` = linked_relation_id
		)
	);             
 
   
                                    
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
                    
                    SELECT count(*)  INTO temp_count FROM `linkedcontact_implications` 
                        WHERE `contact_id` = contact_cursor_value_linked_contact AND `linked_contact_id` = contact_cursor_value_linked_contact_two;
                        
                                     			
						INSERT INTO `linkedcontact_implications` (`contact_id`, `linked_contact_id`, `relation`) VALUES (contact_cursor_value_linked_contact, 
								contact_cursor_value_linked_contact_two,
								(
									SELECT IF(Ri18n.`title_lang`='',r.`name`,Ri18n.`title_lang`) as name 
									FROM `fg_cm_relation` r
									LEFT JOIN `fg_cm_relation_i18n` Ri18n ON Ri18n.`id`=r.`id` AND Ri18n.`lang`=default_club_lang
									WHERE r.`id` = temp_relation
								)
							);
                                                   
                    
                                    
                END IF;
            END IF;
        END IF;
    
	END LOOP loop_contact_two;
    CLOSE contact_cursor_two;	
	
		
END LOOP loop_contact_one;
CLOSE contact_cursor_one;
                                    
SELECT * FROM `linkedcontact_implications`;

END