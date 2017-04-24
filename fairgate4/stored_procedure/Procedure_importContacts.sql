DROP PROCEDURE IF EXISTS  `importContacts`//
CREATE PROCEDURE `importContacts`(IN `tableName1` TEXT, IN `tableName2` TEXT, IN `p_club_id` INT, IN `changed_by` INT, IN `main_contact` TEXT, IN `own_table` TEXT,IN `club_type` TEXT,IN `subFedID` TEXT,IN `FedID` TEXT,IN `with_aplication_club` TEXT)
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

	

    DROP TABLE IF EXISTS `dynamic_Columncursor`;
    SET @dynamicSQL=CONCAT('CREATE TEMPORARY TABLE dynamic_columncursor as SELECT * FROM ',tableName2,' WHERE imp_table="',tableName1,'" ORDER BY fg_table,if(col_fg="is_subscriber",1,2) ASC' );
    PREPARE exec_qry FROM @dynamicSQL;
    EXECUTE exec_qry ;
    DEALLOCATE PREPARE exec_qry;

	BLOCK_CATEGORY: BEGIN

	DECLARE dynamiccolumnCursor CURSOR FOR  SELECT * FROM `dynamic_columncursor` WHERE TRIM(`fg_table`)!='fg_cm_contact' order by TRIM(`fg_table`) ;
	DECLARE fgCmContactColumnCursor CURSOR FOR  SELECT * FROM `dynamic_columncursor` WHERE TRIM(`fg_table`)='fg_cm_contact';

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
  IF club_type='federation' OR club_type='sub_federation' THEN
     SET membership_type_id='fed_membership_id';
  ELSEIF club_type='federation_club' OR club_type='sub_federation_club' OR club_type='standard_club' THEN
    SET membership_type_id='membership_id';
  END IF;
  select membership_type_id;
  SET mem_id='membership_id';
  SET fed_mem_id='fed_membership_id';
        	SET fgCmContactColumns='INSERT INTO fg_cm_contact (`club_id`,`main_club_id`,`is_company`,`is_deleted`,`archived_on`,`is_draft`,`created_club_id`,`has_main_contact`,`last_updated`,`created_at`,`import_id`,`import_table`,`club_membership_cat_id` ,`fed_membership_cat_id`';
	SET fgCmContactValues=CONCAT('(SELECT ',p_club_id,',',p_club_id,',',is_company,',0,0,0,',p_club_id,',',has_MC,',NOW(),NOW(),`row_id`,"',tableName1,'",',mem_id,',',fed_mem_id);


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

                                    SET fgCmContactColumns=CONCAT(fgCmContactColumns,',`joining_date`');
                                    SET fgCmContactValues=CONCAT(fgCmContactValues,",IF(`",membership_type_id,"` IS NULL OR `",membership_type_id,"` = '','0000-00-00',IF(`",fieldname,"` = '0000-00-00',NOW(),",fieldname,")) AS joining_date");
                                    END;
                            WHEN 'leaving_date' THEN
                                BEGIN
                                    SET fgCmContactColumns=CONCAT(fgCmContactColumns,',`leaving_date`');
                                    SET fgCmContactValues=CONCAT(fgCmContactValues,",IF(`",membership_type_id,"` IS NULL OR `",membership_type_id,"` = '','0000-00-00',IF(`",fieldname,"` = '0000-00-00',NULL,",fieldname,")) AS leaving_date");
                                        Select  fgCmContactValues ;
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
   SELECT @dynamicSQL;
   PREPARE exec_qry FROM @dynamicSQL;
     EXECUTE exec_qry ;
  DEALLOCATE PREPARE exec_qry;

       SET @duplicateSql = CONCAT(" INSERT INTO fg_cm_contact (`club_id`,`main_club_id`,`import_table`,`import_id`,`is_company`,`is_deleted`,`created_club_id`,`last_updated`,`created_at`,`joining_date`,`fed_membership_cat_id`,is_fed_membership_confirmed,is_subscriber,has_main_contact)
		(SELECT CL1.id as club_id,C.`main_club_id`,C.`import_table`,C.`import_id`,C.`is_company`,C.`is_deleted`, C.`created_club_id`, C.`last_updated`,C.`created_at`,
       IF(C.fed_membership_cat_id is not null, NOW(), '0000-00-00') as joining_date,C.fed_membership_cat_id,CL.assign_fedmembership_with_application,CL1.default_contact_subscription,C.has_main_contact
        FROM fg_cm_contact C
        INNER JOIN fg_club CL ON CL.id=C.club_id
        LEFT JOIN fg_club CL1 ON CL1.id=CL.parent_club_id OR CL.federation_id=CL1.id
        WHERE CL.club_type IN ('sub_federation_club','federation_club','sub_federation') AND CL.id !=1 and
        C.import_table='",tableName1,"' )");
        select @duplicateSql;
        PREPARE exec_qry FROM @duplicateSql;
        EXECUTE exec_qry ;
        DEALLOCATE PREPARE exec_qry;

    SET @updateFedid=CONCAT("UPDATE fg_cm_contact C INNER JOIN fg_cm_contact C2 ON C2.import_id=C.import_id AND C.import_table=C2.import_table AND C2.club_id ='",FedID,"'
        SET C.fed_contact_id=C2.id, C.is_fed_membership_confirmed='0' WHERE C.main_club_id='",p_club_id,"' AND  C.import_table='",tableName1,"' ");
    PREPARE exec_qry FROM @updateFedid;
    select @updateFedid;
    EXECUTE exec_qry ;
    DEALLOCATE PREPARE exec_qry;

    IF  club_type='sub_federation_club' or club_type='sub_federation' THEN
        SET @updateSubFedid=CONCAT("UPDATE fg_cm_contact C INNER JOIN fg_cm_contact C2 ON C2.import_id=C.import_id AND C.import_table=C2.import_table  AND C2.club_id ='",subFedID,"'
        SET C.subfed_contact_id=C2.id WHERE C.main_club_id='",p_club_id,"' AND C.club_id!='",FedID,"' AND  C.import_table='",tableName1,"' ");
       PREPARE exec_qry FROM @updateSubFedid;
       EXECUTE exec_qry ;
      DEALLOCATE PREPARE exec_qry;
    END IF;
    IF club_type='federation_club' OR club_type='sub_federation_club'  OR club_type='standard_club' THEN
        SET @updateFedid=CONCAT("UPDATE fg_cm_contact C
         SET C.joining_date=NOW() WHERE C.club_id='",p_club_id,"' AND  C.import_table='",tableName1,"' AND C.club_membership_cat_id is not null AND (C.joining_date is null or UNIX_TIMESTAMP(C.joining_date) ='",0,"' )  ");
         PREPARE exec_qry FROM @updateFedid;
         EXECUTE exec_qry ;
        DEALLOCATE PREPARE exec_qry;
   END IF;
        SET @updateFedid=CONCAT("UPDATE fg_cm_contact C
            SET C.joining_date=NOW() WHERE C.club_id='",FedID,"' AND  C.import_table='",tableName1,"' AND C.fed_membership_cat_id is not null AND (C.joining_date is null or UNIX_TIMESTAMP(C.joining_date) ='",0,"' )  ");
        PREPARE exec_qry FROM @updateFedid;
         EXECUTE exec_qry ;
        DEALLOCATE PREPARE exec_qry;

    SET @logquery= CONCAT("INSERT INTO fg_cm_change_log ( `date`,`club_id`, `changed_by`, `confirmed_by`,  `contact_id`,`kind`, `field`, `value_after`,`attribute_id`)
        (SELECT NOW(), '",p_club_id,"','",changed_by,"','",changed_by,"', C.fed_contact_id, 'contact status','','active','' FROM ",tableName1," T LEFT JOIN fg_cm_contact C ON C.import_id=T.row_id  WHERE  C.club_id ='",FedID,"' and C.import_table='",tableName1,"')");
	 PREPARE exec_qry FROM @logquery;
	 EXECUTE exec_qry ;
	 DEALLOCATE PREPARE exec_qry;

   SET @logquery= CONCAT("INSERT INTO fg_cm_change_log ( `date`,`club_id`, `changed_by`, `confirmed_by`,  `contact_id`,`kind`, `field`, `value_after`,`attribute_id`)(SELECT NOW(),'",p_club_id,"' , '",changed_by,"','",changed_by,"', C.id, 'contact type','','",contact_type,"','' FROM ",tableName1," T LEFT JOIN fg_cm_contact C ON C.import_id=T.row_id and C.club_id ='",FedID,"' WHERE C.import_table='",tableName1,"')");
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

  IF(colFg <>'is_subscriber' AND fieldname<>'' AND fieldname<>'fed_contact_id' AND fieldname<>'contact_id' ) THEN
    SET @logquery= CONCAT("INSERT INTO fg_cm_change_log ( `date`, `club_id`,`changed_by`, `confirmed_by`,  `contact_id`,`kind`, `field`, `value_after`,`attribute_id`)(SELECT NOW(),'",p_club_id,"', '",changed_by,"','",changed_by,"', C.id, 'data','",colTitle,"',T.",fieldname,",cast('",colFg,"'as UNSIGNED) FROM ",tableName1," T LEFT JOIN fg_cm_contact C ON C.import_id=T.row_id WHERE C.club_id='",colclubId,"' AND C.import_table='",tableName1,"' AND T.",fieldname," IS NOT NULL AND T.",fieldname," !='' )");
     Select @logquery;
     PREPARE exec_qry FROM @logquery;
   EXECUTE exec_qry ;
  DEALLOCATE PREPARE exec_qry;
  END IF;

  IF(prevLoopTable <> attributetable) THEN
  BEGIN
    IF (queryColumns <>'') THEN
		SET @attribQuery= CONCAT(queryColumns,') (SELECT ',queryValues,' FROM ',tableName1,' T LEFT JOIN fg_cm_contact C ON C.import_id=T.row_id WHERE C.club_id="',insclubId,'" AND C.import_table="',tableName1,'" AND T.is_removed="0") ');
        Select @attribQuery;
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
                Select  queryValues;
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
        SET @attribQuery= CONCAT(queryColumns,') (SELECT C.id',queryValues,' FROM ',tableName1,' T LEFT JOIN fg_cm_contact C ON C.import_id=T.row_id WHERE C.club_id="',FedID,'" AND C.import_table="',tableName1,'" AND T.is_removed="0") ');
        SET my_var=CONCAT(my_var,@attribQuery,'==');
        Select  @attribQuery;
        PREPARE exec_qry FROM @attribQuery;
        EXECUTE exec_qry ;
        DEALLOCATE PREPARE exec_qry;
    END IF;
	
	        SET @updateFedmem=CONCAT("UPDATE fg_cm_contact C
		SET C.first_joining_date=C.joining_date
		WHERE C.import_table='",tableName1,"' AND C.fed_membership_cat_id IS NOT NULL AND C.fed_membership_cat_id !='' AND             C.id=C.fed_contact_id");
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
        SET @same_query= CONCAT('INSERT INTO fg_cm_membership_log ( `club_id`,`contact_id`,`membership_id`,`date`,`kind`,`changed_by`, `value_after`) (SELECT "',p_club_id,'",C.id,C.club_membership_cat_id,C.joining_date, "assigned contacts","',changed_by,'",contactName(C.id) FROM ',tableName1,' T LEFT JOIN fg_cm_contact C ON C.import_id=T.row_id AND C.import_table="',tableName1,'"  WHERE C.import_id=T.row_id AND C.club_id="',p_club_id,'"  AND C.import_table="',tableName1,'" AND (C.club_membership_cat_id IS NOT NULL AND C.club_membership_cat_id!= "") )');
        PREPARE exec_qry FROM @same_query;
        EXECUTE exec_qry ;
        DEALLOCATE PREPARE exec_qry;
            IF with_aplication_club=1 AND (club_type='federation_club' OR club_type='sub_federation_club') THEN
		SET @updateWapplication=CONCAT("UPDATE fg_cm_contact C
		SET C.is_fed_membership_confirmed='",with_aplication_club,"' WHERE C.main_club_id='",p_club_id,"' AND  C.import_table='",tableName1,"' AND C.fed_membership_cat_id is not null   ");
		PREPARE exec_qry FROM @updateWapplication;
		select @updateWapplication;
		EXECUTE exec_qry ;
		DEALLOCATE PREPARE exec_qry;
                SET @same_query_fedconfrmlog= CONCAT('INSERT INTO fg_cm_fedmembership_confirmation_log (`club_id`, `contact_id`, `federation_club_id`,`existing_club_ids`, `modified_date`, `modified_by`, `fedmembership_value_after`, `status`) (SELECT "',p_club_id,'",C.id,C.club_id,C.main_club_id,C.joining_date, "',changed_by,'" ,C.fed_membership_cat_id,"pending" FROM fg_cm_contact C WHERE  C.club_id="',FedID,'" AND C.import_table="',tableName1,'" AND C.fed_membership_cat_id IS NOT NULL AND C.fed_membership_cat_id != "" )');
		PREPARE exec_qry FROM @same_query_fedconfrmlog;
		EXECUTE exec_qry ;
                DEALLOCATE PREPARE exec_qry;
            END IF;
	  

      SET @fed_same_query= CONCAT('INSERT INTO fg_cm_membership_log ( `club_id`,`contact_id`,`membership_id`,`date`,`kind`,`changed_by`, `value_after`) (SELECT "',FedID,'",C.id,C.fed_membership_cat_id,C.joining_date, "assigned contacts","',changed_by,'",contactName(C.id) FROM ',tableName1,' T LEFT JOIN fg_cm_contact C ON C.import_id=T.row_id AND C.import_table="',tableName1,'"  WHERE C.import_id=T.row_id AND C.club_id="',FedID,'"  AND C.import_table="',tableName1,'" AND C.is_fed_membership_confirmed ="',0,'" AND (C.fed_membership_cat_id IS NOT NULL AND C.fed_membership_cat_id != "") )');
Select @fed_same_query;
      PREPARE exec_qry FROM @fed_same_query;
      EXECUTE exec_qry ;
      DEALLOCATE PREPARE exec_qry;
        Select lev_col;
       IF(lev_col IS NOT NULL AND lev_col !='') THEN
            SET @same_query_leving= CONCAT('INSERT INTO fg_cm_membership_log ( `club_id`,`contact_id`,`membership_id`,`date`,`kind`,`changed_by`, `value_before`,`value_after`) (SELECT "',p_club_id,'",C.id,C.club_membership_cat_id,C.leaving_date, "assigned contacts","',changed_by,'",contactName(C.id),"-" FROM ',tableName1,' T LEFT JOIN fg_cm_contact C ON C.import_id=T.row_id AND C.import_table="',tableName1,'" WHERE C.import_id=T.row_id AND C.club_id="',p_club_id,'"  AND C.import_table="',tableName1,'" AND (T.membership_id IS NOT NULL AND T.membership_id != "" AND T.',lev_col,' IS NOT NULL AND T.',lev_col,' !="" ) )');
		 Select @same_query_leving;
             PREPARE exec_qry FROM @same_query_leving;
             EXECUTE exec_qry ;
             DEALLOCATE PREPARE exec_qry;
            SET mem_leaving =CONCAT('IF(T.',lev_col,' IS NOT NULL AND T.',lev_col,' !="",T.',lev_col,',NULL)');
            SET @same_query_leving= CONCAT('INSERT INTO fg_cm_membership_log ( `club_id`,`contact_id`,`membership_id`,`date`,`kind`,`changed_by`, `value_before`,`value_after`) (SELECT "',p_club_id,'",C.id,C.fed_membership_cat_id,C.leaving_date, "assigned contacts","',changed_by,'",contactName(C.id),"-" FROM ',tableName1,' T LEFT JOIN fg_cm_contact C ON C.import_id=T.row_id AND C.import_table="',tableName1,'" WHERE C.import_id=T.row_id AND C.club_id="',FedID,'"  AND C.import_table="',tableName1,'" AND (T.fed_membership_id IS NOT NULL AND T.fed_membership_id != "" AND T.',lev_col,' IS NOT NULL AND T.',lev_col,' !="" ) )');
			Select @same_query_leving;
             PREPARE exec_qry FROM @same_query_leving;
             EXECUTE exec_qry ;
             DEALLOCATE PREPARE exec_qry;
            SET mem_leaving =CONCAT('IF(T.',lev_col,' IS NOT NULL AND T.',lev_col,' !="",T.',lev_col,',NULL)');
        END IF;

        SET mem_type='club';
        SET @same_query_history= CONCAT('INSERT INTO fg_cm_membership_history (`contact_id`,`membership_club_id`,`membership_id`,`membership_type`,`joining_date`,`leaving_date`,`changed_by`) (SELECT C.id,',p_club_id,',C.club_membership_cat_id,"',mem_type,'",IF(UNIX_TIMESTAMP(C.joining_date) = "0", NULL, C.joining_date),IF(UNIX_TIMESTAMP(C.leaving_date) = "0", NULL, C.leaving_date) , "',changed_by,'" FROM fg_cm_contact C WHERE  C.club_id="',p_club_id,'" AND C.import_table="',tableName1,'" AND C.club_membership_cat_id IS NOT NULL AND C.club_membership_cat_id != "" )');
		Select @same_query_history;
	 PREPARE exec_qry FROM @same_query_history;
	 EXECUTE exec_qry ;
	 DEALLOCATE PREPARE exec_qry;

         SET mem_type='federation';
         SET @same_query_fed_history= CONCAT('INSERT INTO fg_cm_membership_history (`contact_id`,`membership_club_id`,`membership_id`,`membership_type`,`joining_date`,`leaving_date`,`changed_by`) (SELECT C.id,',FedID,',C.fed_membership_cat_id,"',mem_type,'",IF(UNIX_TIMESTAMP(C.joining_date) = "0", NULL, C.joining_date) ,IF(UNIX_TIMESTAMP(C.leaving_date) = "0", NULL, C.leaving_date), "',changed_by,'" FROM  fg_cm_contact C WHERE  C.club_id="',FedID,'" AND C.import_table="',tableName1,'" AND C.is_fed_membership_confirmed ="',0,'"  AND C.fed_membership_cat_id IS NOT NULL AND C.fed_membership_cat_id   != "" )');
		 Select @same_query_fed_history;
	 PREPARE exec_qry FROM @same_query_fed_history;
	 EXECUTE exec_qry ;
	 DEALLOCATE PREPARE exec_qry;
    


        IF club_type='federation_club' OR club_type='sub_federation_club' OR club_type='standard_club' THEN
            SET @updateClubmem=CONCAT("UPDATE fg_cm_contact C
            SET C.club_membership_cat_id=NULL WHERE C.club_id='",p_club_id,"' AND  C.import_table='",tableName1,"' AND C.club_membership_cat_id is not null  AND UNIX_TIMESTAMP(C.leaving_date)!='",0,"'  AND (C.leaving_date is not null   or C.leaving_date <'",NOW(),"' )  ");
		 Select @updateClubmem;
            PREPARE exec_qry FROM @updateClubmem;
            select @updateClubmem;
            EXECUTE exec_qry ;
            DEALLOCATE PREPARE exec_qry;
             SET @same_query_fed_history= CONCAT('INSERT INTO fg_club_assignment(`fed_contact_id`,`club_id`,`from_date`,`is_approved`) (SELECT C.fed_contact_id,',p_club_id,',NOW(),IF(C.is_fed_membership_confirmed = "1", "0", "1") as is_fed_membership_confirmed  FROM  fg_cm_contact C WHERE  C.club_id="',FedID,'" AND C.import_table="',tableName1,'"   AND C.fed_membership_cat_id IS NOT NULL AND C.fed_membership_cat_id   != "" )');
             PREPARE exec_qry FROM @same_query_fed_history;
            EXECUTE exec_qry ;
            DEALLOCATE PREPARE exec_qry;
        END IF;

        IF club_type='federation_club' OR club_type='sub_federation_club' OR club_type='federation' OR club_type='sub_federation' THEN
            SET @updateFedmem=CONCAT("UPDATE fg_cm_contact C
            SET C.fed_membership_cat_id=NULL WHERE C.club_id='",FedID,"' AND  C.import_table='",tableName1,"' AND C.fed_membership_cat_id is not null   AND UNIX_TIMESTAMP(C.leaving_date)!='",0,"' AND (C.leaving_date is not null or C.leaving_date <'",NOW(),"' )  ");
            PREPARE exec_qry FROM @updateFedmem;
            select @updateFedmem;
            EXECUTE exec_qry ;
            DEALLOCATE PREPARE exec_qry;
        END IF;
       

    COMMIT;
    END