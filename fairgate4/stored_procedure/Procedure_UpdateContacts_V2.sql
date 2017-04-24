DROP PROCEDURE IF EXISTS `updateContacts_V2`//
CREATE PROCEDURE `updateContacts_V2`(tableName1 TEXT,tableName2 TEXT,p_club_id INT, changed_by INT, main_contact TEXT,own_table TEXT,IN `club_type` TEXT,IN `FedID` TEXT,IN `subFedID` TEXT)
BEGIN
    DECLARE record_not_found, attr_set_id INTEGER DEFAULT 0;
    DECLARE attr_set_id_inv INTEGER DEFAULT 137;
    DECLARE id INTEGER DEFAULT 0;
    DECLARE same_as INTEGER DEFAULT 1;
    DECLARE has_MC TEXT DEFAULT '0';
    DECLARE is_mem TEXT DEFAULT '0';
    DECLARE is_company text;
    DECLARE dynamicSQL TEXT;
    DECLARE exec_qry TEXT;
    DECLARE exec_qry1 TEXT;
    DECLARE fieldname TEXT;
    DECLARE attributetable TEXT;
    DECLARE prevLoopTable TEXT DEFAULT '';
    DECLARE rowc INTEGER;
    DECLARE colfg TEXT DEFAULT '';
    DECLARE coltitle TEXT DEFAULT '';
    DECLARE attribquery,tableName TEXT DEFAULT '';
    DECLARE clubId TEXT DEFAULT '';
    DECLARE queryColumns TEXT DEFAULT '';
    DECLARE queryValues TEXT DEFAULT '';
    DECLARE logquery TEXT DEFAULT '';
    DECLARE queryDuplicate TEXT DEFAULT '';
    DECLARE fgCmContactColumns TEXT DEFAULT '';
    DECLARE fgCmContactValues TEXT DEFAULT '';
    DECLARE my_var TEXT DEFAULT '';
    DECLARE same_query TEXT DEFAULT '';
    DECLARE sf_query TEXT DEFAULT '';
    DECLARE filedname_joindate_val TEXT DEFAULT '';
    DECLARE joindate_query TEXT DEFAULT '';
    DECLARE impTable TEXT;
    DECLARE rowid_joindate INTEGER;
    DECLARE subClubQuery, sfGuardJoinQuery TEXT DEFAULT '';
    DECLARE subClubWhere TEXT DEFAULT '1';
	DECLARE colclubId TEXT DEFAULT '';
    DECLARE tblContact TEXT DEFAULT '';
    DECLARE colContactid  TEXT DEFAULT '';
	DECLARE ColidField TEXT DEFAULT '';
	DECLARE updateField TEXT;
    DECLARE tempContactField TEXT;
    DECLARE attrContactField TEXT;
	DECLARE clubTblid TEXT;
	DECLARE fgCmDuplicate TEXT;
	DECLARE clubIdCondition TEXT DEFAULT '';
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET record_not_found = 1;
    
	
    START TRANSACTION;
   
    DROP TABLE IF EXISTS `dynamic_Columncursor`;
    SET @dynamicSQL=CONCAT('CREATE TEMPORARY TABLE dynamic_Columncursor as SELECT * FROM ',tableName2,' WHERE imp_table="',tableName1,'" ORDER BY fg_table ASC' );
    PREPARE exec_qry FROM @dynamicSQL;
    EXECUTE exec_qry ;
    DEALLOCATE PREPARE exec_qry;
	
	 IF(club_type='federation') THEN
	  SET subClubQuery = CONCAT('INNER JOIN ',own_table,' mf ON mf.fed_contact_id=C.fed_contact_id ');
    ELSEIF(club_type='sub_federation') THEN
      SET subClubQuery = CONCAT('INNER JOIN ',own_table,' mf ON mf.contact_id=C.subfed_contact_id ');
	 ELSE
	  SET subClubQuery = CONCAT('INNER JOIN ',own_table,' mf ON mf.contact_id=C.id ');
	END IF;

	BLOCK_CATEGORY: BEGIN
		
	    DECLARE dynamiccolumnCursor CURSOR FOR  SELECT * FROM `dynamic_Columncursor` WHERE TRIM(`fg_table`)<>'fg_cm_contact';
		DECLARE fgCmContactColumnCursor CURSOR FOR  SELECT * FROM `dynamic_Columncursor` WHERE TRIM(`fg_table`)='fg_cm_contact' AND col_fg!='contact_id' AND col_imp IS NOT NULL AND col_imp !='';
		
	
	
	
	SET fgCmContactColumns='INSERT INTO fg_cm_contact (`club_id`,`id`';
	SET fgCmContactValues=CONCAT('(SELECT ',p_club_id,',','C.id');
	SET fgCmDuplicate=CONCAT("ON DUPLICATE KEY UPDATE `id`=VALUES(`id`)");

	OPEN fgCmContactColumnCursor;
		fgCmContactColumnCursor: LOOP
			FETCH fgCmContactColumnCursor INTO rowc,fieldname,colFg,attributetable,impTable,colTitle,colclubId;
			IF record_not_found THEN
				SET record_not_found = 0;
				LEAVE fgCmContactColumnCursor;
			END IF;
                        IF(fieldname !='' and colFg ='first_joining_date') THEN
                            BEGIN
                                SET SQL_SAFE_UPDATES = 0;
                                SET @attribQuery = CONCAT('UPDATE fg_cm_contact C 
                                INNER JOIN ',tableName1,' T ON T.contact_id=C.id  
                                INNER JOIN ( SELECT m.id,m.contact_id,m.joining_date FROM fg_cm_membership_history m INNER JOIN ',tableName1,' T ON T.contact_id=m.contact_id WHERE m.contact_id = T.contact_id ORDER BY m.joining_date ASC
                                            ) AS firstHistory ON firstHistory.contact_id = C.id
                                SET C.first_joining_date=T.',fieldname,',C.joining_date=T.',fieldname,' 
                                WHERE C.first_joining_date IS NOT NULL AND C.first_joining_date=C.joining_date AND T.is_removed="0" AND C.is_permanent_delete="0"');
                                PREPARE exec_qry FROM @attribQuery;
                                SET my_var=CONCAT(my_var,@attribQuery);
                                EXECUTE exec_qry ;
                                DEALLOCATE PREPARE exec_qry;

                                SET @attribQuery = CONCAT('UPDATE fg_cm_contact C 
                                INNER JOIN ',tableName1,' T ON T.contact_id=C.id  
                                INNER JOIN ( SELECT m.id,m.contact_id,m.joining_date FROM fg_cm_membership_history m INNER JOIN ',tableName1,' T ON T.contact_id=m.contact_id WHERE m.contact_id = T.contact_id ORDER BY m.joining_date ASC
                                            ) AS firstHistory ON firstHistory.contact_id = C.id
                                SET C.first_joining_date=T.',fieldname,'
                                WHERE C.first_joining_date IS NOT NULL AND C.first_joining_date != C.joining_date AND T.is_removed="0" AND C.is_permanent_delete="0"');
                                PREPARE exec_qry FROM @attribQuery;
                                SET my_var=CONCAT('===',my_var,@attribQuery);
                                EXECUTE exec_qry ;
                                DEALLOCATE PREPARE exec_qry;
                                
                                
                                SET @dynamicSQL=CONCAT('CREATE TEMPORARY TABLE tmp_membership_history as SELECT H.* FROM fg_cm_membership_history H INNER JOIN ',tableName1,' T ON T.contact_id=H.contact_id GROUP BY H.contact_id HAVING H.joining_date=MIN(H.joining_date)' );
                                PREPARE exec_qry FROM @dynamicSQL;
                                SET my_var=CONCAT('===',my_var,@dynamicSQL);
                                EXECUTE exec_qry ;
                                DEALLOCATE PREPARE exec_qry;

                                SET @attribQuery = CONCAT('UPDATE fg_cm_membership_history M
                                INNER JOIN tmp_membership_history TH ON TH.id=M.id
                                INNER JOIN ',tableName1,' T ON T.contact_id=M.contact_id
                                SET M.joining_date = T.',fieldname);
                                SET my_var=CONCAT('===',my_var,@attribQuery);
                                PREPARE exec_qry FROM @attribQuery;
                                EXECUTE exec_qry ;
                                DEALLOCATE PREPARE exec_qry;
                                DROP TABLE IF EXISTS tmp_membership_history;
                            END;
                        ELSEIF(fieldname !='' and fieldname !='same_invoice_address' and  fieldname !='same_invoice_address') THEN
				BEGIN
					SET fgCmContactColumns=CONCAT(fgCmContactColumns,',`',colFg,'`');
					SET fgCmContactValues=CONCAT(fgCmContactValues,",",fieldname);
					SET attrContactField = 'id';
					SET attributetable ='fg_cm_contact';
					SET updateField ='id';
					SET fgCmDuplicate=CONCAT(fgCmDuplicate,",`",colFg,"`=VALUES(`",colFg,"`)");
					SELECT colFg;
					IF(colFg='same_invoice_address') THEN
						SET @logquery = CONCAT("UPDATE fg_cm_contact C INNER JOIN ",tableName1," tp ON (tp.fed_contact_id=C.fed_contact_id AND tp.is_removed=0)  set C.`same_invoice_address`='0' WHERE C.fed_contact_id=tp.fed_contact_id AND tp.is_removed=0");
						Select @logquery;
						PREPARE exec_qry FROM @logquery;
						EXECUTE exec_qry ;
						DEALLOCATE PREPARE exec_qry;
					END IF;
					IF(colFg='is_subscriber') THEN
                            SET @logquery= CONCAT("INSERT INTO fg_cm_change_log ( `date`, `changed_by`, `confirmed_by`, `club_id`, `contact_id`,`kind`, `field`, `value_before`, `value_after`,`attribute_id`)(SELECT NOW(), ",changed_by,",",changed_by,",C.club_id, C.id, 'system','newsletter',(SELECT IF(A.is_subscriber = '1', 'subscribed', 'unsubscribed')  FROM ",attributetable," A WHERE A.id=C.id  ) AS old_value,IF(T.",fieldname," = '1', 'subscribed', 'unsubscribed' ) as new_value,cast('",colFg,"'as UNSIGNED) FROM ",tableName1," T INNER JOIN fg_cm_contact C ON C.id=T.contact_id ",subClubQuery," WHERE C.is_permanent_delete='0' AND T.is_removed='0' HAVING  old_value !=new_value)");
                            Select @logquery;
							PREPARE exec_qry FROM @logquery;
							 EXECUTE exec_qry ;
							 DEALLOCATE PREPARE exec_qry;
					END IF;
					IF(colFg='intranet_access') THEN
						 SET @logquery= CONCAT("INSERT INTO fg_cm_change_log ( `date`, `changed_by`, `confirmed_by`,`club_id`,  `contact_id`,`kind`, `field`, `value_before`, `value_after`,`attribute_id`)(SELECT NOW(), ",changed_by,",",changed_by,",C.club_id, C.id, 'system','intranet access',(SELECT IF(A.intranet_access = '1', 'yes', 'no')  FROM ",attributetable," A WHERE A.id=C.id  ) AS old_value,IF(T.",fieldname," = '1', 'yes', 'no' ) as new_value,cast('",colFg,"'as UNSIGNED) FROM ",tableName1," T INNER JOIN fg_cm_contact C ON C.id=T.contact_id ",subClubQuery," WHERE C.is_permanent_delete='0' AND T.is_removed='0' HAVING  old_value !=new_value)");
                            Select @logquery;
							PREPARE exec_qry FROM @logquery;
							 EXECUTE exec_qry ;
							 DEALLOCATE PREPARE exec_qry;
					END IF;
                  END;
			END IF;
                        
		END LOOP fgCmContactColumnCursor;
    CLOSE fgCmContactColumnCursor;
   Select fgCmDuplicate;
   SET @dynamicSQL=CONCAT(fgCmContactColumns,')',fgCmContactValues,' FROM ',tableName1,' T inner join fg_cm_contact C on C.id=T.contact_id WHERE  is_removed=0 )',fgCmDuplicate);
   SELECT @dynamicSQL;
   PREPARE exec_qry FROM @dynamicSQL;
   EXECUTE exec_qry ;
   DEALLOCATE PREPARE exec_qry;
	
	
	OPEN dynamiccolumnCursor;
		dynamiccolumnCursorLoop: LOOP
			FETCH dynamiccolumnCursor INTO rowc,fieldname,colFg,attributetable,impTable,colTitle,clubId;
			IF record_not_found THEN
					SET record_not_found = 0;
					LEAVE dynamiccolumnCursorLoop;
			END IF;
			
			
			IF (fieldname !='' AND attributetable != 'fg_cm_contact') THEN
				        
                            SET tableName=SUBSTRING(attributetable,1,11);
                            IF (clubId=FedID) THEN
                                    SET updateField = 'fed_contact_id';
                                    SET tempContactField = 'fed_contact_id';
                                    SET attrContactField = 'fed_contact_id';
                                    SET clubIdCondition = CONCAT(' A.`club_id`= ',FedID,' AND ');
                            ELSEIF (clubId=subFedID) THEN
                                    SET updateField = 'subfed_contact_id';
                                    SET tempContactField = 'fed_contact_id';
                                    SET attrContactField = 'contact_id';
                                    SET clubIdCondition = CONCAT(' A.`club_id`= ',subFedID,' AND ');
                            ELSE
                                    SET updateField = 'id';
                                    SET tempContactField = 'contact_id';
                                    SET attrContactField = 'contact_id';
                                    SET clubIdCondition = '';
                            END IF;
			    IF(colFg ='3') THEN 
                                SET SQL_SAFE_UPDATES=0;
                                SET @sf_query=CONCAT("UPDATE sf_guard_user su INNER JOIN ",tableName1," tp ON (tp.contact_id=su.contact_id AND tp.is_removed=0) ",sfGuardJoinQuery," set `username`=tp.",fieldname,",`username_canonical`=tp.",fieldname,",`email`=tp.",fieldname,",`email_canonical`=tp.",fieldname," WHERE su.contact_id=tp.contact_id AND su.id !=0 AND tp.is_removed=0");
                                Select @sf_query;
                                PREPARE exec_qry FROM @sf_query;
                                EXECUTE exec_qry ;
                                DEALLOCATE PREPARE exec_qry;
                            END IF;
                            IF(fieldname !='') THEN 
                                    IF(tableName='master_club' or  attributetable='master_system') THEN
                                            SET clubIdCondition = '';
                                    END IF;
                                IF(attributetable='master_system') THEN
                                  SET attrContactField = 'fed_contact_id';
                                  SET @logquery= CONCAT("INSERT INTO fg_cm_change_log ( `date`, `club_id`,`changed_by`, `confirmed_by`,  `contact_id`,`kind`, `field`, `value_before`, `value_after`,`attribute_id`)(SELECT NOW(),'",p_club_id,"', ",changed_by,",",changed_by,", C.fed_contact_id, 'data','",colTitle,"',(SELECT  IF(`",colFg,"` is null or `",colFg,"` = '0000-00-00'  , '', `",colFg,"`) FROM ",attributetable," A WHERE", clubIdCondition," A.",attrContactField,"=C.",updateField,"  ) AS old_value,T.",fieldname,",cast('",colFg,"'as UNSIGNED) FROM ",tableName1," T INNER JOIN fg_cm_contact C ON C.fed_contact_id=T.fed_contact_id and C.club_id=",p_club_id,"   WHERE C.is_permanent_delete='0' AND T.is_removed='0' having  (  old_value !=T.`",fieldname,"`) ) ");
                                        Select @logquery;
                                        PREPARE exec_qry FROM @logquery;
                                        EXECUTE exec_qry ;
                                        DEALLOCATE PREPARE exec_qry;
                                ELSE
                                        SET @logquery= CONCAT("INSERT INTO fg_cm_change_log ( `date`,`club_id`, `changed_by`, `confirmed_by`,  `contact_id`,`kind`, `field`, `value_before`, `value_after`,`attribute_id`)(SELECT NOW(),'",p_club_id,"',  ",changed_by,",",changed_by,", C.",updateField," , 'data','",colTitle,"',(SELECT IF(`",colFg,"` IS NULL, '', `",colFg,"`) FROM ",attributetable," A WHERE", clubIdCondition," A.",attrContactField,"=C.",updateField,"  ) AS old_value,T.",fieldname,",cast('",colFg,"'as UNSIGNED) FROM ",tableName1," T INNER JOIN fg_cm_contact C ON C.id=T.contact_id ",subClubQuery," WHERE C.is_permanent_delete='0' AND T.is_removed='0' HAVING  (old_value !=T.`",fieldname,"` )  ) ");
                                        Select @logquery;
                                        PREPARE exec_qry FROM @logquery;
                                        EXECUTE exec_qry ;
                                        DEALLOCATE PREPARE exec_qry;
                                END IF;
                            IF(tableName='master_club' or club_type='standard_club' ) THEN
                                SET @attribQuery= CONCAT('INSERT INTO fg_cm_contact(id,last_updated) (SELECT C.id,NOW() FROM ',tableName1,' T LEFT JOIN fg_cm_contact C ON C.id=T.',tempContactField,' LEFT JOIN ',attributetable,' A ON C.id=A.',attrContactField,' WHERE T.is_removed="0" AND C.is_permanent_delete="0" AND  ( (A.`',colFg,'` is null and  T.`',fieldname,'` is not null ) or A.`',colFg,'` !=T.',fieldname,' )) ON DUPLICATE KEY UPDATE `last_updated`=VALUES(`last_updated`),`id`=VALUES(`id`)');
                            ELSE
                                SET @attribQuery= CONCAT('INSERT INTO fg_cm_contact(id,',updateField,',last_updated) (SELECT C.id,C.',updateField,',NOW() FROM ',tableName1,' T LEFT JOIN fg_cm_contact C ON C.',updateField,'=T.',tempContactField,' LEFT JOIN ',attributetable,' A ON C.',updateField,'=A.',attrContactField,' WHERE T.is_removed="0" AND C.is_permanent_delete="0" AND  ( (A.`',colFg,'` is null and  T.`',fieldname,'` is not null ) or A.`',colFg,'` !=T.',fieldname,' )) ON DUPLICATE KEY UPDATE `last_updated`=VALUES(`last_updated`),`id`=VALUES(`id`)');
                            END IF;
                            PREPARE exec_qry FROM @attribQuery;
                            EXECUTE exec_qry ;
                            DEALLOCATE PREPARE exec_qry;
                            Select @attribQuery;
                        END IF;	
                        IF(prevLoopTable <> attributetable) THEN 
                            BEGIN
						
                                IF (queryColumns <>'') THEN

                                    set my_var=CONCAT(my_var,queryColumns,'==');
                                    SET @attribQuery= CONCAT(queryColumns,') (SELECT ',queryValues,' FROM ',tableName1,' T INNER JOIN fg_cm_contact C ON C.fed_contact_id=T.fed_contact_id ',subClubQuery,' WHERE T.is_removed="0" AND C.is_permanent_delete="0" ) ',queryDuplicate);
                                    SELECT @attribQuery;
                                    PREPARE exec_qry FROM @attribQuery;
                                    EXECUTE exec_qry ;
                                    DEALLOCATE PREPARE exec_qry;
                                    SET attribQuery='';
                                    SET queryColumns='';
                                    SET queryValues='';
                                END IF;
                                IF(FedID=clubId)  THEN
                                        SET colContactid ="fed_contact_id";
                                        SET colidField = "fed_contact_id";
                                ELSEIF(SubFedID=clubId) THEN
                                        SET colContactid ='contact_id';
                                        SET colidField = "subfed_contact_id";
                                ELSE
                                        SET colContactid ='contact_id';
                                        SET colidField = 'id';
                                END IF;
                                IF(attributetable='master_system') THEN
                                        SET colContactid ="fed_contact_id";
                                        SET colidField = "fed_contact_id";
                                END IF;	 
                                        SET queryColumns=CONCAT("INSERT INTO ",attributetable," (",colContactid,",`",colfg,"`" );
                                        SET queryValues= CONCAT("C.",ColidField,",T.",fieldname);
                                        SET queryDuplicate=CONCAT("ON DUPLICATE KEY UPDATE `",colFg,"`=VALUES(`",colFg,"`) ,`",colContactid,"`=VALUES(`",colContactid,"`) ");
                                IF(tableName != 'master_club' and attributetable<>'master_system') THEN
                                        SET queryColumns= CONCAT(queryColumns,", `club_id`");
                                        SET queryValues= CONCAT( queryValues,",",clubId);
                                        SET queryDuplicate=CONCAT(queryDuplicate,",`club_id`=VALUES(`club_id`)");
                                END IF;
                            END;
                        ELSEIF(colFg <>'contact_id' AND fieldname !='' ) THEN 
                            BEGIN
                                    SET queryColumns= CONCAT(queryColumns,", `",colFg,"`");
                                    SET queryValues= CONCAT( queryValues,",T.",fieldname);
                                    SET queryDuplicate=CONCAT(queryDuplicate,",`",colFg,"`=VALUES(`",colFg,"`)");
                            END;
                        END IF;
			SET prevLoopTable=attributetable;
						
		   END IF;
		END LOOP dynamiccolumnCursorLoop;
	CLOSE dynamiccolumnCursor;
	END BLOCK_CATEGORY;
	
        IF queryColumns !='' THEN
        SET @attribQuery= CONCAT(queryColumns,') (SELECT ',queryValues,' FROM ',tableName1,' T LEFT JOIN fg_cm_contact C ON C.fed_contact_id=T.fed_contact_id ',subClubQuery,' WHERE T.is_removed="0" AND C.is_permanent_delete="0") ',queryDuplicate);
        SELECT @attribQuery;
        PREPARE exec_qry FROM @attribQuery;
        EXECUTE exec_qry ;
        DEALLOCATE PREPARE exec_qry;
	END IF;
      

COMMIT;
END