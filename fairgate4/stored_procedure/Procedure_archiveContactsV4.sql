DROP PROCEDURE IF EXISTS `archiveContactsV4`//
CREATE PROCEDURE `archiveContactsV4`(IN tablename TEXT,IN currentuser INT,IN clubid INT,IN system_att_first_name VARCHAR(50),IN system_att_last_name VARCHAR(50),IN system_att_company_name VARCHAR(50),IN system_att_dob  VARCHAR(50),IN team_terminology VARCHAR(50),IN date_today DATETIME,IN str_todate DATETIME,IN simple_date DATETIME, IN present_time INT,IN leaving_date_in VARCHAR(50), IN club_heirarchy VARCHAR(50), IN currentClubType VARCHAR(50), IN primaryEmailSubs VARCHAR(255), IN salutaionSubs VARCHAR(255),genderSubs VARCHAR(255), IN system_att_corres_lang VARCHAR(50), userDefined_leavingDate VARCHAR(50))
BEGIN
    
    DECLARE loop_iteration_var,count_userid,count_user,user_contact_id,count_security,count_security1,role_log_id,count_con,contactid1,contactid2,contactid3 INTEGER;
    DECLARE gpcount,user_id_new,contact_cursor_fed_membership_cat_id,contact_ids_cursor_contact_id,is_postal_address_val,comp_def_contact_new INTEGER DEFAULT "";
    DECLARE contact_cursor_contact_id, fedContactId, subFedContactId, contact_cursor_fedContactId, contact_cursor_subFedContactId, is_company1,is_company2,is_company3,is_company4,contact_id1,club_id1,contact_id_user,funid,user_contact_idy,count_securityn INTEGER;
    DECLARE cursor_linked_contact_id,group_id_new,role_id_new,is_executive_board_val,rcid,team_roster_category_id,contactid4,contactidn, is_companyn,contact_cursor_is_draft, contact_cursor_is_subscriber,outReturnCode  INTEGER;
    DECLARE category_title_new,value_before_new,group_type_new,group_type1 TEXT;
    DECLARE sf_guard_user_var,usergroup_name,group_name1,group_name,group_name2,group_name3,group_title_new,role_title, succ_mess, sql_err TEXT DEFAULT "";
    DECLARE role_type,function_title,comp_def_contact_fun_new TEXT DEFAULT "";
    DECLARE group_name_var,contactname,contactname1,contactname2,default_contactname,company_name1, fst_nme,lst_nme,fst_nme1,lst_nme1,fst_nme2,lst_nme2,fst_nme3,lst_nme3 VARCHAR(50);
    DECLARE first_name1,last_name1,def_contact_name,company_name,first_name,last_name,company_name2,first_name2,last_name2, company_namen,contactnamen,lst_nmen,fst_nmen,value_after_change,value_before_active VARCHAR(50);
    DECLARE first_namen VARCHAR(50) DEFAULT NULL;
    DECLARE last_namen VARCHAR(50) DEFAULT NULL;
    DECLARE record_not_found,subscriberflag INTEGER DEFAULT 0;
    DECLARE loop2query2 TEXT;DECLARE exec_qry,exec_qry1 TEXT;
    DECLARE insertqry, logstr1,logstr2,logstr3,logstr4,logstr5,logstr6,logstr7,logstr8,insertSubscriberLog, testStr TEXT;
    DECLARE pre_query, exec_sel_qry, dynamicSQL,insertEmailLog,insertFirstnameLog,insertLastnameLog,insertCompanyLog,insertGenderLog,insertSalutationLog TEXT;
    DECLARE mastertablename,linked_club_type,catagory_contact_assign VARCHAR(50);
    DECLARE linked_club_id,linked_contact_id_cursor, fgcm_contact_id, rc_contact_id, contact_connection_cursor_contact_id, contact_connection_cursor_club_id, contact_connection_cursor_fedContactId, contact_connection_cursor_subFedContactId INTEGER;
    DECLARE cat_id_new,cat_club,is_fed_cat,is_executive_board,last_inserted_role_log_id,household_contact_id,household_linked_contact_id,household_club_id,household_linked_club_id INTEGER;
    DECLARE archive_contact_name,cat_type,household_relation_name,household_linked_relation_name,currentClubtable VARCHAR(255);
    DECLARE other_contact_id,other_linkedcontact_id,other_relation_id,other_club_id,group_club_id,company_def_club_id,manual_role_type_count INTEGER;
    DECLARE other_relation, other_name, other_connection_type, mainContactName, mastertablenameMembership, heirarchy_club_type VARCHAR(255);
    DECLARE contact_cursor_club_id,contact_cursor_club_membership_cat_id, heirarchy_club_id, count,contact_cursor_is_former_fed_member INTEGER;
    DECLARE contactidscursor CURSOR FOR 
    SELECT `contact_id` FROM `archivecontactsto` WHERE `club_id`= clubid AND `author`=currentuser AND `archived_date`=date_today;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET record_not_found = 1;    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
            ROLLBACK;
    END;
    START TRANSACTION;
    SET @insertqry = "INSERT INTO `fg_cm_change_log` (`contact_id`,`club_id`, `date`, `kind`, `field`,`value_before`, `value_after`,`changed_by`, `confirmed_by` ) VALUES";
    SET @testStr = " ";
    BLOCK1: BEGIN
        OPEN contactidscursor;
            contact_ids_one: LOOP
                FETCH contactidscursor INTO contact_ids_cursor_contact_id;
                IF record_not_found THEN
                        SET record_not_found = 0;
                        LEAVE contact_ids_one;
                END IF;
                SELECT contactName(contact_ids_cursor_contact_id) INTO mainContactName;
                SELECT `is_company` INTO is_companyn FROM `fg_cm_contact` WHERE `id` = contact_ids_cursor_contact_id;
                SELECT `is_subscriber` INTO subscriberflag FROM `archivecontactsto` WHERE `contact_id` = contact_ids_cursor_contact_id AND `archived_date`=date_today;
                SELECT `fed_contact_id`, `subfed_contact_id` INTO fedContactId, subFedContactId FROM `fg_cm_contact` WHERE `id` = contact_ids_cursor_contact_id;
                BLOCK2: BEGIN
                    DECLARE contactcursor CURSOR FOR
                    SELECT c.`id`,c.`club_id`, c.`fed_contact_id`, c.`subfed_contact_id`, c.`is_draft`, c.`is_subscriber`,c.`fed_membership_cat_id`,c.`club_membership_cat_id`,c.`is_former_fed_member` FROM `fg_cm_contact` AS c LEFT JOIN `fg_cm_membership` AS m ON m.`id`= c.`fed_membership_cat_id` WHERE c.`id` IN(fedContactId, subFedContactId, contact_ids_cursor_contact_id) ;
                    OPEN contactcursor;
                        loop_contact: LOOP
                            FETCH contactcursor INTO contact_cursor_contact_id, contact_cursor_club_id, contact_cursor_fedContactId, contact_cursor_subFedContactId, contact_cursor_is_draft, contact_cursor_is_subscriber, contact_cursor_fed_membership_cat_id,contact_cursor_club_membership_cat_id, contact_cursor_is_former_fed_member ;
                            IF record_not_found THEN
                                SET record_not_found = 0;
                                LEAVE loop_contact;
                            END IF;

                            IF(contact_cursor_is_draft =1 AND contact_cursor_is_subscriber =1)THEN
                                SELECT `value_after` INTO value_after_change FROM `fg_cm_change_log`  WHERE `contact_id` = contact_cursor_contact_id AND `kind` = 'contact status' ORDER BY `id` DESC LIMIT 1;
                                IF(value_after_change!='')THEN
                                    SET value_before_active =  value_after_change;
                                ELSE
                                    SET value_before_active =  'Subscriber (Contact administration)';
                                END IF;
                            ELSE
                                SET value_before_active =   'Active'; 
                            END IF;
                            
                            IF (contact_cursor_contact_id = contact_cursor_fedContactId) THEN
                                SET logstr2 = CONCAT("(",contact_cursor_contact_id,",NULL,","'",date_today,"'",",","'",'contact status',"'",",","'",'-',"'",",","'",value_before_active,"'",",","'",'Archived',"'",",",currentuser,",",currentuser,")");								
                                SET @insertqry = CONCAT(@insertqry,logstr2,',');
                            END IF;
                            
                            -- Updating fg_cm_contact 
                            IF (contact_cursor_club_membership_cat_id IS NOT NULL OR contact_cursor_club_membership_cat_id !='') THEN
                                SET @logstr8 = CONCAT("UPDATE `fg_cm_contact` SET  `is_deleted`= '1',`is_seperate_invoice` = '0', `archived_on`='",leaving_date_in,"', `leaving_date`='",userDefined_leavingDate,"',`last_updated`='",leaving_date_in,"',`is_fed_membership_confirmed`='0', `fed_membership_cat_id` = ","NULL",", `resigned_on`='",leaving_date_in,"', `club_membership_cat_id` = ", "NULL"," WHERE `id`= ",contact_cursor_contact_id);
                            ELSE 
                                SET @logstr8 = CONCAT("UPDATE `fg_cm_contact` SET  `is_deleted`= '1',`is_seperate_invoice` = '0', `archived_on`='",leaving_date_in,"',`last_updated`='",leaving_date_in,"',`is_fed_membership_confirmed`='0', `fed_membership_cat_id` = ","NULL",", `club_membership_cat_id` = ","NULL"," WHERE `id`= ",contact_cursor_contact_id);
                            END IF;

                            SET @primaryEmailQry=CONCAT("SELECT `",primaryEmailSubs,"` INTO @primaryEmail FROM `master_system` WHERE `fed_contact_id`=",fedContactId); 
                            PREPARE exec_sel_qry FROM @primaryEmailQry;
                            EXECUTE exec_sel_qry ;	
                            DEALLOCATE PREPARE exec_sel_qry;

                            IF ((contact_ids_cursor_contact_id = contact_cursor_contact_id) AND (@primaryEmail != '' AND @primaryEmail IS NOT NULL) AND (subscriberflag=1)) THEN
                                SET @updateOwnTable = CONCAT("UPDATE `fg_cm_contact` SET `is_subscriber`=0 WHERE `id`=",contact_cursor_contact_id);
                                PREPARE exec_up_qry FROM @updateOwnTable;
                                EXECUTE exec_up_qry ;	
                                DEALLOCATE PREPARE exec_up_qry;

                                SET @selectUserDetails= CONCAT("INSERT INTO `fg_cn_subscriber` (`club_id`,`email`,`first_name`,`last_name`,`company`,`gender`,`salutation`,`correspondance_lang`,`created_at`,`created_by`) SELECT ",contact_cursor_club_id,",'",@primaryEmail,"',`",system_att_first_name,"`,`",system_att_last_name,"`,`",system_att_company_name,"`,`",genderSubs,"`,`",salutaionSubs,"`,`",system_att_corres_lang,"`,'",leaving_date_in,"',",currentuser," FROM `master_system` WHERE `fed_contact_id`=",fedContactId); 
                                PREPARE exec_ins_qry FROM @selectUserDetails;
                                EXECUTE exec_ins_qry ;	
                                DEALLOCATE PREPARE exec_ins_qry;

                                SELECT LAST_INSERT_ID() INTO @subscriberId;

                                SET @systemValues=CONCAT("SELECT `",primaryEmailSubs,"`,`",system_att_first_name,"`,`",system_att_last_name,"`,`",system_att_company_name,"`,`",genderSubs,"`,`",salutaionSubs,"`,`",system_att_corres_lang,"` INTO @primaryEmailSys,@firstnameSys,@lastnameSys,@companySys,@genderSys,@salutaionSys,@corresLangSys FROM `master_system` WHERE `fed_contact_id`=",fedContactId);
                                PREPARE exec_sel_qry FROM @systemValues;
                                EXECUTE exec_sel_qry ;	
                                DEALLOCATE PREPARE exec_sel_qry;

                                SET @insertSubscriberLog = "INSERT INTO `fg_cn_subscriber_log` (`subscriber_id`,`club_id`,`date`,`kind`,`field`,`value_before`,`value_after`,`changed_by`) VALUES";
                                IF(@primaryEmailSys IS NOT NULL AND @primaryEmailSys !='' )THEN
                                        SET @insertSubscriberLog=CONCAT(@insertSubscriberLog," (",@subscriberId,",",contact_cursor_club_id,",'",leaving_date_in,"','data','email','','",@primaryEmailSys,"',",currentuser,"),");
                                END IF;
                                IF(@firstnameSys IS NOT NULL AND @firstnameSys !='' )THEN
                                        SET @firstnameTrimmed = REPLACE(@firstnameSys, "'", "''");
                                        SET @insertSubscriberLog=CONCAT(@insertSubscriberLog," (",@subscriberId,",",contact_cursor_club_id,",'",leaving_date_in,"','data','first_name','','",@firstnameTrimmed,"',",currentuser,"),");
                                END IF;
                                IF(@lastnameSys IS NOT NULL AND @lastnameSys !='' )THEN
                                        SET @lastnameTrimmed = REPLACE(@lastnameSys, "'", "''");
                                        SET @insertSubscriberLog=CONCAT(@insertSubscriberLog," (",@subscriberId,",",contact_cursor_club_id,",'",leaving_date_in,"','data','last_name','','",@lastnameTrimmed,"',",currentuser,"),");
                                END IF;
                                IF(@companySys IS NOT NULL AND @companySys !='' )THEN
                                        SET @companyTrimmed = REPLACE(@companySys, "'", "''");
                                        SET @insertSubscriberLog=CONCAT(@insertSubscriberLog," (",@subscriberId,",",contact_cursor_club_id,",'",leaving_date_in,"','data','company','','",@companyTrimmed,"',",currentuser,"),");
                                END IF;
                                IF(@genderSys IS NOT NULL AND @genderSys !='' )THEN
                                        SET @insertSubscriberLog=CONCAT(@insertSubscriberLog," (",@subscriberId,",",contact_cursor_club_id,",'",leaving_date_in,"','data','gender','','",@genderSys,"',",currentuser,"),");
                                END IF;
                                IF(@salutaionSys IS NOT NULL AND @salutaionSys !='' )THEN
                                        SET @insertSubscriberLog=CONCAT(@insertSubscriberLog," (",@subscriberId,",",contact_cursor_club_id,",'",leaving_date_in,"','data','salutation','','",@salutaionSys,"',",currentuser,"),");
                                END IF;
                                IF(@corresLangSys IS NOT NULL AND @corresLangSys !='' )THEN
                                        SET @insertSubscriberLog=CONCAT(@insertSubscriberLog," (",@subscriberId,",",contact_cursor_club_id,",'",leaving_date_in,"','data','correspondance_lang','','",@corresLangSys,"',",currentuser,")");
                                END IF;

                                SET @insertSubscriberLog = CONCAT(@insertSubscriberLog,'##');
                                SET @insertSubscriberLog  = REPLACE(@insertSubscriberLog,',##','');
                                PREPARE exec_ins_qry FROM @insertSubscriberLog;
                                EXECUTE exec_ins_qry ;	
                                DEALLOCATE PREPARE exec_ins_qry;

                            END IF;

                            -- This block is used to update fg_cm_membership_history and fg_cm_membership log
                            -- if the contact is a fed_member
                            IF(contact_cursor_club_membership_cat_id IS NOT NULL OR contact_cursor_club_membership_cat_id != '')THEN 

                                INSERT INTO `fg_cm_membership_log` (`club_id`,`membership_id`,`date`,`kind`,`field`,`value_before`,`value_after`,`changed_by`) VALUES (contact_cursor_club_id,contact_cursor_club_membership_cat_id,date_today,'assigned contacts','',mainContactName,'',currentuser);
                                UPDATE `fg_cm_membership_history` SET `leaving_date`=userDefined_leavingDate WHERE `contact_id`=contact_cursor_contact_id AND `membership_id`=contact_cursor_club_membership_cat_id ORDER BY `id` DESC LIMIT 1;

                            END IF;
                            
                            -- Appending log entry query
                            PREPARE exec_qry1 FROM @logstr8;
                            EXECUTE exec_qry1;
                            DEALLOCATE PREPARE exec_qry1; 

                        END LOOP loop_contact;
                    CLOSE contactcursor;
                END BLOCK2;



                -- This section is used to update the household_head field in the master tables 
                -- if the contact have a household connection
                SET mastertablename = '';
                BLOCK3: BEGIN  
                    DECLARE masterclubcursor CURSOR FOR 
                    SELECT distinct fgcmlc.`club_id` , fc.`club_type`,fgcmlc.`linked_contact_id`, fgcmlc.`contact_id`
                    FROM `fg_cm_linkedcontact` AS fgcmlc 
                    LEFT JOIN `fg_club` as fc ON fgcmlc.`club_id`= fc.`id`
                    WHERE fgcmlc.`contact_id` IN(fedContactId, subFedContactId,contact_ids_cursor_contact_id) AND fgcmlc.`type` = 'household';
                    OPEN masterclubcursor;
                        loopmasterclub: LOOP
                            FETCH masterclubcursor INTO linked_club_id,linked_club_type,linked_contact_id_cursor, fgcm_contact_id;
                            IF record_not_found THEN
                                SET record_not_found = 0;
                                LEAVE loopmasterclub;
                            END IF;

                            SET @dynamiclinkedSQL=CONCAT('SELECT `is_household_head`',' INTO @master_table_ishousehold FROM `fg_cm_contact` WHERE `id` = ',fgcm_contact_id);
                            PREPARE exec_sel_qry FROM @dynamiclinkedSQL;
                            EXECUTE exec_sel_qry ;	
                            DEALLOCATE PREPARE exec_sel_qry;

                            IF(@master_table_ishousehold = 1)THEN
                                SET @dynamicMasterupdateSQL=CONCAT('UPDATE `fg_cm_contact` SET `is_household_head`= ','0',' WHERE `id`=',fgcm_contact_id);
                                PREPARE exec_sel_qry FROM @dynamicMasterupdateSQL;
                                EXECUTE exec_sel_qry ;	
                                DEALLOCATE PREPARE exec_sel_qry;

                                SET SQL_SAFE_UPDATES = 0;

                                SET @dynamicMasterLinkedContactSQL=CONCAT('UPDATE `fg_cm_contact` AS mt LEFT JOIN `fg_cm_linkedcontact` AS fcl ON mt.`id`=fcl.`linked_contact_id` AND fcl.`club_id`=',linked_club_id,' AND fcl.`contact_id`=',fgcm_contact_id,' SET mt.`is_household_head`=0 ',' WHERE mt.`is_seperate_invoice`= 1 AND fcl.`contact_id`=',fgcm_contact_id,' AND mt.`id`=fcl.`linked_contact_id` AND fcl.`club_id`=',linked_club_id);
                                PREPARE exec_up_qry FROM @dynamicMasterLinkedContactSQL;
                                EXECUTE exec_up_qry ;	
                                DEALLOCATE PREPARE exec_up_qry;
								
                                SET @dynamicCountContact=CONCAT('SELECT COUNT(`linked_contact_id`)',' INTO @count_linked_contact_id FROM `fg_cm_linkedcontact` WHERE `contact_id` = ',fgcm_contact_id,' AND `club_id`=',linked_club_id,' AND `type`="household"');
                                PREPARE exec_up_qry FROM @dynamicCountContact;
                                EXECUTE exec_up_qry ;	
                                DEALLOCATE PREPARE exec_up_qry;

                                IF(@count_linked_contact_id>1) THEN
                                    SET @dynamicMasterSetMainContactSQL=CONCAT('UPDATE `fg_cm_contact` AS mt SET `is_household_head`= ','1',' WHERE mt.`id`=',linked_contact_id_cursor);
                                    PREPARE exec_up_qry FROM @dynamicMasterSetMainContactSQL;
                                    EXECUTE exec_up_qry ;	
                                    DEALLOCATE PREPARE exec_up_qry; 
                                END IF;
                                SET SQL_SAFE_UPDATES = 1;
                            END IF;

                        END LOOP loopmasterclub;
                    CLOSE masterclubcursor;
            	END BLOCK3; 

                -- This section is used to make changes in the fg_cm_club_log and delete entry of the contact in the sf_guard_user table 
                -- if the user have any user rights
                BLOCK4: BEGIN 
                    DECLARE sfguard_userrights_cursor CURSOR FOR
                    SELECT COUNT(gu.`contact_id`), gu.`contact_id`, gu.`club_id`, gg.`id`, gg.`type`,  IF(r.`id` != '',r.`id`,p.`id`),gu.`id`, 
                        IF (r.`id` != '' , CONCAT(r.`type`,"-",gg.`name`," (",r.`title`,")"),IF(p.`id` != '' , CONCAT('P',"-",gg.`name`," (",p.`title`,")"), gg.`name` ))
                    FROM sf_guard_user AS gu
                    JOIN sf_guard_user_group AS gug ON gug.`user_id` = gu.`id`
                    LEFT JOIN sf_guard_group AS gg ON gg.`id` = gug.`group_id`
                    LEFT JOIN sf_guard_user_team AS fsa ON ( gug.`group_id` = fsa.`group_id` AND gu.`id` = fsa.`user_id`)
                    LEFT JOIN fg_rm_role AS r ON fsa.`role_id` = r.`id`
                    LEFT JOIN sf_guard_user_page AS fsp ON ( gug.`group_id` = fsp.`group_id` AND gu.`id` = fsp.`user_id`)
                    LEFT JOIN fg_cms_page AS p ON fsp.`page_id` = p.`id`
                    WHERE gu.`contact_id` IN(fedContactId, subFedContactId,contact_ids_cursor_contact_id)
                    GROUP BY gg.`id`,r.`id`;

                    OPEN sfguard_userrights_cursor;
                        loop_sfguard_userrights: LOOP 
                            FETCH sfguard_userrights_cursor 
                            INTO count_user, contact_id_user, group_club_id, group_id_new, group_type_new, role_id_new, user_id_new, group_name2 ;  

                            IF record_not_found THEN
                                SET record_not_found = 0;
                                LEAVE loop_sfguard_userrights;
                            END IF;

                            IF(count_user>0)THEN
                                SET logstr4 = CONCAT("(",contact_id_user,",",group_club_id,",'",date_today,"'",",","'",'user rights',"'",",","'",'-',"'",",","'",group_name2,"'",",","'",'-',"'",",",currentuser,",",currentuser,")");
                                SET @insertqry = CONCAT(@insertqry,logstr4,',');										
                            END IF;

                            SELECT COUNT(`user_id`) INTO count_userid FROM `sf_guard_user_group` WHERE `group_id`= group_id_new AND `user_id`= user_id_new;
                            /*log entry in fg_club_log emoved as its not used anymore*/
                        END LOOP loop_sfguard_userrights;
                    CLOSE sfguard_userrights_cursor;
                    DELETE FROM `sf_guard_user` WHERE `contact_id` IN(fedContactId, subFedContactId,contact_ids_cursor_contact_id);
                END BLOCK4; 
                -- Updating fg_cm_role_log,fg_cm_function_log,fg_cm_log_assignment and delete entry of the contact in the fg_rm_role_contact table 
                -- if the user have any assignments
                BLOCK6: BEGIN
                    DECLARE fgrmrolecontact_cursor CURSOR FOR
                    SELECT rc.`id`, rcat.`title`,rcat.`id`, r.`id`, r.`type`, r.`title`,rf.`id`, rf.`title`,rcat.`club_id`,rcat.`is_fed_category`,r.`is_executive_board`,rcat.`contact_assign`, rc.`contact_id` FROM `fg_rm_role_contact` AS rc
                        LEFT JOIN fg_rm_category_role_function AS crf ON rc.`fg_rm_crf_id` = crf.`id`
                        INNER JOIN fg_rm_category AS rcat ON crf.`category_id` = rcat.`id`
                        INNER JOIN fg_rm_role AS r ON crf.`role_id` = r.`id`
                        LEFT JOIN fg_rm_function AS rf ON crf.`function_id` = rf.`id`
                        WHERE rc.`contact_id` IN(fedContactId, subFedContactId,contact_ids_cursor_contact_id);
                    OPEN fgrmrolecontact_cursor;
                        loop_fgrmrolecontact: LOOP 
                            FETCH fgrmrolecontact_cursor INTO rcid, category_title_new, cat_id_new, role_id_new, role_type, role_title, funid, function_title,cat_club,is_fed_cat,is_executive_board,catagory_contact_assign, rc_contact_id;    
                            IF record_not_found THEN
                                SET record_not_found = 0;
                                LEAVE loop_fgrmrolecontact;
                            END IF;
                            IF(function_title != '')THEN
                                SET value_before_new = CONCAT(role_title," (",function_title,")");
                            ELSE
                                SET value_before_new = role_title ;
                            END IF;
                            IF(category_title_new = 'Team')THEN
                                SET category_title_new = team_terminology;
                            END IF;
                            IF(is_fed_cat=1)THEN
                                   SET cat_type='fed';
                            ELSE 
                                   SET cat_type='club';
                            END IF;

                            INSERT INTO `fg_cm_log_assignment` (`contact_id`, `category_club_id`, `role_type`,`category_id`, `role_id`, `function_id`,`date`, `category_title`, `value_before`, `value_after`,`changed_by` ) VALUES (rc_contact_id,cat_club,cat_type,cat_id_new,role_id_new,funid,date_today,category_title_new,value_before_new,'',currentuser);
                            INSERT INTO `fg_rm_role_log` (`role_id`, `contact_id`, `club_id`, `date`, `kind`,`field`, `value_before`, `value_after`,`changed_by`) VALUES (role_id_new, rc_contact_id, cat_club, date_today, 'assigned contacts', value_before_new, mainContactName, '', currentuser);

                            IF(funid != '')THEN
                                INSERT INTO `fg_rm_function_log` (`date`, `role_id`, `contact_id`, `club_id`, `kind`,`function_id`,`field`, `value_before`, `value_after`,`changed_by`) VALUES (date_today, role_id_new, rc_contact_id, cat_club, 'assigned contacts', funid, function_title, mainContactName, '', currentuser);
                            END IF;
						 
                            DELETE FROM `fg_rm_role_contact` WHERE `id`= rcid;
                            
                        END LOOP loop_fgrmrolecontact;
                    CLOSE fgrmrolecontact_cursor;
                END BLOCK6; 


                -- Updating fg_cm_log_connection if the user have any connections
                BLOCK7: BEGIN  
                    SET SQL_SAFE_UPDATES = 0;
                    INSERT INTO `fg_cm_log_connection` (`contact_id`, `linked_contact_id`, `assigned_club_id`,`date`,`connection_type`, `relation`, `value_before`,`value_after`,`changed_by`) SELECT fgcmlc.`contact_id`,fgcmlc.`linked_contact_id`,fgcmlc.`club_id`,date_today,'Household contact',fcr.`name`,contactName(fgcmlc.`linked_contact_id`),'',currentuser
                    FROM `fg_cm_linkedcontact` AS fgcmlc 
                    LEFT JOIN `fg_cm_relation` AS fcr ON fgcmlc.`relation_id`=fcr.`id`
                    WHERE fgcmlc.`contact_id` IN(fedContactId, subFedContactId,contact_ids_cursor_contact_id) AND fgcmlc.`type` = 'household';

                    INSERT INTO `fg_cm_log_connection` (`contact_id`, `linked_contact_id`, `assigned_club_id`,`date`,`connection_type`, `relation`, `value_before`,`value_after`,`changed_by`) SELECT fgcmlc.`contact_id`,fgcmlc.`linked_contact_id`,fgcmlc.`club_id`,date_today,'Household contact',fcr.`name`,contactName(contact_ids_cursor_contact_id),'',currentuser
                    FROM `fg_cm_linkedcontact` AS fgcmlc 
                    LEFT JOIN `fg_cm_relation` AS fcr ON fgcmlc.`relation_id`=fcr.`id`
                    WHERE fgcmlc.`linked_contact_id` IN(fedContactId, subFedContactId,contact_ids_cursor_contact_id) AND fgcmlc.`type` = 'household';
                    SET SQL_SAFE_UPDATES = 1;
                 END BLOCK7;

                 -- Updating fg_cm_log_connection if the user have any connections
                BLOCK8: BEGIN 
                    DECLARE other_connection_cursor CURSOR FOR 
                    SELECT fgcmlc.`contact_id`,fgcmlc.`linked_contact_id`,fgcmlc.`relation_id`,fgcmlc.`relation`,fcr.`name`,fgcmlc.`type`,fgcmlc.`club_id`
                    FROM `fg_cm_linkedcontact` AS fgcmlc 
                    LEFT JOIN `fg_cm_relation` AS fcr ON fgcmlc.`relation_id`=fcr.`id`
                    WHERE (fgcmlc.`contact_id` IN(fedContactId, subFedContactId,contact_ids_cursor_contact_id) OR fgcmlc.`linked_contact_id` IN(fedContactId, subFedContactId,contact_ids_cursor_contact_id)) AND (fgcmlc.`type`='otherpersonal' OR fgcmlc.`type`='othercompany');

                    OPEN other_connection_cursor;
                            loopotherconnection: LOOP
                                    FETCH other_connection_cursor INTO other_contact_id,other_linkedcontact_id,other_relation_id,other_relation,other_name,other_connection_type,other_club_id;
                                    IF record_not_found THEN
                                            SET record_not_found = 0;
                                            LEAVE loopotherconnection;
                                    END IF;
                                    SET SQL_SAFE_UPDATES = 0;
                                    IF(is_companyn=1) THEN
                                            CASE 
                                                    WHEN other_connection_type='otherpersonal' THEN
                                                            INSERT INTO `fg_cm_log_connection` (`contact_id`, `linked_contact_id`, `assigned_club_id`,`date`,`connection_type`, `relation`, `value_before`,`value_after`,`changed_by`) VALUES (other_contact_id, other_linkedcontact_id, other_club_id, date_today, 'Single person', other_relation, contactName(other_linkedcontact_id),'', currentuser);

                                                    WHEN other_connection_type='othercompany' THEN
                                                            INSERT INTO `fg_cm_log_connection` (`contact_id`, `linked_contact_id`, `assigned_club_id`,`date`,`connection_type`, `relation`, `value_before`,`value_after`,`changed_by`) VALUES (other_contact_id, other_linkedcontact_id, other_club_id, date_today, 'Company', other_relation, contactName(other_linkedcontact_id),'', currentuser);
                                            END CASE;
                                    ELSE
                                            CASE 
                                                    WHEN other_connection_type='otherpersonal' THEN
                                                            INSERT INTO `fg_cm_log_connection` (`contact_id`, `linked_contact_id`, `assigned_club_id`,`date`,`connection_type`, `relation`, `value_before`,`value_after`,`changed_by`) VALUES (other_contact_id, other_linkedcontact_id, other_club_id, date_today, 'Single person', other_name, contactName(other_linkedcontact_id),'', currentuser);

                                                    WHEN other_connection_type='othercompany' THEN
                                                            INSERT INTO `fg_cm_log_connection` (`contact_id`, `linked_contact_id`, `assigned_club_id`,`date`,`connection_type`, `relation`, `value_before`,`value_after`,`changed_by`) VALUES (other_contact_id, other_linkedcontact_id, other_club_id, date_today, 'Company', other_relation, contactName(other_linkedcontact_id),'', currentuser);
                                            END CASE;
                                    END IF;
                                    SET SQL_SAFE_UPDATES = 1;
                            END LOOP loopotherconnection;
                    CLOSE other_connection_cursor;
                END BLOCK8;

                SET SQL_SAFE_UPDATES = 0;
                DELETE FROM `fg_cm_linkedcontact` WHERE (`contact_id` IN(fedContactId, subFedContactId,contact_ids_cursor_contact_id) OR `linked_contact_id` IN(fedContactId, subFedContactId,contact_ids_cursor_contact_id)); 

                SET SQL_SAFE_UPDATES = 1;
                
                DELETE FROM `fg_cm_bookmarks` WHERE `contact_id` IN(fedContactId, subFedContactId,contact_ids_cursor_contact_id);
                DELETE FROM `fg_table_settings` WHERE `contact_id` IN(fedContactId, subFedContactId,contact_ids_cursor_contact_id);
