DROP PROCEDURE IF EXISTS  `importShareContacts`;
DELIMITER $$;
CREATE PROCEDURE `importShareContacts`(IN `tableName1` TEXT, IN `p_club_id` INT, IN `changed_by` INT, IN `main_contact` TEXT, IN `club_type` TEXT)
BEGIN
    DECLARE record_not_found, attr_set_id INTEGER DEFAULT 0;
    DECLARE has_MC TEXT DEFAULT '0';
    DECLARE is_company text  DEFAULT '0';
    DECLARE dynamicSQL TEXT;
    DECLARE exec_qry TEXT;
    DECLARE mem_type TEXT  DEFAULT '';
    DECLARE fieldname TEXT;
    DECLARE attributetable TEXT;
    DECLARE prevLoopTable TEXT DEFAULT '';
    DECLARE rowc INTEGER;
    DECLARE colfg TEXT;
    DECLARE coltitle TEXT;
    DECLARE attribquery TEXT DEFAULT '';
    DECLARE queryColumns TEXT DEFAULT '';
    DECLARE queryValues TEXT DEFAULT '';
    DECLARE logquery TEXT DEFAULT '';
    DECLARE clubId TEXT DEFAULT '';
    DECLARE colclubId TEXT DEFAULT '';
    DECLARE email_value TEXT DEFAULT '';
    DECLARE fgCmContactColumns TEXT DEFAULT '';
    DECLARE fgCmContactValues TEXT DEFAULT '';
    DECLARE my_var TEXT DEFAULT '';
    DECLARE same_query TEXT DEFAULT '';
    DECLARE tableName TEXT DEFAULT '';
    DECLARE joindate_query TEXT DEFAULT '';
    DECLARE impTable TEXT;
    DECLARE defSubscri INTEGER DEFAULT 1;
    DECLARE lev_col TEXT DEFAULT NULL;
    DECLARE mem_id TEXT DEFAULT '';
    DECLARE fed_mem_id TEXT DEFAULT '';
    DECLARE insclubId TEXT DEFAULT '';
    DECLARE mem_leaving TEXT DEFAULT 'NULL';
    DECLARE contact_type TEXT DEFAULT '';
    DECLARE subscriber_value INTEGER DEFAULT 0;
    DECLARE membership_type_id TEXT DEFAULT '';
    DECLARE query_text TEXT DEFAULT '';



    DECLARE CONTINUE HANDLER FOR
    NOT FOUND SET record_not_found = 1;

	SET SQL_SAFE_UPDATES = 0;

    BLOCK_CATEGORY: BEGIN

	DECLARE dynamiccolumnCursor CURSOR FOR  SELECT * FROM `import_maping` WHERE TRIM(`fg_table`)!='fg_cm_contact' AND `imp_table`=tableName1 order by TRIM(`fg_table`),if(col_fg="is_subscriber",1,2) ;
	DECLARE fgCmContactColumnCursor CURSOR FOR  SELECT * FROM `import_maping` WHERE TRIM(`fg_table`)='fg_cm_contact' AND `imp_table`=tableName1 AND col_fg != 'club_ids' ORDER BY fg_table,if(col_fg="is_subscriber",1,2) ASC;

	IF main_contact='single' THEN
            SET contact_type='Single person';
	ELSE
            SET is_company='1';
            SET contact_type='Company';
            IF main_contact='companyWithMain' THEN
                SET has_MC='1';
            END IF;
	END IF;
	SET lev_col = (SELECT col_imp FROM import_maping WHERE imp_table=tableName1 AND col_fg='leaving_date' LIMIT 1);
	
	SET mem_id='membership_id';
	SET fed_mem_id='fed_membership_id';
    SET fgCmContactColumns='INSERT INTO fg_cm_contact (`club_id`,`main_club_id`,`is_company`,`is_deleted`,`archived_on`,`is_draft`,`created_club_id`,`has_main_contact`,`last_updated`,`created_at`,`import_id`,`import_table`,`club_membership_cat_id` ,`fed_membership_cat_id`';
	SET fgCmContactValues=CONCAT('(SELECT main_club_id,main_club_id,',is_company,',0,0,0,main_club_id,',has_MC,',NOW(),NOW(),`row_id`,"',tableName1,'",',mem_id,',',fed_mem_id);


	OPEN fgCmContactColumnCursor;
		fgCmContactColumnCursor: LOOP
			FETCH fgCmContactColumnCursor INTO rowc,fieldname,colFg,attributetable,impTable,colTitle,colclubId;
			IF record_not_found THEN
				SET record_not_found = 0;
				LEAVE fgCmContactColumnCursor;
			END IF;

			CASE colFg
				 WHEN 'joining_date' THEN
					BEGIN
                                                SET membership_type_id = fieldname;
						-- SET fgCmContactColumns=CONCAT(fgCmContactColumns,',`joining_date`');
						-- SET fgCmContactValues=CONCAT(fgCmContactValues,",IF(`",membership_type_id,"` IS NULL OR `",membership_type_id,"` = '','0000-00-00',IF(`",fieldname,"` = '0000-00-00',NOW(),",fieldname,")) AS joining_date");
					END;
				ELSE
				IF(colFg ='is_subscriber' AND fieldname = '') THEN
						 SET @defSubscri='';
						 SET @logquery= CONCAT("SELECT default_contact_subscription INTO @defSubscri FROM fg_club WHERE id=",p_club_id);
						 PREPARE exec_qry FROM @logquery;
						 EXECUTE exec_qry ;
						 DEALLOCATE PREPARE exec_qry;
						 SET queryValues= CONCAT(queryValues,",",@defSubscri);
						 SET subscriber_value = @defSubscri;
						 SET fgCmContactColumns=CONCAT(fgCmContactColumns,',`is_subscriber`');
						 IF  (length(fieldname)>0)  THEN

						   SET fgCmContactValues=CONCAT(fgCmContactValues,",",fieldname);
						 ELSE

						  SET fgCmContactValues=CONCAT(fgCmContactValues,",",subscriber_value);
						 END IF;

			   ELSE
					  BEGIN
						  SET fgCmContactColumns=CONCAT(fgCmContactColumns,',`',colFg,'`');
						  SET fgCmContactValues=CONCAT(fgCmContactValues,",",fieldname);
					  END;
			  END IF;
			END CASE;


		END LOOP fgCmContactColumnCursor;
    CLOSE fgCmContactColumnCursor;
	SET @dynamicSQL=CONCAT(fgCmContactColumns,')',fgCmContactValues,' FROM ',tableName1,' WHERE  is_removed=0 )');
	PREPARE exec_qry FROM @dynamicSQL;
    EXECUTE exec_qry ;
	DEALLOCATE PREPARE exec_qry;
	-- insert entry for federation and sub federation
	SET @duplicateSql = CONCAT(" INSERT INTO fg_cm_contact (`club_id`,`main_club_id`,`import_table`,`import_id`,`is_company`,`is_deleted`,`created_club_id`,`last_updated`,`created_at`,`joining_date`,`fed_membership_cat_id`,is_fed_membership_confirmed,is_subscriber,has_main_contact)
	(SELECT CL1.id as club_id,C.`main_club_id`,C.`import_table`,C.`import_id`,C.`is_company`,C.`is_deleted`, C.`created_club_id`, C.`last_updated`,C.`created_at`,
   IF(C.fed_membership_cat_id is not null, NOW(), '0000-00-00') as joining_date,C.fed_membership_cat_id,0,CL1.default_contact_subscription,C.has_main_contact
	FROM fg_cm_contact C
	INNER JOIN fg_club CL ON CL.id=C.club_id
	LEFT JOIN fg_club CL1 ON CL1.id=CL.parent_club_id OR CL.federation_id=CL1.id
	WHERE CL.club_type IN ('sub_federation_club','federation_club','sub_federation') AND CL.id !=1 and
	C.import_table='",tableName1,"' )");
	PREPARE exec_qry FROM @duplicateSql;
	EXECUTE exec_qry ;
	DEALLOCATE PREPARE exec_qry;
	-- update federation id 
    SET @updateFedid=CONCAT("UPDATE fg_cm_contact C INNER JOIN fg_cm_contact C2 ON C2.import_id=C.import_id AND C.import_table=C2.import_table AND C2.club_id ='",p_club_id,"'
        SET C.fed_contact_id=C2.id, C.is_fed_membership_confirmed='0' WHERE C.import_table='",tableName1,"' ");
    PREPARE exec_qry FROM @updateFedid;
    EXECUTE exec_qry ;
    DEALLOCATE PREPARE exec_qry;
	-- update sub federation id
	SET @updateSubFedid=CONCAT("UPDATE fg_cm_contact C INNER JOIN fg_cm_contact C2 ON C2.import_id=C.import_id AND C.import_table=C2.import_table
	INNER JOIN fg_club CL ON C2.club_id = CL.id AND CL.club_type = 'sub_federation'
	SET C.subfed_contact_id=C2.id WHERE C.club_id!=CL.federation_id AND C.import_table='",tableName1,"' ");
	PREPARE exec_qry FROM @updateSubFedid;
	EXECUTE exec_qry ;
	DEALLOCATE PREPARE exec_qry;
    IF(membership_type_id != '') THEN
    -- update fed membership joining date as now if null
    SET @updateFedid=CONCAT("UPDATE fg_cm_contact C INNER JOIN ",tableName1," T ON T.row_id= C.import_id
	SET C.joining_date=T.",membership_type_id," WHERE C.club_id='",p_club_id,"' AND  C.import_table='",tableName1,"' AND C.fed_membership_cat_id is not null ");
	PREPARE exec_qry FROM @updateFedid;
	EXECUTE exec_qry ;
	DEALLOCATE PREPARE exec_qry;
    END IF;
	-- insert contact status log
	SET @logquery= CONCAT("INSERT INTO fg_cm_change_log ( `date`,`club_id`, `changed_by`, `confirmed_by`,  `contact_id`,`kind`, `field`, `value_after`,`attribute_id`)
	(SELECT NOW(), T.main_club_id,'",changed_by,"','",changed_by,"', C.fed_contact_id, 'contact status','','active','' FROM ",tableName1," T LEFT JOIN fg_cm_contact C ON C.import_id=T.row_id  WHERE  C.club_id ='",p_club_id,"' and C.import_table='",tableName1,"')");
	PREPARE exec_qry FROM @logquery;
	EXECUTE exec_qry ;
	DEALLOCATE PREPARE exec_qry;
	-- insert contact type log
	SET @logquery= CONCAT("INSERT INTO fg_cm_change_log ( `date`,`club_id`, `changed_by`, `confirmed_by`,  `contact_id`,`kind`, `field`, `value_after`,`attribute_id`)(SELECT NOW(), T.main_club_id , '",changed_by,"','",changed_by,"', C.id, 'contact type','','",contact_type,"','' FROM ",tableName1," T LEFT JOIN fg_cm_contact C ON C.import_id=T.row_id and C.club_id ='",p_club_id,"' WHERE C.import_table='",tableName1,"')");
	PREPARE exec_qry FROM @logquery;
	EXECUTE exec_qry ;
	DEALLOCATE PREPARE exec_qry;


OPEN dynamiccolumnCursor;
  dynamiccolumnCursorLoop: LOOP
    FETCH dynamiccolumnCursor INTO rowc,fieldname,colFg,attributetable,impTable,colTitle,colclubId;
    IF record_not_found THEN
        SET record_not_found = 0;
        LEAVE dynamiccolumnCursorLoop;
    END IF;
	SET my_var=CONCAT(my_var,'++');
	-- insert entry to club table
	IF(colFg = 'club_entry') THEN
		SET colTitle = '';
		IF(fieldname != '') THEN
			SET fieldname = CONCAT(',',fieldname);
			SET colTitle = ',C.club_id';
		END IF;
		SET @logquery= CONCAT("INSERT INTO ",attributetable," (`contact_id`",fieldname,")(SELECT C.id",colTitle," FROM fg_cm_contact C WHERE C.club_id='",colclubId,"' AND C.import_table='",tableName1,"' )");
		PREPARE exec_qry FROM @logquery;
		EXECUTE exec_qry ;
		DEALLOCATE PREPARE exec_qry;
		ITERATE dynamiccolumnCursorLoop;
	END IF;
	IF(colFg <>'is_subscriber' AND fieldname<>'' AND fieldname<>'fed_contact_id' AND fieldname<>'contact_id' ) THEN
		SET @logquery= CONCAT("INSERT INTO fg_cm_change_log ( `date`, `club_id`,`changed_by`, `confirmed_by`,  `contact_id`,`kind`, `field`, `value_after`,`attribute_id`)(SELECT NOW(),T.main_club_id, '",changed_by,"','",changed_by,"', C.id, 'data','",colTitle,"',T.",fieldname,",cast('",colFg,"'as UNSIGNED) FROM ",tableName1," T LEFT JOIN fg_cm_contact C ON C.import_id=T.row_id WHERE C.club_id='",colclubId,"' AND C.import_table='",tableName1,"' AND T.",fieldname," IS NOT NULL AND T.",fieldname," !='' )");
		PREPARE exec_qry FROM @logquery;
		EXECUTE exec_qry ;
		DEALLOCATE PREPARE exec_qry;
	END IF;

	IF(prevLoopTable <> attributetable) THEN
	BEGIN
		IF (queryColumns <>'') THEN
			SET @attribQuery= CONCAT(queryColumns,') (SELECT ',queryValues,' FROM ',tableName1,' T LEFT JOIN fg_cm_contact C ON C.import_id=T.row_id WHERE C.club_id="',insclubId,'" AND C.import_table="',tableName1,'" AND T.is_removed="0") ');
			PREPARE exec_qry FROM @attribQuery;
			EXECUTE exec_qry ;
			DEALLOCATE PREPARE exec_qry;
			SET @attribQuery='';
			SET queryColumns='';
			SET queryValues='';
		END IF;
		IF (attributetable='master_system') THEN
			SET queryColumns=CONCAT("INSERT INTO ",attributetable," (`fed_contact_id`,`",colFg,"`");
			SET queryValues= CONCAT(",T.",fieldname);
			SET insclubId = colclubId;
		ELSE
			IF(SUBSTRING(attributetable,1,11) <> 'master_club')    THEN
				SET queryColumns=CONCAT("INSERT INTO ",attributetable," (`club_id`,`",colFg,"`");
				SET queryValues= CONCAT(colclubId);
			ELSE
				SET queryColumns=CONCAT("INSERT INTO ",attributetable," (`",colFg,"`");
				SET queryValues='';
			END IF;

			IF(length(fieldname)>0) THEN
				IF(length(queryValues)>0) THEN
					SET queryValues= CONCAT( queryValues,",T.",fieldname);
				ELSE
					SET queryValues= CONCAT("T.",fieldname);
				END IF;
			ELSEIF (colFg='fed_contact_id' or colFg='contact_id')  THEN
				IF(length(queryValues)>0) THEN
				   SET queryValues= CONCAT(queryValues,", C.`id`");
				ELSE
					SET queryValues= CONCAT("C.`id`");
				END IF;
			 END IF;


		END IF;
		SET insclubId = colclubId;
	END;
	ELSE
	BEGIN
		SET queryColumns= CONCAT(queryColumns,", `",colFg,"`");
		IF(length(fieldname)>0) THEN
			IF(length(queryValues)>0) THEN
				SET queryValues= CONCAT( queryValues,",T.",fieldname);
			ELSE
				SET queryValues= CONCAT("T.",fieldname);
			END IF;
		ELSEIF (colFg='fed_contact_id' or colFg='contact_id')  THEN
			SET queryValues= CONCAT(queryValues,", C.`id`");
		END IF;
	END;
	END IF;
	SET prevLoopTable=attributetable;
	END LOOP dynamiccolumnCursorLoop;
CLOSE dynamiccolumnCursor;

END BLOCK_CATEGORY;

      
		
    IF queryColumns !='' THEN
        SET @attribQuery= CONCAT(queryColumns,') (SELECT C.id',queryValues,' FROM ',tableName1,' T LEFT JOIN fg_cm_contact C ON C.import_id=T.row_id WHERE C.club_id="',p_club_id,'" AND C.import_table="',tableName1,'" AND T.is_removed="0") ');
        SET my_var=CONCAT(my_var,@attribQuery,'==');
        PREPARE exec_qry FROM @attribQuery;
        EXECUTE exec_qry ;
        DEALLOCATE PREPARE exec_qry;
    END IF;
	
	SET @updateFedmem=CONCAT("UPDATE fg_cm_contact C
	SET C.first_joining_date=C.joining_date
	WHERE C.import_table='",tableName1,"' AND C.fed_membership_cat_id IS NOT NULL AND C.fed_membership_cat_id !='' AND C.id=C.fed_contact_id");
	PREPARE exec_qry FROM @updateFedmem;
	EXECUTE exec_qry ;
	DEALLOCATE PREPARE exec_qry;
	
	SET @updateFedmem=CONCAT("UPDATE fg_cm_contact C
	INNER JOIN fg_club CL ON CL.id=C.club_id AND CL.club_type IN ('standard_club','sub_federation_club','federation_club')
	SET C.first_joining_date=C.joining_date
	WHERE C.import_table='",tableName1,"' AND C.club_membership_cat_id IS NOT NULL AND C.club_membership_cat_id !=''");
	PREPARE exec_qry FROM @updateFedmem;
	EXECUTE exec_qry ;
	DEALLOCATE PREPARE exec_qry;
	-- insert fed membership log
	SET @fed_same_query= CONCAT('INSERT INTO fg_cm_membership_log ( `club_id`,`contact_id`,`membership_id`,`date`,`kind`,`changed_by`, `value_after`) (SELECT "',p_club_id,'",C.id,C.fed_membership_cat_id,C.joining_date, "assigned contacts","',changed_by,'",contactName(C.id) FROM ',tableName1,' T LEFT JOIN fg_cm_contact C ON C.import_id=T.row_id AND C.import_table="',tableName1,'"  WHERE C.import_id=T.row_id AND C.club_id="',p_club_id,'"  AND C.import_table="',tableName1,'" AND C.is_fed_membership_confirmed ="',0,'" AND (C.fed_membership_cat_id IS NOT NULL AND C.fed_membership_cat_id != "") )');
	PREPARE exec_qry FROM @fed_same_query;
	EXECUTE exec_qry ;
	DEALLOCATE PREPARE exec_qry;
		
	IF(lev_col IS NOT NULL AND lev_col !='') THEN
		SET mem_leaving =CONCAT('IF(T.',lev_col,' IS NOT NULL AND T.',lev_col,' !="",T.',lev_col,',NULL)');
		SET @same_query_leving= CONCAT('INSERT INTO fg_cm_membership_log ( `club_id`,`contact_id`,`membership_id`,`date`,`kind`,`changed_by`, `value_before`,`value_after`) (SELECT T.main_club_id,C.id,C.fed_membership_cat_id,C.leaving_date, "assigned contacts","',changed_by,'",contactName(C.id),"-" FROM ',tableName1,' T LEFT JOIN fg_cm_contact C ON C.import_id=T.row_id AND C.import_table="',tableName1,'" WHERE C.import_id=T.row_id AND C.club_id="',p_club_id,'"  AND C.import_table="',tableName1,'" AND (T.fed_membership_id IS NOT NULL AND T.fed_membership_id != "" AND T.',lev_col,' IS NOT NULL AND T.',lev_col,' !="" ) )');
		PREPARE exec_qry FROM @same_query_leving;
		EXECUTE exec_qry ;
		DEALLOCATE PREPARE exec_qry;
		SET mem_leaving =CONCAT('IF(T.',lev_col,' IS NOT NULL AND T.',lev_col,' !="",T.',lev_col,',NULL)');
	END IF;

	SET mem_type='federation';
	SET @same_query_fed_history= CONCAT('INSERT INTO fg_cm_membership_history (`contact_id`,`membership_club_id`,`membership_id`,`membership_type`,`joining_date`,`leaving_date`,`changed_by`) (SELECT C.id,',p_club_id,',C.fed_membership_cat_id,"',mem_type,'",IF(UNIX_TIMESTAMP(C.joining_date) = "0", NULL, C.joining_date) ,IF(UNIX_TIMESTAMP(C.leaving_date) = "0", NULL, C.leaving_date), "',changed_by,'" FROM  fg_cm_contact C WHERE  C.club_id="',p_club_id,'" AND C.import_table="',tableName1,'" AND C.is_fed_membership_confirmed ="',0,'"  AND C.fed_membership_cat_id IS NOT NULL AND C.fed_membership_cat_id   != "" )');
	PREPARE exec_qry FROM @same_query_fed_history;
	EXECUTE exec_qry ;
	DEALLOCATE PREPARE exec_qry;

	SET @same_query_fed_history= CONCAT('INSERT INTO fg_club_assignment(`fed_contact_id`,`club_id`,`from_date`,`is_approved`) (SELECT C.fed_contact_id,C.main_club_id,NOW(),"1" as is_fed_membership_confirmed  FROM  fg_cm_contact C WHERE  C.club_id="',p_club_id,'" AND C.import_table="',tableName1,'"   AND C.fed_membership_cat_id IS NOT NULL AND C.fed_membership_cat_id   != "" )');
	PREPARE exec_qry FROM @same_query_fed_history;
	EXECUTE exec_qry ;
	DEALLOCATE PREPARE exec_qry;
	-- DROP TABLE IF EXISTS `dynamic_Columncursor_share`;
    END