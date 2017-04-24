-- --------------------------------------------------------------------------------
-- Routine DDL
-- Note: comments before and after the routine body will not be stored by the server
-- --------------------------------------------------------------------------------
DELIMITER $$

CREATE  PROCEDURE `deleteDuplicateSubscribers`(IN `p_club_id` INT)
BEGIN
    DECLARE record_not_found, attr_set_id INTEGER DEFAULT 0;
    DECLARE dynamicSQL TEXT;
    DECLARE exec_qry TEXT;
    DECLARE fedChecking TEXT DEFAULT '';
    DECLARE clubTable TEXT DEFAULT '';
    DECLARE update_query TEXT DEFAULT '';
    DECLARE delete_query TEXT DEFAULT '';
    DECLARE clubId TEXT DEFAULT '';
    DECLARE my_var TEXT DEFAULT '';
    DECLARE clubType TEXT DEFAULT '';
    DECLARE parent TEXT DEFAULT '';
    DECLARE levels TEXT DEFAULT '';

    DECLARE CONTINUE HANDLER FOR
    NOT FOUND SET record_not_found = 1;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
    END;
	
	START TRANSACTION;
   
    DROP TABLE IF EXISTS `dynamic_Columncursor`;
    SET @dynamicSQL=CONCAT('CREATE TEMPORARY TABLE dynamic_Columncursor as SELECT @clubid AS Club_id,
                (SELECT club_type FROM fg_club WHERE id = Club_id) AS club_type,
                (SELECT @clubid := parent_club_id FROM fg_club WHERE id = Club_id) AS parent,
                @level := @level+ 1 AS level 
                FROM (SELECT @clubid :="',p_club_id,'", @level:= 0) vars, fg_club h WHERE @clubid > 1 ORDER BY level DESC' );
    PREPARE exec_qry FROM @dynamicSQL;
    EXECUTE exec_qry ;
    DEALLOCATE PREPARE exec_qry;

	BLOCK_CATEGORY: BEGIN
		
	DECLARE dynamiccolumnCursor CURSOR FOR  SELECT * FROM `dynamic_Columncursor`;
	
	
	-- mapping table iteration
	OPEN dynamiccolumnCursor;
		dynamiccolumnCursorLoop: LOOP
			FETCH dynamiccolumnCursor INTO clubId,clubType,parent,levels;
			IF record_not_found THEN
					SET record_not_found = 0;
					LEAVE dynamiccolumnCursorLoop;
			END IF;
			
			IF clubType='federation' OR clubType='sub_federation' THEN
			BEGIN
				SET fedChecking=CONCAT('AND (c.is_former_fed_member =1 OR mf.is_fed_member=1 OR c.club_id=',clubId,')');
				SET clubTable = CONCAT("master_federation_",clubId);
			END;
			ELSE
				SET fedChecking=CONCAT('AND c.club_id=',clubId);
				SET clubTable = CONCAT("master_club_",clubId);
			END IF;
			SET SQL_SAFE_UPDATES = 0;
			SET @update_query= CONCAT('UPDATE ',clubTable,' mf 
                    LEFT JOIN master_system ms ON ( ms.contact_id=mf.contact_id) 
                    LEFT JOIN fg_cm_contact c on ms.contact_id=c.id AND c.is_deleted=0 AND c.is_permanent_delete=0
                    LEFT JOIN fg_cn_subscriber cs ON lower(trim(cs.email))=lower(trim(ms.`3`)) AND cs.club_id=',clubId,'
                    SET `is_subscriber`=1 WHERE lower(trim(cs.email))=lower(trim(ms.`3`)) AND c.is_deleted=0 AND c.is_permanent_delete=0 ',fedChecking);
			PREPARE exec_qry FROM @update_query;
			EXECUTE exec_qry ;
			DEALLOCATE PREPARE exec_qry;
			
			SET @delete_query= CONCAT('DELETE cs FROM fg_cn_subscriber cs 
                    INNER JOIN master_system ms ON ( lower(trim(cs.email))=lower(trim(ms.`3`)) AND cs.club_id=',clubId,') 
                    INNER JOIN ',clubTable,' mf on ms.contact_id=mf.contact_id 
                    INNER JOIN fg_cm_contact c on ms.contact_id=c.id AND c.is_deleted=0 AND c.is_permanent_delete=0 
                    WHERE lower(trim(cs.email))=lower(trim(ms.`3`)) AND c.is_deleted=0 AND c.is_permanent_delete=0 ',fedChecking);
			PREPARE exec_qry FROM @delete_query;
			EXECUTE exec_qry ;
			DEALLOCATE PREPARE exec_qry;
			
		END LOOP dynamiccolumnCursorLoop;
	CLOSE dynamiccolumnCursor;
	END BLOCK_CATEGORY;

    SELECT my_var;
     
	DROP TABLE `dynamic_Columncursor`;

	COMMIT;
END