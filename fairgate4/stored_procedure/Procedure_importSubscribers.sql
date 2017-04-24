DROP PROCEDURE IF EXISTS  `importSubscribers`//
CREATE PROCEDURE `importSubscribers`(IN `tableName1` TEXT, IN `tableName2` TEXT, IN `p_club_id` INT, IN `changed_by` INT, IN `own_table` TEXT)
BEGIN
	DECLARE record_not_found INTEGER DEFAULT 0;
        DECLARE queryColumns,queryValues,fieldname,colFg,attributetable,impTable,colTitle TEXT DEFAULT '';
        DECLARE rowc INTEGER;
        DECLARE my_var TEXT DEFAULT '';
        DECLARE clubId INTEGER;
	DECLARE CONTINUE HANDLER FOR
	NOT FOUND SET record_not_found = 1;
	DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		ROLLBACK;
	END;
	
	START TRANSACTION;
   
    DROP TABLE IF EXISTS `dynamic_subscriber_columncursor`;
    SET @dynamicSQL=CONCAT('CREATE TEMPORARY TABLE dynamic_subscriber_columncursor as SELECT * FROM ',tableName2,' WHERE imp_table="',tableName1,'" ORDER BY fg_table ASC' );
    PREPARE exec_qry FROM @dynamicSQL;
    EXECUTE exec_qry ;
    DEALLOCATE PREPARE exec_qry;

	BLOCK1: BEGIN

	DECLARE dynamiccolumnCursor CURSOR FOR  SELECT * FROM `dynamic_subscriber_columncursor`;
	

	SET my_var='';


	SET @colFgList= CONCAT("SELECT GROUP_CONCAT(`col_fg` SEPARATOR '`,`' ) INTO @fgColList FROM `",tableName2,"` WHERE `imp_table` = '",tableName1,"'");
	PREPARE exec_qry FROM @colFgList;
	EXECUTE exec_qry ;
	DEALLOCATE PREPARE exec_qry;

	SET @colTempList= CONCAT("SELECT GROUP_CONCAT(`col_imp` SEPARATOR '`,`' ) INTO @tempColList FROM `",tableName2,"` WHERE `imp_table` = '",tableName1,"'");
	PREPARE exec_qry FROM @colTempList;
	EXECUTE exec_qry ;
	DEALLOCATE PREPARE exec_qry;

	SET @attribQuery= CONCAT("INSERT INTO `fg_cn_subscriber` (`club_id`,`created_at`,`import_table`,`import_id`,`",@fgColList,"`) (SELECT '",p_club_id,"',NOW(),'",tableName1,"',T.`row_id`,`",@tempColList,"` FROM `",tableName1,"` T WHERE T.`is_removed`='0') ");
	set my_var=CONCAT(my_var,@attribQuery,'==');
	PREPARE exec_qry FROM @attribQuery;
	EXECUTE exec_qry ;
	DEALLOCATE PREPARE exec_qry;

	-- mapping table iteration
	OPEN dynamiccolumnCursor;
		dynamiccolumnCursorLoop: LOOP
			FETCH dynamiccolumnCursor INTO rowc,fieldname,colFg,attributetable,impTable,colTitle, clubId;
			IF record_not_found THEN
					SET record_not_found = 0;
					LEAVE dynamiccolumnCursorLoop;
			END IF;
			-- skip fg_cm_contact fields since its updated			
			IF(attributetable='fg_cn_subscriber') THEN

				IF(colFg <>'' AND fieldname<>'') THEN
					SET @logquerySelect= CONCAT("INSERT INTO `fg_cn_subscriber_log` (`subscriber_id`,`club_id`,`date`,`kind`,`field`,`value_before`,`value_after`,`changed_by`) (SELECT cns.`id`,'",p_club_id,"', NOW(), 'data','",colFg,"','',T.`",fieldname,"`,'",changed_by,"' FROM `",tableName1,"` T LEFT JOIN `fg_cn_subscriber` cns ON cns.`import_id`=T.`row_id` WHERE cns.`import_table`='",tableName1,"' AND T.`",fieldname,"` IS NOT NULL AND T.`",fieldname,"` !='' AND cns.`import_id`=T.`row_id`); ");

					PREPARE exec_qry FROM @logquerySelect;
					EXECUTE exec_qry ;
					DEALLOCATE PREPARE exec_qry;
				END IF;
				
			END IF;

		END LOOP dynamiccolumnCursorLoop;
	CLOSE dynamiccolumnCursor;

	END BLOCK1;
        
	-- DROP TABLE `dynamic_subscriber_columncursor`;
	-- SET @same_query= CONCAT("DROP TABLE ",tableName1);
	-- PREPARE exec_qry FROM @same_query;
	-- EXECUTE exec_qry ;
	-- DEALLOCATE PREPARE exec_qry;
	
	/*SET @same_query= CONCAT("DELETE FROM ",tableName2," WHERE imp_table='",tableName1,"'");
	PREPARE exec_qry FROM @same_query;
	EXECUTE exec_qry ;
	DEALLOCATE PREPARE exec_qry; */

	COMMIT;
END