--                 DELETE FROM `fg_club_bookmarks` WHERE `contact_id` IN(fedContactId, subFedContactId,contact_ids_cursor_contact_id);
--                 DELETE FROM `fg_club_table_settings` WHERE `contact_id` IN(fedContactId, subFedContactId,contact_ids_cursor_contact_id);
                DELETE FROM `fg_rm_role_manual_contacts` WHERE `contact_id` IN(fedContactId, subFedContactId,contact_ids_cursor_contact_id);
                DELETE FROM `fg_cn_recepients_mandatory` WHERE `contact_id` IN(fedContactId, subFedContactId,contact_ids_cursor_contact_id); 
                DELETE FROM `fg_cn_recepients_exception` WHERE `contact_id` IN(fedContactId, subFedContactId,contact_ids_cursor_contact_id);
                DELETE FROM `fg_dm_assigment` WHERE `contact_id` IN(fedContactId, subFedContactId,contact_ids_cursor_contact_id);
                DELETE FROM `fg_cm_fedmembership_confirmation_log` WHERE `status`='PENDING' AND `contact_id` IN(fedContactId, subFedContactId,contact_ids_cursor_contact_id);
                DELETE FROM `fg_cm_change_toconfirm` WHERE `confirm_status`='NONE' AND `contact_id` IN(fedContactId, subFedContactId,contact_ids_cursor_contact_id);


            END LOOP contact_ids_one;	
            SET @insertqry = CONCAT(@insertqry,'##');
            SET @pre_query  = REPLACE(@insertqry,',##','');
            
            PREPARE exec_qry FROM @pre_query;
            EXECUTE exec_qry;
            DEALLOCATE PREPARE exec_qry;
        CLOSE contactidscursor;
    END BLOCK1;
    /*INSERT INTO `fg_cache` (`tabletitle`, `club_id`, `updated_at`) VALUES ('common',clubid,present_time); */
 COMMIT;
END